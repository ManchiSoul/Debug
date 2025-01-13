<?php

	class WS_Form_API_Style extends WS_Form_API {

		public function __construct() {

			// Call parent on WS_Form_API
			parent::__construct();
		}

		// API - GET - CSS
		public function api_get_css($parameters) {

			// Get style ID
			$style_id = absint(WS_Form_Common::get_query_var_nonce('style_id', '', $parameters));

			// Force alt styles
			$alt_force = absint(WS_Form_Common::get_query_var_nonce('wsf_alt', '', $parameters));

			// Output HTTP header
			parent::api_css_header();

			// Output CSS
			try {

				$ws_form_style = new WS_Form_Style();
				$ws_form_style->id = $style_id;
				WS_Form_Common::echo_esc_css($ws_form_style->get_css_vars_markup(true, true, false, true, $alt_force, true));

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			exit;
		}

		// API - GET
		public function api_get($parameters) {

			// Get style ID
			$style_id = absint(WS_Form_Common::get_query_var_nonce('style_id', '', $parameters));

			try {

				$ws_form_style = new WS_Form_Style();
				$ws_form_style->id = $style_id;
				return $ws_form_style->db_read(true, false);

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}
		}

		// API - GET - CSS variables - CSV (No alternative CSS variables)
		public function api_get_css_variables_csv($parameters, $alt = false) {

			$ws_form_style = new WS_Form_Style();

			// Get header
			$header = $ws_form_style->styler_css_variables_get_header();

			// Alt?
			$alt = !empty(WS_Form_Common::get_query_var('alt'));

			// Calc?
			$calc = !empty(WS_Form_Common::get_query_var('calc'));

			// Shade?
			$shade = !empty(WS_Form_Common::get_query_var('shade'));

			// Get rows
			$rows = array_merge(array($header), $ws_form_style->styler_css_variables_get_rows($alt, $calc, $shade));

			// Output headers
			WS_Form_Common::file_download_headers('wsf-css-variables.csv', 'text/csv');

			// Use the output stream for fputcsv
			$output = fopen('php://output', 'w');

			// Output rows
			foreach($rows as $row) {

				fputcsv($output, $row);
			}

			// Close the output stream
			fclose($output);

			exit;
		}

		// API - GET - CSS variables - JSON
		public function api_get_css_variables_json($parameters, $alt = false) {

			$ws_form_style = new WS_Form_Style();

			// Get header
			$header = $ws_form_style->styler_css_variables_get_header($alt);

			// Alt?
			$alt = !empty(WS_Form_Common::get_query_var('alt'));

			// Calc?
			$calc = !empty(WS_Form_Common::get_query_var('calc'));

			// Shade?
			$shade = !empty(WS_Form_Common::get_query_var('shade'));

			// Get rows
			return array_merge($header, $ws_form_style->styler_css_variables_get_rows($alt, $calc, $shade));
		}

		// API - PUT
		public function api_put($parameters) {

			// Get style ID
			$style_id = self::api_get_id($parameters);

			// Get style object
			$style_object = WS_Form_Common::get_query_var_nonce('style', false, $parameters);

			// Check style object
			if(
				!$style_object ||
				!property_exists($style_object, 'id') ||
				!property_exists($style_object, 'label') ||
				!property_exists($style_object, 'meta') ||
				($style_id != $style_object->id)
			) {
				return false;
			}

			// Sanitize CSS values
			foreach((array) $style_object->meta as $meta_key => $meta_value) {

				$style_object->meta->$meta_key = WS_Form_Common::sanitize_css_value($meta_value);
			}

			// Get meta data
			$ws_form_style = new WS_Form_Style();
			$ws_form_style->id = $style_id;

			try {

				// Put style as object
				// 3rd 'true' attribute ensures existing meta data is replaced
				$ws_form_style->db_update_from_object($style_object, false, true);

				// Auto publish
				if($ws_form_style->publish_auto) {

					$ws_form_style->db_publish();

				} else {

					$ws_form_style->db_checksum();
				}

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			return true;
		}

		// API - PUT - Label
		public function api_put_label($parameters) {

			$ws_form_style = new WS_Form_Style();
			$ws_form_style->id = self::api_get_id($parameters);

			// Get new label
			$label = WS_Form_Common::get_query_var_nonce('label', false, $parameters);

			try {

				$ws_form_style->db_label($label);

				// Update checksum
				$ws_form_style->db_checksum();

				// Publish
				if($ws_form_style->publish_auto) {

					$ws_form_style->db_publish();
				}

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			return true;
		}

		// API - POST - Upload - JSON
		public function api_post_upload_json($parameters) {

			$style_id = absint(self::api_get_id($parameters));

			$ws_form_style = new WS_Form_Style();

			if(empty($style_id)) {

				try {

					$ws_form_style->db_create();				

				} catch (Exception $e) {

					parent::api_throw_error($e->getMessage());
				}

			} else {

				$ws_form_style->id = $style_id;
			}

			try {

				// Get style object from file
				$style_object = WS_Form_Common::get_object_from_post_file('style');

				// Reset style
				$ws_form_style->db_import_reset();

				// Build style
				$ws_form_style->db_update_from_object($style_object, true, true);

				// Update checksum
				$ws_form_style->db_checksum();

				// Publish
				if($ws_form_style->publish_auto) {

					$ws_form_style->db_publish();
				}

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			return true;
		}

		// Get style ID
		public function api_get_id($parameters) {

			return absint(WS_Form_Common::get_query_var_nonce('style_id', 0, $parameters));
		}
	}