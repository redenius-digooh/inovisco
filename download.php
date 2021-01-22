<?php
/*
 * Download the availability.
 */
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