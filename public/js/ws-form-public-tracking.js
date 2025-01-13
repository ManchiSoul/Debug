(function($) {

	'use strict';

	// Form tracking
	$.WS_Form.prototype.form_tracking = function() {

		for(var tracking_id in $.WS_Form.tracking) {

			if(!$.WS_Form.tracking.hasOwnProperty(tracking_id)) { continue; }

			var tracking_config = $.WS_Form.tracking[tracking_id];	

			// Check this tracking method is enabled
			if(!this.get_object_meta_value(this.form, tracking_id, false)) { continue; }

			// Get client source
			if(typeof(tracking_config['client_source']) === 'undefined') { continue; }
			var client_source = tracking_config['client_source'];

			// Get server query var
			if(typeof(tracking_config['server_query_var']) === 'undefined') { continue; }
			var server_query_var = tracking_config['server_query_var'];

			var tracking_value = '';

			switch(client_source) {

				case 'query_var' :

					// Get client query var
					if(typeof(tracking_config['client_query_var']) === 'undefined') { break; }
					var client_query_var = tracking_config['client_query_var'];

					// Read query var value
					tracking_value = this.get_query_var(client_query_var);

					break;

				case 'referrer' :

					// Get document referrer
					tracking_value = (typeof(document.referrer) !== 'undefined') ? document.referrer : '';
					break;

				case 'href' :

					// Get location HREF
					tracking_value = (typeof(location.href) !== 'undefined') ? location.href : '';
					break;

				case 'pathname' :

					// Get location pathname
					tracking_value = (typeof(location.pathname) !== 'undefined') ? location.pathname : '';
					break;

				case 'query_string' :

					// Get location query string (search)
					tracking_value = (typeof(location.search) !== 'undefined') ? location.search : '';
					break;

				case 'hash' :

					// Get location hash
					tracking_value = (typeof(location.hash) !== 'undefined') ? location.hash : '';
					break;

				case 'os' :

					// Get window.navigator operating system
					if(typeof(window.navigator) === 'undefined') { break; }
					tracking_value = (typeof(window.navigator.platform) !== 'undefined') ? window.navigator.platform : '';
					break;

				case 'geo_location' :

					// Does browser support geolocation?
					if(!navigator.geolocation) { break; }

					// Get geo location
					var ws_this = this;

					navigator.geolocation.getCurrentPosition(function(position) {

						var tracking_geo_location = position.coords.latitude + ',' + position.coords.longitude;

						// Set hidden value
						ws_this.form_geo_location_process(tracking_geo_location);

						// Debug
						ws_this.log('log_tracking_geo_location', tracking_geo_location, 'tracking');

					}, function showError(error) {

						// Set hidden value
						ws_this.form_geo_location_process(error.code);

						// Debug
						ws_this.error('error_tracking_geo_location', ($.WS_Form.debug_rendered ? ws_this.form_geo_location_get_error(error) : ''), 'tracking');
					});

					continue;
			}

			// Add to form
			this.form_add_hidden_input(server_query_var, tracking_value);
		}
	}

	// Form geo location - Process
	$.WS_Form.prototype.form_geo_location_process = function(tracking_geo_location) {

		// Add to form
		this.form_add_hidden_input('wsf_geo_location', tracking_geo_location);
	}

	// Form geo location - Process
	$.WS_Form.prototype.form_geo_location_get_error = function(error) {

		switch(error.code) {

			case error.PERMISSION_DENIED:

				return this.language('debug_tracking_geo_location_permission_denied');

			case error.POSITION_UNAVAILABLE:

				return this.language('debug_tracking_geo_location_position_unavailable');

			case error.TIMEOUT:

				return this.language('debug_tracking_geo_location_timeout');

			default:

				return this.language('debug_tracking_geo_location_default');
		}
	}

})(jQuery);
