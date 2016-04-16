<?php
/**
 * RamSources building routes.
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
$app->group('/rating', function() use ($app, $container) {
  $r = $container['ratings'];
  /**
   * POST /v1/rating/new
   */
  $app->post('/new', function (Request $request, Response $response, $args) use ($r) {
    $parsedData = $request->getParsedBody();
    //make a new rating. 
    $updateResponse = $r->setRating($parsedData);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  /**
   * GET /v1/rating/rid/{rid}
   */
  $app->get('/rid/{rid}', function (Request $request, Response $response, $args) use ($r) {
    $rid = $args['rid'];
    //get detailed data about a rating. 
    $updateResponse = $r->getRatingDetail($rid);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
});