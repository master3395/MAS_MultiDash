<?php
if (!defined('CMS_VERSION')) {
    exit;
}

while (ob_get_level() > 0) {
    @ob_end_clean();
}

if (!$this->VisibleToAdminUser()) {
    header('Content-Type: text/event-stream; charset=utf-8');
    header('Cache-Control: no-cache');
    echo "event: error\ndata: {\"message\":\"access_denied\"}\n\n";
    exit;
}

if ($this->GetPreference('enabled', '1') !== '1') {
    header('Content-Type: text/event-stream; charset=utf-8');
    header('Cache-Control: no-cache');
    echo "event: error\ndata: {\"message\":\"disabled\"}\n\n";
    exit;
}

if ($this->GetPreference('transport_mode', 'auto') === 'poll') {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Polling mode enabled';
    exit;
}

@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', 'off');
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

$this->LoadMasClass('MasMdRealtimeHub');
$hub = MasMdRealtimeHub::bootstrap($this);
/** @var MasMdSecurity $security */
$security = $hub['security'];
/** @var MasMdPresence $presence */
$presence = $hub['presence'];
/** @var MasMdActivityLog $activity */
$activity = $hub['activity'];

require_once cms_join_path($this->GetModulePath(), 'lib', 'mas_md_request.php');
$roomId = $security->sanitizeRoomId((string) mas_md_request_param($params, (string) $id, 'room_id', 'dashboard'));
$sinceEvent = (int) mas_md_request_param($params, (string) $id, 'since_id', 0);
$lastEvent = $sinceEvent;

MasMdRealtimeHub::sendSseEvent('connected', array('time' => time(), 'room_id' => $roomId));

$presence->heartbeat($roomId, false, '{}');
$activity->log('join', $this->Lang('event_join'));

$end = time() + 25;
while (time() < $end && !connection_aborted()) {
    $events = $activity->listSince($lastEvent, 20);
    foreach ($events as $ev) {
        MasMdRealtimeHub::sendSseEvent('activity', $ev);
        $lastEvent = max($lastEvent, (int) $ev['id']);
    }
    MasMdRealtimeHub::sendSseEvent('presence', array(
        'room' => $presence->listActive($roomId),
        'all' => $presence->listActive(null),
    ));
    MasMdRealtimeHub::sendSseEvent('ping', array('t' => time()));
    sleep(2);
}

MasMdRealtimeHub::sendSseEvent('reconnect', array('after' => 1));
exit;
