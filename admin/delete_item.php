<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'administrator' && $_SESSION['role'] !== 'manager')) {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $item_id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = :item_id");
        $stmt->bindParam(':item_id', $item_id);
        $stmt->execute();

        // Redirect back to the manage items page with a success message
        header("Location: manage_items.php?success=Item deleted successfully!");
        exit();

    } catch (PDOException $e) {
        // Redirect back with an error message
        header("Location: manage_items.php?error=Error deleting item: " . urlencode($e->getMessage()));
        exit();
    }

} else {
    // Redirect back with an error message for invalid ID
    header("Location: manage_items.php?error=Invalid item ID.");
    exit();
}
?>
