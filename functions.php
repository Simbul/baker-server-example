<?php

  // **************************************************************************
  //
  // This file contains all the functions used by the server.
  //

  // Verify a base64-encoded receipt with the App Store.
  //
  // In case verification is successful, a nested hash representing the data
  // returned from the App Store will be returned.
  // In case of verification error, exceptions will be raised.
  function verifyReceipt($base64_receipt) {
    global $log, $file_db;

    $endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';
    $shared_secret = file_get_contents('shared_secret.txt');

    $postData = json_encode(array(
      'receipt-data' => $base64_receipt,
      'password' => $shared_secret
    ));

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = curl_exec($ch);
    $errno    = curl_errno($ch);
    $errmsg   = curl_error($ch);
    curl_close($ch);

    if ($errno != 0) {
      throw new Exception($errmsg, $errno);
    }

    $data = json_decode($response);

    $log->LogDebug("Store response: ". var_export($data, true));

    if (!is_object($data)) {
      throw new Exception('Invalid response data');
    }

    if (!isset($data->status) || ($data->status != 0 && $data->status != 21006)) {
      $product_id = $data->receipt->product_id;
      $log->LogError("Invalid receipt for $product_id : status " . $data->status);
      throw new Exception('Invalid receipt');
    }

    return $data;
  }

  // Mark issues as purchased, based on the app_store_data parameter.
  //
  // This function will examine a receipt verification response coming from the
  // App Store and mark as purchased all the issues it covers.
  // This function should be passed a verification response for an
  // auto-renewable subscription.
  function markIssuesAsPurchased($app_store_data, $app_id, $user_id) {
    global $log, $file_db;

    $receipt = $app_store_data->receipt;

    $start = intval($receipt->purchase_date_ms) / 1000;
    if ($data->status == 0) {
      $finish = intval($data->latest_receipt_info->expires_date) / 1000;
    } else if ($data->status == 21006) {
      $finish = intval($data->latest_expired_receipt_info->expires_date) / 1000;
    }

    $result = $file_db->query("SELECT product_id FROM issues WHERE app_id='$app_id' AND product_id NOT NULL AND `date` > datetime($start, 'unixepoch') AND `date` < datetime($finish, 'unixepoch')");
    $product_ids_to_mark = $result->fetchAll(PDO::FETCH_COLUMN);

    $insert = "INSERT OR IGNORE INTO purchased_issues (app_id, user_id, product_id)
      VALUES ('$app_id', '$user_id', :product_id)";
    $stmt = $file_db->prepare($insert);
    foreach ($product_ids_to_mark as $key => $product_id) {
      $stmt->bindParam(':product_id', $product_id);
      $stmt->execute();
    }
  }

  // Mark a single issue as purchased.
  //
  // This function will mark the issue with the given product_id as purchased.
  function markIssueAsPurchased($product_id, $app_id, $user_id) {
    global $log, $file_db;

    $insert = "INSERT OR IGNORE INTO purchased_issues (app_id, user_id, product_id)
      VALUES ('$app_id', '$user_id', '$product_id')";
    $stmt = $file_db->prepare($insert);
    $stmt->execute();
  }

?>
