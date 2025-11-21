const OPENWEATHER_KEY = window.OPENWEATHER_KEY || 'YOUR_OPENWEATHER_API_KEY';
const HOME_PAGE_URL = window.HOME_PAGE_URL || '/';

// Wrap everything in DOMContentLoaded so DOM + deferred libs are available
document.addEventListener('DOMContentLoaded', () => {

  // ---------- UTILS ----------
  function showToast(msg, success = true) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.style.background = success ? 'green' : '#e53e3e';
    t.style.opacity = '1';
    setTimeout(() => { t.style.opacity = '0'; }, 3000);
  }

  function safeEl(id) { return document.getElementById(id); }

  // ---------- WEATHER ----------
  const citySearch = safeEl('citySearch');
  const citySearchBtn = safeEl('citySearchBtn');

  async function fetchWeather(city) {
    if (!OPENWEATHER_KEY || OPENWEATHER_KEY === 'YOUR_OPENWEATHER_API_KEY') {
      const el = safeEl('currentLocation');
      if (el) el.textContent = 'Please set OPENWEATHER_KEY.';
      return;
    }
    try {
      const url = `https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)}&appid=${OPENWEATHER_KEY}&units=metric`;
      const response = await fetch(url);
      if (!response.ok) throw new Error('City not found or API error.');
      const data = await response.json();

      safeEl('currentTemp') && (safeEl('currentTemp').textContent = `${Math.round(data.main.temp)}°C`);
      safeEl('currentDesc') && (safeEl('currentDesc').textContent = data.weather[0].description.replace(/\b\w/g, l => l.toUpperCase()));
      safeEl('currentLocation') && (safeEl('currentLocation').textContent = data.name);
      safeEl('windVal') && (safeEl('windVal').textContent = `${data.wind.speed} m/s`);
      safeEl('humVal') && (safeEl('humVal').textContent = `${data.main.humidity}%`);
      safeEl('currentIcon') && (safeEl('currentIcon').innerHTML = getWeatherIcon(data.weather[0].icon));

      showToast(`Weather updated for ${data.name}!`, true);

      // fetch forecast
      await fetchForecast(data.coord.lat, data.coord.lon);
    } catch (err) {
      console.error('Weather fetch error:', err);
      showToast(err.message || 'Weather fetch failed', false);
      safeEl('currentTemp') && (safeEl('currentTemp').textContent = '--°C');
      safeEl('currentDesc') && (safeEl('currentDesc').textContent = '--');
      safeEl('currentLocation') && (safeEl('currentLocation').textContent = 'Error loading weather.');
    }
  }

  function getWeatherIcon(iconCode) {
    if (!iconCode) return '❓';
    if (iconCode.includes('01')) return '☀️';
    if (iconCode.includes('02') || iconCode.includes('03') || iconCode.includes('04')) return '☁️';
    if (iconCode.includes('09') || iconCode.includes('10')) return '🌧️';
    if (iconCode.includes('11')) return '⛈️';
    if (iconCode.includes('13')) return '❄️';
    if (iconCode.includes('50')) return '🌫️';
    return '❓';
  }

  async function fetchForecast(lat, lon) {
    if (!OPENWEATHER_KEY || !lat || !lon) return;
    try {
      const url = `https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&appid=${OPENWEATHER_KEY}&units=metric`;
      const resp = await fetch(url);
      if (!resp.ok) throw new Error('Forecast error');
      const data = await resp.json();
      const forecastList = safeEl('forecastList');
      if (!forecastList) return;
      forecastList.innerHTML = '';
      const daily = data.list.filter(i => i.dt_txt.includes("12:00:00")).slice(0, 4);
      daily.forEach(day => {
        const date = new Date(day.dt * 1000).toLocaleDateString('en-US', { weekday: 'short' });
        const temp = Math.round(day.main.temp);
        const icon = getWeatherIcon(day.weather[0].icon);
        const block = document.createElement('div');
        block.className = 'forecast-item';
        block.innerHTML = `<div class="text-sm font-semibold">${date}</div><div style="font-size:24px;">${icon}</div><div class="text-lg font-bold">${temp}°C</div>`;
        forecastList.appendChild(block);
      });
    } catch (err) {
      console.warn('Forecast error', err);
    }
  }

  if (citySearchBtn && citySearch) {
    citySearchBtn.addEventListener('click', () => {
      const city = citySearch.value.trim();
      if (city) fetchWeather(city);
    });
    // initial load
    fetchWeather('Tacloban, PH');
  }

  // ---------- ICONS (lucide) ----------
  if (window.lucide && typeof lucide.replace === 'function') {
    lucide.replace();
  }

  // ---------- AVAILABILITY TOGGLE ----------
  const availToggle = safeEl('availabilityToggle');
  const availStatus = safeEl('availabilityStatus');
  if (availToggle && availStatus) {
    const saved = localStorage.getItem('driver_available');
    if (saved === 'false') {
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

  // ---------- SEARCH SUGGESTIONS ----------
  const searchInput = safeEl('search');
  const suggestions = safeEl('search-suggestions');
  if (searchInput && suggestions) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim().toLowerCase();
      if (!q) { suggestions.classList.add('hidden'); return; }
      const drivers = JSON.parse(localStorage.getItem('drivers') || '[]');
      const passengers = JSON.parse(localStorage.getItem('passengers') || '[]');
      const results = [];
      drivers.forEach(d => {
        if ((d.firstName + ' ' + d.lastName).toLowerCase().includes(q) || (d.plateNumber || '').toLowerCase().includes(q)) {
          results.push({ type: 'Driver', text: `${d.lastName}, ${d.firstName}` });
        }
      });
      passengers.forEach(p => {
        if ((p.lastName + ' ' + p.firstName).toLowerCase().includes(q)) {
          results.push({ type: 'Passenger', text: `${p.lastName}, ${p.firstName}` });
        }
      });
      suggestions.innerHTML = results.length ? results.map(r => `<div class="p-2 hover:bg-[#0b1220] cursor-pointer">${r.type}: ${r.text}</div>`).join('') : '<div class="p-2 text-muted">No results</div>';
      suggestions.classList.remove('hidden');
    });

    document.addEventListener('click', e => {
      if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) suggestions.classList.add('hidden');
    });
  }

  // ---------- LIVE TRACKER MAP ----------
  let map = null;
  let routingControl = null;
  let driverMarker = null;
  let watchId = null;
  let currentPosition = null;
  let isTracking = false;

  function initMapOnce() {
    // Ensure container is present
    const mapEl = safeEl('map');
    if (!mapEl) return console.warn('Map element (#map) not found.');

    // Avoid double init
    if (map) {
      setTimeout(() => map.invalidateSize(), 150);
      return;
    }

    if (typeof L === 'undefined') {
      console.error('Leaflet not loaded yet.');
      return;
    }

    // Create map
    map = L.map('map').setView([11.2406, 125.0022], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    // Start live tracking
    startLiveTracking();

    // invalidate in case hidden on init
    setTimeout(() => { if (map) map.invalidateSize(); }, 200);
  }

  function startLiveTracking() {
    if (!navigator.geolocation) {
      showToast('Geolocation not supported', false);
      return;
    }

    // Get initial position
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        currentPosition = [lat, lng];

        // Create driver marker with custom icon
        if (driverMarker) {
          map.removeLayer(driverMarker);
        }

        const driverIcon = L.divIcon({
          className: 'driver-marker',
          html: `
            <div class="driver-icon">
              <div class="driver-pulse"></div>
              <div class="driver-dot">🚐</div>
            </div>
          `,
          iconSize: [30, 30],
          iconAnchor: [15, 15]
        });

        driverMarker = L.marker(currentPosition, { icon: driverIcon })
          .addTo(map)
          .bindPopup(`
            <div class="driver-popup">
              <h4><strong>Your Location</strong></h4>
              <p>Lat: ${lat.toFixed(6)}</p>
              <p>Lng: ${lng.toFixed(6)}</p>
              <p>Speed: ${(position.coords.speed || 0).toFixed(1)} m/s</p>
              <p>Updated: ${new Date().toLocaleTimeString()}</p>
            </div>
          `);

        // Center map on driver location
        map.setView(currentPosition, 16);

        // Setup route if available
        setupRoute();

        showToast('Live tracking started', true);
      },
      (error) => {
        console.error('Geolocation error:', error);
        showToast('Unable to get location', false);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 60000
      }
    );

    // Watch position for continuous tracking
    watchId = navigator.geolocation.watchPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const speed = position.coords.speed || 0;
        currentPosition = [lat, lng];

        // Update driver marker position
        if (driverMarker) {
          driverMarker.setLatLng(currentPosition);
          driverMarker.getPopup().setContent(`
            <div class="driver-popup">
              <h4><strong>Your Location</strong></h4>
              <p>Lat: ${lat.toFixed(6)}</p>
              <p>Lng: ${lng.toFixed(6)}</p>
              <p>Speed: ${speed.toFixed(1)} m/s</p>
              <p>Updated: ${new Date().toLocaleTimeString()}</p>
            </div>
          `);
        }

        // Update speed gauge
        updateSpeedGauge(speed * 3.6); // Convert m/s to km/h

        // Update route if needed
        if (routingControl && isTracking) {
          updateRoute();
        }
      },
      (error) => {
        console.error('Watch position error:', error);
      },
      {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 30000
      }
    );
  }

  function setupRoute() {
    if (!currentPosition || typeof L.Routing === 'undefined') return;

    const endPoint = L.latLng(11.2406, 125.0022); // Default destination

    try {
      routingControl = L.Routing.control({
        waypoints: [L.latLng(currentPosition[0], currentPosition[1]), endPoint],
        routeWhileDragging: false,
        showAlternatives: false,
        router: L.Routing.osrmv1 ? L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }) : undefined,
        lineOptions: { styles: [{ color: '#4299E1', weight: 6, opacity: 0.8 }] },
        createMarker: function() { return null; } // Don't create default markers
      }).addTo(map);

      routingControl.on('routesfound', function(e) {
        const route = e.routes[0];
        const distance = (route.summary.totalDistance / 1000).toFixed(1);
        const timeSec = route.summary.totalTime;
        const eta = new Date(Date.now() + timeSec * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        safeEl('dist-remaining') && (safeEl('dist-remaining').textContent = `${distance} KM`);
        safeEl('eta-value') && (safeEl('eta-value').textContent = eta);
      });
    } catch (err) {
      console.warn('Routing setup error', err);
    }
  }

  function updateRoute() {
    if (!routingControl || !currentPosition) return;

    try {
      const waypoints = routingControl.getWaypoints();
      if (waypoints.length >= 2) {
        waypoints[0].latLng = L.latLng(currentPosition[0], currentPosition[1]);
        routingControl.setWaypoints(waypoints);
      }
    } catch (err) {
      console.warn('Route update error', err);
    }
  }

  function updateSpeedGauge(speedKmh) {
    const speedValue = safeEl('speed-value');
    const speedRing = safeEl('speed-gauge-ring');
    
    if (speedValue) {
      speedValue.textContent = Math.round(speedKmh);
    }
    
    if (speedRing) {
      const maxSpeed = 100; // Max speed for gauge
      const percentage = Math.min(speedKmh / maxSpeed, 1);
      const circumference = 440;
      const offset = circumference - (percentage * circumference);
      speedRing.style.strokeDashoffset = offset;
    }
  }

  // Trip control functions
  function startTrip() {
    isTracking = true;
    showToast('Trip started - Live tracking active', true);
    if (currentPosition) {
      map.setView(currentPosition, 16);
    }
  }

  function stopTrip() {
    isTracking = false;
    if (watchId) {
      navigator.geolocation.clearWatch(watchId);
      watchId = null;
    }
    showToast('Trip stopped - Tracking paused', true);
  }

  // Try to initialize map now; if libs loaded deferred, try again shortly
  initMapOnce();
  // One retry if the library was not ready the first time
  if (!map) setTimeout(initMapOnce, 600);

  // Re-layout map on window resize
  window.addEventListener('resize', () => { if (map) setTimeout(() => map.invalidateSize(), 120); });

  // Trip control event listeners
  const startBtn = safeEl('startSim');
  const stopBtn = safeEl('stopSim');
  
  if (startBtn) {
    startBtn.addEventListener('click', startTrip);
  }
  
  if (stopBtn) {
    stopBtn.addEventListener('click', stopTrip);
  }

  // ---------- MODAL OVERLAY CLOSE ----------
  document.querySelectorAll('.custom-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.style.display = 'none'; });
  });

  // ---------- SAMPLE PASSENGER DATA ----------
  (function ensurePassengers() {
    const p = JSON.parse(localStorage.getItem('passengers') || '[]');
    if (!p.length) {
      localStorage.setItem('passengers', JSON.stringify([
        { id: 'P1', lastName: 'Dela Cruz', firstName: 'Juan', destination: 'Tacloban', arrivalTime: '12:30 PM', seat: '1A', arrived: false },
        { id: 'P2', lastName: 'Santos', firstName: 'Mary', destination: 'Ormoc', arrivalTime: '1:10 PM', seat: '2B', arrived: false }
      ]));
    }
  })();

  // ---------- PROFILE MODAL (guarded) ----------
  // Assume you have a showToast function for notifications (defined once)
