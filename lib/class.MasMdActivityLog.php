<?php
if (!defined('CMS_VERSION')) {
    exit;
}

class MasMdActivityLog
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
        return CMS_DB_PREFIX . 'mod_mas_md_events';
    }

    public function log($eventType, $summary, $meta = array())
    {
        $db = $this->mod->GetDb();
        $id = (int) $db->GenId(CMS_DB_PREFIX . 'mod_mas_md_events_seq');
        $db->Execute(
            'INSERT INTO ' . $this->table() . ' (id, ts, admin_user_id, display_name, event_type, summary, meta_json) VALUES (?,?,?,?,?,?,?)',
            array(
                $id,
                time(),
                $this->security->getAdminUserId(),
                $this->security->getDisplayName(),
                (string) $eventType,
                (string) $summary,
                json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            )
        );
        return $id;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listSince($sinceId, $limit = 50)
    {
        $db = $this->mod->GetDb();
        $limit = max(1, min(100, (int) $limit));
        $sinceId = (int) $sinceId;
        if ($sinceId > 0) {
            $rows = $db->GetArray(
                'SELECT id, ts, admin_user_id, display_name, event_type, summary, meta_json FROM ' . $this->table() . ' WHERE id > ? ORDER BY id ASC LIMIT ' . $limit,
                array($sinceId)
            );
        } else {
            $rows = $db->GetArray(
                'SELECT id, ts, admin_user_id, display_name, event_type, summary, meta_json FROM ' . $this->table() . ' ORDER BY id DESC LIMIT ' . $limit
            );
            if (is_array($rows)) {
                $rows = array_reverse($rows);
            }
        }
        if (!is_array($rows)) {
            return array();
        }
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id' => (int) $r['id'],
                'ts' => (int) $r['ts'],
                'admin_user_id' => (int) $r['admin_user_id'],
                'display_name' => (string) $r['display_name'],
                'event_type' => (string) $r['event_type'],
                'summary' => (string) $r['summary'],
                'meta' => json_decode((string) ($r['meta_json'] ?? '{}'), true) ?: array(),
            );
        }
        return $out;
    }
}
