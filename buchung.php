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
        try {        
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(
                    $uploaded_dir . $filename);
            $data = array(1,$spreadsheet->getActiveSheet()->
                    toArray(null,true,true,true)); 

            $eins = $data[1][1]['A'];
            $zwei = $data[1][1]['B'];
            
            if ($eins != 'Agentur' || $zwei != 'Name') {echo"w";
                $namefehlt = 1;
                $upload = 0;
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
        require_once 'import.php';
    } else {
        $wrongtyp = 1;
    }
}

require_once 'oben.php';

if ($upload == 1) {
    require_once 'details.php';
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
    if ($namefehlt == 1) {
    ?>
                    <tr>
                        <td class="fehler">
                        Bitte sorgen Sie f&uuml;r eine korrekte Datei, 
                        hier fehlen wichtige Spalten!
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
                            <button type="submit" name="neu" value="1">
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