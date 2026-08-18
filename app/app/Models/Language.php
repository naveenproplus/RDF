<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_in_english',
        'code',
        'status',
    ];

    public function scopeActive($query)
    {
        $query->whereStatus('Active');

        // Keep legacy soft-deleted rows out of API/web language lists.
        if (Schema::hasColumn($this->getTable(), 'DFlag')) {
            $query->where('DFlag', 0);
        }

        return $query;
    }

    public function translations()
    {
        return $this->hasOne(Translation::class, 'language_id', 'id');
    }
}
