<?php
// Load the most important files.
error_reporting(E_ALL & ~ E_NOTICE);
ini_set ('display_errors', 'On');

require_once 'angemeldet.php';
require_once 'datenbank.php';
mysqli_query($conn, "SET NAMES 'utf8'");
?>