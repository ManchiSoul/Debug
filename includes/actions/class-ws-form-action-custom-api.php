<?php

	class WS_Form_Action_Custom_Endpoint extends WS_Form_Action {

		public $id = 'customendpoint';
		public $pro_required = true;
		public $label;
		public $label_action;
		public $events;
		public $multiple = true;
		public $configured = false;
		public $priority = 150;
		public $can_repost = true;
		public $form_add = false;

		// Config
		public $endpoint;
		public $request_method;
		public $content_type;

		public $field_mapping;
		public $custom_mapping;
		public $header_mapping;

		public $response_process;
		public $timeout;
		public $response_code_mapping;
		public $response_mapping;

		public $authentication;
		public $username;
		public $password;

		public $ssl_verify;
		public $file_to_url;
		public $ignore_empty;
		public $array_to_delimited;
		public $array_dedupe;
		public $cookie_passthrough;

		public function __construct() {

			// Set label
			$this->label = __('Webhook', 'ws-form');

			// Set label for actions pull down
			$this->label_action = __('Webhook', 'ws-form');

			// Events
			$this->events = array('submit');

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);

			// Register action
			parent::register($this);
		}

		// Post to endpoint
		public function post($form, &$submit, $config) {

			// Load configuration
			self::load_config($config);

			$data = array();

			// Get fields in single dimension array
			$fields = WS_Form_Common::get_fields_from_form($form);

			// Process field mapping
			foreach($this->field_mapping as $field_map) {

				// Get key
				$node = $field_map['action_' . $this->id . '_endpoint_key'];
				$node = WS_Form_Common::parse_variables_process($node, $form, $submit, 'text/plain');
				if(empty($node)) { continue; }

				// Get value
				$field_id = $field_map['ws_form_field'];
				if(empty($field_id)) { continue; }
				$value = parent::get_submit_value($submit, WS_FORM_FIELD_PREFIX . $field_id, '', true);

				// Get type
				$type = isset($field_map['action_' . $this->id . '_endpoint_type']) ? $field_map['action_' . $this->id . '_endpoint_type'] : '';
				if(!in_array($type, array('', 'string', 'integer', 'float', 'boolean'))) { continue; }

				// Get field type
				if(!isset($fields[$field_id])) { continue; }
				$field = $fields[$field_id];

				// Process by field type
				switch($field->type) {

					case 'file' :
					case 'signature' :

						// Add URLs to file objects
						if(is_array($value)) {

							$file_urls = array();

							foreach($value as $file_object_index => $file_object) {

								if(
									isset($file_object['url']) ||
									!isset($file_object['name']) ||
									!isset($file_object['size']) ||
									!isset($file_object['type']) ||
									!isset($file_object['path'])

								) { continue; }

								// Get handler
								$handler = isset($file_object['handler']) ? $file_object['handler'] : 'wsform';

								// Get URL
								if(isset(WS_Form_File_Handler_WS_Form::$file_handlers[$handler])) {

									$file_url = WS_Form_File_Handler_WS_Form::$file_handlers[$handler]->get_url($file_object, $field_id, $file_object_index, $submit->hash);
									$value[$file_object_index]['url'] = $file_url;
									$file_urls[] = $file_url;
								}
							}

							if($this->file_to_url) {

								$value = implode(',', $file_urls);
							}
						}

						break;
				}

				// Type
				$value = self::type_process($value, $type);

				// Check empty
				if($this->ignore_empty && is_string($value) && ($value === '')) {

					continue;
				}

				// Add endpoint value to data using dot notation
				try {

					WS_Form_Common::set_path_value($data, $node, $value, $this->array_dedupe);

				}  catch (Exception $e) {

					parent::error($e->getMessage());
					return 'halt';
				}
			}

			// Process custom mapping
			foreach($this->custom_mapping as $custom_map) {

				// Get key
				$node = $custom_map['action_' . $this->id . '_endpoint_key'];
				$node = WS_Form_Common::parse_variables_process($node, $form, $submit, 'text/plain');
				if(empty($node)) { continue; }

				// Get value
				$value = WS_Form_Common::parse_variables_process($custom_map['action_' . $this->id . '_endpoint_value'], $form, $submit, 'text/plain');

				// Get type
				$type = isset($custom_map['action_' . $this->id . '_endpoint_type']) ? $custom_map['action_' . $this->id . '_endpoint_type'] : '';
				if(!in_array($type, array('', 'string', 'integer', 'float', 'boolean'))) { continue; }

				// Type
				$value = self::type_process($value, $type);

				// Check empty
				if($this->ignore_empty && is_string($value) && ($value === '')) {

					continue;
				}

				// Add endpoint value to data using dot notation
				try {

					WS_Form_Common::set_path_value($data, $node, $value, $this->array_dedupe);

				}  catch (Exception $e) {

					parent::error($e->getMessage());
					return 'halt';
				}
			}

			// Apply filters
			$data = apply_filters('wsf_action_webhook_payload', $data, $form, $submit, $config);

			// Encode if not GET method
			if(
				($this->content_type == 'application/json') &&
				($this->request_method != 'GET')
			) {

				$data = wp_json_encode($data);
				parent::success(sprintf(__('Request payload: <pre>%s</pre>', 'ws-form'), esc_html(print_r($data, true))));

			} else {

				parent::success(sprintf(__('Request payload: <pre>%s</pre>', 'ws-form'), esc_html(http_build_query($data))));
			}

			// Authentication
			$username = false;
			$password = false;
			if($this->authentication != '') {

				$username = $this->username;
				$password = $this->password;

				parent::success(sprintf(__('Request authentication: %s', 'ws-form'), $this->authentication));
			}

			// Process HTTP headers
			$http_headers = array();
			foreach($this->header_mapping as $header_map) {

				// Get header name
				$header_name = WS_Form_Common::parse_variables_process($header_map['action_' . $this->id . '_header_name'], $form, $submit, 'text/plain');
				if(empty($header_name)) { continue; }

				// Get header value
				$header_value = WS_Form_Common::parse_variables_process($header_map['action_' . $this->id . '_header_value'], $form, $submit, 'text/plain');

				// Save field
				$http_headers[$header_name] = $header_value;
			}

			// Apply filters
			$http_headers = apply_filters('wsf_action_webhook_http_headers', $http_headers, $form, $submit, $config);

			if(count($http_headers) > 0) {

				parent::success(sprintf(__('Request headers: <pre>%s</pre>', 'ws-form'), esc_html(print_r($http_headers, true))));
			}

			// Parse endpoint
			$this->endpoint = WS_Form_Common::parse_variables_process($this->endpoint, $form, $submit, 'text/plain');

			// Sanitize endpoint
			$this->endpoint = sanitize_url($this->endpoint);

			parent::success(sprintf(__('Request endpoint: %s', 'ws-form'), esc_html($this->endpoint)));
			parent::success(sprintf(__('Request request method: %s', 'ws-form'), esc_html($this->request_method)));
			parent::success(sprintf(__('Request content type: %s', 'ws-form'), esc_html($this->content_type)));

			// Cookie passthrough - Only if the hostnames match for security reasons
			$cookies = array();

			if($this->cookie_passthrough) {

				$referrer = sanitize_url(WS_Form_Common::get_http_env_raw(array('HTTP_REFERER')));
				$referrer_parsed = wp_parse_url($referrer);
				$endpoint_parsed = wp_parse_url($this->endpoint);

				if($referrer_parsed['host'] === $endpoint_parsed['host']) {

					foreach($_COOKIE as $name => $value) {

						$cookies[] = new WP_Http_Cookie(array('name' => $name, 'value' => $value));
					}

					parent::success(__('Same hostname cookie passthrough', 'ws-form'));
				}
			}

			// SSL Verify
			parent::success(sprintf(__('Request SSL verify: %s', 'ws-form'), ($this->ssl_verify ? 'Enabled' : 'Disabled')));

			// Check timeout
			if($this->timeout === 0) {

				$this->timeout = WS_FORM_API_CALL_TIMEOUT;
			}

			if($this->response_process) {

				parent::success(sprintf(__('Request timeout: %u second%s', 'ws-form'), esc_html($this->timeout), (($this->timeout === 1) ? '' : 's')));
				parent::success(__('Response processing: Enabled', 'ws-form'));

				// Timer
				$http_response_time_start = microtime(true);

			} else {

				parent::success(__('Response processing: Disabled', 'ws-form'));
			}

			// Make API request
			$http_response = parent::api_call($this->endpoint, '', $this->request_method, $data, $http_headers, $this->authentication, $username, $password, $this->content_type, $this->content_type, $this->timeout, $this->ssl_verify, $cookies, $this->response_process);

			if($http_response['error']) {

				// Errors
				parent::error($http_response['error_message']);
				return 'halt';

			} else {

				// Success
				$http_code = absint($http_response['http_code']);

				// Debug
				// Should we handle responses?
				if($this->response_process) {

					// Debug - Response time
					$http_response_time = (microtime(true) - $http_response_time_start) * 1000;

					parent::success(sprintf(__('Response time: %u ms', 'ws-form'), esc_html($http_response_time)));

					// Debug - Response code
					parent::success(sprintf(__('Response code: %u', 'ws-form'), esc_html($http_code)));

					// Debug - Response
					parent::success(sprintf(__('Response: <pre>%s</pre>', 'ws-form'), esc_html(print_r($http_response, true))));

					// Default response handler
					$response_handler = '';

					// Response code mapping
					foreach($this->response_code_mapping as $response_code_map) {

						// Get code
						$response_code = absint($response_code_map['action_' . $this->id . '_response_code']);

						// Check code
						if($response_code !== $http_code) { continue; }

						// Get response handler
						$response_handler = $response_code_map['action_' . $this->id . '_response_handler'];
						if(!in_array($response_handler, array('', 'halt', 'error', 'response_mapping'))) {

							$response_handler = '';
						}
					}

					switch($response_handler) {

						case 'halt' :

							parent::success(__('Response code mapping: Halt', 'ws-form'));

							return 'halt';

						case 'error' :

							parent::success(__('Response code mapping: Error', 'ws-form'));

							throw new ErrorException(

								sprintf(

									__('Webhook error - HTTP status code: %u (%s)', 'ws-form'),
									esc_html($http_code),
									esc_html(self::get_http_status_code_description($http_code))
								)
							);

						case 'response_mapping' :

							if(count($this->response_mapping) > 0) {

								parent::success(__('Response code mapping: Process response field mapping', 'ws-form'));

								// Get response
								$response_json = $http_response['response'];

								// Decode response
								$response = json_decode($response_json);

								// Check decoded response
								if(is_null($response)) {

									parent::error(__('HTTP response is not in JSON format. Unable to process response mappings.', 'ws-form'));
									return 'halt';
								}

								// Check for array
								if(is_array($response) && (count($response) > 0)) {

									$response = $response[0];
								}

								// Check for object
								if(!is_object($response)) {

									parent::error(__('HTTP response does not contain a JSON object. Unable to process response mappings.', 'ws-form'));
									return 'halt';						
								}

								// Process response field mapping
								foreach($this->response_mapping as $response_map) {

									// Get node
									$node = $response_map['action_' . $this->id . '_endpoint_key'];
									$node = WS_Form_Common::parse_variables_process($node, $form, $submit, 'text/plain');
									if(empty($node)) { continue; }

									// Get field ID
									$field_id = $response_map['ws_form_field'];
									if(empty($field_id)) { continue; }

									// Get field value
									try {

										$field_value = WS_Form_Common::get_path_value($response, $node);

									}  catch (Exception $e) {

										// Ignore if value not found
										continue;
									}

									// Success
									parent::success(sprintf(__('Response field mapping: Field ID: %u', 'ws-form'), $field_id), array(

										array(

											'action' => 'field_value',
											'field_id' => absint($field_id),
											'value' => $field_value
										)
									));
								}
							}

							break;

						default :

							parent::success(__('Response code mapping: Ignore', 'ws-form'));
					}
				}
			}

			return true;
		}

		// Type processing
		public function type_process($value, $type) {

			// Array to delimited
			if(is_array($value) && $this->array_to_delimited) {

				$value = implode(',', $value);
			}

			if($type !== '') {

				if(is_object($value)) { $value = (array) $value; }
				if(is_array($value)) { $value = implode(',', $value); }
			}
			switch($type) {

				case 'string' :

					$value = strval($value);
					break;

				case 'integer' :

					$value = intval(trim($value));
					break;

				case 'float' :

					$value = floatval(trim($value));
					break;

				case 'boolean' :

					if($value === true) { return true; }
					if($value === false) { return false; }
					$value = trim(strtolower($value));
					$value = !empty($value) && !in_array($value, array('false', '0', 'off', 'no', 'null'));
					break;
			}

			return $value;
		}

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build config_meta_keys
			$config_meta_keys = array(

				// Request

				// Custom Endpoint
				'action_' . $this->id . '_endpoint' => array(

					'label'							=>	__('URL of Endpoint', 'ws-form'),
					'type'							=>	'text',
					'help'							=>	__('URL of endpoint to send data to.', 'ws-form'),
					'default'						=>	'https://'
				),

				// Request Method
				'action_' . $this->id . '_request_method' => array(

					'label'							=>	__('Request Method', 'ws-form'),
					'type'							=>	'select',
					'help'							=>	__('Select the HTTP request method to use.', 'ws-form'),
					'options'					=>	array(

						array('value' => 'POST', 'text' => 'POST'),
						array('value' => 'GET', 'text' => 'GET'),
						array('value' => 'PUT', 'text' => 'PUT'),
						array('value' => 'DELETE', 'text' => 'DELETE'),
						array('value' => 'PATCH', 'text' => 'PATCH'),
					),
					'default'						=>	'POST'
				),

				// Content type
				'action_' . $this->id . '_content_type' => array(

					'label'						=>	__('Content Type', 'ws-form'),
					'type'						=>	'select',
					'help'						=>	__('Select the content type.', 'ws-form'),
					'options'					=>	array(

						array('value' => 'application/json', 'text' => 'JSON (application/json)'),
						array('value' => 'application/x-www-form-urlencoded', 'text' => sprintf('%s (application/x-www-form-urlencoded)', __('URL Encoded', 'ws-form'))),
					),
					'default'					=>	'application/json',
				),

				// Field mapping
				'action_' . $this->id . '_field_mapping' => array(

					'label'						=>	__('Field Mapping', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Map WS Form fields to endpoint keys.', 'ws-form'),
					'meta_keys'					=>	array(

						'ws_form_field',
						'action_' . $this->id . '_endpoint_key',
						'action_' . $this->id . '_endpoint_type'
					)
				),

				// Custom mapping
				'action_' . $this->id . '_custom_mapping' => array(

					'label'						=>	__('Custom Mapping', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Map custom values to endpoint keys.', 'ws-form'),
					'meta_keys'					=>	array(

						'action_' . $this->id . '_endpoint_key',
						'action_' . $this->id . '_endpoint_value',
						'action_' . $this->id . '_endpoint_type'
					),
					'variable_helper'			=>	true
				),

				// Key
				'action_' . $this->id . '_endpoint_key' => array(

					'label'						=>	__('Key', 'ws-form'),
					'type'						=>	'text'
				),

				// Value
				'action_' . $this->id . '_endpoint_value' => array(

					'label'						=>	__('Value', 'ws-form'),
					'type'						=>	'text'
				),

				// Type
				'action_' . $this->id . '_endpoint_type' => array(

					'label'						=>	__('Type', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => '', 'text' => __('Source', 'ws-form')),
						array('value' => 'string', 'text' => __('String', 'ws-form')),
						array('value' => 'integer', 'text' => __('Integer', 'ws-form')),
						array('value' => 'float', 'text' => __('Float', 'ws-form')),
						array('value' => 'boolean', 'text' => __('Boolean', 'ws-form'))
					)
				),

				// Header mapping
				'action_' . $this->id . '_header_mapping' => array(

					'label'						=>	__('HTTP Header Name/Values', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Add HTTP Header name/values to your endpoint call.', 'ws-form'),
					'meta_keys'					=>	array(

						'action_' . $this->id . '_header_name',
						'action_' . $this->id . '_header_value'
					),
					'variable_helper'			=>	true
				),

				// Header name
				'action_' . $this->id . '_header_name' => array(

					'label'						=>	__('Name', 'ws-form'),
					'type'						=>	'text'
				),

				// Header value
				'action_' . $this->id . '_header_value' => array(

					'label'						=>	__('Value', 'ws-form'),
					'type'						=>	'text'
				),

				// Authentication

				// Authentication - Enable
				'action_' . $this->id . '_authentication' => array(

					'label'						=>	__('Authentication', 'ws-form'),
					'type'						=>	'select',
					'help'						=>	__('Select the type of authentication.', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 'text' => __('None', 'ws-form')),
						array('value' => 'basic', 'text' => __('Basic', 'ws-form')),
					),
					'default'					=>	'',
				),

				// Authentication - Username
				'action_' . $this->id . '_username' => array(

					'label'							=>	__('Username', 'ws-form'),
					'type'							=>	'text',
					'help'							=>	__('Authentication username.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'			=>	'!=',
							'meta_key'		=>	'action_' . $this->id . '_authentication',
							'meta_value'	=>	''
						)
					)
				),

				// Authentication - Password
				'action_' . $this->id . '_password' => array(

					'label'							=>	__('Password', 'ws-form'),
					'type'							=>	'password',
					'help'							=>	__('Authentication password.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'			=>	'!=',
							'meta_key'		=>	'action_' . $this->id . '_authentication',
							'meta_value'	=>	''
						)
					)
				),

				// Response

				// Response Process
				'action_' . $this->id . '_response_process' => array(

					'label'						=>	__('Process Response', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, WS Form will process response code and field mapping. If not checked, WS Form will not wait for a response and errors will be ignored.', 'ws-form'),
					'default'					=>	'on',
				),

				// Timeout
				'action_' . $this->id . '_timeout' => array(

					'label'						=>	__('Timeout', 'ws-form'),
					'type'						=>	'number',
					'placeholder'				=>	WS_FORM_API_CALL_TIMEOUT,
					'help'						=>	__('How long the connection should stay open in seconds.', 'ws-form'),
					'default'					=>	'',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'action_' . $this->id . '_response_process',
							'meta_value'	=>	'on'
						)
					)
				),

				// Response code handling
				'action_' . $this->id . '_response_code_mapping' => array(

					'label'						=>	__('Response Code Mapping', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Add handlers for specific response codes.', 'ws-form'),
					'meta_keys'					=>	array(

						'action_' . $this->id . '_response_code',
						'action_' . $this->id . '_response_handler'
					),
					'default'					=>	self::get_response_code_mapping_default(),
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'action_' . $this->id . '_response_process',
							'meta_value'	=>	'on'
						)
					)
				),

				// Response code
				'action_' . $this->id . '_response_code' => array(

					'label'						=>	__('Code', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	self::get_http_status_codes_options()
				),

				// Response handler
				'action_' . $this->id . '_response_handler' => array(

					'label'						=>	__('Handler', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => '', 'text' => __('Ignore', 'ws-form')),
						array('value' => 'response_mapping', 'text' => __('Process Response Field Mapping', 'ws-form')),
						array('value' => 'error', 'text' => __('Throw error', 'ws-form')),
						array('value' => 'halt', 'text' => __('Halt', 'ws-form')),
					)
				),

				// Response mapping
				'action_' . $this->id . '_response_mapping' => array(

					'label'						=>	__('Response Field Mapping', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Map response values to WS Form fields. Response must be in JSON format.', 'ws-form'),
					'meta_keys'					=>	array(

						'action_' . $this->id . '_endpoint_key',
						'ws_form_field'
					),
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'action_' . $this->id . '_response_process',
							'meta_value'	=>	'on'
						)
					)
				),

				// Settings

				// SSL Verify
				'action_' . $this->id . '_ssl_verify' => array(

					'label'						=>	__('SSL Verify', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('Whether to verify SSL for the request.', 'ws-form'),
					'default'					=>	'on',
				),

				// File to URL
				'action_' . $this->id . '_file_to_url' => array(

					'label'						=>	__('Use URLs for File Fields', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, file and signature fields will be converted to URLs instead of file objects. Multiple files are comma separated.', 'ws-form'),
					'default'					=>	'on',
				),

				// Ignore empty values
				'action_' . $this->id . '_ignore_empty' => array(

					'label'						=>	__('Ignore Empty Values', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, keys with an empty value will not be included in the request.', 'ws-form'),
					'default'					=>	'',
				),

				// Array to delimited
				'action_' . $this->id . '_array_to_delimited' => array(

					'label'						=>	__('Convert Arrays to Delimited Text', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, fields that have multiple values (e.g. Checkboxes) will be converted to comma separated values.', 'ws-form'),
					'default'					=>	'on',
				),

				// Array deduplication
				'action_' . $this->id . '_array_dedupe' => array(

					'label'						=>	__('Deduplicate Arrays', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, multiple mappings to the same key will be deduplicated.', 'ws-form'),
					'default'					=>	'',
				),

				// Cookie passthrough
				'action_' . $this->id . '_cookie_passthrough' => array(

					'label'						=>	__('Cookie Passthrough', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('Whether to pass cookies from the form submission through to the API request. Applies to same host requests only.', 'ws-form'),
					'default'					=>	'',
				),
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}

		// Look HTTP status code description
		public function get_http_status_code_description($http_status_code) {

			// Get HTTP status codes
			$http_status_codes = self::get_http_status_codes();

			return isset($http_status_codes[$http_status_code]) ? $http_status_codes[$http_status_code] : __('Unknown status code', 'ws-form');
		}

		// Get HTTP status code options
		public function get_http_status_codes_options() {

			// Return array
			$return_array = array();

			// Get HTTP status codes
			$http_status_codes = self::get_http_status_codes();

			foreach($http_status_codes as $value => $text) {

				$return_array[] = array(

					'value' => (string) $value,
					'text' => sprintf('%u (%s)', $value, $text)
				);
			}

			return $return_array;
		}

		// Get HTTP status codes
		public function get_http_status_codes() {

			return array(

				101 => 'Switching Protocols',
				102 => 'Processing',
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				207 => 'Multi-Status',
				208 => 'Already Reported',
				226 => 'IM Used',
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				306 => 'Reserved',
				307 => 'Temporary Redirect',
				308 => 'Permanent Redirect',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				422 => 'Unprocessable Entity',
				423 => 'Locked',
				424 => 'Failed Dependency',
				426 => 'Upgrade Required',
				428 => 'Precondition Required',
				429 => 'Too Many Requests',
				430 => 'Unassigned',
				431 => 'Request Header Fields Too Large',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				506 => 'Variant Also Negotiates (Experimental',
				507 => 'Insufficient Storage',
				508 => 'Loop Detected',
				510 => 'Not Extended',
				511 => 'Network Authentication Required'
			);
		}

		// Default response code mapping
		public function get_response_code_mapping_default() {

			return array(

				// OK
				array(

					'action_' . $this->id . '_response_code' => '200',
					'action_' . $this->id . '_response_handler' => 'response_mapping'
				),

				// Created
				array(

					'action_' . $this->id . '_response_code' => '201',
					'action_' . $this->id . '_response_handler' => 'response_mapping'
				),

				// No content
				array(

					'action_' . $this->id . '_response_code' => '204',
					'action_' . $this->id . '_response_handler' => ''
				),

				// Not modified
				array(

					'action_' . $this->id . '_response_code' => '304',
					'action_' . $this->id . '_response_handler' => ''
				),

				// Bad request
				array(

					'action_' . $this->id . '_response_code' => '400',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Unauthorized
				array(

					'action_' . $this->id . '_response_code' => '401',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Forbidden
				array(

					'action_' . $this->id . '_response_code' => '403',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Not found
				array(

					'action_' . $this->id . '_response_code' => '404',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Not acceptable
				array(

					'action_' . $this->id . '_response_code' => '406',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Request timeout
				array(

					'action_' . $this->id . '_response_code' => '408',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Conflict
				array(

					'action_' . $this->id . '_response_code' => '409',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Server error
				array(

					'action_' . $this->id . '_response_code' => '500',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Not implemented
				array(

					'action_' . $this->id . '_response_code' => '501',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Bad gateway
				array(

					'action_' . $this->id . '_response_code' => '502',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Service unavailable
				array(

					'action_' . $this->id . '_response_code' => '503',
					'action_' . $this->id . '_response_handler' => 'error'
				),

				// Gateway timeout
				array(

					'action_' . $this->id . '_response_code' => '504',
					'action_' . $this->id . '_response_handler' => 'error'
				)
			);
		}

		// Process wp_error_process
		public function wp_error_process($post) {

			$error_messages = $post->get_error_messages();
			self::error_js($error_messages);
		}

		// Error
		public function error_js($error_messages) {

			if(!is_array($error_messages)) { $error_messages = array($error_messages); }

			foreach($error_messages as $error_message) {

				// Show the message
				parent::error($error_message, array(

					array(

						'action' => 'message',
						'message' => $error_message,
						'type' => 'danger',
						'method' => $this->message_method,
						'clear' => $this->message_clear,
						'scroll_top' => $this->message_scroll_top,
						'duration' => $this->message_duration,
						'form_hide' => $this->message_form_hide,
						'form_show' => $this->message_form_hide,
						'message_hide' => false
					)
				));
			}
		}

		// Load config for this action
		public function load_config($config = array()) {

			// Request
			$this->endpoint = parent::get_config($config, 'action_' . $this->id . '_endpoint', '');
			$this->request_method = parent::get_config($config, 'action_' . $this->id . '_request_method');
			if(!in_array($this->request_method, array('POST', 'GET', 'PUT', 'DELETE', 'PATCH'))) { $this->request_method = 'POST'; }
			$this->content_type = parent::get_config($config, 'action_' . $this->id . '_content_type', '');
			$this->field_mapping = parent::get_config($config, 'action_' . $this->id . '_field_mapping', array());
			if(!is_array($this->field_mapping)) { $this->field_mapping = array(); }
			$this->custom_mapping = parent::get_config($config, 'action_' . $this->id . '_custom_mapping', array());
			if(!is_array($this->custom_mapping)) { $this->custom_mapping = array(); }
			$this->header_mapping = parent::get_config($config, 'action_' . $this->id . '_header_mapping', array());
			if(!is_array($this->header_mapping)) { $this->header_mapping = array(); }

			// Authentication
			$this->authentication = parent::get_config($config, 'action_' . $this->id . '_authentication', '');
			$this->username = parent::get_config($config, 'action_' . $this->id . '_username', '');
			$this->password = parent::get_config($config, 'action_' . $this->id . '_password', '');

			// Response
			$this->response_process = (parent::get_config($config, 'action_' . $this->id . '_response_process', 'on') == 'on');
			$this->timeout = absint(parent::get_config($config, 'action_' . $this->id . '_timeout', WS_FORM_API_CALL_TIMEOUT));
			$this->response_mapping = parent::get_config($config, 'action_' . $this->id . '_response_mapping', array());
			if(!is_array($this->response_mapping)) { $this->response_mapping = array(); }
			$this->response_code_mapping = parent::get_config($config, 'action_' . $this->id . '_response_code_mapping', self::get_response_code_mapping_default());
			if(!is_array($this->response_code_mapping)) { $this->response_code_mapping = array(); }

			// Settings
			$this->ssl_verify = (parent::get_config($config, 'action_' . $this->id . '_ssl_verify', 'on') == 'on');
			$this->file_to_url = (parent::get_config($config, 'action_' . $this->id . '_file_to_url', 'on') == 'on');
			$this->array_to_delimited = (parent::get_config($config, 'action_' . $this->id . '_array_to_delimited', 'on') == 'on');
			$this->ignore_empty = (parent::get_config($config, 'action_' . $this->id . '_ignore_empty', '') == 'on');
			$this->array_dedupe = (parent::get_config($config, 'action_' . $this->id . '_array_dedupe', '') == 'on');
			$this->cookie_passthrough = (parent::get_config($config, 'action_' . $this->id . '_cookie_passthrough', '') == 'on');
		}


		// Get settings
		public function get_action_settings() {

			$settings = array(

				'meta_keys'		=> array(

					// Request
					'action_' . $this->id . '_endpoint',
					'action_' . $this->id . '_request_method',
					'action_' . $this->id . '_content_type',
					'action_' . $this->id . '_field_mapping',
					'action_' . $this->id . '_custom_mapping',
					'action_' . $this->id . '_header_mapping',

					// Authentication
					'action_' . $this->id . '_authentication',
					'action_' . $this->id . '_username',
					'action_' . $this->id . '_password',

					// Response
					'action_' . $this->id . '_response_process',
					'action_' . $this->id . '_timeout',
					'action_' . $this->id . '_response_code_mapping',
					'action_' . $this->id . '_response_mapping',

					// Settings
					'action_' . $this->id . '_ssl_verify',
					'action_' . $this->id . '_file_to_url',
					'action_' . $this->id . '_ignore_empty',
					'action_' . $this->id . '_array_to_delimited',
					'action_' . $this->id . '_array_dedupe',
					'action_' . $this->id . '_cookie_passthrough',
				)
			);

			// Wrap settings so they will work with sidebar_html function in admin.js
			$settings = parent::get_settings_wrapper($settings);

			// Add labels
			$settings->label = $this->label;
			$settings->label_action = $this->label_action;

			// Add multiple
			$settings->multiple = $this->multiple;

			// Add events
			$settings->events = $this->events;

			// Add can_repost
			$settings->can_repost = $this->can_repost;

			// Apply filter
			$settings = apply_filters('wsf_action_' . $this->id . '_settings', $settings);

			return $settings;
		}
	}

	new WS_Form_Action_Custom_Endpoint();
