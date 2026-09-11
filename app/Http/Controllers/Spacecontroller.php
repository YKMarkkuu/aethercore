<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Space;
use App\Models\SpaceChannel;
use App\Models\SpaceMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'icon' => 'nullable|image|max:5120',
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

        SpaceMember::create([
            'space_id' => $space->id,
            'user_id' => Auth::id(),
            'role' => 'owner',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'space_id' => $space->id]);
        }

        return redirect()->route('spaces.show', $space);
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
            ? $activeChannel->messages()->with('user')->get()
            : collect();

        return view('spaces.show', [
            'space' => $space,
            'activeChannel' => $activeChannel,
            'messages' => $messages,
            'isOwner' => $space->isOwner(Auth::id()),
        ]);
    }

    public function join(Space $space)
    {
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