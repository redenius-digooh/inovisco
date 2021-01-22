<?php
/*
 * Selection: New booking process, ongoing booking processes, download 
 * availability.
 */
require_once 'db.php';
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
                                        <form action="uebersicht.php" method="post">
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