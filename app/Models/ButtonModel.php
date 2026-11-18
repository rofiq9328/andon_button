<?php

namespace App\Models;

use CodeIgniter\Model;

class ButtonModel extends Model
{
    protected $table      = 'button_id';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = ['id', 'factory', 'line', 'worker'];

    /**
     * Utility bawaan model untuk membersihkan nomor worker
     */
    public function cleanWorkerNumber($worker_number)
    {
        if (empty($worker_number)) return '';
        $clean = str_replace(['"', '\\"', '\\'], '', $worker_number);
        return preg_replace('/[^0-9]/', '', $clean);
    }

    /**
     * Mencari Button ID spesifik
     */
    public function getButtonId($line, $worker, $factory = '1')
    {
        $workerClean = $this->cleanWorkerNumber($worker);
        $factory = in_array($factory, ['01', '1']) ? '1' : '2';

        // Menggunakan Query Builder CI4 (Otomatis aman dari SQL Injection tanpa PDO prepare manual)
        $result = $this->where('factory', $factory)
                       ->where('line', $line)
                       ->where('worker', $workerClean)
                       ->first();

        // Coba alternatif cleaning jika tidak ketemu (sama seperti fungsi lama)
        if (!$result) {
            $workerAlt = preg_replace('/\D/', '', $worker);
            $result = $this->where('factory', $factory)
                           ->where('line', $line)
                           ->where('worker', $workerAlt)
                           ->first();
        }

        return $result;
    }
}