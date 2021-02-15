<?php
/*
 * Upload Excel file and display details.
 */
session_start();
require_once 'db.php';
require __DIR__ .  '/vendor/autoload.php';

$namefehlt = 0;

if (isset($_FILES['datei']) && $_POST['neu'] == 1) {
    if ($_FILES['datei']["type"] == 
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        
        $tag = date("Y-m-d-H-i-s");
        $filename = $_FILES["datei"]["name"] . "_" . $tag;        
        $uploaded_dir = "./uploadfiles/";        
        $path = $uploaded_dir . $filename;
        move_uploaded_file($_FILES["datei"]["tmp_name"], $path);
        $upload = 1;
        require_once 'import.php';

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get(
                'https://cms.digooh.com:8081/api/v1/players',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ]
                ]
            );
            $body = $response->getBody();
            $data = json_decode((string) $body);

            $anzahl = count($data);
            
            mysqli_set_charset($conn,"utf8");
            
            $sql = "DELETE * FROM player";
            $db_erg = mysqli_query($conn, $sql);
            
            foreach ($data->data as $key => $value) {
                $id = $value->id;
                $name = $value->name;

                $sql = "INSERT INTO player (id, name) VALUES ('" . $id . "', '" . 
                        $name . "')";
                $db_erg = mysqli_query($conn, $sql);
            }
            
            $client = new \GuzzleHttp\Client();
            $response = $client->get(
                'https://cms.digooh.com:8081/api/v1/criteria',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ]
                ]
            );
            $body = $response->getBody();
            $data = json_decode((string) $body);

            $anzahl = count($data);
            
            mysqli_set_charset($conn,"utf8");
            
            $sql = "DELETE * FROM criteria";
            $db_erg = mysqli_query($conn, $sql);
            
            foreach ($data->data as $key => $value) {
                $id = $value->id;
                $name = $value->name;

                $sql = "INSERT INTO criteria (id, name) VALUES ('" . $id . "', '" . 
                        $name . "')";
                $db_erg = mysqli_query($conn, $sql);
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    } else {
        $wrongtyp = 1;
    }
}

if ($_GET['manuell'] == 1 || $_POST['manuell'] == 1) {
    // username
    require_once __DIR__ .  '/vendor/autoload.php';

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
    
    $client = new \GuzzleHttp\Client();
    $response = $client->get(
        'https://cms.digooh.com:8081/api/v1/criteria',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'query' => [
                'sort'=> 'name'
            ],
        ]
    );
    $body = $response->getBody();
    $data = json_decode((string) $body);
    $kriterien = array();
    $kritarr = array();
    foreach ($data->data as $key => $value) {
        $kriterien[] = array('id' => $value->id, 'name' => $value->name);
        $kritarr[] = utf8_encode($value->name);
    }
    
    $sql = "SELECT MAX(angebot) AS angebot FROM buchung WHERE user = '" 
            . $_SESSION['user'] . "'";
    $db_erg = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array( $db_erg)) {
        $angebot = $row['angebot'] + 1;
    }
    
    $sql = "SELECT id, name FROM player ORDER BY name";
    $db_erg = mysqli_query($conn, $sql);
    $play = array();
    while ($row = mysqli_fetch_array( $db_erg)) {
        $players[] = array('id' => $row['id'], 'name' => $row['name']);
        $play[] = utf8_encode($row['name']);
    }
}

if ($_POST['speichern'] == 1) {
    $sql = "SELECT MAX(angebot) AS angebot FROM buchung WHERE user = '" 
            . $_SESSION['user'] . "'";
    $db_erg = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array( $db_erg)) {
        $angebot = $row['angebot'] + 1;
    }
    
    if (!$checks || !$checke) {
        $error = "Das Startdatum oder Enddatum war nicht korrekt!";
    }
    if ($_POST['play_times'] < 0 || $_POST['play_times'] > 360) {
        $error = 'Die "Einblendungen pro Stunde" m&uuml;ssen einen Wert zwischen'
                . " 0 und 360 haben!";
    }
    else {
        $kriarr = explode(", ", $_POST['sammelkriterium']);
        $krit = array();
        $_POST['player'] = array();
        if (is_array($kriarr)) {
            if (count($kriarr) > 0 && $kriarr[0] != '') {
                foreach ($kriarr as $kriteri) {
                    $client = new \GuzzleHttp\Client();
                    $response = $client->get(
                        'https://cms.digooh.com:8081/api/v1/criteria',
                        [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                            ],
                            'query' => [
                                'filter[name]'=> $kriteri
                            ],
                        ]
                    );
                    $body = $response->getBody();
                    $data = json_decode((string) $body);
                    foreach ($data->data as $key => $value) {
                        $einzelkriterium = $value->id . ",";
                        $krit[] = $value->id;
                    }

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
                                'include'=> 'criteria',
                                'filter[criteria]'=> $einzelkriterium,
                                'limit'=> '130'
                            ]
                        ]
                    );
                    $body = $response->getBody();
                    $data = json_decode((string) $body);
                    foreach ($data->data as $key => $value) {
                        $_POST['player'][] = $value->id;
                    }
                }
            }
        }
        
        $playerarr = explode(", ", $_POST['sammelplayer']);
        if (is_array($playerarr)) {
            if ($playerarr[0] != '') {
                foreach ($playerarr as $playe) {
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
                                'include'=> 'criteria',
                                'filter[name]'=> $playe,
                                'limit'=> '130'
                            ]
                        ]
                    );
                    $body = $response->getBody();
                    $data = json_decode((string) $body);
                    foreach ($data->data as $key => $value) {
                        if (!in_array($value->id, $_POST['player'])) {
                            $_POST['player'][] = $value->id;
                        }
                    }
                }
            }
        }
        
        $player = array_unique($_POST['player']);
        $i = 1;
        foreach ($player as $playerid) {
            if ($i == 1) {
                $kritstr = implode(", ", $krit);
                $sql = "INSERT INTO buchung (start_date, end_date, play_times, name,"
                        . "agentur, kunde, angebot, user, criterien, text, upload)"
                        . " VALUES ("
                        . "'" . $_POST['start_date'] . "', "
                        . "'" . $_POST['end_date'] . "', "
                        . "'" . $_POST['play_times'] . "', "
                        . "'" . $_POST['name'] . "', "
                        . "'" . $_POST['agentur'] . "', "
                        . "'" . $_POST['kunde'] . "', "
                        . "'" . $angebot . "', "
                        . "'" . $_SESSION['user'] . "', "
                        . "'" . $kritstr . "', "
                        . "'" . $_POST['text'] . "', "
                        . "'2')";echo $sql;
                $erg = mysqli_query($conn, $sql);
            }
            
            $sql = "INSERT INTO playerbuchung (players, angebot)"
                    . " VALUES ("
                    . "'" . $playerid . "', "
                    . "'" . $angebot . "')";
            $erg = mysqli_query($conn, $sql);
            
            $i++;
        }
        $upload = 2;
    }
}

