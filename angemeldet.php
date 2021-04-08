<?php
/*
 * Check whether someone is correctly logged into the system.
 */
session_start();
if (!isset($_SESSION['token_direct']) || !isset($_SESSION['user'])) {
	header('Location: /index.php');
	//header("Location: http://88.99.184.137/inovisco_direct/");
}
