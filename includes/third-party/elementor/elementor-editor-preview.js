(function($) {

	'use strict';

	$(window).on('elementor/frontend/init', function() {

		// For editor when a form is added
		elementorFrontend.hooks.addAction('frontend/element_ready/ws-form.default', function($scope, $) {

			if(typeof(wsf_form_init) === 'function') {

				// Initialize form
				wsf_form_init(true);

				// Remove class from elementor widget
				$scope.removeClass('elementor-widget-empty');
			}
		});
	});

	// Form selector
	$(document).on('change', '.wsf-elementor-form-selector select', function() {

		window.parent.jQuery('#elementor-controls select[data-setting="form_id"]').val($(this).val()).trigger('change');
	})

})(jQuery);
