<?php
/**
 * User Orders Page
 * 
 * Displays the current user's order history
 */
session_start();
require_once('config.php');

// Authentication check
if (!isLoggedIn()) {
    redirect('login.php', 'Please log in to view your orders');
}

// Get user's orders
$orders = getUserOrders($_SESSION['user_id']);

// Page title for template
$pageTitle = "My Orders";
include('header.php');
?>

<div class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>My Orders</h1>
            <p>View your order history and track your purchases</p>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                <a href="home.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-container">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?= htmlspecialchars($order['order_number']) ?></h3>
                                <p class="order-date">Placed on <?= formatDate($order['order_date']) ?></p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="order-total">
                                <strong>Total: ₹<?= number_format($order['total'], 2) ?></strong>
                            </div>
                            <div class="order-actions">
                                <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-outline">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="page-actions">
            <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
        </div>
    </div>
</div>

<?php
// Helper functions
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
        error_log("Error getting user orders: " . $e->getMessage());
        return [];
    }
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
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

include('footer.php');
?> 