<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_create_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'body' => 'This is a test body',
            'type' => 'news',
            'status' => 'draft'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['id', 'title', 'body', 'status', 'user']]);
    }

    /** @test */
    public function cannot_create_post_without_required_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/posts', []);
        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function user_can_only_update_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id, 'status' => 'draft']);
        $this->actingAs($user, 'sanctum');

        $response = $this->putJson("/api/posts/{$post->id}", ['title' => 'Updated Title']);
        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title']);
    }

    /** @test */
    public function non_owner_cannot_update_post()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $other->id, 'status' => 'draft']);
        $this->actingAs($user, 'sanctum');

        $response = $this->putJson("/api/posts/{$post->id}", ['title' => 'Updated Title']);
        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_submit_draft_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id, 'status' => 'draft']);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson("/api/posts/{$post->id}/submit");
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Post submitted for approval']);
    }
}
