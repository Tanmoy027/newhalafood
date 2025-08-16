<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = intval($_GET['id']);

// Get product details
$sql = "SELECT f.*, c.name as country_name, c.id as country_id 
        FROM food_items f 
        LEFT JOIN countries c ON f.country_id = c.id 
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Get related products from the same country or category
$related_sql = "SELECT f.*, c.name as country_name 
               FROM food_items f 
               LEFT JOIN countries c ON f.country_id = c.id 
               WHERE (f.country_id = ? OR f.category = ?) 
               AND f.id != ? 
               ORDER BY RAND() 
               LIMIT 4";
$stmt = $conn->prepare($related_sql);
$stmt->bind_param("isi", $product['country_id'], $product['category'], $product_id);
$stmt->execute();
$related_result = $stmt->get_result();
$stmt->close();

// Page title
$page_title = $product['name'] . ' - Food Catalog';

include 'includes/header.php';
?>

<section class="product-detail-section py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <?php if (!empty($product['category'])): ?>
                <li class="breadcrumb-item"><a href="category.php?name=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="product-image-container">
                    <?php if (!empty($product['image']) && file_exists("assets/img/foods/" . $product['image'])): ?>
                        <img src="assets/img/foods/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-main-image">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/500x500?text=<?php echo urlencode($product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-main-image">
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="product-details">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-meta mb-4">
                        <?php if (!empty($product['country_name'])): ?>
                        <div class="product-origin">
                            <span class="meta-label">Origin:</span>
                            <a href="country.php?id=<?php echo $product['country_id']; ?>" class="meta-value country-link">
                                <?php echo htmlspecialchars($product['country_name']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['category'])): ?>
                        <div class="product-category">
                            <span class="meta-label">Category:</span>
                            <a href="category.php?name=<?php echo urlencode($product['category']); ?>" class="meta-value">
                                <?php echo htmlspecialchars($product['category']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Product badges -->
                        <div class="product-badges">
                            <?php if ((isset($product['featured']) && $product['featured']) || (isset($product['is_featured']) && $product['is_featured'])): ?>
                                <span class="badge bg-primary">Featured</span>
                            <?php endif; ?>
                            <?php if (isset($product['is_bestseller']) && $product['is_bestseller']): ?>
                                <span class="badge bg-warning">Best Seller</span>
                            <?php endif; ?>
                            <?php if (isset($product['is_new_arrival']) && $product['is_new_arrival']): ?>
                                <span class="badge bg-success">New Arrival</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="product-description mb-4">
                        <h3>Description</h3>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($product['ingredients'])): ?>
                    <div class="product-ingredients mb-4">
                        <h3>Ingredients</h3>
                        <div class="ingredients-content">
                            <?php echo nl2br(htmlspecialchars($product['ingredients'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="product-actions mt-4">
                        <!-- Back button -->
                        <a href="javascript:history.back()" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                        
                        <!-- Country link -->
                        <?php if (!empty($product['country_id'])): ?>
                        <a href="country.php?id=<?php echo $product['country_id']; ?>" class="btn btn-primary ms-2">
                            <i class="fas fa-globe me-2"></i> More from <?php echo htmlspecialchars($product['country_name']); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products Section -->
<?php if ($related_result && $related_result->num_rows > 0): ?>
<section class="related-products-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title">You May Also Like</h2>
        
        <div class="row">
            <?php while($related = $related_result->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="product-card h-100">
                        <div class="product-image">
                            <?php if (!empty($related['image']) && file_exists("assets/img/foods/" . $related['image'])): ?>
                                <img src="assets/img/foods/<?php echo $related['image']; ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x300?text=<?php echo urlencode($related['name']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                            <?php endif; ?>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $related['id']; ?>" class="view-btn" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="product-content">
                            <?php if (!empty($related['country_name'])): ?>
                                <span class="product-country"><?php echo htmlspecialchars($related['country_name']); ?></span>
                            <?php endif; ?>
                            
                            <h3 class="product-title">
                                <a href="product.php?id=<?php echo $related['id']; ?>"><?php echo htmlspecialchars($related['name']); ?></a>
                            </h3>
                            
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="btn-view-details">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
    /* Product Detail Styles */
    .product-detail-section {
        padding: 60px 0;
    }
    
    .breadcrumb {
        margin-bottom: 30px;
        background-color: transparent;
        padding: 0;
    }
    
    .breadcrumb-item a {
        color: var(--primary);
    }
    
    .breadcrumb-item.active {
        color: var(--text-dark);
    }
    
    .product-image-container {
        position: relative;
        margin-bottom: 20px;
    }
    
    .product-main-image {
        width: 100%;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .product-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
    }
    
    .product-meta {
        margin-bottom: 20px;
    }
    
    .product-meta > div {
        margin-bottom: 10px;
    }
    
    .meta-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-right: 10px;
    }
    
    .meta-value {
        color: var(--text-light);
    }
    
    .country-link {
        color: var(--primary);
    }
    
    .product-badges {
        margin-top: 15px;
    }
    
    .badge {
        padding: 6px 12px;
        margin-right: 8px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .product-description, .product-ingredients {
        margin-bottom: 30px;
    }
    
    .product-description h3, .product-ingredients h3 {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: var(--text-dark);
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-light);
    }
    
    .description-content, .ingredients-content {
        color: var(--text-light);
        line-height: 1.8;
    }
    
    .product-actions {
        margin-top: 30px;
    }
    
    /* Related Products Styles */
    .related-products-section {
        padding: 60px 0;
        background-color: var(--light-bg);
    }
    
    .related-products-section .section-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 30px;
        text-align: center;
        position: relative;
    }
    
    .related-products-section .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background-color: var(--primary);
    }
    
    /* Responsive */
    @media (max-width: 991px) {
        .product-title {
            font-size: 1.8rem;
        }
    }
    
    @media (max-width: 767px) {
        .product-actions {
            flex-direction: column;
            gap: 10px;
        }
        
        .product-actions .btn {
            width: 100%;
            margin-left: 0 !important;
            margin-bottom: 10px;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>