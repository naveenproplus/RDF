<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryChargeRule extends Model
{
    use HasFactory;
    use softDeletes;


    protected $table = "tbl_delivery_charge_rules";

    protected $fillable = [
        "min_order_value",
        "max_order_value",
        "delivery_charge",
        "created_at",
        "updated_at",
        "deleted_at"
    ];
}
