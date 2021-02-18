<?php
// get all from buchung
$sql = "SELECT id, kunde, name, start_date, end_date, play_times,"
        . " agentur, angebot, inovisco, digooh, einfrieren, kunde, name"
        . " FROM buchung"
        . " WHERE user = '" . $user . "'" . $an;
$db_erg = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array( $db_erg)) {
    // get player
    $sql2 = "SELECT id, players, deleted, lfsph FROM playerbuchung WHERE 1"
        . $an;
    $db_erg2 = mysqli_query($conn, $sql2);
    while ($row2 = mysqli_fetch_array( $db_erg2)) {
        $deleted = $row2['deleted'];
        $lfsph = $row2['lfsph'];
        $players = $row2['players'];
        $playerid = $row2['id'];
        
        $id = $row['id'];
        $alleid[] = $row['id'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $play_times = $row['play_times'];
        $agentur = $row['agentur'];
        $angebot = $row['angebot'];
        $digooh = $row['digooh'];
        $inovisco = $row['inovisco'];
        $digooh = $row['digooh'];
        $einfrieren = $row['einfrieren'];

        require __DIR__ .  '/vendor/autoload.php';

        $sql = "SELECT name FROM player WHERE id = " . $players;
        $db = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_array( $db)) {
            $displayname = $row['name'];
        }

        $client = new \GuzzleHttp\Client();

        // get entries from least
        if ($start_date != '' && $end_date >= date("Y-m-d")) {
            try {
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

        mysqli_set_charset($conn,"utf8");

        $buchungen[] = array('agentur' => $agentur,
            'players' => $players, 'problem' => $problem, 'start_date' =>
            $start_date, 'end_date' => $end_date, 'id' => $id, 
            'deleted' => $deleted, 'restzeit' => $restzeit, 'lfsph' => $lfsph,
            'play_times' => $play_times, 'displayname' => $displayname,
            'inovisco' => $inovisco, 'digooh' => $digooh);
    }
}