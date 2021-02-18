
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

// get all bookings from the user
$sql = "SELECT id, start_date, end_date, play_times, kunde, name FROM buchung"
        . " WHERE user = '" . $_POST['user'] . "' AND angebot = " 
            . $_POST['angebot'];
$db_erg = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array($db_erg)) {
    // get 
    $sql2 = "SELECT id, players, deleted, lfsph FROM playerbuchung WHERE"
        . " angebot = " . $_POST['angebot'];
    $db_erg2 = mysqli_query($conn, $sql2);
    while ($row2 = mysqli_fetch_array( $db_erg2)) {
        $deleted = $row2['deleted'];
        $lfsph = $row2['lfsph'];
        $players = $row2['players'];
        $playerid = $row2['id'];
        
        $id = $row['id'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $play_times = $row['play_times'];
        $kunde = $row['kunde'];
        $name = $row['name'];

        require_once __DIR__ .  '/vendor/autoload.php';

        $client = new \GuzzleHttp\Client();

        if ($start_date != '' && $end_date >= date("Y-m-d")) {
            try {
                // get entries from least
                $response = $client->post(
                    'https://cms.digooh.com:8081/api/v1/campaigns/least',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ],
                        'json' => [
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'players' => $players
                        ]
                    ]
                );
                $body = $response->getBody();
                $data = json_decode((string) $body);

                foreach ($data as $key => $value) {
                    $lfsph = $value / 10;
                }

            //    $restzeit = ($lfsph - $play_times);
                $restzeit = ($lfsph);
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }

            if ($restzeit <= 0) {
                $problem = 1;
                $gesproblem = 1;
                $probleme[] = $id;
            }
            elseif ($restzeit < $play_times) {
                $problem = 1;
                $gesproblem = 1;
                $teilprobleme[] = $id;
            }
            else {
                $problem = 0;
            }
        }

        $aufdb[] = array('id' => $id, 'playerid' => $playerid,
            'players' => $players, 'problem' => $problem, 'start_date' =>
            $start_date, 'end_date' => $end_date, 'lfsph' => $lfsph);
    }

    foreach ($aufdb as $key => $in) {
        $sql = "UPDATE buchung SET "
                . "export = 1"
                . " WHERE id = " . $in['id'];
        $erg = mysqli_query($conn, $sql);
        $sql = "UPDATE playerbuchung SET "
                . "lfsph = " . $in['lfsph'] . ","
                . "problem = " . $in['problem']
                . " WHERE id = " . $in['playerid'];
        $erg = mysqli_query($conn, $sql);
    }
}

require_once 'buchungstabelle.php';

//set column headers
$columnHeader = '';
$columnHeader = "Displayname" . "\t" . "DisplayID" . "\t" . "verfuegbare Einblendungen pro Stunde" . "\t";
$setData = '';
$upper = "Angebotsnummer" . "\t" . '"' . $_POST['angebot'] . '"' . "\n";
$upper .= "Kundenname" . "\t" . '"' . $kunde . '"' . "\n";
$upper .= "Kampagne" . "\t" . '"' . $name . '"' . "\n\n";

foreach ($buchungen as $key => $inhalt) {
    $rowData = '';
    $value = '"' . mb_convert_encoding($inhalt['displayname'], "UTF-8") . '"' . "\t";
    $value .= '"' . $inhalt['players'] . '"' . "\t";
    $value .= '"' . $inhalt['lfsph'] . '"' . "\t";
    $rowData .= $value;
    $setData .= trim($rowData) . "\n";  
}

//echo utf8_encode($columnHeader . "\n" . $setData . "\n");
echo $upper . $columnHeader . "\n" . $setData . "\n";
?>