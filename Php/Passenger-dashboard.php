<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../index.html");
    exit;
}

// Get passenger data from database
$displayName = 'User';
$passengerData = [];

// Try to get name from session first
if (isset($_SESSION['first_name']) && !empty($_SESSION['first_name'])) {
    $displayName = $_SESSION['first_name'];
}

if (isset($_SESSION['account_id']) && $_SESSION['role'] === 'passenger') {
    try {
        require_once 'db.php';
        
        // Get passenger data with user email
        $sql = "
            SELECT 
                p.firstname, p.lastname, p.contact_number, p.address, 
                p.gender, p.date_of_birth, u.email
            FROM passengers p
            LEFT JOIN users u ON p.account_id = u.account_id
            WHERE p.account_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['account_id']]);
        $passengerData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug: Check what data we got
        error_log('Account ID: ' . $_SESSION['account_id']);
        error_log('Passenger Data: ' . print_r($passengerData, true));
        
        if ($passengerData && !empty($passengerData['firstname'])) {
            $displayName = $passengerData['firstname'];
            $_SESSION['first_name'] = $passengerData['firstname'];
            $_SESSION['email'] = $passengerData['email'];
        } else {
            // Try users table for basic info
            $userSql = "SELECT username, email FROM users WHERE account_id = ?";
            $userStmt = $pdo->prepare($userSql);
            $userStmt->execute([$_SESSION['account_id']]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            if ($userData) {
                $displayName = $userData['username'] ?: 'User';
                $_SESSION['email'] = $userData['email'];
                $_SESSION['first_name'] = $displayName;
            }
        }
    } catch (Exception $e) {
        $displayName = 'Passenger';
    }
}


if (isset($_SESSION['role']) && $_SESSION['role'] !== 'passenger') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showModal('Access Denied', 'Tanging mga Passenger lang ang may pahintulot na makapasok dito.');
            setTimeout(() => { window.location.href='../index.html'; }, 3000);
        });
    </script>";
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Passenger Dashboard - Biyahe Now</title>

    <link rel="stylesheet" href="../styles/Passenger-dashboard.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    </head>
