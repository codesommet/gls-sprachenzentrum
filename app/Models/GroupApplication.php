<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GroupApplication extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'group_id',
        'full_name',
        'whatsapp_number',
        'birthday',
        'status',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function registerMediaCollections(): void
    {
        // No conversions (comme ta config)
        $this->addMediaCollection('card_recto')->singleFile();
        $this->addMediaCollection('card_verso')->singleFile();
    }
}
