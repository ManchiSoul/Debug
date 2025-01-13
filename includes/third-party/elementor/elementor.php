<?php

	add_action('plugins_loaded', function() {

		if(
			isset($_GET) && isset($_GET['elementor-preview'])	// phpcs:ignore WordPress.Security.NonceVerification
		) {

			// Disable debug
			add_filter('wsf_debug_enabled', function($debug_render) { return false; }, 10, 1);

			// Core enqueues
			add_action('wp_enqueue_scripts', function() { do_action('wsf_enqueue_core'); });
		}
	});

	if(defined('ELEMENTOR_VERSION')) {

		if(WS_Form_Common::version_compare(ELEMENTOR_VERSION, '3.5') >= 0) {

			// >= Version 3.5
			add_action('elementor/widgets/register', function($widgets_manager) {

				// Include WS Form widget class
				include 'class-elementor-ws-form-widget.php';

				// Unregister normal WordPress widget
				$widgets_manager->unregister('wp-widget-ws_form_widget');

				// Initiate WS Form widget
				$widgets_manager->register(new \Elementor_WS_Form_Widget());
			});

		} else {

			// < Version 3.5
			add_action('elementor/widgets/widgets_registered', function($widgets_manager) {

				// Include WS Form widget class
				include 'class-elementor-ws-form-widget.php';

				// Unregister normal WordPress widget
				$widgets_manager->unregister_widget_type('wp-widget-ws_form_widget');

				// Initiate WS Form widget
				$widgets_manager->register_widget_type(new \Elementor_WS_Form_Widget());
			});
		}
	}
