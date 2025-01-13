<?php

	// Register Elementor widget
	class Elementor_WS_Form_Widget extends \Elementor\Widget_Base {

		public $is_edit_mode;
		public $is_preview_mode;
//		public $is_preview;

		public function __construct($data = array(), $args = null) {

			parent::__construct($data, $args);

			// is_edit_mode is true if we are at the outer editor level
			$this->is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

			// is_preview_mode is true if we are at the inner editor level (Preview iframe)
			$this->is_preview_mode = \Elementor\Plugin::$instance->preview->is_preview_mode();

			// is_preview() is true if we are previewing the page (i.e. eye icon clicked)
//			$this->is_preview = is_preview();

			if($this->is_preview_mode) {

				wp_register_script('wsf-elementor', WS_FORM_PLUGIN_DIR_URL . 'includes/third-party/elementor/elementor-editor-preview.js', array('elementor-frontend'), WS_FORM_VERSION, true);
				wp_register_style('wsf-elementor-css', WS_FORM_PLUGIN_DIR_URL . 'includes/third-party/elementor/elementor-editor-preview.css', array(), WS_FORM_VERSION, 'all');

			} else {

				if(!is_admin()) {

					wp_register_script('wsf-elementor-public', WS_FORM_PLUGIN_DIR_URL . 'includes/third-party/elementor/elementor-public.js', array('elementor-frontend'), WS_FORM_VERSION, true);
				}
			}
		}

		public function get_script_depends() {

			if($this->is_preview_mode) {

				return array('wsf-elementor');

			} else {

				if(!is_admin()) {

					return array('wsf-elementor-public');
				}
			}

			return array();
		}

		public function get_style_depends() {

			if($this->is_preview_mode) {

				return array('wsf-elementor-css');

			} else {

				return array();
			}
		}

		public function get_name() {

			return 'ws-form';
		}

		public function get_title() {

			return WS_FORM_NAME_PRESENTABLE;
		}

		public function get_icon() {

			return 'eicon-form-horizontal';
		}

		public function get_categories() {

			return array('basic');
		}

		protected function register_controls() {

			$this->start_controls_section(

				'form_section',

				array(

					'label' => WS_FORM_NAME_GENERIC,
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(

				'form_id',

				array(

					'label' => __( 'Form', 'ws-form' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => WS_Form_Common::get_forms_array(),
					'label_block' => true
				)
			);

			$this->end_controls_section();
		}

		protected function render() {

			$settings = $this->get_settings_for_display();

			// Get form ID
			$form_id = isset($settings['form_id']) ? absint($settings['form_id']) : 0;

			// Check if form has been selected
			if($form_id > 0) {

				if($this->is_edit_mode) {

					echo sprintf('<div style="min-height:42px">%s</div>', do_shortcode(sprintf('[%s id="%u" visual_builder="true"]', WS_FORM_SHORTCODE, $form_id)));	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

				} else {

					echo do_shortcode(sprintf('[%s id="%u"]', WS_FORM_SHORTCODE, $form_id));	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				}

			} else {

				if($this->is_edit_mode) {
?>
<div class="wsf-elementor-form-selector">
<?php
					// Output WS Form SVG Logo
					WS_Form_Common::echo_logo();

					// Get forms
					$forms = WS_Form_Common::get_forms_array();
?>
<select class="wsf-field">
<?php
					foreach($forms as $form_id => $form_label) {

?><option value="<?php WS_Form_Common::echo_esc_attr($form_id); ?>"><?php WS_Form_Common::echo_esc_html($form_label); ?></option>
<?php
					}
?>
</select>

</div>
<?php
				}
			}
		}
	}