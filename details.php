<?php
/*
 * It is checked whether there are still enough slots that are needed in the 
 * campaign.
 */
session_start();
require_once 'db.php';

if ($_POST['neuupload'] == 1) {
    // new upload
    header("Location: http://88.99.184.137/inovisco_direct/buchung.php");
}

if (isset($_POST['speichern'])) {
    // update
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
        $sql = "UPDATE buchung SET "
        . "start_date = '" . $_POST['start_date'] . "', "
        . "end_date = '" . $_POST['end_date'] . "', "
        . "play_times = '" . $_POST['play_times'] . "', "
        . "name = '" . $_POST['name'] . "', "
        . "agentur = '" . $_POST['agentur'] . "', "                
        . "text = '" . $_POST['text'] . "', "
        . "kunde = '" . $_POST['kunde'] . "' WHERE id = " . $_POST['id'];
        $erg = mysqli_query($conn, $sql);
    }
}

if ($_POST['export'] == 1) {
    $sql = "UPDATE buchung SET "
            . "export = 1"
            . " WHERE angebot = " . $_POST['angebot'];
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['einfrieren'] == 1) {
    // freeze
    $sql = "UPDATE buchung SET "
            . "einfrieren = 1"
            . " WHERE angebot = " . $_POST['angebot'];
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['teildelete'] == 1) {
    // partial deletion 
    foreach ($_POST['delete_teilkampagne'] as $delid) {
        $sql = "UPDATE playerbuchung SET deleted = 1 WHERE id = " . $delid;
        $erg = mysqli_query($conn, $sql);
    }
}

if ($_GET['delete'] == 1 || $_POST['delete'] == 1) {
    // deletion
    if ($_GET['playerid'] != '') {
        $sql = "UPDATE playerbuchung SET deleted = 1 WHERE id = " 
                . $_GET['playerid'];
        $erg = mysqli_query($conn, $sql);
    } else {
        foreach ($_POST['delete_kampagne'] as $delid) {
            $sql = "UPDATE playerbuchung SET deleted = 1 WHERE id = " . $delid;
            $erg = mysqli_query($conn, $sql);
        }
    }
}

if ($_GET['undo'] == 1) {
    // undo
    $sql = "UPDATE playerbuchung SET deleted = 0 WHERE id = " . $_GET['playerid'];
    $erg = mysqli_query($conn, $sql);
}

if ($_GET['user'] != '') {
    $_POST['user'] = $_GET['user'];
}
if ($_GET['pruefen'] == 1 || $_POST['pruefen'] == 1) {
    $user = $_POST['user'];
} else {
    $user = $_SESSION['user'];
}

// username
require_once __DIR__ .  '/vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$response = $client->get(
    'https://cms.digooh.com:8081/api/v1/users',
    [
        'headers' => [
            'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'query' => [
            'sort'=> '-name',
            'filter[name]'=> $_SESSION['user'],
        ]
    ]
);
$body = $response->getBody();
$data = json_decode((string) $body);
foreach ($data->data as $key => $value) {
    $company = $value->company->name;
}

// set offer number
if ($_GET['angebot'] || $_POST['angebot']) {
    $angebot = $_GET['angebot'] . $_POST['angebot'];
} else {
    $sql = "SELECT MAX(angebot) AS angebot FROM buchung WHERE user = '" . $user 
            . "'";
    $db_erg = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array( $db_erg)) {
        $angebot = $row['angebot'];
    }
}
if ($angebot) {
    $an = " AND angebot = " . $angebot;
}

// get all bookings 
$sql = "SELECT id, players, deleted, lfsph FROM playerbuchung WHERE"
        . " angebot = " . $angebot;
$db_erg2 = mysqli_query($conn, $sql);
while ($row2 = mysqli_fetch_array( $db_erg2)) {
    $deleted = $row2['deleted'];
    $lfsph = $row2['lfsph'];
    $players = $row2['players'];
    $playerid = $row2['id'];
        
    $sql = "SELECT id, kunde, name, start_date, end_date, play_times, text,"
            . " agentur, angebot, inovisco, digooh, einfrieren, export, criterien"
            . " FROM buchung WHERE user = '" . $user . "'" . $an;
    $db_erg = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_array( $db_erg)) {
        $id = $row['id'];
        $playerid = $playerid;
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $play_times = $row['play_times'];
        $name = $row['name'];
        $kunde = $row['kunde'];
        $agentur = $row['agentur'];
        $angebot = $row['angebot'];
        $digooh = $row['digooh'];
        $inovisco = $row['inovisco'];
        $digooh = $row['digooh'];
        $einfrieren = $row['einfrieren'];
        $export = $row['export'];
        $alleid[] = $playerid;
        $criterien = $row['criterien'];
        $text = $row['text'];
    }

    require_once __DIR__ .  '/vendor/autoload.php';
    
    $sql = "SELECT name FROM player WHERE id = " . $players;
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
                        'players' => $players
                    ]
                ]
            );
            $body = $response->getBody();
            $data = json_decode((string) $body);
            
            foreach ($data as $key => $value) {
                $lfsphjetzt = $value / 10;
            }

        //    $restzeit = ($lfsph - $play_times);
            $restzeit = ($lfsphjetzt);
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }

        if ($restzeit <= 0) {
            $problem = 1;
            $gesproblem = 1;
            $probleme[] = $playerid;
        }
        elseif ($restzeit < $play_times) {
            $problem = 2;
            $gesproblem = 1;
            $teilprobleme[] = $playerid;
        }
        else {
            $problem = 0;
        }
    }
    
    $buchungen[] = array('agentur' => $agentur, 'name' => $name,
        'players' => $players, 'problem' => $problem, 'start_date' =>
        $start_date, 'end_date' => $end_date, 'id' => $id, 
        'deleted' => $deleted, 'restzeit' => $restzeit, 'lfsph' => $lfsph,
        'play_times' => $play_times, 'displayname' => $displayname,
        'inovisco' => $inovisco, 'digooh' => $digooh, 'lfsphjetzt' => 
        $lfsphjetzt, 'playerid' => $playerid, 'criterien' => $criterien,
        'text' => $text);
}

