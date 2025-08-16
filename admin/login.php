<?php
session_start();

// Include database connection
require_once '../includes/db.php';

// Define SITE_URL if not already defined
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    $base_path = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
    define('SITE_URL', $protocol . $domain . $base_path);
}

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Option 1: Simple hardcoded authentication (for testing)
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = 'admin';
            
            header('Location: index.php');
            exit;
        } else {
            // Option 2: Database authentication (commented out for now)
            // Uncomment this when you have set up your admin_users table
            /*
            $sql = "SELECT id, username, password FROM admin_users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // For plain text password (not recommended for production)
                if ($password === $admin['password']) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    header('Location: index.php');
                    exit;
                }
                
                // For hashed password using password_verify (recommended)
                // if (password_verify($password, $admin['password'])) {
                //     $_SESSION['admin_id'] = $admin['id'];
                //     $_SESSION['admin_username'] = $admin['username'];
                //     
                //     header('Location: index.php');
                //     exit;
                // }
            }
            */
            
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Food Catalog</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #006E51;
            --primary-dark: #00584A;
        }
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h1 {
            color: var(--primary);
            font-size: 28px;
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h1>Food Catalog Admin</h1>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>" class="text-muted"><i class="fas fa-arrow-left me-1"></i> Back to Website</a>
        </div>
    </div>
</body>
</html>