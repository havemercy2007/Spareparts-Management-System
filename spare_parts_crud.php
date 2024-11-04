<?php
session_start();
include 'db/db.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';
switch ($action) {
    case 'fetch':
        fetchParts($conn);
        break;
    case 'add':
        addPart($conn);
        break;
    case 'edit':
        editPart($conn);
        break;
    case 'delete':
        deletePart($conn);
        break;
    case 'get':
        getPart($conn);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function fetchParts($conn) {
    $stmt = $conn->prepare("SELECT * FROM parts");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['category']}</td>
                <td>{$row['price']}</td>
                <td>{$row['stock']}</td>
                <td>{$row['quantity']}</td>
                <td><img src='{$row['image']}' alt='Image' width='50'></td>
                <td>
                    <button class='btn btn-warning editBtn' data-id='{$row['id']}'>Edit</button>
                    <button class='btn btn-danger deleteBtn' data-id='{$row['id']}'>Delete</button>
                </td>
            </tr>";
    }
}

function addPart($conn) {
    // Validate inputs
    if (!validateInputs($_POST)) {
        echo json_encode(['error' => 'Invalid input data']);
        return;
    }

    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $quantity = $_POST['quantity'];
    $image = handleFileUpload($_FILES['image']);

    if (empty($image)) {
        echo json_encode(['error' => 'Failed to upload image']);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO parts (name, category, price, stock, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiis", $name, $category, $price, $stock, $quantity, $image);
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Part added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add part.']);
    }
}

function editPart($conn) {
    // Validate inputs
    if (!validateInputs($_POST)) {
        echo json_encode(['error' => 'Invalid input data']);
        return;
    }

    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $quantity = $_POST['quantity'];
    $image = handleFileUpload($_FILES['image'], true); // true to indicate edit

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE parts SET name=?, category=?, price=?, stock=?, quantity=?, image=? WHERE id=?");
    $stmt->bind_param("ssdiisi", $name, $category, $price, $stock, $quantity, $image, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Part updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update part.']);
    }
}

function deletePart($conn) {
    $id = $_POST['id'];
    if (!is_numeric($id)) {
        echo json_encode(['error' => 'Invalid ID']);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM parts WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Part deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete part.']);
    }
}

function getPart($conn) {
    $id = $_GET['id'];
    if (!is_numeric($id)) {
        echo json_encode(['error' => 'Invalid ID']);
        return;
    }

    $stmt = $conn->prepare("SELECT * FROM parts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Part not found.']);
    }
}

function validateInputs($data) {
    return isset($data['name']) && isset($data['category']) && is_numeric($data['price']) && 
           is_numeric($data['stock']) && is_numeric($data['quantity']);
}

function handleFileUpload($file, $isEdit = false) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileName = basename($file['name']);
        $uploadFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
            return $uploadFilePath; // Return the file path
        }
    }

    // If editing, return the current image path if no new image is uploaded
    return $isEdit ? $_POST['currentImage'] : null;
}
?>
