<?php

require_once('floorplan.php');

$mysqli = new mysqli('localhost', 'seatbooking', 'FGP6qGsh7jfqdTLL', 'seatbooking');

if ($mysqli->connect_errno) {
    echo('db connect failed');
    exit;
}

$mysqli->query('truncate table floorplan;');

$x = 0;
$y = 0;
foreach ($floor as $row) {
    foreach ($row as $col => $type) {
        $query = 'insert into `floorplan` (x, y, type) values ('.$x.','.$y.','.$type.');';
        $mysqli->query($query);
        if ($mysqli->error) { echo($mysqli->error); exit; }
        $x++;
        echo($type);
    }
    $x = 0;
    $y++;
    echo("\n");
}

$mysqli->close();


echo('Done');
?>
