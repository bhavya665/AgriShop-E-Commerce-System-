<?php
session_start();
require_once('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch product details
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        echo "Product not found!";
        exit;
    }
}

// Update product
if (isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];

    // Update query
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, stock = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsdi", $name, $description, $price, $category, $stock, $id);

    if ($stmt->execute()) {
        echo "Product updated successfully!";
        header("Location: products.php");  // Redirect back to products page
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        header { background: #28a745; padding: 20px; color: white; text-align: center; }
        nav a { color: white; margin: 0 10px; text-decoration: none; }
        .container { max-width: 1000px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; }
        .form-container input, .form-container select, .form-container textarea, .form-container button { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
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
    <h2>Edit Product</h2>

    <!-- Edit Product Form -->
    <div class="form-container">
        <form method="POST" action="edit_product.php?id=<?= $product['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Product Name" required>
            <textarea name="description" placeholder="Product Description" required><?= htmlspecialchars($product['description']) ?></textarea>
            <input type="number" name="price" value="<?= $product['price'] ?>" placeholder="Price" required>
            <select name="category" required>
                <option value="seeds" <?= $product['category'] == 'seeds' ? 'selected' : '' ?>>Seeds</option>
                <option value="tools" <?= $product['category'] == 'tools' ? 'selected' : '' ?>>Tools</option>
                <option value="crop-protection" <?= $product['category'] == 'crop-protection' ? 'selected' : '' ?>>Crop Protection</option>
                <option value="animal-husbandry" <?= $product['category'] == 'animal-husbandry' ? 'selected' : '' ?>>Animal Husbandry</option>
            </select>
            <input type="number" name="stock" value="<?= $product['stock'] ?>" placeholder="Stock Quantity" required>
            <button type="submit" name="update_product">Update Product</button>
        </form>
    </div>
</div>

<footer>
    &copy; <?= date("Y") ?> Agri E-Commerce
</footer>

</body>
</html>
