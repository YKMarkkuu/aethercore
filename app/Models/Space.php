<?php

namespace App\Models;

use App\Enums\SpacePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'description', 'icon'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function channels()
    {
        return $this->hasMany(SpaceChannel::class)->orderBy('position');
    }

    public function members()
    {
        return $this->hasMany(SpaceMember::class);
    }

    public function roles()
    {
        return $this->hasMany(SpaceRole::class)->orderByDesc('position');
    }

    public function isMember($userId): bool
    {
        return $this->members->contains('user_id', $userId);
    }

    public function isOwner($userId): bool
    {
        return $this->owner_id === $userId;
    }

    public function getIconUrl(): ?string
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }

    /**
     * The role held by a given member. Falls back to the space's default
     * role (is_default = true) when the member has no role_id set yet —
     * e.g. a member created before this system existed, or one that
     * lost its role via nullOnDelete. Returns null only if the space
     * somehow has no default role at all (shouldn't happen once
     * Response 3's seeder runs on every space).
     */
    public function getUserRole(int $userId): ?SpaceRole
    {
        $member = $this->members->firstWhere('user_id', $userId);

        if (!$member) {
            return null;
        }

        if ($member->role_id) {
            return SpaceRole::find($member->role_id);
        }

        return $this->roles()->where('is_default', true)->first();
    }

    /**
     * Owners always pass every permission check, regardless of what
     * role (if any) they hold — ownership is out-of-band from the role
     * system, same as isOwner() already treats it elsewhere.
     */
    public function userHasPermission(int $userId, SpacePermission $permission): bool
    {
        if ($this->isOwner($userId)) {
            return true;
        }

        $role = $this->getUserRole($userId);

        return $role !== null && $role->hasPermission($permission->value);
    }

    /**
     * Used for hierarchy checks (guardAgainstHierarchy in
     * SpaceMemberController) — how "senior" a user is, for deciding
     * whether they're allowed to act on another member. Owner is
     * hardcoded above any real role position (roles top out at 100 for
     * the seeded Owner role, so 999 keeps the actual owner unambiguously
     * senior to everyone even if a space's roles are later
     * re-numbered). A user with no role falls back to 0, same as the
     * seeded default Member role's position.
     */
    public function getHighestRolePosition(int $userId): int
    {
        if ($this->isOwner($userId)) {
            return 999;
        }

        $role = $this->getUserRole($userId);

        return $role->position ?? 0;
    }

    /**
     * Reassigns a member's role. Does not itself check whether the
     * caller is allowed to do this — that's the responsibility of the
     * controller/middleware (Response 4/5), same division of concerns
     * as removeMember() below.
     */
    public function assignRole(int $userId, int $roleId): void
    {
        $this->members()->where('user_id', $userId)->update(['role_id' => $roleId]);
    }

    public function removeMember(int $userId): void
    {
        $this->members()->where('user_id', $userId)->delete();
    }
}