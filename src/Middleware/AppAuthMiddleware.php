<?php
/**
 * App token verification. Called on every route.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\Middleware;

class AppAuthMiddleware {

  private $serverToken;
  private $tokenAuth;

  /**
   * AppAuthMiddleware constructor.
   * @param $container
   */
  function __construct($container) {
    //get server verification token.
    $this->serverToken = $container['dbconfig']['token'];
  }

  /**
   * Called when route is hit. Rejects connection if token is wrong.
   * @param $request
   * @param $response
   * @param $next 
   * @return mixed
   */
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