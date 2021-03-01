<?php
header('Content-Encoding: UTF-8');
header("Content-type: application/x-msexcel; charset=utf-8");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=User_Detail.xls");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
require_once 'db.php';
$user = $_POST['user'];
$an = " AND angebot = " . $_POST['angebot'];

if ($_SESSION['company'] != 'DIGOOH' && $_SESSION['company'] != 'Update Test') {
    $whereuser = "user = '" . $user . "' AND";
}

$sql = "SELECT a.players, c.start_date, c.end_date "
        . "FROM playerbuchung AS a"
        . " LEFT JOIN player AS b ON a.players = b.id"
        . " LEFT JOIN buchung AS c ON a.angebot = c.angebot"
        . " WHERE a.angebot = " . $_POST['angebot'] . " ORDER BY b.name";
    $erg = mysqli_query($conn, $sql);
    while ($row2 = mysqli_fetch_array($erg)) {
        $playarr[] = $row2['players'];
        $start_date = $row2['start_date'];
        $end_date = $row2['end_date'];
    }
    $anz = count($playarr);
    
try {
    require_once __DIR__ .  '/vendor/autoload.php';
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'https://cms.digooh.com:8082/api/v1/campaigns/least',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'players' => $playarr
            ]
        ]
    );
    $body = $response->getBody();
    $data = json_decode((string) $body);

    for($i=0; $i<$anz; $i++) {
        $arr2[] = [$data->players[$i]->id => $data->players[$i]->free];
    }

    foreach($arr2 as $key => $value) {
        foreach($value as $key => $d) {
            $arr[$key] = $d;
        }
    }
    
}
catch (Exception $e) {
    echo $e->getMessage();
}
// get all bookings from the user
$sql = "SELECT id, start_date, end_date, play_times, kunde, name FROM buchung"
        . " WHERE " . $whereuser . " angebot = " 
            . $_POST['angebot'];
$db_erg = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array($db_erg)) {
    // get 
    $sql2 = "SELECT a.id, a.players, a.deleted, a.lfsph, b.custom_sn2, b.name AS"
        . " displayname FROM playerbuchung AS a"
        . " LEFT JOIN player AS b ON a.players = b.id WHERE (a.deleted IS NULL"
        . " OR a.deleted = 0) AND"
        . " a.angebot = " . $_POST['angebot'];
    $db_erg2 = mysqli_query($conn, $sql2);
    while ($row2 = mysqli_fetch_array( $db_erg2)) {
        $deleted = $row2['deleted'];
        $lfsph = $row2['lfsph'];
        $players = $row2['players'];
        $playarr[] = $row2['players'];
        $playerid = $row2['id'];
        $custom_sn2 = $row2['custom_sn2'];
        $displayname = $row2['displayname'];
        
        $id = $row['id'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $play_times = $row['play_times'];
        $kunde = $row['kunde'];
        $name = $row['name'];
    
        $anz = count($playarr);

        $lfsphjetzt = (int)$arr[$players] / 10;
        $restzeit = ($lfsphjetzt);

        if ($restzeit <= 0) {
            $problem = 1;
            $gesproblem = 1;
            $probleme[] = $id;
        }
        elseif ($restzeit < $play_times) {
            $problem = 1;
            $gesproblem = 1;
            $teilprobleme[] = $id;
            $anzeige = $restzeit;
        }
        else {
            $problem = 0;
            $anzeige = $play_times;
        }

        $aufdb[] = array('id' => $id, 'playerid' => $playerid,
            'players' => $players, 'problem' => $problem, 'start_date' =>
            $start_date, 'end_date' => $end_date, 'anzeige' => $anzeige, 
            'custom_sn2' => $custom_sn2, 'displayname' => $displayname, 'lfsph'
            => $lfsph, 'lfsphjetzt' => $lfsphjetzt);
    }
}

foreach ($aufdb as $key => $in) {
    $sql = "UPDATE buchung SET "
            . "export = 1"
            . " WHERE id = " . $in['id'];
    $erg = mysqli_query($conn, $sql);
    $sql = "UPDATE playerbuchung SET "
            . "lfsph = " . $in['lfsphjetzt'] . ","
            . "problem = " . $in['problem']
            . " WHERE id = " . $in['playerid'];
    $erg = mysqli_query($conn, $sql);
}

//set column headers
$columnHeader = '';
$columnHeader = "Displayname" . "\t" . "QID" . "\t" . "Einblendungen pro Stunde" . "\t";
$setData = '';
$upper = "Angebotsnummer" . "\t" . '"' . $_POST['angebot'] . '"' . "\n";
$upper .= "Kundenname" . "\t" . '"' . $kunde . '"' . "\n";
$upper .= "Kampagne" . "\t" . '"' . $name . '"' . "\n\n";

function umlauteumwandeln($str){
    $tempstr = Array("Ä" => "AE", "Ö" => "OE", "Ü" => "UE", "ä" => "ae", "ö" => "oe", "ü" => "ue", "ß" => "ss"); 
    return strtr($str, $tempstr);
}

foreach ($aufdb as $key => $inhalt) {
    $rowData = '';
    $value = '"' . umlauteumwandeln($inhalt['displayname']) . '"' . "\t";
    $value .= '"' . $inhalt['custom_sn2'] . '"' . "\t";
    $value .= '"' . $inhalt['anzeige'] . '"' . "\t";
    $rowData .= $value;
    $setData .= trim($rowData) . "\n";  
}

echo $upper . $columnHeader . "\n" . $setData . "\n";
?>