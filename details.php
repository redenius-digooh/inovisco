<?php
/*
 * It is checked whether there are still enough slots that are needed in the 
 * campaign.
 */
session_start();
require_once 'db.php';

require_once __DIR__ .  '/vendor/autoload.php';

// set user
if ($_SESSION['company'] != 'DIGOOH' && $_SESSION['company'] != 'Update Test') {
    $whereuser = "WHERE user = '" . $_SESSION['user'] . "'";
    $setuser = "user = '" . $_SESSION['user'] . "'";
    $user = $_SESSION['user'];
} else {
    $setuser = "1 = 1";
}

// new booking upload
if ($_POST['neuupload'] == 1) {
    header("Location: http://88.99.184.137/inovisco_direct/buchung.php");
}

// Inovisco declined
if ($_POST['inoschlecht'] == 1) {
    $sql = "UPDATE buchung SET inovisco = 0 WHERE user = '" . $_POST['user'] 
            . "' AND angebot = '" . $_POST['angebot'] . "'";
    $erg = mysqli_query($conn, $sql);
}

// Digooh declined
if ($_POST['sendschlecht'] == 1) {
    $sql = "UPDATE buchung SET inovisco = NULL, send_digooh = NULL, einfrieren "
            . "= NULL, export= NULL, digooh = NULL, info_ablehnung = '"
            . $_POST['ablehnungsinfo'] . "' WHERE user = '" . $_POST['user']
            . "' AND angebot = '" . $_POST['angebot'] . "'";
    $erg = mysqli_query($conn, $sql);
    
    $sql = "SELECT name, useremail FROM buchung WHERE "
            . "user = '" . $_POST['user'] . "' AND angebot = '" . 
            $_POST['angebot'] . "'";
    $db_erg = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array( $db_erg)) {
        $name = $row['name'];
        $useremail = $row['useremail'];
    }
    
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'https://prod-44.westeurope.logic.azure.com:443/workflows/437b6742e5054af3a0d2157333db7993/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=1iZaLnJq15I2gT7IEjJ0socjvJT4r2hmj8Fkrqw3PIg',
        [
            'json' => [
                'An' => $useremail,
                'Ablehnungsinfo' => $_POST['ablehnungsinfo'],
                'Kampagne' => $name
            ]
        ]
    );
    $body = $response->getBody();
}

// update
if ($_POST['speichern'] == 1) {
    $sd = explode("-", $_POST['start_date']);
    $ed = explode("-", $_POST['end_date']);
    $checks = checkdate($sd[1],$sd[2],$sd[0]);
    $checke = checkdate($ed[1],$ed[2],$ed[0]);
    if (!$checks || !$checke) {
        $error = "Das Startdatum oder Enddatum war nicht korrekt!";
    }
    if ($_POST['play_times'] < 0 || $_POST['play_times'] > 360) {
        $error = 'Die "Einblendungen pro Stunde" m&uuml;ssen einen Wert zwischen'
                . " 0 und 360 haben!";
    }
    else {
        if ($_POST['sammelkriterium'] != '') {
            $inscria = "criterien = '" . $_POST['sammelkriterium'] . "', ";
            $kriarr = explode(", ", $_POST['sammelkriterium']);
            $krit = array();
            $_POST['player'] = array();
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
                                    'limit'=> '130'
                                ]
                            ]
                        );
                        $body = $response->getBody();
                        $data = json_decode((string) $body);
                        foreach ($data->data as $key => $value) {
                            $_POST['player'][] = $value->id;
                        }
                    }
                }
            }
            if ($_POST['player'][0] != '') {
                // delete all players
                $del = "DELETE FROM playerbuchung WHERE angebot = " 
                        . $_POST['angebot'];
                $erg = @mysqli_query($conn, $del);
                    
                // insert all players for the offer
                foreach($_POST['player'] as $insplayer) {
                    $sql = "SELECT custom_sn2 FROM player WHERE id = " . $insplayer;
                    $db = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_array($db)) {
                        $custom_sn2 = $row['custom_sn2'];
                    }
                
                    $sql = "INSERT INTO playerbuchung (players, custom_sn2, "
                            . "angebot)"
                        . " VALUES ("
                        . "'" . $insplayer . "', "
                        . "'" . $custom_sn2 . "', "
                        . "'" . $_POST['angebot'] . "')";
                    $erg = mysqli_query($conn, $sql);
                }
            }
        }
        
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
        
        if ($krit[0] != '') {
            $kritstr = implode(", ", $krit);
        } else {
            $kritstr = $_POST['criterien_alt'];
        }
        
        if ($bind[0] != '') {
            $bindstr = implode(", ", $bind);
        } else {
            $bindstr = $_POST['bindcriterien_alt'];
        }
        
        if ($aus[0] != '') {
            $ausstr = implode(", ", $aus);
        } else {
            $ausstr = $_POST['auscriterien_alt'];
        }
        
        // update booking
        $sql = "UPDATE buchung SET "
        . "start_date = '" . $_POST['start_date'] . "', "
        . "end_date = '" . $_POST['end_date'] . "', "
        . "play_times = '" . $_POST['play_times'] . "', "
        . "name = '" . $_POST['name'] . "', "
        . "agentur = '" . $_POST['agentur'] . "', "                
        . "text = '" . $_POST['text'] . "', "
        . "motive = '" . $_POST['motive'] . "', " 
        . "criterien = '" . $kritstr . "', "
        . "and_criteria = '" . $bindstr . "', "
        . "exclude_criteria = '" . $ausstr . "', "
        . "abnummer = '" . $abnummer . "', "
        . "kunde = '" . $_POST['kunde'] . "' WHERE id = " . $_POST['id'];
        $erg = mysqli_query($conn, $sql);
    }
}

