<?php

	class WS_Form_Action_Hook extends WS_Form_Action {

		public $id = 'hook';
		public $pro_required = false;
		public $label;
		public $label_action;
		public $events;
		public $multiple = true;
		public $configured = true;
		public $can_repost = true;
		public $form_add = false;

		// Config
		public $type;
		public $hook;
		public $priority = 190;

		public function __construct() {

			// Set label
			$this->label = __('Run Hook', 'ws-form');

			// Set label for actions pull down
			$this->label_action = __('Run WordPress Hook', 'ws-form');

			// Events
			$this->events = array('submit');

			// Register action
			parent::register($this);

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);
		}

		public function get_priority($config) {

			// Load config
			self::load_config($config);

			return $this->priority;			
		}

		public function post($form, &$submit, $config) {

			// Load config
			self::load_config($config);

			// Check hook
			if($this->hook !== '') {

				// Run hook
				switch($this->type) {

					case 'apply_filter' :

						$filter_return = apply_filters($this->hook, $form, $submit);

						// If false is returned, halt action processing
						if($filter_return === false) { return 'halt'; }
						if($filter_return === 'halt') { return 'halt'; }

						// Success
						parent::success(sprintf(__('Hook successfully called: %s', 'ws-form'), $this->type . "('" . $this->hook . "', \$form, \$submit)"));

						// Process return types

						// Object = Set submit object
						if(is_object($filter_return) && (get_class($filter_return) == 'submit')) {

							// Adjust submit data
							$submit = $filter_return;

						// Array = Process return actions
						} else if(is_array($filter_return)) {

							return parent::process_return_actions($filter_return, $form, $submit);

						// Numeric = Set spam level
						} else if(is_numeric($filter_return)) {

							// Set spam level on submit record
							if(is_null(parent::$spam_level) || (parent::$spam_level < $filter_return)) {

								parent::$spam_level = $filter_return;
							}

							return $filter_return;
						}

						break;

					case 'do_action' :

						do_action($this->hook, $form, $submit);

						// Success
						parent::success(sprintf(__('Hook successfully called: %s', 'ws-form'), $this->type . "('" . $this->hook . "', \$form, \$submit)"));

						break;

					default :

						// Invalid type
						parent::error(__('No type in action configuration', 'ws-form'));

						return 'halt';
				}

			} else {

				// Invalid hook
				parent::error(__('No hook tag in action configuration', 'ws-form'));

				return 'halt';
			}
		}

		public function load_config($config) {

			$this->type = parent::get_config($config, 'action_' . $this->id . '_type');
			$this->hook = parent::get_config($config, 'action_' . $this->id . '_hook');
			$this->priority = parent::get_config($config, 'action_' . $this->id . '_priority', 190);
			if($this->priority == '') { $this->priority = 10; } else { $this->priority = intval($this->priority); }
		}

		// Get settings
		public function get_action_settings() {

			$settings = array(

				'meta_keys'		=> array(

					'action_' . $this->id . '_type',
					'action_' . $this->id . '_hook',
					'action_' . $this->id . '_priority'
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

						array('value' => 'apply_filter', 'text' => __('Filter - apply_filters($hook_tag, $form, $submit)', 'ws-form')),
						array('value' => 'do_action', 'text' => __('Action - do_action($hook_tag, $form, $submit)', 'ws-form'))
					),
					'default'					=>	'apply_filter'
				),

				// Hook
				'action_' . $this->id . '_hook'	=> array(

					'label'		=>	__('Hook Tag', 'ws-form'),
					'type'		=>	'text',
					'help'		=>	__('Tag name of the hook.', 'ws-form'),
				),

				// Priority
				'action_' . $this->id . '_priority'	=> array(

					'label'		=>	__('Priority', 'ws-form'),
					'type'		=>	'select',
					'options'					=>	array(

						array('value' => '-10', 'text' => __('Before submission created', 'ws-form')),
						array('value' => '10', 'text' => __('Before other actions', 'ws-form')),
						array('value' => '190', 'text' => __('After other actions', 'ws-form'))
					),
					'default'	=>	190,
					'help'		=>	__('Specify when the hook should run.', 'ws-form')
				)
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}
	}

	new WS_Form_Action_Hook();
