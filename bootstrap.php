<?php
declare(strict_types=1);

require_once __DIR__.'/config.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/LookingGlass.php';
require_once __DIR__.'/Parser.php';
require_once __DIR__.'/SpeedTest.php';
require_once __DIR__.'/AsyncDNSResolver.php';

// The session name is determined and started.
session_name('ADVLOOKINGLASS');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Additional security HTTP headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// We create the database connection immediately.
$db = DB::getConnection();
