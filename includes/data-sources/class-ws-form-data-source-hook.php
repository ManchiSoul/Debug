<?php

	#[AllowDynamicProperties]
	class WS_Form_Data_Source_Hook extends WS_Form_Data_Source {

		public $id = 'hook';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 0;

		public function __construct() {

			// Set label
			$this->label = __('WordPress Filter Hook', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving data...', 'ws-form');

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
			if(!isset($meta_key_config['default'])) { return self::error(__('No default value', 'ws-form'), $field_id, $this, $api_request); }

			// Get default value
			$meta_value = $meta_key_config['default'];

			// Remove unnecessary elements
			if(isset($meta_value['rows_per_page'])) { unset($meta_value['rows_per_page']); }
			if(isset($meta_value['group_index'])) { unset($meta_value['group_index']); }
			if(isset($meta_value['default'])) { unset($meta_value['default']); }

			// Remove column and group row IDs
			foreach($meta_value['columns'] as $column_index => $column) {

				if(isset($meta_value['columns'][$column_index]['id'])) { unset($meta_value['columns'][$column_index]['id']); }
			}

			$row_options = array('default', 'required', 'disabled', 'hidden');

			foreach($meta_value['groups'] as $group_index => $group) {

				if(isset($meta_value['groups'][$group_index]['page'])) { unset($meta_value['groups'][$group_index]['page']); }
				if(isset($meta_value['groups'][$group_index]['mask_group'])) { unset($meta_value['groups'][$group_index]['mask_group']); }

				foreach($group['rows'] as $row_index => $row) {

					if(isset($meta_value['groups'][$group_index]['rows'][$row_index]['id'])) { unset($meta_value['groups'][$group_index]['rows'][$row_index]['id']); }

					// Options
					foreach($row_options as $option) {

						if(isset($meta_value['groups'][$group_index]['rows'][$row_index][$option])) { $meta_value['groups'][$group_index]['rows'][$row_index][$option] = ($meta_value['groups'][$group_index]['rows'][$row_index][$option] === 'on'); }
					}
				}
			}

			// Get Meta Box field key
			$hook = $this->{'data_source_' . $this->id . '_hook'};

			// Get data
  			try {
 
	 			$meta_value = apply_filters($hook, $meta_value, absint($field_id), $form_object);
			
			} catch(Exception $e) {

				return self::error(sprintf(__('PHP Exception: %s', 'ws-form'), $e->getMessage()), $field_id, $this, $api_request);;
			}

			// Add nav elements
			if(!isset($meta_value['rows_per_page'])) { $meta_value['rows_per_page'] = 10; }
			if(!isset($meta_value['group_index'])) { $meta_value['group_index'] = 0; }

			// Row ID
			$row_id = 0;

			// Add column and group row IDs
			foreach($meta_value['columns'] as $column_index => $column) {

				$meta_value['columns'][$column_index]['id'] = $column_index;
			}
			foreach($meta_value['groups'] as $group_index => $group) {

				// Reset page
				$meta_value['groups'][$group_index]['page'] = 0;

				// Mask group
				$meta_value['groups'][$group_index]['mask_group'] = isset($meta_value['groups'][$group_index]['mask_group']) ? (!empty($meta_value['groups'][$group_index]['mask_group']) ? 'on' : '') : '';

				// Rows
				foreach($group['rows'] as $row_index => $row) {

					// ID
					if(!isset($meta_value['groups'][$group_index]['rows'][$row_index]['id'])) { $meta_value['groups'][$group_index]['rows'][$row_index]['id'] = $row_id++; }

					// Options
					foreach($row_options as $option) {

						$meta_value['groups'][$group_index]['rows'][$row_index][$option] = (isset($meta_value['groups'][$group_index]['rows'][$row_index][$option]) && $meta_value['groups'][$group_index]['rows'][$row_index][$option]) ? 'on' : '';
					}
				}
			}

			// Encode for return
			$meta_value = json_decode(wp_json_encode($meta_value));

			// Return data
			return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value, 'max_num_pages' => 0, 'meta_keys' => array());
		}

		// Get meta keys
		public function get_data_source_meta_keys() {

			return array(

				'data_source_' . $this->id . '_hook'
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

				// Hook
				'data_source_' . $this->id . '_hook'	=> array(

					'label'		=>	__('Filter Hook Tag', 'ws-form'),
					'type'		=>	'text',
					'help'		=>	sprintf(

						'%s <a href="%s" target="_blank">%s</a>',

						__('Tag name of the filter hook.', 'ws-form'),
						WS_Form_Common::get_plugin_website_url('/knowledgebase/data-source-wordpress-filter-hook/'),
						__('Learn More', 'ws-form')
					),
				),

				// Get Data
				'data_source_' . $this->id . '_get' => array(

					'label'						=>	__('Get Data', 'ws-form'),
					'type'						=>	'button',
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_hook',
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

			// Get form ID
			$form_object = false;
			$form_id = WS_Form_Common::get_query_var('id', 0);
			if($form_id > 0) {

				$ws_form_form = New WS_Form_Form();
				$ws_form_form->id = $form_id;
				$form_object = $ws_form_form->db_read(true, true);
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
			$get_return = self::get($form_object, $field_id, $page, $meta_key, $meta_value, false, true);

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

	new WS_Form_Data_Source_Hook();
