<?php

require 'vendor/autoload.php';
require 'config.php';

use RamSources\ResourceLoaders\ResourceLoader;
use RamSources\User\RamUser;
use RamSources\ResourceLoaders\CommentLoader;
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
    $app->put('/update/id/{id}', function (Request $request, Response $response, $args) use ($r) {

      $parsedData = $request->getParsedBody();
      $updateResponse = $r->updateResource($parsedData);

      $response->withStatus(200);
      $response->getBody()->write(json_encode($updateResponse));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
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
    $app->get('/new/{user}/{pass}/{name}', function(Request $request, Response $response, $args) use ($u) {
      $user = $args['user'];
      $pass = $args['pass'];
      $rname = explode("-", $args['name']);
      $name = $rname[0] . " " . $rname[1];
      try {
        $u->createUser($user, $pass, $name);
        $response->withStatus(200);
        $status = ['result' => 'Success', 'message' => 'User has been created'];
        $response->getBody()->write(json_encode($status));
      }
      catch(PDOException $e) {
        $response->withStatus(500);
        $status = ['result' => 'Fail', 'message' => $e->getMessage()];
        $response->getBody()->write(json_encode($status));
        $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
        return $newresponce;
      }
    });
    $app->get('/login', function(Request $request, Response $response, $args) use ($u) {
      $authHeader = $request->getHeader('Authorization');
      $userInfo = $u->decodeHeader($authHeader[0]);
      $u->getUser($userInfo[0]);

      try {
        $u->verifyPass($userInfo[0], $userInfo[1]);
        $response->withStatus(200);
        $response->getBody()->write(json_encode($u->createUserToken()));
        $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
        return $newresponce;
      }
      catch (Exception $e) {
        $status = array('result' => 'Fail', 'message' => $e->getMessage());
        $response->withStatus(500);
        $response->getBody()->write(json_encode($status));
        $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
        return $newresponce;
      }
    });
    /*
    $app->get('/login/web', function(Request $request, Response $response, $args) use ($u) {
      include 'src/Templates/login.php';
      $response->getBody()->write($html);
    });
    */
  }); //end /user
  $app->group('/comment', function() use ($app, $dbconfig) {
    $c = new CommentLoader($dbconfig);
    $app->post('/new', function (Request $request, Response $response, $args) use ($c) {
      $parsedData = $request->getParsedBody();
      $updateResponse = $c->addComment($parsedData);

      $response->withStatus(200);
      $response->getBody()->write(json_encode($updateResponse));
      $newResponse = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newResponse;
    });
  });
}); //end /v1

$app->run();