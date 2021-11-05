<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use App\Models\BmdAuth;
use Illuminate\Support\Str;
use App\Models\AuthProviderType;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;



    protected $sampleUser = null;
    protected $sampleUserBmdAuth = null;
    protected $sampleUserUnhashedPassword = 'abcd1234';

    protected $sampleUserManager = null;
    protected $sampleUserManagerBmdAuth = null;      
    
    protected $sampleOrderManager = null;
    protected $sampleOrderManagerBmdAuth = null;



    protected function initRandomUser()
    {

        $u = new User();
        $u->email = 'sampleUser@test.com';
        $u->password = Hash::make($this->sampleUserUnhashedPassword);
        $u->save();


        $aBmdAuth = new BmdAuth();
        $aBmdAuth->user_id = $u->id;
        $aBmdAuth->token = Str::uuid()->toString();
        $aBmdAuth->expires_in = getdate()[0] + (60 * 60 * 24);
        $aBmdAuth->frontend_pseudo_expires_in = $aBmdAuth->expires_in;
        $aBmdAuth->auth_provider_type_id = AuthProviderType::BMD;
        $aBmdAuth->save();


        $this->sampleUser = $u;
        $this->sampleUserBmdAuth = $aBmdAuth;
    }



    protected function setRandomUserManagerUser()
    {
        $this->seed(RoleSeeder::class);

        
        $u = new User();
        $u->email = 'sampleUserManager@test.com';
        $u->password = Hash::make($this->sampleUserUnhashedPassword);
        $u->save();


        $aBmdAuth = new BmdAuth();
        $aBmdAuth->user_id = $u->id;
        $aBmdAuth->token = Str::uuid()->toString();
        $aBmdAuth->expires_in = getdate()[0] + (60 * 60 * 24);
        $aBmdAuth->frontend_pseudo_expires_in = $aBmdAuth->expires_in;
        $aBmdAuth->auth_provider_type_id = AuthProviderType::BMD;
        $aBmdAuth->save();


        $this->sampleUserManager = $u;
        $this->sampleUserManagerBmdAuth = $aBmdAuth;

        $userManagerRole = Role::where('name', 'UserManager')->get()[0];

        $u->roles()->sync([$userManagerRole->id]);
    }



    protected function setSampleOrderManager()
    {
        $this->seed(RoleSeeder::class);

        
        $u = new User();
        $u->email = 'sampleOrderManager@test.com';
        $u->password = Hash::make($this->sampleUserUnhashedPassword);
        $u->save();


        $aBmdAuth = new BmdAuth();
        $aBmdAuth->user_id = $u->id;
        $aBmdAuth->token = Str::uuid()->toString();
        $aBmdAuth->expires_in = getdate()[0] + (60 * 60 * 24);
        $aBmdAuth->frontend_pseudo_expires_in = $aBmdAuth->expires_in;
        $aBmdAuth->auth_provider_type_id = AuthProviderType::BMD;
        $aBmdAuth->save();


        $this->sampleOrderManager = $u;
        $this->sampleOrderManagerBmdAuth = $aBmdAuth;

        $managerRole = Role::where('name', 'OrderManager')->get()[0];

        $u->roles()->sync([$managerRole->id]);
    }
}
