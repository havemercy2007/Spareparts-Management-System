<?php
session_start();
include 'db/db.php'; // Ensure this path is correct for your project structure

// Ensure user is logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Assumes `user_id` is stored in session

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);

    // Validate message length
    if (strlen($message) > 0 && strlen($message) <= 500) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO send_receiver (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        
        if ($stmt->execute()) {
            // Optional: Success message (could be done via AJAX for better UX)
            echo "<div class='alert alert-success'>Message sent successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to send message. Please try again.</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>Message must be between 1 and 500 characters.</div>";
    }
}

// Fetch messages sent to and from the logged-in user
$stmt = $conn->prepare("
    SELECT sr.*, u1.username AS sender_name, u2.username AS receiver_name
    FROM send_receiver sr
    JOIN users u1 ON sr.sender_id = u1.id
    JOIN users u2 ON sr.receiver_id = u2.id
    WHERE sr.sender_id = ? OR sr.receiver_id = ?
    ORDER BY sr.sent_at DESC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messaging - Benz Car Spare Parts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Benz Spare Parts Kigali</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="message.php">Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2>Messages</h2>

        <!-- Send Message Form -->
        <form method="POST" id="sendMessageForm" class="mb-4">
            <div class="mb-3">
                <label for="receiver_id" class="form-label">Recipient</label>
                <select name="receiver_id" id="receiver_id" class="form-control" required>
                    <!-- Populate with user list -->
                    <?php
                    $userQuery = $conn->query("SELECT id, username FROM users WHERE id != $user_id");
                    while ($user = $userQuery->fetch_assoc()) {
                        echo "<option value='{$user['id']}'>{$user['username']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea name="message" id="message" class="form-control" rows="3" required maxlength="500"></textarea>
                <small class="text-muted">Max 500 characters.</small>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>

        <!-- Display Messages -->
        <h3>Conversation</h3>
        <div class="list-group">
            <?php while ($row = $messages->fetch_assoc()): ?>
                <div class="list-group-item">
                    <strong><?php echo htmlspecialchars($row['sender_name']); ?> to <?php echo htmlspecialchars($row['receiver_name']); ?>:</strong>
                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                    <small class="text-muted"><?php echo $row['sent_at']; ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
