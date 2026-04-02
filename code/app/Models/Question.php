<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    use HasFactory;

    protected $table = 'question';
    protected $primaryKey = 'question_id';

    protected $fillable = [
        'question',
        'media_link',
        'sound_link',
        'test_id',
        'interest_field_id',
        'question_number',
        'image_description'
    ];

    protected $attributes = [
        'media_link' => null,
        'sound_link' => null,
        'image_description' => null,
    ];

    // Relationships
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id', 'test_id');
    }

    public function interestField()
    {
        return $this->belongsTo(InterestField::class, 'interest_field_id', 'interest_field_id');
    }

    public function questionTranslations()
    {
        return $this->hasMany(QuestionTranslation::class, 'question_id', 'question_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id', 'question_id');
    }

    // Helper methods for translations
    public function getTranslation($languageCode)
    {
        return $this->questionTranslations()
            ->whereHas('language', function ($query) use ($languageCode) {
                $query->where('language_code', $languageCode);
            })
            ->first();
    }

    public function getQuestion($languageCode = null)
    {
        if (!$languageCode) {
            return $this->question;
        }

        $translation = $this->getTranslation($languageCode);
        return $translation && $translation->question ? $translation->question : $this->question;
    }

    public function getMediaLink($languageCode = null)
    {
        if (!$languageCode) {
            return $this->media_link;
        }

        $translation = $this->getTranslation($languageCode);
        return $translation && $translation->media_link ? $translation->media_link : $this->media_link;
    }

    public function getSoundLink($languageCode = null)
    {
        if (!$languageCode) {
            return $this->sound_link;
        }

        $translation = $this->getTranslation($languageCode);
        return $translation && $translation->sound_link ? $translation->sound_link : $this->sound_link;
    }

    public function getImageDescription($languageCode = null)
    {
        if (!$languageCode) {
            return $this->image_description;
        }

        $translation = $this->getTranslation($languageCode);
        return $translation && $translation->image_description ? $translation->image_description : $this->image_description;
    }

    public function getImageUrl($languageCode = null)
    {
        $filename = $this->getMediaLink($languageCode);
        if (!$filename) {
            return null;
        }

        // Check if it's already a full URL
        if (filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        return route('question.image', ['filename' => $filename]);
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
