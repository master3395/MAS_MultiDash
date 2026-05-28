<?php
if (!defined('CMS_VERSION')) {
    exit;
}

$oldversion = $oldversion ?? '0.0.0';

require_once dirname(__DIR__) . '/MAS_Common/lib/mas_admin_ui.php';
Mas_Admin_Ui::ensureIconGif($this);
Mas_Admin_Ui::ensureBanner($this);

$sandbox = cms_join_path($this->GetModulePath(), 'data', 'files');
if (!is_dir($sandbox)) {
    @mkdir($sandbox, 0755, true);
}

$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('upgraded', $this->GetVersion()));
