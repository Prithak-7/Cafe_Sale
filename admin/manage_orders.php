<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'administrator' && $_SESSION['role'] !== 'manager')) {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id_to_update = $_POST['order_id'];
    $new_status = $_POST['status'];

    if (in_array($new_status, ['Pending', 'Paid', 'Cancelled'])) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
            $stmt->bindParam(':status', $new_status);
            $stmt->bindParam(':order_id', $order_id_to_update);
            $stmt->execute();
            $message = "Order #{$order_id_to_update} status updated to {$new_status}.";
        } catch (PDOException $e) {
            $message = "Error updating order status: " . $e->getMessage();
        }
    } else {
        $message = "Invalid order status.";
    }
}

try {
    $stmt = $pdo->query("SELECT o.order_id, o.order_date, o.total_amount, o.payment_method, o.status, u.username AS placed_by, t.table_number
                           FROM orders o
                           LEFT JOIN users u ON o.user_id = u.id
                           LEFT JOIN tables t ON o.table_id = t.table_id
                           ORDER BY o.order_date DESC"); // Order by date descending
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Orders</h1>
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

        <?php if ($message): ?>
            <p class="<?= strpos($message, 'Error') === 0 ? 'error' : 'success' ?>"><?= $message ?></p>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p>No orders found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Table Number</th> <th>Placed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_id'] ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($order['order_date'])) ?></td>
                            <td>₹<?= htmlspecialchars(number_format($order['total_amount'], 2)) ?></td>
                            <td><?= htmlspecialchars($order['payment_method']) ?></td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>"><select name="status">
                                        <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Paid" <?= $order['status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                                        <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" style="padding: 5px 10px; font-size: 0.8em;">Update</button>
                                </form>
                            </td>
                            <td><?= htmlspecialchars($order['table_number'] ?: '-') ?></td> <td><?= htmlspecialchars($order['placed_by']) ?></td>
                            <td><a href="view_order_details.php?id=<?= $order['order_id'] ?>">View Details</a></td>
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