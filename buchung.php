<?php
/*
 * Upload Excel file and display details.
 */
session_start();
require_once 'db.php';
mysqli_query($conn, "SET NAMES 'utf8'");
require __DIR__ . '/vendor/autoload.php';

$namefehlt = 0;

// upload file
if (isset($_FILES['datei']) && $_POST['neu'] == 1) {
	if ($_FILES['datei']['type'] ==
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
		$tag = date('Y-m-d-H-i-s');
		$filename = $_FILES['datei']['name'];
		$uploaded_dir = 'uploadfiles/';
		$path = $uploaded_dir . $filename;
		move_uploaded_file($_FILES['datei']['tmp_name'], $path);
		$upload = 1;
		require_once 'import.php';
	} else {
		$wrongtyp = 1;
	}
}

// manuel entry
if ($_GET['manuell'] == 1 || $_POST['manuell'] == 1) {
	require_once __DIR__ . '/vendor/autoload.php';

	// get all criteria and player
	require_once 'getall.php';

	// get max offer
	$sql = 'SELECT MAX(angebot) AS angebot FROM buchung';
	$db_erg = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($db_erg)) {
		$angebot = $row['angebot'] + 1;
	}
}

// update
if ($_POST['speichern'] == 1) {
	// get max offer
	$sql = 'SELECT MAX(angebot) AS angebot FROM buchung';
	$db_erg = mysqli_query($conn, $sql);
	while ($row = mysqli_fetch_array($db_erg)) {
		$angebot = $row['angebot'] + 1;
	}

	$d1 = substr($_POST['firstinput'], 3, 2);
	$m1 = substr($_POST['firstinput'], 0, 2);
	$y1 = substr($_POST['firstinput'], 6, 4);
	$d2 = substr($_POST['secondinput'], 3, 2);
	$m2 = substr($_POST['secondinput'], 0, 2);
	$y2 = substr($_POST['secondinput'], 6, 4);

	$start_date = $y1 . '-' . $m1 . '-' . $d1;
	$end_date = $y2 . '-' . $m2 . '-' . $d2;

	$sd = explode('/', $_POST['start_date']);
	$ed = explode('/', $_POST['end_date']);
	$checks = checkdate($m1, $d1, $y1);
	$checke = checkdate($m2, $d2, $y2);

	if (date("$y2-$m2-$d2") < date('Y-m-d')) {
		$error = 'Das Enddatum muss in der Zukunft liegen!';
	} elseif (date("$y2-$m2-$d2") < date("$y1-$m1-$d1")) {
		$error = 'Das Startdatum muss vor dem Enddatum liegen!';
	} elseif (!$checks || !$checke) {
		$error = 'Das Startdatum oder Enddatum war nicht korrekt!';
	} elseif ($_POST['play_times'] < 0 || $_POST['play_times'] > 360) {
		$error = 'Die "Einblendungen pro Stunde" m&uuml;ssen einen Wert zwischen'
				. ' 0 und 360 haben!';
	} elseif ($_POST['sammelkriterium'] == '' && $_POST['sammelplayer'] == '') {
		$error = 'Es muss ein Kriterium oder ein Display ausgew&auml;hlt werden!';
	} else {
		$binarr = explode(', ', $_POST['bindkriterium']);
		$bind = [];
		if (is_array($binarr)) {
			if (count($binarr) > 0 && $binarr[0] != '') {
				foreach ($binarr as $bineri) {
					// get criteria id with given name
					$sql = "SELECT id FROM criteria WHERE name = '" . $bineri . "'";
					$db_erg = mysqli_query($conn, $sql);
					while ($row = mysqli_fetch_array($db_erg)) {
						$bind[] = $row['id'];
					}
				}
			}
		}

		$ausarr = explode(', ', $_POST['auskriterium']);
		$aus = [];
		if (is_array($ausarr)) {
			if (count($ausarr) > 0 && $ausarr[0] != '') {
				foreach ($ausarr as $auseri) {
					// get criteria id with given name
					$sql = "SELECT id FROM criteria WHERE name = '" . $auseri . "'";
					$db_erg = mysqli_query($conn, $sql);
					while ($row = mysqli_fetch_array($db_erg)) {
						$aus[] = $row['id'];
					}
				}
			}
		}

		$kriarr = explode(', ', $_POST['sammelkriterium']);
		$krit = [];
		$_POST['player'] = [];
		if (is_array($kriarr)) {
			if (count($kriarr) > 0 && $kriarr[0] != '') {
				foreach ($kriarr as $kriteri) {
					// get criteria id with given name
					$sql = "SELECT id FROM criteria WHERE name = '" . $kriteri . "'";
					$db_erg = mysqli_query($conn, $sql);
					while ($row = mysqli_fetch_array($db_erg)) {
						$einzelkriterium = $row['id'] . ',';
						$krit[] = $row['id'];
					}

					// get players
					$client = new \GuzzleHttp\Client();
					$response = $client->get(
						'https://cms.digooh.com:8081/api/v1/players',
						[
							'headers' => [
								'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
								'Content-Type' => 'application/json',
								'Accept' => 'application/json',
							],
							'query' => [
								'include' => 'criteria',
								'filter[criteria]' => $einzelkriterium,
								'filter[bind_criteria]' => $bind,
								'filter[ex_criteria]' => $aus,
								'limit' => '130'
							]
						]
					);
					$body = $response->getBody();
					$data = json_decode((string) $body);
					// only player width AA-criterion
					foreach ($data->data as $key => $value) {
						$_POST['player1'][] = $value->id;
					}
				}
			}
		}

		if ($_POST['pps1'] > 0 || $_POST['pps2'] > 0) {
			$_POST['player2'] = [];
			if ($_POST['pps1'] > 0) {
				$ppsf = ' WHERE a.pps >= ' . $_POST['pps1'];
				$pps = $_POST['pps1'];
			}

			if ($_POST['pps2'] > 0) {
				$ppsf = ' WHERE a.pps >= ' . $_POST['pps2'];
				$pps = $_POST['pps2'];
			}

			$_POST['player'] = $_POST['player1'];
		} else {
			$pps = 0;
			$_POST['player'] = $_POST['player1'];
		}

		$playermark = 0;
		$playerarr = explode(', ', $_POST['sammelplayer']);
		if (is_array($playerarr)) {
			if ($playerarr[0] != '') {
				$playermark = 1;
				foreach ($playerarr as $playe) {
					// get player id with given name
					$sql = "SELECT id FROM player WHERE name = '" . $playe . "'";
					$db = mysqli_query($conn, $sql);
					while ($row = mysqli_fetch_array($db)) {
						if (!in_array($row['id'], $_POST['player'])) {
							$_POST['player'][] = $row['id'];
						}
					}
				}
			}
		}

		// insert into buchung
		if ($_POST['player'][0] == '') {
			$_POST['player'][] = 0;
		}

		$d1 = substr($_POST['firstinput'], 3, 2);
		$m1 = substr($_POST['firstinput'], 0, 2);
		$y1 = substr($_POST['firstinput'], 6, 4);
		$d2 = substr($_POST['secondinput'], 3, 2);
		$m2 = substr($_POST['secondinput'], 0, 2);
		$y2 = substr($_POST['secondinput'], 6, 4);

		$start_date = $y1 . '-' . $m1 . '-' . $d1;
		$end_date = $y2 . '-' . $m2 . '-' . $d2;

		$player = array_unique($_POST['player']);
		$i = 1;
		foreach ($player as $playerid) {
			if ($i == 1) {
				$kritstr = implode(', ', $krit);
				$binstr = implode(', ', $bind);
				$ausstr = implode(', ', $aus);
				$sql = 'INSERT INTO buchung (start_date, end_date, play_times, name,'
						. 'agentur, kunde, angebot, user, useremail, criterien, '
						. 'and_criteria, exclude_criteria, text, motive, abnummer,'
						. ' pps, upload) VALUES ('
						. "'" . $start_date . "', "
						. "'" . $end_date . "', "
						. "'" . $_POST['play_times'] . "', "
						. "'" . $_POST['name'] . "', "
						. "'" . $_POST['agentur'] . "', "
						. "'" . $_POST['kunde'] . "', "
						. "'" . $angebot . "', "
						. "'" . $_SESSION['user'] . "', "
						. "'" . $_SESSION['useremail'] . "', "
						. "'" . $kritstr . "', "
						. "'" . $binstr . "', "
						. "'" . $ausstr . "', "
						. "'" . $_POST['text'] . "', "
						. "'" . $_POST['motive'] . "', "
						. "'" . $_POST['abnummer'] . "', "
						. "'" . $pps . "', "
						. "'2')";
				$erg = mysqli_query($conn, $sql);
			}

			// insert into playerbuchung
			$sql = 'SELECT custom_sn1, custom_sn2 FROM player WHERE id = ' . $playerid;
			$db = mysqli_query($conn, $sql);
			while ($row = mysqli_fetch_array($db)) {
				$custom_sn1 = $row['custom_sn1'];
				$custom_sn2 = $row['custom_sn2'];
			}

			$sql = 'INSERT INTO playerbuchung (players, custom_sn1, custom_sn2,'
					. 'angebot, playermark) VALUES ('
					. "'" . $playerid . "', "
					. "'" . $custom_sn1 . "', "
					. "'" . $custom_sn2 . "', "
					. "'" . $angebot . "',"
					. "'" . $playermark . "')";
			$erg = mysqli_query($conn, $sql);

			$i++;
		}
		$upload = 2;
	}
}

