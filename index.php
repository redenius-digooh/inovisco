<?php
session_start();
 
if(isset($_GET['login'])) {
    require __DIR__ .  '/vendor/autoload.php';

    $client = new GuzzleHttp\Client();
    $response = $client->post(
        'https://cms.digooh.com:8081/api/v1/authorizations',
        [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'username' => $_POST['username'],
                'password' => $_POST['password'],
            ],
        ]
    );
    $body = $response->getBody();
    $a = json_decode((string) $body);
    $access_token = $a->access_token;
    $user = $a->user->name;
    $_SESSION['token_direct'] = $access_token;
    $_SESSION['user'] = $user;
    
    header("Location: http://88.99.184.137/inovisco_direct/auswahl.php");
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
        <center>
            <form action="index.php?login=1" method="post">
                <table>
                    <tr>
                        <th colspan="2">
                            <h2>Inovisco Direct</h2>
                        </th>
                    </tr>
                    <tr>
                        <td class="min">Username</td>
                        <td><input type="text" name="username" required></td>
                    </tr>
                    <tr>
                        <td class="min">Passwort</td>
                        <td><input type="password" name="password" required></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="button">
                            <button type="submit" name="action" value="1">Abschicken</button>
                        </td>
                    </tr>
                </table>
            </form>
        </center>
    </body>
</html>