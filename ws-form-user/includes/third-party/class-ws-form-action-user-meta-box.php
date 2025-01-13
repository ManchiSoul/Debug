<?php

	class WS_Form_Action_User_Meta_Box extends WS_Form_Action_User {

		public $meta_box_file_fields = array();
		public $meta_box_update_fields = array();
		public $meta_box_attachments = array();

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

			// Meta Box - Fields
			$config_meta_keys['action_user_meta_box_field_id'] = array(

				'label'							=>	__('Meta Box Field', 'ws-form-user'),
				'type'							=>	'select',
				'options'						=>	is_admin() ? WS_Form_Meta_Box::meta_box_get_fields_all('user', false, false, false, true, false) : array(),
				'options_blank'					=>	__('Select...', 'ws-form-user')
			);

			// Meta Box - Field mapping
			$config_meta_keys['action_user_field_mapping_meta_box'] = array(

				'label'						=>	__('Meta Box Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map WS Form fields to Meta Box fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'ws_form_field',
					'action_user_meta_box_field_id'
				),
				'meta_keys_unique'			=>	array(

					'action_user_meta_box_field_id'
				),
				'condition'					=>	array(

					array(

						'logic'			=>	'!=',
						'meta_key'		=>	'action_user_list_id',
						'meta_value'	=>	''
					)
				)
			);

			// Populate - Meta Box - Field mapping
			$config_meta_keys['action_user_form_populate_field_mapping_meta_box'] = array(

				'label'						=>	__('Meta Box Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map Meta Box field values to WS Form fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'action_user_meta_box_field_id',
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

			array_splice($settings['meta_keys'], 2, 0, 'action_user_field_mapping_meta_box');

			return $settings;
		}

		// Process form populate
		public function hook_config_settings_form_admin($meta_keys) {

			parent::meta_key_inject($meta_keys, 'action_user_form_populate_field_mapping_meta_box', 'form_populate_tag_mapping');

			return $meta_keys;
		}

		// Process list fields
		public function hook_list_fields($list_fields, $list_id) {

			// Get fields
			$fields = WS_Form_Meta_Box::meta_box_get_fields_all('user', false, false, true, false, false);

			// Process list fields
			$meta_box_fields_to_list_fields_return = WS_Form_Meta_Box::meta_box_fields_to_list_fields($fields, $list_fields['group_index'], $list_fields['section_index']);

			// Merge return
			$list_fields['list_fields'] = array_merge_recursive($list_fields['list_fields'], $meta_box_fields_to_list_fields_return['list_fields']);
			$list_fields['group_index'] = $meta_box_fields_to_list_fields_return['group_index'];
			$list_fields['section_index'] = $meta_box_fields_to_list_fields_return['section_index'] + 1;

			return $list_fields;
		}

		// Process list fields meta data
		public function hook_list_fields_meta_data($list_fields_meta_data, $list_id) {

			// Get fields
			$fields = WS_Form_Meta_Box::meta_box_get_fields_all('user', false, false, true, false, false);

			// Process meta data
			$meta_box_fields_to_meta_data_return = WS_Form_Meta_Box::meta_box_fields_to_meta_data($fields, $list_fields_meta_data['group_index'], $list_fields_meta_data['section_index']);

			// Process return
			$list_fields_meta_data['group_meta_data'] = array_merge_recursive($list_fields_meta_data['group_meta_data'], $meta_box_fields_to_meta_data_return['group_meta_data']);
			$list_fields_meta_data['section_meta_data'] = array_merge_recursive($list_fields_meta_data['section_meta_data'], $meta_box_fields_to_meta_data_return['section_meta_data']);
			$list_fields_meta_data['group_index'] = $meta_box_fields_to_meta_data_return['group_index'];
			$list_fields_meta_data['section_index'] = $meta_box_fields_to_meta_data_return['section_index'] + 1;

			return $list_fields_meta_data;
		}

		// Process form actions
		public function hook_form_actions($form_actions, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_Meta_Box::meta_box_get_fields_all('user', false, false, true, false, false);

			$meta_box_fields_to_list_fields_return = WS_Form_Meta_Box::meta_box_fields_to_list_fields($fields);
			$list_fields = $meta_box_fields_to_list_fields_return['list_fields'];

			$form_actions[$this->id]['meta']['action_user_field_mapping_meta_box'] = array();

			foreach($list_fields as $list_field) {

				if(
					!isset($form_field_id_lookup_all[$list_field['id']]) ||
					!WS_Form_Meta_Box::meta_box_field_mappable($list_field['action_type'])
				) {
					continue;
				}

				$form_actions[$this->id]['meta']['action_user_field_mapping_meta_box'][] = array(

					'ws_form_field' => $form_field_id_lookup_all[$list_field['id']],
					'action_user_meta_box_field_id' => $list_field['id']
				);
			}

			return $form_actions;
		}

		// Process form meta
		public function hook_form_meta($form_meta, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_Meta_Box::meta_box_get_fields_all('user', false, false, false, true, false);

			$form_meta['action_user_form_populate_field_mapping_meta_box'] = array();

			foreach($fields as $field) {

				if(!isset($form_field_id_lookup_all[$field['value']])) { continue; }

				$form_meta['action_user_form_populate_field_mapping_meta_box'][] = array(

					'action_user_meta_box_field_id' => $field['value'],
					'ws_form_field' => $form_field_id_lookup_all[$field['value']]
				);
			}

			return $form_meta;
		}

		// Process field mapping
		public function hook_field_mapping($field_mapping_return, $form, $submit, $config, $list_id) {

			if($field_mapping_return === 'halt') { return 'halt'; }

			// Field mapping
			$field_mapping_meta_box = parent::get_config($config, 'action_user_field_mapping_meta_box', array());
			if(!is_array($field_mapping_meta_box)) { $field_mapping_meta_box = array(); }

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
 			$field_types = WS_Form_Config::get_field_types_flat();

			// Run through each field mapping
			foreach($field_mapping_meta_box as $field_map_meta_box) {

				// Get Meta Box key
				$meta_box_field_id = $field_map_meta_box['action_user_meta_box_field_id'];

				// Get submit value
				$field_id = $field_map_meta_box['ws_form_field'];
				$field_name = WS_FORM_FIELD_PREFIX . $field_id;
				$get_submit_value_repeatable_return = parent::get_submit_value_repeatable($submit, $field_name, array(), true);

				if(
					!is_array($get_submit_value_repeatable_return) ||
					!is_array($get_submit_value_repeatable_return['value']) ||
					!isset($get_submit_value_repeatable_return['value'][0])
				) { continue; }

				// Get Meta Box field type
				$meta_box_field_type = WS_Form_Meta_Box::meta_box_get_field_type($meta_box_field_id);
				if($meta_box_field_type === false) { continue; }

				// Field type data formatting
				switch($meta_box_field_type) {

					// Single image is not stored as an array
					case 'single_image' :

						$meta_value_file_empty = 0;
						break;

					default :

						$meta_value_file_empty = array();
				}

				// Meta Box field type processing
				$meta_box_field_is_file = in_array($meta_box_field_type, WS_Form_Meta_Box::meta_box_get_field_types_file());
				if($meta_box_field_is_file) {

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

					// Remember which Meta Box key this field needs to be mapped to
					$this->meta_box_file_fields[$field_id] = $meta_box_field_id;
				}

				// Get parent Meta Box field type
				$meta_box_parent_data = WS_Form_Meta_Box::meta_box_get_parent_data($meta_box_field_id);
				$meta_box_parent_field_type = isset($meta_box_parent_data['type']) ? $meta_box_parent_data['type'] : false;
				$meta_box_parent_repeater = isset($meta_box_parent_data['repeater']) ? $meta_box_parent_data['repeater'] : false;

				// Check if parent is repeatable
				switch($meta_box_parent_field_type) {

					case 'key_value' :
					case 'group' :

						$meta_box_parent_field_id = isset($meta_box_parent_data['field_id']) ? $meta_box_parent_data['field_id'] : false;

						if(!isset($this->meta_box_update_fields[$meta_box_parent_field_id])) {

							$this->meta_box_update_fields[$meta_box_parent_field_id] = array();
						}

						$repeatable_index = $get_submit_value_repeatable_return['repeatable_index'];

						// Add each value
						foreach($get_submit_value_repeatable_return['value'] as $repeater_index => $meta_value) {

							// Convert empty arrays to empty strings
							if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

							// Process meta value
							$meta_value = WS_Form_Meta_Box::meta_box_ws_form_field_value_to_meta_box_meta_value($meta_value, $meta_box_field_type, $meta_box_field_id, $field_id, $fields, $field_types);

							// Key value index changes
							if($meta_box_parent_field_id . '_key' === $meta_box_field_id) { $meta_box_field_id = 0; }
							if($meta_box_parent_field_id . '_value' === $meta_box_field_id) { $meta_box_field_id = 1; }

							// Add to fields to update
							if($meta_box_parent_repeater) {

								// As repeater (clone enabled)
								if(!isset($this->meta_box_update_fields[$meta_box_parent_field_id][$repeater_index])) {

									$this->meta_box_update_fields[$meta_box_parent_field_id][$repeater_index] = array();
								}

								// If this is a file and no file submitted, then remove file by setting value to 0
								if($meta_box_field_is_file) {

									if(empty($meta_value)) {

										$this->meta_box_update_fields[$meta_box_parent_field_id][$repeater_index][$meta_box_field_id] = $meta_value_file_empty;
									}

								} else {

									// Add to fields to update
									$this->meta_box_update_fields[$meta_box_parent_field_id][$repeater_index][$meta_box_field_id] = $meta_value;
								}

							} else {

								// As regular row (clone disabled)
								if(!isset($this->meta_box_update_fields[$meta_box_parent_field_id][$meta_box_field_id])) {

									$this->meta_box_update_fields[$meta_box_parent_field_id][$meta_box_field_id] = array();
								}

								// If this is a file and no file submitted, then remove file by setting value to 0
								if($meta_box_field_is_file) {

									if(empty($meta_value)) {

										$this->meta_box_update_fields[$meta_box_parent_field_id][$meta_box_field_id] = $meta_value_file_empty;
									}

								} else {

									// Add to fields to update
									$this->meta_box_update_fields[$meta_box_parent_field_id][$meta_box_field_id] = $meta_value;
								}
							}
						}

						break;

					default :

						// Get meta value
						$meta_value = $get_submit_value_repeatable_return['value'][0];

						// Convert empty arrays to empty strings
						if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

						// Process meta value
						$meta_value = WS_Form_Meta_Box::meta_box_ws_form_field_value_to_meta_box_meta_value($meta_value, $meta_box_field_type, $meta_box_field_id, $field_id, $fields, $field_types);

						// If this is a file and no file submitted, then remove file by setting value to 0
						if($meta_box_field_is_file) {

							if(empty($meta_value)) {

								$this->meta_box_update_fields[$meta_box_field_id] = $meta_value_file_empty;
							}

						} else {

							// Add to fields to update
							$this->meta_box_update_fields[$meta_box_field_id] = $meta_value;
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

			if(isset($this->meta_box_file_fields[$field_id])) {

				// Get Meta Box field ID
				$meta_box_field_id = $this->meta_box_file_fields[$field_id];

				// Get parent Meta Box field type
				$meta_box_parent_data = WS_Form_Meta_Box::meta_box_get_parent_data($meta_box_field_id);
				$meta_box_parent_field_id = isset($meta_box_parent_data['field_id']) ? $meta_box_parent_data['field_id'] : false;
				$meta_box_parent_repeater = isset($meta_box_parent_data['repeater']) ? $meta_box_parent_data['repeater'] : false;

				// Get ACF attachments key (Used to group attachment ID's together)
				$meta_box_attachments_key = $meta_box_field_id . ($repeatable ? '_' . $repeater_index : '');

				if(!isset($this->meta_box_attachments[$meta_box_attachments_key])) {

					$this->meta_box_attachments[$meta_box_attachments_key] = array(

						'parent_field_id' => $meta_box_parent_field_id,
						'parent_repeater' => $meta_box_parent_repeater,
						'field_id' => $meta_box_field_id,
						'meta_value_array' => array(),
						'repeatable' => $repeatable,
						'repeater_index' => $repeater_index
					);
				}

				$this->meta_box_attachments[$meta_box_attachments_key]['meta_value_array'][] = strval($attachment_id);
			}

			return true;
		}

		// Process attachements
		public function hook_attachments($list_id) {

			foreach($this->meta_box_attachments as $meta_box_attachment) {

				$meta_box_parent_field_id = $meta_box_attachment['parent_field_id'];
				$meta_box_parent_repeater = $meta_box_attachment['parent_repeater'];
				$meta_box_field_id = $meta_box_attachment['field_id'];
				$meta_value_array = $meta_box_attachment['meta_value_array'];
				$repeatable = $meta_box_attachment['repeatable'];
				$repeater_index = $meta_box_attachment['repeater_index'];

				// Get Meta Box field type
				$meta_box_field_type = WS_Form_Meta_Box::meta_box_get_field_type($meta_box_field_id);
				if($meta_box_field_type === false) { continue; }

				// Field type data formatting
				switch($meta_box_field_type) {

					// Single image is not stored as an array
					case 'single_image' :

						$meta_value = $meta_value_array[0];
						break;

					default :

						$meta_value = $meta_value_array;
				}

				// Add to fields to update
				if($meta_box_parent_field_id !== false) {

					if(!isset($this->meta_box_update_fields[$meta_box_parent_field_id])) {

						$this->meta_box_update_fields[$meta_box_parent_field_id] = array();
					}

					if($meta_box_parent_repeater) {

						if(!isset($this->meta_box_update_fields[$meta_box_parent_field_id][$repeater_index])) {

							$this->meta_box_update_fields[$meta_box_parent_field_id][$repeater_index] = array();
						}

						$this->meta_box_update_fields[$meta_box_parent_field_id][$repeater_index][$meta_box_field_id] = $meta_value;

					} else {

						if(!isset($this->meta_box_update_fields[$meta_box_parent_field_id])) {

							$this->meta_box_update_fields[$meta_box_parent_field_id] = array();
						}

						$this->meta_box_update_fields[$meta_box_parent_field_id][$meta_box_field_id] = $meta_value;
					}

				} else {

					$this->meta_box_update_fields[$meta_box_field_id] = $meta_value;
				}
			}

			return true;
		}

		// Process user meta
		public function hook_user_meta($form, $submit, $config, $user_id, $list_id) {

			// Add slashes
			$this->meta_box_update_fields = wp_slash($this->meta_box_update_fields);

			// Update fields
			foreach($this->meta_box_update_fields as $meta_box_field_id => $meta_value) {

				rwmb_set_meta($user_id, $meta_box_field_id, $meta_value, ['object_type' => 'user']);
			}

			return true;
		}

		// Process get
		public function hook_get($return_array, $form, $user_id, $list_id) {

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
			$field_types = WS_Form_Config::get_field_types_flat();

			// Get Meta Box field mappings
			$field_mapping_meta_box = WS_Form_Common::get_object_meta_value($form, 'action_user_form_populate_field_mapping_meta_box', '');
			if(is_array($field_mapping_meta_box)) {

				// Get Meta Box field values for current user
				$meta_box_field_data = WS_Form_Meta_Box::meta_box_get_field_data('user', false, $user_id);

				// Run through each mapping
				foreach($field_mapping_meta_box as $field_map_meta_box) {

					// Get Meta Box field key
					$meta_box_field_id = $field_map_meta_box->{'action_user_meta_box_field_id'};

					// Get field ID
					$field_id = $field_map_meta_box->ws_form_field;

					// Get meta value
					if(!isset($meta_box_field_data[$meta_box_field_id])) { continue; }

					// Read Meta Box field data
					$meta_box_field = $meta_box_field_data[$meta_box_field_id];
					$meta_box_field_repeater = $meta_box_field['repeater'];
					$meta_box_field_values = $meta_box_field['values'];

					// Get Meta Box field type
					$meta_box_field_type = WS_Form_Meta_Box::meta_box_get_field_type($meta_box_field_id);
					if($meta_box_field_type === false) { continue; }

					// Process meta_box_field_values
					$meta_box_field_values = WS_Form_Meta_Box::meta_box_meta_box_meta_value_to_ws_form_field_value($meta_box_field_values, $meta_box_field_type, $meta_box_field_repeater, $field_id, $fields, $field_types);

					// Set value
					if($meta_box_field_repeater) {

						// Build section_repeatable_return
						if(
							isset($fields[$field_id]) &&
							isset($fields[$field_id]->section_repeatable) &&
							$fields[$field_id]->section_repeatable &&
							isset($fields[$field_id]->section_id) &&
							is_array($meta_box_field_values)
						) {

							$section_id = $fields[$field_id]->section_id;
							$section_count = (isset($return_array['section_repeatable']['section_' . $section_id]) && isset($return_array['section_repeatable']['section_' . $section_id]['index'])) ? count($return_array['section_repeatable']['section_' . $section_id]['index']) : 1;
							if(count($meta_box_field_values) > $section_count) { $section_count = count($meta_box_field_values); }
							$return_array['section_repeatable']['section_' . $section_id] = array('index' => range(1, $section_count));
						}

						// Build fields_repeatable_return
						$return_array['fields_repeatable'][$field_id] = $meta_box_field_values;

					} else {

						// Build fields_return
						$return_array['fields'][$field_id] = $meta_box_field_values;
					}
				}
			}

			return $return_array;
		}

		// Logo
		public function hook_svg_custom_field_logos($svg_custom_field_logos, $list_id) {

			if(WS_Form_Meta_Box::meta_box_get_fields_all('user', false, false, true, false, true)) {

				$svg_custom_field_logos[] = '<path d="M1.9 0h18.2c1 0 1.9.9 1.9 1.9v18.2c0 1.1-.9 1.9-1.9 1.9H1.9c-1 0-1.9-.9-1.9-1.9V1.9C0 .9.9 0 1.9 0z" fill="#010101"/><path d="M14.3 13.6l.2-4.5-2.7 7.6h-1.4L7.6 9.2l.2 4.5v1.6l1.1.2v1.2H4.6v-1.2l1.1-.2V7.9l-1.1-.2V6.5H8.4L11 14l2.6-7.5h3.8v1.2l-1.1.2v7.3l1.1.2v1.2h-4.2v-1.2l1.1-.2v-1.6z" fill="#fff"/>';
			}

			return $svg_custom_field_logos;
		}
	}

	new WS_Form_Action_User_Meta_Box();

