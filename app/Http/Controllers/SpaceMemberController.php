<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\SpaceBan;
use App\Models\SpaceRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceMemberController extends Controller
{
    /**
     * KICK_MEMBERS is enforced by the space.permission middleware on
      * the route (Response 6) — this method only handles the
      * owner-protection and hierarchy rules, which are business rules
      * rather than permissions (no permission should ever override them).
     */
    public function kick(Space $space, User $user)
    {
        $this->guardAgainstOwner($space, $user);
          $this->guardAgainstHierarchy($space, $user);

        $space->removeMember($user->id);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "{$user->display_name} was removed from the Space."]);
        }

        return back()->with('success', "{$user->display_name} was removed from the Space.");
    }

    /**
    * BAN_MEMBERS is enforced by middleware. Records the ban and
    * removes the member the same way kick() does.
     */
    public function ban(Request $request, Space $space, User $user)
    {
        $this->guardAgainstOwner($space, $user);
        $this->guardAgainstHierarchy($space, $user);

        $request->validate([
            'reason' => 'nullable|string|max:300',
        ]);

        SpaceBan::updateOrCreate(
            ['space_id' => $space->id, 'user_id' => $user->id],
            ['banned_by' => Auth::id(), 'reason' => $request->reason]
        );

        $space->removeMember($user->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "{$user->display_name} was banned from the Space."]);
        }

        return back()->with('success', "{$user->display_name} was banned from the Space.");
    }

    /**
     * MANAGE_ROLES is enforced by middleware. The owner's role can
     * never be reassigned away from them through this endpoint, and
     * hierarchy still applies — an Admin cannot reassign another
     * Admin's (or higher's) role, only someone strictly below them.
     * The role being ASSIGNED is also hierarchy-checked (not just the
     * target member) — without this, an Admin could grant someone an
     * Owner-level or equal-to-Admin-level role despite not being able
     * to touch a member who already holds that role, which is a
     * privilege escalation path around the whole hierarchy system.
     */
    public function assignRole(Request $request, Space $space, User $user)
    {
        if ($space->isOwner($user->id)) {
            abort(403, 'The Space owner\'s role cannot be changed.');
        }

        if (!$space->isMember($user->id)) {
            abort(404, 'That user is not a member of this Space.');
        }

        $this->guardAgainstHierarchy($space, $user);

        $request->validate([
            'role_id' => 'required|integer|exists:space_roles,id',
        ]);

        // The role must actually belong to THIS space — otherwise a
        // crafted request could assign a role_id from a different
        // space the caller happens to manage.
        $role = SpaceRole::where('id', $request->role_id)
            ->where('space_id', $space->id)
            ->first();

        if (!$role) {
            abort(422, 'That role does not belong to this Space.');
        }

        $actorPosition = $space->getHighestRolePosition(Auth::id());

        if ($role->position >= $actorPosition) {
            abort(403, 'You cannot assign a role at or above your own level.');
        }

        $space->assignRole($user->id, $role->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "{$user->display_name} is now {$role->name}."]);
        }

        return back()->with('success', "{$user->display_name} is now {$role->name}.");
    }

    protected function guardAgainstOwner(Space $space, User $user): void
    {
        if ($space->isOwner($user->id)) {
            abort(403, 'The Space owner cannot be kicked or banned.');
        }

        if (!$space->isMember($user->id)) {
            abort(404, 'That user is not a member of this Space.');
        }
    }

    /**
     * Discord-style hierarchy: the actor can only act on a target whose
     * highest role position is strictly lower than their own. Owner
     * immunity is already handled separately (guardAgainstOwner /
     * assignRole's inline owner check) before this runs.
     */
    protected function guardAgainstHierarchy(Space $space, User $target): void
    {
        $actorPosition = $space->getHighestRolePosition(Auth::id());
        $targetPosition = $space->getHighestRolePosition($target->id);

        if ($targetPosition >= $actorPosition) {
            abort(403, 'You cannot act on someone with equal or higher role position.');
        }
    }
}
