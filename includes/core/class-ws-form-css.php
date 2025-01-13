<?php

	#[AllowDynamicProperties]
	class WS_Form_CSS {

		public $skins = array();
		public $skin_id = WS_FORM_CSS_SKIN_DEFAULT;
		public $skin = false;
		public $skin_config = array();
		public $skin_label = '';
		public $skin_setting_id_prefix = '';
		public $skin_defaults = array();
		public $skin_option = '';
		public $skin_file = '';

		public $styles = array();
		public $style_id = false;

		// Init
		public function init() {

			// Initial build
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			$css_public_layout = WS_Form_Common::option_get('css_public_layout', '');
			if($css_compile && empty($css_public_layout)) {

				self::rebuild();
			}

			// Force rebuild (e.g. if WS Form or add-on updated)
			if(WS_Form_Common::option_get('css_rebuild') === true) {

				self::rebuild();
			}

			// Actions that recompile CSS
			add_action('wsf_settings_update', array($this, 'build_public_css'));
			add_action('wsf_style_publish', array($this, 'build_public_css'));

			// Customize rebuild
			add_filter('customize_save_response', function($response) {

				if(WS_Form_Common::customizer_enabled()) {

					self::build_public_css();
				}

				return $response;
			});
		}

		// Rebuild
		public function rebuild() {

			// Runs on wp_loaded action to ensure add-on CSS hooks load properly
			add_action('wp_loaded', array($this, 'build_public_css'));
		}

		// Layout
		public function get_layout($css_minify = null, $force_build = false, $rtl = false) {

			// Build CSS
			$css_return = '';

			// Minify
			if(is_null($css_minify)) {

				$css_minify = !SCRIPT_DEBUG;
			}

			// Initial build of compiled files
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			if($css_compile && !$force_build) {

				// RTL suffix
				$rtl_suffix = $rtl ? '_rtl' : '';

				if($css_minify) {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_layout%s_min', $rtl_suffix));

				} else {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_layout%s', $rtl_suffix));
				}

			} else {

				include_once 'css/class-ws-form-css-layout.php';

				$ws_form_css_layout = new WS_Form_CSS_Layout();

				ob_start();

				// Render layout
				$ws_form_css_layout->render_layout();

				$css_return = ob_get_contents();

				ob_end_clean();

				// Apply filters
				$css_return = apply_filters('wsf_css_get_layout', $css_return);

				// Minify?
				$css_return = $css_minify ? self::minify($css_return) : $css_return;
			}

			return $css_return;
		}

		// Style
		public function get_style($css_minify = null, $force_build = false, $rtl = false) {

			// Build CSS
			$css_return = '';

			// Minify
			if(is_null($css_minify)) {

				$css_minify = !SCRIPT_DEBUG;
			}

			// Initial build of compiled files
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			if($css_compile && !$force_build) {

				if($css_minify) {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_style_%u_min', $this->style_id));

				} else {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_style_%u', $this->style_id));
				}

			} else {

				$ws_form_style = new WS_Form_Style();
				$ws_form_style->id = $this->style_id;

				// Get CSS vars markup
				$css_return = $ws_form_style->get_css_vars_markup(true, true, false, true, false, true);

				// Apply filters
				$css_return = apply_filters('wsf_css_get_style', $css_return);

				// Minify?
				$css_return = $css_minify ? self::minify($css_return) : $css_return;
			}

			return $css_return;
		}

		// Email
		public function get_email() {

			include_once 'css/class-ws-form-css-email.php';
			$ws_form_css_email = new WS_Form_CSS_Email();

			return $ws_form_css_email->get_email();
		}

		// Build public CSS files
		public function build_public_css() {

			$css_compile = WS_Form_Common::option_get('css_compile', false);

			if($css_compile) {

				// Build file upload directory
				$upload_dir = WS_Form_Common::upload_dir_create(WS_FORM_CSS_FILE_PATH);
				if($upload_dir['error']) {

					throw new Exception($upload_dir['message']);
				}

				// Get file upload directory
				$dir = $upload_dir['dir'];

				// Build layout CSS
				self::build_public_css_set('get_layout', 'layout', $dir);

				if(WS_Form_Common::styler_enabled()) {

					// Remember old style ID
					$style_id_old = $this->style_id;

					// Get styles
					$ws_form_style = new WS_Form_Style();
					$ws_form_style->check_initialized(true);
					$styles = $ws_form_style->db_read_all('', "NOT (status = 'trash')");

					foreach($styles as $style) {

						// Set style ID
						$this->style_id = $style['id'];

						// Build style CSS
						self::build_public_css_set('get_style', sprintf('style.%s', $this->style_id), $dir);
					}

					// Reset style ID
					$this->style_id = $style_id_old;

				} else {

					// Remember old skin ID
					$skin_id_old = $this->skin_id;

					// Get skins
					self::get_skins();

					// Build skins
					foreach($this->skins as $skin_id => $skin) {

						// Set skin ID
						$this->skin_id = $skin_id;

						// Load skin
						self::skin_load();

						// Build skin CSS
						self::build_public_css_set('get_skin', sprintf('skin%s', $this->skin_file), $dir);
					}

					// Build conversational CSS
					self::build_public_css_set('get_conversational', 'conversational', $dir);

					// Reset skin ID
					$this->skin_id = $skin_id_old;
				}
			}

			// Remove css_rebuild option
			WS_Form_Common::option_remove('css_rebuild');
		}

		public function build_public_css_set($function, $part, $dir) {

			// Build option part
			$part_option = str_replace('.', '_', $part);

			// Build CSS - Normal
			$css = self::{$function}(false, true, false);
			file_put_contents(sprintf('%s/public.%s.css', $dir, $part), $css);
			WS_Form_Common::option_set(sprintf('css_public_%s', $part_option), $css);

			// Build CSS - Minimized
			$css_minimized = self::minify($css);
			$css = null;
			file_put_contents(sprintf('%s/public.%s.min.css', $dir, $part), $css_minimized);
			WS_Form_Common::option_set(sprintf('css_public_%s_min', $part_option), $css_minimized);
			$css_minimized = null;

			if(WS_Form_Common::customizer_enabled()) {

				// Build CSS - RTL
				$css_rtl = self::{$function}(false, true, true);
				file_put_contents(sprintf('%s/public.%s.rtl.css', $dir, $part), $css_rtl);
				WS_Form_Common::option_set(sprintf('css_public_%s_rtl', $part_option), $css_rtl);

				// Build CSS - RTL - Minimized
				$css_rtl_minimized = self::minify($css_rtl);
				$css_rtl = null;
				file_put_contents(sprintf('%s/public.%s.rtl.min.css', $dir, $part), $css_rtl_minimized);
				WS_Form_Common::option_set(sprintf('css_public_%s_rtl_min', $part_option), $css_rtl_minimized);
				$css_rtl_minimized = null;
			}
		}

		public function minify($css) {

			// Basic minify
			$css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css);
			$css = preg_replace('/\s{2,}/', ' ', $css);
			$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
			$css = preg_replace('/;}/', '}', $css);
			$css = str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),"",$css);

			return $css;
		}

		// Escape CSS values
		public function e($css_value) {

			WS_Form_Common::echo_esc_css($css_value);
		}

		// Escape CSS color values for SVG
		public function c($css_value) {

			WS_Form_Common::echo_urlencode($css_value);
		}

		// Legacy

		// Get skins
		public function get_skins() {

			if(count($this->skins) > 0) { return $this->skins; }

			// Get skins
			$this->skins = WS_Form_Config::get_skins(WS_FORM_EDITION == 'pro');

			// Skin override (Used by customize feature)
			$skin_id_override = WS_Form_Common::get_query_var('wsf_skin_id', '', false, false, true, 'GET');
			if($skin_id_override) {

				// Get all skin IDs
				$skin_ids = array_keys($this->skins);

				// Check skin ID override is valid
				if(!in_array($skin_id_override, $skin_ids)) { $skin_id_override = WS_FORM_CSS_SKIN_DEFAULT; }

				$this->skin_id = $skin_id_override;
			}

			return $this->skins;
		}

		// Skin
		public function get_skin($css_minify = null, $force_build = false, $rtl = false) {

			// Build CSS
			$css_return = '';

			// Minify
			if(is_null($css_minify)) {

				$css_minify = !SCRIPT_DEBUG;
			}

			// Initial build of compiled files
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			if($css_compile && !$force_build) {

				// Load skin
				self::skin_load();

				// RTL suffix
				$rtl_suffix = $rtl ? '_rtl' : '';

				if($css_minify) {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_skin%s%s_min', $this->skin_option, $rtl_suffix));

				} else {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_skin%s%s', $this->skin_option, $rtl_suffix));
				}

			} else {

				include_once 'css/class-ws-form-css-skin.php';

				$ws_form_css_skin = new WS_Form_CSS_Skin();
				$ws_form_css_skin->skin_id = $this->skin_id;

				ob_start();

				// Render skin
				$ws_form_css_skin->render_skin();

				if($rtl) {

					// Render RTL skin
					$ws_form_css_skin->render_skin_rtl();
				}

				$css_return = ob_get_contents();

				ob_end_clean();

				// Apply filters
				$css_return = apply_filters('wsf_get_skin', $css_return);		// Legacy
				$css_return = apply_filters('wsf_css_get_skin', $css_return);	// New

				// Minify?
				$css_return = $css_minify ? self::minify($css_return) : $css_return;
			}

			return $css_return;
		}

		// Load skin
		public function skin_load() {

			// Get skin
			$this->skin_id = apply_filters('wsf_css_skin_id', $this->skin_id);

			// Get skins
			self::get_skins();

			// Check skin config
			if(!isset($this->skins[$this->skin_id])) { throw new ErrorException(__('Invalid skin ID', 'ws-form')); }			

			// Load config
			$this->skin_config = $this->skins[$this->skin_id];

			// Label
			$this->skin_label = $this->skin_config['label'];

			// Setting ID prefix
			$this->skin_setting_id_prefix = $this->skin_config['setting_id_prefix'];

			// Setting defaults
			$this->skin_defaults = $this->skin_config['defaults'];

			// Set skin option
			$this->skin_option = ($this->skin_setting_id_prefix != '') ? '_' . $this->skin_setting_id_prefix : '';

			// Set skin file
			$this->skin_file = ($this->skin_setting_id_prefix != '') ? '.' . $this->skin_setting_id_prefix : '';
		}

		// Set skin variables
		public function skin_variables() {

			// Set variables
			$enable_cache = !(WS_Form_Common::get_query_var('customize_theme') !== '');

			// Get customize groups
			$customize_groups = WS_Form_Config::get_customize();

			foreach($customize_groups as $customize_group) {

				foreach($customize_group['fields'] as $meta_key => $config) {

					$this->{$meta_key} = WS_Form_Common::option_get('skin' . $this->skin_option . '_' . $meta_key, null, false, $enable_cache, true);
					if(is_null($this->{$meta_key})) { $this->{$meta_key} = isset($this->skin_defaults[$meta_key]) ? $this->skin_defaults[$meta_key] : ''; }
				}
			}
		}

		// Set option defaults
		public function option_set_defaults() {

			// Get skins
			self::get_skins();

			foreach($this->skins as $skin_id => $skin) {

				$this->skin_id = $skin_id;
				$this->skin_load();

				// Set up customizer options with default values
				foreach($this->skin_defaults as $meta_key => $meta_value) {

					WS_Form_Common::option_set('skin' . $this->skin_option . '_' . $meta_key, $meta_value, false);
				}
			}
		}

		// Set color shades
		public function skin_color_shades() {

			// Default
			$this->color_default_lightest_dark_10 = WS_Form_Color::hex_color_darken_percentage($this->color_default_lightest, 10);
			$this->color_default_lightest_dark_20 = WS_Form_Color::hex_color_darken_percentage($this->color_default_lightest, 20);
			$this->color_default_lighter_dark_10 = WS_Form_Color::hex_color_darken_percentage($this->color_default_lighter, 10);
			$this->color_default_lighter_dark_20 = WS_Form_Color::hex_color_darken_percentage($this->color_default_lighter, 20);

			// Primary
			$this->color_primary_dark_10 = WS_Form_Color::hex_color_darken_percentage($this->color_primary, 10);
			$this->color_primary_dark_20 = WS_Form_Color::hex_color_darken_percentage($this->color_primary, 20);

			// Secondary
			$this->color_secondary_dark_10 = WS_Form_Color::hex_color_darken_percentage($this->color_secondary, 10);
			$this->color_secondary_dark_20 = WS_Form_Color::hex_color_darken_percentage($this->color_secondary, 20);

			// Success
			self::skin_color_shade_set('success', $this->color_success);

			// Information
			self::skin_color_shade_set('information', $this->color_information);

			// Warning
			self::skin_color_shade_set('warning', $this->color_warning);

			// Danger
			self::skin_color_shade_set('danger', $this->color_danger);
		}

		// Skin color shade set
		public function skin_color_shade_set($name, $color) {

			$this->{sprintf('color_%s_light_40', $name)} = WS_Form_Color::hex_color_lighten_percentage($color, 40);
			$this->{sprintf('color_%s_light_85', $name)} = WS_Form_Color::hex_color_lighten_percentage($color, 85);
			$this->{sprintf('color_%s_dark_10', $name)} = WS_Form_Color::hex_color_darken_percentage($color, 10);
			$this->{sprintf('color_%s_dark_20', $name)} = WS_Form_Color::hex_color_darken_percentage($color, 20);
			$this->{sprintf('color_%s_dark_40', $name)} = WS_Form_Color::hex_color_darken_percentage($color, 40);
			$this->{sprintf('color_%s_dark_60', $name)} = WS_Form_Color::hex_color_darken_percentage($color, 60);
		}

		// Admin
		public function get_admin() {

			include_once 'css/class-ws-form-css-admin.php';
			$ws_form_css_admin = new WS_Form_CSS_Admin();
			return $ws_form_css_admin->get_admin();
		}

		// Conversational
		public function get_conversational($css_minify = null, $force_build = false, $rtl = false) {

			// Build CSS
			$css_return = '';

			// Minify
			if(is_null($css_minify)) {

				$css_minify = !SCRIPT_DEBUG;
			}

			// Initial build of compiled files
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			if($css_compile && !$force_build) {

				// RTL suffix
				$rtl_suffix = $rtl ? '_rtl' : '';

				if($css_minify) {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_conversational%s_min', $rtl_suffix));

				} else {

					$css_return = WS_Form_Common::option_get(sprintf('css_public_conversational%s', $rtl_suffix));
				}

			} else {

				include_once 'css/class-ws-form-css-conversational.php';

				$ws_form_css_conversational = new WS_Form_CSS_Conversational();

				ob_start();

				// Render skin
				$ws_form_css_conversational->render_conversational();

				if($rtl) {

					// Render RTL skin
					$ws_form_css_conversational->render_conversational_rtl();
				}

				$css_return = ob_get_contents();

				ob_end_clean();

				// Apply filters
				$css_return = apply_filters('wsf_css_get_conversational', $css_return);

				// Minify?
				$css_return = $css_minify ? self::minify($css_return) : $css_return;
			}
			return $css_return;
		}
	}
