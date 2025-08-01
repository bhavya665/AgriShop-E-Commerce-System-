<?php
// Check if user is logged in (example function)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fetch products for homepage
function getFeaturedProducts($conn) {
    $sql = "SELECT * FROM products LIMIT 8";
    $result = $conn->query($sql);
    return $result;
}
?>
