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
            $query->where('user_id', operator: auth()->id());
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        // Search
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('body', 'like', '%' . $request->search . '%');
        }
        $limit = $request->get('limit', 10);

        $posts = $query->orderBy('created_at', 'desc')->paginate($limit);

     return response()->json([
            'data' => $posts->items(), // actual posts array
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }

    public function feed(Request $request)
    {

        $query = Post::with('user')
            ->where('status', 'approved')
            ->whereNotNull('published_at');

        if ($request->query('my_feeds')) {
            $query->where('user_id', operator: auth()->id());
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('body', 'like', '%' . $request->search . '%');
        }

        $limit = $request->get('limit', 10);

        $posts = $query->orderBy('published_at', 'desc')->paginate($limit);

        return response()->json([
            'data' => $posts->items(), // actual posts array
            'meta' => [
                'current_page' => $posts->currentPage(),
          'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
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
        // Allow viewing if:
        // 1. Post is approved, OR
        // 2. User owns the post, OR
        // 3. User is admin
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

        $data = $request->all();

        // Update published_at if status changed to approved
        if ($request->has('status') && $request->status === 'approved' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

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

    public function publicFeed(Request $request)
    {
        $query = Post::with('user')
            ->where('status', 'approved');

        // Search (only if not empty)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Optional: filter by type (e.g. news, article)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Pagination limit
        $limit = $request->get('limit', 10);

        $posts = $query->orderBy('published_at', 'desc')->paginate($limit);

        return response()->json($posts);
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
