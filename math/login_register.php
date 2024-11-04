<?php
include 'db/db.php';
session_start();

// Initialize error message
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Sanitize username input
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    if ($action === 'login') {
        // Login process
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "No user found with that username.";
        }
    } elseif ($action === 'register') {
        // Registration process
        $email = trim($_POST['email']);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Check for duplicate username
            $checkStmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                // Hash password and store user in database
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashedPassword, $email);

                if ($stmt->execute()) {
                    header("Location: login_register.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login/Register - Benz Car Spare Parts</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script>
        function toggleForm() {
            document.getElementById("loginForm").classList.toggle("d-none");
            document.getElementById("registerForm").classList.toggle("d-none");
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Error Display -->
        <?php if (!empty($error)) : ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login_register.php" id="loginForm" class="<?= isset($_POST['action']) && $_POST['action'] === 'register' ? 'd-none' : '' ?>">
            <h2>Login</h2>
            <input type="hidden" name="action" value="login">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="javascript:void(0);" onclick="toggleForm()">Register here</a></p>
        </form>

        <!-- Register Form -->
        <form method="POST" action="login_register.php" id="registerForm" class="<?= isset($_POST['action']) && $_POST['action'] === 'register' ? '' : 'd-none' ?>">
            <h2>Register</h2>
            <input type="hidden" name="action" value="register">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit">Register</button>
            <p>Already have an account? <a href="javascript:void(0);" onclick="toggleForm()">Login here</a></p>
        </form>
    </div>
</body>
</html>
