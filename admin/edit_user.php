<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

$errors = [];
$success = false;

// Fetch the user details if an ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT id, username, role, email FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die("User not found.");
        }
    } catch (PDOException $e) {
        die("Error fetching user: " . $e->getMessage());
    }
} else {
    die("Invalid user ID.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id_to_update = $_POST['user_id'];
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Only update if provided

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }
    if (empty($role)) {
        $errors['role'] = 'Role is required.';
    }

    if (empty($errors)) {
        try {
            // Check if the new username is already taken by another user
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :user_id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':user_id', $user_id_to_update);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                $errors['username'] = 'Username already exists.';
            } else {
                $sql = "UPDATE users SET username = :username, role = :role, email = :email";
                if (!empty($password)) {
                    $sql .= ", password = :password"; // In real app, use password_hash()
                }
                $sql .= " WHERE id = :user_id";

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $user_id_to_update);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':email', $email);
                if (!empty($password)) {
                    $stmt->bindParam(':password', $password);
                }
                $stmt->execute();
                $success = 'User updated successfully!';

                // Refetch the user details after successful update
                $stmt = $pdo->prepare("SELECT id, username, role, email FROM users WHERE id = :user_id");
                $stmt->bindParam(':user_id', $user_id_to_update);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $errors['db'] = 'Error updating user: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit User</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Menu</a></li>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_items.php">Manage Items</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>

        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-container">
                <?php foreach ($errors as $error): ?>
                    <p class="error"><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                <?php if (isset($errors['username'])): ?>
                    <p class="error"><?= $errors['username'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">New Password (leave blank to keep current):</label>
                <input type="password" id="password" name="password">
                <small>If you leave this field blank, the password will not be changed.</small>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="administrator" <?= $user['role'] == 'administrator' ? 'selected' : '' ?>>Administrator</option>
                    <option value="manager" <?= $user['role'] == 'manager' ? 'selected' : '' ?>>Manager</option>
                    <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <p class="error"><?= $errors['role'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?: '') ?>">
            </div>

            <button type="submit">Update User</button>
            <a href="manage_users.php" class="button">Cancel</a>
        </form>
    </div>
</body>
</html>