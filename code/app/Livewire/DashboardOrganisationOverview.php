<?php

namespace App\Livewire;

use App\Models\InterestField;
use App\Models\Organisation;
use App\Models\OrganisationTest;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardOrganisationOverview extends Component
{
    public function render()
    {
        //Count total of users of the organisation of admin
        $totalUsers = User::where('organisation_id', auth()->user()->organisation_id)->count();
        //Count total of tests the organisation has acces to
        $totalTests = OrganisationTest::where('organisation_id', auth()->user()->organisation_id)->count();
        //count completed tests of organisation
        $countCompleteAttempts = TestAttempt::join('users', 'users.user_id', '=', 'test_attempt.user_id')->where('finished', true)->where('organisation_id', auth()->user()->organisation_id)->count();
        //Count total of attempts of organisation
        $countAttempts = TestAttempt::join('users', 'users.user_id', '=', 'test_attempt.user_id')->where('organisation_id', auth()->user()->organisation_id)->count();

        //calculate completion score
        if ($countAttempts!=0){
            $completionScore = round($countCompleteAttempts/$countAttempts*100) . '%';
        }
        else{
            $completionScore = (__('pagesresearcher.NoAttempts'));
        }

        //Take all intrestfields
        $interestFields = InterestField::all();
        //Count intrestfields
        $mostChosenIntrestFields = DB::table('answer')
            ->join('test_attempt', 'answer.test_attempt_id', '=', 'test_attempt.test_attempt_id')
            ->join('question', 'answer.question_id', "=", "question.question_id")
            ->join('interest_field', 'question.interest_field_id', "=", "interest_field.interest_field_id")
            ->join('users', 'users.user_id', '=', 'test_attempt.user_id')
            ->where('users.organisation_id', auth()->user()->organisation_id)
            ->select(
                'interest_field.name as interest_field_name',
                DB::raw('COUNT(answer.answer_id) as total_chosen'))
            ->groupBy('interest_field.interest_field_id', 'interest_field.name')
            ->orderByDesc('total_chosen')
            ->get();

        return view('livewire.dashboard-organisation-overview', compact('totalUsers', 'totalTests', 'interestFields', 'mostChosenIntrestFields', 'countAttempts', 'completionScore'));
    }
}
