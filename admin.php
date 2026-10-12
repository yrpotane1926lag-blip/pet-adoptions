<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    header("Location: index.php");
    exit();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_pet'])) {
        $id = (int) ($_POST['pet_id'] ?? 0);
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $breed = trim($_POST['breed']);
        $age = trim($_POST['age']);
        $size = trim($_POST['size']);
        $imageUrl = trim($_POST['image_url']);
        $status = trim($_POST['status']);
        $health = trim($_POST['health']);
        $backstory = trim($_POST['backstory']);
        $condition = trim($_POST['pet_condition']);
        $vaccinated = isset($_POST['vaccinated']) ? 1 : 0;

        if ($id > 0) {
            $stmt = $conn->prepare("
                UPDATE pets
                SET name = ?, type = ?, breed = ?, age = ?, size = ?, image_url = ?, status = ?,
                    health = ?, backstory = ?, pet_condition = ?, vaccinated = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssssssii", $name, $type, $breed, $age, $size, $imageUrl, $status, $health, $backstory, $condition, $vaccinated, $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("
                INSERT INTO pets
                    (name, type, breed, age, size, image_url, status, health, backstory, pet_condition, vaccinated)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssssssssi", $name, $type, $breed, $age, $size, $imageUrl, $status, $health, $backstory, $condition, $vaccinated);
            $stmt->execute();
        }
    }

    if (isset($_POST['delete_pet'])) {
        $id = (int) $_POST['pet_id'];
        $stmt = $conn->prepare("DELETE FROM pets WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    if (isset($_POST['update_application'])) {
        $applicationId = (int) $_POST['application_id'];
        $status = $_POST['application_status'];
        $adminNotes = trim($_POST['admin_notes']);

        $stmt = $conn->prepare("UPDATE adoption_applications SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $adminNotes, $applicationId);
        $stmt->execute();

        $appStmt = $conn->prepare("
            SELECT aa.user_email, aa.user_name, p.id AS pet_id, p.name AS pet_name
            FROM adoption_applications aa
            JOIN pets p ON p.id = aa.pet_id
            WHERE aa.id = ?
        ");
        $appStmt->bind_param("i", $applicationId);
        $appStmt->execute();
        $application = $appStmt->get_result()->fetch_assoc();

        if ($application) {
            if ($status === 'Approved') {
                $petStatus = 'Adopted';
            } elseif ($status === 'Rejected') {
                $petStatus = 'Available';
            } else {
                $petStatus = 'Pending';
            }

            $petStmt = $conn->prepare("UPDATE pets SET status = ? WHERE id = ?");
            $petStmt->bind_param("si", $petStatus, $application['pet_id']);
            $petStmt->execute();

            $subject = "PetPoint Adoption Application {$status}";
            $message = "Hello {$application['user_name']},\n\n"
                . "Your adoption application for {$application['pet_name']} is now {$status}.\n\n"
                . ($adminNotes ? "Admin note: {$adminNotes}\n\n" : "")
                . "Thank you,\nPetPoint";
            @mail($application['user_email'], $subject, $message, "From: PetPoint <no-reply@petpoint.local>\r\n");
        }
    }

    header("Location: admin.php");
    exit();
}

$editPet = null;
if (isset($_GET['edit_pet'])) {
    $id = (int) $_GET['edit_pet'];
    $stmt = $conn->prepare("SELECT * FROM pets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editPet = $stmt->get_result()->fetch_assoc();
}

$pets = $conn->query("SELECT * FROM pets ORDER BY created_at DESC");
$applications = $conn->query("
    SELECT aa.*, p.name AS pet_name, p.type, p.breed
    FROM adoption_applications aa
    JOIN pets p ON p.id = aa.pet_id
    ORDER BY FIELD(aa.status, 'Pending', 'Approved', 'Rejected'), aa.created_at DESC
");

$totalPets = $conn->query("SELECT COUNT(*) AS total FROM pets")->fetch_assoc()['total'] ?? 0;
$pendingAdoptions = $conn->query("SELECT COUNT(*) AS total FROM adoption_applications WHERE status = 'Pending'")->fetch_assoc()['total'] ?? 0;
$approvedAdoptions = $conn->query("SELECT COUNT(*) AS total FROM adoption_applications WHERE status = 'Approved'")->fetch_assoc()['total'] ?? 0;
$availablePets = $conn->query("SELECT COUNT(*) AS total FROM pets WHERE status = 'Available'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetPoint | Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-wrapper">
    <nav class="sidebar">
        <div class="sidebar-logo">PetPoint Admin</div>
        <ul>
            <li><a href="#dashboard" class="active">Dashboard</a></li>
            <li><a href="#pets">Manage Pets</a></li>
            <li><a href="#requests">Adoption Requests</a></li>
            <li><a href="adopt.php">User Page</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php">Sign Out</a>
        </div>
    </nav>

    <main class="content" id="dashboard">
        <header class="content-header">
            <div>
                <h1>Welcome back, Admin!</h1>
                <p>Manage PetPoint listings and adoption applications.</p>
            </div>
            <a class="btn-add" href="#pet-form">+ Add New Pet</a>
        </header>

        <div class="stats-container">
            <div class="stat-card"><h3><?= (int) $totalPets ?></h3><p>Total Pets</p></div>
            <div class="stat-card"><h3><?= (int) $availablePets ?></h3><p>Available Pets</p></div>
            <div class="stat-card"><h3><?= (int) $pendingAdoptions ?></h3><p>Pending Adoptions</p></div>
            <div class="stat-card"><h3><?= (int) $approvedAdoptions ?></h3><p>Approved Adoptions</p></div>
        </div>

        <section class="form-container" id="pet-form">
            <h2><?= $editPet ? 'Edit Pet' : 'Add New Pet' ?></h2>
            <form method="post" class="pet-form">
                <input type="hidden" name="pet_id" value="<?= (int) ($editPet['id'] ?? 0) ?>">
                <label>Name<input type="text" name="name" value="<?= e($editPet['name'] ?? '') ?>" required></label>
                <label>Type
                    <select name="type" required>
                        <?php foreach (['Dog', 'Cat', 'Other'] as $option): ?>
                            <option value="<?= $option ?>" <?= ($editPet['type'] ?? '') === $option ? 'selected' : '' ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Breed<input type="text" name="breed" value="<?= e($editPet['breed'] ?? '') ?>" required></label>
                <label>Age<input type="text" name="age" value="<?= e($editPet['age'] ?? '') ?>" required></label>
                <label>Size
                    <select name="size" required>
                        <?php foreach (['Small', 'Medium', 'Large'] as $option): ?>
                            <option value="<?= $option ?>" <?= ($editPet['size'] ?? '') === $option ? 'selected' : '' ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Status
                    <select name="status" required>
                        <?php foreach (['Available', 'Pending', 'Adopted'] as $option): ?>
                            <option value="<?= $option ?>" <?= ($editPet['status'] ?? '') === $option ? 'selected' : '' ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="wide">Image URL<input type="url" name="image_url" value="<?= e($editPet['image_url'] ?? '') ?>" required></label>
                <label class="wide">Health<textarea name="health" required><?= e($editPet['health'] ?? '') ?></textarea></label>
                <label class="wide">Back Story<textarea name="backstory" required><?= e($editPet['backstory'] ?? '') ?></textarea></label>
                <label class="wide">Condition<textarea name="pet_condition" required><?= e($editPet['pet_condition'] ?? '') ?></textarea></label>
                <label class="checkbox-label"><input type="checkbox" name="vaccinated" <?= !empty($editPet['vaccinated']) ? 'checked' : '' ?>> Vaccinated</label>
                <div class="form-actions">
                    <button class="btn-add" type="submit" name="save_pet"><?= $editPet ? 'Update Pet' : 'Add Pet' ?></button>
                    <?php if ($editPet): ?><a class="btn-cancel" href="admin.php#pet-form">Cancel</a><?php endif; ?>
                </div>
            </form>
        </section>

        <section class="table-container" id="pets">
            <h2>Pet Listings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Breed</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Vaccinated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                        <tr>
                            <td><img src="<?= e($pet['image_url']) ?>" class="img-thumb" alt="<?= e($pet['name']) ?>"></td>
                            <td><strong><?= e($pet['name']) ?></strong></td>
                            <td><?= e($pet['type']) ?></td>
                            <td><?= e($pet['breed']) ?></td>
                            <td><?= e($pet['size']) ?></td>
                            <td><span class="badge <?= strtolower(e($pet['status'])) ?>"><?= e($pet['status']) ?></span></td>
                            <td><?= $pet['vaccinated'] ? 'Yes' : 'No' ?></td>
                            <td class="table-actions">
                                <a class="btn-edit" href="admin.php?edit_pet=<?= (int) $pet['id'] ?>#pet-form">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete this pet?');">
                                    <input type="hidden" name="pet_id" value="<?= (int) $pet['id'] ?>">
                                    <button class="btn-delete" type="submit" name="delete_pet">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section class="table-container" id="requests">
            <h2>Adoption Requests</h2>
            <table>
                <thead>
                    <tr>
                        <th>Pet</th>
                        <th>Applicant</th>
                        <th>Contact</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = $applications->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= e($request['pet_name']) ?></strong><br><?= e($request['type']) ?> - <?= e($request['breed']) ?></td>
                            <td><?= e($request['user_name']) ?><br><?= e($request['address']) ?></td>
                            <td><?= e($request['user_email']) ?><br><?= e($request['phone']) ?></td>
                            <td><?= e($request['reason']) ?></td>
                            <td><span class="badge <?= strtolower(e($request['status'])) ?>"><?= e($request['status']) ?></span></td>
                            <td>
                                <form method="post" class="decision-form">
                                    <input type="hidden" name="application_id" value="<?= (int) $request['id'] ?>">
                                    <textarea name="admin_notes" placeholder="Admin notes"><?= e($request['admin_notes'] ?? '') ?></textarea>
                                    <select name="application_status">
                                        <?php foreach (['Pending', 'Approved', 'Rejected'] as $option): ?>
                                            <option value="<?= $option ?>" <?= $request['status'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn-add" type="submit" name="update_application">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

</body>
</html>
