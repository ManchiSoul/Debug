<?php

	class WS_Form_Cron {

		public function __construct() {

			// Register additional schedules
			self::cron_schedules();
		}

		// Schedule - Register additional schedules
		public function cron_schedules() {

			add_filter('cron_schedules', function($schedules) {

				$schedules['wsf_minute'] = array(

					'interval' => 60,
					'display' => esc_html__( 'Once Every Minute' ),
				);

				$schedules['wsf_quarter_hour'] = array(

					'interval' => 900,
					'display' => esc_html__( 'Once Every 15 Minutes' ),
				);

				$schedules['wsf_half_hour'] = array(

					'interval' => 1800,
					'display' => esc_html__( 'Once Every 30 Minutes' ),
				);

				$schedules['wsf_hour'] = array(

					'interval' => 3600,
					'display' => esc_html__( 'Once Every Hour' ),
				);

				$schedules['wsf_twice_daily'] = array(

					'interval' => 43200,
					'display' => esc_html__( 'Once Every 12 Hours' ),
				);

				$schedules['wsf_daily'] = array(

					'interval' => 86400,
					'display' => esc_html__( 'Once Daily' ),
				);

				$schedules['wsf_weekly'] = array(

					'interval' => 604800,
					'display' => esc_html__( 'Once Weekly' ),
				);

				$schedules['wsf_monthly'] = array(

					'interval' => 2635200,	// Not precisely a month
					'display' => esc_html__( 'Once Monthly' ),
				);


				return $schedules;
			});
		}
	}

	new WS_Form_Cron();
