(function($) {

	'use strict';

	// Form - Radio validation
	$.WS_Form.prototype.form_radio_validation = function() {

		var ws_this = this;

		$('[data-type="radio"]:not([data-radio-validation-init]),[data-type="price_radio"]:not([data-radio-validation-init])', this.form_canvas_obj).each(function() {

			// Get radios
			var radios = $('input[type="radio"]', $(this));
			if(!radios.length) { return; }

			// On change event
			radios.on('change', function() {

				ws_this.form_radio_validation_process($(this));
			});

			// Initial process
			ws_this.form_radio_validation_process(radios.first());

			// Mark as processed
			$(this).attr('data-radio-validation-init', '');
		});
	}

	$.WS_Form.prototype.form_radio_validation_process = function(obj) {

		// Get radio group
		var radio_group = obj.closest('[role="radiogroup"]');

		// Check if it valid
		if(this.is_valid(obj)) {

			// Remove attribute
			radio_group.removeAttr('data-wsf-invalid');

		} else {

			// Add attribute
			radio_group.attr('data-wsf-invalid', '');
		}
	}

})(jQuery);
