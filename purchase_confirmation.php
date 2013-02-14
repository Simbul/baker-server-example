<?php

  // **************************************************************************
  //
  // This file implements the endpoint for the "purchase confirmation" API call.
  //

  require_once 'header.php';

  $base64_receipt = stripcslashes($_POST['receipt_data']);
  $purchase_type = $_POST['type'];

  $data = verifyReceipt($base64_receipt);

  $receipt = $data->receipt;
  $product_id = $receipt->product_id;
  $transaction_id = $receipt->transaction_id;

  $log->LogDebug("Saving $purchase_type $product_id in the receipt database");

  $file_db->query(
    "INSERT OR IGNORE INTO receipts (transaction_id, app_id, user_id, product_id, type, base64_receipt)
    VALUES ('$transaction_id', '$app_id', '$user_id', '$product_id', '$purchase_type', '$base64_receipt')"
  );

  if ($purchase_type == 'auto-renewable-subscription') {
    markIssuesAsPurchased($data, $app_id, $user_id);
  } else if ($purchase_type == 'issue') {
    markIssueAsPurchased($product_id, $app_id, $user_id);
  } else if ($purchase_type == 'free-subscription') {
    // Nothing to do, as the server assumes free subscriptions won't be enabled
  }

?>
