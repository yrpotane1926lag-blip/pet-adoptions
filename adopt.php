<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$contactEmail = "yrpotane1926lag@student.fatima.edu.ph";
$contactNumber = "09123456789";

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $petId = (int) $_POST['pet_id'];
    $userName = trim($_POST['user_name']);
    $userEmail = trim($_POST['user_email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $reason = trim($_POST['reason']);
    $homeType = trim($_POST['home_type']);
    $hasPets = trim($_POST['has_pets']);

    $stmt = $conn->prepare("
        INSERT INTO adoption_applications
            (pet_id, user_email, user_name, phone, address, reason, home_type, has_pets)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssssss", $petId, $userEmail, $userName, $phone, $address, $reason, $homeType, $hasPets);
    $stmt->execute();

    $conn->query("UPDATE pets SET status = 'Pending' WHERE id = {$petId} AND status = 'Available'");

    $petStmt = $conn->prepare("SELECT name, breed, type FROM pets WHERE id = ?");
    $petStmt->bind_param("i", $petId);
    $petStmt->execute();
    $pet = $petStmt->get_result()->fetch_assoc();

    $subject = "New PetPoint Adoption Application";
    $message = "New adoption application\n\n"
        . "Pet: {$pet['name']} ({$pet['type']} - {$pet['breed']})\n"
        . "Applicant: {$userName}\n"
        . "Email: {$userEmail}\n"
        . "Phone: {$phone}\n"
        . "Address: {$address}\n"
        . "Home Type: {$homeType}\n"
        . "Has Other Pets: {$hasPets}\n\n"
        . "Reason:\n{$reason}\n";
    $headers = "From: PetPoint <no-reply@petpoint.local>\r\nReply-To: {$userEmail}\r\n";
    $mailSent = @mail($contactEmail, $subject, $message, $headers);

    $_SESSION['flash_message'] = $mailSent
        ? "Application submitted and emailed successfully."
        : "Application submitted. Email sending needs mail setup in PHP/XAMPP.";

    header("Location: adopt.php#profile");
    exit();
}

$type = $_GET['type'] ?? '';
$breed = trim($_GET['breed'] ?? '');
$size = $_GET['size'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];
$paramTypes = '';

if ($type !== '') {
    $where[] = "type = ?";
    $params[] = $type;
    $paramTypes .= 's';
}

if ($breed !== '') {
    $where[] = "breed LIKE ?";
    $params[] = "%{$breed}%";
    $paramTypes .= 's';
}

if ($size !== '') {
    $where[] = "size = ?";
    $params[] = $size;
    $paramTypes .= 's';
}

if ($search !== '') {
    $where[] = "(name LIKE ? OR breed LIKE ? OR type LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $paramTypes .= 'sss';
}

$sql = "SELECT * FROM pets";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY FIELD(status, 'Available', 'Pending', 'Adopted'), name";

$petStmt = $conn->prepare($sql);
if ($params) {
    $petStmt->bind_param($paramTypes, ...$params);
}
$petStmt->execute();
$pets = $petStmt->get_result();

$profileStmt = $conn->prepare("
    SELECT aa.*, p.name AS pet_name, p.type, p.breed, p.image_url
    FROM adoption_applications aa
    JOIN pets p ON p.id = aa.pet_id
    WHERE aa.user_email = ?
    ORDER BY aa.created_at DESC
");
$profileStmt->bind_param("s", $_SESSION['email']);
$profileStmt->execute();
$applications = $profileStmt->get_result();

$flash = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetPoint | Adopt</title>
    <link rel="stylesheet" href="adopt.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.2.0/css/line.css">
    <script src="adopt.js" defer></script>
</head>
<body>
    <header>
        <nav class="navbar section-content">
            <a href="index.php" class="nav-logo">
                <h2 class="logo-text">PetPoint</h2>
            </a>
            <ul class="nav-menu">
                <button id="menu-close-button" class="fas fa-times"></button>
                <li class="nav-item"><a href="index.php#home" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="index.php#about" class="nav-link">About</a></li>
                <li class="nav-item"><a href="#pets" class="nav-link">Pets</a></li>
                <li class="nav-item"><a href="#profile" class="nav-link">My Profile</a></li>
                <li class="nav-item"><a href="#contact" class="nav-link">Contact Us</a></li>
            </ul>
            <div class="nav-auth">
                <span class="user-name"><?= e($_SESSION['name'] ?? 'User') ?></span>
                <a href="logout.php" class="login-btn">Logout</a>
                <?php if (($_SESSION['role'] ?? '') === 'ADMIN'): ?>
                    <a href="admin.php" class="admin-shortcut" title="Admin Dashboard"><i class="uil uil-setting"></i></a>
                <?php endif; ?>
            </div>
            <button id="menu-open-button" class="fas fa-bars"></button>
        </nav>
    </header>

    <main class="adoption-section">
        <section class="page-title" id="pets">
            <h2>Find Your New Best Friend</h2>
            <p>Every pet deserves a loving home. Start your journey here.</p>
            <?php if ($flash): ?>
                <div class="flash-message"><?= e($flash) ?></div>
            <?php endif; ?>
        </section>

        <form class="filter-bar" method="get" action="adopt.php#pets">
            <input type="text" name="search" placeholder="Search name, dog, cat..." value="<?= e($search) ?>">
            <select name="type">
                <option value="">All types</option>
                <option value="Dog" <?= $type === 'Dog' ? 'selected' : '' ?>>Dogs</option>
                <option value="Cat" <?= $type === 'Cat' ? 'selected' : '' ?>>Cats</option>
            </select>
            <input type="text" name="breed" placeholder="Breed" value="<?= e($breed) ?>">
            <select name="size">
                <option value="">All sizes</option>
                <option value="Small" <?= $size === 'Small' ? 'selected' : '' ?>>Small</option>
                <option value="Medium" <?= $size === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Large" <?= $size === 'Large' ? 'selected' : '' ?>>Large</option>
            </select>
            <button type="submit">Filter</button>
            <a href="adopt.php#pets">Clear</a>
        </form>

        <div class="pet-container">
            <?php if ($pets->num_rows === 0): ?>
                <p class="empty-state">No pets matched your filters.</p>
            <?php endif; ?>

            <?php while ($pet = $pets->fetch_assoc()): ?>
                <article class="pet-card">
                    <div class="pet-image">
                        <img src="<?= e($pet['image_url']) ?>" alt="<?= e($pet['name']) ?>">
                    </div>
                    <div class="pet-details">
                        <h3 class="pet-name"><?= e($pet['name']) ?></h3>
                        <p class="pet-breed"><?= e($pet['breed']) ?> &bull; <?= e($pet['age']) ?> &bull; <?= e($pet['size']) ?></p>
                        <span class="status-tag <?= strtolower(e($pet['status'])) ?>"><?= e($pet['status']) ?></span>
                        <div class="pet-actions">
                            <button
                                class="btn-adopt"
                                type="button"
                                data-open-adopt
                                data-pet-id="<?= (int) $pet['id'] ?>"
                                data-pet-name="<?= e($pet['name']) ?>"
                                <?= $pet['status'] !== 'Available' ? 'disabled' : '' ?>
                            >
                                <?= $pet['status'] === 'Available' ? 'Adopt Me!' : 'Under Process' ?>
                            </button>
                            <button
                                class="btn-details"
                                type="button"
                                data-open-details
                                data-name="<?= e($pet['name']) ?>"
                                data-health="<?= e($pet['health']) ?>"
                                data-backstory="<?= e($pet['backstory']) ?>"
                                data-condition="<?= e($pet['pet_condition']) ?>"
                                data-vaccinated="<?= $pet['vaccinated'] ? 'Yes' : 'No' ?>"
                            >
                                Details
                            </button>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <section class="profile-section" id="profile">
            <h2>My Adoption Status</h2>
            <div class="status-list">
                <?php if ($applications->num_rows === 0): ?>
                    <p class="empty-state">No adoption applications yet.</p>
                <?php endif; ?>

                <?php while ($application = $applications->fetch_assoc()): ?>
                    <div class="status-card">
                        <img src="<?= e($application['image_url']) ?>" alt="<?= e($application['pet_name']) ?>">
                        <div>
                            <h3><?= e($application['pet_name']) ?></h3>
                            <p><?= e($application['type']) ?> &bull; <?= e($application['breed']) ?></p>
                            <span class="status-tag <?= strtolower(e($application['status'])) ?>"><?= e($application['status']) ?></span>
                            <?php if (!empty($application['admin_notes'])): ?>
                                <p class="admin-notes">Admin note: <?= e($application['admin_notes']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section class="contact-section" id="contact">
            <h2>Contact Us</h2>
            <p>Email: <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a></p>
            <p>Number: <a href="tel:<?= e($contactNumber) ?>"><?= e($contactNumber) ?></a></p>
        </section>
    </main>

    <div class="modal" id="adoptModal" aria-hidden="true">
        <div class="modal-panel">
            <button class="modal-close" type="button" data-close-modal>&times;</button>
            <h2>Adoption Application</h2>
            <p class="modal-subtitle" id="adoptPetName"></p>
            <form method="post" action="adopt.php">
                <input type="hidden" name="pet_id" id="adoptPetId">
                <label>Name<input type="text" name="user_name" value="<?= e($_SESSION['name'] ?? '') ?>" required></label>
                <label>Email<input type="email" name="user_email" value="<?= e($_SESSION['email'] ?? '') ?>" required></label>
                <label>Phone Number<input type="text" name="phone" required></label>
                <label>Address<textarea name="address" required></textarea></label>
                <label>Home Type
                    <select name="home_type" required>
                        <option value="">Select one</option>
                        <option value="House">House</option>
                        <option value="Apartment">Apartment</option>
                        <option value="Condo">Condo</option>
                        <option value="Other">Other</option>
                    </select>
                </label>
                <label>Do you have other pets?
                    <select name="has_pets" required>
                        <option value="">Select one</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </label>
                <label>Why do you want to adopt this pet?<textarea name="reason" required></textarea></label>
                <button class="submit-btn" type="submit" name="submit_application">Submit Application</button>
            </form>
        </div>
    </div>

    <div class="modal" id="detailsModal" aria-hidden="true">
        <div class="modal-panel">
            <button class="modal-close" type="button" data-close-modal>&times;</button>
            <h2 id="detailsName">Pet Details</h2>
            <dl class="details-list">
                <dt>Health</dt>
                <dd id="detailsHealth"></dd>
                <dt>Back Story</dt>
                <dd id="detailsBackstory"></dd>
                <dt>Condition</dt>
                <dd id="detailsCondition"></dd>
                <dt>Vaccinated</dt>
                <dd id="detailsVaccinated"></dd>
            </dl>
        </div>
    </div>
</body>
</html>
