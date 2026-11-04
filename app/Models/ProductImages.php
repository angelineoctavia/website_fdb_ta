<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductImages extends Model
{
    protected $table = 'product_images';
    protected $primaryKey = 'PRODUCT_IMAGES_ID';
    public $incrementing = true;

    protected $guarded = [];

    /**
     * - "https://..."               -> apa adanya
     * - "Mockup_Pics/ABC.jpg"        -> file ada di public/
     * - "storage/products/x.jpg"     -> public/storage (storage:link)
     * - "products/x.jpg"             -> hasil store('products','public') -> storage/products/x.jpg
     */
    public function getSrcAttribute(): string
    {
        $url = (string) $this->PRODUCT_IMAGES_URL;

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $url = ltrim($url, '/');

        if (file_exists(public_path($url))) {
            return asset($url);
        }

        return asset('storage/' . preg_replace('#^storage/#', '', $url));
    }
}