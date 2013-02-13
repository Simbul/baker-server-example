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

  $insert = "INSERT OR IGNORE INTO receipts (transaction_id, app_id, user_id, product_id, type, base64_receipt)
  VALUES (:transaction_id, :app_id, :user_id, :product_id, :type, :base64_receipt)";
  $stmt = $file_db->prepare($insert);

  $stmt->bindParam(':transaction_id', $transaction_id);
  $stmt->bindParam(':app_id', $app_id);
  $stmt->bindParam(':user_id', $user_id);
  $stmt->bindParam(':product_id', $product_id);
  $stmt->bindParam(':type', $purchase_type);
  $stmt->bindParam(':base64_receipt', $base64_receipt);

  $stmt->execute();

  if ($purchase_type == 'auto-renewable-subscription') {
    markIssuesAsPurchased($data, $app_id, $user_id);
  } else if ($purchase_type == 'issue') {
    markIssueAsPurchased($product_id, $app_id, $user_id);
  } else if ($purchase_type == 'free-subscription') {
    // Nothing to do, as the server assumes free subscriptions won't be enabled
  }

?>
