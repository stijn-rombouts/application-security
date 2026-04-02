<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestAttempt extends Model
{
    use HasFactory;

    protected $table = 'test_attempt';
    protected $primaryKey = 'test_attempt_id';

    protected $fillable = [
        'test_id',
        'user_id',
        'finished',
    ];

    /**
     * Relationship with Test model
     */
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id', 'test_id');
    }

    /**
     * Relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship with Answer model
     */
    public function answers()
    {
        return $this->hasMany(Answer::class, 'test_attempt_id', 'test_attempt_id');
    }
}
