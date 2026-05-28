<?php
if (!defined('CMS_VERSION')) {
    exit;
}

class MasMdFileBrowser
{
    /** @var MAS_MultiDash */
    private $mod;

    /** @var MasMdPathGuard */
    private $guard;

    /** @var MasMdDocumentStore */
    private $docs;

    /** @var MasMdActivityLog */
    private $log;

    public function __construct(MAS_MultiDash $mod, MasMdPathGuard $guard, MasMdDocumentStore $docs, MasMdActivityLog $log)
    {
        $this->mod = $mod;
        $this->guard = $guard;
        $this->docs = $docs;
        $this->log = $log;
    }

    /**
     * @return array<int,array<string,string>>
     */
    public function listRoots()
    {
        $items = array(
            array('key' => 'sandbox', 'label' => $this->mod->Lang('root_sandbox')),
        );
        $config = cms_utils::get_config();
        $uploads = realpath((string) ($config['uploads_path'] ?? cms_join_path(CMS_ROOT_PATH, 'uploads')));
        $extra = trim((string) $this->mod->GetPreference('cms_upload_roots', ''));
        if ($uploads && $extra !== '') {
            $parts = preg_split('/[\s,;]+/', $extra);
            foreach ($parts as $rel) {
                $rel = trim((string) $rel, "/ \t\n\r\0\x0B");
                if ($rel === '') {
                    continue;
                }
                $items[] = array('key' => 'cms:' . $rel, 'label' => $this->mod->Lang('root_cms') . ': ' . $rel);
            }
        }
        return $items;
    }

    /**
     * @return array{ok:bool,entries?:array,error?:string}
     */
    public function listDir($rootKey, $relative = '')
    {
        $resolved = $this->guard->resolveFile($rootKey, $relative);
        if (!$resolved['ok']) {
            return array('ok' => false, 'error' => $resolved['error']);
        }
        $path = $resolved['path'];
        if (!is_dir($path)) {
            return array('ok' => false, 'error' => 'not_a_directory');
        }
        $entries = array();
        $dh = @opendir($path);
        if (!$dh) {
            return array('ok' => false, 'error' => 'read_failed');
        }
        while (($name = readdir($dh)) !== false) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            $full = cms_join_path($path, $name);
            $entries[] = array(
                'name' => $name,
                'dir' => is_dir($full),
                'size' => is_file($full) ? (int) filesize($full) : 0,
                'mtime' => (int) @filemtime($full),
            );
        }
        closedir($dh);
        usort($entries, function ($a, $b) {
            if ($a['dir'] !== $b['dir']) {
                return $a['dir'] ? -1 : 1;
            }
            return strcasecmp($a['name'], $b['name']);
        });
        return array('ok' => true, 'entries' => $entries, 'path' => $relative);
    }

    /**
     * @return array{ok:bool,document?:array,doc_key?:string,error?:string}
     */
    public function readFile($rootKey, $relative)
    {
        $resolved = $this->guard->resolveFile($rootKey, $relative);
        if (!$resolved['ok']) {
            return array('ok' => false, 'error' => $resolved['error']);
        }
        $path = $resolved['path'];
        if (!is_file($path)) {
            return array('ok' => false, 'error' => 'not_a_file');
        }
        if (!$this->guard->isEditableExtension($path)) {
            return array('ok' => false, 'error' => 'extension_denied');
        }
        $max = (int) $this->mod->GetPreference('max_file_bytes', '524288');
        $size = (int) filesize($path);
        if ($size > $max) {
            return array('ok' => false, 'error' => 'file_too_large');
        }
        $content = @file_get_contents($path);
        if ($content === false) {
            return array('ok' => false, 'error' => 'read_failed');
        }
        $docKey = $this->guard->docKeyForPath($rootKey, $relative);
        $doc = $this->docs->ensureFromFile($docKey, $content, 'text/plain');
        $this->log->log('file_open', $this->mod->Lang('event_file_open') . ': ' . $relative, array('root' => $rootKey, 'path' => $relative));
        return array('ok' => true, 'document' => $doc, 'doc_key' => $docKey, 'path' => $relative, 'root' => $rootKey);
    }

    /**
     * @return array{ok:bool,error?:string}
     */
    public function writeFileFromDoc($rootKey, $relative, $content)
    {
        $resolved = $this->guard->resolveFile($rootKey, $relative);
        if (!$resolved['ok']) {
            return array('ok' => false, 'error' => $resolved['error']);
        }
        $path = $resolved['path'];
        if (!$this->guard->isEditableExtension($path)) {
            return array('ok' => false, 'error' => 'extension_denied');
        }
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return array('ok' => false, 'error' => 'mkdir_failed');
        }
        if (@file_put_contents($path, $content, LOCK_EX) === false) {
            return array('ok' => false, 'error' => 'write_failed');
        }
        return array('ok' => true);
    }
}
