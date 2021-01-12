<?php
require_once 'angemeldet.php';
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
<?php
require_once 'oben.php';

?>
                            <table class="ohnerahmen">
                                <tr>
                                    <td>
                                        <table>
                                            <tr>
                                                <?php if ($upload == 1) { ?>
                                                <td class="gruen">
                                                <?php } else { ?>
                                                <td class="rot">
                                                <?php } ?>
                                                    Upload Buchung</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                        <table>
                                            <tr>
                                                <?php if ($pruef_innovisco == 1) 
                                                { ?>
                                                <td class="gruen">
                                                <?php } else { ?>
                                                <td>
                                                <?php } ?>
                                                    Pr&uuml;fung Inovisco</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                        <table>
                                            <tr>                                                
                                                <?php if ($pruef_digooh == 1) 
                                                { ?>
                                                <td class="gruen">
                                                <?php } else { ?>
                                                <td>
                                                <?php } ?>
                                                    Pr&uuml;fung Digooh</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                        <table>
                                            <tr>
                                                <?php if ($upload == 1 &&
                                                        $pruef_innovisco == 1 &&
                                                        $pruef_digooh == 1) {?>
                                                <td class="gruen">
                                                <?php } else { ?>
                                                <td>
                                                <?php } ?>
                                                Buchung abgeschlossen</td>
                                            </tr>
                                        </table>
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