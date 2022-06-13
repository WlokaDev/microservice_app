<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testRegister() : void
    {
        $data = [
            'name' => $this->faker->userName,
            'email' => $this->faker->email,
            'password' => $this->faker->password
        ];

        $data['password_confirmation'] = $data['password'];

        $response = $this->post('api/v1/auth/register', $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas(User::class, [
            'email' => $data['email'],
            'email_verified_at' => null
        ]);
    }

    public function testLogin()
    {
        $user = User::factory()->create([
            'password' => Hash::make('haslo1234')
        ]);

        $response = $this->post('api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'haslo1234'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'user' => [
                    'name',
                    'email',
                    'id'
                ],
                'access_token'
            ],
            'code'
        ]);
    }

    public function testLogout()
    {
        $user = User::factory()->create();

        Sanctum::actingAs(
            $user
        );

        $this->post('/api/v1/auth/logout')
        ->assertStatus(200);
    }


    public function testForgotPassword()
    {
        $user = User::factory()->create();

        $this->post('/api/v1/auth/forgot-password', [
            'email' => $user->email
        ])
            ->assertStatus(200);

        $this->assertDatabaseHas('password_resets', [
            'email' => $user->email
        ]);
    }

    public function testResetPassword()
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);
        $password = $this->faker->password;

        $this->post('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password
        ])
            ->assertStatus(200);

        $user->refresh();

        $this->assertEquals(
            true,
            Hash::check($password, $user->password)
        );
    }
}


