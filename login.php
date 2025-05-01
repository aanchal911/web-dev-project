<?php
// login.php
session_start(); // Start session to manage login state
include 'db_connect.php';

$errors = [];
$login_identifier = ''; // Can be username or email

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.html'); // Or account.php if you create one
    exit;
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_identifier = trim($_POST['login_identifier'] ?? ''); // Username or Email
    $password = $_POST['password'] ?? '';

    // --- Basic Validation ---
    if (empty($login_identifier)) {
        $errors[] = "Username or Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // --- Attempt Login (if no basic validation errors) ---
    if (empty($errors)) {
        // Prepare SQL to find user by username OR email
        $sql = "SELECT user_id, username, password_hash FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $login_identifier, $login_identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                // User found, fetch data
                $user = $result->fetch_assoc();

                // Verify the password
                if (password_verify($password, $user['password_hash'])) {
                    // Password is correct - Login successful!
                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Store user data in session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    // You might store other non-sensitive info if needed

                    // Redirect to a logged-in area (e.g., homepage or account page)
                    header('Location: index.html?login=success'); // Redirect to home
                    exit;
                } else {
                    // Invalid password
                    $errors[] = "Invalid username/email or password.";
                }
            } else {
                // User not found
                $errors[] = "Invalid username/email or password.";
            }
            $stmt->close();
        } else {
            $errors[] = "An error occurred during login. Please try again.";
            // Log the error: error_log("Login prepare statement failed: " . $conn->error);
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
    <title>Login - Aasha Plushies</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&family=Pacifico&display=swap" rel="stylesheet">
    <!-- Reusing styles from register.php -->
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
        .error-messages { margin-bottom: 20px; padding: 15px; border-radius: 8px; text-align: left; background-color: #fdd; color: #900; border: 1px solid #fbb; }
        .error-messages ul { margin: 0; padding-left: 20px; list-style: none; }
        .info-message { margin-bottom: 20px; padding: 15px; border-radius: 8px; text-align: left; background-color: #eef; color: #33a; border: 1px solid #cce; } /* For messages like 'Registration successful' */
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
                <li><a href="login.php" class="active">Login</a></li> <!-- Mark as active -->
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h1>Login</h1>

            <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
                <div class="info-message">
                    Registration successful! Please log in.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post" novalidate>
                <div class="form-group">
                    <label for="login_identifier">Username or Email</label>
                    <input type="text" id="login_identifier" name="login_identifier" value="<?php echo htmlspecialchars($login_identifier); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit">Login</button>
            </form>
            <a href="register.php" class="form-link">Don't have an account? Register here</a>
            <!-- Add forgot password link later if needed -->
            <!-- <a href="forgot_password.php" class="form-link">Forgot Password?</a> -->
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Aasha Plushies. All rights reserved.</p>
    </footer>
</body>
</html>