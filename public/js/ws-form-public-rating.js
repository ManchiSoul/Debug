(function($) {

	'use strict';

	// Adds rating elements
	$.WS_Form.prototype.form_rating = function() {

		var ws_this = this;

		// Get skin spacing small
		var skin_spacing_small = ws_form_settings.skin_spacing_small;

		// Get rating objects
		$('[data-rating]:not([data-init-rating])', this.form_canvas_obj).each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-rating', '');

			// Get field wrapper
			var field_wrapper = ws_this.get_field_wrapper($(this));

			// Get field
			var field = ws_this.get_field($(this));

			// Read rating data
			var rating_max = parseInt(ws_this.get_object_meta_value(field, 'rating_max', '5'), 10);
			if(isNaN(rating_max)) { rating_max = 5; }
			if(rating_max < 1) { rating_max = 1; }

			// Set min and max
			$(this).attr('min', 0).attr('max', rating_max);

			// Read rating
			var rating_value = $(this).val();

			// Rating design
			var rating_icon = ws_this.get_object_meta_value(field, 'rating_icon', 'star');
			var rating_size = parseInt(ws_this.get_object_meta_value(field, 'rating_size', '24'), 10);
			if(isNaN(rating_size)) { rating_size = 24; }
			if(rating_size < 1) { rating_size = 1; }
			var rating_color_off = $(this).attr('data-rating-color-off');
			var rating_color_on = $(this).attr('data-rating-color-on');

			// Read only
			var readonly = (typeof($(this).attr('readonly')) !== 'undefined');

			// Build rating icon HTML
			if(rating_icon != 'custom') {

				var rating_icon_html = '<svg class="wsf-rating-icon" height="' + ws_this.esc_attr(rating_size) + '" width="' + ws_this.esc_attr(rating_size) + '" viewBox="0 0 16 16" style="display: block; height: auto; max-width: 100%;">';

				switch(rating_icon) {

					// Heart
					case 'heart' :

						rating_icon_html += '<path d="M7.4,13.6c-0.3-0.4-1.2-1.1-1.9-1.7c-2.1-1.6-2.4-1.8-3.2-2.6C0.7,7.9,0,6.4,0,4.5 c0-1,0.1-1.3,0.3-1.9c0.5-1,1.1-1.7,2-2.1C2.9,0.1,3.2,0,4.2,0c1.1,0,1.3,0.1,1.9,0.5c0.8,0.4,1.5,1.3,1.7,1.9L8,2.8l0.2-0.5 c1.4-3,5.8-3,7.4,0.1c0.5,1,0.5,3.1,0.1,4.2c-0.6,1.5-1.6,2.7-4.1,4.5C10,12.2,8.2,14,8,14.3C7.9,14.6,8,14.3,7.4,13.6z" fill="' + ws_this.esc_attr(rating_color_off) + '" />';
						break;

					// Check
					case 'check' :

						rating_icon_html += '<path d="M7.3 14.2l-7.1-5.2 1.7-2.4 4.8 3.5 6.6-8.5 2.3 1.8z" fill="' + ws_this.esc_attr(rating_color_off) + '" />';
						break;

					// Circle
					case 'circle' :

						rating_icon_html += '<path d="M0,8a8,8 0 1,0 16,0a8,8 0 1,0 -16,0" fill="' + ws_this.esc_attr(rating_color_off) + '" />';
						break;

					// Flag
					case 'flag' :

						rating_icon_html += '<path d="M11.8 1.2s-2.7 1.3-5.3.3C3.6.5 2.3.9 1 2.5c-.2-.1-.5-.1-.7 0-.3.2-.4.6-.2.9l7.1 11.5c.1.2.4.3.6.3.1 0 .2 0 .4-.1.3-.2.4-.6.2-1l-3.2-5c1.2-1.5 2.6-1.9 5.4-.9 2.7.9 5.4-.4 5.4-.4l-4.2-6.6z" fill="' + ws_this.esc_attr(rating_color_off) + '" />';
						break;

					// Smiley
					case 'smiley' :

						rating_icon_html += '<path d="M8 16c4.4 0 8-3.6 8-8s-3.6-8-8-8-8 3.6-8 8 3.6 8 8 8zM8 1.5c3.6 0 6.5 2.9 6.5 6.5s-2.9 6.5-6.5 6.5S1.5 11.6 1.5 8 4.4 1.5 8 1.5zM4.6 5.6c0-.6.4-1 1-1s1 .4 1 1-.4 1-1 1-1-.5-1-1zm4.5 0c0-.6.4-1 1-1s1 .4 1 1-.4 1-1 1-1-.5-1-1zM11 9.3l1.3.8c-.9 1.5-2.5 2.4-4.3 2.4s-3.4-1-4.3-2.4L5 9.3c.6 1 1.7 1.7 3 1.7s2.4-.6 3-1.7z" fill="' + ws_this.esc_attr(rating_color_off) + '" />';
						break;

					// Square
					case 'square' :

						rating_icon_html += '<path d="M0 0h16v16H0z" fill="' + ws_this.esc_attr(rating_color_off) + '" />';
						break;

					// Thumb
					case 'thumb' :

						rating_icon_html += '<path d="M13.9 9.9c0 .7-.7 1.2-1.4 1.2.5.1 1.2.7 1.2 1.4s-.7 1.1-1.3 1.1h-.6c.5.1 1.1.8 1.1 1.4 0 .7-.7 1.1-1.4 1.1h-6c-2 0-3.5-1.6-3.5-3.6V8.7c0-1.4.8-2.9 2-3.3.9-.6 2.8-1.7 3.2-3.8V.1s2.6-.3 2.6 2.2c0 2.9-1.8 3.4-.4 3.5h2.2c.8 0 1.4.8 1.4 1.5s-.6 1.1-1.2 1.2h.6c.9 0 1.5.7 1.5 1.4z" fill="' + ws_this.esc_attr(rating_color_off) + '" />';
						break;

					// Star
					default :

						rating_icon_html += '<path d="M12.9 15.8c-1.6-1.2-3.2-2.5-4.9-3.7-1.6 1.3-3.3 2.5-4.9 3.7 0 0-.1 0-.1-.1.6-2 1.2-4 1.9-6C3.3 8.4 1.7 7.2 0 5.9h6C6.7 3.9 7.3 2 8 0h.1c.7 1.9 1.3 3.9 2 5.9H16V6c-1.6 1.3-3.2 2.5-4.9 3.8.6 1.9 1.3 3.9 1.8 6 .1-.1 0 0 0 0z" fill="' + ws_this.esc_attr(rating_color_off) + '" />';
						break;
				}

				rating_icon_html += '</svg>';

			} else {

				var rating_icon_html = ws_this.get_object_meta_value(field, 'rating_icon_html', '<span>*</span>');
			}

			// Read horizontal alignment
			var rating_horizontal_align = ws_this.get_object_meta_value(field, 'horizontal_align', 'left');

			// Build rating HTML
			var rating_html = '<div class="wsf-rating-wrapper" tabindex="0" style="outline:0;"><ul class="wsf-rating" style="display: flex; list-style: none; margin: 0; padding: 0; user-select: none; justify-content: ' + rating_horizontal_align + '">';

			for(var rating_index = 1; rating_index <= rating_max; rating_index++) {

				rating_html += '<li class="wsf-rating-' + ws_this.esc_attr(rating_index + ((rating_index <= rating_value) ? ' wsf-rating-selected' : '')) + '" style="' + (!readonly ? 'cursor: pointer; ' : '') + 'display:inline-block; -webkit-padding-end: ' + ws_this.esc_attr(skin_spacing_small) + 'px; padding-inline-end: ' + ws_this.esc_attr(skin_spacing_small) + 'px; margin: 0;" data-value="' + ws_this.esc_attr(rating_index) + '" title="' + ws_this.esc_attr(rating_index) + '">' + rating_icon_html + '</li>';
			}

			rating_html += '</ul></div>';

			// Add to field
			$(this).after(rating_html);

			// Init
			$('ul.wsf-rating > li', field_wrapper).each(function(e) {

				$('svg.wsf-rating-icon path', $(this)).attr('fill', $(this).hasClass('wsf-rating-selected') ? rating_color_on : rating_color_off);
			});

			// Change to hidden input
			$(this).on('change input', function() {

				ws_this.form_rating_process($(this), Math.round(parseFloat($(this).val()), 0));
			});

			var rating_input = $(this);

			// Only add event handlers if not read only
			if(!readonly) {

				// Event handlers
				$('.wsf-rating-wrapper', field_wrapper).on('keydown', function(e) {

					if(e.keyCode == 39) {

						e.preventDefault();

						var rating_current = rating_input.val();

						if(rating_current < rating_max) {
	
							rating_input.val(++rating_current).trigger('change');
						}
					}

					if(e.keyCode == 37) {

						e.preventDefault();

						var rating_current = rating_input.val();

						if(rating_current > 0) {
	
							rating_input.val(--rating_current).trigger('change');
						}
					}
				});

				$('ul.wsf-rating > li', field_wrapper).on('click touchstart', function() {

					var rating_obj = $('[data-rating]', $(this).closest('[data-id]'));

					var rating_index = parseInt($(this).attr('data-value'), 10); 

					// Set rating
					rating_obj.val(rating_index).attr('data-value-old', rating_index).trigger('change');
				});

				$('ul.wsf-rating > li', field_wrapper).on('mouseover', function() {

					var rating_obj = $('[data-rating]', $(this).closest('[data-id]'));
					var rating_color_off = rating_obj.attr('data-rating-color-off');
					var rating_color_on = rating_obj.attr('data-rating-color-on');

					// Store old value
					rating_obj.attr('data-value-old', rating_obj.val());

					// Get new value
					var rating_value = Math.round(parseFloat($(this).attr('data-value')), 0); 

					// Store new value
					rating_obj.val(rating_value).trigger('input');

					// Now highlight all the stars that's not after the current hovered star
					$(this).parent().children('li').each(function(e) {
		
						if (e < rating_value) {

							$(this).addClass('wsf-rating-hover');
							$('svg.wsf-rating-icon path', $(this)).attr('fill', rating_color_on);

						} else {

							$(this).removeClass('wsf-rating-hover');
							$('svg.wsf-rating-icon path', $(this)).attr('fill', rating_color_off);
						}

					})

				}).on('mouseout', function() {

					var rating_obj = $('[data-rating]', $(this).closest('[data-id]'));

					// If an old value is set, reset it
					if(typeof(rating_obj.attr('data-value-old')) !== 'undefined') {

						var rating_value_old = rating_obj.attr('data-value-old');
						rating_obj.val(rating_value_old).trigger('input');
					}

					$(this).parent().children('li').each(function(e) {

						$(this).removeClass('wsf-rating-hover');

						$('svg.wsf-rating-icon path', $(this)).attr('fill', $(this).hasClass('wsf-rating-selected') ? rating_color_on : rating_color_off);
					});
				});
			}
		});
	}

	// Rating process
	$.WS_Form.prototype.form_rating_process = function(rating_obj, rating_index) {

		var rating_color_off = rating_obj.attr('data-rating-color-off');
		var rating_color_on = rating_obj.attr('data-rating-color-on');

		// Now highlight all the stars that's not after the current hovered star
		$('ul li', rating_obj.parent()).each(function(e) {

			if (e < rating_index) {

				$(this).addClass('wsf-rating-selected');
				$('svg.wsf-rating-icon path', $(this)).attr('fill', rating_color_on);

			} else {

				$(this).removeClass('wsf-rating-selected');
				$('svg.wsf-rating-icon path', $(this)).attr('fill', rating_color_off);
			}
		});
	}

})(jQuery);
