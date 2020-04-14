<?php
namespace Soatok\FaqOff\Endpoints;

use Slim\App;
use Slim\Container;
use Soatok\AnthroKit\Auth\Endpoints\Authorize;
use Soatok\AnthroKit\Auth\Middleware\{
    AuthorizedUsersOnly,
    GuestsOnly
};
use Soatok\FaqOff\Middleware\AdminsOnly;

/** @var App $app */
/** @var Container $container */
$container = $app->getContainer();
$guestsOnly = new GuestsOnly($container);
$authOnly = new AuthorizedUsersOnly($container);
$adminsOnly = new AdminsOnly($container);

$app->group('/admin', function () use ($app, $container) {
    $app->any('/ajax/{action:[A-Za-z0-9\-_]+}', 'admin.ajax');
    $app->any('/account/{action:(?:edit|view)}/{id:[0-9]+}', 'admin.accounts');
    $app->any('/accounts', 'admin.accounts');
    $app->any('/author/{action:(?:edit|view)}/{id:[0-9]+}', 'admin.authors');
    $app->any('/authors', 'admin.authors');
    $app->any('/collection/{collection:[^/]+}/{action:[^/]+}/{entry:[^/]+}[/{extra:[^/]+}]', 'admin.entries');
    $app->any('/collection/{collection:[^/]+}/entries', 'admin.entries');
    $app->any('/collection/{collection:[^/]+}', 'admin.collections');
    $app->get('/collections', 'admin.collections');
    $app->any('/custom[/{action:[^/]+}]', 'admin.custom');
    $app->get('/invite-tree', 'admin.invitetree');
    $app->any('/notices[/{id:[0-9]+}]', 'admin.notices');
    $app->any('/settings[/{which:[^/]+}]', 'admin.settings');
    $app->any('/theme/{action:[^/]+}/[{id:[0-9]+}]', 'admin.themes');
    $app->any('/theme[/{action:[^/]+}]', 'admin.themes');
    $app->get('/themes', 'admin.themes');
    $app->get('/', 'admin.home');
    $app->get('', 'admin.home');
})->add($adminsOnly);

$app->group('/manage', function () use ($app, $container) {
    // Authenticated users only...
    $app->any('/ajax/{action:[A-Za-z0-9\-_]+}', 'manage.ajax');
    $app->any('/collection/{collection:[0-9]+}/entry/{entry:[0-9]+}[/{action:[a-z]+}]', 'manage.entry');
    $app->any('/collection/{collection:[0-9]+}/{action:entry}/{create:create}', 'manage.entry');
    $app->any('/collection/{id:[0-9]+}[/{action:[a-z]+}]', 'manage.collections');
    $app->any('/collections', 'manage.collections');
    $app->any('/collection', 'manage.collections');
    $app->any('/author/{action:create}', 'manage.author');
    $app->any('/author/{id:[0-9]+}/{action:[^/]+}[/{sub:[^/]+}]', 'manage.author');
    $app->any('/author/{id:[0-9]+}', 'manage.author');
    $app->any('/authors', 'manage.author');
    $app->any('/author', 'manage.author');
    $app->any('/invite/{action:create}', 'manage.invite');
    $app->any('/invite', 'manage.invite');
    $app->get('/inbox', 'manage.questions');
    $app->get('/', 'manage');
    $app->get('', 'manage');
})->add($authOnly);

$app->any('/@{author:[^/]+}/{collection:[^/]+}/[{entry:[^/]+}]', 'entry');
$app->any('/@{author:[^/]+}/{collection:[^/]+}', 'collection');
$app->get('/@{author:[^/]+}[/]', 'author');
// Only authenticated users can logout:
$app->any('/auth/{action:logout}[/{extra:[^/]+}]', 'authorize')
    ->add($authOnly);
// Only guests can do this:
$app->any('/auth/{action:register|invite|login|twitter|verify}[/{extra:[^/]+}]', 'authorize')
    ->add($guestsOnly);
// No middleware on activation:
$app->any('/auth/{action:activate}[/{extra:[^/]+}]', 'authorize');
$app->any('/generic-error[/{error:[^/]+}]', 'error');
$app->get('/authors', 'author');
$app->get('/', 'homepage');
$app->get('', 'homepage');


$container['admin.ajax'] = function (Container $c) {
    return new Admin\AJAX($c);
};
$container['admin.accounts'] = function (Container $c) {
    return new Admin\Accounts($c);
};
$container['admin.authors'] = function (Container $c) {
    return new Admin\Authors($c);
};
$container['admin.collections'] = function (Container $c) {
    return new Admin\Collections($c);
};
$container['admin.custom'] = function (Container $c) {
    return new Admin\CustomContent($c);
};
$container['admin.entries'] = function (Container $c) {
    return new Admin\Entries($c);
};
$container['admin.home'] = function (Container $c) {
    return new Admin\HomePage($c);
};
$container['admin.invitetree'] = function (Container $c) {
    return new Admin\InviteTree($c);
};
$container['admin.settings'] = function (Container $c) {
    return new Admin\Settings($c);
};
$container['admin.notices'] = function (Container $c) {
    return new Admin\Notices($c);
};
$container['admin.themes'] = function (Container $c) {
    return new Admin\Themes($c);
};


$container['manage'] = function (Container $c) {
    return new Manage\ControlPanel($c);
};
$container['manage.ajax'] = function (Container $c) {
    return new Manage\AJAX($c);
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
$container['manage.questions'] = function (Container $c) {
    return new Manage\Questions($c);
};
$container['manage.entry'] = function (Container $c) {
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
$container['error'] = function (Container $c) {
    return new GenericError($c);
};
$container['homepage'] = function (Container $c) {
    return new HomePage($c);
};
$container['staticpage'] = function (Container $c) {
    return new StaticPage($c);
};

$container['notFoundHandler'] = function (Container $c) {
    return new StaticPage($c);
};