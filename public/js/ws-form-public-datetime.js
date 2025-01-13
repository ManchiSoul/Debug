(function($) {

	'use strict';

	// Form - Date
	$.WS_Form.prototype.form_date = function() {

		// Get date time pickers
		var obj = $('[data-type="datetime"] input', this.form_canvas_obj);

		// Do not load date picker if no datetime fields found
		if(!obj.length) { return false; }

		// Do not use date/time picker
		if($.WS_Form.settings_plugin.ui_datepicker == 'off') { return false; }

		if(
			this.form_date_datetimepicker_enabled()
		) {		

			// Process form date fields
			this.form_date_process();

		} else {

			if(this.native_date) {

				var ws_this = this;

				// Ensure date values are in correct format
				obj.each(function () {

					var value = $(this).attr('value');
					if(value == '') { return false; }

					var type = $(this).attr('data-date-type');

					try {

						switch(type) {

							case 'datetime-local' :

								var date_time = ws_this.get_new_date(value);
								var date_time_blog = new Date(date_time.valueOf() - (date_time.getTimezoneOffset() * 60000)).toISOString();
								value = date_time_blog.substring(0,date_time_blog.length-1);
								break;
						}

					} catch(e) {

						ws_this.error('error_datetime_default_value', e);
					}

					$(this).val(value);
				});
			}
		}

		// Process date validity
		this.form_date_validity();
	}

	// Form - Date - datetimepicker enabled
	$.WS_Form.prototype.form_date_datetimepicker_enabled = function() {

		return (

			// Checks for our datetimepicker (Avoids clash with other datetimepickers)
			(typeof(jQuery().datetimepicker) === 'function') &&
			(typeof(jQuery.datetimepicker) === 'object') &&							
			(typeof(jQuery.datetimepicker.setLocale) === 'function') &&
			(typeof(jQuery.datetimepicker.setDateFormatter) === 'function') &&

			(

				// Use jQuery date/time picker
				($.WS_Form.settings_plugin.ui_datepicker == 'on') ||

				// If browser does not support native date/time picked, use datetimepicker
				(
					($.WS_Form.settings_plugin.ui_datepicker == 'native') &&
					!this.native_date
				)
			)
		);
	}

	// Form - Date - Process
	$.WS_Form.prototype.form_date_process = function(obj) {

		var ws_this = this;

		if(typeof(obj) === 'undefined') {

			obj = $('[data-type="datetime"] input', this.form_canvas_obj);
		}

		// Language
		var locale = ws_form_settings.locale;
		var args_lang = locale.substring(0, 2);
		jQuery.datetimepicker.setLocale(args_lang);

		// Process each date field
		obj.each(function() {

			// Get field
			var field = ws_this.get_field($(this));

			// Check for read only
			if($(this).is('[readonly]')) {

				$(this).datetimepicker('destroy');
				return;
			}

			// Get ID
			var id = $(this).attr('id') + '-datetime-picker';

			// Input type date/time
			var input_type_datetime = $(this).attr('data-date-type');

			// Date format
			var format_date = $(this).attr('data-date-format') ? $(this).attr('data-date-format') : ws_form_settings.date_format;

			// Time format
			var format_time = $(this).attr('data-time-format') ? $(this).attr('data-time-format') : ws_form_settings.time_format;

			// Time step
			var time_step = $(this).attr('data-time-step') ? parseInt($(this).attr('data-time-step'), 10) : 15;
			if(time_step <= 0) { time_step = 15 ;}

			// Base args
			var args = {

				allowBlank: true,
				formatDate: 'Y-m-d',
				formatTime: format_time,
				id: id,
				scrollInput: false,
				scrollMonth : false,
				scrollTime : false,
				timepickerScrollbar: false,
				validateOnBlur: false,
				rtl: ws_form_settings.rtl
			};

			// Get custom class
			args.className = 'wsf-datetime-picker';
			var class_datetime_picker = ws_this.get_object_meta_value(field, 'class_datetime_picker');
			if(class_datetime_picker) { args.className += ' ' + class_datetime_picker.trim(); }

			// Set parent ID as form
			var parent_form = ws_this.get_object_meta_value(field, 'parent_form');
			if(parent_form) { args.parentID = '#' + ws_this.form_obj.attr('id'); }

			// Inline
			if(typeof($(this).attr('data-inline')) !== 'undefined') { args.inline = true; }

			// Day of week start
			var dow_start = $(this).attr('data-dow-start') ? parseInt($(this).attr('data-dow-start'), 10) : 1;
			if(dow_start < 0) { dow_start = 0; }
			if(dow_start > 6) { dow_start = 6; }
			args.dayOfWeekStart = dow_start;

			// Min date
			var min_date = $(this).attr('min-date');
			if(min_date) {

				if(
					(min_date.charAt(0) != '+') &&
					(min_date.charAt(0) != '-')
				) {

					var gmt_offset = parseInt(ws_form_settings.gmt_offset, 10);
					var min_date = ws_this.get_new_date(min_date);
					min_date.setHours(min_date.getHours() + (gmt_offset * -1));
					args.minDate = ws_this.date_format(min_date, 'Y-m-d');

				} else {

					// Allow + / - dates
					args.minDate = min_date;
				}
			}

			// Max date
			var max_date = $(this).attr('max-date');
			if(max_date) {

				if(
					(max_date.charAt(0) != '+') &&
					(max_date.charAt(0) != '-')
				) {

					var gmt_offset = parseInt(ws_form_settings.gmt_offset, 10);
					var max_date = ws_this.get_new_date(max_date);
					max_date.setHours(max_date.getHours() + (gmt_offset * -1));
					args.maxDate = ws_this.date_format(max_date, 'Y-m-d');

				} else {

					// Allow + / - dates
					args.maxDate = max_date;
				}
			}

			// Min time
			var min_time = $(this).attr('min-time');
			if(min_time) { args.minTime = min_time; }

			// Max time
			var max_time = $(this).attr('max-time');
			if(max_time) { args.maxTime = max_time; }

			// Year start
			var year_start = $(this).attr('data-year-start');
			if(year_start) { args.yearStart = year_start; }

			// Year end
			var year_end = $(this).attr('data-year-end');
			if(year_end) { args.yearEnd = year_end; }

			// Date/time on/off
			switch(input_type_datetime) {

				case 'date' :

					args.format = format_date;
					args.timepicker = false;
					args.closeOnDateSelect = true;
					break;

				case 'time' :

					args.format = format_time;
					args.datepicker = false;
					args.step = time_step;
					args.closeOnDateSelect = true;
					break;

				case 'month' :

					args.format = 'F Y';
					args.timepicker = false;
					args.closeOnDateSelect = true;
					break;

				case 'week' :

					var language_week = '';
					ws_this.language('week').split('').map(letter => { language_week += '\\' + letter; })
					args.format = language_week + ' W, Y';
					args.timepicker = false;
					args.weeks = true;
					args.closeOnDateSelect = true;
					break;

				default :

					args.format = format_date + ' ' + format_time;
					args.step = time_step;
			}

			if(ws_this.conversational) {

				args.onShow = function(e, input) {

					var datetimepicker = $('.xdsoft_datetimepicker').first();
					input.after(datetimepicker);
				};
			}

			// Disabled week days
			var disabled_week_days_array = [];
			var disabled_week_days = ws_this.get_object_meta_value(field, 'disabled_week_days');

			if(
				(typeof(disabled_week_days) === 'object') &&
				disabled_week_days.length
			) {

				for(var disabled_week_days_index in disabled_week_days) {

					if(!disabled_week_days.hasOwnProperty(disabled_week_days_index)) { continue; }

					var disabled_week_day = disabled_week_days[disabled_week_days_index];

					if(typeof(disabled_week_day.disabled_week_days_day) === 'undefined') { continue; }

					disabled_week_day = parseInt(disabled_week_day.disabled_week_days_day, 10);

					if((disabled_week_day >= 0) && (disabled_week_day <= 6)) {

						disabled_week_days_array.push(disabled_week_day);
					}
				}
			}

			if(disabled_week_days_array.length > 0) {

				args.disabledWeekDays = disabled_week_days_array;
			}

			// Disabled dates
			var disabled_dates_array = [];
			var disabled_dates = ws_this.get_object_meta_value(field, 'disabled_dates');

			if(
				(typeof(disabled_dates) === 'object') &&
				disabled_dates.length
			) {

				for(var disabled_dates_index in disabled_dates) {

					if(!disabled_dates.hasOwnProperty(disabled_dates_index)) { continue; }

					var disabled_date = disabled_dates[disabled_dates_index];

					if(typeof(disabled_date.disabled_dates_date) === 'undefined') { continue; }
					if(disabled_date.disabled_dates_date == '') { continue; }

					disabled_dates_array.push(disabled_date.disabled_dates_date);
				}
			}

			if(disabled_dates_array.length > 0) {

				args.disabledDates = disabled_dates_array;
			}

			// Enabled dates
			var enabled_dates_array = [];
			var enabled_dates = ws_this.get_object_meta_value(field, 'enabled_dates');

			if(
				(typeof(enabled_dates) === 'object') &&
				enabled_dates.length
			) {

				for(var enabled_dates_index in enabled_dates) {

					if(!enabled_dates.hasOwnProperty(enabled_dates_index)) { continue; }

					var enabled_date = enabled_dates[enabled_dates_index];

					if(typeof(enabled_date.enabled_dates_date) === 'undefined') { continue; }
					if(enabled_date.enabled_dates_date == '') { continue; }

					enabled_dates_array.push(enabled_date.enabled_dates_date);
				}
			}

			if(enabled_dates_array.length > 0) {

				args.allowDates = enabled_dates_array;
			}

			// Enabled times
			var enabled_times_array = [];
			var enabled_times = ws_this.get_object_meta_value(field, 'enabled_times');

			if(
				(typeof(enabled_times) === 'object') &&
				enabled_times.length
			) {

				for(var enabled_times_index in enabled_times) {

					if(!enabled_times.hasOwnProperty(enabled_times_index)) { continue; }

					var enabled_time = enabled_times[enabled_times_index];

					if(typeof(enabled_time.enabled_times_time) === 'undefined') { continue; }
					if(enabled_time.enabled_times_time == '') { continue; }

					enabled_times_array.push(enabled_time.enabled_times_time);
				}
			}

			if(enabled_times_array.length > 0) {

				args.allowTimes = enabled_times_array;
			}

			// Initialize date / time picker
			$(this).datetimepicker(args);

			// Set style
			$('#' + id).attr('data-wsf-style-id', ws_this.form.meta.style_id);
		});
	}

	// Form - Date - Validity
	$.WS_Form.prototype.form_date_validity = function() {

		if(typeof(jQuery().datetimepicker) === 'undefined') { return; }

		var ws_this = this;

		// Get all jQuery date fields
		$('input[type="text"][data-date-type]:not([data-hidden],[data-hidden-section],[data-hidden-group]), input[type="text"][data-date-type]:not([data-hidden],[data-hidden-section],[data-hidden-group])', this.form_canvas_obj).each(function() {

			ws_this.field_date_validity_process($(this));

			// Field validation on change
			$(this).on('change', function() {

				// Process validity
				ws_this.field_date_validity_process($(this));
			});
		});
	}

	// Form - Date - Validity - Process
	$.WS_Form.prototype.field_date_validity_process = function(obj) {

		// Get field ID
		var field_id = this.get_field_id(obj);

		// Get field
		var field = this.get_field(obj);

		// Reset invalid feedback
		this.set_invalid_feedback(obj, '');

		// Get input date
		var input_date = obj.val();

		// Ignore if empty
		if(input_date === '') { return; }

		// Translate
		if(typeof(this.field_date_translate) === 'function') {

			input_date = this.field_date_translate(input_date);
		}

		// Get input type date/time
		var input_type_datetime = obj.attr('data-date-type');

		// Ignore date/time types we don't do min/max checks on
		switch(input_type_datetime) {

			case 'week' :
			case 'month' :
			case 'time' :

				return;
		}

		// Get date format
		var format_date = obj.attr('data-date-format') ? obj.attr('data-date-format') : ws_form_settings.date_format;

		// Convert input value into date
		var input_date = this.get_date(input_date, input_type_datetime, format_date);

		// Check its a valid date
		if(!this.date_valid(input_date)) {

			// Set invalid feedback
			this.set_invalid_feedback(obj, false);

			return;
		}

		// Get min
		var min_date = this.parse_date_min_max(obj, 'min-date');

		// Get max
		var max_date = this.parse_date_min_max(obj, 'max-date');

		// Check against min
		if(
			(min_date && (input_date < min_date)) ||
			(max_date && (input_date > max_date))
		) {

			// Set invalid feedback
			this.set_invalid_feedback(obj, false);
			return;
		}

		// Disabled week days
		var disabled_week_days = this.get_object_meta_value(field, 'disabled_week_days');

		if(
			(typeof(disabled_week_days) === 'object') &&
			disabled_week_days.length
		) {

			for(var disabled_week_days_index in disabled_week_days) {

				if(!disabled_week_days.hasOwnProperty(disabled_week_days_index)) { continue; }

				var disabled_week_day = disabled_week_days[disabled_week_days_index];

				if(typeof(disabled_week_day.disabled_week_days_day) === 'undefined') { continue; }

				disabled_week_day = parseInt(disabled_week_day.disabled_week_days_day, 10);

				if(
					(disabled_week_day >= 0) &&
					(disabled_week_day <= 6) &&
					(input_date.getDay() === disabled_week_day)
				) {

					// Set invalid feedback
					this.set_invalid_feedback(obj, false);
					return;
				}
			}
		}

		// Disabled dates
		var disabled_dates = this.get_object_meta_value(field, 'disabled_dates');

		if(
			(typeof(disabled_dates) === 'object') &&
			disabled_dates.length
		) {

			for(var disabled_dates_index in disabled_dates) {

				if(!disabled_dates.hasOwnProperty(disabled_dates_index)) { continue; }

				var disabled_date = disabled_dates[disabled_dates_index];

				if(typeof(disabled_date.disabled_dates_date) === 'undefined') { continue; }
				if(disabled_date.disabled_dates_date == '') { continue; }

				var disabled_date = this.get_date(disabled_date.disabled_dates_date, 'date', 'Y-m-d');

				if(
					(disabled_date.getFullYear() === input_date.getFullYear()) &&
					(disabled_date.getMonth() === input_date.getMonth()) &&
					(disabled_date.getDate() === input_date.getDate())
				) {

					// Set invalid feedback
					this.set_invalid_feedback(obj, false);
					return;
				}
			}
		}

		// Enabled dates
		var enabled_dates = this.get_object_meta_value(field, 'enabled_dates');

		if(
			(typeof(enabled_dates) === 'object') &&
			enabled_dates.length
		) {

			var enabled_dates_valid = false;
			var enabled_dates_valid_check = false;

			for(var enabled_dates_index in enabled_dates) {

				if(!enabled_dates.hasOwnProperty(enabled_dates_index)) { continue; }

				var enabled_date = enabled_dates[enabled_dates_index];

				if(typeof(enabled_date.enabled_dates_date) === 'undefined') { continue; }
				if(enabled_date.enabled_dates_date == '') { continue; }

				var enabled_date = this.get_date(enabled_date.enabled_dates_date, 'date', 'Y-m-d');

				if(
					(enabled_date.getFullYear() === input_date.getFullYear()) &&
					(enabled_date.getMonth() === input_date.getMonth()) &&
					(enabled_date.getDate() === input_date.getDate())
				) {

					enabled_dates_valid = true;
				}

				enabled_dates_valid_check = true;
			}

			if(enabled_dates_valid_check && !enabled_dates_valid) {

				// Set invalid feedback
				this.set_invalid_feedback(obj, false);
				return;
			}
		}
	}

	// Parse min / max date (e.g. -1970/01/05)
	$.WS_Form.prototype.parse_date_min_max = function(obj, attr) {

		// Get input date
		var input_date = obj.attr(attr);

		// Check for blank input date
		if(!input_date) { return ''; }

		// Translate date
		if(typeof(this.field_date_translate) === 'function') {

			input_date = this.field_date_translate(input_date);
		}

		// GMT offset for time
		var gmt_offset = 0;

		// Get input type date/time
		var input_type_datetime = obj.attr('data-date-type');

		// Check for +1970-01-01 offset format
		switch(input_type_datetime) {

			case 'date' :
			case 'datetime-local' :
			case 'week' :
			case 'month' :

				// Check first character
				var input_date_first_char = input_date.substring(0, 1);

				if(
					(input_date_first_char === '-') ||
					(input_date_first_char === '+')
				) {

					// Get rest of date string
					var input_date_offset_string = input_date.substring(1);

					// Convert to JavaScript date
					var input_date_offset_date = this.get_new_date(input_date_offset_string);
					if(!this.date_valid(input_date_offset_date)) { return ''; }

					// Get EPOCH days
					var input_date_offset_days = Math.floor(input_date_offset_date / 8.64e7);

					// Multiple by -1 if negative
					var input_date_offset_days = input_date_offset_days * ((input_date_first_char === '-') ? -1 : 1);

					// Build output date
					var output_date = new Date();

					// Set output date to midnight
					output_date.setHours(0,0,0,0);

					// Add offset days
					output_date.setDate(output_date.getDate() + input_date_offset_days);

					// Return date
					return output_date;
				}
		}

		// Format input date
		switch(input_type_datetime) {

			case 'date' :

				input_date += ' 00:00:00';
				break;

			case 'datetime-local' :

				gmt_offset = parseInt(ws_form_settings.gmt_offset, 10);
				break;

			case 'time' :

				input_date = '01/01/1970 ' + input_date;
				break;
		}

		// Create date
		var output_date = this.get_new_date(input_date);

		// Check date is valid
		if(!this.date_valid(output_date)) { return ''; }

		// Apply GMT offset
		output_date.setHours(output_date.getHours() + (gmt_offset * -1));

		return output_date;
	}

})(jQuery);
