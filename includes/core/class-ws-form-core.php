<?php

	class WS_Form_Core {

		// Get SET SQL from data array (key => value pairs) for each field in $fields
		public function get_wpdb_data($fields, $object, $insert = false) {

			global $wpdb;

			$data = array();
			$format = array();

			foreach($fields as $field) {

				if(!is_null($field)) { $field = trim($field); }
				$value = '';
				$format_set = false;

				// Set value if found
				if(property_exists($object, $field)) { $value = $object->{$field}; }

				// Check for arrays
				if(is_array($value)) { $value = serialize($value); }

				switch($field) {

					case 'label' :

						// Truncate
						if(strlen($value) > WS_FORM_LABEL_MAX_LENGTH) {

							$value = substr($value, 0, WS_FORM_LABEL_MAX_LENGTH);
						}

						// Comply with unfiltered_html capability
						$value = WS_Form_Common::santitize_unfiltered_input($value);

						break;

					case 'parent_section_id' :

						if($insert) { $value = 0; }
						$format_set = '%d';
						break;

					case 'child_count' :

						if($insert) { $value = 0; }
						$format_set = '%d';
						break;

					case 'date_added' :

						if($insert) { $value = WS_Form_Common::get_mysql_date(); }
						break;

					case 'date_updated' :

						$value = WS_Form_Common::get_mysql_date();
						break;

					case 'user_id' :

						$value = get_current_user_id();
						$format_set = '%d';
						break;

					case 'sort_index' :

						$format_set = '%d';
						break;

					case 'form_id' :

						if($insert) { $value = $this->form_id; }
						$format_set = '%d';
						break;

					case 'group_id' :

						if($insert) { $value = $this->group_id; }
						$format_set = '%d';
						break;

					case 'section_id' :

						if($insert) { $value = $this->section_id; }
						$format_set = '%d';
						break;

					case 'spam_level' :

						$format_set = '%d';
						break;
				}

				// Check for null values
				if(is_null($value)) { $format_set = null; }

				$data[$field] = $value;
				$format[] = ($format_set !== false) ? $format_set : '%s';
			}

			$return_array = array('data' => $data, 'format' => $format);

			return $return_array;
		}

		// Santize label
		public function sanitize_label($label_default) {

			// Check label
			if(empty($this->label)) {

				$this->label = $label_default;
			}

			// Truncate label
			if(strlen($this->label) > WS_FORM_LABEL_MAX_LENGTH) {

				$this->label = substr($this->label, 0, WS_FORM_LABEL_MAX_LENGTH);
			}

			// Comply with unfiltered_html capability
			$this->label = WS_Form_Common::santitize_unfiltered_input($this->label);
		}

		// Update (then Insert on fail) an object
		public function db_update_insert($table_name, $fields_update, $fields_insert, $object, $object_type, $id = 0, $insert = true) {

			global $wpdb;

			$sql = $wpdb->prepare(

				"SELECT id FROM $table_name WHERE id = %d LIMIT 1",
				$id
			);

			// See if ID already exists
			if(is_null($wpdb->get_var($sql))) {

				// Get wpdb insert data and format
				$wpdb_insert_array = self::get_wpdb_data(explode(',', $fields_insert), $object, true);

				// Set ID (Ensure ID is set back correctly)
				if(isset($object->id) && !isset($wpdb_insert_array['data']['id'])) {

					$wpdb_insert_array['data']['id'] = $object->id;
					$wpdb_insert_array['format'][] = '%d';
				}

				// Insert
				$insert_count = $wpdb->insert($table_name, $wpdb_insert_array['data'], $wpdb_insert_array['format']);
				if($insert_count === false) {

					self::db_throw_error(__('Unable to insert', 'ws-form') . ' ' . $object_type);
				}

				return $wpdb->insert_id;

			} else {

				// Get wpdb update data and format
				$wpdb_update_array = self::get_wpdb_data(explode(',', $fields_update), $object, false);

				// Update
				$update_count = $wpdb->update($table_name, $wpdb_update_array['data'], array('id' => $id), $wpdb_update_array['format'], array('%d'));
				if($update_count === false) {

					self::db_throw_error(__('Unable to update', 'ws-form') . ' ' . $object_type);
				}

				return $id;
			}
		}

		// Object sort index processing
		public function db_object_sort_index($table_name, $parent_field, $parent_id, $next_sibling_id, $id) {

			global $wpdb;

			// Get current parent_id
			$sql = $wpdb->prepare(

				"SELECT $parent_field FROM $table_name WHERE id = %d LIMIT 1;",
				$id
			);

			$parent_id_old = $wpdb->get_var($sql);
			if(is_null($parent_id_old)) { self::db_wpdb_handle_error(__('Error getting current parent ID', 'ws-form')); }

			// Get new sort index
			$sort_index = self::db_object_sort_index_get($table_name, $parent_field, $parent_id, $next_sibling_id);

			// Update sort index
			$sql = $wpdb->prepare(

				"UPDATE $table_name SET $parent_field = %d, sort_index = %d WHERE id = %d;",
				$parent_id,
				$sort_index,
				$this->id
			);

			if($wpdb->query($sql) === false) { self::db_wpdb_handle_error(__('Error adjusting sort index', 'ws-form')); }

			// Clean up sort indexes
			self::db_object_sort_index_clean($table_name, $parent_field, $parent_id);

			if($parent_id != $parent_id_old) {

				// Clean up sort indexes of old parent
				self::db_object_sort_index_clean($table_name, $parent_field, $parent_id_old);
			}

			return $sort_index;
		}

		// Clean object sort indexes
		public function db_object_sort_index_clean($table_name, $parent_field, $parent_id) {

			global $wpdb;

			// Clean up sort indexes
			$wpdb->query('SET @i := 0;');

			$sql = $wpdb->prepare(

				"UPDATE $table_name SET sort_index = (@i := @i + 1) WHERE $parent_field = %d ORDER BY sort_index;",
				$parent_id
			);

			if(
				($wpdb->query($sql) === false) &&

				// Don't throw error on WordPress Playground (SQLite)
				!empty($_SERVER['SERVER_SOFTWARE']) &&
				($_SERVER['SERVER_SOFTWARE'] != 'PHP.wasm')
			) {

				self::db_wpdb_handle_error(__('Error tidying sort index', 'ws-form'));
			}
		}

		// Get next sort index
		public function db_object_sort_index_get($table_name, $parent_field, $parent_id, $next_sibling_id = 0) {

			global $wpdb;

			// Work out sort index
			if($next_sibling_id == 0) {

				// Get next sort_index
				$sql = $wpdb->prepare(

					"SELECT IFNULL(MAX(sort_index), 0) FROM $table_name WHERE $parent_field = %d;",
					$parent_id
				);

				$sort_index = $wpdb->get_var($sql) + 1;
				if(is_null($sort_index)) { self::db_wpdb_handle_error(__('Unable to determine sort index', 'ws-form')); }

			} else {

				// Adopt sort_index of next sibling
				$sql = $wpdb->prepare(

					"SELECT sort_index FROM $table_name WHERE id = %d LIMIT 1;",
					$next_sibling_id
				);

				$sort_index = $wpdb->get_var($sql);
				if(is_null($sort_index)) { self::db_wpdb_handle_error('Unable to determine sort index'); }

				// Increment records below and including current sort index
				$sql = $wpdb->prepare(

					"UPDATE $table_name SET sort_index = (sort_index + 1) WHERE $parent_field = %d AND sort_index >= %d;",
					$parent_id,
					$sort_index
				);

				if($wpdb->query($sql) === false) { self::db_wpdb_handle_error(__('Error adjusting sort indexes', 'ws-form')); }
			}

			return $sort_index;
		}

		// Get object label
		public function db_object_get_label($table_name, $object_id) {

			global $wpdb;

			if($object_id == 0) { self::db_wpdb_handle_error(__('Object ID is zero, cannot get label', 'ws-form')); }

			$sql = $wpdb->prepare(

				"SELECT label FROM $table_name WHERE id = %d LIMIT 1;",
				$object_id
			);

			$object_label = $wpdb->get_var($sql);
			if($object_label === false) { self::db_wpdb_handle_error(__('Error getting object label', 'ws-form')); }

			return $object_label;
		}

		// Build meta data for an object
		public function build_meta_data($meta_data, $meta_keys, $meta_values = false) {

			$return_array = [];

			foreach($meta_data as $key => $value) {

				if(is_array($value)) {

					if($key === 'meta_keys') {

						foreach($value as $meta_key) {

							if(!(isset($meta_keys[$meta_key]['dummy']) && $meta_keys[$meta_key]['dummy'] == true)) {

								if(isset($meta_keys[$meta_key]['default'])) {

									// Check default value for variables
									if(
										($meta_values !== false) &&
										is_string($meta_keys[$meta_key]['default']) &&
										(strpos($meta_keys[$meta_key]['default'], '#') !== false)
									) {

										$meta_keys[$meta_key]['default'] = WS_Form_Common::mask_parse($meta_keys[$meta_key]['default'], $meta_values);
									}

									$meta_value = $meta_keys[$meta_key]['default'];

								} else {

									$meta_value = '';
								}

								// Handle boolean values
								$meta_value = is_bool($meta_value) ? ($meta_value ? 'on' : '') : $meta_value;

								// Handle key changes
								if(isset($meta_keys[$meta_key]['key'])) {

									$meta_key = $meta_keys[$meta_key]['key'];
								}

								// Add to return array
								$return_array[$meta_key] = $meta_value;
							}
						}

					} else {

						// Follow
						$return_array = array_merge($return_array, self::build_meta_data($value, $meta_keys, $meta_values));
					}
				}
			}

			return $return_array;
		}

		// Handle DB error
		public function db_wpdb_handle_error($error_message) {

			global $wpdb;

			if($wpdb->last_error !== '') {

				self::db_throw_error(sprintf('%s (%s)', $error_message, $wpdb->last_error));

			} else {

				self::db_throw_error($error_message);
			}
		}

		// Throw error
		public function db_throw_error($error) {
			
			throw new Exception($error);
		}
	}