// freeze booking
if ($_POST['einfrieren'] == 1) {
    $sql = "UPDATE buchung SET "
            . "einfrieren = 1"
            . " WHERE angebot = " . $_POST['angebot'];
    $erg = mysqli_query($conn, $sql);
}

// partial player deletion 
if ($_POST['teildelete'] == 1) {
    foreach ($_POST['delete_teilkampagne'] as $delid) {
        $sql = "UPDATE playerbuchung SET deleted = 1 WHERE id = " . $delid;
        $erg = mysqli_query($conn, $sql);
    }
}

// player deletion
if ($_GET['delete'] == 1 || $_POST['delete'] == 1) {
    if ($_GET['playerid'] != '') {
        $sql = "UPDATE playerbuchung SET deleted = 1 WHERE id = " 
                . $_GET['playerid'];
        $erg = mysqli_query($conn, $sql);
    } else {
        if ($_POST['delete_kampagne'][0] != '') {
            foreach ($_POST['delete_kampagne'] as $delid) {
                $sql = "UPDATE playerbuchung SET deleted = 1 WHERE id = " . $delid;
                $erg = mysqli_query($conn, $sql);
            }
        }
    }
}

// undo deletion
if ($_GET['undo'] == 1) {
    $sql = "UPDATE playerbuchung SET deleted = 0 WHERE id = " . $_GET['playerid'];
    $erg = mysqli_query($conn, $sql);
}

if ($_GET['user'] != '') {
    $_POST['user'] = $_GET['user'];
}
if ($_GET['pruefen'] == 1 || $_POST['pruefen'] == 1) {
    $user = $_POST['user'];
}

// set offer number
if ($_GET['angebot'] || $_POST['angebot']) {
    $angebot = $_GET['angebot'] . $_POST['angebot'];
} else {
    $sql = "SELECT MAX(angebot) AS angebot FROM buchung";
    $db_erg = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array( $db_erg)) {
        $angebot = $row['angebot'];
    }
}

if ($angebot) {
    $an = " AND angebot = " . $angebot;
}

// get all criteria
$sql = "SELECT id, name FROM criteria ORDER BY name";
$db_erg = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array( $db_erg)) {
    $kriterien[] = array('id' => $row['id'], 'name' => $row['name']);
    $kritarr[] = utf8_encode($row['name']);
}

// get all bookings 
$sql = "SELECT id, kunde, name, start_date, end_date, play_times, text, motive,"
            . " agentur, angebot, inovisco, digooh, einfrieren, export, criterien,"
            . " and_criteria, exclude_criteria, send_digooh, abnummer FROM "
            . "buchung WHERE " . $setuser . $an;
