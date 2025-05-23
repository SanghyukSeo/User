<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

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

  $id = $_POST['id'] ?? null;
  $status = $_POST['status'] ?? null;
  $note = $_POST['note'] ?? null;

  if (!$id || !$status) {
    echo json_encode(['success' => false, 'error' => 'Missing id or status']);
    exit;
  }

  // ✅ 상태 및 관리자 메모 업데이트
  $stmt = $pdo->prepare("UPDATE reports SET status = ?, admin_note = ? WHERE uid = ?");
  $stmt->execute([$status, $note, $id]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}