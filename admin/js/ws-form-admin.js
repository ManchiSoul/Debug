(function($) {

	'use strict';

	// Set is_admin
	$.WS_Form.prototype.set_is_admin = function() { return true; }

	// One time init for admin page
	$.WS_Form.prototype.init = function() {

		// Set globals
		this.set_globals();

		// Build form
		this.form_build();

		// Sidebar reset
		this.sidebar_reset();

		// Window resizing
		this.window_resize_init();

		// Set CSS root variables
		this.root_css_variables_set();

		// Key down events
		this.keydown_events_init();

		// Intro
		this.intro();

		// Render publish button
		this.published_checksum = this.form.published_checksum;
		this.publish_render(this.form.checksum);

		// Push initial history to stack
		this.history_push({ form: this.form, history: {'date': ws_form_settings.date, 'time': ws_form_settings.time }});

		// Tooltips
		this.tooltips();

		// Variable modal
		this.variable_helper_modal();

		// Styler preview URL
		this.set_preview_url_styler();
	}

	// One time init for partials (Not edit)
	$.WS_Form.prototype.init_partial = function() {

		// Set CSS root variables
		this.root_css_variables_set();
	}

	// Set CSS root variables
	$.WS_Form.prototype.root_css_variables_set = function() {

		if($('#adminmenuwrap')) {

			var adminmenuwrap_width = $('#adminmenuwrap').width();
			$(':root').css('--wp-sidebar-width', adminmenuwrap_width + 'px');
		}
	}

	// Key down events
	$.WS_Form.prototype.keydown_events_init = function() {

		// Key down events
		$(document).on('keydown', function(e) {

			var keyCode = e.keyCode || e.which;

			// Command (Mac) / Ctrl (PC)
			var ctrl = ((typeof(e.metaKey) !== 'undefined') ? e.metaKey : false) || ((typeof(e.ctrlKey) !== 'undefined') ? e.ctrlKey : false);

			if(typeof($.WS_Form.this.keydown[keyCode]) === 'object') {

				var keydown = $.WS_Form.this.keydown[keyCode];

				if((keydown.ctrl_key && ctrl) || (!keydown.ctrl_key)) {

					e.preventDefault();
					keydown.function();
				}
			}
		});
	}

	// Window - Resize - Init
	$.WS_Form.prototype.window_resize_init = function() {

		$(window).on('resize', function() {

			// Toolbox sidebar reopened if it was closed and screen goes beyond mobile cut-off
			if(	(($('#wsf-sidebars').attr('data-current') == 'toolbox') || ($('#wsf-sidebars').attr('data-current') == undefined)) &&
				$('#wsf-sidebar-toolbox').hasClass('wsf-sidebar-closed') &&
				window.matchMedia('(min-width: ' + $.WS_Form.this.mobile_min_width + ')').matches
			) {

				// Sidebar - Toolbox - Open
				$.WS_Form.this.sidebar_open('toolbox');
			}
		});
	}

	// Set global variables once for performance
	$.WS_Form.prototype.set_globals = function(framework_override, admin_public) {

		// Get framework ID
		this.framework_id = (typeof(framework_override) !== 'undefined' ? framework_override : $.WS_Form.settings_plugin.framework);

		// Get framework settings
		this.framework = $.WS_Form.frameworks.types[this.framework_id];

		// Get current framework for tabs
		this.framework_fields = this.framework['fields'][typeof(admin_public) !== 'undefined' ? admin_public : 'admin'];

		// Set mobile breakpoint size
		this.mobile_min_width = '851px';

		// Build conditional highlight array
		this.conditional_icons_build(false);
	}

	// Intro
	$.WS_Form.prototype.intro = function() {

		// Intro
		if(typeof(introJs) !== 'function') { return; }

		$('body').addClass('wsf-intro');

		var ws_this = this;

		// Request intro
		$.WS_Form.this.api_call('helper/intro/', 'GET', false, function(hint_steps_config) {

			// Loader off
			$.WS_Form.this.loader_off();

			if(typeof(hint_steps_config) !== 'object') { return; }

			// Build hint steps
			var hints = [];
			var hints_sidebar_open = [];
			var hints_button_url = [];
			for(var hint_config_index in hint_steps_config) {

				if(!hint_steps_config.hasOwnProperty(hint_config_index)) { continue; }

				var hint_step_config = hint_steps_config[hint_config_index];
				var hint_step = {}

				if(typeof(hint_step_config.hint) !== 'undefined') { hint_step.hint = hint_step_config.hint; }
				if(typeof(hint_step_config.position) !== 'undefined') { hint_step.hintPosition = hint_step_config.position; }
				if(typeof(hint_step_config.element) !== 'undefined') { hint_step.element = $(hint_step_config.element)[0]; }
				if(typeof(hint_step_config.sidebar_open) !== 'undefined') { hints_sidebar_open[hint_config_index] = hint_step_config.sidebar_open; }
				if(typeof(hint_step_config.button_url) !== 'undefined') { hints_button_url[hint_config_index] = hint_step_config.button_url; }

				hints.push(hint_step);
			}

			var intro = introJs();

			intro.setOptions({

				hints: hints
			});

			// On hint click
			intro.onhintclick(function(hint_element, item, step_id) {

				if(typeof(hints_sidebar_open[step_id]) !== 'undefined') {

					var id = hints_sidebar_open[step_id];

					// Open
					var meta_key_open_function = 'sidebar_' + id + '_open';
					if(typeof(window[meta_key_open_function]) === 'function') {

						// Get dom objects
						var obj_outer = $('#wsf-sidebar-' + id);
						var obj_inner = $('.wsf-sidebar-inner', obj_outer);

						window[meta_key_open_function]($.WS_Form.this, obj_inner, $(this));

					} else {

						// Open
						$.WS_Form.this.sidebar_open(id);
					}
				}

				if(typeof(hints_button_url[step_id]) !== 'undefined') {

					var url = hints_button_url[step_id];

					setTimeout(function() {

						$('.introjs-tooltiptext').append('&nbsp;<a href="' + ws_this.esc_attr(url) + '" class="introjs-button" role="button" target="_blank">' + ws_this.language('intro_learn_more') + '</a>');
						$('.introjs-tooltiptext').append('<div data-action="wsf-intro-skip" class="wsf-intro-skip">' + ws_this.language('intro_skip') + '</div>');

						$('[data-action="wsf-intro-skip"]', $('.introjs-tooltiptext')).on('click', function() {

							introJs().hideHints();

							$('body').removeClass('wsf-intro');
						});

					}, 50);
				}

			});

			// On intro complete
			intro.oncomplete(function() {

				$(body).removeClass('wsf-intro');
			});

			intro.addHints();
		});
	}

	// Render any interface elements that rely on the form object (Also called on a form push)
	$.WS_Form.prototype.form_render = function() {

		// Process conditional icons
		this.conditional_highlight_row_index = false;

		this.conditional_icons_build(false);
		// Form name
		$('[data-action="wsf-form-label"]').val(this.form.label);

		if(!this.form_interface) {

			// Sidebars
			this.sidebars_render();

			// Render the breakpoints
			this.breakpoints();

			// Form - Label - Change
			$('[data-action="wsf-form-label"]').on('change', function(e) {

				// If change occurred as a result of someone changing the field (and not a jQuery val update)
				if(e.originalEvent) {

					// Get label
					var label = $(this).val();
					if(label == '') { label = $.WS_Form.this.get_label_default('form'); $(this).val(label);}

					// Change its value
					$.WS_Form.this.form.label = label;

					// Push the change to the API
					$.WS_Form.this.form_put();
				}
			});

			// Form - Label - Keyup
			$('[data-action="wsf-form-label"]').on('keydown input', function(e) {

				var keyCode = e.keyCode || e.which;

				if(keyCode === 13) {

					e.preventDefault();

					$(this).trigger('blur');

				} else {

					$('#wsf-sidebar-form [name="label"]').val($(this).val());
				}
			});

			// Form - Label - Focus
			$('[data-action="wsf-form-label"]').on('focus', function(e) {

				$('[data-action="wsf-label-save"]').show();
			});

			// Form - Label - Blur
			$('[data-action="wsf-form-label"]').on('blur', function(e) {

				$('[data-action="wsf-label-save"]').hide();
			});

			// Publish
			$('[data-action="wsf-publish"]').on('click', function() { $.WS_Form.this.form_publish(); });

			// Preview
			if($.WS_Form.settings_plugin.helper_live_preview) {

				$('[data-action="wsf-preview"]').on('click', function(e) { $.WS_Form.this.form_preview(e, $(this)); });
			}

			// Upload form
			$('[data-action="wsf-form-upload"]').on('click', function() {

				// Remember object
				$.WS_Form.this.upload_obj = $.WS_Form.this.form_obj;

				// Click file input
				$('#wsf-object-upload-file').val('').trigger('click');
			});

			// Download
			$('[data-action="wsf-form-download"]').on('click', function() { $.WS_Form.this.form_download(); });

			// Undo
			$('[data-action="wsf-undo"]').on('click', function() { $.WS_Form.this.undo(); });

			// Redo
			$('[data-action="wsf-redo"]').on('click', function() { $.WS_Form.this.redo(); });

			// Event - Mouse up
			this.mouseup_mode = false;
			$(document).on('mouseup', function() {

				switch($.WS_Form.this.mouseup_mode) {

					case 'column_size' :

						$.WS_Form.this.column_size_change_release();
						break;

					case 'offset' :

						$.WS_Form.this.offset_change_release();
						break;
				}
			});

			// Object upload
			$('#wsf-object-upload-file').on('change', function() {

				// Get files
				var files = $('#wsf-object-upload-file').prop("files");

				if(files.length > 0) {

					var form_upload_window = $('> .wsf-object-upload-json-window', $.WS_Form.this.upload_obj);
					form_upload_window.show();

					$.WS_Form.this.object_upload_json(files, form_upload_window, $.WS_Form.this.upload_obj, function(response) {

						// Redraw form
						$.WS_Form.this.form_build();

					}, function() {

						$('> .wsf-object-upload-json-window', $.WS_Form.this.upload_obj).hide();

					}, true);
				}
			});

			this.form_interface = true;
		}

		// Groups - Tabs - Add
		var add_group_tab = '<li class="wsf-group-add wsf-ui-cancel" role="tab"><button' + this.tooltip(this.language('add_group'), 'top-center') + ' tabindex="-1">' + this.svg('group') + this.svg('plus') + '</button></li>';

		$('.wsf-group-tabs').append(add_group_tab);
		$('.wsf-group-add button').on('click', function() { $.WS_Form.this.group_post($(this)); });

		// Groups - Tabs - Initialize
		this.group_tabs_init();

		// Update undo/redo update button
		this.undo_redo_update();

		// Build groups
		$('.wsf-group').each(function() {

			$.WS_Form.this.group_render($(this));
		});

		// Breakpoint buttons
		this.breakpoint_buttons();

		// Initialize draggable
		this.init_ui();

		// Form upload
		this.form_obj.append('<div class="wsf-object-upload-json-window"><div class="wsf-object-upload-json-window-content"><h1>' + this.language('drop_zone_form') + '</h1><div class="wsf-uploads"></div></div></div>');

		// Drag enter
		this.form_obj.on('dragenter', function (e) {

			e.stopPropagation();
			e.preventDefault();

			// Check dragged object is a file
			if(!$.WS_Form.this.drag_is_file(e)) { return; }

			$('.wsf-object-upload-json-window', $(this)).show();
		});

		// Drag over
		$('.wsf-object-upload-json-window', this.form_obj).on('dragover', function (e) {

			e.stopPropagation();
			e.preventDefault();
		});

		// Drop
		$('.wsf-object-upload-json-window', this.form_obj).on('drop', function (e) {

			e.preventDefault();

			var files = e.originalEvent.dataTransfer.files;
			$.WS_Form.this.object_upload_json(files, $(this), null, function(response) {

				// Redraw form
				$.WS_Form.this.form_build();

			}, function() {

				$('.wsf-object-upload-json-window', $.WS_Form.this.form_obj).hide();

			}, true);
		});

		// Drag leave
		$('.wsf-object-upload-json-window', this.form_obj).on('dragleave', function (e) {

			$('.wsf-object-upload-json-window', $.WS_Form.this.form_obj).hide();
		});

		// Check multiple field
		this.field_check_multiple();

		// Show hidden elements
		$('.wsf-loading-hidden').show();

		// Set min-height to avoid scrolling issues
		$('.wsf-fields, .wsf-sections').on('mousedown touchstart', function() {

			var height = $(this).height();
			$(this).css('min-height', height + 'px');

		}).on('mouseup touchend', function() {

			$('.wsf-fields, .wsf-sections').css('min-height', 'auto');
		});
	}

	// API - Form - PUT
	$.WS_Form.prototype.form_put = function(full, form_build, history_suppress, preview_update, complete) {

		if(typeof(full) === 'undefined') { full = false; }
		if(typeof(form_build) === 'undefined') { form_build = true; }
		if(typeof(history_suppress) === 'undefined') { history_suppress = false; }

		var form = $.extend(true, {}, this.form); // Deep clone

		// If not doing a full push, we'll remove the groups key to provide a smaller cut down form
		if(!full) { delete form.groups; }

		// Render interface
		if(form_build) { $.WS_Form.this.form_build(); }

		// Suppress history?
		if(history_suppress) { form.history_suppress = 'on'; }

		// Call AJAX request
		this.api_call('form/' + this.form_id + (full ? '/full' : '') + '/put/', 'POST', {'form': form}, function(response) {

			if(typeof(complete) !== 'undefined') { complete(); }

			// Update preview window
			if(preview_update) {

				$.WS_Form.this.form_preview_update();
			}

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Form - Publish
	$.WS_Form.prototype.form_publish = function() {

		// Loader on
		this.loader_on();

		// Save any updates
		this.object_save_changes();

		// Reset sidebar
		this.sidebar_reset();

		// Call AJAX request
		this.api_call('form/' + this.form_id + '/publish/', 'POST', false, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Form - Preview
	$.WS_Form.prototype.form_preview = function(e, button_obj) {

		// Prevent default
		e.preventDefault();

		// Save any updates
		this.object_save_changes();

		// Open preview window
		if(
			!this.preview_window ||
			this.preview_window.closed
		) {

			this.preview_window = window.open(button_obj.attr('href'), 'wsf-preview-' + this.form_id);

			$(window).on('beforeunload', function() {

				$.WS_Form.this.preview_window.close();
				this.preview_window = undefined;
			});
		}

		// Focus
		this.preview_window.focus();
	}

	// Form - Preview - Update
	$.WS_Form.prototype.form_preview_update = function() {

		try {

			if(
				(typeof(this.preview_window) !== 'undefined') &&
				(typeof(this.preview_window.location) !== 'undefined') &&
				(typeof(this.preview_window.location.reload) !== 'undefined') &&
				$.WS_Form.settings_plugin.helper_live_preview
			) {

				this.preview_window.location.reload();
			}

		} catch(e) {

			this.preview_window = undefined;
		}
	}

	// Object - Uploader
	$.WS_Form.prototype.object_upload_json = function(files, upload_json_window_obj, obj, success_callback, error_callback, show_confirm) {

		if(typeof(show_confirm) === 'undefined') { show_confirm = false; }

		// Check file count
		if(files.length == 0) {

			error_callback();

			return false;
		}

		switch(obj) {

			case 'style' :

				var object = 'style';
				var object_id = 0;
				break;

			default :

				var object = (obj === null) ? 'form' : $.WS_Form.this.get_object_type(obj);
				var object_id = (obj === null) ? $.WS_Form.this.form_id : obj.attr('data-id');
		}

		// Confirm upload
		if((object === 'form') && show_confirm && !confirm($.WS_Form.this.language(object + '_import_confirm'))) {

			upload_json_window_obj.hide();

			error_callback();

			return;
		}

		// Hide H1
		$('h1', upload_json_window_obj).hide();

		// Create form data
		var data = new FormData();

		switch(object) {

			case 'style' :
				break;

			default :

				data.append('id', this.form_id);
				data.append(object + '_id', object_id);
		}

		data.append('file', files[0]);
		data.append(ws_form_settings.wsf_nonce_field_name, ws_form_settings.wsf_nonce);

		// Additional data attributes
		switch(object) {

			case 'group' :

				// Get next sibling ID (0 = Last or only element in form)
				var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;
				data.append('next_sibling_id', next_sibling_id);
				break;

			case 'section' :

				// Get next sibling ID (0 = Last or only element in form)
				var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;
				data.append('next_sibling_id', next_sibling_id);

				// Group ID
				var group_id = obj.closest('.wsf-group').attr('data-id');
				data.append('group_id', group_id);
				break;
		}

		// Reset sidebar
		this.sidebar_reset();

		// Create status bar for this file
		var status_bar = new this.upload_status_bar(this, upload_json_window_obj)

		// Populate status_bar
		status_bar.populate(files[0].name, files[0].size);

		// Build URL
		switch(object) {

			case 'style' :

				var url = ws_form_settings.url_ajax + 'style/upload/json';
				break;

			default :

				var url = ws_form_settings.url_ajax + object + '/' + object_id + '/upload/json';
		}

		var jqXHR = $.ajax({

			beforeSend: function(xhr) {

				xhr.setRequestHeader('X-WP-Nonce', ws_form_settings.x_wp_nonce);
			},

			xhr: function() {

				// Upload progress
				var xhrobj = $.ajaxSettings.xhr();
				if (xhrobj.upload) {

					xhrobj.upload.addEventListener('progress', function(e) {

						var percent = 0;
						var position = e.loaded || e.position;
						var total = e.total;
						if (e.lengthComputable) {
							percent = Math.ceil(position / total * 100);
						}

						status_bar.set_progress(percent);

					}, false);
				}

				return xhrobj;
			},

			url: url,
			type: 'POST',
			contentType: false,
			processData: false,
			cache: false,
			data: data,

			success: function(response) {

				// Set progress bar to 100%
				status_bar.set_progress(100);

				if(typeof(response.form) !== 'undefined') {

					// If full form returned by API, load it
					if((typeof(response.form_full) !== 'undefined') && response.form_full) {

						// Replace this form with response form
						$.WS_Form.this.form = response.form;

						switch(object) {

							case 'form' :

								// Reset tab index
								var tab_index = $.WS_Form.this.get_object_meta_value($.WS_Form.this.form, 'tab_index', 0, true);
								if(tab_index != 0) {

									$.WS_Form.this.set_object_meta_value($.WS_Form.this.form, 'tab_index', 0);
									$.WS_Form.this.group_tab_index_save(0);
								}

								break;
						}

						// Build data cache
						$.WS_Form.this.data_cache_build();

						// Process checksum
						$.WS_Form.this.api_call_process_checksum(response);

						// Re-render breakpoints
						$.WS_Form.this.breakpoints();
					}
				}

				// Save if we are using undo function (Called after success_callback to ensure response returned is in caches)
				if(typeof(response.history) !== 'undefined') {

					// Push to history stack
					$.WS_Form.this.history_push(response);
				}

				// Call success script
				if(typeof(success_callback) === 'function') { success_callback(response); }
			},

			error: function(response) {

				// Process error
				$.WS_Form.this.api_call_error_handler(response, url, error_callback);
			}
		});

		status_bar.set_abort(jqXHR);
	}

	// Form - Publish
	$.WS_Form.prototype.form_download = function() {

		// Build downloader
		var downloader_html = '<form id="wsf-form-downloader" action="' + this.esc_attr(ws_form_settings.url_ajax + 'form/' + this.form_id + '/download/json') + '" method="post">';

		downloader_html += '<input type="hidden" name="form_id" value="' + this.esc_attr(this.form_id) + '" />';
		downloader_html += '<input type="hidden" name="_wpnonce" value="' + this.esc_attr(ws_form_settings.x_wp_nonce) + '" />';
		downloader_html += '<input type="hidden" name="' + this.esc_attr(ws_form_settings.wsf_nonce_field_name) + '" value="' + this.esc_attr(ws_form_settings.wsf_nonce) + '" />';

		downloader_html += '</form>';

		// Inject into body
		var downloader = $('body').append(downloader_html);

		// Submit
		$('#wsf-form-downloader').trigger('submit');

		// Remove
		$('#wsf-form-downloader').remove();
	}

	// Group init
	$.WS_Form.prototype.group_render = function(obj) {

		// Get group ID
		var group_id = obj.attr('data-id');

		// Get group data
		var this_group_data = this.group_data_cache[group_id];

		// Add settings to each group tab
		var group_tab_obj = $('.wsf-group-tab[data-id="' + this.esc_selector(group_id) + '"]');

		// Get group
		var group = this.group_data_cache[group_id];

		// Read group settings
		var group_hidden = this.get_object_meta_value(group, 'hidden', false);
		var group_conditional = (this.conditional_icons_array['group'].indexOf(parseInt(group_id, 10)) !== -1);

		// Icon count
		var group_icon_count = 0;
		if(group_hidden) { group_icon_count++; }
		if(group_conditional) { group_icon_count++; }

		// Apply icon count class
		var group_label = $('.wsf-group-label', group_tab_obj);
		for(var group_icon_count_index = 0; group_icon_count_index < 1; group_icon_count_index++) {

			group_label.removeClass('wsf-group-icon-count-' + group_icon_count_index);
		}
		group_label.addClass('wsf-group-icon-count-' + group_icon_count);

		// Icons
		if(group_hidden) { $('.wsf-group-hidden', group_tab_obj).show(); } else { $('.wsf-group-hidden', group_tab_obj).hide(); }
		if(group_conditional) { $('.wsf-group-conditional', group_tab_obj).show(); } else { $('.wsf-group-conditional', group_tab_obj).hide(); }

		// Initialize group
		if(!obj.find('.wsf-group-header').length) {

			// Add column helper class
			if($.WS_Form.settings_plugin.helper_columns == 'on') { obj.addClass('wsf-column-helper'); }

			// Build group header
			var group_header_html = '<div class="wsf-group-header">';

			// Get settings HTML
			group_header_html += this.settings_html('group', group_id, true);

			group_header_html += '</div>';

			// Add group HTML
			obj.prepend(group_header_html);

			// Settings - Events
			this.settings_events(obj, 'group');

			// Hover event
			this.hover_events(group_tab_obj, 'group', group_id);
			this.hover_events(obj, 'group', group_id);
			// Drag sections here
			var section_blank_html = '<li class="wsf-section-blank"><div>' + this.language('blank_section') + '</div></li>';
			obj.find('.wsf-sections').prepend(section_blank_html);

			// Section interface HTML
			var section_interface_html = '<div class="wsf-section-add" data-group-id="' + this.esc_attr(group_id) + '"><button' + this.tooltip(this.language('add_section'), 'left') + '>' + this.svg('section') + this.svg('plus') + '</button></div>';

			// Upload
			section_interface_html += '<div class="wsf-object-upload-json-window"><div class="wsf-object-upload-json-window-content"><div class="wsf-uploads"></div></div></div>';

			// Add section interface
			obj.append(section_interface_html);
			$('.wsf-section-add[data-group-id="' + this.esc_selector(group_id) + '"] button').on('click', function() {

				$.WS_Form.this.section_post($(this));
			});

			// Initialize label
			this.label_init(group_tab_obj);

			// Build sections
			obj.find(".wsf-section").each(function() {

				$.WS_Form.this.section_render($(this), false);
			})
		}
	}

	// Group - Count
	$.WS_Form.prototype.group_tabs_count = function() {

		var tabs_obj = $('.wsf-group-tabs li.wsf-group-tab');

		var group_count = tabs_obj.length;

		if(group_count === 1) {

			tabs_obj.first().addClass('wsf-disabled');

		} else {

			tabs_obj.first().removeClass('wsf-disabled');
		}

		$('#wsf-sidebars').attr('data-group-count', group_count);
	}

	// Group - Tabs - Init
	$.WS_Form.prototype.group_tabs_init = function(index) {

		if(typeof(index) === 'undefined') {

			// If index not specified, use the form tab_index meta value, or use value of 0 and create meta
			var index = this.get_object_meta_value(this.form, 'tab_index', 0, true);

		} else {

			// Save tab index
			$.WS_Form.this.group_tab_index_save(index);
		}

		// Modifying jQuery tabs to disable keydown events
		$.widget('ui.tabs', $.ui.tabs, {

			options: { keyboard: true },

			_tabKeydown: function(e) {

				if(this.options.keyboard) {

					this._super('_tabKeydown');

				} else {

					return false;
				}
			}
		});

		// Destroy tabs (Ensures subsequent calls work)
		if($('#' + this.form_obj_id).hasClass('ui-tabs')) {

			$('#' + this.form_obj_id).tabs('destroy');
		}
		$('#' + this.form_obj_id).tabs({

			active: index,

			activate: function(e, ui) {

				// Loader on
				$.WS_Form.this.loader_on();

				// Save tab index
				$.WS_Form.this.group_tab_index_save(ui.newTab.index());
			}
		});

		// Count tabs
		this.group_tabs_count();
	}

	// Group - Tabs - Index save
	$.WS_Form.prototype.group_tab_index_save = function(index) {

		// Store tab index to form meta data
		this.set_object_meta_value(this.form, 'tab_index', index);

		// Push tab_index to API (Suppress history)
		this.form_put(false, false, true, false);
	}

	// API - Group - POST
	$.WS_Form.prototype.group_post = function(obj) {

		// Loader on
		this.loader_on();

		// Pre-save current tab index to form meta (it will have changed because of the add)
		var tab_obj_index = obj.index();	// Tab index of '+', this index would be replaced with new group tab
		this.set_object_meta_value(this.form, 'tab_index', tab_obj_index);

		// Call AJAX request
		$.WS_Form.this.api_call('group/', 'POST', false, function(response) {

			// Get new group ID
			var group = $.extend(true, {}, response.data);
			var group_id = group.id;

			// Get HTML for group (This also adds the group to the group cache)
			var group_html = $.WS_Form.this.get_group_html(group);

			// Add HTML to form
			$('#wsf-form .wsf-groups').append(group_html);

			var group_obj = $('.wsf-group[data-id="' + $.WS_Form.this.esc_selector(group_id) + '"]');

			// Add group to tabs
			var tab_html = $.WS_Form.this.get_tab_html(group);
			$('.wsf-group-tabs li.wsf-group-add').before(tab_html);

			// Insert new group tab HTML after tab for current object
			var new_tab_obj = $('.wsf-group-tab[data-id="' + $.WS_Form.this.esc_selector(group_id) + '"]');

			// Initialize tabs
			$.WS_Form.this.group_tabs_init(new_tab_obj.index());

			// Render group
			$.WS_Form.this.group_render(group_obj);

			// Trigger label edit
			$('input', new_tab_obj).first().trigger('dblclick');

			// Initialize UI
			$.WS_Form.this.init_ui();

			// Loader
			$.WS_Form.this.loader_off();

			// Count tabs
			$.WS_Form.this.group_tabs_count();
		});
	}

	// API - Group - PUT - SORT INDEX
	$.WS_Form.prototype.group_put_sort_index = function(obj) {

		// Get currently active tab index
		var tab_index = $('.wsf-group-tabs li.ui-tabs-active').index();

		// Store tab index to form meta data
		this.set_object_meta_value(this.form, 'tab_index', tab_index);

		// Get next sibling ID (0 = Last or only element in form)
		var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;
		if(this.next_sibling_id_old == next_sibling_id) { return false; }

		// Loader on
		this.loader_on();

		// Get group ID
		var group_id = obj.attr('data-id');

		// Build request parameters
		var params = {

			next_sibling_id: next_sibling_id
		};

		// Call AJAX request
		this.api_call('group/' + group_id + '/sort-index/', 'POST', params, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// API - Section - PUT - CLONE
	$.WS_Form.prototype.group_put_clone = function(obj) {

		// Loader on
		this.loader_on();

		// Read data attributes
		var group_id = obj.attr('data-id');

		// Get associated tab
		var obj_tab = $('.wsf-group-tab[data-id="' + this.esc_selector(group_id) + '"]');

		// Get tab index
		var tab_obj_index = obj_tab.index();

		// Pre-save current tab index to form meta (it will have changed because of the add)
		this.set_object_meta_value(this.form, 'tab_index', tab_obj_index + 1);

		// Get next sibling ID (0 = Last or only element in group)
		var next_sibling_id = (typeof(obj_tab.next().attr('data-id')) !== 'undefined') ? obj_tab.next().attr('data-id') : 0;

		// Build request parameters
		var params = {

			next_sibling_id: next_sibling_id
		};

		// Call AJAX request
		var call_obj = obj;
		this.api_call('group/' + group_id + '/clone/', 'POST', params, function(response) {

			// Get group ID
			var group_id_new = response.data.id;

			// Get group tab HTML
			var group_tab_html = $.WS_Form.this.get_tab_html(response.data);

			// Insert new group tab HTML after tab for current object
			var new_tab_obj = $(group_tab_html).insertAfter($('.wsf-group-tab[data-id="' + $.WS_Form.this.esc_selector(group_id) + '"]'));

			// Get group HTML (Stores it to cache)
			var group_html = $.WS_Form.this.get_group_html(response.data);

			// Insert new group HTML after obj
			$(group_html).insertAfter(call_obj);

			// Get new group object
			var new_obj = $('.wsf-group[data-id="' + $.WS_Form.this.esc_selector(group_id_new) + '"]');

			// Build group
			$.WS_Form.this.group_render(new_obj);

			// Groups - Tabs - Initialize
			$.WS_Form.this.group_tabs_init(new_tab_obj.index());

			// Update blank fields
			$.WS_Form.this.object_blank_update(new_obj);

			// Trigger label edit
			$('input', new_tab_obj).first().trigger('dblclick');

			// Initialize draggable elements to work with new section
			$.WS_Form.this.init_ui();

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Section - Render
	$.WS_Form.prototype.section_render = function(obj) {

		// Get section ID
		var section_id = obj.attr('data-id');

		// Get section
		var section = this.section_data_cache[section_id];

		// Read section settings
		var section_hidden = this.get_object_meta_value(section, 'hidden_section', false);
		var section_disabled = this.get_object_meta_value(section, 'disabled_section', false);
		var section_repeatable = this.get_object_meta_value(section, 'section_repeatable', false);
		var section_conditional = (this.conditional_icons_array['section'].indexOf(parseInt(section_id, 10)) !== -1);
		// Icon count
		var section_icon_count = 0;
		if(section_hidden) { section_icon_count++; }
		if(section_disabled) { section_icon_count++; }
		if(section_repeatable) { section_icon_count++; }
		if(section_conditional) { section_icon_count++; }
		// Apply icon count class
		var section_label = $('.wsf-section-label', obj);
		for(var section_icon_count_index = 0; section_icon_count_index < 4; section_icon_count_index++) {

			section_label.removeClass('wsf-section-icon-count-' + section_icon_count_index);
		}
		section_label.addClass('wsf-section-icon-count-' + section_icon_count);

		// Hidden
		if(section_hidden) { $('.wsf-section-hidden', obj).show(); } else { $('.wsf-section-hidden', obj).hide(); }

		// Disabled
		if(section_disabled) { $('.wsf-section-disabled', obj).show(); } else { $('.wsf-section-disabled', obj).hide(); }

		// Repeatable
		if(section_repeatable) { $('.wsf-section-repeatable', obj).show(); } else { $('.wsf-section-repeatable', obj).hide(); }

		// Conditional
		if(section_conditional) { $('.wsf-section-conditional', obj).show(); } else { $('.wsf-section-conditional', obj).hide(); }

		// Add column helper class
		if($.WS_Form.settings_plugin.helper_columns == 'on') { obj.addClass('wsf-column-helper'); }

		// Settings
		if(obj.find('.wsf-settings-section').length == 0) {

			// Settings
			var section_html = this.settings_html('section', section_id, true);

			// Resize
			section_html += this.column_size_change_html();

			// Offset
			section_html += this.offset_change_html();

			// Upload
			section_html += '<div class="wsf-object-upload-json-window"><div class="wsf-object-upload-json-window-content"><div class="wsf-uploads"></div></div></div>';

			// Add section HTML
			obj.append(section_html);

			// Settings - Events
			this.settings_events(obj, 'section');

			// Hover event
			this.hover_events(obj, 'section', section_id);
			// Drag fields here
			var field_blank_html = '<li class="wsf-field-blank"><div>' + this.language('blank_field') + '</div></li>';
			obj.find('.wsf-fields').prepend(field_blank_html);

			// Column size - Change
			this.column_size_change_init(obj);

			// Offset - Change
			this.offset_change_init(obj);

			// Initialize label
			this.label_init(obj);
		}

		// Build fields
		obj.find('li.wsf-field-wrapper[data-id]').each(function() {

			$.WS_Form.this.field_render($(this));
		})
	}

	// API - Section - POST
	$.WS_Form.prototype.section_post = function(obj) {

		// Loader on
		this.loader_on();

		// Get group ID
		var group_id = obj.closest('.wsf-group').attr('data-id');

		// Build request parameters
		var params = {

			group_id: group_id
		};

		// Call AJAX request
		$.WS_Form.this.api_call('section/', 'POST', params, function(response) {

			// Get new section ID
			var section = $.extend(true, {}, response.data);
			var section_id = section.id;

			// Get HTML for section (This also adds the section to the section cache)
			var section_html = $.WS_Form.this.get_section_html(section);

			// Add HTML to section UL
			$('.wsf-sections[data-id="' + $.WS_Form.this.esc_selector(group_id) + '"]').append(section_html);

			var section_obj = $('.wsf-section[data-id="' + $.WS_Form.this.esc_selector(section_id) + '"]');

			// Render section
			$.WS_Form.this.section_render(section_obj);

			// Trigger label edit
			$('input', section_obj).first().trigger('dblclick');

			// Initialize draggable elements to work with new section
			$.WS_Form.this.init_ui();

			// Loader
			$.WS_Form.this.loader_off();
		});
	}

	// API - Templates - Section - POST
	$.WS_Form.prototype.template_section_post = function(obj) {

		// Loader on
		this.loader_on();

		// Template ID
		var template_id = obj.attr('data-id');

		// Get group ID
		var group_id = obj.closest('.wsf-group').attr('data-id');

		// Get next sibling ID (0 = Last or only element in section)
		var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;

		// Build request parameters
		var params = {

			group_id: 			group_id,
			next_sibling_id: 	next_sibling_id,
			template_id: 		template_id
		};

		// Call AJAX request
		$.WS_Form.this.api_call('section/template/', 'POST', params, function(response) {

			// If full form returned by API, load it
			if(response.form_full) {

				$.WS_Form.this.form = response.form;

				// Build data cache
				$.WS_Form.this.data_cache_build();

				// Process checksum
				$.WS_Form.this.api_call_process_checksum(response);

				// Render form
				$.WS_Form.this.form_build();
			}

			// Loader
			$.WS_Form.this.loader_off();
		});
	}

	// API - Section - PUT - SORT INDEX
	$.WS_Form.prototype.section_put_sort_index = function(obj) {

		// Get next sibling ID (0 = Last or only element in form)
		var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;

		// Get group_id section has been dragged to
		var group_id = obj.closest('.wsf-group').attr('data-id');

		// Check if it moved
		if((this.next_sibling_id_old == next_sibling_id) && (this.group_id_old == group_id)) { return false; }

		// Loader on
		this.loader_on();

		// Get section ID
		var section_id = obj.attr('data-id');

		// Build request parameters
		var params = {

			group_id:			group_id,
			next_sibling_id:	next_sibling_id
		};

		// Call AJAX request
		$.WS_Form.this.api_call('section/' + section_id + '/sort-index/', 'POST', params, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// API - Section - PUT - CLONE
	$.WS_Form.prototype.section_put_clone = function(obj) {

		// Loader on
		this.loader_on();

		// Read data attributes
		var section_id = obj.attr('data-id');

		// Get next sibling ID (0 = Last or only element in section)
		var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;

		// Build request parameters
		var params = {

			next_sibling_id: next_sibling_id
		};

		// Call AJAX request
		var call_obj = obj;
		this.api_call('section/' + section_id + '/clone/', 'POST', params, function(response) {

			// Get section ID
			var section_id = response.data.id;

			// Get section HTML (Stores it to cache)
			var section_html = $.WS_Form.this.get_section_html(response.data);

			// Insert new section HTML after obj
			$(section_html).insertAfter(call_obj);

			// Get new section object
			var new_obj = $('.wsf-section[data-id="' + $.WS_Form.this.esc_selector(section_id) + '"]');

			// Build section
			$.WS_Form.this.section_render(new_obj);

			// Trigger label edit
			$('input', new_obj).first().trigger('dblclick');

			// Update blank fields
			$.WS_Form.this.object_blank_update(new_obj);

			// Initialize draggable elements to work with new section
			$.WS_Form.this.init_ui();

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Build field
	$.WS_Form.prototype.field_render = function(obj, field) {

		if(typeof(field) === 'undefined') {

			var field_id = obj.attr('data-id');
			var field = this.field_data_cache[field_id];

		} else {

			var field_id = field.id;
		}

		// Get column class array
		var class_array = this.column_class_array(field);
		if(obj.hasClass('wsf-editing')) { class_array.push('wsf-editing'); }
		if(obj.hasClass('wsf-saving')) { class_array.push('wsf-saving'); }
		var class_string = 'wsf-field-wrapper' + (class_array.length ? ' ' + class_array.join(' ') : '');
		obj.attr('class', class_string);

		// Get field type
		var field_type = field.type;

		// Get field data
		var field_type_config = $.WS_Form.field_type_cache[field_type];

		// Variables for rendering field
		var this_field_label = field.label;

		if(typeof(field_type_config) !== 'undefined') {

			var field_type_config_label = field_type_config.label;
			var field_type_config_icon = field_type_config.icon;
			var field_type_config_has_label = (typeof(field_type_config.mask_field_label) !== 'undefined');
			var field_type_config_has_mask_preview = (typeof(field_type_config.mask_preview) !== 'undefined');
			var field_type_config_multiple = (typeof(field_type_config.multiple) !== 'undefined') ? field_type_config.multiple : true;
			var field_type_config_admin_hide_id = (typeof(field_type_config.admin_hide_id) !== 'undefined') ? field_type_config.admin_hide_id : false;
			var field_type_config_mask_wrappers_drop = (typeof(field_type_config.mask_wrappers_drop) !== 'undefined') ? field_type_config.mask_wrappers_drop : false;

		} else {

			var field_type_config_label = this.language('error_field_type_unknown');
			var field_type_config_icon = this.svg('default');
			var field_type_config_has_label = true;
			var field_type_config_has_mask_preview = false;
			var field_type_config_multiple = false;
			var field_type_config_admin_hide_id = false;
			var field_type_config_mask_wrappers_drop = false;
		}

		// Set field ID as title
		if(
			$.WS_Form.settings_plugin.helper_field_id &&
			!field_type_config_admin_hide_id
		) {

			obj.attr('title', '#field(' + field_id + ')');
		}

		// Check to see if resize should be ignored
		var field_required = this.get_object_meta_value(field, 'required', false);
		var field_hidden = this.get_object_meta_value(field, 'hidden', false);
		var field_disabled = this.get_object_meta_value(field, 'disabled', false);
		var field_readonly = this.get_object_meta_value(field, 'readonly', false);
		var field_conditional = (this.conditional_icons_array['field'].indexOf(parseInt(field_id, 10)) !== -1);
		var field_source_last_api_error = this.get_object_meta_value(field, 'data_source_last_api_error', '');

		// Determine if there are required settings and field setting errors
		var field_setting_error = this.field_setting_error(field);

		if(field_setting_error.has_required_setting) { obj.attr('data-required-setting', ''); }

		// Icons
		var field_icon_array = [];
		if(field_required) { field_icon_array.push('<span class="wsf-required" title="' + this.language('required') + '"></span>'); }
		if(field_hidden) { field_icon_array.push('<span title="' + this.language('hidden') + '">' + this.svg('hidden') + '</span>'); }
		if(field_disabled) { field_icon_array.push('<span title="' + this.language('disabled') + '">' + this.svg('disabled') + '</span>'); }
		if(field_readonly) { field_icon_array.push('<span title="' + this.language('readonly') + '">' + this.svg('readonly') + '</span>'); }
		if(field_conditional) { field_icon_array.push('<span class="wsf-field-conditional" title="' + this.language('conditional') + '">' + this.svg('conditional') + '</span>'); }
		if(field_setting_error.field_setting_error !== false) { field_icon_array.push('<span class="wsf-required-setting" title="' + this.language('required_setting') + '">' + this.svg('warning') + '</span>'); }
		if(field_source_last_api_error !== '') {

			// Show error
			if(typeof(this.form_obj.attr('data-data-source-error')) === 'undefined') {

				// Show error
				this.data_source_error(field, field_source_last_api_error);

				// Set attribute so this error only shows once
				this.form_obj.attr('data-data-source-error', '');
			}

			// Add icon
			field_icon_array.push('<span class="wsf-data-source-error" title="' + this.language('data_grid_data_source_error') + '">' + this.svg('warning') + '</span>');
		}
		var field_icon_count = field_icon_array.length;

		// Label
		var field_html = '<div class="wsf-field-inner">';
		field_html += '<div class="wsf-field-label' + ((field_icon_count > 0) ? ' wsf-field-icon-count-' + field_icon_count : '') + '">' + field_type_config_icon;
		field_html += field_icon_array.join('');
		field_html += '<input type="text" value="' + this.esc_attr(this_field_label) + '" data-label="' + this.esc_attr(field_id) + '" readonly maxlength="1024" aria-label="' + this.language('field_label_aria', field_type_config.label) + '""></div>';

		if(!field_type_config_admin_hide_id) {

			field_html += '<div class="wsf-field-type">' + this.esc_html(field_type_config_label);

			if($.WS_Form.settings_plugin.helper_field_id) {

				field_html += '<span class="wsf-field-id">' + this.language('id') + ': ' + this.esc_html(field_id) + '</span>';
			}

			field_html += '</div>';
		}

		if(field_type_config_has_mask_preview) {

			// Parse field admin field_preview
			var text_editor = this.get_object_meta_value(field, 'text_editor', '', true);
			var html_editor = this.get_object_meta_value(field, 'html_editor', '', true);
			var mask_values = {'text_editor': text_editor, 'html_editor': html_editor};
			var field_preview = this.mask_parse(field_type_config.mask_preview, mask_values);

			// WPAutoP
			var wpautop = this.wpautop_parse_variable(field, field_type_config);
			if(wpautop) { field_preview = this.wpautop(field_preview); }

			// Fix any open tags
			var div = document.createElement('div');
			div.innerHTML = field_preview;
			field_preview = div.innerHTML;

			field_html += '<div class="wsf-field-preview">' + field_preview + '</div>';
		}

		field_html += '</div>';

		// reCAPTCHA override
		if((field_type == 'recaptcha') && (this.get_object_meta_value(field, 'recaptcha_recaptcha_type', 'default') == 'invisible')) { field_type_config_mask_wrappers_drop = true; }

		// hCaptcha override
		if((field_type == 'hcaptcha') && (this.get_object_meta_value(field, 'hcaptcha_type', 'default') == 'invisible')) { field_type_config_mask_wrappers_drop = true; }

		// Settings
		field_html += this.settings_html('field', field_id, field_type_config_multiple, field_type_config_mask_wrappers_drop);

		if(!field_type_config_mask_wrappers_drop) {

			// Column size icon
			field_html += this.column_size_change_html();

			// Offset icon
			field_html += this.offset_change_html();
		}

		// Inject HTML
		obj.html(field_html);

		// Settings - Events
		this.settings_events(obj, 'field');

		// Hover event
		this.hover_events(obj, 'field', field_id);
		// Initialize label
		this.label_init(obj);

		if(!field_type_config_mask_wrappers_drop) {

			// Make column size changeable
			this.column_size_change_init(obj);

			// Make offset changeable
			this.offset_change_init(obj);
		}
	}

	// Fields settings error checking
	$.WS_Form.prototype.fields_setting_error = function() {

		$('[data-required-setting]', this.form_obj).each(function() {

			$.WS_Form.this.field_render($(this));
		})
	}

	// Field settings error check
	$.WS_Form.prototype.field_setting_error = function(field) {

		var field_setting_error = [];

		if(typeof(field.meta) === 'undefined') { return false; }

		if(
			(typeof($.WS_Form.meta_keys_required_setting[field.type]) === 'undefined') &&
			(typeof($.WS_Form.field_type_cache[field.type]) !== 'undefined')
		) {

			var field_type = $.WS_Form.field_type_cache[field.type];

			var meta_keys = this.field_type_meta_keys(field_type, 'required_setting', 'required_setting_global_meta_key');

			$.WS_Form.meta_keys_required_setting[field.type] = meta_keys;
		}

		var has_required_setting = ($.WS_Form.meta_keys_required_setting[field.type].length > 0);

		if(has_required_setting) {

			for(var meta_key_required_index in $.WS_Form.meta_keys_required_setting[field.type]) {

				if(!$.WS_Form.meta_keys_required_setting[field.type].hasOwnProperty(meta_key_required_index)) { continue; }

				var meta_key = $.WS_Form.meta_keys_required_setting[field.type][meta_key_required_index];

				// Check for blank
				if(
					(typeof(field.meta[meta_key]) === 'undefined') ||
					(field.meta[meta_key] == '')

				) { field_setting_error.push(meta_key); }

				// Check value if this is a select for choosing a field
				if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { continue; }
				var meta_key_config = $.WS_Form.meta_keys[meta_key];

				if(typeof(meta_key_config['type']) === 'undefined') { continue; }
				if(meta_key_config['type'] == 'select') {

					if(typeof(meta_key_config['options']) === 'undefined') { continue; }
					if(meta_key_config['options'] == 'fields') {

						var field_id = field.meta[meta_key];

						if(typeof(this.field_data_cache[field_id]) === 'undefined') {

							field_setting_error.push(meta_key);
						}
					}

					if(meta_key_config['options'] == 'sections') {

						var section_id = field.meta[meta_key];

						if(typeof(this.section_data_cache[section_id]) === 'undefined') {

							field_setting_error.push(meta_key);
						}
					}
				}
			}
		}

		return {'has_required_setting': has_required_setting, 'field_setting_error': ((field_setting_error.length > 0) ? field_setting_error : false) };
	}

	// Blank section / field update
	$.WS_Form.prototype.object_blank_update = function(obj) {

		if(typeof(obj) === 'undefined') { obj = $('.wsf-group'); }

		// Sections
		$('.wsf-sections', obj).each(function() {

			var section_wrapper_count = $('.wsf-section:not(.ui-sortable-helper)', $(this)).length;
			var helper_count = $('.wsf-section.ui-sortable-helper', $(this)).length;
			var placeholder_count = $('.wsf-section-placeholder', $(this)).length;
			var blank_count = $('.wsf-section-blank:visible', $(this)).length;
			var total_count = section_wrapper_count + placeholder_count;

			// If dragging a new section, this ensure the out even fires correctly
			if($.WS_Form.this.dragged_section) {

				if((section_wrapper_count == 0) && (helper_count == 1) && (placeholder_count == 1) && (blank_count == 0)) { total_count = 0; }
			}

			var blank_section_obj = $('.wsf-section-blank', $(this));
			if(total_count == 0) {

				blank_section_obj.show();

			} else {

				blank_section_obj.hide();
			}
		});

		// Fields
		$('.wsf-fields', obj).each(function() {

			var field_wrapper_count = $('li.wsf-field-wrapper:not(.ui-sortable-helper)', $(this)).length;
			var helper_count = $('li.wsf-field-wrapper.ui-sortable-helper', $(this)).length;
			var placeholder_count = $('.wsf-field-placeholder', $(this)).length;
			var blank_count = $('.wsf-field-blank:visible', $(this)).length;
			var total_count = field_wrapper_count + placeholder_count;

			// If dragging a new field, this ensure the out even fires correctly
			if($.WS_Form.this.dragged_field) {

				if((field_wrapper_count == 0) && (helper_count == 1) && (placeholder_count == 1) && (blank_count == 0)) { total_count = 0; }
			}

			var blank_field_obj = $('.wsf-field-blank', $(this));
			if(total_count == 0) {

				blank_field_obj.show();

			} else {

				blank_field_obj.hide();
			}
		});
	}

	// API - Field - POST
	$.WS_Form.prototype.field_post = function(obj) {

		// Loader on
		this.loader_on();

		// Blur any labels that are being edited to save them
		$('li.wsf-field-wrapper[data-id] input:not([readonly])').trigger('blur');

		// Get section ID
		var section_id = obj.parent().attr('data-id');

		// Get field type
		var type = obj.attr('data-type');

		// Get field width (factor)
		var width_factor = obj.attr('data-width-factor');

		// Get next sibling ID (0 = Last or only element in section)
		var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;

		// Build request parameters
		var params = {

			section_id:			section_id,
			type:				type,
			next_sibling_id:	next_sibling_id
		};

		if(width_factor) {

			params.width_factor = width_factor;
		}

		// Call AJAX request
		var call_obj = obj;
		$.WS_Form.this.api_call('field/', 'POST', params, function(response) {

			// Set data attribute
			call_obj.attr('data-id', response.data.id);

			// Store data to field_data_cache array
			$.WS_Form.this.field_data_cache[response.data.id] = $.extend(true, {}, response.data);

			// Build field
			$.WS_Form.this.field_render(call_obj);

			// Trigger label edit
			$('input', obj).first().trigger('dblclick');

			// Update blank fields
			$.WS_Form.this.object_blank_update($('.wsf-section[data-id="' + $.WS_Form.this.esc_selector(section_id) + '"]'));

			// Check multiple field
			$.WS_Form.this.field_check_multiple();

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// field_type_click_section_id set
	$.WS_Form.prototype.field_type_click_section_id_set = function() {

		var group_index = this.get_object_meta_value(this.form, 'tab_index', 0, true);
		if(!group_index) { group_index = 0; }

		if(typeof(this.form.groups) === 'undefined') { return false; }
		if(typeof(this.form.groups[group_index]) === 'undefined') { return false; }

		var group = this.form.groups[group_index];
		if(typeof(group.sections) === 'undefined') { return false; }

		var section_id = false;
		var field_date_updated_max = false;

		// Find section with newest fields
		for(var section_index in group.sections) {

			if(!group.sections.hasOwnProperty(section_index)) { continue; }

			var section = group.sections[section_index];
			if(section_id === false) { section_id = section.id; }

			if(typeof(section.fields) === 'undefined') { return false; }

			// If there are no fields in section, use it.
			if(section.fields.length == 0) { section_id = section.id; break; }

			for(var field_index in section.fields)  {

				if(!section.fields.hasOwnProperty(field_index)) { continue; }

				var field = section.fields[field_index];

				if(
					(field_date_updated_max === false) ||
					(field_date_updated_max < field.date_updated)
				) {

					field_date_updated_max = field.date_updated;

					section_id = section.id;
				}
			}
		}

		return section_id;
	}

	// Check for fields that can only be added to the form once
	$.WS_Form.prototype.field_check_multiple = function() {

		var field_type_disabled = [];

		// Reset
		for(var field_type_id in $.WS_Form.field_type_cache) {

			if(!$.WS_Form.field_type_cache.hasOwnProperty(field_type_id)) { continue; }

			field_type_disabled[field_type_id] = false;
		}

		// Run through each field on the page
		for(var field_id in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(field_id)) { continue; }

			// Get field
			if(typeof(this.field_data_cache[field_id]) === 'undefined') { continue; }
			var field = this.field_data_cache[field_id];
			if(typeof(field['type']) === 'undefined') { continue; }
			var field_type_id = field['type'];

			// Get field type data
			var field_type = $.WS_Form.field_type_cache[field_type_id];

			// Check to see if multiple attribute is set
			if(typeof(field_type['multiple']) === 'undefined') { continue; }

			var multiple = field_type['multiple'];

			if(!multiple) {

				// Mark as disabled if it only be added once
				field_type_disabled[field_type_id] = true;
			}
		}

		// Set
		for(var field_type_id in $.WS_Form.field_type_cache) {

			if(!$.WS_Form.field_type_cache.hasOwnProperty(field_type_id)) { continue; }

			var toolbar_field_obj = $('#wsf-form-field-selector [data-type="' + this.esc_selector(field_type_id) + '"]');
			
			if(field_type_disabled[field_type_id]) {

				toolbar_field_obj.addClass('wsf-field-disabled');

			} else {

				toolbar_field_obj.removeClass('wsf-field-disabled');
			}
		}
	}

	// API - Field - PUT - SORT INDEX
	$.WS_Form.prototype.field_put_sort_index = function(obj) {

		// Ensure object and data-id exists
		if(!obj.length) { return; }
		if(!obj.attr('data-id')) { return; }

		// Get next sibling ID (0 = Last or only element in section)
		var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;

		// Get section_id section has been dragged to
		var section_id = obj.closest('.wsf-section').attr('data-id');

		// Check if it moved
		if((this.next_sibling_id_old == next_sibling_id) && (this.section_id_old == section_id)) { return false; }

		// Loader on
		this.loader_on();

		// Get field ID
		var field_id = obj.attr('data-id');

		// Build request parameters
		var params = {

			next_sibling_id:	next_sibling_id,
			section_id:			section_id
		};

		// Call AJAX request
		$.WS_Form.this.api_call('field/' + field_id + '/sort-index/', 'POST', params, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// API - PUT - CLONE
	$.WS_Form.prototype.field_put_clone = function(obj) {

		// Loader on
		this.loader_on();

		// Read data attributes
		var field_id = obj.attr('data-id');

		// Get next sibling ID (0 = Last or only element in section)
		var next_sibling_id = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;

		// Build request parameters
		var params = {

			next_sibling_id: next_sibling_id
		};

		// Call AJAX request
		var call_obj = obj;
		this.api_call('field/' + field_id + '/clone/', 'POST', params, function(response) {

			// Get field ID
			var field_id = response.data.id;

			// Get field HTML (Stores it to cache)
			var field_html = $.WS_Form.this.get_field_html(response.data);

			// Insert new field after obj
			$(field_html).insertAfter(call_obj);

			// Get new field object
			var new_obj = $('li.wsf-field-wrapper[data-id="' + $.WS_Form.this.esc_selector(field_id) + '"]');

			// Hide
			new_obj.hide();

			// Build field
			$.WS_Form.this.field_render(new_obj);

			// Show
			new_obj.show();

			// Trigger label edit
			$('input', new_obj).first().trigger('dblclick');

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Label - Init
	$.WS_Form.prototype.label_init = function(object_wrapper) {

		// Get label object
		var label_obj = object_wrapper.find('input').first();

		// Get ID and object type
		var object_id = object_wrapper.attr('data-id');
		var object = $.WS_Form.this.get_object_type(object_wrapper);

		switch(object) {

			case 'group' :

				// Autosize input
				this.input_auto_size(label_obj, 'wsf-group-tab-input-dummy');
				break;
		}

		// On input, save label to meta data
		label_obj.on('keydown input', function(e) {

			var object_wrapper = $(this).closest('[data-id]');
			var object_id = object_wrapper.attr('data-id');
			var object = $.WS_Form.this.get_object_type(object_wrapper);

			var keyCode = e.keyCode || e.which;

			if(keyCode === 13) {

				e.preventDefault();

				$(this).trigger('blur');

			} else {

				// Set data attribute
				var object_label = $(this).val();

				// Update
				$.WS_Form.this.label_update(object, object_id, object_label);
			}
		});

		// Blur - Save label
		label_obj.on('blur', function() {

			var object_wrapper = $(this).closest('[data-id]');
			var object_id = object_wrapper.attr('data-id');
			var object = $.WS_Form.this.get_object_type(object_wrapper);

			// Save label
			$.WS_Form.this.label_save($(this), object, object_id);

			// Enable double click
			$.WS_Form.this.label_dblclick_enable($(this));
		});

		// Focus - Enable editing
		label_obj.on('focus', function() {

			// Switch off readonly and select it
			$(this).prop('readonly', false).trigger('select');

			// Store old label
			$.WS_Form.this.label_old = $(this).val();

			// Disable double click
			$(this).off('dblclick');
		});

		// Double click- Enable editing
		this.label_dblclick_enable(label_obj);
	}

	// Label - Double click - Enable
	$.WS_Form.prototype.label_dblclick_enable = function(label_obj) {

		// Double click / Focus - Enable editing
		label_obj.on('dblclick', function() {

			// Switch off readonly and select it
			$(this).prop('readonly', false).trigger('select');

			// Store old label
			$.WS_Form.this.label_old = $(this).val();

			// Disable double click
			$(this).off('dblclick');
		});
	}

	// Label - Update
	$.WS_Form.prototype.label_update = function(object, object_id, object_label) {

		// Store to appropriate object cache
		switch(object) {

			case 'group':

				$.WS_Form.this.group_data_cache[object_id].label = object_label;
				break;

			case 'section':

				$.WS_Form.this.section_data_cache[object_id].label = object_label;
				break;

			case 'field':

				$.WS_Form.this.field_data_cache[object_id].label = object_label;
				break;
		}

		// Update sidebar label
		var sidebar_label = $('#wsf-sidebar-' + object + '[data-id="' + this.esc_selector(object_id) + '"] [name="label"]');
		if(sidebar_label.length) {

			// Check for blank label
			if(object_label.trim() == '') {

				object_label = this.get_label_default(object);
			}

			sidebar_label.val(object_label);
		}
	}

	// Label - Save
	$.WS_Form.prototype.label_save = function(label_obj, object, object_id) {

		// Switch on readonly
		document.getSelection().removeAllRanges();
		label_obj.prop('readonly', true);

		var object_label = label_obj.val();
		object_label = object_label.trim();

		// Check to see if label has changed. If it hasn't don't bother saving it.
		if(object_label == this.label_old) { return true; }

		// Check for blank label
		if(object_label == '') {

			object_label = this.get_label_default(object);
			label_obj.val(object_label).trigger('change');
		}

		// Update to ensure no AJAX requests have effected last update
		this.label_update(object, object_id, object_label);

		// Loader on
		$.WS_Form.this.loader_on();

		// Build parameters
		var params = {};

		// Form ID
		params['form_id'] = this.form_id;

		// Object data
		switch(object) {

			case 'group':

				params[object] = $.WS_Form.this.group_data_cache[object_id];
				break;

			case 'section':

				params[object] = $.WS_Form.this.section_data_cache[object_id];
				break;

			case 'field':

				params[object] = $.WS_Form.this.field_data_cache[object_id];
				break;
		}

		// Call AJAX request
		this.api_call(object + '/' + object_id + '/put/', 'POST', params, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Get object type
	$.WS_Form.prototype.get_object_type = function(obj) {

		if(obj.hasClass('wsf-form')) { return('form'); }
		if(obj.hasClass('wsf-form-style')) { return('style'); }
		if(obj.hasClass('wsf-group-tab')) { return('group'); }
		if(obj.hasClass('wsf-group')) { return('group'); }
		if(obj.hasClass('wsf-section')) { return('section'); }
		if(obj.hasClass('wsf-field-wrapper')) { return('field'); }
		if(obj.hasClass('wsf-data-grid-group-tab')) { return('data-grid-group-tab'); }
		return false;
	}

	// Get default label
	$.WS_Form.prototype.get_label_default = function(object) {

		var label_default = '';

		switch(object) {

			case 'form' :

				label_default = this.language('default_label_form');
				break;

			case 'group' :

				label_default = this.language('default_label_group');
				break;

			case 'section' :

				label_default = this.language('default_label_section');
				break;

			case 'field' :

				label_default = this.language('default_label_field');
				break;
		}

		return label_default;
	}

	// Edit (Used for editing sections and fields)
	$.WS_Form.prototype.object_edit = function(obj, reload) {

		// Get object type
		var object = this.get_object_type(obj);

		// Read data attributes
		var object_id = obj.attr('data-id');

		// Get sidebar dom object
		var obj_sidebar_outer = $('#wsf-sidebar-' + object);

		// Check to see if object is already being edited
		if(obj.hasClass('wsf-editing')) {

			// Object specific functions
			switch(object) {

				case 'group' :

					// Remove editing class from tab
					$('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"]').removeClass('wsf-editing');

					break;
			}

			// Remove editing class
			obj.removeClass('wsf-editing');

			// Process as save
			this.object_save(obj);

			// Sanitize sidebars
			this.sidebars_sanitize();

			// Reset sidebar
			this.sidebar_reset();

			return false;
		}

		// Save changes on any open objects
		this.object_save_changes(reload);

		// Sanitize sidebars
		this.sidebars_sanitize();

		// Add editing class to object
		obj.addClass('wsf-editing');

		// Object specific functions
		switch(object) {

			case 'group' :

				// Add editing class to tab
				$('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"]').addClass('wsf-editing');

				// Show this group (Select tab)
				var tab_index = $('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"] a').trigger('click');

				break;
		}

		// Get object data
		var object_data = this.get_object_data(object, object_id);

		// Get object meta data
		var object_meta = this.get_object_meta(object, object_id);

		// Create new object data that edits will be saved to
		if(!reload) {

			this.object_data_scratch = $.extend(true, {}, object_data); // Deep clone
		}

		// Destroy tabs (Ensures subsequent calls work)
		if(obj_sidebar_outer.hasClass('ui-tabs')) { obj_sidebar_outer.tabs('destroy'); }

		// Data object and ID
		obj_sidebar_outer.attr('data-object', object).attr('data-id', object_id);

		// Build sidebar
		var sidebar_html_tabs = '';
		var sidebar_html = '';
		var sidebar_html_buttons = '';
		var sidebar_inits = [];
		if(typeof(object_meta.fieldsets) !== 'undefined') {

			// Clear sidebar caches
			this.sidebar_cache_clear(obj_sidebar_outer);

			var buttons = [
				{'class': 'wsf-button-primary', 'action': 'wsf-sidebar-save-close', 'label': this.language('save_and_close')},
				{'class': 'wsf-button-primary', 'action': 'wsf-sidebar-save', 'label': this.language('save')},
				{'class': '', 'action': 'wsf-sidebar-cancel', 'label': this.language('cancel')},
			];

			switch(object) {

				case 'group' :
				case 'section' :
				case 'field' :

					buttons.push({'class': '', 'action': 'wsf-sidebar-clone', 'right': true, 'label': this.language('clone')});
					buttons.push({'class': 'wsf-button-danger', 'action': 'wsf-sidebar-delete', 'label': this.language('delete')});
			}

			var sidebar_return = this.sidebar_html(object, object_id, this.object_data_scratch, object_meta, false, true, true, true, buttons);
			sidebar_html_tabs = sidebar_return.html_tabs;
			sidebar_html = sidebar_return.html;
			sidebar_html_buttons = sidebar_return.html_buttons;
			sidebar_inits = sidebar_return.inits;
		}

		// Initialize title for objects
		this.sidebar_title_init(object, object_id, object_meta, obj_sidebar_outer);

		// Tabs
		obj_sidebar_outer.append(sidebar_html_tabs);

		// Inner
		obj_sidebar_outer.append('<div class="wsf-sidebar-inner">' + sidebar_html + '</div>');

		var obj_sidebar_inner = $('.wsf-sidebar-inner', obj_sidebar_outer);

		// Buttons
		obj_sidebar_outer.append(sidebar_html_buttons);

		// Initialize sidebar
		this.sidebar_inits(sidebar_inits, obj_sidebar_outer, obj_sidebar_inner, this.object_data_scratch);

		// Initialize label for objects
		this.sidebar_label_init(object_meta, obj, object, obj_sidebar_outer, obj_sidebar_inner);

		// Initialize buttons for objects
		this.sidebar_buttons_init(obj, obj_sidebar_outer);

		// Initialize change events for objects
		this.sidebar_change_event_init(obj, object, obj_sidebar_inner);

		// Open sidebar
		this.sidebar_open(object);
	}

	// Sanitize sidebars
	$.WS_Form.prototype.sidebars_sanitize = function() {
 
		// Reset any existing meta_keys IDs to avoid conflicts between sidebars
		$(".wsf-sidebar:not(#wsf-sidebar-support) [data-meta-key]").removeAttr('id data-meta-key data-meta-key-type');
	}

	// Save changes to any open objects
	$.WS_Form.prototype.object_save_changes = function(reload) {

		if(typeof(reload) === 'undefined') { reload = false; }

		// Check if sidebar is locked
		if(this.sidebar_lock_count) { return false; }

		// Check if any sidebars need to be saved
		var sidebar_current = $('[data-action-sidebar].wsf-editing').first().attr('data-action-sidebar');

		// Save changes to actions or conditional logic
		switch(sidebar_current) {

			// Save form
			case 'form' :

				$.WS_Form.this.object_save($('#wsf-form'));

				break;

			// Save actions
			case 'action' :

				$.WS_Form.this.sidebar_action_save();

				break;

			// Save conditional logic
			case 'conditional' :

				$.WS_Form.this.sidebar_conditional_save();

				break;
			default :

				// Remove editing class on any other objects that are currently being edited
				var obj_editing = $('.wsf-editing', $('#wsf-form'));
				if(obj_editing.length && !reload) {

					// Unfocus any focussed fields to force change event
					$('.wsf-sidebar.wsf-sidebar-open :focus').trigger('blur');

					// Process as save
					this.object_save(obj_editing);

					// Remove editing class
					obj_editing.removeClass('wsf-editing');
				}			
		}
	}

	// Edit - Button - Save
	$.WS_Form.prototype.object_button_save = function(obj, close) {

		if(typeof(close) === 'undefined') { close = false; }

		// Save object
		this.object_save(obj);

		// Get object type
		var object = this.get_object_type(obj);

		// Get ID of field
		var object_id = obj.attr('data-id');

		// Reset sidebar
		if(close) {

			// Cancel object
			this.object_cancel(obj);

			// Reset sidebar
			this.sidebar_reset();
		}
	}

	// Edit - Button - Cancel
	$.WS_Form.prototype.object_button_cancel = function(obj) {

		// Cancel object
		this.object_cancel(obj);

		// Reset sidebar
		this.sidebar_reset();
	}

	// Edit - Button - Clone
	$.WS_Form.prototype.object_button_clone = function(obj) {

		// Cancel object
//		this.object_cancel(obj);

		// Reset sidebar
//		this.sidebar_reset();

		// Get object type
		var object = this.get_object_type(obj);

		switch(object) {

			case 'group' :

				wsf_group_clone(this, obj);
				break;

			case 'section' :

				wsf_section_clone(this, obj);
				break;

			case 'field' :

				wsf_field_clone(this, obj);
				break;
		}
	}

	// Edit - Button - Delete
	$.WS_Form.prototype.object_button_delete = function(obj) {

		// Cancel object
		this.object_cancel(obj);

		// Reset sidebar
		this.sidebar_reset();

		// Get object type
		var object = this.get_object_type(obj);

		switch(object) {

			case 'group' :

				wsf_group_delete(this, obj);
				break;

			case 'section' :

				wsf_section_delete(this, obj);
				break;

			case 'field' :

				wsf_field_delete(this, obj);
				break;
		}
	}

	// Save notification
	$.WS_Form.prototype.saving_notification = function(obj) {

		var ws_this = this;

		$('#wsf-sidebars .wsf-sidebar').addClass('wsf-saving');
	
		var obj_group = false;
		if(obj) {

			obj.addClass('wsf-saving');

			// Get object type
			var object = this.get_object_type(obj);

			// Read data attributes
			var object_id = obj.attr('data-id');

			// Object specific functions
			switch(object) {

				case 'group' :

					// Add editing class to tab
					obj_group = $('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"]');

					break;
			}
		}
		if(obj_group) { obj_group.addClass('wsf-saving'); }

		setTimeout(function() {

			$('#wsf-sidebars .wsf-sidebar').removeClass('wsf-saving');
			if(obj) { obj.removeClass('wsf-saving'); }
			if(obj_group) { obj_group.removeClass('wsf-saving'); }

		}, 500);
	}	

	// Object - Save
	$.WS_Form.prototype.object_save = function(obj, save) {

		if(typeof(save) === 'undefined') { save = true; }

		// Get object type
		var object = this.get_object_type(obj);

		// Read object ID
		var object_id = obj.attr('data-id');

		// Get sidebar dom object
		var obj_sidebar_outer = $('#wsf-sidebar-' + object);
		var obj_sidebar_inner = $('.wsf-sidebar-inner', obj_sidebar_outer);

		if(save) {

			// Save old object state to determine if we'll save it later
			switch(object) {

				case 'form' :

					var object_old = JSON.stringify(this.form);
					break;

				case 'group' :

					var object_old = JSON.stringify(this.group_data_cache[object_id]);
					break;

				case 'section' :

					var object_old = JSON.stringify(this.section_data_cache[object_id]);
					break;

				case 'field' :

					var object_old = JSON.stringify(this.field_data_cache[object_id]);
					break;
			}
		}

		// Run through each of the field meta and set it
		for(var key in this.object_meta_cache) {

			if(!this.object_meta_cache.hasOwnProperty(key)) { continue; }

			// Get meta_key
			var meta_key = this.object_meta_cache[key]['meta_key'];

			// Update object data
			var meta_value = this.object_data_update_by_meta_key(object, this.object_data_scratch, meta_key);

			// Data source
			if(
				(meta_key === 'data_source_id') &&
				meta_value
			) {
				// Reset data grid
				var meta_key = $('[data-meta-key="data_source_id"]', obj_sidebar_inner).closest('.wsf-data-grid').attr('data-meta-key');
				$.WS_Form.this.data_grid_clear_rows(meta_key);
			}
		}

		// Object label
		switch(object) {

			case 'field' :

				var field_data = this.field_data_cache[object_id];
				var field_type_config = $.WS_Form.field_type_cache[field_data.type];
				var label_default = field_type_config.label_default;
				break;

			default :

			var label_default = this.get_label_default(object);
		}
		var object_label = $('[name="label"]', obj_sidebar_inner).val();
		if(typeof(object_label) !== 'undefined') {

			this.object_data_scratch['label'] = (object_label == '') ? label_default : object_label;
		}

		if(save) {

			var sidebar_fields_toggle_init_process = false;

			// Move new object data to appropriate object
			switch(object) {

				case 'form' :

					this.form.label = this.object_data_scratch.label;
					this.form.meta = this.object_data_scratch.meta;

					// Set preview URL for style button
					this.set_preview_url_styler();

					break;

				case 'group' :

					this.group_data_cache[object_id].label = this.object_data_scratch.label;
					this.group_data_cache[object_id].meta = this.object_data_scratch.meta;

					break;

				case 'section' :

					// Check to see if sidebar fields toggle should run
					var section_repeatable_old = this.get_object_meta_value(this.section_data_cache[object_id], 'section_repeatable', false);
					var section_repeatable_new = this.get_object_meta_value(this.object_data_scratch, 'section_repeatable', false);
					sidebar_fields_toggle_init_process = (!section_repeatable_old && section_repeatable_new); 

					// Optimize breakpoints
					this.breakpoint_optimize(this.object_data_scratch);

					this.section_data_cache[object_id].label = this.object_data_scratch.label;
					this.section_data_cache[object_id].meta = this.object_data_scratch.meta;

					break;

				case 'field' :

					// Optimize breakpoints
					this.breakpoint_optimize(this.object_data_scratch);

					// Optimize orientation breakpoints
					var field_type = (typeof(this.object_data_scratch.type) !== 'undefined') ? this.object_data_scratch.type : false;
					switch(field_type) {

						case 'checkbox' :
						case 'radio' :

							this.orientation_breakpoint_optimize(this.object_data_scratch);
					}

					this.field_data_cache[object_id].label = this.object_data_scratch.label;
					this.field_data_cache[object_id].meta = this.object_data_scratch.meta;

					break;
			}

			// If scratch is different to current object, save the data
			if(JSON.stringify(this.object_data_scratch) !== object_old) {

				// Loader on
				this.loader_on();

				// Build parameters
				var params = {

					form_id: this.form_id
				};

				// Object data
				params[object] = this.object_data_scratch;

				// Save notification
				$.WS_Form.this.saving_notification(obj);

				// Call AJAX request
				this.api_call(object + '/' + object_id + '/put/', 'POST', params, function(response) {

					switch(object) {

						case 'field' :

							// If there is a meta preview, re-render the field. We run this after the API call finishes in case there is any server side formatting of content.
							if((typeof(field_type_config.mask_preview) !== 'undefined') && field_type_config.mask_preview) {

								$.WS_Form.this.field_render(obj, $.WS_Form.this.field_data_cache[object_id]);
							}

							break;

						case 'section' :

							// Initialize fields toggle for objects
							if(sidebar_fields_toggle_init_process) {

								$.WS_Form.this.sidebar_fields_toggle_init(obj, object, obj_sidebar_inner);
							}

							break;
					}

					// Loader off
					$.WS_Form.this.loader_off();
				});
			}
		}
	}

	// Object - Cancel
	$.WS_Form.prototype.object_cancel = function(obj) {

		// Get object type
		var object = this.get_object_type(obj);

		// Get ID of field
		var object_id = obj.attr('data-id');

		// Render
		switch(object) {

			case 'form' :

				// Reset form title
				$('[data-action="wsf-form-label"]').val($.WS_Form.this.form['label']);

				// Remove editing class on form edit button
				$('[data-action="wsf-form-settings"]').removeClass('wsf-editing');

				break;

			case 'group' :

				// Render group
				this.group_render(obj);

				// Change tab label
				var object_data = this.get_object_data(object, object_id);
				$('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"] a input').val(object_data['label']).trigger('change');

				// Remove editing class on tab
				$('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"]').removeClass('wsf-editing');

				break;

			case 'section' :

				// Render section
				var object_data = this.get_object_data(object, object_id);
				$('.wsf-section[data-id="' + this.esc_selector(object_id) + '"] .wsf-section-label input').val(object_data['label']);
				$.WS_Form.this.section_render(obj);
				break;

			case 'field' :

				// Render field
				this.field_render(obj);
				break;
		}

		// If we were using scratch, lets reset the classes on the field
		if(this.object_data_scratch !== false) {

			// Get framework
			var object_data = false;

			// Get object data
			switch(object) {

				case 'section' :

					object_data = this.section_data_cache[object_id];
					break;

				case 'field' :

					object_data = this.field_data_cache[object_id];
					break;
			}

			// Add classes
			if(object_data !== false) {

				this.column_classes_render(obj, this.object_data_scratch, false);
				this.column_classes_render(obj, object_data);
			}
		}

		// Remove editing class
		obj.removeClass('wsf-editing');

		// Clear object_data_scratch
		this.object_data_scratch = false;

		// Clear any breakpoint objects
		$('.wsf-breakpoint-sizes.wsf-breakpoint-sizes-initialized').remove();

		// Clear keyup functions
		$.WS_Form.this.keydown = [];
	}

	// Object - Delete
	$.WS_Form.prototype.object_delete = function(obj) {

		// Loader on
		this.loader_on();

		// Hide object
		obj.hide();

		// Get object type
		var object = this.get_object_type(obj);

		// Get object ID
		var object_id = obj.attr('data-id');

		// Remove from cache
		switch(object) {

			case 'group' :

				// Remove object
				obj.remove();

				// Get tab and remember its index
				var tab_obj = $('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"]');
				var tab_obj_index = tab_obj.index();

				// Hide tab
				tab_obj.remove();

				// Count tabs
				this.group_tabs_count();

				// Select next closest tab
				tab_obj_index--;
				if(tab_obj_index < 0) { tab_obj_index = 0; }

				// Select new tab
				$('.wsf-group-tabs li.wsf-group-tab:eq(' + tab_obj_index + ') a').trigger('click');

				// Save current tab index
				this.set_object_meta_value(this.form, 'tab_index', tab_obj_index);

				// Delete from cache
				delete this.group_data_cache[object_id];

				break;

			case 'section' :

				// Remove object
				obj.remove();

				// Update blank fields
				$.WS_Form.this.object_blank_update();

				// Delete from cache
				delete this.section_data_cache[object_id];

				break;

			case 'field' :

				// Remove object
				obj.remove();

				// Update blank fields
				$.WS_Form.this.object_blank_update();

				// Delete from cache
				delete this.field_data_cache[object_id];

				break;
		}

		// Call AJAX request
		this.api_call(object + '/' + object_id + '/delete/', 'POST', false, function(response) {

			// Check multiple field
			$.WS_Form.this.field_check_multiple();

			// Check fields that have required settings
			$.WS_Form.this.fields_setting_error();

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Download object as JSON file
	$.WS_Form.prototype.object_download = function(obj) {

		// Get object type
		var object = this.get_object_type(obj);

		// Get object ID
		var object_id = obj.attr('data-id');

		// Build downloader
		var downloader_html = '<form id="wsf-object-downloader" action="' + this.esc_attr(ws_form_settings.url_ajax + object + '/' + object_id) + '/download/json" method="post">';

		downloader_html += '<input type="hidden" name="id" value="' + this.esc_attr(this.form_id) + '" />';
		downloader_html += '<input type="hidden" name="_wpnonce" value="' + this.esc_attr(ws_form_settings.x_wp_nonce) + '" />';
		downloader_html += '<input type="hidden" name="' + this.esc_attr(ws_form_settings.wsf_nonce_field_name) + '" value="' + this.esc_attr(ws_form_settings.wsf_nonce) + '" />';

		downloader_html += '</form>';

		// Inject into body
		var downloader = $('body').append(downloader_html);

		// Submit
		$('#wsf-object-downloader').trigger('submit');

		// Remove
		$('#wsf-object-downloader').remove();

		// Reset sidebar
		this.sidebar_reset();
	}

	// Add template to library
	$.WS_Form.prototype.object_template_add = function(obj) {

		// Get object type
		var object = this.get_object_type(obj);

		// Get object ID
		var object_id = obj.attr('data-id');

		// Call AJAX request
		$.WS_Form.this.api_call(object + '/' + object_id + '/template/add/', 'POST', false, function(response) {

			// Sidebar - Toolbox - Open
			$.WS_Form.this.sidebar_open('toolbox');

			// Click the Sections tab
			$('[data-wsf-tab-key="section-selector"]', $('#wsf-sidebar-toolbox')).trigger('click');

			// Handle response from template API endpoing
			$.WS_Form.this.template_api_response(response);

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Download template as JSON file
	$.WS_Form.prototype.template_download = function(template_id) {

		// Build downloader
		var downloader_html = '<form id="wsf-template-downloader" action="' + this.esc_attr(ws_form_settings.url_ajax + 'template/download/json') + '" method="post">';

		downloader_html += '<input type="hidden" name="template_id" value="' + this.esc_attr(template_id) + '" />';
		downloader_html += '<input type="hidden" name="_wpnonce" value="' + this.esc_attr(ws_form_settings.x_wp_nonce) + '" />';
		downloader_html += '<input type="hidden" name="' + this.esc_attr(ws_form_settings.wsf_nonce_field_name) + '" value="' + this.esc_attr(ws_form_settings.wsf_nonce) + '" />';

		downloader_html += '</form>';

		// Inject into body
		var downloader = $('body').append(downloader_html);

		// Submit
		$('#wsf-template-downloader').trigger('submit');

		// Remove
		$('#wsf-template-downloader').remove();
	}

	// Upload JSON file to object
	$.WS_Form.prototype.object_upload = function(obj) {

		// Remember object
		this.upload_obj = obj;

		// Initiate file selector
		$('#wsf-object-upload-file').val('').trigger('click');
	}

	// Update object from field sidebar
	$.WS_Form.prototype.object_data_update_by_meta_key = function(object, object_data, meta_key) {

		// Read meta key config
		var meta_key_config = $.WS_Form.meta_keys[meta_key];

		// Get type to determine how to render it
		var meta_key_type = meta_key_config['type'];

		// Check for key change
		if(typeof($.WS_Form.meta_keys[meta_key]['key']) !== 'undefined') { meta_key = $.WS_Form.meta_keys[meta_key]['key']; }

		// Read meta_value from form elements
		var field_obj = $('#wsf-sidebar-' + object + ' [data-meta-key="' + this.esc_selector(meta_key) + '"]');
		if(!field_obj.length) { return false; }

		// Get meta_value
		var meta_value = this.get_meta_value_by_obj(field_obj, meta_key_type);

		// Set object meta
		if(meta_value !== false) {

			this.set_object_meta_value(object_data, meta_key, meta_value);
		}

		return meta_value;
	}

	// Get meta value by obj
	$.WS_Form.prototype.get_meta_value_by_obj = function(obj, meta_key_type) {

		var meta_value = false;

		switch(meta_key_type) {

			// Skip data grids
			case 'data_grid' :

				break;

			// Convert checkbox meta value to boolean
			case 'checkbox' :

				meta_value = obj.is(':checked') ? obj.val() : '';
				break;

			// Repeater
			case 'repeater' :

				// Get data
				var repeater = this.sidebar_repeater_get(obj);
				var meta_key = obj.attr('data-meta-key');

				meta_value = [];

				// Get column data
				for(var meta_keys_index in repeater.meta_keys) {

					if(!repeater.meta_keys.hasOwnProperty(meta_keys_index)) { continue; }

					var meta_keys_single = repeater.meta_keys[meta_keys_index];

					// Ensure meta key is configured
					if(typeof($.WS_Form.meta_keys[meta_keys_single]) === 'undefined') { continue; }

					// Check for key change
					if(typeof($.WS_Form.meta_keys[meta_keys_single]['key']) !== 'undefined') { meta_keys_single = $.WS_Form.meta_keys[meta_keys_single]['key']; }

					var repeater_row_index = 0;

					$('[name="' + this.esc_selector(meta_key + '_' + meta_keys_single) + '[]"]').each(function() {

						if(typeof(meta_value[repeater_row_index]) === 'undefined') { meta_value[repeater_row_index] = {}; }
						var meta_value_cell = $.WS_Form.this.get_meta_value_by_obj($(this), $(this).attr('data-meta-key-type'));
						if(meta_value_cell !== false) { meta_value[repeater_row_index][meta_keys_single] = meta_value_cell; }
						repeater_row_index++;
					});
				}

				break;

			default :

				meta_value = obj.val();
		}

		// Check for null values (e.g. from unpopulated selects)
		if(meta_value == null) { meta_value = ''; }

		return meta_value;
	}

	// Sidebar - Get HTML
	$.WS_Form.prototype.sidebar_html = function(object, object_id, object_data, object_meta, repeater_meta_key, render_wrappers, render_field_wrappers, render_label, buttons, depth, inits, datetime_type) {

		if(typeof(object_meta.fieldsets) === 'undefined') { return ''; }
		if(typeof(repeater_meta_key) === 'undefined') { repeater_meta_key = false; }
		if(typeof(render_wrappers) === 'undefined') { render_wrappers = true; }
		if(typeof(render_field_wrappers) === 'undefined') { render_field_wrappers = true; }
		if(typeof(render_label) === 'undefined') { render_label = true; }
		if(typeof(buttons) === 'undefined') { buttons = true; }
		if(typeof(depth) === 'undefined') { depth = 1; }
		if(typeof(inits) === 'undefined') { inits = []; }
		if(typeof(datetime_type) === 'undefined') { datetime_type = 'datetime-local'; }

		var repeater = (repeater_meta_key !== false);

		if(repeater) {

			render_wrappers = false;
			render_field_wrappers = false;
			render_label = false;
			buttons = false;
		}

		// Get fieldsets
		var fieldsets = object_meta.fieldsets;

		// Tabs HTML
		var tab_count = 0;
		var sidebar_html_tabs = '';
		if((depth == 1) && !repeater) {

			// Build tab UL
			for(var key in fieldsets) {

				if(!fieldsets.hasOwnProperty(key)) { continue; }

				var fieldset = fieldsets[key];

				sidebar_html_tabs += '<li><a href="#wsf-' + this.esc_attr(object + '-' + key) + '" data-wsf-tab-key="' + this.esc_attr(key) + '">' + fieldset['label'] + '</a></li>';

				tab_count++;
			}

			sidebar_html_tabs = '<ul class="wsf-sidebar-tabs wsf-sidebar-tabs-' + tab_count + '">' + sidebar_html_tabs + "</ul>\n\n";

			if(tab_count > 1) {

				inits.push('tabs');

			} else {

				sidebar_html_tabs = '';
			}
		}

		// Sidebar HTML
		var sidebar_html = '';

		// Build tab content
		var fieldset_index = 0;

		for(var key in fieldsets) {

			if(!fieldsets.hasOwnProperty(key)) { continue; }

			var fieldset = fieldsets[key];

			if((depth == 1) && !repeater) {

				sidebar_html += this.comment_html(this.language('group') + ' - ' + fieldset['label']);
				sidebar_html += '<div id="wsf-' + this.esc_attr(object + '-' + key) + '"' + ((tab_count > 1) ? ' class="wsf-sidebar-tabs-panel"' : '') + '>';
			}

			/* Sidebar classes */
			var sidebar_classes = ['wsf-fieldset'];
			if(typeof(fieldset.class) !== 'undefined') {

				sidebar_classes = sidebar_classes.concat(fieldset.class);
			}

			sidebar_html += render_wrappers ? '<fieldset class="' + this.esc_attr(sidebar_classes.join(' ')) + '">' : '';

			// Render child fieldset
			if(depth > 1 && fieldset.label) {

				sidebar_html += '<legend>' + fieldset.label + '</legend>';
			}

			// Render field type and label
			if((fieldset_index == 0) && (depth == 1) && render_label) {

				sidebar_html += '<div class="wsf-field-wrapper"><label class="wsf-label" id="wsf-' + this.esc_attr(object) + '-label-label" for="wsf-' + this.esc_attr(object) + '-label">Label</label><input name="label" id="wsf-' + this.esc_attr(object) + '-label" class="wsf-field" type="text" value="' + this.esc_attr(object_data.label) + '" maxlength="1024" aria-labelledby="wsf-' + this.esc_attr(object) + '-label-label" /></div>';
				inits.push('label');
			}

			// Render fieldset variables
			if(typeof(fieldset.meta_keys) !== 'undefined') {

				for(var key in fieldset.meta_keys) {

					if(!fieldset.meta_keys.hasOwnProperty(key)) { continue; }

					var sidebar_html_options = '';
					var sidebar_html_options_object = {};

					var meta_key = fieldset.meta_keys[key];

					// Check to see if meta_key is defined
					if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') {

						this.error('error_meta_key', meta_key);
						continue;
					}

					// Read meta key config
					var meta_key_config = $.WS_Form.meta_keys[meta_key];

					// meta_key override
					if(typeof(meta_key_config['key']) !== 'undefined') { meta_key = meta_key_config['key']; }

					// Option check
					if(typeof(meta_key_config['option_check']) !== 'undefined') {

						if($.WS_Form.settings_plugin[meta_key_config['option_check']]) { continue; }
					}

					// Condition
					if(typeof(meta_key_config.condition) !== 'undefined') {

						for(var condition_index in meta_key_config.condition) {

							if(!meta_key_config.condition.hasOwnProperty(condition_index)) { continue; }

							var condition = meta_key_config.condition[condition_index];

							// Push condition to sidebar conditions
							this.sidebar_conditions.push({'logic': condition.logic, 'meta_key': condition.meta_key, 'meta_value': condition.meta_value, 'show': meta_key, 'logic_previous': (typeof(condition.logic_previous) !== 'undefined') ? condition.logic_previous : '&&', 'type': (typeof(condition.type) !== 'undefined') ? condition.type : 'sidebar_meta_key'});

							// Initialize
							inits.push('conditions');
						}
					}

					// Get required
					var meta_key_required = (typeof(meta_key_config['required']) !== 'undefined') ? meta_key_config['required'] : false;

					// Get label
					var meta_key_label = meta_key_config['label'];

					// Get type to determine how to render it
					var meta_key_type = meta_key_config['type'];

					// Get meta value
					var meta_value_fallback = (typeof(meta_key_config['fallback']) !== 'undefined') ? meta_key_config['fallback'] : false;
					var meta_value_default = (meta_value_fallback === false) ? ((typeof(meta_key_config['default']) !== 'undefined') ? meta_key_config['default'] : '') : meta_value_fallback;
					var meta_value = this.get_object_meta_value(object_data, meta_key, meta_value_default, true);

					// Datetime types
					if(meta_key == 'input_type_datetime') { datetime_type = meta_value; }

					// Build help HTML
					if((typeof(meta_key_config['help']) !== 'undefined') && ($.WS_Form.settings_plugin.helper_field_help || (typeof(meta_key_config['help_force']) !== 'undefined' ? meta_key_config['help_force'] : false)) && !repeater) {

						var sidebar_html_help = '<div class="wsf-helper">' + meta_key_config['help'] + '</div>';

					} else {

						var sidebar_html_help = '';
					}

					// Build label HTML
					var sidebar_html_label = (repeater || (typeof(meta_key_label) === 'undefined')) ? '' : '<label class="wsf-label"' + ((meta_key_type != 'repeater') ? ' for="wsf_' + this.esc_attr(meta_key) + '"' : '') + '>' + meta_key_label + (meta_key_required ? ' <span class="wsf-required"></span>' : '') + '</label>';

					// Field attributes - Standard
					var class_field = (typeof(meta_key_config['class_field']) !== 'undefined') ? ' ' + meta_key_config['class_field'] : '';

					var field_attributes = ' ' + (repeater ? ' name="' + this.esc_attr(repeater_meta_key + '_' + meta_key) + '[]"' : 'id="wsf_' + this.esc_attr(meta_key) + '"') + ' class="wsf-field' + this.esc_attr(class_field) + (repeater ? ' wsf-field-small' : '') + '" data-meta-key="' + this.esc_attr(meta_key) + '" data-meta-key-type="' + this.esc_attr(meta_key_type) + '"';

					// Field options
					var meta_key_options = [];

					// Field options - Required setting
					if((typeof(meta_key_config['required_setting']) !== 'undefined') && meta_key_config['required_setting']) {

						var required_setting = true;

						// Check to see if a global setting exists
						if((typeof(meta_key_config['required_setting_global_meta_key']) !== 'undefined') && meta_key_config['required_setting_global_meta_key']) {

							var required_setting_global_meta_key = meta_key_config['required_setting_global_meta_key'];

							// Check to see if a global override setting exists
							if(
								(typeof($.WS_Form.settings_plugin[required_setting_global_meta_key]) !== 'undefined') &&
								($.WS_Form.settings_plugin[required_setting_global_meta_key] != '')
							) {
								// Override placeholder
								meta_key_config.placeholder = this.language('sidebar_placeholder_global_setting');

								// Not required because global setting value exists
								required_setting = false;
							}
						}

						if(required_setting) {

							meta_key_options.push('<li class="wsf-required-setting">' + this.svg('warning') + '</li>');

							field_attributes += ' data-required-setting';

							inits.push('required-setting');
						}
					}

					// Field options - Compatibility
					if((typeof(meta_key_config['compatibility_url']) !== 'undefined') && $.WS_Form.settings_plugin.helper_compatibility && !repeater) {

						meta_key_options.push('<li><a class="wsf-compatibility" href="' + this.esc_attr(meta_key_config['compatibility_url']) + '" target="_blank"' + this.tooltip(this.language('attribute_compatibility'), 'top-right') + ' tabindex="-1">' + this.svg('markup') + '</a></li>');
					}

					// Field options - Calc
					if(
						(object === 'field') &&
						(typeof(meta_key_config['calc']) !== 'undefined')
					) {

						var field_type = object_data.type;
						var field_type_config = $.WS_Form.field_type_cache[field_type];
						var calc_in = (typeof(field_type_config.calc_in) !== 'undefined') ? field_type_config.calc_in : false;
						var calc_for_type = (typeof(meta_key_config.calc_for_type) !== 'undefined') ? meta_key_config.calc_for_type : false;

						// Only show calc icon if this field can accept calculated input and meta key type matches or calc_for_type is not specified
						if(
							calc_in &&
							(
								(calc_for_type === false) ||
								(calc_for_type === meta_key_type)
							)
						) {

							meta_key_options.push('<li><div data-action="wsf-calc" data-option-meta-key="' + this.esc_attr(meta_key) + '"' + this.tooltip(this.language('calc'), 'top-right') + '>' + this.svg('calc') + '</div></li>');
							inits.push('calc');
						}
					}

					// Field options - Select list
					if(typeof(meta_key_config['select_list']) === 'object') {

						meta_key_options.push('<li><div data-action="wsf-select-list" data-option-meta-key="' + this.esc_attr(meta_key) + '"' + this.tooltip(this.language('select_list'), 'top-right') + '>' + this.svg('menu') + '</div></li>');

						inits.push('select-list');
					}

					// Field options - Variable helper
					if(
						((typeof(meta_key_config['variable_helper']) !== 'undefined') && meta_key_config['variable_helper']) ||

						// Legacy support for add-ons that specify select_list = true
						((typeof(meta_key_config['select_list']) !== 'undefined') && (meta_key_config['select_list'] === true)) 
					) {
						meta_key_options.push('<li><div data-action="wsf-variable-helper" data-option-meta-key="' + this.esc_attr(meta_key) + '"' + this.tooltip(this.language('variable_helper'), 'top-right') + '>' + this.svg('hash') + '</div></li>');

						inits.push('variable-helper');
					}

					// Field options - Auto map
					if((typeof(meta_key_config['auto_map']) !== 'undefined') && !repeater) {

						meta_key_options.push('<li><div data-action="wsf-auto-map" data-option-meta-key="' + this.esc_attr(meta_key) + '" data-object="' + this.esc_attr(object) + '" data-object-id="' + this.esc_attr(object_id) + '"' + this.tooltip(this.language('auto_map'), 'top-right') + '>' + this.svg('exchange') + '</div></li>');

						inits.push('auto-map');
					}

					// Field options - API reload
					if((typeof(meta_key_config['reload']) !== 'undefined')) {

						var reload_config = meta_key_config['reload'];

						// Reload attributes
						var reload_attributes = '';
						if(typeof(reload_config['action_id']) !== 'undefined') { reload_attributes += ' data-action-id="' + this.esc_attr(reload_config['action_id']) + '"'; }
						if(typeof(reload_config['action_id_meta_key']) !== 'undefined') { reload_attributes += ' data-action-id-meta-key="' + this.esc_attr(reload_config['action_id_meta_key']) + '"'; }
						if(typeof(reload_config['list_id_meta_key']) !== 'undefined') { reload_attributes += ' data-list-id-meta-key="' + this.esc_attr(reload_config['list_id_meta_key']) + '"'; }
						if(typeof(reload_config['list_sub_id_meta_key']) !== 'undefined') { reload_attributes += ' data-list-sub-id-meta-key="' + this.esc_attr(reload_config['list_sub_id_meta_key']) + '"'; }
						reload_attributes += ' data-method="' + this.esc_attr(reload_config['method']) + '"';
						if(repeater) { reload_attributes += ' data-repeater-meta-key="' + this.esc_attr(repeater_meta_key) + '"'; }

						meta_key_options.push('<li><span data-action="wsf-api-reload"' + reload_attributes + ' data-meta-key-for="' + this.esc_attr(meta_key) + '"' + this.tooltip(this.language('action_api_reload'), 'top-right') + '>' + this.svg('reload') + '</span></li>');

						inits.push('options-action-reload');
					}

					// Field options - Build
					var meta_key_options_html = '';
					if(meta_key_options.length > 0) {

						meta_key_options_html = '<ul class="wsf-meta-key-options">' + meta_key_options.join('') + '</ul>';
					}

					// Default option (Use for inheriting parent values)
					if(typeof(meta_key_config['options_default']) !== 'undefined') {

						// Get default options
						var options_default_label = false;
						var options_default_meta_key = meta_key_config['options_default'];

						// Read default meta key config
						var meta_key_config_default = $.WS_Form.meta_keys[options_default_meta_key];

						// Get default value of default meta key
						var meta_value_default = (typeof(meta_key_config_default.default) !== 'undefined') ? meta_key_config_default.default : false;

						// Determine default meta value from form object meta value
						var options_default_meta_value = this.get_object_meta_value(this.form, options_default_meta_key, meta_value_default);

						if(options_default_meta_value) {

							var options_default_meta_key = $.WS_Form.meta_keys[options_default_meta_key];

							switch(options_default_meta_key['type']) {

								case 'select' :

									if(typeof(options_default_meta_key['options']) !== 'undefined') {

										var options_default_options = options_default_meta_key['options'];

										for(var options_default_options_index in options_default_options) {

											if(!options_default_options.hasOwnProperty(options_default_options_index)) { continue; }
											if(typeof(options_default_options[options_default_options_index]) === 'function') { continue; }

											var options_default_value = options_default_options[options_default_options_index]['value'];
											if(options_default_value != options_default_meta_value) { continue; }

											options_default_label = options_default_options[options_default_options_index]['text'];
										}
									}
									break;

								default :

									options_default_label = options_default_meta_value;
							}
						}

						// Determine if it should be selected
						var option_selected = (meta_value == 'default') ? ' selected' : '';

						// Build option
						sidebar_html_options += '<option value="default"' + option_selected + '>' + this.language('default') + ((options_default_label !== false) ? ' (' + options_default_label + ')' : '') + "</option>\n";
					}

					// Build options HTML
					if(typeof(meta_key_config['options']) !== 'undefined') {

						// Get options
						var meta_key_options = meta_key_config['options'];

						// Option filtering by framework (e.g. to only show label positions available for the current framework)
						var meta_key_options_filter = false;
						if(typeof(meta_key_config['options_framework_filter']) !== 'undefined') {

							// Get options filter from framework
							if(typeof(this.framework[meta_key_config['options_framework_filter']]) !== 'undefined') {

								meta_key_options_filter = this.framework[meta_key_config['options_framework_filter']];
							}
						}

						// Pre-defined options
						switch(meta_key_options) {

							case 'sections' :

								// Check cache
								if(typeof(this.meta_key_options_cache['sections']) !== 'undefined') {

									// Cached version found, so set meta_key_options to copy of that cached data
									var meta_key_options = $.extend(true, [], this.meta_key_options_cache['sections']);
									break;
								}

								// Clear options
								var meta_key_options = [];

								// Filter
								var section_filter_attribute = (typeof(meta_key_config['section_filter_attribute']) !== 'undefined') ? meta_key_config['section_filter_attribute'] : false;

								// Build options
								for(var section_index in this.section_data_cache) {

									if(!this.section_data_cache.hasOwnProperty(section_index)) { continue; }

									var section = this.section_data_cache[section_index];
									
									switch(section_filter_attribute) {

										case 'section_repeatable' :

											var section_repeatable = this.get_object_meta_value(section, 'section_repeatable', false);
											if(!section_repeatable) { continue; }
											break;
									}

									var text = section.label;

									meta_key_options.push({'value': section.id, 'text': text + ' (' + this.language('id') + ': ' + section.id + ')', 'type': section.type});
								}

								// Sort options alphabetically
								meta_key_options.sort(function(a, b) {

									if(a.text < b.text) { return -1; }
									if(a.text > b.text) { return 1; }
									return 0;
								});

								// Store to cache
								this.meta_key_options_cache['sections'] = $.extend(true, [], meta_key_options);

								break;

							case 'fields' :

								// Filter by mappable?
								var fields_filter_mappable = (

									(typeof(meta_key_config['fields_filter_mappable']) === 'undefined') ||
									meta_key_config['fields_filter_mappable']
								);

								// Build cache key
								var cache_key = 'fields' + (fields_filter_mappable ? '_mappable' : '');

								// Check cache
								if(typeof(this.meta_key_options_cache[cache_key]) !== 'undefined') {

									// Cached version found, so set meta_key_options to copy of that cached data
									var meta_key_options = $.extend(true, [], this.meta_key_options_cache[cache_key]);
									break;
								}

								// Build options
								var meta_key_options = this.options_fields(fields_filter_mappable);

								// Store to cache
								this.meta_key_options_cache[cache_key] = $.extend(true, [], meta_key_options);

								break;

							case 'data_source' :

								// Check cache
								if(typeof(this.meta_key_options_cache['data_source']) !== 'undefined') {

									// Cached version found, so set meta_key_options to copy of that cached data
									var meta_key_options = $.extend(true, [], this.meta_key_options_cache['data_source']);
									break;
								}

								// Build options
								var meta_key_options = this.options_data_sources();

								// Store to cache
								this.meta_key_options_cache['data_source'] = $.extend(true, [], meta_key_options);

								break;

							case 'action_api_populate' :

								var meta_key_options = [];

								break;

							case 'ecommerce_cart_price_type' :

								// Clear options
								var meta_key_options = [];

								// Price types
								var cart_price_type_other = false;
								for(var cart_price_type in $.WS_Form.ecommerce.cart_price_types) {

									if(!$.WS_Form.ecommerce.cart_price_types.hasOwnProperty(cart_price_type)) { continue; }

									var cart_price_type_config = $.WS_Form.ecommerce.cart_price_types[cart_price_type];

									// Check fields to see if this price type has already been assigned
									var disabled = false;
									if(cart_price_type !== meta_value) {

										for(var fields_index in $.WS_Form.this.field_data_cache) {

											if(!$.WS_Form.this.field_data_cache.hasOwnProperty(fields_index)) { continue; }

											var field = $.WS_Form.this.field_data_cache[fields_index];

											var ecommerce_cart_price_type = this.get_object_meta_value(field, 'ecommerce_cart_price_type', false);

											if(ecommerce_cart_price_type === cart_price_type) {

												disabled = true;
												break;
											}
										}
									}

									if(cart_price_type == 'other') {

										var cart_price_type_other = {'value': cart_price_type, 'text': cart_price_type_config['label'], 'disabled' : false};
										continue;
									}

									meta_key_options.push({'value': cart_price_type, 'text': cart_price_type_config['label'], 'disabled' : disabled});
								}

								// Sort options alphabetically
								meta_key_options.sort(function(a, b) {

									if(a.text < b.text) { return -1; }
									if(a.text > b.text) { return 1; }
									return 0;
								});

								// Add other
								if(cart_price_type_other !== false) {

									meta_key_options.push(cart_price_type_other);
								}

								break;
							default :

								var meta_key_options = $.extend(true, [], meta_key_options);
						}

						// Insert blank option
						if(typeof(meta_key_config['options_blank']) !== 'undefined') {

							meta_key_options.unshift({'value': '', 'text': meta_key_config['options_blank'], 'disabled_never': true});
						}

						// Filters
						var fields_filter_type = (typeof(meta_key_config['fields_filter_type']) !== 'undefined') ? meta_key_config['fields_filter_type'] : false;
						var fields_filter_type_exclude = (typeof(meta_key_config['fields_filter_type_exclude']) !== 'undefined') ? meta_key_config['fields_filter_type_exclude'] : false;
						var fields_filter_attribute = (typeof(meta_key_config['fields_filter_attribute']) !== 'undefined') ? meta_key_config['fields_filter_attribute'] : false;
						var fields_filter_include_self = (typeof(meta_key_config['fields_filter_include_self']) !== 'undefined') ? meta_key_config['fields_filter_include_self'] : false;

						// Build options
						var optgroup_last = false;
						for(var meta_key_option_index in meta_key_options) {

							if(!meta_key_options.hasOwnProperty(meta_key_option_index)) { continue; }
							if(typeof(meta_key_options[meta_key_option_index]) === 'function') { continue; }

							// Option single
							var meta_key_option = meta_key_options[meta_key_option_index];

							// Filter - By Field Type
							if(fields_filter_type !== false) {

								// Filter by field type
								if(typeof(meta_key_option.type) !== 'undefined') {

									// Skip option if it does not match filter requirements
									if(fields_filter_type.indexOf(meta_key_option.type) === -1) { continue; }

									// Skip same field
									if(
										!fields_filter_include_self &&
										(meta_key_option.value == object_id)
									) {
										continue;
									}
								}
							}

							// Filter - By Field Type - Exclude
							if(fields_filter_type_exclude !== false) {

								// Filter by field type
								if(typeof(meta_key_option.type) !== 'undefined') {

									// Skip option if it does not match filter requirements
									if(fields_filter_type_exclude.indexOf(meta_key_option.type) !== -1) { continue; }

									// Skip same field
									if(
										!fields_filter_include_self &&
										(meta_key_option.value == object_id)

									) { continue; }
								}
							}

							// Filter - By Field Type Attribute
							if(fields_filter_attribute !== false) {

								// Read field
								if(
									(typeof(meta_key_option.type) !== 'undefined') &&
									(typeof($.WS_Form.field_type_cache[meta_key_option.type]) !== 'undefined')
								) {

									// Get field config
									var field_config = $.WS_Form.field_type_cache[meta_key_option.type];

									// Skip option if it does not match filter requirements
									var attribute_found = fields_filter_attribute.find(function(attribute) {

										return (

											(typeof(field_config[attribute]) !== 'undefined') &&
											field_config[attribute]
										);
									});
									if(!attribute_found) { continue; }
								}
							}

							// Option value
							var option_value = meta_key_option.value;

							// Filter array
							if((meta_key_options_filter !== false) && (meta_key_options_filter.indexOf(option_value) == -1)) { continue; }

							// Option text
							var option_text = meta_key_option.text;

							// Determine if it should be selected
							if(meta_value === null) { meta_value = ''; }
							var option_selected = ((typeof(meta_value) === 'object') ? (meta_value.indexOf(option_value) !== -1) : (meta_value == option_value)) ? ' selected' : '';

							// Determine if it should be disabled
							var meta_key_option_disabled = (typeof(meta_key_option.disabled) !== 'undefined') ? meta_key_option.disabled : false;
							var option_disabled = meta_key_option_disabled ? ' disabled' : '';

							// Determine if it should be disabled always (prevents unique functionality from not disabling fields)
							var meta_key_option_disabled_always = (typeof(meta_key_option.disabled_always) !== 'undefined') ? meta_key_option.disabled_always : false;
							var option_disabled_always = meta_key_option_disabled_always ? ' data-disabled-always' : '';

							// Determine if it should never be disabled (prevents 'Select...' from being disabled)
							var meta_key_option_disabled_never = (typeof(meta_key_option.disabled_never) !== 'undefined') ? meta_key_option.disabled_never : false;
							var option_disabled_never = meta_key_option_disabled_never ? ' data-disabled-never' : '';

							// Check for optgroup
							var optgroup = (typeof(meta_key_option.optgroup) !== 'undefined') ? meta_key_option.optgroup : false;
							if(optgroup !== optgroup_last) {

								// Close last optgroup
								if(optgroup_last !== false) {

									sidebar_html_options += '</optgroup>';
								}

								// Open optgroup
								sidebar_html_options += '<optgroup label="' + this.esc_attr(optgroup) + '">';

								// Remember optgroup
								optgroup_last = optgroup;
							}
							// Build option
							sidebar_html_options += '<option value="' + this.esc_attr(option_value) + '"' + option_selected + option_disabled + option_disabled_always + option_disabled_never + '>' + this.esc_html(option_text) + "</option>\n";
							sidebar_html_options_object[this.esc_html(option_value)] = this.esc_html(option_text);
						}

						// Close last optgroup
						if(optgroup_last !== false) {

							sidebar_html_options += '</optgroup>';
						}
					}

					// Select - Legacy support
					if(meta_key_type == 'select_ajax') {

						meta_key_type = 'select';
						meta_key_config['select2'] = true;
					}

					var select2 = (typeof(meta_key_config['select2']) !== 'undefined') ? meta_key_config['select2'] : false;

					if(select2) {

						// Add data-wsf-select2 to field attributes
						field_attributes += ' data-wsf-select2';

						var select_ajax_method_search = (typeof(meta_key_config['select_ajax_method_search']) !== 'undefined') ? meta_key_config['select_ajax_method_search'] : false;
						var select_ajax_method_cache = (typeof(meta_key_config['select_ajax_method_cache']) !== 'undefined') ? meta_key_config['select_ajax_method_cache'] : false;
						var select_ajax_placeholder = (typeof(meta_key_config['select_ajax_placeholder']) !== 'undefined') ? meta_key_config['select_ajax_placeholder'] : false;

						// AJAX
						if(select_ajax_method_search) {

							// Add field attribute
							field_attributes += ' data-select-ajax-method-search="' + this.esc_attr(select_ajax_method_search) + '"';
							field_attributes += select_ajax_method_cache ? ' data-select-ajax-method-cache="' + this.esc_attr(select_ajax_method_cache) + '"' : '';
							field_attributes += select_ajax_placeholder ? ' data-select-ajax-placeholder="' + this.esc_attr(select_ajax_placeholder) + '"' : '';
							if(meta_value) { field_attributes += ' data-select-ajax-id="'  + this.esc_attr(meta_value) + '"'; }

							// Initialize
							inits.push('select2-ajax');

						} else {

							// Initialize
							inits.push('select2');
						}

						// Tags (Pills)
						var select_tags = (typeof(meta_key_config['select2_tags']) !== 'undefined') ? meta_key_config['select2_tags'] : false;
						field_attributes += select_tags ? ' data-tags="true"' : '';

						// Build default options
						if(!select_ajax_method_cache && (typeof(meta_value) === 'object')) {

							for(var meta_value_index in meta_value) {

								if(!meta_value.hasOwnProperty(meta_value_index)) { continue; }
								if(typeof(meta_value[meta_value_index]) === 'function') { continue; }

								var meta_value_single = meta_value[meta_value_index];

								// If it doesn't already exist in the options
								if(typeof(sidebar_html_options_object[meta_value_single]) === 'undefined') {

									// Build option
									sidebar_html_options += '<option value="' + this.esc_attr(meta_value_single) + '" selected>' + this.esc_html(meta_value_single) + "</option>\n";
									sidebar_html_options_object[meta_value_single] = meta_value_single;
								}
							}
						}
					}

					// Build select number options
					if(meta_key_type == 'select_number') {

						// Get minimum and maximum values
						var minimum = (typeof(meta_key_config['minimum']) !== 'undefined') ? meta_key_config['minimum'] : 1;
						var maximum = (typeof(meta_key_config['maximum']) !== 'undefined') ? meta_key_config['maximum'] : 100;
						if(maximum == 'framework_column_count') { maximum = ($.WS_Form.settings_plugin.framework_column_count - 1); }

						for(var option_value = minimum; option_value <= maximum; option_value++) {

							// Determine if it should be selected
							var option_selected = (meta_value == option_value) ? ' selected' : '';

							// Build option
							sidebar_html_options += '<option value="' + this.esc_attr(option_value) + '"' + option_selected + '>' + this.esc_html(option_value) + "</option>\n";
						}

						// Change to normal select
						meta_key_type = 'select';
					}

					// Field attributes - Mask placeholder
					if(typeof(meta_key_config.mask_placeholder) !== 'undefined') {

						var placeholder = meta_key_config.mask_placeholder;

						// Check for custom invalid feedback mask (This allows for invalid feedback masks per field type)
						if(meta_key === 'invalid_feedback') {

							var field_type = object_data.type;
							var field_type_config = $.WS_Form.field_type_cache[field_type];
							placeholder = (typeof(field_type_config['invalid_feedback']) !== 'undefined') ? field_type_config['invalid_feedback'] : placeholder;
						}

						// Add data-placeholder to field attributes
						field_attributes += ' data-placeholder="' + this.esc_attr(placeholder) + '"';

						// Initialize
						inits.push('placeholders');
					}

					// Field attributes - Placeholder
					if(typeof(meta_key_config.placeholder) !== 'undefined') {

						// Add placeholder to field attributes
						field_attributes += ' placeholder="' + this.esc_attr(meta_key_config.placeholder) + '"';
					}

					// Field attributes - Multiple
					if(typeof(meta_key_config.multiple) !== 'undefined') {

						// Add multiple to field attributes
						field_attributes += ' multiple';
					}

					// Field attributes - Rows
					if(typeof(meta_key_config.rows) !== 'undefined') {

						// Add rows to field attributes
						field_attributes += ' rows="' + this.esc_attr(meta_key_config.rows) + '"';
					}

					// Field attributes - Data change
					if((typeof(meta_key_config.data_change) !== 'undefined')) {

						// Add reload on change to field attributes
						field_attributes += ' data-change-event="' + this.esc_attr(meta_key_config.data_change.event) + '"';
						field_attributes += ' data-change-action="' + this.esc_attr(meta_key_config.data_change.action) + '"';
					}

					// Field attributes - Fields toggle
					if((typeof(meta_key_config.fields_toggle) !== 'undefined')) {

						// Add fields toggle to field attributes
						field_attributes += ' data-fields-toggle';
					}

					// Field attributes - Min, Max, Step
					if(typeof(meta_key_config.min) !== 'undefined') { field_attributes += ' min="' + parseFloat(meta_key_config.min) + '"'; }
					if(typeof(meta_key_config.max) !== 'undefined') { field_attributes += ' max="' + parseFloat(meta_key_config.max) + '"'; }
					if(typeof(meta_key_config.step) !== 'undefined') { field_attributes += ' step="' + parseFloat(meta_key_config.step) + '"'; }

					// Field attributes - Options - Action
					if(((typeof(meta_key_config.options_action_id) !== 'undefined') || (typeof(meta_key_config.options_action_id_meta_key) !== 'undefined')) && (typeof(meta_key_config.options_action_api_populate) !== 'undefined')) {

						if(typeof(meta_key_config.options_action_id) !== 'undefined') { field_attributes += ' data-options-action-id="' + this.esc_attr(meta_key_config.options_action_id) + '"'; }
						if(typeof(meta_key_config.options_action_id_meta_key) !== 'undefined') { field_attributes += ' data-options-action-id-meta-key="' + this.esc_attr(meta_key_config.options_action_id_meta_key) + '"'; }
						if(typeof(meta_key_config.options_list_id_meta_key) !== 'undefined') { field_attributes += ' data-options-list-id-meta-key="' + this.esc_attr(meta_key_config.options_list_id_meta_key) + '"'; }
						if(typeof(meta_key_config.options_list_sub_id_meta_key) !== 'undefined') { field_attributes += ' data-options-list-sub-id-meta-key="' + this.esc_attr(meta_key_config.options_list_sub_id_meta_key) + '"'; }
						field_attributes += ' data-options-action-api-populate="' + this.esc_attr(meta_key_config.options_action_api_populate) + '"';
						field_attributes += ' data-object="' + this.esc_attr(object) + '"';
						field_attributes += ' data-object-id="' + this.esc_attr(object_id) + '"';
						if(repeater) { field_attributes += ' data-repeater-meta-key="' + this.esc_attr(repeater_meta_key) + '"'; }

						inits.push('options-action');
					}

					// Field attributes - Required
					if(meta_key_required) { field_attributes += ' required'; }

					// Field attributes - Additional
					if(typeof(meta_key_config.attributes) !== 'undefined') {

						for(var attribute in meta_key_config.attributes) {

							if(!meta_key_config.attributes.hasOwnProperty(attribute)) { continue; }

							var attribute_value = meta_key_config.attributes[attribute];
							field_attributes += ' ' + attribute + '="' + this.esc_attr(attribute_value) + '"';
						}
					}

					// Field attributes - Column toggle
					if((typeof(meta_key_config.column_toggle_meta_key) !== 'undefined') && (typeof(meta_key_config.column_toggle_column_id) !== 'undefined')) {

						field_attributes += ' data-column-toggle-meta-key="' + this.esc_attr(meta_key_config.column_toggle_meta_key) + '"';
						field_attributes += ' data-column-toggle-column-id="' + this.esc_attr(meta_key_config.column_toggle_column_id) + '"';

						inits.push('column-toggle');
					}

					// Add meta key to cache
					if(!repeater) {

						this.object_meta_cache.push({'meta_key': meta_key, 'meta_key_type': meta_key_type, 'meta_value': meta_value});
					}

					// Check to see if this field should render if groups_group is present
					if(typeof(meta_key_config['show_if_groups_group']) !== 'undefined') {

						var field_type = object_data.type;
						var field_type_config = $.WS_Form.field_type_cache[field_type];

						var data_source = (typeof(field_type_config['data_source']) !== 'undefined') ? field_type_config['data_source'] : false;

						// Get data source ID
						if(
							(typeof(data_source.id) !== 'undefined') &&
							(typeof($.WS_Form.meta_keys[data_source.id]) !== 'undefined')
						) {

							// Read meta key config
							var data_source_meta_key_config = $.WS_Form.meta_keys[data_source.id]

							// Check if data grid has groups_group set
							var groups_group = (typeof(data_source_meta_key_config['groups_group']) !== 'undefined') ? data_source_meta_key_config['groups_group'] : false;

							// Render as hidden value
							if(groups_group !== meta_key_config['show_if_groups_group']) {

								meta_key_type = 'hidden';
								meta_value = '';
							}
						}
					}

					// Process by meta key type
					switch(meta_key_type) {

						// Checkbox
						case 'checkbox' :

							var meta_key_checked = (meta_value == 'on') ? ' checked' : '';
							var sidebar_html_field = meta_key_options_html + '<input type="checkbox"' + field_attributes + meta_key_checked + ' />' + sidebar_html_label + sidebar_html_help;
							break;

						// Select
						// RegEx Filter
						case 'select' :
						case 'regex_filter' :
						case 'data_grid_field' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<select' + field_attributes + '>' + sidebar_html_options + '</select>' + sidebar_html_help;
							break;

						// Text Editor
						case 'text_editor' :

							field_attributes += (typeof(meta_key_config.css) !== 'undefined') ? ' data-helper-css="' + this.esc_attr(meta_key_config.css) + '"' : '';
							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<textarea data-text-editor="true"' + field_attributes + '>' + this.esc_html(meta_value, true) + '</textarea>' + sidebar_html_help;
							inits.push('text-editor');
							break;

						// HTML Editor
						case 'html_editor' :

							field_attributes += (typeof(meta_key_config.mode) !== 'undefined') ? ' data-html-editor-mode="' + this.esc_attr(meta_key_config.mode) + '"' : '';
							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<textarea data-html-editor="true"' + field_attributes + '>' + this.esc_html(meta_value, true) + '</textarea>' + sidebar_html_help;
							inits.push('html-editor');
							break;

						// Textarea
						case 'textarea' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<textarea' + field_attributes + '>' + this.esc_html(meta_value, true) + '</textarea>' + sidebar_html_help;
							break;

						// Number
						case 'number' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="number"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />' + sidebar_html_help;
							inits.push('number');
							break;

						// Range
						case 'range' :

							field_attributes += (typeof(meta_key_config.range_value_mask) !== 'undefined') ? ' data-range-value-mask="' + this.esc_attr(meta_key_config.range_value_mask) + '"' : '';
							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="range"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' /><small id="wsf_' + this.esc_attr(meta_key) + '_range_value"></small>' + sidebar_html_help;
							inits.push('range');
							break;

						// Password
						case 'password' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="password"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' autocomplete="new-password" />' + sidebar_html_help;
							break;

						// Email
						case 'email' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="email"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />' + sidebar_html_help;
							break;

						// Tel
						case 'tel' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="tel"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />' + sidebar_html_help;
							break;

						// URL
						case 'url' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="url"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />' + sidebar_html_help;
							break;

						// Date/Time
						case 'datetime' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="' + this.esc_attr(datetime_type) + '"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />' + sidebar_html_help;
							inits.push('datetime');
							break;

						// Color
						case 'color' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="color"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />' + sidebar_html_help;
							break;

						// Breakpoint sizes
						case 'breakpoint_sizes' :

							var sidebar_html_field = '<div class="wsf-breakpoint-sizes" data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '"></div>';
							inits.push('breakpoint-sizes');
							break;

						// Orientation Breakpoint sizes
						case 'orientation_breakpoint_sizes' :

							var sidebar_html_field = '<div class="wsf-orientation-breakpoint-sizes" data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '"></div>';
							inits.push('orientation-breakpoint-sizes');
							break;

						// Data grid
						case 'data_grid' :

							var sidebar_html_field = '<fieldset class="wsf-fieldset wsf-data-grid" data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '"></fieldset>';
							inits.push('data-grid');
							break;

						// Field select
						case 'field_select' :

							var sidebar_html_field = '<div class="wsf-field-selector"></div>';
							inits.push('field-select');
							break;

						case 'section_select' :

							var sidebar_html_field = '<div class="wsf-section-selector"></div>';
							inits.push('section-select');
							break;

						// Form history
						case 'form_history' :

							var sidebar_html_field = '';
							inits.push('form-history');
							break;

						// Knowledge Base
						case 'knowledgebase' :

							var sidebar_html_field = '';
							break;

						// Contact
						case 'contact' :

							var sidebar_html_field = '';
							inits.push('contact');
							break;

						// Repeater
						case 'repeater' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<div id="wsf_' + this.esc_attr(meta_key) + '" class="wsf-repeater" data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '" data-meta-key-type="' + this.esc_attr(meta_key_type) + '"></div>' + sidebar_html_help;
							inits.push('repeater');
							break;

						// Button
						case 'button' :

							var sidebar_html_field = '<button class="wsf-button wsf-button-full' + class_field + '" id="wsf_' + this.esc_attr(meta_key) + '" data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '" data-meta-key-type="' + this.esc_attr(meta_key_type) + '">' + this.esc_html(meta_key_label) + '</button>';
							break;

						// Conversational - Preview
						case 'conversational_preview' :

							var sidebar_html_field = '<a data-action="wsf-conversational-preview" data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '" data-meta-key-type="' + this.esc_attr(meta_key_type) + '" class="wsf-button wsf-button-small" href="' + this.esc_attr(ws_form_settings.conversational_preview_url) + '" target="wsf-preview-' + this.esc_attr(this.form_id) + '"><svg height="16" width="16" viewBox="0 0 16 16"><path d="M8 3.9c-6.7 0-8 5.1-8 5.1s2.2 4.1 7.9 4.1 8.1-4 8.1-4-1.3-5.2-8-5.2zM5.3 5.4c0.5-0.3 1.3-0.3 1.3-0.3s-0.5 0.9-0.5 1.6c0 0.7 0.2 1.1 0.2 1.1l-1.1 0.2c0 0-0.3-0.5-0.3-1.2 0-0.8 0.4-1.4 0.4-1.4zM7.9 12.1c-4.1 0-6.2-2.3-6.8-3.2 0.3-0.7 1.1-2.2 3.1-3.2-0.1 0.4-0.2 0.8-0.2 1.3 0 2.2 1.8 4 4 4s4-1.8 4-4c0-0.5-0.1-0.9-0.2-1.3 2 0.9 2.8 2.5 3.1 3.2-0.7 0.9-2.8 3.2-7 3.2z"></path></svg> ' + this.esc_html(meta_key_label) + '</a>';
							inits.push('conversational-preview');
							break;

						// Conversational - Style
						case 'conversational_style' :

							var sidebar_html_field = '<a data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '" data-meta-key-type="' + this.esc_attr(meta_key_type) + '" class="wsf-button wsf-button-small" href="' + this.esc_attr(ws_form_settings.conversational_style_url) + '" target="wsf-preview-' + this.esc_attr(this.form_id) + '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18.2 16.6c0-1-.2-.9-.1-2 0-1.3 1.6-2.8 3.2.3.9 1.8 2.4.7 2.6-.2.5-2.7-.3-5.9-2.8-9C16.7.3 9-1.2 3.9 2.4-1.2 5.9-1.3 14 3.4 18.7c4.2 4.2 10.5 6.8 13.7 3.2 1.5-2.4 1.2-3 1.1-5.3zM6.7 3.9c.9-.6 2.2-.4 2.8.5.7.9.4 2.1-.5 2.8-.9.6-2.2.4-2.8-.5s-.4-2.2.5-2.8zm-4 8.2c-.6-.9-.4-2.2.5-2.8.9-.7 2.2-.4 2.8.5.6.9.4 2.2-.5 2.8-.9.7-2.1.5-2.8-.5zm9.9-5.3c-.6-.9-.4-2.2.5-2.8.9-.6 2.2-.4 2.8.5.6.9.4 2.2-.5 2.8-.9.7-2.2.5-2.8-.5zM5.7 17.3c-.6-.9-.4-2.2.5-2.8.9-.7 2.2-.4 2.8.5.6.9.4 2.2-.5 2.8-.9.7-2.1.4-2.8-.5zM12 17c.9-.6 2.2-.4 2.8.5.6.9.4 2.2-.5 2.8-.9.6-2.2.4-2.8-.5-.6-.9-.4-2.2.5-2.8z"/></svg> ' + this.esc_html(meta_key_label) + '</a>';
							break;

						// Conversational - Customize (Legacy)
						case 'conversational_customize' :

							var sidebar_html_field = '<a data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '" data-meta-key-type="' + this.esc_attr(meta_key_type) + '" class="wsf-button wsf-button-small" href="' + this.esc_attr(ws_form_settings.conversational_customize_url) + '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18.2 16.6c0-1-.2-.9-.1-2 0-1.3 1.6-2.8 3.2.3.9 1.8 2.4.7 2.6-.2.5-2.7-.3-5.9-2.8-9C16.7.3 9-1.2 3.9 2.4-1.2 5.9-1.3 14 3.4 18.7c4.2 4.2 10.5 6.8 13.7 3.2 1.5-2.4 1.2-3 1.1-5.3zM6.7 3.9c.9-.6 2.2-.4 2.8.5.7.9.4 2.1-.5 2.8-.9.6-2.2.4-2.8-.5s-.4-2.2.5-2.8zm-4 8.2c-.6-.9-.4-2.2.5-2.8.9-.7 2.2-.4 2.8.5.6.9.4 2.2-.5 2.8-.9.7-2.1.5-2.8-.5zm9.9-5.3c-.6-.9-.4-2.2.5-2.8.9-.6 2.2-.4 2.8.5.6.9.4 2.2-.5 2.8-.9.7-2.2.5-2.8-.5zM5.7 17.3c-.6-.9-.4-2.2.5-2.8.9-.7 2.2-.4 2.8.5.6.9.4 2.2-.5 2.8-.9.7-2.1.4-2.8-.5zM12 17c.9-.6 2.2-.4 2.8.5.6.9.4 2.2-.5 2.8-.9.6-2.2.4-2.8-.5-.6-.9-.4-2.2.5-2.8z"/></svg> ' + this.esc_html(meta_key_label) + '</a>';
							break;

						// Conversational - View
						case 'conversational_view' :

							// Build view URL
							var conversational_slug = this.get_object_meta_value(object_data, 'conversational_slug');
							if(conversational_slug == '') { conversational_slug = 'wsf-conversational-form-' + this.form_id; }
							var conversational_view_url = ws_form_settings.url_site + '/' + conversational_slug + '/';

							var sidebar_html_field = '<a data-action="wsf-conversational-view" data-object="' + this.esc_attr(object) + '" data-id="' + this.esc_attr(object_id) + '" data-meta-key="' + this.esc_attr(meta_key) + '" data-meta-key-type="' + this.esc_attr(meta_key_type) + '" href="' + this.esc_attr(conversational_view_url) + '" target="wsf-preview-conversational-' + this.esc_attr(this.form_id) + '">' + this.esc_html(conversational_view_url) + '</a>';
							break;
						// Image
						case 'image' :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<div class="wsf-field-inline"><input type="text"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />';
							sidebar_html_field += '<button class="wsf-button wsf-button-primary" data-for="wsf_' + this.esc_attr(meta_key) + '">' + this.language('sidebar_button_image') + '</button></div>' + sidebar_html_help;
							inits.push('image');
							break;

						// Media
						case 'media' :

							// Get media filename
							if(meta_value !== '') {

								try {

									var media = JSON.parse(meta_value);

								} catch(e) {

									var media = {id: 0, filename: ''};
								}

								var media_filename = typeof(media.filename) ? media.filename : '';

							} else {

								var media_filename = '';
							}

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<div class="wsf-field-inline"><input type="text" class="wsf-field wsf-field-small" value="' + this.esc_attr(media_filename) + '" readonly /><input type="hidden"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />';
							sidebar_html_field += '<button class="wsf-button wsf-button-small" data-for="wsf_' + meta_key + '">' + this.language('sidebar_button_media') + '</button></div>' + sidebar_html_help;
							inits.push('media');
							break;

						// HTML
						case 'html' :

							var html = (typeof(meta_key_config['html']) !== 'undefined') ? meta_key_config['html'] : '';
							var sidebar_html_field = '<div class="wsf-sidebar-html"' + field_attributes + '>' + html + '</div>';
							break;

						// Hidden
						case 'hidden' :

							var sidebar_html_field = '<input type="hidden"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />';
							break;

						// Default (Text)
						default :

							var sidebar_html_field = meta_key_options_html + sidebar_html_label + '<input type="text"' + field_attributes + ' value="' + this.esc_attr(meta_value) + '"' + ' />' + sidebar_html_help;
					}

					// Indent HTML
					var indent_html = (typeof(meta_key_config['indent']) !== 'undefined') ? ' wsf-field-indent' : '';

					// Add fieldset field HTML
					var field_wrapper = (typeof(meta_key_config['field_wrapper']) !== 'undefined') ? meta_key_config['field_wrapper'] : true;
					if(render_field_wrappers && field_wrapper) {

						var class_wrapper = (typeof(meta_key_config['class_wrapper']) !== 'undefined') ? ' ' + meta_key_config['class_wrapper'] : '';

						sidebar_html += '<div class="wsf-field-wrapper' + class_wrapper + indent_html + '">' + sidebar_html_field + "</div>\n";

					} else {

						sidebar_html += sidebar_html_field + "\n";
					}
				}
			}

			// Render child fieldset
			if(typeof(fieldset.fieldsets) !== 'undefined') {

				var sidebar_return = this.sidebar_html(object, object_id, object_data, fieldset, repeater, render_wrappers, render_field_wrappers, render_label, buttons, (depth + 1), inits, datetime_type);
				sidebar_html += sidebar_return.html;
				inits = inits.concat(sidebar_return.inits);
			}

			sidebar_html += render_wrappers ? '</fieldset>' : '';

			if((depth == 1) && !repeater) {

				sidebar_html += '</div>';

				sidebar_html += this.comment_html(this.language('group') + ' - ' + fieldset['label'], true);
			}

			fieldset_index++;
		}

		var sidebar_html_buttons = (buttons !== false) ? this.sidebar_buttons_html(buttons) : '';

		return {'html_tabs': sidebar_html_tabs, 'html': sidebar_html, 'html_buttons': sidebar_html_buttons, 'inits': inits};
	}

	// Sidebar - Options - Mappable Fields
	$.WS_Form.prototype.options_fields = function(fields_filter_mappable) {

		var options = [];

		for(var group_index in this.form.groups) {

			if(!this.form.groups.hasOwnProperty(group_index)) { continue; }

			var group = this.form.groups[group_index];

			options.push({'value': 'group-' + group.id, 'text': group.label + ' (' + this.language('id') + ': ' + group.id + ')', 'type': 'group', 'disabled': true, 'disabled_always': true});

			var sections = group.sections;

			for(var section_index in sections) {

				if(!sections.hasOwnProperty(section_index)) { continue; }

				var section = sections[section_index];

				options.push({'value': 'section-' + section.id, 'text': '- ' + section.label + ' (' + this.language('id') + ': ' + section.id + ')', 'type': 'section', 'disabled': true, 'disabled_always': true});

				var fields = section.fields;

				for(var field_index in fields) {

					if(!fields.hasOwnProperty(field_index)) { continue; }

					var field = fields[field_index];

					var field_type_config = $.WS_Form.field_type_cache[field.type];

					if(fields_filter_mappable) {

						var mappable = (typeof(field_type_config['mappable'])) ? field_type_config['mappable'] : false;
						if(!mappable) { continue; }
					}

					options.push({'value': field.id, 'text': '-- ' + field.label + ' (' + this.language('id') + ': ' + field.id + ')', 'type': field.type});
				}
			}
		}

		return options;
	}

	// Sidebar - Options - Data Sources
	$.WS_Form.prototype.options_data_sources = function() {

		var options = [];

		for(var data_source_id in $.WS_Form.data_sources) {

			if(!$.WS_Form.data_sources.hasOwnProperty(data_source_id)) { continue; }

			var data_source = $.WS_Form.data_sources[data_source_id];
			if(typeof(data_source.label) === 'undefined') { continue; }

			options.push({'value': data_source_id, 'text': data_source.label});
		}

		return options;
	}

	// Sidebar - Clear caches (Done each time a sidebar is rendered)
	$.WS_Form.prototype.sidebar_cache_clear = function(obj) {

		// Reset object meta cache (Used later to recall the meta keys that need saving)
		this.object_meta_cache = [];

		// Reset sidebar_conditions
		this.sidebar_conditions = [];
		obj.removeAttr('data-sidebar-conditions-init');

		// Clear options cache
		this.meta_key_options_cache = [];
	}

	// Sidebar - Set up input that need to match datetime type selected
	$.WS_Form.prototype.sidebar_buttons_html = function(buttons) {

		if((typeof(buttons) === 'undefined') || (buttons === true)) { buttons = [

				{'class': 'wsf-button-primary', 'action': 'wsf-sidebar-save-close', 'label': this.language('save_and_close')},
				{'class': 'wsf-button-primary', 'action': 'wsf-sidebar-save', 'label': this.language('save')},
				{'class': '', 'action': 'wsf-sidebar-cancel', 'label': this.language('cancel')},
			];
		}

		if(buttons === false) { return ''; }

		var return_html = '<div class="wsf-sidebar-footer">';

		// Buttons
		return_html += '<ul class="wsf-list-inline">';

		for(var button_index in buttons) {

			if(!buttons.hasOwnProperty(button_index)) { continue; }

			var button = buttons[button_index];

			if(typeof(button.class) === 'undefined') { button.class = ''; }
			if(typeof(button.action) === 'undefined') { continue; }
			if(typeof(button.label) === 'undefined') { continue; }
			if(typeof(button.disabled) === 'undefined') { button.disabled = false; }
			if(typeof(button.right) === 'undefined') { button.right = false; }

			return_html += '<li' + (button.right ? ' class="wsf-button-right"' : '') + '><button class="wsf-button wsf-button-small' + ((button.class != '') ? ' ' + this.esc_attr(button.class) : '') + '" data-action="' + this.esc_attr(button.action) + '"' + ((button.id !== undefined) ? ' data-id="' + this.esc_attr(button.id) + '"' : '') + (button.disabled ? ' disabled' : '') + '>' + button.label + '</button></li>';
		}

		return_html += '</ul>';
		return_html += '</div>';

		return return_html;
	}

	// Sidebar - Init
	$.WS_Form.prototype.sidebar_inits = function(inits, obj_sidebar_outer, obj_sidebar_inner, object_data) {

		if(typeof(inits) !== 'object') { inits = []; }
		if(!inits.length) { return; }
		if(typeof(obj_sidebar_inner) === 'undefined') { obj_sidebar_inner = obj_sidebar_outer; }
		if(typeof(object_data) === 'undefined') { object_data = []; }

		var mode = $.WS_Form.settings_plugin.mode;
		var mode_basic = (mode == 'basic');

		// Initialize tabs
		if(inits.indexOf('tabs') != -1) {

			this.sidebar_tabs_init(obj_sidebar_outer, obj_sidebar_inner);
		}

		// Initialize TinyMCE
		if(inits.indexOf('text-editor') != -1) {

			this.sidebar_tinymce_init(obj_sidebar_inner);
		}

		// Initialize HTML editors
		if(inits.indexOf('html-editor') != -1) {

			this.sidebar_html_editor_init(obj_sidebar_inner);
		}

		// Initialize repeaters
		if(inits.indexOf('repeater') != -1) {

			this.sidebar_repeater_init(obj_sidebar_inner);
		}

		// Initialize data grids
		if(inits.indexOf('data-grid') != -1) {

			this.sidebar_data_grids_init(obj_sidebar_outer);
		}

		// Initialize field selector
		if(inits.indexOf('field-select') != -1) {

			this.sidebar_field_select_init(obj_sidebar_outer);
		}

		// Initialize section selector
		if(inits.indexOf('section-select') != -1) {

			this.sidebar_section_select_init(obj_sidebar_outer);
		}

		// Initialize form history
		if(inits.indexOf('form-history') != -1) {

			this.sidebar_form_history_init();
		}

		// Initialize breakpoint sizes
		if(inits.indexOf('breakpoint-sizes') != -1) {

			this.sidebar_breakpoint_sizes(obj_sidebar_outer);
		}

		// Initialize orientation breakpoint sizes
		if(inits.indexOf('orientation-breakpoint-sizes') != -1) {

			this.sidebar_orientation_breakpoint_sizes(obj_sidebar_outer);
		}

		// Initialize select2
		if(inits.indexOf('select2') != -1) {

			this.sidebar_select2(obj_sidebar_outer);
		}

		// Initialize select2 AJAX
		if(inits.indexOf('select2-ajax') != -1) {

			this.sidebar_select2_ajax(obj_sidebar_outer);
		}

		// Initialize default value range slider
		if((inits.indexOf('range') != -1)) {

			this.sidebar_range_init(obj_sidebar_inner);
		}

		// Initialize default value number
		if((inits.indexOf('number') != -1) && mode_basic) {

			this.sidebar_number_init(obj_sidebar_inner);
		}

		// Initialize sidebar placeholders
		if(inits.indexOf('placeholders') != -1) {

			this.sidebar_placeholders_init(obj_sidebar_outer);
		}
		// Initialize calc
		if(inits.indexOf('calc') != -1) {

			this.sidebar_calc(obj_sidebar_outer);
		}
		// Initialize sidebar select lists
		if(inits.indexOf('select-list') != -1) {

			this.sidebar_select_list(obj_sidebar_outer);
		}

		// Initialize sidebar variable helper
		if(inits.indexOf('variable-helper') != -1) {

			this.sidebar_variable_helper(obj_sidebar_outer);
		}

		// Initialize sidebar auto mapping
		if(inits.indexOf('auto-map') != -1) {

			this.sidebar_auto_map(obj_sidebar_outer);
		}

		// Initialize sidebar knowledgebase
		if(inits.indexOf('knowledgebase') != -1) {

			this.sidebar_knowledgebase(obj_sidebar_outer, obj_sidebar_inner);
		}

		// Initialize sidebar conversational preview
		if(inits.indexOf('conversational-preview') != -1) {

			this.sidebar_conversational_preview(obj_sidebar_outer, obj_sidebar_inner);
		}
		// Initialize required setting event handler
		if(inits.indexOf('required-setting') != -1) {

			this.sidebar_required_setting(object_data, obj_sidebar_outer, obj_sidebar_inner);
		}

		// Initialize image selector
		if(inits.indexOf('image') != -1) {

			this.sidebar_image(obj_sidebar_inner);
		}

		// Initialize media selector
		if(inits.indexOf('media') != -1) {

			this.sidebar_media(obj_sidebar_inner);
		}

		// Column toggle
		if(inits.indexOf('column-toggle') != -1) {

			this.sidebar_column_toggle(obj_sidebar_inner);
		}

		// Initialize options populated by action methods
		if(inits.indexOf('options-action') != -1) {

			// Initialize sidebar conditions
			if(inits.indexOf('conditions') != -1) {

				this.sidebar_conditions_init(obj_sidebar_outer);
			}

			this.sidebar_option_action_init(obj_sidebar_inner, function() {

				// Only run these inits after API requests have all finished

				// Initialize options populated by action methods
				if(inits.indexOf('options-action-reload') != -1) {

					$.WS_Form.this.sidebar_api_reload_init(obj_sidebar_inner);
				}

				// Initialize repeaters
				if(inits.indexOf('repeater') != -1) {

					$.WS_Form.this.sidebar_repeater_init(obj_sidebar_inner);
				}

				// Initial run of conditional logic
				$.WS_Form.this.sidebar_condition_process(obj_sidebar_outer, obj_sidebar_outer, true);
			});

		} else {

			// Initialize sidebar conditions
			if(inits.indexOf('conditions') != -1) {

				$.WS_Form.this.sidebar_conditions_init(obj_sidebar_outer);
			}
		}
	}

	// Sidebar - Title - Init
	$.WS_Form.prototype.sidebar_title_init = function(object, object_id, object_meta, obj_outer) {

		switch(object) {

			case 'form' :

				var sidebar_label = this.language('sidebar_title_' + object);
				var sidebar_icon = this.svg(object);
				var sidebar_compatibility_html = '';

				// Knowledge base URL
				var kb_url = this.get_plugin_website_url('/knowledgebase/form-settings/', 'sidebar');
				var sidebar_kb_html = '<a class="wsf-kb-url" href="' + this.esc_attr(kb_url) + '" target="_blank"' + this.tooltip(this.language('field_kb_url'), 'bottom-center') + ' tabindex="-1">' + this.svg('question-circle') + '</a>';

				// Build ID html
				var sidebar_field_id_html = ($.WS_Form.settings_plugin.helper_field_id) ? '<code data-action="wsf-clipboard"' + this.tooltip(this.language('clipboard'), 'left') + '>[' + ws_form_settings.shortcode + ' id="' + this.form_id + '"]</code>' : '';

				break;

			case 'group' :

				var sidebar_label = this.language('sidebar_title_' + object);
				var sidebar_icon = this.svg(object);
				var sidebar_compatibility_html = '';

				// Knowledge base URL
				var kb_url = this.get_plugin_website_url('/knowledgebase/tabs/', 'sidebar');
				var sidebar_kb_html = '<a class="wsf-kb-url" href="' + this.esc_attr(kb_url) + '" target="_blank"' + this.tooltip(this.language('field_kb_url'), 'bottom-center') + ' tabindex="-1">' + this.svg('question-circle') + '</a>';

				// Build ID html
				var sidebar_field_id_html = ($.WS_Form.settings_plugin.helper_field_id) ? '<code>' + this.language('id') + ': ' + object_id + '</code>' : '';

				break;

			case 'section' :

				var sidebar_label = this.language('sidebar_title_' + object);
				var sidebar_icon = this.svg(object);
				var sidebar_compatibility_html = '';

				// Knowledge base URL
				var kb_url = this.get_plugin_website_url('/knowledgebase/sections/', 'sidebar');
				var sidebar_kb_html = '<a class="wsf-kb-url" href="' + this.esc_attr(kb_url) + '" target="_blank"' + this.tooltip(this.language('field_kb_url'), 'bottom-center') + ' tabindex="-1">' + this.svg('question-circle') + '</a>';

				// Build ID html
				var sidebar_field_id_html = ($.WS_Form.settings_plugin.helper_field_id) ? '<code>' + this.language('id') + ': ' + object_id + '</code>' : '';

				break;

			case 'field' :

				var sidebar_label = object_meta.label;	// Function sidebar_title esc_html label
				var sidebar_icon = (typeof(object_meta.icon) !== 'undefined' ? (object_meta.icon) : '');

				// Build knowledge base HTML
				if((typeof(object_meta['kb_url']) !== 'undefined')) {

					var kb_url = this.get_plugin_website_url(object_meta['kb_url'], 'sidebar');
					var sidebar_kb_html = '<a class="wsf-kb-url" href="' + this.esc_attr(kb_url) + '" target="_blank"' + this.tooltip(this.language('field_kb_url'), 'bottom-center') + ' tabindex="-1">' + this.svg('question-circle') + '</a>';
				}

				// Build compatibility icon HTML
				if((typeof(object_meta['compatibility_url']) !== 'undefined') && $.WS_Form.settings_plugin.helper_compatibility) {

					var sidebar_compatibility_html = '<a class="wsf-compatibility" href="' + this.esc_attr(object_meta['compatibility_url']) + '" target="_blank"' + this.tooltip(this.language('field_compatibility'), 'bottom-center') +  ' tabindex="-1">' + this.svg('markup-circle') + '</a>';
				}

				// Build ID html
				var object_meta_admin_hide_id = (typeof(object_meta.admin_hide_id) !== 'undefined') ? object_meta.admin_hide_id : false;
				var sidebar_field_id_html = object_meta_admin_hide_id ? '' : (($.WS_Form.settings_plugin.helper_field_id) ? '<code data-action="wsf-clipboard"' + this.tooltip(this.language('clipboard'), 'left') + '>#field(' + this.esc_html(object_id) + ')</code>' : '');

				break;

			default :

				var sidebar_label = this.language('sidebar_title_' + object);
				var sidebar_icon = this.svg(object);
				var sidebar_compatibility_html = '';
				var sidebar_field_id_html = '';
		}

		obj_outer.html(this.sidebar_title(sidebar_icon, sidebar_label, sidebar_compatibility_html, sidebar_kb_html, sidebar_field_id_html, true));

		this.sidebar_expand_contract_init();

		this.clipboard(obj_outer);
	}

	// Sidebar - Label - Init
	$.WS_Form.prototype.sidebar_label_init = function(object_meta, obj, object, obj_outer, obj_inner) {

		// Label keyup event
		$('[name="label"]', obj_inner).on('input', function() {

			// Check scratch data exists
			if($.WS_Form.this.object_data_scratch === false) { return false; }

			// Get field_label value
			var object_label = $(this).val();
			object_label = object_label.trim();
			switch(object) {

				case 'field' :

					var label_default = object_meta.label_default;
					break;

				default :

					var label_default = $.WS_Form.this.get_label_default(object);
			}
			$.WS_Form.this.object_data_scratch['label'] = ((object_label == '') ? label_default : object_label);

			switch(object) {

				case 'form' :

					// Change form label
					$('[data-action="wsf-form-label"]').val($.WS_Form.this.object_data_scratch['label']);
					break;

				case 'group' :

					// Change tab label
					var group_id = obj.attr('data-id');
					$('.wsf-group-tab[data-id="' + $.WS_Form.this.esc_selector(group_id) + '"] a input').val($.WS_Form.this.object_data_scratch['label']).trigger('change');

					break;

				case 'section' :

					// Render section (Simulate using new object data)
					var section_id = obj.attr('data-id');
					$('.wsf-section[data-id="' + $.WS_Form.this.esc_selector(section_id) + '"] .wsf-section-label input').val($.WS_Form.this.object_data_scratch['label']);

					// Render placeholders
					$.WS_Form.this.sidebar_placeholders_init(obj_outer);

					break;

				case 'field' :

					// Render field (Simulate using new object data)
					$.WS_Form.this.field_render(obj, $.WS_Form.this.object_data_scratch);

					// Render placeholders
					$.WS_Form.this.sidebar_placeholders_init(obj_outer);

					break;
			}
		});
	}

	// Sidebar - Fields Toggle - Init
	$.WS_Form.prototype.sidebar_fields_toggle_init = function(obj, object, obj_inner) {

		$('[data-fields-toggle]:checked', obj_inner).each(function() {

			// Get meta key
			var meta_key = $(this).attr('data-meta-key');

			// Get meta key config
			if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { return false; }
			var meta_key_config = $.WS_Form.meta_keys[meta_key];			

			// Get fields toggle config
			if(typeof(meta_key_config['fields_toggle']) === 'undefined') { return false; }
			var fields_toggle = meta_key_config['fields_toggle'];

			// Get fields ignore config
			var fields_ignore = (typeof(meta_key_config['fields_ignore']) !== 'undefined') ? meta_key_config['fields_ignore'] : [];

			// Get section ID
			var section_id = $(this).closest('[data-object="section"]').attr('data-id');

			// Check for ignore fields
			for(var fields_ignore_index in fields_ignore) {

				if(!fields_ignore.hasOwnProperty(fields_ignore_index)) { continue; }

				var field_type = fields_ignore[fields_ignore_index];

				if($.WS_Form.this.field_type_in_section(section_id, field_type)) { return false; }
			}

			// Add fields
			for(var fields_toggle_index in fields_toggle) {

				if(!fields_toggle.hasOwnProperty(fields_toggle_index)) { continue; }

				var field_toggle = fields_toggle[fields_toggle_index];
				var field_type = field_toggle.type;
				var field_width_factor = (typeof(field_toggle.width_factor) !== 'undefined') ? field_toggle.width_factor : false;

				// If repeatable checked but field type not in section, then add
				if(!$.WS_Form.this.field_type_in_section(section_id, field_type)) {

					var field_html = '<li class="wsf-field-wrapper" data-type="' + $.WS_Form.this.esc_attr(field_type) + '"' + ((field_width_factor !== false) ? ' data-width-factor="' + $.WS_Form.this.esc_attr(field_width_factor) + '"' : '') + '>' + $(this).html() + '</li>';
					$('#wsf-fields-' + $.WS_Form.this.esc_selector(section_id)).append(field_html);

					var field_obj = $('#wsf-fields-' + $.WS_Form.this.esc_selector(section_id) + ' li').last();

					// Push new field to AJAX
					$.WS_Form.this.field_post(field_obj);

					// Init UI
					$.WS_Form.this.init_ui();
				}
			}
		});
	}

	$.WS_Form.prototype.field_type_in_section = function(section_id, field_type) {

		var group_id = $('#wsf-section-' + $.WS_Form.this.esc_selector(section_id)).closest('.wsf-sections').attr('data-id');
		if(!group_id) { return false; }

		if(typeof(this.form.groups) === 'undefined') { return false; }

		for(var group_index in this.form.groups) {

			if(!this.form.groups.hasOwnProperty(group_index)) { continue; }

			var group = this.form.groups[group_index];

			if(group.id != group_id) { continue; }

			if(typeof(group.sections) === 'undefined') { return false; }

			for(var section_index in group.sections) {

				if(!group.sections.hasOwnProperty(section_index)) { continue; }

				var section = group.sections[section_index];

				if(section.id != section_id) { continue; }

				if(typeof(section.fields) === 'undefined') { return false; }

				for(var field_index in section.fields) {

					if(!section.fields.hasOwnProperty(field_index)) { continue; }

					var field = section.fields[field_index];

					if(field.type == field_type) { return true; }
				}
			}
		}

		return false;
	}

	// Sidebar - Change Event - Init
	$.WS_Form.prototype.sidebar_change_event_init = function(obj, object, obj_inner) {

		$('[data-change-event]', obj_inner).each(function() {

			var change_event = $(this).attr('data-change-event');

			$(this).on(change_event, function() {

				var change_action = $(this).attr('data-change-action');

				// Update field data
				var object_id = obj.attr('data-id');
				var meta_key = $(this).attr('data-meta-key');

				$.WS_Form.this.object_data_update_by_meta_key(object, $.WS_Form.this.object_data_scratch, meta_key);

				// Meta key specific
				switch(meta_key) {

					case 'recaptcha_recaptcha_type' :
					case 'hcaptcha_type' :

						// If reCAPTCHA or hCaptcha is set to invisible, reset breakpoints to full width
						if($(this).val() == 'invisible') {

							// Remove old classes
							$.WS_Form.this.column_classes_render(obj, $.WS_Form.this.object_data_scratch, false);

							// Reset
							$.WS_Form.this.breakpoint_reset_process($.WS_Form.this.object_data_scratch);

							// Add new classes
							$.WS_Form.this.column_classes_render(obj, $.WS_Form.this.object_data_scratch);
						}
						break;
				}

				// Object specific
				switch(object) {

					case 'group' :

						// Render group tab icons
						var object_data_old = $.extend(true, {}, $.WS_Form.this.group_data_cache[object_id]); // Deep clone
						$.WS_Form.this.group_data_cache[object_id] = $.WS_Form.this.object_data_scratch;
						$.WS_Form.this.group_render(obj);
						obj.addClass('wsf-editing');
						$.WS_Form.this.group_data_cache[object_id] = object_data_old;
						break;

					case 'section' :

						// Render section
						var object_data_old = $.extend(true, {}, $.WS_Form.this.section_data_cache[object_id]); // Deep clone
						$.WS_Form.this.section_data_cache[object_id] = $.WS_Form.this.object_data_scratch;
						$.WS_Form.this.section_render(obj);
						obj.addClass('wsf-editing');
						$.WS_Form.this.section_data_cache[object_id] = object_data_old;
						break;

					case 'field' :

						// Render field
						var object_data_old = $.extend(true, {}, $.WS_Form.this.field_data_cache[object_id]); // Deep clone
						$.WS_Form.this.field_data_cache[object_id] = $.WS_Form.this.object_data_scratch;
						$.WS_Form.this.field_render(obj);
						obj.addClass('wsf-editing');
						$.WS_Form.this.field_data_cache[object_id] = object_data_old;
						break;
				}

				// Reload sidebar on change
				if(change_action == 'reload') {

					$.WS_Form.this.object_save(obj, false);
					obj.removeClass('wsf-editing')
					$.WS_Form.this.object_edit(obj, true);
				}
			});
		});
	}

	// Sidebar - Image
	$.WS_Form.prototype.sidebar_image = function(obj_sidebar_inner) {

		var file_frame;
		var sidebar_image_wrapper;

		$('input[data-meta-key-type="image"]', obj_sidebar_inner).each(function() {

			// Get wrapper
			sidebar_image_wrapper = $(this).closest('.wsf-field-inline')

			// Find button
			var sidebar_image_button = $('button', sidebar_image_wrapper);

			sidebar_image_button.on('click', function() {

				// Get wrapper
				sidebar_image_wrapper = $(this).closest('.wsf-field-inline')

				// If the media frame already exists, reopen it.
				if(file_frame) {

					// Open frame
					file_frame.open();
					return;
				}

				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({

					title: 'Select image',
					button: {
						text: 'Use this image',
					},
					multiple: false
				});

				// When an image is selected, run a callback.
				file_frame.on('select', function() {

					// We set multiple to false so only get one image from the uploader
					var attachment = file_frame.state().get('selection').first().toJSON();

					$('input', sidebar_image_wrapper).val(attachment.url);
				});

				// Finally, open the modal
				file_frame.open();
			});
		});
	}

	// Sidebar - Media
	$.WS_Form.prototype.sidebar_media = function(obj_sidebar_inner) {

		var file_frame;
		var sidebar_media_wrapper;

		$('input[data-meta-key-type="media"]', obj_sidebar_inner).each(function() {

			var field_id = $(this).attr('id');

			// Get wrapper
			sidebar_media_wrapper = $(this).closest('.wsf-field-inline')

			// Find button
			var sidebar_media_button = $('button', sidebar_media_wrapper);

			sidebar_media_button.on('click', function() {

				// Get wrapper
				sidebar_media_wrapper = $(this).closest('.wsf-field-inline')

				// If the media frame already exists, reopen it.
				if(file_frame) {

					// Open frame
					file_frame.open();
					return;
				}

				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({

					title: 'Select media',
					button: {
						text: 'Use this media',
					},
					multiple: false,
				});

				// When a media is selected, run a callback.
				file_frame.on('select', function() {

					// We set multiple to false so only get one media from the uploader
					var attachment = file_frame.state().get('selection').first().toJSON();
					var value = JSON.stringify({id: attachment.id, filename: attachment.filename});

					$('input[type="hidden"]', sidebar_media_wrapper).val(value);
					$('input[type="text"]', sidebar_media_wrapper).val(attachment.filename);
				});

				// Finally, open the modal
				file_frame.open();
			});
		});
	}

	// Sidebar - Column toggle
	$.WS_Form.prototype.sidebar_column_toggle = function(obj_sidebar_inner) {

		if(!obj_sidebar_inner.hasClass('wsf-sidebar')) { obj_sidebar_inner = obj_sidebar_inner.closest('.wsf-sidebar'); }

		var ws_this = this;

		$('input[data-column-toggle-meta-key]', obj_sidebar_inner).each(function() {

			ws_this.sidebar_column_toggle_process($(this), obj_sidebar_inner);

			if(!$(this).attr('data-column-toggle-meta-key-init')) {

				$(this).on('change', function() { ws_this.sidebar_column_toggle_process($(this), obj_sidebar_inner); }).attr('data-column-toggle-meta-key-init', '');
			}
		});
	}

	// Sidebar - Column toggle
	$.WS_Form.prototype.sidebar_column_toggle_process = function(obj, obj_sidebar_inner) {

		var meta_key = obj.attr('data-column-toggle-meta-key');
		var meta_key_wrapper = $('[data-meta-key="' + this.esc_selector(meta_key) + '"]', obj_sidebar_inner);
		var column_id = obj.attr('data-column-toggle-column-id');
		var cells = $('[data-id="' + this.esc_selector(column_id) + '"]', meta_key_wrapper);

		if(obj.is(':checked')) {

			cells.css({'display':'table-cell'});

		} else {

			cells.hide();
		}
	}

	// Sidebar - Title - Init
	$.WS_Form.prototype.sidebar_required_setting = function(object_data, obj_sidebar_inner) {

		$('[data-required-setting]', obj_sidebar_inner).on('change keyup input', function() {

			$.WS_Form.this.sidebar_required_setting_process(object_data, obj_sidebar_inner);
		});

		// Initial run
		$.WS_Form.this.sidebar_required_setting_process(object_data, obj_sidebar_inner);
	}

	$.WS_Form.prototype.sidebar_required_setting_process = function(object_data, obj_sidebar_outer, obj_sidebar_inner) {

		$('[data-required-setting]', obj_sidebar_inner).each(function() {

			var require_setting_icon = $('.wsf-required-setting', $(this).closest('.wsf-field-wrapper'));

			if($(this).val() == '') {

				$(this).addClass('wsf-error');
				require_setting_icon.show();

			} else {

				$(this).removeClass('wsf-error');
				require_setting_icon.hide();
			}
		})
	}

	// Sidebar - Knowledge Base
	$.WS_Form.prototype.sidebar_knowledgebase = function(obj_sidebar_outer, obj_sidebar_inner) {

		var knowledgebase_obj = $('#wsf-form-knowledgebase .wsf-fieldset', obj_sidebar_inner);

		// If data is already loaded, no need to make further requests to server
		if(knowledgebase_obj.attr('data-loaded')) { return true; }

		// Query vars
		var query_vars = 'l=' + encodeURIComponent(ws_form_settings.locale_user) + '&e=' + encodeURIComponent(ws_form_settings.edition) + '&v=' + encodeURIComponent(ws_form_settings.version);

		// Search
		var sidebar_knowledgebase_html = '<div class="wsf-field-wrapper">';

		sidebar_knowledgebase_html += '<label class="wsf-label" for="wsf-kb-search-keyword">' + this.language('knowledgebase_search_label') + '</label>';

		sidebar_knowledgebase_html += '<div class="wsf-field-inline">';
		sidebar_knowledgebase_html += '<input type="text" id="wsf-kb-search-keyword" class="wsf-field" value="" placeholder="' + this.language('knowledgebase_search_placeholder') + '" />';
		sidebar_knowledgebase_html += '<button class="wsf-button wsf-button-primary" data-action="wsf-kb-search">' + this.language('knowledgebase_search_button') + '</button>';
		sidebar_knowledgebase_html += '</div>';

		sidebar_knowledgebase_html += '</div>';

		// View all
		sidebar_knowledgebase_html += '<div class="wsf-field-wrapper">';
		sidebar_knowledgebase_html += '<p><a href="https://wsform.com/knowledgebase/" target="_blank">' + this.language('knowledgebase_view_all') + '</a></p>';
		sidebar_knowledgebase_html += '</div>';

		// Content
		sidebar_knowledgebase_html += '<div class="wsf-field-wrapper">';
		sidebar_knowledgebase_html += '<div id="wsf-kb-content"></div>';
		sidebar_knowledgebase_html += '</div>';

		// Icons CSS
		sidebar_knowledgebase_html += '<link rel="stylesheet" href="https://cdn.wsform.com/plugin-support/css/icons.css?' + query_vars + '">';

		knowledgebase_obj.html(sidebar_knowledgebase_html); 

		// Request knowledge base content and pass across WS Form variables only just so we know what knowledge base content to provide
		$.get(

			'https://wsform.com/plugin-support/knowledgebase_content.php?' + query_vars,
			function( data ) {

				$('#wsf-kb-content').html(data);
				knowledgebase_obj.attr('data-loaded', 'true');
			}
		);

		// Search button
		$('[data-action="wsf-kb-search"]').on('click', function() {

			$.WS_Form.this.sidebar_knowledgebase_search();
		});

		// Enter on keyword
		$('#wsf-kb-search-keyword').on('keydown', function(e) {

			var keyCode = e.keyCode || e.which;

			if(keyCode === 13) {

				$.WS_Form.this.sidebar_knowledgebase_search();
			}
		});

		// Wrap contact form in form tag
		var contact_html = $('#wsf-form-contact').html();
		var contact_html = '<form id="wsf-contact-form">' + contact_html + '</form>';
		$('#wsf-form-contact').html(contact_html);

		// Load support search system
		$.getScript('https://wsform.com/plugin-support/js/support_search.js', function() {

			new $.WS_Form_Support_Search({

				obj_input: $('#wsf_contact_inquiry'),
				obj_output: $('#wsf_contact_support_search_results'),
				results_class: 'notice notice-info',
				results_url_suffix: '?utm_source=ws_form_pro&utm_medium=sidebar&utm_campaign=support_search'
			});
		});

		$('#wsf-contact-form').on('submit', function(e) {

			$('#wsf_contact_submit').attr('disabled', 'disabled');

			// Check GDPR checkbox
			var contact_gdpr = $('#wsf_contact_gdpr').is(':checked');
			if(!contact_gdpr) { return false; }

			// Build form data
			var params = {

				contact_first_name: 	$('#wsf_contact_first_name').val(),
				contact_last_name: 		$('#wsf_contact_last_name').val(),
				contact_email: 			$('#wsf_contact_email').val(),
				contact_inquiry: 		$('#wsf_contact_inquiry').val(),
				contact_push_form: 		$('#wsf_contact_push_form').is(':checked'),
				contact_push_system: 	$('#wsf_contact_push_system').is(':checked')
			};

			// Fallback to variables
			$.WS_Form.this.api_call('helper/support-contact-submit/', 'POST', params, function(data) {

				if(data.error) {

					$.WS_Form.this.error(support_contact_error, data.error_message);

				} else {

					$('#wsf-form-contact form fieldset').html('<div class="wsf-field-wrapper">' + data.response + '</div>');
				}

				$.WS_Form.this.loader_off();
			});
			return false;
		});
	}

	// Sidebar - Knowledge Base - Search
	$.WS_Form.prototype.sidebar_knowledgebase_search = function() {

		var keyword = $('#wsf-kb-search-keyword').val();
		if(keyword == '') { return; }

		// Tidy up keyword
		keyword = keyword.trim();

		$.get(

			'https://wsform.com/plugin-support/knowledgebase_search.php?l=' + encodeURIComponent(ws_form_settings.locale_user) + '&e=' + encodeURIComponent(ws_form_settings.edition) + '&v=' + encodeURIComponent(ws_form_settings.version) + '&k=' + encodeURIComponent(keyword),
			function( data ) {

				$('#wsf-kb-content').html(data);
			}
		);
	}

	// Sidebar - Conversational - Preview
	$.WS_Form.prototype.sidebar_conversational_preview = function(obj_sidebar_outer, obj_sidebar_inner) {

		// Conversational
		if($.WS_Form.settings_plugin.helper_live_preview) {

			$('[data-action="wsf-conversational-preview"]', obj_sidebar_inner).on('click', function(e) { $.WS_Form.this.form_preview(e, $(this)); });
		}
	}

	// Sidebar - Auto Map Fields
	$.WS_Form.prototype.sidebar_auto_map = function(obj) {

		$('[data-action="wsf-auto-map"]', obj).on('click', function() {

			// Start Auto Map annimation
			var api_auto_map_obj = $(this);
			api_auto_map_obj.addClass('wsf-api-method-calling');

			// Get meta key
			var option_meta_key = $(this).attr('data-option-meta-key');
			var object = $(this).attr('data-object');
			var object_id = $(this).attr('data-object-id');

			// Get meta key config
			if(typeof($.WS_Form.meta_keys[option_meta_key]) === 'undefined') { return false; }
			var meta_key_config = $.WS_Form.meta_keys[option_meta_key];

			// Check type
			if(typeof(meta_key_config.type) === 'undefined') { return false; }
			if(meta_key_config.type !== 'repeater') { return false; }

			// Get meta keys to map
			if(typeof(meta_key_config.meta_keys) === 'undefined') { return false; }
			var meta_keys = meta_key_config.meta_keys;
			if(meta_keys.length !== 2) { return false; }

			var index_fields = false;
			var key_field = false;
			var key_api_field = false;
			var index_api_fields = false;
			var options_action_id = false;
			var options_list_id_meta_key = false;
			var options_list_sub_id_meta_key = false;
			var options_action_api_populate = 'list_fields_fetch';

			// Get lists of data
			for(var meta_key_index in meta_keys) {

				if(!meta_keys.hasOwnProperty(meta_key_index)) { continue; }

				var meta_key = meta_keys[meta_key_index];

				// Get meta key config
				if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { return false; }
				var meta_key_config = $.WS_Form.meta_keys[meta_key];

				// Check type
				if(typeof(meta_key_config.type) === 'undefined') { return false; }				
				var meta_key_type = meta_key_config.type;
				if(meta_key_type != 'select') { return false; }

				// Get options
				if(typeof(meta_key_config.options) === 'undefined') { return false; }
				var meta_key_options = meta_key_config.options;

				switch(meta_key_options) {

					case 'fields' :

						key_field = (typeof(meta_key_config.key) !== 'undefined') ? meta_key_config.key : meta_key;
						index_fields = meta_key_index;
						break;

					case 'action_api_populate' :

						index_api_fields = meta_key_index;
						key_api_field = meta_key;
						options_list_id_meta_key = meta_key_config.options_list_id_meta_key;
						options_list_sub_id_meta_key = meta_key_config.options_list_sub_id_meta_key;

						if(typeof(meta_key_config.options_action_id_meta_key) !== 'undefined') {

							options_action_id = $('[data-meta-key="' + $.WS_Form.this.esc_selector(meta_key_config.options_action_id_meta_key) + '"]').val();

						} else {

							options_action_id = meta_key_config.options_action_id;
						}

						options_action_api_populate = (typeof(meta_key_config.options_action_api_populate) !== 'undefined') ? meta_key_config.options_action_api_populate : 'list_fields_fetch';

						break;
				}
			}

			if(index_fields === false) { return false; }
			if(index_api_fields === false) { return false; }

			// Get list ID
			var options_action_list_id = $('[data-meta-key="' + $.WS_Form.this.esc_selector(options_list_id_meta_key) + '"]').val();

			// Get list sub ID
			var options_action_list_sub_id = $('[data-meta-key="' + $.WS_Form.this.esc_selector(options_list_sub_id_meta_key) + '"]').val();

			// Get API call path
			var api_call_path = $.WS_Form.this.action_api_method_path(options_action_id, options_action_api_populate, options_action_list_id, options_action_list_sub_id);

			// Clear options action cache
			$.WS_Form.this.options_action_cache_clear(api_call_path);

			// Lock sidebar
			$.WS_Form.this.sidebar_lock();

			// Make API call
			var fields_api = [];
			$.WS_Form.this.api_call(api_call_path, 'GET', false, function(response) {

				if(typeof(response.data) === 'undefined') { return false; }

				for(var api_field_index in response.data) {

					if(!response.data.hasOwnProperty(api_field_index)) { continue; }

					var api_field = response.data[api_field_index];

					fields_api.push({'id': api_field.id, 'label': api_field.label});
				}

				if(fields_api.length == 0) { $.WS_Form.error('error_auto_map_api_fields'); return false; }

				// Mapping
				var action_value = [];
				for(var fields_api_index in fields_api) {

					if(!fields_api.hasOwnProperty(fields_api_index)) { continue; }

					var field_api = fields_api[fields_api_index];

					var field_api_label = field_api.label;

					var field_id_best = false;
					var field_score_best = 0;
					for(var fields_index in $.WS_Form.this.field_data_cache) {

						if(!$.WS_Form.this.field_data_cache.hasOwnProperty(fields_index)) { continue; }

						var field = $.WS_Form.this.field_data_cache[fields_index];

						var field_label = field.label;

						var score = $.WS_Form.this.score(String(field_label), String(field_api_label));
						score += $.WS_Form.this.score(String(field_api_label), String(field_label));

						if(score > field_score_best) {

							field_score_best = score;
							field_id_best = fields_index;
						}
					}

					if(field_score_best > 0) {

						var action_value_single = {};
						action_value_single[key_field] = field_id_best;
						action_value_single[key_api_field] = field_api.id;
						action_value.push(action_value_single);
					}
				}

				// Set meta_key value
				if(action_value.length > 0) {

					// Get object and object ID
					var object_data = $.WS_Form.this.get_object_data(object, object_id, true);

					object_data.meta[option_meta_key] = action_value;

					// Initialize sidebar
					var inits = ['options-action', 'repeater'];
					var obj_sidebar_inner = obj.closest('.wsf-sidebar-inner');
					if(!obj_sidebar_inner.length) { obj_sidebar_inner = $('.wsf-sidebar-inner', obj); }
					$.WS_Form.this.sidebar_inits(inits, obj_sidebar_inner);
				}

				// Remove class
				api_auto_map_obj.removeClass('wsf-api-method-calling');

				// Unlock sidebar
				$.WS_Form.this.sidebar_unlock();

			}, function() {

				// Unlock sidebar
				$.WS_Form.this.sidebar_unlock();

			}, true);	// Bypass loader
		});
	}
	// Sidebar - Calc - Init
	$.WS_Form.prototype.sidebar_calc = function(obj) {

		var ws_this = this;

		$('[data-action="wsf-calc"]').on('click', function() {

			var field_wrapper = $(this).closest('.wsf-field-wrapper');
			var meta_key = $(this).attr('data-option-meta-key');
			var already_open = $(this).hasClass('wsf-calc-open');

			$('.wsf-calc', $('#wsf-sidebars')).remove();
			$('.wsf-calc-open').removeClass('wsf-calc-open').attr('autocomplete', false);

			if(!already_open) {

				$(this).addClass('wsf-calc-open').attr('autocomplete', 'off');

				// Get calculator HTML
				var calc_html = ws_this.sidebar_calc_html();

				$.WS_Form.this.sidebar_calc_process(field_wrapper, calc_html, meta_key);
			}
		});
	}

	// Sidebar - Calc - Init
	$.WS_Form.prototype.sidebar_calc_process = function(obj, calc_html, meta_key) {

		// Append calculator
		var wsf_field_wrapper = $('[data-meta-key="' + this.esc_selector(meta_key) + '"]').closest('.wsf-field-wrapper');
		var wsf_helper = $('.wsf-helper', wsf_field_wrapper);

		if(wsf_helper.length) {

			$(calc_html).insertBefore(wsf_helper);

		} else {
	
			$(calc_html).insertAfter($('[data-meta-key="' + this.esc_selector(meta_key) + '"]'));
		}

		// Add calc function
		$.WS_Form.this.input_insert_function($('[data-meta-key="' + this.esc_selector(meta_key) + '"]'), '#calc()', true, true);

		// Insert
		$('[data-action="wsf-calc-insert"]').on('click', function(e) {

			var insert_text = (typeof($(this).attr('data-insert')) !== 'undefined') ? $(this).attr('data-insert') : false;
			
			if(insert_text !== false) {

				$.WS_Form.this.input_insert_text($('[data-meta-key="' + $.WS_Form.this.esc_selector(meta_key) + '"]'), insert_text);
			}
		})

		// Insert - Select
		$('[data-action="wsf-calc-insert-select"]').on('change', function(e) {

			var insert_text = $(this).val();
			
			if(insert_text !== '') {

				$.WS_Form.this.input_insert_function($('[data-meta-key="' + $.WS_Form.this.esc_selector(meta_key) + '"]'), insert_text, false, false);
			}

			$(this).find('option:eq(0)').prop('selected', true);
		});

		// Insert - Select and Highlight Parameters
		$('[data-action="wsf-calc-insert-select-highlight-parameters"]').on('change', function(e) {

			var insert_text = $(this).val();
			
			if(insert_text !== '') {

				$.WS_Form.this.input_insert_function($('[data-meta-key="' + $.WS_Form.this.esc_selector(meta_key) + '"]'), insert_text, false, true);
			}

			$(this).find('option:eq(0)').prop('selected', true);
		});

		// Delete
		$('[data-action="wsf-calc-delete"]').on('click', function(e) {

			$.WS_Form.this.input_delete($('[data-meta-key="' + $.WS_Form.this.esc_selector(meta_key) + '"]'));
		})

		// Clear
		$('[data-action="wsf-calc-clear"]').on('click', function(e) {

			$.WS_Form.this.input_clear($('[data-meta-key="' + $.WS_Form.this.esc_selector(meta_key) + '"]'));

			// Add calc function
			$.WS_Form.this.input_insert_function($('[data-meta-key="' + $.WS_Form.this.esc_selector(meta_key) + '"]'), '#calc()', true, true);
		})
	}

	// Sidebar - Calc - HTML
	$.WS_Form.prototype.sidebar_calc_html = function(obj) {

		var calc_html = '<div class="wsf-calc"><table><tbody>';

		// Run through each row
		for(var calc_row_index in $.WS_Form.calc) {

			if(!$.WS_Form.calc.hasOwnProperty(calc_row_index)) { continue; }

			var calc_row = $.WS_Form.calc[calc_row_index];

			calc_html += '<tr>';

			// Run through each column
			for(var calc_column_index in calc_row) {

				if(!calc_row.hasOwnProperty(calc_column_index)) { continue; }

				var calc_column = calc_row[calc_column_index];

				// Title
				var title = (typeof(calc_column.title) !== 'undefined') ? calc_column.title : ((typeof(calc_column.label) !== 'undefined') ? calc_column.label : false);

				// Colspan
				var colspan = (typeof(calc_column.colspan) !== 'undefined') ? ' colspan="' + this.esc_attr(calc_column.colspan) + '"' : '';

				// Action
				var data_action = (typeof(calc_column.action) !== 'undefined') ? ' data-action="wsf-calc-' + this.esc_attr(calc_column.action) + '"' : '';
				var data_insert = (typeof(calc_column.action) !== 'undefined') ? ' data-insert="' + this.esc_attr(calc_column.insert) + '"' : '';

				// Class
				var class_array = ['wsf-button', 'wsf-button-large', 'wsf-button-full'];

				if(typeof(calc_column.class) !== 'undefined') {

					class_array.push(calc_column.class);
				}

				calc_html += '<td' + colspan + '>';

				switch(calc_column.type) {

					case 'button' :

						calc_html += '<button class="' + this.esc_attr(class_array.join(' ')) + '"' + (title ? ' title="' + this.esc_attr(title) + '"' : '') + data_action + data_insert + '>' + this.esc_html(calc_column.label) + '</button>';

						break;

					case 'select' :

						calc_html += '<select class="' + this.esc_attr(class_array.join(' ')) + '"' + (title ? ' title="' + this.esc_attr(title) + '"' : '') + data_action + '>';
						calc_html += '<option selected>' + calc_column.label + '</option>';

						var source = calc_column.source;

						switch(source) {

							case 'field' :

								var options = this.get_select_options_field(true);

								for(var option_index in options) {

									if(!options.hasOwnProperty(option_index)) { continue; }
									if(typeof(options[option_index]) === 'function') { continue; }

									var option = options[option_index];

									calc_html += '<option value="' + this.esc_attr(option.value) + '">' + this.esc_html(option.text) + '</option>';
								}

								break;

							case 'variables' :

								var variables_group_id = calc_column.variables_group_id;

								for(var parse_variable_help_index in $.WS_Form.parse_variable_help) {

									if(!$.WS_Form.parse_variable_help.hasOwnProperty(parse_variable_help_index)) { continue; }
									if(typeof($.WS_Form.parse_variable_help[parse_variable_help_index]) === 'function') { continue; }

									var parse_variable_help = $.WS_Form.parse_variable_help[parse_variable_help_index];

									if(parse_variable_help.group_id != variables_group_id) { continue; }

									calc_html += '<option value="' + this.esc_attr(parse_variable_help.value) + '">' + this.esc_html(parse_variable_help.text) + '</option>';
								}

								break;
						}

						calc_html += '</select>';

						break;

				}

				calc_html += '</td>';
			}

			calc_html += '</tr>';
		}

		calc_html += '</tbody></table></div>';

		return calc_html;
	}
	// Sidebar - Variable Helper - Init
	$.WS_Form.prototype.sidebar_variable_helper = function(obj) {

		$('#wsf-sidebars').on('click', '[data-action="wsf-variable-helper"]', function() {

			// Variable helper
			$.WS_Form.this.variable_helper_modal_open($(this).attr('data-option-meta-key') ? $('[data-meta-key="' + $(this).attr('data-option-meta-key') + '"]', $(this).closest('.wsf-field-wrapper')) : false);
		});
	}

	// Sidebar - Select list - Init
	$.WS_Form.prototype.sidebar_select_list = function(obj) {

		$('[data-action="wsf-select-list"]').on('click', function() {

			var meta_key = $(this).attr('data-option-meta-key');
			var field_wrapper = $(this).closest('.wsf-field-wrapper');
			var already_open = $(this).hasClass('wsf-select-list-open');

			$('.wsf-select-list', $('#wsf-sidebars')).remove();
			$('.wsf-select-list-open').removeClass('wsf-select-list-open').attr('autocomplete', false);

			if(!already_open) {

				$(this).addClass('wsf-select-list-open').attr('autocomplete', 'off');

				// Get heading
				var select_list_heading = (typeof($.WS_Form.meta_keys[meta_key]['select_list_heading']) !== 'undefined') ? $.WS_Form.meta_keys[meta_key]['select_list_heading'] : false;

				// List specified at meta key level
				$.WS_Form.this.sidebar_select_list_process(field_wrapper, $.WS_Form.meta_keys[meta_key]['select_list'], meta_key, select_list_heading);
			}
		});
	}

	// Get list of options for a select containing the form fields
	$.WS_Form.prototype.get_select_options_field = function(calc_out_filter) {

		if(typeof(calc_out_filter) !== 'undefined') { calc_out_filter = false; }

		var list_fields = [];

		for(var group_index in $.WS_Form.this.form.groups) {

			if(!$.WS_Form.this.form.groups.hasOwnProperty(group_index)) { continue; }

			var group = $.WS_Form.this.form.groups[group_index];

			for(var section_index in group.sections) {

				if(!group.sections.hasOwnProperty(section_index)) { continue; }

				var section = group.sections[section_index];

				for(var field_index in section.fields) {

					if(!section.fields.hasOwnProperty(field_index)) { continue; }

					var field = section.fields[field_index];

					var field_type_config = $.WS_Form.field_type_cache[field.type];

					var value_out = (typeof(field_type_config['value_out'])) ? field_type_config['value_out'] : false;

					var calc_out = !calc_out_filter || ((typeof(field_type_config['calc_out'])) ? field_type_config['calc_out'] : false);

					if(value_out && calc_out) {

						list_fields.push({

							'text': field.label + ' (' + this.language('id') + ': ' + field.id + ')',
							'value': '#field(' + field.id + ')',
							'group': $.WS_Form.this.language('field')
						});
					}
				}
			}
		}

		return list_fields;
	}

	$.WS_Form.prototype.sidebar_select_list_process = function(obj, list, meta_key, heading) {

		var ws_this = this;

		if(typeof(heading) === 'undefined') { heading = false; }

		// Build list
		var list_html = '<div class="wsf-select-list"><table>';

		if(heading !== false) { list_html += '<thead><tr><th>' + heading + '</th></tr></thead>'; }

		list_html += '<tbody>';

		var group_id_last = false;

		for(var list_index in list) {

			if(!list.hasOwnProperty(list_index)) { continue; }

			var list_item = list[list_index];

			if(typeof(list_item.group_id) !== 'undefined') {

				var group_id = list_item.group_id;
				if(group_id !== group_id_last) {

					list_html += '<tr><th>' + list_item.group_label + '</th></tr>';

					group_id_last = group_id;
				}

			}

			list_html += '<tr data-action="wsf-list-item-insert" data-action-html="' + $.WS_Form.this.esc_attr(list_item.value) + '"><td>' + $.WS_Form.this.esc_html(list_item.text) + '</td></tr>';
		}

		list_html += '</tbody></table></div>';

		// Append list
		var wsf_field_wrapper = $('[data-meta-key="' + this.esc_selector(meta_key) + '"]').closest('.wsf-field-wrapper');
		var wsf_helper = $('.wsf-helper', wsf_field_wrapper);

		if(wsf_helper.length) {

			$(list_html).insertBefore(wsf_helper);

		} else {
	
			$(list_html).insertAfter($('[data-meta-key="' + this.esc_selector(meta_key) + '"]'));
		}

		// Turn off loader
		$.WS_Form.this.loader_off();

		// Set up events
		$('[data-action="wsf-list-item-insert"]').on('click', function(e) {

			var insert_text = $(this).attr('data-action-html');

			$.WS_Form.this.input_insert_text($('[data-meta-key="' + ws_this.esc_selector(meta_key) + '"]'), insert_text);
		})
	}

	// Sidebar - Option - Action - Reload - Init
	$.WS_Form.prototype.sidebar_api_reload_init = function(obj) {

		// Action API method events
		this.api_reload_init(obj, function(obj, action_id, action_api_method) {

			// Initialize sidebar
			var inits = ['options-action', 'repeater'];
			var obj_sidebar_inner = obj.closest('.wsf-sidebar-inner');
			$.WS_Form.this.sidebar_inits(inits, obj_sidebar_inner);

			// Unlock sidebar
			$.WS_Form.this.sidebar_unlock();

		}, null, true);
	}

	// Sidebar - Option - Action - Init
	$.WS_Form.prototype.sidebar_option_action_init = function(obj, complete) {

		// Get select objects
		this.options_action_objects = $('[data-options-action-api-populate]', obj).toArray();

		// Start processing them
		this.sidebar_option_action_process(complete);
	}

	// Sidebar - Option - Action - Init
	$.WS_Form.prototype.sidebar_option_action_process = function(complete) {

		// If there are no more actions to process, then return true
		if(this.options_action_objects.length == 0) { 

			// Run complete function
			complete();

			return true;
		}

		// Get next select to populate
		var options_action_obj = $(this.options_action_objects.shift());

		// Get action ID and API method
		var action_id = options_action_obj.attr('data-options-action-id');
		if(action_id == undefined) {

			var action_id_meta_key = options_action_obj.attr('data-options-action-id-meta-key');
			if(action_id_meta_key != undefined) {

				action_id = $('[data-meta-key="' + this.esc_selector(action_id_meta_key) + '"]', $('#wsf-sidebars')).val();
			}
		}
		var action_api_method = options_action_obj.attr('data-options-action-api-populate');

		// Get list ID
		var list_id_meta_key = options_action_obj.attr('data-options-list-id-meta-key');
		var list_id = (list_id_meta_key != undefined) ? $('[data-meta-key="' + this.esc_selector(list_id_meta_key) + '"]', $('#wsf-sidebars')).val() : false;

		// Get list sub ID
		var list_sub_id_meta_key = options_action_obj.attr('data-options-list-sub-id-meta-key');
		var list_sub_id = (list_sub_id_meta_key != undefined) ? $('[data-meta-key="' + this.esc_selector(list_sub_id_meta_key) + '"]', $('#wsf-sidebars')).val() : false;

		// Check if list sub is required
		if(
			(list_sub_id_meta_key != undefined) &&
			!list_sub_id
		) {

			// Process next
			return this.sidebar_option_action_process(complete);
		}

		// Get API call path
		var api_call_path = $.WS_Form.this.action_api_method_path(action_id, action_api_method, list_id, list_sub_id);

		// Check action_id
		if(api_call_path === false) {

			// Process next
			return this.sidebar_option_action_process(complete);
		}

		// Get object and object ID
		var object = options_action_obj.attr('data-object');
		var object_id = options_action_obj.attr('data-object-id');
		var object_data = $.WS_Form.this.get_object_data(object, object_id, true);

		// Get meta key and meta value
		var meta_key = options_action_obj.attr('data-meta-key');

		// Get meta key config
		var meta_key_config = (typeof($.WS_Form.meta_keys[meta_key]) !== 'undefined') ? $.WS_Form.meta_keys[meta_key] : false;
		if(meta_key_config === false) {

			// Process next
			return this.sidebar_option_action_process(complete);
		}

		// Check for repeater
		var repeater_meta_key = options_action_obj.attr('data-repeater-meta-key');

		// Get meta_value
		if(repeater_meta_key === undefined) {

			var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, [], true);

		} else {

			// Get row index
			var row = options_action_obj.closest('tr');
			var row_index = row.index();

			// Get repeater data
			var repeater_meta_value_array = $.WS_Form.this.get_object_meta_value(object_data, repeater_meta_key, [], true);

			// Get row data
			var repeater_row_array = (typeof(repeater_meta_value_array[row_index]) !== 'undefined') ? repeater_meta_value_array[row_index] : [];

			// Get column (meta_value)
			var meta_value = (typeof(repeater_row_array[meta_key]) !== 'undefined') ? repeater_row_array[meta_key] : '';
		}

		// Check if options exist in cache
		if(typeof(this.options_action_cache[api_call_path]) === 'undefined') {

			// Lock sidebar
			$.WS_Form.this.sidebar_lock();

			// Make API call
			$.WS_Form.this.api_call(api_call_path, 'GET', false, function(response) {

				// Build options for action
				var options_action = [];

				if(typeof(response.data) !== 'undefined') {

					var rows = response.data;
					for(var row_index in rows) {

						if(!rows.hasOwnProperty(row_index)) { continue; }

						var row = rows[row_index];

						if(row === null) { continue; }

						var text = row.label;
						if((typeof(row.required) !== 'undefined') && row.required) { text += ' *'; }
						if((typeof(row.no_map) !== 'undefined') && row.no_map) { continue; }

						options_action.push({'value': row.id, 'text': text});
					}
				}

				// Add to cache
				$.WS_Form.this.options_action_cache[api_call_path] = options_action;

				// Render
				var sidebar_option_action_process_render_return = $.WS_Form.this.sidebar_option_action_process_render(options_action_obj, options_action, meta_value, complete);

				// Unlock sidebar
				$.WS_Form.this.sidebar_unlock();

				return sidebar_option_action_process_render_return;

			}, function() {

				// Failed, so attempt to process next
				var sidebar_option_action_process_return = $.WS_Form.this.sidebar_option_action_process(complete);

				// Unlock sidebar
				$.WS_Form.this.sidebar_unlock();

				return sidebar_option_action_process_return;

			}, true);	// Bypass loader

		} else {

			// Pull from cache
			var options_action = this.options_action_cache[api_call_path];

			// Render
			return this.sidebar_option_action_process_render(options_action_obj, options_action, meta_value, complete);
		}
	}

	// Sidebar - Lock
	$.WS_Form.prototype.sidebar_lock = function() {

		if(this.sidebar_lock_count === 0) {

			// Loader on
			this.loader_on();

			// Get sidebar object
			var sidebar_obj = $('#wsf-sidebars .wsf-sidebar-open');

			// Add class
			sidebar_obj.addClass('wsf-sidebar-locked');

			// Disable elements
			$('select[data-options-action-id-meta-key], select[data-options-action-id], .wsf-sidebar-footer button', sidebar_obj).prop('disabled', true).attr('data-lock-disabled', '');
		}

		this.sidebar_lock_count++;
	}

	// Sidebar - Unlock
	$.WS_Form.prototype.sidebar_unlock = function() {

		this.sidebar_lock_count--;

		if(this.sidebar_lock_count < 0) {

			this.sidebar_lock_count = 0;
		}

		if(this.sidebar_lock_count === 0) {

			// Loader off
			this.loader_off();

			// Get sidebar object
			var sidebar_obj = $('#wsf-sidebars .wsf-sidebar-open');

			// Remove class
			sidebar_obj.removeClass('wsf-sidebar-locked');

			// Enable elements
			$('[data-lock-disabled]', sidebar_obj).prop('disabled', false).removeAttr('data-lock-disabled');
		}
	}

	// Sidebar - Option - Action - Init
	$.WS_Form.prototype.sidebar_option_action_process_render = function(options_action_obj, options_action, meta_value, complete) {

		if(meta_value === false) { meta_value = ''; }

		var options_html = '';

		// Read meta key config
		var meta_key = options_action_obj.attr('data-meta-key');
		if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { return this.sidebar_option_action_process(complete); }
		var meta_key_config = $.WS_Form.meta_keys[meta_key];

		// Insert blank option
		if(typeof(meta_key_config['options_blank']) !== 'undefined') {

			options_html += '<option value="">' + this.esc_html(meta_key_config['options_blank']) + '</option>';
		}

		// Render options
		for(var options_action_index in options_action) {

			if(!options_action.hasOwnProperty(options_action_index)) { continue; }
			if(typeof(options_action[options_action_index]) === 'function') { continue; }

			var option_action_single = options_action[options_action_index];

			options_html += '<option value="' + this.esc_attr(option_action_single.value) + '"' + ((meta_value.toString() === option_action_single.value.toString()) ? ' selected' : '') + '>' + this.esc_html(option_action_single.text) + '</option>';
		}

		// Populate
		options_action_obj.html(options_html);

		// Process next
		return this.sidebar_option_action_process(complete);
	}

	// Sidebar - Repeater - Init
	$.WS_Form.prototype.sidebar_repeater_init = function(obj_inner) {

		$('.wsf-repeater', obj_inner).each(function() {

			// Render
			$.WS_Form.this.sidebar_repeater_render($(this));
		});
	}

	// Sidebar - Repeater - Row - New
	$.WS_Form.prototype.sidebar_repeater_render = function(obj) {

		// Get data
		var repeater = this.sidebar_repeater_get(obj);

		// Get repeater HTML
		var sidebar_repeater_html_return = this.sidebar_repeater_html(repeater);

		// Render
		obj.html(sidebar_repeater_html_return.html);

		// Init
		this.sidebar_inits(sidebar_repeater_html_return.inits, obj);

		// Check for uniques
		if(repeater.meta_keys_unique !== false) {

			for(var meta_keys_unique_index in repeater.meta_keys_unique) {

				if(!repeater.meta_keys_unique.hasOwnProperty(meta_keys_unique_index)) { continue; }

				var meta_key_unique = repeater.meta_keys_unique[meta_keys_unique_index];

				// Read meta key config
				var meta_key_config = $.WS_Form.meta_keys[meta_key_unique];

				// meta_key_unique override
				if(typeof(meta_key_config['key']) !== 'undefined') { meta_key_unique = meta_key_config['key']; }

				this.sidebar_repeater_options_unique(meta_key_unique, obj);

				$('select[data-meta-key="' + this.esc_selector(meta_key_unique) + '"]', obj).on('change', function() {

					$.WS_Form.this.sidebar_repeater_options_unique(meta_key_unique, obj);
				});
			}
		}

		// Event - Add Row
		$('[data-action="wsf-repeater-row-add"] div', obj).on('click', function() {

			// Get data
			var repeater_obj = $(this).closest('.wsf-repeater');

			var object = repeater_obj.attr('data-object');
			var object_id = repeater_obj.attr('data-id');
			var object_data = $.WS_Form.this.get_object_data(object, object_id, true);

			var meta_key = repeater_obj.attr('data-meta-key');

			// Save repeater data
			$.WS_Form.this.object_data_update_by_meta_key(object, object_data, meta_key);

			// Get repeater data
			var repeater = $.WS_Form.this.sidebar_repeater_get(repeater_obj);

			// Get empty row
			var repeater_data_row = $.WS_Form.this.sidebar_repeater_row_new(repeater.meta_keys);

			// Add to data
			repeater.data.push(repeater_data_row);
			$.WS_Form.this.set_object_meta_value(repeater.object_data, repeater.meta_key, repeater.data);

			// Re-render
			$.WS_Form.this.sidebar_repeater_render(repeater_obj);
		});

		// Event - Delete Row
		$('[data-action="wsf-repeater-row-delete"]', obj).on('click', function() {

			// Get data
			var repeater_obj = $(this).closest('.wsf-repeater');

			var object = repeater_obj.attr('data-object');
			var object_id = repeater_obj.attr('data-id');
			var object_data = $.WS_Form.this.get_object_data(object, object_id, true);

			var meta_key = repeater_obj.attr('data-meta-key');

			// Get repeater data
			var repeater = $.WS_Form.this.sidebar_repeater_get(repeater_obj);

			// Get row data
			var row = $(this).closest('tr');
			var row_index = row.index();

			// Delete row from DOM
			row.remove();

			// Save repeater data
			$.WS_Form.this.object_data_update_by_meta_key(object, object_data, meta_key);

			// Re-render
			$.WS_Form.this.sidebar_repeater_render(repeater_obj);
		});

		// Rows - Sortable
		$('table.wsf-repeater-table', obj).sortable({

			items: 'tbody tr',
			containment: 'parent',
			cursor: 'move',
			tolerance: 'pointer',
			handle: '[data-action="wsf-repeater-row-sort"]',
			axis: 'y',
			cancel: '.wsf-ui-cancel, input[type=text]:not([readonly])',

			start: function(e, ui) {

				// Refresh sortable positions (to ensure helpers vertical positioning is correct)
				$('table.wsf-repeater-table', obj).sortable('refreshPositions');

				// Get index being dragged
				$.WS_Form.repeater_index_dragged_from = (ui.helper.index());
			},

			stop: function(e, ui) {

				// Get index dragged to
				var row_index_old = $.WS_Form.repeater_index_dragged_from;
				var row_index_new = ui.item.index();

				// Get meta key
				var meta_key = ui.item.closest('[data-meta-key]').attr('data-meta-key');
				if(!meta_key) { return; }
				if(typeof($.WS_Form.this.object_data_scratch.meta[meta_key]) === 'undefined') { return; }

				// Get repeater rows
				var rows = $.WS_Form.this.object_data_scratch.meta[meta_key];
				if(typeof(rows) !== 'object') { return; }

				// Re-order
				rows.splice(row_index_new, 0, rows.splice(row_index_old, 1)[0]);
			}
		});	
	}

	// Sidebar - Repeater - Options Fields - Unique (Disable options that are already selected)
	$.WS_Form.prototype.sidebar_repeater_options_unique = function(meta_key_unique, obj) {

		var ws_this = this;

		var selected_values_all = [];

		$('select[data-meta-key="' + this.esc_selector(meta_key_unique) + '"]', obj).each(function() {

			// Add selected value
			var select_val = $(this).val();
			if(select_val != '') {

				selected_values_all.push(select_val);
			}

			// Reset options
			$('option:not([data-disabled-always])', $(this)).prop('disabled', false);
		});

		// Remove encoding
		for(var selected_values_index in selected_values_all) {

			if(!selected_values_all.hasOwnProperty(selected_values_index)) { continue; }

			if(typeof(selected_values_all[selected_values_index]) === 'string') {

				// Handle forward slashes
				selected_values_all[selected_values_index] = this.replace_all(selected_values_all[selected_values_index], '\\', '\\\\');
			}
		}

		$('select[data-meta-key="' + this.esc_selector(meta_key_unique) + '"]', obj).each(function() {

			// Get selected values
			var selected_values = $.extend(true, [], selected_values_all);

			// Remove the currently selected value
			var val_current = $(this).val();
			if(typeof(val_current) === 'string') {

				// Handle forward slashes
				val_current = $.WS_Form.this.replace_all(val_current, '\\', '\\\\');
			}
			var selected_value_index = selected_values.indexOf(val_current);
			if(selected_value_index > -1) { selected_values.splice(selected_value_index, 1); }

			// Disable the selected values
			for(var selected_values_index in selected_values) {

				if(!selected_values.hasOwnProperty(selected_values_index)) { continue; }

				$('option[value="' + ws_this.esc_selector(selected_values[selected_values_index]) + '"]:not([data-disabled-never])', $(this)).prop('disabled', true);
			}
		});
	}

	// Sidebar - Repeater - Row - New
	$.WS_Form.prototype.sidebar_repeater_row_new = function(meta_keys) {

		var repeater_row = {};

		for(var meta_keys_index in meta_keys) {

			if(!meta_keys.hasOwnProperty(meta_keys_index)) { continue; }

			var meta_key = meta_keys[meta_keys_index];

			// Ensure meta key is configured
			if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { return false; }

			var meta_value = (typeof($.WS_Form.meta_keys[meta_key].default) !== 'undefined') ? $.WS_Form.meta_keys[meta_key].default : '';

			if(typeof($.WS_Form.meta_keys[meta_key]['key']) !== 'undefined') { meta_key = $.WS_Form.meta_keys[meta_key]['key']; }

			repeater_row[meta_key] = meta_value;
		}

		return repeater_row;
	}

	// Sidebar - Repeater - Get data
	$.WS_Form.prototype.sidebar_repeater_get = function(obj) {

		// Get meta key
		var meta_key = obj.attr('data-meta-key');

		// Read meta key config
		if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { return false; }
		var meta_key_config = $.WS_Form.meta_keys[meta_key];

		// Read meta keys for repeater
		if(typeof(meta_key_config['meta_keys']) !== 'object') { return false; }
		var meta_keys = meta_key_config['meta_keys'];

		// Custom column names
		var columns = (typeof(meta_key_config['columns']) !== 'undefined') ? meta_key_config['columns'] : false;

		// Read meta keys unique
		var meta_keys_unique = (typeof(meta_key_config['meta_keys_unique']) !== 'undefined') ? meta_key_config['meta_keys_unique'] : false;

		// Get object
		var object = obj.attr('data-object');

		// Get object ID
		var object_id = obj.attr('data-id');

		// Get data
		var object_data = $.WS_Form.this.get_object_data(object, object_id, true);

		// Get repeater data
		var data = $.WS_Form.this.get_object_meta_value(object_data, meta_key, [], true);
		if(typeof(data) !== 'object') { data = []; }

		return {'object': object, 'object_id': object_id, 'meta_key': meta_key, 'meta_keys': meta_keys, 'meta_keys_unique': meta_keys_unique, 'object_data': object_data, 'data': data, 'columns': columns};
	}

	// Sidebar - Repeater - HTML
	$.WS_Form.prototype.sidebar_repeater_html = function(repeater) {

		var repeater_inits = [];

		// Build repeater
		var repeater_html = '<table class="wsf-repeater-table"><thead><tr>';

		// Blank column for sort icons
		repeater_html += '<th data-icon></th>';

		// Header columns
		for(var meta_keys_index in repeater.meta_keys) {

			if(!repeater.meta_keys.hasOwnProperty(meta_keys_index)) { continue; }

			var meta_keys_single = repeater.meta_keys[meta_keys_index];

			// Ensure meta key is configured
			if(typeof($.WS_Form.meta_keys[meta_keys_single]) === 'undefined') { return false; }

			var meta_key_single = $.WS_Form.meta_keys[meta_keys_single];

			var column_id = (typeof(meta_key_single.column_id) !== 'undefined') ? ' data-id="' + this.esc_attr(meta_key_single.column_id) + '"' : '';

			if(repeater.columns && repeater.columns[meta_keys_index]) {

				// Custom column name
				repeater_html += '<th' + column_id + '>' + this.esc_html(repeater.columns[meta_keys_index]) + '</th>';

			} else {

				// Meta key label for column name
				repeater_html += '<th' + column_id + '>' + this.esc_html(meta_key_single.label) + '</th>';
			}
		}

		// Blank column for delete icons
		repeater_html += '<th data-icon></th>';

		repeater_html += '</tr></thead><tbody>';

		// Rows
		for(var repeater_data_index in repeater.data) {

			if(!repeater.data.hasOwnProperty(repeater_data_index)) { continue; }

			var repeater_data_row = repeater.data[repeater_data_index];

			var sidebar_html_return = this.sidebar_repeater_row_html(repeater, repeater_data_row, repeater.meta_key);

			repeater_html += sidebar_html_return.html;

			repeater_inits = repeater_inits.concat(sidebar_html_return.inits);
		}

		repeater_html += '</tbody></table>';

		// Add button
		repeater_html += '<div data-action="wsf-repeater-row-add"><div' + this.tooltip(this.language('repeater_row_add'), 'left') + '>' + this.svg('plus-circle') + '</div></div>';

		// Add column-toggle to repeater_inits
		repeater_inits.push('column-toggle');

		return {'html': repeater_html, 'inits': repeater_inits};
	}

	// Sidebar - Repeater - Row - HTML
	$.WS_Form.prototype.sidebar_repeater_row_html = function(repeater, repeater_data_row, repeater_meta_key) {

		var repeater_row_inits = [];

		var repeater_row_html = '<tr>';

		// Sort
		repeater_row_html += '<td data-icon><div data-action="wsf-repeater-row-sort"' + this.tooltip(this.language('repeater_row_sort'), 'top-left') + '>' + this.svg('sort') + '</div></td>'

		// Build object data (sidebar_html then uses this to extract the meta_value)
		var object_data = [];
		object_data['meta'] = repeater_data_row;

		for(var meta_keys_index in repeater.meta_keys) {

			if(!repeater.meta_keys.hasOwnProperty(meta_keys_index)) { continue; }

			var meta_keys_single = repeater.meta_keys[meta_keys_index];

			var meta_key_single = $.WS_Form.meta_keys[meta_keys_single];

			var column_id = (typeof(meta_key_single.column_id) !== 'undefined') ? ' data-id="' + this.esc_attr(meta_key_single.column_id) + '"' : '';

			var repeater_fieldset = {'fieldsets': {}};
			repeater_fieldset.fieldsets[repeater.object_id] = {'meta_keys': [meta_keys_single]};

			var sidebar_html_return = this.sidebar_html(repeater.object, repeater.object_id, object_data, repeater_fieldset, repeater_meta_key);

			repeater_row_html += '<td' + column_id + '>' + sidebar_html_return.html + '</td>';

			repeater_row_inits = repeater_row_inits.concat(sidebar_html_return.inits);
		}

		repeater_row_html += '<td data-icon><div data-action="wsf-repeater-row-delete"' + this.tooltip(this.language('repeater_row_delete'), 'top-right') + '>' + this.svg('delete-circle') + '</div></td>';

		repeater_row_html += '</tr>';

		return {'html': repeater_row_html, 'inits': repeater_row_inits};
	}

	// Sidebar - Range - Init
	$.WS_Form.prototype.sidebar_range_init = function(obj_inner) {

		// Configure all range sliders
		$('input[type="range"]', obj_inner).each(function() {

			// Value changes
			$(this).on('input', function() {

				$.WS_Form.this.sidebar_range_update(obj_inner, $(this));
			});

			$.WS_Form.this.sidebar_range_update(obj_inner, $(this));
		});
	}

	// Sidebar - Range - Process
	$.WS_Form.prototype.sidebar_range_update = function(obj_inner, obj) {

		var value = obj.val();
		var meta_key = obj.attr('data-meta-key');
		var range_value_mask = (typeof(obj.attr('data-range-value-mask')) !== 'undefined') ? obj.attr('data-range-value-mask') : '#value';

		var range_value_mask_lookups = {

			value: value,
			value_plural: (value == 1) ? '' : 's'
		};

		var range_value_html = this.mask_parse(range_value_mask, range_value_mask_lookups);

		$('#wsf_' + meta_key + '_range_value', obj_inner).html(range_value_html);
	}

	// Sidebar - Number - Init
	$.WS_Form.prototype.sidebar_number_init = function(obj_inner) {

		// Configure default values that are number inputs
		var obj = $('input[type="number"][data-meta-key="default_value"]', obj_inner).first();
		if(!obj.length) { return false; }

		// Look for min, max or step value value
		$('[data-meta-key="min"],[data-meta-key="max"],[data-meta-key="step"]', obj_inner).on('change', function() { $.WS_Form.this.sidebar_default_value_attributes(obj_inner, obj, 'number'); });
		$.WS_Form.this.sidebar_default_value_attributes(obj_inner, obj, 'number');
	}

	// Sidebar - Update default value attributes
	$.WS_Form.prototype.sidebar_default_value_attributes = function(obj_outer, obj, type) {

		// Get obj value
		var value = obj.val();

		var obj_min = $('[data-meta-key="min"]', obj_outer).first();
		var obj_max = $('[data-meta-key="max"]', obj_outer).first();
		var obj_step = $('[data-meta-key="step"]', obj_outer).first();

		// Get values according to type
		switch(type) {

			case 'range' :

				var min = this.get_number(obj_min.val(), 0);
				var max = this.get_number(obj_max.val(), 100);
				var step = this.get_number(obj_step.val(), 1);

				if((obj_max.val() != '') && (min > max)) { min = max; obj_min.val(min); }

				if(min == 0) { obj.removeAttr('min'); } else { obj.attr('min', min); }
				if(max == 100) { obj.removeAttr('max'); } else { obj.attr('max', max); }
				if(step == 1) { obj.removeAttr('step'); } else { obj.attr('step', step); }

				// Check value
				obj.val(value > max ? max : value < min ? min : value);

				break;

			case 'number' :

				var min = (obj_min.val() != '') ? this.get_number(obj_min.val()) : false;
				var max = (obj_min.val() != '') ? this.get_number(obj_max.val()) : false;
				var step = (obj_min.val() != '') ? this.get_number(obj_step.val()) : false;

				if((min !== false) && (max !== false) && (min > max)) { min = max; obj_min.val(min); }

				if(min === false) { obj.removeAttr('min'); } else { obj.attr('min', min); }
				if(max === false) { obj.removeAttr('max'); } else { obj.attr('max', max); }
				if(step === false) { obj.removeAttr('step'); } else { obj.attr('step', step); }

				// Check value
				if((max !== false) && (value > max)) { obj.val(max); }
				if((min !== false) && (value < min)) { obj.val(min); }
		}
	}

	// Sidebar - Tabs - Init
	$.WS_Form.prototype.sidebar_tabs_init = function(obj_outer, obj_inner) {

		// Init tabs
		obj_outer.tabs({

			activate: function() {

				// Reset scrolling
				obj_inner.scrollTop(0);

				// Initialize TinyMCE
				$.WS_Form.this.sidebar_tinymce_init(obj_inner);

				// Initialize HTML
				$.WS_Form.this.sidebar_html_editor_init(obj_inner);
			}
		});
	}

	// Sidebar - Field Select - Init
	$.WS_Form.prototype.sidebar_field_select_init = function() {

		// Build search array
		var search_array = [];

		for(var group_key in $.WS_Form.field_types) {

			if(!$.WS_Form.field_types.hasOwnProperty(group_key)) { continue; }
 
			// Get group
			var group = $.WS_Form.field_types[group_key];
			var group_label = group.label.toLowerCase();

			// Get types in group
			var types = group.types;

			// Skip empty groups
			if(types.length == 0) { continue; }

			// Run through each type
			for (var type in types) {

				if(!types.hasOwnProperty(type)) { continue; }

				// Get field type config
				var field_type_config = types[type];

				// Build keyword
				var keyword = field_type_config.label.toLowerCase();
				if(typeof(field_type_config.keyword) !== 'undefined') {

					keyword += ' ' + field_type_config.keyword.toLowerCase();
				}
				keyword += ' ' + group_label;

				// Add field type to search array
				search_array.push({keyword: keyword, type: type});
			}
		}

		// Keyword search
		var field_select_html = '<fieldset class="wsf-fieldset"><div class="wsf-field-wrapper""><input id="wsf-field-selector-search" class="wsf-field" type="search" placeholder="' + this.language('field_search') + '" /></div></fieldset>';

		// Field select
		field_select_html += '<div class="wsf-field-selector">' + this.sidebar_field_select_html() + '</div>';


		// Add field types
		$('#wsf-form-field-selector').html(field_select_html);

		// Search
		$('#wsf-field-selector-search').on('input change paste', function() {

			var keywords = $(this).val();
			keywords = keywords.toLowerCase().trim();

			var keyword_array = keywords.split(' ');

			var types_matched = [];

			for(var keyword_array_index in keyword_array) {

				if(!keyword_array.hasOwnProperty(keyword_array_index)) { continue; }

				var keyword = keyword_array[keyword_array_index];

				// Trim keyword
				keyword = keyword.trim();

				for(var search_array_index in search_array) {

					if(!search_array.hasOwnProperty(search_array_index)) { continue; }

					var search_array_config = search_array[search_array_index];

					var score = 0;

					var search_array_keyword = search_array_config.keyword;

					var search_array_keyword_indexof = search_array_keyword.indexOf(keyword);

					if(search_array_keyword_indexof === 0) { score += 2; }
					if(search_array_keyword_indexof > 0) { score += 1; }

					if(score > 0) {

						if(typeof(types_matched[search_array_config.type]) === 'undefined') {

							types_matched[search_array_config.type] = score;

						} else {

							types_matched[search_array_config.type] += score;
						}
					}
				};
			}

			// Sort by score descending
			var types_matched = Object.fromEntries(Object.entries(types_matched).sort(([, a], [, b]) => b - a));

			// Hide all
			$('#wsf-form-field-selector li.wsf-field-selector-group').hide();
			$('#wsf-form-field-selector li.wsf-field-wrapper[data-type]').hide().attr('data-hidden', '');

			// Show matching types
			for(var type in types_matched) {

				if(!types_matched.hasOwnProperty(type)) { continue; }

				$('#wsf-form-field-selector li.wsf-field-wrapper[data-type="' + $.WS_Form.this.esc_selector(type) + '"]').show().removeAttr('data-hidden');
			}

			// Show groups that have matching types
			$('#wsf-form-field-selector li.wsf-field-selector-group').each(function() {

				if($('li.wsf-field-wrapper:not([data-hidden])', $(this)).length) {

					$(this).show();
				}
			});
		});

		// Make field types draggable
		$('.wsf-field-selector-group > ul > li:not(.wsf-pro-required)').draggable({

			connectToSortable: '.wsf-fields',
			helper: 'clone',
			zIndex: 100001,
			cancel: '.wsf-field-disabled, .wsf-pro-required',
			appendTo: '#wsf-field-draggable ul',
			distance: 3,
			start: function(e, ui) {

				// Set correct width
				ui.helper.width($(this).width());

				// Add label
				var field_label = $('.wsf-field-type', ui.helper).html();
				$('.wsf-field-label', ui.helper).append('<input type="text" value="' + $.WS_Form.this.esc_attr(field_label) + '" readonly aria-label="' + $.WS_Form.this.esc_attr($.WS_Form.this.language('field_label')) + '">');

				// Store the field object that was cloned
				$.WS_Form.this.dragged_field = $(ui.helper);

				// Mobile reset
				$.WS_Form.this.sidebar_reset_mobile();
			},
			drag: function() {

				$.WS_Form.this.field_type_click_drag_check = true;
			},
			stop: function() {

				if(!$.WS_Form.this.dragged_field_in_section) {

					$.WS_Form.this.dragged_field = null;
				}
			}

		}).disableSelection();

		// Make field type clickable
		$('.wsf-field-selector-group > ul > li:not(.wsf-pro-required)').on('mousedown', function() {

			$.WS_Form.this.field_type_click_drag_check = false;
		});
		$('.wsf-field-selector-group > ul > li:not(.wsf-pro-required)').on('mouseup', function() {

			if(
				!$(this).hasClass('wsf-field-disabled') &&
				($.WS_Form.this.field_type_click_drag_check === false) &&
				!$('body').hasClass('wsf-column-size-change-body') &&
				!$('body').hasClass('wsf-offset-change-body') &&
				!$.WS_Form.this.dragging
			) {

				// Get field type
				var field_type = $(this).attr('data-type');

				// Get field type data
				var field_type_config = $.WS_Form.field_type_cache[field_type];

				// Check to see if multiple attribute is set
				var multiple = (typeof(field_type_config['multiple']) !== 'undefined') ? field_type_config['multiple'] : true;
				if(!multiple) { $(this).addClass('wsf-field-disabled'); }

				// Get section ID
				var section_id = $.WS_Form.this.field_type_click_section_id_set();
				if(section_id === false) { return; }

				// Build field HTML
				var field_html = '<li class="wsf-field-wrapper" data-type="' + $.WS_Form.this.esc_attr(field_type) + '">' + $(this).html() + '</li>';
				$('#wsf-fields-' + $.WS_Form.this.esc_selector(section_id)).append(field_html);

				// Get new field object
				var field_obj = $('#wsf-fields-' + $.WS_Form.this.esc_selector(section_id) + ' li').last();

				// Set label
				var field_label = $('.wsf-field-type', $(this)).html();
				$('.wsf-field-label', field_obj).append('<input type="text" value="' + $.WS_Form.this.esc_attr(field_label) + '" readonly aria-label="' + $.WS_Form.this.esc_attr($.WS_Form.this.language('field_label')) + '>');

				// Push new field to AJAX
				$.WS_Form.this.field_post(field_obj);

				// Init UI
				$.WS_Form.this.init_ui();
			}
		});

		// Button - Cancel
		$('[data-action="wsf-sidebar-cancel"]', $('#wsf-sidebar-toolbox')).on('click', function() {

			$.WS_Form.this.sidebar_reset();
		});
	}

	// Sidebar - Field Select - HTML
	$.WS_Form.prototype.sidebar_field_select_html = function() {

		var field_select_html = '<ul>';

		// Add field types
		for (var group_key in $.WS_Form.field_types) {

			var group = $.WS_Form.field_types[group_key];
			var label = group.label;
			var types = group.types;

			// Skip empty groups
			if(types.length == 0) { continue; }

			var field_select_html_fields = '';

			// Add field types
			for (var type in types) {

				var field_type = types[type];

				// Is pro required? (i.e. edition is not pro)
				var pro_required = field_type.pro_required;
				field_select_html_fields += '<li class="wsf-field-wrapper' + (pro_required ? ' wsf-pro-required' : '') + '" data-type="' + this.esc_attr(type) + '"><div class="wsf-field-inner"><div class="wsf-field-label">' + field_type.icon + '</div><div class="wsf-field-type">' + field_type.label + '</div></div></li>';
			}

			if(field_select_html_fields != '') {

				field_select_html += '<li class="wsf-field-selector-group wsf-fields-group-' + group_key + '"><h3>' + label + '</h3><ul>' + field_select_html_fields + '</ul></li>';
			}
		}

		field_select_html += '</ul>';

		return field_select_html;
	}

	// Sidebar - Section Select - Init
	$.WS_Form.prototype.sidebar_section_select_init = function() {

		// Build search array
		var search_array = [];

		// Add field types
		for(var template_category_index in $.WS_Form.templates_section) {

			if(!$.WS_Form.templates_section.hasOwnProperty(template_category_index)) { continue; }

			// Get template category
			var template_category = $.WS_Form.templates_section[template_category_index];

			// Get template category label
			if(typeof(template_category.label) !== 'string') { continue; }
			var template_category_label = template_category.label.toLowerCase();

			// Get templates
			if(typeof(template_category.templates) !== 'object') { continue; }
			var templates = template_category.templates;

			// Skip empty template categories
			if(templates.length == 0) { continue; }

			// Add field types
			for(var template_key in templates) {

				if(!templates.hasOwnProperty(template_key)) { continue; }

				// Get template
				var template = templates[template_key];

				// Get template ID
				if(typeof(template.id) === 'undefined') { continue; }
				var template_id = template.id;

				// Build keyword
				if(typeof(template.label) !== 'string') { continue; }
				var keyword = template.label.toLowerCase();

				if(typeof(template.keyword) !== 'undefined') {

					keyword += ' ' + template.keyword.toLowerCase();
				}
				keyword += ' ' + template_category_label;

				// Add field type to search array
				search_array.push({keyword: keyword, id: template_id});				
			}
		}

		// Keyword search
		var section_select_html = '<fieldset class="wsf-fieldset"><div class="wsf-field-wrapper""><input id="wsf-section-selector-search" class="wsf-field" type="search" placeholder="' + this.language('section_search') + '" /></div></fieldset>';

		// Section select
		section_select_html += '<div class="wsf-section-selector">' + this.sidebar_section_select_html() + '</div>';

		var section_selector_obj = $('#wsf-form-section-selector');


		// Add sections
		section_selector_obj.html(section_select_html);

		$('.wsf-section-selector-group', section_selector_obj).each(function() {

			var section_select_group_obj = $(this);

			// Drag enter
			$(this).on('dragenter', function (e) {

				e.stopPropagation();
				e.preventDefault();

				// Check dragged object is a file
				if(!$.WS_Form.this.drag_is_file(e)) { return; }

				$('.wsf-section-selector-upload-json-window', section_select_group_obj).show();
			});

			// Drag over
			$('.wsf-section-selector-upload-json-window', $(this)).on('dragover', function (e) {

				e.stopPropagation();
				e.preventDefault();
			});

			// Drop
			$('.wsf-section-selector-upload-json-window', $(this)).on('drop', function (e) {

				e.preventDefault();

				var files = e.originalEvent.dataTransfer.files;

				$.WS_Form.this.section_upload_json(files, $(this), function(response) {

					// Handle response from template API endpoing
					$.WS_Form.this.template_api_response(response);

				}, function() {

					$('.wsf-section-selector-upload-json-window', section_select_group_obj).hide();
				});
			});

			// Drag leave
			$('.wsf-section-selector-upload-json-window', $(this)).on('dragleave', function (e) {

				$('.wsf-section-selector-upload-json-window', section_select_group_obj).hide();
			});

			// Upload
			$('[data-action="wsf-section-upload"]', $(this)).on('click', function(e) {

				// Click file input
				$('input[id="wsf-section-upload-file"]', section_select_group_obj).trigger('click');
			});

			$('input[id="wsf-section-upload-file"]', $(this)).on('change', function() {

				var files = $('input[id="wsf-section-upload-file"]').prop("files");

				if(files.length > 0) {

					var section_upload_window = $('.wsf-section-selector-upload-json-window', section_select_group_obj);
					section_upload_window.show();
					$.WS_Form.this.section_upload_json(files, section_upload_window, function(response) {

						// Handle response from template API endpoing
						$.WS_Form.this.template_api_response(response);

					}, function() {

						$('.wsf-section-selector-upload-json-window', section_select_group_obj).hide();
					});
				}
			});
		});

		// Export
		$('[data-action="wsf-section-download"]', section_selector_obj).on('mouseup', function(e) {

			e.preventDefault();
			e.stopPropagation();

			// Get section
			var section_obj = $(this).closest('.wsf-section');

			// Get template ID
			var template_id = section_obj.attr('data-id');

			// Download template
			$.WS_Form.this.template_download(template_id);
		});

		// Delete
		$('[data-action="wsf-section-delete"]', section_selector_obj).on('mouseup', function(e) {

			e.preventDefault();
			e.stopPropagation();

			// Get section
			var section_obj = $(this).closest('.wsf-section');

			// Get template ID
			var template_id = section_obj.attr('data-id');

			// Buttons
			var buttons = [

				{label:$.WS_Form.this.language('cancel'), action:'wsf-cancel'},
				{label:$.WS_Form.this.language('delete'), action:'wsf-confirm', class:'wsf-button-danger'}
			];

			$.WS_Form.this.popover($.WS_Form.this.language('confirm_section_template_delete'), buttons, section_obj, function() {

				// Build params
				var params = {

					type: 			'section',
					template_id: 	template_id,
				};

				// Call AJAX request
				$.WS_Form.this.api_call('template/delete/', 'POST', params, function(response) {

					// Handle response from template API endpoing
					$.WS_Form.this.template_api_response(response);

					// Loader off
					$.WS_Form.this.loader_off();
				});
			});
		});

		// Search
		$('#wsf-section-selector-search', section_selector_obj).on('input change paste', function() {

			var keywords = $(this).val();
			keywords = keywords.toLowerCase().trim();

			var keyword_array = keywords.split(' ');

			var ids_matched = [];

			for(var keyword_array_index in keyword_array) {

				if(!keyword_array.hasOwnProperty(keyword_array_index)) { continue; }

				var keyword = keyword_array[keyword_array_index];
				keyword = keyword.trim();

				search_array.find(function(search_array_config) {

					var score = 0;

					var search_array_keyword = search_array_config.keyword;

					var search_array_keyword_indexof = search_array_keyword.indexOf(keyword);

					if(search_array_keyword_indexof === 0) { score += 2; }
					if(search_array_keyword_indexof > 0) { score += 1; }

					if(score > 0) {

						if(typeof(ids_matched[search_array_config.id]) === 'undefined') {

							ids_matched[search_array_config.id] = score;

						} else {

							ids_matched[search_array_config.id] += score;
						}
					}
				});
			}

			// Order by score
			function ids_matched_compare(a, b) {

				return (a == b) ? 0 : ((a < b) ? -1 : 1);
			}
			ids_matched.sort(ids_matched_compare);

			// Hide all
			var form_section_select_obj = $('#wsf-form-section-selector');
			$('li.wsf-section-selector-group', form_section_select_obj).hide();
			$('li.wsf-section[data-id], li.wsf-section-blank', form_section_select_obj).hide().attr('data-hidden', '');

			// Show matching types
			for(var id in ids_matched) {

				if(!ids_matched.hasOwnProperty(id)) { continue; }

				$('li.wsf-section[data-id="' + $.WS_Form.this.esc_selector(id) + '"]', form_section_select_obj).show().removeAttr('data-hidden');
			}

			// If nothing is hidden, show blank sections
			if(!$('li.wsf-section[data-hidden]', form_section_select_obj).length) {

				$('li.wsf-section-blank', form_section_select_obj).show().removeAttr('data-hidden');
			}

			// Show groups that have matching types
			$('.wsf-section-selector-group', form_section_select_obj).each(function() {

				if($('li.wsf-section:not([data-hidden]),li.wsf-section-blank:not([data-hidden])', $(this)).length) {

					$(this).show();
				}
			});
		});

		// Make sections draggable
		$('.wsf-section-selector-group > ul.wsf-section-selector-group-sections > li:not(.wsf-pro-required,.wsf-section-blank)').draggable({

			connectToSortable: '.wsf-sections',
			helper: 'clone',
			zIndex: 100001,
			cancel: '.wsf-section-disabled, .wsf-pro-required, [data-action]',
			appendTo: '#wsf-section-draggable ul',
			distance: 3,
			start: function(e, ui) {

				// Set correct width
				ui.helper.width($(this).width());

				// Store the field object that was cloned
				$.WS_Form.this.dragged_section = $(ui.helper);

				// Mobile reset
				$.WS_Form.this.sidebar_reset_mobile();
			},
			drag: function() {

				$.WS_Form.this.section_id_click_drag_check = true;
			},
			stop: function() {

				if(!$.WS_Form.this.dragged_section_in_group) {

					$.WS_Form.this.dragged_section = null;
				}
			}

		}).disableSelection();

		// Make section type clickable
		$('.wsf-section-selector-group > ul.wsf-section-selector-group-sections > li:not(.wsf-pro-required,.wsf-section-blank)').on('mousedown', function() {

			$.WS_Form.this.section_type_click_drag_check = false;
		});
		$('.wsf-section-selector-group > ul.wsf-section-selector-group-sections > li:not(.wsf-pro-required,.wsf-section-blank)').on('mouseup', function() {

			if(
				!$(this).hasClass('wsf-section-disabled') &&
				($.WS_Form.this.section_type_click_drag_check === false) &&
				!$('body').hasClass('wsf-column-size-change-body') &&
				!$('body').hasClass('wsf-offset-change-body') &&
				!$.WS_Form.this.dragging
			) {

				// Get height of helper
				var height = $('.wsf-section-inner', $(this)).height();

				// Get template title
				var label = $('.wsf-template-title tspan', $(this)).html();

				// Get data ID
				var id = $(this).attr('data-id');

				// Get currently active tab index
				var group_id = $('.wsf-group-tabs li.ui-tabs-active').attr('data-id');

				// Get current group
				var group_obj = ($('.wsf-groups .wsf-group[data-id="' + $.WS_Form.this.esc_selector(group_id) + '"]', $.WS_Form.this.form_obj));

				// Build section HTML
				var section_html = '<li class="wsf-section" data-id="' + $.WS_Form.this.esc_attr(id) + '"><div class="wsf-section-inner" style="height:' + $.WS_Form.this.esc_attr(height) + 'px"><div class="wsf-section-label"><input type="text" value="' + $.WS_Form.this.esc_attr(label) + '" readonly></div><div class="wsf-section-type">' + $.WS_Form.this.language('section') + '</div></div></li>';

				// Append HTML to group
				$('ul.wsf-sections', group_obj).append(section_html);

				// Get section
				var section_obj = $('ul.wsf-sections li', group_obj).last();

				// Push new section to AJAX
				$.WS_Form.this.template_section_post(section_obj);

				// Init UI
				$.WS_Form.this.init_ui();
			}
		});
	}

	$.WS_Form.prototype.sidebar_section_select_html = function() {

		var section_select_html = '<ul>';

		// Add field types
		for(var template_category_index in $.WS_Form.templates_section) {

			if(!$.WS_Form.templates_section.hasOwnProperty(template_category_index)) { continue; }

			var template_category = $.WS_Form.templates_section[template_category_index];

			var template_category_id = template_category.id;
			var template_category_label = template_category.label;
			var template_category_upload = (typeof(template_category.upload) !== 'undefined') ? template_category.upload : false;
			var template_category_download = (typeof(template_category.download) !== 'undefined') ? template_category.download : false;
			var template_category_delete = (typeof(template_category.delete) !== 'undefined') ? template_category.delete : false;
			var templates = template_category.templates;

			// Skip empty template categories
			if(
				(template_category_id != 'wsfuser') &&
				(templates.length == 0)

			) { continue; }

			var section_select_html_templates = '';

			// Add field types
			for(var template_index in templates) {

				if(!templates.hasOwnProperty(template_index)) { continue; }

				var template = templates[template_index];
				var template_id = template.id;
				var template_label = template.label;
				var template_pro_required = template.pro_required;

				// Is pro required? (i.e. edition is not pro)
				if(template_pro_required) { continue; }

				// Template
				section_select_html_templates += '<li class="wsf-section' + (template_pro_required ? ' wsf-pro-required' : '') + '" data-id="' + this.esc_attr(template_id) + '" aria-label="' + this.esc_attr(template_label) + '"><div class="wsf-section-inner">' + template.svg + '</div>';

				// Download icon
				if(template_category_download) {

					section_select_html_templates += '<div data-action="wsf-section-download"' + this.tooltip(this.language('section_download'), 'top-right') + '>' + this.svg('download') + '</div>';
				}

				// Delete icon
				if(template_category_delete) {

					section_select_html_templates += '<div data-action="wsf-section-delete"' + this.tooltip(this.language('section_delete'), 'top-right') + '>' + this.svg('delete') + '</div>';
				}

				section_select_html_templates += '</li>';
			}

			if(
				(section_select_html_templates == '') &&
				(template_category_id == 'wsfuser')
			) {

				section_select_html_templates = '<li class="wsf-section-blank"><div>' + this.language('section_selector_drop_zone', false, false) + '</div></li>';
			}

			if(section_select_html_templates != '') {

				section_select_html += '<li class="wsf-section-selector-group">';

				// Icon array
				var li_array = [];

				// Icon - Upload
				if(template_category_upload) {

					li_array.push('<li><div data-action="wsf-section-upload"' + this.tooltip(this.language('section_selector_import'), 'top-right') + '>' + this.svg('upload') + '</div><input type="file" class="wsf-section-upload" id="wsf-section-upload-file" accept=".json"/></li>');
				}

				if(li_array.length) { section_select_html += '<ul class="wsf-section-selector-group-options">' + li_array.join('') + '</ul>'; }

				// Category label + Sections
				section_select_html += '<h3>' + template_category_label + '</h3><ul class="wsf-section-selector-group-sections">' + section_select_html_templates + '</ul>';

				// Drop zone
				if(template_category_id == 'wsfuser') {

					section_select_html += '<div class="wsf-section-selector-upload-json-window"><div class="wsf-section-selector-upload-json-window-content"><h1>' + this.language('drop_zone_section') + '</h1><div class="wsf-uploads"></div></div></div>';
				}

				section_select_html += '</li>';
			}
		}

		section_select_html += '</ul>';

		return section_select_html;
	}

	// Section - Uploader
	$.WS_Form.prototype.section_upload_json = function(files, obj, success_callback, error_callback, show_confirm) {

		// Hide H1
		$('h1', obj).hide();

		if(files.length == 0) {

			error_callback();

			return false;
		}

		// Create form data
		var form_data = new FormData();
		form_data.append('file', files[0]);
		form_data.append(ws_form_settings.wsf_nonce_field_name, ws_form_settings.wsf_nonce);

		// Create status bar for this file
		var status_bar = new this.upload_status_bar(this, obj)

		// Populate status_bar
		status_bar.populate(files[0].name, files[0].size);

		// Send file to the server using AJAX
		this.section_upload_ajax(form_data, status_bar, obj, success_callback, error_callback);
	}

	// Section - Uploaded JSON - AJAX request
	$.WS_Form.prototype.section_upload_ajax = function(form_data, status_bar, obj, success_callback, error_callback) {

		var url = ws_form_settings.url_ajax + 'template/upload/json';

		var jqXHR = $.ajax({

			beforeSend: function(xhr) {

				xhr.setRequestHeader('X-WP-Nonce', ws_form_settings.x_wp_nonce);
			},

			xhr: function() {

				// Upload progress
				var xhrobj = $.ajaxSettings.xhr();
				if (xhrobj.upload) {

					xhrobj.upload.addEventListener('progress', function(e) {

						var percent = 0;
						var position = e.loaded || e.position;
						var total = e.total;
						if (e.lengthComputable) {
							percent = Math.ceil(position / total * 100);
						}

						status_bar.set_progress(percent);

					}, false);
				}

				return xhrobj;
			},

			url: url,
			type: 'POST',
			contentType: false,
			processData: false,
			cache: false,
			data: form_data,

			success: function(response) {

				// Set progress bar to 100%
				status_bar.set_progress(100);

				// Call success script
				if(typeof(success_callback) === 'function') { success_callback(response); }
			},

			error: function(response) {

				// Process error
				$.WS_Form.this.api_call_error_handler(response, url, error_callback);
			}
		});

		status_bar.set_abort(jqXHR);
	}

	// Sidebar - Form History - Init
	$.WS_Form.prototype.sidebar_form_history_init = function() {

		// Inject HTML
		var form_history_html = '<div class="wsf-form-history"><h3>' + this.language('sidebar_title_history') + '</h3><ul></ul></div>';
		$('#wsf-form-form-history').html(form_history_html);

		// Event - History - Mouse - Leave
		if(!this.touch_device) {

			$('.wsf-form-history ul').on('mouseleave', function() {

				var history_index = $.WS_Form.this.history_index;
				$.WS_Form.this.history_pull(history_index);

				// Update history classes
				$.WS_Form.this.sidebar_form_history_classes();
			});
		}
	}

	// Sidebar - TinyMCE - Init
	$.WS_Form.prototype.sidebar_tinymce_init = function(obj_inner) {

		if(typeof(wp) === 'undefined') { return false; }
		if(typeof(wp.editor) === 'undefined') { return false; }
		if(typeof(wp.editor.remove) === 'undefined') { return false; }
		if(typeof(wp.editor.initialize) === 'undefined') { return false; }

		$('[data-text-editor="true"]:visible', obj_inner).each(function() {

			var id = $(this).attr('id');

			var init = { 

				tinymce: { 

					wpautop: 			true,
					plugins: 			'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview', 
					toolbar1: 			'formatselect bold italic | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | spellchecker | fullscreen | wp_adv',
					toolbar2: 			'strikethrough hr forecolor pastetext removeformat charmap outdent indent undo redo',
					height: 			'200px', 

					init_instance_callback: function (editor) {

						editor.on('keyup input change', function (e) {

							$('#' + editor.id).val(wp.editor.getContent(editor.id)).trigger('keyup').trigger('input');
						});
					}
				},

				quicktags: true,
				mediaButtons: true
			};

			// CSS
			var css = $(this).attr('data-helper-css');
			if(css) { init.tinymce.content_css = ws_form_settings.url_ajax + 'helper/' + css + '/'; }

			wp.editor.remove(id); 
			wp.editor.initialize(id, init)
		})
	}

	// Edit - Button - Save
	$.WS_Form.prototype.sidebar_buttons_init = function(obj, obj_outer) {

		// Init save events
		$('button[data-action="wsf-sidebar-save"]', obj_outer).on('click', function() {

			// Save
			$.WS_Form.this.object_button_save(obj, false);
		});

		// Init save and close events
		$('button[data-action="wsf-sidebar-save-close"]', obj_outer).on('click', function() {

			// Save and close
			$.WS_Form.this.object_button_save(obj, true);
		});

		// Init cancel events
		$('button[data-action="wsf-sidebar-cancel"]', obj_outer).on('click', function() {

			// Cancel
			$.WS_Form.this.object_button_cancel(obj);
		});

		// Init clone events
		$('button[data-action="wsf-sidebar-clone"]', obj_outer).on('click', function() {

			// Clone
			$.WS_Form.this.object_button_clone(obj);
		});

		// Init delete events
		$('button[data-action="wsf-sidebar-delete"]', obj_outer).on('click', function() {

			// Clone
			$.WS_Form.this.object_button_delete(obj);
		});

		// Set up key shortcuts
		$.WS_Form.this.keydown[27] = {'function': function() { $.WS_Form.this.object_button_cancel(obj); }, 'ctrl_key': false};
		$.WS_Form.this.keydown[83] = {'function': function() { $.WS_Form.this.object_button_save(obj, true); }, 'ctrl_key': true};
	}

	// Sidebar - Init conditions
	$.WS_Form.prototype.sidebar_conditions_init = function(obj_sidebar_outer) {

		if(this.sidebar_conditions.length === 0) { return true; }

		var ws_this = this;

		// Sidebar condition events
		var sidebar_condition_added = [];
		if(typeof(obj_sidebar_outer.attr('data-sidebar-conditions-init')) === 'undefined') {

			// Get main obj_sidebar_outer in case this is a data source obj
			if(!obj_sidebar_outer.hasClass('wsf-sidebar')) {

				obj_sidebar_outer = obj_sidebar_outer.closest('.wsf-sidebar');
			}

			for(var sidebar_conditions_index in this.sidebar_conditions) {

				if(!this.sidebar_conditions.hasOwnProperty(sidebar_conditions_index)) { continue; }

				// Get sidebar condition meta key
				var sidebar_condition = this.sidebar_conditions[sidebar_conditions_index];

				// Check type
				if(sidebar_condition.type !== 'sidebar_meta_key') { continue; }

				var sidebar_condition_meta_key = sidebar_condition.meta_key;

				// Check meta key exists
				var meta_key_selector = '[data-meta-key="' + this.esc_selector(sidebar_condition_meta_key) + '"]';
				var data_meta_key = $(meta_key_selector, obj_sidebar_outer);
				if(data_meta_key.length == 0) { continue; }

				// Ensure only one change event is added per meta key
				if(typeof(sidebar_condition_added[sidebar_condition_meta_key]) !== 'undefined') { continue; }
				sidebar_condition_added[sidebar_condition_meta_key] = true;

				// Create on change event
				obj_sidebar_outer.on('input change', meta_key_selector, function() { ws_this.sidebar_condition_process(obj_sidebar_outer, $(this), false); });
			}

			obj_sidebar_outer.attr('data-sidebar-conditions-init', '');
			this.sidebar_conditions_events_added = true;
		}

		// Initial run
		this.sidebar_condition_process(obj_sidebar_outer, obj_sidebar_outer, true);
	}

	// Sidebar - Condition process
	$.WS_Form.prototype.sidebar_condition_process = function(obj_sidebar_outer, obj, initial_run) {

		if(this.sidebar_conditions.length == 0) { return true; }

		var condition_result_array = [];

		// Run all conditions
		for(var sidebar_conditions_index in this.sidebar_conditions) {

			if(!this.sidebar_conditions.hasOwnProperty(sidebar_conditions_index)) { continue; }

			var sidebar_condition = this.sidebar_conditions[sidebar_conditions_index];

			var sidebar_condition_type = sidebar_condition.type;
			var sidebar_condition_logic = sidebar_condition.logic;
			var sidebar_condition_meta_key = sidebar_condition.meta_key;
			var sidebar_condition_meta_value = sidebar_condition.meta_value;
			var sidebar_condition_logic_previous = sidebar_condition.logic_previous;

			// Check type
			if(sidebar_condition_type === 'sidebar_meta_key') {

				// Get meta key obj
				var sidebar_condition_meta_key_obj = $('[data-meta-key="' + this.esc_selector(sidebar_condition_meta_key) + '"]', obj_sidebar_outer);

				// Check meta key exists
				if(!sidebar_condition_meta_key_obj.length) { continue; }
			}

			// Get meta key type
			var meta_key_config = $.WS_Form.meta_keys[sidebar_condition_meta_key];
			var sidebar_condition_meta_key_type = meta_key_config['type'];

			// Get meta key to show	
			var sidebar_condition_show = sidebar_condition.show;
			var sidebar_condition_show_obj = $('[data-meta-key="' + this.esc_selector(sidebar_condition_show) + '"]', obj_sidebar_outer);
			if(!sidebar_condition_show_obj.length) { continue; }

			// Get current result
			var result = true;
			var meta_value = '';

			// Process condition
			switch(sidebar_condition_meta_key_type) {

				case 'checkbox' :

					switch(sidebar_condition_type) {

						case 'object_meta_value_form' :

							meta_value = this.get_object_meta_value(this.form, sidebar_condition_meta_key, false);
							break;

						case 'sidebar_meta_key' :

							meta_value = sidebar_condition_meta_key_obj.is(':checked');
							break;
					}


					switch(sidebar_condition_logic) {

						case '==' :

							result = meta_value;
							break;	

						case '!=' :

							result = !meta_value;
							break;	
					}

					break;

				default :

					switch(sidebar_condition_type) {

						case 'object_meta_value_form' :

							meta_value = this.get_object_meta_value(this.form, sidebar_condition_meta_key, false);
							break;

						case 'sidebar_meta_key' :

							meta_value = sidebar_condition_meta_key_obj.val();
							break;
					}

					if(meta_value === null) { meta_value = ''; }

					// Check for options_default
					if(meta_value === 'default') {

						var meta_key_config = $.WS_Form.meta_keys[sidebar_condition_meta_key];
						if(typeof(meta_key_config['options_default']) !== 'undefined') {

							meta_value = this.get_object_meta_value(this.form, meta_key_config['options_default'], '');
						}
					}

					switch(sidebar_condition_logic) {

						case '==' :

							result = (meta_value == sidebar_condition_meta_value);
							break;	

						case '!=' :

							result = (meta_value != sidebar_condition_meta_value);
							break;	

						case 'contains' :

							result = (meta_value.indexOf(sidebar_condition_meta_value) !== -1);
							break;	

						case 'contains_not' :

							result = (meta_value.indexOf(sidebar_condition_meta_value) === -1);
							break;	
					}
			}

			// Assign to result
			if(typeof(condition_result_array[sidebar_condition_show]) === 'undefined') {

				condition_result_array[sidebar_condition_show] = result;

			} else {

				switch(sidebar_condition_logic_previous) {

					case '||' :

						condition_result_array[sidebar_condition_show] = (condition_result_array[sidebar_condition_show] || result);
						break;

					default :

						condition_result_array[sidebar_condition_show] = (condition_result_array[sidebar_condition_show] && result);
						break;
				}
			}
		}

		// Process results
		for(sidebar_condition_show in condition_result_array) {

			var condition_result = condition_result_array[sidebar_condition_show];

			// Show / hide
			var show_obj = $('[data-meta-key="' + this.esc_selector(sidebar_condition_show) + '"]', obj_sidebar_outer).closest('.wsf-field-wrapper');

			// Show / hide object
			if(condition_result) {

				show_obj.show().removeClass('wsf-field-hidden');

			} else {

				show_obj.hide().addClass('wsf-field-hidden');
			}

			// Sidebar fieldset toggles
			this.sidebar_fieldset_toggle(show_obj);
		}

		if(!initial_run) {

			// Check if this is an element within a datagrid
			var object_data_saved = false;

			var data_grid_obj = obj.closest('.wsf-data-grid');
			if(data_grid_obj.length) {

				var data_grid_meta_key = data_grid_obj.attr('data-meta-key');

				switch(data_grid_meta_key) {

					case 'conditional' :

						$.WS_Form.this.conditional_save();
						object_data_saved = true;
						break;

					case 'action' :

						$.WS_Form.this.action_save();
						object_data_saved = true;
						break;
				}
			}

			if(!object_data_saved) {

				// Get object data
				var object_identifier = obj.closest('[data-object]');
				var object = object_identifier.attr('data-object');
				var object_id = object_identifier.attr('data-id');
				var object_data = $.WS_Form.this.get_object_data(object, object_id, true);

				// Save sidebar
				for(var key in $.WS_Form.this.object_meta_cache) {

					if(!$.WS_Form.this.object_meta_cache.hasOwnProperty(key)) { continue; }

					// Get meta_key
					var meta_key = $.WS_Form.this.object_meta_cache[key]['meta_key'];

					// Update object data
					$.WS_Form.this.object_data_update_by_meta_key(object, object_data, meta_key);
				}
			}

			// Init
			var inits = ['options-action', 'repeater', 'text-editor', 'html-editor'];
			$.WS_Form.this.sidebar_inits(inits, obj_sidebar_outer);
		}
	}

	// Sidebar - Show/hide fieldsets
	$.WS_Form.prototype.sidebar_fieldset_toggle = function(obj) {

		var fieldset_obj = obj.closest('.wsf-fieldset');
		var fields_count = $('.wsf-field-wrapper', fieldset_obj).length;
		var fields_hidden = $('.wsf-field-hidden', fieldset_obj).length;

		if(fields_count == fields_hidden) {

			fieldset_obj.hide();

		} else {

			fieldset_obj.show();
		}
	}

	// Sidebar - Update placeholders
	$.WS_Form.prototype.sidebar_placeholders_init = function(obj) {

		$('[data-placeholder]', obj).each(function() {

			// Get placeholder mask
			var mask_placeholder = $(this).attr('data-placeholder');

			// Process invalid_feedback_mask
			if(mask_placeholder == '#invalid_feedback_mask') {

				// Set mask placeholder from form setting
				mask_placeholder = $.WS_Form.this.get_form_invalid_feedback_mask();
			}

			// Get label
			var label_obj = $('[name="label"]', obj);

			// Get label value
			var label = label_obj.length ? label_obj.val() : '';

			// Parse mask_placeholder
			var placeholder = $.WS_Form.this.get_invalid_feedback_mask_parsed(mask_placeholder, label);

			// Set placeholder
			$(this).attr('placeholder', $.WS_Form.this.esc_attr(placeholder));
		});
	}

	// Footer - Breakpoint slider
	$.WS_Form.prototype.breakpoints = function() {

		// Build breakpoints
		var obj_breakpoints = $('#wsf-breakpoints');

		// Get current framework breakpoints
		var framework_breakpoints = this.framework.breakpoints;

		// Get icons for use with breakpoint key (0, 25, 50, 75, 100, 125, 150)
		var framework_icons = $.WS_Form.frameworks.icons;

		// Get current breakpoint
		var breakpoint = this.get_object_meta_value(this.form, 'breakpoint');

		// Reset global breakpoints array
		$.WS_Form.breakpoints = [];

		// Ensure breakpoint exists in current framework, otherwise set it to closest match to current
		var breakpoint_found = false;
		var breakpoint_diff_min = 0;
		var breakpoint_closest = 0;
		var breakpoint_slider_max = 0;
		var breakpoint_slider_value = 1;

		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			breakpoint_slider_max++;

			if((breakpoint_key == breakpoint) && !breakpoint_found) {

				breakpoint_found = true;
				breakpoint_slider_value = breakpoint_slider_max;

			} else {

				var breakpoint_diff = Math.abs(breakpoint_key - breakpoint);
				if((breakpoint_diff_min == 0) || (breakpoint_diff < breakpoint_diff_min)) {

					breakpoint_diff_min = breakpoint_diff;
					breakpoint_closest = breakpoint_key;
				}
			}

			// Store breakpoint
			$.WS_Form.breakpoints.push(breakpoint_key);
		}

		if(!breakpoint_found) {

			// Could not find breakpoint, so we need to set breakpoint to closet found
			breakpoint = breakpoint_closest;

			// Set object meta
			this.set_object_meta_value(this.form, 'breakpoint', breakpoint);

			// Push form
			$.WS_Form.this.form_put(false, false, true, false);
		}

		// Set on form
		var form_breakpoint_class = framework_breakpoints[breakpoint]['id'];
		$('#' + this.form_obj_id).attr('data-breakpoint', form_breakpoint_class);

		// Inject breakpoint framework
		obj_breakpoints.html('<div id="wsf-slider"></div>');

		// Breakpoint buttons
		obj_breakpoints.append('<ul class="wsf-breakpoint-actions"><li><button class="wsf-button wsf-button-full wsf-button-small" data-action="wsf-reset">' + this.svg('undo') + ' ' + this.language('breakpoint_reset') + '</button></li></ul>')

		// Now work on the UL
		var obj_slider = $('#wsf-slider');

		// Render each breakpoint
		var breakpoint_index = 0;

		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			var breakpoint = framework_breakpoints[breakpoint_key];

			// Get breakpoint icon (SVG from config)
			if(typeof(framework_icons[breakpoint_key]) === 'undefined') {

				var breakpoint_icon = '';

			} else {

				var breakpoint_icon = framework_icons[breakpoint_key];
			}

			// Build help text
			var breakpoint_help_text_array = [];
			if(typeof(breakpoint.name) !== 'undefined') { breakpoint_help_text_array.push(breakpoint.name); }
			if(typeof(breakpoint.min_width) !== 'undefined') { breakpoint_help_text_array.push('>= ' + breakpoint.min_width + 'px'); } else { breakpoint_help_text_array.push('> 0 px'); }
			if(typeof(breakpoint.max_width) !== 'undefined') { breakpoint_help_text_array.push('<= ' + breakpoint.max_width + 'px'); }
			var breakpoint_help_text = breakpoint_help_text_array.join("\n");

			// Add breakpoint to ul
			var tooltip_attribute = 'top-' + ((breakpoint_index === 0) ? 'left' : 'center');
			obj_slider.append('<label' + this.tooltip(breakpoint_help_text, tooltip_attribute) + ' style="' + (ws_form_settings.rtl ? 'right' : 'left') + ':' + (breakpoint_index / (breakpoint_slider_max - 1) * 100) + '%;">' + breakpoint_icon + '</label>');

			breakpoint_index++;
		}

		// Breakpoints - Slider
		$('#wsf-slider').slider({

			range: 'max',
			min: 1,
			max: breakpoint_slider_max,
			value: ws_form_settings.rtl ? (breakpoint_slider_max - (breakpoint_slider_value - 1)) : breakpoint_slider_value,

			start: function() {

				if($.WS_Form.settings_plugin.helper_columns != 'off') { $('.wsf-group:visible').addClass('wsf-column-helper'); }

				// Set dragging
				$.WS_Form.this.dragging = true;
			},

			stop: function() {

				if($.WS_Form.settings_plugin.helper_columns != 'on') { $('.wsf-group:visible').removeClass('wsf-column-helper'); }

				// Push breakpoint to API (Disable history)
				$.WS_Form.this.form_put(false, false, true, false);

				// Set dragging
				$.WS_Form.this.dragging = false;
			},

			slide: function(e, ui) {

				// Read breakpoint
				var key = $.WS_Form.breakpoints[ws_form_settings.rtl ? (breakpoint_slider_max - ui.value) : (ui.value - 1)];

				// Set breakpoint
				$.WS_Form.this.breakpoint_set(key);
			}
		});

		// Button - Reset
		$('#wsf-breakpoints [data-action="wsf-reset"]').on('click', function() {

			var buttons = [

				{label:$.WS_Form.this.language('cancel'), action:'wsf-cancel'},
				{label:$.WS_Form.this.language('reset'), action:'wsf-confirm', class:'wsf-button-danger'}
			];

			$.WS_Form.this.popover($.WS_Form.this.language('confirm_breakpoint_reset'), buttons, $(this), function() {

				$.WS_Form.this.breakpoint_reset();
			});
		});
	}

	// Breakpoints - Optimize
	$.WS_Form.prototype.breakpoint_optimize = function(object) {

		// Run through each breakpoint and tidy up data (i.e. if breakpoint size matches previous breakpoint size, delete it)
		var framework = $.WS_Form.frameworks.types[$.WS_Form.settings_plugin.framework];

		// Get current framework breakpoints
		var framework_breakpoints = framework.breakpoints;

		// Get current framework column count
		var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

		// Go through breakpoints
		var column_size_value_old = 0;
		var breakpoint_index = 0;
		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			var breakpoint = framework_breakpoints[breakpoint_key];

			// Get breakpoint default column size
			if(breakpoint_index == 0) {

				var column_size_value_old = (typeof(breakpoint.column_size_default) !== 'undefined') ? (breakpoint.column_size_default == 'column_count' ? framework_column_count : breakpoint.column_size_default) : column_size_value_old;
				var offset_value_old;
			}

			// Get meta keys
			var column_size_value_key = 'breakpoint_size_' + breakpoint_key;
			var offset_value_key = 'breakpoint_offset_' + breakpoint_key;

			// Column sizes
			var column_size_value = this.get_object_meta_value(object, column_size_value_key, '', false);
			if(column_size_value != '') {

				column_size_value = parseInt(column_size_value, 10);

				if(column_size_value == column_size_value_old) {

					// Found a breakpoint column size that matches the previous value, so this meta should be deleted
					object.meta[column_size_value_key] = '';
				}

				// Remember this value for next cycle
				column_size_value_old = column_size_value;
			}

			// Offset
			var offset_value = this.get_object_meta_value(object, offset_value_key, '', false);
			if(offset_value != '') {

				offset_value = parseInt(offset_value, 10);

				if(offset_value == offset_value_old) {

					// Found a breakpoint offset that matches the previous value, so this meta should be deleted
					object.meta[offset_value_key] = '';
				}

				// Remember this value for next cycle
				offset_value_old = offset_value;
			}

			breakpoint_index++;
		}
	}

	// Footer - Breakpoint slider - Reset
	$.WS_Form.prototype.breakpoint_reset = function() {

		var framework = $.WS_Form.settings_plugin.framework;

		// Get current group
		var group_obj = $($('.wsf-group-tab.ui-tabs-active a').attr('href'));

		// Sections
		for(var object_id in this.section_data_cache) {

			if(!this.section_data_cache.hasOwnProperty(object_id)) { continue; }

			var object = this.section_data_cache[object_id];

			// Optimize
			$.WS_Form.this.breakpoint_reset_process(object);
		};

		// Fields
		for(var object_id in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(object_id)) { continue; }

			var object = this.field_data_cache[object_id];

			// Optimize
			$.WS_Form.this.breakpoint_reset_process(object);
		};

		// Form build
		$.WS_Form.this.form_build();

		// Loader on
		$.WS_Form.this.loader_on();

		// Call AJAX request
		$.WS_Form.this.api_call('form/' + $.WS_Form.this.form_id + '/full/put/', 'POST', {'form': $.WS_Form.this.form, 'history_method': 'put_reset'}, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Footer - Breakpoint slider - Set
	$.WS_Form.prototype.breakpoint_set = function(key) {

		// Get framework
		var framework = $.WS_Form.settings_plugin.framework;

		// Get breakpoint ID
		var form_data_breakpoint = $.WS_Form.frameworks.types[framework]['breakpoints'][key]['id'];

		// Set new breakpoint
		$.WS_Form.this.set_object_meta_value($.WS_Form.this.form, 'breakpoint', key);

		// Set breakpoint ID in form data-breakpoint attribute
		$('#' + $.WS_Form.this.form_obj_id).attr('data-breakpoint', form_data_breakpoint);
	};

	// Footer - Breakpoint slider - Button rendering
	$.WS_Form.prototype.breakpoint_buttons = function() {

		var can_reset = false;

		// Get current group
		var group_obj = $($('.wsf-group-tab.ui-tabs-active a').attr('href'));

		// Sections
		for(var object_id in this.section_data_cache) {

			if(!this.section_data_cache.hasOwnProperty(object_id)) { continue; }

			var object = this.section_data_cache[object_id];

			can_reset = can_reset || $.WS_Form.this.breakpoint_can_reset(object, true);
		};

		// Fields
		for(var object_id in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(object_id)) { continue; }

			var object = this.field_data_cache[object_id];

			can_reset = can_reset || $.WS_Form.this.breakpoint_can_reset(object, true);
		};

		// Set buttons
		$('#wsf-breakpoints [data-action="wsf-reset"]').attr('disabled', !can_reset);
	}

	// Sidebar - Breakpoint sizes
	$.WS_Form.prototype.sidebar_breakpoint_sizes = function(obj) {

		// Init breakpoint sizes
		$('.wsf-breakpoint-sizes:not(.wsf-breakpoint-sizes-initialized)', obj).each(function(i, e) {

			$.WS_Form.this.sidebar_breakpoint_sizes_init($(this), e);
		});
	}

	// Sidebar - Breakpoint sizes - Init
	$.WS_Form.prototype.sidebar_breakpoint_sizes_init = function(obj, element) {

		var ws_this = this;

		element.render = function() {

			// Get object data
			var object = obj.attr('data-object');
			var object_id = obj.attr('data-id');
			var meta_key = obj.attr('data-meta-key');

			switch(object) {

				case 'section' :

					var object_obj = $('.wsf-section[data-id="' + ws_this.esc_selector(object_id) + '"]');
					break;

				case 'field' :

					var object_obj = $('.wsf-field-wrapper[data-id="' + ws_this.esc_selector(object_id) + '"]');
					break;
			}

			// Get object data from scratch
			var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
			if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

			// Get current data grid data
			var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, false);
			if(meta_value === false) { $.WS_Form.this.error('error_object_meta_value'); }

			// Render HTML
			obj.html($.WS_Form.this.sidebar_breakpoint_sizes_html(object_obj, meta_key, object_data));

			// Column size selects
			$('select[data-action="wsf-column"]', obj).on('change', function() {

				// Get breakpoint
				var breakpoint = $(this).attr('data-id');

				// Get column size
				var column_size = $(this).val();

				// Get meta key
				var meta_key = 'breakpoint_size_' + breakpoint;

				if(!object_obj.length) { $.WS_Form.this.error('error_object'); } else {

					// Remove old classes
					$.WS_Form.this.column_classes_render(object_obj, object_data, false);

					// Update framework size meta
					$.WS_Form.this.set_object_meta_value(object_data, meta_key, column_size);

					// Add new classes
					$.WS_Form.this.column_classes_render(object_obj, object_data);
				}

				// Render
				element.render();
			});

			// Offset selects
			$('select[data-action="wsf-offset"]', obj).on('change', function() {

				// Get breakpoint
				var breakpoint = $(this).attr('data-id');

				// Get offset
				var offset = $(this).val();

				// Get meta key
				var meta_key = 'breakpoint_offset_' + breakpoint;

				if(!object_obj.length) { $.WS_Form.this.error('error_object'); } else {

					// Remove old classes
					$.WS_Form.this.column_classes_render(object_obj, object_data, false);

					// Update framework size meta
					$.WS_Form.this.set_object_meta_value(object_data, meta_key, offset);

					// Add new classes
					$.WS_Form.this.column_classes_render(object_obj, object_data);
				}

				// Render
				element.render();
			});

			// Reset
			$('[data-action="wsf-reset"]', obj).on('click', function() {

				var buttons = [

					{label:$.WS_Form.this.language('cancel'), action:'wsf-cancel'},
					{label:$.WS_Form.this.language('reset'), action:'wsf-confirm', class:'wsf-button-danger'}
				];

				$.WS_Form.this.popover($.WS_Form.this.language('confirm_breakpoint_reset'), buttons, $(this), function() {

					// Remove old classes
					$.WS_Form.this.column_classes_render(object_obj, object_data, false);

					// Reset
					$.WS_Form.this.breakpoint_reset_process(object_data);

					// Add new classes
					$.WS_Form.this.column_classes_render(object_obj, object_data);

					// Render
					element.render();
				});
			});

			// Set optimized button
			var can_reset = $.WS_Form.this.breakpoint_can_reset(object_data, true);

			// Render buttons
			$('[data-action="wsf-reset"]', obj).attr('disabled', !can_reset);

			// Mark as initialized
			obj.addClass('wsf-breakpoint-sizes-initialized');
		}

		// Render
		element.render();
	}

	// Sidebar - Breakpoint sizes - HTML
	$.WS_Form.prototype.sidebar_breakpoint_sizes_html = function(object_obj, meta_key, object_data) {

		var return_html = '';

		// Get selected framework
		var framework_id = $.WS_Form.settings_plugin.framework;

		// Get framework from config
		var framework = $.WS_Form.frameworks.types[framework_id];

		// Get frame work column count
		var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

		// Get current framework breakpoints
		var framework_breakpoints = framework.breakpoints;

		// Get icons for use with breakpoint key (0, 25, 50, 75, 100, 125, 150)
		var framework_icons = $.WS_Form.frameworks.icons;

		var column_size_default = false;
		var offset_default = false;
		var breakpoint_index = 0;
		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			var breakpoint = framework_breakpoints[breakpoint_key];

			// Get breakpoint name
			var breakpoint_name = breakpoint.name;

			if(breakpoint_index == 0) {

				// Get breakpoint default column size
				var column_size_default = (typeof(breakpoint.column_size_default) !== 'undefined') ? (breakpoint.column_size_default == 'column_count' ? framework_column_count : breakpoint.column_size_default) : column_size_default;

				// Get breakpoint default offset size
				var offset_default = 0;
			}

			// Get column size
			var column_size_value_key = 'breakpoint_size_' + breakpoint_key;
			var column_size_value = this.get_object_meta_value(object_data, column_size_value_key, '', false);
			if(column_size_value != '') { column_size_value = parseInt(column_size_value, 10); var column_size_value_actual = column_size_value; } else { var column_size_value_actual = column_size_default; }

			// Get offset
			var offset_value_key = 'breakpoint_offset_' + breakpoint_key;
			var offset_value = this.get_object_meta_value(object_data, offset_value_key, '', false);
			if(offset_value != '') { offset_value = parseInt(offset_value, 10); var offset_value_actual = offset_value; } else { var offset_value_actual = offset_default; }

			// Work out max width
			var column_size_max = framework_column_count - offset_value;
			var offset_max = framework_column_count - column_size_value_actual;

			return_html += '<div>';

			// Get breakpoint icon (SVG from config)
			if(typeof(framework_icons[breakpoint_key]) === 'undefined') {

				var breakpoint_icon = '';

			} else {

				var breakpoint_icon = framework_icons[breakpoint_key];
			}

			return_html += '<label class="wsf-label">' + breakpoint_icon + this.esc_html(breakpoint_name) + '</label>';

			// Width / Offset group
			return_html += '<div class="wsf-breakpoint-width-offset">';

			// Columns
			return_html += '<div>';
			return_html += '<label class="wsf-label" for="' + this.esc_attr(column_size_value_key) + '">' + this.language('breakpoint_offset_column_width') + '</label>';

			return_html += '<select class="wsf-field wsf-column-size-select" id="' + this.esc_attr(column_size_value_key) + '" data-id="' + this.esc_attr(breakpoint_key) + '" data-action="wsf-column">';
			return_html += '<option value=""' + ((column_size_value == '') ? ' selected' : '') + '>';

			var column_size_default_description = '(' + this.language(((column_size_default == 1) ? 'breakpoint_option_column_default_singular' : 'breakpoint_option_column_default_plural'), column_size_default) + ')';

			if(ws_form_settings.rtl && (column_size_default !== false)) { return_html += column_size_default_description + ' '; }

			return_html += (breakpoint_index == 0 ? this.language('breakpoint_option_default') : this.language('breakpoint_option_inherit'));

			if(!ws_form_settings.rtl && (column_size_default !== false)) { return_html += ' ' + column_size_default_description; }

			return_html += '</option>';

			for(var i=1; i<=column_size_max; i++) {

				return_html += '<option value="' + this.esc_attr(i) + '"' + ((column_size_value === i) ? ' selected' : '') + '>' + this.language(((i == 1) ? 'breakpoint_option_column_singular' : 'breakpoint_option_column_plural'), i) + '</option>';
			}

			return_html += '</select>';

			return_html += '</div>';

			// Offset
			return_html += '<div>';
			return_html += '<label class="wsf-label" for="' + this.esc_attr(offset_value_key) + '">' + this.language('breakpoint_offset_column_offset') + '</label>';

			return_html += '<select class="wsf-field wsf-offset-select" id="' + this.esc_attr(offset_value_key) + '" data-id="' + this.esc_attr(breakpoint_key) + '" data-action="wsf-offset">';
			return_html += '<option value=""' + ((offset_value === '') ? ' selected' : '') + '>';

			var offset_default_description = '(' + this.language(((offset_default == 1) ? 'breakpoint_option_offset_default_singular' : 'breakpoint_option_offset_default_plural'), offset_default) + ')';

			if(ws_form_settings.rtl) { return_html += offset_default_description + ' '; }

			return_html += (breakpoint_index == 0 ? this.language('breakpoint_option_default') : this.language('breakpoint_option_inherit'));

			if(!ws_form_settings.rtl) { return_html += ' ' + offset_default_description; }

			return_html += '</option>';

			for(var i=0; i<=offset_max; i++) {

				return_html += '<option value="' + this.esc_attr(i) + '"' + ((offset_value === i) ? ' selected' : '') + '>' + this.language(((i == 1) ? 'breakpoint_option_offset_singular' : 'breakpoint_option_offset_plural'), i) + '</option>';
			}

			return_html += '</select>';

			return_html += '</div>';

			// /Column / Offset group
			return_html += '</div>';

			return_html += '</div>';

			// Remember column size
			if(column_size_value !== '') { column_size_default = column_size_value; }

			// Remember offset
			if(offset_value !== '') { offset_default = offset_value; }

			breakpoint_index++;
		}

		// Buttons
		return_html += '<ul class="wsf-list-inline">';
		return_html += '<li><button class="wsf-button wsf-button-small" data-action="wsf-reset">' + this.svg('undo') + ' ' + this.language('breakpoint_reset') + '</button></li>';
		return_html += '</ul>';

		return return_html;
	}

	// Breakpoints - Can reset
	$.WS_Form.prototype.breakpoint_can_reset = function(object) {

		var can_reset = false;

		// Run through each breakpoint and tidy up data (i.e. if breakpoint size matches previous breakpoint size, delete it)
		var framework = $.WS_Form.frameworks.types[$.WS_Form.settings_plugin.framework];

		// Get current framework breakpoints
		var framework_breakpoints = framework.breakpoints;

		// Get current framework column count
		var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

		// Go through breakpoints
		var column_size_value_old = 0;
		var breakpoint_index = 0;
		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			var breakpoint = framework_breakpoints[breakpoint_key];

			// Get breakpoint default column size
			if(breakpoint_index == 0) {

				var column_size_value_old = (typeof(breakpoint.column_size_default) !== 'undefined') ? (breakpoint.column_size_default == 'column_count' ? framework_column_count : breakpoint.column_size_default) : column_size_value_old;
				var offset_value_old;
			}

			// Get meta keys
			var column_size_value_key = 'breakpoint_size_' + breakpoint_key;
			var offset_value_key = 'breakpoint_offset_' + breakpoint_key;

			// Column sizes
			var column_size_value = this.get_object_meta_value(object, column_size_value_key, '', false);
			if(column_size_value != '') {

				column_size_value = parseInt(column_size_value, 10);

				if(column_size_value != framework_column_count) {

					can_reset = true;
				}

				// Remember this value for next cycle
				column_size_value_old = column_size_value;
			}

			// Offset
			var offset_value = this.get_object_meta_value(object, offset_value_key, '', false);
			if(offset_value != '') {

				offset_value = parseInt(offset_value, 10);

				if(offset_value != 0) {

					can_reset = true;
				}

				// Remember this value for next cycle
				offset_value_old = offset_value;
			}

			breakpoint_index++;
		}

		return can_reset;
	}

	// Breakpoints - Reset
	$.WS_Form.prototype.breakpoint_reset_process = function(object) {

		// Run through each breakpoint and tidy up data (i.e. if breakpoint size matches previous breakpoint size, delete it)
		var framework = $.WS_Form.frameworks.types[$.WS_Form.settings_plugin.framework];

		// Get current framework breakpoints
		var framework_breakpoints = framework.breakpoints;

		// Go through breakpoints
		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			// Delete object metas
			object.meta['breakpoint_size_' + breakpoint_key] = '';
			object.meta['breakpoint_offset_' + breakpoint_key] = '';
		}
	}


	// Sidebar - Framework sizes
	$.WS_Form.prototype.sidebar_orientation_breakpoint_sizes = function(obj) {

		// Init data grids
		$('.wsf-orientation-breakpoint-sizes:not(.wsf-orientation-breakpoint-sizes-initialized)', obj).each(function(i, e) {

			$.WS_Form.this.sidebar_orientation_breakpoint_sizes_init($(this), e);
		});
	}

	// Sidebar - Framework sizes - Init
	$.WS_Form.prototype.sidebar_orientation_breakpoint_sizes_init = function(obj, element) {

		element.render = function() {

			// Get object data
			var object = obj.attr('data-object');
			var object_id = obj.attr('data-id');
			var meta_key = obj.attr('data-meta-key');
			var object_obj = $('.wsf-field-wrapper[data-id="' + $.WS_Form.this.esc_selector(object_id) + '"]');

			// Get object data from scratch
			var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
			if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

			// Get current data grid data
			var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, false);
			if(meta_value === false) { $.WS_Form.this.error('error_object_meta_value'); }

			// Render HTML
			obj.html($.WS_Form.this.sidebar_orientation_breakpoint_sizes_html(object_obj, meta_key, object_data));

			// Column size selects
			$('select[data-action="wsf-column"]', obj).on('change', function() {

				// Get breakpoint
				var breakpoint = $(this).attr('data-id');

				// Get column size
				var column_size = $(this).val();

				// Get meta key
				var meta_key = 'orientation_breakpoint_size_' + breakpoint;

				if(!object_obj.length) { $.WS_Form.this.error('error_object'); } else {

					// Update framework size meta
					$.WS_Form.this.set_object_meta_value(object_data, meta_key, column_size);
				}

				// Render
				element.render();
			});

			// Reset
			$('[data-action="wsf-reset"]', obj).on('click', function() {

				var buttons = [

					{label:$.WS_Form.this.language('cancel'), action:'wsf-cancel'},
					{label:$.WS_Form.this.language('reset'), action:'wsf-confirm', class:'wsf-button-danger'}
				];

				$.WS_Form.this.popover($.WS_Form.this.language('confirm_orientation_breakpoint_reset'), buttons, $(this), function() {

					// Reset
					$.WS_Form.this.orientation_breakpoint_reset_process(object_data);

					// Render
					element.render();
				});
			});

			// Set optimized button
			var can_reset = $.WS_Form.this.orientation_breakpoint_can_reset(object_data, true);

			// Render buttons
			$('[data-action="wsf-reset"]', obj).attr('disabled', !can_reset);

			// Mark as initialized
			obj.addClass('wsf-orientation-breakpoint-sizes-initialized');
		}

		// Render
		element.render();
	}

	// Sidebar - Framework sizes - HTML
	$.WS_Form.prototype.sidebar_orientation_breakpoint_sizes_html = function(object_obj, meta_key, object_data) {

		var return_html = '';

		// Get selected framework
		var framework_id = $.WS_Form.settings_plugin.framework;

		// Get framework from config
		var framework = $.WS_Form.frameworks.types[framework_id];

		// Get frame work column count
		var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

		// Get current framework breakpoints
		var framework_breakpoints = framework.breakpoints;

		// Get icons for use with breakpoint key (0, 25, 50, 75, 100, 125, 150)
		var framework_icons = $.WS_Form.frameworks.icons;

		var column_size_default = false;
		var breakpoint_index = 0;
		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			var breakpoint = framework_breakpoints[breakpoint_key];

			// Get breakpoint name
			var breakpoint_name = breakpoint.name;

			if(breakpoint_index == 0) {

				// Get breakpoint default column size
				var column_size_default = (typeof(breakpoint.column_size_default) !== 'undefined') ? (breakpoint.column_size_default == 'column_count' ? framework_column_count : breakpoint.column_size_default) : column_size_default;
			}

			// Get column size
			var column_size_value_key = 'orientation_breakpoint_size_' + breakpoint_key;
			var column_size_value = this.get_object_meta_value(object_data, column_size_value_key, '', false);
			if(column_size_value != '') { column_size_value = parseInt(column_size_value, 10); var column_size_value_actual = column_size_value; } else { var column_size_value_actual = column_size_default; }

			return_html += '<div>';

			// Get breakpoint icon (SVG from config)
			if(typeof(framework_icons[breakpoint_key]) === 'undefined') {

				var breakpoint_icon = '';

			} else {

				var breakpoint_icon = framework_icons[breakpoint_key];
			}

			return_html += '<label class="wsf-label">' + breakpoint_icon + this.language('orientation_breakpoint_label_width', this.esc_html(breakpoint_name)) + '</label>';

			return_html += '<select class="wsf-field wsf-column-size-select" id="' + this.esc_attr(column_size_value_key) + '" data-id="' + this.esc_attr(breakpoint_key) + '" data-action="wsf-column">';
			return_html += '<option value=""' + ((column_size_value == '') ? ' selected' : '') + '>' + (breakpoint_index == 0 ? this.language('orientation_breakpoint_option_default') : this.language('orientation_breakpoint_option_inherit'));

			if(column_size_default !== false) {

				return_html += ' (' + this.orientation_column_width_description(column_size_default, framework_column_count) + ')'; 
			}

			return_html += '</option>';

			for(var i=1; i<=framework_column_count; i++) {

				if(
					(i > (framework_column_count / 2)) &&
					(i < framework_column_count)

				) { continue; }

				var orientation_column_width_description = this.orientation_column_width_description(i, framework_column_count);
				if(orientation_column_width_description === false) { continue; }

				return_html += '<option value="' + this.esc_attr(i) + '"' + ((column_size_value === i) ? ' selected' : '') + '>' + orientation_column_width_description + '</option>';
			}

			return_html += '</select>';

			return_html += '</div>';

			// Remember column size
			if(column_size_value !== '') { column_size_default = column_size_value; }

			breakpoint_index++;
		}

		// Buttons
		return_html += '<ul class="wsf-list-inline">';
		return_html += '<li><button class="wsf-button wsf-button-small" data-action="wsf-reset">' + this.svg('undo') + ' ' + this.language('breakpoint_reset') + '</button></li>';
		return_html += '</ul>';

		return return_html;
	}

	// Breakpoints - Optimize
	$.WS_Form.prototype.orientation_column_width_description = function(columns, columns_max) {

		// Check modulus
		if((columns_max % columns) !== 0) { return false; }

		// Base description
		var column_width_description = this.language(((columns == 1) ? 'orientation_breakpoint_option_column_singular' : 'orientation_breakpoint_option_column_plural'), columns);

		// Extra description
		switch(columns / columns_max) {

			case (1) : column_width_description += this.language('orientation_breakpoint_width_full'); break;

			case (1/2) : column_width_description += this.language('orientation_breakpoint_width', '&frac12;', false); break;

			case (1/3) : column_width_description += this.language('orientation_breakpoint_width', '&frac13;', false); break;
			case (2/3) : column_width_description += this.language('orientation_breakpoint_width', '&frac23;', false); break;

			case (1/4) : column_width_description += this.language('orientation_breakpoint_width', '&frac14;', false); break;
			case (3/4) : column_width_description += this.language('orientation_breakpoint_width', '&frac34;', false); break;

			case (1/5) : column_width_description += this.language('orientation_breakpoint_width', '&frac15;', false); break;
			case (2/5) : column_width_description += this.language('orientation_breakpoint_width', '&frac25;', false); break;
			case (3/5) : column_width_description += this.language('orientation_breakpoint_width', '&frac35;', false); break;
			case (4/5) : column_width_description += this.language('orientation_breakpoint_width', '&frac45;', false); break;

			case (1/6) : column_width_description += this.language('orientation_breakpoint_width', '&frac16;', false); break;
			case (5/6) : column_width_description += this.language('orientation_breakpoint_width', '&frac56;', false); break;
		}

		return column_width_description;
	}

	// Breakpoints - Optimize
	$.WS_Form.prototype.orientation_breakpoint_optimize = function(object) {

		// Run through each breakpoint and tidy up data (i.e. if breakpoint size matches previous breakpoint size, delete it)
		var framework = $.WS_Form.frameworks.types[$.WS_Form.settings_plugin.framework];

		// Get current framework breakpoints
		var framework_breakpoints = framework.breakpoints;

		// Get current framework column count
		var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

		// Go through breakpoints
		var column_size_value_old = 0;
		var breakpoint_index = 0;
		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			var breakpoint = framework_breakpoints[breakpoint_key];

			// Get breakpoint default column size
			if(breakpoint_index == 0) {

				var column_size_value_old = (typeof(breakpoint.column_size_default) !== 'undefined') ? (breakpoint.column_size_default == 'column_count' ? framework_column_count : breakpoint.column_size_default) : column_size_value_old;
			}

			// Get meta keys
			var column_size_value_key = 'orientation_breakpoint_size_' + breakpoint_key;

			// Column sizes
			var column_size_value = this.get_object_meta_value(object, column_size_value_key, '', false);
			if(column_size_value != '') {

				column_size_value = parseInt(column_size_value, 10);

				if(column_size_value == column_size_value_old) {

					// Found a breakpoint column size that matches the previous value, so this meta should be deleted
					object.meta[column_size_value_key] = '';
				}

				// Remember this value for next cycle
				column_size_value_old = column_size_value;
			}

			breakpoint_index++;
		}
	}

	// Breakpoints - Can reset
	$.WS_Form.prototype.orientation_breakpoint_can_reset = function(object) {

		var can_reset = false;

		// Run through each breakpoint and tidy up data (i.e. if breakpoint size matches previous breakpoint size, delete it)
		var framework = $.WS_Form.frameworks.types[$.WS_Form.settings_plugin.framework];

		// Get current framework breakpoints
		var framework_breakpoints = framework.breakpoints;

		// Get current framework column count
		var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

		// Go through breakpoints
		var column_size_value_old = 0;
		var breakpoint_index = 0;
		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			var breakpoint = framework_breakpoints[breakpoint_key];

			// Get breakpoint default column size
			if(breakpoint_index == 0) {

				var column_size_value_old = (typeof(breakpoint.column_size_default) !== 'undefined') ? (breakpoint.column_size_default == 'column_count' ? framework_column_count : breakpoint.column_size_default) : column_size_value_old;
			}

			// Get meta keys
			var column_size_value_key = 'orientation_breakpoint_size_' + breakpoint_key;

			// Column sizes
			var column_size_value = this.get_object_meta_value(object, column_size_value_key, '', false);
			if(column_size_value != '') {

				column_size_value = parseInt(column_size_value, 10);

				if(column_size_value != framework_column_count) {

					can_reset = true;
				}

				// Remember this value for next cycle
				column_size_value_old = column_size_value;
			}

			breakpoint_index++;
		}

		return can_reset;
	}

	// Breakpoints - Reset
	$.WS_Form.prototype.orientation_breakpoint_reset_process = function(object) {

		// Run through each breakpoint and tidy up data (i.e. if breakpoint size matches previous breakpoint size, delete it)
		var framework = $.WS_Form.frameworks.types[$.WS_Form.settings_plugin.framework];

		// Get current framework breakpoints
		var framework_breakpoints = framework.breakpoints;

		// Go through breakpoints
		for(var breakpoint_key in framework_breakpoints) {

			if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

			// Delete object metas
			object.meta['orientation_breakpoint_size_' + breakpoint_key] = '';
		}
	}

	// Sidebar - Select2
	$.WS_Form.prototype.sidebar_select2 = function(obj) {

		var ws_this = this;

		// Get language
		var locale = ws_form_settings.locale_user;
		var language = locale.substring(0, 2);

		$('select[data-wsf-select2]:not([data-select-ajax-method-search])', obj).each(function() {

			var config = {

				// Add clear icon
				allowClear: true,

				// Placeholder
				placeholder: (typeof($(this).attr('placeholder')) !== 'undefined') ? $(this).attr('placeholder') : '',

				// Language
				language: language,

				// CSS
				selectionCssClass: 'wsf-select2-selection',
				dropdownCssClass: 'wsf-select2-dropdown',

				// Dropdown parent
				dropdownParent: $(this).parent()
			};

			$(this).select2(config);

			// Open event
			$(this).on('select2:open', function (e) {

				$('.select2-search__field', $(this).parent()).get(0).focus();
			});
		});
	}

	// Sidebar - Select2 AJAX
	$.WS_Form.prototype.sidebar_select2_ajax = function(obj) {

		var ws_this = this;

		// Get field ID
		var field_id = obj.closest('.wsf-sidebar[data-id]').attr('data-id');

		// Get data meta keys
		var data_meta_keys = [];
		var data_select_ajax_ids = [];
		$('select[data-wsf-select2][data-select-ajax-method-search]', obj).each(function() {

			// Get meta_value
			var data_meta_key = $(this).attr('data-meta-key');
			if(typeof(data_meta_keys[data_meta_key]) === 'undefined') {

				data_meta_keys[data_meta_key] = $(this).attr('data-select-ajax-method-cache');
			}

			var data_select_ajax_id = $(this).attr('data-select-ajax-id');
			if(data_select_ajax_id) {

				data_select_ajax_ids = data_select_ajax_ids.concat(data_select_ajax_id.split(','));
			}
		});

		for(var data_meta_key in data_meta_keys) {

			if(!data_meta_keys.hasOwnProperty(data_meta_key)) { continue; }

			var select_ajax_method_cache = data_meta_keys[data_meta_key];

			// Build select_ajax_cache_id
			var select_ajax_cache_id = data_meta_key + '_' + field_id;

			// Store result to cache
			if(!select_ajax_method_cache) {

				ws_this.select_ajax_cache[select_ajax_cache_id] = [];

				// Process select AJAX without caching
				ws_this.sidebar_select2_ajax_process(obj, data_meta_key, select_ajax_cache_id);

			} else {

				if(typeof(ws_this.select_ajax_cache[select_ajax_cache_id]) === 'undefined') {

					var params = {

						form_id: 	ws_this.form_id,
						meta_key: 	data_meta_key,
						ids: 		data_select_ajax_ids
					};

					// Call AJAX request
					$.WS_Form.this.api_call('select2/' + select_ajax_method_cache, 'POST', params, function(response) {

						ws_this.loader_off();

						// Store result to cache
						ws_this.select_ajax_cache[select_ajax_cache_id] = response;

						// Process select AJAX
						ws_this.sidebar_select2_ajax_process(obj, data_meta_key, select_ajax_cache_id);
					});

				} else {

					// Process select AJAX without caching
					ws_this.sidebar_select2_ajax_process(obj, data_meta_key, select_ajax_cache_id);
				}
			}
		}
	}

	// Sidebar - Select2 AJAX
	$.WS_Form.prototype.sidebar_select2_ajax_process = function(obj, data_meta_key, select_ajax_cache_id) {

		var ws_this = this;

		// Get language
		var locale = ws_form_settings.locale_user;
		var language = locale.substring(0, 2);

		// Init select2 AJAX
		$('select[data-wsf-select2][data-meta-key="' + this.esc_selector(data_meta_key) + '"]', obj).each(function() {

			var select_ajax_method_search = $(this).attr('data-select-ajax-method-search');
			var select_ajax_placeholder = $(this).attr('data-select-ajax-placeholder');

			var config = {

				ajax: {

					// AJAX URL
					url: ws_form_settings.url_ajax + 'select2/' + select_ajax_method_search,

					// Data type of JSON
					dataType: 'json',

					// Modify request
					data: function(params) {

						// Add WP REST API Authentication
						params._wpnonce = ws_form_settings.x_wp_nonce;
						return params;
					},

					// Add call delay
					delay: 250,

					// Enable caching
					cache: true
				},

				// Minimum input length
				minimumInputLength: 1,

				// Add clear icon
				allowClear: true,

				// Placeholder
				placeholder: select_ajax_placeholder,

				// Language
				language: language,

				// CSS
				selectionCssClass: 'wsf-select2-selection',
				dropdownCssClass: 'wsf-select2-dropdown',

				// Dropdown parent
				dropdownParent: $(this).parent()
			};

			// Get default value from cache
			var select_ajax_id = $(this).attr('data-select-ajax-id');
			var select_ajax_cache = ws_this.select_ajax_cache[select_ajax_cache_id];
			if((typeof(select_ajax_id) !== 'undefined') && (select_ajax_cache !== false)) {

				config.data = [];

				var select_ajax_id_array = select_ajax_id.split(',');

				for(var select_ajax_id_array_index in select_ajax_id_array) {

					if(!select_ajax_id_array.hasOwnProperty(select_ajax_id_array_index)) { continue; }

					var select_ajax_id = select_ajax_id_array[select_ajax_id_array_index];

					var option_text = (typeof(select_ajax_cache[select_ajax_id]) !== 'undefined') ? select_ajax_cache[select_ajax_id] : false;
					if(option_text !== false) {

						config.data.push({ id: select_ajax_id, text: option_text, selected: true });
					}
				}
			}

			// Add selection to select AJAX cache
			if((typeof($(this).select2) === 'function') && select_ajax_method_search) {

				$(this).select2(config);

				// Select event
				$(this).on('select2:select', function (e) {

					var id = e.params.data.id;
					var text = e.params.data.text;
					var data_meta_key = $(this).attr('data-meta-key');
					ws_this.select_ajax_cache[select_ajax_cache_id][id] = text;
				});

				// Autofocus
				$(this).on('select2:open', function (e) {

					$('.select2-search__field', $(this).parent()).get(0).focus();
				});
			}
		});
	}

	// Data grid
	$.WS_Form.prototype.data_grid_html = function(meta_key, meta_value, data_source_id, read_only, bulk_action_row_ids) {

		// Check data grid object
		if(typeof(meta_value) !== 'object') { return ''; }
		if(typeof(meta_value.columns) === 'undefined') { this.error('error_data_grid_columns'); }
		if(typeof(meta_value.groups) === 'undefined') { this.error('error_data_grid_groups'); }
		if(typeof(meta_value.rows_per_page) === 'undefined') { this.error('error_data_grid_rows_per_page'); }
		if(typeof(data_source_id) === 'undefined') { data_source_id = ''; }
		if(typeof(bulk_action_row_ids) === 'undefined') { bulk_action_row_ids = false; }

		// Get columns and rows
		var columns = meta_value.columns;
		var groups = meta_value.groups;
		var rows_per_page = meta_value.rows_per_page;

		// Get meta key config
		var meta_key_config = $.WS_Form.meta_keys[meta_key];

		// Are groups enabled?
		var groups_group = meta_key_config.groups_group;

		// Conditional?
		var conditional = ((typeof(meta_key_config.conditional) !== 'undefined') && meta_key_config.conditional);

		// Upload / download?
		var upload_download = (typeof(meta_key_config.upload_download) !== 'undefined') && meta_key_config.upload_download;

		// Data source
		var data_source = (typeof(meta_key_config.data_source) !== 'undefined') && meta_key_config.data_source;

		// Overrides
		if(typeof(rows_per_page_override) !== 'undefined') { rows_per_page = rows_per_page_override; }

		// Get counts
		var column_count = columns.length;
		var group_count = groups.length;

		var return_html = '';

		// Data source
		if(data_source) {

			// Div for injecting data source meta keys into
			return_html += '<div id="wsf-data-source-meta"></div>';
		}

		// Groups wrapper
		return_html += '<div class="wsf-field-wrapper wsf-data-grid-groups">';

		// Build group tabs
		return_html += '<div class="wsf-data-grid-group-tabs-wrapper">';

		return_html += '<ul class="wsf-data-grid-group-tabs">';

		var group_count = groups.length;
		for(var group_index in groups) {

			if(!groups.hasOwnProperty(group_index)) { continue; }
			if(typeof(groups[group_index]) === 'function') { continue; }

			var group = groups[group_index];
			var group_label = group.label;

			return_html += '<li class="wsf-data-grid-group-tab' + ((group_count == 1) ? ' ui-state-active' : '') + '">';
			return_html += '<a href="#wsf-data-grid-group-' + this.esc_attr(group_index) + '">' + this.esc_html(group_label) + '</a>';

			if(!read_only && group_count > 1) {
				return_html += '<div data-action="wsf-data-grid-group-delete"' + this.tooltip(this.language('data_grid_group_delete'), 'top-center') + '>' + this.svg('delete-circle') + '</div>';
			}

			return_html += '</li>';
		}

		if(!read_only && groups_group) {

			// Add group
			return_html += '<li class="wsf-ui-cancel" data-action="wsf-data-grid-group-add"><div' + this.tooltip(this.language('data_grid_group_add'), 'top-center') + '>' + this.svg('plus-circle') + '</div></li>';
		}

		return_html += "</ul>\n\n";

		// Icon array
		var li_array = [];

		// Upload/download?
		if(upload_download) {

			// Upload CSV
			if(!read_only) {

				li_array.push('<li><div data-action="wsf-data-grid-upload"' + this.tooltip(this.language('data_grid_group_upload_csv'), 'bottom-right') + '>' + this.svg('upload') + '</div><input type="file" class="wsf-file-upload" id="wsf-data-grid-upload-file" accept=".csv"/></li>');
			}

			// Download CSV
			li_array.push('<li><div data-action="wsf-data-grid-download"' + this.tooltip(this.language('data_grid_group_download_csv'), 'bottom-right') + '>' + this.svg('download') + '</div></li>');
		}

		// Compatibility
		if((typeof(meta_key_config.compatibility_url) !== 'undefined') && $.WS_Form.settings_plugin.helper_compatibility) {

			li_array.push('<li><div class="wsf-data-grid-compatibility"' + this.tooltip(this.language('field_compatibility'), 'bottom-right') + '><a class="wsf-compatibility" href="' + this.esc_attr(meta_key_config.compatibility_url) + '" target="_blank" tabindex="-1">' + this.svg('markup') + '</a></div></li>');
		}

		// Variable helper
		if((typeof(meta_key_config.variable_helper) !== 'undefined') && meta_key_config.variable_helper) {

			li_array.push('<li><div data-action="wsf-variable-helper"' + this.tooltip(this.language('variable_helper'), 'bottom-right') + '>' + this.svg('hash') + '</div></li>');
		}

		if(li_array.length) { return_html += '<ul class="wsf-data-grid-options">' + li_array.join('') + '</ul>'; }

		return_html += "</div>\n\n";

		// Build each group
		for(var group_index in groups) {

			if(!groups.hasOwnProperty(group_index)) { continue; }
			if(typeof(groups[group_index]) === 'function') { continue; }

			var group = groups[group_index];
			return_html += this.data_grid_html_group(group, group_index, columns, rows_per_page, meta_key, read_only, bulk_action_row_ids);

			if(!groups_group) { break; }
		}

		if(upload_download) {

			// Data upload-csv
			return_html += '<div class="wsf-data-grid-upload-csv-window"><div class="wsf-data-grid-upload-csv-window-content"><h1>' + this.language('drop_zone_data_grid') + '</h1><div class="wsf-uploads"></div></div></div>';
		}

		return_html += '</div>';

		return return_html;
	}

	// Data grid - Group
	$.WS_Form.prototype.data_grid_html_group = function(group, group_index, columns, rows_per_page, meta_key, read_only, bulk_action_row_ids) {

		// Group - Options
		var meta_key_config = $.WS_Form.meta_keys[meta_key];

		// Get current page
		var page = parseInt(group.page, 10);

		// Get row count
		if(typeof(group.rows) === 'undefined') { group.rows = []; }
		var rows = group.rows;
		var row_count = rows.length;

		// Support attributes
		var row_default = ((typeof(meta_key_config.row_default) !== 'undefined') ? meta_key_config.row_default : false);
		var row_disabled = read_only ? false : ((typeof(meta_key_config.row_disabled) !== 'undefined') ? meta_key_config.row_disabled : false);
		var row_required = ((typeof(meta_key_config.row_required) !== 'undefined') ? meta_key_config.row_required : false);
		var row_hidden = read_only ? false : ((typeof(meta_key_config.row_hidden) !== 'undefined') ? meta_key_config.row_hidden : false);

		// Group settings
		var groups_label = (typeof(meta_key_config.groups_label) !== 'undefined') ? meta_key_config.groups_label : true;
		var groups_label_label = (typeof(meta_key_config.groups_label_label) !== 'undefined') ? meta_key_config.groups_label_label : this.language('data_grid_groups_label');

		var groups_label_render = (typeof(meta_key_config.groups_label_render) !== 'undefined') ? meta_key_config.groups_label_render : true;
		var groups_label_render_label = (typeof(meta_key_config.groups_label_render_label) !== 'undefined') ? meta_key_config.groups_label_render_label : this.language('data_grid_groups_label_render');

		var groups_group = (typeof(meta_key_config.groups_group) !== 'undefined') ? meta_key_config.groups_group : true;
		var groups_group_label = (typeof(meta_key_config.groups_group_label) !== 'undefined') ? meta_key_config.groups_group_label : this.language('data_grid_groups_group');

		var groups_disabled = (typeof(meta_key_config.groups_disabled) !== 'undefined') ? meta_key_config.groups_disabled : true;
		var groups_auto_group = (typeof(meta_key_config.groups_auto_group) !== 'undefined') ? meta_key_config.groups_auto_group : false;
		var group_settings_show = groups_label || groups_auto_group || groups_group || groups_disabled;

		var rows_randomize = (typeof(meta_key_config.rows_randomize) !== 'undefined') ? meta_key_config.rows_randomize : false;

		// Build table
		var return_html = '<div id="wsf-data-grid-group-' + this.esc_attr(group_index) + '" class="wsf-data-grid-group" data-group-index="' + this.esc_attr(group_index) + '">';

		// Table
		return_html += '<div class="wsf-data-grid-table-outer"><div class="wsf-data-grid-table-inner"><table class="wsf-data-grid-table"><thead>';

		return_html += this.data_grid_html_row_header(columns, group_index, row_default, row_disabled, row_required, row_hidden, meta_key, read_only);

		return_html += '</thead><tbody>';

		// Build each row
		for(var row_index = (page * rows_per_page); ((row_index < row_count) && ((rows_per_page == 0) || (row_index < ((page + 1) * rows_per_page)))); row_index++) {

			return_html += this.data_grid_html_row(rows[row_index], group_index, row_index, row_default, row_disabled, row_required, row_hidden, meta_key, read_only, bulk_action_row_ids);
		}

		return_html += "</tbody></table>";

		return_html += '</div>';

		// Row - Add
		if(!read_only) {

			return_html += '<div data-action="wsf-data-grid-row-add"><div' + this.tooltip(this.language('data_grid_row_add'), 'left') + '>' + this.svg('plus-circle') + '</div></div>';
		}

		// Table - Outer/Inner
		return_html += "</div>";

		// Group - Pagination
		return_html += '<ul class="wsf-data-grid-pagination"></ul>';

		// Footer
		return_html += '<ul class="wsf-data-grid-footer wsf-list-inline">';

		if(!read_only) {

			// Bulk actions
			return_html += '<li>';

			return_html += '<label class="wsf-label wsf-label-small">' + this.language('data_grid_row_bulk_actions') + '</label>';

			return_html += '<div class="wsf-field-inline">';
			return_html += '<select class="wsf-field wsf-field-small">';
			return_html += '<option value="">' + this.language('data_grid_row_bulk_actions_select') + "</option>";

			// Group - Bulk action - Default
			if(row_default) {
				return_html += '<option value="default">' + this.language('data_grid_row_bulk_actions_default') + "</option>";
				return_html += '<option value="default_off">' + this.language('data_grid_row_bulk_actions_default_off') + "</option>";
			}

			// Group - Bulk action - Required
			if(row_required) {
				return_html += '<option value="required">' + this.language('data_grid_row_bulk_actions_required') + "</option>";
				return_html += '<option value="required_off">' + this.language('data_grid_row_bulk_actions_required_off') + "</option>";
			}

			// Group - Bulk action - Disabled
			if(row_disabled) {
				return_html += '<option value="disabled">' + this.language('data_grid_row_bulk_actions_disabled') + "</option>";
				return_html += '<option value="disabled_off">' + this.language('data_grid_row_bulk_actions_disabled_off') + "</option>";
			}

			// Group - Bulk action - Hidden
			if(row_hidden) {
				return_html += '<option value="hidden">' + this.language('data_grid_row_bulk_actions_hidden') + "</option>";
				return_html += '<option value="hidden_off">' + this.language('data_grid_row_bulk_actions_hidden_off') + "</option>";
			}

			// Group - Bulk action - Delete
			return_html += '<option value="delete">' + this.language('data_grid_row_bulk_actions_delete') + "</option>";

			return_html += '</select>';
			return_html += '<button class="wsf-button wsf-button-primary wsf-button-small" data-action="wsf-data-grid-bulk-action" disabled>' + this.language('data_grid_row_bulk_actions_apply') + "</button>";
			return_html += '</div>'

			return_html += '</li>'
		}

		// Data grid - Rows per page
		return_html += '<li>';
		return_html += '<label class="wsf-label wsf-label-small">' + this.language('data_grid_rows_per_page') + '</label>';
		return_html += '<div class="wsf-field-inline">';
		return_html += '<select class="wsf-field wsf-field-small">';
		return_html += '<option value="0">' + this.language('data_grid_rows_per_page_0') + '</option>';

		// Render rows per page options
		var rows_per_page_options = $.WS_Form.settings_form.data_grid.rows_per_page_options;

		for(var key in rows_per_page_options) {

			if(!rows_per_page_options.hasOwnProperty(key)) { continue; }

			return_html += '<option value="' + this.esc_attr(key) + '"' + (key == rows_per_page ? ' selected' : '') + '>' + this.esc_html(rows_per_page_options[key]) + '</option>';
		}

		return_html += '</select>';
		return_html += '<button class="wsf-button wsf-button-primary wsf-button-small" data-action="wsf-data-grid-rows-per-page" disabled>' + this.language('data_grid_rows_per_page_apply') + '</button>';
		return_html += '</div>'

		return_html += '</li>'

		if(!read_only && group_settings_show) {

			// Group - Settings - Open
			return_html += '<li class="wsf-flex-none"><div data-action="wsf-data-grid-settings"' + this.tooltip(this.language('data_grid_settings'), 'top-right') + '>' + this.svg('settings') + '</div></li>';
		}

		return_html += '</ul>'

		if(!read_only && group_settings_show) {

			return_html += '<div class="wsf-data-grid-settings-wrapper">';

			return_html += '<div class="wsf-data-grid-settings">';

			// Group - Settings - Label
			if(groups_label) {

				return_html += '<label for="wsf-data-grid-group-label-' + this.esc_attr(group_index) + '" class="wsf-label">' + this.esc_html(groups_label_label) + '</label>';
				return_html += '<input type="text" id="wsf-data-grid-group-label-' + this.esc_attr(group_index) + '" class="wsf-field" data-text="label" value="' + this.esc_attr(group.label) + "\" maxlength=\"1024\" />";
			}

			// Group - Settings - Group (optgroup / fieldset)
			if(groups_group) {

				return_html += '<input type="checkbox" id="wsf-data-grid-mask_group-' + this.esc_attr(group_index) + '" class="wsf-field" data-children="wsf-data-grid-groups-group-' + this.esc_attr(group_index) + '" data-checkbox="mask_group"' + (group.mask_group ? ' checked' : '') + " />";
				return_html += '<label for="wsf-data-grid-mask_group-' + this.esc_attr(group_index) + '" class="wsf-label">' + this.esc_html(groups_group_label) + '</label>';
			}

			return_html += '<div id="wsf-data-grid-groups-group-' + this.esc_attr(group_index) + '" class="wsf-field-indent">';

			// Group - Settings - Label - Render
			if(groups_label && groups_label_render) {

				return_html += '<input type="checkbox" id="wsf-data-grid-group-label-render-' + this.esc_attr(group_index) + '" class="wsf-field" data-checkbox="label_render"' + (group.label_render ? ' checked' : '') + " />";
				return_html += '<label for="wsf-data-grid-group-label-render-' + this.esc_attr(group_index) + '" class="wsf-label">' + this.esc_html(groups_label_render_label) + '</label>';
			}

			// Group - Settings - Disabled
			if(groups_disabled) {

				return_html += '<input type="checkbox" id="wsf-data-grid-disabled-' + this.esc_attr(group_index) + '" class="wsf-field" data-checkbox="disabled"' + (group.disabled ? ' checked' : '') + " />";
				return_html += '<label for="wsf-data-grid-disabled-' + this.esc_attr(group_index) + '" class="wsf-label">' + this.language('data_grid_group_disabled') + '</label>';
			}

			return_html += '</div>';

			// Group - Settings - Auto Group
			if(groups_auto_group) {

				return_html += '<label for="wsf-data-grid-auto-group-' + this.esc_attr(group_index) + '" class="wsf-label">' + this.language('data_grid_group_auto_group') + '</label>';
				return_html += '<select id="wsf-data-grid-auto-group-' + this.esc_attr(group_index) + '" class="wsf-field" data-action="wsf-data-grid-auto-group"></select>';
			}

			// /Settings
			return_html += '</div>';

			// /Settings wrapper
			return_html += '</div>';
		}

		// /Group
		return_html += "</div>\n\n";

		return return_html;
	}

	// Data grid - Header row
	$.WS_Form.prototype.data_grid_html_row_header = function(columns, group_index, row_default, row_disabled, row_required, row_hidden, meta_key, read_only) {

		// Get meta key config
		var meta_key_config = $.WS_Form.meta_keys[meta_key];

		// Config
		var max_columns = ((typeof(meta_key_config.max_columns) !== 'undefined') ? meta_key_config.max_columns : 0);
		var type_sub = (typeof(meta_key_config.type_sub) !== 'undefined') ? meta_key_config.type_sub : false;
		var read_only_header = (typeof(meta_key_config.read_only_header) !== 'undefined') ? meta_key_config.read_only_header : false;

		// Build data grid header row HTML
		var return_html = '<tr>'

		if(!read_only) {

			// Row ID
			var row_id = 'wsf-data-grid-bulk-' + meta_key + '-' + group_index;

			// Spacer - Sort
			return_html += '<th data-fixed-sort class="wsf-data-grid-icon"></th>';

			// Spacer - Bulk action select all
			return_html += '<th data-fixed-select class="wsf-data-grid-checkbox"><input id="' + this.esc_attr(row_id) + '" class="wsf-field" data-action="wsf-data-grid-row-select-all" type="checkbox" /><label for="' + this.esc_attr(row_id) + '" class="wsf-label"></label></th>'
		}

		var column_count = 0;
		for(var key in columns) {

			if(!columns.hasOwnProperty(key)) { continue; }
			if(typeof(columns[key]) === 'function') { continue; }

			var column = columns[key];
			var column_id = column.id;
			var column_name = column.label;

			return_html += '<th><input type="text" class="wsf-field wsf-field-small" data-id="' + this.esc_attr(column_id) + '" data-column="' + this.esc_attr(key) + '" data-action="wsf-data-grid-column-label" value="' + this.esc_attr(column_name) + '"' + ((read_only || read_only_header) ? ' readonly' : '') + '/>';

			if(!read_only && (columns.length > 1) && ((max_columns > 1) || (max_columns == 0))) {

				return_html += '<div data-action="wsf-data-grid-column-delete" data-id="' + this.esc_attr(column_id) + '"' + this.tooltip(this.language('data_grid_column_delete'), 'bottom-center') + '>' + this.svg('delete-circle') + '</div>';
			}

			return_html += '</th>';

			// Max columns
			column_count++;
			if((max_columns > 0) && (column_count == max_columns)) { break; }
		}

		// Sub type edit
		if(type_sub !== false) {

			return_html += '<th data-fixed-icon></th>';
			return_html += '<th data-fixed-icon></th>';
		}

		// Supported attributes
		if(type_sub !== false) { return_html += '<th data-fixed-icon></th>'; }
		if(row_default) { return_html += '<th data-fixed-icon></th>'; }
		if(row_required) { return_html += '<th data-fixed-icon></th>'; }
		if(row_disabled) { return_html += '<th data-fixed-icon></th>'; }
		if(row_hidden) { return_html += '<th data-fixed-icon></th>'; }

		if(
			!read_only &&
			((max_columns == 0) || (columns.length < max_columns))
		) {

			// Spacer - Add column
			return_html += '<th data-fixed-icon><div data-action="wsf-data-grid-column-add"' + this.tooltip(this.language('data_grid_column_add'), 'top-right') + '>' + this.svg('plus-circle') + '</div></th>';
		}

		return_html += '</tr>';

		return return_html;
	}

	// Data grid - Row
	$.WS_Form.prototype.data_grid_html_row = function(row, group_index, row_index, row_default, row_disabled, row_required, row_hidden, meta_key, read_only, bulk_action_row_ids) {

		// Get meta key config
		var meta_key_config = $.WS_Form.meta_keys[meta_key];

		// Config
		var max_columns = ((typeof(meta_key_config.max_columns) !== 'undefined') ? meta_key_config.max_columns : 0);
		var type_sub = (typeof(meta_key_config.type_sub) !== 'undefined') ? meta_key_config.type_sub : false;
		var conditional = ((typeof(meta_key_config.conditional) !== 'undefined') && meta_key_config.conditional);
		var insert_image = ((typeof(meta_key_config.insert_image) !== 'undefined') && meta_key_config.insert_image) && !read_only;

		// Read row data
		if(
			(row === null) ||
			(typeof(row) !== 'object') ||
			(typeof(row.data) !== 'object')
		) {
			return '';
		}
		var data = row.data;

		// Get column count
		if(typeof(data.length) === 'undefined') { return ''; }
		var column_count = data.length;

		// Build data grid row HTML
		var return_html = '<tr data-index="' + this.esc_attr(row_index) + '">';

		if(!read_only) {

			var row_id = 'wsf-data-grid-bulk-' + meta_key + '-' + group_index + '-' + row_index;

			// Sort
			return_html += '<td data-fixed-sort><div data-action="wsf-data-grid-row-sort"' + this.tooltip(this.language('data_grid_row_sort'), 'top-left') + '>' + this.svg('sort') + '</div></td>'

			// Selector
			var bulk_action_row_checked = ((typeof(bulk_action_row_ids) === 'object') && (bulk_action_row_ids.indexOf(row_index) !== -1));
			return_html += '<td data-fixed-select><input data-action="wsf-data-grid-row-select" id="' + this.esc_attr(row_id) + '" class="wsf-field" type="checkbox" tabindex="-1"' + (bulk_action_row_checked ? ' checked' : '') + ' /><label for="' + this.esc_attr(row_id) + '" class="wsf-label"></label></td>'
		}

		// Build each column
		for(var column_index = 0; column_index < column_count; column_index++) {

			var column_value = data[column_index];
			return_html += '<td' + (insert_image ? ' data-insert-image' : '') + '><input class="wsf-field wsf-field-small" type="text" data-column="' + this.esc_attr(column_index) + '" value="' + this.esc_attr(column_value) + '"' + (read_only ? ' readonly' : '') + ' />';

			if(insert_image) {

				return_html += '<div data-action="wsf-insert-image"' + this.tooltip(this.language('data_grid_insert_image'), 'left') + '>' + this.svg('file-picture') + '</div>';
			}

			return_html += '</td>';

			// Max columns
			if((max_columns > 0) && (column_index == (max_columns - 1))) { break; }
		}

		// Sub type edit
		if(type_sub !== false) {

			return_html += '<td data-fixed-icon><div data-action="wsf-data-grid-' + this.esc_attr(type_sub) + '-edit"' + this.tooltip(this.language('data_grid_' + type_sub + '_edit'), 'top-center') + '>' + this.svg('edit') + '</div></td>';
			return_html += '<td data-fixed-icon><div data-action="wsf-data-grid-' + this.esc_attr(type_sub) + '-clone"' + this.tooltip(this.language('data_grid_' + type_sub + '_clone'), 'top-center') + '>' + this.svg('clone') + '</div></td>';
		}

		// Supported attributes
		if(row_default) { return_html += '<td data-fixed-icon><div data-attribute="default" data-status="' + (row.default ? 'on': '') + '"' + (read_only ? ' data-read-only' : '') + this.tooltip(this.language('data_grid_row_default'), 'top-center') + '>' + this.svg('check') + '</div></td>'; }
		if(row_required) { return_html += '<td data-fixed-icon><div data-attribute="required" data-status="' + (row.required ? 'on': '') + '"' + (read_only ? ' data-read-only' : '') + this.tooltip(this.language('data_grid_row_required'), 'top-center') + '>' + this.svg('asterisk') + '</div></td>'; }
		if(row_disabled) { return_html += '<td data-fixed-icon><div data-attribute="disabled" data-status="' + (row.disabled ? 'on': '') + '"' + (read_only ? ' data-read-only' : '') + this.tooltip(this.language('data_grid_row_disabled'), 'top-center') + '>' + this.svg('disabled') + '</div></td>'; }
		if(row_hidden) { return_html += '<td data-fixed-icon><div data-attribute="hidden" data-status="' + (row.hidden ? 'on': '') + '"' + (read_only ? ' data-read-only' : '') + this.tooltip(this.language('data_grid_row_hidden'), 'top-center') + '>' + this.svg('visible') + this.svg('hidden') + '</div></td>'; }

		// Delete
		if(!read_only) {

			return_html += '<td data-fixed-icon><div data-action="wsf-data-grid-row-delete"' + this.tooltip(this.language('data_grid_row_delete'), 'top-right') + '>' + this.svg('delete-circle') + '</div></td>';
		}

		return_html += "</tr>\n";

		return return_html;
	}

	// Data grids - Init
	$.WS_Form.prototype.sidebar_data_grids_init = function(obj) {

		// Init data grids
		$('.wsf-data-grid:not(.wsf-data-grid-initialized)', obj).each(function(i, e) {

			$.WS_Form.this.data_grid_init($(this), e);
		});
	}

	// Data grid - Settings - Children
	$.WS_Form.prototype.data_grid_settings_children = function(data_grid_obj, obj) {

		// Meta value
		var this_meta_value = obj.is(':checked');

		// Children
		var children = obj.attr('data-children');
		if(children) {

			var children_array = children.split(',');

			for(var child_index in children_array) {

				if(!children_array.hasOwnProperty(child_index)) { continue; }

				var child = children_array[child_index];

				if(this_meta_value) {

					$('#' + child, data_grid_obj).show();

				} else {

					$('#' + child, data_grid_obj).hide();
				}
			}
		}
	}

	// Data grid - Init
	$.WS_Form.prototype.data_grid_init = function(obj, element) {

		element.render = function(read_only, row_index_focus, bulk_action_row_ids) {

			// Read only?
			if(typeof(read_only) === 'undefined') { read_only = false; }

			// Focus row?
			if(typeof(row_index_focus) === 'undefined') { row_index_focus = false; }

			// Bulk action row ids?
			if(typeof(bulk_action_row_ids) === 'undefined') { bulk_action_row_ids = false; }

			// Get data grid attributes
			var object = obj.attr('data-object');
			var object_id = obj.attr('data-id');
			var meta_key = obj.attr('data-meta-key');
			var meta_key_config = $.WS_Form.meta_keys[meta_key];
			var meta_key_type_sub = (typeof(meta_key_config['type_sub']) !== 'undefined') ? meta_key_config['type_sub'] : false;
			var data_source = (typeof(meta_key_config['data_source']) !== 'undefined') ? meta_key_config['data_source'] : false;

			if(meta_key_type_sub === 'conditional') {

				// Reset conditional highlight row index
				$.WS_Form.this.conditional_highlight_row_index = false;

				// Render conditional logic icons
				$.WS_Form.this.conditional_icons_build(true, $.WS_Form.this.object_data_scratch);
			}
			// Get object data
			var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
			if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

			// Get current data grid data
			var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, '', true);

			// If data is empty, we'll create empty data
			if(meta_value === '') {

				var meta_value = $.extend(true, {}, meta_key_config.default);
				$.WS_Form.this.set_object_meta_value(object_data, meta_key, meta_value);
			}

			// Optimize data
			meta_value = $.WS_Form.this.data_grid_optimize(meta_value);

			// Render HTML
			var data_source_id = $.WS_Form.this.get_data_source_id(object_data);

			obj.html($.WS_Form.this.data_grid_html(meta_key, meta_value, data_source_id, read_only, bulk_action_row_ids));

			var render_redo = false;

			// Data source
			if(data_source) {

				// Initial render
				$.WS_Form.this.data_source_render(obj, element);
			}

			if(!read_only) {

				// Focus
				if(row_index_focus !== false) {

					var input_first = $('tr[data-index="' + $.WS_Form.this.esc_selector(row_index_focus) + '"] td input[type="text"]').first();
					if(input_first.val()) { input_first.trigger('select'); } else { input_first.trigger('focus'); }
				}

				// Auto group
				$('[data-action="wsf-data-grid-auto-group"]', obj).on('change', function() {

					var auto_group_index = $(this).val();

					// Get group_index
					var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');

					// Get meta data (Deep clone)
					var group = $.extend(true, {}, meta_value.groups[group_index]);

					// Get rows
					var rows = group.rows;

					// Create new groups data
					var groups_new = [];
					for(var row_index in rows) {

						if(!rows.hasOwnProperty(row_index)) { continue; }

						// Get label
						var group_label = rows[row_index]['data'][auto_group_index];
						if(typeof(group_label) === 'undefined') { group_label = ''; }

						// See if this exists, if not create key
						if(typeof(groups_new[group_label]) === 'undefined') {

							groups_new[group_label] = $.extend(true, {}, group);
							groups_new[group_label].label = (group_label == '') ? $.WS_Form.this.language('data_grid_group_label_default') : group_label;
							groups_new[group_label].rows = [];
							groups_new[group_label].mask_group = 'on';
							groups_new[group_label].page = '0';
							groups_new[group_label].disabled = '';
						}

						// Add row to group
						groups_new[group_label].rows.push(rows[row_index]);
					}

					// Move to meta_value
					meta_value.groups = [];
					for(var group_key in groups_new) {

						if(!groups_new.hasOwnProperty(group_key)) { continue; }

						meta_value.groups.push(groups_new[group_key]);
					}

					// Sort each group by label
					meta_value.groups.sort(function(a, b) {

						return (a.label === b.label) ? true : ((a.label < b.label) ? -1 : 1);
					});

					// Reset group index
					meta_value.group_index = 0;

					// Render
					element.render();
				});

				// Calculate width of icon and delete columns combined and set that as padding right on table
				$('.wsf-data-grid-groups table', obj).each(function() {

					var th_icon_icons_width = 0;
					var th_icons = $('th[data-fixed-icon]', $(this));
					for(var th_icon_index = 0; th_icon_index < th_icons.length; th_icon_index++) {

						th_icon_icons_width += $(th_icons[th_icon_index]).outerWidth();
					}

					var table_padding_right = th_icon_icons_width;

					if(ws_form_settings.rtl) {

						$(this).css({'padding-right': '47px', 'padding-left': table_padding_right + 'px'});

					} else {

						$(this).css({'padding-left': '47px', 'padding-right': table_padding_right + 'px'});
					}
				});

				// Drag enter
				$('.wsf-data-grid-group', obj).on('dragenter', function (e) {

					e.stopPropagation();
					e.preventDefault();

					// Check dragged object is a file
					if(!$.WS_Form.this.drag_is_file(e)) { return; }

					$('.wsf-data-grid-upload-csv-window', obj).show();
				});

				// Drag over
				$('.wsf-data-grid-upload-csv-window', obj).on('dragover', function (e) {

					e.stopPropagation();
					e.preventDefault();
				});

				// Drop
				$('.wsf-data-grid-upload-csv-window', obj).on('drop', function (e) {

					e.preventDefault();

					var files = e.originalEvent.dataTransfer.files;
					$.WS_Form.this.data_grid_upload_csv(object, object_id, meta_key, files, $(this), function() {

						$('.wsf-data-grid-upload-csv-window', obj).hide();
					});
				});

				// Drag leave
				$('.wsf-data-grid-upload-csv-window', obj).on('dragleave', function (e) {

					$('.wsf-data-grid-upload-csv-window', obj).hide();
				});

				// Upload
				$('[data-action="wsf-data-grid-upload"]', obj).on('click', function() {

					// Click file input
					$('input[id="wsf-data-grid-upload-file"]', obj).trigger('click');
				});
				$('input[id="wsf-data-grid-upload-file"]', obj).on('change', function() {

					var files = $('input[id="wsf-data-grid-upload-file"]', obj).prop("files");

					if(files.length > 0) {

						var data_grid_upload_csv_window = $('.wsf-data-grid-upload-csv-window', obj);
						data_grid_upload_csv_window.show();
						$.WS_Form.this.data_grid_upload_csv(object, object_id, meta_key, files, data_grid_upload_csv_window);
					}
				});

				// Media picker
				var insert_image = ((typeof(meta_key_config.insert_image) !== 'undefined') && meta_key_config.insert_image);
				if(insert_image) {

					$('[data-action="wsf-insert-image"]', obj).on('click', function() {

						// Get associated input
						$.WS_Form.this.file_frame_input_obj = $('input', $(this).closest('td'));

						// If the media frame already exists, reopen it.
						if($.WS_Form.this.file_frame) {

							// Open frame
							$.WS_Form.this.file_frame.open();
							return;
						}

						// Create the media frame.
						$.WS_Form.this.file_frame = wp.media.frames.file_frame = wp.media({

							title: 'Select image',
							library: {
								type: 'image'
							},
							button: {
								text: 'Use this image'
							},
   							multiple: false
						});

						// When an image is selected, run a callback.
						$.WS_Form.this.file_frame.on('select', function() {

							// We set multiple to false so only get one image from the uploader
							var attachment = $.WS_Form.this.file_frame.state().get('selection').first().toJSON();

							// Get img tag attributes
							var img_src = attachment.url;
							var img_alt = attachment.alt;
							var img_width = attachment.width;
							var img_height = attachment.height;

							// Build HTML
							var img_html = '<img' + (img_width ? (' width="' + $.WS_Form.this.esc_attr(img_width) + '"') : '') + (img_height ? (' height="' + $.WS_Form.this.esc_attr(img_height) + '"') : '') + (img_alt ? (' alt="' + $.WS_Form.this.esc_attr(img_alt) + '"') : '') + ' src="' + $.WS_Form.this.esc_attr(img_src)+ '" />';

							// Set input value
							$.WS_Form.this.file_frame_input_obj.val(img_html).trigger('input');
						});

						// Finally, open the modal
						$.WS_Form.this.file_frame.open();
					});
				}
			}

			// Download
			$('[data-action="wsf-data-grid-download"]', obj).on('click', function() {

				// Get current group object
				var group_obj = $($('.wsf-data-grid-group-tabs .ui-tabs-active').find('a').attr('href'), obj);

				// Get group index
				var group_index = group_obj.attr('data-group-index');

				// Initiate file download
				$.WS_Form.this.data_grid_download_csv(object, object_id, meta_key, group_index);
			});

			if(!read_only) {
				// Condition - Edit
				$('[data-action="wsf-data-grid-conditional-edit"]', obj).on('click', function() {

					$.WS_Form.this.data_grid_row_open($(this), 'conditional', meta_value);
				});

				// Condition - Clone
				$('[data-action="wsf-data-grid-conditional-clone"]', obj).on('click', function() {

					// Save
					$.WS_Form.this.conditional_save();

					// Clone
					$.WS_Form.this.data_grid_row_clone($(this), obj, object, object_id, element, meta_value, meta_key_type_sub);
				});
				// Action - Edit
				$('[data-action="wsf-data-grid-action-edit"]', obj).on('click', function() {

					$.WS_Form.this.data_grid_row_open($(this), 'action', meta_value);
				});

				// Action - Clone
				$('[data-action="wsf-data-grid-action-clone"]', obj).on('click', function(e) {

					// Save
					$.WS_Form.this.action_save();

					// Clone
					$.WS_Form.this.data_grid_row_clone($(this), obj, object, object_id, element, meta_value, meta_key_type_sub);
				});

				// Attributes
				$('[data-attribute]', obj).on('click', function() {

					// Read attribute data
					var attribute = $(this).attr('data-attribute');

					// Get group
					var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');
					var group = meta_value.groups[group_index];

					var rows = group.rows;

					var attribute_on = ($(this).attr('data-status') == 'on');

					// Determine whether you can multi-select default values
					var multiple = (obj.closest('.wsf-sidebar').find('[data-meta-key="multiple"]:checked').length == 1);
					switch(attribute) {

						case 'default' :

							var row_default_multiple = (typeof(meta_key_config.row_default_multiple) !== 'undefined') ? meta_key_config.row_default_multiple : false;
							multiple = (multiple || row_default_multiple);
							break;

						case 'required' :

							var row_required_multiple = (typeof(meta_key_config.row_required_multiple) !== 'undefined') ? meta_key_config.row_required_multiple : false;
							multiple = (multiple || row_required_multiple);
							break;

						case 'disabled' :

							multiple = true;
							break;

						case 'hidden' :

							multiple = true;
							break;
					}

					// If this is a default or required attribute and you cannot select multiple, then clear current
					if(!multiple) {

						// CSS change
						$('.wsf-data-grid-group [data-attribute="' + $.WS_Form.this.esc_selector(attribute) + '"]', $(this).closest('.wsf-data-grid-groups')).attr('data-status', '');

						// Data change
						for(var group_all_index in meta_value.groups) {

							if(!meta_value.groups.hasOwnProperty(group_all_index)) { continue; }

							var group_all = meta_value.groups[group_all_index];
							var rows_all = group_all.rows;

							// Data change
							for(var row_all_index in rows_all) {

								if(!rows_all.hasOwnProperty(row_all_index)) { continue; }

								delete rows_all[row_all_index][attribute];
							}
						}
					}

					// CSS change
					if(attribute_on) {

						$(this).attr('data-status', '');
						var attribute_value = '';

					} else {

						$(this).attr('data-status', 'on');
						var attribute_value = 'on';
					}

					// Get row offset
					var page = group.page;
					var rows_per_page = meta_value.rows_per_page;
					var row_offset = (page * rows_per_page);

					// Get data position
					var row_index = row_offset + ($(this).closest('tr').index());

					// Data change
					if(attribute_value == '') {

						delete rows[row_index][attribute];

					} else {

						rows[row_index][attribute] = attribute_value;
					}

					// Conditional icons
					if(
						(attribute === 'disabled') &&
						(meta_key_type_sub === 'conditional') 
					) {

						$.WS_Form.this.conditional_icons_build(true, $.WS_Form.this.object_data_scratch);
					}
				});

				// Options
				$('[data-action="wsf-data-grid-settings"]', obj).on('click', function() {

					// Get options div
					var options_wrapper = $('.wsf-data-grid-settings-wrapper', obj);
					var options = $('.wsf-data-grid-settings', obj);

					// Determine if options is visible or not
					var visible = options.is(':visible');

					// Animate
					if(visible) {

						// Hide
						$(this).removeClass('wsf-editing');
						options_wrapper.removeClass('wsf-data-grid-settings-open');
						options.slideUp();

					} else {

						// Show
						$(this).addClass('wsf-editing');
						options_wrapper.addClass('wsf-data-grid-settings-open');
						options.slideDown();
					}
				});

				// Options - Checkboxes - Init
				$('input[data-checkbox][data-children]', obj).each(function() {

					$.WS_Form.this.data_grid_settings_children(obj, $(this));
				});

				// Options - Checkboxes
				$('input[data-checkbox]', obj).on('change', function() {

					// Get group_index
					var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');

					// Get meta data
					var group = meta_value.groups[group_index];

					// Meta key
					var this_meta_key = $(this).attr('data-checkbox');

					// Meta value
					var this_meta_value = $(this).is(':checked');

					// Set value
					group[this_meta_key] = this_meta_value ? 'on' : '';

					// Children
					$.WS_Form.this.data_grid_settings_children(obj, $(this));
				});

				// Options - Text
				$('input[data-text]', obj).on('input', function() {

					// Get group_index
					var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');

					// Get meta data
					var group = meta_value.groups[group_index];

					// Meta key
					var this_meta_key = $(this).attr('data-text');

					// Meta value
					var this_meta_value = ($(this).val() != '') ? $(this).val() : $.WS_Form.this.language('data_grid_group_label_default');

					// Set value
					group[this_meta_key] = this_meta_value;

					// Update tab copy
					$('.wsf-data-grid-group-tab a[href="#wsf-data-grid-group-' + $.WS_Form.this.esc_selector(group_index) + '"], .wsf-data-grid-group-tab span', obj).html($.WS_Form.this.esc_html(this_meta_value));
				});
			}

			// Options - Rows per page
			$('[data-action="wsf-data-grid-rows-per-page"]', obj).on('click', function() {

				// Get rows per page
				var rows_per_page = parseInt($(this).siblings('select').first().val(), 10);

				// Save rows per page
				$.WS_Form.this.data_grid_group_rows_per_page_set(meta_value, rows_per_page, object, object_id, meta_key, function() {

					// Refresh data grid
					element.render(read_only);
				});
			});
			$('[data-action="wsf-data-grid-rows-per-page"]', obj).each(function() {

				$(this).siblings('select').first().on('change', function() {

					$('[data-action="wsf-data-grid-rows-per-page"]', obj).prop('disabled', false);
				});
			});

			// Pagination
			$('.wsf-data-grid-pagination', obj).each(function() {

				// Get group_index
				var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');

				// Get meta data
				var group = meta_value.groups[group_index];
				var rows_per_page = meta_value.rows_per_page;
				var page = (rows_per_page == 0) ? 0 : parseInt(group.page, 10);
				var rows = group.rows;
				var row_offset = (page * rows_per_page);
				var row_count = rows.length;
				var pagination_reach = 4;

				// Total pages
				var pages = (rows_per_page == 0) ? 1 : Math.ceil(row_count / rows_per_page);

				// If current page is beyond last page, set page to last page
				if(((page * rows_per_page) >= row_count) && (row_count > 0)) {

					// Go to the last page
					page = (pages - 1);

					// Save new page
					$.WS_Form.this.data_grid_group_page_set(group, group_index, page, object, object_id, meta_key, function() {

						// Refresh data grid
						render_redo = true;
					});
				}

				if(pages > 1) {

					// Calculate page positions for navigation elements
					var page_first = 0;
					var page_last = (pages - 1);
					var page_previous = (page > 0) ? (page - 1) : page;
					var page_next = (page < page_last) ? (page + 1) : page;
					var page_previous_disabled = ((page == 0) ? ' class="disabled"' : '');
					var page_next_disabled = ((page == page_last) ? ' class="disabled"' : '');

					// Previous
					var pagination_html = '<li data-page="' + $.WS_Form.this.esc_attr(page_first) + '"' + page_previous_disabled + '><div>' + $.WS_Form.this.svg('first') + '</div></li>';
					pagination_html += '<li data-page="' + $.WS_Form.this.esc_attr(page_previous) + '"' + page_previous_disabled + '><div>' + $.WS_Form.this.svg('previous') + '</div></li>';

					// Pagination start and end (Goes as far as pagination reach)
					var page_index_start = page - pagination_reach;
					if(page_index_start < 0) { page_index_start = 0; }
					var page_index_end = page + pagination_reach;
					if(page_index_end > page_last) { page_index_end = page_last; }

					// Pagination
					for(var page_index = page_index_start; page_index < (page_index_end + 1); page_index++) {

						pagination_html += '<li' + ((page_index == page) ? ' class="active"' : '') + ' data-page="' + $.WS_Form.this.esc_attr(page_index) + '"><div>' + (page_index + 1) + '</div></li>';
					}

					// Next
					pagination_html += '<li data-page="' + $.WS_Form.this.esc_attr(page_next) + '"' + page_next_disabled + '><div>' + $.WS_Form.this.svg('next') + '</div></li>';
					pagination_html += '<li data-page="' + $.WS_Form.this.esc_attr(page_last) + '"' + page_next_disabled + '><div>' + $.WS_Form.this.svg('last') + '</div></li>';

					// Inject pagination
					$(this).prepend(pagination_html);

					// Pagination events
					$('li[data-page]', $(this)).on('click', function() {

						// Get page
						var page_new = $(this).attr('data-page');
						if(page != page_new) {

							// Save new page
							$.WS_Form.this.data_grid_group_page_set(group, group_index, page_new, object, object_id, meta_key, function() {

								// Refresh data grid
								element.render(read_only);
							});
						}
					});

				} else {

					// Remove page selector, only 1 page
					$(this).remove();
				}
			});

			// Redo render because pages changed
			if(render_redo) { element.render(read_only); return false; }

			// Group tabs
			var meta_key_config = $.WS_Form.meta_keys[meta_key];
			var groups_group = meta_key_config.groups_group;

			if(groups_group) {

				var group_index = meta_value.group_index;
				$('.wsf-data-grid-groups', obj).tabs({

					active: group_index,

					activate: function(e, ui) {

						if(!read_only) {

							// Refresh sortable positions (to ensure li helpers vertical positioning is correct)
							$('table.wsf-data-grid-table', obj).sortable('refreshPositions');
						}

						// Get new group index
						var group_index_new = ui.newTab.index();

						// Save new group index to scratch
						meta_value.group_index = group_index_new;

						// Get object data of original field
						var object_data_old = $.WS_Form.this.get_object_data(object, object_id);
						if(object_data_old === false) { $.WS_Form.this.error('error_object_data'); }

						// Get data grid data of original field
						var meta_value_old = $.WS_Form.this.get_object_meta_value(object_data_old, meta_key, false);
						if(meta_value_old === false) { $.WS_Form.this.error('error_object_meta_value'); }

						if(typeof(meta_value_old.groups[group_index_new]) !== 'undefined') {

							// Loader on
							$.WS_Form.this.loader_on();

							// Save new group index to original field
							meta_value_old.group_index = group_index_new;

							// Build parameters
							var params = {

								form_id: $.WS_Form.this.form_id
							};

							// Object data
							params[object] = object_data_old;
							params[object]['history_suppress'] = 'on';

							// Call AJAX request
							$.WS_Form.this.api_call(object + '/' + object_id + '/put/', 'POST', params, function(response) {

								// Loader off
								$.WS_Form.this.loader_off();
							});
						}
					}
				});

				if(!read_only) {

					// Group tabs - Sortable
					$('.wsf-data-grid-group-tabs', obj).sortable({

						cursor:				'move',
						containment: 		'parent',
						scroll: 			false,
						forceHelperSize:	true,
						placeholder:		'wsf-data-grid-group-tab-placeholder',
						cancel:				'.wsf-ui-cancel',
						items:				'li:not(.wsf-ui-cancel)',

						start: function(e, ui) {

							// Get index being dragged
							$.WS_Form.data_grid_group_tab_index_dragged_from = ui.helper.index();

							var height = ui.helper.height();
							var width = ui.helper.outerWidth();
							var styles = [
								'height: ' + height + 'px',
								'width: ' + width + 'px'
							].join(';');

							ui.placeholder.attr('style', styles);
						},

						stop: function(e, ui) {

							// Get meta value
							var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key);
							if(meta_value === false) { $.WS_Form.this.error('error_object_meta_value'); }

							// Get groups
							var groups = meta_value.groups;

							// Get index dragged to
							var group_index_old = $.WS_Form.data_grid_group_tab_index_dragged_from;
							var group_index_new = ui.item.index();

							// Move meta data index
							if (group_index_new >= groups.length) {

								var k = group_index_new - groups.length;
								while ((k--) + 1) {
									groups.push(undefined);
								}
							}
							groups.splice(group_index_new, 0, groups.splice(group_index_old, 1)[0]);
						}
					});

					// Group tabs - Add
					$('[data-action="wsf-data-grid-group-add"]', obj).on('click', function() {

						if(typeof(meta_value.groups[0]) !== 'undefined') {

							// Build new group (Deep clone)
							var group_new = $.extend(true, {}, meta_value.groups[0]);

							// No rows for a new group
							group_new.rows = [];

							// Defaults
							group_new.label = $.WS_Form.this.language('data_grid_group_label_default');
							group_new.disabled = '';
							group_new.mask_group = '';
							group_new.label_render = '';

							// Add group
							meta_value.groups.push(group_new);

							// Get group count and set group_index to last
							meta_value.group_index = meta_value.groups.length - 1;

							// Refresh data grid
							element.render();

						} else {

							$.WS_Form.this.error('error_data_grid_default_group');
						}
					});

					// Group tabs - Delete
					$('[data-action="wsf-data-grid-group-delete"]', obj).on('click', function() {

						var group_obj = $(this).closest('li');
						var group_index = group_obj.index();

						// Get groups
						var groups = meta_value.groups;

						// Buttons
						var buttons = [

							{label:$.WS_Form.this.language('cancel'), action:'wsf-cancel'},
							{label:$.WS_Form.this.language('delete'), action:'wsf-confirm', class:'wsf-button-danger'}
						];

						// Call popover
						$.WS_Form.this.popover($.WS_Form.this.language('confirm_data_grid_group_delete'), buttons, group_obj, function() {

							// Delete row
							delete groups[group_index];

							// Remove empty values and write back to meta_value
							meta_value.groups = $.WS_Form.this.array_remove_empty(groups);

							// Select next closest tab
							group_index--;
							if(group_index < 0) { group_index = 0; }
							meta_value.group_index = group_index;

							// Refresh data grid
							element.render();
						});
					});
				}
			}

			if(!read_only) {

				// Columns - Add
				$('[data-action="wsf-data-grid-column-add"]', obj).on('click', function() {

					// Get columns
					var columns = meta_value.columns;

					// Get highest column id
					var id_new = 0;
					for(var key in columns) {

						if(!columns.hasOwnProperty(key)) { continue; }

						if(columns[key].id > id_new) { id_new = columns[key].id; }
					}
					id_new++;

					// Build new column
					var column_new = {

						'id':		id_new,
						'label':	$.WS_Form.this.language('data_grid_column_label_default')
					}

					// Add column
					meta_value.columns.push(column_new);

					// Add column to rows
					var groups = meta_value.groups;

					for(var group_key in groups) {

						if(!groups.hasOwnProperty(group_key)) { continue; }

						var group = groups[group_key];

						var rows = group.rows;

						for(var row_key in rows) {

							if(!rows.hasOwnProperty(row_key)) { continue; }

							var row = rows[row_key];

							if(row === null) { continue; }

							row.data.push('');
						}
					}

					// Update data mask fields
					$.WS_Form.this.data_grid_update_mask_row_lookups(object, object_id, meta_key);

					// Get current scroll position
					var scroll_left = $('.wsf-data-grid-table-inner', obj).scrollLeft();

					// Render
					element.render();

					var last_th_obj = $(".wsf-data-grid-table-inner thead th:not('[data-fixed-icon]'):last", obj);

					// Get width of newly added column
					var column_width = last_th_obj.outerWidth();

					// Scroll left
					$('.wsf-data-grid-table-inner', obj).scrollLeft(scroll_left).animate({scrollLeft: scroll_left + column_width}, 200);

					// Select input
					$('input', last_th_obj).trigger('select');
				});

				// Columns - Delete
				$('[data-action="wsf-data-grid-column-delete"]', obj).on('click', function() {

					// Get column ID to delete
					var column_obj = $(this).closest('th');
					var column_id = $(this).attr('data-id');

					// Buttons
					var buttons = [

						{label:$.WS_Form.this.language('cancel'), action:'wsf-cancel'},
						{label:$.WS_Form.this.language('delete'), action:'wsf-confirm', class:'wsf-button-danger'}
					];

					// Call popover
					$.WS_Form.this.popover($.WS_Form.this.language('confirm_data_grid_column_delete'), buttons, column_obj, function() {

						// Get columns
						var columns = meta_value.columns;

						// Find key to delete
						for(var column_key in columns) {

							if(!columns.hasOwnProperty(column_key)) { continue; }

							if(parseInt(columns[column_key].id) == parseInt(column_id, 10)) {

								delete columns[column_key];

								// Remove empty elements after delete
								meta_value.columns = $.WS_Form.this.array_remove_empty(meta_value.columns);

								break;
							}
						}

						// Delete column from groups and rows
						if(column_key !== false) {

							var groups = meta_value.groups;

							for(var group_key in groups) {

								if(!groups.hasOwnProperty(group_key)) { continue; }

								var group = groups[group_key];

								var rows = group.rows;

								for(var row_key in rows) {

									if(!rows.hasOwnProperty(row_key)) { continue; }

									var row = rows[row_key];

									if(row === null) { continue; }

									delete row.data[column_key];

									// Remove empty elements after delete
									row.data = $.WS_Form.this.array_remove_empty(row.data);
								}
							}
						}

						// Update data mask fields
						$.WS_Form.this.data_grid_update_mask_row_lookups(object, object_id, meta_key);

						// Get current scroll position
						var scroll_left = $('.wsf-data-grid-table-inner', obj).scrollLeft();

						// Get width of newly added column
						var column_width = column_obj.outerWidth();

						// Render
						element.render();

						// Scroll left
						$('.wsf-data-grid-table-inner', obj).scrollLeft(scroll_left).animate({scrollLeft: scroll_left - column_width}, 200);
					});
				});

				// Column - Name
				$('[data-action="wsf-data-grid-column-label"]', obj).on('keydown input', function(e) {

					var keyCode = e.keyCode || e.which;

					// Enter key
					if (keyCode === 13) {

						$(this).trigger('blur');
						return false;
					}

					// Get column index
					var column_index = $(this).attr('data-column');
					var column = meta_value.columns[column_index];

					// Meta value
					var label = ($(this).val() != '') ? $(this).val() : $.WS_Form.this.language('data_grid_column_label_default');

					// Update column label
					meta_value.columns[column_index].label = label;

					// Update data mask fields
					$.WS_Form.this.data_grid_update_mask_row_lookups(object, object_id, meta_key);
				});

				// Rows - Input changes
				$('td input[type="text"]', obj).on('keydown input', function(e) {

					var keyCode = e.keyCode || e.which;

					// Enter key
					if (keyCode === 13) {

						var inputs = $('input[type="text"]', obj);
						var idx = inputs.index(this);
						if(typeof(inputs[idx + 1]) !== 'undefined') { $(inputs[idx + 1]).trigger('focus'); }
						return false;
					}

					// Get group
					var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');
					var group = meta_value.groups[group_index];

					// Get row offset
					var page = group.page;
					var rows_per_page = meta_value.rows_per_page;
					var row_offset = (page * rows_per_page);

					// Get data position
					var row_index = row_offset + ($(this).closest('tr').index());
					var column_index = $(this).attr('data-column');

					// Update row data
					var rows = group.rows;
					rows[row_index]['data'][column_index] = $(this).val();
				});

				// Rows - Sortable
				$('table.wsf-data-grid-table', obj).sortable({

					items: 'tbody tr',
					containment: 'parent',
					cursor: 'move',
					tolerance: 'pointer',
					handle: '[data-action="wsf-data-grid-row-sort"]',
					axis: 'y',
					cancel: '.wsf-ui-cancel, input[type=text]:not([readonly])',

					start: function(e, ui) {

						// Refresh sortable positions (to ensure li helpers vertical positioning is correct)
						$('table.wsf-data-grid-table', obj).sortable('refreshPositions');

						// Get index being dragged
						$.WS_Form.data_grid_index_dragged_from = (ui.helper.index());
					},

					stop: function(e, ui) {

						// Get meta value
						var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key);
						if(meta_value === false) { $.WS_Form.this.error('error_object_meta_value'); }

						// Get group
						var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');
						var group = meta_value.groups[group_index];

						// Get row offset
						var page = group.page;
						var rows_per_page = meta_value.rows_per_page;
						var row_offset = (page * rows_per_page);

						// Get index dragged to
						var row_index_old = row_offset + $.WS_Form.data_grid_index_dragged_from;
						var row_index_new = row_offset + (ui.item.index());

						// Move meta data index
						var rows = group.rows;
						if (row_index_new >= rows.length) {

							var k = row_index_new - rows.length;

							while ((k--) + 1) {
								rows.push(undefined);
							}
						}
						rows.splice(row_index_new, 0, rows.splice(row_index_old, 1)[0]);
					}
				});

				// Rows - Add
				$('[data-action="wsf-data-grid-row-add"] div', obj).on('click', function() {

					// Get group
					var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');
					var group = meta_value.groups[group_index];
					var rows_per_page = meta_value.rows_per_page;
					var page = (rows_per_page == 0) ? 0 : group.page;

					// Get columns
					var columns = meta_value.columns;

					// Get number of columns
					var column_count = columns.length;

					// Check rows element exists
					if(
						(group.rows === null) ||
						(typeof(group.rows) !== 'object')
					) {
						group.rows = [];
					}

					// Create blank row
					var rows = group.rows;

					// New data
					switch(meta_key_type_sub) {

						case 'conditional' :

							var data = [$.WS_Form.this.language('conditional_label_default'), JSON.stringify($.WS_Form.this.conditional_new())];
							break;

						case 'action' :

							var data = [$.WS_Form.this.language('action_label_default'), JSON.stringify($.WS_Form.this.action_new())];
							break;

						default :

							var data = new Array(column_count).join('.').split('.');
					}

					var row = {

						'id':	$.WS_Form.this.data_grid_row_next_id(meta_value),
						'data':	data
					}

					// Push to data
					rows.push(row);
					var row_count = rows.length;

					// Get last page index
					var pages = (rows_per_page == 0) ? 1 : Math.ceil(row_count / rows_per_page);
					var page_last = (pages - 1);

					// If last page is not the current page, go to the last page
					if(page != page_last) {

						// Save new page
						$.WS_Form.this.data_grid_group_page_set(group, group_index, page_last, object, object_id, meta_key, function() {

							// Refresh data grid
							element.render(read_only, row_count - 1);
						});

					} else {

						// Refresh data grid
						element.render(read_only, row_count - 1);
					}

					// Open row
					if(meta_key_type_sub !== false) {

						// Get the newly added edit icon
						var obj = $('[data-action="wsf-data-grid-' + meta_key_type_sub + '-edit"]', obj).last();

						// Open edit row
						$.WS_Form.this.data_grid_row_open(obj, meta_key_type_sub, meta_value);
					}
				});

				// Rows - Delete
				$('[data-action="wsf-data-grid-row-delete"]', obj).on('click', function() {

					// Get row object
					var row_obj = $(this).closest('tr');

					var buttons = [

						{label:$.WS_Form.this.language('cancel'), action:'wsf-cancel'},
						{label:$.WS_Form.this.language('delete'), action:'wsf-confirm', class:'wsf-button-danger'}
					];

					var row_delete = function() {

						// Get group
						var group_index = row_obj.closest('.wsf-data-grid-group').attr('data-group-index');
						var group = meta_value.groups[group_index];

						// Get row offset
						var page = (rows_per_page == 0) ? 0 : group.page;
						var rows_per_page = meta_value.rows_per_page;
						var row_offset = (page * rows_per_page);
						var row_index = row_offset + row_obj.index();

						// Delete row
						delete group.rows[row_index];

						// Remove empty elements after delete
						group.rows = $.WS_Form.this.array_remove_empty(group.rows);

						// Refresh data grid
						element.render();
					}

					if(meta_key_type_sub !== false) {

						$.WS_Form.this.popover($.WS_Form.this.language('confirm_' + meta_key_type_sub + '_delete'), buttons, row_obj, row_delete);

					} else {

						row_delete();
					}

					row_obj.siblings('tr.wsf-ui-cancel').removeClass('wsf-ui-cancel');
				});

				// Bulk action
				$('[data-action="wsf-data-grid-bulk-action"]', obj).on('click', function() {

					// Get bulk action
					var bulk_action = $(this).siblings('select').first().val();

					// Get bulk action rows
					var bulk_action_rows = $(this).closest('.wsf-data-grid-group').find('input[data-action="wsf-data-grid-row-select"]:checked', obj).closest('tr');

					// Get group
					var group_index = $(this).closest('.wsf-data-grid-group').attr('data-group-index');
					var group = meta_value.groups[group_index];

					// Get row offset
					var page = group.page;
					var rows_per_page = meta_value.rows_per_page;
					var row_offset = (page * rows_per_page);

					// Bulk action row IDs
					var bulk_action_row_ids = [];

					switch(bulk_action) {

						case 'delete' :

							// Delete array indexes
							bulk_action_rows.each(function() {

								var row_index = row_offset + $(this).index();
								delete group.rows[row_index];
							});

							// Remove empty elements after delete
							group.rows = $.WS_Form.this.array_remove_empty(group.rows);

							break;

						case 'default' :
						case 'disabled' :
						case 'required' :
						case 'hidden' :

							// Set values
							bulk_action_rows.each(function() {

								var row_index = row_offset + $(this).index();
								group.rows[row_index][bulk_action] = 'on';
								bulk_action_row_ids.push(row_index);
							});

							break;

						case 'default_off' :
						case 'disabled_off' :
						case 'required_off' :
						case 'hidden_off' :

							bulk_action = bulk_action.replace('_off', '');

							// Set values
							bulk_action_rows.each(function() {

								var row_index = row_offset + $(this).index();
								delete group.rows[row_index][bulk_action];
								bulk_action_row_ids.push(row_index);
							});

							break;
					}

					// Refresh data grid
					element.render(false, false, bulk_action_row_ids);
				});

				// Bulk action - Select all / no rows
				$('[data-action="wsf-data-grid-row-select-all"]', obj).on('change', function() {

					var table = $(this).closest('table');
					var checked = $(this).is(':checked');

					if(checked) {

						$('input[data-action="wsf-data-grid-row-select"]', table).prop('checked', true);

					} else {

						$('input[data-action="wsf-data-grid-row-select"]', table).prop('checked', false);
					}

					$.WS_Form.this.data_grid_bulk_action_button(obj, object, object_id, meta_key);
				});

				// Bulk action - Select rows
				$('[data-action="wsf-data-grid-row-select"]', obj).on('change', function() {

					$.WS_Form.this.data_grid_bulk_action_button(obj, object, object_id, meta_key);
				});

				// Multiple
				obj.closest('.wsf-sidebar').find('[data-meta-key="multiple"]').on('change', function() {

					$.WS_Form.this.data_grid_bulk_action_button(obj, object, object_id, meta_key, true, element);
				});

				// Initial render
				$.WS_Form.this.data_grid_bulk_action_button(obj, object, object_id, meta_key);
			}

			// Update data mask fields
			$.WS_Form.this.data_grid_update_mask_row_lookups(object, object_id, meta_key);

			// Mark as initialized
			obj.addClass('wsf-data-grid-initialized');
		}

		// Render
		element.render();

		// Should data grid be hidden?
		var object = obj.attr('data-object');
		var object_id = obj.attr('data-id');
		var meta_key = obj.attr('data-meta-key');
		var meta_key_config = $.WS_Form.meta_keys[meta_key];
		var data_source = (typeof(meta_key_config['data_source']) !== 'undefined') ? meta_key_config['data_source'] : false;

		if(data_source) {

			// Get object data
			var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
			if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

			// Render HTML
			var data_source_id = $.WS_Form.this.get_data_source_id(object_data);

			if(data_source_id != '') {

				$('.wsf-data-grid-groups', obj).hide();
			}
		}
	}

	// Data grid - Optimize
	$.WS_Form.prototype.data_grid_optimize = function(meta_value) {

		// Initial checks
		if(
			(meta_value === null) ||
			(typeof(meta_value) !== 'object') ||
			(typeof(meta_value.groups) !== 'object')
		) {
			return meta_value;
		}

		var groups = meta_value.groups;

		// Process groups
		for(var group_index in groups) {

			if(!groups.hasOwnProperty(group_index)) { continue; }

			var group = groups[group_index];

			// Check group
			if(
				(group === null) ||
				(typeof(group) !== 'object') ||
				(typeof(group.rows) !== 'object')
			) {
				delete meta_value.groups[group_index];

				continue
			}

			var rows = group.rows;

			// Process rows
			for(var row_index in rows) {

				if(!rows.hasOwnProperty(row_index)) { continue; }

				var row = rows[row_index];

				// Check row
				if(
					(row === null) ||
					(typeof(row) !== 'object') ||
					(typeof(row.data) !== 'object')
				) {
					delete meta_value.groups[group_index].rows[row_index];

					continue
				}

				// Delete default if blank
				if(
					(typeof(row.default) !== 'undefined') &&
					(row.default == '')
				) {
					delete meta_value.groups[group_index].rows[row_index].default;
				}

				// Delete required if blank
				if(
					(typeof(row.required) !== 'undefined') &&
					(row.required == '')
				) {
					delete meta_value.groups[group_index].rows[row_index].required;
				}

				// Delete hidden if blank
				if(
					(typeof(row.hidden) !== 'undefined') &&
					(row.hidden == '')
				) {
					delete meta_value.groups[group_index].rows[row_index].hidden;
				}

				// Delete disabled if blank
				if(
					(typeof(row.disabled) !== 'undefined') &&
					(row.disabled == '')
				) {
					delete meta_value.groups[group_index].rows[row_index].disabled;
				}
			}

			// Remove empty rows
			meta_value.groups[group_index].rows = $.WS_Form.this.array_remove_empty(meta_value.groups[group_index].rows);
		}

		// Remove empty gruoups
		meta_value.groups = $.WS_Form.this.array_remove_empty(meta_value.groups);

		return meta_value;
	}

	// Data source - Get data_source_id
	$.WS_Form.prototype.get_data_source_id = function(object_data) {

		// Get data source ID from object data
		var data_source_id = $.WS_Form.this.get_object_meta_value(object_data, 'data_source_id', '');

		// Check data source ID
		if(typeof($.WS_Form.data_sources[data_source_id]) === 'undefined') { data_source_id = ''; }

		return data_source_id;
	}

	// Data source - Render
	$.WS_Form.prototype.data_source_render = function(obj, element) {

		// Get data grid attributes
		var object = obj.attr('data-object');
		var object_id = obj.attr('data-id');
		var meta_key = obj.attr('data-meta-key');
		var meta_key_config = $.WS_Form.meta_keys[meta_key];
		var data_source = (typeof(meta_key_config['data_source']) !== 'undefined') ? meta_key_config['data_source'] : false;

		// Get object data
		var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
		if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

		// Get current data grid data
		var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, '', true);

		// Render HTML
		var data_source_id = $.WS_Form.this.get_data_source_id(object_data);

		// Get data source meta
		var data_source_meta = $.WS_Form.data_sources[data_source_id];

		// Checks
		if(
			(typeof(data_source_meta.fieldsets) === 'undefined') ||
			(typeof(data_source_meta.fieldsets[data_source_id]) === 'undefined') ||
			(typeof(data_source_meta.fieldsets[data_source_id].meta_keys) === 'undefined')
		) {

			// No data source
			$('#wsf-data-source-meta', obj).html('');

			// Show data grid
			$('.wsf-data-grid-groups', obj).show();

			return;
		}

		// Run through each meta key
		for(var meta_key_index in data_source_meta.fieldsets[data_source_id].meta_keys) {

			if(!data_source_meta.fieldsets[data_source_id].meta_keys.hasOwnProperty(meta_key_index)) { continue; }

			var meta_key = data_source_meta.fieldsets[data_source_id].meta_keys[meta_key_index];

			// Check meta key exists
			if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { continue; }

			// Check if meta key exists in object data, if it does, don't add it
			if(typeof(object_data.meta[meta_key]) !== 'undefined') { continue; }

			// Read meta key config to get default value
			var meta_key_config = $.WS_Form.meta_keys[meta_key];

			// Check for default value
			var value = (typeof(meta_key_config['default']) !== 'undefined') ? meta_key_config['default'] : '';

			// Add to object_data
			object_data.meta[meta_key] = value;
		}

		// Build data source HTML
		var sidebar_html_return = this.sidebar_html(object, object_id, object_data, data_source_meta, false, false, true, false, false);

		// Set HTML
		$('#wsf-data-source-meta', obj).html(sidebar_html_return.html);

		// On change
		$('[data-meta-key="data_source_id"]', obj).on('change', function() {

			var data_source_id = $(this).val();

			$.WS_Form.this.object_data_update_by_meta_key(object, object_data, 'data_source_id');
			obj.removeAttr('data-sidebar-conditions-init');
			$.WS_Form.this.data_source_render(obj, element);

			if(data_source_id != '') {

				// Hide data grid if a data source is selected
				$('.wsf-data-grid-groups', obj).hide();

			} else {

				// Re-render
				element.render();
			}

			// Clear last api error
			$.WS_Form.this.data_source_last_api_error_clear(object_data);
		});

		// On get data
		$('[data-meta-key="data_source_get"]').on('click', function() {

			$.WS_Form.this.data_source_get(obj);
		});

		// Initialize data source
		this.sidebar_inits(sidebar_html_return.inits, obj, obj, object_data);
	}

	// Data source - Clear last API error
	$.WS_Form.prototype.data_source_last_api_error_clear = function(object_data) {

		// Check if there was an error
		var last_api_error = $.WS_Form.this.get_object_meta_value(object_data, 'data_source_last_api_error', '');
		if(last_api_error !== '') {

			// Make API call to clear last API error
			$.WS_Form.this.api_call('field/' + object_data.id + '/last-api-error/clear/', 'POST', false, function() {

				// Remove warning icon
				$('.wsf-field-wrapper[data-id="' + $.WS_Form.this.esc_selector(object_data.id) + '"] .wsf-data-source-error').remove();

				// Loader off
				$.WS_Form.this.loader_off();
			});
		}
	}

	// Data source - Get
	$.WS_Form.prototype.data_source_get = function(obj, page, status_bar) {

		if(typeof(page) === 'undefined') { page = 1; }

		// Get data grid attributes
		var object = obj.attr('data-object');
		var object_id = obj.attr('data-id');
		var meta_key = obj.attr('data-meta-key');
		var meta_key_config = $.WS_Form.meta_keys[meta_key];
		var data_source = (typeof(meta_key_config['data_source']) !== 'undefined') ? meta_key_config['data_source'] : false;

		// Get object data
		var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
		if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

		// Get current data grid data
		var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, '', true);

		// Render HTML
		var data_source_id = $.WS_Form.this.get_data_source_id(object_data);

		// Get data source meta
		var data_source_meta = $.WS_Form.data_sources[data_source_id];

		// Data source not set
		if(!data_source_id) { return; }

		// Save mask row lookups
		var mask_row_lookups = ['meta_key_value', 'meta_key_label', 'meta_key_price', 'meta_key_parse_variable'];
		for(var mask_row_lookup_index in mask_row_lookups) {

			if(!mask_row_lookups.hasOwnProperty(mask_row_lookup_index)) { continue; }

			var mask_row_lookup = mask_row_lookups[mask_row_lookup_index];

			var mask_row_lookup_meta_key = (typeof(meta_key_config[mask_row_lookup]) !== 'undefined') ? meta_key_config[mask_row_lookup] : false;

			if(mask_row_lookup_meta_key !== false) {

				this.object_data_update_by_meta_key(object, object_data, mask_row_lookup_meta_key);
			}
		}

		// Get data source meta
		var data_source_meta = $.WS_Form.data_sources[data_source_id];

		// Checks
		if(
			(typeof(data_source_meta.endpoint_get) === 'undefined') ||
			(typeof(data_source_meta.fieldsets) === 'undefined') ||
			(typeof(data_source_meta.fieldsets[data_source_id]) === 'undefined') ||
			(typeof(data_source_meta.fieldsets[data_source_id].meta_keys) === 'undefined')
		) {

			return;
		}

		// Page through response
		var max_num_pages = 1;

		// Build API parameters
		var params = {

			field_id: 	object_id,
			meta_key: 	meta_key,
			meta_value: meta_value,
			page: 		page
		};

		for(var meta_key_index in data_source_meta.fieldsets[data_source_id].meta_keys) {

			if(!data_source_meta.fieldsets[data_source_id].meta_keys.hasOwnProperty(meta_key_index)) { continue; }

			var meta_key_single = data_source_meta.fieldsets[data_source_id].meta_keys[meta_key_index];

			this.object_data_update_by_meta_key(object, object_data, meta_key_single);

			// Check if meta key exists in object data, if it does, don't add it
			if(typeof(object_data.meta[meta_key_single]) !== 'undefined') {

				params[meta_key_single] = object_data.meta[meta_key_single];
			}
		}

		// Update data grid
		this.object_data_update_by_meta_key(object, object_data, meta_key);

		// Set status bar
		if(typeof(status_bar) === 'undefined') {

			// Create status bar for this file
			var status_bar = new this.upload_status_bar(this, obj, true, false, false)

			// Populate status_bar
			status_bar.populate(data_source_meta.label_retrieving);

			// Set initial progress
			status_bar.set_progress(0);

			// Show upload window
			$('h1', obj).hide();
			$('.wsf-data-grid-upload-csv-window', obj).show();

			// Disable button
			$('[data-meta-key="data_source_get"]', obj).prop('disabled', true);
		}

		// Show data grid
		$('.wsf-data-grid-groups', obj).show();

		// Loader on
		$.WS_Form.this.loader_on();

		// Retrieve data
		this.api_call(data_source_meta.endpoint_get, 'POST', params, function(response) {

			if(typeof(response.meta_value) !== 'object') { $.WS_Form.this.error('data_grid_data_source_error'); }

			// Process page
			var max_num_pages = (typeof(response.max_num_pages) !== 'undefined') ? response.max_num_pages : 1;

			// Set status
			var progress = (max_num_pages > 1) ? Math.round((page / (max_num_pages - 1)) * 100) : 100;
			status_bar.set_progress(progress);

			if(page === 1) {

				// Write to meta data
				$.WS_Form.this.object_data_scratch.meta[meta_key] = response.meta_value;

			} else {

				// Append group data
				for(var group_index in response.meta_value.groups) {

					if(!response.meta_value.groups.hasOwnProperty(group_index)) { continue; }

					var group = response.meta_value.groups[group_index];

					if(typeof(group.rows) === 'undefined') { continue; }

					for(var row_index in response.meta_value.groups[group_index].rows) {

						if(!response.meta_value.groups[group_index].rows.hasOwnProperty(row_index)) { continue; }

						var row = response.meta_value.groups[group_index].rows[row_index];

						$.WS_Form.this.object_data_scratch.meta[meta_key].groups[group_index].rows.push(row);
					}
				}
			}

			if(page < max_num_pages) {

				// Retrieve next page
				$.WS_Form.this.data_source_get(obj, ++page, status_bar);

			} else {

				// Check for meta keys (Used to set column selections)
				if(typeof(response.meta_keys) === 'object') {

					for(var meta_key_single in response.meta_keys) {

						if(!response.meta_keys.hasOwnProperty(meta_key_single)) { continue; }

						$.WS_Form.this.object_data_scratch.meta[meta_key_single] = response.meta_keys[meta_key_single];
					}
				}

				// Update data mask fields
				$.WS_Form.this.data_grid_update_mask_row_lookups(object, object_id, meta_key);

				// Deselect data source?
				var deselect_data_source_id = (typeof(response.deselect_data_source_id) !== 'undefined') ? response.deselect_data_source_id : '';
				if(deselect_data_source_id) {

					$.WS_Form.this.object_data_scratch.meta['data_source_id'] = '';
				}

				// Set initial progress
				status_bar.set_progress(100);

				// Render data grid
				setTimeout(function() {

					obj.closest('.wsf-data-grid')[0].render(!deselect_data_source_id);

				}, 200);

				// Clear last api error
				$.WS_Form.this.data_source_last_api_error_clear(object_data);

				// Loader off
				$.WS_Form.this.loader_off();
			}

		}, function(data) {

			// Show error message
			if(typeof(data.error_message) !== 'undefined') { $.WS_Form.this.data_source_error(object_data, data); }

			// Render data grid
			obj.closest('.wsf-data-grid')[0].render(true);

			// Hide data grid if a data source is selected
			$('.wsf-data-grid-groups', obj).hide();

			// Enable button
			$('[data-meta-key="data_source_get"]', obj).prop('disabled', false);

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// Data source - Error
	$.WS_Form.prototype.data_source_error = function(field, last_api_error) {

		// Build data source error
		var error_message_array = [];
		error_message_array.push(this.language('data_grid_data_source_error_last_field', field.label + ' (' + this.language('data_grid_data_source_error_last_field_id', field.id) + ')'));
		error_message_array.push(this.language('data_grid_data_source_error_last_source', last_api_error.data_source_label));
		error_message_array.push(this.language('data_grid_data_source_error_last_date', last_api_error.date));
		error_message_array.push(this.language('data_grid_data_source_error_last_error', last_api_error.error_message));
		var last_api_error_message = error_message_array.join('<br />');

		// Show error
		this.error('data_grid_data_source_error_last', last_api_error_message);
	}

	// Data grid - Clear rows (Keep columns for mapping purposes)
	$.WS_Form.prototype.data_grid_clear_rows = function(meta_key) {

		// Get default value as fallback
		var meta_key_config = $.WS_Form.meta_keys[meta_key];
		if(typeof(meta_key_config.default) === 'undefined') { return; }

		// Get current value
		var meta_value = this.get_object_meta_value(this.object_data_scratch, meta_key, $.extend(true, {}, meta_key_config.default));

		// Clear rows
		if(
			(typeof(meta_value.groups) === 'object') &&
			(typeof(meta_value.groups[0]) === 'object') &&
			(typeof(meta_value.groups[0].rows) === 'object')
		) {

			// Run through each group and reset the rows
			for(var group_index in meta_value.groups) {

				meta_value.groups[group_index].rows = [];
			}
		}

		// Set meta value
		this.set_object_meta_value(this.object_data_scratch, meta_key, meta_value);
	}

	// Data grid - Open row
	$.WS_Form.prototype.data_grid_row_open = function(obj_edit, row_type, meta_value) {

		// Delete row if it exists
		if($('.wsf-hidden-table-row').length) {

			// Enable row sorting
			$('[data-action="wsf-data-grid-row-sort"]').removeClass('wsf-ui-cancel');

			// Save
			switch(row_type) {

				case 'action' :

					this.action_save();

					break;

				case 'conditional' :

					this.conditional_save();

					// Reset conditional highlight row index
					this.conditional_highlight_row_index = false;

					// Render conditional logic icons
					this.conditional_icons_build(true, this.object_data_scratch);

					break;
			}

			// Remove old row
			$('.wsf-hidden-table-row').remove();

			// If user is click on same row we just closed, then return
			var same_row_type = obj_edit.hasClass('wsf-data-grid-row-open');

			// Remove open class
			$('.wsf-data-grid-row-open').removeClass('wsf-data-grid-row-open');

			if(same_row_type) { return; }
		}

		// Disable row sorting
		$('[data-action="wsf-data-grid-row-sort"]').addClass('wsf-ui-cancel');

		obj_edit.addClass('wsf-data-grid-row-open');

		// Get group
		var group_index = obj_edit.closest('.wsf-data-grid-group').attr('data-group-index');
		var group = meta_value.groups[group_index];

		var rows = group.rows;

		// Get row offset
		var page = group.page;
		var rows_per_page = meta_value.rows_per_page;
		var row_offset = (page * rows_per_page);

		// Get data position
		var row_index = row_offset + (obj_edit.closest('tr').index());

		if(row_type === 'conditional') {

			// Reset conditional highlight row index
			this.conditional_highlight_row_index = row_index;

			// Render conditional logic icons
			this.conditional_icons_build(true, this.object_data_scratch);
		}
		var row = rows[row_index];

		// Create row
		var closest_tr = obj_edit.closest('tr');
		var td_count = $('td', closest_tr).length;
		var row_html = '<tr id="wsf-' + row_type + '-tr" class="wsf-hidden-table-row"><td></td><td></td><td class="wsf-hidden-table-cell"><div></div></td><td></td><td></td><td></td></tr>';
		$(row_html).insertAfter(closest_tr).css('display', 'table-row');

		// Get data
		switch(row_type) {

			case 'action' :

				this.action_data = row.data;
				this.action_render_from_data();
				break;

			case 'conditional' :

				this.conditional_data = row.data;
				this.conditional_render_from_data();
				break;
		}
	}

	// Data grid - Clone row
	$.WS_Form.prototype.data_grid_row_clone = function(obj_clone, obj, object, object_id, element, meta_value, meta_key_type_sub) {

		// Run through each of the field meta and set it
		for(var key in this.object_meta_cache) {

			if(!this.object_meta_cache.hasOwnProperty(key)) { continue; }

			// Get meta_key
			var meta_key = this.object_meta_cache[key]['meta_key'];

			// Update object data
			this.object_data_update_by_meta_key(object, this.object_data_scratch, meta_key);
		}

		// Get row object
		var row_obj = obj_clone.closest('tr');

		// Get group
		var group_obj = row_obj.closest('.wsf-data-grid-group');
		var group_index = group_obj.attr('data-group-index');
		var group = meta_value.groups[group_index];

		// Close any open rows
		$('.wsf-hidden-table-row', group_obj).remove();

		// Get row offset
		var page = (rows_per_page == 0) ? 0 : group.page;
		var rows = group.rows;
		var rows_per_page = meta_value.rows_per_page;
		var row_offset = (page * rows_per_page);
		var row_index = row_offset + row_obj.index();

		// Get row we are cloning
		var row = $.extend(true, {}, rows[row_index]);
		row.id = $.WS_Form.this.data_grid_row_next_id(meta_value);
		row.data[0] = row.data[0] + ' (Copy)';

		// Add duplicate row beneath current row
		rows.splice(row_index + 1, 0, row);

		// Reset indexes and IDs
		var rows_temp = [];
		for (var row_key in rows) {

			rows_temp.push(rows[row_key]);
		}
		rows = rows_temp;	// Write back to meta_value

		// Set new row index
		row_index++;

		// Get row count
		var row_count = rows.length;

		// Get last page index
		var pages = (rows_per_page == 0) ? 1 : Math.ceil(row_count / rows_per_page);
		var page_last = (pages - 1);

		// If last page is not the current page, go to the last page
		if(page != page_last) {

			// Save new page
			this.data_grid_group_page_set(group, group_index, page_last, object, object_id, meta_key, function() {

				// Refresh data grid
				element.render(false, row_index);
			});

		} else {

			// Refresh data grid
			element.render(false, row_index);
		}

		// Open row
		if(meta_key_type_sub !== false) {

			// Get the newly added edit icon
			var obj_edit = $('tr[data-index="' + this.esc_selector(row_index) + '"] td [data-action="wsf-data-grid-' + this.esc_selector(meta_key_type_sub) + '-edit"]', obj).first();

			// Open edit row
			this.data_grid_row_open(obj_edit, meta_key_type_sub, meta_value);
		}
	}

	// Data grid - Expand
	$.WS_Form.prototype.data_grid_row_next_id = function(meta_value) {

		var row_id = 0;
		var groups = meta_value['groups'];

		for(var group_index in groups) {

			if(!groups.hasOwnProperty(group_index)) { continue; }

			var group = groups[group_index];

			var rows = group['rows'];

			for(var row_index in rows) {

				if(!rows.hasOwnProperty(row_index)) { continue; }

				var row = rows[row_index];

				// Error checking
				if(row === null) { continue; }
				if(typeof(row.id) == 'undefined') { this.error('error_data_grid_row_id'); }

				// Get row ID
				var id = parseInt(row.id, 10);

				// If higher row ID found, set row_id
				if(id > row_id) { row_id = id; }
			}
		}

		// Increment row ID
		row_id++;

		return row_id;
	}

	// Data grid - Uploader
	$.WS_Form.prototype.data_grid_upload_csv = function(object, object_id, meta_key, files, obj, error_callback) {

		// Hide H1
		$('h1', obj).hide();

		if(files.length == 0) {

			if(typeof(error_callback) === 'function') { error_callback(); }

			return false;
		}

		// Create form data
		var form_data = new FormData();
		form_data.append('id', this.form_id);
		form_data.append('file', files[0]);
		form_data.append('meta_key', meta_key);
		form_data.append(ws_form_settings.wsf_nonce_field_name, ws_form_settings.wsf_nonce);

		// Create status bar for this file
		var status_bar = new this.upload_status_bar(this, obj)

		// Populate status_bar
		status_bar.populate(files[0].name, files[0].size);

		// Send file to the server using AJAX
		this.data_grid_upload_csv_ajax(object, object_id, form_data, status_bar, obj, function(response) {

			// If successful, run complete
			$.WS_Form.this.data_grid_upload_csv_complete(object, object_id, meta_key, response.data, obj);
		});
	}

	// Date grid - Uploader complete
	$.WS_Form.prototype.data_grid_upload_csv_complete = function(object, object_id, meta_key, meta_value, obj) {

		// Optimize data
		meta_value = $.WS_Form.this.data_grid_optimize(meta_value);

		// Write to meta data
		$.WS_Form.this.object_data_scratch.meta[meta_key] = meta_value;

		// Update data mask fields (With reset - Resets the meta_values to 0 on the data_grid_fields)
		$.WS_Form.this.data_grid_update_mask_row_lookups(object, object_id, meta_key, true);

		// Render data grid
		obj.closest('.wsf-data-grid')[0].render();
	}

	// Data grid - Download CSV
	$.WS_Form.prototype.data_grid_download_csv = function(object, object_id, meta_key, group_index, use_scratch) {

		if(typeof(use_scratch) === 'undefined') { use_scratch = true; }

		// Should we use the scratch data? If so we need to send it to the API request
		if(use_scratch) {

			// Build meta_value_json
			var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
			if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

			// Get current data grid data
			var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, false);
			if(meta_value === false) { $.WS_Form.this.error('error_object_meta_value'); }
			var meta_value_json = encodeURIComponent(JSON.stringify(meta_value));
		}

		// Build downloader
		var downloader_html = '<form id="wsf-data-grid-downloader" action="' + this.esc_attr(ws_form_settings.url_ajax + object + '/' + object_id) + '/download/csv" method="post">';

		downloader_html += '<input type="hidden" name="form_id" value="' + this.esc_attr(this.form_id) + '" />';
		downloader_html += '<input type="hidden" name="meta_key" value="' + this.esc_attr(meta_key) + '" />';
		downloader_html += '<input type="hidden" name="group_index" value="' + this.esc_attr(group_index) + '" />';
		downloader_html += '<input type="hidden" name="_wpnonce" value="' + this.esc_attr(ws_form_settings.x_wp_nonce) + '" />';
		downloader_html += '<input type="hidden" name="' + this.esc_attr(ws_form_settings.wsf_nonce_field_name) + '" value="' + this.esc_attr(ws_form_settings.wsf_nonce) + '" />';

		if(use_scratch) {

			downloader_html += '<input type="hidden" name="meta_value" value="' + this.esc_attr(meta_value_json) + '" />';
		}

		downloader_html += '</form>';

		// Inject into body
		var downloader = $('body').append(downloader_html);

		// Submit
		$('#wsf-data-grid-downloader').trigger('submit');

		// Remove
		$('#wsf-data-grid-downloader').remove();
	}

	// Convert an object to a CSV encoded string
	$.WS_Form.prototype.object_to_csv = function(object) {

		var data_array = new Array;

		for (var o in object) {

			var cell = object[o];

			if(cell.indexOf(',') != -1) {

				cell = this.replace_all(cell, /"/g, '""');
				cell = '"' + cell + '"';
			}

			data_array.push(cell);
		}

		return data_array.join(',') + '\r\n';
	}

	// Data grid - Uploaded CSV - AJAX request
	$.WS_Form.prototype.data_grid_upload_csv_ajax = function(object, object_id, form_data, status_bar, obj, success_callback) {

		var url = ws_form_settings.url_ajax + object + '/' + object_id + '/upload/csv';

		var jqXHR = $.ajax({

			beforeSend: function(xhr) {

				xhr.setRequestHeader('X-WP-Nonce', ws_form_settings.x_wp_nonce);
			},

			xhr: function() {

				// Upload progress
				var xhrobj = $.ajaxSettings.xhr();
				if (xhrobj.upload) {

					xhrobj.upload.addEventListener('progress', function(e) {

						var percent = 0;
						var position = e.loaded || e.position;
						var total = e.total;
						if (e.lengthComputable) {
							percent = Math.ceil(position / total * 100);
						}

						status_bar.set_progress(percent);

					}, false);
				}

				return xhrobj;
			},

			url: url,
			type: 'POST',
			contentType: false,
			processData: false,
			cache: false,
			data: form_data,

			success: function(response) {

				// Set progress bar to 100%
				status_bar.set_progress(100);

				// Call success script
				if(typeof(success_callback) === 'function') { success_callback(response); }
			},

			error: function(response) {

				// Hide drag and drop zone
				obj.hide();

				// Process error
				$.WS_Form.this.api_call_error_handler(response, url, error_callback);
			}
		});

		status_bar.set_abort(jqXHR);
	}

	// Data grid - Uploader status bar
	$.WS_Form.prototype.upload_status_bar = function(ws_this, obj, render_file_name, render_file_size, render_abort) {

		if(typeof(render_file_name) === 'undefined') { render_file_name = true; }
		if(typeof(render_file_size) === 'undefined') { render_file_size = true; }
		if(typeof(render_abort) === 'undefined') { render_abort = true; }

		// Build status bar
		this.status_bar = $('<div class="wsf-upload-status-bar"></div>');
		this.progress_bar = $('<div class="wsf-upload-status-bar-progress"><div></div></div>').appendTo(this.status_bar);
		if(render_file_name) {
			this.file_name = $('<div class="wsf-upload-status-bar-file-name"></div>').appendTo(this.status_bar);
		} else {
			this.file_name = false;
		}
		if(render_file_size) {
			this.file_size = $('<div class="wsf-upload-status-bar-file-size"></div>').appendTo(this.status_bar);
		} else {
			this.file_size = false;
		}
		if(render_abort) {
			this.abort = $('<div class="wsf-upload-status-bar-abort">Abort</div>').appendTo(this.status_bar);
		} else {
			this.abort = false;
		}
		$('.wsf-uploads', obj).append(this.status_bar);

		// Methods
		this.set_progress = function(progress) {

			var progress_bar_width = (progress * this.progress_bar.width()) / 100;

			var progress_text = ((progress < 100) ? (progress + '%') : ws_this.language('importing'));

			this.progress_bar.find('div').animate({width:progress_bar_width}, 10).html('<span>' + progress_text + '</span>');

			if(
				(this.abort !== false) &&
				(parseInt(progress, 10) >= 100)
			) {
				
				this.abort.hide();
			}
		}

		// Populate file information
		this.populate = function(file_name, file_size) {

			if(this.file_name !== false) {

				this.file_name.html(file_name);
			}

			if(this.file_size !== false) {

				var size_string ='';
				var size_kb = file_size / 1024;

				if(parseInt(size_kb, 10) > 1024) {

					var size_mb = size_kb / 1024;
					size_string = size_mb.toFixed(2) + ' MB';

				} else {

					size_string = size_kb.toFixed(2) + ' KB';
				}

				this.file_size.html(size_string);
			}
		}

		this.set_abort = function(jqxhr) {

			if(this.abort === false) { return; }

			var sb = this.status_bar;
			this.abort.on('click', function() {

				jqxhr.abort();
				sb.hide();
			});
		}
	}

	// Data grid - Update data mask fields (Updates the select dropdowns with the column headings)
	$.WS_Form.prototype.data_grid_update_mask_row_lookups = function(object, object_id, meta_key, meta_value_reset) {

		// Only run this on fields
		if(object !== 'field') { return false; }

		// Check for meta_value_reset
		if(typeof(meta_value_reset) === 'undefined') { meta_value_reset = false; }

		// Get object data of scratch field
		var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
		if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

		// Get data grid data of original old
		var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, false);
		if(meta_value === false) { $.WS_Form.this.error('error_object_meta_value'); }

		// Get columns
		var columns = meta_value.columns;

		// Get field type
		var field_type = object_data.type;

		// Get field type config
		var field_type_config = $.WS_Form.field_type_cache[field_type];

		if(typeof(field_type_config.mask_row_lookups) !== 'undefined') {

			// Read data mask fields
			var mask_row_lookups = field_type_config.mask_row_lookups;

			// Include auto group select
			mask_row_lookups.push('wsf-data-grid-auto-group');

			// Run through each data mask field
			for(var data_mask_field_key in mask_row_lookups) {

				if(!mask_row_lookups.hasOwnProperty(data_mask_field_key)) { continue; }
				if(typeof(mask_row_lookups[data_mask_field_key]) === 'function') { continue; }

				// Get data mask field ID
				var data_mask_field = mask_row_lookups[data_mask_field_key];

				// Is this the auto group select?
				var auto_group = (data_mask_field == 'wsf-data-grid-auto-group');

				// Get current value
				var meta_value = auto_group ? '' : this.get_object_meta_value(object_data, data_mask_field, 0);

				// Get object for data mask field
				var data_mask_field_object = $('#wsf-sidebar-' + this.esc_selector(object) + ' [' + (auto_group ? 'data-action' : 'data-meta-key') + '="' + this.esc_selector(data_mask_field) + '"]');

				// Clear contents of data mask field
				data_mask_field_object.empty();

				// Build options array
				var options_array = [];
				var meta_value_found = false;
				for(var column_key in columns) {

					if(!columns.hasOwnProperty(column_key)) { continue; }
					if(typeof(columns[column_key]) === 'function') { continue; }

					var column = columns[column_key];

					if(parseInt(column.id) == parseInt(meta_value, 10)) { meta_value_found = true; }

					options_array.push({

						'value': column.id,
						'text': column.label
					});
				}

				// If the current meta value (column ID) is no longer found (e.g. Column deleted) reset meta_value to default
				if(!meta_value_found || meta_value_reset) {

					// Get default value
					if(typeof($.WS_Form.meta_keys[data_mask_field]) !== 'undefined') {

						var meta_key_config = $.WS_Form.meta_keys[data_mask_field];
						var default_value = (typeof(meta_key_config.default) !== 'undefined') ? meta_key_config.default : 0;

						// If default value is larger than the number of available columns, set it to zero
						if(default_value > column_key) { default_value = '0'; }

					} else {

						default_value = '0';
					}

					this.set_object_meta_value(object_data, data_mask_field, default_value);
					meta_value = default_value;
				}

				// Sort array
				options_array.sort(function(a, b) {

					if(a.text < b.text) return -1;
					if(a.text > b.text) return 1;

					return 0;
				});

				// Built options HTML
				var options_html = auto_group ? '<option value="">' + this.language('data_grid_group_auto_group_select') + "</option>\n" : '';
				for(var options_array_key in options_array) {

					if(!options_array.hasOwnProperty(options_array_key)) { continue; }
					if(typeof(options_array[options_array_key]) === 'function') { continue; }

					var option_value = options_array[options_array_key].value;
					var option_selected = auto_group ? '' : ((parseInt(option_value, 10) == parseInt(meta_value, 10)) ? ' selected' : '');
					var option_text = options_array[options_array_key].text;
					options_html += '<option value="' + this.esc_attr(option_value) + '"' + option_selected + '>' + this.esc_html(option_text) + "</option>\n";
				}

				data_mask_field_object.html(options_html);
			}
		}
	}

	// Data grid - Bulk action button
	$.WS_Form.prototype.data_grid_bulk_action_button = function(obj, object, object_id, meta_key, default_check, element) {

		if(typeof(default_check) === 'undefined') { default_check = false; }
		if(typeof(element) === 'undefined') { element = false; }

		// Determine whether you can multi-select default values
		var has_multiple = obj.closest('.wsf-sidebar').find('[data-meta-key="multiple"]').length;
		var multiple = obj.closest('.wsf-sidebar').find('[data-meta-key="multiple"]:checked').length;

		// Run through each group
		var groups_obj = $('.wsf-data-grid-group', obj).each(function() {

			// If multiple defaults are not supported, then ensure only on row is set as default
			if(!multiple && default_check) {

				// Get object data of scratch field
				var object_data = $.WS_Form.this.get_object_data(object, object_id, true);
				if(object_data === false) { $.WS_Form.this.error('error_object_data'); }

				// Get data grid data of scratch
				var meta_value = $.WS_Form.this.get_object_meta_value(object_data, meta_key, false);
				if(meta_value === false) { $.WS_Form.this.error('error_object_meta_value'); }

				// Get group
				var group_index = $(this).attr('data-group-index');
				var group = meta_value.groups[group_index];
				var rows = group.rows;

				// Check to see if number of default rows > 1
				var default_row_count = 0;
				for(var row_index in rows) {

					if(!rows.hasOwnProperty(row_index)) { continue; }

					if(rows[row_index]['default'] != '') { default_row_count++; }
					if(default_row_count > 1) { break; }
				}

				// More than one default row
				if(default_row_count > 0) {

					var default_row_count = 0;
					for(var row_index in rows) {

						if(!rows.hasOwnProperty(row_index)) { continue; }

						if(rows[row_index]['default'] != '') {

							default_row_count++;

							// Remove any default rows after the first default
							if(default_row_count > 1) { rows[row_index]['default'] = ''; }
						}
					}

					if(element !== false) { element.render(); }
				}
			}

			// Get all checked bulk action checkboxes
			var checked = $('[data-action="wsf-data-grid-row-select"]:checked', $(this));

			// Get bulk action button object
			var bulk_action_button = $('[data-action="wsf-data-grid-bulk-action"]', $(this));

			// Get bulk action select object
			var bulk_action_select = bulk_action_button.siblings('select');

			// Set button disabled attribute
			bulk_action_button.attr('disabled', (checked.length == 0));

			// Set select attribute
			bulk_action_select.attr('disabled', (checked.length == 0));

			// If multiple is not enabled and more than one bulk action checkbox is checked, disable the default option in bulk edit options
			var default_disabled = (has_multiple && !multiple && (checked.length > 1));
			if(default_disabled) {

				$('option[value="default"]', bulk_action_select).prop('disabled', true);

			} else {

				$('option[value="default"]', bulk_action_select).prop('disabled', false);
			}
		});
	}

	// Data grid - Group page set
	$.WS_Form.prototype.data_grid_group_page_set = function(group, group_index, page_new, object, object_id, meta_key, complete) {

		// Set new page on scratch
		group.page = page_new;

		// Get object data of original field
		var object_data_old = $.WS_Form.this.get_object_data(object, object_id);
		if(object_data_old === false) { $.WS_Form.this.error('error_object_data'); }

		// Get data grid data of original old (Will be false if no existing conditional exists)
		var meta_value_old = $.WS_Form.this.get_object_meta_value(object_data_old, meta_key, false);
		if(
			(meta_value_old !== false) &&
			(typeof(meta_value_old.groups) !== 'undefined') &&
			(typeof(meta_value_old.groups[group_index]) !== 'undefined')
		) {

			// Loader on
			$.WS_Form.this.loader_on();

			// Save new page to original field
			meta_value_old.groups[group_index].page = page_new;

			// Build parameters
			var params = {

				form_id: $.WS_Form.this.form_id
			};

			// Object data
			params[object] = object_data_old;
			params[object]['history_suppress'] = 'on';

			// Call AJAX request
			$.WS_Form.this.api_call(object + '/' + object_id + '/put/', 'POST', params, function(response) {

				// Loader off
				$.WS_Form.this.loader_off();
			});
		}

		// Run complete function
		if(typeof(complete) !== 'undefined') { complete(); }
	}

	// Save rows per page
	$.WS_Form.prototype.data_grid_group_rows_per_page_set = function(meta_value, rows_per_page, object, object_id, meta_key, complete) {

		// Saves rows per page
		meta_value.rows_per_page = rows_per_page;

		// Get object data of original field
		var object_data_old = $.WS_Form.this.get_object_data(object, object_id);
		if(object_data_old === false) { $.WS_Form.this.error('error_object_data'); }

		// Get data grid data of original old
		var meta_value_old = $.WS_Form.this.get_object_meta_value(object_data_old, meta_key, false);
		if(meta_value_old === false) { $.WS_Form.this.error('error_object_meta_value'); }

		// Loader on
		$.WS_Form.this.loader_on();

		// Save new rows_per_page to original field
		meta_value_old.rows_per_page = rows_per_page;

		// Set all group pages to 0
		for(var group_index in meta_value.groups) {

			if(!meta_value.groups.hasOwnProperty(group_index)) { continue; }

			meta_value.groups[group_index].page = 0;
		}
		for(var group_index in meta_value_old.groups) {

			if(!meta_value_old.groups.hasOwnProperty(group_index)) { continue; }

			meta_value_old.groups[group_index].page = 0;
		}

		// Build parameters
		var params = {

			form_id: $.WS_Form.this.form_id
		};

		// Object data
		params[object] = object_data_old;
		params[object]['history_suppress'] = 'on';

		// Run complete function
		if(typeof(complete) !== 'undefined') { complete(); }

		// Call AJAX request
		$.WS_Form.this.api_call(object + '/' + object_id + '/put/', 'POST', params, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	// HTML Editor - Init
	$.WS_Form.prototype.sidebar_html_editor_init = function(obj_sidebar_inner) {

		if(
			(typeof(wp) !== 'undefined') &&
			(typeof(wp.codeEditor) !== 'undefined')
		) {

			// Kill existing
			$('.CodeMirror', obj_sidebar_inner).remove();

			// Initialize CodeMirror
			$('.wsf-sidebar-tabs-panel:visible [data-html-editor="true"], [id^=wsf-action] [data-html-editor="true"], .wsf-html-editor [data-html-editor="true"]', obj_sidebar_inner).each(function() {

				var mode = $(this).attr('data-html-editor-mode') ? $(this).attr('data-html-editor-mode') : 'htmlmixed';

				var args = {

					mode: mode
				};

				if(mode === 'javascript') {

					args.lint = {options: {esversion: 2021}};
				}

				var code_editor_settings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
				code_editor_settings.codemirror = _.extend({}, code_editor_settings.codemirror, args);

				wp.codeEditor.initialize($(this).attr('id'), code_editor_settings);
			});

			$('.CodeMirror').each(function() {

				var code_editor = $(this)[0].CodeMirror;
				code_editor.on('keyup', function (cm) {

					var code_editor_value = cm.getValue();
					var code_editor_textarea = cm.getTextArea();
					$(code_editor_textarea).val(code_editor_value).trigger('input');
				});
			});

		} else {

			// Initialize textarea (CodeMirror not supported)
			$(document).delegate('[data-html-editor="true"]', 'keydown input', function(e) {

				var keyCode = e.keyCode || e.which;

				if(keyCode === 9) {

					e.preventDefault();

					var start = this.selectionStart;
					var end = this.selectionEnd;

					$(this).val($(this).val().substring(0, start) + "\t" + $(this).val().substring(end));
					this.selectionStart = this.selectionEnd = start + 1;
				}
			});
		}
	}
	// Conditional - Init
	$.WS_Form.prototype.conditional_init = function(conditional_obj) {

		// Process IF
		for(var group_index in this.conditional.if) {

			if(!this.conditional.if.hasOwnProperty(group_index)) { continue; }

			var group = this.conditional.if[group_index];

			// Error check group
			if(typeof(group.conditions) === 'undefined') { continue; }

			// Process conditions
			for(var condition_index in group.conditions) {

				if(!group.conditions.hasOwnProperty(condition_index)) { continue; }

				var condition = group.conditions[condition_index];

				var condition_id = condition.id;

				var condition_obj = $('.wsf-conditional-condition[data-id="' + this.esc_selector(condition_id) + '"]');

				this.conditional_condition(condition_obj, condition, condition_id);
			}
		}

		// Then
		this.conditional_thenelse_init('then');

		// Else
		this.conditional_thenelse_init('else');

		// Events

		// Add condition
		$('[data-action="wsf-conditional-condition-add"]', conditional_obj).on('click', function() {

			var group_index = $(this).attr('data-id');

			// Add condition
			$.WS_Form.this.conditional.if[group_index].conditions.push($.WS_Form.this.conditional_condition_new());

			// Render conditional
			$.WS_Form.this.conditional_render();

			// Save
			$.WS_Form.this.conditional_save();
		});

		// Group - Add
		$('[data-action="wsf-conditional-group-add"]', conditional_obj).on('click', function() {

			// Add group
			$.WS_Form.this.conditional.if.push($.WS_Form.this.conditional_group_new());

			// Render conditional
			$.WS_Form.this.conditional_render();

			// Save
			$.WS_Form.this.conditional_save();
		});

		// Group - Logic previous
		$('.wsf-conditional-group [data-attribute="logic_previous"]', conditional_obj).on('change', function() {

			var group_index = parseInt($(this).closest('.wsf-conditional-group').attr('data-id'), 10);

			$.WS_Form.this.conditional.if[group_index].logic_previous = $(this).val();

			// Save
			$.WS_Form.this.conditional_save();
		});

		// Group - Delete
		$('[data-action="wsf-conditional-group-delete"]', conditional_obj).on('click', function() {

			var group_index = parseInt($(this).closest('.wsf-conditional-group').attr('data-id'), 10);

			// Delete group
			delete($.WS_Form.this.conditional.if[group_index]);

			// Remove empty elements after delete
			$.WS_Form.this.conditional.if = $.WS_Form.this.array_remove_empty($.WS_Form.this.conditional.if);

			// Render conditional
			$.WS_Form.this.conditional_render();

			// Save
			$.WS_Form.this.conditional_save();

			// Render conditional logic icons
			$.WS_Form.this.conditional_icons_build(true, $.WS_Form.this.object_data_scratch);
		});

		// Then - Add
		$('[data-action="wsf-conditional-then-add"]', conditional_obj).on('click', function() {

			$.WS_Form.this.conditional_thenelse_add('then');
		});

		// Else - Add
		$('[data-action="wsf-conditional-else-add"]', conditional_obj).on('click', function() {

			$.WS_Form.this.conditional_thenelse_add('else');
		});

		// Sortable - Groups
		$('.wsf-conditional-if > ul', conditional_obj).sortable({

			items: "li.wsf-conditional-group",
			cursor: 'move',
			containment: 'parent',
			handle: '[data-action="wsf-group-sort"]',
			axis: 'y',

			sort: function(e, ui) {

				var container = $(this),
				placeholder = container.children('.ui-sortable-placeholder:first');

				var helpHeight = ui.helper.outerHeight(),
				helpTop = ui.position.top,
				helpBottom = helpTop + helpHeight;

				container.children().each(function () {
					var item = $(this);

					if(!item.hasClass('ui-sortable-helper') && !item.hasClass('ui-sortable-placeholder')) {
						var itemHeight = item.outerHeight(),
						itemTop = item.position().top,
						itemBottom = itemTop + itemHeight;

						if((helpTop > itemTop) && (helpTop < itemBottom)) {
							var tolerance = Math.min(helpHeight, itemHeight) / 2,
							distance = helpTop - itemTop;

							if(distance < tolerance) {
								placeholder.insertBefore(item);
								container.sortable('refreshPositions');
								return false;
							}

						} else if((helpBottom < itemBottom) && (helpBottom > itemTop)) {
							var tolerance = Math.min(helpHeight, itemHeight) / 2,
							distance = itemBottom - helpBottom;

							if(distance < tolerance) {
								placeholder.insertAfter(item);
								container.sortable('refreshPositions');
								return false;
							}
						}
					}
				});
			},

			start: function(e, ui) {		

				// Get object being sorted
				var obj = ui.placeholder;
				var height = ui.helper.height();
				var width = ui.helper.width();
				var styles = [
					'height: ' + height + 'px',
					'width: ' + width + 'px'
				].join(';');		

				// Apply styles
				obj.attr('style', styles);

				// Remember condition index
				$.WS_Form.conditional_group_index_old = ui.helper.index();
			},

			stop: function(e, ui) {

				// Group index
				var group_index_old = $.WS_Form.conditional_group_index_old;
				var group_index_new = ui.item.index();

				// Skip if nothing has changed
				if(group_index_old == group_index_new) { return true; }

				// Move meta data index
				var groups = $.WS_Form.this.conditional.if;

				if(group_index_new >= groups.length) {

					var k = group_index_new - groups.length;

					while ((k--) + 1) {
						groups.push(undefined);
					}
				}
				groups.splice(group_index_new, 0, groups.splice(group_index_old, 1)[0]);

				// Save
				$.WS_Form.this.conditional_save();
			}
		});

		// Sortable - Conditions
		$('.wsf-conditional-group > ul', conditional_obj).sortable({

			items: 'li.wsf-conditional-condition',
			cursor: 'move',
			containment: 'parent',
			handle: '[data-action="wsf-conditional-condition-sort"]',
			axis: 'y',
			connectWith: '.wsf-conditional-group > ul',

			sort: function(e, ui) {

				var container = $(this),
				placeholder = container.children('.ui-sortable-placeholder:first');

				var helpHeight = ui.helper.outerHeight(),
				helpTop = ui.position.top,
				helpBottom = helpTop + helpHeight;

				container.children().each(function () {

					var item = $(this);

					if(!item.hasClass('ui-sortable-helper') && !item.hasClass('ui-sortable-placeholder')) {

						var itemHeight = item.outerHeight(),
						itemTop = item.position().top,
						itemBottom = itemTop + itemHeight;

						if((helpTop > itemTop) && (helpTop < itemBottom)) {

							var tolerance = Math.min(helpHeight, itemHeight) / 2,
							distance = helpTop - itemTop;

							if(distance < tolerance) {
								placeholder.insertBefore(item);
								container.sortable('refreshPositions');
								return false;
							}

						} else if((helpBottom < itemBottom) && (helpBottom > itemTop)) {

							var tolerance = Math.min(helpHeight, itemHeight) / 2,
							distance = itemBottom - helpBottom;

							if(distance < tolerance) {
								placeholder.insertAfter(item);
								container.sortable('refreshPositions');
								return false;
							}
						}
					}
				});
			},

			start: function(e, ui) {

				// Get object being sorted
				var obj = ui.placeholder;
				var height = ui.helper.height();
				var width = ui.helper.width();
				var styles = [
					'height: ' + height + 'px',
					'width: ' + width + 'px'
				].join(';');

				// Apply styles
				obj.attr('style', styles);

				// Remember group index
				$.WS_Form.conditional_group_index_old = parseInt($(this).closest('.wsf-conditional-group').attr('data-id'), 10);

				// Remember condition index
				$.WS_Form.conditional_condition_index_old = ui.helper.index();
			},

			stop: function(e, ui) {

				// Group index
				var group_index_old = $.WS_Form.conditional_group_index_old;
				var group_index_new = parseInt(ui.item.closest('.wsf-conditional-group').attr('data-id'), 10);

				// Condition index
				var condition_index_old = $.WS_Form.conditional_condition_index_old;
				var condition_index_new = ui.item.index();

				// Skip if nothing has changed
				if((group_index_old == group_index_new) && (condition_index_old == condition_index_new)) { return true; }

				// Store condition
				var condition = $.extend(true, {}, $.WS_Form.this.conditional.if[group_index_old].conditions[condition_index_old]);

				// Remove condition from old array
				delete $.WS_Form.this.conditional.if[group_index_old].conditions[condition_index_old];

				// Remove empty array elements
				$.WS_Form.this.conditional.if[group_index_old].conditions = $.WS_Form.this.array_remove_empty($.WS_Form.this.conditional.if[group_index_old].conditions);

				// Get new conditions array
				var conditions_new = $.WS_Form.this.conditional.if[group_index_new].conditions;

				// Inject condition
				conditions_new.splice(condition_index_new, 0, condition);

				// Save
				$.WS_Form.this.conditional_save();
			}
		});

		// Sortable - IF
		this.conditional_thenelse_sortable('then', conditional_obj);

		// Sortable - ELSE
		this.conditional_thenelse_sortable('else', conditional_obj);
	}

	// Conditional - Render from data
	$.WS_Form.prototype.conditional_render_from_data = function() {

		// Validate conditions_data
		try {

			this.conditional = JSON.parse(this.conditional_data[1]);

		} catch(e) {

			this.conditional = this.conditional_new();
		}

		// Check base elements exist
		if(typeof(this.conditional.if) === 'undefined') { this.error('error_conditional_if'); }
		if(typeof(this.conditional.then) === 'undefined') { this.error('error_conditional_then'); }
		if(typeof(this.conditional.else) === 'undefined') { this.error('error_conditional_else'); }

		// Render
		this.conditional_render();
	}

	// Conditional - Blank
	$.WS_Form.prototype.conditional_new = function() {

		// Build a new conditional
		var conditional = {if: [], then: [], else: []};

		// Push first if group
		conditional.if.push(this.conditional_group_new());

		return conditional;
	}

	// Conditional - Render
	$.WS_Form.prototype.conditional_render = function() {

		// Save scroll position
		var conditional_inner_obj = $('#wsf-sidebar-conditional .wsf-sidebar-inner');
		var conditional_scroll_position = conditional_inner_obj.scrollTop();

		// Get conditional object
		var conditional_obj = $('#wsf-conditional-tr > td > div');

		// Show conditional object
		conditional_obj.show();

		// Get conditional HTML
		var conditional_html = this.conditional_html();

		// Add conditional HTML to object
		conditional_obj.html(conditional_html);

		// Initialize conditional
		this.conditional_init(conditional_obj);

		// Set scroll position to previous position to avoid jumping to the top
		conditional_inner_obj.scrollTop(conditional_scroll_position);
	}

	// Conditional - HTML
	$.WS_Form.prototype.conditional_html = function() {

		var conditional_settings = $.WS_Form.settings_form.conditional;
		var conditional_settings_logic_previous = conditional_settings.logic_previous;

		// Error check conditional settings
		if(typeof($.WS_Form.settings_form.conditional) === 'undefined') { this.error('error_conditional_settings'); }

		// Conditional
		var conditional_html = '<ul>';

		// IF
		conditional_html += '<li class="wsf-conditional-if">';

		// IF - Heading
		conditional_html += '<div class="wsf-conditional-title"><h3>' + this.language('conditional_if') + '</h3></div>';

		// Groups
		conditional_html += '<ul>';

		var logic_previous = false;
		for(var group_index in this.conditional.if) {

			if(!this.conditional.if.hasOwnProperty(group_index)) { continue; }

			var group = this.conditional.if[group_index];

			// Error check logic previous
			if(typeof(group['logic_previous']) === 'undefined') { this.error('error_conditional_logic_previous_group'); continue; }

			// Group
			conditional_html += '<li class="wsf-conditional-group" data-id="' + this.esc_attr(group_index) + '">';

			// Group - Logic previous
			conditional_html += '<div class="wsf-conditional-group-logic-previous" data-id="' + this.esc_attr(group_index) + '">';
			conditional_html += '<select data-attribute="logic_previous" class="wsf-field wsf-field-small">';

			for(var logic_key in conditional_settings_logic_previous) {

				if(!conditional_settings_logic_previous.hasOwnProperty(logic_key)) { continue; }

				var logic = conditional_settings_logic_previous[logic_key];
				conditional_html += '<option value="' + this.esc_attr(logic_key) + '"' + ((logic_key == group['logic_previous']) ? ' selected' : '') + '>' + this.esc_html(logic['text']) + '</option>';
			}

			conditional_html += '</select>';

			// /Group - Logic previous
			conditional_html += '</div>';

			// Error check group
			if(typeof(group.conditions) === 'undefined') { continue; }

			// Process conditions

			// Conditions
			conditional_html += '<ul>';

			for(var condition_index in group.conditions) {

				if(!group.conditions.hasOwnProperty(condition_index)) { continue; }

				var condition = group.conditions[condition_index];

				var condition_id = condition.id;

				// Store to cache
				this.object_cache['condition'][condition_id] = condition;

				// Error check condition
				if(typeof(condition['object']) === 'undefined') { this.error('error_conditional_object'); continue; }
				if(typeof(condition['object_id']) === 'undefined') { this.error('error_conditional_object_id'); continue; }
				if(typeof(condition['logic']) === 'undefined') { this.error('error_conditional_logic'); continue; }
				if(typeof(condition['logic_previous']) === 'undefined') { this.error('error_conditional_logic_previous'); continue; }

				// Condition
				conditional_html += '<li class="wsf-conditional-condition" data-id="' + this.esc_attr(condition_id) + '"></li>';
			}

			// Sort handle
			conditional_html += '<li data-action="wsf-group-sort"' + this.tooltip(this.language('conditional_group_sort'), 'right') + '>' + this.svg('sort') + '</li>';

			// Button - Group - Delete
			conditional_html += '<li data-action="wsf-conditional-group-delete"' + this.tooltip(this.language('conditional_group_delete'), 'left') + ' data-id="' + this.esc_attr(group_index) + '">' + this.svg('delete-circle') + '</li>';

			// Button - Condition - Add
			conditional_html += '<li data-action="wsf-conditional-condition-add"' + this.tooltip(this.language('conditional_condition_add'), 'right') + ' data-id="' + this.esc_attr(group_index) + '">' + this.svg('plus-circle') + '</li>';

			// /Conditons
			conditional_html += '</ul>';

			// /Group
			conditional_html += '</li>';
		}

		// Button - Group - Add
		conditional_html += '<li data-action="wsf-conditional-group-add"' + this.tooltip(this.language('conditional_group_add'), 'right') + ' data-id="' + this.esc_attr(group_index) + '">' + this.svg('plus-circle') + '</li>';

		// /Groups
		conditional_html += '</ul>';

		// /IF
		conditional_html += '</li>';

		// THEN
		conditional_html += this.conditional_thenelse_html_base('then');

		// ELSE
		conditional_html += this.conditional_thenelse_html_base('else');

		// /Conditional
		conditional_html += '</ul>';

		return conditional_html;
	}

	// Conditional - Save
	$.WS_Form.prototype.conditional_save = function() {

		if(!$('#wsf-conditional-tr').length) { return false; }

		// Check if sidebar is locked
		if(this.sidebar_lock_count) { return false; }

		this.conditional_data[1] = JSON.stringify(this.conditional);
	}

	// Conditional - Group - New
	$.WS_Form.prototype.conditional_group_new = function() {

		return {

			conditions: [this.conditional_condition_new()],
			logic_previous: '||'	// Never changes
		};
	}

	// Conditional - Condition
	$.WS_Form.prototype.conditional_condition = function(condition_obj, condition, condition_id) {

		// Get conditional condition HTML
		var conditional_condition_html = this.conditional_condition_html(condition_obj, condition);

		// Button - Condition - Delete
		conditional_condition_html += '<li data-action="wsf-conditional-condition-delete"' + this.tooltip(this.language('conditional_condition_delete'), 'top-right') + '>' + this.svg('delete-circle') + '</li>';

		// /ul
		conditional_condition_html += '</ul>';

		// Set HTML of condition
		condition_obj.html(conditional_condition_html);

		// Object ID event handler
		this.conditional_objects_events('condition', condition_obj);

		// Object row ID event handler
		this.conditional_object_rows_events('condition', condition_obj);

		// Value
		this.conditional_value_events('condition', condition_obj);

		// Case sensitive
		this.conditional_case_sensitive_events('condition', condition_obj);

		// Multiple
		this.conditional_multiple_events('condition', condition_obj);

		// Logic event handler
		$('[data-attribute="logic"]', condition_obj).on('change', function() {

			// Get condition ID
			var condition_id = condition_obj.attr('data-id');

			// Get condition array from cache
			var condition = $.WS_Form.this.object_cache['condition'][condition_id];

			// Set logic
			condition.logic = $(this).val();
			condition.value = '';

			// Re-initialize this condition
			$.WS_Form.this.conditional_condition(condition_obj, condition, condition_id);

			// Save
			$.WS_Form.this.conditional_save();
		});

		// Logic previous event handler
		$('[data-attribute="condition_logic_previous"]', condition_obj).on('change', function() {

			// Get condition ID
			var condition_id = condition_obj.attr('data-id');

			// Get condition array from cache
			var condition = $.WS_Form.this.object_cache['condition'][condition_id];

			// Set object row ID
			condition.logic_previous = $(this).val();

			// Save
			$.WS_Form.this.conditional_save();
		});

		// Delete event handler
		$('[data-action="wsf-conditional-condition-delete"]', condition_obj).on('click', function() {

			var condition_id = parseInt($(this).closest('.wsf-conditional-condition').attr('data-id'), 10);

			// Delete condition
			for(var group_index in $.WS_Form.this.conditional.if) {

				if(!$.WS_Form.this.conditional.if.hasOwnProperty(group_index)) { continue; }

				var group = $.WS_Form.this.conditional.if[group_index];

				// Error check group
				if(typeof(group.conditions) === 'undefined') { continue; }

				// Process conditions
				for(var condition_index in group.conditions) {

					if(!group.conditions.hasOwnProperty(condition_index)) { continue; }

					var condition = group.conditions[condition_index];

					if(condition_id == condition.id) {

						// Delete array element
						delete(group.conditions[condition_index]);

						// Remove empty elements after delete
						group.conditions = $.WS_Form.this.array_remove_empty(group.conditions);
					}
				}
			}

			// Render conditional
			$.WS_Form.this.conditional_render();

			// Save
			$.WS_Form.this.conditional_save();

			// Show conditional icons
			$.WS_Form.this.conditional_icons_build(true, $.WS_Form.this.object_data_scratch);
		});
	}

	// Conditional - Condition HTML
	$.WS_Form.prototype.conditional_condition_html = function(condition_obj, condition) {

		var condition_id = condition['id'];
		var condition_object = condition['object'];
		var condition_object_id = condition['object_id'];
		var condition_object_row_id = this.get_object_row_id(condition);	// Array of integers
		var condition_logic = condition['logic'];
		var condition_logic_previous = condition['logic_previous'];
		var condition_value = (typeof(condition['value']) === 'undefined') ? false : condition['value'];
		var condition_case_sensitive = (typeof(condition['case_sensitive']) === 'undefined') ? false : condition['case_sensitive'];
		var condition_multiple = (typeof(condition['multiple']) === 'undefined') ? false : condition['multiple'];

		// Render logic previous
		var condition_html = this.conditional_logic_previous_html('condition', condition_logic_previous);

		// Render sort
		condition_html += this.conditional_sort_html('condition');

		// Render object IDs
		var conditional_objects = this.conditional_objects_html('condition', condition_id, condition_object, condition_object_id);
		condition_html += conditional_objects.html;
		if(!conditional_objects.found) { return condition_html; }

		// Render logic
		var conditional_logic = this.conditional_logic_html('condition', condition_object, condition_object_id, condition_logic);
		condition_html += conditional_logic.html;
		var logic_key_found = conditional_logic.logic_key_found;
		if(!logic_key_found) { return condition_html; }

		// Render object row IDs
		if(conditional_logic.rows) {

			var conditional_object_rows = this.conditional_object_rows_html('condition', logic_key_found, condition_object, condition_object_id, condition_object_row_id, condition_multiple, condition_id);
			if(conditional_object_rows.has_data_grid_fields) {

				condition_html += conditional_object_rows.html;
				if(!conditional_object_rows.found) { return condition_html; }
			}
		}

		// Render value
		condition_html += this.conditional_value_html('condition', logic_key_found, condition_object, condition_object_id, condition_value, condition_case_sensitive, condition_id);

		return condition_html;
	}

	// Conditional - Condition - New
	$.WS_Form.prototype.conditional_condition_new = function() {

		var condition_id = this.conditional_condition_id_next();

		return {id: condition_id, object: '', object_id: '', object_row_id: '', logic: '', value: '', case_sensitive: true, logic_previous: '||'};
	}

	// Conditional - Condition - New
	$.WS_Form.prototype.conditional_condition_id_next = function() {

		var condition_id = 0;

		// Run through each group
		for(var group_index in this.conditional.if) {

			if(!this.conditional.if.hasOwnProperty(group_index)) { continue; }

			var conditions = this.conditional.if[group_index].conditions;

			// Run through each condition
			for(var condition_index in conditions) {

				if(!conditions.hasOwnProperty(condition_index)) { continue; }

				var condition = conditions[condition_index];

				if(condition.id > condition_id) { condition_id = condition.id; }
			}
		}

		// Increment condition ID
		condition_id++;

		return condition_id;
	}

	// Conditional - Then/Else
	$.WS_Form.prototype.conditional_thenelse = function(type, thenelse_obj, thenelse, thenelse_id) {

		// Get conditional condition HTML
		var conditional_thenelse_html = this.conditional_thenelse_html(type, thenelse_obj, thenelse);

		// Button - Condition - Delete
		conditional_thenelse_html += '<li data-action="wsf-conditional-' + type + '-delete"' + this.tooltip(this.language('conditional_' + type + '_delete'), 'top-right') + '>' + this.svg('delete-circle') + '</li>';

		// /ul
		conditional_thenelse_html += '</ul>';

		// Set HTML of condition
		thenelse_obj.html(conditional_thenelse_html);

		// Object ID event handler
		this.conditional_objects_events(type, thenelse_obj);

		// Object row ID event handler
		this.conditional_object_rows_events(type, thenelse_obj);

		// Value event handler
		this.conditional_value_events(type, thenelse_obj);

		// Multiple
		this.conditional_multiple_events(type, thenelse_obj);

		// Action event handler
		$('[data-attribute="object_action"]', thenelse_obj).on('change', function() {

			// Get object ID
			var object_id = thenelse_obj.attr('data-id');

			// Get object array from cache
			var object = $.WS_Form.this.object_cache[type][object_id];

			// Set action
			var action_old = object.action;
			var action = $(this).val();
			object.action = action;
			object.object_row_id = '';
			object.value = '';

			// Get auto else disable count in IF condition
			var auto_else_disabled_count = $('.wsf-conditional-if select[data-attribute="logic"] option[data-auto-else-disabled]:selected', $(this).closest('#wsf-conditional-tr')).length;

			// Add auto else?
			if(
				(type == 'then') &&
				(action != '') &&
				(action_old == '') &&
				(auto_else_disabled_count == 0)
			) {

				$.WS_Form.this.conditional_then_auto_else(object);
			}

			// Re-initialize this then/else
			$.WS_Form.this.conditional_thenelse(type, thenelse_obj, thenelse, thenelse_id);

			// Save
			$.WS_Form.this.conditional_save();

			// Show conditional icons
			$.WS_Form.this.conditional_icons_build(true, $.WS_Form.this.object_data_scratch);
		});

		// Delete event handler
		$('[data-action="wsf-conditional-' + type + '-delete"]', thenelse_obj).on('click', function() {

			$.WS_Form.this.conditional_thenelse_delete(type, $(this));

			// Show conditional icons
			$.WS_Form.this.conditional_icons_build(true, $.WS_Form.this.object_data_scratch);
		});
	}

	// Conditional - Then auto else
	$.WS_Form.prototype.conditional_then_auto_else = function(then_single, keyup_event) {

		if(typeof(keyup_event) === 'undefined') { keyup_event = false; }

		// Check for else action
		var conditional_settings = $.WS_Form.settings_form.conditional;
		var action_configuration = conditional_settings['objects'][then_single.object]['action'][then_single.action];

		// If this action is not configured for auto_else, then return false
		if(typeof(action_configuration.auto_else) === 'undefined') { return false; }

		var action_else = action_configuration.auto_else;

		// Check to see if an else action exists for this object, object_id, object_row_id
		var else_found = false;
		for(var else_index in $.WS_Form.this.conditional.else) {

			if(!$.WS_Form.this.conditional.else.hasOwnProperty(else_index)) { continue; }

			var else_single = $.WS_Form.this.conditional.else[else_index];

			// Try to find matching object / object_id / action
			else_found |= (
				(else_single.object == then_single.object) &&
				(else_single.object_id == then_single.object_id) &&
				(else_single.action == action_else)
				);
		}

		// If we are updating a value as a string and the else cannot be found, then return false
		if(keyup_event && !else_found) { return false; }

		// Add
		if(!else_found) {

			var else_single = $.WS_Form.this.conditional_thenelse_new('else');
			else_single.object = then_single.object;
			else_single.object_id = then_single.object_id;
			else_single.action = action_else;
		}

		// Get value from array
		if(typeof(action_configuration.values) === 'object' && !keyup_event) {

			for(var value_index in action_configuration.values) {

				if(!action_configuration.values.hasOwnProperty(value_index)) { continue; }

				var value = action_configuration.values[value_index];
				if(value.value == then_single.value) {

					if(typeof(value.auto_else) !== 'undefined') {

						else_single.value = value.auto_else;
					}
				}
			}
		}

		// Update else value
		if(action_configuration.values === true && else_found && keyup_event) {

			// Set value
			else_single.value = then_single.value;

			// Update else
			var else_single_value_obj = $('.wsf-conditional-else[data-id="' + this.esc_selector(else_single.id) + '"] [data-attribute="value"]');
			else_single_value_obj.val(else_single.value);
		}

		if(!else_found) {

			// Add
			this.conditional['else'].push(else_single);
		}

		if(!keyup_event) {

			// Render
			this.conditional_render();
		}
	}

	// Conditional - Then/Else HTML Base
	$.WS_Form.prototype.conditional_thenelse_html_base = function(type) {

		// Wrapper
		var thenelse_html = '<li class="wsf-conditional-' + type + '-wrapper">';

		// Heading
		thenelse_html += '<div class="wsf-conditional-title"><h3>' + this.language('conditional_' + type) + '</h3></div>';

		// Action
		thenelse_html += '<ul>';
		for(var thenelse_index in this.conditional[type]) {

			if(!this.conditional[type].hasOwnProperty(thenelse_index)) { continue; }

			var thenelse = this.conditional[type][thenelse_index];
			var thenelse_id = thenelse.id;

			// Store to cache
			this.object_cache[type][thenelse_id] = thenelse;

			thenelse_html += '<li class="wsf-conditional-' + type + '" data-id="' + thenelse_id + '"></li>';
		}

		// Button - Then - Add
		thenelse_html += '<li data-action="wsf-conditional-' + type + '-add"' + this.tooltip(this.language('conditional_' + type + '_add'), 'right') + '>' + this.svg('plus-circle') + '</li>';

		thenelse_html += '</ul>';

		// /Wrapper
		thenelse_html += '</li>';

		return thenelse_html;
	}

	// Conditional - Then/Else HTML
	$.WS_Form.prototype.conditional_thenelse_html = function(type, thenelse_obj, thenelse) {

		var thenelse_id = thenelse['id'];
		var thenelse_object = thenelse['object'];
		var thenelse_object_id = thenelse['object_id'];
		var thenelse_object_row_id = this.get_object_row_id(thenelse);	// Array of integers
		var thenelse_action = thenelse['action'];
		var thenelse_value = thenelse['value'];
		var thenelse_multiple = (typeof(thenelse['multiple']) === 'undefined') ? false : thenelse['multiple'];

		var thenelse_html = '<ul class="wsf-list-inline">';

		// Render sort
		thenelse_html += this.conditional_sort_html(type);

		// Render object IDs
		var conditional_objects = this.conditional_objects_html(type, thenelse_id, thenelse_object, thenelse_object_id, true, false, false);
		thenelse_html += conditional_objects.html;
		if(!conditional_objects.found) { return thenelse_html; }

		// Render actions
		var conditional_actions = this.conditional_actions_html(type, thenelse_object, thenelse_object_id, thenelse_action);
		thenelse_html += conditional_actions.html;
		var action_key_found = conditional_actions.found;
		if(!action_key_found) { return thenelse_html; }

		if(conditional_actions.value_row_ids) {

			// Render object row IDs
			var conditional_object_rows = this.conditional_object_rows_html(type, action_key_found, thenelse_object, thenelse_object_id, thenelse_object_row_id, thenelse_multiple, thenelse_id);
			if(conditional_object_rows.has_data_grid_fields) {

				thenelse_html += conditional_object_rows.html;
				if(!conditional_object_rows.found) { return thenelse_html; }
			}
		}

		// Render value
		thenelse_html += this.conditional_value_html(type, action_key_found, thenelse_object, thenelse_object_id, thenelse_value, false, thenelse_id);

		return thenelse_html;
	}

	// Conditional - Then/Else Init
	$.WS_Form.prototype.conditional_thenelse_init = function(type) {

		// Process THEN
		for(var thenelse_index in this.conditional[type]) {

			if(!this.conditional[type].hasOwnProperty(thenelse_index)) { continue; }

			var thenelse = this.conditional[type][thenelse_index];
			var thenelse_id = thenelse.id;

			var thenelse_obj = $('.wsf-conditional-' + type + '[data-id="' + thenelse_id + '"]');

			this.conditional_thenelse(type, thenelse_obj, thenelse, thenelse_id);
		}
	}

	// Conditional - Then/Else Add
	$.WS_Form.prototype.conditional_thenelse_add = function(type) {

		var thenelse = $.WS_Form.this.conditional_thenelse_new(type);

		// Add
		this.conditional[type].push(thenelse);

		// Render conditional
		$.WS_Form.this.conditional_render();

		// Save
		$.WS_Form.this.conditional_save();
	}

	// Conditional - Then/Else Delete
	$.WS_Form.prototype.conditional_thenelse_delete = function(type, obj) {

		var thenelse_id = parseInt(obj.closest('.wsf-conditional-' + type).attr('data-id'), 10);

		// Delete condition
		for(var thenelse_index in this.conditional[type]) {

			if(!this.conditional[type].hasOwnProperty(thenelse_index)) { continue; }

			var thenelse = this.conditional[type][thenelse_index];

			if(thenelse_id == thenelse.id) {

				// Delete array element
				delete(this.conditional[type][thenelse_index]);

				// Remove empty elements after delete
				this.conditional[type] = $.WS_Form.this.array_remove_empty(this.conditional[type]);

				// Break out of for loop
				break;
			}
		}

		// Render conditional
		this.conditional_render();

		// Save
		this.conditional_save();

		// Show conditional icons
		this.conditional_icons_build(true, this.object_data_scratch);
	}

	// Conditional - Then/Else - Sortable
	$.WS_Form.prototype.conditional_thenelse_sortable = function(type, conditional_obj) {

		$('.wsf-conditional-' + type + '-wrapper > ul', conditional_obj).sortable({

			items: 'li.wsf-conditional-' + type,
			cursor: 'move',
			containment: 'parent',
			tolerance: 'pointer',
			handle: '[data-action="wsf-conditional-' + type + '-sort"]',
			axis: 'y',

			sort: function(e, ui) {

				var container = $(this),
				placeholder = container.children('.ui-sortable-placeholder:first');

				var helpHeight = ui.helper.outerHeight(),
				helpTop = ui.position.top,
				helpBottom = helpTop + helpHeight;

				container.children().each(function () {
					var item = $(this);

					if(!item.hasClass('ui-sortable-helper') && !item.hasClass('ui-sortable-placeholder')) {
						var itemHeight = item.outerHeight(),
						itemTop	= item.position().top,
						itemBottom = itemTop + itemHeight;

						if((helpTop > itemTop) && (helpTop < itemBottom)) {
							var tolerance = Math.min(helpHeight, itemHeight) / 2,
							distance = helpTop - itemTop;

							if(distance < tolerance) {
								placeholder.insertBefore(item);
								container.sortable('refreshPositions');
								return false;
							}

						} else if((helpBottom < itemBottom) && (helpBottom > itemTop)) {
							var tolerance = Math.min(helpHeight, itemHeight) / 2,
							distance = itemBottom - helpBottom;

							if(distance < tolerance) {
								placeholder.insertAfter(item);
								container.sortable('refreshPositions');
								return false;
							}
						}
					}
				});
			},

			start: function(e, ui) {

				// Remember condition index
				$.WS_Form.conditional_index_dragged_from = ui.helper.index();
			},

			stop: function(e, ui) {

				// Condition index
				var thenelse_index_old = $.WS_Form.conditional_index_dragged_from;
				var thenelse_index_new = ui.item.index();

				// Skip if nothing has changed
				if(thenelse_index_old == thenelse_index_new) { return true; }

				// Move meta data index
				if (thenelse_index_new >= $.WS_Form.this.conditional[type].length) {

					var k = thenelse_index_new - $.WS_Form.this.conditional[type].length;
					while ((k--) + 1) {
						$.WS_Form.this.conditional[type].push(undefined);
					}
				}
				$.WS_Form.this.conditional[type].splice(thenelse_index_new, 0, $.WS_Form.this.conditional[type].splice(thenelse_index_old, 1)[0]);

				// Save
				$.WS_Form.this.conditional_save();
			}
		});
	}

	// Conditional - Then/Else - New
	$.WS_Form.prototype.conditional_thenelse_new = function(type) {

		var condition_id = this.conditional_thenelse_id_next(type);

		return {id: condition_id, object: '', object_id: '', object_row_id: '', action: '', value: ''};
	}

	// Conditional - Then/Else - Next ID
	$.WS_Form.prototype.conditional_thenelse_id_next = function(type) {

		var thenelse_id = 0;

		// Run through each group
		for(var thenelse_index in this.conditional[type]) {

			if(!this.conditional[type].hasOwnProperty(thenelse_index)) { continue; }

			var thenelse = this.conditional[type][thenelse_index];

			if(thenelse.id > thenelse_id) { thenelse_id = thenelse.id; }
		}

		// Increment then/else ID
		thenelse_id++;

		return thenelse_id;
	}

	// Conditional - Logic previous
	$.WS_Form.prototype.conditional_logic_previous_html = function(type, logic_previous) {

		// Render logic previous
		var conditional_settings = $.WS_Form.settings_form.conditional;
		var conditional_settings_logic_previous = conditional_settings.logic_previous;

		var logic_previous_html = '<div class="wsf-conditional-logic"><select data-attribute="condition_logic_previous" class="wsf-field wsf-field-small">';

		for(var logic_key in conditional_settings_logic_previous) {

			if(!conditional_settings_logic_previous.hasOwnProperty(logic_key)) { continue; }

			var logic = conditional_settings_logic_previous[logic_key];
			logic_previous_html += '<option value="' + this.esc_attr(logic_key) + '"' + ((logic_key == logic_previous) ? ' selected' : '') + '>' + this.esc_html(logic['text']) + '</option>';
		}

		logic_previous_html += '</select></div>';

		logic_previous_html += '<ul class="wsf-list-inline">';

		return logic_previous_html;
	}

	// Conditional - Logic previous
	$.WS_Form.prototype.conditional_sort_html = function(type) {

		return '<li data-action="wsf-conditional-' + type + '-sort"' + this.tooltip(this.language('conditional_' + type + '_sort'), 'top-left') + '>' + this.svg('sort') + '</li>';
	}

	// Conditional - Render objects
	$.WS_Form.prototype.conditional_objects_html = function(type, id, object, object_id, actions, submit, user) {

		if(typeof(actions) === 'undefined') { actions = false; }
		if(typeof(submit) === 'undefined') { submit = true; }
		if(typeof(user) === 'undefined') { user = true; }

		var options = [];

		options.push({optgroup: this.conditional_options_groups(type), 'value': this.form_id, 'text': this.language('form'), 'object': 'form', 'disabled': false});

		if(actions) {

			options = this.conditional_options_actions(options);
		}

		if(submit) {

			options.push({'value': 0, 'text': this.language('submission'), 'object': 'submit', 'disabled': false});
		}

		if(user) {

			options.push({'value': 0, 'text': this.language('user'), 'object': 'user', 'disabled': false});
		}

		var objects_html = '<li><select data-attribute="object_id" class="wsf-field wsf-field-small" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '">';
		objects_html += '<option value="">' + this.language('conditional_' + type + '_select') + '</option>';

		var conditional_options = this.conditional_options(object, object_id, options, type);

		objects_html += conditional_options.html;

		objects_html += '</select></li>';

		return {'found': conditional_options.found, 'html': objects_html};
	}

	// Conditional - Render object rows
	$.WS_Form.prototype.conditional_options_actions = function(options) {

		var action = this.get_object_meta_value(this.form, 'action', false);
		if(
			(action === false) ||
			(typeof(action.groups) === 'undefined') ||
			(typeof(action.groups[0]) === 'undefined') ||
			(typeof(action.groups[0].rows) !== 'object') ||
			(action.groups[0].rows.length == 0)
		) {
			return options;
		}

		var rows = action.groups[0].rows;

		for(var row_index in rows) {

			if(!rows.hasOwnProperty(row_index)) { continue; }

			var row = rows[row_index];
			options.push({'value': row.id, 'text': '- ' + this.language('action') + ': ' + row.data[0], 'object': 'action'});
		}

		return options;
	}

	// Conditional - Render object rows
	$.WS_Form.prototype.conditional_object_rows_html = function(type, type_sub, object, object_id, object_row_id, object_multiple, id) {

 		var conditional_settings = $.WS_Form.settings_form.conditional;
		var conditional_settings_objects = conditional_settings.objects;
		var logic = $.extend(true, [], conditional_settings_objects[object]['logic']);
		var action = $.extend(true, [], conditional_settings_objects[object]['action']);
		var has_data_grid_fields = false;
		var object_row_id_found = false;
		var object_rows_html = '';

		// Check object data
		var field = false;
		switch(object) {

			case 'field' :

				field = this.field_data_cache[object_id];
				break;
		}

		// Value
		switch(type) {

			case 'condition' :

				var data = logic[type_sub];
				var multiple = (typeof(data['multiple']) === 'undefined') ? false : data['multiple'];
				break;

			case 'then' :
			case 'else' :

				var data = action[type_sub];
				var multiple = (typeof(data['multiple']) === 'undefined') ? false : data['multiple'];
				break;
		}

		// Check for field conditional configuration
		if(field !== false) {

			// Get field type meta config
			var field_type = $.WS_Form.field_type_cache[field.type];

			// Are there conditional settings?
			if(typeof(field_type['conditional']) !== 'undefined') {

				// Is a data grid associated with this field type?
				if(typeof(field_type['conditional']['data_grid_fields']) !== 'undefined') {

					has_data_grid_fields = true;

					// Read data grid to use
					var data_grid = field_type['conditional']['data_grid_fields'];

					// Get the meta key for the option text
					var option_text = field_type['conditional']['option_text'];

					// Get field data grid
					var data = this.get_object_meta_value(field, data_grid, false);
					if(data === false) { this.error('error_conditional_data_grid'); }

					// Get column id for the option text
					var option_text_column_id = parseInt(this.get_object_meta_value(field, option_text, false), 10);
					var placeholder_row = this.get_object_meta_value(field, 'placeholder_row', '');

					// Get column index for id
					var columns = data.columns;
					var option_text_column_index = 0;

					for(var column_index in columns) {

						if(!columns.hasOwnProperty(column_index)) { continue; }

						if(parseInt(columns[column_index].id) === option_text_column_id) {

							option_text_column_index = column_index;
							break;
						}
					}

					// Render object row IDs
					object_rows_html += '<li><select data-attribute="object_row_id" class="wsf-field wsf-field-small" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '"' + (object_multiple ? ' multiple size="10"' : '') + '>';
					if(!object_multiple) { object_rows_html += '<option value="">' + this.language('conditional_condition_select_row') + '</option>'; }
					if(placeholder_row != '') {

						var row_id_found = (object_row_id !== false) ? (object_row_id.indexOf(0) !== -1) : false;
						object_row_id_found |= row_id_found;

						object_rows_html += '<option value="0"' + (row_id_found ? ' selected' : '') + '>[' + this.language('conditional_condition_select_placeholder') + ': ' + placeholder_row + ']</option>';
					}

					// Build option data
					var groups = data.groups;
					for(var group_index in groups) {

						if(!groups.hasOwnProperty(group_index)) { continue; }

						// Get group data
						var group = groups[group_index];

						// Optgroup
						var optgroup = (typeof(group.mask_group) !== 'undefined') ? (group.mask_group == 'on') : false;
						if(optgroup) { object_rows_html += '<optgroup label="' + this.esc_attr(group.label) + '">'; }

						// Get rows
						var rows = group.rows;

						for(var row_index in rows) {

							if(!rows.hasOwnProperty(row_index)) { continue; }

							var row = rows[row_index];

							if(row === null) { continue; }

							// Get row data
							var row_data = row.data;

							// Get column data for the option text
							var option_text = row_data[option_text_column_index];

							var row_id_found = (object_row_id !== false) ? (object_row_id.indexOf(parseInt(row.id, 10)) !== -1) : false;
							object_row_id_found |= row_id_found;

							// Build option
							object_rows_html += '<option value="' + this.esc_attr(row.id) + '"' + (row_id_found ? ' selected' : '') + '>' + this.esc_html(option_text) + '</option>';
						}

						if(optgroup) { object_rows_html += '</optgroup>'; }
					}

					object_rows_html += '</select></li>';
				}
			}

			// Multiple (Have to check for radio because checkbox and radio both use value_row_check / value_row_uncheck)
			if(
				multiple &&
				!(
					(field.type === 'radio') &&
					(type !== 'condition')
				)
			) {

				var object_multiple_id = 'wsf-multiple-' + type + '-' + id;
				object_rows_html += '<li><input id="' + this.esc_attr(object_multiple_id) + '" type="checkbox" data-attribute="multiple" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '" class="wsf-field" value="on"' + (object_multiple ? ' checked' : '') + ' /><label for="' + this.esc_attr(object_multiple_id) + '" class="wsf-label">' + this.language('conditional_multiple') + (object_multiple ? '<div class="wsf-multiple-instruction" id="' + this.esc_attr(object_multiple_id) + '-instruction">' + this.language('conditional_multiple_instruction') + '</div></li>' : '');
			}
		}

		return {'has_data_grid_fields': has_data_grid_fields, 'found': object_row_id_found, 'html': object_rows_html};
	}

	// Conditional - Render actions
	$.WS_Form.prototype.conditional_actions_html = function(type, object, object_id, object_action) {

		var conditional_settings = $.WS_Form.settings_form.conditional;
		var conditional_settings_objects = conditional_settings.objects;
		var actions = $.extend(true, [], conditional_settings_objects[object]['action']);
		var action_key_found = false;
		var value_row_ids = false;

		// Check object data
		var field = false;
		switch(object) {

			case 'field' :

			field = this.field_data_cache[object_id];
			break;
		}

		// Check for field conditional configuration
		if(field !== false) {

			// Get field type meta config
			var field_type = $.WS_Form.field_type_cache[field.type];

			// Get data source ID
			var data_source_id = $.WS_Form.this.get_data_source_id(field);
			var has_data_source = (data_source_id !== '');

			// Are there conditional settings?
			if(typeof(field_type['conditional']) !== 'undefined') {

				// Are there certain logics only to include?
				if(typeof(field_type['conditional']['actions_enabled']) !== 'undefined') {

					var actions_enabled = field_type['conditional']['actions_enabled'];

					// Reset actions, build from scratch
					var actions = [];

					for(var action_index in actions_enabled) {

						if(!actions_enabled.hasOwnProperty(action_index)) { continue; }

						var action_enabled = actions_enabled[action_index];

						if(typeof(conditional_settings_objects[object]['action'][action_enabled]) !== 'undefined') {

							actions[action_enabled] = conditional_settings_objects[object]['action'][action_enabled];
						}
					}
				}

				// Are there certain actions only to include?
				if(typeof(field_type['conditional']['actions_disabled']) !== 'undefined') {

					var actions_disabled = field_type['conditional']['actions_disabled'];

					for(var action_index in actions_disabled) {

						if(!actions_disabled.hasOwnProperty(action_index)) { continue; }

						var action_disabled = actions_disabled[action_index];

						if(typeof(actions[action_disabled]) !== 'undefined') {

							delete(actions[action_disabled]);
						}
					}
				}
			}
		}

		// Render actions
		var actions_html = '<li><select data-attribute="object_action" class="wsf-field wsf-field-small">';
		actions_html += '<option value="">' + this.language('conditional_' + type + '_select_action') + '</option>';

		for(var action_key in actions) {

			if(!actions.hasOwnProperty(action_key)) { continue; }

			var action = actions[action_key];

			// Check for hidden
			var hidden = ((typeof(action.hidden) !== 'undefined') && action.hidden);
			if(hidden) { continue; }

			// Check for data source exclusion
			var disabled = (

				(((typeof(action.data_source_exclude) !== 'undefined') ? action.data_source_exclude : false) && has_data_source) ||
				(((typeof(action.loader_exclude) !== 'undefined') ? action.loader_exclude : false) && !this.get_object_meta_value(this.form, 'loader'))
			);

			// Add option
			actions_html += '<option value="' + this.esc_attr(action_key) + '"' + ((action_key == object_action) ? ' selected' : '') + (disabled ? ' disabled' : '') + '>' + this.esc_html(action.text) + '</option>';
			if(action_key == object_action) {

				action_key_found = action_key;
				value_row_ids |= ((typeof(action.value_row_ids) !== 'undefined') ? action.value_row_ids : false);
			}
		}

		actions_html += '</select></li>';

		return {'found': action_key_found, 'value_row_ids': value_row_ids, 'html': actions_html};
	}

	// Conditional - Render logic
	$.WS_Form.prototype.conditional_logic_html = function(type, object, object_id, object_logic) {

		// Logic
		var conditional_settings = $.WS_Form.settings_form.conditional;
		var conditional_settings_logic_previous = conditional_settings.logic_previous;
		var conditional_settings_logic_group = conditional_settings.logic_group;
		var conditional_settings_objects = conditional_settings.objects;
		var logics = $.extend(true, [], conditional_settings_objects[object]['logic']);
		var logic_key_found = false;
		var logic_rows = false;

		// Check object data
		var field = false;
		switch(object) {

			case 'field' :

				field = this.field_data_cache[object_id];
				break;
		}

		// Check for field conditional configuration
		if(field !== false) {

			// Get field type meta config
			var field_type = $.WS_Form.field_type_cache[field.type];

			// Get data source ID
			var data_source_id  = this.get_object_meta_value(field, 'data_source_id', '');
			var has_data_source = (data_source_id !== '');

			// Are there conditional settings?
			if(typeof(field_type['conditional']) !== 'undefined') {

				// Are there certain logics only to include?
				if(typeof(field_type['conditional']['logics_enabled']) !== 'undefined') {

					var logics_enabled = field_type['conditional']['logics_enabled'];

					// Reset logics, build from scratch
					var logics = [];

					for(var logic_index in logics_enabled) {

						if(!logics_enabled.hasOwnProperty(logic_index)) { continue; }

						var logic_enabled = logics_enabled[logic_index];

						if(typeof(conditional_settings_objects[object]['logic'][logic_enabled]) !== 'undefined') {

							logics[logic_enabled] = conditional_settings_objects[object]['logic'][logic_enabled];
						}
					}
				}

				// Are there certain logics only to exclude?
				if(typeof(field_type['conditional']['logics_disabled']) !== 'undefined') {

					var logics_disabled = field_type['conditional']['logics_disabled'];

					for(var logic_index in logics_disabled) {

						if(!logics_disabled.hasOwnProperty(logic_index)) { continue; }

						var logic_disabled = logics_disabled[logic_index];

						if(typeof(logics[logic_disabled]) !== 'undefined') {

							delete(logics[logic_disabled]);
						}
					}
				}
			}
		}

		// Render logics
		var logic_html = '<li><select data-attribute="logic" class="wsf-field wsf-field-small">';
		logic_html += '<option value="">' + this.language('conditional_condition_select_logic') + '</option>';

		var group_last = false

		for(var logic_key in logics) {

			if(!logics.hasOwnProperty(logic_key)) { continue; }

			var logic = logics[logic_key];

			// Check for data source exclusion
			var disabled = ((typeof(logic.data_source_exclude) !== 'undefined') ? logic.data_source_exclude : false) && has_data_source;

			// Check for optgroup
			var group = ((typeof(logic.group) !== 'undefined') ? logic.group : false);
			if(
				(group !== group_last) &&
				(group !== false)
			) {

				var group_label = conditional_settings_logic_group[group]['label'];

				// </optgroup>
				if(group_last !== false) {

					logic_html += '</optgroup>';
				}

				// <optgroup>
				logic_html += '<optgroup label="' + this.esc_attr(group_label) + '">';

				group_last = group;
			}

			// Check for auto_else_disabled
			var auto_else_disabled = (group !== false) ? ((typeof(conditional_settings_logic_group[group]['auto_else_disabled']) !== 'undefined') ? conditional_settings_logic_group[group]['auto_else_disabled'] : false) : false;

			// Add option
			logic_html += '<option value="' + this.esc_attr(logic_key) + '"' + ((logic_key == object_logic) ? ' selected' : '') + (disabled ? ' disabled' : '') + (auto_else_disabled ? ' data-auto-else-disabled' : '') + '>' + this.esc_html(logic.text) + '</option>';
			if(logic_key == object_logic) {

				logic_key_found = logic_key;
				logic_rows = (typeof(logic.rows) !== 'undefined') ? logic.rows : false;
			}
		}

		// </optgroup>
		if(group_last !== false) {

			logic_html += '</optgroup>';
		}

		logic_html += '</select></li>';

		return {'logic_key_found': logic_key_found, 'html': logic_html, 'rows': logic_rows};
	}

	// Conditional - Render value
	$.WS_Form.prototype.conditional_value_html = function(type, type_sub, object, object_id, object_value, object_case_sensitive, id) {

		var conditional_settings = $.WS_Form.settings_form.conditional;
		var conditional_settings_objects = conditional_settings.objects;
		var logic = $.extend(true, [], conditional_settings_objects[object]['logic']);
		var action = $.extend(true, [], conditional_settings_objects[object]['action']);
		var values_html = '';

		// Get data
		switch(type) {

			case 'condition' :

				var data = logic[type_sub];
				var case_sensitive = (typeof(data['case_sensitive']) === 'undefined') ? true : data['case_sensitive'];
				break;

			case 'then' :
			case 'else' :

				var data = action[type_sub];
				var case_sensitive = false;
				break;
		}

		// Read logic or action data
		var values = (typeof(data['values']) === 'undefined') ? true : data['values'];

		// Input
		if(values === true) {

			// Read input parameters
			var input_type = (typeof(data['type']) === 'undefined') ? 'text' : data['type'];
			var min = (typeof(data['min']) === 'undefined') ? false : data['min'];
			var max = (typeof(data['max']) === 'undefined') ? false : data['max'];
			var unit = (typeof(data['unit']) === 'undefined') ? '' : data['unit'];
			var placeholder = (typeof(data['placeholder']) === 'undefined') ? '' : data['placeholder'];

			// Special condition for datetime field
			if(input_type == 'datetime') {

				// Check object data
				var field = false;
				switch(object) {

					case 'field' :

						field = this.field_data_cache[object_id];
						input_type = this.get_object_meta_value(field, 'input_type_datetime', 'datetime-local');
						break;
				}
			}

			// Create input
			switch(input_type) {

				case 'textarea' :

					values_html = '<li class="wsf-conditional-value"><textarea data-attribute="value" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '" class="wsf-field wsf-field-small">' + this.esc_html(object_value) + '</textarea></li>';
					break;

				case 'text_editor' :

					values_html = '<li class="wsf-conditional-value wsf-text-editor"><textarea id="wsf-text-editor-' + this.esc_attr(this.text_editor_index) + '" data-attribute="value" data-text-editor="true" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '" class="wsf-field wsf-field-small">' + this.esc_html(object_value) + '</textarea></li>';
					this.text_editor_index++;
					break;

				case 'html_editor' :
				case 'js_editor' :

					values_html = '<li class="wsf-conditional-value wsf-html-editor"><textarea id="wsf-html-editor-' + this.esc_attr(this.code_editor_index) + '" data-attribute="value" data-html-editor="true" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '" class="wsf-field wsf-field-small"' + ((input_type == 'js_editor') ? ' data-html-editor-mode="javascript"' : '') + '>' + this.esc_html(object_value) + '</textarea></li>';
					this.code_editor_index++;
					break;

				default :

					values_html = '<li class="wsf-conditional-value"><input type="text" data-attribute="value" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '" class="wsf-field wsf-field-small" value="' + this.esc_attr(object_value) + '"' + (min === false ? '' : ' min="' + this.esc_attr(min) + '"') + (max === false ? '' : ' max="' + this.esc_attr(max) + '"') + (placeholder === false ? '' : ' placeholder="' + this.esc_attr(placeholder) + '"') + ' /><span class="wsf-field-unit">' + unit + '</span></li>';
					break;
			}

			// Case sensitivity
			if(case_sensitive) {

				var object_case_sensitive_id = 'wsf-case-sensitive-' + type + '-' + id;
				values_html += '<li><input id="' + this.esc_attr(object_case_sensitive_id) + '" type="checkbox" data-attribute="case_sensitive" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '" class="wsf-field" value="on"' + (object_case_sensitive ? ' checked' : '') + ' /><label for="' + this.esc_attr(object_case_sensitive_id) + '" class="wsf-label">' + this.language('conditional_case_sensitive') + '</label></li>';
			}
		}

		// Fields
		if(values === 'fields') {

			var options = this.conditional_options_groups('field', 'field');

			var values_html = '<li><select data-attribute="value" class="wsf-field wsf-field-small" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '">';
			values_html += '<option value="">' + this.language('conditional_field_select') + '</option>';

			var conditional_options = this.conditional_options('field', object_value, options, type);

			values_html += conditional_options.html;

			values_html += '</select></li>';
		}

		// Select
		if(typeof(values) === 'object') {

			values_html = '<li><select data-attribute="value" class="wsf-field wsf-field-small" data-type="' + this.esc_attr(type) + '" data-id="' + this.esc_attr(id) + '">';

			for(var value_key in values) {

				if(!values.hasOwnProperty(value_key)) { continue; }

				var value = values[value_key];

				values_html += '<option value="' + this.esc_attr(value.value) + '"' + ((value.value == object_value) ? ' selected' : '') + '>' + this.esc_html(value.text) + '</option>';
			}

			values_html += '</select></li>';
		}

		return values_html;
	}

	// Condition - Objects events
	$.WS_Form.prototype.conditional_objects_events = function(type, object_obj) {

		var objs = $('[data-attribute="object_id"][data-type="' + this.esc_selector(type) + '"]', object_obj);

/*		// Get language
		var locale = ws_form_settings.locale_user;
		var language = locale.substring(0, 2);

		// Select2
		objs.each(function() {

			var config = {

				// Add clear icon
				allowClear: true,

				// Placeholder
				placeholder: (typeof($(this).attr('placeholder')) !== 'undefined') ? $(this).attr('placeholder') : '',

				// Language
				language: language,

				// CSS
				selectionCssClass: 'wsf-select2-conditional-selection',
				dropdownCssClass: 'wsf-select2-conditional-dropdown',

				// Dropdown parent
				dropdownParent: $(this).parent()
			};

			$(this).select2(config);

			// Open event
			$(this).on('select2:open', function (e) {

				$('.select2-search__field', $(this).parent()).get(0).focus();
			});
		});
*/
		// On change event
		objs.on('change', function() {

			// Get type
			var type = $(this).attr('data-type');

			// Get object id
			var object_id = $(this).attr('data-id');

			// Get object_single array from cache
			var object_single = $.WS_Form.this.object_cache[type][object_id];

			// Get object_obj
			var object_obj = $(this).closest('.wsf-conditional-' + type);

			// Set object ID
			object_single.object = $(this).find(":selected").attr('data-object');
			object_single.object_id = $(this).val();
			object_single.object_row_id = 0;
			object_single.value = '';
			object_single.multiple = false;

			// Re-initialize this object_single
			switch(type) {

				case 'condition' :

					object_single.logic = '';
					$.WS_Form.this.conditional_condition(object_obj, object_single, object_id);
					break;

				case 'then' :
				case 'else' :

					object_single.action = '';
					$.WS_Form.this.conditional_thenelse(type, object_obj, object_single, object_id);
					break;
			}

			// Save
			$.WS_Form.this.conditional_save();

			// Show conditional icons
			$.WS_Form.this.conditional_icons_build(true, $.WS_Form.this.object_data_scratch);
		});
	}

	// Condition - Object rows events
	$.WS_Form.prototype.conditional_object_rows_events = function(type, object_obj) {

		$('[data-attribute="object_row_id"][data-type="' + this.esc_selector(type) + '"]', object_obj).on('change', function() {

			// Get type
			var type = $(this).attr('data-type');

			// Get object id
			var object_id = $(this).attr('data-id');

			// Get object_single array from cache
			var object_single = $.WS_Form.this.object_cache[type][object_id];

			// Get object_obj
			var object_obj = $(this).closest('.wsf-conditional-' + type);

			// Set object row ID
			object_single.object_row_id = $(this).val();
			object_single.value = '';

			// Re-initialize this object_single
			switch(type) {

				case 'then' :
				case 'else' :

					$.WS_Form.this.conditional_thenelse(type, object_obj, object_single, object_id);
					break;
			}

			// Save
			$.WS_Form.this.conditional_save();
		});
	}

	// Condition - Value events
	$.WS_Form.prototype.conditional_value_events = function(type, object_obj) {

		$('[data-attribute="value"][data-type="' + this.esc_selector(type) + '"]', object_obj).on('input', function() {

			// Get type
			var type = $(this).attr('data-type');

			// Get object id
			var object_id = $(this).attr('data-id');

			// Get object_single array from cache
			var object_single = $.WS_Form.this.object_cache[type][object_id];

			// Get object_obj
			var object_obj = $(this).closest('.wsf-conditional-' + type);

			// Set value
			object_single.value = $(this).val();

			// Auto else
			if(type == 'then') {

				var conditional_settings = $.WS_Form.settings_form.conditional;
				var action_configuration = conditional_settings['objects'][object_single.object]['action'][object_single.action];
				var action_else_copy = (typeof(action_configuration.auto_else_copy) !== 'undefined') ? action_configuration.auto_else_copy : false;

				if(action_else_copy) {

					$.WS_Form.this.conditional_then_auto_else(object_single, true);
				}
			}
		});

		$('[data-attribute="value"][data-type="' + this.esc_selector(type) + '"]', object_obj).on('change', function() {

			// Get type
			var type = $(this).attr('data-type');

			// Get object id
			var object_id = $(this).attr('data-id');

			// Get object_single array from cache
			var object_single = $.WS_Form.this.object_cache[type][object_id];

			// Get object_obj
			var object_obj = $(this).closest('.wsf-conditional-' + type);

			// Set value
			object_single.value = $(this).val();

			// Auto else
			if(type == 'then') {

				var conditional_settings = $.WS_Form.settings_form.conditional;
				var action_configuration = conditional_settings['objects'][object_single.object]['action'][object_single.action];
				var action_else_copy = (typeof(action_configuration.auto_else_copy) !== 'undefined') ? action_configuration.auto_else_copy : false;

				if(action_else_copy) {

					$.WS_Form.this.conditional_then_auto_else(object_single);
				}
			}

			// Re-initialize this object_single
			switch(type) {

				case 'condition' :

					$.WS_Form.this.conditional_condition(object_obj, object_single, object_id);
					break;

				case 'then' :
				case 'else' :

					$.WS_Form.this.conditional_thenelse(type, object_obj, object_single, object_id);
					break;
			}

			// Save
			$.WS_Form.this.conditional_save();

			// Show conditional icons
			$.WS_Form.this.conditional_icons_build(true, $.WS_Form.this.object_data_scratch);
		});

		// Text Editor
		this.sidebar_tinymce_init(object_obj);

		// HTML Editor
		this.sidebar_html_editor_init(object_obj);
	}

	// Condition - Case sensitive
	$.WS_Form.prototype.conditional_case_sensitive_events = function(type, object_obj) {

		$('[data-attribute="case_sensitive"][data-type="' + this.esc_selector(type) + '"]', object_obj).on('change', function() {

			// Get type
			var type = $(this).attr('data-type');

			// Get object id
			var object_id = $(this).attr('data-id');

			// Get object_single array from cache
			var object_single = $.WS_Form.this.object_cache[type][object_id];

			// Get object_obj
			var object_obj = $(this).closest('.wsf-conditional-' + type);

			// Set case sensitive
			object_single.case_sensitive = $(this).is(':checked');

			// Re-initialize this object_single
			switch(type) {

				case 'condition' :

				$.WS_Form.this.conditional_condition(object_obj, object_single, object_id);
				break;

				case 'then' :
				case 'else' :

				$.WS_Form.this.conditional_thenelse(type, object_obj, object_single, object_id);
				break;
			}

			// Save
			$.WS_Form.this.conditional_save();
		});
	}

	// Condition - Multiple
	$.WS_Form.prototype.conditional_multiple_events = function(type, object_obj) {

		$('[data-attribute="multiple"][data-type="' + this.esc_selector(type) + '"]', object_obj).on('change', function() {

			// Get type
			var type = $(this).attr('data-type');

			// Get object id
			var object_id = $(this).attr('data-id');

			// Get object_single array from cache
			var object_single = $.WS_Form.this.object_cache[type][object_id];

			// Get object_obj
			var object_obj = $(this).closest('.wsf-conditional-' + type);

			// Set multiple
			object_single.multiple = $(this).is(':checked');

			// If set to single, ensure only one row is selected
			if(
				!object_single.multiple &&
				(typeof(object_single.object_row_id) === 'object') &&
				(typeof(object_single.object_row_id[0]) !== 'undefined')
			) {

				object_single.object_row_id = object_single.object_row_id[0];
			}

			// Re-initialize this object_single
			switch(type) {

				case 'condition' :

				$.WS_Form.this.conditional_condition(object_obj, object_single, object_id);
				break;

				case 'then' :
				case 'else' :

				$.WS_Form.this.conditional_thenelse(type, object_obj, object_single, object_id);
				break;
			}

			// Save
			$.WS_Form.this.conditional_save();
		});
	}

	// Condition - Options
	$.WS_Form.prototype.conditional_options = function(object, object_id, options, type, level) {

		if(typeof(level) === 'undefined') { level = 1; }

		var options_html = '';
		var object_id_found = false;

		for(var options_index in options) {

			if(!options.hasOwnProperty(options_index)) { continue; }

			var option = options[options_index];

			var option_selected = (option.object == object) && (option.value == object_id);
//			var option_disabled = (typeof(option['disabled_' + type]) !== 'undefined') ? option['disabled_' + type] : false;
			var option_disabled = (typeof(option.disabled) !== 'undefined') ? option.disabled : false;
			object_id_found |= option_selected;

			if(typeof(option.optgroup) !== 'undefined') {

				options_html += '<option value="' + this.esc_attr(option.value) + '" data-object="' + this.esc_attr(option.object) + '"' + (option_selected ? ' selected' : '') + (option_disabled ? ' disabled' : '') + '>' + ((level > 1) ? Array(level).join('-') + ' ' : '') + this.esc_html(option.text) + '</option>';
				var conditional_options = this.conditional_options(object, object_id, option.optgroup, type, level + 1);
				options_html += conditional_options.html;
				object_id_found |= conditional_options.found;

			} else {

				options_html += '<option value="' + this.esc_attr(option.value) + '" data-object="' + this.esc_attr(option.object) + '"' + (option_selected ? ' selected' : '') + (option_disabled ? ' disabled' : '') + '>' + ((level > 1) ? Array(level).join('-') + ' ' : '') + this.esc_html(option.text) + '</option>';
			}
		}

		return {'html': options_html, 'found': object_id_found};
	}

	// Condition - Options - Groups
	$.WS_Form.prototype.conditional_options_groups = function(type, object_filter) {

		if(typeof(object_filter) === 'undefined') { object_filter = false; }
		var disabled = (object_filter !== false) && (object_filter !== 'group');

		var options = [];

		for(var group_index in this.form.groups) {

			if(!this.form.groups.hasOwnProperty(group_index)) { continue; }

			var group = this.form.groups[group_index];

			options.push({optgroup: this.conditional_options_sections(type, group.sections, object_filter), 'value': group.id, 'text': this.language('group') + ': ' + group.label + ' (' + this.language('id') + ': ' + group.id + ')', 'object': 'group', 'disabled': disabled});
		}

		return options;
	}

	// Condition - Options - Sections
	$.WS_Form.prototype.conditional_options_sections = function(type, sections, object_filter) {

		if(typeof(object_filter) === 'undefined') { object_filter = false; }
		var disabled = (object_filter !== false) && (object_filter !== 'section');

		var options = [];

		for(var section_index in sections) {

			if(!sections.hasOwnProperty(section_index)) { continue; }

			var section = sections[section_index];

			options.push({optgroup: this.conditional_options_fields(type, section.fields, object_filter), 'value': section.id, 'text': this.language('section') + ': ' + section.label + ' (' + this.language('id') + ': ' + section.id + ')', 'object': 'section', 'disabled': disabled});
		}

		return options;
	}

	// Condition - Options - Fields
	$.WS_Form.prototype.conditional_options_fields = function(type, fields, object_filter) {

		if(typeof(object_filter) === 'undefined') { object_filter = false; }
		var disabled = (object_filter !== false) && (object_filter !== 'field');

		var options = [];

		for(var field_index in fields) {

			if(!fields.hasOwnProperty(field_index)) { continue; }

			var field = fields[field_index];

			// Get field type meta config
			var field_type = $.WS_Form.field_type_cache[field.type];

			// Are there conditional settings?
			if(typeof(field_type['conditional']) !== 'undefined') {

				// Should this field type be excluded?
				if(typeof(field_type['conditional']['exclude_' + type]) !== 'undefined') {

					if(field_type['conditional']['exclude_' + type]) { continue; }
				}
			}

			options.push({value: field.id, 'text': field.label + ' (' + this.language('id') + ': ' + field.id + ')', 'object': 'field', 'disabled' : disabled});
		}

		return options;
	}

	// Conditional highlight array - Build
	$.WS_Form.prototype.conditional_icons_build = function(render_objects, form_object) {

		if(typeof(form_object) === 'undefined') { form_object = this.form; }

		var conditionals = this.get_object_meta_value(form_object, 'conditional', false);

		// Reset arrays
		this.conditional_icons_array = {

			group: [],
			section: [],
			field: []
		};

		this.conditional_row_array = {

			group: [],
			section: [],
			field: []
		};

		this.conditional_highlight_array = $.extend(true, {}, this.conditional_icons_array);

		// Run through each condition to determine which groups, sections and tabs need icons or highlighting
		if(
			(conditionals !== false) &&
			(typeof(conditionals.groups) !== 'undefined') &&
			(typeof(conditionals.groups[0]) !== 'undefined') &&
			(typeof(conditionals.groups[0].rows) !== 'undefined')
		) {

			for(var conditional_index in conditionals.groups[0].rows) {

				if(!conditionals.groups[0].rows.hasOwnProperty(conditional_index)) { continue; }

				var row = conditionals.groups[0].rows[conditional_index];

				if(row === null) { continue; }

				var row_disabled = (typeof(row.disabled) !== 'undefined') && row.disabled;

				var icon = !row_disabled;

				var highlight = (this.conditional_highlight_row_index !== false) && (conditional_index == this.conditional_highlight_row_index);

				if(
					(typeof(row.data) === 'undefined') ||
					(typeof(row.data[1]) === 'undefined')

				) { continue; }

				// Read data
				var conditional = row.data[1];

				// Attempt to JSON decode it
				try {

					var conditional = JSON.parse(conditional);

				} catch(e) {

					var conditional = false;
				}

				if(
					(typeof(conditional.if) === 'undefined') ||
					(typeof(conditional.then) === 'undefined') ||
					(typeof(conditional.else) === 'undefined')

				) { continue; }

				// Process IF
				for(var group_index in conditional.if) {

					if(!conditional.if.hasOwnProperty(group_index)) { continue; }

					var group = conditional.if[group_index];

					// Error check group
					if(!group || !group.conditions) { continue; }

					// Process conditions
					for(var condition_then_else_index in group.conditions) {

						if(!group.conditions.hasOwnProperty(condition_then_else_index)) { continue; }

						var condition_then_else = group.conditions[condition_then_else_index];

						this.conditional_icons_process(condition_then_else, conditional_index, icon, highlight);
					}
				}

				// Process THEN
				for(var condition_then_else_index in conditional.then) {

					if(!conditional.then.hasOwnProperty(condition_then_else_index)) { continue; }

					var condition_then_else = conditional.then[condition_then_else_index];

					this.conditional_icons_process(condition_then_else, conditional_index, icon, highlight);
				}

				// Process ELSE
				for(var condition_then_else_index in conditional.else) {

					if(!conditional.else.hasOwnProperty(condition_then_else_index)) { continue; }

					var condition_then_else = conditional.else[condition_then_else_index];

					this.conditional_icons_process(condition_then_else, conditional_index, icon, highlight);
				}
			}
		}

		// Render
		if(render_objects) {

			// Reset icons
			$('.wsf-group-tab .wsf-group-conditional, .wsf-section .wsf-section-conditional').hide();
			$('.wsf-field-wrapper .wsf-field-conditional').remove();

			// Set icons
			for(var object in this.conditional_icons_array) {

				if(!this.conditional_icons_array.hasOwnProperty(object)) { continue; }

				var object_ids = this.conditional_icons_array[object];

				for(var object_id_index in object_ids) {

					if(!object_ids.hasOwnProperty(object_id_index)) { continue; }

					var object_id = object_ids[object_id_index];
					var obj = this.conditional_icons_get_obj(object, object_id);

					if(obj && obj.length) {

						switch(object) {

							case 'group' :

								this.group_render(obj);
								break;

							case 'section' :

								this.section_render(obj);
								break;

							case 'field' :

								this.field_render(obj);
								break;
						}
					}
				}
			}

			// Reset highlighting
			$('.wsf-group-tab, .wsf-group, .wsf-section, .wsf-field-wrapper').removeClass('wsf-conditional-highlight');

 			// Set highlighting
			for(var object in this.conditional_highlight_array) {

				if(!this.conditional_highlight_array.hasOwnProperty(object)) { continue; }

				var object_ids = this.conditional_highlight_array[object];

				for(var object_id_index in object_ids) {

					if(!object_ids.hasOwnProperty(object_id_index)) { continue; }

					var object_id = object_ids[object_id_index];
					var obj = this.conditional_icons_get_obj(object, object_id);

					if(obj && obj.length) {

						obj.addClass('wsf-conditional-highlight');
					}
				}
			}
		}
	}

	// Conditional highlight array - Process
	$.WS_Form.prototype.conditional_icons_get_obj = function(object, object_id) {

		// Check object ID exists
		switch(object) {

			case 'group' :

				return $('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"], .wsf-group[data-id="' + this.esc_selector(object_id) + '"]');

			case 'section' :

				return $('.wsf-section[data-id="' + this.esc_selector(object_id) + '"]');

			case 'field' :

				return $('.wsf-field-wrapper[data-id="' + this.esc_selector(object_id) + '"]');
		}

		return false;
	}

	// Conditional highlight array - Process
	$.WS_Form.prototype.conditional_icons_process = function(conditional_then_else, conditional_index, icon, highlight) {

		if(
			(conditional_then_else === null) ||
			(typeof(conditional_then_else) !== 'object') ||
			(typeof(conditional_then_else.object) === 'undefined') ||
			(typeof(conditional_then_else.object_id) === 'undefined') ||
			(typeof(conditional_then_else.value) === 'undefined')
		) { 
			return false;
		}

		// Read condition
		var object = conditional_then_else.object;
		var object_id = parseInt(conditional_then_else.object_id, 10);
		var value = conditional_then_else.value;

		// Check object selected
		if(!object) { return; }

		// Check object ID
		var obj = this.conditional_icons_get_obj(object, object_id);
		if(!obj || !obj.length) { return; }

		// Check object and object_id
		if(typeof(this.conditional_icons_array[object]) === 'object') {

			if(icon && (this.conditional_icons_array[object].indexOf(object_id) === -1)) {

				this.conditional_icons_array[object].push(object_id);
			}

			if(highlight && (this.conditional_highlight_array[object].indexOf(object_id) === -1)) {

				this.conditional_highlight_array[object].push(object_id);
			}
		}

		// Conditional row array push
		this.conditional_row_array_push(object, object_id, conditional_index);

		// Check value (e.g. Matches field)
		var value_int = parseInt(value, 10);
		if(value_int > 0) {

			if(icon && (this.conditional_icons_array[object].indexOf(value_int) === -1)) {

				this.conditional_icons_array[object].push(value_int);

				if(typeof(this.conditional_row_array[object][object_id]) === 'undefined') { this.conditional_row_array[object][object_id] = []; }
				this.conditional_row_array[object][object_id].push(conditional_index);
			}

			if(highlight && (this.conditional_highlight_array[object].indexOf(value_int) === -1)) {

				this.conditional_highlight_array[object].push(value_int);
			}

			// Conditional row array push
			this.conditional_row_array_push(object, value_int, conditional_index);
		}

		// Check value for variables
		if(value.indexOf('#') !== -1) {

			for(var object in $.WS_Form.parse_variables_repairable) {

				if(!$.WS_Form.parse_variables_repairable.hasOwnProperty(object)) { continue; }

				switch(object) {

					case 'section' :

						var objects = this.section_data_cache;
						break;

					case 'field' :

						var objects = this.field_data_cache;
						break;
				}

				var parse_variables = $.WS_Form.parse_variables_repairable[object];

				for(var parse_variable_index in parse_variables) {

					if(!parse_variables.hasOwnProperty(parse_variable_index)) { continue; }

					var parse_variable = parse_variables[parse_variable_index];

					if(value.indexOf('#' + parse_variable) === -1) { continue; }

					for(var object_id in objects) {

						if(!objects.hasOwnProperty(object_id)) { continue; }

						object_id = parseInt(object_id, 10);

						if(
							(value.indexOf('#' + parse_variable + '(' + object_id + ')') !== -1) ||
							(value.indexOf('#' + parse_variable + '(' + object_id + ',') !== -1)
						) {

							if(icon && (this.conditional_icons_array[object].indexOf(object_id) === -1)) {

								this.conditional_icons_array[object].push(object_id);

								if(typeof(this.conditional_row_array[object][object_id]) === 'undefined') { this.conditional_row_array[object][object_id] = []; }
								this.conditional_row_array[object][object_id].push(conditional_index);
							}

							if(highlight && (this.conditional_highlight_array[object].indexOf(object_id) === -1)) {

								this.conditional_highlight_array[object].push(object_id);
							}

							// Conditional row array push
							this.conditional_row_array_push(object, object_id, conditional_index);
						}
					}
				}
			}
		}
	}

	// Conditional row array push
	$.WS_Form.prototype.conditional_row_array_push = function(object, object_id, conditional_index) {

		if(typeof(this.conditional_row_array[object][object_id]) === 'undefined') { this.conditional_row_array[object][object_id] = []; }
		this.conditional_row_array[object][object_id].push(conditional_index);
	}

	// Action - Render from data
	$.WS_Form.prototype.action_render_from_data = function() {

		// Validate action_data
		try {

			this.action = JSON.parse(this.action_data[1]);

		} catch(e) {

			this.action = this.action_new();
		}

		// Render
		this.action_render();
	}

	// Action - Blank
	$.WS_Form.prototype.action_new = function() {

		// Build a new action
		var action = {

			id: '',
			meta: {},
			events: []
		};

		return action;
	}

	// Action - Render
	$.WS_Form.prototype.action_render = function() {

		var action_obj = $('#wsf-action-tr > td > div');

		var action_id = this.action.id;

		// Ensure action is still installed
		if(typeof($.WS_Form.actions[action_id]) === 'undefined') { this.action = this.action_new(); action_id = ''; }

		// Build action global fields
		var action_header_html = '<fieldset class="wsf-fieldset wsf-fieldset-header" data-action-header="true" data-object="action" data-id="' + this.esc_attr(action_id) + '">';

		// Type of action
		action_header_html += '<div class="wsf-field-wrapper"><label class="wsf-label">' + this.language('data_grid_action_action') + '</label>';
		action_header_html += '<select data-meta-key="action_id" class="wsf-field">';
		action_header_html += '<option value="">' + this.language('options_select') + '</option>';

		// Get actions that can only be fired once and are already included
		var action_single_use_exhausted_array = [];
		var action_meta = this.get_object_meta_value(this.object_data_scratch, 'action');
		var actions = action_meta.groups[0].rows;
		for(var action_index in actions) {

			if(!actions.hasOwnProperty(action_index)) { continue; }

			// Get action
			try {

				var action_single = JSON.parse(actions[action_index].data[1]);

			} catch(e) {

				continue;
			}

			// Get action_id
			if(typeof(action_single['id']) === 'undefined') { continue; }
			var action_single_id = action_single['id'];

			// Get multiple value
			if(typeof($.WS_Form.actions[action_single_id]) === 'undefined') { continue }
			if(typeof($.WS_Form.actions[action_single_id].multiple) === 'undefined') { continue }

			// If action can only be run once, hide it from the actions pull down
			if(!$.WS_Form.actions[action_single_id].multiple) { action_single_use_exhausted_array.push(action_single_id); }
		}

		// Sort actions by label_action
		var actions_sorted = [];
		for (var action_sorted_id in $.WS_Form.actions) { actions_sorted.push({id: action_sorted_id, action: $.WS_Form.actions[action_sorted_id]}); }
		actions_sorted.sort(function (a, b) { return (a.action.label_action.toLowerCase() < b.action.label_action.toLowerCase()) ? -1 : 1; } );

		for(var action_sorted_index in actions_sorted) {

			if(!actions_sorted.hasOwnProperty(action_sorted_index)) { continue; }

			var action_single = actions_sorted[action_sorted_index];

			var action_single_id = action_single.id;

			// Skip one time use actions that have already be used
			if((action_single_use_exhausted_array.indexOf(action_single_id) !== -1) && (action_single_id != action_id)) { continue; }

			var action_single_label = action_single.action.label_action;

			action_header_html += '<option value="' + this.esc_attr(action_single_id) + '"' + ((action_single_id == action_id) ? ' selected' : '') + '>' + this.esc_html(action_single_label) + '</option>';
		}


		action_header_html += '</select>';
		action_header_html += '</div>';

		action_header_html += '<div class="wsf-field-wrapper">';

		if(action_id != '') {

			action_header_html += '<label class="wsf-label">' + this.language('data_grid_action_event') + '</label>';

			// When to fire action?
			var action_events = $.WS_Form.settings_form.sidebars.action.events;
			for(var action_event_key in action_events) {

				if(!action_events.hasOwnProperty(action_event_key)) { continue; }

				var action_event = action_events[action_event_key];
				var action_selected = ($.WS_Form.this.action.events.indexOf(action_event_key) != -1);

				action_header_html += '<div><input class="wsf-field" type="checkbox" id="wsf_action_event_' + this.esc_attr(action_event_key) + '" name="wsf_action_event[]"' + (action_selected ? ' checked' : '') + ' /><label class="wsf-label" for="wsf_action_event_' + this.esc_attr(action_event_key) + '">' + this.esc_html(action_event['label']) + '</label></div>';
			}
		}

		action_header_html += '</div>';

		action_header_html += '</fieldset>';

		action_obj.html(action_header_html);

		// Get sidebar HTML and Inits
		if(action_id != '') {

			// Clear sidebar caches
			this.sidebar_cache_clear(action_obj);

			var sidebar_html_return = this.sidebar_html('action', action_id, this.action, $.WS_Form.actions[action_id], false, true, true, false, false);

			// Add action HTML to object
			action_obj.append(sidebar_html_return.html);

			// Initialize action
			this.sidebar_inits(sidebar_html_return.inits, action_obj, action_obj, this.action);
		}

		// Action change event
		$('[data-meta-key="action_id"]', action_obj).on('change', function() {

			var action_id = $(this).val();

			// Get action label object
			var action_label = $('[data-column="0"]', $(this).closest('tr').prev());

			// Set action variables
			$.WS_Form.this.action_save(true);

			// Render sidebar
			$.WS_Form.this.action_render();

			// Set checkboxes and label
			if(action_id != '') {

				// Set default events
				var action = $.WS_Form.actions[action_id];
				if(typeof(action.events) !== 'undefined') {

					for(var action_event_index in action.events) {

						if(!action.events.hasOwnProperty(action_event_index)) { continue; }

						var action_event = action.events[action_event_index];

						$('#wsf_action_event_' + action_event).prop('checked', true);
					}
				}

				// Set action label
				if(action_label.val() === $.WS_Form.this.language('action_label_default')) {

					action_label.val(action.label_action);
				}

			} else {

				// Set action label
				var default_label = $.WS_Form.this.language('action_label_default');
				action_label.val(default_label);
			}

			// Trigger save of label
			action_label.trigger('input');

			// Set action variables
			$.WS_Form.this.action_save(true);
		});

		// Reload on change
		$('[data-change="reload"]', action_obj).on('change', function() {

			// Set action variables
			$.WS_Form.this.action_save();

			// Render sidebar
			$.WS_Form.this.action_render();
		});

		action_obj.show();
	}

	// Action API method events
	$.WS_Form.prototype.api_reload_init = function(obj, success_callback, error_callback, sidebar) {

		$('[data-action="wsf-api-reload"]', obj).on('click', function() {

			// Lock sidebar
			$.WS_Form.this.sidebar_lock();

			var api_reload_obj = $(this);

			// Start reloader spinning
			api_reload_obj.addClass('wsf-api-method-calling');

			// If this reload button is in a sidebar, save the sidebar to scratch before calling the API request
			if(sidebar) {

				// Get meta key data
				var meta_key = $('[data-meta-key="' + $.WS_Form.this.esc_selector(api_reload_obj.attr('data-meta-key-for')) + '"]');
				var object = meta_key.attr('data-object');
				var object_id = meta_key.attr('data-object-id');
				var object_data = $.WS_Form.this.get_object_data(object, object_id, true);

				// Save sidebar
				for(var key in $.WS_Form.this.object_meta_cache) {

					if(!$.WS_Form.this.object_meta_cache.hasOwnProperty(key)) { continue; }

					// Get meta_key
					var meta_key = $.WS_Form.this.object_meta_cache[key]['meta_key'];

					// Update object data
					$.WS_Form.this.object_data_update_by_meta_key(object, object_data, meta_key);
				}
			}

			// Get action ID and method
			var reload_action_id = api_reload_obj.attr('data-action-id');
			if(reload_action_id == undefined) {

				var reload_action_id_meta_key = api_reload_obj.attr('data-action-id-meta-key');
				if(reload_action_id_meta_key != undefined) {

					reload_action_id = $('[data-meta-key="' + $.WS_Form.this.esc_selector(reload_action_id_meta_key) + '"]', $('#wsf-sidebars')).val();
				}
			}
			var reload_method = api_reload_obj.attr('data-method');

			// Get list ID
			var reload_list_id_meta_key = api_reload_obj.attr('data-list-id-meta-key');
			var reload_list_id = (reload_list_id_meta_key != undefined) ? $('[data-meta-key="' + $.WS_Form.this.esc_selector(reload_list_id_meta_key) + '"]', $('#wsf-sidebars')).val() : false;

			// Get list sub ID
			var reload_list_sub_id_meta_key = api_reload_obj.attr('data-list-sub-id-meta-key');
			var reload_list_sub_id = (reload_list_sub_id_meta_key != undefined) ? $('[data-meta-key="' + $.WS_Form.this.esc_selector(reload_list_sub_id_meta_key) + '"]', $('#wsf-sidebars')).val() : false;

			// Get API call path
			var api_call_path = $.WS_Form.this.action_api_method_path(reload_action_id, reload_method, reload_list_id, reload_list_sub_id);

			$.WS_Form.this.options_action_cache_clear(api_call_path);

			// Make API call
			$.WS_Form.this.api_call(api_call_path, 'GET', false, function() {

				api_reload_obj.removeClass('wsf-api-method-calling');
				success_callback(api_reload_obj, reload_action_id, reload_method);

			}, function(data) {

				if(data.error) { $.WS_Form.this.error('error_bad_request_message', data.error_message); }
				api_reload_obj.removeClass('wsf-api-method-calling');

			}, true);	// Bypass loader
		});
	}

	// Clear options_action_cache
	$.WS_Form.prototype.options_action_cache_clear = function(api_call_path) {

		if(api_call_path === false) { return false; }

		if(api_call_path.endsWith('/fetch/')) {

			api_call_path = api_call_path.substring(0, api_call_path.length - 6);
		}

		$.WS_Form.this.options_action_cache[api_call_path] = undefined;
	}

	// Get API call path
	$.WS_Form.prototype.action_api_method_path = function(action_id, method, list_id, list_sub_id) {

		// Check values
		if((typeof(action_id) === 'undefined') || (action_id == '') || (action_id === null)) { return false; }
		if((typeof(method) === 'undefined') || (method == '') || (method === null)) { return false; }
		if((typeof(list_id) === 'undefined') || (list_id == '') || (list_id === null)) { list_id = false; }
		if((typeof(list_sub_id) === 'undefined') || (list_sub_id == '') || (list_sub_id === null)) { list_sub_id = false; }

		// Build API path and params
		var api_call_path = 'action/' + action_id + '/';

		// Process API method
		switch(method) {

			case 'lists' :

				api_call_path += 'lists/';
				break;

			case 'lists_fetch' :

				api_call_path += 'lists/fetch/';
				break;

			case 'list' :

				if(list_id === false) { return false; }
				api_call_path += 'list/' + encodeURIComponent(list_id) + '/';
				break;

			case 'list_fetch' :

				if(list_id === false) { return false; }
				api_call_path += 'list/' + encodeURIComponent(list_id) + '/fetch/';
				break;

			case 'list_subs' :

				if(list_id === false) { return false; }
				api_call_path += 'list/' + encodeURIComponent(list_id) + '/subs/';
				break;

			case 'list_subs_fetch' :

				if(list_id === false) { return false; }
				api_call_path += 'list/' + encodeURIComponent(list_id) + '/subs/fetch/';
				break;

			case 'list_fields' :

				if(list_id === false) { return false; }
				if(list_sub_id === false) {

					api_call_path += 'list/' + encodeURIComponent(list_id) + '/fields/';

				} else {

					api_call_path += 'list/' + encodeURIComponent(list_id) + '/subs/' + encodeURIComponent(list_sub_id) + '/fields/';
				}
				break;

			case 'list_fields_fetch' :

				if(list_id === false) { return false; }
				if(list_sub_id === false) {

					api_call_path += 'list/' + encodeURIComponent(list_id) + '/fields/fetch/';

				} else {

					api_call_path += 'list/' + encodeURIComponent(list_id) + '/subs/' + encodeURIComponent(list_sub_id) + '/fields/fetch/';
				}
				break;

			default :

				api_call_path += method + '/';
		}

		return api_call_path;
	}

	// Set the action array variables
	$.WS_Form.prototype.action_save = function(build_meta) {

		if(typeof(build_meta) === 'undefined') { build_meta = false; }

		// Check if sidebar is locked
		if(this.sidebar_lock_count) { return false; }

		if(this.action === false) { return false; }
		if(!$('#wsf-action-tr').length) { return false; }

		this.action.id = $('[data-meta-key="action_id"]').val();
		this.action.events = [];
		this.action.meta = {};

		if(this.action.id != '') {

			// Set meta_values
			var meta_data = this.build_meta_data($.WS_Form.actions[this.action.id], $.WS_Form.meta_keys);
			for(var meta_key in meta_data) {

				if(!meta_data.hasOwnProperty(meta_key)) { continue; }

				// Ensure meta key is configured
				if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { continue; }

				if(build_meta) {

					var meta_value = meta_data[meta_key];
					this.action.meta[meta_key] = meta_value;

				} else {

					this.object_data_update_by_meta_key('action', this.action, meta_key);
				}
			}

			// Set events
			var action_events = $.WS_Form.settings_form.sidebars.action.events;
			for(var action_event_key in action_events) {

				if(!action_events.hasOwnProperty(action_event_key)) { continue; }

				var action_event_selector = '#wsf_action_event_' + action_event_key;
				if($(action_event_selector).is(':checked')) {

					this.action.events.push(action_event_key);
				}
			}
		}

		// Write to row data
		this.action_data[1] = JSON.stringify(this.action);
	}

	// Build meta data for an object
	$.WS_Form.prototype.build_meta_data = function(meta_data, meta_keys, return_array) {

		if(typeof(return_array) == 'undefined') { return_array = []; }

		for(var key in meta_data) {

			if(!meta_data.hasOwnProperty(key)) { continue; }

			var value = meta_data[key];

			if(typeof(value) === 'object') {

				if(key === 'meta_keys') {

					for(var meta_key_index in value) {

						if(!value.hasOwnProperty(meta_key_index)) { continue; }

						var meta_key = value[meta_key_index];

						// Skip unknown meta_keys
						if(typeof(meta_keys[meta_key]) === 'undefined') { continue; }

						// Skip dummy entries
						if((typeof(meta_keys[meta_key]['dummy'] !== 'undefined') && meta_keys[meta_key]['dummy'])) { continue; }

						// Get default meta value
						if(typeof(meta_keys[meta_key]['default']) !== 'undefined') {

							var meta_value = meta_keys[meta_key]['default'];

						} else {

							var meta_value = '';
						}

						// Handle boolean values
						meta_value = (typeof(meta_value) === 'boolean') ? (meta_value ? 'on' : '') : meta_value;

						// Handle key changes
						if(typeof(meta_keys[meta_key]['key']) !== 'undefined') {

							meta_key = meta_keys[meta_key]['key'];
						}

						// Add to return array
						return_array[meta_key] = meta_value;
					}

				} else {

					// Follow
					return_array = this.build_meta_data(value, meta_keys, return_array);
				}
			}
		}

		return return_array;
	}

	// Sidebar - Title
	$.WS_Form.prototype.sidebar_title = function(sidebar_icon, sidebar_label, sidebar_compatibility_html, sidebar_kb_html, sidebar_field_id_html, sidebar_expand, sidebar_logo_html) {

		if(typeof(sidebar_compatibility_html) === 'undefined') { sidebar_compatibility_html = ''; }
		if(typeof(sidebar_kb_html) === 'undefined') { sidebar_kb_html = ''; }
		if(typeof(sidebar_field_id_html) === 'undefined') { sidebar_field_id_html = ''; }
		if(typeof(sidebar_expand) === 'undefined') { sidebar_expand = false; }
		if(typeof(sidebar_logo_html) === 'undefined') { sidebar_logo_html = ''; }

		// Expand / Contract
		if(sidebar_expand) {

			var expand_contract = '<div data-action="wsf-sidebar-expand"' + this.tooltip(this.language('sidebar_expand'), 'bottom-right') + '>' + this.svg('expand') + '</div>';
			expand_contract += '<div data-action="wsf-sidebar-contract"' + this.tooltip(this.language('sidebar_contract'), 'bottom-right') + '>' + this.svg('contract') + '</div>';

		} else {

			var expand_contract = '';
		}

		return '<div class="wsf-sidebar-header"><div class="wsf-sidebar-icon">' + sidebar_icon + '</div><h2>' + this.esc_html(sidebar_label) + '</h2>' + sidebar_field_id_html + sidebar_kb_html + sidebar_compatibility_html + expand_contract + sidebar_logo_html + '</div>';
	}

	// Sidebar - Open
	$.WS_Form.prototype.sidebar_open = function(id) {

		// Close the current side bar if it is different from the id being opened
		var sidebar_current = $('#wsf-sidebars').attr('data-current');

		// Reset sidebar resize
		this.sidebar_resize_reset();

		if((typeof(sidebar_current) !== 'undefined') && (sidebar_current != id)) {

			// Different sidebar requested
			var meta_key_close_function = 'sidebar_' + sidebar_current + '_close';
			if(typeof(window[meta_key_close_function]) === 'function') {

				window[meta_key_close_function]($.WS_Form.this);
			}

			// Close current sidebar
			this.sidebar_close(sidebar_current);

			if(sidebar_current === 'conditional') {

				// Reset conditional highlight row index
				this.conditional_highlight_row_index = false;

				// Render conditional logic icons
				this.conditional_icons_build(true);
			}
		}

		// Add editing class to sidebar button
		$('[data-action-sidebar="' + this.esc_selector(id) + '"]').addClass('wsf-editing');

		// Initial tab
		if(ws_form_settings.sidebar_tab_key !== false) {

			$('[data-wsf-tab-key="' + this.esc_selector(ws_form_settings.sidebar_tab_key) + '"]', $('#wsf-sidebar-' + id)).trigger('click');
			ws_form_settings.sidebar_tab_key = false;
		}

		// Sidebar - Open
		$('#wsf-sidebar-' + id).removeClass('wsf-sidebar-closed').addClass('wsf-sidebar-open');

		// Set current side bar open
		$('#wsf-sidebars').attr('data-current', id);

		// Reset scrolling
		$('.wsf-sidebar-inner').scrollTop(0);

		// Overflow hidden to improve touch scrolling in sidebar
		if(window.matchMedia('(max-width: 600px)').matches) {

			$('html').css({'overflow':'hidden'});
			$('body').css({'overflow':'auto','-webkit-overflow-scrolling':'touch'});
		}

		// Close variable helper
		this.variable_helper_modal_close();
	}

	// Sidebar - Close
	$.WS_Form.prototype.sidebar_close = function(id) {

		// Remove editing class from button
		$('[data-action-sidebar="' + this.esc_selector(id) + '"]').removeClass('wsf-editing').trigger('blur');

		// Sidebar - Close all
		$('.wsf-sidebar').addClass('wsf-sidebar-closed').removeClass('wsf-sidebar-open');

		// Remove sidebar attribute
		$('#wsf-sidebars').removeAttr('data-current');

		// Overflow hidden to improve touch scrolling in sidebar
		if(window.matchMedia('(max-width: 600px)').matches) {

			$('html').css({'overflow':''});
			$('body').css({'overflow':'','-webkit-overflow-scrolling':''});
		}

		// Close variable helper
		this.variable_helper_modal_close();
	}

	// Sidebar - Reset
	$.WS_Form.prototype.sidebar_reset = function() {

		// Reset
		if(window.matchMedia('(min-width: ' + $.WS_Form.this.mobile_min_width + ')').matches) {

			// Get initial sidebar to open
			var id = ws_form_settings.sidebar_reset_id;
			ws_form_settings.sidebar_reset_id = 'toolbox';

			// Open sidebar
			var meta_key_open_function = 'sidebar_' + id + '_open';
			if(typeof(window[meta_key_open_function]) === 'function') {

				// Get dom objects
				var obj_outer = $('#wsf-sidebar-' + id);
				var obj_inner = $('.wsf-sidebar-inner', obj_outer);

				window[meta_key_open_function](this, null, null);

			} else {

				// Open
				this.sidebar_open(id);
			}

		} else {

			// Mobile
			var sidebar_current = $('#wsf-sidebars').attr('data-current');
			this.sidebar_close(sidebar_current);
		}
	}

	// Sidebar - Reset - Mobile
	$.WS_Form.prototype.sidebar_reset_mobile = function() {

		// Close if at mobile breakpoint
		if(!window.matchMedia('(min-width: ' + $.WS_Form.this.mobile_min_width + ')').matches) {

			// Mobile
			var sidebar_current = $('#wsf-sidebars').attr('data-current');
			this.sidebar_close(sidebar_current);
		}
	}

	// Sidebars - Render
	$.WS_Form.prototype.sidebars_render = function() {

		var sidebars = $.WS_Form.settings_form['sidebars'];
		for(var sidebar_key in sidebars) {

			if(!sidebars.hasOwnProperty(sidebar_key)) { continue; }

			this.sidebar_render(sidebar_key);
		}

		this.sidebar_expand_contract_init();
	}

	// Sidebar - Expand / Contract init
	$.WS_Form.prototype.sidebar_expand_contract_init = function() {

		// Expand button event
		$('[data-action="wsf-sidebar-expand"]', $('#wsf-sidebars')).on('click', function() {

			$.WS_Form.this.sidebar_expanded_obj = $(this).closest('.wsf-sidebar');
			$.WS_Form.this.sidebar_expanded_obj.addClass('wsf-sidebar-expanded');
		});

		// Contract button event
		$('[data-action="wsf-sidebar-contract"]', $('#wsf-sidebars')).on('click', function() {

			$.WS_Form.this.sidebar_resize_reset();
		});
	}

	// Sidebar - Render
	$.WS_Form.prototype.sidebar_render = function(id) {

		var tab_count = 0;

		// Add wrapper
		this.sidebar_wrapper_add(id);

		var sidebar_config = $.WS_Form.settings_form['sidebars'][id];

		var sidebar_static = (typeof(sidebar_config.static) !== 'undefined') ? sidebar_config.static : false;
		var sidebar_buttons = (typeof(sidebar_config.buttons) !== 'undefined') ? sidebar_config.buttons : false;
		var sidebar_nav = (typeof(sidebar_config.nav) !== 'undefined') ? sidebar_config.nav : false;
		var sidebar_expand = (typeof(sidebar_config.expand) !== 'undefined') ? sidebar_config.expand : false;
		var sidebar_url = (typeof(sidebar_config.url) !== 'undefined') ? sidebar_config.url : false;
		var sidebar_label = (typeof(sidebar_config.label) !== 'undefined') ? sidebar_config.label : 'Title';
		var sidebar_icon = (typeof(sidebar_config.icon) !== 'undefined') ? sidebar_config.icon : 'default';

		// Create nav button
		if(sidebar_nav) {

			if(sidebar_url) {

				var sidebar_button_html = '<li data-action-sidebar="' + this.esc_attr(id) + '"' + this.tooltip(sidebar_label, 'bottom-center') + ' class="wsf-pro-required"><a href="' + this.get_plugin_website_url(sidebar_url, 'nav') + '" target="_blank" title="' + this.esc_attr(sidebar_label) + '">' + this.svg(sidebar_icon) + '</a></li>';

			} else {

				var sidebar_button_html = '<li data-action-sidebar="' + this.esc_attr(id) + '"' + this.tooltip(sidebar_label, (id === 'form' ? 'bottom-right' : 'bottom-center')) + '>' + this.svg(sidebar_icon) + '</li>';
			}
			$('#wsf-header .wsf-settings').prepend(sidebar_button_html);
		}

		// Direct link for icon?
		if(sidebar_url) { return; }

		// Get dom objects
		var obj_outer = $('#wsf-sidebar-' + id);

		// Build knowledge base HTML
		if((typeof(sidebar_config.kb_url) !== 'undefined')) {

			var kb_url = this.get_plugin_website_url(sidebar_config.kb_url, 'sidebar');
			var sidebar_kb_html = '<a class="wsf-kb-url" href="' + this.esc_attr(kb_url) + '" target="_blank"' + this.tooltip(this.language('field_kb_url'), 'bottom-right') + ' tabindex="-1">' + this.svg('question-circle') + '</a>';
		}

		// Build logo HTML
		var sidebar_logo_html = (typeof(sidebar_config.logo) !== 'undefined') ? sidebar_config.logo : '';

		obj_outer.html(this.sidebar_title(this.svg(sidebar_icon), sidebar_label, '', sidebar_kb_html, '', sidebar_expand, sidebar_logo_html));

		// Render static sidebar content
		if(sidebar_static && (typeof(sidebar_config.meta) !== 'undefined')) {

			// Clear sidebar caches
			this.sidebar_cache_clear(obj_outer);
			var sidebar_return = this.sidebar_html('form', this.form_id, this.form, sidebar_config.meta, false, true, true, false, sidebar_buttons);
			var sidebar_html_tabs = sidebar_return.html_tabs;
			var sidebar_html = sidebar_return.html;
			var sidebar_html_buttons = sidebar_return.html_buttons;
			var sidebar_inits = sidebar_return.inits;

			// Tabs
			obj_outer.append(sidebar_html_tabs);

			// Inner
			obj_outer.append('<div class="wsf-sidebar-inner">' + sidebar_html + '</div>');

			// Buttons
			obj_outer.append(sidebar_html_buttons);

			// Get inner
			var obj_inner = $('.wsf-sidebar-inner', obj_outer);

			// Initialize
			this.sidebar_inits(sidebar_inits, obj_outer, obj_inner);
		}

		// Open action
		$('[data-action-sidebar="' + this.esc_selector(id) + '"]').on('click', function() {

			// Save changes on any open objects
			$.WS_Form.this.object_save_changes();

			var id = $(this).attr('data-action-sidebar');
			var sidebar_current = $('[data-action-sidebar].wsf-editing').first().attr('data-action-sidebar');

			if(id != sidebar_current) {

				// Open
				var meta_key_open_function = 'sidebar_' + id + '_open';
				if(typeof(window[meta_key_open_function]) === 'function') {

					// Get dom objects
					var obj_outer = $('#wsf-sidebar-' + id);
					var obj_inner = $('.wsf-sidebar-inner', obj_outer);

					window[meta_key_open_function]($.WS_Form.this, obj_inner, $(this));

				} else {

					// Open
					$.WS_Form.this.sidebar_open(id);
				}

			} else {

				// Toggle
				var meta_key_toggle_function = 'sidebar_' + id + '_toggle';
				if(typeof(window[meta_key_toggle_function]) === 'function') {

					// Get dom objects
					var obj_outer = $('#wsf-sidebar-' + id);
					var obj_inner = $('.wsf-sidebar-inner', obj_outer);

					window[meta_key_toggle_function]($.WS_Form.this, obj_inner, $(this));

				} else {

					// Reset sidebar
					$.WS_Form.this.sidebar_reset();
				}
			}
		});
	}

	// Sidebar - Resize - Reset
	$.WS_Form.prototype.sidebar_resize_reset = function(sidebar_obj) {

		if($.WS_Form.this.sidebar_expanded_obj !== false) {

			$.WS_Form.this.sidebar_expanded_obj.removeClass('wsf-sidebar-expanded');
		}

		$.WS_Form.this.sidebar_expanded_obj = false;
	}

	// Sidebar - Add wrapper
	$.WS_Form.prototype.sidebar_wrapper_add = function(id) {

		var sidebar_html = '<div id="wsf-sidebar-' + id + "\" class=\"wsf-sidebar wsf-sidebar-closed\"></div>\n\n";
		$('#wsf-sidebars').append(sidebar_html);
	}

	// Sidebar - History - Init
	$.WS_Form.prototype.sidebar_form_history = function() {

		var obj = $('.wsf-form-history ul');

		// Clear history ul
		obj.empty();

		// Render each history li (last first)
		for(var i = (this.form_history.length - 1); i >= 0 ; i--) {

			var form_history_single = this.form_history[i].history;

			// Build description
			var history_description = '';

			if(i == 0) {

				history_description = $.WS_Form.settings_form.history.initial;

			} else {

				// Get verb
				var verb = $.WS_Form.settings_form.history.method[form_history_single.method];

				// Get object name
				var direct_object = '';

				if(typeof(form_history_single.id) !== 'undefined') {

					var object_id = form_history_single.id;

					// Add label
					if(typeof(form_history_single.label) !== 'undefined') {

						direct_object = ' <span class="wsf-history-highlight">' + this.esc_html(form_history_single.label) + '</span>';
					}
				}

				history_description = verb + direct_object;
			}

			// Build history HTML (form_history_single.date + '<br />' +)
			var history_html = '<li data-id="' + this.esc_attr(i) + '"' + ((this.history_index == i) ? ' class="wsf-history-current"' : '') + '><div class="date">' + form_history_single.time + '</div><div class="description">' + history_description + '</div></li>';

			// Add to obj
			obj.append(history_html);

			// Get newly added li
			var newLI = obj.find('li[data-id="' + this.esc_selector(i) + '"]').first();

			// Mouse - Enter
			if(!this.touch_device) {

				newLI.on('mouseenter', function() {

					var history_index = $(this).attr('data-id');

					// Pull the history
					$.WS_Form.this.history_pull(history_index);

					// Update history classes
					$.WS_Form.this.sidebar_form_history_classes(history_index);
				});
			}

			// Click
			newLI.on('click', function() {

				// Get history indexThis
				$.WS_Form.this.history_index = parseInt($(this).attr('data-id'), 10);

				// Pull the history
				$.WS_Form.this.history_pull($.WS_Form.this.history_index, true, true);

				// Update history classes
				$.WS_Form.this.sidebar_form_history_classes($.WS_Form.this.history_index);
			});
		}

		// Update history classes
		this.sidebar_form_history_classes();
	}

	// History - Update classes
	$.WS_Form.prototype.sidebar_form_history_classes = function(index) {

		if(typeof(index) === 'undefined') { index = this.history_index; }

		$('.wsf-form-history ul li').each(function() {

			// Reset
			$(this).removeClass('wsf-history-current').removeClass('wsf-history-undo').removeClass('wsf-history-redo');

			var history_id = parseInt($(this).attr('data-id'), 10);
			if(history_id == index) { $(this).addClass('wsf-history-current'); }
			if(history_id > index) { $(this).addClass('wsf-history-redo'); }
			if(history_id < index) { $(this).addClass('wsf-history-undo'); }
		});
	}

	// History - Push
	$.WS_Form.prototype.history_push = function(data) {

		// Update preview window
		this.form_preview_update();

		// Clear history after current history index
		this.form_history = this.form_history.slice(0, (this.history_index + 1));

		var data = $.extend(true, {}, data); // Deep clone

		// Add data to form_history
		if(typeof(data.history) !== 'undefined') {

			if(data.history !== false) {

				// New history object
				var history_object = {

					'form':	data.form,
					'history':	data.history
				};

				// Push form to history
				this.form_history.push(history_object);
			}
		}

		// Calculate number of history steps
		this.history_index = (this.form_history.length - 1);

		// Re-render history
		this.sidebar_form_history();

		// Update undo / redo buttons
		this.undo_redo_update();

		// Breakpoint buttons
		this.breakpoint_buttons();
	}

	// History - Pull
	$.WS_Form.prototype.history_pull = function(index, push_to_api, preview_update) {

		if(typeof(push_to_api) === 'undefined') { push_to_api = false; }
		if(typeof(preview_update) === 'undefined') { preview_update = false; }

		// Loader on
		this.loader_on();

		// Reset sidebar
		this.sidebar_reset();

		// Get previous state
		this.form = $.extend(true, {}, this.form_history[index]['form']);

		// Build data cache
		this.data_cache_build();

		if(push_to_api) {

			// Push form
			this.form_put(true, true, true, preview_update);	// true = Full push / Does form_build as part of form_put

		} else {

			// Render form
			this.form_build();

			// Loader off
			this.loader_off();
		}
	}

	// Button - Undo (Reduces history_index by one)
	$.WS_Form.prototype.undo = function() {

		// Decrement history index
		if(this.history_index == 0) { return false; }

		// Make sure we aren't at the beginning of the history
		var history_length = this.form_history.length;

		// If there is 1 or less length, we cannot undo. Index 0 / Length 1 = original form.
		if(history_length <= 1) { return false; }

		// Reduce history index by 1
		this.history_index--;

		// Process the history at index
		this.history_pull(this.history_index, true, true);

		// Re-render history
		this.sidebar_form_history();

		// Re-render undo redo buttons
		this.undo_redo_update();
	}

	// Button - Redo (Increases history_index by one)
	$.WS_Form.prototype.redo = function() {

		// Calculate number of history steps
		var history_length = this.form_history.length;

		// Make sure we aren't already at end of history
		if(this.history_index == (history_length - 1)) { return false; }

		// Increase history index by 1
		this.history_index++;

		// Process the history at index
		this.history_pull(this.history_index, true, true);

		// Re-render history
		this.sidebar_form_history();

		// Re-render undo redo buttons
		this.undo_redo_update();
	}

	$.WS_Form.prototype.undo_redo_update = function() {

		// Calculate number of history steps
		var history_length = this.form_history.length;

		// Render undo button
		if(this.history_index > 0) {

			// Hide number if > 99
			var history_index = (this.history_index > 99) ? '' : this.history_index;

			// Show undo button count
			$('[data-action="wsf-undo"]').removeClass('wsf-undo-inactive').find('.count').html(history_index);

		} else {

			// Hide undo button count
			$('[data-action="wsf-undo"]').addClass('wsf-undo-inactive').find('.count').html('');
		}

		// Render redo button
		if(this.history_index < (history_length - 1)) {

			// Hide number if > 99
			var history_index = (this.history_index > 99) ? '' : (history_length - 1) - this.history_index;

			// Show redo button count
			$('[data-action="wsf-redo"]').removeClass('wsf-redo-inactive').find('.count').html(history_index);

		} else {

			// Hide redo button cont
			$('[data-action="wsf-redo"]').addClass('wsf-redo-inactive').find('.count').html('');
		}
	}

	// Popover - Render
	$.WS_Form.prototype.popover = function(message, buttons, obj, confirm_function) {

		// Reset popovers
		this.popover_reset();
		$('body').addClass('wsf-scroll-lock');
		$('.wsf-group.wsf-ui-cancel').removeClass('wsf-ui-cancel');
		$('.wsf-section.wsf-ui-cancel').removeClass('wsf-ui-cancel');
		$('.wsf-field-wrapper.wsf-ui-cancel').removeClass('wsf-ui-cancel');
		$('.wsf-data-grid-group-tab.wsf-ui-cancel').removeClass('wsf-ui-cancel');

		var popover_obj = $('#wsf-popover');

		var popover_html = '<p>' + message + '</p>';

		// Add class
		obj.addClass('wsf-ui-cancel');

		// Get object type
		var object = this.get_object_type(obj);

		// Read data attributes
		var object_id = obj.attr('data-id');

		// Object specific functions
		switch(object) {

			case 'group' :

				// Add cancel class to tab
				$('.wsf-group-tab[data-id="' + this.esc_selector(object_id) + '"]').addClass('wsf-ui-cancel');

				break;

			case 'data-grid-group-tab' :

				// Add cancel class to tab content
				$($('a', obj).attr('href')).addClass('wsf-ui-cancel');

				break;			
		}

		for(var key in buttons) {

			if(!buttons.hasOwnProperty(key)) { continue; }

			var button = buttons[key];

			var button_label = button.label;
			var button_action = button.action;
			var button_class = '';

			if(typeof(button.class) !== 'undefined') { button_class = button.class; }

			popover_html += '<button class="wsf-button wsf-button-small';
			if(button_class != '') { popover_html += ' ' + button_class; }
			popover_html += '" data-action="' + this.esc_attr(button_action) + '">' + this.esc_html(button_label) + '</button>';
		}

		// Render popover
		popover_obj.html(popover_html);

		// Show popover
		popover_obj.css({ opacity: 0 });
		popover_obj.show();		// Need to show here prior to calculating width and height

		// Position popover
		var position = obj.offset();
		var popover_width = popover_obj.innerWidth();
		var popover_height = popover_obj.innerHeight();
		popover_height += 6;
		var object_width = obj.width();
		var window_width = $(window).width();

		// Calculate position of popover
		var position_left = position.left + (object_width / 2) - (popover_width / 2);
		var position_top = position.top - popover_height;

		// Ensure popover kept within boundaries
		var wpcontent_offset = $('#wpcontent').offset();
		if(position_left < wpcontent_offset.left) { position_left = wpcontent_offset.left; }
		if(position_top < wpcontent_offset.top) { position_top = wpcontent_offset.top; }
		if((position_left + popover_width) > window_width) { position_left = (window_width - popover_width); }

		// Position
		popover_obj.offset({ left: position_left, top: position_top });

		// Button event handles
		popover_obj.find('button[data-action]').each(function() {

			$(this).on('click', function(e) {

				e.preventDefault();

				var action = $(this).attr('data-action');

				switch(action) {

					case 'wsf-confirm' :

					if(typeof(confirm_function) === 'function') {

						confirm_function();
					}
					break;
				}

				// Hide 
				$.WS_Form.this.popover_reset();

				obj.removeClass('wsf-ui-cancel');

				// Get object type
				var object = $.WS_Form.this.get_object_type(obj);

				// Read data attributes
				var object_id = obj.attr('data-id');

				// Object specific functions
				switch(object) {

					case 'group' :

						// Remove cancel class from tab
						$('.wsf-group-tab[data-id="' + $.WS_Form.this.esc_selector(object_id) + '"]').removeClass('wsf-ui-cancel');

						break;

					case 'data-grid-group-tab' :

						// Remove cancel class from tab content
						$($('a', obj).attr('href')).removeClass('wsf-ui-cancel');

						break;			
				}
			});

		});

		popover_obj.css({ opacity: 1 });
	}

	// Popover - Render - Reset
	$.WS_Form.prototype.popover_reset = function(message, buttons, obj, confirm_function, id) {

		$('body').removeClass('wsf-scroll-lock');
		$('#wsf-popover').hide();
		$('#wsf-popover').html('');
	}

	// Settings - HTML
	$.WS_Form.prototype.settings_html = function(object, object_id, multiple, no_resize) {

		if(typeof(no_resize) === 'undefined') { no_resize = false; }

		var settings_html = '<ul class="wsf-settings wsf-settings-' + object + (no_resize ? ' wsf-settings-no-resize' : '') + ' wsf-ui-cancel">';

		var buttons = $.WS_Form.settings_form[object].buttons;

		for(var key in buttons) {

			if(!buttons.hasOwnProperty(key)) { continue; }

			var button = buttons[key];

			var button_name = button['name'];
			var button_icon = button['icon'];

			var button_method = button['method'];
			if(!multiple && (button_method == 'clone')) { continue; }

			settings_html += '<li data-id="' + this.esc_attr(object_id) + '" data-action="' + this.esc_attr(button_method) + '"' + this.tooltip(button_name, 'top-center') + '>' + button_icon + '</li>';
		}

		settings_html += '</ul>';

		return settings_html;
	}

	// Settings - Events
	$.WS_Form.prototype.settings_events = function(obj, object) {

		$('.wsf-settings-' + object + ' li', obj).each(function() {

			$(this).on('click', function() {

				var method_function = 'wsf_' + object + '_' + $(this).attr('data-action');

				if(typeof(window[method_function], 'function') !== 'undefined') {

					var singleObj = $('.wsf-' + object + (object == 'field' ? '-wrapper' : '') + '[data-id="' + $.WS_Form.this.esc_selector($(this).attr('data-id')) + '"]').first();

					window[method_function]($.WS_Form.this, singleObj, $(this));
				}
			});
		});
	}

	// Hover - Events
	$.WS_Form.prototype.hover_events = function(obj, object, object_id) {

		var ws_this = this;

		obj.on('mouseover mouseout', function(e) {

			if(
				($('#wsf-sidebars').attr('data-current') !== 'conditional') ||
				(typeof(ws_this.conditional_row_array[object][object_id]) === 'undefined') ||
				$(e.target).closest('[data-wsf-tooltip]').length

			) { return; }

			e.stopPropagation();

 			// Set highlighting
			for(var conditional_row_array_index in ws_this.conditional_row_array[object][object_id]) {

				if(!ws_this.conditional_row_array[object][object_id].hasOwnProperty(conditional_row_array_index)) { continue; }

				var conditional_index = ws_this.conditional_row_array[object][object_id][conditional_row_array_index];

				var conditional_row_obj = $('#wsf-sidebar-conditional tr[data-index="' + ws_this.esc_selector(conditional_index) + '"]');

				if(e.type === 'mouseover') {

					conditional_row_obj.addClass('wsf-conditional-highlight');

				} else {

					conditional_row_obj.removeClass('wsf-conditional-highlight');
				}
			}
		});
	}
	// Column size - Change - HTML
	$.WS_Form.prototype.column_size_change_html = function() {

		return '<div class="wsf-column-size wsf-ui-cancel" title="' + this.language('column_size_change') + '"></div>';
	}

	// Column size - Change - Initialize
	$.WS_Form.prototype.column_size_change_init = function(obj) {

		// Mouse down event
		obj.find('.wsf-column-size').last().on('mousedown', function() {

			// Set mouseup mode
			$.WS_Form.this.mouseup_mode = 'column_size';

			// Add class to body
			$('body').addClass('wsf-column-size-change-body');

			// Add class to field
			obj.addClass('wsf-column-size-change');

			// Remember current object being resized
			$.WS_Form.this.column_size_change_obj = obj;

			// Get width of parent
			var ul_width = obj.parent().innerWidth();

			// Find meta for field
			var object_id = obj.attr('data-id');

			// Section
			if(obj.hasClass('wsf-section')) {

				// Add column helper class
				if($.WS_Form.settings_plugin.helper_columns == 'resize') { obj.closest('.wsf-group').addClass('wsf-column-helper'); }

				var object = $.WS_Form.this.section_data_cache[object_id];
			}

			// Field
			if(obj.hasClass('wsf-field-wrapper')) {

				// Add column helper class
				if($.WS_Form.settings_plugin.helper_columns == 'resize') { obj.closest('.wsf-section').addClass('wsf-column-helper'); }

				var object = $.WS_Form.this.field_data_cache[object_id];
			}

			// If using scratch, temporarily use scratch data
			if($.WS_Form.this.object_data_scratch !== false) {

				if($.WS_Form.this.object_data_scratch.id == object_id) {

					object = $.WS_Form.this.object_data_scratch;
				}
			}

			// Get frameworks
			var framework = $.WS_Form.settings_plugin.framework;

			// Get framework
			var framework = $.WS_Form.frameworks.types[framework];

			// Get number of columns
			var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

			// Get current framework breakpoints
			var framework_breakpoints = framework.breakpoints;

			// Get current breakpoint
			var breakpoint_current = $.WS_Form.this.get_object_meta_value($.WS_Form.this.form, 'breakpoint');

			// Fallback size
			$.WS_Form.this.column_size = framework_column_count;

			// Run through breakpoints and get size of closest sibling
			var offset = 0;
			for(var breakpoint_key in framework_breakpoints) {

				if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

				var breakpoint = framework_breakpoints[breakpoint_key];

				// Column size

				// Get meta key
				var meta_key = 'breakpoint_size_' + breakpoint_key;

				// Get meta value
				var meta_value = $.WS_Form.this.get_object_meta_value(object, meta_key, '', false);
				if(meta_value != '') {

					$.WS_Form.this.column_size = parseInt(meta_value, 10);
				}

				// Offset

				// Get meta key
				var meta_key = 'breakpoint_offset_' + breakpoint_key;

				// Get meta value
				var meta_value = $.WS_Form.this.get_object_meta_value(object, meta_key, '', false);
				if(meta_value != '') {

					offset = parseInt(meta_value, 10);
				}

				// If we are at the current breakpoint, break out of loop
				if(breakpoint_key == breakpoint_current) { break; }
			}

			// Reset old breakpoint size
			$.WS_Form.this.column_size_old = $.WS_Form.this.column_size;

			// Get object left position
			var obj_offset = obj.offset();
			var obj_left = obj_offset.left;

			// Object width
			var obj_width = obj.width();

			// Calculate max size
			var column_size_max = framework_column_count - offset;

			// Resize
			$(document).on('mousemove',function(e) {

				e.preventDefault();

				$.WS_Form.this.column_size_change(e, obj, obj_left, obj_width, ul_width, object, column_size_max);
			});
		});
	}

	// Column size - Change (Called on mousemove)
	$.WS_Form.prototype.column_size_change = function(e, obj, obj_left, obj_width, ul_width, object, column_size_max) {

		if(this.column_size_change_obj !== false) {

			// Get relative x position
			if(ws_form_settings.rtl) {

				var mouse_offset_x = (obj_left + obj_width) - e.pageX;

			} else {

				var mouse_offset_x = e.pageX - obj_left;
			}
			if(mouse_offset_x < 0) { mouse_offset_x = 0; }

			// Get number of columns
			var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

			// Calculate new size
			var column_size_new = Math.round(((mouse_offset_x / ul_width) * framework_column_count) + 0.5);
			if(column_size_new < 1) { column_size_new = 1; }
			if(column_size_new > column_size_max) { column_size_new = column_size_max; }

			// If we have calculated a new size, write it to the form object and write the field class
			if(column_size_new != this.column_size) {

				// Remove old classes
				this.column_classes_render(obj, object, false);

				// Get current breakpoint
				var breakpoint = this.get_object_meta_value(this.form, 'breakpoint');

				// Update object
				this.column_size_set(object, breakpoint, column_size_new);

				// Add new classes
				this.column_classes_render(obj, object);

				var object_id = obj.attr('data-id');
				var object_type = this.get_object_type(obj);

				// Render framework sizes in sidebar?
				if(
					($.WS_Form.this.object_data_scratch !== false) &&
					$('#wsf-sidebar-' + object_type + ' .wsf-breakpoint-sizes').length
				) {

					if($.WS_Form.this.object_data_scratch.id == object_id) {

						// Render framework sizes in sidebar
						$('#wsf-sidebar-' + object_type + ' .wsf-breakpoint-sizes').get(0).render();
					}
				}

				// Remember column size
				this.column_size = column_size_new;
			}
		}
	}

	// Column size - Size - Release
	$.WS_Form.prototype.column_size_change_release = function() {

		var obj = this.column_size_change_obj;

		// Only runs if field resize in progress
		if(this.column_size_change_obj !== false) {

			// If field size has chanegd
			if(this.column_size != this.column_size_old) {

				// Loader on
				this.loader_on();

				// Get object ID
				var api_object_id = obj.attr('data-id');

				// Determine object being resized
				var api_object_type = false;
				var api_params = {};

				// Get current breakpoint
				var breakpoint = this.get_object_meta_value(this.form, 'breakpoint');

				// Section
				if(obj.hasClass('wsf-section')) {

					// Update object (To ensure we overwrite any updates from the API)
					this.column_size_set(this.section_data_cache[api_object_id], breakpoint, this.column_size);

					var object = this.section_data_cache[api_object_id];

					api_object_type = 'section';
					api_params = {'section': this.section_data_cache[api_object_id]};
				}

				// Field
				if(obj.hasClass('wsf-field-wrapper')) {

					// Update object (To ensure we overwrite any updates from the API)
					this.column_size_set(this.field_data_cache[api_object_id], breakpoint, this.column_size);

					var object = this.field_data_cache[api_object_id];

					api_object_type = 'field';
					api_params = {'field': object};

					// Optimize orientation breakpoints
					var field_type = (typeof(object.type) !== 'undefined') ? object.type : false;
					switch(field_type) {

						case 'checkbox' :
						case 'radio' :

							this.orientation_breakpoint_optimize(object);
					}
				}

				if(api_object_type === false) { return false; }

				// Optimize
				this.breakpoint_optimize(object);

				// Add history method
				api_params['history_method'] = 'put_resize';

				// Call AJAX request
				this.api_call(api_object_type + '/' + api_object_id + '/put/', 'POST', api_params, function(response) {

					// Loader off
					$.WS_Form.this.loader_off();
				});
			}

			// Remove class from field
			obj.removeClass('wsf-column-size-change');

			// Remove column helper class
			if($.WS_Form.settings_plugin.helper_columns == 'resize') { $('.wsf-column-helper').removeClass('wsf-column-helper'); }

			// Remove class from body
			$('body').removeClass('wsf-column-size-change-body');

			// Reset
			this.column_size_change_obj = false;
			this.column_size = 0;
			this.mouseup_mode = false;

			// Unbind mousemove event
			$(document).off('mousemove');
		}
	}

	// Column size - Set
	$.WS_Form.prototype.column_size_set = function(object, breakpoint, size) {

		// Set breakpoint size meta in object
		this.set_object_meta_value(object, 'breakpoint_size_' + breakpoint, size);
	}

	// Column size - Change - HTML
	$.WS_Form.prototype.offset_change_html = function() {

		return '<div class="wsf-offset wsf-ui-cancel" title="' + this.language('offset_change') + '"></div>';
	}

	// Column size - Change - Initialize
	$.WS_Form.prototype.offset_change_init = function(obj) {

		// Mouse down event
		obj.find('.wsf-offset').last().on('mousedown', function() {

			// Set mouseup mode
			$.WS_Form.this.mouseup_mode = 'offset';

			// Add class to body
			$('body').addClass('wsf-offset-change-body');

			// Add class to field
			obj.addClass('wsf-offset-change');

			// Remember current object being resized
			$.WS_Form.this.offset_change_obj = obj;

			// Get width of parent
			var ul_width = obj.parent().innerWidth();

			// Find meta for field
			var object_id = parseInt(obj.attr('data-id'), 10);

			// Section
			if(obj.hasClass('wsf-section')) {

				// Add column helper class
				if($.WS_Form.settings_plugin.helper_columns == 'resize') { obj.closest('.wsf-group').addClass('wsf-column-helper'); }

				var object = $.WS_Form.this.section_data_cache[object_id];
			}

			// Field
			if(obj.hasClass('wsf-field-wrapper')) {

				// Add column helper class
				if($.WS_Form.settings_plugin.helper_columns == 'resize') { obj.closest('.wsf-section').addClass('wsf-column-helper'); }

				var object = $.WS_Form.this.field_data_cache[object_id];
			}

			// If using scratch, temporarily use scratch data
			if($.WS_Form.this.object_data_scratch !== false) {

				if($.WS_Form.this.object_data_scratch.id == object_id) {

					object = $.WS_Form.this.object_data_scratch;
				}
			}

			// Get frameworks
			var framework = $.WS_Form.settings_plugin.framework;

			// Get framework
			var framework = $.WS_Form.frameworks.types[framework];

			// Get number of columns
			var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

			// Get current framework breakpoints
			var framework_breakpoints = framework.breakpoints;

			// Get current breakpoint
			var breakpoint_current = $.WS_Form.this.get_object_meta_value($.WS_Form.this.form, 'breakpoint');

			// Fallback size
			$.WS_Form.this.offset = 0;

			// Run through breakpoints and get size of closest sibling
			var breakpoint_index = 0;
			for(var breakpoint_key in framework_breakpoints) {

				if(!framework_breakpoints.hasOwnProperty(breakpoint_key)) { continue; }

				var breakpoint = framework_breakpoints[breakpoint_key];

				if(breakpoint_index == 0) {

					var column_size = (typeof(breakpoint.column_size_default) !== 'undefined') ? (breakpoint.column_size_default == 'column_count' ? framework_column_count : breakpoint.column_size_default) : column_size_default;
				}

				// Offset

				// Get meta key
				var meta_key = 'breakpoint_offset_' + breakpoint_key;

				// Get meta value
				var meta_value = $.WS_Form.this.get_object_meta_value(object, meta_key, '', false);
				if(meta_value != '') {

					$.WS_Form.this.offset = parseInt(meta_value, 10);
				}

				// Column size

				// Get meta key
				var meta_key = 'breakpoint_size_' + breakpoint_key;

				// Get meta value
				var meta_value = $.WS_Form.this.get_object_meta_value(object, meta_key, '', false);
				if(meta_value != '') {

					column_size = parseInt(meta_value, 10);
				}

				// If we are at the current breakpoint, break out of loop
				if(breakpoint_key == breakpoint_current) { break; }

				breakpoint_index++;
			}

			// Reset old breakpoint size
			$.WS_Form.this.offset_old = $.WS_Form.this.offset;

			// Get object left position
			var obj_offset = obj.offset();
			var obj_left = obj_offset.left;

			// Get object width
			var obj_width = obj.width();

			// Calculate max size
			var offset_max = framework_column_count - column_size;

			// Resize
			$(document).on('mousemove',function(e) {

				e.preventDefault();

				$.WS_Form.this.offset_change(e, obj, obj_left, obj_width, ul_width, object, offset_max);
			});
		});
	}

	// Column size - Change (Called on mousemove)
	$.WS_Form.prototype.offset_change = function(e, obj, obj_left, obj_width, ul_width, object, offset_max) {

		if(this.offset_change_obj !== false) {

			// Get relative x position
			if(ws_form_settings.rtl) {

				var mouse_offset_x = (obj_left + obj_width) - e.pageX;

			} else {

				var mouse_offset_x = e.pageX - obj_left;
			}

			// Get number of columns
			var framework_column_count = parseInt($.WS_Form.settings_plugin.framework_column_count, 10);

			// Calculate new size
			var offset_new = $.WS_Form.this.offset_old + Math.round(((mouse_offset_x / ul_width) * framework_column_count) + 0.5) - 1;

			if(offset_new < 0) { offset_new = 0; }
			if(offset_new > offset_max) { offset_new = offset_max; }

			// If we have calculated a new size, write it to the form object and write the field class
			if(offset_new != this.offset) {

				// Remove old classes
				this.column_classes_render(obj, object, false);

				// Get current breakpoint
				var breakpoint = this.get_object_meta_value(this.form, 'breakpoint');

				// Update object
				this.offset_set(object, breakpoint, offset_new);

				// Add new classes
				this.column_classes_render(obj, object);

				var object_id = parseInt(obj.attr('data-id'), 10);
				var object_type = this.get_object_type(obj);

				// Render framework sizes in sidebar?
				if(
					($.WS_Form.this.object_data_scratch !== false) &&
					$('#wsf-sidebar-' + object_type + ' .wsf-breakpoint-sizes').length
				) {

					if($.WS_Form.this.object_data_scratch.id == object_id) {

						// Render framework sizes in sidebar
						$('#wsf-sidebar-' + object_type + ' .wsf-breakpoint-sizes').get(0).render();
					}
				}

				// Remember breakpoint
				this.offset = offset_new;
			}
		}
	}

	// Column size - Size - Release
	$.WS_Form.prototype.offset_change_release = function() {

		var obj = this.offset_change_obj;

		// Only runs if field resize in progress
		if(this.offset_change_obj !== false) {

			// If field size has chanegd
			if(this.offset != this.offset_old) {

				// Loader on
				this.loader_on();

				// Get object ID
				var api_object_id = parseInt(obj.attr('data-id'), 10);

				// Determine object being resized
				var api_object_type = false;
				var api_params = {};

				// Get current breakpoint
				var breakpoint = this.get_object_meta_value(this.form, 'breakpoint');

				// Section
				if(obj.hasClass('wsf-section')) {

					// Update object (To ensure we overwrite any updates from the API)
					this.offset_set(this.section_data_cache[api_object_id], breakpoint, this.offset);

					api_object_type = 'section';
					api_params = {'section': this.section_data_cache[api_object_id]};
				}

				// Field
				if(obj.hasClass('wsf-field-wrapper')) {

					// Update object (To ensure we overwrite any updates from the API)
					this.offset_set(this.field_data_cache[api_object_id], breakpoint, this.offset);

					api_object_type = 'field';
					api_params = {'field': this.field_data_cache[api_object_id]};
				}

				if(api_object_type === false) { return false; }

				// Add history method
				api_params['history_method'] = 'put_offset';

				// Call AJAX request
				this.api_call(api_object_type + '/' + api_object_id + '/put/', 'POST', api_params, function(response) {

					// Loader off
					$.WS_Form.this.loader_off();
				});
			}

			// Remove class from field
			obj.removeClass('wsf-offset-change');

			// Remove column helper class
			if($.WS_Form.settings_plugin.helper_columns == 'resize') { $('.wsf-column-helper').removeClass('wsf-column-helper'); }

			// Remove class from body
			$('body').removeClass('wsf-offset-change-body');

			// Reset
			this.offset_change_obj = false;
			this.offset = 0;
			this.mouseup_mode = false;

			// Unbind mousemove event
			$(document).off('mousemove');
		}
	}

	// Column size - Set
	$.WS_Form.prototype.offset_set = function(object, breakpoint, size) {

		// Set breakpoint size meta in object
		this.set_object_meta_value(object, 'breakpoint_offset_' + breakpoint, size.toString());
	}

	// Initialize jQuery UI
	$.WS_Form.prototype.init_ui = function() {

		// jQuery UI Sortable

		// Groups (tabs) sortable
		this.groups_sortable();

		// Sections sortable
		this.sections_sortable();

		// Fields sortable
		this.fields_sortable();

		// Group tabs droppable
		this.group_tabs_droppable();

		// Blank fields
		this.object_blank_update();
	}

	// Make form groups sortable
	$.WS_Form.prototype.groups_sortable = function() {

		// Get wsf-group-tabs
		var obj_group_tabs = $('.wsf-group-tabs');

		// Section - Sortable (Make fields within section sortable)
		obj_group_tabs.sortable({

			cursor:				'move',
			scroll: 			false,
			forceHelperSize:	true,
			placeholder:		'wsf-group-tab-placeholder',
			cancel:				'.wsf-ui-cancel, .wsf-disabled, input[type=text]:not([readonly]), li:not([data-id])',
			items:				'> li:not(.wsf-ui-cancel)',

			start: function (e, ui) {

				// Get object being sorted
				var obj = ui.placeholder;
				var height = ui.helper.height();
				var width = ui.helper.outerWidth();
				var styles = [
					'height: ' + height + 'px',
					'width: ' + width + 'px'
				].join(';');

				// Apply styles
				obj.attr('style', styles);

				// Get next sibling ID (0 = Last or only element in group)
				$.WS_Form.this.next_sibling_id_old = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;

				// Set dragging
				$.WS_Form.this.dragging = true;
			},

			stop: function(e, ui) {

				// Push field sort index to AJAX
				$.WS_Form.this.group_put_sort_index(ui.item);

				// Set dragging
				$.WS_Form.this.dragging = false;
			}

		}).disableSelection();
	}

	// Make group sections sortable
	$.WS_Form.prototype.sections_sortable = function() {

		// Group - Sortable (Make sections within group sortable)
		$('.wsf-sections').sortable({

			tolerance: 			'pointer',
			forceHelperSize: 	true,
			placeholder: 		'wsf-section-placeholder',
			items: 				'> li',
			cancel: 			'.wsf-section-blank, .wsf-ui-cancel, input[type=text]:not([readonly]), li:not([data-id])',
			connectWith: 		'.wsf-sections',

			start: function (e, ui) {

				// Get object being sorted
				var obj = ui.placeholder;

				// Build placeholder style
				ui.helper.css('height', 'auto');
				var height = ui.helper.height();
				var margin_left = ui.helper.css('background-size');
				var width = ui.helper.css('max-width');
				var styles = [
					'height: ' + height + 'px',
					'-webkit-box-flex: 0',
					'-ms-flex: 0 0 ' + width,
					'flex: 0 0 ' + width,
					'margin-left: ' + margin_left,
					'max-width: ' + width
				].join(';');

				// Apply style
				obj.attr('style', styles);

				// Refresh positions
				$('.wsf-sections').sortable('refreshPositions');

				// Get next sibling ID (0 = Last or only element in group)
				$.WS_Form.this.next_sibling_id_old = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;
				$.WS_Form.this.group_id_old = obj.closest('.wsf-group').attr('data-id');

				// Set dragging
				$.WS_Form.this.dragging = true;
			},

			over: function(e, ui) {

				// Blank update
				$.WS_Form.this.object_blank_update();

				// Set helper height
				ui.helper.css('height', 'auto');
				var height = ui.helper.height();
				ui.placeholder.height(height);

				if($.WS_Form.this.dragged_section) {

					$.WS_Form.this.dragged_section_in_group = true;
				}
			},

			out: function(e, ui) {

				// Blank update
				$.WS_Form.this.object_blank_update();

				if($.WS_Form.this.dragged_section) {

					$.WS_Form.this.dragged_section_in_group = false;
				}
			},

			stop: function(e, ui) {

				// Blank update
				$.WS_Form.this.object_blank_update();

				if($.WS_Form.this.dragged_section) {

					// Get height of helper
					var height = $('.wsf-section-inner', $.WS_Form.this.dragged_section).height();

					// Get template title
					var label = $('.wsf-template-title tspan', $.WS_Form.this.dragged_section).html();

					// Render section while it is loaded
					$.WS_Form.this.dragged_section.html('<div class="wsf-section-inner"><div class="wsf-section-label"><input type="text" value="' + $.WS_Form.this.esc_attr(label) + '" readonly></div><div class="wsf-section-type">' + $.WS_Form.this.language('section') + '</div></div>').removeAttr('style');
					$('.wsf-section-inner', $.WS_Form.this.dragged_section).attr('style', 'height:' + height + 'px');

					// Push new section to AJAX
					$.WS_Form.this.template_section_post($.WS_Form.this.dragged_section);

					// Init UI
					$.WS_Form.this.init_ui();

				} else {

					// Push section sort index to AJAX
					$.WS_Form.this.section_put_sort_index(ui.item);
				}

				// Reset section
				$.WS_Form.this.dragging = false;
				$.WS_Form.this.dragged_section = null;
				$.WS_Form.this.dragged_section_in_group = false;
			}

		}).disableSelection();
	}

	// Make section fields sortable
	$.WS_Form.prototype.fields_sortable = function() {

		// Section - Sortable (Make fields within section sortable)
		$('.wsf-fields').sortable({

			tolerance: 			'pointer',
			forceHelperSize: 	true,
			placeholder: 		'wsf-field-placeholder',
			items: 				'> li',
			cancel: 			'.wsf-field-blank, .wsf-ui-cancel, input[type=text]:not([readonly]), li:not([data-id])',
			connectWith: 		'.wsf-fields',

			start: function (e, ui) {

				if(!$.WS_Form.this.dragged_field) {

					// Get object being sorted
					var obj = ui.placeholder;

					// Build placeholder style
					var height = ui.helper.height();
					var margin_left = ui.helper.css('background-size');
					var width = ui.helper.css('max-width');
					var styles = [
						'height: ' + height + 'px',
						'-webkit-box-flex: 0',
						'-ms-flex: 0 0 ' + width,
						'flex: 0 0 ' + width,
						'margin-left: ' + margin_left,
						'max-width: ' + width
					].join(';');

					// Apply style
					obj.attr('style', styles);

					// Refresh positions
					$('.wsf-fields').sortable('refreshPositions');

					// Get next sibling ID (0 = Last or only element in group)
					$.WS_Form.this.next_sibling_id_old = (typeof(obj.next().attr('data-id')) !== 'undefined') ? obj.next().attr('data-id') : 0;
					$.WS_Form.this.section_id_old = obj.closest('.wsf-section').attr('data-id');

					// Set dragging
					$.WS_Form.this.dragging = true;
				}
			},

			over: function(e, ui) {

				// Blank update
				$.WS_Form.this.object_blank_update();

				ui.helper.css('height', 'auto');
				var height = ui.helper.height();
				ui.placeholder.height(height);

				if($.WS_Form.this.dragged_field) {

					$.WS_Form.this.dragged_field_in_section = true;

					$.WS_Form.this.dragged_field.width($(ui.placeholder).width());

				} else {

					ui.helper.width($(ui.placeholder).width());
				}
			},

			out: function(e, ui) {

				// Blank update
				$.WS_Form.this.object_blank_update();

				if($.WS_Form.this.dragged_field) {

					$.WS_Form.this.dragged_field_in_section = false;
				}
			},

			stop: function(e, ui) {

				$.WS_Form.this.object_blank_update();

				if($.WS_Form.this.dragged_field) {

					// Remove style so li inherits fields styling
					$.WS_Form.this.dragged_field.removeAttr('style');

					// Push new field to AJAX
					$.WS_Form.this.field_post($.WS_Form.this.dragged_field);

					// Init UI
					$.WS_Form.this.init_ui();

				} else {

					// Push field sort index to AJAX
					$.WS_Form.this.field_put_sort_index(ui.item);
				}

				// Reset dragged_field
				$.WS_Form.this.dragging = false;
				$.WS_Form.this.dragged_field = null;
				$.WS_Form.this.dragged_field_in_section = false;
			}

		}).disableSelection();
	}

	// Make the group tabs droppable so that sections and fields can be dropped into them
	$.WS_Form.prototype.group_tabs_droppable = function() {

		$('.wsf-group-tab').droppable({

			accept: '.wsf-sections li.wsf-section[data-id], .wsf-fields li.wsf-field-wrapper[data-id]',
			hoverClass: 'wsf-group-tab-hover',
			tolerance: 'pointer',
			over: function(e, ui) {

				// Show tab
				$('a', e.target).trigger('click');

				// Move helper to current group
				if(ui.helper.hasClass('wsf-section')) {

					var ul_dummy_selector = '.wsf-sections';
				}
				if(ui.helper.hasClass('wsf-field-wrapper')) {

					var ul_dummy_selector = '.wsf-fields';
				}

				if(typeof(ul_dummy_selector) !== 'undefined') {

					// Append to appropriate dummy UL container
					var ul_dummy = $($(this).find('a').attr('href')).find(ul_dummy_selector).first();
					ui.draggable.appendTo(ul_dummy).show();

					// Refresh positions
					$('.wsf-sections').sortable('refreshPositions');
					$('.wsf-fields').sortable('refreshPositions');
				}
			}
		});
	}

	// Get SVG
	$.WS_Form.prototype.svg = function(id) {

		return (typeof($.WS_Form.settings_form.icons[id]) !== 'undefined') ? $.WS_Form.settings_form.icons[id] : $.WS_Form.settings_form.icons.default;
	}

	// Test API
	$.WS_Form.prototype.api_test = function(success_callback, error_callback) {

		this.api_call('helper/test/', 'GET', false, function(response) {

			if(
				(typeof(response.error) !== 'undefined') &&
				!response.error
			) {

				success_callback();

			} else {

				error_callback((typeof(response.error_message) !== 'undefined') ? response.error_message : false);
			}

		}, function(response) {

			error_callback(false);
		});
	}

	// Detect framework
	$.WS_Form.prototype.framework_detect = function(success_callback, error_callback) {

		this.api_call('helper/framework-detect/', 'POST', false, function(response) {

			var framework = {'type': response.data.type, 'name': response.data.framework.name};
			success_callback(framework);

		}, error_callback);
	}

	// Form statistics reset
	$.WS_Form.prototype.form_stat_reset = function(form_id, success_callback, error_callback) {

		this.api_call('form/' + form_id + '/stat/reset/', 'POST', false, success_callback, error_callback);
	}

	// Send form statistics test
	$.WS_Form.prototype.report_form_statistics_test = function(success_callback, error_callback) {

		this.api_call('form/stat/report-form-statistics-test/', 'POST', false, success_callback, error_callback);
	}

	// Send form submission error test
	$.WS_Form.prototype.submit_error_test = function(success_callback, error_callback) {

		this.api_call('submit/error/report-submit-error-test/', 'POST', false, success_callback, error_callback);
	}

	// Push setup
	$.WS_Form.prototype.setup_push = function(params, success_callback, error_callback) {

		this.api_call('helper/setup-push/', 'POST', params, success_callback, error_callback);
	}

	// Template - Form
	$.WS_Form.prototype.template_form = function() {

		// Tabs (Run initially to avoid jolt in tabs)
		$('#wsf-template-add').tabs({

			activate: function(e, ui) {

				var action_populated = ui.newPanel.attr('data-populated');
				if(action_populated === 'true') { return; }

				var action_id = ui.newPanel.attr('data-action-id');
				if(action_id === undefined) { return; }

				// Populate templates
				$.WS_Form.this.template_form_populate(action_id);
			}
		});

		// Click event - Add blank
		$('[data-action="wsf-add-blank"]').on('click', function(e) {

			e.preventDefault();

			// Scroll to top of page
			$(window).scrollTop(0);

			// Show loading message (Avoids double clicks)
			$.WS_Form.this.template_form_loading_on();

			$('#ws-form-action').val('wsf-add-blank');
			$('#ws-form-action-do').trigger('submit');
		});

		// Click event - Add from template
		$('li:not(.wsf-pro-required) [data-action="wsf-add-template"]').on('click', function() {

			// Scroll to top of page
			$(window).scrollTop(0);

			// Show loading message (Avoids double clicks)
			$.WS_Form.this.template_form_loading_on();

			var id = $(this).attr('data-id');

			$('#ws-form-action').val('wsf-add-template');
			$('#ws-form-id').val(id);
			$('#ws-form-action-do').trigger('submit');
		});

		// Modal - Close
		$('[data-action="wsf-close"]', $('#wsf-template-add-modal')).on('click', function() {

			// Close modal
			$.WS_Form.this.template_form_add_modal_close();
		});

		// Click event - Add from action
		$('[data-action="wsf-template-add-modal-submit"]', $('#wsf-template-add-modal')).on('click', function() {

			// Scroll to top of page
			$(window).scrollTop(0);

			// Loader on
			$.WS_Form.this.loader_on();

			// Close modal
			$('#wsf-template-add-modal').hide();
			$('#wsf-template-add-modal-backdrop').hide();
			$(document).off('keydown');

			// Show loading message (Avoids double clicks)
			$.WS_Form.this.template_form_loading_on();

			// Set values
			$('#ws-form-id').val($(this).attr('data-id'));
			$('#ws-form-action').val($(this).attr('data-action'));
			$('#ws-form-action-id').val($(this).attr('data-action-id'));
			$('#ws-form-list-id').val($(this).attr('data-list-id'));

			// Remove old pass through fields
			$('#ws-form-action-do [data-form-add-modal-pass-through]').remove();

			// Pass through modal fields
			var form_fields = $('input, select, textarea', '#wsf-template-add-modal-form');
			form_fields.each(function() {

				$('#ws-form-action-do').append('<input type="hidden" data-form-add-modal-pass-through name="' + $(this).attr('name') + '" value="' + $(this).val() + '" />');
			});

			// Submit
			$('#ws-form-action-do').trigger('submit');
		});

		// Open modal - List sub
		$(document).on('click', '[data-action="wsf-modal-form"]', function(e) {

			e.preventDefault();

			// Get modal form
			var modal_form = $(this).attr('data-modal-form');

			// Legacy fallback
			if(typeof(modal_form) === 'undefined') {

				// Get list sub label
				var list_sub_modal_label_div = $(this).closest('[data-action-form-add-modal-label]');
				var form_add_modal_label = list_sub_modal_label_div ? list_sub_modal_label_div.attr('data-action-form-add-modal-label') : $(this).attr('data-label');

				modal_form = {

					'label' : form_add_modal_label,
					'action' : 'wsf-add-action',
					'fields' : [

						{
							'id'		: 'wsf-list-sub-id',
							'name'		: 'list_sub_id',
							'type'		: 'select',
							'required'	: true
						}
					],
					'get_list_subs' : true
				};

			} else {

				modal_form = JSON.parse(modal_form);
			}

			// Setup create button

			// Template ID
			$('#wsf-modal-buttons-create button').attr('data-id', $(this).attr('data-id'));

			// Action
			$('#wsf-modal-buttons-create button').attr('data-action', modal_form.action);

			// Action ID
			$('#wsf-modal-buttons-create button').attr('data-action-id', $(this).attr('data-action-id'));

			// List ID
			$('#wsf-modal-buttons-create button').attr('data-list-id', $(this).attr('data-list-id'));

			// Disable create button
			$('#wsf-modal-buttons-create button').prop('disabled', true);

			// Generate form
			var fields = modal_form.fields;

			var form_html = '';

			for(var field_index in fields) {

				if(!fields.hasOwnProperty(field_index)) { continue; }

				var field = fields[field_index];

				form_html += '<div class="wsf-template-add-modal-field-wrapper">';

				if(field.label) {

					form_html += '<label for="' + $.WS_Form.this.esc_attr(field.id) + '">' + field.label + '</label>';
				}

				var field_attributes = ' id="' + $.WS_Form.this.esc_attr(field.id)+ '" name="' + $.WS_Form.this.esc_attr(field.name) + '"';

				if(field.required) {

					field_attributes += ' required';
				}

				if(field.placeholder) {

					field_attributes += ' placeholder="' + $.WS_Form.this.esc_attr(field.placeholder) + '"';
				}

				switch(field.type) {

					case 'select' :

						form_html += '<select' + field_attributes + '></select>';
						break;

					case 'textarea' :

						form_html += '<textarea' + field_attributes + '></textarea>';
						break;
				}

				form_html += '</div>';
			}

			// Set modal title
			$('#wsf-template-add-modal .wsf-modal-title h2').html(modal_form.label);

			// Set modal form
			$('#wsf-template-add-modal-form').html(form_html);

			// Show modal
			$('#wsf-template-add-modal-backdrop').show();
			$('#wsf-template-add-modal').show();

			// Escape key
			$(document).on('keydown', function(e) {

				var keyCode = e.keyCode || e.which;

				if(keyCode === 27) { 

					// Close modal
					$.WS_Form.this.template_form_add_modal_close();
				}
			});

			// Get form fields
			var form_fields = $('input, select, textarea', '#wsf-template-add-modal-form');

			// Validation
			form_fields.on('change input paste', function() {

				$('#wsf-modal-buttons-create button').attr('disabled', $(this).closest('form')[0].checkValidity() ? false : '');
			});

			// Focus
			form_fields.first().trigger('focus');

			// Post populate
			if(modal_form.get_list_subs) {

				// Get action and list ID
				var action_id = $(this).attr('data-action-id');
				var list_id = $(this).attr('data-list-id');

				// Get API call path
				var api_call_path = $.WS_Form.this.action_api_method_path(action_id, 'list_subs_fetch', list_id);

				// Clear select
				$('#wsf-list-sub-id').empty().append($("<option />").val('').text($.WS_Form.this.language('list_subs_call')));

				// Make API call
				$.WS_Form.this.api_call(api_call_path, 'GET', false, function(response) {

					if(response.error) {

						$.WS_Form.this.loader_off();

						// Hide modal
						$.WS_Form.this.template_form_add_modal_close();

						// Throw error
						$.WS_Form.this.error('error_action_list_sub_get');

						return;
					}

					// Populate select
					$('#wsf-list-sub-id').empty().append($("<option />").val('').text($.WS_Form.this.language('list_subs_select')));

					var list_subs = response.data;

					for(var list_sub_index in list_subs) {

						if(!list_subs.hasOwnProperty(list_sub_index)) { continue; }

						var list_sub = list_subs[list_sub_index];

						$('#wsf-list-sub-id').append($("<option />").val(list_sub.id).text(list_sub.label));
					}

					$.WS_Form.this.loader_off();
				});
			}
		});

		// Click modal backdrop
		$(document).on('click', '#wsf-template-add-modal-backdrop', function(e) {

			// Close modal
			$.WS_Form.this.template_form_add_modal_close();
		});

		// Action API method events
		var action_obj = $('#wsf-template-add');
		this.api_reload_init(action_obj, function(obj, action_id, action_api_method) {

			switch(action_api_method) {

				case 'lists_fetch' :

					// Populate templates
					$.WS_Form.this.template_form_populate(action_id);
					break;
			}

		}, null, false)

		// Get configuration
		this.get_configuration(function() {

			$.WS_Form.this.loader_off();
		});
	}

	// Close modal
	$.WS_Form.prototype.template_form_add_modal_close = function() {

		$('#wsf-template-add-modal').hide();
		$('#wsf-template-add-modal-backdrop').hide();
		$(document).keydown = null;
	}

	// Template - Show loading div
	$.WS_Form.prototype.template_form_loading_on = function(action_id) {

		$('#wsf-template-add-loading').show();
	}

	// Templates - Populate
	$.WS_Form.prototype.template_form_populate = function(action_id) {

		// Loader on
		$.WS_Form.this.loader_on();

		// Populate templates
		var template_templates_obj = $('#wsf_template_category_' + action_id + ' ul.wsf-templates');

		// Get templates
		$.WS_Form.this.api_call('template/action/' + action_id, 'GET', [], function(response) {

			if(response.error) {

				// Loader off
				$.WS_Form.this.loader_off();
				return;
			}

			var templates = response.data;

			var template_content = '';
			var template_count = 0;

			for(var template_index in templates) {

				if(!templates.hasOwnProperty(template_index)) { continue; }

				var template = templates[template_index];

				var template_id = template.id;
				var template_label = template.label;
				var template_svg = template.svg;
				var template_list_sub = (typeof(template.list_sub) !== 'undefined') ? template.list_sub : false;
				var template_data_action = template_list_sub ? 'wsf-modal-form' : 'wsf-add-template-action';

				template_content += '<li><div class="wsf-template" data-action-id="' + $.WS_Form.this.esc_attr(action_id) + '" data-list-id="' + $.WS_Form.this.esc_attr(template_id) + '" title="' + $.WS_Form.this.esc_attr(template_label) + '">';
				template_content += template_svg;
				template_content += '<div class="wsf-template-actions">';
				template_content += '<button class="wsf-button wsf-button-primary wsf-button-full" data-action="' + $.WS_Form.this.esc_attr(template_data_action) + '" data-action-id="' + $.WS_Form.this.esc_attr(action_id) + '" data-list-id="' + $.WS_Form.this.esc_attr(template_id) + '">' + ws_form_settings_language_form_add_create + '</button>';
				template_content += '</div>';
				template_content += '</div>';
				template_content += '</li>';

				template_count++;
			}

			if(template_count > 0) {

				// Delete templates (2nd li onwards)				
				$('li:not(:first-child)', template_templates_obj).remove();

				// Populate templates
				template_templates_obj.append(template_content);
			}

			// Mark as populated so AJAX is not called again
			$('#wsf_template_category_' + action_id).attr('data-populated', 'true');

			// Click event - Add from action
			$('[data-action="wsf-add-template-action"]', template_templates_obj).on('click', function() {

				// Show loading message (Avoids double clicks)
				$.WS_Form.this.template_form_loading_on();

				var action_id = $(this).attr('data-action-id');
				var list_id = $(this).attr('data-list-id');

				$('#ws-form-action').val('wsf-add-action');
				$('#ws-form-action-id').val(action_id);
				$('#ws-form-list-id').val(list_id);
				$('#ws-form-action-do').trigger('submit');
			})

			// Loader off
			$.WS_Form.this.loader_off();
		});
	}

	$.WS_Form.prototype.template_api_response = function(response) {

		if(typeof(response.data) !== 'undefined') {

			$.WS_Form.templates_section = response.data;

			$.WS_Form.this.sidebar_section_select_init();
		}
	}

	// Template - Style
	$.WS_Form.prototype.template_style = function() {

		// Tabs (Run initially to avoid jolt in tabs)
		$('#wsf-template-add').tabs({

			activate: function(e, ui) {

				var action_populated = ui.newPanel.attr('data-populated');
				if(action_populated === 'true') { return; }

				var action_id = ui.newPanel.attr('data-action-id');
				if(action_id === undefined) { return; }
			}
		});

		// Click event - Add from template
		$('li:not(.wsf-pro-required) [data-action="wsf-add-template"]').on('click', function() {

			// Scroll to top of page
			$(window).scrollTop(0);

			// Show loading message (Avoids double clicks)
			$.WS_Form.this.template_style_loading_on();

			var id = $(this).attr('data-id');

			$('#ws-style-action').val('wsf-add-template');
			$('#ws-style-id').val(id);
			$('#ws-style-action-do').trigger('submit');
		});

		// Get configuration
		this.get_configuration(function() {

			$.WS_Form.this.loader_off();
		});
	}

	// Template - Show loading div
	$.WS_Form.prototype.template_style_loading_on = function(action_id) {

		$('#wsf-template-add-loading').show();
	}

	// WP List Table - Form
	$.WS_Form.prototype.wp_list_table_form = function() {

		this.form_id = 0;

		var form_list_table_obj = $('#wsf-form-list-table');

		// Form actions
		$('[data-action]:not([data-action="wsf-clipboard"])', form_list_table_obj).on('click', function() {

			$('#wsf-action').val($(this).attr('data-action'));
			$('#wsf-id').val($(this).attr('data-id'));
			$('#wsf-action-do').trigger('submit');
		});

		// Form upload
		$('table.wp-list-table', form_list_table_obj).wrap('<div id="wsf-form-table"></div>');
		var form_table_obj = $('#wsf-form-table');
		form_table_obj.append('<div class="wsf-object-upload-json-window"><div class="wsf-object-upload-json-window-content"><h1></h1><div class="wsf-uploads"></div></div></div>');

		// Drag enter
		form_table_obj.on('dragenter', function (e) {

			e.stopPropagation();
			e.preventDefault();

			// Check dragged object is a file
			if(!$.WS_Form.this.drag_is_file(e)) { return; }

			$('.wsf-object-upload-json-window', $(this)).show();
		});

		// Drag over
		$('.wsf-object-upload-json-window', form_table_obj).on('dragover', function (e) {

			e.stopPropagation();
			e.preventDefault();
		});

		// Drop
		$('.wsf-object-upload-json-window', form_table_obj).on('drop', function (e) {

			e.preventDefault();

			var files = e.originalEvent.dataTransfer.files;

			$.WS_Form.this.object_upload_json(files, $(this), null, function() {

				location.reload();

			}, function() {

				$('.wsf-object-upload-json-window', form_table_obj).hide();
			});
		});

		// Drag leave
		$('.wsf-object-upload-json-window', form_table_obj).on('dragleave', function (e) {

			$('.wsf-object-upload-json-window', form_table_obj).hide();
		});

		// Upload
		$('[data-action-button="wsf-form-upload"]').on('click', function(e) {

			// Click file input
			$('#wsf-object-upload-file').val('').trigger('click');
		});

		$('#wsf-object-upload-file').on('change', function() {

			var files = $('#wsf-object-upload-file').prop("files");

			if(files.length > 0) {

				var form_upload_window = $('> .wsf-object-upload-json-window', $.WS_Form.this.upload_obj);
				form_upload_window.show();
				$.WS_Form.this.object_upload_json(files, form_upload_window, null, function() {

					location.reload();

				}, function() {

					$('> .wsf-object-upload-json-window', $.WS_Form.this.upload_obj).hide();
				});
			}
		});

		// Toggle status
		$('[data-action-ajax="wsf-form-status"]', form_table_obj).on('click', function() {

			var form_id = $(this).attr('data-id');
			var status = $(this).is(':checked');

			// Loader on
			$.WS_Form.this.loader_on();

			var status_label_obj = $('#wsf-status-' + form_id + '-label');

			if(status) {

				// Set title
				status_label_obj.attr('title', $.WS_Form.this.language('publish'))

				// Publish
				$.WS_Form.this.api_call('form/' + form_id + '/publish/', 'POST', false, function(response) {

					// Loader off
					$.WS_Form.this.loader_off();
				});

			} else {

				// Set title
				status_label_obj.attr('title', $.WS_Form.this.language('draft'));

				// Draft
				$.WS_Form.this.api_call('form/' + form_id + '/draft/', 'POST', false, function(response) {

					// Loader off
					$.WS_Form.this.loader_off();
				});
			}
		});

		// Prevent default on server side functions (Stops page jumping)
		$('[data-action="wsf-clone"], [data-action="wsf-delete"], [data-action="wsf-export"]', form_table_obj).on('click', function(e) {

			e.preventDefault();
		});

		// Get form locations
		$('[data-action-ajax="wsf-form-locate"]', form_table_obj).on('click', function(e) {

			e.preventDefault();

			// Remember button object
			var location_button_obj = $(this);

			// Blur link so hover does not get stuck
			location_button_obj.trigger('blur');

			// Get form ID
			var form_id = $(this).attr('data-id');

			// Loader on
			$.WS_Form.this.loader_on();

			// Get locations
			$.WS_Form.this.api_call('form/' + form_id + '/locations/', 'GET', false, function(response) {

				// Loader off
				$.WS_Form.this.loader_off();

				var form_location_array = [];

				if(typeof(response[form_id]) !== 'undefined') {

					// Render each location the form was found
					for(var form_location_id in response[form_id]) {

						if(!response[form_id].hasOwnProperty(form_location_id)) { continue; }

						var form_location = response[form_id][form_location_id];
						var form_location_id = form_location.id;
						var form_location_type = form_location.type;
						var form_location_type_name = form_location.type_name;
						var form_location_title = form_location.title;

						switch(form_location.type) {

							case 'widget' :

								form_location_array.push($.WS_Form.this.esc_html(form_location_type_name) + ': ' + '<a href="widgets.php">' + $.WS_Form.this.esc_html(form_location_title) + '</a>');
								break;

							default :

								form_location_array.push($.WS_Form.this.esc_html(form_location_type_name) + ': ' + '<a href="post.php?post=' + form_location_id + '&post_type=' + form_location_type + '&action=edit">' + $.WS_Form.this.esc_html(form_location_title) + '</a>');
								break;
						}
					}
				}

				if(form_location_array.length == 0) {

					var location_html = '<div class="wsf-helper">' + $.WS_Form.this.language('form_location_not_found') + '</div>';

				} else {

					var location_html = '<div class="wsf-helper">' + $.WS_Form.this.language('form_location_found', '<span class="ws-form-location">' + form_location_array.join(', </span><span class="wsf-form-location">'), false) + '</div>';
				}

				var row_actions_obj = location_button_obj.closest('.row-actions');
				var td_obj = location_button_obj.closest('td');
				var form_locations_obj = $('.wsf-form-locations', td_obj);

				if(form_locations_obj.length) {

					form_locations_obj.html(location_html);

				} else {

					row_actions_obj.before('<div class="wsf-form-locations">' + location_html + '</div>');
				}
			});
		});

		this.clipboard(form_table_obj, 'shortcode_copied', 'table');
	}


	// WP List Table - Style
	$.WS_Form.prototype.wp_list_table_style = function() {

		this.style_id = 0;

		var style_list_table_obj = $('#wsf-style-list-table');

		// Style actions
		$('[data-action="wsf-clone"], [data-action="wsf-delete"], [data-action="wsf-export"], [data-action="wsf-restore"]', style_list_table_obj).on('click', function() {

			$('#wsf-action').val($(this).attr('data-action'));
			$('#wsf-id').val($(this).attr('data-id'));
			$('#wsf-action-do').trigger('submit');
		});

		$('[data-action="wsf-default"]', style_list_table_obj).on('click', function() {

			if(confirm($.WS_Form.this.language('styler_default_confirm'))) {

				$('#wsf-action').val($(this).attr('data-action'));
				$('#wsf-id').val($(this).attr('data-id'));
				$('#wsf-action-do').trigger('submit');
			}
		});

		$('[data-action="wsf-default-conv"]', style_list_table_obj).on('click', function() {

			if(confirm($.WS_Form.this.language('styler_default_conv_confirm'))) {

				$('#wsf-action').val($(this).attr('data-action'));
				$('#wsf-id').val($(this).attr('data-id'));
				$('#wsf-action-do').trigger('submit');
			}
		});

		$('[data-action="wsf-reset"]', style_list_table_obj).on('click', function() {

			if(confirm($.WS_Form.this.language('styler_reset_confirm'))) {

				$('#wsf-action').val($(this).attr('data-action'));
				$('#wsf-id').val($(this).attr('data-id'));
				$('#wsf-action-do').trigger('submit');
			}
		});

		// Style upload
		$('table.wp-list-table', style_list_table_obj).wrap('<div id="wsf-style-table"></div>');
		var style_table_obj = $('#wsf-style-table');
		style_table_obj.append('<div class="wsf-object-upload-json-window"><div class="wsf-object-upload-json-window-content"><h1></h1><div class="wsf-uploads"></div></div></div>');

		// Drag enter
		style_table_obj.on('dragenter', function (e) {

			e.stopPropagation();
			e.preventDefault();

			// Check dragged object is a file
			if(!$.WS_Form.this.drag_is_file(e)) { return; }

			$('.wsf-object-upload-json-window', $(this)).show();
		});

		// Drag over
		$('.wsf-object-upload-json-window', style_table_obj).on('dragover', function (e) {

			e.stopPropagation();
			e.preventDefault();
		});

		// Drop
		$('.wsf-object-upload-json-window', style_table_obj).on('drop', function (e) {

			e.preventDefault();

			var files = e.originalEvent.dataTransfer.files;

			$.WS_Form.this.object_upload_json(files, $(this), 'style', function() {

				location.reload();

			}, function() {

				$('.wsf-object-upload-json-window', style_table_obj).hide();
			});
		});

		// Drag leave
		$('.wsf-object-upload-json-window', style_table_obj).on('dragleave', function (e) {

			$('.wsf-object-upload-json-window', style_table_obj).hide();
		});

		// Upload
		$('[data-action-button="wsf-style-upload"]').on('click', function(e) {

			// Click file input
			$('#wsf-object-upload-file').val('').trigger('click');
		});

		$('#wsf-object-upload-file').on('change', function() {

			var files = $('#wsf-object-upload-file').prop("files");

			if(files.length > 0) {

				var style_upload_window = $('> .wsf-object-upload-json-window', $.WS_Form.this.upload_obj);
				style_upload_window.show();
				$.WS_Form.this.object_upload_json(files, style_upload_window, 'style', function() {

					location.reload();

				}, function() {

					$('> .wsf-object-upload-json-window', $.WS_Form.this.upload_obj).hide();
				});
			}
		});

		// Toggle status
		$('[data-action-ajax="wsf-style-status"]', style_table_obj).on('click', function() {

			var style_id = $(this).attr('data-id');
			var status = $(this).is(':checked');

			// Loader on
			$.WS_Form.this.loader_on();

			var status_label_obj = $('#wsf-status-' + style_id + '-label');

			if(status) {

				// Set title
				status_label_obj.attr('title', $.WS_Form.this.language('publish'))

				// Publish
				$.WS_Form.this.api_call('style/' + style_id + '/publish/', 'POST', false, function(response) {

					// Loader off
					$.WS_Form.this.loader_off();
				});

			} else {

				// Set title
				status_label_obj.attr('title', $.WS_Form.this.language('draft'));

				// Draft
				$.WS_Form.this.api_call('style/' + style_id + '/draft/', 'POST', false, function(response) {

					// Loader off
					$.WS_Form.this.loader_off();
				});
			}
		});

		// Prevent default on server side functions (Stops page jumping)
		$('[data-action="wsf-clone"], [data-action="wsf-delete"], [data-action="wsf-export"], [data-action="wsf-default"], [data-action="wsf-default-conv"], [data-action="wsf-reset"], [data-action="wsf-restore"]', style_table_obj).on('click', function(e) {

			e.preventDefault();
		});

		// Get style locations
		$('[data-action-ajax="wsf-style-locate"]', style_table_obj).on('click', function(e) {

			e.preventDefault();

			// Remember button object
			var location_button_obj = $(this);

			// Blur link so hover does not get stuck
			location_button_obj.trigger('blur');

			// Get style ID
			var style_id = $(this).attr('data-id');

			// Loader on
			$.WS_Form.this.loader_on();

			// Get locations
			$.WS_Form.this.api_call('style/' + style_id + '/locations/', 'GET', false, function(response) {

				// Loader off
				$.WS_Form.this.loader_off();

				var style_location_array = [];

				if(typeof(response[style_id]) !== 'undefined') {

					// Render each form the style was found
					for(var style_location_id in response[style_id]) {

						if(!response[style_id].hasOwnProperty(style_location_id)) { continue; }

						var style_location = response[style_id][style_location_id];
						var style_location_id = style_location.id;
						var style_location_type = style_location.type;
						var style_location_type_name = style_location.type_name;
						var style_location_title = style_location.title;

						style_location_array.push($.WS_Form.this.esc_html(style_location_type_name) + ': ' + '<a href="post.php?post=' + style_location_id + '&post_type=' + style_location_type + '&action=edit">' + $.WS_Form.this.esc_html(style_location_title) + '</a>');
					}
				}

				if(style_location_array.length == 0) {

					var location_html = '<div class="wsf-helper">' + $.WS_Form.this.language('style_location_not_found') + '</div>';

				} else {

					var location_html = '<div class="wsf-helper">' + $.WS_Form.this.language('style_location_found', '<span class="ws-style-location">' + style_location_array.join(', </span><span class="wsf-style-location">'), false) + '</div>';
				}

				var row_actions_obj = location_button_obj.closest('.row-actions');
				var td_obj = location_button_obj.closest('td');
				var style_locations_obj = $('.wsf-style-locations', td_obj);

				if(style_locations_obj.length) {

					style_locations_obj.html(location_html);

				} else {

					row_actions_obj.before('<div class="wsf-style-locations">' + location_html + '</div>');
				}
			});
		});
	}

	// Clipboard copying
	$.WS_Form.prototype.clipboard = function(obj, language_id, type) {

		// Copy shortcode to clipboard
		$('[data-action="wsf-clipboard"]', obj).on('click', function(e) {

			e.preventDefault();

			// Get text to copy
			var copy_text = $(this).attr('data-copy-text');
			if(copy_text === undefined) {

				var copy_text = $(this).html();
				if(copy_text === undefined) {

					return;
				}
			}

			// Copy text to clipboard
			var copy_to_obj = $('<input>');
			$('body').append(copy_to_obj);
			copy_to_obj.val(copy_text).trigger('select');
			document.execCommand('copy');
			copy_to_obj.remove();

			if(language_id !== undefined) {

				// Show copied message
				switch(type) {

					case 'table' :

						var helper_obj = $(this).closest('td');

						if(helper_obj && helper_obj.length) {

							helper_obj.append('<div class="wsf-helper">' + $.WS_Form.this.language(language_id) + '</div>');
							setTimeout(function() { $('.wsf-helper', helper_obj).remove(); }, 2000);
						}

						break;
				}
			}
		});
	}

	// WP List Table - Submit
	$.WS_Form.prototype.wp_list_table_submit = function(form_id) {

		var submissions_obj = $('#wsf-submissions');

		// Hide ID column hide option
		$('#id-hide').parent().remove();

		// Change form
		$('#wsf_filter_id', submissions_obj).on('change', function() {

			$.WS_Form.this.wp_list_table_filter_do()
		});

		// Bulk action
		$('#doaction', submissions_obj).on('click', function(e) {

			var action = $('#bulk-action-selector-top', submissions_obj).val();

			switch(action) {

				case 'wsf-bulk-export' :

					e.preventDefault();

					var submit_ids = [];

					$('input:checkbox[name="bulk-ids[]"]:checked').each(function(){

						submit_ids.push($(this).val());
					});

					if(submit_ids.length) {

						$.WS_Form.this.submit_export(submit_ids);
					}

					break;
			}
		});

		// Set form ID
		this.form_id = form_id;
		if(this.form_id == 0) { return; }

		// Hide column
		$(document).on('click', '#screen-options-wrap .hide-column-tog', function() {

			var params = {

				hidden: columns.hidden()
			};

			$.WS_Form.this.api_call('helper/user_meta_hidden_column/', 'POST', params, null, null, false, true, true);
		});

		// Toggle status
		$('[data-action-ajax="wsf-submit-starred"]', submissions_obj).on('click', function(e) {

			e.preventDefault();

			var submit_id = $(this).attr('data-id');
			var starred = $(this).hasClass('wsf-starred-on');
			var starred_obj = $(this);

			// Loader on
			$.WS_Form.this.loader_on();

			if(starred) {

				// Remove class
				$(this).removeClass('wsf-starred-on');

				// Starred - On
				$.WS_Form.this.api_call('submit/' + submit_id + '/starred/off/', 'POST', false, function(response) {

					// Loader off
					$.WS_Form.this.loader_off();
				});

			} else {

				// Add class
				$(this).addClass('wsf-starred-on');

				// Starred - Off
				$.WS_Form.this.api_call('submit/' + submit_id + '/starred/on/', 'POST', false, function(response) {

					// Loader off
					$.WS_Form.this.loader_off();
				});
			}
		});

		// Toggle viewed
		$('[data-action-ajax="wsf-submit-viewed"]', submissions_obj).on('click', function(e) {

			e.preventDefault();

			var submit_id = $(this).attr('data-id');
			var viewed = !$(this).closest('tr').hasClass('wsf-submit-not-viewed');
			var viewed_obj = $(this);

			// Loader on
			$.WS_Form.this.loader_on();

			if(viewed) {

				// Remove class
				viewed_obj.closest('tr').addClass('wsf-submit-not-viewed');

				// Set link text
				viewed_obj.html($.WS_Form.this.language('viewed_off'));

				// Mark as unread
				$.WS_Form.this.api_call('submit/' + submit_id + '/viewed/off/', 'POST', false, function(response) {

					// Update submission count in admin menu
					if(typeof(window.wsf_admin_wp_count_submit_unread_ajax) === 'function') {

						window.wsf_admin_wp_count_submit_unread_ajax($.WS_Form.this.form_id);
					}

					// Loader off
					$.WS_Form.this.loader_off();
				});

			} else {

				// Add class
				viewed_obj.closest('tr').removeClass('wsf-submit-not-viewed');

				// Set link text
				viewed_obj.html($.WS_Form.this.language('viewed_on'));

				// Mark as read
				$.WS_Form.this.api_call('submit/' + submit_id + '/viewed/on/', 'POST', false, function(response) {

					// Update submission count in admin menu
					if(typeof(window.wsf_admin_wp_count_submit_unread_ajax) === 'function') {

						window.wsf_admin_wp_count_submit_unread_ajax($.WS_Form.this.form_id);
					}

					// Loader off
					$.WS_Form.this.loader_off();
				});
			}
		});

		// Prevent default on server side functions (Stops page jumping)
		$('[data-action="wsf-delete"], [data-action="wsf-export"]', submissions_obj).on('click', function(e) {

			e.preventDefault();
		});

		// Tooltips
		this.tooltips();

		// Get configuration
		this.get_configuration(function() {

			// Get form
			$.WS_Form.this.get_form(function() {

				// Initialize the submit page
				$.WS_Form.this.wp_list_table_submit_init();

				// Initialize key down events
				$.WS_Form.this.keydown_events_init();

				// Loader off
				$.WS_Form.this.loader_off()

			});
		});
	}

	// WP List Table - Submit - Init
	$.WS_Form.prototype.wp_list_table_submit_init = function() {

		// Set as sidebar closed
		$('#wpcontent').addClass('wsf-sidebar-closed');

		// Set globals
		this.set_globals('ws-form', 'public');

		// Build data cache
		this.data_cache_build();

		// Build field type cache
		this.field_type_cache_build();

		// Check for a hash ID
		if(window.location.hash) {

			var id = Number(window.location.hash.substring(1));
			if(!isNaN(id)) {

				// View # record
				$.WS_Form.this.submit_action('wsf-view', id);
			}
		}

		// Action
		$('[data-action]:not([data-action="wsf-clipboard"])').on('click', function() {

			var id = $(this).attr('data-id');
			var action = $(this).attr('data-action');

			switch(action) {

				case 'wsf-view' :
				case 'wsf-edit' :

					$.WS_Form.this.submit_action(action, id);
					break;

				case 'wsf-export-all' :

					$.WS_Form.this.submit_export();
					break;

				case 'wsf-export' :

					var submit_id = $(this).attr('data-id');
					$.WS_Form.this.submit_export([submit_id]);
					break;

				default :

					$('#ws-form-action').val(action);
					$('#ws-form-submit-id').val(id);
					$('#ws-form-action-do').trigger('submit');
			}
		});

		// Filter
		$('#wsf_filter_do').on('click', function(e) {

			$.WS_Form.this.wp_list_table_filter_do()
		})

		// Reset
		$('#wsf_filter_reset').on('click', function(e) {

			$('#wsf_filter_date_from').val('');
			$('#wsf_filter_date_to').val('');
			$('#wsf_filter_keyword').val('');

			$.WS_Form.this.wp_list_table_filter_do()
		})

		// Date fields
		$('#wsf_filter_date_from,#wsf_filter_date_to').datepicker();
	}

	// Submit export
	$.WS_Form.prototype.submit_export = function(submit_ids) {

		// Show popup
		$.WS_Form.this.submit_export_popup_show();

		// Page
		var page = 0;
		var complete = false;

		// Prepare AJAX request
		var data = {

			'id': this.form_id
		};

		if(typeof(submit_ids) !== 'undefined') {

			data.submit_ids = submit_ids;

		} else {

			data.date_from = $('#wsf_filter_date_from').val();
			data.date_to = $('#wsf_filter_date_to').val();
			data.keyword = $('#wsf_filter_keyword').val();
		}

		// NONCE
		data[ws_form_settings.wsf_nonce_field_name] = ws_form_settings.wsf_nonce;

		// Start export process
		this.submit_export_do(data);
	}

	$.WS_Form.prototype.submit_export_do = function(data, page, hash, records_total) {

		if(typeof(page) === 'undefined') { page = 0; }

		data.page = page;

		if(typeof(hash) !== 'undefined') { data.hash = hash; }

		var url = ws_form_settings.url_ajax + 'submit/export/';

		// Make AJAX request
		$.ajax({

			url: url,
			type: 'POST',
			data: data,

			beforeSend: function(xhr) {

				xhr.setRequestHeader('X-WP-Nonce', ws_form_settings.x_wp_nonce);
			},

			success: function(response) {

				// Check for error
				if(response.error) {

					// Hide popup
					$.WS_Form.this.submit_export_popup_hide();

					$.WS_Form.this.error('error_submit_export', response.error_message);

					return;
				}

				// Check response
				if(typeof(response.complete) !== 'undefined') {

					var complete = response.complete;
					var hash = response.hash;

					// Get popup object
					var popup_obj = $('#wsf-submit-export-popup');

					if(complete) {

						// Set progress bar value
						$('.wsf-popup-progress-bar progress', popup_obj).val(100);

						// Hide popup
						setTimeout(function() {

							$.WS_Form.this.submit_export_popup_hide();

						}, 1000);

						// Redirect to download export
						location.href = response.url;

					} else {

						// Check for records total
						if(response.records_total !== false) {

							records_total = response.records_total;
						}

						// Calculate progress
						var progress = Math.round((records_total ? (response.records_processed / records_total) : 0) * 100, 0);

						// Hide text
						$('.wsf-popup-progress-inner p', popup_obj).hide();

						// Show progress bar
						$('.wsf-popup-progress-bar', popup_obj).show();

						// Set progress bar value
						$('.wsf-popup-progress-bar progress', popup_obj).val(progress);

						// Process next page
						$.WS_Form.this.submit_export_do(data, page + 1, hash, records_total);
					}

				} else {

					// Hide popup
					$.WS_Form.this.submit_export_popup_hide();
				}
			},

			error: function(response){

				// Hide popup
				$.WS_Form.this.submit_export_popup_hide();

				// Process error
				$.WS_Form.this.api_call_error_handler(response, url);
			}
		});
	}

	// Submit export popup - Show
	$.WS_Form.prototype.submit_export_popup_show = function() {

		// Get popup object
		var popup_obj = $('#wsf-submit-export-popup');

		// Show text
		$('.wsf-popup-progress-inner p', popup_obj).show();

		// Hide progress bar
		$('.wsf-popup-progress-bar', popup_obj).hide();

		// Show popup
		popup_obj.show();
	}

	// Submit export popup - Hide
	$.WS_Form.prototype.submit_export_popup_hide = function() {

		$('#wsf-submit-export-popup').hide();
	}

	// Remove hidden form elements we don't need
	$.WS_Form.prototype.wp_list_table_filter_do = function() {

		$('#wsf-submissions form [name="_wp_http_referer"]').remove();
		$('#wsf-submissions form [name="_wpnonce"]').remove();
		$('#wsf-submissions form [name="action"]').attr('disabled', 'disabled');
		$('#wsf-submissions form [name="action2"]').attr('disabled', 'disabled');
		$('#current-page-selector').val('1');

		$('#wsf-submissions form').trigger('submit');
	}

	// Submit actions
	$.WS_Form.prototype.submit_action = function(action, id) {

		switch(action) {

			case 'wsf-view' :

				this.submit_render(id, true);
				break;

			case 'wsf-edit' :

				this.submit_render(id, false);
				break;
		}
	}

	// Request a submit record
	$.WS_Form.prototype.submit_render = function(id, view) {

		// Mark as read in interface
		var viewed_obj = $('[data-action-ajax="wsf-submit-viewed"][data-id="' + this.esc_selector(id) + '"]');
		viewed_obj.closest('tr').removeClass('wsf-submit-not-viewed');
		viewed_obj.html($.WS_Form.this.language('viewed_on'));

		// Get sidebar outer object
		var sidebar_outer_obj = $('#wsf-sidebar-submit');

		// Clear submit ID
		$('h2 span', sidebar_outer_obj).html('');

		// Make API call to get the submit record
		this.api_call('submit/' + id, 'GET', false, function(response) {

			if(typeof(response.data) === 'undefined') { return; }

			// Get submit data
			var submit = response.data;

			// Preview?
			var preview = (typeof(submit.preview) !== 'undefined') ? submit.preview : false;

			if(!preview && $.WS_Form.this.form.published_checksum) {

				// Render submit using published form data
				$.WS_Form.this.submit_render_do(submit, $.WS_Form.this.form, view);

			} else {

				// Check if we have draft form data already
				if($.WS_Form.this.form_draft === false) {

					// Get draft form data
					$.WS_Form.this.api_call('form/' + $.WS_Form.this.form_id + '/full/', 'GET', false, function(response) {

						// Store form data
						$.WS_Form.this.form_draft = response.form;

						// Render submit using draft form data
						$.WS_Form.this.submit_render_do(submit, $.WS_Form.this.form_draft, view);

					}, false, false, false, false, true);

				} else {

					// Render submit using draft form data
					$.WS_Form.this.submit_render_do(submit, $.WS_Form.this.form_draft, view);
				}
			}

			// Update submission count in admin menu
			if(typeof(window.wsf_admin_wp_count_submit_unread_ajax) === 'function') {

				window.wsf_admin_wp_count_submit_unread_ajax($.WS_Form.this.form_id);
			}
		});
	}

	$.WS_Form.prototype.submit_render_do = function(submit, form, view) {

		var submit_scratch = $.extend(true, {}, submit); // Deep clone
		var sidebar_outer_obj = $('#wsf-sidebar-submit');

		// Checks
		if(
				(typeof(form) === 'undefined') ||
				(typeof(form.groups) === 'undefined') ||
				(parseInt(submit.form_id) !== parseInt(this.form_id, 10))
			) {

			// Turn off loader
			this.loader_off();

			return;
		}

		// Should e-commerce fields be editable?
		var submit_edit_ecommerce = this.get_object_value($.WS_Form.settings_plugin, 'submit_edit_ecommerce');

		// Are there groups?
		var has_groups = (form.groups.length > 1);

		// Is there meta data?
		var has_meta = (typeof(submit['meta']) !== 'undefined');

		// Encrypted?
		var encrypted_html = (submit.encrypted) ? '<div class="wsf-encrypted"' + this.tooltip(this.language('submit_encrypted'), 'top-center') + '>' + this.svg('readonly') + '</div>' : '';

		// Section repeatable
		var section_repeatable = (typeof(submit['section_repeatable']) !== 'undefined') ? submit['section_repeatable'] : false;

		// Expand / Contract
		var expand_contract = '<div data-action="wsf-sidebar-expand"' + this.tooltip(this.language('sidebar_expand'), 'bottom-right') + '>' + this.svg('expand') + '</div>';
		expand_contract += '<div data-action="wsf-sidebar-contract"' + this.tooltip(this.language('sidebar_contract'), 'bottom-right') + '>' + this.svg('contract') + '</div>';

		// Title
		var sidebar_html_title = '<div class="wsf-sidebar-header"><div class="wsf-sidebar-icon">' + this.svg('table') + '</div><h2>' + this.language('submission') + '</h2>' + encrypted_html + '<code></code>' + expand_contract + '</div>';

		// Info
		var sidebar_html_info = '<table id="wsf-sidebar-info">';

		sidebar_html_info += this.submit_row_render(submit, 'status_full', this.language('submit_status'), 'status');
		sidebar_html_info += this.submit_row_render(submit, 'date_added_wp', this.language('submit_date_added'));
		sidebar_html_info += this.submit_row_render(submit, 'date_updated_wp', this.language('submit_date_updated'));
		sidebar_html_info += this.submit_row_render(submit, 'user_id', this.language('submit_user'), 'user');
		sidebar_html_info += this.submit_row_render(submit, 'duration', this.language('submit_duration'), 'duration');

		sidebar_html_info += '</table>';

		// Inner
		var sidebar_html = '<form class="wsf-sidebar-inner" id="ws-form-submit" data-id="' + this.esc_attr(submit.id) + '">';

		// No meta data
		if(!has_meta) { return sidebar_html; }

		// Run through each group
		for(var group_index in form.groups) {

			if(!form.groups.hasOwnProperty(group_index)) { continue; }

			var group = form.groups[group_index];

			// Check for sections
			if((typeof(group.sections) === 'undefined') || (group.sections.length == 0)) { continue; }

			// Run through each section
			var group_label_rendered = false;
			for(var section_index in group.sections) {

				if(!group.sections.hasOwnProperty(section_index)) { continue; }

				// Get section
				var section = group.sections[section_index];

				// Get section ID
				var section_id = section.id;

				// Build section ID string
				var section_id_string = 'section_' + section_id;
				var section_repeatable_array = (

					(section_repeatable !== false) &&
					(typeof(section_repeatable[section_id_string]) !== 'undefined') &&
					(typeof(section_repeatable[section_id_string]['index']) !== 'undefined')

				) ? section_repeatable[section_id_string]['index'] : [false];

				// Run through each field
				for(var field_index in section.fields) {

					if(!section.fields.hasOwnProperty(field_index)) { continue; }

					var field = section.fields[field_index];

					if(typeof($.WS_Form.field_type_cache[field.type]) === 'undefined') { continue; }

					// Get field type
					var field_type = $.WS_Form.field_type_cache[field.type];

					// Determine if meta data would exist for this field type
					var submit_save = (typeof(field_type.submit_save) !== 'undefined') ? field_type.submit_save : false;

					// If not, delete it
					if(!submit_save) { delete section.fields[field_index]; }
				}

				// Remove empty elements after delete
				section.fields = this.array_remove_empty(section.fields);

				// Check for fields
				if((typeof(section.fields) === 'undefined') || (section.fields.length == 0)) { continue; }

				// Build fieldset HTML
				var fieldset_html = '';

				// Group label
				if(has_groups && !group_label_rendered) { fieldset_html += '<h3>' + this.esc_html(group.label) + "</h3>\n"; group_label_rendered = true; }

				if(section.meta.label_render) { fieldset_html += '<legend>' + this.esc_html(section.label) + "</legend>\n"; }

				// Loop through section_repeatable_array
				for(var section_repeatable_array_index in section_repeatable_array) {

					if(!section_repeatable_array.hasOwnProperty(section_repeatable_array_index)) { continue; }

					// Check if repeatable
					var section_repeatable_index = section_repeatable_array[section_repeatable_array_index];
					var section_repeatable_suffix = '';

					// Repeatable, so render fieldset and set field_name suffix
					if(section_repeatable_index !== false) {

						// Repeatable section found
						section_repeatable_index = parseInt(section_repeatable_index, 10);
						if(section_repeatable_index <= 0) { continue; }

						// Render fieldset
						fieldset_html += '<fieldset class="wsf-fieldset wsf-fieldset-repeatable" data-repeatable data-repeatable-index="' + this.esc_attr(section_repeatable_index) + '" data-id="' + this.esc_attr(section_id) + '"><legend>#' + (parseInt(section_repeatable_array_index, 10) + 1)  + '</legend>';

						// Set field_name suffix
						var section_repeatable_suffix = '_' + section_repeatable_index;
					}

					// Run through each field
					for(var field_index in section.fields) {

						if(!section.fields.hasOwnProperty(field_index)) { continue; }

						var field = section.fields[field_index];
						var field_name = this.field_name_prefix + field.id + section_repeatable_suffix;
						var value = (typeof(submit['meta'][field_name]) !== 'undefined') ? submit['meta'][field_name]['value'] : '';

						// Change rendering of field types for submit editing
						var field_type_old = field.type;
						switch(field.type) {

							case 'rating' :

								if(!view) {

									field.type = 'range';
									field.meta['min'] = '0';
									field.meta['max'] = field.meta['rating_max'];
									field.meta['help'] = '#value';
									this.field_data_cache[field.id].meta['help'] = '#value';

								}
								break;
						}

						fieldset_html += '<div class="wsf-field-wrapper" data-id="' + this.esc_attr(field.id) + '" data-type="' + this.esc_attr(field.type) + '"' + (section_repeatable_index ? (' data-repeatable-index="' + this.esc_attr(section_repeatable_index) + '"') : '') + '>';

						// Get field type
						var field_type = field.type;

						// Get field type config
						if(typeof($.WS_Form.field_type_cache[field_type]) === 'undefined') { continue; }
						var field_type_config = $.WS_Form.field_type_cache[field_type];

						// WPAutoP?
						var wpautop = $.WS_Form.this.wpautop_parse_variable(field, field_type_config);
						if(wpautop && view) { value = $.WS_Form.this.wpautop(value); }

						// Get whether field can be edited or not
						var submit_edit = (typeof(field_type_config.submit_edit) !== 'undefined') ? field_type_config.submit_edit : false;

						// Is this an e-commerce price? If so and price editing is allowed, set submit_edit to true
						var submit_edit_ecommerce_field = (typeof(field_type_config.submit_edit_ecommerce) !== 'undefined') ? field_type_config.submit_edit_ecommerce : false;
						if(submit_edit_ecommerce && submit_edit_ecommerce_field) { submit_edit = true; }

						// Get field type ID
						var field_type_id = (typeof(field_type_config.submit_edit_type) !== 'undefined') ? field_type_config.submit_edit_type : field_type;
						if(field_type_id !== field.type) { field.type = field_type_id; }

						// If editing, show fields in view mode if config says they cannot be edited
						var field_view = !view ? !submit_edit : view;

						if(submit_edit) {

							// Create blank scratch data if not found in original submit
							if((typeof(submit['meta'][field_name]) === 'undefined')) {

								submit_scratch['meta'][field_name] = {

									'id' :			field.id,
									'value' :		'', 
									'type' :		field.type
								};
							}

						} else {

							// Delete from scratch if it cannot be edited to that it is not included in the AJAX PUT request
							delete(submit_scratch['meta'][field_name])
						}

						// Render field by field type
						if(field_view) {

							var field_mask = '<div><strong>' + this.esc_html(field.label) + '</strong><br />#field</div>';

						} else {

							var field_mask = '#field';
						}

						var field_html = false;

						// Field HTML by field type (View or Edit)
						switch(field_type_id) {

							// Files
							case 'signature' :
							case 'file' :

								if((typeof(value) === 'object') && (typeof(value[0]) === 'object')) {

									if(!field_view) {

										field_mask = '<label class="wsf-label">' + this.esc_html(field.label) + '</label>#field';

									} else {

										field_mask = '#field';
									}

									field_html = '<div class="wsf-table-outer"><table>';
									field_html += '<thead>';
									field_html += '<tr>';
									field_html += '<th colspan="4">' + this.esc_html(field.label) + '</th>';
									field_html += '</tr>';
									field_html += '</thead>';

									field_html += '<tbody>';

									for(var file_index in value) {

										if(!value.hasOwnProperty(file_index)) { continue; }

										var file = value[file_index];
										var file_name = file.name;
										var file_type = file.type;
										var file_url = file.url;
										var file_url_download = file_url + (file_url.split('?')[1] ? '&' : '?') + 'download=1';

										var file_icon = this.svg((typeof($.WS_Form.file_types[file_type]) !== 'undefined') ? $.WS_Form.file_types[file_type]['icon'] : $.WS_Form.file_types['default']['icon']);

										field_html += '<tr>';

										// Icon
										field_html += '<td class="wsf-submit-file-view" data-icon><a download="' + this.esc_attr(file_name) + '" href="' + this.esc_attr(file_url) + '">' + file_icon + '</a></td>';

										// Filename
										field_html += '<td><a download="' + this.esc_attr(file_name) + '" href="' + this.esc_attr(file_url_download) + '" title="' + this.esc_attr(file_name) + '">' + this.esc_html(file_name) + '</a> (' + this.get_file_size(file.size) + ')</td>';

										// Download
										field_html += '<td class="wsf-submit-file-download" data-icon><a download="' + this.esc_attr(file_name) + '" href="' + this.esc_attr(file_url_download) + '">' + this.svg('download') + '</a></td>';

										// View
										field_html += '<td class="wsf-submit-file-view" data-icon><a href="' + this.esc_attr(file_url) + '" target="_blank">' + this.svg('visible') + '</a></td>';

										field_html += '</tr>';
									}

									field_html += '</tbody></table></div>';
								}

								break;
						}
						// Field HTML by field type
						if(field_view && (field_html === false) && (value != '')) {

							switch(field_type_id) {

								case 'rating' :

									var rating = parseInt(value, 10);
									var rating_max = this.get_object_meta_value(field, 'rating_max', 5);
									if(!rating_max) { rating_max = 5; }

									if((rating >= 0) && (rating <= rating_max)) {

										field_html = '<ul class="wsf-submit-rating wsf-list-inline">';

										for(var rating_index = 0; rating_index < rating_max; rating_index++) {

											var rating_class = (rating_index < rating) ? ' class="wsf-submit-rating-on"' : '';

											field_html += '<li' + rating_class + '>' + this.svg('rating') + '</li>';
										}

										field_html += '</ul>';
									}
									break;

								case 'googlemap' :

									if(
										(typeof(value) === 'object') &&
										(typeof(value['lat']) !== 'undefined') &&
										(typeof(value['lng']) !== 'undefined')
									) {

										value = value['lat'] + ',' + value['lng'];

										// Get lookup URL mask
										var latlon_lookup_url_mask = $.WS_Form.settings_plugin.latlon_lookup_url_mask;
										if(!latlon_lookup_url_mask) { value = this.esc_html(value); break; }

										// Get #value for mask
										var latlon_lookup_url_mask_values = {'value': value};

										// Build lookup URL
										var latlon_lookup_url = this.mask_parse(latlon_lookup_url_mask, latlon_lookup_url_mask_values);

										field_html = '<a href="' + this.esc_attr(latlon_lookup_url) + '" target="_blank">' + this.esc_html(value) + '</a>';
									}

									break;
								case 'email' :

									field_html = '<a href="mailto:' + this.esc_attr(value) + '">' + this.esc_html(value) + '</a>';
									break;

								case 'tel' :

									field_html = '<a href="tel:' + this.esc_attr(this.get_tel(value)) + '">' + this.esc_html(value) + '</a>';
									break;

								case 'url' :

									field_html = '<a href="' + this.esc_attr(value) + '" target="_blank">' + this.esc_html(value) + '</a>';
									break;

								case 'textarea' :

									if(typeof(value) === 'string') {

										// Add additional new lines to ending </p> tags to improve wpautop
										value = this.replace_all(value, '</p>', "\n")

										// Strip all HTML to avoid formatting issues and XSS issues
										field_html = this.html_strip(value);

										// WPAutoP
										var wpautop = this.wpautop_parse_variable(field, field_type_config);
										if(wpautop) { field_html = this.wpautop(field_html); }
									}

									break;
								case 'color' :

									field_html = '<span class="wsf-submit-color-sample" style="background:' + this.esc_attr(value) + '"></span><span class="wsf-submit-color">' + this.esc_html(value) + '</span>';
									break;

								case 'range' :

									value = parseInt(value, 10);

									var min = this.get_object_meta_value(this.field_data_cache[field.id], 'min', 0);
									if((min == '') || isNaN(min)) { min = 0; }

									var max = this.get_object_meta_value(this.field_data_cache[field.id], 'max', 100);
									if((max == '') || isNaN(max)) { max = 100; }

									if((value != '') && !isNaN(value)) {

										if(value >= 1 && ((max - min) >= 1)) {

											field_html = '<progress class="wsf-progress" min="' + this.esc_attr(min) + '" max="' + this.esc_attr(max) + '" value="' + this.esc_attr(value) + '"></progress><div class="wsf-helper">' + this.esc_html(value) + '</div>';

										} else {

											field_html = this.esc_html(value);
										}
									}

									break;
							}
						}

						// HTML encode
						if(typeof(value) === 'object') {

							for(var key in value) {

								if(!value.hasOwnProperty(key)) { continue; }

								if(typeof(value[key]) === 'string') {

									value[key] = this.esc_html(value[key]);
								}
							}
						}
						if(typeof(value) === 'string') {

							value = this.esc_html(value);
						}

						if(!field_view) {

							// Edit
							field_mask = '#field';
							field_html = this.get_field_html_single(field, value, true, section_repeatable_index);

						} else {

							// Format array values
							if(typeof(value) === 'object') {

								var value_html = '';
								var value_index = 0;

								for(var key in value) {

									if(!value.hasOwnProperty(key)) { continue; }

									if(typeof(value[key]) === 'string') {

										// Already HTML encoded above
										value_html += ((value_index > 0) ? '<br />' : '') + value[key];
									}

									value_index++;
								}

								value = value_html;
							}

							// Blank values
							if(field_html === false) { field_html = (value == '') ? '-' : value; }
						}

						// Parse and add field
						var field_mask_values = {'field': field_html};

						fieldset_html += this.mask_parse(field_mask, field_mask_values);

						fieldset_html += '</div>'

						// Mark submit meta as processed
						if(typeof(submit['meta'][field_name]) === 'object') {

							submit['meta'][field_name]['processed'] = true;
						}

						// Revert field type
						if(typeof(field_type_old) !== 'undefined') { field.type = field_type_old; }
					}

					// End of repeatable section
					if(section_repeatable_suffix !== false) {

						fieldset_html += '</fieldset>';
					}
				}

				if(fieldset_html != '') {

					sidebar_html += '<fieldset class="wsf-fieldset">' + fieldset_html + '</fieldset>';
				}
			}
		}

		if(view) {
			if(
				(typeof(submit['meta']['ecommerce_transaction_id']) !== 'undefined') ||
				(typeof(submit['meta']['ecommerce_cart_total']) !== 'undefined') ||
				(typeof(submit['meta']['woocommerce_order_id']) !== 'undefined')
			) {

				// E-commerce data
				var ecommerce_rows = [];

				// Price types
				for(var meta_key in $.WS_Form.ecommerce.cart_price_types) {

					if(!$.WS_Form.ecommerce.cart_price_types.hasOwnProperty(meta_key)) { continue; }

					var cart_price_type = $.WS_Form.ecommerce.cart_price_types[meta_key];

					meta_key = 'ecommerce_cart_' + meta_key;

					var value = (typeof(submit['meta'][meta_key]) === 'undefined') ? false : submit['meta'][meta_key];

					if((value !== false) && (value != '')) {

						value = this.get_price(parseFloat(value));

						// Get priority
						var priority = typeof(cart_price_type.priority) ? cart_price_type.priority : 10000;

						ecommerce_rows.push({label: cart_price_type.label, value: value, priority: priority});

					} else {

						continue;
					}
				}

				// Other fixed meta keys
				for(var meta_key in $.WS_Form.ecommerce.meta_keys) {

					if(!$.WS_Form.ecommerce.meta_keys.hasOwnProperty(meta_key)) { continue; }

					var meta_key_config = $.WS_Form.ecommerce.meta_keys[meta_key];

					if(typeof(submit['meta'][meta_key]) !== 'undefined') {

						var label = meta_key_config.label;
						var value = submit['meta'][meta_key];

						// Lookup?
						if(
							(typeof(meta_key_config.lookup) !== 'undefined') &&
							(typeof($.WS_Form.ecommerce[meta_key_config.lookup]) !== 'undefined') &&
							(typeof($.WS_Form.ecommerce[meta_key_config.lookup][value]) !== 'undefined')
						) {

							value = $.WS_Form.ecommerce[meta_key_config.lookup][value].label;
						}

						// Type
						if(typeof(meta_key_config.type) !== 'undefined') {

							switch(meta_key_config.type) {

								case 'price' : value = this.get_price(parseFloat(value)); break;
							}
						}

						// Hide blank values
						if(value == '') { continue; }

						// Get priority
						var priority = typeof(meta_key_config.priority) ? meta_key_config.priority : 10000;

						// Check for mask
						var mask = (typeof(meta_key_config.mask) !== 'undefined') ? meta_key_config.mask : false;
						if(mask !== false) { value = this.mask_parse(mask, { 'value' : value }); }

						// Push to e-commerce rows
						ecommerce_rows.push({label: label, value: value, priority: priority});
					}
				}

				// Sort ecommerce rows by priority
				ecommerce_rows.sort(function(a, b) {
				    a = a.priority;
				    b = b.priority;
				    return a < b ? -1 : (a > b ? 1 : 0);
				});

				// Render e-commerce rows
				if(ecommerce_rows.length > 0) {

					var sidebar_ecommerce_html = "<fieldset class=\"wsf-fieldset\">\n";
					sidebar_ecommerce_html += '<h3>' + this.language('submit_ecommerce') + "</h3>\n";

					for(var ecommerce_row_index in ecommerce_rows) {

						if(!ecommerce_rows.hasOwnProperty(ecommerce_row_index)) { continue; }

						var ecommerce_row = ecommerce_rows[ecommerce_row_index];

						sidebar_ecommerce_html += '<div class="wsf-field-wrapper">';
						sidebar_ecommerce_html += '<div><strong>' + this.esc_html(ecommerce_row.label) + '</strong><br />' + ecommerce_row.value + '</div>';
						sidebar_ecommerce_html += "</div>\n";
					}

					sidebar_ecommerce_html += "</fieldset>\n";

					sidebar_html += sidebar_ecommerce_html;
				}
			}
			// Actions
			var sidebar_actions_html = this.sidebar_render_actions(submit);
			if(sidebar_actions_html !== false) { sidebar_html += sidebar_actions_html; }

			// Tracking data
			var sidebar_tracking_html = "<fieldset class=\"wsf-fieldset\">\n";

			sidebar_tracking_html += '<h3>' + this.language('submit_tracking') + "</h3>\n";

			var tracking_count = 0;

			for(var meta_key in $.WS_Form.tracking) {

				if(!$.WS_Form.tracking.hasOwnProperty(meta_key)) { continue; }

				var tracking = $.WS_Form.tracking[meta_key];

				var value = (typeof(submit['meta'][meta_key]) === 'undefined') ? false : submit['meta'][meta_key];

				if((value !== false) && (value != '')) {

					switch(tracking['type']) {

						case 'url' :

							value = '<a href="' + this.esc_attr(value) + '" target="_blank">' + this.esc_html(value) + '</a>';
							break;

						case 'tel' :

							value = '<a href="tel:' + this.esc_attr(value) + '" target="_blank">' + this.esc_html(value) + '</a>';
							break;

						case 'email' :

							value = '<a href="email:' + this.esc_attr(value) + '" target="_blank">' + this.esc_html(value) + '</a>';
							break;

						case 'ip' :

							// Get lookup URL mask
							var ip_lookup_url_mask = $.WS_Form.settings_plugin.ip_lookup_url_mask;
							if(!ip_lookup_url_mask) { value = this.esc_html(value); break; }

							// Regex to validate IP
							var ip_regex = /((^\s*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\s*$)|(^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$))|(^\s*((?=.{1,255}$)(?=.*[A-Za-z].*)[0-9A-Za-z](?:(?:[0-9A-Za-z]|\b-){0,61}[0-9A-Za-z])?(?:\.[0-9A-Za-z](?:(?:[0-9A-Za-z]|\b-){0,61}[0-9A-Za-z])?)*)\s*$)/;

							// Split IP (IP can be comma separated if proxy in use)
							var ip_array = value.split(',');
							var value_array = [];

							for(var ip_array_index in ip_array) {

								if(!ip_array.hasOwnProperty(ip_array_index)) { continue; }

								// Get IP
								var ip = ip_array[ip_array_index];

								// Trim
								ip = ip.trim();

								// Validate IP
								if(!ip_regex.test(ip)) { continue; }

								// Get #value for mask
								var ip_lookup_url_mask_values = {'value': ip};

								// Build lookup URL
								var ip_lookup_url = this.mask_parse(ip_lookup_url_mask, ip_lookup_url_mask_values);

								value_array.push('<a href="' + this.esc_attr(ip_lookup_url) + '" target="_blank">' + this.esc_html(ip) + '</a>');
							}

							value = value_array.join('<br />');

							break;

						case 'latlon' :

							if(/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/.test(value)) {

								// Get lookup URL mask
								var latlon_lookup_url_mask = $.WS_Form.settings_plugin.latlon_lookup_url_mask;
								if(!latlon_lookup_url_mask) { value = this.esc_html(value); break; }

								// Get #value for mask
								var latlon_lookup_url_mask_values = {'value': value};

								// Build lookup URL
								var latlon_lookup_url = this.mask_parse(latlon_lookup_url_mask, latlon_lookup_url_mask_values);

								value = '<a href="' + this.esc_attr(latlon_lookup_url) + '" target="_blank">' + this.esc_html(value) + '</a>';

							} else {

								switch(parseInt(value, 10)) {

									case 1 :

										value = this.language('submit_tracking_geo_location_permission_denied');
										break;

									case 2 :

										value = this.language('submit_tracking_geo_location_position_unavailable');
										break;

									case 3 :

										value = this.language('submit_tracking_geo_location_timeout');
										break;

									default :

										value = '-';
								}
							}
							break;

						default :

							value = this.esc_html(value);
					}

				} else {

					continue;
				}

				sidebar_tracking_html += '<div class="wsf-field-wrapper">';
				sidebar_tracking_html += '<div><strong>' + this.esc_html(tracking.label) + '</strong><br />' + value + '</div>';
				sidebar_tracking_html += "</div>\n";

				tracking_count++;
			}

			sidebar_tracking_html += "</fieldset>\n";

			if(tracking_count > 0) { sidebar_html += sidebar_tracking_html; }
		}

		sidebar_html += '</form>';

		// Next / Previous IDs
		var tbody = $('#wsf-submissions .wp-list-table tbody');

		var row_current = $('[data-id="' + this.esc_selector(submit.id) + '"]', tbody).first().closest('tr');

		var row_previous = row_current.prev();
		var row_id_previous = (row_previous.length) ? $('[data-action="wsf-view"]', row_previous).attr('data-id') : undefined;

		var row_next = row_current.next();
		var row_id_next = (row_next.length) ? $('[data-action="wsf-view"]', row_next).attr('data-id') : undefined;

		// Build buttons - View
		var buttons_view = [

			{'class': 'wsf-button-primary', 'action': 'wsf-sidebar-edit', 'label': this.language('edit')},
			{'action': 'wsf-sidebar-print', 'label': this.language('print')},
			{'action': 'wsf-sidebar-close', 'label': this.language('close')},
			{'action': 'wsf-sidebar-view', 'right': true, 'id': row_id_previous, 'label': this.language('previous'), 'disabled': (row_id_previous === undefined)},
			{'action': 'wsf-sidebar-view', 'id': row_id_next, 'label': this.language('next'), 'disabled': (row_id_next === undefined)}
		];

		// Build buttons - Edit
		var buttons_edit = [

			{'class': 'wsf-button-primary', 'action': 'wsf-sidebar-save', 'label': this.language('save')},
			{'class': '', 'action': 'wsf-sidebar-cancel', 'label': this.language('cancel')},
			{'class': 'wsf-button-danger', 'right': true, 'action': 'wsf-sidebar-delete', 'label': this.language('trash')},
		];

		// Footer
		var sidebar_html_buttons = this.sidebar_buttons_html(view ? buttons_view : buttons_edit);

		// Push HTML to sidebar inner
		sidebar_outer_obj.html(sidebar_html_title);
		sidebar_outer_obj.append(sidebar_html_info);
		sidebar_outer_obj.append(sidebar_html);
		sidebar_outer_obj.append(sidebar_html_buttons);

		// Populate submit ID
		$('#wsf-sidebar-submit .wsf-sidebar-header code').html(this.language('id') + ': ' + submit.id);

		var sidebar_inner_obj = $('.wsf-sidebar-inner', sidebar_outer_obj);

		// Button events
		if(view) {

			// Previous / Next
			$('[data-action="wsf-sidebar-view"]', sidebar_outer_obj).on('click', function() {

				var id = $(this).attr('data-id');
				if(typeof(id) !== 'undefined') { $.WS_Form.this.submit_render(id, true); }

				// Reset popovers
				$.WS_Form.this.popover_reset();
			});

			// Edit
			$('[data-action="wsf-sidebar-edit"]', sidebar_outer_obj).on('click', function() {

				$.WS_Form.this.submit_render(submit.id, false);

				// Reset popovers
				$.WS_Form.this.popover_reset();
			});

			// Print
			$('[data-action="wsf-sidebar-print"]', sidebar_outer_obj).on('click', function() {

				window.print();
			});

			// Close
			$('[data-action="wsf-sidebar-close"]', sidebar_outer_obj).on('click', function() {

				$('#wsf-sidebar-submit').removeClass('wsf-sidebar-open').addClass('wsf-sidebar-closed');
				$('#wpcontent').removeClass('wsf-sidebar-open').addClass('wsf-sidebar-closed');

				// Overflow hidden to improve touch scrolling in sidebar
				if(window.matchMedia('(max-width: 600px)').matches) {

					$('html').css({'overflow':''});
					$('body').css({'overflow':'','-webkit-overflow-scrolling':''});
				}

				// Reset popovers
				$.WS_Form.this.popover_reset();
			});

			// Repost
			$('[data-action="wsf-submit-action-repost"]', sidebar_outer_obj).on('click', function() {

				var ws_this = $(this);

				var obj_action = $(this).closest('tr');

				var buttons = [

					{label:$.WS_Form.this.language('cancel'), action:'wsf-cancel'},
					{label:$.WS_Form.this.language('repost'), action:'wsf-confirm', class:'wsf-button-danger'}
				];

				$.WS_Form.this.popover($.WS_Form.this.language('confirm_action_repost'), buttons, obj_action, function() {

					// Animate
					ws_this.addClass('wsf-api-method-calling');

					// Params
					var params = {

						id: 			$.WS_Form.this.form_id,
						action_index: 	ws_this.attr('data-submit-action-index')
					};

					// Make API call
					$.WS_Form.this.api_call('submit/' + submit.id + '/action/', 'POST', params, function(response) {

						// Success
						ws_this.removeClass('wsf-api-method-calling');

						if(typeof(response.data) === 'undefined') { return false; }

						// Process logs
						if((typeof(response.data.logs) !== 'undefined') && response.data.logs.length) {

							var log_message = response.data.logs.join('<br />');
							$.WS_Form.this.message(log_message, true, 'notice-success'); 
						}

						// Process errors
						if((typeof(response.data.errors) !== 'undefined') && response.data.errors.length) {

							var error_message = response.data.errors.join('<br />');
							$.WS_Form.this.message(error_message, true, 'notice-error'); 
						}

					}, function() {

						// Error
						ws_this.removeClass('wsf-api-method-calling');

					}, false, true);	// Bypass loader
				});

				obj_action.siblings('tr.wsf-ui-cancel').removeClass('wsf-ui-cancel');
			});

			// Toggle submit action row
			$('[data-toggle]', sidebar_outer_obj).on('click', function() {

				$(this).attr('data-status', $(this).attr('data-status') == 'on' ? '' : 'on');
				var toggle_id = $(this).attr('data-toggle');
				$('#' + toggle_id).toggle();
			});

		} else {

			// Save
			$('[data-action="wsf-sidebar-save"]', sidebar_outer_obj).on('click', function() {

				$.WS_Form.this.submit_button_save(submit_scratch);
			});

			// Trash
			$('[data-action="wsf-sidebar-delete"]', sidebar_outer_obj).on('click', function() {

				$('#ws-form-action').val('wsf-delete');
				$('#ws-form-submit-id').val(submit_scratch.id);
				$('#ws-form-action-do').trigger('submit');
			});

			// Cancel
			$('[data-action="wsf-sidebar-cancel"]', sidebar_outer_obj).on('click', function() {

				$.WS_Form.this.submit_button_cancel(submit_scratch);
			});

			// Set up key shortcuts
			$.WS_Form.this.keydown[27] = {'function': function() { $.WS_Form.this.submit_button_cancel(submit_scratch); }, 'ctrl_key': false};
			$.WS_Form.this.keydown[83] = {'function': function() { $.WS_Form.this.submit_button_save(submit_scratch); }, 'ctrl_key': true};

			// Initialize range sliders
			this.form_help_value(sidebar_inner_obj);
		}

		// Turn off loader
		this.loader_off();

		// Show sidebar
		$('#wsf-sidebar-submit').removeClass('wsf-sidebar-closed').addClass('wsf-sidebar-open');
		$('#wpcontent').removeClass('wsf-sidebar-closed').addClass('wsf-sidebar-open');

		// Overflow hidden to improve touch scrolling in sidebar
		if(window.matchMedia('(max-width: 600px)').matches) {

			$('html').css({'overflow':'hidden'});
			$('body').css({'overflow':'auto','-webkit-overflow-scrolling':'touch'});
		}

		// Expand / contract
		this.sidebar_expand_contract_init();
	}

	// Submit - Save
	$.WS_Form.prototype.submit_button_save = function(submit_scratch) {

		// Build form data
		var form_submit_obj = $('#ws-form-submit');
		var form_data = new FormData(form_submit_obj[0]);

		// Update submit meta data
		for(var meta_key in submit_scratch.meta) {

			if(!submit_scratch.meta.hasOwnProperty(meta_key)) { continue; }

			// Only process field meta
			if(meta_key.indexOf(this.field_name_prefix) == -1) { continue; }

			// If meta data contains data from a delete field, skip it
			if(typeof(submit_scratch.meta[meta_key]['value']) === 'undefined') { continue; }

			// Check if repeatable
			var section_repeatable_index = submit_scratch.meta[meta_key]['repeatable_index'];

			// Get field name
			var field_id = submit_scratch.meta[meta_key]['id']
			var field_name = this.field_name_prefix + field_id + (section_repeatable_index ? '[' + section_repeatable_index + ']' : '');

			// Get value from form data and assign to submit meta value
			var meta_value = (form_data.get(field_name) !== null) ? form_data.get(field_name) : (form_data.getAll(field_name + '[]') ? form_data.getAll(field_name + '[]') : '');

			submit_scratch.meta[meta_key]['value'] = meta_value;

			// Removed processed references
			delete(submit_scratch.meta[meta_key]['processed']);
		}

		// Push to API
		var params = {

			id :			this.form_id,
			submit_id : 	submit_scratch.id,
			submit : 		submit_scratch
		};

		// Call AJAX request
		this.api_call('submit/' + submit_scratch.id + '/put/', 'POST', params, function(response) {

			// Reload
			location.reload();
		});
	}

	// Submit - Cancel
	$.WS_Form.prototype.submit_button_cancel = function(submit_scratch) {

		// Clear keyup functions
		$.WS_Form.this.keydown = [];

		// View submit
		$.WS_Form.this.submit_render(submit_scratch.id, true);
	}

	// Submit - Actions HTML
	$.WS_Form.prototype.sidebar_render_actions = function(submit) {

		// Get actions
		if(typeof(submit.actions) === 'undefined') { return false; }
		var submit_actions = submit.actions;

		if(submit_actions === false) { return false; }
		if(submit_actions.length == 0) { return false; }

		// Build actions HTML
		var sidebar_actions_html = "<fieldset id=\"wsf-submit-sctions\" class=\"wsf-fieldset\">\n";

		sidebar_actions_html += '<h3>' + this.language('submit_actions') + "</h3>\n";
		sidebar_actions_html += '<div class="wsf-field-wrapper">';
		sidebar_actions_html += '<div class="wsf-table-outer">';
		sidebar_actions_html += "<table>\n";

		// Header row
		sidebar_actions_html += '<thead><tr>';
		sidebar_actions_html += '<th width="16" style="text-align: center;">' + this.language('submit_actions_column_index') + '</th>';
		sidebar_actions_html += '<th>' + this.language('submit_actions_column_action') + '</th>';
		sidebar_actions_html += '<th></th>';
		sidebar_actions_html += '<th></th>';
		sidebar_actions_html += '<th></th>';
		sidebar_actions_html += '<th></th>';
		sidebar_actions_html += "</tr></thead>\n<tbody>\n";

		// Run through each action
		for(var submit_action_index in submit_actions) {

			if(!submit_actions.hasOwnProperty(submit_action_index)) { continue; }

			// Save logged action data
			var submit_action_index = parseInt(submit_action_index, 10);
			var submit_action = submit_actions[submit_action_index];
			var submit_action_id = submit_action.id;
			var submit_action_label = submit_action.label;
			var submit_action_meta = submit_action.meta;

			// Check to see if the action is installed so we can extract more data
			var action_installed = (typeof($.WS_Form.actions[submit_action_id]) !== 'undefined');
			var action = (action_installed) ? $.WS_Form.actions[submit_action_id] : false;

			// Can report action?
			var action_can_repost = (typeof(action.can_repost) !== 'undefined') ? action.can_repost : false;

			// Get action meta HTML
			var sidebar_render_action_meta_html = this.sidebar_render_action_meta_html(action, submit_action);

			// Get action log HTML
			var sidebar_render_action_logs_html = this.sidebar_render_action_logs_html(submit_action, 'logs');

			// Get action error HTML
			var sidebar_render_action_errors_html = this.sidebar_render_action_logs_html(submit_action, 'errors');

			// Action row
			sidebar_actions_html += '<tr>';

			// Action column - Index
			sidebar_actions_html += '<td style="text-align: center;">' + (submit_action_index + 1) + '</td>';

			// Action column - Label 
			sidebar_actions_html += '<td>' + this.esc_html(submit_action_label) + '</td>';

			// Action column - Meta
			sidebar_actions_html += '<td data-icon>';

			if(sidebar_render_action_meta_html !== false) {

				sidebar_actions_html += '<div data-toggle="wsf-action-meta-' + this.esc_attr(submit_action_index) + '"' + this.tooltip(this.language('submit_actions_meta'), 'top-center') + '>' + this.svg('edit') + '</div>';
			}

			sidebar_actions_html += '</td><td data-icon>';

			// Action column - Logs
			if(sidebar_render_action_logs_html !== false) {

				sidebar_actions_html += '<div data-toggle="wsf-action-logs-' + this.esc_attr(submit_action_index) + '"' + this.tooltip(this.language('submit_actions_logs'), 'top-center') + '>' + this.svg('info-circle') + '</div>';
			}

			sidebar_actions_html += '</td><td data-icon>';

			// Action column - Errors
			if(sidebar_render_action_errors_html !== false) {

				sidebar_actions_html += '<div data-toggle="wsf-action-errors-' + this.esc_attr(submit_action_index) + '"' + this.tooltip(this.language('submit_actions_errors'), 'top-right') + '>' + this.svg('warning') + '</div>';
			}

			sidebar_actions_html += '</td><td data-icon>';

			// Action column - Repost
			if(action_can_repost) {

				sidebar_actions_html += '<div data-action="wsf-submit-action-repost" data-submit-action-index="' + this.esc_attr(submit_action_index) + '"' + this.tooltip(this.language('submit_actions_repost'), 'top-right') + '>' + this.svg('redo') + '</div>';
			}

			sidebar_actions_html += "</td></tr>\n";

			// Action meta
			if(sidebar_render_action_meta_html !== false) {

				sidebar_actions_html += '<tr id="wsf-action-meta-' + this.esc_attr(submit_action_index) + '" class="wsf-hidden-table-row"><td colspan="6" class="wsf-hidden-table-cell"><div>';
				sidebar_actions_html += sidebar_render_action_meta_html;
				sidebar_actions_html += '</div></td></tr>';
			}

			// Action logs
			if(sidebar_render_action_logs_html !== false) {

				sidebar_actions_html += '<tr id="wsf-action-logs-' + this.esc_attr(submit_action_index) + '" class="wsf-hidden-table-row"><td colspan="6" class="wsf-hidden-table-cell"><div>';
				sidebar_actions_html += sidebar_render_action_logs_html;
				sidebar_actions_html += '</div></td></tr>';
			}

			// Action errors
			if(sidebar_render_action_errors_html !== false) {

				sidebar_actions_html += '<tr id="wsf-action-errors-' + this.esc_attr(submit_action_index) + '" class="wsf-hidden-table-row"><td colspan="6" class="wsf-hidden-table-cell"><div>';
				sidebar_actions_html += sidebar_render_action_errors_html;
				sidebar_actions_html += '</div></td></tr>';
			}
		}

		sidebar_actions_html += "</tbody>\n</table>\n</div>\n</div>";

		sidebar_actions_html += '</fieldset>';

		return sidebar_actions_html;
	}

	// Submit - Action meta HTML
	$.WS_Form.prototype.sidebar_render_action_meta_html = function(action, submit_action) {

		var submit_action_id = submit_action.id;

		// Actions have a single level fieldset configuration
		var action_meta_key_cache = this.get_action_meta_key_cache(action, submit_action_id);

		// Run through submit_action meta
		if(typeof(submit_action.meta) === 'undefined') { return false; }
		var submit_action_meta = submit_action.meta;

		if(submit_action_meta.length == 0) { return false; }

		var sidebar_action_meta_html = '<table>';

		// Header row
		sidebar_action_meta_html += '<thead><tr>';
		sidebar_action_meta_html += '<th>' + this.language('submit_actions_column_meta_label') + '</th>';
		sidebar_action_meta_html += '<th>' + this.language('submit_actions_column_meta_value') + '</th>';
		sidebar_action_meta_html += "</tr></thead>\n<tbody>\n";

		// Run through each submit_action_meta row
		for(var meta_key in submit_action_meta) {

			if(!submit_action_meta.hasOwnProperty(meta_key)) { continue; }

			// Get label (Use meta_key if not found)
			var meta_label = (typeof(action_meta_key_cache[meta_key]) !== 'undefined') ? action_meta_key_cache[meta_key].label : meta_key;

			// Process meta_value
			var meta_value = submit_action_meta[meta_key];

			// If it is an object, its likely a repeater field so render that as a table
			if(typeof(meta_value) === 'object') {

				if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { continue; }
				var meta_config = $.WS_Form.meta_keys[meta_key];

				if(typeof(meta_config.type) === 'undefined') { continue; }
				var meta_type = meta_config.type;

				switch(meta_type) {

					case 'repeater' :

						var meta_value_repeater = '<table>';

						for(var meta_value_index in meta_value) {

							if(!meta_value.hasOwnProperty(meta_value_index)) { continue; }

							var meta_value_row = meta_value[meta_value_index];

							meta_value_repeater += '<tr>';

							for(var meta_value_row_index in meta_value_row) {

								if(!meta_value_row.hasOwnProperty(meta_value_row_index)) { continue; }

								var meta_value_column = meta_value_row[meta_value_row_index];

								meta_value_repeater += '<td>' + this.esc_html(meta_value_column) + '</td>';
							}

							meta_value_repeater += '</tr>';

						}
						meta_value_repeater += '</table>';

						meta_value = meta_value_repeater;

						break;

					default :

						// We don't have a case for processing this meta_type so output as JSON so it can be viewed
						meta_value = JSON.stringify(meta_value);
				}

			} else {

				meta_value = this.esc_html(meta_value);
			}

			sidebar_action_meta_html += '<tr><td>' + this.esc_html(meta_label) + '</td><td>' + ((meta_value != '') ? meta_value : '-') + '</td></tr>';
		} 

		sidebar_action_meta_html += "</tbody>\n</table>";

		return sidebar_action_meta_html;
	}

	// Submit - Action meta HTML
	$.WS_Form.prototype.sidebar_render_action_logs_html = function(submit_action, key) {

		// Run through submit_action log or error
		if(typeof(submit_action[key]) === 'undefined') { return false; }
		var submit_action_logs = submit_action[key];

		if(submit_action_logs.length == 0) { return false; }

		var sidebar_action_logs_html = '<table>';

		// Header row
		sidebar_action_logs_html += '<thead><tr>';
		sidebar_action_logs_html += '<th>' + this.language('submit_actions_column_' + key) + '</th>';
		sidebar_action_logs_html += "</tr></thead>\n<tbody>\n";

		// Run through each submit_action_meta row
		for(var meta_key in submit_action_logs) {

			if(!submit_action_logs.hasOwnProperty(meta_key)) { continue; }

			// Get message
			var message = submit_action_logs[meta_key];

			sidebar_action_logs_html += '<tr><td>' + this.esc_html(message) + '</td></tr>';
		} 

		sidebar_action_logs_html += "</tbody>\n</table>";

		return sidebar_action_logs_html;
	}

	// Submit - Action meta HTML
	$.WS_Form.prototype.get_action_meta_key_cache = function(action, action_id) {

		var action_meta_key_cache = [];

		if(typeof(action.fieldsets) === 'undefined') { return []; }
		if(typeof(action.fieldsets[action_id]) === 'undefined') { return []; }
		if(typeof(action.fieldsets[action_id].meta_keys) === 'undefined') { return []; }

		for(var meta_key_index in action.fieldsets[action_id].meta_keys) {

			if(!action.fieldsets[action_id].meta_keys.hasOwnProperty(meta_key_index)) { continue; }

			var meta_key = action.fieldsets[action_id].meta_keys[meta_key_index];

			if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { continue; }
			var meta_key_config = $.WS_Form.meta_keys[meta_key];

			if(typeof(meta_key_config.label) === 'undefined') { continue; }

			// Add to action_meta_key_cache
			action_meta_key_cache[meta_key] = {'label': $.WS_Form.meta_keys[meta_key].label};
		}

		return action_meta_key_cache;
	}

	// Render submit row
	$.WS_Form.prototype.submit_row_render = function(submit, field, label, type) {

		if(typeof(submit[field]) === 'undefined') { return; }
		if(typeof(type) === 'undefined') { type = 'default'; }

		// Preview?
		var preview = (typeof(submit.preview) !== 'undefined') ? submit.preview : false;

		var return_html = '<tr><th width="80">' + label + '</th><td>';

		switch(type) {

			// Status
			case 'status' :

				return_html += submit[field] + (preview ? (' (' + this.language('submit_preview') + ')') : '');
				break;

			// Duration in days, hours, minutes
			case 'duration' :

				if(parseInt(submit[field], 10) === 0) { return ''; }
				return_html += this.get_nice_duration(submit[field]);
				break;

			// User
			case 'user' :

				var user_id = parseInt(submit[field]);

				if((user_id === 0) || (typeof(submit['user']) === 'undefined')) {

					return_html += '-';

				} else {

					var user_label = submit['user']['display_name'];

					return_html += '<a href="user-edit.php?user_id=' + user_id + '">' + this.esc_html(user_label) + '</a>';
				}

				break;

			default :

				return_html += submit[field];
		}

		return_html += '</td></tr>';

		return return_html;
	}

	// Generate random key
	$.WS_Form.prototype.key_generate = function(length) {

		if(typeof(length) === 'undefined') { length = 64; }

		var characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]\\{}|;\':",./<>?';
		var key = '';

		for (var i = 0; i < 64; i++) {

			var rand = Math.round(Math.random() * characters.length);
			if(rand < 0) { rand = 0; }
			if(rand > (characters.length -1)) { rand = characters.length - 1; }
			key += characters[rand];
		}

		return key;
	}

	// API Call
	$.WS_Form.prototype.api_call = function(ajax_path, method, params, success_callback, error_callback, checksum_request, bypass_loader, bypass_form_processing, form_parse) {

		this.api_call_queue.push({

			'ajax_path':				ajax_path,
			'method':					method,
			'params':					params,
			'success_callback':			success_callback,
			'error_callback':			error_callback,
			'checksum_request':			checksum_request,
			'bypass_loader':			bypass_loader,
			'bypass_form_processing':	bypass_form_processing,
			'form_parse': 				form_parse
		});

		// Start API queue
		if(!this.api_call_queue_running) { this.api_call_process_next(); }
	}

	// API Call - Process
	$.WS_Form.prototype.api_call_process_next = function() {

		if(this.api_call_queue.length == 0) { this.api_call_queue_running = false; return false; }

		this.api_call_queue_running = true;

		var api_call = this.api_call_queue.shift();

		this.api_call_process(api_call.ajax_path, api_call.method, api_call.params, api_call.success_callback, api_call.error_callback, api_call.checksum_request, api_call.bypass_loader, api_call.bypass_form_processing, api_call.form_parse);
	}

	// API Call - Process
	$.WS_Form.prototype.api_call_process = function(ajax_path, method, params, success_callback, error_callback, checksum_request, bypass_loader, bypass_form_processing, form_parse) {

		// Defaults
		if(typeof(method) === 'undefined') { method = 'POST'; }
		if(typeof(params) === 'undefined') { params = false; }
		if(typeof(success_callback) === 'undefined') { success_callback = false; }
		if(typeof(error_callback) === 'undefined') { error_callback = false; }
		if(typeof(checksum_request) === 'undefined') { checksum_request = false; }
		if(typeof(bypass_loader) === 'undefined') { bypass_loader = false; }
		if(typeof(bypass_form_processing) === 'undefined') { bypass_form_processing = false; }
		if(typeof(form_parse) === 'undefined') { form_parse = false; }

		// Show loader
		if(!checksum_request && !bypass_loader) { this.loader_on(); }

		// Set form_id
		var data = {};
		if(this.form_id > 0) {

			if(params !== false) {

				data.data = JSON.stringify(params);
				data.data = this.mod_security_fix(data.data);
			}
			data.id = this.form_id;
			data.wsf_fti = this.get_object_meta_value(this.form, 'tab_index', 0);

		} else {

			if(params !== false) { data = params; }
		}

		// Set is admin
		data.wsf_fia = true;

		// Form parse (This is added in case we are requesting the form on the submission edit page where we need data sources to be processed)
		if(form_parse) {

			data.wsf_fp = true;
		}

		// NONCE
		data[ws_form_settings.wsf_nonce_field_name] = ws_form_settings.wsf_nonce;

		// Make AJAX request
		var url = ws_form_settings.url_ajax + ajax_path;

		var ajax_request = {

			method: method,

			url: url,

			beforeSend: function(xhr) {

				xhr.setRequestHeader('X-WP-Nonce', ws_form_settings.x_wp_nonce);
			},

			success: function(response) {

				// Reset checksum checking (Prevents form refreshing due to this API call)
				if(!checksum_request) { $.WS_Form.checksum = false; }

				if(!bypass_form_processing && (typeof(response.form) !== 'undefined')) {

					// Process checksum
					$.WS_Form.this.api_call_process_checksum(response);
				}

				// Build data cache if queue is now empty
				if($.WS_Form.this.api_call_queue.length == 0) {

					// If full form returned by API, load it
					if((typeof(response.form_full) !== 'undefined') && response.form_full) {

						$.WS_Form.this.form = response.form;
					}

					// Build data cache
					$.WS_Form.this.data_cache_build();
				}

				// Call success function
				if(typeof(success_callback) === 'function') { success_callback(response); } else { $.WS_Form.this.loader_off(); }

				// Save if we are using undo function (Called after success_callback to ensure response returned is in caches)
				if(!bypass_form_processing && (typeof(response.history) !== 'undefined')) {

					// Push to history stack
					$.WS_Form.this.history_push(response);
				}

				// Process next API call
				$.WS_Form.this.api_call_process_next();
			},

			error: function(response) {

				$.WS_Form.this.loader_off();

				// Process error
				$.WS_Form.this.api_call_error_handler(response, url, error_callback);

				// Process next API call
				$.WS_Form.this.api_call_process_next();
			}
		};

		if(data !== false) { ajax_request.data = data; }

		$.ajax(ajax_request);

		return this;
	};

	// API call - Process checksum
	$.WS_Form.prototype.api_call_process_checksum = function(response) {

		// Look for checksum
		if((typeof(response.form.checksum) !== 'undefined') && (typeof(response.form.published_checksum) !== 'undefined')) {

			// Save published checksum
			this.published_checksum = response.form.published_checksum;

			// Render publish button
			this.publish_render(response.form.checksum);
		}
	}

	// API call - Error handler
	$.WS_Form.prototype.api_call_error_handler = function(response, url, error_callback) {

		// Get response data
		var data = (typeof(response.responseJSON) !== 'undefined') ? response.responseJSON : false;

		// Get status
		var status = response.status;

		// Process response data
		if(data) {

			// Reload if REST cookie is invalid
			if(
				(typeof(data) !== 'undefined') &&
				(typeof(data.code) !== 'undefined') &&
				(data.code === 'rest_cookie_invalid_nonce')
			) {

				location.reload();
			}
		}

		// Process WS Form API error message
		if(data && data.error) {

			if(data.error_message) {

				this.error('error_api_call_' + status + '_message', data.error_message);

			} else {

				this.error('error_api_call_' + status, url);
			}

		} else {

			// Fallback
			this.error('error_api_call_' + status, url);
		}

		// Call error call back
		if(typeof(error_callback) === 'function') {

			// Run error callback
			error_callback(data);

		}
	}

	// If current checksum does not match published check sum
	$.WS_Form.prototype.publish_render = function(checksum) {

		if(checksum == '') { return false; }

		if(checksum != this.published_checksum) {

			// Add class to publish button
			$('[data-action="wsf-publish"]').prop('disabled', false);

		} else {

			// Remove class from publish button
			$('[data-action="wsf-publish"]').prop('disabled', true);
		}
	}

	// Handle a log message (Disregard in admin)
	$.WS_Form.prototype.log = function(log_message) {}

	// Render an error message
	$.WS_Form.prototype.error = function(language_id, variable) {

		// Check for variable
		if(typeof(variable) == 'undefined') { variable = ''; }

		var message = this.language(language_id, variable, false, true).replace(/%s/g, variable);

		this.message(message, true, 'notice-error');
	}

	// Message
	$.WS_Form.prototype.message = function(message, dismissable, type, insert_after_header_end) {

		if(typeof(dismissable) == 'undefined') { dismissable = true; }
		if(typeof(type) == 'undefined') { type = 'notice-success'; }
		if(typeof(insert_after_header_end) == 'undefined') { insert_after_header_end = true; }

		// Build notice
		var notice = '<div class="notice ' + type + '"><p>' + message + '</p>' + (dismissable ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' + this.language('dismiss', false, true, true) + '</span></button>' : '') + '</div>';
		notice.replace("\n", "<br />\n");

		// Append message to notice div
		if(insert_after_header_end) {

			var notice_obj = $(notice).insertAfter($('.wp-header-end'));

			// Button click event
			if(dismissable) {

				$('button', notice_obj).on('click', function() { $(this).closest('div').remove(); });
			}

		} else {

			return notice;
		}
	}

	// Sidebar - Form - Open
	window.sidebar_form_open = function(ws_this, obj_form, obj_button) {

		var obj_outer = $('#wsf-sidebar-form');

		// Edit the form settings (This function opens the object sidebar)
		ws_this.object_edit($('#wsf-form'));
	}

	// Sidebar - Form - Toggle
	window.sidebar_form_toggle = function(ws_this, obj_form, obj_button) {

		// Remove editing class
		$('#wsf-form').removeClass('wsf-editing');

		ws_this.sidebar_reset();
	}

	// Sidebar - Form - Close
	window.sidebar_form_close = function(ws_this, obj_form, obj_button) {

		// Remove editing class
		$('.wsf-form').removeClass('wsf-editing');
	}

	// Sidebar - Conditional - Open
	window.sidebar_conditional_open = function(ws_this, obj_form, obj_button) {

		var obj_outer = $('#wsf-sidebar-conditional');

		// Title
		var sidebar_config = $.WS_Form.settings_form['sidebars']['conditional'];

		// Build knowledge base HTML
		if((typeof(sidebar_config.kb_url) !== 'undefined')) {

			var kb_url = ws_this.get_plugin_website_url(sidebar_config.kb_url, 'sidebar');
			var sidebar_kb_html = '<a class="wsf-kb-url" href="' + ws_this.esc_attr(kb_url) + '" target="_blank"' + ws_this.tooltip(ws_this.language('field_kb_url'), 'bottom-center') + ' tabindex="-1">' + ws_this.svg('question-circle') + '</a>';
		}

		obj_outer.html(ws_this.sidebar_title(ws_this.svg(sidebar_config.icon), sidebar_config.label, '', sidebar_kb_html, '', true));

		// Inner
		obj_outer.append('<div class="wsf-sidebar-inner"><fieldset class="wsf-fieldset"><div class="wsf-data-grid" data-object="form" data-id="' + ws_this.esc_attr(ws_this.form_id) + '" data-meta-key="conditional"></div></fieldset></div>');

		// Buttons
		obj_outer.append(ws_this.sidebar_buttons_html());

		// Get object data
		var object_data = ws_this.get_object_data('form', ws_this.form_id);

		// Create new object data that edits will be saved to
		ws_this.object_data_scratch = $.extend(true, {}, object_data); // Deep clone

		// Initialize conditional datagrid
		ws_this.sidebar_data_grids_init($('#wsf-sidebar-conditional'));

		// Button - Save
		$('[data-action="wsf-sidebar-save"]', obj_outer).on('click', function() {

			ws_this.sidebar_conditional_save(false);
			ws_this.conditional_highlight_row_index = false;

			ws_this.conditional_icons_build(true);
		});

		// Button - Save & close
		$('[data-action="wsf-sidebar-save-close"]', obj_outer).on('click', function() {

			ws_this.sidebar_conditional_save(true);
			ws_this.conditional_highlight_row_index = false;

			ws_this.conditional_icons_build(true);
		});

		// Button - Cancel
		$('[data-action="wsf-sidebar-cancel"]', obj_outer).on('click', function() {

			ws_this.sidebar_reset();
			ws_this.conditional_highlight_row_index = false;

			ws_this.conditional_icons_build(true);
		});

		// Set up key shortcuts
		$.WS_Form.this.keydown[27] = {'function': function() {

			ws_this.sidebar_reset();
			ws_this.conditional_highlight_row_index = false;

			ws_this.conditional_icons_build(true);
		}, 'ctrl_key': false};

		$.WS_Form.this.keydown[83] = {'function': function() {

			ws_this.sidebar_conditional_save(true);
			ws_this.conditional_highlight_row_index = false;

			ws_this.conditional_icons_build(true);
		}, 'ctrl_key': true};

		// Expand / contract
		ws_this.sidebar_expand_contract_init();

		// Initialize
		var inits = ['variable-helper'];
		ws_this.sidebar_inits(inits, obj_outer);

		// Open conditional sidebar
		ws_this.sidebar_open('conditional');
	}

	$.WS_Form.prototype.sidebar_conditional_save = function(close) {

		// Save
		this.conditional_save();

		// Save to form
		this.form.meta.conditional = this.object_data_scratch.meta.conditional;

		// Build parameters
		var params = {

			form_id: 	this.form_id,
			form: 		this.object_data_scratch
		};

		// Clear keyup functions
		$.WS_Form.this.keydown = [];

		// Save notification
		$.WS_Form.this.saving_notification();

		// Call AJAX request
		this.api_call('form/' + this.form_id + '/put/', 'POST', params, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});

		// Reset sidebar
		if(close) {

			// Reset sidebar
			this.sidebar_reset();
		}
	}

	// Sidebar - Action - Open
	window.sidebar_action_open = function(ws_this, obj_form, obj_button) {

		var obj_outer = $('#wsf-sidebar-action');

		// Title
		var sidebar_config = $.WS_Form.settings_form['sidebars']['action'];

		// Build knowledge base HTML
		if((typeof(sidebar_config.kb_url) !== 'undefined')) {

			var kb_url = ws_this.get_plugin_website_url(sidebar_config.kb_url, 'sidebar');
			var sidebar_kb_html = '<a class="wsf-kb-url" href="' + ws_this.esc_attr(kb_url) + '" target="_blank"' + ws_this.tooltip(ws_this.language('field_kb_url'), 'bottom-right') + ' tabindex="-1">' + ws_this.svg('question-circle') + '</a>';
		}

		obj_outer.html(ws_this.sidebar_title(ws_this.svg(sidebar_config.icon), sidebar_config.label, '', sidebar_kb_html, '', true));

		// Inner
		obj_outer.append('<div class="wsf-sidebar-inner"><fieldset class="wsf-fieldset"><div class="wsf-data-grid" data-object="form" data-id="' + ws_this.esc_attr(ws_this.form_id) + '" data-meta-key="action"></div></fieldset></div>');


		// Buttons
		obj_outer.append(ws_this.sidebar_buttons_html());

		// Get object data
		var object_data = ws_this.get_object_data('form', ws_this.form_id);

		// Create new object data that edits will be saved to
		ws_this.object_data_scratch = $.extend(true, {}, object_data); // Deep clone

		// Initialize conditional datagrid
		ws_this.sidebar_data_grids_init($('#wsf-sidebar-action'));

		// Button - Save
		$('[data-action="wsf-sidebar-save"]', obj_outer).on('click', function() {

			ws_this.sidebar_action_save(false);
		});

		// Button - Save
		$('[data-action="wsf-sidebar-save-close"]', obj_outer).on('click', function() {

			ws_this.sidebar_action_save(true);
		});

		// Button - Cancel
		$('[data-action="wsf-sidebar-cancel"]', obj_outer).on('click', function() {

			ws_this.sidebar_reset();
		});

		// Set up key shortcuts
		$.WS_Form.this.keydown[27] = {'function': function() { ws_this.sidebar_reset(); }, 'ctrl_key': false};
		$.WS_Form.this.keydown[83] = {'function': function() { ws_this.sidebar_action_save(true); }, 'ctrl_key': true};

		// Expand / contract
		ws_this.sidebar_expand_contract_init();

		// Open actions sidebar
		ws_this.sidebar_open('action');
	}

	$.WS_Form.prototype.sidebar_action_save = function(close) {

		// Save
		this.action_save();

		// Save to form
		this.form.meta.action = this.object_data_scratch.meta.action;

		// Build parameters
		var params = {

			form_id: 	this.form_id,
			form: 		this.object_data_scratch
		};

		// Clear keyup functions
		$.WS_Form.this.keydown = [];

		// Saving notification
		$.WS_Form.this.saving_notification();

		// Call AJAX request
		this.api_call('form/' + this.form_id + '/put/', 'POST', params, function(response) {

			// Loader off
			$.WS_Form.this.loader_off();
		});

		// Reset sidebar
		if(close) {

			// Reset sidebar
			this.sidebar_reset();
		}
	}

	// Score
	$.WS_Form.prototype.score = function (input, word, fuzziness) {

		'use strict';

		// If the string is equal to the word, perfect match.
		if (input === word) { return 1; }

		//if it's not a perfect match and is empty return 0
		if (word === "") { return 0; }

		var runningScore = 0,
		charScore,
		finalScore,
		string = input,
		lString = string.toLowerCase(),
		strLength = string.length,
		lWord = word.toLowerCase(),
		wordLength = word.length,
		idxOf,
		startAt = 0,
		fuzzies = 1,
		fuzzyFactor,
		i;

		// Cache fuzzyFactor for speed increase
		if (fuzziness) { fuzzyFactor = 1 - fuzziness; }

		// Walk through word and add up scores.
		// Code duplication occurs to prevent checking fuzziness inside for loop
		if (fuzziness) {
			for (i = 0; i < wordLength; i+=1) {

				// Find next first case-insensitive match of a character.
				idxOf = lString.indexOf(lWord[i], startAt);

				if (idxOf === -1) {

					fuzzies += fuzzyFactor;

				} else {

					if (startAt === idxOf) {

						// Consecutive letter & start-of-string Bonus
						charScore = 0.7;

					} else {

						charScore = 0.1;

						// Acronym Bonus
						// Weighing Logic: Typing the first character of an acronym is as if you
						// preceded it with two perfect character matches.
						if (string[idxOf - 1] === ' ') { charScore += 0.8; }
					}

					// Same case bonus.
					if (string[idxOf] === word[i]) { charScore += 0.1; }

					// Update scores and startAt position for next round of indexOf
					runningScore += charScore;
					startAt = idxOf + 1;
				}
			}

		} else {

			for (i = 0; i < wordLength; i+=1) {

				idxOf = lString.indexOf(lWord[i], startAt);

				if (-1 === idxOf) { return 0; }

				if (startAt === idxOf) {
					charScore = 0.7;
				} else {
					charScore = 0.1;
					if (string[idxOf - 1] === ' ') { charScore += 0.8; }
				}

				if (string[idxOf] === word[i]) { charScore += 0.1; }

				runningScore += charScore;
				startAt = idxOf + 1;
			}
		}

		// Reduce penalty for longer strings.
		finalScore = 0.5 * (runningScore / strLength    + runningScore / wordLength) / fuzzies;

		if ((lWord[0] === lString[0]) && (finalScore < 0.85)) {
			finalScore += 0.15;
		}

		return finalScore;
	};

	// Get nice file size
	$.WS_Form.prototype.get_file_size = function(bytes) {

		if(bytes >= 1048576) {

			bytes = Math.round(bytes / 1048576, 2) + ' MB';

		} else if(bytes >= 1024) {

			bytes = Math.round(bytes / 1024, 2) + ' KB';

		} else if(bytes > 1) {

			bytes = bytes + ' bytes';

		} else if(bytes == 1) {

			bytes = bytes + ' byte';

		} else {

			bytes = '0 bytes';
		}

		return bytes;
	}

	// Extract numbers from telephone number
	$.WS_Form.prototype.get_tel = function(tel_input) {
	
		return tel_input.replace(/[^+\d]+/g, "");
	}

	// Insert text into an input
	$.WS_Form.prototype.input_insert_text = function(input, text) {

		// Get meta_key
		var meta_key = input.attr('data-meta-key-type');

		switch(meta_key) {

			case 'text_editor' :

				// Insert text
				tinymce.activeEditor.execCommand('mceInsertContent', false, text);

   				break;

			case 'html_editor' :

				// Insert text
				var code_editor = $('.CodeMirror')[0].CodeMirror;
				var code_editor_doc = code_editor.getDoc();
				var code_editor_cursor = code_editor_doc.getCursor();
				code_editor_doc.replaceRange(text, code_editor_cursor);

				// Save
				var code_editor_value = code_editor.getValue();
				var code_editor_textarea = code_editor.getTextArea();
				$(code_editor_textarea).val(code_editor_value).trigger('keyup');

   				break;
		
			default :

				// Inject text
				var caret_position_start = input[0].selectionStart;
				var caret_position_end = input[0].selectionEnd;
				var input_val = input.val();
				input.val(input_val.substring(0, caret_position_start) + text + input_val.substring(caret_position_end));
				input.trigger('focus').trigger('change');

				// Set new caret position
				var new_caret_position = caret_position_start + text.length;
				if(input.prop('selectionStart') !== null) { input.prop('selectionStart', new_caret_position); }
				if(input.prop('selectionEnd') !== null) { input.prop('selectionEnd', new_caret_position); }
		}
	}

	// Insert function into an input
	$.WS_Form.prototype.input_insert_function = function(input, function_name, check_existing, highlight_parameters) {

		if(typeof(check_existing) === 'undefined') { check_existing = false; }

		// Get existing value
		var input_value = input.val();
		var input_value_blank = (input_value == '');

		// Get meta_key
		var meta_key = input.attr('data-meta-key-type');

		// If input already contains this function, move to end of function and return
		if(check_existing && !input_value_blank) {

			var input_value_index = input_value.indexOf(function_name.slice(0, -1));
			if(input_value_index !== -1) {

				var bracket_start_index = input_value_index + function_name.length - 2;
				var input_string = input_value.substring(bracket_start_index);
				var bracket_finish_index = this.get_bracket_finish_index(input_string);

				if(bracket_finish_index !== -1) {

					var new_caret_position = bracket_start_index + bracket_finish_index;

					switch(meta_key) {

						case 'text_editor' :
						case 'html_editor' :

							// Skip
							break;

						default :

							// Set new caret position
							input.focus();
							if(input.prop('selectionStart') !== null) { input.prop('selectionStart', new_caret_position); }
							if(input.prop('selectionEnd') !== null) { input.prop('selectionEnd', new_caret_position); }
					}
				}
			}

			return;
		}

		// Calculate parameter length
		var bracket_start_index = function_name.indexOf('(');
		if(bracket_start_index === -1) {

			parameter_length = 0;
			highlight_parameters = false;

		} else {

			var input_string = function_name.substring(bracket_start_index);
			var bracket_finish_index = bracket_start_index + this.get_bracket_finish_index(input_string);
			var parameter_length = bracket_finish_index - bracket_start_index - 1;
		}

		switch(meta_key) {

			case 'text_editor' :

				// Insert
				tinymce.activeEditor.execCommand('mceInsertContent', false, function_name);

				// Set new caret position
				if(input_value_blank) {
					var node = tinymce.activeEditor.selection.getNode();
					tinymce.activeEditor.selection.setCursorLocation(node.firstChild, function_name.length - 1);
				}

   				break;

			case 'html_editor' :

				// Insert
				var code_editor = $('.CodeMirror')[0].CodeMirror;
				var code_editor_doc = code_editor.getDoc();
				var code_editor_cursor = code_editor_doc.getCursor();
				code_editor_doc.replaceRange(function_name, code_editor_cursor);

				// Save
				var code_editor_value = code_editor.getValue();
				var code_editor_textarea = code_editor.getTextArea();
				$(code_editor_textarea).val(code_editor_value).trigger('keyup');

				// Set new caret position
				var code_editor_cursor = code_editor_doc.getCursor();
				code_editor_cursor.ch--;
				code_editor.focus();
				code_editor_doc.setCursor(code_editor_cursor);

   				break;

   			default :

   				// Insert
				var caret_position_start = input[0].selectionStart;
				var caret_position_end = input[0].selectionEnd;
				var input_val = input.val();
				input.val(input_val.substring(0, caret_position_start) + function_name + input_val.substring(caret_position_end));
				input.trigger('focus').trigger('change');

				// Set new caret position
				var new_caret_position = caret_position_start + function_name.length - (highlight_parameters ? 1 : 0);
				if(input.prop('selectionStart') !== null) { input.prop('selectionStart', new_caret_position - (highlight_parameters ? parameter_length : 0)); }
				if(input.prop('selectionEnd') !== null) { input.prop('selectionEnd', new_caret_position); }
		}
	}

	// Delete character in an input
	$.WS_Form.prototype.input_delete = function(input) {

		// Get meta_key
		var meta_key = input.attr('data-meta-key-type');

		switch(meta_key) {

			case 'text_editor' :

				var editor_range = tinymce.activeEditor.selection.getRng();
				if(editor_range.endOffset == 0) { break; }
				var node = editor_range.commonAncestorContainer;
				var range = document.createRange();
				range.selectNodeContents(node);
				range.setStart(node, editor_range.endOffset - 1);
				range.setEnd(node, editor_range.endOffset);
				range.deleteContents();
				tinymce.activeEditor.focus();

   				break;

			case 'html_editor' :

				var code_editor = $('.CodeMirror')[0].CodeMirror;
				var code_editor_doc = code_editor.getDoc();
				var code_editor_cursor_start = $.extend(true, {}, code_editor_doc.getCursor());
				var code_editor_cursor_end = $.extend(true, {}, code_editor_doc.getCursor());
				code_editor_cursor_start.ch = code_editor_cursor_start.ch -1;
				code_editor_cursor_end.ch = code_editor_cursor_end.ch;
				code_editor_doc.replaceRange('', code_editor_cursor_start, code_editor_cursor_end);
				code_editor.focus();
				code_editor_doc.setCursor(code_editor_cursor_start);

   				break;

   			default :

				var caret_position = input[0].selectionStart;
				var input_val = input.val();
				input.val(input_val.substring(0, caret_position - 1) + input_val.substring(caret_position - 0));
				input.focus();
				var new_caret_position = caret_position - 1;
				if(input.prop('selectionStart') !== null) { input.prop('selectionStart', new_caret_position); }
				if(input.prop('selectionEnd') !== null) { input.prop('selectionEnd', new_caret_position); }
		}
	}

	// Clear an input
	$.WS_Form.prototype.input_clear = function(input) {

		// Get meta_key
		var meta_key = input.attr('data-meta-key-type');

		switch(meta_key) {

			case 'text_editor' :

				tinymce.activeEditor.setContent('');
				tinymce.activeEditor.focus();

   				break;

			case 'html_editor' :

				var code_editor = $('.CodeMirror')[0].CodeMirror;
				code_editor.setValue("");
				code_editor.focus();

				var code_editor_value = code_editor.getValue();
				var code_editor_textarea = code_editor.getTextArea();
				$(code_editor_textarea).val('').trigger('keyup');

   				break;

   			default :

				input.val('');
				input.focus();
		}
	}

	// Highlight menu
	$.WS_Form.prototype.menu_highlight = function(page) {

		if(typeof(page) === 'undefined') { page = 'ws-form'; }

		// Highlight menu
		$('#toplevel_page_' + page).removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu current').addClass('selected');
		$('[href="admin.php?page=' + page + '"]', $('#toplevel_page_' + page)).closest('li').addClass('wp-menu-open current');
	}

	// Get website URL
	$.WS_Form.prototype.get_plugin_website_url = function(path, medium) {

		if(typeof(path) === 'undefined') { path = ''; }
		var medium_parameter = (typeof(medium) == 'undefined') ? '' : '&utm_medium=' + medium;
		return 'https://wsform.com' + path + '?utm_source=ws_form_pro' + medium_parameter;
	}

	// mod_security fix
	$.WS_Form.prototype.mod_security_fix = function(input_string) {

		var output_string = this.replace_all(input_string, '#', '~WSF%23~');
		output_string = this.replace_all(output_string, '<', '~WSF%60~');
		output_string = this.replace_all(output_string, '>', '~WSF%62~');
		output_string = this.replace_all(output_string, 'SELECT', '~WSFTCELES~');
		output_string = this.replace_all(output_string, 'select', '~WSFtceles~');
		output_string = this.replace_all(output_string, 'Select', '~WSFtceleS~');
		output_string = this.replace_all(output_string, 'style', '~WSFelyts~');
		output_string = this.replace_all(output_string, 'STYLE', '~WSFELYTS~');
		output_string = this.replace_all(output_string, 'src', '~WSFcrs~');
		output_string = this.replace_all(output_string, 'SRC', '~WSFCRS~');
		output_string = this.replace_all(output_string, 'xmlns', '~WSFsnlmx~');
		output_string = this.replace_all(output_string, 'XMLNS', '~WSFSNLMX~');
		output_string = this.replace_all(output_string, 'object_id', '~WSFid_tcejbo~');

		return output_string;
	}

	// Is dragged object a file?
	$.WS_Form.prototype.drag_is_file = function(e) {

		if(
			(typeof(e.originalEvent) !== 'undefined') &&
			(typeof(e.originalEvent.dataTransfer) !== 'undefined') &&
			(typeof(e.originalEvent.dataTransfer.types) !== 'undefined')
		) {

			var data_transfer_types = e.originalEvent.dataTransfer.types;

			return data_transfer_types.includes('Files');
		}

		return true;
	}

	// Tooltip
	$.WS_Form.prototype.tooltip = function(title, position) {

		if(typeof(position) === 'undefined') { position = 'bottom-center'; }

		if($.WS_Form.settings_plugin.helper_icon_tooltip) {

			return ' data-wsf-tooltip="' + this.esc_attr(position) + '" title="' + this.esc_attr(title) + '"';

		} else {

			return ' title="' + this.esc_attr(title) + '"';
		}
	}

	// Tooltips
	$.WS_Form.prototype.tooltips = function() {

		$('#wpbody').on('mouseenter', '[data-wsf-tooltip]', function() {

			$(this).attr('data-wsf-tooltip-title', $(this).attr('title')).removeAttr('title');
		});
		$('#wpbody').on('mouseleave', '[data-wsf-tooltip-title]', function() {

			$(this).attr('title', $(this).attr('data-wsf-tooltip-title')).removeAttr('data-wsf-tooltip-title');
		});
	}

	// Variable helper modal
	$.WS_Form.prototype.variable_helper_modal = function() {

		// Get modal object
		var variables_helper_modal_obj = $('#wsf-variable-helper-modal');

		// Move variable helper to end of body (To ensure draggable works)
		$('#wpbody').append( variables_helper_modal_obj.detach() );

		// Build tabs HTML
		var tabs_html = '';
		var tab_content_html = '';
		var group_id_old = false;

		// Fields
		tabs_html += '<li data-wsf-vh-group-id="hash-field" class="wsf-vh-tab wsf-vh-tab-active">#field</li>';	
		tab_content_html += '<div id="wsf-vh-group-hash-field" class="wsf-vh-group wsf-vh-group-active"></div>';

		// Variables
		for(var parse_variable_help_index in $.WS_Form.parse_variable_help) {

			if(!$.WS_Form.parse_variable_help.hasOwnProperty(parse_variable_help_index)) { continue; }

			var variable = $.WS_Form.parse_variable_help[parse_variable_help_index];

			// Tab
			if(variable.group_id != group_id_old) {

				// Add variable group to tabs
				tabs_html += '<li data-wsf-vh-group-id="' + variable.group_id + '" class="wsf-vh-tab">' + this.esc_html(variable.group_label) + '</li>';				

				// Close tab content
				if(group_id_old !== false) {

					tab_content_html += '</div></div>';
				}

				// Remember old group ID
				group_id_old = variable.group_id;

				// Build content
				tab_content_html += '<div id="wsf-vh-group-' + this.esc_attr(variable.group_id) + '" class="wsf-vh-group">';
				tab_content_html += '<h3>' + this.esc_html(variable.group_label) + '</h3><div class="wsf-vh-group-inner">';
			}

			// Tab content
			tab_content_html += this.variable_helper_modal_var_html(variable);

		}

		// Close tab content
		tab_content_html += '</div></div>';

		// Inject into modal
		$('#wsf-variable-helper-variables').append('<ul id="wsf-variable-helper-tabs">' + tabs_html + '</ul><div id="wsf-variable-helper-tab-content">' + tab_content_html + '</div>');

		// Tabs functionality
		$('.wsf-vh-tab', variables_helper_modal_obj).on('click', function(e) {

			e.preventDefault();

			$.WS_Form.this.variable_helper_modal_tab_show($(this).attr('data-wsf-vh-group-id'));
		})

		// Close
		$('[data-action="wsf-close"]', variables_helper_modal_obj).on('click', function() {

			$.WS_Form.this.variable_helper_modal_close();
		});

		$(window).on('resize', function() {

			$.WS_Form.this.variable_helper_modal_size();
		});

		// Draggable
		this.variable_helper_modal_draggable();

		// Change meta key if a different sidebar input focussed
		$('#wsf-sidebars').on('focus', 'input[type="text"],textarea', function() {

			$.WS_Form.this.variable_helper_modal_insert_obj = $(this);
		});
	}

	// Variable helper modal - Open
	$.WS_Form.prototype.variable_helper_modal_open = function(insert_obj) {

		// Remember meta key
		this.variable_helper_modal_insert_obj = insert_obj ? insert_obj : false;

		// Update field group tab content
		this.variable_helper_modal_field_group();

		// Show modal
		$('#wsf-variable-helper-modal').show();

		// Center
		this.variable_helper_modal_center();

		// Resize
		this.variable_helper_modal_size();

		// Search
		this.variable_helper_modal_search();

		// Insert into meta key
		this.variable_helper_modal_insert();

		// Copy
		this.variable_helper_modal_copy();

		// Focus on the search field
		$('#wsf-variable-helper-search-input').focus();
	}

	// Variable helper modal - Close
	$.WS_Form.prototype.variable_helper_modal_close = function() {

		// Hide modal
		$('#wsf-variable-helper-modal').hide();
	}

	// Variable helper modal - Insert
	$.WS_Form.prototype.variable_helper_modal_insert = function() {

		// Get modal object
		var variables_helper_modal_obj = $('#wsf-variable-helper-modal');

		// Code click event
		$('.wsf-vh-var-code code', variables_helper_modal_obj).off().on('click', function(e) {

			e.preventDefault();

			if($.WS_Form.this.variable_helper_modal_insert_obj) {

				$.WS_Form.this.input_insert_text($.WS_Form.this.variable_helper_modal_insert_obj, $(this).html());
			}
		});
	}

	// Variable helper modal - Copy
	$.WS_Form.prototype.variable_helper_modal_copy = function() {

		// Get modal object
		var variables_helper_modal_obj = $('#wsf-variable-helper-modal');

		// Code copy event
		$('.wsf-vh-var-copy', variables_helper_modal_obj).off().on('click', function(e) {

			e.preventDefault();

			// Get text to copy
			var copy_text = $('code', $(this).closest('.wsf-vh-var-code')).html();
			if(copy_text === undefined) { return; }

			// Copy text to clipboard
			var copy_to_obj = $('<input>');
			$('body').append(copy_to_obj);
			copy_to_obj.val(copy_text).trigger('select');
			document.execCommand('copy');
			copy_to_obj.remove();
		});
	}

	// Variable helper modal - Center
	$.WS_Form.prototype.variable_helper_modal_center = function() {

		// Get modal object
		var variables_helper_modal_obj = $('#wsf-variable-helper-modal');

		// Get modal size
		var modal_width = variables_helper_modal_obj.outerWidth();
		var modal_height = variables_helper_modal_obj.outerHeight();

		// Get viewport size
		var viewport_width = $(window).width();
		var viewport_height = $(window).height();

		// Calculate new offsets
		var offset_top_new = (viewport_height / 2) - (modal_height / 2);
		var offset_left_new = (viewport_width / 2) - (modal_width / 2);

		// Set new offsets
		variables_helper_modal_obj.css('top', offset_top_new + 'px');
		variables_helper_modal_obj.css('left', offset_left_new + 'px');
	}

	// Variable helper modal - Draggable
	$.WS_Form.prototype.variable_helper_modal_draggable = function() {

		// Get modal object
		var modal_obj = $('#wsf-variable-helper-modal');

		// Get modal header object
		var modal_header_obj = $('#wsf-variable-helper-header');

		// Mouse down event
		modal_header_obj.on('mousedown', function(mde) {

			mde = mde || window.event;
			mde.preventDefault();

			// Get initial offset
			var offset = modal_obj.offset();
			var offset_top_initial = offset.top;
			var offset_left_initial = offset.left;

			// Get initial mouse positions
			var mouse_x_initial = mde.clientX;
			var mouse_y_initial = mde.clientY;

			// Get window size
			var window_width = $(window).width();
			var window_height = $(window).height();

			// Get height of header
			var modal_header_height = modal_header_obj.outerHeight();

			// Get modal width
			var modal_width = modal_obj.outerWidth();

			$(document).on('mouseup', function() {

				$(document).off('mouseup').off('mousemove');
			});

			$(document).on('mousemove', function(mme) {

				mme = mme || window.event;
				mme.preventDefault();

				// Calculate movement
				var movement_x = mme.clientX - mouse_x_initial;
				var movement_y = mme.clientY - mouse_y_initial;

				// Calculate offsets
				var offset_top_new = offset_top_initial + movement_y;
				var offset_left_new = offset_left_initial + movement_x;

				// Calculate bounds
				var bound_top_min = 0;
				var bound_top_max = (window_height - modal_header_height);
				var bound_left_min = 0;
				var bound_left_max = (window_width - modal_width);

				// Bounds
				if(offset_top_new < bound_top_min) { offset_top_new = bound_top_min; }
				if(offset_top_new > bound_top_max) { offset_top_new = bound_top_max; }
				if(offset_left_new < bound_left_min) { offset_left_new = bound_left_min; }
				if(offset_left_new > bound_left_max) { offset_left_new = bound_left_max; }

				// Move modal
				modal_obj.css('top', offset_top_new + 'px');
				modal_obj.css('left', offset_left_new + 'px');
			});
		});
  	}

	// Variable helper modal - Show tab
	$.WS_Form.prototype.variable_helper_modal_tab_show = function(group_id) {

		// Get modal object
		var variables_helper_modal_obj = $('#wsf-variable-helper-modal');

		// Remove existing active class
		$('.wsf-vh-tab-active', variables_helper_modal_obj).removeClass('wsf-vh-tab-active');

		// Add active class to current tab
		$('.wsf-vh-tab[data-wsf-vh-group-id="' + group_id + '"]').addClass('wsf-vh-tab-active');

		// Hide tab content
		$('.wsf-vh-group-active', variables_helper_modal_obj).removeClass('wsf-vh-group-active');

		// Show tab content
		$('#wsf-vh-group-' + group_id).addClass('wsf-vh-group-active');
	}

	// Variable helper modal - Size
	$.WS_Form.prototype.variable_helper_modal_size = function() {

		// Get modal object
		var variables_helper_modal_obj = $('#wsf-variable-helper-modal');

		// Calculate modal height
		var height_modal = variables_helper_modal_obj.outerHeight();

		// Calculate title height
		var height_title = $('.wsf-modal-title', variables_helper_modal_obj).outerHeight();

		// Calculate search height
		var height_search = $('#wsf-variable-helper-search').outerHeight();

		// Calculate tabs height
		var height_tabs = $('#wsf-variable-helper-tabs').outerHeight();

		// Calculate h3 height
		var height_h3 = $('h3', $('#wsf-variable-helper-tab-content')).outerHeight();

		// Calculate height for tab content
		var height_content = height_modal - (height_title + 10 + height_search + 10 + height_tabs + 10 + height_h3) - 16;

		// Set tab content height
		$('.wsf-vh-group-inner', $('#wsf-variable-helper-tab-content')).css('height', height_content + 'px');
	}

	// Variable helper modal - Search
	$.WS_Form.prototype.variable_helper_modal_search = function() {

		// Build search array
		var search_array = [];

		// Fields

		// Process tabs
		for(var group_index in $.WS_Form.this.form.groups) {

			if(!$.WS_Form.this.form.groups.hasOwnProperty(group_index)) { continue; }

			var group = $.WS_Form.this.form.groups[group_index];

			// Process sections
			for(var section_index in group.sections) {

				if(!group.sections.hasOwnProperty(section_index)) { continue; }

				var section = group.sections[section_index];

				// Process fields
				for(var field_index in section.fields) {

					if(!section.fields.hasOwnProperty(field_index)) { continue; }

					var field = section.fields[field_index];

					var field_type_config = $.WS_Form.field_type_cache[field.type];

					if((typeof(field_type_config['value_out'])) ? field_type_config['value_out'] : false) {

						// Build keyword
						var keyword = '#field(' + field.id + ') #field(' + field.id + ', ' + field.label.toLowerCase();
						var key = 'field_' + field.id;

						search_array.push({keyword: keyword, key: key, group_id: 'hash-field'});
					}
				}
			}
		}

		// Variables
		for(var parse_variable_help_index in $.WS_Form.parse_variable_help) {

			if(!$.WS_Form.parse_variable_help.hasOwnProperty(parse_variable_help_index)) { continue; }

			var variable = $.WS_Form.parse_variable_help[parse_variable_help_index];
 
			// Add variable
			var keyword = variable.value.substring(1).toLowerCase();
			keyword += ' #' + keyword;

			// Add keywords
			if(typeof(variable.keyword) !== 'undefined') {

				keyword += ' ' + variable.keyword.toLowerCase();
			}

			// Add group label
			keyword += ' ' + variable.group_label.toLowerCase();;

			// Add to search array
			search_array.push({keyword: keyword, key: variable.key, group_id: variable.group_id});
		}

		// Search
		$('#wsf-variable-helper-search-input').off().on('input change paste', function() {

			var keywords = $(this).val();
			keywords = keywords.toLowerCase().trim();

			if(
				(keywords.length <= 2) &&
				isNaN(keywords)
			) {

				// Show all tabs
				$('#wsf-variable-helper-tabs li.wsf-vh-tab').show();

				// Show all variables
				$('#wsf-variable-helper-tab-content .wsf-vh-var').show().removeAttr('data-hidden');

				// Hide no results
				$('#wsf-variable-helper-tab-content').show();
				$('#wsf-variable-helper-search-no-results').hide();

				// Resize
				$.WS_Form.this.variable_helper_modal_size();

				return;
			}

			var keyword_array = keywords.split(' ');

			var variables_matched = [];
			var variables_matched_count = 0;

			var group_ids_matched = [];

			for(var keyword_array_index in keyword_array) {

				if(!keyword_array.hasOwnProperty(keyword_array_index)) { continue; }

				var keyword = keyword_array[keyword_array_index];
				keyword = keyword.trim();

				search_array.find(function(search_array_config) {

					var score = 0;

					var search_array_keyword = search_array_config.keyword;

					var search_array_keyword_indexof = search_array_keyword.indexOf(keyword);

					if(search_array_keyword_indexof === 0) { score += 2; }
					if(search_array_keyword_indexof > 0) { score += 1; }

					if(score > 0) {

						if(typeof(variables_matched[search_array_config.key]) === 'undefined') {

							variables_matched[search_array_config.key] = score;

						} else {

							variables_matched[search_array_config.key] += score;
						}

						var group_id = search_array_config.group_id;

						if(group_ids_matched.indexOf(group_id) === -1) {

							group_ids_matched.push(group_id);
						}

						variables_matched_count++;
					}
				});
			}

			// Show results
			$('#wsf-variable-helper-tabs li.wsf-vh-tab').hide();
			$('#wsf-variable-helper-tab-content').show();
			$('#wsf-variable-helper-tab-content .wsf-vh-var').hide();
			$('#wsf-variable-helper-search-no-results').hide();

			if(variables_matched_count) {

				// Show matching tabs
				for(var group_ids_matched_index in group_ids_matched) {

					if(!group_ids_matched.hasOwnProperty(group_ids_matched_index)) { continue; }

					var group_id = group_ids_matched[group_ids_matched_index];

					$('#wsf-variable-helper-tabs li.wsf-vh-tab[data-wsf-vh-group-id="' + group_id + '"]').show();
				}

				// Show matching variables
				for(var key in variables_matched) {

					if(!variables_matched.hasOwnProperty(key)) { continue; }

					$('#wsf-variable-helper-tab-content .wsf-vh-var[data-wsf-vh-key="' + $.WS_Form.this.esc_selector(key) + '"]').show();
				}

				// Get first tab
				var tab_first_obj = $('#wsf-variable-helper-tabs li.wsf-vh-tab:visible').first();

				// Get group ID
				var group_id = tab_first_obj.attr('data-wsf-vh-group-id');

				// Show tab
				$.WS_Form.this.variable_helper_modal_tab_show(group_id);

			} else {

				// Show no results
				$('#wsf-variable-helper-tab-content').hide();
				$('#wsf-variable-helper-search-no-results').show();
			}

			// Resize
			$.WS_Form.this.variable_helper_modal_size();
		});
	}

	// Variable helper modal - Variables (#field)
	$.WS_Form.prototype.variable_helper_modal_field_group = function() {

		// Get modal object
		var variables_helper_modal_obj = $('#wsf-variable-helper-modal');

		// Get number of form tabs
		var group_count = $.WS_Form.this.form.groups.length;

		// Build tab content HTML
		var language_id = this.language('id');
		var language_label = this.language('field');

		var html_form = '';

		// Process tabs
		for(var group_index in $.WS_Form.this.form.groups) {

			if(!$.WS_Form.this.form.groups.hasOwnProperty(group_index)) { continue; }

			var group = $.WS_Form.this.form.groups[group_index];

			// Ship empty tabs
			if(group.sections.length === 0) { continue; }

			var html_group = '';

			// Process sections
			for(var section_index in group.sections) {

				if(!group.sections.hasOwnProperty(section_index)) { continue; }

				var section = group.sections[section_index];

				// Ship empty sections
				if(section.fields.length === 0) { continue; }

				var html_section = '';

				// Process fields
				for(var field_index in section.fields) {

					if(!section.fields.hasOwnProperty(field_index)) { continue; }

					var field = section.fields[field_index];

					var field_type_config = $.WS_Form.field_type_cache[field.type];

					if((typeof(field_type_config['value_out'])) ? field_type_config['value_out'] : false) {

						html_section += this.variable_helper_modal_var_html({

							key: 'field_' + field.id,
							group_id: 'field',
							group_label: language_label,
							preamble: this.language('field') + ': <strong>' + this.esc_html(field.label) + '</strong>',
							value: '#field(' + field.id + ')',
							usage: ['client', 'action']
						});
					}
				}

				if(html_section) {

					html_group += '<div class="wsf-vh-var-section"><h5>' + this.language('section') + ': <strong>' + this.esc_html(section.label) + '</strong> (' + section.id + ')</h5>' + html_section + '</div>';
				}
			}

			if(html_group) {

				if(group_count > 0) {

					html_form += '<div class="wsf-vh-var-group"><h4>' + this.language('group') + ': <strong>' + this.esc_html(group.label) + '</strong> (' + group.id + ')</h4>';
				}

				html_form += html_group;

				if(group_count > 0) {

					html_form += '</div>';
				}
			}
		}

		if(html_form) {

			html_form = '<h3>#field</h3><div class="wsf-vh-group-inner">' + html_form + '</div>';

		} else {

			html_form = 'Empty form';
		}

		// Set HTML
		$('#wsf-vh-group-hash-field').html(html_form);
	}

	// Variable helper modal - Variable HTML
	$.WS_Form.prototype.variable_helper_modal_var_html = function(variable) {

		var tab_content_html = '<div class="wsf-vh-var" data-wsf-vh-key="' + this.esc_attr(variable.key) + '" data-wsf-vh-group-id="' + this.esc_attr(variable.group_id) + '">';

		// Help - Knowledge base article
		if(variable.kb_slug) {

			// Get knowledge base URL
			var kb_url = this.get_plugin_website_url('/knowledgebase/' + variable.kb_slug + '/', 'variable_helper');

			// Add icon
			tab_content_html += '<a class="wsf-vh-var-kb-url" href="' + this.esc_attr(kb_url) + '" target="_blank"' + this.tooltip(this.language('field_kb_url'), 'left') + ' tabindex="-1">' + this.svg('question-circle') + '</a>';
		}

		// Help - Knowledge base category
		if(variable.kb_category_slug) {

			// Get knowledge base category URL
			var kb_category_url = this.get_plugin_website_url('/knowledgebase_category/' + variable.kb_category_slug + '/', 'variable_helper');

			// Add icon
			tab_content_html += '<a class="wsf-vh-var-kb-url" href="' + this.esc_attr(kb_category_url) + '" target="_blank"' + this.tooltip(this.language('field_kb_url'), 'left') + ' tabindex="-1">' + this.svg('question-circle') + '</a>';
		}

		// Usage
		if(!variable.usage) { variable.usage = ['action']; }	// Fallback for actions

		tab_content_html += '<ul class="wsf-vh-var-usage">';

		// Process sections
		for(var usage_index in variable.usage) {

			if(!variable.usage.hasOwnProperty(usage_index)) { continue; }

			var variable_usage = variable.usage[usage_index];

			tab_content_html += '<li class="wsf-vh-var-usage-' + this.esc_attr(variable_usage) + '">' + this.language('usage_' + variable_usage) + '</li>';
		}

		tab_content_html += '</ul>';

		// Preamble
		if(variable.preamble) {

			// May contain HTML
			tab_content_html += '<p class="wsf-vh-var-preamble">' + variable.preamble + '</p>';
		}

		// Variable syntax
		tab_content_html += '<div class="wsf-vh-var-code"><code' + this.tooltip(this.language('variable_insert'), 'right') + '>' + this.esc_html(variable.value) + '</code> <div class="wsf-vh-var-copy"' + this.tooltip(this.language('clipboard'), 'right') + '>' + this.svg('clone') + '</div></div>';

		// Description
		if(variable.description) {

			// May contain HTML
			tab_content_html += '<p class="wsf-vh-var-description">' + variable.description + '</p>';
		}

		tab_content_html += '</div>';

		return tab_content_html;
	}

	// Input auto size
	$.WS_Form.prototype.input_auto_size = function(input_obj, dummy_class) {

		this.input_auto_size_process(input_obj, dummy_class);

		input_obj.on('change input paste', function() {

			$.WS_Form.this.input_auto_size_process($(this), dummy_class);
		})
	}

	$.WS_Form.prototype.input_auto_size_process = function(input_obj, dummy_class) {

		var input_val = input_obj.val();

		var input_width_obj = $('<span' + ((typeof(dummy_class) !== 'undefined') ? ' class="' + this.esc_attr(dummy_class) + '"' : '') + '>' + this.esc_html(input_val) + '</span>');

		input_width_obj.hide().appendTo(document.body);

		var input_width = input_width_obj.width();

		input_width_obj.remove();

		input_obj.width(input_width);
	}

	// Get meta keys associated with a field type
	$.WS_Form.prototype.field_type_meta_keys = function(field_type, config_filter, config_filter_global) {

		if(typeof(config_filter) === 'undefined') { config_filter = false; }
		if(typeof(config_filter_global) === 'undefined') { config_filter_global = false; }

		var field_type_meta = [];

		// Get fieldsets
		var fieldsets = field_type.fieldsets;

		for(var key in fieldsets) {

			if(!fieldsets.hasOwnProperty(key)) { continue; }

			var fieldset = fieldsets[key];

			// Render fieldset variables
			if(typeof(fieldset.meta_keys) !== 'undefined') {

				for(var key in fieldset.meta_keys) {

					if(!fieldset.meta_keys.hasOwnProperty(key)) { continue; }

					var meta_key = fieldset.meta_keys[key];

					if(config_filter) {

						// Get meta key config
						if(typeof($.WS_Form.meta_keys[meta_key]) === 'undefined') { continue; }
						var meta_key_config = $.WS_Form.meta_keys[meta_key];

						var config_filter_true = (typeof(meta_key_config[config_filter]) !== 'undefined') ? meta_key_config[config_filter] : false;

						if(config_filter_true) {

							var required_setting = true;

							// Check to see if a global override setting exists
							var config_filter_global_value = (typeof(meta_key_config[config_filter_global]) !== 'undefined') ? meta_key_config[config_filter_global] : false;

							if(
								config_filter_global_value &&
								(typeof($.WS_Form.settings_plugin[config_filter_global_value]) !== 'undefined') &&
								($.WS_Form.settings_plugin[config_filter_global_value] !== '') &&
								($.WS_Form.settings_plugin[config_filter_global_value] !== false)
							) {

								required_setting = false;
							}

							if(required_setting) {

								field_type_meta.push(meta_key);
							}
						}

					} else {

						field_type_meta.push(meta_key);
					}
				}
			}

			// Render child fieldset
			if(typeof(fieldset.fieldsets) !== 'undefined') {

				var field_type_meta_keys_return = this.field_type_meta_keys(fieldset, config_filter, config_filter_global);
				if(field_type_meta_keys_return.length > 0) { field_type_meta = field_type_meta.concat(field_type_meta_keys_return); }
			}
		}

		return field_type_meta;
	}

	$.WS_Form.prototype.set_preview_url_styler = function() {

		// Get style ID
		var style_id = this.get_object_meta_value(this.form, 'style_id', 0);

		if(style_id == 0) {

			style_id = ws_form_settings.style_id_default;
		}

		$('[data-action="wsf-style"]').attr('href', this.get_preview_url(this.form.id, false, style_id, false, false));
	}


	$.WS_Form.prototype.get_preview_url = function(form_id, template_id, style_id, conversational, submit_hash) {

		if(typeof(form_id) === 'undefined') { form_id = 0; }
		if(typeof(template_id) === 'undefined') { template_id = 'styler'; }
		if(typeof(conversational) === 'undefined') { conversational = false; }
		if(typeof(submit_hash) === 'undefined') { submit_hash = false; }

		// Get site URL
		var url = ws_form_settings.url_site;

		// Form ID
		var query_args = [];
		query_args.push('wsf_preview' + (conversational ? '_conversational' : '') + '_form_id=' + parseInt(form_id, 10));

		// Template ID
		if(template_id) {

			query_args.push('wsf_preview_template_id=' + template_id);
		}

		// Style ID
		query_args.push('wsf_preview_styler=true');

		// Submit hash
		if(submit_hash) {

			query_args.push('wsf_submit_hash=' + submit_hash);
		}

		// Random 
		var characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		var rand_string = '';
		for(var i = 0; i < 12; ++i) { rand_string += characters.charAt(Math.floor(Math.random() * characters.length)); }


		query_args.push('wsf_rand=' + rand_string);

		return url + '?' + query_args.join('&');
	}

	// Sidebar - Support - Open
	window.sidebar_support_open = function(ws_this, obj_form, obj_button) {

		var obj_outer = $('#wsf-sidebar-support');

		// Button - Cancel
		$('[data-action="wsf-sidebar-cancel"]', obj_outer).on('click', function() {

			ws_this.sidebar_reset();
		});

		// Set up key shortcuts
		$.WS_Form.this.keydown[27] = {'function': function() { ws_this.sidebar_reset(); }, 'ctrl_key': false};

		// Initialize
		var inits = ['knowledgebase'];
		ws_this.sidebar_inits(inits, obj_outer);

		// Open conditional sidebar
		ws_this.sidebar_open('support');
	}

	// Method - Group - Edit
	window.wsf_group_edit = function(ws_this, obj_group, obj_button) {

		ws_this.object_edit(obj_group);
	}

	// Method - Group - Clone
	window.wsf_group_clone = function(ws_this, obj_group, obj_button) {

		// Process form sidebar as a cancel event
		ws_this.object_cancel(obj_group);

		// Clone object
		ws_this.group_put_clone(obj_group);

		// Open toolbox sidebar
		ws_this.sidebar_reset();
	}

	// Method - Group - Delete
	window.wsf_group_delete = function(ws_this, obj_group, obj_button) {

		var buttons = [

			{label:ws_this.language('cancel'), action:'wsf-cancel'},
			{label:ws_this.language('delete'), action:'wsf-confirm', class:'wsf-button-danger'}
		];

		ws_this.popover(ws_this.language('confirm_group_delete'), buttons, obj_group, function() {

			// Process form sidebar as a cancel event
			ws_this.object_cancel(obj_group);

			ws_this.object_delete(obj_group);

			// Open toolbox sidebar
			ws_this.sidebar_reset();
		});
	}

	// Method - Group - Download
	window.wsf_group_download = function(ws_this, obj_group, obj_button) {

		ws_this.object_download(obj_group);
	}

	// Method - Group - Upload
	window.wsf_group_upload = function(ws_this, obj_group, obj_button) {

		ws_this.object_upload(obj_group);
	}

	// Method - Group - Add to Library
	window.wsf_group_template_add = function(ws_this, obj_group, obj_button) {

		ws_this.object_template_add(obj_group);
	}

	// Method - Section - Edit
	window.wsf_section_edit = function(ws_this, obj_section, obj_button) {

		ws_this.object_edit(obj_section);
	}

	// Method - Section - Clone
	window.wsf_section_clone = function(ws_this, obj_section, obj_button) {

		// Process form sidebar as a cancel event
		ws_this.object_cancel(obj_section);

		// Clone objecty
		ws_this.section_put_clone(obj_section);

		// Open toolbox sidebar
		ws_this.sidebar_reset();
	}

	// Method - Section - Delete
	window.wsf_section_delete = function(ws_this, obj_section, obj_button) {

		var buttons = [

			{label:ws_this.language('cancel'), action:'wsf-cancel'},
			{label:ws_this.language('delete'), action:'wsf-confirm', class:'wsf-button-danger'}
		];

		ws_this.popover(ws_this.language('confirm_section_delete'), buttons, obj_section, function() {

			// Process form sidebar as a cancel event
			ws_this.object_cancel(obj_section);

			ws_this.object_delete(obj_section);

			// Open toolbox sidebar
			ws_this.sidebar_reset();
		});
	}

	// Method - Section - Download
	window.wsf_section_download = function(ws_this, obj_section, obj_button) {

		ws_this.object_download(obj_section);
	}

	// Method - Section - Upload
	window.wsf_section_upload = function(ws_this, obj_section, obj_button) {

		ws_this.object_upload(obj_section);
	}

	// Method - Section - Add to Library
	window.wsf_section_template_add = function(ws_this, obj_section, obj_button) {

		ws_this.object_template_add(obj_section);
	}

	// Method - Field - Edit
	window.wsf_field_edit = function(ws_this, obj_field, obj_button) {

		ws_this.object_edit(obj_field);
	}

	// Method - Field - Clone
	window.wsf_field_clone = function(ws_this, obj_field, obj_button) {

		// Save changes on any open objects
		ws_this.object_save_changes();

		// Clone object
		ws_this.field_put_clone(obj_field);

		// Open toolbox sidebar
		ws_this.sidebar_reset();
	}

	// Method - Field - Delete
	window.wsf_field_delete = function(ws_this, obj_field, obj_button) {

		var buttons = [

			{label:ws_this.language('cancel'), action:'wsf-cancel'},
			{label:ws_this.language('delete'), action:'wsf-confirm', class:'wsf-button-danger'}
		];

		ws_this.popover(ws_this.language('confirm_field_delete'), buttons, obj_field, function() {

			// Process form sidebar as a cancel event
			ws_this.object_cancel(obj_field);

			// Delete object
			ws_this.object_delete(obj_field);

			// Open toolbox sidebar
			ws_this.sidebar_reset();
		});
	}

})(jQuery);
