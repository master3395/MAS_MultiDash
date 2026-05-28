<?php
if (!defined('CMS_VERSION')) {
    exit;
}

class MasMdDocumentStore
{
    /** @var MAS_MultiDash */
    private $mod;

    /** @var MasMdSecurity */
    private $security;

    public function __construct(MAS_MultiDash $mod, MasMdSecurity $security)
    {
        $this->mod = $mod;
        $this->security = $security;
    }

    private function table()
    {
        return CMS_DB_PREFIX . 'mod_mas_md_documents';
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getByKey($docKey)
    {
        $db = $this->mod->GetDb();
        $row = $db->GetRow(
            'SELECT doc_key, revision, content, mime, updated_by, updated_at FROM ' . $this->table() . ' WHERE doc_key = ?',
            array($docKey)
        );
        if (!$row) {
            return null;
        }
        return array(
            'doc_key' => (string) $row['doc_key'],
            'revision' => (int) $row['revision'],
            'content' => (string) $row['content'],
            'mime' => (string) $row['mime'],
            'updated_by' => (int) $row['updated_by'],
            'updated_at' => (int) $row['updated_at'],
        );
    }

    public function ensureFromFile($docKey, $content, $mime = 'text/plain')
    {
        $existing = $this->getByKey($docKey);
        if ($existing) {
            return $existing;
        }
        $db = $this->mod->GetDb();
        $now = time();
        $db->Execute(
            'INSERT INTO ' . $this->table() . ' (doc_key, revision, content, mime, updated_by, updated_at) VALUES (?,?,?,?,?,?)',
            array($docKey, 1, $content, $mime, $this->security->getAdminUserId(), $now)
        );
        return $this->getByKey($docKey);
    }

    /**
     * @return array{ok:bool,document?:array,conflict?:array}
     */
    public function save($docKey, $baseRevision, $content, $mime = 'text/plain')
    {
        $db = $this->mod->GetDb();
        $row = $this->getByKey($docKey);
        if (!$row) {
            $db->Execute(
                'INSERT INTO ' . $this->table() . ' (doc_key, revision, content, mime, updated_by, updated_at) VALUES (?,?,?,?,?,?)',
                array($docKey, 1, $content, $mime, $this->security->getAdminUserId(), time())
            );
            return array('ok' => true, 'document' => $this->getByKey($docKey));
        }
        if ((int) $baseRevision !== (int) $row['revision']) {
            return array('ok' => false, 'conflict' => $row);
        }
        $newRev = (int) $row['revision'] + 1;
        $now = time();
        $db->Execute(
            'UPDATE ' . $this->table() . ' SET revision = ?, content = ?, mime = ?, updated_by = ?, updated_at = ? WHERE doc_key = ?',
            array($newRev, $content, $mime, $this->security->getAdminUserId(), $now, $docKey)
        );
        return array('ok' => true, 'document' => $this->getByKey($docKey));
    }
}
