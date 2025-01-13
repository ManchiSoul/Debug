<?php

	class WS_Form_Config_Customize extends WS_Form_Config {

		// Configuration - Skins
		public static function get_skins($include_conversational = true) {

			$skins = array(

				'ws_form'			=>	array(

					'label'				=>	WS_FORM_NAME_GENERIC,

					'setting_id_prefix'	=>	'',

					'defaults'			=>	array(

						// Colors
						'color_default'					=> '#000000',
						'color_default_inverted' 		=> '#ffffff',
						'color_default_light' 			=> '#767676',
						'color_default_lighter' 		=> '#ceced2', 
						'color_default_lightest' 		=> '#efeff4',
						'color_primary'					=> '#205493',
						'color_secondary'				=> '#5b616b',
						'color_success'					=> '#2e8540',
						'color_information'				=> '#02bfe7',
						'color_warning'					=> '#fdb81e',
						'color_danger'					=> '#bb0000',
						'color_form_background'			=> '',

						// Typography
						'font_family'					=> 'inherit',
						'font_size' 					=> 16,
						'font_size_large'				=> 18,
						'font_size_small'				=> 14,
						'font_weight'					=> 'inherit',
						'line_height'					=> 1.4,

						// Border
						'border'						=> true,
						'border_width'					=> 1,
						'border_style'					=> 'solid',
						'border_radius'					=> 4,

						// Box shadow
						'box_shadow'					=> true,
						'box_shadow_width' 				=> 2,
						'box_shadow_color_opacity'		=> 1,

						// Transition
						'transition'					=> true,
						'transition_speed'				=> 200,
						'transition_timing_function'	=> 'ease-in-out',

						// Advanced
						'grid_gutter'					=> 20,
						'spacing'						=> 10,
						'spacing_small'					=> 5,
						'label_position_inside_mode'	=> 'move',
						'label_column_inside_scale'		=> 0.9
					)
				)
			);

			if($include_conversational) {

				$skins['ws_form_conv'] = array(

					'label'				=>	sprintf(__('%s - Conversational', 'ws-form'), WS_FORM_NAME_GENERIC),

					'conversational'	=>	true,

					'setting_id_prefix'	=>	'conv',

					'defaults'			=>	array(

						// Colors
						'color_default'					=> '#000000',
						'color_default_inverted' 		=> '#ffffff',
						'color_default_light' 			=> '#767676',
						'color_default_lighter' 		=> '#ceced2',
						'color_default_lightest' 		=> '#efeff4',
						'color_primary'					=> '#205493',
						'color_secondary'				=> '#5b616b',
						'color_success'					=> '#2e8540',
						'color_information'				=> '#02bfe7',
						'color_warning'					=> '#fdb81e',
						'color_danger'					=> '#bb0000',
						'color_form_background'			=> '#ffffff',

						// Typography
						'font_family'					=> '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
						'font_size' 					=> 22,
						'font_size_large'				=> 26,
						'font_size_small'				=> 18,
						'font_weight'					=> 'normal',
						'line_height'					=> 1.4,

						// Border
						'border'						=> true,
						'border_width'					=> 2,
						'border_style'					=> 'solid',
						'border_radius'					=> 4,

						// Box shadow
						'box_shadow'					=> true,
						'box_shadow_width' 				=> 2,
						'box_shadow_color_opacity'		=> 1,

						// Transition
						'transition'					=> true,
						'transition_speed'				=> 200,
						'transition_timing_function'	=> 'ease-in-out',

						// Advanced
						'grid_gutter'					=> 40,
						'spacing'						=> 20,
						'spacing_small'					=> 10,
						'label_position_inside_mode'	=> 'move',
						'label_column_inside_scale'		=> 0.9,

						// Conversational
						'conversational_max_width'					=> '800px',
						'conversational_color_background'			=> '#efeff4',
						'conversational_color_background_nav'		=> '#585858',
						'conversational_color_foreground_nav'		=> '#ffffff',
						'conversational_opacity_section_inactive'	=> '0.25'
					)
				);
			}

			foreach($skins as $skin_id => $skin) {

				$defaults = $skins[$skin_id]['defaults'];

				$skins[$skin_id]['defaults']['label_column_inside_offset'] = -(round(($defaults['font_size'] * $defaults['line_height']) / 2) + 10 - $defaults['border_width']);
			}

			// Apply filter
			$skins = apply_filters('wsf_config_skins', $skins);

			return $skins;
		}

		// Configuration - Customize
		public static function get_customize() {

			$customize	=	array(

				'colors'	=>	array(

					'heading'	=>	__('Colors', 'ws-form'),

					'fields'	=>	array(

						'color_default'	=> array(

							'label'			=>	__('Default', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Labels, field values, and help text.', 'ws-form')
						),

						'color_default_inverted'	=> array(

							'label'			=>	__('Inverted', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Field backgrounds and button text.', 'ws-form')
						),

						'color_default_light'	=> array(

							'label'			=>	__('Light', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Placeholders and disabled field values.', 'ws-form')
						),

						'color_default_lighter'	=> array(

							'label'			=>	__('Lighter', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Field borders and buttons.', 'ws-form')
						),

						'color_default_lightest'	=> array(

							'label'			=>	__('Lightest', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Range slider backgrounds, progress bar backgrounds, and disabled field backgrounds.', 'ws-form')
						),

						'color_primary'	=> array(

							'label'			=>	__('Primary', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Checkboxes, radios, range sliders, progress bars, and submit buttons.')
						),

						'color_secondary'	=> array(

							'label'			=>	__('Secondary', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Secondary elements such as a reset button.', 'ws-form')
						),

						'color_success'	=> array(

							'label'			=>	__('Success', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Completed progress bars, save buttons, and success messages.')
						),

						'color_information'	=> array(

							'label'			=>	__('Information', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Information messages.', 'ws-form')
						),

						'color_warning'	=> array(

							'label'			=>	__('Warning', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Warning messages.', 'ws-form')
						),

						'color_danger'	=> array(

							'label'			=>	__('Danger', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Required field labels, invalid field borders, invalid feedback, remove repeatable section buttons, and danger messages.')
						),

						'color_form_background'	=> array(

							'label'			=>	__('Form Background', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Leave blank for none.', 'ws-form')
						)
					)
				),

				'typography'	=>	array(

					'heading'		=>	__('Typography', 'ws-form'),

					'fields'		=>	array(

						'font_family'	=> array(

							'label'			=>	__('Font Family', 'ws-form'),
							'type'			=>	'text',
							'description'	=>	__('Font family used throughout the form.', 'ws-form')
						),

						'font_size'	=> array(

							'label'			=>	__('Font Size', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Regular font size used on the form.', 'ws-form')
						),

						'font_size_large'	=> array(

							'label'			=>	__('Font Size Large', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Font size used for section labels and fieldset legends.', 'ws-form')
						),

						'font_size_small'	=> array(

							'label'			=>	__('Font Size Small', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Font size used for help text and invalid feedback text.', 'ws-form')
						),

						'font_weight'	=>	array(

							'label'			=>	__('Font Weight', 'ws-form'),
							'type'			=>	'select',
							'choices'		=>	array(

								'inherit'	=>	__('Inherit', 'ws-form'),
								'normal'	=>	__('Normal', 'ws-form'),
								'bold'		=>	__('Bold', 'ws-form'),
								'100'		=>	'100',
								'200'		=>	'200',
								'300'		=>	'300',
								'400'		=>	'400 (' . __('Normal', 'ws-form') . ')',
								'500'		=>	'500',
								'600'		=>	'600',
								'700'		=>	'700 (' . __('Bold', 'ws-form') . ')',
								'800'		=>	'800',
								'900'		=>	'900'
							),
							'description'	=>	__('Font weight used throughout the form.', 'ws-form')
						),


						'line_height'	=> array(

							'label'			=>	__('Line Height', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Line height used throughout form.', 'ws-form')
						)
					)
				),

				'borders'	=>	array(

					'heading'		=>	__('Borders', 'ws-form'),

					'fields'		=>	array(

						'border'	=>	array(

							'label'			=>	__('Enabled', 'ws-form'),
							'type'			=>	'checkbox',
							'description'	=>	__('When checked, borders will be shown.', 'ws-form')
							),

						'border_width'	=> array(

							'label'			=>	__('Width', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Specify the width of borders used through the form. For example, borders around form fields.', 'ws-form')
						),

						'border_style'	=>	array(

							'label'			=>	__('Style', 'ws-form'),
							'type'			=>	'select',
							'choices'		=>	array(

								'dashed'	=>	__('Dashed', 'ws-form'),
								'dotted'	=>	__('Dotted', 'ws-form'),
								'double'	=>	__('Double', 'ws-form'),
								'groove'	=>	__('Groove', 'ws-form'),
								'inset'		=>	__('Inset', 'ws-form'),
								'outset'	=>	__('Outset', 'ws-form'),
								'ridge'		=>	__('Ridge', 'ws-form'),
								'solid'		=>	__('Solid', 'ws-form')
							),
							'description'	=>	__('Border style used throughout the form.', 'ws-form')
						),

						'border_radius'	=> array(

							'label'			=>	__('Radius', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Border radius used throughout the form.', 'ws-form')
						)
					)
				),

				'box_shadows'	=>	array(

					'heading'		=>	__('Box Shadows', 'ws-form'),

					'fields'		=>	array(

						'box_shadow'	=>	array(

							'label'			=>	__('Enabled', 'ws-form'),
							'type'			=>	'checkbox',
							'description'	=>	__('When checked, box shadows will be shown.', 'ws-form')
							),

						'box_shadow_width'	=> array(

							'label'			=>	__('Width', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Specify the width of box shadows used through the form. For example, box shadows around focused form fields.', 'ws-form')
						),

						'box_shadow_color_opacity'	=> array(

							'label'			=>	__('Opacity', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Specify the opacity of box shadows used through the form. (e.g. 0 is fully transparent and 1 is fully opaque)', 'ws-form')
						)
					)
				),

				'transitions'	=>	array(

					'heading'	=>	__('Transitions', 'ws-form'),

					'fields'	=>	array(

						'transition'	=>	array(

							'label'			=>	__('Enabled', 'ws-form'),
							'type'			=>	'checkbox',
							'description'	=>	__('When checked, transitions will be used on the form.', 'ws-form')
						),

						'transition_speed'	=> array(

							'label'			=>	__('Speed', 'ws-form'),
							'type'			=>	'number',
							'help'			=>	__('Value in milliseconds.', 'ws-form'),
							'description'	=>	__('Transition speed in milliseconds.', 'ws-form')
						),

						'transition_timing_function'	=>	array(

							'label'			=>	__('Timing Function', 'ws-form'),
							'type'			=>	'select',
							'choices'		=>	array(

								'ease'			=>	__('Ease', 'ws-form'),
								'ease-in'		=>	__('Ease In', 'ws-form'),
								'ease-in-out'	=>	__('Ease In Out', 'ws-form'),
								'ease-out'		=>	__('Ease Out', 'ws-form'),
								'linear'		=>	__('Linear', 'ws-form'),
								'step-end'		=>	__('Step End', 'ws-form'),
								'step-start'	=>	__('Step Start', 'ws-form')
							),
							'description'	=>	__('Speed curve of the transition effect.', 'ws-form')
						)
					)
				),

				'advanced'	=>	array(

					'heading'	=>	__('Advanced', 'ws-form'),

					'fields'	=>	array(

						'grid_gutter'	=> array(

							'label'			=>	__('Grid Gutter', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Sets the distance between form elements.', 'ws-form')
						),

						'spacing'	=> array(

							'label'			=>	__('Spacing', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Spacing used for section legends, checkboxes, and radios', 'ws-form')
						),

						'spacing_small'	=> array(

							'label'			=>	__('Spacing Small', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Spacing used for field labels, help text, invalid feedback, ratings, and section icons.', 'ws-form')
						),

						'label_position_inside_mode'	=>	array(

							'label'			=>	__('Inside Label Behavior', 'ws-form'),
							'type'			=>	'select',
							'choices'		=>	array(

								'move'			=>	__('Move', 'ws-form'),
								'hide'			=>	__('Hide', 'ws-form')
							),
							'description'	=>	__('Select the behavior of the label if content is present in a field.', 'ws-form')
						),

						'label_column_inside_offset'	=>	array(

							'label'			=>	__('Inside Label Vertical Offset', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('How many pixels to move the label vertically if content is present in a field.', 'ws-form')
						),

						'label_column_inside_scale'	=>	array(

							'label'			=>	__('Inside Label Scale', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('What factor to scale the label by if content is present in a field.', 'ws-form')
						)
					)
				),

				'conversational'	=>	array(

					'heading'	=>	__('Conversational', 'ws-form'),

					'skin_ids'	=>	array('ws_form_conv'),

					'fields'	=>	array(

						'conversational_max_width'	=> array(

							'label'			=>	__('Form Maximum Width', 'ws-form'),
							'type'			=>	'text',
							'description'	=>	__('Sets the max width of the conversational form.', 'ws-form')
						),

						'conversational_color_background'	=> array(

							'label'			=>	__('Background Color', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Leave blank for none.', 'ws-form')
						),

						'conversational_color_background_nav'	=> array(

							'label'			=>	__('Navigation Background Color', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Leave blank for none.', 'ws-form')
						),

						'conversational_color_foreground_nav'	=> array(

							'label'			=>	__('Navigation Foreground Color', 'ws-form'),
							'type'			=>	'color',
							'description'	=>	__('Leave blank for none.', 'ws-form')
						),

						'conversational_opacity_section_inactive'	=> array(

							'label'			=>	__('Inactive Section Opacity', 'ws-form'),
							'type'			=>	'number',
							'description'	=>	__('Leave blank for none.', 'ws-form')
						)
					)
				)
			);

			// Apply filter
			$customize = apply_filters('wsf_config_customize', $customize);

			return $customize;
		}
	}