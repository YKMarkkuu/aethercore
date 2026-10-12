<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Space;
use App\Models\SpaceBan;
use App\Models\SpaceChannel;
use App\Models\SpaceMember;
use App\Services\SpaceRoleSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpaceController extends Controller
{
    /**
     * "Your Spaces" list — only spaces you're already a member of.
     * There's no public browse/discover directory in v1; joining
     * happens via an invite card shared in the Feed (see partials.post-card)
     * or the /spaces/{space}/join route it links to.
     */
    public function index()
    {
        $spaces = Space::whereHas('members', function ($q) {
            $q->where('user_id', Auth::id());
        })->withCount('members')->get();

        return view('spaces.index', compact('spaces'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'icon' => 'nullable|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $space = Space::create([
            'owner_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->hasFile('icon')
                ? $request->file('icon')->store('space-icons', 'public')
                : null,
        ]);

        // Every Space starts with one default channel, same as Discord.
        SpaceChannel::create([
            'space_id' => $space->id,
            'name' => 'general',
            'position' => 0,
        ]);

        $ownerRole = SpaceRoleSeeder::seedDefaultRoles($space);

        SpaceMember::create([
            'space_id' => $space->id,
            'user_id' => Auth::id(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'space_id' => $space->id]);
        }

        return redirect()->route('spaces.show', $space);
    }

    /**
     * Edit a Space's name, description, and icon. Only fields present in
     * the request are touched (same pattern as SettingsController::
     * updatePrivacy()), so a name-only edit never clears the description.
     *
     * Authorized by the MANAGE_SPACE permission. The owner always passes
     * (Space::userHasPermission() short-circuits on isOwner()), and the
     * seeded Owner and Admin roles both carry MANAGE_SPACE.
     *
     * NOTE: browsers can't send multipart/form-data over a real PUT, so a
     * form that uploads an icon must POST with @method('PUT') /
     * _method=PUT. ajax-forms.js already does this via FormData.
     */
    public function update(Request $request, Space $space)
    {
        if (!$space->userHasPermission(Auth::id(), \App\Enums\SpacePermission::MANAGE_SPACE)) {
            abort(403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:300',
            'icon' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120', // no SVG
        ]);

        if ($request->has('name')) {
            $space->name = $request->name;
        }

        if ($request->has('description')) {
            $space->description = $request->description;
        }

        $oldIcon = null;
        if ($request->hasFile('icon')) {
            $oldIcon = $space->icon;
            $space->icon = $request->file('icon')->store('space-icons', 'public');
        }

        $space->save();

        // Delete the old file only after the new one is stored and saved,
        // and only if it lives in this feature's own folder.
        if ($oldIcon && str_starts_with($oldIcon, 'space-icons/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldIcon);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Space updated.',
                'space' => [
                    'id' => $space->id,
                    'name' => $space->name,
                    'description' => $space->description,
                    'icon_url' => $space->getIconUrl(),
                ],
            ]);
        }

        return back()->with('success', 'Space updated.');
    }

    /**
     * Shows the Space. If you're not a member yet (arrived via an
     * invite card from the Feed), shows a join prompt instead of the
     * channels/chat.
     */
    public function show(Space $space, ?SpaceChannel $channel = null)
    {
        $space->load(['members.user', 'channels']);

        $isMember = $space->isMember(Auth::id());

        if (!$isMember) {
            return view('spaces.join-prompt', compact('space'));
        }

        // Default to the first channel (lowest position — 'general' for
        // freshly-created spaces) if none was specified in the URL.
        $activeChannel = $channel ?? $space->channels->first();

        if ($activeChannel && $activeChannel->space_id !== $space->id) {
            abort(404);
        }

            $messages = $activeChannel
            ? $activeChannel->messages()->with(['user', 'replyTo.user'])->get()
            : collect();

        // Reaction data for the initial paint, same pattern as
        // ConversationController::show() — avoids pills "popping in" late
        // waiting on the first poll cycle.
        $reactionsByMessage = [];
        if ($messages->isNotEmpty()) {
            $rows = DB::table('space_message_reactions')
                ->whereIn('message_id', $messages->pluck('id'))
                ->get();

            foreach ($rows as $row) {
                if (!isset($reactionsByMessage[$row->message_id])) {
                    $reactionsByMessage[$row->message_id] = ['counts' => [], 'mine' => []];
                }
                $reactionsByMessage[$row->message_id]['counts'][$row->type] =
                    ($reactionsByMessage[$row->message_id]['counts'][$row->type] ?? 0) + 1;
                if ($row->user_id === Auth::id()) {
                    $reactionsByMessage[$row->message_id]['mine'][] = $row->type;
                }
            }
        }

        return view('spaces.show', [
            'space' => $space,
            'activeChannel' => $activeChannel,
            'messages' => $messages,
            'reactionsByMessage' => $reactionsByMessage,
            'isOwner' => $space->isOwner(Auth::id()),
        ]);
    }

    /**
     * Returns the Members tab's inner partial as HTML, for the generic
     * ajax-forms.js refresh pattern (see partials.space-members-modal).
     */
    public function membersTab(Space $space)
    {
        if (!$space->isMember(Auth::id())) {
            abort(403);
        }

        $space->load('members.user', 'roles');

        return view('partials.space-members.member-list', compact('space'));
    }

    public function join(Space $space)
    {
        $bannedFromSpace = SpaceBan::where('space_id', $space->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($bannedFromSpace) {
            abort(403, 'You are banned from this Space.');
        }

        if (!$space->isMember(Auth::id())) {
            SpaceMember::create([
                'space_id' => $space->id,
                'user_id' => Auth::id(),
                'role' => 'member',
            ]);
        }

        return redirect()->route('spaces.show', $space);
    }

    /**
     * Owners can't leave in v1 — there's no ownership-transfer flow yet,
     * so an ownerless Space would be stuck. They can delete it instead.
     */
    public function leave(Space $space)
    {
        if ($space->isOwner(Auth::id())) {
            return back()->withErrors(['space' => 'As the owner, delete the Space instead of leaving it.']);
        }

        SpaceMember::where('space_id', $space->id)
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('spaces.index');
    }

    public function destroy(Space $space)
    {
        if (!$space->isOwner(Auth::id())) {
            abort(403);
        }

        $space->delete();

        return redirect()->route('spaces.index');
    }

    /**
     * Posts an invite card for this Space to the Feed. Reuses the
     * existing Post model (shared_space_id) so it renders through the
     * same partials.post-card everything else in the feed already uses.
     */
    public function shareToFeed(Request $request, Space $space)
    {
        if (!$space->isMember(Auth::id())) {
            abort(403, 'You must be a member of a Space to share it.');
        }

        $request->validate([
            'content' => 'nullable|string|max:500',
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content ?? '',
            'shared_space_id' => $space->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post_id' => $post->id]);
        }

        return redirect()->route('feed')->with('success', 'Space invite shared to your feed!');
    }
}