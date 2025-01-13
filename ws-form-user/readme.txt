=== WS Form PRO - User Management ===
Contributors: westguard
Requires at least: 5.3
Tested up to: 6.5
Stable tag: trunk
Requires PHP: 5.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

User Management add-on for WS Form PRO.

== Description ==

User Management add-on for WS Form PRO.

== Installation ==

For help installing this plugin, please see our [Installation](https://wsform.com/knowledgebase/installation/?utm_source=wp_plugins&utm_medium=readme) knowledgebase article.

== Changelog ==

= 1.6.4 - 11/11/2024 =
* Bug Fix: Fixed load_plugin_textdomain warning

= 1.6.3 - 06/03/2024 =
* Bug Fix: Toolset user meta updates

= 1.6.2 - 05/30/2024 =
* Bug Fix: JetEngine field mapping context fix

= 1.6.1 - 05/21/2024 =
* Bug Fix: User ID variable in third party classes

= 1.6.0 - 05/14/2024 =
* Added: ACPT integration
* Added: Restructured custom field integrations to improve performance

= 1.5.17 - 04/17/2024 =
* Changed: Period added to end of error messages to match WordPress error conventions
* Bug Fix: Translatable strings for password matching

= 1.5.16 - 11/28/2023 =
* Bug Fix: Pods escaping

= 1.5.15 - 11/17/2023 =
* Added: Filter hooks for login and lost password error handling; wsf_action_user_signon_error and wsf_action_user_lostpassword_error

= 1.5.14 - 09/23/2023 =
* Bug Fix: Limited HTML entity decoding to strings

= 1.5.13 - 09/22/2023 =
* Added: Improved handling of user meta data that is already HTML encoded

= 1.5.12 - 06/13/2023 =
* Bug Fix: Fix to acf_ws_form_field_value_to_acf_meta_value call

= 1.5.11 - 06/05/2023 =
* Bug Fix: JetEngine config issue

= 1.5.10 - 06/05/2023 =
* Added: Support for JetEngine relationships

= 1.5.9 - 05/24/2023 =
* Added: Improved handling of various ACF field types

= 1.5.8 - 05/04/2023 =
* Bug Fix: Additional data format fixes for Meta Box file and image field types

= 1.5.7 - 05/03/2023 =
* Bug Fix: Data format for Meta Box file and image field types

= 1.5.6 - 03/10/2023 =
* Added: Forward slashes now escaped in user and custom field data to follow method used by WordPress

= 1.5.5 - 03/28/2023 =
* Changed: Removed error settings as these are now controlled in Form Settings

= 1.5.4 - 03/24/2023 =
* Bug Fix: JetEngine media field data when set to both format

= 1.5.3 - 03/23/2023 =
* Bug Fix: JetEngine media and gallery field data formats

= 1.5.2 - 02/28/2023 =
* Bug Fix: JetEngine populate sidebar field mapping conditional logic
* Bug Fix: JetEngine user context when retrieving field settings

= 1.5.1 - 02/15/2023 =
* Bug Fix: Fix to acf_ws_form_field_value_to_acf_meta_value function parameters

= 1.5.0 - 01/22/2023 =
* Added: JetEngine support

= 1.4.8 - 11/25/2022 =
* Added: Custom field configuration no longer loading on client side to improve performance

= 1.4.7 - 10/11/22 =
* Added: Support for removing file uploads in custom field plugin groups and repeaters

= 1.4.6 - 09/01/22 =
* Bug Fix: Parameters passed to post_process function

= 1.4.5 - 08/27/22 =
* Added: Added support for removing file uploads

= 1.4.4 - 08/14/22 =
* Added: Send user notification setting for the register method

= 1.4.3 - 06/03/22 =
* Bug Fix: User role select population

= 1.4.2 - 11/20/21 =
* Added: Message and redirect added to logon template

= 1.4.1 - 10/13/21 =
* Added: Moved main class to include

= 1.4.0 - 10/12/21 =
* Added: Toolset support

= 1.3.2 =
* Bug Fix: Minor typo fix

= 1.3.1 =
* Added: Languages folder

= 1.3.0 =
* Added: Pods support

= 1.2.1 =
* Added: Fixed ACF selector for get function

= 1.2.0 =
* Added: Meta Box support
* Added: ACF Custom Database Tables plugin support

= 1.1.16 =
* Added: wp_set_current_user after wp_signon added

= 1.1.15 =
* Added: ACF field validation

= 1.1.14 =
* Bug Fix: get_password_reset_key error handling

= 1.1.13 =
* Added: WooCommerce billing and shipping fields

= 1.1.12 =
* Bug Fix: ACF meta data for images

= 1.1.11 =
* Bug Fix: ACF field mappings on get

= 1.1.10 =
* Bug Fix: File fields in repeaters

= 1.1.9 =
* Added: Custom meta mapping

= 1.1.8 =
* Bug Fix: ACF boolean field

= 1.1.7 =
* Added: Support for ACF gallery and Google map field

= 1.1.6 =
* Added: Improved handling of empty array values to ACF

= 1.1.5 =
* Bug Fix: Images in repeaters sometimes did not map due to inconsistent parent value in ACF object data

= 1.1.4 =
* Changed: REST endpoint initialization for WordPress 5.5
* Added: ACF image field support
* Added: Unique field mapping
* Bug Fix: ACF true/false field type
* Bug Fix: ACF mapping

= 1.1.3 =
* Added: Support for ACF group/repeater widths
* Added: Support for 'User Form' group rule (Add / Edit)

= 1.1.2 =
* Bug Fix: ACF bug fixes for repeater and group form data tab

= 1.1.1 =
* Added: Expose on added users

= 1.1.0 =
* Added: Full ACF support

= 1.0.13 =
* Bug Fix: List fields fix

= 1.0.12 =
* Added: ACF true/false for data grids

= 1.0.11 =
* Change: ACF support

= 1.0.10 =
* Change: Array handling

= 1.0.9 =
* Change: Regular field mapping hidden in auto populate

= 1.0.8 =
* Added: Auto populate for edit profile
* Added: Addition user fields

= 1.0.7 =
* Added: Stacked actions, e.g. register then login

= 1.0.6 =
* Added: Logout method

= 1.0.5 =
* Bug fix: Password strength meter removed from login password

= 1.0.4 =
* Added: Password creation
* Bug fix: Reset password fix

= 1.0.3 =
* Bug fix: REST API

= 1.0.2 =
* Bug fix: WordPress 5.1 fix.

= 1.0.1 =
* Bug fix: Update system.

= 1.0.0 =
* Initial release.
