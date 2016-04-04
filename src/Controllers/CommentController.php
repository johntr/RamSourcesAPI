<?php

namespace RamSources\Controllers;
use RamSources\Utils\Database;

class CommentController {

  private $conn;

  function __construct($dbconfig) {

    $this->conn = new Database($dbconfig);
  }

  function getCommentsByResource($rid) {
    $sql = "SELECT * From `Comments` WHERE resource_id = :rid";

    $this->conn->query($sql);
    $this->conn->bind(':rid', $rid);
    $this->conn->execute();

    return $this->conn->results();
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

      $message = array('Result' => $this->conn->lastInsertId());
      return $message;
    } catch (\PDOException $e) {

      $message = array('Result' => $e->getMessage());
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
      $message = array('Result' => 'Success', 'Message' => 'Removed comment ' . $cid);
      return $message;
    }
    catch(\PDOException $e) {
      $this->conn->cancelTransaction();
      $message = array('Result' => $e->getMessage());
      return $message;
    }

  }
}