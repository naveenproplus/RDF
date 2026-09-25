<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewLike extends Model
{
    use HasFactory;


    protected $table = "tbl_review_likes";
    protected $primaryKey = 'ReviewLikeID';
    public $timestamps = false;

    protected $fillable = [
        "ReviewLikeID",
        "ReviewID",
        "CustomerID",
        "ProductID"
    ];
}
