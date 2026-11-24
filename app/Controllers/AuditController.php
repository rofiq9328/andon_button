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
    ];

    /** Pilihan yang sah untuk pengisian massal per kolom. */
    private const OPTIONS = [
        'tersedia'        => ['ADA', 'TIDAK ADA'],
        'kondisi_remote'  => ['ON', 'OFF'],
        'visual_nomor'    => ['ADA', 'TIDAK ADA'],
        'material_lampu'  => ['ON', 'OFF'],
        'material_layar'  => ['MUNCUL', 'TIDAK'],
        'mekanik_lampu'   => ['ON', 'OFF'],
        'mekanik_layar'  => ['MUNCUL', 'TIDAK'],
        'qc_lampu'        => ['ON', 'OFF'],
        'qc_layar'        => ['MUNCUL', 'TIDAK'],
        'spv_lampu'       => ['ON', 'OFF'],
        'spv_layar'      => ['MUNCUL', 'TIDAK'],
        'perbaikan_rusak' => ['NO', 'YES'],
        'ganti_baterai'   => ['NO', 'YES'],
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

    /** Kolom wajib diperiksa untuk menentukan unit sudah diaudit. */
    private const REQUIRED_FIELDS = [
        'tersedia', 'kondisi_remote', 'visual_nomor',
        'material_lampu', 'material_layar',
        'mekanik_lampu', 'mekanik_layar',
        'qc_lampu', 'qc_layar',
        'spv_lampu', 'spv_layar',
    ];

    private function isAdmin(): bool
    {
        return \App\Libraries\Roles::has('admin');
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
            if (isset($u[$field]) && $u[$field] === $badValue) {
                $bad = true;
            }
        }

        foreach (self::REQUIRED_FIELDS as $field) {
            if (array_key_exists($field, $u) && $u[$field] !== null) {
                $filled++;
            }
        }

        if ($bad) {
            return 'bad';
        }
        if ($filled === count(self::REQUIRED_FIELDS)) {
            return 'ok';
        }

        return $filled > 0 ? 'partial' : 'empty';
    }

    /**
     * Button dianggap sudah diaudit jika seluruh kolom pemeriksaan sudah terisi.
     * Hasil pemeriksaan bermasalah tetap dihitung sebagai sudah diaudit.
     */
    private function isUnitAudited(array $unit): bool
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if ($unit[$field] === null) {
                return false;
            }
        }

        return true;
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
        $sessionResult = $auditModel->getOrCreateSession($periode, $this->userId());
        $session = $sessionResult['session'] ?? null;

        if (!$session) {
            return null;
        }

        // Sinkronisasi seluruh unit hanya saat sesi baru dibuat.
        // Penugasan line berikutnya sudah disinkronkan oleh PicAssignmentController.
        if (!empty($sessionResult['created'])) {
            (new AuditAndonDetailModel())->createForSession(
                (int) $session['id'],
                $periode,
                $this->unitsForUser($this->userId())
            );
        }

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

        $lines = (new AuditAndonDetailModel())->getProgressByLine((int) $session['id']);

        foreach ($lines as &$row) {
            $row['line'] = (int) $row['line'];
            $row['total'] = (int) $row['total'];
            $row['audited'] = (int) $row['audited'];
        }
        unset($row);

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
        $base = $this->baseFor($session);
        $detailModel = new AuditAndonDetailModel();
        $lineNumbers = $detailModel->getLinesForSession((int) $session['id']);

        if (!in_array($line, $lineNumbers, true)) {
            return redirect()->to($base)->with('error', "Line {$line} tidak ada di sesi ini.");
        }

        // Hanya ambil unit dari line yang sedang dibuka, bukan seluruh sesi.
        $units = $detailModel->getForSessionLine((int) $session['id'], $line);
        $idx  = array_search($line, $lineNumbers, true);
        $prev = $idx > 0 ? $lineNumbers[$idx - 1] : null;
        $next = ($idx !== false && $idx < count($lineNumbers) - 1) ? $lineNumbers[$idx + 1] : null;

        return view('audit/line', [
            'session'  => $session,
            'owner'    => $this->ownerOf($session),
            'editable' => (new AuditAndonModel())->isEditable($session),
            'line'     => $line,
            'units'    => $units,
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

        // Validasi nilai di endpoint agar update langsung tetap membatasi pilihan input.
        if ($field !== 'catatan' && $value !== '' &&
            (!isset(self::OPTIONS[$field]) || !in_array($value, self::OPTIONS[$field], true))) {
            return $this->jsonError('Nilai tidak valid untuk kolom tersebut.', 422);
        }

        $db = db_connect();
        $db->table('audit_andon_detail')
            ->where('id', $unitId)
            ->where('audit_id', $sessionId)
            ->update([
                $field       => $value === '' ? null : $value,
                'updated_by' => $this->userId(),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Periksa keberadaan hanya jika tidak ada baris yang berubah
        // (misalnya nilai yang dikirim ternyata sama dengan nilai lama).
        if ($db->affectedRows() < 1) {
            $exists = $db->table('audit_andon_detail')
                ->where('id', $unitId)
                ->where('audit_id', $sessionId)
                ->countAllResults();

            if ($exists < 1) {
                return $this->jsonError('Unit tidak ditemukan.', 404);
            }
        }

        if (!$auditModel->markSubmitted($sessionId)) {
            return $this->jsonError('Data tersimpan, tetapi status sesi gagal diperbarui.', 500);
        }

        return $this->response->setJSON([
            'ok'     => true,
            'status' => 'active',
            'time'   => date('H:i:s'),
            'csrf'   => csrf_hash(),
        ]);
    }

    /**
     * Autosave batch: menyimpan beberapa perubahan dari satu form dalam satu request
     * dan satu query UPDATE. Mengurangi request HTTP saat PIC mengisi cepat.
     */
    public function autosaveBatch()
    {
        $sessionId = (int) $this->request->getPost('session_id');
        $rawChanges = (string) $this->request->getPost('changes');
        $changes = json_decode($rawChanges, true);

        if (!is_array($changes) || empty($changes)) {
            return $this->jsonError('Tidak ada perubahan untuk disimpan.', 422);
        }
        if (count($changes) > 500) {
            return $this->jsonError('Jumlah perubahan terlalu banyak dalam satu proses.', 422);
        }

        $auditModel = new AuditAndonModel();
        $session = $auditModel->find($sessionId);

        if (!$session || !$this->canAccess($session)) {
            return $this->jsonError('Sesi tidak ditemukan.', 404);
        }
        if (!$auditModel->isEditable($session)) {
            return $this->jsonError('Sesi sudah terkunci.', 403);
        }

        // Simpan nilai terakhir untuk kombinasi unit + kolom yang sama.
        $normalised = [];
        foreach ($changes as $change) {
            if (!is_array($change)) {
                return $this->jsonError('Format perubahan tidak valid.', 422);
            }

            $unitId = isset($change['unit_id']) ? (int) $change['unit_id'] : 0;
            $field = isset($change['field']) ? (string) $change['field'] : '';
            $value = isset($change['value']) ? trim((string) $change['value']) : '';

            if ($unitId < 1 || !in_array($field, self::EDIT_FIELDS, true)) {
                return $this->jsonError('Unit atau kolom tidak valid.', 422);
            }
            if ($field !== 'catatan' && $value !== '' &&
                (!isset(self::OPTIONS[$field]) || !in_array($value, self::OPTIONS[$field], true))) {
                return $this->jsonError('Nilai tidak valid untuk kolom ' . $field . '.', 422);
            }

            $normalised[$unitId . ':' . $field] = [
                'unit_id' => $unitId,
                'field'   => $field,
                'value'   => $value,
            ];
        }

        $normalised = array_values($normalised);
        $unitIds = array_values(array_unique(array_column($normalised, 'unit_id')));
        $db = db_connect();

        // Pastikan semua unit yang dikirim memang milik sesi ini.
        $validIds = $db->table('audit_andon_detail')
            ->select('id')
            ->where('audit_id', $sessionId)
            ->whereIn('id', $unitIds)
            ->get()
            ->getResultArray();

        if (count($validIds) !== count($unitIds)) {
            return $this->jsonError('Ada unit yang tidak ditemukan dalam sesi ini.', 404);
        }

        // Buat satu UPDATE dengan CASE per kolom. Semua nilai telah divalidasi
        // dan nama kolom berasal dari daftar whitelist di atas.
        $byField = [];
        foreach ($normalised as $change) {
            $byField[$change['field']][] = $change;
        }

        $set = [];
        foreach ($byField as $field => $fieldChanges) {
            $case = 'CASE id ';
            foreach ($fieldChanges as $change) {
                $sqlValue = $change['value'] === '' ? 'NULL' : $db->escape($change['value']);
                $case .= 'WHEN ' . (int) $change['unit_id'] . ' THEN ' . $sqlValue . ' ';
            }
            $case .= 'ELSE ' . $db->protectIdentifiers($field) . ' END';
            $set[] = $db->protectIdentifiers($field) . ' = ' . $case;
        }

        $now = date('Y-m-d H:i:s');
        $set[] = $db->protectIdentifiers('updated_by') . ' = ' . (int) $this->userId();
        $set[] = $db->protectIdentifiers('updated_at') . ' = ' . $db->escape($now);
        $idList = implode(',', array_map('intval', $unitIds));

        $sql = 'UPDATE ' . $db->protectIdentifiers('audit_andon_detail')
            . ' SET ' . implode(', ', $set)
            . ' WHERE ' . $db->protectIdentifiers('audit_id') . ' = ' . $sessionId
            . ' AND ' . $db->protectIdentifiers('id') . ' IN (' . $idList . ')';

        $db->transBegin();
        $db->query($sql);
        $db->table('audit_andon')
            ->where('id', $sessionId)
            ->update(['updated_at' => $now]);

        if (!$db->transStatus()) {
            $db->transRollback();
            return $this->jsonError('Gagal menyimpan perubahan audit.', 500);
        }

        $db->transCommit();

        return $this->response->setJSON([
            'ok'    => true,
            'saved' => count($normalised),
            'csrf'  => csrf_hash(),
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

        // Pengisian massal untuk satu kolom: nilai dipilih dari tombol di header tabel.
        $value = trim((string) $this->request->getPost('value'));
        if ($value !== '') {
            if (!isset(self::OPTIONS[$field]) || !in_array($value, self::OPTIONS[$field], true)) {
                return redirect()->to($target)->with('error', 'Pilihan nilai tidak valid untuk kolom tersebut.');
            }

            $db = db_connect();
            $total = $db->table('audit_andon_detail')
                ->where('audit_id', $sessionId)
                ->where('line', $line)
                ->countAllResults();

            if ($total < 1) {
                return redirect()->to($target)->with('error', 'Tidak ada unit pada line ini.');
            }

            $db->table('audit_andon_detail')
                ->where('audit_id', $sessionId)
                ->where('line', $line)
                ->update([
                    $field       => $value,
                    'updated_by' => $this->userId(),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            if (!$auditModel->markSubmitted($sessionId)) {
                return redirect()->to($target)->with('error', 'Data tersimpan, tetapi status sesi gagal diperbarui.');
            }

            return redirect()->to($target)
                ->with('success', "{$field}: {$value} diterapkan ke {$total} unit.");
        }

        // Dukungan alur isi cepat lama tetap dipertahankan untuk kompatibilitas.
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

        if ($total > 0 && !$auditModel->markSubmitted($sessionId)) {
            return redirect()->to($target)->with('error', 'Data tersimpan, tetapi status sesi gagal diperbarui.');
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

        if (!$auditModel->markSubmitted($sessionId)) {
            return redirect()->to($target)
                ->with('error', 'Data tersimpan, tetapi status sesi gagal diperbarui.');
        }

        if ($this->request->getPost('action') === 'serahkan') {
            return redirect()->to($base)
                ->with('success', 'Data berhasil disimpan. Sesi tetap dapat diperbarui sampai akhir bulan.');
        }

        return redirect()->to($target)
            ->with('success', 'Data berhasil disimpan otomatis.');
    }

    /**
     * Hapus satu sesi audit beserta seluruh detail hasil pemeriksaannya.
     * Hanya admin yang dapat menghapus sesi.
     */
    public function deleteSession(int $sessionId)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('audit-andon/isi')
                ->with('error', 'Hanya admin yang bisa menghapus sesi audit.');
        }

        $auditModel = new AuditAndonModel();
        $session = $auditModel->find($sessionId);

        if (!$session) {
            return redirect()->to('audit-andon')
                ->with('error', 'Sesi audit tidak ditemukan.');
        }

        $db = db_connect();
        $db->transBegin();

        $db->table('audit_andon_detail')
            ->where('audit_id', $sessionId)
            ->delete();

        $deleted = $auditModel->delete($sessionId);

        if (!$deleted || !$db->transStatus()) {
            $db->transRollback();

            return redirect()->to('audit-andon?periode=' . rawurlencode($session['periode']))
                ->with('error', 'Sesi audit gagal dihapus. Tidak ada data yang diubah.');
        }

        $db->transCommit();

        return redirect()->to('audit-andon?periode=' . rawurlencode($session['periode']))
            ->with('success', 'Sesi audit beserta seluruh hasil pemeriksaannya berhasil dihapus.');
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
