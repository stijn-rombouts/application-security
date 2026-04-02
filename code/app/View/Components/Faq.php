<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use App\Models\Language;
use App\Models\Faq as FaqModel;

class Faq extends Component
{
    public $faqs;

    /**
     * Create a new component instance.
     */
    public function __construct($faqs = null)
    {
        // If FAQs are explicitly provided, use them.
        if ($faqs !== null) {
            $this->faqs = $faqs;
            return;
        }

        // Determine current locale and try to load translations for it.
        $locale = app()->getLocale();

        // Default: load base FAQs
        $baseFaqs = FaqModel::all();

        // If locale is not the default 'nl' (project default), try to map translations
        if ($locale && $locale !== 'nl') {
            $languageId = Language::where('language_code', $locale)->value('language_id');
            if ($languageId) {
                // Map each FAQ to use translation when available
                $this->faqs = $baseFaqs->map(function ($faq) use ($languageId) {
                    $translation = $faq->translations()->where('language_id', $languageId)->first();
                    if ($translation) {
                        // Return a simple object with question and answer fields (keeps original properties too)
                        return (object) array_merge($faq->toArray(), [
                            'question' => $translation->question,
                            'answer' => $translation->answer,
                        ]);
                    }
                    return $faq;
                });
                return;
            }
        }

        // Fallback to base FAQs
        $this->faqs = $baseFaqs;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.faq');
    }
}
