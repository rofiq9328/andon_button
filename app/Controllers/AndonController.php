<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ButtonModel;
use App\Models\TroubleHistoryModel;
use App\Models\ClearLogModel;

class AndonController extends BaseController
{
    public function index()
    {
        return view('andon/dashboard');
    }

    public function status()
    {
        $config = config('AndonConfig');
        $lines = $config->lines;

        $multiHandle = curl_multi_init();
        $handles = [];

        foreach ($lines as $index => $line) {
            $url = "http://{$line['ip']}:{$line['port']}/context/global";
            $handles[$index] = curl_init();
            curl_setopt_array($handles[$index], [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT_MS => 900,
                CURLOPT_CONNECTTIMEOUT_MS => 500,
                CURLOPT_NOSIGNAL => true,
            ]);
            curl_multi_add_handle($multiHandle, $handles[$index]);
        }

        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            $selected = curl_multi_select($multiHandle, 0.2);
            // Hindari busy-loop pada kondisi tertentu ketika tidak ada socket yang siap.
            if ($selected === -1) {
                usleep(10000);
            }
        } while ($running > 0);

        $results = [];
        foreach ($lines as $index => $line) {
            $handle = $handles[$index];
            $content = curl_multi_getcontent($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $responseTime = curl_getinfo($handle, CURLINFO_TOTAL_TIME) * 1000;

            if ($content && $httpCode === 200) {
                $data = json_decode($content, true);
                if ($data !== null) {
                    $workers = $this->parseWorkers($data['memory'] ?? []);
                    $results[] = [
                        'line_number' => $line['line_number'],
                        'ip' => $line['ip'],
                        'building' => $line['building'],
                        'status' => 'online',
                        'factory' => $data['memory']['factory_number']['msg'] ?? $line['factory'],
                        'lights' => $this->parseLights($data['memory'] ?? []),
                        'workers' => $workers,
                        'total_active' => $this->countActiveWorkers($workers),
                        'has_trouble' => $this->hasTrouble($workers),
                        'timestamp' => date('Y-m-d H:i:s'),
                        'response_time' => $responseTime,
                    ];
                }
            }

            curl_multi_remove_handle($multiHandle, $handle);
            curl_close($handle);
        }
        curl_multi_close($multiHandle);

        return $this->response->setJSON([
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'total_processed' => count($results),
            'data' => $results,
        ]);
    }

    // Method untuk menggantikan clear_api.php
    public function clear()
    {
        $input = $this->request->getJSON(true);

        $lineNumber = $input['line_number'] ?? '';
        $workerNumber = $input['worker_number'] ?? '';
        $troubleType = $input['trouble_type'] ?? '';
        $factory = $input['factory'] ?? '1';

        if (empty($lineNumber) || empty($workerNumber)) {
            return $this->response->setJSON(['success' => false, 'error' => 'ID andon tidak ditemukan']);
        }

        $buttonModel = new ButtonModel();
        $buttonData = $buttonModel->getButtonId($lineNumber, $workerNumber, $factory);

        if (!$buttonData || empty($buttonData['id'])) {
            return $this->response->setJSON([
                'success' => false,
                'error' => "Button ID tidak ditemukan untuk Line $lineNumber Worker $workerNumber",
            ]);
        }

        $buttonId = $buttonData['id'];
        $target = $this->resolveClearTarget((int) $lineNumber, (string) $buttonData['factory']);
        $result = $this->sendClearSocket($buttonId, $target);

        if ($result['success']) {
            $troubleModel = new TroubleHistoryModel();
            $troubleModel->markAsCleared(
                $lineNumber,
                $buttonModel->cleanWorkerNumber($workerNumber),
                $troubleType,
                true
            );

            $logModel = new ClearLogModel();
            $logModel->insert([
                'line_number' => $lineNumber,
                'worker_number' => $buttonModel->cleanWorkerNumber($workerNumber),
                'trouble_type' => $troubleType,
                'serial_number' => $buttonId,
                'command' => "TX:$buttonId:05:00:0:CL",
                'result' => 'success',
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON($result);
    }

    /**
     * Menentukan server tujuan clear. Line yang ada di clear_areas dikirim
     * ke Raspberry area tersebut, selain itu ke server factory seperti biasa.
     */
    private function resolveClearTarget(int $lineNumber, string $factory): ?array
    {
        $config = config('AndonConfig');

        foreach ($config->clear_areas as $area) {
            if (in_array($lineNumber, $area['lines'], true)) {
                return [
                    'host' => $area['host'],
                    'port' => $area['port'],
                ];
            }
        }

        return $config->factory_config[$factory] ?? null;
    }

    private function sendClearSocket($buttonId, ?array $server)
    {
        if (!$server) {
            return ['success' => false, 'error' => 'Konfigurasi server clear tidak ditemukan'];
        }

        $target = "{$server['host']}:{$server['port']}";

        $socket = @fsockopen($server['host'], $server['port'], $errno, $errstr, 2);
        if (!$socket) {
            return ['success' => false, 'error' => "Gagal konek ke {$target}"];
        }

        $sent = fwrite($socket, $buttonId . "\n");
        fclose($socket);

        if ($sent === false) {
            return ['success' => false, 'error' => "Gagal mengirim clear ke {$target}"];
        }

        return [
            'success' => true,
            'message' => 'ANDON CLEAR SENT',
            'data'    => ['button_id' => $buttonId, 'target' => $target],
        ];
    }

    private function parseWorkers($memory)
    {
        $workers = ['spv' => [], 'mk' => [], 'qc' => [], 'mt' => []];
        $types = ['spv_array' => 'spv', 'mk_array' => 'mk', 'qc_array' => 'qc', 'mt_array' => 'mt'];

        foreach ($types as $memoryKey => $workerType) {
            if (isset($memory[$memoryKey]['msg'])) {
                $arrayStr = trim($memory[$memoryKey]['msg'], '[]" ');
                if (!empty($arrayStr)) {
                    $workerNumbers = array_map('trim', explode(',', $arrayStr));
                    $workers[$workerType] = array_filter(
                        $workerNumbers,
                        static fn ($number) => !empty($number) && $number !== '""'
                    );
                }
            }
        }

        return $workers;
    }

    private function parseLights($memory)
    {
        $lights = ['spv' => false, 'mk' => false, 'qc' => false, 'mt' => false];
        foreach (['spv_array', 'mk_array', 'qc_array', 'mt_array'] as $key) {
            if (isset($memory[$key]['msg'])) {
                $lights[explode('_', $key)[0]] = !empty(trim($memory[$key]['msg'], '[]" '));
            }
        }

        return $lights;
    }

    private function countActiveWorkers($workers)
    {
        return array_reduce($workers, static fn ($carry, $item) => $carry + count($item), 0);
    }

    private function hasTrouble($workers)
    {
        foreach ($workers as $numbers) {
            if (!empty($numbers)) {
                return true;
            }
        }

        return false;
    }
}