function showToast(message, type) {
    const toast = document.getElementById('toast');
    // NOTE: Tiyakin na may element na may ID="toast" sa iyong HTML
    if (!toast) return;

    toast.textContent = message;
    toast.className = `fixed bottom-6 right-6 p-3 rounded-lg shadow-xl opacity-100 transition-opacity duration-300 ${type === 'error' ? 'bg-red-500' : 'bg-green-500'}`;
    setTimeout(() => {
        toast.className = 'fixed bottom-6 right-6 opacity-0 transition-opacity duration-300';
    }, 3000);
}

// NEW FUNCTION TO FETCH AND DISPLAY DATA
// Responsable ito sa pagkuha ng data mula sa DB at pag-fill sa form
function loadProfileData() {
    fetch('../Php/get_driver_profile.php')
        .then(response => {
            if (!response.ok) {
                 throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const profile = data.data;
                
                // 1. Populate the Display Fields (Name at Email sa taas ng modal)
                document.getElementById('profileNameDisplay').textContent = profile.firstname + ' ' + profile.lastname;
                // Gamitin ang email na galing sa DB (mula sa JOIN query)
                document.getElementById('profileEmailDisplay').textContent = profile.email || 'N/A';
                
                // 2. Populate the Input Fields (Para sa editing)
                // Gumamit ng || '' para maiwasan ang 'null' string kung NULL ang value sa DB
                document.getElementById('inputAddress').value = profile.address || '';
                document.getElementById('inputContact').value = profile.contact || '';
                document.getElementById('inputVehicle').value = profile.vehicle_type || '';
                document.getElementById('inputPlate').value = profile.plate_number || '';
                document.getElementById('inputLicense').value = profile.license_number || '';
                
                // NEW FIELDS
                document.getElementById('inputLicenseExpiry').value = profile.license_expiry || ''; 
                document.getElementById('inputArea').value = profile.area_of_operation || '';
                document.getElementById('inputSchedule').value = profile.working_schedule || '';
                document.getElementById('inputExperience').value = profile.experience_years || '';
                
                // Date and Gender Fields
                document.getElementById('inputDOB').value = profile.dob || '';
                document.getElementById('inputGender').value = profile.gender || 'Other'; 
                
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching profile:', error);
            showToast('Failed to load profile data.', 'error');
        });
}

// EVENT LISTENERS

// ---------- PROFILE MODAL INIT ----------
document.getElementById('navProfile').addEventListener('click', function(e) {
    e.preventDefault();
    loadProfileData(); // I-load ang data bago ipakita ang modal
    document.getElementById('profileModal').style.display = 'flex';
    
    // Tiyakin na naka-view mode ang inputs pagbukas
    const formInputs = document.querySelectorAll('#driverProfileForm input, #driverProfileForm select');
    formInputs.forEach(input => {
        input.disabled = true;
        input.classList.remove('bg-gray-700/80', 'border-highlight');
        input.classList.add('bg-gray-700'); // View mode style
    });
    document.getElementById('editProfileBtn').classList.remove('hidden');
    document.getElementById('saveProfileBtn').classList.add('hidden');
});


// TOGGLE EDIT MODE (FIXED: Enable inputs at i-toggle ang buttons)
document.getElementById('editProfileBtn').addEventListener('click', function() {
    const formInputs = document.querySelectorAll('#driverProfileForm input, #driverProfileForm select');
    formInputs.forEach(input => {
        input.disabled = false; // ENABLE inputs
        
        // Paglilinis ng view mode styles at pag-a-apply ng edit mode styles
        input.classList.remove('bg-gray-700'); 
        input.classList.add('bg-gray-700/80', 'border-highlight'); 
    });

    document.getElementById('editProfileBtn').classList.add('hidden');
    document.getElementById('saveProfileBtn').classList.remove('hidden');
});

// SAVE CHANGES (FIXED: Magse-save, magre-refresh ng form, at babalik sa view mode)
document.getElementById('saveProfileBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const form = document.getElementById('driverProfileForm');
    const formData = new FormData(form);
    
    // Ipadala ang data sa PHP update script
    fetch('../Php/update_driver_profile.php', {
        method: 'POST',
        body: formData 
    })
    .then(response => {
        if (!response.ok) {
            // Kung may HTTP error (404, 500, etc.)
            throw new Error('Server returned an error: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // 1. SUCCESS MESSAGE
            showToast(data.message || 'Matagumpay na na-update ang Profile!', 'success');
            
            // 2. Ibalik sa view mode (DISABLED)
            const formInputs = document.querySelectorAll('#driverProfileForm input, #driverProfileForm select');
            formInputs.forEach(input => {
                input.disabled = true; // DISABLED ulit
                
                // Ibalik ang view mode styles
                input.classList.remove('bg-gray-700/80', 'border-highlight');
                input.classList.add('bg-gray-700'); 
            });
            
            // I-toggle ang buttons
            document.getElementById('editProfileBtn').classList.remove('hidden');
            document.getElementById('saveProfileBtn').classList.add('hidden');
            
            // 3. I-re-load ang data para manatili ang laman ng form
            loadProfileData(); 
            
        } else {
            // PHP Logic Error
            showToast(data.message || 'May error habang nag-a-update.', 'error');
        }
    })
    .catch(error => {
        // Network or Parsing Error
        console.error('Error saving profile:', error);
        showToast('System Error: Failed to save changes.', 'error');
    });
});

  // ---------- SIDEBAR NAVIGATION ----------
  function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.page-section').forEach(section => {
      section.style.display = 'none';
      section.classList.remove('active');
    });
    
    // Show selected section
    const targetSection = document.getElementById(sectionId + 'Section');
    if (targetSection) {
      targetSection.style.display = 'block';
      targetSection.classList.add('active');
    }
    
    // Update nav items
    document.querySelectorAll('.nav-item').forEach(item => {
      item.classList.remove('active');
    });
    
    // Add active class to clicked nav item
    const activeNav = document.querySelector(`[data-section="${sectionId}"]`);
    if (activeNav) {
      activeNav.classList.add('active');
    }
    
    // Re-initialize map if dashboard is shown
    if (sectionId === 'dashboard' && map) {
      setTimeout(() => map.invalidateSize(), 150);
    }
  }
  
  // Add click listeners to nav items
  document.querySelectorAll('.nav-item[data-section]').forEach(navItem => {
    navItem.addEventListener('click', (e) => {
      e.preventDefault();
      const section = navItem.getAttribute('data-section');
      showSection(section);
    });
  });
  
  // Initialize with dashboard section
  showSection('dashboard');
  
  // Check passenger count periodically
  checkPassengerCount();
  setInterval(checkPassengerCount, 30000); // Check every 30 seconds
  
  // Mobile menu toggle
  const mobileMenuToggle = document.getElementById('mobileMenuToggle');
  const sidebar = document.getElementById('sidebar');
  const mobileOverlay = document.getElementById('mobileOverlay');
  
  if (mobileMenuToggle && sidebar && mobileOverlay) {
    mobileMenuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      mobileOverlay.classList.toggle('hidden');
    });
    
    mobileOverlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      mobileOverlay.classList.add('hidden');
    });
  }

  // ---------- LOGOUT CONFIRMATION ----------
  function showLogoutConfirm(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'custom-modal-overlay';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:2000;display:flex;align-items:center;justify-content:center;';
    modal.innerHTML = `
      <div class="custom-modal" style="background:#2D3748;border-radius:12px;padding:24px;max-width:400px;width:90%;">
        <h4 style="color:white;font-size:18px;margin-bottom:16px;">${message}</h4>
        <div style="display:flex;justify-content:flex-end;gap:12px;">
          <button id="modal-cancel" style="background:#6B7280;color:white;padding:8px 16px;border:none;border-radius:6px;cursor:pointer;">Cancel</button>
          <button id="modal-confirm" style="background:#DC2626;color:white;padding:8px 16px;border:none;border-radius:6px;cursor:pointer;">Yes</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    modal.querySelector('#modal-confirm').onclick = () => { modal.remove(); callback(true); };
    modal.querySelector('#modal-cancel').onclick = () => { modal.remove(); callback(false); };
  }

  // ---------- PASSENGER COUNT CHECK ----------
  async function checkPassengerCount() {
    try {
      const response = await fetch('../Php/get_driver_passenger_count.php');
      const data = await response.json();
      
      console.log('Passenger count response:', JSON.stringify(data, null, 2)); // Debug info
      
      if (data.success && data.count > 0) {
        updatePassengerCheckButton(data.count);
      } else {
        resetPassengerCheckButton();
        if (!data.success) {
          console.warn('Passenger count check failed:', data.message);
        }
      }
    } catch (error) {
      console.error('Error checking passenger count:', error);
    }
  }
  
  function updatePassengerCheckButton(count) {
    const btn = safeEl('passengerCheckBtn');
    if (btn) {
      btn.textContent = `Passenger Check-in (${count})`;
      btn.classList.remove('bg-yellow-600');
      btn.classList.add('bg-red-600', 'animate-pulse');
      btn.style.position = 'relative';
      
      // Add notification badge
      let badge = btn.querySelector('.notification-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'notification-badge';
        badge.style.cssText = `
          position: absolute;
          top: -8px;
          right: -8px;
          background: #ef4444;
          color: white;
          border-radius: 50%;
          width: 20px;
          height: 20px;
          font-size: 12px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: bold;
        `;
        btn.appendChild(badge);
      }
      badge.textContent = count;
    }
  }
  
  function resetPassengerCheckButton() {
    const btn = safeEl('passengerCheckBtn');
    if (btn) {
      btn.textContent = 'Passenger Check-in';
      btn.classList.remove('bg-red-600', 'animate-pulse');
      btn.classList.add('bg-yellow-600');
      
      const badge = btn.querySelector('.notification-badge');
      if (badge) {
        badge.remove();
      }
    }
  }
  
  // ---------- PASSENGER MODAL ----------
  async function openPassengerModal() {
    try {
      const response = await fetch('../Php/get_driver_passengers.php');
      const data = await response.json();
      
      if (data.success) {
        showPassengerModal(data.passengers);
      } else {
        showToast('No passengers found', false);
      }
    } catch (error) {
      console.error('Error loading passengers:', error);
      showToast('Error loading passenger data', false);
    }
  }
  
  function showPassengerModal(passengers) {
    const modal = safeEl('passengerModal');
    const passengerList = safeEl('passengerList');
    
    if (!modal || !passengerList) return;
    
    if (passengers.length === 0) {
      passengerList.innerHTML = '<p class="text-center text-muted">No passengers found for your assigned route.</p>';
    } else {
      passengerList.innerHTML = `
        <div class="space-y-3">
          ${passengers.map(passenger => `
            <div class="bg-secondary p-4 rounded-lg border border-gray-600">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <h4 class="font-bold text-lg text-white">${passenger.passenger_name}</h4>
                  <p class="text-sm text-muted">Seat ${passenger.seat_number}</p>
                </div>
                <div class="text-right">
                  <span class="px-2 py-1 ${passenger.status === 'Pending' ? 'bg-yellow-600' : 'bg-green-600'} text-white text-xs rounded">${passenger.status}</span>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div><span class="text-muted">Contact:</span> ${passenger.contact_number}</div>
                <div><span class="text-muted">Time:</span> ${passenger.departure_time}</div>
                <div><span class="text-muted">Date:</span> ${passenger.booking_date}</div>
                <div><span class="text-muted">Booking ID:</span> ${passenger.booking_id}</div>
              </div>
              <div class="mt-3 flex gap-2">
                <button class="check-in-btn px-3 py-1 ${passenger.status === 'Pending' ? 'bg-green-600' : 'bg-gray-600'} text-white text-sm rounded" data-booking-id="${passenger.booking_id}" ${passenger.status === 'Confirmed' ? 'disabled' : ''}>
                  ${passenger.status === 'Pending' ? 'Confirm' : 'Confirmed'}
                </button>
                <button class="contact-btn px-3 py-1 bg-blue-600 text-white text-sm rounded" data-contact="${passenger.contact_number}">
                  Call
                </button>
              </div>
            </div>
          `).join('')}
        </div>
      `;
      
      // Add event listeners for check-in buttons
      passengerList.querySelectorAll('.check-in-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const bookingId = this.getAttribute('data-booking-id');
          checkInPassenger(bookingId, this);
        });
      });
      
      // Add event listeners for call buttons
      passengerList.querySelectorAll('.contact-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const contact = this.getAttribute('data-contact');
          window.open(`tel:${contact}`);
        });
      });
    }
    
    modal.style.display = 'flex';
  }
  
  async function checkInPassenger(bookingId, button) {
    try {
      button.textContent = 'Processing...';
      button.disabled = true;
      
      const formData = new FormData();
      formData.append('booking_id', bookingId);
      
      const response = await fetch('../Php/confirm_passenger_checkin.php', {
        method: 'POST',
        body: formData
      });
      
      const data = await response.json();
      
      if (data.success) {
        button.textContent = 'Confirmed';
        button.classList.remove('bg-green-600');
        button.classList.add('bg-gray-600');
        showToast('Booking confirmed and passenger notified', true);
      } else {
        button.textContent = 'Confirm';
        button.disabled = false;
        showToast('Error: ' + data.message, false);
      }
    } catch (error) {
      console.error('Confirmation error:', error);
      button.textContent = 'Confirm';
      button.disabled = false;
      showToast('Error confirming booking', false);
    }
  }
  
  // Add click listener to passenger check button
  const passengerCheckBtn = safeEl('passengerCheckBtn');
  if (passengerCheckBtn) {
    passengerCheckBtn.addEventListener('click', openPassengerModal);
  }

  // ---------- MODAL CLOSE FUNCTIONS ----------
  window.closePassengers = function() {
    const modal = safeEl('passengerModal');
    if (modal) modal.style.display = 'none';
  };
  
  window.closeEmergency = function() {
    const modal = safeEl('emergencyModal');
    if (modal) modal.style.display = 'none';
  };
  
  window.closeSupport = function() {
    const modal = safeEl('supportModal');
    if (modal) modal.style.display = 'none';
  };

  // ---------- SAFE LOGOUT ----------
  const logoutBtn = safeEl('nav-logout');
  if (logoutBtn) logoutBtn.addEventListener('click', () => {
    showLogoutConfirm('Are you sure you want to logout?', (confirmed) => {
      if (confirmed) {
        window.location.href = '../php/logout.php';
      }
    });
  });

}); // end DOMContentLoaded
