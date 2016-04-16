<?php
/**
 * Now this is where the magic happens all Resouce functions are set here. All CRUD operations and returning of detailed data.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\Controllers;

class ResourceController {

  private $db; //current db connection
  private $c;

  function __construct($container) {
    //set connections to db
    $this->c = $container;
    $this->db = $container['database'];
  }

  /**
   * Here we will get all of the resources we have in the db this is filterable to a single resouce by passing its id.
   * @param null $id
   * @return array all results in associate array
   */
  function getResources($id = null) {
    //if we have an id just get that one back. return summary data of an resource.
    if ($id) {
      $sql = "SELECT T1.resource_id, T1.resource_name, T1.resource_type, T1.floor, T2.name, T2.location, T1.incident_status
              FROM `Resource` as T1
              INNER JOIN `Building` as T2
              ON T1.building_id = T2.building_id
              WHERE T1.resource_id = :id";
      $this->db->query($sql);
      //bind teh id.
      $this->db->bind(':id', $id);
      $this->db->execute();
      return $this->db->single();
    }
    else {
      //THis is summary resource data for all resource.
      $sql = "SELECT T1.resource_id, T1.resource_name, T1.resource_type, T1.floor, T2.name, T2.location, T1.incident_status
              FROM `Resource` as T1
              INNER JOIN `Building` as T2
              ON T1.building_id = T2.building_id";
      $this->db->query($sql);
      $this->db->execute();
      return $this->db->results();
    }
  }

  /**
   * Function to return all of the buildings in the db.
   * @return mixed
   */
  function getBuildings() {
    //get all of the buildings.
    $sql = "SELECT * FROM `Building`";
    $this->db->query($sql);
    $this->db->execute();
    $data = $this->db->results();
    //location stored comma delimited. Break that apart and return each in separate elements.
    $buildings = $this->_locationExplode($data);
    return $buildings;
  }

  /**
   * Return all resources for a building.
   * @param $bid = building id
   * @return array
   */
  function getResourceByBuilding($bid) {
    //stanard data for resources in a building.
    $sql = "SELECT T1.resource_id, T1.resource_name, T1.resource_type, T1.floor, T2.name, T2.location
              FROM `Resource` as T1
              INNER JOIN `Building` as T2
              ON T1.building_id = T2.building_id
              WHERE T1.building_id = :bid";
    $this->db->query($sql);

    try {
      //bind the id.
      $this->db->bind(':bid', $bid);
      $this->db->execute();
      $data = $this->db->results();
      //if we get data return to user else return a failure.
      return count($data)>0 ? $data : array('result' => 'Failure', 'message' => 'No Resource for that building');
    } catch (\PDOException $e) {
      //@TODO log message.
      return array('result' => 'Failure', 'message' => $e->getMessage());
    }
  }

  /**
   * Get all the resources for a given type. Returns detailed data about resource. 
   * @param $type = resource type
   * @return array
   * @throws \Exception
   */
  function getResourceByType($type) {
    //update query based on type. If we add more resources we'll need to update this switch. @TODO Can we improve this to be more dynamic?
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
        //we do not know the type.
        throw new \Exception('Type not configured.');
    }
    $this->db->query($sql);
    //bind the type to the query.
    $this->db->bind(':type', $type);
    $this->db->execute();
    //get the results.
    $data = $this->db->results();
    //break location out from comma delimited.
    $output = $this->_locationExplode($data);
    //It was suggested we return the inventory and ratings on this function.
    $v = $this->c['inventory'];
    $r = $this->c['ratings'];
    //so foreach resource we are going to get its inventory if it's type vending and get its rating.
    for ($i = 0; $i < count($output); $i++) {
      if($type == 'vending') {
        $inv = $v->getInventoryById($output[$i]['resource_id']);
        $output[$i]['inventory'] = $inv['message'];
      }
      $rat = $r->getRatingDetail($output[$i]['resource_id']);
      $output[$i]['rating'] = $rat;
    }
    return $output;
  }

  /**
   * This is going to return a detailed view of a resource by id. Most of the data collected about a resource is returned here.
   * @param $id
   * @return array
   */
  function getResourceDetail($id) {
    //get basic data about resource
    $resourceData = $this->getResources($id);
    //Since our resource type tables are proper case. Form the type to proper case to be used in queries later.
    $resourceType = ucwords($resourceData['resource_type']);
    //get the resource's comments.
    $c = $this->c['comments'];
    $commentData = $c->getCommentsByResource($id);
    //remove comment if we do not get any.
    if (isset($commentData['result'])) {
      unset($commentData);
    }
    //get the inventory if its a vending machine.
    if($resourceType == 'Vending') {
      $inv = $this->c['inventory'];
      $inventoryData = $inv->getInventoryById($id);
    }
    //start our resource query.
    $sql = "SELECT * FROM `$resourceType` WHERE resource_id = :id";
    try {
      $this->db->query($sql);
      //bind the id.
      $this->db->bind(':id', $id);
      $this->db->execute();
      $resourceTypeData = $this->db->single();
      //merge the type data and the basic resource data
      $returnDta['resource'] = array_merge($resourceData, $resourceTypeData);
      if(isset($inventoryData)) {
        $returnDta['inventory'] = $inventoryData['message'];
      }
      //and now add the comments.
      $returnDta['comments'] = $commentData;
      return $returnDta;    //return all of the data we have about a resource.
    }
    catch (\PDOException $e) {
      //shit's, fuck fam.
      $message = array('Result' => $e->getMessage());
      return $message;
    }

  }

  /**
   * Creates a new resource data. We will need its basic data and type data.
   * The query is being built based on passed data. We are relying on our frontend to determin what is passed.
   * @TODO Not sure if this is really going to be used.
   * @param $rawData //We are encoding the type in the key so resource_type is going to be resource_resource_type and soap_type will be bathroom_soap_type.
   * @return array
   */
  function addResource($rawData) {
    $data = array();
    //get the type data from keys. So we know where the data is going.
    foreach($rawData as $k => $v) {
      //get the first key from the _
      $type = substr($k,0,strpos($k,'_'));
      //then get the rest after the first _
      $sub = substr($k,strpos($k,'_')+1);
      //create a new array based on that.
      $data[$type][$sub] = $v;

    }

    $resourceType = $data['resource']['resource_type'];
    //now figure out what type we have.
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
        $message = array('result' => "Failure", 'message' => "Unable to determine resource type.");
        return $message;
    }

    //build generic resource query.
    $resourceSQL = "INSERT INTO `Resource` (building_id, resource_type, resource_name, floor) VALUES (:building_id, :resource_type, :resource_name, :floor)";

    //Now that we have our 2 queries lets run them.
    try {
      $this->db->query($resourceSQL);
      $this->db->beginTransaction();
      //resource info is pretty static so lets just say what it is and where it goes.
      $this->db->bind(':building_id', $data['resource']['building_id']);
      $this->db->bind(':resource_type', $data['resource']['resource_type']);
      $this->db->bind(':resource_name', $data['resource']['resource_name']);
      $this->db->bind(':floor', $data['resource']['floor']);
      $this->db->execute();
      //get the id of the resource we just added.
      $rid = $this->db->lastInsertId();

      //now we can add the type data. This is very dynamic based on type.
      $this->db->query($typeSQL);
      //bind the resource we created to the type query.
      $this->db->bind(':resource_id', $rid);
      //now for each data point bind it to its type query.
      foreach($data[$type] as $k => $v) {
        $this->db->bind(':'.$k, $v);
      }

      $this->db->execute();   //lets give it a try.
      $this->db->endTransaction();

      $message = array('result' => 'Success');
      return $message;
    } catch (\Exception $e) {
      //we have a problem
      $this->db->cancelTransaction();
      $message = array('result' => "Failure", 'message' => $e->getMessage());
      return $message;
    }
  }
  /**
   * Function to update resources. THis will take a 2d array with resource_data and type_data elements.
   * This will update all of the data for a resource so we do not need to know what changed we will just update the whole row. So pass all the resource data.
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
    $typeSQL .= " WHERE " . $resourceIdType . " = :id";      //finish off typeSQL statement.

    //build generic resource query.
    $resourceSQL = "UPDATE `Resource` SET  building_id = :bid, resource_type = :rtype , resource_name = :rname, floor = :floor WHERE resource_id = :rid";

    //Now that we have our 2 queries lets run them.
    try {
      $this->db->query($typeSQL);
      $this->db->beginTransaction();
      $i=0;
      //bind our data via key, value.
      foreach ($bind as $b) {
        $this->db->bind($b['key'], $b['value']);
        $i++;
      }
      $this->db->bind(':id', $data['type_data'][$resourceIdType]);
      $this->db->execute();
      //we know what this needs to lets update it.
      $this->db->query($resourceSQL);
      $this->db->bind(':rid', $data['resource_data']['resource_id']);
      $this->db->bind(':bid', $data['resource_data']['building_id']);
      $this->db->bind(':rtype', $data['resource_data']['resource_type']);
      $this->db->bind(':rname', $data['resource_data']['resource_name']);
      $this->db->bind(':floor', $data['resource_data']['floor']);
      $this->db->execute();
      $this->db->endTransaction();

      $message = array('result' => 'Success');
    } catch (\Exception $e) {
      $this->db->cancelTransaction();
      $message = array('result' => $e->getMessage());
    }
    return $message;
  }

  /**
   * Lets users notify us and other users if there is a problem.
   * @param $rid
   * @return array
   */
  public function reportResource($rid) {
    //get the resource data
    $resource = $this->getResources($rid);
    //We are going to flag the resource has having an incident. So we dont get multiple notifications.
    if ($resource['incident_status'] == 0) {
      $sql = "UPDATE `Resource` SET incident_status = 1 WHERE resource_id = :rid";

      try {
        $this->db->query($sql);
        //bind the resource to the query.
        $this->db->bind(':rid', $rid);
        $this->db->execute();
        //email the notification @TODO this should use a notification class.
        $this->_notifyReport($resource);
        return array("result" => "Success", "message" => "Message of outage sent.");
      } catch (\PDOException $e) {
        return array("result" => "Failure", "message" => $e->getMessage());
      } catch (\Exception $e) {
        return array("result" => "Failure", "message" => $e->getMessage());
      }
    }
    else {
      return array("result" => "Failure", "message" => "An incident has already been reported for this resource");
    }
  }

  /**
   * The mailing part of our notification method. This will email out the incident.
   * @param $r
   * @throws \Exception
   * @throws \phpmailerException
   */
  private function _notifyReport($r) {
    $mail = new \PHPMailer();
    $mail->Host = 'localhost';
    $mail->Port = 587;
    $mail->setFrom('no-reply@ramsources.com', 'Ramsources Email Validation');
    $mail->isHTML(true);

    $mail->addAddress('info@ramsources.com', "RamSources Info");
    //$mail->addBCC('jtredlich@gmail.com', 'John Redlich');
    $mail->Subject = 'Ramsources Email Verification';
    $HTMLbody = $this->_createHTMLBody($r);
    $TXTbody = strip_tags($HTMLbody);
    $mail->Body = $HTMLbody;
    $mail->AltBody = $TXTbody;

    if (!$mail->send()) {
      throw new \Exception($mail->ErrorInfo);
    }
    else {
      //$this->lo->logNotification("Sent email to {$this->userInfo['email']}.");
    }
  }

  /**
   * The body of our email.
   * @param $r
   * @return string
   */
  private function _createHTMLBody($r) {
    $body = "<p>Hello Sir,<br/>This is RamSources letting you know there is a problem with the {$r['resource_type']} on floor {$r['floor']} in {$r['name']}. </p>";
    return $body;
  }

  /**
   * This will take our long,lat and turn it into ['long'] ['lat'].
   * @param $data
   * @return array
   */
  private function _locationExplode($data) {
    $buildings = array();
    foreach ($data as $d) {
      //if we even have a location explode it by ,
      if(!empty($d['location'])) {
        //get our long and lat
        $coords = explode(',', $d['location']);
        $d['lat'] = $coords[0];
        //trim off some extra data.
        $d['long'] = ltrim($coords[1]);
      }
      else {
        //if not we want to null that out. 
        $d['lat'] = NULL;
        $d['long'] = NULL;
      }
      $buildings[] = $d;
    }
    unset($d);
    return $buildings;
  }
}