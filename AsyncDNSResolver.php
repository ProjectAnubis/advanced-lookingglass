<?php
declare(strict_types=1);

namespace AdvancedLG;

use React\EventLoop\Factory as LoopFactory;
use React\Dns\Resolver\Factory as DnsFactory;

class AsyncDNSResolver {
    private $loop;
    private $resolver;
    
    public function __construct() {
        $this->loop = LoopFactory::create();
        $dnsFactory = new DnsFactory();
        // We use Google DNS server; can be changed as needed.
        $this->resolver = $dnsFactory->create('8.8.8.8', $this->loop);
    }
    
    /**
     * Performs asynchronous reverse DNS queries.
     * @param string   $ip
     * @param callable $callback function(string $hostname): void
     */
    public function resolve(string $ip, callable $callback): void {
        $this->resolver->reverse($ip)->then(
            function ($hostname) use ($callback) {
                $callback($hostname);
            },
            function () use ($callback) {
                $callback('');
            }
        );
        $this->loop->run();
    }
}
