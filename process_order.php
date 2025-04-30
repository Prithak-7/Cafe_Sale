<?php
session_start();
require 'database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'You must be logged in to place an order.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_data']) && isset($_POST['total_amount'])) {
    $table_id = $_POST['table_id'] ?? null;  // Get table_id, allow null
    $user_id = $_SESSION['user_id'];
    $total_amount = $_POST['total_amount'];
    $order_data = json_decode($_POST['order_data'], true);

    if (empty($order_data)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Your order is empty.']);
        exit();
    }

    try {
        // Start a transaction to ensure data integrity
        $pdo->beginTransaction();

        // Insert the order into the 'orders' table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_date, total_amount, payment_method, status, table_id) VALUES (:user_id, NOW(), :total_amount, 'Cash', 'Pending', :table_id)"); // Defaulting to 'Cash' and 'Pending' for now
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':table_id', $table_id);
        $stmt->execute();
        $order_id = $pdo->lastInsertId();

        // Insert the individual items into the 'order_items' table
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (:order_id, :item_id, :quantity, :price)");
        foreach ($order_data as $item) {
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':item_id', $item['id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
        }

        // Commit the transaction
        $pdo->commit();

        echo json_encode(['success' => 'Order placed successfully!', 'order_id' => $order_id]);

    } catch (PDOException $e) {
        // If there's an error, rollback the transaction
        $pdo->rollBack();
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Error placing order: ' . $e->getMessage()]);
    }

} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid request.']);
}
?>