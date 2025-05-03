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

// POST parameters
$state = $_POST['state'] ?? ''; // Life-Threatening, Non Life-Threatening, Security
$etype = $_POST['etype'] ?? ''; // e.g. Bleeding, AED
$lat   = isset($_POST['lat']) ? (float)$_POST['lat'] : null;
$long  = isset($_POST['long']) ? (float)$_POST['long'] : null;
$rname = $_POST['rname'] ?? '';
$rphone = $_POST['rphone'] ?? '';

if ($etype==='' || $lat===null || $long===null) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Missing parameters']);
  exit;
}

// convert type and priority
$type = ($state === 'Security') ? 'Security' : (
  ($state === 'Life-Threatening') ? 'Medical-Urgent' : 'Medical-General'
);
$priority = ($state === 'Life-Threatening') ? 'High' : (
  ($state === 'Non Life-Threatening') ? 'Normal' : 'Normal'
);

// generate token
$token = bin2hex(random_bytes(16));

// insert into DB
$sql = "INSERT INTO reports (authtok, type, priority, etype, lat, lng, name, phone)
        VALUES (:tok, :type, :priority, :etype, :lat, :lng, :name, :phone)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':tok' => $token,
  ':type' => $type,
  ':priority' => $priority,
  ':etype' => $etype,
  ':lat' => $lat,
  ':lng' => $long,
  ':name' => $rname,
  ':phone' => $rphone,
]);

$uid = (int)$pdo->lastInsertId();

echo json_encode([
  'status' => 'success',
  'uid' => $uid,
  'authtok' => $token
]);
