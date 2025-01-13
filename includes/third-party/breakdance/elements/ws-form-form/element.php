<?php

namespace BreakdanceCustomElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
	"BreakdanceCustomElements\\WS_Form_Form",
	\Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class WS_Form_Form extends \Breakdance\Elements\Element
{
	static function uiIcon()
	{
		return \WS_Form_Common::get_admin_icon('currentColor', false);
	}

	static function tag()
	{
		return 'div';
	}

	static function tagOptions()
	{
		return [];
	}

	static function tagControlPath()
	{
		return false;
	}

	static function name()
	{
		return WS_FORM_NAME_GENERIC;
	}

	static function className()
	{
		return 'ws-form-breakdance';
	}

	static function category()
	{
		return 'forms';
	}

	static function badge()
	{
		return false;
	}

	static function slug()
	{
		return get_called_class();
	}

	static function template()
	{
		return '%%SSR%%';
	}

	static function ssr($propertiesData, $parentPropertiesData = [], $isBuilder = false, $repeaterItemNodeId = null)
	{
		// Get form ID
		$form_id = absint(@$propertiesData['content']['form']['form_id']);

		// Get form dynamic ID
		$form_dynamic_id = absint(@$propertiesData['content']['form']['form_dynamic_id']);

		if(
			($form_id === 0) &&
			($form_dynamic_id > 0) 
		) {
			$form_id = $form_dynamic_id;
		}

		// Get form element ID
		$form_element_id = @$propertiesData['content']['form']['form_element_id'];

		if($form_id > 0) {

			// Show shortcode
			$shortcode = sprintf('[ws_form id="%u"%s]', $form_id, ($form_element_id != '') ? sprintf(' element_id="%s"', esc_attr($form_element_id)) : '');
			return do_shortcode($shortcode);

		} else {

			if($isBuilder) {

				return __('Please select a form in the element settings.', 'ws-form');

			} else {

				return '';
			}
		}
	}

	static function defaultCss()
	{
		return '';
	}

	static function defaultProperties()
	{
		return false;
	}

	static function defaultChildren()
	{
		return false;
	}

	static function cssTemplate()
	{
		return file_get_contents(__DIR__ . '/css.twig');
	}

	static function designControls()
	{
        return [c(
        "container",
        "Container",
        [c(
        "width",
        "Width",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "height",
        "Height",
        [],
        ['type' => 'unit', 'layout' => 'inline'],
        true,
        false,
        [],
      ), c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline', 'colorOptions' => ['type' => 'solidAndGradient']],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding", 
      "padding", 
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\borders",
      "Borders", 
      "borders", 
       ['type' => 'popout']
     )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\typography_with_effects_and_align",
      "Typography", 
      "typography", 
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Spacing", 
      "spacing", 
       ['type' => 'popout']
     )];
	}

	static function contentControls()
	{

		// Build form items
		$form_items = array();

		$forms = \WS_Form_Common::get_forms_array();

		foreach($forms as $id => $label) {

			$form_items[] = array(

				'value' => $id,
				'text' => $label
			);
		}

		return [

			c(
				"form",
				"Form",
				[
					c(
						"form_id",
						"Form",
						[],
						[
							'type' => 'dropdown',
							'layout' => 'vertical',
							'placeholder' => __('Select form or enter form ID below...', 'ws-form'),
							'items' => $form_items
						],
						false,
						false,
						[],
					),

					c(
						"form_dynamic_id",
						"Form ID (Optional)",
						[],
						[
							'type' => 'text',
							'layout' => 'vertical',
							'placeholder' => __('Enter the form ID to render.', 'ws-form'),
							'condition' => [

								'path' => 'content.form.form_id',
								'operand' => 'is not set',
								'value' => true
							]
						],
						false,
						false,
						[],
					),

					c(
						"form_element_id",
						"Form Element ID (Optional)",
						[],
						[
							'type' => 'text',
							'layout' => 'vertical',
							'placeholder' => __('Enter the id attribute of the form element.', 'ws-form')
						],
						false,
						false,
						[],
					)
				],
				['type' => 'section', 'layout' => 'vertical'],
				false,
				false,
				[],
			)
		];
	}

	static function settingsControls()
	{
		return [];
	}

	static function dependencies()
	{
		return false;
	}

	static function settings()
	{
		return false;
	}

	static function addPanelRules()
	{
		return false;
	}

	static public function actions()
	{
        return [
            'onMountedElement' => [
                [
                    'script' => <<<JS
                        (function () {
                        	wsf_form_init(true);
                        } ());
                    JS,
                ],
            ],
        ];
	}

	static function nestingRule()
	{
		return ["type" => "final",   ];
	}

	static function spacingBars()
	{
		return false;
	}

	static function attributes()
	{
		return false;
	}

	static function experimental()
	{
		return false;
	}

	static function order()
	{
		return 0;
	}

	static function dynamicPropertyPaths()
	{
		return [

			'0' => ['accepts' => 'string', 'path' => 'content.form.form_dynamic_id'],
			'1' => ['accepts' => 'string', 'path' => 'content.form.form_element_id']
		];
	}

	static function additionalClasses()
	{
		return false;
	}

	static function projectManagement()
	{
		return false;
	}

	static function propertyPathsToWhitelistInFlatProps()
	{
		return false;
	}

	static function propertyPathsToSsrElementWhenValueChanges()
	{
		return false;
	}
}
