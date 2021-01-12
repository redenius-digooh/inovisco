<?php
$servername = "localhost";
$username = "redenius";
$password = "red10?";
$dbname = "innovisco_direct";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>