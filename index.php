<?php
require 'vendor/autoload.php';
require 'config.php';

use RamSources\Middleware\UserAuthMiddleware;
use RamSources\Middleware\AppAuthMiddleware;

$app = new \Slim\App();
$app->add(new AppAuthMiddleware($dbconfig));

$app->group('/v1', function() use ($app,$dbconfig) {

  require 'app/routes/resource_routes.php';
  require 'app/routes/building_routes.php';
  require 'app/routes/user_routes.php';
  require 'app/routes/comment_routes.php';

}); //end /v1

$app->run();