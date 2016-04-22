<?php
/**
 * User Verification clas. Manages School email verification, Sends welcome email and processes verifications. 
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\User;

use PHPMailer;

class RamVerification {


  private $db;
  private $log;

  /** @var  \RamSources\Utils\Mailer $mail */
  private $mail;
  private $hash;
  private $userInfo = array();

  function __construct($container) {

    $this->db = $container['database'];
    $this->log = $container['logs'];
    $this->mail = $container['mailer'];

  }

  /**
   * Generates random bytes for verify hash and emails verification link to user. 
   * @param $userInfo  array of user data.
   */
  function sendVerify($userInfo) {
    $this->userInfo = $userInfo;
    //generate hash for email. @TODO should be own function. 
    $this->hash = bin2hex(openssl_random_pseudo_bytes(4));
    //generate time to save hash against. 
    $date = strtotime('now');
    //save hash to db. 
    $sql = "INSERT INTO `Verification` (verify_time, verify_hash, user_id, status) VALUES (:vdate, :vhash, :id ,:status)";
    try {
      $this->db->query($sql);
      $this->db->bind(':vdate', $date);
      $this->db->bind(':vhash', $this->hash);
      $this->db->bind(':id', $this->userInfo['id']);
      $this->db->bind(':status', 0);
      $this->db->execute();
      //send out email. 
      $this->sendVerifyMail();
    }
    catch(\PDOException $e) {
      $this->log->logError($e->getMessage());
    } 
    catch(\Exception $e) {
      //In case we cannot send our email. 
      $this->log->logError($e->getMessage());
    }
  }

  /**
   * Sends email to user with hashed link. 
   * @throws \Exception
   * @throws \phpmailerException
   */
  private function sendVerifyMail() {
    //setup account to send email.
    $this->mail->addTo(array($this->userInfo['email']));
    $this->mail->addFrom();
    $this->mail->addSubject("RamSources Email Verification");
    $this->mail->addBody($this->_createHTMLBody());
    $this->mail->send();
//    $this->mail->addAddress($this->userInfo['email'], $this->userInfo['name']);
//    //admin email to keep track of functionality.
//    $this->mail->addBCC('jtredlich@gmail.com', 'John Redlich');
//    $this->mail->Subject = 'Ramsources Email Verification';
//    //generate email body based on user info.
//    $HTMLbody = $this->_createHTMLBody();
//    //in case they only use plain text. Who even does that anymore? Richard Stallman, that is who.
//    $TXTbody = strip_tags($HTMLbody);
//    $this->mail->Body = $HTMLbody;
//    $this->mail->AltBody = $TXTbody;
//    //send our mail.
//    if (!$this->mail->send()) {
//      //shit's broke.
//      throw new \Exception($this->mail->ErrorInfo);
//    }
//    else {
//      //log that we sent the email.
//      $this->log->logNotification("Sent email to {$this->userInfo['email']}.");
//    }
  }

  /**
   * Get the invalidate used hash and return the user id associated with hash. 
   * @param $hash
   * @return array returns user id or failed message.
   */
  function getIdFromHash($hash) {
    $this->hash = $hash;
    //check to see if we get a hash id.
    if(is_null($this->hash) || empty($this->hash)) {
      return array('result' => 'Fail', "message" => "No hash passed.");
    }
    //set verification hash as used. 
    $updateHash = "UPDATE `Verification` SET status=1 WHERE verify_hash=:hash";
    //update user account that it is verified. 
    $getID = "SELECT user_id, status FROM `Verification` WHERE verify_hash = :hash";

    try {
      $this->db->beginTransaction();  //start our db transaction since we have multi. queries. 
      $this->db->query($getID);   //get hash id. 
      $this->db->bind(':hash', $this->hash);
      $this->db->execute();
      $userID = $this->db->single();
      //now invalidate hash so it cannot be used again. //@TODO get new hash function. 
      $this->db->query($updateHash);
      $this->db->bind(':hash', $this->hash);
      $this->db->execute();
      $this->db->endTransaction();
      //if the hash hasen't been used return the user id. 
      if ($userID['status'] == 0) {
        return $userID['user_id'];
      }
      //let them know the hash was used. 
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

  /**
   * HTML body of verification email. 
   * @return string
   */
  private function _createHTMLBody() {
    $body = "<p>Hello {$this->userInfo['name']},<br/>Thank you for creating a RamSources account. RamSources is a service for SUNY Farmingdale students, we will need you to verify your account by either clicking the link below or copying it into a browser.<br/><a href='http://www.ramsources.com/userverify/index.html?id=$this->hash'>http://www.ramsources.com/app/userverify?id=$this->hash</a></p>";
    return $body;
  }

}