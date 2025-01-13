(function($) {

	'use strict';

	// Form - Checkbox Min / Max
	$.WS_Form.prototype.form_checkbox_min_max = function() {

		var ws_this = this;

		$('[data-checkbox-min]:not([data-checkbox-min-max-init]),[data-checkbox-max]:not([data-checkbox-min-max-init])', this.form_canvas_obj).each(function () {

			var checkbox_min = $(this).attr('data-checkbox-min');
			var checkbox_max = $(this).attr('data-checkbox-max');

			// If neither attribute present, disregard this feature
			if(
				(typeof(checkbox_min) === 'undefined') &&
				(typeof(checkbox_max) === 'undefined')
			) {

				return;
			}

			// Get field ID
			var field_id = $(this).attr('data-id');

			// Get repeatable suffix
			var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

			// Get field label
			var field_config = ws_this.field_data_cache[field_id];
			var field_label = field_config.label;

			// Build number input
			var checkbox_min_max = $('<input type="number" id="' + ws_this.esc_attr(ws_this.form_id_prefix) + 'checkbox-min-max-' + ws_this.esc_attr(field_id + section_repeatable_suffix) + '" data-checkbox-min-max data-progress-include="change" style="display: none !important;" aria-label="Validator" />', ws_this.form_canvas_obj);

			// Add min attribute
			if(typeof(checkbox_min) !== 'undefined') { checkbox_min_max.attr('min', checkbox_min); }

			// Add max attribute
			if(typeof(checkbox_max) !== 'undefined') { checkbox_min_max.attr('max', checkbox_max); }
			checkbox_max = parseInt(checkbox_max, 10);

			// Add value attribute
			var checked_count = $('input[type="checkbox"]:not([data-wsf-select-all]):checked', $(this)).length;
			checkbox_min_max.attr('value', checked_count);

			// Add before invalid feedback
			var invalid_feedback_obj = ws_this.get_invalid_feedback_obj($(this));
			if(invalid_feedback_obj.length) {

				invalid_feedback_obj.before(checkbox_min_max);

			} else {

				$(this).append(checkbox_min_max);
			}

			// Add event on all checkboxes
			$('input[type="checkbox"]:not([data-wsf-select-all])', $(this)).on('change', function(e) {

				// Get field wrapper
				var field_wrapper = ws_this.get_field_wrapper($(this));

				// Get field ID
				var field_id = ws_this.get_field_id($(this));

				// Get repeatable suffix
				var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

				// Get count
				var checked_count = $('input[type="checkbox"]:not([data-wsf-select-all]):checked', field_wrapper).length;

				// Max check
				var input_number = $('input[type="number"]', field_wrapper);
				var checkbox_max = ws_this.get_number(input_number.attr('max'), 0, false);

				if(
					(checkbox_max > 0) &&
					(checked_count > checkbox_max)
				) {

					$(this).prop('checked', false);
					checked_count--;
				}

				// Set count
				var checkbox_min_max_obj = $('#' + ws_this.form_id_prefix + 'checkbox-min-max-' + field_id + section_repeatable_suffix, ws_this.form_canvas_obj);
				checkbox_min_max_obj.val(checked_count).trigger('change');
			});

			// Flag so it only initializes once
			$(this).attr('data-checkbox-min-max-init', '');
		});
	}

	// Select all
	$.WS_Form.prototype.form_checkbox_select_all = function() {

		var ws_this = this;

		$('[data-wsf-select-all]:not([data-init-select-all])', this.form_canvas_obj).each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-select-all', '');

			// Get select all name
			var select_all_name = $(this).attr('name');
			$(this).removeAttr('name').removeAttr('value').attr('data-wsf-select-all', select_all_name);

			// Click event
			$(this).on('click', function() {

				var select_all = $(this).is(':checked');
				var select_all_name = $(this).attr('data-wsf-select-all');

				// Get field wraper
				var field_wrapper_obj = $(this).closest('[data-id]');

				// Is select all within a field set
				var fieldset_obj = $(this).closest('fieldset', field_wrapper_obj);

				// Determine context
				var context = fieldset_obj.length ? fieldset_obj : ws_this.form_canvas_obj;

				// We use 'each' here to ensure they are checked in ascending order
				$('[name="' + ws_this.esc_selector(select_all_name) + '"]:enabled', context).each(function() {

					$(this).prop('checked', select_all).trigger('change');
				});
			})
		});
	}

})(jQuery);
