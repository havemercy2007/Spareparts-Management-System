<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php"); // Redirect to login page
    exit();
}

// Include database connection
include 'db/db.php';

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Fetch parts from the database using prepared statements
$stmt = $conn->prepare("SELECT * FROM parts");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any parts were found
    if ($result->num_rows > 0) {
        // Fetch all parts and store them in an array
        $parts = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $parts = []; // No parts found
    }

    // Close the statement
    $stmt->close();
} else {
    // Handle query preparation error
    die("Query preparation failed: " . htmlspecialchars($conn->error));
}

// Close the database connection
$conn->close();
?>

<!-- HTML to display the fetched parts -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spare Parts List</title>
</head>
<body>
    <h1>List of Spare Parts</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Quantity</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($parts)): ?>
                <?php foreach ($parts as $part): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($part['id']); ?></td>
                        <td><?php echo htmlspecialchars($part['name']); ?></td>
                        <td><?php echo htmlspecialchars($part['category']); ?></td>
                        <td><?php echo htmlspecialchars($part['price']); ?></td>
                        <td><?php echo htmlspecialchars($part['stock']); ?></td>
                        <td><?php echo htmlspecialchars($part['quantity']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($part['image']); ?>" alt="<?php echo htmlspecialchars($part['name']); ?>" width="100"></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No parts found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
