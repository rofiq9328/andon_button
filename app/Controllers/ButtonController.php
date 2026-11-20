<?php

namespace App\Controllers;

use App\Models\ButtonModel;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ButtonController extends BaseController
{
    public function updateView()
    {
        $buttonModel = new ButtonModel();

        // Ambil semua data button, urutkan berdasarkan ID (opsional)
        $data = [
            'buttons' => $buttonModel->orderBy('id', 'ASC')->findAll()
        ];

        // Kirim data ke view
        return view('andon/update_button', $data);
    }

    public function processExcel()
    {
        // 1. Validasi File Excel
        $validation = \Config\Services::validation();
        $valid = $this->validate([
            'file_excel' => [
                'rules' => 'uploaded[file_excel]|ext_in[file_excel,xls,xlsx]',
                'errors' => [
                    'uploaded' => 'Silakan pilih file Excel terlebih dahulu.',
                    'ext_in'   => 'File harus berekstensi .xls atau .xlsx'
                ]
            ]
        ]);

        if (!$valid) {
            session()->setFlashdata('error', $validation->getError('file_excel'));
            return redirect()->back();
        }

        $file = $this->request->getFile('file_excel');
        if ($file->isValid() && !$file->hasMoved()) {
            $ext = $file->getClientExtension();
            $reader = ($ext == 'xls') ? new \PhpOffice\PhpSpreadsheet\Reader\Xls() : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

            $spreadsheet = $reader->load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $buttonModel = new ButtonModel();
            $db = \Config\Database::connect();

            $rows = [];
            foreach ($sheetData as $key => $row) {
                if ($key == 0) continue;

                $id      = trim($row[0] ?? '');
                $factory = trim($row[1] ?? '');
                $line    = trim($row[2] ?? '');
                $worker  = trim($row[3] ?? '');

                if (empty($id)) continue;

                $workerClean = $buttonModel->cleanWorkerNumber($worker);
                if (empty($line) || empty($workerClean)) continue;

                $rows[] = [
                    'id'      => $id,
                    'factory' => $factory,
                    'line'    => $line,
                    'worker'  => $workerClean,
                ];
            }

            $slotOwner = [];
            $cleanRows = [];
            $bariskonflik = [];
            foreach ($rows as $row) {
                $slotKey = $row['line'] . '|' . $row['worker'];

                if (isset($slotOwner[$slotKey]) && $slotOwner[$slotKey] !== $row['id']) {
                    $bariskonflik[] = "{$row['id']} vs {$slotOwner[$slotKey]} (line {$row['line']} / worker {$row['worker']})";
                    continue;
                }

                $slotOwner[$slotKey] = $row['id'];
                $cleanRows[] = $row;
            }

            $targetById = [];
            foreach ($cleanRows as $row) {
                $targetById[$row['id']] = $row['line'] . '|' . $row['worker'];
            }

            $jumlahBerhasil = 0;
            $modelErrors = [];
            $dilepas = [];

            $db->transStart();

            // Slot yang dibutuhkan Excel: pemilik lamanya dilepas dari worker-nya (data tetap ada)
            $toClear = [];
            foreach ($cleanRows as $row) {
                $occupant = $buttonModel->where('line', $row['line'])
                    ->where('worker', $row['worker'])
                    ->first();

                if ($occupant && $occupant['id'] !== $row['id']) {
                    $toClear[$occupant['id']] = true;
                    if (!isset($targetById[$occupant['id']])) {
                        $dilepas[] = $occupant['id'];
                    }
                }
            }
            foreach (array_keys($toClear) as $idToClear) {
                if ($buttonModel->update($idToClear, ['worker' => null]) === false) {
                    $modelErrors[] = "kosongkan worker ID {$idToClear}: " . implode(' ', $buttonModel->errors());
                }
            }

            foreach ($cleanRows as $data) {
                $id = $data['id'];
                $existingById = $buttonModel->find($id);
                $ok = false;

                if ($existingById) {
                    $update = $data;
                    unset($update['id']);
                    $ok = $buttonModel->update($id, $update);
                } else {
                    $existingBySlot = $buttonModel->where('line', $data['line'])
                        ->where('worker', $data['worker'])
                        ->first();

                    if ($existingBySlot) {
                        $ok = $buttonModel->update($existingBySlot['id'], $data);
                    } else {
                        $ok = $buttonModel->insert($data);
                    }
                }

                if ($ok === false) {
                    $modelErrors[] = "ID {$id}: " . implode(' ', $buttonModel->errors());
                    continue;
                }

                $jumlahBerhasil++;
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                $dbError = $db->error();
                log_message('error', 'Import Excel button gagal di database: ' . json_encode($dbError));
                session()->setFlashdata('error', 'Gagal menyimpan data ke database: ' . ($dbError['message'] ?? 'penyebab tidak diketahui'));
            } elseif ($modelErrors) {
                log_message('error', 'Import Excel button, data ditolak model: ' . implode(' | ', $modelErrors));
                session()->setFlashdata('error', "{$jumlahBerhasil} data tersimpan, " . count($modelErrors) . ' data ditolak: ' . implode(' | ', array_slice($modelErrors, 0, 3)));
            } elseif ($bariskonflik || $dilepas) {
                $parts = ["$jumlahBerhasil data berhasil diupdate dan disimpan."];
                if ($bariskonflik) {
                    $parts[] = count($bariskonflik) . ' baris dilewati karena ada 2 ID dalam Line & Worker yang sama, perbaiki file Excel Anda.';
                }
                if ($dilepas) {
                    $parts[] = count($dilepas) . ' ID yang tidak ada di file dilepas dari slot line/worker karena slotnya dipakai ID dari Excel.';
                }
                $detail = $bariskonflik;
                foreach ($dilepas as $idLepas) {
                    $detail[] = "ID {$idLepas} dilepas dari slotnya (worker dikosongkan)";
                }
                session()->setFlashdata('warning', implode(' ', $parts));
                session()->setFlashdata('warningDetail', $detail);
            } else {
                session()->setFlashdata('success', "$jumlahBerhasil Data Button berhasil diupdate dan disimpan.");
            }

            return redirect()->back();
        }

        session()->setFlashdata('error', 'Gagal memproses file.');
        return redirect()->back();
    }

    public function clearAll()
    {
        // Konfirmasi sudah dilakukan lewat SweetAlert di halaman Master Button

        $db = \Config\Database::connect();
        $jumlah = $db->table('button_id')->countAllResults();

        try {
            $db->table('button_id')->truncate();
        } catch (\Throwable $e) {
            log_message('error', 'Kosongkan data button gagal: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal mengosongkan data button: ' . $e->getMessage());
            return redirect()->back();
        }

        log_message('warning', 'Semua data button dikosongkan oleh ' . session()->get('username') . " ({$jumlah} baris)");
        session()->setFlashdata('success', "{$jumlah} data button berhasil dikosongkan. Silakan upload file Excel baru.");
        return redirect()->back();
    }

    public function delete($id)
    {
        $buttonModel = new ButtonModel();

        if ($buttonModel->find($id)) {
            $buttonModel->delete($id);
            session()->setFlashdata('success', "Data Button dengan ID $id berhasil dihapus.");
        } else {
            session()->setFlashdata('error', "Data tidak ditemukan.");
        }

        return redirect()->back();
    }


    public function updateSingle()
    {
        $buttonModel = new ButtonModel();

        $id      = $this->request->getPost('id');
        $factory = $this->request->getPost('factory');
        $line    = $this->request->getPost('line');
        $worker  = $buttonModel->cleanWorkerNumber($this->request->getPost('worker'));

        // Cek bentrok slot (line & worker) dengan ID lain
        $existing = $buttonModel->where('line', $line)
            ->where('worker', $worker)
            ->first();

        // Jika slot sudah ada yang pakai, dan ID-nya bukan ID yang sedang diedit
        if ($existing && $existing['id'] !== $id) {
            session()->setFlashdata('error', "Gagal Edit: Slot Line $line / Worker $worker sudah dipakai oleh ID {$existing['id']}.");
            return redirect()->back();
        }

        // Jalankan Update
        $buttonModel->update($id, [
            'factory' => $factory,
            'line'    => $line,
            'worker'  => $worker
        ]);

        session()->setFlashdata('success', "Data Button ID $id berhasil diperbarui.");
        return redirect()->back();
    }
}
