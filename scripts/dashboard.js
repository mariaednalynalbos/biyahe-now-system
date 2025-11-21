const OPENWEATHER_KEY = 'YOUR_OPENWEATHER_API_KEY'; // replace with your key
const HOME_PAGE_URL = '/index.php';

// ========== Initialize Lucide icons ==========
document.addEventListener('DOMContentLoaded', () => { lucide.replace(); });

// ========== Utility ==========
function showToast(msg, success = true) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.style.background = success ? 'green' : '#e53e3e';
  t.style.opacity = 1;
  setTimeout(() => t.style.opacity = 0, 3000);
}

// ========== Availability Toggle ==========
const availToggle = document.getElementById('availabilityToggle');
const availStatus = document.getElementById('availabilityStatus');
if (availToggle && availStatus) {
  const savedAvail = localStorage.getItem('driver_available');
  if (savedAvail === 'false') {
    availToggle.checked = false;
    availStatus.textContent = 'Not available';
    availStatus.classList.remove('text-green-400');
    availStatus.classList.add('text-red-400');
  }
  availToggle.addEventListener('change', () => {
    if (availToggle.checked) {
      availStatus.textContent = 'Available';
      availStatus.classList.remove('text-red-400');
      availStatus.classList.add('text-green-400');
      localStorage.setItem('driver_available', 'true');
    } else {
      availStatus.textContent = 'Not available';
      availStatus.classList.remove('text-green-400');
      availStatus.classList.add('text-red-400');
      localStorage.setItem('driver_available', 'false');
    }
  });
}

// ========== Search ==========
const searchInput = document.getElementById('search');
const suggestions = document.getElementById('search-suggestions');
if (searchInput && suggestions) {
  searchInput.addEventListener('input', () => {
    const q = searchInput.value.trim().toLowerCase();
    if (!q) { suggestions.classList.add('hidden'); return; }
    const drivers = JSON.parse(localStorage.getItem('drivers') || '[]');
    const passengers = JSON.parse(localStorage.getItem('passengers') || '[]');
    const results = [];

    drivers.forEach(d => {
      if ((d.firstName + ' ' + d.lastName).toLowerCase().includes(q) || d.plateNumber?.toLowerCase()?.includes(q)) {
        results.push({ type: 'Driver', text: d.lastName + ', ' + d.firstName });
      }
    });
    passengers.forEach(p => {
      if ((p.lastName + ' ' + p.firstName).toLowerCase().includes(q)) {
        results.push({ type: 'Passenger', text: p.lastName + ', ' + p.firstName });
      }
    });
    suggestions.innerHTML = results.length
      ? results.map(r => `<div class="p-2 hover:bg-[#0b1220] cursor-pointer">${r.type}: ${r.text}</div>`).join('')
      : '<div class="p-2 text-[var(--muted)]">No results</div>';
    suggestions.classList.remove('hidden');
  });

  document.addEventListener('click', e => {
    if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) suggestions.classList.add('hidden');
  });
}

// ========== Leaflet Map ==========
let map;
function initMap() {
  if (map) return;
  map = L.map('map').setView([11.2406, 125.0022], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
  L.marker([11.2406, 125.0022]).addTo(map).bindPopup("Current Location").openPopup();
}
document.addEventListener('DOMContentLoaded', initMap);

// ========== Navigation ==========
document.querySelectorAll('.nav-item').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    a.classList.add('active');
    document.querySelectorAll('.page-section').forEach(s => s.style.display = 'none');
    const section = a.dataset.section || 'dashboard';
    const secEl = document.getElementById(section + 'Section');
    if (secEl) secEl.style.display = 'block';
  });
});

// ========== Logout ==========
const logoutBtn = document.getElementById('nav-logout');
if (logoutBtn) logoutBtn.addEventListener('click', () => { window.location.href = HOME_PAGE_URL; });

// ========== Modals Overlay Close ==========
document.querySelectorAll('.custom-modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => { if (e.target === overlay) overlay.style.display = 'none'; });
});

