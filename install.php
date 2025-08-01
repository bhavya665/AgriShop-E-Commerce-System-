<?php
/**
 * AgriShop Installation Script
 * This script helps set up the application with either MySQL or MongoDB
 */

echo "🌱 AgriShop Installation Script\n";
echo "================================\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "❌ Error: PHP 7.4 or higher is required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}

echo "✅ PHP version: " . PHP_VERSION . "\n";

// Check required extensions
$required_extensions = ['pdo', 'json', 'openssl'];
$optional_extensions = ['gd'];
$missing_extensions = [];
$missing_optional = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

foreach ($optional_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_optional[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "❌ Missing required PHP extensions: " . implode(', ', $missing_extensions) . "\n";
    echo "Please install these extensions and try again.\n";
    exit(1);
}

echo "✅ All required PHP extensions are installed\n";

if (!empty($missing_optional)) {
    echo "⚠️  Missing optional PHP extensions: " . implode(', ', $missing_optional) . "\n";
    echo "   These are not required for basic functionality but may limit some features.\n";
}

// Create necessary directories
$directories = ['assets', 'assets/images', 'logs'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Created directory: $dir\n";
        } else {
            echo "❌ Failed to create directory: $dir\n";
            exit(1);
        }
    } else {
        echo "✅ Directory exists: $dir\n";
    }
}

// Ask user for database preference
echo "\n📊 Database Configuration\n";
echo "Choose your database system:\n";
echo "1. MySQL (Traditional relational database)\n";
echo "2. MongoDB (NoSQL document database)\n";

$choice = custom_readline("Enter your choice (1 or 2): ");

if ($choice == '1') {
    echo "\n🐬 MySQL Setup\n";
    echo "=============\n";
    
    $host = custom_readline("MySQL Host (default: localhost): ") ?: 'localhost';
    $username = custom_readline("MySQL Username (default: root): ") ?: 'root';
    $password = custom_readline("MySQL Password: ");
    $database = custom_readline("Database Name (default: agri_ecommerce): ") ?: 'agri_ecommerce';
    
    // Test MySQL connection
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ MySQL connection successful\n";
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        echo "✅ Database '$database' created/verified\n";
        
        // Import schema
        if (file_exists('database_setup.sql')) {
            $sql = file_get_contents('database_setup.sql');
            $sql = str_replace('agri_ecommerce', $database, $sql);
            
            $pdo->exec("USE `$database`");
            $pdo->exec($sql);
            echo "✅ Database schema imported successfully\n";
        } else {
            echo "❌ database_setup.sql not found\n";
            exit(1);
        }
        
        // Update config
        updateConfig('mysql', $host, $username, $password, $database);
        
    } catch (PDOException $e) {
        echo "❌ MySQL connection failed: " . $e->getMessage() . "\n";
        exit(1);
    }
    
} elseif ($choice == '2') {
    echo "\n🍃 MongoDB Setup\n";
    echo "===============\n";
    
    // Check if MongoDB extension is installed
    if (!extension_loaded('mongodb')) {
        echo "❌ MongoDB PHP extension is not installed\n";
        echo "Please install it using:\n";
        echo "  Ubuntu/Debian: sudo apt-get install php-mongodb\n";
        echo "  CentOS/RHEL: sudo yum install php-mongodb\n";
        echo "  Windows: composer require mongodb/mongodb\n";
        exit(1);
    }
    
    echo "✅ MongoDB PHP extension is installed\n";
    
    $host = custom_readline("MongoDB Host (default: localhost): ") ?: 'localhost';
    $port = custom_readline("MongoDB Port (default: 27017): ") ?: '27017';
    $database = custom_readline("Database Name (default: agri_ecommerce): ") ?: 'agri_ecommerce';
    
    // Test MongoDB connection
    try {
        $mongo_uri = "mongodb://$host:$port";
        $mongo_client = new MongoDB\Client($mongo_uri);
        $mongo_client->listDatabases();
        echo "✅ MongoDB connection successful\n";
        
        // Run MongoDB setup
        if (file_exists('mongo_setup.php')) {
            // Temporarily update config for setup
            updateConfig('mongodb', $host, '', '', $database, $port);
            
            // Run setup script
            ob_start();
            include 'mongo_setup.php';
            $output = ob_get_clean();
            echo $output;
        } else {
            echo "❌ mongo_setup.php not found\n";
            exit(1);
        }
        
    } catch (Exception $e) {
        echo "❌ MongoDB connection failed: " . $e->getMessage() . "\n";
        exit(1);
    }
    
} else {
    echo "❌ Invalid choice. Please run the script again.\n";
    exit(1);
}

