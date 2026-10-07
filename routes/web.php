<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\MagicLinkController;
use App\Livewire\Auth\SignIn;

Route::get('/', \App\Livewire\Home::class)->name('home');

Route::get('/login', SignIn::class)->name('login');

Route::get('/login/link/{token}', MagicLinkController::class)
    ->middleware('throttle:10,1')
    ->name('login.link');