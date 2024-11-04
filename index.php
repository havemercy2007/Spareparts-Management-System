<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['message'] = "You must log in to access this page."; // Informative message
    header("Location: login_register.php");
    exit();
}

// Include database connection
include 'db/db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Immediately terminate on connection failure
}

// Fetch parts from the database
$result = $conn->query("SELECT * FROM parts");
if (!$result) {
    die("Query failed: " . $conn->error); // Provide feedback if the query fails
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benz Car Spare Parts Kigali</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            <form class="d-flex" action="search.php" method="GET">
                <input class="form-control me-2" type="search" placeholder="Search" name="query" required>
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
            <ul class="navbar-nav ms-3">
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-5">
    <h1 class="text-center mb-4">Welcome to Benz Car Spare Parts Kigali</h1>

    <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header text-center bg-primary text-white">
                            <?= htmlspecialchars($row['part_name']); ?> <!-- Prevent XSS -->
                        </div>
                        <div class="card-body">
                            <p class="card-text">Description: <?= htmlspecialchars($row['description']); ?></p>
                            <p class="card-text">Price: $<?= htmlspecialchars($row['price']); ?></p>
                            <a href="part_details.php?id=<?= $row['id']; ?>" class="btn btn-success">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            No spare parts available at the moment. Please check back later.
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
