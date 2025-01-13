<?php

	class WS_Form_View extends WS_Form_Core {

		public $table_name;
		public $table_name_meta;

		public $type;
		public $form_id;
		public $field_id;
		public $value;
		public $status;

		public $decimals;
		public $thousand_separator;
		public $decimal_separator;

		public $count_submit = 0;
		public $count_save = 0;
		public $count_view = 0;

		public function __construct() {

			global $wpdb;

			$this->table_name = sprintf('%s%ssubmit', $wpdb->prefix, WS_FORM_DB_TABLE_PREFIX);
			$this->table_name_meta = sprintf('%s_meta', $this->table_name);

			// Register shortcodes
			add_shortcode('ws_form_stat', array($this, 'shortcode'));
		}

		public function shortcode($atts) {

			try {

				self::process_atts($atts);

				return self::get_return($this->type);

			} catch (Exception $e) {

				return $e->getMessage();
			}
		}

		public function reset() {

			$this->type = false;
			$this->form_id = false;
			$this->field_id = false;
			$this->value = false;
			$this->status = 'publish';

			$this->decimals = 0;
			$this->thousand_separator = ',';
			$this->decimal_separator = '.';
		}

		public function process_atts($atts) {

			// Reset
			self::reset();

			// Get type
			$this->type = isset($atts['type']) ? trim(strtolower($atts['type'])) : 'count_submit';

			// Check type
			if(!in_array($this->type, array(

				'percentage',
				'percent',
				'count',
				'sum',
				'average',

			))) {

				throw new Exception(__('Invalid type specified', 'ws-form'));
			}

			// Get form ID
			if(isset($atts['form_id'])) {

				$this->form_id = absint($atts['form_id']);
				if($this->form_id == 0) { throw new Exception(__('Invalid form ID specified', 'ws-form')); }
			}

			// Get field ID
			if(isset($atts['field_id'])) {

				$this->field_id = absint($atts['field_id']);
				if($this->field_id == 0) { throw new Exception(__('Invalid field ID specified', 'ws-form')); }
			}

			// Get decimals
			if(isset($atts['decimals'])) {

				$this->decimals = absint($atts['decimals']);
			}

			// Get thousand separator
			if(isset($atts['thousand_separator'])) {

				$this->thousand_separator = $atts['thousand_separator'];
			}

			// Get decimal separator
			if(isset($atts['decimal_separator'])) {

				$this->decimal_separator = $atts['decimal_separator'];
			}

			// Get decimal separator
			if(
				isset($atts['status']) &&
				in_array($atts['status'], array(

					'draft',
					'publish',
					'all'
				))
			) {

				$this->status = $atts['status'];
			}

			// Get value
			if(isset($atts['value'])) {

				$this->value = trim($atts['value']);
			}
		}

		public function percentage($atts) {

			$sql = $wpdb->prepare(

				"SELECT COUNT(id) FROM {$this->table_name} WHERE id = %d LIMIT 1;",
				$this->id
			);
		}

		public function get_formatted_number($count) {

			if(!is_numeric($count)) { $count = 0; }

			return number_format(

				$count,
				$this->decimals,
				$this->decimal_separator,
				$this->thousand_separator
			);
		}

		public function get_return($type) {

			global $wpdb;

			$count = 0;
			$average = 0;
			$total = 0;

			// WHERE SQL
			$where_sql_array = array();

			// Status
			if($this->status != 'all') {

				$where_sql_array[] = "{$this->table_name}.status = 'publish'";
			}
			
			$where_sql = (count($where_sql_array) > 0) ? ' AND ' . implode(' AND ', $where_sql_array) : '';

			// Check form ID
			if($this->form_id !== false) {

				// Get total submission records
				$sql = $wpdb->prepare(

					"SELECT COUNT(id) FROM {$this->table_name} WHERE {$this->table_name}.form_id = %d$where_sql LIMIT 1;",

					$this->form_id
				);

				$count = $wpdb->get_var($sql);

				if($wpdb->last_error) {

					throw new Exception($wpdb->last_error);
				}

				$total = $count;
			}

			// Check field ID
			if($this->field_id !== false) {

				switch($type) {

					case 'count' :
					case 'percentage' :
					case 'percent' :

						// Get total submission records
						$sql = $wpdb->prepare(

							"SELECT COUNT({$this->table_name_meta}.id) AS meta_value_count, {$this->table_name_meta}.meta_value FROM {$this->table_name_meta} RIGHT JOIN {$this->table_name} ON {$this->table_name_meta}.parent_id = {$this->table_name}.id WHERE {$this->table_name_meta}.field_id = %d$where_sql GROUP BY meta_value;",

							$this->field_id
						);

						$results = $wpdb->get_results($sql);

						if($wpdb->last_error) {

							throw new Exception($wpdb->last_error);
						}

						foreach($results as $result) {

							$meta_value = maybe_unserialize($result->meta_value);
							$meta_value_count = absint($result->meta_value_count);

							if($this->value !== false) {

								if(is_array($meta_value)) {

									// Skip nested arrays and objects
									if(
										isset($meta_value[0]) &&
										(is_array($meta_value[0]) || is_object($meta_value[0]))
									) {
										continue;
									}

									// Trim all values
									$meta_value = array_map('trim', $meta_value);

									// Check for matches
									if(in_array($this->value, $meta_value)) {

										$count += $meta_value_count;
									}
								}

								if(
									(is_string($meta_value) || is_numeric($meta_value)) &&
									($this->value === strval($meta_value))
								) {

									$count += $meta_value_count;
								}

							} else {

								$count += $meta_value_count;
							}

							$total += $meta_value_count;
						}

						break;

					case 'sum' :

						// Get sum
						$sql = $wpdb->prepare(

							"SELECT SUM({$this->table_name_meta}.meta_value) FROM {$this->table_name_meta} RIGHT JOIN {$this->table_name} ON {$this->table_name_meta}.parent_id = {$this->table_name}.id WHERE {$this->table_name_meta}.field_id = %d$where_sql;",

							$this->field_id
						);

						$sum = $wpdb->get_var($sql);

						if($wpdb->last_error) {

							throw new Exception($wpdb->last_error);
						}

						break;

					case 'average' :

						// Get average
						$sql = $wpdb->prepare(

							"SELECT AVG({$this->table_name_meta}.meta_value) FROM {$this->table_name_meta} RIGHT JOIN {$this->table_name} ON {$this->table_name_meta}.parent_id = {$this->table_name}.id WHERE {$this->table_name_meta}.field_id = %d$where_sql;",

							$this->field_id
						);

						$average = $wpdb->get_var($sql);

						if($wpdb->last_error) {

							throw new Exception($wpdb->last_error);
						}

						break;
				}
			}

			switch($type) {

				case 'count' :

					return self::get_formatted_number($count);

				case 'percent' :
				case 'percentage' :

					return self::get_formatted_number(($total > 0) ? (($count / $total) * 100) : 0) . '%';

				case 'sum' :

					return self::get_formatted_number($sum);

				case 'average' :

					return self::get_formatted_number($average);
			}

			return 0;
		}
	}

	new WS_Form_View();