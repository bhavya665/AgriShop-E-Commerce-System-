<?php
/**
 * User Profile Page
 * 
 * Displays user information and account options
 */
session_start();
require_once('config.php');

// Authentication check
if (!isLoggedIn()) {
    redirect('login.php', 'Please log in to view your profile');
}

// Get user data with error handling
$user = getUserProfile($_SESSION['user_id']);
if (!$user) {
    redirect('error.php', 'Unable to retrieve user information');
}

// Get order history
$orders = getUserOrders($_SESSION['user_id']);

// Page title for template
$pageTitle = "My Profile";
include('header.php'); // Move header to separate file
?>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="profile-sidebar">
                <h2>Account Navigation</h2>
                <ul class="profile-nav">
                    <li class="active"><a href="profile.php">My Profile</a></li>
                    <li><a href="user_orders.php">Order History</a></li>
                    <li><a href="addresses.php">Saved Addresses</a></li>
                    <li><a href="change-password.php">Change Password</a></li>
                </ul>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?= count($orders) ?></span>
                        <span class="stat-label">Orders</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= $user['days_member'] ?></span>
                        <span class="stat-label">Days as Member</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="profile-content">
                <div class="profile-header">
                    <h1>Welcome, <?= htmlspecialchars($user['username']) ?></h1>
                    <div class="last-login">
                        Last login: <?= formatDateTime($user['last_login']) ?>
                    </div>
                </div>
                
                <div class="profile-section">
                    <h3>Account Information</h3>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Username:</div>
                            <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Member since:</div>
                            <div class="info-value"><?= formatDate($user['created_at']) ?></div>
                        </div>
                    </div>
                    <a href="edit-profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
                
                <?php if (!empty($orders)): ?>
                <div class="profile-section">
                    <h3>Recent Orders</h3>
                    <div class="orders-table-responsive">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                                <tr>
                                    <td><?= $order['order_number'] ?></td>
                                    <td><?= formatDate($order['order_date']) ?></td>
                                    <td><span class="status-badge status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span></td>
                                    <td>₹<?= number_format($order['total'], 2) ?></td>
                                    <td><a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($orders) > 3): ?>
                        <a href="user_orders.php" class="btn btn-link">View All Orders <i class="fa fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="profile-actions">
                    <a href="logout.php" class="btn btn-danger">Log Out</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include helper functions file
function getUserProfile($userId) {
    try {
        if (isUsingMongoDB()) {
            // MongoDB version
            require_once 'functions_mongo.php';
            
            // Get user by ID
            $users = executeMongoQuery('users', 'find', ['_id' => $userId]);
            if (!empty($users)) {
                $user = $users[0];
                
                // Calculate days as member
                $createdDate = new DateTime($user['created_at']);
                $now = new DateTime();
                $daysMember = $now->diff($createdDate)->days;
                
                return [
                    'id' => $user['_id'],
                    'username' => $user['name'] ?? $user['full_name'] ?? 'User',
                    'email' => $user['email'],
                    'created_at' => $user['created_at'],
                    'last_login' => $user['last_login'] ?? $user['created_at'],
                    'days_member' => $daysMember
                ];
            }
            return false;
        } else {
            // MySQL version (fallback)
            $conn = getDatabaseConnection();
            
            $stmt = $conn->prepare("
                SELECT u.id, u.username, u.email, u.created_at, u.last_login,
                       DATEDIFF(NOW(), u.created_at) as days_member
                FROM users u
                WHERE u.id = ?
            ");
            
            if (!$stmt) {
                logError("Prepare failed: " . $conn->error);
                return false;
            }
            
            $stmt->bind_param("i", $userId);
            
            if (!$stmt->execute()) {
                logError("Execute failed: " . $stmt->error);
                return false;
            }
            
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            return $user;
        }
    } catch (Exception $e) {
        logError("Error getting user profile: " . $e->getMessage());
        return false;
    }
}

function getUserOrders($userId) {
    try {
        if (isUsingMongoDB()) {
            // MongoDB version
            require_once 'functions_mongo.php';
            
            // Get orders for user
            $orders = executeMongoQuery('orders', 'find', ['user_id' => $userId]);
            
            // Format orders for display
            $formattedOrders = [];
            foreach ($orders as $order) {
                $formattedOrders[] = [
                    'id' => $order['_id'],
                    'order_number' => $order['_id'], // Use ID as order number for now
                    'order_date' => $order['created_at'],
                    'status' => $order['status'] ?? 'pending',
                    'total' => $order['total_amount'] ?? 0
                ];
            }
            
            return $formattedOrders;
        } else {
            // MySQL version (fallback)
            $conn = getDatabaseConnection();
            
            $stmt = $conn->prepare("
                SELECT o.id, o.order_number, o.created_at as order_date, 
                       o.status, o.total
                FROM orders o
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC
            ");
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("i", $userId);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            $orders = [];
            
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            
            $stmt->close();
            return $orders;
        }
    } catch (Exception $e) {
        logError("Error getting user orders: " . $e->getMessage());
        return [];
    }
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatDateTime($date) {
    return date('d M Y, h:i A', strtotime($date));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function redirect($page, $message = '') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
    }
    header("Location: $page");
    exit;
}

function logError($message) {
    error_log($message, 0);
}

include('footer.php'); // Move footer to separate file
?>
