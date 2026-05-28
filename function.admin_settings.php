<?php
if (!defined('CMS_VERSION')) {
    exit;
}

require_once dirname(__DIR__) . '/MAS_Common/lib/mas_admin_ui.php';
$smarty->assign('module', $this);
$smarty->assign('mod', $this);
Mas_Admin_Ui::assignBranding($this, $smarty);

$donationsHidden = ($this->GetPreference('hidedonationstab') == $this->GetVersion());
$smarty->assign('formstart', $this->CreateFormStart($id, 'admin_settings_save', $returnid));
$smarty->assign('formend', $this->CreateFormEnd());
$adminsections = array(
    $this->Lang('extensions') => 'extensions',
    $this->Lang('content') => 'content',
    $this->Lang('siteadmin') => 'siteadmin',
    $this->Lang('usersgroups') => 'usersgroups',
    $this->Lang('layout') => 'layout',
    $this->Lang('ecommerce') => 'ecommerce',
);
$smarty->assign('adminsection_label', $this->Lang('adminsection'));
$smarty->assign('adminsection_help', $this->Lang('adminsectionhelp'));
$smarty->assign('adminsection_dropdown', $this->CreateInputDropdown($id, 'adminsection', $adminsections, -1, $this->GetPreference(MAS_MultiDash::PREF_ADMIN_SECTION, 'extensions')));
$smarty->assign('showdonationstab_checkbox', $this->CreateInputCheckbox($id, 'showdonationstab', '1', !$donationsHidden));
$smarty->assign('showdonationstab_label', $this->Lang('show_donations_tab'));
$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', $this->Lang('submit')));

echo $this->ProcessTemplate('admin_settings.tpl');
