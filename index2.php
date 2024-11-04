<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login_register.php");
    exit();
}

include 'db/db.php';

// Fetch parts from the database
$result = $conn->query("SELECT * FROM parts");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
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
                <input class="form-control me-2" type="search" placeholder="Search" name="query">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
            <ul class="navbar-nav ms-3">
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <h4 class="text-primary">Categories</h4>
            <!-- Sidebar with Categories -->
            <ul class="list-group">
                <li class="list-group-item"><a href="#">Engine Parts</a></li>
                <li class="list-group-item"><a href="#">Body Parts</a></li>
                <li class="list-group-item"><a href="#">Accessories</a></li>
                <!-- Add other categories as needed -->
            </ul>
        </div>
        
        <!-- Parts Display -->
        <div class="col-lg-9">
            <h2 class="text-primary">Available Spare Parts</h2>
            <div class="row">
                <?php while ($part = $result->fetch_assoc()) { ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo $part['image_url']; ?>" class="card-img-top" alt="Spare Part Image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $part['name']; ?></h5>
                                <p class="card-text"><?php echo $part['description']; ?></p>
                                <p class="text-success">Price: <?php echo $part['price']; ?> RWF</p>
                                <a href="part_details.php?id=<?php echo $part['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
