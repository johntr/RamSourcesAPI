<?php
/**
 * RamSources resource routes.
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
$app->group('/resource', function() use ($app, $container) {

  $r = $container['resources'];   //start a new resource class.
  /**
   * GET /v1/resource/all
   */
  $app->get('/all', function (Request $request, Response $response, $args) use ($r) {
    $response->withStatus(200);
    //get all of the resources
    $response->getBody()->write(json_encode($r->getResources()));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  /**
   * GET /v1/resource/id/{id}
   */
  $app->get('/id/{id}', function (Request $request, Response $response, $args) use ($r) {
    $id = $args['id'];
    $response->withStatus(200);
    //get a single resource
    $response->getBody()->write(json_encode($r->getResources($id)));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  /**
   * GET /v1/resource/type/{type}
   */
  $app->get('/type/{type}', function(Request $request, Response $response, $args) use ($r) {
    $t = $args['type'];
    try {
      $response->withStatus(200);
      //get resources by type.
      $response->getBody()->write(json_encode($r->getResourceByType($t)));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
    } catch (Exception $e) {
      $new_response = $response->withStatus(400);
      $response->getBody()->write($e->getMessage());
      return $new_response;
    }
  });
  /**
   * GET /v1/resource/detail/id/{id}
   */
  $app->get('/detail/id/{id}', function(Request $request, Response $response, $args) use ($r) {
    $id = $args['id'];

    $response->withStatus(200);
    //get detail data about a resource
    $response->getBody()->write(json_encode($r->getResourceDetail($id)));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });
  /**
   * GET /v1/resource/report/id/{id}
   */
  $app->get('/report/id/{id}', function(Request $request, Response $response, $args) use ($r) {
    $id = $args['id'];

    $response->withStatus(200);
    //report a resource.
    $response->getBody()->write(json_encode($r->reportResource($id)));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  });

  /**
   * POST /v1/resource/new
   */
  $app->post('/new', function (Request $request, Response $response, $args) use ($r) {
    $parsedData = $request->getParsedBody();    //get body data.
    //add a new resource.
    $updateResponse = $r->addResource($parsedData);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  })->add($container['user_middle']);

  /**
   * PUT /v1/resource/update/id/{id}
   */
  $app->put('/update/id/{id}', function (Request $request, Response $response, $args) use ($r) {
    //get body data
    $parsedData = $request->getParsedBody();
    //pass that data and update the resource. 
    $updateResponse = $r->updateResource($parsedData);

    $response->withStatus(200);
    $response->getBody()->write(json_encode($updateResponse));
    $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
    return $newResponse;
  })->add($container['user_middle']);
}); //end /resource
