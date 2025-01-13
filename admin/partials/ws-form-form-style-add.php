<?php

	global $wpdb;

	// Get core template data
	$ws_form_template = new WS_Form_Template;
	$ws_form_template->type = 'style';
	$template_categories = $ws_form_template->read_config();

	// Loader icon
	WS_Form_Common::loader();
?>
<script>

	// Localize
	var ws_form_settings_language_style_add_create = '<?php esc_html_e('Use Template', 'ws-form'); ?>';

</script>

<svg xmlns="http://www.w3.org/2000/svg" style="width: 0; height: 0; position: absolute;"><defs><pattern id="wsf-styler-template-circle-check" width="10" height="10" patternUnits="userSpaceOnUse"><rect width="10" height="10" fill="#ddd"/><rect width="5" height="5" fill="#fff"/><rect x="5" y="5" width="5" height="5" fill="#fff"/></pattern></defs></svg>

<div id="wsf-wrapper" class="<?php WS_Form_Common::wrapper_classes(); ?>">

<!-- Header -->
<div class="wsf-header">
<h1><?php esc_html_e('Add Style', 'ws-form'); ?></h1>
</div>
<hr class="wp-header-end">
<!-- /Header -->
<?php

	// Review nag
	WS_Form_Common::review();
?>
<p><?php esc_html_e('To create a new style, start by selecting a template.', 'ws-form'); ?></p>

<!-- Template -->
<div id="wsf-template-add">

<!-- Tabs - Categories -->
<ul id="wsf-template-add-tabs">
<?php

	// Loop through templates
	foreach ($template_categories as $template_category)  {

		if(isset($template_category->templates) && (count($template_category->templates) == 0)) { continue; }

		$action_id = isset($template_category->action_id) ? $template_category->action_id : false;

?><li><a href="<?php WS_Form_Common::echo_esc_attr(sprintf('#wsf_template_category_%s', $template_category->id)); ?>"><?php WS_Form_Common::echo_esc_html($template_category->label); ?><?php

		if(($action_id !== false) && ($template_category->reload)) {

?><span data-action="wsf-api-reload" data-action-id="<?php WS_Form_Common::echo_esc_attr($action_id); ?>" data-method="lists_fetch"<?php

	WS_Form_Common::tooltip_e(__('Update', 'ws-form'), 'top-center');	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

?>><?php WS_Form_Common::render_icon_16_svg('reload'); ?></span><?php

		}

?></a></li>
<?php

	}
?>
</ul>
<!-- Tabs - Categories -->
<?php

	// Loop through templates
	foreach ($template_categories as $template_category)  {

		if(isset($template_category->templates) && (count($template_category->templates) == 0)) { continue; }
?>
<!-- Tab Content: <?php WS_Form_Common::echo_esc_html($template_category->label); ?> -->
<div id="<?php WS_Form_Common::echo_esc_attr(sprintf('wsf_template_category_%s', $template_category->id)); ?>"<?php if(isset($template_category->action_id)) { ?> data-action-id="<?php WS_Form_Common::echo_esc_attr($template_category->action_id); ?>"<?php } ?> style="display: none;">
<ul class="wsf-templates">
<?php
		$ws_form_template->template_category_render($template_category);
?>
</ul>

</div>
<!-- /Tab Content: <?php WS_Form_Common::echo_esc_html($template_category->label); ?> -->
<?php

	}
?>

</div>
<!-- /Template -->

<!-- Loading -->
<div id="wsf-template-add-loading" class="wsf-popup-progress">
	<div class="wsf-popup-progress-backdrop"></div>
	<div class="wsf-popup-progress-inner"><img src="<?php WS_Form_Common::echo_esc_attr(sprintf('%sadmin/images/loader.gif', WS_FORM_PLUGIN_DIR_URL)); ?>" class="wsf-responsive" width="256" height="256" alt="<?php esc_attr_e('Your style is being created...', 'ws-form'); ?>" /><p><?php esc_html_e('Your style is being created...', 'ws-form'); ?></p></div>
</div>
<!-- /Loading -->

<!-- WS Form - Modal -->
<div id="wsf-template-add-modal-backdrop" class="wsf-modal-backdrop" style="display: none;"></div>

<div id="wsf-template-add-modal" class="wsf-modal" style="display: none; margin-left: -200px; margin-top: -100px; width: 400px;">

<div id="wsf-template-add">

<!-- WS Form - Modal - Header -->
<div class="wsf-modal-title"><?php

	WS_Form_Common::echo_get_admin_icon('#002e5f', false);	// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

?><h2></h2></div>
<div class="wsf-modal-close" data-action="wsf-close" title="<?php esc_attr_e('Close', 'ws-form'); ?>"></div>
<!-- /WS Form - Modal - Header -->

<!-- WS Form - Modal - Content -->
<div class="wsf-modal-content">

<form id="wsf-template-add-modal-form" action="<?php WS_Form_Common::echo_esc_attr(WS_Form_Common::get_admin_url()); ?>" method="post"></form>

</div>
<!-- /WS Form - Modal - Content -->

<!-- WS Form - Modal - Buttons -->
<div class="wsf-modal-buttons">

<div id="wsf-modal-buttons-cancel">
<a data-action="wsf-close"><?php esc_html_e('Cancel', 'ws-form'); ?></a>
</div>

<div id="wsf-modal-buttons-create">
<button class="button button-primary" data-action="wsf-template-add-modal-submit"><?php esc_html_e('Create', 'ws-form'); ?></button>
</div>

</div>
<!-- /WS Form - Modal - Buttons -->

</div>

</div>
<!-- /WS Form - Modal -->

<!-- Form Actions -->
<form action="<?php WS_Form_Common::echo_esc_attr(WS_Form_Common::get_admin_url()); ?>" id="ws-style-action-do" method="post">
<input type="hidden" name="_wpnonce" value="<?php WS_Form_Common::echo_esc_attr(wp_create_nonce('wp_rest')); ?>">
<?php wp_nonce_field(WS_FORM_POST_NONCE_ACTION_NAME, WS_FORM_POST_NONCE_FIELD_NAME); ?>
<input type="hidden" name="page" value="ws-form-style">
<input type="hidden" id="ws-style-action" name="action" value="">
<input type="hidden" id="ws-style-id" name="id" value="">
<input type="hidden" id="ws-style-action-id" name="action_id" value="">
<input type="hidden" id="ws-style-list-id" name="list_id" value="">
</form>
<!-- /Form Actions -->

<script>

	(function($) {

		'use strict';

		// On load
		$(function() {

			// Init template functionality
			var wsf_obj = new $.WS_Form();

			wsf_obj.init_partial();
			wsf_obj.tooltips();
			wsf_obj.template_style();
		});

	})(jQuery);

</script>

</div>
