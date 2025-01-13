<?php

	class WS_Form_Style extends WS_Form_Core {

		public $id;
		public $label;
		public $user_id;
		public $date_added;
		public $date_updated;
		public $version;
		public $status;
		public $default;
		public $default_conv;
		public $checksum;
		public $published_checksum;
		public $meta;

		public $publish_auto = true;

		public $table_name;

		public $style_id_default = 0;
		public $style_id_conv_default = 0;
		public $style_ids_alt = array();

		const DB_INSERT = 'label,user_id,date_added,date_updated,version,`default`,default_conv';
		const DB_UPDATE = 'label,date_updated';
		const DB_SELECT = 'label,status,`default`,default_conv,checksum,published_checksum,id';

		public function __construct() {

			global $wpdb;

			$this->id = 0;
			$this->checksum = '';
			$this->label = __('New Style', 'ws-form');
			$this->meta = array();

			$this->table_name = sprintf('%s%sstyle', $wpdb->prefix, WS_FORM_DB_TABLE_PREFIX);
		}

		// Delete styles
		public function reset() {

			global $wpdb;

			$wpdb->query("DELETE FROM {$this->table_name};");
			$wpdb->query("DELETE FROM {$this->table_name}_meta;");
		}

		// Check style system has been initialized (Runs as public in case of plugin updates or cron process)
		public function check_initialized($bypass_user_capability_check = false, $use_legacy = false) {

			// Get styles
			$styles = self::db_read_all('', "NOT (status = 'trash')", '', '', '', true);

			// No styles
			if(
				!is_array($styles) ||
				(count($styles) == 0)
			) {
				// Build initial styles
				self::init($bypass_user_capability_check, $use_legacy);
			}
		}

		// Initialize styles system for first time
		public function init($bypass_user_capability_check = false, $use_legacy = false) {

			// Create first style
			$this->label = __('Standard - Light', 'ws-form');
			$style_id_default = self::db_create(true, false, $use_legacy, $bypass_user_capability_check);

			// Create conversational style
			$this->label = __('Conversational - Light', 'ws-form');
			$style_id_conv_default = self::db_create(false, true, $use_legacy, $bypass_user_capability_check);

			// Resolve form style IDs
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->db_style_resolve($bypass_user_capability_check);

			// Set style_id_default
			$this->style_id_default = $this->id = $style_id_default;
			$this->style_id_conv_default = $style_id_conv_default;

			return $this->id;
		}

		// Get form style ID
		public function get_style_id_from_form_object($form_object, $conversational = false) {

			// Get style ID
			$style_id = absint(WS_Form_Common::get_object_meta_value($form_object, ($conversational ? 'style_id_conv' : 'style_id'), 0));

			// Check for default style ID
			if(empty($style_id)) {

				$style_id = ($conversational ? self::get_style_id_conv_default() : self::get_style_id_default());
			}

			return $style_id;
		}

		// Get 
		public function get_style_id_options($conversational = false) {

			$style_id_options = array();

			// Get styles
			$styles = self::db_read_all('', " status = 'publish'", 'label ASC', '', '', false);

			// Get default style ID
			$style_id_default = ($conversational ? self::get_style_id_conv_default() : self::get_style_id_default());

			foreach($styles as $style) {

				$style_id = absint($style['id']);

				$style_id_options[] = array(
					'value' => (($style_id_default == $style_id) ? 0 : $style['id']),
					'text' => $style['label'] . (($style_id_default == $style_id) ? ' (' . __('Default', 'ws-form') . ')' : '')
				);
			}

			return $style_id_options;
		}

		// Get default style ID
		public function get_style_id_default() {

			if(!empty($this->style_id_default)) { return $this->style_id_default; }

			global $wpdb;

			// Get default style ID
			$this->style_id_default = absint($wpdb->get_var("SELECT id FROM {$this->table_name} WHERE `default` = 1 LIMIT 1;"));

			// If default style not found
			if(empty($this->style_id_default)) {

				$this->style_id_default = self::init();
			}

			// Check default style exists
			if(empty($this->style_id_default)) {

				parent::db_throw_error(__('Error obtaining default style', 'ws-form'));
			}

			return $this->style_id_default;
		}

		// Get default style ID conversational
		public function get_style_id_conv_default() {

			if(!empty($this->style_id_conv_default)) { return $this->style_id_conv_default; }

			global $wpdb;

			// Get default style ID
			$this->style_id_conv_default = absint($wpdb->get_var("SELECT id FROM {$this->table_name} WHERE `default_conv` = 1 LIMIT 1;"));

			// If default style not found
			if(empty($this->style_id_conv_default)) {

				$this->style_id_conv_default = self::init();
			}

			// Check default style exists
			if(empty($this->style_id_conv_default)) {

				parent::db_throw_error(__('Error obtaining default conversational style', 'ws-form'));
			}

			return $this->style_id_conv_default;
		}

		// Get all style IDs
		public function get_style_ids() {

			global $wpdb;

			// Get all style IDs
			return $wpdb->get_col("SELECT id FROM {$this->table_name} WHERE status = 'publish';");
		}

		// Get style preview
		public function get_preview_html($style_id) {

			// Build meta data
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'style';
			$ws_form_meta->parent_id = $style_id;

			$return_html = '<div class="wsf-styler-color-preview-wrapper">';

			// Meta keys to use for preview colors
			$meta_keys = array(

				'form_color_background' => __('Background', 'ws-form'),
				'form_color_base' => __('Base', 'ws-form'),
				'form_color_base_contrast' => __('Base - Contrast', 'ws-form'),
				'form_color_accent' => __('Accent', 'ws-form'),
				'form_color_neutral' => __('Neutral', 'ws-form'),
				'form_color_primary' => __('Primary', 'ws-form'),
				'form_color_secondary' => __('Secondary', 'ws-form'),
			);

			$return_html .= self::get_preview_html_group($ws_form_meta, $meta_keys, $style_id);

			// Check if alt exists
			$alt = ($ws_form_meta->db_read('alt') == 'on');

			if($alt) {

				$meta_keys = array(

					'form_color_background_alt' => __('Background (Alt)', 'ws-form'),
					'form_color_base_alt' => __('Base (Alt)', 'ws-form'),
					'form_color_base_contrast_alt' => __('Base - Contrast (Alt)', 'ws-form'),
					'form_color_accent_alt' => __('Accent (Alt)', 'ws-form'),
					'form_color_neutral_alt' => __('Neutral (Alt)', 'ws-form'),
					'form_color_primary_alt' => __('Primary (Alt)', 'ws-form'),
					'form_color_secondary_alt' => __('Secondary (Alt)', 'ws-form'),
				);

				$return_html .= self::get_preview_html_group($ws_form_meta, $meta_keys, $style_id);
			}

			return $return_html .= '</div>';
		}

		// Get style preview
		public function get_preview_html_group($ws_form_meta, $meta_keys, $style_id) {

			$return_html = '<div class="wsf-styler-color-preview-group">';

			foreach($meta_keys as $meta_key => $title) {

				$meta_value = $ws_form_meta->db_read($meta_key);

				$return_html .= sprintf(

					'<div class="wsf-styler-color-preview-background"><div class="wsf-styler-color-preview" style="background-color:%s;" title="%s"></div></div>',
					esc_attr($meta_value),
					esc_attr($title)
				);
			}

			return $return_html . '</div>';
		}

		// Create style
		public function db_create($default = false, $default_conv = false, $use_legacy = false, $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('create_form_style', $bypass_user_capability_check);

			global $wpdb;

			// Sanitize label
			self::sanitize_label(__('New Style', 'ws-form'));

			// Add style
			$sql = $wpdb->prepare(

				"INSERT INTO {$this->table_name} (" . self::DB_INSERT . ") VALUES (%s, %d, %s, %s, %s, %d, %d);",
				$this->label,
				get_current_user_id(),
				WS_Form_Common::get_mysql_date(),
				WS_Form_Common::get_mysql_date(),
				WS_FORM_VERSION,
				($default ? 1 : 0),
				($default_conv ? 1 : 0)
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error adding style', 'ws-form')); }

			// Get inserted ID
			$this->id = $wpdb->insert_id;

			// Build meta data object
			$meta_data_object = apply_filters('wsf_style_create_meta_data', self::get_default_meta_data_object($use_legacy, $default_conv));

			// Build meta data
			$form_meta = New WS_Form_Meta();
			$form_meta->object = 'style';
			$form_meta->parent_id = $this->id;
			$form_meta->db_update_from_object($meta_data_object, true, $bypass_user_capability_check);

			// Update checksum
			self::db_checksum($bypass_user_capability_check);

			// Publish
			if($this->publish_auto) {

				self::db_publish($bypass_user_capability_check);
			}

			// Run action
			do_action('wsf_style_create', $this);

			return $this->id;
		}

		public function db_create_from_template($id) {

			if(empty($id)) { return false; }

			// Create new style
			self::db_create(false);

			// Load template form data
			$ws_form_template = New WS_Form_Template();
			$ws_form_template->type = 'style';
			$ws_form_template->id = $id;
			$ws_form_template->read();
			$style_object = $ws_form_template->object;

			// Ensure form attributes are reset
			$style_object->status = 'draft';
			$style_object->default = 0;
			$style_object->default_conv = 0;
			$style_object->meta->template_id = $id;

			// Create form
			self::db_update_from_object($style_object, true, true);

			// Set checksum
			self::db_checksum();

			// Publish
			if($this->publish_auto) {

				self::db_publish();
			}

			return $this->id;
		}

		// Read record to array
		public function db_read($get_meta = true, $bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('read_form_style', $bypass_user_capability_check);

			global $wpdb;

			self::db_check_id();

			// Read form
			$sql = $wpdb->prepare(

				"SELECT " . self::DB_SELECT . " FROM {$this->table_name} WHERE id = %d AND NOT (status = 'trash') LIMIT 1;",
				$this->id
			);

			$style_array = $wpdb->get_row($sql, 'ARRAY_A');
			if(is_null($style_array)) { parent::db_wpdb_handle_error(__('Unable to read style', 'ws-form')); }

			// Set class variables
			foreach($style_array as $key => $value) {

				$this->{$key} = $value;
			}

			// Process meta data
			if($get_meta) {

				// Read meta
				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'style';
				$ws_form_meta->parent_id = $this->id;
				$metas = $ws_form_meta->db_read_all($bypass_user_capability_check);
				$style_array['meta'] = $this->meta = $metas;
			}

			// Convert into object
			return json_decode(wp_json_encode($style_array));
		}

		// Read - Published data
		public function db_read_published() {

			// No capabilities required, this is a public method

			global $wpdb;

			// Get contents of published field
			$sql = $wpdb->prepare(

				"SELECT checksum, published FROM {$this->table_name} WHERE id = %d AND NOT (status = 'trash') LIMIT 1;",
				$this->id
			);

			$published_row = $wpdb->get_row($sql);
			if(is_null($published_row)) { parent::db_wpdb_handle_error(__('Unable to read published style data', 'ws-form')); }

			// Read published JSON string
			$published_string = $published_row->published;

			// Empty published field (Never published)
			if($published_string == '') { return false; }

			// Inject latest checksum
			$style_object = json_decode($published_string);
			$style_object->checksum = $published_row->checksum;

			// Set label
			$this->label = $style_object->label;

			return $style_object;
		}

		// Set - Published
		public function db_publish($bypass_user_capability_check = false) {

			// User capability check
			WS_Form_Common::user_must('publish_form_style', $bypass_user_capability_check);

			global $wpdb;

			// Set style as published
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = 'publish', date_publish = %s, date_updated = %s WHERE id = %d LIMIT 1;",
				WS_Form_Common::get_mysql_date(),
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error publishing style', 'ws-form')); }

			// Read full style
			$style_object = self::db_read(true, $bypass_user_capability_check);

			// Update checksum
			self::db_checksum($bypass_user_capability_check);

			// Set checksums
			$style_object->checksum = $this->checksum;
			$style_object->published_checksum = $this->checksum;

			// Apply filters
			apply_filters('wsf_style_publish', $style_object);

			// JSON encode
			$style_json = wp_json_encode($style_object);

			// Publish style
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET published = %s, published_checksum = %s WHERE id = %d LIMIT 1;", 
				$style_json,
				$this->checksum,
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error publishing style', 'ws-form')); }

			// Do action
			do_action('wsf_style_publish', $style_object);
		}

		// Set label
		public function db_label($label) {

			// User capability check
			WS_Form_Common::user_must('edit_form_style');

			self::db_check_id();

			global $wpdb;

			if($label == '') { $label = __('Style', 'ws-form'); }

			// Set default 0 where default is 1
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET label = %s, date_updated = %s WHERE id = %d;",
				$label,
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting style label', 'ws-form')); }
		}

		// Set - Default
		public function db_default() {

			// User capability check
			WS_Form_Common::user_must('edit_form_style');

			self::db_check_id();

			global $wpdb;

			// Set default 0 where default is 1
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET `default` = 0, date_updated = %s WHERE `default` = 1;",
				WS_Form_Common::get_mysql_date()
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error resetting style as default', 'ws-form')); }

			// Set default 1
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET `default` = 1, date_updated = %s WHERE id = %d LIMIT 1;",
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting style as default', 'ws-form')); }

			// Update checksum
			self::db_checksum();

			// Set any form with style ID of $this->id to be 0
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->style_id_to_zero($this->id);

			// Set style_id_default
			$this->style_id_default = $this->id;
		}

		// Set - Default
		public function db_default_conv() {

			// User capability check
			WS_Form_Common::user_must('edit_form_style');

			self::db_check_id();

			global $wpdb;

			// Set default conversational 0 where default is 1
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET default_conv = 0, date_updated = %s WHERE default_conv = 1;",
				WS_Form_Common::get_mysql_date()
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error resetting style as default conversational', 'ws-form')); }

			// Set default conversational 1
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET default_conv = 1, date_updated = %s WHERE id = %d LIMIT 1;",
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting style as default conversational', 'ws-form')); }

			// Update checksum
			self::db_checksum();

			// Set any form with style ID of $this->id to be 0
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->style_id_to_zero($this->id);

			// Set style_id_default
			$this->style_id_default = $this->id;
		}

		// Set - Reset
		public function db_reset() {

			// User capability check
			WS_Form_Common::user_must('edit_form_style');

			self::db_check_id();

			// Check if style is conversational
			$style_id_conv_default = self::get_style_id_conv_default();

			// Is conversational?
			$conversational = ($this->id == $style_id_conv_default);

			// Initiate form meta object
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'style';
			$ws_form_meta->parent_id = $this->id;

			// Build meta
			$meta = apply_filters('wsf_style_create_meta_data', self::get_default_meta_data_object(false, $conversational));

			// Get source template ID
			$template_id = $ws_form_meta->db_get_object_meta('template_id', '');

			if(!empty($template_id)) {

				try {

					// Attempt to read template
					$ws_form_template = New WS_Form_Template();
					$ws_form_template->type = 'style';
					$ws_form_template->id = $template_id;
					$ws_form_template->read();

					// Merge template meta with base meta
					$meta = (object) array_merge((array) $meta, (array)$ws_form_template->object->meta);

				} catch(Exception $e) {}
			}

			// Build meta data
			$ws_form_meta->db_update_from_object($meta);

			// Update checksum
			self::db_checksum();

			// Publish
			if($this->publish_auto) {

				self::db_publish();
			}
		}

		// Set - Draft
		public function db_draft() {

			// User capability check
			WS_Form_Common::user_must('publish_form_style');

			self::db_check_id();

			global $wpdb;

			// Set style as draft
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = 'draft', date_publish = '', date_updated = %s, published = '', published_checksum = '' WHERE id = %d AND `default` = 0 LIMIT 1;",
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error drafting style', 'ws-form')); }

			// Update checksum
			self::db_checksum();
		}

		// Import reset
		public function db_import_reset() {

			// User capability check
			WS_Form_Common::user_must('import_form_style');

			self::db_check_id();

			global $wpdb;

			// Delete meta
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'style';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_delete_by_object();

			// Set style as draft
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = 'draft', date_publish = NULL, date_updated = %s, published = '', published_checksum = NULL, `default` = 0, default_conv = 0 WHERE id = %d LIMIT 1;",
				WS_Form_Common::get_mysql_date(),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error resetting form', 'ws-form')); }
		}

		// Read - Recent
		public function db_read_recent($limit = 10) {

			return self::db_read_all('', " NOT (status = 'trash')", 'date_updated DESC', $limit, '', false);
		}

		// Read - All
		public function db_read_all($join = '', $where = '', $order_by = '', $limit = '', $offset = '', $bypass_user_capability_check = false, $select = '') {

			// User capability check
			if(!$bypass_user_capability_check && !WS_Form_Common::can_user('read_form_style')) { return false; }

			global $wpdb;

			// Get style data
			if($select == '') { $select = self::DB_SELECT; }
			
			if($join != '') {

				$select_array = explode(',', $select);
				foreach($select_array as $key => $select) {

					$select_array[$key] = $this->table_name . '.' . $select;
				}
				$select = implode(',', $select_array);
			}

			$sql = "SELECT {$select} FROM {$this->table_name}";

			if($join != '') { $sql .= sprintf(" %s", $join); }
			if($where != '') { $sql .= sprintf(" WHERE %s", $where); }
			if($order_by != '') { $sql .= sprintf(" ORDER BY %s", $order_by); }
			if($limit != '') { $sql .= sprintf(" LIMIT %u", absint($limit)); }
			if($offset != '') { $sql .= sprintf(" OFFSET %u", absint($offset)); }

			$sql .= ';';

			return $wpdb->get_results($sql, 'ARRAY_A');	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		// Delete
		public function db_delete() {

			// User capability check
			WS_Form_Common::user_must('delete_form_style');

			global $wpdb;

			self::db_check_id();

			// Get status
			$sql = $wpdb->prepare(

				"SELECT status FROM {$this->table_name} WHERE id = %d AND `default` = 0 AND default_conv = 0;",
				$this->id
			);

			$status = $wpdb->get_var($sql);
			if(is_null($status)) { return false; }

			// If status is trashed, do a permanent delete of the data
			if($status == 'trash') {

				// Delete meta
				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'style';
				$ws_form_meta->parent_id = $this->id;
				$ws_form_meta->db_delete_by_object();

				// Delete style
				$sql = $wpdb->prepare(

					"DELETE FROM {$this->table_name} WHERE id = %d;",
					$this->id
				);
	
				if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error deleting style', 'ws-form')); }

				// Attempt to delete cached CSS files and options
				try {

					// Get file upload directory
					$upload_dir = WS_Form_Common::upload_dir_create(WS_FORM_CSS_FILE_PATH);

					// Get file upload directory
					$dir = $upload_dir['dir'];

					// Remove files
					unlink(sprintf('%s/public.style.%u.css', $dir, $this->id));
					unlink(sprintf('%s/public.style.%u.min.css', $dir, $this->id));

					// Remove options
					WS_Form_Common::option_remove(sprintf('css_public_style_%u', $this->id));
					WS_Form_Common::option_remove(sprintf('css_public_style_%u_min', $this->id));

				} catch(Exception $e) {}

				// Do action
				do_action('wsf_style_delete', $this->id);

			} else {

				// Set status to 'trash'
				self::db_set_status('trash');

				// Do action
				do_action('wsf_style_trash', $this->id);
			}

			// Change any forms that are using this style ID to use the default style ID
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->db_style_resolve();

			return true;
		}

		// Delete trashed styles
		public function db_trash_delete() {

			// Get all trashed styles
			$styles = self::db_read_all('', "status='trash'");

			foreach($styles as $style) {

				$this->id = $style['id'];
				self::db_delete();
			}

			// Change any forms that are using these style IDs to use the default style ID
			$ws_form_form = new WS_Form_Form();
			$ws_form_form->db_style_resolve();

			return true;
		}

		// Clone
		public function db_clone() {

			// User capability check
			WS_Form_Common::user_must('create_form_style');

			global $wpdb;

			// Read style data
			$style_object = self::db_read();

			// Clone form
			$sql = $wpdb->prepare(

				"INSERT INTO {$this->table_name} (" . self::DB_INSERT . ") VALUES (%s, %d, %s, %s, %s, 0, 0);",
				sprintf(__('%s (Copy)', 'ws-form'), $this->label),
				get_current_user_id(),
				WS_Form_Common::get_mysql_date(),
				WS_Form_Common::get_mysql_date(),
				WS_FORM_VERSION
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error cloning style', 'ws-form')); }

			// Get new form ID
			$this->id = $wpdb->insert_id;

			// Build form (As new)
			self::db_update_from_object($style_object, true, true);

			// Update checksum
			self::db_checksum();

			// Update form label
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET label =  '%s' WHERE id = %d;",
				sprintf(__('%s (Copy)', 'ws-form'), $this->label),
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error updating style label', 'ws-form')); }

			// Publish
			if($this->publish_auto) {

				self::db_publish();
			}

			return $this->id;
		}

		// Restore
		public function db_restore() {

			// User capability check
			WS_Form_Common::user_must('delete_form_style');

			// Draft
			self::db_draft();

			// Do action
			do_action('wsf_style_restore', $this->id);
		}

		// Set status of style
		public function db_set_status($status) {

			// User capability check
			WS_Form_Common::user_must('edit_form_style');

			global $wpdb;

			self::db_check_id();

			// Ensure provided form status is valid
			if(WS_Form_Common::check_style_status($status) == '') {

				parent::db_throw_error(sprintf(__('Invalid style status: %s', 'ws-form'), $status));
			}

			// Update style record
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET status = '%s' WHERE id = %d AND `default` = 0 LIMIT 1;",
				$status,
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting style status', 'ws-form')); }

			return true;
		}

		// Get style status name
		public function db_get_status_name($status) {

			switch($status) {

				case 'draft' : 		return __('Draft', 'ws-form'); break;
				case 'publish' : 	return __('Published', 'ws-form'); break;
				case 'trash' : 		return __('Trash', 'ws-form'); break;
				default :			return $status;
			}
		}

		// Get checksum of current style and store it to database
		public function db_checksum($bypass_user_capability_check = false) {

			global $wpdb;

			self::db_check_id();

			// Get style data
			$style_object = self::db_read(true, $bypass_user_capability_check);

			// Remove any variables that change each time checksum calculated or don't affect the public style
			unset($style_object->checksum);
			unset($style_object->published_checksum);

			// Serialize
			$style_serialized = serialize($style_object);

			// MD5
			$this->checksum = md5($style_serialized);

			// Update style record
			$sql = $wpdb->prepare(

				"UPDATE {$this->table_name} SET checksum = '%s' WHERE id = %d LIMIT 1;",
				$this->checksum,
				$this->id
			);

			if($wpdb->query($sql) === false) { parent::db_wpdb_handle_error(__('Error setting checksum', 'ws-form')); }

			return $this->checksum;
		}

		// Get style count by status
		public function db_get_count_by_status($status = '') {

			global $wpdb;

			$status = WS_Form_Common::check_style_status($status);

			if($status == '') {

				$sql = "SELECT COUNT(id) FROM {$this->table_name} WHERE NOT (status = 'trash')";

			} else {

				$sql = $wpdb->prepare(

					"SELECT COUNT(id) FROM {$this->table_name} WHERE status = %s",
					$status
				);
			}

			$style_count = $wpdb->get_var($sql);
			if(is_null($style_count)) { $style_count = 0; }

			return $style_count; 
		}

		// Push style array
		public function db_update_from_object($style_object, $new = false, $replace_meta = false) {

			// User capability check
			WS_Form_Common::user_must('edit_form_style');

			// Store old style ID
			$style_object_id_old = isset($style_object->id) ? $style_object->id : false;

			// Check for style ID in $style_object
			if(isset($style_object->id) && !$new) { $this->id = absint($style_object->id); }

			if(!$new) { self::db_check_id(); }

			// Update / Insert
			$this->id = parent::db_update_insert($this->table_name, self::DB_UPDATE, self::DB_INSERT, $style_object, 'style', $this->id, false);

			// Update meta
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'style';
			$ws_form_meta->parent_id = $this->id;
			$ws_form_meta->db_update_from_object($style_object->meta, false, false, $replace_meta);

			return $this->id;
		}

		public function get_default_meta_data_object($use_legacy = false, $use_conv = false) {

			$meta_data = array();

			// Get default meta data
			$meta_data_default = self::get_meta_data(0, false, $use_legacy, $use_conv, false);

			// Settings
			foreach($meta_data_default['meta_setting'] as $var) {

				$meta_data[$var['meta_key']] = $var['value'];
			}

			// Vars
			foreach($meta_data_default['meta'] as $var) {

				$meta_data[$var['meta_key']] = $var['value'];
			}

			// Vars - Alt
			foreach($meta_data_default['meta_alt'] as $var) {

				$meta_data[$var['meta_key']] = $var['value'];
			}

			return json_decode(wp_json_encode($meta_data));
		}

		// Get style label
		public function db_get_label() {

			// User capability check
			WS_Form_Common::user_must('read_form_style');

			return parent::db_object_get_label($this->table_name, $this->id);
		}

		// Check id
		public function db_check_id() {

			if(absint($this->id) === 0) { parent::db_throw_error(__('Invalid style ID', 'ws-form')); }
			return true;
		}

		// API - POST - Download - JSON
		public function db_download_json($published = false) {

			// User capability check
			!$published && WS_Form_Common::user_must('export_form_style');

			// Check style ID
			self::db_check_id();

			// Get style
			if($published) {

				$style_object = self::db_read_published();

			} else {

				$style_object = self::db_read();
			}

			// Clean style
			unset($style_object->checksum);
			unset($style_object->published_checksum);

			// Stamp style data
			$style_object->identifier = WS_FORM_IDENTIFIER;
			$style_object->version = WS_FORM_VERSION;
			$style_object->time = time();
			$style_object->status = 'draft';
			$style_object->meta->export_object = 'style';

			// Add checksum
			$style_object->checksum = md5(wp_json_encode($style_object));

			// Build filename
			$filename = 'wsf-style-' . strtolower($style_object->label) . '.json';

			// HTTP headers
			WS_Form_Common::file_download_headers($filename, 'application/json');

			// Output JSON
			WS_Form_Common::echo_wp_json_encode($style_object);
			
			exit;
		}

		// Get CSS vars as CSS
		public function has_alt() {

			self::db_check_id();

			global $wpdb;

			if(empty($this->style_ids_alt)) {

				$this->style_ids_alt = $wpdb->get_col("SELECT parent_id FROM {$this->table_name}_meta WHERE meta_key = 'alt' AND meta_value = 'on';");
			}

			return in_array($this->id, $this->style_ids_alt);
		}

		// Get CSS vars as CSS
		public function get_css_vars_markup($vars_markup = true, $vars_calc_markup = true, $node = false, $published = true, $alt_force = false, $bypass_user_capability_check = false) {

			self::db_check_id();

			// Check if style has alt enabled
			$has_alt = self::has_alt() || $alt_force;

			// Build node
			if($node === false) {

				$node = sprintf(

					':where([data-wsf-style-id="%u"])',
					$this->id
				);
			}

			// Get CSS vars array
			$meta_data = self::get_meta_data($this->id, $published, false, false, $bypass_user_capability_check);

			// Build return CSS
			$return_css = sprintf(

				"%s {\n",
				$node
			);

			// Vars
			if($vars_markup) {

				foreach($meta_data['meta'] as $css_var) {

					$return_css .= sprintf("%s: %s;\n", $css_var['var'], $css_var['value']);
				}

				if($has_alt) {

					foreach($meta_data['meta_alt'] as $css_var) {

						$return_css .= sprintf("%s: %s;\n", $css_var['var'], $css_var['value']);
					}
				}

				foreach($meta_data['meta_shade'] as $css_var) {

					$return_css .= sprintf("%s: %s;\n", $css_var['var'], $css_var['value']);
				}

				if($has_alt) {

					foreach($meta_data['meta_shade_alt'] as $css_var) {

						$return_css .= sprintf("%s: %s;\n", $css_var['var'], $css_var['value']);
					}
				}
			}

			// Vars calc
			if($vars_calc_markup) {

				usort($meta_data['meta_calc'], function ($item1, $item2) {

					return ($item1['var'] == $item2['var']) ? 0 : ($item1['var'] < $item2['var'] ? -1 : 1);
				});

				foreach($meta_data['meta_calc'] as $css_var_calc) {

					$return_css .= sprintf("%s: %s;\n", $css_var_calc['var'], $css_var_calc['value']);
				}

				if($has_alt) {

					usort($meta_data['meta_calc_alt'], function ($item1, $item2) {

						return ($item1['var'] == $item2['var']) ? 0 : ($item1['var'] < $item2['var'] ? -1 : 1);
					});

					foreach($meta_data['meta_calc_alt'] as $css_var_calc) {

						$return_css .= sprintf("%s: %s;\n", $css_var_calc['var'], $css_var_calc['value']);
					}
				}
			}

			$return_css .= "}\n";

			return $return_css;
		}

		// Get CSS vars as an array
		public function get_meta_data($style_id = 0, $published = true, $use_legacy = false, $use_conv = false, $bypass_user_capability_check = false, $children = false, $style_object = false, $label_prefix_array = array()) {

			$return_meta_data = $return_meta_data_alt = $return_meta_data_shade= $return_meta_data_shade_alt = $return_meta_data_calc = $return_meta_data_calc_alt = $return_meta_data_setting = array();

			// Read style config
			if($children === false) {

				// Get CSS vars from config
				$children = WS_Form_Config::get_styler()['meta'];
			}

			// Read style data
			if(
				($style_id !== 0) &&
				($style_object === false)
			) {
				if($published) {

					$style_object = self::db_read_published(true, $bypass_user_capability_check);

				} else {

					$style_object = self::db_read(true, $bypass_user_capability_check);
				}
			}

			// Get legacy defaults
			if($use_legacy) {

				// true forces conversational skin data to be returned
				$skins = WS_Form_Config::get_skins(true);

				$legacy_defaults = $use_conv ? $skins['ws_form_conv']['defaults'] : $skins['ws_form']['defaults'];

			}

			foreach($children as $id => $child) {

				// Process meta
				if(!empty($child['meta'])) {

					foreach($child['meta'] as $meta_key => $meta_config) {

						if(
							empty($meta_config['label']) ||
							empty($meta_config['type'])
						) {
							continue;
						}

						// Label
						$label = sprintf(

							'%s - %s',
							implode(' - ', array_merge($label_prefix_array, array($child['label']))),
							$meta_config['label']
						);

						// Process by type
						switch($meta_config['type']) {

							case 'calc' :

								// Get CSS var
								if(!empty($meta_config['default'])) {

									$return_meta_data_calc[] = array(

										'label' => $label,
										'type' => $meta_config['type'],
										'var' => $meta_config['var'],
										'value' => $meta_config['default']
									);
								}

								if(!empty($meta_config['default_alt'])) {

									$return_meta_data_calc_alt[] = array(

										'label' => sprintf('%s - %s', $label, __('Alt', 'ws-form')),
										'type' => $meta_config['type'],
										'var' => sprintf('%s-alt', $meta_config['var']),
										'value' => $meta_config['default_alt']
									);
								}

								break;

							default :

								// Values
								$meta_value = false;
								$meta_value_alt = false;

								// Setting
								$is_setting = isset($meta_config['setting']) && $meta_config['setting'];
								if($is_setting) {

									$css_var = '';

								} else {

									if(!isset($meta_config['var'])) { continue 2; }
									$css_var = $meta_config['var'];
								}

								// Is color?
								$is_color = ($meta_config['type'] === 'color');

								// Defaults
								$default = !empty($meta_config['default']) || $is_setting ? $meta_config['default'] : 'inherit';

								// Defaults - Conversational
								if($use_conv) {

									$default = !empty($meta_config['default_conv']) ? $meta_config['default_conv'] : $default;
								}

								// Get stored value
								if($style_id !== 0) {

									// Get stored value for style ID
									$meta_value = WS_Form_Common::get_object_meta_value($style_object, $meta_key, $default);
								}

								// Get legacy value
								if(
									$use_legacy &&
									isset($meta_config['legacy_v1_option_key'])
								) {

									// Get option key
									$legacy_v1_option_key = $meta_config['legacy_v1_option_key'];

									// Get legacy default
									$legacy_v1_default_key = str_replace('skin_', '', $legacy_v1_option_key);
									$legacy_v1_default_value = (isset($legacy_defaults[$legacy_v1_default_key]) ? $legacy_defaults[$legacy_v1_default_key] : '');

									// Change option key if requesting conversational data
									if($use_conv) {

										$legacy_v1_option_key = str_replace('skin_', 'skin_conv_', $legacy_v1_option_key);
									}

									// Get meta value
									$meta_value =  WS_Form_Common::option_get($legacy_v1_option_key, false);

									// Set to default if blank
									if($meta_value === '') {

										$meta_value = $legacy_v1_default_value;
									}

									// If legacy value doesn't exist, we'll use styler default
									if($meta_value === false) {

										$meta_value = '';
									}

									// Add suffix
									if($meta_value != '') {

										if(isset($meta_config['legacy_v1_suffix'])) {

											$meta_value .= $meta_config['legacy_v1_suffix'];
										}
									}
								}

								// Check value
								if($meta_value == '') {

									// Get default value
									$meta_value = $default;
								}

								// Normal
								$meta_data_single = array(

									'label' => $label,
									'type' => $meta_config['type'],
									'meta_key' => $meta_key,
									'value' => WS_Form_Common::sanitize_css_value($meta_value),
									'default' => WS_Form_Common::sanitize_css_value($default)
								);


								if(!empty($css_var)) {

									$meta_data_single['var'] = $css_var;
								}

								if($is_setting) {

									$return_meta_data_setting[] = $meta_data_single;

								} else {

									$return_meta_data[] = $meta_data_single;
								}

								// Process color
								if($is_color) {

									// If value requires a color shade, add the shade to calculations
//									$return_meta_data_shade = self::add_css_var_shade($return_meta_data_shade, $meta_value);
									$return_meta_data_shade = self::get_meta_data_shades($label, $return_meta_data_shade, $css_var, $meta_config, false);

									// Default alt auto?
									$default_alt_auto = isset($meta_config['default_alt_auto']) ? $meta_config['default_alt_auto'] : true;

									// Alt default
									$default_alt = !empty($meta_config['default_alt']) ? $meta_config['default_alt'] : ($default_alt_auto ? WS_Form_Color::color_to_color_alt(self::get_css_var_color_parsed($default)) : $meta_value);

									// Alt meta key
									$meta_key_alt = $meta_key . '_alt';

									// Get stored value
									if($style_id !== 0) {

										// Get stored value for style ID
										$meta_value_alt =trim(WS_Form_Common::get_object_meta_value($style_object, $meta_key_alt, $default_alt));
									}

									// Get legacy value
									if(
										$use_legacy &&
										isset($meta_config['legacy_v1_option_key_alt'])
									) {

										$meta_value_alt = WS_Form_Common::option_get($meta_config['legacy_v1_option_key_alt'], '');
									}

									// If value is still blank, use default value
									if($meta_value_alt == '') {

										// Get default value
										$meta_value_alt = $default_alt;
									}

									// Add alt var
									$meta_data_single = array(

										'label' => sprintf('%s - %s', $label, __('Alt', 'ws-form')),
										'type' => $meta_config['type'],
										'meta_key' => $meta_key_alt,
										'value' => WS_Form_Common::sanitize_css_value($meta_value_alt),
										'default' => WS_Form_Common::sanitize_css_value($default_alt)
									);

									$css_var_alt = sprintf('%s-alt', $css_var);

									if(!empty($css_var)) {

										$meta_data_single['var_src'] = $css_var;
										$meta_data_single['var'] = $css_var_alt;
									}

									// If value requires a color shade, add the shade to calculations
									$return_meta_data_shade_alt = self::get_meta_data_shades($label, $return_meta_data_shade_alt, $css_var, $meta_config, true);

									$return_meta_data_alt[] = $meta_data_single;
								}
						}
					}
				}

				// Process children
				if(!empty($child['children'])) {

					$get_meta_data_return = self::get_meta_data(

						$style_id,
						$published,
						$use_legacy,
						$use_conv,
						$bypass_user_capability_check,
						$child['children'],
						$style_object,
						array_merge($label_prefix_array, array($child['label']))
					);

					$return_meta_data = array_merge($return_meta_data, $get_meta_data_return['meta']);
					$return_meta_data_alt = array_merge($return_meta_data_alt, $get_meta_data_return['meta_alt']);
					$return_meta_data_shade = array_merge($return_meta_data_shade, $get_meta_data_return['meta_shade']);
					$return_meta_data_shade_alt = array_merge($return_meta_data_shade_alt, $get_meta_data_return['meta_shade_alt']);
					$return_meta_data_calc = array_merge($return_meta_data_calc, $get_meta_data_return['meta_calc']);
					$return_meta_data_calc_alt = array_merge($return_meta_data_calc_alt, $get_meta_data_return['meta_calc_alt']);
					$return_meta_data_setting = array_merge($return_meta_data_setting, $get_meta_data_return['meta_setting']);
				}
			}

			return array(

				'meta' => $return_meta_data,
				'meta_alt' => $return_meta_data_alt,
				'meta_shade' => $return_meta_data_shade,
				'meta_shade_alt' => $return_meta_data_shade_alt,
				'meta_calc' => $return_meta_data_calc,
				'meta_calc_alt' => $return_meta_data_calc_alt,
				'meta_setting' => $return_meta_data_setting
			);
		}

		// Parse CSS var color value
		public function get_css_var_color_parsed($css_var_value) {

			return $css_var_value;
		}

		// Get color shade 
		public function add_css_var_shade($return_vars_calc, $css_var_value) {

			// Process color shades
			if(!preg_match('/var\((.*)\)/', $css_var_value, $matches)) { return $return_vars_calc; }

			$css_var_color_shade = $matches[1];

			if(isset($return_vars_calc[$css_var_color_shade])) { return $return_vars_calc; }

			// Check if shade required
			$color_array = explode('-', $css_var_color_shade);

			if(count($color_array) < 3) { return $return_vars_calc; }

			$color_shade_amount = intval(array_pop($color_array));
			if($color_shade_amount === 0) { return $return_vars_calc; }

			$color_shade_method = array_pop($color_array);
			if(!in_array($color_shade_method, array('light', 'dark'))) { return $return_vars_calc; }

			$color_shade_var = implode('-', $color_array);

			// Add variables
			$return_vars_calc[$css_var_color_shade] = array(

				'var' => $css_var_color_shade,
				'value' => sprintf(

					'color-mix(in oklab, var(%s), %s %u%%)',
					$color_shade_var,
					(($color_shade_method == 'light') ? '#FFF' : '#000'),
					$color_shade_amount
				)
			);

			$return_vars_calc[$css_var_color_shade . '-alt'] = array(

				'var' => $css_var_color_shade . '-alt',
				'value' => sprintf(

					'color-mix(in oklab, var(%s-alt), %s %u%%)',
					$color_shade_var,
					(($color_shade_method == 'light') ? '#FFF' : '#000'),
					$color_shade_amount
				)
			);

			return $return_vars_calc;
		}

		// Get CSS var calculations for shades
		public function get_meta_data_shades($label, $return_vars_calc, $css_var, $var, $alt = false) {

			if(empty($var['shades'])) { return $return_vars_calc; }

			if(is_array($var['shades'])) {

				$shades = $var['shades'];

			} else {

				$shades = WS_Form_Color::get_shades();
			}

			foreach($shades as $suffix => $shade) {

				// Get mix
				if(empty($shade['mix'])) { continue; }
				$mix = $shade['mix'];

				// Get amount
				$amount = !empty($shade['amount']) ? absint($shade['amount']) : 0;

				// Add variables
				if($alt) {

					$return_vars_calc[] = array(

						'label' => sprintf('%s - %s', $label, $shade['label']),
						'type' => 'shade',
						'var' => sprintf('%s-%s-alt', $css_var, $suffix),
						'value' => sprintf(

							'color-mix(in srgb, var(%s), %s 50%%);',
							sprintf('%s-%s', $css_var, $suffix),
							(($mix == '#000') ? '#fff' : '#000')
						)
					);

				} else {

					$return_vars_calc[] = array(

						'label' => sprintf('%s - %s', $label, $shade['label']),
						'type' => 'shade',
						'var' => sprintf('%s-%s', $css_var, $suffix),
						'value' => sprintf(

							'color-mix(in oklab, var(%s), %s %u%%)',
							$css_var,
							(($mix == '#000') ? '#000' : '#fff'),
							$amount
						)
					);
				}
			}

			return $return_vars_calc;
		}

		public function get_svg($published = true) {

			self::db_check_id();

			try {

				if($published) {

					// Published
					$style_object = self::db_read_published();

				} else {

					// Draft
					$style_object = self::db_read(true, true);
				}

			} catch(Exception $e) { return false; }

			return self::get_svg_from_style_object($style_object, true);
		}

		// Get SVG shades
		public function get_svg_shades($title, $color) {

			$svg_shades = array();

			$shades = WS_Form_Color::get_shades_dark();

			foreach($shades as $suffix => $shade) {

				// Get mix
				if(empty($shade['mix'])) { continue; }
				$mix = $shade['mix'];

				// Get amount
				$amount = !empty($shade['amount']) ? absint($shade['amount']) : 0;

				$svg_shade_color = (WS_Form_Color::color_is_transparent($color) ? 'transparent' : sprintf(

					'color-mix(in oklab, %s, %s %u%%)',
					WS_Form_Common::esc_css($color),
					(($mix == '#000') ? '#000' : '#fff'),
					$amount
				));

				$svg_shades[] = array(

					'title' => $title,
					'color' => $svg_shade_color
				);
			}

			$svg_shades[] = array(

				'title' => $title,
				'color' => $color
			);

			$shades = WS_Form_Color::get_shades_light();

			foreach($shades as $suffix => $shade) {

				// Get mix
				if(empty($shade['mix'])) { continue; }
				$mix = $shade['mix'];

				// Get amount
				$amount = !empty($shade['amount']) ? absint($shade['amount']) : 0;

				$svg_shade_color = WS_Form_Color::color_is_transparent($color) ? 'transparent' : sprintf(

					'color-mix(in oklab, %s, %s %u%%)',
					WS_Form_Common::esc_css($color),
					(($mix == '#000') ? '#000' : '#fff'),
					$amount
				);

				$svg_shades[] = array(

					'title' => $title,
					'color' => $svg_shade_color
				);
			}

			return $svg_shades;
		}

		// Get SVG of form
		public function get_svg_from_style_object($style_object, $label = false, $svg_width = false, $svg_height = false) {

			// Check form object
			if(
				!is_object($style_object) ||
				!property_exists($style_object, 'meta')
			) {
				return '';
			}

			// Default width and height
			if($svg_width === false) { $svg_width = WS_FORM_TEMPLATE_SVG_WIDTH_FORM; }
			if($svg_height === false) { $svg_height = WS_FORM_TEMPLATE_SVG_HEIGHT_FORM; }

			// Get colors
			$form_color_background = WS_Form_Common::get_object_meta_value($style_object, 'form_color_background', 'transparent');
			$form_color_base = WS_Form_Common::get_object_meta_value($style_object, 'form_color_base', WS_Form_Color::get_color_base());

			// Build colors
			$colors = array(

				array(
					'title' => __('Back', 'ws-form'),
					'color' => $form_color_background
				),

				array(
					'title' => __('Base', 'ws-form'),
					'color' => $form_color_base
				),

				array(
					'title' => __('Cont', 'ws-form'),
					'color' => WS_Form_Common::get_object_meta_value($style_object, 'form_color_base_contrast', WS_Form_Color::get_color_base_contrast())
				),

				array(
					'title' => __('Acc', 'ws-form'),
					'color' => WS_Form_Common::get_object_meta_value($style_object, 'form_color_accent', WS_Form_Color::get_color_accent())
				),

				array(
					'title' => __('Neut', 'ws-form'),
					'color' => WS_Form_Common::get_object_meta_value($style_object, 'form_color_neutral', WS_Form_Color::get_color_neutral())
				),

				array(
					'title' => __('Pri', 'ws-form'),
					'color' => WS_Form_Common::get_object_meta_value($style_object, 'form_color_primary', WS_Form_Color::get_color_primary())
				),

				array(
					'title' => __('Sec', 'ws-form'),
					'color' => WS_Form_Common::get_object_meta_value($style_object, 'form_color_secondary', WS_Form_Color::get_color_secondary())
				),
			);

			// Build segments
			$segments = array();

			foreach($colors as $color) {

				$segments[] = self::get_svg_shades(

					$color['title'],
					$color['color']
				);
			}

			// Build SVG
			$svg = sprintf(
				'<svg xmlns="http://www.w3.org/2000/svg" class="wsf-responsive wsf-template-svg-style" viewBox="0 0 %u %u"><rect height="100%%" width="100%%" fill="' . esc_attr($form_color_background) . '"/></rect>',
				$svg_width,
				$svg_height
			);

			$svg .= '<text fill="' . esc_attr($form_color_base) . '" class="wsf-template-title"><tspan x="5" y="16">#label</tspan></text>';

			// Circles
			$svg .= '<g>';

			$width = $svg_width * 0.9;
			$x = ($svg_width / 2) - ($width / 2);
			$gap = 5;

			// Calculate the number of circles
			$circle_count = count($colors);

			// Total gap space between circles
			$gap_width = ($circle_count - 1) * $gap;

			// Remaining width for circles after accounting for gaps
			$circle_width = ($width - $gap_width) / $circle_count;

			// Radius of each circle
			$radius = $circle_width / 2;

			$y = $svg_height - $circle_width - ($radius / 2);

			// Loop through the colors and create discs
			$current_x = $x + $radius; // Start at the x position, offset by the radius
			foreach ($colors as $color) {

				$title = $color['title'];
				$fill = $color['color'];

				if(WS_Form_Color::color_is_transparent($fill)) {

					$fill = 'url(#wsf-styler-template-circle-check)';

				} else {

					$fill = esc_attr($fill);
				}

				$svg .= '<text fill="' . esc_attr($form_color_base) . '" class="wsf-template-color-caption"><tspan x="' . $current_x . '" y="' . ($y - 10) . '" text-anchor="middle" font-size="6px">' . esc_html($title) . '</tspan></text>';

				$svg .= '<circle cx="' . $current_x . '" cy="' . $y . '" r="' . $radius . '" fill="' . $fill . '" stroke="' . esc_attr($form_color_base) . '" stroke-width="1" title="' . esc_attr($title) . '"/>';
				$current_x += ($circle_width + $gap); // Move to the center of the next disc
			}

			$svg .= '</g>';

			// Shade wheel
			$x = $svg_width / 2;
			$y = ($svg_height / 2) - $radius;
			$radius = $svg_width * 0.4;
			$svg .= '<g>';

			// Calculate the number of segments and total angle
			$segment_count = count($segments);
			$angle_step = 360 / $segment_count;

			$current_angle = 0;

			foreach ($segments as $segment) {

				$inner_radius = 0;
				$shade_step = $radius / count($segment); // Calculate the step for each ring

				foreach ($segment as $shade) {

					$title = $shade['title'];
					$fill = $shade['color'];

					$outer_radius = $inner_radius + $shade_step;

					// Calculate start and end angles for the segment
					$start_angle = deg2rad($current_angle);
					$end_angle = deg2rad($current_angle + $angle_step);

					// Calculate arc coordinates for inner and outer radii
					$x1_inner = $x + $inner_radius * cos($start_angle);
					$y1_inner = $y + $inner_radius * sin($start_angle);
					$x2_inner = $x + $inner_radius * cos($end_angle);
					$y2_inner = $y + $inner_radius * sin($end_angle);

					$x1_outer = $x + $outer_radius * cos($start_angle);
					$y1_outer = $y + $outer_radius * sin($start_angle);
					$x2_outer = $x + $outer_radius * cos($end_angle);
					$y2_outer = $y + $outer_radius * sin($end_angle);

					// Determine if the arc is a large arc
					$large_arc_flag = ($angle_step > 180) ? 1 : 0;

					if(WS_Form_Color::color_is_transparent($fill)) {

						$fill = 'url(#wsf-styler-template-circle-check)';

					} else {

						$fill = esc_attr($fill);
					}

					// Add a path element for the concentric ring
					$svg .= '<path d="M' . $x1_inner . ',' . $y1_inner .
							' A' . $inner_radius . ',' . $inner_radius . ' 0 ' . $large_arc_flag . ',1 ' . $x2_inner . ',' . $y2_inner .
							' L' . $x2_outer . ',' . $y2_outer .
							' A' . $outer_radius . ',' . $outer_radius . ' 0 ' . $large_arc_flag . ',0 ' . $x1_outer . ',' . $y1_outer .
							' Z" fill="' . $fill . '" title="' . esc_attr($title) . '"/>';

					$inner_radius = $outer_radius; // Move to the next ring
				}

				$current_angle += $angle_step; // Move to the next segment
			}

			$svg .= '</g>';

			$svg .= '</svg>';

			return $svg;
		}

		public function styler_css_variables_get_header() {

			return array(

				'var',
				'label',
				'type',
				'default'
			);
		}

		public function styler_css_variables_get_rows($alt = false, $calc = false, $shade = false, $style_id = 0, $published = true, $use_legacy = false, $use_conv = false, $bypass_user_capability_check = false) {

			$rows = array();

			$ws_form_style = new WS_Form_Style();
			$meta_data = $ws_form_style->get_meta_data($style_id, $published, $use_legacy, $use_conv, $bypass_user_capability_check);

			// Process meta
			$rows = self::styler_css_variables_get_rows_process($meta_data['meta']);

			if($alt) {

				// Process meta alt
				$rows = array_merge($rows, self::styler_css_variables_get_rows_process($meta_data['meta_alt']));
			}

			if($calc) {

				// Process calc
				$rows = array_merge($rows, self::styler_css_variables_get_rows_sort(self::styler_css_variables_get_rows_process($meta_data['meta_calc'])));

				if($alt) {

					// Process calc alt
					$rows = array_merge($rows, self::styler_css_variables_get_rows_sort(self::styler_css_variables_get_rows_process($meta_data['meta_calc_alt'])));
				}
			}

			if($shade) {

				// Process shade
				$rows = array_merge($rows, self::styler_css_variables_get_rows_sort(self::styler_css_variables_get_rows_process($meta_data['meta_shade'])));

				if($alt) {

					// Process shade alt
					$rows = array_merge($rows, self::styler_css_variables_get_rows_sort(self::styler_css_variables_get_rows_process($meta_data['meta_shade_alt'])));
				}
			}

			return $rows;
		}

		public function styler_css_variables_get_rows_sort($rows) {

			usort($rows, function ($a, $b) {
				return strcmp($a['var'], $b['var']);
			});

			return $rows;
		}

		public function styler_css_variables_get_rows_process($metas) {

			$rows = array();

			foreach($metas as $meta) {

				$rows[] = array(

					'var' => $meta['var'],
					'label' => $meta['label'],
					'type' => $meta['type'],
					'default' => $meta['value']
				);
			}

			return $rows;
		}
	}
