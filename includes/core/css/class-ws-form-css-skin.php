<?php

	// Legacy
	class WS_Form_CSS_Skin extends WS_Form_CSS {

		// Render
		public function render_skin() {

			// Load skin
			self::skin_load();

			// Load variables
			self::skin_variables();

			// Skin color shades
			self::skin_color_shades();

			// Forms
			$this->background_color = $this->color_default_inverted;
			$this->border_color = $this->color_default_lighter;
			$this->checked_color = $this->color_primary;
			$this->color = $this->color_default;
			$this->disabled_background_color = $this->color_default_lightest;
			$this->disabled_border_color = $this->color_default_lighter;
			$this->disabled_color = $this->color_default_light;
			$this->error_background_color = $this->color_default_inverted;
			$this->error_border_color = $this->color_danger;
			$this->error_color = $this->color_default;
			$this->focus = true; // true | false
			$this->focus_background_color = $this->color_default_inverted;
			$this->focus_border_color = $this->color_primary;
			$this->focus_color = $this->color_default;
			$this->help_color = $this->color_default;
			$this->invalid_feedback_color = $this->color_danger;
			$this->hover = false; // true | false
			$this->hover_background_color = $this->color_default_inverted;
			$this->hover_border_color = $this->color_primary;
			$this->hover_color = $this->color_default;
			$this->label_color = $this->color_default;
			$this->placeholder_color = $this->color_default_light;
			$this->spacing_horizontal = 10;
			$this->spacing_vertical = 8.5;

			$uom = 'px';
			$input_height = round(($this->font_size * $this->line_height) + ($this->spacing_vertical * 2) + ($this->border_width * 2));
			$checkbox_size = round($this->font_size * $this->line_height);
			$radio_size = round($this->font_size * $this->line_height);
			$color_size = $input_height;
			$range_size = round(($input_height / 2));
			$progress_size = round(($input_height / 2));
			$meter_size = round(($input_height / 2));
?>
/* Skin ID: <?php WS_Form_Common::echo_esc_html($this->skin_label); ?> (<?php WS_Form_Common::echo_esc_html($this->skin_id); ?>) */

.wsf-form {
<?php if ($this->color_form_background) { ?>
	background-color: <?php self::e($this->color_form_background); ?>;
<?php } ?>
	box-sizing: border-box;
	color: <?php self::e($this->color_default); ?>;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	-webkit-tap-highlight-color: transparent;
	text-size-adjust: 100%;
}

.wsf-form *, .wsf-form *::before, .wsf-form *::after {
	box-sizing: inherit;
}

.wsf-section,
.wsf-fieldset {
	border: none;
	margin: 0;
	min-width: 0;
	padding: 0;
}

.wsf-section.wsf-sticky {
	align-self: flex-start;
<?php if ($this->color_form_background) { ?>
	background-color: <?php self::e($this->color_form_background); ?>;
<?php } ?>
	height: auto;
	margin-top: -<?php self::e($this->grid_gutter . $uom); ?>;
	padding-top: <?php self::e($this->grid_gutter . $uom); ?>;
	position: -webkit-sticky;
	position: sticky;
	top: 0;
	z-index: 2;
}

.wsf-section > legend,
.wsf-fieldset > legend {
	border: 0;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size_large . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin-bottom: <?php self::e($this->spacing . $uom); ?>;
	padding: 0;
}

.wsf-form ul.wsf-group-tabs {
	border-bottom: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->color_default_lighter); ?>;
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	list-style: none;
	margin: 0 0 <?php self::e($this->grid_gutter . $uom); ?> 0;
	padding: 0;
	position: relative;
}

.wsf-form ul.wsf-group-tabs > li {
	box-sizing: border-box;
	margin-bottom: -<?php self::e($this->border_width . $uom); ?>;
	outline: none;
	position: relative;
}

.wsf-form ul.wsf-group-tabs > li > a {
	background-color: transparent;
	border: <?php self::e(($this->border_width . $uom . ' ' . $this->border_style) . ' transparent'); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-top-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-top-right-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	box-shadow: none;
	color: <?php self::e($this->color_default); ?>;
	cursor: pointer;
	display: block;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	padding: 8px 16px;
	text-align: center;
	text-decoration: none;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	white-space: nowrap;
}

.wsf-form ul.wsf-group-tabs > li > a:focus {
	border-color: <?php self::e($this->color_default_lighter); ?>;
	outline: 0;
}

.wsf-form ul.wsf-group-tabs > li > a.wsf-tab-disabled {
	color: <?php self::e($this->color_default_light); ?>;
	cursor: not-allowed;
	pointer-events: none;
}

.wsf-form ul.wsf-group-tabs > li.wsf-tab-active {
	z-index: 1;
}

.wsf-form ul.wsf-group-tabs > li.wsf-tab-active > a {
	background-color: <?php self::e($this->color_default_inverted); ?>;
	border-color: <?php self::e($this->color_default_lighter); ?>;
	border-bottom-color: transparent;
	color: <?php self::e($this->color_default); ?>;
	cursor: default;
}

.wsf-form-canvas.wsf-vertical {
	display: flex;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs {
	border-bottom: none;
	-webkit-border-end: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->color_default_lighter); ?>;
	border-inline-end: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->color_default_lighter); ?>;
	flex-direction: column;
	-webkit-margin-end: <?php self::e($this->grid_gutter . $uom); ?>;
	margin-inline-end: <?php self::e($this->grid_gutter . $uom); ?>;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs > li {
	margin-bottom: 0;
	-webkit-margin-end: -<?php self::e($this->border_width . $uom); ?>;
	margin-inline-end: -<?php self::e($this->border_width . $uom); ?>;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs > li > a {
	border: <?php self::e(($this->border_width . $uom . ' ' . $this->border_style) . ' transparent'); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-top-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-top-right-radius: 0;
	border-bottom-left-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs > li > a:focus {
	border-color: <?php self::e($this->color_default_lighter); ?>;
	outline: 0;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs > li.wsf-tab-active > a {
	border-color: <?php self::e($this->color_default_lighter); ?>;
	-webkit-border-end-color: transparent;
	border-inline-end-color: transparent;
}

.wsf-form-canvas.wsf-vertical .wsf-groups {
	width: 100%;
}

.wsf-form ul.wsf-group-tabs.wsf-steps {
	border-bottom: none;
	counter-reset: step;
	justify-content: space-between;
	flex-wrap: nowrap;
	z-index: 0;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li {
	margin-bottom: 0;
	width: 100%;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li > a {
	border: none;
	padding: 0;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li > a::before {
	background-color: <?php self::e($this->color_primary); ?>;
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->color_primary); ?>;
	border-radius: 50%;
  	content: counter(step);
  	counter-increment: step;
	color: <?php self::e($this->color_default_inverted); ?>;
	display: block;
	font-weight: bold;
  	height: <?php self::e($input_height . $uom); ?>;
	line-height: <?php self::e(($input_height - ($this->border_width * 2)) . $uom); ?>;
	margin: 0 auto <?php self::e($this->spacing . $uom); ?>;
  	text-align: center;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	width: <?php self::e($input_height . $uom); ?>;
}

.wsf-form ul.wsf-group-tabs.wsf-steps.wsf-steps-success > li > a::before {
	background-color: <?php self::e($this->color_success); ?>;
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->color_success); ?>;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li > a::after {
	background-color: <?php self::e($this->color_primary); ?>;
	content: '';
	height: <?php self::e($this->border_width . $uom); ?>;
	left: -50%;
	position: absolute;
	top: <?php self::e(($input_height / 2) . $uom); ?>;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	width: 100%;
	z-index: -2;
}

.wsf-form ul.wsf-group-tabs.wsf-steps.wsf-steps-success > li > a::after {
	background-color: <?php self::e($this->color_success); ?>;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li:first-child > a::after {
	content: none;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li > a:not(.wsf-tab-disabled):focus:before {
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}

.wsf-form ul.wsf-group-tabs.wsf-steps.wsf-steps-success > li > a:not(.wsf-tab-disabled):focus:before {
	border-color: <?php self::e($this->color_success); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_success, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li > a.wsf-tab-disabled:before,
.wsf-form ul.wsf-group-tabs.wsf-steps > li.wsf-tab-active ~ li > a.wsf-tab-disabled:before {
	color: <?php self::e($this->color_default_light); ?>;
	cursor: not-allowed;
	pointer-events: none;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li.wsf-tab-active {
	z-index: -1;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li.wsf-tab-active > a {
	background-color: transparent;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li.wsf-tab-active > a::before {
	background-color: <?php self::e($this->color_default_inverted); ?>;
	color: <?php self::e($this->color_primary); ?>;
}

.wsf-form ul.wsf-group-tabs.wsf-steps.wsf-steps-success > li.wsf-tab-active > a::before {
	color: <?php self::e($this->color_success); ?>;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li.wsf-tab-active ~ li > a::before {
	background-color: <?php self::e($this->color_default_inverted); ?>;
	border-color: <?php self::e($this->border_color); ?>;
	color: <?php self::e($this->color_default); ?>;
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li.wsf-tab-active ~ li > a::after {
	background-color: <?php self::e($this->border_color); ?>;
}

.wsf-form ul.wsf-group-tabs.wsf-steps.wsf-steps-no-label > li > a > span {
	display: none;
}

.wsf-form ul.wsf-group-tabs.wsf-steps.wsf-steps-checks > li > a::before {
	content: '\2713';
}

.wsf-form ul.wsf-group-tabs.wsf-steps.wsf-steps-checks > li.wsf-tab-active > a::before {
	content: counter(step);
}

.wsf-form ul.wsf-group-tabs.wsf-steps.wsf-steps-checks > li.wsf-tab-active ~ li > a::before {
	content: counter(step);
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-steps {
	-webkit-border-end: none;
    border-inline-end: none;
    justify-content: flex-start;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-steps > li {
	margin-bottom: <?php self::e(($this->grid_gutter - 1) . $uom); ?>;
	-webkit-margin-end: 0;
	margin-inline-end: 0;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-steps > li > a::after {
	height: 100%;
	left: <?php self::e(($input_height / 2) . $uom); ?>;
	top: -50%;
	width: <?php self::e($this->border_width . $uom); ?>;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-steps > li > a {
	text-align: left;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-steps > li > a::before {
	display: inline-block;
	margin-bottom: 0;
	-webkit-margin-end: <?php self::e($this->spacing . $uom); ?>;
	margin-inline-end: <?php self::e($this->spacing . $uom); ?>;
}

.wsf-form ul.wsf-group-tabs.wsf-sticky {
	align-self: flex-start;
<?php if ($this->color_form_background) { ?>
	background-color: <?php self::e($this->color_form_background); ?>;
<?php } ?>
	height: auto;
	position: -webkit-sticky;
	position: sticky;
	top: 0;
	z-index: 2;
}

.wsf-form ul.wsf-group-tabs.wsf-sticky {
	margin-top: -<?php self::e($this->grid_gutter . $uom); ?>;
	padding-top: <?php self::e($this->grid_gutter . $uom); ?>;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-sticky {
	margin-top: 0;
	padding-top: 0;
}

.wsf-form ul.wsf-group-tabs.wsf-sticky.wsf-steps {
	margin-bottom: 0;
	padding-bottom: <?php self::e($this->grid_gutter . $uom); ?>;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-sticky.wsf-steps {
	margin-top: -<?php self::e($this->grid_gutter . $uom); ?>;
	padding-top: <?php self::e($this->grid_gutter . $uom); ?>;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-sticky.wsf-steps > li > a:last-child {
	margin-bottom: 0;
}

.wsf-grid {
	margin-left: -<?php self::e(($this->grid_gutter / 2) . $uom); ?>;
	margin-right: -<?php self::e(($this->grid_gutter / 2) . $uom); ?>;
}

.wsf-tile {
	padding-left: <?php self::e(($this->grid_gutter / 2) . $uom); ?>;
	padding-right: <?php self::e(($this->grid_gutter / 2) . $uom); ?>;
}

.wsf-field-wrapper {
	margin-bottom: <?php self::e($this->grid_gutter . $uom); ?>;
}

.wsf-field-wrapper.wsf-sticky {
	align-self: flex-start;
<?php if ($this->color_form_background) { ?>
	background-color: <?php self::e($this->color_form_background); ?>;
<?php } ?>
	height: auto;
	margin-bottom: 0;
	margin-top: -<?php self::e($this->grid_gutter . $uom); ?>;
	padding-bottom: <?php self::e($this->grid_gutter . $uom); ?>;
	padding-top: <?php self::e($this->grid_gutter . $uom); ?>;
	position: -webkit-sticky;
	position: sticky;
	top: 0;
	z-index: 2;
}

.wsf-field-wrapper[data-type='texteditor'],
.wsf-field-wrapper[data-type='html'],
.wsf-field-wrapper[data-type='divider'],
.wsf-field-wrapper[data-type='message'] {
	margin-bottom: 0;
}

.wsf-inline {
	display: inline-flex;
	flex-direction: column;
	-webkit-margin-end: <?php self::e($this->spacing . $uom); ?>;
	margin-inline-end: <?php self::e($this->spacing . $uom); ?>;
}

.wsf-label-wrapper label.wsf-label {
	padding: <?php self::e(($this->spacing_vertical + $this->border_width) . $uom); ?> 0;
	margin-bottom: 0;
}

label.wsf-label {
	display: block;
<?php if ($this->label_color != $this->color_default) { ?>
	color: <?php self::e($this->label_color); ?>;
<?php } ?>
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin-bottom: <?php self::e($this->spacing_small . $uom); ?>;
	user-select: none;
}

.wsf-field + label.wsf-label,
select.wsf-field ~ .select2-container + label.wsf-label,
input[type=text].wsf-field ~ .dropzone + label.wsf-label,
input[type=text].wsf-field ~ canvas + label.wsf-label,
.wsf-input-group-append + label.wsf-label {
	margin-bottom: 0;
	margin-top: <?php self::e($this->spacing_small . $uom); ?>;
}

.wsf-invalid-feedback {
	color: <?php self::e($this->invalid_feedback_color); ?>;
	font-size: <?php self::e($this->font_size_small . $uom); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin-top: <?php self::e($this->spacing_small . $uom); ?>;
}

.wsf-help {
	color: <?php self::e($this->help_color); ?>;
	display: block;
	font-size: <?php self::e($this->font_size_small . $uom); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin-top: <?php self::e($this->spacing_small . $uom); ?>;
}

.wsf-help-pre {
	margin-bottom: <?php self::e($this->spacing_small . $uom); ?>;
	margin-top: 0;
}

.wsf-label-position-inside .wsf-help-pre {
	margin-bottom: <?php self::e(($this->spacing_small + $this->spacing_vertical) . $uom); ?>;	
}

[data-wsf-tooltip=""]:before,
[data-wsf-tooltip=""]:after {
	opacity: 0 !important;
}

[data-wsf-tooltip] {
	cursor: help;
	position: relative;
}

[data-wsf-tooltip] svg {
	display: inline-block;
	vertical-align: text-bottom;
}

[data-wsf-tooltip]:before,
[data-wsf-tooltip]:after {
	opacity: 0;
	pointer-events: none;
	position: absolute;
<?php if ($this->transition) { ?>
	transition: opacity <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, visibility <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	user-select: none;
	visibility: hidden;
	z-index: 1000;
}

[data-wsf-tooltip]:focus {
	outline: 0;
}

[data-wsf-tooltip]:hover:before,
[data-wsf-tooltip]:hover:after,
[data-wsf-tooltip]:focus:before,
[data-wsf-tooltip]:focus:after {
	opacity: 1;
	visibility: visible;
}

[data-wsf-tooltip]:before {
	border: 5px solid transparent;
	border-top-color: <?php self::e($this->color_default_light); ?>;
	bottom: calc(100% - 5px);
	content: "";
	left: 50%;
	transform: translateX(-50%);
}

[data-wsf-tooltip]:after {
	background-color: <?php self::e($this->color_default_light); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	bottom: calc(100% + <?php self::e($this->spacing_small . $uom); ?>);
	color: <?php self::e($this->color_default_inverted); ?>;
	content: attr(data-wsf-tooltip);
	font-size: <?php self::e($this->font_size_small . $uom); ?>;
	left: 50%;
	max-width: 320px;
	min-width: 180px;
	padding: <?php self::e($this->spacing . $uom); ?>;
	text-align: center;
	transform: translateX(-50%);
}

.wsf-input-group {
	align-items: stretch;
	display: flex;
	flex-wrap: wrap;
	width: 100%;
}

.wsf-input-group > .wsf-field,
.wsf-input-group > select.wsf-field ~ .select2-container,
.wsf-input-group > input[type=text].wsf-field ~ .dropzone,
.wsf-input-group > input[type=text].wsf-field ~ canvas,
.wsf-input-group > .iti {
	flex: 1 1 auto;
	min-width: 0;
	position: relative;
	width: 1% !important;
}

<?php if ($this->border_radius > 0) { ?>
.wsf-input-group-has-prepend > .wsf-field,
.wsf-input-group-has-prepend > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-prepend > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-prepend > .dropzone,
.wsf-input-group-has-prepend > .iti > input[type="tel"] {
	border-top-left-radius: 0 !important;
	border-bottom-left-radius: 0 !important;
}

.wsf-input-group-has-append > .wsf-field,
.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-append > .dropzone,
.wsf-input-group-has-append > .iti > input[type="tel"] {
	border-top-right-radius: 0 !important;
	border-bottom-right-radius: 0 !important;
}
<?php } ?>

.wsf-input-group-prepend,
.wsf-input-group-append {
	align-items: center;
	background-color: <?php self::e($this->color_default_lightest); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
	color: <?php self::e($this->color); ?>;
	display: flex;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
}

.wsf-input-group-prepend {
<?php if ($this->border_radius > 0) { ?>
	border-top-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-bottom-left-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
<?php if ($this->border) { ?>
	-webkit-border-end: none;
	border-inline-end: none;
<?php } ?>
}

.wsf-input-group-append {
<?php if ($this->border_radius > 0) { ?>
	border-top-right-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-bottom-right-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
<?php if ($this->border) { ?>
	-webkit-border-start: none;
	border-inline-start: none;
<?php } ?>
}

.wsf-input-group > label.wsf-label,
.wsf-input-group > .wsf-invalid-feedback,
.wsf-input-group > .wsf-help {
	width: 100%;
}

input[type=date].wsf-field,
input[type=datetime-local].wsf-field,
input[type=file].wsf-field,
input[type=month].wsf-field,
input[type=password].wsf-field,
input[type=search].wsf-field,
input[type=time].wsf-field,
input[type=week].wsf-field,
input[type=email].wsf-field,
input[type=number].wsf-field,
input[type=tel].wsf-field,
input[type=text].wsf-field,
input[type=url].wsf-field,
select.wsf-field,
textarea.wsf-field {
	-webkit-appearance: none;
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
	color: <?php self::e($this->color); ?>;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin: 0;
	min-height: auto;
	max-width: auto;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
	touch-action: manipulation;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, background-image <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	width: 100%;
}

input[type=date].wsf-field,
input[type=datetime-local].wsf-field,
input[type=file].wsf-field,
input[type=month].wsf-field,
input[type=password].wsf-field,
input[type=search].wsf-field,
input[type=time].wsf-field,
input[type=week].wsf-field,
input[type=email].wsf-field,
input[type=number].wsf-field,
input[type=tel].wsf-field,
input[type=text].wsf-field,
input[type=url].wsf-field,
select.wsf-field:not([multiple]):not([size]) {
	height: <?php self::e($input_height . $uom); ?>;
}

input[type=date].wsf-field::placeholder,
input[type=datetime-local].wsf-field::placeholder,
input[type=file].wsf-field::placeholder,
input[type=month].wsf-field::placeholder,
input[type=password].wsf-field::placeholder,
input[type=search].wsf-field::placeholder,
input[type=time].wsf-field::placeholder,
input[type=week].wsf-field::placeholder,
input[type=email].wsf-field::placeholder,
input[type=number].wsf-field::placeholder,
input[type=tel].wsf-field::placeholder,
input[type=text].wsf-field::placeholder,
input[type=url].wsf-field::placeholder,
select.wsf-field::placeholder,
textarea.wsf-field::placeholder {
	color: <?php self::e($this->placeholder_color); ?>;
	opacity: 1;
}

<?php if ($this->hover) { ?>
input[type=date].wsf-field:hover:enabled,
input[type=datetime-local].wsf-field:hover:enabled,
input[type=file].wsf-field:hover:enabled,
input[type=month].wsf-field:hover:enabled,
input[type=password].wsf-field:hover:enabled,
input[type=search].wsf-field:hover:enabled,
input[type=time].wsf-field:hover:enabled,
input[type=week].wsf-field:hover:enabled,
input[type=email].wsf-field:hover:enabled,
input[type=number].wsf-field:hover:enabled,
input[type=tel].wsf-field:hover:enabled,
input[type=text].wsf-field:hover:enabled,
input[type=url].wsf-field:hover:enabled,
select.wsf-field:hover:enabled,
textarea.wsf-field:hover:enabled {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>;
<?php } ?>
<?php if ($this->hover_color != $this->color) { ?>
	color: <?php self::e($this->hover_color); ?>;
<?php } ?>
}
<?php } ?>

input[type=date].wsf-field:focus,
input[type=datetime-local].wsf-field:focus,
input[type=file].wsf-field:focus,
input[type=month].wsf-field:focus,
input[type=password].wsf-field:focus,
input[type=search].wsf-field:focus,
input[type=time].wsf-field:focus,
input[type=week].wsf-field:focus,
input[type=email].wsf-field:focus,
input[type=number].wsf-field:focus,
input[type=tel].wsf-field:focus,
input[type=text].wsf-field:focus,
input[type=url].wsf-field:focus,
select.wsf-field:focus,
textarea.wsf-field:focus {
<?php if ($this->focus) { ?>
<?php if ($this->focus_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->focus_background_color); ?>;
<?php } ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
<?php if ($this->focus_color != $this->color) { ?>
	color: <?php self::e($this->focus_color); ?>;
<?php } ?>
<?php } ?>
	outline: 0;
}

input[type=date].wsf-field:disabled,
input[type=datetime-local].wsf-field:disabled,
input[type=file].wsf-field:disabled,
input[type=month].wsf-field:disabled,
input[type=password].wsf-field:disabled,
input[type=search].wsf-field:disabled,
input[type=time].wsf-field:disabled,
input[type=week].wsf-field:disabled,
input[type=email].wsf-field:disabled,
input[type=number].wsf-field:disabled,
input[type=tel].wsf-field:disabled,
input[type=text].wsf-field:disabled,
input[type=url].wsf-field:disabled,
select.wsf-field:disabled,
textarea.wsf-field:disabled {
<?php if ($this->disabled_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->disabled_background_color); ?>;
<?php } ?>
<?php if ($this->border) { ?>
<?php if ($this->disabled_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
<?php } ?>
<?php } ?>
<?php if ($this->disabled_color != $this->color) { ?>
	color: <?php self::e($this->disabled_color); ?>;
	-webkit-text-fill-color: <?php self::e($this->disabled_color); ?>;
<?php } else { ?>
	-webkit-text-fill-color: <?php self::e($this->color); ?>;
<?php } ?>
	cursor: not-allowed;
	opacity: 1;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
}

input[type=date].wsf-field::-moz-focus-inner,
input[type=datetime-local].wsf-field::-moz-focus-inner,
input[type=file].wsf-field::-moz-focus-inner,
input[type=month].wsf-field::-moz-focus-inner,
input[type=password].wsf-field::-moz-focus-inner,
input[type=search].wsf-field::-moz-focus-inner,
input[type=time].wsf-field::-moz-focus-inner,
input[type=week].wsf-field::-moz-focus-inner,
input[type=email].wsf-field::-moz-focus-inner,
input[type=number].wsf-field::-moz-focus-inner,
input[type=tel].wsf-field::-moz-focus-inner,
input[type=text].wsf-field::-moz-focus-inner,
input[type=url].wsf-field::-moz-focus-inner,
select.wsf-field::-moz-focus-inner,
textarea.wsf-field::-moz-focus-inner {
	border: 0;
	padding: 0;
}

/* Number */
input[type=number].wsf-field::-webkit-inner-spin-button,
input[type=number].wsf-field::-webkit-outer-spin-button {
	height: auto;
}

input[type=number][data-wsf-no-spinner].wsf-field::-webkit-outer-spin-button,
input[type=number][data-wsf-no-spinner].wsf-field::-webkit-inner-spin-button {
	display: none !important;
}
input[type=number][data-wsf-no-spinner].wsf-field {
	-moz-appearance: textfield;
}

/* Text Area */
textarea.wsf-field {
	min-height: <?php self::e($input_height . $uom); ?>;
	overflow: auto;
	resize: vertical;
}

textarea.wsf-field[data-textarea-type='tinymce'] {
	border-top-left-radius: 0;
	border-top-right-radius: 0;
}

[data-type='textarea'] .wp-editor-tabs {
	box-sizing: content-box;
}

[data-type='textarea'] .mce-btn.mce-active button,
[data-type='textarea'] .mce-btn.mce-active:hover button,
[data-type='textarea'] .mce-btn.mce-active i,
[data-type='textarea'] .mce-btn.mce-active:hover i {
	color: #000;
}

/* Select */
select.wsf-field:not([multiple]):not([size]) {
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
	background-position: right <?php self::e($this->spacing_horizontal . $uom); ?> center;
	background-repeat: no-repeat;
	background-size: 10px 5px;
	-webkit-padding-end: <?php self::e((($this->spacing_horizontal * 2) + 10) . $uom); ?>;
	padding-inline-end: <?php self::e((($this->spacing_horizontal * 2) + 10) . $uom); ?>;
/*	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;*/
}

select.wsf-field:not([multiple]):not([size])::-ms-expand {
	display: none;
}

select.wsf-field option {
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
}

<?php if ($this->hover) { ?>
<?php if ($this->hover_color != $this->color) { ?>
	select.wsf-field:not([multiple]):not([size]):hover {
		background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->hover_color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
	}
<?php } ?>
<?php } ?>

<?php if ($this->focus) { ?>
<?php if ($this->focus_color != $this->color) { ?>
select.wsf-field:not([multiple]):not([size]):focus {
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->focus_color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
}
<?php } ?>
<?php } ?>

select.wsf-field:not([multiple]):not([size]):-moz-focusring {
	color: transparent;
	text-shadow: 0 0 0 #000;
}

select.wsf-field:not([multiple]):not([size]):disabled {
<?php if ($this->disabled_color != $this->color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->disabled_color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
<?php } ?>
}

select.wsf-field optgroup {
	font-weight: bold;
}

<?php if ($this->disabled_color != $this->color) { ?>
select.wsf-field option:disabled {
	color: <?php self::e($this->disabled_color); ?>;
}
<?php } ?>

select.wsf-field ~ .select2-container {
	display: block;
	width: 100% !important;
}

select.wsf-field ~ .select2-container:focus {
	outline: none;
}

select.wsf-field ~ .select2-container .select2-selection--single,
select.wsf-field ~ .select2-container .select2-selection--multiple {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	height: auto;
	min-height: <?php self::e($input_height . $uom); ?>;
	outline: 0;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
	touch-action: manipulation;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, background-image <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

select.wsf-field ~ .select2-container .select2-selection--single {
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
	background-position: right <?php self::e($this->spacing_horizontal . $uom); ?> center;
	background-repeat: no-repeat;
	background-size: 10px 5px;
	-webkit-padding-end: <?php self::e((($this->spacing_horizontal * 2) + 10) . $uom); ?>;
	padding-inline-end: <?php self::e((($this->spacing_horizontal * 2) + 10) . $uom); ?>;
}

select.wsf-field ~ .select2-container--default .select2-selection--multiple .select2-selection__clear {
	margin-right: 0;
	margin-top: -1px;
}

<?php if ($this->hover) { ?>
<?php if ($this->hover_color != $this->color) { ?>
select.wsf-field ~ .select2-container .select2-selection--single:hover {
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->hover_color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
}
<?php } ?>
<?php } ?>

<?php if ($this->focus) { ?>
<?php if ($this->focus_color != $this->color) { ?>
select.wsf-field ~ .select2-container .select2-selection--single:focus {
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->focus_color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
}
<?php } ?>
<?php } ?>

select.wsf-field ~ .select2-container--default.select2-container--disabled .select2-selection--single,
select.wsf-field ~ .select2-container--default.select2-container--disabled .select2-selection--multiple {
<?php if ($this->disabled_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->disabled_background_color); ?>;
<?php } ?>
<?php if ($this->disabled_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
<?php } ?>
	cursor: not-allowed;
}

select.wsf-field ~ .select2-container--default.select2-container--disabled .select2-selection--single {
<?php if ($this->disabled_color != $this->color) { ?>
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->disabled_color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
<?php } ?>
}

select.wsf-field ~ .select2-container .select2-selection--multiple {
	padding-bottom: 0;
}

select.wsf-field ~ .select2-container .select2-search--inline {
	margin: 0;
}

select.wsf-field ~ .select2-container .select2-search--inline .select2-search__field {
	color: <?php self::e($this->color); ?>;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	height: auto;
	line-height: <?php self::e($this->line_height); ?>;
	margin-top: 0;
}

select.wsf-field ~ .select2-container .select2-search--inline .select2-search__field::placeholder {
	color: <?php self::e($this->placeholder_color); ?>;
}

select.wsf-field ~ .select2-container--default .select2-selection--multiple .select2-selection__choice {
	background-color: <?php self::e($this->color_default_lightest); ?>;
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
	font-size: <?php self::e($this->font_size_small . $uom); ?>;
	margin: 0 0 <?php self::e($this->spacing_small . $uom); ?>;
	-webkit-margin-end: <?php self::e($this->spacing_small . $uom); ?>;
	margin-inline-end: <?php self::e($this->spacing_small . $uom); ?>;
}

select.wsf-field ~ .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
	color: <?php self::e($this->color_default_light); ?>;
}

select.wsf-field ~ .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
	color: <?php self::e($this->color); ?>;
}

select.wsf-field ~ .select2-container--default.select2-container--focus:not(.select2-container--disabled) .select2-selection--single,
select.wsf-field ~ .select2-container--default.select2-container--focus:not(.select2-container--disabled) .select2-selection--multiple {
<?php if ($this->focus) { ?>
<?php if ($this->focus_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->focus_background_color); ?>;
<?php } ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
<?php if ($this->focus_color != $this->color) { ?>
	color: <?php self::e($this->focus_color); ?>;
<?php } ?>
<?php } ?>
}

select.wsf-field ~ .select2-container .select2-selection--single .select2-selection__rendered,
select.wsf-field ~ .select2-container .select2-selection--multiple .select2-selection__rendered {
	color: <?php self::e($this->color); ?>;
	line-height: <?php self::e($this->line_height); ?> !important;
	padding-left: 0;
	padding-right: 0;
}

select.wsf-field ~ .select2-container .select2-selection--single .select2-selection__rendered .select2-search--inline,
select.wsf-field ~ .select2-container .select2-selection--multiple .select2-selection__rendered .select2-search--inline{
	margin-bottom: 0;
}

select.wsf-field ~ .select2-container .select2-selection--single .select2-selection__placeholder,
select.wsf-field ~ .select2-container .select2-selection--multiple .select2-selection__placeholder {
	color: <?php self::e($this->color); ?>;
}

select.wsf-field ~ .select2-container .select2-selection--single .select2-selection__arrow,
select.wsf-field ~ .select2-container .select2-selection--multiple .select2-selection__arrow {
	display: none;
}

.wsf-select2-dropdown {
	background-color: <?php self::e($this->background_color); ?>;
	z-index: 10000;	/* Elementor Pop-up CSS Fix */
}

.select2-container--open .wsf-select2-dropdown.select2-dropdown--above {
<?php if ($this->border) { ?>
	border-color: <?php self::e($this->border_color); ?>;
<?php } ?>
	border-top-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-top-right-radius: <?php self::e($this->border_radius . $uom); ?>;
	box-shadow: none;
	overflow-x: hidden;
}

.select2-container--open .wsf-select2-dropdown.select2-dropdown--below {
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
	border-top: none;
<?php } else { ?>
	border: none;
<?php } ?>
	border-bottom-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-bottom-right-radius: <?php self::e($this->border_radius . $uom); ?>;
	box-shadow: none;
	overflow-x: hidden;
}

.wsf-select2-dropdown .select2-search--dropdown {
	margin-bottom: <?php self::e($this->spacing_small . $uom); ?>;
	padding: <?php self::e($this->spacing_small . $uom . ' ' . $this->spacing_small . $uom); ?> 0;
}

.wsf-select2-dropdown .select2-search__field {
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin: 0;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, background-image <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

.wsf-select2-dropdown .select2-search--dropdown .select2-search__field {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
	color: <?php self::e($this->color); ?>;
}

<?php if ($this->hover) { ?>
.wsf-select2-dropdown .select2-search--dropdown .select2-search__field:hover:enabled {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>;
<?php } ?>
<?php if ($this->hover_color != $this->color) { ?>
	color: <?php self::e($this->hover_color); ?>;
<?php } ?>
}
<?php } ?>

.wsf-select2-dropdown .select2-search--dropdown .select2-search__field:focus {
<?php if ($this->focus) { ?>
<?php if ($this->focus_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->focus_background_color); ?>;
<?php } ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
<?php if ($this->focus_color != $this->color) { ?>
	color: <?php self::e($this->focus_color); ?>;
<?php } ?>
<?php } ?>
	outline: 0;
}

.wsf-select2-dropdown .select2-results {
	line-height: <?php self::e($this->line_height); ?>;
}

.wsf-select2-dropdown .select2-results .select2-results__option {
<?php if ($this->border) { ?>
	border-top: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border-top: none;
<?php } ?>
	color: <?php self::e($this->color); ?>;
	font-size: <?php self::e($this->font_size_small . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	margin: 0;
	padding: <?php self::e($this->spacing_small . $uom); ?>;
}

.select2-results .select2-results__option[role=group] {
	padding: 0;
}

.select2-results .select2-results__option[aria-disabled=true] {
	color: <?php self::e($this->disabled_color); ?>;
	cursor: not-allowed;
}

.wsf-select2-dropdown .select2-results .select2-results__option[aria-selected=true] {
	background-color: <?php self::e($this->color_default_lightest); ?>;
}

.wsf-select2-dropdown .select2-results .select2-results__option--highlighted[aria-selected] {
	background-color: <?php self::e($this->color_primary); ?>;
	color: <?php self::e($this->background_color); ?>;
}

/* Checkbox */
input[type=checkbox].wsf-field {
	background: none;
	border: none;
	bottom: auto;
	height: <?php self::e($checkbox_size . $uom); ?>;
	left: auto;
	margin: 0;
	opacity: 0;
	position: absolute;
	right: auto;
	top: auto;
	width: <?php self::e($checkbox_size . $uom); ?>;
}

input[type=checkbox].wsf-field + label.wsf-label {
	color: <?php self::e($this->color_default); ?>;
	display: inline-block;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin: 0 0 <?php self::e($this->spacing . $uom); ?>;
	-webkit-padding-start: <?php self::e(($checkbox_size + $this->spacing_small) . $uom); ?>;
	padding-inline-start: <?php self::e(($checkbox_size + $this->spacing_small) . $uom); ?>;
	position: relative;
<?php if ($this->transition) { ?>
	transition: color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

input[type=checkbox].wsf-field + label.wsf-label::before {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	content: '';
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($checkbox_size . $uom); ?>;
	left: 0;
	position: absolute;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	vertical-align: top;
	width: <?php self::e($checkbox_size . $uom); ?>;
}

input[type=checkbox].wsf-field + label.wsf-label::after {
	content: '';
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($checkbox_size . $uom); ?>;
	left: 0;
	position: absolute;
	top: 0;
	vertical-align: top;
	width: <?php self::e($checkbox_size . $uom); ?>;
}

input[type=checkbox].wsf-field + label.wsf-label + .wsf-invalid-feedback {
	margin-bottom: <?php self::e($this->spacing . $uom); ?>;
	margin-top: -<?php self::e($this->spacing_small . $uom); ?>;
}

<?php if ($this->hover) { ?>
input[type=checkbox].wsf-field:enabled:hover + label.wsf-label::before {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>
<?php } ?>
}
<?php } ?>

<?php if ($this->focus) { ?>
input[type=checkbox].wsf-field:focus + label.wsf-label::before {
<?php if ($this->focus_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->focus_background_color); ?>;
<?php } ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field:disabled + label.wsf-label {
<?php if ($this->disabled_color != $this->color) { ?>
	color: <?php self::e($this->disabled_color); ?>;
<?php } ?>
}

input[type=checkbox].wsf-field:disabled + label.wsf-label::before {
<?php if ($this->disabled_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->disabled_background_color); ?>;
<?php } ?>
<?php if ($this->border) { ?>
<?php if ($this->disabled_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
<?php } ?>
<?php } ?>
	cursor: not-allowed;
}

input[type=checkbox].wsf-field:disabled + label.wsf-label::after {
	cursor: not-allowed;
}

input[type=checkbox].wsf-field:checked + label.wsf-label::before {
	background-color: <?php self::e($this->checked_color); ?>;
	border-color: <?php self::e($this->checked_color); ?>;
}

input[type=checkbox].wsf-field:checked + label.wsf-label::after {
	background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='<?php self::c($this->background_color); ?>' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e");
	background-position: 50%;
	background-size: 50%;
	background-repeat: no-repeat;
}

input[type=checkbox].wsf-field:checked:disabled + label.wsf-label::before {
	opacity: .5;
}

/* Radio */
input[type=radio].wsf-field {
	background: none;
	border: none;
	bottom: auto;
	height: <?php self::e($radio_size . $uom); ?>;
	left: auto;
	margin: 0;
	opacity: 0;
	position: absolute;
	right: auto;
	top: auto;
	width: <?php self::e($radio_size . $uom); ?>;
}

input[type=radio].wsf-field + label.wsf-label {
	color: <?php self::e($this->color_default); ?>;
	display: inline-block;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin: 0 0 <?php self::e($this->spacing . $uom); ?>;
	-webkit-padding-start: <?php self::e(($radio_size + $this->spacing_small) . $uom); ?>;
	padding-inline-start: <?php self::e(($radio_size + $this->spacing_small) . $uom); ?>;
	position: relative;
<?php if ($this->transition) { ?>
	transition: color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

input[type=radio].wsf-field + label.wsf-label::before {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
	border-radius: 50%;
	content: '';
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($radio_size . $uom); ?>;
	left: 0;
	position: absolute;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	vertical-align: top;
	width: <?php self::e($radio_size . $uom); ?>;
}

input[type=radio].wsf-field + label.wsf-label::after {
	content: '';
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($checkbox_size . $uom); ?>;
	left: 0;
	position: absolute;
	top: 0;
	vertical-align: top;
	width: <?php self::e($checkbox_size . $uom); ?>;
}

input[type=radio].wsf-field + label.wsf-label + .wsf-invalid-feedback {
	margin-bottom: <?php self::e($this->spacing . $uom); ?>;
	margin-top: -<?php self::e($this->spacing_small . $uom); ?>;
}

<?php if ($this->hover) { ?>
input[type=radio].wsf-field:enabled:hover + label.wsf-label::before {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>
<?php } ?>
}
<?php } ?>

<?php if ($this->focus) { ?>
input[type=radio].wsf-field:focus + label.wsf-label::before {
<?php if ($this->focus_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->focus_background_color); ?>;
<?php } ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

input[type=radio].wsf-field:disabled + label.wsf-label {
<?php if ($this->disabled_color != $this->color) { ?>
	color: <?php self::e($this->disabled_color); ?>;
<?php } ?>
}

input[type=radio].wsf-field:disabled + label.wsf-label::before {
<?php if ($this->disabled_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->disabled_background_color); ?>;
<?php } ?>
<?php if ($this->border) { ?>
<?php if ($this->disabled_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
<?php } ?>
<?php } ?>
	cursor: not-allowed;
}

input[type=radio].wsf-field:disabled + label.wsf-label::after {
	cursor: not-allowed;
}

input[type=radio].wsf-field:checked + label.wsf-label::before {
	background-color: <?php self::e($this->checked_color); ?>;
	border-color: <?php self::e($this->checked_color); ?>;
}

input[type=radio].wsf-field:checked + label.wsf-label::after {
	background-image: url('data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="-4 -4 8 8"%3e%3ccircle r="2" fill="<?php self::c($this->background_color); ?>"/%3e%3c/svg%3e');
	background-position: 50%;
	background-size: contain;
	background-repeat: no-repeat;
}

input[type=radio].wsf-field:checked:disabled + label.wsf-label::before {
	opacity: .5;
}

input[type=checkbox].wsf-field.wsf-switch,
input[type=radio].wsf-field.wsf-switch {
	width: <?php self::e((($checkbox_size * 2) - ($this->border_width * 4)) . $uom); ?>;
}

input[type=checkbox].wsf-field.wsf-switch + label.wsf-label,
input[type=radio].wsf-field.wsf-switch + label.wsf-label {
	-webkit-padding-start: <?php self::e((($checkbox_size * 2) - ($this->border_width * 4)  + $this->spacing_small) . $uom); ?>;
	padding-inline-start: <?php self::e((($checkbox_size * 2) - ($this->border_width * 4)  + $this->spacing_small) . $uom); ?>;
	position: relative;
}

input[type=checkbox].wsf-field.wsf-switch + label.wsf-label::before,
input[type=radio].wsf-field.wsf-switch + label.wsf-label::before {
	border-radius: <?php self::e(($checkbox_size / 2) + ($this->border_width * 2) . $uom); ?>;
	position: absolute;
	width: <?php self::e((($checkbox_size * 2) - ($this->border_width * 4)) . $uom); ?>;
}

input[type=checkbox].wsf-field.wsf-switch + label.wsf-label::after,
input[type=radio].wsf-field.wsf-switch + label.wsf-label::after {
	background-color: <?php self::e($this->border_color); ?>;
	border-radius: 50%;
	height: <?php self::e(($checkbox_size - ($this->border_width * 4)) . $uom); ?>;
	left: <?php self::e(($this->border_width * 2). $uom); ?>;
	top: <?php self::e(($this->border_width * 2). $uom); ?>;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, left <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	width: <?php self::e(($checkbox_size - ($this->border_width * 4)) . $uom); ?>;
}

<?php if ($this->hover) { ?>
input[type=checkbox].wsf-field.wsf-switch:enabled:hover + label.wsf-label::after,
input[type=radio].wsf-field.wsf-switch:enabled:hover + label.wsf-label::after {
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>
<?php } ?>
}
<?php } ?>

<?php if ($this->focus) { ?>
input[type=checkbox].wsf-field.wsf-switch:focus + label.wsf-label::after,
input[type=radio].wsf-field.wsf-switch:focus + label.wsf-label::after {
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field.wsf-switch:disabled + label.wsf-label::after,
input[type=radio].wsf-field.wsf-switch:disabled + label.wsf-label::after {
<?php if ($this->border) { ?>
<?php if ($this->disabled_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
<?php } ?>
<?php } ?>
}

input[type=checkbox].wsf-field.wsf-switch:checked + label.wsf-label::before,
input[type=radio].wsf-field.wsf-switch:checked + label.wsf-label::before {
	background-color: <?php self::e($this->checked_color); ?>;
}

input[type=checkbox].wsf-field.wsf-switch:checked + label.wsf-label::after,
input[type=radio].wsf-field.wsf-switch:checked + label.wsf-label::after {
	background-color: <?php self::e($this->background_color); ?>;
	background-image: none;
	border-color: <?php self::e($this->background_color); ?>;
	left: <?php self::e(($checkbox_size - ($this->border_width * 2)) . $uom); ?>
}

input[type=checkbox].wsf-field.wsf-button + label.wsf-label,
input[type=radio].wsf-field.wsf-button + label.wsf-label {
  	background-color: <?php self::e($this->color_default_lighter); ?>;
<?php if ($this->border) { ?>
  	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
  	border: none;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
  	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
  	color: <?php self::e($this->color); ?>;
  	cursor: pointer;
  	display: inline-block;
  	font-family: <?php self::e($this->font_family); ?>;
  	font-size: <?php self::e($this->font_size . $uom); ?>;
  	font-weight: <?php self::e($this->font_weight); ?>;
  	line-height: <?php self::e($this->line_height); ?>;
  	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
  	margin: 0 0 <?php self::e(($this->grid_gutter / 2) . $uom); ?>;
  	text-align: center;
  	text-decoration: none;
  	touch-action: manipulation;
<?php if ($this->transition) { ?>
  	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
  	vertical-align: middle;
}

input[type=checkbox].wsf-field.wsf-button + label.wsf-label::after,
input[type=radio].wsf-field.wsf-button + label.wsf-label::after {
	display: none;
}

input[type=checkbox].wsf-field.wsf-button.wsf-button-full + label.wsf-label,
input[type=radio].wsf-field.wsf-button.wsf-button-full + label.wsf-label {
	display: block;
}

input[type=checkbox].wsf-field.wsf-button + label.wsf-label::before,
input[type=radio].wsf-field.wsf-button + label.wsf-label::before {
	display: none;
}

<?php if ($this->focus) { ?>
input[type=checkbox].wsf-field.wsf-button:focus + label.wsf-label,
input[type=radio].wsf-field.wsf-button:focus + label.wsf-label {
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->border_color, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field.wsf-button:disabled + label.wsf-label,
input[type=radio].wsf-field.wsf-button:disabled + label.wsf-label {
	cursor: not-allowed;
	opacity: .5;
}

input[type=checkbox].wsf-field.wsf-button:checked + label.wsf-label,
input[type=radio].wsf-field.wsf-button:checked + label.wsf-label {
	background-color: <?php self::e($this->color_primary); ?>;
	border-color: <?php self::e($this->color_primary); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}

<?php if ($this->focus) { ?>
input[type=checkbox].wsf-field.wsf-button:checked:focus + label.wsf-label,
input[type=radio].wsf-field.wsf-button:checked:focus + label.wsf-label {
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field.wsf-color,
input[type=radio].wsf-field.wsf-color {
	height: <?php self::e($color_size . $uom); ?>;
	width: <?php self::e($color_size . $uom); ?>;
}

input[type=checkbox].wsf-field.wsf-color + label.wsf-label,
input[type=radio].wsf-field.wsf-color + label.wsf-label {
	margin-left: 0;
	padding-left: 0;
	position: relative;
}

input[type=checkbox].wsf-field.wsf-color + label.wsf-label::before,
input[type=radio].wsf-field.wsf-color + label.wsf-label::before {
	display: none;
}

input[type=checkbox].wsf-field.wsf-color + label.wsf-label::after,
input[type=radio].wsf-field.wsf-color + label.wsf-label::after {
	display: none;
}

input[type=checkbox].wsf-field.wsf-color + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color + label.wsf-label > span {
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($color_size . $uom); ?>;
<?php if ($this->transition) { ?>
	transition: border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	vertical-align: middle;
	width: <?php self::e($color_size . $uom); ?>;
}

input[type=checkbox].wsf-field.wsf-color.wsf-circle + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color.wsf-circle + label.wsf-label > span {
	border-radius: 50%;
}

<?php if ($this->hover) { ?>
input[type=checkbox].wsf-field.wsf-color:enabled:hover + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color:enabled:hover + label.wsf-label > span {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>
<?php } ?>
}
<?php } ?>

<?php if ($this->focus) { ?>
input[type=checkbox].wsf-field.wsf-color:focus + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color:focus + label.wsf-label > span {
<?php if ($this->focus_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->focus_background_color); ?>;
<?php } ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field.wsf-color:disabled + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color:disabled + label.wsf-label > span {
	cursor: not-allowed;
	opacity: .5;
}

input[type=checkbox].wsf-field.wsf-color:checked + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color:checked + label.wsf-label > span {
	border-color: <?php self::e($this->checked_color); ?>;
	box-shadow: inset 0 0 0 2px <?php self::e($this->color_default_inverted); ?>;
}

input[type=checkbox].wsf-field.wsf-image + label.wsf-label,
input[type=radio].wsf-field.wsf-image + label.wsf-label {
	margin-left: 0;
	padding-left: 0;
	position: relative;
}

input[type=checkbox].wsf-field.wsf-image + label.wsf-label::before,
input[type=radio].wsf-field.wsf-image + label.wsf-label::before {
	display: none;
}

input[type=checkbox].wsf-field.wsf-image + label.wsf-label::after,
input[type=radio].wsf-field.wsf-image + label.wsf-label::after {
	display: none;
}

input[type=checkbox].wsf-field.wsf-image + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image + label.wsf-label > img {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	cursor: pointer;
	display: inline-block;
	height: auto;
	max-width: 100%;
	padding: 2px;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	vertical-align: middle;
}

input[type=checkbox].wsf-field.wsf-image.wsf-circle + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image.wsf-circle + label.wsf-label > img {
	border-radius: 50%;
}

input[type=checkbox].wsf-field.wsf-image.wsf-responsive + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image.wsf-responsive + label.wsf-label > img {
	height: auto;
	max-width: 100%;
	width: 100%; 
}

input[type=checkbox].wsf-field.wsf-image.wsf-image-full + label.wsf-label,
input[type=radio].wsf-field.wsf-image.wsf-image-full + label.wsf-label {
	width: 100%;
}

<?php if ($this->hover) { ?>
input[type=checkbox].wsf-field.wsf-image:enabled:hover + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image:enabled:hover + label.wsf-label > img {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>
<?php } ?>
}
<?php } ?>

<?php if ($this->focus) { ?>
input[type=checkbox].wsf-field.wsf-image:focus + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image:focus + label.wsf-label > img {
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field.wsf-image:disabled + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image:disabled + label.wsf-label > img {
	cursor: not-allowed;
	opacity: .5;
}

input[type=checkbox].wsf-field.wsf-image:checked + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image:checked + label.wsf-label > img {
	background-color: <?php self::e($this->checked_color); ?>;
	border-color: <?php self::e($this->checked_color); ?>;
}

.wsf-image-caption {
	color: <?php self::e($this->help_color); ?>;
	display: block;
	font-size: <?php self::e($this->font_size_small . $uom); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin-top: <?php self::e($this->spacing_small . $uom); ?>;
}

[data-wsf-hierarchy='1'] {
	-webkit-margin-start: <?php self::e($checkbox_size . $uom); ?>;
	margin-inline-start: <?php self::e($checkbox_size . $uom); ?>;
}

[data-wsf-hierarchy='2'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 2) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 2) . $uom); ?>;
}

[data-wsf-hierarchy='3'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 3) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 3) . $uom); ?>;
}

[data-wsf-hierarchy='4'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 4) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 4) . $uom); ?>;
}

[data-wsf-hierarchy='5'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 5) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 5) . $uom); ?>;
}

[data-wsf-hierarchy='6'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 6) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 6) . $uom); ?>;
}

[data-wsf-hierarchy='7'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 7) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 7) . $uom); ?>;
}

[data-wsf-hierarchy='8'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 8) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 8) . $uom); ?>;
}

[data-wsf-hierarchy='9'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 9) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 9) . $uom); ?>;
}

[data-wsf-hierarchy='10'] {
	-webkit-margin-start: <?php self::e(($checkbox_size * 10) . $uom); ?>;
	margin-inline-start: <?php self::e(($checkbox_size * 10) . $uom); ?>;
}

/* Date/Time Picker */
<?php if(WS_Form_Common::option_get('ui_datepicker', 'on') !== 'off') { ?>

body .xdsoft_datetimepicker {
	background: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
	padding: <?php self::e($this->spacing . $uom . ' 0 ' . $this->spacing . $uom . ' ' . $this->spacing . $uom); ?>;
	/* z-index added to ensure date/time picker appears in Bricks pop-ups which have a z-index of 10000 */
	z-index: 999999;
}

body .xdsoft_datetimepicker.xdsoft_inline {
	display: flex;
}

body .xdsoft_datetimepicker.xdsoft_inline .xdsoft_datepicker {
	float: none;
    width: 100%;
}

body .xdsoft_datetimepicker.xdsoft_inline .xdsoft_timepicker {
	float: none;
}

body .xdsoft_datetimepicker.xdsoft_inline .xdsoft_datepicker.active + .xdsoft_timepicker {
	min-width: 70px;
	width: 25%;
}

body .xdsoft_datetimepicker.xdsoft_inline .xdsoft_datepicker:not(.active) + .xdsoft_timepicker {
	width: 100%;
}

body .xdsoft_datetimepicker .xdsoft_datepicker,
body .xdsoft_datetimepicker .xdsoft_timepicker {
	margin-left: 0;
	margin-right: <?php self::e($this->spacing . $uom); ?>;
}

body .xdsoft_datetimepicker .xdsoft_timepicker {
	width: 70px;
}

body .xdsoft_datetimepicker .xdsoft_monthpicker .xdsoft_prev {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='5px' height='10px' viewBox='0 0 5 10'%3E%3Cpolygon fill='<?php self::c($this->color); ?>' points='0 5 5 10 5 0'%3E%3C/polygon%3E%3C/svg%3E");
	background-position: center;
	background-repeat: no-repeat;
	background-size: 8px 15px;
}

body .xdsoft_datetimepicker .xdsoft_monthpicker .xdsoft_next {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='5px' height='10px' viewBox='0 0 5 10'%3E%3Cpolygon fill='<?php self::c($this->color); ?>' points='0 10 5 5 0 0'%3E%3C/polygon%3E%3C/svg%3E");
	background-position: center;
	background-repeat: no-repeat;
	background-size: 8px 15px;
}

body .xdsoft_datetimepicker .xdsoft_today_button {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16px' height='16px' viewBox='0 0 16 16'%3E%3Cpath fill='<?php self::c($this->color); ?>' d='M8 1.4l-2 1.3v-1.7h-2v3l-4 2.6 0.6 0.8 7.4-4.8 7.4 4.8 0.6-0.8z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color); ?>' d='M8 4l-6 4v7h5v-3h2v3h5v-7z'%3E%3C/path%3E%3C/svg%3E");
	background-position: center;
	background-repeat: no-repeat;
	background-size: 15px;
}

body .xdsoft_datetimepicker .xdsoft_timepicker .xdsoft_prev {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10px' height='5px' viewBox='0 0 10 5'%3E%3Cpolyline fill='<?php self::c($this->color); ?>' points='0 5 10 5 5 0'%3E%3C/polyline%3E%3C/svg%3E");
	background-position: center;
	background-repeat: no-repeat;
	background-size: 15px 8px;
	margin-left: auto;
    margin-right: auto;
}

body .xdsoft_datetimepicker .xdsoft_timepicker .xdsoft_next {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10px' height='5px' viewBox='0 0 10 5'%3E%3Cpolyline fill='<?php self::c($this->color); ?>' points='0 0 10 0 5 5'%3E%3C/polyline%3E%3C/svg%3E");
	background-position: center;
	background-repeat: no-repeat;
	background-size: 15px 8px;
	margin-left: auto;
    margin-right: auto;
}

body .xdsoft_datetimepicker .xdsoft_label {
	background-color: <?php self::e($this->background_color); ?>;
	color: <?php self::e($this->color); ?>;
	font-size: 14px;
}

body .xdsoft_datetimepicker .xdsoft_label > .xdsoft_select {
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
}

body .xdsoft_datetimepicker .xdsoft_label > .xdsoft_select > div > .xdsoft_option {
	background-color: <?php self::e($this->background_color); ?>;
}

body .xdsoft_datetimepicker .xdsoft_calendar td,
body .xdsoft_datetimepicker .xdsoft_calendar th {
	background-color: <?php self::e($this->color_default_lightest); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
	color: <?php self::e($this->color); ?>;
	font-size: 12px;
}

body .xdsoft_datetimepicker .xdsoft_timepicker .xdsoft_time_box {
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
}

body .xdsoft_datetimepicker .xdsoft_timepicker .xdsoft_time_box > div > div {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border-top: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border-top: none;
<?php } ?>
	color: <?php self::e($this->color); ?>;
}

body .xdsoft_datetimepicker .xdsoft_label > .xdsoft_select > div > .xdsoft_option.xdsoft_current {
	background: <?php self::e($this->color_primary); ?> !important;
	box-shadow: none;
	color: <?php self::e($this->color_default_inverted); ?> !important;
}

body .xdsoft_datetimepicker .xdsoft_label > .xdsoft_select > div > .xdsoft_option:hover {
	background: <?php self::e($this->color_primary); ?> !important;
	color: <?php self::e($this->color_default_inverted); ?> !important;
}

body .xdsoft_datetimepicker .xdsoft_calendar td.xdsoft_today {
    color: <?php self::e($this->color_primary); ?> !important;
    font-weight: bold;
}

body .xdsoft_datetimepicker .xdsoft_calendar td.xdsoft_current,
body .xdsoft_datetimepicker .xdsoft_timepicker .xdsoft_time_box>div>div.xdsoft_current {
	background: <?php self::e($this->color_primary); ?> !important;
	box-shadow: none;
	color: <?php self::e($this->color_default_inverted); ?> !important;
}

body .xdsoft_datetimepicker .xdsoft_calendar td:hover,
body .xdsoft_datetimepicker .xdsoft_timepicker .xdsoft_time_box>div>div:hover {
	background: <?php self::e($this->color_primary); ?> !important;
	color: <?php self::e($this->color_default_inverted); ?> !important;
}

body .xdsoft_datetimepicker .xdsoft_calendar td.xdsoft_disabled,
body .xdsoft_datetimepicker .xdsoft_calendar td.xdsoft_other_month,
body .xdsoft_datetimepicker .xdsoft_time_box>div>div.xdsoft_disabled {
	opacity: .25;
}
<?php } ?>

/* Range Slider */
input[type=range].wsf-field {
	-webkit-appearance: none;
	background: none;
	background-size: calc(100% - <?php self::e($range_size . $uom); ?>);
	background-position: <?php self::e(($range_size / 2) . $uom); ?> center;
	border: none;
	margin: 0;
	padding: 0;
	position: relative;
	width: 100%;
	z-index: 1;
	--wsf-color-lower-track: <?php self::e($this->color_primary); ?>;
	--wsf-color-upper-track: <?php self::e($this->color_default_lightest); ?>;
	--wsf-fill-lower-track: 0%;
}

input[type=range].wsf-field[data-fill-lower-track]::-webkit-slider-runnable-track {
	background-image: linear-gradient(to right, 
		var(--wsf-color-lower-track),
		var(--wsf-color-lower-track) var(--wsf-fill-lower-track),
		var(--wsf-color-upper-track) var(--wsf-fill-lower-track),
		var(--wsf-color-upper-track) 100%
	);
}
input[type=range].wsf-field[data-fill-lower-track]::-moz-range-track {
	background-image: linear-gradient(to right, 
		var(--wsf-color-lower-track),
		var(--wsf-color-lower-track) var(--wsf-fill-lower-track),
		var(--wsf-color-upper-track) var(--wsf-fill-lower-track),
		var(--wsf-color-upper-track) 100%
	);
}

input[type=range].wsf-field::-webkit-slider-runnable-track {
	background-color: <?php self::e($this->color_default_lightest); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	cursor: pointer;
	height: <?php self::e(($range_size / 2) . $uom); ?>;
}

input[type=range].wsf-field::-moz-range-track {
	background-color: <?php self::e($this->color_default_lightest); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	cursor: pointer;
	height: <?php self::e(($range_size / 2) . $uom); ?>;
}

input[type=range].wsf-field::-ms-track {
	background-color: <?php self::e($this->color_default_lightest); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	cursor: pointer;
	height: <?php self::e(($range_size / 2) . $uom); ?>;
	background-color: transparent;
	border-color: transparent;
	border-color: transparent;
	color: transparent;
}

input[type=range].wsf-field::-ms-fill-lower,
input[type=range].wsf-field::-ms-fill-upper {
	background-color: <?php self::e($this->color_default_lightest); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	cursor: pointer;
	height: <?php self::e(($range_size / 2) . $uom); ?>;
}

input[type=range].wsf-field[data-fill-lower-track]::-ms-fill-lower {
	background-color: <?php self::e($this->color_primary); ?>;
}

input[type=range].wsf-field::-webkit-slider-thumb {
	-webkit-appearance: none;
	background-color: <?php self::e($this->color_primary); ?>;
	border-radius: 50%;
	cursor: pointer;
	height: <?php self::e($range_size . $uom); ?>;
	margin-top: -<?php self::e(($range_size / 4) . $uom); ?>;
<?php if ($this->transition) { ?>
	transition: box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	width: <?php self::e($range_size . $uom); ?>;
}

input[type=range].wsf-field::-moz-range-thumb {
	-webkit-appearance: none;
	background-color: <?php self::e($this->color_primary); ?>;
	border: none;
	border-radius: 50%;
	cursor: pointer;
	height: <?php self::e($range_size . $uom); ?>;
	margin-top: -<?php self::e(($range_size / 4) . $uom); ?>;
<?php if ($this->transition) { ?>
	transition: box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	width: <?php self::e($range_size . $uom); ?>;
}

input[type=range].wsf-field::-ms-thumb {
	-webkit-appearance: none;
	background-color: <?php self::e($this->color_primary); ?>;
	border: none;
	border-radius: 50%;
	cursor: pointer;
	height: <?php self::e($range_size . $uom); ?>;
	margin-top: -<?php self::e(($range_size / 4) . $uom); ?>;
<?php if ($this->transition) { ?>
	transition: box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	width: <?php self::e($range_size . $uom); ?>;
}

input[type=range].wsf-field:focus {
	outline: none;
}

<?php if ($this->focus) { ?>
<?php if ($this->box_shadow) { ?>
input[type=range].wsf-field:focus::-webkit-slider-thumb {
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
}
<?php } ?>
<?php } ?>

<?php if ($this->focus) { ?>
<?php if ($this->box_shadow) { ?>
input[type=range].wsf-field:focus::-moz-range-thumb {
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
}
<?php } ?>
<?php } ?>

<?php if ($this->focus) { ?>
<?php if ($this->box_shadow) { ?>
input[type=range].wsf-field:focus::-ms-thumb{
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
}
<?php } ?>
<?php } ?>

input[type=range].wsf-field::-moz-focus-outer {
	border: 0;
}

input[type=range].wsf-field:disabled {
	opacity: .5;
}

input[type=range].wsf-field:disabled::-webkit-slider-runnable-track {
	cursor: not-allowed;
}

input[type=range].wsf-field:disabled::-moz-range-track {
	cursor: not-allowed;
}

input[type=range].wsf-field:disabled::-ms-fill-lower,
input[type=range].wsf-field:disabled::-ms-fill-upper {
	cursor: not-allowed;
}

input[type=range].wsf-field:disabled::-webkit-slider-thumb {
	cursor: not-allowed;
}

input[type=range].wsf-field:disabled::-moz-range-thumb {
	cursor: not-allowed;
}

input[type=range].wsf-field:disabled::-ms-thumb {
	cursor: not-allowed;
}

input[type=range].wsf-field.wsf-hue::-webkit-slider-runnable-track {
	background-image: linear-gradient(to right, #ff0000 0%, #ffff00 17%, #00ff00 33%, #00ffff 50%, #0000ff 67%, #ff00ff 83%, #ff0000 100%);
}

input[type=range].wsf-field.wsf-hue::-moz-range-track {
	background-image: linear-gradient(to right, #ff0000 0%, #ffff00 17%, #00ff00 33%, #00ffff 50%, #0000ff 67%, #ff00ff 83%, #ff0000 100%);
}

input[type=range].wsf-field.wsf-hue::-ms-track {
	background-image: linear-gradient(to right, #ff0000 0%, #ffff00 17%, #00ff00 33%, #00ffff 50%, #0000ff 67%, #ff00ff 83%, #ff0000 100%);
}

.wsf-field-wrapper[data-type='range'] input[type=range].wsf-field + datalist,
.wsf-field-wrapper[data-type='price_range'] input[type=range].wsf-field + datalist {
	display: block;
	position: relative;
	margin: 0 <?php self::e(($range_size / 2) . $uom); ?> 0;
	top: -<?php self::e(($range_size + $this->spacing_small) . $uom); ?>;
	width: calc(100% - <?php self::e($range_size . $uom); ?>);
	z-index: 0;
}

.wsf-field-wrapper[data-type='range'] input[type=range].wsf-field + datalist option,
.wsf-field-wrapper[data-type='price_range'] input[type=range].wsf-field + datalist option {
	color: <?php self::e($this->help_color); ?>;
	display: block;
	font-size: <?php self::e($this->font_size_small . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	left: var(--wsf-position-tick-mark);
	padding: 0;
	position: absolute;
	top: 0;
}

.wsf-field-wrapper[data-type='range'] input[type=range].wsf-field + datalist option:before,
.wsf-field-wrapper[data-type='price_range'] input[type=range].wsf-field + datalist option:before {
	background-color: <?php self::e($this->color_default_lighter); ?>;
	content: '';
	display: block;
	height: <?php self::e($range_size . $uom); ?>;
	margin-left: -<?php self::e($this->border_width . $uom); ?>;
	width: <?php self::e($this->border_width . $uom); ?>;
}

.wsf-field-wrapper[data-type='range'] input[type=range].wsf-field + datalist option:after,
.wsf-field-wrapper[data-type='price_range'] input[type=range].wsf-field + datalist option:after {
	content: attr(data-label);
	position: absolute;
	left: 50%;
	transform: translateX(-50%);
}

@-moz-document url-prefix() { 
	.wsf-field-wrapper[data-type='range'] input[type=range].wsf-field + datalist,
	.wsf-field-wrapper[data-type='price_range'] input[type=range].wsf-field + datalist {
		display: none;
	}
}

.wsf-field-wrapper[data-type='range'].wsf-range-vertical,
.wsf-field-wrapper[data-type='price_range'].wsf-range-vertical {
	--wsf-range-vertical-height: 200px;
}

.wsf-field-wrapper[data-type='range'].wsf-range-vertical input[type=range].wsf-field,
.wsf-field-wrapper[data-type='price_range'].wsf-range-vertical input[type=price_range].wsf-field {
    left: calc((-1 * var(--wsf-range-vertical-height)) + <?php self::e(($range_size / 2) . $uom); ?>);
    position: relative;
    transform: rotate(270deg);
    transform-origin: center right;
    width: var(--wsf-range-vertical-height);
    z-index: 1;
}

.wsf-field-wrapper[data-type='range'].wsf-range-vertical input[type=range].wsf-field + datalist,
.wsf-field-wrapper[data-type='price_range'].wsf-range-vertical input[type=range].wsf-field + datalist {
	left: calc((-1 * var(--wsf-range-vertical-height)) + <?php self::e(($range_size / 2) . $uom); ?>);
	top: -<?php self::e($this->spacing_small . $uom); ?>;
    transform: rotate(270deg);
    transform-origin: center right;
    width: calc(var(--wsf-range-vertical-height) - <?php self::e($range_size . $uom); ?>);
}

.wsf-field-wrapper[data-type='range'].wsf-range-vertical input[type=range].wsf-field + datalist option:after,
.wsf-field-wrapper[data-type='price_range'].wsf-range-vertical input[type=range].wsf-field + datalist option:after {
	top: 15px;
    transform: rotate(90deg);
    transform-origin: left center;
}
	
.wsf-field-wrapper[data-type='range'].wsf-range-vertical input[type=range].wsf-field ~ small,
.wsf-field-wrapper[data-type='price_range'].wsf-range-vertical input[type=price_range].wsf-field ~ small {
	margin-top: calc(var(--wsf-range-vertical-height) - <?php self::e(($range_size / 2) . $uom); ?>);
    position: relative;
}

<?php if ($this->hover) { ?>
input[type=color].wsf-field:hover:enabled {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>;
<?php } ?>
<?php if ($this->hover_color != $this->color) { ?>
	color: <?php self::e($this->hover_color); ?>;
<?php } ?>
}
<?php } ?>

input[type=color].wsf-field:focus {
<?php if ($this->focus) { ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
<?php } ?>
	outline: 0;
}

input[type=color].wsf-field:disabled {
<?php if ($this->border) { ?>
<?php if ($this->disabled_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
<?php } ?>
<?php } ?>
	cursor: not-allowed;
	opacity: .5;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
}

input[type=color].wsf-field::-moz-focus-inner {
	border: 0;
	padding: 0;
}

input[type=color].wsf-field::-webkit-color-swatch-wrapper {
	padding: 0;
}

input[type=color].wsf-field::-webkit-color-swatch {
	border: none;
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
	box-shadow: inset 0 0 0 2px <?php self::e($this->color_default_inverted); ?>;
}

/* File Upload */
input[type=file].wsf-field {
	cursor: pointer;
	overflow: hidden;
}

input[type=file].wsf-field::file-selector-button {
	-webkit-appearance: none;
	background-color: <?php self::e($this->color_default_lightest); ?>;
	border: none;
<?php if ($this->border) { ?>
	-webkit-border-end: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
	border-inline-end: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
	border-radius: 0;
	color: <?php self::e($this->color) ?>;
	cursor: pointer;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	height: <?php self::e($input_height . $uom); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin: -<?php self::e($this->spacing_vertical . $uom . ' -' . $this->spacing_horizontal . $uom); ?>;
	-webkit-margin-end: <?php self::e($this->spacing_vertical . $uom); ?>;
	margin-inline-end: <?php self::e($this->spacing_vertical . $uom); ?>;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
	touch-action: manipulation;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

input[type=file].wsf-field::-webkit-file-upload-button {
	-webkit-appearance: none;
	background-color: <?php self::e($this->color_default_lightest); ?>;
	border: none;
<?php if ($this->border) { ?>
	-webkit-border-end: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
	border-inline-end: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
	border-radius: 0;
	color: <?php self::e($this->color) ?>;
	cursor: pointer;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	height: <?php self::e($input_height . $uom); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin: -<?php self::e($this->spacing_vertical . $uom . ' -' . $this->spacing_horizontal . $uom); ?>;
	-webkit-margin-end: <?php self::e($this->spacing_vertical . $uom); ?>;
	margin-inline-end: <?php self::e($this->spacing_vertical . $uom); ?>;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
	touch-action: manipulation;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

input[type=file].wsf-field:enabled::file-selector-button:hover {
	background-color: <?php self::e($this->color_default_lightest_dark_10); ?>;
}

input[type=file].wsf-field:enabled::-webkit-file-upload-button:hover {
	background-color: <?php self::e($this->color_default_lightest_dark_10); ?>;
}

input[type=file].wsf-field:enabled::file-selector-button:focus {
	background-color: <?php self::e($this->color_default_lightest_dark_20); ?>;
	outline: 0;
}

input[type=file].wsf-field:enabled::-webkit-file-upload-button:focus {
	background-color: <?php self::e($this->color_default_lightest_dark_20); ?>;
	outline: 0;
}

input[type=file].wsf-field:disabled {
	cursor: not-allowed;
}

input[type=file].wsf-field:disabled::file-selector-button {
	color: <?php self::e($this->color) ?>;
	cursor: not-allowed;
	-webkit-text-fill-color: <?php self::e($this->color) ?>;
}

input[type=file].wsf-field:disabled::-webkit-file-upload-button {
	color: <?php self::e($this->color) ?>;
	cursor: not-allowed;
	-webkit-text-fill-color: <?php self::e($this->color) ?>;
}

input[type=text].wsf-field ~ .dropzone {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' dashed ' . $this->border_color); ?>;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	padding: <?php self::e($this->grid_gutter . $uom . ' ' . $this->grid_gutter . $uom . ' 0'); ?>;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	cursor: pointer;
}

input[type=text].wsf-field ~ .dropzone .dz-message {
	color: <?php self::e($this->placeholder_color); ?>;
	margin-bottom: <?php self::e($this->grid_gutter . $uom); ?>;
	text-align: center;
}

input[type=text].wsf-field ~ .dropzone.dz-started .dz-message {
	display: none;
}

input[type=text].wsf-field ~ .dropzone .wsf-dropzonejs-previews {
	pointer-events: none;
}

input[type=text].wsf-field ~ .dropzone .wsf-dropzonejs-preview {
	cursor: move;
	pointer-events: all;
}

input[type=text].wsf-field ~ .dropzone .wsf-progress {
	background-color: <?php self::e($this->color_default_lightest); ?>;
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
	height: <?php self::e(($progress_size / 2) . $uom); ?>;
	margin-top: <?php self::e($this->spacing_small . $uom); ?>;
	overflow: hidden;
}

input[type=text].wsf-field ~ .dropzone .wsf-progress .wsf-upload {
	background-color: <?php self::e($this->color_primary); ?>;
	height: 100%;
}

input[type=text].wsf-field ~ .dropzone .wsf-progress.wsf-progress-success .wsf-upload {
	background-color: <?php self::e($this->color_success); ?>;
}

input[type=text].wsf-field ~ .dropzone .dz-complete .wsf-progress {
	display: none;
}

<?php if ($this->focus) { ?>
input[type=text].wsf-field:enabled ~ .dropzone.dz-drag-hover {
<?php if ($this->focus_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->focus_background_color); ?>;
<?php } ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
<?php if ($this->focus_color != $this->color) { ?>
	color: <?php self::e($this->focus_color); ?>;
<?php } ?>
}
<?php } ?>

input[type=text].wsf-field:disabled ~ .dropzone {
<?php if ($this->disabled_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->disabled_background_color); ?>;
<?php } ?>
<?php if ($this->border) { ?>
<?php if ($this->disabled_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
<?php } ?>
<?php } ?>
<?php if ($this->disabled_color != $this->color) { ?>
	color: <?php self::e($this->disabled_color); ?>;
<?php } ?>
	cursor: not-allowed;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
}

input[type=text].wsf-field:disabled ~ .dropzone .wsf-dropzonejs-preview {
	opacity: .5;
}

.wsf-validated input[type=text].wsf-field:invalid ~ .dropzone {
<?php if ($this->error_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->error_background_color); ?>;
<?php } ?>
<?php if ($this->border) { ?>
<?php if ($this->error_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->error_border_color); ?>;
<?php } ?>
<?php } ?>
}

<?php if ($this->focus) { ?>
<?php if ($this->box_shadow) { ?>
.wsf-validated input[type=text].wsf-field:invalid ~ .dropzone.dz-drag-hover {
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->error_border_color, $this->box_shadow_color_opacity)); ?>;
}
<?php } ?>
<?php } ?>

input[type=text].wsf-field ~ .dropzone .wsf-invalid-feedback {
	display: block;
}

/* Signature */
input[type=text].wsf-field ~ canvas {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	cursor: crosshair;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	width: 100%;
}

<?php if ($this->hover) { ?>
input[type=text].wsf-field ~ canvas:hover {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>;
<?php } ?>
<?php if ($this->hover_color != $this->color) { ?>
	color: <?php self::e($this->hover_color); ?>;
<?php } ?>
}
<?php } ?>

input[type=text].wsf-field ~ canvas:focus {
<?php if ($this->focus) { ?>
<?php if ($this->focus_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->focus_background_color); ?>;
<?php } ?>
<?php if ($this->focus_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->focus_border_color); ?>;
<?php } ?>
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
<?php if ($this->focus_color != $this->color) { ?>
	color: <?php self::e($this->focus_color); ?>;
<?php } ?>
<?php } ?>
	outline: 0;
}

input[type=text].wsf-field:disabled ~ canvas {
<?php if ($this->disabled_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->disabled_background_color); ?>;
<?php } ?>
<?php if ($this->border) { ?>
<?php if ($this->disabled_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->disabled_border_color); ?>;
<?php } ?>
<?php } ?>
<?php if ($this->disabled_color != $this->color) { ?>
	color: <?php self::e($this->disabled_color); ?>;
<?php } ?>
	cursor: not-allowed;
	opacity: 1;
	pointer-events: none;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
}

input[type=text].wsf-field:disabled ~ .wsf-help > [data-action='wsf-signature-clear'] {
	display: none;
}

.wsf-validated input[type=text].wsf-field:invalid ~ canvas {
<?php if ($this->error_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->error_background_color); ?>;
<?php } ?>
<?php if ($this->border) { ?>
<?php if ($this->error_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->error_border_color); ?>;
<?php } ?>
<?php } ?>
}

