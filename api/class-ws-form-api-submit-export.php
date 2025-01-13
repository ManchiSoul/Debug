<?php

	class WS_Form_API_Submit_Export extends WS_Form_API {

		public function __construct() {

			// Call parent on WS_Form_API
			parent::__construct();
		}

		// API - Export
		public function api_export() {

			// Get form ID
			$form_id = absint(WS_Form_Common::get_query_var_nonce('id'));

			// Get page
			$page = absint(WS_Form_Common::get_query_var_nonce('page'));

			// Get hash
			$hash = WS_Form_Common::get_query_var_nonce('hash');

			// Check hash
			if(empty($hash)) {

				$hash = wp_hash($form_id . '_' . time() . '_' . wp_rand());
			}

			// Get CSV file name
			$csv_file_name = self::api_get_csv_file_name($hash, $form_id);

			// First page
			if($page === 0) {

				$csv_file_pointer = fopen($csv_file_name, 'w');

				if($csv_file_pointer === false) {

					return self::api_export_error(sprintf(

						/* translators: %s = CSV file name */
						__('Unable to create temporary file: %s', 'ws-form'),

						$csv_file_name
					));
				}

			} else {

				if(!file_exists($csv_file_name)) {

					return self::api_export_error(sprintf(

						/* translators: %s = CSV file name */
						__('Unable to open temporary file %s', 'ws-form'),

						$csv_file_name
					));
				}

				$csv_file_pointer = fopen($csv_file_name, 'a');
			}

			// Build page
			$ws_form_submit_export = New WS_Form_Submit_Export($form_id);
			$db_export_csv_page_return = $ws_form_submit_export->get_csv_page(

				$csv_file_pointer,
				$page,
				WS_Form_Common::get_query_var_nonce('keyword'),
				WS_Form_Common::get_admin_submit_filters(),
				WS_Form_Common::get_query_var_nonce('orderby'),
				WS_Form_Common::get_query_var_nonce('order'),
				false,																// Bypass capability check
				apply_filters('wsf_submit_export_csv_clear_hidden_fields', true),	// Clear hidden fields
				false																// Sanitize rows (Retain HTML for CSV files)
			);

			// Get records processed
			$records_processed = $db_export_csv_page_return['records_processed'];

			// Get records total
			$records_total = $db_export_csv_page_return['records_total'];

			// Get page size
			$page_size = apply_filters('wsf_submit_export_page_size', WS_FORM_SUBMIT_EXPORT_PAGE_SIZE);

			// Complete?
			$complete = ($records_processed < ($page_size * ($page + 1)));

			// Build return array
			$return_array = array('error' => false, 'complete' => $complete, 'hash' => $hash, 'records_processed' => $records_processed, 'records_total' => $records_total, 'page' => $page, 'page_size' => $page_size);

			// Add download URL if completed
			if($complete) {

				$return_array['url'] = WS_Form_Common::get_api_path(sprintf('submit/export/%s', urlencode($hash)), sprintf('_wpnonce=%s', urlencode(wp_create_nonce('wp_rest'))));
			}

			return $return_array;
		}

		// API export - Error
		public function api_export_error($error_message) {

			return array('error' => true, 'error_message' => $error_message);
		}

		// API export - Get
		public function api_export_get($parameters) {

			// Get hash
			$hash = WS_Form_Common::get_query_var_nonce('wsf_hash', false, $parameters);

			// Get CSV file name
			$csv_file_name = self::api_get_csv_file_name($hash);

			// Check file name
			if(!file_exists($csv_file_name)) { throw new Exception(__('CSV file not found', 'ws-form')); }

			// Check file size
			$csv_file_size = filesize($csv_file_name);

			// If file size large, zip the file
			if($csv_file_size > apply_filters('wsf_submit_export_file_size_zip', WS_FORM_SUBMIT_EXPORT_FILE_SIZE_ZIP)) {

				// Create zip archive
				$zip = new ZipArchive();

				// Build file names
				$zip_file_name = sprintf('%s%s', get_temp_dir(), WS_Form_Common::filename_datestamp('ws-form-submit', 'zip'));
				$csv_file_name_base = WS_Form_Common::filename_datestamp('ws-form-submit', 'csv');

				// Open zip file
				if($zip->open($zip_file_name, ZipArchive::CREATE) !== true) { throw new Exception(__('Unable to create zip file', 'ws-form')); }

				// Add CSV file to zip
				$zip->addFile($csv_file_name, $csv_file_name_base);

				// Close zip file
				$zip->close();

				// Delete old CSV file
				unlink($csv_file_name);

				// Set CSV file name to the ZIP file
				$csv_file_name = $zip_file_name;

				// Build file name
				$http_file_name = WS_Form_Common::filename_datestamp('ws-form-submit', 'zip');

				// HTTP headers
				WS_Form_Common::file_download_headers($http_file_name, 'application/zip');

			} else {

				// Build file name
				$http_file_name = WS_Form_Common::filename_datestamp('ws-form-submit', 'csv');

				// HTTP headers
				WS_Form_Common::file_download_headers($http_file_name, 'text/csv');
			}

			// Clear output buffer
			if(ob_get_length()) { ob_clean(); }

			// Read and output the CSV file
			readfile($csv_file_name);

			// Delete temporary file
			unlink($csv_file_name);

			exit;
		}

		// Get CSV file name
		public function api_get_csv_file_name($hash, $form_id = 0) {

			// Check hash
			if(!WS_Form_Common::check_submit_hash($hash)) { throw new Exception(__('Invalid CSV hash', 'ws-form')); }

			// Get submit export directory
			$submit_export_dir = WS_Form_Common::upload_dir_create(WS_FORM_SUBMIT_EXPORT_TMP_DIR);
			if($submit_export_dir['error']) {

				parent::db_throw_error($submit_export_dir['error']);
			}
			$submit_export_dir = $submit_export_dir['dir'];

			// Get CSV file name
			return sprintf('%s/%s.csv', $submit_export_dir, $hash);
		}
	}