$db_erg = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array( $db_erg)) {
    $id = $row['id'];
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $play_times = $row['play_times'];
    $name = $row['name'];
    $kunde = $row['kunde'];
    $agentur = $row['agentur'];
    $angebot = $row['angebot'];
    $digooh = $row['digooh'];
    $inovisco = $row['inovisco'];
    $einfrieren = $row['einfrieren'];
    $export = $row['export'];
    $criterien = $row['criterien'];
    $bindcriterien = $row['and_criteria'];
    $auscriterien = $row['exclude_criteria'];
    $text = $row['text'];
    $motive = $row['motive'];
    $send_digooh = $row['send_digooh'];
    $abnummer = $row['abnummer'];
}

// get criterianames
$criteriaarr = explode(",", $criterien);
if ($criteriaarr[0] != '') {
    foreach ($criteriaarr as $cri) {
        $sql = "SELECT name FROM criteria WHERE id = " . $cri;
        $db = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_array( $db)) {
            $crit[] = $row['name'];
        }
    }
}
$bindcriteriaarr = explode(",", $bindcriterien);
if ($bindcriteriaarr[0] != '') {
    foreach ($bindcriteriaarr as $bindcri) {
        $sql = "SELECT name FROM criteria WHERE id = " . $bindcri;
        $db = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_array( $db)) {
            $bindn[] = $row['name'];
        }
    }
}
$auscriteriaarr = explode(",", $auscriterien);
if ($auscriteriaarr[0] != '') {
    foreach ($auscriteriaarr as $auscri) {
        $sql = "SELECT name FROM criteria WHERE id = " . $auscri;
        $db = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_array( $db)) {
            $ausn[] = $row['name'];
        }
    }
}

// get entries from least
if ($start_date != '' && $end_date >= date("Y-m-d")) {
    $sql = "SELECT a.players "
        . "FROM playerbuchung AS a"
        . " LEFT JOIN player AS b ON a.players = b.id"
        . " WHERE a.angebot = " . $angebot . " ORDER BY b.name";
    $erg = mysqli_query($conn, $sql);
    while ($row2 = mysqli_fetch_array($erg)) {
        $playarr[] = $row2['players'];
    }
    $playerstr = implode(",", $playarr);
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
}

// get players
$sql = "SELECT a.players, a.id, a.deleted, a.lfsph, b.name, a.custom_sn2 "
        . "FROM playerbuchung AS a"
        . " LEFT JOIN player AS b ON a.players = b.id"
        . " WHERE a.angebot = " . $angebot . " ORDER BY b.name";
$db_erg2 = mysqli_query($conn, $sql);
$gruen = 0;
$alleplayer = array();
while ($row2 = mysqli_fetch_array($db_erg2)) {
    if (!in_array($row2['custom_sn2'], $alleplayer)) {
        $deleted = $row2['deleted'];
        $lfsph = $row2['lfsph'];
        $players = $row2['players'];
        $playerid = $row2['id'];
        $alleid[] = $row2['id'];
        $displayname = $row2['name'];
        $displays[] = $row2['name'];
        $custom_sn2 = $row2['custom_sn2'];
        $alleplayer[] = $custom_sn2;
        require_once __DIR__ .  '/vendor/autoload.php';

        $client = new \GuzzleHttp\Client();
        
        $lfsphjetzt = (int)$arr[$players] / 10;
        $restzeit = ($lfsphjetzt);
        
        if ($restzeit <= 0) {
            $problem = 1;
            $gesproblem = 1;
            $probleme[] = $playerid;
        }
        elseif (floor($restzeit) < $play_times) {
            $problem = 2;
            $gesproblem = 1;
            $teilprobleme[] = $playerid;
            $gelbeb[] = (int)$restzeit;
        }
        else {
            $problem = 0;
            $gruen = $gruen + 1;
        }
    
        $buchungen[] = array('agentur' => $agentur, 'name' => $name,
            'players' => $players, 'problem' => $problem, 'start_date' =>
            $start_date, 'end_date' => $end_date, 'id' => $id, 
            'deleted' => $deleted, 'restzeit' => $restzeit, 'lfsph' => $lfsph,
            'play_times' => $play_times, 'displayname' => $displayname,
            'inovisco' => $inovisco, 'digooh' => $digooh, 'lfsphjetzt' => 
            $lfsphjetzt, 'playerid' => $playerid, 'criterien' => $criterien,
            'text' => $text, 'send_digooh' => $send_digooh, 'custom_sn2' =>
            $custom_sn2);
    }
}

