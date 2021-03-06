<?php
use Soatok\AnthroKit\Auth\Fursona;

$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

$default = [
    'displayErrorDetails' => true, // set to false in production
    'addContentLengthHeader' => false, // Allow the web server to send the content-length header

    // You can override this in local/admins.json
    'admin-accounts' => [],

    // Enable aggregate statistics
    'aggregate-stats' => true,

    // Change in local/settings.php to show more news.
    'front-news-limit' => 5,

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
            APP_ROOT . '/templates/'
        ],
    ],

    'twig-custom' => [
        'css' => [],
        'js' => [],
        'vars' => [
            'hostname' =>
                ($isHttps ? 'https' : 'http') .
                '://' .
                \preg_replace(
                    '/[^A-Za-z0-9.:]+/',
                    '',
                    $_SERVER['HTTP_HOST'] ?? 'localhost'
                ),
            'opengraph_image_url' =>
                'https://raw.githubusercontent.com/soatok/dholecrypto-web/master/public/ansi-dhole.png',
            'dark-theme' => true,
            'site-name' => 'FAQ Off'
        ]
    ],

    // Monolog settings
    'logger' => [
        'name' => 'slim-app',
        'path' => isset($_ENV['docker']) ? 'php://stdout' : APP_ROOT . '/logs/app.log',
        'level' => \Monolog\Logger::DEBUG,
    ],
];

$anthrokit = [
    'allow-password-auth' => false,
    'allow-twitter-auth' => true,
    'redirect' => [
        'account-banned' => '/generic-error/account-banned',
        'auth-success' => '/',
        'auth-failure' => '/generic-error/auth-failure',
        'activate-success' => '/',
        'empty-params' => '/generic-error/empty-params',
        'invalid-action' => '/generic-error/invalid-action',
        'invite-required' => '/generic-error/invite-required',
        'login' => '/auth/login',
        'logout-fail' => '/generic-error/logout-fail',
        'logout-success' => '/',
        'register' => '/auth/register',
        'twitter' => '/auth/twitter',
        'twitter-error' => '/generic-error/twitter-error',
    ],
    'require-invite-register' => false,
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
        ],
        'invites' => [
            'table' => 'faqoff_invites',
            'field' => [
                'id' => 'inviteid',
                'from' => 'invitefrom',
                'twitter' => 'twitter',
                'email' => 'email',
                'invite_code' => 'invite_code',
                'claimed' => 'claimed',
                'created' => 'created',
                'newaccountid' => 'newaccountid'
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

// Merge local changes to AnthroKit configuration
if (is_readable(APP_ROOT . '/local/anthrokit.php')) {
    $local = require_once APP_ROOT . '/local/anthrokit.php';
    $anthrokit = $local + $anthrokit;
}
$anthrokit = Fursona::autoConfig($anthrokit);

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
