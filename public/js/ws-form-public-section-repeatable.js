(function($) {

	'use strict';

	// Form - Initialize all repeatable sections
	$.WS_Form.prototype.form_section_repeatable = function() {

		var ws_this = this;

		// Get array of each section [section_id]=count
		var section_id_repeatable_array = this.get_section_id_repeatable_array();

		// Process each section
		for(var section_id in section_id_repeatable_array) {

			if(!section_id_repeatable_array.hasOwnProperty(section_id)) { continue; }

			// Get section data
			var section = this.section_data_cache[section_id];

			// Get section count
			var section_count = section_id_repeatable_array[section_id];

			// Get section repeat min
			var section_repeat_min = this.get_section_repeat_min(section);

			// Get section repeat max
			var section_repeat_max = this.get_section_repeat_max(section, section_repeat_min);

			// Get section repeat default
			var section_repeat_default = this.get_section_repeat_default(section, section_repeat_min, section_repeat_max);

			for(var section_clone_index = 0; section_clone_index < (section_repeat_default - section_count); section_clone_index++) {

				// Get section obj to clone
				var section_clone_this = $('[data-repeatable][data-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).last();

				// Clone
				this.section_clone(section_clone_this);
			}

			// Navigation - Render
			this.section_repeatable_navigation_render(section_id);

			// Navigation - Events
			this.section_repeatable_navigation_events(section_id);

			// Navigation - Enable / Disable
			this.section_repeatable_navigation_enable_disable(section_id);

			// Labels
			this.section_repeatable_labels(section_id);

			// Trigger event
			this.form_canvas_obj.trigger('wsf-section-repeatable-' + section_id);
		}

		// Section repeatable hidden field
		this.section_repeatable_hidden_field();

		// Trigger event
		this.form_canvas_obj.trigger('wsf-section-repeatable');
	}

	// Adds section icons
	$.WS_Form.prototype.section_repeatable_navigation_render = function(section_id) {

		var ws_this = this;

		var method_same_section_array = ['move-up', 'move-down', 'drag'];

		var section_obj = $('[data-repeatable][data-id="' + this.esc_selector(section_id) + '"]');

		// Add data-repeatable-section-id to move up, move down and drag buttons
		for(var method_same_section_index in method_same_section_array) {

			if(!method_same_section_array.hasOwnProperty(method_same_section_index)) { continue; }

			var method_same_section = method_same_section_array[method_same_section_index];

			$('[data-action="wsf-section-' + method_same_section + '-button"]:not([data-repeatable-section-id])', section_obj).attr('data-repeatable-section-id', section_id);
		}

		var section_icon_sets = {

			'circle' : {

				'add' : '<path d="M13.7 2.3C12.1.8 10.1 0 8 0S3.9.8 2.3 2.3 0 5.9 0 8s.8 4.1 2.3 5.7S5.9 16 8 16s4.1-.8 5.7-2.3S16 10.1 16 8s-.8-4.1-2.3-5.7zM8 14.8c-3.7 0-6.8-3-6.8-6.8s3-6.8 6.8-6.8 6.8 3 6.8 6.8-3.1 6.8-6.8 6.8zm.6-7.4h2.8v1.2H8.6v2.8H7.4V8.6H4.6V7.4h2.8V4.6h1.2v2.8z"/>',
				'delete' : '<path d="M8 16c-2.1 0-4.1-.8-5.7-2.3S0 10.1 0 8s.8-4.1 2.3-5.7S5.9 0 8 0s4.1.8 5.7 2.3S16 5.9 16 8s-.8 4.1-2.3 5.7S10.1 16 8 16zM8 1.2c-3.7 0-6.8 3-6.8 6.8s3 6.8 6.8 6.8 6.8-3 6.8-6.8S11.7 1.2 8 1.2zm3.4 6.2H4.6v1.2h6.9V7.4z"/>',
				'move-up' : '<path d="M8 16c-2.1 0-4.1-.8-5.7-2.3S0 10.1 0 8s.8-4.1 2.3-5.7S5.9 0 8 0s4.1.8 5.7 2.3S16 5.9 16 8s-.8 4.1-2.3 5.7S10.1 16 8 16zM8 1.2c-3.7 0-6.8 3-6.8 6.8s3 6.8 6.8 6.8 6.8-3 6.8-6.8S11.7 1.2 8 1.2zm4.3 7.9L8 4.7 3.7 9.1l.9.9L8 6.5l3.4 3.4.9-.8z"/>',
				'move-down' : '<path d="M8 0c2.1 0 4.1.8 5.7 2.3S16 5.9 16 8s-.8 4.1-2.3 5.7S10.1 16 8 16s-4.1-.8-5.7-2.3S0 10.1 0 8s.8-4.1 2.3-5.7S5.9 0 8 0zm0 14.8c3.7 0 6.8-3 6.8-6.8s-3-6.8-6.8-6.8S1.2 4.3 1.2 8s3.1 6.8 6.8 6.8zM3.7 6.9L8 11.3 12.3 7l-.9-.9L8 9.5 4.6 6.1l-.9.8z"/>',
				'drag' : '<path d="M8 0c2.1 0 4.1.8 5.7 2.3S16 5.9 16 8s-.8 4.1-2.3 5.7S10.1 16 8 16s-4.1-.8-5.7-2.3S0 10.1 0 8s.8-4.1 2.3-5.7S5.9 0 8 0zm0 14.8c3.7 0 6.8-3 6.8-6.8s-3-6.8-6.8-6.8S1.2 4.3 1.2 8s3.1 6.8 6.8 6.8zm-4-4.7h8v1.3H4v-1.3zm0-2.7h8v1.3H4V7.4zm0-2.8h8v1.3H4V4.6z"/>',
				'reset' : '<path d="M8,0 C10.1,0 12.1,0.8 13.7,2.3 C15.3,3.8 16,5.9 16,8 C16,10.1 15.2,12.1 13.7,13.7 C12.2,15.3 10.1,16 8,16 C5.9,16 3.9,15.2 2.3,13.7 C0.7,12.2 0,10.1 0,8 C0,5.9 0.8,3.9 2.3,2.3 C3.8,0.7 5.9,0 8,0 Z M8,14.8 C11.7,14.8 14.8,11.8 14.8,8 C14.8,4.2 11.8,1.2 8,1.2 C4.2,1.2 1.2,4.3 1.2,8 C1.2,11.7 4.3,14.8 8,14.8 Z M8,4 C6.5,4 5.2,4.8 4.55,6.05 L4,5.5 L4,7.5 L6,7.5 L5.25,6.75 C5.75,5.75 6.8,5 8,5 C9.65,5 11,6.35 11,8 C11,9.65 9.65,11 8,11 C7.1,11 6.3,10.6 5.75,9.95 L5,10.6 C5.7,11.45 6.8,12 8,12 C10.2,12 12,10.2 12,8 C12,5.8 10.2,4 8,4 Z"/>',
				'clear' : '<path d="M13.7,2.3 C12.1,0.8 10.1,0 8,0 C5.9,0 3.9,0.8 2.3,2.3 C0.7,3.8 0,5.9 0,8 C0,10.1 0.8,12.1 2.3,13.7 C3.8,15.3 5.9,16 8,16 C10.1,16 12.1,15.2 13.7,13.7 C15.3,12.2 16,10.1 16,8 C16,5.9 15.2,3.9 13.7,2.3 Z M8,14.8 C4.3,14.8 1.2,11.8 1.2,8 C1.2,4.2 4.2,1.2 8,1.2 C11.8,1.2 14.8,4.2 14.8,8 C14.8,11.8 11.7,14.8 8,14.8 Z M8,7.15147186 L9.97989899,5.17157288 L10.8284271,6.02010101 L8.84852814,8 L10.8284271,9.97989899 L9.97989899,10.8284271 L8,8.84852814 L6.02010101,10.8284271 L5.17157288,9.97989899 L7.15147186,8 L5.17157288,6.02010101 L6.02010101,5.17157288 L8,7.15147186 Z"/>'
			},

			'circle-solid' : {

				'add' : '<path d="M13.7 2.3C12.1.8 10.1 0 8 0S3.9.8 2.3 2.3C.7 3.8 0 5.9 0 8s.8 4.1 2.3 5.7C3.8 15.3 5.9 16 8 16s4.1-.8 5.7-2.3C15.3 12.2 16 10.1 16 8s-.8-4.1-2.3-5.7zM8.6 7.4h2.8v1.2H8.6v2.8H7.4V8.6H4.6V7.4h2.8V4.6h1.2v2.8z"/>',
				'delete' : '<path d="M8 16c-2.1 0-4.1-.8-5.7-2.3C.7 12.2 0 10.1 0 8s.8-4.1 2.3-5.7C3.8.7 5.9 0 8 0s4.1.8 5.7 2.3C15.3 3.8 16 5.9 16 8s-.8 4.1-2.3 5.7C12.2 15.3 10.1 16 8 16zm3.4-8.6H4.6v1.2h6.9V7.4h-.1z"/>',
				'move-up' : '<path d="M8 16c-2.1 0-4.1-.8-5.7-2.3C.7 12.2 0 10.1 0 8s.8-4.1 2.3-5.7C3.8.7 5.9 0 8 0s4.1.8 5.7 2.3C15.3 3.8 16 5.9 16 8s-.8 4.1-2.3 5.7C12.2 15.3 10.1 16 8 16zm4.3-6.9L8 4.7 3.7 9.1l.9.9L8 6.5l3.4 3.4.9-.8z"/>',
				'move-down' : '<path d="M8 0c2.1 0 4.1.8 5.7 2.3C15.3 3.8 16 5.9 16 8s-.8 4.1-2.3 5.7C12.2 15.3 10.1 16 8 16s-4.1-.8-5.7-2.3C.7 12.2 0 10.1 0 8s.8-4.1 2.3-5.7C3.8.7 5.9 0 8 0zM3.7 6.9L8 11.3 12.3 7l-.9-.9L8 9.5 4.6 6.1l-.9.8z"/>',
				'drag' : '<path d="M8 0c2.1 0 4.1.8 5.7 2.3C15.3 3.8 16 5.9 16 8s-.8 4.1-2.3 5.7C12.2 15.3 10.1 16 8 16s-4.1-.8-5.7-2.3C.7 12.2 0 10.1 0 8s.8-4.1 2.3-5.7C3.8.7 5.9 0 8 0zm4 10.1H4v1.3h8v-1.3zm0-2.7H4v1.3h8V7.4zm0-2.8H4v1.3h8V4.6z"/>',
				'reset' : '<path d="M8,0 C10.1,0 12.1,0.8 13.7,2.3 C15.3,3.8 16,5.9 16,8 C16,10.1 15.2,12.1 13.7,13.7 C12.2,15.3 10.1,16 8,16 C5.9,16 3.9,15.2 2.3,13.7 C0.7,12.2 0,10.1 0,8 C0,5.9 0.8,3.9 2.3,2.3 C3.8,0.7 5.9,0 8,0 Z M8,4 C6.5,4 5.2,4.8 4.55,6.05 L4,5.5 L4,7.5 L6,7.5 L5.25,6.75 C5.75,5.75 6.8,5 8,5 C9.65,5 11,6.35 11,8 C11,9.65 9.65,11 8,11 C7.1,11 6.3,10.6 5.75,9.95 L5,10.6 C5.7,11.45 6.8,12 8,12 C10.2,12 12,10.2 12,8 C12,5.8 10.2,4 8,4 Z"/>',
				'clear' : '<path d="M8,0 C10.1,0 12.1,0.8 13.7,2.3 C15.3,3.8 16,5.9 16,8 C16,10.1 15.2,12.1 13.7,13.7 C12.2,15.3 10.1,16 8,16 C5.9,16 3.9,15.2 2.3,13.7 C0.7,12.2 0,10.1 0,8 C0,5.9 0.8,3.9 2.3,2.3 C3.8,0.7 5.9,0 8,0 Z M9.97989899,5.17157288 L8,7.15147186 L6.02010101,5.17157288 L5.17157288,6.02010101 L7.15147186,8 L5.17157288,9.97989899 L6.02010101,10.8284271 L8,8.84852814 L9.97989899,10.8284271 L10.8284271,9.97989899 L8.84852814,8 L10.8284271,6.02010101 L9.97989899,5.17157288 Z"/>'
			},

			'square' : {

				'add' : '<path d="M8 16H0V0h16v16H8zM8 1.2H1.2v13.6h13.6V1.2H8zm.6 6.2h2.8v1.2H8.6v2.8H7.4V8.6H4.6V7.4h2.8V4.6h1.2v2.8z"/>',
				'delete' : '<path d="M8 16H0V0h16v16H8zM8 1.2H1.2v13.6h13.6V1.2H8zm3.4 6.2H4.6v1.2h6.9V7.4h-.1z"/>',
				'move-up' : '<path d="M8 16H0V0h16v16H8zM8 1.2H1.2v13.6h13.6V1.2H8zm4.3 7.9L8 4.7 3.7 9.1l.9.9L8 6.5l3.4 3.4.9-.8z"/>',
				'move-down' : '<path d="M8 16H0V0h16v16H8zM8 1.2H1.2v13.6h13.6V1.2H8zM3.7 6.9L8 11.3 12.3 7l-.9-.9L8 9.5 4.6 6.1l-.9.8z"/>',
				'drag' : '<path d="M8 16H0V0h16v16H8zM8 1.2H1.2v13.6h13.6V1.2H8zm-4 8.9h8v1.3H4v-1.3zm0-2.7h8v1.3H4V7.4zm0-2.8h8v1.3H4V4.6z"/>',
				'reset' : '<path d="M8,16 L0,16 L0,8 L0,0 L8,0 L16,0 L16,8 L16,16 L8,16 Z M8,1.2 L1.2,1.2 L1.2,8 L1.2,14.8 L8,14.8 L14.8,14.8 L14.8,8 L14.8,1.2 L8,1.2 Z M8,4 C6.5,4 5.2,4.8 4.55,6.05 L4,5.5 L4,7.5 L6,7.5 L5.25,6.75 C5.75,5.75 6.8,5 8,5 C9.65,5 11,6.35 11,8 C11,9.65 9.65,11 8,11 C7.1,11 6.3,10.6 5.75,9.95 L5,10.6 C5.7,11.45 6.8,12 8,12 C10.2,12 12,10.2 12,8 C12,5.8 10.2,4 8,4 Z"/>',
				'clear' : '<path d="M8,16 L0,16 L0,8 L0,0 L8,0 L16,0 L16,8 L16,16 L8,16 Z M8,1.2 L1.2,1.2 L1.2,8 L1.2,14.8 L8,14.8 L14.8,14.8 L14.8,8 L14.8,1.2 L8,1.2 Z M8,7.15147186 L9.97989899,5.17157288 L10.8284271,6.02010101 L8.84852814,8 L10.8284271,9.97989899 L9.97989899,10.8284271 L8,8.84852814 L6.02010101,10.8284271 L5.17157288,9.97989899 L7.15147186,8 L5.17157288,6.02010101 L6.02010101,5.17157288 L8,7.15147186 Z"/>'
			},

			'square-solid' : {

				'add' : '<path d="M16 0v16H0V0h16zM8.6 4.6H7.4v2.8H4.6v1.2h2.8v2.8h1.2V8.6h2.8V7.4H8.6V4.6z"/>',
				'delete' : '<path d="M8 16H0V0h16v16H8zm3.4-8.6H4.6v1.2h6.9V7.4h-.1z"/>',
				'move-up' : '<path d="M8 16H0V0h16v16H8zm4.3-6.9L8 4.7 3.7 9.1l.9.9L8 6.5l3.4 3.4.9-.8z"/>',
				'move-down' : '<path d="M8 16H0V0h16v16H8zM3.7 6.9L8 11.3 12.3 7l-.9-.9L8 9.5 4.6 6.1l-.9.8z"/>',
				'drag' : '<path d="M16 0v16H0V0h16zm-4 10.1H4v1.3h8v-1.3zm0-2.7H4v1.3h8V7.4zm0-2.8H4v1.3h8V4.6z"/>',
				'reset' : '<path d="M16,0 L16,16 L0,16 L0,0 L16,0 Z M8,4 C6.5,4 5.2,4.8 4.55,6.05 L4,5.5 L4,7.5 L6,7.5 L5.25,6.75 C5.75,5.75 6.8,5 8,5 C9.65,5 11,6.35 11,8 C11,9.65 9.65,11 8,11 C7.1,11 6.3,10.6 5.75,9.95 L5,10.6 C5.7,11.45 6.8,12 8,12 C10.2,12 12,10.2 12,8 C12,5.8 10.2,4 8,4 Z"/>',
				'clear' : '<path d="M16,0 L16,16 L0,16 L0,0 L16,0 Z M9.97989899,5.17157288 L8,7.15147186 L6.02010101,5.17157288 L5.17157288,6.02010101 L7.15147186,8 L5.17157288,9.97989899 L6.02010101,10.8284271 L8,8.84852814 L9.97989899,10.8284271 L10.8284271,9.97989899 L8.84852814,8 L10.8284271,6.02010101 L9.97989899,5.17157288 Z"/>'
			}
		};

		// Get section icon objects
		$('[data-section-icons]', section_obj).each(function() {

			// Do not re-render already rendered icons
			if($(this).html() !== '') { return; }

			var section_icons_array = [];

			var field = ws_this.get_field($(this));

			// Read data-repeatable-section-id
			var repeatable_section_id = $(this).attr('data-repeatable-section-id');
			if(typeof(repeatable_section_id) === 'undefined') { 

				ws_this.error('error_section_icon_no_section');
				repeatable_section_id = 0;
			}
			$(this).attr('data-repeatable-section-id', false);

			// Check if button is in its own repeatable section
			var parent_section_obj = $(this).closest('[data-repeatable]');
			var own_repeatable_section = (

				parent_section_obj.length &&
				(parent_section_obj.attr('data-id') == repeatable_section_id)
			);

			// Read section icons style
			var section_icons_style = ws_this.get_object_meta_value(field, 'section_icons_style', []);
			$(this).addClass('wsf-section-icons-' + section_icons_style);

			// Read section icons size
			var section_icons_size = parseInt(ws_this.get_object_meta_value(field, 'section_icons_size', '24'), 10);
			if(isNaN(section_icons_size)) { section_icons_size = 24; }
			if(section_icons_size < 1) { section_icons_size = 1; }

			// Read colors
			var section_icons_color_on = ws_this.get_object_meta_value(field, 'section_icons_color_on', '#000');
			var section_icons_color_off = ws_this.get_object_meta_value(field, 'section_icons_color_off', '#888');

			// Read horizontal alignment
			var section_icons_horizontal_align = ws_this.get_object_meta_value(field, 'horizontal_align', 'right');

			// Get skin spacing small
			var skin_spacing_small = ws_form_settings.skin_spacing_small;

			// Read section icons
			var section_icons = ws_this.get_object_meta_value(field, 'section_icons', []);

			for(var section_icon_index in section_icons) {

				if(!section_icons.hasOwnProperty(section_icon_index)) { continue; }

				var section_icon = section_icons[section_icon_index];

				var section_icon_type = ((typeof(section_icon.section_icons_type) !== 'undefined') ? section_icon.section_icons_type : false);
				if(section_icon_type === false) { continue; }

				var section_icon_label = ((typeof(section_icon.section_icons_label) !== 'undefined') ? section_icon.section_icons_label : ws_this.language('section_icon_' + section_icon_type))

				// Check if icon type can be rendered here
				if(
					(method_same_section_array.indexOf(section_icon_type) !== -1) &&
					!own_repeatable_section
				) {

					ws_this.error('error_section_icon_not_in_own_section', section_icon_type);
					continue;
				}

				var section_icon_text = '<span class="wsf-section-icon-text">' + ws_this.language('section_icon_' + section_icon_type) + '</span>';

				// Determine cursor
				var section_icon_cursor = ((section_icon_type == 'drag') ? 'move' : 'pointer');
				
				// Build HTML
				switch(section_icons_style) {

					case 'text' :

						var section_icon_html = section_icon_text;
						break;

					case 'custom' :

						var section_icon_html = ws_this.get_object_meta_value(field, 'section_icons_html_' + section_icon_type, '');
						if(section_icon_html == '') { section_icon_html = section_icon_text; }
						break;

					default :

						var section_icon_svg = (

							(typeof(section_icon_sets[section_icons_style]) !== 'undefined') &&
							(typeof(section_icon_sets[section_icons_style][section_icon_type]) !== 'undefined')

						) ? section_icon_sets[section_icons_style][section_icon_type] : false;

						var section_icon_html = (section_icon_svg !== false) ? ('<svg class="wsf-section-icon" focusable="false" height="' + ws_this.esc_attr(section_icons_size) + '" width="' + ws_this.esc_attr(section_icons_size) + '" viewBox="0 0 16 16" style="display: block; height: auto; max-width: 100%;">' + section_icon_svg + '</svg>') : section_icon_text;
				}

				section_icons_array.push('<a href="#" data-action="wsf-section-' + ws_this.esc_attr(section_icon_type) + '-icon" class="wsf-section-' + ws_this.esc_attr(section_icon_type) + '" style="cursor: ' + ws_this.esc_attr(section_icon_cursor) + '; margin: 0; -webkit-margin-end: ' + ws_this.esc_attr(skin_spacing_small) + 'px; margin-inline-end: ' + ws_this.esc_attr(skin_spacing_small) + 'px; padding: 0;" title="' + ws_this.esc_attr(section_icon_label) + '" aria-label="' + ws_this.esc_attr(section_icon_label) + '" data-color-on="' + ws_this.esc_attr(section_icons_color_on) + '" data-color-off="' + ws_this.esc_attr(section_icons_color_off) + '" data-repeatable-section-id="' + ws_this.esc_attr(repeatable_section_id) + '">' + section_icon_html + '</a>');
			}

			$(this).html('<div class="wsf-section-icons" style="display: flex; justify-content: ' + ws_this.esc_attr(section_icons_horizontal_align) + '; list-style-type: none; margin: 0; padding: 0; user-select: none;">' + section_icons_array.join('') + '</div>');
		});
	}

	// Initialize navigation enabled/disabled on a repeatable section
	$.WS_Form.prototype.section_repeatable_navigation_enable_disable = function(section_id) {

		var ws_this = this;

		// Get section obj
		var section_obj = $('[data-repeatable][data-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj);

		// Get section count
		var section_count = section_obj.length;

		// Get section data
		var section = this.section_data_cache[section_id];

		// Get section repeat min
		var section_repeat_min = this.get_section_repeat_min(section);

		// Get section repeat max
		var section_repeat_max = this.get_section_repeat_max(section, section_repeat_min);

		// Add - Button / Icon (Can be placed anywhere on the form)
		$('button[data-action="wsf-section-add-button"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"], a[data-action="wsf-section-add-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).each(function() {

			if((section_repeat_max !== false) && (section_count >= section_repeat_max)) {

				ws_this.section_action_off($(this), 'add');

			} else {

				ws_this.section_action_on($(this), 'add');
			}
		});

		// Delete - Button / Icon (Can be placed anywhere on the form)
		$('button[data-action="wsf-section-delete-button"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"], a[data-action="wsf-section-delete-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).each(function() {

			if(section_count <= section_repeat_min) {

				ws_this.section_action_off($(this), 'delete');

			} else {

				ws_this.section_action_on($(this), 'delete');
			}
		});

		// Move Up - Button / Icon (Must be in repeatable section you want to move up)
		$('button[data-action="wsf-section-move-up-button"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"], a[data-action="wsf-section-move-up-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).each(function(button_index) {

			var section_index = ws_this.section_repeatable_get_index($(this), section_id);

			if((section_count == 1) || (section_index == 0)) {

				ws_this.section_action_off($(this), 'move-up');

			} else {

				ws_this.section_action_on($(this), 'move-up');
			}
		});

		// Move Up - Button / Icon (Must be in repeatable section you want to move down)
		$('button[data-action="wsf-section-move-down-button"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"], a[data-action="wsf-section-move-down-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).each(function(button_index) {

			var section_index = ws_this.section_repeatable_get_index($(this), section_id);

			if((section_count == 1) || (section_index == (section_count - 1))) {

				ws_this.section_action_off($(this), 'move-down');

			} else {

				ws_this.section_action_on($(this), 'move-down');
			}
		});

		// Drag - Button / Icon (Can be placed anywhere on the form)
		$('a[data-action="wsf-section-drag-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).each(function() {

			if(section_count == 1) {

				ws_this.section_action_off($(this), 'drag');

			} else {

				ws_this.section_action_on($(this), 'drag');
			}
		});

		// De-dupe
		this.form_dedupe_value_scope_process_all();
	}

	// Initialize events on a repeatable section
	$.WS_Form.prototype.section_repeatable_navigation_events = function(section_id) {

		var ws_this = this;

		// Get section count
		var section_count = $('[data-repeatable][data-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).length;

		// Get section data
		var section = this.section_data_cache[section_id];

		// Get section repeat min
		var section_repeat_min = this.get_section_repeat_min(section);

		// Get section repeat max
		var section_repeat_max = this.get_section_repeat_max(section, section_repeat_min);

		// Add - Button (Can be placed anywhere on the form)
		$('button[data-action="wsf-section-add-button"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section]), a[data-action="wsf-section-add-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section])', this.form_canvas_obj).on('click', function(e) {

			e.preventDefault();

			// Check if we should run this
			if($(this).hasClass('wsf-section-add-disabled')) { return; }

			// Get section ID
			var section_id = $(this).attr('data-repeatable-section-id');
			if(typeof(section_id) === 'undefined') {

				ws_this.error('error_section_button_no_section');
				return;
			}

			// Check if button is in its own repeatable section
			var parent_section_obj = $(this).closest('[data-repeatable]');
			var own_repeatable_section = (

				parent_section_obj.length &&
				(parent_section_obj.attr('data-id') == section_id)
			);

			// Clone section
			if(own_repeatable_section) {

				// Button/icon is in its own repeatable section
				ws_this.section_clone($(this).closest('[data-repeatable]'));

			} else {

				// Button/icon relates to a different section on the page (add to bottom)
				var section_to_clone = $('[data-repeatable][data-id="' + ws_this.esc_selector(section_id) + '"]', ws_this.form_canvas_obj).last();
				ws_this.section_clone(section_to_clone);
			}

			// Initialize cloned section
			ws_this.section_add_init(section_id);

			// Trigger event
			ws_this.form_canvas_obj.trigger('wsf-section-repeatable').trigger('wsf-section-repeatable-' + section_id).trigger('wsf-section-repeatable-add-' + section_id);

		}).attr('data-init-repeatable-section', '');

		// Delete - Button (Can be placed anywhere on the form)
		$('button[data-action="wsf-section-delete-button"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section]), a[data-action="wsf-section-delete-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section])', this.form_canvas_obj).on('click', function(e) {

			e.preventDefault();

			// Check if we should run this
			if($(this).hasClass('wsf-section-delete-disabled')) { return; }

			// Get section ID
			var section_id = $(this).attr('data-repeatable-section-id');
			if(typeof(section_id) === 'undefined') {

				ws_this.error('error_section_button_no_section');
				return;
			}

			// Check if confirmation required
			var section = ws_this.section_data_cache[section_id];
			var section_repeatable_remove_row_confirm = ws_this.get_object_meta_value(section, 'section_repeatable_remove_row_confirm', '');
			var section_repeatable_remove_row_confirm_message = ws_this.get_object_meta_value(section, 'section_repeatable_remove_row_confirm_message', '');

			if(
				section_repeatable_remove_row_confirm &&
				!confirm(section_repeatable_remove_row_confirm_message)
			) {
				return;
			}

			// Check if button is in its own repeatable section
			var parent_section_obj = $(this).closest('[data-repeatable]');
			var own_repeatable_section = (

				parent_section_obj.length &&
				(parent_section_obj.attr('data-id') == section_id)
			);

			// Delete section
			if(own_repeatable_section) {

				// Button/icon is in its own repeatable section
				$(this).closest('[data-repeatable]').remove();

			} else {

				// Button/icon relates to a different section on the page (remove from bottom)
				var section_to_remove = $('[data-repeatable][data-id="' + ws_this.esc_selector(section_id) + '"]', ws_this.form_canvas_obj).last();
				section_to_remove.remove();
			}

			// Initialize removed section
			ws_this.section_remove_init(section_id);

			// Trigger event
			ws_this.form_canvas_obj.trigger('wsf-section-repeatable').trigger('wsf-section-repeatable-' + section_id).trigger('wsf-section-repeatable-delete-' + section_id);

		}).attr('data-init-repeatable-section', '');

		// Move Up - Button / Icon
		$('button[data-action="wsf-section-move-up-button"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section]), a[data-action="wsf-section-move-up-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section])', this.form_canvas_obj).on('click', function(e) {

			e.preventDefault();

			// Check if we should run this
			if($(this).hasClass('wsf-section-move-up-disabled')) { return; }

			var section_id = $(this).attr('data-repeatable-section-id');
			var section_obj = $(this).closest('[data-repeatable]');

			// Move section up
			section_obj.prev().insertAfter(section_obj);

			// Navigation enable/disable
			ws_this.section_repeatable_navigation_enable_disable(section_id);

			// Labels
			ws_this.section_repeatable_labels(section_id);

			// Section repeatable hidden field
			ws_this.section_repeatable_hidden_field();

			// Calculations
			if(typeof(ws_this.form_calc) === 'function') { ws_this.form_calc(false, section_id); }

			// Trigger event
			ws_this.form_canvas_obj.trigger('wsf-section-repeatable-move-' + section_id);

		}).attr('data-init-repeatable-section', '');

		// Move Down - Button / Icon
		$('button[data-action="wsf-section-move-down-button"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section]), a[data-action="wsf-section-move-down-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section])', this.form_canvas_obj).on('click', function(e) {

			e.preventDefault();

			// Check if we should run this
			if($(this).hasClass('wsf-section-move-down-disabled')) { return; }

			var section_id = $(this).attr('data-repeatable-section-id');
			var section_obj = $(this).closest('[data-repeatable]');

			// Move section down
			section_obj.next().insertBefore(section_obj);

			// Navigation enable/disable
			ws_this.section_repeatable_navigation_enable_disable(section_id);

			// Labels
			ws_this.section_repeatable_labels(section_id);

			// Section repeatable hidden field
			ws_this.section_repeatable_hidden_field();

			// Calculations
			if(typeof(ws_this.form_calc) === 'function') { ws_this.form_calc(false, section_id); }

			// Trigger event
			ws_this.form_canvas_obj.trigger('wsf-section-repeatable-move-' + section_id);

		}).attr('data-init-repeatable-section', '');

		// Drag Icon
		$('a[data-action="wsf-section-drag-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section])', this.form_canvas_obj).on('mouseenter touchstart', function(e) {

			e.preventDefault();

			// Check if we should run this
			if($(this).hasClass('wsf-section-drag-disabled')) { return; }

			var section_obj = $(this).closest('[data-repeatable]');

			if(
				(ws_this.section_repeatable_dragged === false)
			) {

				var section_id = section_obj.attr('data-id');

				// Make each section sortable
				section_obj.parent(':not(.ui-sortable)').sortable({

					items: '> [data-repeatable][data-id="' + ws_this.esc_selector(section_id) + '"]',
					cancel: '.wsf-section-repeatable-cancel',
					cursor: 'move',
					handle: '.wsf-section-drag',
					tolerance: 'pointer',
					containment: 'parent',
					start: function(event, ui) {

						ws_this.section_repeatable_dragged = true;
					},

					stop: function(event, ui) {

						ws_this.section_repeatable_dragged = false;

						// Navigation enable/disable
						ws_this.section_repeatable_navigation_enable_disable(section_id);

						// Labels
						ws_this.section_repeatable_labels(section_id);

						// Section repeatable hidden field
						ws_this.section_repeatable_hidden_field();

						// Calculations
						if(typeof(ws_this.form_calc) === 'function') { ws_this.form_calc(false, section_id); }

						// Trigger event
						ws_this.form_canvas_obj.trigger('wsf-section-repeatable-move-' + section_id);
					}
				});
			}

		}).on('mouseleave touchstart', function(e) {

			e.preventDefault();

			// Check if we should run this
			if($(this).hasClass('wsf-section-drag-disabled')) { return; }

			if(ws_this.section_repeatable_dragged === false) {

				$(this).closest('[data-repeatable]').parent('.ui-sortable').sortable('destroy');
			}

		}).attr('data-init-repeatable-section', '');

		// Reset - Icon (Can be placed anywhere on the form)
		$('a[data-action="wsf-section-reset-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section])', this.form_canvas_obj).on('click', function(e) {

			e.preventDefault();

			// Get section ID
			var section_id = $(this).attr('data-repeatable-section-id');
			if(typeof(section_id) === 'undefined') {

				ws_this.error('error_section_button_no_section');
				return;
			}

			// Check if button is in its own repeatable section
			var parent_section_obj = $(this).closest('[data-repeatable]');
			var own_repeatable_section = (

				parent_section_obj.length &&
				(parent_section_obj.attr('data-id') == section_id)
			);

			// Get section repeatable index
			var section_repeatable_index = own_repeatable_section ? parent_section_obj.attr('data-repeatable-index') : false;

			// Reset all fields in section
			ws_this.section_fields_reset(section_id, false, section_repeatable_index);

			// Trigger event
			ws_this.form_canvas_obj.trigger('wsf-section-repeatable-reset-' + section_id);

		}).attr('data-init-repeatable-section', '');

		// Clear - Icon (Can be placed anywhere on the form)
		$('a[data-action="wsf-section-clear-icon"][data-repeatable-section-id="' + this.esc_selector(section_id) + '"]:not([data-init-repeatable-section])', this.form_canvas_obj).on('click', function(e) {

			e.preventDefault();

			// Get section ID
			var section_id = $(this).attr('data-repeatable-section-id');
			if(typeof(section_id) === 'undefined') {

				ws_this.error('error_section_button_no_section');
				return;
			}

			// Check if button is in its own repeatable section
			var parent_section_obj = $(this).closest('[data-repeatable]');
			var own_repeatable_section = (

				parent_section_obj.length &&
				(parent_section_obj.attr('data-id') == section_id)
			);

			// Get section repeatable index
			var section_repeatable_index = own_repeatable_section ? parent_section_obj.attr('data-repeatable-index') : false;

			// Reset all fields in section
			ws_this.section_fields_reset(section_id, true, section_repeatable_index);

			// Trigger event
			ws_this.form_canvas_obj.trigger('wsf-section-repeatable-clear-' + section_id);

		}).attr('data-init-repeatable-section', '');
	}

	// Initialize repeatable section labels
	$.WS_Form.prototype.section_repeatable_labels = function(section_id) {

		// Get section data
		var section = this.section_data_cache[section_id];

		// Section repeat labels
		var section_render_label = this.get_object_meta_value(section, 'render_label', 'on');
		var section_repeat_label = this.get_object_meta_value(section, 'section_repeat_label', 'on');
		if(section_render_label && !section_repeat_label) {

			$('[data-repeatable][data-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).each(function(index) {

				index ? $('> legend,h1,h2,h3,h4,h5,h6', $(this)).hide() : $('> legend,h1,h2,h3,h4,h5,h6', $(this)).show();
			});
		}
	}

	// Get section index an object is in
	$.WS_Form.prototype.section_repeatable_get_index = function(obj, section_id) {

		var section_index = 0;

		// Get section this object is in
		var section_obj = obj.closest('[data-repeatable]');

		// Run through sections and find matching section
		$('[data-repeatable][data-id="' + this.esc_selector(section_id) + '"]', this.form_canvas_obj).each(function(this_section_index) {

			if($(this).attr('id') === section_obj.attr('id')) { section_index = this_section_index; return false; }
		});

		return section_index;
	}

	// Initialize repeatable section hidden field
	$.WS_Form.prototype.section_repeatable_hidden_field = function() {

		var ws_this = this;

		this.section_repeatable_indexes = {};

		var section_obj = $('[data-repeatable]', this.form_canvas_obj);

		// Process each section
		section_obj.each(function() {

			// Get section ID
			var section_id = $(this).attr('data-id');

			// Get section repeatable index
			var section_repeatable_index = $(this).attr('data-repeatable-index');

			// Add to section_repeatable_indexes
			if(typeof(ws_this.section_repeatable_indexes['section_' + section_id]) === 'undefined') { ws_this.section_repeatable_indexes['section_' + section_id] = []; }
			ws_this.section_repeatable_indexes['section_' + section_id].push(section_repeatable_index);

			// Trigger event
			ws_this.form_canvas_obj.trigger('wsf-section-repeatable-' + section_id);
		});

		// Set section_repeatable_indexes hidden field
		this.form_add_hidden_input('wsf_form_section_repeatable_index', JSON.stringify(this.section_repeatable_indexes), false, false, true);

		// Trigger event
		if(section_obj.length) {

			this.form_canvas_obj.trigger('wsf-section-repeatable');
		}
	}

	// Get section repeatable array
	$.WS_Form.prototype.get_section_id_repeatable_array = function() {

		// Get all repeatable sections
		var section_id_repeatable_array = [];

		$('[data-repeatable]', this.form_canvas_obj).each(function() {

			var section_id = $(this).attr('data-id');

			if(typeof(section_id_repeatable_array[section_id]) === 'undefined') {

				section_id_repeatable_array[section_id] = 1;

			} else {

				section_id_repeatable_array[section_id]++;
			}
		});

		return section_id_repeatable_array;
	}

	// Section button/icon off
	$.WS_Form.prototype.section_action_off = function(obj, id) {

		obj.addClass('wsf-section-' + id + '-disabled').filter(':button').prop('disabled', true).attr('aria-disabled', 'true');

		// Section icon
		if(typeof(obj.attr('data-color-off')) !== 'undefined') {

			obj.css({cursor: 'not-allowed'}).attr('aria-disabled', 'true').attr('tabindex', '-1');
			var color_off = obj.attr('data-color-off');
			$('svg path', obj).attr({'fill': color_off});
			$('span.wsf-section-icon-text', obj).css({color: color_off});
		}
	}

	// Section button/icon on
	$.WS_Form.prototype.section_action_on = function(obj, id) {

		obj.removeClass('wsf-section-' + id + '-disabled').filter(':button').removeAttr('disabled', false).removeAttr('aria-disabled');

		// Section icon
		if(typeof(obj.attr('data-color-on')) !== 'undefined') {

			var action = obj.attr('data-action');
			var cursor = ((action === 'wsf-section-drag-icon') ? 'move' : 'pointer');
			obj.css({cursor: cursor}).removeAttr('aria-disabled').removeAttr('tabindex');
			var color_on = obj.attr('data-color-on');
			$('svg path', obj).attr({'fill': color_on});
			$('span.wsf-section-icon-text', obj).css({color: color_on});
		}
	}

	// Clone section
	$.WS_Form.prototype.section_clone = function(section_obj) {

		// Get section ID
		var section_id = section_obj.attr('data-id');

		// Get section data
		var section = this.section_data_cache[section_id];

		// Adjust section meta data
		section.meta.disabled_section = (section_obj.is(':disabled') ? 'on' : '');
		section.meta.hidden_section = (section_obj.is('[style!="display:none;"][style!="display: none;"]') ? '' : 'on');

		// Get section HTML
		var section_html = this.get_section_html(section);

		// Add after current section
		var section_new = $(section_html).insertAfter(section_obj);

		$('[data-init-conditional]', section_new).removeAttr('[data-init-conditional]');

		// Remove fields that cannot be cloned
		for(var field_type in $.WS_Form.field_type_cache) {

			if(!$.WS_Form.field_type_cache.hasOwnProperty(field_type)) { continue; }

			var field_type_config = $.WS_Form.field_type_cache[field_type];

			var multiple = (typeof(field_type_config.multiple) !== 'undefined') ? field_type_config.multiple : true;

			if(multiple === false) {

				var field_obj = $('[data-type="' + this.esc_selector(field_type) + '"]', section_new);

				if(field_obj.length) {

					// Remove field
					field_obj.remove();
				}
			}
		}

		return section_new;
	}

	// Initialize after section added
	$.WS_Form.prototype.section_add_init = function(section_id) {

		// Navigation - Render
		this.section_repeatable_navigation_render(section_id);

		// Navigation - Events
		this.section_repeatable_navigation_events(section_id);

		// Navigation - Enable / Disable
		this.section_repeatable_navigation_enable_disable(section_id);

		// Labels
		this.section_repeatable_labels(section_id);

		// Text areas
		if(typeof(this.form_textarea) === 'function') { this.form_textarea(); }

		// Input masks
		this.form_inputmask();

		// Checkbox min max
		if(typeof(this.form_checkbox_min_max) === 'function') { this.form_checkbox_min_max(); }

		// Checkbox select all
		if(typeof(this.form_checkbox_select_all) === 'function') { this.form_checkbox_select_all(); }

		// Radio validation
		if(typeof(this.form_radio_validation) === 'function') { this.form_radio_validation(); }

		// Select
		if(typeof(this.form_select) === 'function') { this.form_select(); }

		// Select min max
		if(typeof(this.form_select_min_max) === 'function') { this.form_select_min_max(); }

		// Select2
		if(typeof(this.form_select2) === 'function') { this.form_select2(); }

		// Range sliders
		this.form_help_value();

		// Dates
		if(typeof(this.form_date) === 'function') { this.form_date(); }

		// Colors
		if(typeof(this.form_color) === 'function') { this.form_color(); }

		// Signature
		if(typeof(this.form_signature) === 'function') { this.form_signature(); }

		// Email
		if(typeof(this.form_email) === 'function') { this.form_email(); }

		// Label
		this.form_label();

		// Tel inputs
		if(typeof(this.form_tel) === 'function') { this.form_tel(); }

		// Rating
		if(typeof(this.form_rating) === 'function') { this.form_rating(); }

		// Google Map
		if(typeof(this.form_google_map) === 'function') { this.form_google_map(); }

		// Google Address
		if(typeof(this.form_google_address) === 'function') { this.form_google_address(); }

		// Google Address
		if(typeof(this.form_google_address) === 'function') { this.form_google_address(); }

		// Google Route
		if(typeof(this.form_google_route) === 'function') { this.form_google_route(); }

		// Dedupe value scope
		this.form_dedupe_value_scope();

		// File inputs
		if(typeof(this.form_file) === 'function') { this.form_file(); }

		// Text input and textarea character and word count
		if(typeof(this.form_character_word_count) === 'function') { this.form_character_word_count(); }

		// Password strength meter
		if(typeof(this.form_password_strength_meter) === 'function') { this.form_password_strength_meter(); }

		// E-Commerce
		if(typeof(this.form_ecommerce) === 'function') { this.form_ecommerce(); }

		// Repeatable section hidden field
		this.section_repeatable_hidden_field();

		// Form validation - Real time
		this.form_validate_real_time();

		// Calculations
		if(typeof(this.form_calc) === 'function') { this.form_calc(false, section_id); }

		// Credit Card
		this.form_credit_card();

		// Transform
		this.form_transform();

		// Navigation
		this.form_navigation();

		// Analytics
		if(typeof(this.form_analytics) === 'function') { this.form_analytics(); }

		// Client side form conditions
		if(typeof(this.form_conditional) === 'function') { this.form_conditional(); }

		// Progress
		if(typeof(this.form_progress) === 'function') { this.form_progress(); }

		// Required
		this.form_required();

		// Field Cascacding
		if(typeof(this.form_cascade) === 'function') { this.form_cascade(); }

		// Geo
		if(typeof(this.form_geo) === 'function') { this.form_geo(); }

		// Bypass
		this.form_bypass(false);
	}

	// Initialize after section remove
	$.WS_Form.prototype.section_remove_init = function(section_id) {

		// Remove signatures from array
		if(this.signatures.length > 0) {

			for(var signature_index in this.signatures) {

				if(!this.signatures.hasOwnProperty(signature_index)) { continue; }

				var signature = this.signatures[signature_index];

				var signature_name = signature.name;

				var signature_obj_field = $('[name="' + this.esc_selector(signature_name) + '"]', this.form_canvas_obj);

				if(!signature_obj_field.length) {

					this.signatures.splice(signature_index, 1);
				}
			}
		}

		// Delete unused validation_message_cache elements
		for(var section_repeatable_index in this.validation_message_cache[section_id]) {

			var section_obj = $('[data-repeatable][data-id="' + this.esc_selector(section_id) + '"][data-repeatable-index="' + this.esc_selector(section_repeatable_index) + '"]', this.form_canvas_obj);
			if(!section_obj.length) {

				delete this.validation_message_cache[section_id][section_repeatable_index];
			}
		}

		// Delete unused calcs
		if(typeof(this.form_calc) === 'function') { this.form_calc_clean(); }

		// Navigation - Enable / Disable
		this.section_repeatable_navigation_enable_disable(section_id);

		// Labels
		this.section_repeatable_labels(section_id);

		// Re-validate the form
		this.form_validate_real_time_process();

		// Process form progress
		if(typeof(this.form_progress_process) === 'function') { this.form_progress_process(); }

		// Repeatable section hidden field
		this.section_repeatable_hidden_field();

		// Calculations
		if(typeof(this.form_calc) === 'function') { this.form_calc(false, section_id); }

		// Recalculate e-commerce
		if(
			this.has_ecommerce &&
			(typeof(this.form_ecommerce_calculate) === 'function')
		) {
			this.form_ecommerce_calculate();
		}
	}

	// Dedupe values by repeatable section
	$.WS_Form.prototype.form_dedupe_value_scope = function() {

		var ws_this = this;

		$('[data-value-scope]:not([data-init-value-scope])', this.form_canvas_obj).each(function() {

			// Check this field is in a repeatable section
			if(!$(this).closest('[data-repeatable]')) { return; }

			// Process
			ws_this.form_dedupe_value_scope_process($(this), true);

			$(this).on('change', function() {

				// Process
				ws_this.form_dedupe_value_scope_process($(this), false);
			});

			// Add init attribute so it does not initialize again
			$(this).attr('data-init-value-scope', '');
		})
	}

	$.WS_Form.prototype.form_dedupe_value_scope_process_all = function() {

		var ws_this = this;

		$('select[data-value-scope]', this.form_canvas_obj).each(function() {

			// Check this field is in a repeatable section
			if(!$(this).closest('[data-repeatable]')) { return; }

			// Process
			ws_this.form_dedupe_value_scope_process($(this), false);
		})
	}

	$.WS_Form.prototype.form_dedupe_value_scope_process = function(obj, initial) {

		var ws_this = this;

		// Get field id
		var field_id = this.get_field_id(obj);

		// Get field type
		var field_type = this.get_field_type(obj);

		switch(field_type) {

			case 'select' :
			case 'price_select' :

				if(!initial) {

					$('[name^="' + this.esc_selector(ws_form_settings.field_prefix + field_id) + '["] option[data-dedupe]', this.form_canvas_obj).removeAttr('data-dedupe').removeAttr('disabled', false);
				}

				$('[name^="' + this.esc_selector(ws_form_settings.field_prefix + field_id) + '["]', this.form_canvas_obj).each(function() {

					// Get section ID
					var section_id = ws_this.get_section_id($(this));

					// Get section repeatable index
					var section_repeatable_index = ws_this.get_section_repeatable_index($(this));

					var values = $(this).val();
					if(typeof(values) !== 'object') { values = [values]; }

					for(var value_index in values) {

						if(!values.hasOwnProperty(value_index)) { continue; }

						var value = values[value_index];

						if(value === '') { continue; }

						// Run through each repeatable section that does not match this section
						$('[id^="' + ws_this.esc_selector(ws_this.form_id_prefix + 'section-' + section_id) + '-"]:not([data-repeatable-index="' + ws_this.esc_selector(section_repeatable_index) + '"])').each(function() {

							var field_matching_obj = $('[name^="' + ws_this.esc_selector(ws_form_settings.field_prefix + field_id) + '["]', $(this));

							$('option[value="' + ws_this.esc_selector(value) + '"]:not([data-dedupe])', field_matching_obj).prop('selected', false).prop('disabled', true).attr('data-dedupe', '');
						});
					}
				});

				break;

			case 'checkbox' :
			case 'radio' :
			case 'price_checkbox' :
			case 'price_radio' :

				if(!initial) {

					$('[name^="' + this.esc_selector(ws_form_settings.field_prefix + field_id) + '["][data-dedupe]', this.form_canvas_obj).removeAttr('data-dedupe').prop('disabled', false);
				}

				$('[name^="' + this.esc_selector(ws_form_settings.field_prefix + field_id) + '["]:checked', this.form_canvas_obj).each(function() {

					// Re-check if checked (Because it might have been unchecked below)
					if(!$(this).is(':checked')) { return; }

					// Get section ID
					var section_id = ws_this.get_section_id($(this));

					// Get section repeatable index
					var section_repeatable_index = ws_this.get_section_repeatable_index($(this));

					// Get field type
					var value = $(this).attr('value');

					// Run through each repeatable section that does not match this section
					$('[id^="' + ws_this.esc_selector(ws_this.form_id_prefix) + 'section-' + ws_this.esc_selector(section_id) + '-"]:not([data-repeatable-index="' + ws_this.esc_selector(section_repeatable_index) + '"])').each(function() {

						var field_matching_obj = $('[name^="' + ws_this.esc_selector(ws_form_settings.field_prefix + field_id) + '["][value="' + ws_this.esc_selector(value) + '"]', $(this));

						field_matching_obj.prop('checked', false).prop('disabled', true).attr('data-dedupe', '');
					});
				});

				break;
		}
	}

	$.WS_Form.prototype.get_section_repeat_min = function(section) {

		// Section repeat - Min
		var section_repeat_min = this.get_object_meta_value(section, 'section_repeat_min', 1);
		if(
			(section_repeat_min == '') ||
			isNaN(section_repeat_min)

		) { section_repeat_min = 1; } else { section_repeat_min = parseInt(section_repeat_min, 10); }
		if(section_repeat_min < 1) { section_repeat_min = 1; }

		return section_repeat_min;
	}

	$.WS_Form.prototype.get_section_repeat_max = function(section, section_repeat_min) {

		if(typeof(section_repeat_min) === 'undefined') { section_repeat_min = this.get_section_repeat_min(section); }

		// Section repeat - Max
		var section_repeat_max = this.get_object_meta_value(section, 'section_repeat_max', false);
		if(
			(section_repeat_max == '') ||
			isNaN(section_repeat_min)

		) { section_repeat_max = false; } else { section_repeat_max = parseInt(section_repeat_max, 10); }

		if(
			(section_repeat_max !== false) &&
			(section_repeat_max < section_repeat_min)
		) {

			section_repeat_min = section_repeat_max;
		}

		return section_repeat_max;
	}

	$.WS_Form.prototype.get_section_repeat_default = function(section, section_repeat_min, section_repeat_max) {

		if(typeof(section_repeat_min) === 'undefined') { section_repeat_min = this.get_section_repeat_min(section); }
		if(typeof(section_repeat_max) === 'undefined') { section_repeat_max = this.get_section_repeat_max(section, section_repeat_min); }

		// Section repeat - Default
		var section_repeat_default = this.get_object_meta_value(section, 'section_repeat_default', 1);
		if(
			(section_repeat_default == '') ||
			isNaN(section_repeat_default)

		) { section_repeat_default = 1; } else { section_repeat_default = parseInt(section_repeat_default, 10); }

		if(section_repeat_default < section_repeat_min) {

			section_repeat_default = section_repeat_min;
		}
		if((section_repeat_max !== false) && (section_repeat_default > section_repeat_max)) {

			section_repeat_default = section_repeat_max;
		}

		return section_repeat_default;
	}

})(jQuery);
