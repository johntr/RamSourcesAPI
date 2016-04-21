<?php
/**
 * Mailing class to send emails via Sendgrid.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\Utils;


class Mailer {

  private $sendgrid;
  private $email;
  /** @var \RamSources\Utils\Logging $log */
  private $log;

  /**
   * Start a new email. 
   * Mailer constructor.
   * @param $c
   */
  function __construct($c) {
    $this->sendgrid = new \SendGrid($c['mailkey']);
    $this->email = new \SendGrid\Email();
    $this->log = $c['logs'];
  }

  /**
   * Add To field to email. Can take an array of email addresses to add to an email. 
   * @param $email array of to emails.
   */
  function addTo($email) {
    foreach($email as $e) {
      $this->email->addTo($e);
    }
  }

  /**
   * Add address to from addresss. This is configured for this use. 
   */
  function addFrom() {
    $this->email->setFrom('no-reply@hrpotentialcenter.com');
    $this->email->setBcc('jtredlich@gmail.com');

  }

  /**
   * Add subject to email. 
   * @param $sub
   */
  function addSubject($sub) {
    $this->email->setSubject($sub);
  }

  /**
   * Add the HTML body and plain text to email body. 
   * @param $body
   */
  function addBody($body) {
    $this->email->setHtml($body);
    $this->email->setText(strip_tags($body));
  }

  /**
   * Try and send our email. If not log the error. 
   * @return bool Return true of false to see if email sent. 
   */
  function send() {
    try {
      $this->sendgrid->send($this->email);
      return true;
    } catch (\SendGrid\Exception $e) {
      $error = $e->getCode() . ": ";
      foreach($e->getErrors() as $er) {
        $error .= $er;
      }
      $this->log->logError($error);
      return false;
    }
  }
}
