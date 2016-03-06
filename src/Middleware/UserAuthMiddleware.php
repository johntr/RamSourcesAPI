<?php

namespace RamSources\Middleware;

use \RamSources\User\RamUser;

class UserAuthMiddleware {

  private $tokenAuth;
  private $u;
  private $dbconfig;

  function __construct($dbconfig) {
    $this->dbconfig = $dbconfig;
  }

  function __invoke($request, $response, $next) {
    $this->tokenAuth = $request->getHeader('Authorization');
    $decodeAuth = explode(':',base64_decode(substr($this->tokenAuth[0], 6)));
    $this->u = new RamUser($this->dbconfig);
    $this->u->getUser($decodeAuth[0]);
    if($this->u->verifyToken($decodeAuth[1])) {
      $response = $next($request, $response);
      return $response;
    }
    else {
      $response = $response->withStatus(401);
      return $response;
    }
  }
}
