<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\BmdAuth;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();        
        $this->initRandomUser();
        $this->setRandomUserManagerUser();
    }



    /** @test */
    public function it_responds_validation_exception_when_non_existent_user_signs_in()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $response = $this->post('/api/auth/signIn', [
            'email' => 'test@test.com',
            'password' => 'abcd1234',
        ]);
    }



    /** @test */
    public function it_responds_403_when_non_manager_user_signs_in()
    {
        // $this->expectException(HttpException::class);

        $response = $this->postJson('/api/auth/signIn', [
            'email' => $this->sampleUser->email,
            'password' => $this->sampleUserUnhashedPassword
        ]);


        $response->assertStatus(403);
    }



    /** @test */
    public function it_signs_in_manager_user()
    {
        $response = $this->postJson('/api/auth/signIn', [
            'email' => $this->sampleUserManager->email,
            'password' => $this->sampleUserUnhashedPassword
        ]);

        $updatedBmdAuthOfSignedInManager = BmdAuth::where('user_id', $this->sampleUserManager->id)->get()[0];

        $response
            ->assertStatus(200)
            ->assertJson([
                'isResultOk' => true,
                'resultCode' => AuthController::LOGIN_RESULT_CODE_SUCCESS,
                'objs' => [
                    'bmdToken' => $updatedBmdAuthOfSignedInManager->token,
                    'authProviderId' => 1
                ]
            ]);
    }
}
