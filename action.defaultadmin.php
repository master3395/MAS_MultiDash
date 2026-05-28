<?php
if (!defined('CMS_VERSION')) {
    exit;
}

if (!$this->VisibleToAdminUser()) {
    return $this->DisplayErrorPage($id, $params, $returnid, $this->Lang('accessdenied'));
}

if (isset($params['hidedonationssubmit'])) {
    $this->SetPreference('hidedonationstab', $this->GetVersion());
}

if (!empty($params['msg'])) {
    echo $this->ShowMessage($this->Lang($params['msg']));
}

$activetab = '';
if (isset($params['activetab'])) {
    $activetab = (string) $params['activetab'];
}

require_once dirname(__DIR__) . '/MAS_Common/lib/mas_admin_ui.php';
$smarty->assign('mod', $this);
Mas_Admin_Ui::assignBranding($this, $smarty);

require_once cms_join_path($this->GetModulePath(), 'lib', 'class.MasMdRealtimeHub.php');
$this->LoadMasClass('MasMdRealtimeHub');

$apiUrl = str_replace('&amp;', '&', $this->GetApiBaseUrl($id, $returnid));
$streamUrl = str_replace('&amp;', '&', $this->GetStreamUrl($id, $returnid));
$pollMs = (int) $this->GetPreference('poll_interval_ms', '3000');
$transport = (string) $this->GetPreference('transport_mode', 'auto');
$jsUrl = Mas_Admin_Ui::versionedAssetUrl($this, 'js/mas-md-admin.js');
$cssUrl = Mas_Admin_Ui::versionedAssetUrl($this, 'css/mas-md-admin.css');

$smarty->assign('mas_md_api_url', $apiUrl);
$smarty->assign('mas_md_stream_url', $streamUrl);
$smarty->assign('mas_md_poll_ms', $pollMs);
$smarty->assign('mas_md_transport', $transport);
$smarty->assign('mas_md_js_url', $jsUrl);
$smarty->assign('mas_md_css_url', $cssUrl);
$smarty->assign('mas_md_can_manage', $this->CanManage() ? 1 : 0);

echo $this->StartTabHeaders();
echo $this->SetTabHeader('settings', $this->Lang('settings'), ($activetab === '' || $activetab === 'settings'));
echo $this->SetTabHeader('dashboard', $this->Lang('tab_dashboard'), ($activetab === 'dashboard'));
echo $this->SetTabHeader('filemanager', $this->Lang('tab_filemanager'), ($activetab === 'filemanager'));
echo $this->SetTabHeader('adminsettings', $this->Lang('adminsettings'), ($activetab === 'adminsettings'));
if ($this->ShowDonationsTab()) {
    echo $this->SetTabHeader('donations', $this->Lang('donations_tab'), ($activetab === 'donations'));
}
echo $this->EndTabHeaders();
echo $this->StartTabContent();

echo $this->StartTab('settings');
$smarty->assign('start_form', $this->CreateFormStart($id, 'admin_save', $returnid));
$smarty->assign('end_form', $this->CreateFormEnd());
$smarty->assign('input_enabled', $this->CreateInputCheckbox($id, 'enabled', '1', $this->GetPreference('enabled', '1')));
$smarty->assign('input_transport', $this->CreateInputDropdown($id, 'transport_mode', array(
    'auto' => $this->Lang('transport_auto'),
    'poll' => $this->Lang('transport_poll'),
    'sse' => $this->Lang('transport_sse'),
), -1, $this->GetPreference('transport_mode', 'auto')));
$smarty->assign('input_poll_ms', $this->CreateInputText($id, 'poll_interval_ms', $this->GetPreference('poll_interval_ms', '3000'), 8, 8));
$smarty->assign('input_idle', $this->CreateInputText($id, 'idle_timeout_sec', $this->GetPreference('idle_timeout_sec', '90'), 6, 6));
$smarty->assign('input_max_bytes', $this->CreateInputText($id, 'max_file_bytes', $this->GetPreference('max_file_bytes', '524288'), 12, 12));
$smarty->assign('input_cms_roots', $this->CreateInputText($id, 'cms_upload_roots', $this->GetPreference('cms_upload_roots', ''), 80, 255));
$smarty->assign('input_allow_php', $this->CreateInputCheckbox($id, 'allow_php_edit', '1', $this->GetPreference('allow_php_edit', '0')));
$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', $this->Lang('submit')));
echo $this->ProcessTemplate('settings.tpl');
echo $this->EndTab();

echo $this->StartTab('dashboard');
echo $this->ProcessTemplate('dashboard.tpl');
echo $this->EndTab();

echo $this->StartTab('filemanager');
echo $this->ProcessTemplate('filemanager.tpl');
echo $this->EndTab();

echo $this->StartTab('adminsettings');
include dirname(__FILE__) . '/function.admin_settings.php';
echo $this->EndTab();

if ($this->ShowDonationsTab()) {
    echo $this->StartTab('donations');
    include dirname(__FILE__) . '/function.donations.php';
    echo $this->EndTab();
}

echo $this->EndTabContent();

if ($jsUrl !== '') {
    echo '<script src="' . htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8') . '"></script>';
}
if ($cssUrl !== '') {
    echo '<link rel="stylesheet" href="' . htmlspecialchars($cssUrl, ENT_QUOTES, 'UTF-8') . '">';
}
