<?php

	#[AllowDynamicProperties]
	class WS_Form_Form extends WS_Form_Core {

		public $id;
		public $user_id;
		public $date_added;
		public $date_updated;
		public $version;
		public $label;
		public $status;
		public $checksum;
		public $published_checksum;
		public $count_stat_view;
		public $count_stat_save;
		public $count_stat_submit;
		public $count_submit;
		public $count_submit_unread;

		public $new_lookup;
		public $meta;

		public $table_name;

		public $count_update_ws_form_submit = false;
		public $count_update_ws_form_form_stat = false;

		const DB_INSERT = 'label,user_id,date_added,date_updated,version';
		const DB_UPDATE = 'label,date_updated';
		const DB_SELECT = 'label,status,checksum,published_checksum,count_stat_view,count_stat_save,count_stat_submit,count_submit,count_submit_unread,id';

 		const FILE_ACCEPTED_MIME_TYPES = 'application/json';

		public function __construct() {

			global $wpdb;

			$this->id = 0;
			$this->checksum = '';
			$this->new_lookup = array();
			$this->new_lookup['form'] = array();
			$this->new_lookup['group'] = array();
			$this->new_lookup['section'] = array();
			$this->new_lookup['field'] = array();
			$this->label = __('New Form', 'ws-form');
			$this->meta = array();

			$this->table_name = sprintf('%s%sform', $wpdb->prefix, WS_FORM_DB_TABLE_PREFIX);
		}

		// Create form
		public function db_create($create_group = true) {

			// User capability check
			WS_Form_Common::user_must('create_form');

			global $wpdb;

			// Sanitize label
			self::sanitize_label(__('New Form', 'ws-form'));

			// Add form
			$sql = $wpdb->prepare(

				"INSERT INTO {$this->table_name} (" . self::DB_INSERT . ") VALUES (%s, %d, %s, %s, %s);",
				$this->label,
				get_current_user_id(),
				WS_Form_Common::get_mysql_date(),
				WS_Form_Common::get_mysql_date(),
				WS_FORM_VERSION
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error adding form', 'ws-form')); }

			// Get inserted ID
			$this->id = $wpdb->insert_id;

			// Build meta data object
			$settings_form_admin = WS_Form_Config::get_settings_form_admin();
			$meta_data = $settings_form_admin['sidebars']['form']['meta'];
			$meta_keys = WS_Form_Config::get_meta_keys();
			$meta_keys = apply_filters('wsf_form_create_meta_keys', $meta_keys);
			$meta_data_array = array_merge(self::build_meta_data($meta_data, $meta_keys), $this->meta);
			$meta_data_object = json_decode(wp_json_encode($meta_data_array));
			$meta_data_object = apply_filters('wsf_form_create_meta_data', $meta_data_object);

			// Build meta data
			$form_meta = New WS_Form_Meta();
			$form_meta->object = 'form';
			$form_meta->parent_id = $this->id;
			$form_meta->db_update_from_object($meta_data_object);

			// Build first group
			if($create_group) {

				$ws_form_group = New WS_Form_Group();
				$ws_form_group->form_id = $this->id;
				$ws_form_group->db_create();
			}

			// Run action
			do_action('wsf_form_create', $this);

			return $this->id;
		}

		public function db_create_from_template($id) {

			if(empty($id)) { return false; }

			// Create new form
			self::db_create(false);

			// Load template form data
			$ws_form_template = New WS_Form_Template();
			$ws_form_template->id = $id;
			$ws_form_template->read();
			$form_object = $ws_form_template->object;

			// Ensure form attributes are reset
			$form_object->status = 'draft';
			$form_object->count_submit = 0;
			$form_object->count_submit_unread = 0;
			$form_object->meta->breakpoint = '25';
			$form_object->meta->tab_index = '0';

			// Create form
			self::db_update_from_object($form_object, true, true);
			// Fix data - Conditional
			self::db_conditional_repair();
			// Fix data - Actions
			self::db_action_repair();

			// Fix data - Meta
			self::db_meta_repair();

			// Set checksum
			self::db_checksum();

			return $this->id;
		}

		// Legacy
		public function db_create_from_wizard($id) {

			self::db_create_from_template($id);
		}

		public function db_create_from_action($action_id, $list_id, $list_sub_id = false) {

			// Create new form
			self::db_create(false);

			if($this->id > 0) {

				// Modify form so it matches action list
				WS_Form_Action::update_form($this->id, $action_id, $list_id, $list_sub_id);

				return $this->id;

			} else {

				return false;
			}
		}

		public function db_create_from_hook($hook) {

			// Run hook to determine $action_id, $list_id and $list_sub_id
			try {

				$hook_return = apply_filters($hook, false);

			} catch (Exception $e) {

				$hook_return = array(

					'error' => true,
					'error_message' => $e->getMessage()
				);
			}

			// Check for errors
			if(
				isset($hook_return['error']) &&
				$hook_return['error']
			) {

				if(isset($hook_return['error_message'])) {

					// Store error so we can display it later
					WS_Form_Common::option_set('form_add_error', $hook_return['error_message']);
				}

				return false;
			}

			// Check hook return
			if(
				!is_array($hook_return) ||
				!isset($hook_return['error']) ||
				!isset($hook_return['action_id']) ||
				!isset($hook_return['list']) ||
				!isset($hook_return['list_fields'])
			) {

				return false;
			}

			// Read hook return data
			$action_id = $hook_return['action_id'];
			$list = $hook_return['list'];
			$list_fields = $hook_return['list_fields'];
			$list_fields_meta_data = isset($hook_return['list_fields_meta_data']) ? $hook_return['list_fields_meta_data'] : false;
			$form_fields = isset($hook_return['form_fields']) ? $hook_return['form_fields'] : false;
			$form_actions = isset($hook_return['form_actions']) ? $hook_return['form_actions'] : false;
			$form_conditionals = isset($hook_return['form_conditionals']) ? $hook_return['form_conditionals'] : false;
			$form_meta = isset($hook_return['form_meta']) ? $hook_return['form_meta'] : false;

			// Create new form
			self::db_create(false);

			// Check form created
			if($this->id > 0) {

				// Modify form so it matches action list
				WS_Form_Action::update_form($this->id, $action_id, false, false, $list, $list_fields, $list_fields_meta_data, $form_fields, $form_actions, $form_conditionals, $form_meta);

				return $this->id;

			} else {

				return false;
			}
		}

		// Read record to array
		public function db_read($get_meta = true, $get_groups = false, $checksum = false, $form_parse = false, $bypass_user_capability_check = false, $preview = false) {

			// User capability check
			WS_Form_Common::user_must('read_form', $bypass_user_capability_check);

			global $wpdb;

			$form_object = false;

			if($preview) {

				// Check preview template ID
				switch(sanitize_text_field(WS_Form_Common::get_query_var('wsf_preview_template_id'))) {

					case 'styler' :
						$preview_template_id = 'styler-pro';
						break;

					default :

						$preview_template_id = false;
				}

				// If valid preview template ID is set, then read the template
				if(!empty($preview_template_id)) {

					// Read styler form from template
					$ws_form_template = new WS_Form_Template();
					$ws_form_template->type = 'preview';
					$ws_form_template->id = $preview_template_id;

					// Get template
					try {

						$template = $ws_form_template->read();

					} catch (Exception $e) {

						parent::db_wpdb_handle_error(__('Unable to read preview template', 'ws-form'));
					}

					// Get form JSON
					$form_json = $template->json;

					// Decode to object
					$form_object = json_decode($form_json);

					// Set form object parameters
					$form_object->id = 1;
					$form_object->checksum = $form_object->published_checksum = self::db_checksum_process($form_object);
					$form_object->meta->style_id = absint(WS_Form_Common::get_query_var('wsf_preview_style_id'));
				}
			}

			if($form_object === false) {

				self::db_check_id();

				// Read form
				$sql = $wpdb->prepare(

					"SELECT " . self::DB_SELECT . " FROM {$this->table_name} WHERE id = %d AND NOT (status = 'trash') LIMIT 1;",
					$this->id
				);

				$form_array = $wpdb->get_row($sql, 'ARRAY_A');
				if(is_null($form_array)) { parent::db_wpdb_handle_error(__('Unable to read form', 'ws-form')); }

				// Process groups (Done first in case we are requesting only fields)
				if($get_groups) {

					// Read sections
					$ws_form_group = New WS_Form_Group();
					$ws_form_group->form_id = $this->id;
					$ws_form_group_return = $ws_form_group->db_read_all($get_meta, $checksum, $bypass_user_capability_check);

					$form_array['groups'] = $ws_form_group_return;
				}

				// Set class variables
				foreach($form_array as $key => $value) {

					$this->{$key} = $value;
				}

				// Process meta data
				if($get_meta) {

					// Read meta
					$ws_form_meta = New WS_Form_Meta();
					$ws_form_meta->object = 'form';
					$ws_form_meta->parent_id = $this->id;
					$metas = $ws_form_meta->db_read_all($bypass_user_capability_check);
					$form_array['meta'] = $this->meta = $metas;
				}

				// Convert into object
				$form_object = json_decode(wp_json_encode($form_array));
			}

			// Form parser
			if(isset($form_object->groups) && $form_parse) {

				$form_object = self::form_parse($form_object, false);
			}

			// Return array
			return $form_object;
		}

		// Read - Published data
		public function db_read_published($form_parse = false) {

			// No capabilities required, this is a public method

			global $wpdb;

			// Get contents of published field
			$sql = $wpdb->prepare(

				"SELECT checksum, published FROM {$this->table_name} WHERE id = %d AND NOT (status = 'trash') LIMIT 1;",
				$this->id
			);

			$published_row = $wpdb->get_row($sql);
			if(is_null($published_row)) { parent::db_wpdb_handle_error(__('Unable to read published form data', 'ws-form')); }

			// Read published JSON string
			$published_string = $published_row->published;

			// Empty published field (Never published)
			if($published_string == '') { return false; }

			// Inject latest checksum
			$form_object = json_decode($published_string);
			$form_object->checksum = $published_row->checksum;

			// Set label
			$this->label = $form_object->label;

			// Form parser
			if(isset($form_object->groups) && $form_parse) {

				$form_object = self::form_parse($form_object, true);
			}

			return $form_object;
		}

		// Set - Published
		public function db_publish($bypass_user_capability_check = false, $data_source_schedule_reset = true) {

			// User capability check
			WS_Form_Common::user_must('publish_form', $bypass_user_capability_check);

			global $wpdb;

			// Set form as published
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = 'publish', date_publish = %s, date_updated = %s WHERE id = %d LIMIT 1;",
				WS_Form_Common::get_mysql_date(),
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error publishing form', 'ws-form')); }

			// Read full form
			$form_object = self::db_read(true, true, false, false, $bypass_user_capability_check);

			// Update checksum
			self::db_checksum($bypass_user_capability_check);

			// Set checksums
			$form_object->checksum = $this->checksum;
			$form_object->published_checksum = $this->checksum;

			// Apply filters
			apply_filters('wsf_form_publish', $form_object);

			// JSON encode
			$form_json = wp_json_encode($form_object);

			// Publish form
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET published = %s, published_checksum = %s WHERE id = %d LIMIT 1;", 
				$form_json,
				$this->checksum,
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error publishing form', 'ws-form')); }

			// Do action
			do_action('wsf_form_publish', $form_object);

			if($data_source_schedule_reset) {

				// Clear data source scheduled events
				$ws_form_data_source_cron = new WS_Form_Data_Source_Cron();
				$ws_form_data_source_cron->schedule_clear_all($this->id);

				// Field types
				$field_types = WS_Form_Config::get_field_types_flat();

	 			// Form fields
				$fields = WS_Form_Common::get_fields_from_form($form_object, true);

				// Meta keys
				$meta_keys = false;

				// Process fields
				foreach($fields as $field) {

					if(!isset($field->type)) { continue; }

					// Get field type
					$field_type = $field->type;

					// Check to see if field type exists
					if(isset($field_types[$field_type])) {

						// Get field type config
						$field_type_config = $field_types[$field_type];

						// Remove layout editor only fields
						$layout_editor_only = isset($field_type_config['layout_editor_only']) ? $field_type_config['layout_editor_only'] : false;
						if($layout_editor_only) { continue; }

						// Data sources
						self::data_source_process($form_object, $meta_keys, $field, $field_type_config, true, 'db_publish');
					}
				}
			}
		}

		// Accessibility
		public function form_accessibility($form_object) {

 			// Form fields
			$fields = WS_Form_Common::get_fields_from_form($form_object, true);

			// Process fields
			foreach($fields as $field) {

				if(!isset($field->type)) { continue; }

				// Get suggested autocomplete
				$autocomplete = WS_Form_Common::label_to_autocomplete($field->label, $field->type);

				if($autocomplete !== false) {

					// Get keys
					$field_key = $field->field_key;
					$section_key = $field->section_key;
					$group_key = $field->group_key;

					// Set autocomplete
					$form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]->meta->autocomplete = $autocomplete;
				}
			}

			return $form_object;
		}

		// Parse form
		public function form_parse($form_object, $published = false) {

			// Field types
			$field_types = WS_Form_Config::get_field_types_flat();

 			// Form fields
			$fields = WS_Form_Common::get_fields_from_form($form_object, true);

			// Meta keys
			$meta_keys = WS_Form_Config::get_meta_keys();

			// Reset field keys
			$reset_field_keys = array();

			// Process fields
			foreach($fields as $field) {

				if(!isset($field->type)) { continue; }

				// Get field type
				$field_type = $field->type;

				// Check to see if field type exists
				if(!isset($field_types[$field_type])) { continue; }

				// Get field config
				$field_config = $field_types[$field_type];

				// Get keys
				$field_key = $field->field_key;
				$section_key = $field->section_key;
				$group_key = $field->group_key;

				// Remove layout editor only fields
				$layout_editor_only = isset($field_config['layout_editor_only']) ? $field_config['layout_editor_only'] : false;
				if($layout_editor_only) {

					// Remove field
					unset($form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]);

					// Add to reset_field_keys
					$reset_field_keys[$section_key] = array(

						'group_key' => $group_key,
						'section_key' => $section_key
					);

					continue;
				}

				// WPAutoP
				$wpautop_form_parse = isset($field_config['wpautop_form_parse']) ? $field_config['wpautop_form_parse'] : false;					
				if($wpautop_form_parse !== false) {

					if(!is_array($wpautop_form_parse)) { $wpautop_form_parse = array($wpautop_form_parse); }

					foreach($wpautop_form_parse as $wpautop_form_parse_meta_key) {

						// Check meta key exists
						if(!isset($field->meta->{$wpautop_form_parse_meta_key})) { continue; }

						// Update form_object
						$form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]->meta->{$wpautop_form_parse_meta_key} = wpautop($field->meta->{$wpautop_form_parse_meta_key});
					}
				}

				// do_shortcode
				$meta_do_shortcode = isset($field_config['meta_do_shortcode']) ? $field_config['meta_do_shortcode'] : false;
				if($meta_do_shortcode !== false) {

					if(!is_array($meta_do_shortcode)) { $meta_do_shortcode = array($meta_do_shortcode); }

					foreach($meta_do_shortcode as $meta_do_shortcode_meta_key) {

						// Check meta key exists
						if(!isset($field->meta->{$meta_do_shortcode_meta_key})) { continue; }

						// Update form_object
						$form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]->meta->{$meta_do_shortcode_meta_key} = WS_Form_Common::do_shortcode($field->meta->{$meta_do_shortcode_meta_key});
					}
				}

				// Parse field label with a scope of server
				if(isset($form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]->label)) {

					$form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]->label = WS_Form_Common::parse_variables_process($field->label, $form_object, false, 'text/html', 'form_parse');
				}

				if(isset($field->meta)) {

					foreach((array) $field->meta as $meta_key => $meta_value) {

						// Parse field meta data with a scope of form_parse
						if(
							is_string($meta_value) &&
							(strpos($meta_value, '#') !== false)
						) {

							$form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]->meta->{$meta_key} = WS_Form_Common::parse_variables_process($meta_value, $form_object, false, 'text/html', 'form_parse');
						}
					}
				}

				// Data sources
				self::data_source_process($form_object, $meta_keys, $field, $field_config, $published, 'form_parse');
			}

			// Key resetting is done to ensure when the form object is handled with JavaScript it is represented as an array and not as an object due to key offsets not starting at 0

			// Reset field keys
			foreach($reset_field_keys as $reset_field_key) {

				$group_key = $reset_field_key['group_key'];
				$section_key = $reset_field_key['section_key'];

				$form_object->groups[$group_key]->sections[$section_key]->fields = array_values($form_object->groups[$group_key]->sections[$section_key]->fields);
			}

			// Style ID
			if(WS_Form_Common::styler_enabled()) {

				$ws_form_style = new WS_Form_Style();

				// Get style ID
				$style_id = $ws_form_style->get_style_id_from_form_object($form_object);

				// Set style ID
				$form_object->meta->style_id = $style_id;

				// Check if style has alt enabled
				$ws_form_style->id = $style_id;
				$form_object->meta->style_alt = $ws_form_style->has_alt();

				// Get conversational style ID
				$style_id_conv = $ws_form_style->get_style_id_from_form_object($form_object, true);

				// Set conversational style ID
				$form_object->meta->style_id_conv = $style_id_conv;

				// Check if conversational style has alt enabled
				$ws_form_style->id = $style_id_conv;
				$form_object->meta->style_conv_alt = $ws_form_style->has_alt();
			}

			// Translate form
			$form_object = apply_filters('wsf_form_translate', $form_object);

			return $form_object;
		}

		// Change form data so it is public facing
		public function form_public(&$form_object) {

			// Filter actions
			$actions = array();
			if(
				isset($form_object->meta) &&
				isset($form_object->meta->action) &&
				isset($form_object->meta->action->groups) &&
				isset($form_object->meta->action->groups[0]) &&
				isset($form_object->meta->action->groups[0]->rows)
			) {

				foreach($form_object->meta->action->groups[0]->rows as $action) {

					if(
						!isset($action->id) ||
						is_null($action->id) ||
						(isset($action->disabled) && ($action->disabled == 'on')) ||
						!isset($action->data) ||
						!isset($action->data[1])
					) {
						continue;
					}

					// Get data
					$data = json_decode($action->data[1]);
					if(is_null($data)) { continue; }

					// Get action ID
					if(!isset($data->id)) { continue; }
					$action_id = $data->id;
					if(!isset(WS_Form_Action::$actions[$action_id])) { continue; }

					// Get events
					if(!isset($data->events)) { continue; }
					$events = $data->events;

					// Check for Conversion Tracking type
					$conversion_type = (

						($data->id === 'conversion') &&
						isset($data->meta) &&
						isset($data->meta->action_conversion_type)
					) ? $data->meta->action_conversion_type : false;

					$actions[] = array(

						'id' => $action->id,
						'save' => in_array('save', $events),
						'submit' => in_array('submit', $events),
						'conversion_type' => $conversion_type
					);
				}

				$form_object->meta->action = $actions;
			}

 			// Form fields
			$fields = WS_Form_Common::get_fields_from_form($form_object, true);

			// Meta keys
			$meta_keys_protected = array_fill_keys(WS_Form_Config::get_meta_keys_protected(), null);

			// Process fields
			foreach($fields as $field) {

				// Filter meta data
				if(isset($field->meta)) {

					// Remove matching meta keys
					foreach(array_intersect_key((array) $field->meta, $meta_keys_protected) as $meta_key => $meta_value) {

						// Unset meta data
						unset($form_object->groups[$field->group_key]->sections[$field->section_key]->fields[$field->field_key]->meta->{$meta_key});
					}
				}
			}

			// Hidden fields - Added on form submit
			$hidden_fields = apply_filters( 'wsf_submit_hidden_fields', array(), $form_object );
			$submit_hidden_fields = array();

			if(is_array($hidden_fields)) {

				foreach($hidden_fields as $hidden_field) {

					// Check hidden field is an array with name and value elements set
					if(
						!is_array($hidden_field) ||
						!isset($hidden_field['name']) ||
						!isset($hidden_field['value'])
					) {
						continue;
					}

					// Ensure type is set
					if(!isset($hidden_field['type'])) { $hidden_field['type'] = 'string'; }

					// Check type
					if(!in_array($hidden_field['type'], array('string', 'local_storage'))) { continue; }

					// Build hidden field
					$submit_hidden_field = array(

						'name' => $hidden_field['name'],
						'value' => $hidden_field['value'],
						'type' => $hidden_field['type']
					);

					// Check for ID
					if(isset($hidden_field['id'])) {

						$submit_hidden_field['id'] = $hidden_field['id'];
					}

					// Check for attributes
					if(isset($hidden_field['attributes'])) {

						$submit_hidden_field['attributes'] = $hidden_field['attributes'];
					}

					// Add hidden field
					$submit_hidden_fields[] = $submit_hidden_field;
				}
			}

			// Set submit hidden fields
			if(count($submit_hidden_fields) > 0) {

				$form_object->submit_hidden_fields = $submit_hidden_fields;
			}

			// Remove form object properties that are not required publicly
			$properties = array(

				'count_stat_view',
				'count_stat_save',
				'count_stat_submit',
				'count_submit',
				'count_submit_unread',
			);

			foreach($properties as $property) {

				if(property_exists($form_object, $property)) {

					unset($form_object->$property);
				}
			}
		}

		// Apply form restrictions
		public function apply_restrictions(&$form_object) {

			// Get user roles
			$current_user = wp_get_current_user();
			$user_roles = $current_user->roles;

			// Get groups
			$groups = isset($form_object->groups) ? $form_object->groups : array();

			// Reset keys
			$reset_group_keys = false;
			$reset_section_keys = array();
			$reset_field_keys = array();

			foreach($groups as $group_key => $group) {

				// Determine if group should show
				if(!WS_Form_Common::object_show($group, 'group', $current_user, $user_roles)) {

					// Unset group
					unset($form_object->groups[$group_key]);

					// Set reset_group_keys true
					$reset_group_keys = true;

					continue;
				}

				$sections = isset($group->sections) ? $group->sections : array();

				foreach($sections as $section_key => $section) {

					// Determine if section should show
					if(!WS_Form_Common::object_show($section, 'section', $current_user, $user_roles)) {

						// Unset section
						unset($form_object->groups[$group_key]->sections[$section_key]);

						// Add to reset_section_keys
						$reset_section_keys[$group_key] = array(

							'group_key' => $group_key
						);

						continue;
					}

					$fields = isset($section->fields) ? $section->fields : array();

					// Process fields
					foreach($fields as $field_key => $field) {

						// Determine if field should show
						if(!WS_Form_Common::object_show($field, 'field', $current_user, $user_roles)) {

							// Unset field
							unset($form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]);

							// Add to reset_field_keys
							$reset_field_keys[$section_key] = array(

								'group_key' => $group_key,
								'section_key' => $section_key
							);

							continue;
						}
					}
				}
			}

			// Key resetting is done to ensure when the form object is handled with JavaScript it is represented as an array and not as an object due to key offsets not starting at 0

			// Reset field keys
			foreach($reset_field_keys as $reset_field_key) {

				$group_key = $reset_field_key['group_key'];
				$section_key = $reset_field_key['section_key'];

				$form_object->groups[$group_key]->sections[$section_key]->fields = array_values($form_object->groups[$group_key]->sections[$section_key]->fields);
			}

			// Reset section keys
			foreach($reset_section_keys as $reset_section_key) {

				$group_key = $reset_section_key['group_key'];

				$form_object->groups[$group_key]->sections = array_values($form_object->groups[$group_key]->sections);
			}

			// Reset group keys
			if($reset_group_keys) {

				$form_object->groups = array_values($form_object->groups);
			}
		}

		// Data source processing
		public function data_source_process(&$form_object, &$meta_keys, $field, $field_config, $published = false, $mode = 'form_parse') {

			$data_source = isset($field_config['data_source']) ? $field_config['data_source'] : false;
			if(
				($data_source === false) ||
				!isset($data_source['id'])
			) {

				return false;
			}

			// Get meta key
			$meta_key = $data_source['id'];

			// Get meta keys if not set
			if($meta_keys === false) { $meta_keys = WS_Form_Config::get_meta_keys(); }

			if(!isset($meta_keys[$meta_key])) { return false; }

			$meta_key_config = $meta_keys[$meta_key];

			// Check if data source enabled
			$data_source_enabled = isset($meta_key_config['data_source']) ? $meta_key_config['data_source'] : false;

			if(!$data_source_enabled) { return false; }

			// Check if data source ID is set
			$data_source_id = WS_Form_Common::get_object_meta_value($field, 'data_source_id', '');

			if(
				($data_source_id === '') ||
				!isset(WS_Form_Data_Source::$data_sources[$data_source_id]) ||
				!method_exists(WS_Form_Data_Source::$data_sources[$data_source_id], 'get_data_source_meta_keys')
			) {

				return false;
			}

			$data_source = WS_Form_Data_Source::$data_sources[$data_source_id];

			// Get meta keys
			$config_meta_keys = $data_source->config_meta_keys();

			// Get data source meta keys
			$data_source_meta_keys = $data_source->get_data_source_meta_keys();

			// Configure
			$recurrence_found = false;
			foreach($data_source_meta_keys as $data_source_meta_key) {

				$meta_value_default = isset($config_meta_keys[$data_source_meta_key]['default']) ? $config_meta_keys[$data_source_meta_key]['default'] : false;

				$data_source->{$data_source_meta_key} = WS_Form_Common::get_object_meta_value($field, $data_source_meta_key, $meta_value_default);
				if($data_source_meta_key == 'data_source_recurrence') { $recurrence_found = true; }
			}
			if($recurrence_found) {

				// Check for update frequency
				$recurrence = WS_Form_Common::get_object_meta_value($field, 'data_source_recurrence', 'wsf_realtime');
				if(empty($recurrence)) { $recurrence = 'wsf_realtime'; }

			} else {

				$recurrence = 'wsf_realtime';
			}

			// Form parse - Only run if realtime or if previewing the form
			if(
				($mode === 'form_parse') &&

				(
					!$published ||
					($recurrence === 'wsf_realtime')
				)
			) {

				// Get existing meta_value
				$meta_value = WS_Form_Common::get_object_meta_value($field, $meta_key, false);

				// Get replacement meta_value
				$get_return = $data_source->get($form_object, $field->id, 1, $meta_key, $meta_value, true);	// true = form_parse to ignore paging

				// Error checking
				if(!(isset($get_return['error']) && $get_return['error'])) {

					// Get keys
					$field_key = $field->field_key;
					$section_key = $field->section_key;
					$group_key = $field->group_key;

					// Set meta_key
					$form_object->groups[$group_key]->sections[$section_key]->fields[$field_key]->meta->{$meta_key} = $get_return['meta_value'];

					// Check if data source ID is set
					$data_source_last_api_error = WS_Form_Common::get_object_meta_value($field, 'data_source_last_api_error', '');

					// Clear last_api_error
					if($data_source_last_api_error !== '') {

						$ws_form_field = new WS_Form_Field();
						$ws_form_field->id = $field->id;
						$ws_form_field->db_last_api_error_clear();
					}
				}
			}

			// Publishing
			if($mode === 'db_publish') {

				// Clear last_api_error
				$ws_form_field = new WS_Form_Field();
				$ws_form_field->id = $field->id;
				$ws_form_field->db_last_api_error_clear();

				// If real time don't set up scheduled event
				if($recurrence === 'wsf_realtime') { return; }

				// Add scheduled event
				$ws_form_data_source_cron = new WS_Form_Data_Source_Cron();

				// Add new event
				$ws_form_data_source_cron->schedule_add($form_object->id, $field->id, $recurrence);

				// Initial run so that published form has data
				$ws_form_data_source_cron->schedule_run($form_object->id, $field->id);
			}
		}

		// Set - Draft
		public function db_draft() {

			// User capability check
			WS_Form_Common::user_must('publish_form');

			global $wpdb;

			// Set form as draft
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = 'draft', date_publish = '', date_updated = %s, published = '', published_checksum = '' WHERE id = %d LIMIT 1;",
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error drafting form', 'ws-form')); }

			// Read full form
			$form_object = self::db_read(true, true);

			// Update checksum
			self::db_checksum();
		}

		// Import reset
		public function db_import_reset() {

			// User capability check
			WS_Form_Common::user_must('import_form');

			global $wpdb;

			// Delete meta
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'form';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_delete_by_object();

			// Delete form groups
			$ws_form_group = New WS_Form_Group();
			$ws_form_group->form_id = $this->id;
			$ws_form_group->db_delete_by_form(false);

			// Set form as draft
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = 'draft', date_publish = NULL, date_updated = %s, published = '', published_checksum = NULL WHERE id = %d LIMIT 1;",
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error resetting form', 'ws-form')); }
		}

		// Read - Recent
		public function db_read_recent($limit = 10) {

			return self::db_read_all('', " NOT (status = 'trash')", 'date_updated DESC', $limit, '', false);
		}

		// Read - All
		public function db_read_all($join = '', $where = '', $order_by = '', $limit = '', $offset = '', $count_submit_update_all = true, $bypass_user_capability_check = false, $select = '') {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_form')) { return false; }

			global $wpdb;

			// Update count submit on all forms
			if($count_submit_update_all) { self::db_count_update_all(); }

			// Get form data
			if($select == '') { $select = self::DB_SELECT; }
			
			if($join != '') {

				$select_array = explode(',', $select);
				foreach($select_array as $key => $select) {

					$select_array[$key] = $this->table_name . '.' . $select;
				}
				$select = implode(',', $select_array);
			}

			$sql = "SELECT {$select} FROM {$this->table_name}";

			if($join != '') { $sql .= sprintf(" %s", $join); }
			if($where != '') { $sql .= sprintf(" WHERE %s", $where); }
			if($order_by != '') { $sql .= sprintf(" ORDER BY %s", $order_by); }
			if($limit != '') { $sql .= sprintf(" LIMIT %u", absint($limit)); }
			if($offset != '') { $sql .= sprintf(" OFFSET %u", absint($offset)); }

			$sql .= ';';

			return $wpdb->get_results($sql, 'ARRAY_A');	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		// Delete
		public function db_delete() {

			// User capability check
			WS_Form_Common::user_must('delete_form');

			global $wpdb;

			self::db_check_id();

			// Get status
			$sql = $wpdb->prepare(

				"SELECT status FROM {$this->table_name} WHERE id = %d;",
				$this->id
			);

			$status = $wpdb->get_var($sql);
			if(is_null($status)) { return false; }

			// If status is trashed, do a permanent delete of the data
			if($status == 'trash') {

				// Delete meta
				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'form';
				$ws_form_meta->parent_id = $this->id;
				$ws_form_meta->db_delete_by_object();

				// Delete form groups
				$ws_form_group = New WS_Form_Group();
				$ws_form_group->form_id = $this->id;
				$ws_form_group->db_delete_by_form(false);

				// Delete form stats
				$ws_form_form_stat = New WS_Form_Form_Stat();
				$ws_form_form_stat->form_id = $this->id;
				$ws_form_form_stat->db_delete();

				// Delete form
				$sql = $wpdb->prepare(

					"DELETE FROM {$this->table_name} WHERE id = %d;",
					$this->id
				);
	
				if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error deleting form', 'ws-form')); }

				// Delete submission hidden column meta
				delete_user_option(get_current_user_id(), 'managews-form_page_ws-form-submitcolumnshidden-' . $this->id, !is_multisite());

				// Delete conversational permalink
				$conversational_permalinks = WS_Form_Common::option_get('conversational_permalinks');
				if(is_array($conversational_permalinks) && isset($conversational_permalinks[$this->id])) {

					unset($conversational_permalinks[$this->id]);
					WS_Form_Common::option_set('conversational_permalinks', $conversational_permalinks);
				}
				// Do action
				do_action('wsf_form_delete', $this->id);

			} else {

				// Set status to 'trash'
				self::db_set_status('trash');

				// Do action
				do_action('wsf_form_trash', $this->id);
			}

			// Clear data source scheduled events
			$ws_form_data_source_cron = new WS_Form_Data_Source_Cron();
			$ws_form_data_source_cron->schedule_clear_all($this->id);

			return true;
		}

		// Delete trashed forms
		public function db_trash_delete() {

			// Get all trashed forms
			$forms = self::db_read_all('', "status='trash'");

			foreach($forms as $form) {

				$this->id = $form['id'];
				self::db_delete();
			}

			return true;
		}

		// Clone
		public function db_clone() {

			// User capability check
			WS_Form_Common::user_must('create_form');

			global $wpdb;

			// Read form data
			$form_object = self::db_read(true, true);

			// Clone form
			$sql = $wpdb->prepare(

				"INSERT INTO {$this->table_name} (" . self::DB_INSERT . ") VALUES (%s, %d, %s, %s, %s);",
				sprintf(__('%s (Copy)', 'ws-form'), $this->label),
				get_current_user_id(),
				WS_Form_Common::get_mysql_date(),
				WS_Form_Common::get_mysql_date(),
				WS_FORM_VERSION
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error cloning form', 'ws-form')); }

			// Get new form ID
			$this->id = $wpdb->insert_id;

			// Build form (As new)
			self::db_update_from_object($form_object, true, true);
			// Fix data - Conditional
			self::db_conditional_repair();
			// Fix data - Action
			self::db_action_repair();

			// Fix data - Meta
			self::db_meta_repair();

			// Update checksum
			self::db_checksum();

			// Update form label
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET label =  '%s' WHERE id = %d;",
				sprintf(__('%s (Copy)', 'ws-form'), $this->label),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating form label', 'ws-form')); }

			return $this->id;
		}

		// Restore
		public function db_restore() {

			// User capability check
			WS_Form_Common::user_must('delete_form');

			// Draft
			self::db_draft();

			// Do action
			do_action('wsf_form_restore', $this->id);
		}

		// Set status of form
		public function db_set_status($status) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			global $wpdb;

			self::db_check_id();

			// Ensure provided form status is valid
			if(WS_Form_Common::check_form_status($status) == '') {

				parent::db_throw_error(sprintf(__('Invalid form status: %s', 'ws-form'), $status));
			}

			// Update form record
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = '%s' WHERE id = %d LIMIT 1;",
				$status,
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting form status', 'ws-form')); }

			return true;
		}

		// Get form status name
		public function db_get_status_name($status) {

			switch($status) {

				case 'draft' : 		return __('Draft', 'ws-form'); break;
				case 'publish' : 	return __('Published', 'ws-form'); break;
				case 'trash' : 		return __('Trash', 'ws-form'); break;
				default :			return $status;
			}
		}

		// Update all count_submit values
		public function db_count_update_all($bypass_user_capability_check = false) {

			// Update form submit count
			global $wpdb;

			// Get all forms
			$sql = "SELECT id,count_stat_view,count_stat_save,count_stat_submit,count_submit,count_submit_unread FROM {$this->table_name}";

			$forms = $wpdb->get_results($sql, 'ARRAY_A');

			foreach($forms as $form) {

				$this->id = $form['id'];

				// Update
				self::db_count_update($form, $bypass_user_capability_check);
			}
		}

		// Set count fields
		public function db_count_update($form = false, $bypass_user_capability_check = false) {

			global $wpdb;

			self::db_check_id();

			// Get form stat totals
			if($this->count_update_ws_form_form_stat === false) {

				$this->count_update_ws_form_form_stat = new WS_Form_Form_Stat();
			}
			$this->count_update_ws_form_form_stat->form_id = $this->id;
			$count_array = $this->count_update_ws_form_form_stat->db_get_counts_cached();

			// Get form submit total
			if($this->count_update_ws_form_submit === false) {

				$this->count_update_ws_form_submit = new WS_Form_Submit();
			}
			$this->count_update_ws_form_submit->form_id = $this->id;
			$count_submit = $this->count_update_ws_form_submit->db_get_count_submit_cached($bypass_user_capability_check);
			$count_submit_unread = $this->count_update_ws_form_submit->db_get_count_submit_unread_cached($bypass_user_capability_check);

			// Check if new values are different from existing values
			$data_same = (

				$form &&
				(absint($count_array['count_view']) == $form['count_stat_view']) &&
				(absint($count_array['count_save']) == $form['count_stat_save']) &&
				(absint($count_array['count_submit']) == $form['count_stat_submit']) &&
				(absint($count_submit) == $form['count_submit']) &&
				(absint($count_submit_unread) == $form['count_submit_unread'])
			);

			if(!$data_same) {

				// Update form record
				$sql = $wpdb->prepare(

					"UPDATE {$this->table_name} SET count_stat_view = %d, count_stat_save = %d, count_stat_submit = %d, count_submit = %d, count_submit_unread = %d WHERE id = %d LIMIT 1;",
					absint($count_array['count_view']),
					absint($count_array['count_save']),
					absint($count_array['count_submit']),
					absint($count_submit),
					absint($count_submit_unread),
					$this->id
				);

				if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating counts', 'ws-form')); }
			}
		}

		// Reset count fields
		public function db_count_reset($form = false) {

			global $wpdb;

			self::db_check_id();

			// Update form record
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET count_stat_view = 0, count_stat_save = 0, count_stat_submit = 0 WHERE id = %d LIMIT 1;",
				$this->id
			);
			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error resetting counts', 'ws-form')); }
		}

		// Set count_submit_unread
		public function db_update_count_submit_unread($bypass_user_capability_check = false) {

			global $wpdb;

			self::db_check_id();

			// Get form submit total
			$ws_form_submit = New WS_Form_Submit();
			$ws_form_submit->form_id = $this->id;
			$count_submit_unread = $ws_form_submit->db_get_count_submit_unread_cached($bypass_user_capability_check);

			// Update form record
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET count_submit_unread = %d WHERE id = %d LIMIT 1;",
				absint($count_submit_unread),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating submit unread count', 'ws-form')); }
		}

		// Get total submissions unread
		public function db_get_count_submit_unread_total() {

			global $wpdb;

			$sql = "SELECT SUM(count_submit_unread) AS count_submit_unread FROM {$this->table_name} WHERE status IN ('publish', 'draft');";

			$count_submit_unread = $wpdb->get_var($sql);

			return empty($count_submit_unread) ? 0 : absint($count_submit_unread);
		}

		// Get checksum of current form and store it to database
		public function db_checksum($bypass_user_capability_check = false) {

			global $wpdb;

			self::db_check_id();

			// Get form data
			$form_object = self::db_read(true, true, true, false, $bypass_user_capability_check);

			// MD5
			$this->checksum = self::db_checksum_process($form_object);

			// Update form record
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET checksum = '%s' WHERE id = %d LIMIT 1;",
				$this->checksum,
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting checksum', 'ws-form')); }

			return $this->checksum;
		}

		// Process checksum
		public function db_checksum_process($form_object) {

			// Remove any variables that change each time checksum calculated or don't affect the public form
			unset($form_object->checksum);
			unset($form_object->published_checksum);
			unset($form_object->meta->tab_index);
			unset($form_object->meta->breakpoint);

			// Serialize
			$form_serialized = serialize($form_object);

			// MD5
			return md5($form_serialized);
		}

		// Get form count by status
		public function db_get_count_by_status($status = '') {

			global $wpdb;

			$status = WS_Form_Common::check_form_status($status);

			if($status == '') {

				$sql = "SELECT COUNT(id) FROM {$this->table_name} WHERE NOT (status = 'trash')";

			} else {

				$sql = $wpdb->prepare(

					"SELECT COUNT(id) FROM {$this->table_name} WHERE status = %s",
					$status
				);
			}

			$form_count = $wpdb->get_var($sql);
			if(is_null($form_count)) { $form_count = 0; }

			return $form_count; 
		}

		// Push form from array (if full, include all groups, sections, fields)
		public function db_update_from_object($form_object, $full = true, $new = false, $replace_meta = false) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			// Store old form ID
			$form_object_id_old = isset($form_object->id) ? $form_object->id : false;

			// Check for form ID in $form_object
			if(isset($form_object->id) && !$new) { $this->id = absint($form_object->id); }

			if(!$new) { self::db_check_id(); }

			// Update / Insert
			$this->id = parent::db_update_insert($this->table_name, self::DB_UPDATE, self::DB_INSERT, $form_object, 'form', $this->id, false);

			// Add to lookups
			if($form_object_id_old !== false) {

				$this->new_lookup['form'][$form_object_id_old] = $this->id;
			}

			// Base meta for new records
			if(!isset($form_object->meta) || !is_object($form_object->meta)) { $form_object->meta = new stdClass(); }
			if($new) {

				$settings_form_admin = WS_Form_Config::get_settings_form_admin();
				$meta_data = $settings_form_admin['sidebars']['form']['meta'];
				$meta_keys = WS_Form_Config::get_meta_keys();
				$meta_keys = apply_filters('wsf_form_create_meta_keys', $meta_keys);
				$meta_data_array = array_merge(self::build_meta_data($meta_data, $meta_keys), (array) $form_object->meta);
				$meta_data_object = json_decode(wp_json_encode($meta_data_array));
				$form_object->meta = apply_filters('wsf_form_create_meta_data', $meta_data_object);
			}

			// Update meta
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'form';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_update_from_object($form_object->meta, false, false, $replace_meta);

			// Full update?
			if($full) {

				// Update groups
				$ws_form_group = New WS_Form_Group();
				$ws_form_group->form_id = $this->id;
				$ws_form_group->db_update_from_array($form_object->groups, $new, $replace_meta);

				if($new) {

					$this->new_lookup['group'] = $this->new_lookup['group'] + $ws_form_group->new_lookup['group'];
					$this->new_lookup['section'] = $this->new_lookup['section'] + $ws_form_group->new_lookup['section'];
					$this->new_lookup['field'] = $this->new_lookup['field'] + $ws_form_group->new_lookup['field'];
				}
			}

			return $this->id;
		}

		// Conditional repair (Repairs a duplicated conditional and replaces with new_lookup values)
		public function db_conditional_repair($filter_conditional_row_indexes = false) {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			// Get conditional configuration
			$settings_conditional = WS_Form_Config::get_settings_conditional(false);

			// Get parse variables repairable
			$parse_variables_repairable = WS_Form_Config::get_parse_variables_repairable();

			// Check form ID
			self::db_check_id();

			// Read conditional
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'form';
			$ws_form_meta->parent_id = $this->id;
			$conditional = $ws_form_meta->db_get_object_meta('conditional');

			// Data integrity check
			if(!isset($conditional->groups)) { return true; }
			if(!isset($conditional->groups[0])) { return true; }
			if(!isset($conditional->groups[0]->rows)) { return true; }

			// Run through each conditional (data grid rows)
			$rows = $conditional->groups[0]->rows;

			foreach($rows as $row_index => $row) {

				// Filter by row index
				if(
					($filter_conditional_row_indexes !== false) &&
					!in_array($row_index, $filter_conditional_row_indexes)
				) {

					continue;
				}

				// Data integrity check
				if(!isset($row->data)) { continue; }
				if(!isset($row->data[1])) { continue; }

				$data = $row->data[1];

				// Data integrity check
				if(gettype($data) !== 'string') { continue; }
				if($data == '') { continue; }

				// Converts conditional JSON string to object
				$conditional_json_decode = json_decode($data);
				if(is_null($conditional_json_decode)) { continue; }

				// Process IF conditions
				$if = $conditional_json_decode->if;

				// Run through each group in IF
				foreach($if as $group) {

					if(!isset($group->conditions)) { continue; }
					$if_then_elses = $group->conditions;

					// Run through each IF
					foreach($if_then_elses as $if_then_else) {

						self::db_conditional_repair_process($settings_conditional, $parse_variables_repairable, $if_then_else);
					}
				}

				// Run through each THEN
				$if_then_elses = $conditional_json_decode->then;

				foreach($if_then_elses as $if_then_else) {

					self::db_conditional_repair_process($settings_conditional, $parse_variables_repairable, $if_then_else);
				}

				// Run through each ELSE
				$if_then_elses = $conditional_json_decode->else;

				foreach($if_then_elses as $if_then_else) {

					self::db_conditional_repair_process($settings_conditional, $parse_variables_repairable, $if_then_else);
				}

				// Write conditional
				$conditional_json_encode = wp_json_encode($conditional_json_decode);
				$conditional->groups[0]->rows[$row_index]->data[1] = $conditional_json_encode;
				$meta_data_array = array('conditional' => $conditional);
				$ws_form_meta->db_update_from_array($meta_data_array);
			}
		}

		// Condition repair - Process
		public function db_conditional_repair_process($settings_conditional, $parse_variables_repairable, $if_then_else) {

			if(
				isset($if_then_else->object) &&
				isset($this->new_lookup[$if_then_else->object])
			) {

				// Straight swap - Object ID
				if(isset($this->new_lookup[$if_then_else->object][$if_then_else->object_id])) {

					$if_then_else->object_id = $this->new_lookup[$if_then_else->object][$if_then_else->object_id];
				}

				// Straight swap - Value
				if(
					isset($if_then_else->logic) &&
					isset($this->new_lookup[$if_then_else->object][$if_then_else->value])
				) {

					// Check to see if this logic is for a field (e.g. the logic has a field selector)
					if(
						isset($settings_conditional['objects'][$if_then_else->object]) &&
						isset($settings_conditional['objects'][$if_then_else->object]['logic']) &&
						isset($settings_conditional['objects'][$if_then_else->object]['logic'][$if_then_else->logic]) &&
						isset($settings_conditional['objects'][$if_then_else->object]['logic'][$if_then_else->logic]['values']) &&
						($settings_conditional['objects'][$if_then_else->object]['logic'][$if_then_else->logic]['values'] === 'fields')
					) {

						$if_then_else->value = $this->new_lookup[$if_then_else->object][$if_then_else->value];
					}
				}
			}

			// String replace in value
			if(isset($if_then_else->value)) {

				foreach($parse_variables_repairable as $object => $parse_variables) {

					foreach($this->new_lookup[$object] as $field_id_old => $field_id_new) {

						foreach($parse_variables as $parse_variable) {

							$if_then_else->value = str_replace('#' . $parse_variable . '(' . $field_id_old . ')', ($field_id_new != '') ? '#' . $parse_variable . '(' . $field_id_new . ')' : '', $if_then_else->value);
							$if_then_else->value = str_replace('#' . $parse_variable . '(' . $field_id_old . ',', ($field_id_new != '') ? '#' . $parse_variable . '(' . $field_id_new . ',' : '', $if_then_else->value);
						}
					}
				}
			}
		}
		// Action repair (Repairs a duplicated action and replaces with new_lookup values)
		public function db_action_repair() {

			// User capability check
			WS_Form_Common::user_must('edit_form');

			// Get parse variables repairable
			$parse_variables_repairable = WS_Form_Config::get_parse_variables_repairable();

			// Get meta keys
			$meta_keys = WS_Form_Config::get_meta_keys();

			// Check form ID
			self::db_check_id();

			// Read action
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'form';
			$ws_form_meta->parent_id = $this->id;
			$action = $ws_form_meta->db_get_object_meta('action');

			// Data integrity check
			if(!isset($action->groups)) { return true; }
			if(!isset($action->groups[0])) { return true; }
			if(!isset($action->groups[0]->rows)) { return true; }

			// Run through each action (data grid rows)
			$rows = $action->groups[0]->rows;

			foreach($rows as $row_index => $row) {

				// Data integrity check
				if(!isset($row->data)) { continue; }
				if(!isset($row->data[1])) { continue; }

				$data = $row->data[1];

				// Data integrity check
				if(gettype($data) !== 'string') { continue; }
				if($data == '') { continue; }

				// Converts action JSON string to object
				$action_json_decode = json_decode($data);
				if(is_null($action_json_decode)) { continue; }

				$action_id = $action_json_decode->id;

				// Skip actions that are not installed
				if(!isset(WS_Form_Action::$actions[$action_id])) { continue; }

				// Process metas
				$metas = $action_json_decode->meta;

				// Run through each meta
				foreach($metas as $meta_key => $meta_value) {

					if(is_array($meta_value)) {

						foreach($meta_value as $repeater_key => $repeater_row) {

							if(isset($repeater_row->ws_form_field)) {

								$ws_form_field = $repeater_row->ws_form_field;

								if(isset($this->new_lookup['field']) && isset($this->new_lookup['field'][$ws_form_field])) {

									$metas->{$meta_key}[$repeater_key]->ws_form_field = $this->new_lookup['field'][$ws_form_field];
								}
							}

							foreach($repeater_row as $key => $value) {

								// String replace - Field
								foreach($this->new_lookup['field'] as $field_id_old => $field_id_new) {

									foreach($parse_variables_repairable['field'] as $parse_variable) {

										$metas->{$meta_key}[$repeater_key]->{$key} = str_replace('#' . $parse_variable . '(' . $field_id_old . ')', ($field_id_new != '') ? '#' . $parse_variable . '(' . $field_id_new . ')' : '',$metas->{$meta_key}[$repeater_key]->{$key});
										$metas->{$meta_key}[$repeater_key]->{$key} = str_replace('#' . $parse_variable . '(' . $field_id_old . ',', ($field_id_new != '') ? '#' . $parse_variable . '(' . $field_id_new . ',' : '',$metas->{$meta_key}[$repeater_key]->{$key});
									}
								}

								// String replace - Section
								foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

									foreach($parse_variables_repairable['section'] as $parse_variable) {

										$metas->{$meta_key}[$repeater_key]->{$key} = str_replace('#' . $parse_variable . '(' . $section_id_old . ')', ($section_id_new != '') ? '#' . $parse_variable . '(' . $section_id_new . ')' : '', $metas->{$meta_key}[$repeater_key]->{$key});
									}
								}
							}
						}

					} else {

						// Direct numeric replacement (e.g. field selector)
						if(
							isset($this->new_lookup['field']) &&
							isset($this->new_lookup['field'][$meta_value]) &&
							isset($meta_keys[$meta_key]) &&
							isset($meta_keys[$meta_key]['type']) &&
							($meta_keys[$meta_key]['type'] == 'select')
						) {
							$metas->{$meta_key} = $this->new_lookup['field'][$meta_value];
						}

						// String replace - Field
						foreach($this->new_lookup['field'] as $field_id_old => $field_id_new) {

							foreach($parse_variables_repairable['field'] as $parse_variable) {

								// Whole
								$metas->{$meta_key} = str_replace('#' . $parse_variable . '(' . $field_id_old . ')', ($field_id_new != '') ? '#' . $parse_variable . '(' . $field_id_new . ')' : '', $metas->{$meta_key});

								// Start
								$metas->{$meta_key} = str_replace('#' . $parse_variable . '(' . $field_id_old . ',', ($field_id_new != '') ? '#' . $parse_variable . '(' . $field_id_new . ',' : '', $metas->{$meta_key});

								// Mid
								$metas->{$meta_key} = str_replace(',' . $field_id_old . ',', ($field_id_new != '') ? ',' . $field_id_new . ',' : '', $metas->{$meta_key});
								$metas->{$meta_key} = str_replace(', ' . $field_id_old . ',', ($field_id_new != '') ? ', ' . $field_id_new . ',' : '', $metas->{$meta_key});

								// End
								$metas->{$meta_key} = str_replace(',' . $field_id_old . ')', ($field_id_new != '') ? ',' . $field_id_new . ')' : '', $metas->{$meta_key});
								$metas->{$meta_key} = str_replace(', ' . $field_id_old . ')', ($field_id_new != '') ? ', ' . $field_id_new . ')' : '', $metas->{$meta_key});
							}
						}

						// String replace - Section
						foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

							foreach($parse_variables_repairable['section'] as $parse_variable) {

								$metas->{$meta_key} = str_replace('#' . $parse_variable . '(' . $section_id_old . ')', ($section_id_new != '') ? '#' . $parse_variable . '(' . $section_id_new . ')' : '', $metas->{$meta_key});
							}
						}
					}
				}

				// Write action
				$action_json_encode = wp_json_encode($action_json_decode);
				$action->groups[0]->rows[$row_index]->data[1] = $action_json_encode;
				$meta_data_array = array('action' => $action);
				$ws_form_meta->db_update_from_array($meta_data_array);
			}
		}

		// Meta repair - Update any field references in meta data
		public function db_meta_repair($filter_group_ids = false, $filter_section_ids = false) {

			// Get form object
			$form_object = self::db_read(true, true);

			// Get field meta
			$meta_keys = WS_Form_Config::get_meta_keys();

			// Look for field meta that uses fields for option lists, and also repeater fields
			$meta_key_check = array();
			foreach($meta_keys as $meta_key => $meta_key_config) {

				// Check for meta_keys that contain #section_id
				if(isset($meta_key_config['default']) && ($meta_key_config['default'] === '#section_id')) {

					$meta_key_check[$meta_key] = array('repeater' => false, 'section_id' => true, 'meta_key' => $meta_key);
					continue;
				}

				// Check for meta_keys that use field for options
				if(isset($meta_key_config['options']) && ($meta_key_config['options'] === 'fields')) {

					$meta_key_check[$meta_key] = array('repeater' => false, 'section_id' => false, 'meta_key' => $meta_key);
					continue;
				}

				// Check for meta_keys that use fields for repeater fields
				if(isset($meta_key_config['type']) && ($meta_key_config['type'] === 'repeater')) {

					if(!isset($meta_key_config['meta_keys'])) { continue; }

					foreach($meta_key_config['meta_keys'] as $meta_key_repeater) {

						if(!isset($meta_keys[$meta_key_repeater])) { continue; }

						$meta_key_repeater_config = $meta_keys[$meta_key_repeater];

						if(isset($meta_key_repeater_config['key'])) {

							$meta_key_repeater = $meta_key_repeater_config['key'];
						}

						if(isset($meta_key_repeater_config['options']) && ($meta_key_repeater_config['options'] === 'fields')) {

							$meta_key_check[$meta_key] = array('repeater' => true, 'section_id' => false, 'meta_key' => $meta_key_repeater);
							continue;
						}
					}
				}
			}

			// Repair form (unless we're filtering by section IDs)
			if(
				($filter_group_ids === false) &&
				($filter_section_ids === false)
			) {

				self::db_meta_repair_process($form_object, 'form', $meta_key_check);
			}

			// Run through each field and look for these meta keys
			$fields = WS_Form_Common::get_fields_from_form($form_object, true, $filter_group_ids, $filter_section_ids);
			foreach($fields as $field) {

				self::db_meta_repair_process($field, 'field', $meta_key_check);
			}
		}

		// Meta repair - Process
		public function db_meta_repair_process($object, $object_type, $meta_key_check) {

			// Get field meta as array
			$object_meta = (array) $object->meta;
			if(count($object_meta) == 0) { return; }

			$object_update = false;
			$object_meta_update = false;

			// Find meta keys that contain only field numbers to make sure we don't update other numeric values
			$keys_to_process = array_intersect_key($object_meta, $meta_key_check);
			foreach($keys_to_process as $meta_key => $meta_value) {

				// Check for repeater
				$repeater = $meta_key_check[$meta_key]['repeater'];
				if($repeater && is_array($object_meta[$meta_key])) {

					$repeater_meta_key = $meta_key_check[$meta_key]['meta_key'];

					foreach($object_meta[$meta_key] as $repeater_index => $repeater_row) {

						$meta_value = absint($object_meta[$meta_key][$repeater_index]->{$repeater_meta_key});

						if(isset($this->new_lookup['field']) && isset($this->new_lookup['field'][$meta_value])) {

							$object_meta[$meta_key][$repeater_index]->{$repeater_meta_key} = $this->new_lookup['field'][$meta_value];
							$object_meta_update = true;
						}
					}
				}

				// Check for section_id
				$section_id = $meta_key_check[$meta_key]['section_id'];
				if($section_id) {

					$section_id_meta_key = $meta_key_check[$meta_key]['meta_key'];
					$section_id_old = $object_meta[$section_id_meta_key];
					if(isset($this->new_lookup['section']) && isset($this->new_lookup['section'][$section_id_old])) {

						$object_meta[$section_id_meta_key] = $this->new_lookup['section'][$section_id_old];
						$object_meta_update = true;
					}

				} else {

					$meta_value = absint($object_meta[$meta_key]);

					if(isset($this->new_lookup['field']) && isset($this->new_lookup['field'][$meta_value])) {

						$object_meta[$meta_key] = $this->new_lookup['field'][$meta_value];
						$object_meta_update = true;
					}
				}
			}

			// Get parse variables repairable
			$parse_variables_repairable = WS_Form_Config::get_parse_variables_repairable();

			// Variable replace
			foreach($this->new_lookup['field'] as $object_id_old => $object_id_new) {

				foreach($parse_variables_repairable['field'] as $parse_variable) {

					$replace_this_array = array(

						// Whole
						'#' . $parse_variable . '(' . $object_id_old . ')' => '#' . $parse_variable . '(' . $object_id_new . ')',

						// Start
						'#' . $parse_variable . '(' . $object_id_old . ',' => '#' . $parse_variable . '(' . $object_id_new . ',',

						// Mid
						',' . $object_id_old . ',' => ',' . $object_id_new . ',',
						', ' . $object_id_old . ',' => ', ' . $object_id_new . ',',

						// End
						',' . $object_id_old . ')' => ',' . $object_id_new . ')',
						', ' . $object_id_old . ')' => ', ' . $object_id_new . ')'
					);

					foreach($replace_this_array as $replace_this => $with_this) {

						$with_this_final = ($object_id_new != '') ? $with_this : '';

						// Update label
						if($object_type == 'field') {

							$object->label = str_replace(

								$replace_this,
								$with_this_final,
								$object->label,
								$counter
							);
							if($counter > 0) { $object_update = true; }
						}

						// Update meta data
						foreach($object_meta as $object_meta_key => $object_meta_value) {

							if(is_string($object_meta_value)) {

								if(
									empty($object_meta_value) ||
									(strpos($object_meta_value, '#') === false)
								) {
									continue;
								}

								$object_meta[$object_meta_key] = str_replace(

									$replace_this,
									$with_this_final,
									$object_meta[$object_meta_key],
									$counter
								);
								if($counter > 0) { $object_meta_update = true; }
							}
						}
					}
				}
			}

			foreach($this->new_lookup['section'] as $section_id_old => $section_id_new) {

				foreach($object_meta as $object_meta_key => $object_meta_value) {

					if(is_string($object_meta_value)) {

						if(
							empty($object_meta_value) ||
							(strpos($object_meta_value, '#') === false)
						) {
							continue;
						}

						foreach($parse_variables_repairable['section'] as $parse_variable) {

							$replace_this_array = array(

								'#' . $parse_variable . '(' . $section_id_old . ')' => '#' . $parse_variable . '(' . $section_id_new . ')',
								'#' . $parse_variable . '(' . $section_id_old . ',' => '#' . $parse_variable . '(' . $section_id_new . ','
							);

							foreach($replace_this_array as $replace_this => $with_this) {

								$with_this_final = ($section_id_new != '') ? $with_this : '';

								$object_meta[$object_meta_key] = str_replace(

									$replace_this,
									$with_this_final,
									$object_meta[$object_meta_key],
									$counter
								);

								if($counter > 0) { $object_meta_update = true; }
							}
						}
					}
				}
			}

			// Update object
			if($object_update) {

				$ws_form_field = new WS_Form_Field();
				$ws_form_field->id = $object->id;
				$ws_form_field->section_id = $object->section_id;
				$ws_form_field->db_update_from_object($object, false);
			}

			// Update meta data
			if($object_meta_update) {

				$ws_form_meta = new WS_Form_Meta();
				$ws_form_meta->object = $object_type;
				$ws_form_meta->parent_id = $object->id;
				$ws_form_meta->db_update_from_array($object_meta);
			}
		}

		// Get form to preview
		public function db_get_preview_form_id() {

			// User capability check
			WS_Form_Common::user_must('read_form');

			global $wpdb;

			// Get contents of published field
			$sql = "SELECT id FROM {$this->table_name} ORDER BY date_updated DESC LIMIT 1;";

			$form_id = $wpdb->get_var($sql);

			if(is_null($form_id)) { return 0; } else { return $form_id; }
		}

		// Get form label
		public function db_get_label() {

			// User capability check
			WS_Form_Common::user_must('read_form');

			return parent::db_object_get_label($this->table_name, $this->id);
		}

		// Check id
		public function db_check_id() {

			if(absint($this->id) === 0) { parent::db_throw_error(__('Invalid form ID', 'ws-form')); }
			return true;
		}

		// API - POST - Download - JSON
		public function db_download_json($published = false) {

			// User capability check
			!$published && WS_Form_Common::user_must('export_form');

			// Check form ID
			self::db_check_id();

			// Get form
			if($published) {

				$form_object = self::db_read_published();

			} else {

				$form_object = self::db_read(true, true);
			}

			// Clean form
			unset($form_object->checksum);
			unset($form_object->published_checksum);

			// Stamp form data
			$form_object->identifier = WS_FORM_IDENTIFIER;
			$form_object->version = WS_FORM_VERSION;
			$form_object->time = time();
			$form_object->status = 'draft';
			$form_object->count_submit = 0;
			$form_object->meta->tab_index = 0;
			$form_object->meta->export_object = 'form';
			$form_object->meta->style_id = 0;
			$form_object->meta->style_id_conv = 0;

			// Add checksum
			$form_object->checksum = md5(wp_json_encode($form_object));

			// Build filename
			$filename = 'wsf-form-' . strtolower($form_object->label) . '.json';

			// HTTP headers
			WS_Form_Common::file_download_headers($filename, 'application/json');

			// Output JSON
			WS_Form_Common::echo_wp_json_encode($form_object);
			
			exit;
		}

		// Find pages a form is embedded on
		public function db_get_locations() {

			// User capability check
			WS_Form_Common::user_must('read_form');

			// Return array
			$form_to_post_array = array();

			// Get post types
			$post_types_exclude = array('attachment');
			$post_types = get_post_types(array('show_in_menu' => true), 'objects', 'or');
			$args_post_types = array();

			foreach($post_types as $post_type) {

				$post_type_name = $post_type->name;

				if(in_array($post_type_name, $post_types_exclude)) { continue; }

				$args_post_types[] = $post_type_name;
			}

			// Post types
			$args = array(

				'post_type' 		=> $args_post_types,
				'posts_per_page' 	=> -1
			);

			// Apply filter
			$args = apply_filters('wsf_get_locations_args', $args);

			// Get posts
			$posts = get_posts($args);

			// Bricks
			$bricks_enabled = class_exists('\Bricks\Elements');

			// Run through each post
			foreach($posts as $post) {

				// Get content
				$post_content = $post->post_content;

				// Look for forms in the post content
				$form_id_array = self::find_shortcode_in_string($post_content);

				// Bricks
				if($bricks_enabled) {

					// Extract of code
					$elements = get_post_meta($post->ID, BRICKS_DB_PAGE_CONTENT, true);

					if(is_array($elements)) {

						foreach($elements as $element) {

							if(
								!is_array($element) ||
								!isset($element['name']) ||
								($element['name'] !== 'ws-form-form') ||
								!isset($element['settings']) ||
								!isset($element['settings']['form-id']) ||
								($element['settings']['form-id'] != $this->id)
							) {
								continue;
							}

							$form_id_array[] = absint($element['settings']['form-id']);
						}
					}
				}

				// Run filter
				$form_id_array = apply_filters('wsf_get_locations_post', $form_id_array, $post, $this->id);

				if(count($form_id_array) > 0) {

					foreach($form_id_array as $form_id) {

						if(
							($this->id > 0) &&
							($this->id != $form_id)
						) {

							continue;
						}

						// Get post type
						$post_type = get_post_type_object($post->post_type);

						// If found, register in the return array
						if(!isset($form_to_post_array[$form_id])) { $form_to_post_array[$form_id] = array(); }
						if(!isset($form_to_post_array[$form_id][$post->post_type . '-' . $post->ID])) {

							$form_to_post_array[$form_id][$post->post_type . '-' . $post->ID] = array(

								'id'		=> $post->ID,
								'type'		=> $post->post_type,
								'type_name'	=> $post_type->labels->singular_name,
								'title'		=> (empty($post->post_title) ? $post->ID : $post->post_title)
							);
						}
					}
				}
			}

			// Get registered sidebars
			global $wp_registered_sidebars;

			// Get current widgets
			$sidebars_widgets = get_option('sidebars_widgets');
			$wsform_widgets = get_option('widget_' . WS_FORM_WIDGET);

			if($sidebars_widgets !== false) {

				// Run through each widget
				foreach($sidebars_widgets as $sidebars_widget_id => $sidebars_widget) {

					if(!is_array($sidebars_widget)) { continue; }

					// Check if the sidebar exists
					if(!isset($wp_registered_sidebars[$sidebars_widget_id])) { continue; }
					if(!isset($wp_registered_sidebars[$sidebars_widget_id]['name'])) { continue; }

					foreach($sidebars_widget as $setting) {

						// Is this a WS Form widget?
						if(strpos($setting, WS_FORM_WIDGET) !== 0) { continue; }

						// Get widget instance
						$setting_array = explode('-', $setting);
						if(!isset($setting_array[1])) { continue; }
						$widget_instance = absint($setting_array[1]);

						// Check if that widget instance is valid
						if(!isset($wsform_widgets[$widget_instance])) { continue; }
						if(!isset($wsform_widgets[$widget_instance]['form_id'])) { continue; }

						// Get form ID used by widget ID
						$form_id = absint($wsform_widgets[$widget_instance]['form_id']);
						if($form_id === 0) { continue; }

						if(
							($this->id > 0) &&
							($this->id !== $form_id)
						) {

							continue;
						}

						// If found, register in the return array
						if(!isset($form_to_post_array[$form_id])) { $form_to_post_array[$form_id] = array(); }
						if(!isset($form_to_post_array[$form_id]['widget-' . $sidebars_widget_id])) {

							$form_to_post_array[$form_id]['widget-' . $sidebars_widget_id] = array(

								'id'		=> $sidebars_widget_id,
								'type'		=> 'widget',
								'type_name'	=> __('Widget', 'ws-form'),
								'title'		=> $wp_registered_sidebars[$sidebars_widget_id]['name']
							);
						}
					}
				}
			}

			return $form_to_post_array;
		}

		// Find WS Form shortcodes or Gutenberg blocks in a string
		public function find_shortcode_in_string($input) {

			$form_id_array = array();

			// Gutenberg block search
			if(function_exists('parse_blocks')) {

				$parse_blocks = parse_blocks($input);
				foreach($parse_blocks as $parse_block) {

					if(!isset($parse_block['blockName'])) { continue; }
					if(!isset($parse_block['attrs'])) { continue; }
					if(!isset($parse_block['attrs']['form_id'])) { continue; }

					$block_name = $parse_block['blockName'];

					if(strpos($block_name, 'wsf-block/') === 0) {

						$form_id_array[] = absint($parse_block['attrs']['form_id']);
					}
				}
			}

			// Shortcode search
			$has_shortcode = has_shortcode($input, WS_FORM_SHORTCODE);

			$pattern = get_shortcode_regex();
			if(
				preg_match_all('/'. $pattern .'/s', $input, $matches) &&
				array_key_exists(2, $matches) &&
				in_array(WS_FORM_SHORTCODE, $matches[2])
			) {

				foreach( $matches[0] as $key => $value) {

					$get = str_replace(" ", "&" , $matches[3][$key] );
			        parse_str($get, $output);

			        if(isset($output['id'])) {

			        	$form_id_array[] = (int) filter_var($output['id'], FILTER_SANITIZE_NUMBER_INT);
					}
				}
			}

			return $form_id_array;
		}

		public function apply_limits($form_object) {

			// Check form scheduling
			$schedule_limit_message = self::schedule_limit($form_object);
			if($schedule_limit_message !== false) { return $schedule_limit_message; }

			// Check submission limits
			$submit_limit_message = self::submit_limit($form_object);
			if($submit_limit_message !== false) { return $submit_limit_message; }

			// Check user
			$user_limit_message = self::user_limit($form_object);
			if($user_limit_message !== false) { return $user_limit_message; }

			// Check IP
			$ip_limit_message = self::ip_limit($form_object);
			if($ip_limit_message !== false) { return $ip_limit_message; }

			// Custom limits filter
			$form_limit_message = apply_filters('wsf_form_limit', false, $form_object);
			if($form_limit_message !== false) { return $form_limit_message; }

			return false;
		}

		public function schedule_limit($form_object) {

			// Get current GMT time
			$gmt_time = time();

			// Schedule - Start
			$schedule_start = WS_Form_Common::get_object_meta_value($form_object, 'schedule_start', '');
			if($schedule_start) {

				// Check schedule start
				$schedule_start_datetime = WS_Form_Common::get_object_meta_value($form_object, 'schedule_start_datetime', '');
				$schedule_start_datetime = ($schedule_start_datetime == '') ? false : strtotime(get_gmt_from_date($schedule_start_datetime));

				// Before start date
				if(($schedule_start_datetime !== false) && ($gmt_time < $schedule_start_datetime)) {

					$message = WS_Form_Common::get_object_meta_value($form_object, 'schedule_start_message', '');
					$message_type = WS_Form_Common::get_object_meta_value($form_object, 'schedule_start_message_type', '');
					if($message == '') { return ''; }
				}
			}

			// Schedule - End
			$schedule_end = WS_Form_Common::get_object_meta_value($form_object, 'schedule_end', '');
			if($schedule_end && !isset($message)) {

				// Check schedule end
				$schedule_end_datetime = WS_Form_Common::get_object_meta_value($form_object, 'schedule_end_datetime', '');
				$schedule_end_datetime = ($schedule_end_datetime == '') ? false : strtotime(get_gmt_from_date($schedule_end_datetime));

				// After end date
				if(($schedule_end_datetime !== false) && ($gmt_time >= $schedule_end_datetime)) {

					$message = WS_Form_Common::get_object_meta_value($form_object, 'schedule_end_message', '');
					$message_type = WS_Form_Common::get_object_meta_value($form_object, 'schedule_end_message_type', '');
					if($message == '') { return ''; }
				}
			}

			// Show message
			if(isset($message)) {

				// Build return
				return self::limit_return_message($message_type, $message, $form_object);

			} else {

				// No scheduling issues
				return false;
			}
		}

		public function submit_limit($form_object) {

			// Check for submission limits
			$submit_limit = WS_Form_Common::get_object_meta_value($form_object, 'submit_limit', '');
			if(!$submit_limit) { return false; }

			// Check limit count
			$submit_limit_count = WS_Form_Common::get_object_meta_value($form_object, 'submit_limit_count', '');
			$submit_limit_count = ($submit_limit_count == '') ? false : absint($submit_limit_count);
			if($submit_limit_count === false) { return false; }

			// Check by logged in user
			$submit_limit_logged_in = WS_Form_Common::get_object_meta_value($form_object, 'submit_limit_logged_in', '');
			if($submit_limit_logged_in) {

				// Ensure user is logged in
				$user_id = get_current_user_id();

			} else {

				$user_id = 0;
			}

			// Check limit period
			$submit_limit_period = WS_Form_Common::get_object_meta_value($form_object, 'submit_limit_period', '');
			switch($submit_limit_period) {

				case 'minute' :
				case 'hour' :
				case 'day' :
				case 'week' :
				case 'month' :
				case 'year' :

					$sql_where_date = " AND date_added > '" . WS_Form_Common::get_mysql_date('-1 ' . $submit_limit_period) . "'";

					break;

				default :

					$sql_where_date = '';
			}

			// Get submit count
			$sql_where = sprintf('form_id = %u%s', $form_object->id, $sql_where_date);

			// User ID
			if($user_id > 0) {

				$sql_where .= sprintf(' AND user_id = %u', $user_id);
			}

			$ws_form_submit = new WS_Form_Submit();
			$submit_count = $ws_form_submit->db_read_count('', $sql_where, true);

			// Check submit count
			if($submit_count < $submit_limit_count) { return false; }

			// Get message
			$submit_limit_message = WS_Form_Common::get_object_meta_value($form_object, 'submit_limit_message', '');

			// If message is blank, return nothing
			if($submit_limit_message == '') { return ''; }

			// Get message type
			$submit_limit_message_type = WS_Form_Common::get_object_meta_value($form_object, 'submit_limit_message_type', '');

			// Build return
			return self::limit_return_message($submit_limit_message_type, $submit_limit_message, $form_object);
		}

		public function user_limit($form_object) {

			$user_limit_logged_in = WS_Form_Common::get_object_meta_value($form_object, 'user_limit_logged_in', '');
			$form_user_roles = WS_Form_Common::get_object_meta_value($form_object, 'form_user_roles', false);
			$form_user_capabilities = WS_Form_Common::get_object_meta_value($form_object, 'form_user_capabilities', false);

			if(!$user_limit_logged_in) { return false; }

			// Get user roles
			$current_user = wp_get_current_user();

			switch($user_limit_logged_in) {

				// Must be logged in
				case 'on' :

					if($current_user->ID > 0) { return false; }
					break;

				// Must be logged out
				case 'out' :

					if($current_user->ID == 0) { return false; }
					break;

				// Must have user role or capability
				case 'role_capability' :

					if(is_array($form_user_roles) && (count($form_user_roles) > 0)) {

						$user_role_ok = false;

						$user_roles = $current_user->roles;

						if(is_user_logged_in()) {

							foreach($form_user_roles as $form_user_role) {

								if(in_array($form_user_role, $user_roles)) {

									$user_role_ok = true;
								}
							}
						}

					} else {

						$user_role_ok = true;
					}

					if(is_array($form_user_capabilities) && (count($form_user_capabilities) > 0)) {

						$user_capability_ok = false;

						if(is_user_logged_in()) {

							foreach($form_user_capabilities as $form_user_capability) {

								if(WS_Form_Common::can_user($form_user_capability)) {

									$user_capability_ok = true;
								}
							}
						}

					} else {

						$user_capability_ok = true;
					}

					if($user_role_ok && $user_capability_ok) { return false; }

					break;
			}

			// Get message
			$user_limit_logged_in_message = WS_Form_Common::get_object_meta_value($form_object, 'user_limit_logged_in_message', '');

			// If message is blank, return nothing
			if($user_limit_logged_in_message == '') { return ''; }

			// Get message type
			$user_limit_logged_in_message_type = WS_Form_Common::get_object_meta_value($form_object, 'user_limit_logged_in_message_type', '');

			// Build return
			return self::limit_return_message($user_limit_logged_in_message_type, $user_limit_logged_in_message, $form_object);
		}

		public function ip_limit($form_object) {

			// Check for IP limits
			$ip_limit = WS_Form_Common::get_object_meta_value($form_object, 'ip_limit', '');
			if(!$ip_limit) { return false; }

			// Get IP address
			$ip = WS_Form_Common::get_ip();
			if(empty($ip)) { return false; }

			// Check limit count
			$ip_limit_count = WS_Form_Common::get_object_meta_value($form_object, 'ip_limit_count', '');
			$ip_limit_count = ($ip_limit_count == '') ? false : absint($ip_limit_count);
			if($ip_limit_count === false) { return false; }

			// Check limit period
			$ip_limit_period = WS_Form_Common::get_object_meta_value($form_object, 'ip_limit_period', '');
			switch($ip_limit_period) {

				case 'minute' :
				case 'hour' :
				case 'day' :
				case 'week' :
				case 'month' :
				case 'year' :

					$sql_where_date = " AND date_added > '" . WS_Form_Common::get_mysql_date('-1 ' . $ip_limit_period) . "'";

					break;

				default :

					$sql_where_date = '';
			}

			global $wpdb;

			// Get submit count
			$sql_join = sprintf(

				'RIGHT JOIN %1$ssubmit_meta smk ON (smk.parent_id = %1$ssubmit.id AND smk.meta_key = \'tracking_remote_ip\' AND smk.meta_value = \'%2$s\')',
				$wpdb->prefix . WS_FORM_DB_TABLE_PREFIX,
				esc_sql($ip)
			);

			$sql_where = sprintf('form_id = %u AND status = \'publish\'%s', $form_object->id, $sql_where_date);

			$ws_form_submit = new WS_Form_Submit();
			$submit_count = $ws_form_submit->db_read_count($sql_join, $sql_where, true);

			// Check submit count
			if($submit_count < $ip_limit_count) { return false; }

			// Get message
			$ip_limit_message = WS_Form_Common::get_object_meta_value($form_object, 'ip_limit_message', '');

			// If message is blank, return nothing
			if($ip_limit_message == '') { return ''; }

			// Get message type
			$ip_limit_message_type = WS_Form_Common::get_object_meta_value($form_object, 'ip_limit_message_type', '');

			// Build return
			return self::limit_return_message($ip_limit_message_type, $ip_limit_message, $form_object);
		}

		public function limit_return_message($type, $message, $form_object) {

			// Check type
			if(($type == '') || !in_array($type, array('success', 'information', 'warning', 'danger'))) {

				return WS_Form_Common::parse_variables_process($message, $form_object, false, 'text/html');
			}

			// Init framework config
			$framework_id = WS_Form_Common::option_get('framework', WS_FORM_DEFAULT_FRAMEWORK);
			$frameworks = WS_Form_Config::get_frameworks();
			$framework = $frameworks['types'][$framework_id];

			// Get mask wrapper
			$mask_wrapper = $framework['message']['public']['mask_wrapper'];

			// Build lookups
			$mask_wrapper_lookups = array(

				'mask_wrapper_class' 	=> $framework['message']['public']['types'][$type]['mask_wrapper_class'],
				'message'				=> $message
			);

			// Parse message wrapper class and message
			$return_message = WS_Form_Common::mask_parse($mask_wrapper, $mask_wrapper_lookups);

			// Standard parse
			return WS_Form_Common::parse_variables_process($return_message, $form_object, false, 'text/html');
		}
		public function get_svg($published = false) {

			self::db_check_id();

			try {

				if($published) {

					// Published
					$form_object = self::db_read_published();

				} else {

					// Draft
					$form_object = self::db_read(true, true);
				}

			} catch(Exception $e) { return false; }

			return self::get_svg_from_form_object($form_object, true);
		}

		// Get SVG of form
		public function get_svg_from_form_object($form_object, $label = false, $svg_width = false, $svg_height = false) {

			// Check form object
			if(
				!is_object($form_object) ||
				!property_exists($form_object, 'groups')
			) {
				return '';
			}

			// Default width and height
			if($svg_width === false) { $svg_width = WS_FORM_TEMPLATE_SVG_WIDTH_FORM; }
			if($svg_height === false) { $svg_height = WS_FORM_TEMPLATE_SVG_HEIGHT_FORM; }

			// Get form column count
			$svg_columns = absint(WS_Form_Common::option_get('framework_column_count', 0));
			if($svg_columns == 0) { self::db_throw_error(__('Invalid framework column count', 'ws-form')); }

			if(WS_Form_Common::styler_enabled()) {

				// Get colors
				$color_base = WS_Form_Color::get_color_base();
				$color_base_contrast = WS_Form_Color::get_color_base_contrast();
				$color_success = WS_Form_Color::get_color_success();
				$color_info = WS_Form_Color::get_color_info();
				$color_warning = WS_Form_Color::get_color_warning();
				$color_danger = WS_Form_Color::get_color_danger();

				// Set variables to use styler CSS vars
				$ws_form_css = (object) array(

					// Fixed dimensions
					'border_width' => 0.5,
					'border_radius' => 1,
					'grid_gutter' => 5,

					// Fixed colors (We don't want to use style because user could have specified third party vars)
					'color_form_background' => $color_base_contrast,
					'color_default' => $color_base,
					'color_default_lighter' => sprintf(

						'color-mix(in oklab, %s, #FFF 80%%)',
						$color_base
					),
					'color_default_lightest' => sprintf(

						'color-mix(in oklab, %s, #FFF 90%%)',
						$color_base
					),
					'color_default_inverted' => $color_base_contrast,
					'color_primary' => WS_Form_Color::get_color_primary(),
					'color_secondary' => WS_Form_Color::get_color_secondary(),
					'color_success' => $color_success,
					'color_success_light_85' => sprintf(

						'color-mix(in oklab, %s, #FFF 85%%)',
						$color_success
					),
					'color_success_light_40' => sprintf(

						'color-mix(in oklab, %s, #FFF 40%%)',
						$color_success
					),
					'color_success_dark_40' => sprintf(

						'color-mix(in oklab, %s, #000 40%%)',
						$color_success
					),
					'color_information' => $color_info,
					'color_information_light_85' => sprintf(

						'color-mix(in oklab, %s, #FFF 85%%)',
						$color_info
					),
					'color_information_light_40' => sprintf(

						'color-mix(in oklab, %s, #FFF 40%%)',
						$color_info
					),
					'color_information_dark_40' => sprintf(

						'color-mix(in oklab, %s, #000 40%%)',
						$color_info
					),
					'color_warning' => $color_warning,
					'color_warning_light_85' => sprintf(

						'color-mix(in oklab, %s, #FFF 85%%)',
						$color_warning
					),
					'color_warning_light_40' => sprintf(

						'color-mix(in oklab, %s, #FFF 40%%)',
						$color_warning
					),
					'color_warning_dark_40' => sprintf(

						'color-mix(in oklab, %s, #000 40%%)',
						$color_warning
					),
					'color_danger' => $color_danger,
					'color_danger_light_85' => sprintf(

						'color-mix(in oklab, %s, #FFF 85%%)',
						$color_danger
					),
					'color_danger_light_40' => sprintf(

						'color-mix(in oklab, %s, #FFF 40%%)',
						$color_danger
					),
					'color_danger_dark_40' => sprintf(

						'color-mix(in oklab, %s, #000 40%%)',
						$color_danger
					),
				);

			} else {

				// CSS
				$ws_form_css = new WS_Form_CSS();

				// Load skin
				$ws_form_css->skin_load();

				// Load variables
				$ws_form_css->skin_variables();

				// Load color shades
				$ws_form_css->skin_color_shades();

				// Skin adjustments
				if($ws_form_css->color_form_background == '') { $ws_form_css->color_form_background = '#ffffff'; }
				if($ws_form_css->border_width > 0) { $ws_form_css->border_width = ($ws_form_css->border_width / 2); }
				if($ws_form_css->border_radius > 0) { $ws_form_css->border_radius = ($ws_form_css->border_radius / 4); }
				if($ws_form_css->grid_gutter > 0) { $ws_form_css->grid_gutter = ($ws_form_css->grid_gutter / 4); }
			}

			// Columns
			$col_index_max = $svg_columns;
			$col_width = 10.8333;

			// Rows
			$row_spacing = $ws_form_css->grid_gutter;

			// Gutter
			$gutter_width = $ws_form_css->grid_gutter;

			// Fields
			$field_height = 8;
			$field_adjust_x = -0.17;

			// Legend
			$legend_font_size = 8;
			$legend_margin_bottom = 2;

			// Labels
			$label_font_size = 6;
			$label_margin_bottom = 2;
			$label_offset_y = 0;
			$label_margin_x = 2;
			$label_margin_y = 1;
			$label_inside_y = ($field_height / 2) + ($label_font_size / 2) - 1;

			// Origin
			$origin_x = ($col_width / 2);
			$origin_y = 25;

			// Offset
			$offset_x = $origin_x;
			$offset_y = $origin_y;

			// Gradient
			$gradient_height = 20;

			$row_height_max = 0;

			// Get field types
			$field_types = WS_Form_Common::get_field_types();

			// Get form fields
			$fields = array();
			foreach($form_object->groups as $group) {

				$section_index = 0;

				foreach($group->sections as $section) {

					// Section break;
					if($section_index > 0) {

						// Add section break
						$fields[] = array(

							'label'				=>	'',
							'label_render'		=>	true,
							'required'			=>	false,
							'type'				=>	'section_break',
							'size'				=>	12,
							'offset'			=>	0,
							'object'			=>	false
						);
					}

					// Section legend
					$section_label_render = WS_Form_Common::get_object_meta_value($section, 'label_render', false);
					if($section_label_render) {

						$section_label = $section->label;

						if($section_label != '') {

							// Add to legend fields
							$fields[] = array(

								'label'				=>	esc_html($section_label),
								'label_render'		=>	true,
								'required'			=>	false,
								'type'				=>	'section_label',
								'size'				=>	$svg_columns,
								'offset'			=>	0,
								'object'			=>	false
							);
						}
					}

					foreach($section->fields as $field) {

						// Get field type config
						$field_type = $field->type;
						if(!isset($field_types[$field_type])) { continue; }
						$field_type_config = $field_types[$field_type];

						// Fields to skip
						$template_svg_exclude = isset($field_type_config['template_svg_exclude']) ? $field_type_config['template_svg_exclude'] : false;
						if($template_svg_exclude) { continue; }

						// Get field size
						$field_size_columns = absint((isset($field->meta->breakpoint_size_25)) ? $field->meta->breakpoint_size_25 : $svg_columns);

						// Get field offset
						$field_offset_columns = absint((isset($field->meta->breakpoint_offset_25)) ? $field->meta->breakpoint_offset_25 : 0);

						// Add to fields
						$fields[] = array(

							'label'				=>	esc_html($field->label),
							'label_render'		=>	WS_Form_Common::get_object_meta_value($field, 'label_render', false),
							'required'			=>	WS_Form_Common::get_object_meta_value($field, 'required', false),
							'type'				=>	$field->type,
							'size'				=>	$field_size_columns,
							'offset'			=>	$field_offset_columns,
							'object'			=>	$field
						);
					}

					$section_index++;
				}

				// Skip other groups (tabs)
				break;
			}

			$field_type_buttons = apply_filters('wsf_template_svg_buttons', array(

				'submit' => array('fill' => $ws_form_css->color_primary, 'color' => $ws_form_css->color_default_inverted),
				'save' => array('fill' => $ws_form_css->color_success, 'color' => $ws_form_css->color_default_inverted),
				'clear' => array('fill' => $ws_form_css->color_default_lighter, 'color' => $ws_form_css->color_default),
				'reset' => array('fill' => $ws_form_css->color_default_lighter, 'color' => $ws_form_css->color_default),
				'tab_previous' => array('fill' => $ws_form_css->color_default_lighter, 'color' => $ws_form_css->color_default),
				'tab_next' => array('fill' => $ws_form_css->color_default_lighter, 'color' => $ws_form_css->color_default),
				'button' => array('fill' => $ws_form_css->color_default_lighter, 'color' => $ws_form_css->color_default),
				'section_add' => array('fill' => $ws_form_css->color_default_lighter, 'color' => $ws_form_css->color_default),
				'section_delete' => array('fill' => $ws_form_css->color_danger, 'color' => $ws_form_css->color_default_inverted),
				'section_up' => array('fill' => $ws_form_css->color_default_lighter, 'color' => $ws_form_css->color_default),
				'section_down' => array('fill' => $ws_form_css->color_default_lighter, 'color' => $ws_form_css->color_default)
			));
			$field_type_buttons = apply_filters('wsf_wizard_svg_buttons', $field_type_buttons);	// Legacy

			$field_type_price_span = apply_filters('wsf_template_svg_price_span', array());
			$field_type_price_span = apply_filters('wsf_wizard_svg_price_span', $field_type_price_span);	// Legacy

			// Build SVG
			$svg = sprintf(
				'<svg xmlns="http://www.w3.org/2000/svg" class="wsf-responsive" viewBox="0 0 %u %u"><rect height="100%%" width="100%%" fill="' . $ws_form_css->color_form_background . '"/>',
				$svg_width,
				$svg_height
			);

			// Definitions
			$svg .= '<defs>';

			// Gradient ID
			$gradient_id = 'wsf-template-bottom' . (isset($form_object->checksum) ? '-' . $form_object->checksum : '');

			// Definitions - Gradient - Bottom
			$svg .= '<linearGradient id="' . $gradient_id . '" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:' . $ws_form_css->color_form_background . ';stop-opacity:0" /><stop offset="100%" style="stop-color:' . $ws_form_css->color_form_background . ';stop-opacity:1" /></linearGradient>';

			$svg .= '</defs>';

			// Label
			$svg .= sprintf('<text fill="%s" class="wsf-template-title"><tspan x="%u" y="16">%s</tspan></text>',
				$ws_form_css->color_default,
				is_rtl() ? ($svg_width - 5) : 5,
				(($label !== false) ? $form_object->label : '#label')
			);

			// Process each field
			$col_index = 0;
			$svg_array = array();
			$label_found = false;

			foreach($fields as $field) {

				// Field size and offset
				$field_size_columns = ($field['size'] > 0) ? $field['size'] : $svg_columns;
				$field_offset_columns = ($field['offset'] > 0) ? $field['offset'] : 0;

				// Field width
				$field_cols = ($col_index_max / $field_size_columns);
				$field_width = ($field_size_columns * $col_width) - (($field_cols > 1) ? ((1 - (1 / $field_cols)) * $gutter_width) : 0);

				// Field offset width
				$field_cols_offset = ($field_offset_columns > 0) ? ($col_index_max / $field_offset_columns) : 0;
				$field_width_offset = ($field_cols_offset > 0) ? ($field_offset_columns * $col_width) - (($field_cols_offset > 1) ? ((1 - (1 / $field_cols_offset)) * $gutter_width) - $gutter_width : 0) : 0;

				// Field - X
				if(is_rtl()) {

					$field_x = $svg_width - (($offset_x + $field_adjust_x) + $field_width_offset + $field_width);

				} else {

					$field_x = ($offset_x + $field_adjust_x) + $field_width_offset;

				}

				// Label - X
				if(is_rtl()) {

					$label_x = $field_x + $field_width;
					$label_inside_x = $label_x - (($ws_form_css->border_width * 2) + (($field_height - $label_font_size) / 2));

				} else {

					$label_x = $field_x;
					$label_inside_x = $label_x + (($ws_form_css->border_width * 2) + (($field_height - $label_font_size) / 2));
				}

				// Process by field type

				// Buttons
				if(isset($field_type_buttons[$field['type']])) {

					$label_button_x = $field_x + ($field_width / 2);
					$button_fill = $field_type_buttons[$field['type']]['fill'];
					$button_fill_label = $field_type_buttons[$field['type']]['color'];

					// Get class_field_button_type
					$class_field_button_type = (isset($field['object']->meta) && isset($field['object']->meta->class_field_button_type)) ? $field['object']->meta->class_field_button_type : '';
					switch($class_field_button_type) {

						case 'primary' :

							$button_fill = $ws_form_css->color_primary;
							$button_fill_label = $ws_form_css->color_default_inverted;
							break;

						case 'secondary' :

							$button_fill = $ws_form_css->color_secondary;
							$button_fill_label = $ws_form_css->color_default_inverted;
							break;

						case 'success' :

							$button_fill = $ws_form_css->color_success;
							$button_fill_label = $ws_form_css->color_default_inverted;
							break;

						case 'information' :

							$button_fill = $ws_form_css->color_information;
							$button_fill_label = $ws_form_css->color_default;
							break;

						case 'warning' :

							$button_fill = $ws_form_css->color_warning;
							$button_fill_label = $ws_form_css->color_default;
							break;

						case 'danger' :

							$button_fill = $ws_form_css->color_danger;
							$button_fill_label = $ws_form_css->color_default_inverted;
							break;

						default :

							$button_fill = $ws_form_css->color_default_lightest;
							$button_fill_label = $ws_form_css->color_default;
							break;
					}

					// Button - Rectangle
					$svg_field = '<rect x="' . $field_x . '" y="0" fill="' . $button_fill . '" stroke="' . $button_fill . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . $field_height . '"/>';

					// Button - Label
					$svg_field .= '<text transform="translate(' . $label_button_x . ',' . $label_inside_y . ')" class="wsf-template-label" fill="' . $button_fill_label . '" text-anchor="middle">' . $field['label'] . '</text>';

					// Add to SVG array
					$svg_single = array('svg' => $svg_field, 'height' => $field_height);

				} elseif (isset($field_type_price_span[$field['type']])) {

					// Price Span - Rectangle
					$svg_field = '<rect x="' . $field_x . '" y="0" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" stroke-dasharray="2 1" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . $field_height . '"/>';

					// Price Span - Label
					$svg_field .= '<text fill="' . $ws_form_css->color_default . '" transform="translate(' . $label_inside_x . ',' . $label_inside_y . ')" class="wsf-template-label">' . $field['label'] . '</text>';

					// Add to SVG array
					$svg_single = array('svg' => $svg_field, 'height' => $field_height);

				} else {

					// Render label
					$label_render = $field['label_render'];
					$label_offset_x = 0;

					// Move/force label if inline with an SVG element
					switch($field['type']) {

						case 'checkbox' :
						case 'price_checkbox' :
						case 'radio' :
						case 'price_radio' :

							$label_render = true;
							$label_offset_x = is_rtl() ? ($field_height + $label_margin_x) * -1 : ($field_height + $label_margin_x);
							break;
					}

					if($label_render) {

						// Label (Origin is bottom left of text)
						$svg_field = '<text fill="' . $ws_form_css->color_default . '" transform="translate(' . ($label_x + $label_offset_x) . ',' . $label_font_size . ')" class="wsf-template-label">' . $field['label'] . ($field['required'] ? '<tspan fill="' . $ws_form_css->color_danger . '"> *</tspan>' : '') . '</text>';
						$label_offset_y = $label_font_size + $label_margin_bottom;
						$label_found = true;

					} else {

						$svg_field = '';
						$label_offset_y = 0;
					}

					// Process by type
					switch($field['type']) {

						case 'section_break' :

							// Add to SVG array
							$svg_single = array('svg' => false, 'height' => 0);

							break;

						case 'section_label' :

							$label_x = (is_rtl() ? ($svg_width - $field_x) : $field_x);

							// Legend
							$svg_field = '<text fill="' . $ws_form_css->color_default . '" transform="translate(' . $label_x . ',' . $legend_font_size . ')" class="wsf-template-legend">' . $field['label'] . '</text>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $legend_font_size + $legend_margin_bottom);

							break;

						case 'progress' :

							// Progress - Random width
							$progress_width = wp_rand(round($field_width / 6), round($field_width - ($field_width / 6)));

							// Progress - Rectangle - Outer
							$svg_field .= '<rect x="' . $field_x . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_default_lighter . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . ($field_height / 2) . '"/>';

							// Progress - Rectangle - Inner
							$svg_field .= '<rect x="' . (is_rtl() ? ($field_x + $field_width - $progress_width) : $field_x) . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_primary . '" stroke="' . $ws_form_css->color_primary . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $progress_width . '" height="' . ($field_height / 2) . '"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + ($field_height / 2));

							break;

						case 'range' :
						case 'price_range' :

							// Range - Random x position
							$range_x = wp_rand(round($field_width / 6), round($field_width - ($field_width / 6)));

							// Range - Rectangle
							$svg_field .= '<rect x="' . $field_x . '" y="' . (($label_offset_y + ($field_height / 2)) - 1) . '" fill="' . $ws_form_css->color_default_lightest . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . ($field_height / 4) . '"/>';

							// Range - Circle (Slider)
							$svg_field .= '<circle cx="' . ($field_x + $range_x) . '" cy="' . ($label_offset_y + ($field_height / 2)) . '" r="' . ($field_height / 2) . '" fill="' . $ws_form_css->color_primary . '"/>
							';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + $field_height);

							break;

						case 'message' :

							// Get class_field_message_type
							$class_field_message_type = (isset($field['object']->meta) && isset($field['object']->meta->class_field_message_type)) ? $field['object']->meta->class_field_message_type : '';

							if($class_field_message_type == 'none') {

								$message_fill = $ws_form_css->color_default_lightest;
								$message_fill_left = $ws_form_css->color_default_lighter;
								$message_fill_label = $ws_form_css->color_default;

							} else {

								$message_fill_var = sprintf('color_%s_light_85', $class_field_message_type);
								$message_fill_left_var = sprintf('color_%s_light_40', $class_field_message_type);
								$message_fill_label_var = sprintf('color_%s_dark_40', $class_field_message_type);

								$message_fill = $ws_form_css->{$message_fill_var};
								$message_fill_left = $ws_form_css->{$message_fill_left_var};
								$message_fill_label = $ws_form_css->{$message_fill_label_var};
							}


							// Message - Rectangle
							$svg_field = '<rect x="' . $field_x . '" y="0" fill="' . $message_fill . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . $field_height . '"/>';
							$svg_field .= '<rect x="' . $field_x . '" y="0" fill="' . $message_fill_left . '" rx="' . $ws_form_css->border_radius . '" width="' . ($ws_form_css->border_width * 2) . '" height="' . $field_height . '"/>';

							// Message - Label
							$svg_field .= '<text transform="translate(' . $label_inside_x . ',' . $label_inside_y . ')" class="wsf-template-label" fill="' . $message_fill_label . '">' . $field['label'] . '</text>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $field_height);

							break;

						case 'textarea' :

							// Textarea - Rectangle
							$svg_field .= '<rect x="' . $field_x . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . ($field_height * 2) . '"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + ($field_height * 2));

							break;

						case 'signature' :

							// Signature - Rectangle
							$svg_field .= '<rect x="' . $field_x . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . ($field_height * 2) . '"/>';

							// Signature - Icon
							$svg_field .= '<path transform="translate(' . ($field_x + (is_rtl() ? ($field_width - 16) : 3)) . ',' . ($label_offset_y + 2) . ') scale(0.75)" fill="' . $ws_form_css->color_default . '" d="M13.3 3.9l-.6-.2a1 1 0 00-.6.2c-1 .8-1.7 1.8-2.1 3-.3.6-.4 1.2-.3 1.7.9-.3 1.8-.7 2.5-1.3.8-.6 1.3-1.4 1.5-2.3v-.6l-.4-.5zM0 12.4h15.6v1.2H0v-1.2zM2.1 8l1.3-1.3.8.8-1.3 1.3 1.3 1.3-.8.8-1.3-1.3-1.3 1.3-.8-.8 1.3-1.3L0 7.5l.8-.8L2.1 8zm13.6 2.8v.4h-1.2l-.1-.7c-.3-.2-.9-.1-1.8.2l-.4.1c-.6.2-1.2.2-1.8.1-.6-.1-1.1-.5-1.5-1-.9.2-2 .2-3.5.2V8.9l3.1-.1c-.1-.7 0-1.5.2-2.3.3-.8.7-1.5 1.2-2.2.5-.7 1.1-1.2 1.7-1.5.8-.5 1.6-.4 2.4.2.4.3.7.8.9 1.4.1.3 0 .6-.1 1s-.3.9-.6 1.3c-.3.5-.7.9-1.1 1.2-.8.7-1.8 1.2-2.8 1.6.5.3 1.2.3 1.8.1l1-.3c.7-.1 1.2-.1 1.6 0 .5.2.7.4.8.8l.2.7z"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + ($field_height * 2));

							break;

						case 'rating' :

							$rating_color_on = WS_Form_Common::get_object_meta_value($field['object'], 'rating_color_on', '#fdb81e');
							$rating_color_off = WS_Form_Common::get_object_meta_value($field['object'], 'rating_color_off', '#ceced2');

							// Rating
							for($rating_index = 0; $rating_index < 5; $rating_index++) {

								$field_rating_offset_x = ($rating_index * 9);

								$rating_color = ($rating_index < 3) ? $rating_color_on : $rating_color_off;

								$svg_field .= '<path transform="translate(' . ($field_x + (is_rtl() ? $field_width - 8 - ($field_rating_offset_x) : $field_rating_offset_x)) . ',' . ($label_offset_y) . ') scale(0.5)" d="M12.9 15.8c-1.6-1.2-3.2-2.5-4.9-3.7-1.6 1.3-3.3 2.5-4.9 3.7 0 0-.1 0-.1-.1.6-2 1.2-4 1.9-6C3.3 8.4 1.7 7.2 0 5.9h6C6.7 3.9 7.3 2 8 0h.1c.7 1.9 1.3 3.9 2 5.9H16V6c-1.6 1.3-3.2 2.5-4.9 3.8.6 1.9 1.3 3.9 1.8 6 .1-.1 0 0 0 0z" fill="' . $rating_color . '" />';
							}

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + $field_height);

							break;

						case 'googlemap' :

							// Map
							$svg_field .= sprintf('<g transform="translate(%.4f,%.4f)"><g transform="scale(%.4f,%.4f)"><path fill="#f5ede1" d="M0 0h150v40H0z"/><path fill="#c1e1b2" d="M99.6 0L97 6.8l11.7 3.9 4.9 5.7L119.9 0zM10.3 38.7L9.1 40h3.7z"/><path d="M138.4 27.3c-3.4.3-6.9.4-10-.5-2.9-.8-5.3-2.7-8.2-4.4a41.8 41.8 0 00-9.3-4.4c-6.6-2-13.6-.6-19.7.3l-4 .8a105 105 0 01-38 .5c-7-1.5-13-5.8-19.5-11.7-1.3-1.2-4.1-4.8-6.6-8h-6.3a52.7 52.7 0 0035.1 25.4C69 28.2 91.3 23.7 92 23.6c5.7-.9 11.7-1.6 17.1 0 2.8.8 5.4 2.3 8.1 3.9 3 1.7 6.3 3.6 9.9 4.6 4.1 1.2 7.9.8 11.7.5 2.9-.2 5.7-.5 8.3 0 .8.1 1.8.4 2.8.8v-6.9l-1.2-.3c-3.3-.3-7.2.9-10.3 1.1z" fill="#8acdf1"/><path fill="#fff" d="M135.5 0h-3.7c7.7 2.8 15.8 5.8 17 6.4l1.2.6V5.6l-.6-.3c-1.2-.6-7.2-2.8-13.9-5.3zM94.3 31l-1.3-.3c-.7 2.6-1.8 6.4-2.8 9.3h1.3c1.1-3 2.1-6.6 2.8-9zM100.4 33.7c-.6-.2-1.2.1-1.4.6l-2.1 5.6h2.3l1.4-3.9c2.4.8 8.2 2.6 9.3 2.7.7.1 3.7.5 7.2 1.2h9c-5.6-2-15.8-3.3-15.9-3.3-.9 0-8.4-2.4-9.8-2.9z"/><path fill="#fff" d="M10.3 39l1.7 1h2.7c-1.4-.7-2.7-1.4-3.5-2l3.6-4.4c1.8 1.4 5.4 3.9 9.1 6.4H26c-4.1-2.7-8.6-5.9-10.6-7.4l1.5-2.2A493.2 493.2 0 0030.1 40h2.2l-3.6-2.6a374 374 0 01-11.1-8.1l.5-.8.8-1.4.9-1.6L36 36.2l-1.6 3.7h2.8l-.4-.1 6.1-14.6a68 68 0 008.1 2.5c1.7.4 4.5.7 7.9.9l-2.1 7.6 1.2.3 1.5-5.3c3.8 2 7 3.5 10.5 4.8l-1.1 4h1.3l2-7 6.7 2.5a9.4 9.4 0 00-.4 3.4v.1c.1.3.3.6 1.2 1.1h2.5c-1.3-.7-2.2-1.2-2.5-1.5-.1-1.3.9-4.8 1.7-7.4l.8-3c4.5-.4 8.5-1.1 11.5-2 3.3-1 14.9-.7 17.2 1.1a64.4 64.4 0 0016 8c1.1.3 2.5.5 4 .6l-2.8 4.2h1.5l2.7-4.2h7.6a78 78 0 018.5.1l-2.4 4.1h1.5l2.3-3.9h.2v-1.3c-2.7-.4-6.3-.3-10.1-.2a55 55 0 01-12.7-.6c-4.5-1.2-13.3-6-15.6-7.7-2.8-2.2-14.9-2.3-18.3-1.3a133 133 0 01-32.8 2.6l2-7.4 4.9.1a67 67 0 0011.2-.7l4.5-.7c12.5-2 17.1-2.9 18.4-3.7a25 25 0 0110.2 2l3.3 1.8c4.8 2.7 12 6.8 15.9 7.5 4.4.8 17.5-1.5 17.6-1.5h.2l1.2.1v-1.3l-1-.1-.8-.3.7-1.1 1-1.5V19c-.9.9-1.6 1.9-2.1 2.8a8 8 0 01-.8 1.1l.1.1-22.9-10.5-6.7-3.1 3.5-9.1h-1.3l-3.3 8.6-17-7.8.3-.8h-1.3L96 6.6a232.1 232.1 0 00-18.7-3.9c.6-.6 1-1.3 1.4-2L79 0h-2.6c-1.3 1.9-2.5 2.7-4 2.7-.6 0-3.8-1-7-2l-2.6-.8H55l4.7 1.4-1.2 4.9L51.3 5l2.1-5.1h-2.3l-2 4.7a32 32 0 01-3.1-.8l-4.7-2.4c.3-.4.5-.9.6-1.4h-1.3l-.2.8-1.5-.8h-2.6a203 203 0 009.5 5c.6.3 1.7.5 3.1.9l-1.1 2.6-3.2 7.7-2.6-1.1a52.7 52.7 0 01-10.5-8.9l4-6.1h-2.6l-.4.6-.4-.6h-1.5l1.2 1.7L30 4.6a132 132 0 01-4-4.5h-1.6a178 178 0 004.9 5.6l-4 6.6a22 22 0 00-5-4.1L19 6.3 14.9.2h-1.5C15 2.8 16.8 5.3 18 7.1l.4.6-1.3.2c-1.1.5-1.8 1.9-2.3 3l-.4.7.9.9.6-1c.4-.8.9-2 1.7-2.3 1.6-.7 4.4 1.8 7 4.4l-5.9 10-6.1-4 3.5-5.4-1.1-1a427 427 0 01-8.5 12.6c-1.7-1.7-4.3-4-6.5-5.8v1.7a196 196 0 015.8 5.2l-3.1 4.4-2.6-2.2v1.7l8.5 6.9L6.5 40h2.9l.9-1zm63.2-10.3l7.5-.5-.7 2.5-.9 3.5a81 81 0 01-6.7-2.5l.8-3zm-1.3 0l-1.7 6.1A94.4 94.4 0 0160 30l.3-1.3h11.9zM100.8 14A135 135 0 0183 17.4l-4.6.7-2.2.3-.8-8.5c4.1.5 11.4 1.3 12.6 1.3l1.9.2c2.7.3 7.6.8 11.1.8h.3c-.1.8-.3 1.6-.5 1.8zm1.4-.2l.7-2.8.3-1.3c2.7.9 5 1.8 6.4 2.8.6.5 1.5 1.9 2.3 3.2a25.8 25.8 0 00-9.7-1.9zm14.9-3.5l6.6 3.1 22.9 10.5c-3.9.6-12.4 1.8-15.4 1.2a73.5 73.5 0 01-16.7-8l2.6-6.8zM99 2l17 7.8-2.4 6.3c-1.1-2-2.2-3.9-3.2-4.6a46.6 46.6 0 00-13.1-4.8L99 2zM76.3 3.5l6.1 1.3A246 246 0 01102 9.3l-.3 1.4-.1.3c-3.5.1-8.7-.5-11.5-.8l-2-.2c-1 0-7.7-.7-12.7-1.3l-.3-4.4 1.2-.8zM75 18.6c-3.2.3-7.5.4-12 .2l2.7-10.3 6.8 1 .2.1 1.6.2c.1 3.2.5 6.5.7 8.8zM66.9 3.5c3.3 1 4.9 1.4 5.5 1.4l1.4-.2.3 3.8-1.2-.1c-.5-.2-2.1-.4-6.9-1.1l.9-3.8zm-5.8-1.8l3.7 1.1.9.3-1 4-4.8-.7 1.2-4.7zM49.7 9.3l1.3-3c3.8.7 9 1.4 13.4 2.1L63 13.6l-6.5-1.5-7.3-1.7.5-1.1zm-1 2.3l7.5 1.7 6.5 1.5-1 4c-5.3-.3-10.8-.9-15.1-2.1a133 133 0 002.1-5.1zm-2.6 6.3A75.6 75.6 0 0060.7 20h.6l-2 7.4a56 56 0 01-8-.9c-2.6-.6-5.3-1.4-7.9-2.4l2.7-6.2zM30.8 7.2c3.7 3.8 7.8 7.4 10.6 8.9l2.7 1.1-2.6 6.1a55.5 55.5 0 01-10.1-5.2c-1.3-.9-2.8-2.5-4.5-4.2l-.1-.1 4-6.6zm-4.7 7.7c1.7 1.7 3.2 3.3 4.6 4.3C33.3 21 37 22.9 41 24.5l-4.5 10.6-16-10.6 5.6-9.6zm-14.2 5.4l6.2 4-1 1.7-.8 1.4a2 2 0 01-.4.6 152 152 0 01-6.1-4.8l2.1-2.9zM5.3 33.5l-1.7-1.4 3.1-4.3.7.8c.6.8-.5 2.5-1.4 3.7l-.7 1.2zm1 .8l.8-1.3c1.1-1.6 2.4-3.6 1.3-5.1l-1-1.1 1.7-2.4 6.1 4.8c-1.6 2.4-3.6 5-5.9 7.6l-3-2.5z"/><path fill="#fff" d="M60.4 37.2l-1 2.8h1.3l.8-2.3-1.1-.5zM53.6 33.8l-1.2-.4-1 2.9-1.2 3.7h1.3l1.1-3.3 1-2.9zM2.4 7.8c1.7 0 3.3-.2 5-.4l3.9-.3-.1-1.3-4 .4c-2.4.2-4.9.5-7.2.3v1.3l.9.1L0 9.7v2.8l2.4-4.7z"/><path fill="#d1ccc4" d="M117.5 9.1l6.8 3.2 22.9 10.5.2.1-.1-.3.8-1.1c.5-.8 1.1-1.8 2-2.7v-.3c-1 .9-1.6 2-2.2 2.9-.3.4-.6 1-.8 1.1l-22.7-10.4-6.6-3.1 3.4-9.1h-.2l-3.5 9.2zM132 0h-.5a328.2 328.2 0 0118.6 7.1v-.2l-1.2-.6c-1.3-.6-9.2-3.5-16.9-6.3zM149 23.4l-.6-.3.7-1 1-1.4v-.3L149 22l-.7 1.1-.1.1h.1l.8.3 1 .1v-.2a2 2 0 01-1.1 0zM5.7 26.9l-3 4.3L0 29v.2l2.6 2.2.1.1.1-.1L5.9 27v-.1l-.1-.1a128 128 0 00-5.9-5.3v.2l5.8 5.2zM8.6 37.8v-.1l-8.6-7v.2a290 290 0 008.4 6.8L6.3 40h.2l2.1-2.2zM76.3 0c-1.3 1.8-2.5 2.6-3.9 2.6-.6 0-3.8-1-7-2L63.2 0h-.6l2.8.9c3.1 1 6.4 2 7 2 1.5 0 2.8-.9 4.1-2.8h-.2zM1 7.8l.1-.1H1l-1-.1v.2h.8L0 9.5v.4l1-2.1zM18.2 8.6l-.7.1c-.8.3-1.3 1.6-1.7 2.4l-.5.9-.8-.7.3-.7c.5-1 1.1-2.4 2.2-2.9.4-.2.8-.2 1.2-.2h.2l-.4-.7c-1.2-1.7-2.9-4.2-4.5-6.8h-.2c1.6 2.6 3.4 5.2 4.6 6.9l.3.4-1.1.2c-1.2.5-1.8 2-2.3 3l-.4.7.1.1-.1.1 1 .9.1-.1.6-1.1c.4-.8.9-2 1.6-2.3l.6-.1c1.6 0 4.1 2.3 6.3 4.5l-5.8 9.9-6-3.9 3.5-5.3v-.1l-1.3-.7v.1L6.6 25.7a202 202 0 00-6.5-5.8v.2c2.2 1.8 4.7 4.1 6.5 5.8l-.1.1.1-.1 8.5-12.5.9.6-3.5 5.3-.1.1.1.1 6.1 4 .1.1v-.1l5.9-10v-.2c-2.2-2.4-4.8-4.7-6.4-4.7z"/><path fill="#d1ccc4" d="M1.9 6.6l5.4-.3c1.2-.1 2.5-.3 3.8-.3v1.1l-3.8.3-4.9.3h-.1L0 12.2v.4l2.4-4.7c1.7 0 3.3-.2 4.9-.4l3.9-.3h.1l-.1-1.4h-.1l-3.9.3a47 47 0 01-5.3.3L0 6.3v.2l1.9.1zM32.8 0l-.3.4-.3-.4H32l.4.6.1.1.1-.1.4-.6h-.2zM148.8 24.7h-.2c-.1 0-9.6 1.7-15.1 1.7l-2.4-.2a74.4 74.4 0 01-15.9-7.5l-3.3-1.8c-3.3-1.6-7.7-2-9.6-2h-.7c-1.2.8-5.6 1.6-18.3 3.7l-4.5.7c-2.5.4-6.6.7-11.1.7l-4.9-.1h-.1v.1l-2 7.4v.1h.1l5.7.1c8.7 0 20.4-.7 27.1-2.7a28 28 0 016.8-.6c4.2 0 9.6.5 11.4 1.9a66.6 66.6 0 0015.6 7.8c2.3.6 5.7.7 8.4.7h8.5c2 0 4.1 0 5.9.3v-.2c-1.8-.2-3.9-.3-5.9-.3h-8.5c-2.7 0-6-.1-8.3-.7-4.5-1.2-13.3-6-15.5-7.7-1.8-1.4-7.3-1.9-11.5-1.9-3 0-5.5.2-6.9.6-6.7 2-18.4 2.7-27.1 2.7l-5.6-.1 1.9-7.2 4.8.1c4.6 0 8.7-.3 11.2-.7l4.5-.7c12.6-2 17.1-2.9 18.4-3.7h.6c1.9 0 6.3.4 9.5 2l3.3 1.8a74.4 74.4 0 0015.9 7.5l2.5.2c5.5 0 15.1-1.7 15.2-1.7h.1l.1.1V25l1.1.1v-.2c-.5-.2-.9-.2-1.2-.2zM72.8 8.5l1.3.2v-.1l-.2-3.9v-.1h-.1l-1.3.2c-.7 0-2.3-.4-5.5-1.4h-.1v.1L66 7.3v.1h.1c4.4.7 6.2.9 6.7 1.1zM67 3.6c3.2 1 4.7 1.4 5.5 1.4l1.3-.1.3 3.6-1.1-.1c-.6-.2-2.2-.4-6.8-1.1.2-1.5.5-2.8.8-3.7zM30.6 0h-.2l1.2 1.7-1.7 2.6L26 0h-.2l4 4.5.1.1.1-.1 1.8-2.8v-.2L30.6 0z"/><path fill="#d1ccc4" d="M72.4 9.5c-.5-.2-3.6-.6-6.8-1h-.1v.1l-2.7 10.3v.1h.1l4.5.1c2.7 0 5.3-.1 7.5-.3h.1v-.1l-.8-8.8v-.1h-.1l-1.6-.2-.1-.1zm2.5 9.1a83 83 0 01-7.4.3l-4.4-.1 2.6-10.1 6.7 1 .3.1 1.5.2c.1 2.9.4 6.2.7 8.6zM98.3 0L96 6.2c-4.6-1.1-9.4-2.1-13.3-2.8l-5.1-1L79 .5c-.1-.2 0-.4.1-.5h-.2l-.2.3-1.4 2-.1.1h.1l5.3 1.1c3.9.8 8.8 1.7 13.4 2.8h.1v-.1l2.4-6.4h-.2zM75.3 8.7c2.7.4 11.5 1.4 12.7 1.4l2 .2c2.6.3 7.5.8 11 .8h.7l.1-.4.3-1.4v-.1h-.1a187 187 0 00-19.6-4.5l-6.1-1.3-1.3.8h-.1v.1l.4 4.4zm1-5.1l6.1 1.3c5.3 1.1 13.4 2.6 19.5 4.5l-.3 1.3v.2c-3.5 0-8.7-.5-11.5-.8l-2-.2c-1.2 0-9.8-1-12.6-1.3l-.3-4.3 1.1-.7zM40.5 0l-.2.7L39 0h-.4l1.7.9.1.1V.9l.2-.8h-.1zM45.8 4.9c-1.2-.5-6.6-3.4-9.4-4.9H36c2.6 1.5 8.5 4.5 9.7 5.1l3 .8-4.2 10-2.5-1.1A54.5 54.5 0 0131.6 6l4-6.1h-.2l-4 6V6l.1.1C34 8.7 38.8 13.3 42 15c.7.4 1.6.8 2.6 1.1h.1l4.4-10.4H49a8.8 8.8 0 01-3.2-.8zM61 1.6l-1.2 4.8v.1h.1l4.8.7h.1v-.1l1-4V3l-1-.3L61 1.6zm3.8 1.3l.8.2-.9 3.8-4.6-.7 1.1-4.5 3.6 1.2zM101.1 12.1c-3.5 0-8.4-.5-11.1-.8l-1.9-.2c-1.1 0-8.4-.8-12.6-1.3h-.1v.1l.8 8.5v.1h.1l2.2-.3 4.6-.7c5.4-.9 16.7-2.7 17.9-3.5l.5-1.8v-.1h-.4zm-.3 1.9c-1.3.8-12.4 2.6-17.8 3.4l-4.6.7-2.1.3-.8-8.3c4.2.5 11.3 1.3 12.5 1.3l1.9.2c2.7.3 7.6.8 11.1.8h.2l-.4 1.6zM109.6 12.4c-1.3-.9-3.5-1.9-6.4-2.8h-.1v.1l-.3 1.3c-.2 1.1-.4 2.1-.7 2.8v.1h.1c2 0 6.2.4 9.6 1.9l.2.1-.1-.2a9.5 9.5 0 00-2.3-3.3zm-7.2 1.3l.6-2.7.3-1.2c2.9.9 5 1.9 6.3 2.8.5.3 1.2 1.3 2.1 2.9a27.1 27.1 0 00-9.3-1.8zM56.5 12.2l6.5 1.5h.1v-.1l1.3-5.2v-.1h-.1L51 6.2h-.1l-1.8 4.3h.1l7.3 1.7zM51 6.4l13.2 2.1-1.3 5-6.4-1.5-7.2-1.6 1.7-4z"/><path fill="#d1ccc4" d="M116.1 9.7L99 1.9h-.1V2l-1.7 4.6v.1h.1a41.2 41.2 0 0113 4.8c1 .7 2.1 2.6 3.2 4.6l.1.2.1-.2c.3-1.1 1.1-3.2 2.4-6.4zm-2.6 6.2a15 15 0 00-3.1-4.5 41.2 41.2 0 00-13-4.8l1.7-4.5 16.8 7.7-2.4 6.1zM117 10.3l-2.6 6.8v.1h.1l1.2.7a71 71 0 0015.5 7.3l2.2.1c3.5 0 9.3-.7 13.3-1.4h.3l-.3-.1-22.9-10.5-6.8-3zm5.7 2.7l1 .4 22.6 10.4c-3.9.6-9.5 1.3-12.9 1.3l-2.1-.1a76 76 0 01-15.5-7.3l-1.2-.7 2.6-6.6 5.5 2.6zM119.6 0l-3.2 8.5L99.6.8l.3-.8h-.2l-.3.8v.1h.1l17 7.8h.1v-.1l3.3-8.6h-.3zM31.3 18.2c2.5 1.8 6.1 3.6 10.1 5.2h.1l2.6-6.2H44l-2.7-1.1a53.3 53.3 0 01-10.6-8.9l.1-.2-.1.1-4 6.6v.1l.2.2c1.6 1.7 3.2 3.3 4.4 4.2zm-.5-10.9c2.5 2.6 7.2 7.1 10.5 8.9.8.4 1.6.8 2.6 1.1l-2.5 5.9c-4-1.6-7.5-3.4-10-5.2-1.3-.9-2.8-2.5-4.5-4.2l-.1-.1 4-6.4zM72.4 33.1l6.5 2.4a9.3 9.3 0 00-.4 3.3v.1c.1.3.3.6 1.1 1.1h.4c-1-.6-1.3-.9-1.3-1.2v-.1c-.1-.6.1-1.7.4-3.3v-.1H79a73 73 0 01-6.7-2.5h-.1v.1l-2 7.1h.2l2-6.9zM100.8 36.2a94.3 94.3 0 0016 3.8h1a135 135 0 00-7.6-1.3c-1.1-.1-6.4-1.8-9.3-2.7h-.1v.1L99.3 40h.2l1.3-3.8zM7.5 28.5l-.8-.8-.1-.1-.1.1L3.4 32v.1l.1.1 1.7 1.4.1.1.1-.1.8-1.2c.8-1.3 1.9-3 1.3-3.9zM6 32.2l-.7 1.2-1.5-1.3 3-4.2.7.7c.4.8-.7 2.4-1.5 3.6zM15.9 28.1h.1l.4-.6.8-1.3v-.1l1-1.7v-.1h-.1l-6.2-4h-.1v.1l-2 3v.1l.1.1 6 4.5zm-4-7.7l6 3.9-.9 1.6v.1l-.8 1.3-.3.5a135 135 0 01-5.9-4.6l1.9-2.8zM28.8 37.3l-11-8 .5-.8.8-1.4.9-1.5 16 10.6a104 104 0 00-1.6 3.7h.2l1.6-3.7v-.1h-.1L19.9 25.4l-.1-.1v.1l-.9 1.6-.8 1.4-.5.8v.1l.1.1 11.1 8.1 3.5 2.5h.3l-3.8-2.7z"/><path fill="#d1ccc4" d="M36.5 35.2h.1l4.5-10.6v-.1H41a54.2 54.2 0 01-10.3-5.3c-1.4-1-3-2.6-4.6-4.3l-.1-.1-5.6 9.6v.1h.1l16 10.7zM26.1 15c1.5 1.6 3.1 3.3 4.5 4.2 2.5 1.8 6.2 3.7 10.2 5.3l-4.4 10.4a1687 1687 0 00-15.9-10.5l5.6-9.4zM9.4 36.9c2.5-2.8 4.4-5.4 5.9-7.7V29c-2.8-2.1-4.8-3.6-6.1-4.8l-.1-.1-.1.2-1.7 2.4v.1l.1.1 1 1.1c1 1.5-.3 3.4-1.3 5a5 5 0 00-.8 1.4v.1h.1l3 2.4zM7.2 33c1.1-1.6 2.4-3.6 1.3-5.2l-1-1.1 1.6-2.2 6 4.7a86.4 86.4 0 01-5.8 7.5l-2.9-2.3.8-1.4zM55.5 0h-.7l5 1.4-1.2 4.7-7-1.1 2.1-5h-.2l-2.2 5.2h.1l7.2 1.2h.1v-.1L60 1.4v-.1h-.1L55.5 0zM10.3 39.1l1.5.9h.4l-1.8-1.1h-.1l-.1.1-1 1.1h.2l.9-1zM149.9 36.2h.1V36H149.7v.1l-2.3 4h.2l2.3-3.9zM132.4 35.9l3.3.1h8.5l4.2.1-2.3 4h.2l2.3-4 .1-.1h-.1l-4.3-.1h-8.5l-3.3-.1h-.1l-2.7 4.2h.2l2.5-4.1zM135.7 0h-.5A515.6 515.6 0 01150 5.7v-.2l-.6-.3c-1.1-.6-7-2.8-13.7-5.2zM24.4 0h-.2a178 178 0 004.9 5.6L25.2 12a26 26 0 00-4.9-4.1L19 6.1 14.9 0h-.2l4.1 6.2 1.3 1.9c1.7.9 3.5 2.6 5 4.1l.1.1.1-.1 4-6.6v-.2A77.3 77.3 0 0124.4 0zM37 39.8l6.1-14.4A51 51 0 0059 28.8l-2 7.5v.1l1.4.4v-.1l1.4-5.2c3.8 2 6.8 3.4 10.3 4.7L69 40.1h.2l1.1-3.9v-.1h-.1a94.4 94.4 0 01-10.5-4.8l-.1-.1v.1l-1.4 5.2-1-.3 2-7.5v-.1h-.1a51 51 0 01-16-3.4H43v.1l-6.1 14.6v.1h.1l.2.1h.6l-.8-.3z"/><path fill="#d1ccc4" d="M52.7 36.7l1-2.9v-.1l-1.4-.5v.1l-1 2.9-1.2 3.7h.2l1.2-3.7.9-2.8 1 .4-.9 2.8-1.1 3.3h.2l1.1-3.2zM61.6 37.8l-1.3-.7v.1l-1 2.8h.2l.9-2.7 1 .4-.8 2.2h.2l.8-2.1zM56.3 13.2l-7.5-1.7h-.1l-2.2 5.3h.1a78 78 0 0015.1 2.1h.1v-.1l1-4v-.1h-.1l-6.4-1.5zm5.3 5.5c-4.1-.2-10-.7-14.9-2l2.1-4.9 7.4 1.7 6.4 1.5-1 3.7zM51.2 0l-1.9 4.6-3-.8-4.7-2.4L42 0h-.2l-.4 1.4v.1h.1l4.7 2.4c.5.2 1.5.5 3.1.8h.1l2-4.8h-.2zM60.7 19.9c-4-.2-9.8-.8-14.6-2.1H46l-2.7 6.4h.1a52.6 52.6 0 0015.9 3.3h.1v-.1a278 278 0 012-7.4v-.1H61h-.3zm-1.4 7.4A51 51 0 0143.6 24l2.5-6a81.5 81.5 0 0014.6 2.1h.5l-1.9 7.2zM60.3 28.6l-.4 1.4h.1c3.9 2.1 7 3.5 10.5 4.9h.1v-.1l1.7-6.1v-.1h-.1l-5.8.1-6.1-.1zm6 .3l5.7-.1-1.6 5.9a92.4 92.4 0 01-10.3-4.8l.3-1.1 5.9.1zM79.3 34.3l.1-.1.9-3.5.7-2.5v-.1h-.1l-7.5.5h-.1v.1l-.8 3.1v.1h.1l6.7 2.4zm-5.7-5.5l7.3-.5-.7 2.4-.9 3.4c-2.1-.7-4.9-1.7-6.5-2.4l.8-2.9zM79.9 38.4c0-1.3.9-4.8 1.7-7.4l.8-2.9c4.8-.5 8.7-1.1 11.5-1.9 1.2-.3 3.5-.5 6.3-.5 4.2 0 9.4.5 10.8 1.6a63.5 63.5 0 0016.1 8c1 .3 2.3.5 3.8.6l-2.7 4.2h.2l2.8-4.2.1-.1h-.2c-1.6-.1-2.9-.3-3.9-.6a64.4 64.4 0 01-16-8c-1.4-1.1-6.5-1.7-10.9-1.7-2.8 0-5.1.2-6.3.6A62 62 0 0182.5 28h-.1v.1l-.8 3c-.7 2.6-1.7 6.2-1.7 7.5l2.4 1.5h.4c-1.4-.8-2.5-1.4-2.8-1.7z"/><path fill="#d1ccc4" d="M94.3 31l-1.3-.5v.1c-.6 2.4-1.8 6.3-2.8 9.4h.2l2.7-9.2 1 .3-2.6 9h.2l2.6-9.1zM110.3 36.6c-1-.1-8.5-2.5-9.9-2.9l-.4-.1c-.5 0-.9.3-1.1.8L96.8 40h.2l2.1-5.6c.1-.4.5-.6.9-.6l.3.1c1.3.5 8.9 2.8 9.9 3 .1 0 10 1.3 15.7 3.2h.5c-5.4-2.1-16-3.5-16.1-3.5zM15.6 32.6l1.4-2 10.9 8 2.1 1.5h.3L28 38.5l-11-8-.1-.1-.1.1-1.5 2.2-.1.1.1.1 10.4 7.3h.3l-10.4-7.6zM11.3 38l3.5-4.3c1.8 1.4 5.3 3.9 8.9 6.3h.3c-3.7-2.5-7.4-5.1-9.2-6.5l-.1-.1-.1.1-3.6 4.4v.1l.1.1 3.4 1.9h.4c-1.5-.8-2.8-1.4-3.6-2z"/><path fill="#ffe168" d="M58.6 34.9a680 680 0 01-41-20.7c-1.3-1.3-4.2-6.3-6.7-10.6L8.8 0H5.9l2.8 4.8c2.9 4.9 5.6 9.6 7.1 11.2a625 625 0 0041.8 21.3l5.7 2.7h5.9l-10.6-5.1zM95.4 32.8c-2.8-12-5.9-25.1-8.9-32.8h-2.7c3 7.4 6.4 22.1 9.1 33.3l1.6 6.7h2.6l-1.7-7.2zM111.2 39.7a44.4 44.4 0 018.9-21.9A219 219 0 01143.7 0h-4.4a188 188 0 00-21.1 16.1c-5.8 6.6-7.8 15-9.4 23.1l-.1.8h2.6l-.1-.3z"/><path fill="#d8b348" d="M57.7 37.2c-4-1.9-39.5-19-41.8-21.3-1.5-1.5-4.2-6.2-7.1-11.1L6 0h-.2l2.9 4.8c2.9 4.9 5.6 9.6 7.1 11.2 2.3 2.3 37.8 19.4 41.8 21.3l5.5 2.6h.4l-5.8-2.7zM95.5 32.7c-2.8-11.9-5.9-25-8.9-32.7h-.2c2.9 7.7 6 20.8 8.9 32.8L97 40h.2l-1.7-7.3zM108.8 39.2a46.6 46.6 0 019.4-23c2.3-2.6 11.1-9.1 21.2-16.2h-.3c-10 7-18.7 13.5-21 16.1-5.8 6.6-7.9 15-9.5 23.1l-.2.8h.2c.2-.3.2-.5.2-.8zM93 33.3A284.8 284.8 0 0083.9 0h-.2c3 7.4 6.4 22.1 9.1 33.4l1.6 6.6h.2L93 33.3z"/><path fill="#d8b348" d="M58.6 34.8a578.1 578.1 0 01-40.9-20.7c-1.3-1.3-4.2-6.3-6.7-10.6L9 0h-.3l2.1 3.6c2.5 4.4 5.4 9.3 6.8 10.7 1.4 1.3 19 10.2 40.9 20.7L69 40h.4l-10.8-5.2zM111.3 39.7a45.2 45.2 0 018.8-21.9A220 220 0 01143.8-.1h-.3c-11 7.7-21.2 15.1-23.5 17.8a44.8 44.8 0 00-8.9 21.9l-.1.3h.2l.1-.2z"/></g><rect x="0" y="0" fill="none" stroke="%s" stroke-width="%.4f" width="%.4f" height="%.4f"/><path fill="%s" transform="translate(%.4f,8)" d="M8 0c-2.8 0-5 2.2-5 5s4 11 5 11c1 0 5-8.2 5-11s-2.2-5-5-5zM8 8c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3z"></path></g>', $field_x, $label_offset_y, ($field_width / 150), (($field_height * 4) / 40), $ws_form_css->color_default_lighter, $ws_form_css->border_width, $field_width, ($field_height * 4), $ws_form_css->color_primary, (($field_width / 2) - 8));

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + ($field_height * 4));

							break;

						case 'texteditor' :
						case 'html' :

							// Rectangle
							$svg_field .= '<rect x="' . $field_x . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" stroke-dasharray="2 1" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . ($field_height * 2) . '"/>';

							// Label
							$svg_field .= '<text fill="' . $ws_form_css->color_default . '" transform="translate(' . $label_inside_x . ',' . $label_inside_y . ')" class="wsf-template-label">' . $field['label'] . '</text>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + ($field_height * 2));

							break;

						case 'recaptcha' :
						case 'hcaptcha' :
						case 'turnstile' :

							// Rectangle
							$svg_field .= '<rect x="' . $field_x . '" y="' . $label_offset_y . '" fill="#f9f9f9" stroke="#d3d3d3" stroke-width="1" width="' . $field_width . '" height="' . ($field_height * 2) . '"/>';

							$checkbox_x = (is_rtl() ? ($field_x + $field_width - ($field_height + ($field_height / 2))) : $field_x + ($field_height / 2));
							$label_x = (is_rtl() ? ($field_x + $field_width - ($field_height * 2)) : $field_x + ($field_height * 2));

							// Checkbox
							$svg_field .= '<rect x="' . $checkbox_x . '" y="' . ($label_offset_y + ($field_height / 2)) . '" fill="#ffffff" stroke="#d3d3d3" stroke-width="1" width="' . $field_height . '" height="' . $field_height . '"/>';

							// Label
							$svg_field .= '<text fill="#000000" transform="translate(' . $label_x . ',' . ($label_inside_y + ($field_height / 2)) . ')" class="wsf-template-label">I\'m not a robot</text>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + ($field_height * 2));

							break;

						case 'divider' :

							// Divider - Line
							$svg_field .= '<line x1="' . $field_x . '" x2="' . ($field_x + $field_width) . '" y1="' . ($label_offset_y + ($field_height / 2)) . '" y2="' . ($label_offset_y + ($field_height / 2)) . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + $field_height);

							break;

						case 'spacer' :

							// Add to SVG array
							$svg_single = array('svg' => '', 'height' => $field_height);

							break;

						case 'section_icons' :

							// Section Icons - Path +
							$svg_field .= '<path transform="translate(' . ($field_x + (is_rtl() ? ($field_height + 3) : 0)) . ',' . $label_offset_y . ')" d="M7.7,1.3A4.82,4.82,0,0,0,4.5,0,4.82,4.82,0,0,0,1.3,1.3,4.22,4.22,0,0,0,0,4.5,4.82,4.82,0,0,0,1.3,7.7,4.22,4.22,0,0,0,4.5,9,4.82,4.82,0,0,0,7.7,7.7,4.22,4.22,0,0,0,9,4.5,4.82,4.82,0,0,0,7.7,1.3Zm-3.2,7A3.8,3.8,0,1,1,8.3,4.5,3.8,3.8,0,0,1,4.5,8.3Zm.4-4.2H6.5v.7H4.9V6.4H4.1V4.9H2.6V4.1H4.2V2.6h.7Z"/>';

							// Section Icons - Path -
							$svg_field .= '<path transform="translate(' . ($field_x + (is_rtl() ? 0 : ($field_height + 3))) . ',' . $label_offset_y . ')" d="M4.5,9A4.82,4.82,0,0,1,1.3,7.7,4.22,4.22,0,0,1,0,4.5,4.82,4.82,0,0,1,1.3,1.3,4.22,4.22,0,0,1,4.5,0,4.82,4.82,0,0,1,7.7,1.3,4.22,4.22,0,0,1,9,4.5,4.82,4.82,0,0,1,7.7,7.7,4.22,4.22,0,0,1,4.5,9ZM4.5.7A3.8,3.8,0,1,0,8.3,4.5,3.8,3.8,0,0,0,4.5.7ZM6.4,4.1H2.6v.7H6.5V4.1Z"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + $field_height);

							break;

						case 'color' :

							// Color - Random Fill
							$rect_fill = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
							$rect_x = (is_rtl() ? ($field_x + $field_width - $field_height) : $field_x);

							// Default - Rectangle
							$svg_field .= '<rect x="' . $field_x . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . $field_height . '"/>';

							// Color - Rectangle
							$svg_field .= '<rect x="' . $rect_x . '" y="' . $label_offset_y . '" fill="' . $rect_fill . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_height . '" height="' . $field_height . '"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + $field_height);

							break;

						case 'checkbox' :
						case 'price_checkbox' :

							$rect_x = (is_rtl() ? ($svg_width - $field_x - $field_height) : $field_x);

							// Checkbox - Rectangle
							$svg_field .= '<rect x="' . $rect_x . '" y="0" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_height . '" height="' . $field_height . '"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $field_height);

							break;

						case 'radio' :
						case 'price_radio' :

							$circle_x = ((is_rtl() ? ($svg_width - $field_x - $field_height) : $field_x) + ($field_height / 2));

							// Radio - Circle
							$svg_field .= '<circle cx="' . $circle_x . '" cy="' . ($field_height / 2) . '" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" r="' . ($field_height / 2) . '"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $field_height);

							break;

						case 'file' :

							$button_width = ($field_width / 3);
							$button_xpos = (is_rtl() ? ($field_x + $field_width - $button_width) : $field_x);
							$label_button_x = $button_xpos + ($button_width / 2);

							// File - Rectangle - Outer
							$svg_field .= '<rect x="' . $field_x . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . $field_height . '"/>';

							// File - Rectangle - Button
							$svg_field .= '<rect x="' . $button_xpos . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_default_lightest . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $button_width . '" height="' . $field_height . '"/>';

							// File - Text - Button
							$svg_field .= '<text fill="' . $ws_form_css->color_default . '" transform="translate(' . $label_button_x . ' ' . ($label_offset_y + $label_inside_y) . ')" class="wsf-template-label" text-anchor="middle">Choose File</text>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + $field_height);

							break;

						default :

							// Default - Rectangle
							$svg_field .= '<rect x="' . $field_x . '" y="' . $label_offset_y . '" fill="' . $ws_form_css->color_default_inverted . '" stroke="' . $ws_form_css->color_default_lighter . '" stroke-width="' . $ws_form_css->border_width . '" rx="' . $ws_form_css->border_radius . '" width="' . $field_width . '" height="' . $field_height . '"/>';

							// Add to SVG array
							$svg_single = array('svg' => $svg_field, 'height' => $label_offset_y + $field_height);
					}
				}

				if($svg_single['svg'] !== false) {

					$svg_array[] = $svg_single;
				}

				// Col index
				$col_index += $field_size_columns + $field_offset_columns;
				if($col_index >= $col_index_max) {

					// Process row
					$get_svg_row_return = self::get_svg_row($svg_array);

					// Return row
					$svg .= sprintf('<g transform="translate(0,%f)">%s</g>', $offset_y, $get_svg_row_return['svg']);

					// Work out position of offset_x and offset_y
					$row_height = $get_svg_row_return['height'];
					$offset_y += $row_height + (($row_height > 0) ? $row_spacing : 0);

					// Reset for next row
					$col_index = 0;
					$svg_array = array();
					$offset_x = $origin_x;

				} else {

					$offset_x += $field_width + $gutter_width;
				}

				// Stop rendering if we're over the bottom edge
				if($offset_y > $svg_height) { break; }
			}

			// Add last row
			if(count($svg_array) > 0) {

				// Process row
				$get_svg_row_return = self::get_svg_row($svg_array);

				// Return row
				$svg .= sprintf('<g transform="translate(0,%f)">%s</g>', $offset_y, $get_svg_row_return['svg']);
			}

			// Left rectangle
			$svg .= sprintf('<rect x="0" y="0" width="%u" height="%u" fill="' . $ws_form_css->color_form_background . '" />', $origin_x - 1, $svg_height);

			// Right rectangle
			$svg .= sprintf('<rect x="%f" y="0" width="%u" height="%u" fill="' . $ws_form_css->color_form_background . '" />', ($svg_width - $origin_x) + 1, $origin_x, $svg_height);

			// Bottom rectangles
			$svg .= sprintf('<rect x="0" y="%f" width="%u" height="%u" fill="url(#%s)" />', ($svg_height - $gradient_height - $origin_x), $svg_width, $gradient_height, $gradient_id);
			$svg .= sprintf('<rect x="0" y="%f" width="%u" height="%u" fill="' . $ws_form_css->color_form_background . '" />', ($svg_height - $origin_x), $svg_width, $origin_x + 1);

			// End of SVG
			$svg .= '</svg>';

			return $svg;
		}

		// Get SVG row
		public function get_svg_row($svg_array) {

			$svg = '';
			$height = 0;

			// Get overall height
			foreach($svg_array as $svg_field) {

				$svg_field_height = $svg_field['height'];

				if($svg_field_height > $height) { $height = $svg_field_height; }
			}

			// Build SVG
			foreach($svg_array as $svg_field) {

				$svg_field_svg = $svg_field['svg'];
				$svg_field_height = $svg_field['height'];

				$svg .= sprintf('<g transform="translate(0,%f)">%s</g>', ($height - $svg_field_height), $svg_field_svg);
			}

			return array('svg' => $svg, 'height' => $height);
		}

		// Style resolve
		public function db_style_resolve($bypass_user_capability_check = false) {

			global $wpdb;

			// Meta
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'form';
			$form_meta_table_name = $ws_form_meta->db_get_table_name();

			// Style
			$ws_form_style = new WS_Form_Style();

			// Get all forms with missing or invalid style_id meta values
			$sql = "SELECT f.id FROM {$this->table_name} f LEFT JOIN $form_meta_table_name m ON m.parent_id = f.id AND m.meta_key = 'style_id' LEFT JOIN {$ws_form_style->table_name} s ON (m.meta_value = s.id OR m.meta_value = 0) AND s.status = 'publish' WHERE m.id IS NULL OR s.id IS NULL;";

			$forms = $wpdb->get_results($sql);

			if($forms) {

				$meta = array(

					'style_id' => 0 	// Style ID of 0 will use the default style
				);

				foreach($forms as $form) {

					$ws_form_meta->parent_id = $form->id;
					$ws_form_meta->db_update_from_array($meta, false, $bypass_user_capability_check);
				}
			}
		}

		// Style ID to zero (Used by style db_default method)
		public function style_id_to_zero($old_style_id) {

			global $wpdb;

			// Set all forms with style ID of $old_style_id to 0
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} f LEFT JOIN $form_meta_table_name m ON m.parent_id = f.id AND m.meta_key = 'style_id' SET m.meta_value = 0 WHERE m.meta_value = %d;",
				$old_style_id
			);

			$wpdb->query($sql);
		}

		// Style ID converational to zero (Used by style db_default method)
		public function style_id_conv_to_zero($old_style_id_conv) {

			global $wpdb;

			// Set all forms with style ID conv of $old_style_id_conv to 0
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} f LEFT JOIN $form_meta_table_name m ON m.parent_id = f.id AND m.meta_key = 'style_id_conv' SET m.meta_value = 0 WHERE m.meta_value = %d;",
				$old_style_id_conv
			);

			$wpdb->query($sql);
		}
	}