// Digooh approved
if ($_POST['gut'] == 1) {
    $sql = "UPDATE buchung SET digooh = 1 WHERE user = '" . $user 
            . "' AND angebot = '" . $_POST['angebot'] . "'";
    $erg = mysqli_query($conn, $sql);

    // set new campaign
    require_once __DIR__ .  '/vendor/autoload.php';
    $alleplayers = implode(",", $alleplayer);
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'https://cms.digooh.com:8082/api/v1/campaigns',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'name' => $name,
                'start_date' => $start_date,
                'end_date' => $end_date,
        //        'publish' => false,
                'priority' => 2,
                'play_type' => 0,
                'play_times' => $play_times,
        //        'time_flag' => false,
                'criteria' => $criterien,
        //        'and_criteria' => $o[0],
        //        'exclude_criteria' => $o[0],
                'players' => strval($alleplayers),
        //        'tags' => $o[0],
                'tag_option' => 2,
        //        'media' => $o[0],
                'descr' => $text
            ]
        ]
    );
    $body = $response->getBody();
    
    header("Location: http://88.99.184.137/inovisco_direct/details.php?angebot=" . $_POST['angebot']);
}

// send email to Digooh
if ($_POST['send_digooh'] == 1) {
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'https://prod-61.westeurope.logic.azure.com:443/workflows/9c9cd20cdc0f4852b73e4178e263572c/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=Y3fmlxVqVrtmWdWMp0g6VNWc6ZfcCwmx_MTU0Ao6V4A',
        [
            'json' => [
                'Ansprehpartner' => $_POST['user'],
                'Telefon' => $_POST['telefon'],
                'E-Mail' => $_POST['email'],
                'Kunde' => $_POST['kunde'],
                'Zeitraum' => $_POST['zeitraum'],
                'Anzahl Tage' => $_POST['tage'],
                'Displays (Anzahl, Einblendungen)' => $_POST['displayeinblendungen'],
                'Anzahl Motive' => $_POST['motive'],
                'Infotext' => $_POST['text'],
                'Datum' => $_POST['datum']
            ]
        ]
    );
    $body = $response->getBody();
    
    $sql = "UPDATE buchung SET send_digooh = 1, inovisco = 1 WHERE user = '" . $user
            . "' AND angebot = '" . $_POST['angebot'] . "'";
    $erg = mysqli_query($conn, $sql);
    
    header("Location: http://88.99.184.137/inovisco_direct/details.php?angebot=" . $_POST['angebot']);
}
    
require_once 'oben2.php';
?>
            <table class="ohnerahmen">
                <tr>
<?php
if ($inovisco != 1) {
?>
                    <td class="blau">Prozessschritt: Pr&uuml;fung Inovisco</td>
<?php
} else {
?>
                    <td class="blau">Prozessschritt: Pr&uuml;fung Digooh</td>
<?php
}
?>
                </tr>
                <tr>
                    <td class="zelle">
<?php
if ($gesproblem == 1) {
?>
Das Hochladen war erfolgreich. Nicht alle Displays oder Slots sind verf&uuml;gbar!
F&uuml;r eine Buchung muss die Kampagne ge&auml;ndert und die Verf&uuml;gbarkeit 
erneut gepr&uuml;ft werden!
<?php
} else {
?>
Das Hochladen war erfolgreich. Alle Displays und Slots sind verf&uuml;gbar, 
die Kampagne kann zur Pr&uuml;fung an Digooh gesendet werden!
<?php
}

