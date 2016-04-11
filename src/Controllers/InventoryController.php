<?php
namespace RamSources\Controllers;


class InventoryController {

  private $db;
  private $log;


  function __construct($container) {
    $this->db = $container['database'];
    $this->log = $container['logs'];
  }

  function getInventoryById($id) {

    $sql = "SELECT inv_type, inv_name FROM `Ramventory` WHERE resource_id = :id ";

    try {
      $this->db->query($sql);
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

  function addInventory($rid,$inv) {
    
    $sql = "INSERT INTO (inv_type, inv_name, resource_id) VALUES (:type, :name, :rid)";
    
    try {
      $this->db->beginTransaction();
      foreach($inv as $k =>$v) {
        $this->db->query($sql);
        $this->db->bind('rid', $rid);
        $this->db->bind(':type', $k);
        $this->db->bind(':name', $v);
        $this->db->execute();
      }
      $this->db->endTransaction();
    }
    catch(\PDOException $e) {
      $this->db->cancelTransaction();
      $this->log->logError($e);
      return array('result' => 'Failure', 'message' => $e->getMessage());
    }
  }
}