<?php
if (!function_exists('cmsms')) {
    exit;
}

// Use require (not require_once) so defaults merge into this module's $lang every time.
require dirname(dirname(__DIR__)) . '/MAS_Common/lang/mas_docs_defaults_en_US.php';

$lang['donations_tab'] = 'Donations';
$lang['tab_donations'] = 'Donations';
$lang['donate_btn'] = 'Donate';
$lang['donations_sponsor_logo_alt'] = 'News Targeted logo';
$lang['donations_sponsor_link'] = 'Visit NewsTargeted';
$lang['show_donations_tab'] = 'Show Donations tab';
$lang['show_donations_tab_help'] = 'When turned off, the Donations tab is hidden until you enable it again in Admin Settings.';
$lang['donations_tab_hidden'] = 'Donations tab has been hidden. You can show it again in Admin Settings.';
$lang['sponsors'] = 'Current sponsors, thank you for your support!';
$lang['donationstext'] = 'A lot of time and effort has been put into creating this module. Please consider a small donation using the button below, especially if you use this module in a commercial context. Thank you!';
$lang['hidedonationssubmit'] = 'Hide donations tab';
$lang['adminsection'] = 'Admin menu section';
$lang['adminsectionhelp'] = 'Choose where MAS MultiDash appears in the CMSMS admin menu.';
$lang['extensions'] = 'Extensions';
$lang['content'] = 'Content';
$lang['siteadmin'] = 'Site Admin';
$lang['usersgroups'] = 'Users and Groups';
$lang['layout'] = 'Layout';
$lang['ecommerce'] = 'E-Commerce';
$lang['adminsettings'] = 'Admin Settings';
$lang['settings'] = 'Settings';
$lang['submit'] = 'Save';
$lang['save'] = 'Save';
$lang['settingsupdated'] = 'Settings updated.';
$lang['prefsupdated'] = 'Preferences updated.';

$lang['friendlyname'] = 'MAS MultiDash';
$lang['moddescription'] = 'Real-time collaborative admin dashboard with live presence, typing indicators, activity feed, and collaborative file editing.';
$lang['postinstall'] = 'Grant "Use MAS_MultiDash" and "Manage MAS_MultiDash" to the appropriate admin groups.';
$lang['postuninstall'] = 'MAS MultiDash has been removed.';
$lang['really_uninstall'] = 'Remove MAS MultiDash and all collaborative session data?';
$lang['uninstalled'] = 'Module uninstalled.';
$lang['installed'] = 'Module version %s installed.';
$lang['upgraded'] = 'Module version %s upgraded.';
$lang['accessdenied'] = 'Access denied. Check your permissions.';
$lang['accessdenied_manage'] = 'Manage permission required for this action.';
$lang['rate_limit'] = 'Too many requests. Please wait a moment.';
$lang['api_error'] = 'Request failed.';
$lang['unknown_action'] = 'Unknown API action.';
$lang['module_disabled'] = 'MAS MultiDash is disabled in module settings.';
$lang['guest_admin'] = 'Guest';
$lang['admin_user'] = 'Admin';

$lang['tab_dashboard'] = 'Dashboard';
$lang['tab_filemanager'] = 'File manager';
$lang['settings_intro'] = 'Configure real-time transport, idle detection, and allowed CMS upload paths.';
$lang['pref_enabled'] = 'Enable collaborative features';
$lang['pref_transport'] = 'Transport mode';
$lang['transport_auto'] = 'Auto (SSE with poll fallback)';
$lang['transport_poll'] = 'Polling only';
$lang['transport_sse'] = 'SSE only';
$lang['pref_poll_ms'] = 'Poll interval (ms)';
$lang['pref_idle'] = 'Idle timeout (seconds)';
$lang['pref_max_bytes'] = 'Max file size (bytes)';
$lang['pref_cms_roots'] = 'CMS upload subfolders (comma-separated, relative to uploads)';
$lang['pref_cms_roots_help'] = 'Example: MAS_MultiDash, images/docs. Only paths under the site uploads directory are allowed.';
$lang['pref_allow_php'] = 'Allow editing .php files (not recommended)';

