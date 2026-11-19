<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['username', 'email', 'password', 'full_name', 'role', 'is_active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id'       => 'permit_empty|is_natural_no_zero',
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'role'     => 'required|in_list[admin,supervisor,user,pic_audit,petugas_area,verifikator,viewer]',
    ];

    protected $validationMessages = [
        'username' => [
            'is_unique' => 'Username ini sudah dipakai.',
        ],
    ];

    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Helper untuk create/update user dengan password otomatis di-hash.
     * Kalau field password kosong saat update, tidak akan diubah.
     */
    public function saveWithHashedPassword(array $data, ?int $id = null): bool
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        if ($id) {
            // Wajib disisipkan: $validationRules di atas pakai placeholder {id}
            // (is_unique[...,id,{id}]) untuk mengecualikan baris ini sendiri
            // saat cek username/email unik. Placeholder itu diambil dari key
            // 'id' pada $data yang divalidasi, bukan dari argumen $id di sini.
            $data['id'] = $id;

            return $this->update($id, $data);
        }

        return (bool) $this->insert($data);
    }
}