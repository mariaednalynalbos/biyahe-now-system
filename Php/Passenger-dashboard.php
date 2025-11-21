<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../index.html");
    exit;
}

// Get passenger name from database if logged in as passenger
$displayName = 'Passenger';
if (isset($_SESSION['account_id']) && $_SESSION['role'] === 'passenger') {
    try {
        require_once 'db.php';
        $sql = "SELECT firstname, lastname FROM passengers WHERE passenger_id = ? OR account_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['account_id'], $_SESSION['account_id']]);
        $passenger = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($passenger) {
            $displayName = $passenger['firstname'] ?? $_SESSION['first_name'] ?? 'Passenger';
        }
    } catch (Exception $e) {
        $displayName = $_SESSION['first_name'] ?? 'Passenger';
    }
} else {
    $displayName = $_SESSION['first_name'] ?? 'Passenger';
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
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Passenger Dashboard - Biyahe Now</title>

    <link rel="stylesheet" href="../styles/Passenger-dashboard.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    </head>
<body>
    
    <nav class="top-nav-main">
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

                    <div class="utility-card trip-history-card" id="historySummaryCard">
                        <h3>Trip History</h3>
                        <div class="history-list" id="recentHistoryList">
                            <div class="history-item">
                                <i class="fas fa-check-circle success"></i>
                                <div class="details">
                                    <span class="route-name">Tacloban to Naval</span>
                                    <span class="driver-info">Driver: Pedro Reyes | Van: Van 2</span>
                                    <span class="date-time">Oct 20, 2024 @ 10:00 AM</span>
                                </div>
                            </div>
                            <div class="history-item">
                                <i class="fas fa-check-circle success"></i>
                                <div class="details">
                                    <span class="route-name">Ormoc to Cebu</span>
                                    <span class="driver-info">Driver: Maria Dela Cruz | Van: Van 5</span>
                                    <span class="date-time">Sep 5, 2024 @ 07:30 AM</span>
                                </div>
                            </div>
                        </div>
                        <a href="#" class="view-all-link" data-section="history">View All History <i class="fas fa-chevron-right"></i></a>
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
            <h2>My Profile (Update Account)</h2>
            <div class="profile-card utility-card">
                <form id="profileForm">
                    <div class="form-grid profile-form-grid">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="profileFirstName" value="Passenger" required>
                        </div>
                         <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="profileLastName" value="One" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="profileEmail" value="passenger@test.com" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" id="profilePhone" value="0917xxxxxxx">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea id="profileAddress">Tacloban City, Leyte</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full-red update-btn">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                    <button type="button" class="btn btn-secondary update-btn">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    </form>
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
</body>
</html>