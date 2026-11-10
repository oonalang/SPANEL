<?php
namespace App\Controllers;

use App\Models\CodeModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;

include('conn.php');
$CompName = "Justine Hacks";

$this->userid = session()->userid;
$this->model = new UserModel();
$this->user = $this->model->getUser($this->userid);
$user = $this->user;
$usern = $user->username;
$sql = "SELECT `email` from `users` where username='".$user->username."'";
$result = mysqli_query($conn, $sql);
$usermail = mysqli_fetch_assoc($result);

date_default_timezone_set('Asia/Manila');
$timestamp = date('d/m/Y h:i:sa');
$accesstime = date('h:i:sa');
$webpage = $_SERVER['REQUEST_URI'];
$browser = $_SERVER['HTTP_USER_AGENT'];
$url = $_SERVER['SERVER_NAME'];
$server = $_SERVER['HTTP_HOST'];

function getUserIP()
{
    $clientIp  = @$_SERVER['HTTP_CLIENT_IP'];
    $forwardIp = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remoteIp  = $_SERVER['REMOTE_ADDR'];
    if(filter_var($clientIp, FILTER_VALIDATE_IP))
    {
        $ipaddress = $clientIp;
    }
    elseif(filter_var($forwardIp, FILTER_VALIDATE_IP))
    {
        $ipaddress = $forwardIp;
    }
    else
    {
        $ipaddress = $remoteIp;
    }
    return $ipaddress;
}
$user_ip = getUserIP();

    if (isset($username)||($email)) {  
        $email = \Config\Services::email();
        $email->setFrom('support@darkespyt.in', 'Justine Hacks');
        $email->setTo($usermail);
        
        $email->setSubject("[$server]✔ Logged in as $usern at $timestamp");
        $email->setMessage("<p>Dear [ $usern ],</p>
        
        <p>Congratulations! You have successfully logged in to your account. We hope you enjoy using our PANEL services. If you have any questions or need assistance, please don't hesitate to contact us.</p>
        
        <p>Thank you for being a valued customer.<br><br>Sincerely,<br>[ TEAM DARK - @$CompName ]</p>
        <p>Your IP</p>
    <p>$user_ip</p>
    <p>Time</p>
    <p>$timestamp</p>
      <p>Accessed Page</p>
      <p>$webpage</p>
      <p>Stranger's Browser</p>
      <p>$browser</p>
    
    <p>Thanks for choosing @Justine_Hacks and we Definitely Try to give Best Things for you!</p>
    
  <p>Copyright © 2023 Justine Hacks</p>");
$email->send();
}
?>