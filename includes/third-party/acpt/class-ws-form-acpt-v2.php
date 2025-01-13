<?php

	class WS_Form_ACPT {

		public static $acpt_fields = array();
		public static $acpt_field_advanced_options = array();

		// Get fields all - V1
		public static function acpt_get_fields_all($context = 'post', $choices_filter = false, $raw = false, $traverse = false, $has_fields = false) {

			// ACPT fields
			$options_acpt = array();

			$fields_found = false;

			// Get ACPT field groups
			switch($context) {

				case false :

					$acpt_field_groups = array_merge(array(), self::acpt_get_post_field_groups());
					break;

				case 'user' :

					$acpt_field_groups = self::acpt_get_user_field_groups();
					break;

				case 'posts' :

					$acpt_field_groups = self::acpt_get_post_field_groups();
					break;

				default :

					$acpt_field_groups = self::acpt_get_post_field_groups($context);
			}

			// Process each ACPT field group
			foreach($acpt_field_groups as $acpt_field_group) {

				// Get fields
				if(!property_exists($acpt_field_group, 'fields') || !is_array($acpt_field_group->fields)) { continue; }
				$acpt_fields = $acpt_field_group->fields;

				// Has fields?
				if($has_fields && (count($acpt_fields) > 0)) { $fields_found = true; break; }

				// Process fields
				WS_Form_ACPT::acpt_get_fields_process($options_acpt, $acpt_field_group, $acpt_fields, $choices_filter, $raw, $traverse);
			}

			return $has_fields ? $fields_found : $options_acpt;
		}

		// Get user field groups
		public static function acpt_get_user_field_groups() {

			$acpt_field_groups = array();

			$meta_groups = ACPT\Core\Repository\MetaRepository::get(array(

				'belongsTo' => ACPT\Constants\MetaTypes::USER
			));

			// Process each meta group
			foreach($meta_groups as $meta_group) {

				// Get meta boxes associated with post type
				foreach($meta_group->getBoxes() as $meta_box_model) {

					// Add to groups
					$acpt_field_groups[] = $meta_box_model->toStdObject();
				}
			}

			return $acpt_field_groups;
		}

		// Get post field groups
		public static function acpt_get_post_field_groups($context = false) {

			$acpt_field_groups = array();

			$meta = ($context === false) ? array() : array('postType' => $context);

			$repos = ACPT\Core\Repository\CustomPostTypeRepository::get($meta);

			// Get repos
			foreach($repos as $repo) {

				// Get post type
				$post_type = $repo->getName();

				// Get post type label
				$singular = $repo->getSingular();
				if($singular == '') { continue; }

				// Get meta groups
				$meta_groups = ACPT\Core\Repository\MetaRepository::get(array(

			        'belongsTo' => ACPT\Constants\MetaTypes::CUSTOM_POST_TYPE,
			        'find' => $post_type
		        ));

				// Process each meta group
				foreach($meta_groups as $meta_group) {

					// Get meta boxes associated with post type
					foreach($meta_group->getBoxes() as $meta_box_model) {

						// Add to groups
						$acpt_field_groups[] = $meta_box_model->toStdObject();
					}
				}
			}

			return $acpt_field_groups;
		}

		// Get fields
		public static function acpt_get_fields_process(&$options_acpt, $acpt_field_group, $acpt_fields, $choices_filter, $raw, $traverse, $prefix = '') {

			foreach($acpt_fields as $acpt_field) {

				// Get field type
				$acpt_field_type = $acpt_field->type;

				// Store group name
				$acpt_field->wsf_box_name = $acpt_field_group->label;

				// Only return fields that have choices
				$process_field = true;
				if(
					$choices_filter &&
					(
						!isset($acpt_field->options) ||
						!is_array($acpt_field->options) ||
						(count($acpt_field->options) == 0)
					)
				) {
					$process_field = false;
				}

				if($process_field) {

					if($raw) {

						$options_acpt[$acpt_field->id] = $acpt_field;

					} else {

						// Check if mappable
						if(self::acpt_field_mappable($acpt_field_type)) {

							$options_acpt[] = array('value' => $acpt_field->id, 'text' => sprintf('%s%s - %s', $acpt_field_group->label, $prefix, $acpt_field->label));
						}
					}
				}

				// Check for sub fields
				if($traverse) {

					if(
						isset($acpt_field->children) &&
						is_array($acpt_field->children) &&
						(count($acpt_field->children) > 0)
					) {
						self::acpt_get_fields_process($options_acpt, $acpt_field_group, $acpt_field->children, $choices_filter, $raw, $traverse, $prefix . ' - ' . $acpt_field->label);
					}
				}
			}
		}

		// Get field data
		public static function acpt_get_field_data($context = 'post', $object_id = false) {

			$field_objects = self::acpt_get_fields_all($context, false, true, false, false);
			if($field_objects === false) { return array(); }

			$return_array = array();

			foreach($field_objects as $field_object) {

				// Get field ID
				$field_id = $field_object->id;

				// Get field name
				$field_name = $field_object->name;

				// Get box name
				$field_box_name = $field_object->boxName;

				// Build args
				$args = array(

					'box_name' => $field_box_name,
					'field_name' => $field_name
				);

				// Add context args
				switch($context) {

					case 'user' :

						$args['user_id'] = $object_id;
						break;

					default :

						$args['post_id'] = $object_id;
				}

				// Get values
				$values = get_acpt_field($args);

				// Check for rows
				if(acpt_field_has_rows($args)) {

					if(!is_array($values)) { continue; }

					// Build field name for field ID lookup
					if(!property_exists($field_object, 'children')) { continue; }

					$field_name_id_lookup = array();

					foreach($field_object->children as $field_object_child) {

						if(
							!property_exists($field_object_child, 'name') ||
							!property_exists($field_object_child, 'id')
						) {
							continue;
						}

						$field_name_id_lookup[$field_object_child->name] = $field_object_child->id;
					}

					// Process repeater values
					foreach($values as $repeater_index => $fields) {

						if(!is_array($fields)) { continue; }

						foreach($fields as $field_name => $field_value) {

							// Get field ID
							if(!isset($field_name_id_lookup[$field_name])) { continue; }

							$field_id = $field_name_id_lookup[$field_name];

							if(!isset($return_array[$field_id])) {

								$return_array[$field_id] = array('repeater' => true, 'values' => array());
							}

							// Set field value
							$return_array[$field_id]['values'][$repeater_index] = $field_value;
						}
					}

				} else {

					// Set field value
					$return_array[$field_id] = array('repeater' => false, 'values' => $values);
				}
			}

			return $return_array;
		}

		// Process ACPT fields
		public static function acpt_fields_to_list_fields($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();

			$box_name_last = false;

			foreach($fields as $field) {

				// Get field type
				$action_type = $field->type;
				$type = self::acpt_action_field_type_to_ws_form_field_type($field);
				if($type === false) { continue; }

				// Get meta
				$meta = self::acpt_action_field_to_ws_form_meta_keys($field);

				// Adjust label if blank
				if($field->label == '') {

					$field->label = __('(no label)', 'acpt');
					$meta['label_render'] = '';
				}

				// Section names
				$box_name = property_exists($field, 'wsf_box_name') ? $field->wsf_box_name : false;

				if(
					($depth === 0) &&
					($box_name !== false) &&
					($box_name !== $box_name_last)
				) {

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					$box_name_last = $box_name;
				}

				// Groups
				switch($action_type) {

					case 'Repeater' :

						if(property_exists($field, 'children')) {

							$acpt_fields_to_list_fields_return = self::acpt_fields_to_list_fields($field->children, $group_index, $section_index + 1, 1, $depth + 1);
							if(count($acpt_fields_to_list_fields_return['list_fields']) > 0) {

								$section_index++;
								$field_index = 1;

								$list_fields = array_merge($list_fields, $acpt_fields_to_list_fields_return['list_fields']);

								$section_index++;
								$field_index = 1;
							}
						}

						continue 2;
				}

				$list_fields_single = array(

					'id' => 				$field->id,
					'label' => 				$field->label, 
					'label_field' => 		$field->label, 
					'type' => 				$type,
					'action_type' =>		$action_type,
					'required' => 			(property_exists($field, 'isRequired') ? ($field->isRequired == 1) : false),
					'default_value' => 		(property_exists($field, 'defaultValue') ? $field->defaultValue : ''),
					'pattern' => 			(property_exists($field, 'pattern') ? $field->pattern : ''),
					'placeholder' => 		'',
					'group_index' =>		$group_index,
					'section_index' => 		$section_index,
					'sort_index' => 		$field_index++,
					'visible' =>			true,
					'meta' => 				$meta,
					'no_map' =>				true
				);

				// Help
				if(
					property_exists($field, 'description') &&
					!empty($field->description)
				) {

					$list_fields_single['help'] = $field->description;
				}

				// Width
				$wrapper_width = floatval(self::acpt_get_advanced_option($field, 'width'));

				if(
					($wrapper_width > 0) &&
					($wrapper_width <= 100)
				) {

					$list_fields_single['width_factor'] = ($wrapper_width / 100);
				}

				$list_fields[] = $list_fields_single;
			}

			return array('list_fields' => $list_fields, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Get advanced option
		public static function acpt_get_advanced_option($field, $key, $default_value = '') {

			if(
				!is_object($field) ||
				!property_exists($field, 'id')
			) {
				return $default_value;
			}

			$field_id = $field->id;

			if(!isset(self::$acpt_field_advanced_options[$field_id])) {

				self::$acpt_field_advanced_options[$field_id] = array();

				// Process advanced options
				if(property_exists($field, 'advancedOptions')) {

					foreach((array) $field->advancedOptions as $advanced_option) {

						if(
							!is_object($advanced_option) ||
							!property_exists($advanced_option, 'key') ||
							!property_exists($advanced_option, 'value')
						) {
							continue;
						}

						self::$acpt_field_advanced_options[$field_id][$advanced_option->key] = $advanced_option->value;
					}
				}
			}

			if(isset(self::$acpt_field_advanced_options[$field_id][$key])) {

				return self::$acpt_field_advanced_options[$field_id][$key];

			} else {

				return $default_value;
			}
		}

		// Convert action field to WS Form meta key
		public static function acpt_action_field_to_ws_form_meta_keys($field) {

			$meta_return = array();

			$type = $field->type;

			// Get default value
			$default_value = property_exists($field, 'defaultValue') ? $field->defaultValue : '';

			// ACPT advanced option - Headline
			$headline = self::acpt_get_advanced_option($field, 'headline');

			if(!empty($headline)) {

				switch($headline) {

					case 'top' : $meta_return['label_position'] = 'top'; break;
					case 'left' : $meta_return['label_position'] = 'left'; break;
					case 'right' : $meta_return['label_position'] = 'right'; break;
					case 'none' : $meta_return['label_render'] = ''; break;
				}
			}

			// ACPT advanced option - Min
			$min = self::acpt_get_advanced_option($field, 'min');

			// ACPT advanced option - Max
			$max = self::acpt_get_advanced_option($field, 'max');

			// ACPT advanced option - Step
			$step = self::acpt_get_advanced_option($field, 'step');

			// ACPT advanced option - Pattern
			$pattern = self::acpt_get_advanced_option($field, 'pattern');

			// ACPT advanced option - CSS
			$css = self::acpt_get_advanced_option($field, 'css');

			if(!empty($css)) {

				$meta_return['class_field_wrapper'] = sanitize_text_field($css);
			}

			// Get WS Form meta configurations for action field types
			switch($type) {

				// Google Map
				case 'Address' :

					// Default latitude and longitude used by ACPT
					$meta_return['google_map_lat'] = '-33.8688';
					$meta_return['google_map_lng'] = '151.2195';

					// Default zoom used by ACPT
					$meta_return['google_map_zoom'] = '18';

					return $meta_return;

				// Embed
				case 'Embed' :

					$meta_return['placeholder'] = 'e.g. https://www.youtube.com/watch?v=XXXXXXXXXXX';

					return $meta_return;

				// Date / Time
				case 'DateTime' :

					$meta_return['input_type_datetime'] = 'datetime-local';

					return $meta_return;

				// Time
				case 'Time' :

					$meta_return['input_type_datetime'] = 'time';

					return $meta_return;

				// Build data grids for radio and select
				case 'Checkbox' :
				case 'Country' :
				case 'Post' :
				case 'PostObject' :
				case 'PostObjectMulti' :
				case 'Radio' :
				case 'Select' :
				case 'SelectMulti' :
				case 'TermObject' :
				case 'TermObjectMulti' :
				case 'Toggle' :
				case 'User' :
				case 'UserMulti' :

					$choices = false;

					switch($type) {

						case 'Checkbox' :

							$meta_key = 'data_grid_checkbox';
							$meta_return['checkbox_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('acpt', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'acpt';
							$meta_return['data_source_acpt_field_id'] = $field->id;

							$choices = property_exists($field, 'options') ? $field->options : array();

							break;

						case 'Country' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Select 2
							$meta_return['select2'] = 'on';

							// Build country choices
							$choices = array();

							$get_countries_alpha_2 = WS_Form_Config::get_countries_alpha_2();

							foreach($get_countries_alpha_2 as $value => $label) {

								$choices[] = (object) array(

									'value' => strtolower($value),
									'label' => $label
								);
							}

							break;

						case 'Post' :
						case 'PostObject' :
						case 'PostObjectMulti' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('post', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'post';

							switch($type) {

								case 'Post' :

									// Get post type
									if(
										property_exists($field, 'relations') &&
										is_array($field->relations) &&
										isset($field->relations[0]) &&
										is_object($field->relations[0])
									) {
										// Get relationship
										$relationship = $field->relations[0];

										// Get relation
										switch($relationship->relationship) {

											case 'OneToManyUni' :
											case 'OneToManyBi' :
											case 'ManyToManyUni' :
											case 'ManyToManyBi' :

												$meta_return['multiple'] = 'on';
												break;
										}

										// Get post type
										if(
											property_exists($relationship, 'to') &&
											is_object($relationship->to) &&
											property_exists($relationship->to, 'value') &&
											is_string($relationship->to->value) &&
											property_exists($relationship->to, 'type') &&
											($relationship->to->type == ACPT\Constants\MetaTypes::CUSTOM_POST_TYPE)
										) {

											$meta_return['data_source_post_filter_post_types'] = array(

												array(

													'data_source_post_post_types' => $relationship->to->value
												)
											);
										}
									}

									break;

								case 'PostObject' :
								case 'PostObjectMulti' :

									$meta_return['data_source_post_filter_post_types'] = array(

										array(

											'data_source_post_post_types' => 'post'
										)
									);

									break;
							}

							// Multi
							if($type == 'PostObjectMulti') {

								$meta_return['multiple'] = 'on';
							}

							// Select2
							$meta_return['select2'] = 'on';
							$meta_return['select2_ajax'] = 'on';

							$choices = property_exists($field, 'options') ? $field->options : array();

							break;

						case 'TermObject' :
						case 'TermObjectMulti' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('term', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'term';

							// Multi
							if($type == 'TermObjectMulti') {

								$meta_return['multiple'] = 'on';
							}

							// Select2
							$meta_return['select2'] = 'on';
							$meta_return['select2_ajax'] = 'on';

							$choices = property_exists($field, 'options') ? $field->options : array();

							break;

						case 'User' :
						case 'UserMulti' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('user', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'user';

							// Multi
							if($type == 'UserMulti') {

								$meta_return['multiple'] = 'on';
							}

							// Select2
							$meta_return['select2'] = 'on';
							$meta_return['select2_ajax'] = 'on';

							$choices = property_exists($field, 'options') ? $field->options : array();

							break;

						case 'Radio' :

							$meta_key = 'data_grid_radio';
							$meta_return['radio_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('acpt', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'acpt';
							$meta_return['data_source_acpt_field_id'] = $field->id;

							$choices = property_exists($field, 'options') ? $field->options : array();

							break;

						case 'Select' :
						case 'SelectMulti' :

							$meta_key = 'data_grid_select';
							$meta_return['select_field_label'] = 1;

							// Data source set-up
							$meta_return = WS_Form_Data_Source::get_data_source_meta('acpt', $meta_return);

							// Set up data source
							$meta_return['data_source_id'] = 'acpt';
							$meta_return['data_source_acpt_field_id'] = $field->id;

							// Multiple
							if($type == 'SelectMulti') {

								$meta_return['multiple'] = 'on';
								$meta_return['placeholder_row'] = '';
							}

							// Select 2
							$meta_return['select2'] = 'on';

							$choices = property_exists($field, 'options') ? $field->options : array();

							break;

						case 'Toggle' :

							$meta_key = 'data_grid_checkbox';
							$meta_return['checkbox_field_label'] = 1;
							$meta_return['class_field'] = 'wsf-switch';

							$choices = array(

								(object) array(

									'value' => 'on',
									'label' => $field->label
								)
							);

							break;
					}

					// Get options
					if(!is_array($choices)) { return false; }

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

					if($type == 'Toggle') {

						$default_value = WS_Form_Common::is_true($default_value) ? 'on' : '';
					}
					if(!is_array($default_value)) { $default_value = array($default_value); }

					foreach($choices as $choice) {

						$value = $choice->value;
						$label = $choice->label;

						$row_new = array(

							'id'		=> $id,
							'data'		=> array($value, $label)
						);

						if(in_array($value, $default_value)) {

							$row_new['default'] = 'on';
						}

						$rows[] = $row_new;

						$id++;
					}

					// Modify meta
					$meta['groups'][0]['rows'] = $rows;

					$meta_return[$meta_key] = $meta;

					return $meta_return;

				case 'Range' :
				case 'Number' :
				case 'Length' :
				case 'Weight' :

					if(!empty($min)) {

						$meta_return['min'] = absint($min);
					}

					if(!empty($max)) {

						$meta_return['max'] = absint($max);
					}

					if(!empty($step)) {

						$meta_return['step'] = absint($step);
					}

					return $meta_return;

				case 'Text' :
				case 'Editor' :

					if(!empty($min)) {

						$meta_return['min_length'] = absint($min);
					}

					if(!empty($max)) {

						$meta_return['max_length'] = absint($max);
					}

					if(!empty($pattern)) {

						$meta_return['pattern'] = sanitize_text_field($pattern);
					}

					if($type == 'Editor') {

						global $wp_version;
						if(WS_Form_Common::version_compare($wp_version, '4.8') >= 0) {
							$meta_return['input_type_textarea'] = 'tinymce';
						}
					}

					return $meta_return;

				case 'HTML' :

					global $wp_version;
					if(WS_Form_Common::version_compare($wp_version, '4.9') >= 0) {
						$meta_return['input_type_textarea'] = 'html';
					}
					return $meta_return;

				case 'File' :
				case 'Gallery' :
				case 'Image' :
				case 'Video' :

					// File handler
					$meta_return['file_handler'] = 'attachment';

					// Sub type
					$meta_return['sub_type'] = 'dropzonejs';

					// Accept
					switch($type) {

						case 'Image' :
						case 'Gallery' :

							$meta_return['accept'] = WS_Form_Common::get_accept_string(array('image'));
							break;

						case 'Video' :

							$meta_return['accept'] = WS_Form_Common::get_accept_string(array('video'));
							break;

						case 'File' :

							$meta_return['accept'] = WS_Form_Common::get_accept_string(false, array('image', 'video'));
							break;
					}

					// Multiple
					if($type == 'Gallery') {

						$meta_return['multiple_file'] = 'on';
					}

					return $meta_return;

				default :

					return false;
			}
		}

		// Process ACPT fields
		public static function acpt_fields_to_meta_data($fields, $group_index = 0, $section_index = 1, $field_index = 1, $depth = 0) {

			$list_fields = array();
			$group_meta_data = array();
			$section_meta_data = array();

			$box_name_last = false;

			foreach($fields as $field) {

				$action_type = $field->type;
				$type = self::acpt_action_field_type_to_ws_form_field_type($field);

				// Skip unsupported field types
				if($type === false) { continue; }

				// Section names
				$box_name = property_exists($field, 'wsf_box_name') ? $field->wsf_box_name : false;

				// Group name
				if(
					($depth === 0) &&
					($box_name !== false) &&
					($box_name !== $box_name_last)
				) {

					if(
						!(
							($section_index === 0) &&
							($field_index === 1) 
						)
					) {

						$section_index++;
					}

					if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
					$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $box_name;

					$box_name_last = $box_name;
				}

				// Repeaters & Groups
				switch($action_type) {

					case 'Repeater' :

						if(property_exists($field, 'children')) {

							$acpt_fields_to_meta_data_return = self::acpt_fields_to_meta_data($field->children, $group_index, $section_index + 1, 1, $depth + 1);

							if(count($acpt_fields_to_meta_data_return['list_fields']) > 0) {

								$section_index++;
								$field_index = 1;

								if(!isset($section_meta_data['group_' . $group_index])) { $section_meta_data['group_' . $group_index] = array(); }
								if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }

								if(property_exists($field, 'width')) {

									$wrapper_width = floatval($field->width);

									if(
										($wrapper_width > 0) &&
										($wrapper_width <= 100)
									) {

										$section_meta_data['group_' . $group_index]['section_' . $section_index]['width_factor'] = ($wrapper_width / 100);
									}		
								}

								$group_meta_data = array_merge($group_meta_data, $acpt_fields_to_meta_data_return['group_meta_data']);
								$section_meta_data = array_merge($section_meta_data, $acpt_fields_to_meta_data_return['section_meta_data']);

								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $field->label;
								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label_render'] = 'on';

								$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeatable'] = 'on';

								if(
									property_exists($field, 'min') &&
									($field->min != '')
								) {

									$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeatable_min'] = absint($field->min);
								}

								if(
									property_exists($field, 'max') &&
									($field->max != '')
								) {

									$section_meta_data['group_' . $group_index]['section_' . $section_index]['section_repeatable_max'] = absint($field->max);
								}

								$section_index++;
								$field_index = 1;

								if(!isset($section_meta_data['group_' . $group_index]['section_' . $section_index])) { $section_meta_data['group_' . $group_index]['section_' . $section_index] = array(); }
								$section_meta_data['group_' . $group_index]['section_' . $section_index]['label'] = $box_name;
							}
						}

						continue 2;
				}

				// Dummy entry
				$list_fields[] = array();

				$field_index++;
			}

			return array('list_fields' => $list_fields, 'group_meta_data' => $group_meta_data, 'section_meta_data' => $section_meta_data, 'group_index' => $group_index, 'section_index' => $section_index);
		}

		// Parent field crawler
		public static function acpt_repeater_field_walker($parent_field_array, $sub_field, $post) {

			$return_value = '';

			$parent_field = array_shift($parent_field_array);

			if(have_rows($parent_field, $post->ID)) {

				while(have_rows($parent_field, $post->ID)) {

					the_row();

					$row = get_row();

					if(count($parent_field_array) == 0) {

						$sub_field_value = get_sub_field($sub_field);

						if($sub_field_value !== false) { return $sub_field_value; }

					} else {

						$return_value = self::acpt_repeater_field_walker($parent_field_array, $sub_field);
					}
				}
			}

			return $return_value;
		}

		// Get parent key data for repeatables. We need this to be able to add the repeatable field meta data.
		public static function acpt_get_parent_data($acpt_field_id, $context = 'post') {

			$acpt_field_object = self::acpt_get_field_object($acpt_field_id, $context);
			if(!is_object($acpt_field_object)) { return false; }

			$acpt_parent_field_id = property_exists($acpt_field_object, 'parentId') ? $acpt_field_object->parentId : false;

			if(empty($acpt_parent_field_id)) { return false; }

			// Get parent ID
			if(!empty($acpt_parent_field_id)) {

				// Get parent field object
				$acpt_parent_field_object = self::acpt_get_field_object($acpt_parent_field_id, $context);
				if(!is_object($acpt_parent_field_object)) { return false; }

				return array(

					'meta_key' => $acpt_parent_field_object->name,
					'acpt_id' => $acpt_parent_field_object->id,
					'type' => $acpt_parent_field_object->type
				);
			}

			return false;
		}

		// Process acpt_field_values as WordPress object (containing ID property)
		public static function acpt_field_values_relation($acpt_field_values, $property = 'ID') {

			if(
				is_string($acpt_field_values) ||
				is_numeric($acpt_field_values)
			) {
				return array($acpt_field_values);
			}

			$return_array = array();

			// Process posts
			if(!is_array($acpt_field_values)) { $acpt_field_values = array($acpt_field_values); }

			foreach($acpt_field_values as $acpt_field_value_single) {

				if(
					is_string($acpt_field_value_single) ||
					is_numeric($acpt_field_value_single)
				) {
					$return_array[] = $acpt_field_value_single;
					continue;
				}

				if(
					!is_object($acpt_field_value_single) ||
					!property_exists($acpt_field_value_single, $property)
				) {
					continue;
				};

				$return_array[] = $acpt_field_value_single->{$property};
			}

			return (count($return_array) > 0) ? $return_array : false;
		}

		// Process acpt_field_values as URL
		public static function acpt_field_values_url($acpt_field_values) {

			return (isset($acpt_field_values['url'])) ? $acpt_field_values['url'] : '';
		}

		// Process acpt_field_values as rating
		public static function acpt_field_values_rating($acpt_field_values) {

			return absint(floatval($acpt_field_values) / 2);
		}

		// Process acpt_field_values by meta key
		public static function acpt_field_values_by_meta_key_as_float($acpt_field_values, $meta_key) {

			$return_value = (is_array($acpt_field_values) && isset($acpt_field_values[$meta_key])) ? $acpt_field_values[$meta_key] : '';

			return ($return_value != '') ? floatval($return_value) : '';
		}

		// Process acpt_field_values as file
		public static function acpt_field_values_file($acpt_field_values) {

			$return_array = array();

			// Process attachment IDs
			if(!is_array($acpt_field_values)) { $acpt_field_values = array($acpt_field_values); }

			foreach($acpt_field_values as $acpt_field_value_single) {

				if(is_array($acpt_field_value_single)) {

					// Gallery
					foreach($acpt_field_value_single as $acpt_attachment) {

						$file_object = self::acpt_field_values_file_process($acpt_attachment);

						if($file_object !== false) {

							$return_array[] = $file_object;
						}
					}

				} else {

					// Image, video or file
					$file_object = self::acpt_field_values_file_process($acpt_field_value_single);

					if($file_object !== false) {

						$return_array[] = $file_object;
					}
				}
			}

			return (count($return_array) > 0) ? $return_array : false;
		}

		// Process acpt_field values as file - Process
		public static function acpt_field_values_file_process($object) {

			if(
				!is_object($object) ||
				!property_exists($object, 'id')
			) {
				return false;
			}

			$attachment_id = absint($object->getId());

			if(!$attachment_id) { return false; }

			$file_object = WS_Form_File_Handler::get_file_object_from_attachment_id($attachment_id);

			if($file_object === false) { return false; }

			return $file_object;
		}

		// Process acpt_field_values as address
		public static function acpt_field_values_address($acpt_field_values) {

			if(
				is_array($acpt_field_values) &&
				isset($acpt_field_values['address'])
			) {
				return $acpt_field_values['address'];

			} else {

				return false;
			}
		}

		// Process acpt_field_values as country
		public static function acpt_field_values_country($acpt_field_values) {

			if(
				is_array($acpt_field_values) &&
				isset($acpt_field_values['country'])
			) {
				return $acpt_field_values['country'];

			} else {

				return false;
			}
		}

		// Process acpt_field_values as boolean
		public static function acpt_field_values_boolean($acpt_field_values, $field_id, $fields, $field_types) {

			// Get meta value array (Array containing values of data grid)
			return WS_Form_Common::is_true($acpt_field_values) ? WS_Form_Common::get_meta_value_array($field_id, $fields, $field_types)[0] : false;
		}

		// Process acpt_field_values as date
		public static function acpt_field_values_date_time($acpt_field_values, $acpt_field_type, $field_id, $fields) {

			if(
				($acpt_field_values === '') ||
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
			$time = strtotime($acpt_field_values);

			switch($acpt_field_type) {

				case 'Date' :

					return $wp_new ? wp_date($format_date, $time, $utc) : gmdate($format_date, $time);

				case 'DateTime' :

					return $wp_new ? wp_date($format_date . ' ' . $format_time, $time, $utc) : gmdate($format_date . ' ' . $format_time, $time);

				case 'Time' :

					return $wp_new ? wp_date($format_time, $time, $utc) : gmdate($format_time, $time);
			}

			return '';
		}

		// Get field
		public static function acpt_get_field_object($acpt_field_id, $context = 'post') {

			// Get ACPT fields
			if(!isset(self::$acpt_fields[$context])) {

				// Retrieve fields
				self::$acpt_fields[$context] = self::acpt_get_fields_all($context, false, true, true);
			}

			// Check if field ID exists
			if(!isset(self::$acpt_fields[$context][$acpt_field_id])) { return false; }

			return self::$acpt_fields[$context][$acpt_field_id];
		}

		// Get field type
		public static function acpt_get_field_type($acpt_field_id, $context = 'post') {

			// Get field object
			$acpt_field_object = self::acpt_get_field_object($acpt_field_id, $context);

			// Return field type
			return (!is_object($acpt_field_object) || !property_exists($acpt_field_object, 'type')) ? false : $acpt_field_object->type;
		}

		// Get field name
		public static function acpt_get_field_name($acpt_field_id, $context = 'post') {

			// Get field object
			$acpt_field_object = self::acpt_get_field_object($acpt_field_id, $context);

			// Return field type
			return (!is_object($acpt_field_object) || !property_exists($acpt_field_object, 'name')) ? false : $acpt_field_object->name;
		}

		// Get box name
		public static function acpt_get_box_name($acpt_field_id, $context = 'post') {

			// Get field object
			$acpt_field_object = self::acpt_get_field_object($acpt_field_id, $context);

			// Return field type
			return (!is_object($acpt_field_object) || !property_exists($acpt_field_object, 'boxName')) ? false : $acpt_field_object->boxName;
		}

		// Get file field types
		public static function acpt_get_field_types_file() {

			return array(

				'File',
				'Gallery',
				'Image',
				'Video'
			);
		}

		// Convert ACPT meta value to WS Form field
		public static function acpt_acpt_meta_value_to_ws_form_field_value($acpt_field_values, $acpt_field_type, $acpt_field_repeater, $field_id, $fields, $field_types) {

			switch($acpt_field_type) {

				case 'Address' :

					if($acpt_field_repeater) {

						// Process object IDs
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_address($acpt_field_value);
						}

					} else {

						// Process object ID
						$acpt_field_values = self::acpt_field_values_address($acpt_field_values);
					}

					break;

				case 'Country' :

					if($acpt_field_repeater) {

						// Process object IDs
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_country($acpt_field_value);
						}

					} else {

						// Process object ID
						$acpt_field_values = self::acpt_field_values_country($acpt_field_values);
					}


					break;

				case 'Post' :
				case 'PostObject' :
				case 'PostObjectMulti' :
				case 'User' :
				case 'UserMulti' :
				case 'TermObject' :
				case 'TermObjectMulti' :

					$property = (($acpt_field_type == 'TermObject') || ($acpt_field_type == 'TermObjectMulti')) ? 'term_id' : 'ID'; 

					if($acpt_field_repeater) {

						// Process object IDs
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_relation($acpt_field_value, $property);
						}

					} else {

						// Process object ID
						$acpt_field_values = self::acpt_field_values_relation($acpt_field_values, $property);
					}

					break;

				case 'File' :
				case 'Image' :
				case 'Video' :
				case 'Gallery' :

					if($acpt_field_repeater) {

						// Process repeated attachment IDs
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_file($acpt_field_value);
						}

					} else {

						// Process regular attachment IDs
						$acpt_field_values = self::acpt_field_values_file($acpt_field_values);
					}

					break;

				case 'Toggle' :

					if($acpt_field_repeater) {

						// Process repeated true false values
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_boolean($acpt_field_value, $field_id, $fields, $field_types);
						}

					} else {

						// Process regular true false value
						$acpt_field_values = self::acpt_field_values_boolean($acpt_field_values, $field_id, $fields, $field_types);
					}

					break;

				case 'Currency' :

					if($acpt_field_repeater) {

						// Process repeated meta values
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_by_meta_key_as_float($acpt_field_value, 'amount');
						}

					} else {

						// Process regular meta value
						$acpt_field_values = self::acpt_field_values_by_meta_key_as_float($acpt_field_values, 'amount');
					}

					break;

				case 'Length' :

					if($acpt_field_repeater) {

						// Process repeated meta value
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_by_meta_key_as_float($acpt_field_value, 'length');
						}

					} else {

						// Process regular meta value
						$acpt_field_values = self::acpt_field_values_by_meta_key_as_float($acpt_field_values, 'length');
					}

					break;

				case 'Weight' :

					if($acpt_field_repeater) {

						// Process repeated meta value
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_by_meta_key_as_float($acpt_field_value, 'weight');
						}

					} else {

						// Process regular meta value
						$acpt_field_values = self::acpt_field_values_by_meta_key_as_float($acpt_field_values, 'weight');
					}

					break;

				case 'Rating' :

					if($acpt_field_repeater) {

						// Process repeated rating values
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_rating($acpt_field_value, $field_id, $fields, $field_types);
						}

					} else {

						// Process regular rating value
						$acpt_field_values = self::acpt_field_values_rating($acpt_field_values, $field_id, $fields, $field_types);
					}

					break;

				case 'Url' :

					if($acpt_field_repeater) {

						// Process repeated URL values
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_url($acpt_field_value, $field_id, $fields, $field_types);
						}

					} else {

						// Process regular URL value
						$acpt_field_values = self::acpt_field_values_url($acpt_field_values, $field_id, $fields, $field_types);
					}

					break;

				case 'Date' :
				case 'DateTime' :
				case 'Time' :

					if($acpt_field_repeater) {

						// Process repeated date
						foreach($acpt_field_values as $acpt_field_values_index => $acpt_field_value) {

							$acpt_field_values[$acpt_field_values_index] = self::acpt_field_values_date_time($acpt_field_value, $acpt_field_type, $field_id, $fields);
						}

					} else {

						// Process regular date
						$acpt_field_values = self::acpt_field_values_date_time($acpt_field_values, $acpt_field_type, $field_id, $fields);
					}

					break;
			}

			return $acpt_field_values;
		}

		// Convert WS Form field value to ACPT meta value
		public static function acpt_ws_form_field_value_to_acpt_meta_value($meta_value, $acpt_field_type, $acpt_field_id = false, $field_id = false, $fields = false, $field_types = false) {

			switch($acpt_field_type) {

				case 'Country' :

					// Convert array to string
					if(is_array($meta_value) && isset($meta_value[0])) { $meta_value = $meta_value[0]; }

					// Get full country name
					$countries = WS_Form_Config::get_countries_alpha_2();

					// Get uppercase of country 2 character code
					$meta_value_uppercase = strtoupper($meta_value);

					if(isset($countries[$meta_value_uppercase])) {

						$meta_value = array(

							'value' => $countries[$meta_value_uppercase],
							'country' => strtolower($meta_value)
						);
					}

					break;

				case 'Currency' :

					$amount = WS_Form_Common::get_number($meta_value, 0, true);
 
					$unit = WS_Form_Common::option_get('currency', WS_Form_Common::get_currency_default());

					$meta_value = array(

						'amount' => $amount,
						'unit' => $unit
					);

					break;

				case 'Length' :

					$length = WS_Form_Common::get_number($meta_value, 0, true);

					$meta_value = array(

						'length' => $length,
						'unit' => 'INCH'
					);

					break;

				case 'Weight' :

					$weight = WS_Form_Common::get_number($meta_value, 0, true);

					$meta_value = array(

						'weight' => $weight,
						'unit' => 'OUNCE'
					);

					break;

				case 'Rating' :

					// ACPT rating is 0 = 10 (10 = 5 stars)
					$meta_value = absint($meta_value * 2);

					break;

				case 'Date' :
				case 'DateTime' :
				case 'Time' :

					// Repurpose date to US format for strtotime
					if($field_id !== false) {

						if($fields !== false) {

							if(!isset($fields[$field_id])) {

								return '';
							}
							// Get field object
							$field_object = $fields[$field_id];
						}

						switch($acpt_field_type) {

							case 'Date' :

								$meta_value = WS_Form_Common::get_date_by_type($meta_value, $field_object, 'Y-m-d');
								break;

							case 'DateTime' :

								$meta_value = WS_Form_Common::get_date_by_type($meta_value, $field_object, 'Y-m-d H:i:s');
								break;

							case 'Time' :

								$meta_value = WS_Form_Common::get_date_by_type($meta_value, $field_object, 'H:i:s');
								break;
						}
					}

					break;

				case 'Toggle' :

					$meta_value = WS_Form_Common::is_true($meta_value) ? 1 : 0;

					break;

				case 'Radio' :
				case 'Select' :
				case 'User' :

					if(
						is_array($meta_value) &&
						isset($meta_value[0])
					) {
						$meta_value = $meta_value[0];
					}

					break;

				// Relationship - Must be an array
				case 'Post' :
				case 'PostObjectMulti' :
				case 'TermObjectMulti' :
				case 'UserMulti' :

					$meta_value = !is_array($meta_value) ? array($meta_value) : $meta_value;

					break;

				// Relationship - Must not be an array
				case 'PostObject' :
				case 'TermObject' :
				case 'User' :

					$meta_value = is_array($meta_value) ? $meta_value[0] : $meta_value;

					break;

				case 'Toggle' :

					$meta_value = WS_Form_Common::is_true($meta_value);

					break;

				case 'Number' :

					if(
						($meta_value != '') &&
						!is_numeric($meta_value)
					) {
						$meta_value = WS_Form_Common::get_number($meta_value, 0, true);
					}

					break;

				case 'Url' :

					if(
						($meta_value != '') &&
						is_string($meta_value)
					) {
						$meta_value = array(

							'url' => $meta_value,
							'label' => ''
						);
					}

					break;
			}

			return $meta_value;
		}

		// Convert action field type to WS Form field type
		public static function acpt_action_field_type_to_ws_form_field_type($field) {

			$type = $field->type;

			switch($type) {

				// Basic
				case 'Text' : return 'text';
				case 'Editor' : return 'textarea';
				case 'Number' : return 'number';
				case 'Range' : return 'range';
				case 'Textarea' : return 'textarea';
//				case 'List' :
				case 'HTML' : return 'textarea';
				case 'Select' : return 'select';
				case 'SelectMulti' : return 'select';
				case 'Toggle' : return 'checkbox';
				case 'Checkbox' : return 'checkbox';
				case 'Radio' : return 'radio';

				// Specialized
				case 'Country' : return 'select';
				case 'Date' : return 'datetime';
				case 'DateTime' : return 'datetime';
//				case 'DateRange' :
				case 'Time' : return 'datetime';
				case 'Url' : return 'url';
				case 'Phone' : return 'tel';
				case 'Email' : return 'email';
				case 'Address' : return 'googleaddress';
				case 'Color' : return 'color';
//				case 'Icon' :
				case 'Rating' : return 'rating';

				// Unit of Measure
				case 'Currency' : return 'price';
				case 'Weight' : return 'number';
				case 'Length' : return 'number';

				// Media
				case 'Embed' : return 'url';
				case 'File' : return 'file';
				case 'Gallery' : return 'file';
				case 'Image' : return 'file';
				case 'Video' : return 'file';

				// Relations
				case 'Post' : return 'select';
				case 'PostObject' : return 'select';
				case 'PostObjectMulti' : return 'select';
				case 'User' : return 'select';
				case 'UserMulti' : return 'select';
				case 'TermObject' : return 'select';
				case 'TermObjectMulti' : return 'select';

				// Repeater
				case 'Repeater' : return 'repeater';

				// Flexible content
//				case 'FlexibleContent' :
			}

			return false;
		}

		// Fields that we can push data to
		public static function acpt_field_mappable($acpt_field_type) {

			switch($acpt_field_type) {

				// Basic
				case 'Text' :
				case 'Editor' :
				case 'Number' :
				case 'Range' :
				case 'Textarea' :
//				case 'List' :
				case 'HTML' :
				case 'Select' :
				case 'SelectMulti' :
				case 'Toggle' :
				case 'Checkbox' :
				case 'Radio' :

				// Specialized
				case 'Country' :
				case 'Date' :
				case 'DateTime' :
//				case 'DateRange' :
				case 'Time' :
				case 'Url' :
				case 'Phone' :
				case 'Email' :
				case 'Address' :
				case 'Color' :
//				case 'Icon' :
				case 'Rating' :

				// Unit of Measure
				case 'Currency' :
				case 'Weight' :
				case 'Length' :

				// Media
				case 'Embed' :
				case 'File' :
				case 'Gallery' :
				case 'Image' :
				case 'Video' :

				// Flexible content
//				case 'FlexibleContent' :

				// Relations
				case 'Post' :
				case 'PostObject' :
				case 'PostObjectMulti' :
				case 'User' :
				case 'UserMulti' :
				case 'TermObject' :
				case 'TermObjectMulti' :

					return true;

				default :

					return false;
			}
		}
	}