-- Create database tables for Supabase PostgreSQL

-- Create ENUM types first
CREATE TYPE user_type_enum AS ENUM ('passenger', 'driver', 'admin');
CREATE TYPE trip_status_enum AS ENUM ('scheduled', 'active', 'completed', 'cancelled');
CREATE TYPE booking_status_enum AS ENUM ('pending', 'confirmed', 'cancelled');

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type user_type_enum DEFAULT 'passenger',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Drivers table
CREATE TABLE IF NOT EXISTS drivers (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    license_number VARCHAR(100),
    vehicle_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Routes table
CREATE TABLE IF NOT EXISTS routes (
    id SERIAL PRIMARY KEY,
    origin VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    distance DECIMAL(10,2),
    duration INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trips table
CREATE TABLE IF NOT EXISTS trips (
    id SERIAL PRIMARY KEY,
    driver_id INTEGER REFERENCES drivers(id),
    route_id INTEGER REFERENCES routes(id),
    departure_time TIME,
    available_seats INTEGER DEFAULT 14,
    price DECIMAL(10,2),
    status trip_status_enum DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    trip_id INTEGER REFERENCES trips(id),
    seats_booked INTEGER DEFAULT 1,
    total_price DECIMAL(10,2),
    status booking_status_enum DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);