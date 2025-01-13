<?php

	class WS_Form_JetEngine {

		public static $jetengine_fields = array();

		// Get fields all
		public static function jetengine_get_fields_all($context = 'post_type', $object = 'post', $choices_filter = false, $raw = false, $traverse = false, $has_fields = false) {

			// JetEngine fields
			$options_jetengine = array();

			$fields_found = false;

			// If retrieving user context then object should be null
			if($context == 'user') { $object = null; }

			// Get JetEngine groups
			if($context !== false) {

				$jetengine_groups = jet_engine()->meta_boxes->get_fields_for_context($context, $object);

			} else {

				$jetengine_groups = array_merge(

					jet_engine()->meta_boxes->get_fields_for_context('post_type', null),
					jet_engine()->meta_boxes->get_fields_for_context('user', null)
				);
			}

			if(!empty($object)) {

				$jetengine_groups = array($object => $jetengine_groups);
			}

			if($context !== 'user') {

				$post_types = get_post_types([], 'objects');
			}

			foreach($jetengine_groups as $object => $jetengine_fields) {

				// Has fields?
				if($has_fields && (count($jetengine_fields) > 0)) { return true; }

				// Group name
				switch($context) {

					case 'user' :

						$jetengine_field_group_name = __('User', 'ws-form');;
						break;

					default :

						$jetengine_field_group_name = isset($post_types[$object]) ? $post_types[$object]->labels->singular_name : __('Unknown', 'ws-form');
				}

				// Process fields
				WS_Form_JetEngine::jetengine_get_fields_process($options_jetengine, $jetengine_field_group_name, $jetengine_fields, $choices_filter, $raw, $traverse, $context);
			}

			return $has_fields ? $fields_found : $options_jetengine;
		}

		// Get fields
		public static function jetengine_get_fields_process(&$options_jetengine, $jetengine_field_group_name, $jetengine_fields, $choices_filter, $raw, $traverse, $context = '', $depth = 0, $parent_field_name = '') {

			foreach($jetengine_fields as $jetengine_field) {

				if(
					!isset($jetengine_field['name']) ||
					!isset($jetengine_field['type'])
				) {
					continue;
				}

				// Get field name
				$jetengine_field_name = $jetengine_field['name'];

				// Get field type
				$jetengine_field_type = $jetengine_field['type'];

				// Fields to filter (User default fields which are added by JetEngine)
				if($context == 'user') {

					switch($jetengine_field_name) {

						case 'first_name' :
						case 'last_name' :
						case 'description' :

							continue 2;
					}
				}

				// Adjust label if blank
				if($jetengine_field['title'] == '') {

					$jetengine_field['title'] = $jetengine_field_name;
				}

				// Add parent
				$jetengine_field['parent'] = $parent_field_name;

				// Only return fields that have options
				$process_field = true;
				if(
					$choices_filter &&
					!(
						// Fields with options
						(
							isset($jetengine_field['options']) &&
							is_array($jetengine_field['options']) &&
							(count($jetengine_field['options']) > 0)
						)

						||

						// Fields with options from a glossary
						(
							isset($jetengine_field['options_from_glossary']) &&
							!empty($jetengine_field['options_from_glossary'])
						)

						||

						// Fields with options from a query
						(
							isset($jetengine_field['options_source']) &&
							is_string($jetengine_field['options_source']) &&
							($jetengine_field['options_source'] === 'query') &&
							isset($jetengine_field['query_id'])
						)
					)
				) {
					$process_field = false;
				}

				if($process_field) {

					if($raw) {

						$options_jetengine[$jetengine_field_name] = $jetengine_field;

					} else {

						// Get field object type
						$jetengine_object_type = (($depth === 0) && isset($jetengine_field['object_type'])) ? $jetengine_field['object_type'] : 'field';

						// Check if mappable
						if(
							self::jetengine_field_mappable($jetengine_field_type) &&
							($jetengine_object_type == 'field')
						) {

							$options_jetengine[] = array('value' => $jetengine_field_name, 'text' => sprintf('%s - %s', $jetengine_field_group_name, $jetengine_field['title']));
						}
					}
				}

				// Check for sub fields
				if($traverse) {

					if(
						isset($jetengine_field['repeater-fields']) &&
						is_array($jetengine_field['repeater-fields']) &&
						(count($jetengine_field['repeater-fields']) > 0)
					) {
						self::jetengine_get_fields_process($options_jetengine, $jetengine_field_group_name, $jetengine_field['repeater-fields'], $choices_filter, $raw, $traverse, $context . ' - ' . $jetengine_field['title'], $depth + 1, $jetengine_field_name);
					}
				}
			}
		}

		// Get field
		public static function jetengine_get_field_settings($jetengine_field_name, $context = 'post_type') {

			// Get JetEngine fields
			if(!isset(self::$jetengine_fields[$context])) {

				// Retrieve fields
				self::$jetengine_fields[$context] = self::jetengine_get_fields_all($context, null, false, true, true);
			}

			// Check if field ID exists
			if(!isset(self::$jetengine_fields[$context][$jetengine_field_name])) { return false; }

			return self::$jetengine_fields[$context][$jetengine_field_name];
		}

		// Get field data
		public static function jetengine_get_field_data($context = 'post_type', $object = 'post', $object_id = 0) {

			$fields = self::jetengine_get_fields_all($context, $object, false, true);
			if($fields === false) { return array(); }

			$return_array = array();

			foreach($fields as $field) {

				$field_name = $field['name'];
				$field_type = $field['type'];

				$return_single = array();

				switch($context) {

					case 'post_type' :

						$field_value = get_post_meta($object_id, $field_name, true);
						break;

					case 'user' :

						$field_value = get_user_meta($object_id, $field_name, true);
						break;
				}

				if(
					($field_type == 'repeater') &&
					is_array($field_value)
				) {
					$fields_repeater = $field['repeater-fields'];

					foreach($fields_repeater as $field_repeater) {

						$field_repeater_name = $field_repeater['name'];
						$field_repeater_type = $field_repeater['type'];

						$field_repeater_values = array();

						foreach($field_value as $item => $field_repeater_fields) {

							$field_repeater_values[] = isset($field_repeater_fields[$field_repeater_name]) ? $field_repeater_fields[$field_repeater_name] : '';
						}

						$return_array[$field_repeater_name] = array(

							'repeater' => true,
							'type' => $field_repeater_type,
							'values' => $field_repeater_values
						);
					}

				}  else {

					$return_array[$field_name] = array(

						'repeater' => false,
						'type' => $field_type,
						'values' => $field_value
					);
				}
			}

			return $return_array;
		}

		// Process JetEngine fields
		public static function jetengine_fields_to_list_fields($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();

			$wsf_group_name_last = false;

			// Get sort index
			$sort_index = $field_index;

			foreach($fields as $field) {

				switch($field['type']) {

					case 'repeater' :

						if(!isset($field['repeater-fields'])) { continue 2; }

						$jetengine_fields_to_list_fields_return = self::jetengine_fields_to_list_fields($field['repeater-fields'], $group_index, $section_index + 1, 1, $depth + 1);
						if(count($jetengine_fields_to_list_fields_return['list_fields']) == 0) { continue 2; }

						$section_index++;
						$field_index = 1;

						$list_fields = array_merge($list_fields, $jetengine_fields_to_list_fields_return['list_fields']);

						$section_index++;
						$field_index = 1;

						continue 2;
				}

				// Get field object type
				$object_type = (($depth === 0) && isset($field['object_type'])) ? $field['object_type'] : 'field';

				// Process by object type
				switch($object_type) {

					case 'field' :

						$type = self::jetengine_action_field_type_to_ws_form_field_type($field);

						if($type === false) { continue 2; }

						break;

					 case 'accordion' :

						$section_index++;
						$field_index = 1;

					 	continue 2;

					case 'tab' :

						$group_index++;
						$section_index = 0;
						$field_index = 1;

						continue 2;

					case 'endpoint' :

						continue 2;
				}

				// Get meta
				$meta = self::jetengine_action_field_to_ws_form_meta_keys($field);

				// Adjust label if blank
				if($field['title'] == '') {

					$field['title'] = __('(no label)', 'ws-form');
					$meta['label_render'] = '';
				}

				$list_fields_single = array(

					'id' => 				$field['name'],
					'label' => 				$field['title'], 
					'label_field' => 		$field['title'], 
					'type' => 				$type,
					'action_type' =>		$field['type'],
					'required' => 			(isset($field['is_required']) ? ($field['is_required'] == 1) : false),
					'default_value' => 		(isset($field['default_val']) ? $field['default_val'] : ''),
					'pattern' => 			'',
					'placeholder' => 		'',
					'group_index' =>		$group_index,
					'section_index' => 		$section_index,
					'sort_index' => 		$sort_index++,
					'visible' =>			true,
					'meta' => 				$meta,
					'no_map' =>				true
				);

				// Help
				if(isset($field['description'])) {

					$list_fields_single['help'] = $field['description'];
				}

				// Width
				if(isset($field['width'])) {

					$wrapper_width = floatval(str_replace('%', '', $field['width']));

					if(
						($wrapper_width > 0) &&
						($wrapper_width <= 100)
					) {

						$list_fields_single['width_factor'] = ($wrapper_width / 100);
					}
				}

				$list_fields[] = $list_fields_single;
			}

			return array('list_fields' => $list_fields, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Convert action field to WS Form meta key
		public static function jetengine_action_field_to_ws_form_meta_keys($field) {

			$meta_return = array();

			$type = $field['type'];
			$default_value = isset($field['default_val']) ? $field['default_val'] : '';

			// Max length
			if(
				isset($field['max_length']) &&
				($field['max_length'] != '')
			) {

				$meta_return['max_length'] = absint($field['max_length']);
			}

			// Get WS Form meta configurations for action field types
			switch($type) {

				// Build data grids for radio and select
				case 'select' :
				case 'checkbox' :
				case 'radio' :
				case 'switcher' :
				case 'posts' :

					switch($type) {

						case 'posts' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;
							$meta_return['select2'] = 'on';
							$meta_return['select2_ajax'] = 'on';

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('post', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'post';

							// Multiple
							if(isset($field['is_multiple']) && ($field['is_multiple'] == 1)) {

								$meta_return['multiple'] = 'on';
								$meta_return['placeholder_row'] = '';
							}

							// Post types
							$post_types = isset($field['search_post_type']) ? $field['search_post_type'] : array();
							if(!is_array($post_types)) { $post_types = array(); }
							$meta_return['data_source_post_filter_post_types'] = array();
							foreach($post_types as $post_type) {

								$meta_return['data_source_post_filter_post_types'][] = array(

									'data_source_post_post_types' => $post_type
								);
							}

							$choices = array();

							break;

						case 'switcher' :

							$meta_key = 'data_grid_checkbox';
							$meta_return['checkbox_field_label'] = 1;
							$meta_return['class_field'] = 'wsf-switch';

							$choices = array('true' => $field['title']);

							break;

						case 'select' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('jetengine', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'jetengine';
							$meta_return['data_source_jetengine_field_name'] = $field['name'];

							// Multiple
							if(
								isset($field['is_multiple']) &&
								($field['is_multiple'] == 1)
							) {

								$meta_return['multiple'] = 'on';
								$meta_return['placeholder_row'] = '';

							}

							$choices = array();

							break;

						case 'checkbox' :

							$meta_key = 'data_grid_checkbox';
							$meta_return['checkbox_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('jetengine', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'jetengine';
							$meta_return['data_source_jetengine_field_name'] = $field['name'];

							$choices = array();

							break;

						case 'radio' :

							$meta_key = 'data_grid_radio';
							$meta_return['radio_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('jetengine', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'jetengine';
							$meta_return['data_source_jetengine_field_name'] = $field['name'];

							$choices = array();

							break;
					}

					// Get base meta
					$meta_keys = WS_Form_Config::get_meta_keys();
					if(!isset($meta_keys[$meta_key])) { return false; }
					if(!isset($meta_keys[$meta_key]['default'])) { return false; }

					$meta = $meta_keys[$meta_key]['default'];

					// Configure columns
					$meta['columns'] = array(

						array('id' => 0, 'label' => __('Value', 'ws-form')),
						array('id' => 1, 'label' => __('Label', 'ws-form'))
					);

					// Build new rows
					$rows = array();
					$id = 1;

					foreach($choices as $value => $text) {

						$rows[] = array(

							'id'		=> $id,
							'data'		=> array($value, $text)
						);

						$id++;
					}

					// Modify meta
					$meta['groups'][0]['rows'] = $rows;

					$meta_return[$meta_key] = $meta;

					return $meta_return;

				case 'number' :

					if(
						isset($field['min_value']) &&
						($field['min_value'] != '')
					) {

						$meta_return['min'] = absint($field['min_value']);
					}

					if(
						isset($field['max_value']) &&
						($field['max_value'] != '')
					) {

						$meta_return['max'] = absint($field['max_value']);
					}

					if(
						isset($field['step_value']) &&
						($field['step_value'] != '')
					) {

						$meta_return['step'] = absint($field['step_value']);
					}

					return $meta_return;

				case 'date' :

					$meta_return['input_type_datetime'] = 'date';
					return $meta_return;

				case 'datetime-local' :

					$meta_return['input_type_datetime'] = 'datetime-local';
					return $meta_return;

				case 'time' :

					$meta_return['input_type_datetime'] = 'time';
					return $meta_return;

				case 'wysiwyg' :

					global $wp_version;
					if(WS_Form_Common::version_compare($wp_version, '4.8') >= 0) {
						$meta_return['input_type_textarea'] = 'tinymce';
					}
					return $meta_return;

				case 'html' :

					$meta_return['html_editor'] = isset($field['html']) ? $field['html'] : '';
					return $meta_return;

				case 'media' :
				case 'gallery' :

					// File handler
					$meta_return['file_handler'] = 'attachment';
					$meta_return['sub_type'] = 'dropzonejs';

					switch($type) {

						case 'gallery' :

							$meta_return['multiple_file'] = 'on';
							break;
					}

					return $meta_return;

				default :

					return false;
			}
		}

		// Process JetEngine fields
		public static function jetengine_fields_to_meta_data($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();
			$group_meta_data = array();
			$section_meta_data = array();

			foreach($fields as $field) {

				$type = self::jetengine_action_field_type_to_ws_form_field_type($field);

				// Skip unsupported field types
				if($type === false) { continue; }

				switch($field['type']) {

					case 'repeater' :

						// Repeater
						if(!isset($field['repeater-fields'])) { continue 2; }

						$jetengine_fields_to_meta_data_return = self::jetengine_fields_to_meta_data($field['repeater-fields'], $group_index, $section_index + 1, 1, $depth + 1);
						if(count($jetengine_fields_to_meta_data_return['list_fields']) == 0) { continue 2; }

						$section_index++;
						$field_index = 1;

						if(!isset($section_meta_data['group_' . $group_index])) { $section_meta_data['group_' . $group_index] = array(); }
						if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }

						if(isset($field['width'])) {

							$wrapper_width = floatval(str_replace('%', '', $field['width']));

							if(
								($wrapper_width > 0) &&
								($wrapper_width <= 100)
							) {

								$section_meta_data['group_' . $group_index]['section_' . $section_index]['width_factor'] = ($wrapper_width / 100);
							}		
						}

						$group_meta_data = array_merge($group_meta_data, $jetengine_fields_to_meta_data_return['group_meta_data']);
						$section_meta_data = array_merge($section_meta_data, $jetengine_fields_to_meta_data_return['section_meta_data']);

						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $field['title'];
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label_render'] = 'on';

						$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeatable'] = 'on';

						$section_index++;
						$field_index = 1;

						if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = __('Section', 'ws-form');

						continue 2;
				}

				// Get field object type
				$object_type = (($depth === 0) && isset($field['object_type'])) ? $field['object_type'] : 'field';

				switch($object_type) {

					case 'tab' :

						$group_index++;
						$section_index = 0;
						$field_index = 1;

						if(!isset($group_meta_data['group_' . $group_index])) { $group_meta_data['group_' . $group_index] = array(); }
						$group_meta_data['group_' . $group_index]['label'] = $field['title'];

						if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = __('Section', 'ws-form');

						continue 2;

					case 'accordion' :

						$section_index++;
						$field_index = 1;

						if(!isset($section_meta_data['group_' . $group_index])) { $section_meta_data['group_' . $group_index] = array(); }
						if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $field['title'];
						$section_meta_data['group_' . $group_index]['section_' . $section_index]['label_render'] = 'on';

						continue 2;
				}

				// Dummy entry
				$list_fields[] = array();

				$field_index++;
			}

			return array('list_fields' => $list_fields, 'group_meta_data' => $group_meta_data, 'section_meta_data' => $section_meta_data, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Get parent key data for repeatables. We need this to be able to add the repeatable field meta data.
		public static function jetengine_get_parent_data($jetengine_field_name, $context = 'post_type') {

			$field_settings = self::jetengine_get_field_settings($jetengine_field_name, $context);
			if($field_settings === false) { return false; }

			$parent = isset($field_settings['parent']) ? $field_settings['parent'] : '';

			if(!empty($parent)) {
				
				$field_settings_parent = self::jetengine_get_field_settings($parent, $context);
				if($field_settings_parent === false) { return false; }

				return array(

					'id' => $field_settings_parent['id'],
					'name' => $field_settings_parent['name'],
					'type' => $field_settings_parent['type']
				);
			}

			return false;
		}

		// Process jetengine_field_values as file
		public static function jetengine_field_values_file($jetengine_field_values) {

			$return_array = array();

			// Process attachment IDs
			if(!is_array($jetengine_field_values)) { $jetengine_field_values = array($jetengine_field_values); }

			foreach($jetengine_field_values as $jetengine_field_value_single) {

				$attachment_id = absint($jetengine_field_value_single);
				if(!$attachment_id) { continue; }

				$file_object = WS_Form_File_Handler::get_file_object_from_attachment_id($attachment_id);
				if($file_object === false) { continue; }

				$return_array[] = $file_object;
			}

			return (count($return_array) > 0) ? $return_array : false;
		}

		// Process jetengine_field_values as checkbox
		public static function jetengine_field_values_checkbox($jetengine_field_values, $field_id, $fields, $field_types, $is_array) {

			if(empty($jetengine_field_values)) { return ''; }

			if(!is_array($jetengine_field_values)) { $jetengine_field_values = array($jetengine_field_values); }

			// Support for 'Save as array' JetEngine field setting
			if($is_array) { return $jetengine_field_values; }

			$return_array = array();

			foreach($jetengine_field_values as $key => $value) {

				if($value == 'true') {

					$return_array[] = $key;
				}
			}

			return $return_array;
		}

		// Process jetengine_field_values as date
		public static function jetengine_field_values_date_time($jetengine_field_values, $jetengine_field_type, $field_id, $fields, $is_timestamp) {

			if(
				($jetengine_field_values === '') ||
				(absint($field_id) === 0) ||
				!isset($fields[$field_id])
			) {
				 return '';
			}

			// Get field object
			$field_object = $fields[$field_id];

			// Get formats
			$format_date = WS_Form_Common::get_object_meta_value($field_object, 'format_date', get_option('date_format'));
			if(empty($format_date)) { $format_date = get_option('date_format'); }
			$format_time = WS_Form_Common::get_object_meta_value($field_object, 'format_time', get_option('time_format'));
			if(empty($format_time)) { $format_time = get_option('time_format'); }

			// We'll use UTC so that wp_date doesn't offset the date
			$utc = new DateTimeZone('UTC');

			// Check WordPress version
			$wp_new = WS_Form_Common::wp_version_at_least('5.3');

			// Get time
			$time = $is_timestamp ? $jetengine_field_values : strtotime($jetengine_field_values);

			switch($jetengine_field_type) {

				case 'date' :

					return $wp_new ? wp_date($format_date, $time, $utc) : gmdate($format_date, $time);

				case 'datetime-local' :

					return $wp_new ? wp_date($format_date . ' ' . $format_time, $time, $utc) : gmdate($format_date . ' ' . $format_time, $time);

				case 'time' :

					return $wp_new ? wp_date($format_time, $time, $utc) : gmdate($format_time, $time);
			}

			return '';
		}

		// Get field type
		public static function jetengine_get_field_type($jetengine_field_name, $context = 'post_type') {

			$field_settings = self::jetengine_get_field_settings($jetengine_field_name, $context);
			if($field_settings === false) { return false; }

			return $field_settings['type'];
		}

		// Get file field types
		public static function jetengine_get_field_types_file() {

			return array(

				'media',
				'gallery'
			);
		}

		// Convert JetEngine meta value to WS Form field
		public static function jetengine_jetengine_meta_value_to_ws_form_field_value($jetengine_field_values, $jetengine_field_type, $jetengine_field_repeater, $jetengine_field_name, $field_id, $fields, $field_types, $context = 'post_type') {

			switch($jetengine_field_type) {

				case 'media' :
				case 'gallery' :

					$jetengine_field_settings = WS_Form_JetEngine::jetengine_get_field_settings($jetengine_field_name, $context);
					if($jetengine_field_settings === false) { return array(); }

					$jetengine_value_format = isset($jetengine_field_settings['value_format']) ? $jetengine_field_settings['value_format'] : 'id';

					if($jetengine_field_repeater) {

						// Process repeated attachment IDs
						foreach($jetengine_field_values as $jetengine_field_values_index => $jetengine_field_value) {

							$jetengine_field_values[$jetengine_field_values_index] = self::jetengine_field_values_file(self::jetengine_get_attachment_id($jetengine_field_value, $jetengine_value_format, $jetengine_field_type));
						}

					} else {


						// Process regular attachment IDs
						$jetengine_field_values = self::jetengine_field_values_file(self::jetengine_get_attachment_id($jetengine_field_values, $jetengine_value_format, $jetengine_field_type));
					}

					break;

				case 'date' :
				case 'time' :
				case 'datetime-local' :

					$jetengine_field_settings = WS_Form_JetEngine::jetengine_get_field_settings($jetengine_field_name, $context);
					if($jetengine_field_settings === false) { return array(); }

					// Check if field uses a timestamp
					$is_timestamp = isset($jetengine_field_settings['is_timestamp']) && ($jetengine_field_settings['is_timestamp'] == 1);

					if($jetengine_field_repeater) {

						// Process repeated date
						foreach($jetengine_field_values as $jetengine_field_values_index => $jetengine_field_value) {

							$jetengine_field_values[$jetengine_field_values_index] = self::jetengine_field_values_date_time($jetengine_field_value, $jetengine_field_type, $field_id, $fields, $is_timestamp);
						}

					} else {

						// Process regular date
						$jetengine_field_values = self::jetengine_field_values_date_time($jetengine_field_values, $jetengine_field_type, $field_id, $fields, $is_timestamp);
					}

					break;

				case 'checkbox' :

					$jetengine_field_settings = WS_Form_JetEngine::jetengine_get_field_settings($jetengine_field_name, $context);
					if($jetengine_field_settings === false) { return array(); }

					// Check if field uses an array
					$is_array = isset($jetengine_field_settings['is_array']) && ($jetengine_field_settings['is_array'] == 1);

					if($jetengine_field_repeater) {

						// Process repeated checkbox
						foreach($jetengine_field_values as $jetengine_field_values_index => $jetengine_field_value) {

							$jetengine_field_values[$jetengine_field_values_index] = self::jetengine_field_values_checkbox($jetengine_field_value, $jetengine_field_type, $field_id, $fields, $is_array);
						}

					} else {

						// Process regular checkbox
						$jetengine_field_values = self::jetengine_field_values_checkbox($jetengine_field_values, $jetengine_field_type, $field_id, $fields, $is_array);
					}

					break;
			}

			return $jetengine_field_values;
		}

		// Get attachment ID from JetEngine field value and value format
		public static function jetengine_get_attachment_id($jetengine_field_value, $jetengine_value_format, $jetengine_field_type) {

			if(empty($jetengine_field_value)) { return array(); }

			$return_array = array();

			switch($jetengine_value_format) {

				case 'id' :
				case 'url' :

					if(
						!is_string($jetengine_field_value) &&
						!is_numeric($jetengine_field_value)
					) {
						break;
					}

					foreach(explode(',', $jetengine_field_value) as $attachment_value) {

						switch($jetengine_value_format) {

							case 'id' :

								$attachment_id = absint($attachment_value);
								break;

							case 'url' :

								$attachment_id = absint(attachment_url_to_postid($attachment_value));
								break;
						}

						if($attachment_id > 0) {

							$return_array[] = $attachment_id;
						}
					}

					break;

				case 'both' :

					if(!is_array($jetengine_field_value)) {

						break;
					}

					// JetEngine does not use an array of arrays for user meta data
					if(isset($jetengine_field_value['id'])) {

						// Single array
						$attachment_id = absint($jetengine_field_value['id']);

						if($attachment_id > 0) {

							$return_array[] = $attachment_id;
						}

					} else {

						// Array of arrays
						foreach($jetengine_field_value as $attachment_value) {

							if(
								!is_array($attachment_value) ||
								!isset($attachment_value['id'])
							) {
								continue;
							}

							$attachment_id = absint($attachment_value['id']);

							if($attachment_id > 0) {

								$return_array[] = $attachment_id;
							}
						}
					}

					break;
			}

			return $return_array;
		}

		// Convert WS Form field value to JetEngine meta value
		public static function jetengine_ws_form_field_value_to_jetengine_meta_value($meta_value, $jetengine_field_type, $jetengine_field_name = false, $field_id = false, $fields = false, $field_types = false, $context = 'post_type') {

			switch($jetengine_field_type) {

				case 'date' :
				case 'time' :
				case 'datetime-local' :

					if($meta_value == '') { return ''; }

					$jetengine_field_settings = self::jetengine_get_field_settings($jetengine_field_name, $context);
					if($jetengine_field_settings === false) { return ''; }

					$is_timestamp = isset($jetengine_field_settings['is_timestamp']) && ($jetengine_field_settings['is_timestamp'] == 1);

					if($field_id !== false) {

						if(!isset($fields[$field_id])) {

							return '';
						}

						// Get field object
						$field_object = $fields[$field_id];

						switch($jetengine_field_type) {

							case 'date' :

								$return_date = WS_Form_Common::get_date_by_type($meta_value, $field_object, 'Y-m-d');

								return $is_timestamp ? strtotime($return_date) : $return_date;

							case 'time' :

								return WS_Form_Common::get_date_by_type($meta_value, $field_object, 'H:i');

							case 'datetime-local' :

								$return_date = sprintf(

									'%sT%s',
									WS_Form_Common::get_date_by_type($meta_value, $field_object, 'Y-m-d'),
									WS_Form_Common::get_date_by_type($meta_value, $field_object, 'H:i')
								);

								return $is_timestamp ? strtotime($return_date) : $return_date;
						}
					}

					break;

				case 'switcher' :

					return empty($meta_value) ? 'false' : 'true';

				case 'select' :
				case 'posts' :

					$jetengine_field_settings = self::jetengine_get_field_settings($jetengine_field_name, $context);

					if($jetengine_field_settings === false) {

						return '';
					}

					$multiple = isset($jetengine_field_settings['is_multiple']) ? $jetengine_field_settings['is_multiple'] : false;

					if($multiple) {

						return $meta_value;

					} else {

						return is_array($meta_value) ? $meta_value[0] : '';
					}

				case 'checkbox' :

					if(
						empty($meta_value) ||
						!is_array($meta_value)
					) {
						return $meta_value;
					}

					// Get field settings
					$jetengine_field_settings = self::jetengine_get_field_settings($jetengine_field_name, $context);

					if(
						($jetengine_field_settings === false) ||
						!isset($jetengine_field_settings['options']) ||
						!is_array($jetengine_field_settings['options'])
					) {
						return '';
					}

					// Check if field uses an array
					$is_array = isset($jetengine_field_settings['is_array']) && ($jetengine_field_settings['is_array'] == 1);

					// Support for 'Save as array' JetEngine field setting
					if($is_array) { return $meta_value; }

					$meta_value_new = array();

					// Get field options
					$options = $jetengine_field_settings['options'];

					// Process each option
					foreach($options as $option) {

						$option_key = $option['key'];

						$meta_value_new[$option_key] = (in_array($option_key, $meta_value) ? 'true' : 'false');
					}

					return $meta_value_new;

				case 'radio' :

					return is_array($meta_value) ? $meta_value[0] : '';

				case 'number' :

					if(
						($meta_value != '') &&
						!is_numeric($meta_value)
					) {
						$meta_value = WS_Form_Common::get_number($meta_value, 0, true);
					}

					break;

				// String based fields
				case 'colorpicker' :
				case 'text' :
				case 'textarea' :

					$meta_value = WS_Form_Common::get_string($meta_value);

					break;
			}

			return $meta_value;
		}

		// Convert action field type to WS Form field type
		public static function jetengine_action_field_type_to_ws_form_field_type($field) {

			$type = $field['type'];

			switch($type) {

				case 'checkbox' : return 'checkbox';
				case 'colorpicker' : return 'color';
				case 'date' : return 'datetime';
				case 'datetime-local' : return 'datetime';
				case 'gallery' : return 'file';
				case 'html' : return 'html';
				case 'iconpicker' : return false;
				case 'media' : return 'file';
				case 'number' : return 'number';
				case 'posts' : return 'select';
				case 'radio' : return 'radio';
				case 'repeater' : return 'repeater';
				case 'select' : return 'select';
				case 'switcher' : return 'checkbox';
				case 'text' : return 'text';
				case 'textarea' : return 'textarea';
				case 'time' : return 'datetime';
				case 'wysiwyg' : return 'textarea';
			}

			return false;
		}

		// Fields that we can push data to
		public static function jetengine_field_mappable($jetengine_field_type) {

			switch($jetengine_field_type) {

				case 'text' :
				case 'date' :
				case 'time' :
				case 'datetime-local' :
				case 'textarea' :
				case 'wysiwyg' :
				case 'switcher' :
				case 'checkbox' :
				case 'media' :
				case 'gallery' :
				case 'radio' :
				case 'select' :
				case 'number' :
				case 'colorpicker' :
				case 'posts' :
				case 'number' :

					return true;

				default :

					return false;
			}
		}
	}