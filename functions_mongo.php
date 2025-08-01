<?php
/**
 * MongoDB-specific functions for AgriShop
 * These functions provide the same interface as MySQL functions for seamless integration
 */

require_once 'db_mongo_simple.php';

/**
 * Check if user is logged in (MongoDB version)
 */
function isLoggedInMongo() {
    return isset($_SESSION['user_id']);
}

/**
 * Fetch featured products for homepage (MongoDB version)
 */
function getFeaturedProductsMongo() {
    try {
        // Get all products and return first 8 (simplified for JSON approach)
        $products = executeMongoQuery('products', 'find', []);
        return array_slice($products, 0, 8);
    } catch (Exception $e) {
        error_log("Error fetching featured products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product by ID (MongoDB version)
 */
function getProductByIdMongo($id) {
    try {
        $product = executeMongoQuery('products', 'findOne', ['filter' => ['_id' => $id]]);
        
        if ($product) {
            // Get category information
            if (isset($product['category_id'])) {
                $category = executeMongoQuery('categories', 'findOne', ['filter' => ['_id' => $product['category_id']]]);
                if ($category) {
                    $product['category_name'] = $category['name'];
                }
            }
            
            return $product;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error fetching product by ID: " . $e->getMessage());
        return false;
    }
}

/**
 * Get related products (MongoDB version)
 */
function getRelatedProductsMongo($categoryId, $excludeId, $limit = 4) {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('products');
        $cursor = $collection->find(
            [
                'category_id' => $categoryId,
                '_id' => ['$ne' => new MongoDB\BSON\ObjectId($excludeId)]
            ],
            ['limit' => $limit, 'sort' => ['rating' => -1]]
        );
        
        return mongoCursorToArray($cursor);
    } catch (Exception $e) {
        error_log("Error fetching related products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product reviews (MongoDB version)
 */
function getProductReviewsMongo($productId) {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('product_reviews');
        $cursor = $collection->find(
            ['product_id' => new MongoDB\BSON\ObjectId($productId)],
            ['sort' => ['date_added' => -1]]
        );
        
        $reviews = [];
        foreach ($cursor as $review) {
            // Get user information
            $user_collection = $mongo_db->selectCollection('users');
            $user = $user_collection->findOne(['_id' => $review->user_id]);
            
            $review_array = (array) $review;
            $review_array['user_name'] = $user ? $user->name : 'Anonymous';
            $reviews[] = $review_array;
        }
        
        return $reviews;
    } catch (Exception $e) {
        error_log("Error fetching product reviews: " . $e->getMessage());
        return [];
    }
}

/**
 * Get products by category with filters (MongoDB version)
 */
function getProductsByCategoryMongo($categoryId = null, $minPrice = 0, $maxPrice = 10000, $sortBy = 'popularity') {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('products');
        
        // Build filter
        $filter = [
            'price' => ['$gte' => $minPrice, '$lte' => $maxPrice]
        ];
        
        if ($categoryId) {
            $filter['category_id'] = $categoryId;
        }
        
        // Build sort options
        $sort = [];
        switch ($sortBy) {
            case 'price_low':
                $sort = ['price' => 1];
                break;
            case 'price_high':
                $sort = ['price' => -1];
                break;
            case 'newest':
                $sort = ['created_at' => -1];
                break;
            case 'rating':
                $sort = ['rating' => -1];
                break;
            default:
                $sort = ['popularity' => -1];
        }
        
        $cursor = $collection->find($filter, ['sort' => $sort]);
        return mongoCursorToArray($cursor);
    } catch (Exception $e) {
        error_log("Error fetching products by category: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all categories (MongoDB version)
 */
function getAllCategoriesMongo() {
    try {
        $categories = executeMongoQuery('categories', 'find', []);
        // Sort by name
        usort($categories, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        return $categories;
    } catch (Exception $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Get category by ID (MongoDB version)
 */
function getCategoryByIdMongo($id) {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('categories');
        $category = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
        return $category ? (array) $category : false;
    } catch (Exception $e) {
        error_log("Error fetching category by ID: " . $e->getMessage());
        return false;
    }
}

/**
 * Authenticate user (MongoDB version)
 */
function authenticateUserMongo($email, $password) {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('users');
        $user = $collection->findOne(['email' => $email]);
        
        if ($user && password_verify($password, $user->password)) {
            return (array) $user;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error authenticating user: " . $e->getMessage());
        return false;
    }
}

/**
 * Register new user (MongoDB version)
 */
function registerUserMongo($name, $email, $password) {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('users');
        
        // Check if user already exists
        $existing_user = $collection->findOne(['email' => $email]);
        if ($existing_user) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Create new user
        $user = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $collection->insertOne($user);
        
        if ($result->getInsertedCount() > 0) {
            return ['success' => true, 'user_id' => $result->getInsertedId()];
        } else {
            return ['success' => false, 'message' => 'Failed to create user'];
        }
    } catch (Exception $e) {
        error_log("Error registering user: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Create order (MongoDB version)
 */
function createOrderMongo($userId, $totalAmount, $shippingAddress, $paymentMethod, $cartItems) {
    global $mongo_db;
    
    try {
        $orders_collection = $mongo_db->selectCollection('orders');
        $order_items_collection = $mongo_db->selectCollection('order_items');
        
        // Create order
        $order = [
            'user_id' => new MongoDB\BSON\ObjectId($userId),
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'shipping_address' => $shippingAddress,
            'payment_method' => $paymentMethod,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $order_result = $orders_collection->insertOne($order);
        
        if ($order_result->getInsertedCount() > 0) {
            $order_id = $order_result->getInsertedId();
            
            // Create order items
            foreach ($cartItems as $product_id => $quantity) {
                $product = getProductByIdMongo($product_id);
                if ($product) {
                    $order_item = [
                        'order_id' => $order_id,
                        'product_id' => new MongoDB\BSON\ObjectId($product_id),
                        'quantity' => $quantity,
                        'price' => $product['price']
                    ];
                    
                    $order_items_collection->insertOne($order_item);
                }
            }
            
            return ['success' => true, 'order_id' => $order_id];
        } else {
            return ['success' => false, 'message' => 'Failed to create order'];
        }
    } catch (Exception $e) {
        error_log("Error creating order: " . $e->getMessage());
        return ['success' => false, 'message' => 'Order creation failed'];
    }
}

/**
 * Get user orders (MongoDB version)
 */
function getUserOrdersMongo($userId) {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('orders');
        $cursor = $collection->find(
            ['user_id' => new MongoDB\BSON\ObjectId($userId)],
            ['sort' => ['created_at' => -1]]
        );
        
        return mongoCursorToArray($cursor);
    } catch (Exception $e) {
        error_log("Error fetching user orders: " . $e->getMessage());
        return [];
    }
}

/**
 * Add product review (MongoDB version)
 */
function addProductReviewMongo($productId, $userId, $rating, $review) {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('product_reviews');
        
        $review_doc = [
            'product_id' => new MongoDB\BSON\ObjectId($productId),
            'user_id' => new MongoDB\BSON\ObjectId($userId),
            'rating' => $rating,
            'review' => $review,
            'date_added' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $collection->insertOne($review_doc);
        
        if ($result->getInsertedCount() > 0) {
            // Update product rating
            updateProductRatingMongo($productId);
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to add review'];
        }
    } catch (Exception $e) {
        error_log("Error adding product review: " . $e->getMessage());
        return ['success' => false, 'message' => 'Review addition failed'];
    }
}

/**
 * Update product rating (MongoDB version)
 */
function updateProductRatingMongo($productId) {
    global $mongo_db;
    
    try {
        $reviews_collection = $mongo_db->selectCollection('product_reviews');
        $products_collection = $mongo_db->selectCollection('products');
        
        // Calculate average rating
        $pipeline = [
            ['$match' => ['product_id' => new MongoDB\BSON\ObjectId($productId)]],
            ['$group' => ['_id' => null, 'avg_rating' => ['$avg' => '$rating']]]
        ];
        
        $result = $reviews_collection->aggregate($pipeline);
        $avg_rating = 0;
        
        foreach ($result as $doc) {
            $avg_rating = $doc->avg_rating;
            break;
        }
        
        // Update product rating
        $products_collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($productId)],
            ['$set' => ['rating' => round($avg_rating, 2)]]
        );
        
        return true;
    } catch (Exception $e) {
        error_log("Error updating product rating: " . $e->getMessage());
        return false;
    }
}

/**
 * Search products (MongoDB version)
 */
function searchProductsMongo($query, $limit = 20) {
    global $mongo_db;
    
    try {
        $collection = $mongo_db->selectCollection('products');
        
        $filter = [
            '$or' => [
                ['name' => ['$regex' => $query, '$options' => 'i']],
                ['description' => ['$regex' => $query, '$options' => 'i']],
                ['sku' => ['$regex' => $query, '$options' => 'i']]
            ]
        ];
        
        $cursor = $collection->find($filter, ['limit' => $limit, 'sort' => ['popularity' => -1]]);
        return mongoCursorToArray($cursor);
    } catch (Exception $e) {
        error_log("Error searching products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user by email (MongoDB version)
 */
function getUserByEmailMongo($email) {
    try {
        $users = executeMongoQuery('users', 'find', ['email' => $email]);
        if (!empty($users)) {
            $user = $users[0];
            // Normalize the name field for consistency
            if (isset($user['full_name']) && !isset($user['name'])) {
                $user['name'] = $user['full_name'];
            }
            return $user;
        }
        return null;
    } catch (Exception $e) {
        error_log("Error getting user by email: " . $e->getMessage());
        return null;
    }
}

/**
 * Create new user (MongoDB version)
 */
function createUserMongo($userData) {
    try {
        $result = executeMongoQuery('users', 'insertOne', $userData);
        return $result && isset($result['insertedId']);
    } catch (Exception $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}
?> 