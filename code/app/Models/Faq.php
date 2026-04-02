<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Faq extends Model
{
    use HasFactory;
    protected $table = 'frequently_asked_question';
    protected $primaryKey = 'faq_id';

    protected $fillable = ['question', 'answer'];

    public function translations()
    {
        return $this->hasMany(FaqTranslation::class, 'faq_id');
    }

    public function translationForLanguage($languageId)
    {
        return $this->translations()->where('language_id', $languageId)->first();
    }

    // helper to get by locale if your Language model stores locale codes:
    public function translationForLocale(string $locale)
    {
        if (! method_exists(\App\Models\Language::class, 'whereLocale')) {
            return $this->translations()->first();
        }

        $language = \App\Models\Language::where('locale', $locale)->first();
        if (! $language) {
            return $this->translations()->first();
        }

        return $this->translationForLanguage($language->id);
    }
}
