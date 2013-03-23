<?php

  // **************************************************************************
  //
  // This file implements the endpoint for the "post apns token" API call.
  //

  require_once 'header.php';

  $apns_token = $_POST['apns_token'];

  $log->LogDebug("Saving $apns_token in the APNS tokens database");

  $file_db->query(
    "INSERT OR IGNORE INTO apns_tokens (app_id, user_id, apns_token)
    VALUES ('$app_id', '$user_id', '$apns_token')"
  );

?>
