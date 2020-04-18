<?php
if (!defined('UPGRADE_SCRIPT')) {
    define('UPGRADE_SCRIPT', $argv[0]);
}

require_once dirname(__DIR__) . '/upgrade.php';

$files = [
    APP_ROOT . '/sql/updates/02-fix-view-offbyone.sql',
    APP_ROOT . '/sql/11-questions.sql',
    APP_ROOT . '/sql/updates/03-allow-questions.sql',
    APP_ROOT . '/sql/updates/04-invite-cap.sql',
];
foreach ($files as $file) {
    $sql = \file_get_contents($file);
    $db->exec($sql);
}
