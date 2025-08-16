<?php
// Check if a session hasn't been started yet before calling session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define SITE_URL if not already defined
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    $path = str_replace('/includes', '', $path);
    define('SITE_URL', $protocol . $domain . $path);
}

// Define SITE_TITLE if not already defined
if (!defined('SITE_TITLE')) {
    define('SITE_TITLE', 'Halal Food');
}

// Set language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ja', 'id', 'ph'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$currentLang = $_SESSION['lang'];

// Check if current page is home page
$isHomePage = false;
$currentFile = basename($_SERVER['PHP_SELF']);
if ($currentFile == 'index.php') {
    $isHomePage = true;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_TITLE; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Inline Critical CSS -->
    <style>
        :root {
            --primary: #0a2d5e;       /* Navy Blue - changed from blue */
            --primary-dark: #051d41;  /* Darker navy - changed from blue */
            --primary-light: #e4e9f2; /* Light navy - changed from blue */
            --secondary: #D0104C;
            --accent: #FBB03B;
            --body-bg: #F9F8F4;
            --text-dark: #2F2F2F;
            --text-light: #757575;
            --border-light: #E7E5E0;
            --white: #FFFFFF;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --halal-green: #0a2d5e;   /* Changed to navy blue */
            --halal-dark-green: #051d41; /* Changed to darker navy blue */
            --halal-gold: #D4AF37;    /* Gold for logo text */
            --nav-color: #0a2d5e;     /* CHANGED: Navy Blue color for nav */
            --nav-color-dark: #051d41; /* CHANGED: Darker Navy blue for nav/footer */
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background-color: var(--body-bg);
            line-height: 1.6;
        }

        a {
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        a:hover {
            color: var(--primary-dark);
        }

        /* Top Bar Styles */
        .top-bar {
            background-color: var(--nav-color-dark); /* Darker navy */
            padding: 5px 0;
            color: var(--white);
            font-size: 0.85rem;
            display: none; /* Hide by default when page loads */
            transition: all 0.3s ease;
        }

        .top-bar.show {
            display: block;
        }

        .top-bar a {
            color: var(--white);
            margin-right: 15px;
            display: inline-flex;
            align-items: center;
        }

        .top-bar a:hover {
            color: var(--halal-gold); /* Gold for hover */
        }

        .top-bar a i {
            margin-right: 6px;
        }

        .language-selector .dropdown-toggle {
            color: var(--white);
            background: transparent;
            border: none;
            padding: 0;
            display: inline-flex;
            align-items: center;
        }

        .language-selector .dropdown-toggle::after {
            display: none;
        }

        .language-selector .dropdown-toggle i {
            margin-left: 5px;
        }

        /* Header Styles */
        .header-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .header-container.scrolled {
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        /* Main Header Styles */
        .main-header {
            padding: 12px 0;
            transition: all 0.3s ease;
            background-color: var(--white);
        }

        .header-container.scrolled .main-header {
            padding: 5px 0;
        }

        /* Islamic Style Logo - Now Navy Blue */
        .logo-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .islamic-logo {
            background-color: var(--halal-green); /* Navy blue for logo */
            border-radius: 15px;
            padding: 6px 18px; /* Default smaller size for inner pages */
            display: inline-block;
            position: relative;
            border: 2px solid var(--halal-gold); /* Gold border */
            text-decoration: none;
            transition: all 0.3s ease;
        }

        /* Larger logo only for home page */
        .is-homepage .islamic-logo {
            padding: 8px 22px;
            border-radius: 16px;
        }

        .header-container.scrolled .islamic-logo {
            padding: 4px 15px;
            border-radius: 12px;
        }

        .islamic-logo:before, .islamic-logo:after {
            content: "";
            position: absolute;
            width: 16px; /* Default smaller size for inner pages */
            height: 16px;
            border: 2px solid var(--halal-gold); /* Gold border */
            border-radius: 50%;
            background-color: var(--white);
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
        }

        /* Larger circles only for home page */
        .is-homepage .islamic-logo:before,
        .is-homepage .islamic-logo:after {
            width: 18px;
            height: 18px;
        }

        .header-container.scrolled .islamic-logo:before, 
        .header-container.scrolled .islamic-logo:after {
            width: 14px;
            height: 14px;
        }

        .islamic-logo:before {
            left: -8px;
            border-right-color: var(--halal-green); /* Navy blue border */
        }

        .islamic-logo:after {
            right: -8px;
            border-left-color: var(--halal-green); /* Navy blue border */
        }

        .logo-text {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .arabic-text {
            font-family: 'Amiri', serif;
            color: var(--halal-gold); /* Gold text */
            font-size: 1.4rem; /* Default smaller size for inner pages */
            font-weight: 700;
            margin-bottom: -5px;
            transition: all 0.3s ease;
        }

        /* Larger text only for home page */
        .is-homepage .arabic-text {
            font-size: 1.6rem;
        }

        .header-container.scrolled .arabic-text {
            font-size: 1.2rem;
        }

        .halal-food-text {
            font-family: 'Cinzel', serif;
            color: var(--halal-gold); /* Gold text */
            font-size: 1.2rem; /* Default smaller size for inner pages */
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        /* Larger text only for home page */
        .is-homepage .halal-food-text {
            font-size: 1.4rem;
        }

        .header-container.scrolled .halal-food-text {
            font-size: 1.1rem;
        }

        .decorative-line {
            height: 2px;
            width: 100%;
            background: var(--halal-gold); /* Gold line */
            position: relative;
            margin: 2px 0;
            transition: all 0.3s ease;
        }

        .header-container.scrolled .decorative-line {
            margin: 1px 0;
            height: 1px;
        }

        .decorative-line:before {
            content: "";
            position: absolute;
            height: 6px; /* Default smaller size for inner pages */
            width: 6px;
            background: var(--halal-gold); /* Gold dot */
            border-radius: 50%;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }

        /* Larger dot only for home page */
        .is-homepage .decorative-line:before {
            height: 8px;
            width: 8px;
        }

        .header-container.scrolled .decorative-line:before {
            height: 5px;
            width: 5px;
        }

        .search-form {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        .search-input {
            width: 100%;
            padding: 8px 15px;
            padding-right: 45px;
            border-radius: 50px;
            border: 1px solid var(--halal-gold); /* Gold border */
            background: linear-gradient(to right, #f9f9f9, #ffffff);
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .header-container.scrolled .search-input {
            padding: 6px 15px;
            padding-right: 40px;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(10, 45, 94, 0.2); /* Navy blue shadow */
            background: #fff;
            border-color: var(--halal-green);
        }

        .search-button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--nav-color); /* Navy blue background */
            color: var(--white);
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid var(--halal-green); /* Navy blue border */
        }

        .header-container.scrolled .search-button {
            width: 30px;
            height: 30px;
        }

        .search-button:hover {
            background: var(--nav-color-dark); /* Darker navy for hover */
        }

        /* Navigation Styles - Horizontal Navigation Bar - Navy Blue */
        .main-nav {
            background-color: var(--nav-color); /* Navy blue background */
            border-bottom: 1px solid var(--halal-gold); /* Gold border for contrast */
            transition: all 0.3s ease;
        }

        .header-container.scrolled .main-nav {
            border-bottom: none;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0;
            list-style: none;
            margin: 0;
            transition: all 0.3s ease;
        }

        .nav-menu > li {
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-menu > li > a {
            display: block;
            padding: 12px 20px;
            color: var(--white);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            font-family: 'Cinzel', serif;
            letter-spacing: 0.5px;
        }

        .header-container.scrolled .nav-menu > li > a {
            padding: 8px 20px;
        }

        .nav-menu > li > a:hover,
        .nav-menu > li.active > a {
            color: var(--halal-gold); /* Gold on hover */
        }

        .nav-menu > li.active > a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 20px;
            right: 20px;
            height: 3px;
            background: var(--halal-gold); /* Gold underline for better visibility on navy */
        }

        .nav-menu .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background-color: var(--white);
            min-width: 220px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 0 0 10px 10px;
            padding: 10px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 100;
            list-style: none;
            border: 1px solid var(--halal-green); /* Navy blue border */
        }

        .nav-menu > li:hover > .dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown li a {
            display: block;
            padding: 8px 20px;
            color: var(--text-dark);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .dropdown li a:hover {
            color: var(--primary); /* Navy blue on hover */
            background-color: var(--primary-light); /* Light navy background */
            padding-left: 25px;
        }

        /* Mobile Menu */
        .mobile-toggle {
            display: none;
        }
        
        /* Main content padding to account for fixed header */
        main {
            padding-top: 120px; /* Adjust this value based on your header height */
            transition: padding-top 0.3s ease;
        }
        
        .scrolled ~ main {
            padding-top: 90px; /* Smaller padding when header is compact */
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

        /* Left-side only blur gradient overlay - transparent not blue */
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
        @media (max-width: 991px) {
            .main-header .container {
                position: relative;
            }
            
            .nav-menu {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--nav-color); /* Navy blue background */
                flex-direction: column;
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
                padding: 15px 0;
                margin-top: 5px;
                display: none;
                align-items: stretch;
                border-radius: 0 0 10px 10px;
                border: 1px solid var(--halal-gold); /* Gold border for contrast */
                z-index: 1001;
            }
            
            .nav-menu.show {
                display: flex;
            }
            
            .nav-menu > li > a {
                padding: 10px 20px;
            }
            
            .nav-menu .dropdown {
                position: static;
                box-shadow: none;
                opacity: 1;
                visibility: visible;
                transform: none;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
                padding: 0;
                border: none;
            }
            
            .nav-menu > li.dropdown-open > .dropdown {
                max-height: 500px;
            }
            
            .mobile-toggle {
                display: block;
                background: transparent;
                border: none;
                padding: 5px;
                font-size: 1.5rem;
                color: var(--primary); /* Navy blue color */
                cursor: pointer;
            }
            
            .islamic-logo {
                padding: 5px 15px;
            }
            
            .is-homepage .islamic-logo {
                padding: 6px 18px;
            }
            
            .islamic-logo:before, .islamic-logo:after {
                width: 14px;
                height: 14px;
            }
            
            .is-homepage .islamic-logo:before,
            .is-homepage .islamic-logo:after {
                width: 16px;
                height: 16px;
            }
            
            .arabic-text {
                font-size: 1.2rem;
            }
            
            .is-homepage .arabic-text {
                font-size: 1.4rem;
            }
            
            .halal-food-text {
                font-size: 1rem;
            }
            
            .is-homepage .halal-food-text {
                font-size: 1.2rem;
            }
            
            .header-container.scrolled .arabic-text {
                font-size: 1.1rem;
            }
            
            .header-container.scrolled .halal-food-text {
                font-size: 0.9rem;
            }
            
            /* Adjust main content padding for mobile */
            main {
                padding-top: 100px;
            }
            
            .scrolled ~ main {
                padding-top: 80px;
            }
            
            /* Hero section mobile styles */
            .hero-section {
                height: 400px; /* Shorter on mobile */
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

        /* Scroll to top button */
        .scroll-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background-color: var(--nav-color); /* Navy blue background */
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
            border: 1px solid var(--halal-gold); /* Gold border for contrast */
        }
        
        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        .scroll-top:hover {
            background-color: var(--nav-color-dark); /* Darker navy for hover */
            transform: scale(1.1);
        }
        
        /* Admin link style */
        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
        }
        
        .admin-link a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: var(--nav-color); /* Navy blue background */
            color: var(--white);
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
            border: 1px solid var(--halal-gold); /* Gold border for contrast */
        }
        
        .admin-link a:hover {
            background-color: var(--nav-color-dark); /* Darker navy for hover */
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- Header Container - Fixed position -->
    <div class="header-container <?php if($isHomePage) echo 'is-homepage'; ?>">
        <!-- Top Bar -->
        <div class="top-bar show">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 d-flex align-items-center">
                        <a href="tel:+81312345678">
                            <i class="fas fa-phone-alt"></i> +81 3-1234-5678
                        </a>
                        <a href="mailto:info@halalfood.com">
                            <i class="far fa-envelope"></i> info@halalfood.com
                        </a>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <div class="language-selector dropdown">
                            <button class="dropdown-toggle" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php 
                                switch($currentLang) {
                                    case 'ja': echo '<i class="fas fa-globe me-1"></i> 日本語'; break;
                                    case 'id': echo '<i class="fas fa-globe me-1"></i> Bahasa Indonesia'; break;
                                    case 'ph': echo '<i class="fas fa-globe me-1"></i> Filipino'; break;
                                    default: echo '<i class="fas fa-globe me-1"></i> English';
                                }
                                ?> <i class="fas fa-chevron-down small"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
                                <li><a class="dropdown-item <?php if($currentLang == 'en') echo 'active'; ?>" href="?lang=en">English</a></li>
                                <li><a class="dropdown-item <?php if($currentLang == 'ja') echo 'active'; ?>" href="?lang=ja">日本語</a></li>
                                <li><a class="dropdown-item <?php if($currentLang == 'id') echo 'active'; ?>" href="?lang=id">Bahasa Indonesia</a></li>
                                <li><a class="dropdown-item <?php if($currentLang == 'ph') echo 'active'; ?>" href="?lang=ph">Filipino</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Header -->
        <header class="main-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-3 col-6">
                        <div class="logo-container">
                            <a href="<?php echo SITE_URL; ?>" class="islamic-logo">
                                <div class="logo-text">
                                    <div class="arabic-text">حلال</div>
                                    <div class="decorative-line"></div>
                                    <div class="halal-food-text">HALAL FOOD</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                  
                    
                    <div class="col-md-3 col-6 d-flex justify-content-end">
                        <button class="mobile-toggle d-lg-none" id="menuToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Navigation - Horizontal Navigation Bar -->
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu" id="mainNav">
                    <li<?php if($isHomePage) echo ' class="active"'; ?>><a href="index.php">HOME</a></li>
                    <li>
                        <a href="javascript:void(0);">CATEGORIES <i class="fas fa-chevron-down ms-1 small"></i></a>
                        <ul class="dropdown">
                            <li><a href="category.php?id=1">Asian Foods</a></li>
                            <li><a href="category.php?id=2">European Foods</a></li>
                            <li><a href="category.php?id=3">Middle Eastern Foods</a></li>
                            <li><a href="category.php?id=4">American Foods</a></li>
                            <li><a href="category.php?id=5">Beverages</a></li>
                            <li><a href="category.php?id=6">Spices & Herbs</a></li>
                        </ul>
                    </li>
                    <li<?php if($currentFile == 'countries.php') echo ' class="active"'; ?>><a href="countries.php">COUNTRIES</a></li>
                    <li<?php if($currentFile == 'bestsellers.php') echo ' class="active"'; ?>><a href="bestsellers.php">BEST SELLERS</a></li>
                    <li<?php if($currentFile == 'new-arrivals.php') echo ' class="active"'; ?>><a href="new-arrivals.php">NEW ARRIVALS</a></li>
                    <li<?php if($currentFile == 'products.php') echo ' class="active"'; ?>><a href="products.php">PRODUCTS</a></li>
                    <li<?php if($currentFile == 'contact.php') echo ' class="active"'; ?>><a href="contact.php">CONTACT</a></li>
                    <li<?php if(strpos($currentFile, 'admin') !== false) echo ' class="active"'; ?>><a href="admin/index.php"><i class="fas fa-lock me-1"></i> ADMIN</a></li>
                </ul>
            </div>
        </nav>
    </div>
    
    <!-- Scroll to top button -->
    <div class="scroll-top">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <main>
    <!-- Admin link for quick access -->
    <?php if (!isset($_SESSION['admin_id'])): ?>
    <div class="admin-link">
        <a href="<?php echo SITE_URL; ?>/admin/login.php" title="Admin Login">
            <i class="fas fa-user-shield"></i>
        </a>
    </div>
    <?php endif; ?>

    <!-- JavaScript for header scrolling effect -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const headerContainer = document.querySelector('.header-container');
        const topBar = document.querySelector('.top-bar');
        const scrollTop = document.querySelector('.scroll-top');
        const mainContent = document.querySelector('main');
        const initialScroll = window.scrollY;
        
        // On page load, check if we should show the top bar
        if (initialScroll > 50) {
            topBar.classList.remove('show');
            headerContainer.classList.add('scrolled');
        }
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.scrollY;
            
            // Add 'scrolled' class to header when scrolling down
            if (currentScroll > 50) {
                headerContainer.classList.add('scrolled');
                topBar.classList.remove('show');
                scrollTop.classList.add('show');
            } else {
                headerContainer.classList.remove('scrolled');
                topBar.classList.add('show');
                scrollTop.classList.remove('show');
            }
        });
        
        // Scroll to top functionality
        scrollTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                mainNav.classList.toggle('show');
            });
        }
    });
    </script>