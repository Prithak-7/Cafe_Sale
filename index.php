<?php
session_start();
require 'database.php';

try {
    $stmt = $pdo->query("SELECT * FROM items WHERE is_available = 1");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching items: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Menu</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Our Cafe!</h1>
        <nav>
            <ul>
                <li><a href="index.php">Menu</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="auth/logout.php">Logout</a></li>
                    <?php if ($_SESSION['role'] == 'administrator' || $_SESSION['role'] == 'manager'): ?>
                        <li><a href="admin/index.php">Admin Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] == 'staff'): ?>
                        <li><a href="staff/place_order.php">Place Order</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="auth/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <h2>Our Menu</h2>
        <?php if (empty($items)): ?>
            <p>Our menu is currently empty. Please check back later.</p>
        <?php else: ?>
            <div class="menu">
                <?php foreach ($items as $item): ?>
                    <div class="menu-item" data-item-id="<?= $item['item_id'] ?>" data-item-name="<?= htmlspecialchars($item['name']) ?>" data-item-price="<?= $item['price'] ?>">
                        <div class="item-details">
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <?php if (!empty($item['description'])): ?>
                                <div class="item-description"><?= htmlspecialchars($item['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="item-actions">
                            <div class="item-price">₹<?= htmlspecialchars(number_format($item['price'], 2)) ?></div>
                            <button class="add-to-order">Add to Order</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="order-summary">
            <h2>Your Order</h2>
            <ul id="order-items-list">
                <li id="empty-order">Your order is empty.</li>
            </ul>
            <div id="order-total">Total: ₹0.00</div>
            <button id="confirm-order-btn" style="display:none;">Confirm Order</button>
            <p id="login-prompt" style="display:none;">Please <a href="auth/login.php">login</a> to place your order.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addToOrderButtons = document.querySelectorAll('.add-to-order');
            const orderItemsList = document.getElementById('order-items-list');
            const orderTotalDisplay = document.getElementById('order-total');
            const confirmOrderBtn = document.getElementById('confirm-order-btn');
            const emptyOrderMessage = document.getElementById('empty-order');
            const loginPrompt = document.getElementById('login-prompt');
            let order = {};
            let total = 0;

            function updateOrderDisplay() {
                orderItemsList.innerHTML = '';
                total = 0;
                let itemCount = 0;

                for (const itemId in order) {
                    if (order.hasOwnProperty(itemId)) {
                        const item = order[itemId];
                        const listItem = document.createElement('li');
                        listItem.textContent = `${item.name} x ${item.quantity} - ₹${(item.price * item.quantity).toFixed(2)}`;

                        const removeButton = document.createElement('button');
                        removeButton.textContent = 'Remove';
                        removeButton.classList.add('remove-item');
                        removeButton.dataset.itemId = itemId;
                        listItem.appendChild(removeButton);

                        orderItemsList.appendChild(listItem);
                        total += item.price * item.quantity;
                        itemCount++;
                    }
                }

                orderTotalDisplay.textContent = `Total: ₹${total.toFixed(2)}`;

                if (itemCount > 0) {
                    emptyOrderMessage.style.display = 'none';
                    confirmOrderBtn.style.display = 'block';
                } else {
                    emptyOrderMessage.style.display = 'block';
                    confirmOrderBtn.style.display = 'none';
                }
            }

            addToOrderButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const menuItem = this.closest('.menu-item');
                    const itemId = menuItem.dataset.itemId;
                    const itemName = menuItem.dataset.itemName;
                    const itemPrice = parseFloat(menuItem.dataset.itemPrice);

                    if (order[itemId]) {
                        order[itemId].quantity++;
                    } else {
                        order[itemId] = { id: itemId, name: itemName, price: itemPrice, quantity: 1 };
                    }
                    updateOrderDisplay();
                });
            });

            orderItemsList.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-item')) {
                    const itemIdToRemove = event.target.dataset.itemId;
                    if (order[itemIdToRemove]) {
                        delete order[itemIdToRemove];
                        updateOrderDisplay();
                    }
                }
            });

            
            confirmOrderBtn.addEventListener('click', function() {
                <?php if (isset($_SESSION['user_id'])): ?>
                    const orderData = Object.values(order);
                    if (orderData.length > 0) {
                        fetch('process_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `order_data=${JSON.stringify(orderData)}&total_amount=${total}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.success + ' Order ID: ' + data.order_id);
                                order = {}; // Clear the order after successful submission
                                updateOrderDisplay();
                            } else if (data.error) {
                                alert('Error: ' + data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                         //   alert('An unexpected error occurred while placing your order.');
                        });
                    } else {
                        alert('Your order is empty!');
                    }
                <?php else: ?>
                    loginPrompt.style.display = 'block';
                <?php endif; ?>
            });

            // Initial update in case there's something in local storage (we're not using it yet)
            updateOrderDisplay();
        });
    </script>
</body>
</html>