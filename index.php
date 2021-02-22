<?php
session_start();
 
if(isset($_GET['login'])) {
    require __DIR__ .  '/vendor/autoload.php';

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

        $sql = "DELETE FROM player";
        $db_erg = mysqli_query($conn, $sql);

        foreach ($data->data as $key => $value) {
            $id = $value->id;
            $name = $value->name;
            foreach ($value->info as $k => $wert) {
                if ($k == 'custom_sn2') {
                    $custom_sn2 = $wert;
                }
            }

            if ($custom_sn2 == '') {
                $custom_sn2 = 0;
            }
            $sql = "INSERT INTO player (id, name, custom_sn2) VALUES ('" . $id . "', '" . 
                    $name . "', '" . $custom_sn2 . "')";
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

        $sql = "DELETE FROM criteria";
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
        
    try {
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
        $email = $a->user->email;
        $_SESSION['token_direct'] = $access_token;
        $_SESSION['user'] = $user;
        $_SESSION['useremail'] = $email;
        
        // username
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
                    'include'=> 'criteria',
                    'filter[name]'=> $_SESSION['user'],
                ]
            ]
        );
        $body = $response->getBody();
        $data = json_decode((string) $body);
        foreach ($data->data as $key => $value) {
            $_SESSION['company'] = $value->company->name;
        }

        header("Location: http://88.99.184.137/inovisco_direct/uebersicht.php");
    }
    catch (Exception $e) {
        $nichtangemeldet = 1;
        // echo $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
	    <script language="JavaScript" type="text/javascript" src="campaign.js" charset="UTF-8"></script>	
        <title></title>
    </head>
    <body>
        <main>
            <section class="glass">
                <?php
if ($nichtangemeldet == 1) {
?>
            <div class="loginfehler"><b>Leider sind Sie nicht korrekt eingeloggt. 
                Bitte versuchen Sie es erneut.</b></div>
<?php
}
?>
                <div class="title">
                    <h1 class="title">Inovisco Direct</h1>
                </div>
                <div class="input">
                    <form action="index.php?login=1" method="post" >
                        <input type="text" name="username" class="logininput"  required >

                        <input type="password" name="password" class="logininput" required>

                        <button type="submit" name="action"  value="1" class="loginbutton">
                            Abschicken
                        </button>
                    </form>
                </div>
        </section>
        <main>
    </body>
</html>