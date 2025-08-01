<?php
session_start();
require_once('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: adminlogin.php");
    exit;
}

// Add product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $stock = intval($_POST['stock']);
    
    try {
        if (isUsingMongoDB()) {
            require_once 'functions_mongo.php';
            
            $productData = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category' => $category,
                'stock' => $stock,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ];
            
            $result = executeMongoQuery('products', 'insertOne', $productData);
            if ($result && isset($result['insertedId'])) {
                $success_message = "Product added successfully!";
            } else {
                $error_message = "Error adding product.";
            }
        } else {
            // MySQL fallback
            $conn = getDatabaseConnection();
            $sql = "INSERT INTO products (name, description, price, category, stock) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdsi", $name, $description, $price, $category, $stock);
            
            if ($stmt->execute()) {
                $success_message = "Product added successfully!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch all products
try {
    if (isUsingMongoDB()) {
        require_once 'functions_mongo.php';
        $products = executeMongoQuery('products', 'find', []);
    } else {
        // MySQL fallback
        $conn = getDatabaseConnection();
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} catch (Exception $e) {
    $products = [];
    $error_message = "Error fetching products: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        header { background: #28a745; padding: 20px; color: white; text-align: center; }
        nav a { color: white; margin: 0 10px; text-decoration: none; }
        .container { max-width: 1200px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background: #28a745; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .btn-edit, .btn-delete { padding: 8px 12px; margin: 2px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-edit { background: #ffc107; color: #000; }
        .btn-edit:hover { background: #e0a800; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
        .form-container { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .form-container input, .form-container select, .form-container textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .form-container textarea { height: 100px; resize: vertical; }
        .form-container button { background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .form-container button:hover { background: #218838; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .price { font-weight: bold; color: #28a745; }
        .stock { font-weight: bold; }
        .stock.low { color: #dc3545; }
        .stock.medium { color: #ffc107; }
        .stock.high { color: #28a745; }
        footer { text-align: center; background: #333; color: white; padding: 15px; margin-top: 40px; }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard - Manage Products</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Manage Products</h2>

    <?php if (isset($success_message)): ?>
        <div class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Add Product Form -->
    <div class="form-container">
        <h3>Add New Product</h3>
        <form method="POST" action="products.php">
            <input type="text" name="name" placeholder="Product Name" required>
            <textarea name="description" placeholder="Product Description" required></textarea>
            <input type="number" name="price" placeholder="Price" step="0.01" min="0" required>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="seeds">Seeds</option>
                <option value="fertilizers">Fertilizers</option>
                <option value="tools">Tools</option>
                <option value="pesticides">Pesticides</option>
                <option value="irrigation">Irrigation</option>
            </select>
            <input type="number" name="stock" placeholder="Stock Quantity" min="0" required>
            <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>

    <!-- Products Table -->
    <h3>Product List (<?= count($products) ?> products)</h3>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars(substr($product['description'] ?? '', 0, 50)) ?>...</td>
                    <td class="price">₹<?= number_format($product['price'] ?? 0, 2) ?></td>
                    <td><?= htmlspecialchars(ucfirst($product['category'] ?? 'N/A')) ?></td>
                    <td class="stock <?= ($product['stock'] ?? 0) < 10 ? 'low' : (($product['stock'] ?? 0) < 50 ? 'medium' : 'high') ?>">
                        <?= $product['stock'] ?? 0 ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($product['status'] ?? 'active')) ?></td>
                    <td>
                        <a href="edit_product.php?id=<?= $product['_id'] ?? $product['id'] ?>" class="btn-edit">Edit</a>
                        <a href="delete_product.php?id=<?= $product['_id'] ?? $product['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<footer>
    &copy; <?= date("Y") ?> Agri E-Commerce Admin Panel
</footer>

</body>
</html> 