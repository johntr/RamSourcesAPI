<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/comment', function() use ($app, $container) {
  $c = $container['comments'];
  
  $app->post('/new', function (Request $request, Response $response, $args) use ($c) {
    $parsedData = $request->getParsedBody();

    if (!empty($parsedData['comment'])) {
      $updateResponse = $c->addComment($parsedData);
      $response->withStatus(200);
      $response->getBody()->write(json_encode($updateResponse));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
    }
    else {
      $response->withStatus(400);
      $response->getBody()->write(json_encode(array("result" => 'Failure', "message" => "Empty comments are not accepted.")));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
    }
  });
  
  $app->get('/id/{id}', function (Request $request, Response $response, $args) use ($c) {
    $id = $args['id'];
    $updateResponse = $c->getCommentsByResource($id);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  
  $app->delete('delete/{id}', function (Request $request, Response $response, $args) use ($c) {
    $id = $args['id'];
    $deleteResponse = $c->removeComment($id);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($deleteResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
});