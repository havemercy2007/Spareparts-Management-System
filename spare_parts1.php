<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

include 'db/db.php';

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Fetch parts from the database using prepared statements
$stmt = $conn->prepare("SELECT * FROM parts");
$stmt->execute();
$result = $stmt->get_result();
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
    <script src="assets/js/main.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Benz Spare Parts Kigali</a>
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
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="query">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
            <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li></ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h2>Available Spare Parts</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add New Part</button>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="partsList">
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr data-id="<?= htmlspecialchars($row['id']) ?>">
                    <td><img src="images/<?= htmlspecialchars($row['image']) ?>" width="50" alt="Part Image"></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['price']) ?></td>
                    <td><?= htmlspecialchars($row['stock']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td>
                        <button class="btn btn-info btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" aria-label="Edit">Edit</button>
                        <button class="btn btn-danger btn-sm delete-btn" aria-label="Delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addPartForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add Spare Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-2" name="name" placeholder="Part Name" required>
                    <input type="text" class="form-control mb-2" name="category" placeholder="Category" required>
                    <input type="number" class="form-control mb-2" name="price" placeholder="Price" required min="0" step="0.01">
                    <input type="number" class="form-control mb-2" name="stock" placeholder="Stock" required min="0">
                    <input type="number" class="form-control mb-2" name="quantity" placeholder="Quantity" required min="0">
                    <input type="file" class="form-control mb-2" name="image" accept="image/*">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Part</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editPartForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Spare Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editPartId">
                    <input type="text" class="form-control mb-2" name="name" id="editPartName" placeholder="Part Name" required>
                    <input type="text" class="form-control mb-2" name="category" id="editPartCategory" placeholder="Category" required>
                    <input type="number" class="form-control mb-2" name="price" id="editPartPrice" placeholder="Price" required min="0" step="0.01">
                    <input type="number" class="form-control mb-2" name="stock" id="editPartStock" placeholder="Stock" required min="0">
                    <input type="number" class="form-control mb-2" name="quantity" id="editPartQuantity" placeholder="Quantity" required min="0">
                    <input type="file" class="form-control mb-2" name="image" id="editPartImage" accept="image/*">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Part</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // AJAX for adding a part
    $('#addPartForm').submit(async function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await $.ajax({
                url: 'add_part.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false
            });
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Error adding part!');
            }
        } catch (error) {
            alert('Error adding part!');
        }
    });

    // AJAX for editing a part
    $(document).on('click', '.edit-btn', function() {
        const row = $(this).closest('tr');
        $('#editPartId').val(row.data('id'));
        $('#editPartName').val(row.find('td:nth-child(2)').text());
        $('#editPartCategory').val(row.find('td:nth-child(3)').text());
        $('#editPartPrice').val(row.find('td:nth-child(4)').text());
        $('#editPartStock').val(row.find('td:nth-child(5)').text());
        $('#editPartQuantity').val(row.find('td:nth-child(6)').text());
    });

    $('#editPartForm').submit(async function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await $.ajax({
                url: 'edit_part.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false
            });
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Error updating part!');
            }
        } catch (error) {
            alert('Error updating part!');
        }
    });

    // AJAX for deleting a part
    $(document).on('click', '.delete-btn', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        if (confirm('Are you sure you want to delete this part?')) {
            $.ajax({
                url: 'delete_part.php',
                type: 'POST',
                data: { id },
                success: function(response) {
                    if (response.success) {
                        row.remove();
                    } else {
                        alert(response.message || 'Error deleting part!');
                    }
                },
                error: function() {
                    alert('Error deleting part!');
                }
            });
        }
    });
</script>
</body>
</html>
