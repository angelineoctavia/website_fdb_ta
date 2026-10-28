<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'PRODUCTS_ID'; // Sesuaikan jika primary key di database-mu berbeda
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'CATEGORIES_ID', 'CATEGORIES_ID');
    }

    // Relasi ke User (Brand)
    public function user()
    {
        return $this->belongsTo(User::class, 'USERS_ID', 'USERS_ID');
    }
}