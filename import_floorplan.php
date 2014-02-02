<?php

require_once('floorplan.php');

$mysqli = new mysqli('localhost', 'seatbooking', 'FGP6qGsh7jfqdTLL', 'seatbooking');

if ($mysqli->connect_errno) {
    echo('db connect failed');
    exit;
}

$mysqli->query('truncate table floorplan;');

$y_max = count($floor);
$row = 1;
$seat = 1;
$newrow = false;
for ($y = 0; $y < $y_max; $y++) {
    $x_max = count($floor[$y]);
    for ($x = 0; $x < $x_max; $x++) {
        $type = $floor[$y][$x];
        if ($type == 6 || $type == 7) {
            $query = 'insert into `floorplan` (x, y, type, row, seat) values ('.$x.','.$y.','.$type.','.$row.','.$seat.');';
            $seat++;
            $newrow = true;
        } else {
            $query = 'insert into `floorplan` (x, y, type) values ('.$x.','.$y.','.$type.');';
        }
        $mysqli->query($query);
        if ($mysqli->error) { echo($mysqli->error); exit; }

        echo($type);
    }
    
    $seat = 1;
    if ($newrow) {
        $newrow = false;
        $row++;
    }
    echo("\n");
}

$mysqli->close();


echo('Done');
?>
