<?php
/**
 * Simple MongoDB-like Setup for AgriShop
 * This creates a JSON-based document store that mimics MongoDB functionality
 */

echo "🍃 Simple MongoDB Setup for AgriShop\n";
echo "====================================\n\n";

// Create data directory for JSON files
$data_dir = 'data';
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
    echo "✅ Created data directory: $data_dir\n";
}

// Create collections (JSON files)
$collections = [
    'users.json',
    'categories.json', 
    'products.json',
    'admin.json',
    'orders.json',
    'order_items.json',
    'product_reviews.json'
];

foreach ($collections as $collection) {
    $file_path = $data_dir . '/' . $collection;
    if (!file_exists($file_path)) {
        file_put_contents($file_path, json_encode([], JSON_PRETTY_PRINT));
        echo "✅ Created collection: $collection\n";
    }
}

// Insert sample data
echo "\n📝 Inserting sample data...\n";

// Categories
$categories = [
    [
        '_id' => 'cat_' . uniqid(),
        'name' => 'Seeds',
        'description' => 'High-quality seeds for various crops',
        'image' => 'assets/images/seeds.jpg',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        '_id' => 'cat_' . uniqid(),
        'name' => 'Tools',
        'description' => 'Essential farming tools and equipment',
        'image' => 'assets/images/tools.jpg',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        '_id' => 'cat_' . uniqid(),
        'name' => 'Fertilizers',
        'description' => 'Organic and chemical fertilizers',
        'image' => 'assets/images/fertilizer.jpg',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        '_id' => 'cat_' . uniqid(),
        'name' => 'Animal Feed',
        'description' => 'Quality feed for livestock',
        'image' => 'assets/images/animal-feed.jpg',
        'created_at' => date('Y-m-d H:i:s')
    ]
];

file_put_contents($data_dir . '/categories.json', json_encode($categories, JSON_PRETTY_PRINT));
echo "✅ Added categories\n";

// Products
$products = [
    [
        '_id' => 'prod_' . uniqid(),
        'name' => 'Wheat Seeds Premium',
        'description' => 'High-yield wheat seeds for optimal harvest',
        'price' => 299.99,
        'category_id' => $categories[0]['_id'],
        'image' => 'assets/images/product1.jpg',
        'stock' => 100,
        'rating' => 4.5,
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        '_id' => 'prod_' . uniqid(),
        'name' => 'Garden Shovel',
        'description' => 'Durable steel garden shovel for all soil types',
        'price' => 45.99,
        'category_id' => $categories[1]['_id'],
        'image' => 'assets/images/product2.jpg',
        'stock' => 50,
        'rating' => 4.2,
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        '_id' => 'prod_' . uniqid(),
        'name' => 'Organic Fertilizer',
        'description' => 'Natural organic fertilizer for healthy plants',
        'price' => 89.99,
        'category_id' => $categories[2]['_id'],
        'image' => 'assets/images/product3.jpg',
        'stock' => 75,
        'rating' => 4.7,
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        '_id' => 'prod_' . uniqid(),
        'name' => 'Cattle Feed Mix',
        'description' => 'Nutritious feed mix for cattle and livestock',
        'price' => 199.99,
        'category_id' => $categories[3]['_id'],
        'image' => 'assets/images/product4.jpg',
        'stock' => 30,
        'rating' => 4.3,
        'created_at' => date('Y-m-d H:i:s')
    ]
];

file_put_contents($data_dir . '/products.json', json_encode($products, JSON_PRETTY_PRINT));
echo "✅ Added products\n";

// Admin user
$admin = [
    [
        '_id' => 'admin_' . uniqid(),
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'email' => 'admin@agrishop.com',
        'role' => 'admin',
        'created_at' => date('Y-m-d H:i:s')
    ]
];

file_put_contents($data_dir . '/admin.json', json_encode($admin, JSON_PRETTY_PRINT));
echo "✅ Added admin user\n";

// Sample users
$users = [
    [
        '_id' => 'user_' . uniqid(),
        'username' => 'farmer1',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'email' => 'farmer1@example.com',
        'full_name' => 'John Farmer',
        'phone' => '+1234567890',
        'address' => '123 Farm Road, Countryside',
        'role' => 'user',
        'created_at' => date('Y-m-d H:i:s')
    ]
];

file_put_contents($data_dir . '/users.json', json_encode($users, JSON_PRETTY_PRINT));
echo "✅ Added sample users\n";

echo "\n🎉 MongoDB-like setup completed!\n";
echo "===============================\n";
echo "Collections created in data/ directory:\n";
foreach ($collections as $collection) {
    echo "- $collection\n";
}
echo "\nSample data has been inserted.\n";
echo "Admin credentials: admin / admin123\n";
?> 