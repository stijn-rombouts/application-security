<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestField extends Model
{

    use HasFactory;

    protected $table = 'interest_field';
    protected $primaryKey = 'interest_field_id';

    protected $fillable = [
        'name',
        'description',
        'active',
        'sound_link',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relationships
    public function questions()
    {
        return $this->hasMany(Question::class, 'interest_field_id', 'interest_field_id');
    }

    public function interestFieldTranslations()
    {
        return $this->hasMany(InterestFieldTranslation::class, 'interest_field_id', 'interest_field_id');
    }

    // Helper methods for translations
    public function getTranslation($languageCode)
    {
        return $this->interestFieldTranslations()
            ->whereHas('language', function ($query) use ($languageCode) {
                $query->where('language_code', $languageCode);
            })
            ->first();
    }

    public function getName($languageCode = null)
    {
        if (!$languageCode) {
            return $this->name;
        }

        $translation = $this->getTranslation($languageCode);
        return $translation && $translation->name ? $translation->name : $this->name;
    }

    public function getDescription($languageCode = null)
    {
        if (!$languageCode) {
            return $this->description;
        }

        $translation = $this->getTranslation($languageCode);
        return $translation && $translation->description ? $translation->description : $this->description;
    }

    public function getSoundLink($languageCode = null)
    {
        if (!$languageCode) {
            return $this->sound_link;
        }
        $translation = $this->getTranslation($languageCode);
        return $translation && $translation->sound_link ? $translation->sound_link : $this->sound_link;
    }

    public function getAudioUrl($languageCode = null)
    {
        $filename = $this->getSoundLink($languageCode);
        if (!$filename) {
            return null;
        }

        return route('question.sound', ['filename' => $filename]);
    }
}
