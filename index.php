<?php
error_reporting(E_ALL & ~E_NOTICE);
?>
<?php 
// Check for accidental username prefix and remove it
$content = file_get_contents(__FILE__);
if (strpos($content, 'zukalutoka<?php') === 0) {
    $content = str_replace('zukalutoka<?php', '<?php', $content);
    file_put_contents(__FILE__, $content);
}

session_start();
require_once 'includes/db.php';  // Make sure this connects to your database

// Fetch categories
$categories_sql = "SELECT * FROM categories ORDER BY name ASC LIMIT 6";
$categories_result = $conn->query($categories_sql);

// Fetch featured/bestseller products
$featured_sql = "SELECT f.*, c.name as country_name 
                FROM food_items f 
                LEFT JOIN countries c ON f.country_id = c.id 
                WHERE f.featured = 1
                ORDER BY f.id DESC 
                LIMIT 8";
$featured_result = $conn->query($featured_sql);

// If no featured products, just get the latest products
if (!$featured_result || $featured_result->num_rows == 0) {
    $featured_sql = "SELECT f.*, c.name as country_name 
                    FROM food_items f 
                    LEFT JOIN countries c ON f.country_id = c.id 
                    ORDER BY f.id DESC 
                    LIMIT 8";
    $featured_result = $conn->query($featured_sql);
}

// Fetch countries
$countries_sql = "SELECT * FROM countries ORDER BY name ASC LIMIT 6";
$countries_result = $conn->query($countries_sql);

include 'includes/header.php'; 
?>

<!-- Hero Banner with Left-side Blur -->
<section class="hero-section" style="background-image: url('assets/img/cover.jpeg');">
    <div class="hero-overlay-gradient"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="hero-content">
                    <h1 class="hero-title">International Grocery Products</h1>
                    <p class="hero-subtitle">Discover quality food items from around the world</p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-primary me-3">View Our Catalog</a>
                        <a href="#categories" class="btn btn-outline">Explore Categories</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Right side remains clear to show image -->
            </div>
        </div>
    </div>
</section>

<!-- Info Cards -->
<section class="info-section">
    <div class="container">
        <div class="info-cards">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h4>International Selection</h4>
                <p>Products from over 30 countries around the world</p>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h4>Quality Products</h4>
                <p>Carefully sourced ingredients and premium quality</p>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h4>Fast Shipping</h4>
                <p>Quick delivery to your doorstep</p>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h4>Customer Support</h4>
                <p>We're here to help with your questions</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section id="categories" class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Browse Our Categories</h2>
            <p class="section-subtitle">Find delicious foods by category</p>
        </div>
        
        <div class="categories-grid">
            <?php 
            // Check if categories table exists
            $table_exists_query = "SHOW TABLES LIKE 'categories'";
            $table_exists_result = $conn->query($table_exists_query);
            
            if ($table_exists_result && $table_exists_result->num_rows > 0 && $categories_result && $categories_result->num_rows > 0):
                while($category = $categories_result->fetch_assoc()):
                    // Count products in this category
                    $count_sql = "SELECT COUNT(*) as count FROM food_items WHERE category = '" . $conn->real_escape_string($category['name']) . "'";
                    $count_result = $conn->query($count_sql);
                    $product_count = ($count_result && $count_result->num_rows > 0) ? $count_result->fetch_assoc()['count'] : 0;
            ?>
            <a href="category.php?id=<?php echo $category['id']; ?>" class="category-card">
                <div class="category-image">
                    <?php if (!empty($category['image']) && file_exists("assets/img/categories/" . $category['image'])): ?>
                        <img src="assets/img/categories/<?php echo $category['image']; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/400x300?text=<?php echo urlencode($category['name']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <?php endif; ?>
                    <div class="category-overlay"></div>
                </div>
                <div class="category-content">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <span class="category-count"><?php echo $product_count; ?> products</span>
                    <span class="view-more">View Collection <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php 
                endwhile;
            else:
                // If no categories in database, show message to add categories
            ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <p><i class="fas fa-info-circle me-2"></i> No categories found.</p>
                    <p>Add categories through the admin panel to display them here.</p>
                </div>
                <?php if (isset($_SESSION['admin_id'])): ?>
                <a href="admin/category-add.php" class="btn btn-primary mt-2">Add Categories</a>
                <?php endif; ?>
            </div>
            <?php 
            endif; 
            ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Best Selling Products</h2>
            <p class="section-subtitle">Our most popular items loved by customers</p>
        </div>
        
        <div class="products-grid">
            <?php 
            if ($featured_result && $featured_result->num_rows > 0):
                while($product = $featured_result->fetch_assoc()):
                    $badge = '';
                    if (isset($product['featured']) && $product['featured']) {
                        $badge = 'Featured';
                    }
            ?>
            <div class="product-card">
                <?php if (!empty($badge)): ?>
                <div class="product-badge"><?php echo $badge; ?></div>
                <?php endif; ?>
                <div class="product-image">
                    <?php if (!empty($product['image']) && file_exists("assets/img/foods/" . $product['image'])): ?>
                        <img src="assets/img/foods/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/300x300?text=<?php echo urlencode($product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endif; ?>
                    <div class="product-actions">
                        <a href="viewproduct.php?id=<?php echo $product['id']; ?>" class="view-btn" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                <div class="product-content">
                    <?php if (!empty($product['country_name'])): ?>
                        <span class="product-country"><?php echo htmlspecialchars($product['country_name']); ?></span>
                    <?php endif; ?>
                    
                    <h3 class="product-title">
                        <a href="viewproduct.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                    </h3>
                    
                    <div class="product-description">
                        <?php 
                        $description = !empty($product['description']) ? $product['description'] : 'No description available';
                        echo htmlspecialchars(substr($description, 0, 100)) . (strlen($description) > 100 ? '...' : ''); 
                        ?>
                    </div>
                    
                    <a href="viewproduct.php?id=<?php echo $product['id']; ?>" class="btn-view-details">View Details</a>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle me-2"></i> No products found.</p>
                        <p>Add products through the admin panel to display them here.</p>
                    </div>
                    <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="admin/product-add.php" class="btn btn-primary mt-2">Add Products</a>
                    <?php endif; ?>
                </div>
            <?php
            endif;
            ?>
        </div>
        
        <?php if ($featured_result && $featured_result->num_rows > 0): ?>
        <div class="view-all-wrapper">
            <a href="bestsellers.php" class="btn btn-outline-primary">View All Best Sellers</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Countries Section -->
