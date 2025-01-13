<?php

	class WS_Form_Action_Conversion extends WS_Form_Action {

		public $id = 'conversion';
		public $pro_required = true;
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

		// Config - Google
		public $tag_action;
		public $tag_category;
		public $tag_label;
		public $tag_value;
		public $tag_custom;

		// Config - Data Layer
		public $data_layer_variables;

		// Config - Fathom
		public $fathom_event;
		public $fathom_cents;
		public $fathom_site_id;

		// Config - Bento
		public $bento_method;
		public $bento_name;
		public $bento_payload;

		// Config - Facebook - Standard
		public $fb_standard_event;
		public $fb_standard_properties;

		// Config - Facebook - Custom
		public $fb_custom_event;
		public $fb_custom_properties;

		// Config - LinkedIn
		public $linkedin_conversion_id;

		public function __construct() {

			// Set label
			$this->label = __('Conversion Tracking', 'ws-form');

			// Set label for actions pull down
			$this->label_action = __('Conversion Tracking', 'ws-form');

			// Events
			$this->events = array('submit');

			// Register action
			parent::register($this);

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);
		}

		public function post($form, &$submit, $config) {

			// Load config
			self::load_config($config);

			// Run conversion tag
			switch($this->type) {

				case 'google' :

					// Process value
					$value = WS_Form_Common::parse_variables_process($this->tag_value, $form, $submit, 'text/plain');
					$value = !in_array(strtolower($value), array('', 'null')) ? absint($value) : 'null';

					// Build parse values
					$parse_values = array(

						'event_action' 		=> esc_js(WS_Form_Common::parse_variables_process($this->tag_action, $form, $submit, 'text/plain')),
						'event_category' 	=> esc_js(WS_Form_Common::parse_variables_process($this->tag_category, $form, $submit, 'text/plain')),
						'event_label' 		=> esc_js(WS_Form_Common::parse_variables_process($this->tag_label, $form, $submit, 'text/plain')),
						'value' 			=> esc_js($value)
					);

					if(!empty($this->data_layer_variables) && is_array($this->data_layer_variables)) {

						foreach($this->data_layer_variables as $data_layer_variable) {

							// Checks
							if(!isset($data_layer_variable['action_' . $this->id . '_dl_var_key'])) { continue; }
							if($data_layer_variable['action_' . $this->id . '_dl_var_key'] == '') { continue; }
							if(!isset($data_layer_variable['action_' . $this->id . '_dl_var_value'])) { continue; }

							$parse_values[$data_layer_variable['action_' . $this->id . '_dl_var_key']] = WS_Form_Common::parse_variables_process($data_layer_variable['action_' . $this->id . '_dl_var_value'], $form, $submit, 'text/plain');
						}
					}

					// Fire event
					parent::success(__('Google analytics event added to queue', 'ws-form'), array(

						array(

							'action' 		=> $this->id,
							'type' 			=> $this->type,
							'parse_values' 	=> $parse_values
						)
					));

					break;

				case 'data_layer' :

					// Build parse values
					$parse_values = array();

					if(!empty($this->data_layer_variables) && is_array($this->data_layer_variables)) {

						foreach($this->data_layer_variables as $data_layer_variable) {

							// Checks
							if(!isset($data_layer_variable['action_' . $this->id . '_dl_var_key'])) { continue; }
							if($data_layer_variable['action_' . $this->id . '_dl_var_key'] == '') { continue; }
							if(!isset($data_layer_variable['action_' . $this->id . '_dl_var_value'])) { continue; }

							$parse_values[$data_layer_variable['action_' . $this->id . '_dl_var_key']] = WS_Form_Common::parse_variables_process($data_layer_variable['action_' . $this->id . '_dl_var_value'], $form, $submit, 'text/plain');
						}
					}

					// Fire event
					parent::success(__('Data layer event added to queue', 'ws-form'), array(

						array(

							'action' 		=> $this->id,
							'type' 			=> $this->type,
							'parse_values' 	=> $parse_values
						)
					));

					break;

				case 'fathom' :

					// Build parse values
					$params_array = array();

					if(!empty($this->fathom_cents)) { $params_array['_value'] = absint(WS_Form_Common::parse_variables_process($this->fathom_cents, $form, $submit, 'text/plain')); }
					if(!empty($this->fathom_site_id)) { $params_array['_site_id'] = WS_Form_Common::parse_variables_process($this->fathom_site_id, $form, $submit, 'text/plain'); }

					$params = (count($params_array) > 0) ? (', ' . json_encode($params_array)) : '';

					// Fire event
					parent::success(__('Fathom event added to queue', 'ws-form'), array(

						array(

							'action' 		=> $this->id,
							'type' 			=> $this->type,
							'parse_values' 	=> array(

								'event' => WS_Form_Common::parse_variables_process($this->fathom_event, $form, $submit, 'text/plain'),
								'params' => $params
							)
						)
					));

					break;

				case 'bento' :

					// Build payload
					$bento_payload = array();

					if(!empty($this->bento_payload) && is_array($this->bento_payload)) {

						foreach($this->bento_payload as $payload) {

							// Checks
							if(!isset($payload['action_' . $this->id . '_bento_key'])) { continue; }
							if($payload['action_' . $this->id . '_bento_key'] == '') { continue; }
							if(!isset($payload['action_' . $this->id . '_bento_value'])) { continue; }

							$bento_payload[$payload['action_' . $this->id . '_bento_key']] = WS_Form_Common::parse_variables_process($payload['action_' . $this->id . '_bento_value'], $form, $submit, 'text/plain');
						}
					}

					// Build parse values
					$params_array = array();

					if(
						!empty($this->bento_name) &&
						(in_array($this->bento_method, array('identify', 'track', 'tag'), true))
					) {
						$params_array[] = sprintf('"%s"', esc_attr(WS_Form_Common::parse_variables_process($this->bento_name, $form, $submit, 'text/plain')));
					}
					if(!empty($bento_payload)) { $params_array[] = json_encode($bento_payload); }

					$params = (count($params_array) > 0) ? implode(', ', $params_array) : '';

					// Fire event
					parent::success(__('Bento event added to queue', 'ws-form'), array(

						array(

							'action' 		=> $this->id,
							'type' 			=> $this->type,
							'parse_values' 	=> array(

								'method' => $this->bento_method,
								'params' => $params
							)
						)
					));

					break;

				case 'facebook_standard' :

					// Build params
					$params_array = array();

					if(!empty($this->fb_standard_properties) && is_array($this->fb_standard_properties)) {

						foreach($this->fb_standard_properties as $fb_standard_property) {

							// Checks
							if(!isset($fb_standard_property['action_' . $this->id . '_fb_standard_property_key'])) { continue; }
							if($fb_standard_property['action_' . $this->id . '_fb_standard_property_key'] == '') { continue; }
							if(!isset($fb_standard_property['action_' . $this->id . '_fb_standard_property_value'])) { continue; }

							$params_array[] = $fb_standard_property['action_' . $this->id . '_fb_standard_property_key'] . ": '" . WS_Form_Common::parse_variables_process($fb_standard_property['action_' . $this->id . '_fb_standard_property_value'], $form, $submit, 'text/plain') . "'";
						}
					}
					$params = (count($params_array) > 0) ? (', {' . implode(', ', $params_array) . '}') : '';

					// Fire event
					parent::success(__('Facebook (Standard) conversion tag added to queue.', 'ws-form'), array(

						array(

							'action'		=> $this->id,
							'type' 			=> $this->type,
							'parse_values' 	=> array(

								'event' 	=> $this->fb_standard_event,
								'params' 	=> $params
							)
						)
					));

					break;

				case 'facebook_custom' :

					// Build params
					$params_array = array();

					if(!empty($this->fb_custom_properties) && is_array($this->fb_custom_properties)) {

						foreach($this->fb_custom_properties as $fb_custom_property) {

							// Checks
							if(!isset($fb_custom_property['action_' . $this->id . '_fb_custom_property_key'])) { continue; }
							if($fb_custom_property['action_' . $this->id . '_fb_custom_property_key'] == '') { continue; }
							if(!isset($fb_custom_property['action_' . $this->id . '_fb_custom_property_value'])) { continue; }

							$params_array[] = $fb_custom_property['action_' . $this->id . '_fb_custom_property_key'] . ": '" . WS_Form_Common::parse_variables_process($fb_custom_property['action_' . $this->id . '_fb_custom_property_value'], $form, $submit, 'text/plain') . "'";
						}
					}
					$params = (count($params_array) > 0) ? (', {' . implode(', ', $params_array) . '}') : '';

					// Fire event
					parent::success(__('Facebook (Custom) conversion tag added to queue.', 'ws-form'), array(

						array(

							'action'		=> $this->id,
							'type' 			=> $this->type,
							'parse_values' 	=> array(

								'event' 	=> $this->fb_custom_event,
								'params' 	=> $params
							)
						)
					));

					break;

				case 'linkedin' :

					// Build params
					$params_array = array();

					// Fire event
					parent::success(__('LinkedIn conversion pixel added to queue.', 'ws-form'), array(

						array(

							'action'		=> $this->id,
							'type' 			=> $this->type,
							'parse_values' 	=> array(

								'conversion_id' => $this->linkedin_conversion_id
							)
						)
					));

					break;

				default :

					// Invalid type
					parent::error(__('No conversion tag type in action configuration.', 'ws-form'));
			}
		}

		public function load_config($config) {

			$this->type = parent::get_config($config, 'action_' . $this->id . '_type');

			// Google Analytics
			$this->tag_action = parent::get_config($config, 'action_' . $this->id . '_tag_action');
			$this->tag_category = parent::get_config($config, 'action_' . $this->id . '_tag_category');
			$this->tag_label = parent::get_config($config, 'action_' . $this->id . '_tag_label');
			$this->tag_value = parent::get_config($config, 'action_' . $this->id . '_tag_value');

			// Data Layer
			$this->data_layer_variables = parent::get_config($config, 'action_' . $this->id . '_data_layer_variables');

			// Fathom
			$this->fathom_event = parent::get_config($config, 'action_' . $this->id . '_fathom_event');
			$this->fathom_cents = parent::get_config($config, 'action_' . $this->id . '_fathom_cents');
			$this->fathom_site_id = parent::get_config($config, 'action_' . $this->id . '_fathom_site_id');

			// Bento
			$this->bento_method = parent::get_config($config, 'action_' . $this->id . '_bento_method');
			$this->bento_name = parent::get_config($config, 'action_' . $this->id . '_bento_name');
			$this->bento_payload = parent::get_config($config, 'action_' . $this->id . '_bento_payload');

			// Facebook - Standard
			$this->fb_standard_event = parent::get_config($config, 'action_' . $this->id . '_fb_standard_event');
			$this->fb_standard_properties = parent::get_config($config, 'action_' . $this->id . '_fb_standard_properties');

			// Facebook - Custom
			$this->fb_custom_event = parent::get_config($config, 'action_' . $this->id . '_fb_custom_event');
			$this->fb_custom_properties = parent::get_config($config, 'action_' . $this->id . '_fb_custom_properties');

			// LinkedIn
			$this->linkedin_conversion_id = parent::get_config($config, 'action_' . $this->id . '_linkedin_conversion_id');
		}

		// Get settings
		public function get_action_settings() {

			$settings = array(

				'meta_keys'		=> array(

					'action_' . $this->id . '_type',

					// Google Analytics
					'action_' . $this->id . '_tag_action',
					'action_' . $this->id . '_tag_category',
					'action_' . $this->id . '_tag_label',
					'action_' . $this->id . '_tag_value',

					// Data Layer
					'action_' . $this->id . '_data_layer_variables',

					// Fathom
					'action_' . $this->id . '_fathom_event',
					'action_' . $this->id . '_fathom_cents',
					'action_' . $this->id . '_fathom_site_id',

					// Bento
					'action_' . $this->id . '_bento_method',
					'action_' . $this->id . '_bento_name',
					'action_' . $this->id . '_bento_payload',

					// Facebook - Standard
					'action_' . $this->id . '_fb_standard_event',
					'action_' . $this->id . '_fb_standard_properties',

					// Facebook - Custom
					'action_' . $this->id . '_fb_custom_event',
					'action_' . $this->id . '_fb_custom_properties',

					// LinkedIn
					'action_' . $this->id . '_linkedin_conversion_id'
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

					'label'						=>	__('Type', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => 'data_layer', 'text' => __('Google Tag Manager (Data Layer)', 'ws-form')),
						array('value' => 'google', 'text' => __('Google Analytics', 'ws-form')),
						array('value' => 'fathom', 'text' => __('Fathom Analytics', 'ws-form')),
						array('value' => 'bento', 'text' => __('Bento', 'ws-form')),
						array('value' => 'facebook_standard', 'text' => __('Facebook (Standard)', 'ws-form')),
						array('value' => 'facebook_custom', 'text' => __('Facebook (Custom)', 'ws-form')),
						array('value' => 'linkedin', 'text' => __('LinkedIn (Insight Tag)', 'ws-form')),
					),
					'default'					=>	'data_layer'
				),

				// Tag action
				'action_' . $this->id . '_tag_action'	=> array(

					'label'			=>	__('Event Name (Action)', 'ws-form'),
					'type'			=>	'text',
					'help'			=>	__('e.g. Submitted', 'ws-form'),
					'variable_helper'	=>	true,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'google'
						)
					)
				),

				// Tag category
				'action_' . $this->id . '_tag_category'	=> array(

					'label'			=>	__('Event Category', 'ws-form'),
					'type'			=>	'text',
					'help'			=>	__('e.g. Form - #form_label', 'ws-form'),
					'variable_helper'	=>	true,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'google'
						)
					)
				),

				// Tag label
				'action_' . $this->id . '_tag_label'	=> array(

					'label'			=>	__('Event Label', 'ws-form'),
					'type'			=>	'text',
					'help'			=>	__('Leave blank for none.', 'ws-form'),
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'google'
						)
					)
				),

				// Tag value
				'action_' . $this->id . '_tag_value'	=> array(

					'label'			=>	__('Event Value', 'ws-form'),
					'type'			=>	'text',
					'help'			=>	__('Numeric positive integer. Leave blank for none.', 'ws-form'),
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'google'
						)
					)
				),

				// Data layer - Variables
				'action_' . $this->id . '_data_layer_variables'	=> array(

					'label'				=>	__('Data Layer Variables', 'ws-form'),
					'type'				=>	'repeater',
					'help'				=>	__('Add variables to the data layer. WS Form variables can be added to the value column.', 'ws-form'),
					'meta_keys'			=>	array(

						'action_' . $this->id . '_dl_var_key',
						'action_' . $this->id . '_dl_var_value'
					),
					'default'			=>	array(),
					'variable_helper'	=> true,
					'condition'			=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'google'
						),

						array(

							'logic'				=>	'==',
							'logic_previous'	=>	'||',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'data_layer'
						)
					)
				),

				// Data layer - Variables - Key
				'action_' . $this->id . '_dl_var_key'	=> array(

					'label'			=>	__('Key', 'ws-form'),
					'type'			=>	'text'
				),

				// Data layer - Variables - Value
				'action_' . $this->id . '_dl_var_value'	=> array(

					'label'			=>	__('Value', 'ws-form'),
					'type'			=>	'text'
				),

				// Fathom - Event code
				'action_' . $this->id . '_fathom_event'	=> array(

					'label'			=>	__('Event Name', 'ws-form'),
					'type'			=>	'text',
					'help'			=>	sprintf(

						'%s <a href="%s" target="_blank">%s</a>',
						__('The Fathom event name.', 'ws-form'),
						esc_attr(WS_Form_Common::get_plugin_website_url('/knowledgebase/fathom/')),
						__('Learn more', 'ws-form')
					),
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'fathom'
						)
					)
				),

				// Fathom - Cents
				'action_' . $this->id . '_fathom_cents'	=> array(

					'label'			=>	__('Cents (Optional)', 'ws-form'),
					'type'			=>	'text',
					'help'			=>	__('Monetary value of event in cents, e.g. 100 = $1.', 'ws-form'),
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'fathom'
						)
					)
				),

				// Fathom - Site ID
				'action_' . $this->id . '_fathom_site_id'	=> array(

					'label'			=>	__('Site ID (Optional)', 'ws-form'),
					'type'			=>	'text',
					'help'			=>	__('The Fathom site ID.', 'ws-form'),
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'fathom'
						)
					)
				),

				// Bento - Method
				'action_' . $this->id . '_bento_method'	=> array(

					'label'			=>	__('Method', 'ws-form'),
					'type'			=>	'select',
					'options'		=>	array(

						array('value' => 'identify', 'text' => __('Identify (Email)', 'ws-form')),
						array('value' => 'updateFields', 'text' => __('Update Fields', 'ws-form')),
						array('value' => 'track', 'text' => __('Track', 'ws-form')),
						array('value' => 'tag', 'text' => __('Tag', 'ws-form')),
					),
					'default'		=>	'identify',
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'bento'
						)
					)
				),

				// Bento - Name
				'action_' . $this->id . '_bento_name'	=> array(

					'label'			=>	__('Value / Name', 'ws-form'),
					'type'			=>	'text',
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'bento'
						),

						array(

							'logic_previous'	=>	'&&',
							'logic'				=>	'!=',
							'meta_key'			=>	'action_' . $this->id . '_bento_method',
							'meta_value'		=>	'updateFields'
						)
					)
				),

				// Bento - Payload
				'action_' . $this->id . '_bento_payload'	=> array(

					'label'				=>	__('Payload', 'ws-form'),
					'type'				=>	'repeater',
					'help'				=>	__('Add key value pairs to the payload. WS Form variables can be added to the value column.', 'ws-form'),
					'meta_keys'			=>	array(

						'action_' . $this->id . '_bento_key',
						'action_' . $this->id . '_bento_value'
					),
					'default'			=>	array(),
					'variable_helper'	=>	true,
					'condition'			=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'bento'
						),

						array(

							'logic_previous'	=>	'&&',
							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_bento_method',
							'meta_value'		=>	'updateFields'
						),

						array(

							'logic_previous'	=>	'||',
							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_bento_method',
							'meta_value'		=>	'track'
						)
					)
				),

				// Bento - Payload - Key
				'action_' . $this->id . '_bento_key'	=> array(

					'label'			=>	__('Key', 'ws-form'),
					'type'			=>	'text'
				),

				// Bento - Payload - Value
				'action_' . $this->id . '_bento_value'	=> array(

					'label'			=>	__('Value', 'ws-form'),
					'type'			=>	'text'
				),

				// Facebook - Standard - Event name
				'action_' . $this->id . '_fb_standard_event'	=> array(

					'label'			=>	__('Event Name', 'ws-form'),
					'type'			=>	'select',
					'options'		=>	array(

						array('value' => 'AddPaymentInfo', 'text' => __('Add Payment Info', 'ws-form')),
						array('value' => 'AddToCart', 'text' => __('Add To Cart', 'ws-form')),
						array('value' => 'AddToWishlist', 'text' => __('Add To Wish list', 'ws-form')),
						array('value' => 'CompleteRegistration', 'text' => __('Complete Registration', 'ws-form')),
						array('value' => 'Contact', 'text' => __('Contact', 'ws-form')),
						array('value' => 'CustomizeProduct', 'text' => __('Customize Product', 'ws-form')),
						array('value' => 'Donate', 'text' => __('Donate', 'ws-form')),
						array('value' => 'FindLocation', 'text' => __('Find Location', 'ws-form')),
						array('value' => 'InitiateCheckout', 'text' => __('Initiate Checkout', 'ws-form')),
						array('value' => 'Lead', 'text' => __('Lead', 'ws-form')),
						array('value' => 'PageView', 'text' => __('Page View', 'ws-form')),
						array('value' => 'Purchase', 'text' => __('Purchase', 'ws-form')),
						array('value' => 'Schedule', 'text' => __('Schedule', 'ws-form')),
						array('value' => 'Search', 'text' => __('Search', 'ws-form')),
						array('value' => 'StartTrial', 'text' => __('Start Trial', 'ws-form')),
						array('value' => 'SubmitApplication', 'text' => __('Submit Application', 'ws-form')),
						array('value' => 'Subscribe', 'text' => __('Subscribe', 'ws-form')),
						array('value' => 'ViewContent', 'text' => __('View Content', 'ws-form'))
					),
					'default'		=>	'',
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'facebook_standard'
						)
					)
				),

				// Facebook - Standard - Object properties
				'action_' . $this->id . '_fb_standard_properties'	=> array(

					'label'			=>	__('Object Properties', 'ws-form'),
					'type'			=>	'repeater',
					'help'			=>	__('Add properties to the Facebook event.', 'ws-form'),
					'meta_keys'		=>	array(

						'action_' . $this->id . '_fb_standard_property_key',
						'action_' . $this->id . '_fb_standard_property_value'
					),
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'facebook_standard'
						)
					)
				),

				// Facebook - Standard - Object property key
				'action_' . $this->id . '_fb_standard_property_key'	=> array(

					'label'			=>	__('Key', 'ws-form'),
					'type'			=>	'select',
					'options'		=>	array(

						array('value' => 'content_category', 'text' => 'content_category'),
						array('value' => 'content_ids', 'text' => 'content_ids'),
						array('value' => 'content_name', 'text' => 'content_name'),
						array('value' => 'content_type', 'text' => 'content_type'),
						array('value' => 'contents', 'text' => 'contents'),
						array('value' => 'currency', 'text' => 'currency'),
						array('value' => 'num_items', 'text' => 'num_items'),
						array('value' => 'predicted_ltv', 'text' => 'predicted_ltv'),
						array('value' => 'search_string', 'text' => 'search_string'),
						array('value' => 'status', 'text' => 'status'),
						array('value' => 'value', 'text' => 'value'),
					),
					'default'		=>	'',
				),

				// Facebook - Standard - Object property value
				'action_' . $this->id . '_fb_standard_property_value'	=> array(

					'label'			=>	__('Value', 'ws-form'),
					'type'			=>	'text'
				),

				// Facebook - Custom - Object property value
				'action_' . $this->id . '_fb_custom_event'	=> array(

					'label'			=>	__('Event Name', 'ws-form'),
					'type'			=>	'text',
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'facebook_custom'
						)
					)
				),

				// Facebook - Standard - Object properties
				'action_' . $this->id . '_fb_custom_properties'	=> array(

					'label'			=>	__('Object Properties', 'ws-form'),
					'type'			=>	'repeater',
					'help'			=>	__('Add properties to the Facebook event.', 'ws-form'),
					'meta_keys'		=>	array(

						'action_' . $this->id . '_fb_custom_property_key',
						'action_' . $this->id . '_fb_custom_property_value'
					),
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'facebook_custom'
						)
					)
				),

				// Facebook - Custom - Object property key
				'action_' . $this->id . '_fb_custom_property_key'	=> array(

					'label'			=>	__('Key', 'ws-form'),
					'type'			=>	'text'
				),

				// Facebook - Custom - Object property value
				'action_' . $this->id . '_fb_custom_property_value'	=> array(

					'label'			=>	__('Value', 'ws-form'),
					'type'			=>	'text'
				),

				// LinkedIn - Conversion ID
				'action_' . $this->id . '_linkedin_conversion_id'	=> array(

					'label'			=>	__('Conversion ID', 'ws-form'),
					'type'			=>	'text',
					'help'			=>	__('You can find this value in your event-specific pixel code.'),
					'variable_helper'	=>	true,
					'condition'		=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'action_' . $this->id . '_type',
							'meta_value'		=>	'linkedin'
						)
					)
				)
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}
	}

	new WS_Form_Action_Conversion();
