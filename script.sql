-- Create database
CREATE DATABASE IF NOT EXISTS sl_transport;
USE sl_transport;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role ENUM('admin', 'driver', 'passenger') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Vehicle types table
CREATE TABLE vehicle_types (
    type_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    average_capacity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles table
CREATE TABLE vehicles (
    vehicle_id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(20) UNIQUE NOT NULL,
    type_id INT NOT NULL,
    registration_number VARCHAR(20) UNIQUE NOT NULL,
    manufacturer VARCHAR(50),
    model VARCHAR(50),
    year_of_manufacture YEAR,
    seating_capacity INT NOT NULL,
    current_status ENUM('active', 'maintenance', 'retired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES vehicle_types(type_id)
);

-- Routes table
CREATE TABLE routes (
    route_id INT PRIMARY KEY AUTO_INCREMENT,
    route_number VARCHAR(20) UNIQUE NOT NULL,
    route_name VARCHAR(100) NOT NULL,
    start_location VARCHAR(100) NOT NULL,
    end_location VARCHAR(100) NOT NULL,
    distance_km DECIMAL(10,2),
    estimated_duration_minutes INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Stops table
CREATE TABLE stops (
    stop_id INT PRIMARY KEY AUTO_INCREMENT,
    stop_name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Route stops mapping table
CREATE TABLE route_stops (
    route_id INT NOT NULL,
    stop_id INT NOT NULL,
    stop_order INT NOT NULL,
    estimated_time_minutes INT,
    PRIMARY KEY (route_id, stop_id),
    FOREIGN KEY (route_id) REFERENCES routes(route_id),
    FOREIGN KEY (stop_id) REFERENCES stops(stop_id)
);

-- Vehicle assignments table
CREATE TABLE vehicle_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    route_id INT NOT NULL,
    driver_id INT NOT NULL,
    assignment_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id),
    FOREIGN KEY (route_id) REFERENCES routes(route_id),
    FOREIGN KEY (driver_id) REFERENCES users(user_id)
);

-- Live tracking table
CREATE TABLE live_tracking (
    tracking_id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    assignment_id INT NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    speed_kmh DECIMAL(5,2),
    heading DECIMAL(5,2),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    next_stop_id INT,
    status ENUM('on-time', 'delayed', 'arriving') DEFAULT 'on-time',
    delay_minutes INT DEFAULT 0,
    current_capacity_percentage INT,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id),
    FOREIGN KEY (assignment_id) REFERENCES vehicle_assignments(assignment_id),
    FOREIGN KEY (next_stop_id) REFERENCES stops(stop_id)
);

-- Schedule table
CREATE TABLE schedules (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT NOT NULL,
    stop_id INT NOT NULL,
    arrival_time TIME NOT NULL,
    departure_time TIME NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (route_id) REFERENCES routes(route_id),
    FOREIGN KEY (stop_id) REFERENCES stops(stop_id)
);

-- Incidents table
CREATE TABLE incidents (
    incident_id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT NOT NULL,
    assignment_id INT,
    incident_type ENUM('breakdown', 'accident', 'delay', 'other') NOT NULL,
    description TEXT,
    reported_by INT NOT NULL,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP,
    status ENUM('reported', 'in_progress', 'resolved') DEFAULT 'reported',
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id),
    FOREIGN KEY (assignment_id) REFERENCES vehicle_assignments(assignment_id),
    FOREIGN KEY (reported_by) REFERENCES users(user_id)
);

-- Create indexes for better query performance
CREATE INDEX idx_live_tracking_timestamp ON live_tracking(timestamp);
CREATE INDEX idx_vehicle_assignments_date ON vehicle_assignments(assignment_date);
CREATE INDEX idx_schedules_route_day ON schedules(route_id, day_of_week);
CREATE INDEX idx_route_stops_order ON route_stops(route_id, stop_order);
CREATE INDEX idx_incidents_vehicle ON incidents(vehicle_id, status);

-- Insert sample vehicle types
INSERT INTO vehicle_types (name, description, average_capacity) VALUES
('Bus', 'Standard city bus', 40),
('Train', 'Passenger train', 120);

-- Insert sample routes
INSERT INTO routes (route_number, route_name, start_location, end_location, distance_km, estimated_duration_minutes) VALUES
('138', 'Pettah - Kadawatha', 'Pettah', 'Kadawatha', 15.5, 45),
('100', 'Colombo - Galle', 'Colombo', 'Galle', 115.0, 180),
('177', 'Kaduwela - Kollupitiya', 'Kaduwela', 'Kollupitiya', 12.8, 40);