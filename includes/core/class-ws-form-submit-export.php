<?php

	class WS_Form_Submit_Export extends WS_Form_Core {

		public $ws_form_submit;
		public $form_id;

		public function __construct($form_id = false) {

			// Check form ID
			if(empty($form_id)) {

				throw new Exception(__('Form ID empty', 'ws-form'));
			}

			// Initial WS_Form_Submit class
			$this->ws_form_submit = New WS_Form_Submit();
			$this->ws_form_submit->form_id = $form_id;

			// Set form ID
			$this->form_id = $form_id;
		}

		// Get rows
		public function get_row_by_id($id, $bypass_user_capability_check = false, $clear_hidden_fields = false, $sanitize_rows = true) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Get record from core
			$submit = $this->ws_form_submit->db_read(

				false,										// Get meta
				false,										// Get expanded
				false,										// Bypass user capability check
				$clear_hidden_fields 						// Clear hidden fields
			);

			return self::process_rows(array($submit), $bypass_user_capability_check, $clear_hidden_fields, $sanitize_rows);
		}

		// Get header
		public function get_header($bypass_user_capability_check = false) {

			return $this->ws_form_submit->get_keys_all($bypass_user_capability_check);
		}

		// Get rows
		public function get_rows($limit = false, $offset = 0, $keyword = '', $filters = false, $order_by = 'id', $order = 'DESC', $bypass_user_capability_check = false, $clear_hidden_fields = false, $sanitize_rows = true) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Get records from core
			$submits = $this->ws_form_submit->db_read_all(

				$this->ws_form_submit->get_join($keyword, $order_by, $bypass_user_capability_check),
				$this->ws_form_submit->get_where($filters, $bypass_user_capability_check),
				$this->ws_form_submit->get_group_by(),
				$this->ws_form_submit->get_order_by($order_by, $order, $bypass_user_capability_check),
				self::get_limit($limit),					// Limit
				self::get_offset($offset),					// Offset
				false,										// Get meta
				false,										// Get expanded
				$bypass_user_capability_check,				// Bypass user capability check
				$clear_hidden_fields 						// Clear hidden fields
			);

			// Return processed rows
			return (empty($submits) || !is_array($submits)) ? array() : self::process_rows($submits, $bypass_user_capability_check, $clear_hidden_fields, $sanitize_rows);
		}

		// Process submit rows
		public function process_rows($submits, $bypass_user_capability_check = false, $clear_hidden_fields = false, $sanitize_rows = true) {

			$rows = array();

			// Get keys
			$keys_fixed = $this->ws_form_submit->get_keys_fixed($bypass_user_capability_check);

			// Get field data
			$this->ws_form_submit->db_get_submit_fields($bypass_user_capability_check);

			// Process meta data
			foreach($submits as $key => $submit_object) {

				// Read expanded
				$this->ws_form_submit->db_read_expanded($submit_object, true, true, true, true, true, true, true, $bypass_user_capability_check);

				// Get meta data
				$submit_object->meta = $this->ws_form_submit->db_get_submit_meta($submit_object, false, $bypass_user_capability_check);

				// Clear hidden fields
				if($clear_hidden_fields) {

					$submit_object = $this->ws_form_submit->clear_hidden_meta_values($submit_object);
				}

				// Build CSV row
				$row = array();

				// Fixed fields
				foreach($keys_fixed as $key => $value) {

					switch($key) {

						case 'date_added' :

							$row[$key] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($submit_object->date_added)));
							break;

						case 'date_updated' :

							$row[$key] = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($submit_object->date_updated)));
							break;

						case 'user_first_name' :

							$row[$key] = (isset($submit_object->user) && !$bypass_user_capability_check) ? $submit_object->user->first_name : '';
							break;

						case 'user_last_name' :

							$row[$key] = (isset($submit_object->user) && !$bypass_user_capability_check) ? $submit_object->user->last_name : '';
							break;

						case 'user_id' :

							$row[$key] = (isset($submit_object->user_id) && !$bypass_user_capability_check) ? $submit_object->{$key} : 0;
							break;

						case 'id' :
						case 'status' :
						case 'status_full' :
						case 'duration' :

							$row[$key] = isset($submit_object->{$key}) ? $submit_object->{$key} : '';
							break;

						default :

							$row[$key] = isset($submit_object->meta[$key]) ? $submit_object->meta[$key] : '';
					}
				}

				// Form fields
				foreach($this->ws_form_submit->submit_fields as $id => $field) {

					$field_name = WS_FORM_FIELD_PREFIX . $id;

					// Get type
					$type = isset($submit_object->meta[$field_name]) ? (isset($submit_object->meta[$field_name]['type']) ? $submit_object->meta[$field_name]['type'] : '') : '';

					// Get value
					$value = isset($submit_object->meta[$field_name]) ? (isset($submit_object->meta[$field_name]['value']) ? $submit_object->meta[$field_name]['value'] : '') : '';

					// Apply filter
					$value = apply_filters('wsf_submit_field_type_csv', $value, $id, $type);

					// Process by type
					switch($type) {

						case 'signature' :
						case 'file' :

							if(!is_array($value)) { break; }

							$value_array = array();

							foreach($value as $file_object_index => $file_object) {

		 						// Get file handler
								$file_handler = isset($file_object['handler']) ? $file_object['handler'] : '';
		 						if($file_handler == '') { $file_handler = 'wsform'; }
		 						if(!isset(WS_Form_File_Handler::$file_handlers[$file_handler])) { continue; }
		 						$file_handler = WS_Form_File_Handler::$file_handlers[$file_handler];

								// Get value array
		 						$value_array[] = $file_handler->get_url($file_object, $id, $file_object_index, $submit_object->hash);
							}

							$value = implode(',', $value_array);

							break;

						case 'datetime' :

							if(
								is_array($value) &&
								isset($value['mysql'])
							) {
								$value = $value['mysql'];
							}
							break;

						case 'googlemap' :

							if(
								is_array($value) &&
								isset($value['lat']) &&
								isset($value['lng'])
							) {
								$value = sprintf('%.7f,%.7f', $value['lat'], $value['lng']);
							}
							break;
					}

					// Process array values (e.g. Select, Checkbox, Radio field types)
					if(is_array($value)) { $value = implode(',', $value); }

					// Add column
					$row['field_' . $id] = $value;
				}

				// E-Commerce
				foreach($this->ws_form_submit->get_keys_ecommerce($bypass_user_capability_check) as $key => $label) {

					// Get value
					$value = isset($submit_object->meta[$key]) ? $submit_object->meta[$key] : '';

					// Add column
					$row[$key] = $value;
				}

				// Tracking
				foreach($this->ws_form_submit->get_keys_tracking($bypass_user_capability_check) as $key => $label) {

					// Get tracking value
					$value = isset($submit_object->meta[$key]) ? $submit_object->meta[$key] : '';

					// Add column
					$row[$key] = $value;
				}
				// Sanitize row
				if($sanitize_rows) {

					$row = self::sanitize_row($row);
				}

				// Add to rows
				$rows[] = apply_filters('wsf_submit_export_csv_row', $row, $this->form_id, $submit_object);
			}

			return $rows;
		}

		// Sanitize row
		public function sanitize_row($row) {

			return array_map(function($column) {

				return esc_html($column);

			}, $row);
		}

		// Get record ids
		public function get_ids($limit = false, $offset = 0, $keyword = '', $filters = false, $order_by = 'id', $order = 'DESC', $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_submission', $bypass_user_capability_check);

			// Get records from core
			$ids = $this->ws_form_submit->db_read_ids(

				$this->ws_form_submit->get_join($keyword, $order_by, $bypass_user_capability_check),
				$this->ws_form_submit->get_where($filters, $bypass_user_capability_check),
				$this->ws_form_submit->get_group_by(),
				$this->ws_form_submit->get_order_by($order_by, $order, $bypass_user_capability_check),
				self::get_limit($limit),					// Limit
				self::get_offset($offset),					// Offset
				false,										// Get meta
				false,										// Get expanded
				$bypass_user_capability_check				// Bypass user capability check
			);

			return is_null($ids) ? array() : $ids;
		}

		// Get record count
		public function get_row_count($keyword = '', $filters = false, $bypass_user_capability_check = false) {

			return $this->ws_form_submit->db_read_count(

				$this->ws_form_submit->get_join($keyword, 'id', $bypass_user_capability_check),
				$this->ws_form_submit->get_where($filters, $bypass_user_capability_check),
				$bypass_user_capability_check
			);
		}

		// Check limit
		public function get_limit($limit = false) {

			return empty($limit) ? absint(apply_filters('wsf_submit_export_page_size', WS_FORM_SUBMIT_EXPORT_PAGE_SIZE)) : $limit;
		}

		// Check offset
		public function get_offset($offset = false) {

			return absint($offset);
		}

		// Get CSV page
		public function get_csv_page(&$file, $page = 0, $keyword = '', $filters = false, $order_by = 'id', $order = 'DESC', $bypass_user_capability_check = false, $clear_hidden_fields = false, $sanitize_rows = true) {

			// User capability check
			WS_Form_Common::user_must('export_submission');

			// Clear hidden fields?
			$clear_hidden_fields = (get_user_meta(get_current_user_id(), 'ws_form_submissions_clear_hidden_fields', true) === 'on');

			// Limit
			$limit = absint(apply_filters('wsf_submit_export_page_size', WS_FORM_SUBMIT_EXPORT_PAGE_SIZE));

			// Offset
			$offset = ($page * $limit);

			// Output header
			if($page === 0) {

				// Get header and apply filter
				$row = apply_filters('wsf_submit_export_csv_header', $this->ws_form_submit->get_keys_all(), $this->form_id);

				// Sanitize row
				if($sanitize_rows) {

					$csv_header = self::sanitize_row($row);
				}

				// Output first column
				fwrite($file, '"ID",');	// To overcome issue with Excel thinking 'ID,' is an SYLK file

				// Write escaped fputcsv
				WS_Form_Common::esc_fputcsv($file, array_slice($row, 1));	// array_slice skips ID column
			}

			// Get records
			$rows = self::get_rows($limit, $offset, $keyword, $filters, $order_by, $order, $bypass_user_capability_check, $clear_hidden_fields, $sanitize_rows);

			// Process records
			foreach($rows as $row) {

				// Write escaped fputcsv
				WS_Form_Common::esc_fputcsv($file, $row);
			}

			// Return data
			return array(

				'records_processed' => $offset + (is_null($rows) ? 0 : count($rows)),
				'records_total' => (($page === 0) ? self::get_row_count() : false)
			);
		}
	}