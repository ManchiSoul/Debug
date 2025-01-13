(function($) {

	'use strict';

	$.WS_Form = function(atts) {

		// Global this (Only for admin, public side needs multiple instances)
		$.WS_Form.this = this;

		// Admin?
		this.is_admin = this.set_is_admin();

		// User roles
		this.user_roles = ws_form_settings.user_roles;

		// Form interface built?
		this.form_interface = false;

		// Group data cache
		this.group_data_cache = [];
		this.group_index = 0;

		// Section data cache
		this.section_data_cache = [];

		// Field data cache
		this.field_data_cache = [];

		// Field data cache
		this.action_data_cache = [];

		// Validation message cache
		this.validation_message_cache = [];

		// Invalid feedback cache
		this.invalid_feedback_cache = [];

		// Object cache
		this.object_cache = [];
		this.object_cache['condition'] = [];
		this.object_cache['then'] = [];
		this.object_cache['else'] = [];

		// Actions
		this.action = false;

		// New object data (for reverting fied data back to an older state)
		this.object_data_scratch = false;

		// Form history
		this.form_history = [];
		this.history_index = 0;

		// Framework
		this.framework_id = false;
		this.framework = false;
		this.framework_fields = false;

		// Column resizing
		this.column_resize_obj = false;
		this.column_size_value = 0;
		this.column_size_value_old = 0;
		this.offset_value = 0;
		this.offset_value_old = 0;

		// Draggable
		this.dragged_field = null;
		this.dragged_field_in_section = null;
		this.dragged_section = null;
		this.dragged_section_in_group = null;
		this.dragging = false;

		// Sortable
		this.next_sibling_id_old = null;
		this.section_id_old = null;
		this.group_id_old = null;
		this.section_repeatable_dragged = false;

		// Repeatable sections
		this.section_repeatable_indexes = {};

		// Checksum
		this.checksum = false;
		this.checksum_setTimeout = false;
		this.published_checksum = '';

		// reCAPTCHA
		this.recaptchas = [];
		this.recaptchas_v2_default = [];
		this.recaptchas_v2_invisible = [];
		this.recaptchas_v3_default = [];
		this.recaptchas_conditions = [];
		this.timeout_recaptcha = 30000;

		// hCaptcha
		this.hcaptchas = [];
		this.hcaptchas_default = [];
		this.hcaptchas_invisible = [];
		this.hcaptchas_conditions = [];
		this.timeout_hcaptcha = 30000;

		// Turnstile
		this.turnstiles = [];
		this.turnstiles_default = [];
		this.turnstiles_conditions = [];
		this.timeout_turnstile = 30000;

		// Conditional (Admin)
		this.conditional = false;
		this.conditional_data = false;
		this.conditional_index_dragged_from = false;
		this.conditional_icons_array = false;
		this.conditional_highlight_array = false;
		this.conditional_highlight_row_index = false;
		this.conditional_row_array = false;

		// Conditional (Public)
		this.conditional_actions_run_action = [];
		this.conditional_actions_run_save = [];
		this.conditional_actions_run_submit = [];
		this.conditional_cache = false;
		this.conditional_event_selector_to_condition = [];

		// Signatures
		this.signatures = [];
		this.signatures_by_name = [];
		this.timeout_signature = 30000;

		// Google maps
		this.google_maps = [];
		this.timeout_google_maps = 30000;

		// Analytics
		this.timeout_analytics_google = 30000;
		this.timeout_analytics_data_layer = 30000;
		this.analytics_function = [];

		// Text editor
		this.text_editor_index = 1;

		// Code editor
		this.code_editor_index = 1;
		// Events reset array (Used on form rebuild)
		this.form_events_reset = [];

		// Key down events
		this.keydown = [];

		// Page component load timeout
		this.timeout_interval = 100;

		// API
		this.api_call_queue = [];
		this.api_call_queue_running = false;

		// Number to word
		this.number_to_word = ['nought','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen','twenty'];

		// Old object label
		this.label_old = '';

		// Submit data
		this.submit = false;
		this.action_js = [];
		this.form_draft = false;

		// Populate
		this.submit_auto_populate = false;

		// Prefixes
		this.form_id_prefix = 'wsf-';
		this.field_name_prefix = ws_form_settings.field_prefix;

		// Meta key options
		this.meta_key_options_cache = [];

		// Options cache
		this.options_action_objects = [];
		this.options_action_cache = [];

		// Sidebars
		this.sidebar_conditions = [];
		this.sidebar_expanded_obj = false;
		this.sidebar_lock_count = 0;

		// Devices
		this.touch_device = ('ontouchend' in document);

		// Hash
		this.hash = '';

		// Token
		this.token = '';
		this.token_validated = false;

		// Form locking
		this.form_post_locked = false;
		this.form_post_lock_start = 0;
		this.form_post_lock_duration_max = 1000;

		// Form loader
		this.form_loader_showing = false;
		this.form_loader_show_start = 0;
		this.form_loader_show_duration_max = this.form_post_lock_duration_max;
		this.form_loader_timeout_id = false;

		// Required fields bypass
		this.field_required_bypass = [];

		// Real time form validation
		this.form_valid = false;
		this.form_valid_old = null;
		this.form_validation_real_time_hooks = [];

		// E-Commerce
		this.ecommerce_cart_price_type = [];
		this.has_ecommerce = false;
		this.form_ecommerce_calculate_enabled = true;

		// Progress
		this.progress = 0;
		this.progress_percent = '0%';
		this.progress_array = [];
		this.progress_class_complete = '';
		this.progress_class_incomplete = '';

		// Upload JSON to object
		this.upload_obj = false;

		// Dates
		var test_date_input = document.createElement('input');
		test_date_input.setAttribute('type', 'date');
		this.native_date = (test_date_input.type == 'date');
		this.timeout_datetimepicker = 5000;

		// Colors
		var test_color_input = document.createElement('input');
		test_color_input.setAttribute('type', 'color');
		this.native_color = (test_color_input.type == 'color');

		// Custom action URL
		this.form_action_custom = false;
		this.form_ajax = true;

		// Object focus
		this.object_focus = false;

		// Field type click drag check
		this.field_type_click_drag_check = false;
		this.section_id_click_drag_check = false;

		// Password strength meter
		this.password_strength_status = 0;

		// Cascade cache
		this.cascade_cache = [];

		// Search AJAX cache
		this.select_ajax_cache = [];

		// Preview
		this.preview_window = undefined;
		// Parse variable: #calc
		this.calc = [];

		// Media picker
		this.file_frame = false;
		this.file_frame_input_obj = false;

		// API call handle
		this.api_call_handle = [];

		// Is this form in a visual builder?
		this.visual_builder = false;

		// Form bypass enabled
		this.form_bypass_enabled = false;

		// Focus fields with validation errors after submit
		this.action_js_process_validation_focus = true;

		// Actions require Google Analytics
		this.action_ga = false;

		// DropzoneJS
		this.dropzonejs_processes = 0;
		this.dropzonejs_index_dragged_from = 0;

		// Conversational
		this.conversational = false;

		// Form stat add lock
		this.form_stat_add_lock = false;

		// Form geo cache
		this.form_geo_cache = false;
		this.form_geo_cache_request = false;
		this.form_geo_stack = [];
	}

	// Render
	$.WS_Form.prototype.render = function(atts) {

		var ws_this = this;

		// Check attributes
		if(typeof(atts) === 'undefined') { this.error('error_attributes'); }
		if(typeof(atts.obj) === 'undefined') { this.error('error_attributes_obj'); }
		if(typeof(atts.form_id) === 'undefined') { this.error('error_attributes_form_id'); }

		// Form canvas (Could be something other than form if element defined)
		this.form_canvas_obj = atts.obj instanceof $ ? atts.obj : $(atts.obj);

		// Form object ID
		this.form_obj_id = this.form_canvas_obj.attr('id');

		// Form ID
		this.form_id = parseInt(atts.form_id, 10);
		if(this.form_id === 0) { return; }

		// Form instance
		this.form_instance_id = parseInt(this.form_canvas_obj.attr('data-instance-id'), 10);
		if(this.form_instance_id === 0) { return; }

		// Form ID prefix
		this.form_id_prefix = this.is_admin ? 'wsf-' : 'wsf-' + this.form_instance_id + '-';
		this.form_id_prefix_function = this.is_admin ? 'wsf_' : 'wsf_' + this.form_instance_id + '_';

		// Form object (Form tag)
		this.form_obj = this.form_canvas_obj.closest('form');
		if(this.form_obj.length) {

			this.form_obj.attr('novalidate', '');

		} else {

			this.form_obj = this.form_canvas_obj;
		}

		// Move attributes and classes to outer form tag
		if(this.form_obj[0] != this.form_canvas_obj[0]) {

			// Add wsf-form to form
			if(!this.form_obj.hasClass('wsf-form')) { this.form_obj.addClass('wsf-form'); }

			// Remove wsf-form from canvas
			if(this.form_canvas_obj.hasClass('wsf-form')) { this.form_canvas_obj.removeClass('wsf-form'); }

			// Move data-wsf-style-id from canvas to form
			if(typeof(this.form_canvas_obj.attr('data-wsf-style-id')) !== 'undefined') {

				// Add data-wsf-style-id to form
				this.form_obj.attr('data-wsf-style-id', this.form_canvas_obj.attr('data-wsf-style-id'));

				// Remove data-wsf-style-id from canvas
				this.form_canvas_obj.removeAttr('data-wsf-style-id');
			}
		}

		// Empty form canvas
		this.form_canvas_obj.html('');

		// Get configuration
		this.get_configuration(function() {

			// Get form
			ws_this.get_form(function() {

				// Render debug console
				if(typeof(ws_this.debug) === 'function') { ws_this.debug(); }

				// Render loader
				if(!ws_this.is_admin) {

					// Initialize loader
					if(typeof(ws_this.form_loader) === 'function') { ws_this.form_loader(); }

					// Show loader
					if(typeof(ws_this.form_loader_show) === 'function') { ws_this.form_loader_show('render'); }
				}
				// Initialize
				ws_this.init();
			});
		});
	}

	// Configuration objects
	$.WS_Form.configured = false;
	$.WS_Form.css_rendered = false;
	$.WS_Form.debug;
	$.WS_Form.debug_rendered = false;
	$.WS_Form.analytics;
	$.WS_Form.tracking;
	$.WS_Form.ecommerce;
	$.WS_Form.calc;
	$.WS_Form.styler;
	$.WS_Form.styler_rendered = false;
	$.WS_Form.settings_plugin;
	$.WS_Form.settings_form = null;
	$.WS_Form.frameworks;
	$.WS_Form.parse_variables;
	$.WS_Form.parse_variable_help;
	$.WS_Form.parse_variable_repairable;
	$.WS_Form.actions;
	$.WS_Form.field_types;
	$.WS_Form.field_type_cache = [];
	$.WS_Form.file_types;
	$.WS_Form.meta_keys;
	$.WS_Form.meta_keys_required_setting = [];
	$.WS_Form.breakpoints;
	$.WS_Form.data_sources;
	$.WS_Form.templates_section;

	// Get configuration
	$.WS_Form.prototype.get_configuration = function(success_callback, force, bypass_loader) {

		if(typeof(force) === 'undefined') { force = false; }
		if(typeof(bypass_loader) === 'undefined') { bypass_loader = false; }

		// Clear caches
		this.options_action_cache = [];

		// Loader on
		if(!bypass_loader) { this.loader_on(); }

		if(!$.WS_Form.configured || force) {

			if(typeof(wsf_form_json_config) === 'undefined') {

				// Get configuration via AJAX
				var ws_this = this;
				this.api_call('config', 'GET', false, function(response) {

					// Set configuration
					ws_this.set_configuration(response.data);

					window.wsf_form_json_config = response.data;

					if(typeof(success_callback) === 'function') { success_callback(); }

				}, false, bypass_loader);

			} else {

				// Get configuration from dom
				this.set_configuration(wsf_form_json_config);

				if(typeof(success_callback) === 'function') { success_callback(); }
			}

		} else {

			// Get form without configuration (Configuration already loaded)
			if(typeof(success_callback) === 'function') { success_callback(); }
		}
	}

	// Set configuration
	$.WS_Form.prototype.set_configuration = function(config) {

		// Store configuration
		$.WS_Form.settings_plugin = config.settings_plugin;
		$.WS_Form.settings_form = config.settings_form;
		$.WS_Form.frameworks = config.frameworks;
		$.WS_Form.field_types = config.field_types;
		$.WS_Form.file_types = config.file_types;
		$.WS_Form.meta_keys = config.meta_keys;
		$.WS_Form.parse_variables = config.parse_variables;
		$.WS_Form.parse_variables_repairable = config.parse_variables_repairable;
		$.WS_Form.parse_variable_help = config.parse_variable_help;
		$.WS_Form.actions = config.actions;
		$.WS_Form.data_sources = config.data_sources;
		$.WS_Form.templates_section = config.templates_section;
		$.WS_Form.calc = config.calc;
		$.WS_Form.tracking = config.tracking;
		$.WS_Form.ecommerce = config.ecommerce;

		if(!this.is_admin) {

			// We base64 decode and then JSON parse this config to avoid any analytics scripts appearing in the config
			$.WS_Form.analytics = JSON.parse(atob(config.analytics));
		}

		// Sort parse_variables
		if(typeof($.WS_Form.parse_variables) !== 'undefined') {

			// Turn parse variables into an array for sorting
			var parse_variables_sort_array = [];
			for(var parse_variables_key in $.WS_Form.parse_variables) {

				if(!$.WS_Form.parse_variables.hasOwnProperty(parse_variables_key)) { continue; }

				parse_variables_sort_array.push([parse_variables_key, $.WS_Form.parse_variables[parse_variables_key].priority]);
			}

			// Sort array
			parse_variables_sort_array.sort(function(a,b){

				var priority_a = (typeof(a[1]) !== 'undefined') ? a[1] : 0;
				var priority_b = (typeof(b[1]) !== 'undefined') ? b[1] : 0;

				return priority_b - priority_a;
			});

			// Build new object
			var parse_variables = {};
			for(var parse_variables_key in parse_variables_sort_array) {

				if(!parse_variables_sort_array.hasOwnProperty(parse_variables_key)) { continue; }

				var parse_variables_sort_key = parse_variables_sort_array[parse_variables_key][0];

				parse_variables[parse_variables_sort_key] = $.WS_Form.parse_variables[parse_variables_sort_key];
			}

			// Put it back
			$.WS_Form.parse_variables = parse_variables;
		}

		// Debug
		if(typeof(config.debug) !== 'undefined') { $.WS_Form.debug = config.debug; }

		// Build field type cache
		this.field_type_cache_build();

		// Set that WS Form is configured
		$.WS_Form.configured = true;
	}

	// Get configuration
	$.WS_Form.prototype.get_form = function(success_callback) {

		// Show error if form ID is 0 or unspecified
		if(this.form_id == 0) {

			this.error('error_form_id');

			this.loader_off();

			return;
		}

		// Set form data-id attribute
		$('#' + this.form_obj_id).attr('data-id', this.form_id);

		if(
			(typeof(window.wsf_form_json) === 'undefined') ||
			(typeof(window.wsf_form_json[this.form_id]) === 'undefined')
		) {

			// Get form from API
			var ws_this = this;
			this.api_call('form/' + this.form_id + '/full/?wsf_fp=true', 'GET', false, function(response) {

				// Store form data
				ws_this.form = response.form;

				// Build cache
				if(typeof(window.wsf_form_json) === 'undefined') { window.wsf_form_json = []; }
				window.wsf_form_json[ws_this.form_id] = response.form;

				// Build data cache
				ws_this.data_cache_build();

				// Success callback
				if(typeof(success_callback) === 'function') { success_callback(); }

				// Loader off
				ws_this.loader_off();
			});

		} else {

			// Get form from dom
			this.form = window.wsf_form_json[this.form_id];

			// Build data cache
			this.data_cache_build();

			// Success callback
			if(typeof(success_callback) === 'function') { success_callback(); }

			// Loader off
			this.loader_off();
		}
	}

	// Build form
	$.WS_Form.prototype.form_build = function() {

		// Timer - Start
		this.timer_start = new Date();

		// Clear form HTML
		this.form_canvas_obj.children().not('.wsf-loader').remove();

		// Append form HTML
		this.form_canvas_obj.append(this.get_form_html(this.form));

		// Add container class
		if(!this.is_admin) {

			var class_form_wrapper = this.get_object_meta_value(this.form, 'class_form_wrapper', '');
			if(class_form_wrapper != '') {

				var form_class = this.form_canvas_obj.attr('class');
				form_class += ' '  + class_form_wrapper.trim();
				this.form_canvas_obj.attr('class', form_class);
			}
		}

		// Render form
		this.form_render();

		// Timer - Duration
		this.timer_duration = new Date() - this.timer_start;

		// Timer - Debug
		if($.WS_Form.debug_rendered) {

			this.debug_info('debug_info_duration', this.timer_duration + 'ms');
		}
	}

	// Get form style ID
	$.WS_Form.prototype.get_form_style_id = function() {

		var framework_id = this.is_admin ? 'ws-form' : $.WS_Form.settings_plugin.framework;
		return $.WS_Form.frameworks.types[framework_id];
	}

	// Get current framework
	$.WS_Form.prototype.get_framework = function() {

		var framework_id = this.is_admin ? 'ws-form' : $.WS_Form.settings_plugin.framework;
		return $.WS_Form.frameworks.types[framework_id];
	}

	// Get form HTML
	$.WS_Form.prototype.get_form_html = function(form) {

		// Form
		if(typeof(form) === 'undefined') { return ''; }
		if(typeof(form.groups) === 'undefined') { return ''; }

		// Get current framework
		var framework = this.get_framework();
		var framework_form = framework['form'][this.is_admin ? 'admin' : 'public'];

		// Label
		var form_label = this.esc_html(form.label);
		var label_render = !this.is_admin && (this.get_object_meta_value(form, 'label_render', 'on') == 'on') ? true : false;

		if(label_render) {

			var label_mask_form = !this.is_admin ? this.get_object_meta_value(this.form, 'label_mask_form', '') : '';
			var mask = (label_mask_form != '') ? label_mask_form : (typeof framework_form['mask_label'] !== 'undefined') ? framework_form['mask_label'] : '';
			var mask_values = {'label': form_label};
			var label_html_parsed = this.mask_parse(mask, mask_values);

		} else {

			var label_html_parsed = '';
		}

		// Tabs
		var form_html = this.get_tabs_html(form);

		// Groups
		form_html += this.get_groups_html(form.groups);

		// Parse wrapper form
		var mask = framework_form['mask_single'];
		var mask_values = {

			'form': form_html,
			'id': this.form_id_prefix + 'tabs',
			'label': label_html_parsed
		};
		var form_html_parsed = this.comment_html('Form: ' + form_label) + this.mask_parse(mask, mask_values) + this.comment_html('Form: ' + form_label, true);

		return form_html_parsed;
	}

	// Get tabs HTML
	$.WS_Form.prototype.get_tabs_html = function(form) {

		// Get groups
		if(typeof(form.groups) === 'undefined') { return ''; }
		var groups = form.groups;

		// Get group count
		var group_count = Object.keys(groups).length;

		// No tabs if there is only 1 group and we are not in admin
		if((group_count == 1) && !this.is_admin) { return ''; }

		var tabs_html = '';

		// Get current framework
		var framework = this.get_framework();
		var framework_tabs = framework['tabs'][this.is_admin ? 'admin' : 'public'];

		// Get tab index cookie if settings require it
		var index = (this.get_object_meta_value(this.form, 'cookie_tab_index')) ? this.cookie_get('tab_index', 0) : 0;

		// Groups
		if((group_count > 1) || this.is_admin) {

			// Build group, section and field data caches
			for(var group_index in groups) {

				if(!groups.hasOwnProperty(group_index)) { continue; }

				var group = groups[group_index];

				tabs_html += this.get_tab_html(group, group_index, (index == group_index));
			}

			// Attributes
			var attributes = '';

			// Hidden tabs?
			if(!this.is_admin) {

				var tabs_hidden = this.get_object_meta_value(form, 'tabs_hide', false);
				if(tabs_hidden) {

					attributes += ' style="display: none;"';
				}
			}

			// Classes
			var class_array = ['wsf-group-tabs'];

			// Class wrapper
			if(!this.is_admin) {

				var class_tabs_wrapper = this.get_object_meta_value(form, 'class_tabs_wrapper', false);
				if(class_tabs_wrapper != '') { class_array.push(class_tabs_wrapper.trim()); }
			}

			// Parse wrapper tabs
			var mask = framework_tabs['mask_wrapper'];
			var mask_values = {

				'attributes': attributes,
				'class': class_array.join(' '),
				'tabs': tabs_html,
				'id': this.form_id_prefix + 'tabs'
			};
			var tabs_html_parsed = this.comment_html(this.language('comment_group_tabs')) + this.mask_parse(mask, mask_values) + this.comment_html(this.language('comment_group_tabs'), true);

			return tabs_html_parsed;

		} else {

			return '';
		}
	}

	// Get tab HTML
	$.WS_Form.prototype.get_tab_html = function(group, index, is_active) {

		if(typeof(index) === 'undefined') { index = 0; }
		if(typeof(is_active) === 'undefined') { is_active = false; }

		// Get current framework for tabs
		var framework = this.get_framework();
		var framework_tabs = framework['tabs'][this.is_admin ? 'admin' : 'public'];

		// Get group label
		var group_label = this.esc_html(group.label);

		// Attributes
		var attributes = '';

		// Hidden
		var hidden = !this.is_admin && (this.get_object_meta_value(group, 'hidden', '') == 'on') ? true : false;
		if(hidden) {

			attributes = ' style="display: none;" data-wsf-group-hidden';
		}

		// Parse and return wrapper tab
		var mask = framework_tabs['mask_single'];
		var mask_values = {

			'attributes': attributes,
			'data_id': group.id,
			'href': '#' + this.form_id_prefix + 'group-' + group.id,
			'label': group_label
		};

		// Active tab
		if(is_active && (typeof(framework_tabs['active']) !== 'undefined')) {

			mask_values['active'] = framework_tabs['active'];

		} else {

			mask_values['active'] = '';
		}

		return this.mask_parse(mask, mask_values);
	}

	// Get groups HTML
	$.WS_Form.prototype.get_groups_html = function(groups) {

		var group_html = '';

		// Check groups
		if(typeof(groups) === 'undefined') { return ''; }

		// Get group count
		var group_count = Object.keys(groups).length;

		// Get current framework
		var framework = this.get_framework();
		var framework_groups = framework['groups'][this.is_admin ? 'admin' : 'public'];

		// Get tab index cookie if settings require it
		var group_index_current = (this.get_object_meta_value(this.form, 'cookie_tab_index')) ? this.cookie_get('tab_index', 0) : 0;

		// Build tabs content
		var groups_html = '';
		var use_mask = this.is_admin || (group_count > 1);

		var group_index_zero = 0;
		for(var group_index in groups) {

			if(!groups.hasOwnProperty(group_index)) { continue; }

			var group = groups[group_index];

			// Render group
			groups_html += this.get_group_html(group, (group_index == group_index_current), use_mask, group_index_zero++);
		}

		// Add container class
		var class_array = ['wsf-groups'];

		// Parse wrapper form
		var mask = (use_mask ? framework_groups['mask_wrapper'] : '#groups');
		var mask_values = {

			'class': class_array.join(' '),
			'column_count' : $.WS_Form.settings_plugin.framework_column_count,
			'groups': groups_html,
			'id': this.form_id_prefix + 'tabs'
		};
		var groups_html_parsed = (use_mask ? this.comment_html(this.language('comment_groups')) : '') + this.mask_parse(mask, mask_values) + (use_mask ? this.comment_html(this.language('comment_groups'), true) : '');

		return groups_html_parsed;
	}

	// Get group HTML
	$.WS_Form.prototype.get_group_html = function(group, is_active, use_mask, group_index) {

		if(typeof is_active === 'undefined') { is_active = false; }
		if(typeof use_mask === 'undefined') { use_mask = true; }

		// Get current framework
		var framework = this.get_framework();
		var framework_groups = framework['groups'][this.is_admin ? 'admin' : 'public'];

		var group_id = this.esc_html(group.id);

		// Label
		var group_label = this.esc_html(group.label);
		var label_render = !this.is_admin && (this.get_object_meta_value(group, 'label_render', 'on') == 'on') ? true : false;

		if(label_render) {

			var label_mask_group = !this.is_admin ? this.get_object_meta_value(this.form, 'label_mask_group', '') : '';
			var mask = (label_mask_group != '') ? label_mask_group : (typeof framework_groups['mask_label'] !== 'undefined') ? framework_groups['mask_label'] : '';
			var mask_values = {'label': group_label};
			var label_html_parsed = this.mask_parse(mask, mask_values);

		} else {

			var label_html_parsed = '';
		}

		// HTML
		var sections_html = this.get_sections_html(group);

		// Classes
		var class_array = [];

		// Class - Base
		if(typeof(framework_groups['class']) !== 'undefined') {

			class_array.push(framework_groups['class']);
		}

		// Class - Wrapper
		if(!this.is_admin) {

			// Wrapper set at form level
			var class_group_wrapper = this.get_object_meta_value(this.form, 'class_group_wrapper', '');
			if(class_group_wrapper != '') { class_array.push(class_group_wrapper.trim()); }

			// Wrapper set at group level
			var class_group_wrapper = this.get_object_meta_value(group, 'class_group_wrapper', '');
			if(class_group_wrapper != '') { class_array.push(class_group_wrapper.trim()); }
		}

		// Class - Active
		if(is_active && (typeof framework_groups['class_active'] !== 'undefined')) {

			class_array.push(framework_groups['class_active']);
		}

		// Attributes
		var attributes_array = [];

		// Class - Hidden
		if(!this.is_admin) {

			var hidden = !this.is_admin && (this.get_object_meta_value(group, 'hidden', '') == 'on') ? true : false;
			if(hidden) { attributes_array.push('data-wsf-group-hidden'); }
		}

		// Parse wrapper tabs content
		var mask = (use_mask ? framework_groups['mask_single'] : '#group');
		var mask_values = {

			'attributes': ((attributes_array.length > 0) ? ' ' : '') + attributes_array.join(' '),
			'class': class_array.join(' '),
			'column_count' : $.WS_Form.settings_plugin.framework_column_count,
			'data_id': group.id,
			'data_group_index': group_index,
			'group': sections_html,
			'id': this.form_id_prefix + 'group-' + group_id,
			'label': label_html_parsed
		};

		var group_html_parsed = (use_mask ? this.comment_html(this.language('comment_group') + ': ' + group_label) : '') + this.mask_parse(mask, mask_values) + (use_mask ? this.comment_html(this.language('comment_group') + ': ' + group_label, true) : '');

		return group_html_parsed;
	}

	// Get sections html
	$.WS_Form.prototype.get_sections_html = function(group) {

		var sections_html = '';

		// Get current framework
		var framework = this.get_framework();
		var framework_sections = framework['sections'][this.is_admin ? 'admin' : 'public'];

		var group_id = group.id;
		var sections = group.sections

		// Check to see if section_repeatable data is available
		var section_repeatable = {};
		if(typeof(this.submit) === 'object') {

			if(typeof(this.submit['section_repeatable']) !== 'undefined') {

				section_repeatable = this.submit['section_repeatable'];
			}

		} else {

			// Check to see if auto populate data exists
			if(this.submit_auto_populate !== false) {

				if(typeof(this.submit_auto_populate['section_repeatable']) !== 'undefined') {

					section_repeatable = this.submit_auto_populate['section_repeatable'];
				}
			}
		}

		// Sections
		if(typeof(sections) === 'undefined') { return ''; }

		for(var section_index in sections) {

			if(!sections.hasOwnProperty(section_index)) { continue; }

			var section = sections[section_index];

			// Check for section repeaters
			var section_id_string = 'section_' + section.id;
			var section_repeatable_array = (

				(section_repeatable !== false) &&
				(typeof(section_repeatable[section_id_string]) !== 'undefined') &&
				(typeof(section_repeatable[section_id_string]['index']) !== 'undefined')

			) ? section_repeatable[section_id_string]['index'] : [false];

			// Loop through section_repeatable_array
			for(var section_repeatable_array_index in section_repeatable_array) {

				if(!section_repeatable_array.hasOwnProperty(section_repeatable_array_index)) { continue; }

				// Get repeatable index
				var section_repeatable_index = section_repeatable_array[section_repeatable_array_index];

				// Render section
				sections_html += this.get_section_html(section, section_repeatable_index);
			}
		}

		// Parse wrapper section
		var mask = framework_sections['mask_wrapper'];
		var mask_values = {

			'class': 'wsf-sections',
			'column_count' : $.WS_Form.settings_plugin.framework_column_count,
			'data_id': group.id, 'sections': sections_html,
			'id': this.form_id_prefix + 'sections-' + group.id
		};
		var sections_html_parsed = this.comment_html(this.language('comment_sections')) + this.mask_parse(mask, mask_values) + this.comment_html(this.language('comment_sections'), true);

		return sections_html_parsed;
	}

	// Get section html
	$.WS_Form.prototype.get_section_html = function(section, section_repeatable_index) {

		if(typeof(section_repeatable_index) === 'undefined') { section_repeatable_index = false; }

		// Is section repeatable?
		var section_repeatable = false;
		if(!this.is_admin) {

			var section_repeatable = (this.get_object_meta_value(section, 'section_repeatable', '') == 'on') ? true : false;
			if(section_repeatable) {

				if(section_repeatable_index === false) {

					// Find next available section_repeatable_index
					section_repeatable_index = 0;

					do {

						section_repeatable_index++;

					} while($('#' + this.form_id_prefix + 'section-' + section.id + '-repeat-' + section_repeatable_index).length);
				}
			}
		}

		// Attributes
		var attributes = '';

		// Get current framework
		var framework = this.get_framework();
		var framework_sections = framework['sections'][this.is_admin ? 'admin' : 'public'];

		// Get column class array
		var class_array = this.column_class_array(section);

		// Is section repeatable?
		if(section_repeatable && !this.is_admin) {

			attributes = this.attribute_modify(attributes, 'data-repeatable', '', true);
			attributes = this.attribute_modify(attributes, 'data-repeatable-index', section_repeatable_index, true);
		}

		// Add any base classes
		if(typeof(framework_sections['class_single']) !== 'undefined') { class_array = class_array.concat(framework_sections['class_single']); }

		// Public
		if(!this.is_admin) {

			// Wrapper set at form level
			var class_section_wrapper = this.get_object_meta_value(this.form, 'class_section_wrapper', '');
			if(class_section_wrapper != '') { class_array.push(class_section_wrapper.trim()); }

			// Wrapper set at section level
			var class_section_wrapper = this.get_object_meta_value(section, 'class_section_wrapper', '');
			if(class_section_wrapper != '') { class_array.push(class_section_wrapper.trim()); }

			// Vertical alignment
			var class_single_vertical_align = this.get_object_meta_value(section, 'class_single_vertical_align', '');
			if(class_single_vertical_align) {

				var class_single_vertical_align_config = this.get_field_value_fallback(false, false, 'class_single_vertical_align');

				if(typeof(class_single_vertical_align_config[class_single_vertical_align]) !== 'undefined') {

					class_array.push(class_single_vertical_align_config[class_single_vertical_align]);
				}
			}

			// Inline validation
			var validate_inline = this.get_object_meta_value(section, 'validate_inline', '');
			if(validate_inline != '') {

				var class_validated_array = (typeof(this.framework.fields.public.class_form_validated) !== 'undefined') ? this.framework.fields.public.class_form_validated : [];

				switch(validate_inline) {

					case 'on' :

						// Get validated class
						if(this.is_iterable(class_validated_array)) {

							class_array.push(...class_validated_array);

						} else {

							class_array.push(class_validated_array);
						}

						break;

					case 'change_blur' :

						attributes = this.attribute_modify(attributes, 'data-wsf-section-validated-class', class_validated_array.join(' '), true);

						break;
				}
			}

			// Conversational
			var form_conversational = this.get_object_meta_value(this.form, 'conversational', false);
			var conversational_full_height_section = this.get_object_meta_value(section, 'conversational_full_height_section', false);
			if(form_conversational && conversational_full_height_section) { class_array.push('wsf-form-conversational-section-full-height'); }
		}

		// Legend
		var section_label = this.esc_html(section.label)
		var label_render = this.is_admin || ((this.get_object_meta_value(section, 'label_render', 'on') == 'on') ? true : false);

		if(label_render) {

			var label_mask_section = !this.is_admin ? this.get_object_meta_value(this.form, 'label_mask_section', '') : '';
			var mask = (label_mask_section != '') ? label_mask_section : ((typeof framework_sections['mask_label'] !== 'undefined') ? framework_sections['mask_label'] : '');
			var mask_values = {'label': section_label};
			var label_html_parsed = this.mask_parse(mask, mask_values);

		} else {

			var label_html_parsed = '';
		}

		if(!this.is_admin) {

			// Disabled
			var disabled_section = this.get_object_meta_value(section, 'disabled_section', '');
			if(disabled_section == 'on') {

				attributes = this.attribute_modify(attributes, 'disabled', '', true);
				attributes = this.attribute_modify(attributes, 'aria-disabled', 'true', true);
			}

			// Hidden
			var hidden_section = this.get_object_meta_value(section, 'hidden_section', '');
			if(hidden_section == 'on') {

				attributes = this.attribute_modify(attributes, 'style', 'display: none;', true);
				attributes = this.attribute_modify(attributes, 'aria-live', 'polite', true);
				attributes = this.attribute_modify(attributes, 'aria-hidden', 'true', true);
			}

			// ARIA label
			var aria_label = this.get_object_meta_value(section, 'aria_label', '');
			if(aria_label == '') { aria_label = section.label; }
			attributes = this.attribute_modify(attributes, 'aria-label', aria_label, true);

			// Custom attributes
			attributes = this.custom_attributes(attributes, section, 'section', section_repeatable_index);
		}

		// HTML
		var section_single_html = this.get_fields_html(section, section_repeatable_index);

		// Parse wrapper section
		var mask = framework_sections['mask_single'];
		var mask_values = {

			'attributes': (attributes ? ' ' : '') + attributes,
			'class': class_array.join(' '),
			'column_count' : $.WS_Form.settings_plugin.framework_column_count,
			'data_id': section.id,
			'id': this.form_id_prefix + 'section-' + section.id + (section_repeatable_index ? ('-repeat-' + section_repeatable_index) : ''),
			'label': label_html_parsed,
			'section': section_single_html,
			'section_id': (($.WS_Form.settings_plugin.helper_section_id) ? ('<span class="wsf-section-id">' + this.language('id') + ': ' + section.id + '</span>') : '')
		};

		var section_html_parsed = this.comment_html(this.language('comment_section') + ': ' + section_label) + this.mask_parse(mask, mask_values) + this.comment_html(this.language('comment_section') + ': ' + section_label, true);

		return section_html_parsed;
	}

	// Process custom attributes
	$.WS_Form.prototype.custom_attributes = function(attributes, object, object_type, section_repeatable_index) {

		var mask_field_attributes_custom = this.get_object_meta_value(object, 'custom_attributes', false);

		if(
			(mask_field_attributes_custom !== false) &&
			(typeof(mask_field_attributes_custom) === 'object') &&
			(mask_field_attributes_custom.length > 0)
		) {

			// If object is not a field then set object to false ready for parse_variables_process
			if(object_type != 'field') { object = false; }

			// Run through each custom attribute
			for(var mask_field_attributes_custom_index in mask_field_attributes_custom) {

				if(!mask_field_attributes_custom.hasOwnProperty(mask_field_attributes_custom_index)) { continue; }

				// Get custom attribute name/value pair
				var mask_field_attribute_custom = mask_field_attributes_custom[mask_field_attributes_custom_index];

				// Check attribute name exists
				if(mask_field_attribute_custom.custom_attribute_name == '') { continue; }

				// Parse custom attribute value
				mask_field_attribute_custom.custom_attribute_value = this.parse_variables_process(mask_field_attribute_custom.custom_attribute_value, section_repeatable_index, false, object).output;

				// Build attribute (Only add value if one is specified)
				attributes = this.attribute_modify(attributes, mask_field_attribute_custom.custom_attribute_name, mask_field_attribute_custom.custom_attribute_value, true);
			}
		}

		return attributes;
	}

	// Get fields html
	$.WS_Form.prototype.get_fields_html = function(section, section_repeatable_index) {

		// Is section repeatable?
		var section_repeatable = (this.get_object_meta_value(section, 'section_repeatable', '') == 'on') ? true : false;
		if(typeof(section_repeatable_index) === 'undefined') { section_repeatable_index = (section_repeatable ? 0 : false); }

		var fields_html = '';

		// Get current framework for tabs
		var framework = this.get_framework();
		var framework_fields = framework['fields'][this.is_admin ? 'admin' : 'public'];

		var section_id = section.id;
		var fields = section.fields;

		// Legend
		var section_label = this.esc_html(section.label)
		var label_render = !this.is_admin && (this.get_object_meta_value(section, 'label_render', 'on') == 'on') ? true : false;

		if(label_render) {

			var label_mask_section = !this.is_admin ? this.get_object_meta_value(this.form, 'label_mask_section', '') : '';
			var mask = (label_mask_section != '') ? label_mask_section : ((typeof framework_fields['mask_wrapper_label'] !== 'undefined') ? framework_fields['mask_wrapper_label'] : '');
			var mask_values = {'label': section_label};
			var label_html_parsed = this.mask_parse(mask, mask_values);

		} else {

			var label_html_parsed = '';
		}

		// Fields
		if(typeof(fields) === 'undefined') { return ''; }

		for(var field_index in fields) {

			if(!fields.hasOwnProperty(field_index)) { continue; }

			var field = fields[field_index];

			// Render field
			fields_html += this.get_field_html(field, section_repeatable_index);
		}

		// Parse wrapper section
		var mask = framework_fields['mask_wrapper'];
		var mask_values = {

			'column_count' : $.WS_Form.settings_plugin.framework_column_count,
			'data_id': section.id,
			'fields': fields_html,
			'id': this.form_id_prefix + 'fields-' + section.id,
			'label': label_html_parsed
		};
		var fields_html_parsed = this.comment_html(this.language('comment_fields')) + this.mask_parse(mask, mask_values) + this.comment_html(this.language('comment_fields'), true);

		return fields_html_parsed;
	}

	// Process values for auto population
	$.WS_Form.prototype.value_populate_process = function(value, field) {

		if(value === null) { return ''; }

		// Parse by field type
		switch(field.type) {

			case 'datetime' :

				return ((typeof(this.get_date_by_type) === 'function') ? this.get_date_by_type(value, field) : '');

			case 'select' :
			case 'checkbox' :
			case 'radio' :
			case 'price_select' :
			case 'price_checkbox' :
			case 'price_radio' :

				// indexOf does a === check so convert object values to strings
				return Array.isArray(value) ? value.map(function(value) { return value ? value.toString() : ''; }) : value;

			case 'quantity' :

				// Get decimal separator
				var decimal_separator = $.WS_Form.settings_plugin.price_decimal_separator;

				// Check if decimal separator is not a period and value contains decimal separator
				if(
					(decimal_separator !== '.') &&
					(value.indexOf(decimal_separator) !== -1)
				) {

					// Process using currency settings
					value = this.get_number(value);
				}

				return value;

			default :

				return this.esc_html(value);
		}
	}

	// Get field html
	$.WS_Form.prototype.get_field_html = function(field, section_repeatable_index) {

		if(typeof(section_repeatable_index) === 'undefined') { section_repeatable_index = false; }

		// Attributes
		var attributes = [];

		// Repeatable
		if(section_repeatable_index !== false) { attributes.push('data-repeatable-index="' + this.esc_attr(section_repeatable_index) + '"'); }

		// Get current framework for tabs
		var framework = this.get_framework();
		var framework_fields = framework['fields'][this.is_admin ? 'admin' : 'public'];

		// Hidden
		if(!this.is_admin) {

			var hidden = (this.get_object_meta_value(field, 'hidden', '') == 'on');
			if(hidden) { attributes.push('style="display: none;" aria-live="polite" aria-hidden="true"'); }
		}

		// Get column class array
		var class_array = this.column_class_array(field);

		// Get sub type
		var sub_type = this.get_object_meta_value(field, 'sub_type', false);
		if(sub_type == '') { sub_type = false; }

		// Add any base classes
		var class_array_config = this.get_field_value_fallback(field.type, false, 'class_single', false, framework_fields, sub_type);
		if(class_array_config !== false) { class_array = class_array.concat(class_array_config); }

		// Add container class
		if(!this.is_admin) {

			// Wrapper set at form level
			var class_field_wrapper = this.get_object_meta_value(this.form, 'class_field_wrapper', '');
			if(class_field_wrapper != '') { class_array.push(class_field_wrapper.trim()); }

			// Wrapper set at field level
			var class_field_wrapper = this.get_object_meta_value(field, 'class_field_wrapper', '');
			if(class_field_wrapper != '') { class_array.push(class_field_wrapper.trim()); }

			// Vertical alignment
			var class_single_vertical_align = this.get_object_meta_value(field, 'class_single_vertical_align', '');
			if(class_single_vertical_align) {

				var class_single_vertical_align_config = this.get_field_value_fallback(field.type, false, 'class_single_vertical_align', false, framework_fields, sub_type);

				if(typeof(class_single_vertical_align_config[class_single_vertical_align]) !== 'undefined') {

					class_array.push(class_single_vertical_align_config[class_single_vertical_align]);
				}
			}

			// Inline validation
			var validate_inline = this.get_object_meta_value(field, 'validate_inline', '');
			if(validate_inline != '') {

				var class_validated_array = (typeof(this.framework.fields.public.class_form_validated) !== 'undefined') ? this.framework.fields.public.class_form_validated : [];

				switch(validate_inline) {

					case 'on' :

						// Get validated class
						if(this.is_iterable(class_validated_array)) {

							class_array.push(...class_validated_array);

						} else {

							class_array.push(class_validated_array);
						}

						break;

					case 'change_blur' :

						attributes.push('data-wsf-field-validated-class="' + class_validated_array.join(' ') + '"');

						break;
				}
			}
		}

		// Check to see if this field is available in the submit data
		var repeatable_suffix = ((section_repeatable_index !== false) ? '_' + section_repeatable_index : '');
		var submit_meta_key = ws_form_settings.field_prefix + field.id + repeatable_suffix;
		if(typeof(this.submit) === 'object') {

			if(
				(typeof(this.submit['meta']) !== 'undefined') &&
				(typeof(this.submit['meta'][submit_meta_key]) !== 'undefined') &&
				(typeof(this.submit['meta'][submit_meta_key]['value']) !== 'undefined') &&
				(this.submit['meta'][submit_meta_key]['value'] !== null)
			) {

				var value = this.submit['meta'][submit_meta_key]['value'];

				value = this.value_populate_process(value, field);
			}

		} else {

			// Check to see if auto populate data exists
			if(this.submit_auto_populate !== false) {

				if(
					(typeof(this.submit_auto_populate['data']) !== 'undefined') &&
					(typeof(this.submit_auto_populate['data'][submit_meta_key]) !== 'undefined') &&
					(this.submit_auto_populate['data'][submit_meta_key] !== null)
				) {

					var value = this.submit_auto_populate['data'][submit_meta_key];

					value = this.value_populate_process(value, field);
				}
			}
		}

		// Get field HTML (Admin returns blank, Public returns rendered field)
		var field_html = (this.is_admin ? '' : this.get_field_html_single(field, value, false, section_repeatable_index));

		// Field label (For comments only)
		var field_label = this.esc_html(field.label)

		// Get field type config
		if(typeof($.WS_Form.field_type_cache[field.type]) === 'undefined') { return ''; }
		var field_config = $.WS_Form.field_type_cache[field.type];

		// Check field is licensed
		if((typeof(field_config['pro_required']) !== 'undefined') && field_config['pro_required']) {

			return '';
		}

		// Check to see if mask_single should be ignored
		var mask_wrappers_drop = this.is_admin ? false : ((typeof field_config['mask_wrappers_drop'] !== 'undefined') ? field_config['mask_wrappers_drop'] : false);

		// reCAPTCHA override
		if((field.type == 'recaptcha') && !this.is_admin && (this.get_object_meta_value(field, 'recaptcha_recaptcha_type', 'default') == 'invisible')) { mask_wrappers_drop = true; }

		// hCaptcha override
		if((field.type == 'hcaptcha') && !this.is_admin && (this.get_object_meta_value(field, 'hcaptcha_type', 'default') == 'invisible')) { mask_wrappers_drop = true; }

		// If wrappers should be dropped, disregard them
		if(mask_wrappers_drop) {

			var mask_single = '#field';

		} else {

			var mask_single = this.get_field_value_fallback(field.type, false, 'mask_single', false, framework_fields, sub_type);
		}

		// Select / Checkbox - Min / Max checked
		switch(field.type) {

			case 'checkbox' :
			case 'price_checkbox' :

				var checkbox_min = this.get_object_meta_value(field, 'checkbox_min', false);
				var checkbox_max = this.get_object_meta_value(field, 'checkbox_max', false);
				var select_all = this.get_object_meta_value(field, 'select_all', false);

				if((checkbox_min === false) && (checkbox_max === false)) { break; }

				// Checks
				if(checkbox_min !== false) {

					checkbox_min = parseInt(checkbox_min, 10);
					if(checkbox_min > 0) {

						attributes.push('data-checkbox-min="' + this.esc_attr(checkbox_min) + '"');
					}
				}

				if(
					(checkbox_max !== false) &&
					!select_all
				) {

					checkbox_max = parseInt(checkbox_max, 10);
					if(checkbox_max >= 0) {

						attributes.push('data-checkbox-max="' + this.esc_attr(checkbox_max) + '"');
					}
				}

				break;

			case 'select' :
			case 'price_select' :

				var select_min = this.get_object_meta_value(field, 'select_min', false);
				var select_max = this.get_object_meta_value(field, 'select_max', false);

				if((select_min === false) && (select_max === false)) { break; }

				// Checks
				if(select_min !== false) {

					select_min = parseInt(select_min, 10);
					if(select_min > 0) {

						attributes.push('data-select-min="' + this.esc_attr(select_min) + '"');
					}
				}

				if(select_max !== false) {

					select_max = parseInt(select_max, 10);
					if(select_max >= 0) {

						attributes.push('data-select-max="' + this.esc_attr(select_max) + '"');
					}
				}

				break;
		}

		// Build parse values
		var mask_values = {

			'attributes': ((attributes.length > 0) ? ' ' : '') + attributes.join(' '),
			'class': class_array.join(' '),
			'data_id': field.id,
			'field': field_html,
			'id': this.get_part_id(field.id, section_repeatable_index, 'field-wrapper'),
			'type': field.type
		};

		// Parse wrapper field
		var field_html_parsed = this.comment_html(this.language('comment_field') + ': ' + field_label) + this.mask_parse(mask_single, mask_values) + this.comment_html(this.language('comment_field') + ': ' + field_label, true);

		return field_html_parsed;
	}

	// Build field type cache
	$.WS_Form.prototype.field_type_cache_build = function() {

		// If public, set field_type_cache to field_types, already in corret format
		if(!this.is_admin) { $.WS_Form.field_type_cache = $.WS_Form.field_types; }

		// If already built, do not build
		if($.WS_Form.field_type_cache.length > 0) { return true; }

		// Add field types
		for (var group_key in $.WS_Form.field_types) {

			var group = $.WS_Form.field_types[group_key];
			var types = group.types;

			// Add field types
			for (var type in types) {

				// Store field type to cache
				$.WS_Form.field_type_cache[type] = types[type];
			}
		}
	}

	// HTML encode string
	$.WS_Form.prototype.esc_html = function(value, encode_new_lines) {

		if(typeof(value) !== 'string') { return value; }

		if(typeof(encode_new_lines) === 'undefined') { encode_new_lines = false; }

		// Process by value type
		switch(typeof(value)) {

			// String
			case 'string' :

				value = this.esc_html_do(value, encode_new_lines);
				break;

			// Arrays / objects
			case 'object' :

				for(var value_index in value) {

					if(!value.hasOwnProperty(value_index)) { continue; }

					if(typeof(value[value_index]) === 'string') {

						value[value_index] = this.esc_html_do(value[value_index], encode_new_lines);
					}
				}
		}

		return value;
	}

	$.WS_Form.prototype.esc_html_do = function(value, encode_new_lines) {

		if(typeof(encode_new_lines) === 'undefined') { encode_new_lines = false; }

		var return_html = this.replace_all(value, '&', '&#38;');
		return_html = this.replace_all(return_html, '<', '&lt;');
		return_html = this.replace_all(return_html, '>', '&gt;');
		return_html = this.replace_all(return_html, '"', '&quot;');

		if(encode_new_lines) {

			return_html = this.replace_all(return_html, '\n', '&#13;');	// Preserves new lines at beginning of textarea elements
		}
		return return_html;
	}

	$.WS_Form.prototype.esc_html_undo = function(value) {

		return new DOMParser().parseFromString(value, 'text/html').documentElement.textContent;
	}

	// Escape attribute
	$.WS_Form.prototype.esc_attr = function(value, encode_new_lines) {

		if(typeof(value) !== 'string') { return value; }

		if(typeof(encode_new_lines) === 'undefined') { encode_new_lines = false; }

		var return_html = this.replace_all(value, '&', '&amp;');
		return_html = this.replace_all(return_html, '<', '&lt;');
		return_html = this.replace_all(return_html, '>', '&gt;');
		return_html = this.replace_all(return_html, "'", '&apos;');
		return_html = this.replace_all(return_html, '"', '&quot;');

		if(encode_new_lines) {

			return_html = this.replace_all(return_html, '\n', '&#13;');
		}

		return return_html;
	}

	// Escape selector
	$.WS_Form.prototype.esc_selector = function(value) {

		if(typeof(value) !== 'string') { return value; }

		var return_html = this.replace_all(value, '"', '\\"');
		return_html = this.replace_all(return_html, "'", "\\'");
		return_html = this.replace_all(return_html, '[', '\\[');
		return_html = this.replace_all(return_html, ']', '\\]');
		return_html = this.replace_all(return_html, ',', '\\,');
		return_html = this.replace_all(return_html, '=', '\\=');

		return return_html;
	}

	// Strip HTML
	$.WS_Form.prototype.html_strip = function(value) {

		// Process by value type
		switch(typeof(value)) {

			// String
			case 'string' :

				value = this.html_strip_do(value);
				break;

			// Arrays / objects
			case 'object' :

				for(var value_index in value) {

					if(!value.hasOwnProperty(value_index)) { continue; }

					if(typeof(value[value_index]) === 'string') {

						value[value_index] = this.html_strip_do(value[value_index]);
					}
				}
		}

		return value;
	}

	$.WS_Form.prototype.html_strip_do = function(value) {

		return this.esc_html(value.replace(/<[^>]*>/g, ''));
	}

	// JS string encode so it can be used in single quotes
	$.WS_Form.prototype.js_string_encode = function(input) {

		if(typeof(input) !== 'string') { return input; }

		var return_html = this.replace_all(input, "'", "\\'");

		return return_html;
	}

	// Loader - On
	$.WS_Form.prototype.loader_on = function() {

		$('#wsf-loader').addClass('wsf-loader-on');
	}

	// Loader - Off
	$.WS_Form.prototype.loader_off = function() {

		$('#wsf-loader').removeClass('wsf-loader-on');
	}

	// HTML encode string
	$.WS_Form.prototype.comment_html = function(string, end) {

		if(typeof(end) === 'undefined') { end = false; }

		return ('<!-- ' + (end ? '/' : '') + string + " -->\n") + (end ? "\n" : '');
	}

	// HTML encode string
	$.WS_Form.prototype.comment_css = function(string) {

		return ("\t/* " + string + " */\n");
	}

	// Get object value
	$.WS_Form.prototype.get_object_value = function(object, element, default_return) {

		if(typeof default_return === 'undefined') { default_return = false; }

		// Check object and return value if found
		if(typeof object === 'undefined') { return default_return; }
		if(typeof object[element] === 'undefined') { return default_return; }
		return object[element];
	}

	// Get object value (with fallback)
	$.WS_Form.prototype.get_field_value_fallback = function(field_type, label_position, element, default_return, framework_fields, sub_type) {

		if(typeof(default_return) === 'undefined') { default_return = false; }
		if(!framework_fields) { framework_fields = this.framework_fields; }
		if(typeof(sub_type) === 'undefined') { sub_type = false; }

		if(sub_type === false) {

			var return_value = this.get_field_value_fallback_process(field_type, label_position, element, default_return, framework_fields);
			return return_value;

		} else {

			var sub_type_return = this.get_field_value_fallback_process(field_type, label_position, element + '_' + sub_type, default_return, framework_fields);
			if(sub_type_return === default_return) {

				var sub_type_return = this.get_field_value_fallback_process(field_type, label_position, element, default_return, framework_fields);
			}

			return sub_type_return;
		}
	}

	$.WS_Form.prototype.get_field_value_fallback_process = function(field_type, label_position, element, default_return, framework_fields) {

		// Get field to check
		var object = framework_fields;
		var object_fallback = $.WS_Form.field_type_cache[field_type];

		// object[label_position] checks
		if(label_position !== false) {

			// object[label_position]['field_types'][field_type][element]
			var object_not_found = (typeof(object) === 'undefined') || (typeof(object[label_position]) === 'undefined') || (typeof(object[label_position]['field_types']) === 'undefined') || (typeof(object[label_position]['field_types'][field_type]) === 'undefined') || (typeof(object[label_position]['field_types'][field_type][element]) === 'undefined');
			if(!object_not_found) { return object[label_position]['field_types'][field_type][element]; }

			// object[label_position][element]
			var object_not_found = (typeof(object) === 'undefined') || (typeof(object[label_position]) === 'undefined') || (typeof (object[label_position][element]) === 'undefined');
			if(!object_not_found) { return object[label_position][element]; }
		}

		// object['field_types'][field_type][element]
		var object_not_found = (typeof(object) === 'undefined') || (typeof(object['field_types']) === 'undefined') || (typeof(object['field_types'][field_type]) === 'undefined') || (typeof(object['field_types'][field_type][element]) === 'undefined');
		if(!object_not_found) { return object['field_types'][field_type][element]; }

		// object[element]
		var object_not_found = (typeof(object) === 'undefined') || (typeof(object[element]) === 'undefined');
		if(!object_not_found) { return object[element]; }

		// object_fallback[element]
		if(typeof(object_fallback) === 'undefined') { return default_return; }
		if(typeof(object_fallback[element]) === 'undefined') { return default_return; }
		return object_fallback[element];
	}

	// Get object data
	$.WS_Form.prototype.get_object_data = function(object, object_id, use_scratch) {

		if(typeof(use_scratch) === 'undefined') { use_scratch = false; }

		// Get object data
		switch(object) {

			case 'form' :

				return use_scratch ? this.object_data_scratch : this.form;

			case 'group' :

				return use_scratch ? this.object_data_scratch : this.group_data_cache[object_id];

			case 'section' :

				return use_scratch ? this.object_data_scratch : this.section_data_cache[object_id];

			case 'field' :

				return use_scratch ? this.object_data_scratch : this.field_data_cache[object_id];

			case 'conditional' :

				return this.conditional;

			case 'action' :

				return this.action;
		}

		return false;
	}

	// Get object meta
	$.WS_Form.prototype.get_object_meta = function(object, object_id) {

		switch(object) {

			case 'form' :

				var object_meta = $.WS_Form.settings_form.sidebars.form.meta;
				break;


			case 'group' :

				var object_meta = $.WS_Form.settings_form.sidebars.group.meta;
				break;

			case 'section' :

				var object_meta = $.WS_Form.settings_form.sidebars.section.meta;
				break;

			case 'field' :

				var object_data = this.field_data_cache[object_id];
				var object_meta = $.WS_Form.field_type_cache[object_data.type];
				break;
		}

		return object_meta;
	}

	// Get object meta value
	$.WS_Form.prototype.get_object_meta_value = function(object, key, default_return, create, parse_variables_process) {

		if(typeof(default_return) === 'undefined') { default_return = false; }

		if(typeof(create) === 'undefined') { create = false; }

		if(typeof(parse_variables_process) === 'undefined') { parse_variables_process = false; }

		if(typeof(object) === 'undefined') { return default_return; }

		if(typeof(object.meta) === 'undefined') { return default_return; }

		if(typeof(object.meta[key]) === 'undefined') {

			if(create) {

				this.set_object_meta_value(object, key, default_return);

			} else {

				return default_return;
			}
		}
		return (parse_variables_process && (typeof(object.meta[key]) === 'string')) ? this.parse_variables_process(object.meta[key]).output : object.meta[key];	
	}

	// Get object meta value
	$.WS_Form.prototype.has_object_meta_key = function(object, key) {

		return (

			(typeof(object) !== 'undefined') &&
			(typeof(object.meta) !== 'undefined') &&
			(typeof(object.meta[key]) !== 'undefined') &&
			(object.meta[key] != '')
		);
	}

	// Parse WS Form variables
	$.WS_Form.prototype.parse_variables_process = function(parse_string, section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth, parse_string_original, field_to_original) {

		var ws_this = this;

		// Checks parse_string
		if(typeof(parse_string) !== 'string') { return this.parse_variables_process_error(parse_string); }
		if(parse_string.indexOf('#') == -1) { return this.parse_variables_process_error(parse_string); }

		// Check section_repeatable_index
		if(typeof(section_repeatable_index) === 'undefined') { section_repeatable_index = false; }

		// Check calc type
		if(typeof(calc_type) === 'undefined') { calc_type = false; }
		var calc = (calc_type === 'calc');

		// Check field_to
		if(typeof(field_to) === 'undefined') { field_to = false; }

		// Check field_part
		if(typeof(field_part) === 'undefined') { field_part = false; }

		// Check calc_register
		if(typeof(calc_register) === 'undefined') { calc_register = true; }

		// Check section_id
		if(typeof(section_id) === 'undefined') { section_id = false; }

		// Check for too many iterations
		if(typeof(depth) === 'undefined') { depth = 1; }

		// Check for original parse string (used for calc)
		if(typeof(parse_string_original) === 'undefined') { parse_string_original = parse_string; }

		// Check for original field (used for calc)
		if(typeof(field_to_original) === 'undefined') { field_to_original = field_to; }

		// Initialize variables
		var variables = {};
		var variables_single_parse = {};

		// Parse type
		var lookups_contain_singles = false;

		// Fields touched
		var variable_fields = [];

		// Check for too many iterations
		if(depth > 100) {

			this.error('error_parse_variable_syntax_error_depth', '', 'error-parse-variables');
			return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_depth'));
		}

		// Process each parse variable key
		for(var parse_variables_key in $.WS_Form.parse_variables) {

			if(!$.WS_Form.parse_variables.hasOwnProperty(parse_variables_key)) { continue; }

			// Process each parse variable
			var parse_variables = $.WS_Form.parse_variables[parse_variables_key];

			// Check for prefix (for performance)
			var var_lookup_found = false;

			for(var var_lookups_index in parse_variables.var_lookups) {

				if(!parse_variables.var_lookups.hasOwnProperty(var_lookups_index)) { continue; }

				// Check if parse string contains lookup value
				if(parse_string.indexOf(parse_variables.var_lookups[var_lookups_index]) !== -1) {

					var_lookup_found = true;
					break;
				}
			}

			// If lookup not found, skip this variable group
			if(!var_lookup_found) { continue; }

			for(var parse_variable in parse_variables['variables']) {

				if(!parse_variables['variables'].hasOwnProperty(parse_variable)) { continue; }

				if(parse_string.indexOf('#' + parse_variable) === -1) { continue; }

				var parse_variable_config = parse_variables['variables'][parse_variable];

				// Assign value
				var parse_variable_value = (typeof(parse_variable_config['value']) !== 'undefined') ? parse_variable_config['value'] : false;
				var parse_variable_attributes = (typeof(parse_variable_config['attributes']) === 'object') ? parse_variable_config['attributes'] : false;

				// Single parse? (Used if different value returned each parse, e.g. random_number)
				var parse_variable_single_parse = (typeof(parse_variable_config['single_parse']) !== 'undefined') ? parse_variable_config['single_parse'] : false;

				// If no attributes specified, then just set the value
				if((parse_variable_attributes === false) && (parse_variable_value !== false)) { variables[parse_variable] = parse_variable_value; continue; }

				// Get number of attributes required
				var variable_attribute_count = (typeof(parse_variable_config['attributes']) === 'object') ? parse_variable_config['attributes'].length : 0;

				if(variable_attribute_count > 0) {

					// Do until no more found
					var variable_index_start = 0;
					do {

						// Find position of variable and brackets
						var variable_index_of = parse_string.indexOf('#' + parse_variable, variable_index_start);

						// No more instances of variable found
						if(variable_index_of === -1) { continue; }

						// Find bracket positions
						var variable_index_of_bracket_start = -1;
						var variable_index_of_bracket_finish = -1;
						var parse_string_function = parse_string.substring(variable_index_of + parse_variable.length + 1);

						// Bracket should immediately follow the variable name
						if(parse_string_function.substring(0, 1) === '(') {

							variable_index_of_bracket_start = variable_index_of + parse_variable.length + 1;
							variable_index_of_bracket_finish = this.get_bracket_finish_index(parse_string_function);

							if(variable_index_of_bracket_finish !== -1) {

								variable_index_of_bracket_finish += variable_index_of_bracket_start;
							}
						}

						// Check brackets found
						if(	(variable_index_of_bracket_start === -1) ||
							(variable_index_of_bracket_finish === -1) ) {

							// Shift index to look for next instance
							variable_index_start += parse_variable.length + 1;

							// Get full string to parse
							parse_variable_full = '#' + parse_variable;

							// No brackets found so set attributes as blank
							var variable_attribute_array = [];

						} else {

							// Shift index to look for next instance
							variable_index_start = variable_index_of_bracket_finish;

							// Get attribute string
							var variable_attribute_string = parse_string.substr(variable_index_of_bracket_start + 1, (variable_index_of_bracket_finish - 1) - variable_index_of_bracket_start);

							// Get full string to parse
							var parse_variable_full = parse_string.substr(variable_index_of, (variable_index_of_bracket_finish + 1) - variable_index_of);

							// Get separator
							var separator = (typeof(parse_variable_config['attribute_separator']) !== 'undefined') ? parse_variable_config['attribute_separator'] : ',';

							// Convert string to attributes
							var variable_attribute_array = this.string_to_attributes(variable_attribute_string, separator);
						}

						// Check each attribute
						for(var parse_variable_attributes_index in parse_variable_attributes) {

							if(!parse_variable_attributes.hasOwnProperty(parse_variable_attributes_index)) { continue; }

							var parse_variable_attribute = parse_variable_attributes[parse_variable_attributes_index];

							var parse_variable_attribute_id = parse_variable_attribute['id'];

							// Was attribute provided for this index?
							var parse_variable_attribute_supplied = (typeof(variable_attribute_array[parse_variable_attributes_index]) !== 'undefined');

							// Check required
							var parse_variable_attribute_required = (typeof(parse_variable_attribute['required']) !== 'undefined') ? parse_variable_attribute['required'] : true;
							if(parse_variable_attribute_required && !parse_variable_attribute_supplied) {

								// Syntax error - Attribute count
								this.error('error_parse_variable_syntax_error_attribute', '#' + parse_variable + ' (Expected ' + parse_variable_attribute_id + ')', 'error-parse-variables');
								return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_attribute', '#' + parse_variable + ' (Expected ' + parse_variable_attribute_id + ')'));
							}

							// Check default
							var parse_variable_attribute_default = (typeof(parse_variable_attribute['default']) !== 'undefined') ? parse_variable_attribute['default'] : false;
							if((parse_variable_attribute_default !== false) && !parse_variable_attribute_supplied) {

								variable_attribute_array[parse_variable_attributes_index] = parse_variable_attribute_default;
							}

							// Check trim
							var parse_variable_attribute_trim = (typeof(parse_variable_attribute['trim']) !== 'undefined') ? parse_variable_attribute['trim'] : true;
							if(parse_variable_attribute_trim) {

								var parse_variable_attribute_value = variable_attribute_array[parse_variable_attributes_index];

								if(typeof(parse_variable_attribute_value) === 'string') {

									variable_attribute_array[parse_variable_attributes_index] = parse_variable_attribute_value.trim();
								}
							}

							// Check valid
							var parse_variable_attribute_valid = (typeof(parse_variable_attribute['valid']) !== 'undefined') ? parse_variable_attribute['valid'] : false;
							if(parse_variable_attribute_valid !== false) {

								if(!parse_variable_attribute_valid.includes(variable_attribute_array[parse_variable_attributes_index])) {

									// Syntax error - Invalid attribute value
									this.error('error_parse_variable_syntax_error_attribute_invalid', '#' + parse_variable + ' (Expected ' + parse_variable_attribute_valid.join(', ') + ')', 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_attribute_invalid', '#' + parse_variable + ' (Expected ' + parse_variable_attribute_valid.join(', ') + ')'));
								}
							}
						}

						// Process variable
						var parsed_variable = '';
						var esc_html = true;

						switch(parse_variable) {
							case 'calc' :
							case 'text' :

								esc_html = false;

								var calc_type = parse_variable;

								switch(calc_type) {

									case 'calc' :

										var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, 'calc', field_to, field_part, calc_register, section_id, depth + 1, parse_string_original, field_to_original);
										var parsed_variable_raw = parse_variables_process_return.output;
										if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
										parsed_variable = this.calc_string(parsed_variable_raw, parse_string_original, false, field_to_original, field_part);
										break;

									case 'text' :

										var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, 'text', field_to, field_part, calc_register, section_id, depth + 1, parse_string_original, field_to_original);
										var parsed_variable_raw = parse_variables_process_return.output;
										if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
										parsed_variable = parsed_variable_raw;
										break;
								}


								// Field allows in?
								if(typeof($.WS_Form.field_type_cache[field_to.type]) === 'undefined') { break; }
								var field_type_config_to = $.WS_Form.field_type_cache[field_to.type];
								var allow_in = (typeof(field_type_config_to[calc_type + '_in']) !== 'undefined') ? field_type_config_to[calc_type + '_in'] : false;

								// Add to this.calc
								if(
									(field_to !== false) &&
									(typeof(field_to) !== 'undefined') &&
//									(depth == 1) &&
									!calc &&
									calc_register &&
									(
										(field_part == 'field_label') ||
										(field_part == 'field_help') ||
										allow_in
									)
								) {

									var parse_variable_process_fields = parse_variables_process_return.fields;

									var field_ids_touched = [];

									// Build an array of the fields that would be touched to process this calculation
									var field_id_touched_processed = [];
									for(var parse_variable_process_fields_index in parse_variable_process_fields) {

										if(!parse_variable_process_fields.hasOwnProperty(parse_variable_process_fields_index)) { continue; }

										var field_id_touched = parse_variable_process_fields[parse_variable_process_fields_index];

										// Keep values unique
										if(field_id_touched_processed.indexOf(field_id_touched) !== -1) { continue; }
										field_id_touched_processed.push(field_id_touched);

										// Run for regular and repeatable sections
										field_ids_touched.push(field_id_touched);
									}

									// Build calc
									var calc_to_push = {

										'field' 					: field_to_original,
										'field_part'				: field_part,
										'type'						: calc_type,
										'value' 					: parse_string_original,
										'field_ids_touched'			: field_ids_touched,
										'section_repeatable_index'	: section_repeatable_index
									};

									// Find existing calc
									var calc_existing = false;
									for(var calc_index in this.calc) {

										if(!this.calc.hasOwnProperty(calc_index)) { continue; }

										var calc_single = this.calc[calc_index];

										if(

											(calc_single.field === field_to_original) &&
											(calc_single.field_part === field_part) &&
											(calc_single.value === parse_string_original) &&
											(calc_single.section_repeatable_index === section_repeatable_index)
										) {

											// Add field_ids_touched to existing calc
											this.calc[calc_index].field_ids_touched = this.calc[calc_index].field_ids_touched.concat(field_ids_touched);

											// Ensure field_ids_touched is unique
											this.calc[calc_index].field_ids_touched = this.calc[calc_index].field_ids_touched.filter(function(value, index, self) { return self.indexOf(value) === index; });

											// Set type
											if(depth === 1) {

												this.calc[calc_index].type = calc_type;
											}

											calc_existing = true;
										}
									}

									// Push to calc array if existing calc not found
									if(!calc_existing) {

										// Add to calc array
										this.calc.push(calc_to_push);

										// Re-order calcs so that attributes are fired first
										this.calc.sort(function(calc_a, calc_b) {

											var field_parts_attr = [

												'field_placeholder',
												'field_min',
												'field_max',
												'field_ecommerce_price_min',
												'field_ecommerce_price_max'
											];

											var calc_a_is_attr = field_parts_attr.indexOf(calc_a.field_part);
											var calc_b_is_attr = field_parts_attr.indexOf(calc_b.field_part);

											return (calc_a_is_attr === calc_b_is_attr) ? 0 : ((calc_a_is_attr < calc_b_is_attr) ? 1 : -1);
										});

										// Re-order calcs so that repeaters fire first
										this.calc.sort(function(calc_a, calc_b) {

											return (calc_a.section_repeatable_index === calc_b.section_repeatable_index) ? 0 : ((calc_a.section_repeatable_index < calc_b.section_repeatable_index) ? 1 : -1);
										});

										// Disable populate on this field if the calculation is being applied to the default value
										if(ws_form_settings.debug && (field_part === 'field_value')) {

											this.debug_field_id_bypass.push(field_to.id);
										}
									}
								}

								break;

							case 'section_rows_start' :

								if(isNaN(variable_attribute_array[0])) { break; }

								var section_rows_end_position = this.closing_string_index(parse_string, '#section_rows_end', '#section_rows_start', variable_index_of_bracket_finish);

								// Get section_id
								section_id = parseInt(variable_attribute_array[0], 10);
								if(section_id <= 0) { break; }

								// Get string to loop
								var parse_string_loop = parse_string.substring(variable_index_of_bracket_finish + 1, variable_index_of_bracket_finish + 1 + (section_rows_end_position - (variable_index_of_bracket_finish + 1)));

								// Build section ID string
								var section_id_string = 'section_' + section_id.toString();
								var section_repeatable_array = (

									(this.section_repeatable_indexes !== false) &&
									(typeof(this.section_repeatable_indexes[section_id_string]) !== 'undefined')

								) ? this.section_repeatable_indexes[section_id_string] : false;

								// Loop through section_repeatable_array
								var parsed_string = '';
								if(section_repeatable_array !== false) {

									for(var section_repeatable_array_index in section_repeatable_array) {

										if(!section_repeatable_array.hasOwnProperty(section_repeatable_array_index)) { continue; }

										var section_repeatable_index_row = section_repeatable_array[section_repeatable_array_index];

										var parse_variables_process_return = this.parse_variables_process(parse_string_loop, section_repeatable_index_row, false, field_to, field_part, false, section_id, depth);
										parsed_string += parse_variables_process_return.output;
									}

									// Remove from section_rows_start to section_rows_end
									parse_string = parse_string.substring(0, variable_index_of) + parsed_string + parse_string.substring(section_rows_end_position + 17);

									variable_index_start = 0;

								} else {

									continue;
								}

								break;

							case 'if' :

								var endif_position = this.closing_string_index(parse_string, '#endif', '#if', variable_index_of_bracket_finish);

								if(endif_position === -1) {

									this.error('error_parse_variable_syntax_error_endif', variable_attribute_string, 'parse_variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_endif', variable_attribute_string));
								}

								// Valid operators
								var operators_valid = ['==', '!=', '>', '>=', '<=', '<', '^=', '$=', '!^=', '!$=', '*=', '!*='];

								// Valid logic previous
								var logic_previous_valid = ['&&', '||'];

								// Run through each attribute and build boolean array
								var if_boolean_array = [];
								var if_boolean_array_index = 0;
								var if_part = 0;

								for(var variable_attribute_index in variable_attribute_array) {

									if(!variable_attribute_array.hasOwnProperty(variable_attribute_index)) { continue; }

									var variable_attribute = variable_attribute_array[variable_attribute_index];

									if(if_part === 0) {

										if_boolean_array[if_boolean_array_index] = {

											'value_a' : '',
											'operator' : '!=',
											'value_b' : ''
										};
									}

									// Decode operators and logic
									if((if_part == 1) || (if_part == 3)) {

										// Convert HTML encoding caused by TinyMCE
										variable_attribute = this.replace_all(variable_attribute, '&amp;', '&');
										variable_attribute = this.replace_all(variable_attribute, '&lt;', '<');
										variable_attribute = this.replace_all(variable_attribute, '&gt;', '>');
									}

									// Check for numeric values
									if((if_part == 0) || (if_part == 2)) {

										var parse_variables_process_return = this.parse_variables_process(variable_attribute, section_repeatable_index, false, field_to, field_part, calc_register, section_id, depth + 1, parse_string_original, field_to_original);
										variable_attribute = variable_attribute_string = parse_variables_process_return.output;

										if(
											(typeof(variable_attribute) === 'string') &&
											(variable_attribute !== '') &&
											!isNaN(variable_attribute)
										) {

											variable_attribute = parseFloat(variable_attribute);
										}
									}

									// Short form #if
									if(
										(if_part == 1) &&
										(logic_previous_valid.indexOf(variable_attribute) !== -1)
									) {

										if_part = 3;
									}

									// Process IF parts
									var if_part_key = false;
									switch(if_part) {

										case 0 :
										case 2 :

											if_part_key = (if_part == 0) ? 'value_a' : 'value_b';
											if_boolean_array[if_boolean_array_index][if_part_key] = variable_attribute;
											if_boolean_array[if_boolean_array_index][if_part_key + '_string'] = variable_attribute_string;
											break;

										case 1 :

											if(operators_valid.indexOf(variable_attribute) === -1) {

												this.error('error_parse_variable_syntax_error_operator', variable_attribute_string, 'parse_variables');
												return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_operator', variable_attribute_string));
											}
											if_boolean_array[if_boolean_array_index]['operator'] = variable_attribute;
											break;

										case 3 :

											if(logic_previous_valid.indexOf(variable_attribute) === -1) {

												this.error('error_parse_variable_syntax_error_logic', variable_attribute_string, 'parse_variables');
												return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_logic', variable_attribute_string));
											}
											if_boolean_array[if_boolean_array_index]['logic_next'] = variable_attribute;
											break;
									}

									if_part++;

									if(if_part > 3) {

										if_part = 0;
										if_boolean_array_index++;
									}
								}

								var result = false;

								var logic_next = '&&';

								for(var if_boolean_array_index in if_boolean_array) {

									if(!if_boolean_array.hasOwnProperty(if_boolean_array_index)) { continue; }

									var if_boolean = if_boolean_array[if_boolean_array_index];
									var result_single = false;

									switch(if_boolean['operator']) {

										// Equals
										case '==' :

											result_single = (if_boolean['value_a'] == if_boolean['value_b']);
											break;

										// Not equals
										case '!=' :

											result_single = (if_boolean['value_a'] != if_boolean['value_b']);
											break;

										// Less than
										case '<' :

											result_single = (if_boolean['value_a'] < if_boolean['value_b']);
											break;

										// Greater than
										case '>' :

											result_single = (if_boolean['value_a'] > if_boolean['value_b']);
											break;

										// Less than or equal
										case '<=' :

											result_single = (if_boolean['value_a'] <= if_boolean['value_b']);
											break;

										// Greater than or equal
										case '>=' :

											result_single = (if_boolean['value_a'] >= if_boolean['value_b']);
											break;

										// Contains
										case '*=' :

											result_single = (if_boolean['value_b_string'] !== '') ? (if_boolean['value_a_string'].indexOf(if_boolean['value_b_string']) !== -1) : false;
											break;

										// Does not contain
										case '!*=' :

											result_single = (if_boolean['value_b_string'] !== '') ? (if_boolean['value_a_string'].indexOf(if_boolean['value_b_string']) === -1) : false;
											break;

										// Starts with
										case '^=' :

											result_single = (if_boolean['value_b_string'] !== '') ? (if_boolean['value_a_string'].indexOf(if_boolean['value_b_string']) === 0) : false;
											break;

										// Does not start with
										case '!^=' :

											result_single = (if_boolean['value_b_string'] !== '') ? (if_boolean['value_a_string'].indexOf(if_boolean['value_b_string']) > 0) : false;
											break;

										// Ends with
										case '$=' :

											result_single = (if_boolean['value_b_string'] !== '') ? (if_boolean['value_a_string'].indexOf(if_boolean['value_b_string']) === (if_boolean['value_a_string'].length - if_boolean['value_b_string'].length)) : false;
											break;

										// Does not end with
										case '!$=' :

											result_single = (if_boolean['value_b_string'] !== '') ? (if_boolean['value_a_string'].indexOf(if_boolean['value_b_string']) !== (if_boolean['value_a_string'].length - if_boolean['value_b_string'].length)) : false;
											break;
									}

									result = (if_boolean_array_index == 0) ? result_single : ((logic_next == '&&') ? (result && result_single) : (result || result_single));

									logic_next = (typeof(if_boolean['logic_next']) !== 'undefined') ? if_boolean['logic_next'] : '&&';
								}

								parsed_variable = false;

								if(result) {

									// Remove the endif
									parse_string = parse_string.substring(0, endif_position) + parse_string.substring(endif_position + 6);

									// Remove the if
									parse_string = parse_string.substring(0, variable_index_of) + parse_string.substring(variable_index_of_bracket_finish + 1);

								} else {

									// Remove from if to endif
									parse_string = parse_string.substring(0, variable_index_of) + parse_string.substring(endif_position + 6);
								}

								variable_index_start = 0;

								break;
							case 'tab_label' :

								if(isNaN(variable_attribute_array[0])) {

									this.error('error_parse_variable_syntax_error_group_id', variable_attribute_array[0], 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_group_id', variable_attribute_array[0]));
								}

								var group_id = parseInt(variable_attribute_array[0], 10);

								if(
									(typeof(this.group_data_cache[group_id]) !== 'undefined') &&
									(typeof(this.group_data_cache[group_id]).label !== 'undefined')
								) {

									parsed_variable = this.group_data_cache[group_id].label;
	
								} else {

									this.error('error_parse_variable_syntax_error_group_id', group_id, 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_group_id', group_id));
								}

								break;

							case 'section_label' :

								if(isNaN(variable_attribute_array[0])) {

									this.error('error_parse_variable_syntax_error_section_id', variable_attribute_array[0], 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_section_id', variable_attribute_array[0]));
								}

								var section_id_label = parseInt(variable_attribute_array[0], 10);

								if(
									(typeof(this.section_data_cache[section_id_label]) !== 'undefined') &&
									(typeof(this.section_data_cache[section_id_label]).label !== 'undefined')
								) {

									parsed_variable = this.section_data_cache[section_id_label].label;
	
								} else {

									this.error('error_parse_variable_syntax_error_section_id', section_id_label, 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_section_id', section_id_label));
								}

								break;

							case 'cookie_get' :

								// Get cookie value
								parsed_variable = this.cookie_get_raw(variable_attribute_array[0]);

								break;

							case 'session_storage_get' :

								// Get session storage value
								parsed_variable = this.session_storage_get_raw(variable_attribute_array[0]);

								break;

							case 'local_storage_get' :

								// Get local storage value
								parsed_variable = this.local_storage_get_raw(variable_attribute_array[0]);

								break;

							case 'field_label' :

								if(isNaN(variable_attribute_array[0])) {

									this.error('error_parse_variable_syntax_error_field_id', variable_attribute_array[0], 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_id', variable_attribute_array[0]));
								}

								var field_id = parseInt(variable_attribute_array[0], 10);

								if(
									(typeof(this.field_data_cache[field_id]) !== 'undefined') &&
									(typeof(this.field_data_cache[field_id]).label !== 'undefined')
								) {

									parsed_variable = this.field_data_cache[field_id].label;
	
								} else {

									this.error('error_parse_variable_syntax_error_field_id', field_id, 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_id', field_id));
								}

								break;

							case 'field_min_id' :
							case 'field_max_id' :
							case 'field_min_value' :
							case 'field_max_value' :
							case 'field_min_label' :
							case 'field_max_label' :

								var field_ids = [];
								var field_min_id = 0;
								var field_min_value = 0;
								var field_min_label = '';
								var field_max_id = 0;
								var field_max_value = 0;
								var field_max_label = '';
								var variable_attribute_array_index = 0;

								var section_repeatable_section_id_to = (typeof(field_to.section_repeatable_section_id) !== 'undefined') ? parseInt(field_to.section_repeatable_section_id, 10) : false;

								// Loop through provided field ID's
								while(!isNaN(variable_attribute_array[variable_attribute_array_index])) {

									// Get field ID
									var field_id = parseInt(variable_attribute_array[variable_attribute_array_index], 10);
									if(!field_id) { variable_attribute_array_index++; continue; }

									// Add to fields touched
									variable_fields.push(field_id);

									// Get field
									if(typeof(this.field_data_cache[field_id]) !== 'object') { continue; }
									var field = this.field_data_cache[field_id];

									// Get field value
									var field_value = this.get_field_value(field, section_repeatable_index_to);
									if(
										(typeof(field_value) == 'object') &&
										(typeof(field_value[0]) !== 'undefined')
									) {
										field_value = this.get_number(field_value[0], 0, true);

									} else {

										field_value = 0; 
									}

									/// Get field label
									var field_label = field.label;

									// Set min and max
									if(variable_attribute_array_index == 0) {

										// Initial values
										field_max_id = field_min_id = field_id;
										field_max_value = field_min_value = field_value;
										field_max_label = field_min_label = field_label;

									} else {

										// Min / max calculations
										if(field_value < field_min_value) {

											field_min_id = field_id;
											field_min_value = field_value;
											field_min_label = field_label;
										}

										if(field_value > field_max_value) {

											field_max_id = field_id;
											field_max_value = field_value;
											field_max_label = field_label;
										}
									}

									// Go to next variable attribute
									variable_attribute_array_index++;
								}

								switch(parse_variable) {

									case 'field_min_id' :

										parsed_variable = field_min_id;
										break;

									case 'field_max_id' :

										parsed_variable = field_max_id;
										break;

									case 'field_min_value' :

										parsed_variable = field_min_value;
										break;

									case 'field_max_value' :

										parsed_variable = field_max_value;
										break;

									case 'field_min_label' :

										parsed_variable = field_min_label;
										break;

									case 'field_max_label' :

										parsed_variable = field_max_label;
										break;
								}

								break;

							case 'date_format' :

								// Parse date
								var date_input = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_from, field_part, calc_register, section_id, depth).output;

								// Parse date format
								var date_format = this.parse_variables_process(variable_attribute_array[1], section_repeatable_index, calc_type, field_from, field_part, calc_register, section_id, depth).output;

								// Get date
								var parsed_variable_date = new Date(date_input);

								// Check date
								if(isNaN(parsed_variable_date.getTime())) {

									this.error('error_parse_variable_syntax_error_date_format', date_input, 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_date_format', date_input));
								}

								// Process date
								parsed_variable = this.date_format(parsed_variable_date, date_format);

								break;

							case 'field' :
							case 'field_float' :
							case 'field_date_format' :
							case 'field_date_offset' :
							case 'field_count_word' :
							case 'field_count_char' :
							case 'ecommerce_field_price' :

								if(isNaN(variable_attribute_array[0])) {

									this.error('error_parse_variable_syntax_error_field_id', variable_attribute_array[0], 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_id', variable_attribute_array[0]));
								}

								var field_id = parseInt(variable_attribute_array[0], 10);

								// Check for infinite loops
								if(
									(
										(field_part === 'field_value') ||
										(field_part === false)
									) &&
									(field_id === parseInt(field_to_original.id, 10))
								) {

									// Syntax error - Infinite loop
									this.error('error_parse_variable_syntax_error_self_ref', parse_variable_full, 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_self_ref', parse_variable_full));
								}

								// Check field exists
								if(typeof(this.field_data_cache[field_id]) === 'undefined') {

									this.error('error_parse_variable_syntax_error_field_id', field_id, 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_id', field_id));
								}

								// Add to fields touched array if we are still dealing with the original field
								if(field_to.id === field_to_original.id) {

									variable_fields.push(field_id);
								}

								// Get field config
								var field_from = this.field_data_cache[field_id];
								if(typeof($.WS_Form.field_type_cache[field_from.type]) === 'undefined') { break; }
								var field_type_config_from = $.WS_Form.field_type_cache[field_from.type];

								// Check #calc and #text
								if(calc_type !== false) {

									// Check field configuration calc_out / text_out
									var allow_out = typeof(field_type_config_from[calc_type + '_out']) ? field_type_config_from[calc_type + '_out'] : false;
									if(!allow_out) {

										this.error('error_parse_variable_syntax_error_' + calc_type + '_out', field_from.label + ' (ID: ' + field_from.id + ')', 'error-parse-variables');
										return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_' + calc_type + '_out', field_from.label + ' (ID: ' + field_from.id + ')'));
									}
								}

								// Check for static value
								var field_static = typeof(field_type_config_from.static) ? field_type_config_from.static : false;

								if(field_static) {

									if(field_static === true) {

										// If static set to true, we use the mask_field
										var value = (typeof(field_type_config_from.mask_field_static) !== 'undefined') ? field_type_config_from.mask_field_static : '';

									} else {

										// Get value
										var value = this.get_object_meta_value(field_from, field_static, '');

										// wpautop?
										if(this.wpautop_parse_variable(field_from, field_type_config_from)) {

											value = this.wpautop(value);
											esc_html = false;
										}
									}

									var parse_variables_process_return = this.parse_variables_process(value, section_repeatable_index, calc_type, field_from, field_part, calc_register, section_id, depth + 1, parse_string_original, field_to_original);
									if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
									parsed_variable = [parse_variables_process_return.output];

									break;
								}

								// Check if submitted as array
								var submit_array_from = (typeof(field_type_config_from['submit_array']) !== 'undefined') ? field_type_config_from['submit_array'] : false;

								// Get criteria needed to work out how to get field value
								var section_repeatable_section_id_from = (typeof(field_from.section_repeatable_section_id) !== 'undefined') ? parseInt(field_from.section_repeatable_section_id, 10) : false;
								var section_repeatable_section_id_to = (typeof(field_to.section_repeatable_section_id) !== 'undefined') ? parseInt(field_to.section_repeatable_section_id, 10) : false;
								var section_repeatable_index_to = section_repeatable_index;

								var parsed_variable = false;

								var delimiter = (typeof(variable_attribute_array[1]) !== 'undefined') ? variable_attribute_array[1] : ',';

								var column = (typeof(variable_attribute_array[2]) !== 'undefined') ? variable_attribute_array[2] : false;

								// REPEATABLE TO REPEATABLE
								// In this scenario, we want the single field in the from section
								if(
									(
										(section_repeatable_index_to !== false) &&
										(section_repeatable_section_id_from === section_id)
									)
									||
									(
										(section_repeatable_section_id_from !== false) &&
										(section_repeatable_section_id_to !== false)
									)
								) {

									// Get source repeatable index
									var parsed_variable = this.get_field_value(field_from, section_repeatable_index_to, submit_array_from, column);
								}

								// REPEATABLE TO NON-REPEATABLE
								// In this scenario, we want the sum of all fields in the from sections
								if(
									(parsed_variable === false) &&
									(section_repeatable_section_id_from !== false) &&
									(section_repeatable_section_id_to === false)
								) {

									// Get source repeatable index
									var parsed_variable = this.get_field_value(field_from, true, submit_array_from, column);
								}

								// NON-REPEATABLE TO ANYTHING
								// In this scenario, simply get the field
								if(
									(parsed_variable === false) &&
									(section_repeatable_section_id_from === false)
								) {

									// Get source repeatable index
									var parsed_variable = this.get_field_value(field_from, false, submit_array_from, column);
								}

								// Value still not found, fallback to default
								if(parsed_variable === false) {

									switch(field_from.type) {

										case 'price_select' :
										case 'price_checkbox' :
										case 'price_radio' :
										case 'select' :
										case 'checkbox' :
										case 'radio' :

											// Get default from data source
											var data_source = this.get_data_source(field_from);

											if(data_source.default_value !== false) {

												parsed_variable = data_source.default_value;

											} else {

												parsed_variable = [];
											}

											break;

										default :

											// Get default value from field
											var default_value = this.get_object_meta_value(field_from, 'default_value', '');
											var parse_variables_process_return = this.parse_variables_process(default_value, section_repeatable_index, calc_type, field_from, field_part, calc_register, section_id, depth + 1, parse_string_original, field_to_original);
											if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
											parsed_variable = [parse_variables_process_return.output];
									}
								}

								// Process by field type
								switch(parse_variable) {

									case 'field' :

										if(column !== false) {

											parsed_variable = this.get_data_grid_column(parsed_variable, field_from, column);
										}
										break;

									case 'field_count_char' :
									case 'field_count_word' :

										var parsed_variable_string = ((typeof(parsed_variable) === 'object') ? parsed_variable.join(delimiter) : '');

										if(typeof(variable_attribute_array[1]) !== 'undefined') {

											var regex_parts = /\/(.*)\/(.*)/.exec(variable_attribute_array[1]);
											parsed_variable_string = parsed_variable_string.replace(new RegExp(regex_parts[1], regex_parts[2]), '');
										}

										var parsed_variable = [(parse_variable == 'field_count_char') ? parsed_variable_string.length : this.get_word_count(parsed_variable_string)];

										break;
								}

								// If this parse is for a calculation, handle the values differently
								if(calc) {

									if(parsed_variable.length) {

										switch(field_from.type) {

											case 'price_select' :
											case 'price_checkbox' :
											case 'price_radio' :
											case 'price' :
											case 'price_subtotal' :
											case 'cart_price' :
											case 'cart_total' :

												var parsed_variable_total = 0;

												// Get value, processing currency
												for(var parsed_variable_index in parsed_variable) {

													if(!parsed_variable.hasOwnProperty(parsed_variable_index)) { continue; }
													parsed_variable_total += this.get_number(parsed_variable[parsed_variable_index], 0, true);
												}

												// Round to e-commerce decimals setting (This removes floating point errors, e.g. 123.4500000000002)
												var price_decimals = parseInt($.WS_Form.settings_plugin.price_decimals, 10);
												parsed_variable = this.get_number(parsed_variable_total, 0, false, price_decimals);

												break;

											case 'datetime' :

												// Get input date
												if(parsed_variable[0] === '') { parsed_variable = ''; break; }

												var input_type_datetime = this.get_object_meta_value(field_from, 'input_type_datetime', 'date');
												var format_date = this.get_object_meta_value(field_from, 'format_date', ws_form_settings.date_format);
												if(!format_date) { format_date = ws_form_settings.date_format; }

												// Convert input to JS date
												var input_datetime = this.get_date(parsed_variable[0], input_type_datetime, format_date);

												// Get date represented in seconds
												parsed_variable = this.get_number(input_datetime.getTime()) / 1000;

												break;

											default :

												var parsed_variable_total = 0;

												// Get value, ignoring currency
												for(var parsed_variable_index in parsed_variable) {

													if(!parsed_variable.hasOwnProperty(parsed_variable_index)) { continue; }

													parsed_variable_total += ws_this.get_number(parsed_variable[parsed_variable_index], 0, false);
												}

												parsed_variable = parsed_variable_total;
										}

									} else {

										parsed_variable = 0;
									}

								} else {

									// HTML encode each parsed_variable array element
									parsed_variable = $.map(parsed_variable, function(value) { return ws_this.esc_html(value); });

									// Join with delimiter
									parsed_variable = parsed_variable.join(delimiter);

									// Already HTML encoded the field values, so disable further HTML encoding
									esc_html = false;
								}

								switch(parse_variable) {

									case 'ecommerce_field_price' :

										var parsed_variable = this.get_price(this.get_number(parsed_variable));
										break;

									case 'field_date_format' :

										if(field_from.type !== 'datetime') {

											this.error('error_parse_variable_syntax_error_field_date_offset', field_id, 'error-parse-variables');
											return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_date_offset', field_id));
										}

										// Parse date
										var date_input = this.parse_variables_process(parsed_variable, section_repeatable_index, calc_type, field_from, field_part, calc_register, section_id, depth).output;

										// Get input date format
										var format_date_input = this.get_object_meta_value(field_from, 'format_date', ws_form_settings.date_format)
										if(!format_date_input) { format_date_input = ws_form_settings.date_format; }

										// Process as timestamp?
										if(this.is_integer(date_input)) {

											parsed_variable_date = new Date(parseInt(date_input, 10) * 1000);

										} else {

											var input_type_datetime = this.get_object_meta_value(field_from, 'input_type_datetime', 'date');

											parsed_variable_date = this.get_date(date_input, input_type_datetime, format_date_input);
										}

										// Ensure parsed_variable_date is a date
										if(
											(parsed_variable_date !== false) &&
											!isNaN(parsed_variable_date.getTime())
										) {

											// Check for format
											if(
												(typeof(variable_attribute_array[1]) !== 'undefined') &&
												(variable_attribute_array[1] != '')
											) {

												var format_date = variable_attribute_array[1];

											} else {

												var format_date = format_date_input;
											}
											if(!format_date) { format_date = ws_form_settings.date_format; }

											// Process date
											parsed_variable = ((typeof(this.date_format) === 'function') ? this.date_format(parsed_variable_date, format_date) : '');
										}

										break;

									case 'field_date_offset' :

										if(field_from.type !== 'datetime') {

											this.error('error_parse_variable_syntax_error_field_date_offset', field_id, 'error-parse-variables');
											return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_date_offset', field_id));
										}

										// Parse date
										var date_input = this.parse_variables_process(parsed_variable, section_repeatable_index, calc_type, field_from, field_part, calc_register, section_id, depth).output;

										// Get input date format
										var format_date_input = this.get_object_meta_value(field_from, 'format_date', ws_form_settings.date_format)
										if(!format_date_input) { format_date_input = ws_form_settings.date_format; }

										// Process as timestamp?
										if(this.is_integer(date_input)) {

											parsed_variable_date = new Date(parseInt(date_input, 10) * 1000);

										} else {

											var input_type_datetime = this.get_object_meta_value(field_from, 'input_type_datetime', 'date');

											parsed_variable_date = this.get_date(date_input, input_type_datetime, format_date_input);
										}

										// Ensure parsed_variable_date is a date
										if(
											(parsed_variable_date !== false) &&
											!isNaN(parsed_variable_date.getTime())
										) {

											// Check for offset
											var seconds_offset = parseInt(this.parse_variables_process(variable_attribute_array[1], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth).output, 10);

											if(seconds_offset) {

												parsed_variable_date.setSeconds(parsed_variable_date.getSeconds() + seconds_offset);
											}

											// Check for format
											if(
												(typeof(variable_attribute_array[2]) !== 'undefined') &&
												(variable_attribute_array[2] != '')
											) {

												var format_date = variable_attribute_array[2];

											} else {

												var format_date = format_date_input;
											}
											if(!format_date) { format_date = ws_form_settings.date_format; }

											// Process date
											parsed_variable = ((typeof(this.date_format) === 'function') ? this.date_format(parsed_variable_date, format_date) : '');
										}

										break;

									case 'field_float' :

										var parsed_variable = this.get_number(parsed_variable);
										break;
								}

								// wpautop?
								if(parse_variable === 'field') {

									if(this.wpautop_parse_variable(field_from, field_type_config_from)) {

										parsed_variable = this.wpautop(parsed_variable);
										esc_html = false;
									}
								}

								break;

							case 'ecommerce_price' :

								var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
								if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
								var number_input = this.calc_string(parse_variables_process_return.output, parse_string_original, false, field_to_original, field_part);

								var parsed_variable = this.get_price(number_input);
 
								break;

							case 'number_format' :

								// Get num
								var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
								if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
								var num = this.calc_string(parse_variables_process_return.output, parse_string_original, false, field_to_original, field_part);

								// Get decimals
								var decimals = variable_attribute_array[1];

								// Get decimal separator
								var decimal_separator = variable_attribute_array[2];

								// Get thousands separator
								var thousands_separator = variable_attribute_array[3];

								var parsed_variable = this.number_format(num, decimals, decimal_separator, thousands_separator);
 
								break;

							case 'select_option_text' :

								if(isNaN(variable_attribute_array[0])) {

									this.error('error_parse_variable_syntax_error_field_id', variable_attribute_array[0], 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_id', variable_attribute_array[0]));
								}

								// Read attributes
								var field_id = parseInt(variable_attribute_array[0], 10);
								var delimiter = variable_attribute_array[1];
								if(typeof(delimiter) === 'undefined') { delimiter = ', '; }

								// Add to fields touched array if we are still dealing with the original field
								if(field_to.id === field_to_original.id) {

									variable_fields.push(field_id);
								}

								// Get field name
								var field_name = ws_form_settings.field_prefix + field_id.toString() + ((section_repeatable_index) ? '[' + section_repeatable_index + ']' : '');

								// Get field selected options
								var field_obj = $('[name="' + this.esc_selector(field_name) + '[]"] option:selected', this.form_canvas_obj);

								// Build parsed variable
								if(field_obj.length) {

									var field_obj_text_array = $.map(field_obj, function(n, i) { return $(n).text(); });
									parsed_variable = field_obj_text_array.join(delimiter);
								}

								break;

							case 'query_var' :

								parsed_variable = this.get_query_var(variable_attribute_array[0], variable_attribute_array[1]);
								break;

							case 'section_row_count' :

								if(isNaN(variable_attribute_array[0])) {

									this.error('error_parse_variable_syntax_error_section_id', variable_attribute_array[0], 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_section_id', variable_attribute_array[0]));
								}

								var section_id_row_count = variable_attribute_array[0];

								// Check section exists
								if(typeof(this.section_data_cache[section_id_row_count]) === 'undefined') {

									this.error('error_parse_variable_syntax_error_section_id', section_id_row_count, 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_section_id', section_id_row_count));
								}

								// Get section count
								var sections = $('[data-repeatable][data-id="' + this.esc_selector(section_id_row_count) + '"]', this.form_canvas_obj);
								parsed_variable = sections.length ? sections.length : 0;

								// No sections found, so set to default in case this is called before the form has rendered
								if(
									(parsed_variable === 0) &&
									(typeof(this.get_section_repeat_default) === 'function')
								) {

									var section = this.section_data_cache[section_id_row_count];

									// Get section repeat default
									var parsed_variable = this.get_section_repeat_default(section);
								}

								break;
							case 'checkbox_label' :
							case 'radio_label' :

								if(isNaN(variable_attribute_array[0])) {

									this.error('error_parse_variable_syntax_error_field_id', variable_attribute_array[0], 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_id', variable_attribute_array[0]));
								}

								// Read attributes
								var field_id = parseInt(variable_attribute_array[0], 10);
								var delimiter = variable_attribute_array[1];
								if(typeof(delimiter) === 'undefined') { delimiter = ', '; }

								// Add to fields touched array if we are still dealing with the original field
								if(field_to.id === field_to_original.id) {

									variable_fields.push(field_id);
								}

								// Get field name
								var field_name = ws_form_settings.field_prefix + field_id.toString() + ((section_repeatable_index) ? '[' + section_repeatable_index + ']' : '');

								// Get field selected options
								var field_obj = $('[name="' + this.esc_selector(field_name) + '[]"]:checked', this.form_canvas_obj);

								// Build parsed variable
								if(field_obj.length) {

									var field_obj_text_array = $.map(field_obj, function(n, i) {

										return $('label[for="' + $(n).attr('id') + '"]').text();
									});
									parsed_variable = field_obj_text_array.join(delimiter);
								}

								break;

							case 'checkbox_count' :
							case 'select_count' :

								if(isNaN(variable_attribute_array[0])) {

									this.error('error_parse_variable_syntax_error_field_id', variable_attribute_array[0], 'error-parse-variables');
									return this.parse_variables_process_error(this.language('error_parse_variable_syntax_error_field_id', variable_attribute_array[0]));
								}

								// Read attributes
								var field_id = parseInt(variable_attribute_array[0], 10);

								// Add to fields touched array if we are still dealing with the original field
								if(field_to.id === field_to_original.id) {

									variable_fields.push(field_id);
								}

								// Get field name
								var field_name = ws_form_settings.field_prefix + field_id.toString() + ((section_repeatable_index) ? '[' + section_repeatable_index + ']' : '');

								switch(parse_variable) {

									case 'checkbox_count' :

										// Get field checked checkboxes
										var field_obj = $('[name="' + this.esc_selector(field_name) + '[]"]:checked', this.form_canvas_obj);

										break;

									case 'select_count' :

										// Get field selected options
										var field_obj = $('select[name="' + this.esc_selector(field_name) + '[]"] option:not([data-placeholder]):selected', this.form_canvas_obj);

										break;
								}

								// Build parsed variable
								parsed_variable = field_obj ? field_obj.length : 0;

								break;

							case 'post_date_custom' :
							case 'server_date_custom' :
							case 'blog_date_custom' :

								var parsed_variable_date = this.get_new_date(parse_variable_value);

								if(typeof(variable_attribute_array[1]) !== 'undefined') {

									var seconds_offset = parseInt(this.parse_variables_process(variable_attribute_array[1], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth).output, 10);

									if(seconds_offset) {

										parsed_variable_date.setSeconds(parsed_variable_date.getSeconds() + seconds_offset);
									}
								}

								parsed_variable = ((typeof(this.date_format) === 'function') ? this.date_format(parsed_variable_date, variable_attribute_array[0]) : '');

								break;

							case 'client_date_custom' :

								var parsed_variable_date = new Date();

								if(typeof(variable_attribute_array[1]) !== 'undefined') {

									var seconds_offset = parseInt(this.parse_variables_process(variable_attribute_array[1], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth).output, 10);

									if(seconds_offset) {

										parsed_variable_date.setSeconds(parsed_variable_date.getSeconds() + seconds_offset);
									}
								}

								parsed_variable = ((typeof(this.date_format) === 'function') ? this.date_format(parsed_variable_date, variable_attribute_array[0]) : '');

								break;

							case 'random_number' :

								var random_number_min = parseInt(this.get_number(variable_attribute_array[0]), 10);
								var random_number_max = parseInt(this.get_number(variable_attribute_array[1]), 10);
								parsed_variable = Math.floor(Math.random() * (random_number_max - random_number_min + 1)) + random_number_min;
								break;

							case 'random_string' :

								var random_string_length = parseInt(this.get_number(variable_attribute_array[0]), 10);
								var random_string_characters = variable_attribute_array[1];
								var random_string_character_length = random_string_characters.length;
								parsed_variable = '';
								for (var random_string_index = 0; random_string_index < random_string_length; random_string_index++) { parsed_variable += random_string_characters[Math.floor(Math.random() * random_string_character_length)]; } 
								break;

							case 'abs' :
							case 'acos' :
							case 'asin' :
							case 'atan' :
							case 'ceil' :
							case 'cos' :
							case 'exp' :
							case 'floor' :
							case 'log' :
							case 'negative' :
							case 'positive' :
							case 'sin' :
							case 'sqrt' :
							case 'tan' :

								var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
								if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
								var number_input = this.calc_string(parse_variables_process_return.output, parse_string_original, false, field_to_original, field_part);

								switch(parse_variable) {

									case 'abs' :

										parsed_variable = Math.abs(number_input);
										break;

									case 'acos' :

										parsed_variable = Math.acos(number_input);
										break;

									case 'asin' :

										parsed_variable = Math.asin(number_input);
										break;

									case 'atan' :

										parsed_variable = Math.atan(number_input);
										break;

									case 'ceil' :

										parsed_variable = Math.ceil(number_input);
										break;

									case 'cos' :

										parsed_variable = Math.cos(number_input);
										break;

									case 'exp' :

										parsed_variable = Math.exp(number_input);
										break;

									case 'floor' :

										parsed_variable = Math.floor(number_input);
										break;

									case 'log' :

										parsed_variable = Math.log(number_input);
										break;

									case 'negative' :
									case 'positive' :

										parsed_variable = (parse_variable == 'positive') ? Math.max(0, number_input) : Math.min(0, number_input);
										break;

									case 'sin' :

										parsed_variable = Math.sin(number_input);
										break;

									case 'sqrt' :

										parsed_variable = Math.sqrt(number_input);
										break;

									case 'tan' :

										parsed_variable = Math.tan(number_input);
										break;
								}

								break;

							case 'lower' :
							case 'upper' :
							case 'ucwords' :
							case 'ucfirst' :
							case 'capitalize' :
							case 'sentence' :
							case 'wpautop' :
							case 'trim' :
							case 'slug' :

								if(typeof(variable_attribute_array[0]) === 'string') {

									var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
									if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
									parsed_variable = parse_variables_process_return.output;

									switch(parse_variable) {

										case 'lower' :

											parsed_variable = parsed_variable.toLowerCase();
											break;

										case 'upper' :

											parsed_variable = parsed_variable.toUpperCase();
											break;

										case 'ucwords' :

											parsed_variable = this.ucwords(parsed_variable);
											break;

										case 'ucfirst' :

											parsed_variable = this.ucfirst(parsed_variable);
											break;

										case 'capitalize' :

											parsed_variable = this.capitalize(parsed_variable);
											break;

										case 'sentence' :

											parsed_variable = this.sentence(parsed_variable);
											break;

										case 'wpautop' :

											parsed_variable = this.wpautop(parsed_variable);
											esc_html = false;
											break;

										case 'trim' :

											parsed_variable = parsed_variable.trim();
											esc_html = false;
											break;

										case 'slug' :

											parsed_variable = this.get_slug(parsed_variable);
											break;
									}
								}

								break;

							case 'pow' :

								// Base
								var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
								if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
								var base = this.calc_string(parse_variables_process_return.output, parse_string_original, decimals, field_to_original, field_part);

								// Exponent
								var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[1], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
								if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
								var exponent = this.calc_string(parse_variables_process_return.output, parse_string_original, decimals, field_to_original, field_part);

								parsed_variable = Math.pow(base, exponent);

								break;

							case 'round' :

								var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[1], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
								var decimals = this.get_number(parse_variables_process_return.output);
								var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
								if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
								parsed_variable = this.calc_string(parse_variables_process_return.output, parse_string_original, decimals, field_to_original, field_part);

								break;

							case 'min' :
							case 'max' :

								var values = [];

								// If we can only find one parameter, try splitting that up by comma
								if(variable_attribute_array.length === 1) {

									var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
									if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
									variable_attribute_array = this.string_to_attributes(parse_variables_process_return.output);
								}

								for(var variable_attribute_array_index in variable_attribute_array) {

									if(!variable_attribute_array.hasOwnProperty(variable_attribute_array_index)) { continue; }

									var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[variable_attribute_array_index], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
									if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
									var number_input = this.calc_string(parse_variables_process_return.output, parse_string_original, false, field_to_original, field_part);
									values.push(number_input);
								}

								parsed_variable = (values.length > 0) ? ((parse_variable == 'min') ? Math.min(...values) : Math.max(...values)) : 0;

								break;

							case 'avg' :

								var parsed_variable_total = 0;

								// If we can only find one parameter, try splitting that up by comma
								if(variable_attribute_array.length === 1) {

									var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[0], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
									if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
									variable_attribute_array = this.string_to_attributes(parse_variables_process_return.output);
								}

								// Run through each attribute and add it to total
								for(var variable_attribute_array_index in variable_attribute_array) {

									if(!variable_attribute_array.hasOwnProperty(variable_attribute_array_index)) { continue; }

									var parse_variables_process_return = this.parse_variables_process(variable_attribute_array[variable_attribute_array_index], section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth);
									if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
									var number_input = this.calc_string(parse_variables_process_return.output, parse_string_original, false, field_to_original, field_part);
									parsed_variable_total += number_input;
								}

								// Work out average
								parsed_variable = parsed_variable_total / variable_attribute_array.length;

								break;
						}

						// Assign value
						if(parsed_variable !== false) {

							// HTML encode?
							if(esc_html) {

								parsed_variable = this.esc_html(parsed_variable);
							}

							if(parse_variable_single_parse) {
	
								variables_single_parse[parse_variable_full.substring(1)] = parsed_variable;

							} else {

								variables[parse_variable_full.substring(1)] = parsed_variable;
							}
						}

					} while (variable_index_of !== -1);

					// Parse function
					parse_string = this.mask_parse(parse_string, variables);
				}
			}
		}

		// Form
		if(parse_string.indexOf('form') != -1) {

			variables['form_id'] = this.form_id;
			variables['form_instance_id'] = this.form_instance_id;
			variables['form_obj_id'] = this.form_obj_id;
			variables['form_label'] = this.form.label;
			variables['form_checksum'] = this.form.published_checksum;
			variables['form_framework'] = this.framework.name;
		}

		// Section
		if(parse_string.indexOf('section') != -1) {

			variables['section_row_index'] = section_repeatable_index;

			// Get section ID
			var section_row_number = 1;

			// Get section row number
			if(
				(section_id === false) &&
				(field_to !== false) &&
				(typeof(field_to.section_id) !== 'undefined') &&
				(parseInt(field_to.section_id, 10) > 0)
			) {

				section_id = parseInt(field_to.section_id, 10);
			}

			if(typeof(this.section_repeatable_indexes['section_' + section_id]) === 'object') {

				section_row_number = this.section_repeatable_indexes['section_' + section_id].indexOf(section_repeatable_index.toString()) + 1;
				if(section_row_number <= 0) { section_row_number = 1; }
			}

			variables['section_row_number'] = section_row_number;
		}

		// Submit
		if(parse_string.indexOf('submit') != -1) {

			variables['submit_hash'] = this.hash;
		}

		// Client
		if(parse_string.indexOf('client') != -1) {

			var client_date_time = new Date();

			variables['client_time'] = ((typeof(this.date_format) === 'function') ? this.date_format(client_date_time, ws_form_settings.time_format) : '');
			variables['client_date'] = ((typeof(this.date_format) === 'function') ? this.date_format(client_date_time, ws_form_settings.date_format) : '');
		}

		// Seconds
		if(parse_string.indexOf('seconds_epoch_midnight') != -1) {

			var now = new Date();
			now.setHours(0,0,0,0);

			variables['seconds_epoch_midnight'] = Math.round(now.getTime() / 1000);
		}
		if(parse_string.indexOf('seconds_epoch') != -1) {

			var now = new Date();

			variables['seconds_epoch'] = Math.round(now.getTime() / 1000);
		}

		// E-Commerce
		var currency = this.get_currency();
		if(
			(parse_string.indexOf('ecommerce') != -1)
		) {

			if(typeof(this.ecommerce_cart_price_type['total']) === 'undefined') {

				this.ecommerce_cart_price_type['total'] = {

					float: 0,
					string: this.get_price(0, currency, false),
					currency: this.get_price(0, currency)
				}
			}

			variables['ecommerce_cart_total_float'] = this.ecommerce_cart_price_type['total'].float;
			variables['ecommerce_cart_total_currency'] = this.ecommerce_cart_price_type['total'].currency;
			variables['ecommerce_cart_total'] = this.ecommerce_cart_price_type['total'].string;

			for(var cart_price_type in $.WS_Form.ecommerce.cart_price_types) {

				if(!$.WS_Form.ecommerce.cart_price_types.hasOwnProperty(cart_price_type)) { continue; }

				if(typeof(this.ecommerce_cart_price_type[cart_price_type]) === 'undefined') {

					this.ecommerce_cart_price_type[cart_price_type] = {

						float: 0,
						string: this.get_price(0, currency, false),
						currency: this.get_price(0, currency)
					};
				}

				variables['ecommerce_cart_' + cart_price_type + '_float'] = this.ecommerce_cart_price_type[cart_price_type].float;
				variables['ecommerce_cart_' + cart_price_type + '_currency'] = this.ecommerce_cart_price_type[cart_price_type].currency;
				variables['ecommerce_cart_' + cart_price_type] = this.ecommerce_cart_price_type[cart_price_type].string;
			}
		}

		// Parse until no more changes made
		var parse_string_before = parse_string;
		parse_string = this.mask_parse(parse_string, variables);
		parse_string = this.mask_parse(parse_string, variables_single_parse, true);
		if(
			(parse_string !== parse_string_before) &&
			(parse_string.indexOf('#') !== -1)
		) {
			var parse_variables_process_return = this.parse_variables_process(parse_string, section_repeatable_index, calc_type, field_to, field_part, calc_register, section_id, depth + 1, parse_string_original, field_to_original);
			parse_string = parse_variables_process_return.output;

			if(typeof(parse_variables_process_return.fields) === 'object') { variable_fields = variable_fields.concat(parse_variables_process_return.fields); }
		}

		var return_object = {

			'output' : parse_string,
			'fields' : variable_fields
		};

		return return_object;
	}

	// Get data grid column values
	$.WS_Form.prototype.get_data_grid_column = function(parsed_variable, field, column) {

		if(parsed_variable.length === 0) { return parsed_variable; }

		var parsed_variable_return = [];

		var meta_key_prefix = field.type;

		switch(meta_key_prefix) {

			case 'select' :
			case 'checkbox' :
			case 'radio' :

				break;

			case 'price_select' :

				var meta_key_prefix = 'select_price';
				break;

			case 'price_checkbox' :

				var meta_key_prefix = 'checkbox_price';
				break;

			case 'price_radio' :

				var meta_key_prefix = 'radio_price';
				break;

			default :

				return parsed_variable;
		}

		var meta_key = 'data_grid_' + meta_key_prefix;

		// Get column mappings
		var column_mapping_value_id = this.get_object_meta_value(field, meta_key_prefix + '_field_value', '0');
		var column_mapping_label_id = this.get_object_meta_value(field, meta_key_prefix + '_field_label', '0');
		var column_mapping_price_id = this.get_object_meta_value(field, meta_key_prefix + '_field_price', '0');
		var column_mapping_parse_variable_id = this.get_object_meta_value(field, meta_key_prefix + '_field_parse_variable', '0');
		var column_mapping_wc_id = this.get_object_meta_value(field, meta_key_prefix + '_field_wc', '0');

		// Get data grid
		var data_grid = this.get_object_meta_value(field, meta_key, false);
		if(data_grid === false) { return parsed_variable; }

		// Check for columns and groups
		if(
			(typeof(data_grid.columns) !== 'object') ||
			(typeof(data_grid.groups) !== 'object')
		) {
			return parsed_variable;
		}

		var value_column_index = 0;
		var label_column_index = 0;
		var price_column_index = 0;
		var parse_variable_column_index = 0;
		var wc_column_index = 0;

		var columns = data_grid.columns;

		// Get column index value and check return_column_index in case it is a column label
		var return_column_index = parseInt(column, 10);

		for(var column_index in columns) {

			if(!columns.hasOwnProperty(column_index)) { continue; }

			var column_single = columns[column_index];

			if(
				(typeof(column_single.id) === 'undefined') ||
				(typeof(column_single.label) === 'undefined')
			) {
				continue;
			}

			// Get column mapping for value
			if(column_single.id == column_mapping_value_id) {

				value_column_index = column_index;
			}

			// Get column mapping for label
			if(column_single.id == column_mapping_label_id) {

				label_column_index = column_index;
			}

			// Get column mapping for price
			if(column_single.id == column_mapping_price_id) {

				price_column_index = column_index;
			}

			// Get column mapping for parse_variable
			if(column_single.id == column_mapping_parse_variable_id) {

				parse_variable_column_index = column_index;
			}

			// Get column mapping for wc
			if(column_single.id == column_mapping_wc_id) {

				wc_column_index = column_index;
			}

			// Check for column by label
			if(column_single.label === column) {

				return_column_index = column_index;
			}
		}

		// Process groups
		var groups = data_grid.groups;
		for(var group_index in groups) {

			if(!groups.hasOwnProperty(group_index)) { continue; }

			var group = groups[group_index];

			// Get rows
			if(typeof(group.rows) === 'undefined') { continue; }
			var rows = group.rows;

			// Process rows
			for(var row_index in rows) {

				if(!rows.hasOwnProperty(row_index)) { continue; }

				var row = rows[row_index];

				// Get row data
				if(
					(row === null) ||
					(typeof(row.data) !== 'object')
				) {
					continue;
				}
				var data = row.data;

				// Clone row data
				var data_cloned = $.extend(true, [], data);

				// Check value and return indexes exist
				if(typeof(data_cloned[value_column_index]) === 'undefined') { continue; }
				if(typeof(data_cloned[return_column_index]) === 'undefined') { continue; }

				// Pre-parsing
				var mask_values_row = {

					'data_grid_row_price': '',
					'data_grid_row_price_currency': '',
					'data_grid_row_woocommerce_cart': '',
					'data_grid_row_value': data_cloned[value_column_index],
					'data_grid_row_action_variable': '',
					'data_grid_row_label': ''
				};

				// Label
				if(
					(label_column_index !== false) &&
					(typeof(data_cloned[label_column_index]) !== 'undefined')
				) {

					mask_values_row['data_grid_row_label'] = data_cloned[label_column_index];
				}

				// Price
				if(
					(price_column_index !== false) &&
					(typeof(data_cloned[price_column_index]) !== 'undefined')
				) {

					mask_values_row['data_grid_row_price_currency'] = this.get_price(data_cloned[price_column_index]);
					mask_values_row['data_grid_row_price'] = this.get_number(data_cloned[price_column_index]);
				}
				// Parse Variable
				if(
					(parse_variable_column_index !== false) &&
					(typeof(data_cloned[parse_variable_column_index]) !== 'undefined')
				) {

					mask_values_row['data_grid_row_action_variable'] = data_cloned[parse_variable_column_index];
				}

				// WooCommerce
				if(
					(wc_column_index !== false) &&
					(typeof(data_cloned[wc_column_index]) !== 'undefined')
				) {

					mask_values_row['data_grid_row_woocommerce_cart'] = data_cloned[wc_column_index];
				}
				// Parse columns
				for(var data_index in data_cloned) {

					if(!data_cloned.hasOwnProperty(data_index)) { continue; }

					if(typeof(data_cloned[data_index]) === 'number') { data_cloned[data_index] = data_cloned[data_index].toString(); }

					data_cloned[data_index] = this.mask_parse(data_cloned[data_index], mask_values_row);
				}

				// Check if value matches
				for(var parsed_variable_index in parsed_variable) {

					if(!parsed_variable.hasOwnProperty(parsed_variable_index)) { continue; }

					var parsed_variable_single = parsed_variable[parsed_variable_index];

					if(parsed_variable_single === data_cloned[value_column_index].toString()) {

						// Add data to default value
						if(typeof(row.data[return_column_index]) === 'undefined') { continue; }
						parsed_variable_return.push(row.data[return_column_index]);
					}
				}
			}
		}

		return parsed_variable_return;
	}

	// WPAutoP
	$.WS_Form.prototype.wpautop_parse_variable = function(field, field_type_config) {

		// Check for wpautop do not process
		if(this.get_object_meta_value(field, 'wpautop_do_not_process', '') == 'on') { return false; }

		// Meta wpautop
		var wpautop_parse_variable = (typeof(field_type_config['wpautop_parse_variable']) !== 'undefined') ? field_type_config['wpautop_parse_variable'] : false;	

		if(typeof(wpautop_parse_variable) === 'object') {

			var condition_output = false;

			for(var wpautop_parse_variable_index in wpautop_parse_variable) {

				if(!wpautop_parse_variable.hasOwnProperty(wpautop_parse_variable_index)) { continue; }

				var condition = wpautop_parse_variable[wpautop_parse_variable_index];

				if(this.get_object_meta_value(field, condition['meta_key'], '') === condition['meta_value']) {

					condition_output = true;
				}
			}

			wpautop_parse_variable = condition_output;
		}

		return wpautop_parse_variable;
	}

	$.WS_Form.prototype.wpautop = function(i, br) {

		if(typeof(br) === 'undefined') { br = true; }

		var pre_tags = new Map;
		if(i.trim() === '') { return ''; }

		i = i + "\n";
		if(i.indexOf( '<pre' ) > -1) {

			var i_parts = i.split( '</pre>' );
			var last_i = i_parts.pop()
			i = '';
			i_parts.forEach(function(i_part, index) {

				var start = i_part.indexOf( '<pre' );

				if(start === -1) {

					i += i_part;
					return;
				}

				var name = "<pre wp-pre-tag-" + index + "></pre>";
				pre_tags[name] = i_part.substr( start ) + '</pre>';
				i += i_part.substr( 0, start ) + name;
			});

			i += last_i;
		}

		i = i.replace(/<br \/>\s*<br \/>/, "\n\n");

		var allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
		i = i.replace( new RegExp('(<' + allblocks + '[^>]*>)', 'gmi'), "\n$1");
		i = i.replace( new RegExp('(</' + allblocks + '>)', 'gmi'), "$1\n\n");
		i = i.replace( /\r\n|\r/, "\n" )

		if(i.indexOf( '<option' ) > -1) {

			i = i.replace( /\s*<option'/gmi, '<option');
			i = i.replace( /<\/option>\s*/gmi, '</option>');
		}

		if(i.indexOf('</object>') > -1) {

			i = i.replace( /(<object[^>]*>)\s*/gmi, '$1');
			i = i.replace( /\s*<\/object>/gmi, '</object>' );
			i = i.replace( /\s*(<\/?(?:param|embed)[^>]*>)\s*/gmi, '$1');
		}

		if(i.indexOf('<source') > -1 || i.indexOf('<track') > -1) {
			// no P/BR around source and track
			i = i.replace( /([<\[](?:audio|video)[^>\]]*[>\]])\s*/gmi, '$1');
			i = i.replace( /\s*([<\[]\/(?:audio|video)[>\]])/gmi, '$1');
			i = i.replace( /\s*(<(?:source|track)[^>]*>)\s*/gmi, '$1');
		}

		i = i.replace(/\n\n+/gmi, "\n\n");

		var is = i.split(/\n\s*\n/);
		i = '';
		is.forEach(function(tinkle) {

			i += '<p>' + tinkle.replace( /^\s+|\s+$/g, '' ) + "</p>\n";
		});

		i = i.replace(/<p>\s*<\/p>/gmi, '');
		i = i.replace(/<p>([^<]+)<\/(div|address|form)>/gmi, "<p>$1</p></$2>");
		i = i.replace(new RegExp('<p>\s*(</?' + allblocks + '[^>]*>)\s*</p>', 'gmi'), "$1", i); // don't i all over a tag
		i = i.replace(/<p>(<li.+?)<\/p>/gmi, "$1");
		i = i.replace(/<p><blockquote([^>]*)>/gmi, "<blockquote$1><p>");
		i = i.replace(/<\/blockquote><\/p>/gmi, '</p></blockquote>');
		i = i.replace(new RegExp('<p>\s*(</?' + allblocks + '[^>]*>)', 'gmi'), "$1");
		i = i.replace(new RegExp('(</?' + allblocks + '[^>]*>)\s*</p>', 'gmi'), "$1");

		if(br) {

			i = i.replace(/<(script|style)(?:.|\n)*?<\/\\1>/gmi, function ( matches ) {

				return matches[0].replace( "\n", "<WPPreserveNewline />" );
			});
			i = i.replace(/(<br \/>)?\s*\n/gmi, "<br />\n");
			i = i.replace( '<WPPreserveNewline />', "\n" );
		}

		i = i.replace(new RegExp('(</?' + allblocks + '[^>]*>)\s*<br />', 'gmi'), "$1");
		i = i.replace(/<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)/gmi, '$1');
		i = i.replace(/\n<\/p>$/gmi, '</p>');

		if(Object.keys(pre_tags).length) {

			i = i.replace( new RegExp( Object.keys( pre_tags ).join( '|' ), "gi" ), function (matched) {
				return pre_tags[matched];
			});
		}

		return i;
	}

	// Calculate string
	$.WS_Form.prototype.calc_string = function(calc_input, parse_string_original, decimals, field, field_part) {

		try {

			// Attempt to calculate
			var calc_output = new Function('return ' + calc_input)();

			// Check for infinity
			if(calc_output === Infinity) {

				// Convert infinity to 0
				calc_output = 0;
			}

			// Clean up calc output (Ensure only numbers returned)
			return this.get_number(calc_output, 0, false, decimals);

		} catch(e) {

			// Handle calc error
			if(
				(typeof(field) === 'object') &&
				(typeof(field.id) !== 'undefined') &&
				(typeof(field.label) !== 'undefined')
			) {

				this.error('error_parse_variable_syntax_error_calc', field.id + ' | ' + this.esc_html(field.label) + (field_part ? ' | ' + this.esc_html(field_part) : '') + ' | ' + e.message + ': ' + this.esc_html(parse_string_original));
			}

			return 0;
		}
	}

	// Find closing string
	$.WS_Form.prototype.closing_string_index = function(parse_string, closing_string, opening_string, index) {

		var depth = 1;

		while(depth > 0) {

			// Look for embedded if
			var opening_string_index = parse_string.indexOf(opening_string, index);
			var closing_string_index = parse_string.indexOf(closing_string, index);

			// Embedded opening string
			if(
				(opening_string_index !== -1) &&
				(closing_string_index !== -1) &&
				(opening_string_index < closing_string_index) 
			) {
				index = opening_string_index + opening_string.length;
				depth++;
				continue;
			}

			// Embedded closing string
			if(
				(closing_string_index !== -1) &&
				(depth > 1)
			) {
				index = closing_string_index + closing_string.length;
				depth--;
				continue;
			}

			// Associated closing string
			if(
				(closing_string_index !== -1) &&
				(depth === 1)
			) {
				break;
			}

			break;
		}

		return closing_string_index;
	}

	// Parse string to attributes
	$.WS_Form.prototype.string_to_attributes = function(input_string, separator) {

		if(
			(typeof(input_string) !== 'string') ||
			(input_string == '')
		) {
			return [];
		}

		if(typeof(separator) !== 'string') { separator = ','; }

		var bracket_index = 1;
		var input_string_index = 0;
		var skip_double_quotes = false;
		var attribute_single = '';
		var attribute_array = [];

		// Replace non standard double quotes
		input_string.replace('“', '"');
		input_string.replace('”', '"');

		// Get input string length
		var input_string_length = input_string.length;

		while(input_string_index < input_string_length) {

			// Get character
			var character = input_string[input_string_index];

			// If end of string, break
			if(character === undefined) { break; }

			if(
				(character === separator) &&
				(bracket_index === 1) &&
				!skip_double_quotes
			) {

				// Create attribute
				attribute_array.push(attribute_single);
				attribute_single = '';

				// Jump to next character
				input_string_index++;

				continue;
			}

			// If double quotes that are not in another function
			if(
				(character === '"') &&
				(bracket_index === 1)
			) {

				// Clear attribute_single if start
				if(!skip_double_quotes) { attribute_single = ''; }

				// Toggle skip double quotes
				skip_double_quotes = !skip_double_quotes;

				// Jump to next character
				input_string_index++;

				continue;
			}

			// If not in double quotes, process brackets
			if(!skip_double_quotes) {

				switch(character) {

					case '(' : bracket_index++; break;

					case ')' : bracket_index--; break;
				}

				if(bracket_index === 0) { break; }
			}

			// Add character to attribute_single
			attribute_single += character;

			input_string_index++;
		}

		attribute_array.push(attribute_single);

		// Strip double quotes at the beginning and end of each attribute
		if(attribute_array.length) {

			attribute_array = attribute_array.map(function(e) { 

				e = e.replace(/^"(.+(?="$))"$/, '$1'); 
				return e;
			});
		}

		return attribute_array;
	}

	$.WS_Form.prototype.get_bracket_finish_index = function(input_string) {

		if(input_string === '') { return -1; }

		// Replace non standard double quotes
		input_string.replace('“', '"');
		input_string.replace('”', '"');

		// Look for closing bracket
		var bracket_index = 1;
		var input_string_index = 1;	// Start at index 1 to avoid the already found opening bracket
		var skip_double_quotes = false;

		while(
			(bracket_index > 0) ||
			(input_string_index < input_string.length)
		) {

			// Get character
			var character = input_string[input_string_index];

			// If end of string, break
			if(character === undefined) { break; }

			// If double quotes 
			if(character === '"') {

				// Toggle skip double quotes
				skip_double_quotes = !skip_double_quotes;

				// Jump to next character
				input_string_index++;

				continue;
			}

			// If not in double quotes, process brackets
			if(!skip_double_quotes) {

				switch(character) {

					case '(' : bracket_index++; break;

					case ')' : bracket_index--; break;
				}

				if(bracket_index === 0) { break; }
			}

			input_string_index++;
		}

		if(bracket_index === 0) {

			return input_string_index;

		} else {

			// Syntax error - Closing bracket not found
			this.error('error_parse_variable_syntax_error_bracket_closing', input_string, 'error-parse-variables');
			return -1;
		}
	}

	// Parse variable error
	$.WS_Form.prototype.parse_variables_process_error = function(error_message) {

		return {'output' : error_message, 'functions': [], 'fields': []};
	}

	// Get value from field
	$.WS_Form.prototype.get_field_value = function(field, section_repeatable_index, submit_array, column) {

		var ws_this = this;

		if(typeof(section_repeatable_index) === 'undefined') { section_repeatable_index = false; }
		if(typeof(submit_array) === 'undefined') { submit_array = false; }
		if(typeof(column) === 'undefined') { column = false; }

		if(section_repeatable_index === false) {

			// Not in a repeatable section
			var field_selector = '[name="' + this.esc_selector(ws_form_settings.field_prefix + field.id);

			// If this is a select, checkbox or radio
			if(submit_array) { field_selector += '[]'; }

		} else if(section_repeatable_index === true) {

			// If we are getting from all repeatable sections
			var field_selector = '[name^="' + this.esc_selector(ws_form_settings.field_prefix + field.id) + '[';

		} else {

			// If we are getting from a specific repeatable section
			var field_selector = '[name="' + this.esc_selector(ws_form_settings.field_prefix + field.id + '[' + section_repeatable_index + ']');

			// If this is a select, checkbox or radio
			if(submit_array) { field_selector += '[]'; }
		}

		field_selector += '"]';

		// Check field(s) exist
		if(!$(field_selector).length) { return false; }

		// Return values
		switch(field.type) {

			case 'select' :
			case 'price_select' :

				field_selector += ' option:selected';
				break;

			case 'checkbox' :
			case 'radio' :
			case 'price_checkbox' :
			case 'price_radio' :

				field_selector += ':checked';
				break;
		}

		var return_array = [];

		$(field_selector, this.form_canvas_obj).each(function() {

			switch(field.type) {

				case 'price_select' :

					if(column === false) {

						return_array.push($(this).attr('data-price'));

					} else {

						return_array.push($(this).val());
					}
					break;

				case 'price_checkbox' :
				case 'price_radio' :

					if(column === false) {

						return_array.push($(this).attr('data-price'));

					} else {

						return_array.push($(this).val());
					}
					break;

				case 'file' :

					var files = [];

					switch($(this).attr('data-file-type')) {

						case 'dropzonejs' :

							var obj_wrapper = $(this).closest('[data-type="file"]');

							if(obj_wrapper) {

								var dropzone = $('.dropzone', obj_wrapper)[0].dropzone;

								if(dropzone.files) {

									var files = dropzone.files;
								}
							}

							break;

						default :

							var files = $(this)[0].files;
					}

					var filenames = [];

					for(var file_index in files) {

						if(!files.hasOwnProperty(file_index)) { continue; }

						var file = files[file_index];

						filenames.push(file.name);
					}

					return_array.push(filenames.join(','));

					break;

				case 'googlemap' :

					var value_json = $(this).val();
					if(!value_json) { break; }

					try {

						var value = JSON.parse(value_json);

					} catch(e) { break; }

					if(
						(typeof(value.lat) !== 'undefined') &&
						(typeof(value.lng) !== 'undefined')
					) {

						return_array.push(value.lat + ',' + value.lng);
					}

					break;

				default :

					var value = $(this).val();

					if(ws_this.is_iterable(value)) {

						return_array.push(...value);

					} else {

						return_array.push(value);
					}
			}
		});

		return return_array;
	}

	// Get data source
	$.WS_Form.prototype.get_data_source = function(field) {

		var data_source_return = { default_value: [] };

		// Get field config
		var field = this.field_data_cache[field.id];
		if(typeof($.WS_Form.field_type_cache[field.type]) === 'undefined') { return false; }
		var field_type_config = $.WS_Form.field_type_cache[field.type];

		// Get data source
		if(typeof(field_type_config.data_source) === 'undefined') { return false; }
		var data_source = field_type_config.data_source;

		// Get data source type
		if(typeof(data_source.type) === 'undefined') { return false; }
		data_source_return.type = data_source.type;

		// Get data source by type
		switch(data_source_return.type) {

			case 'data_grid' :

				// Get data source meta key
				if(typeof(data_source.id) === 'undefined') { return false; }
				data_source_return.meta_key_data_grid = data_source.id;

				// Get data source value key
				data_source_return.meta_key_value_column = (typeof(field_type_config.datagrid_column_value === 'undefined') ? field_type_config.datagrid_column_value : false);

				// Get data grid
				data_source_return.data_grid = this.get_object_meta_value(field, data_source_return.meta_key_data_grid, false);

				// Get value column
				data_source_return.value_column_id = this.get_object_meta_value(field, data_source_return.meta_key_value_column, false);

				// Run through the data grid columns until we find the ID
				var data_columns = (typeof(data_source_return.data_grid.columns)) ? data_source_return.data_grid.columns : [];

				data_source_return.value_column_index = false;
				for(var data_columns_index in data_columns) {

					if(!data_columns.hasOwnProperty(data_columns_index)) { continue; }

					var data_column = data_columns[data_columns_index];

					if(typeof(data_column.id) === 'undefined') { continue; }

					// Match found, store in cache
					if(data_column.id == data_source_return.value_column_id) { data_source_return.value_column_index = data_columns_index; break; }
				}

				// Build default value
				if(
					(data_source_return.value_column_id !== false) &&
					(typeof(data_source_return.data_grid.groups) !== 'undefined')
				) {

					// Process groups
					var groups = data_source_return.data_grid.groups;
					for(var group_index in groups) {

						if(!groups.hasOwnProperty(group_index)) { continue; }

						var group = groups[group_index];

						// Get rows
						if(typeof(group.rows) === 'undefined') { continue; }
						var rows = group.rows;

						// Process rows
						for(var row_index in rows) {

							if(!rows.hasOwnProperty(row_index)) { continue; }

							var row = rows[row_index];

							if(row === null) { continue; }

							// Process default row
							var row_default = (typeof(row.default) !== 'undefined') ? (row.default === 'on') : false;
							if(row_default) {

								if(typeof(row.data) === 'undefined') { continue; }
								if(typeof(row.data[data_source_return.value_column_index]) === 'undefined') { continue; }

								// Add data to default value
								data_source_return.default_value.push(row.data[data_source_return.value_column_index]);
							}
						}
					}
				}

				break;
		}

		return data_source_return;
	}

	// Get query variable
	$.WS_Form.prototype.get_query_var = function(query_var, default_value) {

		if(!default_value) { default_value = ''; }

		var url = window.location.href;
		if(!url) { return default_value; }

		try {

			query_var = query_var.replace(/[\[\]]/g, "\\$&");
			var regex = new RegExp("[?&]" + query_var + "(=([^&#]*)|&|#|$)");
			var results = regex.exec(url);

			if (!results) return default_value;
			if (!results[2]) return default_value;

			try {

				return decodeURIComponent(results[2].replace(/\+/g, " "));

			} catch(e) {

				return default_value;
			}

		} catch(e) {

			return default_value;
		}
	}

	// Set object meta
	$.WS_Form.prototype.set_object_meta_value = function (object, key, value) {

		if(typeof object === 'undefined') { return value; }

		if(typeof object.meta === 'undefined') { return value; }
	
		// Set value
		object.meta[key] = value;
	}

	// Get column class array
	$.WS_Form.prototype.column_class_array = function(object, type) {

		if(typeof(type) === 'undefined') { type = 'breakpoint'; }

		var column_class_array = [];

		// Get current framework breakpoints
		var framework_breakpoints = this.framework.breakpoints;

		// Get class masks
		var column_class = this.framework.columns.column_class;
		var offset_class = this.framework.columns.offset_class;

		var column_size_value_old = 0;
		var offset_value_old = 0;

		for(var breakpoint in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint)) { continue; }

			var column_framework = framework_breakpoints[breakpoint];

			var column_size_value = this.get_object_meta_value(object, type + '_size_' + breakpoint, '');
			if(column_size_value == '') { column_size_value = '0'; }
			column_size_value = parseInt(column_size_value, 10);

			// If a framework breakpoint size is not found, but column_size_default is set, then use configured or specified value as size (Used for Bootstrap 4 that does not fallback to full column width)
			if(column_size_value == 0) {

				if(typeof column_framework.column_size_default !== 'undefined') {

					switch(column_framework.column_size_default) {

						case 'column_count' :

							column_size_value = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);
							break;

						default :

							column_size_value = parseInt(column_framework.column_size_default, 10);
					}
				}

			} else {

				column_size_value = parseInt(column_size_value, 10);
			}

			// Process breakpoint (only if it differs from the previous breakpoint size, otherwise it just inheris the size from the previous breakpoint)
			if((column_size_value > 0) && (column_size_value != column_size_value_old)) {

				// Get ID for parsing
				var id = column_framework.id;

				// Build mask values for parser
				var mask_values = {

					'id': id,
					'size_word': (typeof this.number_to_word[column_size_value] === 'undefined') ? column_size_value : this.number_to_word[column_size_value],
					'size': column_size_value
				};

				// Check for breakpoint specific column class mask
				if(typeof column_framework.column_class !== 'undefined') {

					var column_class_single = column_framework.column_class;

				} else {

					var column_class_single = column_class;
				}

				// Get single class
				var class_single = this.mask_parse(column_class_single, mask_values);

				// Push to class array
				column_class_array.push(class_single);

				// Remember framework size
				column_size_value_old = column_size_value;
			}

			// Offset
			var offset_value = this.get_object_meta_value(object, type + '_offset_' + breakpoint, '');

			// Process breakpoint (only if it differs from the previous breakpoint offset, otherwise it just inheris the offset from the previous breakpoint)
			var offset_found = false;
			if((offset_value !== '') && (offset_value != offset_value_old)) {

				// Get ID for parsing
				var id = column_framework.id;

				// Build mask values for parser
				var mask_values = {

					'id': id,
					'offset_word': (typeof this.number_to_word[offset_value] === 'undefined') ? offset_value : this.number_to_word[offset_value],
					'offset': offset_value
				};

				// Check for breakpoint specific column class mask
				if(typeof column_framework.offset_class !== 'undefined') {

					var offset_class_single = column_framework.offset_class;

				} else {

					var offset_class_single = offset_class;
				}

				// Get single class
				var class_single = this.mask_parse(offset_class_single, mask_values);

				// Push to class array
				column_class_array.push(class_single);

				// Remember framework size
				offset_value_old = offset_value;

				offset_found = true;
			}

			if(offset_found) { column_class_array.push('wsf-has-offset'); }
		}

		return column_class_array;
	}

	// Render field classes (add/remove)
	$.WS_Form.prototype.column_classes_render = function(obj, column, add) {

		// Set wrapper height to auto
		obj.closest('.wsf-sections').css('min-height', 'auto');
		obj.closest('.wsf-fields').css('min-height', 'auto');

		if(typeof add === 'undefined') { add = true; }

		// Get column class array before change
		var class_array = this.column_class_array(column);

		// Add/Remove old classes
		for(var i=0; i < class_array.length; i++) {
			
			if(add) {

				obj.addClass(class_array[i]);

			} else {

				obj.removeClass(class_array[i]);
			}
		}
	}

	// Mask parse
	$.WS_Form.prototype.mask_parse = function(mask, lookups, single_parse) {

		if(typeof(mask) !== 'string') { return ''; }
		if(typeof(single_parse) === 'undefined') { single_parse = false; }

		// Sort variables descending by key
		var lookups_sorted = [];
		var keys = Object.keys(lookups);
		keys.sort(function(variable_a, variable_b) {

			if(variable_a === variable_b) { return 0; }

			var variable_a_is_function = (variable_a.indexOf('(') !== -1);
			var variable_b_is_function = (variable_b.indexOf('(') !== -1);

			if(variable_a_is_function && variable_b_is_function) {

				return variable_a < variable_b ? 1 : -1;
			}

			if(
				(!variable_a_is_function && variable_b_is_function) ||
				(variable_a_is_function && !variable_b_is_function)
			) {

				return variable_a_is_function < variable_b_is_function ? 1 : -1;
			}

			return variable_a < variable_b ? 1 : -1;
		});
		for(var i = 0; i < keys.length; i++) { lookups_sorted[keys[i]] = lookups[keys[i]]; }
		lookups = lookups_sorted;

		// Process mask_lookups array
		for(var key in lookups) {

			if(!lookups.hasOwnProperty(key)) { continue; }

			var value = lookups[key];

			if(single_parse) {

				mask = mask.replace('#' + key, value);

			} else {

				mask = this.replace_all(mask, '#' + key, value);
			}
		}

		return mask;
	}

	// Get localized language string
	$.WS_Form.prototype.language = function(id, value, esc_html, bypass_error) {

		if(typeof(value) === 'undefined') { value = false; }
		if(typeof(esc_html) === 'undefined') { esc_html = true; }
		if(typeof(bypass_error) === 'undefined') { bypass_error = false; }

		var language_string = '';
		var return_string = '';

		if(id === 'error_language') {

			language_string = 'Language reference not found: %s';

		} else {

			if($.WS_Form.settings_form !== null) {

				if(typeof($.WS_Form.settings_form.language) !== 'undefined') {

					if(typeof($.WS_Form.settings_form.language[id]) !== 'undefined') {

						var language_string = $.WS_Form.settings_form.language[id];
					}
				}
			}
		}

		if(language_string == '') {

			if(
				(id !== 'error_language') &&
				!bypass_error
			) {

				this.error('error_language', id);
			}

		} else {

			if(value !== false) { language_string = this.replace_all(language_string, '%s', value); }

			return_string = esc_html ? this.esc_html(language_string) : language_string;
		}

		if(return_string == '') {

			return_string = (value == '') ? '[LANGUAGE NOT FOUND: ' + id + ']' : value;
		}

		return return_string;
	}

	// Set cookie
	$.WS_Form.prototype.cookie_set = function(cookie_name, cookie_value, cookie_expiry, bind_to_form_id) {

		if(typeof(cookie_expiry) === 'undefined') { cookie_expiry = true; }
		if(typeof(bind_to_form_id) === 'undefined') { bind_to_form_id = true; }

		// Read cookie prefix
		var cookie_prefix = this.get_object_value($.WS_Form.settings_plugin, 'cookie_prefix');
		if(!cookie_prefix) { return false; }

		// Check for blank value
		if(cookie_value == '') {

			// Check if cookie already exists
			var cookie_existing_value = this.cookie_get(cookie_name, '');

			if(cookie_existing_value == '') { return false; }

			// Build negative expiry to clear cookie
			var d = new Date();
			d.setTime(d.getTime() - (86400 * 1000));
			var expires = 'expires=' + d.toUTCString() + ';';

		} else {

			// Read cookie timeout
			if(cookie_expiry) {

				// Get cookie timeout value
				var cookie_timeout = this.get_object_value($.WS_Form.settings_plugin, 'cookie_timeout');
				if(!cookie_timeout) { return false; }

				// Build expiry
				var d = new Date();
				d.setTime(d.getTime() + (cookie_timeout * 1000));
				var expires = ' expires=' + d.toUTCString() + ';';

			} else {

				var expires = '';
			}
		}

		// Set document.cookie
		var cookie_string = cookie_prefix + '_' + (bind_to_form_id ? (this.form_id + '_') : '') + cookie_name + "=" + cookie_value + ";" + expires + " path=/; SameSite=Strict; Secure";
		document.cookie = cookie_string;

		return true;
	}

	// Get cookie
	$.WS_Form.prototype.cookie_get = function(cookie_name, default_value, bind_to_form_id) {

		if(typeof(bind_to_form_id) === 'undefined') { bind_to_form_id = true; }

		// Read cookie configurtion
		var cookie_prefix = this.get_object_value($.WS_Form.settings_plugin, 'cookie_prefix');
		if(!cookie_prefix) { return default_value; }

		// Build name
		cookie_name = cookie_prefix + '_' + (bind_to_form_id ? (this.form_id + '_') : '') + cookie_name;

		// Return value
		return this.cookie_get_raw(cookie_name, default_value);
	}

	// Get cookie raw
	$.WS_Form.prototype.cookie_get_raw = function(cookie_name, default_value) {

		// Check default value
		if(typeof(default_value) === 'undefined') { default_value = ''; }

		// Check cookie name and docuent.cookie
		if(
			(cookie_name === '') ||
			!document.cookie ||
			(typeof(document.cookie) !== 'string')
		) {
			return default_value;
		}

		// Split cookie by semi-colon
		var cookie_elements = document.cookie.split(';');

		// Add = to cookie_name for searching
		cookie_name += '=';

		// Run through each cookie element
		for(var cookie_element_index = 0; cookie_element_index < cookie_elements.length; cookie_element_index++) {

			// Get cookie element
			var cookie_element = cookie_elements[cookie_element_index];

			// Skip spaces
			while(cookie_element.charAt(0) == ' ') {

				cookie_element = cookie_element.substring(1);
			}

			if(cookie_element.indexOf(cookie_name) == 0) {

				// Get cookie value
				var cookie_value = cookie_element.substring(cookie_name.length, cookie_element.length);

				// Attempt to URL decode
				try {

					return decodeURIComponent(cookie_value);

				} catch(e) {

					return cookie_value;
				}
			}
		}

		return default_value;
	}

	// Clear cookie
	$.WS_Form.prototype.cookie_clear = function(cookie_name, bind_to_form_id) {

		if(typeof(bind_to_form_id) === 'undefined') { bind_to_form_id = true; }

		// Read cookie prefix
		var cookie_prefix = this.get_object_value($.WS_Form.settings_plugin, 'cookie_prefix');
		if(!cookie_prefix) { return false; }

		// Build expiry
		var d = new Date();
		d.setTime(d.getTime() - (3600 * 1000));

		// Clear cookie (because of negative expiry date)
		var cookie_string = cookie_prefix + '_' + (bind_to_form_id ? (this.form_id + '_') : '') + cookie_name + "=''; expires=" + d.toUTCString() + "; path=/; SameSite=Strict; Secure";
		document.cookie = cookie_string;

		return true;
	}

	// Get session storage raw
	$.WS_Form.prototype.session_storage_get_raw = function(key, default_value) {

		if(typeof(default_value) === 'undefined') { default_value = ''; }

		if(
			(key === '') ||
			(typeof(sessionStorage) !== 'object') ||
			(sessionStorage.getItem(key) === null)
		) {
			return default_value;
		}

		return sessionStorage.getItem(key);
	}

	// Get local storage raw
	$.WS_Form.prototype.local_storage_get_raw = function(key, default_value) {

		if(typeof(default_value) === 'undefined') { default_value = ''; }

		if(
			(key === '') ||
			(typeof(localStorage) !== 'object') ||
			(localStorage.getItem(key) === null)
		) {
			return default_value;
		}

		return localStorage.getItem(key);
	}

	// Tabs
	$.WS_Form.prototype.tabs = function(obj, atts) {

		if(typeof(atts) === 'undefined') { atts = {}; };
		var tab_selector = (typeof(atts.selector) !== 'undefined') ? atts.selector : 'li';
		var tab_active_index = (typeof(atts.active) !== 'undefined') ? atts.active : 0;
		var tab_activate = (typeof(atts.activate) !== 'undefined') ? atts.activate : false;

		var ws_this = this;
		var tab_index = 0;

		obj.addClass('wsf-tabs');

		$(tab_selector, obj).each(function() {

			var tab_obj_outer = $(this);

			tab_obj_outer.find('a[href*="#"]:not([href="#"])').each(function() {

				// Add tab index data attribute
				$(this).attr('data-tab-index', tab_index);

				// Click event
				$(this).off('click').on('click', function(e) {

					e.preventDefault();

					// Trigger any conditional logic associated with a tab click
					$(this).trigger('wsf-click');

					// Stop further propagation
					e.stopPropagation();
					e.stopImmediatePropagation();

					ws_this.tab_show($(this), tab_obj_outer, tab_activate);
				});

				// Initialize tab
				if(tab_index == tab_active_index) {
					
					ws_this.tab_show($(this), tab_obj_outer);
				}
			});

			tab_index++;
		});
	}

	// Tabs - Destroy
	$.WS_Form.prototype.tabs_destroy = function(obj, atts) {

		if(typeof atts === 'undefined') { atts = {}; };
		var tab_selector = (typeof atts.selector !== 'undefined') ? atts.selector : 'li';

		$(tab_selector, obj).each(function() {

			var tab_obj_outer = $(this);

			tab_obj_outer.find('a').each(function() {

				// Remove tab index data attribute
				$(this).removeAttr('data-tab-index');

				// Remove click event
				$(this).off('click');
			});
		});

		obj.removeClass('wsf-tabs');
	}

	// Tabs - Show
	$.WS_Form.prototype.tab_show = function(tab_obj, tab_obj_outer, tab_activate) {

		// Hide siblings
		var ws_this = this;
		tab_obj_outer.siblings().each(function() {

			var tab_obj_sibling = $(this).find('a').first();
			ws_this.tab_hide(tab_obj_sibling, $(this));
		});

		// Tab
		tab_obj_outer.addClass('wsf-tab-active');

		// Tab content
		var tab_hash = tab_obj.attr('href');
		$(tab_hash).show();

		// Tab activate function
		if(typeof(tab_activate) === 'function') {

			var tab_index = tab_obj.attr('data-tab-index');
			tab_activate(tab_index);
		}

		// Fire event
		tab_obj.trigger('tab_show');
	}

	// Tabs - Hide
	$.WS_Form.prototype.tab_hide = function(tab_obj, tab_obj_outer) {

		// Tab
		tab_obj_outer.removeClass('wsf-tab-active');

		// Tab content
		var tab_hash = tab_obj.attr('href');
		$(tab_hash).hide();
	}

	// Build group_data_cache, section_data_cache, field_data_cache and action_data_cache
	$.WS_Form.prototype.data_cache_build = function() {

		// Check we can build the caches
		if(typeof this.form === 'undefined') { return false; }
		if(typeof this.form.groups === 'undefined') { return false; }

		// Clear data caches
		this.group_data_cache = [];
		this.section_data_cache = [];
		this.field_data_cache = [];
		this.action_data_cache = [];

		// Build group, section and field data caches
		for(var group_index in this.form.groups) {

			if(!this.form.groups.hasOwnProperty(group_index)) { continue; }

			var group = this.form.groups[group_index];

			// Process group
			this.data_cache_build_group(group);
		}

		// Build action data cache
		var action = this.get_object_meta_value(this.form, 'action', false);
		if(!(
			(action === false) ||
			(typeof(action.groups) === 'undefined') ||
			(typeof(action.groups[0]) === 'undefined') ||
			(typeof(action.groups[0].rows) !== 'object') ||
			(action.groups[0].rows.length == 0)
		)) {

			var rows = action.groups[0].rows;
			for(var row_index in rows) {

				if(!rows.hasOwnProperty(row_index)) { continue; }

				var row = rows[row_index];

				if(
					(row === null) ||
					(typeof(row) !== 'object') ||
					(typeof(row.data) !== 'object') ||
					(row.data.length == 0)
				) {
					continue;
				}

				this.action_data_cache[row.id] = {'label': row.data[0]};
			}
		}	

		return true;
	}

	// Build group_data_cache
	$.WS_Form.prototype.data_cache_build_group = function(group) {

		// Store to group_data_cache array
		this.group_data_cache[group.id] = group;

		for(var section_index in group.sections) {

			if(!group.sections.hasOwnProperty(section_index)) { continue; }

			var section = group.sections[section_index];

			// Process section
			this.data_cache_build_section(section);
		}

		return true;
	}

	// Build section_data_cache and field_data_cache
	$.WS_Form.prototype.data_cache_build_section = function(section) {

		// Store to section_data_cache array
		this.section_data_cache[section.id] = section;

		var section_repeatable = (

			(typeof(section.meta) !== 'undefined') &&
			(typeof(section.meta.section_repeatable) !== 'undefined') &&
			(section.meta.section_repeatable == 'on')
		);

		// HTML
		for(var field_index in section.fields) {

			if(!section.fields.hasOwnProperty(field_index)) { continue; }

			var field = section.fields[field_index];

			// Repeatable?
			if(section_repeatable) {

				field.section_repeatable_section_id = section.id;
			}

			// Skip fields that are unlicensed (Required for published data)
			if(typeof($.WS_Form.field_type_cache[field.type]) === 'undefined') { continue; }

			// Store to field_data_cache array
			this.field_data_cache[field.id] = field;
		}

		return true;
	}

	// Randomize array
	$.WS_Form.prototype.array_randomize = function(array_to_randomize) {

		for (var i = array_to_randomize.length - 1; i > 0; i--) {

			var j = Math.floor(Math.random() * (i + 1));
			var temp = array_to_randomize[i];
			array_to_randomize[i] = array_to_randomize[j];
			array_to_randomize[j] = temp;
		}					

		return array_to_randomize;
	}

	// Get nice duration
	$.WS_Form.prototype.get_nice_duration = function(duration, show_seconds) {

		if(typeof(show_seconds) == 'undefined') { show_seconds = true; }

		if(duration == 0) { return '-'; }

		var duration_hours = Math.floor(duration / 3600);

		if(show_seconds) {

			var duration_minutes =  Math.floor((duration % 3600) / 60);
			var duration_seconds = duration % 60;

		} else {

			var duration_minutes = Math.ceil((duration % 3600) / 60);
		}

		var return_array = [];

		if(duration_hours > 0) { return_array.push(duration_hours + ' ' + ((duration_hours == 1) ? this.language('hour') : this.language('hours'))); }
		if(duration_minutes > 0) { return_array.push(duration_minutes + ' ' + ((duration_minutes == 1) ? this.language('minute') : this.language('minutes'))); }

		if(show_seconds) {

			return_array.push(duration_seconds + ' ' + ((duration_seconds == 1) ? this.language('second') : this.language('seconds')));
		}

		return return_array.join(' ');
	}

	// Get nice distance
	$.WS_Form.prototype.get_nice_distance = function(distance_meters, unit_system) {

		if(distance_meters == 0) { return '-'; }

		switch(unit_system) {

			// Miles
			case 'IMPERIAL' :

				var distance_miles = distance_meters / 1609;

				if(distance_miles >= 1) {

					return Math.floor(distance_miles) + ' ' + ((distance_miles == 1) ? this.language('mile') : this.language('miles'));

				} else {

					var distance_feet = distance_meters * 3.2808399;

					return Math.floor(distance_feet) + ' ' + ((distance_feet == 1) ? this.language('feet') : this.language('feets'));
				}

			// Kilometers
			default :

				var distance_kilometers = distance_meters / 1000;

				if(distance_kilometers >= 1) {

					return (Math.round(distance_kilometers * 10) / 10) + ' ' + ((distance_kilometers == 1) ? this.language('kilometer') : this.language('kilometers'));

				} else {

					return (Math.round(distance_meters * 10) / 10) + ' ' + ((distance_meters == 1) ? this.language('meter') : this.language('meters'));
				}
		}
	}

	// ucwords
	$.WS_Form.prototype.ucwords = function(input_string) {

		var input_string_array = input_string.split(' ');

		for(var i = 0; i < input_string_array.length; i++) {

			input_string_array[i] = input_string_array[i].charAt(0).toUpperCase() + input_string_array[i].slice(1);
		}

		return input_string_array.join(' ');
	}

	// ucfirst
	$.WS_Form.prototype.ucfirst = function(input_string) {

		var return_string = '';

		if(input_string.length > 0) {

			return_string = input_string[0].toUpperCase();
			if(input_string.length > 1) { return_string += input_string.slice(1); }
		}

		return return_string;
	}

	// capitalize
	$.WS_Form.prototype.capitalize = function(input_string) {

		return this.ucwords(input_string.toLowerCase());
	}

	// sentence
	$.WS_Form.prototype.sentence = function(input_string) {

		return this.ucfirst(input_string.toLowerCase());
	}

	// Get label position
	$.WS_Form.prototype.get_label_position = function(field) {

		// Get sub type
		var sub_type = this.get_object_meta_value(field, 'sub_type', false);
		if(sub_type == '') { sub_type = false; }

		// Get label parameters
		var label_position = this.get_object_meta_value(field, 'label_position', 'default');
		label_position = this.get_field_value_fallback(field.type, false, 'label_position_force', label_position, false, sub_type);

		// Field is using default position, so read default label position of form
		if(label_position == 'default') {

			label_position = this.get_object_meta_value(this.form, 'label_position_form', 'top');
		}

		return label_position;
	}

	// Get help position
	$.WS_Form.prototype.get_help_position = function(field) {

		// Get sub type
		var sub_type = this.get_object_meta_value(field, 'sub_type', false);
		if(sub_type == '') { sub_type = false; }

		// Get help parameters
		var help_position = this.get_object_meta_value(field, 'help_position', 'default');
		help_position = this.get_field_value_fallback(field.type, false, 'help_position_force', help_position, false, sub_type);

		// Field is using default position, so read default help position of form
		if(help_position == 'default') {

			help_position = this.get_object_meta_value(this.form, 'help_position_form', 'bottom');
		}

		return help_position;
	}

	// Get form invalid feedback mask (Default invalid feedback message)
	$.WS_Form.prototype.get_form_invalid_feedback_mask = function() {

		var invalid_feedback_default = this.get_object_meta_value(this.form, 'invalid_feedback_mask', '');

		if(invalid_feedback_default == '') {

			// Get invalid feedback mask
			var meta_key_config = $.WS_Form.meta_keys['invalid_feedback_mask'];

			// Return placeholder
			return meta_key_config.p ? meta_key_config.p : meta_key_config.mask_placeholder;

		} else {

			// Return invalid feedback mask value
			return invalid_feedback_default;
		}
	}

	// Get form invalid feedback mask (Default invalid feedback message)
	$.WS_Form.prototype.get_invalid_feedback_mask_parsed = function(invalid_feedback, label) {

		// Parse invalid_feedback_mask_placeholder
		invalid_feedback = this.replace_all(invalid_feedback, '#label_lowercase', label.toLowerCase());
		invalid_feedback = this.replace_all(invalid_feedback, '#label', label);

		return invalid_feedback;
	}

	// Get field html
	$.WS_Form.prototype.get_field_html_single = function(field, value, is_submit, section_repeatable_index) {

		if(typeof(is_submit) === 'undefined') { is_submit = false; }
		if(typeof(section_repeatable_index) === 'undefined') { section_repeatable_index = false; }

		var field_html = '';
		var attributes_values_field = [];
		var has_value = (typeof(value) !== 'undefined');

		// If we are rendering a field for submission editing, don't register calculations
		var calc_register = !is_submit;

		// Build repeatable suffix
		var repeatable_suffix = ((section_repeatable_index !== false) ? '-repeat-' + section_repeatable_index : '');

		// Build field ID
		var field_id = this.get_part_id(field.id, section_repeatable_index);

		// Build field name
		var field_name = this.get_field_name(field.id, section_repeatable_index);

		// Submit only config
		var submit_attributes_field = ['default', 'class', 'input_type_datetime', 'multiple', 'min', 'max', 'step'];
		var submit_attributes_field_label = ['class'];

		if(typeof($.WS_Form.field_type_cache[field.type]) === 'undefined') { return ''; }

		// Get field type
		var field_type_config = $.WS_Form.field_type_cache[field.type];

		// Check to see if this field can be used in the current edition
		var pro_required = field_type_config['pro_required'];
		if(pro_required) { return this.language('error_pro_required'); }

		// Should label be rendered?
		if(is_submit) {

			var label_render = true;

		} else {

			var label_render = this.get_object_meta_value(field, 'label_render', true);
		}

		// Get sub type
		var sub_type = this.get_object_meta_value(field, 'sub_type', false);
		if(sub_type == '') { sub_type = false; }

		// Check for label disable override
		var label_disabled = this.get_field_value_fallback(field.type, false, 'label_disabled', false, false, sub_type);
		if(label_disabled) { label_render = false; }

		// Should field name be suffixed with []?
		var submit_array = (typeof field_type_config['submit_array'] !== 'undefined') ? field_type_config['submit_array'] : false;
		if(submit_array) { field_name += '[]'; }

		// Determine label_position (If we are not rendering the label, then set to top so no position specific framework masks are used)
		if(label_render && !is_submit) {

			var label_position = this.get_label_position(field);

		} else {

			var label_position = 'top';
		}
		var label_position_inside = false;

		// Check label position is value
		var framework = this.get_framework();
		if(framework.label_positions.indexOf(label_position) === -1) { label_position = 'top'; }

		// Get mask field attributes
		var mask_field_attributes = ($.extend(true, [], this.get_field_value_fallback(field.type, label_position, 'mask_field_attributes', [], false, sub_type)));

		// Check to see if wrappers should be ignored
		var mask_wrappers_drop = (typeof field_type_config['mask_wrappers_drop'] !== 'undefined') ? field_type_config['mask_wrappers_drop'] : false;

		// reCAPTCHA override
		if((field.type == 'recaptcha') && (this.get_object_meta_value(field, 'recaptcha_recaptcha_type', 'default') == 'invisible')) { mask_wrappers_drop = true; }

		// hCaptcha override
		if((field.type == 'hcaptcha') && (this.get_object_meta_value(field, 'hcaptcha_type', 'default') == 'invisible')) { mask_wrappers_drop = true; }

		// Load masks
		var mask = mask_wrappers_drop ? '#field' : this.get_field_value_fallback(field.type, label_position, 'mask', '#field', false, sub_type);
		var mask_field = this.get_field_value_fallback(field.type, label_position, 'mask_field', '', false, sub_type);
		if(is_submit) {

			var mask_field_submit = this.get_field_value_fallback(field.type, label_position, 'mask_field_submit', false, false, sub_type);
			if(mask_field_submit !== false) { mask_field = mask_field_submit; }
		}

		// If label is position left or right, remove help mask value from mask field (Wrapper has #pre_help and #post_help in it)
		if(label_position == 'left' || label_position == 'right') {

			mask_field = mask_field.replace('#pre_help', '');
			mask_field = mask_field.replace('#post_help', '');
		}

		var mask_field_label = label_render ? this.get_field_value_fallback(field.type, label_position, 'mask_field_label', '', false, sub_type) : '';
		var mask_field_label_hide_group = this.get_field_value_fallback(field.type, label_position, 'mask_field_label_hide_group', false, false, sub_type);
		var mask_help = this.get_field_value_fallback(field.type, label_position, 'mask_help', '', false, sub_type);
		var mask_help_append = this.get_field_value_fallback(field.type, label_position, 'mask_help_append', '', false, sub_type);
		var mask_help_append_separator = this.get_field_value_fallback(field.type, label_position, 'mask_help_append_separator', '', false, sub_type);
		var mask_invalid_feedback = this.get_field_value_fallback(field.type, label_position, 'mask_invalid_feedback', '', false, sub_type);

		// Get values
		var default_value_raw = this.get_object_meta_value(field, 'default_value', '', false, false);
		var default_value = this.parse_variables_process(default_value_raw, section_repeatable_index, false, field, 'field_value', calc_register).output;

		var text_editor_raw = this.get_object_meta_value(field, 'text_editor', '', false, false);
		var text_editor = this.parse_variables_process(text_editor_raw, section_repeatable_index, false, field, 'field_text_editor', calc_register).output;

		var html_editor_raw = this.get_object_meta_value(field, 'html_editor', '', false, false);
		var html_editor = this.parse_variables_process(html_editor_raw, section_repeatable_index, false, field, 'field_html', calc_register).output;

		var ecommerce_calculation_persist = this.get_object_meta_value(field, 'ecommerce_calculation_persist', '', false, true);
		var dedupe_value_scope = this.get_object_meta_value(field, 'dedupe_value_scope', '', false, true);
		var hidden_bypass = this.get_object_meta_value(field, 'hidden_bypass', '', false, true);
		var exclude_cart_total = this.get_object_meta_value(field, 'exclude_cart_total', '', false, true);

		// Get text editor and html editor values

		// Get default value
		if(!has_value) {

			value = '';
			if(default_value != '') { value = this.esc_html(default_value); }
			if(text_editor != '') { value = text_editor; }
			if(html_editor != '') { value = html_editor; }
		}

		// Input group
		var process_input_group = true;
		var append = '';

		// Field type checks
		switch(field.type) {

			case 'textarea' :

				process_input_group = (this.get_object_meta_value(field, 'input_type_textarea', '') === '');
				break;

			case 'select' :
			case 'checkbox' :
			case 'radio' :
			case 'price_select' :
			case 'price_checkbox' :
			case 'price_radio' :

				if(value) {

					// Split comma separate values into a trimmed value array
					value = (typeof(value) === 'string') ? value.split(',').map(function(value) { return value.trim(); }) : value;

					has_value = true;
				}

				break;

			case 'file' :
			case 'signature' :
			case 'googlemap' :

				if(
					(typeof(value) === 'object')
				) {

					value = this.esc_html(JSON.stringify(value));
				}

				break;

			case 'price_range' :

				// Price range (Needs to be converted back from currency to number in order for the field to work with a value)
				if(has_value && (field.type == 'price_range')) { value = this.get_number(value); }
				break;

			case 'password' :

				var append = '';

				var password_generate_toggle = this.get_object_meta_value(field, 'password_generate', '');
				var password_visibility_toggle = this.get_object_meta_value(field, 'password_visibility_toggle', '');
				var append_value = this.get_object_meta_value(field, 'append', '');

				if(password_generate_toggle) {

					var password_append_title = this.get_object_meta_value(field, 'text_password_generate', '');
					if(password_append_title == '') { password_append_title = this.language('password_generate'); }
					append += '<div data-wsf-password-generate style="cursor: pointer; user-select: none; outline: 0;' + ((password_visibility_toggle || append_value) ? ' margin-' + (ws_form_settings.rtl ? 'left' : 'right') + ': 8px;' : '') + '" title="' + this.esc_attr(password_append_title) + '" tabindex="0" role="button" aria-pressed="false"><svg style="display: block;" height="16" width="16" viewBox="0 0 16 16"><path d="M8 0c-3 0-5.6 1.6-6.9 4.1l-1.1-1.1v4h4l-1.5-1.5c1-2 3.1-3.5 5.5-3.5 3.3 0 6 2.7 6 6s-2.7 6-6 6c-1.8 0-3.4-0.8-4.5-2.1l-1.5 1.3c1.4 1.7 3.6 2.8 6 2.8 4.4 0 8-3.6 8-8s-3.6-8-8-8z"/></svg></div>';
				}

				if(password_visibility_toggle) {

					var password_append_title = this.get_object_meta_value(field, 'text_password_visibility_toggle_off', '');
					if(password_append_title == '') { password_append_title = this.language('password_visibility_toggle_off'); }
					append += '<div data-wsf-password-visibility-toggle style="cursor: pointer; user-select: none; outline: 0;' + (append_value ? ' margin-' + (ws_form_settings.rtl ? 'left' : 'right') + ': 8px;' : '') + ';" title="' + this.esc_attr(password_append_title) + '" tabindex="0" role="button" aria-pressed="false"><svg style="display: block;" height="16" width="16" viewBox="0 0 16 16"><g class="wsf-password-visibility-off"><path d="M8 3.9c-6.7 0-8 5.1-8 5.1s2.2 4.1 7.9 4.1 8.1-4 8.1-4-1.3-5.2-8-5.2zM5.3 5.4c0.5-0.3 1.3-0.3 1.3-0.3s-0.5 0.9-0.5 1.6c0 0.7 0.2 1.1 0.2 1.1l-1.1 0.2c0 0-0.3-0.5-0.3-1.2 0-0.8 0.4-1.4 0.4-1.4zM7.9 12.1c-4.1 0-6.2-2.3-6.8-3.2 0.3-0.7 1.1-2.2 3.1-3.2-0.1 0.4-0.2 0.8-0.2 1.3 0 2.2 1.8 4 4 4s4-1.8 4-4c0-0.5-0.1-0.9-0.2-1.3 2 0.9 2.8 2.5 3.1 3.2-0.7 0.9-2.8 3.2-7 3.2z"></path></g><g class="wsf-password-visibility-on" style="display: none;"><path d="M12.9 5.2l-0.8 0.8c1.7 0.9 2.5 2.3 2.8 3-0.7 0.9-2.8 3.1-7 3.1-0.7 0-1.2-0.1-1.8-0.2l-0.8 0.8c0.8 0.3 1.7 0.4 2.6 0.4 5.7 0 8.1-4 8.1-4s-0.6-2.4-3.1-3.9z"></path><path d="M12 7.1c0-0.3 0-0.6-0.1-0.8l-4.8 4.7c0.3 0 0.6 0.1 0.9 0.1 2.2 0 4-1.8 4-4z"></path><path d="M15.3 0l-4.4 4.4c-0.8-0.2-1.8-0.4-2.9-0.4-6.7 0-8 5.1-8 5.1s1 1.8 3.3 3l-3.3 3.2v0.7h0.7l15.3-15.3v-0.7h-0.7zM4 11.3c-1.6-0.7-2.5-1.8-2.9-2.3 0.3-0.7 1.1-2.2 3.1-3.2-0.1 0.4-0.2 0.8-0.2 1.3 0 1.1 0.5 2.2 1.3 2.9l-1.3 1.3zM6.2 7.9l-1 0.2c0 0-0.3-0.5-0.3-1.2 0-0.8 0.4-1.5 0.4-1.5 0.5-0.3 1.3-0.3 1.3-0.3s-0.5 0.9-0.5 1.7c-0.1 0.7 0.1 1.1 0.1 1.1z"></path></g></svg></div>';
				}
				break;
		}

		// Prepend / append
		var prepend = process_input_group ? this.parse_variables_process(this.get_object_meta_value(field, 'prepend', ''), section_repeatable_index, false, field, 'field_prepend', calc_register).output : '';
		append += process_input_group ? this.parse_variables_process(this.get_object_meta_value(field, 'append', ''), section_repeatable_index, false, field, 'field_prepend', calc_register).output : '';

		// Classes
		var class_field = '';
		if(!is_submit) {

			var class_field_array = [];

			var class_field_form = this.get_object_meta_value(this.form, 'class_field', '', false, true);
			if(class_field_form != '') { class_field_array.push(class_field_form); }

			var class_field = this.get_object_meta_value(field, 'class_field', '', false, true);
			if(class_field != '') { class_field_array.push(class_field); }

			// Full width class for buttons
			if(!this.get_object_meta_value(field, 'class_field_full_button_remove', '')) {

				var class_field_full_button = this.get_field_value_fallback(field.type, label_position, 'class_field_full_button', '', false, sub_type);
				if(typeof(class_field_full_button) === 'object') {
					class_field_array.push(class_field_full_button.join(' '));
				}
			}

			// Type class for buttons
			var class_field_button_type = this.get_object_meta_value(field, 'class_field_button_type', false);
			if(!class_field_button_type) {

				var class_field_button_type = this.get_field_value_fallback(field.type, label_position, 'class_field_button_type_fallback', false, false, sub_type);
			}
			if(class_field_button_type) {

				var class_field_button_type_config = this.get_field_value_fallback(field.type, label_position, 'class_field_button_type', '', false, sub_type);
				if(typeof(class_field_button_type_config[class_field_button_type]) !== 'undefined') {

					class_field_array.push(class_field_button_type_config[class_field_button_type]);
				}
			}

			// Checkbox and radio styles
			switch(field.type) {

				case 'checkbox' :
				case 'radio' :

					switch(this.get_object_meta_value(field, field.type + '_style', '')) {

						case 'button' : class_field_array.push('wsf-button'); break;
						case 'button-full' : class_field_array.push('wsf-button wsf-button-full'); break;
						case 'switch' : class_field_array.push('wsf-switch'); break;
						case 'color' : class_field_array.push('wsf-color'); break;
						case 'color-circle' : class_field_array.push('wsf-color wsf-circle'); break;
						case 'image' : class_field_array.push('wsf-image'); break;
						case 'image-circle' : class_field_array.push('wsf-image wsf-circle'); break;
						case 'image-responsive' : class_field_array.push('wsf-image wsf-responsive wsf-image-full'); break;
						case 'image-circle-responsive' : class_field_array.push('wsf-image wsf-responsive wsf-image-full wsf-circle'); break;
					}

					break;
			}

			// Type class for message
			var class_field_message_type = this.get_object_meta_value(field, 'class_field_message_type', false);
			if(class_field_message_type) {

				var class_field_message_type_config = this.get_field_value_fallback(field.type, label_position, 'class_field_message_type', '', false, sub_type);
				if(typeof(class_field_message_type_config[class_field_message_type]) !== 'undefined') {

					class_field_array.push(class_field_message_type_config[class_field_message_type]);
				}
			}

			// Input group
			if(
				(prepend !== '') ||
				(append !== '')
			) {

				var class_field_input_group = this.get_field_value_fallback(field.type, label_position, 'class_field_input_group', '', false, sub_type);
				if(class_field_input_group !== '') {

					class_field_array.push(class_field_input_group);
				}
			}

			// Label position
			var label_inside = (typeof(field_type_config['label_inside']) !== 'undefined') ? field_type_config['label_inside'] : false;
			if(
				label_inside &&
				(label_position === 'inside')
			) {

				switch(field.type) {

					case 'textarea' :

						var input_type_textarea = this.get_object_meta_value(field, 'input_type_textarea', false);

						label_position_inside = !(

							(input_type_textarea === 'tinymce') ||
							(input_type_textarea === 'html')
						);

						break;

					case 'select' :
					case 'price_select' :

						var select2 = this.get_object_meta_value(field, 'select2', false);

						label_position_inside = !select2;

						break;

					default :

						label_position_inside = true;
				}

				// Fallback to top
				if(!label_position_inside) { label_position = 'top'; }
			}

			if(!label_inside && (label_position === 'inside')) { label_position = 'top'; }

			if(label_position_inside) {

				field.meta.placeholder = this.parse_variables_process(field.label, section_repeatable_index, false, field, 'field_label', calc_register).output;
			}

			class_field = class_field_array.join(' ');
		}

		// Label / field column widths (For left/right label positioning)
		var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

		var column_width_label_form = parseInt(this.get_object_meta_value(this.form, 'label_column_width_form', 3), 10);
		var column_width_label = this.get_object_meta_value(field, 'label_column_width', 'default');

		switch(column_width_label) {

			case 'default' :
			case '' :

				column_width_label = column_width_label_form;
				break;

			default :

				column_width_label = parseInt(column_width_label, 10);
		}
		if(column_width_label >= framework_column_count) { column_width_label = (framework_column_count - 1); }

		var column_width_field = framework_column_count - column_width_label;

		// Field - Mask values
		var mask_values_field = {

			'id': 					field_id,
			'form_id_prefix':  		this.form_id_prefix,
			'form_id':  			this.form_id,
			'form_instance_id':  	this.form_instance_id,
			'field_id': 			field.id,

			'name': 				field_name,
			'label': 				this.parse_variables_process(field.label, section_repeatable_index, false, field, 'field_label', calc_register).output,
			'value': 				value,
			'required': 			(this.get_object_meta_value(this.form, 'label_required')) ? '<span class="wsf-required-wrapper"></span>' : '',

			'column_width_label': 	column_width_label,
			'column_width_field': 	column_width_field,

			'max_upload_size': 		ws_form_settings.max_upload_size,
			'locale': 				ws_form_settings.locale,
			'currency':  			$.WS_Form.settings_plugin.currency
		};

		// Field type checks
		switch(field.type) {

			// Date
			case 'datetime' :

				if(

					// Use date/time picker
					($.WS_Form.settings_plugin.ui_datepicker == 'on') ||

					// If browser does not support native date/time picker, use jQuery component
					(
						($.WS_Form.settings_plugin.ui_datepicker == 'native') &&
						!this.native_date
					)

				) {

					mask_values_field.datetime_type = 'text';

					if(label_position_inside) {

						mask_field_attributes.push('placeholder');
					}

				} else {

					mask_values_field.datetime_type = this.get_object_meta_value(field, 'input_type_datetime', 'date');
				}
			
				break;

			// Color
			case 'color' :

				if(
					// Use color picker
					($.WS_Form.settings_plugin.ui_color == 'on') ||

					// If browser does not support native color picker, use jQuery component
					(
						($.WS_Form.settings_plugin.ui_color == 'native') &&
						!this.native_color
					)

				) {

					mask_values_field['color_type'] = 'text';

				} else {

					mask_values_field['color_type'] = 'color';
				}
			
				break;

			// File
			case 'file' :

				switch(sub_type) {

					case 'dropzonejs' :

						var placeholder = this.get_object_meta_value(field, 'placeholder', '', false, true);
						if(placeholder == '') { placeholder = this.language('dropzonejs_default_message'); }
						mask_values_field['placeholder'] = placeholder;

						break;
				}

				break;
		}
		// Field - Mask values - Meta data
		var meta_key_parse_variables = this.get_field_value_fallback(field.type, label_position, 'meta_key_parse_variables', [], false, sub_type);
		for(var meta_key_parse_variables_index in meta_key_parse_variables) {

			if(!meta_key_parse_variables.hasOwnProperty(meta_key_parse_variables_index)) { continue; }

			// Get meta key
			var meta_key = meta_key_parse_variables[meta_key_parse_variables_index];

			// Get default value
			var meta_key_config = (typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') ? false : $.WS_Form.meta_keys[meta_key];
			var meta_key_value_default = (meta_key_config !== false) ? ((typeof(meta_key_config['d']) === 'undefined') ? '' : meta_key_config['d']) : '';

			// Get meta value
			var meta_value = this.get_object_meta_value(field, meta_key, meta_key_value_default);

			// If value is an array, turn it into a JSON string
			if(typeof(meta_value) === 'object') { meta_value = JSON.stringify(meta_value); }

			// Encode single quotes for JS purposes
			meta_value = this.replace_all(meta_value, "'", '&#39;');

			mask_values_field[meta_key] = meta_value;
		}

		// Field label - Mask values
		var mask_values_field_label = $.extend(true, {}, mask_values_field);
		mask_values_field_label['label_id'] = this.get_part_id(field.id, section_repeatable_index, 'label');

		// Help
		var help_id = this.get_part_id(field.id, section_repeatable_index, 'help');
		var help = !is_submit ? this.get_object_meta_value(field, 'help', '', false, false) : '';
		var has_help = (help !== '');

		// Initial parse to prevent syntax errors
		var mask_values_help = {

			// Value
			'value':	0,

			// Characters
			'character_count':				0,
			'character_count_label':		'',
			'character_remaining':			0,
			'character_remaining_label':	'',
			'character_min':				0,
			'character_min_label':			'',
			'character_max':				0,
			'character_max_label':			'',

			// Words
			'word_count':			0,
			'word_count_label':		'',
			'word_remaining':		0,
			'word_remaining_label': '',
			'word_min':				0,
			'word_min_label':		'',
			'word_max':				0,
			'word_max_label':		''
		};
		help = this.mask_parse(help, mask_values_help);

		// Parse help
		var help = this.parse_variables_process(help, section_repeatable_index, false, field, 'field_help', calc_register).output;

		// Help - When editing a submission, change help by field type
		if(is_submit) {

			switch(field.type) {

				case 'range' :

					help = '#value';
					break;
			}
		}

		// Help position
		var help_position = this.get_help_position(field);

		// Help classes
		var class_help_array = this.get_field_value_fallback(field.type, label_position, 'class_help_' + ((help_position == 'bottom') ? 'post' : 'pre'), [], false, sub_type);

		// Help mask values
		mask_values_help['id'] = field_id;
		mask_values_help['help_id'] = help_id;
		mask_values_help['help_class'] = class_help_array.join(' ');
		mask_values_help['help'] = help;

		mask_values_field['help_class'] = class_help_array.join(' ');

		// Get invalid_feedback parameters
		var invalid_feedback_render = (is_submit ? false : this.get_object_meta_value(field, 'invalid_feedback_render', false, false, true));

		// Invalid feedback
		var invalid_feedback_last_row = false;
		if(invalid_feedback_render) {

			var mask_values_invalid_feedback = ($.extend(true, {}, mask_values_field));

			// Invalid feedback ID
			var invalid_feedback_id = this.form_id_prefix + 'invalid-feedback-' + field.id + repeatable_suffix;

			// Invalid feedback classes
			var class_invalid_feedback_array = this.get_field_value_fallback(field.type, label_position, 'class_invalid_feedback', [], false, sub_type);
			var invalid_feedback_last_row = this.get_field_value_fallback(field.type, label_position, 'invalid_feedback_last_row', false, false, sub_type);

			// Get invalid feedback string
			var invalid_feedback = this.get_object_meta_value(field, 'invalid_feedback', '', false, true);

			if(invalid_feedback == '') {

				invalid_feedback = this.get_form_invalid_feedback_mask();

			}

			// Parse invalid feedback
			invalid_feedback = this.get_invalid_feedback_mask_parsed(invalid_feedback, field.label);

			// Invalid feedback mask values
			mask_values_invalid_feedback['invalid_feedback_id'] = invalid_feedback_id;
			mask_values_invalid_feedback['invalid_feedback_class'] = class_invalid_feedback_array.join(' ');
			mask_values_invalid_feedback['invalid_feedback'] = invalid_feedback;
			mask_values_invalid_feedback['attributes'] = '';

			var invalid_feedback_parsed = this.mask_parse(mask_invalid_feedback, mask_values_invalid_feedback);

		} else {

			var invalid_feedback_id = false;
			var invalid_feedback_parsed = '';
		}

		mask_values_field['invalid_feedback'] = invalid_feedback_parsed;
		mask_values_field_label['invalid_feedback'] = invalid_feedback_parsed;

		// Field - Attributes
		mask_values_field['attributes'] = '';

		if(is_submit) {

			var mask_field_attributes = submit_attributes_field.filter(function(val) {

				return mask_field_attributes.indexOf(val) != -1;
			});
		}

		if(mask_field_attributes.length > 0) {
			var get_attributes_return = this.get_attributes(field, mask_field_attributes, false, section_repeatable_index);
			mask_values_field['attributes'] += ' '  + get_attributes_return.attributes;
			mask_field_attributes = get_attributes_return.mask_attributes;
			attributes_values_field = get_attributes_return.attribute_values;
		}

		// If there is no wrapper (e.g. hidden field) then add data-repeatable-index attribute directly to field
		if(section_repeatable_index !== false) {

			mask_values_field['attributes'] += ' data-repeatable-index="' + this.esc_attr(section_repeatable_index) + '"';
		}

		// Attributes to inherit at a row level
		var mask_values_row_attributes_source = '';

		// Select/Radio - Cascade
		var cascade = false;
		var cascade_field_filter = false;

		switch(field.type) {

			case 'select' :
			case 'price_select' :
			case 'checkbox' :
			case 'price_checkbox' :
			case 'radio' :
			case 'price_radio' :

				var cascade = this.get_object_meta_value(field, field.type + '_cascade', false);

				if(cascade) {

					var cascade_field_filter = parseInt(this.get_object_meta_value(field, field.type + '_cascade_field_filter', false), 10);
					var cascade_field_id = parseInt(this.get_object_meta_value(field, field.type + '_cascade_field_id', false), 10);

					if(cascade_field_id > 0) {

						var cascade_attribute = ' data-cascade-field-id="' + this.esc_attr(cascade_field_id) + '"';

						switch(field.type) {

							case 'select' :
							case 'price_select' :

								mask_values_field['attributes'] += cascade_attribute;
								break;

							case 'checkbox' :
							case 'price_checkbox' :
							case 'radio' :
							case 'price_radio' :

								mask_values_row_attributes_source = cascade_attribute;
								break;
						}
					}
				}

				var select2_ajax = this.get_object_meta_value(field, 'select2_ajax', false);

				if((cascade || select2_ajax) && value) {

					// Ensure value is an array
					if(typeof(value) !== 'object') { value = [value]; }

					// Add data-wsf-populate attribute
					mask_values_field['attributes'] += ' data-wsf-populate="' + this.esc_attr(JSON.stringify(value)) + '"';
				} 

				break;
		}
		// Custom attributes
		mask_values_field['attributes'] = this.custom_attributes(mask_values_field['attributes'], field, 'field', section_repeatable_index);

		// Field - Attributes - Orientation
		var orientation = this.get_object_meta_value(field, 'orientation', false);

		if(orientation == 'grid') {

			// Get wrapper and row classes
			var class_orientation_wrapper_array = this.get_field_value_fallback(field.type, label_position, 'class_orientation_wrapper', [], false, sub_type);
			var class_orientation_row_array = this.get_field_value_fallback(field.type, label_position, 'class_orientation_row', [], false, sub_type);
			var orientation_group_wrapper_class = class_orientation_wrapper_array.join(' ');

			// Get class array
			var orientation_class_array = this.column_class_array(field, 'orientation_breakpoint');
			orientation_class_array = class_orientation_row_array.concat(orientation_class_array);
			var orientation_row_class = orientation_class_array.join(' ');
		}

		// Field label - Attributes
		mask_values_field_label['attributes'] = '';
		var mask_field_label_attributes = ($.extend(true, [], this.get_field_value_fallback(field.type, label_position, 'mask_field_label_attributes', [], false, sub_type)));

		if(is_submit) {

			var mask_field_label_attributes = submit_attributes_field_label.filter(function(val) {

				return mask_field_label_attributes.indexOf(val) != -1;
			});
		}

		if(mask_field_label_attributes.length > 0) {
			var get_attributes_return = this.get_attributes(field, mask_field_label_attributes, false, section_repeatable_index);
			mask_values_field_label['attributes'] += get_attributes_return.attributes;
			mask_field_label_attributes = get_attributes_return.mask_attributes;
		}

		// Mask values - Data
		var data = '';
		var data_source = this.get_object_value(field_type_config, 'data_source', false);
		var data_row_count = 0;

		var data_source_process = (data_source !== false);

		if(data_source_process) {

			// Get data source type
			if(typeof(data_source.type) === 'undefined') {

				data_source_process = false;

			} else {

				var data_source_type = data_source.type;
			}
			
			// Get data source ID
			if(typeof(data_source.id) === 'undefined') {

				data_source_process = false;

			} else {

				var data_source_id = data_source.id;
			}
		}

		if(data_source_process) {

			// Get array of data
			switch(data_source_type) {

				case 'data_grid' :

					var data_source_object_data = this.get_object_meta_value(field, data_source_id, false);
					break;
			}
			if(data_source_object_data === false) { data_source_process = false; }

			// Columns
			if(typeof(data_source_object_data.columns) === 'undefined') {

				data_source_process = false;

			} else {

				var data_columns = data_source_object_data.columns;
			}
		}

		if(data_source_process) {

			// Data masks
			var mask_group 				=	this.get_field_value_fallback(field.type, label_position, 'mask_group', '', false, sub_type);
			var mask_group_wrapper 		=	this.get_field_value_fallback(field.type, label_position, 'mask_group_wrapper', '', false, sub_type);
			var mask_group_label 		=	this.get_field_value_fallback(field.type, label_position, 'mask_group_label', '', false, sub_type);
			var mask_group_always		=	this.get_field_value_fallback(field.type, label_position, 'mask_group_always', false, false, sub_type);

			var mask_row 				=	this.get_field_value_fallback(field.type, label_position, 'mask_row', '', false, sub_type);
			var mask_row_placeholder	=	this.get_field_value_fallback(field.type, label_position, 'mask_row_placeholder', '', false, sub_type);
			var mask_row_field			=	this.get_field_value_fallback(field.type, label_position, 'mask_row_field', '', false, sub_type);

			// Mask row label can be defined at a framework level for field types to support inline and wrapping labels
			var mask_row_label 			=	this.get_field_value_fallback(field.type, label_position, 'mask_row_label', '', false, sub_type);

			var mask_row_lookups 		=	this.get_field_value_fallback(field.type, label_position, 'mask_row_lookups', [], false, sub_type);
			var datagrid_column_value 	=	this.get_field_value_fallback(field.type, label_position, 'datagrid_column_value', false, false, sub_type);

			var mask_row_default 		= 	this.get_field_value_fallback(field.type, label_position, 'mask_row_default', '', false, sub_type);
			var mask_row_required 		= 	this.get_field_value_fallback(field.type, label_position, 'mask_row_required', ' required data-required', false, sub_type);
			var mask_row_disabled 		= 	this.get_field_value_fallback(field.type, label_position, 'mask_row_disabled', ' disabled', false, sub_type);
			var mask_row_visible 		= 	this.get_field_value_fallback(field.type, label_position, 'mask_row_visible', ' visible', false, sub_type);

			// Randomize rows
			var rows_randomize = this.get_object_meta_value(field, 'data_grid_rows_randomize', '', false, true);
			if(rows_randomize) {

				// Check to see if term hierarchy is enabled, if it is we should force row_randomize to false
				var data_source_term_hierarchy = this.get_object_meta_value(field, 'data_source_term_hierarchy', '', false, true);
				if(data_source_term_hierarchy) { rows_randomize = false; }
			}

			// Placeholder row (e.g. Adds Select... as the first row)
			var placeholder_row = this.get_object_meta_value(field, 'placeholder_row', '', false, true);
			var multiple = this.get_object_meta_value(field, 'multiple', '', false, true);
			if(
				(placeholder_row != '') &&
				!multiple
			) {

				// Inject placeholder row
				var mask_values_row_placeholder = $.extend(true, {}, mask_values_field);
				mask_values_row_placeholder['value'] = placeholder_row;
				data += this.mask_parse(mask_row_placeholder, mask_values_row_placeholder);
			}

			// Value should be an array
			if(has_value && (typeof(value) !== 'object')) {

				// If value is a number, change it to a string (Ensures indexOf works below)
				if(typeof(value) !== 'string') { value = value.toString(); }

				// Convert to array
				value = [value];
			}

			// Has value an index of
			var has_value_indexof = has_value && (typeof(value.indexOf) !== 'undefined');

			// Build mask lookup cache
			var mask_row_lookup_array = [];

			// Run through each data mask field
			for(var mask_row_lookup_key in mask_row_lookups) {

				if(!mask_row_lookups.hasOwnProperty(mask_row_lookup_key)) { continue; }

				// Read data mask field value (this will be the ID for that data grid column)
				var mask_row_lookup = mask_row_lookups[mask_row_lookup_key];

				// Reset
				mask_row_lookup_array[mask_row_lookup] = false;

				// Read value from data
				var mask_row_lookup_value = this.get_object_meta_value(field, mask_row_lookup, false, false, true);

				// If not found...
				if(mask_row_lookup_value === false) { 

					if(typeof($.WS_Form.meta_keys[mask_row_lookup]) !== 'undefined') {

						// Check for a default value
						var meta_key_config = $.WS_Form.meta_keys[mask_row_lookup];

						mask_row_lookup_value = (typeof(meta_key_config.d) !== 'undefined') ? meta_key_config.d : 0;

						// If default value is larger than the number of available columns, set it to zero
						if(mask_row_lookup_value > data_columns.length) { mask_row_lookup_value = 0; }

					} else {

						continue;
					}
				}

				// Run through the data grid columns until we find the ID
				var data_column_index = false;
				for(var data_columns_index in data_columns) {

					if(!data_columns.hasOwnProperty(data_columns_index)) { continue; }

					var data_column = data_columns[data_columns_index];
					var data_column_id = data_column.id;

					// Match found, store in cache
					if(data_column_id == mask_row_lookup_value) { data_column_index = data_columns_index; break; }
				}

				if(data_column_index) { mask_row_lookup_array[mask_row_lookup] = data_column_index; }
			}

			// Cascade
			var cascade_field_filter_column_index = false;
			if(cascade_field_filter !== false) {

				// Run through the data grid columns until we find the ID
				var data_column_index = false;
				for(var data_columns_index in data_columns) {

					if(!data_columns.hasOwnProperty(data_columns_index)) { continue; }

					var data_column = data_columns[data_columns_index];
					var data_column_id = data_column.id;

					// Match found, store in cache
					if(data_column_id == cascade_field_filter) { cascade_field_filter_column_index = data_columns_index; break; }
				}
			}
			// Read groups
			if(typeof data_source_object_data.groups === 'undefined') { this.error('error_data_source_groups'); return ''; }
			var data_groups = data_source_object_data.groups;
			var data_groups_count = Object.keys(data_groups).length;

			// Randomize data groups?
			if(rows_randomize) { data_groups = this.array_randomize(data_groups); }

			// Cycle through groups
			for(var data_group_index in data_groups) {

				if(!data_groups.hasOwnProperty(data_group_index)) { continue; }

				// Mask values
				var mask_values_group = $.extend(true, {}, mask_values_field);
				mask_values_group['group_id'] = this.form_id_prefix + 'datagrid-' + field.id + '-group-' + data_group_index + repeatable_suffix;

				var data_group = data_groups[data_group_index];

				// Check group
				if(
					(data_group == null) ||
					(typeof(data_group) !== 'object')
				) {
					continue;
				}

				// Get group label
				if(typeof(data_group.label) === 'undefined') { this.error('error_data_group_label'); return ''; }
				switch(field.type) {

					case 'select' :
					case 'price_select' :

						var mask_values_group_label_render = true;
						break;

					default :

						var mask_values_group_label_render = (typeof(data_group.label_render) === 'undefined') ? true : data_group.label_render;
				}

				// Group label mask values
				if(mask_values_group_label_render) {

					var mask_values_group_label = $.extend(true, {}, mask_values_field);
					mask_values_group_label['group_label'] = data_group.label;
					mask_values_group_label['label_row_id'] = this.form_id_prefix + 'label-' + field.id + '-group-' + data_group_index + repeatable_suffix;

					// Parse group label mask to build group_label value
					mask_values_group['group_label'] = this.parse_variables_process(this.mask_parse(mask_group_label, mask_values_group_label), section_repeatable_index).output;

				} else {

					mask_values_group['group_label'] = '';
				}

				// Get group disabled (optional)
				mask_values_group['disabled'] = (typeof(data_group.disabled) !== 'undefined') ? (data_group.disabled == 'on' ? ' disabled' : '') : '';

				// Should group data mask be used?
				var mask_group_use = ((typeof(data_group.mask_group) !== 'undefined') ? (data_group.mask_group == 'on') : false) || mask_group_always;

				// Should field label be hidden if groups are in use
				if(mask_group_use && mask_field_label_hide_group) { mask_field_label = ''; }

				// Get group rows (If there are no rows, data_group.rows = undefined)
				var group = '';
				if(typeof(data_group.rows) !== 'undefined') {

					// Clone data group rows
					var data_rows = JSON.parse(JSON.stringify(data_group.rows));

					// Add 'Select All' row
					var select_all_row = this.get_object_meta_value(field, 'select_all', '');
					if(((field.type == 'checkbox') || (field.type == 'price_checkbox')) && (select_all_row == 'on')) {

						// Mask values - Data mask fields
						var select_all_value_index = false;
						var select_all_label_index = false;
						for(var mask_row_lookup in mask_row_lookup_array) {

							if(!mask_row_lookup_array.hasOwnProperty(mask_row_lookup)) { continue; }

							var select_all_index = mask_row_lookup_array[mask_row_lookup];

							if(mask_row_lookup == 'checkbox_field_value') { select_all_value_index = select_all_index; }
							if(mask_row_lookup == 'checkbox_field_label') { select_all_label_index = select_all_index; }
							if(mask_row_lookup == 'checkbox_price_field_value') { select_all_value_index = select_all_index; }
							if(mask_row_lookup == 'checkbox_price_field_label') { select_all_label_index = select_all_index; }
						}

						// Inject new row
						var select_all_row = {

							id: 0,
							default: '',
							required: '',
							hidden: '',
							disabled: '',
							select_all: true,
							data: []
						}
						var select_all_label = this.get_object_meta_value(field, 'select_all_label', '');
						if(select_all_label == '') { select_all_label = this.language('select_all_label'); }
						select_all_row.data[select_all_value_index] = select_all_label;
						select_all_row.data[select_all_label_index] = select_all_label;
						data_rows.unshift(select_all_row);
					}

					// Randomize data rows?
					if(rows_randomize) { data_rows = this.array_randomize(data_rows); }

					// Cycle through rows
					for(var data_row_index in data_rows) {

						if(!data_rows.hasOwnProperty(data_row_index)) { continue; }

						// Get row of data from data grid
						var data_row = data_rows[data_row_index];

						// Skip null rows
						if(
							(data_row === null) ||
							(typeof(data_row) !== 'object') ||
							(typeof(data_row['data']) !== 'object')
						) {
							continue;
						}

						// Cascade
						var cascade_field_filter_value = false;
						if(cascade_field_filter_column_index !== false) {

							var cascade_field_filter_value = (typeof(data_row['data'][cascade_field_filter_column_index]) !== 'undefined') ? data_row['data'][cascade_field_filter_column_index] : false;
						}
						// Is this the last row?
						var last_row = !invalid_feedback_last_row || (data_row_index == (data_rows.length - 1));

						// Mask values
						var mask_values_row = $.extend(true, {}, mask_values_field);

						// Clear values
						mask_values_row['data_grid_row_value'] = '';
						mask_values_row['data_grid_row_action_variable'] = '';
						mask_values_row['data_grid_row_label'] = '';
						mask_values_row['data_grid_row_price'] = '';
						mask_values_row['data_grid_row_price_currency'] = '';
						mask_values_row['data_grid_row_woocommerce_cart'] = '';

						// Mask values - Data mask fields
						for(var mask_row_lookup in mask_row_lookup_array) {

							if(!mask_row_lookup_array.hasOwnProperty(mask_row_lookup)) { continue; }

							// Clear value
							mask_values_row[mask_row_lookup] = '';

							var data_column_index = mask_row_lookup_array[mask_row_lookup];

							if(data_column_index === false) { continue; }
							if(typeof(data_row['data'][data_column_index]) === 'undefined') { continue; }

							var mask_row_lookup_value = data_row['data'][data_column_index];
							if(mask_row_lookup_value === null) { mask_row_lookup_value = ''; }

							mask_row_lookup_value = this.parse_variables_process(mask_row_lookup_value.toString(), section_repeatable_index, false, field, 'data_grid_row', calc_register).output;

							// HTML version of lookup (This is used for encoding labels in values, e.g. price_select option values)
							mask_values_row[mask_row_lookup + '_html'] = this.esc_html(mask_row_lookup_value);

							// Range slider percentage
							switch(field.type) {

								case 'range' :
								case 'price_range' :

									// Get min values
									var range_min = this.get_object_meta_value(field, 'min', 0);
									if((range_min == '') || isNaN(range_min)) { range_min = 0; }

									// Get max value
									var range_max = this.get_object_meta_value(field, 'max', 100);
									if((range_max == '') || isNaN(range_max)) { range_max = 100; }

									// Get range value
									var range_value = this.get_number(mask_row_lookup_value, true);

									// Get range
									var range = parseFloat(range_max) - parseFloat(range_min);

									// Get percentage
									var range_percentage = (range > 0) ? (((range_value - range_min) / range) * 100) : 0;

									var mask_row_lookup_percentage = mask_row_lookup + '_percentage';
									mask_values_row[mask_row_lookup_percentage] = range_percentage;

									// Build datagrid row variable
									if(mask_row_lookup.indexOf('_field_percentage') !== -1) { mask_values_row['data_grid_row_value_percentage'] = range_percentage; }

									break;
							}

							// Store before any encoding
							mask_values_row[mask_row_lookup + '_compare'] = this.esc_html(mask_row_lookup_value);

							// Check for HTML encoding or a price
							var price = false;
							if(typeof($.WS_Form.meta_keys[mask_row_lookup]) !== 'undefined') {

								// Get meta key config
								var mask_row_lookup_config = $.WS_Form.meta_keys[mask_row_lookup];

								// Check for HTML encoding
								var esc_html = (typeof(mask_row_lookup_config['h']) !== 'undefined') ? mask_row_lookup_config['h'] : false;
								if(esc_html) { mask_row_lookup_value = this.esc_html(mask_row_lookup_value); }

								// Check for price
								var price = (typeof(mask_row_lookup_config['pr']) !== 'undefined') ? mask_row_lookup_config['pr'] : false;
							}

							if(price) {

								var mask_row_lookup_value_number = this.get_number(mask_row_lookup_value);
								var mask_row_lookup_currency = mask_row_lookup + '_currency';
								var mask_row_lookup_value_currency= this.get_price(mask_row_lookup_value_number);
								mask_values_row[mask_row_lookup_currency] = mask_row_lookup_value_currency;
								mask_values_row[mask_row_lookup] = mask_row_lookup_value_number;

								// Build datagrid row variable
								if(mask_row_lookup.indexOf('_price_field_price') !== -1) { mask_values_row['data_grid_row_price'] = mask_row_lookup_value_number; }
								if(mask_row_lookup_currency.indexOf('_price_field_price_currency') !== -1) { mask_values_row['data_grid_row_price_currency'] = mask_row_lookup_value_currency; }

							} else {

								mask_values_row[mask_row_lookup] = mask_row_lookup_value;
							}

							// Build datagrid row variables
							if(mask_row_lookup.indexOf('_field_value') !== -1) { mask_values_row['data_grid_row_value'] = mask_row_lookup_value; }
							if(mask_row_lookup.indexOf('_field_parse_variable') !== -1) { mask_values_row['data_grid_row_action_variable'] = mask_row_lookup_value; }
							if(mask_row_lookup.indexOf('_field_label') !== -1) { mask_values_row['data_grid_row_label'] = mask_row_lookup_value; }
							if(mask_row_lookup.indexOf('_field_wc') !== -1) { mask_values_row['data_grid_row_woocommerce_cart'] = mask_row_lookup_value; }
						}

						// Check for row value mask (Used by price_select, price_radio and price_checkbox)
						if(typeof(field_type_config['mask_row_value']) !== 'undefined') {

							mask_values_row['row_value'] = this.mask_parse(field_type_config['mask_row_value'], mask_values_row);

							// Re-parse if user has variables in the row value
							if(mask_values_row['row_value'].indexOf('#') > -1) {

								mask_values_row['row_value'] = this.mask_parse(mask_values_row['row_value'], mask_values_row);
							}
						}
						if(typeof(field_type_config['mask_row_price']) !== 'undefined') {

							mask_values_row['row_price'] = this.mask_parse(field_type_config['mask_row_price'], mask_values_row);
						}

						// Mask values row
						if(data_row.select_all) {

							mask_values_row['row_id'] = this.form_id_prefix + 'field-' + field.id + '-group-' + data_group_index + '-row-' + data_row['id'] + repeatable_suffix;

						} else {

							mask_values_row['row_id'] = this.form_id_prefix + 'field-' + field.id + '-row-' + data_row['id'] + repeatable_suffix;
						}
						mask_values_row['data_id'] = data_row['id'];

						// Copy to row label and field mask values
						var mask_values_row_field = $.extend(true, {}, mask_values_row);
						var mask_values_row_label = $.extend(true, {}, mask_values_row);
						mask_values_row_label['label_id'] = this.form_id_prefix + 'label-' + field.id + repeatable_suffix;
						mask_values_row_label['label_row_id'] = this.form_id_prefix + 'label-' + field.id + '-row-' + data_row['id'] + repeatable_suffix;

						// Build default extra values
						var extra_values_default = [];
						if(
							(!has_value && data_row['default']) ||
							(has_value && (datagrid_column_value !== false) && has_value_indexof && (value.indexOf(mask_values_row[datagrid_column_value + '_compare']) > -1)) ||
							(has_value && (typeof(mask_values_row['row_value']) !== 'undefined') && has_value_indexof && (value.indexOf(mask_values_row['row_value']) > -1))
						) { extra_values_default['default'] = mask_row_default; }
						if(data_row['disabled']) { extra_values_default['disabled'] = mask_row_disabled; }
						if(data_row['required']) { extra_values_default['required'] = mask_row_required; }

						// These attributes we manually push across to rows
						extra_values_default['dedupe_value_scope'] = dedupe_value_scope;
						extra_values_default['hidden_bypass'] = hidden_bypass;
						extra_values_default['exclude_cart_total'] = exclude_cart_total;
						extra_values_default['ecommerce_calculation_persist'] = ecommerce_calculation_persist;

						mask_values_row['attributes'] = mask_values_row_attributes_source;
						mask_values_row_label['attributes'] = '';
						mask_values_row_field['attributes'] = '';

						// Row - Attributes
						var extra_values = $.extend(true, [], extra_values_default);

						if(!is_submit) {

							// class (Inline)
							var class_inline_array = (orientation == 'horizontal') ? this.get_field_value_fallback(field.type, label_position, 'class_inline', false, false, sub_type) : false;
							if(class_inline_array !== false) { extra_values['class'] = class_inline_array.join(' '); }

							// class (Row)
							var class_row_array = this.get_field_value_fallback(field.type, label_position, 'class_row', false, false, sub_type)
							if(class_row_array !== false) { extra_values['class'] = (class_inline_array !== false) ? (extra_values['class'] + ' ' + class_row_array.join(' ')) : class_row_array.join(' '); }

							// class if disabled
							if(data_row['disabled']) {
								var class_row_disabled_array = this.get_field_value_fallback(field.type, label_position, 'class_row_disabled', false, false, sub_type);
								if(class_row_disabled_array !== false) { extra_values['class'] += ' ' + class_row_disabled_array.join(' '); }
							}
						}

						var mask_row_attributes = ($.extend(true, [], this.get_field_value_fallback(field.type, label_position, 'mask_row_attributes', [], false, sub_type)));
						if(mask_row_attributes.length > 0) {
							var get_attributes_return = this.get_attributes(field, mask_row_attributes, extra_values, section_repeatable_index);
							mask_values_row['attributes'] += ' ' + get_attributes_return.attributes;
						}

						// Cascading filter value
						if(cascade_field_filter_value !== false) {

							mask_values_row['attributes'] += ' data-cascade-value="' + this.esc_attr(cascade_field_filter_value) + '"';
						}
						// Skip hidden rows
						if((typeof(data_row['hidden']) !== 'undefined') && data_row['hidden'] && !is_submit) {

							switch(field.type) {

								// Select
								case 'select' :

									continue;

								// Checkboxes and radios
								default :

									mask_values_row['attributes'] += ' style="display: none;"';
							}
						}

						// Orientation
						if(
							(orientation == 'grid') &&
							(orientation_row_class != '') 
						) {

							mask_values_row['attributes'] = this.attribute_modify(mask_values_row['attributes'], 'class', orientation_row_class, true);
						}

						// Row - Label - Attributes
						var extra_values = $.extend(true, [], extra_values_default);

						// Class
						var class_row_field_label_array = this.get_field_value_fallback(field.type, label_position, 'class_row_field_label', false, false, sub_type);
						if(class_row_field_label_array !== false) { extra_values['class'] = class_row_field_label_array.join(' '); }

						var mask_row_label_attributes = ($.extend(true, [], this.get_field_value_fallback(field.type, label_position, 'mask_row_label_attributes', [], false, sub_type)));

						if(is_submit) {

							var mask_row_label_attributes = submit_attributes_field_label.filter(function(val) {

								return mask_row_label_attributes.indexOf(val) != -1;
							});
						}

						if(mask_row_label_attributes.length > 0) {
							var get_attributes_return = this.get_attributes(field, mask_row_label_attributes, extra_values, section_repeatable_index);
							mask_values_row_label['attributes'] += ' ' + get_attributes_return.attributes;
						}

						// Row - Field - Attributes
						var extra_values = $.extend(true, [], extra_values_default);

						// class
						var class_row_field_array = this.get_field_value_fallback(field.type, label_position, 'class_row_field', false, false, sub_type);
						if(class_row_field_array !== false) { extra_values['class'] = class_row_field_array.join(' '); }

						// class (Field setting)
						if(!is_submit && (class_field != '')) { extra_values['class'] += ' ' + class_field.trim(); }

						// aria-labelledby
						extra_values['aria_labelledby'] = mask_values_row_label['label_row_id'];

						// Row attributes
						if(
							(!has_value && data_row['default']) ||
							(has_value && (datagrid_column_value !== false) && has_value_indexof && (value.indexOf(mask_values_row[datagrid_column_value + '_compare']) > -1))
						) { extra_values['default'] = mask_row_default; }

						if(data_row['disabled']) { extra_values['disabled'] = mask_row_disabled; }
						if(data_row['required']) { extra_values['required'] = mask_row_required; }

						// Copy field level attributes to row
						extra_values['required_row'] = attributes_values_field['required'];

						// Build row field attributes
						var mask_row_field_attributes = ($.extend(true, [], this.get_field_value_fallback(field.type, label_position, 'mask_row_field_attributes', [], false, sub_type)));

						if(is_submit) {

							var mask_row_field_attributes = submit_attributes_field.filter(function(val) {

								return mask_row_field_attributes.indexOf(val) != -1;
							});
						}

						if(mask_row_field_attributes.length > 0) {
							var get_attributes_return = this.get_attributes(field, mask_row_field_attributes, extra_values, section_repeatable_index);
							mask_values_row_field['attributes'] += ' ' + get_attributes_return.attributes;
						}
						if(mask_values_row_field['attributes'] != '') { mask_values_row_field['attributes'] = ' ' + mask_values_row_field['attributes']; }

						if(typeof(data_row.select_all) !== 'undefined') {

							mask_values_row_field['attributes'] += ' data-wsf-select-all';
						}

						// Hierarchy (Vertical only)
						if(
							(typeof(data_row.hierarchy) !== 'undefined') &&
							(orientation == '') &&
							!rows_randomize
						) {

							switch(field.type) {

								case 'checkbox' :
								case 'price_checkbox' :
								case 'radio' :
								case 'price_radio' :

									mask_values_row['attributes'] += ' data-wsf-hierarchy="' + this.esc_attr(data_row.hierarchy) + '"';
									break;

								case 'select' :
								case 'price_select' :

									if(data_row.hierarchy > 0) {

										mask_values_row['select_field_label'] = '&nbsp;&nbsp;&nbsp;'.repeat(data_row.hierarchy) + mask_values_row['select_field_label'];
									}
									break;
							}
						}

						// Parse invalid feedback for rows
						if(invalid_feedback_render && !invalid_feedback_last_row) {

							invalid_feedback_id = this.form_id_prefix + 'invalid-feedback-' + field.id + '-row-' + data_row['id'] + repeatable_suffix;
							mask_values_invalid_feedback['invalid_feedback_id'] = invalid_feedback_id;
							if(invalid_feedback_render) {

								var invalid_feedback_parsed = this.mask_parse(mask_invalid_feedback, mask_values_invalid_feedback);

							} else {

								var invalid_feedback_parsed = '';
							}
						}

						// Invalid feedback
						mask_values_row_field['invalid_feedback'] = (last_row ? invalid_feedback_parsed : '');
						mask_values_row_label['invalid_feedback'] = (last_row ? invalid_feedback_parsed : '');

						// Parse field
						var row_field_html = this.mask_parse(mask_row_field, mask_values_row_field);
						mask_values_row_label['row_field'] = row_field_html;
						mask_values_row['row_field'] = row_field_html;

						// Parse label
						var row_field_label = this.mask_parse(mask_row_label, mask_values_row_label);
						mask_values_row['row_label'] = row_field_label;

						// Parse row
						group += this.mask_parse(mask_row, mask_values_row);

						// Increment data row count
						data_row_count++;
					}
				}

				// Check for group wrapper
				if(mask_group_wrapper != '') {

					var mask_values_group_wrapper = {

						group : group
					};

					if(

						(orientation == 'grid') &&
						(orientation_group_wrapper_class != '')
					) {

						mask_values_group_wrapper['attributes'] = ' class="' + this.esc_attr(orientation_group_wrapper_class) + '"';
					}
					group = this.mask_parse(mask_group_wrapper, mask_values_group_wrapper);
				}

				if((mask_group !== false) && mask_group_use) {

					// Parse mask_group
					mask_values_group['group'] = group;
					data += this.mask_parse(mask_group, mask_values_group);

				} else {

					// Ignore group mask, there is only one group
					data += group;
				}
			}
		}

		// Add to mask array
		if(data_row_count > 0) {

			mask_values_field['datalist'] = data;

		} else {

			mask_values_field['datalist'] = '';
		}

		// Field - Attributes
		if(mask_field_attributes.length > 0) {

			var extra_values = [];

			// list
			if(typeof(mask_values_group) !== 'undefined') {
				if(
					(typeof(mask_values_group['group_id']) !== 'undefined') &&
					data_row_count > 0

				) { extra_values['list'] = mask_values_group['group_id']; }
			}

			// aria_labelledby (Used if aria_label is blank)
			var aria_label = this.get_object_meta_value(field, 'aria_label', false, false, true);
			if(aria_label === '') {

				if(
					label_render &&
					(mask_field_label.indexOf('#attributes') !== -1)	// Without attributes, we cannot reference the ID
				) {

					// Use aria_labelledby instead of aria_label
					extra_values['aria_labelledby'] = this.form_id_prefix + 'label-' + field.id + repeatable_suffix;

				} else {

					// Set to label
					extra_values['aria_label'] = field.label;
				}
			}

			// aria_describedby
			if(has_help) { extra_values['aria_describedby'] = help_id; }

			// class (Config)
			var class_field_array = this.get_field_value_fallback(field.type, label_position, 'class_field', false, false, sub_type);
			if(class_field_array !== false) { extra_values['class'] = class_field_array.join(' '); }

			// class (Field setting)
			if(class_field != '') { extra_values['class'] += ' '  + class_field.trim(); }

			// Process attributes
			var get_attributes_return = this.get_attributes(field, mask_field_attributes, extra_values, section_repeatable_index);

			// Store as mask value
			if(get_attributes_return.attributes != '') { mask_values_field['attributes'] += ' ' + get_attributes_return.attributes; }
		}

		// Field Label - Attributes
		if(mask_field_label_attributes.length > 0) {

			var extra_values = [];

			// class
			var class_field_label_array = this.get_field_value_fallback(field.type, label_position, 'class_field_label', false, false, sub_type);
			if(class_field_label_array !== false) { extra_values['class'] = class_field_label_array.join(' '); }

			// Process attributes
			var get_attributes_return = this.get_attributes(field, mask_field_label_attributes, extra_values, section_repeatable_index);

			// Store as mask value
			if(get_attributes_return.attributes != '') { mask_values_field_label['attributes'] += ' ' + get_attributes_return.attributes; }

		}

		// Parse help mask append
		mask_values_help['help_append_separator'] = has_help ? this.mask_parse(mask_help_append_separator, mask_values_help) : '';
		if(mask_help_append != '') {

			mask_values_help['text_clear'] = this.get_object_meta_value(field, 'text_clear', '');
			if(mask_values_help['text_clear'] == '') { mask_values_help['text_clear'] = this.language('clear'); }

			mask_values_help['text_reset'] = this.get_object_meta_value(field, 'text_reset', '');
			if(mask_values_help['text_reset'] == '') { mask_values_help['text_reset'] = this.language('reset'); }

			var help_append_parsed = this.mask_parse(mask_help_append, mask_values_help);

		} else {

			var help_append_parsed = '';
		}
		mask_values_help['help_append'] = help_append_parsed;
		mask_values_help['attributes'] = '';

		// Parse help mask
		var help_parsed = (has_help || help_append_parsed != '') ? this.mask_parse(mask_help, mask_values_help) : '';

		// Pre and post help
		mask_values_field['pre_help'] = mask_values_field_label['pre_help'] = ((help_position == 'bottom') ? '' : help_parsed);
		mask_values_field['post_help'] = mask_values_field_label['post_help'] = ((help_position == 'bottom') ? help_parsed : '');

		// Legacy
		mask_values_field['help'] = help_parsed;
		mask_values_field_label['help'] = help_parsed;

		// Trim attributes
		if(mask_values_field['attributes'] != '') { mask_values_field['attributes'] = ' ' + mask_values_field['attributes'].trim(); }
		if(mask_values_field_label['attributes'] != '') { mask_values_field_label['attributes'] = ' ' + mask_values_field_label['attributes'].trim(); }

		// Parse label
		var label_parsed = this.mask_parse(mask_field_label, mask_values_field_label);

		// Parse label wrapper
		if(label_parsed != '' && !mask_wrappers_drop) {

			var mask_field_label_wrapper = this.get_field_value_fallback(field.type, label_position, 'mask_field_label_wrapper', false, false, sub_type);
			if(mask_field_label_wrapper !== false) {
				var mask_field_label_wrapper_values = {'label': label_parsed};
				label_parsed = this.mask_parse(mask_field_label_wrapper, mask_field_label_wrapper_values);
			}
		}

		var field_in_label = (mask_field_label.indexOf('#field') !== -1);

		// Build field mask based upon label position
		switch(label_position) {

			// Bottom / Inside
			case 'inside' :
			case 'bottom' :

				mask_values_field['pre_label'] = '';
				mask_values_field['post_label'] = field_in_label ? mask_values_field['label'] : label_parsed;
				break;

			// Top
			case 'top' :

				mask_values_field['pre_label'] = field_in_label ? mask_values_field['label'] : label_parsed;
				mask_values_field['post_label'] = '';
				break;

			default :

				mask_values_field['pre_label'] = '';
				mask_values_field['post_label'] = '';
				mask_values_field_label['pre_label'] = '';
				mask_values_field_label['post_label'] = '';
				break;
		}

		// Input group
		if(
			(prepend !== '') ||
			(append !== '')
		) {

			var mask_values_input_group = $.extend(true, {}, mask_values_field);
			mask_values_input_group['invalid_feedback'] = mask_values_field['invalid_feedback'];
			mask_values_input_group['pre_help'] = mask_values_field['pre_help'];
			mask_values_input_group['post_help'] = mask_values_field['post_help'];
			mask_values_input_group['pre_label'] = mask_values_field['pre_label'];
			mask_values_input_group['post_label'] = mask_values_field['post_label'];
			mask_values_field['invalid_feedback'] = '';
			mask_values_field['pre_help'] = '';
			mask_values_field['post_help'] = '';
			mask_values_field['pre_label'] = '';
			mask_values_field['post_label'] = '';

			// Legacy
			mask_values_input_group['help'] = mask_values_field['help'];
			mask_values_field['help'] = '';
		}

		// Parse field
		var field_parsed = this.mask_parse(mask_field, mask_values_field);

		// Input group parsing
		if(
			(prepend !== '') ||
			(append !== '')
		) {

			// Calculate column widths
			var col_small_prepend_factor = this.get_field_value_fallback(field.type, label_position, 'col_small_prepend_factor', false, false, sub_type);
			var col_small_append_factor = this.get_field_value_fallback(field.type, label_position, 'col_small_append_factor', false, false, sub_type);
			if(
				(col_small_prepend_factor !== false) &&
				(col_small_append_factor !== false)
			) {

				var col_small_prepend = (prepend !== '') ? Math.round(framework_column_count * col_small_prepend_factor, 0) : 0;
				mask_values_input_group['col_small_prepend'] = col_small_prepend;

				var col_small_append = (append !== '') ? Math.round(framework_column_count * col_small_append_factor, 0) : 0;
				mask_values_input_group['col_small_append'] = col_small_append;

				mask_values_input_group['col_small_field'] = framework_column_count - (col_small_prepend + col_small_append);
			}

			// Field
			mask_values_input_group['field'] = field_parsed;

			var mask_field_input_group_field = this.get_field_value_fallback(field.type, label_position, 'mask_field_input_group_field', '#field', false, sub_type);

			if(mask_field_input_group_field !== '') {

				field_parsed = this.mask_parse(mask_field_input_group_field, mask_values_input_group);
			}

			// Prepend
			if(prepend !== '') {

				mask_values_input_group['prepend'] = prepend;

				var mask_field_input_group_prepend = this.get_field_value_fallback(field.type, label_position, 'mask_field_input_group_prepend', '#prepend', false, sub_type);

				if(mask_field_input_group_prepend !== '') {

					var prepend_parsed = this.mask_parse(mask_field_input_group_prepend, mask_values_input_group);

					field_parsed = prepend_parsed + field_parsed;
				}
			}

			// Append
			if(append !== '') {

				mask_values_input_group['append'] = append;

				var mask_field_input_group_append = this.get_field_value_fallback(field.type, label_position, 'mask_field_input_group_append', '#append', false, sub_type);

				if(mask_field_input_group_append !== '') {

					var append_parsed = this.mask_parse(mask_field_input_group_append, mask_values_input_group);

					field_parsed = field_parsed + append_parsed;
				}
			}

			// Wrapper
			var mask_field_input_group = this.get_field_value_fallback(field.type, label_position, 'mask_field_input_group', '#field', false, sub_type);

			// Wrapper classes
			var css_input_group_array = [];
			if(prepend !== '') { css_input_group_array.push('wsf-input-group-has-prepend'); }
			if(append !== '') { css_input_group_array.push('wsf-input-group-has-append'); }
			var css_input_group = css_input_group_array.join(' ');
			if(css_input_group) { css_input_group = ' ' + css_input_group; }

			// Final parse
			mask_values_input_group['css_input_group'] = css_input_group;
			mask_values_input_group['field'] = field_parsed;

			field_parsed = this.mask_parse(mask_field_input_group, mask_values_input_group);
		}

		// Check to see if #field is in the label
		if(field_in_label) {

			// Render field in label
			mask_values_field_label['field'] = field_parsed;	// Make the field available in the label mask values
			label_parsed = this.mask_parse(mask_field_label, mask_values_field_label);

			// Finished with field
			field_parsed = '';
			mask_values_field_label['field'] = '';
		}

		// Check to see if the #label is in the field
		var label_in_field = (mask_field.indexOf('#label') !== -1);
		if(label_in_field) {

			// Render label in field
			mask_values_field['label'] = label_parsed;	// Make the label available in the field mask values
			field_parsed = this.mask_parse(mask_field, mask_values_field);

			// Finished with label
			label_parsed = '';
			mask_values_field['label'] = '';
		}

		// Parse field wrapper
		if(field_parsed != '' && !mask_wrappers_drop) {

			var mask_field_wrapper = this.get_field_value_fallback(field.type, label_position, 'mask_field_wrapper', false, false, sub_type);
			if(mask_field_wrapper !== false) {
				var mask_field_wrapper_values = {'field': field_parsed};
				field_parsed = this.mask_parse(mask_field_wrapper, mask_field_wrapper_values);
			}
		}

		// Build field mask based upon label position
		switch(label_position) {

			// Right
			case 'right' :

				field_parsed = field_parsed + label_parsed;
				break;

			// Left
			case 'left' :

				field_parsed = label_parsed + field_parsed;
				break;

			default :

				if(field_in_label) {

					field_parsed = label_parsed;
				}
		}

		// Final parse
		mask_values_field['field'] = field_parsed;
		field_html = this.mask_parse(mask, mask_values_field);

		return field_html;
	}

	// Modify attributes
	$.WS_Form.prototype.attribute_modify = function(attributes_string, key, value, append) {

		var ws_this = this;

		var key_found = false;
		var return_attribute_string = '';

		// Run through each attribute key / value
		var obj = $('<div ' + attributes_string + ' />');
		obj.each(function() {

			$.each(this.attributes, function() {

				if(this.specified) {

					var attribute_key = this.name;
					var attribute_value = this.value;

					if(attribute_key == key) {

						if(append) {

							attribute_value += ' ' + value;

						} else {

							attribute_value = value;
						}
						attribute_value.trim();

						key_found = true;
					}

					return_attribute_string += ' ' + attribute_key;
					if(attribute_value !== '') { return_attribute_string += '="' + ws_this.esc_attr(attribute_value) + '"'; }
				}
			});
		});

		if(!key_found) {

			return_attribute_string += ' ' + key;
			if(value !== '') { return_attribute_string += '="' + this.esc_attr(value) + '"'; }
		}

		return return_attribute_string;
	}

	// Range slider and meter fields
	$.WS_Form.prototype.form_help_value = function(obj) {

		var ws_this = this;
		if(typeof(obj) === 'undefined') { obj = this.form_canvas_obj; }

		// Run through each field
		$('input[type="range"]:not([data-init-help-value]),meter:not([data-init-help-value])', obj).each(function() {

			// If #value is found in the help text, add an event handler
			if(ws_this.form_help_value_render($(this))) {

				$(this).on('input change', function() { ws_this.form_help_value_render($(this)); });

				$(this).attr('data-init-help-value', '');
			}
		});
	}

	// Range slider and meter fields - Process
	$.WS_Form.prototype.form_help_value_render = function(obj) {

		// Get help text
		var field_selector_id = obj.attr('id');
		var field_wrapper_obj = obj.closest('[data-type]');
		var field_id = field_wrapper_obj.attr('data-id');
		var field_repeatable_index = field_wrapper_obj.attr('data-repeatable-index');
		var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '-repeat-' + field_repeatable_index : '';
		var field = this.field_data_cache[field_id];
		var help = this.get_object_meta_value(field, 'help', '');
		var return_value = false;

		// Check for lower track filling
		if(typeof(obj.attr('data-fill-lower-track')) !== 'undefined') {

			var min = parseFloat(obj[0].min || '0');
			var max = parseFloat(obj[0].max || '100');
			var value = parseFloat(obj[0].value || min);
			var range = max - min;
			var percentage = (range > 0) ? ((((value - min) / range) * 100.0) + '%') : '0%';
			$('#' + field_selector_id).get(0).style.setProperty('--wsf-fill-lower-track', percentage);

			return_value = true;
		}

		// Check for help updating
		if(help.indexOf('#value') !== -1) {

			// Parse help mask
			var mask_values_help = {'id': field_selector_id, 'value': obj.val()};
			var help_parsed = this.mask_parse(help, mask_values_help);
			var help_parsed = this.parse_variables_process(help_parsed, field_repeatable_index, false, field, 'field_help').output

			// Update help HTML
			var help_id = this.form_id_prefix + 'help-' + field_id + repeatable_suffix;
			$('#' + help_id).html(help_parsed);

			return_value = true;
		}

		return return_value;
	}

	// Get attributes
	$.WS_Form.prototype.get_attributes = function(object, mask_attributes, extra_values, section_repeatable_index) {

		if(typeof(extra_values) !== 'object') { extra_values = false; }

		// Build attributes array
		var attributes = [];
		var attribute_values = [];

		if(mask_attributes !== false) {

			for(var mask_attributes_key in mask_attributes) {

				if(!mask_attributes.hasOwnProperty(mask_attributes_key)) { continue; }

				var mask_attribute_meta_key = mask_attributes[mask_attributes_key];

				// Skip unknown meta_keys
				if(typeof($.WS_Form.meta_keys[mask_attribute_meta_key]) === 'undefined') { continue; }

				var meta_key = $.WS_Form.meta_keys[mask_attribute_meta_key];

				// Read meta key mask data
				if(this.is_admin) {
	
					var meta_key_mask = (typeof(meta_key.mask) !== 'undefined') ? meta_key.mask : '';
					var meta_key_mask_disregard_on_empty = (typeof meta_key.mask_disregard_on_empty !== 'undefined') ? meta_key.mask_disregard_on_empty : false;
					var meta_key_mask_disregard_on_zero = (typeof meta_key.mask_disregard_on_zero !== 'undefined') ? meta_key.mask_disregard_on_zero : false;
					var meta_key_default = (typeof(meta_key.default) !== 'undefined') ? meta_key.default : '';

				} else {

					var meta_key_mask = (typeof(meta_key.m) !== 'undefined') ? meta_key.m : '';
					var meta_key_mask_disregard_on_empty = (typeof meta_key.e !== 'undefined') ? meta_key.e : false;
					var meta_key_mask_disregard_on_zero = (typeof meta_key.z !== 'undefined') ? meta_key.z : false;
					var meta_key_default = (typeof(meta_key.d) !== 'undefined') ? meta_key.d : '';
				}

				if(extra_values !== false) {

					// Use extra values
					if(typeof(extra_values[mask_attribute_meta_key]) !== 'undefined') {

						var meta_value = extra_values[mask_attribute_meta_key].trim();

					} else {

						var meta_value = '';
					}

				} else {

					// If meta_key key parameter is set, use that to get the object meta value
					if(this.is_admin) {

						var get_object_meta_value_key = (typeof(meta_key.key) !== 'undefined') ? meta_key.key : mask_attribute_meta_key;

					} else {

						var get_object_meta_value_key = (typeof(meta_key.k) !== 'undefined') ? meta_key.k : mask_attribute_meta_key;
					}

					// Get value
					var field_part = (typeof(meta_key.c) !== 'undefined') ? meta_key.c : false;
					if(field_part !== false) {

						var meta_value = this.get_object_meta_value(object, get_object_meta_value_key, meta_key_default, false, false);
						meta_value = this.parse_variables_process(meta_value, section_repeatable_index, false, object, field_part).output

					} else {

						var meta_value = this.get_object_meta_value(object, get_object_meta_value_key, meta_key_default, false, true);
					}

					// Check for global fallback
					if(
						(meta_value == '') &&
						(typeof(meta_key.g) !== 'undefined')
					) {

						var required_setting_global_meta_key = meta_key.g;

						if(typeof($.WS_Form.settings_plugin[required_setting_global_meta_key]) !== 'undefined') {

							if($.WS_Form.settings_plugin[required_setting_global_meta_key]) {

								meta_value  = $.WS_Form.settings_plugin[required_setting_global_meta_key];
							}
						}
					}

					// Remember value
					attribute_values[get_object_meta_value_key] = meta_value;
				}

				// HTML encode value
				meta_value = this.esc_html(meta_value);

				// Parse mask
				var attribute_meta_value = this.mask_parse(meta_key_mask, {'value': meta_value});

				// Push attribute key value pair to attributes array
				if(
					((meta_key_mask_disregard_on_empty && (meta_value != '')) || !meta_key_mask_disregard_on_empty)
					&&
					((meta_key_mask_disregard_on_zero && (parseInt(meta_value, 10) != 0)) || !meta_key_mask_disregard_on_zero)

				) {

					// Push field to attribute array
					attributes.push(attribute_meta_value);

					// Remove this key from the array (so that it is not re-processed further down)
					delete mask_attributes[mask_attributes_key];
				}
			}
		}

		// Remove empty array elements
		mask_attributes = this.array_remove_empty(mask_attributes);

		// Return field attributes string
		return {

			'attributes': 		attributes.join(' '),
			'mask_attributes': 	mask_attributes,
			'attribute_values': attribute_values
		};
	}

	// Get number
	$.WS_Form.prototype.get_number = function(number_input, default_value, process_currency, decimals) {

		if(typeof(default_value) === 'undefined') { default_value = 0; }
		if(typeof(process_currency) === 'undefined') { process_currency = true; }
		if(typeof(decimals) === 'undefined') { decimals = false; }

		// Convert numbers to text
		if(typeof(number_input) === 'number') { number_input = number_input.toString(); }

		// Check input is a string
		if(typeof(number_input) !== 'string') { return 0; }

		// Trim input
		number_input = number_input.trim();

		// If input string contains currency symbol, process currency
		if(number_input.includes(ws_form_settings.currency_symbol)) {

			process_currency = true;
		}

		// Get decimal separator
		var decimal_separator = $.WS_Form.settings_plugin.price_decimal_separator;

		// Convert from current currency
		if(process_currency) {

			// Get thousand separator
			var thousand_separator = $.WS_Form.settings_plugin.price_thousand_separator;

			// Get currency symbol
			var currency_symbol = ws_form_settings.currency_symbol;

			// Decode HTML characters
			if(number_input.includes('&')) {

				number_input = this.parse_html_entities(number_input);
			}

			// Filter out currency symbol (We do not in isolation in case the currency symbol contains the decimal separator character, e.g. UAE)
			number_input = number_input.replace(currency_symbol, '');

			// Ensure the decimal separator setting is included in the regex (Add ,. too in case default value includes alternatives)
			var number_input_regex = new RegExp('[^0-9-' + decimal_separator + ']', 'g');
			number_input = number_input.replace(number_input_regex, '');

			if(
				(decimal_separator !== '') &&
				(thousand_separator !== '') &&
				(decimal_separator === thousand_separator)
			) {

				// Convert decimal separators to periods so parseFloat works
				if(number_input.substr(-3, 1) === decimal_separator) {

					var decimal_index = (number_input.length - 3);
					number_input = number_input.substr(0, decimal_index) + '[dec]' + number_input.substr(decimal_index + 1);
				}

				// Remove thousand separators
				number_input = number_input.replace(thousand_separator, '');

				// Replace [dec] back to decimal separator for parseFloat
				number_input = number_input.replace('[dec]', '.');

			} else {

				// Replace decimal separator to period for parseFloat
				if(decimal_separator !== '') {

					number_input = number_input.replace(decimal_separator, '.');
				}
			}

		} else {

			// Replace decimal separator to period for parseFloat
			if(decimal_separator !== '') {

				number_input = number_input.replace(decimal_separator, '.');
			}
		}

		// parseFloat converts decimal separator to period to ensure that function works
		var number_output = (number_input.trim() === '') ? default_value : (isNaN(number_input) ? default_value : parseFloat(number_input));

		// Round
		if(decimals !== false) { number_output = this.get_number_rounded(parseFloat(number_output), decimals); }

		return number_output;
	}

	// Get number rounded
	$.WS_Form.prototype.get_number_rounded = function(value, decimals) {

		return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
	}

	// Get number to step
	$.WS_Form.prototype.get_number_to_step = function(value, step) {

		if(isNaN(step)) { return value; }

		step || (step = 1.0);
		var inv = 1.0 / step;
		return Math.round(value * inv) / inv;
	}

	// Get float
	$.WS_Form.prototype.get_float = function(number_input, default_value) {

		if(typeof(default_value) === 'undefined') { default_value = 0; }

		// Convert numbers to text
		if(typeof(number_input) === 'number') { number_input = number_input.toString(); }

		// Check input is a string
		if(typeof(number_input) !== 'string') { return 0; }

		// Trim input
		number_input = number_input.trim();

		return parseFloat(number_input);
	}

	// Get currency
	$.WS_Form.prototype.get_currency = function() {

		var return_obj = {};

		var price_decimals = parseInt($.WS_Form.settings_plugin.price_decimals, 10);

		return_obj.prefix = ws_form_settings.currency_symbol;
		return_obj.suffix = '';

		var currency_position = $.WS_Form.settings_plugin.currency_position;
		switch(currency_position) {

			case 'right' :

				return_obj.prefix = '';
				return_obj.suffix = ws_form_settings.currency_symbol;
				break;

			case 'left_space' :

				return_obj.prefix = ws_form_settings.currency_symbol + ' ';
				break;

			case 'right_space' :

				return_obj.prefix = '';
				return_obj.suffix = ' ' + ws_form_settings.currency_symbol;
				break;
		}

		// Price decimals
		return_obj.decimals = (price_decimals < 0) ? 0 : price_decimals;

		// Separators
		return_obj.decimal_separator = $.WS_Form.settings_plugin.price_decimal_separator;
		return_obj.thousand_separator = $.WS_Form.settings_plugin.price_thousand_separator;

		return return_obj;
	}

	// Get price
	$.WS_Form.prototype.get_price = function(price_float, currency, currency_symbol_render) {

		if(typeof(currency) === 'undefined') { currency = this.get_currency(); }
		if(typeof(currency_symbol_render) === 'undefined') { currency_symbol_render = true; }

		if(typeof(price_float) !== 'number') { price_float = parseFloat(price_float); }

		var price = (currency_symbol_render ? currency.prefix : '') + this.replace_all(this.replace_all(price_float.toFixed(currency.decimals).replace(/\B(?=(\d{3})+(?!\d))/g, '[thousand]'), '.', currency.decimal_separator), '[thousand]', currency.thousand_separator) + (currency_symbol_render ? currency.suffix : '');

		return price;
	}

	// Get slug
	$.WS_Form.prototype.get_slug = function(input) {

		return input
			.toString()
			.normalize( 'NFD' )
			.replace( /[\u0300-\u036f]/g, '' )
			.toLowerCase()
			.trim()
			.replace(/\s+/g, '-')
			.replace(/[^\w\-]+/g, '')
			.replace(/\-\-+/g, '-'); 
	}

	// Parse HTML entities
	$.WS_Form.prototype.parse_html_entities = function(str) {

		return str.replace(/&#([0-9]{1,3});/gi, function(match, num_str) {

			var num = parseInt(num_str, 10);

			return String.fromCharCode(num);
		});
	}

	// Add hidden field to canvas
	$.WS_Form.prototype.form_add_hidden_input = function(name, value, id, attributes, single_quote, type) {

		// Do not add if it already exists
		var obj = $('input[name="' + this.esc_selector(name) + '"]', this.form_canvas_obj);
		if(obj.length && (name.indexOf('[]') === -1)) {

			// Just set value if already exists and it is not an array
			obj.val(value);
			return;
		}

		// Check function attributes
		if(typeof(value) === 'undefined') { value = ''; }
		if(typeof(id) === 'undefined') { id = false; }
		if(typeof(attributes) === 'undefined') { attributes = false; }
		if(typeof(single_quote) === 'undefined') { single_quote = false; }
		if(typeof(type) === 'undefined') { type = 'hidden'; }

		// Append to form
		this.form_canvas_obj.append('<input type="' + this.esc_attr(type) + '" name="' + this.esc_attr(name) + '" value=' + (single_quote ? "'" : '"') + this.esc_attr(value) + (single_quote ? "'" : '"') + ((id !== false) ? (' id="' + this.esc_attr(id) + '"') : '') + ((attributes !== false) ? (' ' + attributes) : '') + ' />');
	}

	// Get part ID
	$.WS_Form.prototype.get_part_id = function(field_id, section_repeatable_index, identifier) {

		if(typeof(section_repeatable_index) === 'undefined') { section_repeatable_index = false; }
		if(typeof(identifier) === 'undefined') { identifier = 'field'; }

		var repeatable_suffix = ((section_repeatable_index !== false) ? '-repeat-' + section_repeatable_index : '');
		return this.form_id_prefix + identifier + '-' + field_id + repeatable_suffix;
	}

	// Get field name
	$.WS_Form.prototype.get_field_name = function(field_id, section_repeatable_index, is_submit) {

		if(typeof(section_repeatable_index) === 'undefined') { section_repeatable_index = false; }
		if(typeof(is_submit) === 'undefined') { is_submit = false; }

		var repeatable_suffix = ((section_repeatable_index !== false) ? '-repeat-' + section_repeatable_index : '');
		return this.field_name_prefix + field_id + ((section_repeatable_index !== false) ? (is_submit ? ('_' + section_repeatable_index) : ('[' + section_repeatable_index + ']')) : '');
	}

	// Is iterable?
	$.WS_Form.prototype.is_iterable = function(obj) {

		if(obj == null) { return false; }

		if(typeof(obj) !== 'object') { return false; }

		return typeof obj[Symbol.iterator] === 'function';
	}

	// Is integer?
	$.WS_Form.prototype.is_integer = function(input) {

		if(isNaN(input)) { return false; }

		return /^\d+\.?\d*$/.test(input);
	}

	// Is invalid? Fixes compatibility with jQuery mobile which breaks .is(':invalid')
	$.WS_Form.prototype.is_invalid = function(obj) {

		if(!obj[0] || !obj[0].validity) { return true; }

		return !obj[0].validity.valid;
	}

	// Is valid? Fixes compatibility with jQuery mobile which breaks .is(':valid')
	$.WS_Form.prototype.is_valid = function(obj) {

		if(!obj[0] || !obj[0].validity) { return false; }

		return obj[0].validity.valid;
	}

	// Replace all
	$.WS_Form.prototype.replace_all = function(input, search, replace) {

		if(replace === undefined) { return input.toString(); }

		return input.split(search).join(replace);
	}

	// Remove empty and null elements from an array
	$.WS_Form.prototype.array_remove_empty = function(arr) {

		if(typeof(arr) !== 'object') { return arr; }

		var arr_out = [];

		for(var key in arr) {

			// Check array has property key
			if(!arr.hasOwnProperty(key)) { continue; }

			// Strip null values
			if(arr[key] === null) { continue; }

			arr_out.push(arr[key]);
		}

		return arr_out;
	}

	// Attribute add item
	$.WS_Form.prototype.attribute_add_item = function(obj, attribute_name, item) {

		// Get existing attribute value
		var attribute_value = obj.attr(attribute_name);

		// If attribute exists
		if(typeof(attribute_value) !== 'undefined') {

			// If existing aria-describedby exists and invalid feedback ID is not in the string
			if(attribute_value.indexOf(item) === -1) {

				obj.attr(attribute_name, this.esc_attr(item) + (attribute_value ? ' ' : '') + attribute_value);
			}

		} else {

			// Attribute doesn't exist, so create it
			obj.attr(attribute_name, this.esc_attr(item));
		}

	}

	// Attribute remove item
	$.WS_Form.prototype.attribute_remove_item = function(obj, attribute_name, item) {

		// Get existing attribute value
		var attribute_value = obj.attr(attribute_name);

		// If attribute exists and it contains item
		if(
			(typeof(attribute_value) !== 'undefined') &&
			(attribute_value.indexOf(item) !== -1)
		) {
			// Split attribute value into array
			var attribute_value_array = attribute_value.split(' ');

			// Remove item element
			var ws_this = this;
			attribute_value_array = attribute_value_array.filter(function(e) { return e !== ws_this.esc_attr(item); });

			// Rebuild attribute value
			attribute_value = attribute_value_array.join(' ');

			// If attribute value is not blank, set it, otherwise remove it
			if(attribute_value) {

				obj.attr(attribute_name, attribute_value);

			} else {

				obj.removeAttr(attribute_name);
			}
		}
	}

	// Convert date in string format to JS Date
	$.WS_Form.prototype.get_date = function(input_datetime, input_type_datetime, format_date) {

		if(!input_type_datetime) { input_type_datetime = 'date'; }
		if(!format_date) { format_date = ws_form_settings.date_format; }

		// Process time
		switch(input_type_datetime) {

			case 'time' :

				return new Date('01/01/1970 ' + input_datetime);

			case 'date' :

				input_datetime = input_datetime + ' 00:00:00';
				break;

			case 'week' :
			case 'month' :

				return false;
		}

		// Convert d/m/Y formats to m/d/Y for JavaScript compatibility
		var dm_to_md_date_separator = false;
		switch(format_date) {

			case 'd/m/Y' :

				dm_to_md_date_separator = '/';
				break;

			case 'd.m.Y' :
			case 'j.n.Y' :

				dm_to_md_date_separator = '.';
				break;

			case 'd-m-Y' :

				dm_to_md_date_separator = '-';
				break;
		}

		if(dm_to_md_date_separator !== false) {

			// Split out date and time
			switch(input_type_datetime) {

				case 'datetime-local' :

					var date_time_delimiter_index = input_datetime.indexOf(' ');
					var input_date = input_datetime.substring(0, date_time_delimiter_index);
					var input_time = input_datetime.substring(date_time_delimiter_index);
					break;

				default :

					var input_date = input_datetime;
					var input_time = '';
					break;
			}

			// Convert d/m/Y to m/d/Y
			var date_array = input_date.split(dm_to_md_date_separator);
			if(date_array.length === 3) {

				var d = parseInt(date_array[0], 10),
				m = parseInt(date_array[1], 10),
				y = parseInt(date_array[2], 10);
				var input_date = m + '/' + d + '/' + y;	// Force slashes so Date class works
			}

			// Put it back together again
			input_datetime = input_date + input_time;
		}

		// Strip ordinal indicators
		input_datetime = input_datetime.replace(/(\d+)(st|nd|rd|th)/g, '$1');

		// Change period characters to characters
		input_datetime = this.replace_all(input_datetime, '.', ' ');

		// Remove double spacing
		input_datetime = this.replace_all(input_datetime, '  ', ' ');

		// Convert string to date
		return this.get_new_date(input_datetime);
	}

	// Get new date
	$.WS_Form.prototype.get_new_date = function(input_date) {

		// Modify date for browsers that don't support Y-m-d 00:00:00 formats. Convert - to /.
		if(
			(input_date.charAt(4) == '-') &&
			(input_date.charAt(7) == '-') &&
			(input_date.charAt(10) != 'T') &&
			(input_date.charAt(13) == ':') &&
			!this.date_valid(input_date)
		) {

			// Convert dashes to slashes (Gives same time output but compatible with older browsers)
			input_date = input_date.replace(/-/g, '/');
		}

		// Modify date for browsers that don't support Y-m-d 00:00 am/pm formats. Convert - to /.
		if(
			(input_date.charAt(4) == '-') &&
			!this.date_valid(input_date) &&
			(
				(input_date.toLowerCase().indexOf('am') !== -1) ||
				(input_date.toLowerCase().indexOf('pm') !== -1)
			)
		) {

			// Convert dashes to slashes (Gives same time output but compatible with older browsers)
			input_date = input_date.replace(/-/g, '/');
		}

		return new Date(input_date);
	}

	// Check date is valid
	$.WS_Form.prototype.date_valid = function(input_date) {

		return input_date instanceof Date && !isNaN(input_date.valueOf())
	}

	// Is not a number
	$.WS_Form.prototype.is_not_number = function(input_number) {

		return (

			isNaN(input_number) ||
			(typeof(input_number) === 'boolean') ||
			(input_number === '')
		);
	}

	// Get object_row_id as an array of integers
	$.WS_Form.prototype.get_object_row_id = function(condition) {

		var object_row_id = condition.object_row_id;

		if((typeof(object_row_id) === 'undefined') || (object_row_id == '')) { return false; }

		// Process object_id so values are integers
		if(typeof(object_row_id) === 'object') {

			object_row_id = object_row_id.map(function(id) { return parseInt(id, 10); });
			if(!object_row_id.length) { object_row_id = false; }

		} else {

			object_row_id = (object_row_id !== '') ? [parseInt(object_row_id, 10)] : false;
		}

		return object_row_id;
	}

	// Get nice date by type
	$.WS_Form.prototype.get_date_by_type = function(date, field) {

		if(!date) { return ''; }

		var format_date = this.get_object_meta_value(field, 'format_date', ws_form_settings.date_format);
		if(!format_date) { format_date = ws_form_settings.date_format; }
		var format_time = this.get_object_meta_value(field, 'format_time', ws_form_settings.time_format);
		if(!format_time) { format_time = ws_form_settings.time_format; }

		var input_type_datetime = this.get_object_meta_value(field, 'input_type_datetime', 'date');

		switch(input_type_datetime) {

			case 'datetime-local' :
			case 'date' :

				// Convert to m/d/Y format for JavaScript Date class
				switch(format_date) {

					case 'd/m/Y' :

						var date_time_array = (date.indexOf(' ') !== -1) ? [date.substring(0, date.indexOf(' ')), date.substring(date.indexOf(' ') + 1)] : [date];
						var date_array = date_time_array[0].split('/');
						date = date_array[1] + '/' + date_array[0] + '/' + date_array[2] + ((typeof(date_time_array[1]) !== 'undefined') ? ' ' + date_time_array[1] : '');
						break;

					case 'd-m-Y' :

						var date_time_array = (date.indexOf(' ') !== -1) ? [date.substring(0, date.indexOf(' ')), date.substring(date.indexOf(' ') + 1)] : [date];
						var date_array = date_time_array.split('-');
						date = date_array[1] + '/' + date_array[0] + '/' + date_array[2] + ((typeof(date_time_array[1]) !== 'undefined') ? ' ' + date_time_array[1] : '');
						break;

					case 'd.m.Y' :
					case 'j.n.Y' :

						var date_time_array = (date.indexOf(' ') !== -1) ? [date.substring(0, date.indexOf(' ')), date.substring(date.indexOf(' ') + 1)] : [date];
						var date_array = date_time_array[0].split('.');
						date = date_array[1] + '/' + date_array[0] + '/' + date_array[2] + ((typeof(date_time_array[1]) !== 'undefined') ? ' ' + date_time_array[1] : '');
						break;
				}
		}

		if(input_type_datetime !== 'time') {

			// Check if date is in format of yyyy-mm-dd and does not have a time, if so add time so it is not localized to UTC
			if(
				(date.indexOf('-') === 4) &&
				(date.indexOf(':') === -1)

			) { date += ' 00:00:00'; }
		}

		switch(input_type_datetime) {

			case 'date' :

				date = this.get_new_date(date);
				return this.date_format(date, format_date);

			case 'month' :

				date = this.get_new_date(date);
				return this.date_format(date, 'F Y');

			case 'time' :

				date = this.get_new_date('1970-01-01 ' + date);
				return this.date_format(date, format_time);

			case 'week' :

				date = this.get_new_date(date);
				return this.language('week') + ' ' + this.date_format(date, 'W, Y');

			default :

				date = this.get_new_date(date);
				return this.date_format(date, format_date + ' ' + format_time);
		}
	}

	// Equivalent to PHP's number_format function
	$.WS_Form.prototype.number_format = function(num, decimals, decimal_separator, thousands_separator) {

		// Check num
		if((typeof(num) === 'undefined') || num == null || !isFinite(num)) { return 0; }

		// Check decimals
		if(!decimals) {

			var len = num.toString().split('.').length;
			decimals = len > 1 ? len : 0;
		}

		// Check decimal points
		if(typeof(decimal_separator) === 'undefined') {

			decimal_separator = '.';
		}

		// Check thousands point
		if(typeof(thousands_separator) === 'undefined') {

			thousands_separator = ',';
		}

		// Conver num to floating point and fix it to decimal places (Returns a string)
		num = parseFloat(num).toFixed(decimals);

		// Replace decimal point
		num = num.replace('.', decimal_separator);

		// Split num into array by decimal point
		var num_array = num.split(decimal_separator);

		// Add thousand separators
		num_array[0] = num_array[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_separator);

		// Rejoin string
		num = num_array.join(decimal_separator);

		// Return num as string
		return num;
	}

	// Equivalent to PHP's date function
	$.WS_Form.prototype.date_format = function (date, format) {

		var ws_this = this;

		// Defining locale
		var shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
		var longMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
		var shortDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
		var longDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

		// Defining patterns
		var replaceChars = {

			// Day
			d: function () { var d = this.getDate(); return (d < 10 ? '0' : '') + d },
			D: function () { return shortDays[this.getDay()] },
			j: function () { return this.getDate() },
			l: function () { return longDays[this.getDay()] },
			N: function () { var N = this.getDay(); return (N === 0 ? 7 : N) },
			S: function () { var S = this.getDate(); return (S % 10 === 1 && S !== 11 ? 'st' : (S % 10 === 2 && S !== 12 ? 'nd' : (S % 10 === 3 && S !== 13 ? 'rd' : 'th'))) },
			w: function () { return this.getDay() },
			z: function () { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((this - d) / 86400000) },

			// Week
			W: function () {
				var target = new Date(this.valueOf())
				var dayNr = (this.getDay() + 6) % 7
				target.setDate(target.getDate() - dayNr + 3)
				var firstThursday = target.valueOf()
				target.setMonth(0, 1)
				if (target.getDay() !== 4) {
					target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7)
				}
				var retVal = 1 + Math.ceil((firstThursday - target) / 604800000)

				return (retVal < 10 ? '0' + retVal : retVal)
			},

			// Month
			F: function () { return longMonths[this.getMonth()] },
			m: function () { var m = this.getMonth(); return (m < 9 ? '0' : '') + (m + 1) },
			M: function () { return shortMonths[this.getMonth()] },
			n: function () { return this.getMonth() + 1 },
			t: function () {
				var year = this.getFullYear()
				var nextMonth = this.getMonth() + 1
				if (nextMonth === 12) {
					year = year++
					nextMonth = 0
				}
				return new Date(year, nextMonth, 0).getDate()
			},

			// Year
			L: function () { var L = this.getFullYear(); return (L % 400 === 0 || (L % 100 !== 0 && L % 4 === 0)) },
			o: function () { var d = new Date(this.valueOf()); d.setDate(d.getDate() - ((this.getDay() + 6) % 7) + 3); return d.getFullYear() },
			Y: function () { return this.getFullYear() },
			y: function () { return ('' + this.getFullYear()).substr(2) },

			// Time
			a: function () { return this.getHours() < 12 ? 'am' : 'pm' },
			A: function () { return this.getHours() < 12 ? 'AM' : 'PM' },
			B: function () { return Math.floor((((this.getUTCHours() + 1) % 24) + this.getUTCMinutes() / 60 + this.getUTCSeconds() / 3600) * 1000 / 24) },
			g: function () { return this.getHours() % 12 || 12 },
			G: function () { return this.getHours() },
			h: function () { var h = this.getHours(); return ((h % 12 || 12) < 10 ? '0' : '') + (h % 12 || 12) },
			H: function () { var H = this.getHours(); return (H < 10 ? '0' : '') + H },
			i: function () { var i = this.getMinutes(); return (i < 10 ? '0' : '') + i },
			s: function () { var s = this.getSeconds(); return (s < 10 ? '0' : '') + s },
			v: function () { var v = this.getMilliseconds(); return (v < 10 ? '00' : (v < 100 ? '0' : '')) + v },

			// Timezone
			e: function () { return Intl.DateTimeFormat().resolvedOptions().timeZone },
			I: function () {
				var DST = null
				for (var i = 0; i < 12; ++i) {
					var d = new Date(this.getFullYear(), i, 1)
					var offset = d.getTimezoneOffset()

					if (DST === null) DST = offset
					else if (offset < DST) { DST = offset; break } else if (offset > DST) break
				}
				return (this.getTimezoneOffset() === DST) | 0
			},
			O: function () { var O = this.getTimezoneOffset(); return (-O < 0 ? '-' : '+') + (Math.abs(O / 60) < 10 ? '0' : '') + Math.floor(Math.abs(O / 60)) + (Math.abs(O % 60) === 0 ? '00' : ((Math.abs(O % 60) < 10 ? '0' : '')) + (Math.abs(O % 60))) },
			P: function () { var P = this.getTimezoneOffset(); return (-P < 0 ? '-' : '+') + (Math.abs(P / 60) < 10 ? '0' : '') + Math.floor(Math.abs(P / 60)) + ':' + (Math.abs(P % 60) === 0 ? '00' : ((Math.abs(P % 60) < 10 ? '0' : '')) + (Math.abs(P % 60))) },
			T: function () { var tz = this.toLocaleTimeString(navigator.language, {timeZoneName: 'short'}).split(' '); return tz[tz.length - 1] },
			Z: function () { return -this.getTimezoneOffset() * 60 },

			// Full Date/Time
			c: function () { return ws_this.date_format(date, 'Y-m-d\\TH:i:sP') },
			r: function () { return this.toString() },
			U: function () { return Math.floor(this.getTime() / 1000) }
		}

		return format.replace(/(\\?)(.)/g, function (_, esc, chr) {

			return (esc === '' && replaceChars[chr]) ? replaceChars[chr].call(date) : chr;
		});
	}

})(jQuery);
