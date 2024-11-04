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

// Handle form submission for adding or updating a part
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Set target directory
        $target_dir = "images/";
        $image = $target_dir . basename($_FILES["image"]["name"]);
        
        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $image)) {
            // File uploaded successfully
        } else {
            $_SESSION['message'] = "Sorry, there was an error uploading your file.";
            header("Location: spare_parts.php");
            exit();
        }
    }

    if (isset($_POST['part_id']) && !empty($_POST['part_id'])) {
        // Update existing part
        $part_id = intval($_POST['part_id']);
        $update_stmt = $conn->prepare("UPDATE parts SET name = ?, category = ?, price = ?, stock = ?, image = ? WHERE id = ?");
        $update_stmt->bind_param("ssdisi", $name, $category, $price, $stock, $image, $part_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Part updated successfully!";
        } else {
            $_SESSION['message'] = "Update failed: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        // Add new part
        $insert_stmt = $conn->prepare("INSERT INTO parts (name, category, price, stock, image) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssdis", $name, $category, $price, $stock, $image);
        
        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "Part added successfully!";
        } else {
            $_SESSION['message'] = "Insert failed: " . $conn->error;
        }
        $insert_stmt->close();
    }
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_stmt = $conn->prepare("DELETE FROM parts WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['message'] = "Part deleted successfully!";
    } else {
        $_SESSION['message'] = "Delete failed: " . $conn->error;
    }
    $delete_stmt->close();
}

// Fetch all parts from the database
$parts_query = $conn->query("SELECT * FROM parts");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Parts</title>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>


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

<!-- Messages -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-info text-center">
        <?= $_SESSION['message']; ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Part Form -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Manage Parts</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" id="part_id" name="part_id" value="">
        <div class="mb-3">
            <label for="name" class="form-label">Part Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" id="category" name="category" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" id="price" name="price" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="stock" class="form-label">Stock Quantity</label>
            <input type="number" id="stock" name="stock" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Upload Image</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Part</button>
    </form>
</div>

<!-- Parts Table -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Existing Parts</h2>
    <table class="table table-bordered" id="sparePartsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($part = $parts_query->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($part['id']); ?></td>
                    <td><?= htmlspecialchars($part['name']); ?></td>
                    <td><?= htmlspecialchars($part['category']); ?></td>
                    <td>$<?= htmlspecialchars($part['price']); ?></td>
                    <td><?= htmlspecialchars($part['stock']); ?></td>
                    <td><img src="<?= htmlspecialchars($part['image']); ?>" alt="<?= htmlspecialchars($part['name']); ?>" width="50"></td>
                    <td>
                        <a href="?edit_id=<?= $part['id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="?delete_id=<?= $part['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this part?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
// Pre-fill the form if editing a part
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_stmt = $conn->prepare("SELECT * FROM parts WHERE id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();

    if ($edit_result->num_rows > 0) {
        $part_to_edit = $edit_result->fetch_assoc();
        echo "<script>
                document.getElementById('part_id').value = " . json_encode($part_to_edit['id']) . ";
                document.getElementById('name').value = " . json_encode($part_to_edit['name']) . ";
                document.getElementById('category').value = " . json_encode($part_to_edit['category']) . ";
                document.getElementById('price').value = " . json_encode($part_to_edit['price']) . ";
                document.getElementById('stock').value = " . json_encode($part_to_edit['stock']) . ";
              </script>";
    }

    $edit_stmt->close();
}
?>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
       $(document).ready( function () {
             $('#sparePartsTable').DataTable();
            } );
</script>
</body>
</html>
