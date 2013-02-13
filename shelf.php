<?php

  // **************************************************************************
  //
  // This file implements the endpoint for the "shelf" API call.
  //

  require_once 'header.php';

  $app_id = $_GET['app_id'];
  $user_id = $_GET['user_id'];

  $result = $file_db->query("SELECT * FROM issues WHERE app_id='$app_id'");
  $issues = $result->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($issues);

?>
