(function($) {

	'use strict';

	// Form analytics
	$.WS_Form.prototype.form_analytics = function() {

		var ws_this = this;

		// gtag setup
		this.form_analytics_gtag();

		// Google Analytics
		var analytics_google = this.get_object_meta_value(this.form, 'analytics_google', false);

		// Check to see if Google Analytics is installed
		if(analytics_google || this.action_ga) { this.form_analytics_google(); }
	}

	// Form analytics - gtag setup
	$.WS_Form.prototype.form_analytics_gtag = function() {

		// Check if gtag needs to be exposed (Latest version of GTM doesn't expose gtag)
		if(
			(typeof(window.dataLayer) === 'object') &&
			(typeof(window.gtag) !== 'function')
		) {
			window.gtag = function() { window.dataLayer.push(arguments); }
		}
	}

	// Form analytics - Google
	$.WS_Form.prototype.form_analytics_google = function(total_ms_start) {

		var ws_this = this;
		var analytics_google_functions = $.WS_Form.analytics.google.functions;

		// Timeout check
		if(typeof(total_ms_start) === 'undefined') { total_ms_start = new Date().getTime(); }
		if((new Date().getTime() - total_ms_start) > this.timeout_analytics_google) {

			// Throw debug error
			this.error('error_timeout_analytics_google', '', 'analytics');

			// gtag setup
			this.form_analytics_gtag();

			return false;
		}

		// Run through Google functions
		var analytics_google_function_found = false;
		for(var analytics_google_function in analytics_google_functions) {

			if(!analytics_google_functions.hasOwnProperty(analytics_google_function)) { continue; }

			var analytics_google_function_config = analytics_google_functions[analytics_google_function];

			// Check to see if the window function is available
			if(window[analytics_google_function]) {

				// Found
				analytics_google_function_found = true;

				// Log
				this.log(analytics_google_function_config.log_found, '', 'analytics');

				// Get Google configuration
				var process_forms = this.get_object_meta_value(this.form, 'analytics_google_event_form', true);
				if(process_forms) { this.form_analytics_process_forms('google', analytics_google_function); }

				var process_tabs = this.get_object_meta_value(this.form, 'analytics_google_event_tab', true);
				if(process_tabs && (Object.keys(this.form.groups).length > 0)) { this.form_analytics_process_tabs('google', analytics_google_function); }

				var process_fields = this.get_object_meta_value(this.form, 'analytics_google_event_field', true);
				if(process_fields) { this.form_analytics_process_fields('google', analytics_google_function); }

				// Save which function should be used for future event firing
				this.analytics_function['google'] = analytics_google_function;

				break;
			}
		}

		// Not found, retry
		if(!analytics_google_function_found) {

			setTimeout(function() { ws_this.form_analytics_google(total_ms_start); }, this.timeout_interval);
		}
	}

	// Form analytics - Process - Forms
	$.WS_Form.prototype.form_analytics_process_forms = function(type, type_function) {

		var ws_this = this;

		if(typeof($.WS_Form.analytics[type]) === 'undefined') { return false;; }

		// Run through all tabs and set up analytics events for this type and function
		var analytics_function = $.WS_Form.analytics[type].functions[type_function];
		var analytics_label = $.WS_Form.analytics[type].label;
		var analytics_function_label = analytics_function.label;

		// Set up handler for wsf-submit and wsf-save events
		$(document).on('wsf-submit wsf-save', function(event, form_object, form_id, instance_id, form_obj, form_canvas_obj, tab_index) {

			// Check event
			if(
				(form_obj !== ws_this.form_obj)

			) { return; }

			// Fire event (form_submit)
			if(
				(event.type == 'wsf-submit') &&
				(type == 'google') &&
				(type_function == 'gtag')
			) {

				var parse_values = {

					'event_action' :		'form_submit', 
					'form_id' :				null,
					'form_name' :			null,
					'form_destination' :	null,
					'form_submit_text' :	null
				};

				// Add parse_values.form_id
				if(typeof(ws_this.form_obj.attr('id')) !== 'undefined') {

					parse_values.form_id = ws_this.form_obj.attr('id');
				}

				// Add parse_values.form_name
				if(typeof(ws_this.form_obj.attr('name')) !== 'undefined') {

					parse_values.form_name = ws_this.form_obj.attr('name');
				}

				// Add parse_values.form_destination
				if(typeof(ws_this.form_obj.attr('action')) !== 'undefined') {

					parse_values.form_destination = ws_this.form_obj.attr('action');
				}

				// Add parse_values.form_submit_text
				var button_submit_obj = $('button[type="submit"]', ws_this.form_canvas_obj);

				if(button_submit_obj.length) {

					var form_submit_text = button_submit_obj.first().html();

					if(form_submit_text) {

						parse_values.form_submit_text = form_submit_text;
					}
				}

				// Fire event
				ws_this.form_analytics_event_fire(

					type,
					type_function,
					parse_values
				);

				// Fire event (gtm.formSubmit / wsf.form.save)
				var parse_values = {

					'event_action' : 	((event.type == 'wsf-submit') ? 'gtm.formSubmit' : 'wsf.form.save'),
					'gtm.element' : 	'#form_obj',
				};

				// Add gtm.elementId
				if(typeof(ws_this.form_obj.attr('id')) !== 'undefined') {

					parse_values['gtm.elementId'] = ws_this.form_obj.attr('id');
				}

				// Add gtm.elementClasses
				if(typeof(ws_this.form_obj.attr('class')) !== 'undefined') {

					parse_values['gtm.elementClasses'] = ws_this.form_obj.attr('class');
				}

				// Add gtm.elementUrl
				if(typeof(ws_this.form_obj.attr('action')) !== 'undefined') {

					parse_values['gtm.elementUrl'] = ws_this.form_obj.attr('action');
				}

				// Fire event
				ws_this.form_analytics_event_fire(

					type,
					type_function,
					parse_values
				);
			}

			// Fire event (WS Form)
			ws_this.form_analytics_event_fire(

				type,
				type_function,
				{
					'event_action' : 	(event.type === 'wsf-submit') ? 'Submit' : 'Save',
					'event_category' :	ws_this.js_string_encode(ws_this.language('analytics_category', ws_this.form.label)),
				}
			);
		});

		// Log event
		ws_this.log('log_analytics_event_form', analytics_label + ' (' + analytics_function_label + ')', 'analytics');
	}

	// Form analytics - Process - Tabs
	$.WS_Form.prototype.form_analytics_process_tabs = function(type, type_function, mode) {

		var ws_this = this;

		if(type == 'data_layer') { return false;; }

		// Run through all tabs and set up analytics events for this type and function
		var analytics_function = $.WS_Form.analytics[type].functions[type_function];
		var analytics_label = $.WS_Form.analytics[type].label;
		var analytics_function_label = analytics_function.label;

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		// Set up handler for tab wsf-click events
		$('[' + selector_href + '^="#' + this.form_id_prefix + 'group-"]:not([data-init-analytics-tab-' + mode + '])', this.form_canvas_obj).each(function() {

			$(this).attr('data-init-analytics-tab-' + mode, '');

			$(this).on('wsf-click', function () {

				if(typeof($(this).attr('data-analytics-event-fired')) === 'undefined') {

					var group_index = $(this).parent().index();

					var group = ws_this.form.groups[group_index];

					var group_label = group.label;

					// Parse values
					var parse_values = {

						'event_action' : 	'Tab',
						'event_category' :	ws_this.js_string_encode(ws_this.language('analytics_category', ws_this.form.label)),
						'event_label' :		ws_this.js_string_encode(group_label),
						'wsf_group_id' :	parseInt(group.id, 10)
					};

					// Fire event
					ws_this.form_analytics_event_fire(

						type,
						type_function,
						parse_values
					);

					$(this).attr('data-analytics-event-fired', 'true');
				}
			});
		});

		// Log event
		ws_this.log('log_analytics_event_tab', analytics_label + ' (' + analytics_function_label + ')', 'analytics');
	}

	// Form analytics - Process - Fields
	$.WS_Form.prototype.form_analytics_process_fields = function(type, type_function, mode) {

		var ws_this = this;

		if(typeof($.WS_Form.analytics[type]) === 'undefined') { return false;; }

		// Run through all fields and set up analytics events for this type and function
		var analytics_function = $.WS_Form.analytics[type].functions[type_function];
		var analytics_label = $.WS_Form.analytics[type].label;
		var analytics_function_label = analytics_function.label;

		for(var field_index in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(field_index)) { continue; }

			var field_type = this.field_data_cache[field_index].type;
			var field_type_config = $.WS_Form.field_type_cache[field_type];

			// Get events
			if(typeof(field_type_config.events) === 'undefined') { continue; }
			var analytics_event = field_type_config.events.event;

			// Get field ID
			var field_id = this.field_data_cache[field_index].id;

			// Check to see if this field is submitted as an array
			var submit_array = (typeof(field_type_config.submit_array) !== 'undefined') ? field_type_config.submit_array : false;

			// Check to see if field is in a repeatable section
			var field_wrapper = $('[data-type][data-id="' + this.esc_selector(field_id) + '"]', this.form_canvas_obj);

			// Run through each wrapper found (there might be repeatables)
			field_wrapper.each(function() {

				var section_repeatable_index = ws_this.get_section_repeatable_index($(this));
				var section_repeatable_suffix = (section_repeatable_index > 0) ? '[' + section_repeatable_index + ']' : '';

				if(submit_array) {

					var field_obj = $('[name="' + ws_this.esc_selector(ws_form_settings.field_prefix + field_id + section_repeatable_suffix) + '[]"]:not([data-init-analytics-field-' + mode + '])', ws_this.form_canvas_obj);

				} else {

					var field_obj = $('[name="' + ws_this.esc_selector(ws_form_settings.field_prefix + field_id + section_repeatable_suffix) + '"]:not([data-init-analytics-field-' + mode + '])', ws_this.form_canvas_obj);
				}

				if(field_obj.length) {

					// Flag so it only initializes once
					field_obj.attr('data-init-analytics-field-' + mode, '');

					// Create event
					field_obj.on(analytics_event, function() {

						if(typeof($(this).attr('data-analytics-event-fired-' + mode)) === 'undefined') {

							var field = ws_this.get_field($(this));

							var analytics_event_action = $.WS_Form.field_type_cache[field.type].events.event_action;

							// Parse values
							var parse_values = {

								'event_action' :	analytics_event_action,
								'event_category' : 	ws_this.js_string_encode(ws_this.language('analytics_category', ws_this.form.label)),
								'event_label' :		ws_this.js_string_encode(field.label),
								'wsf_section_id' :	parseInt(field.section_id, 10),
								'wsf_field_id' :	parseInt(field.id, 10)
							}

							// Fire event
							ws_this.form_analytics_event_fire(

								type,
								type_function,
								parse_values
							);

							$(this).attr('data-analytics-event-fired-' + mode, 'true');
						}
					});
				}
			});
		}

		// Log event
		ws_this.log('log_analytics_event_field', analytics_label + ' (' + analytics_function_label + ')', 'analytics');
	}

	// Form analytics - Fire event
	$.WS_Form.prototype.form_analytics_event_fire = function(type, type_function, parse_values) {

		if(typeof($.WS_Form.analytics[type]) === 'undefined') { return false;; }

		// Run through all fields and set up analytics events for this type and function
		var analytics_function = $.WS_Form.analytics[type].functions[type_function];
		var analytics_label = $.WS_Form.analytics[type].label;
		var analytics_function_label = $.WS_Form.analytics[type].functions[type_function].label;

		// Parse event field args
		var analytics_event_function = analytics_function.analytics_event_function;

		// Call function
		if((type_function == 'js') || (typeof(window[type_function]) === 'function') || (typeof(window[type_function]) === 'object')) {

			try {

				// If Google conversion and using gtag, set data layer
				if(
					(typeof(window.dataLayer) === 'object') &&
					(type == 'google') &&
					(type_function == 'gtag')
				) {

					// Reset data layer (Fixes scroll bug)
					window.dataLayer.push(function() { this.reset(); });

					// Build data layer args
					var eventModel = {};

					for(var parse_value_key in parse_values) {

						if(parse_value_key == 'event_action') { continue; }

						if(!parse_values.hasOwnProperty(parse_value_key)) { continue; }

						var parse_value = parse_values[parse_value_key];

						eventModel[parse_value_key] = parse_value;
					}

					// Add additional helpful parameters
					eventModel.wsf_form_id = parseInt(this.form_id, 10);
					eventModel.wsf_form_instance_id = parseInt(this.form_instance_id, 10);
					eventModel.wsf_form_label = this.js_string_encode(this.form.label);

					// Push parse values as dataLayer elements
					parse_values.params = JSON.stringify(eventModel);
				}

				// Check parse params
				if(typeof(parse_values.event_category) === 'undefined') { parse_values.event_category = ''; }
				if(typeof(parse_values.event_label) === 'undefined') { parse_values.event_label = ''; }
				if(typeof(parse_values.value) === 'undefined') { parse_values.value = 'null'; }

				// Parse function
				var analytics_event_function_parsed = this.mask_parse(analytics_event_function, parse_values);

				// Pass form object
				if(analytics_event_function_parsed.indexOf('"#form_obj"') !== -1) {

					analytics_event_function_parsed = analytics_event_function_parsed.replace('"#form_obj"', "document.getElementById('" + this.form_obj.attr('id') + "')");
				}

				// Run JavaScript for event
				if(analytics_event_function_parsed !== '') {

					$.globalEval('(function($) {' + analytics_event_function_parsed + '})(jQuery);');
				}

				// Log event
				this.log('log_analytics_event_fired', analytics_label + ' (' + analytics_function_label + ') ' + analytics_event_function_parsed, 'analytics');

			} catch(e) {

				// Log error
				this.error('log_analytics_event_failed', analytics_label + ' (' + analytics_function_label + ') ' + analytics_event_function_parsed + ' - Error: ' + e.message, 'analytics');
			}

		} else {

			// Log error
			this.error('log_analytics_event_failed', analytics_label + ' (' + analytics_function_label + ') ' + analytics_event_function_parsed, 'analytics');
		}
	}

	// Form analytics - Push to data layer
	$.WS_Form.prototype.data_layer_push = function(parse_values) {

		if(
			(typeof(window.dataLayer) !== 'object') ||
			(typeof(parse_values) !== 'object')
		) {
			return false;
		}

		// Build log value
		var parse_values_log = JSON.stringify(parse_values);

		try {

			// Reset data layer (Fixes scroll bug)
			window.dataLayer.push(function() { this.reset(); });

			// Push to data layer
			window.dataLayer.push(parse_values);

			// Log event
			this.log('log_analytics_event_fired', 'Google Tag Manager (dataLayer) dataLayer(' + parse_values_log + ')', 'analytics');

		} catch(e) {

			// Log error
			this.error('log_analytics_event_failed', 'Google Tag Manager (dataLayer) dataLayer(' + parse_values_log + ') - Error: ' + e.message, 'analytics');
		}
	}

	// JS Action - Conversion
	$.WS_Form.prototype.action_conversion = function(type, parse_values) {

		switch(type) {

			case 'data_layer' :

				this.data_layer_push(

					parse_values
				);

				break;

			default :

				// Check analytics type exists in config
				if(typeof(type) === 'undefined') { return false; }
				if(typeof($.WS_Form.analytics[type]) === 'undefined') { return false; }
				if(typeof($.WS_Form.analytics[type].functions) === 'undefined') { return false; }

				// Get type_function
				if(typeof(this.analytics_function[type]) !== 'undefined') {

					var type_function = this.analytics_function[type];

				} else {

					// Get first function
					var type_function = Object.keys($.WS_Form.analytics[type].functions)[0];
				}

				// Fire event
				this.form_analytics_event_fire(

					type,
					type_function,
					parse_values
				);

				break;
		}

		// Process next js_action
		this.action_js_process_next();
	}

})(jQuery);