<?php if ($this->focus) { ?>
<?php if ($this->box_shadow) { ?>
.wsf-validated input[type=text].wsf-field:invalid ~ canvas:focus {
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->error_border_color, $this->box_shadow_color_opacity)); ?>;
}
<?php } ?>
<?php } ?>

/* Progress */
progress.wsf-progress {
	display: block;
	height: <?php self::e($progress_size . $uom); ?>;
	vertical-align: baseline;
	width: 100%;
}

progress.wsf-progress[value] {
	-webkit-appearance: none;
	background-color: <?php self::e($this->color_default_lightest); ?>;
	border: none;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
}

progress.wsf-progress[value]::-webkit-progress-bar {
	background-color: <?php self::e($this->color_default_lightest); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
}

progress.wsf-progress[value]::-webkit-progress-value {
	background-color: <?php self::e($this->color_primary); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
}

progress.wsf-progress[value]::-moz-progress-bar {
	background-color: <?php self::e($this->color_primary); ?>;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
}

progress.wsf-progress[value]::-ms-fill {
	background-color: <?php self::e($this->color_primary); ?>;
	border: 0;
}

progress.wsf-progress.wsf-progress-success[value]::-webkit-progress-value {
	background-color: <?php self::e($this->color_success); ?>;
}

progress.wsf-progress.wsf-progress-success[value]::-moz-progress-bar {
	background-color: <?php self::e($this->color_success); ?>;
}

