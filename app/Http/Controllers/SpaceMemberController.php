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
     * owner-protection rule, which is a business rule rather than a
     * permission (no permission should ever be able to override it).
     */
    public function kick(Space $space, User $user)
    {
        $this->guardAgainstOwner($space, $user);

        $space->removeMember($user->id);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "{$user->display_name} was removed from the Space."]);
        }

        return back()->with('success', "{$user->display_name} was removed from the Space.");
    }

    /**
     * BAN_MEMBERS is enforced by middleware. Records the ban (see the
     * flag above re: join() not yet checking this table) and removes
     * the member the same way kick() does.
     */
    public function ban(Request $request, Space $space, User $user)
    {
        $this->guardAgainstOwner($space, $user);

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
     * never be reassigned away from them through this endpoint — same
     * "cannot be demoted" rule as the kick/ban owner guard, just phrased
     * for role assignment instead of removal.
     */
    public function assignRole(Request $request, Space $space, User $user)
    {
        if ($space->isOwner($user->id)) {
            abort(403, 'The Space owner\'s role cannot be changed.');
        }

        if (!$space->isMember($user->id)) {
            abort(404, 'That user is not a member of this Space.');
        }

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
}
