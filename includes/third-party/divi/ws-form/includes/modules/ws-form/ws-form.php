<?php

	class ET_Builder_Module_WS_Form extends ET_Builder_Module {

		public $slug       = 'ws_form_divi';
		public $vb_support = 'on';
		public $icon_path  = '';

		public function init() {

			// Set name of module
			$this->name = WS_FORM_NAME_GENERIC;

			// Use raw content, do not wpautop it
			$this->use_raw_content = true;

			// Create Form selector
			$this->settings_modal_toggles = array(

				'general'  => array(

					'toggles' => array(

						'ws_form_divi_form_id' => __('Form', 'ws-form')
					)
				)
			);

			// Set icon
			$this->icon_path = plugin_dir_path(__FILE__) . 'icon.svg';
		}

		public function get_advanced_fields_config() {

			// Remove link options
			return array(

				'link_options' => false,
			);
		}

		public function get_fields() {

			// Return field configuration
			return array(

				'form_id'     => array(

					'label'				=> __('Form', 'ws-form'),
					'type'				=> 'select',
					'options'			=> array('0' => __('Select form...', 'ws-form')) + WS_Form_Common::get_forms_array(false),
					'option_category'	=> 'basic_option',
					'description'		=> __('Select the form that you would like to use for this Divi module.', 'ws-form'),
					'toggle_slug'		=> 'ws_form_divi_form_id'
				)
			);
		}

		public function render($unprocessed_props, $content = null, $render_slug = null) {

			$form_id = isset($this->props['form_id']) ? absint($this->props['form_id']) : 0;

			if($form_id > 0) {

				if(
					isset($_GET) && isset($_GET['et_fb'])	// phpcs:ignore WordPress.Security.NonceVerification
				) {

					// Render shortcode (Editor)
					return sprintf('<div style="min-height:42px">%s</div>', do_shortcode(sprintf('[%s id="%u" visual_builder="true"]', WS_FORM_SHORTCODE, $form_id)));

				} else {

					// Render shortcode (Frontend)
					return do_shortcode(sprintf('[%s id="%u"]', WS_FORM_SHORTCODE, $form_id));
				}

			} else {

				// Render placeholder
				return sprintf('<div class="ws-form-divi-no-form-id"><h2>WS Form</h2><p>%s</p></div>', __('Select the form that you would like to use for this Divi module.', 'ws-form'));
			}
		}
	}

	new ET_Builder_Module_WS_Form;
