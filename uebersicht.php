<?php
/*
 * Overview of the current booking processes.
 */
require_once 'oben.php';

$sql = "SELECT upload, inovisco, digooh FROM buchung WHERE user = '" . 
        $_SESSION['user'] . "'";
$result = $conn->query($sql);
?>
                            <table class="ohnerahmen">
                                <tr>
                                    <td class="balken">
                                        <table class="ohnerahmen">
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
?>
                            <tr>
                                <td width="90px">
                                    <table class="table_klein">
                                        <tr>
                                            <?php if ($row['upload'] == 1) { ?>
                                            <td class="gruen">
                                            <?php } else { ?>
                                            <td class="rot">
                                            <?php } ?>
                                                Upload Buchung</td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="80px">&#10132;&#10132;&#10132;</td>
                                <td width="90px">
                                    <table class="table_klein">
                                        <tr>
                                            <?php if ($row['inovisco'] == 1) 
                                            { ?>
                                            <td class="gruen">
                                            <?php } else { ?>
                                            <td>
                                            <?php } ?>
                                                Pr&uuml;fung Inovisco</td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="80px">&#10132;&#10132;&#10132;</td>
                                <td width="90px">
                                    <table class="table_klein">
                                        <tr>                                                
                                            <?php if ($row['digooh'] == 1) 
                                            { ?>
                                            <td class="gruen">
                                            <?php } else { ?>
                                            <td>
                                            <?php } ?>
                                                Pr&uuml;fung Digooh</td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="80px">&#10132;&#10132;&#10132;</td>
                                <td width="90px">
                                    <table class="table_klein">
                                        <tr>
                                            <?php if ($row['upload'] == 1 &&
                                            $row['inovisco'] == 1 &&
                                            $row['digooh'] == 1) {?>
                                            <td class="gruen">
                                            <?php } else { ?>
                                            <td>
                                            <?php } ?>
                                            Buchung abgeschlossen</td>
                                        </tr>
                                    </table>
                                </td>
                                <td>&#10132;&#10132;&#10132;&#10132;</td>
                            </tr>
<?php
    }
}
?>
                                        </table>
                                    </td>
                                </tr>
                            </table>
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