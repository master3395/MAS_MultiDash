<?php
if (!defined('CMS_VERSION')) {
    exit;
}

while (ob_get_level() > 0) {
    @ob_end_clean();
}

require_once cms_join_path($this->GetModulePath(), 'lib', 'mas_md_request.php');
$this->LoadMasClass('MasMdRealtimeHub');
$hub = MasMdRealtimeHub::bootstrap($this);
/** @var MasMdSecurity $security */
$security = $hub['security'];
/** @var MasMdPresence $presence */
$presence = $hub['presence'];
/** @var MasMdActivityLog $activity */
$activity = $hub['activity'];
/** @var MasMdDocumentStore $docs */
$docs = $hub['docs'];
/** @var MasMdFileBrowser $files */
$files = $hub['files'];

$security->requireUse();
if ($this->GetPreference('enabled', '1') !== '1') {
    $security->deny(503, 'module_disabled');
}
$security->rateLimit('api', 180);

$action = mas_md_get_request_action($params, (string) $id);

$roomId = $security->sanitizeRoomId((string) mas_md_request_param($params, (string) $id, 'room_id', 'dashboard'));

switch ($action) {
    case 'heartbeat':
        $typing = !empty($_REQUEST['typing']) || !empty($params['typing']);
        $payload = isset($_REQUEST['payload']) ? (string) $_REQUEST['payload'] : '{}';
        if (isset($params['payload']) && is_string($params['payload'])) {
            $payload = $params['payload'];
        }
        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            $decoded = array();
        }
        $presence->heartbeat($roomId, $typing, json_encode($decoded));
        $security->jsonOk(array('presence' => $presence->listActive($roomId)));
        break;

    case 'leave':
        $presence->leave();
        $activity->log('leave', $this->Lang('event_leave'));
        $security->jsonOk();
        break;

    case 'feed_list':
        $since = isset($_REQUEST['since_id']) ? (int) $_REQUEST['since_id'] : 0;
        $security->jsonOk(array('events' => $activity->listSince($since, 50)));
        break;

    case 'poll':
        $since = isset($_REQUEST['since_id']) ? (int) $_REQUEST['since_id'] : 0;
        $hubObj = new MasMdRealtimeHub($this, $security, $presence, $activity);
        $security->jsonOk($hubObj->pollPayload($roomId, $since));
        break;

    case 'presence_all':
        $security->jsonOk(array('presence' => $presence->listActive(null)));
        break;

    case 'file_roots':
        $security->jsonOk(array('roots' => $files->listRoots()));
        break;

    case 'file_list':
        $rootKey = (string) mas_md_request_param($params, (string) $id, 'root', 'sandbox');
        $rel = (string) mas_md_request_param($params, (string) $id, 'path', '');
        $result = $files->listDir($rootKey, $rel);
        if (!$result['ok']) {
            http_response_code(400);
            $security->jsonOk(array('ok' => false, 'error' => $result['error']));
        }
        $security->jsonOk($result);
        break;

    case 'file_read':
        $rootKey = isset($_REQUEST['root']) ? (string) $_REQUEST['root'] : 'sandbox';
        $rel = isset($_REQUEST['path']) ? (string) $_REQUEST['path'] : '';
        $result = $files->readFile($rootKey, $rel);
        if (!$result['ok']) {
            http_response_code(400);
            $security->jsonOk(array('ok' => false, 'error' => $result['error']));
        }
        $security->jsonOk($result);
        break;

    case 'doc_save':
        $docKey = isset($_REQUEST['doc_key']) ? (string) $_REQUEST['doc_key'] : '';
        $baseRev = isset($_REQUEST['base_revision']) ? (int) $_REQUEST['base_revision'] : 0;
        $content = isset($_REQUEST['content']) ? (string) $_REQUEST['content'] : '';
        $rootKey = isset($_REQUEST['root']) ? (string) $_REQUEST['root'] : '';
        $relPath = isset($_REQUEST['path']) ? (string) $_REQUEST['path'] : '';
        if ($docKey === '') {
            $security->deny(400, 'api_error');
        }
        $save = $docs->save($docKey, $baseRev, $content);
        if (!$save['ok']) {
            $activity->log('conflict', $this->Lang('event_conflict') . ': ' . $docKey);
            http_response_code(409);
            echo json_encode(array(
                'ok' => false,
                'conflict' => true,
                'document' => $save['conflict'],
            ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($rootKey !== '' && $relPath !== '') {
            $write = $files->writeFileFromDoc($rootKey, $relPath, $content);
            if (!$write['ok']) {
                $security->jsonOk(array('ok' => false, 'error' => $write['error'], 'document' => $save['document']));
            }
        }
        $activity->log('save', $this->Lang('event_save') . ': ' . $docKey);
        $security->jsonOk(array('document' => $save['document']));
        break;

    case 'scratch_create':
        $docKey = 'scratch:' . bin2hex(random_bytes(8));
        $doc = $docs->ensureFromFile($docKey, '', 'text/plain');
        $activity->log('scratch', $this->Lang('event_scratch'));
        $security->jsonOk(array('document' => $doc, 'doc_key' => $docKey));
        break;

    default:
        $security->deny(400, 'unknown_action');
}
