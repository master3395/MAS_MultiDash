<?php
if (!defined('CMS_VERSION')) {
    exit;
}

require_once dirname(__DIR__) . '/MAS_Common/lib/mas_admin_ui.php';
Mas_Admin_Ui::renderDonationsTab($this, $id, $returnid, 'defaultadmin', 'defaultadmin');
