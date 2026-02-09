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

        // optional meta (not required if you rely fully on Spatie)
        'media_type', // none | image | audio | both
        'media_caption', // optional text
        'audio_url', // ✅ NEW: External audio URL (Cloudinary/S3)
        'question_media_type',
        'options_type',
        'difficulty',
        'points',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'difficulty' => 'integer',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('sort_order');
    }

    /**
     * Media collections:
     * - question_image: 1 image max
     * - question_audio: 1 audio max
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('question_image')->singleFile();
        $this->addMediaCollection('question_audio')->singleFile();
    }

    /**
     * Accessors
     */
    public function getImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('question_image');
        return $url ?: null;
    }

    /**
     * Get audio URL: prefer audio_url DB column, fallback to Spatie for backward compatibility
     */
    public function getAudioUrlAttribute(): ?string
    {
        // ✅ NEW: Check audio_url column first (external URLs)
        if ($this->attributes['audio_url'] ?? null) {
            return $this->attributes['audio_url'];
        }

        // Fallback to legacy Spatie media collection (for backward compatibility)
        $url = $this->getFirstMediaUrl('question_audio');
        return $url ?: null;
    }

    /**
     * Helper: check if has audio (either URL or Spatie)
     */
    public function hasAudio(): bool
    {
        return !empty($this->attributes['audio_url']) || (bool) $this->getFirstMedia('question_audio');
    }

    /**
     * Auto detect media_type based on image + audio presence
     * Checks: question_image (Spatie) + audio_url (DB column)
     */
    public function refreshMediaType(): void
    {
        $hasImage = (bool) $this->getFirstMedia('question_image');
        $hasAudio = !empty($this->audio_url) || (bool) $this->getFirstMedia('question_audio');

        $type = 'none';
        if ($hasImage && $hasAudio) {
            $type = 'both';
        } elseif ($hasImage) {
            $type = 'image';
        } elseif ($hasAudio) {
            $type = 'audio';
        }

        $this->forceFill(['media_type' => $type])->saveQuietly();
    }
}
