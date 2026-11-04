<?php

namespace App\Support;

class Phone
{
    /**
     * Format penyimpanan: 628xxxxxxxxx (wajib diawali 628).
     * Menerima input: 628xx... (spasi, tanda strip, titik, dan kurung otomatis dibersihkan).
     * Jika user memasukkan 08 atau format lain, akan dianggap tidak valid (null).
     */
    public static function normalize(?string $raw): ?string
    {
        // 1. Kalau kosong, kembalikan null
        if (empty($raw)) {
            return null;
        }

        // 2. Bersihkan karakter non-angka (spasi, strip, tanda kurung, titik, dll)
        $clean = preg_replace('/[^0-9]/', '', $raw);

        // 3. Hitung total panjang angka
        $length = strlen($clean);

        // 4. Validasi ketat:
        //    - Wajib diawali dengan '628'
        //    - Total panjang digit harus antara 11 sampai 14
        $isStartWith628 = str_starts_with($clean, '628');
        $isValidLength  = ($length >= 11 && $length <= 15);

        if ($isStartWith628 && $isValidLength) {
            return $clean; // Lolos dan valid!
        }

        return null; // Ditolak karena tidak pakai awalan 628
    }
}