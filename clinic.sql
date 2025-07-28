CREATE DATABASE IF NOT EXISTS clinic;
USE clinic;

-- Users table: patients and doctors
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor') NOT NULL
);

-- Sample entries
INSERT INTO users (username, password, role)
VALUES 
('Ravi Kumar', '12345678', 'patient'),
('Dr. Suresh Patil', '19191919', 'doctor'),
('Dr. Meena Kulkarni', '17171717', 'doctor');
