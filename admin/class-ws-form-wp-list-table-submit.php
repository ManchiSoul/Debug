<?php

	class WS_Form_WP_List_Table_Submit extends WP_List_Table {

		public $form_id;

		public $date_from;
		public $date_to;
		public $keyword;

		public $submit_fields = false;
		public $field_data_cache = false;

		public $record_count = false;

		// Construct
	    public function __construct() {

			parent::__construct(array(

				'singular'		=> __('Submission', 'ws-form'),		// Singular label
				'plural'		=> __('Submissions', 'ws-form'),	// Plural label, also this well be one of the table css class
				'ajax'			=> false 							// We won't support Ajax for this table
			));

			// Set primary column
			add_filter('list_table_primary_column',[$this, 'list_table_primary_column'], 10, 2);

			// Get the form ID
			$this->form_id = absint(WS_Form_Common::get_query_var('id'));

			// Initialize submit fields
			$this->submit_fields = array();
			$this->field_data_cache = array();

			if($this->form_id > 0) {

				$ws_form_submit = new WS_Form_Submit;
				$ws_form_submit->form_id = $this->form_id;

				$submit_fields = $ws_form_submit->db_get_submit_fields();

				$ws_form_field = New WS_Form_Field();

				if($submit_fields !== false) {

					foreach($submit_fields as $id => $field) {

						$this->submit_fields[$id] = $field['label'];

						$ws_form_field->id = $id;
						$field_object = $ws_form_field->db_read(true);

						// Build cache of elements used in table
						$this->field_data_cache[$id] = (object) array(

							'rating_max' => WS_Form_Common::get_object_meta_value($field_object, 'rating_max'),
							'min' => WS_Form_Common::get_object_meta_value($field_object, 'min'),
							'max' => WS_Form_Common::get_object_meta_value($field_object, 'max')
						);
					}
				}
			}
	    }

	    // Get columns
		public function get_columns() {

			// Initial columns
  		  	$columns = [

				'cb'			=> '<input type="checkbox" />',
				'media'			=> '<div class="wsf-starred wsf-starred-header">' . WS_Form_Config::get_icon_16_svg('rating') . '</div>',
				'id'			=> __('ID', 'ws-form'),
				'status'		=> __('Status', 'ws-form'),
			];

			// Add form fields as columns (Only those that are saved on submit)
			foreach($this->submit_fields as $key => $label) {

				$columns[WS_FORM_FIELD_PREFIX . $key] = wp_strip_all_tags($label);
			}

			// Add date added
			$columns['date_updated']	= __('Date Updated', 'ws-form');
			$columns['date_added']		= __('Date Added', 'ws-form');

			return $columns;
		}

		// Get sortable columns
		public function get_sortable_columns() {

			$sortable_columns = array(

				'media'		=> array('starred', true),			// Used 'media' as opposed to 'starred' because WordPress considers that a special keyword and excludes it from the screen options column 
				'id'			=> array('id', true),
				'status'		=> array('status', true),
				'date_added'	=> array('date_added', true),
				'date_updated'	=> array('date_updated', true),
			);

			// Add form fields as sortable columns (Only those that are saved on submit)
			foreach($this->submit_fields as $key => $label) {

				$sortable_columns[WS_FORM_FIELD_PREFIX . $key] = array(WS_FORM_FIELD_PREFIX . $key, true);
			}

			return $sortable_columns;
		}

		// Column - Rating
		public function _column_media($item) {

			$starred_class = ($item->starred) ? ' wsf-starred-on' : '';

			$return_html = '<th scope="row" class="manage-column column-is_active"><div data-id="' . $item->id . '" data-action-ajax="wsf-submit-starred" class="wsf-starred' . $starred_class . '"'. WS_Form_Common::tooltip(__('Starred', 'ws-form'), 'top-center') . '>' . WS_Form_Config::get_icon_16_svg('rating') . '</div></th>';

			return $return_html;
		}

		// Add classes to single row
		public function single_row($item) {

			$class_array = array();
			if(isset($item->viewed) && !$item->viewed) { $class_array[] = 'wsf-submit-not-viewed'; }
			$class = implode(' ', $class_array);

			echo '<tr' . (($class != '') ? ' class="' . esc_attr($class) . '"' : '') . '>';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		// Column - Default
		public function column_default($submit, $column_name) {

			if(!isset($submit->meta[$column_name])) { return ''; }

			// Get field data
			$field = $submit->meta[$column_name];

			// Check field
			if(!is_array($field)) { return $field; }	// Plain text return
			if($field['value'] === '') { return ''; }

			// Get field ID
			$field_id = $field['id'];

			// Get field type
			$field_type = $field['type'];

			// Row delimiter
			$submit_delimiter_row = WS_FORM_SECTION_REPEATABLE_DELIMITER_SUBMIT;

			// Get section repeatable index
			$index = false;
			$delimiter_row = WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW;
			if(
				isset($submit->section_repeatable) &&
				isset($field['section_id'])
			) {

				$section_id = absint($field['section_id']);

				if(
					($section_id > 0) &&
					isset($submit->section_repeatable['section_' . $section_id])
				) {

					$index = isset($submit->section_repeatable['section_' . $section_id]['index']) ? $submit->section_repeatable['section_' . $section_id]['index'] : array();
					$delimiter_row = isset($submit->section_repeatable['section_' . $section_id]['delimiter_row']) ? $submit->section_repeatable['section_' . $section_id]['delimiter_row'] : WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW;
				}
			}

			// Get values_array
			if($index === false) {

				$values_array = array($field['value']);

			} else {

				$values_array = array();
				foreach($index as $index_single) {

					if(
						isset($submit->meta[$column_name . '_' . $index_single]) &&
						isset($submit->meta[$column_name . '_' . $index_single]['value'])
					) {
						$value = $submit->meta[$column_name . '_' . $index_single]['value'];
						if($value) { $values_array[] = $value; }
					}
				}
			}

			switch($field_type) {

				case 'signature' :
				case 'file' :

					$value = implode($submit_delimiter_row, array_map(function($file_objects) use ($submit, $field_id) {

						$files_html = '';

						if(is_array($file_objects)) {

							foreach($file_objects as $file_object_index => $file_object) {

								$files_html .= self::file_html($file_object);
							}
						}

						return $files_html;

					}, $values_array));

					break;

				// Just show stored value (already in correct format)
//				case 'datetime' :

//					$value = implode($submit_delimiter_row, array_map(function($datetime) use ($field) { return WS_Form_Common::get_date_by_type($datetime, $field); }, $values_array));
//					break;

				case 'googlemap' :

					$value = implode($submit_delimiter_row, array_map(function($googlemap) {

						if(
							is_array($googlemap) &&
							isset($googlemap['lat']) &&
							isset($googlemap['lng'])
						) {

							$value = sprintf('%.7f,%.7f', $googlemap['lat'], $googlemap['lng']);

							// Get lookup URL mask
							$latlon_lookup_url_mask = WS_Form_Common::option_get('latlon_lookup_url_mask');
							if(empty($latlon_lookup_url_mask)) { return $value; }

							// Get #value for mask
							$latlon_lookup_url_mask_values = array('value' => $value);

							// Build lookup URL
							$latlon_lookup_url = WS_Form_Common::mask_parse($latlon_lookup_url_mask, $latlon_lookup_url_mask_values);

							$value = '<a href="' . esc_attr($latlon_lookup_url) . '" target="_blank">' . esc_html($value) . '</a>';

						} else {

							$value = '';
						}

						return $value;

					}, $values_array));

					break;

				case 'tel' :

					$value = implode($submit_delimiter_row, array_map(function($tel) { return sprintf('<a href="tel:%s">%s</a>', esc_attr(WS_Form_Common::get_tel($tel)), esc_html($tel)); }, $values_array));
					break;

				case 'email' :

					$value = implode($submit_delimiter_row, array_map(function($email) { return sprintf('<a href="mailto:%1$s">%1$s</a>', esc_attr($email)); }, $values_array));
					break;

				case 'url' :

					$value = implode($submit_delimiter_row, array_map(function($url) { return sprintf('<a href="%1$s" target="_blank">%1$s</a>', esc_attr($url)); }, $values_array));
					break;

				case 'rating' :

					$rating_max = WS_Form_Common::get_object_meta_value($this->field_data_cache[$field_id], 'rating_max', 5);
					if(!is_numeric($rating_max)) { $rating_max = 5; }
					if($rating_max < 1) { $rating_max = 1; }

					$value = implode($submit_delimiter_row, array_map(function($rating) use ($rating_max) {

						if(($rating >= 0) && ($rating <= $rating_max)) {

							$value = '<ul class="wsf-submit-rating wsf-list-inline">';

							for($rating_index = 0; $rating_index < $rating_max; $rating_index++) {

								$rating_class = ($rating_index < $rating) ? ' class="wsf-submit-rating-on"' : '';

								$value .= '<li' . $rating_class . '>' . WS_Form_Config::get_icon_16_svg('rating') . '</li>';
							}

							$value .= '</ul>';

						} else {

							$value = $rating;
						}

						return $value;

					}, $values_array));

					break;

				case 'range' :

					$min = WS_Form_Common::get_object_meta_value($this->field_data_cache[$field_id], 'min', 0);
					if(!is_numeric($min)) { $min = 0; }
					$max = WS_Form_Common::get_object_meta_value($this->field_data_cache[$field_id], 'max', 100);
					if(!is_numeric($max)) { $max = 100; }

					$value = implode($submit_delimiter_row, array_map(function($range) use ($min, $max) {

						if($range >= 1 && (($max - $min) >= 1)) {

							$value = sprintf('<progress class="wsf-progress wsf-progress-small" min="%2$s" max="%3$s" value="%1$s"></progress><div class="wsf-helper">%1$s</div>', esc_attr($range), esc_attr($min), esc_attr($max));

						} else {

							$value = esc_html($range);
						}

						return $value;

					}, $values_array));

					break;

				case 'color' :

					$value = implode($submit_delimiter_row, array_map(function($color) { return sprintf('<span class="wsf-submit-color-sample" style="background:%1$s"></span><span class="wsf-submit-color">%1$s</span>', $color); }, $values_array));

					break;

				default :

					$value = implode($submit_delimiter_row, array_map(function($value) use ($delimiter_row) { 

						if(is_array($value)) {

							$value = array_map(function($value) {

								return is_string($value) ? esc_html($value) : $value;
							}, $value);
						}

						if(is_string($value)) {

							$value = esc_html($value);
						}

						// Check for array (e.g. Checkboxes, Selects)
						return is_array($value) ? implode($delimiter_row, $value) : $value;

					}, $values_array));
			}

			// Apply filter
			$value = apply_filters('wsf_table_submit_field_type_list', $value, $field_id, $field_type);

			// Check if value is still an array
			if(is_array($value)) { $value = implode(', ', $value); }

			return $value;
		}

		// File
		function file_html($file_object) {

			// Get URL
			if(!isset($file_object['url'])) { return ''; }
			$url = $file_object['url'];

			// Get name
			if(!isset($file_object['name'])) { return ''; }
			$name = $file_object['name'];

			// Get mime type
			if(!isset($file_object['type'])) { return ''; }
			$type = $file_object['type'];

			// Get file icon
			$file_types = WS_Form_Config::get_file_types();
			$icon = isset($file_types[$type]) ? $file_types[$type]['icon'] : $file_types['default']['icon'];

			// Download
			$return_html = sprintf('<a download="%1$s" href="%2$s" title="%1$s">%3$s</a>', esc_attr($name), esc_attr($url), WS_Form_Config::get_icon_16_svg($icon));

			return $return_html;
		}

		// Column - Checkbox
		function column_cb($item) {

			return sprintf('<input type="checkbox" name="bulk-ids[]" value="%u" />', $item->id);
		}

		// Column - ID
		function column_id($item) {

			// Get ID
			$id = absint($item->id);

			// Title
			$title = sprintf('<strong><a href="#%1$u" data-action="wsf-view" data-id="%1$u">%1$u</a></strong>', $item->id);

			// Actions
			$status = WS_Form_Common::get_query_var('ws-form-status');
			$actions = array();
			switch($status) {

				case 'trash' :

					// Restore / delete permanently
					if(WS_Form_Common::can_user('delete_submission')) {

						$actions['wsf-restore'] = sprintf('<a href="#" data-action="wsf-restore" data-id="%u">%s</a>', $id, __('Restore', 'ws-form'));
						$actions['wsf-delete'] = sprintf('<a href="#" data-action="wsf-delete" data-id="%u">%s</a>', $id, __('Delete Permanently', 'ws-form'));
					}
					break;

				case 'spam' :

					// Read
					if(WS_Form_Common::can_user('read_submission')) {

						$actions['wsf-view'] = sprintf('<a href="#%1$u" data-action="wsf-view" data-id="%1$u">%2$s</a>', $id, __('View', 'ws-form'));
					}

					// Edit
					if(WS_Form_Common::can_user('edit_submission')) {

						$actions['wsf-edit'] = sprintf('<a href="#%1$u" data-action="wsf-edit" data-id="%1$u">%2$s</a>', $id, __('Edit', 'ws-form'));
					}

					// Delete permanently
					if(WS_Form_Common::can_user('delete_submission')) {

						$actions['wsf-delete'] = sprintf('<a href="#" data-action="wsf-delete" data-id="%u">%s</a>', $id, __('Delete Permanently', 'ws-form'));
					}
					break;

				default :

					// Read
					if(WS_Form_Common::can_user('read_submission')) {

						$actions['wsf-view'] = sprintf('<a href="#%1$u" data-action="wsf-view" data-id="%1$u">%2$s</a>', $id, __('View', 'ws-form'));
					}

					// Edit
					if(WS_Form_Common::can_user('edit_submission')) {

						$actions['wsf-edit'] = sprintf('<a href="#%1$u" data-action="wsf-edit" data-id="%1$u">%2$s</a>', $id, __('Edit', 'ws-form'));
					}

					// Edit in Preview
					if(
						WS_Form_Common::option_get('submit_edit_in_preview', '') &&
						WS_Form_Common::can_user('edit_submission')
					) {

						$form_id = absint(WS_Form_Common::get_query_var('id'));

						$actions['wsf-edit-preview'] = sprintf(

							'<a href="%s" target="_blank">%s</a>',
							WS_Form_Common::get_preview_url($form_id, false, false, false, false, $item->hash, 'ws-form'),
							__('Edit in Preview', 'ws-form')
						);
					}
					// Mark as read / unread
					if(WS_Form_Common::can_user('edit_submission')) {

						$actions['wsf-viewed'] = sprintf('<a href="#" data-action-ajax="wsf-submit-viewed" data-id="%1$u">%2$s</a>', $id, ($item->viewed) ? __('Mark as Unread', 'ws-form') : __('Mark as Read', 'ws-form'));
					}

					// Delete
					if(WS_Form_Common::can_user('delete_submission')) {

						$actions['wsf-trash'] = sprintf('<a href="#" data-action="wsf-delete" data-id="%u">%s</a>', $id, __('Trash', 'ws-form'));
					}

					// Export CSV
					if(WS_Form_Common::can_user('export_submission')) {

						$actions['wsf-export'] = sprintf('<a href="#" data-action="wsf-export" data-id="%u">%s</a>', $id, __('Export CSV', 'ws-form'));
					}

					// Apply filter
					$actions = apply_filters('wsf_table_submit_column_actions', $actions, (array) $item, $status);
			}

			return $title . $this->row_actions($actions);
		}

		// Column - Status
		function column_status($item) {

			// Was this submit done in preview mode?
			$preview = isset($item->preview) ? $item->preview : false;

			// Spam level indicator
			$spam_level = isset($item->spam_level) ? $item->spam_level : null;
			$spam_level_indicator = is_null($spam_level) ? '' : '<span class="wsf-spam-level" style="background:' . WS_Form_Color::get_green_to_red_rgb($spam_level, 0, WS_FORM_SPAM_LEVEL_MAX) . '" title="' . sprintf(__('Spam level: %u%%', 'ws-form'), round($spam_level)) . '"></span>';

			// Build title
			$ws_form_submit = New WS_Form_Submit();
			$title = $spam_level_indicator . $ws_form_submit->db_get_status_name($item->status) . ($preview ? ' (' . __('Preview', 'ws-form') . ')' : '');

			return $title;
		}

		// Column - Date added
		function column_date_added($item) {

			$date_added = $item->date_added;

			$date_added = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($date_added)));

			return $date_added;
		}

		// Column - Date updated
		function column_date_updated($item) {

			$date_updated = $item->date_updated;

			$date_updated = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_date_from_gmt($date_updated)));

			return $date_updated;
		}

		// Views
		function get_views(){

			// Get data from API
			$ws_form_submit = New WS_Form_Submit();

			$views = array();
			$current = WS_Form_Common::get_query_var('ws-form-status', 'all');
			$all_url = remove_query_arg(array('ws-form-status', 'paged'));

			// All link
			$count_all = $ws_form_submit->db_get_count_by_status($this->form_id);
			if($count_all) {

				$views['all'] = sprintf(

					'<a href="%s"%s>%s <span class="count">%u</span></a>',
					esc_attr(add_query_arg('ws-form-status', 'all', $all_url)),
					($current === 'all' ? ' class="current"' :''),
					__('All', 'ws-form'),
					$count_all
				);
			}

			// Draft link
			$count_draft = $ws_form_submit->db_get_count_by_status($this->form_id, 'draft');
			if($count_draft) {

				$views['draft'] = sprintf(

					'<a href="%s"%s>%s <span class="count">%u</span></a>',
					esc_attr(add_query_arg('ws-form-status', 'draft', $all_url)),
					($current === 'draft' ? ' class="current"' :''),
					__('In Progress', 'ws-form'),
					$count_draft
				);
			}

			// Published link
			$count_publish = $ws_form_submit->db_get_count_by_status($this->form_id, 'publish');
			if($count_publish) {

				$views['publish'] = sprintf(

					'<a href="%s"%s>%s <span class="count">%u</span></a>',
					esc_attr(add_query_arg('ws-form-status', 'publish', $all_url)),
					($current === 'publish' ? ' class="current"' :''),
					__('Submitted', 'ws-form'),
					$count_publish
				);
			}

			// Spam link
			$count_spam = $ws_form_submit->db_get_count_by_status($this->form_id, 'spam');
			if($count_spam) {

				$views['spam'] = sprintf(

					'<a href="%s"%s>%s <span class="count">%u</span></a>',
					esc_attr(add_query_arg('ws-form-status', 'spam', $all_url)),
					($current === 'spam' ? ' class="current"' :''),
					__('Spam', 'ws-form'),
					$count_spam
				);
			}

			// Trashed link
			$count_trash = $ws_form_submit->db_get_count_by_status($this->form_id, 'trash');
			if($count_trash) {

				$views['trash'] = sprintf(

					'<a href="%s"%s>%s <span class="count">%u</span></a>',
					esc_attr(add_query_arg('ws-form-status', 'trash', $all_url)),
					($current === 'trash' ? ' class="current"' :''),
					__('Trash', 'ws-form'),
					$count_trash
				);
			}

			return $views;
		}

		// Get form count by status
		function form_count_by_status($status = '') {

			global $wpdb;

			$status = WS_Form_Common::check_submit_status($status);

			if($status == '') {

				$sql = "SELECT COUNT(id) FROM {$wpdb->prefix}wsf_form WHERE NOT(status = 'trash')";

			} else {

				$sql = $wpdb->prepare(

					"SELECT COUNT(id) FROM {$wpdb->prefix}wsf_form WHERE status = %s",
					$status
				);
			}

			$form_count = $wpdb->get_var($sql);
			if(is_null($form_count)) { $form_count = 0; }

			return $form_count; 
		}

		// Get data
		function get_data($per_page = 20, $page_number = 1) {

			// If form ID not set, return empty array
			if($this->form_id == 0) { return array(); }

			// Clear hidden fields?
			$clear_hidden_fields = (get_user_meta(get_current_user_id(), 'ws_form_submissions_clear_hidden_fields', true) === 'on');

			// Get data from core
			$ws_form_submit = New WS_Form_Submit();
			$ws_form_submit->form_id = $this->form_id;

			return $ws_form_submit->db_read_all(

				$ws_form_submit->get_search_join(),
				$ws_form_submit->get_search_where(),
				$ws_form_submit->get_search_group_by(),
				$ws_form_submit->get_search_order_by(),
				$per_page,									// Limit
				($page_number - 1) * $per_page,				// Offset
				true,										// Get meta
				true,										// Get expanded
				false,										// Bypass user capability check
				$clear_hidden_fields 						// Clear hidden fields
			);
		}

		// Prepare items
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();

			$per_page     = $this->get_items_per_page('ws_form_submissions_per_page', 20);
			$current_page = $this->get_pagenum();
			$total_items  = self::record_count();

			$this->set_pagination_args(array(

				'total_items' => $total_items, //WE have to calculate the total number of items
				'per_page'    => $per_page //WE have to determine how many items to show on a page
			));

			$this->items = self::get_data($per_page, $current_page);
		}

		// Bulk actions - Prepare
		public function get_bulk_actions() {

			$actions = array();
			$status = WS_Form_Common::get_query_var('ws-form-status');

			switch($status) {

				case 'trash' :

					// User capability check
					if(WS_Form_Common::can_user('delete_submission')) {

						$actions['wsf-bulk-restore'] = __('Restore', 'ws-form');
						$actions['wsf-bulk-delete'] = __('Delete Permanently', 'ws-form');
					}
					break;

				case 'spam' :

					// User capability check
					if(WS_Form_Common::can_user('edit_submission')) {

						$actions['wsf-bulk-not-spam'] = __('Mark as Not Spam', 'ws-form');
					}

					// User capability check
					if(WS_Form_Common::can_user('delete_submission')) {

						$actions['wsf-bulk-delete'] = __('Delete Permanently', 'ws-form');
					}

					break;

				default:

					// User capability check
					if(WS_Form_Common::can_user('edit_submission')) {

						$actions['wsf-bulk-read'] = __('Mark as Read', 'ws-form');
						$actions['wsf-bulk-not-read'] = __('Mark as Unread', 'ws-form');
						$actions['wsf-bulk-starred'] = __('Mark as Starred', 'ws-form');
						$actions['wsf-bulk-not-starred'] = __('Mark as Not Starred', 'ws-form');
						$actions['wsf-bulk-spam'] = __('Mark as Spam', 'ws-form');
					}

					// User capability check
					if(WS_Form_Common::can_user('delete_submission')) {

						$actions['wsf-bulk-delete'] = __('Move to Trash', 'ws-form');
					}

					// User capability check
					if(WS_Form_Common::can_user('export_submission')) {

						$actions['wsf-bulk-export'] = __('Export CSV', 'ws-form');
					}
			}

			return $actions;
		}

		// Extra table nav
		function extra_tablenav($which) {

			// Status related buttons
			$status = WS_Form_Common::get_query_var('ws-form-status');
			switch($status) {

				case 'trash' :
?>
		<div class="alignleft actions">
<?php 
			submit_button(__('Empty Trash', 'ws-form'), 'apply', 'delete_all', false );
?>
		</div>
<?php
					break;
			}

			if($which != 'top') { return; }

			// Select form
			$ws_form_form = New WS_Form_Form();
			$ws_form_form->db_count_update_all();
			$forms = $ws_form_form->db_read_all(

				'',
				(
					($this->form_id > 0) ? sprintf("(NOT (status = 'trash') AND count_submit > 0) OR (id = %u)", $this->form_id) : "NOT (status = 'trash') AND count_submit > 0"
				),
				'label ASC',
				'',
				'',
				false
			);

			if($forms) {
?>
<div class="alignleft actions">
<select id="wsf_filter_id" name="id">
<option value=""><?php esc_html_e('Select form...', 'ws-form'); ?></option>
<?php
				foreach($forms as $form) {

					// Get submit count
					$count_submit = $form['count_submit'];

?><option value="<?php WS_Form_Common::echo_esc_attr(absint($form['id'])); ?>"<?php

					// Selected
					if($form['id'] == $this->form_id) { echo ' selected'; }
?>><?php
					// Label
					WS_Form_Common::echo_esc_html(sprintf(__('%s (ID: %u)', 'ws-form'), $form['label'], $form['id']));

					// Submit count
					WS_Form_Common::echo_esc_html(' - ' . sprintf(_n('%u record', '%u records', $count_submit, 'ws-form'), $count_submit));
?></option>
<?php
				}
?>
</select>
<?php
				// Filters
				if($this->form_id > 0) {
?>
<input type="text" id="wsf_filter_date_from" name="date_from" value="<?php WS_Form_Common::echo_esc_attr(WS_Form_Common::get_query_var('date_from')); ?>" placeholder="<?php esc_attr_e('Date from', 'ws-form'); ?>" autocomplete="off" />

<input type="text" id="wsf_filter_date_to" name="date_to" value="<?php WS_Form_Common::echo_esc_attr(WS_Form_Common::get_query_var('date_to')); ?>" placeholder="<?php esc_attr_e('Date to', 'ws-form'); ?>" autocomplete="off" />

<input type="text" id="wsf_filter_keyword" name="keyword" value="<?php WS_Form_Common::echo_esc_attr(WS_Form_Common::get_query_var('keyword')); ?>" placeholder="<?php esc_attr_e('Search', 'ws-form'); ?>" autocomplete="off" />

<input type="button" id="wsf_filter_do" class="button" value="Filter" />
<input type="button" id="wsf_filter_reset" class="button" value="Reset" />
<?php
				}
?>
</div>
<?php
			}
		}

		// Set primary column
		public function list_table_primary_column($default, $screen) {

		    if($screen === 'ws-form_page_ws-form-submit') { $default = 'id'; }

		    return $default;
		}

		// Get record count
		public function record_count() {

			// If form ID not set, return 0
			if($this->form_id == 0) { return 0; }

			// Use cached record count to avoid multiple database queries
			if($this->record_count !== false) { return $this->record_count; }

			// Get data from API
			$ws_form_submit = New WS_Form_Submit();
			$ws_form_submit->form_id = $this->form_id;

			// Get record count
			$this->record_count = $ws_form_submit->db_read_count(

				$ws_form_submit->get_search_join(),
				$ws_form_submit->get_search_where()
			);

			return $this->record_count;
		}

		// No records
		public function no_items() {

			if($this->form_id == 0) {

				esc_html_e('Please select a form.', 'ws-form');

			} else {

				esc_html_e('No submissions available.', 'ws-form');
			}

		}
	}
