<?php

	class WS_Form_Action_Redirect extends WS_Form_Action {

		public $id = 'redirect';
		public $pro_required = false;
		public $label;
		public $label_action;
		public $events;
		public $multiple = true;
		public $configured = true;
		public $priority = 150;
		public $can_repost = false;
		public $form_add = false;
		public $woocommerce_bypass = true;

		// Config
		public $type;
		public $url;
		public $page;
		public $post_id;
		public $qsp;
		public $exclude_blank_parameters;

		// Constants
		const MAX_PAGE_SEARCH_RESULTS = 10;

		public function __construct() {

			// Set label
			$this->label = __('Redirect', 'ws-form');

			// Set label for actions pull down
			$this->label_action = __('Redirect', 'ws-form');

			// Events
			$this->events = array('submit');

			// Register action
			parent::register($this);

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);

			// API
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 10, 0 );
		}

		public function post($form, &$submit, $config) {

			// Load config
			self::load_config($config);

			// Get URL
			switch($this->type) {

				case 'page' :

					// Get post ID
					$post_id = absint($this->page);

					if($post_id == 0) {

						parent::error(__('Redirect page invalid', 'ws-form'));
					}

					// Get URL
					$url = get_permalink($post_id);

					break;

				case 'post_id' :

					// Get post ID
					$post_id = absint(WS_Form_Common::parse_variables_process($this->post_id, $form, $submit, 'text/plain'));

					if($post_id == 0) {

						parent::error(__('Redirect post ID invalid', 'ws-form'));
					}

					// Get URL
					$url = get_permalink($post_id);

					break;

				default :

					$url = $this->url;
			}

			// Check URL
			if($url !== '') {

				$url = WS_Form_Common::parse_variables_process($url, $form, $submit, 'text/plain');

				// Check for query string parameters
				if(!empty($this->qsp)) {

					foreach($this->qsp as $qsp) {

						// Read field
						if(!isset($qsp['action_' . $this->id . '_qsp_field'])) { continue; }
						$qsp_field = $qsp['action_' . $this->id . '_qsp_field'];
						if($qsp_field == '') { continue; }

						// Read value
						if(!isset($qsp['action_' . $this->id . '_qsp_value'])) { continue; }
						$qsp_value = $qsp['action_' . $this->id . '_qsp_value'];
						if($qsp_value == '') { continue; }

						// Parse field and value
						$qsp_field = WS_Form_Common::parse_variables_process($qsp_field, $form, $submit, 'text/plain');
						$qsp_value = WS_Form_Common::parse_variables_process($qsp_value, $form, $submit, 'text/plain');

						// Exclude blank parameters
						if(
							$this->exclude_blank_parameters &&
							($qsp_value == '')
						) {
							continue;
						}

						// Santize and add to URL
						$url = add_query_arg(urlencode($qsp_field), urlencode($qsp_value), $url);
					}
				}

				// Redirect to URL
				parent::success(sprintf(__('Redirect added to queue: %s', 'ws-form'), $url), array(

					array(

						'action' => $this->id,
						'url' => $url
					)
				));

			} else {

				// Invalid redirect URL
				parent::error(__('No redirect URL in action configuration', 'ws-form'));
			}
		}

		public function load_config($config) {

			$this->type = parent::get_config($config, 'action_' . $this->id . '_type');
			$this->url = parent::get_config($config, 'action_' . $this->id . '_url');
			$this->page = parent::get_config($config, 'action_' . $this->id . '_page');
			$this->post_id = parent::get_config($config, 'action_' . $this->id . '_post_id');
			$this->qsp = parent::get_config($config, 'action_' . $this->id . '_qsp');
			if(!$this->qsp) { $this->qsp = array(); }
			$this->exclude_blank_parameters = parent::get_config($config, 'action_' . $this->id . '_exclude_blank_parameters');
		}

		// Get settings
		public function get_action_settings() {

			$settings = array(

				'meta_keys'		=> array(

					'action_' . $this->id . '_type',
					'action_' . $this->id . '_url',
					'action_' . $this->id . '_page',
					'action_' . $this->id . '_post_id',
					'action_' . $this->id . '_qsp',
					'action_' . $this->id . '_exclude_blank_parameters'
				)
			);

			// Wrap settings so they will work with sidebar_html function in admin.js
			$settings = parent::get_settings_wrapper($settings);

			// Add labels
			$settings->label = $this->label;
			$settings->label_action = $this->label_action;

			// Add multiple
			$settings->multiple = $this->multiple;

			// Add events
			$settings->events = $this->events;

			// Add can_repost
			$settings->can_repost = $this->can_repost;

			// Apply filter
			$settings = apply_filters('wsf_action_' . $this->id . '_settings', $settings);

			return $settings;
		}

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build config_meta_keys
			$config_meta_keys = array(

				// Type
				'action_' . $this->id . '_type'	=> array(

					'label'			=>	__('Type', 'ws-form'),
					'type'			=>	'select',
					'options'		=>	array(

						array('value' => '', 'text' => 'URL'),
						array('value' => 'page', 'text' => 'Page'),
						array('value' => 'post_id', 'text' => 'Post ID')
					)
				),

				// URL
				'action_' . $this->id . '_url'	=> array(

					'label'				=>	__('URL', 'ws-form'),
					'type'				=>	'text',
					'help'				=>	__('URL to redirect to.', 'ws-form'),
					'default'			=>	'/',
					'variable_helper'	=>	true,
					'condition'			=>	array(

						array(

							'logic'          => '==',
							'meta_key'       => 'action_' . $this->id . '_type',
							'meta_value'     => ''
						)
					)
				),

				// Page
				'action_' . $this->id . '_page'	=> array(

					'label'						=>	__('Page', 'ws-form'),
					'type'						=>	'select_ajax',
					'select_ajax_method_search' => 'action_' . $this->id . '_page_search',
					'select_ajax_method_cache'  => 'action_' . $this->id . '_page_cache',
					'select_ajax_placeholder'   => __('Search pages...', 'ws-form'),
					'help'						=>	__('Choose the page to redirect to', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'          => '==',
							'meta_key'       => 'action_' . $this->id . '_type',
							'meta_value'     => 'page'
						)
					)
				),

				// Post ID
				'action_' . $this->id . '_post_id'	=> array(

					'label'				=>	__('Post ID', 'ws-form'),
					'type'				=>	'text',
					'help'				=>	__('Post ID to redirect to.', 'ws-form'),
					'variable_helper'	=>	true,
					'condition'			=>	array(

						array(

							'logic'          => '==',
							'meta_key'       => 'action_' . $this->id . '_type',
							'meta_value'     => 'post_id'
						)
					)
				),

				// Query string parameters
				'action_' . $this->id . '_qsp'	=> array(

					'label'				=>	__('Query String Parameters', 'ws-form'),
					'type'				=>	'repeater',
					'meta_keys'			=>	array(

						'action_' . $this->id . '_qsp_field',
						'action_' . $this->id . '_qsp_value'
					),
					'help'				=>	__('Query string parameters to add to the URL. WS Form will URL encode these values. Variables such as #field(123) can be used here.', 'ws-form'),
					'variable_helper'	=>	true
				),

				// Query string parameters - Field
				'action_' . $this->id . '_qsp_field'	=> array(

					'label'			=>	__('Field', 'ws-form'),
					'type'			=>	'text'
				),

				// Query string parameters - Value
				'action_' . $this->id . '_qsp_value'	=> array(

					'label'			=>	__('Value', 'ws-form'),
					'type'			=>	'text'
				),

				// Exclude blank values
				'action_' . $this->id . '_exclude_blank_parameters'	=> array(

					'label'			=>	__('Exclude Blank Parameters', 'ws-form'),
					'type'			=>	'checkbox',
					'help'			=>	__('If checked, any rows above that have a blank value will be excluded from the query string.', 'ws-form'),
				)
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}

		// Build REST API endpoints
		public function rest_api_init() {

			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/select2/action_' . $this->id . '_page_search/', array( 'methods' => 'GET', 'callback' => array( $this, 'api_page_search' ), 'permission_callback' => function () {
				return WS_Form_Common::can_user('edit_form');
			} ) );
			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/select2/action_' . $this->id . '_page_cache/', array( 'methods' => 'POST', 'callback' => array( $this, 'api_page_cache' ), 'permission_callback' => function () {
				return WS_Form_Common::can_user('edit_form');
			} ) );
		}

		// API endpoint - Search pages
		public function api_page_search($parameters) {

			global $wpdb;

			$term = WS_Form_Common::get_query_var_nonce('term', '', $parameters);

			$results = array();

			$sql = $wpdb->prepare(

				"SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_title LIKE %s AND post_type = 'page' AND NOT (post_status = 'trash');",
				'%' . $term . '%'
			);

			$posts = $wpdb->get_results($sql);

			foreach ($posts as $post) {

				$results[] = array('id' => $post->ID, 'text' => sprintf('%s (ID: %u)', $post->post_title, $post->ID));
			}

			return array('results' => $results);
		}

		// API endpoint - Cache pages (Used for initial load of select2)
		public function api_page_cache($parameters) {

			$return_array = array();

			$post_ids = WS_Form_Common::get_query_var_nonce('ids', '', $parameters);

			foreach ($post_ids as $post_id) {

				$post_id = absint($post_id);

				$post_title = get_the_title($post_id);

				if (!empty($post_title)) {

					$return_array[$post_id] = $post_title;
				}
			}

			return $return_array;
		}
	}

	new WS_Form_Action_Redirect();
