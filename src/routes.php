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

$app->get('/@{author:[^/]+}/{collection:[^/]+}/[{entry:[^/]+}]', 'entry');
$app->get('/@{author:[^/]+}/{collection:[^/]+}', 'collection');
$app->get('/@{author:[^/]+}', 'collection');
$app->any('/auth/{action:[^/]+}[/{extra:[^/]+}]', 'authorize');
$app->get('/', 'staticpage');
$app->get('', 'staticpage');

$container['author'] = function (Container $c) {
    return new Author($c);
};
$container['collection'] = function (Container $c) {
    return new EntryCollection($c);
};
$container['entry'] = function (Container $c) {
    return new Entry($c);
};
$container['staticpage'] = function (Container $c) {
    return new StaticPage($c);
};
$container['authorize'] = function (Container $c) {
    return new Authorize($c);
};

$container['notFoundHandler'] = function (Container $c) {
    return new StaticPage($c);
};