<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';

    public string $username = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->username = request()->string('username')->value();
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check if the token is valid
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('username', $this->username)
            ->first();

        if (!$tokenRecord || !hash_equals($tokenRecord->token, hash('sha256', $this->token))) {
            $this->addError('username', __('This password reset token is invalid.'));
            return;
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($tokenRecord->created_at) > 60) {
            $this->addError('username', __('This password reset token has expired.'));
            return;
        }

        // Find the user
        $user = User::where('username', $this->username)->first();

        if (!$user) {
            $this->addError('username', __('We can\'t find a user with that username.'));
            return;
        }

        // Reset the password
        $user->forceFill([
            'password' => Hash::make($this->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('username', $this->username)->delete();

        event(new PasswordReset($user));

        Session::flash('status', __('Your password has been reset.'));

        $this->redirectRoute('home', navigate: true);
    }
}
