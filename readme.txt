=== WS Form PRO ===
Contributors: westguard
Requires at least: 5.3
Tested up to: 6.7
Requires PHP: 5.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WS Form PRO allows you to build faster, effective, user friendly WordPress forms. Build forms in a single click or use the unique drag and drop editor to create forms in seconds.

== Description ==

= Build Better WordPress Forms =

WS Form lets you create any form for your website in seconds using our unique drag and drop editor. Build everything from simple contact us forms to complex multi-page application forms.

== Installation ==

For help installing WS Form, please see our [Installation](https://wsform.com/knowledgebase/installation/?utm_source=wp_plugins&utm_medium=readme) knowledge base article.

== Changelog ==

= 1.10.8 - 01/08/2024 =
* Added: Inside label position behavior setting to styler
* Added: ACF fields now exclude option assign fields by default
* Added: Message styling added to LITE edition
* Bug Fix: Removed PHP warning in version prior to 8.0 related to style template SVG's
* Bug Fix: Submission export for LITE edition

= 1.10.7 - 01/07/2025 =
* Added: Improved compatibility of new form components with visual builders

= 1.10.6 - 01/06/2025 =
* Bug Fix: Customize publish button issue when wsf_styler_enabled filter set to true

= 1.10.5 - 01/05/2025 =
* Added: Improvements to style preview templates
* Changed: Date/time field day padding reduced to improve size on mobile
* Changed: CSS value sanitization now accepts zero without unit
* Bug Fix: WooCommerce form locking
* Bug Fix: wsf_styler_enabled filter hook
* Bug Fix: Checkbox and radio disabled opacity

= 1.10.4 - 01/04/2025 =
* Bug Fix: Remove width setting on buttons

= 1.10.3 - 01/02/2025 =
* Added: InstaWP staging install support

= 1.10.2 - 01/02/2025 =
* Bug Fix: Removed descending index for style table to avoid incompatibility issues with some storage engines

= 1.10.1 - 01/02/2025 =
* Bug Fix: Activation debug disabled

= 1.10.0 - 01/02/2025 =
* Important installation notes: https://wsform.com/knowledgebase/upgrade-notes-for-1-10-x/
* Added: New form styles (See: https://wsform.com/knowledgebase/styles/)
* Added: New form styler (See: https://wsform.com/knowledgebase/styler/)
* Added: Accessibility improvement: autocomplete attributes now default for password, URL, email and phone fields
* Added: Accessibility improvement: autocomplete attributes completed in form and section templates
* Added: Accessibility improvement: Checkbox field now defaults to label on
* Added: Accessibility improvement: Removed character / word count help text from textarea by default
* Added: Accessibility improvement: URL field now contains https:// placeholder
* Added: Accessibility improvement: Color contrast improvements throughout
* Added: Accessibility improvement: Coloris color picker
* Added: Accessibility improvement: ARIA label on section fieldsets
* Added: Accessibility improvement: Dark / light color themes
* Added: Auto grow setting for Text Area field
* Added: Checkbox and radio styling setting (button, switch, swatch and image)
* Added: Email allow / deny feature now processed client-side
* Added: Improved Dutch translations
* Added: Increased generated password size to 24 characters
* Added: Form saved on field clone
* Added: Support for j.n.Y date format
* Added: Submission export performance improvements
* Added: Descriptive form limit error message on form submit
* Added: Select2 upgraded to version 4.0.13
* Added: WordPress 6.7 compatibility testing
* Added: Author ID added as column in Posts data source
* Added: Points allocation form template
* Added: Repeater level custom attachment title, excerpt, content and alt text
* Added: Updated logo in conversation template
* Added: Postmark API error handling in Send Email action
* Added: #query_var default value parameter
* Added: Limit submissions per logged in user
* Added: Invalid captcha responses no longer throw a PHP exception
* Added: Phone field ITI validation triggered on paste event
* Added: Additional checks when determining capabilities for conditional logic
* Bug Fix: Loader functionality on form reload
* Bug Fix: Translation issue related to widgets_init / load_plugin_textdomain
* Bug Fix: Escaping when using #text
* Bug Fix: Price radio invalid feedback text location
* Bug Fix: Order by in terms data source for term order
* Bug Fix: Form calc clean method for hidden fields
