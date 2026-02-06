<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class QuizOption extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    /**
     * Option media collection:
     * - option_image: 1 image max
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('option_image')->singleFile();
    }

    public function getImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('option_image');
        return $url ?: null;
    }
}
