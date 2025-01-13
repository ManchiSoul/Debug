(function($) {

	'use strict';

	// Cascading
	$.WS_Form.prototype.form_cascade = function() {

		var ws_this = this;

		// Remember checked options
		$('[data-cascade-field-id]:not([data-init-cascade]) input:checked', this.form_canvas_obj).attr('data-checked', '');

		// Process each cascading field
		$('[data-cascade-field-id]:not([data-init-cascade])', this.form_canvas_obj).each(function() {

			var field_id

			// Save this object
			var obj_child_field = $(this);

			// Get child field
			var obj_child = $(this).closest('[data-type]');

			// This skips any subsequent groups that were set as initialized
			if(typeof($(this).attr('data-init-cascade')) !== 'undefined') { return; }

			// Get parent ID
			var obj_parent_id = $(this).attr('data-cascade-field-id');

			// Get repeatable index
			var obj_child_repeatable_index = obj_child.attr('data-repeatable-index');

			// Flag so it only initializes once
			$('[data-cascade-field-id="' + ws_this.esc_selector(obj_parent_id) + '"]', obj_child).attr('data-init-cascade', '');

			// Get parent field
			if(typeof(ws_this.field_data_cache[obj_parent_id]) === 'undefined') { return; }
			var obj_parent_field_config = ws_this.field_data_cache[obj_parent_id];
			var obj_parent_type = obj_parent_field_config.type;

			var obj_parent = false;

			switch(obj_parent_type) {

				case 'hidden' :

					var id_parent_attribute = 'data-id-hidden';

					if(obj_child_repeatable_index) {

						// Field is in a repeatable section, so lets see if parent field if in same section
						var obj_parent = $('[id^="' + ws_this.esc_selector(ws_this.form_id_prefix + 'field-' + obj_parent_id) + '"][data-id-hidden="' + ws_this.esc_selector(obj_parent_id) + '"][data-repeatable-index="' + ws_this.esc_selector(obj_child_repeatable_index) + '"]', ws_this.form_canvas_obj);
						if(!obj_parent.length) { obj_parent = false; }
					}

					if(!obj_parent) {

						// Field is not a repeatable section
						var obj_parent = $('[id="' + ws_this.esc_selector(ws_this.form_id_prefix + 'field-' + obj_parent_id) + '"]', ws_this.form_canvas_obj);				
					}

					break;

				default :

					var id_parent_attribute = 'data-id';

					if(obj_child_repeatable_index) {

						// Field is in a repeatable section, so lets see if parent field if in same section
						var obj_parent = $('[id^="' + ws_this.esc_selector(ws_this.form_id_prefix + 'field-wrapper-' + obj_parent_id) + '"][data-id="' + ws_this.esc_selector(obj_parent_id) + '"][data-repeatable-index="' + ws_this.esc_selector(obj_child_repeatable_index) + '"]', ws_this.form_canvas_obj);
						if(!obj_parent.length) { obj_parent = false; }
					}

					if(!obj_parent) {

						// Field is not a repeatable section
						var obj_parent = $('[id="' + ws_this.esc_selector(ws_this.form_id_prefix + 'field-wrapper-' + obj_parent_id) + '"]', ws_this.form_canvas_obj);				
					}
			}

			// Parent cannot be found
			if(!obj_parent.length) { return; }

			// Parent data
			var repeatable_index_parent = obj_parent.attr('data-repeatable-index');
			if(!repeatable_index_parent) { repeatable_index_parent = 0; }

			var cascade_parent = {

				obj: obj_parent,
				id: obj_parent_id,
				field_config: obj_parent_field_config,
				repeatable_index: repeatable_index_parent,
				type: obj_parent_type
			};

			// Child data
			var id_child = obj_child.attr('data-id');
			var repeatable_index_child = obj_child.attr('data-repeatable-index');
			if(!repeatable_index_child) { repeatable_index_child = 0; }

			var cascade_child = {

				obj: obj_child,
				id: id_child,
				field_config: ws_this.field_data_cache[id_child],
				repeatable_index: repeatable_index_child,
				type: obj_child.attr('data-type')
			};

			// Determine event triggers
			switch(obj_parent_type) {

				case 'text' :
				case 'number' :
				case 'rating' :
				case 'range' :
				case 'price_range' :

					var obj_parent_event = 'change input';

					break;

				default :

					var obj_parent_event = 'change';
			}

			// Event handler
			obj_parent.on(obj_parent_event, function() {

				ws_this.form_cascade_process(cascade_parent, cascade_child, obj_child_field);
			});

			// Initial process
			ws_this.form_cascade_process(cascade_parent, cascade_child, obj_child_field);
		});
	}

	$.WS_Form.prototype.form_cascade_process = function(cascade_parent, cascade_child, obj_child_field) {

		var ws_this = this;

		// Get child data
		var obj_child = cascade_child.obj;
		var obj_child_id = cascade_child.id;
		var obj_child_field_config = cascade_child.field_config;
		var obj_child_repeatable_index = cascade_child.repeatable_index;
		var obj_child_type = cascade_child.type;

		// Get parent data
		var obj_parent = cascade_parent.obj;
		var obj_parent_id = cascade_parent.id;
		var obj_parent_repeatable_index = cascade_parent.repeatable_index;
		var obj_parent_repeatable_suffix = (obj_parent_repeatable_index > 0) ? '[' + obj_parent_repeatable_index + ']' : '';
		var obj_parent_type = cascade_parent.type;
		var obj_parent_name = this.field_name_prefix + obj_parent_id + obj_parent_repeatable_suffix;

		// Get no rows option text
		var option_text_no_rows = this.get_object_meta_value(obj_child_field_config, obj_child_type + '_cascade_option_text_no_rows', '');
		if(option_text_no_rows == '') { option_text_no_rows = this.language('cascade_option_text_no_rows'); }

		// Cascade no match (Show all if no matches)
		var cascade_no_match = this.get_object_meta_value(obj_child_field_config, obj_child_type + '_cascade_no_match', false);

		switch(obj_parent_type) {

			case 'select' :
			case 'price_select' :

				var obj_parent_value = $('[name="' + this.esc_selector(obj_parent_name) + '[]"]', this.form_canvas_obj).val();
				break;

			case 'checkbox' :
			case 'price_checkbox' :
			case 'radio' :
			case 'price_radio' :

				var obj_parent_value = [];
				$('[name="' + this.esc_selector(obj_parent_name) + '[]"]:checked', this.form_canvas_obj).each(function() {

					obj_parent_value.push($(this).val());
				});
				break;

			default :

				var obj_parent_value = $('[name="' + this.esc_selector(obj_parent_name) + '"]', this.form_canvas_obj).val();
		}

		if(
			(obj_parent_value === null) ||
			(obj_parent_value === '') ||
			(typeof(obj_parent_value) === 'undefined') ||
			((typeof(obj_parent_value) === 'object') && (obj_parent_value.length === 0))
		) {

			obj_parent_value = false;

		} else {

			if(typeof(obj_parent_value) !== 'object') {

				obj_parent_value = [obj_parent_value];
			}
		}

		switch(obj_child_type) {

			case 'select' :
			case 'price_select' :

				var obj_child_select = $('select', obj_child);

				// Check for Select2
				if(typeof(obj_child_field.attr('data-wsf-select2')) !== 'undefined') {

					var select2_obj = $('+ .select2', obj_child_field);

				} else {

					var select2_obj = false
				}

				// Check for AJAX
				if(typeof(obj_child_field.attr('data-cascade-ajax')) !== 'undefined') {

					// Cancel existing API call
					if(
						!this.form_post_locked &&
						(typeof(this.api_call_handle[obj_child_id]) === 'object')
					) {

						this.api_call_handle[obj_child_id].abort();
					}

					// Handle empty parent value
					if((obj_parent_value === false) && !cascade_no_match) {

						// Set select to show placeholder text
						obj_child_select.html('<option value="" data-placeholder>' + this.esc_html(option_text_no_rows) + '</option>').val('');

						return;
					}

					// Get placeholder
					var placeholder_row = this.get_object_meta_value(obj_child_field_config, 'placeholder_row', '');

					// Get loading option text
					var option_text_loading = this.get_object_meta_value(obj_child_field_config, obj_child_type + '_cascade_ajax_option_text_loading', '');
					if(option_text_loading == '') { option_text_loading = this.language('cascade_option_text_loading'); }

					// Set select 2 loading placeholder
					if(select2_obj) {

						this.form_cascade_set_select2_placeholder(obj_child_select, option_text_loading);
					}

					// Set select to show placeholder text
					var trigger = (

						(obj_child_select.val() !== '') &&
						(obj_child_select.val() !== null)
					);
					obj_child_select.html('<option value="" data-placeholder>' + this.esc_html(option_text_loading) + '</option>').val('');
					if(trigger) { obj_child_select.trigger('change'); }

					// Build params
					var form_data = new FormData();

					// Form ID
					form_data.append('id', this.form_id);

					// Parent value
					form_data.append('value', JSON.stringify(obj_parent_value));

					// Preview
					form_data.append('preview', this.form_canvas_obj[0].hasAttribute('data-preview'));

					// Default value
					if(typeof(obj_child_select.attr('data-wsf-populate')) !== 'undefined') {

						form_data.append('default_value', obj_child_select.attr('data-wsf-populate'));

						obj_child_select.removeAttr('data-wsf-populate');
					}

					// Call API
					this.api_call_handle[obj_child_id] = this.api_call('field/' + obj_child_id + '/cascade/', 'GET', form_data, function(response) {

						if(!response.data) { return; }

						var field_html = ws_this.get_field_html(response.data, obj_child.attr('data-repeatable-index'));

						var options_html = $('select', $(field_html)).html();

						if(options_html) {

							if(select2_obj) { ws_this.form_cascade_set_select2_placeholder(obj_child_select, placeholder_row); }

							obj_child_select.html(options_html);

						} else {

							if(select2_obj) { ws_this.form_cascade_set_select2_placeholder(obj_child_select, option_text_no_rows); }

							// Set select to show placeholder text
							obj_child_select.html('<option value="" data-placeholder>' + ws_this.esc_html(option_text_no_rows) + '</option>').val('');
						}

						// Remove empty optgroups
						$('optgroup', obj_child_select).each(function() {

							if(!$('option', $(this)).length) { $(this).remove(); }
						});

						// Trigger change
						obj_child_select.trigger('change');
					});

				} else {

					// Get select
					var obj_child_select = $('select', obj_child);

					if(typeof(this.cascade_cache[obj_child_id]) === 'undefined') {

						this.cascade_cache[obj_child_id] = [];
					}

					if(typeof(this.cascade_cache[obj_child_id][obj_child_repeatable_index]) === 'undefined') {

						// Add to cache
						this.cascade_cache[obj_child_id][obj_child_repeatable_index] = '<select id="wsf-cascade-scratch">' + obj_child_select.html() + '</select>';
					}

					// Retrieve from cache
					var obj_child_select_scratch = $(this.cascade_cache[obj_child_id][obj_child_repeatable_index]);

					// Comma separate child values?
					var cascade_field_filter_comma = this.get_object_meta_value(obj_child_field_config, obj_child_type + '_cascade_field_filter_comma', false);

					// Check for matched rows
					var matched_row = false;
					if(obj_parent_value !== false) {

						$('option', obj_child_select_scratch).each(function() {

							var cascade_value = $(this).attr('data-cascade-value');

							if(typeof(cascade_value) === 'string') {

								var cascade_value_array = cascade_field_filter_comma ? cascade_value.split(',') : [cascade_value];

								for(var cascade_value_array_index in cascade_value_array) {

									if(!cascade_value_array.hasOwnProperty(cascade_value_array_index)) { continue; }

									var cascade_value = cascade_value_array[cascade_value_array_index];

									if(obj_parent_value.indexOf(cascade_value) !== -1) {

										// Match found so we won't show all rows
										matched_row = true;
										return false;
									}
								}
							}
						});
					}

					// Cascade no match
					if(!matched_row) {

						// Show all if no match?
						var cascade_no_match = this.get_object_meta_value(obj_child_field_config, obj_child_type + '_cascade_no_match', false);
						if(cascade_no_match) {

							// Show all
							obj_child_select.html(obj_child_select_scratch.html());

						} else {

							// Set select to show placeholder text
							obj_child_select.html('<option value="" data-placeholder>' + this.esc_html(option_text_no_rows) + '</option>').val('');
						}

						// Trigger change
						obj_child_select.trigger('change');

						return;
					}

					// Hide non matching rows
					$('option', obj_child_select_scratch).each(function() {

						var cascade_value = $(this).attr('data-cascade-value');

						if(typeof(cascade_value) === 'string') {

							var cascade_value_array = cascade_field_filter_comma ? cascade_value.split(',') : [cascade_value];

							var cascade_value_found = false;

							for(var cascade_value_array_index in cascade_value_array) {

								if(!cascade_value_array.hasOwnProperty(cascade_value_array_index)) { continue; }

								var cascade_value = cascade_value_array[cascade_value_array_index];

								if(obj_parent_value.indexOf(cascade_value) !== -1) {

									cascade_value_found = true;
									break;
								}
							}

							if(!cascade_value_found) {

								$(this).remove();
							}
						}
					});

					// Remove empty optgroups
					$('optgroup', obj_child_select_scratch).each(function() {

						if(!$('option', $(this)).length) { $(this).remove(); }
					});

					// Show filtered scratch
					obj_child_select.html(obj_child_select_scratch.html());

					// Trigger change
					obj_child_select.trigger('change');
				}

				break;

			case 'checkbox' :
			case 'price_checkbox' :
			case 'radio' :
			case 'price_radio' :

				// Find a matching row
				var matched_row = false;

				// Comma separate child values?
				var cascade_field_filter_comma = this.get_object_meta_value(obj_child_field_config, obj_child_type + '_cascade_field_filter_comma', false);

				// Check for matched rows
				if(obj_parent_value !== false) {

					$('[data-cascade-value]', obj_child).each(function() {

						var cascade_value = $(this).attr('data-cascade-value');

						if(typeof(cascade_value) === 'string') {

							var cascade_value_array = cascade_field_filter_comma ? cascade_value.split(',') : [cascade_value];

							for(var cascade_value_array_index in cascade_value_array) {

								if(!cascade_value_array.hasOwnProperty(cascade_value_array_index)) { continue; }

								var cascade_value = cascade_value_array[cascade_value_array_index];

								if(obj_parent_value.indexOf(cascade_value) !== -1) {

									// Match found so we won't show all rows
									matched_row = true;
									return false;
								}
							}
						}
					});
				}

				// Cascade no match
				var cascade_no_match = this.get_object_meta_value(obj_child_field_config, obj_child_type + '_cascade_no_match', false);
				if(cascade_no_match && !matched_row) {

					$('[data-cascade-value]', obj_child).show().attr('data-cascade-on', '');
					$('fieldset', obj_child).show();

					return;
				}

				// No matching rows
				if(!matched_row) {

					// Hide all
					$('[data-cascade-value]', obj_child).hide().removeAttr('data-cascade-on');

				} else {

					// Hide non matching rows
					$('[data-cascade-value]', obj_child).each(function() {

						var cascade_value = $(this).attr('data-cascade-value');

						if(typeof(cascade_value) === 'string') {

							var cascade_value_array = cascade_field_filter_comma ? cascade_value.split(',') : [cascade_value];

							var cascade_value_found = false;

							for(var cascade_value_array_index in cascade_value_array) {

								if(!cascade_value_array.hasOwnProperty(cascade_value_array_index)) { continue; }

								var cascade_value = cascade_value_array[cascade_value_array_index];

								if(obj_parent_value.indexOf(cascade_value) !== -1) {

									cascade_value_found = true;
									break;
								}
							}

							if(cascade_value_found) {

								$(this).show().attr('data-cascade-on', '');

							} else {

								$(this).hide().removeAttr('data-cascade-on');
							}
						}
					});
				}

				// Remove empty fieldsets
				$('fieldset', obj_child).show().each(function() {

					if(!$('[data-cascade-value][data-cascade-on]', $(this)).length) { $(this).hide(); }
				});

				// Check / uncheck according to datagrid defaults
				$('[data-cascade-value]:not([data-cascade-on]) input:checked', obj_child).each(function() {

					$(this).prop('checked', false).trigger('change');
				});
				$('[data-cascade-value][data-cascade-on] input[data-checked]:not(:checked)', obj_child).each(function() {

					$(this).prop('checked', true).trigger('change');
				});

				break;
		}
	}

	// Set select2 placeholder
	$.WS_Form.prototype.form_cascade_set_select2_placeholder = function(obj, placeholder_text) {

		var select2 = obj.data('select2');
		if(
			select2 &&
			select2.selection &&
			select2.selection.placeholder &&
			select2.selection.placeholder.text
		) {

			select2.selection.placeholder.text = placeholder_text;
		}
	}

})(jQuery);
