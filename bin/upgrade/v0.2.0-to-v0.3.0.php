<?php
if (!defined('UPGRADE_SCRIPT')) {
    define('UPGRADE_SCRIPT', $argv[0]);
}

require_once dirname(__DIR__) . '/upgrade.php';

$files = [
    APP_ROOT . '/sql/09-analytics.sql',
    APP_ROOT . '/sql/updates/01-collection-description.sql'
];
foreach ($files as $file) {
    $sql = \file_get_contents($file);
    $db->exec($sql);
}
