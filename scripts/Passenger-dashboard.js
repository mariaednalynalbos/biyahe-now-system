document.addEventListener('DOMContentLoaded', () => {

    // ===============================
    // 0. GLOBAL VARIABLES
    // ===============================
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.page-section');
    const trackingMap = document.getElementById('trackingMap');
    let mapInstance = null;

    const upcomingTripsTableBody = document.getElementById('upcomingTripsTableBody');
    const fullHistoryTable = document.getElementById('fullHistoryTable');

    const globalModal = document.getElementById('globalModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const toast = document.getElementById('toast');

    // ===============================
    // MODAL & TOAST FUNCTIONS
    // ===============================
    function showModal(title, message, isError = false) {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modalTitle.style.color = isError ? 'red' : 'green';
        globalModal.style.display = 'flex';
    }

    function hideModal() {
        if (globalModal) globalModal.style.display = 'none';
    }

    document.getElementById('closeGlobalModal')?.addEventListener('click', hideModal);
    document.getElementById('modalCloseBtn')?.addEventListener('click', hideModal);
    window.addEventListener('click', (e) => {
        if (e.target.id === 'globalModal') hideModal();
    });

    function showToast(msg) {
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // ===============================
    // 1. POPULATE ROUTE DROPDOWN
    // ===============================
    function populateRouteDropdown() {
        const routeSelect = document.getElementById('route');
        if (!routeSelect) return;

        routeSelect.innerHTML = `
            <option value="">Select Route</option>
            <option value="1">Naval → Tacloban</option>
            <option value="2">Naval → Ormoc</option>
        `;
    }
    populateRouteDropdown();

    // ===============================
    // 2. WORKING MAP WITH LOCATION TRACKING
    // ===============================
    let userLocationMarker = null;
    let watchId = null;
    
    function initTrackingMap() {
        if (!trackingMap) return;
        if (mapInstance) {
            setTimeout(() => mapInstance.invalidateSize(), 200);
            return;
        }
        if (typeof L === 'undefined') return;

        // Initialize map
        mapInstance = L.map('trackingMap').setView([11.2406, 125.0022], 15);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapInstance);
        
        // Start location tracking
        startLocationTracking();
    }
    
    function startLocationTracking() {
        if (!navigator.geolocation) {
            document.getElementById('currentTripInfo').textContent = 'Geolocation not supported.';
            return;
        }
        
        document.getElementById('currentTripInfo').textContent = 'Getting your location...';
        
        // Get current position
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Remove existing marker
                if (userLocationMarker) {
                    mapInstance.removeLayer(userLocationMarker);
                }
                
                // Add new marker
                userLocationMarker = L.marker([lat, lng]).addTo(mapInstance)
                    .bindPopup(`Your Location<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
                
                // Center map on user location
                mapInstance.setView([lat, lng], 17);
                
                document.getElementById('currentTripInfo').textContent = `Location: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            },
            (error) => {
                console.error('Geolocation error:', error);
                let errorMsg = 'Unable to get location.';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Location access denied by user.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Location information unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Location request timed out.';
                        break;
                }
                document.getElementById('currentTripInfo').textContent = errorMsg;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
        
        // Watch position for continuous tracking
        if (watchId) {
            navigator.geolocation.clearWatch(watchId);
        }
        
        watchId = navigator.geolocation.watchPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Update marker position
                if (userLocationMarker) {
                    userLocationMarker.setLatLng([lat, lng]);
                    userLocationMarker.getPopup().setContent(`Your Location<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
                }
                
                document.getElementById('currentTripInfo').textContent = `Tracking: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
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
    
    // Center tracking button functionality
    document.getElementById('trackActiveTripBtn')?.addEventListener('click', () => {
        if (userLocationMarker && mapInstance) {
            mapInstance.setView(userLocationMarker.getLatLng(), 17);
        } else {
            startLocationTracking();
        }
    });
    
    // Show tracking button when initialized
    function showTrackingButton() {
        const trackBtn = document.getElementById('trackActiveTripBtn');
        if (trackBtn) {
            trackBtn.style.display = 'inline-block';
            trackBtn.textContent = 'Center on My Location';
        }
    }

    // ===============================
    // 3. SECTION SWITCHING
    // ===============================
    function switchSection(sectionId) {
        sections.forEach(sec => sec.style.display = 'none');
        navLinks.forEach(link => link.classList.remove('active'));

        const activeSection = document.getElementById(sectionId + 'Section');
        if (activeSection) activeSection.style.display = 'block';

        const activeLink = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
        if (activeLink) activeLink.classList.add('active');

        if (sectionId === 'dashboard') {
            fetchUpcomingTrips();
            initTrackingMap();
            showTrackingButton();
        }
        if (sectionId === 'history') fetchTripHistory();
    }

    navLinks.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            switchSection(link.dataset.section);
        });
    });

    switchSection('dashboard');

    // ===============================
    // 4. TIME DROPDOWN
    // ===============================
    const timeSelect = document.getElementById('tripTime');
    function populateTimeDropdown() {
        timeSelect.innerHTML = `
            <option value="">Select Time</option>
            <option value="06:00:00">6:00 AM</option>
            <option value="10:00:00">10:00 AM</option>
        `;
    }
    populateTimeDropdown();

    // ===============================
    // 5. SEAT SELECTION
    // ===============================
    const seatModal = document.getElementById('seatModal');
    const seatDisplayInput = document.getElementById('selectedSeatDisplay');
    const allSeats = document.querySelectorAll('.seat:not(.driver-seat)');
    let currentSelectedSeat = null;

    document.getElementById('openSeatModalBtn')?.addEventListener('click', async () => {
        const routeId = document.getElementById('route').value;
        const tripTime = document.getElementById('tripTime').value;
        
        if (!routeId || !tripTime) {
            showModal('Error', 'Please select route and time first.', true);
            return;
        }
        
        // Reset all seats to available
        allSeats.forEach(seat => {
            if (!seat.classList.contains('driver-seat')) {
                seat.classList.remove('unavailable', 'selected');
                seat.classList.add('available');
            }
        });
        
        // Fetch occupied seats
        try {
            const response = await fetch(`../Php/get_occupied_seats.php?route_id=${routeId}&departure_time=${tripTime}`);
            const data = await response.json();
            
            if (data.success && data.occupied_seats.length > 0) {
                data.occupied_seats.forEach(seatNum => {
                    const seatElement = document.querySelector(`[data-number="${seatNum}"]`);
                    if (seatElement) {
                        seatElement.classList.remove('available');
                        seatElement.classList.add('unavailable');
                    }
                });
            }
        } catch (error) {
            console.error('Error fetching occupied seats:', error);
        }
        
        seatModal.style.display = 'flex';
    });

    document.getElementById('closeSeatModal')?.addEventListener('click', () => {
        seatModal.style.display = 'none';
    });

    allSeats.forEach(seat => {
        seat.addEventListener('click', () => {
            if (seat.classList.contains('unavailable')) return;

            if (currentSelectedSeat) currentSelectedSeat.classList.remove('selected');

            seat.classList.add('selected');
            currentSelectedSeat = seat;
        });
    });

    document.getElementById('confirmSeatSelection')?.addEventListener('click', () => {
        if (!currentSelectedSeat) return showModal('Error', 'Please select a seat.', true);

        const seatNum = currentSelectedSeat.dataset.number;
        seatDisplayInput.value = `Seat ${seatNum}`;
        document.getElementById('seatNumber').value = seatNum;
        seatModal.style.display = 'none';
        showToast(`Seat ${seatNum} selected.`);
    });

    // ===============================
    // 6. BOOKING SUBMISSION
    // ===============================
    const bookingForm = document.getElementById('bookingForm');

    bookingForm?.addEventListener('submit', async e => {
        e.preventDefault();

        const formData = new FormData(bookingForm);

        const seatText = seatDisplayInput.value;
        const seatMatch = seatText.match(/(\d+)/);
        if (!seatMatch) return showModal("Error", "Please select a seat.", true);

        formData.set('seatNumber', seatMatch[1]);

        const submitBtn = bookingForm.querySelector('button[type="submit"]');
        const oldText = submitBtn.textContent;

        submitBtn.textContent = "Submitting...";
        submitBtn.disabled = true;

        try {
            const response = await fetch('../Php/process_passenger_booking.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showModal("Success", result.message);
                bookingForm.reset();
                seatDisplayInput.value = "";
                document.getElementById('seatNumber').value = "";
                currentSelectedSeat = null;
                if (currentSelectedSeat) currentSelectedSeat.classList.remove('selected');

                await fetchUpcomingTrips();
            } else {
                showModal("Booking Failed", result.message, true);
            }
        } catch (err) {
            showModal("System Error", "Network or server error.", true);
        }

        submitBtn.textContent = oldText;
        submitBtn.disabled = false;
    });

    // ===============================
    // 7. FETCH UPCOMING TRIPS
    // ===============================
    async function fetchUpcomingTrips() {
        if (!upcomingTripsTableBody) return;

        upcomingTripsTableBody.innerHTML = `<tr><td colspan="7" class="text-center">Loading upcoming trips...</td></tr>`;

        try {
            const response = await fetch('../Php/get_passenger_trips.php?type=upcoming');
            const data = await response.json();
            
            console.log('Fetch response:', JSON.stringify(data, null, 2));

            if (!data.success) {
                upcomingTripsTableBody.innerHTML = `<tr><td colspan="7" class="empty-state">Error: ${data.message}</td></tr>`;
                return;
            }
            
            if (data.trips.length === 0) {
                upcomingTripsTableBody.innerHTML = `<tr><td colspan="7" class="empty-state">No upcoming trips found. Book one now!</td></tr>`;
                return;
            }

            upcomingTripsTableBody.innerHTML = data.trips.map(trip => `
                <tr>
                    <td class="passenger-cell">${trip.passenger_name || 'N/A'}</td>
                    <td class="route-cell">${trip.route_name || 'N/A'}</td>
                    <td class="destination-cell">${trip.destination || 'N/A'}</td>
                    <td class="date-cell">${trip.booking_date}</td>
                    <td class="time-cell">${trip.departure_time}</td>
                    <td class="seat-cell">${trip.seat_number}</td>
                    <td class="status-cell"><span class="status-badge status-${trip.status.toLowerCase()}">${trip.status}</span></td>
                </tr>
            `).join('');

        } catch (err) {
            console.error('Fetch error:', err);
            upcomingTripsTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-red-500">Failed to load trips.</td></tr>`;
        }
    }

    // ===============================
    // 8. CANCEL TRIP (listener moved outside function)
    // ===============================
    upcomingTripsTableBody?.addEventListener('click', async (e) => {
        if (!e.target.classList.contains('cancelTripBtn')) return;

        if (!confirm("Cancel this trip?")) return;

        const bookingId = e.target.dataset.id;
        try {
            const res = await fetch('../Php/cancel_trip.php', {
                method: 'POST',
                body: new URLSearchParams({ booking_id: bookingId })
            });

            const result = await res.json();
            if (result.success) {
                showToast("Trip cancelled.");
                fetchUpcomingTrips();
            } else {
                showModal("Error", result.message, true);
            }
        } catch (err) {
            console.error(err);
            showModal("Error", "Network error.", true);
        }
    });

    // ===============================
    // 9. FETCH TRIP HISTORY
    // ===============================
    async function fetchTripHistory() {
        if (!fullHistoryTable) return;

        try {
            const response = await fetch('../Php/get_passenger_trips.php?type=history');
            const data = await response.json();

            fullHistoryTable.innerHTML = "";

            if (!data.success || data.trips.length === 0) {
                fullHistoryTable.innerHTML = `<tr><td colspan="6" class="text-center">No trip history.</td></tr>`;
                return;
            }

            fullHistoryTable.innerHTML = data.trips.map(trip => `
                <tr>
                    <td>${trip.route_name}</td>
                    <td>${trip.origin} → ${trip.destination}</td>
                    <td>${trip.booking_date}</td>
                    <td>${trip.departure_time}</td>
                    <td>${trip.seat_number}</td>
                    <td>${trip.status}</td>
                </tr>
            `).join('');

        } catch (err) {
            console.error(err);
            fullHistoryTable.innerHTML = `<tr><td colspan="6" class="text-center text-red-500">Failed to load history.</td></tr>`;
        }
    }

    // ===============================
    // 10. LOGOUT CONFIRMATION
    // ===============================
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

    const logoutBtn = document.getElementById('nav-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showLogoutConfirm('Are you sure you want to logout?', (confirmed) => {
                if (confirmed) {
                    window.location.href = '../php/logout.php';
                }
            });
        });
    }

});
