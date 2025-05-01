<?php
// register.php
session_start();
include 'db_connect.php';

$errors = []; // Array to hold validation errors
$success_message = '';
$username = ''; // Keep values to repopulate form on error
$email = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Input Retrieval & Basic Sanitization ---
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = "Username must be 3-20 characters long and contain only letters, numbers, and underscores.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) { // Basic length check
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // --- Check if username or email already exists (if no validation errors so far) ---
    if (empty($errors)) {
        // Check Username
        $sql_check_user = "SELECT user_id FROM users WHERE username = ?";
        $stmt_check_user = $conn->prepare($sql_check_user);
        $stmt_check_user->bind_param("s", $username);
        $stmt_check_user->execute();
        $stmt_check_user->store_result(); // Needed for num_rows
        if ($stmt_check_user->num_rows > 0) {
            $errors[] = "Username already taken.";
        }
        $stmt_check_user->close();

        // Check Email (only if username was okay)
        if (empty($errors)) {
            $sql_check_email = "SELECT user_id FROM users WHERE email = ?";
            $stmt_check_email = $conn->prepare($sql_check_email);
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();
            if ($stmt_check_email->num_rows > 0) {
                $errors[] = "Email address already registered.";
            }
            $stmt_check_email->close();
        }
    }

    // --- Process Registration (if no errors) ---
    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT); // Use default strong hashing

        // Insert user into database using prepared statement
        $sql_insert = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        if ($stmt_insert) {
            $stmt_insert->bind_param("sss", $username, $email, $password_hash);
            if ($stmt_insert->execute()) {
                // Registration successful
                $success_message = "Registration successful! You can now log in.";
                // Optional: Redirect to login page immediately
                // header("Location: login.php?registered=success");
                // exit;
                 // Clear form fields on success
                 $username = '';
                 $email = '';
            } else {
                $errors[] = "Registration failed. Please try again later.";
                // Log the detailed error: error_log("Registration failed: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        } else {
             $errors[] = "An error occurred. Please try again.";
             // Log the detailed error: error_log("Prepare statement failed: " . $conn->error);
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aasha Plushies</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&family=Pacifico&display=swap" rel="stylesheet">
    <style>
        .auth-container { max-width: 450px; margin: 50px auto; padding: 30px; background-color: #fff; border-radius: var(--border-radius); box-shadow: var(--box-shadow); text-align: center; }
        .auth-container h1 { margin-bottom: 25px; font-size: 2.5em; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color); }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: var(--body-font); font-size: 1em; }
        .form-group input:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 5px var(--accent-color); }
        .btn-submit { background-color: var(--primary-color); color: white; padding: 12px 25px; border: none; border-radius: 25px; cursor: pointer; font-size: 1.1em; font-weight: 600; transition: background-color 0.3s ease; width: 100%; margin-top: 10px; }
        .btn-submit:hover { background-color: var(--secondary-color); }
        .form-link { margin-top: 20px; display: block; font-size: 0.95em; }
        .error-messages, .success-message { margin-bottom: 20px; padding: 15px; border-radius: 8px; text-align: left; }
        .error-messages ul { margin: 0; padding-left: 20px; }
        .error-messages { background-color: #fdd; color: #900; border: 1px solid #fbb; }
        .success-message { background-color: #dfd; color: #070; border: 1px solid #bdb; }
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
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h1>Create Account</h1>

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
                    <?php echo htmlspecialchars($success_message); ?> <a href="login.php">Click here to login</a>.
                </div>
            <?php endif; ?>

            <form action="register.php" method="post" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password (min. 6 characters)</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-submit">Register</button>
            </form>
            <a href="login.php" class="form-link">Already have an account? Login here</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Aasha Plushies. All rights reserved.</p>
    </footer>
</body>
</html>