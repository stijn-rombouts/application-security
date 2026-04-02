<?php

namespace App\Livewire;

use App\Models\Answer;
use Livewire\Component;

class DownloadCsvButton extends Component
{

    //function to take data and create csv file
    public function createCSVFile()
    {//information file
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="data.csv"',
        ];

        //create file
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['answer_id', 'answer', 'response_time', 'unclear', 'question_id', 'test_attempt_id']);

            //Get all data from answer table
            $answers = Answer::all();

            //Put all data in csv file
            foreach ($answers as $answer){
                fputcsv($handle, [$answer->answer_id, $answer->answer ? 1 : 0, $answer->response_time, $answer->unclear ? 1 : 0, $answer->question_id, $answer->test_attempt_id]);
            }
            fclose($handle);
        };

        //Download in browser with response()-stream()
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.download-csv-button');
    }
}
