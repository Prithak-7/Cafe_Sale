<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

try {
    $stmt = $pdo->query("SELECT * FROM items WHERE is_available = 1");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch available tables
    $stmt = $pdo->query("SELECT table_id, table_number FROM tables WHERE is_available = 1");
    $available_tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - Staff</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Place Order</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Menu</a></li>
                <li><a href="place_order.php">Place Order</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="form-group">
            <label for="table_id">Select Table:</label>
            <select id="table_id" name="table_id">
                <option value="">Select Table Number</option>
                <?php foreach ($available_tables as $table): ?>
                    <option value="<?= $table['table_id'] ?>"><?= $table['table_number'] ?></option>
                <?php endforeach; ?>
            </select>
            <p id="tableWarning" style="color: red; display: none;">Please select a table.</p>
        </div>

        <div class="menu">
            <h2>Menu</h2>
            <?php if (empty($items)): ?>
                <p>No items available.</p>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="menu-item" data-id="<?= $item['item_id'] ?>" data-price="<?= $item['price'] ?>">
                        <div class="item-details">
                            <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="item-description"><?= htmlspecialchars($item['description']) ?></p>
                            <p class="item-price">₹<?= htmlspecialchars(number_format($item['price'], 2)) ?></p>
                        </div>
                        <button onclick="addToOrder(<?= $item['item_id'] ?>, '<?= htmlspecialchars($item['name']) ?>', <?= $item['price'] ?>)">Add to Order</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <div id="order-items"></div>
            <p>Total: ₹<span id="total">0.00</span></p>
            <button id="confirm-order">Confirm Order</button>
        </div>
    </div>

    <script>
        const order = {};
        let staffTotal = 0;
        const orderItemsDiv = document.getElementById('order-items');
        const totalSpan = document.getElementById('total');
        const confirmOrderButton = document.getElementById('confirm-order');
        const tableSelect = document.getElementById('table_id');
        const tableWarning = document.getElementById('tableWarning');

        function addToOrder(id, name, price) {
            if (order[id]) {
                order[id].quantity++;
            } else {
                order[id] = { id: id, name: name, price: price, quantity: 1 };
            }
            updateStaffOrderDisplay();
        }

        function updateStaffOrderDisplay() {
            orderItemsDiv.innerHTML = '';
            staffTotal = 0;
            for (const id in order) {
                const item = order[id];
                const itemTotal = item.price * item.quantity;
                staffTotal += itemTotal;
                orderItemsDiv.innerHTML += `
                    <p>${item.name} x ${item.quantity} - ₹${itemTotal.toFixed(2)} 
                       <button onclick="changeQuantity(${item.id}, -1)">-</button>
                       <button onclick="changeQuantity(${item.id}, 1)">+</button>
                       <button onclick="removeFromOrder(${item.id})">Remove</button>
                    </p>
                `;
            }
            totalSpan.textContent = staffTotal.toFixed(2);
        }

        function changeQuantity(id, change) {
            if (order[id]) {
                order[id].quantity += change;
                if (order[id].quantity <= 0) {
                    delete order[id];
                }
                updateStaffOrderDisplay();
            }
        }

        function removeFromOrder(id) {
            delete order[id];
            updateStaffOrderDisplay();
        }

        confirmOrderButton.addEventListener('click', () => {
            if (Object.keys(order).length > 0) {
                const tableId = tableSelect.value;
                // Validation - ensure a table is selected (if not takeaway)
                if (tableId !== "") {
                    tableWarning.style.display = 'none';
                    const orderData = Object.values(order).map(item => ({
                        id: item.id,
                        quantity: item.quantity,
                        price: item.price
                    }));

                    fetch('../process_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `table_id=${tableId}&order_data=${JSON.stringify(orderData)}&total_amount=${staffTotal}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.success + ' Order ID: ' + data.order_id);
                            order = {}; // Clear the order
                            updateStaffOrderDisplay();
                            tableSelect.value = ''; // Reset table selection
                            // Optionally, update table availability here if needed
                        } else if (data.error) {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred while placing the order.');
                    });
                } else {
                    tableWarning.style.display = 'block';
                }
            } else {
                alert('The order is empty!');
            }
        });

        updateStaffOrderDisplay(); // Initial call
    </script>
</body>
</html>