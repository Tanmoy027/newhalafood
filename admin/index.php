<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once '../includes/db.php';

// Define SITE_URL if not already defined
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    $base_path = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
    define('SITE_URL', $protocol . $domain . $base_path);
}

// Get admin username
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Food Catalog</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #006E51;
            --primary-dark: #00584A;
            --light-bg: #f8f9fa;
        }
        body {
            background-color: var(--light-bg);
            padding: 20px;
        }
        .dashboard-header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        .logout-btn {
            background-color: rgba(255,255,255,0.2);
            color: white;
            border: none;
        }
        .logout-btn:hover {
            background-color: rgba(255,255,255,0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
            <h1>Admin Dashboard</h1>
            <div>
                <span class="me-3">Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
                <a href="logout.php" class="btn btn-sm logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card text-center">
                    <div class="card-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3>Products</h3>
                    <p>Manage your product catalog</p>
                    <a href="products.php" class="btn btn-primary">Manage</a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card text-center">
                    <div class="card-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3>Categories</h3>
                    <p>Manage product categories</p>
                    <a href="categories.php" class="btn btn-primary">Manage</a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card text-center">
                    <div class="card-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3>Countries</h3>
                    <p>Manage country listings</p>
                    <a href="countries.php" class="btn btn-primary">Manage</a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card text-center">
                    <div class="card-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h3>Settings</h3>
                    <p>Configure website settings</p>
                    <a href="settings.php" class="btn btn-primary">Manage</a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Website
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>