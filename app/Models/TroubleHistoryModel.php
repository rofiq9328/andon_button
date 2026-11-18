<?php

namespace App\Models;

use CodeIgniter\Model;

class TroubleHistoryModel extends Model
{
    protected $table      = 'trouble_history';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'line_number', 'building', 'factory', 'trouble_type', 
        'worker_number', 'status', 'timestamp', 'cleared_at', 'duration_seconds'
    ];

    /**
     * Menandai trouble telah di-clear (baik via alat atau manual web)
     */
    public function markAsCleared($line_number, $worker_clean, $trouble_type, $is_manual = false)
    {
        $status = $is_manual ? 'cleared_manual' : 'cleared';
        
        return $this->where('line_number', $line_number)
                    ->where('worker_number', $worker_clean)
                    ->where('trouble_type', $trouble_type)
                    ->where('status', 'active') // Hanya update yang masih aktif
                    ->set('status', $status)
                    ->set('cleared_at', 'NOW()', false)
                    ->set('duration_seconds', 'TIMESTAMPDIFF(SECOND, timestamp, NOW())', false)
                    ->update();
    }
}