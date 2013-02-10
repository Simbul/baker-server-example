<?php
  require_once 'header.php';

  $json_issues = stripcslashes($_POST['issues']);
  $issues = json_decode($json_issues, true);

  // Retrieve latest receipts
  $result = $file_db->query("SELECT base64_receipt FROM receipts WHERE app_id='$app_id' AND user_id='$user_id' AND type='auto-renewable-subscription' ORDER BY transaction_id DESC LIMIT 0, 1");
  $base64_latest_receipt = $result->fetchColumn();

  $data = verifyReceipt($base64_latest_receipt);
  markIssuesAsPurchased($data, $app_id, $user_id);

  $subscribed = ($data->status == 0);

  $result = $file_db->query("SELECT product_id FROM purchased_issues WHERE app_id='$app_id' AND user_id='$user_id'");
  $purchased_product_ids = $result->fetchAll(PDO::FETCH_COLUMN);

  $purchased_issues = array();
  foreach ($issues as $key => $product_id) {
    if (in_array($product_id, $purchased_product_ids)) {
      $purchased_issues[$product_id] = true;
    } else {
      $purchased_issues[$product_id] = false;
    }
  }

  echo json_encode(array(
    'issues' => $purchased_issues,
    'subscribed' => $subscribed
  ));
?>