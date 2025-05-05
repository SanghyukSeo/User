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
  echo "DB connection failed: " . $e->getMessage();
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .request-card.active-card {
      background-color: #212121FF;
    }
    .report-contact.hidden {
      display: none;
    }
    .report-contact {
      font-size: 0.75rem;
      color: #ddd;
      margin-top: 4px;
      line-height: 1.4;
    }
    .report-phone {
      display: inline-block;
      font-size: 14px;
      color: #fff;
      background-color: #444;
      padding: 4px 8px;
      border-radius: 6px;
      text-decoration: none;
    }
    .report-name {
      font-size: 14px;
      margin-top: 6px;
      display: inline-block;
    }
    #noteModal {
      display: none;
      position: fixed;
      top: 30%;
      left: 50%;
      transform: translateX(-50%);
      background: #222;
      padding: 20px;
      border-radius: 8px;
      z-index: 9999;
      color: white;
      width: 300px;
    }
    #noteModal textarea {
      width: 100%;
      margin-bottom: 10px;
    }
  </style>
</head>

<body>
  <div class="map-section">
    <div id="map"></div>
  </div>

  <div class="sidebar">
    <h2>Requests</h2>


    <div class="filter-bar" style="display: flex; align-items: center; justify-content: space-between;">
  <div style="display: flex; align-items: center; gap: 10px;">
    <label for="filterSelect">Sort by:</label>
    <select id="filterSelect" onchange="applyFilter()">
      <option value="time">Time</option>
      <option value="priority">Priority</option>
    </select>
  </div>
  <button class="button" onclick="toggleCompletedView()" id="toggleCompletedBtn">Completed Tasks</button>
</div>

<div class="request-list"></div>

<div class="sidebar-footer">
  <button class="button" onclick="window.location.href='export.php'">Export CSV</button>
</div>

<div id="noteModal">
  <p>Handling Summary</p>
  <textarea id="adminNoteInput" rows="4"></textarea>
  <button class="button" onclick="submitAdminNote()">Submit</button>
  <button class="button" onclick="closeNoteModal()">Cancel</button>
</div>

  <script>
let map;
let activeMarker = null;
let activeCard = null;
let markers = [];
let reportsData = [];
let currentSort = 'time';
let selectedUid = null;
let showCompleted = false;
let pendingNoteId = null;
let pendingNewStatus = null;


function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 18,
    center: { lat: 42.366198, lng: -71.062146 },
    mapTypeId: 'roadmap',
    tilt: 0
  });

  const imageUrl = '../Assets/map.png'; // 오버레이할 이미지

  // ✅ 직접 지정한 지도 위 이미지 영역 (비율 계산 없이 고정)
  const imageBounds = {
  north: 42.366915,
  south: 42.365485,
  east: -71.061355,
  west: -71.063045
};

  const overlay = new google.maps.GroundOverlay(imageUrl, imageBounds, {
    opacity: 1
  });
  overlay.setMap(map);

  loadReports();
  setInterval(loadReports, 5000);
}

function toggleCompletedView() {
  showCompleted = !showCompleted;
  document.getElementById('toggleCompletedBtn').textContent = showCompleted ? 'All Tasks' : 'Completed Tasks';
  applyFilter();
}

