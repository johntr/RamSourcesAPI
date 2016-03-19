<?php

use RamSources\ResourceLoaders\RatingLoader;
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/rating', function() use ($app, $dbconfig) {
  $r = new RatingLoader($dbconfig);
  $app->post('/new', function (Request $request, Response $response, $args) use ($r) {
    $parsedData = $request->getParsedBody();
    $updateResponse = $r->setRating($parsedData);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  $app->get('/rid/{rid}', function (Request $request, Response $response, $args) use ($r) {
    $rid = $args['rid'];
    $updateResponse = $r->getRatingDetail($rid);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
});