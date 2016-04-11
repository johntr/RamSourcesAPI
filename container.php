<?php
require_once "config.php";

use Pimple\Container;

$container = new Container();

$container['dbconfig'] = $dbconfig;

//Utils
$container['database'] = function ($c) {
  return new \RamSources\Utils\Database($c['dbconfig']);
};
$container['logs'] = function ($c) {
  return new \RamSources\Utils\Logging();
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
$container['user'] = function ($c) {
  return new \RamSources\User\RamUser($c);
};
$container['user_verify'] = function ($c) {
  return new \RamSources\User\RamVerification($c);
};