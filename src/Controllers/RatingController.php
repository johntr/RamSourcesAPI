<?php
/**
 * Controller for managing ratings.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\Controllers;

class RatingController {

  private $db;
  private $rid;

  /**
   * RatingController constructor.
   * @param $container
   */
  function __construct($container) {
    $this->db = $container['database'];
  }

  /**
   * Set a rating based on the following db fields: rating, resource_id.
   * @param $data   //rating, resource_id fields.
   * @return array    //message to user.
   */
  function setRating($data) {
    $sql = "INSERT INTO `Rating` (rating, resource_id) VALUES (:rating, :resource_id)";

    try {
      $this->db->query($sql);
      //for each rating field bind it to the query.
      foreach ($data as $k => $v) {
        $this->db->bind(':' . $k, $v);
      }
      $this->db->execute();
      //if its a success let them know.
      $message = array('Result' => 'Success', 'Message' => 'Rating added.');
      return $message;
    } catch (\PDOException $e) {
      $message = array('Result' => 'Fail', 'Message' => $e->getMessage());
      return $message;
    }
  }

  /**
   * Get together all the rating info and return to user.
   * @param $rid
   * @return array
   */
  function getRatingDetail($rid) {
    $this->rid = $rid;
    try {
      //get the ratings.
      $ratings = $this->_getRatingByResource();
      //Get average rating.
      $avgRating = $this->_getAvgRating();
    } catch (\Exception $e) {
      $message = array('Result' => 'Fail', 'Message' => $e->getMessage());
      return $message;
    }
    //round our average out.
    $avg = round($avgRating['average'], 3);
    //get to the nearest .5th star rating.
    $numStars = $this->_roundNearestFifth($avg);
    //return all the rating info for the resource.
    $output = array('total' => count($ratings), 'average' => $avg, 'stars' => $numStars);
    return $output;

  }

  /**
   * return the average rating using the sql avg function.
   * @return array
   */
  private function _getAvgRating() {

    try {
      $sql = "SELECT AVG(rating) as average FROM `Rating` WHERE resource_id = :rid";
      $this->db->query($sql);
      //bind the resource id the query.
      $this->db->bind(':rid', $this->rid);
      $this->db->execute();
      return $this->db->single();
    } catch (\PDOException $e) {
      $message = array('Result' => 'Fail', 'Message' => $e->getMessage());
      return $message;
    }
  }

  /**
   * Get all of the ratings info for a resource.
   * @return mixed
   * @throws \Exception
   */
  private function _getRatingByResource() {

    $sql = "SELECT * FROM `Rating` WHERE resource_id = :rid";

    try {
      $this->db->query($sql);
      //resource id bind to query
      $this->db->bind(':rid', $this->rid);
      $this->db->execute();
      return $this->db->results();
    } catch (\Exception $e) {
      echo $e->getMessage();
      throw new \Exception("No Resources found.");
    }
  }

  /**
   * Round the average to the nearest fifth.
   * @param $avg
   * @return float
   */
  private function _roundNearestFifth($avg) {
    //Multiply the average by 2 and return a whole number.
    $tmpAvg = round(($avg*2),0);
    //Divide the number by 2 to get the .5. 
    return $tmpAvg/2;

  }
}