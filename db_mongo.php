<?php
/**
 * MongoDB Database Connection Configuration
 * 
 * This file handles the connection to MongoDB database.
 * It provides the same interface as the MySQL connection for seamless integration.
 */

// MongoDB credentials - consider moving these to a separate config file for better security
$mongo_host = "localhost";
$mongo_port = 27017;
$mongo_database = "agri_ecommerce";
$mongo_username = ""; // Leave empty for local development
$mongo_password = ""; // Leave empty for local development

// Error reporting settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// MongoDB connection
try {
    // Create MongoDB connection string
    if (!empty($mongo_username) && !empty($mongo_password)) {
        $mongo_uri = "mongodb://{$mongo_username}:{$mongo_password}@{$mongo_host}:{$mongo_port}/{$mongo_database}";
    } else {
        $mongo_uri = "mongodb://{$mongo_host}:{$mongo_port}/{$mongo_database}";
    }
    
    // Create MongoDB client
    $mongo_client = new MongoDB\Client($mongo_uri);
    
    // Select database
    $mongo_db = $mongo_client->selectDatabase($mongo_database);
    
    // Test connection
    $mongo_client->listDatabases();
    
} catch (Exception $e) {
    // Log the error to a file (recommended for production)
    error_log("MongoDB connection error: " . $e->getMessage(), 0);
    
    // Display user-friendly error (customize as needed)
    die("We're experiencing technical difficulties. Please try again later.");
}

/**
 * Helper function for MongoDB query execution (similar to MySQL executeQuery)
 * 
 * @param string $collection Collection name
 * @param array $filter Filter criteria
 * @param array $options Query options
 * @return MongoDB\Driver\Cursor|false Returns the cursor or false on failure
 */
function executeMongoQuery($collection, $filter = [], $options = []) {
    global $mongo_db;
    
    try {
        $mongo_collection = $mongo_db->selectCollection($collection);
        $cursor = $mongo_collection->find($filter, $options);
        return $cursor;
    } catch (Exception $e) {
        error_log("MongoDB query error: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper function for MongoDB insert operations
 * 
 * @param string $collection Collection name
 * @param array $document Document to insert
 * @return MongoDB\InsertOneResult|false Returns the result or false on failure
 */
function executeMongoInsert($collection, $document) {
    global $mongo_db;
    
    try {
        $mongo_collection = $mongo_db->selectCollection($collection);
        $result = $mongo_collection->insertOne($document);
        return $result;
    } catch (Exception $e) {
        error_log("MongoDB insert error: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper function for MongoDB update operations
 * 
 * @param string $collection Collection name
 * @param array $filter Filter criteria
 * @param array $update Update operations
 * @return MongoDB\UpdateResult|false Returns the result or false on failure
 */
function executeMongoUpdate($collection, $filter, $update) {
    global $mongo_db;
    
    try {
        $mongo_collection = $mongo_db->selectCollection($collection);
        $result = $mongo_collection->updateOne($filter, $update);
        return $result;
    } catch (Exception $e) {
        error_log("MongoDB update error: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper function for MongoDB delete operations
 * 
 * @param string $collection Collection name
 * @param array $filter Filter criteria
 * @return MongoDB\DeleteResult|false Returns the result or false on failure
 */
function executeMongoDelete($collection, $filter) {
    global $mongo_db;
    
    try {
        $mongo_collection = $mongo_db->selectCollection($collection);
        $result = $mongo_collection->deleteOne($filter);
        return $result;
    } catch (Exception $e) {
        error_log("MongoDB delete error: " . $e->getMessage());
        return false;
    }
}

/**
 * Convert MongoDB cursor to array (similar to MySQL fetch_assoc)
 * 
 * @param MongoDB\Driver\Cursor $cursor MongoDB cursor
 * @return array Array of documents
 */
function mongoCursorToArray($cursor) {
    $result = [];
    foreach ($cursor as $document) {
        $result[] = (array) $document;
    }
    return $result;
}

/**
 * Get single document from MongoDB (similar to MySQL fetch_assoc)
 * 
 * @param string $collection Collection name
 * @param array $filter Filter criteria
 * @return array|false Document as array or false if not found
 */
function getMongoDocument($collection, $filter) {
    global $mongo_db;
    
    try {
        $mongo_collection = $mongo_db->selectCollection($collection);
        $document = $mongo_collection->findOne($filter);
        return $document ? (array) $document : false;
    } catch (Exception $e) {
        error_log("MongoDB findOne error: " . $e->getMessage());
        return false;
    }
}

/**
 * Count documents in MongoDB collection
 * 
 * @param string $collection Collection name
 * @param array $filter Filter criteria
 * @return int Number of documents
 */
function countMongoDocuments($collection, $filter = []) {
    global $mongo_db;
    
    try {
        $mongo_collection = $mongo_db->selectCollection($collection);
        return $mongo_collection->countDocuments($filter);
    } catch (Exception $e) {
        error_log("MongoDB count error: " . $e->getMessage());
        return 0;
    }
}

// Enable this for automatic cleanup when script ends (optional)
// register_shutdown_function(function() {
//     global $mongo_client;
//     if ($mongo_client) {
//         $mongo_client->close();
//     }
// });
?> 