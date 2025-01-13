<?php

	/**
	 * @link              https://wsform.com/knowledgebase/user-management/
	 * @since             1.0.0
	 * @package           WS_Form_User
	 *
	 * @wordpress-plugin
	 * Plugin Name:       WS Form PRO - User Management
	 * Plugin URI:        https://wsform.com/knowledgebase/user-management/
	 * Description:       User Management add-on for WS Form PRO
	 * Version:           1.6.4
	 * Requires at least: 5.2
	 * Requires PHP:      5.6
	 * License:           GPLv3 or later
	 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 	 * Author:            WS Form
  	 * Author URI:        https://wsform.com/
	 * Text Domain:       ws-form-user
	 * Domain Path:       /languages
	 */

	define('WS_FORM_USER_PLUGIN_ROOT_FILE', __FILE__);
	define('WS_FORM_USER_PLUGIN_BASENAME', plugin_basename(__FILE__));
	define('WS_FORM_USER_VERSION', '1.6.4');

	Class WS_Form_Add_On_User {

		const WS_FORM_PRO_ID 			= 'ws-form-pro/ws-form.php';
		const WS_FORM_PRO_VERSION_MIN 	= '1.8.115';

		function __construct() {

			// Load plugin.php
			if(!function_exists('is_plugin_active')) {

				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}

			// Admin init
			add_action('plugins_loaded', array($this, 'plugins_loaded'), 20);
		}

		function plugins_loaded() {

			// Initialize plugin
			if(self::is_dependency_ok()) {

				new WS_Form_Action_User();

			} else {

				self::dependency_error();

				if(isset($_GET['activate'])) { unset($_GET['activate']); }
			}
		}

		function activate() {

			if (!self::is_dependency_ok()) {

				self::dependency_error();
			}
		}

		// Check dependencies
		function is_dependency_ok() {

			if(!defined('WS_FORM_VERSION')) { return false; }

			return(

				is_plugin_active(self::WS_FORM_PRO_ID) &&
				(version_compare(WS_FORM_VERSION, self::WS_FORM_PRO_VERSION_MIN) >= 0)
			);
		}

		// Add error notice action - Pro
		function dependency_error() {

			// Show error notification
			add_action('after_plugin_row_' . plugin_basename(__FILE__), array($this, 'dependency_error_notification'), 10, 2);
		}

		// Dependency error - Notification
		function dependency_error_notification($file, $plugin) {

			// Checks
			if(!current_user_can('update_plugins')) { return; }
			if($file != plugin_basename(__FILE__)) { return; }

			// Build notice
			$dependency_notice = sprintf('<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p>%s</p></div></td></tr>', sprintf(__('This add-on requires %s (version %s or later) to be installed and activated.', 'ws-form-user'), '<a href="https://wsform.com?utm_source=ws_form_pro&utm_medium=plugins" target="_blank">WS Form PRO</a>', self::WS_FORM_PRO_VERSION_MIN));

			// Show notice
			echo $dependency_notice;
		}
	}

	$wsf_add_on_constant_contact = new WS_Form_Add_On_User();	

	register_activation_hook(__FILE__, array($wsf_add_on_constant_contact, 'activate'));

	// This gets fired by WS Form when it is ready to register add-ons
	add_action('wsf_plugins_loaded', function() {

		// Post
		include 'includes/classes/class-ws-form-action-user.php';

		// ACF
		if(class_exists('WS_Form_ACF')) {

			include 'includes/third-party/class-ws-form-action-user-acf.php';
		}

		// ACPT
		if(class_exists('WS_Form_ACPT')) {

			include 'includes/third-party/class-ws-form-action-user-acpt.php';
		}

		// JetEngine
		if(class_exists('WS_Form_JetEngine')) {

			include 'includes/third-party/class-ws-form-action-user-jetengine.php';
		}

		// Meta Box
		if(class_exists('WS_Form_Meta_Box')) {

			include 'includes/third-party/class-ws-form-action-user-meta-box.php';
		}

		// Pods
		if(class_exists('WS_Form_Pods')) {

			include 'includes/third-party/class-ws-form-action-user-pods.php';
		}

		// Toolset
		if(class_exists('WS_Form_Toolset')) {

			include 'includes/third-party/class-ws-form-action-user-toolset.php';
		}

		// WooCommerce
		if(class_exists('WS_Form_WooCommerce')) {

			include 'includes/third-party/class-ws-form-action-user-woocommerce.php';
		}
	});