progress.wsf-progress.wsf-progress-success[value]::-ms-fill {
	background-color: <?php self::e($this->color_success); ?>;
}

/* Meter */
meter.wsf-meter {
	background: <?php self::e($this->color_default_lightest); ?>;
	border: none;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	display: block;
	height: <?php self::e($meter_size . $uom); ?>;
	overflow: hidden;
	width: 100%;
}

meter.wsf-meter::-webkit-meter-bar {
	background: <?php self::e($this->color_default_lightest); ?>;
	border: none;
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	display: block;
	height: <?php self::e($meter_size . $uom); ?>;
	width: 100%;
}

meter.wsf-meter::-webkit-meter-optimum-value {
	background: <?php self::e($this->color_success); ?>;
}

meter.wsf-meter:-moz-meter-optimum::-moz-meter-bar {
	background: <?php self::e($this->color_success); ?>;
}

meter.wsf-meter::-webkit-meter-suboptimum-value {
	background: <?php self::e($this->color_warning); ?>;
}

meter.wsf-meter:-moz-meter-sub-optimum::-moz-meter-bar {
	background: <?php self::e($this->color_warning); ?>;
}

meter.wsf-meter::-webkit-meter-even-less-good-value {
	background: <?php self::e($this->color_danger); ?>;
}

