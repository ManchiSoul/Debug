(function($) {

	'use strict';

	// Legal
	$.WS_Form.prototype.form_legal = function() {

		var ws_this = this;

		$('[data-wsf-legal]', this.form_canvas_obj).each(function() {

			// Source
			var source = $(this).attr('data-wsf-legal-source');

			if(source) {

				switch(source) {

					// Termageddon
					case 'termageddon' :

						// Clear
						$(this).html('');

						// Read key
						var key = $(this).attr('data-wsf-termageddon-key');
						if(typeof(key) !== 'undefined') {

							// Read extra
							var extra = $(this).attr('data-wsf-termageddon-extra') ? '?' + $(this).attr('data-wsf-termageddon-extra') : '';

							// Set up object to populate
							var obj = $(this);

							// This code adapted from Termageddon's embed code
							var xhr = new XMLHttpRequest();
							xhr.onloadend = function() {

								switch(xhr.status) {

									case 200 :

										// Populate
										obj.html(xhr.responseText);

										// Initialize scrolling
										ws_this.form_legal_scrolling_init(obj);

										break;

									case 404 :

										ws_this.error('error_termageddon_404');
										break;

									default :

										ws_this.error('error_termageddon');
								}
							}
							xhr.open('GET', 'https://app.termageddon.com/api/policy/' + key + extra);
							xhr.send();
						}

						break;
				}

			} else {

				// Initialize scrolling
				ws_this.form_legal_scrolling_init($(this));
			}
		});
	}

	$.WS_Form.prototype.form_legal_scrolling_init = function(obj) {

		var ws_this = this;

		// Get field ID
		var field_id = this.get_field_id(obj);

		// Check if required
		var field_obj = this.field_data_cache[field_id];

		// Get required
		var required = this.get_object_meta_value(field_obj, 'required', '');

		if(required) {

			// Get legal input
			var legal_input_obj = $('[data-wsf-legal-input]', obj.parent());

			// Set up legal input
			legal_input_obj.val('1').addClass(obj.attr('class')).attr('required', '').attr('data-required', '').removeAttr('data-wsf-read');

			// Scrolling
			this.form_legal_scrolling(obj);

			// On scroll
			obj.on('scroll', function() {

				ws_this.form_legal_scrolling($(this));
			});

			// On resize (Also used for triggering a reset if the form is made visible by a third party)
			var legal_resize_observer = new ResizeObserver((entries) => {

				// Get legal input
				var legal_input_obj = $('[data-wsf-legal-input]', obj.parent());

				// Reset legal input
				legal_input_obj.removeAttr('data-wsf-read');

				// Process scrolling
				ws_this.form_legal_scrolling(obj);
			})
			legal_resize_observer.observe(obj[0]);
		}
	}

	$.WS_Form.prototype.form_legal_scrolling = function(obj) {

		// Get legal input
		var legal_input_obj = $('[data-wsf-legal-input]', obj.parent());

		// Check if it has already been read
		if(typeof(legal_input_obj.attr('data-wsf-read')) !== 'undefined') { return; }

		// Check if tab is hidden
		var group_obj = obj.closest('.wsf-group');

		// Check if we should momentarily show the group to calculate scrollHeight (If hidden scrollHeight is 0)
		var group_visibility_toggle = !group_obj.is(":visible");

		if(group_visibility_toggle) { group_obj.show(); }

		// Determine if div is scrolled to the bottom
		if(
			obj.is(':visible') &&
			(obj.scrollTop() + obj.outerHeight() >= obj[0].scrollHeight)
		) {

			legal_input_obj.val('1').attr('data-wsf-read', '').trigger('change');

		} else {

			legal_input_obj.val('').trigger('change');
		}

		if(group_visibility_toggle) { group_obj.hide(); }
	}

})(jQuery);
