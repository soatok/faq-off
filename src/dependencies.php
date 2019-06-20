<?php

use ParagonIE\CSPBuilder\CSPBuilder;
use ParagonIE\EasyDB\Factory;
use Slim\Container;
use Soatok\FaqOff\Utility;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\TransportInterface;

// DIC configuration

$container = $app->getContainer();

$container['csp'] = function (Container $c): CSPBuilder {
    return CSPBuilder::fromFile(__DIR__ . '/content_security_policy.json');
};

// database
$container['db'] = function (Container $c) {
    $settings = $c->get('settings')['database'];
    return Factory::create(
        $settings['dsn'],
        $settings['username'],
        $settings['password'],
        $settings['options'] ?? []
    );
};
$conteiner['database'] = $container['db'];
$container['mailer'] = function (Container $c): TransportInterface {
    $settings = $c->get('settings')['email'] ?? [];
    if (empty($settings['transport'])) {
        $settings['transport'] = null;
    }
    switch ($settings['transport']) {
        case 'smtp':
            return new Smtp(
                new SmtpOptions($settings['options'] ?? [])
            );
        default:
            return new Sendmail();
    }
};

$container['twig'] = function (Container $c): Environment {
    static $twig = null;
    if (!$twig) {
        $settings = $c->get('settings')['twig'];
        $loader = new FilesystemLoader($settings['template_paths']);
        $twig = Utility::terraform(new Environment($loader));
    }
    return $twig;
};

if (empty($_SESSION['anti-csrf'])) {
    $_SESSION['anti-csrf'] = random_bytes(33);
}