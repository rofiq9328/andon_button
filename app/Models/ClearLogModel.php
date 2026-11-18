<?php

namespace App\Models;

use CodeIgniter\Model;

class ClearLogModel extends Model
{
    protected $table      = 'clear_log';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'line_number', 'worker_number', 'trouble_type', 
        'serial_number', 'command', 'result', 'timestamp'
    ];
}