<?php
if (!defined('CMS_VERSION')) {
    exit;
}

$db = $this->GetDb();
$dict = NewDataDictionary($db);
$tables = array(
    'mod_mas_md_presence',
    'mod_mas_md_events',
    'mod_mas_md_documents',
    'mod_mas_md_locks',
);
foreach ($tables as $t) {
    $sqlarray = $dict->DropTableSQL(CMS_DB_PREFIX . $t);
    if (is_array($sqlarray)) {
        $dict->ExecuteSQLArray($sqlarray);
    }
}
$db->DropSequence(CMS_DB_PREFIX . 'mod_mas_md_events_seq');

$this->RemovePermission(MAS_MultiDash::PERM_USE);
$this->RemovePermission(MAS_MultiDash::PERM_MANAGE);

$this->Audit(0, $this->Lang('friendlyname'), $this->Lang('uninstalled'));
