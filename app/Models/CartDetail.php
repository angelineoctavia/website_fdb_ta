<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartDetail extends Model
{
    protected $table = 'carts_details';
    protected $primaryKey = 'CARTS_DETAILS_ID';
    public $incrementing = true;

    protected $guarded = [];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'CARTS_ID', 'CARTS_ID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'PRODUCTS_ID', 'PRODUCTS_ID');
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->CARTS_DETAILS_PRICE * (int) $this->CARTS_DETAILS_QTY;
    }
}