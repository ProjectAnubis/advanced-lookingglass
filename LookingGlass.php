<?php
declare(strict_types=1);

namespace AdvancedLG;

require_once __DIR__.'/db.php';

class LookingGlass
{
    public const IPV4 = 'ipv4';
    public const IPV6 = 'ipv6';

    public const SESSION_TARGET_HOST   = 'target_host';
    public const SESSION_TARGET_METHOD = 'target_method';
    public const SESSION_TOS_CHECKED   = 'tos_checked';
    public const SESSION_CALL_BACKEND  = 'call_backend';
    public const SESSION_ERROR_MESSAGE = 'error_message';
    public const SESSION_CSRF          = 'CSRF';

    public const METHOD_PING         = 'ping';
    public const METHOD_PING6        = 'ping6';
    public const METHOD_MTR          = 'mtr';
    public const METHOD_MTR6         = 'mtr6';
    public const METHOD_TRACEROUTE   = 'traceroute';
    public const METHOD_TRACEROUTE6  = 'traceroute6';

    private const MTR_COUNT = 10;

    /**
     * Verifies the existence of required configuration constants.
     */
    public static function validateConfig(): void
    {
        $required = [
            'LG_TITLE',
            'LG_LOGO',
            'LG_LOGO_DARK',
            'LG_LOGO_URL',
            'LG_CSS_OVERRIDES',
            'LG_BLOCK_NETWORK',
            'LG_BLOCK_LOOKINGGLASS',
            'LG_BLOCK_SPEEDTEST',
            'LG_BLOCK_CUSTOM',
            'LG_CUSTOM_HTML',
            'LG_CUSTOM_PHP',
            'LG_LOCATION',
            'LG_MAPS_QUERY',
            'LG_FACILITY',
            'LG_FACILITY_URL',
            'LG_IPV4',
            'LG_IPV6',
            'LG_METHODS',
            'LG_LOCATIONS',
            'LG_SPEEDTEST_CMD_INCOMING',
            'LG_SPEEDTEST_CMD_OUTGOING',
            'LG_SPEEDTEST_FILES',
            'LG_TERMS',
            'LG_CHECK_LATENCY',
            'LG_THEME'
        ];
        foreach ($required as $constant) {
            if (!defined($constant)) {
                die("{$constant} not found in config.php");
            }
        }
    }

    /**
     * Starts the session.
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('ADVLOOKINGLASS');
            session_start() or die('Could not start session!');
        }
    }

    /**
     * Checks the validity of the IPv4 address.
     */
    public static function isValidIpv4(string $ip): bool
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Checks the validity of the IPv6 address.
     */
    public static function isValidIpv6(string $ip): bool
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Validates the hostname or IP; outputs a valid hostname.
     */
    public static function isValidHost(string $host, string $type): string
    {
        $host = str_replace(['http://', 'https://'], '', $host);
        if (strpos($host, '.') === false) {
            return '';
        }
        if (filter_var('https://'.$host, FILTER_VALIDATE_URL)) {
            if ($host = parse_url('https://'.$host, PHP_URL_HOST)) {
                if ($type === self::IPV4 && isset(dns_get_record($host, DNS_A)[0]['ip'])) {
                    return $host;
                }
                if ($type === self::IPV6 && isset(dns_get_record($host, DNS_AAAA)[0]['ipv6'])) {
                    return $host;
                }
            }
        }
        return '';
    }

