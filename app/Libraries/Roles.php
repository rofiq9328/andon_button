<?php

namespace App\Libraries;

/**
 * Pengecekan role user yang sedang login (bisa lebih dari satu role).
 * Session lama yang hanya punya 'role' tetap dianggap punya role itu.
 */
class Roles
{
    public static function all(): array
    {
        $roles = session()->get('roles');
        if (is_array($roles) && $roles !== []) {
            return $roles;
        }

        $single = session()->get('role');

        return $single ? [$single] : [];
    }

    public static function has(string $role): bool
    {
        return in_array($role, self::all(), true);
    }

    public static function hasAny(array $roles): bool
    {
        return array_intersect($roles, self::all()) !== [];
    }
}
