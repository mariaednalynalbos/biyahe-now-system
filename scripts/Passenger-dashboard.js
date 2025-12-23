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
    // 2. LIVE TRACKING MAP WITH ROAD-TO-ROAD ROUTING
    // ===============================
    let userLocationMarker = null;
    let destinationMarker = null;
    let routingControl = null;
    let watchId = null;
    let currentPosition = null;
    let isTracking = false;
    
    // Route coordinates
    const routeCoordinates = {
        '1': { // Naval to Tacloban
            start: [11.5564, 124.2992], // Naval
            end: [11.2406, 125.0022],   // Tacloban
            name: 'Naval → Tacloban'
        },
        '2': { // Naval to Ormoc
            start: [11.5564, 124.2992], // Naval
            end: [11.0059, 124.6074],   // Ormoc
            name: 'Naval → Ormoc'
        }
    };
    
    function initTrackingMap() {
        if (!trackingMap) return;
        if (mapInstance) {
            setTimeout(() => mapInstance.invalidateSize(), 200);
            return;
        }
        if (typeof L === 'undefined') return;

        // Initialize map
        mapInstance = L.map('trackingMap').setView([11.2406, 125.0022], 12);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapInstance);
        
        // Start location tracking
        startLocationTracking();
        
        // Show active trip route if available
        showActiveTrip();
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
                currentPosition = [lat, lng];
                
                // Remove existing marker
                if (userLocationMarker) {
                    mapInstance.removeLayer(userLocationMarker);
                }
                
                // Create custom icon for user location
                const userIcon = L.divIcon({
                    className: 'user-location-marker',
                    html: `
                        <div style="
                            width: 20px; height: 20px; 
                            background: #4285f4; 
                            border: 3px solid white;
                            border-radius: 50%;
                            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                        "></div>
                    `,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                
                // Add new marker
                userLocationMarker = L.marker([lat, lng], { icon: userIcon }).addTo(mapInstance)
                    .bindPopup(`📍 Your Current Location<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
                
                mapInstance.setView([lat, lng], 15);
                
                // Setup road routing if destination exists
                setupRoute();
                
                document.getElementById('currentTripInfo').textContent = `📍 Live tracking started`;
            },
            (error) => {
                console.error('Geolocation error:', error);
                let errorMsg = 'Unable to get location.';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = '❌ Location access denied. Please enable location.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = '❌ Location information unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = '❌ Location request timed out.';
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
                currentPosition = [lat, lng];
                
                // Update marker position
                if (userLocationMarker) {
                    userLocationMarker.setLatLng([lat, lng]);
                    userLocationMarker.getPopup().setContent(`📍 Your Current Location<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
                }
                
                // Update route if tracking
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
        
        // Get destination from active trip
        fetch('../Php/get_passenger_trips.php?type=upcoming')
            .then(response => response.json())
            .then(data => {
                console.log('Trip data:', data); // Debug log
                if (data.success && data.trips.length > 0) {
                    const activeTrip = data.trips[0];
                    const routeId = String(activeTrip.route_id); // Convert to string
                    console.log('Route ID:', routeId, 'Available routes:', Object.keys(routeCoordinates)); // Debug log
                    
                    if (routeCoordinates[routeId]) {
                        const route = routeCoordinates[routeId];
                        const endPoint = L.latLng(route.end[0], route.end[1]);
                        
                        // Remove existing routing
                        if (routingControl) {
                            mapInstance.removeControl(routingControl);
                        }
                        
                        // Create road routing
                        routingControl = L.Routing.control({
                            waypoints: [L.latLng(currentPosition[0], currentPosition[1]), endPoint],
                            routeWhileDragging: false,
                            showAlternatives: false,
                            router: L.Routing.osrmv1 ? L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }) : undefined,
                            lineOptions: { styles: [{ color: '#4285f4', weight: 6, opacity: 0.8 }] },
                            createMarker: function() { return null; }
                        }).addTo(mapInstance);
                        
                        // Add destination marker
                        if (destinationMarker) {
                            mapInstance.removeLayer(destinationMarker);
                        }
                        
                        const destIcon = L.divIcon({
                            className: 'destination-marker',
                            html: `<div style="width: 30px; height: 30px; background: #ea4335; border: 3px solid white; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>`,
                            iconSize: [30, 30],
                            iconAnchor: [15, 30]
                        });
                        
                        destinationMarker = L.marker(route.end, { icon: destIcon }).addTo(mapInstance)
                            .bindPopup(`🎯 Destination: ${route.name.split(' → ')[1]}<br>📅 ${activeTrip.booking_date}<br>🕐 ${activeTrip.departure_time}`);
                        
                        isTracking = true;
                        
                        routingControl.on('routesfound', function(e) {
                            const routeData = e.routes[0];
                            const distance = (routeData.summary.totalDistance / 1000).toFixed(1);
                            const timeSec = routeData.summary.totalTime;
                            const eta = new Date(Date.now() + timeSec * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            document.getElementById('currentTripInfo').textContent = `🚐 Active Trip: ${route.name} | ${distance}km | ETA: ${eta}`;
                        });
                    } else {
                        console.log('Route not found for ID:', routeId);
                        document.getElementById('currentTripInfo').textContent = `📍 Trip found but route mapping missing for ID: ${routeId}`;
                    }
                } else {
                    console.log('No active trips found');
                }
            })
            .catch(error => console.error('Error setting up route:', error));
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

    // Show active trip route on map
    async function showActiveTrip() {
        try {
            const response = await fetch('../Php/get_passenger_trips.php?type=upcoming');
            const data = await response.json();
            
            if (data.success && data.trips.length > 0) {
                const activeTrip = data.trips[0];
                document.getElementById('currentTripInfo').textContent = `🚐 Active Trip: Seat ${activeTrip.seat_number}`;
            } else {
                document.getElementById('currentTripInfo').textContent = '📍 No active trips. Your location will be tracked.';
            }
        } catch (error) {
            console.error('Error loading active trip:', error);
            document.getElementById('currentTripInfo').textContent = '📍 Ready for location tracking.';
        }
    }
    
    // Center tracking button functionality
    document.getElementById('trackActiveTripBtn')?.addEventListener('click', () => {
        if (routingControl && mapInstance) {
            // Fit map to show full route
            const waypoints = routingControl.getWaypoints();
            if (waypoints.length >= 2) {
                const group = new L.featureGroup([L.marker(waypoints[0].latLng), L.marker(waypoints[1].latLng)]);
                mapInstance.fitBounds(group.getBounds().pad(0.1));
            }
        } else if (userLocationMarker && mapInstance) {
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
            trackBtn.textContent = '🎯 View Full Route';
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
            setTimeout(() => {
                initTrackingMap();
                showTrackingButton();
            }, 100);
        }
        if (sectionId === 'history') fetchTripHistory();
        if (sectionId === 'profile') storeOriginalData();
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
            const response = await fetch('../Php/process_passenger_booking_unified.php', {
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
    // 10. PROFILE MANAGEMENT
    // ===============================
    let originalProfileData = {};
    
    // Store original data for cancel functionality
    function storeOriginalData() {
        originalProfileData = {
            firstname: document.getElementById('profileFirstName').value,
            lastname: document.getElementById('profileLastName').value,
            email: document.getElementById('profileEmail').value,
            contact_number: document.getElementById('profilePhone').value,
            address: document.getElementById('profileAddress').value,
            gender: document.getElementById('profileGender').value,
            date_of_birth: document.getElementById('profileDOB').value
        };
    }
    
    // Initialize original data on page load
    storeOriginalData();
    
    // Edit profile button
    document.getElementById('editProfileBtn')?.addEventListener('click', () => {
        const inputs = document.querySelectorAll('#profileForm input, #profileForm textarea, #profileForm select');
        inputs.forEach(input => {
            input.disabled = false;
            input.style.background = '#ffffff';
        });
        
        document.getElementById('editProfileBtn').style.display = 'none';
        document.getElementById('saveProfileBtn').style.display = 'inline-flex';
        document.getElementById('cancelEditBtn').style.display = 'inline-flex';
    });
    
    // Cancel edit button
    document.getElementById('cancelEditBtn')?.addEventListener('click', () => {
        // Restore original data
        document.getElementById('profileFirstName').value = originalProfileData.firstname || '';
        document.getElementById('profileLastName').value = originalProfileData.lastname || '';
        document.getElementById('profileEmail').value = originalProfileData.email || '';
        document.getElementById('profilePhone').value = originalProfileData.contact_number || '';
        document.getElementById('profileAddress').value = originalProfileData.address || '';
        document.getElementById('profileGender').value = originalProfileData.gender || '';
        document.getElementById('profileDOB').value = originalProfileData.date_of_birth || '';
        
        // Disable inputs
        const inputs = document.querySelectorAll('#profileForm input, #profileForm textarea, #profileForm select');
        inputs.forEach(input => {
            input.disabled = true;
            input.style.background = '#f1f5f9';
        });
        
        document.getElementById('editProfileBtn').style.display = 'inline-flex';
        document.getElementById('saveProfileBtn').style.display = 'none';
        document.getElementById('cancelEditBtn').style.display = 'none';
    });
    
    // Save profile form
    document.getElementById('profileForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('firstName', document.getElementById('profileFirstName').value);
        formData.append('lastName', document.getElementById('profileLastName').value);
        formData.append('email', document.getElementById('profileEmail').value);
        formData.append('contactNumber', document.getElementById('profilePhone').value);
        formData.append('address', document.getElementById('profileAddress').value);
        formData.append('gender', document.getElementById('profileGender').value);
        formData.append('dateOfBirth', document.getElementById('profileDOB').value);
        
        fetch('../Php/update_passenger_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Profile updated successfully');
                
                // Update display name
                const firstName = document.getElementById('profileFirstName').value;
                const lastName = document.getElementById('profileLastName').value;
                const email = document.getElementById('profileEmail').value;
                document.getElementById('profileDisplayName').textContent = `${firstName} ${lastName}`.trim() || 'Passenger';
                document.getElementById('profileDisplayEmail').textContent = email || 'No email';
                
                // Store new original data
                storeOriginalData();
                
                // Disable inputs
                const inputs = document.querySelectorAll('#profileForm input, #profileForm textarea, #profileForm select');
                inputs.forEach(input => {
                    input.disabled = true;
                    input.style.background = '#f1f5f9';
                });
                
                document.getElementById('editProfileBtn').style.display = 'inline-flex';
                document.getElementById('saveProfileBtn').style.display = 'none';
                document.getElementById('cancelEditBtn').style.display = 'none';
            } else {
                showModal('Error', data.message, true);
            }
        })
        .catch(error => {
            console.error('Error updating profile:', error);
            showModal('Error', 'Failed to update profile', true);
        });
    });
    
    // Change password form
    document.getElementById('passwordForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('currentPassword', document.getElementById('currentPassword').value);
        formData.append('newPassword', document.getElementById('newPassword').value);
        formData.append('confirmPassword', document.getElementById('confirmPassword').value);
        
        fetch('../Php/change_passenger_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Password changed successfully');
                document.getElementById('passwordForm').reset();
            } else {
                showModal('Error', data.message, true);
            }
        })
        .catch(error => {
            console.error('Error changing password:', error);
            showModal('Error', 'Failed to change password', true);
        });
    });

    // ===============================
    // 11. LOGOUT CONFIRMATION
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
