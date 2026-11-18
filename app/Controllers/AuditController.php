<?php

namespace App\Controllers;

use App\Models\AuditAndonDetailModel;
use App\Models\AuditAndonModel;
use App\Models\AuditAndonUnitModel;
use App\Models\PicAreaModel;

class AuditController extends BaseController
{
    /**
     * Field yang boleh diubah dari form. Field lain (id, audit_id, line, dst)
     * tidak diambil dari request, supaya tidak bisa diubah lewat form.
     */
    private const EDIT_FIELDS = [
        'tersedia', 'kondisi_remote', 'visual_nomor',
        'material_lampu', 'material_layar',
        'mekanik_lampu', 'mekanik_layar',
        'qc_lampu', 'qc_layar',
        'spv_lampu', 'spv_layar',
        'perbaikan_rusak', 'ganti_baterai',
        'catatan',
    ];

    private function isAdmin(): bool
    {
        return session()->get('role') === 'admin';
    }

    private function userId(): int
    {
        return (int) session()->get('user_id');
    }

    private function canAccess(array $session): bool
    {
        return $this->isAdmin() || (int) $session['user_id'] === $this->userId();
    }

    /**
     * Admin: daftar sesi per periode (default periode berjalan).
     * PIC: langsung ke form miliknya.
     */
    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/audit-andon/isi');
        }

        $auditModel  = new AuditAndonModel();
        $detailModel = new AuditAndonDetailModel();
        $periode     = $this->request->getGet('periode') ?: AuditAndonModel::currentPeriode();

        $sessions = db_connect()->table('audit_andon a')
            ->select('a.*, u.full_name, u.username')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->where('a.periode', $periode)
            ->orderBy('u.full_name', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($sessions as &$s) {
            $s['total'] = $detailModel->where('audit_id', $s['id'])->countAllResults();
            $s['terisi'] = $detailModel->where('audit_id', $s['id'])
                ->where('kondisi_remote IS NOT NULL', null, false)
                ->countAllResults();
            $s['editable'] = $auditModel->isEditable($s);
        }
        unset($s);

        return view('audit/index', [
            'sessions' => $sessions,
            'periode'  => $periode,
        ]);
    }

    /**
     * PIC: sesi miliknya untuk periode berjalan (dibuat otomatis kalau belum ada).
     * Admin: sesi tertentu lewat /audit-andon/sesi/{id}.
     */
    public function form(?int $sessionId = null)
    {
        $auditModel  = new AuditAndonModel();
        $detailModel = new AuditAndonDetailModel();

        if ($this->isAdmin()) {
            if ($sessionId === null) {
                return redirect()->to('/audit-andon');
            }

            $session = $auditModel->find($sessionId);
            if (!$session) {
                return redirect()->to('/audit-andon')->with('error', 'Sesi tidak ditemukan.');
            }
        } else {
            $periode = AuditAndonModel::currentPeriode();
            $result  = $auditModel->getOrCreateSession($periode, $this->userId());
            $session = $result['session'];

            // Sinkronkan unit dari penugasan terbaru. INSERT IGNORE,
            // jadi unit yang sudah ada tidak dobel dan isiannya tetap.
            $detailModel->createForSession(
                (int) $session['id'],
                $periode,
                $this->unitsForUser($this->userId())
            );
        }

        $owner = db_connect()->table('users')
            ->select('full_name, username')
            ->where('id', $session['user_id'])
            ->get()
            ->getRowArray();

        return view('audit/form', [
            'session'  => $session,
            'owner'    => $owner,
            'editable' => $auditModel->isEditable($session),
            'grouped'  => $detailModel->getForSessionGrouped((int) $session['id']),
            'isAdmin'  => $this->isAdmin(),
        ]);
    }

    /**
     * Simpan isian. action = 'draft' menyimpan saja,
     * action = 'serahkan' menyimpan lalu menandai sesi diserahkan.
     */
    public function save()
    {
        $auditModel  = new AuditAndonModel();
        $detailModel = new AuditAndonDetailModel();

        $sessionId = (int) $this->request->getPost('session_id');
        $session   = $auditModel->find($sessionId);

        if (!$session || !$this->canAccess($session)) {
            return redirect()->to($this->isAdmin() ? '/audit-andon' : '/audit-andon/isi')
                ->with('error', 'Sesi tidak ditemukan.');
        }

        $backUrl = $this->isAdmin()
            ? '/audit-andon/sesi/' . $sessionId
            : '/audit-andon/isi';

        $line   = (int) $this->request->getPost('line');
        $anchor = $line > 0 ? '#line-' . $line : '';

        if (!$auditModel->isEditable($session)) {
            return redirect()->to($backUrl)
                ->with('error', 'Sesi sudah terkunci. Minta admin membuka kembali.');
        }

        // Hanya baris detail milik sesi ini yang boleh diubah
        $validIds = array_map(
            'intval',
            $detailModel->where('audit_id', $sessionId)->findColumn('id') ?? []
        );

        $rows  = (array) $this->request->getPost('units');
        $gagal = 0;

        foreach ($rows as $detailId => $fields) {
            $detailId = (int) $detailId;
            if (!in_array($detailId, $validIds, true) || !is_array($fields)) {
                continue;
            }

            $data = [];
            foreach (self::EDIT_FIELDS as $field) {
                if (array_key_exists($field, $fields)) {
                    $value        = trim((string) $fields[$field]);
                    $data[$field] = $value === '' ? null : $value;
                }
            }
            $data['updated_by'] = $this->userId();

            if (!$detailModel->update($detailId, $data)) {
                $gagal++;
            }
        }

        if ($gagal > 0) {
            return redirect()->to($backUrl . $anchor)
                ->with('error', "{$gagal} baris gagal disimpan. Periksa nilai isiannya.");
        }

        if ($this->request->getPost('action') === 'serahkan') {
            $auditModel->markSubmitted($sessionId);

            return redirect()->to($backUrl)
                ->with('success', 'Data diserahkan. Anda masih bisa mengubah sampai akhir bulan.');
        }

        return redirect()->to($backUrl . $anchor)->with('success', 'Draft tersimpan.');
    }

    /**
     * Admin membuka kembali sesi yang sudah terkunci.
     */
    public function reopen(int $sessionId)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/audit-andon/isi')
                ->with('error', 'Hanya admin yang bisa membuka kembali sesi.');
        }

        (new AuditAndonModel())->reopen($sessionId);

        return redirect()->to('/audit-andon/sesi/' . $sessionId)
            ->with('success', 'Sesi dibuka kembali.');
    }

    /**
     * Semua unit dari seluruh rentang line milik PIC.
     */
    private function unitsForUser(int $userId): array
    {
        $unitModel = new AuditAndonUnitModel();
        $units     = [];

        foreach ((new PicAreaModel())->getRangesForUser($userId) as $range) {
            $units = array_merge(
                $units,
                $unitModel->getUnitsInRange((int) $range['line_from'], (int) $range['line_to'])
            );
        }

        return $units;
    }
}
