<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $table = 'organisation';
    use HasFactory;

    protected $primaryKey = 'organisation_id';
    protected $fillable = [
        'name',
        'active',
        'expire_date',
    ];

    protected $casts = [
        'active' => 'boolean',
        'expire_date' => 'date',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'organisation_id', 'organisation_id');
    }

    public function organisationTests()
    {
        return $this->hasMany(OrganisationTest::class, 'organisation_id', 'organisation_id');
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'organisation_test', 'organisation_id', 'test_id');
    }
}
