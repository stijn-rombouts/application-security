<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class UserProfile extends Component
{
    public $user;

    public function mount()
    {
        $this->user = Auth::user();
    }

    #[On('profile-updated')]
    public function refreshProfile()
    {
        $this->user = Auth::user()->fresh();
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}