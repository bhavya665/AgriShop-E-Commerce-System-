<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['admin_username'])) {
    header("Location: adminlogin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f1f8e9;
      margin: 0;
    }
    .container {
      padding: 20px;
    }
    .header {
      background: #558b2f;
      color: white;
      padding: 20px;
      text-align: center;
      position: relative;
    }
    .logout {
      position: absolute;
      top: 20px;
      right: 20px;
      background: #c62828;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .cards {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      margin-top: 30px;
    }
    .card {
      background: white;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      width: 30%;
      min-width: 250px;
      text-align: center;
      transition: transform 0.3s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card a {
      display: block;
      text-decoration: none;
      color: #33691e;
      font-size: 20px;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="header">
  <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></h1>
  <form action="logout.php" method="POST">
    <button class="logout" type="submit">Logout</button>
  </form>
</div>

<div class="container">
  <div class="cards">
    <div class="card">
      <a href="products.php">Manage Products</a>
    </div>
    <div class="card">
      <a href="orders.php">Manage Orders</a>
    </div>
    <div class="card">
      <a href="users.php">Manage Users</a>
    </div>
  </div>
</div>

</body>
</html>
