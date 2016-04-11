<?php
require 'vendor/autoload.php';
require 'container.php';


$app = new \Slim\App();
$app->add($container['auth_middle']);

$app->group('/v1', function() use ($app, $container) {

  require 'app/routes/resource_routes.php';
  require 'app/routes/building_routes.php';
  require 'app/routes/user_routes.php';
  require 'app/routes/comment_routes.php';
  require 'app/routes/rating_routes.php';

}); //end /v1

$app->run();