meter.wsf-meter:-moz-meter-sub-sub-optimum::-moz-meter-bar {
	background: <?php self::e($this->color_danger); ?>;
}

/* Password */
[data-type='password'] input.wsf-field[data-password-strength-meter] {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='25px' width='5px' viewBox='0 0 5 25'%3E%3Cpath fill='<?php self::c($this->color_default_lighter); ?>' d='M2.5,20 C1.125,20 0,21.125 0,22.5 C0,23.875 1.125,25 2.5,25 C3.875,25 5,23.875 5,22.5 C5,21.125 3.875,20 2.5,20 Z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color_default_lighter); ?>' d='M2.5,10 C1.125,10 0,11.125 0,12.5 C0,13.875 1.125,15 2.5,15 C3.875,15 5,13.875 5,12.5 C5,11.125 3.875,10 2.5,10 Z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color_default_lighter); ?>' d='M2.5,5 C3.875,5 5,3.875 5,2.5 C5,1.125 3.875,0 2.5,0 C1.125,0 0,1.125 0,2.5 C0,3.875 1.125,5 2.5,5 Z'%3E%3C/path%3E%3C/svg%3E");
	background-position: right <?php self::e($this->spacing_horizontal . $uom); ?> center;
	background-repeat: no-repeat;
	background-size: 5px 25px;
	-webkit-padding-end: <?php self::e((($this->spacing_horizontal * 2) + 5) . $uom); ?>;
	padding-inline-end: <?php self::e((($this->spacing_horizontal * 2) + 5) . $uom); ?>;
}

