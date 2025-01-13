<?php

	add_action('init', function() {

		// Litespeed NONCE registration
		do_action('litespeed_nonce', WS_FORM_POST_NONCE_ACTION_NAME);
	});

	add_action('wsf_api_no_cache', function() {

		// Litespeed control set nocache
		do_action('litespeed_control_set_nocache', sprintf(

			/* translators: %s = WS Form */
			__('Caching disabled for %s API response', 'ws-form'),

			WS_FORM_NAME_GENERIC
		));
	});