<?php

	class WS_Form_WP_List_Table_Style extends WP_List_Table {

		public $ws_form_style;
		public $style_id_default = 0;
		public $style_id_conv_default = 0;

		// Construct
	    public function __construct() {

			parent::__construct(array(

				'singular'		=> __('Style', 'ws-form'), //Singular label
				'plural'		=> __('Styles', 'ws-form'), //plural label, also this well be one of the table css class
				'ajax'			=> false //We won't support Ajax for this table
			));

			// Set primary column
			add_filter('list_table_primary_column',[$this, 'list_table_primary_column'], 10, 2);

			// Style object
			$this->ws_form_style = new WS_Form_Style();

			// Check initiaized
			$this->ws_form_style->check_initialized();

			// Get default style ID
			$this->style_id_default = $this->ws_form_style->get_style_id_default();

			// Get default style ID
			$this->style_id_conv_default = $this->ws_form_style->get_style_id_conv_default();
	    }

	    // Get columns
		public function get_columns() {

  		  	$columns = [

				'cb'			=> '<input type="checkbox" />',
				'title'			=> __('Name', 'ws-form'),
				'id'			=> __('ID', 'ws-form'),
				'preview'		=> __('Preview', 'ws-form'),
			];

			return $columns;
		}

		// Get sortable columns
		public function get_sortable_columns() {

			return array(

				'title'				=> array('label', true),		// Used 'title' as opposed to 'label' because WordPress considers that a special keyword and excludes it from the screen options column checkboxes
				'id'				=> array('id', true),
			);
		}

		// Column - Default
		public function column_default($item, $column_name) {

			switch ($column_name) {

				case 'name':
				case 'id':

					return $item[$column_name];
					break;

				default:

					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}

		// Column - Conversion
		function column_preview($item) {

			// Get style ID
			$id = absint($item['id']);

			$return_html = $this->ws_form_style->get_preview_html($id);

			return $return_html;
		}

		// Column - Checkbox
		function column_cb($item) {

			return sprintf('<input type="checkbox" name="bulk-ids[]" value="%u" />', $item['id']);
		}

		// Column - Title
		function column_title($item) {

			// Get style ID
			$id = absint($item['id']);

			// Check if default
			$is_default = ($id == $this->style_id_default);

			// Check if default conversational
			$is_default_conv = ($id == $this->style_id_conv_default);

			// Get URL for editing
			$url_edit = WS_Form_Common::get_preview_url(1, 'styler', $id);

			// Default
			$title_default_array = array();

			if($is_default) {

				$title_default_array[] = __('Default', 'ws-form');
			}

			if($is_default_conv) {

				$title_default_array[] = __('Conversational', 'ws-form');
			}
			$title_default = ((count($title_default_array) > 0) ? sprintf(

				' — <span class="post-state">%s</span>',
				implode(' & ', $title_default_array)
			) : '');

			// Title
			if(WS_Form_Common::can_user('edit_form_style')) {

				$title = sprintf('<strong><a href="%s" target="_blank">%s</a>%s</strong>', $url_edit, esc_html($item['label']), $title_default);

			} else {

				$title = sprintf('<strong>%s%s</strong>', esc_html($item['label']), $title_default);
			}

			// Actions
			$status = WS_Form_Common::get_query_var('ws-style-status');
			$actions = array();
			switch($status) {

				case 'trash' :

					if(WS_Form_Common::can_user('delete_form_style')) {

						$actions['restore'] = 	sprintf('<a href="#" data-action="wsf-restore" data-id="%u">%s</a>', $id, __('Restore', 'ws-form'));
						$actions['delete'] = 	sprintf('<a href="#" data-action="wsf-delete" data-id="%u">%s</a>', $id, __('Delete Permanently', 'ws-form'));
					}

					break;

				default :

					if(WS_Form_Common::can_user('edit_form_style')) {

						$actions['edit'] = 	sprintf('<a href="%s" target="_blank">%s</a>', $url_edit, __('Edit', 'ws-form'));
					}

					if(WS_Form_Common::can_user('create_form_style')) {

						$actions['copy'] = 	sprintf('<a href="#" data-action="wsf-clone" data-id="%u">%s</a>', $id, __('Clone', 'ws-form'));
					}

					if(!$is_default && !$is_default_conv && WS_Form_Common::can_user('delete_form_style')) {

						$actions['trash'] = sprintf('<a href="#" data-action="wsf-delete" data-id="%u">%s</a>', $id, __('Trash', 'ws-form'));
					}

					if(WS_Form_Common::can_user('export_form_style')) {

						$actions['export'] = sprintf('<a href="#" data-action="wsf-export" data-id="%u">%s</a>', $id, __('Export', 'ws-form'));
					}

					if(false && WS_Form_Common::can_user('read_form_style')) {

						$actions['locate'] = sprintf('<a href="#" data-action-ajax="wsf-style-locate" data-id="%u">%s</a>', $id, __('Locate', 'ws-form'));
					}

					if(!$is_default && !$is_default_conv && WS_Form_Common::can_user('edit_form_style')) {

						$actions['default'] = 	sprintf('<a href="#" data-action="wsf-default" data-id="%u">%s</a>', $id, __('Set as default', 'ws-form'));
					}
					if(!$is_default && !$is_default_conv && WS_Form_Common::can_user('edit_form_style')) {

						$actions['default_conv'] = 	sprintf('<a href="#" data-action="wsf-default-conv" data-id="%u">%s</a>', $id, __('Set as conversational', 'ws-form'));
					}
					if(WS_Form_Common::can_user('edit_form_style')) {

						$actions['reset'] = 	sprintf('<a href="#" data-action="wsf-reset" data-id="%u">%s</a>', $id, __('Reset', 'ws-form'));
					}
			}

			return $title . $this->row_actions($actions);
		}

		// Views
		function get_views(){

			// Get data from API
			$ws_form_style = New WS_Form_Style();

			$views = array();
			$current = WS_Form_Common::get_query_var('ws-style-status', 'all');
			$all_url = remove_query_arg(array('ws-style-status', 'paged'));

			// All link
			$count_all = $ws_form_style->db_get_count_by_status();
			if($count_all) {

				$views['all'] = sprintf(

					'<a href="%s"%s>%s <span class="count">%u</span></a>',
					esc_attr(add_query_arg('ws-style-status', 'all', $all_url)),
					($current === 'all' ? ' class="current"' :''),
					__('All', 'ws-form'),
					$count_all
				);
			}

			// Trashed link
			$count_trash = $ws_form_style->db_get_count_by_status('trash');
			if($count_trash) {

				$views['trash'] = sprintf(

					'<a href="%s"%s>%s <span class="count">%u</span></a>',
					esc_attr(add_query_arg('ws-style-status', 'trash', $all_url)),
					($current === 'trash' ? ' class="current"' :''),
					__('Trash', 'ws-form'),
					$count_trash
				);
			}

			return $views;
		}

		// Get data
		function get_data($per_page = 20, $page_number = 1) {

			// Build JOIN
			$join = '';

			// Build WHERE
			$where_array = array();

			// Status
			$status = WS_Form_Common::get_query_var('ws-style-status');
			if($status == '') { $status == 'all'; }
			if(WS_Form_Common::check_style_status($status) == '') { $status = 'all'; }
			if($status != 'all') {
	
				// Filter by status
				$where_array[] = sprintf('status = "%s"', esc_sql($status));

			} else {

				// Show everything but trash (All)
				$where_array[] = "NOT(status = 'trash')";
			}

			// Check for search
			$s = WS_Form_Common::get_query_var_nonce('s');
			if(!empty($s)) {

				$where_array[] = sprintf("label LIKE '%%%s%%'", esc_sql($s));
			}


			// Build WHERE
			$where = implode(' AND ', $where_array);

			// Build ORDER BY
			$order_by = '';

			$user = wp_get_current_user();

			// Order by
			$meta_key_orderby = $user->user_nicename . '_list_table_style_orderby';
			$order_by_query_var = WS_Form_Common::check_style_order_by(WS_Form_Common::get_query_var('orderby'));
			if(!empty($order_by_query_var)) { WS_Form_Common::option_set($meta_key_orderby, $order_by_query_var); }
			$order_by_option = WS_Form_Common::check_style_order_by(WS_Form_Common::option_get($meta_key_orderby));
			if(isset($_GET) && !empty($order_by_option)) { $_GET['orderby'] = $order_by_option; }	// phpcs:ignore WordPress.Security.NonceVerification

			// Order
			$meta_key_order = $user->user_nicename . '_list_table_style_order';
			$order_query_var = WS_Form_Common::check_style_order(WS_Form_Common::get_query_var('order'));
			if(!empty($order_query_var)) { WS_Form_Common::option_set($meta_key_order, $order_query_var); }
			$order_option = WS_Form_Common::check_style_order(WS_Form_Common::option_get($meta_key_order));
			if(isset($_GET) && !empty($order_option)) { $_GET['order'] = $order_option; }	// phpcs:ignore WordPress.Security.NonceVerification

			if(!empty($order_by_option)) {

				$order_by = esc_sql($order_by_option);
				$order = !empty($order_option) ? $order_option : 'ASC';
				if(!in_array(strtoupper($order), array('ASC', 'DESC'), true)) { $order = 'ASC'; }
				$order_by .= ' ' . esc_sql(strtoupper($order));

			} else {

				$order_by = 'id ASC';
			}

			// Build LIMIT
			$limit = $per_page;

			// Build OFFSET
			$offset = ($page_number - 1) * $per_page;

			// Get data from API
			$ws_form_style = New WS_Form_Style();
			$result = $ws_form_style->db_read_all($join, $where, $order_by, $limit, $offset);

			return $result;
		}

		// Prepare items
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();

			$per_page     = $this->get_items_per_page('ws_form_styles_per_page', 20);
			$current_page = $this->get_pagenum();
			$total_items  = self::record_count();

			$this->set_pagination_args(array(

				'total_items' => $total_items,
				'per_page'    => $per_page
			));

			$this->items = self::get_data($per_page, $current_page);
		}

		// Bulk actions - Prepare
		public function get_bulk_actions() {

			$actions = array();
			$status = WS_Form_Common::get_query_var('ws-style-status');

			switch($status) {

				case 'trash' :

					if(WS_Form_Common::can_user('delete_form_style')) {

						$actions['wsf-bulk-restore'] = __('Restore', 'ws-form');
						$actions['wsf-bulk-delete'] = __('Delete Permanently', 'ws-form');
					}
					break;

				default:

					if(WS_Form_Common::can_user('delete_form_style')) {

						$actions['wsf-bulk-delete'] = __('Move to Trash', 'ws-form');
					}
			}

			return $actions;
		}

		// Extra table nav
		function extra_tablenav( $which ) {

			$status = WS_Form_Common::get_query_var('ws-style-status');

			switch($status) {

				case 'trash' :

					if(WS_Form_Common::can_user('delete_form_style')) {
?>
		<div class="alignleft actions">
<?php 
			submit_button(__('Empty Trash', 'ws-form'), 'apply', 'delete_all', false );
?>
		</div>
<?php
					}

					break;
			}
		}

		// Set primary column
		public function list_table_primary_column($default, $screen) {

		    if($screen === 'toplevel_page_ws-form') { $default = 'title'; }

		    return $default;
		}

		// Get record count
		public function record_count() {

			if(empty(WS_Form_Common::get_query_var_nonce('s'))) {

				$ws_form_style = New WS_Form_Style();

				$current = WS_Form_Common::get_query_var('ws-style-status', 'all');
				if($current === 'all') { $current = ''; }

				return $ws_form_style->db_get_count_by_status($current);

			} else {

				return count(self::get_data(0, 0));
			}
		}

		// No records
		public function no_items() {

			esc_html_e('No styles available.', 'ws-form');
		}
	}
