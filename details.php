<?php echo"2";
require_once 'datenbank.php';

$sql = "SELECT * FROM buchung";
$db_erg = mysqli_query($conn, $sql);

$problem = 1;
if ($problem) {
?>
Das Hochladen war erfolgreich. Nicht alle Displays oder Slots sind verf&uuml;gbar!
F&uuml;r eine Buchung muss die Kampagne ge&auml;ndert und die Verf&uuml;gbarkeit 
erneut gepr&uuml;ft werden!
<?php
} else {
?>
Das Hochladen war erfolgreich. Alle Displays und Slots sind verf&uuml;gbar, 
die Kampagne kann zur Prüfung an Digooh gesendet werden!
<?php
}
?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>Kampagne</td>
                                        <td>DisplayID</td>
                                        <td>Slot</td>
                                        <td>01.01.</td>
                                        <td>02.01.</td>
                                        <td>03.01.</td>
                                        <td>04.01.</td>
                                    </tr>                                        
<?php
while ($row = mysqli_fetch_array( $db_erg, MYSQLI_ASSOC)) {
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'https://cms.digooh.com:8081/api/v1/campaigns',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'players' => in_array($row['display'], 'players')
            ],
        ]
    );
    $body = $response->getBody();echo"<pre>";
    print_r(json_decode((string) $body));

?>
                                    <tr>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['display']; ?></td>
                                        <td><?php echo $row['slot']; ?></td>
                                        <td>
                                            <?php
                                            $datum = '2021-01-14';
                                            if($datum >= $row['start_date'] AND $datum <= $row['end_date']) {
                                            echo "X"; 
                                            }
                                            ?>
                                        </td>
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
                                <table class="ohnerahmen">
                                    <tr>
                                        <td class="mittig" width: 33,33%>
                                <form action="auswahl.php" method="post">
                                    <button type="submit" name="neu1" 
                                        class="lila" value="1">
                                    Verf&uuml;gbarkeit<br>erneut pr&uuml;fen
                                    </button>
                                </form>
                                        </td>
                                        <td class="mittig" width: 33,33%>
                                <form action="auswahl.php" method="post">
                                    <button type="submit" name="neu2" 
                                        class="lila" value="1">
                                    Zur &Uuml;bersicht</button>
                                </form>
                                        </td>
                                        <td class="mittig" width: 33,33%>
                                <form action="prozess.php" method="post">
                                    <button type="submit" name="neu3" 
                                        class="rot" value="1">
                                    Komplette Kampagne<br>l&ouml;schen</button>
                                </form>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
<?php
} else {
?>
                        <tr>
                            <td>
                                <table class="ohnerahmen" width="100%">
                                    <tr>
                                        <td class="mittig" width: 50%>
                                <form action="auswahl.php" method="post">
                                    <button type="submit" name="neu4" 
                                        class="lila" value="1">
                                    Zur &Uuml;bersicht</button>
                                </form>
                                        </td>
                                        <td class="mittig">
                                <form action="prozess.php" method="post">
                                    <button type="submit" name="neu5" 
                                        class="gruen" value="1">
                                    Zur Pruefung an<br>Digooh senden</button>
                                </form>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
<?php
}
?>