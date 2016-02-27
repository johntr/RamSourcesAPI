<?php

namespace RamSources\Database;

use \PDO;


class Database {

  private $conn;
  private $host;
  private $dbname;
  private $user;
  private $pass;

  private $stmt;
  protected $error;


  function __construct($dbconfig) {

    $this->dbname = $dbconfig['db'];
    $this->host = $dbconfig['host'];
    $this->user = $dbconfig['un'];
    $this->pass = $dbconfig['pw'];


    $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
    $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  }


  public function query($q) {
    $this->stmt = $this->conn->prepare($q);
  }

  public function bind($param, $value, $type = null) {
    if(is_null($type)) {
      switch(true) {
        case is_int($value):
          $type = PDO::PARAM_INT;
          break;
        case is_bool($value):
          $type = PDO::PARAM_BOOL;
          break;
        case is_null($value):
          $type = PDO::PARAM_NULL;
          break;
        default:
          $type = PDO::PARAM_STR;
      }
    }
    $this->stmt->bindValue($param, $value, $type);
  }

  public function execute() {
    return $this->stmt->execute();
  }

  public function results() {
    $this->execute();
    return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function single() {
    $this->execute();
    return $this->stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function rowCount() {
    return $this->stmt->rowCount();
  }

  public function lastInsertId() {
    return $this->conn->lastInsertId();
  }

  public function debugDumpParams() {
    return $this->stmt->debugDumpParams();
  }

}