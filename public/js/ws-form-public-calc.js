(function($) {

	'use strict';

	// Removes any calc elements that relate to objects that don't exist anymore
	$.WS_Form.prototype.form_calc_clean = function() {

		for(var calc_index in this.calc) {

			if(!this.calc.hasOwnProperty(calc_index)) { continue; }

			var calc = this.calc[calc_index];

			var field_id = this.get_part_id(calc.field.id, calc.section_repeatable_index, ((calc.field.type == 'hidden') ? 'field' : 'field-wrapper'));

			var field_obj = $('#' + field_id, this.form_canvas_obj);

			if(!field_obj.length) {

				this.calc.splice(calc_index, 1);
				this.form_calc_clean();
				return;
			}
		}
	}

	$.WS_Form.prototype.form_calc = function(field_obj_id_changed, section_repeatable_added_section_id) {

		if(typeof(field_obj_id_changed) === 'undefined') { field_obj_id_changed = false; }
		if(typeof(section_repeatable_added_section_id) === 'undefined') { section_repeatable_added_section_id = false; }

		// this.calc could change as a result of a repeatable section so we copy it
		var calc_current = $.extend(true, [], this.calc);

		for(var calc_index in calc_current) {

			if(!calc_current.hasOwnProperty(calc_index)) { continue; }

			var calc = calc_current[calc_index];

			// EVENT: Section added
			if(section_repeatable_added_section_id !== false) {

				// Always run #section_row_count variable
				if(calc.value.indexOf('#section_row_count') !== -1) {

					this.form_calc_do(calc, '#section_row_count');
					continue;
				}

				// Always run #section_row_index variable
				if(calc.value.indexOf('#section_row_index') !== -1) {

					this.form_calc_do(calc, '#section_row_index');
					continue;
				}

				// Check if any of the fields exist in the section added
				var field_section_id = calc.field.section_id;
				if(parseInt(field_section_id, 10) === parseInt(section_repeatable_added_section_id, 10)) {

					this.form_calc_do(calc, calc.field.id);
					continue;
				}

				// Check fields ID touched and if they belong in this section, and the field is outside this section, run the calculation
				if(calc.section_repeatable_index === false) {

					for(var field_ids_touched_index in calc.field_ids_touched) {

						if(!calc.field_ids_touched.hasOwnProperty(field_ids_touched_index)) { continue; }

						var field_id_touched = calc.field_ids_touched[field_ids_touched_index];
						var field_config = this.field_data_cache[field_id_touched];

						var section_repeatable_section_id = (typeof(field_config.section_repeatable_section_id) !== 'undefined') ? field_config.section_repeatable_section_id : false;

						if(parseInt(section_repeatable_section_id, 10) === parseInt(section_repeatable_added_section_id, 10)) {

							this.form_calc_do(calc, field_id_touched);
						}
					}
				}

			// EVENT: Field changed
			} else if(field_obj_id_changed !== false) {

				// Get field ID that initiated the change
				var field_obj = $('#' + field_obj_id_changed, this.form_canvas_obj);

				// Don't calculate on self if value
				var field_id_hidden = field_obj.attr('data-id-hidden');
				var field_wrapper_obj = field_id_hidden ? field_obj : field_obj.closest('[data-id]');
				var field_id = field_id_hidden ? parseInt(field_id_hidden, 10) : parseInt(field_wrapper_obj.attr('data-id'), 10);
				if(
					(field_id === calc.field.id) &&
					(calc.field_part == 'value')
				) {
					continue;
				}

				// Get fields that were touched by this calculation
				var field_ids_touched = calc.field_ids_touched;
				if(field_ids_touched.length > 0) {

					if(field_ids_touched.indexOf(field_id) !== -1) {

						// If this calculation is impacted by the field that was changed, run it
						this.form_calc_do(calc, field_id);
					}
				}

			} else {

				// EVENT: Full calculation
				this.form_calc_do(calc, ($.WS_Form.debug_rendered) ? this.language('log_' + calc.type + '_init') : '');
			}
		}
	}

	$.WS_Form.prototype.form_calc_do = function(calc, debug_log_triggered_string) {

		// Log
		this.log('log_' + calc.type + '_fired', calc.field.label + ' - ' + this.esc_html(calc.value) + (debug_log_triggered_string ? ' (' + this.language('log_' + calc.type + '_fired_triggered', debug_log_triggered_string) + ')' : ''), calc.type);

		// Check field configuration calc_in
		if(typeof($.WS_Form.field_type_cache[calc.field.type]) === 'undefined') { return false; }
		var field_config = $.WS_Form.field_type_cache[calc.field.type];
		var allow_in = typeof(field_config[calc.type + '_in']) ? field_config[calc.type + '_in'] : false;
		if(
			(calc.field_part != 'field_label') &&
			(calc.field_part != 'field_help') &&
			!allow_in
		) {

			this.error('error_parse_variable_syntax_error_' + calc.type + '_in', 'ID: ' + calc.field.id, 'parse-variables');
			return false;
		}

		// Process as calculated value
		var value = this.form_calc_get_value(calc);

		// Process calc according to field part
		switch(calc.field_part) {

			case 'field_text_editor' :
			case 'field_html' :

				// Get field object
				var field_wrapper_id = this.get_part_id(calc.field.id, calc.section_repeatable_index, 'field-wrapper');

				// Get target object
				var target_obj = $('[' + ((calc.field_part == 'field_text_editor') ? 'data-text-editor' : 'data-html') + ']', $('#' + field_wrapper_id, this.form_canvas_obj));

				// Set HTML
				target_obj.html(value);

				break;

			case 'field_label' :

				// Get field object
				var label_id = this.get_part_id(calc.field.id, calc.section_repeatable_index, 'label');

				var target_obj = $('#' + label_id, this.form_canvas_obj);

				// Get any child elements (e.g. required)
				var target_obj_children = target_obj.children('.wsf-required-wrapper');

				// Set HTML
				target_obj.html(value);

				// Put child elements back (e.g. required)
				if(target_obj_children) {

					target_obj.append(target_obj_children);
				}

				break;

			case 'field_help' :

				// Get field object
				var help_id = this.get_part_id(calc.field.id, calc.section_repeatable_index, 'help');
				var target_obj = $('#' + help_id, this.form_canvas_obj);

				// Set HTML
				target_obj.html(value);

				break;

			case 'field_placeholder' :
			case 'field_min' :
			case 'field_max' :
			case 'field_min-date' :
			case 'field_max-date' :
			case 'field_min-time' :
			case 'field_max-time' :

				// Get field object
				var field_id = this.get_part_id(calc.field.id, calc.section_repeatable_index);
				var target_obj = $('#' + field_id, this.form_canvas_obj);

				// Get attribute name
				var attribute_name = calc.field_part.substring(6);

				// Remember old value
				var value_old = target_obj.attr(attribute_name);

				// Set HTML
				target_obj.attr(attribute_name, value);

				// Determine if date field should be re-rendered
				if(
					(typeof(this.form_date_datetimepicker_enabled) === 'function') &&
					this.form_date_datetimepicker_enabled() &&
					(calc.field.type === 'datetime')
				) {

					if(typeof(target_obj.datetimepicker) === 'function') {

						target_obj.datetimepicker('destroy');
					}

					if(typeof(this.form_date_process) === 'function') {

						this.form_date_process(target_obj);
					}
				}

				// Trigger change if value changed
				if(value_old !== target_obj.attr(attribute_name)) { target_obj.trigger('change'); }

				break;

			case 'field_ecommerce_price_min' :
			case 'field_ecommerce_price_max' :

				// Get field object
				var field_id = this.get_part_id(calc.field.id, calc.section_repeatable_index);

				// Get field object
				var target_obj = $('#' + field_id, this.form_canvas_obj);

				// Get attribute name
				var attribute_name = calc.field_part.substring(6);

				// Remember old value
				var value_old = target_obj.attr(attribute_name);

				// Set HTML
				target_obj.attr('data-ecommerce-' + ((calc.field_part === 'field_ecommerce_price_min') ? 'min' : 'max'), value);

				// Re-process currency input mask
				if(typeof(this.form_ecommerce_input_mask_currency) === 'function') { this.form_ecommerce_input_mask_currency(target_obj); }

				// Trigger change if value changed
				if(value_old !== target_obj.attr(attribute_name)) { target_obj.trigger('change'); }

				break;

			case 'field_value' :

				// Build field ID
				var field_id = this.get_part_id(calc.field.id, calc.section_repeatable_index);

				// Get field object
				var target_obj = $('#' + field_id, this.form_canvas_obj);		

				// Set value
				switch(calc.field.type) {

					case 'progress' :

						// form_progress_set_value handles the change trigger
						this.form_progress_set_value(target_obj, value);

						break;

					case 'price' :
					case 'price_range' :
					case 'price_subtotal' :
					case 'cart_price' :

						// Get decimal separator
						var decimals = $.WS_Form.settings_plugin.price_decimals;

						// Old value
						var value_compare_old = this.get_number(target_obj.val(), 0, true, decimals);

						// New value
						var value_compare_new = this.get_number(value, 0, true, decimals);

						// Determine if trigger required
						var field_trigger = (value_compare_old !== value_compare_new);

						switch(calc.field.type) {

							case 'price_range' :

								// Change value (float)
								target_obj.val(value_compare_new);

								break;

							default :

								// Change value
								target_obj.val(value);
						}

						// Trigger
						if(field_trigger) { target_obj.trigger('change'); }

						break;

					case 'color' :

						var field_trigger = (target_obj.val() !== value);

						target_obj.val(value);

						if(field_trigger) {

							target_obj.trigger('change');

							// Coloris
							if(typeof(Coloris) !== 'undefined') {

								target_obj[0].dispatchEvent(new Event('input', { bubbles: true }));
							}
						}

						break;

					default :

						// Determine if field trigger should occur
						switch(calc.field.type) {

							case 'number' :
							case 'quantity' :
							case 'range' :
							case 'wc_quantity' :

								var field_trigger = (this.get_number(target_obj.val(), 0, false) !== value);

								break;

							default :

								var field_trigger = (target_obj.val() !== value);

								break;
						}

						// HTML decode value
						// The output of parse_variables_process is HTML encoded
						// We can safely pass HTML entities to val() without risk of XSS

						// Strip HTML
						value = this.esc_html_undo(value);

						// Set value
						target_obj.val(value);

						// If textarea then set value (provides support for CodeMirror and TinyMCE)
						if(
							(calc.field.type == 'textarea') &&
							(typeof(this.textarea_set_value) === 'function')
						) {

							this.textarea_set_value(target_obj, value);
						}

						if(field_trigger) {

							var value_new = target_obj.val();

							// Firefox bug fix (their number field is limited to 14 decimal places if certain languages are set)
							if(
								(calc.field.type == 'number') &&
								!isNaN(value) &&
								!isNaN(value_new)
							) {
								value = parseFloat(value).toFixed(14);
								value_new = parseFloat(value_new).toFixed(14);
							}

							// If the set value equals the get value, and field_trigger, trigger change event (Ensures input can accept that value)
							if((value == value_new) && field_trigger) {

								target_obj.trigger('change');
							}
						}
				}
		}
	}

	$.WS_Form.prototype.form_calc_get_value = function(calc) {

		// Parse default value
		var value = this.parse_variables_process(calc.value, calc.section_repeatable_index, calc.type, calc.field, calc.field_part, false).output;

		switch(calc.field_part) {

			case 'field_min' :
			case 'field_max' :

				switch(calc.field.type) {

					// Range slider bug fix
					case 'range' :
					case 'price_range' :

						var value_number = this.get_number(value, 0, false);

						if(value_number % 1 != 0) {

							value = this.get_number_rounded(value_number, 8);
						}

						break;
				}

				break;

			case 'field_value' :

				// Build field ID
				var field_id = this.get_part_id(calc.field.id, calc.section_repeatable_index);

				// Get field object
				var target_obj = $('#' + field_id, this.form_canvas_obj);

				// Get value for min, max and step values
				var min = max = step = false;

				switch(calc.field.type) {

					case 'progress' :

						value = this.get_number(value, 0, false);

						var max = (typeof(target_obj.attr('max')) !== 'undefined') ? target_obj.attr('max') : 1;
						var step = (typeof(target_obj.attr('step')) !== 'undefined') ? target_obj.attr('step') : step;

						break;

					case 'range' :
					case 'price_range' :

						// Range slider bug fix
						value = this.get_number(value, 0, false);

						if(
							(value % 1 != 0) &&
							((calc.field.type === 'range') || (calc.field.type === 'price_range'))
						) {
							value = this.get_number_rounded(value, 8);
						}

						var min = (typeof(target_obj.attr('min')) !== 'undefined') ? target_obj.attr('min') : 0;
						var max = (typeof(target_obj.attr('max')) !== 'undefined') ? target_obj.attr('max') : 100;
						var step = (typeof(target_obj.attr('step')) !== 'undefined') ? target_obj.attr('step') : 1;

						break;

					case 'price' :
					case 'price_subtotal' :
					case 'cart_price' :
					case 'number' :
					case 'quantity' :
					case 'wc_quantity' :

						value = this.get_number(value, 0, false);

						var min = (typeof(target_obj.attr('min')) !== 'undefined') ? target_obj.attr('min') : min;
						var max = (typeof(target_obj.attr('max')) !== 'undefined') ? target_obj.attr('max') : max;
						var step = (typeof(target_obj.attr('step')) !== 'undefined') ? target_obj.attr('step') : step;

						break;

					case 'datetime' :

						// Date format
						if(calc.type === 'calc') {

							if(this.is_integer(value)) {

								// Date format
								var format_date = target_obj.attr('data-date-format') ? target_obj.attr('data-date-format') : ws_form_settings.date_format;

								// Time format
								var format_time = target_obj.attr('data-time-format') ? target_obj.attr('data-time-format') : ws_form_settings.time_format;

								// Get date time format string
								switch(target_obj.attr('data-date-type')) {

									case 'date' :

										var date_format = format_date;
										break;

									case 'time' :

										var date_format = format_time;
										break;

									case 'month' :

										var date_format = 'F Y';
										break;

									case 'week' :

										var language_week = '';
										ws_this.language('week').split('').map(letter => { language_week += '\\' + letter; })
										var date_format = language_week + ' W, Y';
										break;

									default :

										var date_format = format_date + ' ' + format_time;
								}

								value = this.get_number(value, 0, true);

								value = ((typeof(this.date_format) === 'function') ? this.date_format(new Date(value * 1000), date_format) : '');
							}
						}

						break;
				}

				// Process values for min, max and step values
				if((min !== false) && (value < min)) { value = min; }
				if((max !== false) && (value > max)) { value = max; }
				if(step !== false) { value = this.get_number_to_step(value, step); }				

				// Set value to price
				switch(calc.field.type) {

					case 'price' :
					case 'price_range' :
					case 'price_subtotal' :
					case 'cart_price' :

						// We set the value as follows to ensure the input mask currency is set correctly
						// - No currency symbol
						// - Decimal and thousand separator characters are correct
						value = this.get_price(value, this.get_currency(), false);

						break;
				}

				break;
		}

		return value;
	}

	// Output log entries for calcs
	$.WS_Form.prototype.form_calc_log = function() {

		if(!ws_form_settings.debug) { return; }

		for(var calc_index in this.calc) {

			if(!this.calc.hasOwnProperty(calc_index)) { continue; }

			var calc = this.calc[calc_index];

			this.log('log_' + calc.type + '_registered', calc.field.label + ' - ' + this.esc_html(calc.value) + (calc.field_ids_touched.length ? ' (' + this.language('log_' + calc.type + '_registered_triggered', calc.field_ids_touched.join(', ')) + ')' + (calc.field.id ? (' [' + calc.field.id + ']') : '') : ''), calc.type);
		}
	}

})(jQuery);