[data-type='password'] input.wsf-field[data-password-strength-meter=success] {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='25px' width='5px' viewBox='0 0 5 25'%3E%3Cpath fill='<?php self::c($this->color_success); ?>' d='M2.5,20 C1.125,20 0,21.125 0,22.5 C0,23.875 1.125,25 2.5,25 C3.875,25 5,23.875 5,22.5 C5,21.125 3.875,20 2.5,20 Z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color_success); ?>' d='M2.5,10 C1.125,10 0,11.125 0,12.5 C0,13.875 1.125,15 2.5,15 C3.875,15 5,13.875 5,12.5 C5,11.125 3.875,10 2.5,10 Z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color_success); ?>' d='M2.5,5 C3.875,5 5,3.875 5,2.5 C5,1.125 3.875,0 2.5,0 C1.125,0 0,1.125 0,2.5 C0,3.875 1.125,5 2.5,5 Z'%3E%3C/path%3E%3C/svg%3E");
}

[data-type='password'] input.wsf-field[data-password-strength-meter=warning] {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='25px' width='5px' viewBox='0 0 5 25'%3E%3Cpath fill='<?php self::c($this->color_warning); ?>' d='M2.5,20 C1.125,20 0,21.125 0,22.5 C0,23.875 1.125,25 2.5,25 C3.875,25 5,23.875 5,22.5 C5,21.125 3.875,20 2.5,20 Z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color_warning); ?>' d='M2.5,10 C1.125,10 0,11.125 0,12.5 C0,13.875 1.125,15 2.5,15 C3.875,15 5,13.875 5,12.5 C5,11.125 3.875,10 2.5,10 Z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color_default_lighter); ?>' d='M2.5,5 C3.875,5 5,3.875 5,2.5 C5,1.125 3.875,0 2.5,0 C1.125,0 0,1.125 0,2.5 C0,3.875 1.125,5 2.5,5 Z'%3E%3C/path%3E%3C/svg%3E");
}

