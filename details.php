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

if ($_POST['update'] == 1) {
    // update
    foreach ($_POST['ids'] as $value) {
        $sql = "UPDATE buchung SET "
        . "start_date = '" . $_POST['start_date'] . "', "
        . "end_date = '" . $_POST['end_date'] . "', "
        . "play_times = '" . $_POST['play_times'] . "', "
        . "name = '" . $_POST['name'] . "', "
        . "agentur = '" . $_POST['agentur'] . "', "
        . "kunde = '" . $_POST['kunde'] . "' WHERE id = " . $value;
        $erg = mysqli_query($conn, $sql);
    }
}

if ($_POST['teildelete'] == 1) {
    // partial deletion 
    foreach ($_POST['delete_teilkampagne'] as $delid) {
        $sql = "UPDATE buchung SET deleted = 1 WHERE id = " . $delid;
        $erg = mysqli_query($conn, $sql);
    }
}

if ($_GET['delete'] == 1 || $_POST['delete'] == 1) {
    // deletion
    if ($_GET['id'] != '') {
        $sql = "UPDATE buchung SET deleted = 1 WHERE id = " . $_GET['id'];
        $erg = mysqli_query($conn, $sql);
    } else {
        foreach ($_POST['delete_kampagne'] as $delid) {
            $sql = "UPDATE buchung SET deleted = 1 WHERE id = " . $delid;
            $erg = mysqli_query($conn, $sql);
        }
    }
}

if ($_GET['undo'] == 1) {
    // undo
    $sql = "UPDATE buchung SET deleted = 0 WHERE id = " . $_GET['id'];
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['digooh'] == 1) {
    // info to Digooh
    $empfaenger = "redenius@digooh.com";
    $betreff = "Neue Buchung zur Prüfung";
    $from = "info@digooh.com";
    $text = "Es wurden neue Kampagnen eingetragen: "
            . '<a href="http://88.99.184.137/inovisco_direct/details.php?'
            . 'pruefen=1&user=' . $_SESSION['user'] . '">';
    $headers = "From:" . $from;
    mail($empfaenger, $betreff, $text, $headers);
}

if ($_GET['user'] != '') {
    $_POST['user'] = $_GET['user'];
}
if ($_GET['pruefen'] == 1 || $_POST['pruefen'] == 1) {
    $user = $_POST['user'];
} else {
    $user = $_SESSION['user'];
}

