<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Thread;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $query = Forum::with(['creator', 'threads', 'threads.comments']);

        // Search
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $forums = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $forums]);
    }

    public function indexPublic(Request $request)
    {
        $query = Forum::with('creator')
            ->withCount('threads')
            ->addSelect([
                'comments_count' => Comment::selectRaw('COUNT(*)')
                    ->where('commentable_type', Thread::class)
                    ->whereIn('commentable_id', function ($query) {
                        $query->select('id')
                            ->from('threads')
                            ->whereColumn('threads.forum_id', 'forums.id');
                    })
            ]);

        // Search
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $forums = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $forums]);
    }

    public function store(Request $request)
    {
        // Only admins can create forums
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'is_locked' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $forum = Forum::create([
            'title' => $request->title,
            'description' => $request->description,
            'slug' => \Illuminate\Support\Str::slug($request->title), // Add this line
            'is_locked' => $request->is_locked ?? false,
            'created_by' => auth()->id()
        ]);

        return response()->json(['data' => $forum->load('creator')], 201);
    }

    public function show(Forum $forum)
    {
        return response()->json(['data' => $forum->load(['creator', 'threads', 'threads.author'])]);
    }

    public function update(Request $request, Forum $forum)
    {
        // Only admins can update forums
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'is_locked' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Regenerate slug if title is being updated
        if ($request->has('title') && $request->title !== $forum->title) {
            $data['slug'] = \Illuminate\Support\Str::slug($request->title);
        }

        $forum->update($data);

        return response()->json(['data' => $forum->load('creator')]);
    }

    public function destroy(Forum $forum)
    {
        // Only admins can delete forums
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $forum->delete();

        return response()->json(['message' => 'Forum deleted successfully']);
    }
}
