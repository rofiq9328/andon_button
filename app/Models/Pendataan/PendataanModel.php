<?php

namespace App\Models\Pendataan;

use CodeIgniter\Model;

/**
 * Model dasar pendataan barang. Memakai database yang sama dengan andon.
 */
class PendataanModel extends Model
{
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
