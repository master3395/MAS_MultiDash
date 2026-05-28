<?php
if (!defined('CMS_VERSION')) {
    exit;
}

if (!$this->VisibleToAdminUser()) {
    return $this->DisplayErrorPage($id, $params, $returnid, $this->Lang('accessdenied'));
}

if (isset($params['adminsection'])) {
    $sections = array('extensions' => true, 'content' => true, 'siteadmin' => true, 'usersgroups' => true, 'layout' => true, 'ecommerce' => true);
    $req = trim((string) $params['adminsection']);
    if (isset($sections[$req])) {
        $old = $this->GetPreference(MAS_MultiDash::PREF_ADMIN_SECTION, 'extensions');
        $this->SetPreference(MAS_MultiDash::PREF_ADMIN_SECTION, $req);
        if ($old !== $req) {
            $gCms = cmsms();
            if ($gCms && method_exists($gCms, 'ClearAdminMenuCache')) {
                $gCms->ClearAdminMenuCache();
            }
        }
    }
}

if (isset($params['showdonationstab']) && $params['showdonationstab'] === '1') {
    $this->RemovePreference('hidedonationstab');
} else {
    $this->SetPreference('hidedonationstab', $this->GetVersion());
}

$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('prefsupdated'));
$this->Redirect($id, 'defaultadmin', $returnid, array('msg' => 'settingsupdated', 'activetab' => 'adminsettings'));
