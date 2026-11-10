<?php
include 'conn.php';

$allowedUrls = array(
   $BaseURL . "admin/create-referral",
);

$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (!in_array($referrer, $allowedUrls)) {
    header("Location: $allowedUrls[0]");
    exit;
}

if (isset($_GET['userkey'])) {
    $userId = $_GET['userkey'];

    $sql = "DELETE FROM referral_code WHERE id_reff = $userId";

if ($conn->query($sql) === TRUE) {
        $response = array('success' => true);
        echo json_encode($response);
    } else {
        $response = array('success' => false);
        echo json_encode($response);
    }
} else {
echo "<script>location='index.php';</script>";
}
?>