if ($_POST['inogut'] == 1) {
    // Inovisco approved
    $sql = "UPDATE buchung SET inovisco = 1 WHERE user = '" . $_POST['user'] 
            . "' AND datum = '" . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['inoschlecht'] == 1) {
    // Inovisco declined
    $sql = "UPDATE buchung SET inovisco = 0 WHERE user = '" . $_POST['user'] 
            . "' AND datum = '" . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['gut'] == 1) {
    // Digooh approved
    $sql = "UPDATE buchung SET digooh = 1 WHERE user = '" . $_POST['user'] 
            . "' AND datum = '" . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['schlecht'] == 1) {
    // Digooh declined
    $sql = "UPDATE buchung SET digooh = 0 WHERE user = '" . $_POST['user'] 
            . "' AND datum = '" . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

// username
require __DIR__ .  '/vendor/autoload.php';

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

if ($_GET['angebot'] || $_POST['angebot']) {
    $angebot = $_GET['angebot'] . $_POST['angebot'];
}
if ($angebot) {
    $an = " AND angebot = " . $angebot;
}

$sql = "SELECT id, display, kunde, name, start_date, end_date, play_times,"
        . " deleted, agentur, angebot FROM buchung WHERE user = '" . $user
        . "' AND datum = '" . date("Y-m-d"). "'" . $an;
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

    require __DIR__ .  '/vendor/autoload.php';

    $client = new \GuzzleHttp\Client();

    if ($start_date != '') {
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
    
    $buchungen[] = array('agentur' => $agentur, 'name' => $name,
        'display' => $display, 'problem' => $problem, 'start_date' =>
        $start_date, 'end_date' => $end_date, 'id' => $id, 
        'deleted' => $deleted, 'restzeit' => $restzeit, 'lfsph' => $lfsph,
        'play_times' => $play_times);
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
echo '<a href="http://88.99.184.137/inovisco_direct/details.php?pruefen=1&user='
. $_SESSION['user'] . '">versenden</a>';
?>
                    </td>
                </tr>
        <tr>
            <td style="align: left;">
                <form action="details.php" method="post">
                    <input type="hidden" name="update" value="1">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
                    <?php
                    foreach ($alleid as $val) {
                    ?>
                <input type="hidden" name="ids[]" value="<?php echo $val; ?>">
                    <?php
                    }
                    ?>
                <table class="ohnerahmen" style="align: left;">
                    <tr>
                        <td width="280">Buchung durch:</td>
                        <td><?php echo $company; ?> / 
                            <?php echo $user; ?>
                        </td>
                        <td>
                            <?php if ($_POST['bearbeiten'] != 1) { ?>
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
            echo $start_date . " - " . $end_date;
        } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Einblendungen pro Stunde:</td>
                        <td>
        <?php if ($_POST['bearbeiten'] == 1) { ?>
        <input type="text" name="play_times" value="<?php echo $play_times; ?>" 
            size="10" required>
        <?php } else { 
            echo $play_times;
        } ?>
                        </td>
                        <td>
                        <?php if ($_POST['bearbeiten'] == 1) { ?>
                        <button type="submit" name="speichern" 
                                class="gruen" value="Speichern">Speichern
                        </button>
                        <?php } ?>
                        </td>
                    </tr>
                </table>
                </form>
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
                                <td valign="bottom">DisplayID</td>
                                <td>verf&uuml;gbar / h</td>
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
                    if ($inhalt['problem'] == 1) {
                        if ($inhalt['deleted'] == 1) {
                    ?>
<a href="details.php?id=<?php echo $inhalt['id']; ?>&undo=1&angebot=<?php echo $angebot; ?>">
                                <img src="abbrechengr.png" alt="l&ouml;schen">
                                    </a>
                    <?php
                        } else {
                    ?>
<a href="details.php?id=<?php echo $inhalt['id']; ?>&delete=1&angebot=<?php echo $angebot; ?>">
                                <img src="abbrechenkl.png" alt="l&ouml;schen">
                                    </a>
                    <?php
                        }
                    }
                    ?>
                                </td>
                                <td class="rechts"><?php
                    if ($inhalt['restzeit'] < 0) {
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
                    echo $prob . $inhalt['display'] . '</font>';
                    ?>
                                </td>
                                <td class="rechts">
                                    <?php echo $inhalt['lfsph']; ?>
                                </td>
                            </tr>
<?php
}
?>
                        </table>
                </center></td>
                </tr>
<?php
if ($gesproblem == 1) {
?>
                <tr>
                    <td width="100%">
                        <form action="details.php" method="post">
                        <table class="ohnerahmen">
                            <tr>
                                <td valign="left">
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
                            foreach ($teilprobleme as $item) {
                            ?>
                            <input type="hidden" name="delete_teilkampagne[]" 
                                   value="<?php echo $item; ?>">
                            <?php
                            }
                            foreach ($probleme as $item) {
                            ?>
                            <input type="hidden" name="delete_kampagne[]" 
                                   value="<?php echo $item; ?>">
                            <?php
                            }
                            ?>
                                </td>
                            </tr>
                        </table>
                        </form>
                    </td>
                </tr>
<?php
}
if ($inovisco == '') {
?>
                <tr>
                    <td class="rechts">
                        <form action="details.php" method="post">
                        <input type="hidden" name="pruefen" value="1">
                        <input type="hidden" name="andigooh" value="1">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
        <input type="hidden" name="user" value="<?php echo $user; ?>">
                        <table class="ohnerahmen" width="100%">
                            <tr>
                                <td>
                            <button type="submit" name="inogut" 
                                class="gruen" value="1">
                            Buchung best&auml;tigen</button>
                            <button type="submit" name="inoschlecht" 
                                class="rot" value="1">
                            Buchung ablehnen</button>
                                </td>
                            </tr>
                        </table>
                        </form>
                    </td>
                </tr>
<?php
}
else {
    if ($_POST['andigooh'] == 1) {
?>
                <tr>
                    <td>
                        <form action="details.php" method="post">
                  <input type="hidden" name="user" value="<?php echo $user; ?>">
                  <input type="hidden" name="pruefen" value="1">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
                        <table class="ohnerahmen" width="100%">
                            <tr>
                                <td class="mittig">
                                    <button type="submit" name="digooh" 
                                    class="gruen" value="1">
                                    Zur Pruefung an<br>Digooh senden</button>
                                </td>
                            </tr>
                        </table>
                        </form>
                    </td>
                </tr>
<?php
    }
    if ($_GET['pruefen'] == 1) {
?>
                <tr>
                    <td>
                        <form action="details.php" method="post">
                  <input type="hidden" name="user" value="<?php echo $user; ?>">
                  <input type="hidden" name="geprueft" value="1">
            <input type="hidden" name="angebot" value="<?php echo $angebot; ?>">
                        <table class="ohnerahmen" width="100%">
                            <tr>
                                <td class="mittig" width: 50%>
                            <button type="submit" name="gut" 
                                class="gruen" value="1">
                            Buchung best&auml;tigen</button>
                                </td>
                                <td class="mittig">
                            <button type="submit" name="schlecht" 
                                class="rot" value="1">
                            Buchung ablehnen</button>
                                </td>
                            </tr>
                        </table>
                        </form>
                    </td>
                </tr>
<?php
    }
    if ($_POST['geprueft'] == 1) {
?>
                <tr>
                    <td>
                        Die Pr&uuml;fung ist abgeschlossen.
                    </td>
                </tr>
<?php
    }
}
?>
            </table>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>