<?php

namespace App\View\Components;

use App\Models\Language;
use Illuminate\View\Component;

class LanguageSelector extends Component
{
    public $languages;
    public $position;

    /**
     * Create a new component instance.
     */
    public function __construct($position = 'top-right')
    {
        $this->languages = Language::getEnabledLanguages();
        $this->position = $position;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.language-selector');
    }
}
