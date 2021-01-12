<?php
require_once 'angemeldet.php';
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
                <table>
                    <?php
                    require_once 'oben.php';
                    ?>
                            <table class="ohnerahmen">
                                <tr>
                                    <td>
                                        <form action="buchung.php" method="post">
                                            <button type="submit" name="neu" 
                                                class="button_auswahl" value="1">
                                            Neuer Buchungsprozess</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="buchung.php" method="post">
                                            <button type="submit" name="neu" 
                                                class="button_auswahl" value="1">
                                            Laufende Buchungsprozesse</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="download.php" method="post">
                                            <button type="submit" name="neu" 
                                                class="button_auswahl" value="1">
                                            Download Verf&uuml;gbarkeit</button>
                                        </form>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                </table>
            </form>
        </center>
    </body>
</html>