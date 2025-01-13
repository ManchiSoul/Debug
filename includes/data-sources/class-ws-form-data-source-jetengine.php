<?php

	#[AllowDynamicProperties]
	class WS_Form_Data_Source_JetEngine extends WS_Form_Data_Source {

		public $id = 'jetengine';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 0;

		public function __construct() {

			// Set label
			$this->label = __('JetEngine Field Options', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving JetEngine field options...', 'ws-form');

			// Register action
			parent::register($this);

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);

			// Register API endpoint
			add_action('rest_api_init', array($this, 'rest_api_init'), 10, 0);

			// Records per page
			$this->records_per_page = apply_filters('wsf_data_source_' . $this->id . '_records_per_age', $this->records_per_page);
		}

		// Get
		public function get($form_object, $field_id, $page, $meta_key, $meta_value, $no_paging = false, $api_request = false) {

			// Check meta key
			if(empty($meta_key)) { return self::error(__('No meta key specified', 'ws-form'), $field_id, $this, $api_request); }

			// Get meta key config
			$meta_keys = WS_Form_Config::get_meta_keys();
			if(!isset($meta_keys[$meta_key])) { return self::error(__('Unknown meta key', 'ws-form'), $field_id, $this, $api_request); }
			$meta_key_config = $meta_keys[$meta_key];

			// Check meta value
			if(
				!is_object($meta_value) ||
				!isset($meta_value->columns) ||
				!isset($meta_value->groups) ||
				!isset($meta_value->groups[0])
			) {

				if(!isset($meta_key_config['default'])) { return self::error(__('No default value', 'ws-form'), $field_id, $this, $api_request); }

				// If meta_value is invalid, create one from default
				$meta_value = json_decode(wp_json_encode($meta_key_config['default']));
			}

			// Base meta
			$group = clone($meta_value->groups[0]);

			// Get JetEngine field name
			$jetengine_field_name = $this->{'data_source_' . $this->id . '_field_name'};

			// Get JetEngine field object
			$jetengine_field_object = WS_Form_JetEngine::jetengine_get_field_settings($jetengine_field_name, false);
			if($jetengine_field_object === false) { return self::error(__('Invalid field ID', 'ws-form'), $field_id, $this, $api_request); }

			// Filter by post?
/*			$filter_by_options = false;
			$filter_by_post = $this->{'data_source_' . $this->id . '_filter_by_post'};
			if($filter_by_post) {

				// Get post ID
				$filter_by_post_id = $this->{'data_source_' . $this->id . '_filter_by_post_id'};
				if($filter_by_post_id == '') { $filter_by_post_id = '#post_id'; }
				$filter_by_post_id = absint(WS_Form_Common::parse_variables_process($filter_by_post_id, $form_object, false, 'text/plain'));

				if($filter_by_post_id > 0) {

					// Get parent data to see if this field is in a repeater or group
					$jetengine_parent_data = WS_Form_JetEngine::jetengine_get_parent_data($jetengine_field_name);
					$jetengine_parent_field_type = isset($jetengine_parent_data['type']) ? $jetengine_parent_data['type'] : false;

					switch($jetengine_parent_field_type) {

						case 'repeater' :
						case 'group' :

							$filter_by_options = array();

							// Get JetEngine field name (We'll use this to look up values)
							$jetengine_field_name = $jetengine_field_object['title'];

							// Get parent field object (contains an array of child field values)
							$jetengine_field_name_parent = $jetengine_parent_data['jetengine_key'];
							$jetengine_field_object_parent = get_field_object($jetengine_field_name_parent);

							// Get values
							$jetengine_field_object_parent_values = isset($jetengine_field_object_parent['value']) ? $jetengine_field_object_parent['value'] : false;

							// Process values
							if(is_array($jetengine_field_object_parent_values)) {

								if($jetengine_parent_field_type == 'repeater') {

									foreach($jetengine_field_object_parent_values as $row_index => $row) {

										foreach($row as $name => $value) {

											if($name == $jetengine_field_name) {

											if(empty($value)) { $value = array(); }
											if(is_string($value)) { $value = array($value); }
												$filter_by_options = array_merge($filter_by_options, $value);
											}
										}
									}
								}

								if($jetengine_parent_field_type == 'group') {

									foreach($jetengine_field_object_parent_values as $name => $value) {

										if($name == $jetengine_field_name) {

											if(empty($value)) { $value = array(); }
											if(is_string($value)) { $value = array($value); }
											$filter_by_options = array_merge($filter_by_options, $value);
										}
									}
								}
							}

							break;

						default :

							// Field that is not in a group or repeater
							$filter_by_options = get_field($jetengine_field_name, $filter_by_post_id, false);
							if(empty($filter_by_options)) { $filter_by_options = array(); }
							if(is_string($filter_by_options)) { $filter_by_options = array($filter_by_options); }
					}
				}
			}
*/
			// Get JetEngine field label
			$label = isset($jetengine_field_object['label']) ? $jetengine_field_object['label'] : $this->label;

			// Build rows
			$rows = array();
			$row_index = 1;

			// Columns
			$meta_value->columns = array(

				(object) array('id' => 0, 'label' => __('Value', 'ws-form')),
				(object) array('id' => 1, 'label' => __('Label', 'ws-form'))
			);

			// Get source
			if(
				isset($jetengine_field_object['options_source']) &&
				(is_string($jetengine_field_object['options_source']))
			) {

				$source = $jetengine_field_object['options_source'];

			} else {

				$source = 'options';
			}

			switch($source) {

				case 'glossary' :

					// Get glossary options
					$glossary_id = isset($jetengine_field_object['glossary_id']) ? absint($jetengine_field_object['glossary_id']) : false;

					if($glossary_id > 0) {

						// Get glossary
						$glossary = jet_engine()->glossaries->data->get_item_for_edit($glossary_id);

						// Get fields
						if(
							isset($glossary['fields']) &&
							is_array($glossary['fields'])
						) {

							foreach($glossary['fields'] as $field) {

								$rows[] = (object) array(

									'id'		=> $row_index++,
									'default'	=> (isset($field['is_checked']) ? $field['is_checked'] : ''),
									'data'		=> array(

										$field['value'],
										$field['label']
									)
								);
							}
						}
					}

					break;

				case 'query' :

					// Get query ID
					$query_id = isset($jetengine_field_object['query_id']) ? absint($jetengine_field_object['query_id']) : false;

					if($query_id > 0) {

						// Get query
						$query = \Jet_Engine\Query_Builder\Manager::instance()->get_query_by_id( $query_id );

						// Get query items
						$query_items = $query->get_items();

						if(is_array($query_items)) {

							// Process according to type
							switch($query->query_type) {

								case 'posts' :

									// Columns
									$meta_value->columns = array(

										(object) array('id' => 0, 'label' => __('ID', 'ws-form')),
										(object) array('id' => 1, 'label' => __('Title', 'ws-form')),
										(object) array('id' => 2, 'label' => __('Status', 'ws-form')),
										(object) array('id' => 3, 'label' => __('Slug', 'ws-form')),
										(object) array('id' => 4, 'label' => __('Date', 'ws-form')),
										(object) array('id' => 5, 'label' => __('Type', 'ws-form')),
										(object) array('id' => 6, 'label' => __('Permalink', 'ws-form')),
										(object) array('id' => 7, 'label' => __('Excerpt', 'ws-form'))
									);

									if(!is_array($query_items)) { break; }

									foreach($query_items as $query_item) {

										if(!is_object($query_item)) { continue; }

										$post_id = $query_item->ID;

										$rows[] = (object) array(

											'id'		=> $row_index++,
											'data'		=> array(

												strval($post_id),
												$query_item->post_title,
												$query_item->post_status,
												$query_item->post_name,
												get_the_date('', $post_id),
												$query_item->post_type,
												get_permalink($post_id),
												$query_item->post_excerpt
											)
										);
									}

									break;

								case 'users' :

									// Columns
									$meta_value->columns = array(

										(object) array('id' => 0, 'label' => __('ID', 'ws-form')),
										(object) array('id' => 1, 'label' => __('Display Name', 'ws-form')),
										(object) array('id' => 2, 'label' => __('Nicename', 'ws-form')),
										(object) array('id' => 3, 'label' => __('Login', 'ws-form')),
										(object) array('id' => 4, 'label' => __('Email', 'ws-form')),
										(object) array('id' => 5, 'label' => __('Website', 'ws-form'))
									);

									if(!is_array($query_items)) { break; }

									foreach($query_items as $query_item) {

										if(!is_object($query_item)) { continue; }

										$user_id = $query_item->ID;

										$rows[] = (object) array(

											'id'		=> $row_index++,
											'data'		=> array(

												strval($user_id),
												$query_item->get('display_name'),
												$query_item->get('user_nicename'),
												$query_item->get('user_login'),
												$query_item->get('user_email'),
												$query_item->get('user_url')
											)
										);
									}

									break;

								case 'terms' :

									// Columns
									$meta_value->columns = array(

										(object) array('id' => 0, 'label' => __('ID', 'ws-form')),
										(object) array('id' => 1, 'label' => __('Name', 'ws-form')),
										(object) array('id' => 2, 'label' => __('Slug', 'ws-form')),
										(object) array('id' => 3, 'label' => __('Parent', 'ws-form')),
										(object) array('id' => 4, 'label' => __('Count', 'ws-form')),
										(object) array('id' => 5, 'label' => __('Link', 'ws-form'))
									);

									if(!is_array($query_items)) { break; }

									foreach($query_items as $query_item) {

										if(!is_object($query_item)) { continue; }

										$term_id = $query_item->term_id;

										$rows[] = (object) array(

											'id'		=> $row_index++,
											'data'		=> array(

												strval($term_id),
												$query_item->name,
												$query_item->slug,
												$query_item->parent,
												$query_item->count,
												get_term_link($term_id, $query_item->taxonomy)
											)
										);
									}

									break;
							}
						}
					}

					break;

				default :

					// Get JetEngine field options
					$options = isset($jetengine_field_object['options']) ? $jetengine_field_object['options'] : array();

					// Run through options
					foreach($options as $option) {

						// Filter by post?
		/*				if($filter_by_options !== false) {

							if(!in_array($value, $filter_by_options)) { continue; }
						}
		*/
						$rows[] = (object) array(

							'id'		=> $row_index++,
							'default'	=> (isset($option['is_checked']) ? $option['is_checked'] : ''),
							'data'		=> array(

								$option['key'],
								$option['value']
							)
						);
					}

					break;
			}

			// Build new group if one does not exist
			if(!isset($meta_value->groups[0])) {

				$meta_value->groups[0] = $group;
			}

			$meta_value->groups[0]->label = $label;

			// Rows
			$meta_value->groups[0]->rows = $rows;

			// Delete any old groups
			$group_index = 1;
			while(isset($meta_value->groups[$group_index])) {

				unset($meta_value->groups[$group_index++]);
			}

			switch($source) {

				default :

					// Column mapping
					$meta_keys = parent::get_column_mapping(array(), $meta_value, $meta_key_config);
			}

			// Return data
			return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value, 'max_num_pages' => 0, 'meta_keys' => $meta_keys);
		}

		// Get meta keys
		public function get_data_source_meta_keys() {

			return array(

				'data_source_' . $this->id . '_field_name',
//				'data_source_' . $this->id . '_filter_by_post',
//				'data_source_' . $this->id . '_filter_by_post_id'
			);
		}

		// Get settings
		public function get_data_source_settings() {

			// Build settings
			$settings = array(

				'meta_keys' => self::get_data_source_meta_keys()
			);

			// Add retrieve button
			$settings['meta_keys'][] = 'data_source_' . $this->id . '_get';

			// Wrap settings so they will work with sidebar_html function in admin.js
			$settings = parent::get_settings_wrapper($settings);

			// Add label
			$settings->label = $this->label;

			// Add label retrieving
			$settings->label_retrieving = $this->label_retrieving;

			// Add API GET endpoint
			$settings->endpoint_get = 'data-source/' . $this->id . '/';

			// Apply filter
			$settings = apply_filters('wsf_data_source_' . $this->id . '_settings', $settings);

			return $settings;
		}

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build config_meta_keys
			$config_meta_keys = array(

				// JetEngine Field
				'data_source_' . $this->id . '_field_name' => array(

					'label'						=>	__('JetEngine Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	is_admin() ? WS_Form_JetEngine::jetengine_get_fields_all(false, null, true, false, true) : array(),
					'options_blank'				=>	__('Select...', 'ws-form')
				),

				// JetEngine Filter by Post
/*				'data_source_' . $this->id . '_filter_by_post' => array(

					'label'						=>	__('Filter by Post', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Filter the options by those selected in a post.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_field_name',
							'meta_value'		=>	''
						)
					)
				),
*/
				// JetEngine Filter by Post ID
/*				'data_source_' . $this->id . '_filter_by_post_id' => array(

					'label'						=>	__('Post ID', 'ws-form'),
					'type'						=>	'text',
					'default'					=>	'',
					'placeholder'				=>	'#post_id',
					'help'						=>	sprintf(
*/
						/* translators: %s = WS Form */
/*						__('Choose the post ID to filter by. This can be a number or %s variable. If blank, the ID of the post the form is shown on will be used.', 'ws-form'),

						WS_FORM_NAME_GENERIC
					),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_field_name',
							'meta_value'		=>	''
						),

						array(

							'logic_previous'	=>	'&&',
							'logic'				=>	'==',
							'meta_key'			=>	'data_source_' . $this->id . '_filter_by_post'
						)
					)
				),
*/
				// Get Data
				'data_source_' . $this->id . '_get' => array(

					'label'						=>	__('Get Data', 'ws-form'),
					'type'						=>	'button',
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_field_name',
							'meta_value'		=>	''
						)
					),
					'key'						=>	'data_source_get'
				)
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}

		// Build REST API endpoints
		public function rest_api_init() {

			// Get data source
			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/data-source/' . $this->id . '/', array('methods' => 'POST', 'callback' => array($this, 'api_post'), 'permission_callback' => function () { return WS_Form_Common::can_user('edit_form'); }));
		}

		// api_post
		public function api_post() {

			// Get meta keys
			$meta_keys = self::get_data_source_meta_keys();

			// Read settings
			foreach($meta_keys as $meta_key) {

				$this->{$meta_key} = WS_Form_Common::get_query_var($meta_key, false);
				if(
					is_object($this->{$meta_key}) ||
					is_array($this->{$meta_key})
				) {

					$this->{$meta_key} = json_decode(wp_json_encode($this->{$meta_key}));
				}
			}

			// Get field ID
			$field_id = WS_Form_Common::get_query_var('field_id', 0);

			// Get page
			$page = absint(WS_Form_Common::get_query_var('page', 1));

			// Get meta key
			$meta_key = WS_Form_Common::get_query_var('meta_key', 0);

			// Get meta value
			$meta_value = WS_Form_Common::get_query_var('meta_value', 0);

			// Get return data
			$get_return = self::get(false, $field_id, $page, $meta_key, $meta_value, false, true);

			// Error checking
			if($get_return['error']) {

				// Error
				return self::api_error($get_return);

			} else {

				// Success
				return $get_return;
			}
		}
	}

	new WS_Form_Data_Source_JetEngine();
