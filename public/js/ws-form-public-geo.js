(function($) {

	'use strict';

	// Geo
	$.WS_Form.prototype.form_geo = function() {

		// Check geo is enabled
		var geo = this.get_object_meta_value(this.form, 'geo');
		if(!geo) { return; }

		// Get geo mapping
		var geo_mapping = this.get_object_meta_value(this.form, 'geo_mapping');
		if(typeof(geo_mapping) !== 'object') { return; }

		for(var geo_mapping_index in geo_mapping) {

			if(!geo_mapping.hasOwnProperty(geo_mapping_index)) { continue; }

			// Get map
			var geo_map = geo_mapping[geo_mapping_index];

			// Check map
			if(!geo_map.geo_element || !geo_map.ws_form_field) { continue; }

			// Read element and field ID
			var element = geo_map.geo_element;
			var field_id = geo_map.ws_form_field;

			// Add to geo stack
			this.form_geo_get_element(element, '', 'form_geo_map_process', {field_id: field_id});
		}
	}

	// Geo - Process map
	$.WS_Form.prototype.form_geo_map_process = function(callback_value, callback_data) {

		var ws_this = this;

		// Get field ID
		var field_id = callback_data.field_id;

		var destination_wrappers = $('[id^="' + this.esc_selector(this.form_id_prefix) + 'field-wrapper-"][data-id="' + this.esc_selector(field_id) + '"]:not([data-wsf-geo-set]),input[type="hidden"][data-id-hidden="' + this.esc_selector(field_id) + '"]:not([data-wsf-geo-set])', this.form_canvas_obj);
		if(!destination_wrappers.length) { return; }

		destination_wrappers.each(function() {

			// Get destination repeatable index (This is used to localize the conditional_process_action)
			var destination_repeatable_index = ((typeof($(this).attr('data-repeatable-index')) !== 'undefined') ? $(this).attr('data-repeatable-index') : false);

			// Get destination repeatable suffix
			var destination_repeatable_suffix = (destination_repeatable_index !== false) ? '-repeat-' + destination_repeatable_index : '';

			// Field wrapper object
			var obj_wrapper = $('#' + ws_this.form_id_prefix + 'field-wrapper-' + field_id + destination_repeatable_suffix, ws_this.form_canvas_obj);

			// Set object as processed so that the value is only set once (e.g. if repeatable section added)
			obj_wrapper.attr('data-wsf-geo-set', '');

			// Field object
			var obj = $('#' + ws_this.form_id_prefix + 'field-' + field_id + destination_repeatable_suffix, ws_this.form_canvas_obj);

			// Set field value
			ws_this.field_value_set(obj_wrapper, obj, callback_value);
		});
	}

	// Geo - Get element
	$.WS_Form.prototype.form_geo_get_element = function(element, default_value, callback, callback_data) {

		if(typeof(element) === 'undefined') { return false; }
		if(typeof(default_value) === 'undefined') { default_value = ''; }
		if(typeof(callback) === 'undefined') { return false; }
		if(typeof(callback_data) === 'undefined') { callback_data = false; }

		var ws_this = this;

		// Add request to stack
		this.form_geo_stack.push({

			element: element,
			default_value: default_value,
			callback: callback,
			callback_data: callback_data
		})

		// Check if form_geo_cache exists
		if(this.form_geo_cache !== false) {

			this.form_geo_stack_empty();

		} else {

			// If request is already in progress, then return and wait for the process to finish
			if(this.form_geo_cache_request) { return; }

			this.form_geo_cache_request = true;

			// Request geo data
			var ip_lookup_method = ws_form_settings.ip_lookup_method;

			switch(ip_lookup_method) {

				// ipinfo.io
				case 'ipinfo' :

					var url = 'https://ipinfo.io/json';

					break;

				// ipapi.co (geoplugin and ip-api do not have https endpoints)
				default :

					if(ip_lookup_method != 'ipapico') {

						this.log('log_geo_endpoint_fallback');

						ip_lookup_method = 'ipapico';
					}

					var url = 'https://ipapi.co/json';

					break;
			}

			// Show loader
		 	if(typeof(this.form_loader_show) === 'function') { this.form_loader_show('geolocate'); }

			// Make GET request
			$.get(url, function(resp) {

				if(typeof(resp) !== 'object') { return false; }

				switch(ip_lookup_method) {

					// ipinfo.io
					case 'ipinfo' :

						// Get latitude / longitude
						var lat_lng = ws_this.form_geo_get_resp_value(resp, 'loc');
						var lat_lng_array = lat_lng.split(',');
						var lat = lat_lng_array[0];
						var lng = lat_lng_array[1];

						// Set form geo cache
						ws_this.form_geo_cache = {

							ip: ws_this.form_geo_get_resp_value(resp, 'ip'),
							city: ws_this.form_geo_get_resp_value(resp, 'city'),
							region_short: ws_this.form_geo_get_resp_value(resp, 'region'),
							region_long: ws_this.form_geo_get_resp_value(resp, 'region'),
							postal_code: ws_this.form_geo_get_resp_value(resp, 'postal'),
							country_long: '',
							country_short: ws_this.form_geo_get_resp_value(resp, 'country'),
							lat: lat,
							lng: lng,
							lat_lng: lat_lng,
							org: ws_this.form_geo_get_resp_value(resp, 'org'),
							asn: '',
							currency_code: '',
							currency: '',
							timezone: ws_this.form_geo_get_resp_value(resp, 'timezone')
						};

						break;

					// ipapi.co
					default :

						var lat = ws_this.form_geo_get_resp_value(resp, 'latitude');
						var lng = ws_this.form_geo_get_resp_value(resp, 'longitude');

						// Set form geo cache
						ws_this.form_geo_cache = {

							ip: ws_this.form_geo_get_resp_value(resp, 'ip'),
							city: ws_this.form_geo_get_resp_value(resp, 'city'),
							region_short: ws_this.form_geo_get_resp_value(resp, 'region_code'),
							region_long: ws_this.form_geo_get_resp_value(resp, 'region'),
							postal_code: ws_this.form_geo_get_resp_value(resp, 'postal'),
							country_short: ws_this.form_geo_get_resp_value(resp, 'country_code'),
							country_long: ws_this.form_geo_get_resp_value(resp, 'country_name'),
							lat: lat,
							lng: lng,
							lat_lng: lat + ',' + lng,
							org: ws_this.form_geo_get_resp_value(resp, 'org'),
							asn: ws_this.form_geo_get_resp_value(resp, 'asn'),
							currency_code: ws_this.form_geo_get_resp_value(resp, 'currency'),
							currency_name: ws_this.form_geo_get_resp_value(resp, 'currency_name'),
							timezone: ws_this.form_geo_get_resp_value(resp, 'timezone')
						};

						break;
				}

				// Log
				ws_this.log('log_geo_success', ip_lookup_method);

				// Hide loader
			 	if(typeof(this.form_loader_hide) === 'function') { ws_this.form_loader_hide(); }

				// Empty callback stack
				ws_this.form_geo_stack_empty();
				
			}).fail(function(resp) {

				ws_this.error('error_geo', url);
			});
		}
	}

	// Geo - Get resp value
	$.WS_Form.prototype.form_geo_get_resp_value = function(resp, element, default_value) {

		if(typeof(default_value) === 'undefined') { default_value = ''; }

		if(
			(typeof(resp) !== 'object') ||
			(typeof(resp[element]) === 'undefined')
		) {
			return default_value;
		}

		return this.esc_html(resp[element]);
	}

	// Geo - Empty stack
	$.WS_Form.prototype.form_geo_stack_empty = function() {

		for(var form_geo_stack_index in this.form_geo_stack) {

			if(!this.form_geo_stack.hasOwnProperty(form_geo_stack_index)) { continue; }

			var form_geo_stack = this.form_geo_stack[form_geo_stack_index];

			this.form_geo_callback(form_geo_stack.element, form_geo_stack.default_value, form_geo_stack.callback, form_geo_stack.callback_data);
		}
	}

	// Geo - Process callback
	$.WS_Form.prototype.form_geo_callback = function(element, default_value, callback, callback_data) {

		if(typeof(element) === 'undefined') { return false; }
		if(typeof(default_value) === 'undefined') { default_value = ''; }
		if(typeof(callback) === 'undefined') { return false; }
		if(typeof(callback_data) === 'undefined') { callback_data = false; }

		// Get callback value
		var callback_value = (typeof(this.form_geo_cache[element]) !== 'undefined') ? this.form_geo_cache[element] : default_value;

		// Call callback
		if(typeof(callback) === 'function') {

			callback(callback_value);
		}

		if(typeof(callback) === 'string') {

			this[callback](callback_value, callback_data);
		}
 	}

})(jQuery);
