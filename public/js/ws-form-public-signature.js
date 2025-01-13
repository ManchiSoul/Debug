(function($) {

	'use strict';

	// Signatures
	$.WS_Form.prototype.form_signature = function() {

		var ws_this = this;

		// Get signature objects
		var signature_objects = $('[data-type="signature"]:not([data-init-signature])', this.form_canvas_obj);
		if(
			!signature_objects.length ||
			(typeof(SignaturePad) === 'undefined')
		) {
			return false;
		}

		// Get current framework
		var framework_groups = this.framework['groups']['public'];
		var class_active = typeof(framework_groups['class_active']) ? framework_groups['class_active'] : false;

		// Process each signature
		signature_objects.each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-signature', '');

			// Input
			var input = $('input', $(this));

			// Canvas
			var canvas = $('canvas', $(this));
			var canvas_element = canvas[0];

			// Name
			var name = input.attr('name');

			// Height
			var height = input.attr('data-height');
			canvas.attr('style', 'height: ' + height + '; padding: 0; width: 100%;');

			// ID
			var id = $(this).attr('data-id');

			// Field
			var field = ws_this.field_data_cache[id];

			// Size
			var dot_size = input.attr('data-dot-size');

			// Crop
			var crop = (input.attr('data-crop') !== undefined);

			// Pen color
			var pen_color = input.attr('data-pen-color');

			// Type
			var mime = (input.attr('data-mime') !== undefined) ? input.attr('data-mime') : 'image/png';

			// Build config
			var config = {

				maxWidth: dot_size,
				penColor: pen_color,
			}

			var signature = {signature: false, canvas: canvas, canvas_element: canvas_element, input: input, name: name, mime: mime, config: config, crop: crop};

			// Background color (JPG only)
			if(mime == 'image/jpeg') {

				var background_color = (canvas.attr('data-background-color') !== undefined) ? canvas.attr('data-background-color') : '#ffffff';
				config.backgroundColor = background_color;
				signature.background_color = background_color;
			}

			// Add to signatures array
			ws_this.signatures.push(signature)

			// Momentarily show group if currently hidden so that width is calculated correctly
			var group_obj = $(signature.canvas_element, ws_this.form_canvas_obj).closest('[id^="' + ws_this.esc_selector(ws_this.form_id_prefix) + 'group-"]');
			var group_visible = group_obj.is(':visible');

			if(!group_visible) {

				if(class_active) {

					group_obj.addClass(class_active);

				} else {

					group_obj.show();
				}
			}

			// Init signature pad
			var signature_pad = new SignaturePad(signature.canvas_element, signature.config);

			// Add to signatures object
			signature.signature = signature_pad;

			// Add signature to signatures_by_name array
			ws_this.signatures_by_name[signature.name] = signature;

			// Clear event
			$('[data-action="wsf-signature-clear"]', $(this)).on('click', function(e) {

				e.preventDefault();

				signature.signature.clear();

				// Clear validation input
				signature.input.val('').trigger('change');
			});

			// Mouse down / touch start event
			signature.canvas.on('mousedown touchstart', function() {

				// Set validation input
				signature.input.val('1').trigger('change');
			});

			$(document).on('keydown', function(e) {

				if(signature.canvas.is(':focus') && (e.keyCode == 27)) {

					signature.signature.clear();

					// Clear validation input
					signature.input.val('').trigger('change');
				}
			});

			// Initial redraw
			ws_this.signature_redraw(signature.signature, signature.canvas_element, true);

			// Check for value
			if(typeof(input.attr('value')) !== 'undefined') {

				// Get file object JSON
				var value_json = input.attr('value');

				// Decode JSON
				try {

					var file_objects = JSON.parse(value_json);

				} catch(e) {

					var file_objects = [];
				}

				// Run through each file object
				for(var file_object_index in file_objects) {

					if(!file_objects.hasOwnProperty(file_object_index)) { continue; }

					// Get file object
					var file_object = file_objects[file_object_index];

					// Get file object URL
					if(
						(typeof(file_object) === 'object') &&
						(typeof(file_object.base64) === 'string')
					) {
						signature_pad.fromDataURL(file_object.base64);
					}
				}
			}

			// Hide group if it was hidden originally
			if(!group_visible) {

				if(class_active) {

					group_obj.removeClass(class_active);

				} else {

					group_obj.hide();
				}
			}

			// Fire real time form validation
			ws_this.form_validate_real_time_process(false, false);
		});
	}

	// Signature - Redraw
	$.WS_Form.prototype.signature_redraw = function(signature, canvas_element, clear) {

		if(typeof(clear) === 'undefined') { clear = false; }

		// If the signature is empty, we'll clear it instead of drawing from data otherwise signature thinks it is not empty
		if(!clear && signature.isEmpty()) { clear = true; }

		// Remember canvas
		if(!clear) {

			var canvas_data = signature.toDataURL();
		}

		// Resize
		var ratio = Math.max(window.devicePixelRatio || 1, 1);
		canvas_element.width = canvas_element.offsetWidth * ratio;
		canvas_element.height = canvas_element.offsetHeight * ratio;
		canvas_element.getContext("2d").scale(ratio, ratio);

		// Redraw canvas using original data
		if(clear) {

			signature.clear();

		} else {

			signature.fromDataURL(canvas_data);
		}
	}

	// Signatures - Redraw
	$.WS_Form.prototype.signatures_redraw = function(tab_index, section_id, field_id) {

		if(typeof(section_id) === 'undefined') { section_id = false; }
		if(typeof(field_id) === 'undefined') { field_id = false; }

		for(var signature_index in this.signatures) {

			if(!this.signatures.hasOwnProperty(signature_index)) { continue; }

			var signature = this.signatures[signature_index];

			// Skip if not yet initialized
			if(signature.signature === false) { continue; }

			var signature_canvas_element = signature.canvas_element;

			var signature_tab_index = this.get_group_index($(signature_canvas_element, this.form_canvas_obj));
			var signature_section_id = this.get_section_id($(signature_canvas_element, this.form_canvas_obj));
			var signature_field_id = this.get_field_id($(signature_canvas_element, this.form_canvas_obj));

			if(
				((tab_index !== false) && (signature_tab_index == tab_index)) ||
				((section_id !== false) && (section_id == signature_section_id)) ||
				(field_id == signature_field_id)
			) {
				this.signature_redraw(signature.signature, signature_canvas_element);
			}
		}
	}

	// Signature - Clear all signatures
	$.WS_Form.prototype.signatures_clear = function() {

		for(var signature_index in this.signatures) {

			if(!this.signatures.hasOwnProperty(signature_index)) { continue; }

			var signature = this.signatures[signature_index];

			signature.signature.clear();

			signature.input.val('').trigger('change');
		}
	}

	// Signature - Clear by name
	$.WS_Form.prototype.signature_clear_by_name = function(name) {

		var signature = (typeof(this.signatures_by_name[name]) !== 'undefined') ? this.signatures_by_name[name] : false;
		if(signature !== false) {

			signature.signature.clear();

			signature.input.val('').trigger('change');
		}
	}

	// Signature - Get response by name
	$.WS_Form.prototype.signature_get_response_by_name = function(name) {

		var signature = (typeof(this.signatures_by_name[name]) !== 'undefined') ? this.signatures_by_name[name] : false;
		return (signature !== false) ? (signature.input.val() !== '') : false;
	}

	// Signature - Form post
	$.WS_Form.prototype.signature_form_post = function() {

		// Signatures
		for(var signature_index in this.signatures) {

			if(!this.signatures.hasOwnProperty(signature_index)) { continue; }

			var signature = this.signatures[signature_index];

			// Check for blank signature
			if(
				signature.signature.isEmpty() ||
				(signature.canvas_element.width == 0) ||
				(signature.canvas_element.height == 0)
			) {

				// Add signature image to form data
				$('[name="' + this.esc_selector(signature.name) + '"]', this.form_canvas_obj).val('');
				continue;
			}

			// Get signature data
			var signature_data = signature.signature.toDataURL(signature.mime);

			// Add signature image to form data
			$('[name="' + this.esc_selector(signature.name) + '"]', this.form_canvas_obj).val(signature_data);
		}
	}

})(jQuery);
