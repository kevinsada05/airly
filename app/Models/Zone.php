<?php

namespace App\Models;

use App\Enums\Severity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'polygon',
        'current_severity',
    ];

    protected $casts = [
        'polygon' => 'array',
        'current_severity' => Severity::class,
    ];

    public function histories(): HasMany
    {
        return $this->hasMany(ZoneHistory::class);
    }

    public function imageUploads(): BelongsToMany
    {
        return $this->belongsToMany(ImageUpload::class, 'zone_images')
            ->using(ZoneImage::class)
            ->withTimestamps();
    }

    public function containsPoint(float $lat, float $lng): bool
    {
        $polygon = Arr::get($this->polygon, 'coordinates.0', []);

        if (count($polygon) < 3) {
            return false;
        }

        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i][1] ?? null;
            $yi = $polygon[$i][0] ?? null;
            $xj = $polygon[$j][1] ?? null;
            $yj = $polygon[$j][0] ?? null;

            if ($xi === null || $yi === null || $xj === null || $yj === null) {
                $j = $i;
                continue;
            }

            $intersect = (($yi > $lng) !== ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }

            $j = $i;
        }

        return $inside;
    }

    public function recalculateSeverity(): void
    {
        $hasRed = $this->imageUploads()
            ->whereHas('analysisResult', function ($query) {
                $query->where('severity', Severity::Red->value);
            })
            ->exists();

        $this->current_severity = $hasRed ? Severity::Red : Severity::Green;
        $this->save();

        $this->histories()->create([
            'severity' => $this->current_severity,
            'computed_at' => now(),
            'image_count' => $this->imageUploads()->count(),
        ]);
    }
}
