<?php
/**
 * Admin Orders Management Page
 * 
 * Displays all orders for admin management
 */
session_start();
require_once('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: adminlogin.php");
    exit;
}

// Fetch all orders
try {
    if (isUsingMongoDB()) {
        require_once 'functions_mongo.php';
        $orders = executeMongoQuery('orders', 'find', []);
    } else {
        // MySQL fallback
        $conn = getDatabaseConnection();
        $sql = "SELECT o.*, u.name as user_name, u.email as user_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC";
        $result = $conn->query($sql);
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
} catch (Exception $e) {
    $orders = [];
    $error_message = "Error fetching orders: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        header { background: #28a745; color: white; padding: 20px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        .container { max-width: 1200px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #28a745; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .order-count { font-weight: bold; color: #28a745; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .total { font-weight: bold; color: #28a745; }
        footer { text-align: center; background: #333; color: white; padding: 15px; margin-top: 40px; }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard - Manage Orders</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Manage Orders</h2>

    <?php if (isset($error_message)): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <h3>Order List (<span class="order-count"><?= count($orders) ?></span> orders)</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['_id'] ?? $order['id'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($order['user_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($order['user_email'] ?? 'N/A') ?></td>
                    <td class="total">₹<?= number_format($order['total_amount'] ?? $order['total'] ?? 0, 2) ?></td>
                    <td>
                        <span class="status-badge status-<?= strtolower($order['status'] ?? 'pending') ?>">
                            <?= htmlspecialchars(ucfirst($order['status'] ?? 'pending')) ?>
                        </span>
                    </td>
                    <td><?= date('Y-m-d H:i', strtotime($order['created_at'] ?? 'now')) ?></td>
                    <td>
                        <a href="order-details.php?id=<?= $order['_id'] ?? $order['id'] ?>" style="color: #007bff; text-decoration: none;">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No orders found.</td>
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
