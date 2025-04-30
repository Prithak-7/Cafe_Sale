<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id_to_delete = $_GET['id'];

    // Prevent deleting the currently logged-in administrator
    if ($user_id_to_delete == $_SESSION['user_id']) {
        header("Location: manage_users.php?error=You cannot delete your own account.");
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id_to_delete);
        $stmt->execute();

        // Redirect back to the manage users page with a success message
        header("Location: manage_users.php?success=User deleted successfully!");
        exit();

    } catch (PDOException $e) {
        // Redirect back with an error message
        header("Location: manage_users.php?error=Error deleting user: " . urlencode($e->getMessage()));
        exit();
    }

} else {
    // Redirect back with an error message for invalid ID
    header("Location: manage_users.php?error=Invalid user ID.");
    exit();
}
?>