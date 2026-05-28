<?php
if (!defined('CMS_VERSION')) {
    exit;
}

class MasMdPresence
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
        return CMS_DB_PREFIX . 'mod_mas_md_presence';
    }

    public function heartbeat($roomId, $typing, $payloadJson)
    {
        $db = $this->mod->GetDb();
        $sid = $this->security->getSessionId();
        $uid = $this->security->getAdminUserId();
        $name = $this->security->getDisplayName();
        $now = time();
        $idleSec = (int) $this->mod->GetPreference('idle_timeout_sec', '90');
        $typingUntil = 0;
        if ($typing) {
            $typingUntil = $now + (int) $this->mod->GetPreference('typing_ttl_sec', '5');
        }
        $status = 'online';
        $row = $db->GetRow(
            'SELECT last_seen FROM ' . $this->table() . ' WHERE session_id = ?',
            array($sid)
        );
        if ($row && isset($row['last_seen']) && ($now - (int) $row['last_seen']) > $idleSec) {
            $status = 'idle';
        }
        $exists = $db->GetOne('SELECT COUNT(*) FROM ' . $this->table() . ' WHERE session_id = ?', array($sid));
        if ($exists) {
            $db->Execute(
                'UPDATE ' . $this->table() . ' SET admin_user_id = ?, display_name = ?, room_id = ?, status = ?, typing_until = ?, payload_json = ?, last_seen = ? WHERE session_id = ?',
                array($uid, $name, $roomId, $status, $typingUntil, $payloadJson, $now, $sid)
            );
        } else {
            $db->Execute(
                'INSERT INTO ' . $this->table() . ' (session_id, admin_user_id, display_name, room_id, status, typing_until, payload_json, last_seen) VALUES (?,?,?,?,?,?,?,?)',
                array($sid, $uid, $name, $roomId, $status, $typingUntil, $payloadJson, $now)
            );
        }
        $this->pruneStale($now, $idleSec * 3);
    }

    public function leave()
    {
        $db = $this->mod->GetDb();
        $sid = $this->security->getSessionId();
        $db->Execute('DELETE FROM ' . $this->table() . ' WHERE session_id = ?', array($sid));
    }

    private function pruneStale($now, $maxAge)
    {
        $db = $this->mod->GetDb();
        $cutoff = $now - $maxAge;
        $db->Execute('DELETE FROM ' . $this->table() . ' WHERE last_seen < ?', array($cutoff));
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listActive($roomId = null)
    {
        $db = $this->mod->GetDb();
        $now = time();
        $idleSec = (int) $this->mod->GetPreference('idle_timeout_sec', '90');
        $sql = 'SELECT session_id, admin_user_id, display_name, room_id, status, typing_until, payload_json, last_seen FROM ' . $this->table();
        $params = array();
        if ($roomId !== null && $roomId !== '') {
            $sql .= ' WHERE room_id = ?';
            $params[] = $roomId;
        }
        $sql .= ' ORDER BY display_name ASC';
        $rows = $db->GetArray($sql, $params);
        if (!is_array($rows)) {
            return array();
        }
        $out = array();
        foreach ($rows as $r) {
            $last = (int) ($r['last_seen'] ?? 0);
            $status = (string) ($r['status'] ?? 'online');
            if (($now - $last) > $idleSec) {
                $status = 'idle';
            }
            $typing = ((int) ($r['typing_until'] ?? 0)) > $now;
            $out[] = array(
                'session_id' => (string) $r['session_id'],
                'admin_user_id' => (int) $r['admin_user_id'],
                'display_name' => (string) $r['display_name'],
                'room_id' => (string) $r['room_id'],
                'status' => $status,
                'typing' => $typing,
                'payload' => json_decode((string) ($r['payload_json'] ?? '{}'), true) ?: array(),
                'last_seen' => $last,
                'self' => ($r['session_id'] === $this->security->getSessionId()),
            );
        }
        return $out;
    }
}
