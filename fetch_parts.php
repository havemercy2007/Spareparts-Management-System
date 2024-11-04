<?php
include 'db/db.php';

// Fetch parts from the database
$stmt = $conn->prepare("SELECT * FROM parts");
$stmt->execute();
$result = $stmt->get_result();

$parts = [];
while ($row = $result->fetch_assoc()) {
    $parts[] = $row;
}

header('Content-Type: application/json');
echo json_encode($parts);
?>
