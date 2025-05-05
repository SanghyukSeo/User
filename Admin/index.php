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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
  </style>
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
      <button onclick="revealContact()">Show Contact</button>
      <button onclick="window.location.href='export.php'">Download CSV</button>
    </div>
  </div>

  <script>
let map;
let activeMarker = null;
let activeCard = null;
let markers = [];
let reportsData = [];
let currentSort = 'time';
let selectedUid = null;

function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 17,
    center: { lat: 42.3601, lng: -71.0942 },
    mapTypeId: 'roadmap'
  });

  loadReports();
  setInterval(loadReports, 5000);
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
    sorted.sort((a, b) => {
      return (priorityOrder[getColorByEtype(a.etype)] || 99) - (priorityOrder[getColorByEtype(b.etype)] || 99);
    });
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

function renderReports(data) {
  markers.forEach(m => m.setMap(null));
  markers = [];

  const list = document.querySelector('.request-list');
  list.innerHTML = '';

  data.forEach(report => {
    const lat = parseFloat(report.lat);
    const lng = parseFloat(report.long);
    const isValidLocation = lat && lng;
    const color = getColorByEtype(report.etype);

    const card = document.createElement('div');
    card.className = `request-card ${color}`;
    card.innerHTML = `
      <div class="report-type"><strong>${report.etype?.toUpperCase() || 'UNKNOWN'}</strong></div>
      <div class="tags">
        ${report.info && report.info !== 'N/A' ? `<span>${report.info}</span>` : ''}
        ${report.details1 && report.details1 !== 'N/A' ? `<span>${report.details1}</span>` : ''}
        ${report.details2 && report.details2 !== 'N/A' ? `<span>${report.details2}</span>` : ''}
        ${!isValidLocation ? `<span style="color: red;">⚠ No GPS </span>` : ''}
      </div>
      <div class="report-meta">
        <div class="report-time">${formatTime(report.created_at)}</div>
        <select class="status-dropdown" data-id="${report.uid}">
          <option value="Pending" ${report.status === 'Pending' ? 'selected' : ''}>Pending</option>
          <option value="Completed" ${report.status === 'Completed' ? 'selected' : ''}>Completed</option>
          <option value="Dispatched" ${report.status === 'Dispatched' ? 'selected' : ''}>Dispatched</option>
        </select>
      </div>
      ${(report.rname || report.phone) ? `
      <div class="report-contact hidden">
        ${report.rname ? `👤 ${report.rname}<br>` : ''}
        ${report.phone ? `📞 ${report.phone}` : ''}
      </div>` : ''}
    `;

    card.querySelector('.status-dropdown').addEventListener('change', (e) => {
      const id = e.target.getAttribute('data-id');
      const newStatus = e.target.value;
      fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(newStatus)}`
      })
      .then(res => res.json())
      .then(response => {
        if (response.success) loadReports();
        else alert('Status update failed');
      });
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

      card.onclick = () => {
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

        map.panTo(position);
        setTimeout(() => {
          map.setZoom(19);
        }, 300);

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

      if (report.uid === selectedUid) {
        card.classList.add('active-card');
        card.querySelector('.report-contact')?.classList.remove('hidden');
        activeCard = card;
        marker.originalColor = color;
        marker.setIcon({
          url: '../Assets/pin.svg',
          scaledSize: new google.maps.Size(48, 48),
          anchor: new google.maps.Point(24, 24),
          zIndex: google.maps.Marker.MAX_ZINDEX + 1000
        });
        activeMarker = marker;
      }
    }

    list.appendChild(card);
  });
}
  </script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAlZPcXdktuxgglm7tC4IkiHK2LMxpPfv4&callback=initMap"></script>
</body>
</html>