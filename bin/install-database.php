<?php
declare(strict_types=1);
use ParagonIE\EasyDB\Factory;

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
        exit(255);
    }
}

$local = require APP_ROOT . '/local/settings.php';
$settings = $local = $settings;

$db = Factory::create(
    $settings['settings']['database']['dsn'],
    $settings['settings']['database']['user'],
    $settings['settings']['database']['pass'],
    $settings['settings']['database']['options']
);

/* NOW THE PARSING: */
$driver = \strtolower($db->getDriver());
foreach (\glob(APP_ROOT . '/sql/' . $driver . '/*.sql') as $file) {
    $sql = \file_get_contents($file);
    $db->query($sql);
}
