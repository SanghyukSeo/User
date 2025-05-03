<?php
// Database configuration
$db_host = 'db5017669720.hosting-data.io';
$db_user = 'dbu2702584';
$db_pass = 'PDDM!+2O25';
$db_name = 'dbs14130702';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['status'=>'error','message'=>'Connection failed: ' . $conn->connect_error]));
}
?>