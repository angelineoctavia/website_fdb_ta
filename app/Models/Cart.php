<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';
    protected $primaryKey = 'CARTS_ID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(CartDetail::class, 'CARTS_ID', 'CARTS_ID')
            ->where('CARTS_DETAILS_STATUS_DEL', 0);
    }
}