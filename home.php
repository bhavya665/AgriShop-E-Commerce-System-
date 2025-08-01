<?php
// Include database connection
session_start();
require_once('config.php');


// Set page variables
$siteName = "AgriCart";
$pageTitle = "Agricultural E-Commerce Marketplace";
$currentYear = date("Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($siteName); ?> - <?php echo htmlspecialchars($pageTitle); ?></title>
  <!-- Add favicon -->
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Custom CSS -->
  
  <style>
    /* Base styles (these could be moved to an external CSS file) */
    :root {
      --primary-color: #28a745;
      --secondary-color: #218838;
      --accent-color: #f97316;
      --light-color: #f8f9fa;
      --dark-color: #333;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: #444;
      background-color: #f4f4f4;
    }
    
    a {
      text-decoration: none;
      color: var(--primary-color);
    }
    
    img {
      max-width: 100%;
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    .section {
      padding: 60px 0;
    }
    
    .section-title {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 1rem;
      color: var(--dark-color);
    }
    
    .section-description {
      text-align: center;
      max-width: 800px;
      margin: 0 auto 2rem;
      color: #666;
    }
    
    /* Hero Banner */
    .hero-banner {
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/farm-banner.jpg');
      background-size: cover;
      background-position: center;
      color: white;
      text-align: center;
      padding: 100px 20px;
    }
    
    .hero-banner h1 {
      font-size: 3rem;
      margin-bottom: 1rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .hero-banner p {
      font-size: 1.25rem;
      max-width: 800px;
      margin: 0 auto 2rem;
    }
    
    /* Buttons */
    .btn {
      display: inline-block;
      background: var(--primary-color);
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .btn:hover {
      background: var(--secondary-color);
      transform: translateY(-2px);
    }
    
    .btn-outline {
      background: transparent;
      border: 2px solid white;
    }
    
    .btn-outline:hover {
      background: rgba(255, 255, 255, 0.1);
    }
    
    .cta-buttons {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
    }
    
    /* Cards */
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }
    
    .card {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
    }
    
    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 15px rgba(0,0,0,0.2);
    }
    
    .card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    
    .card-content {
      padding: 20px;
    }
    
    .card-title {
      font-size: 1.25rem;
      margin-bottom: 10px;
    }
    
    .card-text {
      color: #666;
      margin-bottom: 15px;
    }
    
    .price {
      font-weight: bold;
      color: var(--accent-color);
      font-size: 1.2rem;
      display: block;
      margin: 10px 0;
    }
    
    .discount-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: var(--accent-color);
      color: white;
      padding: 5px 10px;
      border-radius: 3px;
      font-weight: bold;
    }
    
    /* Features */
    .features {
      background-color: var(--light-color);
    }
    
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
    }
    
    .feature-item {
      text-align: center;
      padding: 20px;
    }
    
    .feature-icon {
      font-size: 2.5rem;
      color: var(--primary-color);
      margin-bottom: 1rem;
    }
    
    /* Testimonials */
    .testimonial {
      background: white;
      border-radius: 8px;
      padding: 25px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .testimonial-text {
      font-style: italic;
      margin-bottom: 15px;
      position: relative;
      padding-left: 20px;
    }
    
    .testimonial-text::before {
      content: '\201C';
      font-size: 60px;
      position: absolute;
      left: -10px;
      top: -20px;
      color: #e0e0e0;
    }
    
    .testimonial-author {
      display: flex;
      align-items: center;
    }
    
    .testimonial-author img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      margin-right: 15px;
      object-fit: cover;
    }
    
    /* Newsletter */
    .newsletter {
      background-color: var(--primary-color);
      color: white;
      padding: 60px 0;
      text-align: center;
    }
    
    .newsletter-form {
      display: flex;
      max-width: 500px;
      margin: 20px auto 0;
    }
    
    .newsletter-form input {
      flex: 1;
      padding: 12px 15px;
      border: none;
      border-radius: 5px 0 0 5px;
      font-size: 1rem;
    }
    
    .newsletter-form button {
      background: var(--accent-color);
      color: white;
      border: none;
      padding: 0 20px;
      border-radius: 0 5px 5px 0;
      cursor: pointer;
      transition: background 0.3s;
    }
    
    .newsletter-form button:hover {
      background: #e86504;
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
      .hero-banner h1 {
        font-size: 2rem;
      }
      
      .hero-banner p {
        font-size: 1rem;
      }
      
      .section-title {
        font-size: 1.5rem;
      }
      
      .newsletter-form {
        flex-direction: column;
      }
      
      .newsletter-form input,
      .newsletter-form button {
        width: 100%;
        border-radius: 5px;
        margin-bottom: 10px;
      }
      
      .cta-buttons {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Hero Banner -->
<section class="hero-banner">
  <div class="container">
    <h1><?php echo htmlspecialchars($siteName); ?> - Grow More. Save More.</h1>
    <p>Your one-stop online marketplace for high-quality agricultural products, tools, and resources for modern farming.</p>
    <div class="cta-buttons">
      <a href="products.php" class="btn">Shop Now</a>
      <a href="about.php" class="btn btn-outline">Learn More</a>
    </div>
  </div>
</section>

<!-- Categories Section -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Popular Categories</h2>
    <p class="section-description">Discover our extensive range of farming essentials that cater to all your agricultural needs</p>
    
    <div class="card-grid">
      <?php
      // If you have a categories table, you can fetch from there
      $categories = [
        ['id' => 1, 'name' => 'Seeds', 'image' => 'seeds.jpg', 'description' => 'High quality crop seeds for every season and region.'],
        ['id' => 2, 'name' => 'Farming Tools', 'image' => 'tools.jpg', 'description' => 'Durable tools for efficient farming and garden maintenance.'],
        ['id' => 3, 'name' => 'Fertilizers', 'image' => 'fertilizer.jpg', 'description' => 'Improve soil and plant health with our organic and chemical fertilizers.'],
        ['id' => 4, 'name' => 'Animal Feed', 'image' => 'animal-feed.jpg', 'description' => 'Balanced nutrition for livestock and poultry for better yield.']
      ];
      
      foreach ($categories as $category):
      ?>
      <div class="card">
        <img src="assets/images/<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
        <div class="card-content">
          <h3 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h3>
          <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
          <a href="category.php?id=<?php echo $category['id']; ?>" class="btn">Explore</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="section features">
  <div class="container">
    <h2 class="section-title">Why Choose <?php echo htmlspecialchars($siteName); ?></h2>
    <p class="section-description">We provide value and service beyond compare to help your agricultural business thrive</p>
    
    <div class="feature-grid">
      <div class="feature-item">
        <div class="feature-icon">
          <i class="fas fa-truck"></i>
        </div>
        <h3>Fast Delivery</h3>
        <p>We deliver your orders promptly to your doorstep across the country.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">
          <i class="fas fa-leaf"></i>
        </div>
        <h3>Quality Assured</h3>
        <p>All our products undergo strict quality checks to ensure the best results.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">
          <i class="fas fa-headset"></i>
        </div>
        <h3>Expert Support</h3>
        <p>Our team of agricultural experts is always ready to assist you.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">
          <i class="fas fa-rupee-sign"></i>
        </div>
        <h3>Best Prices</h3>
        <p>Get competitive prices with regular discounts and seasonal offers.</p>
      </div>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Featured Products</h2>
    <p class="section-description">Our most popular and top-rated agricultural products to boost your farming productivity</p>
    
    <div class="card-grid">
      <?php
      // Try to fetch featured products from database
      $featured_products = [];
      
    
      
      // If no products from database, use fallback data
      if (empty($featured_products)) {
        $featured_products = [
          ['id' => 1, 'name' => 'Organic Tomato Seeds', 'description' => 'High-yield, disease-resistant premium seeds.', 'price' => 299, 'image' => 'product1.jpg'],
          ['id' => 2, 'name' => 'Premium Garden Tool Set', 'description' => 'Complete set of essential gardening tools.', 'price' => 1499, 'image' => 'product2.jpg'],
          ['id' => 3, 'name' => 'Organic Vermicompost', 'description' => '100% natural, nutrient-rich soil enhancer.', 'price' => 599, 'image' => 'product3.jpg'],
          ['id' => 4, 'name' => 'Advanced Drip Irrigation Kit', 'description' => 'Water-saving irrigation system for all crops.', 'price' => 2999, 'image' => 'product4.jpg']
        ];
      }
      
      foreach ($featured_products as $product):
        $has_discount = isset($product['discount_price']) && $product['discount_price'] > 0;
        $display_price = $has_discount ? $product['discount_price'] : $product['price'];
      ?>
      <div class="card">
        <?php if ($has_discount): 
          $discount_percentage = round(($product['price'] - $product['discount_price']) / $product['price'] * 100);
        ?>
        <div class="discount-badge"><?php echo $discount_percentage; ?>% OFF</div>
        <?php endif; ?>
        
        <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <div class="card-content">
          <h3 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
          <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
          
          <?php if ($has_discount): ?>
          <div class="price">
            <span style="text-decoration: line-through; color: #888; margin-right: 10px;">₹<?php echo htmlspecialchars($product['price']); ?></span>
            <span>₹<?php echo htmlspecialchars($product['discount_price']); ?></span>
          </div>
          <?php else: ?>
          <span class="price">₹<?php echo htmlspecialchars($product['price']); ?></span>
          <?php endif; ?>
          
          <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section class="section">
  <div class="container">
    <h2 class="section-title">What Our Customers Say</h2>
    <p class="section-description">Hear from farmers across the country who have transformed their agriculture with our products</p>
    
    <div class="card-grid">
      <div class="testimonial">
        <p class="testimonial-text">The quality of seeds I purchased from <?php echo htmlspecialchars($siteName); ?> was exceptional. My crop yield increased by 30% compared to last season.</p>
        <div class="testimonial-author">
          <img src="assets/images/farmer1.jpg" alt="Farmer">
          <div>
            <strong>Rajesh Kumar</strong>
            <p>Wheat Farmer, Punjab</p>
          </div>
        </div>
      </div>
      <div class="testimonial">
        <p class="testimonial-text">The drip irrigation system I bought has helped me save water and reduce my overall costs. The customer service was also excellent!</p>
        <div class="testimonial-author">
          <img src="assets/images/farmer2.jpg" alt="Farmer">
          <div>
            <strong>Lakshmi Devi</strong>
            <p>Vegetable Grower, Karnataka</p>
          </div>
        </div>
      </div>
      <div class="testimonial">
        <p class="testimonial-text">Their organic fertilizers have transformed my soil quality. My crops are healthier, and I'm now able to sell premium organic produce.</p>
        <div class="testimonial-author">
          <img src="assets/images/farmer3.jpg" alt="Farmer">
          <div>
            <strong>Amit Patel</strong>
            <p>Organic Farmer, Gujarat</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Seasonal Products -->
<section class="section" style="background-color: #f9f9f9;">
  <div class="container">
    <h2 class="section-title">Seasonal Offers</h2>
    <p class="section-description">Limited time deals on essential farming products for the current season</p>
    
    <div class="card-grid">
      <?php
      // Try to fetch discounted products from database
      $seasonal_products = [];
      
     
      
      // If no products from database, use fallback data
      if (empty($seasonal_products)) {
        $seasonal_products = [
          ['id' => 5, 'name' => 'Monsoon Special Seeds Pack', 'description' => 'Perfect for rainy season plantation.', 'price' => 999, 'discount_price' => 699, 'image' => 'season1.jpg'],
          ['id' => 6, 'name' => 'Premium NPK Fertilizer', 'description' => 'Balanced nutrition for all crops.', 'price' => 800, 'discount_price' => 599, 'image' => 'season2.jpg'],
          ['id' => 7, 'name' => 'Organic Pest Control Spray', 'description' => 'Chemical-free pest management solution.', 'price' => 1200, 'discount_price' => 720, 'image' => 'season3.jpg'],
          ['id' => 8, 'name' => 'Solar Water Pump', 'description' => 'Energy-efficient irrigation solution.', 'price' => 5000, 'discount_price' => 4000, 'image' => 'season4.jpg']
        ];
      }
      
      foreach ($seasonal_products as $product):
        $discount_percentage = round(($product['price'] - $product['discount_price']) / $product['price'] * 100);
      ?>
      <div class="card">
        <div class="discount-badge"><?php echo $discount_percentage; ?>% OFF</div>
        <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <div class="card-content">
          <h3 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
          <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
          <div class="price">
            <span style="text-decoration: line-through; color: #888; margin-right: 10px;">₹<?php echo htmlspecialchars($product['price']); ?></span>
            <span>₹<?php echo htmlspecialchars($product['discount_price']); ?></span>
          </div>
          <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Call to Action -->
<section class="section" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/field.jpg') center/cover; color: white; text-align: center;">
  <div class="container">
    <h2 class="section-title" style="color: white;">Ready to Transform Your Farming?</h2>
    <p style="max-width: 700px; margin: 0 auto 2rem;">Join thousands of satisfied farmers across India who are increasing their productivity and profits with our premium agricultural products.</p>
    <div class="cta-buttons">
      <a href="register.php" class="btn">Register Now</a>
      <a href="category.php" class="btn btn-outline">Browse Categories</a>
    </div>
  </div>
</section>



<?php include 'footer.php'; ?>

<script>
  // Simple JavaScript for animations and interactions
  document.addEventListener('DOMContentLoaded', function() {
    // You could add some interactivity here if needed
    console.log('AgriCart website loaded successfully!');
  });
</script>

</body>
</html>
