<?php
namespace RamSources\Controllers;

class CommentController {

  private $conn;
  private $log;
  private $user;

  function __construct($container) {
    $this->conn = $container['database'];
    $this->log = $container['logs'];
    $this->user = $container['user'];
  }

  function getCommentsByResource($rid) {
    $sql = "SELECT * From `Comments` WHERE resource_id = :rid AND parent_comment = 0";

    try {
      $this->conn->query($sql);
      $this->conn->bind(':rid', $rid);
      $this->conn->execute();

      $output = $this->conn->results();

      //Loop through our comments and add real username and child comments(if any).
      for($i=0;$i < count($output);$i++) {
        $name = $this->user->getUserName($output[$i]['user_id']);
        $output[$i]['user_id'] = $name['name'];
        $output[$i]['child_comments'] = $this->getChildComments($output[$i]['comment_id']);
      }

      return $output ? $output : array(
        'result' => 'Failure',
        'message' => 'There are no comments for this resource'
      );
    } catch (\PDOException $e) {
      return array(
        'result' => 'Failure',
        'message' => $e->getMessage()
      );
    }
  }

  function getChildComments($pid) {
    $sql = "SELECT * FROM `Comments` WHERE parent_comment = :pid";

    try {
      $this->conn->query($sql);
      $this->conn->bind(':pid', $pid);
      $this->conn->execute();
      $results = $this->conn->results();
      for($i=0;$i<count($results);$i++) {
        $name = $this->user->getUserName($results[$i]['user_id']);
        $results[$i]['user_id'] = $name['name'];
      }
      return $results;
    } catch (\PDOException $e) {
      $this->log->logError($e->getMessage());
    }
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