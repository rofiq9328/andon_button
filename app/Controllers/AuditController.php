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
     * Base URL halaman sesi ini (tanpa slash di depan).
     * PIC: audit-andon/isi. Admin: audit-andon/sesi/{id}.
     */
    private function baseFor(array $session): string
    {
        return $this->isAdmin()
            ? 'audit-andon/sesi/' . (int) $session['id']
            : 'audit-andon/isi';
    }

    private function ownerOf(array $session): ?array
    {
        return db_connect()->table('users')
            ->select('full_name, username')
            ->where('id', $session['user_id'])
            ->get()
            ->getRowArray();
    }

    /**
     * Sesi yang boleh dilihat user ini.
     * Dengan $sessionId: sesi tertentu, kalau user boleh mengaksesnya.
     * Tanpa $sessionId: PIC mendapat sesi periode berjalan (dibuat kalau belum ada).
     */
    private function resolveSession(?int $sessionId): ?array
    {
        $auditModel = new AuditAndonModel();

        if ($sessionId !== null) {
            $session = $auditModel->find($sessionId);

            return ($session && $this->canAccess($session)) ? $session : null;
        }

        if ($this->isAdmin()) {
            return null;
        }

        $periode = AuditAndonModel::currentPeriode();
        $session = $auditModel->getOrCreateSession($periode, $this->userId())['session'];

        // Sinkronkan unit dari penugasan terbaru. INSERT IGNORE,
        // jadi unit yang sudah ada tidak dobel dan isiannya tetap.
        (new AuditAndonDetailModel())->createForSession(
            (int) $session['id'],
            $periode,
            $this->unitsForUser($this->userId())
        );

        return $session;
    }

    /**
     * Admin: daftar sesi per periode (default periode berjalan).
     */
    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('audit-andon/isi');
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
     * Halaman utama sesi: kartu per line dengan progres.
     * PIC: sesi miliknya. Admin: sesi tertentu lewat /audit-andon/sesi/{id}.
     */
    public function form(?int $sessionId = null)
    {
        $session = $this->resolveSession($sessionId);
        if ($session === null) {
            return redirect()->to('audit-andon')->with('error', 'Sesi tidak ditemukan.');
        }

        $grouped = (new AuditAndonDetailModel())->getForSessionGrouped((int) $session['id']);

        $lines = [];
        foreach ($grouped as $line => $units) {
            $lines[] = [
                'line'   => (int) $line,
                'total'  => count($units),
                'terisi' => count(array_filter($units, static fn ($u) => $u['kondisi_remote'] !== null)),
            ];
        }

        return view('audit/form', [
            'session'  => $session,
            'owner'    => $this->ownerOf($session),
            'editable' => (new AuditAndonModel())->isEditable($session),
            'lines'    => $lines,
            'base'     => $this->baseFor($session),
            'isAdmin'  => $this->isAdmin(),
        ]);
    }

    /**
     * Form satu line untuk PIC (periode berjalan).
     */
    public function linePic(int $line)
    {
        if ($this->isAdmin()) {
            return redirect()->to('audit-andon');
        }

        $session = $this->resolveSession(null);

        return $this->renderLine($session, $line);
    }

    /**
     * Form satu line untuk admin (atau PIC pemilik sesi).
     */
    public function lineAdmin(int $sessionId, int $line)
    {
        $session = $this->resolveSession($sessionId);
        if ($session === null) {
            return redirect()->to('audit-andon')->with('error', 'Sesi tidak ditemukan.');
        }

        return $this->renderLine($session, $line);
    }

    private function renderLine(array $session, int $line)
    {
        $base    = $this->baseFor($session);
        $grouped = (new AuditAndonDetailModel())->getForSessionGrouped((int) $session['id']);

        if (!isset($grouped[$line])) {
            return redirect()->to($base)->with('error', "Line {$line} tidak ada di sesi ini.");
        }

        // Navigasi ke line sebelum dan sesudahnya
        $lineNumbers = array_keys($grouped);
        $idx  = array_search($line, $lineNumbers, true);
        $prev = $idx > 0 ? $lineNumbers[$idx - 1] : null;
        $next = ($idx !== false && $idx < count($lineNumbers) - 1) ? $lineNumbers[$idx + 1] : null;

        return view('audit/line', [
            'session'  => $session,
            'owner'    => $this->ownerOf($session),
            'editable' => (new AuditAndonModel())->isEditable($session),
            'line'     => $line,
            'units'    => $grouped[$line],
            'prev'     => $prev,
            'next'     => $next,
            'base'     => $base,
            'isAdmin'  => $this->isAdmin(),
        ]);
    }

    /**
     * Simpan isian satu line. action = 'draft' menyimpan line,
     * action = 'serahkan' (tanpa line) menandai sesi diserahkan.
     */
    public function save()
    {
        $auditModel  = new AuditAndonModel();
        $detailModel = new AuditAndonDetailModel();

        $sessionId = (int) $this->request->getPost('session_id');
        $session   = $auditModel->find($sessionId);

        if (!$session || !$this->canAccess($session)) {
            return redirect()->to($this->isAdmin() ? 'audit-andon' : 'audit-andon/isi')
                ->with('error', 'Sesi tidak ditemukan.');
        }

        $base    = $this->baseFor($session);
        $line    = (int) $this->request->getPost('line');
        $lineUrl = $base . '/line/' . $line;
        $target  = $line > 0 ? $lineUrl : $base;

        if (!$auditModel->isEditable($session)) {
            return redirect()->to($target)
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
            return redirect()->to($target)
                ->with('error', "{$gagal} baris gagal disimpan. Periksa nilai isiannya.");
        }

        if ($this->request->getPost('action') === 'serahkan') {
            $auditModel->markSubmitted($sessionId);

            return redirect()->to($base)
                ->with('success', 'Data diserahkan. Anda masih bisa mengubah sampai akhir bulan.');
        }

        return redirect()->to($target)->with('success', "Line {$line} tersimpan.");
    }

    /**
     * Admin membuka kembali sesi yang sudah terkunci.
     */
    public function reopen(int $sessionId)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('audit-andon/isi')
                ->with('error', 'Hanya admin yang bisa membuka kembali sesi.');
        }

        (new AuditAndonModel())->reopen($sessionId);

        return redirect()->to('audit-andon/sesi/' . $sessionId)
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
