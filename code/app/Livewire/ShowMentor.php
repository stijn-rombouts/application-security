<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ShowMentor extends Component
{
    public $mentor;

    public function mount() {
        $user = Auth::user();

        $this->mentor = User::find($user->mentor_id);
    }

    public function render()
    {
        return view('livewire.show-mentor');
    }
}
