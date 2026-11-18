<?php

namespace App\Models;

use CodeIgniter\Model;

class PicAreaModel extends Model
{
    protected $table         = 'pic_area';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'line_from', 'line_to'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'user_id'   => 'required|is_natural_no_zero',
        'line_from' => 'required|is_natural_no_zero',
        'line_to'   => 'required|is_natural_no_zero',
    ];

    /**
     * Semua rentang line milik satu PIC.
     */
    public function getRangesForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('line_from', 'ASC')
            ->findAll();
    }

    /**
     * Cek apakah rentang line bertabrakan dengan penugasan PIC mana pun.
     * $exceptId dipakai saat update, supaya baris itu sendiri tidak dihitung.
     */
    public function hasOverlap(int $lineFrom, int $lineTo, ?int $exceptId = null): bool
    {
        $builder = $this->where('line_from <=', $lineTo)
            ->where('line_to >=', $lineFrom);

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }
}
