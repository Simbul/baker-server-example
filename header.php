<?php

  // **************************************************************************
  //
  // This file will setup all objects required by the server (e.g. a database
  // connection).
  //

  date_default_timezone_set('UTC');

  require_once 'lib/KLogger.php';
  $log = new KLogger ( "log" , KLogger::DEBUG );

  $file_db = new PDO('sqlite:db/baker.sqlite3');
  $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  require_once 'tables.php';
  require_once 'functions.php';

  $log->LogInfo("");
  $log->LogInfo("===vvv=============== Received request ================vvv===");
  $log->LogInfo($_SERVER["REQUEST_URI"]);
  $log->LogInfo("GET " . var_export($_GET, true));
  $log->LogInfo("POST " . var_export($_POST, true));
  $log->LogInfo("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");


  $app_id = $_POST['app_id'];
  $user_id = $_POST['user_id'];

?>
