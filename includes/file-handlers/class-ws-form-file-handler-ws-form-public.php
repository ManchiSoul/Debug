<?php

	class WS_Form_File_Handler_WS_Form_Public extends WS_Form_File_Handler {

		public $id = 'wsformpublic';
		public $pro_required = false;
		public $label;
		public $public = true;

		public function __construct() {

			// Set label
			$this->label = sprintf(

				/* translators: %s = WS Form */
				__('%s (Public)', 'ws-form'),

				WS_FORM_NAME_GENERIC
			);

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

			// Check hash
			if(!WS_Form_Common::check_submit_hash($submit_hash)) {

				parent::db_throw_error(__('Invalid hash ID (File handler: wsformpublic)', 'ws-form'));
			}

			// Field ID
			$field_id = absint($field->id);
			if($field_id == 0) { parent::db_throw_error(__('Invalid field ID', 'ws-form')); }

			foreach($file_objects as $file_object_index => $file_object) {

				// Get file path
				if(!isset($file_object['path'])) { parent::db_throw_error(__('File source path not found in file object', 'ws-form')); }
				$file_path = $file_object['path'];

				// Get file name
				if(!isset($file_object['name'])) { parent::db_throw_error(__('File name not found in file object', 'ws-form')); }
				$file_name = $file_object['name'];

				// Check and sanitize file name
				$file_name = WS_Form_File_Handler::check_file_type($file_name);
				if($file_name === false) { parent::db_throw_error(__('Sorry, you are not allowed to upload this file type.', 'ws-form')); }

				// Sanitize file name
				$file_name = sanitize_file_name($file_name);

				// Build file upload path
				$file_upload_path = $form_id . '/' . $submit_hash . '/' . $field_id;

				// Apply filter
				$file_upload_path = apply_filters('wsf_file_handler_' . $this->id . '_upload_path', $file_upload_path);

				$upload_dir = WS_Form_Common::upload_dir_create($file_upload_path);
				if($upload_dir['error']) {

					parent::db_throw_error($upload_dir['error']);
				}
				$file_upload_dir = $upload_dir['dir'];

				// Move uploaded file to WordPress uploads folder
				$move_uploaded_file_destination = $file_upload_dir . '/' . $file_name;
				if(!rename($file_path, $move_uploaded_file_destination)) {

					parent::db_throw_error(__('Unable to move file to destination.', 'ws-form'));
				}

				// Set path
				$file_objects[$file_object_index]['path'] = $upload_dir['path'] . '/' . $file_name;
			}

			self::touch($file_objects);

			return $file_objects;
		}

		// Get URL
		public function get_url($file_object, $field_id = 0, $file_object_index = 0, $submit_hash = '', $section_repeatable_index = 0) {

			// Ensure this file object belongs to this file handler
			if(!isset($file_object['handler']) || ($file_object['handler'] != $this->id)) { return false; }

			// Check path exists
			if(!isset($file_object['path'])) { return false; }

			return sprintf('%s/%s', WS_Form_Common::get_upload_dir_base_url(), $file_object['path']);
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

			// Check path
			if(!isset($file_object['path']) || ($file_object['path'] == '')) { return false; }
			$path = $file_object['path'];

			// Check name
			if(!isset($file_object['name']) || ($file_object['name'] == '')) { return false; }
			$name = $file_object['name'];

			// Get file path to copy from
			$file_path_copy_from = sprintf('%s/%s', wp_upload_dir()['basedir'], $file_object['path']);

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

			// Check path
			if(!isset($file_object['path']) || ($file_object['path'] == '')) { return false; }

			return array(

				'path' 				=> sprintf('%s/%s', wp_upload_dir()['basedir'], $file_object['path']),
				'unlink_after_use' 	=> false
			);
		}

		// Delete file
		public function delete($file_object) {

			// Ensure this file object belongs to this file handler
			if(!isset($file_object['handler']) || ($file_object['handler'] != $this->id)) { return false; }

			// Read path
			if(!isset($file_object['path'])) { return false; }
			$path = $file_object['path'];

			// Read size
			if(!isset($file_object['size'])) { return false; }
			$size = $file_object['size'];

			// File to delete
			$file_to_delete = sprintf('%s/%s', wp_upload_dir()['basedir'], $path);

			// Check path does not contain rogue data
			if(strpos($file_to_delete, '..') !== false) { return false; }

			// Check file exists
			if(!file_exists($file_to_delete)) { return false; }

			// Check filesize
			if(filesize($file_to_delete) !== $size) { return false; }

			// Delete file
			if(!@unlink($file_to_delete)) { return false; }

			return true;
		}
	}

	new WS_Form_File_Handler_WS_Form_Public();
