<?php

namespace RamSources\Controllers;
use RamSources\Utils\Database;
use RamSources\Utils\Logging;

class CommentController {

  private $conn;
  private $log;

  function __construct($dbconfig) {

    $this->conn = new Database($dbconfig);
    $this->log = new Logging();
  }

  function getCommentsByResource($rid) {
    $sql = "SELECT * From `Comments` WHERE resource_id = :rid";

    $this->conn->query($sql);
    $this->conn->bind(':rid', $rid);
    $this->conn->execute();

    $output = $this->conn->results();

    return $output ? $output : array('result' => 'Failure' , 'message' => 'There are no comments for this resource');
  }

  function addComment($data) {

    $sql = "INSERT INTO `Comments` (parent_comment, comment, date_stamp , user_id, resource_id) VALUES (:parent_comment, :comment, :dates, :user_id, :resource_id)";
    $this->conn->query($sql);
    try {
      foreach ($data as $k => $v) {
        $this->conn->bind(':' . $k, $v);
      }
      $date = date('Y-m-d H:i:s', strtotime('now'));
      $this->conn->bind(':dates', $date);
      $this->conn->execute();
      $id = $this->conn->lastInsertId();
      $message = array('result' => 'Success', 'message' => 'Comment id ' . $id . ' has been posted.');
      return $message;
    } catch (\PDOException $e) {
      $message = array('result' => 'Failure' , 'message' => $e->getMessage());
      $this->log->logError($message);
      return $message;
    }

  }

  function removeComment($cid) {
    $sql = "DELETE FROM `Comments` WHERE comment_id = :cid";

    $this->conn->query($sql);
    try {
      $this->conn->beginTransaction();
      $this->conn->bind(':cid', $cid);
      $this->conn->execute();
      $this->conn->endTransaction();
      $message = array('result' => 'Success', 'message' => 'Removed comment ' . $cid);
      return $message;
    }
    catch(\PDOException $e) {
      $this->conn->cancelTransaction();
      $message = array('result' => 'Failure' , 'message' => $e->getMessage());
      $this->log->logError($message);
      return $message;
    }

  }
}