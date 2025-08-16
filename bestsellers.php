<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 16; // Increased from 12 to 16 products per page
$offset = ($page - 1) * $limit;

// First check if the is_bestseller column exists
$column_exists = false;
$check_column = $conn->query("SHOW COLUMNS FROM food_items LIKE 'is_bestseller'");

if ($check_column && $check_column->num_rows > 0) {
    // Column exists, use it for filtering
    $column_exists = true;
    
    // Get bestseller products
    $sql = "SELECT f.*, c.name as country_name 
            FROM food_items f 
            LEFT JOIN countries c ON f.country_id = c.id 
            WHERE f.is_bestseller = 1 
            ORDER BY f.name ASC 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $products_result = $stmt->get_result();
        $stmt->close();
        
        // Count total bestsellers for pagination
        $count_sql = "SELECT COUNT(*) as total FROM food_items WHERE is_bestseller = 1";
        $count_result = $conn->query($count_sql);
        $total_products = $count_result->fetch_assoc()['total'];
    } else {
        // Handle SQL prepare failure
        $products_result = false;
        $total_products = 0;
        $error = $conn->error;
    }
} else {
    // Column doesn't exist, get the most popular products instead (using ID as a stand-in)
    $sql = "SELECT f.*, c.name as country_name 
            FROM food_items f 
            LEFT JOIN countries c ON f.country_id = c.id 
            ORDER BY f.id ASC 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $products_result = $stmt->get_result();
        $stmt->close();
        
        // Count total products for pagination
        $count_sql = "SELECT COUNT(*) as total FROM food_items";
        $count_result = $conn->query($count_sql);
        $total_products = $count_result->fetch_assoc()['total'];
    } else {
        // Handle SQL prepare failure
        $products_result = false;
        $total_products = 0;
        $error = $conn->error;
    }
}

$total_pages = ceil($total_products / $limit);

// Page title
$page_title = "Best Sellers - Food Catalog";

include 'includes/header.php';
?>

<section class="page-header" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/img/bestsellers-bg.jpg');">
    <div class="container">
        <h1>Best Sellers</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Best Sellers</li>
            </ol>
        </nav>
    </div>
</section>

