<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePendataanTables extends Migration
{
    private array $statements = [
<<<'SQL'
CREATE TABLE factory (
  id_factory INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_factory VARCHAR(10) NOT NULL,
  nama_factory VARCHAR(100) NOT NULL,
  lokasi VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Aktif, 0=Tidak aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_factory),
  UNIQUE KEY uq_factory_kode (kode_factory),
  KEY idx_factory_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE kategori_barang (
  id_kategori INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_kategori VARCHAR(20) NOT NULL,
  nama_kategori VARCHAR(100) NOT NULL,
  deskripsi TEXT DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_kategori),
  UNIQUE KEY uq_kategori_kode (kode_kategori),
  KEY idx_kategori_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE satuan (
  id_satuan INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_satuan VARCHAR(10) NOT NULL,
  nama_satuan VARCHAR(30) NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_satuan),
  UNIQUE KEY uq_satuan_kode (kode_satuan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE area (
  id_area INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_factory INT UNSIGNED DEFAULT NULL,
  parent_area_id INT UNSIGNED DEFAULT NULL,
  cakupan_area ENUM('PUSAT','FACTORY') NOT NULL DEFAULT 'FACTORY',
  kode_area VARCHAR(30) NOT NULL,
  nama_area VARCHAR(100) NOT NULL,
  jenis_area ENUM('LINE','OFFICE','WAREHOUSE','MAINTENANCE','DEPARTMENT','OTHER') NOT NULL DEFAULT 'OTHER',
  deskripsi TEXT DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_area),
  UNIQUE KEY uq_area_kode (kode_area),
  KEY idx_area_factory (id_factory),
  KEY idx_area_parent (parent_area_id),
  KEY idx_area_jenis_status (jenis_area, status),
  CONSTRAINT fk_area_factory FOREIGN KEY (id_factory) REFERENCES factory (id_factory) ON UPDATE CASCADE,
  CONSTRAINT fk_area_parent FOREIGN KEY (parent_area_id) REFERENCES area (id_area) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE barang (
  id_barang INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_barang VARCHAR(30) NOT NULL,
  nama_barang VARCHAR(150) NOT NULL,
  id_kategori INT UNSIGNED NOT NULL,
  id_satuan INT UNSIGNED NOT NULL,
  merek VARCHAR(100) DEFAULT NULL,
  model_tipe VARCHAR(100) DEFAULT NULL,
  spesifikasi TEXT DEFAULT NULL,
  foto_barang VARCHAR(255) DEFAULT NULL,
  deskripsi TEXT DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED DEFAULT NULL,
  updated_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_barang),
  UNIQUE KEY uq_barang_kode (kode_barang),
  KEY idx_barang_kategori (id_kategori),
  KEY idx_barang_satuan (id_satuan),
  KEY idx_barang_nama (nama_barang),
  KEY idx_barang_status (status),
  CONSTRAINT fk_barang_kategori FOREIGN KEY (id_kategori) REFERENCES kategori_barang (id_kategori) ON UPDATE CASCADE,
  CONSTRAINT fk_barang_satuan FOREIGN KEY (id_satuan) REFERENCES satuan (id_satuan) ON UPDATE CASCADE,
  CONSTRAINT fk_barang_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_barang_updated_by FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE aturan_stok_area (
  id_aturan_stok INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_area INT UNSIGNED NOT NULL,
  id_barang INT UNSIGNED NOT NULL,
  stok_minimum DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  stok_ideal DECIMAL(14,2) DEFAULT NULL,
  stok_maksimum DECIMAL(14,2) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED DEFAULT NULL,
  updated_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_aturan_stok),
  UNIQUE KEY uq_aturan_stok_area_barang (id_area, id_barang),
  KEY idx_aturan_stok_status (status),
  CONSTRAINT fk_aturan_stok_area FOREIGN KEY (id_area) REFERENCES area (id_area) ON UPDATE CASCADE,
  CONSTRAINT fk_aturan_stok_barang FOREIGN KEY (id_barang) REFERENCES barang (id_barang) ON UPDATE CASCADE,
  CONSTRAINT fk_aturan_stok_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_aturan_stok_updated_by FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE standar_barang_area (
  id_standar INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_area INT UNSIGNED NOT NULL,
  id_barang INT UNSIGNED NOT NULL,
  jumlah_standar DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  berlaku_mulai DATE NOT NULL,
  berlaku_sampai DATE DEFAULT NULL,
  keterangan TEXT DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_standar),
  UNIQUE KEY uq_standar_area_barang_mulai (id_area, id_barang, berlaku_mulai),
  KEY idx_standar_area_status (id_area, status),
  KEY idx_standar_barang (id_barang),
  CONSTRAINT fk_standar_area FOREIGN KEY (id_area) REFERENCES area (id_area) ON UPDATE CASCADE,
  CONSTRAINT fk_standar_barang FOREIGN KEY (id_barang) REFERENCES barang (id_barang) ON UPDATE CASCADE,
  CONSTRAINT fk_standar_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE user_area (
  id_user_area INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user INT UNSIGNED NOT NULL,
  id_area INT UNSIGNED NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  assigned_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user_area),
  UNIQUE KEY uq_user_area_mulai (id_user, id_area, tanggal_mulai),
  KEY idx_user_area_area_status (id_area, status),
  KEY idx_user_area_user_status (id_user, status),
  KEY fk_user_area_assigned_by (assigned_by),
  CONSTRAINT fk_user_area_area FOREIGN KEY (id_area) REFERENCES area (id_area) ON UPDATE CASCADE,
  CONSTRAINT fk_user_area_user FOREIGN KEY (id_user) REFERENCES users (id) ON UPDATE CASCADE,
  CONSTRAINT fk_user_area_assigned_by FOREIGN KEY (assigned_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE transaksi_barang (
  id_transaksi BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomor_transaksi VARCHAR(40) NOT NULL,
  jenis_transaksi ENUM('SALDO_AWAL','PENERIMAAN','PENEMPATAN','PENGEMBALIAN','TRANSFER','PENGELUARAN','PERUBAHAN_KONDISI','PENYESUAIAN','PENGHAPUSAN') NOT NULL,
  alur_persetujuan ENUM('LANGSUNG','APPROVAL') NOT NULL DEFAULT 'APPROVAL',
  tanggal_transaksi DATETIME NOT NULL,
  dibuat_oleh INT UNSIGNED NOT NULL,
  disetujui_oleh INT UNSIGNED DEFAULT NULL,
  diproses_oleh INT UNSIGNED DEFAULT NULL,
  diposting_oleh INT UNSIGNED DEFAULT NULL,
  status_transaksi ENUM('DRAFT','DIAJUKAN','DISETUJUI','DITOLAK','DIPOSTING','DIBATALKAN') NOT NULL DEFAULT 'DRAFT',
  alasan_penolakan TEXT DEFAULT NULL,
  keterangan TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_transaksi),
  UNIQUE KEY uq_transaksi_nomor (nomor_transaksi),
  KEY idx_transaksi_tanggal (tanggal_transaksi),
  KEY idx_transaksi_jenis_status (jenis_transaksi, status_transaksi),
  KEY idx_transaksi_pembuat (dibuat_oleh),
  KEY fk_transaksi_disetujui_oleh (disetujui_oleh),
  KEY fk_transaksi_diproses_oleh (diproses_oleh),
  KEY fk_transaksi_diposting_oleh (diposting_oleh),
  CONSTRAINT fk_transaksi_dibuat_oleh FOREIGN KEY (dibuat_oleh) REFERENCES users (id) ON UPDATE CASCADE,
  CONSTRAINT fk_transaksi_disetujui_oleh FOREIGN KEY (disetujui_oleh) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_transaksi_diproses_oleh FOREIGN KEY (diproses_oleh) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_transaksi_diposting_oleh FOREIGN KEY (diposting_oleh) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE detail_transaksi_barang (
  id_detail BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_transaksi BIGINT UNSIGNED NOT NULL,
  id_barang INT UNSIGNED NOT NULL,
  id_area_asal INT UNSIGNED DEFAULT NULL,
  id_area_tujuan INT UNSIGNED DEFAULT NULL,
  kondisi_asal ENUM('BAIK','RUSAK','DALAM_PERBAIKAN','TIDAK_LAYAK') DEFAULT NULL,
  kondisi_tujuan ENUM('BAIK','RUSAK','DALAM_PERBAIKAN','TIDAK_LAYAK') DEFAULT NULL,
  jumlah DECIMAL(14,2) NOT NULL,
  keterangan TEXT DEFAULT NULL,
  PRIMARY KEY (id_detail),
  KEY idx_detail_transaksi (id_transaksi),
  KEY idx_detail_barang (id_barang),
  KEY idx_detail_area_asal (id_area_asal),
  KEY idx_detail_area_tujuan (id_area_tujuan),
  CONSTRAINT fk_detail_transaksi FOREIGN KEY (id_transaksi) REFERENCES transaksi_barang (id_transaksi) ON UPDATE CASCADE,
  CONSTRAINT fk_detail_barang FOREIGN KEY (id_barang) REFERENCES barang (id_barang) ON UPDATE CASCADE,
  CONSTRAINT fk_detail_area_asal FOREIGN KEY (id_area_asal) REFERENCES area (id_area) ON UPDATE CASCADE,
  CONSTRAINT fk_detail_area_tujuan FOREIGN KEY (id_area_tujuan) REFERENCES area (id_area) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
CREATE TABLE saldo_barang_area (
  id_saldo BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_area INT UNSIGNED NOT NULL,
  id_barang INT UNSIGNED NOT NULL,
  kondisi ENUM('BAIK','RUSAK','DALAM_PERBAIKAN','TIDAK_LAYAK') NOT NULL DEFAULT 'BAIK',
  jumlah DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_saldo),
  UNIQUE KEY uq_saldo_area_barang_kondisi (id_area, id_barang, kondisi),
  KEY idx_saldo_barang_area (id_barang, id_area),
  CONSTRAINT fk_saldo_area FOREIGN KEY (id_area) REFERENCES area (id_area) ON UPDATE CASCADE,
  CONSTRAINT fk_saldo_barang FOREIGN KEY (id_barang) REFERENCES barang (id_barang) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
<<<'SQL'
INSERT INTO kategori_barang (id_kategori, kode_kategori, nama_kategori, status) VALUES
(1, 'KAT-MSN', 'Mesin Produksi', 1),
(2, 'KAT-FUR', 'Furniture', 1),
(3, 'KAT-IT', 'Perangkat IT', 1),
(4, 'KAT-ALAT', 'Peralatan Kerja', 1),
(5, 'KAT-ELEK', 'Elektronik', 1),
(6, 'KAT-SPARE', 'Spare Part', 1)
SQL,
<<<'SQL'
INSERT INTO satuan (id_satuan, kode_satuan, nama_satuan, status) VALUES
(1, 'PCS', 'Pieces', 1),
(2, 'UNIT', 'Unit', 1),
(3, 'SET', 'Set', 1),
(4, 'BOX', 'Box', 1)
SQL,
    ];

    public function up()
    {
        foreach ($this->statements as $sql) {
            $this->db->query($sql);
        }
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'detail_transaksi_barang', 'saldo_barang_area', 'transaksi_barang', 'user_area',
            'standar_barang_area', 'aturan_stok_area', 'barang', 'satuan', 'kategori_barang',
            'area', 'factory',
        ] as $table) {
            $this->forge->dropTable($table, true);
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }
}
