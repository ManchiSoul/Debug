<?php

	/**
	 * Common functions used by this plugin
	 */
	class WS_Form_Common {

		// Cookie prefix
		const WS_FORM_COOKIE_PREFIX = 'ws_form_';

		// Admin messages
		public static $admin_messages = array();

		// IP lookup response
		public static $ip_lookup_response = false;

		// NONCE verified
		public static $nonce_verified = false;

		// Options cache (Don't use WordPress option caching as it is so slow)
		public static $options = array();

		// Groups cache
		public static $groups = false;

		// Sections cache
		public static $sections = false;

		// Fields cache
		public static $fields = false;

		// Field types cache
		public static $field_types = false;

		// Admin messages - Push
		public static function admin_message_push($message, $type = 'notice-success', $dismissible = true, $nag_notice = true) {

			self::$admin_messages[] = array(

				'message'		=>	$message,
				'type'			=>	$type,
				'dismissible'	=>	$dismissible,
				'nag_notice'	=>	$nag_notice
			);
		}

		// Admin messages - Render
		public static function admin_messages_render() {

			// Server side notices
			foreach(self::$admin_messages as $admin_message) {

				$message = $admin_message['message'];
				$type = isset($admin_message['type']) ? $admin_message['type'] : 'notice-success';
				$dismissible = isset($admin_message['dismissible']) ? $admin_message['dismissible'] : true;
				$nag_notice = isset($admin_message['nag_notice']) ? $admin_message['nag_notice'] : false;

				self::admin_message_render($message, $type, $dismissible, $nag_notice);
			}
		}

		// Admin messages - Render single
		public static function admin_message_render($message, $type = 'notice-success', $dismissible = true, $nag_notice = false, $class = '') {

			if(!(defined('DISABLE_NAG_NOTICES') && DISABLE_NAG_NOTICES && $nag_notice)) {

				// $message may contain HTML
				echo sprintf(		// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

					'<div class="notice %s"><p>%s</p></div>',
					esc_attr($type . ($dismissible ? ' is-dismissible' : '') . ($class ? ' '  . $class : '')),
					str_replace("\n", "<br />\n", $message)
				);
			}
		}

		// Admin messages - Get count
		public static function get_admin_message_count() {

			return count(self::$admin_messages);
		}

		// Wrapper classes
		public static function wrapper_classes() {

			$wrapper_classes_array = array('wrap');

			// Detect if this plugin is being hosted on wordpress.com
			if(
				isset($_SERVER) && // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				isset($_SERVER['HTTP_X_PRESSABLE_PROXY']) && // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				(isset($_SERVER['HTTP_X_PRESSABLE_PROXY']) == 'wordpress')// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitiz	ed
			) {

				$wrapper_classes_array[] = 'wsf-wpcom';
			}

			// Output classes
			self::echo_esc_attr(implode(' ', $wrapper_classes_array));
		}

		// Get option name and options
		public static function get_options($key, $enable_cache) {

			// Default return values
			$option_name = WS_FORM_OPTION_NAME;
			$options = false;

			// Check for action related keys (Disabled by default)
			if(apply_filters('wsf_option_separate_action', WS_FORM_OPTION_SEPARATE_ACTION)) {

				if(strpos($key, 'action_') === 0) {

					$next_underscore = strpos($key, '_', 7);

					if($next_underscore !== false) {

						// Set key prefix
						$key_prefix = substr($key, 0, $next_underscore);

						// Set option name
						$option_name = sprintf('%s_%s', WS_FORM_OPTION_NAME, $key_prefix);

						// Get options
						$options = self::get_options_by_option_name($option_name, $key_prefix, $enable_cache);
					}
				}
			}

			// Check for CSS related keys (Enabled by default)
			if(apply_filters('wsf_option_separate_css', WS_FORM_OPTION_SEPARATE_CSS)) {

				if(strpos($key, 'css_') === 0) {

					// Set key prefix
					$key_prefix = 'css';

					// Set option name
					$option_name = sprintf('%s_%s', WS_FORM_OPTION_NAME, $key_prefix);

					// Get options
					$options = self::get_options_by_option_name($option_name, $key_prefix, $enable_cache);
				}
			}

			// Load options if they haven't been loaded already
			if($options === false) {

				// Check cache
				if($enable_cache && isset(self::$options[$option_name])) {

					$options = self::$options[$option_name];

				} else {

					$options = get_option($option_name, false);

					// Check options
					if(!is_array($options)) { $options = array(); }

					// Cache options
					self::$options[$option_name] = $options;
				}
			}

			// Return option name
			return array('name' => $option_name, 'options' => $options);
		}

		// Migrate check
		public static function get_options_by_option_name($option_name, $key_prefix, $enable_cache) {

			// Check cache
			if($enable_cache && isset(self::$options[$option_name])) {

				return self::$options[$option_name];

			} else {

				// Check if options have already been migrated
				$options = get_option($option_name, false);

				// If no options found, migrate any existing options to the new option name
				if($options === false) {

					$options = self::options_migrate($option_name, $key_prefix);
				}

				// Check options
				if(!is_array($options)) { $options = array(); }

				return $options;
			}
		}

		// Migrate 
		public static function options_migrate($option_name_new, $key_prefix) {

			// Get main options data
			$options = get_option(WS_FORM_OPTION_NAME, false);

			// Build old and new options array
			$options_old = array();
			$options_new = array();

			// If options exist, extract keys that start with key_prefix
			if(is_array($options)) {

				foreach($options as $key => $value) {

					if(strpos($key, $key_prefix) === 0) {

						$options_new[$key] = $value;

					} else {

						$options_old[$key] = $value;
					}
				}

				// Write new options
				if(update_option($option_name_new, $options_new, 'no')) {

					// Cache new options
					self::$options[$option_name_new] = $options_new;

					// Write old options
					update_option(WS_FORM_OPTION_NAME, $options_old, 'no');

					// Cache old options
					self::$options[WS_FORM_OPTION_NAME] = $options_old;
				}
			}

			return $options_new;
		}

		// Get plugin option key value
		public static function option_get($key, $default = false, $default_set = false, $enable_cache = true, $use_default_if_blank = false) {

			// Get option name
			$option_name_return = self::get_options($key, $enable_cache);
			$option_name = $option_name_return['name'];
			$options = $option_name_return['options'];

			// Return default
			$value = $default;

			// If key exists, return the value
			if(isset($options[$key])) {

				$value = $options[$key];

			} else {

				// Set value
				if($default_set) { self::option_set($key, $default); }
			}

			// If value is blank check to see if we should return the default value
			if(($value === '') && $use_default_if_blank) {

				$value = $default;
			}

			return apply_filters('wsf_option_get', $value, $key);
		}

		// Set plugin option key value
		public static function option_set($key, $value, $update = true) {

			// Get option name
			$option_name_return = self::get_options($key, false);
			$option_name = $option_name_return['name'];
			$options = $option_name_return['options'];

			// Set value
			if((isset($options[$key]) && $update) || (!isset($options[$key]))) {

				// Set key to value in options array
				$options[$key] = apply_filters('wsf_option_set', $value, $key);
			}

			// Cache options
			self::$options[$option_name] = $options;

			// Update WordPress option
			update_option($option_name, $options, 'no');
		}

		// Remove plugin option key value
		public static function option_remove($key) {

			// Get option name
			$option_name_return = self::get_options($key, false);
			$option_name = $option_name_return['name'];
			$options = $option_name_return['options'];

			// If key exists, unset it
			if(isset($options[$key])) {

				// Remove key
				unset($options[$key]);

				// Cache options
				self::$options[$option_name] = $options;

				// Update WordPress option
				update_option($option_name, $options, 'no');

				// Key found and removed
				return true;
			}

			// Did not find key
			return false;
		}

		// Force WS Form framework
		public static function option_get_framework_ws_form($value, $key) {

			switch($key) {

				case 'framework' : return 'ws-form';
				case 'css_compile' : return false;
				case 'css_inline' : return false;

				default : return $value;
			}
		}

		// Get admin URL
		public static function get_admin_url($page_slug = '', $item_id = false, $path_extra = '') {

			$page_path = 'admin.php';
			if($page_slug != '') { $page_path .= '?page=' . $page_slug; }
			if($item_id !== false) { $item_id = absint($item_id); $page_path .= '&id=' . $item_id; }
			if($path_extra) { $page_path .= '&' . $path_extra; }

			return admin_url($page_path);
		}

		// Get plugin website link
		public static function get_plugin_website_url($path = '', $medium = false) {

			return sprintf('https://wsform.com%s?utm_source=ws_form_pro%s', $path, (($medium !== false) ? '&utm_medium=' . $medium : ''));
		}

		// Get customize URL
		public static function get_customize_url($panel = 'ws_form', $preview_form_id = 0) {

			return sprintf('customize.php?return=%s&wsf_panel_open=%s%s', urlencode(remove_query_arg(wp_removable_query_args(), wp_unslash($_SERVER['REQUEST_URI']))), $panel, (($preview_form_id > 0) ? sprintf('&wsf_preview_form_id=%u', $preview_form_id) : ''));
		}

		// Get tooltip attributes
		public static function tooltip($title, $position = 'bottom-center') {

			$helper_icon_tooltip = self::option_get('helper_icon_tooltip', true);

			if($helper_icon_tooltip) {

				return sprintf(' data-wsf-tooltip="%s" title="%s"', esc_attr($position), esc_attr($title));

			} else {

				return sprintf(' title="%s"', esc_attr($title));
			}
		}

		// Echo tooltip attribut
		public static function tooltip_e($title, $position = 'bottom-center') {

			// Output is escaped in tooltip method
			echo self::tooltip($title, $position = 'bottom-center');	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		}

		// Output settings attributes
		public static function attributes_e($attributes) {

			if(!is_array($attributes)) { return; }

			foreach($attributes as $key => $value) {

				echo ' ';	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				self::echo_esc_attr($key);
				echo '="';	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				self::echo_esc_attr($value);
				echo '"';	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			}
		}

		// Get query var (NONCE is not available)
		public static function get_query_var($var, $default = '', $parameters = false, $esc_sql = false, $strip_slashes = true, $request_method = false) {

			// REST parameters
			if($parameters !== false) {

				if(isset($parameters[$var])) {

					$return_value = $esc_sql ? esc_sql($parameters[$var]) : $parameters[$var];
					$return_value = self::mod_security_fix($return_value);
					return $strip_slashes ? stripslashes_deep($return_value) : $return_value;
				}
			}

			// Get request method
			$request_method = ($request_method === false) ? self::get_request_method() : $request_method;
			if(!$request_method) { return $default; }

			// Regular GET, POST, PUT handling
			switch($request_method) {

				case 'GET' :

					$post_vars = $_GET;	// phpcs:ignore WordPress.Security.NonceVerification

					break;

				case 'POST' :

					$post_vars = $_POST;	// phpcs:ignore WordPress.Security.NonceVerification

					break;

				case 'PUT' :

					// PUT method data is in php://input so parse that into $post_vars
					parse_str(file_get_contents('php://input'), $post_vars);
					$strip_slashes = false;

					break;

				default :

					return $default;
			}

			// DATA param (This overcomes standard 1000 POST parameter limitation in PHP)
			if(
				isset($post_vars['data'])
			) {

				$data = $strip_slashes ? stripslashes_deep($post_vars['data']) : $post_vars['data'];
				$data = self::mod_security_fix($data);

				$data_array = is_string($data) ? json_decode($data, true) : array();

				if(isset($data_array[$var])) { return $data_array[$var]; }
			}

			// Get return value
			$return_value = isset($post_vars[$var]) ? ($esc_sql ? esc_sql($post_vars[$var]) : $post_vars[$var]) : $default;
			$return_value = self::mod_security_fix($return_value);
			return $strip_slashes ? stripslashes_deep($return_value) : $return_value;
		}

		// Get request var 
		public static function get_query_var_nonce($var, $default = '', $parameters = false, $esc_sql = false, $strip_slashes = true, $request_method_required = false, $request_method = false) {

			// REST parameters
			if($parameters !== false) {

				if(isset($parameters[$var])) {

					$return_value = $esc_sql ? esc_sql($parameters[$var]) : $parameters[$var];
					$return_value = self::mod_security_fix($return_value);
					return $strip_slashes ? stripslashes_deep($return_value) : $return_value;
				}
			}

			// Get from standard _GET _POST arrays
			$request_method = ($request_method === false) ? self::get_request_method() : $request_method;
			if(!$request_method) { return $default; }
			if(
				($request_method_required !== false) &&
				($request_method_required !== $request_method)
			) {

				return $default;
			}

			// NONCE enabled
			$nonce_enabled = is_admin() || is_user_logged_in() || self::option_get('security_nonce');

			// Check wp_verify_nonce exists
			if($nonce_enabled && !function_exists('wp_verify_nonce')) { self::error_nonce(); }

			// Regular GET, POST, PUT handling
			switch($request_method) {

				case 'GET' :

					// If value is not set, return the default value
					if(!isset($_GET)) { return $default; }	// phpcs:ignore WordPress.Security.NonceVerification
					if(!isset($_GET[$var]) && !isset($_GET['data'])) { return $default; }	// phpcs:ignore WordPress.Security.NonceVerification

					// NONCE
					if(
						$nonce_enabled &&
						!self::$nonce_verified &&
						(

							!isset($_GET[WS_FORM_POST_NONCE_FIELD_NAME]) ||	// phpcs:ignore WordPress.Security.NonceVerification
							!wp_verify_nonce(wp_unslash($_GET[WS_FORM_POST_NONCE_FIELD_NAME]), WS_FORM_POST_NONCE_ACTION_NAME)	// phpcs:ignore WordPress.Security.NonceVerification
						)
					) {

						self::error_nonce();

					} else {

						self::$nonce_verified = true;
					}

					$post_vars = $_GET;	// phpcs:ignore WordPress.Security.NonceVerification

					break;

				case 'POST' :

					// If value is not set, return the default value
					if(!isset($_POST)) { return $default; }	// phpcs:ignore WordPress.Security.NonceVerification
					if(!isset($_POST[$var]) && !isset($_POST['data'])) { return $default; }	// phpcs:ignore WordPress.Security.NonceVerification

					// NONCE
					if(
						$nonce_enabled &&
						!self::$nonce_verified &&
						(
							!isset($_POST[WS_FORM_POST_NONCE_FIELD_NAME]) ||	// phpcs:ignore WordPress.Security.NonceVerification
							!wp_verify_nonce(wp_unslash($_POST[WS_FORM_POST_NONCE_FIELD_NAME]), WS_FORM_POST_NONCE_ACTION_NAME)
						)
					) {
						self::error_nonce();

					} else {

						self::$nonce_verified = true;
					}

					$post_vars = $_POST;	// phpcs:ignore WordPress.Security.NonceVerification

					break;

				case 'PUT' :

					// PUT method data is in php://input so parse that into $post_vars
					parse_str(file_get_contents('php://input'), $post_vars);

					// NONCE
					if(
						$nonce_enabled &&
						!self::$nonce_verified &&
						(

							!isset($post_vars[WS_FORM_POST_NONCE_FIELD_NAME]) ||
							!wp_verify_nonce(wp_unslash($post_vars[WS_FORM_POST_NONCE_FIELD_NAME]), WS_FORM_POST_NONCE_ACTION_NAME)
						)
					) {

						self::error_nonce();

					} else {

						self::$nonce_verified = true;
					}

					$strip_slashes = false;

					break;

				default :

					return $default;
			}

			// DATA param (This overcomes standard 1000 POST parameter limitation in PHP)
			if(
				isset($post_vars['data'])
			) {

				$data = $strip_slashes ? stripslashes_deep($post_vars['data']) : $post_vars['data'];
				$data = self::mod_security_fix($data);

				$data_array = is_string($data) ? json_decode($data) : array();

				if(isset($data_array->{$var})) { return $data_array->{$var}; }
			}

			// Get return value
			$return_value = isset($post_vars[$var]) ? ($esc_sql ? esc_sql($post_vars[$var]) : $post_vars[$var]) : $default;
			$return_value = self::mod_security_fix($return_value);
			return $strip_slashes ? stripslashes_deep($return_value) : $return_value;
		}

		// nonce error
		public static function error_nonce() {

			// Simulate WordPress error
			$code = 'rest_forbidden';
			$message = __('Sorry, you are not allowed to do that (WSF).', 'ws-form');
			$status = rest_authorization_required_code();

			// Build response
			$response = array(

				'code' => $code,
				'message' => $message,
				'data' => array(

					'status' => $status
				)
			);

			// Set HTTP content type header
			header('Content-Type: application/json');

			// Set HTTP response code in case a hook has changed this
			http_response_code($status);

			// Send response
			self::echo_wp_json_encode($response);

			exit;
		}

		// Return string with wp_filter_post_kses applied if user does not have unfiltered_html capability
		// If DISALLOW_UNFILTERED_HTML is true then unfiltered_html capability is removed in core
		public static function santitize_unfiltered_input($value, $key = false) {

			$user_unfiltered_html = self::can_user('unfiltered_html');

			// User is permitted to use unfiltered HTML to return value
			if($user_unfiltered_html) { return $value; }

			// Strings
			if(is_string($value)) {

				return wp_kses_post($value);
			}

			// Arrays
			if(is_array($value)) {

				// Check for custom attributes
				switch($key) {

					// Check the attribute names and attempt to filter anything that might result in JavaScript execution.
					case 'custom_attributes' :

						foreach($value as $custom_attribute_index => $custom_attribute_row) {

							// Check that name and value exist in repeater row
							if(!isset($custom_attribute_row->custom_attribute_name)) { continue; }
							if(!isset($custom_attribute_row->custom_attribute_value)) { continue; }

							// Get attribute name
							$attribute_name = $custom_attribute_row->custom_attribute_name;
							if(empty($attribute_name)) { continue; }

							// Sanitize the name
							$attribute_name_sanitized = strtolower(trim(preg_replace('/[^A-Za-z]/', '', $attribute_name)));

							// HTML event attributes begin with 'on'
							if(strpos($attribute_name_sanitized, 'on') === 0) {

								// Remove row containing HTML event attribute
								unset($value[$custom_attribute_index]);

								continue;
							}

							// Get attribute value
							$attribute_value = $custom_attribute_row->custom_attribute_value;

							// Sanitize name and value
							$value[$custom_attribute_index]->custom_attribute_name = preg_replace('/[^\p{L}0-9_.-]/', '', $attribute_name);
							$value[$custom_attribute_index]->custom_attribute_value = esc_attr($attribute_value);
						}

						return $value;
				}
			}

			// Objects
			if(is_object($value)) {

				// Check for custom attributes
				switch($key) {

					// Data grids
					case 'data_grid_datalist' :
					case 'data_grid_select' :
					case 'data_grid_select_price' :
					case 'data_grid_checkbox' :
					case 'data_grid_checkbox_price' :
					case 'data_grid_radio' :
					case 'data_grid_radio_price' :

						return self::santitize_unfiltered_input_data_grid($value);
				}
			}

			return $value;
		}

		// Sanitize data grid
		public static function santitize_unfiltered_input_data_grid($value) {

			// Get groups
			if(!property_exists($value, 'groups')) { return $value; }
			if(!is_array($value->groups)) { return $value; }

			$groups = $value->groups;

			foreach($groups as $group_index => $group) {

				// Get rows
				if(!property_exists($group, 'rows')) { continue; }
				if(!is_array($group->rows)) { continue; }

				$rows = $group->rows;

				foreach($rows as $row_index => $row) {

					// Get data
					if(!property_exists($row, 'data')) { continue; }
					if(!is_array($row->data)) { continue; }

					$data = $row->data;

					foreach($data as $data_index => $data_value) {

						$value->groups[$group_index]->rows[$row_index]->data[$data_index] = wp_kses_post($data_value);
					}
				}
			}

			return $value;
		}

		// mod_security fix
		public static function mod_security_fix($fix_this) {

			if(is_string($fix_this)) {

				$fix_this = str_replace('~WSF%23~', '#', $fix_this);
				$fix_this = str_replace('~WSF%60~', '<', $fix_this);
				$fix_this = str_replace('~WSF%62~', '>', $fix_this);
				$fix_this = str_replace('~WSFTCELES~', 'SELECT', $fix_this);
				$fix_this = str_replace('~WSFtceles~', 'select', $fix_this);
				$fix_this = str_replace('~WSFtceleS~', 'Select', $fix_this);
				$fix_this = str_replace('~WSFelyts~', 'style', $fix_this);
				$fix_this = str_replace('~WSFELYTS~', 'STYLE', $fix_this);
				$fix_this = str_replace('~WSFcrs~', 'src', $fix_this);
				$fix_this = str_replace('~WSFCRS~', 'SRC', $fix_this);
				$fix_this = str_replace('~WSFsnlmx~', 'xmlns', $fix_this);
				$fix_this = str_replace('~WSFSNLMX~', 'XMLNS', $fix_this);
				$fix_this = str_replace('~WSFid_tcejbo~', 'object_id', $fix_this);

				return $fix_this;
			}

			if(is_array($fix_this)) {

				foreach($fix_this as $key => $fix_this_single) {

					$fix_this[$key] = self::mod_security_fix($fix_this_single);
				}

				return $fix_this;
			}

			return $fix_this;
		}
		// Get IP lookup
		public static function get_ip_lookup($var_array, $ip_lookup_method = '') {

			if(!is_array($var_array)) { $var_array = array($var_array); }

			if(self::$ip_lookup_response === false) {

				// Get remote IP address (Sanitized with sanitize_ip_address below)
				$remote_addr = self::get_ip();
				if($remote_addr == '') { return ''; }

				// Split IP (IP can be comma separated if proxy in use)
				$ip_array = explode(',', $remote_addr);
				$ip_lookup = false;

				foreach($ip_array as $ip) {

					// Trim
					$ip = trim($ip);

					// Validate IP
					if(self::sanitize_ip_address($ip) === false) { continue; }

					// Use valid IP found
					$ip_lookup = $ip;
					break;
				}

				if($ip_lookup !== false) {

					// Build URL
					switch($ip_lookup_method) {

						case 'ipinfo' :

							// Check for API key
							$ip_lookup_ipinfo_key = sanitize_text_field(self::option_get('ip_lookup_ipinfo_key'));

							if(!empty($ip_lookup_ipinfo_key)) {

								// Commercial use
								$url = sprintf('https://ipinfo.io/%s/json?token=%s', $ip_lookup, $ip_lookup_ipinfo_key);

							} else {

								// Non-commercial use
								$url = sprintf('https://ipinfo.io/%s/json', $ip_lookup);
							}

							break;

						case 'ipapi' :

							// Check for API key
							$ip_lookup_ipapi_key = sanitize_text_field(self::option_get('ip_lookup_ipapi_key'));

							if(!empty($ip_lookup_ipapi_key)) {

								// Commercial use
								$url = sprintf('https://pro.ip-api.com/json/%s?fields=country,region,city,zip,lat,lon,timezone&key=%s', $ip_lookup, $ip_lookup_ipapi_key);

							} else {

								// Non-commercial use (HTTP only)
								$url = sprintf('http://ip-api.com/json/%s?fields=country,region,city,zip,lat,lon,timezone', $ip_lookup);
							}
							break;

						case 'ipapico' :

							// Check for API key
							$ip_lookup_ipapico_key = sanitize_text_field(self::option_get('ip_lookup_ipapico_key'));

							if(!empty($ip_lookup_ipapico_key)) {

								// Commercial use
								$url = sprintf('https://ipapi.co/%s/json?key=%s', $ip_lookup, $ip_lookup_ipapico_key);

							} else {

								// Non-commercial use (HTTP only)
								$url = sprintf('http://ipapi.co/%s/json', $ip_lookup);
							}
							break;

						default :

							// Data requested from geoplugin.net that uses GeoLite data created by MaxMind, available from http://www.maxmind.com

							// Check for API key
							$ip_lookup_geoplugin_key = sanitize_text_field(self::option_get('ip_lookup_geoplugin_key'));

							if(!empty($ip_lookup_geoplugin_key)) {

								$url = sprintf('https://ssl.geoplugin.net/json.gp?ip=%s&k=%s', $ip_lookup, $ip_lookup_geoplugin_key);

							} else {

								$url = sprintf('http://www.geoplugin.net/json.gp?ip=%s', $ip_lookup);
							}
					}

					// Sanitize URL
					$url = sanitize_url($url);

					// Build args
					$args = array(

						'headers' => array(

							// Set referrer. PHP CURL defaults to request URL.
							'referer' => self::get_referrer()
						),

						'user-agent'	=> self::get_request_user_agent(),
						'timeout'		=> self::get_request_timeout(),
						'sslverify'		=> self::get_request_sslverify()
					);

					// Do IP Lookup
					$wp_remote_get_response = wp_remote_get($url, $args);

					// Check for errors
					if(!is_wp_error($wp_remote_get_response)) {

						$ip_lookup_response = wp_remote_retrieve_body($wp_remote_get_response);

						// Build data
						self::$ip_lookup_response = json_decode($ip_lookup_response);

						if(is_null(self::$ip_lookup_response)) {

							// Set to true if error so we don't request again
							self::$ip_lookup_response = true;

						} else {

							// Convert to array
							self::$ip_lookup_response = (array) self::$ip_lookup_response;
						}

					} else {

						// Set to true so we don't make any further requests
						self::$ip_lookup_response = true;
					}
				}
			}

			// Lookup geo data and return the result
			if(is_array(self::$ip_lookup_response)) {

				$return_array = array();

				foreach($var_array as $var) {

					if(isset(self::$ip_lookup_response[$var])) {

						$return_array[] = self::$ip_lookup_response[$var];
					}
				}

				return implode(',', $return_array);
			}

			return '';
		}

		// Get MySQL date
		public static function get_mysql_date($date = false) {

			$time = ($date === false) ? time() : strtotime($date);
			if($time === false) { return false; }
			$date = gmdate('Y-m-d H:i:s', $time);
			return $date;
		}

		// Get request method
		public static function get_request_method($valid_request_methods = false) {

			// Check for valid request methods
			if(!$valid_request_methods) { $valid_request_methods = ['GET', 'POST', 'PUT', 'DELETE']; }

			// Check to ensure we can determine request method
			if(!isset($_SERVER) || !isset($_SERVER["REQUEST_METHOD"])) { return false; }	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			// Read request method
			$request_method = strtoupper(wp_unslash($_SERVER["REQUEST_METHOD"]));

			// Ensure it is valid
			if(!in_array($request_method, $valid_request_methods)) { return false; }

			return ($request_method != '') ? $request_method : false;
		}

		// Get current user ID (TO DO: We're going to drop this function in future versions)
		public static function get_user_id($exit_on_zero = true) {

			$user_id = get_current_user_id();
			if(($user_id == 0) && $exit_on_zero) { exit; }
			return($user_id);
		}

		// Get request URL
		public static function get_request_url() {

			return home_url(sanitize_url($_SERVER['REQUEST_URI']));
		}

		// Add query arg from key value pair, but retain periods in keys
		public static function wsf_add_query_args($params, $url) {

			if(!is_array($params)) { return $url; }

			foreach($params as $key => $value) {

				// The function add_query_arg changes periods to underscores
				// We don't want this because some APIs (e.g. AWeber) require periods in keys
				// Lets change periods to a different string so we can recover them later
				$key = str_replace('.', '~WSFPERIOD~', $key);

				// URL encode the value(s)
				if(is_array($value)) {

					array_walk_recursive($value, function (&$value) {

						$value = rawurlencode($value);
					});

				} else {

					$value = rawurlencode($value);
				}

				// Add key value to URL
				$url = add_query_arg($key, $value, $url);
			}

			// Recover periods
			$url = str_replace('~WSFPERIOD~', '.', $url);

			return $url;
		}

		// Get current URL (Used by add-ons during oAuth)
		public static function get_current_url() {

			// Check $_SERVER global exists
			if(!isset($_SERVER)) { return ''; }

			// Build prefix
			$request_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';

			// Add hostname
			if(isset($_SERVER['HTTP_HOST'])) { $request_url .= $_SERVER['HTTP_HOST']; }

			// Add request URI
			if(isset($_SERVER['REQUEST_URI'])) { $request_url .= sanitize_url($_SERVER['REQUEST_URI']); }

			return $request_url;
		}
		// Add query string to URL
		public static function add_query_string($url, $query_string) {

			$url_parsed = wp_parse_url($url);
			if(!isset($url_parsed['path'])) { $url .= '/'; }
			$separator = isset($url_parsed['query']) ? '&' : '?';
			$url .= $separator . $query_string;

			return $url;
		}

		// Echo comment - CSS
		public static function comment_css($comment) {

			// Should CSS be commented?
			$comments_css = self::option_get('comments_css', true);

			if($comments_css) {

				// Echo comment
				$return_css = sprintf("/* %s */\n", esc_html($comment));

			} else {

				$return_css = '';
			}

			return $return_css;
		}

		// SVG Render
		public static function render_icon_16_svg($id) {

			$return_html = WS_Form_Config::get_icon_16_svg($id);

			if($return_html !== false) {

				// Static SVG outut
				echo $return_html;	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			}
		}

		// Check form status
		public static function check_form_status($status) {

			return in_array($status, array(

				'draft',
				'publish',
				'trash'

			), true) ? $status : '';
		}

		// Check form order by
		public static function check_form_order_by($order_by) {

			return in_array($order_by, array(

				'status',
				'label',
				'id',
				'count_submit',
				'count_stat_view',
				'conversion'

			), true) ? $order_by : '';
		}

		// Check form order
		public static function check_form_order($order) {

			return in_array($order, array(

				'asc',
				'desc'

			), true) ? $order : '';
		}

		// Check style status
		public static function check_style_status($status) {

			return in_array($status, array(

				'draft',
				'publish',
				'trash'

			), true) ? $status : '';
		}

		// Check style order by
		public static function check_style_order_by($order_by) {

			return in_array($order_by, array(

				'label',
				'id'

			), true) ? $order_by : '';
		}

		// Check style order
		public static function check_style_order($order) {

			return in_array($order, array(

				'asc',
				'desc'

			), true) ? $order : '';
		}

		// Check submit status
		public static function check_submit_status($status) {

			return in_array($status, array(

				'draft',
				'publish',
				'error',
				'spam',
				'trash'

			), true) ? $status : '';
		}

		// Get API base path
		public static function get_api_path($path = '', $query_string = false) {

			// Check permalinks
			$permalink_custom = (get_option('permalink_structure') != '');

			if($permalink_custom) {

				$api_path = rest_url() . WS_FORM_RESTFUL_NAMESPACE . '/' . $path;
				if($query_string !== false) { $api_path .= '?' . $query_string; }

			} else {

				$path = '/' . WS_FORM_RESTFUL_NAMESPACE . '/' . $path;
				$api_path = get_site_url() . '/?rest_route=' . rawurlencode($path);
				if($query_string !== false) { $api_path .= '&' . $query_string; }
			}

			return $api_path;
		}

		// Is debug enabled?
		public static function debug_enabled() {

			if(
				self::is_block_editor() ||
				self::is_customize_preview()

			) { return false; }

			$debug_enabled = false;

			switch(self::option_get('helper_debug', 'off')) {

				case 'administrator' : 	

					if(function_exists('wp_get_current_user')) {

						// Works better for multisite than checking roles. Roles are not available in WP_User on multisite
						$debug_enabled = current_user_can('activate_plugins');
					}

					break;
	
				case 'on' :

					$debug_enabled = true;

					break;
			}

			return apply_filters('wsf_debug_enabled', $debug_enabled);
		}

		// Is styler enabled?
		public static function styler_enabled() {

			return apply_filters(

				'wsf_styler_enabled',
				WS_FORM_STYLER
			);
		}

		// Is styler visible in admin?
		public static function styler_visible_admin() {

			return (
				self::styler_enabled() &&
				(self::option_get('framework', 'ws-form') === 'ws-form') &&
				self::can_user('read_form_style')
			);
		}

		// Shpuld styler visible on site?
		public static function styler_visible_public() {

			return (

				self::styler_visible_admin() &&
				(
					!empty(self::get_query_var('wsf_preview_style_id')) ||
					!empty(self::get_query_var('wsf_preview_styler'))
				)
			);
		}

		// Is customizer enabled?
		public static function customizer_enabled() {

			return !self::styler_enabled();
		}

		// Is customizer visible?
		public static function customizer_visible() {

			return (

				self::customizer_enabled() &&
				(self::option_get('framework', 'ws-form') === 'ws-form') &&
				self::can_user('customize') &&
				self::can_user('read_form')
			);
		}

		// Legacy color functions for add-ons
		public static function hex_lighten_percentage($hex, $percentage) {

			return WS_Form_Color::hex_color_lighten_percentage($hex, $percentage);
		}

		public static function hex_darken_percentage($hex, $percentage) {

			return WS_Form_Color::hex_color_darken_percentage($hex, $percentage);
		}

		// Check to see if object should show
		public static function object_show($object, $object_prefix, $current_user, $user_roles) {

			// Object user status
			$object_user_status = self::get_object_meta_value($object, $object_prefix . '_user_status', '');
			$object_user_roles = self::get_object_meta_value($object, $object_prefix . '_user_roles', false);
			$object_user_capabilities = self::get_object_meta_value($object, $object_prefix . '_user_capabilities', false);

			if($object_user_status) {

				$object_show = false;

				switch($object_user_status) {

					// Must be logged in
					case 'on' :

						if($current_user->ID > 0) { $object_show = true; }
						break;

					// Must be logged out
					case 'out' :

						if($current_user->ID == 0) { $object_show = true; }
						break;

					// Must have user role or capability
					case 'role_capability' :

						if(is_array($object_user_roles) && (count($object_user_roles) > 0)) {

							$user_role_ok = false;

							if(is_user_logged_in()) {

								foreach($object_user_roles as $object_user_role) {

									if(in_array($object_user_role, $user_roles)) {

										$user_role_ok = true;
									}
								}
							}

						} else {

							$user_role_ok = true;
						}

						if(is_array($object_user_capabilities) && (count($object_user_capabilities) > 0)) {

							$user_capability_ok = false;

							if(is_user_logged_in()) {

								foreach($object_user_capabilities as $object_user_capability) {

									if(self::can_user($object_user_capability)) {

										$user_capability_ok = true;
									}
								}
							}

						} else {

							$user_capability_ok = true;
						}

						if($user_role_ok && $user_capability_ok) { $object_show = true; }

						break;
				}

			} else {

				return true;
			}

			return $object_show;
		}

		// Get all fields from form
		public static function get_fields_from_form($form_object, $no_cache = false, $filter_group_ids = false, $filter_section_ids = false) {

			// Retrieve from cache
			if(!$no_cache && (self::$fields !== false)) { return self::$fields; }

			// Get fields
			$fields = self::get_fields_from_form_group($form_object->groups, $filter_group_ids, $filter_section_ids);

			// Add to cache
			self::$fields = $fields;

			return $fields;
		}

		// Get all fields from form
		public static function get_field_types($no_cache = false) {

			// Retrieve from cache
			if(!$no_cache && (self::$field_types !== false)) { return self::$field_types; }

			// Get field types
			$field_types = WS_Form_Config::get_field_types_flat();

			// Add to cache
			self::$field_types = $field_types;

			return $field_types;
		}

		// Run through each group
		public static function get_fields_from_form_group($groups, $filter_group_ids, $filter_section_ids) {

			$fields = array();

			foreach($groups as $key => $group) {

				// Get group ID
				$group_id = $group->id;

				// Filter group IDs
				if(
					($filter_group_ids !== false) &&
					!in_array($group_id, $filter_group_ids)
				) {
					continue;
				}

				if(isset($groups[$key]->sections)) {

					$section_fields = self::get_fields_from_form_section($key, $group->sections, $filter_section_ids);

					$fields = $fields + $section_fields;
				}
			}

			return $fields;
		}

		// Run through each section
		public static function get_fields_from_form_section($group_key, $sections, $filter_section_ids) {

			$fields = array();

			foreach($sections as $section_key => $section) {

				// Get section ID
				$section_id = $section->id;

				// Filter section IDs
				if(
					($filter_section_ids !== false) &&
					!in_array($section_id, $filter_section_ids)
				) {
					continue;
				}

				// Check if repeatable
				$section_repeatable = isset($section->meta) && isset($section->meta->section_repeatable) && !empty($section->meta->section_repeatable);

				if(isset($sections[$section_key]->fields)) {

					$section_fields = array();

					foreach($section->fields as $field_key => $field) {

						$field->group_key = $group_key;
						$field->section_key = $section_key;
						$field->field_key = $field_key;

						$field->section_id = $section_id;
						$field->section_repeatable = $section_repeatable;

						$section_fields[$field->id] = $field;
					}

					$fields = $fields + $section_fields;
				}
			}

			return $fields;
		}

		// Get all sections from form
		public static function get_sections_from_form($form_object, $no_cache = false, $get_fields = true, $get_meta = true) {

			// Retrieve from cache
			if(!$no_cache && (self::$sections !== false)) { return self::$sections; }

			// Get sections
			$sections = self::get_sections_from_form_group($form_object->groups, $get_fields, $get_meta);

			// Add to cache
			self::$sections = $sections;

			return $sections;
		}

		// Run through each group
		public static function get_sections_from_form_group($groups, $get_fields = true, $get_meta = true) {

			$sections_return = array();

			foreach($groups as $key => $group) {

				if(isset($groups[$key]->sections)) {

					$section_fields = self::get_sections_from_form_section($group->sections, $get_fields, $get_meta);

					$sections_return = $sections_return + $section_fields;
				}
			}

			return $sections_return;
		}

		// Run through each section
		public static function get_sections_from_form_section($sections, $get_fields = true, $get_meta = true) {

			$sections_return = array();

			foreach($sections as $key => $section) {

				// Get section ID
				$section_id = $section->id;

				$sections_return[$section_id] = new stdClass();
				$sections_return[$section_id]->label = $section->label;
				$sections_return[$section_id]->fields = array();
				$sections_return[$section_id]->meta = new stdClass();

				// Check if repeatable
				$sections_return[$section_id]->repeatable = isset($section->meta) && isset($section->meta->section_repeatable) && !empty($section->meta->section_repeatable);

				if(isset($section->fields) && $get_fields) {

					$section_fields = array();

					foreach($section->fields as $field) {

						$section_fields[$field->id] = $field;
					}

					$sections_return[$section_id]->fields = $section_fields;
				}

				if(isset($section->meta) && $get_meta) {

					$sections_return[$section_id]->meta = $section->meta;
				}
			}

			return $sections_return;
		}

		// Get all groups from form
		public static function get_groups_from_form($form_object, $no_cache = false) {

			// Retrieve from cache
			if(!$no_cache && (self::$groups !== false)) { return self::$groups; }

			// Get groups
			$groups = array();

			foreach($form_object->groups as $key => $group) {

				$groups[$group->id] = new stdClass();
				$groups[$group->id]->label = $group->label;
			}

			// Add to cache
			self::$groups = $groups;

			return $groups;
		}

		// Mask parse
		public static function mask_parse($mask, $values, $prefix = '#', $single_parse = false) {

			if($mask == '') { return ''; }

			// Final sort
			uksort($values, function($variable_a, $variable_b) {

				if($variable_a === $variable_b) { return 0; }

				$variable_a_is_function = (strpos($variable_a, '(') !== false);
				$variable_b_is_function = (strpos($variable_b, '(') !== false);

				if($variable_a_is_function && $variable_b_is_function) {

					return $variable_a < $variable_b ? 1 : -1;
				}

				if(
					(!$variable_a_is_function && $variable_b_is_function) ||
					($variable_a_is_function && !$variable_b_is_function)
				) {

					return $variable_a_is_function < $variable_b_is_function ? 1 : -1;
				}

				return $variable_a < $variable_b ? 1 : -1;
			});

			foreach($values as $key => $value) {

				if(is_null($value)) { $value = ''; }

				if($single_parse) {

					// Single parse
					$replace = '/' . preg_quote($prefix . $key, '/') . '/';
					$mask = preg_replace($replace, $value, $mask, 1);

				} else {

					// Multi parse (Default)
					$mask = str_replace($prefix . $key, $value, $mask);
				}
			}

			return $mask;
		}

		// Create shortcode
		public static function shortcode($form_id = false) {

			$form_id = absint($form_id);
			if($form_id === 0) { return ''; }

			return sprintf('[%s id="%u"]', WS_FORM_SHORTCODE, esc_attr($form_id));
		}

		// Check file upload capabilities
		public static function uploads_check() {

			// Create file warnings
			$files_warning = [];

			// max_file_uploads
			// Default: 20
			$max_file_uploads = absint(ini_get('max_file_uploads') ? ini_get('max_file_uploads') : '20');

			// Check file permissions
			$upload_dir_create = self::upload_dir_create();
			if($upload_dir_create['error']) { $files_warning[] = $upload_dir_create['message']; }

			// Return result
			return ['max_upload_size' => wp_max_upload_size(), 'max_uploads' => $max_file_uploads, 'errors' => $files_warning];
		}

		// Make sure upload folder can be created. Create it if it doesn't exist.
		public static function upload_dir_create($dir = '') {

			// Get base upload_dir
			$upload_dir = wp_upload_dir()['basedir'];

			// Check upload directory can be written to
			if(!is_writeable($upload_dir)) { return ['error' => true, 'message' => __('Your WordPress uploads directory cannot be written to.', 'ws-form')]; }

			// Get WS Form upload dir
			$upload_dir_path = WS_FORM_UPLOAD_DIR . (($dir != '') ? '/' . $dir : '');
			$upload_dir_ws_form = $upload_dir . '/' . $upload_dir_path;

			// Check to see if WS Forms upload folder exists
			if(!file_exists($upload_dir_ws_form)) {

				if(!wp_mkdir_p($upload_dir_ws_form)) { return ['error' => true, 'message' => sprintf(__('Unable to create upload folder for uploaded files (wp-content/uploads/%s).', 'ws-form'), $upload_dir_path)]; }
			}

			return ['error' => false, 'dir' => $upload_dir_ws_form, 'path' => $upload_dir_path];
		}

		// Check path is valid
		public static function check_path($path_base) {

			$illegal_paths = array('..', '~');

			foreach($illegal_paths as $illegal_path) {

				if(strpos($path_base, $illegal_path) !== false) { wp_die(__('Invalid Path', 'ws-form')); }
			}

			$path = realpath($path_base);
			if(strpos($path, $path_base) !== 0 || strpos($path, $path_base) === false) { wp_die(__('Invalid Path', 'ws-form')); } 

			return $path_base;			
		}

		// Get HTTP environment variable (Accepts array for multiple HTTP environment variable checks)
		// Return from this function must be sanitized
		public static function get_http_env_raw($variable_array) {

			// Checks
			if(!isset($_SERVER)) { return ''; }	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if(!is_array($variable_array)) { $variable_array = array($variable_array); }
			$variable_array_index_last = count($variable_array) - 1;

			// Run through each variable
			foreach($variable_array as $variable_array_index => $variable) {

				if(isset($_SERVER[$variable])) {	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

					if($variable_array_index == $variable_array_index_last) {

						return wp_unslash($_SERVER[$variable]);

					} else {

						if(!empty($_SERVER[$variable])) return wp_unslash($_SERVER[$variable]);
					}
				}
			}

			return '';
		}

		// Get IP
		public static function get_ip() {

			return self::sanitize_ip_address(self::get_http_env_raw(array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR')));
		}

		// Ger user agent
		public static function get_user_agent() {

			return sanitize_text_field(self::get_http_env_raw(array('HTTP_USER_AGENT')));
		}

		// Get referrer
		public static function get_referrer() {

			return sanitize_url(self::get_http_env_raw(array('HTTP_REFERER')));
		}

		// Get wp_remote_* user agent string
		public static function get_request_user_agent() {

			return sprintf('WSForm/%s (wsform.com)', WS_FORM_VERSION);
		}

		// Get wp_remote_* timeout
		public static function get_request_timeout($timeout = WS_FORM_API_CALL_TIMEOUT) {

			return apply_filters('wsf_api_call_timeout', $timeout);
		}

		// Get wp_remote_* sslverify
		public static function get_request_sslverify($ssl_verify = WS_FORM_API_CALL_SSL_VERIFY) {

			return apply_filters('wsf_api_call_verify_ssl', $ssl_verify);
		}

		// Get object property
		public static function get_object_property($object, $property, $default_value = '') {

			// Check for meta data
			if(
				!is_object($object) ||
				!property_exists($object, $property)
			) {
				return $default_value;
			}

			return $object->{$property};
		}

		// Get object var (Legacy)
		public static function get_object_var($object, $property, $default_value = '') {

			return self::get_object_property($object, $property, $default_value);
		}

		// Get object meta value
		public static function get_object_meta_value($object, $meta_key, $default_value = '') {

			// Checks
			if(
				!is_object($object) ||
				!property_exists($object, 'meta') ||
				!property_exists($object->meta, $meta_key)
			) {
				return $default_value;
			}

			return $object->meta->{$meta_key};
		}

		// Get array meta value
		public static function get_array_meta_value($array, $meta_key, $default_value = '') {

			// Check for meta data
			if(!isset($array['meta'])) { return $default_value; }

			// Check for meta_key
			if(!isset($array['meta']->{$meta_key})) { return $default_value; }

			return $array['meta']->{$meta_key};
		}

		// Get numbers from string
		public static function get_tel($phone) {

			return preg_replace('/[^+\d]+/', '', $phone);
		}

		// Get string from any type of input
		public static function get_string($value, $delimiter = ',') {

			// Check for string
			if(is_string($value)) { return $value; }

			// Check for boolean
			if(is_bool($value)) { return $value ? '1' : '0'; }

			// Check for number
			if(is_numeric($value)) { return strval($value); }

			// Check for object
			if(is_object($value)) { $value = (array) $value; }

			// Ensure value is a string
			if(is_array($value)) {

				$value_new = array();

				foreach($value as $value_element) {

					if(
						is_string($value_element) ||
						is_numeric($value_element)
					) {
						$value_new[] = (string) $value_element;
					}
				}

				$value = implode($delimiter, $value_new);
			}

			return $value;
		}

		// Get float with fraction from string
		public static function get_number($number_input, $default_value = 0, $process_currency = false, $decimals = false) {

			// Convert numbers to text
			if(is_numeric($number_input)) { $number_input = strval($number_input); }

			// Check input is a string
			if(!is_string($number_input)) { return 0; }

			// Trim input
			$number_input = trim($number_input);

			// Convert from current currency
			if($process_currency) {

				$currency = self::get_currency();

				// Filter characters required for floatval
				$decimal_separator = $currency['decimal_separator'];
				$thousand_separator = $currency['thousand_separator'];

				// Ensure the decimal separator setting is included in the regex (Add ,. too in case default value includes alternatives)
				$number_input = preg_replace('/[^0-9-' . $decimal_separator . ']/', '', $number_input);

				if($decimal_separator === $thousand_separator) {

					// Convert decimal separators to periods so floatval works
					if(substr($number_input, -3, 1) === $decimal_separator) {

						$decimal_index = (strlen($number_input) - 3);
						$number_input = substr($number_input, 0, $decimal_index) . '[dec]' . substr($number_input, $decimal_index + 1);
					}

					// Remove thousand separators
					$number_input = str_replace($thousand_separator, '', $number_input);

					// Replace [dec] back to decimal separator for floatval
					$number_input = str_replace('[dec]', '.', $number_input);

				} else {

					// Replace [dec] back to decimal separator for floatval
					$number_input = str_replace($decimal_separator, '.', $number_input);
				}
			}

			// floatval converts decimal separator to period to ensure that function works
			$number_output = (trim($number_input) === '') ? $default_value : (!is_numeric($number_input) ? $default_value : floatVal($number_input));

			// Round
			if($decimals !== false) { $number_output = round(floatval($number_output), $decimals); }

			return $number_output;
		}

		// Get array of MIME types
		public static function get_mime_array($value) {

			if(is_array($value)) { return $value; }

			$mime_array = explode(',', $value);
			$mime_array_return = array();

			foreach($mime_array as $mime) {

				$mime_split = $mime_array = explode('/', $mime);
				if(count($mime_split) !== 2) { continue; }
				if(strlen($mime_split[0]) == 0) { continue; }
				if(strlen($mime_split[1]) == 0) { continue; }
				$mime_array_return[] = strtolower(trim($mime));
			}

			return $mime_array_return;
		}

		// Get accept string
		public static function get_accept_string($mime_type_filter_include = false, $mime_type_filter_exclude = false) {

			// Get MIME types
			$mime_types = wp_get_mime_types();

			// Build accept array
			$accept_array = array();

			foreach($mime_types as $extensions => $mime_type) {

				// Filter - Include
				if($mime_type_filter_include !== false) {

					foreach($mime_type_filter_include as $mime_type_filter) {

						if(strpos($mime_type, $mime_type_filter) === false) { continue 2; }
					}
				}

				// Filter - Exclude
				if($mime_type_filter_exclude !== false) {

					foreach($mime_type_filter_exclude as $mime_type_filter) {

						if(strpos($mime_type, $mime_type_filter) !== false) { continue 2; }
					}
				}

				// Split extensions
				$extension_array = explode('|', $extensions);

				foreach($extension_array as $extension) {

					$accept_array[] = sprintf('.%s', $extension);
				}

				// Add MIME type
				$accept_array[] = $mime_type;
			}

			return implode(',', $accept_array);
		}

		// Add datestamp to filename
		public static function filename_datestamp($filename_prefix, $filename_suffix) {

			$filename = $filename_prefix . (self::wp_version_at_least('5.3') ? current_datetime()->format('-Y-m-d-H-i-s') : current_time('-Y-m-d-H-i-s')) . '.' . $filename_suffix;
			return sanitize_file_name($filename);
		}

		// Output file download headers
		public static function file_download_headers($filename, $mime_type, $encoding = 'binary') {

			$filename = sanitize_file_name($filename);			// WordPress function

			// HTTP headers
			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: private', false);
			header('Content-Type: ' . $mime_type);
			header('Content-Disposition: attachment; filename=' . $filename);
			header('Content-Transfer-Encoding: ' . $encoding);
		}

		// Get tracking data
		public static function get_tracking_data($form, $submit, $content_type = 'text/html') {

			$tracking_data = array();

			// Check form and submit meta data
			if(
				($form === false) ||
				!isset($submit->meta)
			) {
				return $tracking_data;
			}

			// Process tracking config
			foreach(WS_Form_Config::get_tracking(false) as $meta_key => $tracking) {

				// Check meta_key is enabled
				if(!self::get_object_meta_value($form, $meta_key, false)) { continue; }

				// Check meta exists in submit data
				if(!isset($submit->meta[$meta_key])) {

					$meta_value = '';

				} else {

					// Get value
					$meta = array(

						'type'	=>	$tracking['type'],
						'value'	=>	$submit->meta[$meta_key]
					);

					// Get parsed version
					$meta_value = self::parse_variables_meta_value($form, $meta, $content_type);

					// Sanitize
					$meta_value = sanitize_text_field($meta_value);

					// Convert # to HTML character (e.g. from tracking_hash) to prevent WS Form variable injection
					$meta_value = str_replace('#', '&num;', $meta_value);
				}

				// Add to tracking data
				$tracking_data[] = array(

					'label' => $tracking['label'],
					'meta_key' => $meta_key,
					'meta_value' => $meta_value,
				);
			}

			return $tracking_data;
		}

		// Parse WS Form variables
		public static function parse_variables_process($parse_string, $form = false, $submit = false, $content_type = 'text/html', $scope = false, $section_repeatable_index = false, $section_row_number = 1, $exclude_secure = false, $action_config = false, $depth = 1) {

			if(!is_string($parse_string)) { return $parse_string; }

			// Checks to speed up this function
			if(strpos($parse_string, '#') === false) { return $parse_string; }

			// Exclude secure on nested parses?
			$exclude_secure_nested_parse = true;

			// Get post
			$post = self::get_post_root();

			// Get user
			$user = self::get_user();

			// Initialize variables
			$variables = array();
			$variables_single_parse = array();

			// Parse type
			$lookups_contain_singles = false;

			// Check for too many iterations
			if($depth > 100) { return ''; }

			// Get parse variables config
			$parse_variables_config = WS_Form_Config::get_parse_variables();

			// Process each parse variable key
			foreach($parse_variables_config as $parse_variables_key => $parse_variables) {

				// Check for prefix (for performance)
				$var_lookup_found = false;

				foreach($parse_variables['var_lookups'] as $var_lookup) {

					// Check if parse string contains lookup value
					if(strpos($parse_string, $var_lookup) !== false) {

						$var_lookup_found = true;
						break;
					}
				}

				// If lookup not found, skip this variable group
				if(!$var_lookup_found) { continue; }

				foreach($parse_variables['variables'] as $parse_variable => $parse_variable_config) {

					// Skip variables that are not in scope
					if($scope !== false) {

						$parse_variable_scope = isset($parse_variable_config['scope']) ? $parse_variable_config['scope'] : array();

						if(is_array($parse_variable_scope)) {

							if(!in_array($scope, $parse_variable_scope)) { continue; }

						} else {

							continue;
						}
					}

					if(strpos($parse_string, '#' . $parse_variable) === false) { continue; }

					$parsed_variable = '';

					switch($parse_variable) {

						case 'email_tracking' :

							// Get tracking data
							$tracking_data = self::get_tracking_data($form, $submit, $content_type);

							foreach($tracking_data as $tracking) {

								// Get label
								$label = $tracking['label'];

								// Get value
								$meta_value = $tracking['meta_value'];

								// Add to fields_all HTML
								switch($content_type) {

									case 'text/html' :

										// $meta_value already sanitied by get_tracking_data
										$parsed_variable .= '<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-bottom: 10px;"><strong>' . esc_html($label) . '</strong><br />' . $meta_value . '</p>';
										break;

									default :

										$parsed_variable .= $label . "\n" . $meta_value . "\n\n";
										break;
								}
							}

							$variables[$parse_variable] = $parsed_variable;

							break;
						default :

							// Get value
							$parse_variable_value = (isset($parse_variable_config['value'])) ? $parse_variable_config['value'] : false;

							// Get value from function
							$parse_variable_function = (isset($parse_variable_config['function'])) ? $parse_variable_config['function'] : false;
							if(
								($parse_variable_function !== false) &&
								is_array($parse_variable_function) &&
								(count($parse_variable_function) == 2)
							) {

								// Get class
								$parse_variable_function_class = $parse_variable_function[0];

								// Get method
								$parse_variable_function_method = $parse_variable_function[1];

								// Call method
								$parse_variable_value = $parse_variable_function_class->$parse_variable_function_method($form, $submit, $content_type, $section_repeatable_index, $action_config);
							}

							// Get attributes
							$parse_variable_attributes = (isset($parse_variable_config['attributes'])) ? $parse_variable_config['attributes'] : false;

							// Single parse? (Used if different value returned each parse, e.g. random_number)
							$parse_variable_single_parse = isset($parse_variable_config['single_parse']) ? $parse_variable_config['single_parse'] : false;

							// If no attributes specified, then just set the value
							if(($parse_variable_attributes === false) && ($parse_variable_value !== false)) { $variables[$parse_variable] = $parse_variable_value; break; }

							// Get number of attributes required
							$variable_attribute_count = isset($parse_variable_config['attributes']) ? count($parse_variable_attributes) : 0;

							// Handle variables
							if($variable_attribute_count > 0) {

								// Do until no more found
								$variable_index_start = 0;
								do {

									$parsed_variable = '';

									// Find position of variable and brackets
									$variable_index_of = strpos($parse_string, '#' . $parse_variable, $variable_index_start);

									// No more instances of variable found
									if($variable_index_of === false) { continue; }

									// Find bracket positions
									$variable_index_of_bracket_start = false;
									$variable_index_of_bracket_finish = false;
									$parse_string_function = substr($parse_string, $variable_index_of + strlen('#' . $parse_variable));

									// Bracket should immediately follow the variable name
									if(substr($parse_string_function, 0, 1) == '(') {

										$variable_index_of_bracket_start = $variable_index_of + strlen('#' . $parse_variable);
										$variable_index_of_bracket_finish = self::closing_string_index($parse_string, ')', '(', $variable_index_of_bracket_start + 1);
									}

									// Check brackets found
									if(	($variable_index_of_bracket_start === false) ||
										($variable_index_of_bracket_finish === false) ) {

										// Shift index to look for next instance
										$variable_index_start = $variable_index_of + strlen('#' . $parse_variable);

										// Get full string to parse
										$parse_variable_full = '#' . $parse_variable;

										// No brackets found so set attributes as blank
										$variable_attribute_array = array();

									} else {

										// Shift index to look for next instance
										$variable_index_start = $variable_index_of_bracket_finish + 1;

										// Ensure bracket starts immediate after parse_variable
										if((($variable_index_of_bracket_start - $variable_index_of) - strlen('#' . $parse_variable)) !== 0) { continue; };

										// Get full string to parse
										$parse_variable_full = substr($parse_string, $variable_index_of, ($variable_index_of_bracket_finish + 1) - $variable_index_of);

										// Get attribute string
										$variable_attribute_string = substr($parse_string, $variable_index_of_bracket_start + 1, ($variable_index_of_bracket_finish - 1) - $variable_index_of_bracket_start);

										// Replace non standard double quotes
										$variable_attribute_string = str_replace('“', '"', $variable_attribute_string);
										$variable_attribute_string = str_replace('”', '"', $variable_attribute_string);

										// Get separator
										$separator = isset($parse_variable_config['attribute_separator']) ? $parse_variable_config['attribute_separator'] : ',';

										// Get attribute array
										$variable_attribute_array = self::string_to_attributes($variable_attribute_string, $separator);

										// Trim and strip double quotes
										foreach($variable_attribute_array as $key => $e) {

											$e = preg_replace('/^"(.+(?="$))"$/', '', $e);
											$variable_attribute_array[$key] = $e;
										}
									}

									// Check each attribute
									foreach($parse_variable_attributes as $parse_variable_attributes_index => $parse_variable_attribute) {

										$parse_variable_attribute_id = $parse_variable_attribute['id'];

										// Was attribute provided for this index?
										$parse_variable_attribute_supplied = isset($variable_attribute_array[$parse_variable_attributes_index]);

										// Check required
										$parse_variable_attribute_required = (isset($parse_variable_attribute['required']) ? $parse_variable_attribute['required'] : true);
										if($parse_variable_attribute_required && !$parse_variable_attribute_supplied) {

											// Syntax error - Attribute count
											self::throw_error(sprintf(__('Syntax error, missing attribute: %s (Expected: %s)', 'ws-form'), '#' . $parse_variable, $parse_variable_attribute_id));
											continue;
										}

										// Check default
										$parse_variable_attribute_default = isset($parse_variable_attribute['default']) ? $parse_variable_attribute['default'] : false;

										if(($parse_variable_attribute_default !== false) && !$parse_variable_attribute_supplied) {

											$variable_attribute_array[$parse_variable_attributes_index] = $parse_variable_attribute_default;
										}

										// Check trim
										$parse_variable_attribute_trim = isset($parse_variable_attribute['trim']) ? $parse_variable_attribute['trim'] : true;
										if(
											$parse_variable_attribute_trim &&
											isset($variable_attribute_array[$parse_variable_attributes_index]) &&
											is_string($variable_attribute_array[$parse_variable_attributes_index])
										) {

											$variable_attribute_array[$parse_variable_attributes_index] = trim($variable_attribute_array[$parse_variable_attributes_index]);
										}

										// Check validity
										$parse_variable_attribute_valid = isset($parse_variable_attribute['valid']) ? $parse_variable_attribute['valid'] : false;
										if($parse_variable_attribute_valid !== false) {

											if(
												isset($variable_attribute_array[$parse_variable_attributes_index]) &&
												!in_array($variable_attribute_array[$parse_variable_attributes_index], $parse_variable_attribute_valid)
											) {

												// Syntax error - Attribute count
												self::throw_error(sprintf(__('Syntax error, invalid attribute: %s (Expected: %s)', 'ws-form'), '#' . $parse_variable, implode(', ', $parse_variable_attribute_valid)));
											}
										}
									}

									// Process variable
									switch($parse_variable) {

										case 'query_var' :
										case 'post_var' :

											$parsed_variable = self::get_query_var($variable_attribute_array[0], $variable_attribute_array[1]);
											if($content_type == 'text/html') { $parsed_variable = htmlentities($parsed_variable); }
											break;

										case 'email_submission' :

											if(!isset($submit->meta)) { break; }

											$render_group_labels = $variable_attribute_array[0];
											$render_section_labels = $variable_attribute_array[1];
											$render_field_labels = $variable_attribute_array[2];
											$render_blank_fields = ($variable_attribute_array[3] == 'true');
											$render_static_fields = ($variable_attribute_array[4] == 'true');

											// Get hidden array
											if(isset($submit->meta['wsf_meta_key_hidden'])) {

												$hidden_array = $submit->meta['wsf_meta_key_hidden'];

											} else {

												$hidden_array = explode(',', self::get_query_var_nonce('wsf_hidden', ''));
											}

											$value = self::parse_variables_fields_all((object) $form, $submit, $content_type, $render_group_labels, $render_section_labels, $render_field_labels, $render_blank_fields, $render_static_fields, $hidden_array, $action_config);

											$parsed_variable = self::parse_variables_process($value, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											break;
										case 'if' :

											$endif_position = self::closing_string_index($parse_string, '#endif', '#if', $variable_index_of_bracket_finish);

											if($endif_position === false) {

												self::throw_error(sprintf(__('Syntax error, missing #endif for #if(%s)', 'ws-form'), $variable_attribute_string));
											}

											// Valid operators
											$operators_valid = array('==', '!=', '>', '>=', '<=', '<', '^=', '$=', '!^=', '!$=', '*=', '!*=');

											// Valid logic previous
											$logic_previous_valid = array('&&', '||');

											// Run through each attribute and build boolean array
											$if_boolean_array = array();
											$if_boolean_array_index = 0;
											$if_part = 0;
											foreach($variable_attribute_array as $variable_attribute) {

												if($if_part === 0) {

													$if_boolean_array[$if_boolean_array_index] = array(

														'value_a' => '',
														'operator' => '!=',
														'value_b' => ''
													);
												}

												// Decode operators and logic
												if(($if_part == 1) || ($if_part == 3)) {

													// Convert HTML encoding caused by TinyMCE
													$variable_attribute = str_replace('&amp;', '&', $variable_attribute);
													$variable_attribute = str_replace('&lt;', '<', $variable_attribute);
													$variable_attribute = str_replace('&gt;', '>', $variable_attribute);
												}

												// Check for numeric values
												if(($if_part == 0) || ($if_part == 2)) {

													$variable_attribute = $variable_attribute_string = self::parse_variables_process($variable_attribute, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);
													if(is_numeric($variable_attribute)) { $variable_attribute = floatval($variable_attribute); }
												}

												// Short form #if
												if(
													($if_part == 1) &&
													in_array($variable_attribute, $logic_previous_valid)
												) {

													$if_part = 3;
												}

												// Process IF parts
												switch($if_part) {

													case 0 :
													case 2 :

														$if_part_key = ($if_part == 0) ? 'value_a' : 'value_b';
														$if_boolean_array[$if_boolean_array_index][$if_part_key] = $variable_attribute;
														$if_boolean_array[$if_boolean_array_index][$if_part_key . '_string'] = $variable_attribute_string;
														break;

													case 1 :

														if(!in_array($variable_attribute, $operators_valid)) {

															self::throw_error(sprintf(__('Syntax error, invalid operator %s in #if(%s).', 'ws-form'), $variable_attribute, $variable_attribute_string));
														}
														$if_boolean_array[$if_boolean_array_index]['operator'] = $variable_attribute;
														break;

													case 3 :

														if(!in_array($variable_attribute, $logic_previous_valid)) {

															self::throw_error(sprintf(__('Syntax error, invalid logic %s in #if(%s).', 'ws-form'), $variable_attribute, $variable_attribute_string));
														}
														$if_boolean_array[$if_boolean_array_index]['logic_next'] = $variable_attribute;
														break;
												}

												$if_part++;

												if($if_part > 3) {

													$if_part = 0;
													$if_boolean_array_index++;
												}
											}

											$result = false;

											$logic_next = '&&';

											foreach($if_boolean_array as $if_boolean_array_index => $if_boolean) {

												switch($if_boolean['operator']) {

													// Equals
													case '==' :

														$result_single = ($if_boolean['value_a'] == $if_boolean['value_b']);
														break;

													// Not equals
													case '!=' :

														$result_single = ($if_boolean['value_a'] != $if_boolean['value_b']);
														break;

													// Less than
													case '<' :

														$result_single = ($if_boolean['value_a'] < $if_boolean['value_b']);
														break;

													// Greater than
													case '>' :

														$result_single = ($if_boolean['value_a'] > $if_boolean['value_b']);
														break;

													// Less than or equal
													case '<=' :

														$result_single = ($if_boolean['value_a'] <= $if_boolean['value_b']);
														break;

													// Greater than or equal
													case '>=' :

														$result_single = ($if_boolean['value_a'] >= $if_boolean['value_b']);
														break;

													// Contains
													case '*=' :

														$result_single = ($if_boolean['value_b_string'] !== '') ? (strpos($if_boolean['value_a_string'], $if_boolean['value_b_string']) !== false) : false;
														break;

													// Does not contain
													case '!*=' :

														$result_single = ($if_boolean['value_b_string'] !== '') ? (strpos($if_boolean['value_a_string'], $if_boolean['value_b_string']) === false) : false;
														break;

													// Starts with
													case '^=' :

														$result_single = ($if_boolean['value_b_string'] !== '') ? (strpos($if_boolean['value_a_string'], $if_boolean['value_b_string']) === 0) : false;
														break;

													// Does not start with
													case '!^=' :

														$result_single = ($if_boolean['value_b_string'] !== '') ? (strpos($if_boolean['value_a_string'], $if_boolean['value_b_string']) > 0) : false;
														break;

													// Ends with
													case '$=' :

														$result_single = ($if_boolean['value_b_string'] !== '') ? (strpos($if_boolean['value_a_string'], $if_boolean['value_b_string']) === (strlen($if_boolean['value_a_string']) - strlen($if_boolean['value_b_string']))) : false;
														break;

													// Does not end with
													case '!$=' :

														$result_single = ($if_boolean['value_b_string'] !== '') ? (strpos($if_boolean['value_a_string'], $if_boolean['value_b_string']) !== (strlen($if_boolean['value_a']) - strlen($if_boolean['value_b_string']))) : false;
														break;
												}

												$result = ($if_boolean_array_index == 0) ? $result_single : (($logic_next == '&&') ? ($result && $result_single) : ($result || $result_single));

												$logic_next = isset($if_boolean['logic_next']) ? $if_boolean['logic_next'] : '&&';
											}

											$parsed_variable = false;

											if($result) {

												// Remove the endif
												$parse_string = substr($parse_string, 0, $endif_position) . substr($parse_string, $endif_position + 6);

												// Remove the if
												$parse_string = substr($parse_string, 0, $variable_index_of) . substr($parse_string, $variable_index_of_bracket_finish + 1);

											} else {

												// Remove from if to endif
												$parse_string = substr($parse_string, 0, $variable_index_of) . substr($parse_string, $endif_position + 6);
											}

											$variable_index_start = 0;

											break;

										case 'section_rows_start' :

											if($submit === false) { break; }

											if(!is_numeric($variable_attribute_array[0])) { break; }

											$section_rows_end_position = self::closing_string_index($parse_string, '#section_rows_end', '#section_rows_start', $variable_index_of_bracket_finish);

											if($section_rows_end_position === false) {

												self::throw_error(sprintf(__('Syntax error, missing #section_rows_end for #section_rows_start(%s)', 'ws-form'), $variable_attribute_string));
											}

											// Get section_id
											$section_id = absint($variable_attribute_array[0]);
											if($section_id === 0) { break; }

											// Get string to loop
											$parse_string_loop = substr($parse_string, $variable_index_of_bracket_finish + 1, $section_rows_end_position - ($variable_index_of_bracket_finish + 1));

											// Unserialize section_repeatable
											$section_repeatable = is_serialized($submit->section_repeatable) ? unserialize($submit->section_repeatable) : false;

											// Build section ID string
											$section_id_string = 'section_' . $section_id;
											$section_repeatable_array = (

												($section_repeatable !== false) &&
												isset($section_repeatable[$section_id_string]) &&
												isset($section_repeatable[$section_id_string]['index'])

											) ? $section_repeatable[$section_id_string]['index'] : [false];

											// Loop through section_repeatable_array
											$parsed_string = '';
											$section_row_number = 1;
											foreach($section_repeatable_array as $section_repeatable_array_index => $section_repeatable_index_row) {

												// Parse at same depth
												$parsed_string .= self::parse_variables_process($parse_string_loop, $form, $submit, $content_type, $scope, $section_repeatable_index_row, $section_row_number++, $exclude_secure_nested_parse, $action_config, $depth);
											}

											// Remove from section_rows_start to section_rows_end
											$parse_string = substr($parse_string, 0, $variable_index_of) . $parsed_string . substr($parse_string, $section_rows_end_position + 17);

											$variable_index_start = 0;

											break;

										case 'section_row_count' :

											if($submit === false) { break; }

											if(!is_numeric($variable_attribute_array[0])) { break; }

											// Get section_id
											$section_id = absint($variable_attribute_array[0]);
											if($section_id === 0) { break; }

											// Unserialize section_repeatable
											$section_repeatable = is_serialized($submit->section_repeatable) ? unserialize($submit->section_repeatable) : false;

											// Build section ID string
											$section_id_string = 'section_' . $section_id;

											// Get row count
											$parsed_variable = (

												($section_repeatable !== false) &&
												isset($section_repeatable[$section_id_string]) &&
												isset($section_repeatable[$section_id_string]['index']) &&
												is_array($section_repeatable[$section_id_string]['index'])

											) ? count($section_repeatable[$section_id_string]['index']) : 0;

											break;
										case 'tab_label' :

											if(!is_numeric($variable_attribute_array[0])) { break; }

											// Get group_id
											$group_id = absint($variable_attribute_array[0]);
											if($group_id === 0) { break; }

											// Get groups
											$groups = self::get_groups_from_form($form);

											if(
												isset($groups[$group_id]) &&
												isset($groups[$group_id]->label)
											) {

												$parsed_variable = self::parse_variables_process($groups[$group_id]->label, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											} else {

												self::throw_error(sprintf(__('Syntax error, invalid group ID in #group_label(%u)', 'ws-form'), $group_id));
											}

											break;

										case 'section_label' :

											if(!is_numeric($variable_attribute_array[0])) { break; }

											// Get section_id
											$section_id = absint($variable_attribute_array[0]);
											if($section_id === 0) { break; }

											// Get sections
											$sections = self::get_sections_from_form($form);

											if(
												isset($sections[$section_id]) &&
												isset($sections[$section_id]->label)
											) {

												$parsed_variable = self::parse_variables_process($sections[$section_id]->label, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											} else {

												self::throw_error(sprintf(__('Syntax error, invalid section ID in #section_label(%u)', 'ws-form'), $section_id));
											}

											break;

										case 'cookie_get' :

											// Get cookie value
											$parsed_variable = htmlentities(self::cookie_get_raw($variable_attribute_array[0]));

											break;

										case 'field_label' :

											if(!is_numeric($variable_attribute_array[0])) { break; }

											// Get field_id
											$field_id = absint($variable_attribute_array[0]);
											if($field_id === 0) { break; }

											// Get fields
											$fields = self::get_fields_from_form($form);

											if(
												isset($fields[$field_id]) &&
												isset($fields[$field_id]->label)
											) {

												$parsed_variable = self::parse_variables_process($fields[$field_id]->label, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											} else {

												self::throw_error(sprintf(__('Syntax error, invalid field ID in #field_label(%u)', 'ws-form'), $field_id));
											}

											break;

										case 'text' :

											if(isset($variable_attribute_array[0])) {

												$value = $variable_attribute_array[0];

												$parsed_variable = self::parse_variables_process($value, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);
											}

											break;

										case 'field_min_id' :
										case 'field_max_id' :
										case 'field_min_value' :
										case 'field_max_value' :
										case 'field_min_label' :
										case 'field_max_label' :

											$field_ids = [];
											$field_min_id = 0;
											$field_min_value = 0;
											$field_min_label = '';
											$field_max_id = 0;
											$field_max_value = 0;
											$field_max_label = '';
											$variable_attribute_array_index = 0;

											// Get fields
											$fields = self::get_fields_from_form($form);

											// Loop through provided field ID's
											while(isset($variable_attribute_array[$variable_attribute_array_index])) {

												// Get field ID
												$field_id = absint($variable_attribute_array[$variable_attribute_array_index]);
												if(empty($field_id)) { $variable_attribute_array_index++; continue; }

												// Get field
												if(!isset($fields[$field_id])) { break; }
												$field = $fields[$field_id];

												// Get meta key
												$meta_key = WS_FORM_FIELD_PREFIX . $field_id;
												if(
													($section_repeatable_index !== false) &&
													!empty($field->section_repeatable)
												) {

													$meta_key .= '_' . $section_repeatable_index;
												}

												if(isset($submit->meta[$meta_key])) {

													// Get value
													$meta = $submit->meta[$meta_key];

													// Get delimiter
													$delimiter = isset($variable_attribute_array[1]) ? $variable_attribute_array[1] : NULL;

													// Get column
													$column_override = isset($variable_attribute_array[2]) ? $variable_attribute_array[2] : false;

													$field_value = self::parse_variables_meta_value($form, $meta, $content_type, false, 'parse_variable', 0, NULL, false, false, false, false);

												} else {

													$field_value = 0;
												}

												/// Get field label
												$field_label = $field->label;

												// Set min and max
												if($variable_attribute_array_index == 0) {

													// Initial values
													$field_max_id = $field_min_id = $field_id;
													$field_max_value = $field_min_value = $field_value;
													$field_max_label = $field_min_label = $field_label;

												} else {

													// Min / max calculations
													if($field_value < $field_min_value) {

														$field_min_id = $field_id;
														$field_min_value = $field_value;
														$field_min_label = $field_label;
													}

													if($field_value > $field_max_value) {

														$field_max_id = $field_id;
														$field_max_value = $field_value;
														$field_max_label = $field_label;
													}
												}

												// Go to next variable attribute
												$variable_attribute_array_index++;
											}

											switch($parse_variable) {

												case 'field_min_id' :

													$parsed_variable = $field_min_id;
													break;

												case 'field_max_id' :

													$parsed_variable = $field_max_id;
													break;

												case 'field_min_value' :

													$parsed_variable = $field_min_value;
													break;

												case 'field_max_value' :

													$parsed_variable = $field_max_value;
													break;

												case 'field_min_label' :

													$parsed_variable = $field_min_label;
													break;

												case 'field_max_label' :

													$parsed_variable = $field_max_label;
													break;
											}

											break;

										case 'date_format' :

											// Parse date
											$date_input = self::parse_variables_process($variable_attribute_array[0], $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											// Parse date format
											$date_format = self::parse_variables_process($variable_attribute_array[1], $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											// Get time
											$time_input = strtotime($date_input);

											// Check time
											if($time_input === false) {

												self::throw_error(sprintf(__('Syntax error, invalid input date: %s', 'ws-form'), $date_input));
											}

											// Process date
											$parsed_variable = gmdate($date_format, $time_input);

											break;

										case 'field' :
										case 'field_float' :
										case 'field_date_format' :
										case 'field_date_offset' :
										case 'ecommerce_field_price' :

											if(!isset($submit->meta)) { break; }

											if(!is_numeric($variable_attribute_array[0])) { break; }

											$field_id = $variable_attribute_array[0];

											// Get fields
											$fields = self::get_fields_from_form($form);

											// Get field
											if(!isset($fields[$field_id])) { break; }
											$field = $fields[$field_id];

											// Get field type
											if(!isset($field->type)) { break; }
											$field_type = $field->type;

											// Get field types in single dimension array
											$field_types = self::get_field_types();

											// Get field config
											if(!isset($field_types[$field_type])) { break; }
											$field_type_config = $field_types[$field_type];

											// WPAutoP?
											$wpautop = self::wpautop_parse_variable($field, $field_type_config);
											$html_encode = !($wpautop && ($content_type == 'text/html'));

											// Get meta key
											$meta_key = WS_FORM_FIELD_PREFIX . $field_id;
											if(
												($section_repeatable_index !== false) &&
												!empty($field->section_repeatable)
											) {

												$meta_key .= '_' . $section_repeatable_index;
											}

											if(isset($submit->meta[$meta_key])) {

												// Get value
												$meta = $submit->meta[$meta_key];

												// Get delimiter
												$delimiter = isset($variable_attribute_array[1]) ? $variable_attribute_array[1] : NULL;

												// Get column
												$column_override = isset($variable_attribute_array[2]) ? $variable_attribute_array[2] : false;

												$value = self::parse_variables_meta_value($form, $meta, $content_type, $html_encode, 'parse_variable', 0, $delimiter, false, false, false, $column_override);

											} else {

												// No submitted value, get static
												$value = '';

												// Check for static fields
												$field_static = isset($field_type_config['static']) ? $field_type_config['static'] : false;

												if($field_static) {

													if($field_static === true) {

														// If static set to true, we use the mask_field
														$value = isset($field_type_config['mask_field_static']) ? $field_type_config['mask_field_static'] : '';

													} else {

														// Get value
														$value = self::get_object_meta_value($field, $field_static, '');
													}
												}
											}

											$parsed_variable = self::parse_variables_process($value, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											switch($parse_variable) {

												case 'ecommerce_field_price' :

													$parsed_variable = self::get_price(self::get_number($parsed_variable, 0, true));
													break;

												case 'field_date_format' :

													if($field_type !== 'datetime') { break; }

													// Get date/time type
													$input_type_datetime = self::get_object_meta_value($field, 'input_type_datetime', 'date');

													// Get input date
													$parsed_variable_date = self::get_date_by_type($parsed_variable, (object) $field, 'Y-m-d H:i:s');

													// Ensure parsed_variable_date is a date
													if($parsed_variable_date !== false) {

														// Check for format
														if(
															isset($variable_attribute_array[1]) &&
															($variable_attribute_array[1] != '')
														) {

															$format_date = $variable_attribute_array[1];

														} else {

															// Get date format
															$format_date = self::get_object_meta_value($field, 'format_date_input', get_option('date_format'));
														}

														if(empty($format_date)) { $format_date = get_option('date_format'); }

														// Process date
														$parsed_variable = gmdate($format_date, strtotime($parsed_variable_date));
													}

													break;

												case 'field_date_offset' :

													if($field_type !== 'datetime') { break; }

													// Get date/time type
													$input_type_datetime = self::get_object_meta_value($field, 'input_type_datetime', 'date');

													// Get input date
													$parsed_variable_date = self::get_date_by_type($parsed_variable, (object) $field, 'Y-m-d H:i:s');

													// Ensure parsed_variable_date is a date
													if($parsed_variable_date !== false) {

														// Check for format
														if(
															isset($variable_attribute_array[2]) &&
															($variable_attribute_array[2] != '')
														) {

															$format_date = $variable_attribute_array[2];

														} else {

															// Get date format
															$format_date = self::get_object_meta_value($field, 'format_date_input', get_option('date_format'));
														}

														if(empty($format_date)) { $format_date = get_option('date_format'); }

														// Check for offset
														$seconds_offset = intval(self::parse_variables_process($variable_attribute_array[1], $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1));

														// Process date
														$parsed_variable = gmdate($format_date, strtotime($parsed_variable_date) + $seconds_offset);
													}

													break;

												case 'field_float' :

													$parsed_variable = self::get_number($parsed_variable, 0, true);
													break;
											}

											// WPAutoP
											if($wpautop && ($content_type == 'text/html')) { $parsed_variable = wpautop($parsed_variable); }

											break;

										case 'post_meta' :

											if(is_null($post)) { break; }

											$meta_key = $variable_attribute_array[0];

											$parsed_variable = get_post_meta($post->ID, $meta_key, true);
											if($parsed_variable === false) { $parsed_variable = ''; }

											if(is_array($parsed_variable)) { $parsed_variable = serialize($parsed_variable); }

											break;

										case 'user_meta' :

											// Check we have user data
											if(($user === false) || !$user->ID) { break; }

											$meta_key = $variable_attribute_array[0];

											$parsed_variable = get_user_meta($user->ID, $meta_key, true);
											if(is_array($parsed_variable)) { $parsed_variable = serialize($parsed_variable); }

											break;

										case 'ecommerce_price' :

											$value = $variable_attribute_array[0];

											$parsed_variable = self::parse_variables_process($value, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											$parsed_variable = self::get_price(self::get_number($parsed_variable, 0, true));

											break;

										case 'number_format' :

											// Get num
											$num = $variable_attribute_array[0];

											$num = self::parse_variables_process($num, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											$num = self::get_number($num, 0, true);

											// Get decimals
											$decimals = $variable_attribute_array[1];

											// Get decimal separator
											$decimal_separator = $variable_attribute_array[2];

											// Get thousands separator
											$thousands_separator = $variable_attribute_array[3];

											// Format
											$parsed_variable = number_format($num, $decimals, $decimal_separator, $thousands_separator);

											break;

										case 'select_option_text' :
										case 'checkbox_label' :
										case 'radio_label' :

											if(!isset($submit->meta)) { break; }

											if(!is_numeric($variable_attribute_array[0])) { break; }

											$field_id = $variable_attribute_array[0];
											$datagrid_delimiter = isset($variable_attribute_array[1]) ? $variable_attribute_array[1] : NULL;

											// Get meta key
											$meta_key = WS_FORM_FIELD_PREFIX . $field_id;
											if($section_repeatable_index !== false) {

												$meta_key .= '_' . $section_repeatable_index;
											}

											if(!isset($submit->meta[$meta_key])) { break; }

											// Get value
											$meta = $submit->meta[$meta_key];
											$value = self::parse_variables_meta_value($form, $meta, $content_type, true, 'label', 0, $datagrid_delimiter);

											$parsed_variable = self::parse_variables_process($value, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											break;

										case 'acf_repeater_field' :

											if(is_null($post)) { break; }

											$parent_field = $variable_attribute_array[0];
											$sub_field = $variable_attribute_array[1];

											$parent_field_array = explode(',', $parent_field);
											foreach($parent_field_array as $key => $value) {

												$parent_field_array[$key] = trim($value);
											}

											$parsed_variable = WS_Form_ACF::acf_repeater_field_walker($parent_field_array, $sub_field, $post);

											break;

										// Post and server date custom
										case 'post_date_custom' :
										case 'server_date_custom' :

											$seconds_offset = intval(isset($variable_attribute_array[1]) ? self::parse_variables_process($variable_attribute_array[1], $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1) : 0);

											$parsed_variable = gmdate($variable_attribute_array[0], strtotime($parse_variable_value) + $seconds_offset);

											if($content_type == 'text/html') { $parsed_variable = htmlentities($parsed_variable); }

											break;

										// Submit date added custom
										case 'submit_date_added_custom' :

											$date_format = isset($variable_attribute_array[0]) ? $variable_attribute_array[0] : '';

											$submit_date_added = self::get_object_property($submit, 'date_added', '');

											$parsed_variable = !empty($submit_date_added) ? get_date_from_gmt($submit_date_added, $date_format) : '';

											if($content_type == 'text/html') { $parsed_variable = htmlentities($parsed_variable); }

											break;

										// Random number
										case 'random_number' :

											$random_number_min = intval($variable_attribute_array[0]);
											$random_number_max = intval($variable_attribute_array[1]);
											$parsed_variable = wp_rand($random_number_min, $random_number_max);
											break;

										// Random string
										case 'random_string' :

											$random_string_length = absint($variable_attribute_array[0]);
											$random_string_characters = $variable_attribute_array[1];
											$random_string_character_length = strlen($random_string_characters) - 1;
											$parsed_variable = '';
											for($random_string_index = 0; $random_string_index < $random_string_length; $random_string_index++) { $parsed_variable .= $random_string_characters[wp_rand(0, $random_string_character_length)]; }
											break;

										// Date
										case 'blog_date_custom' :

											$seconds_offset = intval(isset($variable_attribute_array[1]) ? self::parse_variables_process($variable_attribute_array[1], $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1) : 0);

											$parsed_variable = gmdate($variable_attribute_array[0], current_time('timestamp') + $seconds_offset);

											if($content_type == 'text/html') { $parsed_variable = htmlentities($parsed_variable); }

											break;

										// User
										case 'user_lost_password_url' :

											// Check we have user data
											if(($user === false) || !$user->ID) { break; }

											// Check we can produce a lost password URL
											if(!(

												isset($user->lost_password_key) && 
												($user->lost_password_key != '') && 
												isset($user->user_login) && 
												($user->user_login != '')

											)) { break; }

											// Get path
											$path = $variable_attribute_array[0];

											if($path !== '') {

												$parsed_variable = network_site_url(sprintf('%s?key=%s&login=%s', $path, rawurlencode($user->lost_password_key),rawurlencode($user->user_login)));

											} else {

												$parsed_variable = network_site_url(sprintf('wp-login.php?action=rp&key=%s&login=%s', rawurlencode($user->lost_password_key), rawurlencode($user->user_login)), 'login');
											}

											break;

										// Uppercase
										case 'upper' :
										case 'lower' :
										case 'ucwords' :
										case 'ucfirst' :
										case 'capitalize' :
										case 'sentence' :
										case 'wpautop' :
										case 'trim' :
										case 'slug' :
										case 'hash_md5' :
										case 'hash_sha256' :

											// Get input value
											$input_value = self::parse_variables_process($variable_attribute_array[0], $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);

											switch($parse_variable) {

												// Uppercase
												case 'upper' :

													$parsed_variable = strtoupper($input_value);
													break;

												// Lowercase
												case 'lower' :

													$parsed_variable = strtolower($input_value);
													break;

												// Uppercase words
												case 'ucwords' :

													$parsed_variable = ucwords($input_value);
													break;

												// Uppercase first
												case 'ucfirst' :

													$parsed_variable = ucfirst($input_value);
													break;

												// Capitalize
												case 'capitalize' :

													$parsed_variable = ucwords(strtolower($input_value));
													break;

												// Sentence
												case 'sentence' :

													$parsed_variable = ucfirst(strtolower($input_value));
													break;

												// WPAutoP
												case 'wpautop' :

													$parsed_variable = wpautop($input_value);
													break;

												// Trim
												case 'trim' :

													$parsed_variable = trim($input_value);
													break;

												// Slug
												case 'slug' :

													$parsed_variable = sanitize_title($input_value);
													break;

												// Hash - MD5
												case 'hash_md5' :

													$parsed_variable = md5($input_value);
													break;

												// Hash - SHA-256
												case 'hash_sha256' :

													$parsed_variable = hash('sha256', $input_value);
													break;
											}

											break;
									}

									// Assign value
									if($parsed_variable !== false) {

										if($parse_variable_single_parse) {

											$variables_single_parse[substr($parse_variable_full, 1)] = $parsed_variable;

										} else {

											$variables[substr($parse_variable_full, 1)] = $parsed_variable;
										}
									}

								} while ($variable_index_of !== false);

								// Secure variables
								if($exclude_secure) { $variables = self::parse_variables_exclude_secure($variables); }

								// Parse function
								$parse_string = self::mask_parse($parse_string, $variables);
							}
					}
				}
			}

			// Release memory
			$parse_variables_config = null;

			if($scope === false) {

				// Blog
				if(strpos($parse_string, 'blog') !== false) {

					$variables['blog_admin_email'] = get_bloginfo('admin_email');
				}

				// Seconds
				if(strpos($parse_string, 'seconds_epoch') !== false) {

					$variables['seconds_epoch'] = gmdate('U');
				}

				// Form
				if(strpos($parse_string, 'form') !== false) {

					$variables['form_label'] = self::get_object_property($form, 'label', '');
					$variables['form_id'] = self::get_object_property($form, 'id', '');
					$variables['form_checksum'] = self::get_object_property($form, 'published_checksum', '');

					// These variables are only available on the public side
					$variables['form_obj_id'] = '';
					$variables['form_framework'] = '';
					$variables['form_instance_id'] = 0;
				}

				// Section
				if(strpos($parse_string, 'section') !== false) {

					$variables['section_row_index'] = $section_repeatable_index;
					$variables['section_row_number'] = $section_row_number;
				}

				// Post
				if(strpos($parse_string, 'post') !== false) {

					$post_not_null = !is_null($post);

					$variables['post_id'] = ($post_not_null ? $post->ID : '');
					$variables['post_type'] = ($post_not_null ? $post->post_type : '');
					$variables['post_title'] = ($post_not_null ? $post->post_title : '');
					$variables['post_name'] = ($post_not_null ? $post->post_name : '');
					$variables['post_content'] = ($post_not_null ? $post->post_content : '');
					$variables['post_excerpt'] = ($post_not_null ? $post->post_excerpt : '');
					$variables['post_status'] = ($post_not_null ? $post->post_status : '');
					$variables['post_url'] = ($post_not_null ? get_permalink($post->ID) : '');
					$variables['post_url_edit'] = ($post_not_null ? get_edit_post_link($post->ID) : '');
					$variables['post_date'] = ($post_not_null ? gmdate(get_option('date_format'), strtotime($post->post_date)) : '');
					$variables['post_time'] = ($post_not_null ? gmdate(get_option('time_format'), strtotime($post->post_date)) : '');
				}

				// User
				if(strpos($parse_string, 'user') !== false) {

					$user_id = (($user === false) ? 0 : $user->ID);

					$variables['user_id'] = $user_id;
					$variables['user_login'] = (($user_id > 0) ? $user->user_login : '');
					$variables['user_nicename'] = (($user_id > 0) ? $user->user_nicename : '');
					$variables['user_email'] = (($user_id > 0) ? $user->user_email : '');
					$variables['user_display_name'] = (($user_id > 0) ? $user->display_name : '');
					$variables['user_url'] = (($user_id > 0) ? $user->user_url : '');
					$variables['user_registered'] = (($user_id > 0) ? $user->user_registered : '');
					$variables['user_first_name'] = (($user_id > 0) ? get_user_meta($user_id, 'first_name', true) : '');
					$variables['user_last_name'] = (($user_id > 0) ? get_user_meta($user_id, 'last_name', true) : '');
					$variables['user_bio'] = (($user_id > 0) ? get_user_meta($user_id, 'description', true) : '');
					$variables['user_nickname'] = (($user_id > 0) ? get_user_meta($user_id, 'nickname', true) : '');
					$variables['user_admin_color'] = (($user_id > 0) ? get_user_meta($user_id, 'admin_color', true) : '');
					$variables['user_lost_password_key'] = (($user_id > 0) ? $user->lost_password_key : '');
				}

				// Author
				if(strpos($parse_string, 'author') !== false) {

					$post_author_id = !is_null($post) ? $post->post_author : 0;

					$variables['author_id'] = $post_author_id;
					$variables['author_display_name'] = get_the_author_meta('display_name', $post_author_id);
					$variables['author_first_name'] = get_the_author_meta('first_name', $post_author_id);
					$variables['author_last_name'] = get_the_author_meta('last_name', $post_author_id);
					$variables['author_nickname'] = get_the_author_meta('nickname', $post_author_id);
					$variables['author_email'] = get_the_author_meta('user_email', $post_author_id);
				}

				// Submit
				if(
					(strpos($parse_string, 'submit') !== false) &&
					is_object($submit)
				) {

					$submit_id = self::get_object_property($submit, 'id', '');
					$submit_hash = self::get_object_property($submit, 'hash', '');
					$submit_token = self::get_object_property($submit, 'token', '');
					$submit_date_added = self::get_object_property($submit, 'date_added', '');

					$variables['submit_id'] = $submit_id;
					$variables['submit_user_id'] = self::get_object_property($submit, 'user_id', '');
					$variables['submit_date_added'] = !empty($submit_date_added) ? get_date_from_gmt($submit_date_added, sprintf(

						'%s %s',
						get_option('date_format'),
						get_option('time_format')
					)) : '';
					$variables['submit_hash'] = $submit_hash;
					$variables['submit_status'] = self::get_object_property($submit, 'status', '');
					$variables['submit_status_label'] = WS_Form_Submit::db_get_status_name(self::get_object_property($submit, 'status', ''));

					// Get form ID
					$form_id = absint(self::get_object_property($form, 'id', ''));

					// Build submit admin URL and link
					if($form_id && $submit_id) {

						$submit_admin_url = get_admin_url(null, 'admin.php?page=ws-form-submit&id=' . $form_id . '#' . $submit_id);

						$variables['submit_admin_url'] = $submit_admin_url;
						$variables['submit_admin_link'] = sprintf('<a href="%1$s" target="_blank">%1$s</a>', $submit_admin_url);
					}

					// Build submit URL and link
					if($form_id && $submit_hash) {

						$referrer = self::get_referrer();;

						$wsf_hash = rawurlencode(wp_json_encode(array(

							// Save single submit hash
							array('id' => $form_id, 'hash' => $submit_hash, 'token' => $submit_token)
						)));

						$submit_url = add_query_arg('wsf_hash', $wsf_hash, $referrer);

						$variables['submit_url'] = $submit_url;
						$variables['submit_url_hash'] = $wsf_hash;
						$variables['submit_link'] = sprintf('<a href="%1$s" target="_blank">%1$s</a>', $submit_url);
					}
				}

				// URL
				if(strpos($parse_string, 'url') !== false) {
					
					// URL
					if(strpos($parse_string, 'url_login') !== false) { $variables['url_login'] = wp_login_url(); }
					if(strpos($parse_string, 'url_logout') !== false) { $variables['url_logout'] = wp_logout_url(); }
					if(strpos($parse_string, 'url_lost_password') !== false) { $variables['url_lost_password'] = wp_lostpassword_url(); }
					if(strpos($parse_string, 'url_register') !== false) { $variables['url_register'] = wp_registration_url(); }
				}

				// E-Mail
				if(strpos($parse_string, 'email') !== false) {

					$variables['email_promo'] = sprintf(

						/* translators: %s = WS Form */
						__('Powered by %s.', 'ws-form'),

						sprintf(($content_type == 'text/html') ? '<a href="%s" style="color: #999999; font-size: 12px; text-align: center; text-decoration: none;">WS Form</a>' : 'WS Form %s', self::get_plugin_website_url('', 'email_footer'))
					);
				}

				// E-mail - CSS
				if(strpos($parse_string, 'email_css') !== false) {

					$ws_form_css = new WS_Form_CSS();
					$css = $ws_form_css->get_email();
					$variables['email_css'] = $css;
				}

				// E-mail - E-Commerce
				if(strpos($parse_string, 'email_ecommerce') !== false) {

					$variables['email_ecommerce'] = self::parse_variables_ecommerce($submit);
				}

				// E-Commerce
				if(strpos($parse_string, 'ecommerce') !== false) {

					// Get e-commerce config
					$ecommerce_config = WS_Form_Config::get_ecommerce();

					foreach($ecommerce_config['cart_price_types'] as $meta_key => $cart_price_type) {

						$meta_key = 'ecommerce_cart_' . $meta_key;

						if(strpos($parse_string, $meta_key) !== false) {

							$value = isset($submit->meta[$meta_key]) ? $submit->meta[$meta_key] : false;

							if(($value !== '') && ($value !== false)) {

								$variables[$meta_key] = $value;
								$variables[$meta_key . '_currency'] = self::get_price($value);

							} else {

								$variables[$meta_key] = self::get_price(0);
							}
						}
					}

					foreach($ecommerce_config['meta_keys'] as $meta_key => $meta_key_config) {

						if(strpos($parse_string, $meta_key) !== false) {

							$value = isset($submit->meta[$meta_key]) ? $submit->meta[$meta_key] : false;

							if(($value !== '') && ($value !== false)) {

								// Lookup?
								if(
									(isset($meta_key_config['lookup'])) &&
									(isset($ecommerce_config[$meta_key_config['lookup']])) &&
									(isset($ecommerce_config[$meta_key_config['lookup']][$value]))
								) {

									$value = $ecommerce_config[$meta_key_config['lookup']][$value]['label'];

								}

								// Type
								if(isset($meta_key_config['type'])) {

									switch($meta_key_config['type']) {

										case 'price' : $variables[$meta_key . '_currency'] = self::get_price($value); break;
									}
								}

								$variables[$meta_key] = $value;
							}
						}
					}
				}

				// Tracking
				if(strpos($parse_string, 'tracking') !== false) {

					// Get tracking data
					$tracking_data = self::get_tracking_data($form, $submit, $content_type);

					foreach($tracking_data as $tracking) {

						// Add to variables 
						$variables[$tracking['meta_key']] = $tracking['meta_value'];
					}
				}
			}

			// Variables filter
			$variables = apply_filters('wsf_parse_variables', $variables, $parse_string, $form, $submit, $content_type);

			// Secure variables
			if($exclude_secure) { $variables = self::parse_variables_exclude_secure($variables); }

			// Parse until no more changes made
			$parse_string_before = $parse_string;
			$parse_string = self::mask_parse($parse_string, $variables);
			$parse_string = self::mask_parse($parse_string, $variables_single_parse, '#', true);
			$parse_string = apply_filters('wsf_config_parse_string', $parse_string);

			if(
				($parse_string !== $parse_string_before) &&
				(strpos($parse_string, '#') !== false)
			) {

				$parse_string = self::parse_variables_process($parse_string, $form, $submit, $content_type, $scope, $section_repeatable_index, $section_row_number, $exclude_secure_nested_parse, $action_config, $depth + 1);
			}

			return $parse_string;
		}

		// Blank secure parse variables
		public static function parse_variables_exclude_secure($variables) {

			$parse_variables_secure = WS_Form_Config::get_parse_variables_secure();

			foreach($parse_variables_secure as $parse_variable_key) {

				if(isset($variables[$parse_variable_key])) {

					$variables[$parse_variable_key] = '&num;' . $parse_variable_key;
				}
			}

			return $variables;
		}

		// Parse string to attributes
		public static function string_to_attributes($input_string, $separator = ',') {

			if(
				!is_string($input_string) ||
				($input_string == '')
			) {
				return array();
			}

			$bracket_index = 1;
			$input_string_index = 0;
			$skip_double_quotes = false;
			$attribute_single = '';
			$attribute_array = array();

			// Replace non standard double quotes
			$input_string = str_replace('“', '"', $input_string);
			$input_string = str_replace('”', '"', $input_string);

			// Get input string length
			$input_string_length = strlen($input_string);

			while($input_string_index < $input_string_length) {

				if(!isset($input_string[$input_string_index])) { break; }

				// Get character
				$character = $input_string[$input_string_index];

				if(
					($character === $separator) &&
					($bracket_index === 1) &&
					!$skip_double_quotes
				) {

					// Create attribute
					$attribute_array[] = $attribute_single;
					$attribute_single = '';

					// Jump to next $character
					$input_string_index++;

					continue;
				}

				// If double quotes that are not in another function
				if(
					($character === '"') &&
					($bracket_index === 1)
				) {

					// Clear $attribute_single if start
					if(!$skip_double_quotes) { $attribute_single = ''; }

					// Toggle skip double quotes
					$skip_double_quotes = !$skip_double_quotes;

					// Jump to next $character
					$input_string_index++;

					continue;
				}

				// If not in double quotes, process brackets
				if(!$skip_double_quotes) {

					switch($character) {

						case '(' : $bracket_index++; break;

						case ')' : $bracket_index--; break;
					}

					if($bracket_index === 0) { break; }
				}

				// Add $character to $attribute_single
				$attribute_single .= $character;

				$input_string_index++;
			}

			$attribute_array[] = $attribute_single;

			// Strip double quotes at the beginning and end of each attribute
			if(count($attribute_array) > 0) {

				$attribute_array = array_map(function($attribute) {

					return preg_replace('/^"(.+(?="$))"$/', '$1', $attribute);

				}, $attribute_array);
			}

			return $attribute_array;
		}

		// Find closing string
		public static function closing_string_index($parse_string, $closing_string, $opening_string, $index) {

			$depth = 1;

			while($depth > 0) {

				// Look for embedded if
				$opening_string_index = strpos($parse_string, $opening_string, $index);
				$closing_string_index = strpos($parse_string, $closing_string, $index);

				// Embedded opening string
				if(
					($opening_string_index !== false) &&
					($closing_string_index !== false) &&
					($opening_string_index < $closing_string_index) 
				) {
					$index = $opening_string_index + strlen($opening_string);
					$depth++;
					continue;
				}

				// Embedded closing string
				if(
					($closing_string_index !== false) &&
					($depth > 1)
				) {
					$index = $closing_string_index + strlen($closing_string);
					$depth--;
					continue;
				}

				// Associated closing string
				if(
					($closing_string_index !== false) &&
					($depth === 1)
				) {
					break;
				}

				break;
			}

			return $closing_string_index;
		}

		// Parse form data for use with parse_variables
		public static function parse_variables_meta_value($form, $meta, $content_type, $html_encode = true, $column = 'parse_variable', $column_id_default = 0, $datagrid_delimiter = NULL, $action_config = false, $field = false, $submit = false, $column_override = false) {

			$type = $meta['type'];
			$value = $meta['value'];

			if($value == '') { return ''; }

			// Content type override
			if($content_type == 'column_value') {

				$content_type = 'text/plain';
				$column = 'value';
			}

			// HTML encode values
			if($content_type == 'text/html' && !is_array($value)) {

				if($html_encode) { $value = htmlentities($value); }

				switch($type) {

					case 'url' :

						$value = sprintf('<a href="%1$s" target="_blank">%1$s</a>', $value);
						break;

					case 'tel' :

						$value = sprintf('<a href="tel:%1$s">%1$s</a>', $value);
						break;

					case 'email' :

						if(filter_var($value, FILTER_VALIDATE_EMAIL)) {

							$value = sprintf('<a href="mailto:%1$s">%1$s</a>', $value);
						}
						break;

					case 'ip' :

						// Get lookup URL mask
						$ip_lookup_url_mask = self::option_get('ip_lookup_url_mask');
						if(empty($ip_lookup_url_mask)) { $value = htmlentities($value); break; }

						// Split IP (IP can be comma separated if proxy in use)
						$ip_array = explode(',', $value);
						$value_array = [];

						foreach($ip_array as $ip) {

							// Trim
							$ip = trim($ip);

							// Validate IP
							if(inet_pton($ip) === false) { continue; }

							// Get #value for mask
							$ip_lookup_url_mask_values = array('value' => $ip);

							// Build lookup URL
							$ip_lookup_url = self::mask_parse($ip_lookup_url_mask, $ip_lookup_url_mask_values);

							$value_array[] = '<a href="' . $ip_lookup_url . '" target="_blank">' . htmlentities($ip) . '</a>';
						}

						$value = implode('<br />', $value_array);

						break;

					case 'latlon' :

						if(preg_match('/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/', $value) == 1) {

							// Get lookup URL mask
							$latlon_lookup_url_mask = self::option_get('latlon_lookup_url_mask');
							if(empty($latlon_lookup_url_mask)) { $value = htmlentities($value); break; }

							// Get #value for mask
							$latlon_lookup_url_mask_values = array('value' => $value);

							// Build lookup URL
							$latlon_lookup_url = self::mask_parse($latlon_lookup_url_mask, $latlon_lookup_url_mask_values);

							$value = '<a href="' . $latlon_lookup_url . '" target="_blank">' . htmlentities($value) . '</a>';

						} else {

							switch(absint($value)) {

								case 1 :

									$value = __('User denied the request for geo location', 'ws-form');
									break;

								case 2 :

									$value = __('Geo location information was unavailable', 'ws-form');
									break;

								case 3 :

									$value = __('The request to get user geo location timed out', 'ws-form');
									break;

								default :

									$value = '-';
							}
						}
						break;
				}
			}

			// Process by field type
			switch($type) {

				case 'file' :
				case 'signature' :

					$files = $meta['value'];

					if(!is_array($files)) { break; }

					// File links? (Email action settings)
					if(
						($action_config !== false) &&
						is_array($action_config) &&
						isset($action_config['meta']) && 
						isset($action_config['meta']['action_email_message_file_links'])
					) {

						$file_links = $action_config['meta']['action_email_message_file_links'];

					} else {

						$file_links = self::option_get('action_email_embed_image_link', false);
					}

					// File embed? (Global setting)
					$file_embed = self::option_get('action_email_embed_images', true);

					// File description? (Global setting)
					$file_description = self::option_get('action_email_embed_image_description', true);

					$value_array = array();

					foreach($files as $file_object_index => $file_object) {

						if(!is_array($file_object)) { continue; }

						// Get file handler
						$file_handler = isset($file_object['handler']) ? $file_object['handler'] : '';
						if($file_handler == '') { $file_handler = 'wsform'; }
						if(!isset(WS_Form_File_Handler::$file_handlers[$file_handler])) { continue; }
						$file_handler = WS_Form_File_Handler::$file_handlers[$file_handler];

						// Get field ID
						$field_id = (($field !== false) && isset($field->id)) ? $field->id : false;

						// Get value array
						if(method_exists($file_handler, 'get_value_parse_variable')) {

							// Get hash
							$hash = (($submit !== false) && isset($submit->hash)) ? $submit->hash : '';

							// Use file handler to get the value for parse variables
							$value_array[] = $file_handler->get_value_parse_variable($file_object, $field_id, $file_object_index, $hash, $file_links, $file_embed, $content_type, $file_description, $type);

						} else {

							// Fallback
							$file_size = self::get_file_size($file_object['size']);
							$value_array[] = sprintf('%s (%s)', $file_object['name'], $file_size);
						}
					}
	
					$value = implode((($content_type == 'text/html') ? '<br />' : "\n"), $value_array);

					break;

				case 'datetime' :

					$fields = self::get_fields_from_form($form);

					if(!isset($fields[$meta['id']])) { break; }

					$field = $fields[$meta['id']];

					// If submit is read from database, it is split into MySQL and presentable formats
					if(is_array($value) && isset($value['mysql'])) { $value = $value['mysql']; }

					$value = self::get_date_by_type($value, (object) $field);

					break;

				case 'googlemap' :

					if(
						is_array($value) &&
						isset($value['lat']) &&
						isset($value['lng'])
					) {

						$value = sprintf('%.7f,%.7f', $value['lat'], $value['lng']);

						if($content_type == 'text/html') {

							// Get lookup URL mask
							$latlon_lookup_url_mask = self::option_get('latlon_lookup_url_mask');
							if(empty($latlon_lookup_url_mask)) { break; }

							// Get #value for mask
							$latlon_lookup_url_mask_values = array('value' => $value);

							// Build lookup URL
							$latlon_lookup_url = self::mask_parse($latlon_lookup_url_mask, $latlon_lookup_url_mask_values);

							$value = '<a href="' . $latlon_lookup_url . '" target="_blank">' . htmlentities($value) . '</a>';
						}

					} else {

						$value = '';
					}

					break;

				case 'price_select' :
				case 'select' :
				case 'price_checkbox' :
				case 'checkbox' :
				case 'price_radio' :
				case 'radio' :

					// If value_array is set, we'll use that in case we have repeaters
					if(isset($meta['value_array'])) {

						$value = $meta['value_array'];
					}

					$fields = self::get_fields_from_form($form);

					if(!isset($fields[$meta['id']])) { break; }

					$field = $fields[$meta['id']];

					if(is_null($datagrid_delimiter)) {

						$delimiter_text_plain = "\n";
						$delimiter_text_html = "<br />";

					} else {

						$delimiter_text_plain = $delimiter_text_html = $datagrid_delimiter;
					}

					// Build default value
					$default_value = is_array($value) ? (($content_type == 'text/html') ? implode($delimiter_text_html, $value) : implode($delimiter_text_plain, $value)) : $value;

					// Get data grid value
					$value = self::get_datagrid_value($field, $value, $content_type, $default_value, $column, $column_id_default, $datagrid_delimiter, $column_override);

					break;

				default :

					$value = is_array($value) ? (($content_type == 'text/html') ? implode("<br />", $value) : implode("\n", $value)) : $value;
			}

			return $value;
		}

		// #email_ecommerce
		public static function parse_variables_ecommerce($submit) {

			$ecommerce_html = '';

			$ecommerce_rows = array();

			// Get e-commerce config
			$ecommerce_config = WS_Form_Config::get_ecommerce();

			foreach($ecommerce_config['cart_price_types'] as $meta_key => $cart_price_type) {

				$meta_key = 'ecommerce_cart_' . $meta_key;

				$value = isset($submit->meta[$meta_key]) ? $submit->meta[$meta_key] : false;

				if(($value !== '') && ($value !== false)) {

					$priority = isset($cart_price_type['priority']) ? $cart_price_type['priority'] : 10000;

					$value = self::get_price($value);
					$ecommerce_rows[] = array('label' => $cart_price_type['label'], 'value' => $value, 'priority' => $priority);

				} else {

					continue;
				}
			}

			foreach($ecommerce_config['meta_keys'] as $meta_key => $meta_key_config) {

				$value = isset($submit->meta[$meta_key]) ? $submit->meta[$meta_key] : false;

				if(($value !== '') && ($value !== false)) {

					$label = $meta_key_config['label'];
					$priority = isset($meta_key_config['priority']) ? $meta_key_config['priority'] : 10000;

					// Lookup?
					if(
						(isset($meta_key_config['lookup'])) &&
						(isset($ecommerce_config[$meta_key_config['lookup']])) &&
						(isset($ecommerce_config[$meta_key_config['lookup']][$value]))
					) {

						$value = $ecommerce_config[$meta_key_config['lookup']][$value]['label'];
					}

					// Type
					if(isset($meta_key_config['type'])) {

						switch($meta_key_config['type']) {

							case 'price' : $value = self::get_price($value); break;
						}
					}

					// Push to e-commerce rows
					$ecommerce_rows[] = array('label' => $label, 'value' => $value, 'priority' => $priority);
				}
			}

			// Sort e-commerce rows by priority
			uasort($ecommerce_rows, function ($ecommerce_rows_1, $ecommerce_rows_2) {

				return ($ecommerce_rows_1['priority'] == $ecommerce_rows_2['priority']) ? 0 : (($ecommerce_rows_1['priority'] < $ecommerce_rows_2['priority']) ? -1 : 1);
			});

			if(count($ecommerce_rows) > 0) {

				foreach($ecommerce_rows as $ecommerce_row) {

					$ecommerce_html .= '<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-bottom: 10px;"><strong>' . htmlentities($ecommerce_row['label']) . '</strong><br />' . $ecommerce_row['value'] . '</p>';
				}
			}
			return $ecommerce_html;
		}
		// #email_submission
		public static function parse_variables_fields_all($form, $submit, $content_type, $render_group_labels, $render_section_labels, $render_field_labels, $render_blank_fields, $render_static_fields, $hidden_array, $action_config) {

			$fields_all = self::parse_variables_fields_all_group($form->groups, $form, $submit, $content_type, $render_group_labels, $render_section_labels, $render_field_labels, $render_blank_fields, $render_static_fields, $hidden_array, $action_config);

			return $fields_all;
		}

		// Run through each group
		public static function parse_variables_fields_all_group($groups, $form, $submit, $content_type, $render_group_labels, $render_section_labels, $render_field_labels, $render_blank_fields, $render_static_fields, $hidden_array, $action_config) {

			$groups_html = '';

			$group_count = count($groups);
			$group_label_join = '';

			foreach($groups as $key => $group) {

				if(isset($groups[$key]->sections)) {

					$sections_html = self::parse_variables_fields_all_section($group->sections, $form, $submit, $content_type, $render_section_labels, $render_field_labels, $render_blank_fields, $render_static_fields, $hidden_array, $action_config);

					// Should label be rendered?
					$render_label =	(
										(
											$render_group_labels == 'true'
										)
										||
										(
											($render_group_labels == 'auto') && 
											($sections_html != '')
										)
									) && self::get_object_meta_value($group, 'label_render');

					if(($group_count > 0) && $render_label) {

						switch($content_type) {

							case 'text/html' :

								$groups_html .= $group_label_join . ($render_label ? '<h2>' . htmlentities($group->label) . "</h2>\n" : '');
								$group_label_join = "<hr style=\"margin: 20px 0\" />\n";
								break;

							default :

								$groups_html .= $group_label_join . "** " . $group->label . " **\n\n";
								$group_label_join = "\n";
						}

					}

					$groups_html .= $sections_html;
				}
			}

			return $groups_html;
		}

		// Run through each section
		public static function parse_variables_fields_all_section($sections, $form, $submit, $content_type, $render_section_labels, $render_field_labels, $render_blank_fields, $render_static_fields, $hidden_array, $action_config) {

			$sections_html = '';

			$section_count = count($sections);
			$section_label_join = '';

			// Unserialize section_repeatable
			$section_repeatable = is_serialized($submit->section_repeatable) ? unserialize($submit->section_repeatable) : false;

			// Get field types in single dimension array
			$field_types = WS_Form_Config::get_field_types_flat();

			foreach($sections as $key => $section) {

				// Build section ID string
				$section_id_string = 'section_' . $section->id;
				$section_repeatable_array = (

					($section_repeatable !== false) &&
					isset($section_repeatable[$section_id_string]) &&
					isset($section_repeatable[$section_id_string]['index'])

				) ? $section_repeatable[$section_id_string]['index'] : [false];

				if(!isset($sections[$key]->fields)) { continue; }

				// Loop through section_repeatable_array
				foreach($section_repeatable_array as $section_repeatable_array_index => $section_repeatable_index) {

					$fields_html = '';

					// Check if repeatable
					$section_repeatable_html = '';
					$section_repeatable_suffix = '';

					// Repeatable, so render fieldset and set field_name suffix
					if($section_repeatable_index !== false) {

						// Repeatable section found
						$section_repeatable_index = absint($section_repeatable_index);
						if($section_repeatable_index === 0) { continue; }

						switch($content_type) {

							case 'text/html' :

								// Render fieldset
								$section_repeatable_html = '<h4>#' . (absint($section_repeatable_array_index) + 1) . "</h4>\n";
								break;

							default :

								$section_repeatable_html = '#' . (absint($section_repeatable_array_index) + 1) . "\n";
						}

						// Set field_name suffix
						$section_repeatable_suffix = '_' . $section_repeatable_index;
					}

					// Process fields
					foreach($section->fields as $field) {

						$field_type = $field->type;
						$field_type_config = $field_types[$field_type];

						// Remove layout editor only fields
						$layout_editor_only = isset($field_type_config['layout_editor_only']) ? $field_type_config['layout_editor_only'] : false;
						if($layout_editor_only) { continue; }

						// Check for excluded fields
						$exclude_email = self::get_object_meta_value($field, 'exclude_email', false);
						if($exclude_email) { continue; }

						// Check for static fields
						$field_static = isset($field_type_config['static']) ? $field_type_config['static'] : false;

						if($render_static_fields && $field_static) {

							// do_shortcode (Only on static fields)
							$meta_do_shortcode = isset($field_type_config['meta_do_shortcode']) ? $field_type_config['meta_do_shortcode'] : false;
							if($meta_do_shortcode !== false) {

								if(!is_array($meta_do_shortcode)) { $meta_do_shortcode = array($meta_do_shortcode); }

								foreach($meta_do_shortcode as $meta_do_shortcode_meta_key) {

									// Check meta key exists
									if(
										!isset($field->meta) ||
										!isset($field->meta->{$meta_do_shortcode_meta_key})

									) { continue; }

									// Update form_object
									$field->meta->{$meta_do_shortcode_meta_key} = self::do_shortcode($field->meta->{$meta_do_shortcode_meta_key});
								}
							}

							// Build field name
							$field_name = WS_FORM_FIELD_PREFIX . $field->id;
							if($section_repeatable_index !== false) {

								$field_name = sprintf('%s[%u]', $field_name, $section_repeatable_index);
							}

							// Bypass hidden static elements
							if(in_array($field_name, $hidden_array)) {

								continue;
							}

							if($field_static === true) {

								// If static set to true, we use the mask_field
								$mask_field = isset($field_type_config['mask_field_static']) ? $field_type_config['mask_field_static'] : '';

								if($mask_field == '') {

									$mask_field = isset($field_type_config['mask_field']) ? $field_type_config['mask_field'] : '';
								}

								$fields_html .= self::parse_variables_process($mask_field, $form, $submit, $content_type);

							} else {

								// Get value
								$value = self::get_object_meta_value($field, $field_static, '');

								// WPAutoP?
								$wpautop = self::wpautop_parse_variable($field, $field_type_config);
								if($wpautop && ($content_type == 'text/html')) { $value = wpautop($value); }

								// Get meta value
								$fields_html .= self::parse_variables_process($value, $form, $submit, $content_type);
							}
							continue;
						}

						// Check to ensure this field is saved
						$submit_save = isset($field_type_config['submit_save']) ? $field_type_config['submit_save'] : false;
						if(!$submit_save) { continue; }

						// Get field label
						$label = $field->label;

						// Should label be rendered?
						$render_label =	(

							($render_field_labels == 'true')

							||

							(
								($render_field_labels == 'auto') &&
								self::get_object_meta_value($field, 'label_render')
							)
						);

						// Build field name
						$field_name = WS_FORM_FIELD_PREFIX . $field->id . $section_repeatable_suffix;

						// WPAutoP
						$wpautop = self::wpautop_parse_variable($field, $field_type_config);
						$html_encode = !$wpautop;

						if(isset($submit->meta[$field_name])) {

							// Get submit meta
							$meta = $submit->meta[$field_name];

							// Get field value
							$value = self::parse_variables_meta_value($form, $meta, $content_type, $html_encode, 'parse_variable', 0, NULL, $action_config, $field, $submit);

						} else {

							$value = '';
						}

						// No submit value found
						if($value == '') {

							if($render_blank_fields) {

								$value = '-';

							} else {

								continue;
							}
						}

						// Add to fields_html HTML
						switch($content_type) {

							case 'text/html' :

								// WPAutoP?
								if($wpautop) { $value = wpautop($value); }

								$fields_html .= '<div class="wsf-field">' . ($render_label ? ('<strong>' . htmlentities($label) . '</strong><br />') : '') . $value . "</div>\n";
								break;

							default :

								$fields_html .= ($render_label ? ($label . "\n") : '') . $value . "\n\n";
								break;
						}
					}

					// Should label be rendered?
					$render_label =	(

						($render_section_labels == 'true')

						||

						(
							($render_section_labels == 'auto') &&
							($fields_html != '')
						)
					)
					&& self::get_object_meta_value($section, 'label_render')
					&& ($section_repeatable_array_index == 0);

					// Add section title if fields found
					if($render_label) {

						switch($content_type) {

							case 'text/html' :

								$sections_html .= $render_label ? '<h3>' . htmlentities($section->label) . "</h3>\n" : '';
								break;

							default :

								$sections_html .= $section_label_join . "* " . $section->label . " *\n\n";
								$section_label_join = "\n\n";
						}
					}

					// Add fields
					if($fields_html != '') { $sections_html .= $section_repeatable_html . $fields_html; }
				}
			}

			return $sections_html;
		}

		// WPAutoP
		public static function wpautop_parse_variable($field, $field_type_config) {

			// Check for wpautop do not process
			if(self::get_object_meta_value($field, 'wpautop_do_not_process', '') == 'on') { return false; }

			// Meta wpautop_parse_variable
			$wpautop_parse_variable = isset($field_type_config['wpautop_parse_variable']) ? $field_type_config['wpautop_parse_variable'] : false;

			if(is_array($wpautop_parse_variable)) {

				$condition_output = false;

				foreach($wpautop_parse_variable as $condition) {

					if(self::get_object_meta_value($field, $condition['meta_key'], '') === $condition['meta_value']) {

						$condition_output = true;
					}
				}

				$wpautop_parse_variable = $condition_output;
			}

			return $wpautop_parse_variable;
		}

		// Get value label lookup
		public static function get_datagrid_value($field, $value_array, $content_type, $default_value, $column = 'parse_variable', $column_id_default = 0, $datagrid_delimiter = NULL, $column_override = false) {

			$return_array = array();

			if(!is_array($value_array)) { return $default_value; }

			// Get meta key prefix
			$meta_key_prefix = $field->type;

			// Apply fix to meta key prefix
			switch($meta_key_prefix) {

				case 'price_select' :

					$meta_key_prefix = 'select_price';
					break;

				case 'price_checkbox' :

					$meta_key_prefix = 'checkbox_price';
					break;

				case 'price_radio' :

					$meta_key_prefix = 'radio_price';
					break;
			}

			// Get data grid
			$datagrid = self::get_object_meta_value($field, sprintf('data_grid_%s', $meta_key_prefix), false);
			if($datagrid === false) { return $default_value; }

			// Get value mapping column ID
			$value_column_id = self::get_object_meta_value($field, sprintf('%s_field_value', $meta_key_prefix), 0);
			$value_column_id = absint($value_column_id);

			// Get column ID to return
			$return_column_id = self::get_object_meta_value($field, sprintf('%s_field_%s', $meta_key_prefix, $column), $column_id_default);
			$return_column_id = absint($return_column_id);

			// Preparsing columns
			$label_column_id = self::get_object_meta_value($field, sprintf('%s_field_label', $meta_key_prefix), 0);
			$label_column_id = absint($label_column_id);

			$price_column_id = self::get_object_meta_value($field, sprintf('%s_field_price', $meta_key_prefix), 0);
			$price_column_id = absint($price_column_id);

			$parse_variable_column_id = self::get_object_meta_value($field, sprintf('%s_field_parse_variable', $meta_key_prefix), 0);
			$parse_variable_column_id = absint($parse_variable_column_id);

			$wc_column_id = self::get_object_meta_value($field, sprintf('%s_field_wc', $meta_key_prefix), 0);
			$wc_column_id = absint($wc_column_id);

			// Get data grid columns
			if(!isset($datagrid->columns)) { return $default_value; }
			if(!is_array($datagrid->columns)) { return $default_value; }
			$columns = $datagrid->columns;

			// Get return_column_index from return_column_id
			$return_column_index = false;
			$value_column_index = false;
			$label_column_index = false;
			$price_column_index = false;
			$parse_variable_column_index = false;
			$wc_column_index = false;

			// If column_override specified
			if($column_override !== false) {

				$return_column_index = absint($column_override);
			}

			foreach($columns as $index => $column) {

				// Check
				if(!isset($column->id)) { continue; }

				// Set indexes if column ID found
				if($column_override === false) {

					if($column->id === $return_column_id) { $return_column_index = $index; }

				} else {

					if($column->label == $column_override) { $return_column_index = $index; }
				}

				if($column->id === $value_column_id) { $value_column_index = $index; }
				if($column->id === $label_column_id) { $label_column_index = $index; }
				if($column->id === $price_column_id) { $price_column_index = $index; }
				if($column->id === $parse_variable_column_id) { $parse_variable_column_index = $index; }
				if($column->id === $wc_column_id) { $wc_column_index = $index; }
			}

			// Check that we got indexes back
			if(
				($return_column_index === false) ||
				($value_column_index === false)
			) {
				return $default_value;
			}

			// Get data grid rows
			if(!isset($datagrid->groups)) { return $default_value; }
			if(!is_array($datagrid->groups)) { return $default_value; }
			$groups = $datagrid->groups;

			foreach($groups as $group) {

				if(!isset($group->rows)) { continue; }
				if(!is_array($group->rows)) { continue; }

				$rows = $group->rows;

				foreach($rows as $row) {

					// Get data
					if(!isset($row->data)) { continue; }
					if(!is_array($row->data)) { continue; }
					$data = $row->data;

					// Check value and return indexes exist
					if(!isset($data[$value_column_index])) { continue; }
					if(!isset($data[$return_column_index])) { continue; }

					// Pre-parsing
					$mask_values_row = array(

						'data_grid_row_price' => '',
						'data_grid_row_price_currency' => '',
						'data_grid_row_woocommerce_cart' => '',
						'data_grid_row_value' => $data[$value_column_index],
						'data_grid_row_action_variable' => '',
						'data_grid_row_label' => ''
					);

					// Label
					if(
						($label_column_index !== false) &&
						isset($data[$label_column_index])
					) {

						$mask_values_row['data_grid_row_label'] = $data[$label_column_index];
					}

					// Price
					if(
						($price_column_index !== false) &&
						isset($data[$price_column_index])
					) {

						$mask_values_row['data_grid_row_price_currency'] = self::get_price($data[$price_column_index]);
						$mask_values_row['data_grid_row_price'] = self::get_number($data[$price_column_index]);
					}
					// Parse Variable
					if(
						($parse_variable_column_index !== false) &&
						isset($data[$parse_variable_column_index])
					) {

						$mask_values_row['data_grid_row_action_variable'] = $data[$parse_variable_column_index];
					}

					// WooCommerce
					if(
						($wc_column_index !== false) &&
						isset($data[$wc_column_index])
					) {

						$mask_values_row['data_grid_row_woocommerce_cart'] = $data[$wc_column_index];
					}
					// Parse columns
					foreach($data as $column_index => $column) {

						$data[$column_index] = self::mask_parse($column, $mask_values_row);
					}

					// Check if value matches
					$value_array_index = array_search($data[$value_column_index], $value_array);

					if($value_array_index !== false) {

						unset($value_array[$value_array_index]);

						$return_array[] = $data[$return_column_index];
					}
				}
			}

			// Add any value_array elements that were not found
			$return_array = array_merge($return_array, $value_array);

			// Return unique values to avoid duplicates if there are duplicate values
			$return_array = array_unique($return_array);

			if(is_null($datagrid_delimiter)) {

				$delimiter_text_plain = "\n";
				$delimiter_text_html = "<br />";

			} else {

				$delimiter_text_plain = $delimiter_text_html = $datagrid_delimiter;
			}

			return (($content_type == 'text/html') ? implode($delimiter_text_html, $return_array) : implode($delimiter_text_plain, $return_array));
		}

		// Check if user is logged in
		public static function logged_in() {

			if(!function_exists('is_user_logged_in')) {

				include(ABSPATH . 'wp-includes/pluggable.php'); 
			}

			return is_user_logged_in();
		}

		// Check if user has WordPress capability
		public static function can_user($capability) {

			if(!function_exists('wp_get_current_user')) {

				include(ABSPATH . 'wp-includes/pluggable.php'); 
			}

			return current_user_can($capability);
		}

		// Check if user has WordPress capability, if not, throw an error 
		public static function user_must($capability, $bypass_user_capability_check = false) {

			// Bypass
			if($bypass_user_capability_check) { return true; }

			// Check if user has capability
			if(self::can_user($capability)) {

				return true;

			} else {

				// Throw error
				throw new Exception(sprintf(__('Insufficient user capabilities (%s)', 'ws-form'), $capability));
			}
		}

		// Loader
		public static function loader() {
?>
<!-- Loader -->
<div id="wsf-loader"></div>
<!-- /Loader -->
<?php
		}

		// Review
		public static function review() {

			// Review nag
			$review_nag = self::option_get('review_nag', false);
			if($review_nag) { return; }

			// Determine if review nag should be shown
			$install_timestamp = absint(self::option_get('install_timestamp', time(), true));
			$review_nag_show = (time() > ($install_timestamp + (WS_FORM_REVIEW_NAG_DURATION * 86400)));
			if(!$review_nag_show) { return; }

			// Show nag
			self::admin_message_render(

				sprintf(

					'<p><strong>%s</strong></p><p>%s</p><p class="buttons"><a href="https://wordpress.org/support/plugin/ws-form/reviews/#new-post" class="button button-primary" onclick="wsf_review_nag_dismiss();" target="_blank">%s</a> <a href="#" class="button" onclick="wsf_review_nag_dismiss();">%s</a></p>',

					sprintf(

						/* translators: %s! = Presentable plugin name, e.g. WS Form PRO */
						__('Thank you for using %s!', 'ws-form'),
						WS_FORM_NAME_PRESENTABLE
					),

					sprintf(

						/* translators: %s! = Presentable plugin name, e.g. WS Form PRO */
						__('We hope you have enjoyed using the plugin. Positive reviews from awesome users like you help others to feel confident about choosing %1$s too. If convenient, we would greatly appreciate you sharing your happy experiences with the WordPress community. Thank you in advance for helping us out!', 'ws-form'),
						WS_FORM_NAME_PRESENTABLE
					),

					__('Leave a review', 'ws-form'),
					__('No thanks', 'ws-form')
				),

				'notice-success',
				false,
				false,
				'wsf-review'
			);
?>
<script>

	function wsf_review_nag_dismiss() {

		(function($) {

			'use strict';

			// Hide nag
			$('.wsf-review').hide();

			// Call AJAX to prevent review nag appearing again
			$.ajax({ method: 'POST', url: '<?php self::echo_esc_html(self::get_api_path('helper/review-nag/dismiss/')); ?>', data: { '_wpnonce': '<?php self::echo_esc_attr(wp_create_nonce('wp_rest')); ?>' } });

		})(jQuery);
	}

</script>
<?php
		}

		// Check edition
		public static function is_edition($edition) {

			switch($edition) {

				case 'basic' :

					return true;

				case 'pro' :

					return true;

				default :

					return false;
			}
		}

		// Build data grid meta
		public static function build_data_grid_meta($meta_key, $group_name = false, $columns = false, $rows = false) {

			// Get base meta
			$meta_keys = WS_Form_Config::get_meta_keys_data_grids();

			if(
				!isset($meta_keys[$meta_key]) ||
				!isset($meta_keys[$meta_key]['default'])
			) {
				return false;
			}

			$meta = $meta_keys[$meta_key]['default'];

			if($group_name !== false) { $meta['groups'][0]['label'] = $group_name; }
			if($columns !== false) { $meta['columns'] = $columns; }
			if($rows !== false) { $meta['groups'][0]['rows'] = $rows; }

			return $meta;
		}

		// Get nice file size
		public static function get_file_size($bytes) {

			if($bytes >= 1048576) {

				$bytes = number_format($bytes / 1048576, 2) . ' MB';

			} elseif ($bytes >= 1024) {

				$bytes = number_format($bytes / 1024, 2) . ' KB';

			} elseif ($bytes > 1) {

				$bytes = $bytes . ' bytes';

			} elseif ($bytes == 1) {

				$bytes = $bytes . ' byte';

			} else {

				$bytes = '0 bytes';
			}

			$bytes = str_replace('.00', '', $bytes);

			return $bytes;
		}

		// PHP to MySQL Date Format
		public static function php_to_mysql_date_format($format_string) {

			// Cannot convert: N, z, t, L, o
			$php_to_mysql_date_format_character_array = array(

				'd' => '%d',
				'a' => '%p',
				'D' => '%a',
				'j' => '%e',
				'u' => '%f',
				'W' => '%u',
				'l' => '%W',
				'w' => '%w',
				'M' => '%b',
				'F' => '%M',
				'm' => '%m',
				'n' => '%c',
				'Y' => '%Y',
				'y' => '%y',
				'A' => '%p',
				'g' => '%l',
				'G' => '%k',
				'h' => '%h',
				'H' => '%H',
				'i' => '%i',
				's' => '%S',
			);

			foreach($php_to_mysql_date_format_character_array as $from => $to) {

				$format_string = str_replace($from, $to, $format_string);
			}

			return $format_string;
		}

		// Get m/d/Y formatted date
		public static function get_date_by_site($date) {

			$format_date = get_option('date_format');

			switch($format_date) {

				case 'd/m/Y' :

					$date = str_replace('/', '.', $date);
					break;
			}

			// Strip commas
			$date = str_replace(',', '', $date);

			return $date;
		}

		// Get nice date by type
		public static function get_date_by_type($date, $field_object, $format_output = false) {

			if(empty($date)) { return ''; }
			if(!is_string($date)) { return ''; }

			// Remember date
			$date_source = $date;

			// Get date format
			$format_date = self::get_object_meta_value($field_object, 'format_date', get_option('date_format'));
			if(empty($format_date)) { $format_date = get_option('date_format'); }

			// Get time format
			$format_time = self::get_object_meta_value($field_object, 'format_time', get_option('time_format'));
			if(empty($format_time)) { $format_time = get_option('time_format'); }

			// Process by date format
			switch($format_date) {

				case 'd/m/Y' :

					// Convert / to - so that strtotime works with d/m/Y format
					$date = str_replace('/', '-', $date);
					break;

				case 'd.m.Y' :
				case 'j.n.Y' :

					// Convert . to - so that strtotime works with d.m.Y or j.n.Y format
					$date = str_replace('.', '-', $date);
					break;
			}

			// Translate date
			$date = self::field_date_translate($date);

			// Convert date to time
			$time = strtotime($date);

			// We'll use UTC so that wp_date doesn't offset the date
			$utc = new DateTimeZone('UTC');

			// Check WordPress version
			$wp_new = self::wp_version_at_least('5.3');

			// If conversion failed, return the source date
			if($time === false) { return $date_source; }

			if($format_output === false) {

				$input_type_datetime = self::get_object_meta_value($field_object, 'input_type_datetime', 'date');

				switch($input_type_datetime) {

					case 'date' :

						return $wp_new ? wp_date($format_date, $time, $utc) : gmdate($format_date, $time);

					case 'month' :

						return $wp_new ? wp_date('F Y', $time, $utc) : gmdate('F Y', $time);

					case 'time' :

						return $wp_new ? wp_date($format_time, $time, $utc) : gmdate($format_time, $time);

					case 'week' :

						return __('Week', 'ws-form') . ' ' . ($wp_new ? wp_date('W, Y', $time, $utc) : gmdate('W, Y', $time));

					default :

						return $wp_new ? wp_date($format_date . ' ' . $format_time, $time, $utc) : gmdate($format_date . ' ' . $format_time, $time);
				}

			} else {

				return $wp_new ? wp_date($format_output, strtotime($date), $utc) : gmdate($format_output, strtotime($date));
			}
		}

		// Throw error
		public static function throw_error($error) {
			
			throw new Exception($error);
		}

		// Get system report
		public static function get_system_report_html() {

			// Get system report
			$system_report = WS_Form_Config::get_system();

			// Build system report HTML
			$system_report_html = '<table class="wsf-table-system">';

			// Check WordPress version
			$wp_new = self::wp_version_at_least('5.3');

			foreach($system_report as $group_id => $group) {

				$system_report_html .= '<tbody>';

				$system_report_html .= '<tr><th colspan="2"><h2>' . htmlentities($group['label']) . '</h2></th></tr>';

				foreach($group['variables'] as $item_id => $item) {

					// Valid
					// 0 = Ignore, 1 = Yes, 2 = No
					$valid = isset($item['valid']) ? ($item['valid'] ? 1 : 2) : 0;

					$system_report_html .= '<tr';

					switch($valid) {

						case 1 : $system_report_html .= ' class="wsf-system-valid"'; break;
						case 2 : $system_report_html .= ' class="wsf-system-invalid"'; break;
					}

					// Label
					$system_report_html .= '><td><b>' . htmlentities($item['label']);
					if(isset($item['min'])) { $system_report_html .= ' (Min: ' . $item['min'] . ')'; }
					$system_report_html .= '</b></td>';

					// Value
					$system_report_html .= '<td>';

					$value = isset($item['value']) ? $item['value'] : '-';
					$type = isset($item['type']) ? $item['type'] : 'text';

					switch($type) {

						case 'plugins' :

							if(is_array($value)) {

								$plugin_array = array();

								foreach($value as $plugin_path) {

									$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path);
									$plugin_array[] = sprintf('<a href="%s" target="_blank">%s</a> (%s)', $plugin_data['PluginURI'], $plugin_data['Name'], $plugin_data['Version']);
								}

								$value = implode('<br />', $plugin_array);
							}
							break;

						case 'theme' :

							if(is_object($value)) {

								$value = sprintf('<a href="%s" target="_blank">%s</a> (%s)', $value->get('ThemeURI'), $value->get('Name'), $value->get('Version'));
							}
							break;

						case 'boolean' :

							$value = $value ? __('Yes', 'ws-form') : __('No', 'ws-form');
							break;

						case 'url' :

							$value = sprintf('<a href="%1$s" target="_blank">%1$s</a>', $value);
							break;

						case 'size' :

							$value = size_format($value);
							break;

						case 'edition' :

							switch($value) {

								case 'basic' : $value = 'Basic'; break;
								case 'pro' : $value = 'PRO'; break;
							}
							break;

						case 'date' :

							$value = ($value != '') ? ($wp_new ? wp_date(get_option('date_format'), $value) : gmdate(get_option('date_format'), $value)) : '-';
							break;
					}

					$system_report_html .= $value;

					// Suffix
					if(isset($item['suffix'])) { $system_report_html .= ' ' . $item['suffix']; }

					// Valid
					switch($valid) {

						case 1 : $system_report_html .= WS_Form_Config::get_icon_16_svg('check'); break;
						case 2 : $system_report_html .= WS_Form_Config::get_icon_16_svg('warning'); break;
					}

					$system_report_html .= '</td></tr>';
				}

				$system_report_html .= '</tbody>';
			}

			$system_report_html .= '</table>';

			return $system_report_html;
		}

		// Get a string formatted for SMTP email addresses
		public static function get_email_address($email, $name = '') {

			// Ensure email is valid
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) { return false; }

			// Check length
			if(strlen($name) > 255) { $name = substr($name, 0, 255); }

			// Determine if double quotes and escaping of the name is required
			if(preg_match('/[^\w\s!#$%&\'*+\/=?^_`{|}~.-]|^\s|\s$|\s{2,}/', $name)) {

				// Correct escaping of double quotes for name
//				$name = str_replace('"', '\"', $name);

				// There is a bug in wp_mail that does not handle escaped double quotes properly, therefore we'll remove double quotes other wise double quotes turn into a forward slash
				$name = str_replace('"', '', $name);

				// Enclose in double quotes
				$name = sprintf('"%s"', $name);
			}

			// Return full email address
			return ($name != '') ? sprintf('%s <%s>', $name, $email) : $email;
		}

		// Get encryption status HTML
		public static function get_encryption_status_html() {

			$ws_form_encryption = new WS_Form_Encryption();
			if($ws_form_encryption->key_found === false) {			

				/* translators: %s = wp-config.php */
				$encryption_status_html = '<p>' . sprintf(__('Copy and paste the following code into your %s file after the Authentication Unique Keys and Salts section.', 'ws-form'), '<strong>wp-config.php</strong>') . '</p>';

				$encryption_key = htmlentities($ws_form_encryption->create_random_key());
				$encryption_status_html .= "<textarea class=\"wsf-field wsf-encryption-snippet\" rows=\"6\" readonly>/** Encryption Key For WS Form */\ndefine('WS_FORM_ENCRYPTION_KEY', '$encryption_key');</textarea>";

				$encryption_status_html .= sprintf('<p><strong style="color: #bb0000;">%s:</strong> %s</p>', __('Caution'), __('Once installed, do not change this key. Changing your encryption key at a later date will make it impossible to decrypt existing submission data. THIS KEY IS ONLY GENERATED ONCE. PLEASE ENSURE YOU DO NOT LOSE THIS KEY AND KEEP IT IN A SECURE PLACE. ENCRYPTED DATA AND KEYS CANNOT BE RECOVERED IF THE KEY IS LOST.'));

			} else {

				/* translators: %s = wp-config.php */
				$encryption_status_html = '<p>' . sprintf(__('Key successfully found in %s', 'ws-form'), '<strong>wp-config.php</strong>') . '</p>';

				if($ws_form_encryption->can_encrypt) {

					$encryption_status_html .= '<p><strong>' . __('Encrypting submission meta data.', 'ws-form') . '</strong></p>';

				} else {

					$encryption_status_html .= '<p><strong>' . __('Submission meta data is not being encrypted.', 'ws-form') . '</strong></p>';
				}
			}

			return $encryption_status_html;
		}

		// Get preview URL
		public static function get_preview_url($form_id = 0, $template_id = false, $style_id = false, $styler = false, $conversational = false, $submit_hash = false, $skin_id = 'ws_form') {

			$url = get_site_url(null, '/');

			// Form ID
			$url = add_query_arg(sprintf('wsf_preview%s_form_id', ($conversational ? '_conversational' : '')), $form_id, $url);

			// Template ID
			if(!empty($template_id) && self::styler_enabled()) {

				$url = add_query_arg('wsf_preview_template_id', $template_id, $url);
			}

			// Style ID
			if(!empty($style_id) && self::styler_enabled()) {

				$url = add_query_arg('wsf_preview_style_id', $style_id, $url);
			} 

			if($styler && self::styler_enabled()) {

				$url = add_query_arg('wsf_preview_styler', 'true', $url);
			}

			// Skin ID
			if(!empty($skin_id) && !self::styler_enabled()) {

				$url = add_query_arg('wsf_skin_id', $skin_id, $url);
			}

			// Submit hash
			if($submit_hash !== false) {

				$url = add_query_arg('wsf_submit_hash', $submit_hash, $url);
			}

			// Random 
			$url = add_query_arg('wsf_rand', wp_generate_password(12, false, false), $url);

			return $url;
		}

		// Get obscure license key
		public static function get_license_key_obscured($license_key = false) {

			// License key
			if($license_key === false) {

				$license_key = self::option_get('license_key', '');
			}

			$license_key_length = strlen($license_key);
			$license_key_obscured = ($license_key_length > 6) ? (str_repeat('*', $license_key_length - 6) . substr($license_key, -6)) : '';

			return ($license_key != '') ? $license_key_obscured : __('Unlicensed', 'ws-form');
		}

		// Get currency
		public static function get_currency() {

			$return_array = array();

			$price_decimals = absint(self::option_get('price_decimals'));
			$currency_position = self::option_get('currency_position');

			// Get currency symbol
			$currencies = WS_Form_Config::get_currencies();
			$currency = self::option_get('currency', self::get_currency_default());
			$currency_found = isset($currencies[$currency]) && isset($currencies[$currency]['s']);
			$currency_symbol = $currency_found ? $currencies[$currency]['s'] : '$';

			$return_array['prefix'] = $currency_symbol;
			$return_array['suffix'] = '';

			switch($currency_position) {

				case 'right' :

					$return_array['prefix'] = '';
					$return_array['suffix'] = $currency_symbol;
					break;

				case 'left_space' :

					$return_array['prefix'] = $currency_symbol . ' ';
					break;

				case 'right_space' :

					$return_array['prefix'] = '';
					$return_array['suffix'] = ' ' . $currency_symbol;
					break;
			}

			// Price decimals
			$return_array['decimals'] = ($price_decimals < 0) ? 0 : $price_decimals;

			// Separators
			$return_array['decimal_separator'] = self::option_get('price_decimal_separator');
			$return_array['thousand_separator'] = self::option_get('price_thousand_separator');

			return $return_array;
		}

		public static function get_price($price_float, $currency = false, $currency_symbol_render = true) {

			if($currency === false) { $currency = self::get_currency(); }

			if(!is_numeric($price_float)) { $price_float = floatVal($price_float); }

			return ($currency_symbol_render ? $currency['prefix'] : '') . number_format($price_float, absint($currency['decimals']), $currency['decimal_separator'], $currency['thousand_separator']) . ($currency_symbol_render ? $currency['suffix'] : '');
		}

		public static function get_currency_default() {

			return apply_filters('wsf_currency_default', 'USD');
		}

		public static function get_admin_icon($color = '#a0a5aa', $base64 = true) {

			$svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><path fill="%s" d="M0 0v400h400V0H0zm336.6 118.9c6.7-.1 13.4 5.4 13.4 13.6-.1 7.4-5.9 13.4-13.4 13.4-8.1 0-13.6-6.7-13.5-13.7 0-7.3 6.1-13.5 13.5-13.3zm-124.4 6.5c-12 48.8-24 97.6-36.1 146.3 0 .2-.2.2-.2.4-.8.1-6 .2-10.4.2h-1.9c-2.1 0-3.7-.1-4.1-.1-.2-.2-.3-.4-.3-.6-.1-.2-.1-.5-.2-.7-1.5-6.6-2.9-13.2-4.4-19.8-2.8-12.2-5.5-24.4-8.2-36.6-2.3-10.2-4.5-20.4-6.8-30.6-.9-4.2-1.9-8.3-2.8-12.5-.6-3-1.1-6.1-1.7-9.1-1-5.5-2-11.2-3-16.7-.1-.4-.2-.8-.3-1.5-.2.6-.3.8-.4 1.1-1.5 9-3.4 17.9-5.2 26.8-.9 4.7-2.1 9.5-3.2 14.2-2.8 12.3-5.7 24.6-8.5 36.9-3.6 15.8-7.3 31.6-11 47.5-.1.5-.3 1-.4 1.5-.2.1-.5.2-.6.2H86.7c-.6-.3-.6-.8-.7-1.2-1.2-4.8-2.3-9.6-3.5-14.5-3.6-15.4-7.4-30.9-11-46.4-3.9-16.4-7.8-32.7-11.7-49.1-2.8-11.5-5.5-22.9-8.1-34.3-.2-.7-.5-1.4-.4-2.1 1.2-.2 11.9-.3 14-.1.1.3.2.7.4 1.1 2.4 10 4.7 19.9 7 29.9 2.5 10.7 5 21.4 7.5 32 1.8 7.8 3.7 15.6 5.5 23.3 1.7 7.4 3.2 14.8 4.7 22.2 1.4 6.8 2.8 13.7 4.3 20.5.1.5.2.9.4 1.3.1.2.3.2.6.3.2-.6.3-1.1.4-1.7 1.8-13.1 4.4-25.9 7.4-38.8 4.3-18.6 8.7-37.2 13.1-55.8l7.8-33.3c.1-.5.2-.9.4-1.4 1.7-.1 3.3 0 5-.1h5c1.7 0 3.3-.1 5 .1.2.6.3 1.2.5 1.7 3.8 16.1 7.6 32.2 11.4 48.2 3 12.7 6 25.3 8.9 38 1.3 5.5 2.3 11.2 3.4 16.8 1.3 6.9 2.5 13.8 3.7 20.6.3 1.7.6 3.2.9 4.9.1.3.2.6.7.6l.3-1.2c.8-5.1 1.8-10 2.9-15 3.9-18.1 8.2-36.1 12.4-54.3 3.6-15.2 7.1-30.4 10.7-45.6 1-4.5 2.1-9 3.2-13.5.1-.3.2-.7.4-1.1 1.2-.2 2.4-.1 3.5-.1h6.8c1.2 0 2.4-.2 3.6.2-.8.5-.8.7-.9 1zm86.3 124.5c-3.6 11.5-11.3 19.1-22.8 22.8-3.2 1-6.6 1.7-10 2-5.2.6-10.4.5-15.7-.1-7-.7-13.8-2.7-20.2-5.9-1.2-.6-2.4-1.2-3.5-2.1.6-1.6 5.4-9.7 6.2-10.7.3.2.6.2.9.5 1.7 1.1 3.5 1.8 5.4 2.5 3.2 1.1 6.3 2.1 9.7 2.8 5.1.9 10.1 1.3 15.3.8 12.9-1.4 19.2-10 21.3-18 1.5-5.6 1.6-11.3.2-16.9-.9-3.9-2.8-7.3-5.4-10.2-1.8-2-3.8-3.8-5.9-5.4-4.2-3.3-8.7-6.2-13.3-8.9-4.7-2.8-9.3-5.5-13.8-8.6-2.9-2.1-5.9-4.1-8.4-6.6-3.7-3.6-6.7-7.7-8.9-12.4-1.8-3.9-2.8-8.1-3.2-12.3-.2-2.5-.2-5.1-.1-7.7.6-8.8 4.2-16.2 10.6-22.3 5.9-5.5 12.8-8.9 20.6-10.4 5.3-1 10.7-1.2 16.1-.7 7.4.6 14.5 2.5 21.1 5.9 1.3.7 2.6 1.4 3.9 2.2.3.2.6.5.9.6-.5 1.3-3.8 7.1-5.7 10.1-.2.2-.3.4-.6.6l-1.2-.6c-5.4-3.1-11.1-5.3-17.2-6.2-5.6-.9-11.2-.9-16.7.5-2.8.7-5.4 1.7-7.8 3.3-5.9 3.9-9.3 9.3-10.2 16.2-.6 4.5-.2 8.9 1.2 13.3 1.1 3.5 3 6.5 5.5 9 2.6 2.6 5.5 4.8 8.5 7 3.3 2.3 6.8 4.4 10.3 6.5 5.8 3.4 11.5 7 16.8 11 2.3 1.7 4.6 3.6 6.6 5.6 5.9 5.9 9.5 13 10.6 21.2 1.4 7.2 1.1 14.5-1.1 21.6zm38 26.4c-7.5.1-13.5-6.2-13.5-13.5 0-6.7 5.4-13.5 13.5-13.4 7.4 0 13.4 5.9 13.4 13.4.1 8-6.5 13.6-13.4 13.5zm-.1-64.8c-7.9-.1-13.4-6.6-13.4-13.4 0-7.7 6.4-13.7 13.5-13.5 6.4-.2 13.4 5.1 13.5 13.5 0 7.4-6.1 13.5-13.6 13.4z"></path></svg>', $color);

			return $base64 ? 'data:image/svg+xml;base64,' . base64_encode($svg) : $svg;
		}

		// Load content via AJAX
		public static function ajax_load($url, $id = 'wsf-settings-content') {

			// Build action product IDs
			$action_license_item_id_array = array();

			foreach(get_declared_classes() as $class){

				if(strpos($class, 'WS_Form_Action_') === false) { continue; }
				if(!is_subclass_of($class, 'WS_Form_Action')) { continue; }

				$action = New $class();

				if(method_exists($action, 'get_license_item_id')) {

					$action_license_item_id_array[] = $action->get_license_item_id();
				}
			}

			$action_license_item_ids = implode(',', $action_license_item_id_array);

			$url_variables = array(

				'locale' 					=> rawurlencode(get_locale()),
				'locale_user' 				=> rawurlencode(get_user_locale()),
				'version'					=> rawurlencode(WS_FORM_VERSION),
				'action_license_item_ids'	=> rawurlencode($action_license_item_ids)
			);

			$url = self::mask_parse($url, $url_variables);

			echo sprintf(	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

				'<div id="wsf-settings-content"><script>(function($) {\'use strict\';$(\'#%1$s\').load(\'%2$s\', function(response, status, xhr) { if(status == \'error\') { $(\'#%1$s\').html(\'%3$s\'); }});})(jQuery);</script></div>',

				esc_html($id),
				esc_html($url),
				sprintf(

					'<a href="%s" target="_blank">%s</a>',

					esc_attr(self::get_plugin_website_url('', 'settings')),

					sprintf(

						/* translators: %s = WS Form */
						__('Click here to learn more about %s', 'ws-form'),

						esc_html(WS_FORM_NAME_GENERIC)
					)
				)
			);
		}

		// Get root post
		public static function get_post_root() {

			// Load post (This uses the post ID set before any of the page renders)
			$post = (isset($GLOBALS) && isset($GLOBALS['ws_form_post_root'])) ? $GLOBALS['ws_form_post_root'] : null;

			// Load post by query string (Used by actions when a form is submitted)
			if(is_null($post)) {

				$post_id = absint(self::get_query_var('wsf_post_id', 0));
				if($post_id == 0) { $post_id = absint(self::get_query_var('post_id', 0)); }
				$post = ($post_id > 0) ? get_post($post_id) : null;
				$GLOBALS['ws_form_post_root'] = $post;
			}

			return $post;
		}

		// Get user
		public static function get_user() {

			// Load user
			$user = (isset($GLOBALS) && isset($GLOBALS['ws_form_user'])) ? $GLOBALS['ws_form_user'] : false;

			// Load user by current_user
			if(
				($user === false) &&
				function_exists('wp_get_current_user')
			) {

				$user = wp_get_current_user();
			}

			return $user;
		}

		// Get editable user roles
		public function get_editable_roles() {

			global $wp_roles;

			$all_roles = $wp_roles->roles;
			$editable_roles = apply_filters('editable_roles', $all_roles);

			return $editable_roles;
		}

		// Is block editor on page?
		public static function is_block_editor() {

			// Check for query variable
			if(!empty(self::get_query_var('wsf_block_editor'))) { return true; }

			// Check by current screen
			if(!function_exists('get_current_screen')) { return false; }
			if(!is_object(get_current_screen())) { return false; }
			if(!method_exists(get_current_screen(), 'is_block_editor')) { return false; }

			return get_current_screen()->is_block_editor();
		}

		// Is this a REST request
		public static function is_rest_request() {

			return (defined('REST_REQUEST') && REST_REQUEST);
		}

		// Is this customize preview?
		public static function is_customize_preview() {

			return (self::get_query_var('customize_theme') != '');
		}

		// Is string true?
		public static function is_true($input) {

			// Check for boolean
			if(is_bool($input)) {

				return $input;
			}

			// Check for object
			if(is_object($input)) {

				$input = (array) $input;
			}

			// Check for array
			if(is_array($input)) {

				$input = isset($input[0]) ? $input[0] : '';
			}

			// Check for numeric
			if(is_numeric($input)) {

				$input = strval($input);
			}

			// Check for string
			if(!is_string($input)) {

				return false;
			}

			// True values
			$true_array = array('1', 'on', 'yes', 'true');

			// Trim and lowercase input
			$input = trim(strtolower($input));

			// Return if true
			return in_array($input, $true_array);
		}

		// do_shortcode
		public static function do_shortcode($input) {

			// Get shortcode regex
			$shortcode_regex = get_shortcode_regex();

			// If there are shortcodes in the input
			if(
				preg_match_all('/'. $shortcode_regex .'/s', $input, $matches) &&
				array_key_exists(2, $matches) &&
				!in_array(WS_FORM_SHORTCODE, $matches[2])
			) {

				// Run do_shortcode
				$input = do_shortcode($input);
			}

			return $input;
		}

		// Convert a CSV file to a meta value containing a new data grid
		public static function csv_file_to_data_grid_meta_value($file, $meta_key, $meta_value) {

			// Ensure meta value is an object
			$meta_value = json_decode(wp_json_encode($meta_value));

			// Read file data
			$file_name = $file['name'];
			$file_type = $file['type'];
			$file_tmp_name = $file['tmp_name'];
			$file_error = $file['error'];
			$file_size = $file['size'];
			$file_string = isset($file['string']) ? $file['string'] : file_get_contents($file_tmp_name);

			// Error
			if($file_error !== 0) { self::throw_error(__('File upload error', 'ws-form') . ': ' . $file_error); }

			// Check file extension
			$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			if($ext !== 'csv') { self::throw_error(__('Unsupported file extension', 'ws-form') . ': ' . $ext); }

			// Determine character encoding
			$first2 = substr($file_string, 0, 2);
			$first3 = substr($file_string, 0, 3);
			$first4 = substr($file_string, 0, 3);

			$char_encoding = false;
			if($first3 == WS_FORM_UTF8_BOM) $char_encoding =  'UTF-8';
			elseif($first4 == WS_FORM_UTF32_BIG_ENDIAN_BOM) $char_encoding =  'UTF-32BE';
			elseif($first4 == WS_FORM_UTF32_LITTLE_ENDIAN_BOM) $char_encoding =  'UTF-32LE';
			elseif($first2 == WS_FORM_UTF16_BIG_ENDIAN_BOM) $char_encoding =  'UTF-16BE';
			elseif($first2 == WS_FORM_UTF16_LITTLE_ENDIAN_BOM) $char_encoding =  'UTF-16LE';

			// Convert string
			if($char_encoding) {

				$file_string = mb_convert_encoding($file_string, 'UTF-8', $char_encoding);
			}

			// Load CSV data as file pointer
			$fp = fopen("php://temp", 'r+');
			fputs($fp, $file_string);
			rewind($fp);

			// Read header
			$columns = fgetcsv($fp);
			if($columns === false) { self::throw_error(__('Unable to read header row of file', 'ws-form')); }
			if(is_null($columns)) { self::throw_error(__('Unable to read header row of file', 'ws-form')); }
			if(count($columns) == 0) { self::throw_error(__('No columns to process', 'ws-form')); }

			// Set group_index to 0 (Select first tab)
			$meta_value->group_index = 0;

			// Build columns
			$columns_new = array();
			$column_key_id = -1;
			$column_key_wsf_id = -1;
			$column_key_wsf_default = -1;
			$column_key_wsf_required = -1;
			$column_key_wsf_disabled = -1;
			$column_key_wsf_hidden = -1;

			$column_index = 0;
			foreach($columns as $key => $column) {

				switch(strtolower($column)) {

					case 'wsf_id' :

						$column_key_wsf_id = $key;
						break;

					case 'wsf_default' :

						$column_key_wsf_default = $key;
						break;

					case 'wsf_required' :

						$column_key_wsf_required = $key;
						break;

					case 'wsf_disabled' :

						$column_key_wsf_disabled = $key;
						break;

					case 'wsf_hidden' :

						$column_key_wsf_hidden = $key;
						break;

					case 'id' :
						$column_key_id = $key;

					default:

						$columns_new[] = (object) array(

							'id' => $column_index,
							'label' => $column
						);
						$column_index++;
				}
			}
			$meta_value->columns = $columns_new;

			// Get default group configuration
			$meta_keys = WS_Form_Config::get_meta_keys(0, false);
			if(!isset($meta_keys[$meta_key])) { self::throw_error(__('Unknown meta key', 'ws-form') + ': ' + $meta_key); }
			if(!isset($meta_keys[$meta_key]['default'])) { self::throw_error(__('Default not found', 'ws-form') + ': ' + $meta_key); }
			if(!isset($meta_keys[$meta_key]['default']['groups'])) { self::throw_error(__('Groups not found', 'ws-form') + ': ' + $meta_key); }
			if(!isset($meta_keys[$meta_key]['default']['groups'][0])) { self::throw_error(__('Group[0] not found', 'ws-form') + ': ' + $meta_key); }

			$group = json_decode(wp_json_encode($meta_keys[$meta_key]['default']['groups'][0]));

			// Re-process array to match required format for data grid
			$id_array = [];
			$key = 0;
			while($row = fgetcsv($fp)) {

				if(($row === false) || is_null($row)) { continue; }

				// UTF-8 encode the row
				$row_id = -1;
				$column_index = 0;
				$data = [];
				$default = '';
				$required = '';
				$disabled = '';
				$hidden = '';

				foreach($row as $column_key => $field) {

					if(($row === false) || is_null($field)) { continue 2; }

					$field_lower = strtolower($field);

					switch($column_key) {

						case $column_key_wsf_id:

							$row_id = is_numeric($field_lower) ? absint($field_lower) : -1;
							if($row_id > 0) { $id = $row_id; }
							break;

						case $column_key_wsf_default:

							$default = ($field_lower != '') ? 'on' : '';
							break;

						case $column_key_wsf_required :

							$required = ($field_lower != '') ? 'on' : '';
							break;

						case $column_key_wsf_disabled :

							$disabled = ($field_lower != '') ? 'on' : '';
							break;

						case $column_key_wsf_hidden :

							$hidden = ($field_lower != '') ? 'on' : '';
							break;

						case $column_key_id :

							$row_id = is_numeric($field_lower) ? absint($field_lower) : -1;
							if($row_id > 0) { $id = $row_id; }

						default :

							$data[$column_index] = $field;
							$column_index++;
					}
				}

				// Check for duplicate row IDs (Attempt to fix import data errors)
				if($row_id > 0) {

					if(in_array($row_id, $id_array)) {

						$row_id = -1;
					}
				}

				// ID row not found
				if($row_id == -1) {

					$max_id = 0;
					foreach($id_array as $id) {

						if($id > $max_id) { $max_id = $id; }
					}
					$id = $max_id + 1;
				}

				// Build row
				$row_new = (object) array(

					'id'		=> $id,
					'data'		=> $data
				);

				// Add ID to ID array
				$id_array[] = $id;

				if($default == 'on') { $row_new->default = 'on'; }
				if($disabled == 'on') { $row_new->disabled = 'on'; }
				if($required == 'on') { $row_new->required = 'on'; }
				if($hidden == 'on') { $row_new->hidden = 'on'; }

				$array[$key] = $row_new;

				$key++;
			}

			// Build group label
			if(isset($file['group_label'])) {

				$group_label = $file['group_label'];

			} else {

				$group_label = strtolower($file_name);
				$group_label = str_replace('_', ' ', $group_label);
				$group_label = str_replace('-', ' ', $group_label);
				$group_label = str_replace('.csv', '', $group_label);
				$group_label = ucwords($group_label);
			}

			// Build group
			$group->label = $group_label;
			$group->page = 0;
			$group->rows = $array;

			// Add to meta value
			$meta_value->groups = array($group);
			$meta_value->group_index = 0;

			return $meta_value;
		}

		// Get key value array (Used by third party visual builders)
		public static function get_forms_array($placeholder = true) {

			// Build form list
			$ws_form_form = new WS_Form_Form();
			$forms = $ws_form_form->db_read_all('', "NOT (status = 'trash')", 'label ASC, id ASC', '', '', false, true);
			$form_array = $placeholder ? array('' => __('Select form...', 'ws-form')) : array();

			if($forms) {

				foreach($forms as $form) {

					/* translators: %s: Name of the form, %u: ID of the form */
					$form_array[$form['id']] = sprintf(__('%s (ID: %u)', 'ws-form'), esc_html($form['label']), $form['id']);
				}
			}

			return $form_array;
		}

		// Sanitize IP addresses
		public static function sanitize_ip_address($ip) {

			return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
		}

		// Sanitize CSS value
		public static function sanitize_css_value($css_value, $pattern_match = false) {

			if($css_value == '') { return ''; }

			// Strip tags
			$css_value = wp_strip_all_tags($css_value);

			// Strip invalid UTF-8
			$css_value = wp_check_invalid_utf8($css_value, true);

			// Remove semi-colons
			$css_value = str_replace(';', '', $css_value);

			// Close open brackets
			$css_value = self::close_open_brackets($css_value);

			// Trim any whitespace from the value
			$css_value = trim($css_value);

			return $css_value;
		}

		// Close open brackets
		public static function close_open_brackets($input) {

			if(empty($input)) { return $input; }

		    // Count the number of open and close parentheses
		    $open_count = substr_count($input, '(');
		    $close_count = substr_count($input, ')');

		    // Determine how many closing parentheses are needed
		    $missing_closing = $open_count - $close_count;

		    // If there are missing closing parentheses, append them
		    if ($missing_closing > 0) {
		        $input .= str_repeat(')', $missing_closing);
		    }

		    return $input;
		}

		// Escape CSS output
		public static function esc_css($css_value) {

			// Strip tags
			$css_value = wp_strip_all_tags($css_value);

			// Strip invalid UTF-8
			$css_value = wp_check_invalid_utf8($css_value, true);

			return $css_value;
		}

		// Echo a string escaped using esc_html without a language domain
		// This is used to echo and escape HTML that should not be translated
		public static function echo_esc_html($text) {

			echo esc_html($text);
		}

		// Echo a string escaped using esc_attr without a language domain
		// This is used to echo and escape attributes that should not be translated
		public static function echo_esc_attr($text) {

			echo esc_attr($text);
		}

		// Echo a string escaped using esc_url
		public static function echo_esc_url($url) {

			echo esc_url($url);
		}

		// Echo data using wp_json_encode
		public static function echo_wp_json_encode($data) {

			echo wp_json_encode($data);
		}

		// Echo content using wp_kses
		public static function echo_wp_kses($content, $allowed_html) {

			echo wp_kses($content, $allowed_html);
		}

		// Echo escaped CSS output
		public static function echo_esc_css($css_value) {

			if(empty($css_value)) { return ''; }

			echo self::esc_css($css_value);
		}

		// Echo escaped CSS output inline
		public static function echo_esc_css_inline($css_value) {

			if(empty($css_value)) { return ''; }

			echo '<style>' . self::esc_css($css_value) . '</style>';
		}

		// Echo urlencode
		public static function echo_urlencode($url) {

			echo urlencode($url);
		}

		// Echo admin icon
		public static function echo_get_admin_icon($color = '#a0a5aa', $base64 = true) {

			echo self::get_admin_icon($color, $base64);
		}

		// Echo logo
		public static function echo_logo() {

			echo WS_Form_Config::get_logo_svg();
		}

		// Check form ID
		public static function check_form_id($form_id) {

			if(
				(absint($form_id) === 0)
			) {
				self::throw_error(__('Invalid form ID', 'ws-form'));
			}

			return true;
		}

		// Check submit hash
		public static function check_submit_hash($hash) {

			if(
				($hash == '') ||
				!preg_match('/^[a-f0-9]{32}$/i', $hash)
			) {

				return false;
			}

			return true;
		}

		// Set cookie
		public static function cookie_set($cookie_name, $cookie_value = '', $cookie_expiry = true, $form_id = false, $cookie_prefix_bypass = false) {

			// Cookie name
			if($cookie_prefix_bypass === false) {

				$cookie_prefix = self::option_get('cookie_prefix', '');
				if($cookie_prefix == '') { return false; }

			} else {

				$cookie_prefix = '';
			}

			if($cookie_prefix != '') { $cookie_prefix = $cookie_prefix . '_'; }

			// Build cookie name
			$cookie_name = sprintf('%s%s%s', $cookie_prefix, (($form_id !== false) ? sprintf('%u_', $form_id) : ''), $cookie_name);

			// Cookie expires
			if($cookie_value != '') {

				$cookie_timeout = absint(self::option_get('cookie_timeout', 60 * 60 * 24 * 28));

			} else {

				$cookie_existing_value = self::cookie_get_raw($cookie_name);

				if($cookie_existing_value == '') { return false; }

				$cookie_timeout = (86400 * -1000);
			}

			if($cookie_expiry) {

				$cookie_timeout_date = new DateTime();
				$cookie_timeout_date->setTimestamp(time() + $cookie_timeout);
				$cookie_timeout_date->setTimezone(new DateTimeZone('GMT'));
				$cookie_expires = sprintf(' expires=%s;', $cookie_timeout_date->format('D, d M Y H:i:s e'));

			} else {

				$cookie_expires = '';
			}

			// Set cookie
			header(sprintf('Set-Cookie: %s=%s;%s path=/; SameSite=Strict; Secure', $cookie_name, $cookie_value, $cookie_expires));

			return true;
		}

		// Get cookie raw
		public static function cookie_get_raw($cookie_name, $default_value = '') {

			if(
				($cookie_name === '') ||
				!isset($_COOKIE) ||
				!isset($_COOKIE[$cookie_name])
			) {
				return $default_value;
			}

			return $_COOKIE[$cookie_name];
		}

		// Get object from POST $_FILE
		public static function get_object_from_post_file($object_type = false) {

			// Get files
			if(!isset($_FILES)) { self::throw_error(__('No files found', 'ws-form')); }
			if(!isset($_FILES['file'])) { self::throw_error(__('No files found', 'ws-form')); }

			// Run through files
			$file = $_FILES['file'];

			// Read file data
			$file_name = $file['name'];
			$file_type = $file['type'];
			$file_tmp_name = $file['tmp_name'];
			$file_error = $file['error'];
			$file_size = $file['size'];

			// Error checking
			if($file_error != 0) { self::throw_error(__('File upload error', 'ws-form') . ': ' . $file_error); }
			if($file_size == 0) { self::throw_error(__('File empty', 'ws-form')); }

			// Check file extension
			$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			if($ext !== 'json') { self::throw_error(sprintf(__('Unsupported file extension: %s', 'ws-form') , $ext)); }

			// Check file format
			if(!file_exists($file_tmp_name)) { self::throw_error(__('Unable to read uploaded file', 'ws-form')); }
			$json = file_get_contents($file_tmp_name);

			// Get form object from JSON
			$object = self::get_object_from_json($json, true, $object_type);

			return $object;
		}

		// Get object from JSON and check JSON is valid
		public static function get_object_from_json($json, $checksum_check = false, $object_type = false) {

			// Remove BOM if present
			if(substr($json, 0,3) == pack('CCC', 0xef, 0xbb, 0xbf)) {

				$json = substr($json, 3);
			}

			// Check form JSON format
			$object = json_decode($json);
			if(is_null($object)) { self::throw_error(sprintf(__('JSON corrupt (%s)', 'ws-form'), json_last_error_msg())); }
			if(!is_object($object)) { self::throw_error(__('JSON corrupt (Not object)', 'ws-form')); }

			// Checksum test
			if(
				$checksum_check &&
				!self::object_checksum_check($object)
			) {

				self::throw_error(__('JSON corrupt (Checksum error)', 'ws-form'));
			}

			// Check identifier
			if(
				!isset($object->identifier) ||
				($object->identifier !== WS_FORM_IDENTIFIER)
			) {

				self::throw_error(sprintf(

					/* translators: %s = WS Form */
					__('JSON corrupt (Not a %s JSON file)', 'ws-form'),

					WS_FORM_NAME_GENERIC
				));
			}

			// Check type
			if(
				($object_type !== false) &&
				!empty($object->meta->export_object) &&
				($object_type != $object->meta->export_object)
			) {
				self::throw_error(__('Import error (Invalid import type)', 'ws-form'));
			}

			// Check label
			if(!isset($object->label)) { self::throw_error(__('JSON corrupt (No label)', 'ws-form')); }

			// Check meta
			if(!isset($object->meta)) { self::throw_error(__('JSON corrupt (No meta data)', 'ws-form')); }

			return $object;
		}

		// Form object checksum check
		public static function object_checksum_check($object) {

			// Should checksum be checked?
			if(!apply_filters('wsf_form_checksum_check', true)) {

				return true;
			}

			// Check integrity of object
			if(
				!is_object($object) ||
				!property_exists($object, 'checksum')
			) {
				return false;
			}

			// Get checksum
			$checksum = $object->checksum;

			// Build checksum
			$object_checksum_check = clone $object;
			unset($object_checksum_check->checksum);
			$checksum_file = md5(wp_json_encode($object_checksum_check));

			// Release memory
			$object_checksum_check = null;

			// Check checksum
			return ($checksum == $checksum_file);
		}

		// Get meta value array
		public static function get_meta_value_array($field_id, $fields, $field_types) {

			$meta_value_array = array();

			// Get field
			$field = $fields[$field_id];

			// Get field type config
			$field_type = $field->type;
			if(!isset($field_types[$field_type])) { return array(); }
			$field_type_config = $field_types[$field_type];

			// If has data grid data source
			if(
				isset($field_type_config['datagrid_column_value']) &&
				isset($field_type_config['data_source']) &&
				($field_type_config['data_source']['type'] == 'data_grid')
			) {

				// Get data source
				$data_source_id = $field_type_config['data_source']['id'];
				$data_source = self::get_object_meta_value($field, $data_source_id, 0);

				// Get columns
				if(!isset($data_source->columns)) { return array(); }
				$columns = $data_source->columns;

				// Get data grid column ID
				$datagrid_column_value = $field_type_config['datagrid_column_value'];
				$datagrid_column_id = self::get_object_meta_value($field, $datagrid_column_value, 0);

				// Get data grid column index
				$data_grid_column_index = false;
				foreach($columns as $column_index => $column) {

					if($column->id == $datagrid_column_id) {

						$data_grid_column_index = $column_index;
						break;
					}
				}
				if($data_grid_column_index === false) { return array(); }

				// Get data
				if(
					isset($data_source->groups) &&
					isset($data_source->groups[0]) &&
					isset($data_source->groups[0]->rows)
				) {

					foreach($data_source->groups[0]->rows as $row) {

						if(
							isset($row->data) &&
							isset($row->data[$data_grid_column_index])
						) {

							$meta_value_array[] = $row->data[$data_grid_column_index];
						}
					}
				}
			}

			return $meta_value_array;
		}

		// Inject array at index
		public static function array_inject_element($array_old, $element, $index) {

			$array_new = array_slice($array_old, 0, $index, true);
			$array_new[] = $element;
			$array_new = array_merge($array_new, array_slice($array_old, $index, NULL, true));

			return $array_new;
		}

		// Add dot notation value
		public static function set_path_value(&$data, $path, $value, $dedupe = true) {

			// Pre-process path
			$path_processed = trim($path);
			$path_processed = str_replace('[', '.[', $path_processed);
			$path_processed = str_replace(']', '].', $path_processed);

			// Split path by dot
			$path_array = array_filter(explode('.', $path_processed));
			$path_array_index_max = (count($path_array) - 1);

			foreach($path_array as $path_array_index => $node) {

				// Remove square brackets (arrays)
				$node = str_replace('[', '', $node);
				$node = str_replace(']', '', $node);

				// If this node already has a value set on it, reset it
				if(
					isset($data[$node]) &&
					!is_array($data[$node]) &&
					($path_array_index < $path_array_index_max)
				) {
					throw new Exception(sprintf(__('Node %s not valid or duplicate node.', 'ws-form'), $path));
				}

				$data = &$data[$node];
			}

			// Create array if value already exists
			if(!is_null($data)) {

				// Turn it into an array to handle multiple values
				if(!is_array($data)) {

					$data = array($data);
				}

				// Add element
				$data[] = $value;

				// Make it unique
				if($dedupe) {

					$data = array_unique($data);
				}

			} else {

				$data = $value;
			}

			return $data;
		}

		// Get dot notation value
		public static function get_path_value($data, $path) {

			// Pre-process path
			$path_processed = trim($path);
			$path_processed = str_replace('[', '.[', $path_processed);
			$path_processed = str_replace(']', '].', $path_processed);

			// Split path by dot
			$path_array = array_filter(explode('.', $path_processed));

			foreach($path_array as $node) {

				// Remove square brackets (arrays)
				$node = str_replace('[', '', $node);
				$node = str_replace(']', '', $node);

				if(is_numeric($node)) {

					if(isset($data[$node])) {

						$data = $data[$node];

					} else {

						throw new Exception(sprintf(__('Node %s not found in response data.', 'ws-form'), $path));
					}

				} else {

					if(property_exists($data, $node)) {

						$data = $data->$node;

					} else {

						throw new Exception(sprintf(__('Node %s not found in response data.', 'ws-form'), $path));
					}
				}
			}

			return $data;
		}

		// Get uploads base directory and ensure paths are https (Known bug with wp_upload_dir)
		public static function get_upload_dir_base_url() {

			$upload_dir_base_url = wp_upload_dir()['baseurl'];
			if(is_ssl()) {

				$upload_dir_base_url = str_replace('http://', 'https://', $upload_dir_base_url);
			}

			return $upload_dir_base_url;
		}

		// Get attachment img (Used by email action to avoid any third party filters breaking the return of wp_get_attachment_image, e.g. Avada image lazy load)
		public static function get_attachment_img_html($attachment_id, $image_size = 'thumbnail') {

			// Get email logo attachment data
			$attachment_image_src = wp_get_attachment_image_src($attachment_id, $image_size);

			if($attachment_image_src !== false) {

				// Try to get alt text for attachment
				$email_logo_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

				// Return email logo
				return sprintf(

					'<img src="%s" width="%u" height="%u"%s />',
					esc_attr($attachment_image_src[0]),
					esc_attr($attachment_image_src[1]),
					esc_attr($attachment_image_src[2]),
					(!empty($email_logo_alt) ? sprintf(' alt="%s"', esc_attr($email_logo_alt)) : '')
				);

			} else {

				// Attachment not found
				return '';
			}
		}

		// Wordress version compare
		public static function wp_version_at_least($ver) {

			global $wp_version;

			return (self::version_compare($wp_version, $ver) >= 0);
		}

		// Version compare
		public static function version_compare($ver_a, $ver_b) {

			$ver_a_parts = explode('-', $ver_a);
			$ver_b_parts = explode('-', $ver_b);

			$semver_a = array_shift($ver_a_parts);
			$semver_b = array_shift($ver_b_parts);
			$comparison = version_compare($semver_a, $semver_b);

			if(0 !== $comparison) {
				return $comparison;
			}

			$prerelease_a = array_shift($ver_a_parts);
			$prerelease_b = array_shift($ver_b_parts);
			$comparison = strcmp(is_string($prerelease_a) ? $prerelease_a : '', is_string($prerelease_b) ? $prerelease_b : '');

			if(0 !== $comparison) {
				return $comparison;
			}

			$rev_a = absint(array_shift($ver_a_parts));
			$rev_b = absint(array_shift($ver_b_parts));

			$src_a = 'src' === array_shift($ver_a_parts);
			$src_b = 'src' === array_shift($ver_b_parts);

			if($src_a xor $src_b) {
				return 0;
			}

			// Compare revision numbers.
			return $rev_b - $rev_a;
		}

		// Translate date to English for JavaScript validation
		public static function field_date_translate($date_string) {

			// Check date_string is a string
			if(!is_string($date_string)) { return $date_string; }

			// Get translations for current locale
			$translations = self::field_date_translations();
			if($translations === false) { return $date_string; }

			// Convert date_string to lowercase
			$date_string = strtolower($date_string);

			// Convert HTML entities back to characters
			$date_string = html_entity_decode($date_string);

			// Get months in English
			$months_english = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

			// Translate - Months - Long
			if(isset($translations['m'])) {

				$date_string = self::field_date_translate_replace($date_string, $translations['m'], $months_english, 12);
			}

			// Translate - Months - Short
			if(isset($translations['n'])) {

				$date_string = self::field_date_translate_replace($date_string, $translations['n'], $months_english, 12);
			}

			// Get days in English
			$days_english = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

			// Translate - Days - Long
			if(isset($translations['b'])) {

				$date_string = self::field_date_translate_replace($date_string, $translations['b'], $days_english, 7);
			}

			// Translate - Days - Short
			if(isset($translations['a'])) {

				$date_string = self::field_date_translate_replace($date_string, $translations['a'], $days_english, 7);
			}

			return $date_string;
		}

		public static function field_date_translate_replace($date_string, $lookups, $replacements, $count) {

			if(
				!is_array($lookups) ||
				!is_array($replacements)
			) {
				return $date_string;
			}

			$date_string_original = $date_string;

			// Run through each lookup
			for($index = 0; $index < $count; $index++) {

				$lookup_value = $lookups[$index];

				if(is_array($lookup_value)) {

					// If multiple lookup values exist, process each
					foreach($lookup_value as $lookup_value_single) {

						$date_string = preg_replace('/\b' . strtolower($lookup_value_single) . '\b/u', $replacements[$index], $date_string);
					}

				} else {

					// Straight string swap
					$date_string = preg_replace('/\b' . strtolower($lookup_value) . '\b/u', $replacements[$index], $date_string);
				}

				// There should only be one replacements in a date string, so return if a change occurs
				if($date_string != $date_string_original) { return $date_string; }
			}

			return $date_string;
		}

		// Get translations
		public static function field_date_translations() {

			// Get locale
			$locale = get_user_locale();

			// Get language
			$language = substr($locale, 0, 2);

			// Check for English
			if($language == 'en') { return false; }

			// Translation lookups
			$translations = array(

				'ar' => array( // Arabic
					'm' => array('كانون الثاني','شباط','آذار','نيسان','مايو','حزيران','تموز','آب','أيلول','تشرين الأول','تشرين الثاني','كانون الأول'),
					'a' => array('ن','ث','ع','خ','ج','س','ح'),
					'b' => array('الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت','الأحد')
				),
				'ro' => array( // Romanian
					'm' => array('Ianuarie','Februarie','Martie','Aprilie','Mai','Iunie','Iulie','August','Septembrie','Octombrie','Noiembrie','Decembrie'),
					'a' => array('Du','Lu','Ma','Mi','Jo','Vi','Sâ'),
					'b' => array('Duminică','Luni','Marţi','Miercuri','Joi','Vineri','Sâmbătă')
				),
				'id' => array( // Indonesian
					'm' => array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'),
					'a' => array('Min','Sen','Sel','Rab','Kam','Jum','Sab'),
					'b' => array('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')
				),
				'is' => array( // Icelandic
					'm' => array('Janúar','Febrúar','Mars','Apríl','Maí','Júní','Júlí','Ágúst','September','Október','Nóvember','Desember'),
					'a' => array('Sun','Mán','Þrið','Mið','Fim','Fös','Lau'),
					'b' => array('Sunnudagur','Mánudagur','Þriðjudagur','Miðvikudagur','Fimmtudagur','Föstudagur','Laugardagur')
				),
				'bg' => array( // Bulgarian
					'm' => array('Януари','Февруари','Март','Април','Май','Юни','Юли','Август','Септември','Октомври','Ноември','Декември'),
					'a' => array('Нд','Пн','Вт','Ср','Чт','Пт','Сб'),
					'b' => array('Неделя','Понеделник','Вторник','Сряда','Четвъртък','Петък','Събота')
				),
				'fa' => array( // Persian/Farsi
					'm' => array('فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'),
					'a' => array('یکشنبه','دوشنبه','سه شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'),
					'b' => array('یک‌شنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنج‌شنبه','جمعه','شنبه','یک‌شنبه')
				),
				'ru' => array( // Russian
					'm' => array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'),
					'a' => array('Вс','Пн','Вт','Ср','Чт','Пт','Сб'),
					'b' => array('Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота')
				),
				'uk' => array( // Ukrainian
					'm' => array('Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'),
					'a' => array('Ндл','Пнд','Втр','Срд','Чтв','Птн','Сбт'),
					'b' => array('Неділя','Понеділок','Вівторок','Середа','Четвер','П\'ятниця','Субота')
				),
				'en' => array( // English
					'm' => array('January','February','March','April','May','June','July','August','September','October','November','December'),
					'a' => array('Sun','Mon','Tue','Wed','Thu','Fri','Sat'),
					'b' => array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')
				),
				'el' => array( // Ελληνικά
					'm' => array('Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλιος','Μάιος','Ιούνιος','Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος'),
					'a' => array('Κυρ','Δευ','Τρι','Τετ','Πεμ','Παρ','Σαβ'),
					'b' => array('Κυριακή','Δευτέρα','Τρίτη','Τετάρτη','Πέμπτη','Παρασκευή','Σάββατο')
				),
				'de' => array( // German
					'm' => array('Januar','Februar',array('März','Marz'),'April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'),
					'n' => array(array('Jan','Jän'),'Feb',array('März','Marz'),'Apr','Mai','Juni','Juli','Aug','Sept','Okt','Nov','Dez'),
					'a' => array('So','Mo','Di','Mi','Do','Fr','Sa'),
					'b' => array('Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag')
				),
				'nl' => array( // Dutch
					'm' => array('januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december'),
					'n' => array('jan','feb','maart','apr','mei','juni','juli','aug','sept',array('oct','okt'),'nov','dec'),
					'a' => array('zo','ma','di','wo','do','vr','za'),
					'b' => array('zondag','maandag','dinsdag','woensdag','donderdag','vrijdag','zaterdag')
				),
				'tr' => array( // Turkish
					'm' => array('Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'),
					'a' => array('Paz','Pts','Sal','Çar','Per','Cum','Cts'),
					'b' => array('Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi')
				),
				'fr' => array( //French
					'm' => array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet',array('Août','Aout'),'Septembre','Octobre','Novembre','Décembre'),
					'n' => array('janv','févr','mars','avril','mai','juin','juil',array('août','aout'),'sept','oct','nov',array('dec','déc')),
					'a' => array('Dim','Lun','Mar','Mer','Jeu','Ven','Sam'),
					'b' => array('dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi')
				),
				'es' => array( // Spanish
					'm' => array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'),
					'n' => array('enero','feb','marzo','abr','mayo','jun','jul','agosto',array('sept','set'),'oct','nov','dic'),
					'a' => array('Dom','Lun','Mar','Mié','Jue','Vie','Sáb'),
					'b' => array('Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado')
				),
				'th' => array( // Thai
					'm' => array('มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'),
					'a' => array('อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'),
					'b' => array('อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัส','ศุกร์','เสาร์','อาทิตย์')
				),
				'pl' => array( // Polish
					'm' => array('styczeń','luty','marzec','kwiecień','maj','czerwiec','lipiec','sierpień','wrzesień','październik','listopad','grudzień'),
					'n' => array('stycz','luty','mar','kwiec','maj','czerw','lip','sierp','wrzes','pazdz','listop','grudz'),
					'a' => array('nd','pn','wt','śr','cz','pt','sb'),
					'b' => array('niedziela','poniedziałek','wtorek','środa','czwartek','piątek','sobota')
				),
				'pt' => array( // Portuguese
					'm' => array('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'),
					'n' => array('jan','fev','março','abril','maio','junho','julho','agosto','set','out','nov','dez'),
					'a' => array('Dom','Seg','Ter','Qua','Qui','Sex','Sab'),
					'b' => array('Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado')
				),
				'ch' => array( // Simplified Chinese
					'm' => array('一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'),
					'a' => array('日','一','二','三','四','五','六')
				),
				'se' => array( // Swedish
					'm' => array('Januari','Februari','Mars','April','Maj','Juni','Juli','Augusti','September',  'Oktober','November','December'),
					'n' => array('jan','febr','mars','april','maj','juni','juli','aug','sept','okt','nov','dec'),
					'a' => array('Sön','Mån','Tis','Ons','Tor','Fre','Lör')
				),
				'km' => array( // Khmer (ភាសាខ្មែរ)
					'm' => array('មករា​','កុម្ភៈ','មិនា​','មេសា​','ឧសភា​','មិថុនា​','កក្កដា​','សីហា​','កញ្ញា​','តុលា​','វិច្ឆិកា','ធ្នូ​'),
					'a' => array('អាទិ​','ច័ន្ទ​','អង្គារ​','ពុធ​','ព្រហ​​','សុក្រ​','សៅរ៍'),
					'b' => array('អាទិត្យ​','ច័ន្ទ​','អង្គារ​','ពុធ​','ព្រហស្បតិ៍​','សុក្រ​','សៅរ៍')
				),
				'kr' => array( // Korean
					'm' => array('1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'),
					'a' => array('일','월','화','수','목','금','토'),
					'b' => array('일요일','월요일','화요일','수요일','목요일','금요일','토요일')
				),
				'it' => array( // Italian
					'm' => array('Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'),
					'n' => array('genn','febbr','mar','apr','magg','giugno','luglio','ag','sett','ott','nov','dic'),
					'a' => array('Dom','Lun','Mar','Mer','Gio','Ven','Sab'),
					'b' => array('Domenica','Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato')
				),
				'da' => array( // Dansk
					'm' => array('Januar','Februar','Marts','April','Maj','Juni','Juli','August','September','Oktober','November','December'),
					'n' => array('jan','febr','marts','april','maj','juni','juli','aug','sept','okt','nov','dec'),
					'a' => array('Søn','Man','Tir','Ons','Tor','Fre','Lør'),
					'b' => array('søndag','mandag','tirsdag','onsdag','torsdag','fredag','lørdag')
				),
				'no' => array( // Norwegian
					'm' => array('Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember'),
					'n' => array('jan','febr','mars','april','mai','juni','juli','aug','sept','okt','nov','des'),
					'a' => array('Søn','Man','Tir','Ons','Tor','Fre','Lør'),
					'b' => array('Søndag','Mandag','Tirsdag','Onsdag','Torsdag','Fredag','Lørdag')
				),
				'ja' => array( // Japanese
					'm' => array('1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'),
					'a' => array('日','月','火','水','木','金','土'),
					'b' => array('日曜','月曜','火曜','水曜','木曜','金曜','土曜')
				),
				'vi' => array( // Vietnamese
					'm' => array('Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'),
					'a' => array('CN','T2','T3','T4','T5','T6','T7'),
					'b' => array('Chủ nhật','Thứ hai','Thứ ba','Thứ tư','Thứ năm','Thứ sáu','Thứ bảy')
				),
				'sl' => array( // Slovenščina
					'm' => array('Januar','Februar','Marec','April','Maj','Junij','Julij','Avgust','September','Oktober','November','December'),
					'a' => array('Ned','Pon','Tor','Sre','Čet','Pet','Sob'),
					'b' => array('Nedelja','Ponedeljek','Torek','Sreda','Četrtek','Petek','Sobota')
				),
				'cs' => array( // Čeština
					'm' => array('Leden','Únor','Březen','Duben','Květen','Červen','Červenec','Srpen','Září','Říjen','Listopad','Prosinec'),
					'a' => array('Ne','Po','Út','St','Čt','Pá','So')
				),
				'hu' => array( // Hungarian
					'm' => array('Január','Február','Március','Április','Május','Június','Július','Augusztus','Szeptember','Október','November','December'),
					'a' => array('Va','Hé','Ke','Sze','Cs','Pé','Szo'),
					'b' => array('vasárnap','hétfő','kedd','szerda','csütörtök','péntek','szombat')
				),
				'az' => array( //Azerbaijanian (Azeri)
					'm' => array('Yanvar','Fevral','Mart','Aprel','May','Iyun','Iyul','Avqust','Sentyabr','Oktyabr','Noyabr','Dekabr'),
					'a' => array('B','Be','Ça','Ç','Ca','C','Ş'),
					'b' => array('Bazar','Bazar ertəsi','Çərşənbə axşamı','Çərşənbə','Cümə axşamı','Cümə','Şənbə')
				),
				'bs' => array( //Bosanski
					'm' => array('Januar','Februar','Mart','April','Maj','Jun','Jul','Avgust','Septembar','Oktobar','Novembar','Decembar'),
					'a' => array('Ned','Pon','Uto','Sri','Čet','Pet','Sub'),
					'b' => array('Nedjelja','Ponedjeljak','Utorak','Srijeda','Četvrtak','Petak','Subota')
				),
				'ca' => array( //Català
					'm' => array('Gener','Febrer','Març','Abril','Maig','Juny','Juliol','Agost','Setembre','Octubre','Novembre','Desembre'),
					'a' => array('Dg','Dl','Dt','Dc','Dj','Dv','Ds'),
					'b' => array('Diumenge','Dilluns','Dimarts','Dimecres','Dijous','Divendres','Dissabte')
				),
				'et' => array( //'Eesti'
					'm' => array('Jaanuar','Veebruar','Märts','Aprill','Mai','Juuni','Juuli','August','September','Oktoober','November','Detsember'),
					'a' => array('P','E','T','K','N','R','L'),
					'b' => array('Pühapäev','Esmaspäev','Teisipäev','Kolmapäev','Neljapäev','Reede','Laupäev')
				),
				'eu' => array( //Euskara
					'm' => array('Urtarrila','Otsaila','Martxoa','Apirila','Maiatza','Ekaina','Uztaila','Abuztua','Iraila','Urria','Azaroa','Abendua'),
					'a' => array('Ig.','Al.','Ar.','Az.','Og.','Or.','La.'),
					'b' => array('Igandea','Astelehena','Asteartea','Asteazkena','Osteguna','Ostirala','Larunbata')
				),
				'fi' => array( //Finnish (Suomi)
					'm' => array('Tammikuu','Helmikuu','Maaliskuu','Huhtikuu','Toukokuu','Kesäkuu','Heinäkuu','Elokuu','Syyskuu','Lokakuu','Marraskuu','Joulukuu'),
					'a' => array('Su','Ma','Ti','Ke','To','Pe','La'),
					'b' => array('sunnuntai','maanantai','tiistai','keskiviikko','torstai','perjantai','lauantai')
				),
				'gl' => array( //Galego
					'm' => array('Xan','Feb','Maz','Abr','Mai','Xun','Xul','Ago','Set','Out','Nov','Dec'),
					'a' => array('Dom','Lun','Mar','Mer','Xov','Ven','Sab'),
					'b' => array('Domingo','Luns','Martes','Mércores','Xoves','Venres','Sábado')
				),
				'hr' => array( //Hrvatski
					'm' => array('Siječanj','Veljača','Ožujak','Travanj','Svibanj','Lipanj','Srpanj','Kolovoz','Rujan','Listopad','Studeni','Prosinac'),
					'a' => array('Ned','Pon','Uto','Sri','Čet','Pet','Sub'),
					'b' => array('Nedjelja','Ponedjeljak','Utorak','Srijeda','Četvrtak','Petak','Subota')
				),
				'ko' => array( //Korean (한국어)
					'm' => array('1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'),
					'a' => array('일','월','화','수','목','금','토'),
					'b' => array('일요일','월요일','화요일','수요일','목요일','금요일','토요일')
				),
				'lt' => array( //Lithuanian (lietuvių)
					'm' => array('Sausio','Vasario','Kovo','Balandžio','Gegužės','Birželio','Liepos','Rugpjūčio','Rugsėjo','Spalio','Lapkričio','Gruodžio'),
					'a' => array('Sek','Pir','Ant','Tre','Ket','Pen','Šeš'),
					'b' => array('Sekmadienis','Pirmadienis','Antradienis','Trečiadienis','Ketvirtadienis','Penktadienis','Šeštadienis')
				),
				'lv' => array( //Latvian (Latviešu)
					'm' => array('Janvāris','Februāris','Marts','Aprīlis ','Maijs','Jūnijs','Jūlijs','Augusts','Septembris','Oktobris','Novembris','Decembris'),
					'a' => array('Sv','Pr','Ot','Tr','Ct','Pk','St'),
					'b' => array('Svētdiena','Pirmdiena','Otrdiena','Trešdiena','Ceturtdiena','Piektdiena','Sestdiena')
				),
				'mk' => array( //Macedonian (Македонски)
					'm' => array('јануари','февруари','март','април','мај','јуни','јули','август','септември','октомври','ноември','декември'),
					'a' => array('нед','пон','вто','сре','чет','пет','саб'),
					'b' => array('Недела','Понеделник','Вторник','Среда','Четврток','Петок','Сабота')
				),
				'mn' => array( //Mongolian (Монгол)
					'm' => array('1-р сар','2-р сар','3-р сар','4-р сар','5-р сар','6-р сар','7-р сар','8-р сар','9-р сар','10-р сар','11-р сар','12-р сар'),
					'a' => array('Дав','Мяг','Лха','Пүр','Бсн','Бям','Ням'),
					'b' => array('Даваа','Мягмар','Лхагва','Пүрэв','Баасан','Бямба','Ням')
				),
				'pt_BR' => array( //Português(Brasil)
					'm' => array('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'),
					'a' => array('Dom','Seg','Ter','Qua','Qui','Sex','Sáb'),
					'b' => array('Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado')
				),
				'sk' => array( //Slovenčina
					'm' => array('Január','Február','Marec','Apríl','Máj','Jún','Júl','August','September','Október','November','December'),
					'a' => array('Ne','Po','Ut','St','Št','Pi','So'),
					'b' => array('Nedeľa','Pondelok','Utorok','Streda','Štvrtok','Piatok','Sobota')
				),
				'sq' => array( //Albanian (Shqip)
					'm' => array('Janar','Shkurt','Mars','Prill','Maj','Qershor','Korrik','Gusht','Shtator','Tetor','Nëntor','Dhjetor'),
					'a' => array('Die','Hën','Mar','Mër','Enj','Pre','Shtu'),
					'b' => array('E Diel','E Hënë','E Martē','E Mërkurë','E Enjte','E Premte','E Shtunë')
				),
				'sr_YU' => array( //Serbian (Srpski)
					'm' => array('Januar','Februar','Mart','April','Maj','Jun','Jul','Avgust','Septembar','Oktobar','Novembar','Decembar'),
					'a' => array('Ned','Pon','Uto','Sre','čet','Pet','Sub'),
					'b' => array('Nedelja','Ponedeljak','Utorak','Sreda','Četvrtak','Petak','Subota')
				),
				'sr' => array( //Serbian Cyrillic (Српски)
					'm' => array('јануар','фебруар','март','април','мај','јун','јул','август','септембар','октобар','новембар','децембар'),
					'a' => array('нед','пон','уто','сре','чет','пет','суб'),
					'b' => array('Недеља','Понедељак','Уторак','Среда','Четвртак','Петак','Субота')
				),
				'sv' => array( //Svenska
					'm' => array('Januari','Februari','Mars','April','Maj','Juni','Juli','Augusti','September','Oktober','November','December'),
					'a' => array('Sön','Mån','Tis','Ons','Tor','Fre','Lör'),
					'b' => array('Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag')
				),
				'zh_TW' => array( //Traditional Chinese (繁體中文)
					'm' => array('一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'),
					'a' => array('日','一','二','三','四','五','六'),
					'b' => array('星期日','星期一','星期二','星期三','星期四','星期五','星期六')
				),
				'zh' => array( //Simplified Chinese (简体中文)
					'm' => array('一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'),
					'a' => array('日','一','二','三','四','五','六'),
					'b' => array('星期日','星期一','星期二','星期三','星期四','星期五','星期六')
				),
				'ug' => array( // Uyghur(ئۇيغۇرچە)
					'm' => array('1-ئاي','2-ئاي','3-ئاي','4-ئاي','5-ئاي','6-ئاي','7-ئاي','8-ئاي','9-ئاي','10-ئاي','11-ئاي','12-ئاي'),
					'b' => array('يەكشەنبە','دۈشەنبە','سەيشەنبە','چارشەنبە','پەيشەنبە','جۈمە','شەنبە')
				),
				'he' => array( //Hebrew (עברית)
					'm' => array('ינואר','פברואר','מרץ','אפריל','מאי','יוני','יולי','אוגוסט','ספטמבר','אוקטובר','נובמבר','דצמבר'),
					'a' => array('א\'','ב\'','ג\'','ד\'','ה\'','ו\'','שבת'),
					'b' => array('ראשון','שני','שלישי','רביעי','חמישי','שישי','שבת','ראשון')
				),
				'hy' => array( // Armenian
					'm' => array('Հունվար','Փետրվար','Մարտ','Ապրիլ','Մայիս','Հունիս','Հուլիս','Օգոստոս','Սեպտեմբեր','Հոկտեմբեր','Նոյեմբեր','Դեկտեմբեր'),
					'a' => array('Կի','Երկ','Երք','Չոր','Հնգ','Ուրբ','Շբթ'),
					'b' => array('Կիրակի','Երկուշաբթի','Երեքշաբթի','Չորեքշաբթի','Հինգշաբթի','Ուրբաթ','Շաբաթ')
				),
				'kg' => array( // Kyrgyz
					'm' => array('Үчтүн айы','Бирдин айы','Жалган Куран','Чын Куран','Бугу','Кулжа','Теке','Баш Оона','Аяк Оона','Тогуздун айы','Жетинин айы','Бештин айы'),
					'a' => array('Жек','Дүй','Шей','Шар','Бей','Жум','Ише'),
					'b' => array('Жекшемб','Дүйшөмб','Шейшемб','Шаршемб','Бейшемби','Жума','Ишенб')
				),
				'rm' => array( // Romansh
					'm' => array('Schaner','Favrer','Mars','Avrigl','Matg','Zercladur','Fanadur','Avust','Settember','October','November','December'),
					'a' => array('Du','Gli','Ma','Me','Gie','Ve','So'),
					'b' => array('Dumengia','Glindesdi','Mardi','Mesemna','Gievgia','Venderdi','Sonda')
				),
				'ka' => array( // Georgian
					'm' => array('იანვარი','თებერვალი','მარტი','აპრილი','მაისი','ივნისი','ივლისი','აგვისტო','სექტემბერი','ოქტომბერი','ნოემბერი','დეკემბერი'),
					'a' => array('კვ','ორშ','სამშ','ოთხ','ხუთ','პარ','შაბ'),
					'b' => array('კვირა','ორშაბათი','სამშაბათი','ოთხშაბათი','ხუთშაბათი','პარასკევი','შაბათი')
				)
			);

			// Attempt to find exact locale
			if(isset($translations[$locale])) {

				return $translations[$locale];
			}

			// Attempt to find by language
			if(isset($translations[$language])) {

				return $translations[$language];
			}

			return false;
		}

		// Toolbar enabled
		public static function toolbar_enabled() {

			return (

				!self::option_get('disable_toolbar_menu') &&
				self::can_user('edit_form') &&
				self::can_user('read_submission')
			);
		}

		// Toolbar CSS
		public static function toolbar_css() {

			if(!self::toolbar_enabled()) { return; }

			if(!is_admin_bar_showing()) { return; }
?>
<style>

	#wp-admin-bar-ws-form-node .ab-icon {
		
		width: 20px !important;
		height: 30px !important;
	}

	#wp-admin-bar-ws-form-node .ab-icon svg {

		margin-top: 2px;
		width: 20px;
	}

	#wp-admin-bar-ws-form-node:hover .ab-icon svg path,
	#wp-admin-bar-ws-form-node.hover .ab-icon svg path {

		fill: #00b9eb;
	}

	@media screen and ( max-width: 782px ) {

		#wpadminbar #wp-admin-bar-ws-form-node .ab-icon svg {

			margin: 9px 12px 0 12px;
			height: 28px !important;
			width: 28px !important;
			display: block;
		}

		#wpadminbar li#wp-admin-bar-ws-form-node {

			width: 52px;
			display: block;
		}
	}

</style>
<?php
		}

		// Toolbar JS
		public static function toolbar_js() {

			if(!self::toolbar_enabled()) { return; }

			if(!is_admin_bar_showing()) { return; }

			// Get enqueue args
			$enqueue_args = self::get_enqueue_args();

			// Path to toolbar JS
			$toolbar_js = sprintf('%sshared/js/ws-form-toolbar%s.js', WS_FORM_PLUGIN_DIR_URL, (SCRIPT_DEBUG ? '' : '.min'));

			// Register toolbar JS
			wp_register_script(WS_FORM_NAME . '-toolbar', $toolbar_js, array('jquery'), WS_FORM_VERSION, $enqueue_args);

			// Localize toolbar JS
			wp_localize_script(WS_FORM_NAME . '-toolbar', 'ws_form_toolbar', array(

				'api_url_debug' => self::get_api_path('helper/debug/'),
				'api_url_styler' => self::get_api_path('helper/styler/'),
				'x_wp_nonce' => wp_create_nonce('wp_rest')
			));

			// Enqueue toolbar JS
			wp_enqueue_script(WS_FORM_NAME . '-toolbar');
		}

		// Get enqueue args
		public static function get_enqueue_args() {

			$enqueue_args = array(

				'in_footer' => apply_filters('wsf_enqueue_script_in_footer', (self::option_get('jquery_footer', '') == 'on'))
			);

			if(apply_filters('wsf_enqueue_script_strategy_defer', (self::option_get('js_defer', '') == 'on'))) {

				$enqueue_args['strategy'] = 'defer';
			}

			return $enqueue_args;
		}

		// Escape string to prevent CSV injection hack
		public static function esc_csv($value) {

			// Check input is a string
			if(!is_string($value) && !is_numeric($value)) { return ''; }

			// If character is safe, return the string from this point
			if(in_array(mb_substr($value, 0, 1), array('=', '-', '+', '@', ';', "\t", "\r"), true)) {

				return sprintf('\'%s', $value);

			} else {

				return $value;
			}
		}

		// Escaped version of fputcsv
		public static function esc_fputcsv($stream, $fields) {

			// Check fields is an array
			if(!is_array($fields)) { return false; }

			// Process 
			$fields = array_map(function($value) {

				return self::esc_csv($value);

			}, $fields);

			// Run fputcsv
			return fputcsv($stream, $fields);
		}

		public static function get_admin_submit_filters() {

			// Build filters
			$filters = array();

			// Get status
			$status = self::get_query_var('ws-form-status');
			if(!empty($status)) {

				$filters[] = array(

					'field' => 'status',
					'operator' => '==',
					'value' => $status
				);
			}

			// Get date from
			$date_from = self::get_query_var('date_from');
			if(!empty($date_from)) {

				$filters[] = array(

					'field' => 'date_from',
					'operator' => '==',
					'value' => $date_from
				);
			}

			// Get date to
			$date_to = self::get_query_var('date_to');
			if(!empty($date_to)) {

				$filters[] = array(

					'field' => 'date_to',
					'operator' => '==',
					'value' => $date_to
				);
			}

			// Get ids
			$ids = self::get_query_var('submit_ids');
			if(!empty($ids)) {

				$filters[] = array(

					'field' => 'id',
					'operator' => 'in',
					'value' => $ids
				);
			}

			return $filters;
		}

		// Label to autocomplete
		public static function label_to_autocomplete($label) {

			// Check by field type
			switch(strtolower($label)) {

				case 'first name' :
				case 'given name' :

					return 'given-name';

				case 'family name' :
				case 'last name' :
				case 'surname' :

					return 'family-name';

				case 'email' :
				case 'email address' :

					return 'email';

				case 'cell' :
				case 'phone' :
				case 'tel' :

					return 'tel';

				case 'url' :
				case 'web site' :
				case 'website' :

					return 'url';

				case 'company' :
				case 'company name' :
				case 'organization' :
				case 'organization name' :

					return 'organization';

				case 'title' :
				case 'company title' :
				case 'job title' :
				case 'role' :

					return 'organization-title';

				case 'address' :
				case 'address 1' :
				case 'address line 1' :

					return 'address-line1';

				case 'address 2' :
				case 'address line 2' :

					return 'address-line2';

				case 'city' :
				case 'town' :

					return 'address-level2';

				case 'county' :
				case 'state' :
				case 'state / county / province' :

					return 'address-level1';

				case 'post code' :
				case 'postal code' :
				case 'zip' :
				case 'zip code' :
				case 'zipcode' :

					return 'postal-code';

				case 'country' :

					return 'country-name';

				case 'date of birth' :

					return 'bday';

				default :

					return false;
			}
		}
	}
