<?php

	class WS_Form_Action_User_WooCommerce extends WS_Form_Action_User {

		public $woocommerce_file_fields = array();
		public $woocommerce_update_fields = array();
		public $woocommerce_attachments = array();

		public $product_image_gallery_attachment_ids = array();
		public $product_image_gallery_mapped = false;
		public $product_image_product_gallery_field_ids = array();

		// Construct
		public function __construct() {

			// Settings
			add_filter('wsf_action_user_config_meta_keys', array($this, 'hook_config_meta_keys'), 10, 1);

			// Form building
			add_filter('wsf_action_user_list_fields', array($this, 'hook_list_fields'), 10, 2);
			add_filter('wsf_action_user_list_fields_meta_data', array($this, 'hook_list_fields_meta_data'), 10, 2);
			add_filter('wsf_action_user_form_meta', array($this, 'hook_form_meta'), 10, 3);

			// Form submitting
			add_filter('wsf_action_user_meta_keys', array($this, 'hook_meta_keys'), 10, 6);

			// Form population
			add_filter('wsf_action_user_get_field_mapping', array($this, 'hook_get_field_mapping'), 10, 3);

			// Logo
			add_filter('wsf_action_user_svg_custom_field_logos', array($this, 'hook_svg_custom_field_logos'), 10, 2);
		}

		// Config meta keys
		public function hook_config_meta_keys($config_meta_keys) {

			$woocommerce_fields = is_admin() ? WS_Form_WooCommerce::woocommerce_get_fields_all('user', false, false, true, false, false) : array();

			foreach($woocommerce_fields as $meta_key => $woocommerce_field) {

				$config_meta_keys['action_user_form_populate_field']['options'][] = array('value' => $meta_key, 'text' => $woocommerce_field['label']);
			}

			return $config_meta_keys;
		}

		// Process list fields
		public function hook_list_fields($list_fields, $list_id) {

			// Get fields
			$fields = WS_Form_WooCommerce::woocommerce_get_fields_all('user', false, false, true, false, false);

			// Process list fields
			$woocommerce_fields_to_list_fields_return = WS_Form_WooCommerce::woocommerce_fields_to_list_fields($fields, $list_fields['group_index'], $list_fields['section_index']);

			// Merge return
			$list_fields['list_fields'] = array_merge_recursive($list_fields['list_fields'], $woocommerce_fields_to_list_fields_return['list_fields']);
			$list_fields['group_index'] = $woocommerce_fields_to_list_fields_return['group_index'];
			$list_fields['section_index'] = $woocommerce_fields_to_list_fields_return['section_index'] + 1;

			return $list_fields;
		}

		// Process list fields meta data
		public function hook_list_fields_meta_data($list_fields_meta_data, $list_id) {

			// Get fields
			$fields = WS_Form_WooCommerce::woocommerce_get_fields_all('user', false, false, true, false, false);

			// Process meta data
			$woocommerce_fields_to_meta_data_return = WS_Form_WooCommerce::woocommerce_fields_to_meta_data($fields, $list_fields_meta_data['group_index'], $list_fields_meta_data['section_index']);

			// Process return
			$list_fields_meta_data['group_meta_data'] = array_merge_recursive($list_fields_meta_data['group_meta_data'], $woocommerce_fields_to_meta_data_return['group_meta_data']);
			$list_fields_meta_data['section_meta_data'] = array_merge_recursive($list_fields_meta_data['section_meta_data'], $woocommerce_fields_to_meta_data_return['section_meta_data']);
			$list_fields_meta_data['group_index'] = $woocommerce_fields_to_meta_data_return['group_index'];
			$list_fields_meta_data['section_index'] = $woocommerce_fields_to_meta_data_return['section_index'] + 1;

			return $list_fields_meta_data;
		}

		// Process form meta
		public function hook_form_meta($form_meta, $form_field_id_lookup_all, $list_id) {

			$woocommerce_fields = WS_Form_WooCommerce::woocommerce_get_fields_all('user', false, false, false, true, false);

			foreach($woocommerce_fields as $meta_key => $woocommerce_field) {

				$form_meta['action_user_form_populate_field_mapping'][] = array('action_user_form_populate_field' => $meta_key, 'ws_form_field' => self::form_field_id_lookup($meta_key, $form_field_id_lookup_all));
			}

			return $form_meta;
		}

		// Process meta keys
		public function hook_meta_keys($meta_keys, $form, $submit, $config, $list_id, $api_fields) {

			$woocommerce_fields = WS_Form_WooCommerce::woocommerce_get_fields_all('user', false, false, true, false, false);

			foreach($woocommerce_fields as $meta_key => $woocommerce_field) {

				if(isset($api_fields[$meta_key])) {

					$meta_keys[$meta_key] = $api_fields[$meta_key];
				}
			}

			return $meta_keys;
		}

		// Process get field mapping
		public function hook_get_field_mapping($meta_value, $meta_key, $user_id) {

			$woocommerce_fields = WS_Form_WooCommerce::woocommerce_get_fields_all('user', false, false, true, false, false);

			if(isset($woocommerce_fields[$meta_key])) {

				$meta_value = get_user_meta($user_id, $meta_key, false);
			}

			return $meta_value;
		}

		// Logo
		public function hook_svg_custom_field_logos($svg_custom_field_logos, $list_id) {

			if(WS_Form_WooCommerce::woocommerce_get_fields_all('user', false, false, true, false, true)) {

				$svg_custom_field_logos[] = '<path fill="#7e58a4" d="M2 5.3h18c1.1 0 2 .9 2 2v6.8c0 1.1-.9 2-2 2h-6.4l.9 2.2-3.9-2.2H2c-1.1 0-2-.9-2-2V7.3c0-1.1.9-2 2-2z"/><path fill="#ffffff" d="M1.1 7.1c.1-.1.4-.2.6-.2.5 0 .7.2.8.6.2 1.9.5 3.5.9 4.8l2-3.7c.1-.4.3-.6.6-.6.4 0 .6.2.7.8.2 1 .5 2.1.9 3.1.2-2.3.6-3.9 1.2-4.9.1-.2.3-.4.6-.4.2 0 .4 0 .6.2.2.1.3.3.3.5 0 .1 0 .3-.1.4-.4.7-.6 1.7-.9 3.2-.3 1.4-.3 2.6-.3 3.4 0 .2 0 .4-.1.6-.1.2-.3.3-.5.3s-.5-.1-.7-.3c-.8-.9-1.5-2.1-2-3.8l-1.3 2.6c-.5 1-1 1.5-1.4 1.6-.2 0-.5-.2-.6-.6-.4-1.3-.9-3.6-1.4-6.9-.1-.3 0-.5.1-.7zM20.4 8.6c-.3-.5-.8-.9-1.4-1-.2 0-.3-.1-.5-.1-.9 0-1.6.4-2.1 1.3-.5.8-.7 1.6-.7 2.5 0 .7.1 1.3.4 1.8.3.5.8.9 1.4 1 .2 0 .3.1.5.1.9 0 1.6-.4 2.1-1.3.5-.8.7-1.6.7-2.5.1-.8-.1-1.4-.4-1.8zm-1.1 2.5c-.1.6-.4 1-.7 1.3-.3.2-.5.3-.7.3-.2 0-.4-.2-.5-.6-.1-.3-.2-.5-.2-.8 0-.2 0-.4.1-.7.1-.4.2-.7.5-1.1.2-.4.6-.6.9-.5.2 0 .4.2.5.6.1.3.2.5.2.8 0 .2-.1.4-.1.7zM14.8 8.6c-.3-.5-.8-.9-1.4-1-.2 0-.3-.1-.5-.1-.9 0-1.6.4-2.1 1.3-.5.8-.7 1.6-.7 2.5 0 .7.1 1.3.4 1.8.3.5.8.9 1.4 1 .2 0 .3.1.5.1.9 0 1.6-.4 2.1-1.3.5-.8.7-1.6.7-2.5 0-.8-.1-1.4-.4-1.8zm-1.1 2.5c-.1.6-.4 1-.7 1.3-.3.2-.5.3-.7.3-.2 0-.4-.2-.5-.6-.1-.3-.2-.5-.2-.8 0-.2 0-.4.1-.7.1-.4.2-.7.5-1.1.2-.4.5-.6.8-.5.2 0 .4.2.5.6.1.3.2.5.2.8v.7z"/>';
			}

			return $svg_custom_field_logos;
		}
	}

	new WS_Form_Action_User_WooCommerce();

