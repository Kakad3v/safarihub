<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Livewire\Auth\SignIn;
use App\Livewire\Operators\PackageForm;
use App\Livewire\Operators\OperatorShow;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Home::class)->name('home');

Route::get('/login', SignIn::class)->name('login');

Route::get('/login/link/{token}', MagicLinkController::class)
    ->middleware('throttle:10,1')
    ->name('login.link');

Route::get('/operators/{profile:slug}', OperatorShow::class)->name('operators.show');

Route::get('/trips/{package:slug}', fn(\App\Models\Package $package) => $package->title)
    ->name('packages.show');
    
Route::middleware(['auth', 'operator'])->prefix('operator')->group(function () {
    Route::get('/packages/create', PackageForm::class)->name('operator.packages.create');
    Route::get('/packages/{package}/edit', PackageForm::class)->name('operator.packages.edit');
});