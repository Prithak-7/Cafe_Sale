<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'administrator' && $_SESSION['role'] !== 'manager')) {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

try {
    $stmt = $pdo->query("SELECT * FROM items");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching items: " . $e->getMessage());
}

// Check for success/error messages from delete operation
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Items</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Menu</a></li>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_items.php">Manage Items</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <?php if ($_SESSION['role'] == 'administrator'): ?>
                    <li><a href="manage_users.php">Manage Users</a></li>
                <?php endif; ?>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>

        <p><a href="add_item.php" class="button">Add New Item</a></p>

        <?php if (isset($success_message)): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <p>No items available in the menu.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['item_id'] ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td>₹<?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= $item['is_available'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <a href="edit_item.php?id=<?= $item['item_id'] ?>">Edit</a>
                                <a href="delete_item.php?id=<?= $item['item_id'] ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    td a {
        margin-right: 10px;
        text-decoration: none;
    }
</style>