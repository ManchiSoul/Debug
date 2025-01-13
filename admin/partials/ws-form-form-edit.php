<?php

	// Get ID of form (0 = New)
	$form_id = absint(WS_Form_Common::get_query_var('id', 0));

	// Loader icon
	WS_Form_Common::loader();
?>
<!-- Layout Editor -->
<div id="wsf-layout-editor">

<!-- Header -->
<div class="wsf-loading-hidden">
<div id="wsf-header">
<h1><?php esc_html_e('Edit Form', 'ws-form') ?></h1>

<!-- Form actions -->
<?php

	// Publish
	if(WS_Form_Common::can_user('publish_form')) {
?>
<button data-action="wsf-publish" class="wsf-button wsf-button-small wsf-button-information" disabled><?php WS_Form_Common::render_icon_16_svg('publish'); ?> <?php esc_html_e('Publish', 'ws-form'); ?></button>
<?php
	}

	// Preview
?>
<a data-action="wsf-preview" class="wsf-button wsf-button-small" href="<?php WS_Form_Common::echo_esc_attr(WS_Form_Common::get_preview_url($form_id)); ?>" target="wsf-preview-<?php WS_Form_Common::echo_esc_attr($form_id); ?>"><?php WS_Form_Common::render_icon_16_svg('visible'); ?> <?php esc_html_e('Preview', 'ws-form'); ?></a>
<?php

	// Style
	if(WS_Form_Common::styler_visible_admin()) {
?>
<a data-action="wsf-style" class="wsf-button wsf-button-small" href="#" target="wsf-style-<?php WS_Form_Common::echo_esc_attr($form_id); ?>"><?php WS_Form_Common::render_icon_16_svg('style'); ?> <?php esc_html_e('Style', 'ws-form'); ?></a>
<?php
	}

	// Submissions
	if(WS_Form_Common::can_user('read_submission')) {
?>
<a data-action="wsf-submission" class="wsf-button wsf-button-small" href="<?php WS_Form_Common::echo_esc_attr(admin_url('admin.php?page=ws-form-submit&id=' . $form_id)); ?>"><?php WS_Form_Common::render_icon_16_svg('table'); ?> <?php esc_html_e('Submissions', 'ws-form'); ?></a>
<?php
	}

	// Hook for additional buttons
	do_action('wsf_form_edit_nav_left', $form_id);
?>
<ul class="wsf-settings wsf-settings-form">
<?php
	// Download
	if(WS_Form_Common::can_user('export_form')) {
?>
<li data-action="wsf-form-download"<?php WS_Form_Common::tooltip_e(__('Export Form', 'ws-form'), 'bottom-center'); ?>><?php WS_Form_Common::render_icon_16_svg('download'); ?></li>
<?php
	}
	
	// Upload
	if(WS_Form_Common::can_user('import_form')) {
?>
<li data-action="wsf-form-upload"<?php WS_Form_Common::tooltip_e(__('Import Form', 'ws-form'), 'bottom-center'); ?>><?php WS_Form_Common::render_icon_16_svg('upload'); ?></li>
<?php
	}
?>
<li data-action="wsf-redo"<?php WS_Form_Common::tooltip_e(__('Redo', 'ws-form'), 'bottom-center'); ?> class="wsf-redo-inactive"><?php WS_Form_Common::render_icon_16_svg('redo'); ?></li>
<li data-action="wsf-undo"<?php WS_Form_Common::tooltip_e(__('Undo', 'ws-form'), 'bottom-center'); ?> class="wsf-undo-inactive"><?php WS_Form_Common::render_icon_16_svg('undo'); ?></li>
</ul>
<?php

	// Upload
	if(WS_Form_Common::can_user('import_form')) {
?>
<input type="file" id="wsf-object-upload-file" class="wsf-file-upload" accept=".json" aria-hidden aria-label="<?php esc_html_e('File upload', 'ws-form'); ?>"/>
<?php
	}
?>
</div>
</div>
<!-- /Header -->
<?php

	// Review nag
	WS_Form_Common::review();
?>
<!-- Wrapper -->
<div id="poststuff" class="wsf-loading-hidden">

<hr class="wp-header-end">

<!-- Label -->
<div id="titlediv">
<div id="titlewrap">

<label class="screen-reader-text" id="title-prompt-text" for="title"><?php esc_html_e('Form Label', 'ws-form'); ?></label>
<input type="text" id="title" class="wsf-field" placeholder="<?php esc_html_e('Form Label', 'ws-form'); ?>" data-action="wsf-form-label" name="form_label" size="30" value="" spellcheck="true" autocomplete="off" />
<button data-action="wsf-label-save" class="wsf-button wsf-button-small wsf-button-primary"><?php esc_html_e('Save', 'ws-form'); ?></button>

