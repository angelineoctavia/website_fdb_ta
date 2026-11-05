<?php

namespace App\Support;

class Phone
{
    /**
     * Format penyimpanan: 628xxxxxxxxx (wajib diawali 628).
     * Karakter non-angka (spasi, strip, titik, kurung, +) dibersihkan otomatis.
     * Input berawalan 08 atau format lain dianggap tidak valid (null).
     */
    public static function normalize(?string $raw): ?string
    {
        // 1. Kalau kosong, kembalikan null
        if (empty($raw)) {
            return null;
        }

        // 2. Bersihkan semua karakter non-angka
        $clean = preg_replace('/[^0-9]/', '', $raw);

        // 3. Validasi: wajib diawali 628, total panjang 11 sampai 15 digit
        $length = strlen($clean);

        if (str_starts_with($clean, '628') && $length >= 11 && $length <= 15) {
            return $clean;
        }

        return null;
    }
}