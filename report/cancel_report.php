<?php
// cancel_report.php
header('Content-Type: application/json');

// DB 연결 정보
$host   = 'db5017669720.hosting-data.io';
$port   = 3306;
$dbname = 'dbs14130702';
$user   = 'dbu2702584';
$pass   = 'PDDM!+2O25';
$charset= 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\Exception $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'DB connection failed']);
  exit;
}

$uid     = isset($_POST['uid'])     ? (int)$_POST['uid'] : 0;
$authtok = $_POST['authtok'] ?? '';

if ($uid <= 0 || $authtok === '') {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Missing uid or token']);
  exit;
}

// 토큰 검증
$check = $pdo->prepare("SELECT 1 FROM reports WHERE id = :u AND authtok = :t");
$check->execute([':u' => $uid, ':t' => $authtok]);

if (!$check->fetch()) {
  http_response_code(403);
  echo json_encode(['status'=>'error','message'=>'Invalid uid or token']);
  exit;
}

// 상태를 'Canceled'로 설정
$stmt = $pdo->prepare("UPDATE reports SET status = 'Canceled' WHERE id = :uid");
$stmt->execute([':uid' => $uid]);

echo json_encode(['status' => 'success']);
?>