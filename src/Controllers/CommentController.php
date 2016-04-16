<?php
/**
 * Controller for creating new and returning comments.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */

namespace RamSources\Controllers;

class CommentController {

  private $conn;
  private $log;

  private $user;

  /**
   * CommentController constructor. Setup db, logs and user classes.
   * @param $container
   */
  function __construct($container) {
    $this->conn = $container['database'];
    $this->log = $container['logs'];
    $this->user = $container['user'];
  }

  /**
   * Gets comments based on the id parameter.
   * @param $rid
   * @return array parent and child (if any) comment data.
   */
  function getCommentsByResource($rid) {
    $sql = "SELECT * From `Comments` WHERE resource_id = :rid AND parent_comment = 0";

    try {
      $this->conn->query($sql);
      $this->conn->bind(':rid', $rid);
      $this->conn->execute();

      $output = $this->conn->results();

      //Loop through our comments and add real username and child comments(if any).
      for($i=0;$i < count($output);$i++) {
        //Get the comment's real username.
        $name = $this->user->getUserName($output[$i]['user_id']);
        $output[$i]['user_id'] = $name['name'];
        //add child comment to return data.
        $output[$i]['child_comments'] = $this->getChildComments($output[$i]['comment_id']);
      }
      //return output if any.
      return $output ? $output : array(
        'result' => 'Failure',
        'message' => 'There are no comments for this resource'
      );
    } catch (\PDOException $e) {
      //return db crash info. @TODO log this.
      return array(
        'result' => 'Failure',
        'message' => $e->getMessage()
      );
    }
  }

  /**
   * Return child comments based parent id.
   * @param $pid
   * @return mixed
   */
  function getChildComments($pid) {

    $sql = "SELECT * FROM `Comments` WHERE parent_comment = :pid";

    try {
      $this->conn->query($sql);
      $this->conn->bind(':pid', $pid);
      $this->conn->execute();
      $results = $this->conn->results();
      //Add real username to child comment.
      for($i=0;$i<count($results);$i++) {
        $name = $this->user->getUserName($results[$i]['user_id']);
        $results[$i]['user_id'] = $name['name'];
      }
      return $results;
    } catch (\PDOException $e) {
      $this->log->logError($e->getMessage());
    }
  }

  /**
   * Create a new comment.
   * @param $data  //We will need parent_comment, comment, date_stamp , user_id, resource_id to create a new comment.
   * @return array  //message to user that comment was created.
   */
  function addComment($data) {
    //requires parent_comment, comment, date_stamp , user_id, resource_id to create a new one.
    $sql = "INSERT INTO `Comments` (parent_comment, comment, date_stamp , user_id, resource_id) VALUES (:parent_comment, :comment, :dates, :user_id, :resource_id)";
    $this->conn->query($sql);
    try {
      //bind parameters to query.
      foreach ($data as $k => $v) {
        $this->conn->bind(':' . $k, $v);
      }
      //get time stamp
      $date = date('Y-m-d H:i:s', strtotime('now'));
      //bind it.
      $this->conn->bind(':dates', $date);
      $this->conn->execute();
      //get comment id. 
      $id = $this->conn->lastInsertId();
      $message = array('result' => 'Success', 'message' => 'Comment id ' . $id . ' has been posted.');
      return $message;
    } catch (\PDOException $e) {
      $message = array('result' => 'Failure' , 'message' => $e->getMessage());
      $this->log->logError($message);
      return $message;
    }

  }

  /**
   * Delete a comment by id. 
   * @param $cid
   * @return array
   */
  function removeComment($cid) {
    $sql = "DELETE FROM `Comments` WHERE comment_id = :cid";

    $this->conn->query($sql);
    try {
      $this->conn->beginTransaction();
      //bind the comment id to the query. 
      $this->conn->bind(':cid', $cid);
      $this->conn->execute();
      $this->conn->endTransaction();
      //return results to user. 
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