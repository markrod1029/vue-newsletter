<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'decided_by',
        'decision',
        'reason',
        'decided_at'
    ];

    /**
     * Decision constants
     */
    const DECISION_APPROVED = 'approved';
    const DECISION_REJECTED = 'rejected';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'decided_at' => 'datetime',
    ];

    /**
     * Get the parent approvable model (User, Post, Event, or Thread).
     */
    public function approvable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who made the decision.
     */
    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Scope a query to only include approvals for a specific type.
     */
    public function scopeForType($query, $type)
    {
        return $query->where('approvable_type', $type);
    }

    /**
     * Scope a query to only include approved decisions.
     */
    public function scopeApproved($query)
    {
        return $query->where('decision', self::DECISION_APPROVED);
    }

    /**
     * Scope a query to only include rejected decisions.
     */
    public function scopeRejected($query)
    {
        return $query->where('decision', self::DECISION_REJECTED);
    }

    /**
     * Scope a query to only include recent approvals.
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('decided_at', 'desc')->limit($limit);
    }

    /**
     * Check if the approval is for a user.
     */
    public function isForUser()
    {
        return $this->approvable_type === User::class;
    }

    /**
     * Check if the approval is for a post.
     */
    public function isForPost()
    {
        return $this->approvable_type === Post::class;
    }

    /**
     * Check if the approval is for an event.
     */
    public function isForEvent()
    {
        return $this->approvable_type === Event::class;
    }

    /**
     * Check if the approval is for a thread.
     */
    public function isForThread()
    {
        return $this->approvable_type === Thread::class;
    }

    /**
     * Get the display name of the approvable type.
     */
    public function getApprovableTypeNameAttribute()
    {
        $types = [
            User::class => 'User',
            Post::class => 'Post',
            Event::class => 'Event',
            Thread::class => 'Thread'
        ];

        return $types[$this->approvable_type] ?? class_basename($this->approvable_type);
    }
}
