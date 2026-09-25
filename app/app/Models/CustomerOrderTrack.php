<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderTrack extends Model
{
    use HasFactory;


    protected $table = "tbl_customer_order_track";
    protected $primaryKey = 'TrackID';
    public $timestamps = false;

    protected $fillable = [
        "TrackID",
        "CustomerID",
        "OrderID",
        "Status",
        "Description",
        "StatusDate",
        "orderBy",
        "UpdatedBy",
    ];
}
