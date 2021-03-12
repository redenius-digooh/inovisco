<?php
$_POST['player'] = array();
            
    $d1 = substr($_POST['firstinput'], 3, 2);
    $m1 = substr($_POST['firstinput'], 0, 2);
    $y1 = substr($_POST['firstinput'], 6, 4);
    $d2 = substr($_POST['secondinput'], 3, 2);
    $m2 = substr($_POST['secondinput'], 0, 2);
    $y2 = substr($_POST['secondinput'], 6, 4);
        
    $start_date = $y1 . "-" . $m1 . "-" . $d1;
    $end_date = $y2 . "-" . $m2 . "-" . $d2;
    
    $sd = explode("/", $_POST['start_date']);
    $ed = explode("/", $_POST['end_date']);
    $checks = checkdate($m1,$d1,$y1);
    $checke = checkdate($m2,$d2,$y2);
    if (!$checks || !$checke) {
        $error = "Das Startdatum oder Enddatum war nicht korrekt!";
    }
    if ($_POST['play_times'] < 0 || $_POST['play_times'] > 360) {
        $error = 'Die "Einblendungen pro Stunde" m&uuml;ssen einen Wert zwischen'
                . " 0 und 360 haben!";
    }
    else {
        if ($_POST['sammelkriterium'] != '' || $_POST['criterien_alt'] != '') {
            if ($_POST['sammelkriterium'] == '') {
                $_POST['sammelkriterium'] = $_POST['criterien_alt'];
            }
            
            if ($_POST['bindkriterium'] != '') {
                $habbind = 1;
                $binarr = explode(", ", $_POST['bindkriterium']);
                $bind = array();
                if (is_array($binarr)) {
                    if (count($binarr) > 0 && $binarr[0] != '') {
                        foreach ($binarr as $bineri) {
                            // get criteria id with given name
                            $sql = "SELECT id FROM criteria WHERE name = '" . $bineri . "'";
                            $db_erg = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_array( $db_erg)) {
                                $bind[] = $row['id'];
                            }
                        }
                    }
                }
            }

            if ($_POST['auskriterium'] != '') {
                $habaus = 1;
                $ausarr = explode(", ", $_POST['auskriterium']);
                $aus = array();
                if (is_array($ausarr)) {
                    if (count($ausarr) > 0 && $ausarr[0] != '') {
                        foreach ($ausarr as $auseri) {
                            // get criteria id with given name
                            $sql = "SELECT id FROM criteria WHERE name = '" . $auseri . "'";
                            $db_erg = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_array( $db_erg)) {
                                $aus[] = $row['id'];
                            }
                        }
                    }
                }
            }
            
            if ($habbind == 1) {
                $bindstr = implode(", ", $bind);
            } else {
                $bindstr = $_POST['bindcriterien_alt'];
                if (substr($bindstr, -1, 1) == ',') {
                    $bindstr = substr($bindstr, 0, -1);
                }
                $bind = explode(", ", $bindstr);
            }

            if ($habaus == 1) {
                $ausstr = implode(", ", $aus);
                $ausstrk = $ausstr . ",";
            } else {
                $ausstr = $_POST['auscriterien_alt'];
                if (substr($ausstr, -1, 1) == ',') {
                    $ausstr = substr($ausstr, 0, -1);
                }
                $aus = explode(", ", $ausstr);
                $ausstrk = $ausstr . ",";
            }
            
            $inscria = "criterien = '" . $_POST['sammelkriterium'] . "', ";
            $kriarr = explode(", ", $_POST['sammelkriterium']);
            
            $krit = array();
            if (is_array($kriarr)) {
                if (count($kriarr) > 0 && $kriarr[0] != '') {
                    foreach ($kriarr as $kriteri) {
                        // find criteria id
                        $sql = "SELECT id FROM criteria WHERE name = '" . $kriteri . "'";
                        $db_erg = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_array( $db_erg)) {
                            $einzelkriterium = $row['id'] . ",";
                            $krit[] = $row['id'];
                        }

                        // all players of the criteria
                        $client = new \GuzzleHttp\Client();
                        $response = $client->get(
                            'https://cms.digooh.com:8082/api/v1/players',
                            [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                                    'Content-Type' => 'application/json',
                                    'Accept' => 'application/json',
                                ],
                                'query' => [
                                    'include'=> 'criteria',
                                    'filter[criteria]'=> $einzelkriterium,
                                    'filter[bind_criteria]'=> $bindstr,
                                    'filter[ex_criteria]'=> $ausstrk,
                                    'limit'=> '130'
                                ]
                            ]
                        );
                        $body = $response->getBody();
                        $data = json_decode((string) $body);

                        foreach ($data->data as $key => $value) {
                            $sql = "SELECT id FROM specialplayer WHERE id = '" . $value->id . "'";
                            $db = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_array( $db)) {
                                $_POST['player1'][] = $value->id;
                            }
                        }
                    }
                }
            }
            
            if ($_POST['pps1'] > 0 || $_POST['pps2'] > 0) {
                $_POST['player2'] = array();
                if ($_POST['pps1'] > 0) {
                    $ppsf = " WHERE a.pps >= " . $_POST['pps1'];
                    $pps = $_POST['pps1'];
                }

                if ($_POST['pps2'] > 0) {
                    $ppsf = " WHERE a.pps >= " . $_POST['pps2'];
                    $pps = $_POST['pps2'];
                }

                $sql = "SELECT a.id FROM player AS a"
                        . " LEFT JOIN specialplayer AS b ON a.id = b.id"
                        . $ppsf;
                $db = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_array($db)) {
                    if (!in_array($row['id'], $_POST['player2'])) {
                        $_POST['player2'][] = $row['id'];
                    }
                }
                $_POST['player'] = array_intersect($_POST['player1'], $_POST['player2']);
            } else {
                $pps = 0;
                $_POST['player'] = $_POST['player1'];
            }
            
            if ($_POST['player']) {
                // delete all players
                $del = "DELETE FROM playerbuchung WHERE angebot = " 
                        . $_POST['angebot'];
                $erg = @mysqli_query($conn, $del);
                    
                // insert all players for the offer
                foreach($_POST['player'] as $insplayer) {
                    $sql = "SELECT custom_sn1, custom_sn2 FROM player WHERE id "
                            ."= " . $insplayer;
                    $db = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_array($db)) {
                        $custom_sn1 = $row['custom_sn1'];
                        $custom_sn2 = $row['custom_sn2'];
                    }
                
                    $sql = "INSERT INTO playerbuchung (players, custom_sn1, "
                            . "custom_sn2, angebot)"
                        . " VALUES ("
                        . "'" . $insplayer . "', "
                        . "'" . $custom_sn1 . "', "
                        . "'" . $custom_sn2 . "', "
                        . "'" . $_POST['angebot'] . "')";
                    $erg = mysqli_query($conn, $sql);
                }
            }
        }
        
        if ($_POST['sammelplayer'] != '') {
            $_POST['player'] = explode(", ", $_POST['sammelplayer']);
            
            // delete all players
            $del = "DELETE FROM playerbuchung WHERE angebot = " 
                    . $_POST['angebot'];
            $erg = @mysqli_query($conn, $del);

            // insert all players for the offer
            foreach($_POST['player'] as $insplayer) {
                $sql = "SELECT id, custom_sn1, custom_sn2 FROM player WHERE "
                        . "name = '" . $insplayer . "'";
                $db_erg = mysqli_query($conn, $sql);
                while ($row2 = mysqli_fetch_array( $db_erg)) {
                    $playid = $row2['id'];
                    $custom_sn1 = $row2['custom_sn1'];
                    $custom_sn2 = $row2['custom_sn2'];
                }

                $sql = "INSERT INTO playerbuchung (players, custom_sn1, "
                        . "custom_sn2, angebot)"
                    . " VALUES ("
                    . "'" . $playid . "', "
                    . "'" . $custom_sn1 . "', "
                    . "'" . $custom_sn2 . "', "
                    . "'" . $_POST['angebot'] . "')";
                $erg = mysqli_query($conn, $sql);
            }
        }
        
        if ($krit[0] != '') {
            $kritstr = implode(", ", $krit);
        } else {
            $kritstr = $_POST['criterien_alt'];
        }
        
        // update booking
        $sql = "UPDATE buchung SET "
        . "start_date = '" . $start_date . "', "
        . "end_date = '" . $end_date . "', "
        . "play_times = '" . $_POST['play_times'] . "', "
        . "name = '" . $_POST['name'] . "', "
        . "agentur = '" . $_POST['agentur'] . "', "                
        . "text = '" . $_POST['text'] . "', "
        . "motive = '" . $_POST['motive'] . "', " 
        . "criterien = '" . $kritstr . "', "
        . "and_criteria = '" . $bindstr . "', "
        . "exclude_criteria = '" . $ausstr . "', "
        . "abnummer = '" . $_POST['abnummer'] . "', "
        . "pps = '" . $pps . "', "
        . "kunde = '" . $_POST['kunde'] . "' WHERE id = " . $_POST['id'];
        $erg = mysqli_query($conn, $sql);
    }