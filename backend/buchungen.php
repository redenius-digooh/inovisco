<?php
/*
 * It is checked whether there are still enough slots that are needed in the 
 * campaign.
 */
require_once 'db.php';

$sql = "SELECT id, name, start_date, end_date, play_times, campaign, display, "
        . "agentur, inovisco, digooh FROM buchung WHERE datum = '" 
        . date("Y-m-d"). "'";
$db_erg = mysqli_query($conn, $sql);

require_once 'oben.php';
?>
            <center>
            <table class="ohnerahmen">
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td>Agentur</td>
                                <td>Kampagne</td>
                                <td>DisplayID</td>
                                <td>Slot</td>
                                <td>01.01.</td>
                                <td>02.01.</td>
                                <td>03.01.</td>
                                <td>04.01.</td>
                            </tr>                                        
<?php
while ($row = mysqli_fetch_array( $db_erg)) {
?>
                            <tr>
                                <td><?php echo $row['agentur']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['display']; ?></td>
                                <td>1<?php echo $row['slot']; ?></td>
                                <td>X</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
<?php
}
?>
                        </table>
                    </td>
                </tr>
            </table>
            </center>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>