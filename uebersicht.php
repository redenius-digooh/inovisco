<?php
/*
 * Overview of the current booking processes.
 */
require_once 'db.php';
require_once 'oben.php';
mysqli_query($conn, "SET NAMES 'utf8'");

if ($_SESSION['company'] != 'DIGOOH' && $_SESSION['company'] != 'Update Test') {
	$whereuser = "WHERE user = '" . $_SESSION['user'] . "'";
	$user = "user = '" . $_SESSION['user'] . "'";
	$wherecompany = " AND b.company = '" . $_SESSION['company'] . "'";
} else {
	$user = '1=1';
	$wherecompany = '';
}

if (!empty($_GET['papierkorb']) && $_GET['papierkorb'] == 1) {
	$sql = 'UPDATE buchung SET papierkorb = 1 WHERE id = ' . $_GET['id'];
	$erg = mysqli_query($conn, $sql);
}

if (!empty($_POST['was'])) {
	if ($_POST['was'] == 'upload') {
		$was = ' AND upload = 1';
	} elseif ($_POST['was'] == 'noupload') {
		$was = ' AND upload = 0';
	} elseif ($_POST['was'] == 'inovisco') {
		$was = ' AND inovisco = 1';
	} elseif ($_POST['was'] == 'digooh') {
		$was = ' AND digooh = 1';
	} else {
		$was = '';
	}
} else {
	$was = '';
}

if (!empty($_POST['agent'])) {
	$agent = " AND agentur = '" . $_POST['agent'] . "'";
} else {
	$agent = '';
}

if (!empty($_POST['kund'])) {
	$kund = " AND kunde = '" . $_POST['kund'] . "'";
} else {
	$kund = '';
}

if (!empty($_POST['angeb'])) {
	$angeb = " AND angebot = '" . $_POST['angeb'] . "'";
} else {
	$angeb = '';
}
if (!empty($_POST['wo'])) {
	if ($_POST['wo'] == 'up') {
		$order = ' ORDER BY datum';
	} elseif ($_POST['wo'] == 'down') {
		$order = ' ORDER BY datum desc';
	} else {
	}
} else {
	$order = '';
}

// get the agency
$sql = 'SELECT agentur FROM buchung AS a'
		. ' LEFT JOIN user AS b ON a.user = b.user'
		. " WHERE b.company = '" . $_SESSION['company'] . "'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	$agenturen = [];
	while ($row = $result->fetch_assoc()) {
		if (!in_array($row['agentur'], $agenturen)) {
			$agenturen[] = $row['agentur'];
		}
	}
}

// get the customer
$sql = 'SELECT kunde FROM buchung AS a'
		. ' LEFT JOIN user AS b ON a.user = b.user'
		. " WHERE b.company = '" . $_SESSION['company'] . "'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	$kundenarr = [];
	while ($row = $result->fetch_assoc()) {
		if (!in_array($row['kunde'], $kundenarr)) {
			$kundenarr[] = $row['kunde'];
		}
	}
}

