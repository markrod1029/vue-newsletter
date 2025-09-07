<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('user');
        // Filter by current user's events if requested
        if ($request->query('my_events')) {
            $query->where('user_id', auth()->id());
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $events = $query->orderBy('start_at', 'desc')->paginate(10);

        return response()->json($events);
    }

    public function upcoming(Request $request)
    {
        $query = Event::with('user')
            ->where('status', 'approved')
            ->where('start_at', '>', now());


        // Limit
        $limit = $request->has('limit') ? $request->limit : 10;

        $events = $query->orderBy('start_at', 'asc')->paginate($limit);

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => 'sometimes|in:pending,approved,rejected,archived'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Set default status for students
        $status = $request->status ?? 'pending';
        if (auth()->user()->hasRole('admin')) {
            $status = $request->status ?? 'approved';
        }

        $event = Event::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'status' => $status
        ]);

        return response()->json(['data' => $event->load('user')], 201);
    }

    public function show(Event $event)
    {
        // Only allow viewing approved events or events owned by the user
        if ($event->status !== 'approved' && $event->user_id !== auth()->id()) {
            if (!auth()->user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json(['data' => $event->load('user')]);
    }

    public function update(Request $request, Event $event)
    {
        // Check if user owns the event or is admin
        if ($event->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'start_at' => 'sometimes|required|date',
            'end_at' => 'sometimes|required|date|after:start_at',
            'status' => 'sometimes|in:pending,approved,rejected,archived'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());

        return response()->json(['data' => $event->load('user')]);
    }

    public function destroy(Event $event)
    {
        // Check if user owns the event or is admin
        if ($event->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
