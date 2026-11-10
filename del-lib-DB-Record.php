<?php
include('conn.php');

$allowedUrls = array(
   $BaseURL . "lib",
);

$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (!in_array($referrer, $allowedUrls)) {
    header("Location: $allowedUrls[0]");
    exit;
}

if (isset($_POST['id'])) {
    $fileId = $_POST['id'];

    $fetchFileQuery = "SELECT `file`, `file_type` FROM `lib` WHERE `id` = $fileId";
    $fileResult = mysqli_query($conn, $fetchFileQuery);
    $fileRow = mysqli_fetch_assoc($fileResult);

    if ($fileRow) {
        $filePath = $fileRow['file_type'];
        
        if (unlink($filePath)) {
            $deleteQuery = "DELETE FROM `lib` WHERE `id` = $fileId";
            if (mysqli_query($conn, $deleteQuery)) {
                echo 'File and database record deleted successfully';
            } else {
                echo 'Error deleting file record';
            }
        } else {
            echo 'Error deleting file from server';
        }
    } else {
        echo 'File not found';
    }
} else {
    echo 'Invalid request';
}

mysqli_close($conn);
?>
