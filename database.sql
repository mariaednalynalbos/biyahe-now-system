-- Create database and tables for Biyahe Now
CREATE DATABASE IF NOT EXISTS biyahe_now;
USE biyahe_now;

-- Accounts table
CREATE TABLE accounts (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'driver', 'passenger') DEFAULT 'passenger',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Passengers table
CREATE TABLE passengers (
    passenger_id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT,
    firstname VARCHAR(100),
    lastname VARCHAR(100),
    contact_number VARCHAR(20),
    address TEXT,
    gender ENUM('Male', 'Female', 'Other'),
    date_of_birth DATE,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id)
);

-- Routes table
CREATE TABLE routes (
    route_id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(255) NOT NULL,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    distance_km DECIMAL(5,2),
    estimated_duration VARCHAR(50),
    fare DECIMAL(8,2),
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Bookings table
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    passenger_id INT,
    passenger_name VARCHAR(255),
    route_id INT,
    departure_time TIME,
    seat_number INT,
    contact_number VARCHAR(20),
    booking_date DATE,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (passenger_id) REFERENCES accounts(account_id),
    FOREIGN KEY (route_id) REFERENCES routes(route_id)
);

-- Insert sample routes
INSERT INTO routes (route_name, origin, destination, distance_km, estimated_duration, fare) VALUES
('Naval to Tacloban', 'Naval', 'Tacloban', 250.00, '2-2.5 hours', 200.00),
('Naval to Ormoc', 'Naval', 'Ormoc', 110.00, '2 hours', 200.00);