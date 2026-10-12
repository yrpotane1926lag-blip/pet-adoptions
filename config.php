<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "user_db";

$serverConn = new mysqli($host, $user, $password);

if ($serverConn->connect_error) {
    die("Connection failed: " . $serverConn->connect_error);
}

$serverConn->set_charset("utf8mb4");
$serverConn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$serverConn->close();

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function initializePetPointTables(mysqli $conn): void
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('USER', 'ADMIN') NOT NULL DEFAULT 'USER',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->query("
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
        )
    ");

    $conn->query("
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
            FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
        )
    ");

    $count = $conn->query("SELECT COUNT(*) AS total FROM pets");
    $total = $count ? (int) $count->fetch_assoc()['total'] : 0;

    if ($total === 0) {
        $stmt = $conn->prepare("
            INSERT INTO pets
                (name, type, breed, age, size, image_url, status, health, backstory, pet_condition, vaccinated)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $samplePets = [
            [
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
                1,
            ],
            [
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
                1,
            ],
            [
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
                0,
            ],
        ];

        foreach ($samplePets as $pet) {
            $stmt->bind_param(
                "ssssssssssi",
                $pet[0],
                $pet[1],
                $pet[2],
                $pet[3],
                $pet[4],
                $pet[5],
                $pet[6],
                $pet[7],
                $pet[8],
                $pet[9],
                $pet[10]
            );
            $stmt->execute();
        }
    }
}

initializePetPointTables($conn);
?>