$text="bla, bla, bla";
//$name = "test";
$start_date = "2021-02-10";
$end_date = "2021-03-20";

if(isset($_POST['inogut'])){
    // Inovisco approved
    $sql = "UPDATE buchung SET inovisco = 1 WHERE user = '" . $_POST['user'] 
            . "' AND angebot = '" . $_POST['angebot'] . "'";
    $erg = mysqli_query($conn, $sql);
    /*
    // info to Digooh
    $empfaenger = "redenius@digooh.com";
    $betreff = "Neue Buchung zur Prüfung";
    $from = "info@digooh.com";
    $text = "Es wurden neue Kampagnen eingetragen: "
            . '<a href="http://88.99.184.137/inovisco_direct/details.php?'
            . 'pruefen=1&user=' . $_SESSION['user'] . '">';
    $headers = "From:" . $from;
    mail($empfaenger, $betreff, $text, $headers);*/
}

if ($_POST['inoschlecht'] == 1) {
    // Inovisco declined
    $sql = "UPDATE buchung SET inovisco = 0 WHERE user = '" . $user 
            . "' AND angebot = '" . $_POST['angebot'] . "'";
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['gut'] == 1) {
    // Digooh approved
    $sql = "UPDATE buchung SET digooh = 1 WHERE user = '" . $user 
            . "' AND angebot = '" . $_POST['angebot'] . "'";
    $erg = mysqli_query($conn, $sql);
    
    require_once __DIR__ .  '/vendor/autoload.php';
    $allidstr = implode(",", $alleid);
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'https://cms.digooh.com:8081/api/v1/campaigns',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'name' => $name,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'publish' => false,
                'priority' => 2,
                'play_type' => 0,
                'play_times' => $play_times,
                'time_flag' => false,
                'start_time' => '00:00',
                'end_time' => '00:00',
                'criteria' => $criterien,
                'and_criteria' => $o[0],
                'exclude_criteria' => $o[0],
                'players' => $alleidstr,
                'tags' => $o[0],
                'tag_option' => 2.0,
                'media' => $o[0],
                'descr' => 'explicabo'
            ]
        ]
    );
    $body = $response->getBody();
    print_r(json_decode((string) $body));
}

