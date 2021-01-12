<?php
require_once 'angemeldet.php';

if (isset($_FILES['datei'])) {
    if ($_FILES['datei']['error'] == UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['datei']['tmp_name'], "uploadfiles/" . 
            $_FILES['datei']['name']);
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
	<script language="JavaScript" type="text/javascript" src="campaign.js" 
        charset="UTF-8" ></script>	
        <title></title>
    </head>
    <body>
        <center>
            <table>
<?php
require_once 'oben.php';
?>
                <form action="buchung.php" method="post" enctype="multipart/form-data">
                    <table class="ohnerahmen">
                        <tr>
                            <td class="button" colspan="2">
                                <a href="auswahl.php">zur&uuml;ck</a>
                            </td>
                        </tr>
                        <tr>
                            <td>
<?php
require_once 'oben.php';
if ($upload == 1) {
?>
                            <p>Das Hochladen war erfolgreich. Alle Displays und Slots 
                            sind verf&uuml;gbar, die Kampagne kann zur Prüfung 
                            an Digooh gesendet werden!
</p>
<?php
    $client = new \GuzzleHttp\Client();
    $response = $client->get(
        'https://cms.digooh.com:8081/api/v1/companies',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]
    );if($response) echo"ja"; else echo"nee";
    $body = $response->getBody();
    print_r(json_decode((string) $body));
}
elseif ($upload == 2) {
?>
                            <p>Das Hochladen hat leider nicht geklappt, bitte 
                            versuchen Sie es erneut.</p>
<?php
}
else {
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
                            <button type="submit" name="neu" value="1">hochladen
                                </button>
<?php
}
?>
                        </td>
                    </tr>
                </table>
            </form>
        </center>
    </body>
</html>