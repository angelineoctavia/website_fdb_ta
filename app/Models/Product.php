<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    // ==== ASUMSI NAMA KOLOM (ubah di sini kalau beda dengan database kamu) ====
    public const SELLER_NAME_COLUMN   = 'USERS_NAME';       // nama brand / designer di tabel users
    public const SELLER_PHONE_COLUMN  = 'USERS_PHONE';      // no. WhatsApp seller di tabel users
    public const CATEGORY_NAME_COLUMN = 'CATEGORIES_NAME';  // nama kategori di tabel categories

    protected $table = 'products';
    protected $primaryKey = 'PRODUCTS_ID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('PRODUCTS_STATUS_DEL', 0);
    }

    public function images()
    {
        return $this->hasMany(ProductImages::class, 'PRODUCTS_ID', 'PRODUCTS_ID')
            ->where('PRODUCT_IMAGES_STATUS_DEL', 0)
            ->orderByDesc('PRODUCT_IMAGES_IS_PRIMARY')
            ->orderBy('PRODUCT_IMAGES_ID');
    }

    /** [USERS_ID => nama brand] untuk daftar user tertentu. */
    public static function brandNames($userIds): Collection
    {
        $ids = collect($userIds)->filter()->unique()->values()->all();
        if (empty($ids)) {
            return collect();
        }

        return DB::table('users')->whereIn('USERS_ID', $ids)->pluck(self::SELLER_NAME_COLUMN, 'USERS_ID');
    }

    public function getCoverUrlAttribute(): ?string
    {
        $img = $this->images->first();
        return $img ? $img->src : null;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->PRODUCTS_PRICE, 0, ',', '.');
    }

    /** Asumsi: READY_TYPE 1 = pre-order, 0 = ready stock. */
    public function getIsPreorderAttribute(): bool
    {
        return (int) $this->PRODUCTS_READY_TYPE === 1;
    }

    /**
     * Size chart berbentuk JSON:
     * [{"size":"S","bust":84,"length":60,"shoulder":36,"sleeve":58}, ...]
     */
    public function getSizeRowsAttribute(): ?array
    {
        $raw = $this->PRODUCTS_SIZE_CHART;
        if (! filled($raw)) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
            return $decoded;
        }

        return null;
    }

    /** Kalau size chart bukan JSON, tampilkan sebagai teks biasa. */
    public function getSizeTextAttribute(): ?string
    {
        if ($this->size_rows !== null) {
            return null;
        }

        return filled($this->PRODUCTS_SIZE_CHART) ? $this->PRODUCTS_SIZE_CHART : null;
    }

    /** Daftar ukuran yang bisa dipilih customer. */
    public function getSizesAttribute(): array
    {
        $rows = $this->size_rows;
        if ($rows) {
            $sizes = array_values(array_filter(array_map(fn($r) => $r['size'] ?? null, $rows)));
            if ($sizes) {
                return $sizes;
            }
        }

        return ['S', 'M', 'L', 'XL'];
    }
}