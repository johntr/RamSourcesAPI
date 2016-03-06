<?php

namespace RamSources\User;
use RamSources\Database\Database;

class RamUser {

  private $user;
  private $password;
  private $name;
  private $token;
  private $tokenExp;
  private $verified;

  private $cost = 10;
  private $db;

  function __construct($dbconfig) {
    $this->db = new Database($dbconfig);
  }

  public function createUser($user, $password, $name = NULL) {
    $this->user = $user;
    $this->password = $password;
    $this->name = $name;

    $sql = "INSERT INTO `RamUsers` (user, pass, name) VALUES (:user, :pass, :name)";
    $this->db->query($sql);
    $hashedpass = $this->_create_hash();
    $this->db->bind(':user', $this->user);
    $this->db->bind(':pass', $hashedpass);
    $this->db->bind(':name', $this->name);
    $this->db->execute();
    return $this->db->lastInsertId();

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
      //print_r($userInfo);
      $this->user = $userInfo[0]['user'];
      $this->password = $userInfo[0]['pass'];
      $this->name = $userInfo[0]['name'];
      $this->token = $userInfo[0]['token'];
      $this->tokenExp = $userInfo[0]['token_exp'];
    } else {
      throw new \Exception("No User Returned");
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
    $sql = "SELECT pass FROM `Ramusers` WHERE user = :user LIMIT 1";
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

  private function _generate_token() {
    return bin2hex(openssl_random_pseudo_bytes(16));
  }

  private function _create_hash() {
    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_RANDOM)), '+', '.');
    $salt = sprintf("$2a$%02d$", $this->cost) . $salt;
    return crypt($this->password, $salt);
  }
}