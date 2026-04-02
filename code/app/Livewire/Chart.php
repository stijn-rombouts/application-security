<?php

namespace App\Livewire;

use Livewire\Component;

class Chart extends Component
{
    public $labels = [];
    public $data = [];

    public function render()
    {
        return view('livewire.chart');
    }
}
