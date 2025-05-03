<?php
// DB 연결 설정
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
  echo "DB connection failed: " . $e->getMessage();
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="styles.css" />
  
</head>

<body>
  <div class="map-section">
    <div id="map"></div>
  </div>

  
  <div class="sidebar">
    <h2>Requests</h2>

<div class="filter-bar">
<label for="filterSelect">Sort by:</label>
<select id="filterSelect" onchange="applyFilter()">
  <option value="time">Time</option>
  <option value="priority">Priority</option>
</select>
</div>

    <div class="request-list"></div>

    <div class="sidebar-footer">
      <button onclick="alert('Completed list feature not implemented')">Completed</button>
      <button onclick="window.location.href='export.php'">Download CSV</button>
    </div>
  </div>



  <script>
let map;
let markers = [];
let reportsData = [];
let currentSort = 'time';

function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 17,
    center: { lat: 42.3601, lng: -71.0942 },
    mapTypeId: 'roadmap'
  });

  loadReports(); // 초기 호출
  setInterval(loadReports, 5000); // 5초마다 자동 갱신
}

// 시간 포맷 함수
function formatTime(isoString) {
  const date = new Date(isoString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// 색상 분류 함수
function getColorByEtype(etype) {
  if (!etype) return 'yellow';

  const redList = ['Unconscious', 'Seizure', 'Substance Abuse', 'Other'];
  const blueList = ['Missing Person', 'Harassment', 'Fighting'];

  if (redList.includes(etype)) return 'red';
  if (blueList.includes(etype)) return 'blue';
  return 'yellow';
}

// 필터 적용 함수
function applyFilter() {
  const filter = document.getElementById('filterSelect').value;
  currentSort = filter;

  let sorted = [...reportsData];

  if (filter === 'priority') {
    const priorityOrder = { red: 1, yellow: 2, blue: 3 };
    sorted.sort((a, b) => {
      const colorA = getColorByEtype(a.etype);
      const colorB = getColorByEtype(b.etype);
      return (priorityOrder[colorA] || 99) - (priorityOrder[colorB] || 99);
    });
  } else {
    sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }

  renderReports(sorted);
}

// 데이터 불러오기
function loadReports() {
  fetch('get_reports.php')
    .then(res => res.json())
    .then(data => {
      reportsData = data;
      applyFilter(); // 필터 기준에 따라 표시
    });
}

// 카드 렌더링 함수
function renderReports(data) {
  // 기존 마커 제거
  markers.forEach(m => m.setMap(null));
  markers = [];

  const list = document.querySelector('.request-list');
  list.innerHTML = '';

  data.forEach(report => {
    // 마커 추가
    if (report.lat && report.long) {
      const color = getColorByEtype(report.etype);

      const marker = new google.maps.Marker({
        position: { lat: parseFloat(report.lat), lng: parseFloat(report.long) },
        map: map,
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          scale: 10,
          fillColor: color,
          fillOpacity: 0.8,
          strokeWeight: 0
        }
      });

      markers.push(marker);
    }

    // 카드 추가
    const card = document.createElement('div');
    card.className = `request-card ${getColorByEtype(report.etype)}`;
    card.innerHTML = `
  <div class="report-type"><strong>${report.etype ? report.etype.toUpperCase() : 'UNKNOWN'}</strong></div>
 <div class="tags">
  ${report.info && report.info !== 'N/A' ? `<span>${report.info}</span>` : ''}
  ${report.details1 && report.details1 !== 'N/A' ? `<span>${report.details1}</span>` : ''}
  ${report.details2 && report.details2 !== 'N/A' ? `<span>${report.details2}</span>` : ''}
</div>
  <div class="report-meta">
    <div class="report-time">${formatTime(report.created_at)}</div>
    <div class="status">${report.status}</div>
  </div>
`;
    list.appendChild(card);
  });
}
</script>

</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAlZPcXdktuxgglm7tC4IkiHK2LMxpPfv4&callback=initMap"></script>



</body>
</html>