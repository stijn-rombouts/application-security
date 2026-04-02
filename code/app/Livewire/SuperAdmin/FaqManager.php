<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Language;
use Livewire\WithPagination;

class FaqManager extends Component
{
    use WithPagination;

    public $search = '';
    public $editingId = null;
    public $form = [
        'question_nl' => '',
        'answer_nl' => '',
        'question_en' => '',
        'answer_en' => '',
    ];

    protected function rules(): array
    {
        $rules = [
            'form.question_nl' => 'required|string|max:255',
            'form.answer_nl' => 'required|string|max:2000',
            'form.question_en' => 'required|string|max:255',
            'form.answer_en' => 'required|string|max:2000',
        ];

        return $rules;
    }

    public function startCreate()
    {
        $this->editingId = null;
        $this->resetFormState();
        $this->dispatch('modal-open', name: 'create-faq-form');
    }

    public function startEdit($id)
    {
        $faq = Faq::where('faq_id', $id)->firstOrFail();
        $englishTranslation = FaqTranslation::where('faq_id', $id)
            ->first();
        $this->editingId = $id;
        $this->form['question_nl'] = $faq->question;
        $this->form['answer_nl'] = $faq->answer;
        $this->form['question_en'] = $englishTranslation->question ?? 'English question placeholder';
        $this->form['answer_en'] = $englishTranslation->answer ?? 'English answer placeholder';
        $this->dispatch('modal-open', name: 'create-faq-form');
    }

    public function save()
    {
        $this->validate();
        if ($this->editingId) {
            $faq = Faq::where('faq_id', $this->editingId)->firstOrFail();
            $faq->update([
                'question' => $this->form['question_nl'],
                'answer' => $this->form['answer_nl'],
            ]);
            $englishTranslation = FaqTranslation::where('faq_id', $this->editingId)
                ->where('language_id', Language::where('language_code', 'en')->value('language_id'))
                ->first();
            if ($englishTranslation) {
                $englishTranslation->update([
                    'question' => $this->form['question_en'],
                    'answer' => $this->form['answer_en'],
                ]);
            } else {
                FaqTranslation::create([
                    'faq_id' => $this->editingId,
                    'language_id' => Language::where('language_code', 'en')->value('language_id'),
                    'question' => $this->form['question_en'],
                    'answer' => $this->form['answer_en'],
                ]);
            }

        } else {
            $faq = Faq::create([
                'question' => $this->form['question_nl'],
                'answer' => $this->form['answer_nl'],
            ]);
            FaqTranslation::create([
                'faq_id' => $faq->faq_id,
                'language_id' => Language::where('language_code', 'en')->value('language_id'),
                'question' => $this->form['question_en'],
                'answer' => $this->form['answer_en'],
            ]);
        }

        $this->dispatch('modal-close', name: 'create-faq-form');
        $this->resetFormState();

    }

    public function confirmDelete($id)
    {
        $this->editingId = $id;
        $this->dispatch('modal-open', name: 'delete-faq-confirmation');
    }

    public function deleteFaq()
    {
        Faq::where('faq_id', $this->editingId)->firstOrFail()->delete();
        FaqTranslation::where('faq_id', $this->editingId)->delete();
        $this->resetFormState();
        $this->dispatch('modal-close', name: 'delete-faq-confirmation');
    }
    public function resetFormState()
    {
        $this->form = [
            'question_nl' => '',
            'answer_nl' => '',
            'question_en' => '',
            'answer_en' => '',
        ];
        $this->editingId = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getRecordsProperty()
    {
        $query = Faq::query();
        if (trim($this->search) !== '') {
            $query->where('question', 'like', '%'.$this->search.'%')
                  ->orWhere('answer', 'like', '%'.$this->search.'%');
        }
        return $query->orderBy('faq_id', 'desc')->paginate(10);
    }

    public function render()
    {
        return view('livewire.superadmin.faq-manager', [
            'records' => $this->records,
        ]);
    }
}
