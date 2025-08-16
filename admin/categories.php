<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $where_clause = "WHERE name LIKE '%$search%'";
}

// Check if categories table exists
$table_exists_query = "SHOW TABLES LIKE 'categories'";
$table_exists_result = $conn->query($table_exists_query);
if ($table_exists_result->num_rows == 0) {
    // Create categories table
    $create_table_sql = "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_table_sql)) {
        die("Error creating categories table: " . $conn->error);
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Check if category is used in food_items table
    $check_sql = "SELECT COUNT(*) as count FROM food_items WHERE category = (SELECT name FROM categories WHERE id = $id)";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $item_count = $check_result->fetch_assoc()['count'];
        
        if ($item_count > 0) {
            $_SESSION['error_message'] = "Cannot delete: This category is used by $item_count product(s).";
        } else {
            // Get image filename before deleting
            $img_query = "SELECT image FROM categories WHERE id = $id";
            $img_result = $conn->query($img_query);
            if ($img_result && $img_result->num_rows > 0) {
                $img_data = $img_result->fetch_assoc();
                $image = $img_data['image'];
                
                // Delete the physical image file if it exists
                if (!empty($image) && file_exists("../assets/img/categories/" . $image)) {
                    unlink("../assets/img/categories/" . $image);
                }
            }
            
            // Delete the record
            $delete_sql = "DELETE FROM categories WHERE id = $id";
            if ($conn->query($delete_sql)) {
                $_SESSION['success_message'] = "Category deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Error deleting category: " . $conn->error;
            }
        }
    } else {
        $_SESSION['error_message'] = "Error checking category usage: " . $conn->error;
    }
    
    // Redirect to refresh the page (to prevent duplicate deletes on refresh)
    header("Location: categories.php" . (!empty($search) ? "?search=" . urlencode($search) : ""));
    exit;
}

// Get total records for pagination
$sql_count = "SELECT COUNT(*) as total FROM categories $where_clause";
$result_count = $conn->query($sql_count);
$total_records = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get categories
$sql = "SELECT * FROM categories $where_clause ORDER BY name ASC LIMIT $offset, $limit";
$result = $conn->query($sql);

// Check for success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Admin Dashboard</title>
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
        .search-box {
            max-width: 300px;
        }
        .table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .no-image {
            width: 60px;
            height: 60px;
            background-color: #f0f0f0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            border-radius: 4px;
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
            <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
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
                <h2>Manage Categories</h2>
            </div>
            
            <div class="search-box">
                <form action="" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
        
        <!-- Action Button -->
        <div class="mb-4">
            <a href="category-add.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Add New Category
            </a>
        </div>
        
        <!-- Categories List -->
        <div class="card">
            <div class="card-body">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    // Count products in this category
                                    $product_count = 0;
                                    $count_sql = "SELECT COUNT(*) as count FROM food_items WHERE category = '" . $conn->real_escape_string($row['name']) . "'";
                                    $count_result = $conn->query($count_sql);
                                    
                                    if ($count_result && $count_result->num_rows > 0) {
                                        $product_count = $count_result->fetch_assoc()['count'];
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>";
                                    if (!empty($row['image']) && file_exists("../assets/img/categories/" . $row['image'])) {
                                        echo "<img src='../assets/img/categories/" . $row['image'] . "' alt='" . htmlspecialchars($row['name']) . "'>";
                                    } else {
                                        echo "<div class='no-image'>No Image</div>";
                                    }
                                    echo "</td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . (strlen($row['description'] ?? '') > 50 ? substr(htmlspecialchars($row['description']), 0, 50) . '...' : htmlspecialchars($row['description'] ?? '')) . "</td>";
                                    echo "<td>" . $product_count . "</td>";
                                    echo "<td>
                                            <a href='category-edit.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary' title='Edit'><i class='fas fa-edit'></i></a>
                                            <a href='categories.php?action=delete&id=" . $row['id'] . "' class='btn btn-sm btn-danger " . ($product_count > 0 ? 'disabled' : '') . "' " . 
                                            ($product_count > 0 ? "title='Cannot delete: category is in use'" : "onclick=\"return confirm('Are you sure you want to delete this category?');\" title='Delete'") . "><i class='fas fa-trash-alt'></i></a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No categories found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <!-- Empty/Starter Message -->
                <?php if ($total_records == 0): ?>
                <div class="text-center my-4">
                    <p>No categories have been added yet. Get started by adding your first category!</p>
                    <a href="category-add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add First Category
                    </a>
                </div>
                <?php endif; ?>
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