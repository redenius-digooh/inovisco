<?php
if (isset($_FILES['datei'])) {
    if ($_FILES['datei']['error'] == UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['datei']['tmp_name'], "uploadfiles/" . $_FILES['datei']['name']);
        $upload = 1;
    }
    else {
        $upload = 2;
    }
}
?>
<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
	<script language="JavaScript" type="text/javascript" src="campaign.js" charset="UTF-8" ></script>	
        <title></title>
    </head>
    <body>
        <form action="?login=1" method="post">
            <table>                
<?php
require_once 'oben.php';
if ($upload == 1) {
?>
                        <p>Das Hochladen war erfolgreich.</p>
<?php
}
elseif ($upload == 2) {
?>
                        <p>Das Hochladen hat leider nicht geklappt, bitte versuchen Sie es erneut.</p>
<?php
}
else {
?>
                        <p>Bitte w&auml;hlen Sie eine Exceldatei von Ihrem Rechner aus.</p>
                        <input type="file" name="datei">
                        <button type="submit" name="neu" value="1">hochladen</button>
<?php
}
?>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>