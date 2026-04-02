<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTest extends Model
{
    use HasFactory;

    protected $table = 'user_test';
    protected $primaryKey = 'user_test_id';

    protected $fillable = [
        'user_id',
        'test_id',
    ];

    /**
     * Relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship with Test model
     */
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id', 'test_id');
    }
}