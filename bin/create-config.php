<?php

use GetOpt\{
    ArgumentException,
    Option,
    GetOpt
};
use Soatok\DholeCrypto\Keyring;
use Soatok\DholeCrypto\Key\SymmetricKey;

function usage(): void
{
    echo 'Usage: php create-config.php -u USERNAME -w PASSWORD -d DATABASE', PHP_EOL;
    echo 'Optional arguments:', PHP_EOL;
    echo '    -h HOSTNAME', PHP_EOL;
    echo '    -p PORT', PHP_EOL;
    echo PHP_EOL;
    exit(1);
}

/* PREAMBLE: */
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

$getopt = new GetOpt([
    (new Option('u', 'username', GetOpt::REQUIRED_ARGUMENT))
        ->setDescription('PostgreSQL username'),
    (new Option('w', 'password', GetOpt::REQUIRED_ARGUMENT))
        ->setDescription('PostgreSQL password'),
    (new Option('d', 'database', GetOpt::REQUIRED_ARGUMENT))
        ->setDescription('PostgreSQL database name'),
    (new Option('h', 'host', GetOpt::OPTIONAL_ARGUMENT))
        ->setDescription('PostgreSQL hostname')
        ->setDefaultValue('localhost'),
    (new Option('p', 'port', GetOpt::OPTIONAL_ARGUMENT))
        ->setDescription('PostgreSQL port')
        ->setDefaultValue('5432'),
]);

try {
    $getopt->process();
} catch (ArgumentException $ex) {
    file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
    echo PHP_EOL, $getopt->getHelpText(), PHP_EOL;
    exit(1);
}

$host = $getopt->getOption('host');
$port = $getopt->getOption('port');
$username = $getopt->getOption('username');
$password = $getopt->getOption('password');
$database = $getopt->getOption('database');

if (empty($username) || empty($password) || empty($database)) {
    usage();
}

$dsn = 'pgsql:host=' . $host . ';dbname=' . $database;
if ($port != 5432) {
    $dsn .= ';port=' . $port;
}

$dsn = str_replace("'", "\\'", $dsn);
$username = str_replace("'", "\\'", $username);
$password = str_replace("'", "\\'", $password);

$symmetric = SymmetricKey::generate();
$keyring = new Keyring();

$dump = <<<EODUMP
<?php
use Soatok\DholeCrypto\Keyring;

\$keyring = new Keyring();
return [
    'encryption_key' => include __DIR__ . '/encryption-key.php',

    'database' => [
        'dsn' => '{$dsn}',
        'username' => '{$username}',
        'password' => '{$password}',
        'options' => []
    ],

    'markdown' => [
        'html_input' => 'allow',
        'allow_unsafe_links' => true
    ],

    'password-key' => \$keyring->load('{$keyring->save($symmetric)}'),

    // Twitter OAuth
    'twitter' => [
        'callback_url' => '',
        'consumer_key' => '',
        'consumer_secret' => '',
        'access_token' => '',
        'access_token_secret' => ''
    ],
];
EODUMP;

file_put_contents(APP_ROOT . '/local/settings.php', $dump);
copy(APP_ROOT . '/src/content_security_policy.json', APP_ROOT . '/local/content_security_policy.json');
exit(0); // OK
