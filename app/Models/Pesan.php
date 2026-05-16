<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    protected $fillable = [
        'nomor',
        'pesan',
        'media_path',
        'status',
        'error_message'
    ];
}
