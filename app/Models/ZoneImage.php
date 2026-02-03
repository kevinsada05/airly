<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ZoneImage extends Pivot
{
    protected $table = 'zone_images';

    protected $fillable = [
        'zone_id',
        'image_upload_id',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function imageUpload(): BelongsTo
    {
        return $this->belongsTo(ImageUpload::class);
    }
}
