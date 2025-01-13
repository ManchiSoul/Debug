<?php

	#[AllowDynamicProperties]
	class WS_Form_Submit extends WS_Form_Core {

		public $id = 0;
		public $form_id;
		public $date_added;
		public $date_updated;
		public $date_expire;
		public $user_id;
		public $hash;
		public $token;
		public $token_validated;
		public $duration;
		public $count_submit;
		public $status;
		public $actions;
		public $section_repeatable;
		public $preview;
		public $spam_level;
		public $starred;
		public $viewed;

		public $meta;
		public $meta_protected;

		public $post_mode;

		public $form_object;

		public $error;
		public $error_message;
		public $error_code;

		public $error_validation_actions;

		public $encrypted;

		public $table_name;
		public $table_name_meta;

		public $bypass_required_array;
		public $hidden_array;

		public $field_types;

		public $field_object_cache = array();

		public $file_objects = array();

		public $submit_fields = false;

		public $return_hash = false;

		public $keys = false;
		public $keys_meta = false;
		public $keys_fixed = false;
		public $keys_fields = false;
		public $keys_ecommerce = false;
		public $keys_tracking = false;

		public $form_count_submit_cache = false;
		public $form_count_submit_unread_cache = false;

		const DB_INSERT = 'form_id,date_added,date_updated,date_expire,user_id,hash,token,token_validated,duration,count_submit,status,actions,section_repeatable,preview,spam_level,starred,viewed,encrypted';
		const DB_UPDATE = 'form_id,date_added,date_updated,date_expire,user_id,hash,token,token_validated,duration,count_submit,status,actions,section_repeatable,preview,spam_level,starred,viewed,encrypted';
		const DB_SELECT = 'form_id,date_added,date_updated,date_expire,user_id,hash,token,token_validated,duration,count_submit,status,actions,section_repeatable,preview,spam_level,starred,viewed,encrypted,id';

		public function __construct() {

			global $wpdb;

			$this->id = 0;
			$this->form_id = 0;
			$this->user_id = get_current_user_id();
			$this->hash = '';
			$this->token = false;
			$this->token_validated = false;
			$this->status = 'draft';
			$this->duration = 0;
			$this->count_submit = 0;
			$this->meta = array();
			$this->meta_protected = array();
			$this->actions = '';
			$this->section_repeatable = '';
			$this->preview = false;
			$this->date_added = WS_Form_Common::get_mysql_date();
			$this->date_updated = WS_Form_Common::get_mysql_date();
			$this->date_expire = null;
			$this->spam_level = null;
			$this->starred = false;
			$this->viewed = false;

			$this->post_mode = false;

			$this->error = false;
			$this->error_message = '';
			$this->error_code = 200;

			$this->error_validation_actions = array();

			// Encryption
			$ws_form_encryption = new WS_Form_Encryption();
			$this->encrypted = $ws_form_encryption->can_encrypt;
			// Get field types in single dimension array
			$this->field_types = false;

			$this->table_name = sprintf('%s%ssubmit', $wpdb->prefix, WS_FORM_DB_TABLE_PREFIX);
			$this->table_name_meta = sprintf('%s_meta', $this->table_name);
		}

		// Create
		public function db_create($update_count_submit_unread = true) {

			// No capabilities required, this is a public method

			// Check form ID
			self::db_check_form_id();

			global $wpdb;

			// Insert submit record

			// Handle NULL values because $wpdb->prepare does not
			$sql_prepare = sprintf(

				"INSERT INTO {$this->table_name} (" . self::DB_INSERT . ") VALUES (%%d, %%s, %%s, %s, %%d, '', '', 0, %%d, %%d, %%s, %%s, %%s, %%d, %s, %%d, %%d, %%d);",
				(is_null($this->date_expire) ? 'NULL' : "'" . $this->date_expire . "'"),
				(is_null($this->spam_level) ? 'NULL' : $this->spam_level)
			);

			$sql = $wpdb->prepare(

				$sql_prepare,
				$this->form_id,
				$this->date_added,
				$this->date_updated,
				$this->user_id,
				$this->duration,
				$this->count_submit,
				$this->status,
				$this->actions,
				$this->section_repeatable,
				($this->preview ? 1 : 0),
				($this->starred ? 1 : 0),
				($this->viewed ? 1 : 0),
				($this->encrypted ? 1 : 0)
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error adding submit', 'ws-form')); }

			// Get inserted ID
			$this->id = $wpdb->insert_id;

			// Create hash
			self::db_create_hash();

			// Create token
			self::db_create_token();

			// Update hash
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET hash = %s, token = %s WHERE id = %d LIMIT 1",
				$this->hash,
				$this->token,
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit.', 'ws-form')); }

			// Update form submit unread count statistic
			if($update_count_submit_unread) {

				$ws_form_form = new WS_Form_Form();
				$ws_form_form->id = $this->form_id;
				$ws_form_form->db_update_count_submit_unread(true);
			}

			// Run action
			do_action('wsf_submit_create', $this);
		}

		// Read record to array
		public function db_read($get_meta = true, $get_expanded = true, $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			self::db_check_id();

			global $wpdb;

			// Add fields
			$sql = $wpdb->prepare(

				"SELECT " . self::DB_SELECT . " FROM {$this->table_name} WHERE id = %d LIMIT 1;",
				$this->id
			);

			$submit_array = $wpdb->get_row($sql, 'ARRAY_A');
			if(is_null($submit_array)) { parent::db_wpdb_handle_error(__('Unable to read submission.', 'ws-form')); }

			// Set class variables
			foreach($submit_array as $key => $value) {

				$this->{$key} = $value;
			}

			// Convert into object
			$submit_object = json_decode(wp_json_encode($submit_array));

			// Process meta data
			if($get_meta) {

				$this->meta = $submit_object->meta = self::db_get_submit_meta($submit_object, false, $bypass_user_capability_check);
			}

			// Get user data
			if($get_expanded) {

				self::db_read_expanded($submit_object, true, true, true, true, true, true, true, $bypass_user_capability_check);
			}

			// Preview to boolean
			if(isset($this->preview)) { $this->preview = $submit_object->preview = (bool) $this->preview; }

			// Encrypted to boolean
			if(isset($this->encrypted)) { $this->encrypted = $submit_object->encrypted = (bool) $this->encrypted; }

			// Return array
			return $submit_object;
		}

		// Read expanded data for a record
		public function db_read_expanded(&$submit_object, $expand_user = true, $expand_date_added = true, $expand_date_updated = true, $expand_status = true, $expand_actions = true, $expand_section_repeatable = true, $expand_file_objects = true, $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			if(
				!$bypass_user_capability_check &&	// Do not expand user data if this is a public request
				$expand_user &&
				isset($submit_object->user_id) &&
				($submit_object->user_id > 0)
			) {

				$user = get_user_by('ID', $submit_object->user_id);
				if($user !== false) {

					$this->user = $submit_object->user = (object) array(

						'first_name' 	=>	$user->first_name,
						'last_name' 	=>	$user->last_name,
						'display_name'	=> $user->display_name
					);
				}
			}

			// Date added
			if($expand_date_added && isset($submit_object->date_added)) {

				$this->date_added_wp = $submit_object->date_added_wp = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($submit_object->date_added)));
			}

			// Date updated
			if($expand_date_updated && isset($submit_object->date_updated)) {

				$this->date_updated_wp = $submit_object->date_updated_wp = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($submit_object->date_updated)));
			}

			// Status
			if($expand_status && isset($submit_object->status)) {

				$this->status_full = $submit_object->status_full = self::db_get_status_name($submit_object->status);
			}

			// Unserialize actions
			if($expand_actions && isset($submit_object->actions)) {

				$this->actions = $submit_object->actions = is_serialized($submit_object->actions) ? unserialize($submit_object->actions) : false;
			}

			// Unserialize section_repeatable
			if($expand_section_repeatable && isset($submit_object->section_repeatable)) {

				$this->section_repeatable = $submit_object->section_repeatable = is_serialized($submit_object->section_repeatable) ? unserialize($submit_object->section_repeatable) : false;
			}

			// File objects
			if($expand_file_objects && isset($submit_object->meta)) {

				$metas = (array) $submit_object->meta;

				foreach($metas as $meta_key => $meta) {

					$meta = (array) $meta;

					// Add URLs to file objects all objects
					if(
						isset($meta['type']) &&
						(($meta['type'] == 'file') || ($meta['type'] == 'signature')) &&
						isset($meta['value']) &&
						is_array($meta['value']) &&
						(count($meta['value']) > 0) &&
						is_array($meta['value'][0]) &&
						isset($meta['id'])
					) {

						foreach($meta['value'] as $file_object_index => $file_object) {

							if(
								isset($file_object['url']) ||
								!isset($file_object['name']) ||
								!isset($file_object['size']) ||
								!isset($file_object['type']) ||
								!isset($file_object['path'])

							) { continue; }

							// Get handler
							$handler = isset($file_object['handler']) ? $file_object['handler'] : 'wsform';

							// Get URL
							if(isset(WS_Form_File_Handler_WS_Form::$file_handlers[$handler])) {

								$section_repeatable_index = isset($meta['repeatable_index']) ? absint($meta['repeatable_index']) : 0;

								$url = WS_Form_File_Handler_WS_Form::$file_handlers[$handler]->get_url($file_object, $meta['id'], $file_object_index, $submit_object->hash, $section_repeatable_index);

							} else {

								$url = '#';
							}

							// Set URL
							$this->meta[$meta_key]['value'][$file_object_index]['url'] = $submit_object->meta[$meta_key]['value'][$file_object_index]['url'] = $url;

							// Set preview if attachment ID is set
							if(isset($file_object['attachment_id'])) {

								// Get image size
								$image_size = apply_filters('wsf_dropzonejs_image_size', WS_FORM_DROPZONEJS_IMAGE_SIZE);

								$attachment_id = $file_object['attachment_id'];

								$file_preview = wp_get_attachment_image_src($attachment_id, $image_size, true);
								if($file_preview) {

									$file_preview = $file_preview[0];

								} else {

									$file_preview = wp_get_attachment_thumb_url($attachment_id);

									if(!$file_preview) { $file_preview = ''; }
								}
								if(!$file_preview) { $file_preview = ''; }

								$this->meta[$meta_key]['value'][$file_object_index]['preview'] = $submit_object->meta[$meta_key]['value'][$file_object_index]['preview'] = $file_preview;
							}
						}
					}
				}
			}
		}

		// Read - All
		public function db_read_all($join = '', $where = '', $group_by = '', $order_by = '', $limit = '', $offset = '', $get_meta = true, $get_expanded = true, $bypass_user_capability_check = false, $clear_hidden_fields = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Build SQL
			$sql = sprintf(

				'SELECT %s FROM %s',
				self::get_select($join),
				$this->table_name
			);

			if($join != '') { $sql .= sprintf(" %s", $join); }
			if($where != '') { $sql .= sprintf(" WHERE %s", $where); }
			if($group_by != '') { $sql .= sprintf(" GROUP BY %s", $group_by); }
			if($order_by != '') { $sql .= sprintf(" ORDER BY %s", $order_by); }
			if($limit != '') { $sql .= sprintf(" LIMIT %u", absint($limit)); }
			if($offset != '') { $sql .= sprintf(" OFFSET %u", absint($offset)); }

			$sql .= ';';

			// Get results
			global $wpdb;
			$return_array = $wpdb->get_results($sql);	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if(is_null($return_array)) { return; }

			foreach($return_array as $key => $submit_object) {

				// Check form ID
				if(absint($submit_object->form_id) === 0) {

					// Delete this orphaned submit record
					$this->id = $submit_object->id;
					self::db_delete(true);

					// Remove from return array
					unset($return_array[$key]);

					continue;
				}

				// Process meta data
				if($get_meta) {

					// Get meta data
					$submit_object->meta = self::db_get_submit_meta($submit_object, false, $bypass_user_capability_check);

					// Clear hidden fields
					if($clear_hidden_fields) {

						$submit_object = self::clear_hidden_meta_values($submit_object);
					}
				}
	
				// Process expanded data
				if($get_expanded) {

					self::db_read_expanded($submit_object, true, true, true, true, true, true, true, $bypass_user_capability_check);
				}

				$return_array[$key] = $submit_object;
			}

			return $return_array;
		}

		// Read - All
		public function db_read_ids($join = '', $where = '', $group_by = '', $order_by = '', $limit = '', $offset = '', $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Build SQL
			$sql = sprintf(

				'SELECT %1$s.id FROM %1$s',
				$this->table_name
			);

			if($join != '') { $sql .= sprintf(" %s", $join); }
			if($where != '') { $sql .= sprintf(" WHERE %s", $where); }
			if($group_by != '') { $sql .= sprintf(" GROUP BY %s", $group_by); }
			if($order_by != '') { $sql .= sprintf(" ORDER BY %s", $order_by); }
			if($limit != '') { $sql .= sprintf(" LIMIT %u", absint($limit)); }
			if($offset != '') { $sql .= sprintf(" OFFSET %u", absint($offset)); }

			$sql .= ';';

			global $wpdb;

			$return_array = $wpdb->get_results($sql, 'ARRAY_A');	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			return empty($return_array) ? null : array_column($return_array, 'id');
		}

		// Read - Count
		public function db_read_count($join = '', $where = '', $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			global $wpdb;

			// Build SQL
			$sql = sprintf(

				'SELECT COUNT(%1$s.id) FROM %1$s',
				$this->table_name
			);

			if($join != '') { $sql .= sprintf(" %s", $join); }
			if($where != '') { $sql .= sprintf(" WHERE %s", $where); }

			$sql .= ';';

			$read_count = $wpdb->get_var($sql);
			if(is_null($read_count)) { return 0; }

			return absint($read_count);
		}

		// Read by hash
		public function db_read_by_hash($get_meta = true, $get_expanded = true, $form_id_check = true, $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check form ID
			if($form_id_check) { self::db_check_form_id(); }

			// Check hash
			if(!WS_Form_Common::check_submit_hash($this->hash)) {

				$this->hash = '';

				parent::db_throw_error(__('Invalid hash', 'ws-form'));
			}

			// Check token
			if($this->token !== false) {

				if(!WS_Form_Common::check_submit_hash($this->token)) {

					$this->token = '';

					parent::db_throw_error(__('Invalid token', 'ws-form'));
				}

				$token_check = $this->token;

			} else {

				$token_check = false;
			}

			global $wpdb;

			// Get form submission
			if($form_id_check) {

				$sql = $wpdb->prepare(

					"SELECT " . self::DB_SELECT . " FROM {$this->table_name} WHERE form_id = %d AND hash = %s AND (NOT status = 'trash') LIMIT 1;",
					$this->form_id,
					$this->hash
				);

			} else {

				$sql = $wpdb->prepare(

					"SELECT " . self::DB_SELECT  . " FROM {$this->table_name} WHERE hash = %s AND (NOT status = 'trash') LIMIT 1;",
					$this->hash
				);				
			}

			$submit_array = $wpdb->get_row($sql, 'ARRAY_A');
			if(is_null($submit_array)) {

				$this->hash = '';

				parent::db_wpdb_handle_error(__("Submission record not found. Ensure you have a 'Save Submission' action.", 'ws-form'));
			}

			// Set class variables
			foreach($submit_array as $key => $value) {

				$this->{$key} = $value;
			}

			// Convert into object
			$submit_object = json_decode(wp_json_encode($submit_array));

			// Process meta data
			if($get_meta) {

				$this->meta = $submit_object->meta = self::db_get_submit_meta($submit_object, false, $bypass_user_capability_check);
			}

			// Expand data
			if($get_expanded) {

				self::db_read_expanded($submit_object, true, true, true, true, true, true, true, $bypass_user_capability_check);
			}

			// Perform token validation
			if(!$this->token_validated && ($token_check !== false)) {

				if($this->token === $token_check) {

					$this->token_validated = $submit_object->token_validated = true;

					// Update hash
					$sql = $wpdb->prepare(

						"UPDATE {$this->table_name} SET token_validated = 1, spam_level = 0 WHERE id = %d LIMIT 1",
						$this->id
					);

					if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit.', 'ws-form')); }
				}
			}

			// Return array
			return $submit_object;
		}

		// Update current submit
		public function db_update() {

			// No capabilities required, this is a public method

			// Check ID
			self::db_check_id();

			// Update / Insert
			$this->id = parent::db_update_insert($this->table_name, self::DB_UPDATE, self::DB_INSERT, $this, 'submit', $this->id);

			// Update meta
			if(isset($this->meta)) {

				$ws_form_submit_meta = New WS_Form_Submit_Meta();
				$ws_form_submit_meta->parent_id = $this->id;
				$ws_form_submit_meta->db_update_from_object($this->meta, $this->encrypted);
			}

			// Run action
			do_action('wsf_submit_update', $this);
		}

		// Push submit from array
		public function db_update_from_object($submit_object) {

			// No capabilities required, this is a public method

			// Check for submit ID in $submit
			if(isset($submit_object->id)) { $this->id = absint($submit_object->id); } else { return false; }

			// Encryption
			$submit_encrypted = isset($submit_object->encrypted) ? $submit_object->encrypted : false;

			// Update / Insert
			$this->id = parent::db_update_insert($this->table_name, self::DB_UPDATE, self::DB_INSERT, $submit_object, 'submit', $this->id);

			// Update meta
			if(isset($submit_object->meta)) {

				$ws_form_submit_meta = New WS_Form_Submit_Meta();
				$ws_form_submit_meta->parent_id = $this->id;
				$ws_form_submit_meta->db_update_from_object($submit_object->meta, $submit_encrypted);
			}
		}

		// Get post data. Returns the submission object in a key value format 
		public function get_post_data() {

			// Build return array
			$return_array = array(

				'id' => $this->id,
				'form_id' => $this->form_id,
				'date_added' => $this->date_added,
				'date_updated' => $this->date_updated,
				'date_expire' => $this->date_expire,
				'user_id' => $this->user_id,
				'hash' => $this->hash
			);

			// Process meta
			foreach($this->meta as $key => $value) {

				if(strpos($key, WS_FORM_FIELD_PREFIX) === 0) {

					if(!isset($value['value'])) { continue; }

					if(is_array($value['value'])) { $value['value'] = implode(',', $value['value']); }

					$return_array[$key] = $value['value'];
				}
			}

			return $return_array;
		}

		// Stamp submit with date updated, increase submit count and add duration (if available)
		public function db_stamp() {

			// No capabilities required, this is a public method

			// Check ID
			self::db_check_id();

			// Get duration
			$this->duration = absint(WS_Form_Common::get_query_var_nonce('wsf_duration', 0));

			global $wpdb;

			// Date updated, count submit + 1
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET date_updated = %s, count_submit = count_submit + 1, duration = %d WHERE id = %d LIMIT 1",
				WS_Form_Common::get_mysql_date(),
				$this->duration,
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit date updated.', 'ws-form')); }
			$this->count_submit++;

			// User ID
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET user_id = %d WHERE id = %d AND (user_id = 0 OR user_id IS NULL) LIMIT 1",
				get_current_user_id(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit user ID,', 'ws-form')); }
		}

		// Delete
		public function db_delete($permanent_delete = false, $count_update = true, $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('delete_submission', $bypass_user_capability_check);

			self::db_check_id();

			// Read the submit status
			self::db_read(true, false, $bypass_user_capability_check);

			if(in_array($this->status, array('spam', 'trash'))) { $permanent_delete = true; }

			// If status is trashed, do a permanent delete of the data
			if($permanent_delete) {

				global $wpdb;

				// Delete submit
				$sql = $wpdb->prepare(

					"DELETE FROM {$this->table_name} WHERE id = %d;",
					$this->id
				);

				if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error deleting submit.', 'ws-form')); }

				// Delete meta
				$ws_form_meta = New WS_Form_Submit_Meta();
				$ws_form_meta->parent_id = $this->id;
				$ws_form_meta->db_delete_by_submit($bypass_user_capability_check);

				// Run action
				do_action('wsf_submit_delete_permanent', $this);

			} else {

				// Set status to 'trash'
				self::db_set_status('trash', $count_update, $bypass_user_capability_check);
			}

			return true;
		}

		// Delete trashed submits
		public function db_trash_delete() {

			// User capability check
			WS_Form_Common::user_must('delete_submission');

			self::db_check_form_id();

			// Get all trashed forms
			$submits = self::db_read_all('', "status='trash' AND form_id=" . $this->form_id, '', '', '', '', false, false);

       		foreach($submits as $submit_object) {

				$this->id = $submit_object->id;
				self::db_delete();
			}

			return true;
		}

		// Export by email
		public function db_exporter($email_address) {

			// User capability check
			WS_Form_Common::user_must('read_submission');

			// Check email address
			if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) { return false; }

			$data_to_export = array();

			global $wpdb;

			// Get submit records
			$sql = $wpdb->prepare(

				"SELECT {$this->table_name}.id FROM {$this->table_name_meta} LEFT OUTER JOIN {$this->table_name} ON {$this->table_name}.id = {$this->table_name_meta}.parent_id WHERE (LOWER({$this->table_name_meta}.meta_value) = %s) AND NOT ({$this->table_name}.id IS NULL);",
				strtolower($email_address)
			);

			$submissions = $wpdb->get_results($sql);

			// Process results
			if($submissions) {

				foreach($submissions as $submission) {

					// Reset submit data
					$submit_data = array();

					// Get submit ID
					$submit_id = $submission->id;

					// Get submit record
					$this->id = $submit_id;
					$submit_object = self::db_read();

					// Remove some data that will not be shared for security reasons or internal only
					unset($submit_object->form_id);
					unset($submit_object->user_id);
					unset($submit_object->id);
					unset($submit_object->actions);
					unset($submit_object->preview);
					unset($submit_object->status);


					// Push all submit data
					foreach($submit_object as $key => $value) {

						// Convert objects to array (e.g. user data)
						if(is_object($value)) {

							$value = (array) $value;
						}

						if(is_array($value)) {
							
							foreach($value as $meta_key => $meta_value) {

								if(is_object($meta_value)) {

									$meta_value = (array) $meta_value;
								}

								if(is_array($meta_value)) {

									$value = $meta_value['value'];

									if(is_object($value)) {

										$value = (array) $value;
									}

									if(is_array($value)) {

										$value = implode(',', $value);
									}

								} else {

									$value = $meta_value;											
								}

								$submit_data[] = array('name' => $meta_key, 'value' => $value);
							}

						} else {
							
							$submit_data[] = array('name' => $key, 'value' => $value);
						}
					}

					$data_to_export[] = array(
						'group_id'    => WS_FORM_USER_REQUEST_IDENTIFIER,
						'group_label' => __('Form Submissions', 'ws-form'),
						'item_id'     => WS_FORM_USER_REQUEST_IDENTIFIER . '-' . $submit_object->hash,
						'data'        => $submit_data
					);
				}
			}

			// Return
			return array(

				'data' => $data_to_export,
				'done' => true,
			);
		}

		// Erase by email
		public function db_eraser($email_address) {

			global $wpdb;

			// User capability check
			WS_Form_Common::user_must('delete_submission');

			// Check email address
			if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)) { return false; }

			// Return array
			$items_removed_count = 0;
			$items_retained_count = 0;

			// Get submit records to be deleted
			$sql = $wpdb->prepare(

				"SELECT {$this->table_name}.id FROM {$this->table_name_meta} LEFT OUTER JOIN {$this->table_name} ON {$this->table_name}.id = {$this->table_name_meta}.parent_id WHERE (LOWER({$this->table_name_meta}.meta_value) = %s) AND NOT ({$this->table_name}.id IS NULL);",
				strtolower($email_address)
			);

			$submissions = $wpdb->get_results($sql);

			// Process results
			if($submissions) {

				$items_retained_count = count($submissions);

				if($items_retained_count > 0) {

					// Get first record (Delete one record each time eraser is requested to avoid timeouts)
					if(isset($submissions[0]->id)) {

						// Delete submit record with permanent delete
						$this->id = $submissions[0]->id;
						self::db_delete(true);

						$items_retained_count--;
						$items_removed_count++;
					}
				}
			}

			// Build return values
			$items_removed = ($items_removed_count > 0);
			$items_retained = ($items_retained_count > 0);
			$done = ($items_retained_count <= 0);
			$messages = (($items_removed_count > 0) && ($items_retained_count <= 0)) ? array(sprintf(

				/* translators: %s = WS Form */
				__('%s submissions successfully deleted.', 'ws-form'),

				WS_FORM_NAME_GENERIC
			
			)) : array();

			// Return
			return array(

				'items_removed' => $items_removed_count,
				'items_retained' => $items_retained_count,
				'messages' => $messages,
				'done' => $done,
			);
		}

		// Delete expired
		public function db_delete_expired($count_update_all = true, $bypass_user_capability_check = false) {

			global $wpdb;

			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = 'trash' WHERE (NOT date_expire IS NULL) AND (NOT date_expire = '0000-00-00 00:00:00') AND (NOT status = 'trash') AND (date_expire < %s)",
				WS_Form_Common::get_mysql_date()
			);

			$rows_affected = $wpdb->query($sql);

			// Update form submit unread count statistic
			if($count_update_all) {

				$ws_form_form = new WS_Form_Form();
				$ws_form_form->db_count_update_all($bypass_user_capability_check);
			}

			return $rows_affected;
		}

		// Get submission count by status
		public function db_get_count_by_status($form_id = 0, $status = '') {

			// User capability check
			WS_Form_Common::user_must('read_submission');

			if($form_id == 0) { return 0; }

			global $wpdb;

			// Check status
			$status = WS_Form_Common::check_submit_status($status);

			if($status == '') {

				$sql = $wpdb->prepare(

					"SELECT COUNT(id) FROM {$this->table_name} WHERE NOT(status = 'trash' OR status = 'spam') AND form_id = %d;",
					$form_id
				);

			} else {

				$sql = $wpdb->prepare(

					"SELECT COUNT(id) FROM {$this->table_name} WHERE status = %s AND form_id = %d",
					$status,
					$form_id
				);
			}

			$form_count = $wpdb->get_var($sql);
			if(is_null($form_count)) { $form_count = 0; }

			return $form_count; 
		}

		// Get submit meta
		public function db_get_submit_meta($submit_object, $meta_array = false, $bypass_user_capability_check = false) {

			// No capabilities required, this is a public method
			$submit_meta = array();

			// Get submit record ID
			$submit_id = $submit_object->id;
			$submit_encrypted = isset($submit_object->encrypted) ? $submit_object->encrypted : false;

			// Read meta
			if(!is_array($meta_array)) {

				$ws_form_submit_meta = New WS_Form_Submit_Meta();
				$ws_form_submit_meta->parent_id = $submit_id;
				$meta_array = $ws_form_submit_meta->db_read_all($bypass_user_capability_check, $submit_encrypted);
			}

			// Process meta data
			foreach($meta_array as $index => $meta) {

				// Get field value
				$value = is_serialized($meta['meta_value']) ? unserialize($meta['meta_value']) : $meta['meta_value'];

				// Get field ID
				$field_id = absint($meta['field_id']);

				// If field ID found, process and return as array including type
				if($field_id > 0) {

					// Load field data to cache
					if(isset($this->field_object_cache[$field_id])) {

						// Use cached version
						$field_object = $this->field_object_cache[$field_id];

					} else {

						// Read field data and get type
						$ws_form_field = New WS_Form_Field();
						$ws_form_field->id = $field_id;
						$field_object = $ws_form_field->db_read(true, $bypass_user_capability_check);
						$this->field_object_cache[$field_id] = $field_object;
					}

					// If field no longer exists, just return the value
					if($field_object === false) {

						$submit_meta[$meta['meta_key']] = $value;
						continue;
					}

					// Get field type
					$field_type = $field_object->type;

					// If field type not known, skip
					if($this->field_types === false) { $this->field_types = WS_Form_Config::get_field_types_flat(); }
					if(!isset($this->field_types[$field_type])) { continue; };
					$field_type_config = $this->field_types[$field_type];

					// Legacy date format support
					if(
						($field_type === 'datetime') &&
						is_array($value) &&
						isset($value['mysql'])
					) {
						$value = $value['mysql'];
					}

					// Submit array
					$field_submit_array = (isset($field_type_config['submit_array'])) ? $field_type_config['submit_array'] : false; 

					// Build meta key
					$meta_key = is_null($meta['meta_key']) ? (WS_FORM_FIELD_PREFIX . $field_id) : $meta['meta_key'];

					// Check for repeater
					$repeatable_index = (
						isset($meta['repeatable_index']) &&
						(absint($meta['repeatable_index']) > 0)
					) ? absint($meta['repeatable_index']) : false;

					// Check for section_id
					$section_id = (
						isset($meta['section_id']) &&
						(absint($meta['section_id']) > 0)
					) ? absint($meta['section_id']) : false;

					// Check for repeatable_delimiter_section
					$section_repeatable_section_string = 'section_' . $section_id;
					$section_repeatable_delimiter_section = (
						isset($this->section_repeatable[$section_repeatable_section_string]) &&
						isset($this->section_repeatable[$section_repeatable_section_string]['delimiter_section'])
					) ? $this->section_repeatable[$section_repeatable_section_string]['delimiter_section'] : WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION;

					// Check for repeatable_delimiter_row
					$section_repeatable_delimiter_row = (
						isset($this->section_repeatable[$section_repeatable_section_string]) &&
						isset($this->section_repeatable[$section_repeatable_section_string]['delimiter_row'])
					) ? $this->section_repeatable[$section_repeatable_section_string]['delimiter_row'] : WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW;

					// Build meta data
					$meta_data = array('id' => $field_id, 'value' => $value, 'type' => $field_type, 'section_id' => $section_id, 'repeatable_index' => $repeatable_index);

					// Add to submit meta
					$submit_meta[$meta_key] = $meta_data;

					// Build fallback value
					if($repeatable_index !== false) {

						$meta_key_base = WS_FORM_FIELD_PREFIX . $field_id;

						$submit_meta_not_set = !isset($submit_meta[$meta_key_base]);

						if($submit_meta_not_set) {

							$submit_meta[$meta_key_base] = $meta_data;
						}

						$submit_meta[$meta_key_base]['db_ignore'] = true;
						$submit_meta[$meta_key_base]['repeatable_index'] = false;

						switch($field_type) {

							// Arrays
							case 'file' :
							case 'signature' :
							case 'googlemap' :

								if(!is_array($value)) { $value = array(); }

								if($submit_meta_not_set) {

									$submit_meta[$meta_key_base]['value'] = $value;

								} else {

									foreach($value as $file) {

										$submit_meta[$meta_key_base]['value'][] = $file;
									}
								}
								break;

							// Strings
							default :

								if($submit_meta_not_set) {

									$submit_meta[$meta_key_base]['value'] = self::field_value_stringify($field_object, $submit_meta[$meta_key_base]['value'], $field_submit_array, $section_repeatable_delimiter_row);

								} else {

									$submit_meta[$meta_key_base]['value'] .= $section_repeatable_delimiter_section . self::field_value_stringify($field_object, $value, $field_submit_array, $section_repeatable_delimiter_row);
								}
						}

						// Store raw array values
						$submit_array = isset($field_type_config['submit_array']) ? $field_type_config['submit_array'] : false;
						if($submit_array) {

							if(!is_array($value)) { $value = array($value); }

							if(!isset($submit_meta[$meta_key_base]['value_array'])) {

								$submit_meta[$meta_key_base]['value_array'] = $value;

							} else {

								$submit_meta[$meta_key_base]['value_array'] = array_merge($submit_meta[$meta_key_base]['value_array'], $value);
							}
						}
					}

				} else {

					// Return as string
					$submit_meta[$meta['meta_key']] = $value;
				}
			}

			return $submit_meta;
		}

		// Get number for form submissions
		public function db_get_count_submit_cached($bypass_user_capability_check = false) {

			self::db_check_form_id();

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check cache
			if($this->form_count_submit_cache === false) {

				global $wpdb;

				// Build cache
				$this->form_count_submit_cache = array();

				// Get total number of form submissions
				$sql = "SELECT form_id, COUNT(id) AS count_submit FROM {$this->table_name} WHERE NOT (status = 'trash') GROUP BY form_id;";
				$rows = $wpdb->get_results($sql);

				if(is_null($rows)) { return 0; }

				foreach($rows as $row) {

					$this->form_count_submit_cache[absint($row->form_id)] = absint($row->count_submit);
				}
			}

			return isset($this->form_count_submit_cache[$this->form_id]) ? $this->form_count_submit_cache[$this->form_id] : 0;
		}

		// Get number for form submissions unread
		public function db_get_count_submit_unread_cached($bypass_user_capability_check = false) {

			self::db_check_form_id();

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check cache
			if($this->form_count_submit_unread_cache === false) {

				global $wpdb;

				// Build cache
				$this->form_count_submit_unread_cache = array();

				// Get total number of form submissions that are unread
				$sql = "SELECT form_id, COUNT(id) AS count_submit_unread FROM {$this->table_name} WHERE viewed = 0 AND status IN ('publish', 'draft') GROUP BY form_id;";
				$rows = $wpdb->get_results($sql);

				if(is_null($rows)) { return 0; }

				foreach($rows as $row) {

					$this->form_count_submit_unread_cache[absint($row->form_id)] = absint($row->count_submit_unread);
				}
			}

			return isset($this->form_count_submit_unread_cache[$this->form_id]) ? $this->form_count_submit_unread_cache[$this->form_id] : 0;
		}

		// Restore
		public function db_restore($count_update = true) {

			// User capability check
			WS_Form_Common::user_must('delete_submission');

			self::db_set_status('draft', $count_update);
		}

		// Set starred on / off
		public function db_set_starred($starred = true) {

			// User capability check
			WS_Form_Common::user_must('edit_submission');

			self::db_check_id();

			global $wpdb;

			// Build SQL
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET starred = %d WHERE id = %d LIMIT 1;",
				($starred ? 1 : 0),
				$this->id
			);

			// Update submit record
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting starred status.', 'ws-form')); }
		}

		// Set a submit record as viewed
		public function db_set_viewed($viewed = true, $update_count_submit_unread = true) {

			// User capability check
			WS_Form_Common::user_must('read_submission');

			// Check ID
			self::db_check_id();

			global $wpdb;

			// Set viewed true
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET viewed = %d WHERE id = %d LIMIT 1",
				($viewed ? 1 : 0),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating viewed status.', 'ws-form')); }

			// Update form submit unread count statistic
			if($update_count_submit_unread) {

				$ws_form_form = new WS_Form_Form();
				$ws_form_form->id = $this->form_id;
				$ws_form_form->db_update_count_submit_unread();
			}
		}

		// Set status of submit
		public function db_set_status($status, $count_update = true, $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('edit_submission', $bypass_user_capability_check);

			self::db_check_id();

			global $wpdb;

			// Mark As Spam
			switch($status) {

				case 'spam' :

					$sql = $wpdb->prepare(

						"UPDATE {$this->table_name} SET status = %s, spam_level = 100 WHERE id = %d LIMIT 1;",
						$status,
						$this->id
					);

					break;

				case 'not_spam' :

					$status = 'publish';

					$sql = $wpdb->prepare(

						"UPDATE {$this->table_name} SET status = %s, spam_level = 0 WHERE id = %d LIMIT 1;",
						$status,
						$this->id
					);

					break;

				default :

					$sql = $wpdb->prepare(

						"UPDATE {$this->table_name} SET status = %s WHERE id = %d LIMIT 1;",
						$status,
						$this->id
					);
			}

			// Ensure provided submit status is valid
			if(WS_Form_Common::check_submit_status($status) == '') {

				parent::db_throw_error(sprintf(__('Invalid submit status: %s.', 'ws-form'), $status));
			}

			// Update submit record
			if($wpdb->query($sql) === false) {

				parent::db_wpdb_handle_error(__('Error setting submit status.', 'ws-form'));
			}

			// Update form submit unread count statistic
			if($count_update) {

				self::db_check_form_id();

				$ws_form_form = new WS_Form_Form();
				$ws_form_form->id = $this->form_id;
				$ws_form_form->db_count_update(false, $bypass_user_capability_check);
			}

			// Run action
			do_action('wsf_submit_status', $this->id, $status);

			return true;
		}

		// Get submit status name
		public static function db_get_status_name($status) {

			switch($status) {

				case 'draft' : 		return __('In Progress', 'ws-form'); break;
				case 'publish' : 	return __('Submitted', 'ws-form'); break;
				case 'error' : 		return __('Error', 'ws-form'); break;
				case 'spam' : 		return __('Spam', 'ws-form'); break;
				case 'trash' : 		return __('Trash', 'ws-form'); break;
				default :			return $status;
			}
		}

		// Get submit columns
		public function db_get_submit_fields($bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			if(is_array($this->submit_fields)) { return $this->submit_fields; }

			self::db_check_form_id();

			$visible_count = 0;
			$visible_count_max = 5;

			$this->submit_fields = array();

			// Get form object
			$this->preview = true;
			self::db_form_object_read($bypass_user_capability_check);

			// Get fields in single dimension array
			$fields = WS_Form_Common::get_fields_from_form($this->form_object);

			// Excluded field types
			$field_types_excluded = array('textarea');

			foreach($fields as $field) {

				if($this->field_types === false) { $this->field_types = WS_Form_Config::get_field_types_flat(); }
				if(!isset($this->field_types[$field->type])) { continue; }

				// Get field type
				$field_type_config = $this->field_types[$field->type];

				// Skip unlicensed fields
				if(
					isset($field_type_config['pro_required']) &&
					$field_type_config['pro_required']

				) { continue; }

				// Skip fields that are not saved to meta data
				if(!$field_type_config['submit_save']) { continue; }

				// Skip fields containing the word 'gdpr'
				if(strpos(strtolower($field->label), 'gdpr') !== false) { continue; }

				// Determine if field is required
				$required = WS_Form_Common::get_object_meta_value($field, 'required', false);

				// Determine excluded fields
				$excluded = in_array($field->type, $field_types_excluded);

				// Push to this->submit_fields array
				$this->submit_fields[$field->id] = array(

					'label' 	=> $field->label,
					'type'		=> $field->type,
					'required' 	=> $required,
					'excluded'	=> $excluded,
					'hidden'	=> true
				);
			}

			// Go through each submit field and if it is required, mark it as not hidden
			foreach($this->submit_fields as $id => $field) {

				if($visible_count < $visible_count_max) {

					if($field['required'] && !$field['excluded']) {

						$this->submit_fields[$id]['hidden'] = false;
						$visible_count++;
					}

					if($visible_count == $visible_count_max) { break; }
				}
			}

			if($visible_count < $visible_count_max) {

				// Go through each submit field and if it is not required, mark it as not hidden
				foreach($this->submit_fields as $id => $field) {

					if($visible_count < $visible_count_max) {

						if(!$field['required'] && !$field['excluded']) {

							$this->submit_fields[$id]['hidden'] = false;
							$visible_count++;
						}

						if($visible_count == $visible_count_max) { break; }
					}
				}
			}

			return $this->submit_fields;
		}

		public function get_select($join = false) {

			// Get form data
			$select = self::DB_SELECT;

			if(!empty($join)) {

				$select_array = explode(',', $select);

				foreach($select_array as $key => $select) {

					$select_array[$key] = $this->table_name . '.' . $select;
				}

				$select = implode(',', $select_array);
			}

			return $select;
		}

		public function get_search_join() {

			// Get keyword
			$keyword = WS_Form_Common::get_query_var('keyword');

			// Get order by column
			$order_by = WS_Form_Common::get_query_var('orderby', '');

			return self::get_join($keyword, $order_by, false);
		}

		public function get_join($keyword = '', $order_by = '', $bypass_user_capability_check = false) {

			global $wpdb;

			// Sanitize inputs
			$keyword = sanitize_text_field(trim($keyword));
			$order_by = sanitize_text_field(trim($order_by));

			$join = array();

			// Process keyword
			if($keyword != '') {

				// Get submit fields so we only search current form fields (Excluding old submit meta data)
				self::db_get_submit_fields($bypass_user_capability_check);

				// Keyword search if fields exists
				if(count($this->submit_fields) > 0) {

					$join[] = sprintf(

						'RIGHT JOIN %1$ssubmit_meta smk ON (smk.parent_id = %1$ssubmit.id) AND (smk.field_id IN (%3$s) AND (smk.meta_value LIKE \'%%%2$s%%\'))',
						$wpdb->prefix . WS_FORM_DB_TABLE_PREFIX,
						esc_sql($keyword),
						esc_sql(implode(',', array_keys($this->submit_fields)))
					);
				}
			}

			// Process order by
			if(!empty($order_by)) {

				switch($order_by) {

					case 'id' :
					case 'starred' :
					case 'status' :
					case 'date_added' :
					case 'date_updated' :

						break;

					default :

						$join[] = sprintf('LEFT OUTER JOIN %1$ssubmit_meta ON (%1$ssubmit_meta.parent_id = %1$ssubmit.id) AND (%1$ssubmit_meta.meta_key = \'%2$s\')', $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX, esc_sql($order_by));
				}
			}

			return implode(' ', $join);
		}

		public function get_search_where() {

			return self::get_where(WS_Form_Common::get_admin_submit_filters());
		}

		public function get_where($filters = false, $bypass_user_capability_check = false) {

			global $wpdb;

			// Build WHERE
			$where = sprintf('form_id = %u', $this->form_id);

			if(empty($filters)) {

				// Default status WHERE SQL
				$where .= self::get_where_default_status();

				return $where;
			}

			// Status set
			$status_set = false;

			// Get keys so we can check valid field values
			$keys_fixed = self::get_keys_fixed($bypass_user_capability_check);
			$keys_meta = self::get_keys_meta($bypass_user_capability_check);		// Sets $this->submit_fields;

			// Process filters
			foreach($filters as $filter) {

				// Sanitize filter
				try {

					$filter = self::filter_sanitize($filter, $bypass_user_capability_check);

				} catch (Exception $e) {

					self::db_throw_error($e->getMessage());
				}

				// Read filter
				$field = $filter['field'];
				$operator = $filter['operator'];
				$value = $filter['value'];

				// Specific field / value combinations
				switch($field) {

					case 'status' :

						if($status_set) { continue 2; }

						// Set status set
						$status_set = true;

						// Process Status
						if($value == 'all') {

							// Default status WHERE SQL
							$where .= self::get_where_default_status();

							continue 2;
						};

						break;
				}

				// Process fixed fields
				if(isset($keys_fixed[$field])) {

					// Build SQL
					switch($operator) {

						case '=' :
						case '==' :
						case '>' :
						case '>=' :
						case '<' :
						case '<=' :

							if($operator == '==') { $operator = '='; }

							$where .= sprintf(

								' AND %s %s "%s"',
								esc_sql($field),
								$operator,
								esc_sql($value)
							);

							break;

						case '!=' :

							$where .= sprintf(

								' AND NOT(%s = "%s")',
								esc_sql($field),
								esc_sql($value)
							);

							break;

						case 'in' :

							$where .= sprintf(

								' AND %s IN(%s)',
								esc_sql($field),
								$value                  // Already escaped by filter_sanitize method
							);

							break;

						case 'not_in' :

							$where .= sprintf(

								' AND %s NOT IN(%s)',
								esc_sql($field),
								$value                  // Already escaped by filter_sanitize method
							);

							break;
					}

					continue;
				}

				// Meta data
				if(isset($keys_meta[$field])) {

					// Get field type
					$field_type = $this->submit_fields[$field]['type'];

					// Build SQL
					switch($operator) {

						case '=' :
						case '==' :
						case '>' :
						case '>=' :
						case '<' :
						case '<=' :

							if($operator = '==') { $operator = '='; }

							$where .= sprintf(

								' AND (NOT (SELECT mks.id FROM %1$ssubmit_meta mks WHERE mks.parent_id =  %1$ssubmit.id AND mks.meta_key = "%2$s" AND mks.meta_value %4$s "%3$s" LIMIT 1) IS NULL)',
								$wpdb->prefix . WS_FORM_DB_TABLE_PREFIX,
								esc_sql($field),
								esc_sql($value),
								$operator
							);

							break;

						case '!=' :

							$where .= sprintf(

								' AND (NOT (SELECT mks.id FROM %1$ssubmit_meta mks WHERE mks.parent_id =  %1$ssubmit.id AND mks.meta_key = "%2$s" AND NOT(mks.meta_value = "%3$s") LIMIT 1) IS NULL)',
								$wpdb->prefix . WS_FORM_DB_TABLE_PREFIX,
								esc_sql($field),
								esc_sql($value)
							);

							break;

						case 'in' :

							$where .= sprintf(

								' AND (NOT (SELECT mks.id FROM %1$ssubmit_meta mks WHERE mks.parent_id =  %1$ssubmit.id AND mks.meta_key = "%2$s" AND mks.meta_value IN(%3$s) LIMIT 1) IS NULL)',
								$wpdb->prefix . WS_FORM_DB_TABLE_PREFIX,
								esc_sql($field),
								$value             // Already escaped by filter_sanitize method
							);

							break;

						case 'not_in' :

							$where .= sprintf(

								' AND (NOT (SELECT mks.id FROM %1$ssubmit_meta mks WHERE mks.parent_id =  %1$ssubmit.id AND mks.meta_key = "%2$s" AND mks.meta_value NOT IN(%3$s) LIMIT 1) IS NULL)',
								$wpdb->prefix . WS_FORM_DB_TABLE_PREFIX,
								esc_sql($field),
								$value             // Already escaped by filter_sanitize method
							);

							break;
					}
				}
			}

			// If status has not been set, add default status WHERE SQL
			if(!$status_set) { $where .= self::get_where_default_status(); }

			return $where;
		}

		public function get_where_default_status() {

			return " AND NOT(status = 'trash' OR status = 'spam')";
		}

		public function filter_sanitize($filter, $bypass_user_capability_check) {

			// Get field
			if(!isset($filter['field'])) { self::db_throw_error(__('No filter field specified.', 'ws-form')); }
			$filter['field'] = sanitize_text_field($filter['field']);

			// Convert date_from field
			if($filter['field'] == 'date_from') {

				$filter['field'] = 'date_added';
				$filter['operator'] = '>=';
				$filter['value'] = WS_Form_Common::get_mysql_date(get_gmt_from_date(WS_Form_Common::get_date_by_site(WS_Form_Common::field_date_translate($filter['value'])) . ' 00:00:00'));
			}

			// Convert date_to field
			if($filter['field'] == 'date_to') {

				$filter['field'] = 'date_added';
				$filter['operator'] = '<=';
				$filter['value'] = WS_Form_Common::get_mysql_date(get_gmt_from_date(WS_Form_Common::get_date_by_site(WS_Form_Common::field_date_translate($filter['value'])) . ' 23:59:59'));
			}

			// Check field
			$keys_all = self::get_keys_all($bypass_user_capability_check);
			if(!isset($keys_all[$filter['field']])) { self::db_throw_error(__('Invalid filter field specified.', 'ws-form')); }

			// Get value
			if(!isset($filter['value'])) { self::db_throw_error(__('No filter value specified.', 'ws-form')); }

			// Check value
			if($filter['value'] == '') { self::db_throw_error(__('Empty filter value specified.', 'ws-form')); }
			if(
				!is_string($filter['value']) &&
				!is_numeric($filter['value']) &&
				!is_array($filter['value'])
			) {
				self::db_throw_error(__('Invalid value variable type.', 'ws-form'));
			}

			// Check operator
			$filter['operator'] = strtolower(isset($filter['operator']) ? $filter['operator'] : '==');
			if(!in_array($filter['operator'], array('==', '!=', '>', '>=', '<', '<=', 'in', 'not_in'))) {

				self::db_throw_error(__('Invalid operator specified.', 'ws-form'));
			}

			// Specific field checks
			switch($filter['field']) {

				case 'status' :

					if(WS_Form_Common::check_submit_status($filter['value']) == '') { $filter['value'] = 'all'; }
					break;
			}

			// Operator checks
			switch($filter['operator']) {

				case 'in' :
				case 'not_in' :

					if(!is_array($filter['value'])) { self::db_throw_error(__('Value should be an array.', 'ws-form')); }

					// Process and escape each value
					$value_array_escaped = array();

					foreach($filter['value'] as $value_single) {

						switch(gettype($value_single)) {

							case 'string' :

								$value_array_escaped[] = sprintf('"%s"', esc_sql($value_single));
								break;

							case 'integer' :

								$value_array_escaped[] = esc_sql(intval($value_single));
								break;

							case 'double' :

								$value_array_escaped[] = esc_sql(floatval($value_single));
								break;

							default :

								self::db_throw_error(__('Invalid value variable type.', 'ws-form'));
						}
					}

					$filter['value'] = implode(',', $value_array_escaped);

					break;

				default :

					if(is_array($filter['value'])) { self::db_throw_error(__('Value should not be an array.', 'ws-form')); }

					$filter['value'] = sanitize_text_field($filter['value']);
			}

			return $filter;
		}

		public function get_search_group_by() {

			return self::get_group_by();
		}

		public function get_group_by() {

			// Build GROUP BY SQL
			return sprintf(

				'%s.id',
				$this->table_name
			);
		}

		public function get_search_order_by() {

			// Get order by
			$order_by = WS_Form_Common::get_query_var('orderby');

			// Get order
			$order = WS_Form_Common::get_query_var('order');

			return self::get_order_by($order_by, $order, false);
		}

		public function get_order_by($order_by = 'id', $order = 'DESC', $bypass_user_capability_check = false) {

			global $wpdb;

			// Sanitize inputs
			$order = strtolower(sanitize_text_field(trim($order)));
			$order_by = strtolower(sanitize_text_field(trim($order_by)));

			// Default
			$order_by_return = 'id DESC';

			// Process order by
			if(!empty($order_by)) {

				// Process order
				$order = (!empty($order) && ($order == 'desc')) ? ' DESC' : ' ASC';

				switch($order_by) {

					case 'id' :
					case 'starred' :
					case 'status' :
					case 'date_added' :
					case 'date_updated' :

						$order_by_return = esc_sql($order_by) . $order;
						break;

					default :

						// Get field ID
						$field_id = absint(str_replace(WS_FORM_FIELD_PREFIX, '', $order_by));
						if($field_id == 0) { return $order_by; }

						// Read field object
						try {

							$ws_form_field = new WS_Form_Field();
							$ws_form_field->id = $field_id;
							$field_obj = $ws_form_field->db_read(true, $bypass_user_capability_check);
							if(empty($field_obj)) { return $order_by_return; }

						} catch (Exception $e) {

    						return $order_by_return;
						}

						// Process by field object type
						if($field_obj && property_exists($field_obj, 'type')) {

							switch($field_obj->type) {

								case 'select' :
								case 'checkbox' :
								case 'radio' :

									// Select, checkbox, radio
									$order_by_meta_value = sprintf('(TRIM(BOTH \'"\' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(%1$ssubmit_meta.meta_value,\';\',2),\':\',-1)))', $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX);
									break;

								case 'datetime' :

									// Date
									$format_date = WS_Form_Common::get_object_meta_value($field_obj, 'format_date', get_option('date_format'));
									if(empty($format_date)) { $format_date = get_option('date_format'); }
									$format_date = WS_Form_Common::php_to_mysql_date_format($format_date);

									$format_time = WS_Form_Common::get_object_meta_value($field_obj, 'format_time', get_option('time_format'));
									if(empty($format_time)) { $format_time = get_option('time_format'); }
									$format_time = WS_Form_Common::php_to_mysql_date_format($format_time);

									$input_type_datetime = WS_Form_Common::get_object_meta_value($field_obj, 'input_type_datetime', 'date');

									switch($input_type_datetime) {

										case 'date' :

											$format_string = $format_date;
											break;

										case 'month' :

											$format_string = '%M %Y';
											break;

										case 'time' :

											$format_string = $format_time;
											break;

										case 'week' :

											$format_string = __('Week', 'ws-form') . '%u, %Y';
											break;

										default :

											$format_string = $format_date . ' ' . $format_time;
									}

									$order_by_meta_value = sprintf("STR_TO_DATE(%ssubmit_meta.meta_value, '%s')", $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX, $format_string);
									break;

								case 'price' :

									// Price
									$order_by_meta_value = '(SUBSTRING(' . $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX . 'submit_meta.meta_value, 2) * 1)';
									break;

								case 'number' :
								case 'range' :

									// Number
									$order_by_meta_value = '(' . $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX . 'submit_meta.meta_value * 1)';
									break;

								default :

									// Default
									$order_by_meta_value = $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX . 'submit_meta.meta_value';
							}

							$order_by_return = $order_by_meta_value . $order;
						}
				}
			}

			return $order_by_return;
		}

		// Get keys - All
		public function get_keys_all($bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check cache
			if($this->keys !== false) { return $this->keys; }

			// Add keys - Fixed
			$this->keys = self::get_keys_fixed($bypass_user_capability_check);

			// Add keys - Fields
			$this->keys = array_merge($this->keys, self::get_keys_fields($bypass_user_capability_check));

			// Add keys - E-commerce
			$this->keys = array_merge($this->keys, self::get_keys_ecommerce($bypass_user_capability_check));

			// Add keys -> Tracking
			$this->keys = array_merge($this->keys, self::get_keys_tracking($bypass_user_capability_check));

			return $this->keys;
		}

		// Get keys - Meta
		public function get_keys_meta($bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check cache
			if($this->keys_meta !== false) { return $this->keys_meta; }

			// Add keys - Fields
			$this->keys_meta = self::get_keys_fields($bypass_user_capability_check);

			// Add keys - E-commerce
			$this->keys_meta = array_merge($this->keys_meta, self::get_keys_ecommerce($bypass_user_capability_check));

			// Add keys -> Tracking
			$this->keys_meta = array_merge($this->keys_meta, self::get_keys_tracking($bypass_user_capability_check));

			return $this->keys_meta;
		}

		// Get keys - Fixed
		public function get_keys_fixed($bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check cache
			if($this->keys_fixed !== false) { return $this->keys_fixed; }

			// Build fixed keys fields
			$this->keys_fixed = array(

				'id' => 'Submission ID',
				'status' => 'Status',
				'status_full' => 'Status Full',
				'date_added' => 'Date Added',
				'date_updated' => 'Date Updated',
				'user_id' => 'User ID',
				'user_first_name' => 'User First Name',
				'user_last_name' => 'User Last Name',
				'duration' => 'Duration (Seconds)'
			);

			return $this->keys_fixed;
		}

		// Get keys - Fields
		public function get_keys_fields($bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check cache
			if($this->keys_fields !== false) { return $this->keys_fields; }

			// Get field data
			$submit_fields = self::db_get_submit_fields($bypass_user_capability_check);

			// Build keys
			$this->keys_fields = array();

			// Form fields
			if(is_array($submit_fields)) {

				foreach($submit_fields as $id => $submit_field) {

					$this->keys_fields[sprintf('field_%u', $id)] = $submit_field['label'];
				}
			}

			return $this->keys_fields;
		}

		// Get e-commerce keys
		public function get_keys_ecommerce($bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check cache
			if($this->keys_ecommerce !== false) { return $this->keys_ecommerce; }

			$this->keys_ecommerce = array();

			// E-Commerce
			$ecommerce = WS_Form_Config::get_ecommerce(false);

			// Builder keys columns according to priority
			$keys_ecommerce = array();
			foreach($ecommerce['cart_price_types'] as $key => $ecommerce_config) {

				$priority = isset($ecommerce_config['priority']) ? $ecommerce_config['priority'] : 10000;
				$keys_ecommerce[$key] = array('key' => 'ecommerce_cart_' . $key, 'label' => $ecommerce_config['label'], 'priority' => $priority);
			}
			foreach($ecommerce['meta_keys'] as $key => $ecommerce_config) {

				$priority = isset($ecommerce_config['priority']) ? $ecommerce_config['priority'] : 10000;
				$keys_ecommerce[$key] = array('key' => $key, 'label' => $ecommerce_config['label'], 'priority' => $priority);
			}
			uasort($keys_ecommerce, function ($keys_ecommerce_1, $keys_ecommerce_2) {

				return ($keys_ecommerce_1['priority'] == $keys_ecommerce_2['priority']) ? 0 : (($keys_ecommerce_1['priority'] < $keys_ecommerce_2['priority']) ? -1 : 1);
			});
			foreach($keys_ecommerce as $keys_ecommerce_single) {

				$this->keys_ecommerce[$keys_ecommerce_single['key']] = $keys_ecommerce_single['label'];
			}

			return $this->keys_ecommerce;
		}

		// Get keys - Tracking
		public function get_keys_tracking($bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Check cache
			if($this->keys_tracking !== false) { return $this->keys_tracking; }

			$this->keys_tracking = array();

			// Tracking
			$tracking = WS_Form_Config::get_tracking(false);
			foreach($tracking as $key => $tracking_config) {

				$this->keys_tracking[$key] = $tracking_config['label'];
			}

			return $this->keys_tracking;
		}

		// Setup from post
		public function setup_from_post($process_file_fields = true, $process_non_file_fields = true) {

			// No capabilities required, this is a public method

			// Get form ID
			$this->form_id = absint(WS_Form_Common::get_query_var_nonce('wsf_form_id', 0));

			// If form ID is not specified then we should stop processing
			if($this->form_id === 0) { exit; }

			// Get hash
			$this->hash = WS_Form_Common::get_query_var_nonce('wsf_hash', '');

			// If hash found, look for form submission
			if(
				($this->hash != '') &&
				WS_Form_Common::check_submit_hash($this->hash)
			) {
				try {

					// Read submit by hash
					$this->db_read_by_hash(true, true, true, true);

					// Reset spam level
					$this->spam_level = null;

					// Clear meta data
					$submit_clear_meta_filter_keys = apply_filters('wsf_submit_clear_meta_filter_keys', array());
					foreach($this->meta as $key => $value) {

						if(!in_array($key, $submit_clear_meta_filter_keys)) {

							unset($this->meta[$key]);
						}
					}
					$this->meta_protected = array();

				} catch(Exception $e) {

					$this->hash = '';
				}
			}

			if($this->hash == '') {

				// Create fresh hash for this submission
				$this->db_create_hash();
			}

			// Preview submit?
			$this->preview = (WS_Form_Common::get_query_var_nonce('wsf_preview', false) !== false);

			// Read form
			self::db_form_object_read();

			// Apply restrictions (Removes any groups, sections or fields that are hidden due to restriction settings, e.g. User logged in)
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->apply_restrictions($this->form_object);

			// Apply limits
			if(!$this->preview) {

				$check_limit_response = $ws_form_form->apply_limits($this->form_object);
				if($check_limit_response !== false) {

					parent::db_throw_error($check_limit_response);
				}
			}
			// Do not validate fields that are required bypassed
			$bypass_required = WS_Form_Common::get_query_var_nonce('wsf_bypass_required', '');
			$this->bypass_required_array = explode(',', $bypass_required);

			// Process hidden fields
			$hidden = WS_Form_Common::get_query_var_nonce('wsf_hidden', '');
			$this->hidden_array = explode(',', $hidden);
			if(count($this->hidden_array) > 0) {

				$this->meta['wsf_meta_key_hidden'] = array();
			}

			// Spam protection - Honeypot
			$honeypot_hash = ($this->form_object->published_checksum != '') ? $this->form_object->published_checksum : 'honeypot_unpublished_' . $this->form_id;
			$honeypot_value = WS_Form_Common::get_query_var_nonce("field_$honeypot_hash");
			if($honeypot_value != '') { parent::db_throw_error(__('Spam protection error.', 'ws-form')); }

			// Get sections array
			$sections = WS_Form_Common::get_sections_from_form($this->form_object);

			// Are we submitting the form or just saving it?
			$this->post_mode = WS_Form_Common::get_query_var_nonce('wsf_post_mode', false);
			$form_submit = ($this->post_mode == 'submit');

			// Ensure post mode is valid
			if(!in_array($this->post_mode, array('submit', 'save', 'action'))) { parent::db_throw_error(__('Invalid post mode.', 'ws-form')); }

			// Build section_repeatable
			$section_repeatable = array();
			$wsf_form_section_repeatable_index_json = WS_Form_Common::get_query_var_nonce('wsf_form_section_repeatable_index', false);
			if(!empty($wsf_form_section_repeatable_index_json)) {

				if(is_null($wsf_form_section_repeatable_index = (array) json_decode($wsf_form_section_repeatable_index_json))) {

					parent::db_throw_error(__('Malformed wsf_form_section_repeatable_index JSON value.', 'ws-form'));
				}

				// Save wsf_form_section_repeatable_index to section_repeatable and parse it to ensure the data is valid
				foreach($wsf_form_section_repeatable_index as $section_id_string => $indexes) {

					$section_repeatable[$section_id_string] = array('index' => array());

					foreach($indexes as $index) {

						if(absint($index) === 0) { continue; }

						$section_repeatable[$section_id_string]['index'][] = absint($index);
					}
				}
			}

			// Process each section
			foreach($sections as $section_id => $section) {

				if($section->repeatable) {

					$section_id_string = 'section_' . $section_id;

					// Get repeatable indexes for that section
					if(
						!isset($section_repeatable[$section_id_string]) ||
						!isset($section_repeatable[$section_id_string]['index'])
					) {

						parent::db_throw_error(__('Repeatable data error. Section ID not found in wsf_form_section_repeatable_index.', 'ws-form'));
					}

					$section_repeatable_indexes = $section_repeatable[$section_id_string]['index'];

					foreach($section_repeatable_indexes as $section_repeatable_index) {

						self::setup_from_post_section($section, $form_submit, $process_file_fields, $process_non_file_fields, $section_id, $section_repeatable_index, $section_repeatable);
					}

				} else {

					self::setup_from_post_section($section, $form_submit, $process_file_fields, $process_non_file_fields);
				}
			}

			// Apply wsf_submit_validate hook
			$this->error_validation_actions = self::filter_validate(

				$this->error_validation_actions,

				apply_filters('wsf_submit_validate', $this->error_validation_actions, $this->post_mode, $this)
			);

			// Section repeatable
			if(!empty($section_repeatable)) {

				$this->section_repeatable = serialize($section_repeatable);
			}

			// Post ID
			$post_id = absint(WS_Form_Common::get_query_var_nonce('wsf_post_id', 0));
			if($post_id > 0) {

				$this->meta['post_id'] = $post_id;
			}
			// Tracking
			$tracking_array = WS_Form_Config::get_tracking(false);
			foreach($tracking_array as $meta_key => $tracking) {

				// Check meta_key is enabled
				if(WS_Form_Common::get_object_meta_value($this->form_object, $meta_key, false)) {

					// Get value
					switch($tracking['server_source']) {

						case 'http_env' :

							$meta_value = isset($tracking['server_http_env']) ? WS_Form_Common::get_http_env_raw($tracking['server_http_env']) : '';
							break;

						case 'query_var' :

							$meta_value = isset($tracking['server_query_var']) ? WS_Form_Common::get_query_var_nonce($tracking['server_query_var']) : '';
							break;

						case 'ip_lookup' :

							// IP lookup by ip_lookup_method
							$ip_lookup_method = WS_Form_Common::option_get('ip_lookup_method');

							switch($ip_lookup_method) {

								case 'ipinfo' :

									$meta_value = isset($tracking['ipinfo_var']) ? WS_Form_Common::get_ip_lookup($tracking['ipinfo_var'], 'ipinfo') : '';
									break;

								case 'ipapi' :

									$meta_value = isset($tracking['ipapi_var']) ? WS_Form_Common::get_ip_lookup($tracking['ipapi_var'], 'ipapi') : '';
									break;

								case 'ipapico' :

									$meta_value = isset($tracking['ipapico_var']) ? WS_Form_Common::get_ip_lookup($tracking['ipapico_var'], 'ipapico') : '';
									break;

								default :

									$meta_value = isset($tracking['geoplugin_var']) ? WS_Form_Common::get_ip_lookup($tracking['geoplugin_var']) : '';
							}

							break;

						default :

							$meta_value = '';
					}

					// Add to submit meta
					$this->meta[$meta_key] = $meta_value;
				}
			}

			// E-Commerce
			if(WS_Form_Common::get_query_var_nonce('wsf_ecommerce_cart') != '') {

				// Check for e-commerce fields
				$ecommerce_cart = WS_Form_Common::get_query_var_nonce('wsf_ecommerce_cart');
				if(

					is_array($ecommerce_cart) &&
					(count($ecommerce_cart) > 0)
				) {

					foreach($ecommerce_cart as $price_type => $meta_value) {

						// Add to submit meta
						if(floatVal($meta_value <> 0)) {

							$this->meta['ecommerce_cart_' . $price_type] = $meta_value;
						}
					}
				}

				// E-Commerce transaction ID
				$this->meta['ecommerce_transaction_id'] = WS_Form_Common::get_query_var_nonce('wsf_ecommerce_transaction_id');

				// E-Commerce transaction (JSON)
				$this->meta['ecommerce_transaction'] = WS_Form_Common::get_query_var_nonce('wsf_ecommerce_transaction');

				// E-Commerce status
				$this->meta['ecommerce_status'] = WS_Form_Common::get_query_var_nonce('wsf_ecommerce_status');

				// E-Commerce payment method
				$this->meta['ecommerce_payment_method'] = WS_Form_Common::get_query_var_nonce('wsf_ecommerce_payment_method');
			}
		}

		public function setup_from_post_section($section, $form_submit, $process_file_fields = true, $process_non_file_fields = true, $section_id = false, $section_repeatable_index = false, &$section_repeatable = array()) {

			// Delimiters
			if($section_repeatable_index !== false) {

				// Get delimiters
				$section_repeatable_delimiter_section = WS_Form_Common::get_object_meta_value($section, 'section_repeatable_delimiter_section', WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION);
				if($section_repeatable_delimiter_section == '') { $section_repeatable_delimiter_section = WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION; }
				$section_repeatable_delimiter_row = WS_Form_Common::get_object_meta_value($section, 'section_repeatable_delimiter_row', WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW);
				if($section_repeatable_delimiter_row == '') { $section_repeatable_delimiter_row = WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW; }

				// Add delimiters to section_repeatable
				if(!isset($section_repeatable['section_' . $section_id])) { $section_repeatable['section_' . $section_id] = array(); }
				$section_repeatable['section_' . $section_id]['delimiter_section'] = $section_repeatable_delimiter_section;
				$section_repeatable['section_' . $section_id]['delimiter_row'] = $section_repeatable_delimiter_row;
			}

			// File field types
			$field_type_files = array('file', 'signature');

			// Process each field
			$section_fields = $section->fields;
			foreach($section_fields as $field) {

				// If field type not specified, skip
				if(!isset($field->type)) { continue; };
				$field_type = $field->type;

				// File processing?
				if(!$process_non_file_fields && !in_array($field_type, $field_type_files)) { continue; }
				if(!$process_file_fields && in_array($field_type, $field_type_files)) { continue; }

				// If field type not known, skip
				if($this->field_types === false) { $this->field_types = WS_Form_Config::get_field_types_flat(); }
				if(!isset($this->field_types[$field_type])) { continue; };
				$field_type_config = $this->field_types[$field_type];

				// Remove layout editor only fields
				$layout_editor_only = isset($field_type_config['layout_editor_only']) ? $field_type_config['layout_editor_only'] : false;
				if($layout_editor_only) { continue; }

				// Submit array
				$submit_array = isset($field_type_config['submit_array']) ? $field_type_config['submit_array'] : false;

				// If field is not licensed, skip
				if(
					isset($field_type_config['pro_required']) &&
					$field_type_config['pro_required']

				) { continue; }

				// Submit array
				$field_submit_array = (isset($field_type_config['submit_array'])) ? $field_type_config['submit_array'] : false; 

				// Is field in a repeatable section?
				$field_section_repeatable = isset($field->section_repeatable) && $field->section_repeatable;

				// Save meta data
				if(!isset($field->id)) { continue; }
				$field_id = absint($field->id);

				// Build field name
				$field_name = $field_name_post = $meta_key_hidden = WS_FORM_FIELD_PREFIX . $field_id;

				// Field value
				$field_value = WS_Form_Common::get_query_var_nonce($field_name);

				if($section_repeatable_index !== false) {

					$field_value = isset($field_value[$section_repeatable_index]) ? $field_value[$section_repeatable_index] : '';
					$field_name_post = sprintf('%s[%u]', $field_name, $section_repeatable_index);
					$meta_key_hidden = sprintf('%s_%u', $field_name, $section_repeatable_index);
				}

				// Field bypassed
				$field_bypassed = in_array($field_name_post, $this->bypass_required_array);

				// Field required
				$field_required = WS_Form_Common::get_object_meta_value($field, 'required', false) && !$field_bypassed;

				// Process according to field type
				switch($field_type) {

					case 'email' :

						// Sanitize email address
						$email = sanitize_email($field_value);

						if(
							($email !== '') &&
							(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
						) {

							$email_validate = apply_filters('wsf_action_email_email_validate', true, $email, $this->form_object->id, $field_id);

							if(is_string($email_validate)) {

								self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $email_validate);
							}

							if($email_validate === false) {

								self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, __('Invalid email address.', 'ws-form'));
							}
						}

						break;

					case 'file' :

						// Check if field is configured to use DropzoneJS
						$sub_type = WS_Form_Common::get_object_meta_value($field, 'sub_type', false);

						switch($sub_type) {

							case 'dropzonejs' :

								// Get file objects from submitted UUIDs and attachment IDs
								$file_objects = self::db_file_process_dropzonejs($field, $field_required, $form_submit, $section_repeatable_index, $field_value);

								break;

							default :

								// Get file objects from $_FILES
								$file_objects = self::db_file_process($field, $field_required, $form_submit, $section_repeatable_index, $field_name);
						}

						if(is_array($file_objects)) {

							$field_value = $file_objects;
							$this->file_objects = array_merge($this->file_objects, $file_objects);
						}

						break;

					case 'signature' :

						// Field file handler type
						$field_file_handler = WS_Form_Common::get_object_meta_value($field, 'file_handler_type', 'wsform');

						$file_objects = self::db_signature_process($field, $section_repeatable_index, $field_value);
						if(is_array($file_objects)) {

							$field_value = $file_objects;
							$this->file_objects = array_merge($this->file_objects, $file_objects);

						} else {

							$field_value = '';
						}

						break;
					case 'recaptcha' :

						// Only process if form is being submitted
						if($form_submit) {

							// Get reCAPTCHA secret
							$recaptcha_secret_key = WS_Form_Common::get_object_meta_value($field, 'recaptcha_secret_key', '');

							// If field setting is blank, check global setting
							if(empty($recaptcha_secret_key)) {

								$recaptcha_secret_key = WS_Form_Common::option_get('recaptcha_secret_key', '');
							}

							// Process reCAPTCHA
							try {

								self::db_captcha_process($field_id, $section_repeatable_index, $recaptcha_secret_key, WS_FORM_RECAPTCHA_ENDPOINT, WS_FORM_RECAPTCHA_QUERY_VAR);

							} catch (Exception $e) {

								self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $e->getMessage());
							}
						}

						break;

					case 'hcaptcha' :

						// Only process if form is being submitted
						if($form_submit) {

							// Get hCaptcha secret
							$hcaptcha_secret_key = WS_Form_Common::get_object_meta_value($field, 'hcaptcha_secret_key', '');

							// If field setting is blank, check global setting
							if(empty($hcaptcha_secret_key)) {

								$hcaptcha_secret_key = WS_Form_Common::option_get('hcaptcha_secret_key', '');
							}

							// Process hCaptcha
							try {

								self::db_captcha_process($field_id, $section_repeatable_index, $hcaptcha_secret_key, WS_FORM_HCAPTCHA_ENDPOINT, WS_FORM_HCAPTCHA_QUERY_VAR);

							} catch (Exception $e) {

								self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $e->getMessage());
							}
						}

						break;

					case 'turnstile' :

						// Only process if form is being submitted
						if($form_submit) {

							// Get Turnstile secret
							$turnstile_secret_key = WS_Form_Common::get_object_meta_value($field, 'turnstile_secret_key', '');

							// If field setting is blank, check global setting
							if(empty($turnstile_secret_key)) {

								$turnstile_secret_key = WS_Form_Common::option_get('turnstile_secret_key', '');
							}

							// Process Turnstile
							try {

								self::db_captcha_process($field_id, $section_repeatable_index, $turnstile_secret_key, WS_FORM_TURNSTILE_ENDPOINT, WS_FORM_TURNSTILE_QUERY_VAR);

							} catch (Exception $e) {

								self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $e->getMessage());
							}
						}

						break;
					case 'price' :
					case 'price_range' :

						$field_value = WS_Form_Common::get_price(WS_Form_Common::get_number($field_value, 0, true));

						break;

					case 'googlemap' :

						$field_value_obj = json_decode($field_value);

						if(is_null($field_value_obj)) {

							// Legacy
							$field_value_lat_lng = explode(',', $field_value);

							if(count($field_value_lat_lng) == 2) {

								$field_value = array(

									'lat' => floatval(isset($field_value_lat_lng[0]) ? $field_value_lat_lng[0] : 0),
									'lng' => floatval(isset($field_value_lat_lng[1]) ? $field_value_lat_lng[1] : 0)
								);

							} else {

								$field_value = '';
							}

						} else {

							$field_value = (array) $field_value_obj;
						}

						break;
				}

				// Handle required fields
				if($form_submit && $field_required && ($field_value == '')) {

					self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, self::field_invalid_feedback($field, $field_type_config));
				}

				// Handle hidden fields
				if(in_array($field_name_post, $this->hidden_array)) {

					$this->meta['wsf_meta_key_hidden'][] = $meta_key_hidden;
				}

				// Deduplication
				if($field_value != '') {

					$field_dedupe = WS_Form_Common::get_object_meta_value($field, 'dedupe', false);
					if($field_dedupe) {

						// Get dedupe period
						$field_dedupe_period = WS_Form_Common::get_object_meta_value($field, 'dedupe_period', false);

						// Check for a dupe
						$ws_form_submit_meta = new WS_Form_Submit_Meta();
						if($ws_form_submit_meta->db_dupe_check($this->form_id, $field_id, $field_value, $field_dedupe_period)) {

							$field_dedupe_message = WS_Form_Common::get_object_meta_value($field, 'dedupe_message', '');
							if($field_dedupe_message == '') {

								$field_dedupe_message = __('The value entered has already been used.', 'ws-form');
							}

							$field_dedupe_message_lookups = array(

								'label_lowercase' 	=> strtolower($field->label),
								'label' 			=> $field->label
							);

							$field_dedupe_message = WS_Form_Common::mask_parse($field_dedupe_message, $field_dedupe_message_lookups);

							self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $field_dedupe_message);
						}
					}
				}

				// Allow / Deny
				$field_allow_deny = WS_Form_Common::get_object_meta_value($field, 'allow_deny', '');
				if(
					($field_allow_deny !== '') &&
					in_array($field_allow_deny, array('allow', 'deny'))
				) {

					$field_value_allowed = ($field_allow_deny === 'deny');

					$field_allow_deny_values = WS_Form_Common::get_object_meta_value($field, 'allow_deny_values', array());

					if(

						is_array($field_allow_deny_values) &&
						(count($field_allow_deny_values) > 0)
					) {

						foreach($field_allow_deny_values as $field_allow_deny_row) {

							$field_allow_deny_value = $field_allow_deny_row->allow_deny_value;

							$field_allow_deny_pattern = str_replace('*', '.*', $field_allow_deny_value);

							$field_allow_deny_result = preg_match(sprintf('/%s/', $field_allow_deny_pattern), $field_value);

							if($field_allow_deny_result) {

								$field_value_allowed = ($field_allow_deny === 'allow');
								break;
							}
						}

						if(!$field_value_allowed) {

							$field_allow_deny_message = WS_Form_Common::get_object_meta_value($field, 'allow_deny_message', '');
							if($field_allow_deny_message == '') {

								$field_allow_deny_message = __('The email address entered is not allowed.', 'ws-form');
							}

							self::db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $field_allow_deny_message);
						}
					}
				}

				// Get submit_save
				$submit_save = isset($field_type_config['submit_save']) ? $field_type_config['submit_save'] : false;

				// Build meta_data
				$meta_data = array('id' => $field_id, 'value' => $field_value, 'type' => $field_type, 'section_id' => $section_id, 'repeatable_index' => $section_repeatable_index);
				$meta_key_suffix = (($section_repeatable_index !== false) ? ('_' . $section_repeatable_index) : '');
				if($submit_save !== false) {

					$meta_field = 'meta';

				} else {

					$meta_field = 'meta_protected';
				}

				// Add to submit meta protected
				$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id . $meta_key_suffix] = $meta_data;

				// Build fallback value
				if($section_repeatable_index !== false) {

					$meta_not_set = !isset($this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]);

					if($meta_not_set) {

						$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id] = $meta_data;

						// We don't store the fallback data to the database, it is just made available to any actions that need it
						$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['db_ignore'] = true;

						// Set repeatable index to false
						$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['repeatable_index'] = false;
					}

					switch($field_type) {

						// Merge
						case 'file' :
						case 'signature' :
						case 'googlemap' :

							if($meta_not_set) {

								$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] = $field_value;

							} else {

								if(is_array($field_value)) {

									$meta_value = $this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'];

									if(!is_array($meta_value)) {

										// Currently a blank string
										$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] = $field_value;

									} else {

										// Currently an array
										$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] = array_merge($field_value, $meta_value);
									}
								}
							}

							break;

						// Other fields
						default :

							if($meta_not_set) {

								$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] = self::field_value_stringify($field, $this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'], $field_submit_array, $section_repeatable_delimiter_row);

							} else {

								$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value'] .= $section_repeatable_delimiter_section . self::field_value_stringify($field, $field_value, $field_submit_array, $section_repeatable_delimiter_row);
							}
					}

					// Store raw array values
					if($submit_array) {

						if(!is_array($field_value)) { $field_value = array($field_value); }

						if($meta_not_set) {

							$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value_array'] = $field_value;

						} else {

							$this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value_array'] = array_merge($this->{$meta_field}[WS_FORM_FIELD_PREFIX . $field_id]['value_array'], $field_value);
						}
					}
				}

				// Check if field is mappable
				if(isset($field_type_config['mappable']) ? $field_type_config['mappable'] : false) {

					// Apply wsf_submit_field_validate filter hook
					$this->error_validation_actions = self::filter_validate(

						$this->error_validation_actions,

						apply_filters('wsf_submit_field_validate', $this->error_validation_actions, $field_id, $field_value, $section_repeatable_index, $this->post_mode, $this),

						$field,

						$field_id,

						$field_type_config,

						$section_repeatable_index
					);
				}
			}
		}

		// Process validation filter
		public function filter_validate($actions_old, $actions_new, $field = false, $field_id = false, $field_type_config = false, $section_repeatable_index = false) {

			// Check returned value, it should be an array
			if(
				($actions_new == $actions_old) ||
				!is_array($actions_new)
			) {
				return $actions_old;
			}

			// Legacy - Check if a single action array has been returned
			if(
				isset($actions_new['action']) ||
				isset($actions_new['field_id']) ||
				isset($actions_new['section_repeatable_index']) ||
				isset($actions_new['message'])
			) {

				// Wrap in array to make it match the new format
				$actions_diff = array($actions_new);

			} else {

				// Get only newly added actions from the filter actions
				$actions_diff = array_filter($actions_new, function ($actions_new_element) use ($actions_old) {

				    return !in_array($actions_new_element, $actions_old);
				});
			}

			// Process newly added actions
			foreach($actions_diff as $index => $action) {

				// If string returned, create invalid feedback for that field
				if(is_string($action)) {

					if($field_id !== false) {

						// Field
						$actions_diff[$index] = array(

							'action' 					=> 'field_invalid_feedback',
							'field_id'					=> $field_id,
							'section_repeatable_index'	=> $section_repeatable_index,
							'message' 					=> $action
						);

					} else {

						// Form
						$actions_diff[$index] = array(

							'action' 					=> 'message',
							'message' 					=> $action
						);
					}

					continue;
				}

				// If false returned, create invalid feedback for that field
				if($action === false) {

					if($field_id !== false) {

						// Field
						$actions_diff[$index] = array(

							'action' 					=> 'field_invalid_feedback',
							'field_id' 					=> $field_id,
							'section_repeatable_index' 	=> $section_repeatable_index,
							'message' 					=> self::field_invalid_feedback($field, $field_type_config)
						);

					} else {

						// Form
						$actions_diff[$index] = array(

							'action' 					=> 'message',
							'message' 					=> __('An unknown error occurred.', 'ws-form')
						);
					}

					continue;
				}

				// Full action array returned
				if(is_array($action)) {

					// Add field ID if not found
					if(
						($field_id !== false) &&
						!isset($action['field_id'])
					) {
						$actions_diff[$index]['field_id'] = $field_id;
						$actions_diff[$index]['section_repeatable_index'] = $section_repeatable_index;
					}

					// Get action ID
					$action_id = isset($action['action']) ? $action['action'] : false;

					// Check by action type
					switch($action_id) {

						case 'field_invalid_feedback' :

							// Add message if not found
							if(
								($field_id !== false) &&
								($field_type_config !== false) &&
								!isset($action['message'])) {

								$actions_diff[$index]['message'] = self::field_invalid_feedback($field, $field_type_config);
							}

							break;

						case 'error' :
						case 'message' :

							if($action_id == 'error') {

								$actions_diff[$index]['action'] = 'message';
							}

							if(!isset($action['message'])) {

								if(
									($field_id !== false) &&
									($field_type_config !== false)
								) {

									$actions_diff[$index]['message'] = self::field_invalid_feedback($field, $field_type_config);

								} else {

									$actions_diff[$index]['message'] = __('An unknown error occurred', 'ws-form');
								}
							}

							break;
					}

					continue;
				}

				// Unknown validation type
				unset($actions_diff[$index]);
			}

			return (count($actions_diff) > 0) ? array_merge($actions_old, $actions_diff) : $actions_old;
		}

		// Process file fields
		public function process_file_fields($form_object) {

			// Get sections array
			$sections = WS_Form_Common::get_sections_from_form($form_object);

			// Get section_repeatable
			$section_repeatable = !empty($this->section_repeatable) ? unserialize($this->section_repeatable) : array();

			// Is this a form submit?
			$form_submit = ($this->post_mode == 'submit');

			// Process each section
			foreach($sections as $section_id => $section) {

				if($section->repeatable) {

					$section_id_string = 'section_' . $section_id;

					// Get repeatable indexes for that section
					if(
						!isset($section_repeatable[$section_id_string]) ||
						!isset($section_repeatable[$section_id_string]['index'])
					) {

						parent::db_throw_error(__('Repeatable data error. Section ID not found in wsf_form_section_repeatable_index.', 'ws-form'));
					}

					$section_repeatable_indexes = $section_repeatable[$section_id_string]['index'];

					foreach($section_repeatable_indexes as $section_repeatable_index) {

						self::setup_from_post_section($section, $form_submit, true, false, $section_id, $section_repeatable_index, $section_repeatable);
					}

				} else {

					self::setup_from_post_section($section, $form_submit, true, false);
				
				}
			}
		}

		// Get field invalid feedback
		public function field_invalid_feedback($field, $field_type_config) {

			// Get field invalid feedback
			$field_invalid_feedback = WS_Form_Common::get_object_meta_value($field, 'invalid_feedback', '');

			// Check invalid feedback
			if($field_invalid_feedback == '') {

				// Use form invalid feedback
				$field_invalid_feedback = WS_Form_Common::get_object_meta_value($this->form_object, 'invalid_feedback_mask', '');

				// Check invalid feedback
				if($field_invalid_feedback == '') {

					// Use default invalid feedback
					$field_invalid_feedback = apply_filters('wsf_field_invalid_feedback_text', __('This field is required.', 'ws-form'));
				}
			}

			// Get field label
			$invalid_feedback_label = $field->label;

			// Parse invalid_feedback_mask_placeholder
			$field_invalid_feedback = str_replace('#label_lowercase', strtolower($invalid_feedback_label), $field_invalid_feedback);
			$field_invalid_feedback = str_replace('#label', $invalid_feedback_label, $field_invalid_feedback);

			return $field_invalid_feedback;
		}

		// Meta value stringify
		public function field_value_stringify($field_object, $field_value, $field_submit_array, $section_repeatable_delimiter_row) {

			$field_type = $field_object->type;

			if($field_submit_array) {

				if(!is_array($field_value)) { $field_value = array($field_value); }

				switch($field_type) {

					case 'file' :
					case 'signature' :

						$field_value = $field_value['name'];
						break;

					case 'googlemap' :

						if(
							is_array($field_value) &&
							isset($field_value['lat']) &&
							isset($field_value['lng'])
						) {

							$field_value = sprintf('%.7f,%.7f', $field_value['lat'], $field_value['lng']);

						} else {

							$field_value = '';
						}
						break;

					default :

						$field_value = implode($section_repeatable_delimiter_row, $field_value);
				}

			} else {

				switch($field_type) {

					case 'datetime' :

						$field_value = WS_Form_Common::get_date_by_type($field_value, $field_object);;
						break;
				}
			}

			return $field_value;
		}

		// Read form object
		public function db_form_object_read($bypass_user_capability_check = false) {

			// Check form ID
			self::db_check_form_id();

			// Read form data
			$ws_form_form = New WS_Form_Form();
			$ws_form_form->id = $this->form_id;

			if($this->preview) {

				// Draft
				$form_object = $ws_form_form->db_read(true, true, false, true, $bypass_user_capability_check);

				// Form cannot be read
				if($form_object === false) { parent::db_throw_error(__('Unable to read form data. Still logged in?', 'ws-form')); }

			} else {

				// Published
				$form_object = $ws_form_form->db_read_published(true);

				// Form not yet published
				if($form_object === false) { parent::db_throw_error(__('No published form data.', 'ws-form')); }
			}

			// Filter
			$form_object = apply_filters('wsf_pre_render_' . $this->form_id, $form_object, $this->preview);
			$form_object = apply_filters('wsf_pre_render', $form_object, $this->preview);

			// Convert to object
			$this->form_object = $form_object;
		}

		// Process file upload
		public function db_file_process($field, $field_required, $form_submit, $section_repeatable_index, $field_name) {

			// No capabilities required, this is a public method

			// Image processing
			$file_image_max_width = WS_Form_Common::get_object_meta_value($field, 'file_image_max_width', '');
			$file_image_max_height = WS_Form_Common::get_object_meta_value($field, 'file_image_max_height', '');
			$file_image_crop = (WS_Form_Common::get_object_meta_value($field, 'file_image_crop', '') == 'on');
			$file_image_compression = WS_Form_Common::get_object_meta_value($field, 'file_image_compression', '');
			$file_image_mime = WS_Form_Common::get_object_meta_value($field, 'file_image_mime', '');

			// Check $_FILES exists
			if(!isset($_FILES)) { return false; }

			// Check $_FILES[$field_name] exists
			if(!isset($_FILES[$field_name])) { return false; }

			// Check $_FILES[$field_name]['error'] exists
			if(!isset($_FILES[$field_name]['error'])) { return false; }

			// Get error array
			$file_errors = ($section_repeatable_index === false) ? $_FILES[$field_name]['error'] : $_FILES[$field_name]['error'][$section_repeatable_index];

			// File count
			$file_count = count($file_errors);
			$max_uploads = absint(WS_Form_Common::option_get('max_uploads'));
			$file_min = absint(WS_Form_Common::get_object_meta_value($field, 'file_min', 'wsform'));
			$file_max = absint(WS_Form_Common::get_object_meta_value($field, 'file_max', 'wsform'));

			// File count - System level
			if(
				($max_uploads > 0) &&
				($file_count > $max_uploads)
			) {

				self::db_throw_error_field_invalid_feedback(absint($field->id), $section_repeatable_index, sprintf(__('Too many files were uploaded (Maximum: %u files).', 'ws-form'), $max_uploads));
				return false;
			}

			// File count - Field level
			if(
				($file_min > 0) &&
				($file_count < $file_min) 
			) {

				self::db_throw_error_field_invalid_feedback(absint($field->id), $section_repeatable_index, sprintf(__('Too few files were uploaded (Minimum: %u files).', 'ws-form'), $file_min));
				return false;
			}
			if(
				($file_max > 0) &&
				($file_count > $file_max) 
			) {

				self::db_throw_error_field_invalid_feedback(absint($field->id), $section_repeatable_index, sprintf(__('Too many files were uploaded (Maximum: %u files).', 'ws-form'), $file_max));
				return false;
			}

			// Process files
			$file_objects = array();
			$file_index = 1;

			foreach($file_errors as $file_key => $file_error) {

				$file_error = absint($file_error);

				// Error management
				switch($file_error) {

					case 0:
						break;

					case UPLOAD_ERR_INI_SIZE: 
					case UPLOAD_ERR_FORM_SIZE: 

						self::db_throw_error_field_invalid_feedback(absint($field->id), $section_repeatable_index, sprintf(__('The uploaded file was too large (Maximum size: %s).', 'ws-form'), WS_Form_Common::get_file_size(wp_max_upload_size())));
						return false;

					case UPLOAD_ERR_PARTIAL: 

						parent::db_throw_error(__('The uploaded file was only partially uploaded.', 'ws-form')); 
						return false;

					case UPLOAD_ERR_NO_FILE :

						if($field_required && $form_submit) {

							self::db_throw_error_field_invalid_feedback(absint($field->id), $section_repeatable_index, __('No file was uploaded.', 'ws-form'));
						}
						return false;

					case UPLOAD_ERR_NO_TMP_DIR: 

						parent::db_throw_error(__('Missing a temporary folder.', 'ws-form')); 
						return false;

					case UPLOAD_ERR_CANT_WRITE: 

						parent::db_throw_error(__('Failed to write file to disk.', 'ws-form')); 
						return false;

					case UPLOAD_ERR_EXTENSION: 

						parent::db_throw_error(__('File upload stopped by extension.', 'ws-form')); 
						return false;

					default :

						parent::db_throw_error(__('Unknown file upload error.', 'ws-form')); 
						return false;
				}

				// Get file data
				$file_name = ($section_repeatable_index === false) ? $_FILES[$field_name]['name'][$file_key] : $_FILES[$field_name]['name'][$section_repeatable_index][$file_key];
				$file_type = ($section_repeatable_index === false) ? $_FILES[$field_name]['type'][$file_key] : $_FILES[$field_name]['type'][$section_repeatable_index][$file_key];
				$file_size = absint(($section_repeatable_index === false) ? $_FILES[$field_name]['size'][$file_key] : $_FILES[$field_name]['size'][$section_repeatable_index][$file_key]);
				$file_tmp_name = ($section_repeatable_index === false) ? $_FILES[$field_name]['tmp_name'][$file_key] : $_FILES[$field_name]['tmp_name'][$section_repeatable_index][$file_key];

				// Check file restrictions
				try {

					WS_Form_File_Handler::check_file_restrictions($field, $file_name, $file_type, $file_size, $file_tmp_name);

				} catch (Exception $e) {

					// Throw error
					self::db_throw_error_field_invalid_feedback(

						absint($field->id),
						$section_repeatable_index,
						$e->getMessage()
					);

					return false;
				}

				// Check file was uploaded by HTTP POST
				if(!is_uploaded_file($file_tmp_name)) { parent::db_throw_error(__('Illegal file upload', 'ws-form')); }

				// Check file name
				if(strpos($file_name, '/') !== false) { parent::db_throw_error(__('Illegal file name', 'ws-form')); }
				if(strpos($file_name, '\\') !== false) { parent::db_throw_error(__('Illegal file name', 'ws-form')); }
				$file_name = basename($file_name);

				// Image processing
				$image_processing_mime_array = array(

					'image/jpeg',
					'image/png',
					'image/gif'
				);
				$image_processing_save = false;
				if(in_array($file_type, $image_processing_mime_array)) {

					$image = wp_get_image_editor($file_tmp_name);

					if(!is_wp_error($image)) {

						// Resize
						if(
							($file_image_max_width != '') ||
							($file_image_max_height != '')
						) {

							$file_image_max_width = absint($file_image_max_width);
							$file_image_max_height = absint($file_image_max_height);

							$image->resize($file_image_max_width, $file_image_max_height, $file_image_crop);
							$image_processing_save = true;
						}

						// Image quality
						if($file_image_compression != '') {

							$file_image_compression = absint($file_image_compression);
							if(
								($file_image_compression >= 1) &&
								($file_image_compression <= 100)
							) {

								$image->set_quality($file_image_compression);
								$image_processing_save = true;
							}
						}

						// Image mime
						if($file_image_mime != '') {

							if($image->supports_mime_type($file_image_mime)) {

								$file_type = $file_image_mime;
								$image_processing_save = true;
							}
						}

						// Save
						if($image_processing_save) {

							// Save file
							$saved_image = $image->save($file_tmp_name, $file_type);

							if($saved_image) {

								// Read saved file data
								$file_name = pathinfo($file_name)['filename'] . '.' . pathinfo($saved_image['file'])['extension'];
								$file_tmp_name = $saved_image['path'];
								$file_size = filesize($file_tmp_name);
								$file_type = $saved_image['mime-type'];

							} else {

								// Error saving file
								parent::db_throw_error(__('Unable to save image file after image optimization.', 'ws-form'));
							}
						}
					}
				}

				// Check file object
				$file_object = array();
				$file_object['name'] = WS_Form_File_Handler::file_name_parse($file_name, $this->form_object, $this, $field, $file_count, $file_index, $section_repeatable_index);
				$file_object['type'] = $file_type;
				$file_object['size'] = $file_size;
				$file_object['path'] = $file_tmp_name;

				// Push to file objects array
				$file_objects[] = $file_object;

				$file_index++;
			}

			// Check hash
			if(!WS_Form_Common::check_submit_hash($this->hash)) {

				parent::db_throw_error(__('Invalid hash ID (db_file_process).', 'ws-form'));
			}

			// Field file handler type
	 		$file_handler = WS_Form_Common::get_object_meta_value($field, 'file_handler', 'wsform');
	 		if($file_handler == '') { $file_handler = 'wsform'; }

			// Run file handlers
			$file_objects = apply_filters('wsf_file_handler_' . $file_handler, $file_objects, $this, $field, $section_repeatable_index);

			return $file_objects;
		}

		// DropzoneJS - Process
		public function db_file_process_dropzonejs($field, $field_required, $form_submit, $section_repeatable_index, $field_value) {

			// No capabilities required, this is a public method
			$temp_files = array();

			$file_objects = array();

			if(
				!is_array($field_value)
			) {
				return $file_objects;
			}

			$field_value_decoded = json_decode($field_value[0]);

			if(
				is_null($field_value_decoded) ||
				!isset($field_value_decoded->type) ||
				!isset($field_value_decoded->attachment_ids) ||
				($field_value_decoded->type != 'dropzonejs')
			) {

				return $file_objects;
			}

			$attachment_ids = (array) $field_value_decoded->attachment_ids;

			if($field_required && (count($attachment_ids) == 0)) {

				self::db_throw_error_field_invalid_feedback(absint($field->id), $section_repeatable_index, __('No file was uploaded.', 'ws-form'));
				return false;
			}

			// Count
			$file_count = count($attachment_ids);
			$file_min = absint(WS_Form_Common::get_object_meta_value($field, 'file_min', 'wsform'));
			$file_max = absint(WS_Form_Common::get_object_meta_value($field, 'file_max', 'wsform'));

			// File count - Field level
			if(
				($file_min > 0) &&
				($file_count < $file_min) 
			) {

				self::db_throw_error_field_invalid_feedback(absint($field->id), $section_repeatable_index, sprintf(__('Too few files were uploaded (Minimum: %u files).', 'ws-form'), $file_min));
				return false;
			}
			if(
				($file_max > 0) &&
				($file_count > $file_max) 
			) {

				self::db_throw_error_field_invalid_feedback(absint($field->id), $section_repeatable_index, sprintf(__('Too many files were uploaded (Maximum: %u files).', 'ws-form'), $file_max));
				return false;
			}

			$file_index = 1;

			foreach($attachment_ids as $uuid => $attachment_id) {

				// Get file path full
				$file_path_full = get_attached_file($attachment_id);
				if($file_path_full === false) { continue; }

				// Get file size
				$file_size = 0;
				if(file_exists($file_path_full)) {

					$file_size = filesize($file_path_full);

				} else {

					parent::db_throw_error(__('Cannot read scratch file when processing DropzoneJS upload.', 'ws-form'));
				}

				// Get mime type
				$file_type = get_post_mime_type($attachment_id);

				// Get original file name
				$wsf_file_name = get_post_meta($attachment_id, '_wsf_file_name', true);
				if($wsf_file_name) {

					$file_name = WS_Form_File_Handler::file_name_parse($wsf_file_name, $this->form_object, $this, $field, $file_count, $file_index, $section_repeatable_index);

				} else {

					$file_name = basename($file_path_full);
				}

				// Check file restrictions
				try {

					WS_Form_File_Handler::check_file_restrictions($field, $file_name, $file_type, $file_size, $file_path_full);

				} catch (Exception $e) {

					// Throw error
					self::db_throw_error_field_invalid_feedback(

						absint($field->id),
						$section_repeatable_index,
						$e->getMessage()
					);

					return false;
				}

				// Check file object
				$file_object = array();
				$file_object['name'] = $file_name;
				$file_object['size'] = $file_size;
				$file_object['type'] = $file_type;
				$file_object['attachment_id'] = $attachment_id;

				// Get file path
				$file_path = str_replace(wp_upload_dir()['basedir'] . '/', '', $file_path_full);

				if(!$form_submit) {

					// Get image size
					$image_size = apply_filters('wsf_dropzonejs_image_size', WS_FORM_DROPZONEJS_IMAGE_SIZE);

					// Get file URL
					$file_url = wp_get_attachment_url($attachment_id);
					if(!$file_url) { $file_url = ''; }

					// Get file preview image
					$file_preview = wp_get_attachment_image_src($attachment_id, $image_size, true);
					if($file_preview) {

						$file_preview = $file_preview[0];

					} else {

						$file_preview = wp_get_attachment_thumb_url($attachment_id);
					}
					if(!$file_preview) { $file_preview = ''; }

					// Additional meta data used by the save process
					$file_object['path'] = $file_path;
					$file_object['url'] = $file_url;
					$file_object['preview'] = $file_preview;
					$file_object['uuid'] = $uuid;

				} else {

					// Check if this is a scratch file
					if(get_post_meta($attachment_id, '_wsf_attachment_scratch', true)) {

						// Create temporary copy of file
						require_once(ABSPATH . 'wp-admin/includes/file.php');
						$temp_file = wp_tempnam();
						$temp_files[] = $temp_file;
						copy($file_path_full, $temp_file);

						// Set temporary file on file object (we delete the temp file later)
						$file_object['path'] = $temp_file;

					} else {

						// This is an existing file that wasn't uploaded through the form (e.g. existing post image)
						$file_object['path'] = $file_path;
					}
				}

				$file_objects[] = $file_object;

				$file_index++;
			}

			if($form_submit) {

				// Check hash
				if(!WS_Form_Common::check_submit_hash($this->hash)) {

					parent::db_throw_error(__('Invalid hash ID (db_file_process_dropzonejs).', 'ws-form'));
				}

				// Field file handler type
		 		$file_handler = WS_Form_Common::get_object_meta_value($field, 'file_handler', 'wsform');
		 		if($file_handler == '') { $file_handler = 'wsform'; }

				// Run file handlers
				$file_objects = apply_filters('wsf_file_handler_' . $file_handler, $file_objects, $this, $field, $section_repeatable_index);

				// Delete temporary files
				foreach($temp_files as $temp_file) {

					if(file_exists($temp_file)) { unlink($temp_file); }
				}
			}

			return $file_objects;
		}

		// Process signature
		public function db_signature_process($field, $section_repeatable_index, $field_value) {

			$ws_form_submit_meta = new WS_Form_Submit_Meta();
			return $ws_form_submit_meta->process_signature($field, $section_repeatable_index, $field_value, $this);
		}
		// Process captcha
		public function db_captcha_process($field_id, $section_repeatable_index, $captcha_secret_key, $endpoint, $query_var) {

			// Check Captcha response
			if($captcha_secret_key == '') {

				parent::db_throw_error(__('Captcha secret key not set.', 'ws-form'));
			}

			// Get Captcha response
			$captcha_response = WS_Form_Common::get_query_var_nonce($query_var);
			if(empty($captcha_response)) {

				parent::db_throw_error(__('Invalid captcha response.', 'ws-form'));
			}

			// Body
			$body = array(

				'secret' => $captcha_secret_key,
				'response' => $captcha_response
			);

			// Remote IP (Only passed if Remote IP tracking is enabled)
			if(
				isset($this->meta) &&
				isset($this->meta['tracking_remote_ip'])
			) {

				$tracking_remote_ip = $this->meta['tracking_remote_ip'];

				if(filter_var($tracking_remote_ip, FILTER_VALIDATE_IP) !== false) {

					$body['remoteip'] = $tracking_remote_ip;
				}
			}

			// Build args
			$args = array(

				'user-agent'	=> WS_Form_Common::get_request_user_agent(),
				'timeout'		=> WS_Form_Common::get_request_timeout(),
				'sslverify'		=> WS_Form_Common::get_request_sslverify(),
				'redirection' 	=> 5,
				'httpversion' 	=> '1.0',
				'blocking' 		=> true,
				'headers' 		=> array(),
				'body' 			=> $body,
				'cookies' 		=> array()
			);

			// Get status of captcha from endpoint
			$response = wp_remote_post($endpoint, $args);

			// Check for errors
			if(is_wp_error($response)) {

				$error_message = $response->get_error_message();
				parent::db_throw_error(sprintf(__('Captcha verification failed (%s).', 'ws-form'), $error_message));

			} else {

				$response_body = wp_remote_retrieve_body($response); 
				if($response_body == '') {

					parent::db_throw_error(__('Captcha verification response empty.', 'ws-form'));
				}

				$response_object = json_decode($response_body);
				if(is_null($response_object)) {

					parent::db_throw_error(__('Captcha verification response error.', 'ws-form'));
				}

				$captcha_success = $response_object->success;

				if($captcha_success) {

					// Store spam level
					$this->spam_level = isset($response_object->score) ? ((1 - floatval($response_object->score)) * WS_FORM_SPAM_LEVEL_MAX) : $this->spam_level;

					return true;

				} else {

					if(
						isset($response_object->{'error-codes'}) &&
						is_array($response_object->{'error-codes'})
					) {

						foreach($response_object->{'error-codes'} as $error_code) {

							switch($error_code) {

								case 'missing-input-secret' :

									$error_message = __('Captcha Error: The secret parameter was not passed.', 'ws-form');
									break;

								case 'invalid-input-secret' :

									$error_message = __('Captcha Error: The secret parameter was invalid or did not exist.', 'ws-form');
									break;

								case 'missing-input-response' :

									$error_message = __('Captcha Error: The response parameter was not passed.', 'ws-form');
									break;

								case 'invalid-input-response' :

									$error_message = __('Captcha Error: The response parameter is invalid or has expired.', 'ws-form');
									break;

								case 'bad-request' :

									$error_message = __('Captcha Error: The request was rejected because it was malformed.', 'ws-form');
									break;

								case 'timeout-or-duplicate' :

									$error_message = __('Captcha Error: The response parameter has already been validated before.', 'ws-form');
									break;

								case 'internal-error' :

									$error_message = __('Captcha Error: An internal error happened while validating the response. The request can be retried.', 'ws-form');
									break;

								default :

									$error_message = sprintf(__('Captcha Error: %s.', 'ws-form'), $error_code);
							}

							// Throw error
							parent::db_throw_error($error_message);
						}
					}

					// Handle error
					parent::db_throw_error(__('Captcha invalid.', 'ws-form'));
				}
			}

			return true;
		}

		// Clear hidden meta values
		public function clear_hidden_meta_values($submit_object = false, $bypass_user_capability_check = true) {

			if($submit_object === false) {

				$submit_object = $this;
			}

			if(!isset($submit_object->meta)) { return $submit_object; }
			if(!isset($submit_object->meta['wsf_meta_key_hidden'])) { return $submit_object; }

			// Get section repeatable data (Unserialize if necesary)
			$section_repeatable_serialized = false;
			if(isset($submit_object->section_repeatable)) {

				$section_repeatable_serialized = is_serialized($submit_object->section_repeatable);

				$section_repeatable_array = $section_repeatable_serialized ? unserialize($submit_object->section_repeatable) : $submit_object->section_repeatable;

				if(!is_array($section_repeatable_array)) { $section_repeatable_array = array(); }

			} else {

				$section_repeatable_array = array();
			}
			$section_repeatable_original_array = $section_repeatable_array;
			$section_repeatable_edited = false;

			// Get hidden field names
			$meta_key_hidden_array = $submit_object->meta['wsf_meta_key_hidden'];

			// Clear each hidden array
			$field_ids_hidden = array();
			$field_ids_need_new_fallback = array();

			foreach($meta_key_hidden_array as $meta_key_hidden) {

				if(
					!isset($submit_object->meta[$meta_key_hidden]) ||
					!isset($submit_object->meta[$meta_key_hidden]['id'])
				) {
					continue;
				}

				// Get field ID
				$field_id = absint($submit_object->meta[$meta_key_hidden]['id']);

				// Get section ID (Only set on repeatable sections)
				$section_id = isset($submit_object->meta[$meta_key_hidden]['section_id']) ? absint($submit_object->meta[$meta_key_hidden]['section_id']) : 0;
				if($section_id > 0) {

					if(!isset($field_ids_hidden[$section_id])) { $field_ids_hidden[$section_id] = array(); }

					// Add to fields touched
					if(!isset($field_ids_hidden[$section_id][$field_id])) { $field_ids_hidden[$section_id][$field_id] = 0; }
					$field_ids_hidden[$section_id][$field_id]++;
				}

				// Unset field
				unset($submit_object->meta[$meta_key_hidden]);

				// Unset fallback field
				unset($submit_object->meta[WS_FORM_FIELD_PREFIX . $field_id]);

				$field_ids_need_new_fallback[] = $field_id;
			}

			$field_ids_need_new_fallback = array_unique($field_ids_need_new_fallback);

			if(count($field_ids_need_new_fallback) > 0) {

				// Run through each section and clean section repeatable array
				foreach($field_ids_hidden as $section_id => $fields) {

					// Get section name
					$section_name = sprintf('section_%u', $section_id);

					// Check this exists in the index
					if(!isset($section_repeatable_array[$section_name])) { continue; }

					// Run through each index
					foreach($section_repeatable_array[$section_name]['index'] as $section_repeatable_index => $section_repeatable_id) {

						// Find out how many fields remain for this section
						$section_row_fields_found = false;
						foreach($submit_object->meta as $meta) {

							if(
								!isset($meta['section_id']) ||
								!isset($meta['repeatable_index'])
							) {
								continue;
							}

							// Get section ID (Only set on repeatable sections)
							$meta_section_id = absint($meta['section_id']);
							$meta_repeatable_index = absint($meta['repeatable_index']);
							if(
								($meta_section_id === $section_id) &&
								($meta_repeatable_index === $section_repeatable_id)
							) {

								$section_row_fields_found = true;
								break;
							}
						}

						// If no fields found in this row, then remove it from the section repeatable array
						if(!$section_row_fields_found) {

							// Remove this row from the index
							foreach($section_repeatable_array[$section_name]['index'] as $section_repeatable_index_delete => $section_repeatable_id_delete) {

								if($section_repeatable_id_delete === $section_repeatable_id) {

									unset($section_repeatable_array[$section_name]['index'][$section_repeatable_index_delete]);
									$section_repeatable_array[$section_name]['index'] = array_values($section_repeatable_array[$section_name]['index']);
									$section_repeatable_edited = true;
								}
							}
						}
					}
				}

				// Rebuild meta data
				$meta_array = array();
				foreach($submit_object->meta as $meta_key => $meta) {

					if(
						!isset($meta['value']) ||
						!isset($meta['section_id']) ||
						!isset($meta['id']) ||
						!isset($meta['repeatable_index'])
					) {

						continue;
					}

					// Strip db_ignore
					if(isset($meta['db_ignore']) && $meta['db_ignore']) { continue; }

					// Build meta data
					$meta_array[] = array(

						'meta_key' => $meta_key,
						'meta_value' => $meta['value'],
						'section_id' => $meta['section_id'],
						'field_id' => $meta['id'],
						'repeatable_index' => $meta['repeatable_index']
					);
				}

				// Get new fallback values
				$meta_new = self::db_get_submit_meta($this, $meta_array, $bypass_user_capability_check);

				// Run through field that needs a new fallback
				foreach($field_ids_need_new_fallback as $field_id) {

					// Field name
					$field_name = WS_FORM_FIELD_PREFIX . $field_id;
					if(isset($meta_new[$field_name])) {

						// We don't store the fallback data to the database, it is just made available to any actions that need it
						$meta_new[$field_name]['db_ignore'] = true;

						// Set repeatable index to false
						$meta_new[$field_name]['repeatable_index'] = false;

						// Replace						
						$this->meta[$field_name] = $submit_object->meta[$field_name] = $meta_new[$field_name];
					}
				}

				// Rebuild section_repeatable
				if($section_repeatable_edited) {

					$this->section_repeatable = $submit_object->section_repeatable = ($section_repeatable_serialized ? serialize($section_repeatable_array) : $section_repeatable_array);
				}
			}

			return $submit_object;
		}

		// Handle server side error - Invalid feedback
		public function db_throw_error_field_invalid_feedback($field_id, $section_repeatable_index, $message) {

			// Only process first error for field
			if(isset($this->error_validation_actions[$field_id])) { return; }

			$this->error_validation_actions[$field_id] = array(

				'action' 					=> 'field_invalid_feedback',
				'field_id' 					=> $field_id,
				'section_repeatable_index' 	=> $section_repeatable_index,
				'message' 					=> $message
			);
		}

		// Send error notification
		public function report_submit_error_send($e = 'test') {

			// Check for test send
			if($e === 'test') {

				$test = true;

				$form_id = 123;
				$form_label = __('Test form label', 'ws-form');
				$form_url = '#';

				$message = __('Test message', 'ws-form');
				$code = __('Test code', 'ws-form');
				$file = __('Test file', 'ws-form');
				$line = __('Test line', 'ws-form');
				$trace = __('Test trace', 'ws-form');

			} else {

				$test = false;

				// Check for error object
				if(is_object($e)) {

					$message = method_exists($e, 'getMessage') ? $e->getMessage() :false;
					$code =    method_exists($e, 'getCode') ? $e->getCode() : false;
					$file =    method_exists($e, 'getFile') ? $e->getFile() : false;
					$line =    method_exists($e, 'getLine') ? $e->getLine() : false;
					$trace =   method_exists($e, 'getTraceAsString') ? $e->getTraceAsString() : false;

				} else {

					$message = $code = $file = $line = $trace = false;
				}

				if(is_object($this->form_object)) {

					$form_id = $this->form_object->id;
					$form_label = $this->form_object->label;
					$form_url = WS_Form_Common::get_admin_url('ws-form-edit', $form_id);

				} else {

					$form_id = $form_label = $form_url = false;
				}
			}

			// Get options
			$frequency = WS_Form_Common::option_get('report_submit_error_frequency', 'minute');
			if(empty($frequency)) { $frequency = 'minute'; }
			$email_to = WS_Form_Common::option_get('report_submit_error_email_to', get_bloginfo('admin_email'));
			if(empty($email_to)) { $email_to = get_bloginfo('admin_email'); }
			$email_subject = WS_Form_Common::option_get('report_submit_error_email_subject', __('WS Form - Form Submission Error', 'ws-form'));
			if(empty($email_subject)) { $email_subject = __('WS Form - Form Submission Error', 'ws-form'); }

			// Parse options
			$email_to = trim(WS_Form_Common::parse_variables_process($email_to, false, false, 'text/plain'));
			$email_subject = trim(WS_Form_Common::parse_variables_process($email_subject, false, false, 'text/plain'));

			// Split email addresses
			if(strpos($email_to, ' ') !== false) {

				$email_to_array = explode(' ', $email_to);

			} else {

				$email_to_array = explode(',', $email_to);
			}

			// Check options
			if(!in_array($frequency, array('all', 'minute', 'hour', 'day'))) {

				parent::db_throw_error(__('Invalid frequency', 'ws-form'));
			}

			foreach($email_to_array as $email_to) {

				if(!filter_var($email_to, FILTER_VALIDATE_EMAIL)) {

					parent::db_throw_error(__('Invalid email address: %s', $email_to, 'ws-form'));
				}
			}

			if(empty($email_subject)) {

				parent::db_throw_error(__('Invalid email subject'));
			}

			// Check when error notification was last sent
			if($frequency != 'all') {

				$report_submit_error_last = WS_Form_Common::option_get('report_submit_error_last', false);

				if(!empty($report_submit_error_last)) {

					// Get time of last error
					$report_submit_error_last_time = absint($report_submit_error_last['time']);

					// Get time delta
					$report_submit_error_last_delta = time() - $report_submit_error_last_time;

					// Get time delta max
					switch($frequency) {

						case 'day' :

							$report_submit_error_last_delta_max = 86400;
							break;

						case 'hour' :

							$report_submit_error_last_delta_max = 3600;
							break;

						default :

							$report_submit_error_last_delta_max = 60;
							break;
					}

					// Check delta, if too soon, do not proceed
					if($report_submit_error_last_delta < $report_submit_error_last_delta_max) { return false; }
				}
			}

			// Build email message

			// URL
			$email_message = sprintf(

				'<p><strong>%1$s:</strong> <a href="%2$s" target="_blank">%2$s</a>',
				__('URL', 'ws-form'),
				WS_Form_Common::get_referrer()
			);

			// Build date range
			$date_format = get_option('date_format');

			if(
				($form_url !== false) &&
				($form_label !== false)
			) {

				$email_message .= sprintf(

					'<p><strong>%s:</strong> <a href="%s" target="_blank">%s</a>',
					__('Form', 'ws-form'),
					$form_url,
					esc_html($form_label)
				);
			}

			// Build error table
			if(
				($message !== false) ||
				($code !== false) ||
				($file !== false) ||
				($line !== false) ||
				($trace !== false)
			) {

				$email_message .= '<table class="table-report">';

				// Message
				if($message !== false) {

					$email_message .= sprintf('<tr><th>%s</th><td>%s</td></tr>', __('Message', 'ws-form'), esc_html($message));
				}

				// File
				if($file !== false) {

					$email_message .= sprintf('<tr><th>%s</th><td>%s</td></tr>', __('File', 'ws-form'), esc_html($file));
				}

				// Line
				if($line !== false) {

					$email_message .= sprintf('<tr><th>%s</th><td>%s</td></tr>', __('Line', 'ws-form'), esc_html($line));
				}

				// Code
				if($code !== false) {

					$email_message .= sprintf('<tr><th>%s</th><td>%s</td></tr>', __('Code', 'ws-form'), esc_html($code));
				}

				// Trace
				if($trace !== false) {

					$email_message .= sprintf('<tr><th>%s</th><td><pre>%s</pre></td></tr>', __('Trace', 'ws-form'), esc_html($trace));
				}

				$email_message .= '</table>';

			} else {

				$email_message .= sprintf(

					'<p>%s</p>',
					__('An unknown error occurred.', 'ws-form')
				);

				$email_message .= '<table class="table-report">';

				// Type
				$email_message .= sprintf('<tr><th>%s</th><td>%s</td></tr>', __('Type', 'ws-form'), esc_html(gettype($e)));

				// Variable
				$email_message .= sprintf('<tr><th>%s</th><td>%s</td></tr>', __('Variable', 'ws-form'), esc_html(print_r($e, true)));

				$email_message .= '</table>';
			}

			// Get email template
			$email_template = file_get_contents(sprintf('%sincludes/templates/email/html/error.html', WS_FORM_PLUGIN_DIR_PATH));

			// Parse email template
			$mask_values = array(

				'email_subject' => htmlentities($email_subject),
				'email_title' => __('Form Submission Error', 'ws-form'),
				'email_message' => $email_message
			);

			$wp_mail_message = WS_Form_Common::mask_parse($email_template, $mask_values);

			// Build headers
			$headers = array(

				'Content-Type: text/html'
			);

			// Send email
			wp_mail($email_to_array, $email_subject, $wp_mail_message, $headers);

			// Store last error
			if(!$test) {

				WS_Form_Common::option_set('report_submit_error_last', array(

					'time' => time(),
					'form_id' => $form_id,
					'form_label' => $form_label,
					'message' => $message,
					'code' => $code,
					'file' => $file,
					'line' => $line,
					'trace' => $trace
				));
			}
		}

		// Remove protected meta data
		public function db_remove_meta_protected() {

			$this->meta_protected = array();
		}

		// Compact
		public function db_compact() {

			// Remove form_object
			if(isset($this->form_object)) { unset($this->form_object); }
			if(isset($this->field_types)) { unset($this->field_types); }
		}

		// Create hash
		public function db_create_hash() {

			if($this->hash == '') { $this->hash = esc_sql(wp_hash($this->id . '_' . $this->form_id . '_' . time() . '_' . wp_rand())); }

			// Check hash
			if(!WS_Form_Common::check_submit_hash($this->hash)) {

				parent::db_throw_error(__('Invalid hash (db_create_hash).', 'ws-form'));
			}

			return $this->hash;
		}

		// Create token
		public function db_create_token() {

			if(!WS_Form_Common::check_submit_hash($this->hash)) {

				parent::db_throw_error(__('Invalid hash (db_create_token).', 'ws-form'));
			}

			if($this->token == '') { $this->token = esc_sql(wp_hash($this->id . '_' . $this->form_id . '_' . $this->token . '_' . time() . '_' . wp_rand())); }

			// Check hash
			if(!WS_Form_Common::check_submit_hash($this->token)) {

				parent::db_throw_error(__('Invalid token (db_create_token).', 'ws-form'));
			}

			return $this->token;
		}

		// Check form id
		public function db_check_form_id() {

			if(absint($this->form_id) === 0) { parent::db_throw_error(__('Invalid form ID.', 'ws-form')); }
			return true;
		}

		// Check id
		public function db_check_id() {

			if(absint($this->id) === 0) { parent::db_throw_error(__('Invalid submit ID.', 'ws-form')); }
			return true;
		}
	}