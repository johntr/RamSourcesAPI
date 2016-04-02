<?php

use RamSources\User\RamUser;
use RamSources\User\RamVerification;
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/user', function() use ($app, $dbconfig) {
  $u = new RamUser($dbconfig);
  $v = new RamVerification($dbconfig);
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
  $app->get('/test', function(Request $request, Response $response, $args) use ($u, $v) {
    //$userInfo = array('name' =>"John", 'id' => 1, 'email' => 'jtredlich@gmail.com');
    $id = $v->getIdFromHash('ed74fb06');
    if (!is_array($id)) {
      $u->verifyUser($id);
    }
    else {
      print_r($id);
    }
  });
  /*
  $app->get('/login/web', function(Request $request, Response $response, $args) use ($u) {
    include 'src/Templates/login.php';
    $response->getBody()->write($html);
  });
  */
}); //end /user