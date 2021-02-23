<?php
/*
 *  The data from the campaigns table are saved in the kampagne table.
 */
require_once 'angemeldet.php';
require_once 'datenbank.php';

require __DIR__ .  '/vendor/autoload.php';


$client = new \GuzzleHttp\Client();
$response = $client->get(
    'https://cms.digooh.com:8082/api/v1/campaigns',
    [
        'headers' => [
            'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]
    ]
);
$body = $response->getBody();echo"<pre>";
$data = json_decode((string) $body);

$anzahl = count($data);
//$player = $data->data[3]->players;

foreach ($data->data as $key => $value) {
    $value->players;
    $value->start_date;
    $value->end_date;
    $value->play_type;
    $value->play_times;
    $value->exclude_criteria;
    $player = '';
    $exclude = '';
    
    foreach ($value->players as $k => $wert) {
        if ($wert->id != '') {
            $player .= $wert->id . ", ";
        }
    }
    $play = substr($player, 0, -2);
    
    foreach ($value->exclude_criteria as $k => $wert) {
        if ($wert->id != '') {
            $exclude .= $wert->id . ", ";
        }
    }
    $exclu = substr($exclude, 0, -2);
    
    $arr[] = array('start_date' => $value->start_date, 'end_date' => 
        $value->end_date, 'play_type' => $value->play_type, 'play_times' => 
        $value->play_times, 'players' => $play, 'exclude_criteria' => $exclu);
    
    $sql = "INSERT INTO kampagne (start_date, end_date, play_type, play_times, 
        players, exclude_criteria) VALUES ('" . $value->start_date . "', '" . 
            $value->end_date . "', '" . $value->play_type . "', '" . 
            $value->play_times . "', '" . $play . "', '" . $exclu . "')";
echo $sql;
        if (mysqli_query($conn, $sql)) {
            
        }
}
print_r($arr);


?>
