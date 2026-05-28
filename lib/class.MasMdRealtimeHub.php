<?php
if (!defined('CMS_VERSION')) {
    exit;
}

class MasMdRealtimeHub
{
    /** @var MAS_MultiDash */
    private $mod;

    /** @var MasMdSecurity */
    private $security;

    /** @var MasMdPresence */
    private $presence;

    /** @var MasMdActivityLog */
    private $activity;

    public function __construct(MAS_MultiDash $mod, MasMdSecurity $security, MasMdPresence $presence, MasMdActivityLog $activity)
    {
        $this->mod = $mod;
        $this->security = $security;
        $this->presence = $presence;
        $this->activity = $activity;
    }

    public static function bootstrap(MAS_MultiDash $mod)
    {
        $mod->LoadMasClass('MasMdSecurity');
        $mod->LoadMasClass('MasMdPathGuard');
        $mod->LoadMasClass('MasMdPresence');
        $mod->LoadMasClass('MasMdActivityLog');
        $mod->LoadMasClass('MasMdDocumentStore');
        $mod->LoadMasClass('MasMdFileBrowser');
        $security = new MasMdSecurity($mod);
        $presence = new MasMdPresence($mod, $security);
        $activity = new MasMdActivityLog($mod, $security);
        $docs = new MasMdDocumentStore($mod, $security);
        $guard = new MasMdPathGuard($mod);
        $files = new MasMdFileBrowser($mod, $guard, $docs, $activity);
        return compact('security', 'presence', 'activity', 'docs', 'guard', 'files');
    }

    /**
     * @return array<string,mixed>
     */
    public function pollPayload($roomId, $sinceEventId)
    {
        return array(
            'presence' => $this->presence->listActive($roomId),
            'presence_all' => $this->presence->listActive(null),
            'events' => $this->activity->listSince($sinceEventId, 30),
            'server_time' => time(),
        );
    }

    public static function sendSseEvent($event, $data)
    {
        echo 'event: ' . preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $event) . "\n";
        echo 'data: ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n\n";
        if (function_exists('ob_flush')) {
            @ob_flush();
        }
        @flush();
    }
}
