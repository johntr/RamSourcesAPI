<?php


namespace RamSources\ResourceLoaders;
use RamSources\Database\Database;

class ResourceLoader {

  private $db; //current db connection

  function __construct($dbconfig) {
    //set connections to db
    $this->db = new Database('RamSources', $dbconfig);
  }

  /**
   * Here we will get all of the resources we have in the db.
   * @param null $id
   * @return array all results in associate array
   */
  function getResources($id = null) {
    if ($id) {
      $sql = "SELECT T1.resource_id, T1.resource_name, T1.resource_type, T1.floor, T2.name, T2.location
              FROM `Resource` as T1
              INNER JOIN `Building` as T2
              ON T1.building_id = T2.building_id
              WHERE T1.resource_id = :id";
      $this->db->query($sql);
      $this->db->bind(':id', $id);
      $this->db->execute();
      return $this->db->single();
    }
    else {
      $sql = "SELECT T1.resource_id, T1.resource_name, T1.resource_type, T1.floor, T2.name, T2.location
              FROM `Resource` as T1
              INNER JOIN `Building` as T2
              ON T1.building_id = T2.building_id";
      $this->db->query($sql);
      $this->db->execute();
      return $this->db->results();
    }
  }

  /**
   * @param $bid = building id
   */
  function getResourceByBuilding($bid) {
    $sql = "SELECT T1.resource_id, T1.resource_name, T1.resource_type, T1.floor, T2.name, T2.location
              FROM `Resource` as T1
              INNER JOIN `Building` as T2
              ON T1.building_id = T2.building_id
              WHERE T1.building_id = :bid";
    $this->db->query($sql);
    $this->db->bind(':bid', $bid);
    $this->db->execute();
    return $this->db->results();
  }

  /**
   * @param $type = resource type
   */
  function getResourceByType($type) {

  }
}