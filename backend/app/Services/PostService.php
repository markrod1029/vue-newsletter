<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Str;

class PostService
{
    public function createPost(array $data, int $userId): Post
    {
        return Post::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . uniqid(),
            'body' => $data['body'],
            'type' => $data['type'],
            'status' => $data['status'],
            'cover_image_url' => $data['cover_image_url'] ?? null,
            'published_at' => $data['status'] === 'approved' ? now() : null,
        ]);
    }

    public function updatePost(Post $post, array $data): Post
    {
        if (isset($data['status']) && $data['status'] === 'approved' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);
        return $post;
    }

    public function submitPost(Post $post): bool
    {
        if ($post->status !== 'draft') return false;
        $post->status = 'pending';
        $post->save();
        return true;
    }

    public function deletePost(Post $post): void
    {
        $post->delete();
    }

    public function queryPosts(array $filters = [])
    {
        $query = Post::with('user');

        if (!empty($filters['user_id'])) $query->where('user_id', $filters['user_id']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['type'])) $query->where('type', $filters['type']);
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                                       ->orWhere('body', 'like', "%{$search}%"));
        }

        return $query;
    }
}
