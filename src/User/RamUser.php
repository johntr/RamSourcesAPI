<?php

namespace RamSources\User;

class RamUser {

  private $user;
  private $password;
  private $name;
  private $token;
  private $tokenExp;
  private $verified;

  private $cost = 10;
  private $db;
  private $log;
  private $emailV;

  /**
   * RamUser constructor. Pass the DI container to class.
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

    if($this->checkDupUser($this->user)) {
      return array("result"=>"Fail", "message" => "User with email $user already registered");
    }
    else {
      $sql = "INSERT INTO `RamUsers` (user, pass, name) VALUES (:user, :pass, :name)";
      $this->db->query($sql);
      try {
        $hashedpass = $this->_create_hash();
        $this->db->bind(':user', $this->user);
        $this->db->bind(':pass', $hashedpass);
        $this->db->bind(':name', $this->name);
        $this->db->execute();
        $id = $this->db->lastInsertId();
        //start user email verification.
        $userInfo = array(
          'id' => $id,
          'name' => $this->name,
          'email' => $this->user
        );
        $this->emailV->sendVerify($userInfo);
        $this->log->logNotification($userInfo);
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
   * Function to verify user's email address
   * @param $id
   * @return array - array with message info back.
   */
  public function verifyUser($id) {

    $sql = "UPDATE `RamUsers` SET email_verified = '1' WHERE id = :id";
    try {
      $this->db->query($sql);
      $this->db->bind(':id', $id);
      $this->db->execute();
      return array('result' => 'Success', 'message' => 'User Verified');
    }
    catch(\PDOException $e) {
      echo $e->getMessage();
    }
  }

  /**
   * Function that returns user info in an array.
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
   * Function returns users name based on their id.
   * @param $id
   * @return string
   */
  public function getUserName($id) {
    $sql = "SELECT name FROM `RamUsers` WHERE id = :id";

    try {
      $this->db->query($sql);
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
    $token = $this->_generate_token();
    $tokenexp = date('Y-m-d H:i:s', strtotime('+1 year'));
    if($this->verified) {
      $sql = "UPDATE `RamUsers` SET token = :token, token_exp = :token_exp WHERE user = :user";
      $this->db->query($sql);
      $this->db->bind(':token', $token);
      $this->db->bind(':token_exp', $tokenexp);
      $this->db->bind(':user', $this->user);
      $this->db->execute();
      //@TODO Return more user info here and key value that shit.
      return array($this->user, $token);
    }
    else {
      return array('result'=>'Fail', "message" => "User password not verified");
    }
  }

  /**
   * Check if a user token is valid. A valid token is not expired or matches a user's token.
   * @param $t
   * @return bool
   */
  public function verifyToken($t) {
    $today = strtotime('now');
    $currentexp = strtotime($this->tokenExp);
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
    $sql = "SELECT pass FROM `RamUsers` WHERE user = :user LIMIT 1";
    $this->db->query($sql);
    $this->db->bind(':user', $tryUser);
    $this->db->execute();
    $userPass = $this->db->single();

    if (hash_equals($userPass['pass'], crypt($tryPass, $userPass['pass']))) {
      $this->verified = TRUE;
    } else {
      $this->verified = FALSE;
      throw new \Exception("Wrong Username or Password");
    }
  }
  
  public function checkDupUser($user) {
    $sql = "SELECT 1 FROM `RamUsers` WHERE user = :user";

    try {
      $this->db->query($sql);
      $this->db->bind(':user', $user);
      $this->db->execute();
      return $this->db->single();

    } catch(\PDOException $e) {

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
    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
    $salt = sprintf("$2a$%02d$", $this->cost) . $salt;
    return crypt($this->password, $salt);
  }
}