<?php

require 'vendor/autoload.php';
require 'config.php';

use RamSources\ResourceLoaders\ResourceLoader;
use Slim\Http\Request;
use Slim\Http\Response;

$r = new ResourceLoader($dbconfig);
$app = new \Slim\App();

$app->get('/api/v1/resources', function (Request $request, Response $response, $args) use ($r) {
  $response->withStatus(200);
  $response->getBody()->write(json_encode($r->getResources()));
  $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
  return $newResponse;
});

$app->get('/api/v1/resource/{id}', function (Request $request, Response $response, $args) use ($r) {
  $id = $args['id'];
  $response->withStatus(200);
  $response->getBody()->write(json_encode($r->getResources($id)));
  $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
  return $newResponse;
});

$app->get('/api/v1/resource/type/{type}', function(Request $request, Response $response, $args) use ($r) {
  $t = $args['type'];
  $response->withStatus(200);
  $response->getBody()->write(json_encode($r->getResourceByType($t)));
  $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
  return $newResponse;
});

$app->get('/api/v1/building/{bid}', function(Request $request, Response $response, $args) use ($r) {
  $bid = $args['bid'];
  $response->withStatus(200);
  $response->getBody()->write(json_encode($r->getResourceByBuilding($bid)));
  $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
  return $newResponse;
});

$app->run();