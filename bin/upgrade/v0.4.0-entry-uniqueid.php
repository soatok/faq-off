<?php
use ParagonIE\ConstantTime\Base32;
if (!defined('UPGRADE_SCRIPT')) {
    define('UPGRADE_SCRIPT', $argv[0]);
}

require_once dirname(__DIR__) . '/upgrade.php';

foreach ($db->run("SELECT entryid FROM faqoff_entry WHERE uniqueid IS NULL") as $entry) {
    $db->update('faqoff_entry', [
        'uniqueid' => Base32::encode(random_bytes(20))
    ], [
        'entryid' => $entry['entryid']
    ]);
}
