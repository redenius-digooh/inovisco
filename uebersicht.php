<?php
/*
 * Overview of the current booking processes.
 */
require_once 'db.php';
require_once 'oben.php';

if ($_POST['was'] == 'upload') {
    $was = " AND upload = 1";
}
elseif ($_POST['was'] == 'noupload') {
    $was = " AND upload = 0";
}
elseif ($_POST['was'] == 'inovisco') {
    $was = " AND inovisco = 1";
}
elseif ($_POST['was'] == 'noupload') {
    $was = " AND digooh = 1";
}
else{}

if (!empty($_POST['agent'])) {
    $agent = " AND agentur = '" . $_POST['agent'] . "'";
}

if ($_POST['wo'] == 'up') {
    $order = " ORDER BY datum";
}
elseif ($_POST['wo'] == 'down') {
    $order = " ORDER BY datum desc";
}
else {}

$sql = "SELECT agentur FROM buchung WHERE user"
        . " = '" . 
        $_SESSION['user'] . "'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if (!in_array($row['agentur'], $agenturen)) {
            $agenturen[] = $row['agentur'];
        }
    }
}

$sql = "SELECT DISTINCT(angebot), name, agentur, upload, inovisco, digooh FROM "
        . "buchung WHERE user = '" . 
        $_SESSION['user'] . "'" . $was . $agent . $order;
$result = $conn->query($sql);
?>
                        <table class="ohnerahmen">
                            <tr>
                                <td class="blau">
                                    Laufende Buchungsprozesse
                                </td>
                            </tr>
                        </table>
                        <table class="ohnerahmen">
                            <tr>
                                <td class="balken">
                                    <form action="uebersicht.php" method="post">
                                    <table class="ohnerahmen">
            <tr>
                <td colspan="9">
                    <center>F&uuml;r Details Kampagne anklicken!</center><br>
                    <table class="ohnerahmen">
                        <tr>
<td>
    Alle Buchungen mit:
    <select name="was">
        <option value="">---</option>
        <option value="upload" <?php if ($_POST['was'] == 'upload') 
            echo "selected"; ?>>Upload Buchung</option>
        <option value="noupload" <?php if ($_POST['was'] == 'noupload') 
            echo "selected"; ?>>ohne Upload Buchung</option>
        <option value="inovisco" <?php if ($_POST['was'] == 'inovisco') 
            echo "selected"; ?>>Pr&uuml;fung Inovisco</option>
        <option value="digooh" <?php if ($_POST['was'] == 'digooh') 
            echo "selected"; ?>>Pr&uuml;fung Digooh</option>
    </select>
</td>
<td>
    Agentur:
    <select name="agent">
        <option value="">---</option>
        <?php
        foreach ($agenturen as $value) {
        ?>
    <option value="<?php echo $value; ?>" <?php if ($_POST['agent'] == $value) 
            echo "selected"; ?>><?php echo $value; ?>
    </option>
        <?php } ?>
    </select>
</td>
<td>
    Sortierung:
    <select name="wo">
        <option value="">---</option>
        <option value="up" <?php if ($_POST['wo'] == 'up') 
            echo "selected"; ?>>Erstelldatum aufsteigend</option>
        <option value="down" <?php if ($_POST['wo'] == 'down') 
            echo "selected"; ?>>Erstelldatum absteigend</option>
    </select>
</td>
<td>
    <button type="submit" name="neu" value="1">
                Suchen</button>
</td>
        </tr>
    </table>
</td>
                            </tr>
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
?>
                            <tr>
                                <td><?php echo $row['agentur']; ?></td>
            <td><a href="details.php?angebot=<?php echo $row['angebot']; ?>">
                                        <?php echo $row['name']; ?></a></td>
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
                                <td>&#10132;&#10132;</td>
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
                                <td>&#10132;&#10132;</td>
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
                                <td>&#10132;&#10132;</td>
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