<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB 연결 정보
$host = 'db5017669720.hosting-data.io';
$dbname = 'dbs14130702';
$user = 'dbu2702584';
$pass = 'PDDM!+2O25';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
  $stmt = $pdo->query("SELECT * FROM reports ORDER BY uid DESC"); // ✅ 수정된 부분
  $results = $stmt->fetchAll();

  echo json_encode($results);
} catch (Exception $e) {
  echo json_encode(["error" => $e->getMessage()]);
}
?>