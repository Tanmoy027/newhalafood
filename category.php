<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Get category by ID or name
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Get category by ID
    $category_id = intval($_GET['id']);
    $cat_sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($cat_sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $category_result = $stmt->get_result();
    $stmt->close();
    
    if ($category_result->num_rows > 0) {
        $category = $category_result->fetch_assoc();
        $category_name = $category['name'];
    } else {
        header('Location: index.php');
        exit;
    }
} elseif (isset($_GET['name']) && !empty($_GET['name'])) {
    // Get category by name
    $category_name = $_GET['name'];
    $cat_sql = "SELECT * FROM categories WHERE name = ?";
    $stmt = $conn->prepare($cat_sql);
    $stmt->bind_param("s", $category_name);
    $stmt->execute();
    $category_result = $stmt->get_result();
    $stmt->close();
    
    if ($category_result->num_rows > 0) {
        $category = $category_result->fetch_assoc();
    } else {
        // Just use the name from URL if category doesn't exist in database
        $category = [
            'name' => $category_name,
            'description' => ''
        ];
    }
} else {
    header('Location: index.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12; // 12 products per page
$offset = ($page - 1) * $limit;

// Get products in this category
$products_sql = "SELECT f.*, c.name as country_name 
                FROM food_items f 
                LEFT JOIN countries c ON f.country_id = c.id 
                WHERE f.category = ? 
                ORDER BY f.name ASC 
                LIMIT ?, ?";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param("sii", $category_name, $offset, $limit);
$stmt->execute();
$products_result = $stmt->get_result();
$stmt->close();

// Count total products for pagination
$count_sql = "SELECT COUNT(*) as total FROM food_items WHERE category = ?";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("s", $category_name);
$stmt->execute();
$count_result = $stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_products / $limit);

// Page title
$page_title = htmlspecialchars($category['name']) . ' - Food Catalog';

include 'includes/header.php';
?>

<section class="category-header" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo !empty($category['image']) && file_exists("assets/img/categories/" . $category['image']) ? "assets/img/categories/" . $category['image'] : "https://via.placeholder.com/1200x300?text=" . urlencode($category_name); ?>')">
    <div class="container">
        <h1><?php echo htmlspecialchars($category_name); ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($category_name); ?></li>
            </ol>
        </nav>
    </div>
</section>

<?php if (!empty($category['description'])): ?>
<section class="category-description">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <?php echo nl2br(htmlspecialchars($category['description'])); ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="category-products">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title"><?php echo htmlspecialchars($category_name); ?> Products</h2>
                <p class="result-count"><?php echo $total_products; ?> items found</p>
            </div>
        </div>
        
        <?php if ($products_result && $products_result->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php 
                        $badge = '';
                        if (isset($product['is_bestseller']) && $product['is_bestseller']) {
                            $badge = 'Best Seller';
                        } elseif (isset($product['is_new_arrival']) && $product['is_new_arrival']) {
                            $badge = 'New Arrival';
                        } elseif ((isset($product['featured']) && $product['featured']) || (isset($product['is_featured']) && $product['is_featured'])) {
                            $badge = 'Featured';
                        }
                        ?>
                        
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
                                echo htmlspecialchars(substr($description, 0, 100)) . (strlen($description) > 100 ? '...' : ''); 
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
                                <a class="page-link" href="?<?php echo isset($_GET['id']) ? 'id=' . $_GET['id'] : 'name=' . urlencode($_GET['name']); ?>&page=<?php echo $page-1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo isset($_GET['id']) ? 'id=' . $_GET['id'] : 'name=' . urlencode($_GET['name']); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?<?php echo isset($_GET['id']) ? 'id=' . $_GET['id'] : 'name=' . urlencode($_GET['name']); ?>&page=<?php echo $page+1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <p><i class="fas fa-info-circle me-2"></i> No products found in this category.</p>
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <p>Start adding products to this category through the admin panel.</p>
                    <a href="admin/product-add.php" class="btn btn-primary mt-2">Add Products</a>
                <?php else: ?>
                    <p>Please check back later or explore other categories.</p>
                    <a href="index.php#categories" class="btn btn-primary mt-2">Browse Categories</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Category Page Styles */
    .category-header {
        padding: 60px 0;
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        position: relative;
        margin-bottom: 30px;
    }

    .category-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .category-header .breadcrumb {
        background-color: transparent;
        justify-content: center;
        margin: 0;
        padding: 0;
    }
    
    .category-header .breadcrumb-item, 
    .category-header .breadcrumb-item a {
        color: white;
    }
    
    .category-header .breadcrumb-item+.breadcrumb-item::before {
        color: rgba(255,255,255,0.6);
    }
    
    .category-description {
        margin-bottom: 30px;
    }
    
    .result-count {
        color: var(--text-light);
        margin-bottom: 20px;
    }
    
    .category-products {
        padding-bottom: 60px;
    }
    
    .section-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-dark);
    }
    
    /* Products Grid - reusing styles from index.php */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .pagination-wrapper {
        margin-top: 40px;
    }
    
    .page-link {
        color: var(--primary);
        border-color: var(--border-light);
    }
    
    .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    
    @media (max-width: 991px) {
        .category-header h1 {
            font-size: 2rem;
        }
        
        .section-title {
            font-size: 1.6rem;
        }
    }
    
    @media (max-width: 576px) {
        .category-header {
            padding: 40px 0;
        }
        
        .category-header h1 {
            font-size: 1.7rem;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>