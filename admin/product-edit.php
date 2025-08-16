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

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

// Get countries for dropdown
$sql_countries = "SELECT id, name FROM countries ORDER BY name";
$result_countries = $conn->query($sql_countries);

// Get product details
$sql = "SELECT * FROM food_items WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

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
        $image_name = $product['image']; // Keep existing image if no new one uploaded
        
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
                    // Delete old image if it exists
                    if (!empty($product['image']) && file_exists('../assets/img/foods/' . $product['image'])) {
                        unlink('../assets/img/foods/' . $product['image']);
                    }
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
                // Check which columns exist in the database
                $check_columns_sql = "SHOW COLUMNS FROM food_items";
                $columns_result = $conn->query($check_columns_sql);
                $columns = array();
                
                if ($columns_result) {
                    while($col = $columns_result->fetch_assoc()) {
                        $columns[$col['Field']] = true;
                    }
                }
                
                // Build the SQL update statement based on available columns
                $sql_parts = array();
                $types = "";
                $values = array();
                
                // Always include these fields
                $sql_parts[] = "name = ?";
                $types .= "s";
                $values[] = $name;
                
                $sql_parts[] = "description = ?";
                $types .= "s";
                $values[] = $description;
                
                // Only include image if set
                if (!empty($image_name)) {
                    $sql_parts[] = "image = ?";
                    $types .= "s";
                    $values[] = $image_name;
                }
                
                // Include category if column exists
                if (isset($columns['category'])) {
                    $sql_parts[] = "category = ?";
                    $types .= "s";
                    $values[] = $category;
                }
                
                // Include country_id if column exists
                if (isset($columns['country_id'])) {
                    $sql_parts[] = "country_id = ?";
                    $types .= "i";
                    $values[] = $country_id;
                }
                
                // Include feature flags if columns exist
                if (isset($columns['featured'])) {
                    $sql_parts[] = "featured = ?";
                    $types .= "i";
                    $values[] = $is_featured;
                } elseif (isset($columns['is_featured'])) {
                    $sql_parts[] = "is_featured = ?";
                    $types .= "i";
                    $values[] = $is_featured;
                }
                
                if (isset($columns['is_new_arrival'])) {
                    $sql_parts[] = "is_new_arrival = ?";
                    $types .= "i";
                    $values[] = $is_new_arrival;
                }
                
                if (isset($columns['is_bestseller'])) {
                    $sql_parts[] = "is_bestseller = ?";
                    $types .= "i";
                    $values[] = $is_bestseller;
                }
                
                // Add product ID to values
                $types .= "i";
                $values[] = $product_id;
                
                // Prepare and execute the SQL statement
                $sql = "UPDATE food_items SET " . implode(", ", $sql_parts) . " WHERE id = ?";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    // Dynamically bind parameters
                    $bind_params = array($types);
                    foreach($values as $key => $val) {
                        $bind_params[] = &$values[$key];
                    }
                    call_user_func_array(array($stmt, 'bind_param'), $bind_params);
                    
                    if ($stmt->execute()) {
                        $message = "Product updated successfully!";
                        // Reload product data
                        $sql = "SELECT * FROM food_items WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $product = $result->fetch_assoc();
                    } else {
                        $error = "Error: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    $error = "Error preparing statement: " . $conn->error;
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
    <title>Edit Product | Admin Dashboard</title>
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
        .product-image-preview {
            max-width: 200px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
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
                <h2>Edit Product</h2>
            </div>
            
            <div>
                <a href="products.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Products</a>
            </div>
        </div>
        
        <!-- Edit Product Form -->
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
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" name="category" id="category" class="form-control"
                                   value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>"
                                   placeholder="e.g., Asian Foods">
                        </div>
                        <div class="col-md-3">
                            <label for="country_id" class="form-label">Country</label>
                            <select name="country_id" id="country_id" class="form-select">
                                <option value="0">-- Select Country --</option>
                                <?php 
                                if ($result_countries && $result_countries->num_rows > 0) {
                                    while($country = $result_countries->fetch_assoc()) {
                                        $selected = ($country['id'] == $product['country_id']) ? 'selected' : '';
                                        echo "<option value='" . $country['id'] . "' $selected>" . htmlspecialchars($country['name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="5" class="form-control"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <?php if (!empty($product['image'])): ?>
                            <div>
                                <img src="../assets/img/foods/<?php echo $product['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-image-preview">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep the current image</small>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                                       <?php echo (isset($product['featured']) && $product['featured']) ? 'checked' : ((isset($product['is_featured']) && $product['is_featured']) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="is_featured">
                                    Featured Product
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_new_arrival" id="is_new_arrival" value="1"
                                       <?php echo (isset($product['is_new_arrival']) && $product['is_new_arrival']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_new_arrival">
                                    New Arrival
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_bestseller" id="is_bestseller" value="1"
                                       <?php echo (isset($product['is_bestseller']) && $product['is_bestseller']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_bestseller">
                                    Best Seller
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
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