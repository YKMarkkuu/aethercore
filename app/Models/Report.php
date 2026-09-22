<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['reporter_id', 'reportable_type', 'reportable_id', 'reason', 'details', 'status'];

    public const REASONS = ['spam', 'harassment', 'hate_speech', 'nudity', 'violence', 'other'];

    /**
     * Allowlist of everything a report can target. A future
     * ReportController should map the client's short type string
     * ("post", "comment", ...) through this array rather than trusting
     * a raw ::class string from the request — that's the difference
     * between "user reports a comment" and "user reports an arbitrary
     * model of their choosing."
     */
    public const REPORTABLE_TYPES = [
        'post' => Post::class,
        'comment' => Comment::class,
        'message' => Message::class,
        'space_message' => SpaceMessage::class,
        'user' => User::class,
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reportable()
    {
        return $this->morphTo();
    }
}
