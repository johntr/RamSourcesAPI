<?php

namespace RamSources\Middleware;

class AppAuthMiddleware {

  private $serverToken;
  private $tokenAuth;

  function __construct($dbconfig) {
    $this->serverToken = $dbconfig['token'];
  }

  function __invoke($request, $response, $next) {
    $this->tokenAuth = $request->getHeader('tokenAuth');

    if($this->tokenAuth[0] == $this->serverToken) {
      $response = $next($request, $response);
      return $response;
    }
    else {
      $response = $response->withStatus(401);
      return $response;
    }
  }
}