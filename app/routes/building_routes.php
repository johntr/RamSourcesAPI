<?php

use RamSources\ResourceLoaders\ResourceLoader;
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/building', function() use ($app, $dbconfig) {
  $r = new ResourceLoader($dbconfig);
  $app->get('/all', function(Request $request, Response $response, $args) use ($r) {

    $response->withStatus(200);
    $response->getBody()->write(json_encode($r->getBuildings()));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  $app->get('/bid/{bid}', function(Request $request, Response $response, $args) use ($r) {
    $bid = $args['bid'];
    $response->withStatus(200);
    $response->getBody()->write(json_encode($r->getResourceByBuilding($bid)));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
}); //end /building
