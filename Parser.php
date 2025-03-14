<?php
declare(strict_types=1);

namespace AdvancedLG;

class Hop {
    public int $index;
    public string $ip = '';
    public string $hostname = '';
    /** @var float[] */
    public array $timings = [];
    public int $sent = 0;

    /**
     * Adds timestamp and increments the submission count.
     */
    public function addTiming(float $time): void {
        $this->timings[] = $time;
        $this->sent++;
    }

    /**
     * Calculates statistics: mean, best, worst, standard deviation.
     */
    public function computeStats(): array {
        $count = count($this->timings);
        $avg = $count ? array_sum($this->timings) / $count : 0;
        $best = $count ? min($this->timings) : 0;
        $worst = $count ? max($this->timings) : 0;
        $stdDev = 0;
        if ($count > 1) {
            $mean = $avg;
            $sumSq = 0;
            foreach ($this->timings as $t) {
                $sumSq += pow($t - $mean, 2);
            }
            $stdDev = sqrt($sumSq / ($count - 1));
        }
        return [
            'avg'    => round($avg, 2),
            'best'   => round($best, 2),
            'worst'  => round($worst, 2),
            'stdDev' => round($stdDev, 2),
            'count'  => $count,
        ];
    }
}

class Parser {
    /** @var array<int, Hop> */
    private array $hops = [];
    /** @var array<string, string> DNS cache */
    private array $dnsCache = [];

    /**
    * Processes each line of the MTR output and updates the corresponding hop.
    * Expected format:
     *   - 'h <hop_index> <ip>' : Hop IP information
     *   - 'p <hop_index> <time_in_ms>' : Ping time (ms)
     */
    public function update(string $line): void {
        $line = trim($line);
        if ($line === '') {
            return;
        }
        $parts = preg_split('/\s+/', $line);
        if (!$parts || count($parts) < 3) {
            return;
        }
        $type = $parts[0];
        $index = (int)$parts[1];
        $value = $parts[2];

        if (!isset($this->hops[$index])) {
            $this->hops[$index] = new Hop();
            $this->hops[$index]->index = $index;
        }

        $hop = $this->hops[$index];
        if ($type === 'h') {
            $hop->ip = $value;
            // Asynchronous reverse DNS query (pre-cache check)
            if (!empty($hop->ip)) {
                if (isset($this->dnsCache[$hop->ip])) {
                    $hop->hostname = $this->dnsCache[$hop->ip];
                } else {
                    // Synchronous fallback; AsyncDNSResolver will be used for API integration in the next update.
                    $hostname = gethostbyaddr($hop->ip);
                    $hop->hostname = ($hostname !== $hop->ip) ? $hostname : '';
                    $this->dnsCache[$hop->ip] = $hop->hostname;
                }
            }
        } elseif ($type === 'p') {
            $time = floatval($value);
            $hop->addTiming($time);
        }
    }

    /**
     * Returns the processed hop data in HTML table format.
     */
    public function toHtmlTable(): string {
        $html = '<table class="min-w-full border-collapse border border-gray-300">';
        $html .= '<thead><tr class="bg-gray-200">';
        $html .= '<th class="border border-gray-300 px-2 py-1">Hop</th>';
        $html .= '<th class="border border-gray-300 px-2 py-1">IP</th>';
        $html .= '<th class="border border-gray-300 px-2 py-1">Hostname</th>';
        $html .= '<th class="border border-gray-300 px-2 py-1">Packets</th>';
        $html .= '<th class="border border-gray-300 px-2 py-1">Avg (ms)</th>';
        $html .= '<th class="border border-gray-300 px-2 py-1">Best (ms)</th>';
        $html .= '<th class="border border-gray-300 px-2 py-1">Worst (ms)</th>';
        $html .= '<th class="border border-gray-300 px-2 py-1">Std Dev (ms)</th>';
        $html .= '</tr></thead><tbody>';

        ksort($this->hops);
        foreach ($this->hops as $hop) {
            $stats = $hop->computeStats();
            $html .= '<tr>';
            $html .= '<td class="border border-gray-300 px-2 py-1 text-center">'.$hop->index.'</td>';
            $html .= '<td class="border border-gray-300 px-2 py-1 text-center">'.$hop->ip.'</td>';
            $html .= '<td class="border border-gray-300 px-2 py-1 text-center">'.($hop->hostname ?: '—').'</td>';
            $html .= '<td class="border border-gray-300 px-2 py-1 text-center">'.$stats['count'].'</td>';
            $html .= '<td class="border border-gray-300 px-2 py-1 text-center">'.$stats['avg'].'</td>';
            $html .= '<td class="border border-gray-300 px-2 py-1 text-center">'.$stats['best'].'</td>';
            $html .= '<td class="border border-gray-300 px-2 py-1 text-center">'.$stats['worst'].'</td>';
            $html .= '<td class="border border-gray-300 px-2 py-1 text-center">'.$stats['stdDev'].'</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * __toString() method: Output the table in plain text format.
     */
    public function __toString(): string {
        $output = sprintf("%-4s %-15s %-30s %-7s %-8s %-8s %-8s %-8s\n", 'Hop', 'IP', 'Hostname', 'Pkts', 'Avg', 'Best', 'Worst', 'StdDev');
        ksort($this->hops);
        foreach ($this->hops as $hop) {
            $stats = $hop->computeStats();
            $output .= sprintf(
                "%-4d %-15s %-30s %-7d %-8.2f %-8.2f %-8.2f %-8.2f\n",
                $hop->index,
                $hop->ip,
                $hop->hostname ?: '—',
                $stats['count'],
                $stats['avg'],
                $stats['best'],
                $stats['worst'],
                $stats['stdDev']
            );
        }
        return $output;
    }
}
