<?php

use RamSources\User\RamUser;
use RamSources\User\RamVerification;
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/user', function() use ($app, $container) {
  $u = $container['user'];
  $v = $container['user_verify'];


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

  $app->post('/new', function(Request $request, Response $response, $args) use ($u) {
    $userDta = $request->getParsedBody();
    $user = $userDta['user'];
    $pass = $userDta['pass'];
    $rname = explode("-", $userDta['name']);
    $name = $rname[0] . " " . $rname[1];
    $userDomain = explode('@', $user);

    if ($userDomain[1] == "farmingdale.edu") {
      $message = $u->createUser($user, $pass, $name);
      $response->withStatus(200);
      $response->getBody()->write(json_encode($message));
    }
    else {
      $response->withStatus(401);
      $status = ['result' => 'Fail', 'message' => 'RamSources for SUNY Farmingdale students only, Farmingdale.edu email required.'];
      $response->getBody()->write(json_encode($status));
      $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newresponce;
    }
  });

  $app->put('/userverify', function(Request $request, Response $response, $args) use ($u, $v) {
    $info = $request->getQueryParams();
    $hash = $info['id'];
    $idReturn = $v->getIdFromHash($hash);
    if (!is_array($idReturn)) {
      $message = $u->verifyUser($idReturn);

      $response->withStatus(200);
      $response->getBody()->write(json_encode($message));
      $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newresponce;
    }
    else {
      $response->withStatus(500);
      $response->getBody()->write(json_encode($idReturn));
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