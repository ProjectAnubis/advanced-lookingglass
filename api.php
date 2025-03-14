<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';

use AdvancedLG\LookingGlass;
use AdvancedLG\SpeedTest;

header('Content-Type: application/json');

// API erişimi için API anahtarını kontrol ediyoruz.
$api_key = $_GET['api_key'] ?? ($_POST['api_key'] ?? '');
if ($api_key !== LG_API_KEY) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid API key.']);
    exit;
}

// GET veya POST üzerinden method ve target parametrelerini alıyoruz.
$method = $_GET['method'] ?? ($_POST['method'] ?? '');
$target = trim($_GET['target'] ?? ($_POST['target'] ?? ''));

if (empty($method) || empty($target)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

$allowedMethods = json_decode(LG_METHODS, true);
$supportedExtra = ['speedtest_incoming', 'speedtest_outgoing', 'portscan'];
if (!in_array($method, $allowedMethods, true) && !in_array($method, $supportedExtra, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported method.']);
    exit;
}

ob_start();
switch ($method) {
    case LookingGlass::METHOD_PING:
        LookingGlass::ping($target);
        break;
    case LookingGlass::METHOD_PING6:
        LookingGlass::ping6($target);
        break;
    case LookingGlass::METHOD_MTR:
        LookingGlass::mtr($target);
        break;
    case LookingGlass::METHOD_MTR6:
        LookingGlass::mtr6($target);
        break;
    case LookingGlass::METHOD_TRACEROUTE:
        LookingGlass::traceroute($target);
        break;
    case LookingGlass::METHOD_TRACEROUTE6:
        LookingGlass::traceroute6($target);
        break;
    case 'speedtest_incoming':
        SpeedTest::runTest($target, true);
        break;
    case 'speedtest_outgoing':
        SpeedTest::runTest($target, false);
        break;
    case 'portscan':
        LookingGlass::portscan($target);
        break;
}
$output = ob_get_clean();
echo json_encode(['output' => $output]);
