    const terminalTimes = [
        '4:00 AM','5:00 AM','6:30 AM','7:30 AM','8:45 AM','9:45 AM','11:00 AM','1:30 PM','2:45 PM','3:30 PM','4:30 PM','5:20 PM'
    ];

    const bookingsByTime = {}; 
    const drivers = []; 
    const passengerNamesList = []; 
    const driverNamesList = []; 

    /* Helper function (kept for card rendering date) */
    function formatDate(d){
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
    }

    /* CUSTOM MESSAGE BOXES (Replaces illegal alert() and confirm()) */
    function showMessage(message, isError = false) {
        // Simple console log replacement for alerts
        console.log((isError ? "Error: " : "Success: ") + message);
        
        // In a real app, you would show a custom, non-blocking modal/toast here.
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed; top: 10px; right: 10px; padding: 10px 20px; 
            background: ${isError ? '#dc3545' : '#00adb5'}; color: white; 
            border-radius: 8px; z-index: 2000; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
    
    function showConfirm(message, callback) {
        // Simplified custom confirm logic
        const modal = document.createElement('div');
        modal.className = 'custom-modal-overlay';
        modal.innerHTML = `
            <div class="custom-modal max-w-sm">
                <h4 class="text-xl text-white font-medium mb-4">${message}</h4>
                <div class="flex justify-end space-x-3">
                    <button id="modal-cancel" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg">Cancel</button>
                    <button id="modal-confirm" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        modal.querySelector('#modal-confirm').onclick = () => { modal.remove(); callback(true); };
        modal.querySelector('#modal-cancel').onclick = () => { modal.remove(); callback(false); };
    }
    // END CUSTOM MESSAGE BOXES

    // Function to show custom notifications (kinopya mula sa nauna)
function showNotification(message, isError = false) {
    const notifBox = document.getElementById('customNotification');
    const notifText = document.getElementById('notificationText');
    
    // NOTE: Tiyakin na mayroon kang <div> with ID="customNotification" sa HTML mo!

    if (!notifBox || !notifText) {
        // Fallback kung wala ang notification box sa HTML
        console.log(`[NOTIF] ${isError ? 'ERROR' : 'SUCCESS'}: ${message}`);
        return; 
    }

    notifText.textContent = message;
    
    // Assume you have Tailwind classes for styling the notification box
    notifBox.classList.remove('bg-teal-500', 'bg-red-500', 'hidden');
    notifBox.classList.add(isError ? 'bg-red-500' : 'bg-teal-500', 'flex');
    
    setTimeout(() => {
        notifBox.classList.add('hidden');
        notifBox.classList.remove('flex');
    }, 3500);
}

// Function to handle AJAX form submissions (ITO ANG KAILANGAN MONG IDAGDAG)
async function handleRegistration(formId, processUrl, modalElement) {
    const form = document.getElementById(formId);
    
    if (!form) {
        console.error(`Form ID not found: ${formId}`);
        return;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;

        try {
            // DITO NAGAGANAP ANG AJAX CALL
            const response = await fetch(processUrl, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                // Kung HTTP error, halimbawa 404 o 500
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Inaasahan na ang PHP script (register_*.php) ay nagbabalik ng JSON
            const result = await response.json();
            
            if (result.success) {
                showNotification(result.message, false);
                form.reset();
                modalElement.style.display = 'none'; // Close the modal
            } else {
                showNotification(result.message, true);
            }

        } catch (error) {
            console.error('Registration Error:', error);
            showNotification('An unexpected network error occurred. Check console for details.', true);
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
}

    /* Render 12 booking cards (Now shows empty cards if bookingsByTime is empty) */
    function renderBookingsGrid(){
        const grid = document.getElementById('bookingsGrid');
        grid.innerHTML = '';
        
        const tempDriver = { name: 'Unassigned', route: 'N/A' };

        terminalTimes.forEach((time) => {
            const driverObj = tempDriver;
            const bookings = bookingsByTime[time] || [];

            const card = document.createElement('div');
            card.className = `booking-card ${bookings.length === 0 ? 'empty-card' : ''}`;
            card.dataset.time = time;
            card.innerHTML = `
                <div class="booking-header">
                    <div>
                        <div style="font-weight:700">${time} <span style="color:var(--muted);font-weight:400">(${formatDate(new Date())})</span></div>
                        <div class="driver">Driver: <strong>${driverObj.name}</strong></div>
                    </div>
                    <div>
                        <button class="depart-btn" data-time="${time}" ${bookings.length === 0 ? 'disabled' : ''}>Depart • ${time}</button>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Seat</th><th>Passenger</th><th>Route</th><th>Status</th><th>Time</th></tr>
                        </thead>
                        <tbody>
                            ${bookings.length === 0 ? '<tr><td colspan="5" style="text-align:center; color:var(--muted)">No passengers booked yet.</td></tr>' : 
                                bookings.map(row => `
                                    <tr>
                                        <td>${row.seat}</td>
                                        <td class="click-passenger" data-name="${row.passenger}">${row.passenger}</td>
                                        <td class="click-route">${row.route}</td>
                                        <td class="${row.status === 'Arrived' ? 'arrived' : 'not-arrived'}">${row.status}</td>
                                        <td>${row.status === 'Arrived' ? row.time : '—'}</td>
                                    </tr>
                                `).join('')
                            }
                        </tbody>
                    </table>
                </div>
            `;

            grid.appendChild(card);
            
            card.addEventListener('click', (e) => {
                if (e.target.classList.contains('depart-btn')) return;
                makeCardFullscreen(card);
            });
        });

        // depart handlers
        document.querySelectorAll('.depart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const card = btn.closest('.booking-card');
                if (btn.classList.contains('disabled')) return;
                btn.classList.add('disabled');
                btn.textContent = 'Departed';
                card.classList.add('grayed');
            });
        });

        attachRowHandlersInGrid();
    }

    function attachRowHandlersInGrid(){
        document.querySelectorAll('.click-passenger').forEach(cell => {
            cell.style.cursor = 'pointer';
            cell.addEventListener('click', () => {
                const name = cell.dataset.name;
                highlightName(name);
            });
        });
    }

    function highlightName(name){
        document.querySelectorAll('tbody tr').forEach(tr => tr.classList.remove('highlight'));
        const matches = [...document.querySelectorAll('td')].filter(td => td.textContent.trim() === name);
        if (matches.length){
            const tr = matches[0].closest('tr');
            tr.classList.add('highlight');
            tr.scrollIntoView({behavior:'smooth',block:'center'});
        }
    }


    /* Cards -> panels */
    function attachCardActions(){
        document.getElementById('card-users').addEventListener('click', () => openUsersPanel());
        document.getElementById('card-passengers').addEventListener('click', () => openPassengersPanel());
        document.getElementById('card-available').addEventListener('click', () => showAvailableDriversModal());
    }

    // ⬇️ CLOSE OTHER PANELS WHEN ONE IS OPENED
    const allPanels = ['usersPanel','passengersPanel','driversPanel'];

    function closeAllPanels(exceptId = null){
        allPanels.forEach(id=>{
            const panel = document.getElementById(id);
            if (panel && id !== exceptId){
                panel.style.display = 'none';
            }
        });
    }

    function attachPanelClose(panel){
        const closeBtn = document.createElement('div');
        closeBtn.style.marginTop = '12px';
        closeBtn.innerHTML = '<button class="close-panel bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg">Close Panel</button>';
        panel.appendChild(closeBtn);
        // Use the new CSS class 'close-panel' for positioning consistency
        panel.querySelector('.close-panel').style.position = 'absolute';
        panel.querySelector('.close-panel').style.top = '20px';
        panel.querySelector('.close-panel').style.right = '20px';

        panel.querySelector('.close-panel').addEventListener('click', () => { panel.style.display='none'; });
    }

    /* Users panel */
    function openUsersPanel(){
        closeAllPanels('usersPanel');
        const panel = document.getElementById('usersPanel');
        panel.style.display = 'block';
        panel.innerHTML = '<h2>All Users (Recent rides)</h2><div class="table-wrap"><table><thead><tr><th>Seat</th><th>Name</th><th>Route</th><th>Driver</th><th>Status</th><th>Time</th><th>Date</th><th>Departure</th></tr></thead><tbody><tr><td colspan="8" style="text-align:center; color:var(--muted)">Loading user data from database...</td></tr></tbody></table></div>';
        if (!panel.querySelector('.close-panel')) {
            attachPanelClose(panel);
        }
    }

    /* Passengers panel */
    function openPassengersPanel(){
        closeAllPanels('passengersPanel');
        const panel = document.getElementById('passengersPanel');
        panel.style.display = 'block';
        panel.innerHTML = '<h2>Passengers (Today)</h2><div class="table-wrap"><table><thead><tr><th>Seat</th><th>Name</th><th>Route</th><th>Status</th><th>Time</th><th>Departure</th></tr></thead><tbody><tr><td colspan="6" style="text-align:center; color:var(--muted)">Loading passenger bookings from database...</td></tr></tbody></table></div>';
        if (!panel.querySelector('.close-panel')) {
            attachPanelClose(panel);
        }
    }

    /* Drivers panel */
    function openDriversPanel(){
        closeAllPanels('driversPanel');
        const panel = document.getElementById('driversPanel');
        panel.style.display = 'block';
        // Content is loaded by PHP, just ensure the panel and close button are visible.
        if (!panel.querySelector('.close-panel')) {
            attachPanelClose(panel);
        }
    }

    /* Attaches the single delegated listener to the drivers table. Called once. */
    function initDriversPanelListeners(){
        const driversTableBody = document.getElementById('driversTableBody');
        if (!driversTableBody) return;
        
        driversTableBody.addEventListener('click', function(event) {
            const target = event.target.closest('.assign-route-btn');
            if (target) {
                const driverId = target.getAttribute('data-id'); 
                const driverName = target.getAttribute('data-name');
                
                openAssignRouteModal(driverId, driverName);
            }
        });
    }

    /* Driver detail modal (Requires real data.) -- RETAINED AS IS */
    function openDriverModal(d){
        const overlay = document.createElement('div'); overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal">
                <img src="${d.vanImage || 'https://via.placeholder.com/240x140?text=No+Image'}" alt="${d.name} van">
                <h3>${d.name}</h3>
                <p><strong>Plate:</strong> ${d.plate || 'N/A'}  •  <strong>License ID:</strong> ${d.licenseId || 'N/A'}</p>
                <p><strong>License No:</strong> ${d.licenseNo || 'N/A'}  •  <strong>Status:</strong> ${d.married || 'N/A'}</p>
                <p><strong>Route:</strong> ${d.route || 'N/A'}</p>
                <p><strong>Address:</strong> ${d.address || 'N/A'}</p>
                <div style="display:flex;gap:8px;justify-content:center;margin-top:12px;">
                    <button class="close-modal bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg">Close</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        overlay.querySelector('.close-modal').addEventListener('click', ()=> overlay.remove());
        overlay.addEventListener('click',(e)=>{ if (e.target===overlay) overlay.remove(); });
    }

    /* NEW MODAL FUNCTIONS FOR REGISTRATION */
    function openAdminRegisterModal() {
        const modal = document.getElementById('adminRegisterModal');
        // Set auto-generated date
        const dateInput = document.getElementById('admin_regDate');
        if (dateInput) {
            dateInput.value = formatDate(new Date());
        }
        modal.style.display = 'flex';
    }

    function openDriverRegisterModal() {
        const modal = document.getElementById('driverRegisterModal');
        modal.style.display = 'flex';
    }
    /* END NEW MODAL FUNCTIONS */

    /* Search suggestions (Disabled) */
    function initSearch(){
        const wrap = document.querySelector('.search-wrap');
        const input = document.getElementById('search');
        if (!wrap || !input) return;
        
        const suggestions = document.createElement('div'); 
        suggestions.className='search-suggestions'; 
        wrap.appendChild(suggestions);

        input.addEventListener('input', (e)=>{
            suggestions.innerHTML = '';
            suggestions.style.display='none'; 
        });

        document.addEventListener('click',(ev)=>{ if (!wrap.contains(ev.target)) suggestions.style.display='none'; });
    }


    // Fullscreen/Modal functions (kept)
    function makeCardFullscreen(cardElement) {
        const clone = cardElement.cloneNode(true);
        const overlay = document.createElement('div');
        overlay.className = 'fullscreen-card';
        overlay.innerHTML = '<button class="close-fullscreen bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg">Close</button>';
        overlay.appendChild(clone);
        document.body.appendChild(overlay);

        overlay.querySelector('.close-fullscreen').addEventListener('click', () => overlay.remove());
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.remove();
        });
    }

    // Chart functions (kept)
    function renderTrendChart(){
        const ctx = document.getElementById('trendChart')?.getContext('2d');
        if (!ctx) return;
        const labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        const data = { labels, datasets: [{ label:'Bookings', data:[32,45,40,55,60,72,68], fill:true, backgroundColor: 'rgba(0,173,181,0.12)', borderColor: '#00adb5', tension:0.35, pointRadius:4 }] };
        const cfg = { type:'line', data, options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ grid:{color:'rgba(255,255,255,0.04)'}, ticks:{color:'#cfeff3'} }, x:{ ticks:{color:'#9fb6c8'}, grid:{display:false} } } } };
        new Chart(ctx, cfg);
    }
    function fillMiniCurve(){
        const cw = document.getElementById('mini-curve');
        if (!cw) return;
        cw.style.height = "40px";
        cw.style.borderRadius = "6px";
        cw.style.overflow = "hidden";
        cw.innerHTML = `<svg viewBox="0 0 200 40" preserveAspectRatio="none" width="100%"><path d="M0 30 Q50 5 100 20 T200 12 L200 40 L0 40 Z" fill="rgba(0,173,181,0.12)"></path></svg>`;
    }

    // Navigation Handler (kept)
    const sections = {
        dashboard: document.querySelector(".dashboard"),
        routes: document.getElementById("routesSection"),
        drivers: document.getElementById("driversSection"),
        reports: document.getElementById("reportsSection"),
        logout: document.getElementById("logoutSection")
    };
    function showSection(activeSection) {
        for (let key in sections) {
            if (sections[key]) sections[key].style.display = key === activeSection ? "block" : "none";
        }

        document.querySelectorAll(".sidebar a").forEach(a => a.classList.remove("active"));
        document.querySelector(`#nav-${activeSection}`)?.classList.add("active");
    }


    // Route Form Handlers (kept)
    const form = document.getElementById('routeForm');
    const table = document.getElementById('routesTable');
    const editIndexInput = document.getElementById('editIndex');

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const routeID = document.getElementById('routeID').value;
            const origin = document.getElementById('origin').value;
            const destination = document.getElementById('destination').value;
            const distance = document.getElementById('distance').value;
            const fare = document.getElementById('fare').value;
            const editIndex = editIndexInput.value;

            if (editIndex) {
                const row = table.rows[parseInt(editIndex) + 1]; 
                row.cells[0].innerText = routeID;
                row.cells[1].innerText = origin;
                row.cells[2].innerText = destination;
                row.cells[3].innerText = distance;
                row.cells[4].innerText = fare;
                editIndexInput.value = '';
                showMessage('Route updated successfully!', false);
            } else {
                const newRow = table.insertRow();
                newRow.innerHTML = `
                    <td>${routeID}</td>
                    <td>${origin}</td>
                    <td>${destination}</td>
                    <td>${distance}</td>
                    <td>${fare}</td>
                    <td>
                        <button class="btn-edit bg-yellow-500 py-1 px-2 text-dark rounded" onclick="editRoute(this)">Edit</button>
                        <button class="btn-delete bg-red-500 py-1 px-2 text-white rounded" onclick="deleteRoute(this)">Delete</button>
                    </td>
                `;
                showMessage('New route added successfully!', false);
            }
            resetForm();
        });
    }

    function editRoute(button) {
        const row = button.closest('tr');
        const index = row.rowIndex - 1; 
        document.getElementById('routeID').value = row.cells[0].innerText;
        document.getElementById('origin').value = row.cells[1].innerText;
        document.getElementById('destination').value = row.cells[2].innerText;
        document.getElementById('distance').value = row.cells[3].innerText;
        document.getElementById('fare').value = row.cells[4].innerText;
        document.getElementById('editIndex').value = index;
    }

    function deleteRoute(button) {
        showConfirm('Are you sure you want to delete this route?', (confirmed) => {
            if (confirmed) {
                const row = button.closest('tr');
                row.remove();
                showMessage('Route deleted successfully.', false);
            }
        });
    }

    function resetForm() {
        document.getElementById('routeForm').reset();
        document.getElementById('editIndex').value = '';
    }


    /* Assign Route Modal Logic (Function to open the modal) */
    async function loadDrivers() {
        try {
            const response = await fetch('../Php/get_drivers.php');
            const data = await response.json();
            
            if (data.success) {
                window.allDrivers = data.drivers;
                renderDriversTable(data.drivers);
                updateDriverCounts(data.drivers);
            }
        } catch (error) {
            console.error('Error loading drivers:', error);
        }
    }
    
    function renderDriversTable(drivers) {
        const tbody = document.getElementById('driversTableBody');
        if (!tbody) return;
        
        if (drivers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:var(--muted)">No drivers found.</td></tr>';
            return;
        }
        
        tbody.innerHTML = drivers.map(driver => {
            const status = driver.status || 'Available';
            const routeInfo = driver.route_name ? `${driver.origin} → ${driver.destination} (${driver.departure_time})` : 'Not Assigned';
            
            return `
                <tr>
                    <td>${driver.first_name} ${driver.last_name}</td>
                    <td>${driver.email}</td>
                    <td><span class="status-badge status-${status.toLowerCase()}">${status}</span></td>
                    <td>${driver.driver_id || 'N/A'}</td>
                    <td>${driver.license_number || 'N/A'}</td>
                    <td>${driver.plate_number || 'N/A'}</td>
                    <td>${routeInfo}</td>
                    <td>
                        <button class="assign-route-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded" 
                                data-id="${driver.driver_id}" 
                                data-name="${driver.first_name} ${driver.last_name}">
                            Assign Route
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    function updateDriverCounts(drivers) {
        const onTrip = drivers.filter(d => d.status === 'On Trip').length;
        const available = drivers.filter(d => d.status === 'Available').length;
        
        document.getElementById('onTripDrivers').textContent = onTrip;
        document.getElementById('availableDrivers').textContent = available;
        
        // Update card styling based on counts
        const availableCard = document.getElementById('card-available');
        const onTripCard = document.getElementById('card-ontrip');
        
        if (available > 0) {
            availableCard.style.cursor = 'pointer';
            availableCard.style.opacity = '1';
        } else {
            availableCard.style.cursor = 'default';
            availableCard.style.opacity = '0.6';
        }
    }
    
    function showAvailableDriversModal() {
        const modal = document.createElement('div');
        modal.className = 'custom-modal-overlay';
        modal.innerHTML = `
            <div class="custom-modal max-w-2xl">
                <h3 class="modal-header text-2xl font-semibold text-primary">Available Drivers - Assign to On Trip</h3>
                <button class="close-modal bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded absolute top-4 right-4">×</button>
                <div class="mb-4 mt-4">
                    <button id="assignSelectedBtn" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded" disabled>
                        Set Selected to On Trip
                    </button>
                </div>
                <div class="table-wrap">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllDrivers"></th>
                                <th>Name</th>
                                <th>Route</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="availableDriversTable">
                            <tr><td colspan="4" style="text-align:center; color:var(--muted)">Loading available drivers...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        modal.querySelector('.close-modal').onclick = () => modal.remove();
        modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
        
        loadAvailableDrivers(modal);
    }
    
    async function loadAvailableDrivers(modal) {
        try {
            const response = await fetch('../Php/get_drivers_with_status.php');
            const data = await response.json();
            
            if (data.success) {
                const availableDrivers = data.drivers.filter(d => d.status === 'Available');
                renderAvailableDriversTable(availableDrivers, modal);
            }
        } catch (error) {
            console.error('Error loading available drivers:', error);
        }
    }
    
    function renderAvailableDriversTable(drivers, modal) {
        const tbody = modal.querySelector('#availableDriversTable');
        
        if (drivers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--muted)">No available drivers found.</td></tr>';
            return;
        }
        
        tbody.innerHTML = drivers.map(driver => {
            const routeInfo = driver.route_name || 'Not Assigned';
            const timeInfo = driver.time_slot || 'N/A';
            
            return `
                <tr>
                    <td><input type="checkbox" class="driver-checkbox" data-id="${driver.driver_id}" data-name="${driver.firstname} ${driver.lastname}"></td>
                    <td>${driver.firstname || 'N/A'} ${driver.lastname || 'N/A'}</td>
                    <td>${routeInfo}</td>
                    <td>${timeInfo}</td>
                </tr>
            `;
        }).join('');
        
        // Select all functionality
        const selectAll = modal.querySelector('#selectAllDrivers');
        const checkboxes = modal.querySelectorAll('.driver-checkbox');
        const assignBtn = modal.querySelector('#assignSelectedBtn');
        
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateAssignButton();
        });
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateAssignButton);
        });
        
        function updateAssignButton() {
            const selected = modal.querySelectorAll('.driver-checkbox:checked');
            assignBtn.disabled = selected.length === 0;
            assignBtn.textContent = selected.length > 0 ? `Set ${selected.length} Driver(s) to On Trip` : 'Set Selected to On Trip';
        }
        
        assignBtn.addEventListener('click', function() {
            const selected = modal.querySelectorAll('.driver-checkbox:checked');
            if (selected.length > 0) {
                assignMultipleDriversToOnTrip(selected, modal);
            }
        });
    }
    
    async function assignMultipleDriversToOnTrip(selectedCheckboxes, modal) {
        const driverIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.id);
        const driverNames = Array.from(selectedCheckboxes).map(cb => cb.dataset.name);
        
        try {
            const formData = new FormData();
            formData.append('driver_ids', JSON.stringify(driverIds));
            formData.append('status', 'On Trip');
            
            const response = await fetch('../Php/update_multiple_driver_status.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage(`${driverNames.length} driver(s) assigned to On Trip status`, false);
                modal.remove();
                loadDrivers(); // Refresh driver counts
            } else {
                showMessage('Error: ' + data.message, true);
            }
        } catch (error) {
            console.error('Error updating driver status:', error);
            showMessage('An error occurred while updating driver status', true);
        }
    }
    
    async function updateDriverStatus(driverId, status) {
        try {
            const formData = new FormData();
            formData.append('driver_id', driverId);
            formData.append('status', status);
            
            const response = await fetch('../Php/update_driver_status.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage(`Driver status updated to ${status}`, false);
                loadDrivers(); // Refresh driver counts
            } else {
                showMessage('Error: ' + data.message, true);
            }
        } catch (error) {
            console.error('Error updating driver status:', error);
            showMessage('An error occurred while updating driver status', true);
        }
    }
    
    async function loadDriversList() {
        try {
            const response = await fetch('../Php/get_drivers_with_status.php');
            const data = await response.json();
            
            console.log('Drivers data:', data);
            
            if (data.success) {
                renderDriversListTable(data.drivers);
            } else {
                console.error('Failed to load drivers:', data.message);
            }
        } catch (error) {
            console.error('Error loading drivers list:', error);
        }
    }
    
    function renderDriversListTable(drivers) {
        const tbody = document.getElementById('driversListTable');
        if (!tbody) {
            console.error('driversListTable element not found');
            return;
        }
        
        console.log('Rendering drivers:', drivers);
        
        if (drivers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:var(--muted)">No drivers found.</td></tr>';
            return;
        }
        
        tbody.innerHTML = drivers.map(driver => {
            const isAssigned = driver.status === 'Available' || driver.status === 'On Trip';
            const routeInfo = driver.route_name ? `${driver.origin} → ${driver.destination} (${driver.time_slot})` : 'Not Assigned';
            const status = driver.status || 'Unassigned';
            const rowClass = isAssigned ? 'text-gray-500' : '';
            
            return `
                <tr class="${rowClass}">
                    <td>
                        <span class="${isAssigned ? 'text-gray-500' : ''}">${driver.firstname || 'N/A'} ${driver.lastname || 'N/A'}</span>
                    </td>
                    <td class="${isAssigned ? 'text-gray-500' : ''}">${driver.contact || 'N/A'}</td>
                    <td class="${isAssigned ? 'text-gray-500' : ''}">${driver.contact || 'N/A'}</td>
                    <td class="${isAssigned ? 'text-gray-500' : ''}">${driver.license_number || 'N/A'}</td>
                    <td class="${isAssigned ? 'text-gray-500' : ''}">${driver.plate_number || 'N/A'}</td>
                    <td><span class="status-badge status-${status.toLowerCase()}">${status}</span></td>
                    <td class="${isAssigned ? 'text-gray-500' : ''}">${routeInfo}</td>
                    <td>
                        <button class="assign-route-btn ${isAssigned ? 'bg-gray-500 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600'} text-white py-1 px-3 rounded" 
                                data-id="${driver.driver_id}" 
                                data-name="${driver.firstname} ${driver.lastname}"
                                ${isAssigned ? 'disabled' : ''}>
                            ${isAssigned ? 'Assigned' : 'Assign Route'}
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Add event listeners for individual assign buttons
        tbody.addEventListener('click', function(event) {
            const target = event.target.closest('.assign-route-btn');
            if (target && !target.disabled) {
                const driverId = target.getAttribute('data-id'); 
                const driverName = target.getAttribute('data-name');
                
                openAssignRouteModal(driverId, driverName);
            }
        });
    }

    function populateRouteDropdown(routeSelectElement, currentRouteId = null) {
        routeSelectElement.innerHTML = `
            <option value="" disabled selected>Select Route & Time</option>
            <option value="1|06:00:00">Naval → Tacloban (6:00 AM)</option>
            <option value="1|10:00:00">Naval → Tacloban (10:00 AM)</option>
            <option value="2|06:00:00">Naval → Ormoc (6:00 AM)</option>
            <option value="2|10:00:00">Naval → Ormoc (10:00 AM)</option>
        `;
    }



    
    function openAssignRouteModal(driverId, driverName) {
        const assignRouteModal = document.getElementById('assignRouteModal');
        const assignRouteForm = document.getElementById('assignRouteForm');
        
        if (!assignRouteModal || !assignRouteForm) return;

        const driverIdToAssign = document.getElementById('driverIdToAssign');
        const driverNameForAssignment = document.getElementById('driverNameForAssignment');
        const routeSelect = document.getElementById('routeSelect');
        
        driverIdToAssign.value = driverId;
        driverNameForAssignment.textContent = driverName;
        
        populateRouteDropdown(routeSelect);

        assignRouteModal.style.display = 'flex';
    }

    // ----------------------------------------------------
    // Main DOMContentLoaded Block
    // ----------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        // --- 0. Initial Dashboard Rendering ---
        document.getElementById('totalUsers').textContent = '0'; 
        document.getElementById('totalPassengers').textContent = '0';
        document.getElementById('onTripDrivers').textContent = '0';
        document.getElementById('availableDrivers').textContent = '0';
        
        loadDrivers();

        renderBookingsGrid(); 
        attachCardActions();
        initSearch(); 
        renderTrendChart();
        fillMiniCurve();
        initDriversPanelListeners(); // Attach the driver assignment listener once
        
        // --- NEW: Registration Button Listeners ---
        document.getElementById("registerAdminBtn").addEventListener("click", openAdminRegisterModal);
        document.getElementById("registerDriverBtn").addEventListener("click", openDriverRegisterModal);

        // --- 1. Sidebar Navigation ---
        for (let key in sections) {
            if (sections[key]) sections[key].style.display = key === "dashboard" ? "block" : "none";
        }

        document.getElementById("nav-routes")?.addEventListener("click", () => showSection("routes"));
        document.getElementById("nav-drivers")?.addEventListener("click", () => {
            showSection("drivers");
            loadDriversList();
        });
        document.getElementById("nav-reports")?.addEventListener("click", () => showSection("reports"));
        document.getElementById("nav-logout")?.addEventListener("click", () => {
            showConfirm('Are you sure you want to logout?', (confirmed) => {
                if (confirmed) {
                    window.location.href = '../php/logout.php';
                }
            });
        });
        document.querySelector(".sidebar a.active")?.addEventListener("click", () => showSection("dashboard"));
        
        // --- 3. Route Assignment Modal Listeners (for closing/submission) ---
        const assignRouteModal = document.getElementById('assignRouteModal');
        const assignRouteForm = document.getElementById('assignRouteForm');
        
        if (assignRouteModal && assignRouteForm) {
            const closeBtn = assignRouteModal.querySelector('.close-btn');

            closeBtn.onclick = function() {
                assignRouteModal.style.display = 'none';
                assignRouteForm.reset();
            }

            window.onclick = function(event) {
                if (event.target == assignRouteModal) {
                    assignRouteModal.style.display = 'none';
                    assignRouteForm.reset();
                }
            }

            // Form Submission Handler (AJAX)
            assignRouteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('.btn-save');
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';

                // Parse route and time from selection
                const routeTimeValue = formData.get('routeId');
                if (!routeTimeValue || !routeTimeValue.includes('|')) {
                    throw new Error('Please select a valid route and time');
                }
                const [routeId, departureTime] = routeTimeValue.split('|');
                
                const assignData = new FormData();
                assignData.append('driver_id', formData.get('driverId'));
                assignData.append('route_id', routeId);
                assignData.append('departure_time', departureTime);
                
                fetch('../Php/simple_assign_driver.php', { method: 'POST', body: assignData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Success: ' + data.message, false);
                        loadDrivers();
                    } else {
                        showMessage('Error: ' + data.message, true);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showMessage('An unexpected error occurred. Check console for details.', true);
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Save Assignment';
                    assignRouteModal.style.display = 'none';
                    assignRouteForm.reset();
                });
            });
        }
        
        // --- 4. Booking Table Expand/Shrink (for touch/click focus) ---
        document.querySelectorAll('.booking-card table').forEach(table => {
            table.addEventListener('click', () => {
                const card = table.closest('.booking-card');
                if (table.classList.contains('expanded')) {
                    table.classList.remove('expanded');
                    card.style.zIndex = '';
                    card.style.transform = '';
                } else {
                    document.querySelectorAll('.booking-card table.expanded').forEach(openTable => {
                        openTable.classList.remove('expanded');
                        openTable.closest('.booking-card').style.zIndex = '';
                        openTable.closest('.booking-card').style.transform = '';
                    });
                    table.classList.add('expanded');
                    card.style.zIndex = '999';
                    card.style.transform = 'scale(1.05)';
                }
            });
        });
        
       // --- 5. Registration Form Submission Handlers (New) ---
const adminModal = document.getElementById('adminRegisterModal');
const driverModal = document.getElementById('driverRegisterModal');

// Gagamitin na natin ang bagong handleRegistration function
if (adminModal) {
    handleRegistration('adminRegistrationForm', '/Biyahe/Php/register_admin_process.php', adminModal);
}
if (driverModal) {
    handleRegistration('driverRegistrationForm', '/Biyahe/Php/register_driver_process.php', driverModal);
}

        
        // Simple mobile menu toggle
        const hamburger = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        if (hamburger && sidebar && overlay) {
            hamburger.onclick = function() {
                hamburger.classList.toggle('active');
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
            };
            
            overlay.onclick = function() {
                hamburger.classList.remove('active');
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
            };
        }
        
        // Mobile search toggle function
        window.toggleMobileSearch = function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            const hamburger = document.getElementById('mobileMenuToggle');
            const mobileSearch = document.getElementById('mobileSearch');
            
            sidebar.classList.add('open');
            overlay.classList.add('show');
            hamburger.classList.add('active');
            
            setTimeout(function() {
                if (mobileSearch) {
                    mobileSearch.focus();
                }
            }, 300);
        };
        
        });