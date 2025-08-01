<?php
require_once('config.php');

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            if (isUsingMongoDB()) {
                // MongoDB registration
                require_once 'functions_mongo.php';
                
                // Check if user already exists
                $existingUser = getUserByEmailMongo($email);
                if ($existingUser) {
                    $error = "Email already registered.";
                } else {
                    // Create new user
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $userData = [
                        'full_name' => $name,  // Use full_name to match existing data structure
                        'email' => $email,
                        'password' => $hashedPassword,
                        'created_at' => date('Y-m-d H:i:s'),
                        'role' => 'user'
                    ];
                    
                    $result = createUserMongo($userData);
                    if ($result) {
                        $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                    } else {
                        $error = "Error: Could not register user.";
                    }
                }
            } else {
                // MySQL registration (fallback)
                $conn = getDatabaseConnection();
                
                // Check if user exists
                $checkQuery = "SELECT id FROM users WHERE email = ?";
                $stmt = $conn->prepare($checkQuery);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "Email already registered.";
                } else {
                    // Insert new user
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $insertQuery = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($insertQuery);
                    $stmt->bind_param("sss", $name, $email, $hashedPassword);
                    if ($stmt->execute()) {
                        $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                    } else {
                        $error = "Error: Could not register user.";
                    }
                }
            }
        } catch (Exception $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - AgriShop</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-content">
        <div class="form-container">
            <h2 class="text-center">Register</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                </div>
                
                <button type="submit" class="submit-btn">Register</button>
            </form>
            
            <p class="text-center mt-3">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                e.preventDefault();
                return false;
            }
            
            if (password.length < 6) {
                alert("Password must be at least 6 characters long.");
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