<section class="countries-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Browse by Country</h2>
            <p class="section-subtitle">Explore foods from different regions of the world</p>
        </div>
        
        <div class="countries-slider">
            <?php 
            if ($countries_result && $countries_result->num_rows > 0):
                while($country = $countries_result->fetch_assoc()):
                    // Count products from this country
                    $country_count_sql = "SELECT COUNT(*) as count FROM food_items WHERE country_id = " . intval($country['id']);
                    $country_count_result = $conn->query($country_count_sql);
                    $country_product_count = ($country_count_result && $country_count_result->num_rows > 0) ? $country_count_result->fetch_assoc()['count'] : 0;
                    
                    // Skip countries with no products
                    if ($country_product_count == 0) continue;
            ?>
            <div class="country-item">
                <a href="country.php?id=<?php echo $country['id']; ?>" class="text-decoration-none">
                    <?php if (!empty($country['image']) && file_exists("assets/img/countries/" . $country['image'])): ?>
                        <img src="assets/img/countries/<?php echo $country['image']; ?>" alt="<?php echo htmlspecialchars($country['name']); ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/150x150?text=<?php echo urlencode($country['name']); ?>" alt="<?php echo htmlspecialchars($country['name']); ?>">
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($country['name']); ?></h4>
                </a>
            </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle me-2"></i> No countries found.</p>
                        <p>Add countries through the admin panel to display them here.</p>
                    </div>
                    <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="admin/countries-add.php" class="btn btn-primary mt-2">Add Countries</a>
                    <?php endif; ?>
                </div>
            <?php
            endif;
            ?>
        </div>
        
        <?php if ($countries_result && $countries_result->num_rows > 0): ?>
        <div class="view-all-wrapper">
            <a href="countries.php" class="btn btn-outline-primary">View All Countries</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- About Store Section -->
