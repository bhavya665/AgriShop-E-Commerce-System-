<?php
session_start();
require_once('config.php');

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
  $product_id = intval($_POST['product_id']);
  $quantity = max(1, intval($_POST['quantity']));

  if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
  } else {
    $_SESSION['cart'][$product_id] = $quantity;
  }

  header("Location: cart.php");
  exit;
}

// Handle Remove from Cart
if (isset($_GET['remove'])) {
  $remove_id = intval($_GET['remove']);
  unset($_SESSION['cart'][$remove_id]);
  header("Location: cart.php");
  exit;
}

// Fetch cart products
$products = [];
if (!empty($_SESSION['cart'])) {
  $ids = implode(',', array_keys($_SESSION['cart']));
  $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
  while ($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart - Agri E-Commerce</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f2f2f2; margin: 0; padding: 0; }
    header { background-color: #28a745; padding: 20px; color: white; text-align: center; }
    nav a { color: white; text-decoration: none; margin: 0 10px; }
    .container { max-width: 1000px; margin: auto; background: white; padding: 30px; margin-top: 30px; border-radius: 8px; }
    h2 { margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 12px; border-bottom: 1px solid #ccc; text-align: left; }
    .total { text-align: right; font-size: 1.2em; font-weight: bold; }
    .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
    .btn:hover { background: #218838; }
    footer { text-align: center; background: #333; color: white; padding: 15px; margin-top: 30px; }
  </style>
</head>
<body>

<header>
  <h1>Agri E-Commerce</h1>
  <nav>
    <a href="home.php">Home</a>
    <a href="category.php?type=seeds">Seeds</a>
    <a href="category.php?type=tools">Tools</a>
    <a href="category.php?type=fertilizer">Fertilizer</a>
    <a href="category.php?type=feed">Animal Feed</a>
    <a href="<?= isset($_SESSION['user_id']) ? 'profile.php' : 'login.php' ?>">
      <?= isset($_SESSION['user_id']) ? 'Profile' : 'Login' ?>
    </a>
  </nav>
</header>

<div class="container">
  <h2>Your Cart</h2>

  <?php if (empty($products)): ?>
    <p>Your cart is empty. <a href="home.php">Start shopping!</a></p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Subtotal</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $total = 0; ?>
        <?php foreach ($products as $product): ?>
          <?php
            $pid = $product['id'];
            $qty = $_SESSION['cart'][$pid];
            $subtotal = $qty * $product['price'];
            $total += $subtotal;
          ?>
          <tr>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= $qty ?></td>
            <td>₹<?= number_format($product['price'], 2) ?></td>
            <td>₹<?= number_format($subtotal, 2) ?></td>
            <td><a class="btn" href="cart.php?remove=<?= $pid ?>">Remove</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="total">Total: ₹<?= number_format($total, 2) ?></div>
    <br>
    <a class="btn" href="checkout.php">Proceed to Checkout</a>
  <?php endif; ?>
</div>

<footer>
  &copy; <?= date("Y") ?> Agri E-Commerce
</footer>

</body>
</html>
