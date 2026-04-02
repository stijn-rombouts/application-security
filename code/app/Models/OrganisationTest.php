<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationTest extends Model
{
    use HasFactory;

    protected $table = 'organisation_test';
    protected $primaryKey = 'organisation_test_id';

    protected $fillable = [
        'test_id',
        'organisation_id',
    ];

    /**
     * Relationship with Test model
     */
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id', 'test_id');
    }

    /**
     * Relationship with Organisation model
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'organisation_id');
    }
}