if ($_POST['schlecht'] == 1) {
    // Digooh declined
    $sql = "UPDATE buchung SET digooh = 0 WHERE user = '" . $user
            . "' AND angebot = '" . $_POST['angebot'] . "'";
    $erg = mysqli_query($conn, $sql);
}
require_once 'oben.php';
?>
            <table class="ohnerahmen">
                <tr>
                    <td class="blau">Prozessschritt: Pr&uuml;fung Inovisco
                    </td>
                </tr>
                <tr>
                    <td>
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
            <td style="align: left;">
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
                        <td width="280">Buchung durch:</td>
                        <td><?php echo $company; ?> / 
                            <?php echo $user; ?>
                        </td>
                        <td>
                            <?php if ($_POST['bearbeiten'] != 1 
                                    && $digooh != 1 && $einfrieren != 1) { ?>
                            <button type="submit" name="bearbeiten" 
                                class="grau" value="1">
                            bearbeiten</button>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Angebotsnummer:</td>
                        <td colspan="2"><?php echo $angebot; ?></td>
                    </tr>
                    <tr>
                        <td>Kundenname:</td>
                        <td colspan="2">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
                            <input type="text" name="kunde" value="<?php echo $kunde; ?>" 
               size="40" required>
        <?php } else { 
            echo $kunde;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Agenturname:</td>
                        <td colspan="2">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
    <input type="text" name="agentur" value="<?php echo $agentur; ?>" 
           size="40" required>
    <?php } else { 
            echo $agentur;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Kampagnenname:</td>
                        <td colspan="2">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
        <input type="text" name="name" value="<?php echo $name; ?>" 
               size="40" required>
        <?php } else { 
            echo $name;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Zeitraum:</td>
                        <td colspan="2">
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
                        <td>Einblendungen pro Stunde:</td>
                        <td colspan="2">
        <?php if ($_POST['bearbeiten'] == 1) { ?>
        <input type="text" name="play_times" value="<?php echo $play_times; ?>" 
            size="10" required>
        <?php } else { 
            echo $play_times;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            Kriterien
                        </td>
                        <td>
                            <input type="text" id="search_data" placeholder="" 
                                   autocomplete="off" name="sammelkriterium" 
                            style="width: 310px; border: 1px solid #FFFFFF;"/>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            Displays
                        </td>
                        <td>
                            <input type="text" id="search_player" placeholder="" 
                                   autocomplete="off" name="sammelplayer" 
                            style="width: 310px; border: 1px solid #FFFFFF;"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Infos:</td>
                        <td>
        <?php if ($_POST['bearbeiten'] == 1) { ?>
                            <textarea name="text" rows="4" cols="42">
                                <?php echo $text; ?>
                            </textarea>
        <?php } else { 
            echo $text;
        } ?>
                        </td>
                        <td>
                        <?php if ($_POST['bearbeiten'] == 1
                                && $digooh != 1) { ?>
                            <button type="submit" name="speichern" 
                                class="gruen" value="Speichern">Speichern
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
                <form action="export.php" method="post" target="_new">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">            
                <input type="hidden" name="user" value="<?php echo $user; ?>">
                <button type="submit" name="export" 
                                class="gruen" value="1">Exportieren
                        </button>
                </form>
                <?php
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <p>&nbsp;</p>
            </td>
        </tr>
<tr>
    <td><center>
        <table class="mitrahmen">
            <tr>
                <td valign="bottom">Aktion</td>
                <td valign="bottom">Displayname</td>
                <td valign="bottom" class="rechts">DisplayID</td>
                <td class="rechts">verf&uuml;gbare Einblendungen<br>pro Stunde</td>
                <?php
                if ($export == 1) {
                ?>
                <td valign="bottom" class="rechts">&Auml;nderung seit Export</td>
                <?php
                }
                ?>
            </tr>
<?php
foreach ($buchungen as $key => $inhalt) {
    if ($inhalt['deleted']) {
        echo '<tr class="strikeout">';
    } else {
        echo "<tr>";
    }
?>
                            
                                <td>
                    <?php
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
                    ?>
                                </td>
                                <td><?php echo utf8_encode($inhalt['displayname']); ?></td>
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
                    echo $prob . $inhalt['players'] . '</font>';
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
                    echo $prob . $inhalt['lfsph'] . '</font>';
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
                    echo $prob . $inhalt['lfsphjetzt'] . '</font>';
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
                    echo $prob . $inhalt['lfsphjetzt'] . '</font>';
                    ?>
                                </td>
                    <?php
                }
                ?>
                            </tr>
<?php
}
?>
                        </table>
                </center></td>
                </tr>                
                <tr>
                    <td width="100%">
                        <table class="ohnerahmen">
                            <tr>
<?php
if ($gesproblem == 1 && $inhalt['digooh'] != 1) {
?>
                                <td>
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
    if ($inhalt['inovisco'] != 1) {
?>
                                <td valign="top" class="rechts">
                                    <script type="text/javascript">
                                $(document).ready(function() {
                                    $('form').submit(function(event) {
                                        var formData = {
                                            'user' : <?php echo $user; ?>,
                                            'angebot' : <?php echo angebot; ?>
                                        };

                                        $.ajax({
                                            type        : 'POST',
                                            url         : 'inout.php',
                                            data        : formData,
                                            dataType    : 'json',
                                            encode      : true
                                        })
                                            .done(function(data) {
                                                console.log(data);
                                            });

                                        event.preventDefault();
                                    });
                                });
                                    </script>
                        <form action="https://prod-61.westeurope.logic.azure.com:443/workflows/9c9cd20cdc0f4852b73e4178e263572c/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=Y3fmlxVqVrtmWdWMp0g6VNWc6ZfcCwmx_MTU0Ao6V4A">
                        <input type="hidden" name="user" value="<?php echo $_SESSION['user']; ?>">
                        <input type="hidden" name="Buchungsnummer" value="<?php echo $angebot; ?>">
                        <input type="hidden" name="Kundennummer" value="<?php echo ""; ?>">
                        <input type="hidden" name="Kampagnenname" value="<?php echo $name; ?>">
                        <input type="hidden" name="Text" value="<?php echo $text; ?>">
                        <input type="hidden" name="pruefen" value="1">
                        <input type="hidden" name="andigooh" value="1">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
        <input type="hidden" name="user" value="<?php echo $user; ?>">
                            <button type="submit" name="inogut" 
                                class="gruen" value="1">
                            Check Verfügbarkeit</button>
                            <button type="submit" name="inoschlecht" 
                                class="rot" value="1">
                            Inovisco: Buchung ablehnen</button>
                        </form>
                                </td>
<?php
    }
//    elseif ($inhalt['digooh'] != 1 && $company == 'DIGOOH') {
    elseif ($inhalt['digooh'] != 1 && $company == 'Update Test') {
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
    else {
?>
                                <td>
                            <center>Die Pr&uuml;fung ist abgeschlossen.</center>
                                </td>
<?php
    }
}
?>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>