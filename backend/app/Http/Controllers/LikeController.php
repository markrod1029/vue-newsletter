<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function togglePostLike(Post $post)
    {
        $user = auth()->user();
        $liked = $post->likes()->where('user_id', $user->id)->exists();

        if ($liked) {
            $post->likes()->where('user_id', $user->id)->delete();
            $liked = false;
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        return response()->json([
            'data' => [
                'liked' => $liked,
                'likes_count' => $post->likes()->count()
            ]
        ]);
    }

    public function toggleCommentLike(Comment $comment)
    {
        $user = auth()->user();
        $liked = $comment->likes()->where('user_id', $user->id)->exists();

        if ($liked) {
            $comment->likes()->where('user_id', $user->id)->delete();
            $liked = false;
        } else {
            $comment->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        return response()->json([
            'data' => [
                'liked' => $liked,
                'likes_count' => $comment->likes()->count()
            ]
        ]);
    }
}
