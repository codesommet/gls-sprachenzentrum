<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class QuizQuestion extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'quiz_id',
        'question_text',

        // optional media meta (recommended)
        'media_type',     // none | image | audio | both
        'media_caption',  // optional text

        'difficulty',
        'points',
        'sort_order',
        'is_active',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id');
    }

    /**
     * Optional media collections for a question.
     * - question_image: 1 image max
     * - question_audio: 1 audio max
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('question_image')->singleFile();
        $this->addMediaCollection('question_audio')->singleFile();
    }

    /**
     * Helpers (optional)
     */
    public function getImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('question_image');
        return $url ?: null;
    }

    public function getAudioUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('question_audio');
        return $url ?: null;
    }
}
