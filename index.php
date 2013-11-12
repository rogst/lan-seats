<?php
session_start();

require_once('lib/container.php');

$user = new Container();

$_SESSION['LoggedIn'] = isset($_SESSION['LoggedIn']) ? $_SESSION['LoggedIn'] : false;
$hasBooked = false;

$mysqli = new mysqli('localhost', 'seatbooking', 'FGP6qGsh7jfqdTLL', 'seatbooking');
if ($mysqli->connect_errno) {
    echo('db connect failed');
    exit;
}

if (isset($_GET['get']) and $_GET['get'] == 'seat') {
    $xpos = $_GET['x'];
    $ypos = $_GET['y'];
    $query = 'SELECT t.holder_name FROM floorplan f INNER JOIN tickets t on f.ticket = t.id WHERE f.x = '.$xpos.' and f.y = '.$ypos.';';
    $result = $mysqli->query($query);
    if ($mysqli->error) { echo($mysqli->error); exit; }
    $row = $result->fetch_assoc();
    $result->free();
    echo($row['holder_name']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['seat_number']) && isset($_POST['seat_row'])) {
        $seatnr = $_POST['seat_number'];
        $seatrow = $_POST['seat_row'];
        $query = 'update floorplan set type = 3,ticket='.$_SESSION['TicketInfo']['id'].',reservation_date=\''.date('Y-m-d H:i:s').'\' where x = '.$seatnr.' and y = '.$seatrow.';';
        $mysqli->query($query);
        if ($mysqli->error) { echo($mysqli->error); exit; }
    } elseif (isset($_POST['submitlogin'])) {
        $ticket_code = $mysqli->real_escape_string($_POST['code']);
        $ticket_password = $mysqli->real_escape_string($_POST['password']);
        $query = 'select * from tickets where ticket_code = \''.$ticket_code.'\' and ticket_password = \''.$ticket_password.'\';';
        $result = $mysqli->query($query);
        $row = $result->fetch_assoc();
        $result->free();
        if ($row != null) {
            $_SESSION['TicketInfo'] = $row;
            $_SESSION['LoggedIn'] = true;
        }
    } elseif (isset($_POST['submitlogout'])) {
        $_SESSION['LoggedIn'] = false;
        $_SESSION['TicketInfo'] = null;
    } elseif (isset($_POST['submitunbook'])) {
        $query = 'update floorplan set type = 2,ticket=null,reservation_date=null where ticket = '.$_SESSION['TicketInfo']['id'].';';
        $mysqli->query($query);
        if ($mysqli->error) { echo($mysqli->error); exit; }
        $hasBooked = false;
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

$_SESSION['hasBooked'] = false;
$query = 'select x, y, type, ticket from floorplan order by y,x;';
$result = $mysqli->query($query);
$map = array();
$lastrow = 0;
$currentrow = array();
while ($row = $result->fetch_row()) {
    if ($row[1] != $lastrow) {
        $lastrow = $row[1];
        $map[$row[1]] = $currentrow;
        $currentrow = array();
    }

    $type = $row[2];
    if ($_SESSION['LoggedIn'] == true) {
        if ($_SESSION['TicketInfo']['id'] == $row[3]) {
            $type = 8;
            $hasBooked = true;
        }
    }
    $currentrow[$row[0]] = $type;
}
$map[$row[1]] = $currentrow;

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
                <?php foreach($map as $row): ?>
                    <tr>
                    <?php foreach($row as $col => $value): ?>
                        <?php if ($_SESSION['LoggedIn'] == true && $hasBooked == false && $value == 2): ?>
                        <td class='<?php echo($style[$value][0]); ?>' onclick='select_seat(this);'></td>
                        <?php elseif ($_SESSION['LoggedIn'] == true && $value == 3): ?>
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
                                <?php if ($_SESSION['LoggedIn'] == true): ?>
                                <p>Välkommen <?php echo($_SESSION['TicketInfo']['holder_name']); ?></p>
                                <form id="login_form" method="POST">
                                    <input type="submit" name="submitlogout" value="Logga ut">
                                    <?php if ($hasBooked == true): ?>
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
