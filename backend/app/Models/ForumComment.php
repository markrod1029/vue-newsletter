<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'body',
        'status'
    ];

    /**
     * Status constants
     */
    const STATUS_VISIBLE = 'visible';
    const STATUS_HIDDEN = 'hidden';
    const STATUS_FLAGGED = 'flagged';

    /**
     * Get the parent commentable model (Post, Event, or Thread).
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include visible comments.
     */
    public function scopeVisible($query)
    {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    /**
     * Scope a query to only include comments for a specific type.
     */
    public function scopeForType($query, $type)
    {
        return $query->where('commentable_type', $type);
    }

    /**
     * Scope a query to only include comments from a specific user.
     */
    public function scopeFromUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if the comment can be viewed by a user.
     */
    public function canView(User $user = null)
    {
        // If comment is visible, anyone can view
        if ($this->status === self::STATUS_VISIBLE) {
            return true;
        }

        // If user is admin, can view any comment
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        // If user is the author, can view their own comment
        if ($user && $user->id === $this->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if the comment can be edited by a user.
     */
    public function canEdit(User $user = null)
    {
        if (!$user) {
            return false;
        }

        // User can edit their own comments
        if ($user->id === $this->user_id) {
            return true;
        }

        // Admins can edit any comments
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Check if the comment can be deleted by a user.
     */
    public function canDelete(User $user = null)
    {
        return $this->canEdit($user);
    }
}
