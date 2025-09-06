<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'forum_id',
        'user_id',
        'title',
        'body',
        'status',
        'is_locked'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_locked' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_VISIBLE = 'visible';
    const STATUS_HIDDEN = 'hidden';
    const STATUS_LOCKED = 'locked';

    /**
     * Get the forum that owns the thread.
     */
    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * Get the user that owns the thread.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the comments for the thread.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Scope a query to only include visible threads.
     */
    public function scopeVisible($query)
    {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    /**
     * Scope a query to only include threads from a specific forum.
     */
    public function scopeFromForum($query, $forumId)
    {
        return $query->where('forum_id', $forumId);
    }

    /**
     * Scope a query to only include unlocked threads.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Get the number of comments in the thread.
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * Check if the thread can be accessed by a user.
     */
    public function canAccess(User $user = null)
    {
        // If thread is visible and not locked, anyone can access
        if ($this->status === self::STATUS_VISIBLE && !$this->is_locked) {
            return true;
        }

        // If user is admin, can access any thread
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        // If user is the author, can access their own thread
        if ($user && $user->id === $this->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if the thread can be commented on.
     */
    public function canComment(User $user = null)
    {
        // Check if forum is locked
        if ($this->forum->is_locked) {
            return false;
        }

        // Check if thread is locked
        if ($this->is_locked) {
            return false;
        }

        // Check if thread is visible
        if ($this->status !== self::STATUS_VISIBLE) {
            return false;
        }

        // Check if user is authenticated and approved
        if (!$user || $user->status !== 'approved') {
            return false;
        }

        return true;
    }
}
