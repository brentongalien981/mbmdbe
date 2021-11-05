<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MySampleTest extends TestCase
{

    use RefreshDatabase;



    /** @test */
    public function it_shits()
    {
        $u = new User();
        $u->email = 'test@test.com';
        $u->password = Hash::make('abcd1234');
        $u->save();


        $this->assertDatabaseHas('users', ['email' => 'test@test.com']);
        $this->assertEquals(1, User::all()->count());
    }
}
