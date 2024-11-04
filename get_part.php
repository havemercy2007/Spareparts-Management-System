<?php
header('Content-Type: application/json');
include 'db/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure id is an integer

    $stmt = $conn->prepare("SELECT * FROM parts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $part = $result->fetch_assoc();
        echo json_encode($part);
    } else {
        echo json_encode(["error" => "Part not found"]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "ID parameter missing"]);
}
$conn->close();
?>
