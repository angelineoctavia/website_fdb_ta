<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'wishlists';
    protected $primaryKey = 'WISHLISTS_ID';
    public $incrementing = true;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'PRODUCTS_ID', 'PRODUCTS_ID');
    }
}