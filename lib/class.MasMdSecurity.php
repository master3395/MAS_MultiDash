<?php
if (!defined('CMS_VERSION')) {
    exit;
}

class MasMdSecurity
{
    /** @var MAS_MultiDash */
    private $mod;

    /** @var array<string,int> */
    private static $rateBuckets = array();

    public function __construct(MAS_MultiDash $mod)
    {
        $this->mod = $mod;
    }

    public function requireUse()
    {
        if (!$this->mod->VisibleToAdminUser()) {
            $this->deny(403, 'accessdenied');
        }
    }

    public function requireManage()
    {
        $this->requireUse();
        if (!$this->mod->CanManage()) {
            $this->deny(403, 'accessdenied_manage');
        }
    }

    public function deny($code, $langKey)
    {
        http_response_code((int) $code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'ok' => false,
            'error' => $this->mod->Lang($langKey),
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function jsonOk($payload = array())
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(array('ok' => true), $payload), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function getSessionId()
    {
        if (!empty($_SESSION['mas_md_session_id']) && is_string($_SESSION['mas_md_session_id'])) {
            return $_SESSION['mas_md_session_id'];
        }
        $sid = bin2hex(random_bytes(16));
        $_SESSION['mas_md_session_id'] = $sid;
        return $sid;
    }

    public function getAdminUserId()
    {
        $uid = get_userid();
        return $uid ? (int) $uid : 0;
    }

    public function getDisplayName()
    {
        $uid = $this->getAdminUserId();
        if ($uid <= 0) {
            return $this->mod->Lang('guest_admin');
        }
        $u = cms_userinfo::get_instance($uid);
        if ($u && !empty($u['username'])) {
            return (string) $u['username'];
        }
        return $this->mod->Lang('admin_user') . ' #' . $uid;
    }

    /**
     * @param string $action
     * @param int $maxPerMinute
     */
    public function rateLimit($action, $maxPerMinute = 120)
    {
        $key = $this->getSessionId() . ':' . $action;
        $now = time();
        if (!isset(self::$rateBuckets[$key]) || self::$rateBuckets[$key]['reset'] < $now) {
            self::$rateBuckets[$key] = array('count' => 0, 'reset' => $now + 60);
        }
        self::$rateBuckets[$key]['count']++;
        if (self::$rateBuckets[$key]['count'] > $maxPerMinute) {
            $this->deny(429, 'rate_limit');
        }
    }

    public function sanitizeRoomId($room)
    {
        $room = trim((string) $room);
        if ($room === '') {
            return 'dashboard';
        }
        if (strlen($room) > 128) {
            $room = substr($room, 0, 128);
        }
        return preg_replace('/[^a-zA-Z0-9:_\-\.]/', '', $room);
    }
}
