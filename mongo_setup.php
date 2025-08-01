<?php
/**
 * MongoDB Setup Script for AgriShop
 * This script creates the necessary collections and sample data
 */

require_once 'db_mongo.php';

echo "Setting up MongoDB for AgriShop...\n";

try {
    // Create collections
    $collections = ['users', 'categories', 'products', 'admin', 'orders', 'order_items', 'product_reviews'];
    
    foreach ($collections as $collection_name) {
        $collection = $mongo_db->createCollection($collection_name);
        echo "✅ Created collection: $collection_name\n";
    }
    
    // Insert sample categories
    $categories = [
        [
            'name' => 'Seeds',
            'description' => 'High quality crop seeds for every season and region',
            'image' => 'seeds.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Farming Tools',
            'description' => 'Durable tools for efficient farming and garden maintenance',
            'image' => 'tools.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Fertilizers',
            'description' => 'Improve soil and plant health with our organic and chemical fertilizers',
            'image' => 'fertilizer.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Animal Feed',
            'description' => 'Balanced nutrition for livestock and poultry for better yield',
            'image' => 'animal-feed.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Pesticides',
            'description' => 'Effective pest control solutions for healthy crops',
            'image' => 'pesticides.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Irrigation',
            'description' => 'Water management systems for efficient farming',
            'image' => 'irrigation.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];
    
    $categories_collection = $mongo_db->selectCollection('categories');
    $result = $categories_collection->insertMany($categories);
    echo "✅ Inserted " . count($categories) . " categories\n";
    
    // Get category IDs for products
    $category_docs = $categories_collection->find();
    $category_map = [];
    foreach ($category_docs as $doc) {
        $category_map[$doc->name] = $doc->_id;
    }
    
    // Insert sample products
    $products = [
        [
            'name' => 'Organic Tomato Seeds',
            'description' => 'High-yield, disease-resistant premium seeds for home and commercial farming',
            'price' => 299.00,
            'discount_price' => 0,
            'stock' => 100,
            'image' => 'product1.jpg',
            'category_id' => $category_map['Seeds'],
            'sku' => 'TOM001',
            'rating' => 4.5,
            'popularity' => 100,
            'featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Premium Garden Tool Set',
            'description' => 'Complete set of essential gardening tools including spade, rake, and pruners',
            'price' => 1499.00,
            'discount_price' => 1299.00,
            'stock' => 50,
            'image' => 'product2.jpg',
            'category_id' => $category_map['Farming Tools'],
            'sku' => 'TOOL001',
            'rating' => 4.8,
            'popularity' => 95,
            'featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Organic Vermicompost',
            'description' => '100% natural, nutrient-rich soil enhancer for better plant growth',
            'price' => 599.00,
            'discount_price' => 0,
            'stock' => 200,
            'image' => 'product3.jpg',
            'category_id' => $category_map['Fertilizers'],
            'sku' => 'FERT001',
            'rating' => 4.6,
            'popularity' => 85,
            'featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Advanced Drip Irrigation Kit',
            'description' => 'Water-saving irrigation system suitable for all types of crops',
            'price' => 2999.00,
            'discount_price' => 0,
            'stock' => 25,
            'image' => 'product4.jpg',
            'category_id' => $category_map['Irrigation'],
            'sku' => 'IRR001',
            'rating' => 4.7,
            'popularity' => 90,
            'featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Monsoon Special Seeds Pack',
            'description' => 'Perfect for rainy season plantation with disease resistance',
            'price' => 999.00,
            'discount_price' => 699.00,
            'stock' => 75,
            'image' => 'season1.jpg',
            'category_id' => $category_map['Seeds'],
            'sku' => 'SEED001',
            'rating' => 4.4,
            'popularity' => 70,
            'featured' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Premium NPK Fertilizer',
            'description' => 'Balanced nutrition formula for all crops with slow-release technology',
            'price' => 800.00,
            'discount_price' => 599.00,
            'stock' => 150,
            'image' => 'season2.jpg',
            'category_id' => $category_map['Fertilizers'],
            'sku' => 'FERT002',
            'rating' => 4.3,
            'popularity' => 65,
            'featured' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Organic Pest Control Spray',
            'description' => 'Chemical-free pest management solution for organic farming',
            'price' => 1200.00,
            'discount_price' => 720.00,
            'stock' => 80,
            'image' => 'season3.jpg',
            'category_id' => $category_map['Pesticides'],
            'sku' => 'PEST001',
            'rating' => 4.2,
            'popularity' => 60,
            'featured' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Solar Water Pump',
            'description' => 'Energy-efficient irrigation solution powered by solar energy',
            'price' => 5000.00,
            'discount_price' => 4000.00,
            'stock' => 15,
            'image' => 'season4.jpg',
            'category_id' => $category_map['Irrigation'],
            'sku' => 'PUMP001',
            'rating' => 4.9,
            'popularity' => 75,
            'featured' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];
    
    $products_collection = $mongo_db->selectCollection('products');
    $result = $products_collection->insertMany($products);
    echo "✅ Inserted " . count($products) . " products\n";
    
    // Insert admin user (password: admin123)
    $admin = [
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'email' => 'admin@agricart.com',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $admin_collection = $mongo_db->selectCollection('admin');
    $result = $admin_collection->insertOne($admin);
    echo "✅ Created admin user\n";
    
    // Create indexes for better performance
    $products_collection->createIndex(['category_id' => 1]);
    $products_collection->createIndex(['featured' => 1]);
    $products_collection->createIndex(['price' => 1]);
    $products_collection->createIndex(['sku' => 1], ['unique' => true]);
    
    $orders_collection = $mongo_db->selectCollection('orders');
    $orders_collection->createIndex(['user_id' => 1]);
    $orders_collection->createIndex(['status' => 1]);
    
    $reviews_collection = $mongo_db->selectCollection('product_reviews');
    $reviews_collection->createIndex(['product_id' => 1]);
    $reviews_collection->createIndex(['user_id' => 1]);
    
    $users_collection = $mongo_db->selectCollection('users');
    $users_collection->createIndex(['email' => 1], ['unique' => true]);
    
    echo "✅ Created database indexes\n";
    echo "\n🎉 MongoDB setup completed successfully!\n";
    echo "You can now use MongoDB with your AgriShop application.\n";
    
} catch (Exception $e) {
    echo "❌ Error during setup: " . $e->getMessage() . "\n";
}
?> 