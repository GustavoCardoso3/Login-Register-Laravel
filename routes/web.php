<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PageController;

// Redirect to login page by default
Route::get('/',function(){
    return redirect('/login');
});

// Login routes
Route::get('/login',[AuthController::class,'login'])->name('login');
Route::post('/login',[AuthController::class,'loginPost'])->name('login.post');

// Google OAuth routes
Route::get('/auth/google/redirect',[AuthController::class,'googleRedirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback',[AuthController::class,'googleCallback']);

// GitHub OAuth routes
Route::get('/auth/github/redirect',[AuthController::class,'githubRedirect'])->name('auth.github.redirect');
Route::get('/auth/github/callback',[AuthController::class,'githubCallback']);

// Registration routes
Route::get('/register',[AuthController::class,'register'])->name('register');
Route::post('/register',[AuthController::class,'registerPost'])->name('register.post');

// Logout route
Route::get('/logout',[AuthController::class,'logout'])->name('logout');

// Email verification routes
Route::get('/email/verify', [AuthController::class,'verifyNotice'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}',[AuthController::class,'verifyEmail'])->middleware(['auth','signed'])->name('verification.verify');
Route::post('/email/verification-notification',[AuthController::class ,'verifyHandler'])->middleware(['auth','throttle:6,1'])->name('verification.send');

// Protected routes (only accessible after login and email verification)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/index',[PageController::class,'index'])->name('index');
});


