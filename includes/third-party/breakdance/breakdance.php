<?php 

	if(defined('__BREAKDANCE_VERSION')) {

		add_action('init', function() {

			if(isset($_GET) && isset($_GET['breakdance_iframe'])) {	// phpcs:ignore WordPress.Security.NonceVerification

				// Visual builder enqueues
				do_action('wsf_enqueue_visual_builder');
			}
		});

		add_action('plugins_loaded', function () {

			try {

				// Register element
				include_once 'elements/ws-form-form/element.php';

			} catch (Exception $e) {}
		});
	}
