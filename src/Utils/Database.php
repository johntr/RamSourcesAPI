<?php
/**
 * Database utility class. Basically a wrapper around PDO.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\Utils;

use \PDO;

class Database {

  private $conn;
  private $host;
  private $dbname;
  private $user;
  private $pass;

  private $stmt;


  /**
   * Database constructor. Sets up config for database connection. Bam DB Class baby.
   * @param $dbconfig
   */
  function __construct($dbconfig) {

    $this->dbname = $dbconfig['db'];
    $this->host = $dbconfig['host'];
    $this->user = $dbconfig['un'];
    $this->pass = $dbconfig['pw'];

    //db config based on config variables. @TODO user container config. maybe.
    $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
    //set pdo to throw exceptions.
    $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  }


  /**
   * Prepare sql query. Oh shit, here we go.
   * @param $q
   */
  public function query($q) {
    $this->stmt = $this->conn->prepare($q);
  }

  /**
   * Type sql variables and bind them to the query. SQL Injections are the bad.
   * @param $param
   * @param $value
   * @param null $type
   */
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
        //do string last since php thinks everything are strings.
        default:
          $type = PDO::PARAM_STR;
      }
    }
    $this->stmt->bindValue($param, $value, $type);
  }

  /**
   * Send query off to DB. Lets get this party started.
   * @return mixed
   */
  public function execute() {
    return $this->stmt->execute();
  }

  /**
   * Get results as Associated array. Because PHP arrays are the most awesome.
   * @return mixed
   */
  public function results() {
    $this->execute();
    return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get single record back from db. Good for verifying single result for get ids and such.
   * @return mixed
   */
  public function single() {
    $this->execute();
    return $this->stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get result row count.
   * @return mixed
   */
  public function rowCount() {
    return $this->stmt->rowCount();
  }

  /**
   * Get last inserted id.
   * @return string
   */
  public function lastInsertId() {
    return $this->conn->lastInsertId();
  }

  /**
   * Start db transactionf or multi queries. Fingers crossed.
   * @return bool
   */
  public function beginTransaction() {
    return $this->conn->beginTransaction();
  }

  /**
   * Commit the transaction. Hold onto your butts.
   * @return bool
   */
  public function endTransaction() {
    return $this->conn->commit();
  }

  /**
   * Rolls back bad commit. Not uh, uh you didn't say the magic word.
   * @return bool
   */
  public function cancelTransaction() {
    return $this->conn->rollBack();
  }

  /**
   * Dump the bind values. Lets see what this query is expecting.
   * @return mixed
   */
  public function debugDumpParams() {
    return $this->stmt->debugDumpParams();
  }

}