<?php
declare(strict_types=1);

namespace AdvancedLG;

class SpeedTest {
    /**
     * Performs speed tests with the iperf3 command.
     * @param string $target Test target
     * @param bool   $isIncoming true: incoming, false: outgoing
     */
    public static function runTest(string $target, bool $isIncoming = true): bool {
         $cmdTemplate = $isIncoming ? LG_SPEEDTEST_CMD_INCOMING : LG_SPEEDTEST_CMD_OUTGOING;
         // The {target} placeholder is safely replaced.
         $cmd = str_replace('{target}', escapeshellarg(trim($target)), $cmdTemplate);
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
}
