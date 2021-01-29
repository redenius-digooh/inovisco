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