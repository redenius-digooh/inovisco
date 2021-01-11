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
            <form action="?login=1" method="post">
                <table>
                    <?php
                    require_once 'oben.php';
                    ?>
                            <table border="0">
                                <tr>
                                    <td>
                                        <button type="submit" name="neu" value="1">Neuer Buchungsprozess</button>
                                    </td>
                                    <td>
                                        <button type="submit" name="neu" value="1">Laufende Buchungsprozesse</button>
                                    </td>
                                    <td>
                                        <button type="submit" name="neu" value="1">Download Verf&uuml;gbarkeit</button>
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