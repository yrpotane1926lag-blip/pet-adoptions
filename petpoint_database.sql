CREATE DATABASE IF NOT EXISTS user_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE user_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('USER', 'ADMIN') NOT NULL DEFAULT 'USER',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    age VARCHAR(50) NOT NULL,
    size VARCHAR(20) NOT NULL,
    image_url TEXT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Available',
    health TEXT NOT NULL,
    backstory TEXT NOT NULL,
    pet_condition TEXT NOT NULL,
    vaccinated TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS adoption_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    user_email VARCHAR(150) NOT NULL,
    user_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    reason TEXT NOT NULL,
    home_type VARCHAR(80) NOT NULL,
    has_pets VARCHAR(20) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_adoption_pet
        FOREIGN KEY (pet_id) REFERENCES pets(id)
        ON DELETE CASCADE
);

INSERT INTO pets
    (name, type, breed, age, size, image_url, status, health, backstory, pet_condition, vaccinated)
SELECT
    'Bantay',
    'Dog',
    'Beagle',
    '2 Years Old',
    'Medium',
    'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=500',
    'Available',
    'Healthy, active, and eating well.',
    'Bantay was rescued near a market and quickly became friendly with volunteers.',
    'Good with people, playful, and needs daily walks.',
    1
WHERE NOT EXISTS (SELECT 1 FROM pets WHERE name = 'Bantay' AND breed = 'Beagle');

INSERT INTO pets
    (name, type, breed, age, size, image_url, status, health, backstory, pet_condition, vaccinated)
SELECT
    'Muning',
    'Cat',
    'Persian Cat',
    '1 Year Old',
    'Small',
    'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=500',
    'Available',
    'Healthy coat, normal appetite, no known illness.',
    'Muning was surrendered by a family that could no longer care for her.',
    'Calm indoor cat, prefers quiet spaces.',
    1
WHERE NOT EXISTS (SELECT 1 FROM pets WHERE name = 'Muning' AND breed = 'Persian Cat');

INSERT INTO pets
    (name, type, breed, age, size, image_url, status, health, backstory, pet_condition, vaccinated)
SELECT
    'Browny',
    'Dog',
    'Golden Retriever',
    '3 Years Old',
    'Large',
    'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=500',
    'Pending',
    'Recovering well after basic treatment.',
    'Browny was found wandering and has adjusted well to shelter care.',
    'Gentle, trained with basic commands, best for a home with space.',
    0
WHERE NOT EXISTS (SELECT 1 FROM pets WHERE name = 'Browny' AND breed = 'Golden Retriever');