[data-type='password'] input.wsf-field[data-password-strength-meter=danger] {
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='25px' width='5px' viewBox='0 0 5 25'%3E%3Cpath fill='<?php self::c($this->color_danger); ?>' d='M2.5,20 C1.125,20 0,21.125 0,22.5 C0,23.875 1.125,25 2.5,25 C3.875,25 5,23.875 5,22.5 C5,21.125 3.875,20 2.5,20 Z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color_default_lighter); ?>' d='M2.5,10 C1.125,10 0,11.125 0,12.5 C0,13.875 1.125,15 2.5,15 C3.875,15 5,13.875 5,12.5 C5,11.125 3.875,10 2.5,10 Z'%3E%3C/path%3E%3Cpath fill='<?php self::c($this->color_default_lighter); ?>' d='M2.5,5 C3.875,5 5,3.875 5,2.5 C5,1.125 3.875,0 2.5,0 C1.125,0 0,1.125 0,2.5 C0,3.875 1.125,5 2.5,5 Z'%3E%3C/path%3E%3C/svg%3E");
}

/* Legal */
[data-wsf-legal].wsf-field {
	background-color: <?php self::e($this->background_color); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	color: <?php self::e($this->color); ?>;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

<?php if ($this->hover) { ?>
[data-wsf-legal].wsf-field:hover {
<?php if ($this->hover_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->hover_background_color); ?>;
<?php } ?>
<?php if ($this->hover_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->hover_border_color); ?>;
<?php } ?>
<?php if ($this->hover_color != $this->color) { ?>
	color: <?php self::e($this->hover_color); ?>;
<?php } ?>
}
<?php } ?>

[data-wsf-legal].wsf-field h1,
[data-wsf-legal].wsf-field h2,
[data-wsf-legal].wsf-field h3,
[data-wsf-legal].wsf-field h4,
[data-wsf-legal].wsf-field h5,
[data-wsf-legal].wsf-field h6 {
	color: <?php self::e($this->color); ?>;
	font-family: <?php self::e($this->font_family); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin: 0 0 <?php self::e($this->spacing_vertical . $uom); ?>;
}

[data-wsf-legal].wsf-field p,
[data-wsf-legal].wsf-field ul,
[data-wsf-legal].wsf-field ol,
[data-wsf-legal].wsf-field th,
[data-wsf-legal].wsf-field td {
	color: <?php self::e($this->color); ?>;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin: 0 0 <?php self::e($this->spacing_vertical . $uom); ?>;
}

