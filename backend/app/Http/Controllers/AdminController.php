<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Event;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // public function dashboard()
    // {
    //     $stats = [
    //         'pendingStudents' => User::where('status', 'pending')
    //             ->whereHas('roles', function ($query) {
    //                 $query->where('name', 'student');
    //             })
    //             ->count(),
    //         'pendingPosts' => Post::where('status', 'pending')->count(),
    //         'pendingEvents' => Event::where('status', 'pending')->count(),
    //         'totalUsers' => User::count()
    //     ];

    //     return response()->json($stats);
    // }

    public function dashboard(Request $request)
    {


        $stats = [
            'pendingStudents' => User::where('status', 'pending')
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->count(),
            'pendingPosts' => Post::where('status', 'pending')->count(),
            'pendingEvents' => Event::where('status', 'pending')->count(),
            'totalUsers' => User::count()
        ];

        return response()->json($stats);
    }

    public function pendingStudents()
    {
        $students = User::where('status', 'pending')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->get();

        return response()->json(['data' => $students]);
    }

    public function approveStudent(User $user)
    {
        if ($user->status !== 'pending') {
            return response()->json(['message' => 'User is not pending approval'], 400);
        }

        $user->status = 'approved';
        $user->save();

        // Create approval record
        Approval::create([
            'approvable_type' => User::class,
            'approvable_id' => $user->id,
            'decided_by' => auth()->id(),
            'decision' => 'approved',
            'decided_at' => now()
        ]);

        return response()->json(['message' => 'Student approved successfully']);
    }

    public function rejectStudent(User $user)
    {
        if ($user->status !== 'pending') {
            return response()->json(['message' => 'User is not pending approval'], 400);
        }

        $user->status = 'rejected';
        $user->save();

        // Create approval record
        Approval::create([
            'approvable_type' => User::class,
            'approvable_id' => $user->id,
            'decided_by' => auth()->id(),
            'decision' => 'rejected',
            'decided_at' => now()
        ]);

        return response()->json(['message' => 'Student rejected successfully']);
    }

    public function pendingContent(Request $request)
    {
        $type = $request->query('type', 'all');
        $content = [];

        if ($type === 'all' || $type === 'posts') {
            $posts = Post::where('status', 'pending')
                ->with('user')
                ->get();
            $content = array_merge($content, $posts->toArray());
        }

        if ($type === 'all' || $type === 'events') {
            $events = Event::where('status', 'pending')
                ->with('user')
                ->get();
            $content = array_merge($content, $events->toArray());
        }

        return response()->json(['data' => $content]);
    }

    public function approveContent(Request $request, $id)
    {
        $type = $request->input('type');

        if ($type === 'posts') {
            $post = Post::findOrFail($id);
            if ($post->status !== 'pending') {
                return response()->json(['message' => 'Post is not pending approval'], 400);
            }

            $post->status = 'approved';
            $post->published_at = now();
            $post->save();

            // Create approval record
            Approval::create([
                'approvable_type' => Post::class,
                'approvable_id' => $post->id,
                'decided_by' => auth()->id(),
                'decision' => 'approved',
                'decided_at' => now()
            ]);

            return response()->json(['message' => 'Post approved successfully']);
        } elseif ($type === 'events') {
            $event = Event::findOrFail($id);
            if ($event->status !== 'pending') {
                return response()->json(['message' => 'Event is not pending approval'], 400);
            }

            $event->status = 'approved';
            $event->save();

            // Create approval record
            Approval::create([
                'approvable_type' => Event::class,
                'approvable_id' => $event->id,
                'decided_by' => auth()->id(),
                'decision' => 'approved',
                'decided_at' => now()
            ]);

            return response()->json(['message' => 'Event approved successfully']);
        }

        return response()->json(['message' => 'Invalid content type'], 400);
    }

    public function rejectContent(Request $request, $id)
    {
        $type = $request->input('type');

        if ($type === 'posts') {
            $post = Post::findOrFail($id);
            if ($post->status !== 'pending') {
                return response()->json(['message' => 'Post is not pending approval'], 400);
            }

            $post->status = 'rejected';
            $post->save();

            // Create approval record
            Approval::create([
                'approvable_type' => Post::class,
                'approvable_id' => $post->id,
                'decided_by' => auth()->id(),
                'decision' => 'rejected',
                'decided_at' => now()
            ]);

            return response()->json(['message' => 'Post rejected successfully']);
        } elseif ($type === 'events') {
            $event = Event::findOrFail($id);
            if ($event->status !== 'pending') {
                return response()->json(['message' => 'Event is not pending approval'], 400);
            }

            $event->status = 'rejected';
            $event->save();

            // Create approval record
            Approval::create([
                'approvable_type' => Event::class,
                'approvable_id' => $event->id,
                'decided_by' => auth()->id(),
                'decision' => 'rejected',
                'decided_at' => now()
            ]);

            return response()->json(['message' => 'Event rejected successfully']);
        }

        return response()->json(['message' => 'Invalid content type'], 400);
    }

    public function recentActivity()
    {
        $activity = Approval::with(['approvable', 'decider'])
            ->orderBy('decided_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'title' => $this->getActivityTitle($approval),
                    'description' => $this->getActivityDescription($approval),
                    'created_at' => $approval->decided_at
                ];
            });

        return response()->json($activity);
    }

    private function getActivityTitle($approval)
    {
        $type = class_basename($approval->approvable_type);
        $action = $approval->decision === 'approved' ? 'Approved' : 'Rejected';

        return "{$action} {$type}";
    }

    private function getActivityDescription($approval)
    {
        $approvable = $approval->approvable;
        $decider = $approval->decider;

        if ($approval->approvable_type === User::class) {
            return "Student: {$approvable->name} by {$decider->name}";
        } elseif ($approval->approvable_type === Post::class) {
            return "Post: {$approvable->title} by {$decider->name}";
        } elseif ($approval->approvable_type === Event::class) {
            return "Event: {$approvable->title} by {$decider->name}";
        }

        return "Unknown activity";
    }
}