// ========== Ensure Passenger Data ==========
(function ensurePassengers() {
  const p = JSON.parse(localStorage.getItem('passengers') || '[]');
  if (!p.length) {
    localStorage.setItem('passengers', JSON.stringify([
      { id: 'P1', lastName: 'Dela Cruz', firstName: 'Juan', destination: 'Tacloban', arrivalTime: '12:30 PM', seat: '1A', arrived: false },
      { id: 'P2', lastName: 'Santos', firstName: 'Mary', destination: 'Ormoc', arrivalTime: '1:10 PM', seat: '2B', arrived: false }
    ]));
  }
})();

// Profile page load
document.querySelector('[data-section="profile"]').addEventListener('click', ()=> {
  // hide other sections
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  document.querySelector('[data-section="profile"]').classList.add('active');
  document.querySelectorAll('.page-section').forEach(s=>s.style.display='none');
  document.getElementById('profileSection').style.display='block';

  // fetch current driver info (assuming only one driver stored in localStorage)
  const drivers = JSON.parse(localStorage.getItem('drivers')||'[]');
  let driver = drivers[0]; // default to first driver
  if (!driver) return;

  document.getElementById('profileName').textContent = driver.firstName + ' ' + driver.lastName;
  document.getElementById('profileEmail').textContent = driver.email || '--';
  document.getElementById('profileAddress').textContent = driver.address || '--';
  document.getElementById('profileContact').textContent = driver.contactNumber || '--';
  document.getElementById('profileVehicle').textContent = driver.vehicleType || '--';
  document.getElementById('profilePlate').textContent = driver.plateNumber || '--';
  document.getElementById('profileLicense').textContent = driver.licenseNumber || '--';
  document.getElementById('profileLicenseExpiry').textContent = driver.licenseExpiryDate || '--';
  document.getElementById('profileArea').textContent = driver.areaOfOperation || '--';
  document.getElementById('profileSchedule').textContent = driver.workingSchedule || '--';
  document.getElementById('profileExperience').textContent = driver.yearsExperience || '--';
  document.getElementById('profileDOB').textContent = driver.dateOfBirth || '--';
  document.getElementById('profileGender').textContent = driver.gender || '--';
  document.getElementById('profilePicDisplay').src = driver.profilePicData || 'https://via.placeholder.com/80';
});

// Open Profile Modal
document.getElementById('navProfile').addEventListener('click', ()=> {
  const profileModal = document.getElementById('profileModal');
  profileModal.style.display = 'flex';

  // Fetch driver info (from localStorage or server)
  const drivers = JSON.parse(localStorage.getItem('drivers')||'[]');
  let driver = drivers[0]; // default to first driver
  if (!driver) return;

  document.getElementById('profileName').textContent = driver.firstName + ' ' + driver.lastName;
  document.getElementById('profileEmail').textContent = driver.email || '--';
  document.getElementById('profileAddress').textContent = driver.address || '--';
  document.getElementById('profileContact').textContent = driver.contactNumber || '--';
  document.getElementById('profileVehicle').textContent = driver.vehicleType || '--';
  document.getElementById('profilePlate').textContent = driver.plateNumber || '--';
  document.getElementById('profileLicense').textContent = driver.licenseNumber || '--';
  document.getElementById('profileLicenseExpiry').textContent = driver.licenseExpiryDate || '--';
  document.getElementById('profileArea').textContent = driver.areaOfOperation || '--';
  document.getElementById('profileSchedule').textContent = driver.workingSchedule || '--';
  document.getElementById('profileExperience').textContent = driver.yearsExperience || '--';
  document.getElementById('profileDOB').textContent = driver.dateOfBirth || '--';
  document.getElementById('profileGender').textContent = driver.gender || '--';
  document.getElementById('profilePicDisplay').src = driver.profilePicData || 'https://via.placeholder.com/80';
});

// Close Profile Modal
function closeProfile() {
  document.getElementById('profileModal').style.display = 'none';
}
