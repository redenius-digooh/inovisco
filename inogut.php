<?php
$sql = "UPDATE buchung SET inovisco = 1 WHERE user = '" . $_POST['user'] 
            . "' AND angebot = '" . $_POST['angebot'] . "'";
$erg = mysqli_query($conn, $sql);