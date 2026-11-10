<?php
if (!isset($_SESSION)) {
        session_start();
}
if($_SESSION['last_session_request'] > time() - 2){
        // Redirects visitors who send requests less than 2 seconds apart.
        header("location: 🤣/403.html");
        exit;
}
$_SESSION['last_session_request'] = time();
?>