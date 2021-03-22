<?php
session_start();
require_once 'datenbank.php';

$sql = "SELECT user FROM user";
$db_erg = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($db_erg)) {
    $userarr[] = $row['user'];
}
foreach ($userarr as $user) {
    echo " ";
    $sql = "SELECT a.start_date, a.end_date, a.angebot FROM buchung AS a"
        . " WHERE a.user = '" . $user . "'";
    $db_erg = mysqli_query($conn, $sql);
    $l = '';
    $send = '';
    while ($row = mysqli_fetch_array($db_erg)) {
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $angebot = $row['angebot'];
        $playarr = array();
        $sql = "SELECT b.lfsph, b.players FROM playerbuchung AS b"
            . " WHERE b.angebot = '" . $row['angebot'] . "'";
        $erg = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_array($erg)) {
            $playarr[] = $row['players'];
            $lfsph[$row['players']] = $row['lfsph'];
        }
        $anz = count($playarr);
        
        if ($playarr[0] != '' && $start_date >= date("Y-m-d")) {
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
                    $heute = round($data->players[$i]->free / 10);
                    $damals = $lfsph[$data->players[$i]->id];
                    if ($heute != '' && $damals != '' && $heute != $damals) {
                        $send = 1;
                    }
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
            
            if ($send == 1) {
                $sql = "SELECT email FROM user WHERE user = '" . $user . "'";
                $db_erg = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_array($db_erg)) {
                    $email = $row['email'];
                }
                $client = new \GuzzleHttp\Client();
                $response = $client->post(
                    'https://prod-32.westeurope.logic.azure.com:443/workflows/28cc2a299d344cf880ec0d53a920833a/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=geChVIWwW1Srt6mSevIgw-pdgQd-lAgO49zWqs0-hes',
                    [
                        'json' => [
                            'An' => $email,
                            'Kampagne' => $angebot
                        ]
                    ]
                );
                $body = $response->getBody();
            }
        }
    }
}