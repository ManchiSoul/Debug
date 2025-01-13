(function($) {

	'use strict';

	// Adds international telephone input elements
	$.WS_Form.prototype.form_tel = function() {

		var ws_this = this;

		// Get tel objects
		var tel_objects = $('[data-intl-tel-input]:not([data-init-intl-tel-input])', this.form_canvas_obj);
		if(!tel_objects.length) { return false;}

		// Process each tel object
		tel_objects.each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-intl-tel-input', '');

			// Stylesheet
			if(!$('#wsf-intl-tel-input').length) {

				var image_path = ((ws_form_settings.url_cdn == 'cdn') ? 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.19/build/img/' : (ws_form_settings.url_plugin + 'public/images/external/'));
				if(ws_form_settings.styler_enabled) {

					$('body').append("<style id=\"wsf-intl-tel-input\">\n	.iti__flag { background-image: url(\"" + image_path + "flags.png\");}\n	@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {\n		.iti__flag { background-image: url(\"" + image_path + "flags@2x.png\"); }\n	}\n\n");

				} else {

					$('body').append("<style id=\"wsf-intl-tel-input\">\n	.iti { width: 100%; }\n	.iti__flag { background-image: url(\"" + image_path + "flags.png\");}\n	.iti--allow-dropdown input, .iti--allow-dropdown input[type=tel], .iti--allow-dropdown input[type=text], .iti--separate-dial-code input, .iti--separate-dial-code input[type=tel], .iti--separate-dial-code input[type=text] {\n		padding-right: 6px;\n		padding-left: 52px;\n		margin-left: 0;\n	}\n	@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {\n		.iti__flag { background-image: url(\"" + image_path + "flags@2x.png\"); }\n	}\n\n");
				}
			}

			// Build config
			var config = {

				utilsScript: ((ws_form_settings.url_cdn == 'cdn') ? 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.19/build/js/utils.js' : (ws_form_settings.url_plugin + 'public/js/external/utils.js?ver=19.2.19'))
			}

			// Get field ID
			var field_id = ws_this.get_field_id($(this));

			// Get field
			var field = ws_this.field_data_cache[field_id];

			// Config - Allow dropdown
			config.allowDropdown = (ws_this.get_object_meta_value(field, 'intl_tel_input_allow_dropdown', 'on') == 'on');

			// Config - Auto placeholder
			config.autoPlaceholder = (ws_this.get_object_meta_value(field, 'intl_tel_input_auto_placeholder', 'on') == 'on') ? 'polite' : 'off';

			// Config - National mode
			config.nationalMode = (ws_this.get_object_meta_value(field, 'intl_tel_input_national_mode', 'on') == 'on');

			// Config - Separate dial code
			config.separateDialCode = (ws_this.get_object_meta_value(field, 'intl_tel_input_separate_dial_code', '') == 'on');

			// Config - Initial country
			config.initialCountry = ws_this.get_object_meta_value(field, 'intl_tel_input_initial_country', '');

			// ITI requires 2 character country code to be lowercase
			if(typeof(config.initialCountry) == 'string') { config.initialCountry = config.initialCountry.toLowerCase(); }

			// Config - Geolookup
			if(
				(config.initialCountry == 'auto') &&
				(typeof(ws_this.form_geo) === 'function')
			) {

				config.geoIpLookup = function(callback) {

					// Get geo data
					var geo = ws_this.form_geo_get_element('country_short', 'us', callback);
				};
			}

			// Config - Only countries
			var only_countries = ws_this.get_object_meta_value(field, 'intl_tel_input_only_countries', []);

			if(
				(typeof(only_countries) === 'object') &&
				(only_countries.length > 0)
			) {

				config.onlyCountries = only_countries.map(function(row) { return row.country_alpha_2; });
			}

			// Config - Preferred countries
			var preferred_countries = ws_this.get_object_meta_value(field, 'intl_tel_input_preferred_countries', []);

			if(
				(typeof(preferred_countries) === 'object') &&
				(preferred_countries.length > 0)
			) {

				config.preferredCountries = preferred_countries.map(function(row) { return row.country_alpha_2; });
				config.countrySearch = false;
			}

			// Initialize intlTelInput
			var iti = window.intlTelInput($(this)[0], config);

			// Get field wrapper
			var field_wrapper_obj = ws_this.get_field_wrapper($(this));

			// Set flag container height (so invalid feedback does not break the styling)
//			$('.iti__flag-container', field_wrapper_obj).css({height:$('input[type="tel"]', field_wrapper_obj).outerHeight()});

			// Get invalid feedback object
			var invalid_feedback_obj = ws_this.get_invalid_feedback_obj($(this));

			// Move invalid feedback
			invalid_feedback_obj.insertAfter($(this));

			// Move label if position is set to inside
			if(ws_this.get_label_position(field) == 'inside') {

				// Get label object
				var label_obj = ws_this.get_label_obj($(this));

				// Move label
				label_obj.insertAfter($(this));

				// Inside resize
				ws_this.form_tel_inside_resize(this, field_wrapper_obj, label_obj);

				$(this).on('countrychange', function() {

					ws_this.form_tel_inside_resize(this, field_wrapper_obj, label_obj);
				});
			}

			// Validation
			var validate_number = (ws_this.get_object_meta_value(field, 'intl_tel_input_validate_number', '') == 'on');

			if(validate_number) {

				$(this).on('keyup change input paste countrychange', function() {

					ws_this.form_tel_validate($(this));
				});
			}

			// Fire real time form validation
			ws_this.form_validate_real_time_process(false, false);
		});
	}

	$.WS_Form.prototype.form_tel_validate = function(obj) {

		// Get iti instance
		var iti = window.intlTelInputGlobals.getInstance(obj[0]);

		// Check if valid
		if(
			(obj.val() == '') ||
			iti.isValidNumber()
		) {

			// Reset feedback
			this.set_invalid_feedback(obj, '');

		} else {

			// Get field ID
			var field_id = this.get_field_id(obj);

			// Get field
			var field = this.field_data_cache[field_id];

			// Config - Allow dropdown
			var intl_tel_input_errors = [

				this.get_object_meta_value(field, 'intl_tel_input_label_number', this.language('iti_number')),
				this.get_object_meta_value(field, 'intl_tel_input_label_country_code', this.language('iti_country_code')),
				this.get_object_meta_value(field, 'intl_tel_input_label_short', this.language('iti_short')),
				this.get_object_meta_value(field, 'intl_tel_input_label_long', this.language('iti_long')),
				this.get_object_meta_value(field, 'intl_tel_input_label_number', this.language('iti_number'))
			];

			// Get error number
			var error_code = iti.getValidationError();

			// Get invalid feedback
			var invalid_feedback = (typeof(intl_tel_input_errors[error_code]) !== 'undefined') ? intl_tel_input_errors[error_code] : this.language('iti_number');

			// Invalid feedback
			this.set_invalid_feedback(obj, invalid_feedback);
		}
	}

	$.WS_Form.prototype.form_tel_inside_resize = function(input_obj, field_wrapper_obj, label_obj) {

		// Set left position of label
		label_obj.css({left:$(input_obj).css('padding-left')});

		// Transform X calculation
		var transform_x_padding_left = parseFloat($(input_obj).css('padding-left'));
		var transform_x_padding_right = parseFloat($(input_obj).css('padding-right'));
		var transform_x = transform_x_padding_left - transform_x_padding_right;

		// Set transform variable
		field_wrapper_obj[0].style.setProperty('--wsf-field-tel-transform-x', (transform_x * -1) + 'px');
	}

	$.WS_Form.prototype.form_tel_post = function(form_data) {

		var ws_this = this;

		// Process international telephone inputs
		$('[data-intl-tel-input]', this.form_canvas_obj).each(function() {

			// Get iti instance
			var iti = window.intlTelInputGlobals.getInstance($(this)[0]);

			// Get field ID
			var field_id = ws_this.get_field_id($(this));

			// Get field
			var field = ws_this.field_data_cache[field_id];

			// Get return format
			var return_format = ws_this.get_object_meta_value(field, 'intl_tel_input_format', '');

			// Get number
			switch(return_format) {

				case 'INTERNATIONAL' :
				case 'NATIONAL' :
				case 'E164' :
				case 'RFC3966' :

					// Return if intlTelInputUtils is not yet initialized on the page (prevents JS error if form submitted immediately)
					if(typeof(intlTelInputUtils) === 'undefined') { return; }

					var field_value = iti.getNumber(intlTelInputUtils.numberFormat[return_format]);

					break;

				default :

					return;
			}

			// Get field name
			var field_name = $(this).attr('name');

			// Override form data
			form_data.set(field_name, field_value);
		});
	}

})(jQuery);
