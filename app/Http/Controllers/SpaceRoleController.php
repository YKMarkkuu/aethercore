<?php

namespace App\Http\Controllers;

use App\Enums\SpacePermission;
use App\Models\Space;
use App\Models\SpaceRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SpaceRoleController extends Controller
{
    public function rolesTab(Space $space)
    {
        if (!$space->isMember(Auth::id())) {
            abort(403);
        }

        $space->load('roles', 'members');

        return view('partials.space-members.roles-tab', compact('space'));
    }

    public function store(Request $request, Space $space)
    {
        $actorPosition = $space->getHighestRolePosition(Auth::id());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('space_roles')->where(fn ($q) => $q->where('space_id', $space->id))],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => ['required', 'integer', 'min:0'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(SpacePermission::all())],
        ]);

        if ($validated['position'] >= $actorPosition) {
            abort(403, 'You cannot create a role at or above your own level.');
        }

        $role = SpaceRole::create([
            'space_id' => $space->id,
            'name' => $validated['name'],
            'color' => $validated['color'],
            'position' => $validated['position'],
            'permissions' => $validated['permissions'] ?? [],
            'is_default' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Role \"{$role->name}\" created.",
                'role_id' => $role->id,
                'refresh' => [
                    'rolesTabPane' => route('space-roles.tab', $space),
                    'membersTabPane' => route('space-members.tab', $space),
                ],
            ]);
        }

        return back()->with('success', "Role \"{$role->name}\" created.");
    }

    public function update(Request $request, Space $space, SpaceRole $role)
    {
        if ($role->space_id !== $space->id) {
            abort(404);
        }

        if ($role->is_owner) {
            abort(403, 'The Owner role cannot be edited.');
        }

        $actorPosition = $space->getHighestRolePosition(Auth::id());

        if ($role->position >= $actorPosition) {
            abort(403, 'You cannot edit a role at or above your own level.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('space_roles')->where(fn ($q) => $q->where('space_id', $space->id))->ignore($role->id)],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => ['required', 'integer', 'min:0'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(SpacePermission::all())],
        ]);

        if ($validated['position'] >= $actorPosition) {
            abort(403, 'You cannot move a role to or above your own level.');
        }

        $role->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'position' => $validated['position'],
            'permissions' => $validated['permissions'] ?? [],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Role \"{$role->name}\" updated.",
                'refresh' => [
                    'rolesTabPane' => route('space-roles.tab', $space),
                    'membersTabPane' => route('space-members.tab', $space),
                ],
            ]);
        }

        return back()->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(Request $request, Space $space, SpaceRole $role)
    {
        if ($role->space_id !== $space->id) {
            abort(404);
        }

        if ($role->is_owner) {
            abort(403, 'The Owner role cannot be deleted.');
        }

        if ($role->is_default) {
            abort(403, 'The default role cannot be deleted.');
        }

        $actorPosition = $space->getHighestRolePosition(Auth::id());

        if ($role->position >= $actorPosition) {
            abort(403, 'You cannot delete a role at or above your own level.');
        }

        $roleName = $role->name;

        $space->members()->where('role_id', $role->id)->update(['role_id' => null]);
        $role->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Role \"{$roleName}\" deleted.",
                'refresh' => [
                    'rolesTabPane' => route('space-roles.tab', $space),
                    'membersTabPane' => route('space-members.tab', $space),
                ],
            ]);
        }

        return back()->with('success', "Role \"{$roleName}\" deleted.");
    }
}