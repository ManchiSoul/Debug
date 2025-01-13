<?php

	add_filter('wsf_config_migrate', 'wsf_migrate_formidable');

	function wsf_migrate_formidable($migrate) {

		$migrate['formidable'] = array(

			// Name of the plugin to migrate
			'label' => __('Formidable', 'ws-form'),

			// Version
			'version' => '4.x.x+',

			// Paths to detect this plugin
			'detect' => array('formidable/formidable.php', 'formidable-pro/formidable-pro.php'),

			// Tables to detect this plugins data
			'detect_table' => array('frm_forms', 'frm_fields'),

			// Forms
			'form' => array(

				// Form table configuration
				'table_record'		=> array(

					// SQL parts
					'count'			=> 'id',							// Field to use if counting records
					'select'		=> 'id,name,status,options',
					'from'			=> '#table_prefixfrm_forms',
					'join'			=> '',
					'where'			=> '',
					'where_single'	=> 'id=#form_id',
					'order_by'		=> 'name',

					// Map plugin fields to WS Form parts

					// source 		Plugin key
					// destination 	WS Form key 
					// type
					//		scratch 		Disregarded prior to DB write, used for lookups
					// 		record 			Save at record level
					// 		meta 			Save at meta level 			(Form and field data only)
					// 		meta-submit 	Save at submit meta level 	(Submission data only)
					// 		serialize 		Process as serialized data
					'map'	=> array(

						array('source' => 'id', 'type' => 'record', 'destination' => 'id'),
						array('source' => 'name', 'type' => 'record', 'destination' => 'label', 'default' => __('New Form', 'ws-form')),
						array('source' => 'status', 'type' => 'record', 'destination' => 'status', 'lookup' => array(

							array('find' => 'published', 'replace' => 'publish')
						)),
						array('source' => 'options', 'type' => 'serialize', 'map' => array(

							array('source' => 'submit_value', 'type' => 'scratch', 'destination' => 'button_submit_value'),

							array('source' => 'success_msg', 'type' => 'scratch', 'destination' => 'message_message'),
							array('source' => 'close_date', 'type' => 'meta', 'destination' => 'date_expire'),
							array('source' => 'closed_msg', 'type' => 'meta', 'destination' => 'date_expire_message'),
						))
					),
				),

				'action'	=> array(

					// Notification
					'email_blog' => array(

						'action_id'	=>	'email'
					),

					// Save to database
					'database' => array(

						'action_id'	=>	'database',
						'force'		=>	true
					),

					// Message
					'message' => array(

						'action_id'	=>	'message',
						'meta'		=>	array(

							'action_message_message' => '#message_message'
						)
					)
				)
			),

			'group' => array(

				'records' => 'inline',

				'table_record'		=> array(

					'count'		=> 'id',							// Field to use if counting records
					'select'	=> 'id,name,type,field_order,field_options',
					'from'		=> '#table_prefixfrm_fields',
					'join'		=> '',
					'where'		=> 'form_id=#form_id',
					'order_by'	=> 'field_order',

					'map'		=> array(

						array('source' => 'type', 'type' => 'scratch', 'destination' => 'type_source'),
						array('source' => 'type', 'type' => 'record', 'destination' => 'type')
					),

					'map_by_type' => array(

						'break'	=>	array(

							array('type' => 'record', 'destination' => 'id', 'value' => true),
							array('source' => 'name', 'type' => 'record', 'destination' => 'label'),
							array('group' => true),
						)
					)
				)
			),

			'section' => array(

				'records' => 'inline',

				'table_record'		=> array(

					'count'		=> 'id',							// Field to use if counting records
					'select'	=> 'id,name,type,field_order,field_options',
					'from'		=> '#table_prefixfrm_fields',
					'join'		=> '',
					'where'		=> 'form_id=#form_id',
					'order_by'	=> 'field_order',

					'map'		=> array(

						array('source' => 'type', 'type' => 'scratch', 'destination' => 'type_source'),
						array('source' => 'type', 'type' => 'record', 'destination' => 'type')
					),

					'map_by_type' => array(

						'break'	=>	array(

							array('group' => true),
						),

						'divider'	=>	array(

							array('section' => true),
							array('type' => 'record', 'destination' => 'id', 'value' => true),
							array('source' => 'name', 'type' => 'record', 'destination' => 'label')
						)
					)
				)
			),

			'field' => array(

				'table_record'		=> array(

					'count'		=> 'id',							// Field to use if counting records
					'select'	=> 'id,name,type,field_order,field_options',
					'from'		=> '#table_prefixfrm_fields',
					'join'		=> '',
					'where'		=> 'form_id=#form_id',
					'order_by'	=> 'field_order',

					'map'		=> array(

						array('source' => 'id', 'type' => 'record', 'destination' => 'id'),
						array('source' => 'name', 'type' => 'record', 'destination' => 'label'),
						array('source' => 'type', 'type' => 'scratch', 'destination' => 'type_source'),
						array('source' => 'type', 'type' => 'record', 'destination' => 'type', 'lookup' => array(

								// Basic
								array('find' => 'text', 'replace' => 'text'),
								array('find' => 'textarea', 'replace' => 'textarea'),
								array('find' => 'checkbox', 'replace' => 'checkbox'),
								array('find' => 'radio', 'replace' => 'radio'),
								array('find' => 'select', 'replace' => 'select'),
								array('find' => 'email', 'replace' => 'email'),
								array('find' => 'url', 'replace' => 'url'),
								array('find' => 'number', 'replace' => 'number'),
								array('find' => 'phone', 'replace' => 'tel'),
								array('find' => 'html', 'replace' => 'html'),
								array('find' => 'user_id', 'replace' => 'select'),
								array('find' => 'captcha', 'replace' => 'recaptcha'),

								// Advanced
								array('find' => 'file', 'replace' => 'file'),

								// Sections / Repeaters
								array('find' => 'break', 'replace' => false),
								array('find' => 'divider', 'replace' => false),
								array('find' => 'end_divider', 'replace' => false)
							)
						),
						array('source' => 'field_order', 'type' => 'record', 'destination' => 'sort_index'),
						array('source' => 'field_options', 'type' => 'serialize', 'map_by_type' => array(

						),

						'map' => array(

							array('source' => 'default_blank', 'type' => 'meta', 'destination' => 'default_value'),

							array('source' => 'invalid', 'type' => 'meta', 'destination' => 'invalid_feedback'),
							array('source' => 'required', 'type' => 'meta', 'destination' => 'required', 'lookup' => array(

								array('find' => '', 'replace' => ''),
								array('find' => '1', 'replace' => 'on')
							)),
							array('source' => 'placeholder', 'type' => 'meta', 'destination' => 'placeholder'),
							array('source' => 'step', 'type' => 'meta', 'destination' => 'step'),
							array('source' => 'max', 'type' => 'meta', 'destination' => 'max_length'),
						))
					),

					'map_by_type' => array(

						'break'	=>	array(

							array('group' => true)
						),

						'divider'	=>	array(

							array('section' => true)
						)
					)
				),

				// Field type processing
				// br_to_newline 	Convert br tags to newlines
				// strip_tags 		Strip HTML tags
				// csv_to_array 	Comma separated values to array
				'process'	=>	array(

					// WS Form field type
/*					'textarea'		=>	array(array('process' => 'br_to_newline'), array('process' => 'strip_tags')),
					'checkbox'		=>	array(array('process' => 'csv_to_array')),
					'radio'			=>	array(array('process' => 'csv_to_array')),
					'select'		=>	array(array('process' => 'csv_to_array')),
					'signature' 	=>	array(array('process' => 'img_base64_to_file')),
					'file' 			=>	array(array('process' => 'upload_url_to_file'))
*/				)
			),

			'submission' => array(

				'table_record'		=> array(

					'count'		=> 'id',							// Field to use if counting records
					'select'	=> 'id,created_at,updated_at',
					'from' 		=> '#table_prefixfrm_items',
					'join'		=> '',
					'where' 	=> 'form_id = \'#form_id\'',

					'map'	=> array(

						// Mandatory mappings
						array('source' => 'id', 'type' => 'record', 'destination' => 'id'),
						array('source' => 'user_id', 'type' => 'record', 'destination' => 'user_id'),

						// Optional mappings
						array('source' => 'created_at', 'type' => 'record', 'destination' => 'date_added'),
						array('source' => 'updated_at', 'type' => 'record', 'destination' => 'date_updated')
					),

					'limit' => 25
				),

				'table_metadata'	=> array(

					'select' 	=> 'id,field_id',
					'from' 		=> '#table_prefixitem_metas',
					'join' 		=> '',
					'where' 	=> 'item_id=#record_id',

					'meta_key_mask' => '_formidable_field-#id',
				)
			)
		);

		return $migrate;
	}