<?php

namespace RamSources\User;
use RamSources\Utils\Database;
use RamSources\Utils\Logging;
use PHPMailer;


class RamVerification {


  private $db;
  private $log;
  private $mail;
  private $hash;
  private $userInfo = array();

  function __construct($dbconfig) {

    $this->db = new Database($dbconfig);
    $this->log = new Logging();
    $this->mail = new PHPMailer();

    $this->mail->Host = 'localhost';
    $this->mail->Port = 587;
    $this->mail->setFrom('no-reply@ramsources.com', 'Ramsources Email Validation');
    $this->mail->isHTML(true);

  }
  function sendVerify($userInfo) {
    $this->userInfo = $userInfo;
    $this->hash = bin2hex(openssl_random_pseudo_bytes(4));
    $date = strtotime('now');

    $sql = "INSERT INTO `Verification` (verify_time, verify_hash, user_id, status) VALUES (:vdate, :vhash, :id ,:status)";
    try {
      $this->db->query($sql);
      $this->db->bind(':vdate', $date);
      $this->db->bind(':vhash', $this->hash);
      $this->db->bind(':id', $this->userInfo['id']);
      $this->db->bind(':status', 0);
      $this->db->execute();

      $this->sendVerifyMail();
    }
    catch(\PDOException $e) {
      $this->log->logError($e->getMessage());
    }
    catch(\Exception $e) {
      $this->log->logError($e->getMessage());
    }
  }

  private function sendVerifyMail() {
    $this->mail->addAddress($this->userInfo['email'], $this->userInfo['name']);
    $this->mail->addBCC('jtredlich@gmail.com', 'John Redlich');
    $this->mail->Subject = 'Ramsources Email Verification';
    $HTMLbody = $this->_createHTMLBody();
    $TXTbody = strip_tags($HTMLbody);
    $this->mail->Body = $HTMLbody;
    $this->mail->AltBody = $TXTbody;

    if (!$this->mail->send()) {
      throw new \Exception($this->mail->ErrorInfo);
    }
    else {
      echo "Sent";
    }
  }

  function getIdFromHash($hash) {
    $this->hash = $hash;
    $updateHash = "UPDATE `Verification` SET status=1 WHERE verify_hash=:hash";
    $getID = "SELECT user_id, status FROM `Verification` WHERE verify_hash = :hash";

    try {
      $this->db->beginTransaction();
      $this->db->query($getID);
      $this->db->bind(':hash', $this->hash);
      $this->db->execute();
      $userID = $this->db->single();

      $this->db->query($updateHash);
      $this->db->bind(':hash', $this->hash);
      $this->db->execute();
      $this->db->endTransaction();
      if ($userID['status'] == 0) {
        return $userID['user_id'];
      }
      else {
        $this->log->logError("Fail: Hash already used");
        return array('result' => 'Fail', "message" => "Hash already used");
      }
    }
    catch(\PDOException $e) {
      $this->db->cancelTransaction();
      echo $e->getMessage();
    }
  }

  private function _createHTMLBody() {
    $body = "<p>Hello {$this->userInfo['name']},<br/>Thank you for creating a RamSources account. RamSources is a service for SUNY Farmingdale students, we will need you to verify your account by either clicking the link below or copying it into a browser.<br/><a href='http://www.ramsources.com/app/userverify?id=$this->hash'>http://www.ramsources.com/app/userverify?id=$this->hash</a></p>";
    return $body;
  }

}