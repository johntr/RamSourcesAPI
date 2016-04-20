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
$app->group('/user', function() use ($app, $container) {
  $u = $container['user'];    //start a new user class
  $v = $container['user_verify'];   //and its verification buddy.

  /**
   * GET /v1/user/login
   */
  $app->get('/login', function(Request $request, Response $response, $args) use ($u) {
    //get passes login info.
    $authHeader = $request->getHeader('Authorization');
    //decode login info.
    $userInfo = $u->decodeHeader($authHeader[0]);
    //get the user data.
    $u->getUser($userInfo[0]);

    try {
      //verifiy the pass and flag user verified.
      $u->verifyPass($userInfo[0], $userInfo[1]);
      $response->withStatus(200);
      //now pass back token info.
      $response->getBody()->write(json_encode($u->createUserToken()));
      $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newresponce;
    }
    catch (Exception $e) {
      //I don't think so buddy. That isn't the right info.
      $status = array('result' => 'Fail', 'message' => $e->getMessage());
      $response->withStatus(500);
      $response->getBody()->write(json_encode($status));
      $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newresponce;
    }
  });
  /**
   * POST /v1/user/new
   */
  $app->post('/new', function(Request $request, Response $response, $args) use ($u) {
    //get new user data and create a user
    $userDta = $request->getParsedBody();
    $user = $userDta['user'];   //user name aka email
    $pass = $userDta['pass'];   //user password
    $rname = explode("-", $userDta['name']);  //and lets get their name.
    $name = $rname[0] . " " . $rname[1];  //full name without -
    $userDomain = explode('@', $user);    //get the email domain so we can verifiy its farmingdale.
    //is it a farmingdale email?
    if ($userDomain[1] == "farmingdale.edu") {
      //if it is, create a new user.
      $message = $u->createUser($user, $pass, $name);
      $response->withStatus(200);
      $response->getBody()->write(json_encode($message));
    }
    else {
      //farmingdale only jerks.
      $response->withStatus(401);
      $status = ['result' => 'Fail', 'message' => 'RamSources for SUNY Farmingdale students only, Farmingdale.edu email required.'];
      $response->getBody()->write(json_encode($status));
      $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newresponce;
    }
  });
  /**
   * PUT /v1/user/userverify?id={hash}
   */
  $app->put('/userverify', function(Request $request, Response $response, $args) use ($u, $v) {
    $info = $request->getQueryParams();
    $hash = $info['id'];
    //get the user id based on the hash
    $idReturn = $v->getIdFromHash($hash);
    //if we find the id then pass that id to be verified.
    if (!is_array($idReturn)) {
      $message = $u->verifyUser($idReturn);

      $response->withStatus(200);
      $response->getBody()->write(json_encode($message));
      $newresponce = $response->withHeader('Content-type', 'application/json; charset=UTF-8');
      return $newresponce;
    }
    //or we cannot verify user.
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