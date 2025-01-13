(function($) {

	'use strict';

	$(function() {

		$('.wsf-admin-bar-debug-console').on('click', function(e) {	// , .wsf-admin-bar-styler

			// Prevent default
			e.preventDefault();

			// Get href
			var href = $('a', $(this)).attr('href');

			// Remove #
			var hash = href.substring(1);

			// Check debug console state
			if(['debug-off', 'debug-administrator', 'debug-on'].indexOf(hash) !== -1) {

				var helper_debug = hash.substring(6);

				// Make AJAX request
				$.ajax({

					url: ws_form_toolbar.api_url_debug + helper_debug + '/',
					type: 'POST',
					beforeSend: function(xhr) {

						xhr.setRequestHeader('X-WP-Nonce', ws_form_toolbar.x_wp_nonce);
					},
					complete: function(data){

						location.reload();
					}
				});
			}

			// Check styler state
/*			if(['styler-off', 'styler-administrator', 'styler-on'].indexOf(hash) !== -1) {

				var styler_debug = hash.substring(7);

				// Make AJAX request
				$.ajax({

					url: ws_form_toolbar.api_url_styler + styler_debug + '/',
					type: 'POST',
					beforeSend: function(xhr) {

						xhr.setRequestHeader('X-WP-Nonce', ws_form_toolbar.x_wp_nonce);
					},
					complete: function(data){

						location.reload();
					}
				});
			}
*/
		});
	});

})(jQuery);