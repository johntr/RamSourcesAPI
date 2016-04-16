<?php
/**
 * RamSources Index file. Everything is going to start here! 
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
require 'vendor/autoload.php';
require 'container.php';


$app = new \Slim\App();
//require some authentication before we do anything.
$app->add($container['auth_middle']);

//version 1 of our API. 
$app->group('/v1', function() use ($app, $container) {
  //we are going to break all of our route groups to their own file for neatness. 
  require 'app/routes/resource_routes.php';
  require 'app/routes/building_routes.php';
  require 'app/routes/user_routes.php';
  require 'app/routes/comment_routes.php';
  require 'app/routes/rating_routes.php';

}); //end /v1

$app->run();