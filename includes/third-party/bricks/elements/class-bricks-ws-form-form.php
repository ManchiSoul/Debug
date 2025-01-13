<?php

	class Bricks_WS_Form_Form extends \Bricks\Element {

		// Element properties
		public $category     = 'ws-form';
		public $name         = 'ws-form-form';
		public $icon         = 'ti-view-list';
		public $css_selector = '';

		public function __construct($element = null) {

			if(bricks_is_builder()) {

				$this->scripts = ['wsf_form_init'];
			}

			parent::__construct($element);
		}

		public function get_label() {

			return __('WS Form', 'ws-form');
		}

		public function set_controls() {

			// Form ID
			$this->controls['form-id'] = [

				'tab' => 'content',
				'label' => __( 'Form', 'ws-form' ),
				'type' => 'select', 
				'options' => WS_Form_Common::get_forms_array(false),
				'placeholder' => __( 'Select form or enter form ID below...', 'ws-form' ),
			];

			// Form ID
			$this->controls['form-dynamic-id'] = [

				'tab' => 'content',
				'label' => __( 'Form ID (Optional)', 'ws-form' ),
				'type' => 'text',
				'info' => __( 'Enter the form ID to render. For example: 123 or {dynamic_data}', 'ws-form' ),
				'required' => [ 'form-id', '=', '' ]
			];

			// Form element ID
			$this->controls['form-element-id'] = [

				'tab' => 'content',
				'label' => __( 'Form Element ID (Optional)', 'ws-form' ),
				'type' => 'text',
				'info' => __( 'Enter the id attribute of the form element. For example: my-form or {dynamic_data}', 'ws-form' )
			];
		}

		public function render() {

			$settings = $this->settings;

			// Get form ID
			$form_id = absint(isset($settings['form-id']) ? $settings['form-id'] : '');

			// Get form dynamic ID
			$form_dynamic_id = isset($settings['form-dynamic-id']) ? $this->render_dynamic_data($settings['form-dynamic-id']) : '';

			if(
				($form_id === 0) &&
				($form_dynamic_id > 0) 
			) {
				$form_id = $form_dynamic_id;
			}

			// Get form element ID
			$form_element_id = isset($settings['form-element-id']) ? $this->render_dynamic_data($settings['form-element-id']) : '';

			if($form_id > 0) {

				// Check version is 1.4 or greater
				$version_1_4_greater = (WS_Form_Common::version_compare(BRICKS_VERSION, '1.4') >= 0);

				// Wrapper
				if($version_1_4_greater) {

					echo "<div {$this->render_attributes('_root')}>";	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				}

				// Show shortcode
				$shortcode = sprintf('[ws_form id="%u"%s%s]', $form_id, ($form_element_id != '') ? sprintf(' element_id="%s"', esc_attr($form_element_id)) : '', (bricks_is_builder() ? ' visual_builder="true"' : ''));
				echo do_shortcode($shortcode);	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

				// End wrapper
				if($version_1_4_greater) {

					echo '</div>';	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				}

			} else {

				// Show placeholder
				echo $this->render_element_placeholder([	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

					'icon-class'	=> $this->icon,
					'title'			=> esc_html__('No form selected', 'ws-form'),
					'description'	=> esc_html__('Please select a form from the element controls.', 'ws-form'),

					// Legacy attribute
					'text'			=> esc_html__('No form selected', 'ws-form')
				]);
			}
		}
	}
