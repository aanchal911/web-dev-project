<?php
// products.php
include 'db_connect.php'; // Establishes $conn

// Fetch products from the database
$sql = "SELECT product_id, name, description, price, image_url FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC"; // Only show in-stock items
$result = $conn->query($sql);

// Check for query errors (optional but good practice)
if (!$result) {
    // In production, log this error instead of showing it
    die("Error fetching products: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Plushies - Aasha Plushies</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&family=Pacifico&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.html">Aasha Plushies</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="products.php" class="active">Products</a></li> <!-- Mark as active -->
                <li><a href="login.php">Login</a></li>
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="product-grid-container">
            <h1>Our Adorable Plushies</h1>

            <div class="product-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($product = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/default_plushie.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 100)) . '...'; // Short description ?></p>
                            <p class="product-price">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                            <!-- Add to Cart Form (Functionality in later steps) -->
                            <form action="cart_logic.php" method="post">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <button type="submit" class="btn-add-to-cart">Add to Cart</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No plushies available at the moment. Check back soon!</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Aasha Plushies. All rights reserved.</p>
    </footer>

<?php
// Close the database connection
$conn->close();
?>
    <script src="script.js" defer></script>
</body>
</html>