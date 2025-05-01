<?php
// init_report.php
header('Content-Type: application/json');

// --- your IONOS DB credentials ---
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

// grab & validate POST
$etype = $_POST['etype']   ?? '';
$lat   = isset($_POST['lat'])  ? (float)$_POST['lat']   : null;
$long  = isset($_POST['long']) ? (float)$_POST['long']  : null;

if ($etype==='' || $lat===null || $long===null) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Missing parameters']);
  exit;
}

// generate a random token
$token = bin2hex(random_bytes(16));

// insert a new row; uid is auto‐increment
$sql = "INSERT INTO reports
          (authtok, etype, lat, `long`)
        VALUES
          (:tok, :etype, :lat, :long)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':tok'   => $token,
  ':etype' => $etype,
  ':lat'   => $lat,
  ':long'  => $long,
  ':state'=> $state, // <- 새로 추가된 부분
]);

// fetch the new uid
$uid = (int)$pdo->lastInsertId();

echo json_encode([
  'status'  => 'success',
  'uid'     => $uid,
  'authtok' => $token
]);
