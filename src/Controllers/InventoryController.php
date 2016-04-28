<?php
/**
 * Controller for managing vending machine inventory. You could call this our Ramventory manager.
 *
 * Created by John Redlich for the RamSources project.
 * Spring 2016
 *
 */
namespace RamSources\Controllers;


class InventoryController {

  private $db;
  private $log;


  /**
   * InventoryController constructor.
   * @param $container //DI container.
   */
  function __construct($container) {
    $this->db = $container['database'];
    $this->log = $container['logs'];
  }

  /**
   * Return inventory for a resource by id.
   * @param $id     //Resource id.
   * @return array
   */
  function getInventoryById($id) {

    $sql = "SELECT DISTINCT inv_type, inv_name FROM `Ramventory` WHERE resource_id = :id ";

    try {
      $this->db->query($sql);
      //bind the id to the query.
      $this->db->bind(':id', $id);
      $this->db->execute();

      $results = $this->db->results();

      return array ('result' => 'Success', 'message' => $results);
    }
    catch(\PDOException $e) {
      $this->log->logError($e);
      return array('result' => 'Failure', 'message' => $e->getMessage());
    }
  }

  /**
   * Create new inventory for a vending resource.
   * @param $rid
   * @param $inv
   * @return array
   */
  function addInventory($rid, $inv) {
    
    $sql = "INSERT INTO (inv_type, inv_name, resource_id) VALUES (:type, :name, :rid)";
    
    try {
      //lets try and put this together, a query for each inventory.
      $this->db->beginTransaction();
      foreach($inv as $k =>$v) {
        $this->db->query($sql);
        //bind data needed to create inventory.
        $this->db->bind('rid', $rid);
        $this->db->bind(':type', $k);
        $this->db->bind(':name', $v);
        $this->db->execute();
      }
      //commit our data
      $this->db->endTransaction();
    }
    catch(\PDOException $e) {
      //if we had errors roll the data back. 
      $this->db->cancelTransaction();
      $this->log->logError($e);
      return array('result' => 'Failure', 'message' => $e->getMessage());
    }
  }
}