<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;



    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = 'tbl_logs_' . date('Y');
    }

    public $timestamps = false;

    protected $fillable = [
        'LogID',
        'Description',
        'ModuleName',
        'Action',
        'ReferID',
        'OldData',
        'NewData',
        'IPAddress',
        'UserID',
        'logTime'
    ];
}
