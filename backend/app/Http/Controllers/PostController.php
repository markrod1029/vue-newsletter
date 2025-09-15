<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('user');

        // Filter by current user's posts if requested
        if ($request->query('my_posts')) {
            $query->where('user_id', auth()->id()); // Fixed syntax
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('body', 'like', '%' . $request->search . '%');
            });
        }

        $limit = $request->get('limit', 10);
        $posts = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json([
            'data' => $posts->items(),
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
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480', // 20MB max
            'media_type' => 'nullable|in:image,video',
            'status' => 'required|in:draft,pending,approved,rejected,archived'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'user_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'body' => $request->body,
            'type' => $request->type,
            'status' => $request->status,
            'published_at' => $request->status === 'approved' ? now() : null
        ];

        // Handle media upload
        if ($request->hasFile('media')) {
            $media = $request->file('media');
            $mediaName = time() . '_' . $media->getClientOriginalName();

            // Store in different folders based on media type
            $folder = $request->media_type === 'video' ? 'videos' : 'images';
            $mediaPath = $media->storeAs("posts/$folder", $mediaName, 'public');

            $data['media_url'] = '/storage/' . $mediaPath;
            $data['media_type'] = $request->media_type;
        }

        $post = Post::create($data);

        return response()->json(['data' => $post->load('user')], 201);
    }



public function show(Post $post)
{
    // Allow viewing if:
    // 1. Post is approved, OR
    // 2. User owns the post, OR
    // 3. User is admin
    if ($post->status !== 'approved' && $post->user_id !== auth()->id()) {
        if (!auth()->user() || !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }

    // load relationships
    $post->load(['user', 'likes', 'comments']);

    // determine if current user liked the post
    $isLiked = auth()->check()
        ? $post->likes->contains('user_id', auth()->id())
        : false;

    return response()->json([
        'data' => [
            'id'             => $post->id,
            'title'          => $post->title,
            'slug'           => $post->slug,
            'body'           => $post->body,
            'media_url'      => $post->media_url,
            'media_type'     => $post->media_type,
            'type'           => $post->type,
            'status'         => $post->status,
            'published_at'   => $post->published_at,
            'user'           => $post->user,
            'likes_count'    => $post->likes->count(),
            'comments_count' => $post->comments->count(),
            'is_liked'       => $isLiked,
            'created_at'     => $post->created_at,
            'updated_at'     => $post->updated_at,
        ]
    ]);
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
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
            'media_type' => 'nullable|in:image,video',
            'status' => 'sometimes|required|in:draft,pending,approved,rejected,archived'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['media']);

        // Handle media upload
        if ($request->hasFile('media')) {
            // Delete old media if exists
            if ($post->media_url) {
                $oldMediaPath = str_replace('/storage/', '', $post->media_url);
                Storage::disk('public')->delete($oldMediaPath);
            }

            $media = $request->file('media');
            $mediaName = time() . '_' . $media->getClientOriginalName();

            // Store in different folders based on media type
            $folder = $request->media_type === 'video' ? 'videos' : 'images';
            $mediaPath = $media->storeAs("posts/$folder", $mediaName, 'public');

            $data['media_url'] = '/storage/' . $mediaPath;
            $data['media_type'] = $request->media_type;
        }

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
