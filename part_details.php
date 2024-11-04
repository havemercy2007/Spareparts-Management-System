<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['message'] = "You must log in to access this page.";
    header("Location: login_register.php");
    exit();
}

// Include database connection
include 'db/db.php';

// Validate and sanitize the part_id parameter
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $part_id = intval($_GET['id']); // Convert to integer for security
} else {
    die("Invalid part ID.");
}

// Prepare and execute the query to fetch part details
$stmt = $conn->prepare("SELECT * FROM parts WHERE id = ?");
$stmt->bind_param("i", $part_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the part exists
if ($result->num_rows === 0) {
    die("Part not found.");
}

// Fetch part details
$part = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($part['name']); ?> - Benz Car Spare Parts Kigali</title>
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

<!-- Main Content -->
<div class="container mt-5">
    <h1 class="text-center mb-4"><?= htmlspecialchars($part['name']); ?></h1>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Part Details
        </div>
        <div class="card-body">
            <img src="<?= htmlspecialchars($part['image']); ?>" alt="<?= htmlspecialchars($part['name']); ?>" class="img-fluid mb-3">
            <p><strong>Category:</strong> <?= htmlspecialchars($part['category']); ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($part['description']); ?></p>
            <p><strong>Price:</strong> $<?= htmlspecialchars($part['price']); ?></p>
            <p><strong>Stock:</strong> <?= htmlspecialchars($part['stock']); ?></p>
            <p><strong>Quantity Available:</strong> <?= htmlspecialchars($part['quantity']); ?></p>
            <a href="order.php?part_id=<?= $part['id']; ?>" class="btn btn-success">Order Now</a>
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
