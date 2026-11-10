<?php
include 'conn.php';

$allowedUrls = array(
   $BaseURL . "lib",
);

$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (!in_array($referrer, $allowedUrls)) {
    header("Location: " . $allowedUrls[0]);
    exit;
}

$targetDir = "Farlight/";
$allowedExtension = "so";

date_default_timezone_set('Asia/Manila');
$current_time = date('Y-m-d H:i:s');

$PanelUrl = "https://freeshai.x10.mx/";

$maxFileSize = 10 * 1024 * 1024;

if (isset($_POST["submit"])) {
    $filename = basename($_FILES["fileToUpload"]["name"]);
    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $fileSize = $_FILES["fileToUpload"]["size"];
    $tmpName = $_FILES["fileToUpload"]["tmp_name"];
    $uploadError = $_FILES["fileToUpload"]["error"];

    if ($uploadError !== UPLOAD_ERR_OK) {
        echo "Error uploading file. Error code: $uploadError<br>";

        echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . '<br>';
        echo 'post_max_size: ' . ini_get('post_max_size') . '<br>';
        exit;
    }

    $stmt_check = $conn->prepare("SELECT id FROM lib WHERE file = ?");
    if (!$stmt_check) {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }

    $stmt_check->bind_param("s", $filename);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "A file with the same name already exists in the database.";
    } else {
        if ($fileExt === $allowedExtension) {
            if ($fileSize <= $maxFileSize) {
                $destination = $targetDir . $filename;
                $FullLibPath = $PanelUrl . $destination;

                if (move_uploaded_file($tmpName, $destination)) {
                    $fileSizeFormatted = formatSizeUnits($fileSize);

                    $stmt = $conn->prepare("INSERT INTO lib (file, file_type, file_size, time) VALUES (?, ?, ?, ?)");
                    if (!$stmt) {
                        echo "Error preparing statement: " . $conn->error;
                        exit;
                    }

                    $stmt->bind_param("ssss", $filename, $destination, $fileSizeFormatted, $current_time);
                    $stmt->execute();
                    $stmt->close();

                    echo "File uploaded successfully. Your Lib Url: $FullLibPath";
                } else {
                    echo "Error uploading file.";
                }
            } else {
                echo "File size exceeds the maximum limit of 10MB.";
            }
        } else {
            echo "Only .$allowedExtension files are allowed.";
        }
    }
    $stmt_check->close();
}

function formatSizeUnits($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $index = 0;
    while ($bytes >= 1024 && $index < 4) {
        $bytes /= 1024;
        $index++;
    }
    return round($bytes, 2) . ' ' . $units[$index];
}
?>
