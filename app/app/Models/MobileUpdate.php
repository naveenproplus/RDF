<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileUpdate extends Model
{
    use HasFactory;


    protected $table = "tbl_mobile_version";
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = ['UpdateImageUrl'];

    protected $fillable = [
        "id",
        "logo",
        "title",
        "description",
        "current_version",
        "new_version",
        "android_link",
        "ios_link",
        "submit_text",
        "ignore_text",
        "update_type",
        "update_to",
    ];

    public function getUpdateImageUrlAttribute($value)
    {
        return config('app.url')."/".($this->logo ? $this->logo : "assets/images/no-image.png");
    }
}
