(function($) {

	'use strict';

	// Adds recaptcha elements
	$.WS_Form.prototype.form_recaptcha = function() {

		var ws_this = this;

		// Get reCAPTCHA objects
		var recaptcha_objects = $('[data-recaptcha]', this.form_canvas_obj);
		var recaptcha_objects_count = recaptcha_objects.length;
		if(!recaptcha_objects_count) { return false;}

		// Should header script be loaded
		if(!$('#wsf-recaptcha-script-head').length) {

			var recaptcha_script_head = '<script id="wsf-recaptcha-script-head">';
			recaptcha_script_head += 'var wsf_recaptcha_loaded = false;';
			recaptcha_script_head += 'function wsf_recaptcha_onload() {';
			recaptcha_script_head += 'wsf_recaptcha_loaded = true;';
			recaptcha_script_head += '}';
			recaptcha_script_head += '</script>';

			$('head').append(recaptcha_script_head);
		}

		// Work out what type of reCAPTCHA should load
		var recaptcha_version = ($('[data-recaptcha-type="v3_default"]', this.form_canvas_obj).length) ? 3 : 2;

		// Should reCAPTCHA script be called?
		if(!window['___grecaptcha_cfg'] && !$('#wsf-recaptcha-script-body').length) {

			switch(recaptcha_version) {

				case 2 :

					var recaptcha_script_body = '<script id="wsf-recaptcha-script-body" src="https://www.google.com/recaptcha/api.js?onload=wsf_recaptcha_onload&render=explicit" async defer></script>';
					break;

				case 3 :

					var recaptcha_site_key = $('[data-recaptcha-type="v3_default"]', this.form_canvas_obj).eq(0).attr('data-site-key');
					var recaptcha_script_body = '<script id="wsf-recaptcha-script-body" src="https://www.google.com/recaptcha/api.js?onload=wsf_recaptcha_onload&render=' + recaptcha_site_key + '"></script>';
					break;
			}
			$('body').append(recaptcha_script_body);

		} else {

			window.wsf_recaptcha_loaded = true;
		}

		// Reset reCAPTCHA arrays
		this.recaptchas = [];
		this.recaptchas_v2_default = [];
		this.recaptchas_v2_invisible = [];
		this.recaptchas_v3_default = [];

		recaptcha_objects.each(function() {

			// Name
			var name = $(this).attr('name');

			// ID
			var recaptcha_id = $(this).attr('id');
			if((recaptcha_id === undefined) || (recaptcha_id == '')) { return false; }

			// Site key
			var recaptcha_site_key = $(this).attr('data-site-key');
			if((recaptcha_site_key === undefined) || (recaptcha_site_key == '')) { return false; }

			// Recaptcha type
			var recaptcha_recaptcha_type = $(this).attr('data-recaptcha-type');
			if((recaptcha_recaptcha_type === undefined) || (['v2_default', 'v2_invisible', 'v3_default'].indexOf(recaptcha_recaptcha_type) == -1)) { recaptcha_recaptcha_type = 'default'; }

			// Type
			var recaptcha_type = $(this).attr('data-type');
			if((recaptcha_type === undefined) || (['image', 'audio'].indexOf(recaptcha_type) == -1)) { recaptcha_type = 'image'; }

			// Language (Optional)
			var recaptcha_language = $(this).attr('data-language');
			if(recaptcha_language === undefined) { recaptcha_language = ''; }

			// Action
			var recaptcha_action = $(this).attr('data-recaptcha-action');
			if((recaptcha_action === undefined) || (recaptcha_action == '')) { recaptcha_action = 'ws_form_loaded_#form_id'; }

			switch(recaptcha_recaptcha_type) {

				case 'v2_default' :

					// Size
					var recaptcha_size = $(this).attr('data-size');
					if((recaptcha_size === undefined) || (['normal', 'compact', 'invisible'].indexOf(recaptcha_size) == -1)) { recaptcha_size = 'normal'; }

					// Theme (Default only)
					var recaptcha_theme = $(this).attr('data-theme');
					if((recaptcha_theme === undefined) || (['light', 'dark'].indexOf(recaptcha_theme) == -1)) { recaptcha_theme = 'light'; }

					// Classes
					var class_recaptcha_invalid_label_array = ws_this.get_field_value_fallback('recaptcha', false, 'class_invalid_label', []);
					var class_recaptcha_invalid_field_array = ws_this.get_field_value_fallback('recaptcha', false, 'class_invalid_field', []);
					var class_recaptcha_invalid_invalid_feedback_array = ws_this.get_field_value_fallback('recaptcha', false, 'class_invalid_invalid_feedback', []);
					var class_recaptcha_valid_label_array = ws_this.get_field_value_fallback('recaptcha', false, 'class_valid_label', []);
					var class_recaptcha_valid_field_array = ws_this.get_field_value_fallback('recaptcha', false, 'class_valid_field', []);
					var class_recaptcha_valid_invalid_feedback_array = ws_this.get_field_value_fallback('recaptcha', false, 'class_valid_invalid_feedback', []);

					// Process recaptcha
					var recaptcha_obj_field = $(this);
					var recaptcha_obj_wrapper = recaptcha_obj_field.closest('[data-id]');
					var recaptcha_obj_label = $('label', recaptcha_obj_wrapper);
					var recaptcha_obj_invalid_feedback = $('#' + this.form_id_prefix + 'invalid-feedback-' + recaptcha_id, recaptcha_obj_wrapper, ws_this.form_canvas_obj);

					var config = {'sitekey': recaptcha_site_key, 'type': recaptcha_type, 'theme': recaptcha_theme, 'size': recaptcha_size, 'callback': function(token) {

						// Completed - Label
						recaptcha_obj_label.addClass(class_recaptcha_valid_label_array.join(' '));
						recaptcha_obj_label.removeClass(class_recaptcha_invalid_label_array.join(' '));

						// Completed - Field
						recaptcha_obj_field.addClass(class_recaptcha_valid_field_array.join(' '));
						recaptcha_obj_field.removeClass(class_recaptcha_invalid_field_array.join(' '));

						// Completed - Feedback
						recaptcha_obj_invalid_feedback.addClass(class_recaptcha_valid_invalid_feedback_array.join(' '));
						recaptcha_obj_invalid_feedback.removeClass(class_recaptcha_invalid_invalid_feedback_array.join(' '));

						// Run conditions
						ws_this.recaptcha_conditions_run();

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);

					}, 'expired-callback': function() {

						// Empty - Label
						recaptcha_obj_label.addClass(class_recaptcha_invalid_label_array.join(' '));
						recaptcha_obj_label.removeClass(class_recaptcha_valid_label_array.join(' '));

						// Empty - Field
						recaptcha_obj_field.addClass(class_recaptcha_invalid_field_array.join(' '));
						recaptcha_obj_field.removeClass(class_recaptcha_valid_field_array.join(' '));

						// Empty - Feedback
						recaptcha_obj_invalid_feedback.addClass(class_recaptcha_invalid_invalid_feedback_array.join(' '));
						recaptcha_obj_invalid_feedback.removeClass(class_recaptcha_valid_invalid_feedback_array.join(' '));

						// Run conditions
						ws_this.recaptcha_conditions_run();

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);

					}, 'error-callback': function() {

						// Empty - Label
						recaptcha_obj_label.addClass(class_recaptcha_invalid_label_array.join(' '));
						recaptcha_obj_label.removeClass(class_recaptcha_valid_label_array.join(' '));

						// Empty - Field
						recaptcha_obj_field.addClass(class_recaptcha_invalid_field_array.join(' '));
						recaptcha_obj_field.removeClass(class_recaptcha_valid_field_array.join(' '));

						// Empty - Feedback
						recaptcha_obj_invalid_feedback.addClass(class_recaptcha_invalid_invalid_feedback_array.join(' '));
						recaptcha_obj_invalid_feedback.removeClass(class_recaptcha_valid_invalid_feedback_array.join(' '));

						// Run conditions
						ws_this.recaptcha_conditions_run();

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);
					}};
					if(recaptcha_language != '') { config.hl = recaptcha_language; }

					// Build recaptcha object
					var recaptcha = {'id': false, 'recaptcha_site_key': recaptcha_site_key, 'name': name, 'recaptcha_id': recaptcha_id, 'config': config, 'recaptcha_recaptcha_type': recaptcha_recaptcha_type, 'type': 'v2_default'}

					// Add to recaptcha arrays
					ws_this.recaptchas_v2_default.push(recaptcha);

					ws_this.recaptcha_process(recaptcha);

					break;

				case 'v2_invisible' :

					// Badge (Invisible only)
					var recaptcha_badge = $(this).attr('data-badge');
					if((recaptcha_badge === undefined) || (['bottomright', 'bottomleft', 'inline'].indexOf(recaptcha_badge) == -1)) { recaptcha_badge = 'bottomright'; }

					// Process recaptcha
					var config = {'sitekey': recaptcha_site_key, 'badge': recaptcha_badge, 'size': 'invisible', 'callback': function() {

						// Run conditions
						for(var recaptcha_conditions_index in ws_this.recaptchas_conditions) {

							if(!ws_this.recaptchas_conditions.hasOwnProperty(recaptcha_conditions_index)) { continue; }

							ws_this.recaptchas_conditions[recaptcha_conditions_index]();
						}

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);

						// Form validated
						ws_this.form_post('submit');

					}, 'expired-callback': function() {

						// Run conditions
						for(var recaptcha_conditions_index in ws_this.recaptchas_conditions) {

							if(!ws_this.recaptchas_conditions.hasOwnProperty(recaptcha_conditions_index)) { continue; }

							ws_this.recaptchas_conditions[recaptcha_conditions_index]();
						}

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);

					}, 'error-callback': function() {

						// Throw error
						ws_this.error('error_recaptcha_v2_invisible');

						// Run conditions
						for(var recaptcha_conditions_index in ws_this.recaptchas_conditions) {

							if(!ws_this.recaptchas_conditions.hasOwnProperty(recaptcha_conditions_index)) { continue; }

							ws_this.recaptchas_conditions[recaptcha_conditions_index]();
						}

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);
					}};
					if(recaptcha_language != '') { config.hl = recaptcha_language; }

					// Build recaptcha object
					var recaptcha = {'id': false, 'recaptcha_site_key': recaptcha_site_key, 'name': name, 'recaptcha_id': recaptcha_id, 'config': config, 'recaptcha_recaptcha_type': recaptcha_recaptcha_type, 'type': 'v2_invisible'}

					// Add to recaptcha arrays
					ws_this.recaptchas_v2_invisible.push(recaptcha);

					// Process recaptcha
					ws_this.recaptcha_process(recaptcha);

					break;				

				case 'v3_default' :

					// Parse recaptcha_action
					recaptcha_action = ws_this.parse_variables_process(recaptcha_action).output;

					// Config
					var config = {'action': recaptcha_action};

					// Build recaptcha object
					var recaptcha = {'id': false, 'recaptcha_site_key': recaptcha_site_key, 'name': name, 'recaptcha_id': recaptcha_id, 'config': config, 'recaptcha_recaptcha_type': recaptcha_recaptcha_type, 'type': 'v3_default'}

					// Add to recaptcha arrays
					ws_this.recaptchas_v3_default.push(recaptcha)

					// Process recaptcha
					ws_this.recaptcha_process(recaptcha);

					break;
			}
		});
	}

	// reCAPTCHA run conditions
	$.WS_Form.prototype.recaptcha_conditions_run = function() {

		// Run conditions
		for(var recaptcha_conditions_index in this.recaptchas_conditions) {

			if(!this.recaptchas_conditions.hasOwnProperty(recaptcha_conditions_index)) { continue; }

			this.recaptchas_conditions[recaptcha_conditions_index]();
		}
	}

	// Wait until reCAPTCHA loaded, then process
	$.WS_Form.prototype.recaptcha_process = function(recaptcha, total_ms_start) {

		var ws_this = this;

		// Timeout check
		if(typeof(total_ms_start) === 'undefined') { total_ms_start = new Date().getTime(); }
		if((new Date().getTime() - total_ms_start) > this.timeout_recaptcha) {

			this.error('error_timeout_recaptcha');
			return false;
		}

		// Check to see if reCAPTCHA loaded
		if(wsf_recaptcha_loaded) {

			switch(recaptcha.type) {

				case 'v2_default' :

					var id = grecaptcha.render(recaptcha.recaptcha_id, recaptcha.config);
					recaptcha.id = id;
					this.form_validate_real_time_process(false, false);
					break;

				case 'v2_invisible' :

					var id = grecaptcha.render(recaptcha.recaptcha_id, recaptcha.config);
					recaptcha.id = id;
					this.form_validate_real_time_process(false, false);
					break;

				case 'v3_default' :

					// Add hidden field
					if(!$('[name="g-recaptcha-response"]', ws_this.form_canvas_obj).length) {

						ws_this.form_add_hidden_input('g-recaptcha-response');
					}

					break;
			}

			// Add to reCaptcha array
			this.recaptchas.push(recaptcha);

			// Run conditions
			this.recaptcha_conditions_run();

		} else {

			var ws_this = this;
			setTimeout(function() { ws_this.recaptcha_process(recaptcha, total_ms_start); }, this.timeout_interval);
		}
	}

	// reCAPTCHA V2 invisible execute
	$.WS_Form.prototype.recaptcha_v2_invisible_execute = function() {

		var ws_this = this;		

		// Run through each hidden captcha for this form
		for(var recaptchas_v2_invisible_index in this.recaptchas_v2_invisible) {

			if(!this.recaptchas_v2_invisible.hasOwnProperty(recaptchas_v2_invisible_index)) { continue; }

			// Get ID
			var recaptcha = this.recaptchas_v2_invisible[recaptchas_v2_invisible_index];
			var recaptcha_id = recaptcha.id;

			// Execute
			grecaptcha.execute(recaptcha_id);

			// Fire real time form validation
			ws_this.form_validate_real_time_process(false, false);
		}
	}

	// reCAPTCHA - Reset
	$.WS_Form.prototype.recaptcha_reset = function() {

		// Run through each reCAPTCHA for this form and reset it
		for(var recaptchas_index in this.recaptchas) {

			if(!this.recaptchas.hasOwnProperty(recaptchas_index)) { continue; }

			// Get reCAPTCHA
			var recaptcha = this.recaptchas[recaptchas_index];

			// Reset
			switch(recaptcha.recaptcha_recaptcha_type) {

				case 'v3_default' :

					// Reset V3 - Nothing to do
					break;

				default :

					// Get ID
					var recaptcha_id = recaptcha.id;

					// Reset V2
					if(recaptcha_id !== false) {

						grecaptcha.reset(recaptcha_id);
					}
			}
		}
	}

	// reCAPTCHA V2 invisible execute
	$.WS_Form.prototype.recaptcha_v3_default_execute = function() {

		var ws_this = this;		

		// Run through each hidden captcha for this form
		for(var recaptchas_v3_default_index in this.recaptchas_v3_default) {

			if(!this.recaptchas_v3_default.hasOwnProperty(recaptchas_v3_default_index)) { continue; }

			// Get recaptcha
			var recaptcha = this.recaptchas_v3_default[recaptchas_v3_default_index];

			// Execute
			ws_this.recaptcha_v3_default_execute_single(recaptcha);
		}
	}

	// reCAPTCHA - V3 - Execute - Single
	$.WS_Form.prototype.recaptcha_v3_default_execute_single = function(recaptcha) {

		var ws_this = this;

		grecaptcha.execute(recaptcha.recaptcha_site_key, recaptcha.config).then(function(token){

			// Update V3 token
			$('[name="g-recaptcha-response"]', ws_this.form_canvas_obj).val(token);

			// Fire real time form validation
			ws_this.form_validate_real_time_process(false, false);

			// Log
			ws_this.log('log_recaptcha_v3_action_fired', recaptcha.config.action);
			// Form validated
			ws_this.form_post('submit');
		});
	}

	// reCAPTCHA - Get response by name
	$.WS_Form.prototype.recaptcha_get_response_by_name = function(name) {

		// Run through each reCAPTCHA and look for name
		for(var recaptchas_index in this.recaptchas) {

			if(!this.recaptchas.hasOwnProperty(recaptchas_index)) { continue; }

			var recaptcha = this.recaptchas[recaptchas_index];
			var recaptcha_id = recaptcha.id;

			// If name found, return response
			if(
				(recaptcha.name == name) &&
				(recaptcha_id !== false)
			) {

				return grecaptcha.getResponse(recaptcha_id);
			}
		}

		return '';
	}

	// Adds hcaptcha elements
	$.WS_Form.prototype.form_hcaptcha = function() {

		var ws_this = this;

		// Get hCaptcha objects
		var hcaptcha_objects = $('[data-hcaptcha]', this.form_canvas_obj);
		var hcaptcha_objects_count = hcaptcha_objects.length;
		if(!hcaptcha_objects_count) { return false;}

		// Should header script be loaded
		if(!$('#wsf-hcaptcha-script-head').length) {

			var hcaptcha_script_head = '<script id="wsf-hcaptcha-script-head">';
			hcaptcha_script_head += 'var wsf_hcaptcha_loaded = false;';
			hcaptcha_script_head += 'function wsf_hcaptcha_onload() {';
			hcaptcha_script_head += 'wsf_hcaptcha_loaded = true;';
			hcaptcha_script_head += '}';
			hcaptcha_script_head += '</script>';

			$('head').append(hcaptcha_script_head);
		}

		// Should hCaptcha script be called?
		if(!window['hcaptcha'] && !$('#wsf-hcaptcha-script-body').length) {

			var hcaptcha_script_body = '<script id="wsf-hcaptcha-script-body" src="https://js.hcaptcha.com/1/api.js?onload=wsf_hcaptcha_onload&render=explicit&recaptchacompat=off" async defer></script>';
			$('body').append(hcaptcha_script_body);
		}

		// Reset hCaptcha arrays
		this.hcaptchas = [];
		this.hcaptchas_default = [];
		this.hcaptchas_invisible = [];

		hcaptcha_objects.each(function() {

			// Name
			var name = $(this).attr('name');

			// ID
			var hcaptcha_id = $(this).attr('id');
			if((hcaptcha_id === undefined) || (hcaptcha_id == '')) { return false; }

			// Site key
			var hcaptcha_site_key = $(this).attr('data-site-key');
			if((hcaptcha_site_key === undefined) || (hcaptcha_site_key == '')) { return false; }

			// Type
			var hcaptcha_type = $(this).attr('data-hcaptcha-type');
			if((hcaptcha_type === undefined) || (['default', 'invisible'].indexOf(hcaptcha_type) == -1)) { hcaptcha_type = 'default'; }

			// Language (Optional)
			var hcaptcha_language = $(this).attr('data-language');
			if(hcaptcha_language === undefined) { hcaptcha_language = ''; }

			switch(hcaptcha_type) {

				case 'default' :

					// Size
					var hcaptcha_size = $(this).attr('data-size');
					if((hcaptcha_size === undefined) || (['normal', 'compact'].indexOf(hcaptcha_size) == -1)) { hcaptcha_size = 'normal'; }

					// Theme (Default only)
					var hcaptcha_theme = $(this).attr('data-theme');
					if((hcaptcha_theme === undefined) || (['light', 'dark'].indexOf(hcaptcha_theme) == -1)) { hcaptcha_theme = 'light'; }

					// Classes
					var class_hcaptcha_invalid_label_array = ws_this.get_field_value_fallback('hcaptcha', false, 'class_invalid_label', []);
					var class_hcaptcha_invalid_field_array = ws_this.get_field_value_fallback('hcaptcha', false, 'class_invalid_field', []);
					var class_hcaptcha_invalid_invalid_feedback_array = ws_this.get_field_value_fallback('hcaptcha', false, 'class_invalid_invalid_feedback', []);
					var class_hcaptcha_valid_label_array = ws_this.get_field_value_fallback('hcaptcha', false, 'class_valid_label', []);
					var class_hcaptcha_valid_field_array = ws_this.get_field_value_fallback('hcaptcha', false, 'class_valid_field', []);
					var class_hcaptcha_valid_invalid_feedback_array = ws_this.get_field_value_fallback('hcaptcha', false, 'class_valid_invalid_feedback', []);

					// Process hcaptcha
					var hcaptcha_obj_field = $(this);
					var hcaptcha_obj_wrapper = hcaptcha_obj_field.closest('[data-id]');
					var hcaptcha_obj_label = $('label', hcaptcha_obj_wrapper);
					var hcaptcha_obj_invalid_feedback = $('#' + this.form_id_prefix + 'invalid-feedback-' + hcaptcha_id, hcaptcha_obj_wrapper, ws_this.form_canvas_obj);

					var config = {'sitekey': hcaptcha_site_key, 'type': hcaptcha_type, 'theme': hcaptcha_theme, 'size': hcaptcha_size, 'callback': function(token) {

						// Completed - Label
						hcaptcha_obj_label.addClass(class_hcaptcha_valid_label_array.join(' '));
						hcaptcha_obj_label.removeClass(class_hcaptcha_invalid_label_array.join(' '));

						// Completed - Field
						hcaptcha_obj_field.addClass(class_hcaptcha_valid_field_array.join(' '));
						hcaptcha_obj_field.removeClass(class_hcaptcha_invalid_field_array.join(' '));

						// Completed - Feedback
						hcaptcha_obj_invalid_feedback.addClass(class_hcaptcha_valid_invalid_feedback_array.join(' '));
						hcaptcha_obj_invalid_feedback.removeClass(class_hcaptcha_invalid_invalid_feedback_array.join(' '));

						// Run conditions
						ws_this.hcaptcha_conditions_run();

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);

					}, 'expired-callback': function() {

						// Empty - Label
						hcaptcha_obj_label.addClass(class_hcaptcha_invalid_label_array.join(' '));
						hcaptcha_obj_label.removeClass(class_hcaptcha_valid_label_array.join(' '));

						// Empty - Field
						hcaptcha_obj_field.addClass(class_hcaptcha_invalid_field_array.join(' '));
						hcaptcha_obj_field.removeClass(class_hcaptcha_valid_field_array.join(' '));

						// Empty - Feedback
						hcaptcha_obj_invalid_feedback.addClass(class_hcaptcha_invalid_invalid_feedback_array.join(' '));
						hcaptcha_obj_invalid_feedback.removeClass(class_hcaptcha_valid_invalid_feedback_array.join(' '));

						// Run conditions
						ws_this.hcaptcha_conditions_run();

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);

					}, 'error-callback': function() {

						// Empty - Label
						hcaptcha_obj_label.addClass(class_hcaptcha_invalid_label_array.join(' '));
						hcaptcha_obj_label.removeClass(class_hcaptcha_valid_label_array.join(' '));

						// Empty - Field
						hcaptcha_obj_field.addClass(class_hcaptcha_invalid_field_array.join(' '));
						hcaptcha_obj_field.removeClass(class_hcaptcha_valid_field_array.join(' '));

						// Empty - Feedback
						hcaptcha_obj_invalid_feedback.addClass(class_hcaptcha_invalid_invalid_feedback_array.join(' '));
						hcaptcha_obj_invalid_feedback.removeClass(class_hcaptcha_valid_invalid_feedback_array.join(' '));

						// Run conditions
						ws_this.hcaptcha_conditions_run();

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);
					}};
					if(hcaptcha_language != '') { config.hl = hcaptcha_language; }

					// Build hcaptcha object
					var hcaptcha = {'id': false, 'hcaptcha_site_key': hcaptcha_site_key, 'name': name, 'hcaptcha_id': hcaptcha_id, 'config': config, 'type': 'default'}

					// Add to hcaptcha arrays
					ws_this.hcaptchas_default.push(hcaptcha);

					ws_this.hcaptcha_process(hcaptcha);

					break;

				case 'invisible' :

					// Badge (Invisible only)
					var hcaptcha_badge = $(this).attr('data-badge');
					if((hcaptcha_badge === undefined) || (['bottomright', 'bottomleft', 'inline'].indexOf(hcaptcha_badge) == -1)) { hcaptcha_badge = 'bottomright'; }

					// Process hcaptcha
					var config = {'sitekey': hcaptcha_site_key, 'badge': hcaptcha_badge, 'size': 'invisible', 'callback': function() {

						// Run conditions
						for(var hcaptcha_conditions_index in ws_this.hcaptchas_conditions) {

							if(!ws_this.hcaptchas_conditions.hasOwnProperty(hcaptcha_conditions_index)) { continue; }

							ws_this.hcaptchas_conditions[hcaptcha_conditions_index]();
						}

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);

						// Form validated
						ws_this.form_post('submit');

					}, 'expired-callback': function() {

						// Run conditions
						for(var hcaptcha_conditions_index in ws_this.hcaptchas_conditions) {

							if(!ws_this.hcaptchas_conditions.hasOwnProperty(hcaptcha_conditions_index)) { continue; }

							ws_this.hcaptchas_conditions[hcaptcha_conditions_index]();
						}

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);

					}, 'error-callback': function() {

						// Throw error
						ws_this.error('error_hcaptcha_invisible');

						// Run conditions
						for(var hcaptcha_conditions_index in ws_this.hcaptchas_conditions) {

							if(!ws_this.hcaptchas_conditions.hasOwnProperty(hcaptcha_conditions_index)) { continue; }

							ws_this.hcaptchas_conditions[hcaptcha_conditions_index]();
						}

						// Fire real time form validation
						ws_this.form_validate_real_time_process(false, false);
					}};
					if(hcaptcha_language != '') { config.hl = hcaptcha_language; }

					// Build hcaptcha object
					var hcaptcha = {'id': false, 'hcaptcha_site_key': hcaptcha_site_key, 'name': name, 'hcaptcha_id': hcaptcha_id, 'config': config, 'type': 'invisible'}

					// Add to hcaptcha array
					ws_this.hcaptchas_invisible.push(hcaptcha)

					// Process hcaptcha
					ws_this.hcaptcha_process(hcaptcha);

					break;
			}
		});
	}

	// hCaptcha run conditions
	$.WS_Form.prototype.hcaptcha_conditions_run = function() {

		// Run conditions
		for(var hcaptcha_conditions_index in this.hcaptchas_conditions) {

			if(!this.hcaptchas_conditions.hasOwnProperty(hcaptcha_conditions_index)) { continue; }

			this.hcaptchas_conditions[hcaptcha_conditions_index]();
		}
	}

	// Wait until hCaptcha loaded, then process
	$.WS_Form.prototype.hcaptcha_process = function(hcaptcha_config, total_ms_start) {

		var ws_this = this;

		// Timeout check
		if(typeof(total_ms_start) === 'undefined') { total_ms_start = new Date().getTime(); }
		if((new Date().getTime() - total_ms_start) > this.timeout_hcaptcha) {

			this.error('error_timeout_hcaptcha');
			return false;
		}

		// Check to see if hCaptcha loaded
		if(wsf_hcaptcha_loaded) {

			switch(hcaptcha_config.type) {

				case 'default' :

					var id = hcaptcha.render(hcaptcha_config.hcaptcha_id, hcaptcha_config.config);
					hcaptcha_config.id = id;
					this.form_validate_real_time_process(false, false);
					break;

				case 'invisible' :

					var id = hcaptcha.render(hcaptcha_config.hcaptcha_id, hcaptcha_config.config);
					hcaptcha_config.id = id;
					this.form_validate_real_time_process(false, false);
					break;
			}

			// Add to hcaptcha array
			this.hcaptchas.push(hcaptcha_config);

			// Run conditions
			this.hcaptcha_conditions_run();

		} else {

			var ws_this = this;
			setTimeout(function() { ws_this.hcaptcha_process(hcaptcha_config, total_ms_start); }, this.timeout_interval);
		}
	}

	// hCaptcha V2 invisible execute
	$.WS_Form.prototype.hcaptcha_invisible_execute = function() {

		var ws_this = this;		

		// Run through each hidden captcha for this form
		for(var hcaptchas_invisible_index in this.hcaptchas_invisible) {

			if(!this.hcaptchas_invisible.hasOwnProperty(hcaptchas_invisible_index)) { continue; }

			// Get ID
			var hcaptcha_config = this.hcaptchas_invisible[hcaptchas_invisible_index];
			var hcaptcha_id = hcaptcha_config.id;

			// Execute
			hcaptcha.execute(hcaptcha_id);

			// Fire real time form validation
			ws_this.form_validate_real_time_process(false, false);
		}
	}

	// hCaptcha - Reset
	$.WS_Form.prototype.hcaptcha_reset = function() {

		// Run through each hCaptcha for this form and reset it
		for(var hcaptchas_index in this.hcaptchas) {

			if(!this.hcaptchas.hasOwnProperty(hcaptchas_index)) { continue; }

			// Get ID
			var hcaptcha_config = this.hcaptchas[hcaptchas_index];
			var hcaptcha_id = hcaptcha_config.id;

			// Reset
			hcaptcha.reset(hcaptcha_id);
		}
	}

	// hCaptcha - Get response by name
	$.WS_Form.prototype.hcaptcha_get_response_by_name = function(name) {

		// Run through each hCaptcha and look for name
		for(var hcaptchas_index in this.hcaptchas) {

			if(!this.hcaptchas.hasOwnProperty(hcaptchas_index)) { continue; }

			// Get ID
			var hcaptcha_config = this.hcaptchas[hcaptchas_index];
			var hcaptcha_id = hcaptcha_config.id;

			// If name found, return response
			if(hcaptcha_config.name == name) { return hcaptcha.getResponse(hcaptcha_id); }
		}

		return '';
	}

	// Adds turnstile elements
	$.WS_Form.prototype.form_turnstile = function() {

		var ws_this = this;

		// Get Turnstile objects
		var turnstile_objects = $('[data-turnstile]', this.form_canvas_obj);
		var turnstile_objects_count = turnstile_objects.length;
		if(!turnstile_objects_count) { return false;}

		// Should header script be loaded
		if(!$('#wsf-turnstile-script-head').length) {

			var turnstile_script_head = '<script id="wsf-turnstile-script-head">';
			turnstile_script_head += 'var wsf_turnstile_loaded = false;';
			turnstile_script_head += 'function wsf_turnstile_onload() {';
			turnstile_script_head += 'wsf_turnstile_loaded = true;';
			turnstile_script_head += '}';
			turnstile_script_head += '</script>';

			$('head').append(turnstile_script_head);
		}

		// Should Turnstile script be called?
		if(!window['turnstile'] && !$('#wsf-turnstile-script-body').length) {

			var turnstile_script_body = '<script id="wsf-turnstile-script-body" src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=wsf_turnstile_onload&render=explicit" async defer></script>';
			$('body').append(turnstile_script_body);
		}

		// Reset Turnstile arrays
		this.turnstiles = [];
		this.turnstiles_default = [];

		turnstile_objects.each(function() {

			// Name
			var name = $(this).attr('name');

			// ID
			var turnstile_id = $(this).attr('id');
			if((turnstile_id === undefined) || (turnstile_id == '')) { return false; }

			// Site key
			var turnstile_site_key = $(this).attr('data-site-key');
			if((turnstile_site_key === undefined) || (turnstile_site_key == '')) { return false; }

			// Theme
			var turnstile_theme = $(this).attr('data-theme');
			if((turnstile_theme === undefined) || (['light', 'dark', 'auto'].indexOf(turnstile_theme) == -1)) { turnstile_theme = 'auto'; }

			// Size
			var turnstile_size = $(this).attr('data-size');
			if((turnstile_size === undefined) || (['normal', 'compact'].indexOf(turnstile_size) == -1)) { turnstile_size = 'normal'; }

			// Appearance
			var turnstile_appearance = $(this).attr('data-appearance');
			if((turnstile_appearance === undefined) || (['always', 'execute', 'interaction-only'].indexOf(turnstile_appearance) == -1)) { turnstile_appearance = 'always'; }

			// Classes
			var class_turnstile_invalid_label_array = ws_this.get_field_value_fallback('turnstile', false, 'class_invalid_label', []);
			var class_turnstile_invalid_field_array = ws_this.get_field_value_fallback('turnstile', false, 'class_invalid_field', []);
			var class_turnstile_invalid_invalid_feedback_array = ws_this.get_field_value_fallback('turnstile', false, 'class_invalid_invalid_feedback', []);
			var class_turnstile_valid_label_array = ws_this.get_field_value_fallback('turnstile', false, 'class_valid_label', []);
			var class_turnstile_valid_field_array = ws_this.get_field_value_fallback('turnstile', false, 'class_valid_field', []);
			var class_turnstile_valid_invalid_feedback_array = ws_this.get_field_value_fallback('turnstile', false, 'class_valid_invalid_feedback', []);

			// Process turnstile
			var turnstile_obj_field = $(this);
			var turnstile_obj_wrapper = turnstile_obj_field.closest('[data-id]');
			var turnstile_obj_label = $('label', turnstile_obj_wrapper);
			var turnstile_obj_invalid_feedback = $('#' + this.form_id_prefix + 'invalid-feedback-' + turnstile_id, turnstile_obj_wrapper, ws_this.form_canvas_obj);

			var config = {

				'sitekey': turnstile_site_key,
				'theme': turnstile_theme,
				'size': turnstile_size,
				'appearance': turnstile_appearance,

				'callback': function(token) {

					// Completed - Label
					turnstile_obj_label.addClass(class_turnstile_valid_label_array.join(' '));
					turnstile_obj_label.removeClass(class_turnstile_invalid_label_array.join(' '));

					// Completed - Field
					turnstile_obj_field.addClass(class_turnstile_valid_field_array.join(' '));
					turnstile_obj_field.removeClass(class_turnstile_invalid_field_array.join(' '));

					// Completed - Feedback
					turnstile_obj_invalid_feedback.addClass(class_turnstile_valid_invalid_feedback_array.join(' '));
					turnstile_obj_invalid_feedback.removeClass(class_turnstile_invalid_invalid_feedback_array.join(' '));

					// Run conditions
					ws_this.turnstile_conditions_run();

					// Fire real time form validation
					ws_this.form_validate_real_time_process(false, false);

				},

				'expired-callback': function() {

					// Empty - Label
					turnstile_obj_label.addClass(class_turnstile_invalid_label_array.join(' '));
					turnstile_obj_label.removeClass(class_turnstile_valid_label_array.join(' '));

					// Empty - Field
					turnstile_obj_field.addClass(class_turnstile_invalid_field_array.join(' '));
					turnstile_obj_field.removeClass(class_turnstile_valid_field_array.join(' '));

					// Empty - Feedback
					turnstile_obj_invalid_feedback.addClass(class_turnstile_invalid_invalid_feedback_array.join(' '));
					turnstile_obj_invalid_feedback.removeClass(class_turnstile_valid_invalid_feedback_array.join(' '));

					// Run conditions
					ws_this.turnstile_conditions_run();

					// Fire real time form validation
					ws_this.form_validate_real_time_process(false, false);

				},

				'error-callback': function() {

					// Empty - Label
					turnstile_obj_label.addClass(class_turnstile_invalid_label_array.join(' '));
					turnstile_obj_label.removeClass(class_turnstile_valid_label_array.join(' '));

					// Empty - Field
					turnstile_obj_field.addClass(class_turnstile_invalid_field_array.join(' '));
					turnstile_obj_field.removeClass(class_turnstile_valid_field_array.join(' '));

					// Empty - Feedback
					turnstile_obj_invalid_feedback.addClass(class_turnstile_invalid_invalid_feedback_array.join(' '));
					turnstile_obj_invalid_feedback.removeClass(class_turnstile_valid_invalid_feedback_array.join(' '));

					// Run conditions
					ws_this.turnstile_conditions_run();

					// Fire real time form validation
					ws_this.form_validate_real_time_process(false, false);
				}
			};

			// Build turnstile object
			var turnstile = {'id': false, 'turnstile_site_key': turnstile_site_key, 'name': name, 'turnstile_id': turnstile_id, 'config': config, 'type': 'default'}

			// Add to turnstile arrays
			ws_this.turnstiles_default.push(turnstile);

			ws_this.turnstile_process(turnstile);
		});
	}

	// Turnstile run conditions
	$.WS_Form.prototype.turnstile_conditions_run = function() {

		// Run conditions
		for(var turnstile_conditions_index in this.turnstiles_conditions) {

			if(!this.turnstiles_conditions.hasOwnProperty(turnstile_conditions_index)) { continue; }

			this.turnstiles_conditions[turnstile_conditions_index]();
		}
	}

	// Wait until Turnstile loaded, then process
	$.WS_Form.prototype.turnstile_process = function(turnstile_config, total_ms_start) {

		var ws_this = this;

		// Timeout check
		if(typeof(total_ms_start) === 'undefined') { total_ms_start = new Date().getTime(); }
		if((new Date().getTime() - total_ms_start) > this.timeout_turnstile) {

			this.error('error_timeout_turnstile');
			return false;
		}

		// Check to see if Turnstile loaded
		if(wsf_turnstile_loaded) {

			var id = turnstile.render('#' + turnstile_config.turnstile_id, turnstile_config.config);

			turnstile_config.id = id;

			this.form_validate_real_time_process(false, false);

			// Add to turnstile array
			this.turnstiles.push(turnstile_config);

			// Run conditions
			this.turnstile_conditions_run();

		} else {

			var ws_this = this;
			setTimeout(function() { ws_this.turnstile_process(turnstile_config, total_ms_start); }, this.timeout_interval);
		}
	}

	// Turnstile - Reset
	$.WS_Form.prototype.turnstile_reset = function() {

		// Run through each Turnstile for this form and reset it
		for(var turnstiles_index in this.turnstiles) {

			if(!this.turnstiles.hasOwnProperty(turnstiles_index)) { continue; }

			// Get ID
			var turnstile_config = this.turnstiles[turnstiles_index];
			var turnstile_id = turnstile_config.id;

			// Reset
			turnstile.reset(turnstile_id);
		}
	}

	// Turnstile - Get response by name
	$.WS_Form.prototype.turnstile_get_response_by_name = function(name) {

		// Run through each Turnstile and look for name
		for(var turnstiles_index in this.turnstiles) {

			if(!this.turnstiles.hasOwnProperty(turnstiles_index)) { continue; }

			// Get ID
			var turnstile_config = this.turnstiles[turnstiles_index];
			var turnstile_id = turnstile_config.id;

			// If name found, return response
			if(turnstile_config.name == name) { return turnstile.getResponse(turnstile_id); }
		}

		return '';
	}

	// Captcha validation
	$.WS_Form.prototype.form_validate_captcha = function(captchas_array, field_type, form) {

		if(captchas_array.length == 0) { return true; }

		// hCaptcha
		var class_captcha_invalid_label_array = this.get_field_value_fallback(field_type, false, 'class_invalid_label', []);
		var class_captcha_invalid_field_array = this.get_field_value_fallback(field_type, false, 'class_invalid_field', []);
		var class_captcha_invalid_invalid_feedback_array = this.get_field_value_fallback(field_type, false, 'class_invalid_invalid_feedback', []);
		var class_captcha_valid_label_array = this.get_field_value_fallback(field_type, false, 'class_valid_label', []);
		var class_captcha_valid_field_array = this.get_field_value_fallback(field_type, false, 'class_valid_field', []);
		var class_captcha_valid_invalid_feedback_array = this.get_field_value_fallback(field_type, false, 'class_valid_invalid_feedback', []);

		// Run through each hCaptcha for this form
		for(var captchas_index in captchas_array) {

			if(!captchas_array.hasOwnProperty(captchas_index)) { continue; }

			// Get data
			var captcha_config = captchas_array[captchas_index];
			var captcha_id = captcha_config.id;
			var captcha_name = captcha_config.name;

			// If reCAPTCHA is hidden, bypass this reCAPTCHA
			var captcha_obj = $('#' + captcha_config.captcha_id, this.form_canvas_obj);
			if(
				(captcha_obj.attr('data-required-bypass') !== undefined) ||
				(captcha_obj.attr('data-required-bypass-section') !== undefined) ||
				(captcha_obj.attr('data-required-bypass-group') !== undefined)

			) { continue; };

			var captcha_obj_field = $('[name="' + this.esc_selector(captcha_name) + '"]', form);
			var captcha_obj_wrapper = captcha_obj_field.closest('[data-id]');
			var captcha_obj_label = $('label', captcha_obj_wrapper);
			var captcha_obj_invalid_feedback = $('#' + this.form_id_prefix + 'invalid-feedback-' + captcha_id, form);

			var captcha_response = '';

			switch(field_type) {

				case 'recaptcha' :

					captcha_response = grecaptcha.getResponse(captcha_id);
					break;

				case 'hcaptcha' :

					captcha_response = hcaptcha.getResponse(captcha_id);
					break;

				case 'turnstile' :

					captcha_response = turnstile.getResponse(captcha_id);
					break;
			}

			// Execute
			if(!captcha_response) {

				// Empty - Label
				captcha_obj_label.addClass(class_captcha_invalid_label_array.join(' '));
				captcha_obj_label.removeClass(class_captcha_valid_label_array.join(' '));

				// Empty - Field
				captcha_obj_field.addClass(class_captcha_invalid_field_array.join(' '));
				captcha_obj_field.removeClass(class_captcha_valid_field_array.join(' '));

				// Empty - Feedback
				captcha_obj_invalid_feedback.addClass(class_captcha_invalid_invalid_feedback_array.join(' '));
				captcha_obj_invalid_feedback.removeClass(class_captcha_valid_invalid_feedback_array.join(' '));

				// Determine which tab and object to focus
				return {

					object_focus : captcha_obj_field,
					group_index_focus : this.get_group_index(captcha_obj_wrapper)
				};

			} else {

				// Completed - Label
				captcha_obj_label.addClass(class_captcha_valid_label_array.join(' '));
				captcha_obj_label.removeClass(class_captcha_invalid_label_array.join(' '));

				// Completed - Field
				captcha_obj_field.addClass(class_captcha_valid_field_array.join(' '));
				captcha_obj_field.removeClass(class_captcha_invalid_field_array.join(' '));

				// Completed - Feedback
				captcha_obj_invalid_feedback.addClass(class_captcha_valid_invalid_feedback_array.join(' '));
				captcha_obj_invalid_feedback.removeClass(class_captcha_invalid_invalid_feedback_array.join(' '));
			}
		}

		return true;
	}

	// Form - Validate - Silent - Captchas
	$.WS_Form.prototype.form_validate_silent_captchas = function(captchas_array, field_type, form) {

		if(captchas_array.length == 0) { return true; }

		// Run through each captcha for this form
		for(var captchas_index in captchas_array) {

			if(!captchas_array.hasOwnProperty(captchas_index)) { continue; }

			var captcha_config = captchas_array[captchas_index];

			if(captcha_config.id === false) { return false; }

			// If hCaptcha is hidden, bypass this hCaptcha
			var captcha_id = captcha_config.captcha_id;
			var captcha_obj = $('#' + captcha_id, form);
			if(
				(captcha_obj.attr('data-required-bypass') !== undefined) ||
				(captcha_obj.attr('data-required-bypass-section') !== undefined) ||
				(captcha_obj.attr('data-required-bypass-group') !== undefined)

			) { continue; };

			var captcha_response = '';

			switch(field_type) {

				case 'recaptcha' :

					captcha_response = grecaptcha.getResponse(captcha_config.id);
					break;

				case 'hcaptcha' :

					captcha_response = hcaptcha.getResponse(captcha_config.id);
					break;

				case 'turnstile' :

					captcha_response = turnstile.getResponse(captcha_config.id);
					break;
			}

			// Execute
			if(!captcha_response) { return false; }
		}

		// Validated
		return true;
	}

})(jQuery);
