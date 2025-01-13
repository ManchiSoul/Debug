<?php

	class WS_Form_Action_User_ACF extends WS_Form_Action_User {

		public $acf_file_fields = array();
		public $acf_update_fields = array();
		public $acf_attachments = array();

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

			// ACF - Fields
			$config_meta_keys['action_user_acf_key'] = array(

				'label'							=>	__('ACF Field', 'ws-form-user'),
				'type'							=>	'select',
				'options'						=>	is_admin() ? WS_Form_ACF::acf_get_fields_all(false, false, false, true) : array(),
				'options_blank'					=>	__('Select...', 'ws-form-user')
			);

			// ACF - Field mapping
			$config_meta_keys['action_user_field_mapping_acf'] = array(

				'label'						=>	__('ACF Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map WS Form fields to ACF fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'ws_form_field',
					'action_user_acf_key'
				),
				'meta_keys_unique'			=>	array(

					'action_user_acf_key'
				),
				'condition'					=>	array(

					array(

						'logic'			=>	'!=',
						'meta_key'		=>	'action_user_list_id',
						'meta_value'	=>	''
					)
				)
			);

			// ACF - Validation 
			$config_meta_keys['action_user_acf_validation'] = array(

				'label'						=>	__('Process ACF Validation', 'ws-form-user'),
				'type'						=>	'checkbox',
				'help'						=>	__('Enabling this will process ACF validation filters when the form is submitted.', 'ws-form-user'),
				'default'					=>	'on',
				'condition'					=>	array(

					array(

						'logic'			=>	'!=',
						'meta_key'		=>	'action_user_list_id',
						'meta_value'	=>	''
					)
				)
			);

			// Populate - ACF - Field mapping
			$config_meta_keys['action_user_form_populate_field_mapping_acf'] = array(

				'label'						=>	__('ACF Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map ACF field values to WS Form fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'action_user_acf_key',
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

			array_splice($settings['meta_keys'], 2, 0, 'action_user_acf_validation');
			array_splice($settings['meta_keys'], 2, 0, 'action_user_field_mapping_acf');

			return $settings;
		}

		// Process form populate
		public function hook_config_settings_form_admin($meta_keys) {

			parent::meta_key_inject($meta_keys, 'action_user_form_populate_field_mapping_acf', 'form_populate_tag_mapping');

			return $meta_keys;
		}

		// Process list fields
		public function hook_list_fields($list_fields, $list_id) {

			// Get filter
			$filter = self::get_fields_all_filter($list_id);

			// Get fields
			$fields = WS_Form_ACF::acf_get_fields_all($filter, false, true, false, false);

			// Process list fields
			$acf_fields_to_list_fields_return = WS_Form_ACF::acf_fields_to_list_fields($fields, $list_fields['group_index'], $list_fields['section_index']);

			// Merge return
			$list_fields['list_fields'] = array_merge_recursive($list_fields['list_fields'], $acf_fields_to_list_fields_return['list_fields']);
			$list_fields['group_index'] = $acf_fields_to_list_fields_return['group_index'];
			$list_fields['section_index'] = $acf_fields_to_list_fields_return['section_index'] + 1;

			return $list_fields;
		}

		// Process list fields meta data
		public function hook_list_fields_meta_data($list_fields_meta_data, $list_id) {

			// Get filter
			$filter = self::get_fields_all_filter($list_id);

			// Get fields
			$fields = WS_Form_ACF::acf_get_fields_all($filter, false, true, false, false);

			// Process meta data
			$acf_fields_to_meta_data_return = WS_Form_ACF::acf_fields_to_meta_data($fields, $list_fields_meta_data['group_index'], $list_fields_meta_data['section_index']);

			// Process return
			$list_fields_meta_data['group_meta_data'] = array_merge_recursive($list_fields_meta_data['group_meta_data'], $acf_fields_to_meta_data_return['group_meta_data']);
			$list_fields_meta_data['section_meta_data'] = array_merge_recursive($list_fields_meta_data['section_meta_data'], $acf_fields_to_meta_data_return['section_meta_data']);
			$list_fields_meta_data['group_index'] = $acf_fields_to_meta_data_return['group_index'];
			$list_fields_meta_data['section_index'] = $acf_fields_to_meta_data_return['section_index'] + 1;

			return $list_fields_meta_data;
		}

		// Process form actions
		public function hook_form_actions($form_actions, $form_field_id_lookup_all, $list_id) {

			// Get filter
			$filter = self::get_fields_all_filter($list_id);

			$fields = WS_Form_ACF::acf_get_fields_all($filter, false, true, false);

			$acf_fields_to_list_fields_return = WS_Form_ACF::acf_fields_to_list_fields($fields);
			$list_fields = $acf_fields_to_list_fields_return['list_fields'];

			$form_actions['user']['meta']['action_user_field_mapping_acf'] = array();

			foreach($list_fields as $list_field) {

				if(
					!isset($form_field_id_lookup_all[$list_field['id']]) ||
					!WS_Form_ACF::acf_field_mappable($list_field['action_type'])
				) {
					continue;
				}

				$form_actions['user']['meta']['action_user_field_mapping_acf'][] = array(

					'ws_form_field' => $form_field_id_lookup_all[$list_field['id']],
					'action_user_acf_key' => $list_field['id']
				);
			}

			return $form_actions;
		}

		// Process form meta
		public function hook_form_meta($form_meta, $form_field_id_lookup_all, $list_id) {

			// Get filter
			$filter = self::get_fields_all_filter($list_id);

			$fields = WS_Form_ACF::acf_get_fields_all($filter, false, false, true, false);

			$form_meta['action_user_form_populate_field_mapping_acf'] = array();

			foreach($fields as $field) {

				if(!isset($form_field_id_lookup_all[$field['value']])) { continue; }

				$form_meta['action_user_form_populate_field_mapping_acf'][] = array(

					'action_user_acf_key' => $field['value'],
					'ws_form_field' => $form_field_id_lookup_all[$field['value']]
				);
			}

			return $form_meta;
		}

		// Process field mapping
		public function hook_field_mapping($field_mapping_return, $form, $submit, $config, $list_id) {

			if($field_mapping_return === 'halt') { return 'halt'; }

			// Field mapping
			$field_mapping_acf = parent::get_config($config, 'action_user_field_mapping_acf', array());
			if(!is_array($field_mapping_acf)) { $field_mapping_acf = array(); }

			// ACF validation
			$acf_validation = parent::get_config($config, 'action_user_acf_validation', 'on');

				// Get first option value so we can use that to set the value
				$fields = WS_Form_Common::get_fields_from_form($form);

				// Get field types
				$field_types = WS_Form_Config::get_field_types_flat();

				// Field validation
				if($acf_validation) {

					$acf_field_validation_error = false;
				}

				// Run through each field mapping
				foreach($field_mapping_acf as $field_map_acf) {

					// Get ACF key
					$acf_key = $field_map_acf['action_user_acf_key'];

					// Get submit value
					$field_id = $field_map_acf['ws_form_field'];
					$field_name = WS_FORM_FIELD_PREFIX . $field_id;
					$get_submit_value_repeatable_return = parent::get_submit_value_repeatable($submit, $field_name, array(), true);

					if(
						!is_array($get_submit_value_repeatable_return) ||
						!is_array($get_submit_value_repeatable_return['value']) ||
						!isset($get_submit_value_repeatable_return['value'][0])
					) { continue; }

					// Get ACF field type
					$acf_field_type = WS_Form_ACF::acf_get_field_type($acf_key);
					if($acf_field_type === false) { continue; }

					// Get parent ACF field type
					$acf_data = WS_Form_ACF::acf_get_parent_data($acf_key);
					$acf_parent_field_type = isset($acf_data['type']) ? $acf_data['type'] : false;
					$acf_parent_field_acf_key = isset($acf_data['acf_key']) ? $acf_data['acf_key'] : false;

					// ACF field type processing
					$acf_field_is_file = in_array($acf_field_type, WS_Form_ACF::acf_get_field_types_file());
					if($acf_field_is_file) {

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

						// Remember which ACF key this field needs to be mapped to
						$this->acf_file_fields[$field_id] = array(

							'acf_key' => $acf_key,
							'acf_parent_field_type' => $acf_parent_field_type,
							'acf_parent_field_acf_key' => $acf_parent_field_acf_key
						);
					}

					// Check if parent is repeatable
					switch($acf_parent_field_type) {

						case 'repeater' :

							$repeatable_index = $get_submit_value_repeatable_return['repeatable_index'];

							if(!isset($this->acf_update_fields[$acf_parent_field_acf_key])) {

								$this->acf_update_fields[$acf_parent_field_acf_key] = array();
							}

							// Add each value
							foreach($get_submit_value_repeatable_return['value'] as $repeater_index => $meta_value) {

								if(!isset($this->acf_update_fields[$acf_parent_field_acf_key][$repeater_index])) {

									$this->acf_update_fields[$acf_parent_field_acf_key][$repeater_index] = array();
								}								

								// Convert empty arrays to empty strings
								if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

								// Process meta value
								$meta_value = WS_Form_ACF::acf_ws_form_field_value_to_acf_meta_value($meta_value, $acf_field_type, $field_id, $fields, $field_types, $acf_key);

								// ACF field validation
								if($acf_validation) {

									$section_repeatable_index = isset($repeatable_index[$repeater_index]) ? $repeatable_index[$repeater_index] : 0;
									$valid = WS_Form_ACF::acf_validate_value($submit, $field_id, $section_repeatable_index, $meta_value, $acf_key, sprintf('acf[%s]', $acf_key));
									if($valid !== true) { $acf_field_validation_error = true; }
								}

								// If this is a file and no file submitted, then remove file by setting value to null
								if($acf_field_is_file) {

									if(empty($meta_value)) {

										$this->acf_update_fields[$acf_parent_field_acf_key][$repeater_index][$acf_key] = null;
									}

								} else {

									// Add to fields to update
									$this->acf_update_fields[$acf_parent_field_acf_key][$repeater_index][$acf_key] = $meta_value;
								}
							}

							break;

						case 'group' :

							// Get meta value
							$meta_value = $get_submit_value_repeatable_return['value'][0];

							// Convert empty arrays to empty strings
							if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

							// Process meta value
							$meta_value = WS_Form_ACF::acf_ws_form_field_value_to_acf_meta_value($meta_value, $acf_field_type, $field_id, $fields, $field_types, $acf_key);

							// ACF field validation
							if($acf_validation) {

								$valid = WS_Form_ACF::acf_validate_value($submit, $field_id, 0, $meta_value, $acf_key, sprintf('acf[%s]', $acf_key));
								if($valid !== true) { $acf_field_validation_error = true; }
							}

							if(!isset($this->acf_update_fields[$acf_parent_field_acf_key])) {

								$this->acf_update_fields[$acf_parent_field_acf_key] = array();
							}

							// If this is a file and no file submitted, then remove file by setting value to null
							if($acf_field_is_file) {

								if(empty($meta_value)) {

									$this->acf_update_fields[$acf_parent_field_acf_key][$acf_key] = null;
								}

							} else {

								// Add to fields to update
								$this->acf_update_fields[$acf_parent_field_acf_key][$acf_key] = $meta_value;
							}

							break;

						default :

							// Get meta value
							$meta_value = $get_submit_value_repeatable_return['value'][0];

							// Convert empty arrays to empty strings
							if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

							// Process meta value
							$meta_value = WS_Form_ACF::acf_ws_form_field_value_to_acf_meta_value($meta_value, $acf_field_type, $field_id, $fields, $field_types, $acf_key);

							// ACF field validation
							if($acf_validation) {

								$valid = WS_Form_ACF::acf_validate_value($submit, $field_id, 0, $meta_value, $acf_key, sprintf('acf[%s]', $acf_key));
								if($valid !== true) { $acf_field_validation_error = true; }
							}

							// If this is a file and no file submitted, then remove file by setting value to null
							if($acf_field_is_file) {

								if(empty($meta_value)) {

									$this->acf_update_fields[$acf_key] = null;
								}

							} else {

								// Add to fields to update
								$this->acf_update_fields[$acf_key] = $meta_value;
							}
					}
				}

				// Check for ACF field validation errors
				if($acf_validation && $acf_field_validation_error) { return 'halt'; }

			return $field_mapping_return;
		}

		// Process file
		public function hook_file($file, $attachment_id) {

			$field_id = $file['field_id'];
			$repeatable = $file['repeatable'];
			$repeater_index = $file['repeater_index'];

			if(isset($this->acf_file_fields[$field_id])) {

				// Get ACF key
				$acf_file_field = $this->acf_file_fields[$field_id];

				$acf_key = $acf_file_field['acf_key'];
				$acf_parent_field_type = $acf_file_field['acf_parent_field_type'];
				$acf_parent_field_acf_key = $acf_file_field['acf_parent_field_acf_key'];

				// Get ACF attachments key (Used to group attachment ID's together)
				$acf_attachments_key = $acf_key . ($repeatable ? '_' . $repeater_index : '');

				if(!isset($this->acf_attachments[$acf_attachments_key])) {

					$this->acf_attachments[$acf_attachments_key] = array(

						'acf_key' => $acf_key,
						'acf_parent_field_type' => $acf_parent_field_type,
						'acf_parent_field_acf_key' => $acf_parent_field_acf_key,
						'meta_value_array' => array(),
						'repeatable' => $repeatable,
						'repeater_index' => $repeater_index
					);
				}

				$this->acf_attachments[$acf_attachments_key]['meta_value_array'][] = $attachment_id;
			}

			return true;
		}

		// Process attachements
		public function hook_attachments($list_id) {

			foreach($this->acf_attachments as $acf_attachment) {

				$acf_key = $acf_attachment['acf_key'];
				$acf_parent_field_acf_key = $acf_attachment['acf_parent_field_acf_key'];
				$meta_value_array = $acf_attachment['meta_value_array'];
				$repeatable = $acf_attachment['repeatable'];
				$repeater_index = $acf_attachment['repeater_index'];

				$meta_value = (count($meta_value_array) == 1) ? $meta_value_array[0] : $meta_value_array;

				// Add to fields to update
				if($repeatable) {

					if(!isset($this->acf_update_fields[$acf_parent_field_acf_key])) {

						$this->acf_update_fields[$acf_parent_field_acf_key] = array();
					}

					if(!isset($this->acf_update_fields[$acf_parent_field_acf_key][$repeater_index])) {

						$this->acf_update_fields[$acf_parent_field_acf_key][$repeater_index] = array();
					}

					$this->acf_update_fields[$acf_parent_field_acf_key][$repeater_index][$acf_key] = $meta_value;


				} else {

					$this->acf_update_fields[$acf_key] = $meta_value;
				}
			}

			return true;
		}

		// Process user meta
		public function hook_user_meta($form, $submit, $config, $user_id, $list_id) {

			// Add slashes
			$this->acf_update_fields = wp_slash($this->acf_update_fields);

			// Update fields
			foreach($this->acf_update_fields as $acf_key => $meta_value) {

				update_field($acf_key, $meta_value, sprintf('user_%u', $user_id));
			}

			return true;
		}

		// Process get
		public function hook_get($return_array, $form, $user_id, $list_id) {

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
 			$field_types = WS_Form_Config::get_field_types_flat();

			// Get ACF field mappings
			$field_mapping_acf = WS_Form_Common::get_object_meta_value($form, 'action_user_form_populate_field_mapping_acf', '');
			if(is_array($field_mapping_acf)) {

				// Get ACF field values for current user
				$acf_field_data = WS_Form_ACF::acf_get_field_data('user_' . $user_id);

				// Run through each mapping
				foreach($field_mapping_acf as $field_map_acf) {

					// Get ACF field key
					$acf_key = $field_map_acf->{'action_user_acf_key'};

					// Get field ID
					$field_id = $field_map_acf->ws_form_field;

					// Get meta value
					if(!isset($acf_field_data[$acf_key])) { continue; }

					// Read ACF field data
					$acf_field = $acf_field_data[$acf_key];
					$acf_field_repeater = $acf_field['repeater'];
					$acf_field_values = $acf_field['values'];

					// Get ACF field type
					$acf_field_type = WS_Form_ACF::acf_get_field_type($acf_key);
					if($acf_field_type === false) { continue; }

					// Process acf_field_values
					$acf_field_values = WS_Form_ACF::acf_acf_meta_value_to_ws_form_field_value($acf_field_values, $acf_field_type, $acf_field_repeater, $field_id, $fields, $field_types);

					// Set value
					if($acf_field_repeater) {

						// Build section_repeatable_return
						if(
							isset($fields[$field_id]) &&
							isset($fields[$field_id]->section_repeatable) &&
							$fields[$field_id]->section_repeatable &&
							isset($fields[$field_id]->section_id) &&
							is_array($acf_field_values)
						) {

							$section_id = $fields[$field_id]->section_id;
							$section_count = (isset($return_array['section_repeatable']['section_' . $section_id]) && isset($return_array['section_repeatable']['section_' . $section_id]['index'])) ? count($return_array['section_repeatable']['section_' . $section_id]['index']) : 1;
							if(count($acf_field_values) > $section_count) { $section_count = count($acf_field_values); }
							$return_array['section_repeatable']['section_' . $section_id] = array('index' => range(1, $section_count));
						}

						// Build fields_repeatable_return
						$return_array['fields_repeatable'][$field_id] = $acf_field_values;

					} else {

						// Build fields_return
						$return_array['fields'][$field_id] = $acf_field_values;
					}
				}
			}

			return $return_array;
		}

		// Logo
		public function hook_svg_custom_field_logos($svg_custom_field_logos, $list_id) {

			// Get filter
			$filter = self::get_fields_all_filter($list_id);

			if(WS_Form_ACF::acf_get_fields_all($filter, false, true, false, true)) {

				$svg_custom_field_logos[] = '<path fill="#47bda1" d="M22 .1v21.7c0 .2 0 .2-.2.2H.3c-.2 0-.3 0-.3-.2V.1h22zM10 14.2c.2 0 .3.1.5.1.6.2 1.2.2 1.8 0 .7-.2 1.3-.6 1.8-1.1v1c0 .2.1.2.2.2h1.3c.2 0 .2 0 .2-.2v-2.1c0-.2 0-.2.2-.2h2.3c.1 0 .2 0 .2-.2v-1.2c0-.2 0-.2-.2-.2h-2.2c-.2 0-.3 0-.3-.2v-.8c0-.2.1-.3.3-.3h2.4c.1 0 .2 0 .2-.2V7.5c0-.1 0-.2-.2-.2h-4.2c-.1 0-.2.1-.2.2v1.1l-.1-.1c-.8-.9-1.8-1.2-3-1.1-1.2.2-2 .8-2.6 1.8-.1.2-.2.4-.2.5-.1 0-.1-.1-.1-.2-.3-.6-.6-1.2-.8-1.8-.1-.2-.2-.3-.3-.3h-.7c-.2 0-.3.1-.4.2-.8 2.2-1.7 4.4-2.6 6.6 0 .1-.1.1 0 .2h1.6c.1 0 .1-.1.2-.1.1-.2.2-.5.3-.7 0-.1.1-.2.2-.2h2c.1 0 .2 0 .2.2.2.2.3.4.3.6 0 .1.1.2.2.2h1.5c.3 0 .3 0 .2-.2z"/><path d="M22 .1H.1c0-.1.1 0 .2 0h21.5c0-.1.1-.1.2 0z" fill="#aaa9aa"/><path fill="#f3f3f4" d="M10 14.2c-.6-1.4-1.2-2.8-1.7-4.2 0-.1-.1-.2-.1-.2.1-.1.1-.3.2-.4.2.1.2.4.3.6.6 1.3 1.1 2.6 1.6 4 0 .1.1.2.1.3-.1 0-.2-.1-.4-.1zM14 13.3c.4-.5.7-1 .8-1.6 0-.1.1-.2.2-.2s.1.1.1.2c-.2.7-.5 1.3-1.1 1.8.1 0 0-.1 0-.2zM14.1 8.4c.3 0 .4.3.5.5.3.4.5.8.6 1.3 0 .1.1.2-.1.2-.1 0-.2 0-.3-.2-.1-.6-.4-1.1-.8-1.5 0-.1.1-.2.1-.3z"/><path fill="#47bda1" d="M14.1 11v.5c-.1.1-.3.1-.5 0-.2 0-.2.1-.3.2-.4.8-1.3 1.3-2.2 1.1-.9-.2-1.5-1-1.5-1.9 0-.9.7-1.7 1.6-1.9.9-.2 1.8.3 2.1 1.1.1.2.1.2.3.2.2 0 .4-.1.5 0 0 .2-.1.5 0 .7-.1 0 0 0 0 0zM6.6 10.2l.6 1.5c0 .1 0 .1-.1.1h-.9c-.1 0-.1 0-.1-.1.2-.5.3-1 .5-1.5z"/>';
			}

			return $svg_custom_field_logos;
		}

		// Get ACF fields all filter
		public function get_fields_all_filter($list_id) {

			$filter = array(

				'user_id' => (($list_id == 'register') ? 'new' : get_current_user_id())
			);

			switch($list_id) {

				case 'register' :

					$filter['user_form'] = 'add';
					break;

				case 'update' :

					$filter['user_form'] = 'edit';
					break;
			}

			return $filter;
		}
	}

	new WS_Form_Action_User_ACF();