</div>
</div>
<!-- /Label -->

<!-- Form -->
<div id="wsf-form" class="wsf-form wsf-form-canvas"></div>
<!-- /Form -->

</div>
<!-- /Wrapper -->

<!-- Breakpoints -->
<div id="wsf-breakpoints"></div>
<!-- /Breakpoints -->

</div>
<!-- /Layout Editor -->

<!-- Popover -->
<div id="wsf-popover" class="wsf-ui-cancel"></div>
<!-- /Popover -->

<!-- Field Draggable Container (Fixes Chrome bug) -->
<div class="wsf-field-selector"><div id="wsf-field-draggable"><ul></ul></div></div>
<!-- /Field Draggable Container (Fixes Chrome bug) -->

<!-- Section Draggable Container (Fixes Chrome bug) -->
<div class="wsf-section-selector"><div id="wsf-section-draggable"><ul></ul></div></div>
<!-- /Section Draggable Container (Fixes Chrome bug) -->

<!-- Sidebars -->
<div id="wsf-sidebars"></div>
<!-- /Sidebars -->

<!-- Variable Helper -->
<div id="wsf-variable-helper-modal" class="wsf-modal">

<div id="wsf-variable-helper">

<!-- Variable Helper - Header -->
<div id="wsf-variable-helper-header">

<div class="wsf-modal-title"><?php

	WS_Form_Common::echo_get_admin_icon('#002e5f', false);	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

?><h2><?php

	WS_Form_Common::echo_esc_html(__('Variables', 'ws-form'));	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

?></h2></div>

<div class="wsf-modal-close" data-action="wsf-close" title="<?php esc_attr_e('Close', 'ws-form'); ?>"></div>

</div>
<!-- /Variable Helper - Header -->

<!-- Variable Helper - Content -->
<div class="wsf-modal-content">

<!-- Variable Helper - Search -->
<div id="wsf-variable-helper-search">

<input id="wsf-variable-helper-search-input" class="wsf-field" type="search" placeholder="<?php

	WS_Form_Common::echo_esc_html(__('Variable search...', 'ws-form'));	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

?>" />

</div>
<!-- Variable Helper - /Search -->

<!-- Variable Helper - Search - No Results -->
<div id="wsf-variable-helper-search-no-results">
	
<p><?php

	WS_Form_Common::echo_esc_html(__('No results', 'ws-form'));	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

?>
</div>
<!-- /Variable Helper - /Search - No Results -->

<!-- Variable Helper - Tabs -->
<div id="wsf-variable-helper-variables"></div>
<!-- /Variable Helper - /Tabs -->

</div>
<!-- /Variable Helper - Content -->

</div>

</div>
<!-- /Variable Helper -->

<script>

<?php

	// Get config
	$json_config = WS_Form_Config::get_config(false, array(), true);
?>
	// Embed config
	var wsf_form_json_config = {};
<?php

	// Split up config (Fixes HTTP2 error on certain hosting providers that can't handle the full JSON string)
	foreach($json_config as $key => $config) {

?>	wsf_form_json_config.<?php WS_Form_Common::echo_esc_html($key); ?> = <?php WS_Form_Common::echo_wp_json_encode($config); ?>;
<?php
	}

	$json_config = null;

	// Get form data
	try {

		$ws_form_form = New WS_Form_Form();
		$ws_form_form->id = $form_id;
		$form_object = $ws_form_form->db_read(true, true);
		$json_form = wp_json_encode($form_object);

	} catch(Exception $e) {

		$json_form = false;
	}
?>

	// Embed form data
	var wsf_form_json = { <?php

	WS_Form_Common::echo_esc_html(absint($form_id));

?>: <?php

	echo $json_form;	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	$json_form = null;

?> };

	var wsf_obj = null;

	(function($) {

		'use strict';

		// On load
		$(function() {

			// Manually inject language strings (Avoids having to call the full config)
			$.WS_Form.settings_form = [];
			$.WS_Form.settings_form.language = [];
			$.WS_Form.settings_form.language['error_server'] = '<?php esc_html_e('500 Server error response from server.', 'ws-form'); ?>';

			// Initialize WS Form
			var wsf_obj = new $.WS_Form();

			wsf_obj.menu_highlight();

			wsf_obj.render({

				'obj' : 	'#wsf-form',
				'form_id':	<?php WS_Form_Common::echo_esc_attr($form_id); ?>
			});
		});

	})(jQuery);

</script>
