<?php
/**
 * User manager class. Handles user creation, authentication, login and token generation, and returns users.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\User;

class RamUser {
  private $id;
  private $user;
  private $password;
  private $name;
  private $token;

  private $tokenExp;
  private $verified;

  private $cost = 10; //Bcyrpt cost
  private $db;
  private $log;
  private $emailV;

  /**
   * RamUser constructor. Passes the DI container to class.
   * @param $container
   */
  function __construct($container) {
    $this->db = $container['database'];
    $this->emailV = $container['user_verify'];
    $this->log = $container['logs'];
  }

  /**
   * This method will check for a duplicate user and create a new user if it doesn't exist.
   * @param $user  - user email
   * @param $password - password
   * @param null $name - optional user name.
   * @return array - array with message info back.
   */
  public function createUser($user, $password, $name = NULL) {
    $this->user = $user;
    $this->password = $password;
    $this->name = $name;
    //check to see if this user exists
    if($this->checkDupUser($this->user)) {
      return array("result"=>"Fail", "message" => "User with email $user already registered");
    }
    else {
      $sql = "INSERT INTO `RamUsers` (user, pass, name) VALUES (:user, :pass, :name)";
      $this->db->query($sql);
      try {
        //create password hash.
        $hashedpass = $this->_create_hash();
        //bind user attributes to query
        $this->db->bind(':user', $this->user);
        $this->db->bind(':pass', $hashedpass);
        $this->db->bind(':name', $this->name);
        //run it
        $this->db->execute();
        //get user id
        $id = $this->db->lastInsertId();
        //start user email verification.
        $userInfo = array(
          'id' => $id,
          'name' => $this->name,
          'email' => $this->user
        );
        //send an email
        $this->emailV->sendVerify($userInfo);
        $this->log->logNotification($userInfo);
        //setup return array
        $status = array ('result' => 'Success', 'message' => "User {$this->name} has been created");
        return $status;
      } catch (\PDOException $e) {
        $return = array(
          'result' => 'Fail',
          "message" => $e->getMessage()
        );
        $this->log->logError($return);
        return $return;
      }
    }
  }

  /**
   * Function to verify user's email address. Flags their account as verified in the db. *** Usually called after returning the hash user id.***
   * @param $id
   * @return array - array with message info back.
   */
  public function verifyUser($id) {
    //create query to update the email_verified field as 1 by id.
    $sql = "UPDATE `RamUsers` SET email_verified = '1' WHERE id = :id";
    try {
      $this->db->query($sql);
      $this->db->bind(':id', $id);
      $this->db->execute();
      //let users know they are verified.
      return array('result' => 'Success', 'message' => 'User Verified');
    }
    catch(\PDOException $e) {
      echo $e->getMessage();
    }
  }

  /**
   * Sets user information to instance of the object for further function use.
   * @param $user
   * @throws \Exception
   */
  public function getUser($user) {
    $sql = "SELECT * FROM `RamUsers` WHERE user = :user";
    try{
      $this->db->query($sql);
      $this->db->bind(':user', $user);
      $this->db->execute();
      $userInfo = $this->db->results();
    }
    catch(\PDOException $e) {
      echo $e;
    }
    if(isset($userInfo)) {
      $this->id = $userInfo[0]['id'];
      $this->user = $userInfo[0]['user'];
      $this->password = $userInfo[0]['pass'];
      $this->name = $userInfo[0]['name'];
      $this->token = $userInfo[0]['token'];
      $this->tokenExp = $userInfo[0]['token_exp'];
    } else {
      throw new \Exception("No User Returned");
    }
  }

  /**
   * Function returns string of user's name based on their id.
   * @param $id
   * @return string
   */
  public function getUserName($id) {
    $sql = "SELECT name FROM `RamUsers` WHERE id = :id";

    try {
      //get user name based on it.
      $this->db->query($sql);
      //bind id to query
      $this->db->bind(':id', $id);
      $this->db->execute();
      return $this->db->single();
    }
    catch (\PDOException $e) {
      //@TODO Log error
      return "---";
    }
  }

  /**
   * Generates user token with exp date. returns the id and generated token of the user.
   * @return array
   */
  public function createUserToken() {
    //get a new token
    $token = $this->_generate_token();
    //get an experation date for the token. For now it is a year.
    $tokenexp = date('Y-m-d H:i:s', strtotime('+1 year'));
    //check to see if the user has logged in with their password.
    if($this->verified) {
      //now setup query to pass token and exp date to db query.
      $sql = "UPDATE `RamUsers` SET token = :token, token_exp = :token_exp WHERE user = :user";
      $this->db->query($sql);
      //bind info to query
      $this->db->bind(':token', $token);
      $this->db->bind(':token_exp', $tokenexp);
      $this->db->bind(':user', $this->user);
      $this->db->execute();

      //Return user info based on login.
      return array('id'=>$this->id,'user'=>$this->user,'token'=> $token,'name'=>$this->name);
    }
    else {
      //failure message if user is not password verified.
      return array('result'=>'Fail', "message" => "User password not verified");
    }
  }

  /**
   * Check if a user token is valid. A valid token is not expired and matches the user's token.
   * @param $t
   * @return bool
   */
  public function verifyToken($t) {
    //get today's date
    $today = strtotime('now');
    //get token exp date
    $currentexp = strtotime($this->tokenExp);
    //if token is equal and not out of date.
    if($t == $this->token && $currentexp > $today) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * decodes basic HTTP auth.
   * @param $header
   * @return mixed
   */
  public function decodeHeader($header) {
    return explode(':',base64_decode(substr($header, 6)));
  }

  /**
   * Verifies user password.
   * @param $tryUser
   * @param $tryPass
   * @throws \Exception
   */
  public function verifyPass($tryUser, $tryPass) {
    //get user and password
    $sql = "SELECT pass, email_verified FROM `RamUsers` WHERE user = :user LIMIT 1";
    $this->db->query($sql);
    $this->db->bind(':user', $tryUser);
    $this->db->execute();
    $userPass = $this->db->single();
    if($userPass['email_verified'] == 0) {
      throw new \Exception("User needs to verify email address.");
    }
    //check to see if user's password matches.
    if (hash_equals($userPass['pass'], crypt($tryPass, $userPass['pass']))) {
      //set verified flag if passes
      $this->verified = TRUE;
    } else {
      $this->verified = FALSE;
      throw new \Exception("Wrong Username or Password");
    }
  }

  /**
   * Function check to see if user already created in db.
   * @param $user
   * @return mixed
   */
  public function checkDupUser($user) {
    //return 1 if the user exists
    $sql = "SELECT 1 FROM `RamUsers` WHERE user = :user";

    try {
      $this->db->query($sql);
      //bind user to query
      $this->db->bind(':user', $user);
      $this->db->execute();
      return $this->db->single();

    } catch(\PDOException $e) {
      //@TODO log error.
    }
  }
  /**
   * Generates a token to be associated with a user.
   * @return mixed
   */
  private function _generate_token() {
    return bin2hex(openssl_random_pseudo_bytes(16));
  }

  /**
   * Hashes user's password for storage in the database. 
   * @return mixed
   */
  private function _create_hash() {
    //uses dev/urandom to generate salt then encrpyts password using bcrypt php module.
    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
    $salt = sprintf("$2a$%02d$", $this->cost) . $salt;
    return crypt($this->password, $salt);
  }
}