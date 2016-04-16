<?php
/**
 * RamSources building routes. 
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
use RamSources\Controllers\ResourceController;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Each route is going to return JSON data and the proper status code. 
 */

$app->group('/building', function() use ($app, $container) {
  $r = $container['resources'];   //Start our resource class. 
  /**
   * GET /v1/building/all
   */
  $app->get('/all', function(Request $request, Response $response, $args) use ($r) {
    $response->withStatus(200);
    $response->getBody()->write(json_encode($r->getBuildings()));   //return all buildings. 
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  
  /**
   * GET /v1/building/bid/{bid}
   */
  $app->get('/bid/{bid}', function(Request $request, Response $response, $args) use ($r) {
    $bid = $args['bid'];    //building id. 
    $response->withStatus(200);
    $response->getBody()->write(json_encode($r->getResourceByBuilding($bid)));    //get all resources by building
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
}); //end /building
