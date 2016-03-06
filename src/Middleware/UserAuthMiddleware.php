<?php

namespace RamSources\Middleware;

use \RamSources\User\RamUser;

class UserAuthMiddleware {

  private $tokenAuth;
  private $u;

  function __invoke($request, $responce, $next, $dbconfig ) {
    $this->tokenAuth = $this->app->request->getHeader('Authorization');
    $decodeAuth = explode(':',base64_decode(substr($this->tokenAuth[0], 6)));
    $this->u = new RamUser($dbconfig);
    $this->u->getUser($decodeAuth[0]);
    if($this->u->verifyToken($decodeAuth[1])) {
      $response = $next($request, $response);
      return $response;
    };
  }


  private function denyAccess() {
    $res = $this->app->responce();
    $res->status(401);
  }
}
