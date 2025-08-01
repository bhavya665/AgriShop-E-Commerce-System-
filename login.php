<?php
session_start();
require_once('config.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        if (isUsingMongoDB()) {
            // MongoDB authentication
            require_once 'functions_mongo.php';
            
            // Get user from database
            $user = getUserByEmailMongo($email);
            
            if ($user && password_verify($password, $user['password'])) {
                // Password matches
                $_SESSION['user_id'] = $user['_id'];
                $_SESSION['user_name'] = $user['name'] ?? $user['full_name'];
                header("Location: profile.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            // MySQL authentication (fallback)
            $conn = getDatabaseConnection();
            
            // Get user from database
            $query = "SELECT id, name, password FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            // Check if user exists
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $name, $hashedPassword);
                $stmt->fetch();

                if (password_verify($password, $hashedPassword)) {
                    // Password matches
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $name;
                    header("Location: profile.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No account found with that email.";
            }
        }
    } catch (Exception $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - AgriShop</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-content">
        <div class="form-container">
            <h2 class="text-center">Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="submit-btn">Login</button>
            </form>
            
            <p class="text-center mt-3">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                alert("Please fill out all fields.");
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
