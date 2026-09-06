<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SettingsController;


// ============================================
// AUTH ROUTES (Breeze)
// ============================================
require __DIR__.'/auth.php';


// ============================================
// PUBLIC ROUTES
// ============================================
Route::get('/', function () {
    return view('welcome');
});


// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware(['auth'])->group(function () {

    // ---------- FRIENDS (MUST BE FIRST!) ----------
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends/request/{user}', [FriendController::class, 'sendRequest'])->name('friends.request');
    Route::post('/friends/accept/{user}', [FriendController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/reject/{user}', [FriendController::class, 'rejectRequest'])->name('friends.reject');

    // ---------- PROFILE ----------
    Route::get('/profile/edit', function () {
        return redirect()->route('profile.index');
    })->name('profile.edit');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/status/update', [StatusController::class, 'update'])->name('status.update');
    Route::post('/profile/stats-period', [ProfileController::class, 'updateStatsPeriod'])->name('profile.stats-period');
    Route::post('/profile/top-friends', [ProfileController::class, 'updateTopFriends'])->name('profile.top-friends');

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
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // ---------- SPACES ----------
    Route::get('/spaces', function () {
        return view('spaces');
    })->name('spaces');

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
    Route::get('/conversations/start/{user}', [ConversationController::class, 'startWithUser'])->name('conversations.start');
});
});