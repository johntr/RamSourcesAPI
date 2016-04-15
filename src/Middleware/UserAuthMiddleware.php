<?php
/**
 * User token verification. This will be called on select routes.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\Middleware;

class UserAuthMiddleware {

  private $tokenAuth;
  private $u;
  private $c;

  /**
   * UserAuthMiddleware constructor.
   * @param $container
   */
  function __construct($container) {
    $this->c = $container;
  }

  /**
   * checks to see if user passes valid token. A valid token has not expired and matches token in db for user.
   * @param $request
   * @param $response
   * @param $next
   * @return mixed
   */
  function __invoke($request, $response, $next) {
    //get authorization header
    $this->tokenAuth = $request->getHeader('Authorization');
    //Make a new user class.
    $this->u = $this->c['user'];
    //Get token from header.
    $decodeAuth = $this->u->decodeHeader($this->tokenAuth[0]);
    //Get user info
    $this->u->getUser($decodeAuth[0]);
    //check to see if token is valid and move on.
    if($this->u->verifyToken($decodeAuth[1])) {
      $response = $next($request, $response);
      return $response;
    }
    else {
      //return with error if user is not valid. 
      $response = $response->withStatus(401);
      return $response;
    }
  }
}
