<?php
/*
 * It is checked whether there are still enough slots that are needed in the 
 * campaign.
 */
require_once 'db.php';

$sql = "SELECT id, name, start_date, end_date, play_times, campaign, display, "
        . "agentur, inovisco, digooh FROM buchung WHERE datum = '" 
        . date("Y-m-d"). "' AND inovisco IS NULL";
$db_erg = mysqli_query($conn, $sql);

if ($_POST['delete'] == 1) {
    foreach ($_POST['delete_kampagne'] as $delid) {
        $sql = "DELETE FROM buchung WHERE id = " . $delid;
        $erg = mysqli_query($conn, $sql);
    }
}

if ($_POST['inogut'] == 1) {
    $sql = "UPDATE buchung SET inovisco = 1 WHERE datum = '" 
            . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

if ($_POST['inoschlecht'] == 1) {
    $sql = "UPDATE buchung SET inovisco = 0 WHERE datum = '" 
            . date("Y-m-d"). "'";
    $erg = mysqli_query($conn, $sql);
}

while ($row = mysqli_fetch_array($db_erg)) {
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $play_times = $row['play_times'];
    $name = $row['name'];
    $display = $row['display'];
    $agentur = $row['agentur'];
    $id = $row['id'];
    $inovisco = $row['inovisco'];
    $digooh = $row['digooh'];

    $sql = "SELECT id,start_date, end_date, play_type, play_times FROM kampagne "
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
        $start_date, 'end_date' => $end_date, 'id' => $id, 'inovisco' =>
        $inovisco);
}
require_once 'oben.php';
?>
            <center>
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
foreach ($buchungen as $key => $inhalt) {
?>
                            <tr>
                                <td><?php echo $inhalt['id'] . $inhalt['agentur']; ?></td>
                                <td><?php echo $inhalt['name']; ?></td>
                                <td><?php if ($inhalt['problem'] == 1) {
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
                        <form action="ohneinovisco.php" method="post">
                        <table class="ohnerahmen">
                            <tr>
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
    if ($inhalt['inovisco'] != 1) {
?>
                <tr>
                    <td>
                        <form action="ohneinovisco.php" method="post">
                        <input type="hidden" name="pruefen" value="1">
                        <input type="hidden" name="andigooh" value="1">
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
    } else {
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
            </center>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>