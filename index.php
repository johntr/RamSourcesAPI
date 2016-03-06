<?php

require 'vendor/autoload.php';
require 'config.php';

use RamSources\ResourceLoaders\ResourceLoader;
use RamSources\User\RamUser;
use RamSources\Middleware\UserAuthMiddleware;
use RamSources\Middleware\AppAuthMiddleware;
use Slim\Http\Request;
use Slim\Http\Response;


$app = new \Slim\App();
$app->add(new AppAuthMiddleware($dbconfig));

$app->group('/v1', function() use ($app,$dbconfig) {
  $app->group('/resource', function() use ($app, $dbconfig) {

    $r = new ResourceLoader($dbconfig);

    $app->get('/all', function (Request $request, Response $response, $args) use ($r) {
      $response->withStatus(200);
      $response->getBody()->write(json_encode($r->getResources()));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
    })->add(new UserAuthMiddleware($dbconfig));


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
      }
      catch (Exception $e) {
        $new_response = $response->withStatus(400);
        $response->getBody()->write($e->getMessage());
        return $new_response;
      }
    });
  }); //end /resource

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

  $app->group('/user', function() use ($app, $dbconfig) {
    $u = new RamUser($dbconfig);
    $app->get('', function(Request $request, Response $response, $args) use ($u) {
      $auth = $request->getHeader('Authorization');
      $decodeAuth = explode(':',base64_decode(substr($auth[0], 6)));
      print_r($decodeAuth);
////  $u->createUser('jtredlich@gmail.com', 'test', 'John Redlich');
//      $u->getUser('jtredlich@gmail.com');
//      $u->verifyPass('jtredlich@gmail.com', 'test');
//      //$tokenInfo = $u->createUserToken();
//      $tokenInfo = $u->verifyToken('afa480035bbd463af11040fc3c551373') ? "Token is good" : "Token is bad";
////
//      $response->getBody()->write($tokenInfo);
    });
  }); //end /resource
}); //end /v1














$app->run();