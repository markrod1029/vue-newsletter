<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_if_approved()
    {
        $user = User::factory()->create([
            'email' => 'approved@example.com',
            'password' => Hash::make('password'),
            'status' => 'approved',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'approved@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user', 'token']);
    }

    /** @test */
    public function user_cannot_login_if_not_approved()
    {
        $user = User::factory()->create([
            'email' => 'pending@example.com',
            'password' => Hash::make('password'),
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Your account is pending approval. Please wait for admin approval.']);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid credentials']);
    }
}
