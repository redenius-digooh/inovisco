<?php
/*
 * The data from the uploaded file is saved in the buchung table.
 */
require __DIR__ .  '/vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('uploadfiles/' 
        . $filename); 

$worksheet = $spreadsheet->getActiveSheet();
$i = 0;
$j = 1;

$sql = "SELECT MAX(angebot) AS id FROM buchung";
$db_erg = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array( $db_erg)) {
    $angebot = $row['id'] + 1;
}

$foundInCells = array();
if ($_POST['was'] == 1) {
    $searchValue = 'SDAW';
} else {
    $searchValue = 'QID';
}
foreach ($worksheet->getRowIterator() AS $row) {
    $highestRow = $worksheet->getHighestRow();
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(FALSE);

    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell) {
            if (strtolower($cell->getValue()) == strtolower($searchValue)) {
                $foundInCells = $cell->getCoordinate();
            }
        }
    }
}

$buchstabe = substr($foundInCells, 0, 1);
for ($row = 2; $row <= $highestRow; $row++){
    $cell = $worksheet->getCell($buchstabe . $row);
    $player = $cell->getValue();
    
    if ($j == 1) {
        $query = "INSERT INTO buchung (user, useremail, angebot, "
                . "upload) VALUES ('" . $_SESSION['user'] . "', '" 
                . $_SESSION['useremail'] . "', '" . $angebot . "', 1)";
        $erg = mysqli_query($conn, $query);
    }
    
    if ($_POST['was'] == 1) {
        $sql = "SELECT id FROM player WHERE custom_sn1 = " . $player;
    } else {
        $sql = "SELECT id FROM player WHERE custom_sn2 = " . $player;
    }
    $erg = mysqli_query($conn, $sql);
    while ($row2 = mysqli_fetch_array($erg)) {
        $players = $row2['id'];
    }

    if ($_POST['was'] == 1) {
        $query = "INSERT INTO playerbuchung (players, custom_sn1, angebot) VALUES ('" 
            . $players . "', '" . $player . "', '" . $angebot . "')";
    } else {
        $query = "INSERT INTO playerbuchung (players, custom_sn2, angebot) VALUES ('" 
            . $players . "', '" . $player . "', '" . $angebot . "')";
    }
    $erg = mysqli_query($conn, $query);
    
    $j++;
}
?>