<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\TaskDashboard;

Route::get('/', function () {
    return redirect()->route('dashboard');
    // return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
