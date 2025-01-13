<?php

	class WS_Form_Config_Styler extends WS_Form_Config {

		// Configuration - Styler
		public static function get_styler() {

			$meta = array(

				// Settings
				'setting' => array(

					'label' => __('Settings', 'ws-form'),

					'meta' => array(
						'alt' => array(

							'label' => __('Enable Alternative Color Scheme', 'ws-form'),
							'type' => 'checkbox',
							'default' => '',
							'setting' => true
						),
					),
				),

				// Forms
				'form' => array(

					'label' => __('Form', 'ws-form'),

					'children' => array(

						'color' => array(

							'label' => __('Color Palette', 'ws-form'),

							'meta' => array(

								'form_color_background' => array(

									'label' => __('Background', 'ws-form'),
									'var' => '--wsf-form-color-background',
									'type' => 'color',
									'default' => WS_Form_Color::get_color_background(),
									'default_alt' => WS_Form_Color::get_color_background_alt(),
									'default_conv' => '#ffffff',
									'legacy_v1_option_key' => 'skin_color_form_background',
								),

								'form_color_base' => array(

									'label' => __('Base', 'ws-form'),
									'var' => '--wsf-form-color-base',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_base(),
									'legacy_v1_option_key' => 'skin_color_default',
									'legacy_v1_option_key_alt' => 'skin_color_default_inverted',
								),

								'form_color_base_contrast' => array(

									'label' => __('Base - Contrast', 'ws-form'),
									'var' => '--wsf-form-color-base-contrast',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_base_contrast(),
									'legacy_v1_option_key' => 'skin_color_default_inverted',
									'legacy_v1_option_key_alt' => 'skin_color_default',
								),

								'form_color_accent' => array(

									'label' => __('Accent', 'ws-form'),
									'var' => '--wsf-form-color-accent',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_accent(),
									'legacy_v1_option_key' => 'skin_color_primary'
								),

								'form_color_neutral' => array(

									'label' => __('Neutral', 'ws-form'),
									'var' => '--wsf-form-color-neutral',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_neutral(),
									'legacy_v1_option_key' => 'skin_color_default_lighter',
								),

								'form_color_primary' => array(

									'label' => __('Primary', 'ws-form'),
									'var' => '--wsf-form-color-primary',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_primary(),
									'legacy_v1_option_key' => 'skin_color_primary'
								),

								'form_color_secondary' => array(

									'label' => __('Secondary', 'ws-form'),
									'var' => '--wsf-form-color-secondary',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_secondary(),
									'legacy_v1_option_key' => 'skin_color_secondary'
								),

								'form_color_success' => array(

									'label' => __('Success', 'ws-form'),
									'var' => '--wsf-form-color-success',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_success(),
									'legacy_v1_option_key' => 'skin_color_success'
								),

								'form_color_info' => array(

									'label' => __('Information', 'ws-form'),
									'var' => '--wsf-form-color-info',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_info(),
									'legacy_v1_option_key' => 'skin_color_information'
								),

								'form_color_warning' => array(

									'label' => __('Warning', 'ws-form'),
									'var' => '--wsf-form-color-warning',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_warning(),
									'legacy_v1_option_key' => 'skin_color_warning'
								),

								'form_color_danger' => array(

									'label' => __('Danger', 'ws-form'),
									'var' => '--wsf-form-color-danger',
									'type' => 'color',
									'shades' => true,
									'default' => WS_Form_Color::get_color_danger(),
									'legacy_v1_option_key' => 'skin_color_danger'
								),
							),
						),

						'border' => array(

							'label' => __('Border', 'ws-form'),

							'meta' => array(

								'form_border_color' => array(

									'label' => __('Color', 'ws-form'),
									'var' => '--wsf-form-border-color',
									'type' => 'color',
									'default' => 'transparent'
								),

								'form_border_radius' => array(

									'label' => __('Radius', 'ws-form'),
									'var' => '--wsf-form-border-radius',
									'type' => 'size',
									'default' => '0px',
								),

								'form_border_width' => array(

									'label' => __('Width', 'ws-form'),
									'var' => '--wsf-form-border-width',
									'type' => 'size',
									'default' => '0px',
								),

								'form_border_style' => array(

									'label' => __('Style', 'ws-form'),
									'var' => '--wsf-form-border-style',
									'type' => 'text',
									'default' => 'solid',
									'datalist' => 'wsf-styler-datalist-border-style',
								),
							),
						),

						'spacing' => array(

							'label' => __('Spacing', 'ws-form'),

							'meta' => array(

								'form_grid_gap' => array(

									'label' => __('Grid Gap', 'ws-form'),
									'var' => '--wsf-form-grid-gap',
									'type' => 'size',
									'default' => '20px',
									'default_conv' => '40px',
									'legacy_v1_option_key' => 'skin_grid_gutter',
									'legacy_v1_suffix' => 'px'
								),

								'form_padding_horizontal' => array(

									'label' => __('Padding - Horizontal', 'ws-form'),
									'var' => '--wsf-form-padding-horizontal',
									'type' => 'size',
									'default' => '0px',
								),

								'form_padding_vertical' => array(

									'label' => __('Padding - Vertical', 'ws-form'),
									'var' => '--wsf-form-padding-vertical',
									'type' => 'size',
									'default' => '0px',
								),

								'form_caption_gap' => array(

									'label' => __('Caption Gap', 'ws-form'),
									'var' => '--wsf-form-caption-gap',
									'type' => 'calc',
									'default' => 'calc(var(--wsf-form-grid-gap) / 4)',
								),
							),
						),

						'transition' => array(

							'label' => __('Transition', 'ws-form'),

							'meta' => array(

								'form_transition_enabled' => array(

									'label' => __('Enabled', 'ws-form'),
									'var' => '--wsf-form-transition-enabled',
									'type' => 'checkbox',
									'default' => '1'
								),

								'form_transition' => array(

									'label' => __('Transition', 'ws-form'),
									'var' => '--wsf-form-transition',
									'type' => 'calc',
									'default' => 'calc(var(--wsf-form-transition-speed) * var(--wsf-form-transition-enabled)) var(--wsf-form-transition-timing-function)',
								),

								'form_transition_speed' => array(

									'label' => __('Speed', 'ws-form'),
									'var' => '--wsf-form-transition-speed',
									'type' => 'speed',
									'default' => '200ms',
									'help' => __('Value in milliseconds.', 'ws-form'),
									'description' => __('Transition speed in milliseconds.', 'ws-form'),
									'legacy_v1_option_key' => 'skin_transition_speed',
									'legacy_v1_suffix' => 'ms'
								),

								'form_transition_function' => array(

									'label' => __('Timing Function', 'ws-form'),
									'var' => '--wsf-form-transition-timing-function',
									'type' => 'text',
									'default' => 'ease-in-out',
									'datalist' => 'wsf-styler-datalist-transition-timing-function',
									'description' => __('Speed curve of the transition effect.', 'ws-form'),
									'legacy_v1_option_key' => 'skin_transition_timing_function'
								),
							),
						),

						'typography' => array(

							'label' => __('Typography', 'ws-form'),

							'meta' => array(

								'form_font_family' => array(

									'label' => __('Font Family', 'ws-form'),
									'var' => '--wsf-form-font-family',
									'type' => 'text',
									'default' => 'inherit',
									'datalist' => 'wsf-styler-datalist-font-family',
									'legacy_v1_option_key' => 'skin_font_family',
								),

								'form_font_size' => array(

									'label' => __('Font Size', 'ws-form'),
									'var' => '--wsf-form-font-size',
									'type' => 'size',
									'default' => '16px',
									'default_conv' => '22px',
									'legacy_v1_option_key' => 'skin_font_size',
									'legacy_v1_suffix' => 'px',
								),

								'form_font_size_small' => array(

									'label' => __('Font Size - Small', 'ws-form'),
									'var' => '--wsf-form-font-size-small',
									'type' => 'size',
									'default' => '14px',
									'default_conv' => '18px',
									'legacy_v1_option_key' => 'skin_font_size_small',
									'legacy_v1_suffix' => 'px',
								),

								'form_font_size_large' => array(

									'label' => __('Font Size - Large', 'ws-form'),
									'var' => '--wsf-form-font-size-large',
									'type' => 'size',
									'default' => '18px',
									'default_conv' => '26px',
									'legacy_v1_option_key' => 'skin_font_size_large',
									'legacy_v1_suffix' => 'px',
								),

								'form_font_style' => array(

									'label' => __('Font Style', 'ws-form'),
									'var' => '--wsf-form-font-style',
									'type' => 'text',
									'default' => 'inherit',
									'datalist' => 'wsf-styler-datalist-font-style',
								),

								'form_font_weight' => array(

									'label' => __('Font Weight', 'ws-form'),
									'var' => '--wsf-form-font-weight',
									'type' => 'weight',
									'default' => 'inherit',
									'datalist' => 'wsf-styler-datalist-font-weight',
									'legacy_v1_option_key' => 'skin_font_weight',
								),

								'form_letter_spacing' => array(

									'label' => __('Letter Spacing', 'ws-form'),
									'var' => '--wsf-form-letter-spacing',
									'type' => 'size',
									'default' => 'inherit',
								),

								'form_line_height' => array(

									'label' => __('Line Height', 'ws-form'),
									'var' => '--wsf-form-line-height',
									'type' => 'size',
									'default' => '1.4',
									'legacy_v1_option_key' => 'skin_line_height',
								),

								'form_text_decoration' => array(

									'label' => __('Text Decoration', 'ws-form'),
									'var' => '--wsf-form-text-decoration',
									'type' => 'text',
									'default' => 'inherit',
									'datalist' => 'wsf-styler-datalist-text-decoration',
								),

								'form_text_transform' => array(

									'label' => __('Text Transform', 'ws-form'),
									'var' => '--wsf-form-text-transform',
									'type' => 'text',
									'default' => 'inherit',
									'datalist' => 'wsf-styler-datalist-text-transform',
								),
							),
						),
					),
				),

				// Tab
				'tab' => array(

					'label' => __('Tab', 'ws-form'),

					'children' => array(

						'color_background' => array(

							'label' => __('Background Color', 'ws-form'),

							'meta' => array(

								'group_li_color_background' => array(

									'label' => __('Default', 'ws-form'),
									'var' => '--wsf-group-li-color-background',
									'type' => 'color',
									'default' => 'transparent'
								),

								'group_li_color_background_active' => array(

									'label' => __('Active', 'ws-form'),
									'var' => '--wsf-group-li-color-background-active',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base-contrast)',
								),

								'group_li_color_background_focus' => array(

									'label' => __('Focussed', 'ws-form'),
									'var' => '--wsf-group-li-color-background-focus',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base-contrast)',
								),

								'group_li_color_background_disabled' => array(

									'label' => __('Disabled', 'ws-form'),
									'var' => '--wsf-group-li-color-background-disabled',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-60)',
								),
							),
						),

						'border_style' => array(

							'label' => __('Border Style', 'ws-form'),

							'meta' => array(

								'group_li_border_radius' => array(

									'label' => __('Radius', 'ws-form'),
									'var' => '--wsf-group-li-border-radius',
									'type' => 'size',
									'default' => '4px',
									'legacy_v1_option_key' => 'skin_border_radius',
									'legacy_v1_suffix' => 'px'
								),

								'group_li_border_width' => array(

									'label' => __('Width', 'ws-form'),
									'var' => '--wsf-group-li-border-width',
									'type' => 'size',
									'default' => '1px',
									'default_conv' => '2px',
									'legacy_v1_option_key' => 'skin_border_width',
									'legacy_v1_suffix' => 'px'
								),

								'group_li_border_style' => array(

									'label' => __('Style', 'ws-form'),
									'var' => '--wsf-group-li-border-style',
									'type' => 'text',
									'default' => 'solid',
									'datalist' => 'wsf-styler-datalist-border-style',
									'legacy_v1_option_key' => 'skin_border_style',
								),
							),
						),

						'border_color' => array(

							'label' => __('Border Color', 'ws-form'),

							'meta' => array(

								'group_li_border_color' => array(

									'label' => __('Default', 'ws-form'),
									'var' => '--wsf-group-li-border-color',
									'type' => 'color',
									'default' => 'transparent'
								),

								'group_li_border_color_active' => array(

									'label' => __('Active', 'ws-form'),
									'var' => '--wsf-group-li-border-color-active',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-60)',
								),

								'group_li_border_color_focus' => array(

									'label' => __('Focussed', 'ws-form'),
									'var' => '--wsf-group-li-border-color-focus',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-60)',
								),

								'group_li_border_color_disabled' => array(

									'label' => __('Disabled', 'ws-form'),
									'var' => '--wsf-group-li-border-color-disabled',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-60)',
								),
							),
						),

						'bottom_border' => array(

							'label' => __('Bottom Border', 'ws-form'),

							'meta' => array(

								'group_ul_border_color' => array(

									'label' => __('Color', 'ws-form'),
									'var' => '--wsf-group-ul-border-color',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-60)',
								),

								'group_ul_border_width' => array(

									'label' => __('Width', 'ws-form'),
									'var' => '--wsf-group-ul-border-width',
									'type' => 'size',
									'default' => '1px',
									'legacy_v1_option_key' => 'skin_border_width',
									'legacy_v1_suffix' => 'px',
								),

								'group_ul_border_style' => array(

									'label' => __('Style', 'ws-form'),
									'var' => '--wsf-group-ul-border-style',
									'type' => 'text',
									'default' => 'solid',
									'datalist' => 'wsf-styler-datalist-border-style',
									'legacy_v1_option_key' => 'skin_border_style',
								),
							),
						),

						'size' => array(

							'label' => __('Size', 'ws-form'),

							'meta' => array_merge(

								array(

									'group_li_gap' => array(

										'label' => __('Gap', 'ws-form'),
										'var' => '--wsf-group-li-gap',
										'type' => 'size',
										'default' => '0px',
									),
								),

								self::get_styler_padding_meta('group_li', 'group-li', '16px', '8px')
							),
						),

						'text_color' => array(

							'label' => __('Text Color', 'ws-form'),

							'meta' => array(

								'group_li_color' => array(

									'label' => __('Default', 'ws-form'),
									'var' => '--wsf-group-li-color',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
								),

								'group_li_color_active' => array(

									'label' => __('Active', 'ws-form'),
									'var' => '--wsf-group-li-color-active',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
								),

								'group_li_color_focus' => array(

									'label' => __('Focussed', 'ws-form'),
									'var' => '--wsf-group-li-color-focus',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
								),

								'group_li_color_disabled' => array(

									'label' => __('Disabled', 'ws-form'),
									'var' => '--wsf-group-li-color-disabled',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral)',
								),
							),
						),

						'styled_steps' => array(

							'label' => __('Styled - Steps', 'ws-form'),

							'children' => array(

								'color_background' => array(

									'label' => __('Background Color', 'ws-form'),

									'meta' => array(

										'group_li_steps_color_background' => array(

											'label' => __('Default', 'ws-form'),
											'var' => '--wsf-group-li-steps-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base-contrast)'
										),

										'group_li_steps_color_background_active' => array(

											'label' => __('Active', 'ws-form'),
											'var' => '--wsf-group-li-steps-color-background-active',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base-contrast)'
										),

										'group_li_steps_color_background_completed' => array(

											'label' => __('Completed', 'ws-form'),
											'var' => '--wsf-group-li-steps-color-background-complete',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-primary)'
										),
									)
								),

								'border_color' => array(

									'label' => __('Border Color', 'ws-form'),

									'meta' => array(

										'group_li_steps_border_color' => array(

											'label' => __('Default', 'ws-form'),
											'var' => '--wsf-group-li-steps-border-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-neutral-light-60)',
										),

										'group_li_steps_border_color_active' => array(

											'label' => __('Active', 'ws-form'),
											'var' => '--wsf-group-li-steps-border-color-active',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-primary)',
										),

										'group_li_steps_border_color_complete' => array(

											'label' => __('Completed', 'ws-form'),
											'var' => '--wsf-group-li-steps-border-color-complete',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-primary)',
										),
									),
								),

								'size' => array(

									'label' => __('Size', 'ws-form'),

									'meta' => array(

										'group_step_size' => array(

											'label' => __('Size', 'ws-form'),
											'var' => '--wsf-group-li-steps-size',
											'type' => 'size',
											'default' => 'var(--wsf-field-height)',
										),

										'group_step_gap' => array(

											'label' => __('Gap', 'ws-form'),
											'var' => '--wsf-group-li-steps-gap',
											'type' => 'calc',
											'default' => 'calc(var(--wsf-field-height) / 2)',
											'help' => __('Horizontal gap between step and text when aligned vertically', 'ws-form'),
										),
									),
								),

								'text_color' => array(

									'label' => __('Text Color', 'ws-form'),

									'meta' => array(

										'group_li_steps_color' => array(

											'label' => __('Default', 'ws-form'),
											'var' => '--wsf-group-li-steps-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base)'
										),

										'group_li_steps_color_active' => array(

											'label' => __('Active', 'ws-form'),
											'var' => '--wsf-group-li-steps-color-active',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base)'
										),

										'group_li_steps_color_completed' => array(

											'label' => __('Completed', 'ws-form'),
											'var' => '--wsf-group-li-steps-color-complete',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base-contrast)'
										),
									),
								),

								'typography' => array(

									'label' => __('Typography', 'ws-form'),

									'meta' => self::get_styler_typography_meta('group_li_steps', 'group-li-steps', 'var(--wsf-form-font-size)', '700', 'var(--wsf-field-height)'),
								),
							),
						),

						'typography' => array(

							'label' => __('Typography', 'ws-form'),

							'meta' => self::get_styler_typography_meta('group_li', 'group-li'),
						),
					),
				),

				// Section
				'section' => array(

					'label' => __('Section', 'ws-form'),

					'children' => array(

						'background' => array(

							'label' => __('Background Color', 'ws-form'),

							'meta' => array(

								'section_color_background' => array(

									'label' => __('Color', 'ws-form'),
									'var' => '--wsf-section-color-background',
									'type' => 'color',
									'default' => 'transparent',
								),
							),
						),

						'legend' => array(

							'label' => __('Legend', 'ws-form'),

							'group_focus_selector' => '.wsf-section legend',

							'children' => self::get_style_color_typograpy_gap('section_legend', 'section-legend', 'var(--wsf-form-color-base)', 'transparent', 'var(--wsf-form-font-size-large)', 'var(--wsf-form-font-weight)', 'var(--wsf-form-line-height)', '10px'),
						),
					),
				),

				// Field
				'field' => array(

					'label' => __('Field', 'ws-form'),

					'children' => array(

						'background' => array(

							'label' => __('Background Color', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=text]',

							'meta' => array(

								'field_color_background' => array(

									'label' => __('Default', 'ws-form'),
									'var' => '--wsf-field-color-background',
									'type' => 'color',

									// Leave this as base contrast for inside label and improved light on dark adjustments
									'default' => 'var(--wsf-form-color-base-contrast)',
								),

								'field_color_background_hover' => array(

									'label' => __('Hover', 'ws-form'),
									'var' => '--wsf-field-color-background-hover',
									'type' => 'color',
									'default' => 'var(--wsf-field-color-background)',
								),

								'field_color_background_focus' => array(

									'label' => __('Focus', 'ws-form'),
									'var' => '--wsf-field-color-background-focus',
									'type' => 'color',
									'default' => 'var(--wsf-field-color-background)',
								),

								'field_color_background_disabled' => array(

									'label' => __('Disabled', 'ws-form'),
									'var' => '--wsf-field-color-background-disabled',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-80)',
								),

								'field_color_background_invalid' => array(

									'label' => __('Invalid', 'ws-form'),
									'var' => '--wsf-field-color-background-invalid',
									'type' => 'color',
									'default' => 'var(--wsf-field-color-background)',
								),
							),
						),

						'border_style' => array(

							'label' => __('Border Style', 'ws-form'),

							'meta' => array(

								'field_border_radius' => array(

									'label' => __('Radius', 'ws-form'),
									'var' => '--wsf-field-border-radius',
									'type' => 'size',
									'default' => '4px',
								),

								'field_border_width' => array(

									'label' => __('Width', 'ws-form'),
									'var' => '--wsf-field-border-width',
									'type' => 'size',
									'default' => '1px',
									'legacy_v1_option_key' => 'skin_border_width',
									'legacy_v1_suffix' => 'px'
								),

								'field_border_style' => array(

									'label' => __('Style', 'ws-form'),
									'var' => '--wsf-field-border-style',
									'type' => 'text',
									'default' => 'solid',
									'datalist' => 'wsf-styler-datalist-border-style',
									'legacy_v1_option_key' => 'skin_border_style'
								),

								'field_border' => array(

									'label' => __('Border', 'ws-form'),
									'var' => '--wsf-field-border',
									'type' => 'calc',
									'default' => 'var(--wsf-field-border-width) var(--wsf-field-border-style) var(--wsf-field-border-color)',
									'default_alt' => 'var(--wsf-field-border-width) var(--wsf-field-border-style) var(--wsf-field-border-color-alt)',
								),
							),
						),

						'border_color' => array(

							'label' => __('Border Color', 'ws-form'),

							'meta' => array(

								'field_border_color' => array(

									'label' => __('Default', 'ws-form'),
									'var' => '--wsf-field-border-color',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
								),

								'field_border_color_hover' => array(

									'label' => __('Hover', 'ws-form'),
									'var' => '--wsf-field-border-color-hover',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
								),

								'field_border_color_focus' => array(

									'label' => __('Focus', 'ws-form'),
									'var' => '--wsf-field-border-color-focus',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-primary)'
								),

								'field_border_color_disabled' => array(

									'label' => __('Color - Disabled', 'ws-form'),
									'var' => '--wsf-field-border-color-disabled',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-60)',
								),

								'field_border_color_invalid' => array(

									'label' => __('Color - Invalid', 'ws-form'),
									'var' => '--wsf-field-border-color-invalid',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-danger)',
								),
							),
						),

						'box_shadow_focus' => array(

							'label' => __('Box Shadow (Focus)', 'ws-form'),

							'meta' => array(

								'field_box_shadow_color_focus' => array(

									'label' => __('Color', 'ws-form'),
									'var' => '--wsf-field-box-shadow-color-focus',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-primary)',
								),

								'field_box_shadow_width_focus' => array(

									'label' => __('Width', 'ws-form'),
									'var' => '--wsf-field-box-shadow-width-focus',
									'type' => 'size',
									'default' => '2px',
									'legacy_v1_option_key' => 'skin_box_shadow_width',
									'legacy_v1_suffix' => 'px',
								),

								'field_box_shadow' => array(

									'label' => __('Box Shadow', 'ws-form'),
									'var' => '--wsf-field-box-shadow',
									'type' => 'calc',
									'default' => '0 0 0 var(--wsf-field-box-shadow-width-focus) var(--wsf-field-box-shadow-color-focus)',
									'default_alt' => '0 0 0 var(--wsf-field-box-shadow-width-focus) var(--wsf-field-box-shadow-color-focus-alt)',
								),
							)
						),

						'fieldset_label' => array(

							'label' => __('Fieldset Legend', 'ws-form'),

							'children' => self::get_style_color_typograpy_gap('field_fieldset_legend', 'field-fieldset-legend', 'var(--wsf-form-color-base)', 'transparent', 'var(--wsf-form-font-size)', 'var(--wsf-form-font-weight)', 'var(--wsf-form-line-height)', '10px'),
						),

						'help' => array(

							'label' => __('Help', 'ws-form'),

							'children' => self::get_style_color_typograpy_gap('field_help', 'field-help', 'var(--wsf-form-color-base)', 'transparent', 'var(--wsf-form-font-size-small)', 'var(--wsf-form-font-weight)', 'var(--wsf-form-line-height)', '5px'),
						),

						'invalid_feedback' => array(

							'label' => __('Invalid Feedback', 'ws-form'),

							'children' => self::get_style_color_typograpy_gap('field_invalid_feedback', 'field-invalid-feedback', 'var(--wsf-form-color-danger)', 'transparent', 'var(--wsf-form-font-size-small)', 'var(--wsf-form-font-weight)', 'var(--wsf-form-line-height)', '5px'),
						),

						'label' => array(

							'label' => __('Label', 'ws-form'),

							'children' => array_merge(

								self::get_style_color_typograpy_gap('field_label', 'field-label', 'var(--wsf-form-color-base)', 'transparent', 'var(--wsf-form-font-size)', 'var(--wsf-form-font-weight)', 'var(--wsf-form-line-height)', '5px'),

								array(

									'inside_label' => array(

										'label' => __('Inside Label', 'ws-form'),

										'meta' => array(

											'field_label_inside_mode' => array(

												'label' => __('Behavior', 'ws-form'),
												'var' => '--wsf-field-label-inside-mode',
												'type' => 'select',
												'options' => array(
													array('value' => 'move', 'text' =>	__('Move', 'ws-form')),
													array('value' => 'hide', 'text' =>	__('Hide', 'ws-form')),
												),
												'default' => 'move',
												'legacy_v1_option_key' => 'skin_label_position_inside_mode'
											),

											'field_label_inside_offset' => array(

												'label' => __('Vertical Offset', 'ws-form'),
												'var' => '--wsf-field-label-inside-offset',
												'type' => 'size',
												'default' => '-20px',
												'px_min' => -40,
												'px_max' => 0,
												'legacy_v1_option_key' => 'skin_label_column_inside_offset',
												'legacy_v1_suffix' => 'px'
											),

											'field_label_inside_scale' => array(

												'label' => __('Scale', 'ws-form'),
												'var' => '--wsf-field-label-inside-scale',
												'type' => 'float',
												'default' => '0.9',
												'legacy_v1_option_key' => 'skin_label_column_inside_scale'
											),
										),
									),
								),
							),
						),

						'padding' => array(

							'label' => __('Padding', 'ws-form'),

							'meta' => self::get_styler_padding_meta('field', 'field', '10px', '8.5px'),
						),

						'placeholder' => array(

							'label' => __('Placeholder', 'ws-form'),

							'meta' => array(

								'field_placeholder_color' => array(

									'label' => __('Color', 'ws-form'),
									'var' => '--wsf-field-color-placeholder',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral)',
								),
							),
						),

						'prefix_suffix' => array(

							'label' => __('Prefix / Suffix', 'ws-form'),

							'group_focus_selector' => '.wsf-input-group-prepend',

							'children' => self::get_style_color_typograpy('field_prefix_suffix', 'field-prefix-suffix', 'var(--wsf-form-color-base)', 'var(--wsf-form-color-neutral-light-80)'),
						),

						'text_color' => array(

							'label' => __('Text Color', 'ws-form'),

							'meta' => array(

								'field_color' => array(

									'label' => __('Default', 'ws-form'),
									'var' => '--wsf-field-color',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
									'wcag_background' => '--wsf-field-color-background',
								),

								'field_color_hover' => array(

									'label' => __('Hover', 'ws-form'),
									'var' => '--wsf-field-color-hover',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
								),

								'field_color_focus' => array(

									'label' => __('Focus', 'ws-form'),
									'var' => '--wsf-field-color-focus',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
								),

								'field_color_disabled' => array(

									'label' => __('Disabled', 'ws-form'),
									'var' => '--wsf-field-color-disabled',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral)',
								),

								'field_color_invalid' => array(

									'label' => __('Invalid', 'ws-form'),
									'var' => '--wsf-field-color-invalid',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-danger)',
								),
							),
						),

						'tooltip' => array(

							'label' => __('Tooltip', 'ws-form'),

							'meta' => array(

								'field_tooltip_color_background' => array(

									'label' => __('Background Color', 'ws-form'),
									'var' => '--wsf-field-tooltip-color-background',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral)',
								),

								'field_tooltip_color' => array(

									'label' => __('Font Color', 'ws-form'),
									'var' => '--wsf-field-tooltip-color',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-base)',
								),

								'field_tooltip_font_family' => array(

									'label' => __('Font Family', 'ws-form'),
									'var' => '--wsf-field-tooltip-font-family',
									'type' => 'text',
									'default' => 'var(--wsf-form-font-family)',
									'datalist' => 'wsf-styler-datalist-font-family',
								),

								'field_tooltip_font_size' => array(

									'label' => __('Font Size', 'ws-form'),
									'var' => '--wsf-field-tooltip-font-size',
									'type' => 'size',
									'default' => 'var(--wsf-form-font-size-small)',
								),

								'field_tooltip_font_weight' => array(

									'label' => __('Font Weight', 'ws-form'),
									'var' => '--wsf-field-tooltip-font-weight',
									'type' => 'weight',
									'default' => 'var(--wsf-form-font-weight)',
									'datalist' => 'wsf-styler-datalist-font-weight',
								),

								'field_tooltip_line_height' => array(

									'label' => __('Line Height', 'ws-form'),
									'var' => '--wsf-field-tooltip-line-height',
									'type' => 'size',
									'default' => 'var(--wsf-form-line-height)',
								),

								'field_tooltip_border_radius' => array(

									'label' => __('Radius', 'ws-form'),
									'var' => '--wsf-field-tooltip-border-radius',
									'type' => 'size',
									'default' => 'var(--wsf-field-border-radius)',
								),

								'field_tooltip_gap' => array(

									'label' => __('Gap', 'ws-form'),
									'var' => '--wsf-field-tooltip-gap',
									'type' => 'size',
									'default' => '5px',
									'default_conv' => '10px',
									'legacy_v1_option_key' => 'skin_spacing_small',
									'legacy_v1_suffix' => 'px'
								),
							),
						),

						'typography' => array(

							'label' => __('Typography', 'ws-form'),

							'meta' => array_merge(

								self::get_styler_typography_meta('field','field'),

								array(

									'field_height' => array(

										'label' => __('Field Height', 'ws-form'),
										'var' => '--wsf-field-height',
										'type' => 'calc',
										'default' => 'calc((var(--wsf-field-font-size) * var(--wsf-field-line-height)) + (var(--wsf-field-padding-vertical) * 2) + (var(--wsf-field-border-width) * 2))',
									),
								),
							),
						),
					),
				),

				// Fields
				'field_type' => array(

					'label' => __('Field Types', 'ws-form'),

					'children' => array(

						'button' => array(

							'label' => __('Button', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=button],.wsf-field-wrapper[data-type=submit]',

							'children' => array(

								'color_background' => array(

									'label' => __('Background Color', 'ws-form'),

									'children' => array(

										// Type: None
										'base' => array(

											'label' => __('Base', 'ws-form'),

											'meta' => array(

												'field_button_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-60)',
													'default_alt_auto' => false,
												),

												'field_button_color_background_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-color-background-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-40)',
													'default_alt_auto' => false,
												),

												'field_button_color_background_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-color-background-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Primary
										'primary' => array(

											'label' => __('Primary', 'ws-form'),

											'meta' => array(

												'field_button_primary_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-primary-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)',
													'default_alt_auto' => false,
												),

												'field_button_primary_color_background_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-primary-color-background-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_primary_color_background_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-primary-color-background-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Secondary
										'secondary' => array(

											'label' => __('Secondary', 'ws-form'),

											'meta' => array(

												'field_button_secondary_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-secondary-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-secondary)',
													'default_alt_auto' => false,
												),

												'field_button_secondary_color_background_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-secondary-color-background-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-secondary-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_secondary_color_background_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-secondary-color-background-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-secondary-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Success
										'success' => array(

											'label' => __('Success', 'ws-form'),

											'meta' => array(

												'field_button_success_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-success-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-success)',
													'default_alt_auto' => false,
												),

												'field_button_success_color_background_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-success-color-background-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-success-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_success_color_background_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-success-color-background-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-success-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Information
										'information' => array(

											'label' => __('Information', 'ws-form'),

											'meta' => array(

												'field_button_information_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-info-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-info)',
													'default_alt_auto' => false,
												),

												'field_button_information_color_background_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-info-color-background-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-info-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_information_color_background_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-info-color-background-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-info-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Warning
										'warning' => array(

											'label' => __('Warning', 'ws-form'),

											'meta' => array(

												'field_button_warning_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-warning-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-warning)',
													'default_alt_auto' => false,
												),

												'field_button_warning_color_background_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-warning-color-background-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-warning-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_warning_color_background_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-warning-color-background-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-warning-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Danger
										'danger' => array(

											'label' => __('Danger', 'ws-form'),

											'meta' => array(

												'field_button_danger_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-danger-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-danger)',
													'default_alt_auto' => false,
												),

												'field_button_danger_color_background_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-danger-color-background-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-danger-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_danger_color_background_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-danger-color-background-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-danger-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),
									),
								),

								'border_color' => array(

									'label' => __('Border Color', 'ws-form'),

									'children' => array(

										// Type: Base
										'base' => array(

											'label' => __('Base', 'ws-form'),

											'meta' => array(

												'field_button_border_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-60)',
													'default_alt_auto' => false,
												),

												'field_button_border_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-border-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-40)',
													'default_alt_auto' => false,
												),

												'field_button_border_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-border-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Primary
										'primary' => array(

											'label' => __('Primary', 'ws-form'),

											'meta' => array(

												'field_button_primary_border_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-primary-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)',
													'default_alt_auto' => false,
												),

												'field_button_primary_border_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-primary-border-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_primary_border_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-primary-border-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Secondary
										'secondary' => array(

											'label' => __('Secondary', 'ws-form'),

											'meta' => array(

												'field_button_secondary_border_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-secondary-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-secondary)',
													'default_alt_auto' => false,
												),

												'field_button_secondary_border_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-secondary-border-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-secondary-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_secondary_border_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-secondary-border-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-secondary-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Success
										'success' => array(

											'label' => __('Success', 'ws-form'),

											'meta' => array(

												'field_button_success_border_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-success-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-success)',
													'default_alt_auto' => false,
												),

												'field_button_success_border_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-success-border-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-success-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_success_border_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-success-border-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-success-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Information
										'information' => array(

											'label' => __('Information', 'ws-form'),

											'meta' => array(

												'field_button_information_border_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-info-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-info)',
													'default_alt_auto' => false,
												),

												'field_button_information_border_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-info-border-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-info-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_information_border_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-info-border-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-info-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Warning
										'warning' => array(

											'label' => __('Warning', 'ws-form'),

											'meta' => array(

												'field_button_warning_border_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-warning-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-warning)',
													'default_alt_auto' => false,
												),

												'field_button_warning_border_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-warning-border-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-warning-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_warning_border_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-warning-border-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-warning-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Danger
										'danger' => array(

											'label' => __('Danger', 'ws-form'),

											'meta' => array(

												'field_button_danger_border_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-danger-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-danger)',
													'default_alt_auto' => false,
												),

												'field_button_danger_border_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-danger-border-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-danger-dark-20)',
													'default_alt_auto' => false,
												),

												'field_button_danger_border_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-danger-border-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-danger-dark-40)',
													'default_alt_auto' => false,
												),
											),
										),
									),
								),

								// Border style
								'border_style' => array(

									'label' => __('Border Style', 'ws-form'),

									'meta' => array(

										'field_button_border_radius' => array(

											'label' => __('Radius', 'ws-form'),
											'var' => '--wsf-field-button-border-radius',
											'type' => 'size',
											'default' => 'var(--wsf-field-border-radius)',
										),

										'field_button_border_style' => array(

											'label' => __('Style', 'ws-form'),
											'var' => '--wsf-field-button-border-style',
											'type' => 'text',
											'default' => 'solid',
											'datalist' => 'wsf-styler-datalist-border-style',
										),

										'field_button_border_width' => array(

											'label' => __('Width', 'ws-form'),
											'var' => '--wsf-field-button-border-width',
											'type' => 'size',
											'default' => '1px',
										),
									),
								),

								'padding' => array(

									'label' => __('Padding', 'ws-form'),

									'meta' => self::get_styler_padding_meta('field_button', 'field-button', 'var(--wsf-field-padding-horizontal)', 'var(--wsf-field-padding-vertical)'),
								),

								'size' => array(

									'label' => __('Size', 'ws-form'),

									'meta' => array(

										'field_button_width' => array(

											'label' => __('Width', 'ws-form'),
											'var' => '--wsf-field-button-width',
											'type' => 'size',
											'default' => '100%',
										),
									),
								),

								'text_color' => array(

									'label' => __('Text Color', 'ws-form'),

									'children' => array(

										// Type: Base
										'base' => array(

											'label' => __('Base', 'ws-form'),

											'meta' => array(

												'field_button_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
													'default_alt_auto' => false,
												),

												'field_button_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
													'default_alt_auto' => false,
												),

												'field_button_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Primary
										'primary' => array(

											'label' => __('Primary', 'ws-form'),

											'meta' => array(

												'field_button_primary_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-primary-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt_auto' => false,
												),

												'field_button_primary_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-primary-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-field-button-primary-color)',
													'default_alt_auto' => false,
												),

												'field_button_primary_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-primary-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-field-button-primary-color)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Secondary
										'secondary' => array(

											'label' => __('Secondary', 'ws-form'),

											'meta' => array(

												'field_button_secondary_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-secondary-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt_auto' => false,
												),

												'field_button_secondary_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-secondary-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-field-button-secondary-color)',
													'default_alt_auto' => false,
												),

												'field_button_secondary_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-secondary-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-field-button-secondary-color)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Success
										'success' => array(

											'label' => __('Success', 'ws-form'),

											'meta' => array(

												'field_button_success_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-success-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt_auto' => false,
												),

												'field_button_success_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-success-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-field-button-success-color)',
													'default_alt_auto' => false,
												),

												'field_button_success_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-success-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-field-button-success-color)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Information
										'information' => array(

											'label' => __('Information', 'ws-form'),

											'meta' => array(

												'field_button_information_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-info-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt_auto' => false,
												),

												'field_button_information_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-info-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt_auto' => false,
												),

												'field_button_information_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-info-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Warning
										'warning' => array(

											'label' => __('Warning', 'ws-form'),

											'meta' => array(

												'field_button_warning_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-warning-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
													'default_alt_auto' => false,
												),

												'field_button_warning_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-warning-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
													'default_alt_auto' => false,
												),

												'field_button_warning_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-warning-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
													'default_alt_auto' => false,
												),
											),
										),

										// Type: Danger
										'danger' => array(

											'label' => __('Danger', 'ws-form'),

											'meta' => array(

												'field_button_danger_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-button-danger-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt_auto' => false,
												),

												'field_button_danger_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-button-danger-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-field-button-danger-color)',
													'default_alt_auto' => false,
												),

												'field_button_danger_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-button-danger-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-field-button-danger-color)',
													'default_alt_auto' => false,
												),
											),
										),
									),
								),

								// Typography
								'typography' => array(

									'label' => __('Typography', 'ws-form'),

									'meta' => self::get_styler_typography_meta('field_button', 'field-button')
								),
							),
						),

						'checkbox' => array(

							'label' => __('Checkbox', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=checkbox]',

							'children' => array(

								'color' => array(

									'label' => __('Color', 'ws-form'),

									'meta' => array(

										'field_checkbox_color_color_background' => array(

											'label' => __('Background', 'ws-form'),
											'var' => '--wsf-field-checkbox-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-field-color-background)',
										),

										'field_checkbox_color_checked_color_background' => array(

											'label' => __('Checked', 'ws-form'),
											'var' => '--wsf-field-checkbox-checked-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-accent)',
										),

										'field_checkbox_color_checkmark_color' => array(

											'label' => __('Checkmark', 'ws-form'),
											'var' => '--wsf-field-checkbox-checkmark-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base-contrast)',
										),
									),
								),

								'gap' => array(

									'label' => __('Gap', 'ws-form'),

									'meta' => array(

										'field_checkbox_gap_horizontal' => array(

											'label' => __('Horizontal', 'ws-form'),
											'var' => '--wsf-field-checkbox-gap-horizontal',
											'type' => 'size',
											'default' => '6px',
										),

										'field_checkbox_gap_vertical' => array(

											'label' => __('Vertical', 'ws-form'),
											'var' => '--wsf-field-checkbox-gap-vertical',
											'type' => 'size',
											'default' => '10px',
										),
									),
								),

								'size' => array(

									'label' => __('Size', 'ws-form'),

									'meta' => array(

										'field_checkbox_size' => array(

											'label' => __('Size', 'ws-form'),
											'var' => '--wsf-field-checkbox-size',
											'type' => 'calc',
											'default' => 'calc(var(--wsf-field-font-size) * var(--wsf-field-line-height))',
										),
									),
								),

								'styled' => array(

									'label' => __('Styled', 'ws-form'),

									'children' => array(

										'button' => array(

											'label' => __('Button', 'ws-form'),

											'meta' => array(

												'field_checkbox_button_color_background' => array(

													'label' => __('Background', 'ws-form'),
													'var' => '--wsf-field-checkbox-button-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-60)',
												),

												'field_checkbox_button_color' => array(

													'label' => __('Text', 'ws-form'),
													'var' => '--wsf-field-checkbox-button-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),

												'field_checkbox_checked_button_color_background' => array(

													'label' => __('Checked - BG', 'ws-form'),
													'var' => '--wsf-field-checkbox-checked-button-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-accent)',
												),

												'field_checkbox_checked_button_color' => array(

													'label' => __('Checked - Text', 'ws-form'),
													'var' => '--wsf-field-checkbox-checked-button-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
												),
											),
										),

										'switch' => array(

											'label' => __('Switch', 'ws-form'),

											'children' => array(

												'color' => array(

													'label' => __('Color', 'ws-form'),

													'meta' => array(

														'field_checkbox_switch_color' => array(

															'label' => __('Switch', 'ws-form'),
															'var' => '--wsf-field-checkbox-switch-color',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-neutral-light-60)',
														),

														'field_checkbox_checked_switch_color_background' => array(

															'label' => __('Checked - BG', 'ws-form'),
															'var' => '--wsf-field-checkbox-checked-switch-color-background',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-accent)',
														),

														'field_checkbox_checked_switch_color' => array(

															'label' => __('Checked - Switch', 'ws-form'),
															'var' => '--wsf-field-checkbox-checked-switch-color',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-base-contrast)',
														),
													),
												),

												'size' => array(

													'label' => __('Size', 'ws-form'),

													'meta' => array(

														'field_checkbox_switch_width' => array(

															'label' => __('Switch Width', 'ws-form'),
															'var' => '--wsf-field-checkbox-switch-width',
															'type' => 'calc',
															'default' => 'calc(var(--wsf-field-checkbox-size) * 1.8)',
														),

														'field_checkbox_switch_size' => array(

															'label' => __('Switch Size', 'ws-form'),
															'var' => '--wsf-field-checkbox-switch-size',
															'type' => 'calc',
															'default' => 'calc(var(--wsf-field-checkbox-size) * 0.8)',
														),
													),
												),
											),
										),

										'swatch' => array(

											'label' => __('Swatch', 'ws-form'),

											'meta' => array(

												'field_checkbox_checked_swatch_border_color' => array(

													'label' => __('Checked - Border', 'ws-form'),
													'var' => '--wsf-field-checkbox-checked-swatch-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)'
												),

												'field_checkbox_checked_swatch_box_shadow_color' => array(

													'label' => __('Checked - Box', 'ws-form'),
													'var' => '--wsf-field-checkbox-checked-swatch-box-shadow-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt' => 'var(--wsf-form-color-base-contrast)',
												),
											),
										),

										'image' => array(

											'label' => __('Image', 'ws-form'),

											'meta' => array(

												'field_checkbox_checked_image_border_color' => array(

													'label' => __('Checked - Border', 'ws-form'),
													'var' => '--wsf-field-checkbox-checked-image-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)'
												),

												'field_checkbox_checked_image_box_shadow_color' => array(

													'label' => __('Checked - Box', 'ws-form'),
													'var' => '--wsf-field-checkbox-checked-image-box-shadow-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt' => 'var(--wsf-form-color-base-contrast)',
												),
											),
										),
									),
								),
							),
						),

						'datetime' => array(

							'label' => __('Date/Time', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=datetime]',

							'children' => array(

								'calendar' => array(

									'label' => __('Calendar', 'ws-form'),

									'children' => array(

										'color_background' => array(

											'label' => __('Background Color', 'ws-form'),

											'meta' => array(

												'field_datetime_calendar_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-90)',
												),

												'field_datetime_calendar_today_color_background' => array(

													'label' => __('Today', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-today-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-70)',
												),

												'field_datetime_calendar_current_color_background' => array(

													'label' => __('Current', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-current-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)',
												),

												'field_datetime_calendar_hover_color_background' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-hover-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)',
												),
											),
										),

										'border' => array(

											'label' => __('Border', 'ws-form'),

											'meta' => array(

												'field_datetime_calendar_border_color' => array(

													'label' => __('Color', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-60)',
												),

												'field_datetime_calendar_border_style' => array(

													'label' => __('Style', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-border-style',
													'type' => 'text',
													'default' => 'var(--wsf-field-border-style)',
													'datalist' => 'wsf-styler-datalist-border-style',
												),

												'field_datetime_calendar_border_width' => array(

													'label' => __('Width', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-border-width',
													'type' => 'size',
													'default' => 'var(--wsf-field-border-width)',
												),
											),
										),

										'text_color' => array(

											'label' => __('Text Color', 'ws-form'),

											'meta' => array(

												'field_datetime_calendar_color' => array(

													'label' => __('Text', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),

												'field_datetime_calendar_today_color' => array(

													'label' => __('Today', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-today-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),

												'field_datetime_calendar_current_color' => array(

													'label' => __('Current', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-current-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
												),

												'field_datetime_calendar_hover_color' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-hover-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
												),

												'field_datetime_calendar_disabled_color' => array(

													'label' => __('Disabled', 'ws-form'),
													'var' => '--wsf-field-datetime-calendar-disabled-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-light-80)',
												),
											),
										),

										'typography' => array(

											'label' => __('Typography', 'ws-form'),

											'meta' => self::get_styler_typography_meta('field_datetime_calendar', 'field-datetime-calendar', 'var(--wsf-form-font-size)', '700'),
										),

										'padding' => array(

											'label' => __('Padding', 'ws-form'),

											'meta' => self::get_styler_padding_meta('field_datetime_calendar', 'field-datetime-calendar', '4px', '4px'),
										),
									),
								),

								'calendar_heading' => array(

									'label' => __('Calendar Heading', 'ws-form'),

									'meta' => array(

										'field_datetime_calendar_heading_color_background' => array(

											'label' => __('Background Color', 'ws-form'),
											'var' => '--wsf-field-datetime-calendar-heading-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-neutral-light-80)',
										),

										'field_datetime_calendar_heading-color' => array(

											'label' => __('Text Color', 'ws-form'),
											'var' => '--wsf-field-datetime-calendar-heading-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base)',
										),
									),
								),

								'nav' => array(

									'label' => __('Navigation', 'ws-form'),

									'children' => array(

										'color_background' => array(

											'label' => __('Background Color', 'ws-form'),

											'meta' => array(

												'field_datetime_nav_current_color_background' => array(

													'label' => __('Current', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-current-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)'
												),

												'field_datetime_nav_hover_color_background' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-hover-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)',
												),
											),
										),

										'border' => array(

											'label' => __('Border', 'ws-form'),

											'meta' => array(

												'field_datetime_nav_border_color' => array(

													'label' => __('Color', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-60)',
												),

												'field_datetime_nav_border_style' => array(

													'label' => __('Style', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-border-style',
													'type' => 'text',
													'default' => 'var(--wsf-field-border-style)',
													'datalist' => 'wsf-styler-datalist-border-style',
												),

												// Navigation - Border - Width
												'field_datetime_nav_border_width' => array(

													'label' => __('Width', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-border-width',
													'type' => 'size',
													'default' => 'var(--wsf-field-border-width)',
												),
											),
										),

										'icon' => array(

											'label' => __('Icon', 'ws-form'),

											'meta' => array(

												'field_datetime_nav_icon_color' => array(

													'label' => __('Color', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-icon-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),

												'field_datetime_nav_icon_gap' => array(

													'label' => __('Gap', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-icon-gap',
													'type' => 'size',
													'default' => '8px',
												),

												'field_datetime_nav_icon_size' => array(

													'label' => __('Size', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-icon-size',
													'type' => 'size',
													'default' => 'var(--wsf-form-font-size)',
												),
											),
										),

										'text_color' => array(

											'label' => __('Text Color', 'ws-form'),

											'meta' => array(

												'field_datetime_nav_color' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),

												'field_datetime_nav_current_color' => array(

													'label' => __('Current', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-current-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
												),

												'field_datetime_nav_hover_color' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-datetime-nav-hover-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
												),
											),
										),

										'typography' => array(

											'label' => __('Typography', 'ws-form'),

											'meta' => self::get_styler_typography_meta('field_datetime_nav', 'field-datetime-nav', 'var(--wsf-form-font-size)', '700'),
										),
									),
								),

								'padding' => array(

									'label' => __('Padding', 'ws-form'),

									'meta' => self::get_styler_padding_meta('field_datetime', 'field-datetime', '10px', '10px'),
								),
							),
						),

						'file' => array(

							'label' => __('File', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=file]',

							'children' => array(

								'native' => array(

									'label' => __('Native', 'ws-form'),

									'children' => array(

										'button_color' => array(

											'label' => __('Button - Text Color', 'ws-form'),

											'meta' => array(

												'field_file_button_color' => array(

													'label' => __('Color', 'ws-form'),
													'var' => '--wsf-field-file-button-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),

												'field_file_button_color_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-file-button-color-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),

												'field_file_button_color_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-file-button-color-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),
											),
										),

										'button_color_background' => array(

											'label' => __('Button - Background Color', 'ws-form'),

											'meta' => array(

												'field_file_button_color_background' => array(

													'label' => __('Default', 'ws-form'),
													'var' => '--wsf-field-file-button-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-80)',
												),

												'field_file_button_color_background_hover' => array(

													'label' => __('Hover', 'ws-form'),
													'var' => '--wsf-field-file-button-color-background-hover',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-60)',
												),

												'field_file_button_color_background_focus' => array(

													'label' => __('Focus', 'ws-form'),
													'var' => '--wsf-field-file-button-color-background-focus',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-60)',
												),
											),
										),
									),
								),

								'dropzonejs' => array(

									'label' => __('DropzoneJS', 'ws-form'),

									'children' => array(

										'color_background' => array(

											'label' => __('Background Color', 'ws-form'),

											'meta' => array(

												'field_file_dropzonejs_color_background' => array(

													'label' => __('Color', 'ws-form'),
													'var' => '--wsf-field-file-dropzonejs-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-field-color-background)',
												),
											),
										),

										'border' => array(

											'label' => __('Border', 'ws-form'),

											'meta' => array(

												'field_file_dropzonejs_border_color' => array(

													'label' => __('Color', 'ws-form'),
													'var' => '--wsf-field-file-dropzonejs-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-field-border-color)',
												),

												'field_file_dropzonejs_border_style' => array(

													'label' => __('Style', 'ws-form'),
													'var' => '--wsf-field-file-dropzonejs-border-style',
													'type' => 'text',
													'default' => 'dashed',
													'datalist' => 'wsf-styler-datalist-border-style',
												),

												'field_file_dropzonejs_border_width' => array(

													'label' => __('Width', 'ws-form'),
													'var' => '--wsf-field-file-dropzonejs-border-width',
													'type' => 'size',
													'default' => 'var(--wsf-field-border-width)',
												),

												'field_file_dropzonejs_border_radius' => array(

													'label' => __('Radius', 'ws-form'),
													'var' => '--wsf-field-file-dropzonejs-border-radius',
													'type' => 'size',
													'default' => 'var(--wsf-field-border-radius)',
												),
											),
										),

										'padding' => array(

											'label' => __('Padding', 'ws-form'),

											'meta' => self::get_styler_padding_meta('field_file_dropzonejs', 'field-file-dropzonejs', 'var(--wsf-form-grid-gap)', 'var(--wsf-form-grid-gap)'),
										),

										'progress' => array(

											'label' => __('Progress', 'ws-form'),

											'children' => array(

												'color' => array(

													'label' => __('Color', 'ws-form'),

													'meta' => array(

														'field_file_dropzonejs_progress_color_background' => array(

															'label' => __('Background', 'ws-form'),
															'var' => '--wsf-field-file-dropzonejs-progress-color-background',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-neutral)',
														),

														'field_file_dropzonejs_progress_color_background_bar' => array(

															'label' => __('Bar', 'ws-form'),
															'var' => '--wsf-field-file-dropzonejs-progress-color-background-bar',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-primary)',
														),

														'field_file_dropzonejs_progress_color_background_complete' => array(

															'label' => __('Complete', 'ws-form'),
															'var' => '--wsf-field-file-dropzonejs-progress-color-background-complete',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-success)',
														),
													),
												),

												'size' => array(

													'label' => __('Size', 'ws-form'),

													'meta' => array(

														'field_file_dropzonejs_progress_border_radius' => array(

															'label' => __('Border Radius', 'ws-form'),
															'var' => '--wsf-field-file-dropzonejs-progress-border-radius',
															'type' => 'size',
															'default' => 'var(--wsf-field-border-radius)',
														),

														'field_file_dropzonejs_progress_gap' => array(

															'label' => __('Gap', 'ws-form'),
															'var' => '--wsf-field-file-dropzonejs-progress-gap',
															'type' => 'size',
															'default' => '5px',
															'default_conv' => '10px',
															'legacy_v1_option_key' => 'skin_spacing_small',
															'legacy_v1_suffix' => 'px'
														),

														'field_file_dropzonejs_progress_height' => array(

															'label' => __('Height', 'ws-form'),
															'var' => '--wsf-field-file-dropzonejs-progress-height',
															'type' => 'size',
															'default' => '10px',
														),
													),
												),
											),
										),
									),
								),
							),
						),
						'message' => array(

							'label' => __('Message', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=message]',

							'children' => array(

								// Type: Base
								'base' => array(

									'label' => __('Base', 'ws-form'),

									'meta' => array(

										'field_message_color_background' => array(

											'label' => __('Background Color', 'ws-form'),
											'var' => '--wsf-field-message-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-neutral-light-80)',
											'default_alt_auto' => false,
										),

										'field_message_color' => array(

											'label' => __('Text Color', 'ws-form'),
											'var' => '--wsf-field-message-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base)',
											'default_alt_auto' => false,
										),

										'field_message_anchor_color' => array(

											'label' => __('Anchor Color', 'ws-form'),
											'var' => '--wsf-field-message-anchor-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base)',
											'default_alt_auto' => false,
										),

										'field_message_border_color' => array(

											'label' => __('Border Color', 'ws-form'),
											'var' => '--wsf-field-message-border-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-neutral-light-60)',
											'default_alt_auto' => false,
										),
									),
								),

								// Type: Success
								'success' => array(

									'label' => __('Success', 'ws-form'),

									'meta' => array(

										'field_message_success_color_background' => array(

											'label' => __('Background Color', 'ws-form'),
											'var' => '--wsf-field-message-success-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-success-light-80)',
											'default_alt_auto' => false,
										),

										'field_message_success_color' => array(

											'label' => __('Text Color', 'ws-form'),
											'var' => '--wsf-field-message-success-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-success-dark-40)',
											'default_alt_auto' => false,
										),

										'field_message_success_anchor_color' => array(

											'label' => __('Anchor Color', 'ws-form'),
											'var' => '--wsf-field-message-success-anchor-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-success-dark-60)',
											'default_alt_auto' => false,
										),

										'field_message_success_border_color' => array(

											'label' => __('Border Color', 'ws-form'),
											'var' => '--wsf-field-message-success-border-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-success-light-40)',
											'default_alt_auto' => false,
										),
									),
								),

								// Type: Information
								'information' => array(

									'label' => __('Information', 'ws-form'),

									'meta' => array(

										'field_message_information_color_background' => array(

											'label' => __('Background Color', 'ws-form'),
											'var' => '--wsf-field-message-info-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-info-light-80)',
											'default_alt_auto' => false,
										),

										'field_message_information_color' => array(

											'label' => __('Text Color', 'ws-form'),
											'var' => '--wsf-field-message-info-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-info-dark-40)',
											'default_alt_auto' => false,
										),

										'field_message_information_anchor_color' => array(

											'label' => __('Anchor Color', 'ws-form'),
											'var' => '--wsf-field-message-info-anchor-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-info-dark-60)',
											'default_alt_auto' => false,
										),

										'field_message_information_border_color' => array(

											'label' => __('Border Color', 'ws-form'),
											'var' => '--wsf-field-message-info-border-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-info-light-40)',
											'default_alt_auto' => false,
										),
									),
								),

								// Type: Warning
								'warning' => array(

									'label' => __('Warning', 'ws-form'),

									'meta' => array(

		 								'field_message_warning_color_background' => array(

											'label' => __('Background Color', 'ws-form'),
											'var' => '--wsf-field-message-warning-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-warning-light-80)',
											'default_alt_auto' => false,
										),

										'field_message_warning_color' => array(

											'label' => __('Text Color', 'ws-form'),
											'var' => '--wsf-field-message-warning-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-warning-dark-40)',
											'default_alt_auto' => false,
										),

										'field_message_warning_anchor_color' => array(

											'label' => __('Anchor Color', 'ws-form'),
											'var' => '--wsf-field-message-warning-anchor-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-warning-dark-60)',
											'default_alt_auto' => false,
										),

										'field_message_warning_border_color' => array(

											'label' => __('Border Color', 'ws-form'),
											'var' => '--wsf-field-message-warning-border-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-warning-light-40)',
											'default_alt_auto' => false,
										),
									),
								),

								// Type: Danger
								'danger' => array(

									'label' => __('Danger', 'ws-form'),

									'meta' => array(

										// Type: Danger
										'field_message_danger_color_background' => array(

											'label' => __('Background Color', 'ws-form'),
											'var' => '--wsf-field-message-danger-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-danger-light-80)',
											'default_alt_auto' => false,
										),

										'field_message_danger_color' => array(

											'label' => __('Text Color', 'ws-form'),
											'var' => '--wsf-field-message-danger-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-danger-dark-40)',
											'default_alt_auto' => false,
										),

										'field_message_danger_anchor_color' => array(

											'label' => __('Anchor Color', 'ws-form'),
											'var' => '--wsf-field-message-danger-anchor-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-danger-dark-60)',
											'default_alt_auto' => false,
										),

										'field_message_danger_border_color' => array(

											'label' => __('Border Color', 'ws-form'),
											'var' => '--wsf-field-message-danger-border-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-danger-light-40)',
											'default_alt_auto' => false,
										),
									),
								),
							),
						),
						'color' => array(

							'label' => __('Color', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=color]',

							'meta' => array(

								'field_color_swatch_size' => array(

									'label' => __('Swatch Size', 'ws-form'),
									'var' => '--wsf-field-color-swatch-size',
									'type' => 'calc',
									'default' => 'calc(var(--wsf-field-height) * 0.7)',
								),

								'field_color_swatch_border_radius' => array(

									'label' => __('Swatch Border Radius', 'ws-form'),
									'var' => '--wsf-field-color-swatch-border-radius',
									'type' => 'calc',
									'default' => '50%',
								),
							),
						),

						'meter' => array(

							'label' => __('Meter', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=meter]',

							'meta' => array(

								'field_meter_size' => array(

									'label' => __('Size', 'ws-form'),
									'var' => '--wsf-field-meter-size',
									'type' => 'calc',
									'default' => 'calc(var(--wsf-field-height) / 2)',
								),

								'field_meter_color_background' => array(

									'label' => __('Background Color', 'ws-form'),
									'var' => '--wsf-field-meter-color-background',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-80)',
								),

								'field_meter_color_background_optimum' => array(

									'label' => __('Optimum Color', 'ws-form'),
									'var' => '--wsf-field-meter-color-background-optimum',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-success)',
								),

								'field_meter_color_background_sub_optimum' => array(

									'label' => __('Sub Optimum Color', 'ws-form'),
									'var' => '--wsf-field-meter-color-background-sub-optimum',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-warning)',
								),

								'field_meter_color_background_even_less_good' => array(

									'label' => __('Even Less Good Color', 'ws-form'),
									'var' => '--wsf-field-meter-color-background-even-less-good',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-danger)',
								),
							),
						),

						'password' => array(

							'label' => __('Password', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=password]',

							'children' => array(

								'icon' => array(

									'label' => __('Icon', 'ws-form'),

									'meta' => array(

										'field_password_icon_color' => array(

											'label' => __('Color', 'ws-form'),
											'var' => '--wsf-field-password-icon-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-base)',
										),
									),
								),

								'strength_meter' => array(

									'label' => __('Strength Meter', 'ws-form'),

									'meta' => array(

										'field_password_strength_meter_disc_gap' => array(

											'label' => __('Disc Gap', 'ws-form'),
											'var' => '--wsf-field-password-strength-meter-disc-gap',
											'type' => 'calc',
											'default' => 'calc((var(--wsf-field-height) / 3) * (4 / 13))',
										),

										'field_password_strength_meter_disc_size' => array(

											'label' => __('Disc Size', 'ws-form'),
											'var' => '--wsf-field-password-strength-meter-disc-size',
											'type' => 'calc',
											'default' => 'calc((var(--wsf-field-height) / 3) * (7 / 13))',
										),

										'field_password_strength_meter_disc_color' => array(

											'label' => __('Disc Color', 'ws-form'),
											'var' => '--wsf-field-password-strength-meter-disc-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-neutral-light-60)',
										),

										'field_password_strength_meter_disc_color_danger' => array(

											'label' => __('Disc Color - Danger', 'ws-form'),
											'var' => '--wsf-field-password-strength-meter-disc-color-danger',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-danger)',
										),

										'field_password_strength_meter_disc_color_warning' => array(

											'label' => __('Disc Color - Warning', 'ws-form'),
											'var' => '--wsf-field-password-strength-meter-disc-color-warning',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-warning)',
										),

										'field_password_strength_meter_disc_color_success' => array(

											'label' => __('Disc Color - Success', 'ws-form'),
											'var' => '--wsf-field-password-strength-meter-disc-color-success',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-success)',
										),
									),
								),
							),
						),

						'progress' => array(

							'label' => __('Progress', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=progress]',

							'meta' => array(

								'field_progress_size' => array(

									'label' => __('Size', 'ws-form'),
									'var' => '--wsf-field-progress-size',
									'type' => 'calc',
									'default' => 'calc(var(--wsf-field-height) / 2)',
								),

								'field_progress_color_background' => array(

									'label' => __('Background Color', 'ws-form'),
									'var' => '--wsf-field-progress-color-background',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-neutral-light-80)',
								),

								'field_progress_bar_color' => array(

									'label' => __('Bar Color', 'ws-form'),
									'var' => '--wsf-field-progress-color-background-bar',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-accent)',
								),

								'field_progress_bar_color_complete' => array(

									'label' => __('Bar Color - Complete', 'ws-form'),
									'var' => '--wsf-field-progress-color-background-complete',
									'type' => 'color',
									'default' => 'var(--wsf-form-color-success)',
								),
							),
						),
						'radio' => array(

							'label' => __('Radio', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=radio]',

							'children' => array(

								'color' => array(

									'label' => __('Color', 'ws-form'),

									'meta' => array(

										'field_radio_color_color_background' => array(

											'label' => __('Background', 'ws-form'),
											'var' => '--wsf-field-radio-color-background',
											'type' => 'color',
											'default' => 'var(--wsf-field-color-background)',
										),

										'field_radio_color_checked_color' => array(

											'label' => __('Checked', 'ws-form'),
											'var' => '--wsf-field-radio-checked-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-accent)',
										),
									),
								),

								'gap' => array(

									'label' => __('Gap', 'ws-form'),

									'meta' => array(

										'field_radio_gap_horizontal' => array(

											'label' => __('Horizontal', 'ws-form'),
											'var' => '--wsf-field-radio-gap-horizontal',
											'type' => 'size',
											'default' => '6px',
										),

										'field_radio_gap_vertical' => array(

											'label' => __('Vertical', 'ws-form'),
											'var' => '--wsf-field-radio-gap-vertical',
											'type' => 'size',
											'default' => '10px',
										),
									),
								),

								'size' => array(

									'label' => __('Size', 'ws-form'),

									'meta' => array(

										'field_radio_size' => array(

											'label' => __('Size', 'ws-form'),
											'var' => '--wsf-field-radio-size',
											'type' => 'calc',
											'default' => 'calc(var(--wsf-field-font-size) * var(--wsf-field-line-height))',
										),

										'field_radio_checked_size' => array(

											'label' => __('Size - Checked', 'ws-form'),
											'var' => '--wsf-field-radio-checked-size',
											'type' => 'calc',
											'default' => 'calc(var(--wsf-field-radio-size) * 0.7)',
										),
									),
								),

								'styled' => array(

									'label' => __('Styled', 'ws-form'),

									'children' => array(

										'button' => array(

											'label' => __('Button', 'ws-form'),

											'meta' => array(

												'field_radio_button_color_background' => array(

													'label' => __('Background', 'ws-form'),
													'var' => '--wsf-field-radio-button-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-neutral-light-60)',
												),

												'field_radio_button_color' => array(

													'label' => __('Text', 'ws-form'),
													'var' => '--wsf-field-radio-button-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base)',
												),

												'field_radio_checked_button_color_background' => array(

													'label' => __('Checked - BG', 'ws-form'),
													'var' => '--wsf-field-radio-checked-button-color-background',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-accent)',
												),

												'field_radio_checked_button_color' => array(

													'label' => __('Checked - Text', 'ws-form'),
													'var' => '--wsf-field-radio-checked-button-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
												),
											),
										),

										'switch' => array(

											'label' => __('Switch', 'ws-form'),

											'children' => array(

												'size' => array(

													'label' => __('Size', 'ws-form'),

													'meta' => array(

														'field_radio_switch_width' => array(

															'label' => __('Switch - Width', 'ws-form'),
															'var' => '--wsf-field-radio-switch-width',
															'type' => 'calc',
															'default' => 'calc(var(--wsf-field-radio-size) * 1.8)',
														),

														'field_radio_switch_size' => array(

															'label' => __('Switch - Size', 'ws-form'),
															'var' => '--wsf-field-radio-switch-size',
															'type' => 'calc',
															'default' => 'calc(var(--wsf-field-radio-size) * 0.8)',
														),
													),
												),

												'color' => array(

													'label' => __('Color', 'ws-form'),

													'meta' => array(

														'field_radio_switch_color' => array(

															'label' => __('Switch', 'ws-form'),
															'var' => '--wsf-field-radio-switch-color',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-neutral-light-60)',
														),

														'field_radio_checked_switch_color_background' => array(

															'label' => __('Checked - BG', 'ws-form'),
															'var' => '--wsf-field-radio-checked-switch-color-background',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-accent)',
														),

														'field_radio_checked_switch_color' => array(

															'label' => __('Checked - Switch', 'ws-form'),
															'var' => '--wsf-field-radio-checked-switch-color',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-base-contrast)',
														),
													),
												),
											),
										),

										'swatch' => array(

											'label' => __('Swatch', 'ws-form'),

											'meta' => array(

												'field_radio_checked_swatch_border_color' => array(

													'label' => __('Checked - Border', 'ws-form'),
													'var' => '--wsf-field-radio-checked-swatch-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)'
												),

												'field_radio_checked_swatch_box_shadow_color' => array(

													'label' => __('Checked - Box', 'ws-form'),
													'var' => '--wsf-field-radio-checked-swatch-box-shadow-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt' => 'var(--wsf-form-color-base-contrast)',
												),
											),
										),

										'image' => array(

											'label' => __('Image', 'ws-form'),

											'meta' => array(

												'field_radio_checked_image_border_color' => array(

													'label' => __('Checked - Border', 'ws-form'),
													'var' => '--wsf-field-radio-checked-image-border-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-primary)'
												),

												'field_radio_checked_image_box_shadow_color' => array(

													'label' => __('Checked - Box', 'ws-form'),
													'var' => '--wsf-field-radio-checked-image-box-shadow-color',
													'type' => 'color',
													'default' => 'var(--wsf-form-color-base-contrast)',
													'default_alt' => 'var(--wsf-form-color-base-contrast)',
												),
											),
										),
									),
								),
							),
						),

						'range' => array(

							'label' => __('Range Slider', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=range]',

							'children' => array(

								'track' => array(

									'label' => __('Track', 'ws-form'),

									'meta' => array(

										'field_range_track_border_radius' => array(

											'label' => __('Border Radius', 'ws-form'),
											'var' => '--wsf-field-range-track-border-radius',
											'type' => 'size',
											'default' => 'var(--wsf-field-border-radius)',
										),

										'field_range_track_color' => array(

											'label' => __('Color', 'ws-form'),
											'var' => '--wsf-field-range-track-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-neutral-light-80)',
										),

										'field_range_track_lower_color' => array(

											'label' => __('Color - Lower', 'ws-form'),
											'var' => '--wsf-field-range-track-lower-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-accent)',
										),

										'field_range_track_upper_color' => array(

											'label' => __('Color - Upper', 'ws-form'),
											'var' => '--wsf-field-range-track-upper-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-neutral-light-80)',
										),

										'field_range_track_size' => array(

											'label' => __('Size', 'ws-form'),
											'var' => '--wsf-field-range-track-size',
											'type' => 'calc',
											'default' => 'calc(var(--wsf-field-range-thumb-size) / 2)',
										),
									),
								),

								'thumb' => array(

									'label' => __('Thumb', 'ws-form'),

									'meta' => array(

										'field_range_thumb_color' => array(

											'label' => __('Color', 'ws-form'),
											'var' => '--wsf-field-range-thumb-color',
											'type' => 'color',
											'default' => 'var(--wsf-form-color-accent)',
										),

										'field_range_thumb_size' => array(

											'label' => __('Size', 'ws-form'),
											'var' => '--wsf-field-range-thumb-size',
											'type' => 'calc',
											'default' => 'calc(var(--wsf-field-height) / 2)',
										),
									),
								),

								'tickmark' => array(

									'label' => __('Tickmark', 'ws-form'),

									'meta' => array(

										'field_range_tickmark_color' => array(

											'label' => __('Color', 'ws-form'),
											'var' => '--wsf-field-range-tickmark-color',
											'type' => 'color',
											'default' => 'var(--wsf-field-help-color)',
										),

										'field_range_tickmark_height' => array(

											'label' => __('Height', 'ws-form'),
											'var' => '--wsf-field-range-tickmark-height',
											'type' => 'calc',
											'default' => 'var(--wsf-field-range-thumb-size)',
										),

										'field_range_tickmark_width' => array(

											'label' => __('Width', 'ws-form'),
											'var' => '--wsf-field-range-tickmark-width',
											'type' => 'calc',
											'default' => 'var(--wsf-field-border-width)',
										),
									),
								),

								'tickmark_label' => array(

									'label' => __('Tickmark Labels', 'ws-form'),

									'children' => self::get_style_color_typograpy('field_range_tickmark_label', 'field-range-tickmark-label', 'var(--wsf-field-help-color)', false),
								),
							),
						),
						'select' => array(

							'label' => __('Select', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=select]',

							'children' => array(

								'arrow' => array(

									'label' => __('Down Arrow', 'ws-form'),

									'meta' => array(

										'field_select_arrow_color' => array(

											'label' => __('Color', 'ws-form'),
											'var' => '--wsf-field-select-arrow-color',
											'type' => 'color',
											'default' => 'var(--wsf-field-color)',
										),

										'field_select_arrow_width' => array(

											'label' => __('Width', 'ws-form'),
											'var' => '--wsf-field-select-arrow-width',
											'type' => 'size',
											'default' => '12px',
										),

										'field_select_arrow_height' => array(

											'label' => __('Height', 'ws-form'),
											'var' => '--wsf-field-select-arrow-height',
											'type' => 'size',
											'default' => '6px',
										),

										'field_select_padding_right' => array(

											'label' => __('Padding Right', 'ws-form'),
											'var' => '--wsf-field-select-padding-right',
											'type' => 'calc',
											'default' => 'calc((var(--wsf-field-padding-horizontal) * 2) + var(--wsf-field-select-arrow-width))',
										),
									),
								),

								'select2' => array(

									'label' => __('Select2', 'ws-form'),

									'children' => array(

										'select2_choice' => array(

											'label' => __('Choice (Pill)', 'ws-form'),

											'children' => array(

												'border' => array(

													'label' => __('Border', 'ws-form'),

													'meta' => array(

														'field_select_select2_choice_border_radius' => array(

															'label' => __('Radius', 'ws-form'),
															'var' => '--wsf-field-select-select2-choice-border-radius',
															'type' => 'size',
															'default' => 'var(--wsf-field-border-radius)',
														),
													),
												),

												'color' => array(

													'label' => __('Color', 'ws-form'),

													'meta' => array(

														'field_select_select2_choice_color_background' => array(

															'label' => __('Background', 'ws-form'),
															'var' => '--wsf-field-select-select2-choice-color-background',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-neutral-light-80)',
														),

														'field_select_select2_choice_color' => array(

															'label' => __('Text', 'ws-form'),
															'var' => '--wsf-field-select-select2-choice-color',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-base)',
														),

														'field_select_select2_choice_color_remove' => array(

															'label' => __('Remove', 'ws-form'),
															'var' => '--wsf-field-select-select2-choice-color-remove',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-base)',
														),
													),
												),

												'typography' => array(

													'label' => __('Typography', 'ws-form'),

													'meta' => self::get_styler_typography_meta('field_select_select2_choice', 'field-select-select2-choice', 'var(--wsf-form-font-size-small)'),
												),

												'gap' => array(

													'label' => __('Gap', 'ws-form'),

													'meta' => self::get_styler_gap_meta('field_select_select2_choice', 'field-select-select2-choice'),
												),
											),
										),

										'select2_result' => array(

											'label' => __('Result', 'ws-form'),

											'children' => array(

												'color_background' => array(

													'label' => __('Background Color', 'ws-form'),

													'meta' => array(

														'field_select_select2_result_color_background' => array(

															'label' => __('Default', 'ws-form'),
															'var' => '--wsf-field-select-select2-result-color-background',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-base-contrast)'
														),

														'field_select_select2_result_color_background_selected' => array(

															'label' => __('Selected', 'ws-form'),
															'var' => '--wsf-field-select-select2-result-color-background-selected',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-neutral-light-80)',
														),

														'field_select_select2_result_color_background_highlighted' => array(

															'label' => __('Highlighted', 'ws-form'),
															'var' => '--wsf-field-select-select2-result-color-background-highlighted',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-primary)',
														),
													),
												),

												'text_color' => array(

													'label' => __('Text Color', 'ws-form'),

													'meta' => array(

														'field_select_select2_result_color' => array(
															'label' => __('Default', 'ws-form'),
															'var' => '--wsf-field-select-select2-result-color',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-base)'
														),

														'field_select_select2_result_color_selected' => array(

															'label' => __('Selected', 'ws-form'),
															'var' => '--wsf-field-select-select2-result-color-selected',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-base)'
														),

														'field_select_select2_result_color_highlighted' => array(

															'label' => __('Highlighted', 'ws-form'),
															'var' => '--wsf-field-select-select2-result-color-highlighted',
															'type' => 'color',
															'default' => 'var(--wsf-form-color-base-contrast)'
														),
													),
												),

												'typography' => array(

													'label' => __('Typography', 'ws-form'),

													'meta' => self::get_styler_typography_meta('field_select_select2_result', 'field-select-select2-result'),
												),

												'padding' => array(

													'label' => __('Padding', 'ws-form'),

													'meta' => self::get_styler_padding_meta('field_select_select2_result', 'field-select-select2-result', '7px', '5px'),
												),
											),
										),
									),
								),
							),
						),

						'texteditor' => array(

							'label' => __('Text Editor', 'ws-form'),

							'group_focus_selector' => '.wsf-field-wrapper[data-type=texteditor]',

							'children' => self::get_style_color_typograpy('field_texteditor', 'field-texteditor', 'var(--wsf-form-color-base)', false),
						),
					),
				),
				// Conversational
				'conversational' => array(

					'label' => __('Conversational', 'ws-form'),

					'meta' => array(

						'conv_form_width_max' => array(

							'label' => __('Maximum Form Width', 'ws-form'),
							'var' => '--wsf-conversational-width-max',
							'type' => 'size',
							'default' => '800px',
							'px_min' => '200',
							'px_max' => '2000',
							'legacy_v1_option_key' => 'skin_conversational_max_width',
						),

						'conv_form_padding_horizontal' => array(

							'label' => __('Padding - Horizontal', 'ws-form'),
							'var' => '--wsf-conversational-padding-horizontal',
							'type' => 'size',
							'default' => '40px'
						),

						'conv_color_background' => array(

							'label' => __('Background Color', 'ws-form'),
							'var' => '--wsf-conversational-color-background',
							'type' => 'color',
							'default' => '#efeff4',
							'legacy_v1_option_key' => 'skin_conversational_color_background',
						),

						'conv_nav_color_background' => array(

							'label' => __('Nav Background Color', 'ws-form'),
							'var' => '--wsf-conversational-nav-color-background',
							'type' => 'color',
							'default' => '#585858',
							'legacy_v1_option_key' => 'skin_conversational_color_background_nav',
						),

						'conv_nav_color' => array(

							'label' => __('Nav Foreground Color', 'ws-form'),
							'var' => '--wsf-conversational-nav-color',
							'type' => 'color',
							'default' => '#ffffff',
							'legacy_v1_option_key' => 'skin_conversational_color_foreground_nav',
						),

						'conv_section_opacity_inactive' => array(

							'label' => __('Inactive Section Opacity', 'ws-form'),
							'var' => '--wsf-conversational-section-opacity-inactive',
							'type' => 'opacity',
							'default' => '0.25',
							'legacy_v1_option_key' => 'skin_conversational_opacity_section_inactive',
						),
					),
				),
			);

			// Build styler
			$styler = array(

				'meta' => $meta,
				'palette' => WS_Form_Color::get_palette()
			);

			// Apply filter
			return apply_filters('wsf_config_styler', $styler);
		}

		// Styler - Color
		public static function get_styler_color($key_prefix = 'field', $var_prefix = 'field', $default_color = 'var(--wsf-form-color-base)', $default_color_background = 'var(--wsf-form-color-background)') {

			$meta = array(

				'color' => array(

					'label' => __('Color', 'ws-form'),

					'meta' => array(

						$key_prefix . '_color' => array(

							'label' => __('Text', 'ws-form'),
							'var' => '--wsf-' . $var_prefix . '-color',
							'type' => 'color',
							'default' => $default_color,
						),
					),
				),
			);

			// Background color
			if(!empty($default_color_background)) {

				$meta['color']['meta'] = array_merge(

					array(

						$key_prefix . '_color_background' => array(

							'label' => __('Background', 'ws-form'),
							'var' => '--wsf-' . $var_prefix . '-color-background',
							'type' => 'color',
							'default' => $default_color_background,
						),
					),

					$meta['color']['meta']
				);
			}

			// Apply filter
			return apply_filters('wsf_config_styler_color', $meta);
		}

		// Styler - Typography
		public static function get_styler_typography($key_prefix = 'field', $var_prefix = 'field', $default_font_size = 'var(--wsf-form-font-size)', $default_font_weight = 'var(--wsf-form-font-weight)', $default_line_height = 'var(--wsf-form-line-height)') {

			$meta = array(

				'typography' => array(

					'label' => __('Typography', 'ws-form'),

					'meta' => self::get_styler_typography_meta($key_prefix, $var_prefix, $default_font_size, $default_font_weight, $default_line_height),
				),
			);

			// Apply filter
			return apply_filters('wsf_config_styler_typography', $meta);
		}

		// Styler - Typography - Vars
		public static function get_styler_typography_meta($key_prefix = 'field', $var_prefix = 'field', $default_font_size = 'var(--wsf-form-font-size)', $default_font_weight = 'var(--wsf-form-font-weight)', $default_line_height = 'var(--wsf-form-line-height)') {

			$meta = array(

				$key_prefix . '_font_family' => array(

					'label' => __('Font Family', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-font-family',
					'type' => 'text',
					'default' => 'var(--wsf-form-font-family)',
					'datalist' => 'wsf-styler-datalist-font-family',
				),

				$key_prefix . '_font_size' => array(

					'label' => __('Font Size', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-font-size',
					'type' => 'size',
					'default' => $default_font_size,
				),

				$key_prefix . '_font_style' => array(

					'label' => __('Font Style', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-font-style',
					'type' => 'text',
					'default' => 'var(--wsf-form-font-style)',
					'datalist' => 'wsf-styler-datalist-font-style',
				),

				$key_prefix . '_font_weight' => array(

					'label' => __('Font Weight', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-font-weight',
					'type' => 'weight',
					'default' => $default_font_weight,
					'datalist' => 'wsf-styler-datalist-font-weight',
				),

				$key_prefix . '_letter_spacing' => array(

					'label' => __('Letter Spacing', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-letter-spacing',
					'type' => 'size',
					'default' => 'var(--wsf-form-letter-spacing)',
				),

				$key_prefix . '_line_height' => array(

					'label' => __('Line Height', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-line-height',
					'type' => 'size',
					'default' => $default_line_height,
				),

				$key_prefix . '_text_decoration' => array(

					'label' => __('Text Decoration', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-text-decoration',
					'type' => 'text',
					'default' => 'var(--wsf-form-text-decoration)',
					'datalist' => 'wsf-styler-datalist-text-decoration',
				),

				$key_prefix . '_text_transform' => array(

					'label' => __('Text Transform', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-text-transform',
					'type' => 'text',
					'default' => 'var(--wsf-form-text-transform)',
					'datalist' => 'wsf-styler-datalist-text-transform',
				),
			);

			// Apply filter
			return apply_filters('wsf_config_styler_typography_meta', $meta);
		}

		// Styler - Padding - Vars
		public static function get_styler_padding_meta($key_prefix = 'field', $var_prefix = 'field', $default_horizontal = '10px', $default_vertical = '8.5px') {

			$meta = array(

				$key_prefix . '_padding_horizontal' => array(

					'label' => __('Horizontal', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-padding-horizontal',
					'type' => 'size',
					'default' => $default_horizontal
				),

				$key_prefix . '_padding_vertical' => array(

					'label' => __('Vertical', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-padding-vertical',
					'type' => 'size',
					'default' => $default_vertical
				),

				$key_prefix . '_padding' => array(

					'label' => __('Padding', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-padding',
					'type' => 'calc',
					'default' => 'var(--wsf-' . $var_prefix . '-padding-vertical) var(--wsf-' . $var_prefix . '-padding-horizontal)'
				),
			);

			// Apply filter
			return apply_filters('wsf_config_styler_padding_meta', $meta);
		}

		// Styler - Gap
		public static function get_styler_gap($key_prefix = 'field', $var_prefix = 'field', $default_gap = '5px') {

			$meta = array(

				'gap' => array(

					'label' => __('Gap', 'ws-form'),

					'meta' => self::get_styler_gap_meta($key_prefix, $var_prefix, $default_gap),
				),
			);

			if($default_gap == '5px') {

				$meta['gap']['meta'][$key_prefix . '_gap']['legacy_v1_option_key'] = 'skin_spacing_small';
				$meta['gap']['meta'][$key_prefix . '_gap']['legacy_v1_suffix'] = 'px';
			}

			// Apply filter
			return apply_filters('wsf_config_styler_gap', $meta);
		}

		// Styler - Gap - Vars
		public static function get_styler_gap_meta($key_prefix = 'field', $var_prefix = 'field', $default_gap = '5px') {

			$meta = array(

				$key_prefix . '_gap' => array(

					'label' => __('Gap', 'ws-form'),
					'var' => '--wsf-' . $var_prefix . '-gap',
					'type' => 'size',
					'default' => $default_gap,
				)
			);

			if($default_gap == '5px') {

				$meta[$key_prefix . '_gap']['legacy_v1_option_key'] = 'skin_spacing_small';
				$meta[$key_prefix . '_gap']['legacy_v1_suffix'] = 'px';
			}

			// Apply filter
			return apply_filters('wsf_config_styler_gap_meta', $meta);
		}

		// Styler - Color + Typography
		public static function get_style_color_typograpy($key_prefix = 'field', $var_prefix = 'field', $default_color = 'var(--wsf-form-color-base)', $default_color_background = 'var(--wsf-form-color-background)', $default_font_size = 'var(--wsf-form-font-size)', $default_font_weight = 'var(--wsf-form-font-weight)', $default_line_height = 'var(--wsf-form-line-height)') {

			// Add color
			$meta = self::get_styler_color($key_prefix, $var_prefix, $default_color, $default_color_background);

			// Add typography
			$meta = array_merge($meta, self::get_styler_typography($key_prefix, $var_prefix, $default_font_size, $default_font_weight, $default_line_height));

			// Apply filter
			return apply_filters('wsf_config_styler_color_typograpy', $meta);
		}

		// Styler - Color + Typography + Gap
		public static function get_style_color_typograpy_gap($key_prefix = 'field', $var_prefix = 'field', $default_color = 'var(--wsf-form-color-base)', $default_color_background = 'var(--wsf-form-color-background)', $default_font_size = 'var(--wsf-form-font-size)', $default_font_weight = 'var(--wsf-form-font-weight)', $default_line_height = 'var(--wsf-form-line-height)', $default_gap = '5px') {

			// Add color
			$meta = self::get_styler_color($key_prefix, $var_prefix, $default_color, $default_color_background);

			// Add typography
			$meta = array_merge($meta, self::get_styler_typography($key_prefix, $var_prefix, $default_font_size, $default_font_weight, $default_line_height));

			// Add gap
			$meta = array_merge($meta, self::get_styler_gap($key_prefix, $var_prefix, $default_gap));

			// Apply filter
			return apply_filters('wsf_config_styler_color_typograpy_gap', $meta);
		}
	}