<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\SpaceChannelController;
use App\Http\Controllers\SpaceMessageController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;


// ============================================
// AUTH ROUTES (Breeze)
// ============================================
require __DIR__.'/auth.php';


// ============================================
// PUBLIC ROUTES
// ============================================
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/terms', function () {
    return view('legal.terms');
})->name('legal.terms');

Route::get('/privacy', function () {
    return view('legal.privacy');
})->name('legal.privacy');


// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware(['auth'])->group(function () {

    // ---------- FRIENDS (MUST BE FIRST!) ----------
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::middleware(['throttle:actions'])->group(function () {
        Route::post('/friends/request/{user}', [FriendController::class, 'sendRequest'])->name('friends.request');
        Route::post('/friends/accept/{user}', [FriendController::class, 'acceptRequest'])->name('friends.accept');
        Route::post('/friends/reject/{user}', [FriendController::class, 'rejectRequest'])->name('friends.reject');
    });

    // ---------- PROFILE ----------
    Route::get('/profile/edit', function () {
        return redirect()->route('profile.index');
    })->name('profile.edit');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/{user}/popover', [ProfileController::class, 'popover'])->name('profile.popover');
    Route::get('/users/{user}/popover', [ProfileController::class, 'popover'])->name('users.popover');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/stats-period', [ProfileController::class, 'updateStatsPeriod'])->name('profile.stats-period');
    Route::post('/profile/top-friends', [ProfileController::class, 'updateTopFriends'])->name('profile.top-friends');
    Route::post('/status/update', [StatusController::class, 'update'])->name('status.update');

    Route::post('/presence/heartbeat', [PresenceController::class, 'heartbeat'])->name('presence.heartbeat');
    Route::post('/presence/offline', [PresenceController::class, 'goOffline'])->name('presence.offline');

    // ---------- NOW PLAYING (polling) ----------
    Route::get('/now-playing/{user}', [ProfileController::class, 'nowPlaying'])->name('now-playing');
    Route::get('/now-playing-batch', [ProfileController::class, 'nowPlayingBatch'])->name('now-playing.batch');

    // ---------- SETTINGS ----------
    Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/account', [SettingsController::class, 'updateAccount'])->name('settings.account');
    Route::post('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');
    Route::post('/settings/lastfm', [SettingsController::class, 'connectLastfm'])->name('settings.lastfm');
    Route::delete('/settings/delete', [SettingsController::class, 'deleteAccount'])->name('settings.delete');
});

    // ---------- FEED ----------
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');

    // ---------- POSTS ----------
    Route::middleware(['throttle:actions'])->group(function () {
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('/posts/{post}/like', [PostController::class, 'toggleLike'])->name('posts.like');
        Route::post('/posts/{post}/repost', [PostController::class, 'repost'])->name('posts.repost');
        Route::post('/posts/{post}/share-to-chat', [PostController::class, 'shareToChat'])->name('posts.share-to-chat');
        Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('/posts/updates', [PostController::class, 'updates'])->name('posts.updates');
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    });

    // ---------- SPACES ----------
    Route::get('/spaces', [SpaceController::class, 'index'])->name('spaces');
    Route::get('/spaces/{space}', [SpaceController::class, 'show'])->name('spaces.show');
    Route::get('/spaces/{space}/channels/{channel}', [SpaceController::class, 'show'])->name('spaces.channel');
    Route::get('/space-channels/{channel}/messages/latest', [SpaceMessageController::class, 'latestMessages'])->name('space-messages.latest');

    Route::middleware(['throttle:actions'])->group(function () {
        Route::post('/spaces', [SpaceController::class, 'store'])->name('spaces.store');
        Route::post('/spaces/{space}/join', [SpaceController::class, 'join'])->name('spaces.join');
        Route::post('/spaces/{space}/leave', [SpaceController::class, 'leave'])->name('spaces.leave');
        Route::delete('/spaces/{space}', [SpaceController::class, 'destroy'])->name('spaces.destroy');
        Route::post('/spaces/{space}/share', [SpaceController::class, 'shareToFeed'])->name('spaces.share');

        Route::post('/spaces/{space}/channels', [SpaceChannelController::class, 'store'])->name('space-channels.store');
        Route::delete('/space-channels/{channel}', [SpaceChannelController::class, 'destroy'])->name('space-channels.destroy');

        Route::post('/space-channels/{channel}/messages', [SpaceMessageController::class, 'store'])->name('space-messages.store');
        Route::patch('/space-messages/{message}', [SpaceMessageController::class, 'updateMessage'])->name('space-messages.update');
        Route::delete('/space-messages/{message}', [SpaceMessageController::class, 'destroyMessage'])->name('space-messages.destroy');
        Route::post('/space-messages/{message}/react', [SpaceMessageController::class, 'reactToMessage'])->name('space-messages.react');
    });

    // ---------- MUSIC ----------
    Route::get('/music', function () {
        return view('music');
    })->name('music');

    // ---------- SETTINGS ----------
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    //---------- CONVERSATIONS ----------
    Route::middleware(['auth'])->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}', [ConversationController::class, 'store'])->name('conversations.store');
    Route::get('/conversations/{conversation}/messages/latest', [ConversationController::class, 'latestMessages'])->name('conversations.latest');
    Route::get('/conversations/start/{user}', [ConversationController::class, 'startWithUser'])->name('conversations.start');

    Route::patch('/messages/{message}', [ConversationController::class, 'updateMessage'])->name('messages.update');
    Route::delete('/messages/{message}', [ConversationController::class, 'destroyMessage'])->name('messages.destroy');
    Route::post('/messages/{message}/react', [ConversationController::class, 'reactToMessage'])->name('messages.react');
});
    //---------- BLOCKING ----------
    Route::middleware(['throttle:actions'])->group(function () {
        Route::post('/users/{user}/block', [BlockController::class, 'store'])->name('users.block');
        Route::delete('/users/{user}/block', [BlockController::class, 'destroy'])->name('users.unblock');
    });
    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/{report}/dismiss', [AdminReportController::class, 'dismiss'])->name('reports.dismiss');
        Route::post('/reports/{report}/delete-content', [AdminReportController::class, 'deleteContent'])->name('reports.delete-content');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/unsuspend', [AdminUserController::class, 'unsuspend'])->name('users.unsuspend');
        Route::post('/users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
        Route::post('/users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
});