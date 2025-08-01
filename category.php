<?php
// Start session if not already started
session_start();

// Include database connection
require_once('config.php');

// Configuration and helper functions
$siteName = "AgriCart";
$baseUrl = "http://localhost/webfinal/"; // Update this to your actual base URL

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Get category ID from URL parameter
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$categoryName = "All Categories"; // Default name

// If a specific category is requested, get its details
if ($categoryId > 0 && isset($conn)) {
    $stmt = $conn->prepare("SELECT name, description FROM categories WHERE id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
        $categoryName = $category['name'];
        $categoryDescription = $category['description'];
    }
    $stmt->close();
}

// Define default filter values
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'popularity';

// Get all available categories for sidebar
$categories = [];
if (isset($conn)) {
    $result = $conn->query("SELECT id, name, image FROM categories ORDER BY name");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
}

// Fetch products based on filters
function getProducts($conn, $categoryId, $minPrice, $maxPrice, $sortBy) {
    $products = [];
    
    $sql = "SELECT p.id, p.name, p.description, p.price, p.discount_price, p.stock, p.image, p.rating, 
           c.name as category_name FROM products p 
           LEFT JOIN categories c ON p.category_id = c.id 
           WHERE p.price >= ? AND p.price <= ?";
    
    if ($categoryId > 0) {
        $sql .= " AND p.category_id = ?";
    }
    
    // Add sorting
    switch ($sortBy) {
        case 'price_low':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'newest':
            $sql .= " ORDER BY p.created_at DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY p.rating DESC";
            break;
        default:
            $sql .= " ORDER BY p.popularity DESC";
    }
    
    try {
        $stmt = $conn->prepare($sql);
        
        if ($categoryId > 0) {
            $stmt->bind_param("ddi", $minPrice, $maxPrice, $categoryId);
        } else {
            $stmt->bind_param("dd", $minPrice, $maxPrice);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        // Handle error
        error_log("Database error: " . $e->getMessage());
    }
    
    return $products;
}

// Get products
$products = [];
if (isset($conn)) {
    $products = getProducts($conn, $categoryId, $minPrice, $maxPrice, $sortBy);
}

// Get highest product price for filter
$maxPossiblePrice = 10000; // Default
if (isset($conn)) {
    $result = $conn->query("SELECT MAX(price) as max_price FROM products");
    if ($result && $row = $result->fetch_assoc()) {
        $maxPossiblePrice = ceil($row['max_price']);
    }
}

// Active page for navigation
$currentPage = 'categories';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categoryName); ?> - <?php echo htmlspecialchars($siteName); ?></title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/styles.css">
    <link rel="icon" href="<?php echo $baseUrl; ?>favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ===== CSS RESET & BASE STYLES ===== */
        :root {
            --primary-color: #4a8f29;
            --primary-dark: #3a7020;
            --secondary-color: #f4f4f4;
            --accent-color: #e74c3c;
            --text-dark: #333;
            --text-medium: #666;
            --text-light: #999;
            --bg-light: #f9f9f9;
            --bg-white: #ffffff;
            --shadow-sm: 0 2px 5px rgba(0,0,0,0.05);
            --shadow-md: 0 3px 10px rgba(0,0,0,0.1);
            --shadow-lg: 0 5px 20px rgba(0,0,0,0.15);
            --border-radius-sm: 4px;
            --border-radius-md: 8px;
            --border-radius-lg: 16px;
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-xxl: 3rem;
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }

        img {
            max-width: 100%;
            height: auto;
        }

        a {
            text-decoration: none;
            color: var(--primary-color);
            transition: color var(--transition-normal);
        }

        a:hover {
            color: var(--primary-dark);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }

        /* ===== LAYOUT COMPONENTS ===== */
        /* Header Banner */
        .category-header {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('<?php echo $baseUrl; ?>assets/images/category-banner.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: var(--spacing-xxl) var(--spacing-md);
            text-align: center;
            margin-bottom: var(--spacing-xl);
            position: relative;
            overflow: hidden;
            border-radius: 0 0 var(--border-radius-md) var(--border-radius-md);
        }
        
        .category-header:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(74,143,41,0.8) 0%, rgba(58,112,32,0) 70%);
            z-index: 0;
        }
        
        .category-header-content {
            position: relative;
            z-index: 1;
        }
        
        .category-title {
            font-size: 2.5rem;
            margin-bottom: var(--spacing-md);
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            color: #fff;
        }
        
        .category-description {
            max-width: 800px;
            margin: 0 auto;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        /* Main Layout */
        .content-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-xl);
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-md);
        }
        
        /* Sidebar */
        .sidebar {
            width: 100%;
            max-width: 280px;
            margin-bottom: var(--spacing-xl);
        }
        
        .filter-section {
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            transition: transform var(--transition-normal), box-shadow var(--transition-normal);
        }
        
        .filter-section:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .filter-section h3 {
            margin-bottom: var(--spacing-md);
            color: var(--primary-color);
            font-size: 1.2rem;
            position: relative;
            padding-bottom: var(--spacing-sm);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            min-width: 0;
        }
        
        /* ===== CATEGORIES LIST ===== */
        .category-list {
            list-style: none;
        }
        
        .category-list li {
            margin-bottom: var(--spacing-sm);
        }
        
        .category-list a {
            display: flex;
            align-items: center;
            color: var(--text-dark);
            text-decoration: none;
            padding: var(--spacing-sm) 0;
            transition: all var(--transition-normal);
            border-radius: var(--border-radius-sm);
        }
        
        .category-list a:hover {
            color: var(--primary-color);
            transform: translateX(5px);
            background-color: rgba(74, 143, 41, 0.05);
            padding-left: var(--spacing-sm);
        }
        
        .category-list a.active {
            color: var(--primary-color);
            font-weight: 600;
            background-color: rgba(74, 143, 41, 0.1);
            padding-left: var(--spacing-sm);
        }
        
        .category-list img {
            width: 35px;
            height: 35px;
            object-fit: cover;
            border-radius: var(--border-radius-sm);
            margin-right: var(--spacing-md);
            border: 1px solid #eee;
        }
        
        /* ===== PRICE FILTER ===== */
        .price-filter {
            margin: var(--spacing-md) 0;
        }
        
        .price-inputs {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-sm);
            align-items: center;
        }
        
        .price-inputs input {
            width: 100%;
            padding: var(--spacing-sm);
            border: 1px solid #ddd;
            border-radius: var(--border-radius-sm);
            transition: border var(--transition-normal);
        }
        
        .price-inputs input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .filter-btn {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: background var(--transition-normal);
            margin-top: var(--spacing-md);
            width: 100%;
            text-align: center;
        }
        
        .filter-btn:hover {
            background: var(--primary-dark);
        }
        
        /* ===== SORT OPTIONS ===== */
        .sort-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-md);
            background-color: var(--bg-white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
        }
        
        .sort-options select {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid #ddd;
            border-radius: var(--border-radius-sm);
            background-color: white;
            transition: border var(--transition-normal);
        }
        
        .sort-options select:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .results-count {
            color: var(--text-medium);
            font-weight: 500;
        }

        /* ===== PRODUCT GRID ===== */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        
        .product-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: transform var(--transition-normal), box-shadow var(--transition-normal);
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .product-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: var(--spacing-md);
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .product-category {
            color: var(--text-light);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: var(--spacing-xs);
        }
        
        .product-name {
            font-size: 1.1rem;
            margin-bottom: var(--spacing-sm);
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .product-price {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: var(--spacing-sm);
            font-size: 1.1rem;
        }
        
        .product-price .original-price {
            text-decoration: line-through;
            color: var(--text-light);
            margin-right: var(--spacing-sm);
            font-weight: normal;
            font-size: 0.9rem;
        }
        
        .product-rating {
            color: #FFB800;
            margin-bottom: var(--spacing-sm);
        }
        
        .product-stock {
            font-size: 0.9rem;
            margin-bottom: var(--spacing-md);
        }
        
        .in-stock {
            color: var(--primary-color);
        }
        
        .low-stock {
            color: #e67e22;
        }
        
        .out-of-stock {
            color: var(--accent-color);
        }
        
        .product-actions {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: var(--spacing-sm);
            margin-top: auto;
        }
        
        /* ===== BUTTONS ===== */
        .btn {
            display: inline-block;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            text-align: center;
            transition: all var(--transition-normal);
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            color: white;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--text-dark);
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .btn i {
            margin-right: var(--spacing-xs);
        }
        
        /* ===== DISCOUNT BADGE ===== */
        .discount-badge {
            position: absolute;
            top: var(--spacing-md);
            left: 0;
            background: var(--accent-color);
            color: white;
            padding: var(--spacing-xs) var(--spacing-sm);
            font-weight: bold;
            font-size: 0.85rem;
            z-index: 10;
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
        }
        
        /* ===== PAGINATION ===== */
        .pagination {
            display: flex;
            justify-content: center;
            margin: var(--spacing-xl) 0;
            gap: var(--spacing-xs);
        }
        
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-sm);
            background: var(--bg-white);
            color: var(--text-dark);
            text-decoration: none;
            border: 1px solid #ddd;
            transition: all var(--transition-normal);
        }
        
        .pagination a:hover {
            background: var(--secondary-color);
            border-color: #ccc;
        }
        
        .pagination .current {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* ===== SECTION COMPONENTS ===== */
        .section {
            margin-top: var(--spacing-xxl);
            padding: var(--spacing-xl) 0;
        }
        
        .section-alt {
            background-color: #f8f9fa;
            padding: var(--spacing-xl) 0;
            margin: var(--spacing-xxl) calc(var(--spacing-md) * -1);
            margin-bottom: 0;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
        }
        
        .section-title {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
            font-size: 1.5rem;
            position: relative;
            padding-bottom: var(--spacing-sm);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .view-all {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: color var(--transition-normal);
        }
        
        .view-all:hover {
            color: var(--primary-dark);
        }
        
        .view-all i {
            margin-left: var(--spacing-xs);
            transition: transform var(--transition-normal);
        }
        
        .view-all:hover i {
            transform: translateX(3px);
        }
        
        /* ===== FEATURED CATEGORIES ===== */
        .related-categories {
            margin-top: var(--spacing-xxl);
        }
        
        .related-category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: var(--spacing-md);
            margin-top: var(--spacing-md);
        }
        
        .related-category-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            text-align: center;
            transition: transform var(--transition-normal), box-shadow var(--transition-normal);
            display: block;
        }
        
        .related-category-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .related-category-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .related-category-card:hover img {
            transform: scale(1.05);
        }
        
        .related-category-card h3 {
            padding: var(--spacing-md);
            margin: 0;
            font-size: 1rem;
            color: var(--text-dark);
            transition: color var(--transition-normal);
        }
        
        .related-category-card:hover h3 {
            color: var(--primary-color);
        }

        /* ===== FARMER'S GUIDE ===== */
        .farmers-guide {
            background: #f0f7eb;
            background: linear-gradient(135deg, rgba(240,247,235,1) 0%, rgba(230,243,220,1) 100%);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-xl);
            margin-top: var(--spacing-xxl);
            box-shadow: var(--shadow-sm);
        }
        
        .guide-content {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-xl);
            margin-top: var(--spacing-md);
        }
        
        .guide-image {
            flex: 1;
            min-width: 300px;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        
        .guide-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .farmers-guide:hover .guide-image img {
            transform: scale(1.03);
        }
        
        .guide-text {
            flex: 2;
            min-width: 300px;
        }
        
        .guide-item {
            margin-bottom: var(--spacing-md);
        }
        
        .guide-item h4 {
            display: flex;
            align-items: center;
            margin-bottom: var(--spacing-xs);
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .guide-item i {
            color: var(--primary-color);
            margin-right: var(--spacing-sm);
            font-size: 1.1rem;
        }
        
        .guide-item p {
            color: var(--text-medium);
            margin-left: calc(var(--spacing-md) + var(--spacing-sm));
        }

        /* ===== NO PRODUCTS ===== */
        .no-products {
            text-align: center;
            padding: var(--spacing-xl);
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-md);
        }
        
        .no-products h3 {
            margin-bottom: var(--spacing-md);
            color: var(--text-dark);
        }
        
        .no-products p {
            color: var(--text-medium);
            margin-bottom: var(--spacing-md);
        }

        /* ===== RESPONSIVE STYLES ===== */
        @media (max-width: 992px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .sidebar {
                max-width: 100%;
            }
            
            .filter-sections-container {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: var(--spacing-md);
            }
        }
        
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .category-header {
                padding: var(--spacing-xl) var(--spacing-md);
            }
            
            .category-title {
                font-size: 2rem;
            }
            
            .related-category-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .guide-content {
                flex-direction: column;
            }
            
            .product-actions {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .sort-options {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-sm);
            }
            
            .related-category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .category-title {
                font-size: 1.8rem;
            }
            
            .category-description {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
        }
        
        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .product-card, .filter-section, .farmers-guide {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .product-card:nth-child(2n) {
            animation-delay: 0.1s;
        }
        
        .product-card:nth-child(3n) {
            animation-delay: 0.2s;
        }
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include 'header.php'; ?>
    
    <!-- Category Header Banner -->
    <div class="category-header">
        <div class="category-header-content">
            <h1 class="category-title"><?php echo htmlspecialchars($categoryName); ?></h1>
            <?php if (isset($categoryDescription)): ?>
                <p class="category-description"><?php echo htmlspecialchars($categoryDescription); ?></p>
            <?php else: ?>
                <p class="category-description">Explore our wide range of high-quality agricultural products designed to boost your farming efficiency.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div class="content-wrapper">
            <!-- Sidebar with Filters -->
            <aside class="sidebar">
                <div class="filter-sections-container">
                    <!-- Categories Filter -->
                    <div class="filter-section">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li>
                                <a href="category.php" <?php echo $categoryId == 0 ? 'class="active"' : ''; ?>>
                                    <img src="<?php echo $baseUrl; ?>assets/images/all-categories.jpg" alt="All Categories">
                                    All Categories
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <a href="category.php?id=<?php echo $category['id']; ?>" 
                                       <?php echo $categoryId == $category['id'] ? 'class="active"' : ''; ?>>
                                        <img src="<?php echo $baseUrl; ?>assets/images/<?php echo htmlspecialchars($category['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($category['name']); ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            
                            <?php if (empty($categories)): ?>
                                <!-- Fallback categories if database fetch fails -->
                                <li>
                                    <a href="category.php?id=1">
                                        <img src="<?php echo $baseUrl; ?>assets/images/seeds.jpg" alt="Seeds">
                                        Seeds
                                    </a>
                                </li>
                                <li>
                                    <a href="category.php?id=2">
                                        <img src="<?php echo $baseUrl; ?>assets/images/fertilizers.jpg" alt="Fertilizers">
                                        Fertilizers
                                    </a>
                                </li>
                                <li>
                                    <a href="category.php?id=3">
                                        <img src="<?php echo $baseUrl; ?>assets/images/tools.jpg" alt="Tools">
                                        Tools
                                    </a>
                                </li>
                                <li>
                                    <a href="category.php?id=4">
                                        <img src="<?php echo $baseUrl; ?>assets/images/pesticides.jpg" alt="Pesticides">
                                        Pesticides
                                    </a>
                                </li>
                                <li>
                                    <a href="category.php?id=5">
                                        <img src="<?php echo $baseUrl; ?>assets/images/irrigation.jpg" alt="Irrigation">
                                        Irrigation
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Price Filter -->
                    <div class="filter-section">
                        <h3>Filter by Price</h3>
                        <form action="category.php" method="GET">
                            <?php if ($categoryId > 0): ?>
                                <input type="hidden" name="id" value="<?php echo $categoryId; ?>">
                            <?php endif; ?>
                            <div class="price-filter">
                                <div class="price-inputs">
                                    <input type="number" name="min_price" value="<?php echo $minPrice; ?>" min="0" placeholder="Min">
                                    <span>to</span>
                                    <input type="number" name="max_price" value="<?php echo $maxPrice; ?>" max="<?php echo $maxPossiblePrice; ?>" placeholder="Max">
                                </div>
                            </div>
                            <button type="submit" class="filter-btn">Apply Filter</button>
                        </form>
                    </div>
                    
                    <!-- Seasonal Guide -->
                    <div class="filter-section">
                        <h3>Seasonal Farming Guide</h3>
                        <div style="margin-top: 15px;">
                            <div style="margin-bottom: 12px;">
                                <h4 style="margin-bottom: 5px; color: #333;">Current Season: 
                                    <span style="color: var(--primary-color);">
                                        <?php echo date('M') == 'Dec' || date('M') == 'Jan' || date('M') == 'Feb' ? 'Winter' : 
                                              (date('M') == 'Mar' || date('M') == 'Apr' || date('M') == 'May' ? 'Spring' : 
                                              (date('M') == 'Jun' || date('M') == 'Jul' || date('M') == 'Aug' ? 'Summer' : 'Fall')); ?>
                                    </span>
                                </h4>
                                <p style="font-size: 0.9rem; color: #666;">Recommended products for this season:</p>
                                <ul style="margin: 10px 0 15px 15px; font-size: 0.9rem; color: #666;">
                                    <?php if (date('M') == 'Dec' || date('M') == 'Jan' || date('M') == 'Feb'): // Winter ?>
                                        <li>Winter vegetable seeds</li>
                                        <li>Frost protection equipment</li>
                                        <li>Soil health improvers</li>
                                    <?php elseif (date('M') == 'Mar' || date('M') == 'Apr' || date('M') == 'May'): // Spring ?>
                                        <li>Spring crop seeds</li>
                                        <li>Organic fertilizers</li>
                                        <li>Irrigation equipment</li>
                                    <?php elseif (date('M') == 'Jun' || date('M') == 'Jul' || date('M') == 'Aug'): // Summer ?>
                                        <li>Heat-resistant crop varieties</li>
                                        <li>Water conservation tools</li>
                                        <li>Pest control solutions</li>
                                    <?php else: // Fall ?>
                                        <li>Fall harvest tools</li>
                                        <li>Soil preparation kits</li>
                                        <li>Cover crop seeds</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <a href="<?php echo $baseUrl; ?>seasonal-guide.php" class="btn btn-secondary" style="width: 100%; text-align: center;">View Full Guide</a>
                        </div>
                    </div>
                </div>
            </aside>
            
            <!-- Main Product Listing -->
            <div class="main-content">
                <!-- Sort Options -->
                <div class="sort-options">
                    <span class="results-count"><?php echo count($products); ?> products found</span>
                    <form action="category.php" method="GET" style="display: flex; align-items: center;">
                        <?php if ($categoryId > 0): ?>
                            <input type="hidden" name="id" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                        <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                        <label for="sort" style="margin-right: 10px;">Sort by:</label>
                        <select name="sort" id="sort" onchange="this.form.submit()">
                            <option value="popularity" <?php echo $sortBy == 'popularity' ? 'selected' : ''; ?>>Popularity</option>
                            <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="rating" <?php echo $sortBy == 'rating' ? 'selected' : ''; ?>>Customer Rating</option>
                        </select>
                    </form>
                </div>
                
                <?php if (!empty($products)): ?>
                    <!-- Product Grid -->
                    <div class="product-grid">
                        <?php foreach ($products as $product): 
                            $hasDiscount = isset($product['discount_price']) && $product['discount_price'] > 0;
                            $displayPrice = $hasDiscount ? $product['discount_price'] : $product['price'];
                            
                            if ($hasDiscount) {
                                $discountPercentage = round(($product['price'] - $product['discount_price']) / $product['price'] * 100);
                            }
                            
                            // Determine stock status
                            $stockStatus = '';
                            $stockClass = '';
                            
                            if (isset($product['stock'])) {
                                if ($product['stock'] <= 0) {
                                    $stockStatus = 'Out of Stock';
                                    $stockClass = 'out-of-stock';
                                } elseif ($product['stock'] <= 5) {
                                    $stockStatus = 'Low Stock: ' . $product['stock'] . ' left';
                                    $stockClass = 'low-stock';
                                } else {
                                    $stockStatus = 'In Stock';
                                    $stockClass = 'in-stock';
                                }
                            }
                        ?>
                            <div class="product-card">
                                <?php if ($hasDiscount): ?>
                                    <div class="discount-badge"><?php echo $discountPercentage; ?>% OFF</div>
                                <?php endif; ?>
                                
                                <div class="product-image">
                                    <img src="<?php echo $baseUrl; ?>assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                
                                <div class="product-info">
                                    <?php if (isset($product['category_name'])): ?>
                                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                    <?php endif; ?>
                                    
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    
                                    <div class="product-price">
                                        <?php if ($hasDiscount): ?>
                                            <span class="original-price">₹<?php echo htmlspecialchars($product['price']); ?></span>
                                        <?php endif; ?>
                                        <span>₹<?php echo htmlspecialchars($displayPrice); ?></span>
                                    </div>
                                    
                                    <?php if (isset($product['rating'])): ?>
                                        <div class="product-rating">
                                            <?php
                                            $rating = floatval($product['rating']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } elseif ($i - $rating < 1) {
                                                    echo '<i class="fas fa-star-half-alt"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            echo ' <span>(' . $rating . ')</span>';
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="product-stock <?php echo $stockClass; ?>">
                                        <i class="fas <?php echo $stockClass == 'in-stock' ? 'fa-check-circle' : ($stockClass == 'low-stock' ? 'fa-exclamation-circle' : 'fa-times-circle'); ?>"></i>
                                        <?php echo $stockStatus; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Details</a>
                                        
                                        <?php if (!isset($product['stock']) || $product['stock'] > 0): ?>
                                            <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-primary" disabled style="background-color: #ccc; cursor: not-allowed;">
                                                Out of Stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <a href="#" aria-label="Previous page"><i class="fas fa-chevron-left"></i></a>
                        <a href="#" class="current">1</a>
                        <a href="#">2</a>
                        <a href="#">3</a>
                        <span>...</span>
                        <a href="#">10</a>
                        <a href="#" aria-label="Next page"><i class="fas fa-chevron-right"></i></a>
                    </div>
                    
                <?php else: ?>
                    <!-- No Products Found Message -->
                    <div class="no-products">
                        <h3>No products found matching your criteria</h3>
                        <p>Try adjusting your filters or browse our other categories.</p>
                        <a href="category.php" class="btn btn-primary" style="margin-top: 15px;">View All Products</a>
                    </div>
                <?php endif; ?>
                
                <!-- Farmer's Guide Section -->
                <div class="farmers-guide">
                    <h2 class="section-title">Farmer's Guide: Best Practices</h2>
                    <div class="guide-content">
                        <div class="guide-image">
                            <img src="<?php echo $baseUrl; ?>assets/images/farmers-guide.jpg" alt="Farming Guide">
                        </div>
                        <div class="guide-text">
                            <?php if ($categoryId > 0): ?>
                                <?php if ($categoryName == "Seeds"): ?>
                                    <div class="guide-item">
                                        <h4><i class="fas fa-seedling"></i> Seed Selection</h4>
                                        <p>Choose seeds that are appropriate for your local climate and soil conditions. Consider disease-resistant varieties for better yields.</p>
                                    </div>
                                    <div class="guide-item">
                                        <h4><i class="fas fa-temperature-low"></i> Storage</h4>
                                        <p>Store seeds in a cool, dry place to maintain viability. Most seeds remain viable for 1-3 years when properly stored.</p>
                                    </div>
                                <?php elseif ($categoryName == "Fertilizers"): ?>
                                    <div class="guide-item">
                                        <h4><i class="fas fa-flask"></i> Application Timing</h4>
                                        <p>Apply fertilizers during the growing season when plants can best utilize nutrients. Avoid application during heavy rainfall periods.</p>
                                    </div>
                                    <div class="guide-item">
                                        <h4><i class="fas fa-balance-scale"></i> Dosage</h4>
                                        <p>Follow recommended dosage instructions to avoid over-fertilization, which can damage plants and pollute water sources.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="guide-item">
                                        <h4><i class="fas fa-water"></i> Water Management</h4>
                                        <p>Efficient water management is crucial. Consider drip irrigation systems to minimize water waste and improve crop yields.</p>
                                    </div>
                                    <div class="guide-item">
                                        <h4><i class="fas fa-bug"></i> Integrated Pest Management</h4>
                                        <p>Use a combination of biological controls, crop rotation, and resistant varieties before resorting to chemical pesticides.</p>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="guide-item">
                                    <h4><i class="fas fa-seedling"></i> Crop Selection</h4>
                                    <p>Choose crops that are well-adapted to your local climate, soil conditions, and market demand for best results.</p>
                                </div>
                                <div class="guide-item">
                                    <h4><i class="fas fa-water"></i> Water Management</h4>
                                    <p>Implement efficient irrigation systems and water conservation techniques to maximize crop yield while minimizing water usage.</p>
                                </div>
                            <?php endif; ?>
                            <div class="guide-item">
                                <h4><i class="fas fa-leaf"></i> Soil Health</h4>
                                <p>Regularly test your soil and maintain its health through proper crop rotation, organic matter addition, and pH management.</p>
                            </div>
                            <a href="<?php echo $baseUrl; ?>farming-guides.php" class="btn btn-primary" style="margin-top: 10px;">Read Full Guide</a>
                        </div>
                    </div>
                </div>
                
                <!-- Featured Products Section -->
                <section class="section">
                    <div class="section-header">
                        <h2 class="section-title">Featured Products</h2>
                        <a href="<?php echo $baseUrl; ?>featured-products.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="product-grid">
                        <!-- Display 4 featured products -->
                        <?php
                        $featuredProducts = [];
                        
                        if (isset($conn)) {
                            $result = $conn->query("SELECT id, name, price, discount_price, image FROM products WHERE featured = 1 ORDER BY RAND() LIMIT 4");
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $featuredProducts[] = $row;
                                }
                            }
                        }
                        
                        // Fallback data if no featured products from database
                        if (empty($featuredProducts)) {
                            $featuredProducts = [
                                ['id' => 101, 'name' => 'Organic Tomato Seeds', 'price' => 299, 'image' => 'featured1.jpg'],
                                ['id' => 102, 'name' => 'Premium Garden Tool Set', 'price' => 1499, 'discount_price' => 1299, 'image' => 'featured2.jpg'],
                                ['id' => 103, 'name' => 'Drip Irrigation Kit', 'price' => 2999, 'image' => 'featured3.jpg'],
                                ['id' => 104, 'name' => 'Organic Fertilizer', 'price' => 599, 'discount_price' => 499, 'image' => 'featured4.jpg']
                            ];
                        }
                        
                        foreach ($featuredProducts as $product):
                            $hasDiscount = isset($product['discount_price']) && $product['discount_price'] > 0;
                            $displayPrice = $hasDiscount ? $product['discount_price'] : $product['price'];
                            
                            if ($hasDiscount) {
                                $discountPercentage = round(($product['price'] - $product['discount_price']) / $product['price'] * 100);
                            }
                        ?>
                            <div class="product-card">
                                <?php if ($hasDiscount): ?>
                                    <div class="discount-badge"><?php echo $discountPercentage; ?>% OFF</div>
                                <?php endif; ?>
                                <div class="product-image">
                                    <img src="<?php echo $baseUrl; ?>assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <div class="product-price">
                                        <?php if ($hasDiscount): ?>
                                            <span class="original-price">₹<?php echo htmlspecialchars($product['price']); ?></span>
                                        <?php endif; ?>
                                        <span>₹<?php echo htmlspecialchars($displayPrice); ?></span>
                                    </div>
                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">View</a>
                                        <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-shopping-cart"></i> Add
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                
                <!-- Related Categories -->
                <section class="section">
                    <h2 class="section-title">Related Categories</h2>
                    <div class="related-category-grid">
                        <?php
                        $relatedCategories = [];
                        
                        if (isset($conn) && $categoryId > 0) {
                            $result = $conn->query("SELECT id, name, image FROM categories WHERE id != $categoryId ORDER BY RAND() LIMIT 4");
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $relatedCategories[] = $row;
                                }
                            }
                        } else if (isset($conn)) {
                            $result = $conn->query("SELECT id, name, image FROM categories ORDER BY RAND() LIMIT 4");
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $relatedCategories[] = $row;
                                }
                            }
                        }
                        
                        // Fallback data if no categories from database
                        if (empty($relatedCategories)) {
                            $relatedCategories = [
                                ['id' => 1, 'name' => 'Seeds', 'image' => 'category1.jpg'],
                                ['id' => 2, 'name' => 'Tools', 'image' => 'category2.jpg'],
                                ['id' => 3, 'name' => 'Fertilizers', 'image' => 'category3.jpg'],
                                ['id' => 4, 'name' => 'Pesticides', 'image' => 'category4.jpg']
                            ];
                        }
                        
                        foreach ($relatedCategories as $category):
                        ?>
                            <a href="category.php?id=<?php echo $category['id']; ?>" class="related-category-card">
                                <img src="<?php echo $baseUrl; ?>assets/images/<?php echo htmlspecialchars($category['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>
    
    <!-- Include Footer -->
    <?php include 'footer.php'; ?>
    
    <script>
        // Additional JavaScript for enhanced interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle (if not already handled in header)
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const mainNavigation = document.querySelector('.main-navigation');
            
            if (mobileMenuToggle && mainNavigation) {
                mobileMenuToggle.addEventListener('click', function() {
                    mainNavigation.classList.toggle('active');
                });
            }
            
            // Set active navigation link
            const navLinks = document.querySelectorAll('.main-navigation a');
            navLinks.forEach(link => {
                if (link.getAttribute('href').includes('category.php')) {
                    link.classList.add('active');
                }
            });
            
            // Smooth scroll to top when clicking pagination
            document.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.getAttribute('href') === '#') {
                        e.preventDefault();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
