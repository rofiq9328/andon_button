<?php

namespace App\Controllers;

use App\Models\AuditAndonDetailModel;
use App\Models\AuditAndonModel;
use App\Models\AuditAndonUnitModel;
use App\Models\PicAreaModel;

class AuditController extends BaseController
{
    /** Field yang boleh diubah (termasuk catatan). */
    private const EDIT_FIELDS = [
        'tersedia', 'kondisi_remote', 'visual_nomor',
        'material_lampu', 'material_layar',
        'mekanik_lampu', 'mekanik_layar',
        'qc_lampu', 'qc_layar',
        'spv_lampu', 'spv_layar',
        'perbaikan_rusak', 'ganti_baterai',
        'catatan',
    ];

    /** Nilai normal tiap kolom, dipakai untuk isi cepat. */
    private const NORMAL = [
        'tersedia'        => 'ADA',
        'kondisi_remote'  => 'ON',
        'visual_nomor'    => 'ADA',
        'material_lampu'  => 'ON',
        'material_layar'  => 'MUNCUL',
        'mekanik_lampu'   => 'ON',
        'mekanik_layar'   => 'MUNCUL',
        'qc_lampu'        => 'ON',
        'qc_layar'        => 'MUNCUL',
        'spv_lampu'       => 'ON',
        'spv_layar'       => 'MUNCUL',
        'perbaikan_rusak' => 'NO',
        'ganti_baterai'   => 'NO',
    ];

    /** Nilai yang berarti perlu perhatian, dipakai untuk status unit. */
    private const BAD = [
        'tersedia'        => 'TIDAK ADA',
        'kondisi_remote'  => 'OFF',
        'visual_nomor'    => 'TIDAK ADA',
        'material_lampu'  => 'OFF',
        'material_layar'  => 'TIDAK',
        'mekanik_lampu'   => 'OFF',
        'mekanik_layar'   => 'TIDAK',
        'qc_lampu'        => 'OFF',
        'qc_layar'        => 'TIDAK',
        'spv_lampu'       => 'OFF',
        'spv_layar'       => 'TIDAK',
        'perbaikan_rusak' => 'YES',
        'ganti_baterai'   => 'YES',
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
     * Status satu unit: bad, ok, partial, atau empty.
     */
    private function unitState(array $u): string
    {
        $filled = 0;
        $bad    = false;

        foreach (self::BAD as $field => $badValue) {
            if ($u[$field] !== null) {
                $filled++;
                if ($u[$field] === $badValue) {
                    $bad = true;
                }
            }
        }

        if ($bad) {
            return 'bad';
        }
        if ($filled === count(self::BAD)) {
            return 'ok';
        }

        return $filled > 0 ? 'partial' : 'empty';
    }

    /**
     * Sesi yang boleh dilihat user ini.
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

        (new AuditAndonDetailModel())->createForSession(
            (int) $session['id'],
            $periode,
            $this->unitsForUser($this->userId())
        );

        return $session;
    }

    /**
     * Admin: daftar sesi per periode.
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
            $units = $detailModel->where('audit_id', $s['id'])->findAll();
            $ok = 0;
            foreach ($units as $u) {
                if ($this->unitState($u) === 'ok') {
                    $ok++;
                }
            }
            $s['total']    = count($units);
            $s['terisi']   = $ok;
            $s['editable'] = $auditModel->isEditable($s);
        }
        unset($s);

        return view('audit/index', [
            'sessions' => $sessions,
            'periode'  => $periode,
        ]);
    }

    /**
     * Halaman utama: kartu per line dengan status.
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
            $row = ['line' => (int) $line, 'total' => count($units), 'ok' => 0, 'bad' => 0, 'partial' => 0, 'empty' => 0];
            foreach ($units as $u) {
                $row[$this->unitState($u)]++;
            }
            $lines[] = $row;
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

    public function linePic(int $line)
    {
        if ($this->isAdmin()) {
            return redirect()->to('audit-andon');
        }

        return $this->renderLine($this->resolveSession(null), $line);
    }

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
     * Autosave satu field (dipanggil dari JavaScript). Balas JSON.
     */
    public function autosave()
    {
        $sessionId = (int) $this->request->getPost('session_id');
        $unitId    = (int) $this->request->getPost('unit_id');
        $field     = (string) $this->request->getPost('field');
        $value     = trim((string) $this->request->getPost('value'));

        $auditModel = new AuditAndonModel();
        $session    = $auditModel->find($sessionId);

        if (!$session || !$this->canAccess($session)) {
            return $this->jsonError('Sesi tidak ditemukan.', 404);
        }
        if (!$auditModel->isEditable($session)) {
            return $this->jsonError('Sesi sudah terkunci.', 403);
        }
        if (!in_array($field, self::EDIT_FIELDS, true)) {
            return $this->jsonError('Kolom tidak valid.', 422);
        }

        $detailModel = new AuditAndonDetailModel();
        $detail      = $detailModel->where(['id' => $unitId, 'audit_id' => $sessionId])->first();
        if (!$detail) {
            return $this->jsonError('Unit tidak ditemukan.', 404);
        }

        $ok = $detailModel->update($unitId, [
            $field       => $value === '' ? null : $value,
            'updated_by' => $this->userId(),
        ]);

        if (!$ok) {
            $errors = $detailModel->errors();

            return $this->jsonError($errors ? implode(' ', $errors) : 'Nilai tidak valid.', 422);
        }

        return $this->response->setJSON([
            'ok'   => true,
            'time' => date('H:i:s'),
            'csrf' => csrf_hash(),
        ]);
    }

    private function jsonError(string $message, int $status)
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON(['ok' => false, 'message' => $message, 'csrf' => csrf_hash()]);
    }

