(function($) {

	'use strict';

	// Styler schjeme
	$.WS_Form.prototype.styler_scheme = function(process_media_prefers_color_scheme, process_third_party) {

		if(typeof(process_media_prefers_color_scheme) === 'undefined') { process_media_prefers_color_scheme = true; }
		if(typeof(process_third_party) === 'undefined') { process_third_party = true; }

		if(process_media_prefers_color_scheme) {

			// Set up a media query to detect the user's current color scheme
			var media_query = window.matchMedia('(prefers-color-scheme: dark)');

			// Initial check for the current color scheme
			this.styler_scheme_prefers_color_scheme(media_query);

			// Listen for changes to the user's preference
			media_query.addEventListener('change', this.styler_scheme_prefers_color_scheme);
		}

		if(process_third_party) {

			// Mutation processing roggle
			this.styler_scheme_mutation_process = true;

			// Initial check for third party class theme switchers - To do
			this.styler_scheme_third_party_class();

			// Listen for changes to third party theme switches
			var observer = new MutationObserver(this.styler_scheme_third_party_observer);

			observer.observe(document.documentElement, {

				attributes: true,           // Observe attribute changes
				attributeFilter: ['class']  // Only observe the class attribute
			});
		}
	}

	// Styler scheme - Reset
	$.WS_Form.prototype.styler_scheme_reset = function() {

		// Set up a media query to detect the user's current color scheme
		var media_query = window.matchMedia('(prefers-color-scheme: dark)');

		// Initial check for the current color scheme
		this.styler_scheme_prefers_color_scheme(media_query);

		// Initial check for third party class theme switchers
		this.styler_scheme_third_party_class();
	}

	// Styler scheme - Match media
	$.WS_Form.prototype.styler_scheme_prefers_color_scheme = function(e) {

		this.styler_scheme_mutation_process = false;

		if(e.matches) {

			if(ws_form_settings.scheme == 'light') {

				$.WS_Form.this.styler_scheme_alt_enable();

			} else {

				$.WS_Form.this.styler_scheme_alt_disable();
			}

		} else {

			if(ws_form_settings.scheme == 'light') {

				$.WS_Form.this.styler_scheme_alt_disable();

			} else {

				$.WS_Form.this.styler_scheme_alt_enable();
			}
		}
	}

	// Styler scheme - Enable
	$.WS_Form.prototype.styler_scheme_alt_enable = function() {

		$('html').addClass('wsf-styler-scheme-alt');
	}

	// Styler scheme - Disable
	$.WS_Form.prototype.styler_scheme_alt_disable = function() {

		$('html').removeClass('wsf-styler-scheme-alt');
	}

	// Styler scheme - Third party - Class
	$.WS_Form.prototype.styler_scheme_third_party_class = function(mutation) {

		if(!this.styler_scheme_mutation_process) {

			this.styler_scheme_mutation_process = true;
			return;
		}

		// Define tests
		var dark_tests = [

			// ACSS
			{'present': '[id=automaticcss-core-css]', 'dark': 'html.color-scheme--alt'},

			// Beaver Builder
			{'present': '.fl-builder', 'dark': '.fl-dark-mode'},

			// Breakdance
			{'present': '.breakdance', 'dark': '.breakdance-dark-mode'},

			// Bricks
			{'present': '.bricks-builder', 'dark': '.dark-mode'},

			// Darkify
			{'present': 'html[data-theme]', 'dark': 'html[dark-theme=dark]'},

			// Divi
			{'present': '.et_pb_page', 'dark': '.et-dark-mode'},

			// Elementor
			{'present': '.elementor', 'dark': '.dark-mode'},

			// Oxygen
			{'present': '.oxygen', 'dark': '.dark-mode'}
		];

		// Run tests
		for(var dark_test_index in dark_tests) {

			if(!dark_tests.hasOwnProperty(dark_test_index)) { continue; }

			var dark_test = dark_tests[dark_test_index];

			if($(dark_test.present).length) {

				if($(dark_test.dark).length) {

					this.styler_scheme_alt_enable();

				} else {

					this.styler_scheme_alt_disable();
				}

				break;
			}
		}
	}

	// Styler scheme - Third party observer
	$.WS_Form.prototype.styler_scheme_third_party_observer = function(mutations_list) {

		mutations_list.forEach(function (mutation) {

			if(mutation.attributeName === 'class') {

				$.WS_Form.this.styler_scheme_third_party_class(mutation);
			}
		});
	}

})(jQuery);
