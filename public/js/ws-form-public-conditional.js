(function($) {

	'use strict';

	// Form - Initialize client side form conditions
	$.WS_Form.prototype.form_conditional = function(initial) {

		var ws_this = this;

		if(typeof(initial) === 'undefined') { initial = false; }

		if(this.conditional_cache === false) {

			// Run through the form conditions
			var conditionals = this.get_object_meta_value(this.form, 'conditional', false);

			if(typeof(conditionals.groups) === 'undefined') { return false; }
			if(typeof(conditionals.groups[0]) === 'undefined') { return false; }
			if(typeof(conditionals.groups[0].rows) === 'undefined') { return false; }

			for(var conditional_index in conditionals.groups[0].rows) {

				if(!conditionals.groups[0].rows.hasOwnProperty(conditional_index)) { continue; }

				var row = conditionals.groups[0].rows[conditional_index];

				if(
					(row === null) ||
					(typeof(row) !== 'object')
				) {
					continue;
				}

				// Disabled?
				if((typeof(row.disabled) !== 'undefined') && row.disabled) { continue; }

				// Check for data
				if(
					(typeof(row.data) === 'undefined') ||
					(typeof(row.data[0]) === 'undefined') ||
					(typeof(row.data[1]) === 'undefined')
				) {
					continue;
				}

				// Read data
				var conditional_label = row.data[0];
				var conditional = row.data[1];

				// Attempt to JSON decode it
				try {

					var conditional = JSON.parse(conditional);

				} catch(e) {

					var conditional = false;
				}

				// If conditional found, set up events for it
				if(conditional !== false) {

					conditional.label = conditional_label;
					conditional.last_state = [];
					this.conditional_events(conditional, initial);
					if(this.conditional_cache === false) { this.conditional_cache = []; }
					this.conditional_cache.push(conditional);
				}
			}

		} else {

			// Use cache (Ensure same conditional objects are used if this function is called again)
			for(var conditional_index in this.conditional_cache) {

				if(!this.conditional_cache.hasOwnProperty(conditional_index)) { continue; }

				var conditional = this.conditional_cache[conditional_index];
				conditional.last_state = [];
				this.conditional_events(conditional, initial);
			}
		}

		// Create events
		var conditional_initial_objects = [];

		for(var conditional_selector in this.conditional_event_selector_to_condition) {

			if(!this.conditional_event_selector_to_condition.hasOwnProperty(conditional_selector)) { continue; }

			var events = this.conditional_event_selector_to_condition[conditional_selector];

			for(var conditional_event in events) {

				if(!events.hasOwnProperty(conditional_event)) { continue; }

				var selector_event_config = events[conditional_event];

				var object_event_selector = selector_event_config.object_event_selector;
				var object_event_obj = selector_event_config.object_event_obj;
				var conditionals = selector_event_config.conditionals;

				// Initialize events?
				if(initial) {

					if(conditional_selector === 'null') { conditional_selector = null; }

					// Create missing events
					var conditional_event_added = false;
					object_event_obj.each(function() {

						$(this).on(conditional_event, conditional_selector, { conditionals : conditionals }, function(event, form, form_id, form_instance_id) {

							// If this is a $(document) level trigger, check if it came from the current form
							if(
								(typeof(form_id) !== 'undefined') &&
								(typeof(form_instance_id) !== 'undefined') &&
								(ws_this.form_id !== form_id) &&
								(ws_this.form_instance_id !== form_instance_id)
							) {
								return;
							}

							var repeatable_section_obj = $(this).closest('[data-repeatable-index]');
							var source_repeatable_index = repeatable_section_obj.length ? repeatable_section_obj.attr('data-repeatable-index') : 0;

							for(var conditional_index in event.data.conditionals) {

								if(!event.data.conditionals.hasOwnProperty(conditional_index)) { continue; }

								var conditional = event.data.conditionals[conditional_index];

								ws_this.conditional_process(conditional, false, $(this), source_repeatable_index, event);
							}
						});

						// Add to event reset array
						ws_this.form_events_reset.push({

							obj: $(this),
							event: conditional_event
						});
					});

					// Log
					this.log('log_conditional_event', ((conditional_selector === null) ? this.language('debug_form') : conditional_selector) + ' (' + conditional_event + ')', 'conditional');
				}

				// Get event objects
				var event_objs = $(object_event_selector, this.form_canvas_obj);
				var event_objs_unique = [];
				var event_objs_names = [];

				// Make event objects unique (Prevents multiple processing for checkbox and radio rows)
				event_objs.each(function() {

					// Ignore elements that don't have a name attribute (e.g. checkbox and radio labels)
					if(typeof($(this).attr('name')) === 'undefined') { return; }

					// Get the name attribute value
					var event_obj_name = $(this).attr('name');

					// If this event object has not already been added, add it
					if(!event_objs_names.includes(event_obj_name)) {

						event_objs_names.push(event_obj_name);
						event_objs_unique.push($(this));
					}
				});

				// Fire conditionals initially (Only once per unique element)
				for(var event_objs_unique_index in event_objs_unique) {

					if(!event_objs_unique.hasOwnProperty(event_objs_unique_index)) { continue; }

					var event_obj = event_objs_unique[event_objs_unique_index];

					// If this field only referenced conditions that are not in repeaters, then only initialize once
					if(typeof(event_obj.attr('data-wsf-conditional-initialize-once')) !== 'undefined') { continue; }

					// Get repeatable index of source
					var repeatable_section_obj = event_obj.closest('[data-repeatable-index]');
					var source_repeatable_index = repeatable_section_obj.length ? repeatable_section_obj.attr('data-repeatable-index') : 0;

					var destination_repeatable = false;

					for(var conditional_index in conditionals) {

						if(!conditionals.hasOwnProperty(conditional_index)) { continue; }

						var conditional = conditionals[conditional_index];

						if(ws_this.conditional_destination_repeatable(conditional)) {

							var destination_repeatable = true;
						}

						ws_this.conditional_process(conditional, true, event_obj, source_repeatable_index, false);
					}

					// Add to objects that should only be initialized once
					if(!destination_repeatable && !conditional_initial_objects.includes(event_obj)) {

						conditional_initial_objects.push(event_obj);
					}
				}
			}
		}

		// Mark objects that should only be initialized once (those that don't reference a repeater in any way and can be safely ignored if a repeater row is added)
		for(var conditional_initial_objects_index in conditional_initial_objects) {

			if(!conditional_initial_objects.hasOwnProperty(conditional_initial_objects_index)) { continue; }

			var conditional_initial_object = conditional_initial_objects[conditional_initial_objects_index];

			conditional_initial_object.attr('data-wsf-conditional-initialize-once', '');
		}
	}

	// Form - Check if conditional targets fields in a repeatable section
	$.WS_Form.prototype.conditional_destination_repeatable = function(conditional) {

		return this.conditional_destination_repeatable_then_else(conditional, 'then') || this.conditional_destination_repeatable_then_else(conditional, 'else');
	}

	$.WS_Form.prototype.conditional_destination_repeatable_then_else = function(conditional, thenelse) {

		var actions = conditional[thenelse];

		for(var action_index in actions) {

			if(!actions.hasOwnProperty(action_index)) { continue; }

			var action_single = actions[action_index];

			// Check integrity of action
			if(!this.conditional_action_check(action_single)) { continue; }

			// Read action data
			var destination_object = action_single['object'];
			var destination_object_id = action_single['object_id'];

			// Process by object type
			switch(destination_object) {

				case 'section' :

					// Get section
					if(typeof(this.section_data_cache[destination_object_id]) == 'undefined') { continue; }
					var section = this.section_data_cache[destination_object_id];

					break;

				case 'field' :

					// Get field
					if(typeof(this.field_data_cache[destination_object_id]) == 'undefined') { continue; }
					var field = this.field_data_cache[destination_object_id];

					// Get field section ID
					var section_id = field.section_id;

					// Get section
					if(typeof(this.section_data_cache[section_id]) == 'undefined') { continue; }
					var section = this.section_data_cache[section_id];

					break;
			}

			if(this.get_object_meta_value(section, 'section_repeatable')) { return true; }
		}

		return false;
	}

	// Form - Conditional - Events
	$.WS_Form.prototype.conditional_events = function(conditional, initial) {

		if(typeof(initial) === 'undefined') { initial = true; }

		var ws_this = this;

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		// Run through if groups
		for(var group_index in conditional.if) {

			if(!conditional.if.hasOwnProperty(group_index)) { continue; }

			var group = conditional.if[group_index];

			// Error check group
			if(typeof(group['conditions']) === 'undefined') { continue; }

			// Process conditions
			for(var condition_index in group['conditions']) {

				if(!group['conditions'].hasOwnProperty(condition_index)) { continue; }

				var condition = group['conditions'][condition_index];

				// Check integrity of condition
				if(!this.conditional_condition_check(condition)) { continue; }

				// Read condition
				var object = condition.object;
				var object_id = condition.object_id;
				var logic = condition.logic;
				var value = condition.value;

				// Create events
				var object_events = [];

				switch(object) {

					case 'form' :

						// Create event
						object_events.push(this.conditional_event_get(object, this.form_id, 0, this.form, logic));

						break;

					case 'group' :

						// Get group
						if(typeof(this.group_data_cache[object_id]) === 'undefined') { break; }
						var object_data = this.group_data_cache[object_id];

						// Create event
						object_events.push(this.conditional_event_get(object, object_id, false, object_data, logic));

						break;

					case 'section' :

						// Get section
						if(typeof(this.section_data_cache[object_id]) === 'undefined') { break; }
						var object_data = this.section_data_cache[object_id];

						// Create event
						object_events.push(this.conditional_event_get(object, object_id, false, object_data, logic));

						break;

					case 'field' :

						// Get field
						if(typeof(this.field_data_cache[object_id]) === 'undefined') { break; }
						var object_data = this.field_data_cache[object_id];
						var field_type = $.WS_Form.field_type_cache[object_data.type];

						// Get object row ID
						var object_row_id = this.get_object_row_id(condition);	// Array of integers

						// Determine if field is repeatable
						var field_repeatable = (typeof(object_data.section_repeatable_section_id) !== 'undefined');

						// Create event
						object_events.push(this.conditional_event_get(object, object_id, object_row_id, object_data, logic, field_repeatable));

						// Logic specific events
						var field_attribute_values = [];
						switch(logic) {

							case 'field_match' :
							case 'field_match_not' :

								// Find matching field
								var field_match_id = value;
								if(parseInt(field_match_id, 10) == 0) { break; }
								if(typeof(this.field_data_cache[field_match_id]) === 'undefined') { break; }
								var field_match = this.field_data_cache[field_match_id];

								// Determine if field is repeatable
								var field_repeatable = (typeof(field_match.section_repeatable_section_id) !== 'undefined');

								// Create event for matching field
								object_events.push(this.conditional_event_get('field', field_match_id, false, field_match, logic, field_repeatable));

								field_attribute_values['field_match_id'] = this.form_id_prefix + 'field-' + field_match_id;

								break;
						}

						// Check to see if any logic specific attributes should be added to the field
						var field_attributes = this.get_field_value_fallback(field_type, false, 'attribute_' + logic, false);
						if(typeof(field_attributes) === 'object') {

							var object_event_selector = '#' + this.form_id_prefix + object + '-' + object_id;

							for(var field_attribute_index in field_attributes) {

								if(!field_attributes.hasOwnProperty(field_attribute_index)) { continue; }

								var field_attribute_value = field_attributes[field_attribute_index];

								// Parse field_attribute_value
								field_attribute_value = this.mask_parse(field_attribute_value, field_attribute_values);

								// Set attribute in object
								$(object_event_selector, this.form_canvas_obj).attr(field_attribute_index, field_attribute_value);
							}
						}

						break;

					case 'submit' :

						// Create event
						object_events.push(this.conditional_event_get(object, 0, false, this.submit, logic));

						break;

					case 'user' :

						// Create event
						object_events.push(this.conditional_event_get(object, 0, false, false, logic));

						break;
				}

				// Process events
				for(var object_event_index in object_events) {

					if(!object_events.hasOwnProperty(object_event_index)) { continue; }

					var object_event = object_events[object_event_index];

					var object_event_selector = null;
					var object_event_repeatable = object_event.repeatable;

					// Build event selector
					if(
						(object_event.event == 'wsf-group-index') ||
						(object_event.event == 'wsf-section-repeatable') ||
						(object_event.event == 'wsf-validate') ||
						(object_event.event == 'wsf-validate-silent')
					) {

						if(!initial) { continue; }

						if(object_event.event == 'wsf-section-repeatable') {

							object_event.event = 'wsf-section-repeatable-' + object_id;
						}

						var object_event_obj = this.form_canvas_obj
						var object_event_selector = null;

					} else if(

						(object_event.event == 'wsf-rendered') ||
						(object_event.event == 'wsf-submit') ||
						(object_event.event == 'wsf-save') ||
						(object_event.event == 'wsf-submit wsf-save') ||
						(object_event.event == 'wsf-submit-complete') ||
						(object_event.event == 'wsf-save-complete') ||
						(object_event.event == 'wsf-complete') ||
						(object_event.event == 'wsf-submit-error') ||
						(object_event.event == 'wsf-save-error') ||
						(object_event.event == 'wsf-error') ||
						(object_event.event == 'wsf-submit-success') ||
						(object_event.event == 'wsf-save-success') ||
						(object_event.event == 'wsf-success')
					) {

						var object_event_obj = $(document);
						var object_event_selector = null;

					} else {

						var object_event_obj = this.form_canvas_obj

						switch(object_event.object) {

							case 'form' :

								var object_event_selector = null;
								break;

							case 'group' :

								var object_event_selector = '[' + selector_href + '="#' + this.form_id_prefix + 'group-' + object_id + '"]';
								break;

							case 'section' :

								var object_event_selector = '[id^="' + this.esc_selector(this.form_id_prefix + object_event.object + '-' + object_event.object_id) + '"][data-id="' + this.esc_selector(object_event.object_id) + '"]';
								break;

							case 'field' :

								// Get field type
								var field = this.field_data_cache[object_id];

								switch(field.type) {

									case 'radio' :
									case 'price_radio' :
									case 'checkbox' :
									case 'price_checkbox' :

										var object_event_selector = '[name^="' + this.esc_selector(ws_form_settings.field_prefix + object_id) + '["],[name^="' + this.esc_selector(ws_form_settings.field_prefix + object_id) + '["] ~ label';
										break;

									case 'select' :
									case 'price_select' :

										// Check for select2 mouse events
										var field_select2_mouse_event = this.get_object_meta_value(field, 'select2', false) && (

											[
												'click',
												'mousedown',
												'mouseup',
												'mouseover',
												'mouseout',
												'touchstart',
												'touchmove',
												'touchend',
												'touchcancel',

											].indexOf(object_event.event) !== -1
										);

										if(field_select2_mouse_event) {

											if(object_event_repeatable) {

												var object_event_selector = '[id^="' + this.esc_selector(this.form_id_prefix + object_event.object + '-wrapper-' + object_event.object_id) + '-repeat-"] .select2';

											} else {

												var object_event_selector = '#' + this.esc_selector(this.form_id_prefix + object_event.object + '-wrapper-' + object_event.object_id) + ' .select2';
											}

										} else {

											if(object_event_repeatable) {

												var object_event_selector = '[id^="' + this.esc_selector(this.form_id_prefix + object_event.object + '-' + object_event.object_id) + '-repeat-"]';

											} else {

												var object_event_selector = '#' + this.esc_selector(this.form_id_prefix + object_event.object + '-' + object_event.object_id);
											}
										}

										break;

									default :

										if(object_event_repeatable) {

											var object_event_selector = '[id^="' + this.esc_selector(this.form_id_prefix + object_event.object + '-' + object_event.object_id) + '-repeat-"]';

										} else {

											var object_event_selector = '#' + this.esc_selector(this.form_id_prefix + object_event.object + '-' + object_event.object_id);
										}
										break;
								}
								break;
						}
					}

					// Create event for this condition?
					if(
						object_event.create && 
						(object_event.event !== false) && 
						(object_event_selector !== false)
					) {

						var data_init_conditional_count = 0;

						// Get objects
						var objects = $(object_event_selector);

						objects.each(function() {

							if($(this).attr('data-init-conditional')) {

								data_init_conditional_count++;
							}
						});

						if(data_init_conditional_count === 0) {

							// Process event
							switch(object_event.event) {

								case 'recaptcha' :

									// Create reCAPTCHA event
									this.recaptchas_conditions.push(function() {

										ws_this.conditional_process(conditional, false, objects, 0, false);
									});
									break;

								case 'hcaptcha' :

									// Create hCaptcha event
									this.hcaptchas_conditions.push(function() {

										ws_this.conditional_process(conditional, false, objects, 0, false);
									});
									break;

								case 'turnstile' :

									// Create Turnstile event
									this.turnstiles_conditions.push(function() {

										ws_this.conditional_process(conditional, false, objects, 0, false);
									});
									break;

								default :

									if(object_event_selector === null) { object_event_selector = 'null'; }

									if(typeof(this.conditional_event_selector_to_condition[object_event_selector]) === 'undefined') {

										this.conditional_event_selector_to_condition[object_event_selector] = [];
									}

									if(typeof(this.conditional_event_selector_to_condition[object_event_selector][object_event.event]) === 'undefined') {

										this.conditional_event_selector_to_condition[object_event_selector][object_event.event] = {

											object_event_selector: object_event_selector,
											object_event_obj: object_event_obj,
											conditionals: [],
											event_processed: false
										};
									}

									if(this.conditional_event_selector_to_condition[object_event_selector][object_event.event].conditionals.indexOf(conditional) === -1) {

										this.conditional_event_selector_to_condition[object_event_selector][object_event.event].conditionals.push(conditional);
									}

									// Add to object (For debug)
									if(ws_form_settings.debug) {

										var data_populate_event = $(object_event_selector, this.form_canvas_obj).attr('data-populate-event');
										var data_populate_event_array = data_populate_event ? data_populate_event.split(' ') : [];
										data_populate_event_array.push(object_event.event);
										data_populate_event_array = data_populate_event_array.filter(function(value, index, self) { 
											return self.indexOf(value) === index;
										});
										data_populate_event = data_populate_event_array.join(' ');
										$(object_event_selector, this.form_canvas_obj).attr('data-populate-event', data_populate_event);
									}
							}
						}
					}
				}
			}
		}
	}

	// Form - Get conditional event
	$.WS_Form.prototype.conditional_event_get = function(object, object_id, object_row_id, field, logic, repeatable) {

		if(typeof(repeatable) === 'undefined') { repeatable = false; }

		var object_event = {

			'object':			object,
			'object_id':		object_id,
			'object_row_id':	object_row_id,	// Array of integers
			'create':			true,
			'event':			false,
			'row':				false,
			'repeatable':		repeatable, 
		};

		switch(object) {

			case 'field' :

				var field_type = $.WS_Form.field_type_cache[field.type];

				// Are there conditional settings?
				if(typeof(field_type['conditional']) !== 'undefined') {

					// Should this field type be excluded?
					if(typeof(field_type['conditional']['exclude_condition']) !== 'undefined') {

						object_event.create = !field_type['conditional']['exclude_condition'];
					}

					// Which event type should be created?
					if(typeof(field_type['conditional']['condition_event']) !== 'undefined') {

						object_event.event = field_type['conditional']['condition_event'];
					}

					// Should event occur on row?
					if(typeof(field_type['conditional']['object_event.row']) !== 'undefined') {

						if(field_type['conditional']['object_event.row'] && (object_row_id !== false)) {

							object_event.row = true;
						};
					}
				}

				break;
		}

		// Check for logic event
		var conditional_settings = $.WS_Form.settings_form.conditional;
		var conditional_settings_objects = conditional_settings.objects;
		var conditional_settings_logics = conditional_settings_objects[object]['logic'];

		if(typeof(conditional_settings_logics[logic]) !== 'undefined') {

			if(typeof(conditional_settings_logics[logic]['event']) !== 'undefined') {

				object_event.event = conditional_settings_logics[logic]['event'];
			}
		}

		return object_event;
	}

	// Form - Process client side conditional
	$.WS_Form.prototype.conditional_process = function(conditional, initial, source_obj, source_repeatable_index, event) {

	    // Check conditional
	    if(typeof(conditional.if) === 'undefined') { return false; }
	    if(typeof(conditional.then) === 'undefined') { return false; }
	    if(typeof(conditional.else) === 'undefined') { return false; }

		// Process groups
		var result_conditions = false;
		for(var group_index in conditional.if) {

			if(!conditional.if.hasOwnProperty(group_index)) { continue; }

			var group = conditional.if[group_index];

			// Error check group
			if(typeof(group['conditions']) === 'undefined') { continue; }

			// Process conditions
			var result_group = false;
			var conditional_description_array = [];
			for(var condition_index in group['conditions']) {

				if(!group['conditions'].hasOwnProperty(condition_index)) { continue; }

				var condition = group['conditions'][condition_index];

				// Check integrity of condition
				if(!this.conditional_condition_check(condition)) { continue; }

				// Read condition data
				var object = condition.object;
				var object_id = condition.object_id;
				var logic = condition.logic;
				var value = condition.value;
				var case_sensitive = (typeof(condition.case_sensitive) === 'undefined') ? true : condition.case_sensitive;
				var logic_previous = (condition_index > 0) ? ((typeof(condition.logic_previous) === 'undefined') ? '||' : condition.logic_previous) : '||';
				var field_name = ws_form_settings.field_prefix + object_id;
				var force_result = (typeof(condition.force_result) === 'undefined') ? null : condition.force_result;

				// Get object(s) to check
				switch(object) {

					case 'form' :

						var object_row_id = false;
						var object_event_selector = '#' + this.esc_selector(this.form_obj_id);
						break;

					case 'group' :

						var object_row_id = false;
						var object_event_selector = '#' + this.esc_selector(this.form_id_prefix + 'group-' + object_id);
						break;

					case 'section' :

						var object_row_id = false;
						var object_event_selector = '#' + this.esc_selector(this.form_id_prefix + object + '-' + object_id);
						break;

					case 'field' :

						var object_row_id = this.get_object_row_id(condition);	// Array of integers

						// If source of the event is not in a repeatable section and the condition contains at least one field in a repeaters, then we cannot assess this condition properly and should skip it.
						// This might occur if for example user role is being checked along side field values. User role conditions will fire on wsf-rendered but do not relate to a repeatable section.
						if(!source_repeatable_index) {

							// Get field object
							var field_obj = this.field_data_cache[object_id];

							if(
								(typeof(field_obj) === 'object') &&
								(typeof(field_obj.section_id) !== 'undefined') &&
								(typeof(this.section_data_cache[field_obj.section_id]) === 'object')
							) {
								
								// Get section object
								var section_obj = this.section_data_cache[field_obj.section_id];

								// Get whether section is repeatable
								var section_repeatable = this.get_object_meta_value(section_obj, 'section_repeatable');

								if(section_repeatable) {

									return;
								}
							}
						}

						// Get repeatable suffix
						var repeatable_suffix = (source_repeatable_index > 0) ? '-repeat-' + source_repeatable_index : '';

						var object_event_selector = '#' + this.esc_selector(this.form_id_prefix + object + '-' + object_id + repeatable_suffix);
						var object_wrapper = $('[data-type][data-id="' + this.esc_selector(object_id) + '"]' + ((source_repeatable_index > 0) ? '[data-repeatable-index="' + this.esc_selector(source_repeatable_index) + '"]' : ''), this.form_canvas_obj);
				}

				// Debug
				if($.WS_Form.debug_rendered) {

					// Build conditional description for debug
					switch(object) {

						case 'form' :

							var object_single = this.form;
							break;

						case 'group' :

							var object_single = this.group_data_cache[object_id];
							break;

						case 'section' :

							var object_single = this.section_data_cache[object_id];
							break;

						case 'field' :

							var object_single = this.field_data_cache[object_id];
							break;
					}

					if(typeof(object_single) !== 'undefined') {

						var conditional_settings = $.WS_Form.settings_form.conditional;
						var conditional_settings_logic_previous = conditional_settings.logic_previous;

						var conditional_settings_objects = conditional_settings.objects;
						var conditional_settings_logics = conditional_settings_objects[object]['logic'];

						// Get logic description
						if(typeof(conditional_settings_logics[logic]) !== 'undefined') {

							var logic_description = conditional_settings_logics[logic].text.toUpperCase();

						} else {

							var logic_description = '';
						}

						// Get logic previous description
						if(typeof(conditional_settings_logic_previous[logic_previous]) !== 'undefined') {

							var logic_previous_description = conditional_settings_logic_previous[logic_previous].text.toUpperCase();

						} else {

							var logic_previous_description = '';
						}

						conditional_description_array.push('<strong>' + (condition_index > 0 ? logic_previous_description + ' ' : '') + '[' + this.esc_html(object_single.label) + '] ' + logic_description + ((value != '') ? " '" + this.esc_html(value) + "'" : '') + '</strong> (' + ((object_single.type !== undefined) ? ('Type: ' + object_single.type + ' | ') : '') + 'ID: ' + object_id + ((object_row_id !== false) ? ' | Row ID: ' + object_row_id.join(', ') : '') + ')');
					}
				}

				// Get value
				var object_obj = $(object_event_selector, this.form_canvas_obj);
				var object_value = (object_obj.length) ? object_obj.val() : '';

				// Case sensitive
				if(!case_sensitive) {

					if(object_value && (typeof(object_value) === 'string')) {

						object_value = object_value.toLowerCase();
					}

					if(value && (typeof(value) === 'string')) {

						value = value.toLowerCase();
					}
				}

				// Section repeatable row count
				var logic_section_repeatable = ['r==', 'r!=', 'r>', 'r<', 'r>=', 'r<='];
				if(logic_section_repeatable.indexOf(logic) !== -1) {

					// Set object value to the section count
					var sections = $('[data-repeatable][data-id="' + this.esc_selector(object_id) + '"]', this.form_canvas_obj);
					if(!sections.length) { continue; }
					object_value = sections.length;
				}

				// Parse value
				var value = this.parse_variables_process(value).output;

				// Pre-processing
				switch(logic) {

					// Date processing
					case 'd==' :
					case 'd!=' :
					case 'd>' :
					case 'd<' :
					case 'd>=' :
					case 'd<=' :

						// Convert input to JS date
						var field_from = (typeof(this.field_data_cache[object_id]) !== 'undefined') ? this.field_data_cache[object_id] : false;
						if(field_from !== false) {

							var input_type_datetime = this.get_object_meta_value(field_from, 'input_type_datetime', 'date');
							var format_date = this.get_object_meta_value(field_from, 'format_date', ws_form_settings.date_format);
							if(!format_date) { format_date = ws_form_settings.date_format; }

							value = this.get_date(value, input_type_datetime, format_date);

							object_value = this.get_date(object_value, input_type_datetime, format_date);
						}

						break;

					// Number processing
					case '==' :
					case '!=' :
					case '>' :
					case '<' :
					case '>=' :
					case '<=' :

						// Convert input to floating point number
						var field_from = (typeof(this.field_data_cache[object_id]) !== 'undefined') ? this.field_data_cache[object_id] : false;
						if(field_from !== false) {

							switch(field_from.type) {

								case 'price' :
								case 'price_subtotal' :
								case 'cart_price' :
								case 'cart_total' :

									// Get floating point number from price
									object_value = this.get_number(object_value, 0, true);
									break;

								default :

									// Get floating point number from regular number
									object_value = this.get_number(object_value, 0, false);
									break;
							}

							value = parseFloat(value);
						}
				}

				var result_condition = false; 

				switch(logic) {

					// Number equals (Exact)
					case '==' :

						result_condition = (object_value == value);
						break;

					// Number does not equal
					case '!=' :

						result_condition = (object_value != value);
						break;

					// Greater than
					case '>' :

						result_condition = (object_value > value);
						break;

					// Less than
					case '<' :

						result_condition = (object_value < value);
						break;

					// Greater than or equal to
					case '>=' :

						result_condition = (object_value >= value);
						break;

					// Less than or equal to
					case '<=' :

						result_condition = (object_value <= value);
						break;

					// Equals (Date)
					case 'd==' :

						if((object_value != '') && (value != '')) {

							result_condition = (Date.parse(object_value) == Date.parse(value));
						}
						break;

					// Not equals (Date)
					case 'd!=' :

						if((object_value != '') && (value != '')) {

							result_condition = (Date.parse(object_value) != Date.parse(value));
						}
						break;

					// Greater than (Date)
					case 'd>' :

						if((object_value != '') && (value != '')) {

							result_condition = (Date.parse(object_value) > Date.parse(value));
						}
						break;

					// Less than (Date)
					case 'd<' :

						if((object_value != '') && (value != '')) {

							result_condition = (Date.parse(object_value) < Date.parse(value));
						}
						break;

					// Greater than or equal to (Date)
					case 'd>=' :

						if((object_value != '') && (value != '')) {

							result_condition = (Date.parse(object_value) >= Date.parse(value));
						}
						break;

					// Less than or equal to (Date)
					case 'd<=' :

						if((object_value != '') && (value != '')) {

							result_condition = (Date.parse(object_value) <= Date.parse(value));
						}
						break;

					// Checkbox count equals / not equals
					case 'rc==' :
					case 'rc!=' :

						var object_value = $('input[type="checkbox"]:not([data-wsf-select-all]):checked', object_wrapper).length;
						result_condition = (object_value == this.get_number(value));
						if(logic == 'rc!=') { result_condition = !result_condition; }
						break;

					// Checkbox count greater than
					case 'rc>' :

						var object_value = $('input[type="checkbox"]:not([data-wsf-select-all]):checked', object_wrapper).length;
						result_condition = (object_value > this.get_number(value));
						break;

					// Checkbox count less than
					case 'rc<' :

						var object_value = $('input[type="checkbox"]:not([data-wsf-select-all]):checked', object_wrapper).length;
						result_condition = (object_value < this.get_number(value));
						break;

					// Selected count equals / not equals
					case 'rs==' :
					case 'rs!=' :

						var object_value = $('option:not([data-placeholder]):selected', object_wrapper).length;
						result_condition = (object_value == this.get_number(value));
						if(logic == 'rc!=') { result_condition = !result_condition; }
						break;

					// Selected count greater than
					case 'rs>' :

						var object_value = $('option:not([data-placeholder]):selected', object_wrapper).length;
						result_condition = (object_value > this.get_number(value));
						break;

					// Selected count less than
					case 'rs<' :

						var object_value = $('option:not([data-placeholder]):selected', object_wrapper).length;
						result_condition = (object_value < this.get_number(value));
						break;

					// Section row count equals
					case 'r==' :

						result_condition = (this.get_number(object_value) == this.get_number(value));
						break;

					// Section row count does not equal
					case 'r!=' :

						result_condition = (this.get_number(object_value) != this.get_number(value));
						break;

					// Section row count greater than
					case 'r>' :

						if(object_value != '') {
							result_condition = (this.get_number(object_value) > this.get_number(value));
						}
						break;

					// Section row count less than
					case 'r<' :

						if(object_value != '') {
							result_condition = (this.get_number(object_value) < this.get_number(value));
						}
						break;

					// Section row count greater than or equal to
					case 'r>=' :

						if(object_value != '') {
							result_condition = (this.get_number(object_value) >= this.get_number(value));
						}
						break;

					// Section row count less than or equal to
					case 'r<=' :

						if(object_value != '') {
							result_condition = (this.get_number(object_value) <= this.get_number(value));
						}
						break;

					// Section repeatable changes
					case 'section_repeatable' :

						conditional.last_state[source_repeatable_index] = '';
						result_condition = (event.type == 'wsf-section-repeatable-' + object_id);
						break;

					// String equals
					case 'equals' :
					case 'c==' :

						result_condition = (object_value == value);
						break;

					// String does not equal
					case 'equals_not' :
					case 'c!=' :

						result_condition = (object_value != value);
						break;

					// Contains
					case 'contains' :
					case 'contains_not' :

						result_condition = (object_value.indexOf(value) != -1);
						if(logic == 'contains_not') { result_condition = !result_condition; }
						break;

					// Starts with
					case 'starts' :
					case 'starts_not' :

						result_condition = object_value.startsWith(value);
						if(logic == 'starts_not') { result_condition = !result_condition; }
						break;

					// Ends with
					case 'ends' :
					case 'ends_not' :

						result_condition = object_value.endsWith(value);
						if(logic == 'ends_not') { result_condition = !result_condition; }
						break;

					// Is blank
					case 'blank' :
					case 'blank_not' :

						result_condition = (object_value == '');
						if(logic == 'blank_not') { result_condition = !result_condition; }
						break;

					// Checked
					case 'checked' :
					case 'checked_not' :

						var result_condition = false;

						if(typeof(object_row_id) !== 'object') { break; }

						for(var object_row_id_index in object_row_id) {

							if(!object_row_id.hasOwnProperty(object_row_id_index)) { continue; }

							var object_row_id_single = object_row_id[object_row_id_index];

							var object_event_selector_obj = '#' + this.esc_selector(this.form_id_prefix + object + '-' + object_id + '-row-' + object_row_id_single + repeatable_suffix);

							var object_row_id_selected = $(object_event_selector_obj, this.form_canvas_obj).is(':checked');

							result_condition = result_condition | object_row_id_selected;
						}

						if(logic == 'checked_not') { result_condition = !result_condition; }

						break;

					// Checked - Any
					case 'checked_any' :
					case 'checked_any_not' :

						result_condition = ($('input:checked', object_wrapper).length > 0);
						if(logic == 'checked_any_not') { result_condition = !result_condition; }
						break;

					// Checked - All
					case 'checked_all' :
					case 'checked_all_not' :

						var inputs = $('input:not([disabled])', object_wrapper);
						var inputs_checked = inputs.filter(':checked');
						var result_condition = (inputs.length == inputs_checked.length);
						if(logic == 'checked_all_not') { result_condition = !result_condition; }
						break;

					// Checked value
					case 'checked_value_equals' :
					case 'checked_value_equals_not' :

						$('input:checked', object_wrapper).each(function() {

							var input_value = $(this).val();

							if(!case_sensitive) { input_value = input_value.toLowerCase(); }

							if(input_value === value) {

								result_condition = true;
								return false;
							}
						});

						if(logic == 'checked_value_equals_not') { result_condition = !result_condition; }

						break;

					// Selected
					case 'selected' :
					case 'selected_not' :

						var result_condition = false;

						if(typeof(object_row_id) !== 'object') { break; }

						for(var object_row_id_index in object_row_id) {

							if(!object_row_id.hasOwnProperty(object_row_id_index)) { continue; }

							var object_row_id_single = object_row_id[object_row_id_index];

							var object_row_id_selected = $('option[data-id="' + this.esc_selector(object_row_id_single) + '"]', $(object_event_selector, this.form_canvas_obj)).prop('selected');

							result_condition = result_condition | object_row_id_selected;
						}

						if(logic == 'selected_not') { result_condition = !result_condition; }

						break;

					// Selected - Any
					case 'selected_any' :
					case 'selected_any_not' :

						var result_condition = ($('option:not([data-placeholder]):selected', $(object_event_selector, this.form_canvas_obj)).length > 0);
						if(logic == 'selected_any_not') { result_condition = !result_condition; }
						break;

					// Selected - All
					case 'selected_all' :
					case 'selected_all_not' :

						var options = $('option:not([data-placeholder],[disabled])', $(object_event_selector, this.form_canvas_obj));
						var options_selected = options.filter(':selected');
						var result_condition = (options.length == options_selected.length);
						if(logic == 'selected_all_not') { result_condition = !result_condition; }
						break;

					// Selected value
					case 'selected_value_equals' :
					case 'selected_value_equals_not' :

						$('option:selected', $(object_event_selector, this.form_canvas_obj)).each(function() {

							var option_value = $(this).val();

							if(!case_sensitive) { option_value = option_value.toLowerCase(); }

							if(option_value === value) {

								result_condition = true;
								return false;
							}
						});

						if(logic == 'selected_value_equals_not') { result_condition = !result_condition; }

						break;

					// Regex - Email
					case 'regex_email' :
					case 'regex_email_not' :

						var pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
						result_condition = pattern.test(object_value);
						if(logic == 'regex_email_not') { result_condition = !result_condition; }
						break;

					// Regex - URL
					case 'regex_url' :
					case 'regex_url_not' :

						var pattern = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
						result_condition = pattern.test(object_value);
						if(logic == 'regex_url_not') { result_condition = !result_condition; }
						break;

					// Matches regex
					case 'regex' :
					case 'regex_not' :

						var pattern = new RegExp(value);
						result_condition = pattern.test(object_value);
						if(logic == 'regex_not') { result_condition = !result_condition; }
						break;

					// Events - WS Form
					case 'wsf-rendered' :

					case 'wsf-submit' :
					case 'wsf-submit-complete' :
					case 'wsf-submit-success' :
					case 'wsf-submit-error' :

					case 'wsf-save' :
					case 'wsf-save-complete' :
					case 'wsf-save-success' :
					case 'wsf-save-error' :

					case 'wsf-submit-save' :
					case 'wsf-complete' :
					case 'wsf-success' :
					case 'wsf-error' :

						// Determine result condition
						result_condition = (

							// Single event triggers
							(logic == event.type) ||	// WS Form triggered events

							// Multiple event triggers
							((logic == 'wsf-submit-save') && (['wsf-submit', 'wsf-save'].indexOf(event.type) != -1))
						);

						if(result_condition) {

							conditional.last_state[source_repeatable_index] = '';
						}

						break;

					// Events - User
					case 'click' :

					case 'mousedown' :
					case 'mouseup' :
					case 'mouseover' :
					case 'mouseout' :

					case 'touchstart' :
					case 'touchmove' :
					case 'touchend' :
					case 'touchcancel' :

					case 'focus' :
					case 'blur' :
					case 'change' :
					case 'change_input' :
					case 'input' :

					case 'keyup' :
					case 'keydown' :

						// Ensure event originated from this conditions object ID
						if(
							!event ||
							!event.target
						) {
							result_condition = false;
							break;
						}

						var obj_src = $(event.target);

						var object_id_match = false;

						switch(object) {

							case 'form' :

								object_id_match = true;
								break;

							case 'group' :

								object_id_match = (this.get_group_id(obj_src) == object_id);
								break;

							case 'section' :

								object_id_match = (this.get_section_id(obj_src) == object_id);
								break;

							case 'field' :

								object_id_match = (this.get_field_id(obj_src) == object_id);
								break;
						}

						// Determine result condition
						result_condition = object_id_match && (

							// Single event triggers
							(logic == event.type) ||			// User triggered events
							(
								(typeof(event.handleObj) !== 'undefined') &&
								(typeof(event.handleObj.origType) !== 'undefined') &&
								(logic == event.handleObj.origType)
							) ||

							// Multi-event triggers
							((logic == 'change_input') && (['change', 'input'].indexOf(event.type) != -1))
						);

						if(result_condition) {

							conditional.last_state[source_repeatable_index] = '';
						}

						break;

					// Token validation
					case 'token_validated' :
					case 'token_validated_not' :

						result_condition = (this.submit !== false) && (typeof(this.submit.token_validated) !== 'undefined') && this.submit.token_validated;
						if(logic == 'token_validated_not') { result_condition = !result_condition; }
						break;

					// User
					case 'user_logged_in' :
					case 'user_logged_in_not' :
					case 'user_role' :
					case 'user_role_not' :
					case 'user_capability' :
					case 'user_capability_not' :

						result_condition = force_result;
						break;

					// Color - Hue greater than
					case 'ch>' :
					case 'ch<' :
					case 'cs>' :
					case 'cs<' :
					case 'cl>' :
					case 'cl<' :
					case 'ca>' :
					case 'ca<' :

						if(typeof(this.color_to_hsla_array) !== 'function') {

							result_condition = false;
							break;
						}

						// Convert color to HSLA array
						var hsla_array = this.color_to_hsla_array(object_value);

						switch(logic) {

							// Color - Hue greater than
							case 'ch>' :

								result_condition = (hsla_array.h > value);
								break;

							// Color - Hue less than
							case 'ch<' :

								result_condition = (hsla_array.h < value);
								break;

							// Color - Saturation greater than
							case 'cs>' :

								result_condition = (hsla_array.s > value);
								break;

							// Color - Saturation less than
							case 'cs<' :

								result_condition = (hsla_array.s < value);
								break;

							// Color - Lightness greater than
							case 'cl>' :

								result_condition = (hsla_array.l > value);
								break;

							// Color - Lightness less than
							case 'cl<' :

								result_condition = (hsla_array.l < value);
								break;

							// Color - Alpha greater than
							case 'ca>' :

								result_condition = (hsla_array.a > value);
								break;

							// Color - Alpha less than
							case 'ca<' :

								result_condition = (hsla_array.a < value);
								break;
						}
					
						break;

					// Check reCAPTCHA is valid
					case 'recaptcha' :
					case 'recaptcha_not' :

						if(typeof(this.recaptcha_get_response_by_name)) {

							var captcha_get_response = this.recaptcha_get_response_by_name(field_name);
							result_condition = (typeof(captcha_get_response) === 'string') && (captcha_get_response.length > 0);
							if(logic == 'recaptcha_not') { result_condition = !result_condition; }
						}
						break;

					// Check hCaptcha is valid
					case 'hcaptcha' :
					case 'hcaptcha_not' :

						if(typeof(this.hcaptcha_get_response_by_name)) {

							var captcha_get_response = this.hcaptcha_get_response_by_name(field_name);
							result_condition = (typeof(captcha_get_response) === 'string') && (captcha_get_response.length > 0);
							if(logic == 'hcaptcha_not') { result_condition = !result_condition; }
						}
						break;

					// Check turnstile is valid
					case 'turnstile' :
					case 'turnstile_not' :

						if(typeof(this.turnstile_get_response_by_name)) {

							var captcha_get_response = this.turnstile_get_response_by_name(field_name);
							result_condition = (typeof(captcha_get_response) === 'string') && (captcha_get_response.length > 0);
							if(logic == 'turnstile_not') { result_condition = !result_condition; }
						}
						break;

					// Check signature is valid
					case 'signature' :
					case 'signature_not' :

						if(typeof(this.signature_get_response_by_name)) {

							result_condition = this.signature_get_response_by_name(field_name);
							if(logic == 'signature_not') { result_condition = !result_condition; }
						}
						break;

					// Check file is valid
					case 'file' :
					case 'file_not' :

						if(typeof(this.file_get_count_by_field_id)) {

							result_condition = (this.file_get_count_by_field_id(object_id) > 0);
							if(logic == 'file_not') { result_condition = !result_condition; }
						}
						break;

					// File count greater than
					case 'f>' :

						if(typeof(this.file_get_count_by_field_id)) {

							result_condition = (this.file_get_count_by_field_id(object_id) > value);
						}
						break;

					// Files count less than
					case 'f<' :

						if(typeof(this.file_get_count_by_field_id)) {

							result_condition = (this.file_get_count_by_field_id(object_id) < value);
						}
						break;

					// Files less than
					case 'f==' :
					case 'f!=' :

						if(typeof(this.file_get_count_by_field_id)) {

							result_condition = (this.file_get_count_by_field_id(object_id) == value);
							if(logic == 'f!=') { result_condition = !result_condition; }
						}
						break;

					// Check fields match
					case 'field_match' :
					case 'field_match_not' :

						var field_id = parseInt(value, 10);
						if(field_id > 0) {

							var field_selector = '#' + this.form_id_prefix + 'field-' + field_id + ((source_repeatable_index > 0) ? '-repeat-' + source_repeatable_index : '');
							var field_value = $(field_selector, this.form_canvas_obj).val();

							result_condition = (field_value == object_value);
							if(logic == 'field_match_not') { result_condition = !result_condition; }
						}
						break;

					// Character count equals (Exact)
					case 'cc==' :

						result_condition = (object_value.length == parseInt(value, 10));
						break;

					// Character count does not equal
					case 'cc!=' :

						result_condition = (object_value.length != parseInt(value, 10));
						break;

					// Character count greater than
					case 'cc>' :

						result_condition = (object_value.length > parseInt(value, 10));
						break;

					// Character count less than
					case 'cc<' :

						result_condition = (object_value.length < parseInt(value, 10));
						break;

					// Word count equals (Exact)
					case 'cw==' :

						result_condition = (this.get_word_count(object_value) == parseInt(value, 10));
						break;

					// Word count does not equal
					case 'cw!=' :

						result_condition = (this.get_word_count(object_value) != parseInt(value, 10));
						break;

					// Word count greater than
					case 'cw>' :

						result_condition = (this.get_word_count(object_value) > parseInt(value, 10));
						break;

					// Word count less than
					case 'cw<' :

						result_condition = (this.get_word_count(object_value) < parseInt(value, 10));
						break;

					// Select2 AJAX results
					case 'select2_ajax_results' :
					case 'select2_ajax_results_not' :

						if(initial) { return; }
						conditional.last_state[source_repeatable_index] = '';
						result_condition = (typeof($(object_event_selector, this.form_canvas_obj).attr('data-select2-ajax-results')) !== 'undefined');
						if(logic == 'select2_ajax_results_not') { result_condition = !result_condition; }

						break;

					// Google route - Status: OK
					case 'google_route_status_ok' :

						if(initial) { return; }
						conditional.last_state[source_repeatable_index] = '';
						result_condition = (typeof($(object_event_selector, this.form_canvas_obj).attr('data-google-route-status')) !== 'undefined') && ($(object_event_selector, this.form_canvas_obj).attr('data-google-route-status') == 'OK');

						break;

					// Google route - Status: ZERO_RESULTS
					case 'google_route_status_zero_results' :

						if(initial) { return; }
						conditional.last_state[source_repeatable_index] = '';
						result_condition = (typeof($(object_event_selector, this.form_canvas_obj).attr('data-google-route-status')) !== 'undefined') && ($(object_event_selector, this.form_canvas_obj).attr('data-google-route-status') == 'ZERO_RESULTS');

						break;

					// Group - Active
					case 'active' :
					case 'active_not' :

						result_condition = ($(object_event_selector, this.form_canvas_obj).attr('data-group-index') == this.group_index);
						if(logic == 'active_not') { result_condition = !result_condition; }

						break;

					// Form - Validates
					case 'validate' :
					case 'validate_not' :

						switch(object) {

							case 'form' :

								result_condition = this.form_valid;
								break;

							case 'group' :

								result_condition = this.object_validate($(object_event_selector, this.form_canvas_obj));
								break;

							case 'section' :

								result_condition = this.object_validate($(object_event_selector, this.form_canvas_obj));
								break;

							case 'field' :

								result_condition = this.object_validate(object_wrapper);
								break;
						}

						if(logic == 'validate_not') { result_condition = !result_condition; }
						break;
				}

				// Add condition result to group result
				result_group = this.conditional_logic_previous(result_group, result_condition, logic_previous);
			}

			// Group logic
			var logic_previous = (group_index > 0) ? ((typeof(group.logic_previous) === 'undefined') ? '||' : group.logic_previous) : '||';

			// Add group result to conditions result
			result_conditions = this.conditional_logic_previous(result_conditions, result_group, logic_previous);
		}

		var condition_process_thenelse = false;
		if(result_conditions) {

			// Ensure 'then' fires once
			if(
				(conditional.last_state[source_repeatable_index] !== true) ||
				(typeof(conditional.last_state[source_repeatable_index]) === 'undefined')
			) {

				condition_process_thenelse = 'then';
				conditional.last_state[source_repeatable_index] = true;
			}

		} else {

			// Ensure 'else' fires once
			if(
				(conditional.last_state[source_repeatable_index] !== false) ||
				(typeof(conditional.last_state[source_repeatable_index]) === 'undefined')
			) {

				condition_process_thenelse = 'else';
				conditional.last_state[source_repeatable_index] = false;
			}
		}

		if(condition_process_thenelse !== false) {

			var actions = conditional[condition_process_thenelse];

			if(actions.length) {

				// Log
				this.log('log_conditional_fired_' + condition_process_thenelse, '(' + this.esc_html(conditional.label) + ') ' + conditional_description_array.join(' '), 'conditional');

				// Process action
				this.conditional_process_actions(actions, condition_process_thenelse, source_obj, source_repeatable_index);
			}
		}

		// Recalculate e-commerce
		if(
			!initial &&
			this.has_ecommerce &&
			(typeof(this.form_ecommerce_calculate) === 'function')
		) {
			this.form_ecommerce_calculate();
		}
	}

	$.WS_Form.prototype.conditional_process_actions = function(actions, action_then_else, source_obj, source_repeatable_index) {

		var actions_processed = 0;
		var process_required = false;
		var process_bypass = false;
		var process_navigation = false;
		var ws_this = this;

		for(var action_index in actions) {

			if(!actions.hasOwnProperty(action_index)) { continue; }

			var action_single = actions[action_index];

			// Check integrity of action
			if(!this.conditional_action_check(action_single)) { continue; }

			// Read action data
			var destination_object = action_single['object'];
			var destination_object_id = action_single['object_id'];
			var destination_object_row_id = this.get_object_row_id(action_single);	// Array of integers
			var destination_action = action_single['action'];

			// Process by object type
			switch(destination_object) {

				case 'form' :

					// Get object wrapper
					var destination_obj_wrapper = ws_this.form_obj;

					// Get object
					var destination_obj = ws_this.form_obj;

					// Get value parsed
					var destination_value = (typeof(action_single['value']) === 'undefined') ? false : this.parse_variables_process(action_single['value'], false, false, false, false, false).output;

					// Process action
					var conditional_process_action_return = this.conditional_process_action(action_then_else, destination_action, destination_obj_wrapper, destination_obj, destination_object, destination_object_id, false, destination_value, false);
					process_required = process_required || conditional_process_action_return.process_required;
					process_bypass = process_bypass || conditional_process_action_return.process_bypass;
					process_navigation = process_navigation || conditional_process_action_return.process_navigation;

					break;

				case 'group' :

					// Build group selector
					var destination_selector = '#' + this.esc_selector(this.form_id_prefix + 'group-' + destination_object_id);

					// Get object wrapper and object
					var destination_obj_wrapper = destination_obj = $(destination_selector, this.form_canvas_obj);

					// Get value parsed
					var destination_value = (typeof(action_single['value']) === 'undefined') ? false : this.parse_variables_process(action_single['value'], false, false, false, false, false).output;

					// Process action
					var conditional_process_action_return =  this.conditional_process_action(action_then_else, destination_action, destination_obj_wrapper, destination_obj, destination_object, destination_object_id, false, destination_value, false);
					process_required = process_required || conditional_process_action_return.process_required;
					process_bypass = process_bypass || conditional_process_action_return.process_bypass;
					process_navigation = process_navigation || conditional_process_action_return.process_navigation;

					break;

				case 'section' :

					// Get source section ID
					var source_section_id = this.get_section_id(source_obj);

					// Get all instances of the destination section
					var destination_wrappers = $('[id^="' + this.esc_selector(this.form_id_prefix) + 'section-"][data-id="' + this.esc_selector(destination_object_id) + '"]', this.form_canvas_obj);
					if(!destination_wrappers.length) { break; }
					var destination_wrapper_first = destination_wrappers.first();

					// Same section?
					if(source_section_id === parseInt(destination_object_id, 10)) {

						// Section is repeatable?
						if(destination_wrapper_first.attr('data-repeatable-index')) {

							// Filter by repeatable index
							destination_wrappers = destination_wrappers.filter('[data-repeatable-index="' + this.esc_selector(source_repeatable_index) + '"]');
						}
					}					

					destination_wrappers.each(function() {

						// Get destination repeatable index (This is used to localize the conditional_process_action)
						var destination_repeatable_index = ((typeof($(this).attr('data-repeatable-index')) !== 'undefined') ? $(this).attr('data-repeatable-index') : false);

						// Get value parsed
						var destination_value = (typeof(action_single['value']) === 'undefined') ? false : ws_this.parse_variables_process(action_single['value'], destination_repeatable_index, false, false, false, false, false).output;

						// Process action
						var conditional_process_action_return = ws_this.conditional_process_action(action_then_else, destination_action, $(this), $(this), destination_object, destination_object_id, false, destination_value, destination_repeatable_index);
						process_required = process_required || conditional_process_action_return.process_required;
						process_bypass = process_bypass || conditional_process_action_return.process_bypass;
						process_navigation = process_navigation || conditional_process_action_return.process_navigation;
					});

					break;

				case 'field' :

					// Get source section ID
					var source_section_id = this.get_section_id(source_obj);

					// Get all instances of the destination field
					var destination_wrappers = $('[id^="' + this.esc_selector(this.form_id_prefix) + 'field-wrapper-"][data-id="' + this.esc_selector(destination_object_id) + '"],input[type="hidden"][data-id-hidden="' + this.esc_selector(destination_object_id) + '"]', this.form_canvas_obj);
					if(!destination_wrappers.length) { break; }
					var destination_wrapper_first = destination_wrappers.first();

					// Get destination section ID
					var destination_section_id = this.get_section_id(destination_wrapper_first.first());

					// Same section?
					if(source_section_id === destination_section_id) {

						// Section is repeatable?
						if(destination_wrapper_first.attr('data-repeatable-index')) {

							// Filter by repeatable index
							destination_wrappers = destination_wrappers.filter('[data-repeatable-index="' + this.esc_selector(source_repeatable_index) + '"]');
						}
					}					

					destination_wrappers.each(function() {

						// Get destination repeatable index (This is used to localize the conditional_process_action)
						var destination_repeatable_index = ((typeof($(this).attr('data-repeatable-index')) !== 'undefined') ? $(this).attr('data-repeatable-index') : false);

						// Get destination repeatable suffix
						var destination_repeatable_suffix = (destination_repeatable_index !== false) ? '-repeat-' + destination_repeatable_index : '';

						// Build destination selector

						// Check for multiple rows
						if(typeof(destination_object_row_id) == 'object') {

							var destination_selector_array = [];

							for(var destination_object_row_id_index in destination_object_row_id) {

								if(!destination_object_row_id.hasOwnProperty(destination_object_row_id_index)) { continue; }

								var row_id = destination_object_row_id[destination_object_row_id_index];

								destination_selector_array.push('#' + ws_this.esc_selector(ws_this.form_id_prefix + 'field-' + destination_object_id + '-row-' + row_id + destination_repeatable_suffix));
							}

							var destination_selector = destination_selector_array.join(', ');

						} else {

							var destination_selector = '#' + ws_this.esc_selector(ws_this.form_id_prefix + 'field-' + destination_object_id + destination_repeatable_suffix);
						}

						var destination_obj = $(destination_selector, ws_this.form_canvas_obj);

						// Get field_to
						var field_to = (typeof(ws_this.field_data_cache[destination_object_id]) !== 'undefined') ? ws_this.field_data_cache[destination_object_id] : false;

						// Get value parsed
						var destination_value = (typeof(action_single['value']) === 'undefined') ? false : ws_this.parse_variables_process(action_single['value'], destination_repeatable_index, false, field_to, false, false, false).output;

						// Process action
						var conditional_process_action_return = ws_this.conditional_process_action(action_then_else, destination_action, $(this), destination_obj, destination_object, destination_object_id, destination_object_row_id, destination_value, destination_repeatable_index);
						process_required = process_required || conditional_process_action_return.process_required;
						process_bypass = process_bypass || conditional_process_action_return.process_bypass;
						process_navigation = process_navigation || conditional_process_action_return.process_navigation;
					});

					break;

				case 'action' :

					// Get value parsed
					var destination_value = (typeof(action_single['value']) === 'undefined') ? false : ws_this.parse_variables_process(action_single['value'], false, false, false, false, false).output;

					// Process action
					var conditional_process_action_return = ws_this.conditional_process_action(action_then_else, destination_action, $(this), false, destination_object, destination_object_id, false, destination_value, false);
					process_required = process_required || conditional_process_action_return.process_required;
					process_bypass = process_bypass || conditional_process_action_return.process_bypass;
					process_navigation = process_navigation || conditional_process_action_return.process_navigation;

					break;
			}

			// Increment number of actions processed
			actions_processed++;
		}

		// Process required?
		if(process_required) {

			if(typeof(this.form_progress) === 'function') { this.form_progress(); }
			this.form_required();
		}

		// Process bypass?
		if(process_bypass) {

			if(
				this.form_bypass(true) &&
				(typeof(this.form_tab_validation_process) === 'function')
			) {
				this.form_tab_validation_process();
			}
		}

		// Process navigation?
		if(process_navigation) {

			this.form_navigation();
		}

		return actions_processed;
	}

	$.WS_Form.prototype.conditional_process_action = function(action_then_else, action, obj_wrapper, obj, object, object_id, object_row_id, value, section_repeatable_index) {

		if(typeof(value) === 'undefined') { value = ''; }

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		// Build field name
		var field_name = ws_form_settings.field_prefix + object_id + (section_repeatable_index ? '[' + section_repeatable_index + ']' : '');

		// Set debug action value
		var debug_action_value = value;
		var debug_action_language_id = false;

		// Process required?
		var process_required = false;

		// Process bypass?
		var process_bypass = false;

		// Process navigation?
		var process_navigation = false;

		switch(action) {

			// Set value
			case 'value' :
			case 'value_number' :
			case 'value_datetime' :
			case 'value_tel' :
			case 'value_email' :
			case 'value_textarea' :
			case 'value_url' :
			case 'value_range' : 
			case 'value_rating' :
			case 'value_color' :
			case 'html' :
			case 'text_editor' :
			case 'button_html' :

				// Set field value
				this.field_value_set(obj_wrapper, obj, value);
				break;

			// Increase value
			case 'value_increase' :
			case 'value_decrease' :

				// Get current value
				var value_field = this.get_number(obj.val());

				// Parse value
				value = this.get_number(this.parse_variables_process(value).output);

				// Adjust
				value_field += ((action == 'value_decrease') ? -value : value);

				// Min
				var min = obj.attr('min');
				if(min && (value_field < min)) { break; }

				// Max
				var max = obj.attr('max');
				if(max && (value_field > max)) { break; }

				// Set field value
				this.field_value_set(obj_wrapper, obj, value_field);

				break;

			// Add class (Wrapper)
			case 'class_add_wrapper' :

				obj_wrapper.addClass(value);
				debug_action_language_id = 'debug_action_added';
				break;

			// Remove class
			case 'class_remove_wrapper' :

				obj_wrapper.removeClass(value);
				debug_action_language_id = 'debug_action_removed';
				break;

			// Add class (Wrapper)
			case 'class_add_field' :

				obj.addClass(value);
				debug_action_language_id = 'debug_action_added';
				break;

			// Remove class
			case 'class_remove_field' :

				obj.removeClass(value);
				debug_action_language_id = 'debug_action_removed';
				break;

			// Reset signature
			case 'reset_signature' :

				if(typeof(this.signature_clear_by_name) === 'function') {

					this.signature_clear_by_name(field_name);
					debug_action_language_id = 'debug_action_reset';
				}
				break;

			// Select an option
			case 'value_row_select' :
			case 'value_row_deselect' :

				obj.each(function() {

					if(!$(this).is(':enabled')) { return; }
					var trigger = ($(this).prop('selected') !== (action == 'value_row_select'));
					$(this).prop('selected', false).prop('selected', (action == 'value_row_select'));
					if(trigger) { $(this).closest('select').trigger('change'); }
				});

				debug_action_language_id = 'debug_action_' + ((action == 'value_row_select') ? 'selected' : 'deselected');

				break;

			// Select an option by value
			case 'value_row_select_value' :
			case 'value_row_deselect_value' :

				var check = (action == 'value_row_select_value');
				this.field_value_set(obj_wrapper, obj, value, check);
				debug_action_language_id = 'debug_action_' + (check ? 'selected_value' : 'deselected_value');
				break;

			// Check/uncheck all rows
			case 'value_row_select_all' :
			case 'value_row_deselect_all' :

				$('option', obj_wrapper).each(function() {

					if(!$(this).is(':enabled')) { return; }
					var trigger = ($(this).prop('selected') !== (action == 'value_row_select_all'));
					$(this).prop('selected', false).prop('selected', (action == 'value_row_select_all'));
					if(trigger) { $(this).closest('select').trigger('change'); }
					debug_action_language_id = 'debug_action_' + ((action == 'value_row_select_all') ? 'selected' : 'deselected');
				});

				break;

			// Unselect all options (Clear)
			case 'value_row_reset' :

				$('option:selected:enabled', obj_wrapper).prop('selected', false).trigger('change');
				debug_action_language_id = 'debug_action_reset';
				break;

			// Check/uncheck a checkbox or radio
			case 'value_row_check' :
			case 'value_row_uncheck' :

				obj.each(function() {

					var trigger = ($(this).prop('checked') !== (action == 'value_row_check'));
					$(this).prop('checked', (action == 'value_row_check'));
					if(trigger) { $(this).trigger('change'); }
				});

				debug_action_language_id = 'debug_action_' + ((action == 'value_row_check') ? 'checked' : 'unchecked');

				break;

			// Check/uncheck a checkbox or radio. by value
			case 'value_row_check_value' :
			case 'value_row_uncheck_value' :

				var check = (action == 'value_row_check_value');
				this.field_value_set(obj_wrapper, obj, value, check);
				debug_action_language_id = 'debug_action_' + (check ? 'checked' : 'unchecked');
				break;

			// Check/uncheck all checkboxes
			case 'value_row_check_all' :
			case 'value_row_uncheck_all' :

				$('input[type="checkbox"]', obj_wrapper).each(function() {

					if(!$(this).is(':enabled')) { return; }
					var trigger = ($(this).prop('checked') !== (action == 'value_row_check_all'));
					$(this).prop('checked', (action == 'value_row_check_all'));
					if(trigger) { $(this).trigger('change'); }
					debug_action_language_id = 'debug_action_' + ((action == 'value_row_check_all') ? 'checked' : 'unchecked');
				});

				break;

			// Set required on a checkbox or radio
			case 'value_row_required' :
			case 'value_row_not_required' :

				// Set required attribute
				obj.prop('required', (action == 'value_row_required')).removeAttr('data-init-required');

				if(action == 'value_row_required') {

					// Set ARIA required
					obj.attr('data-required', '').attr('aria-required', 'true').removeAttr('data-conditional-logic-bypass');

				} else {

					// Set ARIA not required
					obj.removeAttr('data-required').removeAttr('aria-required').attr('data-conditional-logic-bypass', '');
				}

				debug_action_language_id = 'debug_action_' + ((action == 'value_row_required') ? 'required' : 'not_required');

				process_required = true;
				process_bypass = true;

				break;

			// Set disabled on a checkbox or radio
			case 'value_row_disabled' :
			case 'value_row_not_disabled' :

				obj.attr('disabled', (action == 'value_row_disabled'));

				// Re-render select2 (Fixes select2 bug where disable attribute is not updated)
				if(typeof(obj.parent().attr('data-wsf-select2')) !== 'undefined') {

					if(typeof(this.form_select2) === 'function') { this.form_select2(obj.parent()); }
				}

				debug_action_language_id = 'debug_action_' + ((action == 'value_row_disabled') ? 'disabled' : 'enabled');
				break;

			// Set visible on a checkbox or radio
			case 'value_row_visible' :
			case 'value_row_not_visible' :

				if(action === 'value_row_not_visible') { obj.parent().hide(); } else { obj.parent().show(); }
				debug_action_language_id = 'debug_action_' + ((action == 'value_row_not_visible') ? 'hide' : 'show');
				process_bypass = true;
				break;

			// Focus checkbox or radio
			case 'value_row_focus' :

				obj.trigger('focus');
				debug_action_language_id = 'debug_action_focussed';
				break;

			// Add class
			case 'value_row_class_add' :

				obj.addClass(value);
				debug_action_language_id = 'debug_action_added';
				break;

			// Remove class
			case 'value_row_class_remove' :

				obj.removeClass(value);
				debug_action_language_id = 'debug_action_removed';
				break;

			// Set custom validity
			case 'value_row_set_custom_validity' :

				// Set invalid feedback
				this.set_invalid_feedback(obj, value, object_row_id);

				// Process bypass
				process_bypass = true;

				break;

			// Set min / max / step / low / high / optimum (Floating point)
			case 'min' :
			case 'max' :
			case 'step' :
			case 'low' :
			case 'high' :
			case 'optimum' :

				value = (value != '') ? this.get_float(value, 0) : false;

				this.obj_set_attribute(obj, action, value);

				break;

			// Set min / max / step (Integer)
			case 'min_int' :
			case 'max_int' :
			case 'step_int' :

				var action_int = action.replace('_int', '');

				value = (value != '') ? this.get_number(value, 0, false) : false;

				this.obj_set_attribute(obj, action_int, value);

				break;

			// Set min / max date / time
			case 'min_date' :
			case 'max_date' :
			case 'min_time' :
			case 'max_time' :

				if(typeof(obj.datetimepicker) === 'function') {

					switch(action) {

						case 'min_date' :

							obj.datetimepicker('setOptions', {minDate: value});
							break;

						case 'max_date' :

							obj.datetimepicker('setOptions', {maxDate: value});
							break;

						case 'min_time' :

							obj.datetimepicker('setOptions', {minTime: value});
							break;

						case 'max_time' :

							obj.datetimepicker('setOptions', {maxTime: value});
							break;
					}
				}

				break;

			// Set e-commerce min / max
			case 'ecommerce_price_min' :
			case 'ecommerce_price_max' :

				value = (value != '') ? this.get_float(value, 0) : false;

				// Get old value before change
				var obj_value_old = obj.val();

				// Set attribute
				this.obj_set_attribute(obj, 'data-ecommerce-' + ((action === 'ecommerce_price_min') ? 'min' : 'max'), value);

				// Re-process currency input mask
				if(typeof(this.form_ecommerce_input_mask_currency) === 'function') { this.form_ecommerce_input_mask_currency(obj); }

				// Trigger change if value changes due to min / max being applied
				if(obj.val() !== obj_value_old) { obj.trigger('change'); }

				break;

			// Set select min / max
			case 'select_min' :
			case 'select_max' :

				var min_max = (action === 'select_min') ? 'min' : 'max';

				value = (value != '') ? this.get_number(value, 0, false) : false;

				var form_select_min_max_process = (typeof(obj_wrapper.attr('data-select-min-max-init')) === 'undefined');

				if(value !== false) {

					obj_wrapper.attr('data-select-' + min_max, value);

				} else {

					obj_wrapper.removeAttr('data-select-' + min_max);
				}

				if(form_select_min_max_process) {

					if(typeof(this.form_select_min_max) === 'function') {

						this.form_select_min_max();
					}

				} else {

					var input_number = $('input[type="number"]', obj_wrapper); 

					this.obj_set_attribute(input_number, min_max, value);
				}

				break;

			// Set checkbox min / max
			case 'checkbox_min' :
			case 'checkbox_max' :

				var min_max = (action === 'checkbox_min') ? 'min' : 'max';

				value = (value != '') ? this.get_number(value, 0, false) : false;

				var form_checkbox_min_max_process = (typeof(obj_wrapper.attr('data-checkbox-min-max-init')) === 'undefined');

				if(value !== false) {

					obj_wrapper.attr('data-checkbox-' + min_max, value);

				} else {

					obj_wrapper.removeAttr('data-checkbox-' + min_max);
				}

				if(form_checkbox_min_max_process) {

					if(typeof(this.form_checkbox_min_max) === 'function') {

						this.form_checkbox_min_max();
					}

				} else {

					var input_number = $('input[type="number"]', obj_wrapper); 

					this.obj_set_attribute(input_number, min_max, value);
				}

				break;

			// Set visibility
			case 'visibility' :

				switch(object) {

					// Tab
					case 'group' :

						var group_tab_obj = $('[' + selector_href + '="#' + this.esc_selector(this.form_id_prefix + 'group-' + object_id) + '"]', this.form_canvas_obj).parent();

						if(value === 'off') {

							// Is tab being hidden currently visible?
							var obj_visible = obj_wrapper.is(':visible');

							if(obj_visible) {

								// Attempt to find first hidden tab and show it
								var groups_visible = $('[id^="' + this.esc_selector(this.form_id_prefix) + 'group-"]:not([data-wsf-group-hidden])');

								if(groups_visible.length) {

									var group_id = groups_visible.first().attr('id');

									$('[' + selector_href + '="#' + this.esc_selector(group_id) + '"]').trigger('click');
								}
							}

							// Hide tab
							group_tab_obj.attr('data-wsf-group-hidden', '').hide().attr('aria-live', 'polite').attr('aria-hidden', 'true');

							// Hide tab content
							obj_wrapper.attr('data-wsf-group-hidden', '').attr('aria-live', 'polite').attr('aria-hidden', 'true');

							debug_action_language_id = 'debug_action_hide';

						} else {

							// Show tab
							group_tab_obj.removeAttr('data-wsf-group-hidden').show().removeAttr('aria-hidden');

							// Show tab content
							obj_wrapper.removeAttr('data-wsf-group-hidden').removeAttr('aria-hidden');

							debug_action_language_id = 'debug_action_show';
						}

						// Process bypass
						process_bypass = true;

						// Process navigation
						process_navigation = true;

						break;

					// Field / section visibility
					case 'section' :
					case 'field' :

						if(value === 'off') {

							// Hide object
							obj_wrapper.hide().attr('aria-live', 'polite').attr('aria-hidden', 'true');

							// Process bypass
							process_bypass = true;

							debug_action_language_id = 'debug_action_hide';

						} else {

							// Show object
							obj_wrapper.show().removeAttr('aria-hidden');

							// Process bypass
							process_bypass = true;

							// Redraw signatures
							if(typeof(this.signatures_redraw) === 'function') {

								if(object == 'section') { this.signatures_redraw(false, object_id); }
								if(object == 'field') { this.signatures_redraw(false, false, object_id); }
							}

							debug_action_language_id = 'debug_action_show';
						}

						break;
				}

				// Redraw labels
				if(object === 'field') {

					this.form_label(obj_wrapper);
				}

				break;

			// Set row count
			case 'set_row_count' :

				// Get sections
				var sections = $('[data-repeatable][data-id="' + this.esc_selector(object_id) + '"]', this.form_canvas_obj);
				if(!sections.length) { break; }
				var section_count = sections.length;
				if(isNaN(value)) { break; }
				var section_count_required = parseInt(value, 10);

				// Get section data
				var section = this.section_data_cache[object_id];

				// Section repeat - Min
				var section_repeat_min = this.get_object_meta_value(section, 'section_repeat_min', 1);
				if(
					(section_repeat_min == '') ||
					isNaN(section_repeat_min)

				) { section_repeat_min = 1; } else { section_repeat_min = parseInt(section_repeat_min, 10); }
				if(section_repeat_min < 1) { section_repeat_min = 1; }

				// Section repeat - Max
				var section_repeat_max = this.get_object_meta_value(section, 'section_repeat_max', false);
				if(
					(section_repeat_max == '') ||
					isNaN(section_repeat_min)

				) { section_repeat_max = false; } else { section_repeat_max = parseInt(section_repeat_max, 10); }

				// Checks
				if(section_count_required < section_repeat_min) { section_count_required = section_repeat_min; }
				if((section_repeat_max !== false) && (section_count_required > section_repeat_max)) {

					section_count_required = section_repeat_max;
				}

				// Add rows
				if(section_count < section_count_required) {

					// Get section obj to clone
					var section_clone_this = sections.last();

					// Calculate number of rows to add
					var rows_to_add = (section_count_required - section_count);
					for(var add_count = 0; add_count < rows_to_add; add_count++) {

						// Clone
						if(typeof(this.section_clone) === 'function') { this.section_clone(section_clone_this); }
					}

					// Initialize added section
					if(typeof(this.section_add_init) === 'function') { this.section_add_init(object_id); }

					// Trigger event
					this.form_canvas_obj.trigger('wsf-section-repeatable').trigger('wsf-section-repeatable-' + object_id);
				}

				// Delete rows
				if(section_count > section_count_required) {

					// Calculate number of rows to delete
					var rows_to_delete = (section_count - section_count_required);
					for(var delete_count = 0; delete_count < rows_to_delete; delete_count++) {

						var sections = $('[data-repeatable][data-id="' + this.esc_selector(object_id) + '"]', this.form_canvas_obj);
						sections.last().remove();
					}

					// Initialize removed section
					if(typeof(this.section_remove_init) === 'function') { this.section_remove_init(object_id); }

					// Trigger event
					this.form_canvas_obj.trigger('wsf-section-repeatable').trigger('wsf-section-repeatable-' + object_id);
				}

				break;

			// Disable
			case 'disabled' :

				switch(object) {

					case 'section' :

						// For sections, we need to look for a fieldset
						obj_wrapper.prop('disabled', (value == 'on'));

						if(value == 'on') {

							obj_wrapper.attr('aria-disabled', 'true');

						} else {

							obj_wrapper.removeAttr('aria-disabled');
						}

						var obj_array = $('[name]', obj_wrapper);

						break;

					case 'field' :

						var obj_array = obj;

						break;
				}

				var ws_this = this;

				$(obj_array).each(function() {

					$(this).prop('disabled', (value == 'on'));

					if(value == 'on') {

						$(this).attr('aria-disabled', 'true');

					} else {

						$(this).removeAttr('aria-disabled');
					}

					var obj_wrapper = $(this).closest('[data-type]');

					var class_disabled_array = ws_this.get_field_value_fallback(obj_wrapper.attr('data-type'), false, 'class_disabled', false);

					if(value == 'on') {

						if(class_disabled_array !== false) { $(this).addClass(class_disabled_array.join(' ')); }
						$(this).css({'pointer-events': 'none'}).attr('data-conditional-logic-bypass', '');

					} else {

						if(class_disabled_array !== false) { $(this).removeClass(class_disabled_array.join(' ')); }
						$(this).css({'pointer-events': 'auto'}).removeAttr('data-conditional-logic-bypass');
					}

					switch(obj_wrapper.attr('data-type')) {

						case 'file' :

							switch($(this).attr('data-file-type')) {

								case 'dropzonejs' :

									if(value == 'on') {

										$('.dropzone', obj_wrapper)[0].dropzone.disable();

									} else {

										$('.dropzone', obj_wrapper)[0].dropzone.enable();
									}

									break;
							}

							break;
					}
				})

				// Re-process form validation
				this.form_validate_real_time();

				// Process navigation
				process_navigation = true;

				debug_action_language_id = 'debug_action_' + ((value == 'on') ? 'disabled' : 'enabled');
				break;

			// Required
			case 'required' :

				// Get field data
				var field = this.field_data_cache[object_id];
				switch(field.type) {

					case 'radio' :
					case 'price_radio' :

						obj = $('[name="' + this.esc_selector(field_name) + '[]"]', obj_wrapper);
						break;
				}

				// Set required attribute
				obj.prop('required', (value == 'on')).removeAttr('data-init-required');

				if(value == 'on') {

					// Set ARIA required
					obj.attr('data-required', '').attr('aria-required', 'true').removeAttr('data-conditional-logic-bypass');

				} else {

					// Set ARIA not required
					obj.removeAttr('data-required').removeAttr('aria-required').removeAttr('data-required-bypass').removeAttr('data-aria-required-bypass').attr('data-conditional-logic-bypass', '');
				}

				// Re-process form validation
				this.form_validate_real_time();

				debug_action_language_id = 'debug_action_' + ((value == 'on') ? 'required' : 'not_required');

				process_required = true;
				process_bypass = true;

				break;

			// Required - Signature
			case 'required_signature' :

				// Set required attribute
				obj.prop('required', (value == 'on')).removeAttr('data-init-required');

				if(value == 'on') {

					// Set ARIA required
					obj.attr('data-required', '').attr('aria-required', 'true').removeAttr('data-conditional-logic-bypass');

				} else {

					// Set ARIA not required
					obj.removeAttr('data-required').removeAttr('aria-required').removeAttr('data-required-bypass').removeAttr('data-aria-required-bypass').attr('data-conditional-logic-bypass', '');
				}

				// Re-process form validation
				this.form_validate_real_time();

				debug_action_language_id = 'debug_action_' + ((value == 'on') ? 'required' : 'not_required');

				process_required = true;
				process_bypass = true;

				break;

			// Read only
			case 'readonly' :

				obj.prop('readonly', (value == 'on'));

				if(value == 'on') {

					obj.attr('aria-readonly', 'true');

				} else {

					obj.removeAttr('aria-readonly');
				}

				debug_action_language_id = 'debug_action_' + ((value == 'on') ? 'read_only' : 'not_read_only');

				// Destroy jQuery component if readonly
				if(typeof(this.form_date) === 'function') {

					this.form_date();
				}

				break;

			// Generate password
			case 'password_generate' :

				if(typeof(this.generate_password) === 'function') {

					obj.val(this.generate_password(24)).trigger('change');
				}

				break;

			// Geolocate
			case 'google_address_auto_complete' :

				if(typeof(this.google_address_auto_complete) === 'function') {

					obj.trigger('wsf-auto-complete');
				}

				break;

			// Set custom validity
			case 'set_custom_validity' :

				// Check for invalid_feedback_last_row
				var invalid_feedback_last_row = false;
				if(typeof(this.field_data_cache[object_id]) !== 'undefined') {

					var field = this.field_data_cache[object_id];

					if(typeof($.WS_Form.field_type_cache[field.type]) !== 'undefined') {

						var field_type_config = $.WS_Form.field_type_cache[field.type];

						invalid_feedback_last_row = (typeof(field_type_config.invalid_feedback_last_row) !== 'undefined') ? field_type_config.invalid_feedback_last_row : false
					}
				}

				// Get the invalid feedback object
				var invalid_feedback_obj = $('[id^="' + this.esc_selector(this.form_id_prefix + 'invalid-feedback-' + object_id) + '"]', obj_wrapper);

				// If invalid feedback is only available on the last row, then set obj to sibling input
				if(invalid_feedback_last_row) {

					var obj = invalid_feedback_obj.siblings('[id^="' + this.esc_selector(this.form_id_prefix + 'field-' + object_id) + '"]');
				}

				// Set invalid feedback
				this.set_invalid_feedback(obj, value, object_row_id);

				// Process bypass
				process_bypass = true;

				break;

			// Click
			case 'click' :

				switch(object) {

					// Tab click
					case 'group' :

						var group_obj = $('[' + selector_href + '="#' + this.esc_selector(this.form_id_prefix + 'group-' + object_id) + '"]:not([data-wsf-tab-disabled])', this.form_canvas_obj);

						// Trigger click
						group_obj.trigger('click');

						// Check if group is hidden (data-wsf-group-hidden will be present on the tab list item)
						var group_tab_obj = group_obj.parent();

						if(typeof(group_tab_obj.attr('data-wsf-group-hidden')) !== 'undefined') {

							// Show tab
							group_tab_obj.removeAttr('data-wsf-group-hidden').show().removeAttr('aria-hidden');

							// Show tab content
							obj_wrapper.removeAttr('data-wsf-group-hidden').removeAttr('aria-hidden');

							// Process bypass
							process_bypass = true;
						}

						break;

					// Field click
					case 'field' :

						obj.trigger('click');
						break;
				}

				debug_action_language_id = 'debug_action_clicked';
				break;

			// Focus
			case 'focus' :

				if(this.conversational) {

					// Focus without scrolling
					this.conversational_scroll_start_x = $(window).scrollLeft();
					this.conversational_scroll_start_y = $(window).scrollTop();
				}

				obj.trigger('focus');

				debug_action_language_id = 'debug_action_focussed';

				break;

			// Action - Run
			case 'action_run' :

				if(this.conditional_actions_run_action.indexOf(object_id) !== -1) {

					this.form_post('action', object_id);
				}
				break;

			// Action - Enable on save
			case 'action_run_on_save' :

				if(this.conditional_actions_run_save.indexOf(object_id) === -1) {
					this.conditional_actions_run_save.push(object_id);
					this.conditional_actions_changed = true;
				}
				break;

			// Action - Enable on submit
			case 'action_run_on_submit' :

				if(this.conditional_actions_run_submit.indexOf(object_id) === -1) {
					this.conditional_actions_run_submit.push(object_id);
					this.conditional_actions_changed = true;
				}
				break;

			// Action - Disable on save
			case 'action_do_not_run_on_save' :

				var object_id_index = this.conditional_actions_run_save.indexOf(object_id);
				if (object_id_index !== -1) {
					this.conditional_actions_run_save.splice(object_id_index, 1);
					this.conditional_actions_changed = true;
				}
				break;

			// Action - Disable on submit
			case 'action_do_not_run_on_submit' :

				var object_id_index = this.conditional_actions_run_submit.indexOf(object_id);
				if (object_id_index !== -1) {
					this.conditional_actions_run_submit.splice(object_id_index, 1);
					this.conditional_actions_changed = true;
				}
				break;

			// Run JavaScript
			case 'javascript' :

				try {

					$.globalEval("(function($) {\n" + value + "\n})(jQuery);");

				} catch(e) {

					this.error('error_js', value);
				}
				break;

			// Copy to clipboard
			case 'copy_to_clipboard' :

				navigator.clipboard.writeText(obj.val());
				break;

			// Form - Show validation
			case 'validate_show' :

				this.form_obj.addClass(this.class_validated);
				break;

			// Form - Hide validation
			case 'validate_hide' :

				this.form_obj.removeClass(this.class_validated);
				break;

			// Form - Save
			case 'form_save' :

				this.form_post('save');
				break;

			// Form - Save if validated
			case 'form_save_validate' :

				this.form_post_if_validated('save');
				break;

			// Form - Clear hash
			case 'form_hash_clear' :

				this.form_hash_clear();
				break;

			// Form - Submit
			case 'form_submit' :

				this.form_obj.trigger('submit');
				break;

			// Form - Clear
			case 'form_clear' :

				this.form_clear();
				break;

			// Form - Reset
			case 'form_reset' :

				this.form_reset();
				break;

			// Form - Loader - Show
			case 'form_loader_show' :

				if(typeof(this.form_loader_show) !== 'undefined') { this.form_loader_show('conditional'); }
				break;

			// Form - Loader - Hide
			case 'form_loader_hide' :

				if(typeof(this.form_loader_hide) !== 'undefined') { this.form_loader_hide(); }
				break;

			// Reset
			// Clear
			case 'reset' :
			case 'reset_file' : 	// Legacy
			case 'clear' :

				var field_clear = (action === 'clear');

				switch(object) {

					case 'group' :

						this.group_fields_reset(object_id, field_clear);

						break;

					case 'section' :

						this.section_fields_reset(object_id, field_clear, section_repeatable_index);

						break;

					case 'field' :

						this.field_reset(object_id, field_clear, obj_wrapper);

						break;
				}

				break;
		}

		if($.WS_Form.debug_rendered) {

			var object_single_type = false;

			// Build action description for debug
			switch(object) {

				case 'form' :

					var object_single_type = this.language('debug_action_form');
					var object_single_label = this.language('debug_action_form');
					break;

				case 'group' :

					if(typeof(this.group_data_cache[object_id]) !== 'undefined') {

						var object_single = this.group_data_cache[object_id];
						var object_single_type = this.language('debug_action_group');
						var object_single_label = object_single.label;
					}
					break;

				case 'section' :

					if(typeof(this.section_data_cache[object_id]) !== 'undefined') {

						var object_single = this.section_data_cache[object_id];
						var object_single_type = this.language('debug_action_section');
						var object_single_label = object_single.label;
					}
					break;

				case 'field' :

					if(typeof(this.field_data_cache[object_id]) !== 'undefined') {

						var object_single = this.field_data_cache[object_id];						
						var object_single_type = object_single.type;
						var object_single_label = object_single.label;
					}
					break;

				case 'action' :

					if(typeof(this.action_data_cache[object_id]) !== 'undefined') {

						var object_single = this.action_data_cache[object_id];
						var object_single_type = this.language('debug_action_action');
						var object_single_label = object_single.label;
					}
					break;
			}

			if(object_single_type !== false) {

				if(debug_action_language_id !== false) { debug_action_value = this.language(debug_action_language_id); }

				var conditional_settings = $.WS_Form.settings_form.conditional;
				var conditional_settings_objects = conditional_settings.objects;
				var conditional_settings_actions = conditional_settings_objects[object]['action'];
				var conditional_settings_action = conditional_settings_actions[action];

				if(!conditional_settings_action) {

					this.error('error_condition_action', action);
					return false;
				}

				var action_description = conditional_settings_action.text.toUpperCase();
				if(typeof(conditional_settings_action.values) !== 'undefined') {

					if(typeof(conditional_settings_action.values) === 'object') {

						if(typeof(conditional_settings_action.values[value]) !== 'undefined') {

							debug_action_value = conditional_settings_action.values[value].text;
						}
					}
				}

				var log_description = '<strong>[' + this.esc_html(object_single_label) + '] ' + action_description + (((debug_action_value !== false) && (debug_action_value != '')) ? " '" + this.esc_html(debug_action_value) + "'" : '') + '</strong> (' + this.language('debug_action_type') + ': ' + object_single_type + ' | ID: ' + object_id + ((object_row_id !== false) ? ' | ' + this.language('debug_action_row') + ' ID: ' + object_row_id.join(', ') : '') + ')';

				this.log('log_conditional_action_' + action_then_else, log_description, 'conditional');
			}
		}

		return { process_required: process_required, process_bypass: process_bypass, process_navigation: process_navigation };
	}

	// Set object attribute (if false, remove the attribute)
	$.WS_Form.prototype.obj_set_attribute = function(obj, attribute, value) {

		if(typeof(obj.attr('data-' + attribute + '-bypass')) !== 'undefined') {

			if(value !== false) {

				obj.attr('data-' + attribute + '-bypass', value).trigger('change');

			} else {

				obj.removeAttr('data-' + attribute + '-bypass').trigger('change');
			}

		} else {

			if(value !== false) {

				obj.attr(attribute, value).trigger('change');

			} else {

				obj.removeAttr(attribute).trigger('change');
			}
		}
	}

	$.WS_Form.prototype.conditional_logic_previous = function(accumulator, value, logic_previous) {

		switch(logic_previous) {

			// OR
			case '||' :

				accumulator |= value;
				break;

			// AND
			case '&&' :

				accumulator &= value;
				break;
		}

		return accumulator;
	}

	// Check integrity of a condition
	$.WS_Form.prototype.conditional_condition_check = function(condition) {

		return !(

			(condition === null) ||
			(typeof(condition) !== 'object') ||
			(typeof(condition.id) === 'undefined') ||
			(typeof(condition.object) === 'undefined') ||
			(typeof(condition.object_id) === 'undefined') ||
			(typeof(condition.object_row_id) === 'undefined') ||
			(typeof(condition.logic) === 'undefined') ||
			(typeof(condition.value) === 'undefined') ||
			(typeof(condition.case_sensitive) === 'undefined') ||
			(typeof(condition.logic_previous) === 'undefined') ||
			(condition.id == '') ||
			(condition.id == 0) ||
			(condition.object == '') ||
			(condition.object_id == '') ||
			(condition.logic == '')
		);
	}

	// Check integrity of an action
	$.WS_Form.prototype.conditional_action_check = function(action) {

		return !(

			(action === null) ||
			(typeof(action) !== 'object') ||
			(typeof(action.object) === 'undefined') ||
			(typeof(action.object_id) === 'undefined') ||
			(typeof(action.action) === 'undefined') ||
			(action.object == '') ||
			(action.object_id == '') ||
			(action.action == '')
		);
	}

})(jQuery);
