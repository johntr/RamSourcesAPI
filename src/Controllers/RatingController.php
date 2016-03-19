<?php

namespace RamSources\Controllers;
use RamSources\Database\Database;

class RatingController {

  private $db;
  private $rid;

  function __construct($dbconfig) {
    $this->db = new Database($dbconfig);
  }

  function setRating($data) {
    $sql = "INSERT INTO `Rating` (rating, resource_id) VALUES (:rating, :resource_id)";

    try {
      $this->db->query($sql);
      foreach ($data as $k => $v) {
        $this->db->bind(':' . $k, $v);
      }
      $this->db->execute();
      $message = array('Result' => 'Success', 'Message' => 'Rating added.');
      return $message;
    } catch (\PDOException $e) {
      $message = array('Result' => 'Fail', 'Message' => $e->getMessage());
      return $message;
    }
  }

  function getRatingDetail($rid) {
    $this->rid = $rid;

    try {
      $ratings = $this->_getRatingByResource();
      $avgRating = $this->_getAvgRating();
    } catch (\Exception $e) {
      $message = array('Result' => 'Fail', 'Message' => $e->getMessage());
      return $message;
    }
    $avg = round($avgRating['average'], 3);
    $numStars = $this->_roundNearestFifth($avg);
    $output = array('total' => count($ratings), 'average' => $avg, 'stars' => $numStars);
    return $output;

  }
  private function _getAvgRating() {

    try {
      $sql = "SELECT AVG(rating) as average FROM `Rating` WHERE resource_id = :rid";
      $this->db->query($sql);
      $this->db->bind(':rid', $this->rid);
      $this->db->execute();
      return $this->db->single();
    } catch (\PDOException $e) {
      $message = array('Result' => 'Fail', 'Message' => $e->getMessage());
      return $message;
    }
  }

  private function _getRatingByResource() {

    $sql = "SELECT * FROM `Rating` WHERE resource_id = :rid";

    try {
      $this->db->query($sql);
      $this->db->bind(':rid', $this->rid);
      $this->db->execute();
      return $this->db->results();
    } catch (\Exception $e) {
      echo $e->getMessage();
      throw new \Exception("No Resources found.");
    }
  }

  private function _roundNearestFifth($avg) {
    $tmpAvg = round(($avg*2),0);
    return $tmpAvg/2;

  }
}