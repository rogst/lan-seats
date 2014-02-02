<?php
session_start();

require_once('config.php');
require_once('lib/container.php');
require_once('lib/dal.php');
require_once('lib/helper_functions.php');

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

if (isset($_GET['action']) and $_GET['action'] == 'getseat') {
    $data = $database->Query('getSeatAndRow', array($_GET['x'],$_GET['y']));
    if ($config->displayRowAsLetter) {
        echo(json_encode(array($data[0]['seat'], chr($data[0]['row'] + 64))));
    } else {
        echo(json_encode(array($data[0]['seat'], $data[0]['row'])));
    }
    exit;
}

if (isset($_GET['action']) and $_GET['action'] == 'getholdername') {
    $data = $database->Query('getTicketHolderName', array($_GET['x'],$_GET['y']));
    echo(json_encode($data[0]['holder_name']));
    exit;
}

if (CheckArrayKeys($_GET, array('action','x','y')) and $_GET['action'] == 'bookseat') {
    $database->Query('bookSeat', array($_GET['x'], $_GET['y'], $context->ticket['id'], date('Y-m-d H:i:s')));
    $data = $database->Query('getSeat', array($_GET['x'], $_GET['y']));
    if ($data[0]['ticket'] == $context->ticket['id']) {
        echo(json_encode('success'));
    } else {
        echo(json_encode('failed')); 
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['seat_number']) && isset($_POST['seat_row'])) {
        $data = $database->Query('getSeat', array($_POST['seat_number'], $_POST['seat_row']));
        if ($data[0]['ticket'] == null) {
            $database->Query('bookSeat', array($_POST['seat_number'], $_POST['seat_row'], $context->ticket['id'], date('Y-m-d H:i:s')));
        } else {
            
        }
    } elseif (isset($_POST['submitlogin'])) {
        $data = $database->Query('getTicket', array($_POST['code'], $_POST['password']));
        if ($data != null) {
            $context->ticket = $data[0];
            $context->loggedIn = true;
        }
    } elseif (isset($_POST['submitlogout'])) {
        $context->loggedIn = false;
        $context->ticket = null;
        $context->hasBooked = false;
    } elseif (isset($_POST['submitunbook'])) {
        $database->Query('unbookSeat', array($context->ticket['id']));
        $context->hasBooked = false;
    }
}

$inlineCSS = '';
$style = array();
$data = $database->Query('getFloortypes');
foreach ($data as $row) {
    $style[$row['id']] = array($row['codename'], $row['displayname'], $row['color']);
    $border = '';
    if ($row['border'] == 1) {
        $border = 'border: 1px solid #000000;';
    }
    if ($row['color'] != null ) {
        $inlineCSS .= '.'.$row['codename'].' { background: '.$row['color'].'; '.$border.' }'."\n";
    }
    if ($context->loggedIn == true && $row['hovercolor'] != null) {
        $inlineCSS .= '.'.$row['codename'].':hover { background: '.$row['hovercolor'].'; '.$border.' }'."\n";
    }
}

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

$inlineCSS .= '.seat { border: 1px solid #000000; }'."\n";
?>
<html>
<head>
    <meta charset="utf8">
    <title>Darkzone - platsbokning</title> 
    <link rel="stylesheet" type="text/css" href="theme.css"/>
    <script src="jquery-2.0.3.min.js"></script>
    <script src="index.js"></script>
    <style>
<?php echo($inlineCSS); ?>
    </style>
</head>
<body>
    <table>
        <tr>
            <td width="75%">
                <table class="floorplan">
                <?php foreach($floorplan as $row): ?>
                    <tr>
                    <?php foreach($row as $col => $value): ?>
                        <?php if ($context->loggedIn == true && $context->hasBooked == false && $value == 6): ?>
                        <td class='seat <?php echo($style[$value][0]); ?>' onclick='select_seat(this);'></td>
                        <?php elseif ($context->loggedIn == true && $value == 7): ?>
                        <td class='seat <?php echo($style[$value][0]); ?>' onclick='view_seat(this);'></td>
                        <?php else: ?>
                        <td class='<?php echo($style[$value][0]); ?>'></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </table>
            </td>
            <td>
                <table class="sidebar">
                    <tr>
                        <td height="100%" valign="top">
                            <div id="login_view">
                                <?php if ($context->loggedIn == true): ?>
                                <p><b>Välkommen <?php echo($context->ticket['holder_name']); ?></b></p>
                                <form id="login_form" method="POST">
                                    <input type="submit" name="submitlogout" value="Logga ut">
                                    <?php if ($context->hasBooked == true): ?>
                                        <input type="submit" name="submitunbook" value="Avboka plats">
                                    <?php endif; ?>
                                </form> 
                                <?php else: ?>
                                <p><b>Logga in för att boka din plats</b></p>
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
                                <?php if ($legend[2] != null): ?>
                                <td style='background: <?php echo($legend[2]); ?>;'>&nbsp;</td><td><?php echo($legend[1]); ?></td>
                                <?php endif; ?>
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
