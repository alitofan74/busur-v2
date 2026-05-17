<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_RESTING = 'resting';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const INPUT_MANUAL = 'manual';
    public const INPUT_EXCEL = 'excel';

    protected $fillable = [
        'nama',
        'tipe_input',
        'total',
        'terkirim',
        'gagal',
        'status',
        'settings',
        'started_at',
        'finished_at',
        'last_processed_at',
    ];

    protected $casts = [
        'total' => 'integer',
        'terkirim' => 'integer',
        'gagal' => 'integer',
        'settings' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_processed_at' => 'datetime',
    ];

    public function pesans(): HasMany
    {
        return $this->hasMany(Pesan::class);
    }
}
