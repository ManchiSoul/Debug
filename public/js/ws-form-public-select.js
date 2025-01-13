(function($) {

	'use strict';

	// Select
	$.WS_Form.prototype.form_select = function() {

		if(ws_form_settings.styler_enabled) {

			// Add span for down arrow
			$('select:not([multiple]):not([size]):not([data-wsf-select2])', this.form_canvas_obj).each(function () {

				$(this).after('<span class="wsf-select-arrow"></span>');
			});
		}
	}

	// Select2
	$.WS_Form.prototype.form_select2 = function(obj) {

		var ws_this = this;

		if(typeof(obj) === 'undefined') { obj = $('[data-wsf-select2]', this.form_canvas_obj); }

		// Check Select2 is loaded
		if(typeof(jQuery().select2) !== 'undefined') {

			obj.each(function() {

				// Get field ID
				var field_id = ws_this.get_field_id($(this));

				// Get field
				var field = ws_this.get_field($(this));

				// Get placeholder
				var placeholder = ws_this.get_object_meta_value(field, 'placeholder_row', '');

				// Get language
				var locale = ws_form_settings.locale;
				var language = locale.substring(0, 2);

				// Check for language overrides
				var language_custom = {}

				ws_this.form_select2_language_add(language_custom, field, 'select2_language_error_loading', 'errorLoading');
				ws_this.form_select2_language_add(language_custom, field, 'select2_language_input_too_short', 'inputTooShort');
				ws_this.form_select2_language_add(language_custom, field, 'select2_language_input_too_long', 'inputTooLong');
				ws_this.form_select2_language_add(language_custom, field, 'select2_language_no_results', 'noResults');
				ws_this.form_select2_language_add(language_custom, field, 'select2_language_searching', 'searching');

				if(Object.keys(language_custom).length) {

					language = language_custom;
				}

				// Build AJAX URL
				var url = ws_form_settings.url_ajax + 'field/' + field_id + '/select-ajax/';

				// Build args
				var args = {

					// Custom selection template to support e-commerce fields
					templateSelection: function(container) {

						if(typeof(container.data_price) !== 'undefined') {

							$(container.element).attr('data-price', container.data_price);
						}

						return container.text;
					},

					// Add clear icon
					allowClear: true,

					// Placeholder
					placeholder: placeholder,

					// Language
					language: language,

					// CSS
					selectionCssClass: 'wsf-select2-selection',
					dropdownCssClass: 'wsf-select2-dropdown',

					// Dropdown parent
					dropdownParent: $(this).parent()
				};

				// Minimum input length
				var minimum_input_length = ws_this.get_object_meta_value(field, 'select2_minimum_input_length', '');
				if(minimum_input_length != '') {

					args.minimumInputLength = parseInt(minimum_input_length, 10);
				}

				// Maximum input length
				var maximum_input_length = ws_this.get_object_meta_value(field, 'select2_maximum_input_length', '');
				if(maximum_input_length != '') {

					args.maximumInputLength = parseInt(maximum_input_length, 10);
				}

				// Use AJAX? (Cannot be used if cascading is enabling)
				var cascade = (ws_this.get_object_meta_value(field, field.type + '_cascade', '') == 'on');
				var select2_ajax = !cascade && (ws_this.get_object_meta_value(field, 'select2_ajax', '') == 'on');
				if(select2_ajax) {

					var field_obj = $(this);

					args['ajax'] = {

						// AJAX URL
						url: url,

						// Data type of JSON
						dataType: 'json',

						// Modify request
						data: function (params) {

							var query = {

								id: ws_this.form_id,
								preview: ws_this.form_canvas_obj[0].hasAttribute('data-preview'),
								keyword: params.term
							};

							// Check for initial population
							if(typeof($(this).attr('data-wsf-populate')) !== 'undefined') {

								var select2_populate = $(this).attr('data-wsf-populate');

								if(select2_populate !== '') {

									query.value = select2_populate;
								}

								if(typeof($(this).attr('data-cascade-ajax')) === 'undefined') {

									$(this).removeAttr('data-wsf-populate');
								}
							}

							// NONCE
							if(ws_form_settings.wsf_nonce) {

								query[ws_form_settings.wsf_nonce_field_name] = ws_form_settings.wsf_nonce;
							}

							if(ws_form_settings.x_wp_nonce) {

								query['_wpnonce'] = ws_form_settings.x_wp_nonce;
							}

							return query;
						},

						processResults: function (data) {

							if(
								data.results &&
								data.results.length
							) {

								field_obj.attr('data-select2-ajax-results', '');

							} else {

								field_obj.removeAttr('data-select2-ajax-results');
							}

							// Trigger results
							field_obj.trigger('wsf-select2-ajax-results');

							return data;
						},

						// Add call delay
						delay: 250,

						// Enable caching
						cache: true
					};
				}

				// Tagging
				var multiple = (ws_this.get_object_meta_value(field, 'multiple', '') == 'on');
				var select2_tags = (ws_this.get_object_meta_value(field, 'select2_tags', '') == 'on');

				if(multiple && select2_tags) {

					args['tags'] = true;
				}

				// Max
				var obj_wrapper = $(this).closest('[data-type="select"]');
				var select_max = obj_wrapper.attr('data-select-max');

				if(select_max) {

					var select_max = parseInt(select_max, 10);
					if(select_max > 0) {

						args['maximumSelectionLength'] = select_max;
					}
				}

				// Initialize select2
				var select2_obj = $(this).select2(args);

				// Autofocus
				select2_obj.on('select2:open', function (e) {

					$('.select2-search__field', $(this).parent()).get(0).focus();
				});

				// Check for pre-population
				var select2_populate = typeof($(this).attr('data-wsf-populate')) !== 'undefined' ? $(this).attr('data-wsf-populate') : '';
				if(select2_populate !== '') {

					// Build params
					var form_data = new FormData();

					// Form ID
					form_data.append('id', ws_this.form_id);

					// Preview?
					form_data.append('preview', ws_this.form_canvas_obj[0].hasAttribute('data-preview'));

					// Value
					form_data.append('value', select2_populate);

					// Make AJAX request
					ws_this.api_call('field/' + field_id + '/select-ajax/', 'GET', form_data, function(data) {

						if(data.results) {

							for(var result_index in data.results) {

								if(!data.results.hasOwnProperty(result_index)) { continue; }

								var result = data.results[result_index];

								if(typeof(result.children) !== 'undefined') {

									// Add optgroup
									var optgroup = $('<optgroup>');
									optgroup.attr('label', result.text);
									var select2_optgroup_obj = select2_obj.append(optgroup);

									for(var result_child_index in result.children) {

										if(!result.children.hasOwnProperty(result_child_index)) { continue; }

										var result_child = result.children[result_child_index];

										// Add selected options
										var option = $('<option>');
										option.attr('value', result_child.id).attr('selected', '').html(result_child.text);
										select2_optgroup_obj.append(option);
									}

								} else {

									// Add selected option
									var option = $('<option>');
									option.attr('value', result.id).attr('selected', '').html(result.text);
									select2_obj.append(option);
								}
							}

							// Trigger change
							select2_obj.trigger('change');
						}
					})
				}
			});
		}
	}

	$.WS_Form.prototype.form_select2_language_add = function(language_custom, field, meta_key, language_key) {

		var custom_message = this.get_object_meta_value(field, meta_key, '')

		if(custom_message == '') { return; }

		var ws_this = this;

		language_custom[language_key] = function (args) {

			var mask_lookups = {};

			switch(language_key) {

				case 'searching' :

					mask_lookups['term'] = (typeof(args.term) !== 'undefined') ? args.term : '';
					break;

				case 'inputTooShort' :

					mask_lookups['term'] = (typeof(args.input) !== 'undefined') ? args.input : '';

					var remaining_chars = args.minimum - args.input.length;
					mask_lookups['char_remaining'] = remaining_chars;
					mask_lookups['char_plural'] = (remaining_chars != 1) ? 's' : '';
					break;

				case 'inputTooLong' :

					mask_lookups['term'] = (typeof(args.input) !== 'undefined') ? args.input : '';

					var over_chars = args.input.length - args.maximum;
					mask_lookups['char_over'] = over_chars;
					mask_lookups['char_plural'] = (over_chars != 1) ? 's' : '';
					break;
			}

			return ws_this.mask_parse(custom_message, mask_lookups);
		}
	}

	// Select Min / Max
	$.WS_Form.prototype.form_select_min_max = function() {

		var ws_this = this;

		$('[data-select-min]:not([data-select-min-max-init]),[data-select-max]:not([data-select-min-max-init])', this.form_canvas_obj).each(function () {

			var select_min = $(this).attr('data-select-min');
			var select_max = $(this).attr('data-select-max');

			// If neither attribute present, disregard this feature
			if(
				(typeof(select_min) === 'undefined') &&
				(typeof(select_max) === 'undefined')
			) {

				return;
			}

			// Get field ID
			var field_id = $(this).attr('data-id');

			// Get repeatable suffix
			var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

			// Build number input
			var select_min_max = $('<input type="number" id="' + ws_this.esc_attr(ws_this.form_id_prefix + 'select-min-max-' + field_id + section_repeatable_suffix) + '" data-select-min-max data-progress-include="change" style="display: none !important;" aria-label="Validator" />', ws_this.form_canvas_obj);

			// Add min attribute
			if(typeof(select_min) !== 'undefined') { select_min_max.attr('min', select_min); }

			// Add max attribute
			if(typeof(select_max) !== 'undefined') { select_min_max.attr('max', select_max); }
			select_max = parseInt(select_max, 10);

			// Add value attribute
			var selected_count = $('select option:selected', $(this)).length;
			select_min_max.attr('value', selected_count);

			// Add before invalid feedback
			var invalid_feedback_obj = ws_this.get_invalid_feedback_obj($(this));
			if(invalid_feedback_obj.length) {

				invalid_feedback_obj.before(select_min_max);

			} else {

				$(this).append(select_min_max);
			}

			// Add event on all selects
			$('select', $(this)).on('change', function() {

				var field_wrapper = ws_this.get_field_wrapper($(this));

				// Get field ID
				var field_id = ws_this.get_field_id($(this));

				// Get repeatable suffix
				var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(this));

				// Get count
				var selected_count = $('select option:selected', field_wrapper).length;

				// Set count
				var select_min_max_obj = $('#' + ws_this.form_id_prefix + 'select-min-max-' + field_id + section_repeatable_suffix, ws_this.form_canvas_obj);
				select_min_max_obj.val(selected_count).trigger('change');
			});

			// Flag so it only initializes once
			$(this).attr('data-select-min-max-init', '');
		});
	}

})(jQuery);
