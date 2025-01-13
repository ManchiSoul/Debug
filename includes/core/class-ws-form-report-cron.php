<?php

	class WS_Form_Report_Cron {

		public function __construct() {

			// Report cron processes
			add_action(WS_FORM_REPORT_SCHEDULE_HOOK, array($this, 'schedule_run'), 10, 2);
		}

		// Report cron processing
		public function schedule_run($report_id) {

			switch($report_id) {

				case WS_FORM_REPORT_ID_FORM_STATISTICS :

					// Send
					$ws_form_form_stat = new WS_Form_Form_Stat();
					$ws_form_form_stat->report_form_statistics_send();

					// If frequency is monthly, then we'll reschedue to ensure we hit monthly exactly (wp_cron doesn't support 1 month)
					$frequency = WS_Form_Common::option_get('report_form_statistics_frequency', 'weekly');

					if($frequency === 'monthly') {

						// Clear schedule
						self::schedule_clear_all($report_id);

						// Reschedule
						$ws_form_form_stat->report_form_statistics_schedule();
					}

					break;
			}
		}

		// Schedule - Add
		public function schedule_add($report_id, $recurrence, $next_run = false) {

			if($next_run === false) { $next_run = time(); }

			// Only add if recurrence valid
			$schedules = wp_get_schedules();
			if(!isset($schedules[$recurrence])) { return; }

			// Schedule args
			$args = array(

				'report_id' => $report_id
			);

			// Schedule event for data source
			wp_schedule_event($next_run, $recurrence, WS_FORM_REPORT_SCHEDULE_HOOK, $args);
		}

		// Schedule - Clear all for report
		public function schedule_clear_all($report_id = false) {

			$scheduled_events = _get_cron_array();

			// If there are no scheduled events, return
			if(empty($scheduled_events)) { return; }

			// Run through each scheduled event
			foreach($scheduled_events as $timestamp => $cron) {

				// If this is not a WS Form data source hook, skip it
				if(!isset($cron[WS_FORM_REPORT_SCHEDULE_HOOK])) { continue; }

				if($report_id !== false) {

					// Check the contents of the scheduled event
					foreach($cron[WS_FORM_REPORT_SCHEDULE_HOOK] as $cron_element_id => $cron_element) {

						if(!isset($cron_element['args'])) { continue 2; }
						if(!isset($cron_element['args']['report_id'])) { continue 2; }
						if($cron_element['args']['report_id'] != $report_id) { continue 2; }
					}
				}

				// Delete this scheduled event
				unset($scheduled_events[$timestamp][WS_FORM_REPORT_SCHEDULE_HOOK]);

				// If this time stamp is now empty, delete it in its entirety
				if(empty($scheduled_events[$timestamp])) {

					unset($scheduled_events[$timestamp]);
				}
			}

			// Save the scheduled events back
			_set_cron_array($scheduled_events);
		}

		// Deactivate
		public function deactivate() {

			self::schedule_clear_all();
		}
	}

	new WS_Form_Report_Cron();
