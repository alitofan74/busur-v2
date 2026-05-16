<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'nama',
        'total',
        'terkirim',
        'gagal',
        'status',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function pesans()
    {
        return $this->hasMany(Pesan::class);
    }
}
