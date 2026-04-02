<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'language';
    use HasFactory;

    protected $primaryKey = 'language_id';
    protected $fillable = [
        'language_code',
        'language_name',
        'enabled',
    ];

    // Get languages that are enabled
    public static function getEnabledLanguages()
    {
        return self::where('enabled', true)->get()->sortBy('language_name');
    }

    // Relationships
    public function questionTranslations()
    {
        return $this->hasMany(QuestionTranslation::class, 'language_id', 'language_id');
    }

    public function interestFieldTranslations()
    {
        return $this->hasMany(InterestFieldTranslation::class, 'language_id', 'language_id');
    }
    public function faqTranslations()
    {
    return $this->hasMany(FrequentlyAskedQuestionTranslation::class, 'language_id');
    }
}
