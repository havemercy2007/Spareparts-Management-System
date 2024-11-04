<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

include 'db/db.php';

// Get the part ID from the URL
$part_id = isset($_GET['part_id']) ? intval($_GET['part_id']) : 0;
$part = null;

// Fetch part details from the database
if ($part_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM parts WHERE id = ?");
    $stmt->bind_param("i", $part_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $part = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Validate quantity (no limit, but it should be a positive integer)
        if ($quantity <= 0) {
            throw new Exception("Invalid quantity. Please enter a positive number.");
        }

        // Check if there is enough stock available
        if ($part['stock'] < $quantity) {
            throw new Exception("Insufficient stock available. You requested $quantity but only " . $part['stock'] . " is in stock.");
        }

        // Insert order into the orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, part_id, quantity, order_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $user_id, $part_id, $quantity);
        $stmt->execute();

        // Update the stock in the parts table
        $new_stock = $part['stock'] - $quantity;
        $stmt = $conn->prepare("UPDATE parts SET stock = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_stock, $part_id);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        // Redirect to confirmation page
        header("Location: order_confirmation.php?part_id=$part_id&quantity=$quantity");
        exit();

    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Spare Part - Benz Car Spare Parts Kigali</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Benz Spare Parts Kigali</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2>Order Spare Part</h2>
        
        <?php if ($part): ?>
            <h5><?= htmlspecialchars($part['name']) ?></h5>
            <p><strong>Category:</strong> <?= htmlspecialchars($part['category']) ?></p>
            <p><strong>Price:</strong> <?= htmlspecialchars($part['price']) ?> RWF</p>
            <p><strong>Stock:</strong> <?= htmlspecialchars($part['stock']) ?> available</p>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                </div>
                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger mt-3"><?= $error_message ?></div>
            <?php endif; ?>

        <?php else: ?>
            <p>Part not found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
