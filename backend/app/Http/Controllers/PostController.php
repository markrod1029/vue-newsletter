<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('user');

        // Filter by current user's posts if requested
        if ($request->query('my_posts')) {
            $query->where('user_id', auth()->id());
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('body', 'like', '%' . $request->search . '%');
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($posts);
    }

    public function feed(Request $request)
    {
        $query = Post::with('user')
            ->where('status', 'approved')
            ->whereNotNull('published_at');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('body', 'like', '%' . $request->search . '%');
        }

        // Limit
        $limit = $request->has('limit') ? $request->limit : 10;

        $posts = $query->orderBy('published_at', 'desc')->paginate($limit);

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|in:news,article',
            'cover_image_url' => 'nullable|url',
            'status' => 'required|in:draft,pending,approved,rejected,archived'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'body' => $request->body,
            'type' => $request->type,
            'status' => $request->status,
            'cover_image_url' => $request->cover_image_url,
            'published_at' => $request->status === 'approved' ? now() : null
        ]);

        return response()->json(['data' => $post->load('user')], 201);
    }

    public function show(Post $post)
    {
        // Only allow viewing approved posts or posts owned by the user
        if ($post->status !== 'approved' && $post->user_id !== auth()->id()) {
            if (!auth()->user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json(['data' => $post->load('user')]);
    }

    public function update(Request $request, Post $post)
    {
        // Check if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:news,article',
            'cover_image_url' => 'nullable|url',
            'status' => 'sometimes|required|in:draft,pending,approved,rejected,archived'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post->update($request->all());

        // Update published_at if status changed to approved
        if ($request->has('status') && $request->status === 'approved' && !$post->published_at) {
            $post->published_at = now();
            $post->save();
        }

        return response()->json(['data' => $post->load('user')]);
    }

    public function destroy(Post $post)
    {
        // Check if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function submit(Post $post)
    {
        // Check if user owns the post
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($post->status !== 'draft') {
            return response()->json(['message' => 'Only draft posts can be submitted'], 400);
        }

        $post->status = 'pending';
        $post->save();

        return response()->json(['message' => 'Post submitted for approval']);
    }
}
