<?php
session_start();

require_once('config.php');
require_once('lib/container.php');
require_once('lib/dal.php');

$context = null;
if (isset($_SESSION['context']) && $_SESSION['context'] != null) {
    $context = unserialize($_SESSION['context']);
} else {
    $context = new Container();
    $context->loggedIn = false;
    $context->hasBooked = false;
}

// Check if context has been loaded
if ($context == null) {
    $_SESSION['context'] = null;
    exit('Failed to load context, try and reload the page.');
}

$database = new DAL($config);

$mysqli = new mysqli('localhost', 'seatbooking', 'FGP6qGsh7jfqdTLL', 'seatbooking');
if ($mysqli->connect_errno) {
    exit('db connect failed');
}

if (isset($_GET['get']) and $_GET['get'] == 'seat') {
    $data = $database->Query('getTicketHolderName', array($_GET['x'],$_GET['y']));
    echo($data[0]['holder_name']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['seat_number']) && isset($_POST['seat_row'])) {
        $database->Query('bookSeat', array($_POST['seat_number'], $_POST['seat_row'], $context->ticket['id'], date('Y-m-d H:i:s')));
    } elseif (isset($_POST['submitlogin'])) {
        $code = $mysqli->real_escape_string($_POST['code']);
        $password = $mysqli->real_escape_string($_POST['password']);
        $data = $database->Query('getTicket', array($code, $password));
        if ($data != null) {
            $context->ticket = $data[0];
            $context->loggedIn = true;
        }
    } elseif (isset($_POST['submitlogout'])) {
        $context->loggedIn = false;
        $context->ticket = null;
    } elseif (isset($_POST['submitunbook'])) {
        $database->Query('unbookSeat', array($context->ticket['id']));
        $context->hasBooked = false;
    }
}

$style = array(
    0 => array('floor', '#FFFFFF'),
    1 => array('wall', '#000000'),
    2 => array('seat_available', '#00FF00'),
    3 => array('seat_taken', '#FF0000'),
    4 => array('stage', '#AA00AA'),
    5 => array('crew_area', '#1FCDB0'),
    6 => array('exit', '#666666'),
    7 => array('entrance', '#6699CD'),
    8 => array('your_seat', '#FFFF00')
);

$data = $database->Query('getFloorplan', array(), 'ENUM');
$floorplan = array();
$lastrow = 0;
$currentrow = array();
foreach ($data as $row) {
    if ($row[1] != $lastrow) {
        $lastrow = $row[1];
        $floorplan[] = $currentrow;
        $currentrow = array();
    }

    $type = $row[2];
    if ($context->loggedIn == true) {
        if ($context->ticket['id'] == $row[3]) {
            $type = 8;
            $context->hasBooked = true;
        }
    }
    $currentrow[] = $type;
}
$floorplan[] = $currentrow;

?>
<html>
<head>
    <meta charset="utf8">
    <title>Darkzone - platsbokning</title> 
    <link rel="stylesheet" type="text/css" href="theme.css"/>
    <script src="jquery-2.0.3.min.js"></script>
    <script src="index.js"></script>
</head>
<body>
    <table>
        <tr>
            <td width="75%">
                <table class="floorplan">
                <?php foreach($floorplan as $row): ?>
                    <tr>
                    <?php foreach($row as $col => $value): ?>
                        <?php if ($context->loggedIn == true && $context->hasBooked == false && $value == 2): ?>
                        <td class='<?php echo($style[$value][0]); ?>' onclick='select_seat(this);'></td>
                        <?php elseif ($context->loggedIn == true && $value == 3): ?>
                        <td class='<?php echo($style[$value][0]); ?>' onclick='view_seat(this);'></td>
                        <?php else: ?>
                        <td class='<?php echo($style[$value][0]); ?>'></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </table>
            </td>
            <td>
                <table>
                    <tr>
                        <td height="100%" valign="top">
                            <div id="login_view">
                                <?php if ($context->loggedIn == true): ?>
                                <p>Välkommen <?php echo($context->ticket['holder_name']); ?></p>
                                <form id="login_form" method="POST">
                                    <input type="submit" name="submitlogout" value="Logga ut">
                                    <?php if ($context->hasBooked == true): ?>
                                        <input type="submit" name="submitunbook" value="Avboka plats">
                                    <?php endif; ?>
                                </form> 
                                <?php else: ?>
                                <p>Logga in för att boka din plats</p>
                                <form id="login_form" method="POST">
                                    <p>Bokningskod:</p><input type="text" name="code"><br>
                                    <p>Lösenord:</p><input type="password" name="password"><br>
                                    <input type="submit" name="submitlogin" value="Login">
                                </form> 
                                <?php endif; ?>
                            </div>
                            <div id="book_view">
                                <span id="selected_seat_info"></span>
                                <form id="selected_seat_form" method="POST">
                                <input id="seat_number" type="hidden" name="seat_number" value="">
                                    <input id="seat_row" type="hidden" name="seat_row" value="">
                                    <input id="book_seat_btn" type="button" value="Boka plats" onclick="book_selected_seat();">
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table>
                            <?php foreach( $style as $legend): ?>
                            <tr>
                                <td style='background: <?php echo($legend[1]); ?>;'>&nbsp;</td><td><?php echo($legend[0]); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<?php
$_SESSION['context'] = serialize($context);
?>