    /**
     * Detects the IP address of the client (proxy situations are taken into account).
     */
    public static function detectIpAddress(): string
    {
        if (php_sapi_name() === 'cli') {
            return '127.0.0.1';
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Securely logs the request to the database before the command is run.
     */
    public static function logRequest(string $targetHost, string $method): void
    {
        try {
            $db = \DB::getConnection();
            $stmt = $db->prepare("INSERT INTO lg_logs (client_ip, target_host, method, request_time) VALUES (:client_ip, :target_host, :method, NOW())");
            $stmt->execute([
                ':client_ip'   => self::detectIpAddress(),
                ':target_host' => $targetHost,
                ':method'      => $method
            ]);
        } catch (\PDOException $e) {
            // Even if logging fails, the application continues to run.
        }
    }

    /**
     * Runs the IPv4 ping command.
     */
    public static function ping(string $host, int $count = 4): bool
    {
        self::logRequest($host, self::METHOD_PING);
        return self::procExecute('ping -4 -c'.(int)$count.' -w15', $host);
    }

    /**
     * Runs the IPv6 ping command.
     */
    public static function ping6(string $host, int $count = 4): bool
    {
        self::logRequest($host, self::METHOD_PING6);
        return self::procExecute('ping -6 -c'.(int)$count.' -w15', $host);
    }

    /**
     * Runs MTR command (IPv4) – live statistics stream with advanced parser.
     */
    public static function mtr(string $host): bool
    {
        self::logRequest($host, self::METHOD_MTR);
        return self::procExecute('mtr --raw -n -4 -c '.self::MTR_COUNT, $host);
    }

    /**
     * Runs MTR command (IPv6) – live statistics stream with advanced parser.
     */
    public static function mtr6(string $host): bool
    {
        self::logRequest($host, self::METHOD_MTR6);
        return self::procExecute('mtr --raw -n -6 -c '.self::MTR_COUNT, $host);
    }

    /**
     * Runs the traceroute command (IPv4).
     */
    public static function traceroute(string $host, int $failCount = 4): bool
    {
        self::logRequest($host, self::METHOD_TRACEROUTE);
        return self::procExecute('traceroute -4 -w2', $host, $failCount);
    }

    /**
     * Runs the traceroute command (IPv6).
     */
    public static function traceroute6(string $host, int $failCount = 4): bool
    {
        self::logRequest($host, self::METHOD_TRACEROUTE6);
        return self::procExecute('traceroute -6 -w2', $host, $failCount);
    }

    /**
     * Runs the portscan command.
     */
    public static function portscan(string $host): bool
    {
        self::logRequest($host, 'portscan');
        $cmd = "nmap -p- " . escapeshellarg($host);
        $spec = [
            0 => ['pipe','r'],
            1 => ['pipe','w'],
            2 => ['pipe','w']
        ];
        $process = proc_open($cmd, $spec, $pipes, null);
        if (!is_resource($process)) {
            return false;
        }
        while (($line = fgets($pipes[1], 4096)) !== false) {
            echo str_pad(htmlspecialchars(trim($line)).'<br />', 4096, ' ', STR_PAD_RIGHT);
            @ob_flush();
            flush();
        }
        proc_close($process);
        return true;
    }

    /**
    * Runs the given command, input is made safe with escapeshellarg,
    * If the MTR command is run, it is processed with advanced Parser (Parser.php).
     */
    private static function procExecute(string $cmd, string $host, int $failCount = 2): bool
    {
        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $hostSafe = escapeshellarg(trim($host));
        $process = proc_open("{$cmd} {$hostSafe}", $spec, $pipes, null);
        if (!is_resource($process)) {
            return false;
        }

        // The MTR command uses an advanced parser.
        if (strpos($cmd, 'mtr') !== false) {
            $parser = new \AdvancedLG\Parser();
            while (($line = fgets($pipes[1], 4096)) !== false) {
                $parser->update($line);
                echo str_pad($parser->toHtmlTable(), 4096, ' ', STR_PAD_RIGHT);
                @ob_flush();
                flush();
            }
        } else {
            while (($str = fgets($pipes[1], 4096)) !== false) {
                echo str_pad(htmlspecialchars(trim($str)).'<br />', 4096, ' ', STR_PAD_RIGHT);
                @ob_flush();
                flush();
            }
        }

        while (($err = fgets($pipes[2], 4096)) !== false) {
            if (strpos($err, 'unknown host') !== false || strpos($err, 'Name or service not known') !== false) {
                echo 'Unauthorized request or invalid host';
                break;
            }
        }

        $status = proc_get_status($process);
        if ($status['running']) {
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            proc_close($process);
        }
        return true;
    }

    /**
     * Simple latency check; (will be improved in next update)
     */
    public static function getLatency(): float
    {
        return 0.00;
    }
}