<section class="about-store-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2">
                <div class="about-image">
                    <img src="assets/img/store-front.jpg" alt="Our Store" class="img-fluid rounded" onerror="this.src='https://via.placeholder.com/600x400?text=Our+Store';">
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="about-content">
                    <h2 class="about-title">About Our Store</h2>
                    <p class="about-subtitle">Your destination for international groceries in Tokyo</p>
                    
                    <div class="about-text">
                        <p>Located in the heart of Tokyo, our international grocery store offers a wide selection of authentic food products from around the world.</p>
                        <p>Our mission is to bring global flavors to your kitchen, allowing you to experience international cuisines without leaving your home.</p>
                    </div>
                    
                    <div class="store-info">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>1-2-3 Shibuya, Tokyo, Japan</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span>Mon-Sat: 10AM-8PM, Sun: 11AM-7PM</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone-alt"></i>
                            <span>+81 3-1234-5678</span>
                        </div>
                    </div>
                    
                    <div class="about-buttons">
                        <a href="contact.php" class="btn btn-primary me-3">Learn More</a>
                        <a href="contact.php" class="btn btn-outline-dark">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-inner">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="newsletter-content">
                        <h3>Subscribe to Our Newsletter</h3>
                        <p>Get updates on new products, special offers, and cooking recipes</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form" action="contact.php" method="post">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Additional styles for home page - Updated for Navy Blue Theme */
    :root {
        /* Updating color variables to navy blue */
        --primary: #0a2d5e;       /* Navy Blue */
        --primary-dark: #051d41;  /* Darker Navy */
        --primary-light: #e4e9f2; /* Light Navy */
    }
    
    /* Hero Section with Left-side Transparent Blur */
    .hero-section {
        position: relative;
        height: 500px;
        background-size: cover;
        background-position: center;
        color: var(--white);
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    /* Left-side only blur gradient overlay - made transparent not blue */
    .hero-overlay-gradient {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, 
            rgba(0, 0, 0, 0.7) 0%, 
            rgba(0, 0, 0, 0.5) 25%, 
            rgba(0, 0, 0, 0.2) 50%, 
            rgba(0, 0, 0, 0) 70%);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        /* Clip the blur to apply only to the left side */
        -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,0) 100%);
        mask-image: linear-gradient(to right, rgba(0,0,0,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,0) 100%);
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 600px;
        padding: 0 15px;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        line-height: 1.2;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.9;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }

    .hero-buttons {
        display: flex;
        gap: 15px;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .btn-outline {
        background-color: transparent;
        border: 2px solid white;
        color: white;
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        background-color: white;
        color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* For mobile devices */
    @media (max-width: 767px) {
        .hero-section {
            height: 400px;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1rem;
        }
        
        .hero-buttons {
            flex-direction: column;
            gap: 10px;
        }
        
        /* Adjust the gradient for mobile */
        .hero-overlay-gradient {
            background: linear-gradient(to right, 
                rgba(0, 0, 0, 0.7) 0%, 
                rgba(0, 0, 0, 0.5) 50%, 
                rgba(0, 0, 0, 0.2) 100%);
        }
    }
    
    /* Info Cards Section */
    .info-section {
        padding: 70px 0;
        background-color: var(--white);
    }
    
    .info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }
    
    .info-card {
        background-color: var(--white);
        padding: 30px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .info-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background-color: rgba(10, 45, 94, 0.1); /* Navy blue */
        color: var(--primary); /* Using navy primary */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 20px;
    }
    
    .info-card h4 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--text-dark);
    }
    
    .info-card p {
        color: var(--text-light);
        margin: 0;
        font-size: 0.95rem;
    }
    
    /* Categories Section */
    .categories-section {
        padding: 80px 0;
        background-color: var(--body-bg);
    }
    
    .section-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: -10px;
        width: 80px;
        height: 3px;
        background-color: var(--primary); /* Updated to navy */
        transform: translateX(-50%);
    }
    
    .section-subtitle {
        color: var(--text-light);
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0 auto;
    }
    
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
    
    .category-card {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        color: var(--text-dark);
    }
    
    .category-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .category-image {
        position: relative;
        overflow: hidden;
        height: 220px;
    }
    
    .category-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.5s ease;
    }
    
    .category-card:hover .category-image img {
        transform: scale(1.1);
    }
    
    .category-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 100%);
    }
    
    .category-content {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 20px;
        color: white;
    }
    
    .category-content h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .category-count {
        display: block;
        font-size: 0.9rem;
        opacity: 0.8;
        margin-bottom: 15px;
    }
    
    .view-more {
        color: var(--accent); /* Gold accent for contrast */
        font-size: 0.95rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }
    
    .view-more i {
        margin-left: 5px;
        transition: all 0.3s ease;
    }
    
    .category-card:hover .view-more {
        opacity: 1;
        transform: translateY(0);
    }
    
    .category-card:hover .view-more i {
        transform: translateX(5px);
    }
    
    /* Products Section */
    .products-section {
        padding: 80px 0;
        background-color: var(--white);
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }
    
    .product-card {
        background-color: var(--white);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .product-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background-color: var(--primary); /* Updated to navy */
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.8rem;
        z-index: 2;
        font-weight: 500;
    }
    
    .product-image {
        position: relative;
        overflow: hidden;
        height: 220px;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.5s ease;
    }
    
    .product-card:hover .product-image img {
        transform: scale(1.1);
    }
    
    .product-actions {
        position: absolute;
        top: 15px;
        right: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        opacity: 0;
        transform: translateX(20px);
        transition: all 0.3s ease;
    }
    
    .product-card:hover .product-actions {
        opacity: 1;
        transform: translateX(0);
    }
    
    .product-actions .view-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--white);
        color: var(--primary); /* Updated to navy */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .product-actions .view-btn:hover {
        background-color: var(--primary); /* Updated to navy */
        color: var(--white);
    }
    
    .product-content {
        padding: 20px;
    }
    
    .product-country {
        display: inline-block;
        color: var(--primary); /* Updated to navy */
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .product-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .product-title a {
        color: var(--text-dark);
        transition: all 0.3s ease;
    }
    
    .product-title a:hover {
        color: var(--primary); /* Updated to navy */
    }
    
    .product-description {
        color: var(--text-light);
        font-size: 0.95rem;
        margin-bottom: 15px;
        line-height: 1.5;
    }
    
    .btn-view-details {
        display: inline-block;
        padding: 8px 20px;
        background-color: var(--primary-light); /* Updated to light navy */
        color: var(--primary); /* Updated to navy */
        border-radius: 5px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .btn-view-details:hover {
        background-color: var(--primary); /* Updated to navy */
        color: white;
    }
    
    .view-all-wrapper {
        text-align: center;
        margin-top: 50px;
    }
    
    .btn-outline-primary {
        padding: 12px 30px;
        border: 2px solid var(--primary); /* Updated to navy */
        background-color: transparent;
        color: var(--primary); /* Updated to navy */
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary); /* Updated to navy */
        color: white;
    }
    
    /* Countries Section */
    .countries-section {
        padding: 80px 0;
        background-color: var(--body-bg);
    }
    
    .countries-slider {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 20px;
    }
    
    .country-item {
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .country-item:hover {
        transform: translateY(-10px);
    }
    
    .country-item img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        margin: 0 auto 10px;
        border: 3px solid var(--white);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .country-item:hover img {
        border-color: var(--primary); /* Updated to navy */
    }
    
    .country-item h4 {
        font-size: 1rem;
        margin: 0;
        color: var(--text-dark);
    }
    
    .country-item a {
        text-decoration: none;
        color: inherit;
    }
    
    /* About Store Section */
    .about-store-section {
        padding: 80px 0;
        background-color: var(--white);
    }
    
    .about-image {
        position: relative;
        margin-bottom: 30px;
    }
    
    .about-image::after {
        content: '';
        position: absolute;
        top: 20px;
        right: -20px;
        width: 100%;
        height: 100%;
        border: 5px solid var(--primary); /* Updated to navy */
        border-radius: 10px;
        z-index: -1;
    }
    
    .about-content {
        padding: 20px;
    }
    
    .about-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-dark);
    }
    
    .about-subtitle {
        color: var(--primary); /* Updated to navy */
        font-size: 1.1rem;
        margin-bottom: 20px;
    }
    
    .about-text {
        margin-bottom: 30px;
        color: var(--text-light);
    }
    
    .store-info {
        margin-bottom: 30px;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .info-item i {
        color: var(--primary); /* Updated to navy */
        margin-right: 10px;
        font-size: 1.1rem;
    }
    
    .info-item span {
        color: var(--text-dark);
    }
    
    .about-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .btn-outline-dark {
        border: 2px solid var(--text-dark);
        background-color: transparent;
        color: var(--text-dark);
    }
    
    .btn-outline-dark:hover {
        background-color: var(--text-dark);
        color: white;
    }
    
    /* Newsletter Section */
    .newsletter-section {
        padding: 50px 0;
        background-color: var(--primary-light); /* Updated to light navy */
    }
    
    .newsletter-inner {
        padding: 30px;
        border-radius: 10px;
    }
    
    .newsletter-content {
        margin-bottom: 20px;
    }
    
    .newsletter-content h3 {
        color: var(--text-dark);
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .newsletter-content p {
        color: var(--text-light);
        margin: 0;
    }
    
    .newsletter-form .input-group {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .newsletter-form .form-control {
        height: 50px;
        border-radius: 50px 0 0 50px;
        padding-left: 20px;
    }
    
    .newsletter-form .btn {
        border-radius: 0 50px 50px 0;
        padding: 0 30px;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 991px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
        }
        
        .section-title {
            font-size: 2rem;
        }
        
        .about-image::after {
            display: none;
        }
    }
    
    @media (max-width: 767px) {
        .hero-section {
            height: 400px;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
        
        .countries-slider {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .hero-section {
            height: 350px;
        }
        
        .hero-title {
            font-size: 1.8rem;
        }
        
        .countries-slider {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<?php include 'includes/footer.php'; ?>