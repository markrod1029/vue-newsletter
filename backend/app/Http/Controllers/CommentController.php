<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->with(['user', 'likes'])
            ->withCount('likes')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add is_liked status for authenticated users
        if (auth()->check()) {
            $comments->each(function ($comment) {
                $comment->is_liked = $comment->likes->contains('user_id', auth()->id());
            });
        }

        return response()->json(['data' => $comments]);
    }

    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content
        ]);

        return response()->json(['data' => $comment->load('user')], 201);
    }
}
