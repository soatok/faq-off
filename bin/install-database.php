<?php
declare(strict_types=1);
use ParagonIE\EasyDB\Factory;

/* PREAMBLE: */
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

$settings = require APP_ROOT . '/src/settings.php';

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
