<?php
include 'db/db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? 0.0;
    $stock = $_POST['stock'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    $id = $_POST['id'] ?? null;

    // Validate required fields
    if (!$name || !$category || !$price || !$stock || !$quantity) {
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    // Image upload handling
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {
            $targetFilePath = $targetDir . uniqid("img_", true) . "." . $imageFileType;
            move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath);
            $imagePath = $targetFilePath;
        } else {
            $response['message'] = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
            echo json_encode($response);
            exit;
        }
    }

    // Insert or Update part
    if ($id) {
        $query = "UPDATE parts SET name=?, category=?, price=?, stock=?, quantity=?, image=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdissi", $name, $category, $price, $stock, $quantity, $imagePath, $id);
    } else {
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
