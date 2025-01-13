<?php

	class WS_Form_Action_User_Pods extends WS_Form_Action_User {

		public $pods_file_fields = array();
		public $pods_update_fields = array();
		public $pods_attachments = array();

		// Construct
		public function __construct() {

			// Settings
			add_filter('wsf_action_user_config_meta_keys', array($this, 'hook_config_meta_keys'), 10, 1);
			add_filter('wsf_action_user_action_settings', array($this, 'hook_action_settings'), 10, 1);
			add_filter('wsf_action_user_config_settings_form_admin', array($this, 'hook_config_settings_form_admin'), 10, 1);

			// Form building
			add_filter('wsf_action_user_list_fields', array($this, 'hook_list_fields'), 10, 2);
			add_filter('wsf_action_user_list_fields_meta_data', array($this, 'hook_list_fields_meta_data'), 10, 2);
			add_filter('wsf_action_user_form_actions', array($this, 'hook_form_actions'), 10, 3);
			add_filter('wsf_action_user_form_meta', array($this, 'hook_form_meta'), 10, 3);

			// Form submitting
			add_filter('wsf_action_user_field_mapping', array($this, 'hook_field_mapping'), 10, 5);
			add_action('wsf_action_user_file', array($this, 'hook_file'), 10, 2);
			add_action('wsf_action_user_attachments', array($this, 'hook_attachments'), 10, 1);
			add_action('wsf_action_user_user_meta', array($this, 'hook_user_meta'), 10, 5);

			// Form population
			add_filter('wsf_action_user_get', array($this, 'hook_get'), 10, 4);

			// Logo
			add_filter('wsf_action_user_svg_custom_field_logos', array($this, 'hook_svg_custom_field_logos'), 10, 2);
		}

		// Config meta keys
		public function hook_config_meta_keys($config_meta_keys) {

			// Pods - Fields
			$config_meta_keys['action_user_pods_field_id'] = array(

				'label'							=>	__('Pods Field', 'ws-form-user'),
				'type'							=>	'select',
				'options'						=>	is_admin() ? WS_Form_Pods::pods_get_fields_all('user', false, false, false, true, false) : array(),
				'options_blank'					=>	__('Select...', 'ws-form-user')
			);

			// Pods - Field mapping
			$config_meta_keys['action_user_field_mapping_pods'] = array(

				'label'						=>	__('Pods Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map WS Form fields to Pods fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'ws_form_field',
					'action_user_pods_field_id'
				),
				'meta_keys_unique'			=>	array(

					'action_user_pods_field_id'
				),
				'condition'					=>	array(

					array(

						'logic'			=>	'!=',
						'meta_key'		=>	'action_user_list_id',
						'meta_value'	=>	''
					)
				)
			);

			// Populate - Pods - Field mapping
			$config_meta_keys['action_user_form_populate_field_mapping_pods'] = array(

				'label'						=>	__('Pods Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map Pods field values to WS Form fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'action_user_pods_field_id',
					'ws_form_field'
				),
				'meta_keys_unique'			=>	array(

					'ws_form_field'
				),
				'condition'	=>	array(

					array(

						'logic'			=>	'==',
						'meta_key'		=>	'form_populate_action_id',
						'meta_value'	=>	'user'
					),

					array(

						'logic'			=>	'==',
						'meta_key'		=>	'form_populate_enabled',
						'meta_value'	=>	'on'
					)
				)
			);

			return $config_meta_keys;
		}

		// Process action settings
		public function hook_action_settings($settings) {

			array_splice($settings['meta_keys'], 2, 0, 'action_user_field_mapping_pods');

			return $settings;
		}

		// Process form populate
		public function hook_config_settings_form_admin($meta_keys) {

			parent::meta_key_inject($meta_keys, 'action_user_form_populate_field_mapping_pods', 'form_populate_tag_mapping');

			return $meta_keys;
		}

		// Process list fields
		public function hook_list_fields($list_fields, $list_id) {

			// Get fields
			$fields = WS_Form_Pods::pods_get_fields_all('user', false, false, true, false, false);

			// Process list fields
			$pods_fields_to_list_fields_return = WS_Form_Pods::pods_fields_to_list_fields($fields, $list_fields['group_index'], $list_fields['section_index']);

			// Merge return
			$list_fields['list_fields'] = array_merge_recursive($list_fields['list_fields'], $pods_fields_to_list_fields_return['list_fields']);
			$list_fields['group_index'] = $pods_fields_to_list_fields_return['group_index'];
			$list_fields['section_index'] = $pods_fields_to_list_fields_return['section_index'] + 1;

			return $list_fields;
		}

		// Process list fields meta data
		public function hook_list_fields_meta_data($list_fields_meta_data, $list_id) {

			// Get fields
			$fields = WS_Form_Pods::pods_get_fields_all('user', false, false, true, false, false);

			// Process meta data
			$pods_fields_to_meta_data_return = WS_Form_Pods::pods_fields_to_meta_data($fields, $list_fields_meta_data['group_index'], $list_fields_meta_data['section_index']);

			// Process return
			$list_fields_meta_data['group_meta_data'] = array_merge_recursive($list_fields_meta_data['group_meta_data'], $pods_fields_to_meta_data_return['group_meta_data']);
			$list_fields_meta_data['section_meta_data'] = array_merge_recursive($list_fields_meta_data['section_meta_data'], $pods_fields_to_meta_data_return['section_meta_data']);
			$list_fields_meta_data['group_index'] = $pods_fields_to_meta_data_return['group_index'];
			$list_fields_meta_data['section_index'] = $pods_fields_to_meta_data_return['section_index'] + 1;

			return $list_fields_meta_data;
		}

		// Process form actions
		public function hook_form_actions($form_actions, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_Pods::pods_get_fields_all('user', false, false, true, false, false);

			$pods_fields_to_list_fields_return = WS_Form_Pods::pods_fields_to_list_fields($fields);
			$list_fields = $pods_fields_to_list_fields_return['list_fields'];

			$form_actions[$this->id]['meta']['action_user_field_mapping_pods'] = array();

			foreach($list_fields as $list_field) {

				if(
					!isset($form_field_id_lookup_all[$list_field['id']]) ||
					!WS_Form_Pods::pods_field_mappable($list_field['action_type'])
				) {
					continue;
				}

				$form_actions[$this->id]['meta']['action_user_field_mapping_pods'][] = array(

					'ws_form_field' => $form_field_id_lookup_all[$list_field['id']],
					'action_user_pods_field_id' => $list_field['id']
				);
			}

			return $form_actions;
		}

		// Process form meta
		public function hook_form_meta($form_meta, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_Pods::pods_get_fields_all('user', false, false, false, true, false);

			$form_meta['action_user_form_populate_field_mapping_pods'] = array();

			foreach($fields as $field) {

				if(!isset($form_field_id_lookup_all[$field['value']])) { continue; }

				$form_meta['action_user_form_populate_field_mapping_pods'][] = array(

					'action_user_pods_field_id' => $field['value'],
					'ws_form_field' => $form_field_id_lookup_all[$field['value']]
				);
			}

			return $form_meta;
		}

		// Process field mapping
		public function hook_field_mapping($field_mapping_return, $form, $submit, $config, $list_id) {

			if($field_mapping_return === 'halt') { return 'halt'; }

			// Field mapping
			$field_mapping_pods = parent::get_config($config, 'action_user_field_mapping_pods', array());
			if(!is_array($field_mapping_pods)) { $field_mapping_pods = array(); }

			// Build pods ID to name lookup
			$pods_id_to_name_lookup = WS_Form_Pods::pods_get_id_to_name_lookup('user');

			// Run through each field mapping
			foreach($field_mapping_pods as $field_map_pods) {

				// Get Pods field ID
				$pods_field_id = $field_map_pods['action_user_pods_field_id'];

				// Get Pods field name
				if(!isset($pods_id_to_name_lookup[$pods_field_id])) { continue; }
				$pods_field_name = $pods_id_to_name_lookup[$pods_field_id];

				// Get submit value
				$field_id = $field_map_pods['ws_form_field'];
				$field_name = WS_FORM_FIELD_PREFIX . $field_id;
				$get_submit_value_repeatable_return = parent::get_submit_value_repeatable($submit, $field_name, array(), true);

				if(
					!is_array($get_submit_value_repeatable_return) ||
					!is_array($get_submit_value_repeatable_return['value']) ||
					!isset($get_submit_value_repeatable_return['value'][0])
				) { continue; }

				// Get Pods field type
				$pods_field_type = WS_Form_Pods::pods_get_field_type($pods_field_id);
				if($pods_field_type === false) { continue; }

				// Pods field type processing
				$pods_field_is_file = in_array($pods_field_type, WS_Form_Pods::pods_get_field_types_file());
				if($pods_field_is_file) {

					// Check to see if this field is attachment mapped, if it isn't, add it
					$field_already_mapped = false;
					foreach($field_mapping_return['attachment_mapping'] as $attachment_map) {

						if($attachment_map['ws_form_field'] == $field_id) {

							$field_already_mapped = true;
							break;
						}
					}
					if(!$field_already_mapped) {

						$field_mapping_return['attachment_mapping'][] = array('ws_form_field' => $field_id);
					}

					// Remember which Pods key this field needs to be mapped to
					$this->pods_file_fields[$field_id] = $pods_field_id;
				}

				// Get meta value
				$meta_value = $get_submit_value_repeatable_return['value'][0];

				// Convert empty arrays to empty strings
				if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

				// Process meta value
				$meta_value = WS_Form_Pods::pods_ws_form_field_value_to_pods_meta_value($meta_value, $pods_field_type, $pods_field_id);

				// If this is a file and no file submitted, then remove file by setting value to 0
				if($pods_field_is_file) {

					if(empty($meta_value)) {

						$this->pods_update_fields[$pods_field_name] = 0;
					}

				} else {

					// Add to fields to update
					$this->pods_update_fields[$pods_field_name] = $meta_value;
				}
			}

			return $field_mapping_return;
		}

		// Process file
		public function hook_file($file, $attachment_id) {

			// Build pods ID to name lookup
			$pods_id_to_name_lookup = WS_Form_Pods::pods_get_id_to_name_lookup('user');

			$field_id = $file['field_id'];

			if(isset($this->pods_file_fields[$field_id])) {

				// Get Pods field ID
				$pods_field_id = $this->pods_file_fields[$field_id];

				if(!isset($this->pods_attachments[$pods_field_id])) {

					$this->pods_attachments[$pods_field_id] = array(

						'field_id' => $pods_field_id,
						'meta_value_array' => array()
					);
				}

				$this->pods_attachments[$pods_field_id]['meta_value_array'][] = $attachment_id;
			}

			return true;
		}

		// Process attachements
		public function hook_attachments($list_id) {

			// Build pods ID to name lookup
			$pods_id_to_name_lookup = WS_Form_Pods::pods_get_id_to_name_lookup('user');

			foreach($this->pods_attachments as $pods_attachment) {

				// Get Pods field ID
				$pods_field_id = $pods_attachment['field_id'];

				// Get Pods field name
				if(!isset($pods_id_to_name_lookup[$pods_field_id])) { continue; }
				$pods_field_name = $pods_id_to_name_lookup[$pods_field_id];

				$meta_value_array = $pods_attachment['meta_value_array'];

				$meta_value = (count($meta_value_array) == 1) ? $meta_value_array[0] : $meta_value_array;

				$this->pods_update_fields[$pods_field_name] = $meta_value;
			}

			return true;
		}

		// Process user meta
		public function hook_user_meta($form, $submit, $config, $user_id, $list_id) {

			// Update fields
			$pods = pods('user', $user_id);
			$pods->save($this->pods_update_fields);

			return true;
		}

		// Process get
		public function hook_get($return_array, $form, $user_id, $list_id) {

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
 			$field_types = WS_Form_Config::get_field_types_flat();

			// Get Pods field mappings
			$field_mapping_pods = WS_Form_Common::get_object_meta_value($form, 'action_user_form_populate_field_mapping_pods', '');
			if(is_array($field_mapping_pods)) {

				// Get Pods field values for current user
				$pods_field_data = WS_Form_Pods::pods_get_field_data('user', false, $user_id);

				// Run through each mapping
				foreach($field_mapping_pods as $field_map_pods) {

					// Get Pods field key
					$pods_field_id = $field_map_pods->{'action_user_pods_field_id'};

					// Get field ID
					$field_id = $field_map_pods->ws_form_field;

					// Get meta value
					if(!isset($pods_field_data[$pods_field_id])) { continue; }

					// Read Pods field data
					$pods_field = $pods_field_data[$pods_field_id];
					$pods_field_values = $pods_field['values'];

					// Get Pods field type
					$pods_field_type = WS_Form_Pods::pods_get_field_type($pods_field_id);
					if($pods_field_type === false) { continue; }

					// Process pods_field_values
					$pods_field_values = WS_Form_Pods::pods_pods_meta_value_to_ws_form_field_value($pods_field_values, $pods_field_type, $field_id, $fields, $field_types);

					// Set value
					$return_array['fields'][$field_id] = $pods_field_values;
				}
			}

			return $return_array;
		}

		// Logo
		public function hook_svg_custom_field_logos($svg_custom_field_logos, $list_id) {

			if(WS_Form_Pods::pods_get_fields_all('user', false, false, true, false, true)) {

				$svg_custom_field_logos[] = '<path fill="#95BF3D" d="M0 22V0h22v22H0zm2.5-11c0 4.7 3.8 8.5 8.3 8.6 4.8.1 8.7-3.7 8.8-8.3.1-4.8-3.8-8.8-8.5-8.8-4.8 0-8.6 3.8-8.6 8.5z"/><path fill="#95BF3D" d="M3 11c0-4.4 3.6-8 8-8s8 3.6 8 8.1c0 4.4-3.6 8-8.1 8C6.6 19.1 3 15.4 3 11zm6.7 1.5c.2-.2.3-.2.5-.2 1.7 0 3.3-.6 4.8-1.5 1-.7 1.9-1.5 2.8-2.3.4-.4.3-1-.3-1.1-1.3-.5-2.2-1.4-2.7-2.7-.1-.3-.3-.4-.5-.5-1.4-.6-2.8-.8-4.3-.6C6.4 4 3.6 7 3.5 10.5c-.2 4.4 3.3 7.9 7.3 8 3.2 0 5.5-1.4 7-4.1.1-.2.1-.4.1-.6-.4-1.3-.3-2.6.4-3.8.2-.4.2-.7 0-1.1-2.4 2.2-5.1 3.8-8.6 3.6z"/><path fill="#95BF3D" d="M13.5 5.7c1.9.1 2.9 1.5 3.3 3 .1.2-.1.2-.2.2-1.5-.1-3.1-1.5-3.5-3 0-.3.2-.2.4-.2zM17 10.3c.6 1.8.4 3.5-.8 5.1-.8-1.3-.7-3.7.8-5.1zM15.4 12.2c0 1.4-.4 2.6-1.5 3.4-.1.1-.2.2-.3.1-.1-.1-.1-.2-.1-.3-.1-1.4.2-2.7 1.3-3.7.1-.1.3-.3.5-.3.1.1 0 .3.1.5v.3zM15.1 10.3c-1.5-.1-2.8-1.4-3.1-2.8-.1-.3.1-.2.2-.2.7.1 1.3.4 1.8.9.6.5 1 1.2 1.1 2.1zM13.4 12.5c0 1.4-.4 2.6-1.7 3.4-.1.1-.2.2-.3.1-.1-.1-.1-.2-.1-.3.1-1.2.4-2.3 1.6-3 .1-.1.2-.1.3-.2h.2zM10.8 8.5c1.2.1 2.4 1.3 2.6 2.5 0 .2 0 .2-.2.2-1.2-.2-2.4-1.3-2.6-2.5-.1-.2 0-.2.2-.2zM11.8 11.9c-1.2-.2-2-.9-2.4-2-.1-.3-.1-.5.3-.4 1.1.2 1.9 1.1 2.1 2.4zM9.4 15.4c.1-1.1.9-2.1 1.8-2.4.1 0 .3-.1.4 0 .1.1 0 .2 0 .3-.3 1.1-.8 1.9-1.9 2.3-.3.1-.4 0-.3-.2zM10.1 12c-1.1-.1-2-.9-2.3-1.9-.1-.4-.1-.4.2-.4 1.1.2 1.9 1.1 2.1 2.3zM8.5 12.6c-.8.7-1.7.7-2.6.1-.3-.2-.2-.3 0-.4.9-.5 1.8-.4 2.6.3zM6.3 9.9c.9.1 1.7.9 1.9 1.8 0 .2 0 .2-.2.2-.8 0-1.9-1.1-1.9-1.8 0-.3.1-.3.2-.2zM10 12.8c-.4 1-1.2 1.5-2.2 1.4-.3 0-.3-.1-.2-.4.6-.7 1.4-1.1 2.4-1zM4.3 10.2c.9-.1 1.6.3 2 1.1.1.3.1.3-.2.3-.8-.1-1.5-.6-1.8-1.4z"/>';
			}

			return $svg_custom_field_logos;
		}
	}

	new WS_Form_Action_User_Pods();

