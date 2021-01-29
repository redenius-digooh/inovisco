<?php
/*
 * The data from the uploaded file is saved in the buchung table.
 */
require __DIR__ .  '/vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('uploadfiles/' 
        . $filename); 

$worksheet = $spreadsheet->getActiveSheet();
$i = 0;

foreach ($worksheet->getRowIterator() AS $row) {
    if ($i > 0) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE);
        foreach ($cellIterator as $cell) {
            $player = $cell->getValue();
            $query = "INSERT INTO buchung (user, players, display) VALUES ('" 
                    . $_SESSION['user'] . "', '" . $player . "', '" 
                    . $player . "')";

            if (mysqli_query($conn, $query)) {
                unlink($filename);
            }
        }
    }
    $i++;
}
?>