<?php

include 'conn.php';

$allowedUrls = array(
   $BaseURL . "ExtendDuration",
);

$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (!in_array($referrer, $allowedUrls)) {
    header("Location: $allowedUrls[0]");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedAction = $_POST['action'];
    $addDays = $_POST['addDays'];
    $selectedGame = $_POST['game'];

    $whereCondition = "";
    if ($selectedGame === "all") {
        $whereCondition = "";
    } else {
        $whereCondition = "game = '$selectedGame'";
    }

    if ($selectedAction === "extend_keygen") {
        $sql = "UPDATE keys_code SET expired_date = DATE_ADD(expired_date, INTERVAL $addDays DAY) WHERE registrator = 'Keygen' AND $whereCondition";
    } elseif ($selectedAction === "extend_not_keygen") {
        $sql = "UPDATE keys_code SET expired_date = DATE_ADD(expired_date, INTERVAL $addDays DAY) WHERE registrator != 'Keygen' AND $whereCondition";
        } elseif ($selectedAction === "extend_all_keys") {
        $sql = "UPDATE keys_code SET expired_date = DATE_ADD(expired_date, INTERVAL $addDays DAY) $whereCondition";
    }
    

    $result = mysqli_query($conn, $sql);
    
    if ($result) {


    echo "<script>location='ExtendDuration';</script>";
} else {
    echo "Update failed: " . mysqli_error($conn);
}
}
?>