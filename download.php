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
            <form action="download.php" method="post">
                <table>                
<?php
require_once 'oben.php';
?>
                            <table class="ohnerahmen">
                                <tr>
                                    <td class="button" colspan="2">
                                        <a href="auswahl.php">zur&uuml;ck</a>
                                    </td>
                                </tr>
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
                        </td>
                    </tr>
                </table>
            </form>
        </center>
    </body>
</html>