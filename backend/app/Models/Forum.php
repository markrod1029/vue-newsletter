<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'slug',
        'is_locked',
        'created_by'
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
     * Get the user who created the forum.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($forum) {
            if (empty($forum->slug)) {
                $forum->slug = \Illuminate\Support\Str::slug($forum->title);

                // Ensure slug is unique
                $originalSlug = $forum->slug;
                $count = 1;
                while (Forum::where('slug', $forum->slug)->exists()) {
                    $forum->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($forum) {
            if ($forum->isDirty('title') && empty($forum->slug)) {
                $forum->slug = \Illuminate\Support\Str::slug($forum->title);

                // Ensure slug is unique
                $originalSlug = $forum->slug;
                $count = 1;
                while (Forum::where('slug', $forum->slug)->where('id', '!=', $forum->id)->exists()) {
                    $forum->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }
    /**
     * Get the threads for the forum.
     */
    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    /**
     * Get all comments for the forum through threads.
     */
    // public function comments()
    // {
    //     return $this->hasManyThrough(Comment::class, Thread::class);
    // }

    public function comments()
    {
        // Since we have polymorphic relationships, we need to query differently
        return Comment::whereHasMorph(
            'commentable',
            [Thread::class],
            function ($query) {
                $query->where('forum_id', $this->id);
            }
        );
    }



    /**
     * Scope a query to only include unlocked forums.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Scope a query to only include locked forums.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Get the number of threads in the forum.
     */
    public function getThreadsCountAttribute()
    {
        return $this->threads()->count();
    }

    /**
     * Get the number of comments in the forum.
     */
    // public function getCommentsCountAttribute()
    // {
    //     return $this->comments()->count();
    // }

    public function getCommentsCountAttribute()
    {
        return Comment::whereHasMorph(
            'commentable',
            [Thread::class],
            function ($query) {
                $query->where('forum_id', $this->id);
            }
        )->count();
    }

    /**
     * Check if the forum can be accessed by a user.
     */
    public function canAccess(User $user = null)
    {
        // If forum is not locked, anyone can access
        if (!$this->is_locked) {
            return true;
        }

        // If forum is locked, only admins can access
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        return false;
    }
}
