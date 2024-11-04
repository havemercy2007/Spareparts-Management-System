<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['message'] = "You must log in to place an order.";
    header("Location: login_register.php");
    exit();
}

// Include database connection
include 'db/db.php';

// Validate and sanitize the part_id parameter
if (isset($_GET['part_id']) && is_numeric($_GET['part_id'])) {
    $part_id = intval($_GET['part_id']);
} else {
    die("Invalid part ID.");
}

// Fetch part details from the database
$stmt = $conn->prepare("SELECT * FROM parts WHERE id = ?");
$stmt->bind_param("i", $part_id);
$stmt->execute();
$part_result = $stmt->get_result();

if ($part_result->num_rows === 0) {
    die("Part not found.");
}

$part = $part_result->fetch_assoc();

// Handle form submission for placing an order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session upon login

    // Calculate total price
    $total_price = $part['price'] * $quantity;

    // Insert the order into the database
    $order_stmt = $conn->prepare("INSERT INTO orders (part_id, user_id, quantity, total_price, order_date) VALUES (?, ?, ?, ?, NOW())");
    $order_stmt->bind_param("iiid", $part_id, $user_id, $quantity, $total_price);

    if ($order_stmt->execute()) {
        $_SESSION['message'] = "Order placed successfully!";
        header("Location: order_confirmation.php?order_id=" . $order_stmt->insert_id);
        exit();
    } else {
        die("Order failed: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order <?= htmlspecialchars($part['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Benz Spare Parts Kigali</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="spare_parts.php">Spare Parts</a></li>
            </ul>
            <ul class="navbar-nav ms-3">
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Order Form -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Order <?= htmlspecialchars($part['name']); ?></h2>
    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Description:</strong> <?= htmlspecialchars($part['description']); ?></p>
            <p><strong>Price per unit:</strong> $<?= htmlspecialchars($part['price']); ?></p>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" min="1" max="<?= $part['stock']; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the statement and connection
$stmt->close();
$conn->close();
?>
