(function($) {

	'use strict';

	// Group - Tabs - Init
	$.WS_Form.prototype.form_tab = function() {

		if(Object.keys(this.form.groups).length <= 1) { return false; }

		var ws_this = this;

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		// Get tab index cookie if settings require it
		var index = parseInt((this.get_object_meta_value(this.form, 'cookie_tab_index')) ? this.cookie_get('tab_index', 0) : 0, 10);

		// Check for query variable
		var index_query_variable = this.get_query_var('wsf_tab_index_' + this.form.id);
		if(index_query_variable !== '') {

			index = parseInt(index_query_variable, 10);
		}

		// Check index is valid
		var tabs_obj = $('.wsf-group-tabs', this.form_canvas_obj);
		var li_obj = tabs_obj.children();
		if(
			(typeof(li_obj[index]) === 'undefined') ||
			(typeof($(li_obj[index]).attr('data-wsf-group-hidden')) !== 'undefined')
		) {

			index = 0;

			var li_obj_visible = $(':not([data-wsf-group-hidden])', li_obj);

			if(li_obj_visible.length) {

				index = li_obj_visible.first().index();
			}

			// Save current tab index to cookie
			if(ws_this.get_object_meta_value(ws_this.form, 'cookie_tab_index')) {

				ws_this.cookie_set('tab_index', index);
			}
		}

		// If we are using the WS Form framework, then we need to run our own tabs script
		if($.WS_Form.settings_plugin.framework === 'ws-form') {

			// Destroy tabs (Ensures subsequent calls work)
			if(tabs_obj.hasClass('wsf-tabs')) { this.tabs_destroy(); }

			// Init tabs
			this.tabs(tabs_obj, { active: index });

		} else {

			// Set active tab
			this.form_tab_group_index_set(index);
		}

		// Progress for tabs
		if(typeof(this.form_progress_tabs) === 'function') { this.form_progress_tabs(); }

		var framework_tabs = this.framework['tabs']['public'];

		if(typeof(framework_tabs.event_js) !== 'undefined') {

			var event_js = framework_tabs.event_js;
			var event_type_js = (typeof(framework_tabs.event_type_js) !== 'undefined') ? framework_tabs.event_type_js : false;
			var event_selector_wrapper_js = (typeof(framework_tabs.event_selector_wrapper_js) !== 'undefined') ? framework_tabs.event_selector_wrapper_js : false;
			var event_selector_active_js = (typeof(framework_tabs.event_selector_active_js) !== 'undefined') ? framework_tabs.event_selector_active_js : false;

			switch(event_type_js) {

				case 'wrapper' :

					var event_selector = $(event_selector_wrapper_js, this.form_canvas_obj);
					break;

				default :

					var event_selector = $('[' + selector_href + '^="#' + this.form_id_prefix + 'group-"]', this.form_canvas_obj);
			}

			// Set up on click event for each tab
			event_selector.on(event_js, function (event, ui) {

				switch(event_type_js) {

					case 'wrapper' :

						var event_active_selector = $(event_selector_active_js, event_selector);
						var tab_index = event_active_selector.index();
						break;

					default :

						var tab_index = $(this).parent().index();
				}

				// Save current tab index to cookie
				if(ws_this.get_object_meta_value(ws_this.form, 'cookie_tab_index')) {

					ws_this.cookie_set('tab_index', tab_index);
				}

				// Progress for tabs
				if(typeof(ws_this.form_progress_tabs) === 'function') { ws_this.form_progress_tabs(); }

				// Redraw signatures
				if(typeof(ws_this.signatures_redraw) === 'function') { ws_this.signatures_redraw(tab_index); }

				// Re-render text editors
				if(typeof(ws_this.form_textarea) === 'function') { ws_this.form_textarea(); }
				// Object focus
				if(ws_this.object_focus !== false) {

					ws_this.object_focus.trigger('focus');
					ws_this.object_focus = false;
				}

				// Tab active event
				ws_this.form_tab_active_event(tab_index);

				// Trigger tab clicked event
				ws_this.trigger('tab-clicked');
			});
		}

		// Set group index
		this.group_index = index;
	}

	// Tab active event
	$.WS_Form.prototype.form_tab_active_event = function(index) {

		// Set group index
		this.group_index = index;

		// Process labels
		this.form_label();

		// Trigger group index change
		this.form_canvas_obj.trigger('wsf-group-index');
	}

	// Tab validation
	$.WS_Form.prototype.form_tab_validation = function() {

		var ws_this = this;

		var tab_validation = this.get_object_meta_value(this.form, 'tab_validation');
		if(tab_validation) {

			this.form_canvas_obj.on('wsf-validate-silent', function() {

				ws_this.form_tab_validation_process();
			});

			this.form_tab_validation_process();
		}

		// Fire initial conditional logic for tabs
		this.form_tab_active_event(this.group_index);
	}

	// Tab validation
	$.WS_Form.prototype.form_tab_validation_process = function() {

		var tab_validation = this.get_object_meta_value(this.form, 'tab_validation');
		if(!tab_validation) { return; }

		var ws_this = this;

		var tab_validated_previous = true;

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		// Get tabs
		var tabs = $('.wsf-group-tabs > :not([data-wsf-group-hidden]) > [' + selector_href + ']', this.form_canvas_obj);

		// Get tab count
		var tab_count = tabs.length;

		// Get tab_index_current
		var tab_index_current = 0;
		tabs.each(function(tab_index) {

			var tab_visible = $($(this).attr(selector_href)).is(':visible');
			if(tab_visible) {

				tab_index_current = tab_index;
				return false;
			}
		});

		tabs.each(function(tab_index) {

			// Render validation for previous tab
			ws_this.form_tab_validation_previous($(this), tab_validated_previous);

			// Validate tab
			if(tab_index < (tab_count - 1)) {

				if(tab_validated_previous === true) {

					var tab_validated_current = ws_this.object_validate($($(this).attr(selector_href)));

				} else {

					var tab_validated_current = false;
				}

				// Render validation for current tab
				ws_this.form_tab_validation_current($(this), tab_validated_current);

				tab_validated_previous = tab_validated_current;
			}

			// If we are on a tab that is beyond the current invalidated tab, change tab to first invalidated tab
			if(
				!tab_validated_current &&
				(tab_index_current > tab_index)
			) {

				// Activate tab
				ws_this.form_tab_group_index_set(tab_index);
			}
		});

		// Form navigation
		this.form_navigation();
	}

	// Tab validation - Current
	$.WS_Form.prototype.form_tab_validation_current = function(obj, tab_validated) {

		// Get selector href
		var selector_href = (typeof(this.framework.tabs.public.selector_href) !== 'undefined') ? this.framework.tabs.public.selector_href : 'href';

		var tab_id = obj.attr(selector_href);
		var tab_content_obj = $(tab_id, this.form_canvas_obj);
		var button_next_obj = $('button[data-action="wsf-tab_next"]', tab_content_obj);

		var tab_validation_show = this.get_object_meta_value(this.form, 'tab_validation_show');

		if(tab_validated) {

			if(tab_validation_show) {

				tab_content_obj.attr('data-wsf-validated', '');

			} else {

				button_next_obj.prop('disabled', false);
			}

		} else {

			if(tab_validation_show) {

				tab_content_obj.removeAttr('data-wsf-validated');

			} else {

				button_next_obj.prop('disabled', true);
			}
		}
	}

	// Tab validation - Previous
	$.WS_Form.prototype.form_tab_validation_previous = function(obj, tab_validated) {

		var framework_tabs = this.framework.tabs.public;

		if(typeof(framework_tabs.class_disabled) !== 'undefined') {

			if(tab_validated) {

				obj.removeClass(framework_tabs.class_disabled).removeAttr('data-wsf-tab-disabled').removeAttr('tabindex');

			} else {

				obj.addClass(framework_tabs.class_disabled).attr('data-wsf-tab-disabled', '').attr('tabindex', '-1');
			}
		}

		if(typeof(framework_tabs.class_parent_disabled) !== 'undefined') {

			if(tab_validated) {

				obj.parent().removeClass(framework_tabs.class_parent_disabled);

			} else {

				obj.parent().addClass(framework_tabs.class_parent_disabled);
			}
		}
	}

	// Tab - Activate by offset amount
	$.WS_Form.prototype.form_tab_group_index_new = function(obj, form_tab_group_index_new) {

		// Activate tab
		this.form_tab_group_index_set(form_tab_group_index_new);

		// Get field ID
		var field_id = obj.closest('[data-id]').attr('data-id');
		var field = this.field_data_cache[field_id];
		var scroll_to_top = this.get_object_meta_value(field, 'scroll_to_top', '');
		var scroll_to_top_offset = this.get_object_meta_value(field, 'scroll_to_top_offset', '0');
		scroll_to_top_offset = (scroll_to_top_offset == '') ? 0 : parseInt(scroll_to_top_offset, 10);
		var scroll_position = this.form_canvas_obj.offset().top - scroll_to_top_offset;

		switch(scroll_to_top) {

			// Instant
			case 'instant' :

				$('html,body').scrollTop(scroll_position);

				break;

			// Smooth
			case 'smooth' :

				var scroll_to_top_duration = this.get_object_meta_value(field, 'scroll_to_top_duration', '0');
				scroll_to_top_duration = (scroll_to_top_duration == '') ? 0 : parseInt(scroll_to_top_duration, 10);

				$('html,body').animate({

					scrollTop: scroll_position

				}, scroll_to_top_duration);

				break;
		}
	}

	// Tab - Set
	$.WS_Form.prototype.form_tab_group_index_set = function(group_index) {

		// Check that tabs exist
		if(Object.keys(this.form.groups).length <= 1) { return false; }

		var framework_tabs = this.framework['tabs']['public'];

		if(typeof(framework_tabs.activate_js) !== 'undefined') {

			var activate_js = framework_tabs.activate_js;	

			if(activate_js != '') {

				// Parse activate_js
				var mask_values = {'form': '#' + this.form_obj_id, 'index': group_index};
				var activate_js_parsed = this.mask_parse(activate_js, mask_values);

				// Execute activate tab javascript
				$.globalEval('(function($) { $(function() {' + activate_js_parsed + '}); })(jQuery);');

				// Set cookie
				this.cookie_set('tab_index', group_index);
			}
		}

		// Progress by tabs
		if(typeof(this.form_progress_tabs) === 'function') { this.form_progress_tabs(); }

		// Log
		this.log('log_group_index', group_index);

		// Tab active event
		this.form_tab_active_event(group_index);
	}

})(jQuery);
