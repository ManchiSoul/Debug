<?php

	abstract class WS_Form_File_Handler extends WS_Form_Core {

		// Variables global to this abstract class
		public static $file_handlers = array();
		private static $return_array = array();

		// Register data source
		public function register($object) {

			// Check if pro required for data source
			if(!WS_Form_Common::is_edition($this->pro_required ? 'pro' : 'basic')) { return false; }

			// Get data source ID
			$file_handler_id = $this->id;

			// Add action to actions array
			self::$file_handlers[$file_handler_id] = $object;

			// Add meta keys to file field type
			if(method_exists($object, 'get_file_handler_settings')) {

				$settings = $object->get_file_handler_settings();

				if(isset($settings['meta_keys'])) {

					add_filter('wsf_config_field_types', function($field_types) {

						$object = self::$file_handlers[$this->id];
						$settings = $object->get_file_handler_settings();
						$meta_keys = $settings['meta_keys'];

						// Locate file handler index in file field
						$file_handler_index = false;
						foreach($field_types['advanced']['types']['file']['fieldsets']['basic']['fieldsets'] as $index => $fieldset) {

							if(in_array('file_handler', $fieldset['meta_keys'])) {

								$file_handler_index = $index;
								break;
							}
						}
						if($file_handler_index !== false) {

							foreach($meta_keys as $meta_key) {

								$field_types['advanced']['types']['file']['fieldsets']['basic']['fieldsets'][$file_handler_index]['meta_keys'][] = $meta_key;
							}
						}

						// Locate file handler index in signature field
						$file_handler_index = false;
						foreach($field_types['advanced']['types']['signature']['fieldsets']['basic']['fieldsets'] as $index => $fieldset) {

							if(in_array('file_handler', $fieldset['meta_keys'])) {

								$file_handler_index = $index;
								break;
							}
						}
						if($file_handler_index !== false) {

							foreach($meta_keys as $meta_key) {

								$field_types['advanced']['types']['signature']['fieldsets']['basic']['fieldsets'][$file_handler_index]['meta_keys'][] = $meta_key;
							}
						}

						return $field_types;
					});
				}
			}
		}

		// Touch with file handler ID
		public function touch(&$file_objects) {

			foreach($file_objects as $file_object_key => $file_object) {

				$file_objects[$file_object_key]['handler'] = $this->id;
			}
		}

		// Check file restrictions
		public static function check_file_restrictions($field, $file_name, $file_type, $file_size, $file_path) {

			// Get max upload size
			$max_upload_size = absint(WS_Form_Common::option_get('max_upload_size'));

			// Get min file size
	 		$file_min_size = floatval(WS_Form_Common::get_object_meta_value($field, 'file_min_size', 'wsform'));
	 		if($file_min_size > 0) { $file_min_size = ($file_min_size * 1048576); }

	 		// Get max file size
	 		$file_max_size = floatval(WS_Form_Common::get_object_meta_value($field, 'file_max_size', 'wsform'));
	 		if($file_max_size > 0) { $file_max_size = ($file_max_size * 1048576); }

			// Get field ID
			$field_id = absint($field->id);

			// Check file size - System level
			if($file_size > $max_upload_size) {

				if(empty($file_name)) {

					// Throw error
					throw new Exception(sprintf(

						/* translators: %s = Maximum file size */
						__('The file is too large (Maximum size: %s).', 'ws-form'),
						WS_Form_Common::get_file_size($max_upload_size)
					));

				} else {

					// Throw error
					throw new Exception(sprintf(

						/* translators: %s = File name, %s = Maximum file size */
						__('The file %s is too large (Maximum size: %s).', 'ws-form'),
						esc_html($file_name),
						WS_Form_Common::get_file_size($max_upload_size)
					));
				}
			}

			// Check minimum file size - Field level
			if(
				($file_min_size > 0) &&
				($file_size < $file_min_size)
			) {

				if(empty($file_name)) {

					// Throw error
					throw new Exception(sprintf(

						/* translators: %s = Minimum file size */
						__('The file is too small (Minimum size: %s).', 'ws-form'),
					 	WS_Form_Common::get_file_size($file_min_size)
					));

				} else {

					// Throw error
					throw new Exception(sprintf(

						/* translators: %s = File name, %s = Minimum file size */
						__('The file %s is too small (Minimum size: %s).', 'ws-form'),
						esc_html($file_name),
					 	WS_Form_Common::get_file_size($file_min_size)
					));
				}
			}

			// Check maximum file size - Field level
			if(
				($file_max_size > 0) &&
				($file_size > $file_max_size)
			) {

				if(empty($file_name)) {

					// Throw error
					throw new Exception(sprintf(

						/* translators: %s = Minimum file size */
						__('The file is too large (Maximum size: %s).', 'ws-form'),
						WS_Form_Common::get_file_size($file_max_size)
					));

				} else {

					// Throw error
					throw new Exception(sprintf(

						/* translators: %s = File name, %s = Minimum file size */
						__('The file %s is too large (Maximum size: %s).', 'ws-form'),
						esc_html($file_name),
						WS_Form_Common::get_file_size($file_max_size)
					));
				}
			}

			// Field accept
			$field_accept = WS_Form_Common::get_object_meta_value($field, 'accept', false);
			$field_accept_array = WS_Form_Common::get_mime_array($field_accept);

			// Check file type
			if(count($field_accept_array) > 0) {

				$field_accepted = false;

				foreach($field_accept_array as $field_accept) {

					$field_accept = str_replace('*', '', $field_accept);

					if(strpos($file_type, $field_accept) !== false) {

						$field_accepted = true;
						break;
					}
				}

				if(!$field_accepted) {

					if(empty($file_name)) {

						// Throw error
						throw new Exception(sprintf(

							/* translators: %s = Accepted file types */
							__('The file is not an accepted type (%s).', 'ws-form'),
							implode(', ', $field_accept_array)
						));

					} else {

						// Throw error
						throw new Exception(sprintf(

							/* translators: %s = File name, %s = Accepted file types */
							__('The file %s is not an accepted type (%s).', 'ws-form'),
							esc_html($file_name),
							implode(', ', $field_accept_array)
						));
					}
				}
			}

			// Get image min width
	 		$file_image_min_width = WS_Form_Common::get_object_meta_value($field, 'file_image_min_width_restrict', '');

			// Get image max width
	 		$file_image_max_width = WS_Form_Common::get_object_meta_value($field, 'file_image_max_width_restrict', '');

			// Get image min height
	 		$file_image_min_height = WS_Form_Common::get_object_meta_value($field, 'file_image_min_height_restrict', '');

			// Get image max height
	 		$file_image_max_height = WS_Form_Common::get_object_meta_value($field, 'file_image_max_height_restrict', '');

			// Get image required aspect ratio
	 		$file_image_required_aspect_ratio = WS_Form_Common::get_object_meta_value($field, 'file_image_required_aspect_ratio', '');

	 		// Image restrictions
			if(
				(
					!empty($file_image_min_width) ||
					!empty($file_image_max_width) ||
					!empty($file_image_min_height) ||
					!empty($file_image_max_height) ||
					!empty($file_image_required_aspect_ratio)
				) &&

				in_array($file_type, array(

					'image/jpeg',
					'image/png',
					'image/gif'
				))
			) {

				$image = wp_get_image_editor($file_path);

				if(!is_wp_error($image)) {

					// Get image dimensions
					$image_size = $image->get_size();
					$image_width = $image_size['width'];
					$image_height = $image_size['height'];

					// Check min width
					if(
						!empty($file_image_min_width) &&
						$image_width < floatval($file_image_min_width)
					) {

						if(empty($file_name)) {

							// Throw error
							throw new Exception(sprintf(

								/* translators: %u = Minimum width in pixels */
								__('The width of the image is too small (Minimum: %u pixels).', 'ws-form'),
								$file_image_min_width
							));

						} else {

							// Throw error
							throw new Exception(sprintf(

								/* translators: %s = File name, %u = Minimum width in pixels */
								__('The width of %s is too small (Minimum: %u pixels).', 'ws-form'),
								esc_html($file_name),
								$file_image_min_width
							));
						}
					}

					// Check max width
					if(
						!empty($file_image_max_width) &&
						$image_width > floatval($file_image_max_width)
					) {

						if(empty($file_name)) {

							// Throw error
							throw new Exception(sprintf(

								/* translators: %u = Maximum width in pixels */
								__('The width of the image is too large (Maximum: %u pixels).', 'ws-form'),
								$file_image_max_width
							));

						} else {

							// Throw error
							throw new Exception(sprintf(

								/* translators: %s = File name, %u = Maximum width in pixels */
								__('The width of %s is too large (Maximum: %u pixels).', 'ws-form'),
								esc_html($file_name),
								$file_image_max_width
							));
						}
					}

					// Check min height
					if(
						!empty($file_image_min_height) &&
						$image_height < floatval($file_image_min_height)
					) {

						if(empty($file_name)) {

							// Throw error
							throw new Exception(sprintf(

								/* translators: %u = Minimum height in pixels */
								__('The height of the image is too small (Minimum: %u pixels).', 'ws-form'),
								$file_image_min_height
							));

						} else {

							// Throw error
							throw new Exception(sprintf(

								/* translators: %s = File name, %u = Minimum height in pixels */
								__('The height of %s is too small (Minimum: %u pixels).', 'ws-form'),
								esc_html($file_name),
								$file_image_min_height
							));
						}
					}

					// Check max height
					if(
						!empty($file_image_max_height) &&
						$image_height > floatval($file_image_max_height)
					) {

						if(empty($file_name)) {

							// Throw error
							throw new Exception(sprintf(

								/* translators: %u = Maximum height in pixels */
								__('The height of the image is too large (Maximum: %u pixels).', 'ws-form'),
								$file_image_max_height
							));

						} else {

							// Throw error
							throw new Exception(sprintf(

								/* translators: %s = File name, %u = Maximum height in pixels */
								__('The height of %s is too large (Maximum: %u pixels).', 'ws-form'),
								esc_html($file_name),
								$file_image_max_height
							));
						}
					}

					// Check aspect ratio
			 		if(!empty($file_image_required_aspect_ratio)) {

			 			// Get required aspect ratio
			 			$aspect_ratio_required_array = explode(':', $file_image_required_aspect_ratio);
			 			$aspect_ratio_required_x = isset($aspect_ratio_required_array[0]) ? floatval($aspect_ratio_required_array[0]) : 0;
			 			$aspect_ratio_required_y = isset($aspect_ratio_required_array[1]) ? floatval($aspect_ratio_required_array[1]) : 0;
			 			$aspect_ratio_required = ($aspect_ratio_required_y > 0) ? ($aspect_ratio_required_x / $aspect_ratio_required_y) : 0;

			 			// Get image aspect ratio
			 			$aspect_ratio_image = ($image_height > 0) ? ($image_width / $image_height) : 0;

			 			if(
			 				($aspect_ratio_required > 0) &&
			 				($aspect_ratio_image > 0) &&
			 				($aspect_ratio_required != $aspect_ratio_image)
			 			) {

							if(empty($file_name)) {

								// Throw error
								throw new Exception(sprintf(

									/* translators: %s = Aspect ratio */
									__('The aspect ratio of the image is incorrect (Must be: %s).', 'ws-form'),
									esc_html($file_image_required_aspect_ratio)
								));

							} else {

								// Throw error
								throw new Exception(sprintf(

									/* translators: %s = File name, %s = Aspect ratio */
									__('The aspect ratio of %s is incorrect (Must be: %s).', 'ws-form'),
									esc_html($file_name),
									esc_html($file_image_required_aspect_ratio)
								));
							}
						}
			 		}
				}
			}
		}

		// DropzoneJS - Upload
		public static function dropzonejs_upload($file_id, &$attachment_ids, $upload_path, $field, $file_name, $file_type, $file_size, $file_tmp_name) {

			// Check file restrictions
			try {

				self::check_file_restrictions($field, false, $file_type, $file_size, $file_tmp_name);

			} catch (Exception $e) {

				// Throw error
				throw new Exception($e->getMessage());
			}

			// Custom upload path
			if(!empty($upload_path)) {

				global $wsf_upload_path;
				$wsf_upload_path = $upload_path;

				$upload_dir_func = function($uploads) {

					global $wsf_upload_path;

					$uploads['path'] = $uploads['basedir'] . '/' . $wsf_upload_path;
					$uploads['url'] = $uploads['baseurl'] . '/' . $wsf_upload_path;
					$uploads['subdir'] = '/' . $wsf_upload_path;

					return $uploads;
				};

				// Set upload directory
				add_filter('upload_dir', $upload_dir_func);
			}

			// Prevent WS Form from creating multiple file sizes for scratch file
			add_filter('intermediate_image_sizes_advanced', array('WS_Form_File_Handler', 'intermediate_image_sizes_advanced'));

			// Process file with media_handle_upload
			$attachment_id = media_handle_upload($file_id, 0, array(), array('test_form' => false, 'action' => 'wsf_file_type_dropzonejs'));

			// Remove filters
			if(!empty($upload_path)) {

				remove_filter('upload_dir', $upload_dir_func);
			}

			remove_filter('intermediate_image_sizes_advanced', array('WS_Form_File_Handler', 'intermediate_image_sizes_advanced'));

			// Error checking
			if(is_wp_error($attachment_id)) {

				$error_message = __('Error handling media upload', 'ws-form');

				if(
					isset($attachment_id->errors) &&
					isset($attachment_id->errors['upload_error']) &&
					isset($attachment_id->errors['upload_error'][0])
				) {

					$error_message = $attachment_id->errors['upload_error'][0];
				}

				throw new Exception($error_message);
			}

			// Save original filename (Used for saved file name setting later)
			update_post_meta($attachment_id, '_wsf_file_name', basename(get_attached_file($attachment_id)));

			$attachment_ids[] = $attachment_id;
		}

		// DropzoneJS - Intermediate image sizing - Retain thumb only
		public static function intermediate_image_sizes_advanced($size) {

			// Get image size
			$image_size = apply_filters('wsf_dropzonejs_image_size', WS_FORM_DROPZONEJS_IMAGE_SIZE);

			return isset($size[$image_size]) ? array($image_size => $size[$image_size]) : array();
		}

		// DropzoneJS - Purge
		public static function dropzonejs_purge() {

			remove_action('pre_get_posts', 'WS_Form_File_Handler::dropzonejs_filter_attachments', 10);

			$cookie_timeout = absint(WS_Form_Common::option_get('cookie_timeout', 60 * 60 * 24 * 28));

			$before = gmdate('Y-m-d H:i:s', strtotime(sprintf('-%u seconds', $cookie_timeout)));

			$args = array(

				'post_type' => 'attachment',

				'posts_per_page' => -1,

				'meta_query' => array(

					array(

						'key'		=> '_wsf_attachment_scratch',
						'compare'	=> 'EXISTS',
					),
				),

				'date_query' => array(

					'column' => 'post_date_gmt',
					'before' => $before,
				),

				'post_parent' => 0,
			);

			$posts = get_posts($args);

			foreach($posts as $post) {

				if(!get_post_meta($post->ID, '_wsf_attachment_scratch', true)) { continue; }

				wp_delete_attachment($post->ID, true);
			}

			add_action('pre_get_posts', 'WS_Form_File_Handler::dropzonejs_filter_attachments', 10);
		}

		// DropzoneJS - Filter attachments
		public static function dropzonejs_filter_attachments($query) {

			if($query->get('post_type') !== 'attachment') { return; }

			$meta_query = $query->get('meta_query');

			if(!is_array($meta_query)) { $meta_query = array(); }

			if(isset($meta_query['relation']) && (strtolower($meta_query['relation']) === 'or')) {

				$meta_query = array($meta_query);
			}

			$meta_query[] = array(

				'key'     => '_wsf_attachment_scratch',
				'compare' => 'NOT EXISTS',
			);

			$query->set('meta_query', $meta_query);
		}
		// Get file object from URL
		public static function get_file_object_from_url($url) {

			$attachment_id = attachment_url_to_postid($url);
			if($attachment_id === 0) { return false; }

			return self::get_file_object_from_attachment_id($attachment_id);
		}

		// Get file object from attachment ID
		public static function get_file_object_from_attachment_id($attachment_id) {

			if(!$attachment_id) { return false; }

			// Get file path full
			$file_path_full = get_attached_file($attachment_id);
			if($file_path_full === false) { return false; }

			// Get file name
			$file_name = basename($file_path_full);			

			// Get file path
			$file_path = str_replace(wp_upload_dir()['basedir'] . '/', '', $file_path_full);

			// Get file size
			$file_size = 0;
			if(file_exists($file_path_full)) {

				$file_size = filesize($file_path_full);

			} else {

				return false;
			}

			// Get mime type
			$file_type = get_post_mime_type($attachment_id);

			// Get UUID
			$file_uuid = md5(get_the_guid($attachment_id));

			// Get image size
			$image_size = apply_filters('wsf_dropzonejs_image_size', WS_FORM_DROPZONEJS_IMAGE_SIZE);

			// Get file URL
			$file_url = wp_get_attachment_url($attachment_id);
			if(!$file_url) { $file_url = ''; }

			// Get file preview
			$file_preview = wp_get_attachment_image_src($attachment_id, $image_size, true);
			if($file_preview) {

				$file_preview = $file_preview[0];

			} else {

				$file_preview = wp_get_attachment_thumb_url($attachment_id);

				if(!$file_preview) { $file_preview = ''; }
			}
			if(!$file_preview) { $file_preview = ''; }

			// Build file object
			$file_object = array(

				'name'          => $file_name,
				'path'          => $file_path,
				'url'           => $file_url,
				'preview'       => $file_preview,
				'size'          => $file_size,
				'type'          => $file_type,
				'uuid'          => $file_uuid,		// Used by DropzoneJS to provide a unique ID
				'attachment_id' => $attachment_id
			);

			return $file_object;
		}

		// File name parse
		public static function file_name_parse($file_name, $form_object, $submit_object, $field, $file_count = 1, $file_index = 0, $section_repeatable_index = 0) {

			// Parse file_name
			$file_name_mask = WS_Form_Common::get_object_meta_value($field, 'file_name_mask', '');
			$file_name_mask = trim($file_name_mask);

			// Check file mask
			if($file_name_mask == '') { return $file_name; }

			// If file is multiple and #field_index is not in the mask, add it
			if(WS_Form_Common::get_object_meta_value($field, 'multiple_file', false)) {

				if(strpos($file_name_mask, '#file_index') === false) {

					// Split file mask into parts
					$file_name_mask_parts = pathinfo($file_name_mask);

					// We can only do this if an extension part is present
					if(isset($file_name_mask_parts['extension'])) {

						// Build new file name mask
						$file_name_mask = isset($file_name_mask_parts['filename']) ? $file_name_mask_parts['filename'] : '';
						$file_name_mask .= (($file_name_mask == '') ? '' : '-') . '#file_index';
						$file_name_mask .= '.' . $file_name_mask_parts['extension'];
					}
				}
			}

			// Split file name into parts
			$file_name_parts = pathinfo($file_name);

			// If split successful, parse file name
			if(is_array($file_name_parts)) {

				$file_filename = isset($file_name_parts['filename']) ? $file_name_parts['filename'] : '';

				$file_name_values = array(

					'file_basename' => isset($file_name_parts['basename']) ? $file_name_parts['basename'] : '',
					'file_filename' => $file_filename,
					'file_extension' => isset($file_name_parts['extension']) ? $file_name_parts['extension'] : '',
					'file_index' => $file_index,
					'file_repeater_index' => $section_repeatable_index
				);

				$file_name = WS_Form_Common::mask_parse($file_name_mask, $file_name_values);
			}

			// Regular parse
			$file_name = WS_Form_Common::parse_variables_process($file_name, $form_object, $submit_object, 'text/plain', false, $section_repeatable_index);

			return $file_name;
		}

		public static function check_file_type($file_name) {

			// WordPress check file type
			$wp_filetype = wp_check_filetype($file_name);

			// Invalid file type?
			if((!$wp_filetype['type'] || !$wp_filetype['ext']) && !current_user_can('unfiltered_upload')) {

				// Throw error
				return false;
			}

			return $file_name;
		}

		public function api_call($endpoint, $path = '', $method = 'GET', $body = null, $headers = array(), $authentication = 'basic', $username = false, $password = false, $accept = 'application/json', $content_type = 'application/json') {
			
			// Build query string
			$query_string = (($body !== null) && ($body !== false) && ($method == 'GET')) ? '?' . http_build_query($body) : '';

			// Headers
			if($accept !== false) { $headers['Accept'] = $accept; }
			if($content_type !== false) { $headers['Content-Type'] = $content_type; }
			if($username !== false) {

				switch($authentication) {

					case 'basic' :

						$headers['Authorization']  = 'Basic ' . base64_encode($username . ':' . $password);
						break;
				}
			}

			// Build args
			$args = array(

				'method'		=> $method,
				'headers'		=> $headers,
				'user-agent'	=> WS_Form_Common::get_request_user_agent(),
				'timeout'		=> WS_Form_Common::get_request_timeout(),
				'sslverify'		=> WS_Form_Common::get_request_sslverify()
			);

			// Add body
			if(
				($method != 'GET') &&
				($body !== null) &&
				($body !== false)
			) {
				$args['body'] = $body;
			}

			// URL
			$url = $endpoint . $path . $query_string;

			// Call using Wordpress wp_remote_request
			$wp_remote_request_response = wp_remote_request($url, $args);

			// Check for error
			if($api_response_error = is_wp_error($wp_remote_request_response)) {

				// Handle error
				$api_response_error_message = $wp_remote_request_response->get_error_message();
				$api_response_headers = array();
				$api_response_body = '';
				$api_response_http_code = 0;

			} else {

				// Handle response
				$api_response_error_message = '';
				$api_response_headers = wp_remote_retrieve_headers($wp_remote_request_response);
				$api_response_body = wp_remote_retrieve_body($wp_remote_request_response);
				$api_response_http_code = wp_remote_retrieve_response_code($wp_remote_request_response);
			}

			// Return response
			return array('error' => $api_response_error, 'error_message' => $api_response_error_message, 'response' => $api_response_body, 'http_code' => $api_response_http_code, 'headers' => $api_response_headers);
		}

		// Get value of an object, otherwise return false if not set
		public function get_object_value($field, $key) {

			return isset($field->{$key}) ? $field->{$key} : false;
		}

		// Get file description
		public function get_file_description(&$value_array, $file_object, $file_url, $file_links, $file_embed, $file_description, $content_type = 'text/html') {

			// File descriptions
			if($file_description) {

				// Get file name
				$file_name = $file_object['name'];

				// Get file size
				$file_size = WS_Form_Common::get_file_size($file_object['size']);

				// File links
				if($file_links) {

					if($content_type == 'text/plain') {

						$value_array[] = sprintf("%s\n%s\n%s", $file_name, $file_url, $file_size);

					} else {

						$value_array[] = sprintf('<a href="%s" target="_blank">%s</a> (%s)', $file_url, $file_name, $file_size);
					}

				} else {

					$value_array[] = sprintf('%s (%s)', $file_name, $file_size);
				}
			}

			// Fallback
			if(!$file_embed && !$file_description) {

				// Get file name
				$file_name = $file_object['name'];

				// File links
				if($file_links) {

					if($content_type == 'text/plain') {

						$value_array[] = sprintf("%s\n%s", $file_name, $file_url);

					} else {

						$value_array[] = sprintf('<a href="%s" target="_blank">%s</a>', $file_url, $file_name);
					}

				} else {

					$value_array[] = $file_name;
				}
			}

			return $value_array;
		}

		// Rename an attachment
		public function attachment_rename($attachment_id, $file_name_new) {

			global $wpdb;

			// Get old file path full
			$file_path_full_old = get_attached_file($attachment_id);

			// Get path info
			$file_pathinfo = pathinfo($file_path_full_old);

			// Get old file name
			$file_name_old = $file_pathinfo['basename'];

			// Get path to attachment
			$file_dirname = $file_pathinfo['dirname'];

			// Get unique file name
			$file_name_new_unique = wp_unique_filename($file_dirname, $file_name_new);

			// Get new full path
			$file_path_full_new = sprintf('%s/%s', $file_dirname, $file_name_new_unique);

			// Rename file
			rename($file_path_full_old, $file_path_full_new);

			// Build new meta data
			$meta = wp_get_attachment_metadata($attachment_id);
			$meta['file'] = str_replace($file_name_old, $file_name_new_unique, $meta['file']);

			// Rename the sizes
			$old_filename_sizemedia = pathinfo(basename($file_path_full_old), PATHINFO_FILENAME);
			$new_filename_sizemedia = pathinfo(basename($file_path_full_new), PATHINFO_FILENAME);

			foreach((array)$meta['sizes'] AS $size => $meta_size) {

				$old_filepath_sizemedia = dirname($file_path_full_old ) . DIRECTORY_SEPARATOR . $meta['sizes'][$size]['file'];
				$meta['sizes'][$size]['file'] = str_replace($old_filename_sizemedia, $new_filename_sizemedia, $meta['sizes'][$size]['file'] );
				$new_filepath_sizemedia = dirname( $file_path_full_new ) . DIRECTORY_SEPARATOR . $meta['sizes'][$size]['file'];
				rename($old_filepath_sizemedia, $new_filepath_sizemedia );
			}

			// Update attachment meta data
			wp_update_attachment_metadata($attachment_id, $meta);

			// Update attachment path
			update_attached_file($attachment_id, $file_path_full_new);

			// Update title of attached file
			wp_update_post(

				array (
					'ID'         => $attachment_id,
					'post_title' => $file_name_new_unique,
					'post_name'  => ''
				)
			);

			// Update _wsf_file_name
			update_post_meta($attachment_id, '_wsf_file_name', $file_name_new_unique);

			// Get attachment URL
			$attachment_url = wp_get_attachment_url($attachment_id);

			// Update guid
			$wpdb->update($wpdb->posts, array('guid' => $attachment_url), array('ID' => $attachment_id));

			// Clear attachment cache
			wp_cache_delete($attachment_id, 'posts');
			wp_cache_delete($attachment_id, 'post_meta');
		}
	}
