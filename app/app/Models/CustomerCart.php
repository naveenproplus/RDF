<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCart extends Model
{
    use HasFactory;

    protected $connection = "rdf_main";
    protected $table = "tbl_customer_cart";
    protected $primaryKey = 'CartID';
    public $timestamps = false;

    protected $fillable = [
        "CartID",
        "CustomerID",
        "ProductID",
        "ProductVariationID",
        "Qty",
    ];
}
