<?php

require 'vendor/autoload.php';
require 'config.php';

use RamSources\ResourceLoaders\ResourceLoader;

$r = new ResourceLoader($dbconfig);

$app = new \Slim\App();


$app->get('/', function ($request, $response, $args) use ($r) {
  echo json_encode($r->getResources());

});

$app->get('/resource/{id}', function ($request, $responce, $args) use ($r) {
  $id = $args['id'];
 // echo $id;
  try {
    echo json_encode($r->getResources($id));
  }
  catch(Exception $e) {
    echo $e->getMessage();
  }
});

$app->get('/building/{bid}', function($request, $responce, $args) use ($r) {
  $bid = $args['bid'];
  echo json_encode($r->getResourceByBuilding($bid));
});

$app->run();