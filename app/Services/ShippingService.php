<?php

namespace App\Services;

class ShippingService
{
    /** Daftar provinsi (untuk dropdown). */
    public function provinces(): array
    {
        return array_keys(config('shipping.provinces'));
    }

    /** Saran nama kota untuk <datalist> (kota yang punya tarif khusus). */
    public function cityOptions(): array
    {
        $out = [];
        foreach (config('shipping.cities') as $cities) {
            foreach (array_keys($cities) as $c) {
                $out[] = ucwords($c);
            }
        }
        sort($out);

        return $out;
    }

    public function normalizeCity(?string $city): string
    {
        $c = mb_strtolower(trim((string) $city));
        $c = preg_replace('/^(kota administrasi|kota|kabupaten|kab\.?)\s+/u', '', $c);

        return trim(preg_replace('/\s+/', ' ', $c));
    }

    /** Tarif per kg untuk provinsi + kota. */
    public function ratePerKg(?string $province, ?string $city): int
    {
        $province = (string) $province;
        $key      = $this->normalizeCity($city);
        $override = config("shipping.cities.$province", []);

        if ($key !== '' && isset($override[$key])) {
            return (int) $override[$key];
        }

        return (int) (config('shipping.provinces')[$province] ?? config('shipping.default_rate'));
    }

    /** Berat tagihan (kg, dibulatkan ke atas, minimal 1). */
    public function billableKg(int $qty): int
    {
        return max(1, (int) ceil($qty * (float) config('shipping.item_weight_kg')));
    }

    public function cost(?string $province, ?string $city, int $qty): int
    {
        return $this->ratePerKg($province, $city) * $this->billableKg($qty);
    }
}