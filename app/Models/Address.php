<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    public const COL = [
        'street'     => 'ADDRESSES_FULL',         // Alamat rumah
        'district'   => 'ADDRESSES_DISTRICT',     // Kecamatan
        'city'       => 'ADDRESSES_CITY',         // Kota / Kabupaten
        'province'   => 'ADDRESSES_PROVINCE',     // Provinsi (kolom baru)
        'postal'     => 'ADDRESSES_POSTAL_CODE',  // Kode pos
        'detail'     => 'ADDRESSES_NOTES',        // Detail lainnya
        'label' => 'ADDRESSES_LABEL',
        'status_del' => 'ADDRESSES_STATUS_DEL',
    ];

    protected $table = 'addresses';
    protected $primaryKey = 'ADDRESSES_ID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    /** Alamat aktif milik user (satu user = satu alamat). */
    public static function forUser(string $userId): ?self
    {
        return static::where('USERS_ID', $userId)->where(self::COL['status_del'], 0)->first();
    }

    public function field(string $key): ?string
    {
        $v = $this->{self::COL[$key]};

        return $v === null ? null : (string) $v;
    }

    public function isComplete(): bool
    {
        foreach (['street', 'district', 'city', 'province', 'postal'] as $k) {
            if (! filled($this->field($k))) {
                return false;
            }
        }

        return true;
    }

    /** Teks alamat untuk disimpan di transactions.TRANSACTIONS_SHIPPING_ADDRESS. */
    public function toText(): string
    {
        $lines = [
            $this->field('street'),
            'Kec. ' . $this->field('district') . ', ' . $this->field('city'),
            $this->field('province') . ' ' . $this->field('postal'),
        ];
        if (filled($this->field('detail'))) {
            $lines[] = 'Detail: ' . $this->field('detail');
        }

        return implode("\n", $lines);
    }
}