<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Set page title
$page_title = "All Products - Halal Food";

// Simple query to get all products - MODIFIED to match your actual database structure
$sql = "SELECT * FROM food_items ORDER BY id DESC";
$result = $conn->query($sql);

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>All Products</h1>
        <p>Browse our complete selection of authentic halal food products</p>
    </div>
</section>

<section class="products-section">
    <div class="container">
        <?php if($result && $result->num_rows > 0): ?>
            <div class="result-info">
                <p>Showing <?php echo $result->num_rows; ?> products</p>
            </div>
            
            <div class="products-grid">
                <?php while($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php if (isset($product['is_bestseller']) && $product['is_bestseller'] == 1): ?>
                            <div class="product-badge best-seller">Best Seller</div>
                        <?php elseif (isset($product['is_new_arrival']) && $product['is_new_arrival'] == 1): ?>
                            <div class="product-badge new-arrival">New Arrival</div>
                        <?php elseif (isset($product['is_featured']) && $product['is_featured'] == 1): ?>
                            <div class="product-badge featured">Featured</div>
                        <?php endif; ?>
                        
                        <div class="product-image">
                            <?php if (!empty($product['image']) && file_exists("assets/img/foods/" . $product['image'])): ?>
                                <img src="assets/img/foods/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x300?text=<?php echo urlencode($product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-content">
                            <div class="product-meta">
                                <?php 
                                // Fetch country name if country_id exists
                                if (!empty($product['country_id'])) {
                                    $country_sql = "SELECT name FROM countries WHERE id = ?";
                                    $stmt = $conn->prepare($country_sql);
                                    $stmt->bind_param("i", $product['country_id']);
                                    $stmt->execute();
                                    $country_result = $stmt->get_result();
                                    if ($country_result && $country_result->num_rows > 0) {
                                        $country = $country_result->fetch_assoc();
                                        echo '<span class="product-country">'.htmlspecialchars($country['name']).'</span>';
                                    }
                                    $stmt->close();
                                }
                                ?>
                                
                                <?php if (!empty($product['category'])): ?>
                                    <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="product-title">
                                <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                            </h3>
                            
                            <?php if(!empty($product['description'])): ?>
                                <div class="product-description">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . (strlen($product['description']) > 80 ? '...' : ''); ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-view-details">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p><i class="fas fa-info-circle me-2"></i> No products found.</p>
                <p>Please check back later as we add new products.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Page Header Styles */
    .page-header {
        background-color: #0a2d5e; /* Navy blue */
        color: white;
        padding: 30px 0;
        text-align: center;
        margin-bottom: 30px;
        background-image: linear-gradient(135deg, rgba(10,45,94,0.9) 0%, rgba(5,29,65,0.9) 100%), url('assets/img/world-map.png'); /* Navy blue gradient */
        background-size: cover;
        background-position: center;
    }
    
    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .page-header p {
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
        opacity: 0.9;
    }
    
    /* Products Section Styles */
    .products-section {
        padding-bottom: 60px;
    }
    
    .result-info {
        margin-bottom: 20px;
        color: #6c757d;
    }
    
    /* Products Grid Styles */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .product-card {
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    /* Product Badge Styles */
    .product-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 5px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 4px;
        z-index: 2;
        color: white;
    }
    
    .product-badge.best-seller {
        background-color: #ffc107; /* Warning yellow */
    }
    
    .product-badge.new-arrival {
        background-color: #28a745; /* Success green */
    }
    
    .product-badge.featured {
        background-color: #0a2d5e; /* Navy blue */
    }
    
    .product-image {
        height: 200px;
        overflow: hidden;
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
    
    .product-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .product-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .product-country,
    .product-category {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .product-country {
        color: #0a2d5e; /* Navy blue */
    }
    
    .product-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 10px;
        line-height: 1.3;
    }
    
    .product-title a {
        color: #212529;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .product-title a:hover {
        color: #0a2d5e; /* Navy blue */
    }
    
    .product-description {
        color: #6c757d;
        margin-bottom: 20px;
        line-height: 1.5;
        font-size: 0.9rem;
        flex-grow: 1;
    }
    
    .btn-view-details {
        display: inline-block;
        padding: 8px 20px;
        background-color: #e4e9f2; /* Light navy */
        color: #0a2d5e; /* Navy blue */
        border-radius: 5px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        align-self: flex-start;
    }
    
    .btn-view-details:hover {
        background-color: #0a2d5e; /* Navy blue */
        color: white;
    }
    
    /* Alert Styles */
    .alert {
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    
    .alert-info {
        background-color: #e4e9f2; /* Light navy */
        color: #0a2d5e; /* Navy blue */
        border-left: 5px solid #0a2d5e; /* Navy blue */
    }
    
    /* Responsive Adjustments */
    @media (max-width: 991px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
    }
    
    @media (max-width: 767px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
        }
        
        .product-image {
            height: 150px;
        }
        
        .product-content {
            padding: 15px;
        }
        
        .product-title {
            font-size: 1rem;
        }
        
        .product-description {
            font-size: 0.85rem;
            margin-bottom: 15px;
        }
        
        .btn-view-details {
            padding: 6px 15px;
            font-size: 0.85rem;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>