<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ProductReview extends Model
{
    use HasFactory;


    protected $table = "tbl_product_reviews";
    protected $primaryKey = 'TrackID';
    public $timestamps = false;

    protected $fillable = [
        "ReviewID",
        "CustomerID",
        "OrderID",
        "ProductID",
        "rating",
        "review",
    ];

    public function customerDetails()
{
    return $this->hasOne(Customer::class, 'CustomerID', 'CustomerID')
        ->select('CustomerID', DB::raw('IF(CustomerName IS NOT NULL, CustomerName, nick_name) AS CustomerName'));
}

}
