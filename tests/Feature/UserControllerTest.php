<?php

namespace Tests\Feature;

use App\Models\AuthProviderType;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\BmdAuth;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->initRandomUser();
        $this->setRandomUserManagerUser();
    }



    /** @test */
    public function it_creates_user()
    {       

        $orderManagerRole = Role::where('name', 'OrderManager')->get()[0];


        $response = $this->post('/api/users/create', [
            'bmdToken' => $this->sampleUserManagerBmdAuth->token,
            'authProviderId' => $this->sampleUserManagerBmdAuth->auth_provider_type_id,
            'email' => 'test@test.com',
            'password' => 'abcd1234',
            'selectedRoleIds' => [$orderManagerRole->id]
        ]);

        $createdUser = User::where('email', 'test@test.com')->get()[0];


        $response->assertSee('isResultOk');
        $response->assertSee('bmdToken');
        $response->assertSee('bmdRefreshToken');
        $response->assertSee('expiresIn');
        $this->assertDatabaseHas('users', ['email' => $createdUser->email]);
        $this->assertDatabaseHas('bmd_auths', ['token' => $createdUser->bmdAuth->token]);

    }



    /** @test */
    public function it_creates_user_with_json_assertions()
    {

        $orderManagerRole = Role::where('name', 'OrderManager')->get()[0];


        $response = $this->postJson('/api/users/create', [
            'bmdToken' => $this->sampleUserManagerBmdAuth->token,
            'authProviderId' => $this->sampleUserManagerBmdAuth->auth_provider_type_id,
            'email' => 'test@test.com',
            'password' => 'abcd1234',
            'selectedRoleIds' => [$orderManagerRole->id]
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'isResultOk' => true
            ]);

        $this->assertEquals(1, User::where('email', 'test@test.com')->get()->count());
    }

}
