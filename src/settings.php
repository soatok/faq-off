<?php
use Soatok\AnthroKit\Auth\Fursona;

$default = [
    'displayErrorDetails' => true, // set to false in production
    'addContentLengthHeader' => false, // Allow the web server to send the content-length header

    'encryption-key' => include __DIR__ . '/encryption-key.php',

    'database' => [
        'dsn' => 'pgsql:host=localhost;dbname=faqoff',
        'username' => 'soatok',
        'password' => 'soatok',
        'options' => []
    ],

    'twig' => [
        'autoescape' => 'html',
        'template_paths' => [
            __DIR__ . '/../templates/'
        ],
    ],

    // Monolog settings
    'logger' => [
        'name' => 'slim-app',
        'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
        'level' => \Monolog\Logger::DEBUG,
    ],
];

$anthrokit = [
        'allow-twitter-auth' => true,
        'redirect' => [
            'auth-success' => '/',
            'activate-success' => '/',
            'empty-params' => '/',
            'invalid-action' => '/',
            'login' => '/auth/login',
            'register' => '/auth/register',
        ],
        'sql' => [
            'accounts' => [
                'table' => 'faqoff_accounts',
                'field' => [
                    'id' => 'accountid',
                    'login' => 'login',
                    'pwhash' => 'pwhash',
                    'twofactor' => 'twofactor',
                    'email' => 'email',
                    'email_activation' => 'email_activation',
                    'external_auth' => 'external_auth'
                ]
            ],
            'account_known_device' => [
                'table' => 'faqoff_account_known_device',
                'field' => [
                    'id' => 'deviceid',
                    'account' => 'accountid',
                    'created' => 'created',
                    'selector' => 'selector',
                    'validator' => 'validator'
                ]
            ]
        ],
        'templates' => [
            'email-activate' => 'email/activate.twig',
            'login' => 'login.twig',
            'register' => 'register.twig',
            'register-success' => 'register-success.twig',
            'two-factor' => 'two-factor.twig'
        ]
];

if (is_readable(APP_ROOT . '/local/settings.php')) {
    $local = require_once APP_ROOT . '/local/settings.php';
    return [
        Fursona::CONTAINER_KEY => $anthrokit,
        'settings' => $local + $default
    ];
}
return [
    Fursona::CONTAINER_KEY => $anthrokit,
    'settings' => $default
];
