<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "Tony1234567!@";
$dbname = "benz_spare_parts";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve and sanitize form data
$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$message = htmlspecialchars($_POST['message']);

// Prepare and bind SQL statement
$stmt = $conn->prepare("INSERT INTO messages (name, email, message, submitted_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $name, $email, $message);

if ($stmt->execute()) {
    // Set email parameters
    $to = "info@benzsparepartskigali.com";
    $subject = "New Message from Contact Form";
    $headers = "From: $email\r\n" .
               "Reply-To: $email\r\n" .
               "Content-Type: text/html; charset=UTF-8";

    // Email body content
    $emailContent = "
        <html>
        <body>
            <h2>New Message from Contact Form</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Message:</strong></p>
            <p>$message</p>
        </body>
        </html>
    ";

    // Send email and check for errors
    if (mail($to, $subject, $emailContent, $headers)) {
        echo "Thank you for reaching out! Your message has been sent successfully.";
    } else {
        echo "Sorry, there was an error sending your message. Please try again.";
        error_log("Mail sending failed for contact form submission from: $email");
    }
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
