<?php
declare(strict_types=1);
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\Factory;

if (!defined('UPGRADE_SCRIPT')) {
    define('UPGRADE_SCRIPT', $argv[0]);
}

/* PREAMBLE: */
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

/* Ensure local settings are defined: */
$settings = require APP_ROOT . '/src/settings.php';
if (!is_readable(APP_ROOT . '/local/settings.php')) {
    if (
        !in_array('-f', $argv, true)
        &&
        !in_array('--force', $argv, true)
    ) {
        echo 'Local settings not defined! Please run create-config.php first.', PHP_EOL;
        echo 'To proceed with the default settings, you must pass the -f or --force ',
        'flag to install-database.php.', PHP_EOL;
        // Silent exit.
        exit(0);
    }
}

$local = require APP_ROOT . '/local/settings.php';
$settings = $local + $settings;

$db = Factory::create(
    $settings['settings']['database']['dsn'],
    $settings['settings']['database']['username'],
    $settings['settings']['database']['password'],
    $settings['settings']['database']['options']
);

/**
 * @param EasyDB $db
 * @return array
 */
function upgrade_auto_detect(EasyDB $db)
{
    $scripts = [];
    if (!table_exists($db, 'faqoff_themes')) {
        $scripts[] = 'v0.1.0-to-v0.2.0.php';
    }
    if (!table_exists($db, 'faqoff_entry_accesslog')) {
        $scripts[] = 'v0.2.0-to-v0.3.0.php';
    }
    if (!table_exists($db, 'faqoff_question_box')) {
        $scripts[] = 'v0.3.0-to-v0.4.0.php';
    }
    return $scripts;
}

/**
 * @param EasyDB $db
 * @param string $table
 * @param string $schema
 * @return bool
 */
function table_exists(EasyDB $db, string $table, string $schema = 'public'): bool
{
    return $db->exists(
    "SELECT count(*)
           FROM   information_schema.tables 
           WHERE  table_schema = ?
           AND    table_name = ?",
        $schema,
        $table
    );
}

if (UPGRADE_SCRIPT === $argv[0]) {
    if ($argc < 2) {
        echo 'No second argument passed.', PHP_EOL;
        exit(0);
    }
    if ($argv[1] === 'auto') {
        $scripts = upgrade_auto_detect($db);
    } else {
        $scripts = [];
        $file = realpath(APP_ROOT . '/bin/upgrade/' . $argv[1] . '.php');
        if (!$file) {
            echo 'File not found: ', $file, PHP_EOL;
            exit(2);
        }
        if (strpos($file, APP_ROOT . '/bin/upgrade/' !== 0)) {
            echo 'Illegal file path: ', $file, PHP_EOL;
            exit(1);
        }
        $scripts []= $argv[1] . '.php';
    }
    if (!$scripts) {
        echo 'No upgrades necessary.', PHP_EOL;
        exit(0);
    }
    foreach ($scripts as $script) {
        $fullPath = APP_ROOT . '/bin/upgrade/' . $script;
        echo 'Running ', $fullPath, PHP_EOL;
        require_once $fullPath;
    }
}
