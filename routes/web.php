<?php

use App\Http\Controllers\AiStudioController;
use App\Http\Controllers\BusinessController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::get('/businesses', [BusinessController::class, 'index']);
Route::get('/businesses/{business}/ai', [AiStudioController::class, 'edit'])->name('ai.studio');

Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
Route::get('/businesses/{business}/ai', [AiStudioController::class, 'edit'])->name('businesses.ai');

require __DIR__.'/auth.php';
