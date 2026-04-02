<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqTranslation extends Model
{
    protected $table = 'frequently_asked_question_translation';
    protected $primaryKey = 'faq_translation_id';

    protected $fillable = [
        'faq_id',
        'language_id',
        'question',
        'answer',
    ];

    public function faq()
    {
        return $this->belongsTo(Faq::class, 'faq_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
