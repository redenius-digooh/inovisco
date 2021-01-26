<?php
/*
 *  The data from the campaigns table are saved in the kampagne table.
 */
require_once 'db.php';

$sql = "SELECT id, name, start_date, end_date, play_times, campaign, display, "
        . "agentur, user FROM buchung WHERE user = '" . $_SESSION['user'] 
        . "' AND id = " . $_GET['kampagne'];
$db_erg = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array( $db_erg)) {
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $play_times = $row['play_times'];
    $name = $row['name'];
    $display = $row['display'];
    $agentur = $row['agentur'];
    $id = $row['id'];
    
    $buchungen[] = array('agentur' => $agentur, 'name' => $name,
        'display' => $display, 'problem' => $problem, 'start_date' =>
        $start_date, 'end_date' => $end_date);
}

require __DIR__ .  '/vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$response = $client->get(
    'https://cms.digooh.com:8081/api/v1/users',
    [
        'headers' => [
            'Authorization' => 'Bearer ' . $_SESSION['token_direct'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'query' => [
            'sort'=> '-name',
            'filter[name]'=> $_SESSION['user'],
        ]
    ]
);
$body = $response->getBody();
$data = json_decode((string) $body);
foreach ($data->data as $key => $value) {
    $company = $value->company->name;
}
require_once 'oben.php';
?>
            <table class="ohnerahmen">
                <tr>
                    <td class="blau">Kampagne
                    </td>
                </tr>
                <tr>
                        <td style="align: left;">
                <table class="ohnerahmen" style="align: left;">
                    <tr>
                        <td>Buchung durch: <?php echo $company; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php 
                        echo "Agentur: ".$buchungen[0]['agentur']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Endkunde:
                        </td>
                    </tr>
                    <tr>
                        <td><?php 
                        echo "Kampagnenname: " . $buchungen[0]['name']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php 
                        echo "Kampganenzeitraum: " . $buchungen[0]['start_date'];
                        echo " - ";
                        echo $buchungen[0]['end_date'];
                        ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
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
foreach ($buchungen as $key => $inhalt) {
?>
                            <tr>
                                <td><?php echo $inhalt['agentur']; ?></td>
                                <td><?php echo $inhalt['name']; ?></td>
                                <td><?php echo $inhalt['display']; ?></td>
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
