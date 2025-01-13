<?php

	//	Framework config: Bootstrap 4

	class WS_Form_Config_Framework_Bootstrap_4 {

		// Configuration - Frameworks
		public static function get_framework_config() {

			return array(

				'name'						=>	__('Bootstrap 4.0', 'ws-form'),

				'default'					=>	false,

				'css_file'					=>	'bootstrap4.css',

				'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

				'columns'					=>	array(

					'column_class'			=>	'col-#id-#size',
					'column_css_selector'	=>	'.col-#id-#size',
					'offset_class'			=>	'offset-#id-#offset',
					'offset_css_selector'	=>	'.offset-#id-#offset'
				),

				'breakpoints'				=>	array(

					// Up to 575px
					25	=>	array(
						'id'					=>	'xs',
						'name'					=>	__('Extra Small', 'ws-form'),
						'column_class'			=>	'col-#size',
						'column_css_selector'	=>	'.col-#size',
						'offset_class'			=>	'offset-#offset',
						'offset_css_selector'	=>	'.offset-#offset',
						'admin_max_width'		=>	575,
						'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
					),
					// Up to 767px
					50	=>	array(
						'id'				=>	'sm',
						'name'				=>	__('Small', 'ws-form'),
						'admin_max_width'	=>	767,
						'min_width'			=>	576
					),

					// Up to 991px
					75	=>	array(
						'id'				=>	'md',
						'name'				=>	__('Medium', 'ws-form'),
						'admin_max_width'	=>	991,
						'min_width'			=>	768
					),

					// Up to 1199px
					100	=>	array(
						'id'				=>	'lg',
						'name'				=>	__('Large', 'ws-form'),
						'admin_max_width'	=>	1199,
						'min_width'			=>	992
					),

					// 1200+
					125	=>	array(
						'id'				=>	'xl',
						'name'				=>	__('Extra Large', 'ws-form'),
						'min_width'			=>	1200
					)
				),

				'form' => array(

					'admin' => array('mask_single' => '#form'),
					'public' => array(

						'mask_single' 	=> '#label#form',
						'mask_label'	=> '<h2>#label</h2>',
					),
				),

				'tabs' => array(

					'public' => array(

						'mask_wrapper'		=>	'<ul class="nav nav-tabs mb-3 #class" role="tablist"#attributes>#tabs</ul>',
						'mask_single'		=>	'<li class="nav-item" role="tab"#attributes><a class="nav-link" href="#href" data-toggle="tab">#label</a></li>',
						'activate_js'		=>	"$('#form ul.nav-tabs li:eq(#index) a').tab('show');",
						'event_js'			=>	'shown.bs.tab',
						'event_type_js'		=>	'tab',
						'class_disabled'	=>	'disabled',
						'class_active'		=>	'active'
					),
				),

				'message' => array(

					'public'	=>	array(

						'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

						'types'	=>	array(

							'success'		=>	array('mask_wrapper_class' => 'alert-success', 'text_class' => 'text-success'),
							'information'	=>	array('mask_wrapper_class' => 'alert-info', 'text_class' => 'text-info'),
							'warning'		=>	array('mask_wrapper_class' => 'alert-warning', 'text_class' => 'text-warning'),
							'danger'		=>	array('mask_wrapper_class' => 'alert-danger', 'text_class' => 'text-danger')
						)
					)
				),

				'action_js' => array(

					'message'	=>	array(

						'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

						'types'	=>	array(

							'success'		=>	array('mask_wrapper_class' => 'alert-success'),
							'information'	=>	array('mask_wrapper_class' => 'alert-info'),
							'warning'		=>	array('mask_wrapper_class' => 'alert-warning'),
							'danger'		=>	array('mask_wrapper_class' => 'alert-danger')
						)
					)
				),

				'groups' => array(

					'public' => array(

						'mask_wrapper'	=>	'<div class="tab-content">#groups</div>',
						'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index" role="tabpanel"#attributes>#label#group</div>',
						'mask_label' 	=> '<h3>#label</h3>',
						'class'			=> 'tab-pane',
						'class_active'	=> 'active',
					)
				),

				'sections' => array(

					'public' => array(

						'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
						'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
					)
				),

				'fields' => array(

					'public' => array(

						// Label position - Left
						'left' => array(

							'mask'							=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label text-right">#label</div>',
							'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
						),

						// Label position - Right
						'right' => array(

							'mask'							=>	'<div class="row">#field</div>',
							'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label">#label</div>',
							'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
						),

						// Masks
						'mask_wrapper' 			=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
						'mask_wrapper_label'	=> '<legend>#label</legend>',
						'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

						// Input group
						'mask_field_input_group'			=>	'#pre_label#pre_help<div class="input-group#css_input_group">#field#post_label#invalid_feedback</div>#post_help',
						'mask_field_input_group_prepend'	=>	'<div class="input-group-prepend"><span class="input-group-text">#prepend</span></div>',
						'mask_field_input_group_append'		=>	'<div class="input-group-append"><span class="input-group-text">#append</span></div>',

						// Required
						'mask_required_label'	=> ' <strong class="text-danger">*</strong>',

						// Help
						'mask_help'				=>	'<small id="#help_id" class="#help_class"#attributes>#help#help_append</small>',

						// Invalid feedback
						'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class" aria-hidden="true"#attributes>#invalid_feedback</div>',

						// Classes - Default
						'class_single'					=> array('form-group'),
		//								'class_single_required'			=> array('required'),
						'class_field'					=> array('form-control'),
						'class_field_label'				=> array(),
						'class_help_pre'				=> array('form-text', 'text-muted'),
						'class_help_post'				=> array('form-text', 'text-muted'),
						'class_invalid_feedback'		=> array('invalid-feedback'),
						'class_inline' 					=> array('form-inline'),
						'class_form_validated'			=> array('was-validated'),
						'class_orientation_wrapper'		=> array('row'),
						'class_orientation_row'			=> array(),
						'class_single_vertical_align'	=> array(

							'middle'	=>	'align-self-center',
							'bottom'	=>	'align-self-end'
						),
						'class_field_button_type'	=> array(

							'default'		=>	'btn-secondary',
							'primary'		=>	'btn-primary',
							'secondary'		=>	'btn-secondary',
							'success'		=>	'btn-success',
							'information'	=>	'btn-info',
							'warning'		=>	'btn-warning',
							'danger'		=>	'btn-danger'
						),
						'class_field_message_type'	=> array(

							'success'		=>	'alert-success',
							'information'	=>	'alert-info',
							'warning'		=>	'alert-warning',
							'danger'		=>	'alert-danger'
						),

						// Classes - Custom by field type
						'field_types'		=> array(

							'select' 	=> array(
								'class_field'			=> array('custom-select')
							),

							'checkbox' 	=> array(
								
								'class_field'			=> array(),
								'class_row'				=> array(),
								'class_row_disabled'	=> array('disabled'),
								'class_row_field'		=> array('custom-control-input'),
								'class_row_field_label'	=> array('custom-control-label'),
								'class_inline' 			=> array('custom-control-inline'),
								'mask_field'			=> '#pre_label#pre_help<div#attributes>#datalist</div>#post_label#invalid_feedback#post_help',
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '<div class="custom-control custom-checkbox">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback</div>',
							),

							'radio' 	=> array(

								'class_field'			=> array(),
								'class_row'				=> array(),
								'class_row_disabled'	=> array('disabled'),
								'class_row_field'		=> array('custom-control-input'),
								'class_row_field_label'	=> array('custom-control-label'),
								'class_inline' 			=> array('custom-control-inline'),
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '<div class="custom-control custom-radio">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label>#invalid_feedback</div>'
							),

							'spacer' 	=> array(
								'class_single'			=> array(),
							),

							'section_icons' 	=> array(

								'class_field'			=> array(),
							),

							'price_select' 	=> array(
								'class_field'			=> array('custom-select'),
							),

							'price_checkbox' 	=> array(

								'class_field'			=> array(),
								'class_row'				=> array(),
								'class_row_disabled'	=> array('disabled'),
								'class_row_field'		=> array('custom-control-input'),
								'class_row_field_label'	=> array('custom-control-label'),
								'class_inline' 			=> array('custom-control-inline'),
								'mask_field'			=> '#pre_label#pre_help<div#attributes>#datalist</div>#post_label#post_help',
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '<div class="custom-control custom-checkbox">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_price_field_label</label>#invalid_feedback</div>',
							),

							'price_radio' 	=> array(

								'class_field'			=> array(),
								'class_row'				=> array(),
								'class_row_disabled'	=> array('disabled'),
								'class_row_field'		=> array('custom-control-input'),
								'class_row_field_label'	=> array('custom-control-label'),
								'class_inline' 			=> array('custom-control-inline'),
								'mask_group'			=> '<fieldset#disabled>#group_label#group</fieldset>',
								'mask_row_label'		=> '<div class="custom-control custom-radio">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_price_field_label</label>#invalid_feedback</div>'
							),
							'recaptcha' => array(

								'class_invalid_field'	=> array('is-invalid'),
								'class_valid_field'		=> array('is-valid'),
								'class_field'			=> array()
							),

							'hcaptcha' => array(

								'class_invalid_field'	=> array('is-invalid'),
								'class_valid_field'		=> array('is-valid'),
								'class_field'			=> array()
							),

							'turnstile' => array(

								'class_invalid_field'	=> array('is-invalid'),
								'class_valid_field'		=> array('is-valid'),
								'class_field'			=> array()
							),
							'file' 	=> array(

								'mask_field'						=> '#pre_help<input type="file" id="#id" name="#name"#attributes />#label#invalid_feedback#post_help',
								'mask_single' 						=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"><div class="custom-file">#field</div></div>',
								'mask_single_dropzonejs' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type">#field</div>',
								'mask_help_dropzonejs_preview'				=>	'<small class="#help_class"#attributes>#help#help_append</small>',
								'mask_invalid_feedback_dropzonejs_preview'	=>	'<div class="#invalid_feedback_class"#attributes>#invalid_feedback</div>',
								'class_field'						=> array('custom-file-input'),
								'class_field_dropzonejs'			=> array(),
								'class_field_label'					=> array('custom-file-label'),
								'class_field_label_dropzonejs'		=> array()
							),

							'submit' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'primary'
							),

							'clear' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),
							'reset' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),

							'tab_previous' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),

							'tab_next' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),
							'section_add' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),

							'section_delete' 	=> array(
								'class_field'		=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'danger'
							),

							'section_up' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),

							'section_down' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),

							'save' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),

							'button' 	=> array(
								'class_field'						=> array('btn'),
								'class_field_full_button'			=> array('btn-block'),
								'class_field_button_type_fallback'	=> 'secondary'
							),

							'message' 	=> array(
								'class_field'						=> array('alert')
							),

							'progress'	=> array(
								'class_field'					=> array('progress-bar'),
								'class_complete'				=> array('bg-success'),
								'mask_field'					=>	'#pre_label#pre_help<div class="progress" id="#id"><div data-progress-bar data-progress-bar-value data-value="0" role="progressbar" style="width: 0%" aria-valuenow="#value" aria-valuemin="0" aria-valuemax="1"#attributes></div></div>#post_help#post_label',
								'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label'),
							)
						)
					)
				)
			);
		}
	}