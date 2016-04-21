<?php
/**
 * RamSources comment routes.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
use Slim\Http\Request;
use Slim\Http\Response;
/**
 * Each route is going to return JSON data and the proper status code.
 */
$app->group('/comment', function() use ($app, $container) {
  $c = $container['comments'];    //get these comments started.
  /**
   * POST /v1/comment/new
   */
  $app->post('/new', function (Request $request, Response $response, $args) use ($c) {
    $parsedData = $request->getParsedBody();  //get the body passed to route
    //if we got one pass it to controller.
    if (!empty($parsedData['comment'])) {
      $updateResponse = $c->addComment($parsedData);
      $response->withStatus(200);
      $response->getBody()->write(json_encode($updateResponse));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
    }
    //Hey, where is my comment?!
    else {
      $response->withStatus(400);
      $response->getBody()->write(json_encode(array("result" => 'Failure', "message" => "Empty comments are not accepted.")));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
    }
  })->add($container['user_middle']);

  /**
   * GET /v1/comment/id/{id}
   */
  $app->get('/id/{id}', function (Request $request, Response $response, $args) use ($c) {
    $id = $args['id'];
    //get comments for a resource. This may not be super clear based on the route.
    $updateResponse = $c->getCommentsByResource($id);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });

  /**
   * DELETE /v1/comment/delete/{id}
   */
  $app->delete('delete/{id}', function (Request $request, Response $response, $args) use ($c) {
    $id = $args['id'];
    //remove a single comment. 
    $deleteResponse = $c->removeComment($id);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($deleteResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  })->add($container['user_middle']);
});