<?php


namespace RamSources\Controllers;
use RamSources\Utils\Database;
use RamSources\Controllers\InventoryController;
use RamSources\Controllers\CommentController;

class ResourceController {

  private $db; //current db connection
  private $dbconfig;

  function __construct($dbconfig) {
    //set connections to db
    $this->dbconfig = $dbconfig;
    $this->db = new Database($dbconfig);
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
   * Function to return all of the builds in the db.
   * @return mixed
   */
  function getBuildings() {
    $sql = "SELECT *
            FROM `Building`";
    $this->db->query($sql);
    $this->db->execute();
    $data = $this->db->results();
    $buildings = $this->_locationExplode($data);
    return $buildings;
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

    try {
      $this->db->bind(':bid', $bid);
      $this->db->execute();
      $data = $this->db->results();
      return count($data)>0 ? $data : array('result' => 'Failure', 'message' => 'No Resource for that building');
    } catch (\PDOException $e) {
      //@TODO log message.
      return array('result' => 'Failure', 'message' => $e->getMessage());
    }
  }

  /**
   * @param $type = resource type
   * @return
   * @throws \Exception
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

    $data = $this->db->results();
    $output = $this->_locationExplode($data);
    if($type = 'vending') {
      $v = new InventoryController($this->dbconfig);
      for ($i = 0; $i < count($output); $i++) {
        $inv = $v->getInventoryById($output[$i]['resource_id']);
        $output[$i]['inventory'] = $inv['message'];
      }
    }
    return $output;
  }

  function getResourceDetail($id) {
    $resourceData = $this->getResources($id);
    $resourceType = ucwords($resourceData['resource_type']);

    $c = new CommentController($this->dbconfig);
    $commentData = $c->getCommentsByResource($id);

    if($resourceType == 'Vending') {
      $inv = new InventoryController($this->dbconfig);
      $inventoryData = $inv->getInventoryById($id);
    }

    $sql = "SELECT * FROM `$resourceType` WHERE resource_id = :id";
    try {
      $this->db->query($sql);
      $this->db->bind(':id', $id);
      $this->db->execute();
      $resourceTypeData = $this->db->single();
      $returnDta['resource'] = array_merge($resourceData, $resourceTypeData);
      if(isset($inventoryData)) {
        $returnDta['inventory'] = $inventoryData['message'];
      }
      $returnDta['comments'] = $commentData;
      return $returnDta;
    }
    catch (\PDOException $e) {
      $message = array('Result' => $e->getMessage());
      return $message;
    }

  }

  function addResource($rawData) {
    $data = array();
    foreach($rawData as $k => $v) {
      $type = substr($k,0,strpos($k,'_'));
      $sub = substr($k,strpos($k,'_')+1);
      $data[$type][$sub] = $v;

    }

    $resourceType = $data['resource']['resource_type'];
    switch($resourceType) {
      case 'bathroom':
        $typeSQL = "INSERT INTO `Bathroom` (soap_type, dryer_type, num_stalls, num_urinals, sex, resource_id) VALUES (:soap_type, :dryer_type, :num_stalls, :num_urinals, :sex, :resource_id)";
        break;
      case 'vending':
        $typeSQL = "INSERT INTO  `Vending` (pay_type, type, resource_id) VALUES (:pay_type, :type, :resource_id)";
        break;
      case 'water':
        $typeSQL = "INSERT INTO `Water` (type, height, resource_id) VALUES (:type, :height, :resource_id)";
        break;
      default:
        //if we don't get a good type return with a JSON error message.
        $message = array('Result' => "FAILURE: Unable to determine resource type.");
        return $message;
    }

    //build generic resource query.
    $resourceSQL = "INSERT INTO `Resource` (building_id, resource_type, resource_name, floor) VALUES (:building_id, :resource_type, :resource_name, :floor)";

    //Now that we have our 2 queries lets run them.
    try {
      $this->db->query($resourceSQL);
      $this->db->beginTransaction();
      $this->db->bind(':building_id', $data['resource']['building_id']);
      $this->db->bind(':resource_type', $data['resource']['resource_type']);
      $this->db->bind(':resource_name', $data['resource']['resource_name']);
      $this->db->bind(':floor', $data['resource']['floor']);
      $this->db->execute();

      $rid = $this->db->lastInsertId();
      $this->db->query($typeSQL);

      $this->db->bind(':resource_id', $rid);
      foreach($data[$type] as $k => $v) {
        $this->db->bind(':'.$k, $v);
      }

      $this->db->execute();
      $this->db->endTransaction();

      $message = array('Result' => 'Success');
      return $message;
    } catch (\Exception $e) {
      $this->db->cancelTransaction();
      $message = array('Result' => $e->getMessage());
      return $message;
    }
  }
  /**
   * Function to update resources. THis will take a 2d array with resource_data and type_data elements.
   * @param $data
   * @return array
   */

  function updateResource($data) {
    //resource specific sql query in typeSQL var.
    $resourceType = $data['resource_data']['resource_type'];
    switch($resourceType) {
      case 'bathroom':
        $typeSQL = "UPDATE `Bathroom` SET ";
        break;
      case 'vending':
        $typeSQL = "UPDATE `Vending` SET ";
        break;
      case 'water':
        $typeSQL = "UPDATE `Water` SET ";
        break;
      default:
        //if we don't get a good type return with a JSON error message.
        $message = array('Result' => "FAILURE: Unable to determine resource type.");
        return $message;
    }

    $bind = array();
    $i = 0;
    //Generate update keys and values and build array for value binds.
    foreach($data['type_data'] as $k => $v) {
      $typeSQL .= "$k = :$k, ";

      $tempBind = ':'. $k;
      $bind[$i]['key'] = $tempBind;
      $bind[$i]['value'] = $v;
      $i++;
    }
    //remove trailing ,
    $typeSQL = rtrim($typeSQL, ", ");
    $resourceIdType = $resourceType . "_id";
    $typeSQL .= " WHERE " . $resourceIdType . " = :id";      //finish off typeSQL statment.

    //build generic resource query.
    $resourceSQL = "UPDATE `Resource` SET  building_id = :bid, resource_type = :rtype , resource_name = :rname, floor = :floor WHERE resource_id = :rid";

    //Now that we have our 2 queries lets run them.
    try {
      $this->db->query($typeSQL);
      $this->db->beginTransaction();
      $i=0;
      foreach ($bind as $b) {
        $this->db->bind($b['key'], $b['value']);
        $i++;
      }
      $this->db->bind(':id', $data['type_data'][$resourceIdType]);
      $this->db->execute();

      $this->db->query($resourceSQL);
      $this->db->bind(':rid', $data['resource_data']['resource_id']);
      $this->db->bind(':bid', $data['resource_data']['building_id']);
      $this->db->bind(':rtype', $data['resource_data']['resource_type']);
      $this->db->bind(':rname', $data['resource_data']['resource_name']);
      $this->db->bind(':floor', $data['resource_data']['floor']);
      $this->db->execute();
      $this->db->endTransaction();

      $message = array('Result' => 'Success');
    } catch (\Exception $e) {
      $this->db->cancelTransaction();
      $message = array('Result' => $e->getMessage());
    }
    return $message;
  }

  private function _locationExplode($data) {
    $buildings = array();
    foreach ($data as $d) {
      if(!empty($d['location'])) {
        $coords = explode(',', $d['location']);
        $d['lat'] = $coords[0];
        $d['long'] = ltrim($coords[1]);
      }
      else {
        $d['lat'] = NULL;
        $d['long'] = NULL;
      }
      $buildings[] = $d;
    }
    unset($d);
    return $buildings;
  }
}