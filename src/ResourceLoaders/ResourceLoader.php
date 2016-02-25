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

    switch($type) {
      case 'bathroom':
        $sql = "SELECT *
                from `Resource`
                Inner join `Building` on Building.building_id = Resource.building_id
                Inner join `Bathroom` on Resource.resource_id = Bathroom.resource_id
                Where Resource.resource_type = :type";
        break;
      case 'vending':
        $sql = "SELECT *
                from `Resource`
                Inner join `Building` on Building.building_id = Resource.building_id
                Inner join `Vending` on Resource.resource_id = Vending.resource_id
                Where Resource.resource_type = :type";
        break;
      case 'water':
        $sql = "SELECT *
                from `Resource`
                Inner join `Building` on Building.building_id = Resource.building_id
                Inner join `Water` on Resource.resource_id = Water.resource_id
                Where Resource.resource_type = :type";
        break;
      default:
        throw new \Exception('Type not configured.');
    }
    $this->db->query($sql);
    $this->db->bind(':type', $type);
    $this->db->execute();

    return $this->db->results();

  }
}