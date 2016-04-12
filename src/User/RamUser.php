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

  function __construct($container) {
    $this->db = $container['database'];
    $this->emailV = $container['user_verify'];
    $this->log = $container['logs'];
  }

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

      return array($this->user, $token);
    }
    else {
      return array('result'=>'Fail', "message" => "User password not verified");
    }
  }

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

  public function decodeHeader($header) {
    return explode(':',base64_decode(substr($header, 6)));
  }

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
      throw new \Exception("Wrong Password");
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

  private function _generate_token() {
    return bin2hex(openssl_random_pseudo_bytes(16));
  }

  private function _create_hash() {
    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_RANDOM)), '+', '.');
    $salt = sprintf("$2a$%02d$", $this->cost) . $salt;
    return crypt($this->password, $salt);
  }
}