<?php

namespace RamSources\Middleware;

class UserAuthMiddleware {

  private $tokenAuth;
  private $u;
  private $c;

  function __construct($container) {
    $this->c = $container;
  }

  function __invoke($request, $response, $next) {
    $this->tokenAuth = $request->getHeader('Authorization');
    $this->u = $this->c['user'];
    $decodeAuth = $this->u->decodeHeader($this->tokenAuth[0]);
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
