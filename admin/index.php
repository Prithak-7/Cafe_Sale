<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'administrator' && $_SESSION['role'] !== 'manager')) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
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

        <div class="dashboard">
            <div class="dashboard-item">
                <h2>Manage Items</h2>
                <p>Create, edit, and delete menu items.</p>
                <a href="manage_items.php" class="button">Go to Manage Items</a>
            </div>

            <div class="dashboard-item">
                <h2>Manage Orders</h2>
                <p>View and update order statuses.</p>
                <a href="manage_orders.php" class="button">Go to Manage Orders</a>
            </div>

            <?php if ($_SESSION['role'] == 'administrator'): ?>
                <div class="dashboard-item">
                    <h2>Manage Users</h2>
                    <p>Create, edit, and delete user accounts.</p>
                    <a href="manage_users.php" class="button">Go to Manage Users</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>