<?php
  require_once 'header.php';

  $file_db->exec("CREATE TABLE IF NOT EXISTS receipts (
    transaction_id VARCHAR(30) PRIMARY KEY,
    app_id VARCHAR(255),
    user_id VARCHAR(255),
    product_id VARCHAR(255),
    type VARCHAR(30),
    base64_receipt TEXT)");

  $file_db->exec("CREATE TABLE IF NOT EXISTS purchased_issues (
    app_id VARCHAR(255),
    user_id VARCHAR(255),
    product_id VARCHAR(255),
    PRIMARY KEY(app_id, user_id, product_id))");

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
  }


?>