    /**
     * Isi cepat: hanya mengisi kotak yang masih kosong, dengan nilai normal.
     * field = nama kolom, atau 'all' untuk semua kolom.
     */
    public function quickFill()
    {
        $sessionId = (int) $this->request->getPost('session_id');
        $line      = (int) $this->request->getPost('line');
        $field     = (string) $this->request->getPost('field');

        $auditModel = new AuditAndonModel();
        $session    = $auditModel->find($sessionId);

        if (!$session || !$this->canAccess($session)) {
            return redirect()->to('audit-andon/isi')->with('error', 'Sesi tidak ditemukan.');
        }

        $target = $this->baseFor($session) . '/line/' . $line;

        if (!$auditModel->isEditable($session)) {
            return redirect()->to($target)->with('error', 'Sesi sudah terkunci.');
        }

        $fields = $field === 'all' ? array_keys(self::NORMAL) : (isset(self::NORMAL[$field]) ? [$field] : []);
        if (empty($fields)) {
            return redirect()->to($target)->with('error', 'Kolom tidak valid.');
        }

        $db    = db_connect();
        $total = 0;

        foreach ($fields as $f) {
            $db->table('audit_andon_detail')
                ->where('audit_id', $sessionId)
                ->where('line', $line)
                ->where("{$f} IS NULL", null, false)
                ->update([
                    $f           => self::NORMAL[$f],
                    'updated_by' => $this->userId(),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            $total += $db->affectedRows();
        }

        return redirect()->to($target)
            ->with('success', "{$total} kotak kosong diisi dengan nilai normal.");
    }

    /**
     * Ringkasan sebelum diserahkan.
     */
    public function review(?int $sessionId = null)
    {
        $session = $this->resolveSession($sessionId);
        if ($session === null) {
            return redirect()->to('audit-andon')->with('error', 'Sesi tidak ditemukan.');
        }

        $grouped = (new AuditAndonDetailModel())->getForSessionGrouped((int) $session['id']);

        $totals   = ['total' => 0, 'ok' => 0, 'bad' => 0, 'partial' => 0, 'empty' => 0];
        $perLine  = [];
        $problems = [];

        foreach ($grouped as $line => $units) {
            $row = ['line' => (int) $line, 'total' => count($units), 'ok' => 0, 'bad' => 0, 'partial' => 0, 'empty' => 0];

            foreach ($units as $u) {
                $state = $this->unitState($u);
                $row[$state]++;
                $totals[$state]++;
                $totals['total']++;

                if ($state === 'bad') {
                    $problems[] = [
                        'line'    => (int) $line,
                        'line_no' => (int) $u['line_no'],
                        'id'      => (int) $u['id'],
                    ];
                }
            }

            $perLine[] = $row;
        }

        return view('audit/review', [
            'session'  => $session,
            'owner'    => $this->ownerOf($session),
            'editable' => (new AuditAndonModel())->isEditable($session),
            'base'     => $this->baseFor($session),
            'isAdmin'  => $this->isAdmin(),
            'totals'   => $totals,
            'perLine'  => $perLine,
            'problems' => $problems,
        ]);
    }

    /**
     * Simpan isian (dipakai untuk tombol Serahkan).
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

        return redirect()->to($target)->with('success', 'Data tersimpan.');
    }

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
