<?php
/*
 * The data from the uploaded file is saved in the buchung table.
 */
require __DIR__ .  '/vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('uploadfiles/' 
        . $filename); 

$data = array(1,$spreadsheet->getActiveSheet()->toArray(null,true,true,true)); 

$zahl = 1;
foreach($data as $key => $row) {
    if ($key != 0) {    
        $name  = $data[1][$zahl]['A'];
        $token_direct  = $data[1][$zahl]['B'];
        $players  = $data[1][$zahl]['C'];
        $start_date  = $data[1][$zahl]['D'];
        $end_date  = $data[1][$zahl]['E'];
        $play_times  = $data[1][$zahl]['F'];
        $campaign = $data[1][$zahl]['G'];
        $display = $data[1][$zahl]['H'];

        $arr = explode('/', $start_date);
        if (strlen($arr[0]) == 1) {
            $tagn = '0' . $arr[0];
            $arr[0] = $tagn;
        }
        if (strlen($arr[1]) == 1) {
            $monatn = '0' . $arr[1];
            $arr[1] = $monatn;
        }
        $tag = $arr[0];
        $monat = $arr[1];
        $jahr = $arr[2];
        $neu_start_date = $jahr . "-" . $tag . "-" . $monat;
        if (substr($neu_start_date, 0, 1) == '-') {
            $neu_start_date = substr($neu_start_date, 1);
        }
        if (substr($neu_start_date, -1) == '-') {
            $neu_start_date = substr($neu_start_date, 0, -1);
        }

        $arr = explode('/', $end_date);
        if (strlen($arr[0]) == 1) {
            $tagn = '0' . $arr[0];
            $arr[0] = $tagn;
        }
        if (strlen($arr[1]) == 1) {
            $monatn = '0' . $arr[1];
            $arr[1] = $monatn;
        }
        $tag = $arr[0];
        $monat = $arr[1];
        $jahr = $arr[2];
        $neu_end_date = $jahr . "-" . $tag . "-" . $monat;
        if (substr($neu_end_date, 0, 1) == '-') {
            $neu_end_date = substr($neu_end_date, 1);
        }
        if (substr($neu_end_date, -1) == '-') {
            $neu_end_date = substr($neu_end_date, 0, -1);
        }

        $query = "INSERT INTO buchung (user, name, campaign, display, players, "
                . "start_date, end_date, play_times) VALUES ('" 
                . $_SESSION['user'] . "', '" . $name . "', '" . $campaign 
                . "', '" . $display . "', '" . $players 
                . "', '" . $neu_start_date . "', '" . $neu_end_date . "', '" 
                . $play_times . "')";

        if (mysqli_query($conn, $query)) {
            unlink($filename);
        }
    }
    $zahl++;
}
?>