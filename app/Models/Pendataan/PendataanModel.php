<?php

namespace App\Models\Pendataan;

use CodeIgniter\Model;

/**
 * Model dasar untuk database pendataan barang (grup koneksi 'pendataan').
 */
class PendataanModel extends Model
{
    protected $DBGroup       = 'pendataan';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
