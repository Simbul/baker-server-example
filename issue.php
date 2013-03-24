<?php

  // **************************************************************************
  //
  // This file implements the endpoint to download an issue.
  //

  require_once 'header.php';

  $app_id = $_GET['app_id'];
  $user_id = $_GET['user_id'];
  $name = $_GET['name'];

  if (!$app_id || !$user_id || !$name) {
    header('HTTP/1.1 400 Bad Request');
    die();
  }

  // Retrieve latest receipts
  $result = $file_db->query(
    "SELECT base64_receipt FROM receipts
    WHERE app_id='$app_id' AND user_id='$user_id' AND type='auto-renewable-subscription'
    ORDER BY transaction_id DESC LIMIT 0, 1"
  );
  $base64_latest_receipt = $result->fetchColumn();

  if ($base64_latest_receipt) {
    $data = verifyReceipt($base64_latest_receipt);
    markIssuesAsPurchased($data, $app_id, $user_id);
  }

  // Retrieve issue
  $result = $file_db->query(
    "SELECT * FROM issues
    WHERE app_id='$app_id' AND name='$name'"
  );
  $issue = $result->fetch(PDO::FETCH_ASSOC);
  $product_id = $issue['product_id'];

  $allow_download = false;
  if ($product_id) {
    // Allow download if the issue is marked as purchased
    $result = $file_db->query(
      "SELECT COUNT(*) FROM purchased_issues
      WHERE app_id='$app_id' AND user_id='$user_id' AND product_id='$product_id'"
    );
    $allow_download = ($result->fetchColumn() > 0);
  } else if ($issue) {
    // No product ID -> the issue is free to download
    $allow_download = true;
  }

  if ($allow_download) {
    $attachment_location = $_SERVER["DOCUMENT_ROOT"] . "/issues/$name.hpub";
    if (file_exists($attachment_location)) {
      header('HTTP/1.1 200 OK');
      header("Cache-Control: public"); // needed for i.e.
      header("Content-Type: application/zip");
      header("Content-Transfer-Encoding: Binary");
      header("Content-Length:".filesize($attachment_location));
      header("Content-Disposition: attachment; filename=file.zip");
      readfile($attachment_location);
      $log->LogDebug("Downloading $attachment_location");
    } else {
      header('HTTP/1.1 404 Not Found');
      $log->LogInfo("Issue not found: $attachment_location");
    }
  } else {
    header('HTTP/1.1 403 Forbidden');
    $log->LogInfo("Download not allowed: $name");
  }

?>
