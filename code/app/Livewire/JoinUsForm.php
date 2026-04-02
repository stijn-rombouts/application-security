<?php

namespace App\Livewire;

use App\Mail\JoinUsRequest;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class JoinUsForm extends Component
{
    public $fullName = '';
    public $emailAddress = '';
    public $heardFrom = '';
    public $joinUs = '';
    public $organisation = '';

    protected $rules = [
        'fullName' => 'required',
        'emailAddress' => 'required|email',
        'heardFrom' => 'required',
        'joinUs' => 'required',
        'organisation' => 'required',
    ];

    public function sendMail()
    {
        $this->validate();

        //Get mails of all super admins
        $allSuperAdminMails = DB::table('users')
            ->join('user_role', 'user_role.user_id', "=", "users.user_id")
            ->join('role', 'user_role.role_id', '=', 'role.role_id')
            ->where('role.role', '=', Role::SUPER_ADMIN)
            ->pluck('users.email');

        // ensure we have a collection and include configured admin address
        $adminAddress = config('mail.admin_address', null);
        if ($adminAddress) {
            $allSuperAdminMails = $allSuperAdminMails->push($adminAddress);
        }

        $allSuperAdminMails = $allSuperAdminMails->unique()->filter()->values();

        // send the same JoinUsRequest mailable to every superadmin (and configured admin)
        foreach ($allSuperAdminMails as $recipient) {
            Mail::to($recipient)->send(
                new JoinUsRequest(
                    $this->fullName,
                    $this->emailAddress,
                    $this->heardFrom,
                    $this->joinUs,
                    $this->organisation
                )
            );
        }

        session()->flash('message', __('joinrequest.contact_success'));
        $this->reset();
    }

    public function render()
    {
        return view('livewire.join-us-form');
    }
}
