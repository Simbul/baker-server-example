<?php
  date_default_timezone_set('UTC');

  require_once 'lib/KLogger.php';
  $log = new KLogger ( "log" , KLogger::DEBUG );

  $log->LogInfo("");
  $log->LogInfo("===vvv=============== Received request ================vvv===");
  $log->LogInfo($_SERVER["REQUEST_URI"]);
  $log->LogInfo("GET " . var_export($_GET, true));
  $log->LogInfo("POST " . var_export($_POST, true));
  $log->LogInfo("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");

  $file_db = new PDO('sqlite:db/baker.sqlite3');
  $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $app_id = $_POST['app_id'];
  $user_id = $_POST['user_id'];

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

  function markIssueAsPurchased($product_id, $app_id, $user_id) {
    global $log, $file_db;

    $insert = "INSERT OR IGNORE INTO purchased_issues (app_id, user_id, product_id)
      VALUES ('$app_id', '$user_id', '$product_id')";
    $stmt = $file_db->prepare($insert);
    $stmt->execute();
  }

?>
