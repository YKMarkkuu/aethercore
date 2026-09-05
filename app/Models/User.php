<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
        protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'status',
        'theme',
        'lastfm_username',
        'lastfm_data',
        'lastfm_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'lastfm_data' => 'array',
        'lastfm_updated_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====
    
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class)->orderBy('created_at', 'desc');
    }

    // ===== STATUS =====

    /**
     * Get the user's status label.
     */
    public function getStatusLabel()
    {
        $statuses = [
            'online' => 'Online',
            'idle' => 'Idle',
            'dnd' => 'Do Not Disturb',
            'offline' => 'Offline',
        ];
        return $statuses[$this->status] ?? 'Online';
    }

    /**
     * Get the user's status color.
     */
    public function getStatusColor()
    {
        $colors = [
            'online' => '#4ade80',
            'idle' => '#fbbf24',
            'dnd' => '#ef4444',
            'offline' => '#6b7280',
        ];
        return $colors[$this->status] ?? '#4ade80';
    }

    /**
     * Get the user's status icon.
     */
    public function getStatusIcon()
    {
        $icons = [
            'online' => '●',
            'idle' => '◐',
            'dnd' => '●',
            'offline' => '○',
        ];
        return $icons[$this->status] ?? '●';
    }

        // ===== THEME =====

        public function getTheme()
        {
            return $this->theme ?? 'aethercore';
        }

        public function getAvailableThemes()
        {
            return [
                'aethercore' => 'AetherCore (XP)',
                'midnight' => 'Midnight (Dark)',
                'daylight' => 'Daylight (Light)',
                'retro' => 'Retro Terminal',
            ];
        }
    

    // ===== FRIENDSHIP RELATIONSHIPS =====
    
    public function sentFriends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
                    ->wherePivot('status', 'accepted');
    }

    public function receivedFriends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'friend_id', 'user_id')
                    ->wherePivot('status', 'accepted');
    }

    public function getFriends()
    {
        $sentIds = $this->sentFriends()->pluck('users.id')->toArray();
        $receivedIds = $this->receivedFriends()->pluck('users.id')->toArray();
        $friendIds = array_unique(array_merge($sentIds, $receivedIds));
        
        return User::whereIn('id', $friendIds)->get();
    }

    public function friendRequests()
    {
        return $this->belongsToMany(User::class, 'friendships', 'friend_id', 'user_id')
                    ->wherePivot('status', 'pending');
    }

    public function sentFriendRequests()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
                    ->wherePivot('status', 'pending');
    }

    public function isFriendWith($userId)
    {
        $isFriend = DB::table('friendships')
            ->where(function($query) use ($userId) {
                $query->where('user_id', $this->id)
                    ->where('friend_id', $userId)
                    ->where('status', 'accepted');
            })
            ->orWhere(function($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('friend_id', $this->id)
                    ->where('status', 'accepted');
            })
            ->exists();
        
        return $isFriend;
    }

    // ===== SPACES RELATIONSHIP (Future) =====
    
    public function spaces()
    {
        return $this->belongsToMany(Space::class, 'space_members', 'user_id', 'space_id');
    }

    // ===== DISPLAY NAME =====
    
    public function getDisplayNameAttribute()
    {
        return $this->profile->display_name ?? $this->name;
    }

    // ===== CHAT RELATIONSHIPS =====

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
                    ->withPivot('last_read_at', 'role')
                    ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getConversationWith($userId)
    {
        return Conversation::getOrCreateDirectConversation($this->id, $userId);
    }

    public function getUnreadMessagesCount()
    {
        return Message::whereIn('conversation_id', $this->conversations()->pluck('conversations.id'))
            ->where('user_id', '!=', $this->id)
            ->where('is_read', false)
            ->count();
    }

        /**
     * Get the user's avatar URL, or fallback to a default.
     */
    public function getAvatarUrl()
    {
        if ($this->profile && $this->profile->avatar) {
            return asset('storage/' . $this->profile->avatar);
        }
        
        // Return a default avatar (first letter with gradient)
        return null;
    }

    /**
     * Get the user's banner URL, or fallback to a default.
     */
    public function getBannerUrl()
    {
        if ($this->profile && $this->profile->banner) {
            return asset('storage/' . $this->profile->banner);
        }
        
        return null;
    }
}