<?php
require_once "config.php";

use Pimple\Container;

$container = new Container();
//each of these is an annon function that creates our class. You know, what DOES Pimple do? 
$container['dbconfig'] = $dbconfig;
$container['mailkey'] = $dbconfig['mailkey'];

//Utils
$container['database'] = function ($c) {
  return new \RamSources\Utils\Database($c['dbconfig']);
};
$container['logs'] = function ($c) {
  return new \RamSources\Utils\Logging();
};
$container['mailer'] = function ($c) {
  return new \RamSources\Utils\Mailer($c);
};

//Middleware
$container['auth_middle'] = function ($c) {
  return new \RamSources\Middleware\AppAuthMiddleware($c);
};
$container['user_middle'] = function ($c) {
  return new \RamSources\Middleware\UserAuthMiddleware($c);
};

//Controllers
$container['resources'] = function ($c) {
  return new \RamSources\Controllers\ResourceController($c);
};
$container['comments'] = function ($c) {
  return new \RamSources\Controllers\CommentController($c);
};
$container['ratings'] = function ($c) {
  return new \RamSources\Controllers\RatingController($c);
};
$container['inventory'] = function ($c) {
  return new \RamSources\Controllers\InventoryController($c);
};

//Users
$container['user'] = $container->factory(function ($c) {
  return new \RamSources\User\RamUser($c);
});
$container['user_verify'] = $container->factory(function ($c) {
  return new \RamSources\User\RamVerification($c);
});