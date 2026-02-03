<?php

namespace App\Models;

use App\Enums\ImageSource;
use App\Enums\ImageUploadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ImageUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lat',
        'lng',
        'captured_at',
        'file_path',
        'source',
        'location_accuracy',
        'status',
        'analysis_version',
        'note',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'captured_at' => 'datetime',
        'location_accuracy' => 'integer',
        'status' => ImageUploadStatus::class,
        'source' => ImageSource::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analysisResult(): HasOne
    {
        return $this->hasOne(AnalysisResult::class);
    }

    public function wasteScan(): HasOne
    {
        return $this->hasOne(WasteScan::class);
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'zone_images')
            ->using(ZoneImage::class)
            ->withTimestamps();
    }
}
