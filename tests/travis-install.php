<?php

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

if (!is_dir(APP_ROOT . '/local')) {
    mkdir(APP_ROOT . '/local', 0777);
}
$code = <<<EOBLOB
<?php

return [
    'database' => [
        'dsn' => 'pgsql:host=localhost;dbname=faqoff_test',
        'username' => 'phpunit',
        'password' => 'phpunit',
        'options' => [],
        'phpunit-safe' => true
    ],
];

EOBLOB
;
file_put_contents(APP_ROOT . '/local/phpunit.php', $code);
