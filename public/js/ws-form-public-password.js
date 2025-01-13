(function($) {

	'use strict';

	// Form password visibility_toggle
	$.WS_Form.prototype.form_password_visibility_toggle = function() {

		var ws_this = this;

		$('[data-wsf-password-visibility-toggle]:not([data-init-wsf-password-visibility-toggle])', this.form_canvas_obj).each(function() {

			$(this).on('click keydown', function(e) {

				// If keydown, check for space or enter
				if(e.type === 'keydown') {

					if(
						(e.keyCode !== 13) && (e.keyCode !== 32)
					) {
						return;
					}
				}

				e.preventDefault();

				var field_wrapper_obj = ws_this.get_field_wrapper($(this));

				var input_obj = $('input', field_wrapper_obj);

				var field_type = input_obj.attr('type');

				var field = ws_this.get_field(input_obj);

				switch(field_type) {

					case 'password' :

						ws_this.form_password_visibility_on($(this), field, input_obj);

						break;

					case 'text' :

						ws_this.form_password_visibility_off($(this), field, input_obj);

						break;
				}
			});

			// Set field as initialized
			$(this).attr('data-init-wsf-password-visibility-toggle', '');
		});
	}

	// Form password - Visibilty On
	$.WS_Form.prototype.form_password_visibility_on = function(obj, field, input_obj) {

		input_obj.attr('type', 'text');

		var title = this.get_object_meta_value(field, 'text_password_visibility_toggle_on', '');
		if(title == '') { title = this.language('password_visibility_toggle_on'); }
		obj.attr('title', title).attr('aria-live', 'polite').attr('aria-pressed', 'true');

		$('g.wsf-password-visibility-off', obj).hide();
		$('g.wsf-password-visibility-on', obj).show();
	}

	// Form password - Visibilty Off
	$.WS_Form.prototype.form_password_visibility_off = function(obj, field, input_obj) {

		input_obj.attr('type', 'password');

		var title = this.get_object_meta_value(field, 'text_password_visibility_toggle_off', '');
		if(title == '') { title = this.language('password_visibility_toggle_off'); }
		obj.attr('title', title).attr('aria-live', 'polite').attr('aria-pressed', 'false');

		$('g.wsf-password-visibility-off', obj).show();
		$('g.wsf-password-visibility-on', obj).hide();
	}

	// Form password - Generate
	$.WS_Form.prototype.form_password_generate = function() {

		var ws_this = this;

		$('[data-wsf-password-generate]:not([data-init-wsf-password-generate])', this.form_canvas_obj).each(function() {

			$(this).on('click keydown', function(e) {

				// If keydown, check for space or enter
				if(e.type === 'keydown') {

					if(
						(e.keyCode !== 13) && (e.keyCode !== 32)
					) {
						return;
					}
				}

				e.preventDefault();

				var field_wrapper_obj = ws_this.get_field_wrapper($(this));

				var input_obj = $('input', field_wrapper_obj);

				var field_type = input_obj.attr('type');

				var field = ws_this.get_field(input_obj);

				// Determine max length
				var max_length = input_obj.attr('max-length');
				if(!max_length) { max_length = 16; }
				max_length = parseInt(max_length, 10);
				if(max_length < 1) { max_length = 16; }

				// Set password
				input_obj.val(ws_this.generate_password(24)).trigger('change');

				// Make password visible
				ws_this.form_password_visibility_on($('[data-wsf-password-visibility-toggle]', field_wrapper_obj), field, input_obj);
			});

			// Set field as initialized
			$(this).attr('data-init-wsf-password-generate', '');
		});
	}

	// Form password strength meters
	$.WS_Form.prototype.form_password_strength_meter = function() {

		var ws_this = this;

		if(
			(typeof(wp) === 'undefined') ||
			(typeof(wp.passwordStrength) === 'undefined') ||
			(typeof(wp.passwordStrength.meter) === 'undefined')
		) {

			return;
		}

		$('[data-password-strength-meter]:not([data-init-password-strength-meter])', this.form_canvas_obj).each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-password-strength-meter', '');

			// Strength meter markup
			if(ws_form_settings.styler_enabled) {

				$(this).after('<ul><li /><li /><li /></ul>');
			}

			// Add password strength meter message div
			var field_wrapper = ws_this.get_field_wrapper($(this));

			// Get field ID
			var field_id = ws_this.get_field_id($(this));

			// Get repeatable suffix
			var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

			// Get invalid feedback object
			var invalid_feedback_obj = ws_this.get_invalid_feedback_obj($(this));

			// Help classes
			var class_help_array = ws_this.get_field_value_fallback('password', false, 'class_help_post', []);
			var help_class = class_help_array.join(' ');

			// Build pasword strength meter div
			var password_strength_meter_div = '<div id="' + ws_this.esc_attr(ws_this.form_id_prefix + 'password-strength-meter-' + field_id + section_repeatable_suffix) + '" class="' + ws_this.esc_attr(help_class) + '"></div>';

			// Add message div
			if(invalid_feedback_obj.length) {

				// Insert before help
				$(password_strength_meter_div).insertBefore(invalid_feedback_obj);

			} else {

				// Insert at end
				field_wrapper.append(password_strength_meter_div);
			}

			// Initial run
			ws_this.form_password_strength_meter_process($(this));

			// Event handling
			$(this).on('change keyup paste', function() {

				// Run on keyup
				ws_this.form_password_strength_meter_process($(this));
			});
		});
	}

	$.WS_Form.prototype.form_password_strength_meter_process = function(obj) {

		// Get field wrapper
		var field_wrapper = this.get_field_wrapper(obj);

		// Get field ID
		var field_id = this.get_field_id(obj);

		// Get repeatable suffix
		var section_repeatable_suffix = this.get_section_repeatable_suffix(obj);

		// Get field value
		var field_value = $('#' + this.form_id_prefix + 'field-' + field_id + section_repeatable_suffix, field_wrapper).val();

	    // Get result DOM element
	    var password_strength_result = $('#' + this.form_id_prefix + 'password-strength-meter-' + field_id + section_repeatable_suffix, field_wrapper);

		// If empty, clear result
		if(!field_value) {

			password_strength_result.html('');
			obj.attr('data-password-strength-meter', '');
			this.set_invalid_feedback(obj, '');
			return;
		}

		// Get password strength
	    var field_value_strength = wp.passwordStrength.meter(field_value, wp.passwordStrength.userInputBlacklist(), field_value);

	    // Get messaging mask types for current framework
		var types = this.get_framework_config_value('message', 'types');

		// Clear existing classes
		for(var type_id in types) {

			if(!types.hasOwnProperty(type_id)) { continue; }

			if((typeof(types[type_id]) !== 'undefined') && (typeof(types[type_id].text_class) !== 'undefined')) {

				password_strength_result.removeClass(types[type_id].text_class);
			}
		}

		var field = this.field_data_cache[field_id];

		switch (field_value_strength) {

			case 2 :

				var type_id = 'danger';
				var language_id = 'bad';
				break;

			case 3 :

				var type_id = 'warning';
				var language_id = 'good';
				break;

			case 4 :

				var type_id = 'success';
				var language_id = 'strong';
				break;

			default :

				var type_id = 'danger';
				var language_id = 'short';
		}

		// Build label and set HTML
		var password_strength_label = this.get_object_meta_value(field, 'text_password_strength_' + language_id, '');
		if(password_strength_label == '') { password_strength_label = this.language('password_strength_' + language_id); }
		password_strength_result.html(password_strength_label).attr('aria-live', 'polite');

		// Check for minimum strength
		var password_strength_invalid = obj.attr('data-password-strength-invalid');
		if(password_strength_invalid) {

			var password_strength_invalid_message = this.get_object_meta_value(field, 'text_password_strength_invalid', '');
			if(password_strength_invalid_message == '') { password_strength_invalid_message = this.language('password_strength_invalid'); }

			var invalid_message = this.get_invalid_feedback(obj);

			if(field_value_strength < parseInt(password_strength_invalid, 10)) {

				if(invalid_message === '') {

					this.set_invalid_feedback(obj, password_strength_invalid_message);
				}

			} else {

				if(invalid_message === password_strength_invalid_message) {

					this.set_invalid_feedback(obj, '');
				}
			}
 		}

		// Apply data attribute to input
		obj.attr('data-password-strength-meter', type_id);

		// Add class
		if((typeof(types[type_id]) !== 'undefined') && (typeof(types[type_id].text_class) !== 'undefined')) {

			password_strength_result.addClass(types[type_id].text_class);
		}
	}

	// Generate password
	$.WS_Form.prototype.generate_password = function(length) {

		if(typeof(length) === 'undefined') { length = 16; }

		// Valid characters per https://developer.wordpress.org/reference/functions/wp_generate_password/
		var characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';

		// Generate password
		var password = '';
		for(var i = 0; i < length; ++i) { password += characters.charAt(Math.floor(Math.random() * characters.length)); }

		return password;
	}

})(jQuery);
