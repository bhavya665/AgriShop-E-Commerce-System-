<?php
session_start();
require_once('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Add product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    
    $sql = "INSERT INTO products (name, description, price, category, stock) VALUES ('$name', '$description', '$price', '$category', '$stock')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Product added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
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
        .container { max-width: 1000px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #28a745; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .btn-edit, .btn-delete { padding: 5px 10px; background: #ffc107; color: white; border: none; cursor: pointer; }
        .btn-edit:hover { background: #e0a800; }
        .btn-delete { background: #dc3545; }
        .btn-delete:hover { background: #c82333; }
        .form-container { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .form-container input, .form-container select, .form-container button { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        footer { text-align: center; background: #333; color: white; padding: 15px; margin-top: 40px; }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard - Agri E-Commerce</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Manage Products</a>
        <a href="orders.php">Manage Orders</a>
        <a href="users.php">Manage Users</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Manage Products</h2>

    <!-- Add Product Form -->
    <div class="form-container">
        <h3>Add New Product</h3>
        <form method="POST" action="products.php">
            <input type="text" name="name" placeholder="Product Name" required>
            <textarea name="description" placeholder="Product Description" required></textarea>
            <input type="number" name="price" placeholder="Price" required>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="seeds">Seeds</option>
                <option value="tools">Tools</option>
                <option value="crop-protection">Crop Protection</option>
                <option value="animal-husbandry">Animal Husbandry</option>
            </select>
            <input type="number" name="stock" placeholder="Stock Quantity" required>
            <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>

    <!-- Products Table -->
    <h3>Product List</h3>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td>$<?= number_format($row['price'], 2) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= $row['stock'] ?></td>
                <td>
                    <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                    <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn-delete">Delete</a>

                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<footer>
    &copy; <?= date("Y") ?> Agri E-Commerce
</footer>

</body>
</html>
