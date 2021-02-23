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
        <link rel="stylesheet" href="style.css">
        <script type='text/javascript' src='https://code.jquery.com/jquery-3.1.0.min.js'></script>
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