if ($error) {
?>
<p><span style="color: red"><?php echo $error; ?></span></p>
<?php
}
?>
                    </td>
                </tr>
        <tr>
            <td style="align: left;" class="zelle">
                <form action="details.php" method="post">
                    <input type="hidden" name="update" value="1">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="user" value="<?php echo $user; ?>">
                    <?php
                    if (is_array($alleid)) {
                        foreach ($alleid as $val) {
                    ?>
                <input type="hidden" name="ids[]" value="<?php echo $val; ?>">
                    <?php
                        }
                    }
                    ?>
                <table class="ohnerahmen" style="align: left;">
                    <tr>
                        <td width="280" class="zelle">Buchung durch:</td>
                        <td><?php echo $_SESSION['company']; ?> / 
                            <?php echo $user; ?>
                        </td>
                        <td class="zelle">
                            <?php if ($_POST['bearbeiten'] != 1 
                                    && $digooh != 1 && $einfrieren != 1) { ?>
                            <button type="submit" name="bearbeiten" 
                                class="grau" value="1">
                            bearbeiten</button>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Angebotsnummer:</td>
                        <td colspan="2" class="zelle"><?php echo $angebot; ?></td>
                    </tr>
                    <tr>
                        <td class="zelle">Kundenname:</td>
                        <td colspan="2" class="zelle">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
                            <input type="text" name="kunde" value="<?php echo $kunde; ?>" 
               size="40" required>
        <?php } else { 
            echo $kunde;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Agenturname:</td>
                        <td colspan="2" class="zelle">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
    <input type="text" name="agentur" value="<?php echo $agentur; ?>" 
           size="40" required>
    <?php } else { 
            echo $agentur;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Kampagnenname:</td>
                        <td colspan="2" class="zelle">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
        <input type="text" name="name" value="<?php echo $name; ?>" 
               size="40" required>
        <?php } else { 
            echo $name;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">AB-Nummer:</td>
                        <td colspan="2" class="zelle">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
        <input type="text" name="abnummer" value="<?php echo $abnummer; ?>" 
               size="40" required>
        <?php } else { 
            echo abnummer;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Zeitraum:</td>
                        <td colspan="2" class="zelle">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
        <input type="text" name="start_date" value="<?php echo $start_date; ?>" 
        size="10" required>
    - <input type="text" name="end_date" value="<?php echo $end_date; ?>" 
        size="10" required> (z.B. 2021-01-20)
        <?php } else { 
            echo $start_date . "  -  " . $end_date;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Einblendungen pro Stunde:</td>
                        <td colspan="2" class="zelle">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
        <input type="text" name="play_times" value="<?php echo $play_times; ?>" 
            size="10" required>
        <?php } else { 
            echo $play_times;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Anzahl Motive:</td>
                        <td colspan="2" class="zelle">
        <?php
        if ($_POST['bearbeiten'] == 1) {
            if ($motive == '') {
                $motive = 1;
            }
        ?>
        <input type="text" name="motive" value="<?php echo $motive; ?>" 
            size="10" required>
        <?php } else { 
            echo $motive;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="zelle">
                            Kriterien:
                        </td>
                        <td class="zelle">
                            <?php if ($_POST['bearbeiten'] == 1) { ?>
                            alt: 
                            <?php
                            if ($crit[0] != '') {
                                $criterienanzeige = implode(", ",$crit);
                                echo $criterienanzeige;
                            }
                            ?>
                            <br>neu: 
                            <input type="text" id="search_data" placeholder="" 
                                   autocomplete="off" name="sammelkriterium" 
                            style="width: 310px; border: 1px solid #FFFFFF;"/>
                            <?php } else {
                                if ($crit[0] != '') {
                                    $criterienanzeige = implode(", ",$crit);
                                    echo $criterienanzeige;
                                }
                            } ?>
                            <input type="hidden" name="criterien_alt" 
                                   value="<?php echo $criterien; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="zelle">
                            Bind mit Kriterien:
                        </td>
                        <td class="zelle">
                            <?php if ($_POST['bearbeiten'] == 1) { ?>
                            alt: 
                            <?php
                            if ($bindn[0] != '') {
                                $bindcriterienanzeige = implode(", ",$bindn);
                                echo $bindcriterienanzeige;
                            }
                            ?>
                            <br>neu: 
                            <input type="text" id="search_bind" placeholder="" 
                                   autocomplete="off" name="bindkriterium" 
                            style="width: 310px; border: 1px solid #FFFFFF;"/>
                            <?php } else {
                                if ($bindn[0] != '') {
                                    $bindcriterienanzeige = implode(", ",$bindn);
                                    echo $bindcriterienanzeige;
                                }
                            } ?>
                            <input type="hidden" name="bindcriterien_alt" 
                                   value="<?php echo $bindcriterien; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="zelle">
                            auszuschlie&szlig;ende Kriterien:
                        </td>
                        <td class="zelle">
                            <?php if ($_POST['bearbeiten'] == 1) { ?>
                            alt: 
                            <?php
                            if ($ausn[0] != '') {
                                $auscriterienanzeige = implode(", ",$ausn);
                                echo $auscriterienanzeige;
                            }
                            ?>
                            <br>neu: 
                            <input type="text" id="search_aus" placeholder="" 
                                   autocomplete="off" name="auskriterium" 
                            style="width: 310px; border: 1px solid #FFFFFF;"/>
                            <?php } else {
                                if ($ausn[0] != '') {
                                    $auscriterienanzeige = implode(", ",$ausn);
                                    echo $auscriterienanzeige;
                                }
                            } ?>
                            <input type="hidden" name="auscriterien_alt" 
                                   value="<?php echo $auscriterien; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="zelle">
                            Displays:
                        </td>
                        <td class="zelle">
                            <?php if ($_POST['bearbeiten'] == 1) { ?>
                            <input type="text" id="search_player" placeholder="" 
                                   autocomplete="off" name="sammelplayer" 
                            style="width: 310px; border: 1px solid #FFFFFF;"/>
                            <?php } else { 
                                if ($displays[0] != '') {
                                    $displayanzeige = implode(",",$displays);
                                    echo utf8_encode($displayanzeige);
                                }
                            } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Infos:</td>
                        <td class="zelle">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
        <textarea name="text" rows="4" cols="42"><?php echo $text; ?></textarea>
        <?php } else { 
            echo $text;
        } ?>
                        </td>
                        <td class="zelle">
                        <?php if ($_POST['bearbeiten'] == 1
                                && $digooh != 1) { ?>
                            <button type="submit" name="speichern" 
                                class="gruen" value="1">Speichern
                        </button>
                        <?php
                                } else {
                                    if ($einfrieren != 1) {
                        ?>
                            <button type="submit" name="einfrieren" 
                                class="rot" value="1">Einfrieren
                        </button>
                                <?php
                                    }
                                }
                                ?>
                        </td>
                    </tr>
                </table>
                </form>
                <?php
                if ($einfrieren == 1) {
                ?>
                <script type="text/javascript">
                    function refresh() {    
                        setTimeout(function () {
                            location.reload(true);
                            return false;
                        }, 50);
                    }
                </script>
                <form action="export.php" method="post" target="_new">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">            
                <input type="hidden" name="user" value="<?php echo $user; ?>">
                <button type="submit" name="exportieren" 
                                class="gruen" value="1" onclick="refresh()">Exportieren
                        </button>
                </form>                
                <?php
                }
                ?>
            </td>
        </tr>
        <tr>
            <td class="zelle">
                <p>&nbsp;</p>
            </td>
        </tr>
                <tr>
                    <td width="100%" class="zelle"><br>
                        <table class="ohnerahmen">
                            <tr>
<?php
if ($gesproblem == 1 && $einfrieren != 1 && $_POST['bearbeiten'] != 1) {
?>
                                <td class="zelle">
                        <form action="details.php" method="post">
                            <button type="submit" name="neuupload" 
                                class="grau" value="1">
                            Neuer Upload</button>
                            <button type="submit" name="teildelete" 
                                class="rot" value="1">
                            Alle unvollst&auml;ndigen<br>l&ouml;schen</button>  
                            <button type="submit" name="delete" 
                                class="rot" value="1">
                            Alle nicht verf&uuml;gbaren<br>l&ouml;schen</button>
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
                            <?php
                            if ($teilprobleme) {
                                foreach ($teilprobleme as $item) {
                            ?>
                            <input type="hidden" name="delete_teilkampagne[]" 
                                   value="<?php echo $item; ?>">
                            <?php
                                }
                            }
                            if ($probleme) {
                                foreach ($probleme as $item) {
                            ?>
                            <input type="hidden" name="delete_kampagne[]" 
                                   value="<?php echo $item; ?>">
                            <?php
                                }
                            }
                            ?>
                        </form>
                                </td>
<?php
}
if ($export == 1) {
    if ($_POST['inogut'] != 1 && $inovisco != 1) {
?>
                                <td valign="top" class="rechts">
                        <form action="details.php" method="post">                        
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
        <input type="hidden" name="user" value="<?php echo $user; ?>">
                            <button type="submit" name="inogut" 
                                class="gruen" value="1">
                            Check Verfügbarkeit</button>
                        </form>
                                </td>
<?php
    }
    elseif ($send_digooh != 1) {
        $firstDate  = new DateTime($start_date);
        $secondDate = new DateTime($end_date);
        $intvl = $firstDate->diff($secondDate);
        $tage = $intvl->days;
        
        if (is_array($gelbeb)) {
            $anzeb = array_count_values($gelbeb);
            foreach ($anzeb as $key => $value) {  
                $gelbei .= $value . ' * ' . $key . " | ";
            }
        }
        $displaeb = $gruen . ' * ' . $play_times . " | ";
        $displaeb .= $gelbei;
?>
                                <td valign="top" class="rechts">
                        <form action="details.php" method="post">                        
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
        <input type="hidden" name="user" value="<?php echo $_SESSION['user']; ?>">
        <input type="hidden" name="telefon" value="<?php echo ''; ?>">
        <input type="hidden" name="email" value="<?php echo $_SESSION['email']; ?>">
        <input type="hidden" name="kunde" value="<?php echo $kunde; ?>">
        <input type="hidden" name="zeitraum" value="<?php echo $start_date 
                . " - " . $end_date; ?>">
        <input type="hidden" name="tage" value="<?php echo (string)$tage; ?>">
        <input type="hidden" name="displayeinblendungen" value="<?php echo $displaeb; ?>">
        <input type="hidden" name="motive" value="<?php echo $motive; ?>">
        <input type="hidden" name="datum" value="<?php echo date("d.m.Y"); ?>">
        <input type="hidden" name="text" value="<?php echo $text; ?>">
                            <button type="submit" name="send_digooh" 
                                class="gruen" value="1">
                            an Digooh senden</button>
                        </form>
                                </td>
<?php
    }
    elseif ($digooh != 1 && ($_SESSION['company'] == 'Update Test' ||
            $_SESSION['company'] == 'DIGOOH') && $_POST['schlecht'] != 1) {
?>
                                <td valign="top" class="rechts">
                        <form action="details.php" method="post">
                  <input type="hidden" name="user" value="<?php echo $user; ?>">
                  <input type="hidden" name="geprueft" value="1">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
                            <button type="submit" name="gut" 
                                class="gruen" value="1">
                            Digooh: Buchung best&auml;tigen</button>
                            <button type="submit" name="schlecht" 
                                class="rot" value="1">
                            Digooh: Buchung ablehnen</button>
                        </form>
                                </td>
<?php
    }
    elseif ($_POST['schlecht'] == 1) {
?>
                                <td>
                        <form action="details.php" method="post">
                            <table class="ohnerahmen">
                    <tr>
                        <td valign="top" class="zelle">
                            Infos zur Ablehnung:
                        </td>
                        <td class="zelle">
                <textarea name="ablehnungsinfo" rows="4" cols="42"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="rechts" colspan="2">
                  <input type="hidden" name="user" value="<?php echo $user; ?>">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
                            <button type="submit" name="sendschlecht" 
                                class="rot" value="1">
                            Info an Verfasser senden</button>
                        </form>
                                </td>
<?php
    } 
    else {
?>
                                <td class="zelle">
                            <center>Die Pr&uuml;fung ist abgeschlossen.</center>
                                </td>
<?php
    }
}

if ($_POST['sendschlecht'] == 1) {
?>
                                <td class="zelle">
                            <center>Die Info-Mail wurde versendet.</center>
                                </td>
<?php
}
?>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
                    </td>
                </tr>
<?php
if ($_POST['bearbeiten'] != 1) {
?>
                <tr>
    <td class="zelle" colspan="5"><center>
        <table class="mitrahmen">
            <tr>
                <td valign="bottom" class="rahmenunten">Aktion</td>
                <td valign="bottom" class="rahmenunten">Displayname</td>
                <td valign="bottom" class="rahmenrechts">QID</td>
                <td class="rahmenrechts">verf&uuml;gbare Einblendungen<br>pro Stunde</td>
                <?php
                if ($export == 1) {
                ?>
                <td valign="bottom" class="rahmenrechts">&Auml;nderung seit Export</td>
                <?php
                }
                ?>
            </tr>
<?php
if ($buchungen[0] != '') {
    foreach ($buchungen as $key => $inhalt) {
        if ($inhalt['deleted']) {
            echo '<tr class="strikeout">';
        } else {
            echo "<tr>";
        }
?>
                            
                                <td class="zelle">
                    <?php
                    if ($einfrieren != 1) {
                        if ($inhalt['problem'] == 1 || $inhalt['problem'] == 2) {
                            if ($inhalt['deleted'] == 1) {
                    ?>
<a href="details.php?playerid=<?php echo $inhalt['playerid']; ?>&undo=1&angebot=<?php echo $angebot; ?>">
                                <img src="abbrechengr.png" alt="l&ouml;schen">
                                    </a>
                    <?php
                            } else {
                    ?>
<a href="details.php?playerid=<?php echo $inhalt['playerid']; ?>&delete=1&angebot=<?php echo $angebot; ?>">
                                <img src="abbrechenkl.png" alt="l&ouml;schen">
                                    </a>
                    <?php
                            }
                        }
                    }
                    ?>
                                </td>
                                <td class="zelle"><?php echo utf8_encode($inhalt['displayname']); ?></td>
                                <td class="rechts">
                    <?php
                    if ($inhalt['restzeit'] <= 0) {
                        $prob = '<font style="color: red">';
                    } elseif ($inhalt['restzeit'] > 0 && $inhalt['restzeit'] < 
                            $inhalt['play_times']) {
                        $prob = '<font style="color: orange">';
                    } elseif ($inhalt['restzeit'] > 0 && $inhalt['restzeit'] >= 
                            $inhalt['play_times']){
                        $prob = '<font style="color: green">';
                    } else {
                        $prob = '';
                    }
                    echo $prob . $inhalt['custom_sn2'] . '</font>';
                    ?>
                                </td>
                <?php
                if ($export == 1) {
                ?>
                                <td class="rechts">
                    <?php
                    if ($inhalt['problem'] == 1) {
                        $prob = '<font style="color: red">';
                    } elseif ($inhalt['problem'] == 2) {
                        $prob = '<font style="color: orange">';
                    } else {
                        $prob = '<font style="color: green">';
                    }
                    echo $prob . (int)$inhalt['lfsph'] . '</font>';
                    ?>
                                </td>
                                <td class="rechts">
                    <?php
                    if ($inhalt['restzeit'] <= 0) {
                        $prob = '<font style="color: red">';
                    } elseif ($inhalt['restzeit'] > 0 && $inhalt['restzeit'] < 
                            $inhalt['play_times']) {
                        $prob = '<font style="color: orange">';
                    } elseif ($inhalt['restzeit'] > 0 && $inhalt['restzeit'] >= 
                            $inhalt['play_times']){
                        $prob = '<font style="color: green">';
                    } else {
                        $prob = '';
                    }
                    echo $prob . (int)$inhalt['lfsphjetzt'] . '</font>';
                    ?>
                                </td>
                <?php
                } else {
                ?>
                                <td class="rechts">
                    <?php
                    if ($inhalt['restzeit'] <= 0) {
                        $prob = '<font style="color: red">';
                    } elseif ($inhalt['restzeit'] > 0 && $inhalt['restzeit'] < 
                            $inhalt['play_times']) {
                        $prob = '<font style="color: orange">';
                    } elseif ($inhalt['restzeit'] > 0 && $inhalt['restzeit'] >= 
                            $inhalt['play_times']){
                        $prob = '<font style="color: green">';
                    } else {
                        $prob = '';
                    }
                    echo $prob . (int)$inhalt['lfsphjetzt'] . '</font>';
                    ?>
                                </td>
                    <?php
                }
                ?>
                            </tr>
<?php
    }
}
?>
                        </table>
                </center></td>
                </tr>
<?php
}
?>
            </table>
        </center>
        <script type="text/javascript">
        $('#search_data').tokenfield({
            autocomplete: {
              source: <?php echo json_encode($kritarr); ?>,
              delay: 100
            },
            showAutocompleteOnFocus: true
        })
        </script>
        <script type="text/javascript">
        $('#search_bind').tokenfield({
            autocomplete: {
              source: <?php echo json_encode($kritarr); ?>,
              delay: 100
            },
            showAutocompleteOnFocus: true
        })
        </script>

        <script type="text/javascript">
        $('#search_aus').tokenfield({
            autocomplete: {
              source: <?php echo json_encode($kritarr); ?>,
              delay: 100
            },
            showAutocompleteOnFocus: true
        })
        </script>
    </body>
</html>