<?php
header('Content-Encoding: UTF-8');
header("Content-type: application/x-msexcel; charset=utf-8");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=User_Detail.xls");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'db.php';
$user = $_POST['user'];
$an = " AND angebot = " . $_POST['angebot'];
require_once 'buchungstabelle.php';

//set column headers
$columnHeader = '';
$columnHeader = "Displayname" . "\t" . "DisplayID" . "\t" . "verfügbare Einblendungen pro Stunde" . "\t";
$setData = '';
$upper = "Angebotsnummer" . "\t" . '"' . $angebot . '"' . "\n";
$upper .= "Kundenname" . "\t" . '"' . $kunde . '"' . "\n";
$upper .= "Kampagne" . "\t" . '"' . $name . '"' . "\n\n";

foreach ($buchungen as $key => $inhalt) {
    $rowData = '';
    $value = '"' . mb_convert_encoding($inhalt['displayname'], "UTF-8") . '"' . "\t";
    $value .= '"' . $inhalt['display'] . '"' . "\t";
    $value .= '"' . $inhalt['lfsph'] . '"' . "\t";
    $rowData .= $value;
    $setData .= trim($rowData) . "\n";  
}

//echo utf8_encode($columnHeader . "\n" . $setData . "\n");
echo $upper . $columnHeader . "\n" . $setData . "\n";
?>