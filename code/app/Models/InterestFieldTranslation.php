<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestFieldTranslation extends Model
{
    use HasFactory;

    protected $table = 'interest_field_translation';
    protected $primaryKey = 'interest_field_translation_id';

    protected $fillable = [
        'interest_field_id',
        'language_id',
        'name',
        'description',
        'sound_link',
    ];

    // Relationships
    public function interestField()
    {
        return $this->belongsTo(InterestField::class, 'interest_field_id', 'interest_field_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'language_id');
    }

    public function getAudioUrl()
    {
        $filename = $this->sound_link;
        if (!$filename) {
            return null;
        }

        return route('question.sound', ['filename' => $filename]);
    }
}
