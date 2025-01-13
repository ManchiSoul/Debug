(function($) {

	'use strict';

	// Styler
	$.WS_Form.prototype.styler = function() {

		if($.WS_Form.styler_rendered) { return; }

		// Get style ID
		var style_id = parseInt(this.get_object_meta_value(this.form, this.conversational ? 'style_id_conv' : 'style_id', 0), 10);

		if(style_id > 0) {

			var form_id = parseInt(this.get_query_var(this.conversational ? 'wsf_preview_conversational_form_id' : 'wsf_preview_form_id'), 10)

			// Check if preview form ID matches form being rendered
			if(form_id == this.form.id) {

				// Render styler
				this.styler_init(style_id);
			}

			// Check style attribute is on form (Not included if form loaded dynamically via block editor)
			if(typeof(this.form_obj.attr('data-wsf-style-id')) === 'undefined') {

				// Add style ID attribute
				this.form_obj.attr('data-wsf-style-id', style_id);
			}
		}
	}

	// Styler - Init
	$.WS_Form.prototype.styler_init = function(style_id) {

		var ws_this = this;

		// Is styler visible on the public side?
		if(!ws_form_settings.styler_visible_public) { return false; }

		this.styler_style_id = style_id;
		this.styler_style = {};
		this.styler_rule = false;
		this.styler_undos = [];
		this.styler_style_element = false;

		this.styler_panel_pos = ws_form_settings.rtl ? 'right' : 'left';
		this.styler_panel_pin = true;
		this.styler_panel_drag = false;
		this.styler_panel_offset_x = 0;
		this.styler_panel_offset_y = 0;
		this.styler_panel_x = 0;
		this.styler_panel_y = 0;
		this.styler_panel_lock_threshold = 2;

		this.styler_changes_made = false;

		this.styler_coloris_swatches = [];

		this.styler_obj_value_last = false;

		// Render styler core
		if(!$.WS_Form.styler_rendered) {

			// Set body padding CSS var to help with left and right pinning
			this.styler_set_pin_offset();

			// Set Coloris swatches
			this.styler_set_coloris_swatches();

			// Get rule
			this.styler_get_rule();

			// Add styler
			this.styler_add();

			// Panel position
			this.styler_panel_position();

			// Panel dragging
			this.styler_panel_dragging();

			// Show loader
			this.styler_loader_show();

			// Set styler element
			this.styler_style_element 

			// Get style data
			this.styler_api_call('style/' + ws_this.styler_style_id + '/', 'GET', false, function(style) {

				var styler_obj = $('#wsf-styler');

				// Set style
				ws_this.styler_style = style;

				// Set label
				ws_this.styler_label_update();

				// Set ID
				$('.wsf-styler-style-id', styler_obj).html(ws_this.language('styler_id') + ': ' + ws_this.esc_html(ws_this.styler_style.id));

				// UI
				var styler_html = ws_this.styler_ui_html();

				// Datalists
				styler_html += ws_this.styler_datalists();

				// Add to main panel
				$('.wsf-styler-panel-main', styler_obj).append(styler_html);

				// Settings
				var styler_html = ws_this.styler_settings_html();

				// Add to settings panel
				$('.wsf-styler-panel-setting', styler_obj).append(styler_html);

				// Add events
				ws_this.styler_events();

				// Initialize variable search
				ws_this.styler_search();

				// Hide loader
				ws_this.styler_loader_hide();
			});

			// Mark styler as rendered
			$.WS_Form.styler_rendered = true;
		}
	}

	// Styler - Coloris swatches
	$.WS_Form.prototype.styler_set_coloris_swatches = function() {

		var palette = window.wsf_form_json_config.styler.palette;

		if(!palette) { return; }

		// Process palette
		for(var palette_index in palette) {

			if(!palette.hasOwnProperty(palette_index)) { continue; }

			var swatch = palette[palette_index];

			this.styler_coloris_swatches.push(swatch.color);
		}
	}

	// Styler - Set pin offset
	$.WS_Form.prototype.styler_set_pin_offset = function() {

		var html_padding_left = parseInt($('html').css('padding-left'), 10) || 0;
		var html_padding_right = parseInt($('html').css('padding-right'), 10) || 0;
		var body_padding_left = parseInt($('body').css('padding-left'), 10) || 0;
		var body_padding_right = parseInt($('body').css('padding-right'), 10) || 0;

		var total_horizontal_padding = html_padding_left + html_padding_right + body_padding_left + body_padding_right;

		$('body').css('--wsf-styler-body-padding', total_horizontal_padding + 'px');
	}

	// Styler - Dragging
	$.WS_Form.prototype.styler_panel_dragging = function() {

		var ws_this = this;

		var styler_obj = $('#wsf-styler');

		// Get panel obj
		var panel_wrapper_obj = $('.wsf-styler-panel-wrapper', styler_obj);

		// Get handle obj
		var handle_obj = $('.wsf-styler-panel header', styler_obj);

		handle_obj.on('mousedown', function(e) {

			// Store offsets
			ws_this.styler_panel_offset_x = e.clientX - panel_wrapper_obj[0].offsetLeft;
			ws_this.styler_panel_offset_y = e.clientY - panel_wrapper_obj[0].offsetTop;

			// Change cursor
			handle_obj.css('cursor', 'grabbing');

			// Set positioning
			ws_this.styler_pos = 'float';
			ws_this.styler_pin = false;
			ws_this.styler_panel_position();

			// Set dragging to true
			ws_this.styler_panel_drag = true;
		});

		$(document).on('mousemove', function(e) {

			if(!ws_this.styler_panel_drag) { return; }

			// Calculate new position
			ws_this.styler_panel_x = e.clientX - ws_this.styler_panel_offset_x;
			ws_this.styler_panel_y = e.clientY - ws_this.styler_panel_offset_y;

			// Calculate bounds
			var styler_panel_x_lock_left = ws_this.styler_panel_lock_threshold;
			var styler_panel_x_lock_right = $(window).width() - panel_wrapper_obj.outerWidth() - ws_this.styler_panel_lock_threshold;

			// Locking
			var styler_pos_old = ws_this.styler_panel_pos;
			if(ws_this.styler_panel_x < styler_panel_x_lock_left) {

				ws_this.styler_panel_pos = 'left';

			} else if(ws_this.styler_panel_x > styler_panel_x_lock_right) {

				ws_this.styler_panel_pos = 'right';

			} else {

				ws_this.styler_panel_pos = 'float';
			}

			// Set the new position
			if(ws_this.styler_panel_pos == 'float') {

				panel_wrapper_obj.css('left', ws_this.styler_panel_x + 'px');
				panel_wrapper_obj.css('top', ws_this.styler_panel_y + 'px');

			} else {

				panel_wrapper_obj.css('left', '');
				panel_wrapper_obj.css('top', '');
			}

			// Panel position
			if(styler_pos_old != ws_this.styler_panel_pos) {

				ws_this.styler_panel_position();
			}
		});

		$(document).on('mouseup', function(e) {

			if(!ws_this.styler_panel_drag) { return; }

			// Change cursor
			handle_obj.css('cursor', 'grab');

			// Set dragging to false
			ws_this.styler_panel_drag = false;
		});
	}


	// Styler - Positioning
	$.WS_Form.prototype.styler_panel_position = function() {

		var styler_obj = $('#wsf-styler');

		// Get panel obj
		var panel_wrapper_obj = $('.wsf-styler-panel-wrapper', styler_obj);

		// Position
		switch(this.styler_panel_pos) {

			case 'left' :

				panel_wrapper_obj.addClass('wsf-styler-panel-left').removeClass('wsf-styler-panel-right');
				break;

			case 'right' :

				panel_wrapper_obj.addClass('wsf-styler-panel-right').removeClass('wsf-styler-panel-left');
				break;

			default:

				panel_wrapper_obj.removeClass('wsf-styler-panel-right').removeClass('wsf-styler-panel-left');
		}

		// Pin
		if(this.styler_panel_pin) {

			switch(this.styler_panel_pos) {

				case 'left' :

					$('body').addClass('wsf-styler-panel-left-pin').removeClass('wsf-styler-panel-right-pin');
					break;

				case 'right' :

					$('body').addClass('wsf-styler-panel-right-pin').removeClass('wsf-styler-panel-left-pin');
					break;

				default :

					$('body').removeClass('wsf-styler-panel-left-pin').removeClass('wsf-styler-panel-right-pin');
			}
		}
	}

	// Styler - Add styler to DOM
	$.WS_Form.prototype.styler_add = function() {

		// HTML
		if(ws_form_settings.rtl) {

			$('body').addClass('wsf-styler-rtl');
		}

		// Wrapper
		var styler_html = '<div id="wsf-styler" data-wsf-style-id="' + this.styler_style_id + '">';

		// Panel
		styler_html += this.styler_panel_html();

		styler_html += '</div>';

		// Add styler to body
		$('body').append(styler_html);
	}

	// Styler - Panel HTML
	$.WS_Form.prototype.styler_panel_html = function() {

		var styler_html = '<div class="wsf-styler-panel-wrapper">';

		// Loader
		styler_html += this.styler_loader_html();

		// Panel
		styler_html += '<div class="wsf-styler-panel">';

		// Header
		styler_html += this.styler_header_html();

		// Settings
		styler_html += '<div class="wsf-styler-panel-setting"></div>';

		// Meta
		styler_html += '<div class="wsf-styler-panel-main">'

		// Label
		styler_html += this.styler_label_html();

		// Search
		styler_html += this.styler_search_html();

		// Scheme
		styler_html += this.styler_scheme_html();
		styler_html += '</div>';

		styler_html += '</div>';

		styler_html += '</div>';

		return styler_html;
	}

	// Styler - Loader HTML
	$.WS_Form.prototype.styler_loader_html = function() {

		return '<div class="wsf-styler-loader"></div>';
	}

	// Styler - Loader - Show
	$.WS_Form.prototype.styler_loader_show = function() {

		$('#wsf-styler .wsf-styler-loader').addClass('wsf-styler-loader-on');
	}

	// Styler - Loader - Hide
	$.WS_Form.prototype.styler_loader_hide = function() {

		$('#wsf-styler .wsf-styler-loader').removeClass('wsf-styler-loader-on');
	}

	// Styler - Header HTML
	$.WS_Form.prototype.styler_header_html = function() {

		var styler_html = '<header>';

		styler_html += '<svg class="wsf-styler-logo" x="0" y="0" viewBox="0 0 1500 428"><path fill="#002D5D" d="m215.2 422.9-44.3-198.4c-.4-1.4-.7-3-1-4.6-.3-1.6-3.4-18.9-9.3-51.8h-.6l-4.1 22.9-6.8 33.5-45.8 198.4H69.7L0 130.1h28.1L68 300.7l18.6 89.1h1.8c3.5-25.7 9.3-55.6 17.1-89.6l39.9-170H175l40.2 170.6c3.1 12.8 8.8 42.5 16.8 89.1h1.8c.6-5.9 3.5-20.9 8.7-44.8 5.2-23.9 21.9-95.5 50.1-214.8h27.8l-72.1 292.8h-33.1zM495 349.5c0 24.7-7.1 44-21.3 57.9-14.2 13.9-34.7 20.9-61.5 20.9-14.6 0-27.4-1.7-38.4-5.1-11-3.4-19.6-7.2-25.7-11.3l12.3-21.3c8.3 5.1 5.9 3.6 16.6 7.4 12 4.2 24.3 6.1 36.9 6.1 16.5 0 29.6-4.9 39-14.8 9.5-9.9 14.2-23.1 14.2-39.7 0-13-3.4-23.9-10.2-32.8-6.8-8.9-19.8-19-38.9-30.4-21.9-12.6-36.8-22.7-44.8-30.2-8-7.6-14.2-16-18.6-25.4-4.4-9.4-6.6-20.5-6.6-33.5 0-21.1 7.8-38.5 23.3-52.2 15.6-13.8 35.4-20.6 59.4-20.6 25.8 0 45.2 6.7 62.6 17.8L481 163.6c-16.2-9.9-33.3-14.8-51.4-14.8-16.6 0-29.8 4.5-39.6 13.4-9.9 8.9-14.8 20.6-14.8 35.2 0 13 3.3 23.8 10 32.5s20.9 19.3 42.6 31.7c21.3 12.8 35.9 23 43.7 30.6 7.9 7.6 13.7 16.1 17.6 25.4 4 9.2 5.9 19.9 5.9 31.9z"/><path fill="#ACA199" d="M643.8 152.8h-50.2V423h-27.8V152.8H525l.2-22.3h40.3l.3-25.5c0-37.2 3.6-60.9 13.4-77.2C589.5 10.7 606.6 0 630.5 0h28.9v23.6c-6.4 0-18.9.2-27.3.4-13.9.2-20.1 4.5-25.1 9.7-4.9 5.2-7.5 11.5-9.9 23.2-2.4 11.7-3.5 27.9-3.5 48.6v24.6h50.2v22.7zM857.1 275.8c0 49.3-8.5 87-25.6 113.2-17 26.2-41.4 39.3-73.1 39.3-31.3 0-55.3-13.1-72-39.3-16.7-26.2-25-63.9-25-113.2 0-100.9 32.7-151.4 98.1-151.4 30.7 0 54.7 13.2 71.8 39.7 17.2 26.4 25.8 63.7 25.8 111.7zm-166.4 0c0 42.3 5.5 74.2 16.6 95.8 11 21.6 28.3 32.4 51.7 32.4 45.9 0 68.9-42.7 68.9-128.2 0-84.7-23-127.1-68.9-127.1-24 0-41.4 10.6-52.2 31.8-10.7 21.3-16.1 53.1-16.1 95.3zM901.8 196.5c0-35.5 42.9-71.7 88.5-72 30.9-.3 42 8.6 53.2 13.7l-13.9 21.6c-9.7-5.1-18.8-9.2-39.9-9.9-13.3-.4-24.1 1.4-35.9 9.3-9.7 6.4-20.4 12.9-23.6 40.8-2.2 19-.8 45.9-.8 67.8V423h-28.1M1047.6 191.4c5.6-48.2 49.8-67.2 80.6-67.2 17.7 0 39.6 6.4 50.2 14.5 9.5 7.2 14.7 13.4 20.3 32.2 7.7-18 13.9-23.4 25.1-31.3 11.2-7.9 25.8-14.9 43.7-14.9 24.2 0 48.4 7.5 62.9 28.5 11.6 16.7 16.8 41 16.8 78.4V423h-27.8V223.5c.7-56.9-14.3-75.2-52-75.2-18.7 0-32.2 4.7-42.2 21.9-9.8 17-14.3 47.9-14.3 81.3v171.4h-27.8V223.5c0-24.8-3.8-43.3-11.5-55.5s-26.7-18.6-42.8-18.6c-21.3 0-35.6 10.4-45.3 28-9.7 17.6-8.6 45.1-8.6 84.6v160.9h-28.1"/><circle fill="#ACA199" cx="1412.6" cy="149.4" r="25.3"/><circle fill="#ACA199" cx="1412.6" cy="273" r="25.3"/><circle fill="#ACA199" cx="1412.6" cy="395" r="25.3"/><path fill="#ACA199" d="M1449.5 85.4c0-2.2.3-4.3.9-6.4.6-2 1.4-3.9 2.4-5.7 1-1.8 2.3-3.4 3.7-4.8 1.5-1.4 3.1-2.7 4.8-3.7 1.8-1 3.7-1.9 5.7-2.4 2-.6 4.1-.9 6.3-.9s4.3.3 6.4.9c2 .6 3.9 1.4 5.7 2.4 1.8 1 3.4 2.3 4.8 3.7 1.5 1.5 2.7 3.1 3.7 4.8 1 1.8 1.8 3.7 2.4 5.7.6 2 .8 4.2.8 6.4s-.3 4.3-.8 6.3c-.6 2-1.4 3.9-2.4 5.7-1 1.8-2.3 3.4-3.7 4.8-1.5 1.5-3.1 2.7-4.8 3.7-1.8 1-3.7 1.9-5.7 2.4-2 .6-4.2.9-6.4.9s-4.3-.3-6.3-.9c-2-.6-3.9-1.4-5.7-2.4-1.8-1-3.4-2.3-4.8-3.7-1.5-1.4-2.7-3.1-3.7-4.8-1-1.8-1.8-3.7-2.4-5.7s-.9-4.1-.9-6.3zm3.2 0c0 1.9.2 3.8.7 5.6.5 1.8 1.2 3.5 2.1 5 .9 1.6 2 3 3.2 4.2 1.2 1.3 2.6 2.3 4.2 3.3 1.5.9 3.2 1.6 4.9 2.1 1.8.5 3.6.7 5.5.7 2.9 0 5.6-.5 8.1-1.6s4.7-2.6 6.6-4.5c1.9-1.9 3.3-4.1 4.4-6.6 1.1-2.5 1.6-5.3 1.6-8.2 0-1.9-.2-3.8-.7-5.6-.5-1.8-1.2-3.5-2.1-5.1-.9-1.6-2-3-3.2-4.3-1.3-1.3-2.6-2.4-4.2-3.3-1.5-.9-3.2-1.6-5-2.1-1.8-.5-3.6-.8-5.5-.8-2.9 0-5.6.6-8.1 1.7s-4.7 2.6-6.5 4.5c-1.9 1.9-3.3 4.1-4.4 6.7-1 2.6-1.6 5.3-1.6 8.3zm17.3 2.5v7.9h-3.5V75.9h6.4c2.6 0 4.4.5 5.7 1.4 1.2.9 1.8 2.3 1.8 4.1 0 1.4-.4 2.6-1.2 3.6-.8 1-2 1.7-3.6 2 .3.1.5.3.7.6.2.2.4.5.5.8l5.1 7.4h-3.3c-.5 0-.9-.2-1.1-.6l-4.5-6.7c-.1-.2-.3-.3-.5-.4-.2-.1-.5-.2-.9-.2h-1.6zm0-2.6h2.6c.8 0 1.5-.1 2.1-.2.6-.2 1-.4 1.4-.7.3-.3.6-.7.8-1.1.2-.4.2-.9.2-1.5 0-.5-.1-1-.2-1.4-.1-.4-.4-.8-.7-1-.3-.3-.7-.5-1.3-.6-.5-.1-1.2-.2-1.9-.2h-2.9v6.7z"/></svg>';

		styler_html += '<ul>';

		styler_html += '<li data-wsf-styler-action="undo" title="' + this.language('styler_undo') + '"><svg height="16" width="16" viewBox="0 0 16 16"><path fill="#444" d="M8 0c-3 0-5.6 1.6-6.9 4.1l-1.1-1.1v4h4l-1.5-1.5c1-2 3.1-3.5 5.5-3.5 3.3 0 6 2.7 6 6s-2.7 6-6 6c-1.8 0-3.4-0.8-4.5-2.1l-1.5 1.3c1.4 1.7 3.6 2.8 6 2.8 4.4 0 8-3.6 8-8s-3.6-8-8-8z"></path></svg></li>';
		styler_html += '<li data-wsf-styler-action="settings" title="' + this.language('styler_settings') + '"><svg height="16" width="16" viewBox="0 0 16 16"><<path d="M16 9v-2l-1.7-0.6c-0.2-0.6-0.4-1.2-0.7-1.8l0.8-1.6-1.4-1.4-1.6 0.8c-0.5-0.3-1.1-0.6-1.8-0.7l-0.6-1.7h-2l-0.6 1.7c-0.6 0.2-1.2 0.4-1.7 0.7l-1.6-0.8-1.5 1.5 0.8 1.6c-0.3 0.5-0.5 1.1-0.7 1.7l-1.7 0.6v2l1.7 0.6c0.2 0.6 0.4 1.2 0.7 1.8l-0.8 1.6 1.4 1.4 1.6-0.8c0.5 0.3 1.1 0.6 1.8 0.7l0.6 1.7h2l0.6-1.7c0.6-0.2 1.2-0.4 1.8-0.7l1.6 0.8 1.4-1.4-0.8-1.6c0.3-0.5 0.6-1.1 0.7-1.8l1.7-0.6zM8 12c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z"/><path d="M10.6 7.9c0 1.381-1.119 2.5-2.5 2.5s-2.5-1.119-2.5-2.5c0-1.381 1.119-2.5 2.5-2.5s2.5 1.119 2.5 2.5z"/></svg></li>';
		styler_html += '<li data-wsf-styler-action="save" title="' + this.language('styler_save') + '"><svg height="16" width="16" viewBox="0 0 16 16"><path d="M15.791849,4.41655721 C15.6529844,4.08336982 15.4862083,3.8193958 15.2916665,3.625 L12.3749634,0.708260362 C12.1806771,0.513974022 11.916703,0.347234384 11.5833697,0.208260362 C11.2502188,0.0694322825 10.9445781,0 10.666849,0 L1.00003637,0 C0.722343724,0 0.486171803,0.0971614127 0.291703035,0.291630181 C0.0972342664,0.485989492 0.000109339408,0.722124927 0.000109339408,0.999963514 L0.000109339408,15.0002189 C0.000109339408,15.2781305 0.0972342664,15.5142659 0.291703035,15.7086617 C0.486171803,15.902948 0.722343724,16.0002189 1.00003637,16.0002189 L15.0002553,16.0002189 C15.2782033,16.0002189 15.5143023,15.902948 15.7086981,15.7086617 C15.9029844,15.5142659 16.0001093,15.2781305 16.0001093,15.0002189 L16.0001093,5.3334063 C16.0001093,5.05553123 15.9307135,4.75 15.791849,4.41655721 Z M6.66684898,1.66655721 C6.66684898,1.57629159 6.69986853,1.49832166 6.76587116,1.43220957 C6.83180082,1.36638938 6.90995318,1.3334063 7.0002188,1.3334063 L9.00032825,1.3334063 C9.09037496,1.3334063 9.16849083,1.3663164 9.23445698,1.43220957 C9.30060554,1.49832166 9.33358862,1.57629159 9.33358862,1.66655721 L9.33358862,4.99996351 C9.33358862,5.09037507 9.30038663,5.16845447 9.23445698,5.23445709 C9.16849083,5.30024081 9.09037496,5.33326036 9.00032825,5.33326036 L7.0002188,5.33326036 C6.90995318,5.33326036 6.83176433,5.30035026 6.76587116,5.23445709 C6.69986853,5.16834501 6.66684898,5.09037507 6.66684898,4.99996351 L6.66684898,1.66655721 Z M12.0003647,14.6669221 L4.00003637,14.6669221 L4.00003637,10.6667761 L12.0003647,10.6667761 L12.0003647,14.6669221 Z M14.6672503,14.6669221 L13.3336251,14.6669221 L13.3333697,14.6669221 L13.3333697,10.3334063 C13.3333697,10.0554947 13.2362083,9.81950525 13.0418125,9.62496351 C12.8474167,9.43056772 12.6112813,9.33329685 12.3336251,9.33329685 L3.66673952,9.33329685 C3.38893742,9.33329685 3.1527655,9.43056772 2.95829673,9.62496351 C2.76393742,9.81935931 2.66670303,10.0554947 2.66670303,10.3334063 L2.66670303,14.6669221 L1.33333322,14.6669221 L1.33333322,1.33326036 L2.66666655,1.33326036 L2.66666655,5.66670315 C2.66666655,5.94454174 2.76379148,6.18056772 2.95826024,6.37503649 C3.15272901,6.5693958 3.38890093,6.66666667 3.66670303,6.66666667 L9.66699492,6.66666667 C9.94465108,6.66666667 10.1810419,6.5693958 10.3751823,6.37503649 C10.5694687,6.18067717 10.666849,5.94454174 10.666849,5.66670315 L10.666849,1.33326036 C10.7709792,1.33326036 10.9063046,1.36792177 11.0731537,1.43735406 C11.2399663,1.50674985 11.3579611,1.57618214 11.4273933,1.64561442 L14.3547138,4.57286194 C14.4241096,4.64229422 14.4935784,4.76222271 14.5629742,4.93228255 C14.6326254,5.10248832 14.6672138,5.23620841 14.6672138,5.3334063 L14.6672138,14.6669221 L14.6672503,14.6669221 Z" fill="#444"></path></svg></li>';
		styler_html += '<li title="' + this.language('styler_support') + '"><a href="https://wsform.com/knowledgebase/styler/?utm_source=ws_form_pro&utm_medium=styler" target="_blank" class="wsf-styler-logo" title="' + this.language('styler_logo') + '"><svg height="16" width="16" viewBox="0 0 16 16"><path d="M9 11h-3c0-3 1.6-4 2.7-4.6 0.4-0.2 0.7-0.4 0.9-0.6 0.5-0.5 0.3-1.2 0.2-1.4-0.3-0.7-1-1.4-2.3-1.4-2.1 0-2.5 1.9-2.5 2.3l-3-0.4c0.2-1.7 1.7-4.9 5.5-4.9 2.3 0 4.3 1.3 5.1 3.2 0.7 1.7 0.4 3.5-0.8 4.7-0.5 0.5-1.1 0.8-1.6 1.1-0.9 0.5-1.2 1-1.2 2z"></path><path d="M9.5 14c0 1.105-0.895 2-2 2s-2-0.895-2-2c0-1.105 0.895-2 2-2s2 0.895 2 2z"></path></svg></a></li>';

		styler_html += '</ul>';

		styler_html += '</header>';

		return styler_html;
	}

	// Styler - Settings HTML
	$.WS_Form.prototype.styler_settings_html = function() {

		var styler_html = '<fieldset class="wsf-styler-settings">';

		// Get settings
		var settings = window.wsf_form_json_config.styler.meta.setting;

		// Legend
		styler_html += '<div class="wsf-styler-settings-legend"><legend>' + this.esc_html(settings.label) + '</legend><div class="wsf-styler-style-id">' + this.language('styler_id') + ': ' + this.styler_style_id + '</div></div>';

		// Label
		styler_html += '<div class="wsf-styler-setting-wrapper">';
		styler_html += '<label for="wsf-styler-setting-label">' + this.language('styler_label') + '</label>';
		styler_html += '<input id="wsf-styler-setting-label" type="text" placeholder="' + this.language('styler_label_placeholder') + '" value="' + this.esc_attr(this.styler_style.label) + '" />';
		styler_html += '</div>';

		// Process settings
		for(var meta_key in settings.meta) {

			if(!settings.meta.hasOwnProperty(meta_key)) { continue; }

			// Get meta config
			var meta_config = settings.meta[meta_key];

			// Get meta value
			var meta_value = this.get_object_meta_value(this.styler_style, meta_key, '');

			// Wrapper
			styler_html += '<div class="wsf-styler-setting-wrapper">';

			// Input ID
			var id = 'wsf-styler-setting-meta-' + meta_key;

			switch(meta_config.type) {

				case 'checkbox' :

					styler_html += '<input class="wsf-styler-setting-meta" type="checkbox" id="' + this.esc_attr(id) + '" name="' + this.esc_attr(meta_key) + '" data-wsf-styler-setting-type="' + meta_config.type + '"' + ((meta_value == 'on') ? ' checked' : '') + ' />';
					styler_html += '<label for="' + this.esc_attr(id) + '">' + this.esc_html(meta_config.label) + '</label>';
					break;

				default :

					styler_html += '<label for="' + this.esc_attr(id) + '">' + this.esc_html(meta_config.label) + '</label>';
					styler_html += '<input class="wsf-styler-setting-meta" type="text" id="' + this.esc_attr(id) + '" name="' + this.esc_attr(meta_key) + '" data-wsf-styler-setting-type="' + meta_config.type + '" value="' + this.esc_attr(meta_value) + '" />';
			}

			styler_html += '</div>';
		}

		styler_html += '<button class="wsf-styler-settings-close" data-wsf-styler-action="settings-close">' + this.language('styler_close') + '</button<';

		styler_html += '</fieldset>';

		return styler_html;
	}


	// Styler - Label HTML
	$.WS_Form.prototype.styler_label_html = function() {

		var styler_html = '<div class="wsf-styler-label">';

		styler_html += '<legend>' + this.language('styler_loading') + '</legend>';

		styler_html += '<div class="wsf-styler-style-id">' + this.language('styler_id') + ': ' + this.styler_style_id + '</div>';

		styler_html += '</div>';

		return styler_html;
	}

	// Styler - Search HTML
	$.WS_Form.prototype.styler_search_html = function() {

		var styler_html = '<div class="wsf-styler-search">';

		styler_html += '<input type="search" id="styler_search_input" placeholder="' + this.language('styler_search_placeholder') + '" />';

		styler_html += '</div>';

		return styler_html;
	}

	// Styler - Scheme HTML
	$.WS_Form.prototype.styler_scheme_html = function() {

		var styler_html = '<fieldset class="wsf-styler-scheme">';

		styler_html += '<legend>' + this.language('styler_scheme') + '</legend>';

		styler_html += '<input type="radio" name="wsf-styler-scheme" id="wsf-styler-scheme-base" value="base" checked><label for="wsf-styler-scheme-base">' + this.language('styler_scheme_base') + '</label>';
		styler_html += '<input type="radio" name="wsf-styler-scheme" id="wsf-styler-scheme-alt" value="alt"><label for="wsf-styler-scheme-alt">' + this.language('styler_scheme_alt') + '</label>';
		styler_html += '<input type="radio" name="wsf-styler-scheme" id="wsf-styler-scheme-both" value="both"><label for="wsf-styler-scheme-both">' + this.language('styler_scheme_both') + '</label>';

		styler_html += '</fieldset>';

		return styler_html;
	}

	// Styler - UI HTML
	$.WS_Form.prototype.styler_ui_html = function() {

		var styler_html = '<div class="wsf-styler-ui">';

		styler_html += this.styler_children_html(window.wsf_form_json_config.styler.meta);

		styler_html += '</div>';

		return styler_html;
	}

	// Styler - Children HTML
	$.WS_Form.prototype.styler_children_html = function(children, level, group_focus_selector) {

		if(!level) { level = 0; }

		var styler_html = '';

		for(var styler_index in children) {

			// Skip settings
			if(styler_index == 'setting') { continue; }

			if(!children.hasOwnProperty(styler_index)) { continue; }

			var styler_child = children[styler_index];

			var styler_child_content_html = '';

			if(typeof(styler_child.group_focus_selector) !== 'undefined') {

				group_focus_selector = styler_child.group_focus_selector;
			}

			// Check for children
			if(styler_child.children) {

				styler_child_content_html += this.styler_children_html(styler_child.children, level + 1, group_focus_selector);
			}

			// Check for meta
			if(styler_child.meta) {

				styler_child_content_html += this.styler_child_meta_html(styler_child.meta, group_focus_selector);
			}

			if(styler_child_content_html) {

				// Wrapper
				styler_html += '<div class="wsf-styler-child">';

				// Label
				styler_html += '<div class="wsf-styler-child-label"><p>' + this.esc_html(styler_child.label) + '<i></i></p></div>';

				// Content
				styler_html += '<div class="wsf-styler-child-content">' + styler_child_content_html + '</div>';

				styler_html += '</div>';
			}
		}

		return styler_html ? ('<div class="wsf-styler-children wsf-styler-children-' + level + '">' + styler_html + '</div>') : '';
	}

	// Styler - Child meta HTML
	$.WS_Form.prototype.styler_child_meta_html = function(meta, group_focus_selector) {

		var styler_html = '';

		for(var meta_key in meta) {

			if(!meta.hasOwnProperty(meta_key)) { continue; }

			var styler_var = meta[meta_key];

			// Skip calcs
			if(styler_var.type == 'calc') { continue; }

			// Set ID from key
			styler_var.meta_key = meta_key;

			// Check var
			if(
				!styler_var.label ||
				!styler_var.default
			) {
				continue;
			}

			// Has alt?
			var has_alt = (styler_var.type == 'color');

			// Outer wrapper
			styler_html += '<div class="wsf-styler-var-wrapper" data-wsf-styler-var="' + this.esc_attr(styler_var.var) + '">';

			styler_html += '<div class="wsf-styler-var-columns' + (has_alt ? ' wsf-styler-var-columns-has-alt' : '') + '">';

			// Primary variable
			styler_html += this.styler_var_column_html(styler_var, false, group_focus_selector);

			// Secondary variable
			if(has_alt) {

				styler_html += this.styler_var_column_html(styler_var, true, group_focus_selector);
			}
			styler_html += '</div>';

			styler_html += '</div>';
		}

		return styler_html ? ('<div class="wsf-styler-meta">' + styler_html + '</div>') : ''
	}

	// Styler - var column HTML
	$.WS_Form.prototype.styler_var_column_html = function(styler_var, alt, group_focus_selector) {

		// Meta key
		var meta_key = styler_var.meta_key + (alt ? '_alt' : '')

		// Meta key attribute
		var meta_key_attribute = 'wsf-styler-meta-key-' + this.replace_all(meta_key, '_', '-');

		// CSS Variable
		var css_var = styler_var.var + (alt ? '-alt' : '');

		// Value
		var value = this.get_object_meta_value(this.styler_style, meta_key, styler_var.default);

		// Type
		var type = styler_var.type;

		// Datalist
		var datalist = styler_var.datalist ? ' list="' + this.esc_attr(styler_var.datalist) + '"' : '';

		// Wrapper
		var styler_html = '<div class="wsf-styler-var-column' + (alt ? ' wsf-styler-var-column-alt' : '') + '" data-wsf-styler-type="' + type + '" data-wsf-styler-var="' + this.esc_attr(css_var) + '">';

		// Top row
		styler_html += '<div class="wsf-styler-var-top">';

		// Label
		if(!alt) {

			styler_html += '<div class="wsf-styler-var-label"><label for="' + this.esc_attr(meta_key_attribute) + '">' + this.esc_html(styler_var.label) + '</label></div>';

		} else {

			styler_html += '<div class="wsf-styler-var-label"><label for="' + this.esc_attr(meta_key_attribute) + '" class="wsf-styler-var-label-both">' + this.language('styler_alt') + '</label><label class="wsf-styler-var-label-alt">' + this.esc_html(styler_var.label) + '</label></div>';
		}

		// Alt
		if(alt) {

			styler_html += '<div class="wsf-styler-var-top-icon" data-wsf-styler-var-action="alt" title="' + this.language('styler_alt_title') + '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M12.52.55l-5,5h0L.55,12.51l3,3,12-12Zm-4,6,4-4,1,1-4,4.05ZM2.77,3.18A3.85,3.85,0,0,1,5.32,5.73h0A3.85,3.85,0,0,1,7.87,3.18h0A3.82,3.82,0,0,1,5.32.64h0A3.82,3.82,0,0,1,2.77,3.18ZM8.5,2.55h0A2,2,0,0,1,9.78,1.27h0A1.92,1.92,0,0,1,8.5,0h0A1.88,1.88,0,0,1,7.23,1.27h0A1.92,1.92,0,0,1,8.5,2.55Zm-6.36,0h0A1.92,1.92,0,0,1,3.41,1.27h0A1.88,1.88,0,0,1,2.14,0h0A1.92,1.92,0,0,1,.86,1.27h0A2,2,0,0,1,2.14,2.55ZM14.73,6.22h0a1.94,1.94,0,0,1-1.28,1.27h0a1.94,1.94,0,0,1,1.28,1.27h0A1.9,1.9,0,0,1,16,7.49h0A1.9,1.9,0,0,1,14.73,6.22Z"/></div>';
		}

		// Copy
		styler_html += '<div class="wsf-styler-var-top-icon" data-wsf-styler-var-copy="' + this.esc_attr(css_var) + '" title="' + this.language('styler_copy') + ' ' + this.esc_attr(css_var) + '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path class="wsf-styler-svg-copy-unchecked" d="M14.5 0h-10A1.5 1.5 0 003 1.5V3H1.5A1.5 1.5 0 000 4.5v10A1.5 1.5 0 001.5 16h10a1.5 1.5 0 001.5-1.5V13h1.5a1.5 1.5 0 001.5-1.5v-10A1.5 1.5 0 0014.5 0zm-3.188 14.5H1.688a.187.187 0 01-.188-.188V4.688c0-.104.084-.188.188-.188H3v7A1.5 1.5 0 004.5 13h7v1.313a.187.187 0 01-.188.187zm3-3H4.688a.187.187 0 01-.188-.188V1.688c0-.104.084-.188.188-.188h9.625c.103 0 .187.084.187.188v9.624a.187.187 0 01-.188.188z" transform="matrix(1 0 0 -1 0 16)"/><path class="wsf-styler-svg-copy-checked" d="M7.3 14.2l-7.1-5.2 1.7-2.4 4.8 3.5 6.6-8.5 2.3 1.8z"/></svg></div>';

		// Undo
		styler_html += '<div class="wsf-styler-var-top-icon" data-wsf-styler-var-action="undo" title="' + this.language('styler_undo') + '" data-wsf-styler-var-undo="' + this.esc_attr(value) + '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="#444" d="M8 0c-3 0-5.6 1.6-6.9 4.1l-1.1-1.1v4h4l-1.5-1.5c1-2 3.1-3.5 5.5-3.5 3.3 0 6 2.7 6 6s-2.7 6-6 6c-1.8 0-3.4-0.8-4.5-2.1l-1.5 1.3c1.4 1.7 3.6 2.8 6 2.8 4.4 0 8-3.6 8-8s-3.6-8-8-8z"></path></svg></div>';

		// Add to undos object
		this.styler_undos.push({meta_key: meta_key, meta_value: value});

		styler_html += '</div>';

		// Bottom row
		styler_html += '<div class="wsf-styler-var-bottom">';

		// Process by type
		switch(type) {

			case 'color' :

				// Hidden input (Used for Coloris picker)
				styler_html += '<input type="text" id="' + this.esc_attr(meta_key_attribute) + '-coloris" class="wsf-styler-var-input-hidden" value="' + this.esc_attr(value) + '" data-coloris' + (alt ? ' data-coloris-alt' : '') + '>';

				// Input (Actual value)
				styler_html += '<input type="text" id="' + this.esc_attr(meta_key_attribute) + '" class="wsf-styler-var-input wsf-styler-var-input-color" value="' + this.esc_attr(value) + '" data-wsf-styler-var="' + this.esc_attr(css_var) + '" data-wsf-styler-meta-key="' + this.esc_attr(meta_key) + '"' + (group_focus_selector ? ' data-wsf-styler-group-focus-selector="' + this.esc_attr(group_focus_selector) + '"' : '') + '>';

				// Color preview
				styler_html += '<div class="wsf-styler-var-color-preview" style="color:var(' + css_var + ')"><button aria-label="' + this.language('styler_pick_color') + '">"' + this.language('styler_pick_color') + '"</button></div>';

				break;

			case 'size' :

				// Hidden input (Actual value)
				styler_html += '<input type="text" id="' + this.esc_attr(meta_key_attribute) + '-input" class="wsf-styler-var-input-hidden" value="' + this.esc_attr(value) + '" data-wsf-styler-var="' + this.esc_attr(css_var) + '" data-wsf-styler-meta-key="' + this.esc_attr(meta_key) + '">';

				// Size wrapper
				styler_html += '<div class="wsf-styler-var-size-wrapper">';

				// Input
				styler_html += '<input type="text" id="' + this.esc_attr(meta_key_attribute) + '" class="wsf-styler-var-input wsf-styler-var-size-value"' + (group_focus_selector ? ' data-wsf-styler-group-focus-selector="' + this.esc_attr(group_focus_selector) + '"' : '') + '>';

				// Unit
				styler_html += '<select id="' + this.esc_attr(meta_key_attribute) + '-select" class="wsf-styler-var-size-unit">';

				styler_html += '<option value=""></option>';

				// Units
				var styler_units = this.styler_units();

				for(var styler_unit_index in styler_units) {

					if(!styler_units.hasOwnProperty(styler_unit_index)) { continue; }

					var styler_unit = styler_units[styler_unit_index];

					styler_html += '<option value="' + styler_unit + '">' + styler_unit + '</option>';
				}

				styler_html += '</select>';

				styler_html += '</div>';

				// Bounds
				var bounds_attributes = [];
				if(typeof(styler_var.px_min) !== 'undefined') { bounds_attributes.push('data-wsf-styler-px-min="' + styler_var.px_min + '"'); }
				if(typeof(styler_var.px_max) !== 'undefined') { bounds_attributes.push('data-wsf-styler-px-max="' + styler_var.px_max + '"'); }
				if(typeof(styler_var.px_step) !== 'undefined') { bounds_attributes.push('data-wsf-styler-px-step="' + styler_var.px_step + '"'); }

				// Range slider
				styler_html += '<input type="range" id="' + this.esc_attr(meta_key_attribute) + '-range" class="wsf-styler-var-size-range"' + (bounds_attributes ? ' ' + bounds_attributes.join(' ') : '') + '>';

				break;

			case 'checkbox' :

				// Hidden input (Actual value)
				styler_html += '<input type="text" id="' + this.esc_attr(meta_key_attribute) + '-input" class="wsf-styler-var-input-hidden" value="' + this.esc_attr(value) + '" data-wsf-styler-var="' + this.esc_attr(css_var) + '" data-wsf-styler-meta-key="' + this.esc_attr(meta_key) + '">';

				// Input (Checkbox)
				styler_html += '<input type="checkbox" id="' + this.esc_attr(meta_key_attribute) + '" class="wsf-styler-var-input wsf-styler-var-input-checkbox" data-checkbox' + (group_focus_selector ? ' data-wsf-styler-group-focus-selector="' + this.esc_attr(group_focus_selector) + '"' : '') + '>';

				break;

			case 'select' :

				// Select
				styler_html += '<select id="' + this.esc_attr(meta_key_attribute) + '" class="wsf-styler-var-input wsf-styler-var-input-select" data-select' + (group_focus_selector ? ' data-wsf-styler-group-focus-selector="' + this.esc_attr(group_focus_selector) + '"' : '') + ' data-wsf-styler-var="' + this.esc_attr(css_var) + '" data-wsf-styler-meta-key="' + this.esc_attr(meta_key) + '">';

				// Get options
				if(styler_var.options) {

					for(var option_index in styler_var.options) {

						if(!styler_var.options.hasOwnProperty(option_index)) { continue; }

						var option = styler_var.options[option_index];

						var option_selected = (value == option.value);

						styler_html += '<option value="' + this.esc_attr(option.value) + '"' + (option_selected ? ' selected' : '') + '>' + this.esc_html(option.text) + '</option>';
					}
				}

				styler_html += '</select>';

				break;

			default :

				// Input (Actual value)
				styler_html += '<input type="text" id="' + this.esc_attr(meta_key_attribute) + '" class="wsf-styler-var-input" value="' + this.esc_attr(value) + '" data-wsf-styler-var="' + this.esc_attr(css_var) + '" data-wsf-styler-meta-key="' + this.esc_attr(meta_key) + '"' + (group_focus_selector ? ' data-wsf-styler-group-focus-selector="' + this.esc_attr(group_focus_selector) + '"' : '') + datalist + '>';
		}

		styler_html += '</div>';

		styler_html += '</div>';

		return styler_html;
	};

	// Styler - Units
	$.WS_Form.prototype.styler_units = function() {

		return ['em', 'px', 'rem', '%'];
	}

	// Styler - Events
	$.WS_Form.prototype.styler_events = function() {

		var ws_this = this;

		var styler_obj = $('#wsf-styler');

		var styler_units = this.styler_units();

		// Value focus
		$('input,select:not(.wsf-styler-var-size-unit)', styler_obj).on('focus', function() {

			if(
				(ws_this.styler_obj_value_last !== false) &&
				(ws_this.styler_obj_value_last[0] != $(this)[0])
			) {
				ws_this.styler_obj_value_last = false;
			}
		});

		// Scheme
		$('[name="wsf-styler-scheme"]', styler_obj).on('change', function() {

			ws_this.styler_scheme_process(true);
		});

		this.styler_scheme_visible(false);

		$('[id="wsf-styler-setting-meta-alt"]', styler_obj).on('change', function() {

			ws_this.styler_scheme_visible(true);
		});
		// Icon - Undo
		$('[data-wsf-styler-action="undo"]', styler_obj).on('click', function() {

			if(confirm(ws_this.language('styler_undo_confirm'))) {

				ws_this.styler_undo();
			}
		});

		// Focus
		$('[data-wsf-styler-group-focus-selector]', styler_obj).on('focus', function() {

			ws_this.styler_group_focus_selector($(this));
		});

		// Icon - Settings
		$('[data-wsf-styler-action="settings"]', styler_obj).on('click', function() {

			var panel_settings_obj = $('.wsf-styler-panel-setting', styler_obj);
			var panel_main_obj = $('.wsf-styler-panel-main', styler_obj);

			if($(this).hasClass('wsf-styler-editing')) {

				$(this).removeClass('wsf-styler-editing');
				panel_settings_obj.hide();
				panel_main_obj.show();

			} else {

				$(this).addClass('wsf-styler-editing');
				panel_settings_obj.show();
				panel_main_obj.hide();
			}
		});

		// Buton - Settings = Close
		$('[data-wsf-styler-action="settings-close"]', styler_obj).on('click', function() {

			$('[data-wsf-styler-action="settings"]', styler_obj).removeClass('wsf-styler-editing');
			$('.wsf-styler-panel-setting', styler_obj).hide();
			$('.wsf-styler-panel-main', styler_obj).show();
		});

		// Icon - Save
		$('[data-wsf-styler-action="save"]', styler_obj).on('click', function() {

			// Show loader
			ws_this.styler_loader_show();

			// Build params
			var params = {

				style: ws_this.styler_style
			};

			// Save style
			ws_this.styler_api_call('style/' + ws_this.styler_style_id + '/put/', 'POST', params, function() {

				// Hide loader
				ws_this.styler_loader_hide();

				// Reset change made
				ws_this.styler_change_made_reset();

			}, function() {

				// Hide loader
				ws_this.styler_loader_hide();

				// Reset change made
				ws_this.styler_change_made_reset();
			});

		});

		// Setting - Label
		$('input#wsf-styler-setting-label', styler_obj).on('change paste input', function() {

			// Store to style
			ws_this.styler_style.label = $(this).val();

			// Set label
			ws_this.styler_label_update();

			// Change made
			ws_this.styler_change_made();
		});

		// Setting - Change
		$('input.wsf-styler-setting-meta', styler_obj).on('change paste input', function() {

			var meta_key = $(this).attr('name');

			switch($(this).attr('data-wsf-styler-setting-type')) {

				case 'checkbox' :

					var meta_value = $(this).is(':checked') ? 'on' : '';
					break;

				default :

					var meta_value = $(this).val();
			}

			// Store to style
			ws_this.styler_style.meta[meta_key] = meta_value;

			// Change made
			ws_this.styler_change_made();
		});

		// Child
		$('.wsf-styler-child-label', styler_obj).on('click', function(e) {

			e.preventDefault();
			e.stopPropagation();

			var child_wrapper = $(this).closest('.wsf-styler-child');

			if(child_wrapper.hasClass('wsf-styler-child-active')) {

				child_wrapper.removeClass('wsf-styler-child-active');
				$('.wsf-styler-child.wsf-styler-child-active', child_wrapper).removeClass('wsf-styler-child-active');

			} else {

				child_wrapper.addClass('wsf-styler-child-active');
			}
		});

		// Build Coloris args
		var args_coloris = {

			alpha: true,
			format: 'mixed',
			formatToggle: true,
			swatches: false,
			theme: 'default',
			themeMode: 'auto',
			rtl: ws_form_settings.rtl ? true : false,
			wrap: false
		};

		// Events by type
		$('.wsf-styler-var-column', styler_obj).each(function() {

			// Copy
			ws_this.styler_event_copy($(this));

			// Undo
			ws_this.styler_event_undo($(this));

			// Process by type
			switch(ws_this.styler_get_type($(this))) {

				case 'color' :

					// Coloris input
					$('input[data-coloris]', $(this)).each(function() {

						// Configure Coloris component
						Coloris.setInstance('#' + $(this).attr('id'), args_coloris);

						// Input event
						$(this).on('change input', function() {

							// Get value
							var value = $(this).val();

							// Get input
							var obj_input = ws_this.styler_get_input($(this));

							// Set value of input
							obj_input.val(value).trigger('change');
						});
					});

					// Color previews
					$('.wsf-styler-var-color-preview', $(this)).on('click', function() {

						// Get Coloris obj
						var obj_input = ws_this.styler_get_input_hidden($(this));

						// Group focus selector
						ws_this.styler_group_focus_selector(ws_this.styler_get_input($(this)));

						// Set swatches
						args_coloris.swatches = ws_this.styler_args_coloris_swatches(obj_input);

						// Configure Coloris component
						Coloris.setInstance('#' + obj_input.attr('id'), args_coloris);

						// Click Coloris to activate it
						obj_input[0].dispatchEvent(new Event('click', { bubbles: true }));
					});

					// Create alternative color
					$('[data-wsf-styler-var-action="alt"]', $(this)).on('click', function() {

						// Get columns
						var columns_obj = $(this).closest('.wsf-styler-var-columns');

						// Get first column
						var column_obj = $('.wsf-styler-var-column:not(.wsf-styler-var-column-alt)', columns_obj);

						// Get input
						var obj_input = ws_this.styler_get_input(column_obj);

						// Get color
						var color = obj_input.val();

						// Get alt color
						var color_alt = ws_this.color_to_color_alt(color);

						// Get alt input
						var input_alt_obj = ws_this.styler_get_input($(this));

						// Set alt color
						input_alt_obj.val(color_alt).trigger('change');
					});

					// Input event
					$('input[data-wsf-styler-var]', $(this)).each(function() {

						// Set Coloris value to match
						ws_this.styler_coloris_set($(this));

						$(this).on('change input paste', function() {

							// Set Coloris value to match
							ws_this.styler_coloris_set($(this));

							// Update CSS var
							ws_this.styler_var_set($(this));
						});
					});

					break;

				case 'size' :

					var obj_value = $('.wsf-styler-var-size-value', $(this));
					var obj_unit = $('.wsf-styler-var-size-unit', $(this));

					// Initialize
					ws_this.styler_event_size_init($(this), styler_units);

					// Get wrapper
					var obj_wrapper = ws_this.styler_get_column($(this));

					// Input events
					$('.wsf-styler-var-size-value, .wsf-styler-var-size-unit', $(this)).on('change input paste', function() {

						ws_this.styler_event_size_change(obj_wrapper, styler_units);
					});

					// Temporarily don't process unit change events if the user changes an input
					obj_value.on('input paste', function() {

						ws_this.styler_obj_value_last = $(this);
					});

					// Units
					obj_unit.on('change', function() {

						ws_this.styler_event_size_unit_change($(this));
					});

					// Input input or change event
					$('input[data-wsf-styler-var]', $(this)).on('change input', function() {

						// Update CSS var
						ws_this.styler_var_set($(this));
					});

					// Range
					$('.wsf-styler-var-size-range', $(this)).on('input', function() {

						// Get value
						var value = $(this).val();

						// Set value
						$('.wsf-styler-var-size-value', obj_wrapper).val(value);

						// Get unit
						var unit = (isNaN(value) ? '' : $('.wsf-styler-var-size-unit', obj_wrapper).val());

						// Set hidden value
						$('.wsf-styler-var-input-hidden', obj_wrapper).val(value + unit).trigger('change');
					});

					break;

				case 'checkbox' :

					// Get wrapper
					var obj_wrapper = ws_this.styler_get_column($(this));

					// Get Coloris obj
					var obj_input = ws_this.styler_get_input_hidden($(this));

					// Get checkbox
					var obj_checkbox = $('[data-checkbox]', obj_wrapper);

					// Set initial 
					obj_checkbox.prop('checked', (obj_input.val() == '1'));

					// Checkbox change
					obj_checkbox.on('change', function() {

						obj_input.val(obj_checkbox.is(':checked') ? '1' : '0').trigger('change');
					})

					// Input event
					obj_input.on('change', function() {

						// Set checkbox
						obj_checkbox.prop('checked', ($(this).val() == '1'));

						// Update CSS var
						ws_this.styler_var_set($(this));
					});

					break;

				case 'select' :

					// Get select
					var obj_select = ws_this.styler_get_select($(this));

					// Checkbox change
					obj_select.on('change', function() {

						// Update CSS var
						ws_this.styler_var_set($(this));
					})

					break;

				default :

					// Input input or change event
					$('input[data-wsf-styler-var]', $(this)).on('change input', function() {

						// Update CSS var
						ws_this.styler_var_set($(this));
					});
				}
		});
	}

	$.WS_Form.prototype.styler_args_coloris_swatches = function(obj) {

		if(typeof(obj.attr('data-coloris-alt')) === 'undefined') {

			return this.styler_coloris_swatches;

		} else {

			// Get base color ID
			var color_alt_id = obj.attr('id');
			var color_id = color_alt_id.replace('-alt', '');

			// Get alt swatches
			return this.color_alt_coloris_swatches($('#' + color_id).val());
		}
	}

	// Styler - Get preview form element
	$.WS_Form.prototype.styler_get_form_obj = function() {

		return $('form.wsf-form-canvas[data-preview]');
	}

	// Styler - var event copy
	$.WS_Form.prototype.styler_group_focus_selector = function(obj) {

		// Find preview form
		var form_obj = this.styler_get_form_obj();
		if(!form_obj.length) { return; }

		// If there are not tabs, skip
		if(!$('.wsf-group-tabs', form_obj).length) { return; }

		// Get field type
		var group_focus_selector = obj.attr('data-wsf-styler-group-focus-selector');

		// Find first instance of field type in form
		var field_obj_wrapper = $(this.esc_attr(group_focus_selector), form_obj);
		if(!field_obj_wrapper.length) { return; }

		// If a field matching the selector is already visible, don't change tabs
		if(field_obj_wrapper.find(':visible').length) { return; }

		// Find group
		var group_obj = this.get_group(field_obj_wrapper.first());
		if(!group_obj.length) { return; }

		// Find group tab
		var tab_obj = $('a[href="#' + group_obj.attr('id') + '"]', form_obj);
		if(!tab_obj.length) { return; }

		// Click group tab
		tab_obj.trigger('click');
	}

	// Styler - var event copy
	$.WS_Form.prototype.styler_event_copy = function(obj) {

		$('[data-wsf-styler-var-copy]', obj).on('click', function(e) {

			// Show checkmark
			$(this).addClass('wsf-styler-svg-copy-check');

			// Copy
			var copy_to_obj = $('<input>');
			$('body').append(copy_to_obj);
			copy_to_obj.val($(this).attr('data-wsf-styler-var-copy')).trigger('select');
			document.execCommand('copy');
			copy_to_obj.remove();

			// Hide checkmark
			var ws_this = $(this);
			setTimeout(function() {

				ws_this.removeClass('wsf-styler-svg-copy-check');

			}, 2000);
		});
	}

	// Styler - var event undo
	$.WS_Form.prototype.styler_event_undo = function(obj) {

		var ws_this = this;

		$('[data-wsf-styler-var-action="undo"]', obj).on('click', function() {

			// Get undo value
			var styler_var_undo = $(this).attr('data-wsf-styler-var-undo');

			if(styler_var_undo != '') {

				switch(ws_this.styler_get_type($(this))) {

					case 'checkbox' :

						var styler_var_obj_input = ws_this.styler_get_input($(this));
						styler_var_obj_input.prop('checked', (styler_var_undo == '1')).trigger('change');
						break;

					case 'select' :

						var styler_var_obj_select = ws_this.styler_get_select($(this));
						styler_var_obj_select.val(styler_var_undo).trigger('change');
						break;

					default :

						var styler_var_obj_input = ws_this.styler_get_input($(this));
						styler_var_obj_input.val(styler_var_undo).trigger('change');
				}
			}
		});
	}

	// Styler - Label update
	$.WS_Form.prototype.styler_label_update = function() {

		$('.wsf-styler-label legend', $('#wsf-styler')).html(this.esc_html(this.styler_style.label));
	}

	// Styler - Undo
	$.WS_Form.prototype.styler_undo = function() {

		var styler_obj = $('#wsf-styler');

		for(var styler_undos_index in this.styler_undos) {

			if(!this.styler_undos.hasOwnProperty(styler_undos_index)) { continue; }

			var styler_undo = this.styler_undos[styler_undos_index];

			var meta_key = styler_undo.meta_key;
			var meta_value = styler_undo.meta_value;

			$('[data-wsf-styler-meta-key="' + meta_key + '"]', styler_obj).val(meta_value).trigger('change');
		}
	}

	// Styler - Scheme visibility
	$.WS_Form.prototype.styler_scheme_visible = function(set_scheme_alt) {

		var styler_obj = $('#wsf-styler');

		var scheme_obj = $('fieldset.wsf-styler-scheme', styler_obj);

		if($('[id="wsf-styler-setting-meta-alt"]', styler_obj).is(':checked')) {

			styler_obj.addClass('wsf-styler-has-alt');
			scheme_obj.css('display', 'flex');
			this.form.meta.style_alt = true;
			this.form.meta.style_conv_alt = true;
			this.form_obj.attr('data-wsf-style-has-alt', '');

		} else {

			styler_obj.removeClass('wsf-styler-has-alt');
			scheme_obj.hide();
			this.form.meta.style_alt = false;
			this.form.meta.style_conv_alt = false;
			this.form_obj.removeAttr('data-wsf-style-has-alt');
			if(set_scheme_alt && (typeof(this.styler_scheme_alt_disable) === 'function')) {

				this.styler_scheme_mutation_process = false;
				this.styler_scheme_alt_disable();
			}

			// Set local storage
			localStorage.setItem('wsf_styler_scheme', 'base');
		}

		// Check local storage
		switch(localStorage.getItem('wsf_styler_scheme')) {

			case 'alt' :

				$('#wsf-styler-scheme-alt').prop('checked', true).trigger('change');
				break;

			case 'both' :

				$('#wsf-styler-scheme-both').prop('checked', true).trigger('change');
				break;

			default :

				$('#wsf-styler-scheme-base').prop('checked', true).trigger('change');
		}
	}

	// Styler - Scheme process
	$.WS_Form.prototype.styler_scheme_process = function(set_scheme_alt) {

		var styler_obj = $('#wsf-styler');

		switch($('fieldset.wsf-styler-scheme input:checked', styler_obj).val()) {

			case 'alt' :

				// Styler UI class
				$('.wsf-styler-ui').removeClass('wsf-styler-ui-base').addClass('wsf-styler-ui-alt');

				// Enable alt scheme
				if(set_scheme_alt && (typeof(this.styler_scheme_alt_enable) === 'function')) {

					this.styler_scheme_mutation_process = false;
					this.styler_scheme_alt_enable();
				}

				// Set local storage
				localStorage.setItem('wsf_styler_scheme', 'alt');

				break;

			case 'both' :

				// Styler UI class
				$('.wsf-styler-ui').addClass('wsf-styler-ui-base').addClass('wsf-styler-ui-alt');

				// Set local storage
				localStorage.setItem('wsf_styler_scheme', 'both');

				break;

			default :

				// Styler UI class
				$('.wsf-styler-ui').addClass('wsf-styler-ui-base').removeClass('wsf-styler-ui-alt');

				// Disable alt scheme
				if(set_scheme_alt && (typeof(this.styler_scheme_alt_disable) === 'function')) {

					this.styler_scheme_mutation_process = false;
					this.styler_scheme_alt_disable();
				}

				// Set local storage
				localStorage.setItem('wsf_styler_scheme', 'base');
		}
	}

	// Styler - Scheme base
	$.WS_Form.prototype.styler_scheme_base = function() {

		$('body').removeClass('wsf-styler-scheme-alt');
	}

	// Styler - Scheme alt
	$.WS_Form.prototype.styler_scheme_alt = function() {

		$('body').addClass('wsf-styler-scheme-alt');
	}
	// Styler - Get value unit
	$.WS_Form.prototype.styler_get_value_unit = function(value, styler_units) {

		value = value.trim();

		if(!value) {

			return {

				'value' : '',
				'unit' : ''
			};
		}

		var unit = '';

		// Split unit and value
		var split = value.match(/^([-.\d]+(?:\.\d+)?)(.*)$/);
		if(split) {

			var value = split[1] ? split[1].trim() : value;
			var unit = split[2] ? split[2].trim().toLowerCase() : '';
		}

		return {

			'value' : value,
			'unit' : ((isNaN(value) && (value[0] != '-')) ? '' : unit)
		};
	}

	// Styler - Coloris set
	$.WS_Form.prototype.styler_coloris_set = function(obj) {

		// Attempt to parse color (i.e. convert var() or color-mix() to a color for Coloris)
		var color = this.color_parse(obj.val(), this.styler_style_id);

		// Get Coloris input
		var input_hidden_obj = this.styler_get_input_hidden(obj);

		// Set Coloris to parsed color
		input_hidden_obj.val(color);
	}

	// Styler - Event size init
	$.WS_Form.prototype.styler_event_size_init = function(obj_wrapper, styler_units) {

		// Get hidden input
		var obj_input = this.styler_get_input_hidden(obj_wrapper);

		// Get value
		var value = obj_input.val();

		// Get value and unit
		var value_unit = this.styler_get_value_unit(value, styler_units);

		value = value_unit.value;
		var unit = value_unit.unit;

		// Set inputs
		$('.wsf-styler-var-size-value', obj_wrapper).val(value);
		$('.wsf-styler-var-size-unit', obj_wrapper).val(unit).attr('data-wsf-styler-var-size-unit-old', unit);

		// Set range attributes
		this.styler_event_range_attributes(obj_wrapper);

		// Size render
		this.styler_event_size_render(obj_wrapper);
	}

	// Styler - Event size change
	$.WS_Form.prototype.styler_event_size_change = function(obj_wrapper, styler_units) {

		// Get value obj
		var obj_value = $('.wsf-styler-var-size-value', obj_wrapper);

		// Get unit obj
		var obj_unit = $('.wsf-styler-var-size-unit', obj_wrapper);

		// Get value
		var value = obj_value.val();

		// Get unit
		if(isNaN(value) && (value[0] != '-')) {

			var unit = '';

		} else {

			var unit = obj_unit.val();
		}

		// Get value and unit
		var value_unit = this.styler_get_value_unit(value, styler_units);

		// Check unit
		if(value_unit.unit) {

			for(var styler_unit_index in styler_units) {

				if(!styler_units.hasOwnProperty(styler_unit_index)) { continue; }

				var styler_unit = styler_units[styler_unit_index];

				if(value_unit.unit[0] == styler_unit[0]) {

					// Set unit
					unit = styler_unit;

					// Set unit input
					obj_unit.val(unit).attr('data-wsf-styler-var-size-unit-old', unit);

					// Reset styler_obj_value_last
					this.styler_obj_value_last = false;

					// Set range attributes
					this.styler_event_range_attributes(obj_wrapper);

					break;
				}
			}

			// Set value
			value = value_unit.value;

			// Set value input
			obj_value.val(value);
		}

		if(value == '-') { value = ''; }
		var value_hidden = value + ((value != '') ? unit : '');

		// Set hidden value
		$('.wsf-styler-var-input-hidden', obj_wrapper).val(value_hidden).trigger('change');

		// Size render
		this.styler_event_size_render(obj_wrapper);
	}

	// Styler - Unit change
	$.WS_Form.prototype.styler_event_size_unit_change = function(obj) {

		// Get obj_wrapper
		var obj_wrapper = this.styler_get_column(obj);

		// Get value obj
		var obj_value = $('.wsf-styler-var-size-value', obj_wrapper);

		// Get unit obj
		var obj_unit = $('.wsf-styler-var-size-unit', obj_wrapper);

		// Get old unit
		var unit_old = obj_unit.attr('data-wsf-styler-var-size-unit-old');

		// Get unit
		var unit = obj_unit.val();

		// Store old unit value
		obj.attr('data-wsf-styler-var-size-unit-old', unit);

		// Check for lock
		if(
			(this.styler_obj_value_last !== false) &&
			(this.styler_obj_value_last[0] == obj_value[0])
		) {
			this.styler_obj_value_last = false;
			return;
		}

		// Get value
		var value = obj_value.val();
		var value_old = value;

		// From px
		if(['px', ''].includes(unit_old)) {

			if(unit == 'rem') {

				value = value / this.styler_get_root_font_size();
				value = parseFloat(value.toFixed(4));

			} else if(unit == 'em') {

				value = 1;

			} else if(unit == '%') {

				value = 100;
			}
		}

		// From rem
		if((unit_old == 'rem')) {

			if(['px', ''].includes(unit)) {

				value = value * this.styler_get_root_font_size();
				value = parseFloat(value.toFixed(4));

			} else if(unit == 'em') {

				value = 1;

			} else if(unit == '%') {

				value = 100;
			}
		}

		// From em
		if((unit_old == 'em')) {

			if(['px', ''].includes(unit)) {

				value = 16;

			} else if(unit == 'rem') {

				value = 1;

			} else if(unit == '%') {

				value = 100;
			}
		}

		// From %
		if((unit_old == '%')) {

			if(['px', ''].includes(unit)) {

				value = 16;

			} else if(['rem', 'em'].includes(unit)) {

				value = 1;
			}
		}

		// Set value
		if(value !== value_old) {

			obj_value.val(value).trigger('change');

			// Set range attributes
			this.styler_event_range_attributes(obj_wrapper);

			// Set range value
			$('.wsf-styler-var-size-range', obj_wrapper).val(parseFloat(value));
		}
	}

	// Styler - Get root font size in pixels
	$.WS_Form.prototype.styler_get_root_font_size = function() {

		// Get the computed font size of the document (in px)
		var root_font_size = parseFloat(getComputedStyle(document.documentElement).fontSize);
		if(!root_font_size) { return 16; }

		return root_font_size;
	}

	// Styler - Event size render
	$.WS_Form.prototype.styler_event_size_render = function(obj_wrapper, set_range) {

		// Get value
		var value = $('.wsf-styler-var-size-value', obj_wrapper).val();

		// Check value
		if(isNaN(value) && (value[0] != '-')) {

			$('.wsf-styler-var-size-unit, .wsf-styler-var-size-range', obj_wrapper).hide();

		} else {

			$('.wsf-styler-var-size-unit, .wsf-styler-var-size-range', obj_wrapper).show();

			if(parseFloat($('.wsf-styler-var-size-range', obj_wrapper).val()) != parseFloat(value)) {

				$('.wsf-styler-var-size-range', obj_wrapper).val(parseFloat(value));
			}
		}
	}

	// Styler Event range attributes
	$.WS_Form.prototype.styler_event_range_attributes = function(obj_wrapper) {

		// Get unit
		var unit = $('.wsf-styler-var-size-unit', obj_wrapper).val();

		// Get range object
		var range_obj = $('.wsf-styler-var-size-range', obj_wrapper);

		switch(unit) {

			case 'px' :

				var min = parseInt(range_obj.attr('data-wsf-styler-px-min') || 0, 10);;
				var max = parseInt(range_obj.attr('data-wsf-styler-px-max') || 100, 10);;
				var step = parseInt(range_obj.attr('data-wsf-styler-px-step') || 0.5, 10);;
				break;

			case '%' :

				var min = 0;
				var max = 100;
				var step = 0.5;
				break;

			default :

				var min = 0;
				var max = 10;
				var step = 0.1;
				break;
		}

		// Set attributes
		range_obj.attr('min', min).attr('max', max).attr('step', step);
	}

	// Styler - var set
	$.WS_Form.prototype.styler_var_set = function(obj) {

		// Get value
		var value = obj.val();

		// Get var
		var var_name = obj.attr('data-wsf-styler-var');

		// Get meta key
		var meta_key = obj.attr('data-wsf-styler-meta-key');

		// Set styler data
		this.styler_style.meta[meta_key]= value;

		// Set stylesheet var
		if(this.styler_rule) {

			this.styler_rule.style.setProperty(var_name, value);

		} else {

			// Fallback method
			$('[data-wsf-style-id="' + this.styler_style_id + '"]').each(function() {

				$(this)[0].style.setProperty(var_name, value);
			});
		}

		// Check for inside display mode
		switch(meta_key) {

			case 'field_label_inside_mode' :

				if(value == 'hide') {

					this.form_obj.addClass('wsf-label-position-inside-hide');

				} else {

					this.form_obj.removeClass('wsf-label-position-inside-hide');
				}
		}

		// Change made
		this.styler_change_made();
	}

	// Styler - Change made
	$.WS_Form.prototype.styler_change_made = function() {

		if(!this.styler_changes_made) {

			$(window).on('beforeunload', function (e) {

				e.preventDefault();

				return '';
			});

			this.styler.styler_changes_made = true;
		}
	}

	// Styler - Change made - Reset
	$.WS_Form.prototype.styler_change_made_reset = function() {

		$(window).off('beforeunload');
	}

	// Styler - Get column
	$.WS_Form.prototype.styler_get_column = function(obj) {

		return obj.hasClass('wsf-styler-var-column') ? obj : obj.closest('.wsf-styler-var-column');
	}

	// Styler - Get var
	$.WS_Form.prototype.styler_get_var = function(obj) {

		var obj_wrapper = this.styler_get_column(obj);

		return obj_wrapper.attr('data-wsf-styler-var');
	}

	// Styler - Get type
	$.WS_Form.prototype.styler_get_type = function(obj) {

		var obj_wrapper = this.styler_get_column(obj);

		return obj_wrapper.attr('data-wsf-styler-type');
	}

	// Styler - Get input
	$.WS_Form.prototype.styler_get_input = function(obj) {

		var obj_wrapper = this.styler_get_column(obj);

		return $('input.wsf-styler-var-input', obj_wrapper);
	}

	// Styler - Get select
	$.WS_Form.prototype.styler_get_select = function(obj) {

		var obj_wrapper = this.styler_get_column(obj);

		return $('select.wsf-styler-var-input', obj_wrapper);
	}

	// Styler - Get hidden input
	$.WS_Form.prototype.styler_get_input_hidden = function(obj) {

		var obj_wrapper = this.styler_get_column(obj);

		return $('input.wsf-styler-var-input-hidden', obj_wrapper);
	}

	// Styler - Get rule
	$.WS_Form.prototype.styler_get_rule = function() {

		Array.from(document.styleSheets).forEach(sheet => {

			if(this.styler_rule) { return false; }

			try {

				// Skip external stylesheets
				if (sheet.href && new URL(sheet.href).origin !== window.location.origin) {
					return;
				}

				// Get rules
				var rules = Array.from(sheet.cssRules || sheet.rules);

				// File data-wsf-style-id stylesheet
				rules.forEach(rule => {

					if(this.styler_rule) { return false; }

					if (rule.selectorText === ':where([data-wsf-style-id="' + this.styler_style_id + '"])') {

						this.styler_rule = rule;
					}
				});

			} catch (e) {

				console.error('Unable to access the stylesheet for style ID: ' + this.styler_style_id + ' (' + e.message + ')');
			}
		});
	}

	// Styler - Datalists
	$.WS_Form.prototype.styler_datalists = function(id, options) {

		var styler_html = this.styler_datalist_border_style();
		styler_html += this.styler_datalist_font_family();
		styler_html += this.styler_datalist_font_style();
		styler_html += this.styler_datalist_font_weight();
		styler_html += this.styler_datalist_text_decoration();
		styler_html += this.styler_datalist_text_transform();
		styler_html += this.styler_datalist_transition_timing_function();

		return styler_html;
	}

	// Styler - Datalist
	$.WS_Form.prototype.styler_datalist = function(id, options) {

		var ws_this = this;

		return '<datalist id="wsf-styler-datalist-' + this.esc_attr(id) + '">' + options.map(option => '<option value="' + ws_this.esc_attr(option.value) + '">' + ws_this.esc_html(option.label) + '</option>').join('') + '</datalist>';
	}

	// Styler - Datalist - Font Family
	$.WS_Form.prototype.styler_datalist_font_family = function() {

		return this.styler_datalist('font-family', [

			{ value: 'inherit', label: 'inherit' },
			{ value: 'Arial, sans-serif', label: 'Arial' },
			{ value: '"Comic Sans MS", cursive, sans-serif', label: 'Comic Sans MS' },
			{ value: '"Courier New", Courier, monospace', label: 'Courier New' },
			{ value: '"Gill Sans", "Gill Sans MT", Calibri, sans-serif', label: 'Gill Sans' },
			{ value: '"Helvetica Neue", Helvetica, sans-serif', label: 'Helvetica' },
			{ value: '"Impact", Charcoal, sans-serif', label: 'Impact' },
			{ value: '"Lucida Sans Unicode", "Lucida Grande", sans-serif', label: 'Lucida Sans' },
			{ value: 'Georgia, serif', label: 'Georgia' },
			{ value: 'Monaco, monospace', label: 'Monaco' },
			{ value: '"Palatino Linotype", "Book Antiqua", Palatino, serif', label: 'Palatino' },
			{ value: '"Segoe UI", Tahoma, Geneva, sans-serif', label: 'Segoe UI' },
			{ value: 'Tahoma, sans-serif', label: 'Tahoma' },
			{ value: '"Times New Roman", Times, serif', label: 'Times New Roman' },
			{ value: '"Trebuchet MS", sans-serif', label: 'Trebuchet MS' },
			{ value: 'Verdana, sans-serif', label: 'Verdana' }
		]);
	}

	// Styler - Datalist - Font style
	$.WS_Form.prototype.styler_datalist_font_style = function() {

		return this.styler_datalist('font-style', [

			{ value: 'normal', label: 'Normal' },
			{ value: 'italic', label: 'Italic' },
			{ value: 'oblique', label: 'Oblique' },
			{ value: 'inherit', label: 'Inherit' },
			{ value: 'initial', label: 'Initial' },
			{ value: 'unset', label: 'Unset' }
		]);
	}

	// Styler - Datalist - Weight
	$.WS_Form.prototype.styler_datalist_font_weight = function() {

		return this.styler_datalist('font-weight', [

			{ value: 'inherit', label: 'inherit' },
			{ value: '100', label: 'Thin' },
			{ value: '200', label: 'Extra Light' },
			{ value: '300', label: 'Light' },
			{ value: '400', label: 'Normal' },
			{ value: '500', label: 'Medium' },
			{ value: '600', label: 'Semi Bold' },
			{ value: '700', label: 'Bold' },
			{ value: '800', label: 'Extra Bold' },
			{ value: '900', label: 'Black' }
		]);
	}

	// Styler - Datalist - Transition timing function
	$.WS_Form.prototype.styler_datalist_transition_timing_function = function() {

		return this.styler_datalist('transition-timing-function', [

			{ value: 'linear', label: 'Linear' },
			{ value: 'ease', label: 'Ease' },
			{ value: 'ease-in', label: 'Ease-In' },
			{ value: 'ease-out', label: 'Ease-Out' },
			{ value: 'ease-in-out', label: 'Ease-In-Out' },
			{ value: 'step-start', label: 'Step Start' },
			{ value: 'step-end', label: 'Step End' }
		]);
	}

	// Styler - Datalist - Border style
	$.WS_Form.prototype.styler_datalist_border_style = function() {

		return this.styler_datalist('border-style', [

			{ value: 'none', label: 'None' },
			{ value: 'dashed', label: 'Dashed' },
			{ value: 'dotted', label: 'Dotted' },
			{ value: 'double', label: 'Double' },
			{ value: 'groove', label: 'Groove' },
			{ value: 'inset', label: 'Inset' },
			{ value: 'outset', label: 'Outset' },
			{ value: 'ridge', label: 'Ridge' },
			{ value: 'solid', label: 'Solid' }
		]);
	}

	// Styler - Datalist - Text decoration
	$.WS_Form.prototype.styler_datalist_text_decoration = function() {

		return this.styler_datalist('text-decoration', [

			{ value: 'none', label: 'None' },
			{ value: 'underline', label: 'Underline' },
			{ value: 'overline', label: 'Overline' },
			{ value: 'line-through', label: 'Line Through' },
			{ value: 'inherit', label: 'Inherit' },
			{ value: 'initial', label: 'Initial' },
			{ value: 'unset', label: 'Unset' }
		]);
	}

	// Styler - Datalist - Text transform
	$.WS_Form.prototype.styler_datalist_text_transform = function() {

		return this.styler_datalist('text-transform', [

			{ value: 'none', label: 'None' },
			{ value: 'capitalize', label: 'Capitalize' },
			{ value: 'uppercase', label: 'Uppercase' },
			{ value: 'lowercase', label: 'Lowercase' },
			{ value: 'full-width', label: 'Full Width' },
			{ value: 'inherit', label: 'Inherit' },
			{ value: 'initial', label: 'Initial' },
			{ value: 'unset', label: 'Unset' }
		]);
	}

	// Styler - Search
	$.WS_Form.prototype.styler_search = function() {

		var ws_this = this;

		// Build search array
		var search_array = this.styler_search_walker(window.wsf_form_json_config.styler.meta);

		// Search
		$('.wsf-styler-search input').on('input change paste', function() {

			// Get keywords entered
			var keywords = $(this).val();

			// Convert to lowercase and trim
			keywords = keywords.toLowerCase().trim();

			// If no keyword entered, reset the interface
			if(keywords.length <= 2) {

				$('#wsf-styler .wsf-styler-child').show().removeClass('wsf-styler-child-active');
				$('#wsf-styler .wsf-styler-var-wrapper').show();
				$('#wsf-styler .wsf-styler-var').show();

				return;
			}

			// Convert keywords entered into an array
			var keyword_array = keywords.split(' ');

			// Get keyword count
			var keyword_count = keyword_array.length;

			var vars_matched = [];

			for(var keyword_array_index in keyword_array) {

				if(!keyword_array.hasOwnProperty(keyword_array_index)) { continue; }

				var keyword = keyword_array[keyword_array_index];

				// Trim keywod
				keyword = keyword.trim();

				for(var search_array_index in search_array) {

					if(!search_array.hasOwnProperty(search_array_index)) { continue; }

					var search_array_config = search_array[search_array_index];

					var score = 0;

					var search_array_keyword = search_array_config.keyword;

					var search_array_keyword_indexof = search_array_keyword.indexOf(keyword);

					if(search_array_keyword_indexof >= 0) { score += 1; }

					if(score > 0) {

						if(typeof(vars_matched[search_array_config.css_var]) === 'undefined') {

							vars_matched[search_array_config.css_var] = score;

						} else {

							vars_matched[search_array_config.css_var] += score;
						}
					}
				};
			}

			// Sort by score descending
			var vars_matched = Object.fromEntries(Object.entries(vars_matched).sort(([, a], [, b]) => b - a));

			// Hide all
			$('#wsf-styler .wsf-styler-child').hide().addClass('wsf-styler-child-active');
			$('#wsf-styler .wsf-styler-var-wrapper').hide();

			// Show matching types
			for(var css_var in vars_matched) {

				if(!vars_matched.hasOwnProperty(css_var)) { continue; }

				// Only show results that match all keywords entered
				if(vars_matched[css_var] < keyword_count) { break; }

				// Show matching vars
				$('#wsf-styler .wsf-styler-var-wrapper[data-wsf-styler-var="' + ws_this.esc_selector(css_var) + '"]').show().parents('.wsf-styler-child').show();
			}
		});
	}

	// Styler - Search walker
	$.WS_Form.prototype.styler_search_walker = function(children, level, level_breadcrumb) {

		if(!level) { level = 0; }
		if(!level_breadcrumb) { level_breadcrumb = ''; }

		var styler_search_array = [];

		for(var child_index in children) {

			if(!children.hasOwnProperty(child_index)) { continue; }

			var styler_child = children[child_index];

			// Check for children
			if(styler_child.children) {

				styler_search_array = styler_search_array.concat(this.styler_search_walker(styler_child.children, level + 1, ((level_breadcrumb != '') ? level_breadcrumb + ' ' : level_breadcrumb) + styler_child.label));
			}

			// Check for meta
			if(styler_child.meta) {

				// Run through each type
				for(var meta_key in styler_child.meta) {

					if(!styler_child.meta.hasOwnProperty(meta_key)) { continue; }

					// Get var config
					var var_config = styler_child.meta[meta_key];

					// Ignore settings
					if(var_config.setting) { continue; }

					// Build keyword
					var keyword_array = [];

					// Parent breadcrumb label
					if(level_breadcrumb) {

						keyword_array.push(this.styler_keyword_tidy(level_breadcrumb));
					}

					// Parent label
					keyword_array.push(this.styler_keyword_tidy(styler_child.label));

					// Variable label
					keyword_array.push(this.styler_keyword_tidy(var_config.label));

					// Variable
					if(var_config.var) {

						keyword_array.push(var_config.var);
					}

					// Variable keyword
					if(typeof(var_config.keyword) !== 'undefined') {

						keyword_array.push(this.styler_keyword_tidy(var_config.keyword));
					}

					// Unique values only
					keyword_array = keyword_array.filter(function(value, index, array) {

						return array.indexOf(value) === index;
					});

					// Join
					var keyword = keyword_array.join(' ');

					// Add field type to search array
					styler_search_array.push({keyword: keyword, css_var: var_config.var});
				}
			}
		}

		return styler_search_array;
	}

	// Styler - Keyword tidy
	$.WS_Form.prototype.styler_keyword_tidy = function(keyword) {

		return keyword.toLowerCase().replace(' -', '').replace('(', '').replace(')', '').trim();
	}

	// Styler - API call
	$.WS_Form.prototype.styler_api_call = function(ajax_path, method, params, success_callback, error_callback) {

		// Defaults
		if(typeof(method) === 'undefined') { method = 'POST'; }
		if(!params) { params = {}; }

		var ws_this = this;

		// Build URL
		var url = ws_form_settings.url_ajax + ajax_path;

		var data = {};

		// NONCE
		if(
			(
				(typeof(data.get) === 'undefined') || // Do it anyway for IE 11
				(data.get(ws_form_settings.wsf_nonce_field_name) === null)
			) &&
			(ws_form_settings.wsf_nonce)
		) {
			data[ws_form_settings.wsf_nonce_field_name] = ws_form_settings.wsf_nonce;
		}

		// Params
		data.data = JSON.stringify(params);

		// Call AJAX
		var ajax_request = {

			method: method,
			url: url,
			beforeSend: function(xhr) {

				// Nonce (X-WP-Nonce)
				if(ws_form_settings.x_wp_nonce) {

					xhr.setRequestHeader('X-WP-Nonce', ws_form_settings.x_wp_nonce);
				}
			},
			statusCode: {

				// Success
				200: function(response) {

					// Call success function
					var success_callback_result = (typeof(success_callback) === 'function') ? success_callback(response) : true;
				},

				// Bad request
				400: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 400, url, error_callback);
				},

				// Unauthorized
				401: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 401, url, error_callback);
				},

				// Forbidden
				403: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 403, url, error_callback);
				},

				// Not found
				404: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 404, url, error_callback);
				},

				// Server error
				500: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 500, url, error_callback);
				}
			}
		};

		// Data
		if(data !== false) { ajax_request.data = data; }

		return $.ajax(ajax_request);
	}

})(jQuery);
