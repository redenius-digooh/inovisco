<?php
/*
 * It is checked whether there are still enough slots that are needed in the 
 * campaign.
 */
require_once 'db.php';

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

$sql = "SELECT id, name, start_date, end_date, play_times, campaign, display, "
        . "agentur, inovisco, digooh FROM buchung WHERE digooh IS NULL" 
        . "' AND datum = '" . date("Y-m-d"). "'";
$db_erg = mysqli_query($conn, $sql);

require_once 'oben.php';
?>
            <table class="ohnerahmen">
                <tr>
                    <td>
                        <table>
                            <tr>
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
while ($row = mysqli_fetch_array($db_erg)) {
?>
                            <tr>
                                <td><?php echo $row['agentur']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['display']; ?></td>
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
if ($_POST['geprueft'] != 1) {
?>
                <tr>
                    <td>
                        <form action="details.php" method="post">
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
} else {
?>
                <tr>
                    <td>
                        Die Pr&uuml;fung ist abgeschlossen.
                    </td>
                </tr>
<?php
}
?>
            </table>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>