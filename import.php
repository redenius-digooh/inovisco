<?php
/*
 * The data from the uploaded file is saved in the buchung table.
 */
require __DIR__ .  '/vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('uploadfiles/' 
        . $filename); 

$worksheet = $spreadsheet->getActiveSheet();
$i = 0;

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
            $query = "INSERT INTO buchung (user, players, display, angebot, "
                    . "upload) VALUES ('" . $_SESSION['user'] . "', '" . $player
                    . "', '" . $player . "', '" . $angebotsid . "', 1)";

            if (mysqli_query($conn, $query)) {
                unlink($filename);
            }
        }
    }
    $i++;
}
?>