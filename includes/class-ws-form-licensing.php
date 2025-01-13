<?php

	/**
	 * Manages plugin licensing
	 */

	// Include EDD software licensing plugin updater
	if(!class_exists('EDD_SL_Plugin_Updater_WS_Form')) {

		// edd_sl_api_request_verify_ssl is set to false in this version of the file
		include('licensing/edd/EDD_SL_Plugin_Updater_WS_Form.php');
	}

	class WS_Form_Licensing {

		const LICENSE_ENDPOINT = 'https://wsform.com/';
		const LICENSE_TRANSIENT_ID = 'wsf-license-status';
		const LICENSE_TRANSIENT_EXPIRY = 86400;	// 1 Day

		private $item_id;
		private $prefix;
		private $name;
		private $version;
		private $author;

		private $path;
		private $tab_license;

		private $test_mode;

		public function __construct($item_id, $action_id = '', $name = WS_FORM_NAME_PRESENTABLE, $version = WS_FORM_VERSION, $author = WS_FORM_AUTHOR, $path = false, $test_mode = false) {

			$this->item_id = $item_id;
			$this->prefix = (($action_id != '') ? sprintf('action_%s_', $action_id) : '');
			$this->name = $name;
			$this->version = $version;
			$this->author = $author;

			$this->path = ($path === false) ? WS_FORM_PLUGIN_ROOT_FILE : $path;
			$this->tab_license = ($action_id != '') ? sprintf('action_%s', $action_id) : 'license';

			$this->test_mode = $test_mode;

			// Resets done in test mode
			if($this->test_mode) {

				set_site_transient('update_plugins', null);
			}

			// License constants
			add_filter('wsf_option_get', array($this, 'option_get'), 10, 2);
		}

		// License constants
		public function option_get($value, $key) {

			// Check for license related key
			if(strpos($key, 'license_key') !== false) {

				// Build license constant (e.g. WSF_LICENSE_KEY)
				$license_constant = sprintf('WSF_%s', strtoupper($key));

				// If defined, return constant instead
				if(defined($license_constant)) {

					return trim(constant($license_constant));
				}
			}

			return $value;
		}

		// Updater
		public function updater() {

			// Build updater args
			$args = array(

				'version' => $this->version,
				'license' => self::license_key(),
				'item_id' => $this->item_id,
				'author'  => $this->author,
				'beta'    => false
			);

			// Test mode
			if($this->test_mode) { $args['test_mode'] = true; };

			// Create new updater instance
			$edd_updater = new EDD_SL_Plugin_Updater_WS_Form(self::LICENSE_ENDPOINT, $this->path, $args);
		}

		// Transient check
		public function transient_check() {

			// Auto activate if license defined in wp-config.php
			if(is_admin()) {

				// Get constant license key
				$license_key_constant = self::maybe_license_key_constant();

				// Build option key
				$option_key = sprintf('%slicense_key_attempt', $this->prefix);

				// Get option value
				$option_value = WS_Form_Common::option_get($option_key);

				// Check to see if activation has been attempted 
				if($license_key_constant !== false) {

					// Create hashed version of license key
					$license_key_constant_hash = self::get_license_key_constant_hash();

					// Check to see if activation of this license key has been attempted
					if(
						($license_key_constant_hash !== false) &&
						($option_value != $license_key_constant_hash)
					) {

						// Set hash so this is only tried once
						WS_Form_Common::option_set($option_key, $license_key_constant_hash);

						// Attempt to activate silently
						self::activate($license_key_constant, true);
					}

				} else {

					// If constant license key has been removed and attempt was made to activate it, remove key so WS Form will attempt to reactivate if the constant is added again. Product will automatically be unlicensed once transient check occurs.
					if($option_value != '') {

						// Remove hash
						WS_Form_Common::option_remove($option_key);

						// Attempt to deactivate silently
						WS_Form_Common::option_set($this->prefix . 'license_activated', false);

						// Reset license expiry
						self::reset_license_expiry();
					}
				}
			}

			// Do not run if remote checks should be disabled
			if(self::do_remote_checks()) {

				// Check license transient
				try {

					// Get license transient value
					$license_transient = get_transient($this->prefix . self::LICENSE_TRANSIENT_ID);

				} catch (Exception $e) {

					// DB error occurred (e.g. DB lock) so lets stop the transient check
					return false;
				}

				if($license_transient === false) {

					// Check license
					self::check();

					// Store license status in case we want to pull it from the transient
					$license_status = array(

						'key'			=>	self::license_key(),
						'activated'		=>	self::license_activated(),
						'expires'		=>	WS_Form_Common::option_get($this->prefix . 'license_expires', ''),
						'time'			=>	time()
					);

					// Set transient
					set_transient($this->prefix . self::LICENSE_TRANSIENT_ID, $license_status, self::LICENSE_TRANSIENT_EXPIRY);
				}
			}

			// Add nag action
			if(is_admin()) {

				add_action('wsf_nag', array($this, 'nag'));
			}
		}

		// Activate
    public function activate($license_key, $silent = false) {
        WS_Form_Common::option_set($this->prefix . 'license_activated', true);
        self::set_license_expiry(new stdClass()); // You might need to adjust this
        return true;
    }

		// Deactivate
    public function deactivate($license_key, $silent = false) {
        // You can either leave this method as it is, or make it always return true
        return true;
    }

		// Check license
		public function check() {

			// Stop self checking
			if(self::LICENSE_ENDPOINT == trailingslashit(home_url())) { return false; }

			// API parameters
			$api_params = array(

				'edd_action'	=> 'check_license',
				'license'		=> self::license_key(),
				'item_id'		=> $this->item_id,
				'url'			=> home_url()
			);

			// Build args
			$args = array(

				'body'			=>	$api_params,
				'user-agent'	=> WS_Form_Common::get_request_user_agent(),
				'timeout'		=> WS_Form_Common::get_request_timeout(),
				'sslverify'		=> WS_Form_Common::get_request_sslverify()
			);

			// Call the custom API.
			$response = wp_remote_post(self::LICENSE_ENDPOINT, $args);

			// Check for errors
			if(is_wp_error($response) || (200 !== wp_remote_retrieve_response_code($response))) { return false; }

			// Get response body
			$response_body = wp_remote_retrieve_body($response);

			// Decode license data
			$license_data = json_decode($response_body);

			// Check returned license data
			if(
				is_null($license_data) ||
				!isset($license_data->success) ||
				!isset($license_data->license) 
			) { return false; }

			if($license_data->success) {

				if($license_data->license === 'valid') {

					self::set_license_expiry($license_data);

				} else {

					// Set license key invalid
					WS_Form_Common::option_set($this->prefix . 'license_activated', false);

					self::reset_license_expiry();
				}
			}

			return true;
		}

		// Get license constant
		public function maybe_license_key_constant($license_key = false) {

			// Build license constant (e.g. WSF_LICENSE_KEY)
			$license_constant = self::get_license_constant();

			// If defined, set value to license constant
			if(defined($license_constant)) {

				return trim(constant($license_constant));
			}

			return $license_key;
		}

		// Get license constant
		public function get_license_constant() {

			return sprintf('WSF_%sLICENSE_KEY', strtoupper($this->prefix));
		}

		// Get license constant hash
		public function get_license_key_constant_hash() {

			$license_key_constant = self::maybe_license_key_constant();

			if($license_key_constant === false) { return false; }

			return substr(md5($license_key_constant), -6);
		}

		// Get license key
		public function license_key() {

			// Override with license constant if defined
			return self::maybe_license_key_constant(WS_Form_Common::option_get($this->prefix . 'license_key', ''));
		}

public function license_activated() {
        return true;
    }


		// Should remote license checks be performed?
		public function do_remote_checks() {

			// License key must be set
			// License key must be activated
			// Only check if in admin
			return !empty(self::license_key()) && self::license_activated() && is_admin();
		}

		// Get license status as string
		public function license_status() {

			if(self::license_activated()) {

				$license_expires = WS_Form_Common::option_get($this->prefix . 'license_expires', '');
				return 'Licensed' . (($license_expires != '') ? ' (Expires: ' . gmdate(get_option('date_format'), $license_expires) . ')' : '');

			} else {

				return 'Unlicensed';
			}
		}

		// Set license expiry
		public function set_license_expiry($license_data) {

			if(!is_object($license_data)) { self::reset_license_expiry(); return false; }

			if(!isset($license_data->expires)) { self::reset_license_expiry(); return false; }

			$license_expires_string = $license_data->expires;

			$license_expires = strtotime($license_expires_string);

			if($license_expires === false) { self::reset_license_expiry(); return false; }

			WS_Form_Common::option_set($this->prefix . 'license_expires', $license_expires);
		}

		// Reset license expiry
		public function reset_license_expiry() {

			WS_Form_Common::option_set($this->prefix . 'license_expires', '');
		}

		// Nag
		public function nag() {

			// Check to see if license is activated
			if(!self::license_activated()) {

				// If license is not activated, show a nag
				WS_Form_Common::admin_message_push(sprintf(__('%s is not licensed. Please enter your license key <a href="%s">here</a> to receive software updates and support.', 'ws-form'), $this->name, WS_Form_Common::get_admin_url('ws-form-settings', false, 'tab=' . $this->tab_license)), 'notice-warning', false, true);
			}
		}

		// License key processing error
		public function error($message) {

			// Set license key deactivated
			WS_Form_Common::option_set($this->prefix . 'license_activated', false);
			return true;
			// Reset license expiry
			self::reset_license_expiry();

			// Error
			WS_Form_Common::admin_message_push($message, 'notice-error');

			return false;
		}
	}
