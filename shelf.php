<?php
  require_once 'header.php';

  $file_db->exec("CREATE TABLE IF NOT EXISTS issues (
    name VARCHAR(255),
    app_id VARCHAR(255),
    title VARCHAR(255),
    info TEXT,
    date TIMESTAMP,
    cover VARCHAR(255),
    url VARCHAR(255),
    product_id VARCHAR(255),
    PRIMARY KEY (name, app_id))");

  $app_id = $_GET['app_id'];
  $user_id = $_GET['user_id'];

  $result = $file_db->query("SELECT * FROM issues WHERE app_id='$app_id'");
  $issues = $result->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($issues);

?>
