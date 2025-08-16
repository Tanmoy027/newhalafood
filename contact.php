<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Process contact form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Simple validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if contacts table exists, create if it doesn't
        $check_table = $conn->query("SHOW TABLES LIKE 'contacts'");
        
        if ($check_table->num_rows == 0) {
            // Create contacts table
            $create_table = "CREATE TABLE contacts (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(200) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->query($create_table);
        }
        
        // Save contact message to database
        $sql = "INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success_message = 'Your message has been sent successfully. We will get back to you soon!';
            
            // Reset form fields after successful submission
            $name = $email = $subject = $message = '';
        } else {
            $error_message = 'Sorry, there was an error sending your message. Please try again later.';
        }
        
        $stmt->close();
    }
}

// Page title
$page_title = "Contact Us - Food Catalog";

include 'includes/header.php';
?>

<section class="page-header" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/img/contact-bg.jpg');">
    <div class="container">
        <h1>Contact Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Contact</li>
            </ol>
        </nav>
    </div>
</section>

<section class="contact-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-5 mb-lg-0">
                <div class="contact-form-wrapper">
                    <h2 class="section-title">Get In Touch</h2>
                    <p class="section-desc mb-4">Have questions, suggestions, or feedback? Fill out the form below and we'll get back to you as soon as possible.</p>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="" class="contact-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Your Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="far fa-paper-plane me-2"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="contact-info">
                    <h2 class="section-title">Contact Information</h2>
                    <p class="section-desc mb-4">Feel free to contact us through any of the channels below.</p>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h5>Address</h5>
                            <p>1-2-3 Shibuya, Tokyo, Japan</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-content">
                            <h5>Phone Number</h5>
                            <p>+81 3-1234-5678</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="far fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h5>Email Address</h5>
                            <p>info@foodcatalog.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h5>Business Hours</h5>
                            <p>Mon-Sat: 10AM-8PM<br>Sun: 11AM-7PM</p>
                        </div>
                    </div>
                    
                    <div class="social-links mt-4">
                        <h5>Connect With Us</h5>
                        <div class="d-flex gap-3 mt-3">
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-pinterest-p"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container-fluid p-0">
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3241.3977861603725!2d139.69800807562816!3d35.66789587259338!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x60188ca7c2087f63%3A0x51f2962ea4abf516!2sShibuya%20Station!5e0!3m2!1sen!2sjp!4v1651234567890!5m2!1sen!2sjp" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<style>
    /* Page Header */
    .page-header {
        padding: 80px 0;
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        position: relative;
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .page-header .breadcrumb {
        background-color: transparent;
        justify-content: center;
        margin: 0;
        padding: 0;
    }
    
    .page-header .breadcrumb-item, 
    .page-header .breadcrumb-item a {
        color: white;
    }
    
    .page-header .breadcrumb-item+.breadcrumb-item::before {
        color: rgba(255,255,255,0.6);
    }
    
    /* Section Intro */
    .section-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
    }
    
    .section-desc {
        color: var(--text-light);
    }
    
    /* Contact Form */
    .contact-form-wrapper {
        background-color: var(--white);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .form-control {
        padding: 12px 15px;
        border-radius: 5px;
        border: 1px solid var(--border-light);
        background-color: var(--light-bg);
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(0, 110, 81, 0.25);
        border-color: var(--primary);
    }
    
    .btn-primary {
        padding: 10px 24px;
        border-radius: 5px;
        background-color: var(--primary);
        border-color: var(--primary);
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }
    
    /* Contact Info */
    .contact-info {
        background-color: var(--white);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .info-item {
        display: flex;
        margin-bottom: 25px;
    }
    
    .info-icon {
        width: 50px;
        height: 50px;
        background-color: var(--primary-light);
        color: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .info-content h5 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--text-dark);
    }
    
    .info-content p {
        margin-bottom: 0;
        color: var(--text-light);
        line-height: 1.5;
    }
    
    /* Social Links */
    .social-links h5 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--text-dark);
    }
    
    .social-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    
    .social-icon:hover {
        background-color: var(--primary);
        color: white;
    }
    
    /* Map Section */
    .map-section {
        margin-top: 50px;
    }
    
    .map-container {
        width: 100%;
        overflow: hidden;
    }
    
    /* Responsive Styles */
    @media (max-width: 991px) {
        .page-header {
            padding: 60px 0;
        }
        
        .page-header h1 {
            font-size: 2rem;
        }
        
        .section-title {
            font-size: 1.8rem;
        }
        
        .map-container iframe {
            height: 350px;
        }
    }
    
    @media (max-width: 576px) {
        .page-header {
            padding: 40px 0;
        }
        
        .page-header h1 {
            font-size: 1.7rem;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .map-container iframe {
            height: 300px;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>