<?php
// cart.php
session_start();
include 'db_connect.php';

$cart_items = array();
$total_price = 0.00;
$product_ids = array();

// Check if cart exists and is not empty
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Get product IDs from cart session keys
    $product_ids = array_keys($_SESSION['cart']);

    // Prepare SQL query to fetch product details for items in cart
    // Use IN clause and placeholders for security
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?')); // e.g., ?,?,?
        $types = str_repeat('i', count($product_ids)); // e.g., iii

        $sql = "SELECT product_id, name, price, image_url FROM products WHERE product_id IN ($placeholders)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    $product_id = $product['product_id'];
                    $quantity = $_SESSION['cart'][$product_id]; // Get quantity from session
                    $subtotal = $product['price'] * $quantity;
                    $total_price += $subtotal;

                    // Store details for display
                    $cart_items[] = [
                        'id' => $product_id,
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image_url'] ?? 'images/default_plushie.png',
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    ];
                }
            }
            $stmt->close();
        } else {
            // Handle prepare statement error
             error_log("Error preparing statement for cart items: " . $conn->error);
             // Optionally display a user-friendly error
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Aasha Plushies</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&family=Pacifico&display=swap" rel="stylesheet">
    <style>
        /* Add specific cart styles here or in style.css */
        .cart-container { padding: 20px; background-color: #fff; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-top: 20px; }
        .cart-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .cart-table th, .cart-table td { padding: 15px 10px; text-align: left; border-bottom: 1px solid var(--accent-color); }
        .cart-table th { background-color: var(--accent-color); color: var(--secondary-color); font-weight: 600; }
        .cart-item-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-right: 15px; vertical-align: middle; }
        .cart-item-details { display: flex; align-items: center; }
        .cart-item-name { font-weight: 500; }
        .quantity-input { width: 50px; padding: 5px; text-align: center; margin-right: 10px; border: 1px solid #ddd; border-radius: 4px;}
        .btn-update, .btn-remove { padding: 5px 10px; font-size: 0.9em; cursor: pointer; border: none; border-radius: 5px; transition: background-color 0.3s ease; }
        .btn-update { background-color: var(--secondary-color); color: white; margin-left: 5px; }
        .btn-update:hover { background-color: #b88eda; }
        .btn-remove { background-color: #e74c3c; color: white; }
        .btn-remove:hover { background-color: #c0392b; }
        .cart-total { text-align: right; margin-top: 20px; font-size: 1.4em; font-weight: bold; color: var(--primary-color); }
        .cart-empty { text-align: center; padding: 40px; font-size: 1.2em; color: var(--text-color); }
        .checkout-button { display: block; width: fit-content; margin: 20px auto 0 auto; background-color: var(--primary-color); color: #fff; padding: 12px 30px; font-size: 1.1em; font-weight: 600; border-radius: 25px; transition: background-color 0.3s ease; text-align: center; }
        .checkout-button:hover { background-color: var(--secondary-color); color: #fff; }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.html">Aasha Plushies</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="cart.php" class="active">Cart</a></li> <!-- Mark as active -->
            </ul>
        </nav>
    </header>

    <main>
        <section class="cart-container">
            <h1>Your Shopping Cart</h1>

            <?php if (!empty($cart_items)): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th colspan="2">Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td style="width: 100px;">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-image">
                                </td>
                                <td>
                                    <span class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                </td>
                                <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                <td>
                                    <form action="cart_logic.php" method="post" style="display: inline-flex; align-items: center;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input" required>
                                        <button type="submit" class="btn-update">Update</button>
                                    </form>
                                </td>
                                <td>$<?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></td>
                                <td>
                                    <a href="cart_logic.php?action=remove&product_id=<?php echo $item['id']; ?>" class="btn-remove" onclick="return confirm('Remove this item?')">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-total">
                    Total: $<?php echo htmlspecialchars(number_format($total_price, 2)); ?>
                </div>

                <a href="checkout.php" class="checkout-button">Proceed to Checkout</a> <!-- Checkout page not yet created -->

            <?php else: ?>
                <p class="cart-empty">Your cart is empty. <a href="products.php">Go find some plushies!</a></p>
            <?php endif; ?>

        </section>
    </main>

    <footer>
        <p>&copy; 2025 Aasha Plushies. All rights reserved.</p>
    </footer>
    <script src="script.js" defer></script>
</body>
</html>