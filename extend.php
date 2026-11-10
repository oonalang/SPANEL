<?php

include 'conn.php';

$allowedUrls = array(
   $BaseURL . "keys",
);

$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (!in_array($referrer, $allowedUrls)) {
    header("Location: $allowedUrls[0]");
    exit;
}

if ($_GET['action'] == 'extendKey') {
    $userKey = $_GET['userkey'];
    $numberOfDays = $_GET['days'];

    function updateKeys($db, $keyId, $expirationDays) {
        $query = "SELECT expired_date, duration FROM keys_code WHERE user_key = '$keyId'";
        $result = mysqli_query($db, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $expiredDate = $row['expired_date'];
            $duration = $row['duration'];

            if ($expiredDate === NULL) {
                $newDuration = $duration + $expirationDays;
                $updateQuery = "UPDATE keys_code SET duration = $newDuration WHERE user_key = '$keyId'";
                mysqli_query($db, $updateQuery);
            } else {
                date_default_timezone_set('Asia/Manila');
                $newDuration = $duration + $expirationDays;
                $newExpiredDate = date('Y-m-d H:i:s', strtotime($expiredDate . " + $expirationDays days"));
                $updateQuery = "UPDATE keys_code SET duration = $newDuration, expired_date = '$newExpiredDate' WHERE user_key = '$keyId'";
                mysqli_query($db, $updateQuery);
            }
        } else {
            echo json_encode(array('success' => false, 'error' => mysqli_error($db)));
        }
    }



    $keyId = mysqli_real_escape_string($conn, $userKey);
    $numberOfDays = mysqli_real_escape_string($conn, $numberOfDays);

    updateKeys($conn, $keyId, $numberOfDays);

    $response = array('success' => true);
    echo json_encode($response);
} else {
    $response = array('success' => false, 'error' => 'Invalid action');
    echo json_encode($response);
}
?>
