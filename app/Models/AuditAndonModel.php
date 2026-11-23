<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditAndonModel extends Model
{
    protected $table         = 'audit_andon';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['periode', 'user_id', 'status', 'submitted_at', 'reopened_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Periode berjalan dalam format YYYY-MM, contoh '2026-11'.
     */
    public static function currentPeriode(): string
    {
        return date('Y-m');
    }

    /**
     * Ambil sesi PIC untuk periode tertentu.
     * Kalau belum ada, buat header-nya. Baris detail dibuat oleh caller
     * (lihat AuditAndonDetailModel::createForSession).
     *
     * @return array{session: array, created: bool}
     */
    public function getOrCreateSession(string $periode, int $userId): array
    {
        $session = $this->where(['periode' => $periode, 'user_id' => $userId])->first();

        if ($session) {
            return ['session' => $session, 'created' => false];
        }

        $this->insert([
            'periode' => $periode,
            'user_id' => $userId,
            'status'  => 'draft',
        ]);

        return [
            'session' => $this->find($this->getInsertID()),
            'created' => true,
        ];
    }

    /**
     * Sesi bisa diubah selama periodenya masih berjalan, atau kalau admin
     * sudah membukanya kembali (reopened_at terisi).
     */
    public function isEditable(array $session): bool
    {
        if (!empty($session['reopened_at'])) {
            return true;
        }

        return $session['periode'] === self::currentPeriode();
    }

    /**
     * Tandai sesi sebagai diserahkan. Data tetap bisa diubah sampai akhir bulan.
     */
    public function markSubmitted(int $sessionId): bool
    {
        $session = $this->find($sessionId);
        if (!$session) {
            return false;
        }

        $data = ['status' => 'submitted'];

        // Simpan waktu submit pertama kali saja; autosave berikutnya tidak
        // menggeser waktu submit setiap kali PIC mengubah isian.
        if (empty($session['submitted_at'])) {
            $data['submitted_at'] = date('Y-m-d H:i:s');
        }

        return (bool) $this->update($sessionId, $data);
    }

    /**
     * Admin membuka kembali sesi yang sudah terkunci.
     */
    public function reopen(int $sessionId): bool
    {
        return (bool) $this->update($sessionId, [
            'reopened_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
