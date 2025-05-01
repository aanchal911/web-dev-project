<?php
// db_connect.php

// --- Database Configuration ---
// Replace with your actual database credentials
$db_host = 'localhost';     // Usually 'localhost' or an IP address
$db_user = 'your_db_username'; // Your MySQL username
$db_pass = 'your_db_password'; // Your MySQL password
$db_name = 'aasha_plushies'; // The database name you created

// --- Establish Connection ---
// Create connection using mysqli
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Check Connection ---
// Check if the connection was successful
if ($conn->connect_error) {
    // Connection failed: Stop script execution and display error
    // In a production environment, you might want to log this error instead of displaying it directly
    error_log("Database Connection Failed: " . $conn->connect_error); // Log the error
    die("Sorry, we're having trouble connecting to the database. Please try again later."); // User-friendly message
}

// --- Set Character Set ---
// Optional: Set the character set to utf8mb4 for better Unicode support
if (!$conn->set_charset("utf8mb4")) {
    // Log error if setting charset fails
    error_log("Error loading character set utf8mb4: " . $conn->error);
    // You might choose to proceed or die here depending on requirements
}

// --- Connection Successful ---
// If the script reaches here, the connection is successful.
// The $conn variable can now be used in other PHP files to interact with the database.
// Example: include 'db_connect.php'; $result = $conn->query("SELECT * FROM products");

// Note: It's generally good practice to close the connection when done,
// but PHP often handles this automatically at the end of script execution.
// Explicit closing: $conn->close(); (usually done at the end of the script that includes this file)

?>