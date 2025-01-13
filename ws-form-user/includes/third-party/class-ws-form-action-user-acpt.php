<?php

	class WS_Form_Action_User_ACPT extends WS_Form_Action_User {

		public $acpt_file_fields = array();
		public $acpt_update_fields = array();
		public $acpt_attachments = array();
		public $acpt_field_type_lookup = array();

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

			// ACPT - Fields
			$config_meta_keys['action_user_acpt_field_id'] = array(

				'label'							=>	__('ACPT Field', 'ws-form-user'),
				'type'							=>	'select',
				'options'						=>	is_admin() ? WS_Form_ACPT::acpt_get_fields_all('user', false, false, true, false) : array(),
				'options_blank'					=>	__('Select...', 'ws-form-user')
			);

			// ACPT - Field mapping
			$config_meta_keys['action_user_field_mapping_acpt'] = array(

				'label'						=>	__('ACPT Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map WS Form fields to ACPT fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'ws_form_field',
					'action_user_acpt_field_id'
				),
				'meta_keys_unique'			=>	array(

					'action_user_acpt_field_id'
				),
				'condition'					=>	array(

					array(

						'logic'			=>	'!=',
						'meta_key'		=>	'action_user_list_id',
						'meta_value'	=>	''
					)
				)
			);

			// Populate - ACPT - Field mapping
			$config_meta_keys['action_user_form_populate_field_mapping_acpt'] = array(

				'label'						=>	__('ACPT Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map ACPT field values to WS Form fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'action_user_acpt_field_id',
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

			array_splice($settings['meta_keys'], 7, 0, 'action_user_field_mapping_acpt');

			return $settings;
		}

		// Process form populate
		public function hook_config_settings_form_admin($meta_keys) {

			parent::meta_key_inject($meta_keys, 'action_user_form_populate_field_mapping_acpt', 'form_populate_tag_mapping');

			return $meta_keys;
		}

		// Process list fields
		public function hook_list_fields($list_fields, $list_id) {

			// Get fields
			$fields = WS_Form_ACPT::acpt_get_fields_all('user', false, true, false, false);

			// Process list fields
			$acpt_fields_to_list_fields_return = WS_Form_ACPT::acpt_fields_to_list_fields($fields, $list_fields['group_index'], $list_fields['section_index']);

			// Merge return
			$list_fields['list_fields'] = array_merge_recursive($list_fields['list_fields'], $acpt_fields_to_list_fields_return['list_fields']);
			$list_fields['group_index'] = $acpt_fields_to_list_fields_return['group_index'];
			$list_fields['section_index'] = $acpt_fields_to_list_fields_return['section_index'] + 1;

			return $list_fields;
		}

		// Process list fields meta data
		public function hook_list_fields_meta_data($list_fields_meta_data, $list_id) {

			$fields = WS_Form_ACPT::acpt_get_fields_all('user', false, true, false, false);

			$acpt_fields_to_meta_data_return = WS_Form_ACPT::acpt_fields_to_meta_data($fields, $list_fields_meta_data['group_index'], $list_fields_meta_data['section_index']);

			// Process return
			$list_fields_meta_data['group_meta_data'] = array_merge_recursive($list_fields_meta_data['group_meta_data'], $acpt_fields_to_meta_data_return['group_meta_data']);
			$list_fields_meta_data['section_meta_data'] = array_merge_recursive($list_fields_meta_data['section_meta_data'], $acpt_fields_to_meta_data_return['section_meta_data']);
			$list_fields_meta_data['group_index'] = $acpt_fields_to_meta_data_return['group_index'];
			$list_fields_meta_data['section_index'] = $acpt_fields_to_meta_data_return['section_index'] + 1;

			return $list_fields_meta_data;
		}

		// Process form actions
		public function hook_form_actions($form_actions, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_ACPT::acpt_get_fields_all('user', false, true, false, false);

			$acpt_fields_to_list_fields_return = WS_Form_ACPT::acpt_fields_to_list_fields($fields);
			$list_fields = $acpt_fields_to_list_fields_return['list_fields'];

			$form_actions['user']['meta']['action_user_field_mapping_acpt'] = array();

			foreach($list_fields as $list_field) {

				if(
					!isset($form_field_id_lookup_all[$list_field['id']]) ||
					!WS_Form_ACPT::acpt_field_mappable($list_field['action_type'])
				) {
					continue;
				}

				$form_actions['user']['meta']['action_user_field_mapping_acpt'][] = array(

					'ws_form_field' => $form_field_id_lookup_all[$list_field['id']],
					'action_user_acpt_field_id' => $list_field['id']
				);
			}


			return $form_actions;
		}

		// Process form meta
		public function hook_form_meta($form_meta, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_ACPT::acpt_get_fields_all('user', false, false, true, false);

			$form_meta['action_user_form_populate_field_mapping_acpt'] = array();

			foreach($fields as $field) {

				if(!isset($form_field_id_lookup_all[$field['value']])) { continue; }

				$form_meta['action_user_form_populate_field_mapping_acpt'][] = array(

					'action_user_acpt_field_id' => $field['value'],
					'ws_form_field' => $form_field_id_lookup_all[$field['value']]
				);
			}

			return $form_meta;
		}

		// Process field mapping
		public function hook_field_mapping($field_mapping_return, $form, $submit, $config, $list_id) {

			if($field_mapping_return === 'halt') { return 'halt'; }

			// Field mapping
			$field_mapping_acpt = parent::get_config($config, 'action_user_field_mapping_acpt', array());
			if(!is_array($field_mapping_acpt)) { $field_mapping_acpt = array(); }

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
			$field_types = WS_Form_Config::get_field_types_flat();

			// Run through each field mapping
			foreach($field_mapping_acpt as $field_map_acpt) {

				if(
					!isset($field_map_acpt['action_user_acpt_field_id']) ||
					!isset($field_map_acpt['ws_form_field'])
				) {
					continue;
				}

				// Get ACPT name
				$acpt_field_id = $field_map_acpt['action_user_acpt_field_id'];

				// Get submit value
				$field_id = $field_map_acpt['ws_form_field'];
				$field_name = WS_FORM_FIELD_PREFIX . $field_id;

				$get_submit_value_repeatable_return = parent::get_submit_value_repeatable($submit, $field_name, array(), true);
				if(
					!is_array($get_submit_value_repeatable_return) ||
					!is_array($get_submit_value_repeatable_return['value']) ||
					!isset($get_submit_value_repeatable_return['value'][0])
				) { continue; }

				// Get ACPT field type
				$acpt_field_type = WS_Form_ACPT::acpt_get_field_type($acpt_field_id, 'user');
				if($acpt_field_type === false) { continue; }

				// Get ACPT field name
				$acpt_field_name = WS_Form_ACPT::acpt_get_field_name($acpt_field_id, 'user');
				if($acpt_field_name === false) { continue; }

				// Get parent ACF field type
				$acpt_data = WS_Form_ACPT::acpt_get_parent_data($acpt_field_id, 'user');
				$acpt_parent_field_type = isset($acpt_data['type']) ? $acpt_data['type'] : false;
				$acpt_parent_field_acpt_field_id = isset($acpt_data['acpt_id']) ? $acpt_data['acpt_id'] : false;

				// Field type data formatting
				$meta_value_file_empty = array(

					'label' => '',
					'url' => ''
				);

				// ACPT field type processing
				$acpt_field_is_file = in_array($acpt_field_type, WS_Form_ACPT::acpt_get_field_types_file());
				if($acpt_field_is_file) {

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

					// Remember which ACPT key this field needs to be mapped to
					$this->acpt_file_fields[$field_id] = array(

						'acpt_field_id' => $acpt_field_id,
						'acpt_field_name' => $acpt_field_name, 
						'acpt_parent_field_type' => $acpt_parent_field_type,
						'acpt_parent_field_acpt_field_id' => $acpt_parent_field_acpt_field_id
					);;
				}

				// Get parent ACPT field type
				$acpt_parent_data = WS_Form_ACPT::acpt_get_parent_data($acpt_field_id, 'user');
				$acpt_parent_field_type = isset($acpt_parent_data['type']) ? $acpt_parent_data['type'] : false;

				// Check if parent is repeatable
				switch($acpt_parent_field_type) {

					case 'Repeater' :

						$acpt_parent_field_id = isset($acpt_parent_data['acpt_id']) ? $acpt_parent_data['acpt_id'] : false;

						if(!isset($this->acpt_update_fields[$acpt_parent_field_id])) {

							$this->acpt_update_fields[$acpt_parent_field_id] = array();
						}

						$repeatable_index = $get_submit_value_repeatable_return['repeatable_index'];

						// Add each value
						foreach($get_submit_value_repeatable_return['value'] as $repeater_index => $meta_value) {

							// Convert empty arrays to empty strings
							if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

							// Process meta value
							$meta_value = WS_Form_ACPT::acpt_ws_form_field_value_to_acpt_meta_value($meta_value, $acpt_field_type, $acpt_field_id, $field_id, $fields, $field_types);

							// As repeater
							if(!isset($this->acpt_update_fields[$acpt_parent_field_id][$repeater_index])) {

								$this->acpt_update_fields[$acpt_parent_field_id][$repeater_index] = array();
							}

							// Get field name
							$field_name = WS_Form_ACPT::acpt_get_field_name($acpt_field_id, 'user');
							if($field_name === false) { continue; }

							// If this is a file and no file submitted, then remove file by setting value to 0
							if($acpt_field_is_file) {

								if(empty($meta_value)) {

									$this->acpt_update_fields[$acpt_parent_field_id][$repeater_index][$field_name] = $meta_value_file_empty;
								}

							} else {

								// Add to fields to update
								$this->acpt_update_fields[$acpt_parent_field_id][$repeater_index][$field_name] = $meta_value;
							}
						}

						break;

					default :

						// Get meta value
						$meta_value = $get_submit_value_repeatable_return['value'][0];

						// Convert empty arrays to empty strings
						if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

						// Process meta value
						$meta_value = WS_Form_ACPT::acpt_ws_form_field_value_to_acpt_meta_value($meta_value, $acpt_field_type, $acpt_field_id, $field_id, $fields, $field_types);

						// If this is a file and no file submitted, then remove file by setting value to 0
						if($acpt_field_is_file) {

							if(empty($meta_value)) {

								$this->acpt_update_fields[$acpt_field_id] = $meta_value_file_empty;
							}

						} else {

							// Add to fields to update
							$this->acpt_update_fields[$acpt_field_id] = $meta_value;
						}
				}
			}

			return $field_mapping_return;
		}

		// Process file
		public function hook_file($file, $attachment_id) {

			$field_id = $file['field_id'];
			$repeatable = $file['repeatable'];
			$repeater_index = $file['repeater_index'];

			if(isset($this->acpt_file_fields[$field_id])) {

				// Get ACPT field ID
				$acpt_file_field = $this->acpt_file_fields[$field_id];

				$acpt_field_id = $acpt_file_field['acpt_field_id'];
				$acpt_field_name = $acpt_file_field['acpt_field_name'];
				$acpt_parent_field_type = $acpt_file_field['acpt_parent_field_type'];
				$acpt_parent_field_acpt_field_id = $acpt_file_field['acpt_parent_field_acpt_field_id'];

				$acpt_field_type = WS_Form_ACPT::acpt_get_field_type($acpt_field_id, 'user');

				// Get ACPT attachments key (Used to group attachment ID's together)
				$acpt_attachments_key = $acpt_field_id . ($repeatable ? '_' . $repeater_index : '');

				if(!isset($this->acpt_attachments[$acpt_attachments_key])) {

					$this->acpt_attachments[$acpt_attachments_key] = array(

						'acpt_field_id' => $acpt_field_id,
						'acpt_field_name' => $acpt_field_name,
						'acpt_parent_field_type' => $acpt_parent_field_type,
						'acpt_parent_field_acpt_field_id' => $acpt_parent_field_acpt_field_id,
						'meta_value' => array(),
						'repeatable' => $repeatable,
						'repeater_index' => $repeater_index						
					);
				}

				// Process by field type
				switch($acpt_field_type) {

					case 'File' :

						$this->acpt_attachments[$acpt_attachments_key]['meta_value'] = array(

							'url' => $file['file_url'],
							'label' => $file['name']
						);

						break;

					case 'Image' :
					case 'Video' :

						$this->acpt_attachments[$acpt_attachments_key]['meta_value'] = $file['file_url'];

						break;

					case 'Gallery' :

						$this->acpt_attachments[$acpt_attachments_key]['meta_value'][] = $file['file_url'];

						break;
				}
			}

			return true;
		}

		// Process attachements
		public function hook_attachments($list_id) {

			foreach($this->acpt_attachments as $acpt_attachment) {

				// Get field data
				$acpt_field_id = $acpt_attachment['acpt_field_id'];
				$acpt_field_name = $acpt_attachment['acpt_field_name'];
				$acpt_parent_field_field_id = $acpt_attachment['acpt_parent_field_acpt_field_id'];
				$acpt_parent_field_type = $acpt_attachment['acpt_parent_field_type'];
				$meta_value = $acpt_attachment['meta_value'];
				$repeatable = $acpt_attachment['repeatable'];
				$repeater_index = $acpt_attachment['repeater_index'];

				// Add to fields to update
				switch($acpt_parent_field_type) {

					case 'Repeater' :

						if(!isset($this->acpt_update_fields[$acpt_parent_field_field_id])) {

							$this->acpt_update_fields[$acpt_parent_field_field_id] = array();
						}

						// As repeater
						if(!isset($this->acpt_update_fields[$acpt_parent_field_field_id][$repeater_index])) {

							$this->acpt_update_fields[$acpt_parent_field_field_id][$repeater_index] = array();
						}

						$this->acpt_update_fields[$acpt_parent_field_field_id][$repeater_index][$acpt_field_name] = $meta_value;

						break;

					default :

						$this->acpt_update_fields[$acpt_field_id] = $meta_value;
				}
			}

			return true;
		}

		// Process user meta
		public function hook_user_meta($form, $submit, $config, $user_id, $list_id) {

			foreach($this->acpt_update_fields as $acpt_field_id => $acpt_field_value) {

				// Get field name
				$field_name = WS_Form_ACPT::acpt_get_field_name($acpt_field_id, 'user');
				if($field_name === false) { continue; }

				// Get box name
				$box_name = WS_Form_ACPT::acpt_get_box_name($acpt_field_id, 'user');
				if($box_name === false) { continue; }

				// Set args
				$args = array(

					'user_id' => $user_id,
					'box_name' => $box_name,
					'field_name' => $field_name,
					'value' => $acpt_field_value
				);

				// Set value
				save_acpt_meta_field_value($args);
			}

			return true;
		}

		// Process get
		public function hook_get($return_array, $form, $user_id, $list_id) {

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
			$field_types = WS_Form_Config::get_field_types_flat();

			// Get ACPT field mappings
			$field_mapping_acpt = WS_Form_Common::get_object_meta_value($form, 'action_user_form_populate_field_mapping_acpt', '');
			if(is_array($field_mapping_acpt)) {

				// Get ACPT field values for current user
				$acpt_field_data = WS_Form_ACPT::acpt_get_field_data('user', $user_id);

				// Run through each mapping
				foreach($field_mapping_acpt as $field_map_acpt) {

					// Get ACPT field key
					$acpt_field_id = $field_map_acpt->{'action_user_acpt_field_id'};

					// Get field ID
					$field_id = $field_map_acpt->ws_form_field;

					// Get meta value
					if(!isset($acpt_field_data[$acpt_field_id])) { continue; }

					// Read ACPT field data
					$acpt_field = $acpt_field_data[$acpt_field_id];
					$acpt_field_repeater = $acpt_field['repeater'];
					$acpt_field_values = $acpt_field['values'];

					// Get ACPT field type
					$acpt_field_type = WS_Form_ACPT::acpt_get_field_type($acpt_field_id, 'user');
					if($acpt_field_type === false) { continue; }

					// Process acpt_field_values
					$acpt_field_values = WS_Form_ACPT::acpt_acpt_meta_value_to_ws_form_field_value($acpt_field_values, $acpt_field_type, $acpt_field_repeater, $field_id, $fields, $field_types);

					// Set value
					if($acpt_field_repeater) {

						// Build section_repeatable_return
						if(
							isset($fields[$field_id]) &&
							isset($fields[$field_id]->section_repeatable) &&
							$fields[$field_id]->section_repeatable &&
							isset($fields[$field_id]->section_id) &&
							is_array($acpt_field_values)
						) {

							$section_id = $fields[$field_id]->section_id;
							$section_count = (isset($return_array['section_repeatable']['section_' . $section_id]) && isset($return_array['section_repeatable']['section_' . $section_id]['index'])) ? count($return_array['section_repeatable']['section_' . $section_id]['index']) : 1;
							if(count($acpt_field_values) > $section_count) { $section_count = count($acpt_field_values); }
							$return_array['section_repeatable']['section_' . $section_id] = array('index' => range(1, $section_count));
						}

						// Build fields_repeatable_return
						$return_array['fields_repeatable'][$field_id] = $acpt_field_values;

					} else {

						// Build fields_return
						$return_array['fields'][$field_id] = $acpt_field_values;
					}
				}
			}

			return $return_array;
		}

		// Logo
		public function hook_svg_custom_field_logos($svg_custom_field_logos, $list_id) {

			if(WS_Form_ACPT::acpt_get_fields_all('user', false, true, false, true)) {

				$svg_custom_field_logos[] = '<path fill="#02C39A" fill-rule="evenodd" clip-rule="evenodd" d="M16.7 5.1 11.4 2c-.3-.2-.6-.2-.9 0L5.2 5.1l5.3 3.1c.3.2.6.2.9 0l5.3-3.1zM12.3.5c-.8-.5-1.8-.5-2.6 0L1.8 5.1l7.9 4.6c.8.5 1.8.5 2.6 0l7.9-4.6L12.3.5zM19.1 9.1l-5.3 3.1c-.3.2-.4.4-.4.7v6.2l5.3-3.1c.3-.2.4-.4.4-.7V9.1zm-6.2 1.6c-.8.5-1.3 1.3-1.3 2.2V22l7.9-4.6c.8-.5 1.3-1.3 1.3-2.2V6.1l-7.9 4.6z"/><path d="M8.7 19v-6.2c0-.3-.2-.6-.4-.7l-5.4-3v6.2c0 .3.2.6.4.7l5.4 3zm1.7-6.1c0-.9-.5-1.8-1.3-2.2L1.2 6.1v9.1c0 .9.5 1.8 1.3 2.2l7.9 4.6v-9.1z" opacity=".5" fill-rule="evenodd" clip-rule="evenodd" fill="#02C39A"/>';
			}

			return $svg_custom_field_logos;
		}
	}

	new WS_Form_Action_User_ACPT();