// get all bookings
$sql = 'SELECT DISTINCT(angebot), a.id, name, agentur, upload, inovisco, digooh, '
	. 'datum, kunde FROM buchung AS a'
	. ' LEFT JOIN user AS b ON a.user = b.user'
	. ' WHERE papierkorb IS NULL'
	. $wherecompany . $was . $agent . $kund
	. $angeb . $order;
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
                        <td colspan="9" class="zelle">
                            <center>F&uuml;r Details Kampagne anklicken!</center><br>
                            <table class="ohnerahmen">
                                <tr>
                                    <td class="zelle">Alle Buchungen mit:
                                        <select name="was" style="width: 240px;">
                                            <option value="">---</option>
                                            <option value="upload" <?php if (isset($_POST['was']) && $_POST['was'] == 'upload') {
	echo 'selected';
} ?>>Upload Buchung
                                            </option>
                                            <option value="noupload" <?php if (isset($_POST['was']) && $_POST['was'] == 'noupload') {
	echo 'selected';
} ?>>ohne Upload Buchung
                                            </option>
                                            <option value="inovisco" <?php if (isset($_POST['was']) && $_POST['was'] == 'inovisco') {
	echo 'selected';
} ?>>Pr&uuml;fung Inovisco
                                            </option>
                                            <option value="digooh" <?php if (isset($_POST['was']) && $_POST['was'] == 'digooh') {
	echo 'selected';
} ?>>Pr&uuml;fung Digooh
                                            </option>
                                        </select>
                                    </td>
                                    <td class="zelle">
                                        Agentur:
                                        <select name="agent" style="width: 240px;">
                                            <option value="">---</option>
                                            <?php
		foreach ($agenturen as $value) {
			?>
                                            <option
                                                value="<?php echo $value; ?>"
                                                <?php if (isset($_POST['agent']) && $_POST['agent'] == $value) {
				echo 'selected';
			} ?>><?php echo $value; ?>
                                            </option>
                                            <?php
		} ?>
                                        </select>
                                    </td>
                                    <td class="zelle">
                                        Sortierung:
                                        <select name="wo" style="width: 240px;">
                                            <option value="">---</option>
                                            <option value="up" <?php if (isset($_POST['wo']) && $_POST['wo'] == 'up') {
			echo 'selected';
		} ?>>Erstelldatum aufsteigend
                                            </option>
                                            <option value="down" <?php if (isset($_POST['wo']) && $_POST['wo'] == 'down') {
			echo 'selected';
		} ?>>Erstelldatum absteigend
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="zelle">
                                        Kunde:<br>
                                        <select name="kund" style="width: 240px;">
                                            <option value="">---</option>
                                            <?php
					foreach ($kundenarr as $value) {
						?>
                                            <option
                                                value="<?php echo $value; ?>"
                                                <?php if ($_POST['kund'] == $value) {
							echo 'selected';
						} ?>><?php echo $value; ?>
                                            </option>
                                            <?php
					}
					?>
                                    </td>
                                    <td colspan="2" class="zelle">
                                        Angebotsnummer:<br>
                                        <input type="text" name="angeb"
                                            value="<?php echo $_POST['angeb'] ?? ''; ?>"
                                            style="width: 240px;">
                                    </td>
                                    <td valign="bottom">
                                        <button type="submit" name="neu" class="grau" value="1">
                                            Suchen</button>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table><br>
                <table class="ohnerahmen">
                    <tr>
                        <td class="rahmenunten" valign="bottom">
                            Kampagne</td>
                        <td class="rahmenunten" valign="bottom">
                            Agentur</td>
                        <td class="rahmenunten" valign="bottom">
                            Angebots-nummer</td>
                        <td class="rahmenunten" valign="bottom">
                            Kunde</td>
                        <td class="rahmenunten" valign="bottom">
                            Erstelldatum</td>
                        <td class="rahmenunten" valign="bottom">
                            <img src="papierkorb.png" alt="Papierkorb">
                        </td>
                        <td colspan="8"></td>
                    </tr>
                    <?php
if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							?>
                    <tr>
                        <td class="zelle" valign="bottom"><a
                                href="details.php?angebot=<?php echo $row['angebot']; ?>">
                                <?php echo $row['name']; ?></a>
                        </td>
                        <td class="zelle" valign="bottom"><?php echo $row['agentur']; ?>
                        </td>
                        <td class="zelle" valign="bottom"><?php echo $row['angebot']; ?>
                        </td>
                        <td class="zelle" valign="bottom"><?php echo $row['kunde']; ?>
                        </td>
                        <td class="zelle" valign="bottom"><?php echo $row['datum']; ?>
                        </td>
                        <td class="zelle" valign="bottom">
                            <?php if ($row['inovisco'] != 1) { ?>
                            <a
                                href="uebersicht.php?papierkorb=1&id=<?php echo $row['id']; ?>">
                                <img src="papierkorb.png" alt="Papierkorb">
                            </a>
                            <?php } ?>
                        </td>
                        <td width="90px" class="zelle" valign="bottom">
                            <table class="table_klein">
                                <tr>
                                    <?php if ($row['upload'] == 1) { ?>
                                    <td class="gruen">Upload Buchung</td>
                                    <?php } elseif ($row['upload'] == 2) { ?>
                                    <td class="gruen">Manuelle Buchung</td>
                                    <?php } else { ?>
                                    <td class="rot">Upload Buchung</td>
                                    <?php } ?>
                                </tr>
                            </table>
                        </td>
                        <td class="zelle" valign="bottom">&#10132;</td>
                        <td width="90px" class="zelle" valign="bottom">
                            <table class="table_klein">
                                <tr>
                                    <?php if ($row['inovisco'] == 1) { ?>
                                    <td class="gruen">
                                        <?php } else { ?>
                                    <td class="grau">
                                        <?php } ?>
                                        Pr&uuml;fung Inovisco
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="zelle" valign="bottom">&#10132;</td>
                        <td width="90px" class="zelle" valign="bottom">
                            <table class="table_klein">
                                <tr>
                                    <?php if ($row['digooh'] == 1) { ?>
                                    <td class="gruen">
                                        <?php } else { ?>
                                    <td class="grau">
                                        <?php } ?>
                                        Pr&uuml;fung Digooh
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="zelle" valign="bottom">&#10132;</td>
                        <td width="90px" class="zelle" valign="bottom">
                            <table class="table_klein">
                                <tr>
                                    <?php if (($row['upload'] == 1 ||
											$row['upload'] == 2) &&
											$row['inovisco'] == 1 &&
											$row['digooh'] == 1) {?>
                                    <td class="gruen">
                                        <?php } else { ?>
                                    <td class="grau">
                                        <?php } ?>
                                        Buchung abgeschlossen
                                    </td>
                                </tr>
                            </table>
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
</td>
</tr>
</table>
</center>
</body>

</html>