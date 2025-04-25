<?php
// update_report.php
header('Content-Type: application/json');

// same DB bootstrap as init_report.php...
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

// required identifiers
$uid     = isset($_POST['uid'])     ? (int)$_POST['uid']     : 0;
$authtok = $_POST['authtok'] ?? '';

if ($uid<=0 || $authtok==='') {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Missing uid or token']);
  exit;
}

// verify token
$check = $pdo->prepare("SELECT 1 FROM reports WHERE uid=:u AND authtok=:t");
$check->execute([':u'=>$uid,':t'=>$authtok]);
if (!$check->fetch()) {
  http_response_code(403);
  echo json_encode(['status'=>'error','message'=>'Invalid uid or token']);
  exit;
}

// build dynamic UPDATE based on what was POSTed
$fields = [
  'awake','breathing','vomiting','bleeding','mood',
  'substances','danger','scene','meds','allergies',
  'conditions','oinfo','cname','rname','rphone'
];

$sets = [];
$params = [':uid'=>$uid];
foreach ($fields as $f) {
  if (isset($_POST[$f]) && $_POST[$f] !== '') {
    $sets[] = "`$f` = :$f";
    $params[":$f"] = $_POST[$f];
  }
}

if (empty($sets)) {
  // nothing to update
  echo json_encode(['status'=>'success','message'=>'Nothing changed']);
  exit;
}

$sql = "UPDATE reports SET " . implode(',', $sets) . " WHERE uid = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['status'=>'success']);
