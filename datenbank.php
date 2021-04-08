<?php
/*
 * Access to the database.
 */
$servername = 'localhost';
$username = 'redenius';
$password = 'red11!';
$dbname = 'inovisco_direct';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die('Connection failed: ' . $conn->connect_error);
}
