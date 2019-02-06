<?php
use Slim\Container;
use ParagonIE\EasyDB\Factory;
// DIC configuration

$container = $app->getContainer();

// database
$container['database'] = function (Container $c) {
    $settings = $c->get('settings')['database'];
    return Factory::create(
        $settings['dsn'],
        $settings['user'],
        $settings['pass'],
        $settings['options']
    );
};

// twig
$container['twig'] = function (Container $c) {
    $settings = $c->get('settings')['twig'];
    $loader = new \Twig_Loader_Filesystem([
        APP_ROOT . '/templates'
    ]);
    return new \Twig_Environment($loader, $settings);
};

// monolog
$container['logger'] = function (Container $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};
