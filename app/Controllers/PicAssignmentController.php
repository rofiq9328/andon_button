<?php

namespace App\Controllers;

use App\Models\PicAreaModel;

class PicAssignmentController extends BaseController
{
    /**
     * Daftar PIC audit dan rentang line masing-masing.
     */
    public function index()
    {
        $pics = db_connect()->table('users')
            ->select('id, username, full_name')
            ->where('role', 'pic_audit')
            ->orderBy('full_name', 'ASC')
            ->get()
            ->getResultArray();

        $ranges = [];
        $assignedByLine = [];
        $picNames = [];

        foreach ($pics as $pic) {
            $picNames[(int) $pic['id']] = $pic['full_name'] ?: $pic['username'];
        }

        foreach ((new PicAreaModel())->orderBy('line_from', 'ASC')->findAll() as $assignment) {
            $userId = (int) $assignment['user_id'];
            $ranges[$userId][] = $assignment;

            // Mendukung data lama berbentuk rentang dan data baru per line.
            for ($line = (int) $assignment['line_from']; $line <= (int) $assignment['line_to']; $line++) {
                $assignedByLine[$line] = $userId;
            }
        }

        $lineRows = db_connect()->table('audit_andon_units')
            ->select('line')
            ->distinct()
            ->orderBy('line', 'ASC')
            ->get()
            ->getResultArray();

        $lineOptions = array_map('intval', array_column($lineRows, 'line'));

        return view('audit/penugasan', [
            'pics'            => $pics,
            'ranges'          => $ranges,
            'lineOptions'     => $lineOptions,
            'assignedByLine'  => $assignedByLine,
            'picNames'        => $picNames,
        ]);
    }

    /**
     * Tambah satu atau beberapa line tertentu ke seorang PIC.
     * Setiap pilihan disimpan sebagai satu baris (line_from = line_to).
     */
    public function store()
    {
        $userId = (int) $this->request->getPost('user_id');
        $rawLines = $this->request->getPost('lines');

        if (!is_array($rawLines) || empty($rawLines)) {
            return redirect()->to('/audit-andon/penugasan')
                ->withInput()
                ->with('error', 'Pilih minimal satu line.');
        }

        $pic = db_connect()->table('users')
            ->select('id, role')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        if (!$pic || $pic['role'] !== 'pic_audit') {
            return redirect()->to('/audit-andon/penugasan')
                ->withInput()
                ->with('error', 'Pilih user dengan role PIC Audit.');
        }

        $lineRows = db_connect()->table('audit_andon_units')
            ->select('line')
            ->distinct()
            ->get()
            ->getResultArray();
        $availableLines = array_map('intval', array_column($lineRows, 'line'));

        $selectedLines = [];
        foreach ($rawLines as $rawLine) {
            $line = filter_var($rawLine, FILTER_VALIDATE_INT);
            if ($line === false || $line < 1 || !in_array((int) $line, $availableLines, true)) {
                return redirect()->to('/audit-andon/penugasan')
                    ->withInput()
                    ->with('error', 'Ada pilihan line yang tidak valid.');
            }

            $selectedLines[(int) $line] = (int) $line;
        }

        $selectedLines = array_values($selectedLines);
        sort($selectedLines, SORT_NUMERIC);

        $db = db_connect();
        $picAreaModel = new PicAreaModel();
        $added = 0;
        $alreadyAssigned = 0;

        $db->transBegin();

        foreach ($selectedLines as $line) {
            $existing = $db->table('pic_area')
                ->select('user_id')
                ->where('line_from <=', $line)
                ->where('line_to >=', $line)
                ->get()
                ->getResultArray();

            $assignedToOther = false;
            $assignedToThisPic = false;

            foreach ($existing as $assignment) {
                if ((int) $assignment['user_id'] === $userId) {
                    $assignedToThisPic = true;
                } else {
                    $assignedToOther = true;
                    break;
                }
            }

            if ($assignedToOther) {
                $db->transRollback();

                return redirect()->to('/audit-andon/penugasan')
                    ->withInput()
                    ->with('error', "LINE {$line} sudah ditugaskan ke PIC lain.");
            }

            if ($assignedToThisPic) {
                $alreadyAssigned++;
                continue;
            }

            $inserted = $picAreaModel->insert([
                'user_id'   => $userId,
                'line_from' => $line,
                'line_to'   => $line,
            ]);

            if ($inserted === false) {
                $db->transRollback();

                return redirect()->to('/audit-andon/penugasan')
                    ->withInput()
                    ->with('error', 'Gagal menyimpan penugasan line.');
            }

            $added++;
        }

        if (!$db->transStatus()) {
            $db->transRollback();

            return redirect()->to('/audit-andon/penugasan')
                ->withInput()
                ->with('error', 'Penugasan line gagal disimpan.');
        }

        $db->transCommit();

        if ($added === 0) {
            $message = 'Semua line yang dipilih sudah ditugaskan ke PIC ini.';
        } else {
            $message = "{$added} line berhasil ditugaskan.";
        }

        if ($alreadyAssigned > 0) {
            $message .= " {$alreadyAssigned} line sudah menjadi penugasan PIC ini.";
        }

        return redirect()->to('/audit-andon/penugasan')
            ->with('success', $message);
    }

    /**
     * Hapus satu penugasan line (atau rentang dari data lama).
     */
    public function delete(int $id)
    {
        (new PicAreaModel())->delete($id);

        return redirect()->to('/audit-andon/penugasan')
            ->with('success', 'Penugasan dihapus.');
    }

}
