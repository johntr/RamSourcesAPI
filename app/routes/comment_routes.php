<?php

use RamSources\Controllers\CommentController;
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/comment', function() use ($app, $dbconfig) {
  $c = new CommentController($dbconfig);
  $app->post('/new', function (Request $request, Response $response, $args) use ($c) {
    $parsedData = $request->getParsedBody();
    $updateResponse = $c->addComment($parsedData);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  $app->get('/id/{id}', function (Request $request, Response $response, $args) use ($c) {
    $id = $args['id'];
    $updateResponse = $c->getCommentsByResource($id);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
});