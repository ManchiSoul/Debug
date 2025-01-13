(function($) {

	'use strict';

	// Form file inputs
	$.WS_Form.prototype.form_file = function() {

		var ws_this = this;

		// Regular file fields
		$('input[type="file"]:not([data-init-file])', this.form_canvas_obj).each(function() {

	 		// Get field data
			var field = ws_this.get_field($(this));

			// Check for file preview
			var obj_file_preview = false;
			var preview = (ws_this.get_object_meta_value(field, 'file_preview', false) === 'on');
			if(preview) {

				// Field - Attributes - Orientation
				var orientation = ws_this.get_object_meta_value(field, 'orientation', false);

				// Get skin grid gutter
				var skin_grid_gutter = ws_form_settings.skin_grid_gutter;

				switch(orientation) {

					case 'grid' :

						// Get wrapper and row classes
						var class_orientation_wrapper_array = ws_this.get_field_value_fallback(field.type, false, 'class_orientation_wrapper', []);
						var class_orientation_row_array = ws_this.get_field_value_fallback(field.type, false, 'class_orientation_row', []);
						class_orientation_wrapper_array.push('wsf-file-preview');
						var orientation_group_wrapper_class = class_orientation_wrapper_array.join(' ');
						var orientation_class_array = ws_this.column_class_array(field, 'orientation_breakpoint');
						orientation_class_array = class_orientation_row_array.concat(orientation_class_array);
						var orientation_row_class = orientation_class_array.join(' ');

						// Get row and img styles
						var orientation_row_style = '';
						var orientation_el_style = 'display: block; height: revert; margin-top: ' + skin_grid_gutter + 'px; width: 100%;';

						break;

					case 'horizontal' :

						// Get wrapper and row classes
						var orientation_group_wrapper_class = 'wsf-file-preview';
						var orientation_row_class = '';

						// Get row and img styles
						var file_preview_width = ws_this.get_object_meta_value(field, 'file_preview_width', false);
						var orientation_row_style = 'display: inline-block; -webkit-margin-end: ' + skin_grid_gutter + 'px; margin-inline-end: ' + skin_grid_gutter + 'px; vertical-align: top;' + ((file_preview_width !== false) ? ' width: ' + file_preview_width + ';' : '');
						var orientation_el_style = 'display: block; height: revert; margin-top: ' + skin_grid_gutter + 'px; max-width: 100%; width: revert;';

						break;

					default :

						// Get wrapper and row classes
						var orientation_group_wrapper_class = 'wsf-file-preview';
						var orientation_row_class = '';

						// Get row and img styles
						var orientation_row_style = '';
						var orientation_el_style = 'display: block; height: revert; margin-top: ' + skin_grid_gutter + 'px; max-width: 100%; width: 100%;';
				}

				// Initialize
				var obj_file_preview = $('<div' + ((orientation_group_wrapper_class != '') ? ' class="' + ws_this.esc_attr(orientation_group_wrapper_class) + '"' : '') + '></div>');
				$(this).closest('[data-type]').append(obj_file_preview);
			}

			$(this).on('change', function() {

				var files = $(this)[0].files;

				// File preview?
				if(obj_file_preview !== false) {

					ws_this.form_file_preview(files, obj_file_preview, orientation_row_class, orientation_row_style, orientation_el_style);
				}
			});

			// Flag so it only initializes once
			$(this).attr('data-init-file', '');
		});

		// DropzoneJS
		if(typeof(Dropzone) !== 'undefined') {

	 		$('[data-file-type="dropzonejs"]:not([data-init-file])', this.form_canvas_obj).each(function() {

		 		// Get field data
				var field_id = ws_this.get_field_id($(this));
				var field = ws_this.get_field($(this));

				Dropzone.autoDiscover = false;

				var field_obj = $(this);
				var field_obj_id = $(this).attr('id');
				var field_multiple = (typeof($(this).attr('multiple')) !== 'undefined');

				// Progress
				var progress_objs = $('[data-source="post_progress"]', this.form_canvas_obj);

				// Orientation
				var orientation = ws_this.get_object_meta_value(field, 'orientation', false);

				var file_thumbnail_width = null;
				var file_thumbnail_height = null;

				// Get skin grid gutter
				var skin_grid_gutter = ws_form_settings.skin_grid_gutter;

				switch(orientation) {

					case 'grid' :

						// Get wrapper and row classes
						var class_orientation_wrapper_array = ws_this.get_field_value_fallback('file', false, 'class_orientation_wrapper', []);
						class_orientation_wrapper_array.push('wsf-dropzonejs-previews');
						var orientation_group_wrapper_class = class_orientation_wrapper_array.join(' ');

						var class_orientation_row_array = ws_this.get_field_value_fallback('file', false, 'class_orientation_row', []);
						var orientation_class_array = ws_this.column_class_array(field, 'orientation_breakpoint');
						orientation_class_array = class_orientation_row_array.concat(orientation_class_array);
						orientation_class_array.push('wsf-dropzonejs-preview');
						var orientation_row_class = orientation_class_array.join(' ');

						// Get row and img styles
						var orientation_row_style = 'margin-bottom: ' + skin_grid_gutter + 'px;';
						var orientation_el_style = 'display: block; height: revert; width: 100%;';

						break;

					case 'horizontal' :

						// Get wrapper and row classes
						var orientation_group_wrapper_class = 'wsf-dropzonejs-previews';
						var orientation_row_class = 'wsf-dropzonejs-preview';

						// Get row and img styles
						var file_preview_width = ws_this.get_object_meta_value(field, 'file_preview_width', false);
						var orientation_row_style = 'display: inline-block; margin-bottom: ' + skin_grid_gutter + 'px; -webkit-margin-end: ' + skin_grid_gutter + 'px; margin-inline-end: ' + skin_grid_gutter + 'px; vertical-align: top;' + ((file_preview_width !== false) ? ' width: ' + file_preview_width + ';' : '');
						var orientation_el_style = 'display: block; width: revert; height: revert; max-width: 100%;';

						var file_preview_width_numeric = parseInt(ws_this.replace_all(file_preview_width, 'px', ''), 10);

						file_thumbnail_width = file_preview_width_numeric;
						file_thumbnail_height = file_preview_width_numeric;

						break;

					default :

						// Get wrapper and row classes
						var orientation_group_wrapper_class = 'wsf-dropzonejs-previews';
						var orientation_row_class = 'wsf-dropzonejs-preview';

						// Get row and img styles
						var orientation_row_style = 'margin-bottom: ' + skin_grid_gutter + 'px;';
						var orientation_el_style = 'display: block; height: revert; max-width: 100%; width: 100%;';
				}

				// Apply classes
				$('#' + field_obj_id + '-dropzonejs-previews').attr('class', orientation_group_wrapper_class);

				// Preview template - Error message
				var mask_invalid_feedback = ws_this.get_field_value_fallback('file', false, 'mask_invalid_feedback_dropzonejs_preview', '');
				var mask_values_invalid_feedback = [];
				mask_values_invalid_feedback['invalid_feedback_id'] = '#' + field_obj_id + '-dropzone-error-message';
				var class_invalid_feedback_array = ws_this.get_field_value_fallback('file', false, 'class_invalid_feedback', []);
				mask_values_invalid_feedback['invalid_feedback_class'] = class_invalid_feedback_array.join(' ');
				mask_values_invalid_feedback['invalid_feedback'] = '';
				mask_values_invalid_feedback['attributes'] = ' data-dz-errormessage';
				var preview_template_error_message = ws_this.mask_parse(mask_invalid_feedback, mask_values_invalid_feedback);

				// Preview template - Name, size, remove
				var mask_help = ws_this.get_field_value_fallback('file', false, 'mask_help_dropzonejs_preview', '');
				var mask_values_help = [];
				mask_values_help['help_id'] = '#' + field_obj_id + '-dropzone-name';
				var class_help_array = ws_this.get_field_value_fallback('file', false, 'class_help_post', []);
				mask_values_help['help_class'] = class_help_array.join(' ');
				mask_values_help['help'] = '';
				mask_values_help['help_append'] = '';

				// Name
				mask_values_help['attributes'] = ' data-dz-name';
				var preview_template_name = ws_this.mask_parse(mask_help, mask_values_help);

				// Size
				mask_values_help['attributes'] = ' data-dz-size';
				var preview_template_size = ws_this.mask_parse(mask_help, mask_values_help);

				// Remove
				mask_values_help['attributes'] = '';
				mask_values_help['help'] = '<a href="#" data-dz-remove>' + ws_this.language('dropzonejs_remove') + '</a>';
				var preview_template_remove = ws_this.mask_parse(mask_help, mask_values_help);

				// Preview template
				var preview_template = '<div class="' + (orientation_row_class ? ' ' + ws_this.esc_attr(orientation_row_class) : '') + ((orientation_row_style != '') ? '" style="' + ws_this.esc_attr(orientation_row_style) + '"' : '') + '">';
				preview_template += '<img data-dz-thumbnail style="' + (orientation_el_style ? ' ' + orientation_el_style : '') + '" />';
				preview_template += preview_template_error_message;
				preview_template += preview_template_name;
				preview_template += preview_template_size;
				preview_template += '<div class="wsf-progress"><div class="wsf-upload" data-dz-uploadprogress></div></div>';
				preview_template += preview_template_remove;
				preview_template += '</div>';

				// DropzoneJS args
				var args = {

					url : ws_form_settings.url_ajax + 'field/' + field_id + '/dropzonejs/',
					paramName: 'file',
					uploadMultiple: field_multiple,
					previewTemplate: preview_template,
					previewsContainer: '#' + field_obj_id + '-dropzonejs-previews',
					thumbnailWidth: file_thumbnail_width,
					thumbnailHeight: file_thumbnail_height,
					init: function() {

						this.add_custom_file = function(file, thumbnail_url, response) {

							this.files.push(file);
							this.emit('addedfile', file);
							this.emit('thumbnail', file, thumbnail_url);
							this.emit('processing', file);
							this.emit('success', file, response, false);
							this.emit('complete', file);

							this.element.classList.add('dz-started');
						}

						this.ajax_complete = function() {

							if(ws_this.dropzonejs_processes > 0) {

								ws_this.dropzonejs_processes--;
							}

							if(ws_this.dropzonejs_processes === 0) {

								ws_this.form_post_unlock('progress', false, true);

								progress_objs.each(function() {

									ws_this.form_progress_set_value($(this), 0);
								});
							}
						};

						this.input_update = function() {

							// Rebuild 
							var attachment_ids = {};

							for(var dropzonejs_obj_files_index in dropzonejs_obj.files) {

								if(!dropzonejs_obj.files.hasOwnProperty(dropzonejs_obj_files_index)) { continue; }

								var file = dropzonejs_obj.files[dropzonejs_obj_files_index];

								if(
									(file.status !== 'success') ||
									(typeof(file.upload.uuid) === 'undefined') ||
									(typeof(file.upload.attachment_id) === 'undefined')
								) {

									continue;
								}

								attachment_ids[file.upload.uuid] = file.upload.attachment_id;
							}

							// Push to field value
							field_obj.val(Object.keys(attachment_ids).length ? JSON.stringify({type: 'dropzonejs', attachment_ids: attachment_ids}) : '').trigger('change');
						}

						this.on('addedfile', function(file) {

							this.element.classList.add('dz-started');

							ws_this.dropzonejs_processes++;
						});
					}
				};

				// Accepted files
				var field_accepted_files = ws_this.get_object_meta_value(field, 'accept', '');
				if(field_accepted_files != '') { args.acceptedFiles = field_accepted_files; }

				// Max files
				var field_max_files = parseInt(ws_this.get_object_meta_value(field, 'file_max', 0), 10);
				args.maxFiles = field_multiple ? ((field_max_files > 0) ? field_max_files : null) : 1;

				// Max file size
				var field_max_size = parseInt(ws_this.get_object_meta_value(field, 'file_max_size', 0), 10);
				if(field_max_size > 0) { args.maxFilesize = field_max_size; }

				// Resize - Width
				var field_resize_width = parseInt(ws_this.get_object_meta_value(field, 'file_image_max_width', 0), 10);
				if(field_resize_width > 0) { args.resizeWidth = field_resize_width; }

				// Resize - Height
				var field_resize_height = parseInt(ws_this.get_object_meta_value(field, 'file_image_max_height', 0), 10);
				if(field_resize_height > 0) { args.resizeHeight = field_resize_height; }

				// Resize - Method
				var field_resize_method = (ws_this.get_object_meta_value(field, 'file_image_crop', '') == 'on');
				args.resizeMethod = field_resize_method ? 'crop' : 'contain';

				// Resize - Compression
				var field_resize_quality = parseInt(ws_this.get_object_meta_value(field, 'file_image_max_height', 100), 10);
				if(field_resize_quality > 0) { args.resizeQuality = field_resize_quality / 100; }

				// Resize - MIME
				var field_resize_mime_type = ws_this.get_object_meta_value(field, 'file_image_mime', '');
				if(field_resize_mime_type != '') { args.resizeMimeType = field_resize_mime_type; }

				// Capture
				var field_capture = ws_this.get_object_meta_value(field, 'file_capture', '');
				if(field_capture != '') { args.capture = field_capture; }

				// Timeout
				var file_timeout = parseInt(ws_this.get_object_meta_value(field, 'file_timeout', ''), 10);
				if(file_timeout > 0) { args.timeout = file_timeout; }

				// Create DropzoneJS
				var dropzonejs_obj = new Dropzone('#' + field_obj_id + '-dropzonejs', args);

				// Event handling (Pass through to input field)
				$('#' + field_obj_id + '-dropzonejs').on('click mousedown mouseup mouseover mouseout touchstart touchend touchmove touchcancel', function(e) {

					$('#' + field_obj_id).trigger(e.type);
				});

				// Sortable
				if(
					field_multiple &&
					ws_this.get_object_meta_value(field, 'dropzonejs_sortable', 'on')
				) {

					$('#' + field_obj_id + '-dropzonejs').sortable({

						items: '.wsf-dropzonejs-preview',
						cursor: 'move',
						tolerance: 'pointer',
						forceHelperSize: true,
						cancel: '[data-dz-remove]',

						start: function(e, ui) {

							// Get index being dragged
							ws_this.dropzonejs_index_dragged_from = ui.helper.index();

							ui.placeholder.attr('style', orientation_row_style);

							var height = ui.helper.height();
							var width = ui.helper.outerWidth();
							ui.placeholder.css({width: width + 'px', height: height + 'px'});
						},

						stop: function(e, ui) {

							// Get index dragged to
							var dropzonejs_index_old = ws_this.dropzonejs_index_dragged_from;
							var dropzonejs_index_new = ui.item.index();

							// Move meta data index
							if (dropzonejs_index_new >= dropzonejs_obj.files.length) {

								var k = dropzonejs_index_new - dropzonejs_obj.files.length;
								while ((k--) + 1) {
									dropzonejs_obj.files.push(undefined);
								}
							}
							dropzonejs_obj.files.splice(dropzonejs_index_new, 0, dropzonejs_obj.files.splice(dropzonejs_index_old, 1)[0]);

							// Push to field value
							dropzonejs_obj.input_update();
						},
					});
				}

				// Save default value
				$(this).attr('data-default-value', $(this).val());

				// Populate files from input
				ws_this.form_file_dropzonejs_populate($(this), false);

				ws_this.dropzonejs_processes = 0;

				dropzonejs_obj.on('sending', function(file, xhr, form_data) {

					// NONCE
					if(ws_form_settings.wsf_nonce) {

						form_data.append(ws_form_settings.wsf_nonce_field_name, ws_form_settings.wsf_nonce);
					}
					if(ws_form_settings.x_wp_nonce) {

						form_data.append('_wpnonce', ws_form_settings.x_wp_nonce);
					}

					// Form ID
					form_data.append('id', ws_this.form_id);

					// Preview
					form_data.append('preview', ws_this.form_canvas_obj[0].hasAttribute('data-preview'));
				});

				dropzonejs_obj.on('success', function(file, response, action) {

					// If this was fired as a result of form_file_dropzonejs_populate, ignore
					if(file.wsf_preload) { return; }

					if(
						(typeof(response.error) !== 'undefined') &&
						(response.error === false)
					) {

						this.options.success(file);

						if(
							(typeof(response.file_objects) === 'object') &&
							(response.file_objects.length > 0)
						) {

							// Get existing attachment ids
							try {

								var field_val = JSON.parse(field_obj.val());
								var attachment_ids = field_val.attachment_ids;

							} catch(e) {

								var attachment_ids = {};
							}

							// Check to see if the UUID is already assigned
							var next_attachment_id = false;

							for(var file_object_index in response.file_objects) {

								if(!response.file_objects.hasOwnProperty(file_object_index)) { continue; }

								var file_object = response.file_objects[file_object_index];

								var attachment_id = file_object.attachment_id;

								if(Object.values(attachment_ids).indexOf(attachment_id) === -1) {

									next_attachment_id = attachment_id;
									break;
								}
							}

							if(next_attachment_id === false) { return; }

							// Save attachment ID to file
							file.upload.attachment_id = next_attachment_id;

							// Get preview element
							if(
								(typeof(file_object) === 'object') &&
								(typeof(file_object.preview) !== 'undefined') &&
								(typeof(file.previewElement) !== 'undefined')
							) {

								// Get preview thumbnail
								var preview_thumbnail = $('img[data-dz-thumbnail]', $(file.previewElement));

								if(preview_thumbnail.length) {

									// If no thumnail exists, set it
									if(typeof(preview_thumbnail.attr('src')) === 'undefined') {

										preview_thumbnail.attr('src', file_object.preview);
									}
								}
							}
						}

						// Push to field value
						this.input_update();

					} else {

						this.options.error(file, response.error_message ? response.error_message : ws_this.language('error_file_upload'));
					}
				});

				dropzonejs_obj.on('processing', function() {

					ws_this.form_post_lock('progress', true, true);
				});

				dropzonejs_obj.on('totaluploadprogress', function(progress) {

					progress_objs.each(function() {

						ws_this.form_progress_set_value($(this), (progress / 100));
					});

					if(progress > 99) {

						$('.wsf-progress', $(this.element)).addClass('wsf-progress-success');
					}
				});

				dropzonejs_obj.on('complete', function() { this.ajax_complete(); });

				dropzonejs_obj.on('canceled', function() { this.ajax_complete(); });

				dropzonejs_obj.on('removedfile', function(file) {

					// Push to field value
					this.input_update();

					this.ajax_complete();
				});

				dropzonejs_obj.on('error', function(file, response) {

					switch(typeof(response)) {

						case 'string' :

							var error_message = response;

							// Check for JSON
							try {

								var response_decoded = JSON.parse(response);

								if(response_decoded !== null) {

									if(response_decoded.error && response_decoded.error_message) {

										var error_message = response_decoded.error_message;
									}
								}

							} catch(e) {}

							break;

						case 'object' :

							// Check for error message
							if(response.error && response.error_message) {

								var error_message = response.error_message;
	
							} else {

								var error_message = JSON.stringify(response);
							}

							break;

						default :

							var error_message = ws_this.language('error_file_upload');
					}

				    $(file.previewElement).find('[data-dz-errormessage]').html(error_message);
				});

				// Check if section is disabled
				var disabled = field_obj.attr('disabled');
				if(!disabled) {

					disabled = disabled || $(this).closest('fieldset').attr('disabled');
				}

				if(disabled) {

					dropzonejs_obj.disable();
				}

				// Flag so it only initializes once
				$(this).attr('data-init-file', '');
			});
		}
	}

	$.WS_Form.prototype.form_file_dropzonejs_populate = function(obj, clear) {

		// Get dropzone object
		var dropzonejs_obj = $('+ .dropzone', obj)[0].dropzone;

		// Reset field
		dropzonejs_obj.removeAllFiles(true);
		obj.val('');

		// If only clearing, return
		if(clear) { return; }

		// Check for existing files
		var default_value = obj.attr('data-default-value');

		// Check if input 
		if(default_value != '') {

			// Get existing file objects
			try {

				var file_objects = JSON.parse(default_value);

			} catch(e) {

				var file_objects = [];
			}

			for(var file_object_index in file_objects) {

				if(!file_objects.hasOwnProperty(file_object_index)) { continue; }

				var file_object = file_objects[file_object_index];

				if(typeof(file_object['name']) === 'undefined') { continue; }

				var file = {

					processing: true,
					accepted: true,
					name: file_object['name'],
					size: file_object['size'],
					type: file_object['type'],
					upload: { uuid: file_object['uuid'], attachment_id: file_object['attachment_id'] },
					status: Dropzone.SUCCESS,
					wsf_preload: true
				};

				// Get preview URL
				var preview = (typeof(file_object['preview']) !== 'undefined') ? file_object['preview'] : file_object['url'];

				// Check UUID
				if(!file.upload.uuid) { file.upload.uuid = Dropzone.uuidv4(); }

				// Add custom file to DropzoneJS
				dropzonejs_obj.add_custom_file(file, preview, { status: 'success' });
			}

			// Push to field value
			dropzonejs_obj.input_update();
		}
	}

	$.WS_Form.prototype.form_file_preview = function(files, obj_file_preview, orientation_row_class, orientation_row_style, orientation_el_style) {

		// Reset
		var file_preview_html = '';
		var files_processed = 0;
		var file_readers = [];
		var file_div_pre = '<div' + ((orientation_row_class != '') ? ' class="' + this.esc_attr(orientation_row_class) + '"' : '') + ((orientation_row_style != '') ? ' style="' + this.esc_attr(orientation_row_style) + '"' : '') + '>';

		// Write fresh wrapper
		obj_file_preview.html('');

		// Process each file
		var preview_count = 0;
		for(var file_index in files) {

			if(!files.hasOwnProperty(file_index)) { continue; }

			var file = files[file_index];

			switch(file.type) {

				// Process file as an image
				case 'image/apng' :
				case 'image/bmp' :
				case 'image/gif' :
				case 'image/jpeg' :
				case 'image/png' :
				case 'image/svg+xml' :
				case 'image/tiff' :
				case 'image/webp' :
				case 'image/x-icon' :

					file_readers[file_index] = new FileReader();
					file_readers[file_index].wsf_file_index = file_index;

					file_readers[file_index].onload = function (e) {

						// Get file index
						var file_index = this.wsf_file_index;

						// Get img src
						var img_src = e.target.result;

						// Get file parameters
						var file = files[file_index];
						var img_alt = file.name;

						// Build image
						var img = $('<img />');
						img.attr('src', img_src);
						img.attr('alt', img_alt);
						img.attr('title', img_alt);
						img.attr('style', orientation_el_style);

						// Add this to the file preview HTML
						obj_file_preview.append(file_div_pre + img[0].outerHTML + '</div>');
					}
					file_readers[file_index].readAsDataURL(file);
					break;

				// Process file as a video
				case 'video/avi' :
				case 'video/mp4' :
				case 'video/ogg' :
				case 'video/ogm' :
				case 'video/ogv' :
				case 'video/webm' :

					var video_src = URL.createObjectURL(file);

					// Build video
					var video = $('<video controls />');
					video.attr('src', video_src);
					video.attr('style', orientation_el_style);
					video.html(this.language('error_not_supported_video'));

					// Add this to the file preview HTML
					obj_file_preview.append(file_div_pre + video[0].outerHTML + '</div>');

					break;

				// Process file as audio
				case 'audio/aac' :
				case 'audio/aacp' :
				case 'audio/flac' :
				case 'audio/mp4' :
				case 'audio/mpeg' :
				case 'audio/ogg' :
				case 'audio/wav' :
				case 'audio/webm' :

					var audio_src = URL.createObjectURL(file);

					// Build audio
					var audio = $('<audio controls />');
					audio.attr('src', audio_src);
					audio.attr('style', orientation_el_style + ' height: 40px;');
					audio.html(this.language('error_not_supported_audio'));

					// Add this to the file preview HTML
					obj_file_preview.append(file_div_pre + audio[0].outerHTML + '</div>');

					break;
			}
		}
	}

	// Get file count
	$.WS_Form.prototype.file_get_count_by_field_id = function(field_id) {

		var field_obj = $('[name^="' + this.esc_selector(ws_form_settings.field_prefix + field_id) + '["]', this.form_canvas_obj);
		if(!field_obj.length) { return false; }

		var file_count = false;

		field_obj.each(function() {

			// Check for DropzoneJS
			if(
				$(this).attr('data-file-type') &&
				($(this).attr('data-file-type') === 'dropzonejs')
			) {

				// DropzoneJS
				var obj_wrapper = $(this).closest('[data-type="file"]');

				if(obj_wrapper) {

					var dropzone = $('.dropzone', obj_wrapper)[0].dropzone;

					if(dropzone.files) {

						file_count += dropzone.files.length;
					}
				}

			} else {

				// Default
				file_count += this.files.length;
			}
		});

		return file_count;
	}

})(jQuery);
