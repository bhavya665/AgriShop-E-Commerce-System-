<?php
session_start();
require_once('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: adminlogin.php");
    exit;
}

// Fetch all users
try {
    if (isUsingMongoDB()) {
        require_once 'functions_mongo.php';
        $users = executeMongoQuery('users', 'find', []);
    } else {
        // MySQL fallback
        $conn = getDatabaseConnection();
        $sql = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
} catch (Exception $e) {
    $users = [];
    $error_message = "Error fetching users: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - Admin Panel</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
    header { background: #28a745; color: white; padding: 20px; text-align: center; }
    nav a { color: white; margin: 0 15px; text-decoration: none; }
    .container { max-width: 1200px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h2 { margin-bottom: 20px; color: #28a745; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #28a745; color: white; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .user-count { font-weight: bold; color: #28a745; }
    footer { text-align: center; background: #333; color: white; padding: 15px; margin-top: 40px; }
  </style>
</head>
<body>

<header>
  <h1>Admin Dashboard - Manage Users</h1>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="orders.php">Orders</a>
    <a href="users.php">Users</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="container">
  <h2>Registered Users</h2>

  <?php if (isset($error_message)): ?>
    <div class="message error"><?= htmlspecialchars($error_message) ?></div>
  <?php endif; ?>

  <h3>User List (<span class="user-count"><?= count($users) ?></span> users)</h3>
  <table>
    <thead>
      <tr>
        <th>User ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Registered At</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
        <tr>
          <td><?= htmlspecialchars($user['_id'] ?? $user['id'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($user['name'] ?? $user['full_name'] ?? $user['username'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars(ucfirst($user['role'] ?? 'user')) ?></td>
          <td><?= date('Y-m-d H:i:s', strtotime($user['created_at'] ?? 'now')) ?></td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" style="text-align: center; padding: 20px;">No users found.</td>
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
