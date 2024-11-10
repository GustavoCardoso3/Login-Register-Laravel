<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthManager;
use App\Http\Controllers\PageManager;


Route::get('/',function(){
    return redirect('/login');
});

Route::get('/login',[AuthManager::class,'login'])->name('login');
Route::post('/login',[AuthManager::class,'loginPost'])->name('login.post');

Route::get('/register',[AuthManager::class,'register'])->name('register');
Route::post('/register',[AuthManager::class,'registerPost'])->name('register.post');

Route::get('/logout',[AuthManager::class,'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/index',[PageManager::class,'index'])->name('index');
});