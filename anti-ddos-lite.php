<?php

/**
 * Cleantalk base class
 *
 * @version 2.0.2
 * @package Cleantalk
 * @author Cleantalk team (welcome@cleantalk.org)
 * @copyright (C) CleanTalk team (https://cleantalk.org)
 * @license MIT License 2022
 *
 */

require 'src/anti-ddos-lib.php';

if (!checkRequirements()) {
    return;
}

if (!isset($_SERVER['REMOTE_ADDR'])) {
    return;
}

$exceptions = [
    '/connect',
];

$requestPath  = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
$is_exception = false;

foreach ($exceptions as $exception) {
    if ($exception !== '' && strpos($requestPath, $exception) !== false) {
        $is_exception = true;
        break;
    }
}

// Skip the check when the request URL is on the exception list.
if (!$is_exception) {
    $data = [
        'anti_ddos_protection_enable' => true, // Turn the protection on or off.
        'anti_ddos_debug' => true,             // Print debug output.
        'skip_not_rated_ua' => false,          // Only let visitors with a trusted user agent through.
        'secure_cookie_days' => 280,           // Days the secure cookie stays valid.
        'redirect_delay' => 100,                 // Seconds to wait before redirecting back to the original URL.
        'remote_ip' => $_SERVER['REMOTE_ADDR'],
        'secure_label' => 'ct_anti_ddos_key',
        'test_headless' => true,               // Block headless browsers such as Selenium.
        'server_url' => isset($_SERVER['HTTPS']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'],

        // Salt for the cookie so it can't be copied between visitors.
        // Generate your own $anti_ddos_salt before using this on another site.
        'anti_ddos_salt' => '4xU9mn2X7iPZpeW2'
    ];

    if ($data['anti_ddos_protection_enable'] || antiDdosCheckDatFileExist()) {
        antiDdosProtectionMain($data);
    }
}