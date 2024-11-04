<?php
include 'db/db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM parts WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['message'] = "Database error: " . $stmt->error;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
