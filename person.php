<?php
/*
 * The person data.
 */
session_start();
require_once 'db.php';
mysqli_query($conn, "SET NAMES 'utf8'");

// update
if ($_POST['speichern'] == 1) {
    $sql = "UPDATE user SET "
            . "user = '" . $_POST['user'] . "',"
            . "email = '" . $_POST['email'] . "',"
            . "password = '" . $_POST['password'] . "',"
            . "telefon = '" . $_POST['telefon'] . "'"
            . " WHERE id = " . $_SESSION['userid'];
    $erg = mysqli_query($conn, $sql);
    $erfolg = 1;
}

// User data
$sql = "SELECT user, email, password, telefon FROM user WHERE "
            . "id = " . $_SESSION['userid'];
$db_erg = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array( $db_erg)) {
    $_POST['user'] = $row['user'];
    $_POST['email'] = $row['email'];
    $_POST['password'] = $row['password'];
    $_POST['telefon'] = $row['telefon'];
}

require_once 'oben2.php';
?>
                <table class="ohnerahmen">
                    <tr>
                        <td class="blau">
                            Userdaten
                        </td>
                    </tr>                    
                    <tr>
                        <td class="zelle">
                            <p><font style="color: green">
                                Die Daten wurden gespeichert.
                            </font></p>
                        </td>
                    </tr>
                </table>
                <form action="person.php" method="post">
                    <table class="ohnerahmen">                        
                    <tr>
                        <td class="zelle">Benutzername:</td>
                        <td class="zelle">
    <input type="text" name="user" value="<?php echo $_POST['user']; ?>" 
               size="40" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Passwort:</td>
                        <td class="zelle">
<input type="password" name="password" value="<?php echo $_POST['password']; ?>" 
               size="40" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">E-Mail:</td>
                        <td class="zelle">
    <input type="text" name="email" value="<?php echo $_POST['email']; ?>" 
               size="40" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="zelle">Telefon:</td>
                        <td class="zelle">
    <input type="text" name="telefon" value="<?php echo $_POST['telefon']; ?>" 
               size="40" required>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="rechts">
                            <button type="submit" name="speichern" 
                                class="gruen" value="1">Speichern
                        </button>
                        </td>
                    </tr>
                    </table>
                </form>
        </center>
    </body>
</html>