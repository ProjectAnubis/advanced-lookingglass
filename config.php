<?php
declare(strict_types=1);

/* Application Information and Visual Settings */
define('LG_TITLE', 'Advanced Looking Glass');
define('LG_LOGO', '<h1 class="text-3xl font-bold text-gray-800">Advanced Looking Glass</h1>');
define('LG_LOGO_DARK', '<h1 class="text-3xl font-bold text-gray-200">Advanced Looking Glass</h1>');
define('LG_LOGO_URL', 'https://github.com/ProjectAnubis/advanced-lookingglass');
define('LG_THEME', 'auto');

/* Tailwind CSS – CDN */
define('LG_CSS_OVERRIDES', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');

/* Block settings */
define('LG_BLOCK_NETWORK', true);
define('LG_BLOCK_LOOKINGGLASS', true);
define('LG_BLOCK_SPEEDTEST', true); // Speed​test active
define('LG_BLOCK_CUSTOM', false);

/* Custom file paths (optional) */
define('LG_CUSTOM_HTML', __DIR__.'/custom.html.php');
define('LG_CUSTOM_PHP', __DIR__.'/custom.post.php');
define('LG_CUSTOM_HEADER_PHP', __DIR__.'/custom.header.php');
define('LG_CUSTOM_FOOTER_PHP', __DIR__.'/custom.footer.php');

/* Location and Network Information */
define('LG_LOCATION', 'Ankara, TR');
define('LG_MAPS_QUERY', 'Ankara, TR');
define('LG_FACILITY', 'ANK Data Center');
define('LG_FACILITY_URL', 'https://kernzen.com');
define('LG_IPV4', '127.0.0.1');
define('LG_IPV6', '::1');

/* Supported methods (in JSON format) – including all advanced tests */
define('LG_METHODS', json_encode([
    'ping',
    'ping6',
    'mtr',
    'mtr6',
    'traceroute',
    'traceroute6',
    'speedtest_incoming',
    'speedtest_outgoing',
    'portscan'
]));

/* Other looking glass locations (in JSON format) */
define('LG_LOCATIONS', json_encode([
    'Location A' => 'https://lg.ank01.kernzen.com',
    'Location B' => 'https://lg.ank02.kernzen.com',
    'Location C' => 'https://lg.ist01.kernzen.com',
]));

/* Speed​​test settings – iperf3 commands (parameter {target} will be changed dynamically) */
define('LG_SPEEDTEST_CMD_INCOMING', 'iperf3 -4 -c {target} -p 5201 -P 4');
define('LG_SPEEDTEST_CMD_OUTGOING', 'iperf3 -4 -c {target} -p 5201 -P 4 -R');

/* Terms of Use – enter URL if required, false otherwise */
define('LG_TERMS', false);

/* Latency control */
define('LG_CHECK_LATENCY', true);

/* API settings */
define('LG_API_KEY', 'YOUR_SECURE_API_KEY'); // For API access

/* Database connection information – please update with your own information */
define('DB_DSN', 'mysql:host=localhost;dbname=lookingglass;charset=utf8mb4');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
