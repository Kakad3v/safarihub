<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Schedule;

Schedule::call(fn() => Booking::where('status', 'pending')
    ->where('expires_at', '<', now())
    ->update(['status' => 'expired']))->everyFiveMinutes();