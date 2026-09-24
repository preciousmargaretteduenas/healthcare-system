CREATE DATABASE IF NOT EXISTS healthcare_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE healthcare_system;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'Staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    birth_date DATE NULL,
    sex VARCHAR(20) NULL,
    contact_number VARCHAR(20) NULL,
    address VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active'
);

CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_code VARCHAR(20) NOT NULL UNIQUE,
    patient_id INT NOT NULL,
    service_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_patient
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_appointment_service
        FOREIGN KEY (service_id) REFERENCES services(service_id)
        ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS queue (
    queue_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    queue_number INT NOT NULL,
    queue_date DATE NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Waiting',
    check_in_time DATETIME NULL,
    called_time DATETIME NULL,
    completed_time DATETIME NULL,
    CONSTRAINT fk_queue_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id)
        ON DELETE CASCADE,
    UNIQUE KEY uq_queue_number_date (queue_date, queue_number),
    UNIQUE KEY uq_queue_appointment (appointment_id)
);

CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL DEFAULT 'Published'
);

INSERT IGNORE INTO services (service_name, description, status) VALUES
('General Consultation', 'Basic health consultation and assessment.', 'Active'),
('Immunization', 'Vaccination and immunization services.', 'Active'),
('Maternal Care', 'Healthcare services for pregnant and postpartum mothers.', 'Active'),
('Child Health', 'Basic health monitoring and services for children.', 'Active'),
('Health Records', 'Patient health record assistance and management.', 'Active');

-- Test staff account:
-- Username: staff
-- Password: staff123
INSERT IGNORE INTO users (username, password, role)
VALUES (
    'staff',
    '$2y$12$4IyoFAA8SctX3V8W0QscDOXPfYmwAbz9CJdos0TETx0ec.SF1Re1e',
    'Staff'
);