function formatTime(isoString) {
  const date = new Date(isoString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function getColorByEtype(etype) {
  const redList = ['Unconscious','Fainted', 'Seizure', 'Substance Abuse', 'Other'];
  const blueList = ['Missing Person', 'Harassment', 'Fighting'];
  if (!etype) return 'yellow';
  if (redList.includes(etype)) return 'red';
  if (blueList.includes(etype)) return 'blue';
  return 'yellow';
}

function applyFilter() {
  const filter = document.getElementById('filterSelect').value;
  currentSort = filter;
  let sorted = [...reportsData];
  if (filter === 'priority') {
    const priorityOrder = { red: 1, yellow: 2, blue: 3 };
    sorted.sort((a, b) => (priorityOrder[getColorByEtype(a.etype)] || 99) - (priorityOrder[getColorByEtype(b.etype)] || 99));
  } else {
    sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }
  renderReports(sorted);
}

function loadReports() {
  fetch('get_reports.php')
    .then(res => res.json())
    .then(data => {
      reportsData = data;
      applyFilter();
    });
}

function revealContact() {
  if (activeCard) {
    const contact = activeCard.querySelector('.report-contact');
    if (contact) contact.classList.remove('hidden');
  }
}

function formatPhoneNumber(phone) {
  const digits = phone.replace(/\D/g, '');
  if (digits.length === 10) {
    return `${digits.slice(0, 3)}-${digits.slice(3, 6)}-${digits.slice(6)}`;
  }
  return phone;
}

function submitAdminNote() {
  const note = document.getElementById('adminNoteInput').value.trim();
  if (pendingNoteId && pendingNewStatus) {
    updateStatus(pendingNoteId, pendingNewStatus, note);
  }
  closeNoteModal();
}

function closeNoteModal() {
  document.getElementById('noteModal').style.display = 'none';
  document.getElementById('adminNoteInput').value = '';
  pendingNoteId = null;
  pendingNewStatus = null;
}

function updateStatus(id, status, note = '') {
  fetch('update_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}&note=${encodeURIComponent(note)}`
  })
  .then(res => res.json())
  .then(response => {
    if (response.success) loadReports();
    else alert('Status update failed');
  });
}
function renderReports(data) {
  markers.forEach(m => m.setMap(null));
  markers = [];
  const list = document.querySelector('.request-list');
  list.innerHTML = '';

  data.forEach(report => {
    if (!showCompleted && report.status === 'Completed') return;
    if (showCompleted && report.status !== 'Completed') return;

    const lat = parseFloat(report.lat);
    const lng = parseFloat(report.long);
    const isValidLocation = lat && lng;
    const color = getColorByEtype(report.etype);
    const formattedPhone = report.phone ? formatPhoneNumber(report.phone) : '';

    const card = document.createElement('div');
    card.className = `request-card ${color}`;
    card.innerHTML = `
      <div class="report-type"><strong>${report.etype?.toUpperCase() || 'UNKNOWN'}</strong></div>
      <div class="tags">
        ${report.info && report.info !== 'N/A' ? `<span>${report.info}</span>` : ''}
        ${report.details1 && report.details1 !== 'N/A' ? `<span>${report.details1}</span>` : ''}
        ${report.details2 && report.details2 !== 'N/A' ? `<span>${report.details2}</span>` : ''}
        ${!isValidLocation ? `<span style='color: red;'>⚠ No GPS </span>` : ''}
      </div>
      ${(report.rname || report.phone) ? `
      <div class="report-contact hidden">
        ${report.rname ? `<span class="report-name">👤 ${report.rname}</span><br>` : ''}
        ${report.phone ? `<a href="tel:${report.phone}" class="report-phone">📞 ${formattedPhone}</a>` : ''}
      </div>` : ''}
      <div class="report-meta">
        <div class="report-time">${formatTime(report.created_at)}</div>
        <select class="status-dropdown" data-id="${report.uid}">
          <option value="Pending" ${report.status === 'Pending' ? 'selected' : ''}>Pending</option>
          <option value="Completed" ${report.status === 'Completed' ? 'selected' : ''}>Completed</option>
          <option value="Dispatched" ${report.status === 'Dispatched' ? 'selected' : ''}>Dispatched</option>
        </select>
      </div>`;

    card.querySelector('.status-dropdown').addEventListener('change', (e) => {
      const id = e.target.getAttribute('data-id');
      const newStatus = e.target.value;

      if (newStatus === 'Completed') {
        pendingNoteId = id;
        pendingNewStatus = newStatus;
        document.getElementById('noteModal').style.display = 'block';
      } else {
        updateStatus(id, newStatus);
      }
    });

    if (isValidLocation) {
      const position = new google.maps.LatLng(lat, lng);
      const marker = new google.maps.Marker({
        position,
        map,
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          scale: 10,
          fillColor: color,
          fillOpacity: 0.8,
          strokeWeight: 0
        },
        zIndex: 1
      });
      markers.push(marker);

      marker.addListener('click', () => {
        card.click();
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
      card.onclick = (e, isAuto = false) => {
  if (activeCard === card) {
    card.classList.remove('active-card');
    if (activeMarker && activeMarker.originalColor) {
      activeMarker.setIcon({
        path: google.maps.SymbolPath.CIRCLE,
        scale: 10,
        fillColor: activeMarker.originalColor,
        fillOpacity: 0.8,
        strokeWeight: 0
      });
    }
    document.querySelectorAll('.report-contact').forEach(el => el.classList.add('hidden'));
    activeCard = null;
    activeMarker = null;
    selectedUid = null;
    return;
  }

  document.querySelectorAll('.request-card').forEach(c => c.classList.remove('active-card'));
  document.querySelectorAll('.report-contact').forEach(el => el.classList.add('hidden'));
  card.classList.add('active-card');
  const contact = card.querySelector('.report-contact');
  if (contact) contact.classList.remove('hidden');
  activeCard = card;
  selectedUid = report.uid;

  if (!isAuto) {
    map.panTo(position);
    setTimeout(() => {
      map.setZoom(19);
    }, 300);
  }

  if (activeMarker && activeMarker.originalColor) {
    activeMarker.setIcon({
      path: google.maps.SymbolPath.CIRCLE,
      scale: 10,
      fillColor: activeMarker.originalColor,
      fillOpacity: 0.8,
      strokeWeight: 0
    });
  }

  marker.originalColor = color;
  marker.setIcon({
    url: '../Assets/pin.svg',
    scaledSize: new google.maps.Size(48, 48),
    anchor: new google.maps.Point(24, 24),
    zIndex: google.maps.Marker.MAX_ZINDEX + 1000
  });
  activeMarker = marker;
};

if (selectedUid && String(selectedUid) === String(report.uid)) {
  setTimeout(() => {
    // trigger 클릭 대신 직접 함수 실행
    card.onclick(null, true); // true = isAuto
  }, 0);
}
    }

    list.appendChild(card);
  });
}
  </script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAlZPcXdktuxgglm7tC4IkiHK2LMxpPfv4&callback=initMap"></script>
</body>
</html>
