<?php
require_once 'angemeldet.php';

if (isset($_FILES['datei']) && $_POST['neu'] == 1) {
    $uploaded_dir = "./uploadfiles/";
    $filename = $_FILES["datei"]["name"];
    $path = $uploaded_dir . $filename;
    move_uploaded_file($_FILES["datei"]["tmp_name"], $path);
    $upload = 1;
}

?>
<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
	<script language="JavaScript" type="text/javascript" src="campaign.js" 
        charset="UTF-8"></script>	
        <title></title>
    </head>
    <body>
        <center>
            <table>
<?php
require_once 'oben.php';
?>
                <form action="buchung.php" method="post" 
                      enctype="multipart/form-data">
                    <table class="ohnerahmen">
                        <tr>
                            <td class="button" colspan="2">
                                <a href="auswahl.php">zur&uuml;ck</a>
                            </td>
                        </tr>
                        <tr>
                            <td>
<?php
if ($upload == 1) {
    require_once 'details.php';
?>
                            </td>
                        </tr>
<?php
} else {
?>
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
<?php
}
?>
                </table>
            </form>
        </center>
    </body>
</html>