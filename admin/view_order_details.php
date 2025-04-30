<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'administrator' && $_SESSION['role'] !== 'manager')) {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = $_GET['id'];

    try {
        // Fetch order details
        $stmt = $pdo->prepare("SELECT o.order_id, o.order_date, o.total_amount, o.payment_method, o.status, c.name AS customer_name, u.username AS placed_by
                               FROM orders o
                               LEFT JOIN customers c ON o.order_id = c.customer_id
                               LEFT JOIN users u ON o.user_id = u.id
                               WHERE o.order_id = :order_id");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            die("Order not found.");
        }

        // Fetch items in the order
        $stmt = $pdo->prepare("SELECT oi.quantity, oi.price, i.name AS item_name
                               FROM order_items oi
                               JOIN items i ON oi.item_id = i.item_id
                               WHERE oi.order_id = :order_id");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Error fetching order details: " . $e->getMessage());
    }
} else {
    die("Invalid order ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Order #<?= $order['order_id'] ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Order Details - Order #<?= $order['order_id'] ?></h1>
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

        <div>
            <h3>Order Information</h3>
            <p><strong>Order ID:</strong> <?= $order['order_id'] ?></p>
            <p><strong>Order Date:</strong> <?= date('Y-m-d H:i:s', strtotime($order['order_date'])) ?></p>
            <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name'] ?: 'Guest') ?></p>
            <p><strong>Placed By:</strong> <?= htmlspecialchars($order['placed_by']) ?></p>
            <p><strong>Total Amount:</strong> ₹<?= htmlspecialchars(number_format($order['total_amount'], 2)) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
        </div>

        <h3>Order Items</h3>
        <?php if (empty($order_items)): ?>
            <p>No items in this order.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price per Item</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₹<?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                            <td>₹<?= htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p><a href="manage_orders.php" class="button">Back to Orders</a></p>
    </div>
</body>
</html>

<style>
    table {
        width: 80%;
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

    h3 {
        margin-top: 20px;
        color: #555;
    }
</style>