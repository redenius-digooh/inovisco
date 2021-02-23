<?php
/*
 * It is checked whether there are still enough slots that are needed in the 
 * campaign.
 */
require_once 'db.php';

if ($_POST['neuupload'] == 1) {
    header("Location: http://88.99.184.137/inovisco_direct/buchung.php");
}

if ($_GET['delete'] == 1) {
    if ($_GET['id'] != '') {
        $sql = "DELETE FROM buchung WHERE id = " . $_GET['id'];
        $erg = mysqli_query($conn, $sql);
    } else {
        foreach ($_POST['delete_kampagne'] as $delid) {
            $sql = "DELETE FROM buchung WHERE id = " . $delid;
            $erg = mysqli_query($conn, $sql);
        }
    }
}

if ($_POST['digooh'] == 1) {
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
    $sql = "UPDATE buchung SET inovisco = 1 WHERE user = '" . $_POST['user'] 
            . "' AND datum = '" . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['inoschlecht'] == 1) {
    $sql = "UPDATE buchung SET inovisco = 0 WHERE user = '" . $_POST['user'] 
            . "' AND datum = '" . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['gut'] == 1) {
    $sql = "UPDATE buchung SET digooh = 1 WHERE user = '" . $_POST['user'] 
            . "' AND datum = '" . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['schlecht'] == 1) {
    $sql = "UPDATE buchung SET digooh = 0 WHERE user = '" . $_POST['user'] 
            . "' AND datum = '" . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

require __DIR__ .  '/vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$response = $client->get(
    'https://cms.digooh.com:8082/api/v1/users',
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

$sql = "SELECT id, name, start_date, end_date, play_times, campaign, display, "
        . "agentur, inovisco, digooh FROM buchung WHERE user = '" . $user . 
        "' AND datum = '" . date("Y-m-d"). "'";
$db_erg = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array( $db_erg)) {
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $play_times = $row['play_times'];
    $name = $row['name'];
    $display = $row['display'];
    $agentur = $row['agentur'];
    $id = $row['id'];
    $inovisco = $row['inovisco'];
    $digooh = $row['digooh'];

    $sql = "SELECT start_date, end_date, play_type, play_times FROM kampagne "
            . " WHERE start_date <= '" .$start_date . "' AND end_date >= '"
            . $start_date . "'";
    $erg = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array( $erg)) {
        $alleslots = 8640;
        if ($row['play_type'] == 1) {
            //Percentage
            $slot = $alleslots / 100 * $row['play_times'];
            $gesslot += $slot;
        }
        elseif ($row['play_type'] == 2) {
            $interval = date_diff($row['start_date'], $row['end_date']);
            //Total Views
            $slot = $row['play_times'] / $interval;
            $gesslot += $slot;
        }
        elseif ($row['play_type'] == 9) {
            //Every xth Slot
            $slot = $alleslots / $row['play_times'];
            $gesslot += $slot;
        }
        else {
            //Times per Hour
            $slot = $row['play_times'] * 24;
            $gesslot += $slot;
        }    
    }

    $zusammenslot = $gesslot + ($play_times * 24);
    if ($zusammenslot > $alleslots) {
        $problem = 1;
        $gesproblem = 1;
        $probleme[] = $id;
    } else {
        $problem = 0;
    }
    $buchungen[] = array('agentur' => $agentur, 'name' => $name,
        'display' => $display, 'problem' => $problem, 'start_date' =>
        $start_date, 'end_date' => $end_date);
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
                <table class="ohnerahmen" style="align: left;">
                    <tr>
                        <td>Buchung durch: <?php echo $company; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php 
                        echo "Agentur: ".$buchungen[0]['agentur']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Endkunde:
                        </td>
                    </tr>
                    <tr>
                        <td><?php 
                        echo "Kampagnenname: " . $buchungen[0]['name']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php 
                        echo "Kampganenzeitraum: " . $buchungen[0]['start_date'];
                        echo " - ";
                        echo $buchungen[0]['end_date'];
                        ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td>Aktion</td>
                                <td>Agentur</td>
                                <td>Kampagne</td>
                                <td>DisplayID</td>
                                <td>Slot</td>
                                <td>01.01.</td>
                                <td>02.01.</td>
                                <td>03.01.</td>
                                <td>04.01.</td>
                            </tr>                                        
<?php
foreach ($buchungen as $key => $inhalt) {
?>
                            <tr>
                                <td>
                                    <a href="details.php?id=<?php echo $id; ?>
                                       &delete=1">
                                  <img src="abbrechenkl.png" alt="l&ouml;schen">
                                    </a>
                                </td>
                                <td><?php echo $inhalt['agentur']; ?></td>
                                <td><?php echo $inhalt['name']; ?></td>
                                <td><?php
                                    if ($inhalt['problem'] == 1) {
                                    $prob = '<font style="color: red">';
                                    } else {
                                    $prob = '<font style="color: green">';
                                    }
                                    echo $prob . $inhalt['display'] . '</font>';
                                    ?></td>
                                <td>1<?php echo $row['slot']; ?></td>
                                <td>X</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
<?php
}
?>
                        </table>
                    </td>
                </tr>
<?php
if ($problem) {
?>
                <tr>
                    <td width="100%">
                        <form action="details.php" method="post">
                        <table class="ohnerahmen">
                            <tr>
                                <td class="mittig" width: 33,33%>
                            <button type="submit" name="neuupload" 
                                class="rot" value="1">
                            Neuer Upload</button>
                                </td>
                                <td class="mittig" width: 33,33%>
                            <button type="submit" name="delete" 
                                class="rot" value="1">
                            Kampagnen<br>l&ouml;schen</button>  
                                </td>
                            </tr>
                        </table>
                            <?php
                            foreach ($probleme as $item) {
                            ?>
                            <input type="hidden" name="delete_kampagne[]" 
                                   value="<?php echo $item; ?>">
                            <?php } ?>
                        </form>
                    </td>
                </tr>
<?php
} else {
    if ($inovisco == '') {
?>
                <tr>
                    <td>
                        <form action="details.php" method="post">
                        <input type="hidden" name="pruefen" value="1">
                        <input type="hidden" name="andigooh" value="1">
        <input type="hidden" name="user" value="<?php echo $user; ?>">
                        <table class="ohnerahmen" width="100%">
                            <tr>
                                <td class="mittig" width: 50%>
                            <button type="submit" name="inogut" 
                                class="gruen" value="1">
                            Buchung best&auml;tigen</button>
                                </td>
                                <td class="mittig">
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
}
?>
            </table>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>