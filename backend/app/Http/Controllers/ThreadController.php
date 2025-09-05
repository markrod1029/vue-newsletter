<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Forum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThreadController extends Controller
{
    public function index(Request $request)
    {
        $query = Thread::with(['author', 'forum', 'comments']);

        // Filter by forum
        if ($request->has('forum_id')) {
            $query->where('forum_id', $request->forum_id);
        }

        // Search
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('body', 'like', '%' . $request->search . '%');
        }

        $threads = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $threads]);
    }

    public function store(Request $request)
    {
        // Check if user is approved
        if (auth()->user()->status !== 'approved') {
            return response()->json(['message' => 'Your account needs to be approved before you can create threads'], 403);
        }

        $validator = Validator::make($request->all(), [
            'forum_id' => 'required|exists:forums,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'sometimes|in:visible,hidden,locked'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if forum is locked
        $forum = Forum::find($request->forum_id);
        if ($forum->is_locked) {
            return response()->json(['message' => 'This forum is locked'], 403);
        }

        $thread = Thread::create([
            'forum_id' => $request->forum_id,
            'user_id' => auth()->id(),
            'title' => $request->title,
            'body' => $request->body,
            'status' => $request->status ?? 'visible'
        ]);

        return response()->json(['data' => $thread->load(['author', 'forum'])], 201);
    }

    public function show(Thread $thread)
    {
        return response()->json(['data' => $thread->load(['author', 'forum', 'comments', 'comments.user'])]);
    }

    public function update(Request $request, Thread $thread)
    {
        // Check if user owns the thread or is admin
        if ($thread->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'status' => 'sometimes|in:visible,hidden,locked'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $thread->update($request->all());

        return response()->json(['data' => $thread->load(['author', 'forum'])]);
    }

    public function destroy(Thread $thread)
    {
        // Check if user owns the thread or is admin
        if ($thread->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $thread->delete();

        return response()->json(['message' => 'Thread deleted successfully']);
    }
}
