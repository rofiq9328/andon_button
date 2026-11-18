<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AndonPoller extends BaseCommand
{
    protected $group       = 'Andon';
    protected $name        = 'andon:poll';
    protected $description = 'Menarik data asinkron dari Raspberry Pi ke Cache.';

    private function generateLineConfigs()
    {
        $lines = [];

        foreach (config('AndonConfig')->lines as $l) {
            $lines[] = [
                'line_number' => 'Line ' . $l['line_number'],
                'ip'          => $l['ip'],
                'port'        => $l['port'],
                'building'    => str_replace('Building ', '', $l['building']),
                'factory'     => $l['factory'],
            ];
        }

        return $lines;
    }

    public function run(array $params)
    {
        $cache = \Config\Services::cache();
        $lines = $this->generateLineConfigs();

        CLI::write("Memulai Andon Poller Worker untuk " . count($lines) . " Line...", 'green');

        while (true) {
            $multiHandle = curl_multi_init();
            $handles = [];

            foreach ($lines as $index => $line) {
                $url = "http://{$line['ip']}:{$line['port']}/context/global";
                $handles[$index] = curl_init();
                curl_setopt_array($handles[$index], [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 2,
                    CURLOPT_CONNECTTIMEOUT => 1,
                ]);
                curl_multi_add_handle($multiHandle, $handles[$index]);
            }

            $running = null;
            do {
                curl_multi_exec($multiHandle, $running);
                curl_multi_select($multiHandle);
            } while ($running > 0);

            $results = [];
            foreach ($lines as $index => $line) {
                $handle = $handles[$index];
                $content = curl_multi_getcontent($handle);
                $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

                if ($content && $httpCode === 200) {
                    $data = json_decode($content, true);
                    $memory = $data['memory'] ?? [];
                    $results[] = [
                        'line_number' => $line['line_number'],
                        'ip'          => $line['ip'],
                        'building'    => $line['building'],
                        'factory'     => $line['factory'],
                        'status'      => 'online',
                        'raw_memory'  => $memory,
                    ];
                } else {
                    $results[] = [
                        'line_number' => $line['line_number'],
                        'ip'          => $line['ip'],
                        'building'    => $line['building'],
                        'status'      => 'offline',
                    ];
                }

                curl_multi_remove_handle($multiHandle, $handle);
                curl_close($handle);
            }
            curl_multi_close($multiHandle);

            $cache->save('andon_latest_status', $results, 5);
            CLI::write("Data diperbarui pada: " . date('Y-m-d H:i:s'));
            sleep(2);
        }
    }
}
