<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Livewire\Auth\SignIn;
use App\Livewire\Operators\OperatorShow;
use App\Livewire\Operators\PackageForm;
use App\Livewire\Trips\TripShow;
use App\Mail\LoginCodeMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Home::class)->name('home');

Route::get('/login', SignIn::class)->name('login');

Route::get('/login/link/{token}', MagicLinkController::class)
    ->middleware('throttle:10,1')
    ->name('login.link');

Route::get('/operators/{profile:slug}', OperatorShow::class)->name('operators.show');

Route::get('/trips/{package:slug}', TripShow::class)->name('trips.show');

Route::get('/bookings/{booking:reference}/pay', function (Booking $booking) {
    abort_unless($booking->user_id === auth()->id(), 403);

    return 'Booking ' . $booking->reference . ' is waiting for its deposit. Payment is the next step.';
})->middleware('auth')->name('bookings.pay');
    
Route::middleware(['auth', 'operator'])->prefix('operator')->group(function () {
    Route::get('/packages/create', PackageForm::class)->name('operator.packages.create');
    Route::get('/packages/{package}/edit', PackageForm::class)->name('operator.packages.edit');
});

if (app()->isLocal()) {
    Route::get('/mail-preview/login-code', fn() => new LoginCodeMail('048213', url('/login/link/example')));
}