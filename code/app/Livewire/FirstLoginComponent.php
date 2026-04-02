<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class FirstLoginComponent extends Component
{
    public bool $open = true;
    public $password;
    public $password_confirmation;

    public function mount()
    {
        $this->open = true;
    }

    public function savePassword()
    {
        //Validation password
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        //Get user and set password
        $user = Auth::user();
        $user->password = Hash::make($this->password);
        //put first login on false
        $user->first_login = false;
        //save user
        $user->save();

        $this->open = false;
        //Go to index page of that role
        return $this->redirect("/");
    }

    public function render()
    {
        return view('livewire.first-login-component');
    }
}
