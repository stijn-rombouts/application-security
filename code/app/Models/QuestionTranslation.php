<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTranslation extends Model
{
    use HasFactory;

    protected $table = 'question_translation';
    protected $primaryKey = 'question_translation_id';

    protected $fillable = [
        'question_id',
        'language_id',
        'question',
        'media_link',
        'sound_link',
        'image_description',
    ];

    protected $attributes = [
        'media_link' => null,
        'sound_link' => null,
        'image_description' => null,
    ];

    // Relationships
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'language_id');
    }
}
