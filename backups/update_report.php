<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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
  echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
  exit;
}

// 필수값 확인
$uid     = isset($_POST['uid'])     ? (int)$_POST['uid'] : 0;
$authtok = $_POST['authtok'] ?? '';

if ($uid <= 0 || $authtok === '') {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Missing uid or token']);
  exit;
}

// 토큰 유효성 확인
$check = $pdo->prepare("SELECT 1 FROM reports WHERE uid = :u AND authtok = :t");
$check->execute([':u' => $uid, ':t' => $authtok]);

if (!$check->fetch()) {
  http_response_code(403);
  echo json_encode(['status' => 'error', 'message' => 'Invalid uid or token']);
  exit;
}

// 업데이트할 필드 정의 (HTML 입력에 맞춤)
$fields = ['oinfo', 'meds', 'allergies', 'conditions', 'cname'];
$sets = [];
$params = [':uid' => $uid];

foreach ($fields as $f) {
  if (isset($_POST[$f]) && $_POST[$f] !== '') {
    $sets[] = "`$f` = :$f";
    $params[":$f"] = $_POST[$f];
  }
}

if (empty($sets)) {
  echo json_encode(['status' => 'success', 'message' => 'Nothing to update']);
  exit;
}

// 업데이트 실행
$sql = "UPDATE reports SET " . implode(', ', $sets) . " WHERE uid = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['status' => 'success']);