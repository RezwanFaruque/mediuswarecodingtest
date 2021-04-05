<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    // making relation for fetching product varient
    public function variants(){
        return $this->hasMany(ProductVariant::class,'product_id','id');
    }

    // making relation for fecting product price
    public function prices(){
        return $this->hasMany(ProductVariantPrice::class,'product_id','id');
    }

}
