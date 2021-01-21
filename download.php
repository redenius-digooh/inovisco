<?php
/*
 * Download the availability.
 */
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
                        <form action="download.php" method="post">
                            <table class="ohnerahmen">
                                <tr>
                                    <td>
                                        Verf&uuml;gbarkeit herunterladen.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="button">
                                        <button type="submit" name="download" value="1">Download</button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                        </td>
                    </tr>
                    <tr>
                        <td class="mittig" width: 33,33%>
                            <form action="auswahl.php" method="post">
                                <button type="submit" name="neu2" 
                                    class="lila" value="1">
                                Zur &Uuml;bersicht</button>
                            </form>
                        </td>
                    </tr>
                </table>
        </center>
    </body>
</html>