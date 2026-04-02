<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    public string $username = '';

    /**
     * Send a password reset link to the provided username.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'username' => ['required', 'string'],
        ]);

        // Find user by username
        $user = User::where('username', $this->username)->first();

        if ($user && $user->email) {
            // Create token manually since we're using username instead of email
            $token = Str::random(60);
            
            // Store the token in the database
            DB::table('password_reset_tokens')->updateOrInsert(
                ['username' => $this->username],
                [
                    'username' => $this->username,
                    'token' => hash('sha256', $token),
                    'created_at' => now(),
                ]
            );

            // Send the reset password notification
            $user->sendPasswordResetNotification($token);
        }

        // Always show the same message for security
        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}
