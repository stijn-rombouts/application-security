<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $table = 'test';
    protected $primaryKey = 'test_id';

    protected $fillable = [
        'test_name',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    // Relationships
    public function organisationTests()
    {
        return $this->hasMany(OrganisationTest::class, 'test_id', 'test_id');
    }

    public function userTests()
    {
        return $this->hasMany(UserTest::class, 'test_id', 'test_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_test', 'test_id', 'user_id');
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_test', 'test_id', 'organisation_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'test_id', 'test_id');
    }

    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class, 'test_id', 'test_id');
    }
}


