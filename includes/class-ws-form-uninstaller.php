<?php

	// Fired during plugin uninstall
	class WS_Form_Uninstaller {

		public static function uninstall() {

			global $wpdb;

			// Get uninstall settings
			$uninstall_options = WS_Form_Common::option_get('uninstall_options', false);
			$uninstall_database = WS_Form_Common::option_get('uninstall_database', false);

			// Delete options
			if($uninstall_options) {

				// Delete main options
				delete_option(WS_FORM_OPTION_NAME);
				delete_site_option(WS_FORM_OPTION_NAME);

				// Suppress / hide errors
				$wpdb->suppress_errors();
				$wpdb->hide_errors();

				// Find all custom option records (Excluding WooCommerce extension)
				$sql = sprintf("SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE '%s_%%' AND NOT (option_name = 'ws_form_wc_pfc')", WS_FORM_OPTION_NAME);
				$rows = $wpdb->get_results($sql);	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				foreach($rows as $row) {

					// Get option name
					$option_name = $row->option_name;

					// Delete option
					delete_option($option_name);
					delete_site_option($option_name);
				}

				// Delete submission hidden column meta
				$ws_form_form = New WS_Form_Form();
				$forms = $ws_form_form->db_read_all('', '', '', '', '', false);
				foreach($forms as $form) {

					delete_user_option(get_current_user_id(), sprintf('managews-form_page_ws-form-submitcolumnshidden-%u', $form['id']), !is_multisite());
				}
			}

			// Delete database tables
			if($uninstall_database) {

				// Drop WS Form tables
				global $wpdb;

				// Get table prefix
				$table_prefix = $wpdb->prefix . WS_FORM_DB_TABLE_PREFIX;

				// Tables to delete
				$tables = array('form', 'form_meta', 'form_stat', 'group', 'group_meta', 'section', 'section_meta', 'field', 'field_meta', 'submit', 'submit_meta', 'style', 'style_meta');

				// Run through each table and delete
				foreach($tables as $table_name) {

					$sql = sprintf("DROP TABLE IF EXISTS %s%s;", $table_prefix, $table_name);
					$wpdb->query($sql);	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				}
			}

			// Flush cache
			wp_cache_flush();
		}
	}
