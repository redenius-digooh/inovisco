<?php
/*
 * Upload Excel file and display details.
 */
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
                $id = $value->id;echo "I: " . $id;
                $name = $value->name;

                $sql = "INSERT INTO player (id, name) VALUES ('" . $id . "', '" . 
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

require_once 'oben.php';

if ($upload == 1) {
    unlink($path);
    header("Location: http://88.99.184.137/inovisco_direct/details.php");
} else {
?>
                <table class="ohnerahmen">
                    <tr>
                        <td class="blau">
                            Prozessschritt: Upload Buchungsexcel​
                        </td>
                    </tr>
                </table>
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
?>
        </center>
    </body>
</html>