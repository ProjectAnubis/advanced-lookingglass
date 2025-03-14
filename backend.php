<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';

use AdvancedLG\LookingGlass;
use AdvancedLG\SpeedTest;

// CSRF and POST control
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Unsupported request method.');
}

if (!isset($_POST['csrfToken']) || !isset($_SESSION[LookingGlass::SESSION_CSRF]) || ($_POST['csrfToken'] !== $_SESSION[LookingGlass::SESSION_CSRF])) {
    exit('Missing or invalid CSRF token.');
}

if (!isset($_POST['submitForm'], $_POST['backendMethod'], $_POST['targetHost'])) {
    exit('Incomplete POST data.');
}

$backendMethod = $_POST['backendMethod'];
$targetHost = trim($_POST['targetHost']);

// Method validation
$allowedMethods = json_decode(LG_METHODS, true);
if (!in_array($backendMethod, $allowedMethods, true)) {
    exit('Unsupported backend method.');
}

// Terms control
if (LG_TERMS && !isset($_POST['checkTerms'])) {
    exit('You must agree with the Terms of Use.');
}

// Input validation: IPv4/IPv6 check
if (in_array($backendMethod, ['ping', 'mtr', 'traceroute', 'portscan'])) {
    if (!LookingGlass::isValidIpv4($targetHost) && !LookingGlass::isValidHost($targetHost, LookingGlass::IPV4)) {
        exit('No valid IPv4 provided.');
    }
}
if (in_array($backendMethod, ['ping6', 'mtr6', 'traceroute6'])) {
    if (!LookingGlass::isValidIpv6($targetHost) && !LookingGlass::isValidHost($targetHost, LookingGlass::IPV6)) {
        exit('No valid IPv6 provided.');
    }
}

// Update session data
$_SESSION[LookingGlass::SESSION_TARGET_HOST] = $targetHost;
$_SESSION[LookingGlass::SESSION_TARGET_METHOD] = $backendMethod;
$_SESSION[LookingGlass::SESSION_CALL_BACKEND] = true;

// Run the relevant command according to the selected method
switch ($backendMethod) {
    case LookingGlass::METHOD_PING:
        LookingGlass::ping($targetHost);
        break;
    case LookingGlass::METHOD_PING6:
        LookingGlass::ping6($targetHost);
        break;
    case LookingGlass::METHOD_MTR:
        LookingGlass::mtr($targetHost);
        break;
    case LookingGlass::METHOD_MTR6:
        LookingGlass::mtr6($targetHost);
        break;
    case LookingGlass::METHOD_TRACEROUTE:
        LookingGlass::traceroute($targetHost);
        break;
    case LookingGlass::METHOD_TRACEROUTE6:
        LookingGlass::traceroute6($targetHost);
        break;
    case 'speedtest_incoming':
        SpeedTest::runTest($targetHost, true);
        break;
    case 'speedtest_outgoing':
        SpeedTest::runTest($targetHost, false);
        break;
    case 'portscan':
        LookingGlass::portscan($targetHost);
        break;
    default:
        exit('Invalid method.');
}
