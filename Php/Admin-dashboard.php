<?php
session_start();

// Use Supabase connection
include_once('supabase_db.php'); 

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit;
}
$displayName = $_SESSION['name'] ?? 'Admin';

// Check if the logged-in user has admin role
if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access denied. Admin only.'); window.location.href='../index.html';</script>";
    exit;
}

// Fetch Dashboard Counts using Supabase
$totalUsers = 0; $activeDrivers = 0; $inactiveDrivers = 0;

try {
    // Get total users
    $users = supabaseQuery('users', 'GET');
    $totalUsers = count($users);
    
    // Count drivers
    $drivers = array_filter($users, function($user) {
        return $user['user_type'] === 'driver';
    });
    $activeDrivers = count($drivers);
    $inactiveDrivers = 0; // Placeholder
    
} catch (Exception $e) {
    // Handle error silently for now
    error_log("Dashboard stats error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Biyahe</title>
    <!-- Load Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Chart.js for the trend chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../styles/Admin-dashboard.css">
</head>
<body class="flex min-h-screen">

    <!-- Mobile Menu Toggle -->
    <button id="mobileMenuToggle" class="mobile-menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open'); document.getElementById('mobileOverlay').classList.toggle('show'); this.classList.toggle('active');">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">

    <!-- Sidebar Logo -->
    <img src="../images/logo.png" class="w-28 h-28 -mt-4 mb-1">

    <h1 class="text-xl font-bold text-white -mt-1 mb-6">Biyahe Now</h1>
        <nav>
            <a href="#" id="nav-dashboard" class="active"><span class="mr-2">🏠</span> Dashboard</a>
            <a href="#" id="nav-routes"><span class="mr-2">🛣️</span> Route Management</a>
            <a href="#" id="nav-drivers"><span class="mr-2">🚗</span> Drivers</a>
            <a href="#" id="nav-reports"><span class="mr-2">📊</span> Reports</a>
            <a href="#" id="nav-logout"><span class="mr-2">🚪</span> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto">
        
        <!-- Dashboard Section -->
        <section class="dashboard">
            <header class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-semibold">Welcome, <span id="adminName"><?php echo htmlspecialchars($displayName); ?>!</span> </h2>
                <h3 class="text-3xl font-semibold">Today's Operations</h3>
                <div class="desktop-actions">
                    <!-- New Registration Buttons -->
                    <button id="registerAdminBtn" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg transition">
                        Register New Admin
                    </button>
                    <button id="registerDriverBtn" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition">
                        Register New Driver
                    </button>
                    <!-- Search placeholder -->
                    <div class="search-wrap relative">
                        <button class="mobile-search-icon" onclick="var box = document.getElementById('mobileSearchBox'); box.style.display = 'flex'; document.getElementById('mobileSearchInput').focus();">🔍</button>
                        <input type="text" id="search" placeholder="Search passenger/driver" class="bg-secondary p-2 rounded-lg text-sm text-light border border-[#4a505b]">
                        <div id="mobileSearchBox" class="mobile-search-box">
                            <input type="text" id="mobileSearchInput" placeholder="Search...">
                            <button onclick="document.getElementById('mobileSearchBox').style.display = 'none';">✕</button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div id="card-users" class="card p-5">
                    <div class="text-2xl font-bold text-primary" id="totalUsers">0</div>
                    <div class="text-sm text-muted">Total Users</div>
                    <div id="mini-curve" class="mt-2"></div>
                </div>
                <div id="card-passengers" class="card p-5">
                    <div class="text-2xl font-bold text-primary" id="totalPassengers">0</div>
                    <div class="text-sm text-muted">Total Passengers (Today)</div>
                </div>
                <div id="card-ontrip" class="card p-5 cursor-pointer">
                    <div class="text-2xl font-bold text-primary" id="onTripDrivers">0</div>
                    <div class="text-sm text-muted">On Trip</div>
                </div>
                <div id="card-available" class="card p-5 cursor-pointer">
                    <div class="text-2xl font-bold text-primary" id="availableDrivers">0</div>
                    <div class="text-sm text-muted">Available</div>
                </div>
            </div>

            <!-- Bookings Grid -->
            <div class="admin-panel">
                <h3 class="text-xl font-semibold mb-4">Today's Departure Schedule</h3>
                <div id="bookingsGrid" class="bookings-grid">
                    <!-- Booking Cards will be rendered here by JS -->
                </div>
            </div>

            <!-- Trend Chart -->
            <div class="admin-panel mb-8 p-6">
                <h3 class="text-xl font-semibold mb-4">Weekly Booking Trend</h3>
                <canvas id="trendChart"></canvas>
            </div>

            <!-- Panels for Details (Users, Passengers, Drivers) -->
            <div id="usersPanel" class="admin-panel mt-6" style="display:none;"></div>
            <div id="passengersPanel" class="admin-panel mt-6" style="display:none;"></div>
            <div id="driversPanel" class="admin-panel mt-6" style="display:none;">
                <!-- Placeholder for Drivers Table loaded by PHP -->
                <h2>Drivers (Admin Assignment)</h2>
                <div class="table-wrap">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Name</th><th>Address</th><th>Status</th><th>License ID</th><th>License No</th><th>Plate</th><th>Route</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="driversTableBody">
                            <tr><td colspan="8" style="text-align:center; color:var(--muted)">Loading drivers...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Routes Management Section -->
        <section id="routesSection" style="display:none;">
            <h2 class="text-3xl font-semibold mb-6">Routes Management</h2>
            <div class="admin-panel p-6 mb-8">
                <h3 class="text-xl font-semibold mb-4">Add / Edit Route</h3>
                <form id="routeForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="hidden" id="editIndex">
                    <input type="text" id="routeID" placeholder="Route ID (e.g., BIL-001)" required class="col-span-1">
                    <input type="text" id="origin" placeholder="Origin" required class="col-span-1">
                    <input type="text" id="destination" placeholder="Destination" required class="col-span-1">
                    <input type="number" id="distance" placeholder="Distance (km)" required class="col-span-1">
                    <input type="number" id="fare" placeholder="Fare (PHP)" required class="col-span-1">
                    <div class="col-span-1 flex space-x-2">
                        <button type="submit" class="btn-submit w-full">Save Route</button>
                        <button type="button" onclick="resetForm()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition">Clear</button>
                    </div>
                </form>
            </div>
            <div class="admin-panel p-6">
                <h3 class="text-xl font-semibold mb-4">Existing Routes</h3>
                <div class="table-wrap">
                    <table id="routesTable" class="table w-full">
                        <thead>
                            <tr>
                                <th>Route ID</th>
                                <th>Origin</th>
                                <th>Destination</th>
                                <th>Distance</th>
                                <th>Fare</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dummy Route Data for demonstration and modal dropdown -->
                            <tr><td>BIL-001</td><td>Naval</td><td>Kawayan</td><td>35</td><td>150</td><td><button class="btn-edit bg-yellow-500 py-1 px-2 text-dark rounded" onclick="editRoute(this)">Edit</button> <button class="btn-delete bg-red-500 py-1 px-2 text-white rounded" onclick="deleteRoute(this)">Delete</button></td></tr>
                            <tr><td>BIL-002</td><td>Naval</td><td>Almeria</td><td>20</td><td>100</td><td><button class="btn-edit bg-yellow-500 py-1 px-2 text-dark rounded" onclick="editRoute(this)">Edit</button> <button class="btn-delete bg-red-500 py-1 px-2 text-white rounded" onclick="deleteRoute(this)">Delete</button></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Drivers Management Section -->
            <div class="admin-panel p-6">
                <h3 class="text-xl font-semibold mb-4">Drivers Management</h3>
                <div class="table-wrap">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>License Number</th>
                                <th>Plate Number</th>
                                <th>Assigned Route</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="driversManagementTable">
                            <tr><td colspan="7" style="text-align:center; color:var(--muted)">Loading drivers...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        
        <!-- Drivers Section -->
        <section id="driversSection" style="display:none;">
            <h2 class="text-3xl font-semibold mb-6">Drivers Management</h2>
            <div class="admin-panel p-6">
                <h3 class="text-xl font-semibold mb-4">All Drivers</h3>
                <div class="table-wrap">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>License Number</th>
                                <th>Plate Number</th>
                                <th>Status</th>
                                <th>Assigned Route</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="driversListTable">
                            <tr><td colspan="8" style="text-align:center; color:var(--muted)">Loading drivers...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        
        <!-- Reports Section -->
        <section id="reportsSection" style="display:none;">
            <h2 class="text-3xl font-semibold mb-6">Reports and Analytics</h2>
            <div class="admin-panel p-6">
                <p class="text-muted">Detailed reports and analytics will be displayed here.</p>
            </div>
        </section>

        <!-- Logout Section (Dummy) -->
        <section id="logoutSection" style="display:none;">
            <h2 class="text-3xl font-semibold mb-6">Confirm Logout</h2>
            <div class="admin-panel p-6">
                <p>Are you sure you want to log out?</p>
                <button id="logoutBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg mt-4">
                    Logout Now
                </button>
            </div>
        </section>

    </main>
    
    <!-- MODALS -->

    <!-- 1. Assign Route Modal (Existing) -->
    <div id="assignRouteModal" class="custom-modal-overlay" style="display:none;">
        <div class="custom-modal">
            <h3 class="modal-header text-2xl font-semibold text-primary">Assign Route to Driver</h3>
            <span class="close-btn absolute top-4 right-4 text-3xl font-light cursor-pointer text-muted hover:text-red-500">&times;</span>
            
            <form id="assignRouteForm">
                <input type="hidden" id="driverIdToAssign" name="driverId">
                <p class="mb-4 text-lg">Assigning route for: <strong id="driverNameForAssignment" class="text-white"></strong></p>
                
                <div class="modal-grid">
                    <div class="form-group full-width">
                        <label for="routeSelect">Select Route & Time</label>
                        <select id="routeSelect" name="routeId" required></select>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <button type="submit" class="btn-submit btn-save">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 2. Admin Registration Modal (NEW) -->
    <div id="adminRegisterModal" class="custom-modal-overlay" style="display:none;">
        <div class="custom-modal">
            <h3 class="modal-header text-2xl font-semibold text-primary">Admin Registration (Basic Info)</h3>
            <button class="close-modal bg-red-500 hover:bg-red-600" onclick="document.getElementById('adminRegisterModal').style.display='none'">Close</button>
            
            <form id="adminRegistrationForm">
                <input type="hidden" name="role" value="admin">
                <div class="form-message-area text-sm mb-4"></div>
                
                <div class="modal-grid">
                    <!-- Basic Info -->
                    <div class="form-group"> <label for="admin_lastName">Last Name</label> <input type="text" id="admin_lastName" name="lastName" required> </div>
                    <div class="form-group"> <label for="admin_firstName">First Name</label> <input type="text" id="admin_firstName" name="firstName" required> </div>
                    <div class="form-group"> <label for="admin_email">Email Address</label> <input type="email" id="admin_email" name="email" required> </div>
                    <div class="form-group"> <label for="admin_username">Username</label> <input type="text" id="admin_username" name="username" required> </div>
                    <div class="form-group"> <label for="admin_password">Password</label> <input type="password" id="admin_password" name="password" required> </div>
                    <div class="form-group"> <label for="admin_confirmPassword">Confirm Password</label> <input type="password" id="admin_confirmPassword" name="confirmPassword" required> </div>
                    
                    <!-- Verification / Role Info -->
                    <div class="form-group"> <label for="admin_code">Admin Code / Auth Key</label> <input type="text" id="admin_code" name="adminCode" required> </div>
                    <div class="form-group"> <label for="admin_position">Position / Role Title</label> <input type="text" id="admin_position" name="position" required> </div>
                    <div class="form-group"> <label for="admin_contact">Contact Number</label> <input type="tel" id="admin_contact" name="contactNumber" required> </div>
                    <div class="form-group"> 
                        <label for="admin_securityQuestion">Security Question</label>
                        <select id="admin_securityQuestion" name="securityQuestion" required>
                            <option value="" disabled selected>Select a question</option>
                            <option value="pet">What was the name of your first pet?</option>
                            <option value="mother">What is your mother's maiden name?</option>
                            <option value="city">In what city were you born?</option>
                        </select>
                    </div>
                    
                    <!-- Optional Info -->
                    <div class="form-group"> 
                        <label for="admin_profilePic">Profile Picture (URL/Upload)</label> 
                        <input type="file" id="admin_profilePic" name="profilePicture" class="pt-2"> 
                    </div>
                    <div class="form-group"> 
                        <label for="admin_branch">Department / Branch Assigned</label> 
                        <input type="text" id="admin_branch" name="branchAssigned" value="BILIRAN" required> 
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="admin_regDate">Date of Registration (Auto)</label>
                        <input type="text" id="admin_regDate" name="registrationDate" readonly value="Loading...">
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <button type="submit" class="btn-submit">Register Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Driver Registration Modal (NEW) -->
    <div id="driverRegisterModal" class="custom-modal-overlay" style="display:none;">
        <div class="custom-modal max-w-2xl">
            <h3 class="modal-header text-2xl font-semibold text-primary">Driver Registration (Application)</h3>
            <button class="close-modal bg-red-500 hover:bg-red-600" onclick="document.getElementById('driverRegisterModal').style.display='none'">Close</button>
            
            <form id="driverRegistrationForm">
                <input type="hidden" name="role" value="driver">
                <div class="form-message-area text-sm mb-4"></div> 

                <h4 class="text-xl text-white font-medium mb-3 mt-4 full-width">Personal Info</h4>
                <div class="modal-grid">
                    <div class="form-group"> <label for="driver_lastName">Last Name</label> <input type="text" id="driver_lastName" name="lastName" required> </div>
                    <div class="form-group"> <label for="driver_firstName">First Name</label> <input type="text" id="driver_firstName" name="firstName" required> </div>
                    <div class="form-group full-width"> <label for="driver_address">Address</label> <input type="text" id="driver_address" name="address" required> </div>
                    <div class="form-group"> <label for="driver_email">Email Address</label> <input type="email" id="driver_email" name="email" required> </div>
                    <div class="form-group"> <label for="driver_contact">Contact Number</label> <input type="tel" id="driver_contact" name="contactNumber" required> </div>
                    <div class="form-group"> <label for="driver_password">Password</label> <input type="password" id="driver_password" name="password" required> </div>
                    <div class="form-group"> <label for="driver_confirmPassword">Confirm Password</label> <input type="password" id="driver_confirmPassword" name="confirmPassword" required> </div>
                    <div class="form-group"> <label for="driver_dob">Date of Birth</label> <input type="date" id="driver_dob" name="dateOfBirth" required> </div>
                    <div class="form-group"> 
                        <label for="driver_gender">Gender</label> 
                        <select id="driver_gender" name="gender" required>
                            <option value="" disabled selected>Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <h4 class="text-xl text-white font-medium mb-3 mt-4 full-width">Vehicle & License Info</h4>
                <div class="modal-grid">
                    <div class="form-group"> <label for="driver_vehicleType">Vehicle Type</label> <input type="text" id="driver_vehicleType" name="vehicleType" placeholder="Van, Tricycle, etc." required> </div>
                    <div class="form-group"> <label for="driver_plateNumber">Plate Number</label> <input type="text" id="driver_plateNumber" name="plateNumber" required> </div>
                    <div class="form-group"> <label for="driver_vehicleModel">Vehicle Model / Year</label> <input type="text" id="driver_vehicleModel" name="vehicleModel" required> </div>
                    <div class="form-group"> <label for="driver_licenseNumber">Driver’s License Number</label> <input type="text" id="driver_licenseNumber" name="licenseNumber" required> </div>
                    <div class="form-group"> <label for="driver_licenseExpiry">License Expiry Date</label> <input type="date" id="driver_licenseExpiry" name="licenseExpiryDate" required> </div>
                    <div class="form-group"> 
                        <label for="driver_orcr">OR/CR Upload (Proof)</label> 
                        <input type="file" id="driver_orcr" name="orcrUpload" class="pt-2"> 
                    </div>
                </div>

                <h4 class="text-xl text-white font-medium mb-3 mt-4 full-width">Availability & Experience</h4>
                <div class="modal-grid">
                    <div class="form-group"> <label for="driver_area">Area of Operation</label> <input type="text" id="driver_area" name="areaOfOperation" placeholder="e.g., Biliran Province" required> </div>
                    <div class="form-group"> <label for="driver_schedule">Working Schedule / Time</label> <input type="text" id="driver_schedule" name="workingSchedule" placeholder="e.g., 4AM - 5PM Daily" required> </div>
                    <div class="form-group"> <label for="driver_experience">Years of Driving Experience</label> <input type="number" id="driver_experience" name="yearsExperience" min="0" required> </div>
                </div>

                <h4 class="text-xl text-white font-medium mb-3 mt-4 full-width">Verification</h4>
                <div class="modal-grid">
                    <div class="form-group"> 
                        <label for="driver_validId">Valid ID Upload (License/Govt ID)</label> 
                        <input type="file" id="driver_validId" name="validIdUpload" class="pt-2" required> 
                    </div>
                    <div class="form-group"> 
                        <label for="driver_profilePic">Profile Picture</label> 
                        <input type="file" id="driver_profilePic" name="profilePicture" class="pt-2" required> 
                    </div>
                </div>

                <div class="mt-6 text-center full-width">
                    <button type="submit" class="btn-submit">Submit Driver Application</button>
                </div>
            </form>
        </div>
    </div>

<script src="/Biyahe/scripts/Admin-dashboard.js"></script>
<script src="/Biyahe/scripts/registration_handler.js"></script>

</body>
</html>