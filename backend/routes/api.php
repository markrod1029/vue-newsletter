<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Public routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified!']);
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

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

        // Route::get('/user', [UserController::class, 'show']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    // Posts
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{post}/submit', [PostController::class, 'submit']);

    // Post Comments
    Route::get('posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);

    // Post Likes
    Route::post('posts/{post}/like', [LikeController::class, 'togglePostLike']);

    // Events
    Route::apiResource('events', EventController::class);

    // Forums
    Route::apiResource('forums', ForumController::class);

    // Threads
    Route::apiResource('threads', ThreadController::class);

    // Comments
    Route::apiResource('comments', CommentController::class);

    // Comment Likes
    Route::post('comments/{comment}/like', [LikeController::class, 'toggleCommentLike']);

    // Comment Replies
    Route::post('comments/{comment}/reply', [CommentController::class, 'storeReply']);

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
