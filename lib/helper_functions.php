<?php

function CheckArrayKeys($array, $keys) {
    $keyNotFound = false;
    foreach ($keys as $key) {
        if (!isset($array[$key])) {
            $keyNotFound = true;
        }
    }

    return !$keyNotFound;
}

?>
