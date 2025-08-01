<?php
session_start();
require_once 'config.php';
// Functions are loaded via config.php

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get product data
$id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : 0;

if (!$id) {
    redirect('404.php');
}

$product = getProductById($id);
if (!$product) {
    redirect('404.php');
}

// Get related products
$relatedProducts = getRelatedProducts($product['category_id'], $id, 4);

// Page title and meta
$pageTitle = htmlspecialchars($product['name']) . ' - Agri E-Commerce';
$metaDescription = substr(strip_tags($product['description']), 0, 160);

// Include header
include 'header.php';
?>

<div class="container product-container">
    <div class="breadcrumb">
        <a href="index.php">Home</a> &gt; 
        <a href="category.php?id=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a> &gt; 
        <?= htmlspecialchars($product['name']) ?>
    </div>
    
    <div class="product-view">
        <div class="product-gallery">
            <img id="main-image" src="assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php if (!empty($product['additional_images'])): ?>
                <div class="thumbnail-gallery">
                    <?php foreach ($product['additional_images'] as $img): ?>
                    <img class="thumbnail" src="assets/images/<?= htmlspecialchars($img) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         onclick="document.getElementById('main-image').src='assets/images/<?= htmlspecialchars($img) ?>'">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-details">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-meta">
                <span class="product-sku">SKU: <?= htmlspecialchars($product['sku']) ?></span>
                <span class="stock-status <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                    <?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                </span>
            </div>
            
            <div class="price">₹<?= number_format($product['price'], 2) ?></div>
            
            <div class="product-description">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>
            
            <?php if ($product['stock'] > 0): ?>
                <form method="post" action="cart.php" class="add-to-cart-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <div class="quantity-selector">
                            <button type="button" class="qty-btn" onclick="decrementQty()">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" required>
                            <button type="button" class="qty-btn" onclick="incrementQty()">+</button>
                        </div>
                    </div>
                    
                    <button class="btn add-to-cart-btn" type="submit" name="add_to_cart">
                        <i class="fa fa-shopping-cart"></i> Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <div class="out-of-stock-message">
                    This product is currently out of stock. Please check back later.
                </div>
            <?php endif; ?>
            
            <div class="product-meta-info">
                <div class="delivery-info">
                    <i class="fa fa-truck"></i> Free delivery for orders over ₹500
                </div>
                <div class="returns-info">
                    <i class="fa fa-refresh"></i> 30-day returns policy
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($relatedProducts)): ?>
    <div class="related-products">
        <h2>You may also like</h2>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $rProduct): ?>
                <div class="product-card">
                    <a href="product.php?id=<?= $rProduct['id'] ?>">
                        <img src="assets/images/<?= htmlspecialchars($rProduct['image']) ?>" 
                             alt="<?= htmlspecialchars($rProduct['name']) ?>">
                        <h3><?= htmlspecialchars($rProduct['name']) ?></h3>
                        <div class="product-price">₹<?= number_format($rProduct['price'], 2) ?></div>
                    </a>
                    <button class="quick-add-btn" data-product-id="<?= $rProduct['id'] ?>">Quick Add</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="product-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="description">Description</button>
            <button class="tab-btn" data-tab="specifications">Specifications</button>
            <button class="tab-btn" data-tab="reviews">Reviews</button>
        </div>
        
        <div class="tab-content active" id="description-tab">
            <?= nl2br(htmlspecialchars($product['full_description'] ?? $product['description'])) ?>
        </div>
        
        <div class="tab-content" id="specifications-tab">
            <?php if (!empty($product['specifications'])): ?>
                <table class="specs-table">
                    <?php foreach ($product['specifications'] as $key => $value): ?>
                        <tr>
                            <th><?= htmlspecialchars($key) ?></th>
                            <td><?= htmlspecialchars($value) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No specifications available for this product.</p>
            <?php endif; ?>
        </div>
        
        <div class="tab-content" id="reviews-tab">
            <?php
            $reviews = getProductReviews($id);
            if (!empty($reviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="reviewer-name"><?= htmlspecialchars($review['user_name']) ?></span>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star<?= $i <= $review['rating'] ? '' : '-o' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="review-date"><?= date('M d, Y', strtotime($review['date_added'])) ?></span>
                            </div>
                            <div class="review-content"><?= nl2br(htmlspecialchars($review['review'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No reviews yet. Be the first to review this product!</p>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="write-review">
                    <h3>Write a Review</h3>
                    <form method="post" action="submit_review.php" class="review-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        
                        <div class="form-group">
                            <label for="rating">Rating:</label>
                            <div class="rating-selector">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                    <label for="star<?= $i ?>"><i class="fa fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="review">Your Review:</label>
                            <textarea id="review" name="review" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn">Submit Review</button>
                    </form>
                </div>
            <?php else: ?>
                <p><a href="login.php">Log in</a> to write a review.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function incrementQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    const currentValue = parseInt(input.value);
    
    if (currentValue < max) {
        input.value = currentValue + 1;
    }
}

function decrementQty() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

// Tab switching functionality
document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons and content
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Add active class to clicked button
        button.classList.add('active');
        
        // Show corresponding content
        const tabId = button.getAttribute('data-tab') + '-tab';
        document.getElementById(tabId).classList.add('active');
    });
});

// Quick add functionality
document.querySelectorAll('.quick-add-btn').forEach(button => {
    button.addEventListener('click', (e) => {
        e.preventDefault();
        const productId = button.getAttribute('data-product-id');
        
        fetch('quick_add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
            },
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Product added to cart');
                updateCartCount(data.cart_count);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
    });
});

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}
</script>

<?php
// Include footer
include 'footer.php';
?>
