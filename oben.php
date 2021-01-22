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
	<script language="JavaScript" type="text/javascript" src="campaign.js" 
        charset="UTF-8"></script>	
        <title></title>
    </head>
    <body>
        <center>
                <table>
                    <tr>
                        <th>
                            <h2 class="obenlinks">Inovisco Direct</h2>
                        </th>
                        <th class="obenrechts">
                            <h4>Angemeldet als: <?php echo $_SESSION['user']; ?>
                                <br>
                                <img src="abbrechenkl.png" alt="abbrechen"> 
                                <a href="abmelden.php" class="weiss">Abmelden</a>
                            </h4>
                        </th>
                    </tr>
                    <tr>
                        <td colspan="2">