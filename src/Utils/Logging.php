<?php
/**
 * Loggind. Uses monolog to log errors and notifications to file log.
 * There are 4 levels to log. @TODO Maybe we need 5, have a nice middle ground. Idk.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\Utils;
//get a monolog object.
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Logging {

  private $log;
  private $stream;

  /**
   * Logging constructor.
   * Start a basic filestream to pass logs entries to.
   */
  function __construct() {

    $this->log = new Logger('RamSource');
    //Start the file stream. @TODO Need to get this in config so we can use on server.
    $this->stream = new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/logs/RamLogs.log', Logger::WARNING);
    $this->log->pushHandler($this->stream);
  }

  /**
   * Level 1 notification. Oh, good to know.
   * @param $message
   */
  function logNotification($message) {
    $this->log->addNotice($message);
  }

  /**
   * Level 2 notification. Meh, ok.
   * @param $message
   */
  function logWarning($message) {
    $this->log->addWarning($message);
  }

  /**
   * Level 3 notification. Shit. What is going wrong here.
   * @param $message
   */
  function logError($message) {
    $this->log->addError($message);
  }

  /**
   * Level 4 notification. Fuck, I never want to do development again.
   * @param $message
   */
  function logCritical($message) {
    $this->log->addCritical($message);
  }
}
