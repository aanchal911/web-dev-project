<?php
// cart_logic.php
session_start(); // Start the session at the very beginning

include 'db_connect.php'; // Needed to potentially verify product IDs or fetch details if necessary

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array(); // Use an associative array: product_id => quantity
}

// --- Input Handling ---
$action = $_POST['action'] ?? $_GET['action'] ?? null; // Get action from POST or GET
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT)
             ?? filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT)
           ?? filter_input(INPUT_GET, 'quantity', FILTER_VALIDATE_INT);

// Default quantity to 1 if not specified for 'add' action
if ($action === 'add' && $quantity === null) {
    $quantity = 1;
}

// --- Cart Actions ---

if ($action && $product_id) {
    // Optional: Verify product exists and has stock before adding/updating
    // $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
    // $stmt->bind_param("i", $product_id);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // if ($result->num_rows > 0) {
    //     $product = $result->fetch_assoc();
    //     $available_stock = $product['stock_quantity'];
    //     // Proceed only if product exists...
    // } else {
    //     // Handle product not found error
    //     header('Location: products.php?error=product_not_found');
    //     exit;
    // }
    // $stmt->close();

    switch ($action) {
        case 'add':
            if ($quantity > 0) {
                // If item already in cart, increment quantity; otherwise, add it
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                // Optional: Check against available stock here
            }
            break;

        case 'update':
            if ($quantity !== null) {
                if ($quantity > 0) {
                    // Update quantity if item exists in cart
                    if (isset($_SESSION['cart'][$product_id])) {
                        $_SESSION['cart'][$product_id] = $quantity;
                        // Optional: Check against available stock here
                    }
                } else {
                    // If quantity is 0 or less, remove the item
                    unset($_SESSION['cart'][$product_id]);
                }
            }
            break;

        case 'remove':
            // Remove item if it exists in cart
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            break;
    }
}

// --- Redirect ---
// Redirect back to the cart page by default, or to products page after adding
if ($action === 'add') {
    // Redirect to products page or cart page after adding
    // header('Location: products.php?added=' . $product_id); // Option 1: Back to products
    header('Location: cart.php?added=' . $product_id); // Option 2: Go to cart
} else {
    // Redirect back to the cart page for update/remove actions
    header('Location: cart.php');
}
exit; // Important to prevent further script execution after redirection

?>