<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $filter = $request->query('filter', 'all'); // all, active, suspended, banned

        $users = User::withCount('posts')
            ->when($search, fn ($q) => $q->where('username', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%"))
            ->when($filter === 'banned', fn ($q) => $q->whereNotNull('banned_at'))
            ->when($filter === 'suspended', fn ($q) => $q->whereNotNull('suspended_until')->where('suspended_until', '>', now()))
            ->when($filter === 'active', fn ($q) => $q->whereNull('banned_at')->where(
                fn ($q2) => $q2->whereNull('suspended_until')->orWhere('suspended_until', '<=', now())
            ))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'filter'));
    }

    /**
     * Moderation fields are deliberately not mass assignable. Assign them
     * directly here so only this controller can change account state.
     */
    public function suspend(Request $request, User $user)
    {
        $this->guardAgainstSelfOrAdmin($user);

        $request->validate(['days' => 'required|integer|min:1|max:365']);

        $user->suspended_until = now()->addDays((int) $request->days);
        $user->save();

        return back()->with('success', "{$user->display_name} suspended for {$request->days} day(s).");
    }

    public function unsuspend(User $user)
    {
        $user->suspended_until = null;
        $user->save();

        return back()->with('success', "{$user->display_name} unsuspended.");
    }

    public function ban(User $user)
    {
        $this->guardAgainstSelfOrAdmin($user);

        $user->banned_at = now();
        $user->save();

        return back()->with('success', "{$user->display_name} banned.");
    }

    public function unban(User $user)
    {
        $user->banned_at = null;
        $user->save();

        return back()->with('success', "{$user->display_name} unbanned.");
    }

    /**
     * Soft delete only (see the soft-deletes migration) — reversible
     * via User::withTrashed()->find($id)->restore() from tinker until
     * a dedicated "Deleted users" screen exists.
     */
    public function destroy(User $user)
    {
        $this->guardAgainstSelfOrAdmin($user);

        $user->delete();

        return back()->with('success', "{$user->display_name} deleted.");
    }

    protected function guardAgainstSelfOrAdmin(User $user): void
    {
        if ($user->id === Auth::id()) {
            abort(403, 'You cannot moderate your own account.');
        }

        if ($user->isAdmin()) {
            abort(403, 'Admins cannot be moderated from this screen.');
        }
    }
}
