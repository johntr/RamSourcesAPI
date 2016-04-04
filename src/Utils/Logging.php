<?php

namespace RamSources\Utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Logging {

  private $log;
  private $stream;

  function __construct() {

    $this->log = new Logger('RamSource');
    $this->stream = new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/logs/RamLogs.log', Logger::WARNING);
    $this->log->pushHandler($this->stream);
  }

  function logWarning($message) {
    
    $this->log->addWarning($message);
  }

  function logError($message) {
    $this->log->addError($message);
  }

  function logCritical($message) {
    $this->log->addCritical($message);
  }
}
