<?php
$servername = "localhost";
$username = "root";
$password = "Tony1234567!@";
$dbname = "benz_spare_parts";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
