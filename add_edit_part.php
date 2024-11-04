<?php
session_start();
include 'db/db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; // Get the ID if it exists
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $quantity = $_POST['quantity'];

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Validate and upload the image
        $targetDir = "uploads/";
        $targetFilePath = $targetDir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath);
            $imagePath = $targetFilePath;
        } else {
            $response['message'] = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
            echo json_encode($response);
            exit;
        }
    }

    if ($id) {
        // Update existing part
        $query = "UPDATE parts SET name=?, category=?, price=?, stock=?, quantity=?, image=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdissi", $name, $category, $price, $stock, $quantity, $imagePath, $id);
    } else {
        // Add new part
        $query = "INSERT INTO parts (name, category, price, stock, quantity, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdiss", $name, $category, $price, $stock, $quantity, $imagePath);
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Part saved successfully.";
    } else {
        $response['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
}
echo json_encode($response);
$conn->close();
?>
