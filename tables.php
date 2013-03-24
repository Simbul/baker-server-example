<?php

  // **************************************************************************
  //
  // This file will create the database tables required to run the server
  //

  if (!$file_db) {
    $file_db = new PDO('sqlite:db/baker.sqlite3');
    $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  // All the issues that will be displayed in the shelf for each application.
  $file_db->exec("CREATE TABLE IF NOT EXISTS issues (
    name VARCHAR(255),
    app_id VARCHAR(255),
    title VARCHAR(255),
    info TEXT,
    date TIMESTAMP,
    cover VARCHAR(255),
    product_id VARCHAR(255),
    PRIMARY KEY (name, app_id))");

  // All the receipts sent by Baker for each application and each user.
  $file_db->exec("CREATE TABLE IF NOT EXISTS receipts (
    transaction_id VARCHAR(30),
    app_id VARCHAR(255),
    user_id VARCHAR(255),
    product_id VARCHAR(255),
    type VARCHAR(30),
    base64_receipt TEXT,
    PRIMARY KEY(transaction_id, app_id, user_id))");

  // All the issues that should be considered as purcahsed for each
  // application and each user.
  // This is a cache table, meant to speed up access to the "purchases" API
  // endpoint. All the data in this table can be recreated from the content
  // of the "issues" and "receipts" tables.
  $file_db->exec("CREATE TABLE IF NOT EXISTS purchased_issues (
    app_id VARCHAR(255),
    user_id VARCHAR(255),
    product_id VARCHAR(255),
    PRIMARY KEY(app_id, user_id, product_id))");

  // All the APNS device tokens sent by Baker for each application and each user.
  $file_db->exec("CREATE TABLE IF NOT EXISTS apns_tokens (
    app_id VARCHAR(255),
    user_id VARCHAR(255),
    apns_token VARCHAR(64),
    PRIMARY KEY(app_id, user_id, apns_token))");

?>
