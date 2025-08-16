<?php 
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Get countries from database
$sql = "SELECT * FROM countries ORDER BY name ASC";
$result = $conn->query($sql);

include 'includes/header.php'; 
?>

<section class="page-header">
    <div class="container">
        <h1>Explore by Country</h1>
        <p>Discover authentic ingredients from around the world</p>
    </div>
</section>

<section class="countries-grid-section">
    <div class="container">
        <div class="countries-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($country = $result->fetch_assoc()) {
                    // Count the number of foods from this country
                    $count_sql = "SELECT COUNT(*) as count FROM food_items WHERE country_id = ?";
                    $count_stmt = $conn->prepare($count_sql);
                    $count_stmt->bind_param("i", $country['id']);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $food_count = ($count_result && $count_result->num_rows > 0) ? $count_result->fetch_assoc()['count'] : 0;
                    $count_stmt->close();
            ?>
                <div class="country-card">
                    <div class="country-image">
                        <?php if (!empty($country['image']) && file_exists("assets/img/countries/" . $country['image'])): ?>
                            <img src="assets/img/countries/<?php echo $country['image']; ?>" alt="<?php echo htmlspecialchars($country['name']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x300?text=<?php echo urlencode($country['name']); ?>" alt="<?php echo htmlspecialchars($country['name']); ?>">
                        <?php endif; ?>
                        <div class="country-flag">
                            <?php if (file_exists("assets/img/flags/" . strtolower($country['name']) . ".png")): ?>
                                <img src="assets/img/flags/<?php echo strtolower($country['name']); ?>.png" alt="<?php echo htmlspecialchars($country['name']); ?> Flag">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/30x20?text=<?php echo substr($country['name'], 0, 2); ?>" alt="<?php echo htmlspecialchars($country['name']); ?> Flag">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="country-content">
                        <h2><?php echo htmlspecialchars($country['name']); ?></h2>
                        <p><?php 
                            if (!empty($country['description'])) {
                                echo htmlspecialchars(substr($country['description'], 0, 80)) . (strlen($country['description']) > 80 ? '...' : '');
                            } else {
                                echo "Explore authentic foods from " . htmlspecialchars($country['name']) . ".";
                            }
                        ?></p>
                        <a href="country.php?id=<?php echo $country['id']; ?>" class="btn-view">View Foods (<?php echo $food_count; ?>)</a>
                    </div>
                </div>
            <?php
                }
            } else {
                // If no countries found in database, show placeholder countries
                $default_countries = [
                    ['id' => 1, 'name' => 'Japan', 'description' => 'Discover authentic Japanese ingredients and specialty soy sauces.'],
                    ['id' => 2, 'name' => 'Italy', 'description' => 'Experience authentic pastas, olive oils, and regional specialties.'],
                    ['id' => 3, 'name' => 'Spain', 'description' => 'From saffron to chorizo, explore the diverse flavors of Spain.'],
                    ['id' => 4, 'name' => 'Thailand', 'description' => 'Authentic Thai curry pastes, noodles, and specialty ingredients.'],
                    ['id' => 5, 'name' => 'France', 'description' => 'Gourmet French products from cheeses to specialty mustards.'],
                    ['id' => 6, 'name' => 'India', 'description' => 'Explore rich spices from different regions of India.']
                ];
                
                foreach ($default_countries as $country) {
            ?>
                <div class="country-card">
                    <div class="country-image">
                        <img src="assets/img/countries/<?php echo strtolower($country['name']); ?>.jpg" alt="<?php echo $country['name']; ?>" onerror="this.src='https://via.placeholder.com/400x300?text=<?php echo urlencode($country['name']); ?>';">
                        <div class="country-flag">
                            <img src="assets/img/flags/<?php echo strtolower($country['name']); ?>.png" alt="<?php echo $country['name']; ?> Flag" onerror="this.src='https://via.placeholder.com/30x20?text=<?php echo substr($country['name'], 0, 2); ?>';">
                        </div>
                    </div>
                    <div class="country-content">
                        <h2><?php echo $country['name']; ?></h2>
                        <p><?php echo $country['description']; ?></p>
                        <a href="country.php?id=<?php echo $country['id']; ?>" class="btn-view">View Foods</a>
                    </div>
                </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
</section>

<style>
    /* Page Header Styles */
    .page-header {
        background-color: #0a2d5e; /* Changed from #006E51 to Navy Blue */
        color: white;
        padding: 25px 0; /* Significantly reduced padding */
        text-align: center;
        margin-bottom: 20px; /* Reduced margin */
        background-image: linear-gradient(135deg, rgba(10,45,94,0.9) 0%, rgba(5,29,65,0.9) 100%), url('assets/img/world-map.png'); /* Changed from green to navy blue */
        background-size: cover;
        background-position: center;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .page-header h1 {
        font-size: 1.8rem; /* Significantly reduced font size */
        font-weight: 700;
        margin-bottom: 5px; /* Smaller margin */
    }
    
    .page-header p {
        font-size: 0.9rem; /* Significantly reduced font size */
        max-width: 600px;
        margin: 0 auto;
        opacity: 0.9;
    }
    
    /* Countries Grid Section */
    .countries-grid-section {
        padding-bottom: 40px; /* Reduced padding */
    }
    
    .countries-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); /* Smaller card width */
        gap: 15px; /* Significantly reduced gap */
    }
    
    /* Country Card Styles */
    .country-card {
        background-color: white;
        border-radius: 8px; /* Smaller radius */
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); /* Smaller shadow */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .country-card:hover {
        transform: translateY(-5px); /* Smaller hover effect */
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    
    .country-image {
        position: relative;
        height: 140px; /* Significantly reduced height */
        overflow: hidden;
    }
    
    .country-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .country-card:hover .country-image img {
        transform: scale(1.05);
    }
    
    .country-flag {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 28px; /* Smaller size */
        height: 18px; /* Smaller size */
        background-color: white;
        border-radius: 2px;
        padding: 1px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .country-flag img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .country-content {
        padding: 12px; /* Significantly reduced padding */
    }
    
    .country-content h2 {
        font-size: 1.1rem; /* Significantly reduced font size */
        font-weight: 600;
        margin-bottom: 5px;
        color: #2F2F2F;
    }
    
    .country-content p {
        color: #6c757d;
        margin-bottom: 10px;
        line-height: 1.4;
        font-size: 0.8rem; /* Significantly reduced font size */
    }
    
    .btn-view {
        display: inline-block;
        background-color: #0a2d5e; /* Changed from #006E51 to Navy Blue */
        color: white;
        padding: 5px 15px; /* Significantly reduced padding */
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.8rem; /* Significantly reduced font size */
        transition: all 0.3s ease;
    }
    
    .btn-view:hover {
        background-color: #051d41; /* Changed from #00584A to darker Navy Blue */
        color: white;
        transform: translateX(3px);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 991px) {
        .page-header {
            padding: 20px 0;
        }
        
        .page-header h1 {
            font-size: 1.6rem;
        }
        
        .countries-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
    }
    
    @media (max-width: 576px) {
        .page-header h1 {
            font-size: 1.4rem;
        }
        
        .countries-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .country-image {
            height: 10px;
        }
        
        .country-content h2 {
            font-size: 1rem;
        }
        
        .country-content p {
            font-size: 0.75rem;
        }
        
        .btn-view {
            padding: 4px 12px;
            font-size: 0.75rem;
        }
    }

    /* Extra small devices */
    @media (max-width: 400px) {
        .countries-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>