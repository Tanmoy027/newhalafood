<?php
// Include database connection if needed
// require_once 'db.php';

// Translation function
function __($key) {
    global $lang;
    
    if (isset($lang[$key])) {
        return $lang[$key];
    }
    
    // Fallback to the key itself if translation is not found
    return $key;
}

// Function to get featured foods (placeholder - replace with your actual DB query)
function getFeaturedFoods() {
    // This is just a placeholder - replace with your database logic
    return [
        [
            'id' => 1,
            'name' => 'Japanese Matcha Powder',
            'description' => 'Premium quality matcha powder from Kyoto, Japan. Perfect for lattes, baking, or traditional tea ceremonies.',
            'image' => 'matcha.jpg',
            'country_id' => 1,
            'country_name' => 'Japan'
        ],
        [
            'id' => 2,
            'name' => 'Italian Truffle Oil',
            'description' => 'Luxury white truffle infused olive oil from Tuscany. Add a gourmet touch to any dish.',
            'image' => 'truffle-oil.jpg',
            'country_id' => 2,
            'country_name' => 'Italy'
        ],
        [
            'id' => 3,
            'name' => 'Spanish Saffron',
            'description' => 'Premium La Mancha saffron threads. The world\'s highest quality saffron for authentic paella and other dishes.',
            'image' => 'saffron.jpg',
            'country_id' => 3,
            'country_name' => 'Spain'
        ],
        [
            'id' => 4,
            'name' => 'Thai Curry Paste Set',
            'description' => 'Authentic set of red, green, and yellow curry pastes made with traditional Thai ingredients.',
            'image' => 'curry-paste.jpg',
            'country_id' => 4,
            'country_name' => 'Thailand'
        ],
    ];
}

// Function to get all countries (placeholder - replace with your actual DB query)
function getAllCountries() {
    // This is just a placeholder - replace with your database logic
    return [
        [
            'id' => 1,
            'name' => 'Japan',
            'description' => 'Discover authentic Japanese ingredients from premium matcha to specialty soy sauces.',
            'image' => 'japan.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Italy',
            'description' => 'Experience the taste of Italy with authentic pastas, olive oils, and regional specialties.',
            'image' => 'italy.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Spain',
            'description' => 'From saffron to chorizo, explore the diverse flavors of Spanish cuisine.',
            'image' => 'spain.jpg'
        ],
        [
            'id' => 4,
            'name' => 'Thailand',
            'description' => 'Authentic Thai curry pastes, noodles, and specialty ingredients for Asian cooking.',
            'image' => 'thailand.jpg'
        ],
        [
            'id' => 5,
            'name' => 'France',
            'description' => 'Gourmet French products from artisanal cheeses to specialty mustards and preserves.',
            'image' => 'france.jpg'
        ],
        [
            'id' => 6,
            'name' => 'India',
            'description' => 'Explore the rich spices and authentic ingredients from different regions of India.',
            'image' => 'india.jpg'
        ],
    ];
}

// Add other functions as needed for your site
?>