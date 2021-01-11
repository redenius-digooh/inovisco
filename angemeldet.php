<?php
session_start();
if (!isset($_SESSION['token_direct'])) {
    header("Location: http://88.99.184.137/inovisco_direct/");
}
?>