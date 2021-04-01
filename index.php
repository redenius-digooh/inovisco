<?php
/*
 * Home page, this is where the session is formed.
 */
session_start();
require_once 'datenbank.php';
if(isset($_GET['login'])) {
    require __DIR__ .  '/vendor/autoload.php';
    
    // always log in with the same access
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
                    'username' => 'livecms',
                    'password' => 'livecms',
                ],
            ]
        );
        $body = $response->getBody();
        $a = json_decode((string) $body);
        $access_token = $a->access_token;
        $_SESSION['token_direct'] = $access_token;
        
        // check login internally
        $sql = "SELECT id, user, email, company, logins FROM user WHERE user = '"
                . $_POST['username'] . "' AND password = '"
                . $_POST['password'] . "'";
        $db_erg = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_array( $db_erg)) {
            $_SESSION['user'] = $row['user'];
            $_SESSION['useremail'] = $row['email'];
            $_SESSION['company'] = $row['company'];
            $_SESSION['userid'] = $row['id'];
            $id = $row['id'];
            $logins = $row['logins'];
        }
    }
    catch (Exception $e) {
        $nichtangemeldet = 1;
    }
    
    // set last login
    $nextlogins = $logins + 1;
    $sql = "Update user SET lastlogin = '" . date("Y-m-d H:m:s") . "', "
            . "logins = " . $nextlogins
            . " WHERE id = " . $id;
    $db_erg = mysqli_query($conn, $sql);
    
    // get all players
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
                if ($k == 'custom_sn1') {
                    $custom_sn1 = $wert;
                }
                if ($k == 'custom_sn2') {
                    $custom_sn2 = $wert;
                }
                if ($k == 'pps') {
                    $pps = $wert;
                }
                if ($k == 'city') {
                    $city = $wert;
                }
                if ($k == 'state') {
                    $state = $wert;
                }
                if ($k == 'address') {
                    $address = $wert;
                }
            }
            if ($custom_sn1 == '') {
                $custom_sn1 = 0;
            }
            if ($custom_sn2 == '') {
                $custom_sn2 = 0;
            }
            $sql = "INSERT INTO player (id, name, custom_sn1, custom_sn2, pps,"
                    . "address, state) "
                    . "VALUES ('" . $id . "', '" . $name . "', '" 
                    . $custom_sn1 . "', '" . $custom_sn2 . "', '" . $pps 
                     . "', '" . $address  . "', '" . $state . "')";
            $db_erg = mysqli_query($conn, $sql);
        }

        // get all criteria
        $client = new \GuzzleHttp\Client();
        $response = $client->get(
            'https://cms.digooh.com:8081/api/v1/criteria',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
        //            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvY21zLmRpZ29vaC5jb206ODA4MVwvYXBpXC92MVwvYXV0aG9yaXphdGlvbnMiLCJpYXQiOjE2MTQwOTQ3MjcsImV4cCI6MTYxNTMwNDMyNywibmJmIjoxNjE0MDk0NzI3LCJqdGkiOiJSZm5qbEFGRmN5TnNXbnJ1Iiwic3ViIjo2MSwicHJ2IjoiODdlMGFmMWVmOWZkMTU4MTJmZGVjOTcxNTNhMTRlMGIwNDc1NDZhYSJ9.AwPqtztUIf1qykrKGRZBJ0d71yx3uXow_Bu1QRh8jIM',
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
        
        // get all players from AA
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
                    'filter[criteria]'=> '140,',
                    'limit'=> '130'
                ]
            ]
        );
        $body = $response->getBody();
        $data = json_decode((string) $body);
        $sql = "DELETE FROM specialplayer";
        $db_erg = mysqli_query($conn, $sql);

        foreach ($data->data as $key => $value) {
            $id = $value->id;

            $sql = "INSERT INTO specialplayer (id) VALUES ('" . $id . "')";
            $db_erg = mysqli_query($conn, $sql);
        }
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
    
    header("Location: http://88.99.184.137/inovisco_direct/uebersicht.php");
}
?>
<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/style.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
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