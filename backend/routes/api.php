<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::get('/feed', [PostController::class, 'feed']); // temporary lang

// Public routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Public content routes
Route::middleware('auth:sanctum')->get('/feed', [PostController::class, 'feed']);

Route::get('/public-feed', [PostController::class, 'publicFeed']);
Route::get('/events/upcoming', [EventController::class, 'upcoming']);
Route::get('/forums-public', [ForumController::class, 'indexPublic']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });


    // Posts
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{post}/submit', [PostController::class, 'submit']);

    // Events
    Route::apiResource('events', EventController::class);

    // Forums
    Route::apiResource('forums', ForumController::class);

    // Threads
    Route::apiResource('threads', ThreadController::class);

    // Comments
    Route::apiResource('comments', CommentController::class);

    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/pending-students', [AdminController::class, 'pendingStudents']);
        Route::post('/approve-student/{user}', [AdminController::class, 'approveStudent']);
        Route::post('/reject-student/{user}', [AdminController::class, 'rejectStudent']);
        Route::get('/pending-content', [AdminController::class, 'pendingContent']);
        Route::post('/approve-content/{id}', [AdminController::class, 'approveContent']);
        Route::post('/reject-content/{id}', [AdminController::class, 'rejectContent']);
    });
});
