<?php

	class WS_Form_Action_User_JetEngine extends WS_Form_Action_User {

		public $jetengine_file_fields = array();
		public $jetengine_update_fields = array();
		public $jetengine_attachments = array();
		public $jetengine_relations = array();

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

			// JetEngine - Field mapping
			$config_meta_keys['action_user_field_mapping_jetengine'] = array(

				'label'						=>	__('JetEngine Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map WS Form fields to JetEngine fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'ws_form_field',
					'action_user_jetengine_field_name'
				),
				'meta_keys_unique'			=>	array(

					'action_user_jetengine_field_name'
				),
				'condition'					=>	array(

					array(

						'logic'			=>	'!=',
						'meta_key'		=>	'action_user_list_id',
						'meta_value'	=>	''
					)
				)
			);

			// Populate - JetEngine - Field mapping
			$config_meta_keys['action_user_form_populate_field_mapping_jetengine'] = array(

				'label'						=>	__('JetEngine Field Mapping', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Map JetEngine field values to WS Form fields.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'action_user_jetengine_field_name',
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

			// JetEngine - Relations
			$config_meta_keys['action_user_jetengine_relations'] = array(

				'label'						=>	__('JetEngine Relations', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Set a JetEngine relation parent or child value to a field value.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'ws_form_field',
					'action_user_jetengine_relation_id',
					'action_user_jetengine_relation_context',
					'action_user_jetengine_relation_replace',
				),
				'meta_keys_unique'			=>	array(

					'action_user_jetengine_field_name'
				),
				'condition'					=>	array(

					array(

						'logic'			=>	'!=',
						'meta_key'		=>	'action_user_list_id',
						'meta_value'	=>	''
					)
				)
			);

			// JetEngine - Relations
			$config_meta_keys['action_user_jetengine_relations_populate'] = array(

				'label'						=>	__('JetEngine Relations', 'ws-form-user'),
				'type'						=>	'repeater',
				'help'						=>	__('Set a field to the value of a JetEngine relation parent or child value.', 'ws-form-user'),
				'meta_keys'					=>	array(

					'action_user_jetengine_relation_id',
					'action_user_jetengine_relation_context',
					'ws_form_field'
				),
				'meta_keys_unique'			=>	array(

					'action_user_jetengine_field_name'
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

			// JetEngine - Relations - ID
			$config_meta_keys['action_user_jetengine_relation_id'] = array(

				'label'							=>	__('Relation', 'ws-form-user'),
				'type'							=>	'select',
				'options'						=>	array()
			);

			// JetEngine - Relations - Context
			$config_meta_keys['action_user_jetengine_relation_context'] = array(

				'label'							=>	__('Context', 'ws-form-user'),
				'type'							=>	'select',
				'options'						=>	array(

					array('value' => 'parent', 'text' => __('Parent', 'ws-form-user')),
					array('value' => 'child', 'text' => __('Child', 'ws-form-user'))
				),
				'default'						=>	'child'
			);

			// JetEngine - Relations - Replace
			$config_meta_keys['action_user_jetengine_relation_replace'] = array(

				'label'						=>	__('Replace', 'ws-form-user'),
				'type'						=>	'select',
				'options'					=>	array(

					array('value' => '', 'text' => __('No', 'ws-form-user')),
					array('value' => 'on', 'text' => __('Yes', 'ws-form-user'))
				),
				'default'					=>	'on'
			);

			// JetEngine - Fields
			$config_meta_keys['action_user_jetengine_field_name'] = array(

				'label'							=>	__('JetEngine Field', 'ws-form-user'),
				'type'							=>	'select',
				'options'						=>	is_admin() ? WS_Form_JetEngine::jetengine_get_fields_all('user', null, false, false, true, false) : array(),
				'options_blank'					=>	__('Select...', 'ws-form-user')
			);

			// JetEngine - Populate relations
			if(is_admin()) {

				$relations = jet_engine()->relations->get_active_relations();

				if(is_array($relations)) {

					foreach($relations as $rel_id => $rel) {

						if(
							jet_engine()->relations->types_helper->object_is($rel->get_args('parent_object'), 'mix', 'users') ||
							jet_engine()->relations->types_helper->object_is($rel->get_args('child_object'), 'mix', 'users')
						) {
							$config_meta_keys['action_user_jetengine_relation_id']['options'][] = array(

								'value' => $rel_id,
								'text' => $rel->get_relation_name()
							);
						}
					}
				}
			}

			return $config_meta_keys;
		}

		// Process action settings
		public function hook_action_settings($settings) {

			array_splice($settings['meta_keys'], 2, 0, 'action_user_jetengine_relations');
			array_splice($settings['meta_keys'], 2, 0, 'action_user_field_mapping_jetengine');

			return $settings;
		}

		// Process form populate
		public function hook_config_settings_form_admin($meta_keys) {

			parent::meta_key_inject($meta_keys, 'action_user_form_populate_field_mapping_jetengine', 'form_populate_tag_mapping');
			parent::meta_key_inject($meta_keys, 'action_user_jetengine_relations_populate', 'form_populate_tag_mapping');

			return $meta_keys;
		}

		// Process list fields
		public function hook_list_fields($list_fields, $list_id) {

			// Get fields
			$fields = WS_Form_JetEngine::jetengine_get_fields_all('user', null, false, true, false, false);

			// Process list fields
			$jetengine_fields_to_list_fields_return = WS_Form_JetEngine::jetengine_fields_to_list_fields($fields, $list_fields['group_index'], $list_fields['section_index']);

			// Merge return
			$list_fields['list_fields'] = array_merge_recursive($list_fields['list_fields'], $jetengine_fields_to_list_fields_return['list_fields']);
			$list_fields['group_index'] = $jetengine_fields_to_list_fields_return['group_index'];
			$list_fields['section_index'] = $jetengine_fields_to_list_fields_return['section_index'] + 1;

			return $list_fields;
		}

		// Process list fields meta data
		public function hook_list_fields_meta_data($list_fields_meta_data, $list_id) {

			// Get fields
			$fields = WS_Form_JetEngine::jetengine_get_fields_all('user', null, false, true, false, false);

			// Process meta data
			$jetengine_fields_to_meta_data_return = WS_Form_JetEngine::jetengine_fields_to_meta_data($fields, $list_fields_meta_data['group_index'], $list_fields_meta_data['section_index']);

			// Process return
			$list_fields_meta_data['group_meta_data'] = array_merge_recursive($list_fields_meta_data['group_meta_data'], $jetengine_fields_to_meta_data_return['group_meta_data']);
			$list_fields_meta_data['section_meta_data'] = array_merge_recursive($list_fields_meta_data['section_meta_data'], $jetengine_fields_to_meta_data_return['section_meta_data']);
			$list_fields_meta_data['group_index'] = $jetengine_fields_to_meta_data_return['group_index'];
			$list_fields_meta_data['section_index'] = $jetengine_fields_to_meta_data_return['section_index'] + 1;

			return $list_fields_meta_data;
		}

		// Process form actions
		public function hook_form_actions($form_actions, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_JetEngine::jetengine_get_fields_all('user', null, false, true, false, false);

			$jetengine_fields_to_list_fields_return = WS_Form_JetEngine::jetengine_fields_to_list_fields($fields);
			$list_fields = $jetengine_fields_to_list_fields_return['list_fields'];

			$form_actions['user']['meta']['action_user_field_mapping_jetengine'] = array();

			foreach($list_fields as $list_field) {

				if(
					!isset($form_field_id_lookup_all[$list_field['id']]) ||
					!WS_Form_JetEngine::jetengine_field_mappable($list_field['action_type'])
				) {
					continue;
				}

				$form_actions['user']['meta']['action_user_field_mapping_jetengine'][] = array(

					'ws_form_field' => $form_field_id_lookup_all[$list_field['id']],
					'action_user_jetengine_field_name' => $list_field['id']
				);
			}

			return $form_actions;
		}

		// Process form meta
		public function hook_form_meta($form_meta, $form_field_id_lookup_all, $list_id) {

			$fields = WS_Form_JetEngine::jetengine_get_fields_all('user', null, false, false, true, false);

			$form_meta['action_user_form_populate_field_mapping_jetengine'] = array();

			foreach($fields as $field) {

				if(!isset($form_field_id_lookup_all[$field['value']])) { continue; }

				$form_meta['action_user_form_populate_field_mapping_jetengine'][] = array(

					'action_user_jetengine_field_name' => $field['value'],
					'ws_form_field' => $form_field_id_lookup_all[$field['value']]
				);
			}

			return $form_meta;
		}

		// Process field mapping
		public function hook_field_mapping($field_mapping_return, $form, $submit, $config, $list_id) {

			if($field_mapping_return === 'halt') { return 'halt'; }

			// Field mapping
			$field_mapping_jetengine = parent::get_config($config, 'action_user_field_mapping_jetengine', array());
			if(!is_array($field_mapping_jetengine)) { $field_mapping_jetengine = array(); }

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
 			$field_types = WS_Form_Config::get_field_types_flat();

			// Run through each field mapping
			foreach($field_mapping_jetengine as $field_map_jetengine) {

				// Get JetEngine field name
				$jetengine_field_name = $field_map_jetengine['action_user_jetengine_field_name'];

				// Get submit value
				$field_id = $field_map_jetengine['ws_form_field'];
				$field_name = WS_FORM_FIELD_PREFIX . $field_id;
				$get_submit_value_repeatable_return = parent::get_submit_value_repeatable($submit, $field_name, array(), true);

				if(
					!is_array($get_submit_value_repeatable_return) ||
					!is_array($get_submit_value_repeatable_return['value']) ||
					!isset($get_submit_value_repeatable_return['value'][0])
				) { continue; }

				// Get JetEngine field type
				$jetengine_field_type = WS_Form_JetEngine::jetengine_get_field_type($jetengine_field_name, 'user');
				if($jetengine_field_type === false) { continue; }

				// Get parent JetEngine field type
				$jetengine_data = WS_Form_JetEngine::jetengine_get_parent_data($jetengine_field_name, 'user');
				$jetengine_parent_field_type = isset($jetengine_data['type']) ? $jetengine_data['type'] : false;
				$jetengine_parent_field_jetengine_field_name = isset($jetengine_data['name']) ? $jetengine_data['name'] : false;

				// JetEngine field type processing
				$jetengine_field_is_file = in_array($jetengine_field_type, WS_Form_JetEngine::jetengine_get_field_types_file());
				if($jetengine_field_is_file) {

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

					// Remember which JetEngine key this field needs to be mapped to
					$this->jetengine_file_fields[$field_id] = array(

						'jetengine_field_name' => $jetengine_field_name,
						'jetengine_parent_field_type' => $jetengine_parent_field_type,
						'jetengine_parent_field_jetengine_field_name' => $jetengine_parent_field_jetengine_field_name
					);
				}

				// Check if parent is repeatable
				switch($jetengine_parent_field_type) {

					case 'repeater' :

						$repeatable_index = $get_submit_value_repeatable_return['repeatable_index'];

						if(!isset($this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name])) {

							$this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name] = array();
						}

						// Add each value
						foreach($get_submit_value_repeatable_return['value'] as $repeater_index => $meta_value) {

							$item_name = sprintf('item-%u', $repeater_index);

							if(!isset($this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name][$item_name])) {

								$this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name][$item_name] = array();
							}								

							// Convert empty arrays to empty strings
							if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

							// Process meta value
							$meta_value = WS_Form_JetEngine::jetengine_ws_form_field_value_to_jetengine_meta_value($meta_value, $jetengine_field_type, $jetengine_field_name, $field_id, $fields, $field_types, 'user');

							// If this is a file and no file submitted, then remove file by setting value to null
							if($jetengine_field_is_file) {

								if(empty($meta_value)) {

									$this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name][$item_name][$jetengine_field_name] = null;
								}

							} else {

								// Add to fields to update
								$this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name][$item_name][$jetengine_field_name] = $meta_value;
							}
						}

						break;

					default :

						// Get meta value
						$meta_value = $get_submit_value_repeatable_return['value'][0];

						// Convert empty arrays to empty strings
						if(is_array($meta_value) && (count($meta_value) == 0)) { $meta_value = ''; }

						// Process meta value
						$meta_value = WS_Form_JetEngine::jetengine_ws_form_field_value_to_jetengine_meta_value($meta_value, $jetengine_field_type, $jetengine_field_name, $field_id, $fields, $field_types, 'user');

						// If this is a file and no file submitted, then remove file by setting value to null
						if($jetengine_field_is_file) {

							if(empty($meta_value)) {

								$this->jetengine_update_fields[$jetengine_field_name] = null;
							}

						} else {

							// Add to fields to update
							$this->jetengine_update_fields[$jetengine_field_name] = $meta_value;
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

			if(isset($this->jetengine_file_fields[$field_id])) {

				// Get JetEngine file field
				$jetengine_file_field = $this->jetengine_file_fields[$field_id];

				$jetengine_field_name = $jetengine_file_field['jetengine_field_name'];
				$jetengine_parent_field_type = $jetengine_file_field['jetengine_parent_field_type'];
				$jetengine_parent_field_jetengine_field_name = $jetengine_file_field['jetengine_parent_field_jetengine_field_name'];

				// Get JetEngine attachments name (Used to group attachment ID's together)
				$jetengine_attachments_name = $jetengine_field_name . ($repeatable ? '_' . $repeater_index : '');

				if(!isset($this->jetengine_attachments[$jetengine_attachments_name])) {

					$this->jetengine_attachments[$jetengine_attachments_name] = array(

						'jetengine_field_name' => $jetengine_field_name,
						'jetengine_parent_field_type' => $jetengine_parent_field_type,
						'jetengine_parent_field_jetengine_field_name' => $jetengine_parent_field_jetengine_field_name,
						'meta_value_array' => array(),
						'repeatable' => $repeatable,
						'repeater_index' => $repeater_index
					);
				}

				$jetengine_field_settings = WS_Form_JetEngine::jetengine_get_field_settings($jetengine_field_name, 'user');

				$value_format = isset($jetengine_field_settings['value_format']) ? $jetengine_field_settings['value_format'] : 'id';

				switch($value_format) {

					case 'id' :

						$this->jetengine_attachments[$jetengine_attachments_name]['meta_value_array'][] = $attachment_id;
						break;

					case 'url' :

						$this->jetengine_attachments[$jetengine_attachments_name]['meta_value_array'][] = $file['file_url'];
						break;

					case 'both' :

						$this->jetengine_attachments[$jetengine_attachments_name]['meta_value_array'][] = array(

							'id' => $attachment_id,
							'url' => $file['file_url']
						);
						break;
				}
			}

			return true;
		}

		// Process attachements
		public function hook_attachments($list_id) {

			foreach($this->jetengine_attachments as $jetengine_attachment) {

				$jetengine_field_name = $jetengine_attachment['jetengine_field_name'];
				$jetengine_parent_field_jetengine_field_name = $jetengine_attachment['jetengine_parent_field_jetengine_field_name'];
				$jetengine_parent_field_type = $jetengine_attachment['jetengine_parent_field_type'];
				$meta_value_array = $jetengine_attachment['meta_value_array'];
				$repeatable = $jetengine_attachment['repeatable'];
				$repeater_index = $jetengine_attachment['repeater_index'];

				// Get parent JetEngine field type
				$jetengine_data = WS_Form_JetEngine::jetengine_get_parent_data($jetengine_field_name, 'user');
				$jetengine_parent_field_type = isset($jetengine_data['type']) ? $jetengine_data['type'] : false;
				$jetengine_parent_field_jetengine_field_name = isset($jetengine_data['name']) ? $jetengine_data['name'] : false;

				// Format according to field type and value_format
				$jetengine_field_settings = WS_Form_JetEngine::jetengine_get_field_settings($jetengine_field_name, 'user');

				$jetengine_field_type = isset($jetengine_field_settings['type']) ? $jetengine_field_settings['type'] : 'media';
				$jetengine_field_value_format = isset($jetengine_field_settings['value_format']) ? $jetengine_field_settings['value_format'] : 'id';

				// Determine meta value
				$meta_value = array();
				
				switch($jetengine_field_value_format) {

					case 'id' :
					case 'url' :

						$meta_value = implode(',', $meta_value_array);
						break;

					case 'both' :

						switch($jetengine_field_type) {
							
							case 'media' :
								
								$meta_value = isset($meta_value_array[0]) ? $meta_value_array[0] : array();
								break;
								
							case 'gallery' :
								
								$meta_value = $meta_value_array;
								break;
						}
						break;
				}

				// Add to fields to update
				switch($jetengine_parent_field_type) {

					case 'repeater' :

						$item_name = sprintf('item-%u', $repeater_index);

						if(!isset($this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name])) {

							$this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name] = array();
						}

						if(!isset($this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name][$item_name])) {

							$this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name][$item_name] = array();
						}

						$this->jetengine_update_fields[$jetengine_parent_field_jetengine_field_name][$item_name][$jetengine_field_name] = $meta_value;

						break;

					default :

						$this->jetengine_update_fields[$jetengine_field_name] = $meta_value;
				}
			}

			return true;
		}

		// Process user meta
		public function hook_user_meta($form, $submit, $config, $user_id, $list_id) {

			// Add slashes
			$this->jetengine_update_fields = wp_slash($this->jetengine_update_fields);

			// Update fields
			foreach($this->jetengine_update_fields as $meta_key => $meta_value) {

				update_user_meta($user_id, $meta_key, $meta_value);
			}

			// JetEngine relations
			$jetengine_relations = parent::get_config($config, 'action_user_jetengine_relations', array());
			if(!is_array($jetengine_relations)) { $jetengine_relations = array(); }

			// Run through each mapping
			foreach($jetengine_relations as $jetengine_relation) {

				// Get JetEngine relation ID
				$jetengine_relation_id = $jetengine_relation['action_user_jetengine_relation_id'];
				if(empty($jetengine_relation_id)) { continue; }

				// Get relation instance
				$relation_instance = jet_engine()->relations->get_active_relations($jetengine_relation_id);
				if($relation_instance === false) { continue; }

				// Get JetEngine relation context
				$jetengine_relation_context = $jetengine_relation['action_user_jetengine_relation_context'];
				if(empty($jetengine_relation_context)) { continue; }
				if(!in_array($jetengine_relation_context, array('child', 'parent'))) { continue; }

				// Set context
				$relation_instance->set_update_context($jetengine_relation_context);

				// Get JetEngine relation field ID
				$field_id = $jetengine_relation['ws_form_field'];
				$submit_value = parent::get_submit_value($submit, WS_FORM_FIELD_PREFIX . $field_id, false, true);
				if($submit_value === false) { continue; }

				// Get JetEngine relation replace
				$jetengine_relation_replace = ($jetengine_relation['action_user_jetengine_relation_replace'] == 'on');

				// Convert to array
				if(!is_array($submit_value)) { $submit_value = implode(',', $submit_value); }

				// Process according to context
				switch($jetengine_relation_context) {

					case 'parent' :

						// Use user ID as the item ID
						$child_id = $user_id;

						// Delete existing relation
						if($jetengine_relation_replace) {

							$relation_instance->delete_rows(false, $child_id);
						}

						// The submitted values are the parent ID
						foreach($submit_value as $parent_id) {

							$parent_id = absint($parent_id);
							if($parent_id === 0) { continue; }

							// Update relation
							$relation_instance->update($parent_id, $child_id);
						}

						break;

					case 'child' :

						// Use user ID as the parent ID
						$parent_id = $user_id;

						// Delete existing relations
						if($jetengine_relation_replace) {

							$relation_instance->delete_rows($parent_id);
						}

						// The submitted values are the child ID
						foreach($submit_value as $child_id) {

							$child_id = absint($child_id);
							if($child_id === 0) { continue; }

							$relation_instance->update($parent_id, $child_id);
						}

						break;
				}
			}
		}

		// Process get
		public function hook_get($return_array, $form, $user_id, $list_id) {

			// Get first option value so we can use that to set the value
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Get field types
 			$field_types = WS_Form_Config::get_field_types_flat();

			// Get JetEngine field mappings
			$field_mapping_jetengine = WS_Form_Common::get_object_meta_value($form, 'action_user_form_populate_field_mapping_jetengine', '');
			if(is_array($field_mapping_jetengine)) {

				// Get JetEngine field values for current user
				$jetengine_field_data = WS_Form_JetEngine::jetengine_get_field_data('user', null, $user_id);

				// Run through each mapping
				foreach($field_mapping_jetengine as $field_map_jetengine) {

					// Get JetEngine field name
					$jetengine_field_name = $field_map_jetengine->{'action_user_jetengine_field_name'};

					// Get field ID
					$field_id = $field_map_jetengine->ws_form_field;

					// Get meta value
					if(!isset($jetengine_field_data[$jetengine_field_name])) { continue; }

					// Read JetEngine field data
					$jetengine_field = $jetengine_field_data[$jetengine_field_name];
					$jetengine_field_repeater = $jetengine_field['repeater'];
					$jetengine_field_values = $jetengine_field['values'];

					// Get JetEngine field type
					$jetengine_field_type = WS_Form_JetEngine::jetengine_get_field_type($jetengine_field_name, 'user');
					if($jetengine_field_type === false) { continue; }

					// Process jetengine_field_values
					$jetengine_field_values = WS_Form_JetEngine::jetengine_jetengine_meta_value_to_ws_form_field_value($jetengine_field_values, $jetengine_field_type, $jetengine_field_repeater, $jetengine_field_name, $field_id, $fields, $field_types, 'user');

					// Set value
					if($jetengine_field_repeater) {

						// Build section_repeatable_return
						if(
							isset($fields[$field_id]) &&
							isset($fields[$field_id]->section_repeatable) &&
							$fields[$field_id]->section_repeatable &&
							isset($fields[$field_id]->section_id) &&
							is_array($jetengine_field_values)
						) {

							$section_id = $fields[$field_id]->section_id;
							$section_count = (isset($return_array['section_repeatable']['section_' . $section_id]) && isset($return_array['section_repeatable']['section_' . $section_id]['index'])) ? count($return_array['section_repeatable']['section_' . $section_id]['index']) : 1;
							if(count($jetengine_field_values) > $section_count) { $section_count = count($jetengine_field_values); }
							$return_array['section_repeatable']['section_' . $section_id] = array('index' => range(1, $section_count));
						}

						// Build fields_repeatable_return
						$return_array['fields_repeatable'][$field_id] = $jetengine_field_values;

					} else {

						// Build fields_return
						$return_array['fields'][$field_id] = $jetengine_field_values;
					}
				}
			}

			// Get JetEngine Relation mappings
			$jetengine_relations_populate = WS_Form_Common::get_object_meta_value($form, 'action_user_jetengine_relations_populate', '');
			if(is_array($jetengine_relations_populate)) {

				// Run through each mapping
				foreach($jetengine_relations_populate as $jetengine_relation) {

					// Get JetEngine relation ID
					$jetengine_relation_id = $jetengine_relation->{'action_user_jetengine_relation_id'};
					if(empty($jetengine_relation_id)) { continue; }

					// Get relation instance
					$relation_instance = jet_engine()->relations->get_active_relations($jetengine_relation_id);
					if($relation_instance === false) { continue; }

					// Get JetEngine relation context
					$jetengine_relation_context = $jetengine_relation->{'action_user_jetengine_relation_context'};
					if(empty($jetengine_relation_context)) { continue; }
					if(!in_array($jetengine_relation_context, array('child', 'parent'))) { continue; }

					// Get JetEngine relation field ID
					$field_id = $jetengine_relation->{'ws_form_field'};

					// Get relation value
					switch($jetengine_relation_context) {

						case 'parent' :

							$jetengine_field_values = $relation_instance->get_parents($user_id, 'ids');
							break;

						case 'child' :

							$jetengine_field_values = $relation_instance->get_children($user_id, 'ids');
							break;
					}

					if(count($jetengine_field_values) == 0) { continue; }

					$return_array['fields'][$field_id] = $jetengine_field_values;
				}
			}

			return $return_array;
		}

		// Logo
		public function hook_svg_custom_field_logos($svg_custom_field_logos, $list_id) {

			if(WS_Form_JetEngine::jetengine_get_fields_all('user', null, false, true, false, true)) {

				$svg_custom_field_logos[] = '<path fill="#9D64ED" d="M1.7 0h18.6c.9 0 1.7.8 1.7 1.7v18.6c0 .9-.8 1.7-1.7 1.7H1.7C.8 22 0 21.2 0 20.3V1.7C.1.7.8 0 1.7 0z"/><path fill="#FFFFFF" d="M19.5 4.6c.3 0 .4.3.2.5L18.2 7c-.2.2-.5.1-.5-.2V6c0-.1 0-.2-.1-.3l-.6-.5c-.2-.2-.1-.6.2-.6h2.3zM7.4 9.5c0 1.8-1.5 3.3-3.3 3.3-.5 0-.8-.4-.8-.8 0-.5.4-.8.8-.8.9 0 1.7-.7 1.7-1.6V7.1c0-.5.4-.8.8-.8.5 0 .8.4.8.8v2.4zm9 0c0 .9.7 1.6 1.7 1.6.5 0 .8.4.8.8s-.4.8-.8.8c-1.8 0-3.3-1.5-3.3-3.3V6.9c0-.5.4-.8.8-.8s.8.4.8.8v.7h.7c.5 0 .8.4.8.8 0 .5-.4.8-.8.8l-.7.3zm-2.2-1.1c-.3-.7-.7-1.3-1.4-1.7-1.6-.9-3.6-.4-4.5 1.2s-.4 3.6 1.2 4.5c1.2.7 2.6.6 3.6-.2.2-.1.4-.4.4-.7 0-.5-.4-.8-.8-.8-.2 0-.4.1-.6.2-.5.3-1.2.4-1.7.1l3.3-1.5c.2-.1.4-.2.5-.4.1-.2.1-.5 0-.7zM12 8.1c.1.1.2.1.3.2L9.6 9.6c0-.3.1-.6.2-.9.4-.8 1.4-1 2.2-.6zm.3 5.9v-.2h-.2v.2h.2c-.1 0-.1 0 0 0zm-.2.4v2h.2v-2c-.1-.1-.1-.1-.2 0 0-.1 0-.1 0 0zm-5.2.4c.1.1.2.3.2.4H5.5c0-.2.1-.3.2-.4.2-.2.3-.2.6-.2.2 0 .4 0 .6.2zM7 16c0-.1-.1-.1 0 0h-.2c-.1.1-.1.1-.2.1h-.3c-.2 0-.4-.1-.6-.2-.1-.1-.2-.3-.2-.4h1.8v-.1c0-.3-.1-.5-.3-.7-.2-.2-.4-.3-.7-.3-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7 0 .3.1.5.3.7.2.2.4.3.7.3.3 0 .5-.1.7-.2V16zm.6-1.6s-.1 0 0 0v2h.2v-1.1c0-.2.1-.4.2-.5s.3-.2.5-.2.4.1.5.2.2.3.2.5v1.1h.2v-1.1c0-.3-.1-.5-.3-.7-.2-.2-.4-.3-.7-.3-.3 0-.5.1-.7.3l-.1-.2c.1-.1.1-.1 0 0 0-.1 0-.1 0 0zm3.1.2c-.2 0-.4.1-.6.2-.2.2-.2.3-.2.6 0 .2.1.4.2.6s.3.2.6.2c.2 0 .4-.1.6-.2.2-.2.2-.3.2-.6 0-.2-.1-.4-.2-.6-.2-.2-.4-.2-.6-.2zm.7 1.4c-.2.2-.5.4-.8.4s-.5-.1-.7-.3c-.2-.2-.3-.4-.3-.7 0-.3.1-.5.3-.7.2-.2.4-.3.7-.3.3 0 .5.1.7.3.2.2.3.4.3.7v1c0 .2-.1.4-.2.5-.2.2-.5.3-.8.3s-.6-.1-.8-.3c-.1-.1-.1-.2-.2-.2v-.2h.2c0 .1.1.1.2.2s.2.1.3.2c.1 0 .2.1.3.1.2 0 .4-.1.6-.3.1-.1.2-.3.2-.4V16zm1.3-1.6c.1-.1.1-.1 0 0l.2-.1V14.6c.2-.2.5-.3.7-.3.3 0 .5.1.7.3.2.2.3.4.3.7v1.1h-.2v-1.1c0-.2-.1-.4-.2-.5s-.3-.2-.5-.2-.4.1-.5.2-.2.3-.2.5v1.1h-.2v-2h-.1zm3.7.4c.1.1.2.3.2.4H15c0-.2.1-.3.2-.4.2-.2.3-.2.6-.2s.4 0 .6.2zm.1 1.2c-.1-.1-.1-.1 0 0h-.2c-.1.1-.1.1-.2.1h-.3c-.2 0-.4-.1-.6-.2-.1-.1-.2-.3-.2-.4h1.8v-.1c0-.3-.1-.5-.3-.7-.2-.2-.4-.3-.7-.3-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7 0 .3.1.5.3.7.2.2.4.3.7.3.3 0 .5-.1.7-.2V16z"/>';
			}

			return $svg_custom_field_logos;
		}
	}

	new WS_Form_Action_User_JetEngine();

