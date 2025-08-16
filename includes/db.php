<?php
// Database configuration from the image
$db_host = "db.us-losa1.bengt.wasmernet.com";
$db_port = 16751; // Port from the image
$db_user = "01f81ba975048000abe2718644e6";
$db_pass = "068a01f8-1ba9-76bd-8000-b4fac51ee30f";
$db_name = "food_catalog";

// Set connection timeout
ini_set('default_socket_timeout', 60);

// Create database connection with error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Use the port parameter in connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    
    // Set charset to avoid character encoding issues
    $conn->set_charset("utf8");
    
} catch (mysqli_sql_exception $e) {
    // Log the error with more details
    error_log("Database connection failed: " . $e->getMessage());
    error_log("Connection details - Host: $db_host, Port: $db_port, Database: $db_name");
    die("Database connection failed. Error: " . $e->getMessage());
}
?>