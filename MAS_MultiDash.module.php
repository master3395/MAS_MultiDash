<?php
if (!defined('CMS_VERSION')) {
    exit;
}

class MAS_MultiDash extends CMSModule
{
    const VERSION = '1.0.4';
    const PREF_ADMIN_SECTION = 'mas_md_admin_section';
    const PERM_USE = 'Use MAS_MultiDash';
    const PERM_MANAGE = 'Manage MAS_MultiDash';

    public function GetName()
    {
        return 'MAS_MultiDash';
    }

    public function GetFriendlyName()
    {
        return $this->Lang('friendlyname');
    }

    public function GetVersion()
    {
        return self::VERSION;
    }

    public function GetHelp()
    {
        require_once dirname(__DIR__) . '/MAS_Common/lib/mas_admin_ui.php';
        return Mas_Admin_Ui::fetchTabbedHelp($this, array(
            array('id' => 'realtime', 'lang' => 'help_realtime_tab'),
        ));
    }

    public function GetAbout()
    {
        require_once dirname(__DIR__) . '/MAS_Common/lib/mas_admin_ui.php';
        return Mas_Admin_Ui::fetchTabbedAbout($this);
    }

    public function GetAuthor()
    {
        return 'master3395';
    }

    public function GetAuthorEmail()
    {
        return 'info [at] newstargeted [dot] com';
    }

    public function GetAuthorUrl()
    {
        return 'https://newstargeted.com/contact/';
    }

    public function GetChangeLog()
    {
        $baseDir = realpath($this->GetModulePath());
        $file = realpath($this->GetModulePath() . DIRECTORY_SEPARATOR . 'CHANGELOG.md');
        if (!$baseDir || !$file || !is_file($file) || !is_readable($file)) {
            return $this->Lang('changelog');
        }
        if (strpos($file, $baseDir) !== 0 || basename($file) !== 'CHANGELOG.md') {
            return $this->Lang('changelog');
        }
        $markdown = @file_get_contents($file);
        if ($markdown === false || $markdown === '') {
            return $this->Lang('changelog');
        }

        return '<div class="mas_md_changelog_html">' . $this->changelogMarkdownToHtml((string) $markdown) . '</div>';
    }

    private function changelogMarkdownToHtml($markdown)
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $markdown);
        if (!is_array($lines)) {
            return '';
        }
        $out = array();
        $inList = false;
        foreach ($lines as $line) {
            if ($line === '') {
                if ($inList) {
                    $out[] = '</ul>';
                    $inList = false;
                }
                continue;
            }
            if (preg_match('/^###\s+(.+)$/', $line, $m)) {
                if ($inList) {
                    $out[] = '</ul>';
                    $inList = false;
                }
                $out[] = '<h3>' . cms_htmlentities($m[1]) . '</h3>';
                continue;
            }
            if (preg_match('/^##\s+(.+)$/', $line, $m)) {
                if ($inList) {
                    $out[] = '</ul>';
                    $inList = false;
                }
                $out[] = '<h2>' . cms_htmlentities($m[1]) . '</h2>';
                continue;
            }
            if (preg_match('/^#\s+(.+)$/', $line, $m)) {
                if ($inList) {
                    $out[] = '</ul>';
                    $inList = false;
                }
                $out[] = '<h2>' . cms_htmlentities($m[1]) . '</h2>';
                continue;
            }
            if (preg_match('/^[-*]\s+(.+)$/', $line, $m)) {
                if (!$inList) {
                    $out[] = '<ul>';
                    $inList = true;
                }
                $out[] = '<li>' . cms_htmlentities($m[1]) . '</li>';
                continue;
            }
            if ($inList) {
                $out[] = '</ul>';
                $inList = false;
            }
            $out[] = '<p>' . cms_htmlentities($line) . '</p>';
        }
        if ($inList) {
            $out[] = '</ul>';
        }

        return implode("\n", $out);
    }

    public function IsPluginModule()
    {
        return false;
    }

    public function HasAdmin()
    {
        return true;
    }

    public function GetAdminSection()
    {
        return $this->GetPreference(self::PREF_ADMIN_SECTION, 'extensions');
    }

    public function GetAdminDescription()
    {
        return $this->Lang('moddescription');
    }

    public function VisibleToAdminUser()
    {
        return $this->CheckPermission(self::PERM_USE);
    }

    public function CanManage()
    {
        return $this->CheckPermission(self::PERM_MANAGE);
    }

    public function GetDependencies()
    {
        return array();
    }

    public function MinimumCMSVersion()
    {
        return '2.2.10';
    }

    public function GetMinimumPHPVersion()
    {
        return '7.4.0';
    }

    public function InitializeFrontend()
    {
        $this->RestrictUnknownParams();
    }

    public function InitializeAdmin()
    {
    }

    public function InstallPostMessage()
    {
        return $this->Lang('postinstall');
    }

    public function UninstallPostMessage()
    {
        return $this->Lang('postuninstall');
    }

    public function UninstallPreMessage()
    {
        return $this->Lang('really_uninstall');
    }

    public function ShowDonationsTab()
    {
        return ($this->GetPreference('hidedonationstab') != $this->GetVersion());
    }

    /**
     * @param string $name e.g. MasMdSecurity
     */
    public function LoadMasClass($name)
    {
        $file = cms_join_path($this->GetModulePath(), 'lib', 'class.' . $name . '.php');
        if (!is_file($file)) {
            throw new Exception('MAS_MultiDash class not found: ' . $name);
        }
        require_once $file;
    }

    public function GetSandboxPath()
    {
        return cms_join_path($this->GetModulePath(), 'data', 'files');
    }

    public function GetApiBaseUrl($id, $returnid = '')
    {
        return $this->appendAdminNoThemeUrl($id, 'ajax_api');
    }

    public function GetStreamUrl($id, $returnid = '')
    {
        return $this->appendAdminNoThemeUrl($id, 'stream');
    }

    /**
     * Admin moduleinterface.php only honors unprefixed showtemplate=false (not m1_showtemplate).
     *
     * @param string $id
     * @param string $action
     * @return string
     */
    public function appendAdminNoThemeUrl($id, $action)
    {
        $url = html_entity_decode($this->create_url($id, $action, ''), ENT_QUOTES, 'UTF-8');
        if (stripos($url, 'showtemplate=false') === false) {
            $sep = (strpos($url, '?') !== false) ? '&' : '?';
            $url .= $sep . 'showtemplate=false';
        }
        if (stripos($url, 'suppressoutput') === false) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 'suppressoutput=1';
        }

        return $url;
    }

    public function SuppressAdminOutput(&$request)
    {
        $action = $this->resolveAdminActionFromRequest($request);

        return in_array($action, array('stream', 'ajax_api'), true);
    }

    /**
     * @param array<string,mixed> $request
     * @return string
     */
    private function resolveAdminActionFromRequest($request)
    {
        $mact = '';
        if (isset($request['mact']) && is_string($request['mact'])) {
            $mact = $request['mact'];
        } elseif (isset($_REQUEST['mact']) && is_string($_REQUEST['mact'])) {
            $mact = (string) $_REQUEST['mact'];
        }
        if ($mact === '') {
            return '';
        }
        $parts = explode(',', html_entity_decode($mact, ENT_QUOTES, 'UTF-8'), 4);

        return isset($parts[2]) ? trim($parts[2]) : '';
    }
}
