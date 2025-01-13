(function($) {

	'use strict';

	// Form progress
	$.WS_Form.prototype.form_progress = function() {

		var ws_this = this;

		// Get framework classes
		var progress_class_complete_array = this.get_field_value_fallback('progress', false, 'class_complete', []);
		this.progress_class_complete = progress_class_complete_array.join(' ');

		var progress_class_incomplete_array = this.get_field_value_fallback('progress', false, 'class_incomplete', []);
		this.progress_class_incomplete = progress_class_incomplete_array.join(' ');

		// Required field event handling
		$('[data-required]:not([data-init-required])', this.form_canvas_obj).each(function() {

			// Get progress event
			var field = ws_this.get_field($(this));
			var field_type = field.type;
			var field_config = $.WS_Form.field_type_cache[field_type];
			var progress_event = field_config.events.event;

			$(this).on(progress_event, function() {

				ws_this.form_progress_process();
			});
		});

		// data-progress-include additional fields to include (e.g. checkbox min/max checked)
		$('[data-progress-include]:not([data-init-required])', this.form_canvas_obj).each(function() {

			// Get progress event
			var progress_event = $(this).attr('data-progress-include');

			$(this).on(progress_event, function() {

				ws_this.form_progress_process();
			});
		});

		// Set value for progress bars with no data source
		$('[data-progress-bar]:not([data-source])', this.form_canvas_obj).each(function() {

			// Get value
			var value = $(this).attr('value');
			if(isNaN(value) || !value) { value = 0; } else { value = parseFloat(value); }

			ws_this.form_progress_set_value($(this), value);
		});

		// Initial progress calculation
		this.form_progress_process();

		// Reset post upload progress indicators
		this.form_progress_api_call_reset();
	}

	// Form progress - Process
	$.WS_Form.prototype.form_progress_process = function() {

		var ws_this = this;
		var radio_field_processed = [];		// This ensures correct progress numbers of radios

		// Count completed fields
		var progress_count = 0;
		var progress_valid_count = 0;

		// Get required fields
		$('[data-required]:not([data-required-bypass],[data-required-bypass-section],[data-required-bypass-group])', this.form_canvas_obj).each(function() {

			// Get progress event
			var field = ws_this.get_field($(this));
			var field_type = field.type;

			// Get repeatable suffix
			var section_repeatable_index = ws_this.get_section_repeatable_index($(this));
			var section_repeatable_suffix = (section_repeatable_index > 0) ? '[' + section_repeatable_index + ']' : '';

			// Build field name
			var field_name = ws_form_settings.field_prefix + ws_this.get_field_id($(this)) + section_repeatable_suffix;

			// Determine field validity based on field type
			var validity = false;
			switch(field_type) {

				case 'radio' :
				case 'price_radio' :

					if(typeof(radio_field_processed[field_name]) === 'undefined') { 

						validity = $(this)[0].checkValidity();

					} else {

						return;
					}
					break;

				case 'signature' :

					if(typeof(ws_this.signature_get_response_by_name) === 'function') {

						validity = ws_this.signature_get_response_by_name(field_name);
					}
					break;

				case 'email' :

					var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
					validity = re.test($(this).val());
					break;

				default :

					validity = $(this)[0].checkValidity();
			}

			if(validity) { progress_valid_count++; }
			radio_field_processed[field_name] = true;
			progress_count++;
		});

		// data-progress-include additional fields to include (e.g. checkbox min/max checked)
		$('[data-progress-include]:not([data-min-bypass],[data-min-bypass-section],[data-min-bypass-group],[data-max-bypass],[data-max-bypass-section],[data-max-bypass-group],[data-step-bypass])', this.form_canvas_obj).each(function() {

			var validity = $(this)[0].checkValidity();
			if(validity) { progress_valid_count++; }
			progress_count++;
		});

		// Calculate progress of the form
		var progress_value = (progress_count > 0) ? (progress_valid_count / progress_count) : 0;

		// Set progress fields
		$('[data-progress-bar][data-source="form_progress"], .wsf-form-conversational-nav-progress', this.form_canvas_obj).each(function() {

			ws_this.form_progress_set_value($(this), progress_value);
		});
	}

	// Form progress - Set value
	$.WS_Form.prototype.form_progress_set_value = function(obj, value) {

		var progress_value_obj = $('[data-progress-bar-value]', obj);
		if(!progress_value_obj.length) { progress_value_obj = obj; }

		// Work out progress max
		if(obj.is('progress')) {

			// Work out range
			if(obj.attr('max')) {

				var progress_max = obj.attr('max');
				if(isNaN(progress_max) || !progress_max) { progress_max = 1; } else { progress_max = parseFloat(progress_max); }

			} else {

				var progress_max = 1;
			}

		} else {

			// Progress max
			var progress_max = 1;
		}

		// Check progress max
		if(progress_max <= 0) { progress_max = 1; }

		// Bounds
		if(value < 0) { value = 0; }
		if(value > progress_max) { value = progress_max; }

		// Work out percentage
		var progress_percentage = Math.round((value / progress_max) * 100);

		// Apply value
		if(obj.is('progress')) {

			// Set value and Trigger
			var field_trigger = (progress_value_obj.val() != value);
			progress_value_obj.val(value);
			if(field_trigger) { progress_value_obj.trigger('change'); }

			// Set ARIA attributes
			obj.attr('aria-valuemin', 0).attr('aria-valuemax', progress_max).attr('aria-valuenow', value);

		} else {

			// Set value and Trigger
			var field_trigger = (progress_value_obj.attr('data-value') != value);
			progress_value_obj.attr('data-value', value).css('width', progress_percentage + '%');
			if(field_trigger) { progress_value_obj.trigger('change'); }

			// Set ARIA attributes
			if(typeof(obj.attr('aria-valuenow')) !== 'undefined') { obj.attr('aria-valuenow', value); }
			if(typeof(progress_value_obj.attr('aria-valuenow')) !== 'undefined') { progress_value_obj.attr('aria-valuenow', value); }
		}

		// Assign classes
		if(progress_percentage == 100) {

			obj.addClass(this.progress_class_complete);
			obj.removeClass(this.progress_class_incomplete);

		} else {

			obj.removeClass(this.progress_class_complete);
			obj.addClass(this.progress_class_incomplete);
		}

		// Help text
		if(this.conversational && obj.hasClass('wsf-form-conversational-nav-progress')) {

			var help_obj = $('.wsf-form-conversational-nav-progress-help', this.form_canvas_obj);
			var help = this.get_object_meta_value(this.form, 'conversational_nav_progress_help', '#progress_percent', false, false);

		} else {

			var field = this.get_field(obj);;
			var help = this.get_object_meta_value(field, 'help', '#progress_percent', false, false);
			var help_obj = this.get_help_obj(obj);
		}

		// If #progress_ not present, don't bother processing
		if(
			help_obj.length &&
			(help.indexOf('#progress') !== -1)
		) {

			// Parse the help text
			var help_values = {

				'progress_remaining_percent': (100 - progress_percentage) + '%',
				'progress_remaining': (100 - progress_percentage),
				'progress_percent': progress_percentage + '%',
				'progress': progress_percentage
			};
			var help_parsed = this.mask_parse(help, help_values);
			help_parsed = this.parse_variables_process(help_parsed).output;

			// Update help HTML
			help_obj.html(help_parsed);
		}
	}

	// Form Progress - Tabs
	$.WS_Form.prototype.form_progress_tabs = function() {

		var ws_this = this;

		var tabs = $('.wsf-group-tabs', this.form_canvas_obj).children(':not([data-wsf-group-hidden])');

		var group_count = tabs.length;

		// Get selector href
		var class_active = (typeof(this.framework.tabs.public.class_active) !== 'undefined') ? this.framework.tabs.public.class_active : 'active';

		// Work out which tab number we are on
		var group_number = 1;
		tabs.each(function() {

			if($(this).hasClass(class_active)) { return false; }

			group_number++;
		});

		// Get progress value
		var progress_value = (group_number / group_count);

		// Update progress fields
		$('[data-source="tab_progress"]', this.form_canvas_obj).each(function() {

			// Set progress fields
			ws_this.form_progress_set_value($(this), progress_value);
		});
	}

	// Form Progress - Reset progress fields configure to use post progress
	$.WS_Form.prototype.form_progress_api_call_reset = function() {

		var ws_this = this;

		$('[data-progress-bar][data-source="post_progress"]', this.form_canvas_obj).each(function() {

			ws_this.form_progress_set_value($(this), 0);
		});
	}

	// Form Progress - API Call
	$.WS_Form.prototype.form_progress_api_call = function(progress_objs, e) {

		if(!e.lengthComputable) { return; }

		var ws_this = this;

		progress_objs.each(function() {

			// Get progress value
			var progress_value = (e.loaded / e.total);

			// Set progress fields
			ws_this.form_progress_set_value($(this), progress_value);
		});
	}

})(jQuery);
