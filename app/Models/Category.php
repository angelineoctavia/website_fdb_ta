<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'CATEGORIES_ID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class, 'CATEGORIES_ID', 'CATEGORIES_ID');
    }
}