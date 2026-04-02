<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $table = 'answer';
    protected $primaryKey = 'answer_id';

    protected $fillable = [
        'answer',
        'response_time',
        'unclear',
        'question_id',
        'test_attempt_id',
    ];

    protected $casts = [
        'answer' => 'boolean',
        'unclear' => 'boolean',
        'response_time' => 'integer',
    ];

    /**
     * Relationship with Question model
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    /**
     * Relationship with TestAttempt model
     */
    public function testAttempt()
    {
        return $this->belongsTo(TestAttempt::class, 'test_attempt_id', 'test_attempt_id');
    }
}