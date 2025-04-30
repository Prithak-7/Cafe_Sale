<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'administrator' && $_SESSION['role'] !== 'manager')) {
    header("Location: ../auth/login.php");
    exit();
}

require '../database.php';

$errors = [];
$success = false;

// Fetch the item details if an ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $item_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = :item_id");
        $stmt->bindParam(':item_id', $item_id);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            die("Item not found.");
        }
    } catch (PDOException $e) {
        die("Error fetching item: " . $e->getMessage());
    }
} else {
    die("Invalid item ID.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $category = trim($_POST['category']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    if (empty($name)) {
        $errors['name'] = 'Name is required.';
    }
    if (!is_numeric($price) || $price <= 0) {
        $errors['price'] = 'Price must be a positive number.';
    }
    if (empty($category)) {
        $errors['category'] = 'Category is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE items SET name = :name, description = :description, price = :price, category = :category, is_available = :is_available WHERE item_id = :item_id");
            $stmt->bindParam(':item_id', $item_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':is_available', $is_available);
            $stmt->execute();
            $success = 'Item updated successfully!';

            // Refetch the item details after successful update
            $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = :item_id");
            $stmt->bindParam(':item_id', $item_id);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $errors['db'] = 'Error updating item: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Item</h1>
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
            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <p class="error"><?= $errors['name'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?= htmlspecialchars($item['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($item['price']) ?>" required>
                <?php if (isset($errors['price'])): ?>
                    <p class="error"><?= $errors['price'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($item['category']) ?>" required>
                <?php if (isset($errors['category'])): ?>
                    <p class="error"><?= $errors['category'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="is_available">Available:</label>
                <input type="checkbox" id="is_available" name="is_available" value="1" <?= $item['is_available'] ? 'checked' : '' ?>>
            </div>

            <button type="submit">Update Item</button>
            <a href="manage_items.php" class="button">Cancel</a>
        </form>
    </div>
</body>
</html>