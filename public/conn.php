<?php

/*
 * Shim for when the document root is /public. The shared connection file is in
 * the project root, but the app includes it as `conn.php` and as
 * FCPATH . 'conn.php'. Neither resolves from /public, so this file requires the root one.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'conn.php';
