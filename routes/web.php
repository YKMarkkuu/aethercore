<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\PostController; // 👈 ADD THIS

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

    // ---------- PROFILE ----------
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

    // ---------- FEED ----------
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');

    // ---------- POSTS ---------- 👈 ADD THIS SECTION
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // ---------- FRIENDS ----------
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends/request/{user}', [FriendController::class, 'sendRequest'])->name('friends.request');
    Route::post('/friends/accept/{user}', [FriendController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/reject/{user}', [FriendController::class, 'rejectRequest'])->name('friends.reject');

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
});