if ($upload == 1) {
	unlink($uploaded_dir . $filename);
	header("Location: http://88.99.184.137/inovisco_direct/details.php?angebot=$angebot");
} elseif ($upload == 2 && $error == '') {
	header("Location: http://88.99.184.137/inovisco_direct/details.php?angebot=$angebot");
} else {
	require_once 'oben2.php'; ?>
<table class="ohnerahmen">
    <tr>
        <td class="blau">
            Prozessschritt: Upload Buchungsexcel​
        </td>
    </tr>
</table>
<?php
	if ($_GET['datei'] == '' && $_GET['manuell'] == '' && $_POST['manuell'] == '') {
		?>
<form action="buchung.php" method="post">
    <table class="ohnerahmen">
        <tr>
            <td class="zelle">Bitte wählen Sie Ihre Eingabe aus.</td>
        </tr>
        <tr>
            <td width="50%" class="zelle">
                <center>
                    <a href="buchung.php?datei=1">
                        Exceldatei hochladen
                    </a>
                </center>
            </td>
            <td class="zelle">
                <center>
                    <a href="buchung.php?manuell=1">
                        Manuelle Eingabe
                    </a>
                </center>
            </td>
        </tr>
    </table>
</form>
<?php
	}
	if ($_GET['datei'] == 1) {
		?>
<form action="buchung.php" method="post" enctype="multipart/form-data">
    <table class="ohnerahmen">
        <?php
	if ($wrongtyp) {
		?>
        <tr>
            <td class="fehler">
                Bitte w&auml;hlen Sie eine korrekte Datei aus!
            </td>
        </tr>
        <?php
	} ?>
        <tr>
            <td class="zelle">
                Buchung mit <input type="radio" name="was" value="1">
                SDAW oder <input type="radio" name="was" value="2">
                QID
            </td>
        </tr>
        <tr>
            <td class="zelle">
                Bitte w&auml;hlen Sie eine Exceldatei von Ihrem
                Rechner aus.
            </td>
        </tr>
        <tr>
            <td class="zelle">
                <input type="file" name="datei">
            </td>
        </tr>
        <tr>
            <td class="button">
                <button type="submit" name="neu" class="gruen" value="1">
                    hochladen</button>
            </td>
        </tr>
    </table>
</form>
<?php
	}
	if ($_GET['manuell'] == 1 || $_POST['manuell'] == 1) {
		?>
<form action="buchung.php" name="buchung" method="post">
    <input type="hidden" name="manuell" value="1">
    <table class="ohnerahmen">
        <?php
						if ($error != '') {
							?>
        <tr>
            <td colspan="2" align="center">
                <font color="red"><?php echo $error; ?>
                </font>
            </td>
        </tr>
        <?php
						} ?>
        <tr>
            <td width="280" class="zelle">Buchung durch:</td>
            <td class="zelle"><?php echo $_SESSION['company']; ?> /
                <?php echo $_SESSION['user']; ?>
            </td>
        </tr>
        <tr>
            <td class="zelle">Angebotsnummer:</td>
            <td class="zelle"><?php echo $angebot; ?>
            </td>
        </tr>
        <tr>
            <td class="zelle">Kundenname:</td>
            <td class="zelle">
                <input type="text" name="kunde"
                    value="<?php echo $_POST['kunde']; ?>"
                    size="40" required>
            </td>
        </tr>
        <tr>
            <td class="zelle">Agenturname:</td>
            <td class="zelle">
                <input type="text" name="agentur"
                    value="<?php echo $_POST['agentur']; ?>"
                    size="40" required>
            </td>
        </tr>
        <tr>
            <td class="zelle">Kampagnenname:</td>
            <td class="zelle">
                <input type="text" name="name"
                    value="<?php echo $_POST['name']; ?>"
                    size="40" required>
            </td>
        </tr>
        <tr>
            <td class="zelle">AB-Nummer:</td>
            <td colspan="2" class="zelle">
                <input type="text" name="abnummer"
                    value="<?php echo $_POST['abnummer']; ?>"
                    size="40" required>
            </td>
        </tr>
        <tr>
            <td class="zelle">Zeitraum:</td>
            <td class="zelle">
                <script>
                    $(function() {
                        $("#datepicker").datepicker();
                    });
                    $(function() {
                        $("#datepick").datepicker();
                    });
                </script>
                <input type="text" id="datepicker" name="firstinput"
                    value="<?php echo $_POST['firstinput']; ?>"
                    size="10" required>
                - <input type="text" id="datepick" name="secondinput"
                    value="<?php echo $_POST['secondinput']; ?>"
                    size="10" required>
                (MM/DD/YYYY)
            </td>
        </tr>
        <tr>
            <td class="zelle">Einblendungen pro Stunde:</td>
            <td class="zelle">
                <input type="text" name="play_times"
                    value="<?php echo $_POST['play_times']; ?>"
                    size="10" required>
            </td>
        </tr>
        <tr>
            <td class="zelle">Anzahl Motive:</td>
            <td class="zelle">
                <?php
		if (empty($motive)) {
			$motive = 1;
		} ?>
                <input type="text" name="motive"
                    value="<?php echo $_POST['motive']; ?>"
                    size="10" required>
            </td>
        </tr>
        <tr>
            <td valign="top" class="zelle">
                Kriterien:
            </td>
            <td class="zelle">
                <input type="text" id="search_data" placeholder="" autocomplete="off" name="sammelkriterium"
                    style="width: 448px; border: 1px solid #FFFFFF;" />
            </td>
        </tr>
        <tr>
            <td valign="top" class="zelle">
                Bind mit Kriterien:
            </td>
            <td class="zelle">
                <input type="text" id="search_bind" placeholder="" autocomplete="off" name="bindkriterium"
                    style="width: 448px; border: 1px solid #FFFFFF;" />
            </td>
        </tr>
        <tr>
            <td valign="top" class="zelle">
                auszuschlie&szlig;ende Kriterien:
            </td>
            <td class="zelle">
                <input type="text" id="search_aus" placeholder="" autocomplete="off" name="auskriterium"
                    style="width: 448px; border: 1px solid #FFFFFF;" />
            </td>
        </tr>
        <tr>
            <td valign="top" class="zelle">
                Displays mit pps-Wert gr&ouml;&szlig;er:
            </td>
            <td class="zelle">
                <select name="pps1">
                    <option value=""></option>
                    <option value="10000">10.000</option>
                    <option value="20000">20.000</option>
                    <option value="30000">30.000</option>
                    <option value="40000">40.000</option>
                </select>
                <input type="text" name="pps2" size="31">
            </td>
        </tr>
        <tr>
            <td valign="top" class="zelle">
                Displays:
            </td>
            <td class="zelle">
                <input type="text" id="search_player" placeholder="" autocomplete="off" name="sammelplayer"
                    style="width: 448px; border: 1px solid #FFFFFF;" />



            </td>
        </tr>
        <tr>
            <td valign="top" class="zelle">
                Infos:
            </td>
            <td class="zelle">
                <textarea name="text" rows="4"
                    cols="43"><?php echo $_POST['text']; ?></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="rechts">
                <button type="submit" name="speichern" class="gruen" value="1">Speichern
                </button>
            </td>
        </tr>
    </table>
</form>
<?php
	}
}
?>
<script type="text/javascript">
    $('#search_data').tokenfield({
        autocomplete: {
            source: <?php echo json_encode($kritarr); ?> ,
            delay: 100
        },
        showAutocompleteOnFocus: true
    })
</script>

<script type="text/javascript">
    $('#search_player').tokenfield({
        autocomplete: {
            source: <?php echo json_encode($play); ?> ,
            delay: 100
        },
        showAutocompleteOnFocus: true
    })
</script>

<script type="text/javascript">
    $('#search_bind').tokenfield({
        autocomplete: {
            source: <?php echo json_encode($kritarr); ?> ,
            delay: 100
        },
        showAutocompleteOnFocus: true
    })
</script>

<script type="text/javascript">
    $('#search_aus').tokenfield({
        autocomplete: {
            source: <?php echo json_encode($kritarr); ?> ,
            delay: 100
        },
        showAutocompleteOnFocus: true
    })
</script>
</center>
</body>

</html>