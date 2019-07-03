<?php

use ParagonIE\EasyDB\Factory;

require_once "autoload-phpunit.php";

/**
 * @param array $settings
 * @return \ParagonIE\EasyDB\EasyDB
 */
function getDB(array $settings)
{
    return Factory::create(
        $settings['dsn'],
        $settings['username'],
        $settings['password'],
        $settings['options'] ?? []
    );
}
if (empty($settings['database']['phpunit-safe'])) {
    echo 'Database is not safe for unit testing!', PHP_EOL,
        'Please ensure you\'re not overwriting your real database.', PHP_EOL;
    exit(255);
}
$db = getDB($settings['database']);

$sqlScripts = glob(APP_ROOT . '/sql/*.sql');
sort($sqlScripts, SORT_STRING | SORT_ASC);
foreach ($sqlScripts as $file) {
    $contents = file_get_contents($file);
    $contents = preg_replace('#/\*.+?\*/#', '', $contents);
    $pieces = explode(';', $contents);
    foreach ($pieces as $piece) {
        $piece = trim($piece);
        if (empty($piece)) {
            continue;
        }
        $db->exec($piece);
    }
}
