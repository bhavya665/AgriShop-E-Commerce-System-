<?php
session_start();
require_once('config.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
  echo "<script>alert('Your cart is empty.'); window.location.href='home.php';</script>";
  exit;
}

// Fetch products
$cart_items = $_SESSION['cart'];
$product_ids = implode(',', array_keys($cart_items));
$result = $conn->query("SELECT id, name, price FROM products WHERE id IN ($product_ids)");
$products = [];
while ($row = $result->fetch_assoc()) {
  $products[] = $row;
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $address = trim($_POST['address']);
  $payment_method = $_POST['payment_method'];

  if ($address && $payment_method) {
    // Simulate placing an order
    unset($_SESSION['cart']);
    echo "<script>alert('Order placed successfully!'); window.location.href='home.php';</script>";
    exit;
  } else {
    $error = "Please fill all fields.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - Agri E-Commerce</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
    header { background: #28a745; padding: 20px; color: white; text-align: center; }
    nav a { color: white; margin: 0 10px; text-decoration: none; }
    .container { max-width: 800px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h2 { margin-bottom: 20px; }
    input, textarea, select { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; }
    .btn { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    .btn:hover { background: #218838; }
    .summary { margin-top: 30px; }
    footer { text-align: center; background: #333; color: white; padding: 15px; margin-top: 40px; }
  </style>
</head>
<body>

<header>
  <h1>Agri E-Commerce</h1>
  <nav>
    <a href="home.php">Home</a>
    <a href="cart.php">Cart</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="container">
  <h2>Checkout</h2>
  
  <?php if (isset($error)): ?>
    <p style="color: red;"><?= $error ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Shipping Address:</label>
    <textarea name="address" required placeholder="Enter full delivery address"></textarea>

    <label>Payment Method:</label>
    <select name="payment_method" required>
      <option value="">-- Select --</option>
      <option value="COD">Cash on Delivery</option>
      <option value="UPI">UPI</option>
      <option value="Card">Debit/Credit Card</option>
    </select>

    <div class="summary">
      <h3>Order Summary</h3>
      <ul>
        <?php $total = 0; ?>
        <?php foreach ($products as $p): ?>
          <?php 
            $qty = $cart_items[$p['id']];
            $subtotal = $qty * $p['price'];
            $total += $subtotal;
          ?>
          <li><?= $p['name'] ?> × <?= $qty ?> = ₹<?= number_format($subtotal, 2) ?></li>
        <?php endforeach; ?>
      </ul>
      <p><strong>Total: ₹<?= number_format($total, 2) ?></strong></p>
    </div>

    <button type="submit" class="btn">Place Order</button>
  </form>
</div>

<footer>
  &copy; <?= date("Y") ?> Agri E-Commerce
</footer>

</body>
</html>