if ($upload == 1) {
    unlink($path);
    header("Location: http://88.99.184.137/inovisco_direct/details.php?angebot=" . $angebot);
}
elseif ($upload == 2) {
    header("Location: http://88.99.184.137/inovisco_direct/details.php?angebot=" . $angebot);
}
else {
    require_once 'oben2.php';
?>
                <table class="ohnerahmen">
                    <tr>
                        <td class="blau">
                            Prozessschritt: Upload Buchungsexcel​
                        </td>
                    </tr>
                </table>
<?php
    if ($_GET['datei'] == '' && $_GET['manuell'] == '') {
?>
                <form action="buchung.php" method="post">
                    <table class="ohnerahmen">
                        <tr>
                            <td>Bitte wählen Sie Ihre Eingabe aus.</td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <center>
                                <a href="buchung.php?datei=1">
                                   Exceldatei hochladen
                                </a>
                                </center>
                            </td>
                            <td>
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
        <form action="buchung.php" method="post" 
                  enctype="multipart/form-data">
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
    }
    ?>
                    <tr>
                        <td>
                        Bitte w&auml;hlen Sie eine Exceldatei von Ihrem 
                            Rechner aus.
                        </td>
                    </tr>
                    <tr>
                        <td>
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
    if ($_GET['manuell'] == 1) {
?>
                <form action="buchung.php" method="post">
                    <table class="ohnerahmen">
                        <tr>
                        <td width="280">Buchung durch:</td>
                        <td><?php echo $company; ?> / 
                            <?php echo $_SESSION['user']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Angebotsnummer:</td>
                        <td><?php echo $angebot; ?></td>
                    </tr>
                    <tr>
                        <td>Kundenname:</td>
                        <td>
        <input type="text" name="kunde" value="<?php echo $kunde; ?>" 
               size="40" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Agenturname:</td>
                        <td>
    <input type="text" name="agentur" value="<?php echo $agentur; ?>" 
           size="40" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Kampagnenname:</td>
                        <td>
        <input type="text" name="name" value="<?php echo $name; ?>" 
               size="40" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Zeitraum:</td>
                        <td>
        <input type="text" name="start_date" value="<?php echo $start_date; ?>" 
        size="10" required>
    - <input type="text" name="end_date" value="<?php echo $end_date; ?>" 
        size="10" required> (z.B. 2021-01-20)
                        </td>
                    </tr>
                    <tr>
                        <td>Einblendungen pro Stunde:</td>
                        <td>
        <input type="text" name="play_times" value="<?php echo $play_times; ?>" 
            size="10" required>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            Kriterien
                        </td>
                        <td>
                            <input type="text" id="search_data" placeholder="" 
                                   autocomplete="off" name="sammelkriterium" 
                            style="width: 310px; border: 1px solid #FFFFFF;"/>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            Displays
                        </td>
                        <td>
                            <input type="text" id="search_player" placeholder="" 
                                   autocomplete="off" name="sammelplayer" 
                            style="width: 310px; border: 1px solid #FFFFFF;"/>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            Infos
                        </td>
                        <td>
                            <textarea name="text" rows="4" cols="42">
                                <?php echo $text; ?>
                            </textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="rechts">
                            <button type="submit" name="speichern" 
                                class="gruen" value="1">Speichern
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
                      source: <?php echo json_encode($kritarr); ?>,
                      delay: 100
                    },
                    showAutocompleteOnFocus: true
                })
		</script>
                
                <script type="text/javascript">
                $('#search_player').tokenfield({
                    autocomplete: {
                      source: <?php echo json_encode($play); ?>,
                      delay: 100
                    },
                    showAutocompleteOnFocus: true
                })
                </script>
        </center>
    </body>
</html>