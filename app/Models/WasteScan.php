<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_upload_id',
        'file_path',
        'item_type',
        'recyclable',
        'instructions',
        'warnings',
        'raw_output',
        'model_name',
    ];

    protected $casts = [
        'recyclable' => 'boolean',
        'raw_output' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imageUpload(): BelongsTo
    {
        return $this->belongsTo(ImageUpload::class);
    }
}
