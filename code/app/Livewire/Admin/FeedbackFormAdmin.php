<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class FeedbackFormAdmin extends Component
{
    //variables
    public $category = '';
    public $message = '';
    public $SendStatus = '';
    public $ErrorCategory='hidden';
    public $ErrorFeedbackMessage = 'hidden';

    public function sendMail(){
        //Check if something is empty and show errors
        $this->category == '' ? $this->ErrorCategory = '' : $this->ErrorCategory ='hidden';
        $this->message == '' ? $this->ErrorFeedbackMessage = '' : $this->ErrorFeedbackMessage ='hidden';

        //Send mail if nothing is empty
        if($this->category != '' and $this->message!=''){
            try{
                //Get mails of all super admins
                $allSuperAdminMails = DB::table('users')
                    ->join('user_role', 'user_role.user_id', "=", "users.user_id")
                    ->join('role', 'user_role.role_id', '=', 'role.role_id')
                    ->where('role.role', '=', 'SuperAdmin')
                    ->pluck('users.email');

                //send mail to every super admin
                foreach ($allSuperAdminMails as $superadmin){
                    //Use the build in mail class from Livewire
                    Mail::send('emails.send-feedback-to-superadmin', [
                        'Name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                        'Type'=> $this->category,
                        'Message' => $this->message,
                        'MailAdmin' => Auth::user()->email
                    ], function($message) use ($superadmin){
                        $message->to($superadmin)
                            ->subject("Een admin heeft feedback voor u!");
                    });
                    //Show message send
                    $this->SendStatus = __('pageFeedback.MessageSend');
                }
            } catch (\Exception $e) {
                //show error message isn't send
                $this->SendStatus = __('pageFeedback.MessageNotSend');
            }

            //make message and send status empty
            $this->message='';
        }
    }

    public function render()
    {
        return view('livewire.admin.feedback-form-admin');
    }
}
