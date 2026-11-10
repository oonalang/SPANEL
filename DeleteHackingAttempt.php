<?php
include('conn.php');

$allowedUrls = array(
   $BaseURL . "ManageHackingAttempt",
);

$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (!in_array($referrer, $allowedUrls)) {
    header("Location: $allowedUrls[0]");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM visitor_logs WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>location='ManageHackingAttempt';</script>";
    } else {
        echo "<script>location='ManageHackingAttempt';</script>";
    }
} else {
    echo "<script>location='https://www.pornhub.com/';</script>";
}
?>
