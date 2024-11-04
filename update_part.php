<?php
session_start();
include 'db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Validate part ID
    $id = intval($_POST['id']);
    if ($id <= 0) {
        $errors[] = "Invalid part ID.";
    }

    // Validate part name
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = "Part name is required.";
    }

    // Validate category
    $category = trim($_POST['category']);
    if (empty($category)) {
        $errors[] = "Category is required.";
    }

    // Validate price
    $price = floatval($_POST['price']);
    if ($price <= 0) {
        $errors[] = "Price must be a positive number.";
    }

    // Validate stock
    $stock = intval($_POST['stock']);
    if ($stock < 0) {
        $errors[] = "Stock cannot be negative.";
    }

    // Validate quantity
    $quantity = intval($_POST['quantity']);
    if ($quantity < 0) {
        $errors[] = "Quantity cannot be negative.";
    }

    // Fetch current image path for the part
    $stmt = $conn->prepare("SELECT image FROM parts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($currentImagePath);
    $stmt->fetch();
    $stmt->close();

    // Image upload handling
    $newImagePath = $currentImagePath;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2 MB

        $fileInfo = pathinfo($_FILES['image']['name']);
        $fileExtension = strtolower($fileInfo['extension']);
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        } elseif ($_FILES['image']['size'] > $maxFileSize) {
            $errors[] = "Image size should not exceed 2 MB.";
        } else {
            // Generate a new unique filename
            $newImagePath = 'images/' . uniqid() . '.' . $fileExtension;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $newImagePath)) {
                $errors[] = "Failed to upload the new image.";
            } else {
                // Delete the old image if it exists
                if ($currentImagePath && file_exists($currentImagePath)) {
                    unlink($currentImagePath);
                }
            }
        }
    }

    // Proceed if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE parts SET name = ?, category = ?, price = ?, stock = ?, quantity = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssdissi", $name, $category, $price, $stock, $quantity, $newImagePath, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Part updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: Unable to update part.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
}

$conn->close();
?>
