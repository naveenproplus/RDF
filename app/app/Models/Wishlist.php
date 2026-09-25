<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;


    protected $table = "tbl_wishlists";

    protected $fillable = [
        "customer_id",
        "product_id",
        "product_variation_id"
    ];
}
