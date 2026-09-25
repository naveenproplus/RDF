<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;


    protected $table = "tbl_customer";
    protected $primaryKey = 'CustomerID';
    public $incrementing = false;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'UpdatedOn';
    protected $appends = ['profileImageUrl'];

    protected $fillable = [
        "CustomerID",
        "CustomerName",
        "nick_name",
        "CustomerImage",
        "GenderID",
        "MobileNo1",
        "Email",
        "language",
        "ActiveStatus",
        "DFlag",
        "otp",
        "otp_verified",
        "api_token",
        "fcmToken",
        "IsAskGoogleReview",
        "IsGoogleReviewed",
        "CreatedBy",
        "CreatedOn",
        "UpdatedBy",
        "UpdatedOn",
        "DeletedBy",
        "DeletedOn",
    ];

    public function getprofileImageUrlAttribute($value)
    {
        return config('app.url')."/".($this->CustomerImage ? $this->CustomerImage : ((strtolower($this->Gender) == "female") ? "assets/images/female-icon.png" : "assets/images/male-icon.png"));
    }
}
