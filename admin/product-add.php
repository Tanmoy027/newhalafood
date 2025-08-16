<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Get countries for dropdown
$sql_countries = "SELECT id, name FROM countries ORDER BY name";
$result_countries = $conn->query($sql_countries);

// First, check if we need to add the category column
$check_category_column = "SHOW COLUMNS FROM food_items LIKE 'category'";
$category_exists = $conn->query($check_category_column);

if ($category_exists->num_rows == 0) {
    // Category column doesn't exist, add it
    $add_category_sql = "ALTER TABLE food_items ADD COLUMN category VARCHAR(100) AFTER description";
    if (!$conn->query($add_category_sql)) {
        $error = "Failed to add category column: " . $conn->error;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $country_id = intval($_POST['country_id'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    
    // Validate inputs
    if (empty($name)) {
        $error = 'Product name is required';
    } else {
        // Handle file upload
        $image_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_extensions)) {
                // Create directory if it doesn't exist
                if (!is_dir('../assets/img/foods/')) {
                    mkdir('../assets/img/foods/', 0777, true);
                }
                
                $new_file_name = time() . '_' . str_replace(' ', '_', $file_name);
                $upload_path = '../assets/img/foods/' . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_name = $new_file_name;
                } else {
                    $error = 'Failed to upload image';
                }
            } else {
                $error = 'Only JPG, JPEG, PNG and GIF files are allowed';
            }
        }
        
        if (empty($error)) {
            try {
                // Insert product into database with all fields including category
                $sql = "INSERT INTO food_items (name, description, category, country_id, image, featured) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                if ($stmt === false) {
                    $error = "Error in SQL preparation: " . $conn->error;
                } else {
                    $stmt->bind_param("sssisi", $name, $description, $category, $country_id, $image_name, $is_featured);
                    
                    if ($stmt->execute()) {
                        $message = "Product added successfully!";
                        // Redirect to products page after a delay
                        header("refresh:2;url=products.php");
                    } else {
                        $error = "Error executing statement: " . $stmt->error;
                    }
                    
                    $stmt->close();
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #006E51;
            --primary-dark: #00584A;
            --secondary: #D0104C;
            --light-bg: #F5F5F5;
            --text-dark: #2F2F2F;
        }
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background: var(--primary);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            padding-top: 20px;
            transition: all 0.3s;
        }
        .sidebar-brand {
            padding: 15px 25px;
            margin-bottom: 20px;
        }
        .sidebar-brand h3 {
            font-size: 1.5rem;
            margin: 0;
        }
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        .sidebar-menu > li {
            margin-bottom: 5px;
        }
        .sidebar-menu > li > a {
            display: block;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu > li > a:hover, 
        .sidebar-menu > li > a.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar-menu > li > a > i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        .top-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .top-bar h2 {
            margin: 0;
            color: var(--text-dark);
            font-size: 1.5rem;
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        /* Mobile styles */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .main-content.active {
                margin-left: 250px;
            }
            .toggle-sidebar {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h3>Food Catalog</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="countries.php"><i class="fas fa-globe"></i> Countries</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <button class="btn toggle-sidebar d-md-none"><i class="fas fa-bars"></i></button>
                <h2>Add New Product</h2>
            </div>
            
            <div>
                <a href="products.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Products</a>
            </div>
        </div>
        
        <!-- Add Product Form -->
        <div class="card">
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Product Name*</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" name="category" id="category" class="form-control" placeholder="e.g., Asian Foods">
                        </div>
                        <div class="col-md-3">
                            <label for="country_id" class="form-label">Country</label>
                            <select name="country_id" id="country_id" class="form-select">
                                <option value="0">-- Select Country --</option>
                                <?php 
                                if ($result_countries && $result_countries->num_rows > 0) {
                                    while($country = $result_countries->fetch_assoc()) {
                                        echo "<option value='" . $country['id'] . "'>" . htmlspecialchars($country['name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="5" class="form-control"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1">
                                <label class="form-check-label" for="is_featured">
                                    Featured Product
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_new_arrival" id="is_new_arrival" value="1">
                                <label class="form-check-label" for="is_new_arrival">
                                    New Arrival
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_bestseller" id="is_bestseller" value="1">
                                <label class="form-check-label" for="is_bestseller">
                                    Best Seller
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar on mobile
            const toggleBtn = document.querySelector('.toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    mainContent.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>