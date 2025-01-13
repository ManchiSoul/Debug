<?php

	// All stats are stored in UTC
	class WS_Form_Form_Stat extends WS_Form_Core {

		public $id;
		public $form_id;
		public $table_name;
		public $date_ranges;

		public $counts_cache = false;

		public function __construct() {

			global $wpdb;

			$this->table_name = sprintf('%s%sform_stat', $wpdb->prefix, WS_FORM_DB_TABLE_PREFIX);

			// Form stat check
			add_filter('wsf_form_stat_check', array($this, 'form_stat_check'), 10, 1);

			// Form statistics report initialize
			add_action('wsf_settings_update', array($this, 'report_form_statistics_schedule'));
		}

		// Add form view
		public function db_add_view() {

			self::db_check_form_id();

			return self::db_add_count('view');
		}

		// Add form save
		public function db_add_save() {

			self::db_check_form_id();

			return self::db_add_count('save');
		}

		// Add form submit
		public function db_add_submit() {

			self::db_check_form_id();

			return self::db_add_count('submit');
		}

		// Add count
		public function db_add_count($type) {

			self::db_check_form_id();

			if(!apply_filters('wsf_form_stat_add_count', true)) { return true; };

			global $wpdb;

			$time_bounds = self::db_get_time_bounds();

			// Get existing record
			$sql = $wpdb->prepare(

				"SELECT id, count_view, count_save, count_submit FROM {$this->table_name} WHERE form_id = %d AND date_added >= %s AND date_added < %s LIMIT 1;",
				$this->form_id,
				date('Y-m-d H:i:s', $time_bounds['start']),
				date('Y-m-d H:i:s', $time_bounds['finish'])
			);

			$row = $wpdb->get_row($sql);
			if(is_null($row)) {

				// Build SQL - New record
				switch($type) {

					case 'view' :
					case 'save' :
					case 'submit' :

						$sql = $wpdb->prepare(

							"INSERT INTO {$this->table_name} (date_added, form_id, count_$type) VALUES (%s, %d, 1);",
							WS_Form_Common::get_mysql_date(),
							$this->form_id
						);

						break;

					default :

						$sql = false;
				}

				// Create record
				if($sql !== false) {

					$rows_inserted = $wpdb->query($sql);

					if($rows_inserted == 0) { parent::db_throw_error(__('Unable to insert stats record.', 'ws-form')); }
					if($rows_inserted === false) { parent::db_wpdb_handle_error(__('Stats record insert failed.', 'ws-form')); }

					$this->id = $wpdb->insert_id;

				} else {

					return false;
				}

			} else {

				// Build SQL - Existing record
				$this->id = $row->id;
				
				switch($type) {

					case 'view' :
					case 'save' :
					case 'submit' :

						$sql = $wpdb->prepare(

							"UPDATE {$this->table_name} SET count_$type = (count_$type + 1) WHERE id = %d LIMIT 1",
							$this->id
						);

						break;

					default :

						$sql = false;
				}

				// Update record
				if($sql !== false) {

					$rows_updated = $wpdb->query($sql);

					if($rows_updated == 0) { parent::db_throw_error(__('Stats record not found.', 'ws-form')); }
					if($rows_updated === false) { parent::db_wpdb_handle_error(__('Stats record update failed.', 'ws-form')); }

				} else {

					parent::db_throw_error(__('Invalid stats count type.', 'ws-form'));
				}
			}

			return true;
		}

		// Delete stats records
		public function db_delete() {

			self::db_check_form_id();

			global $wpdb;

			// Delete
			$sql = $wpdb->prepare(

				"DELETE FROM {$this->table_name} WHERE form_id = %d;",
				$this->form_id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error deleting stats', 'ws-form')); }

			return true;
		}

		// Get counts
		public function db_get_counts() {

			self::db_check_form_id();

			global $wpdb;

			// Get totals
			$sql = $wpdb->prepare(

				"SELECT SUM(count_view) AS count_view_total, SUM(count_save) AS count_save_total, SUM(count_submit) AS count_submit_total FROM {$this->table_name} WHERE form_id = %d;",
				$this->form_id
			);

			$row = $wpdb->get_row($sql);
			if(!is_null($row)) {

				$count_view_total = $row->count_view_total;
				$count_save_total = $row->count_save_total;
				$count_submit_total = $row->count_submit_total;

			} else {

				$count_view_total = 0;
				$count_save_total = 0;
				$count_submit_total = 0;
			}

			return array(

				'count_view' => $count_view_total,
				'count_save' => $count_save_total,
				'count_submit' => $count_submit_total
			);
		}

		// Get counts cached
		public function db_get_counts_cached() {

			self::db_check_form_id();

			if($this->counts_cache === false) {

				global $wpdb;

				// Build count cache
				$this->counts_cache = array();

				// Get counts for each form
				$sql = "SELECT form_id, SUM(count_view) AS count_view_total, SUM(count_save) AS count_save_total, SUM(count_submit) AS count_submit_total FROM {$this->table_name} GROUP BY form_id;";
				$rows = $wpdb->get_results($sql);

				if(is_null($rows)) {

					return array(

						'count_view' => 0,
						'count_save' => 0,
						'count_submit' => 0
					);
				}

				foreach($rows as $row) {

					$this->counts_cache[absint($row->form_id)] = array(

						'count_view' => absint($row->count_view_total),
						'count_save' => absint($row->count_save_total),
						'count_submit' => absint($row->count_submit_total)
					);
				}
			}

			return isset($this->counts_cache[$this->form_id]) ? $this->counts_cache[$this->form_id] : array(

				'count_view' => 0,
				'count_save' => 0,
				'count_submit' => 0
			);
		}

		// Get date data started collecting
		public function db_get_date_since() {

			self::db_check_form_id();

			global $wpdb;

			// Get totals
			$sql = $wpdb->prepare(

				"SELECT date_added FROM {$this->table_name} WHERE form_id = %d ORDER BY date_added LIMIT 1;",
				$this->form_id
			);

			$date_added = $wpdb->get_var($sql);

			$return_value = is_null($date_added) ? false : date_i18n(get_option('date_format'), strtotime(get_date_from_gmt($date_added)));

			return $return_value;
		}

		// Get time bounds
		public function db_get_time_bounds() {

			// Get local time midnight
			$local_date_midnight = WS_Form_Common::wp_version_at_least('5.3') ? wp_date('Y-m-d 00:00:00') : gmdate('Y-m-d 00:00:00', current_time('timestamp'));

			// Get UTC time
			$utc_of_local_date_midnight = get_gmt_from_date($local_date_midnight);

			// Start is local time midnight in UTC
			$date_time_local_start = strtotime($utc_of_local_date_midnight);

			// Finish is 24 hours ahead
			$date_time_local_finish = strtotime('+1 day', $date_time_local_start);

			return(array('start' => $date_time_local_start, 'finish' => $date_time_local_finish));
		}

		// Check form_id
		public function db_check_form_id() {

			if(absint($this->form_id) === 0) { parent::db_throw_error(__('Invalid form ID', 'ws-form')); }

			return true;
		}

		// Get chart data - By time
		public function db_get_chart_data_time($time_from_utc = false, $time_to_utc = false) {

			global $wpdb;

			$where_sql = '';
			$where_array = array();

			// Form ID
			if($this->form_id > 0) { $where_array[] = sprintf('form_id = %u', $this->form_id); }

			// Time from
			if($time_from_utc !== false) { $where_array[] = sprintf('date_added >= \'%s\'', gmdate('Y-m-d H:i:s', $time_from_utc)); }

			// Time to
			if($time_to_utc !== false) { $where_array[] = sprintf('date_added < \'%s\'', gmdate('Y-m-d H:i:s', $time_to_utc)); }

			// Build WHERE SQL
			if(count($where_array) > 0) {

				$where_sql = ' WHERE ' . implode(' AND ', $where_array);
			}

			// Get min and max date ranges
			$sql = sprintf(

				"SELECT MIN(date_added) AS date_added_from, MAX(date_added) AS date_added_to FROM {$this->table_name}%s ORDER BY date_added;",
				$where_sql
			);

			$date_range_row = $wpdb->get_row($sql);	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if(is_null($date_range_row)) { return false; }
			if(is_null($date_range_row->date_added_from)) { return false; }
			if(is_null($date_range_row->date_added_to)) { return false; }

			// Get from and to

			// If a from date is specified, the date start should be that date
			if($time_from_utc !== false) {

				$date_added_from = $time_from_utc;

			} else {

				$date_added_from = strtotime($date_range_row->date_added_from);
			}
			if($time_to_utc !== false) {

				$date_added_to = $time_to_utc;

			} else {

				$date_added_to = time();
			}

			// Get form stat data
			$sql = sprintf(

				"SELECT date_added, count_view, count_save, count_submit FROM {$this->table_name}%s ORDER BY date_added;",
				$where_sql
			);

			$form_stats = $wpdb->get_results($sql);
			if(is_null($form_stats)) { return false; }

			// Build form stat array
			$count_view_total = 0;
			$count_save_total = 0;
			$count_submit_total = 0;
			$form_stat_array = array();
			foreach($form_stats as $form_stat) {

				$date_added_local = get_date_from_gmt($form_stat->date_added, 'Y-m-d');
				if(isset($form_stat_array[$date_added_local])) {

					// Accumulate (This is intentionally coded this way to overcome a PHP ZEND_FETCH_DIM_RW bug)
					$stat_obj = $form_stat_array[$date_added_local];
					$stat_obj->count_view += $form_stat->count_view;
					$stat_obj->count_save += $form_stat->count_save;
					$stat_obj->count_submit += $form_stat->count_submit;
					$form_stat_array[$date_added_local] = $stat_obj;

				} else {

					// Initial
					$form_stat_array[$date_added_local] = $form_stat;
				}

				// Totals
				$count_view_total += $form_stat->count_view;
				$count_save_total += $form_stat->count_save;
				$count_submit_total += $form_stat->count_submit;
			}

			$date_added_from_local = get_date_from_gmt(gmdate('Y-m-d H:i:s', $date_added_from), 'Y-m-d');
			$date_added_to_local = get_date_from_gmt(gmdate('Y-m-d H:i:s', $date_added_to), 'Y-m-d');

			// Build final data
			$chart_data_labels = array();
			$chart_data_dataset_count_view = array();
			$day_index = 0;
			do {

				// Convert date in database to local time
				$date_added_current_local = gmdate('Y-m-d', strtotime($date_added_from_local) + ($day_index * 86400));

				// Add label
				$chart_data_labels[] = gmdate('M j', strtotime($date_added_current_local));

				// Build datasets
				if(isset($form_stat_array[$date_added_current_local])) {

					$form_stat = $form_stat_array[$date_added_current_local];
					$chart_data_dataset_count_view[] = $form_stat->count_view;
					$chart_data_dataset_count_save[] = $form_stat->count_save;
					$chart_data_dataset_count_submit[] = $form_stat->count_submit;

				} else {

					$chart_data_dataset_count_view[] = 0;
					$chart_data_dataset_count_save[] = 0;
					$chart_data_dataset_count_submit[] = 0;
				}

				$day_index++;

			} while($date_added_current_local != $date_added_to_local);

			// Build final data
			$chart_data = array(

				'labels' => $chart_data_labels,

				'datasets' => array(

					array(

						'label' 			=> sprintf('%s (%s)', __('Submissions', 'ws-form'), number_format($count_submit_total)),
						'borderColor' 		=> '#002E5F',
						'borderWidth' 		=> 2,
						'pointRadius' 		=> 2,
						'backgroundColor' 	=> 'rgba(0, 46, 95, 0.5)',
						'fill' 				=> true,
						'data' 				=> $chart_data_dataset_count_submit,
						'pointRadius'		=> 1,
						'pointHitRadius'	=> 5
					),

					array(

						'label' 			=> sprintf('%s (%s)', __('Saves', 'ws-form'), number_format($count_save_total)),
						'borderColor' 		=> '#2A9E1A',
						'borderWidth' 		=> 2,
						'pointRadius' 		=> 2,
						'backgroundColor' 	=> 'rgba(42, 158, 26, 0.25)',
						'fill' 				=> true,
						'data' 				=> $chart_data_dataset_count_save,
						'pointRadius'		=> 1,
						'pointHitRadius'	=> 5
					),

					array(

						'label' 			=> sprintf('%s (%s)', __('Views', 'ws-form'), number_format($count_view_total)),
						'borderColor' 		=> '#39D',
						'borderWidth' 		=> 2,
						'pointRadius' 		=> 2,
						'backgroundColor' 	=> 'rgba(51, 153, 221, 0.25)',
						'fill' 				=> true,
						'data' 				=> $chart_data_dataset_count_view,
						'pointRadius'		=> 1,
						'pointHitRadius'	=> 5
					)
				)
			);

			return $chart_data;
		}

		// Get chart data - By totals
		public function db_get_chart_data_total($time_from_utc = false, $time_to_utc = false) {

			global $wpdb;

			$where_sql = '';
			$where_array = array();

			// Form ID
			if($this->form_id > 0) { $where_array[] = sprintf('form_id = %u', $this->form_id); }

			// Time from
			if($time_from_utc !== false) { $where_array[] = sprintf('date_added >= \'%s\'', gmdate('Y-m-d H:i:s', $time_from_utc)); }

			// Time to
			if($time_to_utc !== false) { $where_array[] = sprintf('date_added < \'%s\'', gmdate('Y-m-d H:i:s', $time_to_utc)); }

			// Build WHERE SQL
			if(count($where_array) > 0) {

				$where_sql = ' WHERE ' . implode(' AND ', $where_array);
			}

			// Get form stat data
			$sql = sprintf(

				"SELECT SUM(count_view) AS count_view, SUM(count_save) AS count_save, SUM(count_submit) AS count_submit FROM {$this->table_name}$where_sql ORDER BY date_added;",
				$where_sql
			);

			$form_stats = $wpdb->get_row($sql);	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if(is_null($form_stats)) { return false; }

			// Build form stat array
			$count_view_total = $form_stats->count_view;
			$count_save_total = $form_stats->count_save;
			$count_submit_total = $form_stats->count_submit;

			if(
				($count_view_total == 0) &&
				($count_save_total == 0) &&
				($count_submit_total == 0)

			) { return false; }

			// Build final data
			$chart_data = array(

				'labels' => array(__('Total Counts', 'ws-form')),

				'datasets' => array(

					array(

						'label' 			=> sprintf('%s (%s)', __('Submissions', 'ws-form'), number_format($count_submit_total)),
						'borderColor' 		=> '#002E5F',
						'borderWidth' 		=> 2,
						'pointRadius' 		=> 2,
						'backgroundColor' 	=> 'rgba(0, 46, 95, 0.5)',
						'fill' 				=> true,
						'data' 				=> array($count_submit_total)
					),

					array(

						'label' 			=> sprintf('%s (%s)', __('Saves', 'ws-form'), number_format($count_save_total)),
						'borderColor' 		=> '#2A9E1A',
						'borderWidth' 		=> 2,
						'pointRadius' 		=> 2,
						'backgroundColor' 	=> 'rgba(42, 158, 26, 0.25)',
						'fill' 				=> true,
						'data' 				=> array($count_save_total)
					),

					array(

						'label' 			=> sprintf('%s (%s)', __('Views', 'ws-form'), number_format($count_view_total)),
						'borderColor' 		=> '#39D',
						'borderWidth' 		=> 2,
						'pointRadius' 		=> 2,
						'backgroundColor' 	=> 'rgba(51, 153, 221, 0.25)',
						'fill' 				=> true,
						'data' 				=> array($count_view_total)
					)
				)
			);

			return $chart_data;
		}

		// Check to see whether stat record should be created
		public function form_stat_check($return_value = true) {

			// Do not log if stats are disabled
			if(WS_Form_Common::option_get('disable_form_stats')) { return false; }

			// If we are not allowing admin stats, then do not log if superadmin, admin, author, editor, contributor
			if(
				!WS_Form_Common::option_get('admin_form_stats') &&
				WS_Form_Common::can_user('edit_posts')
			) {
				return false;
			}

			return $return_value;
		}

		public function report_form_statistics_send() {

			// Get options
			$form_published = WS_Form_Common::option_get('report_form_statistics_form_published', true);
			$frequency = WS_Form_Common::option_get('report_form_statistics_frequency', 'weekly');
			$email_to = WS_Form_Common::option_get('report_form_statistics_email_to', get_bloginfo('admin_email'));
			if(empty($email_to)) { $email_to = get_bloginfo('admin_email'); }
			$email_subject = WS_Form_Common::option_get('report_form_statistics_email_subject', __('WS Form - Form Statistics', 'ws-form'));
			if(empty($email_subject)) { $email_subject = __('WS Form - Form Statistics', 'ws-form'); }

			// Parse options
			$email_to = trim(WS_Form_Common::parse_variables_process($email_to, false, false, 'text/plain'));
			$email_subject = trim(WS_Form_Common::parse_variables_process($email_subject, false, false, 'text/plain'));

			// Split email addresses
			if(strpos($email_to, ' ') !== false) {

				$email_to_array = explode(' ', $email_to);

			} else {

				$email_to_array = explode(',', $email_to);
			}

			// Check options
			if(!in_array($frequency, array('daily', 'weekly', 'monthly'))) {

				parent::db_throw_error(__('Invalid frequency', 'ws-form'));
			}

			foreach($email_to_array as $email_to) {

				if(!filter_var($email_to, FILTER_VALIDATE_EMAIL)) {

					parent::db_throw_error(__('Invalid email address: %s', $email_to, 'ws-form'));
				}
			}

			if(empty($email_subject)) {

				parent::db_throw_error(__('Invalid email subject'));
			}

			// Set frequency specific variables
			switch($frequency) {

				case 'daily' :

					$email_title = __('Daily Form Statistics Report', 'ws-form');
					$offset_from = '-1 days';
					$offset_to = '-1 day';
					break;

				case 'weekly' :

					$email_title = __('Weekly Form Statistics Report', 'ws-form');
					$offset_from = '-7 days';
					$offset_to = '-1 day';
					break;

				case 'monthly' :

					$email_title = __('Monthly Form Statistics Report', 'ws-form');
					$offset_from = '-1 month';
					$offset_to = '-1 day';
					break;
			}

			// Build email message
			$email_message = sprintf(

				'<p><strong>%s:</strong> <a href="%s" target="_blank">%s</a></p>',
				__('Website', 'ws-form'),
				get_site_url(),
				esc_html(get_bloginfo('name'))
			);

			// Build date range
			$date_format = get_option('date_format');

			$email_message .= sprintf(

				'<p><strong>%s:</strong> %s to %s</p>',
				__('Date Range', 'ws-form'),
				self::get_utc_time_from($offset_from, $date_format, true),
				self::get_utc_time_to($offset_to, $date_format, true)
			);

			// Get data
			$report_form_statistics_data = self::report_form_statistics_get_data($form_published, $offset_from, $offset_to);

			$form_count = count($report_form_statistics_data['forms']);

			if($form_count == 0) {

				$email_message .= sprintf(

					'<p>%s</p>',
					($form_published ? __('There are no published forms available to process statistics for.', 'ws-form') : __('There are no forms available to process statistics for.', 'ws-form'))
				);

			} else {

				// Check for form saves
				$form_saves = $report_form_statistics_data['saves'];

				// Check if any saves exist, we'll simplify the table if no saves exists
				$email_message .= '<table class="table-report">';

				// Table heading
				$email_message .= '<thead>';
				$email_message .= sprintf(

					$form_saves ? '<tr><th>%1$s</th><th class="center">%2$s</th><th class="center">%3$s</th><th class="center">%4$s</th><th class="center">%5$s</th></tr>' : '<tr><th>%1$s</th><th class="center">%2$s</th><th class="center">%4$s</th><th class="center">%5$s</th></tr>',
					__('Form', 'ws-form'),
					__('Views', 'ws-form'),
					__('Saves', 'ws-form'),
					__('Submits', 'ws-form'),
					__('Conv.', 'ws-form')
				);
				$email_message .= '</thead>';

				// Totals
				$count_view_total = 0;
				$count_save_total = 0;
				$count_submit_total = 0;

				// Table body
				$email_message .= '<tbody>';
				foreach($report_form_statistics_data['forms'] as $form_data) {

					$count_view = $form_data['count_view_total'];
					$count_save = $form_data['count_save_total'];
					$count_submit = $form_data['count_submit_total'];

					$count_view_total += $count_view;
					$count_save_total += $count_save;
					$count_submit_total += $count_submit;

					// Calculate conversion rate
					$conversion_rate = ($count_view > 0) ? (($count_submit / $count_view) * 100) : 0;
					if($conversion_rate > 100) { $conversion_rate = 100; }

					$email_message .= sprintf(

						$form_saves ? '<tr><td>%1$s</td><td class="right">%2$s</td><td class="right">%3$s</td><td class="right">%4$s</td><td class="right">%5$s</td></tr>' : '<tr><td>%1$s</td><td class="right">%2$s</td><td class="right">%4$s</td><td class="right">%5$s</td></tr>',
						esc_html($form_data['label']),
						self::report_format_cell($count_view),
						self::report_format_cell($count_save),
						self::report_format_cell($count_submit),
						sprintf('%.1f%%', $conversion_rate)
					);
				}
				$email_message .= '</tbody>';

				// Calculate conversion rate
				$conversion_rate = ($count_view_total > 0) ? (($count_submit_total / $count_view_total) * 100) : 0;
				if($conversion_rate > 100) { $conversion_rate = 100; }

				// Table footer
				$email_message .= '<tfoot>';
				$email_message .= sprintf(

					$form_saves ? '<tr><th class="right">%1$s</th><th class="right">%2$s</th><th class="right">%3$s</th><th class="right">%4$s</th><th class="right">%5$s</th></tr>' : '<tr><th class="right">%1$s</th><th class="right">%2$s</th><th class="right">%4$s</th><th class="right">%5$s</th></tr>',
					__('Totals', 'ws-form'),
					self::report_format_cell($count_view_total),
					self::report_format_cell($count_save_total),
					self::report_format_cell($count_submit_total),
					sprintf('%.1f%%', $conversion_rate)
				);
				$email_message .= '</tfoot>';

				$email_message .= '</table>';
			}

			// Build email footer
			$email_footer = sprintf(

				'%s <a href="https://wsform.com/knowledgebase/report-form-statistics/">%s</a>',

				/* translators: %1$s = WS Form */
				sprintf(

					__('This email was sent from %s.', 'ws-form'),
					WS_FORM_NAME_PRESENTABLE
				),

				__('Learn more', 'ws-form')
			);

			// Get email template
			$email_template = file_get_contents(sprintf('%sincludes/templates/email/html/report.html', WS_FORM_PLUGIN_DIR_PATH));

			// Parse email template
			$mask_values = array(

				'email_subject' => htmlentities($email_subject),
				'email_title' => $email_title,
				'email_message' => $email_message,
				'email_footer' => $email_footer
			);

			$message = WS_Form_Common::mask_parse($email_template, $mask_values);

			// Build headers
			$headers = array(

				'Content-Type: text/html'
			);

			// Send email
			wp_mail($email_to_array, $email_subject, $message, $headers);

			return true;
		}

		// Get formatted cell
		public function report_format_cell($input) {

			if($input === 0) {

				return '-';

			} else {

				return absint($input);
			}
		}

		// Get counts for form statistics report
		public function report_form_statistics_get_data($form_published, $offset_from, $offset_to) {

			global $wpdb;

			$return_data = array('forms' => array(), 'saves' => false);

			// Build WHERE SQL
			$where_sql = sprintf('NOT status="trash"%s', ($form_published ? ' AND status="publish"' : ''));

			// Build order_by_sql
			$order_by_sql = 'label';

			// Initiate instance of Form class
			$ws_form_form = new WS_Form_Form();

			// Read all forms
			$forms = $ws_form_form->db_read_all('', $where_sql, $order_by_sql, '', '', false, true);

			// Process each form
			foreach($forms as $form) {

				// Set for mID
				$this->form_id = $form['id'];

				// Get data
				$data = self::report_form_statistics_get_data_process(

					self::get_utc_time_from($offset_from),
					self::get_utc_time_to($offset_to)
				);

				// Check for saves (used for formatting the table)
				if($data['count_save_total'] > 0) { $return_data['saves'] = true; }

				// Build return data
				$return_data['forms'][] = array(

					'id' => $form['id'],
					'label' => $form['label'],
					'count_view_total' => $data['count_view_total'],
					'count_save_total' => $data['count_save_total'],
					'count_submit_total' => $data['count_submit_total']
				);
			}

			return $return_data;
		}

		// Get report form statistics data - By time
		public function report_form_statistics_get_data_process($time_from_utc = false, $time_to_utc = false) {

			global $wpdb;

			$where_sql = '';
			$where_array = array();

			// Form ID
			if($this->form_id > 0) { $where_array[] = sprintf('form_id = %u', $this->form_id); }

			// Time from
			if($time_from_utc !== false) { $where_array[] = sprintf('date_added >= \'%s\'', gmdate('Y-m-d H:i:s', $time_from_utc)); }

			// Time to
			if($time_to_utc !== false) { $where_array[] = sprintf('date_added < \'%s\'', gmdate('Y-m-d H:i:s', $time_to_utc)); }

			// Build WHERE SQL
			if(count($where_array) > 0) {

				$where_sql = ' WHERE ' . implode(' AND ', $where_array);
			}

			// Get form stat data
			$sql = sprintf(

				"SELECT date_added, count_view, count_save, count_submit FROM {$this->table_name}%s;",
				$where_sql
			);

			$form_stats = $wpdb->get_results($sql);

			// Build form stat array
			$count_view_total = 0;
			$count_save_total = 0;
			$count_submit_total = 0;

			if(!is_null($form_stats)) {

				foreach($form_stats as $form_stat) {

					// Totals
					$count_view_total += $form_stat->count_view;
					$count_save_total += $form_stat->count_save;
					$count_submit_total += $form_stat->count_submit;
				}
			}

			return array(

				'count_view_total' => $count_view_total,
				'count_save_total' => $count_save_total,
				'count_submit_total' => $count_submit_total
			);
		}

		// Schedule form statistics report email
		public function report_form_statistics_schedule() {

			// Clear from report schedule (we need to do this in case the scheduling is changed)
			$ws_form_report_cron = new WS_Form_Report_Cron();
			$ws_form_report_cron->schedule_clear_all(WS_FORM_REPORT_ID_FORM_STATISTICS);

			// Get enabled
			$enabled = WS_Form_Common::option_get('report_form_statistics_enable', false);

			if($enabled) {

				// Get frequency
				$frequency = WS_Form_Common::option_get('report_form_statistics_frequency', 'weekly');

				switch($frequency) {

					case 'daily' :

						$offset = '+1 day';

						$recurrence = 'wsf_daily';

						break;

					case 'weekly' :

						$day_of_week = absint(WS_Form_Common::option_get('report_form_statistics_day_of_week', '0'));

						switch($day_of_week) {

							case 1 : $offset = 'next tuesday'; break;
							case 2 : $offset = 'next wednesday'; break;
							case 3 : $offset = 'next thursday'; break;
							case 4 : $offset = 'next friday'; break;
							case 5 : $offset = 'next saturday'; break;
							case 6 : $offset = 'next sunday'; break;
							default : $offset = 'next monday';
						}

						$recurrence = 'wsf_weekly';

						break;

					case 'monthly' :

						$offset = 'first day of next month';

						$recurrence = 'wsf_monthly';

						break;
				}

				// Get midnight time of offset
				$midnight_time_offset = gmdate('Y-m-d 00:00:00', strtotime($offset));

				// Get UTC time of offset
				$midnight_time_offset_utc = get_gmt_from_date($midnight_time_offset);

				// Get next run
				$next_run = strtotime($midnight_time_offset_utc . ' +6 hours');

				// Add to report schedule
				$ws_form_report_cron->schedule_add(WS_FORM_REPORT_ID_FORM_STATISTICS, $recurrence, $next_run);
			}
		}

		// Check to whether the add-view.php script can be used
		public function add_view_php_valid() {

			$add_view_php_error = false;
			$add_view_php_error_message = '';

			// Add view path -  See if we can get wp-load.php (e.g. FlyWheel hosting move wp-load.php for security)
			$wp_load_paths = apply_filters('wsf_form_stat_wp_load_paths', array(

				ABSPATH . '/wp-load.php',
				ABSPATH . '/.wordpress/wp-load.php'	// e.g. FlyWheel
			));

			$wp_load_success = false;

			foreach($wp_load_paths as $wp_load_path) {

				if(file_exists($wp_load_path)) {

					$wp_load_success = true;
					break;
				}
			}

			if(!$wp_load_success) {

				$add_view_php_error = true;
				$add_view_php_error_message = __('wp-load.php', 'ws-form');
			}

			if(!$add_view_php_error && is_admin() && function_exists('curl_version')) {

				// Create a cURL handle
				$curl_handle = curl_init(sprintf('%spublic/add-view.php', WS_FORM_PLUGIN_DIR_URL));

				// CURL options
				$curl_options = array(

					CURLOPT_RETURNTRANSFER	=> true,
					CURLOPT_HEADER			=> false,
					CURLOPT_USERAGENT		=> WS_Form_Common::get_request_user_agent(),
					CURLOPT_CONNECTTIMEOUT	=> 3,
					CURLOPT_TIMEOUT 		=> 3
				);
				curl_setopt_array($curl_handle, $curl_options);

				// Execute
				curl_exec($curl_handle);

				// Check if any error occurred
				if (!curl_errno($curl_handle)) {

					$add_view_url_success = true;

					$http_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

					if($http_code !== 200) {

						$add_view_php_error = true;
						$add_view_php_error_message = sprintf(__('HTTP Code %u', 'ws-form'), $http_code);
					}

				} else {

					$add_view_php_error = true;
					$add_view_php_error_message = __('Unreachable', 'ws-form');
				}

				// Close handle
				curl_close($curl_handle);
			}

			$return_array = array('error' => $add_view_php_error, 'error_message' => $add_view_php_error_message);

			return $return_array;
		}
		// Build date ranges
		public function date_ranges_init() {

			$this->date_ranges = array(

				'today'	=>	array(

					'label' 	=> __('Today', 'ws-form'),
					'time_from'	=> '0 days',
					'time_to'	=> '0 days',
					'type'		=> 'bar',
					'data'		=> 'total'
				),

				'yesterday'	=>	array(

					'label' 	=> __('Yesterday', 'ws-form'),
					'time_from'	=> '-1 days',
					'time_to'	=> '-1 days',
					'type'		=> 'bar',
					'data'		=> 'total'
				),

				'last_7_days'	=>	array(

					'label' 	=> __('Last 7 Days', 'ws-form'),
					'time_from'	=> '-7 days',
					'time_to'	=> '-1 day',
					'type'		=> 'line',
					'data'		=> 'time'
				),

				'last_30_days'	=>	array(

					'label' 	=> __('Last 30 Days', 'ws-form'),
					'time_from'	=> '-30 days',
					'time_to'	=> '-1 day',
					'type'		=> 'line',
					'data'		=> 'time'
				),

				'last_60_days'	=>	array(

					'label' 	=> __('Last 60 Days', 'ws-form'),
					'time_from'	=> '-60 days',
					'time_to'	=> '-1 day',
					'type'		=> 'line',
					'data'		=> 'time'
				),

				'last_90_days'	=>	array(

					'label' 	=> __('Last 90 Days', 'ws-form'),
					'time_from'	=> '-90 days',
					'time_to'	=> '-1 day',
					'type'		=> 'line',
					'data'		=> 'time'
				),

				'month_to_date'	=>	array(

					'label' 	=> __('Month To Date', 'ws-form'),
					'time_from'	=> 'first day of this month',
					'time_to'	=> false,
					'type'		=> 'line',
					'data'		=> 'time'
				),

				'last_month'	=>	array(

					'label' 	=> __('Last Month', 'ws-form'),
					'time_from'	=> 'first day of last month',
					'time_to'	=> 'last day of last month',
					'type'		=> 'line',
					'data'		=> 'time'
				),

				'year_to_date'	=>	array(

					'label' 	=> __('Year To Date', 'ws-form'),
					'time_from'	=> 'first day of january',
					'time_to'	=> false,
					'type'		=> 'line',
					'data'		=> 'time'
				),

				'last_year'	=>	array(

					'label' 	=> __('Last Year', 'ws-form'),
					'time_from'	=> 'first day of january last year',
					'time_to'	=> 'last day of december last year',
					'type'		=> 'line',
					'data'		=> 'time'
				)
			);
		}

		// Get UTC time from
		public function get_utc_time_from($offset, $format = 'Y-m-d H:i:s', $display = false) {

			// Get local time midnight today
			$time_from_local = wp_date('Y-m-d 00:00:00');

			// Get local time from
			$time_from_offset = strtotime($offset, strtotime($time_from_local));

			if($display) {

				return gmdate($format, $time_from_offset);

			} else {

				return strtotime(get_gmt_from_date(gmdate($format, $time_from_offset)));
			}
		}

		// Get GMT time to
		public function get_utc_time_to($offset, $format = 'Y-m-d H:i:s', $display = false) {

			// Get local time 23:59:59 today
			$time_to_local = wp_date('Y-m-d 23:59:59');

			// Get local time to
			$time_to_offset = strtotime($offset, strtotime($time_to_local));

			if($display) {

				return gmdate($format, $time_to_offset);

			} else {

				return strtotime(get_gmt_from_date(gmdate($format, $time_to_offset)));
			}
		}
	}

	new WS_Form_Form_Stat();
