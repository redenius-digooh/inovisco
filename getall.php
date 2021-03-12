<?php
// get all criteria
$sql = "SELECT id, name FROM criteria ORDER BY name";
$db_erg = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array( $db_erg)) {
    $kriterien[] = array('id' => $row['id'], 'name' => $row['name']);
    $kritarr[] = $row['name'];
}

// get all players
$sql = "SELECT id, name FROM player ORDER BY name";
$db_erg = mysqli_query($conn, $sql);
$play = array();
while ($row = mysqli_fetch_array( $db_erg)) {
    $players[] = array('id' => $row['id'], 'name' => $row['name']);
    $play[] = $row['name'];
}
?>