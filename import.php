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
    $angebotsid = $row['id'] + 1;
}

foreach ($worksheet->getRowIterator() AS $row) {
    if ($i > 0) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE);
        
        foreach ($cellIterator as $cell) {
            $player = $cell->getValue();
            
        if ($j == 1) {
            $query = "INSERT INTO buchung (user, angebot, "
                    . "upload) VALUES ('" . $_SESSION['user'] . "', '" 
                    . $angebotsid . "', 1)";

            if (mysqli_query($conn, $query)) {
                unlink($filename);
            }
        }
            $query = "INSERT INTO playerbuchung (players, angebot) VALUES ('" 
                    . $player . "', '" . $angebotsid . "')";

            $erg = mysqli_query($conn, $query);
            $j++;
        }
    }
    $i++;
}
?>