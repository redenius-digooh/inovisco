<?php
/*
 * Download the availability.
 */
require_once 'db.php';
require_once 'oben.php';
?>
                            <table class="ohnerahmen">
                                <tr>
                                    <td class="blau">
                                        Download Verf&uuml;gbarkeit
                                    </td>
                                </tr>
                            </table>
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
                </table>
        </center>
    </body>
</html>