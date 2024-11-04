<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

include 'db/db.php';

// Handle AJAX requests for CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new part
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];

        $stmt = $conn->prepare("INSERT INTO parts (name, category, price, stock) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $name, $category, $price, $stock);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        exit();
    }

    // Edit existing part
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];

        $stmt = $conn->prepare("UPDATE parts SET name = ?, category = ?, price = ?, stock = ? WHERE id = ?");
        $stmt->bind_param("ssdii", $name, $category, $price, $stock, $id);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        exit();
    }

    // Delete part
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM parts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        exit();
    }
}

// Fetch spare parts for display
$result = $conn->query("SELECT * FROM parts");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Benz Car Spare Parts Kigali</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Benz Spare Parts Kigali</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2>Available Spare Parts</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add New Part</button>
        
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="partsList">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id'] ?>">
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['category'] ?></td>
                        <td><?= $row['price'] ?></td>
                        <td><?= $row['stock'] ?></td>
                        <td>
                            <button class="btn btn-info btn-sm edit-btn">Edit</button>
                            <button class="btn btn-danger btn-sm delete-btn">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addPartForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add Spare Part</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control mb-2" name="name" placeholder="Part Name" required>
                        <input type="text" class="form-control mb-2" name="category" placeholder="Category" required>
                        <input type="number" class="form-control mb-2" name="price" placeholder="Price" required>
                        <input type="number" class="form-control mb-2" name="stock" placeholder="Stock" required>
                        <input type="hidden" name="action" value="add">
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
            <form id="editPartForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Spare Part</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control mb-2" name="name" placeholder="Part Name" required>
                        <input type="text" class="form-control mb-2" name="category" placeholder="Category" required>
                        <input type="number" class="form-control mb-2" name="price" placeholder="Price" required>
                        <input type="number" class="form-control mb-2" name="stock" placeholder="Stock" required>
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Add new part
            $('#addPartForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "spare_parts.php",
                    data: $(this).serialize(),
                    success: function (response) {
                        location.reload();
                    }
                });
            });

            // Edit part
            $(document).on('click', '.edit-btn', function () {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const name = row.find('td:nth-child(1)').text();
                const category = row.find('td:nth-child(2)').text();
                const price = row.find('td:nth-child(3)').text();
                const stock = row.find('td:nth-child(4)').text();

                $('#editPartForm input[name="id"]').val(id);
                $('#editPartForm input[name="name"]').val(name);
                $('#editPartForm input[name="category"]').val(category);
                $('#editPartForm input[name="price"]').val(price);
                $('#editPartForm input[name="stock"]').val(stock);

                $('#editModal').modal('show');
            });

            // Save changes for editing
            $('#editPartForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "spare_parts.php",
                    data: $(this).serialize(),
                    success: function (response) {
                        location.reload();
                    }
                });
            });

            // Delete part
            $(document).on('click', '.delete-btn', function () {
                const row = $(this).closest('tr');
                const id = row.data('id');

                if (confirm('Are you sure you want to delete this part?')) {
                    $.ajax({
                        type: "POST",
                        url: "spare_parts.php",
                        data: { action: 'delete', id: id },
                        success: function (response) {
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
