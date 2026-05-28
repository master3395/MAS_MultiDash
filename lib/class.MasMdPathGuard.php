<?php
if (!defined('CMS_VERSION')) {
    exit;
}

class MasMdPathGuard
{
    /** @var MAS_MultiDash */
    private $mod;

    public function __construct(MAS_MultiDash $mod)
    {
        $this->mod = $mod;
    }

    /**
     * @return array<int,string> absolute paths
     */
    public function getAllowedRoots()
    {
        $roots = array();
        $sandbox = realpath($this->mod->GetSandboxPath());
        if ($sandbox && is_dir($sandbox)) {
            $roots[] = $sandbox;
        }
        $config = cms_utils::get_config();
        $uploads = isset($config['uploads_path']) ? (string) $config['uploads_path'] : '';
        if ($uploads === '') {
            $uploads = cms_join_path(CMS_ROOT_PATH, 'uploads');
        }
        $uploadsReal = realpath($uploads);
        if (!$uploadsReal || !is_dir($uploadsReal)) {
            return $roots;
        }
        $extra = trim((string) $this->mod->GetPreference('cms_upload_roots', ''));
        if ($extra === '') {
            return $roots;
        }
        $parts = preg_split('/[\s,;]+/', $extra);
        if (!is_array($parts)) {
            return $roots;
        }
        foreach ($parts as $rel) {
            $rel = trim((string) $rel, "/ \t\n\r\0\x0B");
            if ($rel === '' || strpos($rel, '..') !== false) {
                continue;
            }
            $candidate = realpath(cms_join_path($uploadsReal, $rel));
            if ($candidate && is_dir($candidate) && strpos($candidate, $uploadsReal) === 0) {
                $roots[] = $candidate;
            }
        }

        return array_values(array_unique($roots));
    }

    /**
     * @param string $rootKey sandbox|cms:subdir
     * @param string $relative
     * @return array{ok:bool,path?:string,error?:string}
     */
    public function resolveFile($rootKey, $relative)
    {
        $relative = str_replace('\\', '/', (string) $relative);
        $relative = ltrim($relative, '/');
        if ($relative === '' || strpos($relative, '..') !== false || strpos($relative, "\0") !== false) {
            return array('ok' => false, 'error' => 'invalid_path');
        }
        $roots = $this->getAllowedRoots();
        if ($rootKey === 'sandbox' || $rootKey === '') {
            $base = realpath($this->mod->GetSandboxPath());
            if (!$base) {
                return array('ok' => false, 'error' => 'sandbox_missing');
            }
            $full = realpath(cms_join_path($base, $relative));
            if (!$full || strpos($full, $base) !== 0) {
                return array('ok' => false, 'error' => 'path_denied');
            }
            return array('ok' => true, 'path' => $full, 'root' => 'sandbox');
        }
        if (strpos($rootKey, 'cms:') === 0) {
            $sub = substr($rootKey, 4);
            $config = cms_utils::get_config();
            $uploads = realpath((string) ($config['uploads_path'] ?? cms_join_path(CMS_ROOT_PATH, 'uploads')));
            if (!$uploads) {
                return array('ok' => false, 'error' => 'uploads_missing');
            }
            $base = realpath(cms_join_path($uploads, $sub));
            if (!$base || strpos($base, $uploads) !== 0) {
                return array('ok' => false, 'error' => 'root_denied');
            }
            $full = realpath(cms_join_path($base, $relative));
            if (!$full || strpos($full, $base) !== 0) {
                return array('ok' => false, 'error' => 'path_denied');
            }
            return array('ok' => true, 'path' => $full, 'root' => $rootKey);
        }

        return array('ok' => false, 'error' => 'unknown_root');
    }

    public function isEditableExtension($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allow = array('txt', 'md', 'json', 'tpl', 'css', 'js', 'html', 'htm', 'xml', 'ini', 'log');
        if ($this->mod->GetPreference('allow_php_edit', '0') === '1') {
            $allow[] = 'php';
        }
        return in_array($ext, $allow, true);
    }

    public function docKeyForPath($rootKey, $relative)
    {
        return 'file:' . hash('sha256', $rootKey . '|' . $relative);
    }
}
