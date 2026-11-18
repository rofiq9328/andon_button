<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AndonConfig extends BaseConfig
{
    /**
     * Konfigurasi Socket Server untuk Perintah Clear
     */
    public array $factory_config = [
        '1' => [
            'host' => '192.168.10.254',
            'port' => 2112
        ],
        '2' => [
            'host' => '192.168.10.252',
            'port' => 2112
        ]
    ];

    /**
     * Array ini dibiarkan kosong, karena akan diisi otomatis 
     * oleh fungsi __construct() di bawah.
     */
    public array $lines = [];

    public function __construct()
    {
        parent::__construct();

        // 1. Generate Building B (Line 1 sampai 33)
        // Range IP: 192.168.10.201 - 192.168.10.233
        for ($i = 1; $i <= 33; $i++) {
            $ip_suffix = 200 + $i; // Menghasilkan 201 s/d 233
            
            $this->lines[] = [
                'line_number' => $i,
                'building'    => 'Building B',
                'factory'     => '1',
                'ip'          => "192.168.10.{$ip_suffix}",
                'port'        => 1880
            ];
        }

        // 2. Generate Building A (Line 34 sampai 77)
        // Range IP: 192.168.30.204 sampai selesai (247)
        $ip_suffix_a = 204;
        for ($i = 34; $i <= 77; $i++) {
            $this->lines[] = [
                'line_number' => $i,
                'building'    => 'Building A',
                'factory'     => '2',
                'ip'          => "192.168.20.{$ip_suffix_a}",
                'port'        => 1880
            ];
            
            // Tambahkan 1 untuk IP berikutnya
            $ip_suffix_a++; 
        }
    }
}