<body>
    
    <!-- Mobile Menu Toggle -->
    <button id="mobileMenuToggle" class="mobile-menu-toggle">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>
    
    <nav class="top-nav-main" id="topNav">
        <div class="logo-section">
            <a href="#dashboard" class="logo-link">
                <img src="../images/logo.png" alt="Biyahe Now Logo" class="dashboard-logo">
                <span class="logo-text">Biyahe Now</span>
            </a>
        </div>

        <ul class="nav-links">
            <li><a href="#" class="nav-link active" data-section="dashboard">Dashboard</a></li>
            <li><a href="#" class="nav-link" data-section="history">Trip History</a></li>
            <li><a href="#" class="nav-link" data-section="profile">Profile</a></li>
            <li><a href="#" class="nav-link" data-section="support">Support</a></li>
        </ul>
        <div class="nav-actions">
            <span class="user-welcome">Hello, <?php echo htmlspecialchars($displayName); ?>!</span>
            <a href="../php/logout.php" id="nav-logout" class="btn btn-secondary btn-small"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>
    
    <header class="topbar">
        <div class="search-wrap">
            <input id="search" type="search" placeholder="Search routes, drivers, or bookings..." autocomplete="off">
            <i class="fas fa-search search-icon"></i>
            <div id="search-suggestions" class="search-suggestions"></div>
        </div>
        <div class="contact-info">Need Help? Call us at 800-300-5000</div>
    </header>

    <main class="content">
            <section class="page-section active" id="dashboardSection">
                <div class="dashboard-grid-container"> 
                    
                    <div class="utility-card book-now-card" id="bookNowCard">
                        <h3>Book Now</h3>
                        
                        <form id="bookingForm" action="process_passenger_booking.php" method="POST">
                            <div class="form-grid" id="bookNowFormGrid">
                                <div class="form-group"> 
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" required placeholder="First Name">
                                </div>
                                <div class="form-group"> 
                                    <label for="surname">Surname</label>
                                    <input type="text" id="surname" name="surname" required placeholder="Surname">
                                </div>
                                <div class="form-group">
                                    <label for="route">Route</label>
                                    <select id="route" name="route" required>
                                        <option value="">Loading routes...</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="tripTime">Time</label>
                                    <select id="tripTime" name="tripTime" required>
                                        <option value="">Select Time</option>
                                    </select>
                                </div>
                                <div class="form-group full-width"> 
                                    <label for="contactNumber">Contact Number</label>
                                    <input type="tel" id="contactNumber" name="contactNumber" required placeholder="e.g., 09xxxxxxxxx">
                                </div>
                            </div>
                            
                            <div class="form-group seat-select-group">
                                <label>Seat Selection</label>
                                <input type="text" id="selectedSeatDisplay" placeholder="Click button to select seat" readonly required>
                                <input type="hidden" id="seatNumber" name="seatNumber">
                                <button type="button" class="btn btn-secondary btn-full" id="openSeatModalBtn">
                                    Select Seat <i class="fas fa-chair"></i>
                                </button>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-full-red">
                                <i class="fas fa-ticket-alt"></i> Submit Booking
                            </button>
                        </form>
                    </div>
                    
                    <div class="utility-card upcoming-trips-card" id="upcomingTripsCard">
                        <h3>Upcoming Trips</h3>
                        <div class="table-responsive">
                            <table class="upcoming-trips-table">
                                <thead>
                                    <tr>
                                        <th>Passenger</th>
                                        <th>Route</th>
                                        <th>Destination</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Seat</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="upcomingTripsTableBody">
                                    <tr>
                                        <td colspan="7" class="empty-state">No upcoming trips found. Book one now!</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="utility-card map-card" id="mapCard">
                        <h3>Live Trip Tracking</h3>
                        <div id="trackingMap" class="map-container"></div>
                        <div class="map-info">
                            <p id="currentTripInfo" class="muted">No active trip to track.</p>
                            <button class="btn btn-primary btn-small" id="trackActiveTripBtn" style="display:none;">
                                Center Tracking <i class="fas fa-crosshairs"></i>
                            </button>
                        </div>
                    </div>


                    
                </div>
            </section>

        <section class="page-section" id="historySection" style="display:none;">
            <h2>Trip History</h2> 
            <div class="utility-card full-history-card">
                <h3>List of Rides</h3>
                <div id="fullHistoryTable" class="table-responsive">
                    <p class="muted">Loading full trip history...</p>
                </div>
            </div>
        </section>

        <section class="page-section" id="profileSection" style="display:none;">
            <h2>My Profile</h2>
            <div class="profile-container">
                <!-- Profile Info Card -->
                <div class="profile-info-card utility-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="profile-basic-info">
                            <h3 id="profileDisplayName"><?php 
                                $fullName = trim(($passengerData['firstname'] ?? '') . ' ' . ($passengerData['lastname'] ?? ''));
                                if (empty($fullName)) {
                                    $fullName = $displayName;
                                }
                                echo htmlspecialchars($fullName);
                            ?></h3>
                            <p id="profileDisplayEmail"><?php echo htmlspecialchars($_SESSION['email'] ?? 'No email'); ?></p>
                            <span class="profile-status">Active Passenger</span>
                        </div>
                    </div>
                </div>

                <!-- Update Profile Card -->
                <div class="profile-form-card utility-card">
                    <h4><i class="fas fa-edit"></i> Update Profile Information</h4>
                    <form id="profileForm">
                        <div class="profile-form-vertical">
                            <div class="form-group">
                                <label for="profileFirstName">First Name</label>
                                <input type="text" id="profileFirstName" name="firstName" value="<?php echo htmlspecialchars($passengerData['firstname'] ?? ''); ?>" required disabled>
                            </div>
                            <div class="form-group">
                                <label for="profileLastName">Last Name</label>
                                <input type="text" id="profileLastName" name="lastName" value="<?php echo htmlspecialchars($passengerData['lastname'] ?? ''); ?>" required disabled>
                            </div>
                            <div class="form-group">
                                <label for="profileEmail">Email Address</label>
                                <input type="email" id="profileEmail" name="email" value="<?php echo htmlspecialchars($passengerData['email'] ?? ''); ?>" required disabled>
                            </div>
                            <div class="form-group">
                                <label for="profilePhone">Contact Number</label>
                                <input type="tel" id="profilePhone" name="contactNumber" value="<?php echo htmlspecialchars($passengerData['contact_number'] ?? ''); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="profileAddress">Address</label>
                                <textarea id="profileAddress" name="address" rows="3" disabled><?php echo htmlspecialchars($passengerData['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="profileGender">Gender</label>
                                <select id="profileGender" name="gender" disabled>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($passengerData['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($passengerData['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($passengerData['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="profileDOB">Date of Birth</label>
                                <input type="date" id="profileDOB" name="dateOfBirth" value="<?php echo htmlspecialchars($passengerData['date_of_birth'] ?? ''); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <button type="button" id="editProfileBtn" class="btn btn-secondary">
                                <i class="fas fa-edit"></i> Edit Profile
                            </button>
                            <button type="submit" id="saveProfileBtn" class="btn btn-primary" style="display:none;">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" id="cancelEditBtn" class="btn btn-secondary" style="display:none;">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Card -->
                <div class="password-form-card utility-card">
                    <h4><i class="fas fa-key"></i> Change Password</h4>
                    <form id="passwordForm">
                        <div class="profile-form-vertical">
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" name="currentPassword" required>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" name="newPassword" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="page-section" id="supportSection" style="display:none;">
            <h2>Help & Support</h2>
            <div class="support-grid">
                <div class="help-card utility-card">
                    <h3>Frequently Asked Questions (FAQ)</h3>
                    <ul class="support-list">
                        <li><a href="#"><i class="fas fa-question-circle"></i> How do I change my booking?</a></li>
                        <li><a href="#"><i class="fas fa-question-circle"></i> What are your cancellation policies?</a></li>
                        <li><a href="#"><i class="fas fa-question-circle"></i> Where can I find my e-ticket?</a></li>
                    </ul>
                </div>

                <div class="contact-card utility-card">
                    <h3>Contact Us</h3>
                    <div class="contact-options">
                        <div class="contact-option">
                            <i class="fas fa-phone"></i>
                            <span>Call Center</span>
                            <small>+63 (555) 123-4567</small>
                        </div>
                        <div class="contact-option">
                            <i class="fas fa-comment-dots"></i>
                            <span>Live Chat</span>
                            <small>Start a conversation now</small>
                        </div>
                    </div>
                </div>

                <div class="emergency-card utility-card">
                    <h3>Emergency</h3>
                    <button class="btn btn-emergency btn-full">
                        <i class="fas fa-phone-alt"></i> Emergency Hotline (24/7)
                    </button>
                </div>
            </div>
        </section>
    </main>
    
    <div id="seatModal" class="custom-modal-overlay" style="display:none;">
        <div class="custom-modal seat-modal-content">
            <span class="close-modal" id="closeSeatModal">&times;</span>
            <h3>Select Your Seat</h3>
            <div class="van-layout-container">
                <div class="driver-side">
                    <div class="seat driver-seat">Driver</div>
                    <div class="seat available" data-number="1">1</div>
                    <div class="seat available" data-number="2">2</div>
                    <div class="seat available" data-number="3">3</div>
                    <div class="seat available" data-number="4">4</div>
                </div>
                
                
                <div class="passenger-side">
                    <div class="seat available" data-number="5">5</div>
                    <div class="seat available" data-number="6">6</div>
                    <div class="seat available" data-number="7">7</div>
                    <div class="seat available" data-number="8">8</div>
                    <div class="seat available" data-number="9">9</div>
                </div>
            </div>
            <div class="van-layout-container bottom-row">
                <div class="seat available" data-number="10">10</div>
                <div class="seat available" data-number="11">11</div>
                <div class="seat available" data-number="12">12</div>
                <div class="seat available" data-number="13">13</div>
                <div class="seat available" data-number="14">14</div>
            </div>

            <div class="legend">
                <span class="legend-item"><div class="seat available"></div> Available</span>
                <span class="legend-item"><div class="seat unavailable"></div> Booked</span>
                <span class="legend-item"><div class="seat selected"></div> Selected</span>
            </div>

            <button class="btn btn-primary btn-full-red" id="confirmSeatSelection">Confirm Seat</button>
        </div>
    </div>
    
    <div id="globalModal" class="custom-modal-overlay" style="display:none;">
        <div class="custom-modal modal-small">
            <span class="close-modal" id="closeGlobalModal">&times;</span>
            <h3 id="modalTitle"></h3>
            <p id="modalMessage"></p>
            <button class="btn btn-primary" id="modalConfirmBtn" style="display:none;">Confirm</button>
            <button class="btn btn-secondary" id="modalCloseBtn">Close</button>
        </div>
    </div>
    
    <div id="toast" class="toast"></div>

    <script src="../scripts/Passenger-dashboard.js"></script>
    <script src="../scripts/passenger-mobile.js"></script>
</body>
</html>