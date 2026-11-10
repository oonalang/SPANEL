<?php
$ip = $_SERVER['REMOTE_ADDR'];
$requests = array();
$block_time = 10;

function checkIP($ip) {
    global $block_time, $requests;

    foreach ($requests as $key => $value) {
        if ($value < time()) {
            unset($requests[$key]);
        }
    }

    if (count($requests) >= 20) {
        http_response_code(403);
        exit("THERE IS DDOS SECURITY ");
        
    }

    $requests[$ip] = time() + $block_time;
}

checkIP($ip);
?>