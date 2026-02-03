<?php

namespace App\Models;

use App\Enums\Severity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_upload_id',
        'pollution_detected',
        'severity',
        'confidence',
        'raw_output',
        'model_name',
        'processed_at',
    ];

    protected $casts = [
        'pollution_detected' => 'boolean',
        'severity' => Severity::class,
        'confidence' => 'float',
        'raw_output' => 'array',
        'processed_at' => 'datetime',
    ];

    public function imageUpload(): BelongsTo
    {
        return $this->belongsTo(ImageUpload::class);
    }
}
