<?php
if (!defined('CMS_VERSION')) {
    exit;
}

if (!$this->VisibleToAdminUser()) {
    return $this->DisplayErrorPage($id, $params, $returnid, $this->Lang('accessdenied'));
}

if (!$this->CanManage()) {
    $this->Redirect($id, 'defaultadmin', $returnid, array('activetab' => 'settings'));
    return;
}

$this->SetPreference('enabled', !empty($params['enabled']) ? '1' : '0');
$modes = array('auto', 'poll', 'sse');
$mode = isset($params['transport_mode']) ? (string) $params['transport_mode'] : 'auto';
if (!in_array($mode, $modes, true)) {
    $mode = 'auto';
}
$this->SetPreference('transport_mode', $mode);
$this->SetPreference('poll_interval_ms', max(1000, min(60000, (int) ($params['poll_interval_ms'] ?? 3000))));
$this->SetPreference('idle_timeout_sec', max(30, min(600, (int) ($params['idle_timeout_sec'] ?? 90))));
$this->SetPreference('max_file_bytes', max(1024, min(10485760, (int) ($params['max_file_bytes'] ?? 524288))));
$this->SetPreference('cms_upload_roots', trim((string) ($params['cms_upload_roots'] ?? '')));
$this->SetPreference('allow_php_edit', !empty($params['allow_php_edit']) ? '1' : '0');

$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('prefsupdated'));
$this->Redirect($id, 'defaultadmin', $returnid, array('msg' => 'prefsupdated', 'activetab' => 'settings'));