/* Validation */
.wsf-validated input[type=date].wsf-field:invalid,
.wsf-validated input[type=datetime-local].wsf-field:invalid,
.wsf-validated input[type=file].wsf-field:invalid,
.wsf-validated input[type=month].wsf-field:invalid,
.wsf-validated input[type=password].wsf-field:invalid,
.wsf-validated input[type=search].wsf-field:invalid,
.wsf-validated input[type=time].wsf-field:invalid,
.wsf-validated input[type=week].wsf-field:invalid,
.wsf-validated input[type=email].wsf-field:invalid,
.wsf-validated input[type=number].wsf-field:invalid,
.wsf-validated input[type=tel].wsf-field:invalid,
.wsf-validated input[type=text].wsf-field:invalid,
.wsf-validated input[type=url].wsf-field:invalid,
.wsf-validated select.wsf-field:invalid,
.wsf-validated textarea.wsf-field:invalid {
<?php if ($this->error_background_color != $this->background_color) { ?>
	background-color: <?php self::e($this->error_background_color); ?>;
<?php } ?>
<?php if ($this->border) { ?>
<?php if ($this->error_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->error_border_color); ?>;
<?php } ?>
<?php } ?>
<?php if ($this->error_border_color != $this->color) { ?>
	color: <?php self::e($this->error_color); ?>;
<?php } ?>
}

<?php if ($this->focus) { ?>
<?php if ($this->box_shadow) { ?>
.wsf-validated input[type=date].wsf-field:invalid:focus,
.wsf-validated input[type=datetime-local].wsf-field:invalid:focus,
.wsf-validated input[type=file].wsf-field:invalid:focus,
.wsf-validated input[type=month].wsf-field:invalid:focus,
.wsf-validated input[type=password].wsf-field:invalid:focus,
.wsf-validated input[type=search].wsf-field:invalid:focus,
.wsf-validated input[type=time].wsf-field:invalid:focus,
.wsf-validated input[type=week].wsf-field:invalid:focus,
.wsf-validated input[type=email].wsf-field:invalid:focus,
.wsf-validated input[type=number].wsf-field:invalid:focus,
.wsf-validated input[type=tel].wsf-field:invalid:focus,
.wsf-validated input[type=text].wsf-field:invalid:focus,
.wsf-validated input[type=url].wsf-field:invalid:focus,
.wsf-validated select.wsf-field:invalid:focus,
.wsf-validated textarea.wsf-field:invalid:focus {
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->error_border_color, $this->box_shadow_color_opacity)); ?>;
}
<?php } ?>
<?php } ?>

.wsf-validated input[type=date].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=datetime-local].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=file].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=month].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=password].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=search].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=time].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=week].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=email].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=number].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=tel].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=text].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=url].wsf-field:-moz-ui-invalid,
.wsf-validated select.wsf-field:-moz-ui-invalid,
.wsf-validated textarea.wsf-field:-moz-ui-invalid {
	box-shadow: none;
}

<?php if ($this->error_color != $this->color) { ?>
.wsf-validated select.wsf-field:not([multiple]):not([size]):invalid {
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->error_color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
}
<?php } ?>

.wsf-validated select.wsf-field:invalid ~ .select2-container .select2-selection--single,
.wsf-validated select.wsf-field:invalid ~ .select2-container .select2-selection--multiple {
<?php if ($this->border) { ?>
<?php if ($this->error_border_color != $this->border_color) { ?>
	border-color: <?php self::e($this->error_border_color); ?>;
<?php } ?>
<?php } ?>
}

<?php if ($this->error_color != $this->color) { ?>
.wsf-validated select.wsf-field:invalid ~ .select2-container .select2-selection--single {
	background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2210%22%20height%3D%225%22%20viewBox%3D%22169%20177%2010%205%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22<?php self::c($this->error_color); ?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M174%20182l5-5h-10%22%2F%3E%3C%2Fsvg%3E');
}
<?php } ?>

<?php if ($this->focus) { ?>
<?php if ($this->box_shadow) { ?>
.wsf-validated select.wsf-field:invalid ~ .select2-container--default.select2-container--focus .select2-selection--single,
.wsf-validated select.wsf-field:invalid ~ .select2-container--default.select2-container--focus .select2-selection--multiple {
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->error_border_color, $this->box_shadow_color_opacity)); ?>;
}
<?php } ?>
<?php } ?>

<?php if ($this->border) { ?>
<?php if ($this->error_border_color != $this->border_color) { ?>
.wsf-validated input[type=checkbox].wsf-field:invalid + label.wsf-label::before,
.wsf-validated input[type=radio].wsf-field:invalid + label.wsf-label::before {
	border-color: <?php self::e($this->error_border_color); ?>;
}
<?php } ?>
<?php } ?>

<?php if ($this->focus) { ?>
<?php if ($this->box_shadow) { ?>
.wsf-validated input[type=checkbox].wsf-field:invalid:focus + label.wsf-label::before,
.wsf-validated input[type=radio].wsf-field:invalid:focus + label.wsf-label::before {
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->error_border_color, $this->box_shadow_color_opacity)); ?>;
}
<?php } ?>
<?php } ?>

