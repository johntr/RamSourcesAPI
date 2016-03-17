<?php

use RamSources\ResourceLoaders\ResourceLoader;
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/resource', function() use ($app, $dbconfig) {

  $r = new ResourceLoader($dbconfig);

  $app->get('/all', function (Request $request, Response $response, $args) use ($r) {
    $response->withStatus(200);
    $response->getBody()->write(json_encode($r->getResources()));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });


  $app->get('/id/{id}', function (Request $request, Response $response, $args) use ($r) {
    $id = $args['id'];
    $response->withStatus(200);
    $response->getBody()->write(json_encode($r->getResources($id)));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  $app->get('/type/{type}', function(Request $request, Response $response, $args) use ($r) {
    $t = $args['type'];
    try {
      $response->withStatus(200);
      $response->getBody()->write(json_encode($r->getResourceByType($t)));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
    } catch (Exception $e) {
      $new_response = $response->withStatus(400);
      $response->getBody()->write($e->getMessage());
      return $new_response;
    }
  });
  $app->get('/detail/id/{id}', function(Request $request, Response $response, $args) use ($r) {
    $id = $args['id'];

    $response->withStatus(200);
    $response->getBody()->write(json_encode($r->getResourceDetail($id)));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  $app->put('/update/id/{id}', function (Request $request, Response $response, $args) use ($r) {

    $parsedData = $request->getParsedBody();
    $updateResponse = $r->updateResource($parsedData);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
}); //end /resource
