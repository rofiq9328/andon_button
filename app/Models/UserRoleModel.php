<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    public const ALL_ROLES = ['admin', 'supervisor', 'user', 'pic_audit', 'petugas_area', 'verifikator', 'viewer'];

    public const LABELS = [
        'admin'        => 'Admin',
        'supervisor'   => 'Supervisor',
        'user'         => 'User',
        'pic_audit'    => 'PIC Audit',
        'petugas_area' => 'Petugas Area (Pendataan)',
        'verifikator'  => 'Verifikator (Pendataan)',
        'viewer'       => 'Viewer (Pendataan)',
    ];

    protected $table         = 'user_roles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'role'];
    protected $useTimestamps = false;

    /**
     * Semua role satu user.
     */
    public function rolesForUser(int $userId): array
    {
        return array_column($this->where('user_id', $userId)->findAll(), 'role');
    }

    /**
     * Role untuk banyak user sekaligus, bentuknya: [user_id => [role, ...]].
     */
    public function rolesForUsers(array $userIds): array
    {
        $map = [];
        if (empty($userIds)) {
            return $map;
        }

        foreach ($this->whereIn('user_id', $userIds)->findAll() as $row) {
            $map[(int) $row['user_id']][] = $row['role'];
        }

        return $map;
    }

    /**
     * Mengganti semua role seorang user dengan daftar baru.
     */
    public function syncRoles(int $userId, array $roles): void
    {
        $this->where('user_id', $userId)->delete();

        foreach (self::sanitize($roles) as $role) {
            $this->insert(['user_id' => $userId, 'role' => $role]);
        }
    }

    /**
     * Hanya menerima role yang dikenal, tanpa duplikat, dengan urutan tetap.
     */
    public static function sanitize($input): array
    {
        $list = is_array($input) ? $input : [];
        $list = array_filter(
            array_map('strval', $list),
            static fn ($role) => in_array($role, self::ALL_ROLES, true)
        );

        return array_values(array_unique($list));
    }
}
