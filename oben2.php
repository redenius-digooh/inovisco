<?php
/*
 * Upper area with the login data.
 */
?>
<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="css/jquery-ui.min.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-tokenfield.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/bootstrap-tokenfield.js"></script>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <title></title>
    </head>
    <body>
        <center>
            <table class="tableoben">
                <tr class="troben">
                    <th width="150px" class="oben">
                        <h2 class="obenlinks">Inovisco Direct</h2>
                    </th>
                    <th width="200px" class="oben">
                        <form action="buchung.php" method="post">
                            <button type="submit" name="neu" 
                                class="button_auswahl" value="1">
                            Neuer<br>Buchungsprozess</button>
                        </form>
                    </th>
                    <th width="200px" class="oben">
                        <form action="uebersicht.php" method="post">
                            <button type="submit" name="neu" 
                                class="button_auswahl" value="1">
                            Laufende Buchungsprozesse</button>
                        </form>
                    </th>
                    <th width="200px" class="oben">
                        <form action="download.php" method="post">
                            <button type="submit" name="neu" 
                                class="button_auswahl" value="1">
                            Download Verf&uuml;gbarkeit</button>
                        </form>
                    </th>
                    <th width="200px" class="obenrechts">
                        <h4>Angemeldet als:<br><?php echo $_SESSION['user']; ?>
                            <br>
                            <img src="abbrechenkl.png" alt="abbrechen"> 
                            <a href="abmelden.php" class="weiss">Abmelden</a>
                        </h4>
                    </th>
                </tr>
                <tr>
                    <td colspan="5">