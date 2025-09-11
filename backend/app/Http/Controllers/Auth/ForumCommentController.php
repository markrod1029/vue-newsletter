<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumCommentController extends Controller
{
    public function store(Request $request)
    {
        // Check if user is approved
        if (auth()->user()->status !== 'approved') {
            return response()->json(['message' => 'Your account needs to be approved before you can comment'], 403);
        }

        $validator = Validator::make($request->all(), [
            'commentable_type' => 'required|in:Post,Event,Thread',
            'commentable_id' => 'required|integer',
            'body' => 'required|string',
            'status' => 'sometimes|in:visible,hidden,flagged'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the commentable model exists and is visible
        $modelClass = 'App\\Models\\' . $request->commentable_type;
        $commentable = $modelClass::find($request->commentable_id);

        if (!$commentable) {
            return response()->json(['message' => 'The requested content was not found'], 404);
        }

        // Check if the content is approved/visible
        if (property_exists($commentable, 'status') && $commentable->status !== 'approved') {
            if ($commentable->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
                return response()->json(['message' => 'This content is not available for comments'], 403);
            }
        }

        $comment = Comment::create([
            'commentable_type' => $modelClass,
            'commentable_id' => $request->commentable_id,
            'user_id' => auth()->id(),
            'body' => $request->body,
            'status' => $request->status ?? 'visible'
        ]);

        return response()->json(['data' => $comment->load('user')], 201);
    }

    public function update(Request $request, Comment $comment)
    {
        // Check if user owns the comment or is admin
        if ($comment->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'sometimes|required|string',
            'status' => 'sometimes|in:visible,hidden,flagged'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment->update($request->all());

        return response()->json(['data' => $comment->load('user')]);
    }

    public function destroy(Comment $comment)
    {
        // Check if user owns the comment or is admin
        if ($comment->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
