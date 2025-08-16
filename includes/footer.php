<?php
// Check if current page is home page
$isHomePage = false;
$currentFile = basename($_SERVER['PHP_SELF']);
if ($currentFile == 'index.php') {
    $isHomePage = true;
}
?>
</main>
    
<!-- Footer -->
<footer class="footer <?php echo $isHomePage ? 'home-footer' : 'inner-footer'; ?>">
    <div class="footer-main">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <div class="footer-about">
                        <h4 class="footer-title">Food Catalog</h4>
                        <p class="footer-desc">
                            Discover quality food products from around the world. 
                            We source authentic ingredients to bring global flavors to your kitchen.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-pinterest-p"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="contact.php">About Us</a></li>
                        <li><a href="bestsellers.php">Best Sellers</a></li>
                        <li><a href="new-arrivals.php">New Arrivals</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h4 class="footer-title">Popular Categories</h4>
                    <ul class="footer-links">
                        <li><a href="category.php?id=1">Asian Foods</a></li>
                        <li><a href="category.php?id=2">European Foods</a></li>
                        <li><a href="category.php?id=3">Middle Eastern Foods</a></li>
                        <li><a href="category.php?id=4">American Foods</a></li>
                        <li><a href="category.php?id=5">Beverages</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h4 class="footer-title">Contact Info</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>1-2-3 Shibuya, Tokyo, Japan</span>
                        </li>
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <span>+81 3-1234-5678</span>
                        </li>
                        <li>
                            <i class="far fa-envelope"></i>
                            <span>info@foodcatalog.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Mon-Sat: 10AM-8PM, Sun: 11AM-7PM</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="copyright">
                        &copy; <?php echo date('Y'); ?> Food Catalog. All Rights Reserved.
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="payment-methods">
                        <span>Payment Methods:</span>
                        <?php
                        // Check if SITE_URL is defined, if not define it
                        if (!defined('SITE_URL')) {
                            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                            $domain = $_SERVER['HTTP_HOST'];
                            $path = dirname($_SERVER['PHP_SELF']);
                            define('SITE_URL', $protocol . $domain);
                        }
                        ?>
                        <img src="assets/img/payments.png" alt="Payment Methods" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<a href="#" class="back-to-top">
    <i class="fas fa-chevron-up"></i>
</a>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                mainNav.classList.toggle('show');
                
                // Toggle icon between bars and times
                if (mainNav.classList.contains('show')) {
                    menuToggle.innerHTML = '<i class="fas fa-times"></i>';
                } else {
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        }
        
        // Mobile Dropdown Toggle
        const hasDropdown = document.querySelectorAll('.nav-menu > li');
        
        hasDropdown.forEach(item => {
            if (item.querySelector('.dropdown')) {
                item.addEventListener('click', function(e) {
                    if (window.innerWidth < 992) {
                        // Don't follow the link for parent items with dropdowns on mobile
                        if (e.target.tagName === 'A' && e.target.nextElementSibling && e.target.nextElementSibling.classList.contains('dropdown')) {
                            e.preventDefault();
                        }
                        
                        this.classList.toggle('dropdown-open');
                    }
                });
            }
        });
        
        // Back to Top Button
        const backToTop = document.querySelector('.back-to-top');
        
        if (backToTop) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 200) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            });
            
            backToTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    });
</script>

