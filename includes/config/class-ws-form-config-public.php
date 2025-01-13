<?php

	class WS_Form_Config_Public extends WS_Form_Config {

		// Configuration - Settings - Public
		public static function get_settings_form_public() {

			// Additional language strings for the public
			$settings_form_public = array(

				'language' => array(

					'error_min_length'						=>	__('Minimum character count: %s', 'ws-form'),
					'error_max_length'						=>	__('Maximum character count: %s', 'ws-form'),
					'error_min_length_words'				=>	__('Minimum word count: %s', 'ws-form'),
					'error_max_length_words'				=>	__('Maximum word count: %s', 'ws-form'),
					'error_data_grid_source_type'			=>	__('Data grid source type not specified', 'ws-form'),
					'error_data_grid_source_id'				=>	__('Data grid source ID not specified', 'ws-form'),
					'error_data_source_data'				=>	__('Data source data not found', 'ws-form'),
					'error_data_source_columns'				=>	__('Data source columns not found', 'ws-form'),
					'error_data_source_groups'				=>	__('Data source groups not found', 'ws-form'),
					'error_data_source_group_label'			=>	__('Data source group label not found', 'ws-form'),
					'error_data_group_rows'					=>	__('Data source group rows not found', 'ws-form'),
					'error_data_group_label'				=>	__('Data source group label not found', 'ws-form'),
					'error_mask_help'						=>	__('No help mask defined: %s', 'ws-form'),
					'error_mask_invalid_feedback'			=>	__('No invalid feedback mask defined', 'ws-form'),
					'error_api_call_hash'					=>	__('Hash not returned in API call', 'ws-form'),
					'error_api_call_hash_invalid'			=>	__('Invalid hash returned in API call', 'ws-form'),
					'error_api_call_framework_invalid'		=>	__('Framework config not found', 'ws-form'),
					'error_recaptcha_v2_invisible'			=>	__('reCAPTCHA V2 invisible error', 'ws-form'),
					'error_hcaptcha_invisible'				=>	__('hCaptcha invisible error', 'ws-form'),
					'error_timeout_recaptcha'				=>	__('Timeout waiting for reCAPTCHA to load', 'ws-form'),
					'error_timeout_hcaptcha'				=>	__('Timeout waiting for hCaptcha to load', 'ws-form'),
					'error_timeout_turnstile'				=>	__('Timeout waiting for Turnstile to load', 'ws-form'),
					'error_timeout_analytics_google'		=>	__('Timeout waiting for Google Analytics to load', 'ws-form'),
					'error_timeout_analytics_data_layer'	=>	__('Timeout waiting for Data Layer to load', 'ws-form'),
					'error_timeout_google_maps'				=>	__('Timeout waiting for Google Maps to load', 'ws-form'),
					'error_geocoder_google_address_no_results'	=>	__('No results found for Google Geocoder', 'ws-form'),
					'error_geocoder_google_address_error'	=>	__('Google Geocoder error: %s', 'ws-form'),
					'error_google_key_missing'				=>	__('Google API key has not been entered in global settings', 'ws-form'),
					'error_google_key_invalid'				=>	__('Google API key is incorrect or misconfigured', 'ws-form'),
					'error_datetime_default_value'			=>	__('Default date/time value invalid (%s)', 'ws-form'),
					'error_framework_tabs_activate_js'		=>	__('Framework tab activate JS not defined', 'ws-form'),
					'error_form_draft'						=>	__('Form is in draft', 'ws-form'),
					'error_form_future'						=>	__('Form is scheduled', 'ws-form'),
					'error_form_trash'						=>	__('Form is trashed', 'ws-form'),
					'error_calc'							=>	__('Calculation error: %s'),
					'error_framework_plugin'				=>	__('Framework plugin error: %s', 'ws-form'),
					'error_tracking_geo_location'			=>	__('Tracking - Geo location error: %s', 'ws-form'),
					'error_geo'								=>	__('Geo - IP lookup failed: %s', 'ws-form'),
					'error_action'							=>	__('Actions - %s', 'ws-form'),
					'error_action_no_message'				=>	__('Actions - Error', 'ws-form'),
					'error_payment'							=>	__('Payments - %s', 'ws-form'),
					'error_termageddon'						=>	__('Error retrieving Termageddon content', 'ws-form'),
					'error_termageddon_404'					=>	__('Invalid Termageddon key', 'ws-form'),
					'error_js'								=>	__('Syntax error in JavaScript: %s', 'ws-form'),
					'error_section_button_no_section'		=>	__('No section assigned to this button', 'ws-form'),
					'error_section_icon_no_section'			=>	__('No section assigned to these icons', 'ws-form'),
					'error_section_icon_not_in_own_section'	=>	__('Icon %s must be in its own assigned section', 'ws-form'),
					'error_not_supported_video'				=>	__("Sorry, your browser doesn't support embedded videos.", 'ws-form'),
					'error_not_supported_audio'				=>	__("Sorry, your browser doesn't support embedded audio.", 'ws-form'),
					'error_google_map_style_js'				=>	__('Invalid Google Map embedded JSON style declaration', 'ws-form'),
					'error_file_upload'						=>	__('Error uploading file', 'ws-form'),
					'error_submit_hash'						=>	__('Invalid submission hash', 'ws-form'),
					'error_invalid_feedback'				=>	__('Invalid feedback set on field ID: %s', 'ws-form'),
					'error_google_route'					=>	__('Invalid Google Distance field configuration', 'ws-form'),
					'error_google_route_message'			=>	__('Google Directions API error: %s', 'ws-form'),
					'error_condition_action'				=>	__('Invalid condition action: %s', 'ws-form'),

					// Analytics
					'analytics_category'				=> __('Form - %s', 'ws-form'),

					// International telephone input errors
					'iti_number'						=> __('Invalid number', 'ws-form'),
					'iti_country_code'					=> __('Invalid country code', 'ws-form'),
					'iti_short'							=> __('Too short', 'ws-form'),
					'iti_long'							=> __('Too long', 'ws-form'),
					// Signature
					'clear'								=> __('Clear', 'ws-form'),

					// Google address
					'google_address_place_id_invalid_message' => __('Please choose a valid address.', 'ws-form'),

					// Google map
					'reset'								=> __('Reset', 'ws-form'),

					// Email allow deny message
					'email_allow_deny_message'			=> __('The email address entered is not allowed', 'ws-form'),

					// Password strength
					'password_strength_short'			=> __('Very weak', 'ws-form'),
					'password_strength_bad'				=> __('Weak', 'ws-form'),
					'password_strength_good'			=> __('Medium', 'ws-form'),
					'password_strength_strong'			=> __('Strong', 'ws-form'),
					'password_visibility_toggle_off'	=> __('Show password', 'ws-form'),
					'password_visibility_toggle_on'		=> __('Hide password', 'ws-form'),
					'password_generate'					=> __('Suggest password', 'ws-form'),
					'password_strength_invalid'			=> __('Please choose a stronger password.', 'ws-form'),

					// Duration
					'hour'			=>	__('hour', 'ws-form'),
					'hours'			=>	__('hours', 'ws-form'),
					'minute'		=>	__('minute', 'ws-form'),
					'minutes'		=>	__('minutes', 'ws-form'),
					'second'		=>	__('second', 'ws-form'),
					'seconds'		=>	__('seconds', 'ws-form'),

					// Distance
					'feet'			=>	__('feet', 'ws-form'),
					'feets'			=>	__('feet', 'ws-form'),
					'mile'			=>	__('mile', 'ws-form'),
					'miles'			=>	__('miles', 'ws-form'),
					'meter'			=>	__('meter', 'ws-form'),
					'meters'		=>	__('meters', 'ws-form'),
					'kilometer'		=>	__('km', 'ws-form'),
					'kilometers'	=>	__('km', 'ws-form'),
				)
			);

			// Styler
			if(WS_Form_Common::styler_visible_public()) {

				// Additional language strings for the public styler feature
				$language_extra = array(

					'styler_logo'						=>	WS_FORM_NAME_PRESENTABLE,
					'styler_search_placeholder'			=>	__('Setting search...', 'ws-form'),
					'styler_undo'						=>	__('Undo', 'ws-form'),
					'styler_undo_confirm'				=>	__('Are you sure you want to undo the changes made to this style?', 'ws-form'),
					'styler_pick_color'					=>	__('Pick color', 'ws-form'),
					'styler_save'						=>	__('Save', 'ws-form'),
					'styler_import'						=>	__('Import', 'ws-form'),
					'styler_export'						=>	__('Export', 'ws-form'),
					'styler_loading'					=>	__('Loading...', 'ws-form'),
					'styler_id'							=>	__('ID', 'ws-form'),
					'styler_scheme'						=>	__('Scheme', 'ws-form'),
					'styler_scheme_base'				=>	__('Base', 'ws-form'),
					'styler_scheme_alt'					=>	__('Alt', 'ws-form'),
					'styler_scheme_both'				=>	__('Both', 'ws-form'),
					'styler_settings'					=>	__('Settings', 'ws-form'),
					'styler_support'					=>	__('Support', 'ws-form'),
					'styler_label'						=>	__('Name', 'ws-form'),
					'styler_label_placeholder'			=>	__('Style name', 'ws-form'),
					'styler_close'						=>	__('Close', 'ws-form'),
					'styler_alt'						=>	__('Alt', 'ws-form'),
					'styler_alt_auto'					=>	__('Auto', 'ws-form'),
					'styler_alt_title'					=>	__('Create alternative color', 'ws-form'),
					'styler_copy'						=>	__('Copy', 'ws-form'),
				);

				// Add to language array
				foreach($language_extra as $key => $value) {

					$settings_form_public['language'][$key] = $value;
				}
			}
			// Check if debug is enabled
			$debug = WS_Form_Common::debug_enabled();

			// Debug
			if($debug) {

				// Additional language strings for the public debug feature
				$language_extra = array(

					'debug_logo'						=>	WS_FORM_NAME_PRESENTABLE,

					'debug_form'						=>	__('Form', 'ws-form'),
					'debug_form_rendered'				=>	__('Form rendered', 'ws-form'),

					'debug_minimize'					=>	__('Minimize', 'ws-form'),
					'debug_restore'						=>	__('Restore', 'ws-form'),
					'debug_close'						=>	__('Close', 'ws-form'),
					'debug_close_confirm'				=>	__("Are you sure you want to close the debug console?\n\nYou can re-enable it from the global settings Basic tab later.", 'ws-form'),

					'debug_tools'						=>	__('Tools', 'ws-form'),
					'debug_css'							=>	__('Design', 'ws-form'),
					'debug_log'							=>	__('Log', 'ws-form'),
					'debug_error'						=>	__('Errors', 'ws-form'),

					'debug_tools_populate'				=>	__('Populate', 'ws-form'),
					'debug_tools_identify'				=>	__('Identify', 'ws-form'),
					'debug_tools_edit'					=>	__('Edit', 'ws-form'),
					'debug_tools_submissions'			=>	__('Submissions', 'ws-form'),
					'debug_tools_submit'				=>	__('Submit', 'ws-form'),
					'debug_tools_save'					=>	__('Save', 'ws-form'),
					'debug_tools_populate_submit'		=>	__('Populate & Submit', 'ws-form'),
					'debug_tools_reload'				=>	__('Reload', 'ws-form'),
					'debug_tools_form_clear'			=>	__('Clear', 'ws-form'),
					'debug_tools_form_reset'			=>	__('Reset', 'ws-form'),
					'debug_tools_clear_hash'			=>	__('Clear Session ID', 'ws-form'),
					'debug_tools_clear_log'				=>	__('Clear Log', 'ws-form'),
					'debug_tools_clear_error'			=>	__('Clear Errors', 'ws-form'),

					'debug_info_label'					=>	__('Form Name', 'ws-form'),
					'debug_info_id'						=>	__('Form ID', 'ws-form'),
					'debug_info_style_id'				=>	__('Style ID', 'ws-form'),
					'debug_info_instance'				=>	__('Instance', 'ws-form'),
					'debug_info_hash'					=>	__('Session ID', 'ws-form'),
					'debug_info_checksum'				=>	__('Checksum', 'ws-form'),
					'debug_info_duration'				=>	__('Rendering Time', 'ws-form'),
					'debug_info_framework'				=>	__('Framework', 'ws-form'),
					'debug_info_submit_count'			=>	__('Submit Count', 'ws-form'),
					'debug_info_submit_duration_user'	=>	__('Submit Duration (User)', 'ws-form'),
					'debug_info_submit_duration_client'	=>	__('Submit Duration (Client)', 'ws-form'),
					'debug_info_submit_duration_server'	=>	__('Submit Duration (Server)', 'ws-form'),
					'debug_info_anti_spam_score'		=>	__('Anti-Spam Score', 'ws-form'),

					'debug_hash_empty'					=>	__('New Form', 'ws-form'),
					'debug_tools_publish_pending'		=>	__('Publish Pending', 'ws-form'),

					'debug_action_type'					=>	__('Type', 'ws-form'),
					'debug_action_row'					=>	__('Row', 'ws-form'),
					'debug_action_form'					=>	__('Form', 'ws-form'),
					'debug_action_group'				=>	__('Tab', 'ws-form'),
					'debug_action_section'				=>	__('Section', 'ws-form'),
					'debug_action_action'				=>	__('Action', 'ws-form'),
					'debug_action_reset'				=>	__('Reset', 'ws-form'),
					'debug_action_focussed'				=>	__('Focussed', 'ws-form'),
					'debug_action_clicked'				=>	__('Clicked', 'ws-form'),
					'debug_action_added'				=>	__('Added', 'ws-form'),
					'debug_action_removed'				=>	__('Removed', 'ws-form'),
					'debug_action_selected'				=>	__('Selected', 'ws-form'),
					'debug_action_deselected'			=>	__('Deselected', 'ws-form'),
					'debug_action_selected_value'		=>	__('Selected row by value', 'ws-form'),
					'debug_action_deselected_value'		=>	__('Deselected row by value', 'ws-form'),
					'debug_action_checked'				=>	__('Checked', 'ws-form'),
					'debug_action_unchecked'			=>	__('Unchecked', 'ws-form'),
					'debug_action_checked_value'		=>	__('Checked by value', 'ws-form'),
					'debug_action_unchecked_value'		=>	__('Unchecked by value', 'ws-form'),
					'debug_action_required'				=>	__('Required', 'ws-form'),
					'debug_action_not_required'			=>	__('Not required', 'ws-form'),
					'debug_action_disabled'				=>	__('Disabled', 'ws-form'),
					'debug_action_enabled'				=>	__('Not Disabled', 'ws-form'),
					'debug_action_hide'					=>	__('Hide', 'ws-form'),
					'debug_action_show'					=>	__('Show', 'ws-form'),
					'debug_action_read_only'			=>	__('Read only', 'ws-form'),
					'debug_action_not_read_only'		=>	__('Not read only', 'ws-form'),

					'debug_submit_loaded'				=>	__('Retrieved submit data', 'ws-form'),

					'debug_action_get'					=>	__('Retrieved %s action data', 'ws-form'),

					'debug_tracking_geo_location_permission_denied'		=>	__('User denied the request for geo location.', 'ws-form'),
					'debug_tracking_geo_location_position_unavailable'	=>	__('Geo location information was unavailable.', 'ws-form'),
					'debug_tracking_geo_location_timeout'				=>	__('The request to get user geo location timed out.', 'ws-form'),
					'debug_tracking_geo_location_default'				=>	__('An unknown error occurred whilst retrieving geo location.', 'ws-form'),

					// Log
					'log_hash_set'								=>	__('Session ID received: %s', 'ws-form'),
					'log_hash_not_found'						=>	__('Session ID not found', 'ws-form'),
					'log_hash_clear'							=>	__('Session ID cleared', 'ws-form'),
					'log_token_set'								=>	__('Token received: %s', 'ws-form'),
					'log_conditional_fired_then'				=>	__('IF returned TRUE: %s', 'ws-form'),
					'log_conditional_fired_else'				=>	__('IF returned FALSE: %s', 'ws-form'),
					'log_conditional_action_then'				=>	__('THEN: %s', 'ws-form'),
					'log_conditional_action_else'				=>	__('ELSE: %s', 'ws-form'),
					'log_conditional_event'						=>	__("Added event handler for %s", 'ws-form'),
					'log_analytics_google_loaded_analytics_js'	=>	__('Google Analytics successfully loaded (analytics.js)', 'ws-form'),
					'log_analytics_google_loaded_gtag_js'		=>	__('Google Analytics successfully loaded (gtag.js)', 'ws-form'),
					'log_analytics_google_loaded_ga_js'			=>	__('Google Analytics successfully loaded (ga.js)', 'ws-form'),
					'log_analytics_data_layer_loaded_js'		=>	__('Data layer successfully loaded', 'ws-form'),
					'log_analytics_fathom_loaded_js'			=>	__('Fathom Analytics successfully loaded', 'ws-form'),
					'log_analytics_bento_loaded_js'				=>	__('Bento successfully loaded', 'ws-form'),
					'log_analytics_facebook_loaded_fbevents_js'	=>	__('Facebook Analytics successfully loaded (fbevents.js)', 'ws-form'),
					'log_analytics_linkedin_loaded_insight_js'	=>	__('LinkedIn Insight successfully loaded (insight.js)', 'ws-form'),
					'log_analytics_event_form'					=>	__('Analytics form events initialized: %s', 'ws-form'),
					'log_analytics_event_tab'					=>	__('Analytics tab events initialized: %s', 'ws-form'),
					'log_analytics_event_field'					=>	__('Analytics field events initialized: %s', 'ws-form'),
					'log_analytics_event_fired'					=>	__('Analytics event ran: %s', 'ws-form'),
					'log_analytics_event_failed'				=>	__('Analytics event failed: %s (Function does not exist)', 'ws-form'),
					'log_recaptcha_v3_action_fired'				=>	__('reCAPTCHA V3 action ran: %s', 'ws-form'),
					'log_google_geocode_success'				=>	__('Google Geocoder processed successfully. Place ID: %s', 'ws-form'),
					'log_javascript'							=>	__('JavaScript ran', 'ws-form'),
					'log_honeypot'								=>	__('Spam protection - Honeypot initialized', 'ws-form'),
					'log_tracking_geo_location'					=>	__('Tracking - Geo location: %s', 'ws-form'),
					'log_submit_lock'							=>	__('Duplication protection - Button(s) locked', 'ws-form'),
					'log_submit_unlock'							=>	__('Duplication protection - Button(s) unlocked', 'ws-form'),
					'log_form_submit'							=>	__('Form submitted', 'ws-form'),
					'log_form_save'								=>	__('Form saved', 'ws-form'),
					'log_group_index'							=>	__('Set tab index to: %s', 'ws-form'),
					'log_action'								=>	__('Actions - %s', 'ws-form'),
					'log_trigger'								=>	"jQuery: $(document).trigger('%s', form_object, form_id, instance_id, form_el, form_canvas_el, tab_index)",
					'log_ecommerce_status'						=>	__('Ecommerce - Status set to: %s', 'ws-form'),
					'log_ecommerce_transaction_id'				=>	__('Ecommerce - Transaction ID set to: %s', 'ws-form'),
					'log_ecommerce_payment_method'				=>	__('Ecommerce - Payment method set to: %s', 'ws-form'),
					'log_ecommerce_payment_amount'				=>	__('Ecommerce - Payment amount set to: %s', 'ws-form'),
					'log_payment'								=>	__('Payments - %s', 'ws-form'),
					'log_anti_spam_score'						=>	__('Anti spam score: %s%', 'ws-form'),

					// Calc
					'log_calc_registered'						=>	__('Calculation registered: %s', 'ws-form'),
					'log_calc_registered_triggered'				=>	__('Triggered by: %s', 'ws-form'),
					'log_calc_fired'							=>	__('Calculation fired: %s', 'ws-form'),
					'log_calc_fired_triggered'					=>	__('Triggered by: %s', 'ws-form'),
					'log_calc_init'								=>	__('Initial calculation', 'ws-form'),

					// Text
					'log_text_registered'						=>	__('Text registered: %s', 'ws-form'),
					'log_text_registered_triggered'				=>	__('Triggered by: %s', 'ws-form'),
					'log_text_fired'							=>	__('Text fired: %s', 'ws-form'),
					'log_text_fired_triggered'					=>	__('Triggered by: %s', 'ws-form'),
					'log_text_init'								=>	__('Initial text', 'ws-form'),

					// Action JS
					'log_field_value'							=>	__('Value set on field ID: %s', 'ws-form'),
					'log_field_dropzonejs_file_objects'			=>	__('File field loaded with file objects: %s', 'ws-form'),

					// Geo
					'log_geo_endpoint_fallback'					=>	__('Using ipapi.co geolocation IP lookup service because chosen service does not support HTTPS', 'ws-form'),
					'log_geo_success'							=>	__('Geo lookup successfully completed using %s endpoint', 'ws-form'),

					// Loader
					'log_loader_show'							=>	__('Loader shown', 'ws-form'),
					'log_loader_hide'							=>	__('Loader hidden', 'ws-form'),

					// Debug
					'debug'										=>	__('Debug: %s', 'ws-form')
				);

				// Add to language array
				foreach($language_extra as $key => $value) {

					$settings_form_public['language'][$key] = $value;
				}
			}

			// Conditional
			require_once 'class-ws-form-config-conditional.php';
			$ws_form_config_conditional = new WS_Form_Config_Conditional();
			$settings_form_public['conditional'] = $ws_form_config_conditional->get_settings_conditional(true, $debug);

			// Apply filter
			$settings_form_public = apply_filters('wsf_config_settings_form_public', $settings_form_public);

			return $settings_form_public;
		}

		// Configuration - Get field types public
		public static function get_field_types_public($field_types_filter) {

			$field_types = self::get_field_types_flat(true);

			// Filter by fields found in forms
			if(count($field_types_filter) > 0) {

				$field_types_old = $field_types;
				$field_types = array();

				foreach($field_types_filter as $field_type) {

					if(isset($field_types_old[$field_type])) { $field_types[$field_type] = $field_types_old[$field_type]; }
				}
			}

			// Strip attributes
			$public_attributes_strip = array('label' => false, 'label_default' => false, 'submit_edit' => false, 'conditional' => array('logics_enabled', 'actions_enabled'), 'compatibility_id' => false, 'kb_url' => false, 'fieldsets' => false, 'pro_required' => false);

			foreach($field_types as $key => $field) {

				foreach($public_attributes_strip as $attribute_strip => $attributes_strip_sub) {

					if(isset($field_types[$key][$attribute_strip])) {

						if(is_array($attributes_strip_sub)) {

							foreach($attributes_strip_sub as $attribute_strip_sub) {

								if(isset($field_types[$key][$attribute_strip][$attribute_strip_sub])) {

									unset($field_types[$key][$attribute_strip][$attribute_strip_sub]);
								}
							}

						} else {

							unset($field_types[$key][$attribute_strip]);
						}
					}
				}
			}

			return $field_types;
		}
	}