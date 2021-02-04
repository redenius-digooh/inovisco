<?php
$sql = "SELECT id, display, kunde, name, start_date, end_date, play_times,"
        . " deleted, agentur, angebot, inovisco, digooh, einfrieren FROM buchung"
        . " WHERE user = '" . $user . "'" . $an;
$db_erg = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array( $db_erg)) {
    $display = $row['display'];
    $id = $row['id'];
    $alleid[] = $row['id'];
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $play_times = $row['play_times'];
    $name = $row['name'];
    $kunde = $row['kunde'];
    $deleted = $row['deleted'];
    $agentur = $row['agentur'];
    $angebot = $row['angebot'];
    $digooh = $row['digooh'];
    $inovisco = $row['inovisco'];
    $digooh = $row['digooh'];
    $einfrieren = $row['einfrieren'];

    require __DIR__ .  '/vendor/autoload.php';
    
    $sql = "SELECT name FROM player WHERE id = " . $display;
    $db = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array( $db)) {
        $displayname = $row['name'];
    }

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
                        'players' => $display
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
    
    mysqli_set_charset($conn,"utf8");
    
    $buchungen[] = array('agentur' => $agentur, 'name' => $name,
        'display' => $display, 'problem' => $problem, 'start_date' =>
        $start_date, 'end_date' => $end_date, 'id' => $id, 
        'deleted' => $deleted, 'restzeit' => $restzeit, 'lfsph' => $lfsph,
        'play_times' => $play_times, 'displayname' => $displayname,
        'inovisco' => $inovisco, 'digooh' => $digooh);
}