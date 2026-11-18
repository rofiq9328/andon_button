<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditAndonUnitModel extends Model
{
    protected $table         = 'audit_andon_units';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['line', 'line_no'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Tabel ini terpisah dari button_id dan tidak dipakai oleh fitur clear.

    /**
     * Semua unit dalam rentang line (inklusif), urut per line lalu line_no.
     */
    public function getUnitsInRange(int $lineFrom, int $lineTo): array
    {
        return $this->where('line >=', $lineFrom)
            ->where('line <=', $lineTo)
            ->orderBy('line', 'ASC')
            ->orderBy('line_no', 'ASC')
            ->findAll();
    }
}
