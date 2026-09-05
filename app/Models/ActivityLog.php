<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'log_type',
        'category_id',
        'file_path',
        'file_name',
        'ip_address',
    ];
}