/* Message */
.wsf-alert {
	background-color: <?php self::e($this->color_default_lightest); ?>;
<?php if ($this->border) { ?>
	-webkit-border-start: <?php self::e(($this->border_width * 4) . $uom . ' solid ' . $this->border_color); ?>;
	border-inline-start: <?php self::e(($this->border_width * 4) . $uom . ' solid ' . $this->border_color); ?>;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
	margin-bottom: <?php self::e($this->grid_gutter . $uom); ?>;
}

.wsf-alert a {
	text-decoration: underline;
}

.wsf-alert > :first-child {
	margin-top: 0;
}

.wsf-alert > :last-child {
	margin-bottom: 0;
}

.wsf-alert.wsf-alert-success {
	background-color: <?php self::e($this->color_success_light_85); ?>;
<?php if ($this->border) { ?>
	border-color: <?php self::e($this->color_success_light_40); ?>;
<?php } ?>
	color: <?php self::e($this->color_success_dark_40); ?>;
}

.wsf-alert.wsf-alert-success a,
.wsf-alert.wsf-alert-success a:hover,
.wsf-alert.wsf-alert-success a:focus {
	color: <?php self::e($this->color_success_dark_60); ?>;
}

.wsf-alert.wsf-alert-information {
	background-color: <?php self::e($this->color_information_light_85); ?>;
<?php if ($this->border) { ?>
	border-color: <?php self::e($this->color_information_light_40); ?>;
<?php } ?>
	color: <?php self::e($this->color_information_dark_40); ?>;
}

.wsf-alert.wsf-alert-information a,
.wsf-alert.wsf-alert-information a:hover,
.wsf-alert.wsf-alert-information a:focus {
	color: <?php self::e($this->color_information_dark_60); ?>;
}

.wsf-alert.wsf-alert-warning {
	background-color: <?php self::e($this->color_warning_light_85); ?>;
<?php if ($this->border) { ?>
	border-color: <?php self::e($this->color_warning_light_40); ?>;
<?php } ?>
	color: <?php self::e($this->color_warning_dark_60); ?>;
}

.wsf-alert.wsf-alert-warning a,
.wsf-alert.wsf-alert-warning a:hover,
.wsf-alert.wsf-alert-warning a:focus {
	color: <?php self::e($this->color_warning_dark_60); ?>;
}

.wsf-alert.wsf-alert-danger {
	background-color: <?php self::e($this->color_danger_light_85); ?>;
<?php if ($this->border) { ?>
	border-color: <?php self::e($this->color_danger_light_40); ?>;
<?php } ?>
	color: <?php self::e($this->color_danger_dark_60); ?>;
}

.wsf-alert.wsf-alert-danger a,
.wsf-alert.wsf-alert-danger a:hover,
.wsf-alert.wsf-alert-danger a:focus {
	color: <?php self::e($this->color_danger_dark_60); ?>;
}

/* Button */
button.wsf-button {
	-webkit-appearance: none;
	background-color: <?php self::e($this->color_default_lighter); ?>;
<?php if ($this->border) { ?>
	border: <?php self::e($this->border_width . $uom . ' ' . $this->border_style . ' ' . $this->border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
<?php if ($this->border_radius > 0) { ?>
	border-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
	color: <?php self::e($this->color); ?>;
	cursor: pointer;
	display: inline-block;
	font-family: <?php self::e($this->font_family); ?>;
	font-size: <?php self::e($this->font_size . $uom); ?>;
	font-weight: <?php self::e($this->font_weight); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	padding: <?php self::e($this->spacing_vertical . $uom . ' ' . $this->spacing_horizontal . $uom); ?>;
	margin: 0;
	text-align: center;
	text-decoration: none;
	touch-action: manipulation;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, border-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, box-shadow <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
	vertical-align: middle;
}

button.wsf-button.wsf-button-full {
	width: 100%;
}

<?php if ($this->hover) { ?>
button.wsf-button:hover {
	background-color: <?php self::e($this->color_default_lighter_dark_10); ?>;
	border-color: <?php self::e($this->color_default_lighter_dark_10); ?>;
}
<?php } ?>

button.wsf-button:focus,
button.wsf-button:active {
<?php if ($this->focus) { ?>
	background-color: <?php self::e($this->color_default_lighter_dark_20); ?>;
	border-color: <?php self::e($this->color_default_lighter_dark_20); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->border_color, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
<?php } ?>
	outline: 0;
}

button.wsf-button:disabled {
	background-color: <?php self::e($this->color_default_lighter); ?>;
	border-color: <?php self::e($this->border_color); ?>;
}

button.wsf-button.wsf-button-primary {
	background-color: <?php self::e($this->color_primary); ?>;
	border-color: <?php self::e($this->color_primary); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-primary:hover {
	background-color: <?php self::e($this->color_primary_dark_10); ?>;
	border-color: <?php self::e($this->color_primary_dark_10); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-primary:focus,
button.wsf-button.wsf-button-primary:active {
	background-color: <?php self::e($this->color_primary_dark_20); ?>;
	border-color: <?php self::e($this->color_primary_dark_20); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_primary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-primary:disabled {
	background-color: <?php self::e($this->color_primary); ?>;
	border-color: <?php self::e($this->color_primary); ?>;
}

button.wsf-button.wsf-button-secondary {
	background-color: <?php self::e($this->color_secondary); ?>;
	border-color: <?php self::e($this->color_secondary); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-secondary:hover {
	background-color: <?php self::e($this->color_secondary_dark_10); ?>;
	border-color: <?php self::e($this->color_secondary_dark_10); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-secondary:focus,
button.wsf-button.wsf-button-secondary:active {
	background-color: <?php self::e($this->color_secondary_dark_20); ?>;
	border-color: <?php self::e($this->color_secondary_dark_20); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_secondary, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-secondary:disabled {
	background-color: <?php self::e($this->color_secondary); ?>;
	border-color: <?php self::e($this->color_secondary); ?>;
}

button.wsf-button.wsf-button-success {
	background-color: <?php self::e($this->color_success); ?>;
	border-color: <?php self::e($this->color_success); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-success:hover {
	background-color: <?php self::e($this->color_success_dark_10); ?>;
	border-color: <?php self::e($this->color_success_dark_10); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-success:focus,
button.wsf-button.wsf-button-success:active {
	background-color: <?php self::e($this->color_success_dark_20); ?>;
	border-color: <?php self::e($this->color_success_dark_20); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_success, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-success:disabled {
	background-color: <?php self::e($this->color_success); ?>;
	border-color: <?php self::e($this->color_success); ?>;
}

button.wsf-button.wsf-button-information {
	background-color: <?php self::e($this->color_information); ?>;
	border-color: <?php self::e($this->color_information); ?>;
	color: <?php self::e($this->color_default); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-information:hover {
	background-color: <?php self::e($this->color_information_dark_10); ?>;
	border-color: <?php self::e($this->color_information_dark_10); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-information:focus,
button.wsf-button.wsf-button-information:active {
	background-color: <?php self::e($this->color_information_dark_20); ?>;
	border-color: <?php self::e($this->color_information_dark_20); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_information, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-information:disabled {
	background-color: <?php self::e($this->color_information); ?>;
	border-color: <?php self::e($this->color_information); ?>;
}

button.wsf-button.wsf-button-warning {
	background-color: <?php self::e($this->color_warning); ?>;
	border-color: <?php self::e($this->color_warning); ?>;
	color: <?php self::e($this->color_default); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-warning:hover {
	background-color: <?php self::e($this->color_warning_dark_10); ?>;
	border-color: <?php self::e($this->color_warning_dark_10); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-warning:focus,
button.wsf-button.wsf-button-warning:active {
	background-color: <?php self::e($this->color_warning_dark_20); ?>;
	border-color: <?php self::e($this->color_warning_dark_20); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_warning, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-warning:disabled {
	background-color: <?php self::e($this->color_warning); ?>;
	border-color: <?php self::e($this->color_warning); ?>;
}

button.wsf-button.wsf-button-danger {
	background-color: <?php self::e($this->color_danger); ?>;
	border-color: <?php self::e($this->color_danger); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-danger:hover {
	background-color: <?php self::e($this->color_danger_dark_10); ?>;
	border-color: <?php self::e($this->color_danger_dark_10); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-danger:focus,
button.wsf-button.wsf-button-danger:active {
	background-color: <?php self::e($this->color_danger_dark_20); ?>;
	border-color: <?php self::e($this->color_danger_dark_20); ?>;
<?php if ($this->box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($this->box_shadow_width . $uom); ?> <?php self::e(WS_Form_Color::hex_color_to_rgba_color($this->color_danger, $this->box_shadow_color_opacity)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-danger:disabled {
	background-color: <?php self::e($this->color_danger); ?>;
	border-color: <?php self::e($this->color_danger); ?>;
}

<?php if ($this->border) { ?>
button.wsf-button.wsf-button-inverted {
	background-color: <?php self::e($this->background_color); ?>;
	border-color: <?php self::e($this->border_color); ?>;
	color: <?php self::e($this->color); ?>;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>, color <?php self::e($this->transition_speed . 'ms ' . $this->transition_timing_function); ?>;
<?php } ?>
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-inverted:hover {
	background-color: <?php self::e($this->color_default_lighter); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-inverted:focus,
button.wsf-button.wsf-button-inverted:active {
	background-color: <?php self::e($this->color_default_lighter); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted:disabled {
	background-color: <?php self::e($this->background_color); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-primary {
	border-color: <?php self::e($this->color_primary); ?>;
	color: <?php self::e($this->color_primary); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-primary:hover {
	background-color: <?php self::e($this->color_primary); ?>;
	border-color: <?php self::e($this->color_primary); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-primary:focus {
	background-color: <?php self::e($this->color_primary); ?>;
	border-color: <?php self::e($this->color_primary); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-primary:disabled {
	background-color: <?php self::e($this->background_color); ?>;
	border-color: <?php self::e($this->color_primary); ?>;
	color: <?php self::e($this->color_primary); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-secondary {
	border-color: <?php self::e($this->color_secondary); ?>;
	color: <?php self::e($this->color_secondary); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-secondary:hover {
	background-color: <?php self::e($this->color_secondary); ?>;
	border-color: <?php self::e($this->color_secondary); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-secondary:focus {
	background-color: <?php self::e($this->color_secondary); ?>;
	border-color: <?php self::e($this->color_secondary); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-secondary:disabled {
	background-color: <?php self::e($this->background_color); ?>;
	border-color: <?php self::e($this->color_secondary); ?>;
	color: <?php self::e($this->color_secondary); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-success {
	border-color: <?php self::e($this->color_success); ?>;
	color: <?php self::e($this->color_success); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-success:hover {
	background-color: <?php self::e($this->color_success); ?>;
	border-color: <?php self::e($this->color_success); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-success:focus {
	background-color: <?php self::e($this->color_success); ?>;
	border-color: <?php self::e($this->color_success); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-success:disabled {
	background-color: <?php self::e($this->background_color); ?>;
	border-color: <?php self::e($this->color_success); ?>;
	color: <?php self::e($this->color_success); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-information {
	border-color: <?php self::e($this->color_information); ?>;
	color: <?php self::e($this->color_information); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-information:hover {
	background-color: <?php self::e($this->color_information); ?>;
	border-color: <?php self::e($this->color_information); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-information:focus {
	background-color: <?php self::e($this->color_information); ?>;
	border-color: <?php self::e($this->color_information); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-information:disabled {
	background-color: <?php self::e($this->background_color); ?>;
	border-color: <?php self::e($this->color_information); ?>;
	color: <?php self::e($this->color_information); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-warning {
	border-color: <?php self::e($this->color_warning); ?>;
	color: <?php self::e($this->color_warning); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-warning:hover {
	background-color: <?php self::e($this->color_warning); ?>;
	border-color: <?php self::e($this->color_warning); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-warning:focus {
	background-color: <?php self::e($this->color_warning); ?>;
	border-color: <?php self::e($this->color_warning); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-warning:disabled {
	background-color: <?php self::e($this->background_color); ?>;
	border-color: <?php self::e($this->color_warning); ?>;
	color: <?php self::e($this->color_warning); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-danger {
	border-color: <?php self::e($this->color_danger); ?>;
	color: <?php self::e($this->color_danger); ?>;
}

<?php if ($this->hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-danger:hover {
	background-color: <?php self::e($this->color_danger); ?>;
	border-color: <?php self::e($this->color_danger); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

<?php if ($this->focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-danger:focus {
	background-color: <?php self::e($this->color_danger); ?>;
	border-color: <?php self::e($this->color_danger); ?>;
	color: <?php self::e($this->color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-danger:disabled {
	background-color: <?php self::e($this->background_color); ?>;
	border-color: <?php self::e($this->color_danger); ?>;
	color: <?php self::e($this->color_danger); ?>;
}
<?php } ?>

button.wsf-button::-moz-focus-inner {
	border: 0;
	margin: 0;
	padding: 0;
}

button.wsf-button:disabled {
	cursor: not-allowed;
	opacity: .5;
	transition: none;
}

.wsf-form-post-lock-progress button.wsf-button {
	cursor: progress;
}

/* Helpers */
.wsf-text-primary {
	color: <?php self::e($this->color_primary); ?>;
}

.wsf-text-secondary {
	color: <?php self::e($this->color_secondary); ?>;
}

.wsf-text-success {
	color: <?php self::e($this->color_success); ?>;
}

.wsf-text-information {
	color: <?php self::e($this->color_information); ?>;
}

.wsf-text-warning {
	color: <?php self::e($this->color_warning); ?>;
}

.wsf-text-danger {
	color: <?php self::e($this->color_danger); ?>;
}

.wsf-text-left {
	text-align: left;
}

.wsf-text-center {
	text-align: center;
}

.wsf-text-right {
	text-align: right;
}

.wsf-hidden {
	display: none !important;
}

.wsf-label-position-inside input.wsf-field[placeholder]::placeholder,
.wsf-label-position-inside textarea.wsf-field[placeholder]::placeholder {
	color: transparent !important;
}

.wsf-label-position-inside canvas.wsf-field + label,
.wsf-label-position-inside select.wsf-field + label,
.wsf-label-position-inside input.wsf-field[placeholder] + label,
.wsf-label-position-inside textarea.wsf-field[placeholder] + label,
.wsf-label-position-inside canvas.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside select.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside input.wsf-field[placeholder] + .wsf-input-group-append + label,
.wsf-label-position-inside textarea.wsf-field[placeholder] + .wsf-input-group-append + label {
	left: <?php self::e((($this->grid_gutter / 2) + $this->spacing_horizontal + $this->border_width) . $uom); ?>;
	line-height: <?php self::e($this->line_height); ?>;
	margin-top: 0;
	position: absolute;
	top: <?php self::e($this->spacing_vertical . $uom); ?>;;
	transform-origin: 0 0;
<?php if ($this->transition) { ?>
	transition: transform <?php self::e($this->transition_speed); ?>ms;
<?php } ?>
	user-select: none;
	width: auto;
}

.wsf-label-position-inside canvas.wsf-field + label,
.wsf-label-position-inside select.wsf-field + label,
.wsf-label-position-inside input.wsf-field[placeholder]:focus + label,
.wsf-label-position-inside input.wsf-field[placeholder]:not(:placeholder-shown) + label,
.wsf-label-position-inside textarea.wsf-field[placeholder]:focus + label,
.wsf-label-position-inside textarea.wsf-field[placeholder]:not(:placeholder-shown) + label,
.wsf-label-position-inside canvas.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside select.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside input.wsf-field[placeholder]:focus + .wsf-input-group-append + label,
.wsf-label-position-inside input.wsf-field[placeholder]:not(:placeholder-shown) + .wsf-input-group-append + label,
.wsf-label-position-inside textarea.wsf-field[placeholder]:focus + .wsf-input-group-append + label,
.wsf-label-position-inside textarea.wsf-field[placeholder]:not(:placeholder-shown) + .wsf-input-group-append + label {
<?php

	switch($this->label_position_inside_mode) {

		case 'move' :
?>
	background-color: <?php self::e($this->background_color); ?>;
	-webkit-margin-start: -<?php self::e(($this->font_size / 4) . $uom); ?>;
	margin-inline-start: -<?php self::e(($this->font_size / 4) . $uom); ?>;
	padding-left: <?php self::e(($this->font_size / 4) . $uom); ?>;
	padding-right: <?php self::e(($this->font_size / 4) . $uom); ?>;
	transform: translate(0, <?php self::e($this->label_column_inside_offset . $uom); ?>) scale(<?php self::e($this->label_column_inside_scale); ?>);
<?php
			break;

		default :
?>
	display: none;
<?php
	}
?>
}

.wsf-label-position-inside .iti input.wsf-field[placeholder]:focus + label,
.wsf-label-position-inside .iti input.wsf-field[placeholder]:not(:placeholder-shown) + label,
.wsf-label-position-inside .iti input.wsf-field[placeholder]:focus + .wsf-input-group-append + label,
.wsf-label-position-inside .iti input.wsf-field[placeholder]:not(:placeholder-shown) + .wsf-input-group-append + label {
<?php

	switch($this->label_position_inside_mode) {

		case 'move' :
?>
	transform: translate(var(--wsf-field-tel-transform-x), <?php self::e($this->label_column_inside_offset . $uom); ?>) scale(<?php self::e($this->label_column_inside_scale); ?>);
<?php
			break;
	}
?>
}

/* Fix: z-index for Google Places search results container in Oxygen pop-ups */
.pac-container {
	z-index: 1401;
}
<?php
		}

		// Skin - RTL
		public function render_skin_rtl() {

			// Forms
			$uom = 'px';
			$this->spacing_horizontal = 10;
			$this->spacing_vertical = 8.5;
			$input_height = round(($this->font_size * $this->line_height) + ($this->spacing_vertical * 2) + ($this->border_width * 2));
			$checkbox_size = round($this->font_size * $this->line_height);
			$radio_size = round($this->font_size * $this->line_height);
?>

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs > li > a {
<?php if ($this->border_radius > 0) { ?>
	border-top-left-radius: 0;
	border-top-right-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-bottom-left-radius: 0;
	border-bottom-right-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
}

.wsf-form ul.wsf-group-tabs.wsf-steps > li > a::after {
	left: auto;
	right: -50%;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-steps > li > a::after {
	left: auto;
	right: <?php self::e(($input_height / 2) . $uom); ?>;
}

.wsf-form-canvas.wsf-vertical ul.wsf-group-tabs.wsf-steps > li > a {
	text-align: right;
}

<?php if ($this->border_radius > 0) { ?>
.wsf-input-group-has-prepend > .wsf-field,
.wsf-input-group-has-prepend > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-prepend > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-prepend > .dropzone {
	border-top-left-radius: <?php self::e($this->border_radius . $uom); ?> !important;
	border-top-right-radius: 0 !important;
	border-bottom-left-radius: <?php self::e($this->border_radius . $uom); ?> !important;
	border-bottom-right-radius: 0 !important;
}

.wsf-input-group-has-append > .wsf-field,
.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-append > .dropzone {
	border-top-left-radius: 0 !important;
	border-top-right-radius: <?php self::e($this->border_radius . $uom); ?> !important;
	border-bottom-left-radius: 0 !important;
	border-bottom-right-radius: <?php self::e($this->border_radius . $uom); ?> !important;
}
<?php } ?>

.wsf-input-group-has-prepend.wsf-input-group-has-append > .wsf-field,
.wsf-input-group-has-prepend.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-prepend.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-prepend.wsf-input-group-has-append > .dropzone {
	border-top-left-radius: 0 !important;
	border-top-right-radius: 0 !important;
	border-bottom-left-radius: 0 !important;
	border-bottom-right-radius: 0 !important;
}

.wsf-input-group-prepend {
<?php if ($this->border_radius > 0) { ?>
	border-top-left-radius: 0;
	border-top-right-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-bottom-left-radius: 0;
	border-bottom-right-radius: <?php self::e($this->border_radius . $uom); ?>;
<?php } ?>
}

.wsf-input-group-append {
<?php if ($this->border_radius > 0) { ?>
	border-top-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-top-right-radius: 0;
	border-bottom-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-bottom-right-radius: 0;
<?php } ?>
}

select.wsf-field:not([multiple]):not([size]) {
	background-position: left 10px center;
}

select.wsf-field ~ .select2-container .select2-selection--single {
	background-position: left 10px center;
}

select.wsf-field ~ .select2-container--default .select2-selection--multiple .select2-selection__clear {
	float: left;
}

input[type=checkbox].wsf-field + label.wsf-label::before {
	left: auto;
	right: 0;
}

input[type=checkbox].wsf-field + label.wsf-label::after {
	left: auto;
	right: 0;
}

input[type=radio].wsf-field + label.wsf-label::before {
	left: auto;
	right: 0;
}

input[type=radio].wsf-field + label.wsf-label::after {
	left: auto;
	right: 0;
}

input[type=checkbox].wsf-field.wsf-switch + label.wsf-label::after,
input[type=radio].wsf-field.wsf-switch + label.wsf-label::after {
	left: auto;
	right: <?php self::e(($this->border_width * 2). $uom); ?>;
<?php if ($this->transition) { ?>
	transition: background-color <?php self::e($this->transition_speed); ?>, border-color <?php self::e($this->transition_speed); ?>, right <?php self::e($this->transition_speed); ?>;
<?php } ?>
}

input[type=checkbox].wsf-field.wsf-switch:checked + label.wsf-label::after,
input[type=radio].wsf-field.wsf-switch:checked + label.wsf-label::after {
	left: auto;
	right: <?php self::e(($checkbox_size - ($this->border_width * 2)) . $uom); ?>
}

<?php if ($this->border_radius > 0) { ?>
progress.wsf-progress[value]::-webkit-progress-value {
	border-bottom-left-radius: 0;
	border-bottom-right-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-top-left-radius: 0;
	border-top-right-radius: <?php self::e($this->border_radius . $uom); ?>;
}

progress.wsf-progress[value]::-moz-progress-bar {
	border-bottom-left-radius: 0;
	border-bottom-right-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-top-left-radius: 0;
	border-top-right-radius: <?php self::e($this->border_radius . $uom); ?>;
}

progress.wsf-progress[value="100"]::-webkit-progress-value {
	border-bottom-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-bottom-right-radius: 0;
	border-top-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-top-right-radius: 0;
}

progress.wsf-progress[value="100"]::-moz-progress-bar {
	border-bottom-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-bottom-right-radius: 0;
	border-top-left-radius: <?php self::e($this->border_radius . $uom); ?>;
	border-top-right-radius: 0;
}
<?php } ?>

[data-type='password'] input.wsf-field[data-password-strength-meter] {
	background-position: left <?php self::e($this->spacing_horizontal . $uom); ?> center;
}
}

.wsf-label-position-inside canvas.wsf-field + label,
.wsf-label-position-inside select.wsf-field + label,
.wsf-label-position-inside input.wsf-field[placeholder] + label,
.wsf-label-position-inside textarea.wsf-field[placeholder] + label,
.wsf-label-position-inside canvas.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside select.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside input.wsf-field[placeholder] + .wsf-input-group-append + label,
.wsf-label-position-inside textarea.wsf-field[placeholder] + .wsf-input-group-append + label {
	left: auto;
	right: <?php self::e((($this->grid_gutter / 2) + $this->spacing_horizontal + $this->border_width) . $uom); ?>;
}

/* Fix: RTL for DropzoneJS */
.dz-hidden-input {
	left: auto;
	right: 0;
}

/* Fix: Elementor for TinyMCE buttons */
.elementor-element .wsf-form-canvas .wp-editor-tools button {
	background-color: inherit !important;
}
<?php
		}
	}
