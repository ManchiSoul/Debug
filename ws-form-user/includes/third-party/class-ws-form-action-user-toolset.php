<?php

	class WS_Form_Action_User_Toolset extends WS_Form_Action_User {

		public $toolset_file_fields = array();
		public $toolset_update_fields = array();
		public $toolset_attachments = array();
		public $toolset_field_type_lookup = array();

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

			// Toolset - Fields
			$config_meta_keys['action_user_toolset_field_slug'] = array(

				'label'							=>	__('Toolset Field', 'ws-form-user'),
				'type'							=>	'select',
				'options'						=>	is_admin() ? WS_Form_Toolset::toolset_get_fields_all(array('domain' => Toolset_Element_Domain::USERS), false, false, true, false) : array(),
				'options_blank'					=>	__('Select...', 'ws-form-user')
			);

			// Toolset - Field mapping
			$config_meta_keys['action_user_field_mapping_toolset'] = array(

				'label'						=>	__('Toolset Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map WS Form fields to Toolset fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'ws_form_field',
					'action_user_toolset_field_slug'
				),
				'meta_keys_unique'			=>	array(

					'action_user_toolset_field_slug'
				),
				'condition'					=>	array(

					array(

						'logic'			=>	'!=',
						'meta_key'		=>	'action_user_list_id',
						'meta_value'	=>	''
					)
				)
			);

			// Populate - Toolset - Field mapping
			$config_meta_keys['action_user_form_populate_field_mapping_toolset'] = array(

				'label'						=>	__('Toolset Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map Toolset field values to WS Form fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'action_user_toolset_field_slug',
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

			array_splice($settings['meta_keys'], 2, 0, 'action_user_field_mapping_toolset');

			return $settings;
		}

		// Process form populate
		public function hook_config_settings_form_admin($meta_keys) {

			parent::meta_key_inject($meta_keys, 'action_user_form_populate_field_mapping_toolset', 'form_populate_tag_mapping');

			return $meta_keys;
		}

		// Process list fields
		public function hook_list_fields($list_fields, $list_id) {

			// Get fields
			$fields = WS_Form_Toolset::toolset_get_fields_all(array('domain' => Toolset_Element_Domain::USERS), false, true, false, false);

			// Process list fields
			$toolset_fields_to_list_fields_return = WS_Form_Toolset::toolset_fields_to_list_fields($fields, $list_fields['group_index'], $list_fields['section_index']);

			// Merge return
			$list_fields['list_fields'] = array_merge_recursive($list_fields['list_fields'], $toolset_fields_to_list_fields_return['list_fields']);
			$list_fields['group_index'] = $toolset_fields_to_list_fields_return['group_index'];
			$list_fields['section_index'] = $toolset_fields_to_list_fields_return['section_index'] + 1;

			return $list_fields;
		}

		// Process list fields meta data
		public function hook_list_fields_meta_data($list_fields_meta_data, $list_id) {

			// Get fields
			$fields = WS_Form_Toolset::toolset_get_fields_all(array('domain' => Toolset_Element_Domain::USERS), false, true, false, false);

			// Process meta data
			$toolset_fields_to_meta_data_return = WS_Form_Toolset::toolset_fields_to_meta_data($fields, $list_fields_meta_data['group_index'], $list_fields_meta_data['section_index']);

			// Process return
			$list_fields_meta_data['group_meta_data'] = array_merge_recursive($list_fields_meta_data['group_meta_data'], $toolset_fields_to_meta_data_return['group_meta_data']);
			$list_fields_meta_data['section_meta_data'] = array_merge_recursive($list_fields_meta_data['section_meta_data'], $toolset_fields_to_meta_data_return['section_meta_data']);
			$list_fields_meta_data['group_index'] = $toolset_fields_to_meta_data_return['group_index'];
			$list_fields_meta_data['section_index'] = $toolset_fields_to_meta_data_return['section_index'] + 1;

			return $list_fields_meta_data;
		}

		// Process form actions
		public function hook_form_actions($form_actions, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_Toolset::toolset_get_fields_all(array('domain' => Toolset_Element_Domain::USERS), false, true, false, false);

			$toolset_fields_to_list_fields_return = WS_Form_Toolset::toolset_fields_to_list_fields($fields);
			$list_fields = $toolset_fields_to_list_fields_return['list_fields'];

			$form_actions[$this->id]['meta']['action_user_field_mapping_toolset'] = array();

			foreach($list_fields as $list_field) {

				if(
					!isset($form_field_id_lookup_all[$list_field['id']]) ||
					!WS_Form_Toolset::toolset_field_mappable($list_field['action_type'])
				) {
					continue;
				}

				$form_actions[$this->id]['meta']['action_user_field_mapping_toolset'][] = array(

					'ws_form_field' => $form_field_id_lookup_all[$list_field['id']],
					'action_user_toolset_field_slug' => $list_field['id']
				);
			}

			return $form_actions;
		}

		// Process form meta
		public function hook_form_meta($form_meta, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_Toolset::toolset_get_fields_all(array('domain' => Toolset_Element_Domain::USERS), false, false, true, false);

			$form_meta['action_user_form_populate_field_mapping_toolset'] = array();

			foreach($fields as $field) {

				if(!isset($form_field_id_lookup_all[$field['value']])) { continue; }

				$form_meta['action_user_form_populate_field_mapping_toolset'][] = array(

					'action_user_toolset_field_slug' => $field['value'],
					'ws_form_field' => $form_field_id_lookup_all[$field['value']]
				);
			}

			return $form_meta;
		}

		// Process field mapping
		public function hook_field_mapping($field_mapping_return, $form, $submit, $config, $list_id) {

			if($field_mapping_return === 'halt') { return 'halt'; }

			// Field mapping
			$field_mapping_toolset = parent::get_config($config, 'action_user_field_mapping_toolset', array());
			if(!is_array($field_mapping_toolset)) { $field_mapping_toolset = array(); }

			// Run through each field mapping
			foreach($field_mapping_toolset as $field_map_toolset) {

				// Get Toolset field slug
				$toolset_field_slug = $field_map_toolset['action_user_toolset_field_slug'];

				// Get submit value
				$field_id = $field_map_toolset['ws_form_field'];
				$field_name = WS_FORM_FIELD_PREFIX . $field_id;
				$get_submit_value_repeatable_return = parent::get_submit_value_repeatable($submit, $field_name, array(), true);

				if(
					!is_array($get_submit_value_repeatable_return) ||
					!is_array($get_submit_value_repeatable_return['value']) ||
					!isset($get_submit_value_repeatable_return['value'][0])
				) { continue; }

				// Get Toolset field type
				$toolset_field_type = WS_Form_Toolset::toolset_get_field_type($toolset_field_slug, 'wpcf-usermeta');
				if($toolset_field_type === false) { continue; }

				// Add to fields type lookup
				$this->toolset_field_type_lookup[$toolset_field_slug] = $toolset_field_type;

				// Toolset field type processing
				$toolset_field_is_file = in_array($toolset_field_type, WS_Form_Toolset::toolset_get_field_types_file());
				if($toolset_field_is_file) {

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

					// Remember which Toolset slug this field needs to be mapped to
					$this->toolset_file_fields[$field_id] = $toolset_field_slug;
				}

				// Get meta value
				$meta_value = $get_submit_value_repeatable_return['value'][0];

				// Convert empty arrays to empty strings
				if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

				// Process meta value
				$meta_value = WS_Form_Toolset::toolset_ws_form_field_value_to_toolset_meta_value($meta_value, $toolset_field_type, $toolset_field_slug, 'wpcf-usermeta');

				// If this is a file and no file submitted, then remove file by setting value to 0
				if($toolset_field_is_file) {

					if(empty($meta_value)) {

						$this->toolset_update_fields[$toolset_field_slug] = 0;
					}

				} else {

					// Add to fields to update
					$this->toolset_update_fields[$toolset_field_slug] = $meta_value;
				}
			}

			return $field_mapping_return;
		}

		// Process file
		public function hook_file($file, $attachment_id) {

			$field_id = $file['field_id'];

			if(isset($this->toolset_file_fields[$field_id])) {

				// Get Toolset field ID
				$toolset_field_slug = $this->toolset_file_fields[$field_id];

				if(!isset($this->toolset_attachments[$toolset_field_slug])) {

					$this->toolset_attachments[$toolset_field_slug] = array(

						'field_id' => $toolset_field_slug,
						'meta_value_array' => array()
					);
				}

				$this->toolset_attachments[$toolset_field_slug]['meta_value_array'][] = $file['file_url'];
			}

			return true;
		}

		// Process attachements
		public function hook_attachments($list_id) {

			foreach($this->toolset_attachments as $toolset_attachment) {

				// Get Toolset field ID
				$toolset_field_slug = $toolset_attachment['field_id'];

				$meta_value_array = $toolset_attachment['meta_value_array'];

				$meta_value = (count($meta_value_array) == 1) ? $meta_value_array[0] : $meta_value_array;

				$this->toolset_update_fields[$toolset_field_slug] = $meta_value;
			}

			return true;
		}

		// Process user meta
		public function hook_user_meta($form, $submit, $config, $user_id, $list_id) {

			// Add slashes
			$this->toolset_update_fields = wp_slash($this->toolset_update_fields);

			WS_Form_Toolset::toolset_update_meta($user_id, $this->toolset_update_fields, $this->toolset_field_type_lookup, 'wpcf-usermeta');

			return true;
		}

		// Process get
		public function hook_get($return_array, $form, $user_id, $list_id) {

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
 			$field_types = WS_Form_Config::get_field_types_flat();

			// Get Toolset field mappings
			$field_mapping_toolset = WS_Form_Common::get_object_meta_value($form, 'action_user_form_populate_field_mapping_toolset', '');
			if(is_array($field_mapping_toolset)) {

				// Get Toolset field values for current user
				$toolset_field_data = WS_Form_Toolset::toolset_get_field_data(array('domain' => Toolset_Element_Domain::USERS), $user_id);

				// Run through each mapping
				foreach($field_mapping_toolset as $field_map_toolset) {

					// Get Toolset field slug
					$toolset_field_slug = $field_map_toolset->{'action_user_toolset_field_slug'};

					// Get field ID
					$field_id = $field_map_toolset->ws_form_field;

					// Get meta value
					if(!isset($toolset_field_data[$toolset_field_slug])) { continue; }

					// Read Toolset field data
					$toolset_field = $toolset_field_data[$toolset_field_slug];
					$toolset_field_values = $toolset_field['values'];

					// Get Toolset field type
					$toolset_field_type = WS_Form_Toolset::toolset_get_field_type($toolset_field_slug, 'wpcf-usermeta');
					if($toolset_field_type === false) { continue; }

					// Process toolset_field_values
					$toolset_field_values = WS_Form_Toolset::toolset_toolset_meta_value_to_ws_form_field_value($toolset_field_values, $toolset_field_type, $field_id, $fields, $field_types, $user_id, $toolset_field_slug);

					// Build fields_return
					$return_array['fields'][$field_id] = $toolset_field_values;
				}
			}

			return $return_array;
		}

		// Logo
		public function hook_svg_custom_field_logos($svg_custom_field_logos, $list_id) {

			if(WS_Form_Toolset::toolset_get_fields_all(array('domain' => Toolset_Element_Domain::USERS), false, true, false, true)) {

				$svg_custom_field_logos[] = '<path fill="#EC793D" d="M1 5.1h2.4V2.9c0-.5.4-.9 1-.9H9c.5 0 1 .3 1 .9V5h2V2.9c0-.5.4-.9 1-.9h4.6c.5 0 1 .3 1 .9V5H21c.6 0 1 .3 1 .9v13.2c0 .4-.4.8-1 .8H1c-.5 0-1-.3-1-.9V6c.1-.5.5-.9 1-.9z"/><path fill="#ffffff" d="M16.2 7.3v-3h-1.5v3.1H7.5V4.3H5.9v3.1H2.5v10.2h17V7.3h-3.3z"/>';
			}


			return $svg_custom_field_logos;
		}
	}

	new WS_Form_Action_User_Toolset();