// Create .htaccess file for security
$htaccess_content = "Options -Indexes\nRewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php [QSA,L]";
file_put_contents('.htaccess', $htaccess_content);
echo "✅ Created .htaccess file\n";

// Create sample images directory structure
$sample_images = [
    'assets/images/seeds.jpg',
    'assets/images/tools.jpg',
    'assets/images/fertilizer.jpg',
    'assets/images/animal-feed.jpg',
    'assets/images/pesticides.jpg',
    'assets/images/irrigation.jpg',
    'assets/images/product1.jpg',
    'assets/images/product2.jpg',
    'assets/images/product3.jpg',
    'assets/images/product4.jpg'
];

echo "\n📸 Creating sample image placeholders...\n";
foreach ($sample_images as $image_path) {
    if (!file_exists($image_path)) {
        // Create a simple placeholder image
        $image = imagecreate(300, 200);
        $bg_color = imagecolorallocate($image, 240, 240, 240);
        $text_color = imagecolorallocate($image, 100, 100, 100);
        $text = basename($image_path, '.jpg');
        imagestring($image, 5, 10, 90, $text, $text_color);
        imagejpeg($image, $image_path);
        imagedestroy($image);
        echo "✅ Created placeholder: $image_path\n";
    }
}

echo "\n🎉 Installation completed successfully!\n";
echo "=====================================\n";
echo "Your AgriShop application is now ready to use.\n\n";

echo "📋 Next Steps:\n";
echo "1. Start your web server\n";
echo "2. Navigate to your application URL\n";
echo "3. Register a new user account\n";
echo "4. Access admin panel at: /adminlogin.php\n";
echo "   Username: admin\n";
echo "   Password: admin123\n\n";

echo "🔧 Configuration:\n";
echo "- Edit config.php to customize settings\n";
echo "- Update base_url in config.php\n";
echo "- Add your own product images to assets/images/\n\n";

echo "📚 Documentation:\n";
echo "- Read README.md for detailed information\n";
echo "- Check logs/ directory for error logs\n\n";

echo "🛡️ Security Notes:\n";
echo "- Change default admin password\n";
echo "- Set up SSL certificate for production\n";
echo "- Configure proper file permissions\n";
echo "- Regular backups recommended\n\n";

function updateConfig($db_type, $host, $username, $password, $database, $port = null) {
    $config_content = file_get_contents('config.php');
    
    // Update database type
    $config_content = preg_replace(
        '/\$database_type\s*=\s*[\'"]mysql[\'"];/',
        "\$database_type = '$db_type';",
        $config_content
    );
    
    // Update database configuration
    if ($db_type == 'mysql') {
        $config_content = preg_replace(
            '/\'host\'\s*=>\s*[\'"]localhost[\'"]/',
            "'host' => '$host'",
            $config_content
        );
        $config_content = preg_replace(
            '/\'username\'\s*=>\s*[\'"]root[\'"]/',
            "'username' => '$username'",
            $config_content
        );
        $config_content = preg_replace(
            '/\'password\'\s*=>\s*[\'"]\s*[\'"]/',
            "'password' => '$password'",
            $config_content
        );
        $config_content = preg_replace(
            '/\'database\'\s*=>\s*[\'"]agri_ecommerce[\'"]/',
            "'database' => '$database'",
            $config_content
        );
    } else {
        $config_content = preg_replace(
            '/\'host\'\s*=>\s*[\'"]localhost[\'"]/',
            "'host' => '$host'",
            $config_content
        );
        $config_content = preg_replace(
            '/\'port\'\s*=>\s*27017/',
            "'port' => $port",
            $config_content
        );
        $config_content = preg_replace(
            '/\'database\'\s*=>\s*[\'"]agri_ecommerce[\'"]/',
            "'database' => '$database'",
            $config_content
        );
    }
    
    file_put_contents('config.php', $config_content);
    echo "✅ Updated config.php with database settings\n";
}

function custom_readline($prompt) {
    echo $prompt;
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return trim($line);
}
?> 