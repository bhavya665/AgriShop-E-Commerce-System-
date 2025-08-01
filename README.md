# AgriShop - Agricultural E-Commerce Platform

A modern, responsive e-commerce platform specifically designed for agricultural products, tools, and resources. Built with PHP and supporting both MySQL and MongoDB databases.

## 🌟 Features

### User Features
- **User Registration & Authentication** - Secure user accounts with password hashing
- **Product Browsing** - Browse products by categories with advanced filtering
- **Shopping Cart** - Add/remove items with quantity management
- **Checkout System** - Complete order processing with multiple payment options
- **Product Reviews** - User-generated reviews and ratings
- **Responsive Design** - Mobile-friendly interface
- **Search Functionality** - Find products quickly
- **User Profile** - Manage personal information and order history

### Admin Features
- **Admin Dashboard** - Comprehensive admin panel
- **Product Management** - Add, edit, delete products
- **Order Management** - Track and manage orders
- **User Management** - View and manage user accounts
- **Category Management** - Organize products by categories

### Technical Features
- **Dual Database Support** - Works with both MySQL and MongoDB
- **Security Features** - CSRF protection, input sanitization, SQL injection prevention
- **Performance Optimized** - Database indexing, efficient queries
- **Error Handling** - Comprehensive error logging and user-friendly messages
- **Session Management** - Secure session handling

## 🗄️ Database Support

### MySQL
- Traditional relational database
- ACID compliance
- Structured data with relationships
- Better for complex queries and transactions

### MongoDB
- NoSQL document database
- Flexible schema
- Better for scalability and rapid development
- Native JSON support

## 📋 Requirements

### PHP Extensions
- PDO (for MySQL)
- MongoDB PHP Driver (for MongoDB)
- GD Library (for image processing)
- OpenSSL (for security)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd agriShop
```

### 2. Configure Database

#### For MySQL:
1. Create a MySQL database named `agri_ecommerce`
2. Import the database schema:
```bash
mysql -u root -p agri_ecommerce < database_setup.sql
```

#### For MongoDB:
1. Install MongoDB PHP Driver:
2. Run the MongoDB setup script:

### 3. Configure Application

1. Edit `config.php`:

### 4. Create Required Directories
```bash
mkdir assets
mkdir assets/images
mkdir logs
chmod 755 assets
chmod 755 logs
```

### 5. Set Permissions
```bash
chmod 644 *.php
chmod 755 assets/
chmod 755 logs/
```

## 🔄 Switching Between Databases

### From MySQL to MongoDB:
1. Edit `config.php`:
```php
$database_type = 'mongodb';
```

2. Run MongoDB setup:
```bash
php mongo_setup.php
```

### From MongoDB to MySQL:
1. Edit `config.php`:
```php
$database_type = 'mysql';
```

2. Import MySQL schema:
```bash
mysql -u root -p agri_ecommerce < database_setup.sql
```

## 📁 Project Structure

```
agriShop/
├── config.php              # Main configuration file
├── db.php                  # MySQL database connection
├── db_mongo.php            # MongoDB database connection
├── functions.php           # MySQL-specific functions
├── functions_mongo.php     # MongoDB-specific functions
├── database_setup.sql      # MySQL database schema
├── mongo_setup.php         # MongoDB setup script
├── home.php               # Homepage
├── login.php              # User login
├── register.php           # User registration
├── product.php            # Product details
├── category.php           # Category browsing
├── cart.php               # Shopping cart
├── checkout.php           # Checkout process
├── adminlogin.php         # Admin login
├── dashboard.php          # Admin dashboard
├── header.php             # Site header
├── footer.php             # Site footer
├── assets/                # Static assets
│   └── images/            # Product images
└── logs/                  # Application logs
```

## 🔧 Configuration Options

### Database Configuration
- Switch between MySQL and MongoDB
- Configure connection parameters
- Set up authentication

### Application Settings
- Site name and description
- Base URL configuration
- File upload settings
- Session management
- Security settings

### Performance Settings
- Cache configuration
- Pagination settings
- Error reporting options

## 🎨 Customization

### Styling
- Modify CSS in individual files or create a separate stylesheet
- Update color schemes in CSS variables
- Customize responsive breakpoints

### Functionality
- Add new product categories
- Implement additional payment methods
- Extend admin features
- Add new user roles

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in config.php
   - Ensure database server is running
   - Verify database exists

2. **Image Upload Issues**
   - Check directory permissions
   - Verify upload path in config.php
   - Ensure GD library is installed

3. **Session Issues**
   - Check session configuration
   - Verify session directory permissions
   - Clear browser cookies

4. **MongoDB Connection Issues**
   - Ensure MongoDB PHP driver is installed
   - Check MongoDB service is running
   - Verify connection string format

### Error Logs
- Check `logs/error.log` for detailed error information
- Enable error display in development mode
- Monitor server error logs

## 📝 API Documentation

### Database Functions

#### MongoDB Functions
- `getFeaturedProductsMongo()` - Get featured products
- `getProductByIdMongo($id)` - Get product by ID
- `getRelatedProductsMongo($categoryId, $excludeId, $limit)` - Get related products

**Note**: This application is designed for educational and development purposes. For production use, ensure proper security measures, SSL certificates, and regular backups are implemented. 
