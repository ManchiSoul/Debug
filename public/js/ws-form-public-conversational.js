(function($) {

	'use strict';

	$.WS_Form.prototype.form_conversational = function() {

		var ws_this = this;

		// Set conversation to true
		this.conversational = true;

		// Navigation
		this.form_conversational_navigation();

		// Settings
		this.form_conversational_settings();

		// Form
		this.form_conversational_form();

		// Sections
		this.form_conversational_section();

		// Fields
		this.form_conversational_field();

		// Focus
		this.form_conversational_focus();

		// Key presses
		this.form_conversational_key_down();

		// Resize
		this.form_conversational_resize();

		// Scroll
		this.form_conversational_scroll();

		// Touching in progress
		this.touching = false;
		this.touching_timeout = false;
		this.touching_time = false;

		// Focus processing
		this.focus_process = true;
	}

	// Initialize settings
	$.WS_Form.prototype.form_conversational_settings = function() {

		// Settings
		this.conversational_scroll_duration = parseInt(this.get_object_meta_value(this.form, 'conversational_scroll_duration', 300, false, false));

		// Get initial window inner height
		this.window_inner_height_initial = window.innerHeight;

		// Get first visible section object
		this.section_obj_first = $('.wsf-section:visible', this.form_canvas_obj).first();

		// Get last visible section object
		this.section_obj_last = $('.wsf-section:visible', this.form_canvas_obj).last();

		// Get all sections that should be full height
		this.sections_full_height = $('.wsf-form-conversational-section-full-height', this.form_canvas_obj);

		// Get grid gutter
		this.grid_gutter = parseInt(ws_form_settings.skin_grid_gutter);

		// Mobile?
		this.mobile = window.matchMedia("only screen and (max-width: 543px)").matches;

		// Scroll reference offset for mobile
		this.mobile_scroll_reference_offset = this.grid_gutter;
	}

	// Initialize form
	$.WS_Form.prototype.form_conversational_form = function() {

		// Set height of sections configured to use full height
		if(this.mobile) {

			this.sections_full_height.css({'height': (this.window_inner_height_initial - this.nav_height) + 'px'});

		} else {

			this.sections_full_height.css({'height': 'calc(100vh - ' + this.nav_height + 'px)'});
		}

		// Section first margin
		this.form_conversational_section_first_margin();

		// Section last margin
		this.form_conversational_section_last_margin();
	}

	// Section first margin
	$.WS_Form.prototype.form_conversational_section_first_margin = function() {

		if(!this.section_obj_first.hasClass('wsf-form-conversational-section-full-height')) {

			if(this.mobile) {

				var section_obj_first_margin_top = this.grid_gutter;

			} else {

				var section_obj_first_margin_offset = (this.section_obj_first.height() / 2);
				if(this.conversational_nav) { section_obj_first_margin_offset += (this.nav_height / 2); }

				if((this.window_inner_height_initial / 2) - section_obj_first_margin_offset > this.grid_gutter) {

					var section_obj_first_margin_top = 'calc(50vh - ' + section_obj_first_margin_offset + 'px)';

				} else {

					var section_obj_first_margin_top = this.grid_gutter;
				}
			}

			this.section_obj_first.css({'margin-top': section_obj_first_margin_top});
		}
	}

	// Section last margin
	$.WS_Form.prototype.form_conversational_section_last_margin = function() {

		// Add bottom margin to last section
		if(!this.section_obj_last.hasClass('wsf-form-conversational-section-full-height')) {

			if(this.mobile) {

				var section_obj_last_margin_bottom = this.window_inner_height_initial - this.section_obj_last.height() - this.grid_gutter;

			} else {

				var section_obj_last_margin_offset = (this.section_obj_last.height() / 2) + (this.grid_gutter / 2);
				if(this.conversational_nav) { section_obj_last_margin_offset -= (this.nav_height / 2); }
				if((this.window_inner_height_initial / 2) - section_obj_last_margin_offset > this.grid_gutter) {

					var section_obj_last_margin_bottom = 'calc(50vh - ' + section_obj_last_margin_offset + 'px)';

				} else {

					var section_obj_last_margin_bottom = this.nav_height;
				}
			}

			this.section_obj_last.css({'margin-bottom': section_obj_last_margin_bottom});
		}
	}

	// Initialize navigation
	$.WS_Form.prototype.form_conversational_navigation = function() {

		// Read settings
		this.conversational_nav = this.get_object_meta_value(this.form, 'conversational_nav', 'on', false, false);
		this.conversational_nav_progress_help = this.get_object_meta_value(this.form, 'conversational_nav_progress_help', '#progress_percent', false, false);

		if(!this.conversational_nav) { this.nav_height = 0; return; }

		var ws_this = this;

		// Build navigation HTML
		var navigation_html = '<nav class="wsf-form-conversational-nav">';
		navigation_html += '<div><ul>';
		navigation_html += '<li><progress class="wsf-form-conversational-nav-progress wsf-progress" data-progress-bar data-progress-bar-value value="50" /></progress>';

		if(this.conversational_nav_progress_help != '') {

			navigation_html += '<small class="wsf-form-conversational-nav-progress-help wsf-help">' + this.conversational_nav_progress_help + '</small>';
		}

		navigation_html += '</li>';
		navigation_html += '<li class="wsf-form-conversational-nav-move-up" aria-label="Previous" title="Previous"><svg viewBox="0 0 16 16"><path d="M8 16c-2.1 0-4.1-.8-5.7-2.3S0 10.1 0 8s.8-4.1 2.3-5.7S5.9 0 8 0s4.1.8 5.7 2.3S16 5.9 16 8s-.8 4.1-2.3 5.7S10.1 16 8 16zM8 1.2c-3.7 0-6.8 3-6.8 6.8s3 6.8 6.8 6.8 6.8-3 6.8-6.8S11.7 1.2 8 1.2zm4.3 7.9L8 4.7 3.7 9.1l.9.9L8 6.5l3.4 3.4.9-.8z" fill="#ceced2"></path></svg></li>';
		navigation_html += '<li class="wsf-form-conversational-nav-move-down" aria-label="Next" title="Next"><svg viewBox="0 0 16 16"><path d="M8 0c2.1 0 4.1.8 5.7 2.3S16 5.9 16 8s-.8 4.1-2.3 5.7S10.1 16 8 16s-4.1-.8-5.7-2.3S0 10.1 0 8s.8-4.1 2.3-5.7S5.9 0 8 0zm0 14.8c3.7 0 6.8-3 6.8-6.8s-3-6.8-6.8-6.8S1.2 4.3 1.2 8s3.1 6.8 6.8 6.8zM3.7 6.9L8 11.3 12.3 7l-.9-.9L8 9.5 4.6 6.1l-.9.8z" fill="#ceced2"></path></svg></li>';
		navigation_html += '</ul></div>';
		navigation_html += '</nav>';

		// Append navigation to form
		this.form_canvas_obj.append(navigation_html);

		// Get navigation element
		var navigation_obj = $('.wsf-form-conversational-nav', this.form_canvas_obj);

		// Get height of navigation
		this.nav_height = navigation_obj.outerHeight();

		// Event handlers
		$('.wsf-form-conversational-nav-move-up', navigation_obj).on('click', function() {

			ws_this.form_conversational_section_previous();
		});

		$('.wsf-form-conversational-nav-move-down', navigation_obj).on('click', function() {

			ws_this.form_conversational_section_next();
		});
	}

	$.WS_Form.prototype.form_conversational_section_previous = function() {

		var section_obj_previous = this.conversational_section_obj_focus.prevAll(':visible');

		if(section_obj_previous.length) {

			this.conversational_section_obj_focus = section_obj_previous.first();
			this.form_conversational_focus_section(true, true);
		}
	}

	$.WS_Form.prototype.form_conversational_section_next = function() {

		var section_obj_next = this.conversational_section_obj_focus.nextAll(':visible');

		if(section_obj_next.length) {

			this.conversational_section_obj_focus = section_obj_next.first();
			this.form_conversational_focus_section(true, true);
		}
	}

	// Initialize focus
	$.WS_Form.prototype.form_conversational_focus = function() {

		var ws_this = this;

		// Focus on first visible section
		this.conversational_section_obj_focus = this.section_obj_first;
		this.conversational_section_obj_focus_old = this.conversational_section_obj_focus;
		this.form_conversational_focus_section(false, true);

		// Field focussed
		this.form_canvas_obj.on('focus', '.wsf-field, .wsf-button, [tabindex="0"]', function(event, method) {

			if(!ws_this.focus_process) { return; }

			if(method === 'wsf-internal') { return; }

			var section_obj_current = $(this).closest('.wsf-section:visible');

			if(section_obj_current[0] === ws_this.conversational_section_obj_focus[0]) { return; }

			ws_this.conversational_section_obj_focus = section_obj_current;

			ws_this.form_conversational_focus_section(true, false);
		});
	}

	$.WS_Form.prototype.form_conversational_focus_section = function(scroll_to, focus_first_field) {

		var ws_this = this;

		// Classes
		this.conversational_section_obj_focus_old.removeClass('wsf-form-conversational-active').addClass('wsf-form-conversational-inactive');
		this.conversational_section_obj_focus.removeClass('wsf-form-conversational-inactive').addClass('wsf-form-conversational-active');

		// Check for repeatable sections
		var section_id_new = this.conversational_section_obj_focus.attr('data-id');
		var section_id_old = this.conversational_section_obj_focus_old.attr('data-id');

		$('[id^="wsf-1-section-' + section_id_new + '-repeat-"]').removeClass('wsf-form-conversational-inactive').addClass('wsf-form-conversational-active');

		if(section_id_new !== section_id_old) {

			$('[id^="wsf-1-section-' + section_id_old + '-repeat-"]').removeClass('wsf-form-conversational-active').addClass('wsf-form-conversational-inactive');
		}

		this.conversational_section_obj_focus_old = this.conversational_section_obj_focus;

		// Hide color pickers
		$('[data-type="color"] input', this.form_canvas_obj).each(function() {

			var conversational_section_obj = $(this).closest('.wsf-section');

			if(
				(typeof(Coloris) !== 'undefined') &&
				(conversational_section_obj[0] !== ws_this.conversational_section_obj_focus[0])
			) {
				Coloris.close();
			}
		});

		// Hide date time pickers
		$('[data-type="datetime"] input', this.form_canvas_obj).each(function() {

			var conversational_section_obj = $(this).closest('.wsf-section');

			if(conversational_section_obj[0] !== ws_this.conversational_section_obj_focus[0]) {

				$(this).datetimepicker('hide');
			}
		});

		if(scroll_to) {

			if(this.conversational_section_obj_focus[0] === this.section_obj_first[0]) {

				var scroll_to = 0;

			} else {

				if(this.mobile) {

					var scroll_to = this.form_conversational_section_top_position(this.conversational_section_obj_focus) - this.mobile_scroll_reference_offset;

				} else {

					var scroll_to = this.form_conversational_section_middle_position(this.conversational_section_obj_focus) - (window.innerHeight / 2);
					if(this.conversational_nav) { scroll_to += (this.nav_height / 2); }
				}
			}

			// Focus without scrolling
			if(this.conversational_scroll_start_x !== false) {

				$(window).scrollLeft(this.conversational_scroll_start_x);
				this.conversational_scroll_start_x = false;
			}
			if(this.conversational_scroll_start_y !== false) {

				$(window).scrollTop(this.conversational_scroll_start_y);
				this.conversational_scroll_start_y = false;
			}

			this.conversational_scroll_detect = false;

			$('html, body').stop().animate({

				scrollTop: scroll_to

			}, ws_this.conversational_scroll_duration, function() {

				setTimeout(function() {

					ws_this.conversational_scroll_detect = true;

				}, 200);

				// Focus first field
				if(focus_first_field) {

					ws_this.form_conversational_focus_first_field();
				}
			});

		} else {

			// Focus first field
			if(focus_first_field) {

				this.form_conversational_focus_first_field();
			}
		}
	}

	// Focus on first field in current section
	$.WS_Form.prototype.form_conversational_focus_first_field = function() {

		$('.wsf-field:visible, .wsf-button:visible', this.conversational_section_obj_focus).first().trigger('focus', 'wsf-internal');
	}

	// Initialize key down events
	$.WS_Form.prototype.form_conversational_key_down = function() {

		var ws_this = this;

		$(document).on('keydown', function(e) {

			ws_this.form_conversational_keydown(e, $(this));
		});

		this.form_canvas_obj.on('keydown', '.wsf-field, .wsf-button', function(e) {

			ws_this.form_conversational_keydown(e, $(this));
		});
	}

	$.WS_Form.prototype.form_conversational_keydown = function(e, obj) {

		if(this.conversational_section_obj_focus) {

			switch(e.keyCode) {

				case 13 :

					if(e.shiftKey === false) {

						e.preventDefault();
						e.stopPropagation();

						this.form_conversational_field_changed(obj);
					}

					break;
			}
		}
	}

	// Initialize resize events
	$.WS_Form.prototype.form_conversational_resize = function() {

		var ws_this = this;

		this.conversational_resize_time = false;
		this.conversational_resize_timeout = false;
		this.conversational_resize_delta = 200;

		$(window).on('resize', function() {

			ws_this.conversational_resize_time = new Date();
			if (ws_this.conversational_resize_timeout === false) {

				ws_this.conversational_resize_timeout = true;

				setTimeout(function() { ws_this.form_conversational_resize_process(); }, ws_this.conversational_resize_delta);
			}
		});
	}

	$.WS_Form.prototype.form_conversational_resize_process = function() {

		var ws_this = this;

		if(new Date() - this.conversational_resize_time < this.conversational_resize_delta) {

			setTimeout(function() { ws_this.form_conversational_resize_process(); }, this.conversational_resize_delta);

		} else {

			this.conversational_resize_timeout = false;

			// Settings
			this.form_conversational_settings();

			// Section first margin
			this.form_conversational_section_first_margin();

			// Section last margin
			this.form_conversational_section_last_margin();

			// Focus
			if(!this.mobile) {

				this.form_conversational_focus_section(true, true);
			}
		}
	}

	// Initialize scroll events
	$.WS_Form.prototype.form_conversational_scroll = function() {

		var ws_this = this;

		// Scroll
		this.conversational_scroll_detect = true;
		this.conversational_scroll_start_x = false;
		this.conversational_scroll_start_y = false;

		$(window).on('touchstart', function(event) {

			ws_this.touching_time = new Date();

			if(ws_this.touching_timeout !== false) {

				clearTimeout(ws_this.touching_timeout);
			}

			ws_this.touching = true;
		});

		$(window).on('touchend', function(event) {

			var touch_duration = new Date() - ws_this.touching_time;

			if(touch_duration < 100) {

				// Quick touch
				ws_this.touching = false;

			} else {

				// Prolonged touch
				ws_this.touching_timeout = setTimeout(function() {

					ws_this.touching = false;

				}, 2000);
			}
		});

		$(window).on('scroll', function(event) {

			if(ws_this.mobile && !ws_this.touching) { return; }

			if(!ws_this.conversational_scroll_detect) { return; }

			var scroll_reference = ws_this.mobile ? $(window).scrollTop() + ws_this.mobile_scroll_reference_offset : $(window).scrollTop() + (window.innerHeight / 2);
			var section_obj_focus_new = false;
			var section_scroll_distance_min = false;

			$('fieldset', ws_this.form_canvas_obj).each(function() {

				var section_reference = ws_this.mobile ? ws_this.form_conversational_section_top_position($(this)) : ws_this.form_conversational_section_middle_position($(this));

				var section_scroll_distance = Math.abs(scroll_reference - section_reference);

				if((section_scroll_distance < section_scroll_distance_min) || (section_scroll_distance_min === false)) {

					section_scroll_distance_min = section_scroll_distance;

					section_obj_focus_new = $(this);
				}
			});

			if(section_obj_focus_new && (section_obj_focus_new[0] !== ws_this.conversational_section_obj_focus[0])) {

				ws_this.conversational_section_obj_focus = section_obj_focus_new;

				ws_this.form_conversational_focus_section(false, false);
			}
		});
	}

	// Initialize sections
	$.WS_Form.prototype.form_conversational_section = function() {

		var ws_this = this;

		// Set inactive / active
		$('.wsf-section:visible:first').addClass('wsf-form-conversational-active');
		$('.wsf-section:not(:visible:first)').addClass('wsf-form-conversational-inactive');

		// Section clicked
		this.form_canvas_obj.on('click', '.wsf-section', function(e) {

			if($(e.target).filter('.wsf-field, .wsf-button, [tabindex="0"], label').length) { return; }

			if(this === ws_this.conversational_section_obj_focus[0]) { return; }

			ws_this.conversational_section_obj_focus = $(this);

			ws_this.form_conversational_focus_section(true, false);
		});

		// Repeatable sections
		this.form_canvas_obj.on('click', '[data-action="wsf-section-add-button"], [data-action="wsf-section-add-icon"], [data-action="wsf-section-delete-button"], [data-action="wsf-section-delete-icon"]', function() {

			ws_this.form_conversational_focus_section(false, false);
		});
	}

	// Initialize sections
	$.WS_Form.prototype.form_conversational_field = function() {

		var ws_this = this;

		// Field changed
		this.form_canvas_obj.on('change', '.wsf-field-wrapper:not([data-type="signature"],[data-type="rating"],[data-type="color"],[data-type="checkbox"],[data-type="price_checkbox"],[data-type="radio"],[data-type="price_radio"],[data-type="range"],[data-type="price_range"],[data-type="hidden"]) .wsf-field:not([data-hidden],[data-hidden-section],[data-hidden-group])', function(event) {

			ws_this.form_conversational_field_changed($(this));
		});

		// Prevent select option clicking causing a section change
		this.form_canvas_obj.on('change', '.wsf-field-wrapper select:not([data-hidden],[data-hidden-section],[data-hidden-group])', function(event) {

			event.stopPropagation();
		});

		// Field changed - Rating
		this.form_canvas_obj.on('mouseup', '[data-type="rating"] li', function(event) {

			event.stopPropagation();

			ws_this.form_conversational_field_changed($(this));
		});

		// Field changed - Radio
		this.form_canvas_obj.on('mouseup', '.wsf-field[type="radio"] + label', function(event) {

			event.stopPropagation();

			// Disable focus processing
			ws_this.focus_process = false;

			ws_this.form_conversational_field_changed($(this));

			// Re-enable focus processing
			setTimeout(function() {

				ws_this.focus_process = true;

			}, 500);
		});
	}

	$.WS_Form.prototype.form_conversational_field_changed = function(field_obj) {

		var field_wrapper = field_obj.closest('.wsf-field-wrapper');

		// Get section change was made in
		var section = field_wrapper.closest('.wsf-section');

		// Check section is current section
		if(section[0] !== this.conversational_section_obj_focus[0]) { return; }

		var field_wrapper_last = $('.wsf-field-wrapper:visible:not([data-type="divider"],[data-type="html"],[data-type="message"],[data-type="section_icons"],[data-type="texteditor"])', section).last();

		if(field_wrapper[0] === field_wrapper_last[0]) {

			this.form_conversational_section_next();

		} else {

			var field_wrapper_next = field_obj.closest('.wsf-field-wrapper:visible', section).next();

			if(field_wrapper_next.length) {

				var field_next_obj = $('.wsf-field', field_wrapper_next).first();

				if(field_next_obj.length) {

					field_next_obj.trigger('focus', 'wsf-internal');
				}
			}
		}
	}

	$.WS_Form.prototype.form_conversational_section_middle_position = function(obj) {

		return (obj.offset().top) + (obj.outerHeight() / 2);
	}

	$.WS_Form.prototype.form_conversational_section_top_position = function(obj) {

		return (obj.offset().top);
	}

})(jQuery);