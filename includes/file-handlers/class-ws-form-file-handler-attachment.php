<?php

	class WS_Form_File_Handler_Attachment extends WS_Form_File_Handler {

		public $id = 'attachment';
		public $pro_required = false;
		public $label;
		public $public = true;

		public function __construct() {

			// Set label
			$this->label = __('Media Library', 'ws-form');

			// Register action
			parent::register($this);

			// Create intial file handler
			add_filter('wsf_file_handler_' . $this->id, array($this, 'handler'), 10, 5);
		}

		// Handler
		public function handler($file_objects, $submit, $field, $section_repeatable_index) {

			$form_id = $submit->form_id;
			$submit_hash = $submit->hash;

			// Check form ID
			WS_Form_Common::check_form_id($form_id);

			// Need to require these files
			if(!function_exists('media_handle_upload')) {

				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				require_once(ABSPATH . "wp-admin" . '/includes/file.php');
				require_once(ABSPATH . "wp-admin" . '/includes/media.php');
			}

			// Field ID
			$field_id = absint($field->id);
			if($field_id == 0) { parent::db_throw_error(__('Invalid field ID', 'ws-form')); }

			foreach($file_objects as $file_object_index => $file_object) {

				// Get file name
				if(!isset($file_object['name'])) { parent::db_throw_error(__('File name not found in file object', 'ws-form')); }
				$file_name = $file_object['name'];

				// Get file type
				if(!isset($file_object['type'])) { parent::db_throw_error(__('File type not found in file object', 'ws-form')); }
				$file_type = $file_object['type'];

				// Get file path
				if(!isset($file_object['path'])) { parent::db_throw_error(__('File source path not found in file object', 'ws-form')); }
				$file_path = $file_object['path'];

				// Get file size
				if(!isset($file_object['size'])) { parent::db_throw_error(__('File size not found in file object', 'ws-form')); }
				$file_size = $file_object['size'];

				// Get attachment ID
				$attachment_id = isset($file_object['attachment_id']) ? $file_object['attachment_id'] : false;

				// If the file upload field is configured to use DropzoneJS and this is not a WS Form scratch file then skip it
				if(
					($field->type == 'file') &&
					(WS_Form_Common::get_object_meta_value($field, 'sub_type', true) == 'dropzonejs') &&
					!get_post_meta($attachment_id, '_wsf_attachment_scratch', true)
				) {
					continue;
				}

				// File ID
				$file_id = sprintf('field_%u_%u', $field_id, $file_object_index);

				// If attachment has not yet been handled, create attachment
				if($attachment_id === false) {

					// Build file array
					$_FILES[$file_id] = array(

						'name'					=>	$file_name,
						'type'					=>	$file_type,
						'tmp_name'				=>	$file_path,
						'error'					=>	0,
						'size'					=>	$file_size,
					);

					$attachment_id = media_handle_upload($file_id, 0, array(), array('test_form' => false, 'action' => 'wsf_file_handler_attachment'));

					if(is_wp_error($attachment_id)) {

						$error_message = __('Error handling media upload', 'ws-form');

						if(
							isset($attachment_id->errors) &&
							isset($attachment_id->errors['upload_error']) &&
							isset($attachment_id->errors['upload_error'][0])
						) {

							$error_message = $attachment_id->errors['upload_error'][0];
						}

						parent::db_throw_error(

							sprintf(__('File handler error [%s]: %s', 'ws-form'), $this->id, $error_message)
						);
					}
				}

				// Add meta data
				update_post_meta($attachment_id, '_wsf_attachment_handler_' . $this->id, true);

				// Remove as scratch
				delete_post_meta($attachment_id, '_wsf_attachment_scratch');

				// Get old file path full
				$file_path_full_old = get_attached_file($attachment_id);

				// Get path info
				$file_pathinfo = pathinfo($file_path_full_old);

				// Get old file name
				$file_name_old = $file_pathinfo['basename'];

				// If the file names are different, we should rename the attachment file
				if($file_name != $file_name_old) {

					WS_Form_File_Handler::attachment_rename($attachment_id, $file_name);
				}

				// Custom title, caption and description
				$attachment_update = array();

				// Set title
				$attachment_title = trim(WS_Form_Common::get_object_meta_value($field, 'attachment_title', ''));
				if($attachment_title !== '') {

					// Parse
					$attachment_update['post_title'] = WS_Form_Common::parse_variables_process($attachment_title, $submit->form_object, $submit, 'text/plain', false, $section_repeatable_index);
				}

				// Set caption
				$attachment_caption = trim(WS_Form_Common::get_object_meta_value($field, 'attachment_caption', ''));
				if($attachment_caption !== '') {

					// Parse
					$attachment_update['post_excerpt'] = WS_Form_Common::parse_variables_process($attachment_caption, $submit->form_object, $submit, 'text/plain', false, $section_repeatable_index);
				}

				// Set description
				$attachment_description = trim(WS_Form_Common::get_object_meta_value($field, 'attachment_description', ''));
				if($attachment_description !== '') {

					// Parse
					$attachment_update['post_content'] = WS_Form_Common::parse_variables_process($attachment_description, $submit->form_object, $submit, 'text/plain', false, $section_repeatable_index);
				}

				// Set alt text
				$attachment_alt = trim(WS_Form_Common::get_object_meta_value($field, 'attachment_alt', ''));
				if($attachment_alt !== '') {

					// Parse
					$attachment_update['meta_input'] = array(

						'_wp_attachment_image_alt' => WS_Form_Common::parse_variables_process($attachment_alt, $submit->form_object, $submit, 'text/plain', false, $section_repeatable_index)
					);
				}

				if(count($attachment_update) > 0) {

					$attachment_update['ID'] = $attachment_id;

					wp_update_post($attachment_update);
				}

				// Ensure wp_update_image_subsizes is defined
				if(!function_exists('wp_update_image_subsizes')) {

					require_once ABSPATH . 'wp-admin/includes/image.php';
				}

				// Update image sub sizes
				wp_update_image_subsizes($attachment_id);

				// Get file path
				$file_path = str_replace(wp_upload_dir()['basedir'] . '/', '', get_attached_file($attachment_id));

				// Set path
				$file_objects[$file_object_index]['path'] = $file_path;
				$file_objects[$file_object_index]['attachment_id'] = $attachment_id;
			}

			self::touch($file_objects);

			return $file_objects;
		}

		// Get URL
		public function get_url($file_object, $field_id = 0, $file_object_index = 0, $submit_hash = '', $section_repeatable_index = 0) {

			// Ensure this file object belongs to this file handler
			if(!isset($file_object['handler']) || ($file_object['handler'] != $this->id)) { return false; }

			// Check attachment ID exists
			if(!isset($file_object['attachment_id'])) { return false; }

			$url = wp_get_attachment_url($file_object['attachment_id']);

			if($url === false) { return ''; }

			return $url;
		}

		// Get value for parse variables
		public function get_value_parse_variable($file_object, $field_id = 0, $file_object_index = 0, $submit_hash = '', $file_links = false, $file_embed = false, $content_type = 'text/html', $file_description = true, $field_type = false) {

			$value_array = array();

			// Check if file embed can be used
			if($content_type == 'text/plain') { $file_embed = false; }

			// Get file URL
			$file_url = ($file_links || $file_embed) ? self::get_url($file_object, $field_id, $file_object_index, $submit_hash) : false;

			// File embed
			if($file_embed) {

				// Build data type attribute
				$data_type_attribute = ($field_type !== false) ? sprintf(' data-type="%s"', esc_attr($field_type)) : '';

				if($file_links) {

					$value_array[] = sprintf('<a href="%1$s" target="_blank"><img src="%1$s" style="max-width: 100%%;"%2$s /></a>', $file_url, $data_type_attribute);

				} else {

					$value_array[] = sprintf('<img src="%s" style="max-width: 100%%;"%s />', $file_url, $data_type_attribute);
				}
			}

			// Add description
			parent::get_file_description($value_array, $file_object, $file_url, $file_links, $file_embed, $file_description, $content_type);

			return implode((($content_type == 'text/html') ? '<br />' : "\n"), $value_array);
		}

		// Copy to file
		public function copy_to_temp_file($file_object, $temp_path = false) {

			// Ensure this file object belongs to this file handler
			if(!isset($file_object['handler']) || ($file_object['handler'] != $this->id)) { return false; }

			// Check attachment id
			if(!isset($file_object['attachment_id']) || ($file_object['attachment_id'] == '')) { return false; }
			$attachment_id = absint($file_object['attachment_id']);
			if(!$attachment_id) { return false; }

			// Check name
			if(!isset($file_object['name']) || ($file_object['name'] == '')) { return false; }
			$name = $file_object['name'];
			if(!$name) { return false; }

			// Get file path to copy from
			$file_path_copy_from = get_attached_file($attachment_id);
			if($file_path_copy_from === false) { return false; }

			// Check file exists
			if(!file_exists($file_path_copy_from)) { return false; }

			// Get file path to copy to
			require_once(ABSPATH . 'wp-admin/includes/file.php');

			if($temp_path === false) {

				$file_path_copy_to = wp_tempnam();

			} else {

				if(!file_exists($temp_path)) {

					wp_mkdir_p($temp_path);
				}

				if(!isset($file_object['name']) || ($file_object['name'] == '')) { return false; }

				$file_path_copy_to = $temp_path . '/' . $file_object['name'];
			}

			// Create temporary file
			return copy($file_path_copy_from, $file_path_copy_to) ? $file_path_copy_to : false;
		}

		// Get temp file
		public function get_temp_file($file_object, $temp_path = false) {

			// Ensure this file object belongs to this file handler
			if(!isset($file_object['handler']) || ($file_object['handler'] != $this->id)) { return false; }

			// Check attachment id
			if(!isset($file_object['attachment_id']) || ($file_object['attachment_id'] == '')) { return false; }
			$attachment_id = absint($file_object['attachment_id']);
			if(!$attachment_id) { return false; }

			return array(

				'path' 				=> get_attached_file($attachment_id),
				'unlink_after_use' 	=> false
			);
		}

		// Delete file
		public function delete($file_object) {

			// Ensure this file object belongs to this file handler
			if(!isset($file_object['handler']) || ($file_object['handler'] != $this->id)) { return false; }

			// Read attachment ID
			if(!isset($file_object['attachment_id'])) { return false; }
			$attachment_id = absint($file_object['attachment_id']);
			if(!$attachment_id) { return false; }

			// Ensure attachment_id was uploaded by this handler
			if(!get_post_meta($attachment_id, '_wsf_attachment_handler_' . $this->id, true)) { return false; }

			// Delete attachment
			wp_delete_attachment($attachment_id, true);

			return true;
		}
	}

	new WS_Form_File_Handler_Attachment();
