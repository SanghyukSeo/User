<?php
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
  $stmt = $pdo->query("SELECT * FROM reports ORDER BY created_at DESC");
  $reports = $stmt->fetchAll();
} catch (Exception $e) {
  die("DB connection failed: " . $e->getMessage());
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=reports_export.csv');

$output = fopen('php://output', 'w');

// CSV 헤더
fputcsv($output, ['ID', 'State', 'EType', 'Latitude', 'Longitude', 'Time', 'Name', 'Phone', 'Info', 'Details1', 'Details2', 'Status', 'Admin Note']);

// 데이터 출력
foreach ($reports as $r) {
  fputcsv($output, [
    $r['uid'] ?? '',
    $r['state'] ?? '',
    $r['etype'] ?? '',
    $r['lat'] ?? '',
    $r['long'] ?? '',
    $r['created_at'] ?? '',
    $r['rname'] ?? '',
    $r['phone'] ?? '',
    $r['info'] ?? '',
    $r['details1'] ?? '',
    $r['details2'] ?? '',
    $r['status'] ?? '',
    $r['admin_note'] ?? '',
  ]);
}

fclose($output);
exit;
?>