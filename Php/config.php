<?php
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is usually empty
$dbname = "biyahe_now"; // PALITAN ITO ng pangalan ng inyong database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // I-display ang error kung failed ang connection
    die("Database Connection failed: " . $conn->connect_error);
}

// Set charset to UTF8
$conn->set_charset("utf8");
?>