<?php
/**
 * Configuration File for AgriShop
 * This file controls which database system to use and other application settings
 */

// Database Configuration
// Set to "mysql" or "mongodb"
$database_type = "mongodb"; // Using MongoDB as requested

// Site Configuration
$site_name = "AgriShop";
$base_url = "http://localhost:8000/";

// MySQL Configuration (used when $database_type = "mysql")
$mysql_host = "localhost";
$mysql_username = "root";
$mysql_password = "";
$mysql_database = "agri_ecommerce";

// MongoDB Configuration (used when $database_type = "mongodb")
$mongo_connection_string = "mongodb://localhost:27017";
$mongo_database = "agri_ecommerce";

// Helper functions
function isUsingMongoDB() {
    global $database_type;
    return $database_type === "mongodb";
}

function getDatabaseConnection() {
    global $database_type;
    if (isUsingMongoDB()) {
        require_once "db_mongo_simple.php";
        return getMongoConnection();
    } else {
        require_once "db.php";
        return getMySQLConnection();
    }
}

function loadDatabaseFunctions() {
    global $database_type;
    if (isUsingMongoDB()) {
        require_once "functions_mongo.php";
    } else {
        require_once "functions.php";
    }
}

// Load the appropriate database functions
loadDatabaseFunctions();
?>