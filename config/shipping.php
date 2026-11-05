<?php

/*
 * ONGKIR MANUAL, ASAL SELALU SURABAYA. Satu harga untuk semua kurir (JNE / J&T / dll).
 * Rumus: tarif per kg (provinsi, atau kota kalau ada di 'cities')  x  berat dibulatkan ke atas (kg).
 * Berat = jumlah barang x item_weight_kg (minimal 1 kg).
 *
 * Angka di bawah ESTIMASI per zona, dikalibrasi dari titik acuan:
 *   Surabaya -> Jakarta sekitar Rp15.000-17.000/kg, Surabaya -> Malang sekitar Rp9.000/kg.
 * Cocokkan dengan jne.co.id / aplikasi kurir kalau mau lebih presisi, lalu ubah angkanya di sini saja.
 */
return [
    'origin'         => 'Surabaya',
    'item_weight_kg' => 0.5,      // anggapan: 1 baju = 0,5 kg
    'default_rate'   => 60000,    // kalau provinsi tidak dikenal

    // Rp per kg, per provinsi
    'provinces' => [
        // Jawa & Bali
        'Jawa Timur'                 => 11000,
        'Jawa Tengah'                => 14000,
        'DI Yogyakarta'              => 14000,
        'Jawa Barat'                 => 17000,
        'DKI Jakarta'                => 17000,
        'Banten'                     => 18000,
        'Bali'                       => 16000,
        // Nusa Tenggara
        'Nusa Tenggara Barat'        => 22000,
        'Nusa Tenggara Timur'        => 30000,
        // Sumatera
        'Lampung'                    => 22000,
        'Sumatera Selatan'           => 28000,
        'Jambi'                      => 28000,
        'Bengkulu'                   => 28000,
        'Bangka Belitung'            => 30000,
        'Sumatera Barat'             => 30000,
        'Riau'                       => 30000,
        'Kepulauan Riau'             => 30000,
        'Sumatera Utara'             => 32000,
        'Aceh'                       => 36000,
        // Kalimantan
        'Kalimantan Selatan'         => 24000,
        'Kalimantan Tengah'          => 30000,
        'Kalimantan Timur'           => 30000,
        'Kalimantan Barat'           => 30000,
        'Kalimantan Utara'           => 34000,
        // Sulawesi
        'Sulawesi Selatan'           => 26000,
        'Sulawesi Tengah'            => 32000,
        'Sulawesi Tenggara'          => 32000,
        'Sulawesi Barat'             => 32000,
        'Sulawesi Utara'             => 36000,
        'Gorontalo'                  => 36000,
        // Maluku
        'Maluku'                     => 45000,
        'Maluku Utara'               => 48000,
        // Papua
        'Papua Barat'                => 55000,
        'Papua Barat Daya'           => 55000,
        'Papua'                      => 65000,
        'Papua Tengah'               => 65000,
        'Papua Selatan'              => 65000,
        'Papua Pegunungan'           => 70000,
    ],

    // Override tarif per kota (kunci huruf kecil, tanpa "Kota"/"Kab."). Hanya dipakai bila provinsinya cocok.
    // Kota yang tidak ada di sini otomatis pakai tarif provinsi.
    'cities' => [
        'Jawa Timur' => [
            'surabaya'    => 8000,
            'sidoarjo'    => 9000,
            'gresik'      => 9000,
            'mojokerto'   => 9000,
            'pasuruan'    => 9000,
            'malang'      => 9000,
            'batu'        => 9000,
            'bangkalan'   => 10000,
            'jombang'     => 10000,
            'lamongan'    => 10000,
            'probolinggo' => 10000,
            'kediri'      => 10000,
            'madiun'      => 11000,
            'blitar'      => 11000,
            'tuban'       => 11000,
            'bojonegoro'  => 11000,
            'sampang'     => 11000,
            'ngawi'       => 12000,
            'jember'      => 12000,
            'pamekasan'   => 12000,
            'banyuwangi'  => 13000,
            'situbondo'   => 13000,
            'sumenep'     => 14000,
        ],
    ],
];