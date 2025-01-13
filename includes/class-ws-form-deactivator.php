<?php

	/**
	 * Fired during plugin deactivation
	 */

	class WS_Form_Deactivator {

		public static function deactivate() {

			// Process action deactivations
			foreach(WS_Form_Action::$actions as $action) {

				if(method_exists($action, 'deactivate')) {

					$action->deactivate();
				}
			}

			// Delete cron jobs for reporting
			$ws_form_report_cron = new WS_Form_Report_Cron();
			$ws_form_report_cron->deactivate();
			// Delete cron jobs for data sources
			$ws_form_data_source_cron = new WS_Form_Data_Source_Cron();
			$ws_form_data_source_cron->deactivate();
		}
	}
