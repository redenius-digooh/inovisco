<?php
session_unset();
session_destroy();
session_write_close();
setcookie(session_name(),'',0,'/');
session_regenerate_id(true);
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
                <tr>
                    <th>
                        <h2 class="obenlinks">Inovisco Direct</h2>
                    </th>  
                    <th class="obenrechts">
                        <h2>&nbsp;<br>&nbsp;<br></h2>
                    </th>
                </tr>
                <tr>
                    <td colspan="2">
                        <table class="ohnerahmen">
                            <tr>
                                <td class="button" colspan="2">

                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Sie wurden erfolgreich abgemeldet.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>