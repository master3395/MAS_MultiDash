<?php
if (!defined('CMS_VERSION')) {
    exit;
}

$this->CreatePermission(MAS_MultiDash::PERM_USE, 'Use MAS MultiDash collaborative admin');
$this->CreatePermission(MAS_MultiDash::PERM_MANAGE, 'Manage MAS MultiDash settings and file roots');

$db = $this->GetDb();
$dict = NewDataDictionary($db);
$taboptarray = array('mysql' => 'TYPE=MyISAM');

$flds = '
    session_id C(64) KEY NOTNULL,
    admin_user_id I NOTNULL DEFAULT 0,
    display_name C(128) NOTNULL,
    room_id C(128) NOTNULL,
    status C(16) NOTNULL,
    typing_until I NOTNULL DEFAULT 0,
    payload_json X,
    last_seen I NOTNULL
';
$sqlarray = $dict->CreateTableSQL(CMS_DB_PREFIX . 'mod_mas_md_presence', $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds2 = '
    id I KEY NOTNULL,
    ts I NOTNULL,
    admin_user_id I NOTNULL DEFAULT 0,
    display_name C(128) NOTNULL,
    event_type C(32) NOTNULL,
    summary C(255) NOTNULL,
    meta_json X
';
$sqlarray2 = $dict->CreateTableSQL(CMS_DB_PREFIX . 'mod_mas_md_events', $flds2, $taboptarray);
$dict->ExecuteSQLArray($sqlarray2);
$db->CreateSequence(CMS_DB_PREFIX . 'mod_mas_md_events_seq');

$flds3 = '
    doc_key C(128) KEY NOTNULL,
    revision I NOTNULL DEFAULT 1,
    content XL,
    mime C(64) NOTNULL,
    updated_by I NOTNULL DEFAULT 0,
    updated_at I NOTNULL
';
$sqlarray3 = $dict->CreateTableSQL(CMS_DB_PREFIX . 'mod_mas_md_documents', $flds3, $taboptarray);
$dict->ExecuteSQLArray($sqlarray3);

$flds4 = '
    doc_key C(128) KEY NOTNULL,
    locked_by I NOTNULL DEFAULT 0,
    expires_at I NOTNULL
';
$sqlarray4 = $dict->CreateTableSQL(CMS_DB_PREFIX . 'mod_mas_md_locks', $flds4, $taboptarray);
$dict->ExecuteSQLArray($sqlarray4);

$this->SetPreference('enabled', '1');
$this->SetPreference(MAS_MultiDash::PREF_ADMIN_SECTION, 'extensions');
$this->SetPreference('hidedonationstab', '');
$this->SetPreference('idle_timeout_sec', '90');
$this->SetPreference('typing_ttl_sec', '5');
$this->SetPreference('poll_interval_ms', '3000');
$this->SetPreference('transport_mode', 'auto');
$this->SetPreference('cms_upload_roots', '');
$this->SetPreference('max_file_bytes', '524288');
$this->SetPreference('allow_php_edit', '0');

$sandbox = cms_join_path($this->GetModulePath(), 'data', 'files');
if (!is_dir($sandbox)) {
    @mkdir($sandbox, 0755, true);
}
$idx = cms_join_path($sandbox, 'index.html');
if (!is_file($idx)) {
    @file_put_contents($idx, '<html><body></body></html>');
}

require_once dirname(__DIR__) . '/MAS_Common/lib/mas_admin_ui.php';
Mas_Admin_Ui::ensureIconGif($this);
Mas_Admin_Ui::ensureBanner($this);

$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('installed', $this->GetVersion()));
