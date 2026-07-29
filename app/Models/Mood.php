<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Mood extends Model
{
    protected $fillable = [
        'user_id',
        'mood',
        'note',
        'date',
        'photo_path',
        'voice_note_path',
    ];

    protected $casts = [
        'mood' => 'integer',
        'date' => 'date',
    ];

    protected $appends = ['photo_url', 'voice_note_url'];

    protected static function booted(): void
    {
        static::deleting(function (Mood $mood) {
            if ($mood->photo_path) {
                Storage::disk('public')->delete($mood->photo_path);
            }
            if ($mood->voice_note_path) {
                Storage::disk('public')->delete($mood->voice_note_path);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function getVoiceNoteUrlAttribute(): ?string
    {
        return $this->voice_note_path ? Storage::disk('public')->url($this->voice_note_path) : null;
    }
}
