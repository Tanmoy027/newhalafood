<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

// Get product details to delete image
$sql = "SELECT image FROM food_items WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Delete the product
$sql = "DELETE FROM food_items WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    // Delete product image if exists
    if (!empty($product['image']) && file_exists('../assets/img/foods/' . $product['image'])) {
        unlink('../assets/img/foods/' . $product['image']);
    }
    
    // Set success message in session
    $_SESSION['success_message'] = "Product deleted successfully!";
} else {
    $_SESSION['error_message'] = "Error deleting product: " . $stmt->error;
}

$stmt->close();

// Redirect back to products page
header('Location: products.php');
exit;
?>