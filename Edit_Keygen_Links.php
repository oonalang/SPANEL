<?php

include 'conn.php';

$allowedUrls = array(
   $BaseURL . "Links",
);

$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (!in_array($referrer, $allowedUrls)) {
    header("Location: $allowedUrls[0]");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new8BPLink = $_POST["8bp_link"];
    $newCODMLink = $_POST["codm_link"];
    $newFL84Link = $_POST["fl84_link"];
    $newMLBBLink = $_POST["mlbb_link"];
    $newPUBGLink = $_POST["pubg_link"];

    $sql = "UPDATE Keygen_Links SET 
        8BP_Link = '$new8BPLink',
        CODM_Link = '$newCODMLink',
        FL84_Link = '$newFL84Link',
        MLBB_Link = '$newMLBBLink',
        PUBG_Link = '$newPUBGLink'
        WHERE id = 1";

    if ($conn->query($sql) === TRUE) {


        echo "<script>location='Links';</script>";
    } else {
        echo "Update link failed";
    }
}
?>
