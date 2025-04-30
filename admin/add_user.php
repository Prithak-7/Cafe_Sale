<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $email = trim($_POST['email']);

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }
    if (empty($role)) {
        $errors['role'] = 'Role is required.';
    }

    if (empty($errors)) {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                $errors['username'] = 'Username already exists.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (:username, :password, :role, :email)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password); // In a real app, use password_hash()
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $success = 'User added successfully!';
            }
        } catch (PDOException $e) {
            $errors['db'] = 'Error adding user: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Add New User</h1>
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
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <?php if (isset($errors['username'])): ?>
                    <p class="error"><?= $errors['username'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($errors['password'])): ?>
                    <p class="error"><?= $errors['password'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="administrator">Administrator</option>
                    <option value="manager">Manager</option>
                    <option value="staff">Staff</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <p class="error"><?= $errors['role'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email">
            </div>

            <button type="submit">Add User</button>
            <a href="manage_users.php" class="button">Cancel</a>
        </form>
    </div>
</body>
</html>