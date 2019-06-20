<?php
namespace Soatok\FaqOff\Endpoints;

use Slim\App;
use Slim\Container;
use Soatok\AnthroKit\Auth\Endpoints\Authorize;
use Soatok\AnthroKit\Auth\Middleware\{
    AuthorizedUsersOnly,
    GuestsOnly
};

/** @var App $app */

    /** @var Container $container */
    $container = $app->getContainer();
    $guestsOnly = new GuestsOnly($container);
    $authOnly = new AuthorizedUsersOnly($container);

    $app->any('/auth/{action:[^/]+}[/{extra:[^/]+}]', 'authorize');
    $app->get('/', 'staticpage');
    $app->get('', 'staticpage');

    $container['staticpage'] = function (Container $c) {
        return new StaticPage($c);
    };
    $container['authorize'] = function (Container $c) {
        return new Authorize($c);
    };

    $container['notFoundHandler'] = function (Container $c) {
        return new StaticPage($c);
    };