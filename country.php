<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Check if country ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: countries.php');
    exit;
}

$country_id = intval($_GET['id']);

// Get country details
$country_sql = "SELECT * FROM countries WHERE id = ?";
$stmt = $conn->prepare($country_sql);
$stmt->bind_param("i", $country_id);
$stmt->execute();
$country_result = $stmt->get_result();

if ($country_result->num_rows === 0) {
    header('Location: countries.php');
    exit;
}

$country = $country_result->fetch_assoc();
$stmt->close();

// Get foods from this country
$foods_sql = "SELECT * FROM food_items WHERE country_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($foods_sql);
$stmt->bind_param("i", $country_id);
$stmt->execute();
$foods_result = $stmt->get_result();
$stmt->close();

include 'includes/header.php';
?>

<section class="country-header" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo !empty($country['image']) && file_exists("assets/img/countries/" . $country['image']) ? "assets/img/countries/" . $country['image'] : "https://via.placeholder.com/1200x400?text=" . urlencode($country['name']); ?>')">
    <div class="container">
        <h1><?php echo htmlspecialchars($country['name']); ?> Foods</h1>
        <p>Discover authentic products and ingredients from <?php echo htmlspecialchars($country['name']); ?></p>
        
        <?php if(file_exists("assets/img/flags/" . strtolower($country['name']) . ".png")): ?>
            <div class="country-flag">
                <img src="assets/img/flags/<?php echo strtolower($country['name']); ?>.png" alt="<?php echo htmlspecialchars($country['name']); ?> Flag">
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($country['description'])): ?>
<section class="country-description-section">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h2>About <?php echo htmlspecialchars($country['name']); ?> Cuisine</h2>
                <p><?php echo nl2br(htmlspecialchars($country['description'])); ?></p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="foods-section">
    <div class="container">
        <h2 class="section-title">Foods from <?php echo htmlspecialchars($country['name']); ?></h2>
        
        <?php if ($foods_result && $foods_result->num_rows > 0): ?>
        <div class="foods-grid">
            <?php while($food = $foods_result->fetch_assoc()): ?>
            <div class="food-card">
                <div class="food-image">
                    <?php if ((isset($food['is_featured']) && $food['is_featured']) || (isset($food['featured']) && $food['featured'])): ?>
                        <div class="food-badge featured">Featured</div>
                    <?php elseif (isset($food['is_new_arrival']) && $food['is_new_arrival']): ?>
                        <div class="food-badge new">New</div>
                    <?php elseif (isset($food['is_bestseller']) && $food['is_bestseller']): ?>
                        <div class="food-badge bestseller">Best Seller</div>
                    <?php endif; ?>
                    
                    <?php if (!empty($food['image']) && file_exists("assets/img/foods/" . $food['image'])): ?>
                        <img src="assets/img/foods/<?php echo $food['image']; ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/300x200?text=<?php echo urlencode($food['name']); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
                    <?php endif; ?>
                </div>
                <div class="food-content">
                    <?php if (!empty($food['category'])): ?>
                        <span class="food-category"><?php echo htmlspecialchars($food['category']); ?></span>
                    <?php endif; ?>
                    
                    <h3 class="food-name"><?php echo htmlspecialchars($food['name']); ?></h3>
                    
                    <div class="food-description">
                        <?php 
                        if (!empty($food['description'])) {
                            echo htmlspecialchars(substr($food['description'], 0, 100)) . (strlen($food['description']) > 100 ? '...' : '');
                        } else {
                            echo "No description available.";
                        }
                        ?>
                    </div>
                    
                    <a href="product.php?id=<?php echo $food['id']; ?>" class="food-details-btn">View Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php else: ?>
        <div class="no-foods-found">
            <div class="alert alert-info">
                <p><i class="fas fa-info-circle me-2"></i> No foods found from <?php echo htmlspecialchars($country['name']); ?> yet.</p>
                <p>Check back later or explore other countries!</p>
            </div>
            <a href="countries.php" class="btn btn-primary mt-3">Browse Other Countries</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Country Header Styles */
    .country-header {
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        padding: 80px 0;
        margin-bottom: 40px;
        position: relative;
    }
    
    .country-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5));
    }
    
    .country-header .container {
        position: relative;
        z-index: 1;
    }
    
    .country-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .country-header p {
        font-size: 1.2rem;
        max-width: 700px;
        margin: 0 auto 20px;
        opacity: 0.9;
    }
    
    .country-header .country-flag {
        display: inline-block;
        background-color: white;
        padding: 3px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        margin-top: 20px;
    }
    
    .country-header .country-flag img {
        height: 40px;
        width: auto;
    }
    
    /* Country Description Section */
    .country-description-section {
        margin-bottom: 40px;
    }
    
    .country-description-section .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .country-description-section h2 {
        font-size: 1.8rem;
        color: #2F2F2F;
        margin-bottom: 15px;
    }
    
    .country-description-section p {
        color: #6c757d;
        line-height: 1.7;
    }
    
    /* Foods Section */
    .foods-section {
        padding-bottom: 80px;
    }
    
    .section-title {
        font-size: 2rem;
        font-weight: 700;
        color: #2F2F2F;
        margin-bottom: 30px;
        text-align: center;
        position: relative;
        padding-bottom: 15px;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 80px;
        height: 3px;
        background-color: #006E51;
        transform: translateX(-50%);
    }
    
    .foods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }
    
    /* Food Card Styles */
    .food-card {
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .food-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.12);
    }
    
    .food-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }
    
    .food-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .food-card:hover .food-image img {
        transform: scale(1.1);
    }
    
    .food-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        z-index: 2;
    }
    
    .food-badge.featured {
        background-color: #006E51;
    }
    
    .food-badge.new {
        background-color: #D0104C;
    }
    
    .food-badge.bestseller {
        background-color: #FF9500;
    }
    
    .food-content {
        padding: 20px;
    }
    
    .food-category {
        display: inline-block;
        color: #006E51;
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .food-name {
        font-size: 1.3rem;
        color: #2F2F2F;
        margin-bottom: 10px;
        font-weight: 600;
        line-height: 1.3;
    }
    
    .food-description {
        color: #6c757d;
        font-size: 0.95rem;
        margin-bottom: 15px;
        line-height: 1.5;
    }
    
    .food-details-btn {
        display: inline-block;
        background-color: #F1F9F7;
        color: #006E51;
        padding: 8px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .food-details-btn:hover {
        background-color: #006E51;
        color: white;
    }
    
    /* No Foods Found */
    .no-foods-found {
        text-align: center;
        padding: 40px 20px;
        max-width: 600px;
        margin: 0 auto;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 991px) {
        .country-header {
            padding: 60px 0;
        }
        
        .country-header h1 {
            font-size: 2rem;
        }
        
        .section-title {
            font-size: 1.7rem;
        }
    }
    
    @media (max-width: 576px) {
        .country-header {
            padding: 40px 0;
        }
        
        .country-header h1 {
            font-size: 1.8rem;
        }
        
        .foods-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
    }
</style>

<?php include 'includes/footer.php'; ?>