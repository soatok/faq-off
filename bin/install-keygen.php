<?php
declare(strict_types=1);
define('APP_ROOT', dirname(__DIR__));

if (file_exists(APP_ROOT . '/local/encryption-key.php')) {
    exit(1); // Key already exists
}

file_put_contents(
    APP_ROOT . '/local/encryption-key.php',
    '<?php' . PHP_EOL .
    'use ParagonIE\HiddenString\HiddenString;' . PHP_EOL .
    'return new HiddenString(' . PHP_EOL .
    '    \sodium_hex2bin(\'' . \sodium_hex2bin(\random_bytes(32)) . '\')' . PHP_EOL .
    ');' . PHP_EOL
);
