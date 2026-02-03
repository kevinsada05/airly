<?php

namespace App\Models;

use App\Enums\Severity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoneHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'severity',
        'computed_at',
        'image_count',
        'notes',
    ];

    protected $casts = [
        'severity' => Severity::class,
        'computed_at' => 'datetime',
        'image_count' => 'integer',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
