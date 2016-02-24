<?php

require 'vendor/autoload.php';
require 'config.php';

use RamSources\ResourceLoaders\ResourceLoader;

$r = new ResourceLoader($dbconfig);

$app = new \Slim\App();

$app->get('/', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) use ($r) {
  $response->withStatus(200);
  $response->getBody()->write(json_encode($r->getResources()));
  $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
  return $newResponse;
});

$app->get('/resource/{id}', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) use ($r) {
  $id = $args['id'];
  $response->withStatus(200);
  $response->getBody()->write(json_encode($r->getResources($id)));
  $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
  return $newResponse;
});

$app->get('/building/{bid}', function(\Slim\Http\Request $request, \Slim\Http\Response $response, $args) use ($r) {
  $bid = $args['bid'];
  $response->withStatus(200);
  $response->getBody()->write(json_encode($r->getResourceByBuilding($bid)));
  $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
  return $newResponse;
});

$app->run();