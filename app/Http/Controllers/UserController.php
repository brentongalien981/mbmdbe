<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Bmd\Constants\BmdGlobalConstants;
use App\Rules\MBMDRoleIds;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function create(Request $r)
    {

        // BMD-ON-STAGING: Set stricter validation rules.
        $acceptedEmailDomainsValidationRule = 'email|min:5|max:64|unique:users';
        // $acceptedEmailDomainsValidationRule = 'email|min:8|max:64|unique:users';
        // $acceptedEmailDomainsValidationRule .= '|ends_with:' . implode(',', BmdGlobalConstants::MBMD_ACCEPTED_EMAIL_DOMAINS);

        // $passwordValidationRule = Password::min(8)
        //     ->letters()
        //     ->mixedCase()
        //     ->numbers()
        //     ->symbols()
        //     ->uncompromised();
        $passwordValidationRule = Password::min(4)
            ->letters()
            ->numbers();

        $v = $r->validate([
            'email' => $acceptedEmailDomainsValidationRule,
            'password' => $passwordValidationRule,
            'selectedRoleIds' => ['required', new MBMDRoleIds]
        ]);


        return [
            'msg' => 'In CLASS: UserController, METHOD: create()',
            'email' => $r->email,
            'selectedRoleIds' => $r->selectedRoleIds,
            'v' => $v
        ];
    }
}
