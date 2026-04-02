<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

        /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'password',
        'email',
        'is_sound_on',
        'vision_type',
        'mentor_id',
        'organisation_id',
        'language_id',
        'first_login',
        'active',
        'profile_picture_url',
    ];

    /**
     * Get the full URL for the user's profile picture.
     */
    public function getProfilePictureUrlAttribute()
    {
        $filename = $this->attributes['profile_picture_url'] ?? null;
        if (!$filename) {
            return null;
        }
        // Use route for private profile pictures
        return route('profile.picture', ['filename' => $filename]);
    }

    /**
     * Get the raw filename of the user's profile picture without URL transformation.
     */
    public function getRawProfilePictureName()
    {
        return $this->attributes['profile_picture_url'] ?? null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_sound_on' => 'boolean',
            'first_login' => 'boolean',
            'active' => 'boolean',
            'mentor_id' => 'integer',
            'organisation_id' => 'integer',
            'language_id' => 'integer',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->first_name . ' ' . $this->last_name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        // Only send if user has an email
        if ($this->email) {
            $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
        }
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    /**
     * Get the organization that belongs to the user.
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'organisation_id');
    }

    /**
     * Get the language that belongs to the user.
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'language_id');
    }

    /**
     * Get the mentor that belongs to the user.
     */
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id', 'user_id');
    }

    /**
     * Get the users that have this user as their mentor.
     */
    public function mentees()
    {
        return $this->hasMany(User::class, 'mentor_id', 'user_id');
    }

    /**
     * Get the test attempts that belong to the user.
     */
    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class, 'user_id', 'user_id');
    }

    /**
     * Get the user tests that belong to the user.
     */
    public function userTests()
    {
        return $this->hasMany(UserTest::class, 'user_id', 'user_id');
    }

    /**
     * Get the tests that are assigned to the user.
     */
    public function tests()
    {
        return $this->belongsToMany(Test::class, 'user_test', 'user_id', 'test_id');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('role', $role)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('role', $roles)->exists();
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('role', $roles)->count() === count($roles);
    }

    /**
     * Get the user's primary role (first role).
     */
    public function getPrimaryRole(): ?Role
    {
        return $this->roles()->first();
    }

    /**
     * Get the user's primary role name.
     */
    public function getPrimaryRoleName(): ?string
    {
        $role = $this->getPrimaryRole();
        return $role ? $role->role : null;
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Check if user is a mentor.
     */
    public function isMentor(): bool
    {
        return $this->hasRole(Role::MENTOR);
    }

    /**
     * Check if user is a client.
     */
    public function isClient(): bool
    {
        return $this->hasRole(Role::CLIENT);
    }

    public function isResearcher(): bool
    {
        return $this->hasRole(Role::RESEARCHER);
    }
}
