<?php

namespace Tests\Feature;

use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Middleware\BmdAuth;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Mockery\MockInterface;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class UserControllerTest extends TestCase
{
    // use RefreshDatabase;



    /** @test */
    public function it_creates_user()
    {
        // $this->withoutExceptionHandling();

        // // Test roles
        // $r1 = new Role();
        // $r1->name = 'TestRole';
        // $r1->save();        


        // Gate::shouldReceive('forUser')->once();
        // Gate::shouldReceive('authorize')->once();        


        // $mock = $this->mock(BmdAuthProvider::class, function ($mock) {
        //     // $mock->shouldReceive('setInstance')->once();
        //     // $mock->shouldReceive('check')->once()->andReturn(true);

        //     $testUser = User::factory()->create();

        //     $mock->shouldReceive('user')->once()->andReturn($testUser);
        // });


        // $mockedUserPolicy = $this->mock(UserPolicy::class, function ($mock) {
        //     $mock->shouldReceive('create')->once()->andReturn(true);
        // });

        
        // $response = $this->post('/api/users/create', [
        //     'bmdToken' => 'abcdefg8a9a98a',
        //     'authProviderId' => 1,
        //     'email' => 'test@test.com',
        //     'password' => 'abcd1234',
        //     'selectedRoleIds' => [$r1->id]
        // ]);


        // $response->assertSee('pussy');
    }
}
