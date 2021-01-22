<?php
/*
 * Overview of the current booking processes.
 */
require_once 'db.php';
require_once 'oben.php';

if ($_POST['was'] == 'upload') {
    $was = 'AND upload = 1';
}
elseif ($_POST['was'] == 'noupload') {
    $was = 'AND upload = 0';
}
elseif ($_POST['was'] == 'inovisco') {
    $was = 'AND inovisco = 1';
}
elseif ($_POST['was'] == 'noupload') {
    $was = 'AND digooh = 1';
}
else{}

if ($_POST['wo'] == 'up') {
    $was = ' ORDER BY datum';
}
elseif ($_POST['wo'] == 'down') {
    $was = ' ORDER BY datum desc';
}
else {}

$sql = "SELECT name, upload, inovisco, digooh FROM buchung WHERE user = '" . 
        $_SESSION['user'] . "'" . $was;
$result = $conn->query($sql);
?>
                            <table class="ohnerahmen">
                                <tr>
                                    <td class="balken">
                                        <form action="uebersicht.php" method="post">
                                        <table class="ohnerahmen">
            <tr>
                <td colspan="3">
                    Alle Buchungen mit:
                    <select name="was">
                        <option value="-">---</option>
                        <option value="upload">Upload Buchung</option>
                        <option value="noupload">ohne Upload Buchung</option>
                        <option value="inovisco">Pr&uuml;fung Inovisco</option>
                        <option value="digooh">Pr&uuml;fung Digooh</option>
                    </select>
                </td>
                <td colspan="5">
                    Sortierung:
                    <select name="wo">
                        <option value="-">---</option>
                        <option value="up">Erstelldatum aufsteigend</option>
                        <option value="down">Erstelldatum absteigend</option>
                    </select>
                    &nbsp;&nbsp;&nbsp;
                    <button type="submit" name="neu" value="1">
                                Suchen</button>
                </td>
            </tr>
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <td width="90px">
                                    <table class="table_klein">
                                        <tr>
                                            <?php if ($row['upload'] == 1) { ?>
                                            <td class="gruen">
                                            <?php } else { ?>
                                            <td class="rot">
                                            <?php } ?>
                                                Upload Buchung</td>
                                        </tr>
                                    </table>
                                </td>
                                <td>&#10132;</td>
                                <td width="90px">
                                    <table class="table_klein">
                                        <tr>
                                            <?php if ($row['inovisco'] == 1) 
                                            { ?>
                                            <td class="gruen">
                                            <?php } else { ?>
                                            <td>
                                            <?php } ?>
                                                Pr&uuml;fung Inovisco</td>
                                        </tr>
                                    </table>
                                </td>
                                <td>&#10132;</td>
                                <td width="90px">
                                    <table class="table_klein">
                                        <tr>                                                
                                            <?php if ($row['digooh'] == 1) 
                                            { ?>
                                            <td class="gruen">
                                            <?php } else { ?>
                                            <td>
                                            <?php } ?>
                                                Pr&uuml;fung Digooh</td>
                                        </tr>
                                    </table>
                                </td>
                                <td>&#10132;</td>
                                <td width="90px">
                                    <table class="table_klein">
                                        <tr>
                                            <?php if ($row['upload'] == 1 &&
                                            $row['inovisco'] == 1 &&
                                            $row['digooh'] == 1) {?>
                                            <td class="gruen">
                                            <?php } else { ?>
                                            <td>
                                            <?php } ?>
                                            Buchung abgeschlossen</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
<?php
    }
}
?>
                                        </table>
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
        </center>
    </body>
</html>