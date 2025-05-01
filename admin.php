<?php
// admin.php
session_start();
include 'db_connect.php';

// --- Authentication Check ---
// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=admin'); // Redirect back to login
    exit;
}

// --- Role Check Placeholder ---
// In a real app, you'd check if the logged-in user has admin privileges
// Example:
// $user_id = $_SESSION['user_id'];
// $sql_check_admin = "SELECT is_admin FROM users WHERE user_id = ?";
// $stmt_check_admin = $conn->prepare($sql_check_admin);
// $stmt_check_admin->bind_param("i", $user_id);
// $stmt_check_admin->execute();
// $result_admin = $stmt_check_admin->get_result();
// $user_data = $result_admin->fetch_assoc();
// if (!$user_data || $user_data['is_admin'] != 1) {
//     // Not an admin, redirect or show error
//     header('Location: index.html?error=unauthorized');
//     exit;
// }
// $stmt_check_admin->close();


$errors = [];
$success_message = '';

// --- Handle Form Submissions (Add/Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? null;

    // --- Add Product ---
    if ($action === 'add_product') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);
        $image_url = trim($_POST['image_url'] ?? ''); // Basic URL handling

        // Validation
        if (empty($name)) $errors[] = "Product name is required.";
        if ($price === false || $price < 0) $errors[] = "Valid price is required.";
        if ($stock_quantity === false || $stock_quantity < 0) $errors[] = "Valid stock quantity is required.";
        // Basic URL validation (optional, can be more robust)
        if (!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
           // $errors[] = "Invalid Image URL format."; // Allow relative paths too? Maybe remove strict validation for now.
        }


        if (empty($errors)) {
            $sql_add = "INSERT INTO products (name, description, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?)";
            $stmt_add = $conn->prepare($sql_add);
            if ($stmt_add) {
                $stmt_add->bind_param("ssdis", $name, $description, $price, $stock_quantity, $image_url);
                if ($stmt_add->execute()) {
                    $success_message = "Product added successfully!";
                } else {
                    $errors[] = "Failed to add product. Database error.";
                    // Log error: error_log("Admin Add Product failed: " . $stmt_add->error);
                }
                $stmt_add->close();
            } else {
                 $errors[] = "Database error preparing statement.";
                 // Log error: error_log("Admin Add Prepare failed: " . $conn->error);
            }
        }
    }

    // --- Delete Product ---
    elseif ($action === 'delete_product') {
        $product_id_to_delete = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if ($product_id_to_delete) {
            // Add CSRF token check here in production
            $sql_delete = "DELETE FROM products WHERE product_id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            if ($stmt_delete) {
                $stmt_delete->bind_param("i", $product_id_to_delete);
                if ($stmt_delete->execute()) {
                    if ($stmt_delete->affected_rows > 0) {
                        $success_message = "Product deleted successfully!";
                    } else {
                        $errors[] = "Product not found or already deleted.";
                    }
                } else {
                    $errors[] = "Failed to delete product. Database error.";
                     // Log error: error_log("Admin Delete Product failed: " . $stmt_delete->error);
                }
                $stmt_delete->close();
            } else {
                 $errors[] = "Database error preparing statement.";
                 // Log error: error_log("Admin Delete Prepare failed: " . $conn->error);
            }
        } else {
            $errors[] = "Invalid product ID for deletion.";
        }
    }
     // --- Update Product (Placeholder - Usually needs separate form/logic) ---
    // elseif ($action === 'update_product') {
    //     // Logic for updating would go here, typically involving fetching existing data,
    //     // displaying it in a form, and processing the update submission.
    //     $errors[] = "Update functionality not yet implemented.";
    // }
}

// --- Fetch Existing Products ---
$products = [];
$sql_fetch = "SELECT product_id, name, price, stock_quantity, image_url FROM products ORDER BY name ASC";
$result = $conn->query($sql_fetch);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} elseif (!$result) {
     $errors[] = "Error fetching products: " . $conn->error;
     // Log error
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Aasha Plushies</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&family=Pacifico&display=swap" rel="stylesheet">
    <style>
        /* Admin specific styles */
        .admin-container { max-width: 1000px; margin: 30px auto; padding: 20px; }
        .admin-section { background-color: #fff; padding: 25px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 30px; }
        .admin-section h2 { font-family: var(--heading-font); color: var(--primary-color); margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .admin-table th, .admin-table td { padding: 12px 10px; text-align: left; border-bottom: 1px solid var(--accent-color); font-size: 0.95em; }
        .admin-table th { background-color: var(--accent-color); color: var(--secondary-color); font-weight: 600; }
        .admin-table img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; vertical-align: middle; margin-right: 10px; }
        .admin-table .actions a, .admin-table .actions button { margin-right: 5px; padding: 4px 8px; font-size: 0.85em; text-decoration: none; border-radius: 4px; cursor: pointer; }
        .admin-table .actions .edit-btn { background-color: var(--secondary-color); color: white; border: none; }
        .admin-table .actions .delete-btn { background-color: #e74c3c; color: white; border: none; }
        .admin-table .actions .delete-btn:hover { background-color: #c0392b; }
        .admin-table .actions .edit-btn:hover { background-color: #b88eda; }

        /* Form styles (similar to auth forms) */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: var(--text-color); font-size: 0.9em; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: var(--body-font); font-size: 0.95em; }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .form-group input:focus, .form-group textarea:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 4px var(--accent-color); }
        .btn-submit { background-color: var(--primary-color); color: white; padding: 10px 20px; border: none; border-radius: 20px; cursor: pointer; font-size: 1em; font-weight: 600; transition: background-color 0.3s ease; margin-top: 10px; }
        .btn-submit:hover { background-color: var(--secondary-color); }

        /* Messages */
        .error-messages, .success-message { margin-bottom: 20px; padding: 12px; border-radius: 6px; text-align: left; font-size: 0.95em; }
        .error-messages ul { margin: 0; padding-left: 18px; }
        .error-messages { background-color: #fdd; color: #900; border: 1px solid #fbb; }
        .success-message { background-color: #dfd; color: #070; border: 1px solid #bdb; }

        /* Logout Link */
        .logout-link { display: inline-block; margin-left: 20px; color: #e74c3c; font-weight: 500; }
        .logout-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.html">Aasha Plushies (Admin)</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.html">View Site</a></li>
                <li><a href="admin.php" class="active">Manage Products</a></li>
                <!-- Add links to other admin sections if needed -->
                <li>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="logout.php" class="logout-link">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-container">
        <h1>Admin Panel - Product Management</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Add New Product Form -->
        <section class="admin-section">
            <h2>Add New Plushie</h2>
            <form action="admin.php" method="post">
                <input type="hidden" name="action" value="add_product">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="text" id="image_url" name="image_url" placeholder="e.g., images/teddy.jpg or https://...">
                    </div>
                </div>
                 <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <button type="submit" class="btn-submit">Add Product</button>
            </form>
        </section>

        <!-- List Existing Products -->
        <section class="admin-section">
            <h2>Existing Plushies</h2>
            <?php if (!empty($products)): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/default_plushie.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                <td class="actions">
                                    <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="edit-btn">Edit</a> <!-- Link to future edit page -->
                                    <form action="admin.php" method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </section>

    </main>

    <footer>
        <p>&copy; 2025 Aasha Plushies - Admin Area</p>
    </footer>
</body>
</html>