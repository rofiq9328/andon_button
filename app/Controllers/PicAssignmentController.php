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
        foreach ((new PicAreaModel())->orderBy('line_from', 'ASC')->findAll() as $r) {
            $ranges[(int) $r['user_id']][] = $r;
        }

        return view('audit/penugasan', [
            'pics'   => $pics,
            'ranges' => $ranges,
        ]);
    }

    /**
     * Tambah rentang line ke seorang PIC.
     */
    public function store()
    {
        $userId   = (int) $this->request->getPost('user_id');
        $lineFrom = (int) $this->request->getPost('line_from');
        $lineTo   = (int) $this->request->getPost('line_to');

        $error = $this->validateRange($userId, $lineFrom, $lineTo);
        if ($error !== null) {
            return redirect()->to('/audit-andon/penugasan')
                ->withInput()
                ->with('error', $error);
        }

        (new PicAreaModel())->insert([
            'user_id'   => $userId,
            'line_from' => $lineFrom,
            'line_to'   => $lineTo,
        ]);

        return redirect()->to('/audit-andon/penugasan')
            ->with('success', "Line {$lineFrom} sampai {$lineTo} ditugaskan.");
    }

    /**
     * Hapus satu rentang penugasan.
     */
    public function delete(int $id)
    {
        (new PicAreaModel())->delete($id);

        return redirect()->to('/audit-andon/penugasan')
            ->with('success', 'Penugasan dihapus.');
    }

    /**
     * Cek user, rentang, keberadaan unit, dan tabrakan dengan PIC lain.
     * Mengembalikan pesan error, atau null kalau valid.
     */
    private function validateRange(int $userId, int $lineFrom, int $lineTo): ?string
    {
        $pic = db_connect()->table('users')
            ->select('id, role')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        if (!$pic || $pic['role'] !== 'pic_audit') {
            return 'Pilih user dengan role PIC Audit.';
        }

        if ($lineFrom < 1 || $lineTo < $lineFrom) {
            return 'Rentang line tidak valid. Line "sampai" harus sama atau lebih besar dari line "dari".';
        }

        $unitCount = db_connect()->table('audit_andon_units')
            ->where('line >=', $lineFrom)
            ->where('line <=', $lineTo)
            ->countAllResults();

        if ($unitCount === 0) {
            return 'Tidak ada unit di rentang line tersebut.';
        }

        if ((new PicAreaModel())->hasOverlap($lineFrom, $lineTo)) {
            return 'Rentang line bertabrakan dengan penugasan PIC lain.';
        }

        return null;
    }
}
