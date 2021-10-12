<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;



    /** @test */
    public function it_tests_shit() {
        $users = User::factory()->count(5)->create();

        
        $this->assertEquals(5, $users->count());
    }



}