$lang['live_admins'] = 'Admins online';
$lang['activity_feed'] = 'Live activity';
$lang['scratch_pad'] = 'Collaborative scratch pad';
$lang['scratch_hint'] = 'Create a shared note visible to other admins on this dashboard.';
$lang['scratch_new'] = 'New scratch note';
$lang['editor_placeholder'] = 'Start typing...';
$lang['editor_title'] = 'Editor';
$lang['file_root'] = 'Root folder';
$lang['root_sandbox'] = 'Module sandbox';
$lang['root_cms'] = 'CMS uploads';

$lang['event_join'] = 'Joined collaborative session';
$lang['event_leave'] = 'Left collaborative session';
$lang['event_file_open'] = 'Opened a file';
$lang['event_save'] = 'Saved a document';
$lang['event_conflict'] = 'Save conflict detected';
$lang['event_scratch'] = 'Created scratch note';

$lang['help_realtime'] = 'Realtime transport';
$lang['help_realtime_tab'] = 'Realtime and troubleshooting';
$lang['help_realtime_body'] = '<p>MAS MultiDash uses HTTP polling in the CMS admin by default (most reliable on OpenLiteSpeed and moduleinterface.php).</p><p>Optional SSE mode is available via Settings if your server supports bare module output. If live updates stall, use Polling only.</p><p>Test SSE: <code>curl -N "YOUR_STREAM_URL&amp;showtemplate=false&amp;suppressoutput=1"</code></p>';

$lang['about_module_summary'] = 'Collaborative admin presence, activity feed, and file editing for CMS Made Simple.';

$lang['changelog'] = 'See CHANGELOG.md in the module directory.';

// About / Help tab labels (explicit fallback if shared defaults were skipped earlier in the request)
$lang['help'] = 'Open Help in the Module Manager for tabbed documentation.';
$lang['help_general'] = 'General';
$lang['help_configuration'] = 'Configuration';
$lang['help_troubleshooting'] = 'Troubleshooting';
$lang['help_overview_heading'] = 'Overview';
$lang['help_quickstart_heading'] = 'Quick start';
$lang['help_quickstart_1'] = 'Install or upgrade the module from Extensions, then open its admin screen.';
$lang['help_quickstart_2'] = 'Configure options on the Settings tab and save.';
$lang['help_quickstart_3'] = 'Use the Dashboard and File manager tabs for collaborative editing.';
$lang['help_requirements_heading'] = 'System requirements';
$lang['help_requirements_cms'] = 'CMS Made Simple';
$lang['help_requirements_php'] = 'PHP';
$lang['help_configuration_body'] = 'Use the Settings and Admin settings tabs in the module admin. Preferences are stored in the CMSMS module preference store unless documented otherwise.';
$lang['help_config_admin_heading'] = 'Admin';
$lang['help_config_admin_1'] = 'Choose the admin menu section under Admin settings if the module supports it.';
$lang['help_config_admin_2'] = 'Grant module permissions to the correct admin groups under Users and Groups.';
$lang['help_ts_1'] = 'Clear CMSMS template and cache after changing module settings.';
$lang['help_ts_2'] = 'Check the admin audit log and PHP error log for failures.';
$lang['help_ts_3'] = 'Confirm file permissions and ownership match your host requirements.';
$lang['help_ts_support_body'] = 'For site-specific support contact';
$lang['help_credits_heading'] = 'Credits';
$lang['help_credits_body'] = 'Developed by';
$lang['about_tab_module'] = 'Module';
$lang['about_tab_compat'] = 'Compatibility';
$lang['about_tab_summary'] = 'Summary';
$lang['about_tab_changelog'] = 'Change log';
$lang['about_label_name'] = 'Name';
$lang['about_label_version'] = 'Version';
$lang['about_label_author'] = 'Author';
$lang['about_label_email'] = 'Email';
$lang['about_label_url'] = 'Website';
$lang['about_label_license'] = 'License';
$lang['about_license_value'] = 'MIT';
$lang['about_min_cms'] = 'Minimum CMS Made Simple';
$lang['about_min_php'] = 'Minimum PHP';
$lang['about_dependencies_heading'] = 'Module dependencies';
$lang['about_dependencies_none'] = 'No module dependencies declared.';
