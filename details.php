<?php
/*
 * It is checked whether there are still enough slots that are needed in the 
 * campaign.
 */
$sql = "SELECT name, start_date, end_date, play_times, campaign, display FROM "
        . "buchung WHERE user = '" . $_SESSION['user'] . "' AND datum"
        . "= '" . date("Y-m-d"). "'";
$db_erg = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array( $db_erg)) {
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $play_times = $row['play_times'];
    $name = $row['name'];
    $display = $row['display'];

    $sql = "SELECT start_date, end_date, play_type, play_times FROM kampagne WHERE "
            . "start_date <= '" .$start_date . "' AND end_date >= '" . $start_date 
            . "'";
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
    } else {
        $problem = 0;
    }
    $buchungen[] = array('name' => $name, 'display' => $display,
                        'problem' => $problem);
}
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
foreach ($buchungen as $key => $inhalt) {
?>
                                    <tr>
                                        <td><?php echo $inhalt['name']; ?></td>
                                        <td>
                                            <?php
                                            if ($inhalt['problem'] == 1) {
                                            ?>
                                            <font style="color: red">
                                            <?php
                                            } else {
                                                ?>
                                            <font style="color: green">
                                            <?php
                                            }
                                            echo $inhalt['display'];
                                            ?>
                                            </font>
                                        </td>
                                        <td>1<?php echo $row['slot']; ?></td>
                                        <td>X
                                            <?php
                                            /*$datum = '2021-01-14';
                                            if($datum >= $start_date AND 
                                                $datum <= $end_date) {
                                            echo "X"; 
                                            }*/
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