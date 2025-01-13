(function($) {

	'use strict';

	// Google Routing
	$.WS_Form.prototype.form_google_route = function() {

		var ws_this = this;

		// Get Google Address field objects
		var google_route_objects = $('[data-google-distance]:not([data-init-google-route])', this.form_canvas_obj);
		var google_route_objects_count = google_route_objects.length;
		if(!google_route_objects_count) { return false;}

		// Google API Init
		if(!this.form_google_maps_api_init()) { return false; };

		// Run through each autocomplete object
		google_route_objects.each(function() {

			$(this).attr('data-init-google-route', '');

			// Set to zero
			$(this).val(0);

			// Build google_route object
			var google_route = {};

			// Field
			var field = ws_this.get_field($(this));

			// $(this)
			google_route.obj = this;

			// Field ID
			google_route.field_id = ws_this.get_field_id($(this));

			// Origin
			google_route.field_id_origin = ws_this.get_object_meta_value(field, 'google_route_field_id_origin', false);

			// Waypoints
			google_route.waypoints = ws_this.get_object_meta_value(field, 'google_route_waypoints', false);
			google_route.waypoints_optimize = ws_this.get_object_meta_value(field, 'google_route_waypoints_optimize', false);

			// Destination
			google_route.field_id_destination = ws_this.get_object_meta_value(field, 'google_route_field_id_destination', false);

			// Field mapping
			google_route.field_mapping = ws_this.get_object_meta_value(field, 'google_route_field_mapping', []);

			// Travel mode
			google_route.travel_mode = ws_this.get_object_meta_value(field, 'google_route_travel_mode', 'DRIVING');

			// Unit system
			google_route.unit_system = ws_this.get_object_meta_value(field, 'google_route_unit_system', 'METRIC');

			// Avoid
			google_route.avoid_highways = ws_this.get_object_meta_value(field, 'google_route_avoid_highways', '');
			google_route.avoid_tolls = ws_this.get_object_meta_value(field, 'google_route_avoid_tolls', '');
			google_route.avoid_ferries = ws_this.get_object_meta_value(field, 'google_route_avoid_ferries', '');

			// Google map - Field ID
			google_route.map_field_id = ws_this.get_object_meta_value(field, 'google_route_map', '');

			// Process
			ws_this.google_route_process(google_route);
		});
	}

	// Google Routing - Process
	$.WS_Form.prototype.google_route_process = function(google_route, total_ms_start) {

		var ws_this = this;

		// Reposition flag
		google_route.reposition = true;

		// Timeout check
		if(typeof(total_ms_start) === 'undefined') { total_ms_start = new Date().getTime(); }
		if((new Date().getTime() - total_ms_start) > this.timeout_google_maps) {

			this.error('error_timeout_google_maps');
			return false;
		}

		// Check Google Maps elements have loaded (These checks exist in case Google Maps is loaded by a third party component)
		if(
			window.google &&
			window.google.map &&
			window.google.maps.DirectionsService
		) {
			wsf_google_maps_loaded = true;
		}

		// Check to see if Google Maps loaded
		if(!wsf_google_maps_loaded) {

			setTimeout(function() { ws_this.google_route_process(google_route, total_ms_start); }, this.timeout_interval);

			return false;
		}

		if(
			// Check original and destination
			!google_route.field_id_origin ||
			!google_route.field_id_destination ||
			!['BICYCLING', 'DRIVING', 'TRANSIT', 'WALKING'].includes(google_route.travel_mode) ||
			!['METRIC', 'IMPERIAL'].includes(google_route.unit_system)
		) {

			this.error('error_google_route');
			return false;
		}

		// Process with data-place-id attribute is set
		var event_function = function(event) {

			var google_route = event.data.google_route;
			var obj = $(google_route.obj);
			var obj_origin = event.data.obj_origin;
			var obj_destination = event.data.obj_destination;
			var waypoints = event.data.waypoints;

			// Check for place IDs
			var place_id_origin = (typeof(obj_origin.attr('data-place-id')) !== 'undefined') ? obj_origin.attr('data-place-id') : false;
			var place_id_destination = (typeof(obj_destination.attr('data-place-id')) !== 'undefined') ? obj_destination.attr('data-place-id') : false;

			// Check for values
			var val_origin = obj_origin.val();
			var val_destination = obj_destination.val();

			// Build origin and destination
			if(place_id_origin) {

				// Use place ID
				var origin = { placeId: place_id_origin };

			} else if(val_origin != '') {

				var origin = ws_this.google_route_get_origin_destination(val_origin);

			} else {

				return false;
			}

			if(place_id_destination) {

				// Use place ID
				var destination = { placeId: place_id_destination };

			} else if(val_destination != '') {

				var destination = ws_this.google_route_get_origin_destination(val_destination);

			} else {

				return false;
			}

			// Process waypoints
			var arg_waypoints = [];

			for(var waypoint_index in waypoints) {

				if(!waypoints.hasOwnProperty(waypoint_index)) { continue; }

				// Get waypoint
				var waypoint = waypoints[waypoint_index];

				// Get selector
				var waypoint_selector = waypoint.selector

				// Get object
				var waypoint_objs = $(waypoint_selector);

				// Process each waypoint object
				waypoint_objs.each(function() {

					var waypoint_place_id = (typeof($(this).attr('data-place-id')) !== 'undefined') ? $(this).attr('data-place-id') : false;

					// Check for values
					var waypoint_val = $(this).val();

					// Build waypoint
					if(waypoint_place_id) {

						// Use place ID
						var arg_waypoint = {

							location: { placeId: waypoint_place_id }
						};

					} else if(waypoint_val != '') {

						var arg_waypoint = {

							location: ws_this.google_route_get_origin_destination(waypoint_val)
						};

					} else {

						return;
					}

					// Type
					switch(waypoint.type) {

						case 'stopover_true' :

							arg_waypoint.stopover = true;
							break;

						case 'stopover_false' :

							arg_waypoint.stopover = false;
							break;
					}

					// Add to waypoints
					arg_waypoints.push(arg_waypoint);
				});
			}

			try {

				// Initialize directions service
				var directions_service = new google.maps.DirectionsService();

				// Build args
				var directions_service_args = {

					origin: origin,
					destination: destination,
					travelMode: google_route.travel_mode,
					unitSystem: google.maps.UnitSystem[google_route.unit_system],
					avoidFerries: (google_route.avoid_ferries == 'on'),
					avoidHighways: (google_route.avoid_highways == 'on'),
					avoidTolls: (google_route.avoid_tolls == 'on')
				};

				// Waypoints
				if(arg_waypoints.length) {

					directions_service_args.waypoints = arg_waypoints;

					// Optimize waypoints
					if(google_route.waypoints_optimize) {

						directions_service_args.optimizeWaypoints = true;
					}
				}

				// Make distance matrix service request
				directions_service.route(directions_service_args, function(result, status) {

					switch(status) {

						case 'OK' :

							// Get route
							var route = result.routes[0];

							// Get legs
							var legs = result.routes[0].legs;
							var legs_count = legs.length;

							// Process legs
							var distance_metric_total = 0;
							var duration_total = 0;
							var start_address = '';
							var start_lat = '';
							var start_lng = '';
							var end_address = '';
							var end_lat = '';
							var end_lng = '';

							for(var leg_index in legs) {

								if(!legs.hasOwnProperty(leg_index)) { continue; }

								// Get leg
								var leg = legs[leg_index];

								// Add to totals
								distance_metric_total += leg.distance.value;
								duration_total += leg.duration.value;

								// Start
								if(leg_index == 0) {

									start_address = leg.start_address;
									start_lat = leg.start_location.lat();
									start_lng = leg.start_location.lng();
								}

								// End
								if(leg_index == (legs_count - 1)) {

									end_address = leg.end_address;
									end_lat = leg.end_location.lat();
									end_lng = leg.end_location.lng();
								}
							}

							// Get distance as text
							var distance_text = ws_this.get_nice_distance(distance_metric_total, google_route.unit_system);

							// Get duration as text
							var duration_text = ws_this.get_nice_duration(duration_total, false);

							// Field mapping
							if(
								(typeof(google_route.field_mapping) === 'object') &&
								google_route.field_mapping.length
							) {

								for(var field_mapping_index in google_route.field_mapping) {

									if(!google_route.field_mapping.hasOwnProperty(field_mapping_index)) { continue; }

									// Get field mapping
									var field_mapping = google_route.field_mapping[field_mapping_index];

									// Get component
									var google_route_element = (typeof(field_mapping.google_route_element) !== 'undefined') ? field_mapping.google_route_element : '';
									if(google_route_element == '') { continue; }

									// Process by element
									var value = '';
									switch(google_route_element) {

										case 'distance_text' :

											value = distance_text;
											break;

										case 'distance_value_metric' :

											value = distance_metric_total;
											break;

										case 'distance_value_metric_km' :

											value = distance_metric_total / 1000;
											break;

										case 'distance_value_imperial' :

											value = distance_metric_total / 1609;
											break;

										case 'distance_value_imperial_yard' :

											value = (distance_metric_total / 1609) * 1760;
											break;

										case 'duration_text' :

											value = duration_text;
											break;

										case 'duration_value' :

											value = duration_total;
											break;

										case 'duration_value_minute' :

											value = duration_total / 60;
											break;

										case 'duration_value_hour' :

											value = duration_total / 3600;
											break;

										case 'duration_value_day' :

											value = duration_total / 86400;
											break;

										case 'duration_value_week' :

											value = duration_total / 604800;
											break;

										case 'duration_value_year' :

											value = duration_total / 31536000;
											break;

										case 'start_address' :

											value = start_address;
											break;

										case 'start_lat' :

											value = start_lat;
											break;

										case 'start_lng' :

											value = start_lng;
											break;

										case 'start_lat_lng' :

											value = start_lat + ',' + start_lng;
											break;

										case 'end_address' :

											value = end_address;
											break;

										case 'end_lat' :

											value = end_lat;
											break;

										case 'end_lng' :

											value = end_lng;
											break;

										case 'end_lat_lng' :

											value = end_lat + ',' + end_lng;
											break;

										case 'summary' :

											value = route.summary;
											break;
									}

									// Get field ID
									var field_id = parseInt((typeof(field_mapping.ws_form_field) !== 'undefined') ? field_mapping.ws_form_field : '', 10);
									if(field_id == 0) { continue; }

									// Get section repeatable index
									var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(google_route.obj));

									// Field wrapper object
									var field_wrapper_obj = $('#' + ws_this.form_id_prefix + 'field-wrapper-' + field_id + section_repeatable_suffix, ws_this.form_canvas_obj);

									// Field object
									var field_obj = $('#' + ws_this.form_id_prefix + 'field-' + field_id + section_repeatable_suffix, ws_this.form_canvas_obj);

									// Set field value
									ws_this.field_value_set(field_wrapper_obj, field_obj, value);
								}
							}

							// Set Google Map
							if(
								(google_route.map_field_id != '') &&
								(typeof(ws_this.google_maps[google_route.map_field_id]) !== 'undefined')
							) {

								// Get Google Map
								var google_map = ws_this.google_maps[google_route.map_field_id];

								// Init directions renderer
								ws_this.google_map_directions_renderer_init(google_map);

								// Set directions on map
								google_map.directions_renderer.setDirections(result);
							}

							break;

						default :

							// Throw error
							ws_this.error('error_google_route_message', JSON.stringify(result));
					}

					// Set attribute and trigger conditional logic
					obj.attr('data-google-route-status', status).trigger('wsf-google-route');
				});

			} catch(error) {

				ws_this.error('error_google_route_message', error);
			}
		};

		// Clear on change
		var event_clear_function = function(event) {

			var google_route = event.data.google_route;
			var obj = $(google_route.obj);
			var obj_origin = event.data.obj_origin;
			var obj_destination = event.data.obj_destination;

			// Clear place ID
			obj.removeAttr('data-place-id');

			// Field mapping
			if(
				(typeof(google_route.field_mapping) === 'object') &&
				google_route.field_mapping.length
			) {

				for(var field_mapping_index in google_route.field_mapping) {

					if(!google_route.field_mapping.hasOwnProperty(field_mapping_index)) { continue; }

					// Get field mapping
					var field_mapping = google_route.field_mapping[field_mapping_index];

					// Get component
					var google_route_element = (typeof(field_mapping.google_route_element) !== 'undefined') ? field_mapping.google_route_element : '';
					if(google_route_element == '') { continue; }

					// Process by element
					var value = '';
					switch(google_route_element) {

						case 'distance_text' :
						case 'duration_text' :
						case 'start_address' :
						case 'start_lat' :
						case 'start_lng' :
						case 'start_lat_lng' :
						case 'end_address' :
						case 'end_lat' :
						case 'end_lng' :
						case 'end_lat_lng' :
						case 'summary' :

							value = '';
							break;

						case 'distance_value_metric' :
						case 'distance_value_metric_km' :
						case 'distance_value_imperial' :
						case 'distance_value_imperial_yard' :
						case 'duration_value' :
						case 'duration_value_minute' :
						case 'duration_value_hour' :
						case 'duration_value_day' :
						case 'duration_value_week' :
						case 'duration_value_year' :

							value = 0;
							break;
					}

					// Get field ID
					var field_id = parseInt((typeof(field_mapping.ws_form_field) !== 'undefined') ? field_mapping.ws_form_field : '', 10);
					if(field_id == 0) { continue; }

					// Get section repeatable index
					var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(google_route.obj));

					// Field wrapper object
					var obj_wrapper = $('#' + ws_this.form_id_prefix + 'field-wrapper-' + field_id + section_repeatable_suffix, ws_this.form_canvas_obj);

					// Field object
					var obj = $('#' + ws_this.form_id_prefix + 'field-' + field_id + section_repeatable_suffix, ws_this.form_canvas_obj);

					// Set field value
					ws_this.field_value_set(obj_wrapper, obj, value);
				}
			}

			// Reset Google Map
			if(
				(google_route.map_field_id != '') &&
				(typeof(ws_this.google_maps[google_route.map_field_id]) !== 'undefined')
			) {

				// Get Google Map
				var google_map = ws_this.google_maps[google_route.map_field_id];

				// Init directions renderer
				ws_this.google_map_directions_renderer_init(google_map);

				// Reset directions
				google_map.directions_renderer.set('directions', null);
			}
		};

		// Get section repeatable suffix
		var section_repeatable_suffix = ws_this.get_section_repeatable_suffix($(google_route.obj));

		// Field objects
		var obj_origin = $('#' + ws_this.form_id_prefix + 'field-' + google_route.field_id_origin + section_repeatable_suffix, ws_this.form_canvas_obj);
		var obj_destination = $('#' + ws_this.form_id_prefix + 'field-' + google_route.field_id_destination + section_repeatable_suffix, ws_this.form_canvas_obj);

		if(
			!obj_origin.length ||
			!obj_destination.length
		) {

			this.error('error_google_route');
			return false;
		}

		// Get field types
		var field_type_origin = this.get_field_type(obj_origin);
		var field_type_destination = this.get_field_type(obj_destination);

		// Set up events on origin and destination fields
		var data = { google_route: google_route, obj_origin: obj_origin, obj_destination: obj_destination };

		// Origin events
		if(field_type_origin === 'googleaddress') {

			obj_origin.on('wsf-place-id-reset', data, event_clear_function);
			obj_origin.on('wsf-place-id-set', data, event_function);

		} else {

			obj_origin.on('change', data, event_clear_function);
			obj_origin.on('change', data, event_function);
		}

		// Destination events
		if(field_type_destination === 'googleaddress') {

			obj_destination.on('wsf-place-id-reset', data, event_clear_function);
			obj_destination.on('wsf-place-id-set', data, event_function);

		} else {

			obj_destination.on('change', data, event_clear_function);
			obj_destination.on('change', data, event_function);
		}

		// Process waypoints
		var waypoints = [];

		for(var waypoint_index in google_route.waypoints) {

			if(!google_route.waypoints.hasOwnProperty(waypoint_index)) { continue; }

			// Get waypoint
			var waypoint = google_route.waypoints[waypoint_index];

			// Get waypoint field ID
			var waypoint_field_id = waypoint.google_route_waypoint_field_id;

			// Get waypoint type
			var waypoint_type = waypoint.google_route_waypoint_type;

			// Get field config
			if(typeof(this.field_data_cache[waypoint_field_id]) === 'undefined') { continue; }
			var field_config = this.field_data_cache[waypoint_field_id];

			// Build field selector
			if(field_config.section_repeatable) {

				var field_selector = '[id^="' + this.esc_selector(this.form_id_prefix + 'field-' + waypoint_field_id) + '-repeat-"]';

			} else {

				var field_selector = '#' + this.esc_selector(this.form_id_prefix + 'field-' + waypoint_field_id);
			}

			// Add events
			if(field_config.type === 'googleaddress') {

				this.form_canvas_obj.on('wsf-place-id-reset', field_selector, data, event_function);
				this.form_canvas_obj.on('wsf-place-id-set', field_selector, data, event_function);

			} else {

				this.form_canvas_obj.on('change', field_selector, data, event_clear_function);
				this.form_canvas_obj.on('change', field_selector, data, event_function);
			}

			var section_repeatable_section_id = (typeof(field_config.section_repeatable_section_id) !== 'undefined') ? field_config.section_repeatable_section_id : false;

			if(section_repeatable_section_id) {

				this.form_canvas_obj.on('wsf-section-repeatable-delete-' + section_repeatable_section_id + ' wsf-section-repeatable-move-' + section_repeatable_section_id, data, event_function);
			}

			// Add to waypoints array
			waypoints.push({

				field_id: waypoint_field_id,
				type: waypoint_type,
				selector: field_selector,
			});
		}

		// Add waypoints to data
		data.waypoints = waypoints;

		// Initial clear
		event_clear_function({data: data});

		// If both origin and destination are not Google Address fields, do initial route request
		if(
			(field_type_origin !== 'googleaddress') &&
			(field_type_destination !== 'googleaddress')
		) {
			event_function({data: data});
		}
	}

	// Google Routing - Process
	$.WS_Form.prototype.google_map_directions_renderer_init = function(google_map) {

		if(google_map.directions_renderer === false) {

			// Build args
			var directions_renderer_args = {

				polylineOptions: {

					strokeColor: google_map.routing_polyline_color,
					strokeWeight: google_map.routing_polyline_weight
				}
			};

			// Custom icon URL
			if(google_map.routing_icon_url_origin) {

				directions_renderer_args.markerOptions = {

					icon: {

						url: google_map.routing_icon_url_origin
					}
				};
			}

			google_map.directions_renderer = new google.maps.DirectionsRenderer(directions_renderer_args);

			google_map.directions_renderer.setMap(google_map.map);
		}
	}

	// Google Routing - Process
	$.WS_Form.prototype.google_route_get_origin_destination = function(input) {

		// Check if input contains latitude,longitude string
		if(/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/.exec(input) !== null) {

			// Split by comma
			var lat_lng = input.split(',');

			var lat = parseFloat(lat_lng[0]);
			var lng = parseFloat(lat_lng[1]);

			return {lat: lat, lng: lng};

		} else {

			return input;
		}
	}

})(jQuery);
