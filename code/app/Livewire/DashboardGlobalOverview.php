<?php

namespace App\Livewire;

use App\Models\InterestField;
use App\Models\Organisation;
use App\Models\TestAttempt;
use App\Models\User;
use App\Models\Test;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardGlobalOverview extends Component
{
    public function render()
    {
        //Get data for dashboard researcher
        $totalOrganisations = Organisation::count();
        $totalUsers = User::count();
        $totalTests = Test::count();
        $countCompleteAttempts = TestAttempt::where('finished', true)->count();
        $countAttempts = TestAttempt::count();
        if ($countAttempts!=0){
            $completionScore = round($countCompleteAttempts/$countAttempts*100) . '%';
        }
        else{
            $completionScore = (__('pagesresearcher.NoAttempts'));
        }
        $interestFields = InterestField::all();
        $mostChosenIntrestFields = DB::table('answer')
            ->join('test_attempt', 'answer.test_attempt_id', '=', 'test_attempt.test_attempt_id')
            ->join('question', 'answer.question_id', "=", "question.question_id")
            ->join('interest_field', 'question.interest_field_id', "=", "interest_field.interest_field_id")
            ->select(
                'interest_field.name as interest_field_name',
                DB::raw('COUNT(answer.answer_id) as total_chosen'))
            ->groupBy('interest_field.interest_field_id', 'interest_field.name')
            ->orderByDesc('total_chosen')
            ->get();

        return view('livewire.dashboard-global-overview', compact('totalUsers', 'totalTests', 'interestFields', 'mostChosenIntrestFields', 'totalOrganisations', 'completionScore'));
    }
}