<section class="bestsellers-section py-3">
    <div class="container">
        <div class="section-intro text-center mb-3">
            <h2 class="section-title">Our Most Popular Products</h2>
            <p class="section-desc">Discover our customers' favorite international foods that have received the highest ratings and most orders.</p>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-warning mt-2 py-2">
                <p class="mb-0">Note: We're currently experiencing a technical issue. Showing our featured products instead.</p>
            </div>
            <?php endif; ?>
            
            <?php if (!$column_exists && !isset($error)): ?>
            <div class="alert alert-info mt-2 py-2">
                <p class="mb-0">We're showing our featured products collection.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="row mb-2">
            <div class="col-12">
                <p class="result-count"><?php echo $total_products; ?> products found</p>
            </div>
        </div>
        
        <?php if ($products_result && $products_result->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-badge bestseller">Best Seller</div>
                        <div class="product-image">
                            <?php if (!empty($product['image']) && file_exists("assets/img/foods/" . $product['image'])): ?>
                                <img src="assets/img/foods/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x300?text=<?php echo urlencode($product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="view-btn" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="product-content">
                            <?php if (!empty($product['country_name'])): ?>
                                <span class="product-country"><?php echo htmlspecialchars($product['country_name']); ?></span>
                            <?php endif; ?>
                            
                            <h3 class="product-title">
                                <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                            </h3>
                            
                            <div class="product-description">
                                <?php 
                                $description = !empty($product['description']) ? $product['description'] : 'No description available';
                                echo htmlspecialchars(substr($description, 0, 60)) . (strlen($description) > 60 ? '...' : ''); 
                                ?>
                            </div>
                            
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-view-details">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <p><i class="fas fa-info-circle me-2"></i> No bestseller products found.</p>
                <p>Please check back later for our featured bestsellers.</p>
                <a href="index.php" class="btn btn-primary mt-2">Return to Home</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Page Header - Reduced size */
    .page-header {
        padding: 40px 0; /* Reduced from 80px */
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        position: relative;
        margin-bottom: 20px; /* Reduced from 40px */
    }

    .page-header h1 {
        font-size: 1.8rem; /* Reduced from 2.5rem */
        font-weight: 700;
        margin-bottom: 10px; /* Reduced from 15px */
    }
    
    .page-header .breadcrumb {
        background-color: transparent;
        justify-content: center;
        margin: 0;
        padding: 0;
        font-size: 0.85rem; /* Smaller font size */
    }
    
    .page-header .breadcrumb-item, 
    .page-header .breadcrumb-item a {
        color: white;
    }
    
    .page-header .breadcrumb-item+.breadcrumb-item::before {
        color: rgba(255,255,255,0.6);
    }
    
    /* Section Intro - Reduced size */
    .section-title {
        font-size: 1.5rem; /* Reduced from 2rem */
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 10px; /* Reduced from 15px */
    }
    
    .section-desc {
        max-width: 700px;
        margin: 0 auto;
        color: var(--text-light);
        font-size: 0.9rem; /* Smaller font size */
    }
    
    /* Products Grid - Smaller cards */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Reduced from 280px */
        gap: 15px; /* Reduced from 30px */
        margin-bottom: 25px; /* Reduced from 40px */
    }
    
    /* Bestseller Badge */
    .product-badge.bestseller {
        background-color: #FF9800;
        font-size: 0.7rem; /* Smaller font */
        padding: 3px 8px; /* Smaller padding */
    }
    
    /* Result Count */
    .result-count {
        color: var(--text-light);
        margin-bottom: 15px; /* Reduced from 20px */
        font-size: 0.85rem; /* Smaller font */
    }
    
    /* Product Cards - Smaller size */
    .product-card {
        background-color: var(--white);
        border-radius: 6px; /* Reduced from 10px */
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05); /* Reduced shadow */
        transition: all 0.3s ease;
        position: relative;
    }
    
    .product-card:hover {
        transform: translateY(-5px); /* Reduced from -10px */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); /* Reduced shadow */
    }
    
    .product-badge {
        position: absolute;
        top: 10px; /* Reduced from 15px */
        left: 10px; /* Reduced from 15px */
        background-color: var(--primary);
        color: white;
        padding: 3px 8px; /* Reduced from 5px 10px */
        border-radius: 3px; /* Reduced from 5px */
        font-size: 0.7rem; /* Reduced from 0.8rem */
        z-index: 2;
        font-weight: 500;
    }
    
    .product-image {
        position: relative;
        overflow: hidden;
        height: 160px; /* Reduced from 220px */
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.5s ease;
    }
    
    .product-card:hover .product-image img {
        transform: scale(1.08); /* Reduced from 1.1 */
    }
    
    .product-actions {
        position: absolute;
        top: 10px; /* Reduced from 15px */
        right: 10px; /* Reduced from 15px */
        display: flex;
        flex-direction: column;
        gap: 8px; /* Reduced from 10px */
        opacity: 0;
        transform: translateX(15px); /* Reduced from 20px */
        transition: all 0.3s ease;
    }
    
    .product-card:hover .product-actions {
        opacity: 1;
        transform: translateX(0);
    }
    
    .product-actions .view-btn {
        width: 30px; /* Reduced from 40px */
        height: 30px; /* Reduced from 40px */
        border-radius: 50%;
        background-color: var(--white);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem; /* Reduced from 1rem */
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Reduced shadow */
        transition: all 0.3s ease;
    }
    
    .product-actions .view-btn:hover {
        background-color: var(--primary);
        color: var(--white);
    }
    
    .product-content {
        padding: 12px; /* Reduced from 20px */
    }
    
    .product-country {
        display: inline-block;
        color: var(--primary);
        font-size: 0.75rem; /* Reduced from 0.9rem */
        margin-bottom: 5px; /* Reduced from 10px */
    }
    
    .product-title {
        font-size: 0.95rem; /* Reduced from 1.2rem */
        font-weight: 600;
        margin-bottom: 6px; /* Reduced from 10px */
        line-height: 1.3; /* Reduced from 1.5 */
    }
    
    .product-title a {
        color: var(--text-dark);
        transition: all 0.3s ease;
    }
    
    .product-title a:hover {
        color: var(--primary);
    }
    
    .product-description {
        color: var(--text-light);
        font-size: 0.8rem; /* Reduced from 0.95rem */
        margin-bottom: 10px; /* Reduced from 15px */
        line-height: 1.3; /* Reduced from 1.5 */
    }
    
    .btn-view-details {
        display: inline-block;
        padding: 5px 12px; /* Reduced from 8px 20px */
        background-color: var(--primary-light);
        color: var(--primary);
        border-radius: 4px; /* Reduced from 5px */
        font-weight: 500;
        font-size: 0.75rem; /* Reduced from 0.9rem */
        transition: all 0.3s ease;
    }
    
    .btn-view-details:hover {
        background-color: var(--primary);
        color: white;
    }
    
    /* Pagination - Smaller controls */
    .pagination .page-link {
        padding: 0.25rem 0.5rem; /* Smaller pagination */
        font-size: 0.8rem;
    }
    
    /* Responsive Styles */
    @media (max-width: 991px) {
        .page-header {
            padding: 30px 0; /* Reduced from 60px */
        }
        
        .page-header h1 {
            font-size: 1.5rem; /* Reduced from 2rem */
        }
        
        .section-title {
            font-size: 1.3rem; /* Reduced from 1.8rem */
        }
    }
    
    @media (max-width: 767px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr); /* 3 columns on medium screens */
        }
    }
    
    @media (max-width: 576px) {
        .page-header {
            padding: 25px 0; /* Reduced from 40px */
        }
        
        .page-header h1 {
            font-size: 1.3rem; /* Reduced from 1.7rem */
        }
        
        .section-title {
            font-size: 1.2rem; /* Reduced from 1.5rem */
        }
        
        .products-grid {
            grid-template-columns: repeat(2, 1fr); /* 2 columns on small screens */
            gap: 10px; /* Further reduced gap */
        }
        
        .product-image {
            height: 140px; /* Smaller on mobile */
        }
        
        .product-description {
            display: none; /* Hide description on very small screens */
        }
    }
    
    /* Extra small devices */
    @media (max-width: 350px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr); /* Keep 2 columns */
            gap: 8px; /* Minimal gap */
        }
        
        .product-image {
            height: 120px; /* Even smaller */
        }
        
        .product-content {
            padding: 8px; /* Smaller padding */
        }
        
        .btn-view-details {
            padding: 3px 8px;
            font-size: 0.7rem;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>