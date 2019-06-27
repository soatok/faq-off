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

$app->group('/manage', function () use ($app, $container) {
    // Authenticated users only...
    $app->get('/collection/{collection:[0-9]+}/entry/{entry:[0-9]+}[/{action:[a-z]+}]', 'manage.entry');
    $app->get('/collection/{id:[0-9]+}/entry/create', 'manage.create-entry');
    $app->get('/collection/{id:[0-9]+}[/{action:[a-z]+}]', 'manage.collections');
    $app->get('/author/{action:create}', 'manage.author');
    $app->get('/author/{id:[0-9]+}/{action:[^/]+}[/{sub:[^/]+}]', 'manage.author');
    $app->get('/author/{id:[0-9]+}', 'manage.author');
    $app->get('/authors', 'manage.author');
    $app->get('/invite', 'manage.invite');
    $app->get('/', 'manage');
    $app->get('', 'manage');
})->add($authOnly);

$app->get('/@{author:[^/]+}/{collection:[^/]+}/[{entry:[^/]+}]', 'entry');
$app->get('/@{author:[^/]+}/{collection:[^/]+}', 'collection');
$app->get('/@{author:[^/]+}', 'collection');
// Only authenticated users can logout:
$app->any('/auth/{action:logout}[/{extra:[^/]+}]', 'authorize')
    ->add($authOnly);
// Only guests can do this:
$app->any('/auth/{action:register|login|twitter|verify}[/{extra:[^/]+}]', 'authorize')
    ->add($guestsOnly);
// No middleware on activation:
$app->any('/auth/{action:activate}[/{extra:[^/]+}]', 'authorize');
$app->get('/', 'staticpage');
$app->get('', 'staticpage');


$container['manage'] = function (Container $c) {
    return new Manage\ControlPanel($c);
};
$container['manage.author'] = function (Container $c) {
    return new Manage\Author($c);
};
$container['manage.invite'] = function (Container $c) {
    return new Manage\Invite($c);
};
$container['manage.collections'] = function (Container $c) {
    return new Manage\Collections($c);
};
$container['manage.entry'] = function (Container $c) {
    return new Manage\Entries($c);
};
$container['manage.create-entry'] = function (Container $c) {
    return new Manage\Entries($c);
};

$container['authorize'] = function (Container $c) {
    return new Authorize($c);
};
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

$container['notFoundHandler'] = function (Container $c) {
    return new StaticPage($c);
};