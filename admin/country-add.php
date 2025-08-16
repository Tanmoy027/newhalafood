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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        $error = 'Country name is required';
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
                if (!is_dir('../assets/img/countries/')) {
                    mkdir('../assets/img/countries/', 0777, true);
                }
                
                $new_file_name = time() . '_' . str_replace(' ', '_', $file_name);
                $upload_path = '../assets/img/countries/' . $new_file_name;
                
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
            // Check if countries table exists
            $table_exists_query = "SHOW TABLES LIKE 'countries'";
            $table_exists_result = $conn->query($table_exists_query);
            if ($table_exists_result->num_rows == 0) {
                // Create countries table
                $create_table_sql = "CREATE TABLE countries (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    image VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if (!$conn->query($create_table_sql)) {
                    die("Error creating countries table: " . $conn->error);
                }
            }
            
            // Insert country into database
            $sql = "INSERT INTO countries (name, description, image) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            // Check if preparation was successful
            if ($stmt === false) {
                $error = "Error in SQL preparation: " . $conn->error;
            } else {
                $stmt->bind_param("sss", $name, $description, $image_name);
                
                if ($stmt->execute()) {
                    $message = "Country added successfully!";
                    // Redirect to countries page after a delay
                    header("refresh:2;url=countries.php");
                } else {
                    $error = "Error: " . $stmt->error;
                }
                
                $stmt->close();
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
    <title>Add Country | Admin Dashboard</title>
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
            <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="countries.php" class="active"><i class="fas fa-globe"></i> Countries</a></li>
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
                <h2>Add New Country</h2>
            </div>
            
            <div>
                <a href="countries.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Countries</a>
            </div>
        </div>
        
        <!-- Add Country Form -->
        <div class="card">
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Country Name*</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Country Flag/Image</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        <small class="text-muted">Recommended size: 300x200 pixels</small>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Add Country</button>
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