<style>
    /* Additional Footer Styles */
    .footer {
        background-color: #252525; /* Original dark background color */
        color: #adb5bd;
        font-size: 0.9rem;
    }
    
    /* Home page (normal size) */
    .home-footer .footer-main {
        padding: 60px 0 40px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-main {
        padding: 30px 0 20px; /* Half the padding */
    }
    
    /* Home page (normal size) */
    .home-footer .footer-title {
        color: white;
        font-size: 1.2rem;
        margin-bottom: 20px;
        font-weight: 600;
        position: relative;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-title {
        font-size: 0.9rem; /* Smaller font */
        margin-bottom: 10px; /* Half the margin */
    }
    
    .footer-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -8px;
        width: 40px;
        height: 3px;
        background-color: var(--primary); /* Keep original accent color */
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-title::after {
        width: 30px; /* Smaller width */
        height: 2px; /* Smaller height */
        bottom: -5px; /* Closer to text */
    }
    
    /* Home page (normal size) */
    .home-footer .footer-desc {
        line-height: 1.7;
        margin-bottom: 20px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-desc {
        font-size: 0.75rem; /* Smaller font */
        line-height: 1.4; /* Reduced line height */
        margin-bottom: 10px; /* Half the margin */
    }
    
    /* Home page (normal size) */
    .home-footer .social-links {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .social-links {
        gap: 8px; /* Half the gap */
        margin-bottom: 15px; /* Half the margin */
    }
    
    /* Home page (normal size) */
    .home-footer .social-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .social-link {
        width: 24px; /* Smaller size */
        height: 24px; /* Smaller size */
        font-size: 0.7rem; /* Smaller font */
    }
    
    .social-link:hover {
        background-color: var(--primary); /* Keep original accent color */
        color: white;
        transform: translateY(-3px);
    }
    
    .inner-footer .social-link:hover {
        transform: translateY(-2px); /* Smaller hover effect */
    }
    
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    /* Home page (normal size) */
    .home-footer .footer-links li {
        margin-bottom: 12px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-links li {
        margin-bottom: 6px; /* Half the margin */
    }
    
    /* Home page (normal size) */
    .home-footer .footer-links a {
        color: #adb5bd;
        transition: all 0.3s ease;
        display: block;
        position: relative;
        padding-left: 15px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-links a {
        font-size: 0.75rem; /* Smaller font */
        padding-left: 10px; /* Smaller padding */
    }
    
    .footer-links a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: var(--primary); /* Keep original accent color */
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-links a::before {
        width: 4px; /* Smaller dot */
        height: 4px; /* Smaller dot */
    }
    
    .home-footer .footer-links a:hover {
        color: white;
        padding-left: 20px;
    }
    
    .inner-footer .footer-links a:hover {
        padding-left: 12px; /* Smaller hover effect */
    }
    
    .footer-links a:hover::before {
        opacity: 1;
    }
    
    .footer-contact {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    /* Home page (normal size) */
    .home-footer .footer-contact li {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-contact li {
        margin-bottom: 8px; /* Half the margin */
        font-size: 0.75rem; /* Smaller font */
    }
    
    /* Home page (normal size) */
    .home-footer .footer-contact li i {
        color: var(--primary); /* Keep original accent color */
        margin-right: 10px;
        margin-top: 5px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-contact li i {
        margin-right: 5px; /* Half the margin */
        margin-top: 2px; /* Half the margin */
        font-size: 0.7rem; /* Smaller font */
    }
    
    /* Home page (normal size) */
    .home-footer .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px 0;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .footer-bottom {
        padding: 10px 0; /* Half the padding */
    }
    
    /* Home page (normal size) */
    .home-footer .copyright {
        margin: 0;
        font-size: 0.9rem;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .copyright {
        font-size: 0.7rem; /* Smaller font */
    }
    
    /* Home page (normal size) */
    .home-footer .payment-methods {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .payment-methods {
        gap: 5px; /* Half the gap */
    }
    
    /* Home page (normal size) */
    .home-footer .payment-methods span {
        font-size: 0.8rem;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .payment-methods span {
        font-size: 0.65rem; /* Smaller font */
    }
    
    /* Home page (normal size) */
    .home-footer .payment-methods img {
        max-height: 24px;
    }
    
    /* Inner pages (smaller size - 2x smaller) */
    .inner-footer .payment-methods img {
        max-height: 18px; /* Smaller height */
    }
    
    .back-to-top {
        position: fixed;
        right: 20px;
        bottom: -50px;
        width: 40px;
        height: 40px;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        opacity: 0;
        z-index: 99;
    }
    
    .back-to-top.show {
        bottom: 20px;
        opacity: 1;
    }
    
    .back-to-top:hover {
        background-color: var(--primary-dark);
        color: white;
        transform: translateY(-5px);
    }
    
    @media (max-width: 991px) {
        .home-footer .footer-main {
            padding: 40px 0 20px;
        }
        
        .inner-footer .footer-main {
            padding: 20px 0 10px; /* Half the padding for mobile */
        }
        
        .payment-methods {
            justify-content: flex-start;
            margin-top: 15px;
        }
        
        .inner-footer .payment-methods {
            margin-top: 8px; /* Half the margin for mobile */
        }
    }
</style>
</body>
</html>